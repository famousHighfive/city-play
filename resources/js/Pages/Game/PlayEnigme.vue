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

const validationOk = ref(false);

const gpsLoading = ref(false);

const chrono = ref('00:00');

let targetMarker = null;

let radiusCircle = null;

let timerInterval = null;

/*
|--------------------------------------------------------------------------
| COORDONNEES DU LIEU
|--------------------------------------------------------------------------
*/

const cibleLat = computed(() =>
    Number(props.enigme.place.latitude)
);

const cibleLng = computed(() =>
    Number(props.enigme.place.longitude)
);

const rayon = computed(() =>
    Number(props.enigme.place.rayon_validation ?? 30)
);

const locomotionLabel = computed(() =>
    props.labels.moyens_locomotion[
        props.game.moyen_locomotion
    ] ?? props.game.moyen_locomotion
);

const difficulteLabel = computed(() =>
    props.labels.niveaux_difficulte[
        props.game.niveau_difficulte
    ] ?? props.game.niveau_difficulte
);

const modeLabel = computed(() =>
    props.labels.modes[
        props.game.mode_jeu
    ] ?? props.game.mode_jeu
);

const lancerChrono = () => {

    timerInterval = setInterval(() => {

        const debut = new Date(props.game.date_debut);

        const maintenant = new Date();

        const difference =
            Math.floor((maintenant - debut) / 1000);

        const minutes =
            Math.floor(difference / 60);

        const secondes =
            difference % 60;

        chrono.value =
            `${String(minutes).padStart(2, '0')}:${String(secondes).padStart(2, '0')}`;

    }, 1000);

};

/*
|--------------------------------------------------------------------------
| INITIALISATION CARTE
|--------------------------------------------------------------------------
*/

const initMap = () => {

    if (!mapContainer.value || mapInstance.value) {

        return;
    }

    mapInstance.value = L.map(
        mapContainer.value
    ).setView(
        [cibleLat.value, cibleLng.value],
        16
    );

    L.tileLayer(
        'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        {
            attribution:
                '&copy; OpenStreetMap contributors',
        }
    ).addTo(mapInstance.value);


    targetMarker = L.circleMarker(
        [cibleLat.value, cibleLng.value],
        {
            radius: 10,
            color: '#ef4444',
            fillColor: '#ef4444',
            fillOpacity: 1,
            weight: 3,
        }
    )
        .addTo(mapInstance.value)
        .bindPopup(props.enigme.place.nom);


    radiusCircle = L.circle(
        [cibleLat.value, cibleLng.value],
        {
            radius: rayon.value,
            color: '#22c55e',
            fillColor: '#22c55e',
            fillOpacity: 0.15,
            weight: 2,
        }
    ).addTo(mapInstance.value);

};

/*
|--------------------------------------------------------------------------
| AFFICHER POSITION JOUEUR
|--------------------------------------------------------------------------
*/

const afficherPositionJoueur = (
    lat,
    lng
) => {


    if (playerMarker.value) {

        mapInstance.value.removeLayer(
            playerMarker.value
        );
    }


    playerMarker.value = L.circleMarker(
        [lat, lng],
        {
            radius: 10,
            color: '#2563eb',
            fillColor: '#3b82f6',
            fillOpacity: 1,
            weight: 3,
        }
    )
        .addTo(mapInstance.value)
        .bindPopup('Votre position');

    /*
    |--------------------------------------------------------------------------
    | Zoom automatique
    |--------------------------------------------------------------------------
    */

    const bounds = L.latLngBounds([
        [cibleLat.value, cibleLng.value],
        [lat, lng],
    ]);

    mapInstance.value.fitBounds(
        bounds.pad(0.30)
    );

};

const validerPosition = () => {

    if (!navigator.geolocation) {

        alert('GPS indisponible');

        return;
    }

    gpsLoading.value = true;

    validationMessage.value = null;

    /*
    |--------------------------------------------------------------------------
    | Capture GPS
    |--------------------------------------------------------------------------
    */

    navigator.geolocation.getCurrentPosition(

        (position) => {

            const latitude =
                position.coords.latitude;

            const longitude =
                position.coords.longitude;

            /*
            |--------------------------------------------------------------------------
            | Affiche joueur sur carte
            |--------------------------------------------------------------------------
            */

            afficherPositionJoueur(
                latitude,
                longitude
            );

            /*
            |--------------------------------------------------------------------------
            | Appel backend Laravel
            |--------------------------------------------------------------------------
            */

            router.post(

                route(
                    'game.validate.position',
                    props.game.id
                ),

                {
                    latitude,
                    longitude,

                    enigme_id:
                        props.enigme.id,
                },

                {

                    preserveScroll: true,

                    onSuccess: (page) => {
                        validationOk.value = true;

                        validationMessage.value =
                            'Position validée avec succès 🎉';
                    },

                    onError: () => {

                        validationOk.value = false;

                        validationMessage.value =
                            'Vous êtes encore trop loin du lieu.';
                    },

                    onFinish: () => {

                        gpsLoading.value = false;
                    }

                }

            );

        },

        () => {

            gpsLoading.value = false;

            alert(
                'Impossible de récupérer votre position.'
            );

        },

        {
            enableHighAccuracy: true,
            timeout: 15000,
        }

    );

};


