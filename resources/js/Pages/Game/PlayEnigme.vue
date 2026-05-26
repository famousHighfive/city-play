<script setup>
import { ref, computed, nextTick, onMounted, onUnmounted, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { useGameStore } from '@/stores/game';
import { useAudioStore } from '@/stores/audio';
import ConfirmDialog from 'primevue/confirmdialog';
import { useConfirm } from 'primevue/useconfirm';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import Dialog from 'primevue/dialog';
import GameMap from '@/Components/GameMap.vue';

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
    parcours: {
        type: Array,
        default: () => []
    },
    currentPlaceIndex: {
        type: Number,
        default: 0
    }
});

const gameStore = useGameStore();
const audioStore = useAudioStore();
const confirm = useConfirm();
const toast = useToast();

const showPlan = ref(false);
const showModal = ref(false);
const modalLieu = ref(null);
const xpGagnes = ref(0);
const showGameEndModal = ref(false);
const gpsLoading = ref(false);
const indiceLoading = ref(false);
const solutionLoading = ref(false);
const skipLoading = ref(false);
const gpsError = ref(props.gps_error);
const page = usePage();

// Formatage du temps pour l'affichage (ex: "15 min 04s")
const timerDisplay = computed(() => {
    const totalSeconds = gameStore.remainingSeconds;
    if (totalSeconds <= 0) return "00:00:00";
    
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;
    
    return [
        hours.toString().padStart(2, '0'),
        minutes.toString().padStart(2, '0'),
        seconds.toString().padStart(2, '0')
    ].join(':');
});

const locomotionLabel = computed(() =>
    props.labels?.moyens_locomotion?.[props.game.moyen_locomotion] ?? props.game.moyen_locomotion
);

const difficulteLabel = computed(() =>
    props.labels?.niveaux_difficulte?.[props.game.niveau_difficulte] ?? props.game.niveau_difficulte
);

const modeLabel = computed(() =>
    props.labels?.modes?.[props.game.mode_jeu] ?? props.game.mode_jeu
);

const lieuValidation = computed(() => props.enigme.place || props.enigme.lieu_validation);

// Lieux conseillés autour de la place (modal GPS / solution)
const recommandationsModal = computed(() => {
    const list = modalLieu.value?.recommandation;
    return Array.isArray(list) ? list.filter((r) => r?.nom?.trim()) : [];
});

// Numéro « plus d'infos » (ressource du lieu)
const ressourceModal = computed(() => modalLieu.value?.ressource?.trim() || null);

const telLienModal = computed(() => {
    if (!ressourceModal.value) return null;
    const chiffres = ressourceModal.value.replace(/[^\d+]/g, '');
    return chiffres ? `tel:${chiffres}` : null;
});
const cibleLat = computed(() => Number(lieuValidation.value?.latitude ?? 0));
const cibleLng = computed(() => Number(lieuValidation.value?.longitude ?? 0));
const rayon = computed(() => Number(lieuValidation.value?.rayon_validation ?? 30));

const ouvrirModal = async (data) => {
    if (!data?.nom) return;
    modalLieu.value = data;
    xpGagnes.value = data.xp_gagnes || 0;
    await nextTick();
    showModal.value = true;
    if (data.type === 'success') {
        audioStore.play('success');
        if (xpGagnes.value > 0) {
            toast.add({
                severity: 'success',
                summary: 'Félicitations !',
                detail: `Lieu découvert. +${xpGagnes.value} XP`,
                life: 4000
            });
        }
    } else {
        audioStore.play('notification');
    }
};

const fermerModal = () => {
    showModal.value = false;
};

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

const handleTimeUp = () => {
    showGameEndModal.value = true;
    audioStore.play('notification');
    // Forcer la clôture côté serveur
    router.post(route('game.force-end', props.game.id));
};

const routeParams = () => ({
    game: props.game.id,
    enigme: props.enigme.id,
});

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

const demanderIndice = () => {
    audioStore.play('click');
    indiceLoading.value = true;
    router.post(route('game.indice', routeParams()), {}, { 
        preserveScroll: true,
        onFinish: () => { indiceLoading.value = false; }
    });
};

const voirSolution = () => {
    confirm.require({
        message: 'Voulez-vous vraiment voir la solution ? Cela marquera l\'énigme comme non résolue.',
        header: 'Confirmation',
        icon: 'pi pi-exclamation-triangle',
        accept: () => {
            audioStore.play('click');
            solutionLoading.value = true;
            router.post(route('game.solution', routeParams()), {}, {
                onFinish: () => { solutionLoading.value = false; }
            });
        },
        reject: () => {
            audioStore.play('click');
        }
    });
};

