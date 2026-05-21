<script setup>
/**
 * Composant de jeu pour une énigme spécifique.
 * Gère l'affichage de l'énigme, le chronomètre, la carte GPS et les interactions.
 */
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import L from 'leaflet';

// Props reçus du contrôleur Laravel
const props = defineProps({
    game: Object,         // Données de la partie en cours
    enigme: Object,       // Détails de l'énigme actuelle
    progression: Object,  // État de progression (étape x/y)
    labels: Object,       // Libellés pour les énumérations (difficulté, mode, etc.)
    modal_lieu: {         // Données pour afficher la modal du lieu (solution/validation)
        type: Object,
        default: null,
    },
    gps_error: {          // Erreur GPS éventuelle passée par le flash
        type: String,
        default: null,
    },
});

// --- ÉTAT RÉACTIF ---
const mapContainer = ref(null);
const mapInstance = ref(null);
const playerMarker = ref(null);
let targetMarker = null;
let radiusCircle = null;

const gpsLoading = ref(false);
const gpsError = ref(props.gps_error);
const showModal = ref(false);
const modalLieu = ref(null);
let timerInterval = null;

const defaultMapCenter = [6.370293, 2.391236]; // Centre par défaut (Cotonou)

// --- CHRONOMÈTRE ---
// Initialisation du temps restant en secondes
const remainingSeconds = ref(
    Math.max(0, Number(props.game.duree_restante ?? props.game.duree_prevue) * 60)
);

// Formatage du temps pour l'affichage (ex: "15 min 04s")
const timerDisplay = computed(() => {
    const minutes = Math.floor(remainingSeconds.value / 60);
    const seconds = remainingSeconds.value % 60;
    return `${minutes} min ${seconds.toString().padStart(2, '0')}s`;
});

/**
 * Lance le décompte du temps restant.
 */
const lancerChrono = () => {
    if (timerInterval) clearInterval(timerInterval);
    timerInterval = setInterval(() => {
        if (remainingSeconds.value <= 0) {
            clearInterval(timerInterval);
            return;
        }
        remainingSeconds.value -= 1;
    }, 1000);
};

// --- CALCULS ET LIBELLÉS ---
// Récupération des libellés lisibles pour l'UI
const locomotionLabel = computed(() =>
    props.labels?.moyens_locomotion?.[props.game.moyen_locomotion] ?? props.game.moyen_locomotion
);

const difficulteLabel = computed(() =>
    props.labels?.niveaux_difficulte?.[props.game.niveau_difficulte] ?? props.game.niveau_difficulte
);

const modeLabel = computed(() =>
    props.labels?.modes?.[props.game.mode_jeu] ?? props.game.mode_jeu
);

// Coordonnées et rayon de validation (on utilise 'place' ou 'lieu_validation' selon la structure)
const lieuValidation = computed(() => props.enigme.place || props.enigme.lieu_validation);
const cibleLat = computed(() => Number(lieuValidation.value?.latitude ?? 0));
const cibleLng = computed(() => Number(lieuValidation.value?.longitude ?? 0));
const rayon = computed(() => Number(lieuValidation.value?.rayon_validation ?? 30));

// --- LOGIQUE D'AFFICHAGE (MODALES & FLASH) ---
const page = usePage();

/**
 * Ouvre la modal d'information sur un lieu.
 */
const ouvrirModal = async (data) => {
    if (!data?.nom) return;
    modalLieu.value = data;
    await nextTick();
    showModal.value = true;
};

const fermerModal = () => {
    showModal.value = false;
};

// Surveillance des données flash pour ouvrir automatiquement les modales
watch(
    () => props.modal_lieu || page.props.flash?.modal_lieu,
    (data) => { if (data) ouvrirModal(data); },
    { immediate: true, deep: true }
);

watch(
    () => props.gps_error ?? page.props.flash?.gps_error,
    (msg) => { gpsError.value = msg ?? null; },
    { immediate: true }
);

// --- ACTIONS DU JEU ---
const routeParams = () => ({
    game: props.game.id,
    enigme: props.enigme.id,
});

/**
 * Valide la position GPS actuelle.
 */
const validerPosition = () => {
    if (!navigator.geolocation) {
        alert('La géolocalisation n\'est pas supportée par votre navigateur.');
        return;
    }

    gpsLoading.value = true;
    gpsError.value = null;

    navigator.geolocation.getCurrentPosition(
        (position) => {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;

            // Mise à jour visuelle immédiate sur la carte
            afficherPositionJoueur(lat, lng);

            // Envoi au backend pour validation métier
            router.post(
                route('game.valider', routeParams()),
                { latitude: lat, longitude: lng },
                {
                    preserveScroll: true,
                    onFinish: () => { gpsLoading.value = false; },
                }
            );
        },
        () => {
            gpsLoading.value = false;
            gpsError.value = "Impossible d'accéder à votre position GPS.";
        },
        { enableHighAccuracy: true, timeout: 15000 }
    );
};

/**
 * Demande un indice.
 */
const demanderIndice = () => {
    router.post(route('game.indice', routeParams()), {}, { preserveScroll: true });
};

/**
 * Révèle la solution de l'énigme.
 */
const voirSolution = () => {
    if (!confirm('Voulez-vous vraiment voir la solution ? Cela marquera l\'énigme comme non résolue.')) {
        return;
    }
    router.post(route('game.solution', routeParams()));
};

/**
 * Passe à l'énigme suivante (si résolue ou abandonnée).
 */
const passerSuivant = () => {
    router.post(route('game.skip', routeParams()));
};

