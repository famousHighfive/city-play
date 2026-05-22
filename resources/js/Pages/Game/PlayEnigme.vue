<script setup>
/**
 * Composant de jeu pour une énigme spécifique.
 * Gère l'affichage de l'énigme, le chronomètre, la carte GPS et les interactions.
 */
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import L from 'leaflet';
import { useGameStore } from '@/stores/game';
import { useAudioStore } from '@/stores/audio';
import ConfirmDialog from 'primevue/confirmdialog';
import { useConfirm } from 'primevue/useconfirm';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import Dialog from 'primevue/dialog';

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

// --- STORES ---
const gameStore = useGameStore();
const audioStore = useAudioStore();
const confirm = useConfirm();
const toast = useToast();

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
const showGameEndModal = ref(false); // Modal pour la fin de partie

const defaultMapCenter = [6.370293, 2.391236]; // Centre par défaut (Cotonou)

// --- CHRONOMÈTRE AVEC STORE ---
// Utiliser le store Pinia pour gérer le chronomètre
const timerDisplay = computed(() => gameStore.formattedTime);
const remainingSeconds = computed(() => gameStore.remainingSeconds);

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
    // Jouer un son selon le type de modal
    if (data.type === 'success') {
        audioStore.play('success');
    } else {
        audioStore.play('notification');
    }
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
    (msg) => { 
        gpsError.value = msg ?? null; 
        if (msg) {
            audioStore.play('error');
        }
    },
    { immediate: true }
);

// Surveiller quand le temps est écoulé
const handleTimeUp = () => {
    showGameEndModal.value = true;
    audioStore.play('notification');
};

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
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: 'La géolocalisation n\'est pas supportée par votre navigateur.',
            life: 5000
        });
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
            audioStore.play('error');
        },
        { enableHighAccuracy: true, timeout: 15000 }
    );
};

/**
 * Demande un indice.
 */
const demanderIndice = () => {
    audioStore.play('click');
    router.post(route('game.indice', routeParams()), {}, { preserveScroll: true });
};

/**
 * Révèle la solution de l'énigme.
 */
const voirSolution = () => {
    confirm.require({
        message: 'Voulez-vous vraiment voir la solution ? Cela marquera l\'énigme comme non résolue.',
        header: 'Confirmation',
        icon: 'pi pi-exclamation-triangle',
        accept: () => {
            audioStore.play('click');
            router.post(route('game.solution', routeParams()));
        },
        reject: () => {
            audioStore.play('click');
        }
    });
};

/**
 * Passe à l'énigme suivante (si résolue ou abandonnée).
 */
const passerSuivant = () => {
    audioStore.play('click');
    router.post(route('game.skip', routeParams()));
};

/**
 * Permet de passer à une autre énigme disponible.
 */
const passerAutreEnigme = () => {
    confirm.require({
        message: 'Passer à une autre énigme ?',
        header: 'Confirmation',
        icon: 'pi pi-info-circle',
        accept: () => {
            audioStore.play('click');
            router.post(route('game.skip', routeParams()));
        }
    });
};

/**
 * Met la partie en pause et retourne au dashboard.
 */
const mettreEnPause = () => {
    confirm.require({
        message: 'Mettre la partie en pause ?',
        header: 'Confirmation',
        icon: 'pi pi-pause-circle',
        accept: () => {
            gameStore.pause();
            audioStore.play('click');
            router.post(route('game.pause', props.game.id), {}, {
                onSuccess: () => router.visit(route('dashboard'))
            });
        }
    });
};

const retourDashboard = () => {
    audioStore.play('click');
    router.visit(route('dashboard'));
};

const retourDashboardApresFin = () => {
    gameStore.reset();
    router.visit(route('dashboard'));
};