const passerSuivant = () => {
    audioStore.play('click');
    skipLoading.value = true;
    router.post(route('game.skip', routeParams()), {}, {
        onFinish: () => { skipLoading.value = false; }
    });
};

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

onMounted(() => {
    // Toujours s'assurer que le timer est synchronisé avec l'ID de la partie actuelle
    if (gameStore.gameId !== props.game.id) {
        gameStore.initializeGame(
            props.game.id,
            props.game.duree_restante
        );
    } else {
        // Si on revient sur la même partie, on s'assure qu'elle n'est pas en pause localement
        // si le statut backend est "en_cours"
        if (props.game.statut === 'en_cours') {
            gameStore.isPaused = false;
        }
        gameStore.startTimer();
    }

    window.addEventListener('game-time-up', handleTimeUp);
});

onUnmounted(() => {
    window.removeEventListener('game-time-up', handleTimeUp);
});

const peutValiderGps = computed(() => props.enigme.pivot.statut === 'en_cours' && !props.enigme.pivot.solution_affichee);
const peutVoirSolution = computed(() => props.enigme.pivot.statut === 'en_cours' && !props.enigme.pivot.solution_affichee);
const peutPasserSuivant = computed(() => props.enigme.pivot.statut !== 'en_cours' || props.enigme.pivot.solution_affichee);

const backgroundImage = computed(() => {
    if (props.enigme.image) {
        return `/storage/${props.enigme.image}`;
    }
    return 'https://media4.giphy.com/media/v1.Y2lkPTc5MGI3NjExNjducnN2aGY3cDZuZ2NicHNzMnlmOW1mcWZ1NnV0NWx5bXlxcTcyNSZlcD12MV9pbnRlcm5hbF9naWZfYnlfaWQmY3Q9Zw/l41lHnkHmdFgjUnmw/giphy.gif';
});
</script>