/**
 * Permet de passer à une autre énigme disponible.
 */
const passerAutreEnigme = () => {
    if (!confirm('Passer à une autre énigme ?')) return;
    router.post(route('game.skip', routeParams()));
};

/**
 * Met la partie en pause et retourne au dashboard.
 */
const mettreEnPause = () => {
    if (!confirm('Mettre la partie en pause ?')) return;
    router.post(route('game.pause', props.game.id), {}, {
        onSuccess: () => router.visit(route('dashboard'))
    });
};

const retourDashboard = () => {
    router.visit(route('dashboard'));
};

// --- LOGIQUE CARTE (LEAFLET) ---
const initMap = (lat, lng) => {
    if (!mapContainer.value) return;
    if (mapInstance.value) mapInstance.value.remove();

    mapInstance.value = L.map(mapContainer.value, { zoomControl: false })
        .setView([lat, lng], 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(mapInstance.value);
};

const renderTarget = () => {
    if (!mapInstance.value || !cibleLat.value) return;

    if (targetMarker) targetMarker.remove();
    if (radiusCircle) radiusCircle.remove();

    targetMarker = L.circleMarker([cibleLat.value, cibleLng.value], {
        radius: 8, color: '#4f46e5', fillColor: '#4f46e5', fillOpacity: 1, weight: 2
    }).addTo(mapInstance.value);

    radiusCircle = L.circle([cibleLat.value, cibleLng.value], {
        radius: rayon.value, color: '#4f46e5', fillColor: '#4f46e5', fillOpacity: 0.1, weight: 1
    }).addTo(mapInstance.value);
};

const afficherPositionJoueur = (lat, lng) => {
    if (!mapInstance.value) initMap(lat, lng);
    if (playerMarker.value) playerMarker.value.remove();

    playerMarker.value = L.circleMarker([lat, lng], {
        radius: 10, color: '#ef4444', fillColor: '#ef4444', fillOpacity: 1, weight: 3
    }).addTo(mapInstance.value).bindPopup('Vous êtes ici');

    mapInstance.value.setView([lat, lng], 16);
};

// --- CYCLE DE VIE ---
onMounted(() => {
    initMap(cibleLat.value || defaultMapCenter[0], cibleLng.value || defaultMapCenter[1]);
    renderTarget();
    lancerChrono();
});

onUnmounted(() => {
    if (mapInstance.value) mapInstance.value.remove();
    if (timerInterval) clearInterval(timerInterval);
});

// --- PERMISSIONS UI ---
const peutValiderGps = computed(() => props.enigme.pivot.statut === 'en_cours' && !props.enigme.pivot.solution_affichee);
const peutVoirSolution = computed(() => props.enigme.pivot.statut === 'en_cours' && !props.enigme.pivot.solution_affichee);
const peutPasserAutreEnigme = computed(() => props.enigme.pivot.statut === 'en_cours' && !props.enigme.pivot.solution_affichee);
const peutPasserSuivant = computed(() => props.enigme.pivot.statut !== 'en_cours' || props.enigme.pivot.solution_affichee);
const partieEnPausePossible = computed(() => props.game.statut === 'en_cours');

</script>

<template>
    <Head :title="`Partie - ${game.environment?.nom ?? 'City Play'}`" />

    <AuthenticatedLayout>
        <div class="py-12 bg-gray-100 min-h-screen">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8 space-y-4">

                <!-- Infos barre supérieure -->
                <div class="bg-white rounded-lg shadow px-4 py-3 flex flex-wrap gap-3 text-xs text-gray-600">
                    <span class="font-semibold text-gray-800">{{ modeLabel }}</span>
                    <span>· {{ difficulteLabel }}</span>
                    <span>· {{ locomotionLabel }}</span>
                    <span class="text-indigo-600 font-bold">· {{ timerDisplay }} restants</span>
                    <span v-if="game.mode_jeu === 'equipe'">· {{ game.nb_membres }} membres</span>
                </div>

                <!-- En-tête de l'énigme avec boutons d'action -->
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

                <!-- Contenu principal -->
                <div class="bg-white p-6 shadow rounded-b-lg space-y-6">
                    <!-- Image de l'énigme -->
                    <div v-if="enigme.image" class="overflow-hidden rounded-lg max-h-60 bg-gray-200">
                        <img
                            :src="'/storage/' + enigme.image"
                            alt="Illustration"
                            class="w-full h-full object-cover"
                        />
                    </div>

                    <!-- Texte de l'énigme -->
                    <div class="prose max-w-none">
                        <h3 class="text-gray-500 text-sm font-medium">Votre énigme</h3>
                        <p class="text-lg text-gray-800 font-serif italic">" {{ enigme.texte }} "</p>
                    </div>

                    <!-- Carte et validation GPS -->
                    <div
                        v-if="peutValiderGps"
                        class="border-t border-gray-100 pt-4 space-y-3"
                    >
                        <h4 class="text-sm font-semibold text-gray-700">
                            Valider votre position
                        </h4>
                        <p class="text-xs text-gray-500">
                            Votre position sera comparée au lieu à trouver (rayon
                            {{ rayon }} m).
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

                    <!-- Section Indices -->
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

                    <!-- Actions disponibles -->
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

        <!-- Modal pour la solution ou la validation réussie -->
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
/* Styles spécifiques pour Leaflet et boutons */
#play-map {
    z-index: 0;
}
.gps-validate-btn:hover:not(:disabled) {
    background-color: #15803d;
}
</style>
