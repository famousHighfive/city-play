<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { useAudioStore } from '@/stores/audio';
import L from 'leaflet';

const props = defineProps({
    gameId: {
        type: Number,
        required: true
    },
    parcours: {
        type: Array,
        required: true
    }
});

const audioStore = useAudioStore();
const currentPhase = ref('loading'); // loading, map
const loadingProgress = ref(0);
const currentMessageIndex = ref(0);
const currentPlaceIndex = ref(0);
const mapContainer = ref(null);
let mapInstance = ref(null);
let markers = ref([]);

const loadingMessages = [
    "Initialisation du système d'exploration...",
    "Analyse de la zone géographique...",
    "Synchronisation des énigmes...",
    "Calcul du parcours optimal...",
    "Vérification des points d'intérêt...",
    "Préparation de l'expérience immersive...",
    "Chargement des données cartographiques...",
    "Finalisation de l'expédition..."
];

const currentMessage = computed(() => loadingMessages[currentMessageIndex.value]);

let loadingInterval = null;
let messageInterval = null;
let placeRevealInterval = null;

onMounted(() => {
    // Start loading phase
    audioStore.play('notification');
    
    loadingInterval = setInterval(() => {
        loadingProgress.value += Math.random() * 10;
        if (loadingProgress.value >= 100) {
            loadingProgress.value = 100;
            clearInterval(loadingInterval);
            clearInterval(messageInterval);
            
            // Transition to map phase
            setTimeout(() => {
                currentPhase.value = 'map';
                audioStore.play('success');
                initMap();
                startPlaceReveal();
            }, 500);
        }
    }, 200);

    messageInterval = setInterval(() => {
        currentMessageIndex.value = (currentMessageIndex.value + 1) % loadingMessages.length;
    }, 2000);
});

onUnmounted(() => {
    if (loadingInterval) clearInterval(loadingInterval);
    if (messageInterval) clearInterval(messageInterval);
    if (placeRevealInterval) clearInterval(placeRevealInterval);
    if (mapInstance.value) mapInstance.value.remove();
});

const initMap = () => {
    if (!mapContainer.value) return;

    const firstPlace = props.parcours[0];
    mapInstance.value = L.map(mapContainer.value, {
        zoomControl: false,
        attributionControl: false,
        zoomSnap: 0.1
    }).setView([firstPlace.latitude, firstPlace.longitude], 14);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        maxZoom: 19
    }).addTo(mapInstance.value);
};

const startPlaceReveal = () => {
    placeRevealInterval = setInterval(() => {
        if (currentPlaceIndex.value < props.parcours.length) {
            revealPlace(props.parcours[currentPlaceIndex.value], currentPlaceIndex.value);
            currentPlaceIndex.value++;
        } else {
            clearInterval(placeRevealInterval);
        }
    }, 800);
};

const revealPlace = (place, index) => {
    if (!mapInstance.value) return;

    const isFirst = index === 0;
    const marker = L.circleMarker([place.latitude, place.longitude], {
        radius: isFirst ? 12 : 8,
        color: isFirst ? '#10b981' : '#4f46e5',
        fillColor: isFirst ? '#10b981' : '#4f46e5',
        fillOpacity: 0.8,
        weight: 3
    }).addTo(mapInstance.value);

    if (isFirst) {
        // Add pulsing effect for first marker
        const pulseCircle = L.circle([place.latitude, place.longitude], {
            radius: 20,
            color: '#10b981',
            fillColor: '#10b981',
            fillOpacity: 0.2,
            weight: 0
        }).addTo(mapInstance.value);

        let pulseRadius = 20;
        const pulseInterval = setInterval(() => {
            pulseRadius += 2;
            if (pulseRadius > 50) pulseRadius = 20;
            pulseCircle.setRadius(pulseRadius);
            pulseCircle.setStyle({ fillOpacity: 0.4 - (pulseRadius - 20) / 100 });
        }, 100);

        marker.on('click', () => {
            audioStore.play('click');
            startGame();
        });
    }

    markers.value.push(marker);

    // Fit bounds to include all markers so far
    if (markers.value.length > 0) {
        const group = L.featureGroup(markers.value);
        mapInstance.value.fitBounds(group.getBounds().pad(0.2));
    }
};