<template>
    <Head :title="`Partie - ${game.environment?.nom ?? 'City Play'}`" />

    <AuthenticatedLayout>
        <div 
            class="min-h-screen bg-cover bg-center bg-fixed relative"
            :style="{ backgroundImage: `url(${backgroundImage})` }"
        >
            <!-- Overlay pour la lisibilité -->
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-[2px]"></div>

            <div class="relative z-10 py-12">
                <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Header -->
                <div class="flex items-center justify-between bg-white/80 backdrop-blur-md p-6 rounded-3xl shadow-lg border border-white/20">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-600 to-purple-600 flex items-center justify-center shadow-lg">
                            <span class="text-2xl">🎮</span>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">
                                Énigme {{ progression.etape }} / {{ progression.total }}
                            </h1>
                            <div class="flex items-center gap-3 text-sm text-gray-500 mt-1">
                                <span class="font-semibold text-indigo-600">{{ modeLabel }}</span>
                                <span>·</span>
                                <span>{{ difficulteLabel }}</span>
                                <span>·</span>
                                <span>{{ locomotionLabel }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <button
                            @click="showPlan = true"
                            class="px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-gray-700 font-semibold text-sm hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm flex items-center gap-2"
                        >
                            <span>🗺️</span>
                            Plan
                        </button>
                        <button
                            @click="mettreEnPause"
                            class="px-4 py-2.5 bg-amber-50 border border-amber-200 rounded-xl text-amber-700 font-semibold text-sm hover:bg-amber-100 transition-all flex items-center gap-2"
                        >
                            <span>⏸️</span>
                            Pause
                        </button>
                    </div>
                </div>

                <!-- Timer -->
                <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-lg border border-white/20 p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-orange-500 to-red-500 flex items-center justify-center">
                                <span class="text-2xl">⏱️</span>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Temps restant</p>
                                <p class="text-3xl font-black bg-gradient-to-r from-orange-600 to-red-600 bg-clip-text text-transparent">
                                    {{ timerDisplay }}
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-4">
                            <div v-if="game.mode_jeu === 'equipe'" class="text-center">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Équipe</p>
                                <p class="text-xl font-bold text-gray-800">{{ game.nb_membres }} membres</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enigma Card -->
                <div class="bg-white/90 backdrop-blur-md rounded-3xl shadow-xl border border-white/20 overflow-hidden">
                    <div class="p-8 space-y-8">
                        <!-- Enigma Text -->
                        <div>
                            <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wider mb-3">
                                Votre énigme
                            </p>
                            <p class="text-2xl text-gray-800 font-serif italic leading-relaxed">
                                "{{ enigme.texte }}"
                            </p>
                        </div>

                        <!-- Hints Section -->
                        <div class="border-t border-gray-100 pt-6">
                            <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">
                                Indices demandés ({{ enigme.pivot.nb_indices_demandes }} / 2)
                            </h3>
                            
                            <div class="space-y-3">
                                <div
                                    v-if="enigme.pivot.nb_indices_demandes >= 1"
                                    class="bg-gradient-to-r from-amber-50 to-yellow-50 border-l-4 border-amber-500 p-5 rounded-xl"
                                >
                                    <p class="text-amber-800 font-semibold text-sm uppercase tracking-wider mb-2">Indice 1</p>
                                    <p class="text-amber-900">{{ enigme.indice_1 }}</p>
                                </div>

                                <div
                                    v-if="enigme.pivot.nb_indices_demandes >= 2"
                                    class="bg-gradient-to-r from-amber-50 to-yellow-50 border-l-4 border-amber-500 p-5 rounded-xl"
                                >
                                    <p class="text-amber-800 font-semibold text-sm uppercase tracking-wider mb-2">Indice 2</p>
                                    <p class="text-amber-900">{{ enigme.indice_2 || "Pas d'indice supplémentaire disponible." }}</p>
                                </div>
                            </div>

                            <button
                                v-if="enigme.pivot.nb_indices_demandes < 2 && !enigme.pivot.solution_affichee"
                                @click="demanderIndice"
                                :disabled="indiceLoading"
                                class="mt-4 px-6 py-3 bg-gradient-to-r from-amber-500 to-orange-500 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all disabled:opacity-60 flex items-center justify-center gap-2"
                            >
                                <span v-if="indiceLoading" class="animate-spin">⚙️</span>
                                <span v-else>💡</span>
                                {{ indiceLoading ? 'Chargement...' : 'Obtenir un indice' }}
                            </button>
                        </div>

                        <!-- Action Buttons -->
                        <div class="border-t border-gray-100 pt-6">
                            <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Actions</h3>
                            
                            <div class="flex flex-wrap gap-3">
                                <button
                                    v-if="peutValiderGps"
                                    @click="validerPosition"
                                    :disabled="gpsLoading"
                                    class="flex-1 min-w-[200px] px-6 py-4 bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-bold rounded-xl shadow-xl hover:shadow-2xl transition-all disabled:opacity-60 flex items-center justify-center gap-3"
                                >
                                    <span v-if="gpsLoading" class="animate-spin">🔍</span>
                                    <span v-else>📍</span>
                                    {{ gpsLoading ? 'Localisation en cours...' : 'Valider ma position GPS' }}
                                </button>

                                <button
                                    v-if="peutVoirSolution"
                                    @click="voirSolution"
                                    :disabled="solutionLoading"
                                    class="px-6 py-4 border-2 border-red-200 text-red-700 bg-gradient-to-r from-red-50 to-pink-50 font-semibold rounded-xl hover:from-red-100 hover:to-pink-100 transition-all disabled:opacity-60 flex items-center justify-center gap-2"
                                >
                                    <span v-if="solutionLoading" class="animate-spin">⚙️</span>
                                    Voir la solution
                                </button>

                                <button
                                    v-if="peutPasserSuivant"
                                    @click="passerSuivant"
                                    :disabled="skipLoading"
                                    class="px-6 py-4 bg-gradient-to-r from-gray-800 to-gray-900 text-white font-bold rounded-xl shadow-xl hover:shadow-2xl transition-all disabled:opacity-60 flex items-center justify-center gap-2"
                                >
                                    <span v-if="skipLoading" class="animate-spin">⚙️</span>
                                    Énigme suivante →
                                </button>
                            </div>
                        </div>

                        <!-- GPS Error -->
                        <div
                            v-if="gpsError"
                            class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-xl p-5"
                        >
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">⚠️</span>
                                <span class="font-semibold text-amber-900">{{ gpsError }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PrimeVue Components -->
        <ConfirmDialog />
        <Toast position="top-right" />

        <!-- Plan Modal -->
        <Dialog v-model:visible="showPlan" :modal="true" :closable="true" class="p-0" :style="{ width: '90vw', height: '85vh' }">
            <div class="h-full flex flex-col bg-slate-900">
                <div class="p-4 bg-slate-800 border-b border-slate-700 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">🗺️</span>
                        <h2 class="text-xl font-bold text-white">Plan du parcours</h2>
                    </div>
                    <button
                        @click="showPlan = false"
                        class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg font-semibold text-sm transition-colors"
                    >
                        Fermer
                    </button>
                </div>
                <div class="flex-1">
                    <GameMap 
                        :parcours="parcours" 
                        :show-player-location="true"
                        :current-place-index="currentPlaceIndex"
                    />
                </div>
            </div>
        </Dialog>

        <!-- Place Modal -->
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
                            class="w-full max-w-lg rounded-3xl bg-white p-10 shadow-2xl"
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
                                <div v-if="xpGagnes > 0" class="mt-2 inline-flex items-center gap-1.5 px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-black animate-bounce">
                                    ✨ +{{ xpGagnes }} XP gagnés
                                </div>
                                <p class="text-sm text-gray-500 mt-2">
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

                            <div class="mt-6 space-y-4">
                            <!-- Lieu découvert -->
                            <div class="rounded-2xl bg-gradient-to-br from-indigo-50 to-purple-50 border border-indigo-100 p-6">
                                <h3 class="text-xl font-bold text-gray-900">
                                    {{ modalLieu.nom }}
                                </h3>
                                <p
                                    v-if="modalLieu.description"
                                    class="text-sm text-gray-600 leading-relaxed mt-2"
                                >
                                    {{ modalLieu.description }}
                                </p>
                                <p
                                    v-else
                                    class="text-sm text-gray-400 italic mt-2"
                                >
                                    Aucune description disponible pour ce lieu.
                                </p>
                            </div>

                            <!-- Lieux recommandés à proximité (toujours visible) -->
                            <div class="rounded-2xl border border-emerald-100 bg-emerald-50/80 p-5 space-y-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-lg" aria-hidden="true">🗺️</span>
                                    <h4 class="text-sm font-black text-emerald-800 uppercase tracking-wider">
                                        Lieux recommandés à proximité
                                    </h4>
                                </div>
                                <p class="text-xs text-emerald-700/90">
                                    D'autres endroits à découvrir autour de ce lieu :
                                </p>
                                <ul v-if="recommandationsModal.length" class="space-y-2">
                                    <li
                                        v-for="(rec, i) in recommandationsModal"
                                        :key="i"
                                        class="text-sm bg-white rounded-xl px-4 py-3 border border-emerald-100 shadow-sm"
                                    >
                                        <span class="font-semibold text-gray-900">{{ rec.nom }}</span>
                                        <span
                                            v-if="rec.description"
                                            class="block text-gray-500 text-xs mt-1 leading-relaxed"
                                        >
                                            {{ rec.description }}
                                        </span>
                                    </li>
                                </ul>
                                <p v-else class="text-sm text-emerald-800/60 italic">
                                    Aucun lieu recommandé pour le moment.
                                </p>
                            </div>

                            <!-- Plus d'infos (contact cliquable) -->
                            <div
                                v-if="ressourceModal && telLienModal"
                                class="rounded-2xl border border-sky-100 bg-sky-50/90 p-5 space-y-2"
                            >
                                <div class="flex items-center gap-2">
                                    <span class="text-lg" aria-hidden="true">ℹ️</span>
                                    <h4 class="text-sm font-black text-sky-800 uppercase tracking-wider">
                                        Plus d'infos
                                    </h4>
                                </div>
                                <p class="text-xs text-sky-700/90">
                                    Besoin d'aide ou d'un renseignement ? Contactez :
                                </p>
                                <a
                                    :href="telLienModal"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white border border-sky-200 px-4 py-3 text-base font-bold text-sky-900 shadow-sm hover:bg-sky-100 hover:border-sky-300 transition active:scale-[0.98]"
                                >
                                    <span aria-hidden="true">📞</span>
                                    {{ ressourceModal }}
                                </a>
                            </div>
                            </div>

                            <button
                                type="button"
                                @click="fermerModal"
                                class="mt-8 w-full rounded-2xl bg-gradient-to-r from-indigo-600 to-purple-700 py-4 text-sm font-bold text-white hover:from-indigo-700 hover:to-purple-800 transition-all shadow-xl hover:shadow-2xl hover:scale-[1.02]"
                            >
                                Continuer →
                            </button>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>

        <!-- Game End Modal -->
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
                    class="rounded-2xl bg-gradient-to-r from-indigo-600 to-purple-700 px-10 py-4 text-white font-bold text-lg shadow-xl hover:scale-105 transition-all"
                >
                    Retour au dashboard
                </button>
            </div>
        </Dialog>
    </AuthenticatedLayout>
</template>