const demanderIndice = () => {

    router.post(
        route(
            'game.indice',
            [
                props.game.id,
                props.enigme.id
            ]
        )
    );

};


const voirSolution = () => {

    if (
        confirm(
            'Voir la solution ?'
        )
    ) {

        router.post(
            route(
                'game.solution',
                [
                    props.game.id,
                    props.enigme.id
                ]
            )
        );

    }

};

const passerSuivant = () => {

    router.post(
        route(
            'game.skip',
            [
                props.game.id,
                props.enigme.id
            ]
        )
    );

};



onMounted(() => {

    initMap();

    lancerChrono();

});


onUnmounted(() => {

    /*
    |--------------------------------------------------------------------------
    | Nettoyage map
    |--------------------------------------------------------------------------
    */

    if (mapInstance.value) {

        mapInstance.value.remove();

        mapInstance.value = null;
    }

    /*
    |--------------------------------------------------------------------------
    | Stop chrono
    |--------------------------------------------------------------------------
    */

    if (timerInterval) {

        clearInterval(timerInterval);
    }

});
</script>

<template>

    <Head :title="`Partie - ${game.environment?.nom}`" />

    <AuthenticatedLayout>

        <div class="min-h-screen bg-gradient-to-b from-slate-950 via-slate-900 to-slate-950 text-white">

            <!-- HEADER -->
            <div class="sticky top-0 z-30 backdrop-blur-xl bg-black/30 border-b border-white/10">

                <div class="max-w-7xl mx-auto px-4 py-4">

                    <div class="flex flex-col lg:flex-row gap-4 lg:items-center lg:justify-between">

                        <!-- TITRE -->
                        <div>

                            <p class="text-indigo-400 text-xs uppercase tracking-[0.3em] font-bold">
                                City Play
                            </p>

                            <h1 class="text-2xl md:text-3xl font-black mt-1">
                                {{ enigme.place.nom }}
                            </h1>

                            <p class="text-sm text-slate-300 mt-2">
                                Étape {{ progression.etape }}
                                / {{ progression.total }}
                            </p>

                        </div>

                        <!-- STATS -->
                        <div class="flex flex-wrap gap-3">

                            <div class="glass-card">
                                🎮 {{ modeLabel }}
                            </div>

                            <div class="glass-card">
                                🧠 {{ difficulteLabel }}
                            </div>

                            <div class="glass-card">
                                🚶 {{ locomotionLabel }}
                            </div>

                            <div class="glass-card chrono-card">
                                ⏱️ {{ chrono }}
                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <!-- CONTENT -->
            <div class="max-w-7xl mx-auto px-4 py-8">

                <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

                    <!-- COLONNE ENIGME -->
                    <div class="xl:col-span-2 space-y-6">

                        <!-- IMAGE -->
                        <div
                            v-if="enigme.image"
                            class="overflow-hidden rounded-3xl border border-white/10 shadow-2xl"
                        >

                            <img
                                :src="'/storage/' + enigme.image"
                                class="w-full h-[300px] object-cover"
                            />

                        </div>

                        <!-- ENIGME -->
                        <div class="game-card">

                            <div class="flex items-center gap-3 mb-5">

                                <div class="h-12 w-12 rounded-2xl bg-indigo-500/20 flex items-center justify-center text-2xl">
                                    🧩
                                </div>

                                <div>

                                    <p class="text-xs uppercase tracking-[0.3em] text-indigo-300">
                                        Mission
                                    </p>

                                    <h2 class="text-2xl font-black">
                                        Résolvez l'énigme
                                    </h2>

                                </div>

                            </div>

                            <div class="rounded-3xl bg-black/20 border border-white/10 p-6">

                                <p class="text-lg md:text-xl leading-9 text-slate-100 font-medium">
                                    “ {{ enigme.texte }} ”
                                </p>

                            </div>

                        </div>

                        <!-- INDICES -->
                        <div class="game-card">

                            <div class="flex items-center justify-between">

                                <h3 class="text-xl font-bold">
                                    💡 Indices
                                </h3>

                                <span class="text-xs bg-amber-500/20 text-amber-300 px-3 py-1 rounded-full">
                                    {{ enigme.pivot.nb_indices_demandes }}/2
                                </span>

                            </div>

                            <div class="mt-5 space-y-4">

                                <div
                                    v-if="enigme.pivot.nb_indices_demandes >= 1"
                                    class="indice-card"
                                >
                                    <strong>Indice 1 :</strong>
                                    {{ enigme.indice_1 }}
                                </div>

                                <div
                                    v-if="enigme.pivot.nb_indices_demandes >= 2"
                                    class="indice-card"
                                >
                                    <strong>Indice 2 :</strong>
                                    {{ enigme.indice_2 }}
                                </div>

                                <button
                                    v-if="enigme.pivot.nb_indices_demandes < 2 && !enigme.pivot.solution_affichee"
                                    @click="demanderIndice"
                                    class="action-btn bg-amber-500 hover:bg-amber-600"
                                >
                                    Obtenir un indice
                                </button>

                            </div>

                        </div>

                    </div>

                    <!-- SIDEBAR -->
                    <div class="space-y-6">

                        <!-- MAP -->
                        <div class="game-card">

                            <div class="flex items-center justify-between mb-4">

                                <h3 class="text-xl font-bold">
                                    📍 Carte GPS
                                </h3>

                                <span class="text-xs bg-green-500/20 text-green-300 px-3 py-1 rounded-full">
                                    Rayon {{ rayon }}m
                                </span>

                            </div>

                            <div
                                ref="mapContainer"
                                class="h-[350px] rounded-3xl overflow-hidden border border-white/10"
                            />

                            <p
                                v-if="validationMessage"
                                class="mt-4 rounded-2xl p-4 text-sm font-medium"
                                :class="
                                    validationOk
                                        ? 'bg-green-500/20 text-green-300 border border-green-500/30'
                                        : 'bg-red-500/20 text-red-300 border border-red-500/30'
                                "
                            >
                                {{ validationMessage }}
                            </p>

                            <button
                                v-if="!enigme.pivot.solution_affichee"
                                @click="validerPosition"
                                :disabled="gpsLoading"
                                class="validate-btn mt-5"
                            >

                                <span v-if="gpsLoading">
                                    Localisation...
                                </span>

                                <span v-else>
                                    📡 Valider ma position
                                </span>

                            </button>

                        </div>

                        <!-- SOLUTION -->
                        <div
                            v-if="enigme.pivot.solution_affichee"
                            class="game-card border border-red-500/30"
                        >

                            <h3 class="text-xl font-bold text-red-300">
                                ☠️ Solution révélée
                            </h3>

                            <p class="mt-4 text-slate-200">
                                {{ enigme.solution }}
                            </p>

                        </div>

                        <!-- ACTIONS -->
                        <div class="game-card">

                            <h3 class="text-xl font-bold mb-5">
                                ⚔️ Actions
                            </h3>

                            <div class="space-y-4">

                                <button
                                    v-if="!enigme.pivot.solution_affichee"
                                    @click="voirSolution"
                                    class="w-full action-btn bg-red-600 hover:bg-red-700"
                                >
                                    Voir la solution
                                </button>

                                <button
                                    v-if="enigme.pivot.solution_affichee || enigme.pivot.statut === 'resolue'"
                                    @click="passerSuivant"
                                    class="w-full action-btn bg-indigo-600 hover:bg-indigo-700"
                                >
                                    Étape suivante →
                                </button>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </AuthenticatedLayout>

</template>

<style scoped>

.game-card {

    @apply bg-white/5 backdrop-blur-xl border border-white/10 rounded-3xl p-6 shadow-2xl;
}

.glass-card {

    @apply bg-white/10 border border-white/10 rounded-2xl px-4 py-2 text-sm font-semibold backdrop-blur-lg;
}

.indice-card {

    @apply bg-amber-500/10 border border-amber-500/20 text-amber-100 rounded-2xl p-4 text-sm;
}

.action-btn {

    @apply px-5 py-3 rounded-2xl font-bold text-white transition-all duration-300 shadow-lg w-full;
}

.validate-btn {

    @apply w-full rounded-2xl bg-green-600 hover:bg-green-700 py-4 font-black text-white transition-all duration-300 shadow-2xl;
}

.chrono-card {

    animation: pulseChrono 1.5s infinite;
}

@keyframes pulseChrono {

    0% {

        transform: scale(1);
    }

    50% {

        transform: scale(1.04);
    }

    100% {

        transform: scale(1);
    }

}
</style>