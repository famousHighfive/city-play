<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import L from 'leaflet';

const props = defineProps({
    game: Object,
    enigme: Object,
    progression: Object,
    labels: Object,
    modal_lieu: {
        type: Object,
        default: null,
    },
    gps_error: {
        type: String,
        default: null,
    },
});

const mapContainer = ref(null);
const mapInstance = ref(null);
const playerMarker = ref(null);
const gpsLoading = ref(false);
const gpsError = ref(props.gps_error);

const page = usePage();
const showModal = ref(false);
const modalLieu = ref(null);

const defaultMapCenter = [6.370293, 2.391236];

const locomotionLabel = computed(
    () => props.labels.moyens_locomotion[props.game.moyen_locomotion] ?? props.game.moyen_locomotion
);

const difficulteLabel = computed(
    () => props.labels.niveaux_difficulte[props.game.niveau_difficulte] ?? props.game.niveau_difficulte
);

const modeLabel = computed(
    () => props.labels.modes[props.game.mode_jeu] ?? props.game.mode_jeu
);

const peutValiderGps = computed(
    () => props.enigme.pivot.statut === 'en_cours' && !props.enigme.pivot.solution_affichee
);

const peutVoirSolution = computed(() => props.enigme.pivot.statut === 'en_cours');

const peutPasserSuivant = computed(
    () =>
        props.enigme.pivot.statut === 'resolue'
        || props.enigme.pivot.statut === 'non_resolue'
        || props.enigme.pivot.solution_affichee
);

const peutPasserAutreEnigme = computed(
    () => props.enigme.pivot.statut === 'en_cours' && !props.enigme.pivot.solution_affichee
);

const partieEnPausePossible = computed(() => props.game.statut === 'en_cours');

const ouvrirModal = async (data) => {
    if (!data?.nom) {
        return;
    }
    modalLieu.value = data;
    await nextTick();
    showModal.value = true;
};

const fermerModal = () => {
    showModal.value = false;
};

const ouvrirModalDepuisFlash = (data) => {
    if (data?.nom) {
        ouvrirModal(data);
    }
};

watch(
    () => props.modal_lieu,
    ouvrirModalDepuisFlash,
    { immediate: true, deep: true }
);

watch(
    () => page.props.flash?.modal_lieu,
    ouvrirModalDepuisFlash,
    { immediate: true, deep: true }
);

watch(
    () => props.gps_error ?? page.props.flash?.gps_error,
    (msg) => {
        gpsError.value = msg ?? null;
    },
    { immediate: true }
);

const routeParams = () => ({
    game: props.game.id,
    enigme: props.enigme.id,
});

const demanderIndice = () => {
    router.post(route('game.indice', routeParams()), {}, {
        preserveScroll: true,
        preserveState: false,
    });
};

const voirSolution = () => {
    router.post(route('game.solution', routeParams()), {}, {
        preserveScroll: true,
        preserveState: false,
    });
};

const passerSuivant = () => {
    fermerModal();
    router.post(route('game.skip', routeParams()), {}, {
        preserveState: false,
    });
};

const passerAutreEnigme = () => {
    if (!confirm('Passer à une autre énigme sans résoudre celle-ci ?')) {
        return;
    }
    fermerModal();
    router.post(route('game.skip', routeParams()), {}, {
        preserveState: false,
    });
};

const mettreEnPause = () => {
    if (!confirm('Mettre la partie en pause ? Vous pourrez reprendre depuis le tableau de bord.')) {
        return;
    }
    router.post(route('game.pause', props.game.id));
};

const retourDashboard = () => {
    router.visit(route('dashboard'));
};

