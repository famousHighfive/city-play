<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import { useAudioStore } from '@/stores/audio';
import GameMap from '@/Components/GameMap.vue';

const props = defineProps({
    game: Object,
    parcours: Array,
});

const audioStore = useAudioStore();

const isMuted = ref(false);
const toggleMute = () => {
    isMuted.value = !isMuted.value;
    audioStore.isMuted = isMuted.value;
};

const currentPhase = ref('loading');
const showContinueButton = ref(false);
const loadingProgress = ref(0);
const currentLoadingMessage = ref('Initialisation du système d’exploration...');
const firstPlaceActive = ref(false);
let loadingInterval = null;
let messageInterval = null;

const loadingMessages = [
    'Initialisation du système d’exploration...',
    'Analyse des itinéraires disponibles...',
    'Synchronisation des points d’intérêt...',
    'Lecture des zones géographiques...',
    'Préparation des indices et énigmes...',
    'Calibration de la difficulté...',
    'Détection des conditions environnementales...',
    'Optimisation du parcours...',
    'Génération de votre aventure personalisée...',
    'Préparation terminée !'
];

const weatherData = {
    icon: '☀️',
    condition: 'Conditions favorables',
    temp: '24°C',
    ambiance: 'Ciel dégagé, parfait pour l’exploration'
};

const showLoading = () => {
    loadingProgress.value = 0;
    currentLoadingMessage.value = loadingMessages[0];
    
    loadingInterval = setInterval(() => {
        loadingProgress.value = Math.min(loadingProgress.value + Math.random() * 15, 100);
    }, 300);
    
    let messageIndex = 1;
    messageInterval = setInterval(() => {
        if (messageIndex < loadingMessages.length) {
            currentLoadingMessage.value = loadingMessages[messageIndex];
            messageIndex++;
        }
    }, 1200);
    
    audioStore.play('notification');
};

const startGame = () => {
    audioStore.play('success');
    router.visit(route('game.show', props.game.id));
};

onMounted(async () => {
    showLoading();
    
    setTimeout(async () => {
        clearInterval(loadingInterval);
        clearInterval(messageInterval);
        loadingProgress.value = 100;
        
        await nextTick();
        
        setTimeout(() => {
            currentLoadingMessage.value = 'Préparation terminée !';
            showContinueButton.value = true;
        }, 500);
    }, 10000);
});

const handleContinue = () => {
    currentPhase.value = 'map';
    nextTick(() => {
        setTimeout(() => {
            firstPlaceActive.value = true;
        }, 1000);
    });
};

onUnmounted(() => {
    if (loadingInterval) clearInterval(loadingInterval);
    if (messageInterval) clearInterval(messageInterval);
});
</script>