// --- LOGIQUE CARTE (LEAFLET) - AVEC MASQUAGE DES LABELS ---
const initMap = (lat, lng) => {
    if (!mapContainer.value) return;
    if (mapInstance.value) mapInstance.value.remove();

    mapInstance.value = L.map(mapContainer.value, { zoomControl: false })
        .setView([lat, lng], 16);

    // Utiliser un style de carte minimaliste sans labels pour l'exploration
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_nolabels/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap contributors, &copy; CARTO'
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
    
    // Initialiser le store avec les données de la partie
    if (gameStore.gameId !== props.game.id) {
        gameStore.initializeGame(
            props.game.id,
            props.game.duree_restante ?? props.game.duree_prevue
        );
    } else {
        // Si le jeu est déjà dans le store, reprendre
        gameStore.startTimer();
    }

    // Écouter l'événement de fin de temps
    window.addEventListener('game-time-up', handleTimeUp);
});

onUnmounted(() => {
    if (mapInstance.value) mapInstance.value.remove();
    window.removeEventListener('game-time-up', handleTimeUp);
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
        <div class="py-12 bg-gradient-to-br from-slate-50 to-indigo-50 min-h-screen">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8 space-y-4">

                <!-- Infos barre supérieure -->
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-xl border border-white/50 px-6 py-4 flex flex-wrap gap-3 text-sm">
                    <span class="font-semibold text-gray-800 bg-gradient-to-r from-indigo-500 to-purple-600 bg-clip-text text-transparent">{{ modeLabel }}</span>
                    <span class="text-gray-600">· {{ difficulteLabel }}</span>
                    <span class="text-gray-600">· {{ locomotionLabel }}</span>
                    <span class="font-bold bg-gradient-to-r from-orange-500 to-red-500 bg-clip-text text-transparent">· {{ timerDisplay }} restants</span>
                    <span v-if="game.mode_jeu === 'equipe'" class="text-gray-600">· {{ game.nb_membres }} membres</span>
                </div>

                <!-- En-tête de l'énigme avec boutons d'action -->
                <div class="bg-gradient-to-br from-indigo-600 to-purple-700 text-white p-6 rounded-2xl shadow-2xl">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs uppercase tracking-wider font-semibold opacity-80">
                                Énigme {{ progression.etape }} / {{ progression.total }}
                            </p>
                            <h1 class="text-2xl font-black mt-1">À résoudre</h1>
                        </div>
                        <div class="flex flex-col gap-2 shrink-0">
                            <button
                                v-if="partieEnPausePossible"
                                type="button"
                                @click="mettreEnPause"
                                class="rounded-xl bg-white/20 hover:bg-white/30 px-4 py-2 text-sm font-semibold transition-all duration-300 hover:scale-105 backdrop-blur-sm"
                            >
                                ⏸ Pause
                            </button>
                            <button
                                type="button"
                                @click="retourDashboard"
                                class="rounded-xl bg-white/10 hover:bg-white/20 px-4 py-2 text-sm font-medium transition-all duration-300 hover:scale-105 backdrop-blur-sm"
                            >
                                ← Dashboard
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Contenu principal -->
                <div class="bg-white/90 backdrop-blur-xl p-8 shadow-2xl rounded-2xl border border-white/60 space-y-8">
                    <!-- Image de l'énigme -->
                    <div v-if="enigme.image" class="overflow-hidden rounded-2xl max-h-72 bg-gradient-to-br from-gray-100 to-gray-200 shadow-inner">
                        <img
                            :src="'/storage/' + enigme.image"
                            alt="Illustration"
                            class="w-full h-full object-cover transition-transform duration-500 hover:scale-105"
                        />
                    </div>

                    <!-- Texte de l'énigme -->
                    <div class="prose max-w-none">
                        <h3 class="text-gray-500 text-sm font-semibold uppercase tracking-wider">Votre énigme</h3>
                        <p class="text-xl text-gray-800 font-serif italic mt-3 leading-relaxed">" {{ enigme.texte }} "</p>
                    </div>

                    <!-- Carte et validation GPS -->
                    <div
                        v-if="peutValiderGps"
                        class="border-t border-gray-100 pt-6 space-y-4"
                    >
                        <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">
                            Valider votre position
                        </h4>
                        <p class="text-xs text-gray-500">
                            Votre position sera comparée au lieu à trouver (rayon
                            {{ rayon }} m).
                        </p>
                        <div
                            id="play-map"
                            ref="mapContainer"
                            class="h-80 w-full rounded-2xl border border-gray-200 shadow-lg overflow-hidden"
                        />
                        <div
                            v-if="gpsError"
                            class="text-sm rounded-xl px-6 py-4 bg-gradient-to-r from-amber-50 to-orange-50 text-amber-900 border border-amber-200 shadow-md"
                        >
                            <div class="flex items-center gap-2">
                                <span class="text-2xl">⚠️</span>
                                <span class="font-semibold">{{ gpsError }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Section Indices -->
                    <div class="border-t border-gray-100 pt-6 space-y-4">
                        <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">
                            Indices demandés ({{ enigme.pivot.nb_indices_demandes }} / 2)
                        </h4>

                        <div
                            v-if="enigme.pivot.nb_indices_demandes >= 1"
                            class="bg-gradient-to-r from-amber-50 to-yellow-50 border-l-4 border-amber-500 p-5 rounded-xl shadow-md"
                        >
                            <strong class="text-amber-800 text-sm uppercase tracking-wider">Indice 1 :</strong>
                            <p class="text-amber-900 mt-2">{{ enigme.indice_1 }}</p>
                        </div>

                        <div
                            v-if="enigme.pivot.nb_indices_demandes >= 2"
                            class="bg-gradient-to-r from-amber-50 to-yellow-50 border-l-4 border-amber-500 p-5 rounded-xl shadow-md"
                        >
                            <strong class="text-amber-800 text-sm uppercase tracking-wider">Indice 2 :</strong>
                            <p class="text-amber-900 mt-2">{{ enigme.indice_2 || "Pas d'indice supplémentaire disponible." }}</p>
                        </div>

                        <button
                            v-if="enigme.pivot.nb_indices_demandes < 2 && !enigme.pivot.solution_affichee"
                            @click="demanderIndice"
                            class="text-sm bg-gradient-to-r from-amber-500 to-orange-500 text-white px-6 py-3 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 font-semibold"
                        >
                            Obtenir un indice
                        </button>
                    </div>

                    <!-- Actions disponibles -->
                    <div class="border-t border-gray-100 pt-6 space-y-4">
                        <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Actions</h4>

                        <div class="flex flex-wrap gap-3">
                            <button
                                v-if="peutValiderGps"
                                type="button"
                                :disabled="gpsLoading"
                                @click="validerPosition"
                                class="gps-validate-btn bg-gradient-to-r from-green-500 to-emerald-600 text-white text-sm px-8 py-4 rounded-xl shadow-xl font-semibold disabled:opacity-60 transition-all duration-300 hover:scale-105 hover:shadow-2xl"
                            >
                                {{
                                    gpsLoading
                                        ? '🔍 Localisation...'
                                        : '📍 Valider ma position GPS'
                                }}
                            </button>

                            <button
                                v-if="peutVoirSolution"
                                type="button"
                                @click="voirSolution"
                                class="text-sm border-2 border-red-200 text-red-700 bg-gradient-to-r from-red-50 to-pink-50 hover:from-red-100 hover:to-pink-100 px-6 py-4 rounded-xl font-semibold transition-all duration-300 hover:scale-105"
                            >
                                Voir la solution (lieu)
                            </button>

                            <button
                                v-if="peutPasserAutreEnigme"
                                type="button"
                                @click="passerAutreEnigme"
                                class="text-sm border-2 border-gray-300 text-gray-700 bg-gradient-to-r from-white to-gray-50 hover:from-gray-50 hover:to-gray-100 px-6 py-4 rounded-xl font-semibold transition-all duration-300 hover:scale-105 shadow-md"
                            >
                                Passer à une autre énigme →
                            </button>

                            <button
                                v-if="peutPasserSuivant"
                                type="button"
                                @click="passerSuivant"
                                class="bg-gradient-to-r from-gray-800 to-gray-900 text-white text-sm px-8 py-4 rounded-xl shadow-xl font-semibold transition-all duration-300 hover:scale-105 hover:shadow-2xl"
                            >
                                Énigme suivante →
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PrimeVue ConfirmDialog et Toast -->
        <ConfirmDialog />
        <Toast position="top-right" />

        <!-- Modal pour la solution ou la validation réussie -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="showModal && modalLieu"
                    class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 backdrop-blur-sm px-4"
                    @click.self="fermerModal"
                >
                    <Transition
                        enter-active-class="transition duration-300 ease-out"
                        enter-from-class="opacity-0 scale-90"
                        enter-to-class="opacity-100 scale-100"
                        leave-active-class="transition duration-200 ease-in"
                        leave-from-class="opacity-100 scale-100"
                        leave-to-class="opacity-0 scale-90"
                        appear
                    >
                        <div
                            v-if="showModal && modalLieu"
                            class="w-full max-w-lg rounded-3xl bg-white p-10 shadow-2xl border border-white/60"
                            role="dialog"
                            aria-modal="true"
                        >
                            <div
                                v-if="modalLieu.type === 'success'"
                                class="text-center mb-6"
                            >
                                <div class="text-7xl mb-4 animate-bounce">🎉</div>
                                <h2 class="text-3xl font-black bg-gradient-to-r from-green-600 to-emerald-700 bg-clip-text text-transparent">
                                    Félicitations !
                                </h2>
                                <p class="text-sm text-gray-500 mt-3">
                                    Vous avez trouvé le bon endroit.
                                </p>
                            </div>

                            <div
                                v-else
                                class="text-center mb-6"
                            >
                                <div class="text-7xl mb-4">📍</div>
                                <h2 class="text-3xl font-black text-gray-800">
                                    Le lieu à découvrir
                                </h2>
                                <p class="text-sm text-gray-500 mt-3">
                                    Énigme marquée comme non résolue.
                                </p>
                            </div>

                            <div class="rounded-2xl bg-gradient-to-br from-indigo-50 to-purple-50 border border-indigo-100 p-6 space-y-3">
                                <h3 class="text-xl font-bold text-gray-900">
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
                                class="mt-8 w-full rounded-2xl bg-gradient-to-r from-indigo-600 to-purple-700 py-4 text-sm font-bold text-white hover:from-indigo-700 hover:to-purple-800 transition-all duration-300 shadow-xl hover:shadow-2xl hover:scale-105"
                            >
                                Continuer →
                            </button>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>

        <!-- Modal pour la fin de partie -->
        <Dialog v-model:visible="showGameEndModal" :modal="true" :closable="false" class="p-0">
            <div class="text-center p-10">
                <div class="text-8xl mb-6 animate-pulse">⏰</div>
                <h2 class="text-4xl font-black bg-gradient-to-r from-red-600 to-orange-600 bg-clip-text text-transparent mb-4">
                    Temps écoulé !
                </h2>
                <p class="text-lg text-gray-600 mb-8">
                    Votre partie est terminée.
                </p>
                <button
                    @click="retourDashboardApresFin"
                    class="rounded-2xl bg-gradient-to-r from-indigo-600 to-purple-700 px-10 py-4 text-white font-bold text-lg shadow-xl hover:scale-105 transition-all duration-300"
                >
                    Retour au dashboard
                </button>
            </div>
        </Dialog>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Styles spécifiques pour Leaflet et boutons */
#play-map {
    z-index: 0;
}
.gps-validate-btn:hover:not(:disabled) {
    background: linear-gradient(to right, #15803d, #065f46);
}
</style>