const startGame = () => {
    router.visit(route('game.show', props.gameId));
};
</script>

<template>
    <div class="fixed inset-0 z-[9999] bg-slate-900 overflow-hidden">
        <!-- Loading Phase -->
        <Transition
            enter-active-class="transition-opacity duration-1000"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-500"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="currentPhase === 'loading'" class="absolute inset-0 flex flex-col items-center justify-center">
                <!-- Animated Background -->
                <div class="absolute inset-0 overflow-hidden">
                    <div class="absolute -top-1/2 -left-1/2 w-full h-full bg-gradient-to-br from-indigo-900/30 to-purple-900/30 animate-pulse" />
                    <div class="absolute -bottom-1/2 -right-1/2 w-full h-full bg-gradient-to-tl from-emerald-900/30 to-cyan-900/30 animate-pulse" style="animation-delay: 1s" />
                </div>

                <!-- Logo/Icon -->
                <div class="relative mb-12">
                    <div class="w-24 h-24 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-2xl animate-bounce">
                        <span class="text-4xl">🗺️</span>
                    </div>
                    <div class="absolute inset-0 rounded-full border-4 border-indigo-400/30 animate-ping" />
                </div>

                <!-- Loading Text -->
                <div class="relative z-10 text-center mb-8">
                    <h2 class="text-3xl font-black text-white mb-4">Préparation de l'expédition</h2>
                    <p class="text-lg text-indigo-200 max-w-md mx-auto">{{ currentMessage }}</p>
                </div>

                <!-- Progress Bar -->
                <div class="relative z-10 w-full max-w-md px-8">
                    <div class="h-2 bg-slate-800 rounded-full overflow-hidden">
                        <div 
                            class="h-full bg-gradient-to-r from-indigo-500 via-purple-500 to-emerald-500 rounded-full transition-all duration-300"
                            :style="{ width: `${loadingProgress}%` }"
                        />
                    </div>
                    <p class="text-center text-sm text-slate-400 mt-2">{{ Math.round(loadingProgress) }}%</p>
                </div>
            </div>
        </Transition>

        <!-- Map Phase -->
        <Transition
            enter-active-class="transition-opacity duration-1000"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
        >
            <div v-if="currentPhase === 'map'" class="absolute inset-0 flex flex-col">
                <!-- Header -->
                <div class="relative z-10 bg-gradient-to-b from-slate-900/90 to-transparent p-6 text-center">
                    <h2 class="text-2xl font-black text-white mb-2">Votre parcours est prêt</h2>
                    <p class="text-indigo-200">Cliquez sur le point clignotant pour commencer</p>
                </div>

                <!-- Map Container -->
                <div class="flex-1 relative">
                    <div 
                        ref="mapContainer" 
                        class="absolute inset-0"
                        style="z-index: 0"
                    />
                </div>

                <!-- Weather Info -->
                <div class="relative z-10 bg-gradient-to-t from-slate-900/90 to-transparent p-6">
                    <div class="max-w-md mx-auto bg-slate-800/80 backdrop-blur-xl rounded-2xl p-6 border border-slate-700 shadow-2xl">
                        <div class="flex items-center gap-4">
                            <div class="text-4xl">🌤️</div>
                            <div>
                                <h3 class="font-bold text-white">Ambiance agréable</h3>
                                <p class="text-sm text-slate-400">Conditions idéales pour l'exploration</p>
                            </div>
                            <div class="ml-auto text-right">
                                <p class="text-2xl font-black text-emerald-400">24°C</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>