<template>
    <div class="fixed inset-0 z-[9999] overflow-hidden">
        <!-- Loading Phase -->
        <Transition name="fade" mode="out-in">
            <div v-if="currentPhase === 'loading'" 
                 class="absolute inset-0 bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 flex flex-col items-center justify-center">
                <!-- Background effects -->
                <div class="absolute inset-0 overflow-hidden">
                    <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl animate-pulse"></div>
                    <div class="absolute bottom-1/4 right-1/4 w-80 h-80 bg-emerald-500/10 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s"></div>
                </div>
                
                <div class="relative z-10 flex flex-col items-center max-w-lg px-8">
                    <!-- Logo/Icon -->
                    <div class="mb-8">
                        <div class="w-24 h-24 rounded-3xl bg-gradient-to-br from-indigo-600 to-emerald-500 flex items-center justify-center shadow-2xl shadow-indigo-500/30 animate-bounce">
                            <span class="text-5xl">🗺️</span>
                        </div>
                    </div>
                    
                    <!-- Title -->
                    <h1 class="text-3xl font-black text-white mb-2 tracking-tight">CityPlay</h1>
                    <p class="text-indigo-300/80 text-sm mb-12 uppercase tracking-widest">Préparation de l'expédition</p>
                    
                    <!-- Loader -->
                    <div class="w-full mb-8">
                        <div class="relative h-3 bg-slate-800 rounded-full overflow-hidden">
                            <div 
                                class="absolute inset-y-0 left-0 bg-gradient-to-r from-indigo-500 via-purple-500 to-emerald-500 rounded-full transition-all duration-300 ease-out"
                                :style="{ width: loadingProgress + '%' }"
                            ></div>
                        </div>
                        <div class="mt-3 text-right text-xs text-indigo-400 font-mono">{{ Math.round(loadingProgress) }}%</div>
                    </div>
                    
                    <!-- Loading message -->
                    <div class="h-16 flex items-center justify-center text-center">
                        <p v-if="!showContinueButton" class="text-lg text-slate-200 font-medium animate-pulse">{{ currentLoadingMessage }}</p>
                        
                        <button 
                            v-else
                            @click="handleContinue"
                            class="group relative px-8 py-4 bg-gradient-to-r from-indigo-600 to-emerald-500 rounded-2xl text-white font-bold text-lg shadow-2xl hover:scale-105 transition-all duration-300 flex items-center gap-3 overflow-hidden"
                        >
                            <span class="relative z-10">Cliquer pour continuer</span>
                            <span class="relative z-10 animate-bounce">👉</span>
                            <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Map Phase -->
        <Transition name="fade" mode="out-in">
            <div v-if="currentPhase === 'map'" class="absolute inset-0 bg-slate-900">
                <!-- Map -->
                <GameMap 
                    :parcours="parcours" 
                    :show-player-location="true"
                    :current-place-index="0"
                    @place-click="startGame"
                />
                
                <!-- Overlay UI -->
                <div class="absolute inset-0 pointer-events-none">
                    <!-- Top bar -->
                    <div class="absolute top-0 left-0 right-0 p-6 flex items-center justify-between pointer-events-auto">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-slate-800/80 backdrop-blur-xl flex items-center justify-center shadow-xl border border-slate-700/50">
                                <span class="text-2xl">🗺️</span>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-white">Votre parcours</h2>
                                <p class="text-sm text-slate-400">{{ parcours.length }} lieux à découvrir</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Weather panel -->
                    <div class="absolute top-6 right-6 pointer-events-auto">
                        <div class="bg-slate-800/90 backdrop-blur-xl rounded-2xl p-5 shadow-2xl border border-white/10 min-w-[200px]">
                            <div class="flex items-center gap-3 mb-3">
                                <span class="text-4xl">{{ weatherData.icon }}</span>
                                <div>
                                    <p class="text-white font-semibold">{{ weatherData.condition }}</p>
                                    <p class="text-2xl font-bold text-indigo-400">{{ weatherData.temp }}</p>
                                </div>
                            </div>
                            <p class="text-xs text-slate-400 leading-relaxed">{{ weatherData.ambiance }}</p>
                        </div>
                    </div>

                    <!-- Sound control button (Bottom Right) -->
                    <div class="absolute bottom-28 right-6 pointer-events-auto">
                        <button 
                            @click="toggleMute"
                            class="w-16 h-16 rounded-full bg-slate-800/90 backdrop-blur-xl border border-white/10 flex items-center justify-center text-3xl shadow-2xl hover:scale-110 transition-transform active:scale-95"
                            :title="isMuted ? 'Activer le son' : 'Couper le son'"
                        >
                            <span>{{ isMuted ? '🔇' : '🔊' }}</span>
                        </button>
                    </div>
                    
                    <!-- Bottom instruction -->
                    <div class="absolute inset-0 flex items-center justify-center p-8 pointer-events-none">
                        <div class="max-w-md w-full pointer-events-auto">
                            <Transition name="slide-up">
                                <div v-if="firstPlaceActive" 
                                     class="bg-slate-900/90 backdrop-blur-xl rounded-3xl p-8 shadow-2xl border border-white/10 text-center">
                                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-indigo-600 to-emerald-500 flex items-center justify-center shadow-2xl mx-auto mb-6">
                                        <span class="text-4xl animate-pulse">🎯</span>
                                    </div>
                                    <h3 class="text-2xl font-black text-white mb-3">Bienvenue sur votre trajet !</h3>
                                    <p class="text-slate-300 mb-8 leading-relaxed">Voici le parcours que vous allez découvrir. Cliquez sur le premier lieu déverrouillé (point lumineux) pour lancer l'aventure.</p>
                                    <button 
                                        @click="startGame"
                                        class="w-full px-8 py-4 bg-gradient-to-r from-indigo-600 to-emerald-500 text-white rounded-2xl font-black text-lg hover:scale-105 transition-all shadow-xl shadow-indigo-500/20"
                                    >
                                        Commencer l'exploration →
                                    </button>
                                </div>
                            </Transition>
                        </div>
                    </div>
                </div>

            </div>
        </Transition>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.slide-up-enter-active {
    transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}
.slide-up-enter-from {
    opacity: 0;
    transform: translateY(30px);
}
</style>
