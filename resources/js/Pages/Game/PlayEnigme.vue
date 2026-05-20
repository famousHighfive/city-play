<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import L from 'leaflet';

const props = defineProps({
    game: Object,
    enigme: Object,
    progression: Object,
    labels: Object,
});

const mapContainer = ref(null);
const mapInstance = ref(null);
const playerMarker = ref(null);
const validationMessage = ref(null);
const validationOk = ref(null);
const gpsLoading = ref(false);

let targetMarker = null;
let radiusCircle = null;

const cibleLat = computed(() => Number(props.enigme.place.latitude));
const cibleLng = computed(() => Number(props.enigme.place.longitude));
const rayon = computed(() => Number(props.enigme.place.rayon_validation ?? 30));

const locomotionLabel = computed(
    () => props.labels.moyens_locomotion[props.game.moyen_locomotion] ?? props.game.moyen_locomotion
);

const difficulteLabel = computed(
    () => props.labels.niveaux_difficulte[props.game.niveau_difficulte] ?? props.game.niveau_difficulte
);

const modeLabel = computed(
    () => props.labels.modes[props.game.mode_jeu] ?? props.game.mode_jeu
);

const demanderIndice = () => {
    router.post(route('game.indice', [props.game.id, props.enigme.id]));
};

const voirSolution = () => {
    if (confirm('Êtes-vous sûr ? Découvrir la solution marquera l\'énigme comme non résolue.')) {
        router.post(route('game.solution', [props.game.id, props.enigme.id]));
    }
};

const passerSuivant = () => {
    router.post(route('game.skip', [props.game.id, props.enigme.id]));
};

const distanceMetres = (lat1, lon1, lat2, lon2) => {
    const R = 6371000;
    const toRad = (deg) => (deg * Math.PI) / 180;
    const dLat = toRad(lat2 - lat1);
    const dLon = toRad(lon2 - lon1);
    const a =
        Math.sin(dLat / 2) ** 2 +
        Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLon / 2) ** 2;

    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
};

const afficherPositionJoueur = (lat, lng, distance) => {
    if (!mapInstance.value) {
        return;
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

    const bounds = L.latLngBounds([
        [cibleLat.value, cibleLng.value],
        [lat, lng],
    ]);
    mapInstance.value.fitBounds(bounds.pad(0.25));

    validationOk.value = distance <= rayon.value;
    validationMessage.value = validationOk.value
        ? `Bonne position ! Vous êtes à ${Math.round(distance)} m du lieu.`
        : `Vous êtes à ${Math.round(distance)} m du lieu (rayon autorisé : ${rayon.value} m). Rapprochez-vous.`;
};

const initMap = () => {
    if (!mapContainer.value || mapInstance.value) {
        return;
    }

    mapInstance.value = L.map(mapContainer.value).setView([cibleLat.value, cibleLng.value], 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(mapInstance.value);

    targetMarker = L.circleMarker([cibleLat.value, cibleLng.value], {
        radius: 9,
        color: '#dc2626',
        fillColor: '#ef4444',
        fillOpacity: 1,
        weight: 2,
    })
        .addTo(mapInstance.value)
        .bindPopup(`Lieu : ${props.enigme.place.nom}`);

    radiusCircle = L.circle([cibleLat.value, cibleLng.value], {
        radius: rayon.value,
        color: '#16a34a',
        fillColor: '#22c55e',
        fillOpacity: 0.15,
        weight: 2,
    }).addTo(mapInstance.value);
};

const validerPosition = () => {
    if (!navigator.geolocation) {
        alert('La géolocalisation n’est pas disponible.');
        return;
    }

    gpsLoading.value = true;
    validationMessage.value = null;

    navigator.geolocation.getCurrentPosition(
        (position) => {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            const distance = distanceMetres(lat, lng, cibleLat.value, cibleLng.value);

            afficherPositionJoueur(lat, lng, distance);
            gpsLoading.value = false;
        },
        () => {
            gpsLoading.value = false;
            alert('Impossible de récupérer votre position.');
        },
        { enableHighAccuracy: true, timeout: 15000 }
    );
};

onMounted(() => {
    initMap();
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
                    <p class="text-xs uppercase tracking-wider font-semibold opacity-75">
                        Étape {{ progression.etape }} / {{ progression.total }}
                    </p>
                    <h1 class="text-xl font-bold">Lieu : {{ enigme.place.nom }}</h1>
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
                        <h3 class="text-gray-500 text-sm font-medium">Votre Mission :</h3>
                        <p class="text-lg text-gray-800 font-serif italic">" {{ enigme.texte }} "</p>
                    </div>

                    <!-- Carte Leaflet : lieu cible + zone de validation -->
                    <div class="border-t border-gray-100 pt-4 space-y-3">
                        <h4 class="text-sm font-semibold text-gray-700">
                            Localisation du lieu
                        </h4>
                        <p class="text-xs text-gray-500">
                            Cercle vert = zone de validation ({{ rayon }} m). Validez votre position GPS sur la carte.
                        </p>
                        <div
                            id="play-map"
                            ref="mapContainer"
                            class="h-72 w-full rounded-xl border border-gray-200 z-0"
                        />
                        <p
                            v-if="validationMessage"
                            class="text-sm rounded-lg px-4 py-3"
                            :class="validationOk
                                ? 'bg-green-50 text-green-800 border border-green-200'
                                : 'bg-amber-50 text-amber-900 border border-amber-200'"
                        >
                            {{ validationMessage }}
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

                    <div class="border-t border-gray-100 pt-4 space-y-3">
                        <div
                            v-if="enigme.pivot.solution_affichee"
                            class="bg-red-50 border-l-4 border-red-500 p-4 rounded text-red-900"
                        >
                            <h4 class="font-bold">Solution révélée :</h4>
                            <p class="mt-1 text-sm font-mono">{{ enigme.solution }}</p>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-4 pt-4">
                            <button
                                v-if="!enigme.pivot.solution_affichee"
                                @click="voirSolution"
                                class="text-sm text-red-600 hover:underline"
                            >
                                Abandonner et voir la solution
                            </button>

                            <button
                                v-if="enigme.pivot.solution_affichee || enigme.pivot.statut === 'resolue'"
                                @click="passerSuivant"
                                class="bg-gray-800 text-white text-sm px-5 py-2 rounded-md shadow hover:bg-gray-700 font-medium"
                            >
                                Passer à l'étape suivante →
                            </button>

                            <button
                                v-else
                                type="button"
                                :disabled="gpsLoading"
                                @click="validerPosition"
                                class="gps-validate-btn bg-green-600 text-white text-sm px-5 py-2.5 rounded-md shadow font-semibold disabled:opacity-60"
                            >
                                {{
                                    gpsLoading
                                        ? 'Localisation...'
                                        : '📍 Valider ma position GPS'
                                }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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

.gps-validate-btn:hover {
    background-color: #15803d;
}
</style>
