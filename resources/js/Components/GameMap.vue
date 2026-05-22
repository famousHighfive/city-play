<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import L from 'leaflet';

const props = defineProps({
    parcours: {
        type: Array,
        default: () => []
    },
    showPlayerLocation: {
        type: Boolean,
        default: false
    },
    currentPlaceIndex: {
        type: Number,
        default: 0
    }
});

const emit = defineEmits(['place-click']);

const mapContainer = ref(null);
let mapInstance = null;
let markers = [];
let playerMarker = null;
let routePolyline = null;
let watchId = null;

const initMap = () => {
    if (!mapContainer.value) return;
    
    const centerLat = props.parcours.length > 0 
        ? props.parcours[0].latitude 
        : 6.370293;
    const centerLng = props.parcours.length > 0 
        ? props.parcours[0].longitude 
        : 2.391236;
        
    mapInstance = L.map(mapContainer.value, { 
        zoomControl: false,
        attributionControl: false
    }).setView([centerLat, centerLng], 14);
    
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_nolabels/{z}/{x}/{y}{r}.png', {
        maxZoom: 19
    }).addTo(mapInstance);
    
    renderParcours();
    if (props.showPlayerLocation) {
        startLocationTracking();
    }
};

const renderParcours = () => {
    markers.forEach(m => mapInstance.removeLayer(m));
    markers = [];
    
    if (routePolyline) {
        mapInstance.removeLayer(routePolyline);
    }
    
    const coords = [];
    
    props.parcours.forEach((place, index) => {
        coords.push([place.latitude, place.longitude]);
        
        const isActive = index === props.currentPlaceIndex;
        const isCompleted = index < props.currentPlaceIndex;
        
        const marker = L.circleMarker([place.latitude, place.longitude], {
            radius: isActive ? 14 : (isCompleted ? 8 : 10),
            color: isActive ? '#10b981' : (isCompleted ? '#64748b' : '#6366f1'),
            fillColor: isActive ? '#10b981' : (isCompleted ? '#64748b' : '#6366f1'),
            fillOpacity: isActive ? 1 : 0.8,
            weight: isActive ? 4 : 2,
            className: isActive ? 'place-marker active' : 'place-marker'
        }).addTo(mapInstance);
        
        if (isActive) {
            marker.on('click', () => {
                emit('place-click', index);
            });
        }
        
        markers.push(marker);
    });
    
    if (coords.length >= 2) {
        routePolyline = L.polyline(coords, {
            color: '#818cf8',
            weight: 3,
            opacity: 0.7,
            dashArray: '10, 10',
            lineJoin: 'round'
        }).addTo(mapInstance);
        
        const bounds = L.latLngBounds(coords);
        mapInstance.fitBounds(bounds, { padding: [50, 50] });
    }
};

const startLocationTracking = () => {
    if (!navigator.geolocation) return;
    
    watchId = navigator.geolocation.watchPosition(
        (position) => {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            
            if (playerMarker) {
                playerMarker.setLatLng([lat, lng]);
            } else {
                playerMarker = L.circleMarker([lat, lng], {
                    radius: 12,
                    color: '#ef4444',
                    fillColor: '#ef4444',
                    fillOpacity: 1,
                    weight: 3,
                    className: 'player-marker'
                }).addTo(mapInstance).bindPopup('Vous êtes ici');
            }
        },
        null,
        { enableHighAccuracy: true, timeout: 15000, maximumAge: 5000 }
    );
};

watch(() => props.parcours, () => {
    if (mapInstance) {
        renderParcours();
    }
}, { deep: true });

watch(() => props.currentPlaceIndex, () => {
    if (mapInstance) {
        renderParcours();
    }
});

onMounted(() => {
    initMap();
});

onUnmounted(() => {
    if (watchId) {
        navigator.geolocation.clearWatch(watchId);
    }
    if (mapInstance) {
        mapInstance.remove();
    }
});
</script>

<template>
    <div ref="mapContainer" class="w-full h-full"></div>
</template>

<style scoped>
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

.player-marker {
    transition: all 0.5s ease;
}
</style>
