<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import { useAudioStore } from '@/stores/audio';
import L from 'leaflet';

const props = defineProps({
    game: Object,
    parcours: Array,
});

const audioStore = useAudioStore();

const currentPhase = ref('loading');
const loadingProgress = ref(0);
const currentLoadingMessage = ref('Initialisation du système d’exploration...');
const revealedPlaces = ref([]);
const routeDrawn = ref(false);
const firstPlaceActive = ref(false);
const mapContainer = ref(null);
let mapInstance = null;
let markers = [];
let routePolyline = null;
let loadingInterval = null;
let messageInterval = null;
let revealInterval = null;

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

const initMap = () => {
    if (!mapContainer.value || !props.parcours.length) return;
    
    const firstPlace = props.parcours[0];
    mapInstance = L.map(mapContainer.value, { 
        zoomControl: false,
        attributionControl: false
    }).setView([firstPlace.latitude, firstPlace.longitude], 14);
    
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        maxZoom: 19
    }).addTo(mapInstance);
};

const revealPlaces = async () => {
    revealedPlaces.value = [];
    let index = 0;
    
    revealInterval = setInterval(() => {
        if (index < props.parcours.length) {
            const place = props.parcours[index];
            
            const marker = L.circleMarker([place.latitude, place.longitude], {
                radius: index === 0 ? 12 : 8,
                color: index === 0 ? '#10b981' : '#6366f1',
                fillColor: index === 0 ? '#10b981' : '#6366f1',
                fillOpacity: 0.8,
                weight: index === 0 ? 3 : 2,
                className: index === 0 ? 'place-marker active' : 'place-marker'
            }).addTo(mapInstance);
            
            if (index === 0) {
                marker.on('click', () => {
                    startGame();
                });
            }
            
            markers.push(marker);
            revealedPlaces.value.push({ ...place, index });
            
            if (index > 0) {
                drawRoute();
            }
            
            index++;
        } else {
            clearInterval(revealInterval);
            firstPlaceActive.value = true;
            if (markers[0]) {
                markers[0].setStyle({
                    radius: 14,
                    weight: 4
                });
            }
        }
    }, 800);
};

const drawRoute = () => {
    const coords = revealedPlaces.value.map(p => [p.latitude, p.longitude]);
    
    if (routePolyline) {
        mapInstance.removeLayer(routePolyline);
    }
    
    routePolyline = L.polyline(coords, {
        color: '#818cf8',
        weight: 3,
        opacity: 0.7,
        dashArray: '10, 10',
        lineJoin: 'round'
    }).addTo(mapInstance);
    
    if (coords.length >= 2) {
        const bounds = L.latLngBounds(coords);
        mapInstance.fitBounds(bounds, { padding: [50, 50] });
    }
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
            currentPhase.value = 'map';
            nextTick(() => {
                initMap();
                setTimeout(() => {
                    revealPlaces();
                }, 500);
            });
        }, 500);
    }, 10000);
});

onUnmounted(() => {
    if (loadingInterval) clearInterval(loadingInterval);
    if (messageInterval) clearInterval(messageInterval);
    if (revealInterval) clearInterval(revealInterval);
    if (mapInstance) mapInstance.remove();
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
                        <p class="text-lg text-slate-200 font-medium animate-pulse">{{ currentLoadingMessage }}</p>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Map Phase -->
        <Transition name="fade" mode="out-in">
            <div v-if="currentPhase === 'map'" class="absolute inset-0 bg-slate-900">
                <!-- Map container -->
                <div ref="mapContainer" class="absolute inset-0"></div>
                
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
                                <p class="text-sm text-slate-400">{{ revealedPlaces.length }} / {{ parcours.length }} lieux détectés</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Weather panel -->
                    <div class="absolute top-6 right-6 pointer-events-auto">
                        <div class="bg-slate-800/80 backdrop-blur-xl rounded-2xl p-5 shadow-xl border border-slate-700/50 min-w-[200px]">
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
                    
                    <!-- Bottom instruction -->
                    <div class="absolute bottom-0 left-0 right-0 p-8 pointer-events-auto">
                        <div class="max-w-2xl mx-auto">
                            <Transition name="slide-up">
                                <div v-if="firstPlaceActive" 
                                     class="bg-gradient-to-r from-emerald-600 to-teal-500 rounded-2xl p-6 shadow-2xl border border-emerald-400/30">
                                    <div class="flex items-center gap-4">
                                        <div class="w-14 h-14 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur">
                                            <span class="text-3xl animate-pulse">🎯</span>
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="text-xl font-bold text-white mb-1">Votre aventure commence !</h3>
                                            <p class="text-emerald-100/90">Cliquez sur le point lumineux pour commencer votre exploration.</p>
                                        </div>
                                        <div class="hidden sm:block">
                                            <div class="px-4 py-2 bg-white/20 rounded-lg backdrop-blur text-white font-semibold text-sm animate-bounce">
                                                →
                                            </div>
                                        </div>
                                    </div>
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

.place-marker {
    transition: all 0.3s ease;
}

.place-marker.active {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    }
    50% {
        box-shadow: 0 0 0 20px rgba(16, 185, 129, 0);
    }
}
</style>