const initMap = (lat, lng) => {
    if (!mapContainer.value) {
        return;
    }

    if (mapInstance.value) {
        mapInstance.value.remove();
        mapInstance.value = null;
    }

    mapInstance.value = L.map(mapContainer.value).setView([lat, lng], 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(mapInstance.value);
};

const afficherPositionJoueur = (lat, lng) => {
    if (!mapContainer.value) {
        return;
    }

    if (!mapInstance.value) {
        initMap(lat, lng);
    }

    if (playerMarker.value) {
        mapInstance.value.removeLayer(playerMarker.value);
    }

    playerMarker.value = L.circleMarker([lat, lng], {
        radius: 10,
        color: '#2563eb',
        fillColor: '#3b82f6',
        fillOpacity: 0.9,
        weight: 2,
    })
        .addTo(mapInstance.value)
        .bindPopup('Votre position');

    mapInstance.value.setView([lat, lng], 16);
};

const validerPosition = () => {
    if (!navigator.geolocation) {
        alert('La géolocalisation n’est pas disponible.');
        return;
    }

    gpsLoading.value = true;
    gpsError.value = null;

    navigator.geolocation.getCurrentPosition(
        (position) => {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;

            afficherPositionJoueur(lat, lng);

            router.post(
                route('game.valider', routeParams()),
                { latitude: lat, longitude: lng },
                {
                    preserveScroll: true,
                    preserveState: false,
                    onFinish: () => {
                        gpsLoading.value = false;
                    },
                }
            );
        },
        () => {
            gpsLoading.value = false;
            alert('Impossible de récupérer votre position.');
        },
        { enableHighAccuracy: true, timeout: 15000 }
    );
};

onMounted(() => {
    if (mapContainer.value) {
        initMap(defaultMapCenter[0], defaultMapCenter[1]);
    }
});

onUnmounted(() => {
    if (mapInstance.value) {
        mapInstance.value.remove();
        mapInstance.value = null;
    }
});
</script>

<template>
    <Head :title="`Partie - ${game.environment?.nom ?? 'City Play'}`" />

    <AuthenticatedLayout>
        <div class="py-12 bg-gray-100 min-h-screen">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8 space-y-4">

                <div class="bg-white rounded-lg shadow px-4 py-3 flex flex-wrap gap-3 text-xs text-gray-600">
                    <span class="font-semibold text-gray-800">{{ modeLabel }}</span>
                    <span>· {{ difficulteLabel }}</span>
                    <span>· {{ locomotionLabel }}</span>
                    <span>· {{ game.duree_restante ?? game.duree_prevue }} min restantes</span>
                    <span v-if="game.mode_jeu === 'equipe'">· {{ game.nb_membres }} membres</span>
                </div>

                <div class="bg-indigo-600 text-white p-4 rounded-t-lg shadow">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs uppercase tracking-wider font-semibold opacity-75">
                                Énigme {{ progression.etape }} / {{ progression.total }}
                            </p>
                            <h1 class="text-xl font-bold">À résoudre</h1>
                        </div>
                        <div class="flex flex-col gap-2 shrink-0">
                            <button
                                v-if="partieEnPausePossible"
                                type="button"
                                @click="mettreEnPause"
                                class="rounded-lg bg-white/20 hover:bg-white/30 px-3 py-1.5 text-xs font-semibold transition"
                            >
                                ⏸ Pause
                            </button>
                            <button
                                type="button"
                                @click="retourDashboard"
                                class="rounded-lg bg-white/10 hover:bg-white/20 px-3 py-1.5 text-xs font-medium transition"
                            >
                                ← Dashboard
                            </button>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 shadow rounded-b-lg space-y-6">
                    <div v-if="enigme.image" class="overflow-hidden rounded-lg max-h-60 bg-gray-200">
                        <img
                            :src="'/storage/' + enigme.image"
                            alt="Illustration"
                            class="w-full h-full object-cover"
                        />
                    </div>

                    <div class="prose max-w-none">
                        <h3 class="text-gray-500 text-sm font-medium">Votre énigme</h3>
                        <p class="text-lg text-gray-800 font-serif italic">" {{ enigme.texte }} "</p>
                    </div>

                    <div
                        v-if="peutValiderGps"
                        class="border-t border-gray-100 pt-4 space-y-3"
                    >
                        <h4 class="text-sm font-semibold text-gray-700">
                            Valider votre position
                        </h4>
                        <p class="text-xs text-gray-500">
                            Votre position sera comparée au lieu à trouver (rayon
                            {{ enigme.lieu_validation.rayon_validation }} m).
                        </p>
                        <div
                            id="play-map"
                            ref="mapContainer"
                            class="h-72 w-full rounded-xl border border-gray-200 z-0"
                        />
                        <p
                            v-if="gpsError"
                            class="text-sm rounded-lg px-4 py-3 bg-amber-50 text-amber-900 border border-amber-200"
                        >
                            {{ gpsError }}
                        </p>
                    </div>

                    <div class="border-t border-gray-100 pt-4 space-y-3">
                        <h4 class="text-sm font-semibold text-gray-700">
                            Indices demandés ({{ enigme.pivot.nb_indices_demandes }} / 2)
                        </h4>

                        <div
                            v-if="enigme.pivot.nb_indices_demandes >= 1"
                            class="bg-amber-50 border-l-4 border-amber-500 p-3 rounded text-sm text-amber-900"
                        >
                            <strong>Indice 1 :</strong> {{ enigme.indice_1 }}
                        </div>

                        <div
                            v-if="enigme.pivot.nb_indices_demandes >= 2"
                            class="bg-amber-50 border-l-4 border-amber-500 p-3 rounded text-sm text-amber-900"
                        >
                            <strong>Indice 2 :</strong>
                            {{ enigme.indice_2 || "Pas d'indice supplémentaire disponible." }}
                        </div>

                        <button
                            v-if="enigme.pivot.nb_indices_demandes < 2 && !enigme.pivot.solution_affichee"
                            @click="demanderIndice"
                            class="text-xs bg-amber-500 text-white px-3 py-1.5 rounded shadow hover:bg-amber-600 transition"
                        >
                            Obtenir un indice
                        </button>
                    </div>

                    <div class="border-t border-gray-100 pt-4 space-y-4">
                        <h4 class="text-sm font-semibold text-gray-700">Actions</h4>

                        <div class="flex flex-wrap gap-3">
                            <button
                                v-if="peutValiderGps"
                                type="button"
                                :disabled="gpsLoading"
                                @click="validerPosition"
                                class="gps-validate-btn bg-green-600 text-white text-sm px-5 py-2.5 rounded-md shadow font-semibold disabled:opacity-60 transition"
                            >
                                {{
                                    gpsLoading
                                        ? 'Localisation...'
                                        : '📍 Valider ma position GPS'
                                }}
                            </button>

                            <button
                                v-if="peutVoirSolution"
                                type="button"
                                @click="voirSolution"
                                class="text-sm border border-red-200 text-red-700 bg-red-50 hover:bg-red-100 px-4 py-2.5 rounded-md font-medium transition"
                            >
                                Voir la solution (lieu)
                            </button>

                            <button
                                v-if="peutPasserAutreEnigme"
                                type="button"
                                @click="passerAutreEnigme"
                                class="text-sm border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 px-4 py-2.5 rounded-md font-medium transition"
                            >
                                Passer à une autre énigme →
                            </button>

                            <button
                                v-if="peutPasserSuivant"
                                type="button"
                                @click="passerSuivant"
                                class="bg-gray-800 text-white text-sm px-5 py-2.5 rounded-md shadow hover:bg-gray-700 font-medium transition"
                            >
                                Énigme suivante →
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="showModal && modalLieu"
                    class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 px-4"
                    @click.self="fermerModal"
                >
                    <Transition
                        enter-active-class="transition duration-200 ease-out"
                        enter-from-class="opacity-0 scale-95"
                        enter-to-class="opacity-100 scale-100"
                        leave-active-class="transition duration-150 ease-in"
                        leave-from-class="opacity-100 scale-100"
                        leave-to-class="opacity-0 scale-95"
                        appear
                    >
                        <div
                            v-if="showModal && modalLieu"
                            class="w-full max-w-md rounded-2xl bg-white p-8 shadow-2xl"
                            role="dialog"
                            aria-modal="true"
                        >
                            <div
                                v-if="modalLieu.type === 'success'"
                                class="text-center mb-4"
                            >
                                <div class="text-5xl mb-2">🎉</div>
                                <h2 class="text-2xl font-black text-green-700">
                                    Félicitations !
                                </h2>
                                <p class="text-sm text-gray-500 mt-1">
                                    Vous avez trouvé le bon endroit.
                                </p>
                            </div>

                            <div
                                v-else
                                class="text-center mb-4"
                            >
                                <div class="text-5xl mb-2">📍</div>
                                <h2 class="text-2xl font-black text-gray-800">
                                    Le lieu à découvrir
                                </h2>
                                <p class="text-sm text-gray-500 mt-1">
                                    Énigme marquée comme non résolue.
                                </p>
                            </div>

                            <div class="rounded-xl bg-indigo-50 border border-indigo-100 p-5 space-y-2">
                                <h3 class="text-lg font-bold text-gray-900">
                                    {{ modalLieu.nom }}
                                </h3>
                                <p
                                    v-if="modalLieu.description"
                                    class="text-sm text-gray-600 leading-relaxed"
                                >
                                    {{ modalLieu.description }}
                                </p>
                                <p
                                    v-else
                                    class="text-sm text-gray-400 italic"
                                >
                                    Aucune description disponible pour ce lieu.
                                </p>
                            </div>

                            <button
                                type="button"
                                @click="fermerModal"
                                class="mt-6 w-full rounded-xl bg-indigo-600 py-3 text-sm font-semibold text-white hover:bg-indigo-700 transition"
                            >
                                Continuer
                            </button>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>
    </AuthenticatedLayout>
</template>

<style scoped>
@keyframes gps-blink {
    0%,
    100% {
        opacity: 1;
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.75);
        transform: scale(1);
    }
    50% {
        opacity: 0.88;
        box-shadow: 0 0 0 14px rgba(34, 197, 94, 0);
        transform: scale(1.03);
    }
}

.gps-validate-btn {
    animation: gps-blink 1.1s ease-in-out infinite;
}

.gps-validate-btn:hover:not(:disabled) {
    background-color: #15803d;
}
</style>
