<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import L from 'leaflet';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

defineProps({
    environments: Array
});

const toast = useToast();

const map = ref(null);
const search = ref('');

let marker = null;

const form = useForm({
    environment_id: '',
    nom: '',
    description: '',
    latitude: '',
    longitude: '',
    rayon_validation: 30,
    ordre: 0,
    popularite: 1,
});

const submit = () => {

    form.post(route('places.store'), {

        onSuccess: () => {

            form.reset();

            if (marker) {
                map.value.removeLayer(marker);
            }

            map.value.setView([6.370293, 2.391236], 13);
            toast.add({ severity: 'success', summary: 'Succès', detail: 'Lieu créé avec succès !', life: 5000 });
        }
    });
};

onMounted(() => {

    map.value = L.map('map').setView([6.370293, 2.391236], 13);

    L.tileLayer(
        'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        {
            attribution: '&copy; OpenStreetMap contributors'
        }
    ).addTo(map.value);

    map.value.on('click', async (e) => {

        const lat = e.latlng.lat;
        const lng = e.latlng.lng;

        form.latitude = lat;
        form.longitude = lng;

        updateMarker(lat, lng);

        try {

            const response = await fetch(
                `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`
            );

            const data = await response.json();

            if (data.display_name && !form.nom) {
                form.nom = data.display_name;
            }

        } catch (error) {
            console.error(error);
        }
    });
});

const updateMarker = (lat, lng) => {

    if (marker) {
        map.value.removeLayer(marker);
    }

    marker = L.marker([lat, lng]).addTo(map.value);

    map.value.setView([lat, lng], 16);
};

const useCurrentLocation = () => {

    if (!navigator.geolocation) {
        toast.add({ severity: 'error', summary: 'Erreur', detail: 'Géolocalisation non supportée', life: 5000 });
        return;
    }

    navigator.geolocation.getCurrentPosition(

        async (position) => {

            const lat = position.coords.latitude;
            const lng = position.coords.longitude;

            form.latitude = lat;
            form.longitude = lng;

            updateMarker(lat, lng);

            try {

                const response = await fetch(
                    `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`
                );

                const data = await response.json();

                if (data.display_name && !form.nom) {
                    form.nom = data.display_name;
                }

            } catch (error) {
                console.error(error);
            }
        },

        () => {
            toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de récupérer votre position', life: 5000 });
        }
    );
};

const searchPlace = async () => {

    if (!search.value) {
        toast.add({ severity: 'warn', summary: 'Attention', detail: 'Veuillez saisir un lieu', life: 5000 });
        return;
    }

    try {

        const response = await fetch(
            `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(search.value)}`
        );

        const data = await response.json();

        if (data.length === 0) {
            toast.add({ severity: 'warn', summary: 'Attention', detail: 'Lieu introuvable', life: 5000 });
            return;
        }

        const place = data[0];

        const lat = parseFloat(place.lat);
        const lng = parseFloat(place.lon);

        form.latitude = lat;
        form.longitude = lng;
        form.nom = place.display_name;

        updateMarker(lat, lng);

    } catch (error) {
        console.error(error);
        toast.add({ severity: 'error', summary: 'Erreur', detail: 'Erreur lors de la recherche', life: 5000 });
    }
};
</script>

<template>

    <Head title="Créer une Place" />

    <AppLayout>

        <div class="max-w-6xl mx-auto py-10 px-4">

            <div class="bg-white rounded-2xl shadow overflow-hidden">

                <div class="border-b px-8 py-6">

                    <h1 class="text-3xl font-bold">
                        Nouvelle Place
                    </h1>

                    <p class="text-gray-500 mt-2">
                        Ajouter un nouveau lieu CityPlay
                    </p>

                </div>

                <form
                    @submit.prevent="submit"
                    class="p-8 space-y-8"
                >

                    <!-- ENVIRONNEMENT -->
                    <div>

                        <label class="block mb-2 font-medium">
                            Environnement
                        </label>

                        <select
                            v-model="form.environment_id"
                            class="w-full border rounded-xl px-4 py-3"
                        >
                            <option value="">
                                Sélectionner
                            </option>

                            <option
                                v-for="env in environments"
                                :key="env.id"
                                :value="env.id"
                            >
                                {{ env.nom }}
                            </option>

                        </select>

                    </div>

                    <!-- RECHERCHE -->
                    <div>

                        <label class="block mb-2 font-medium">
                            Recherche
                        </label>

                        <div class="flex gap-3">

                            <input
                                v-model="search"
                                type="text"
                                placeholder="Ex: Palais des Congrès Cotonou"
                                class="flex-1 border rounded-xl px-4 py-3"
                            />

                            <button
                                type="button"
                                @click="searchPlace"
                                class="bg-indigo-600 text-white px-6 rounded-xl"
                            >
                                Rechercher
                            </button>

                        </div>

                    </div>

                    <!-- NOM -->
                    <div>

                        <label class="block mb-2 font-medium">
                            Nom
                        </label>

                        <input
                            v-model="form.nom"
                            type="text"
                            class="w-full border rounded-xl px-4 py-3"
                        />

                    </div>

                    <!-- MAP -->
                    <div>

                        <div class="flex justify-between items-center mb-4">

                            <div>

                                <h2 class="font-semibold">
                                    Position GPS
                                </h2>

                                <p class="text-sm text-gray-500">
                                    Cliquez sur la carte
                                </p>

                            </div>

                            <button
                                type="button"
                                @click="useCurrentLocation"
                                class="bg-emerald-600 text-white px-5 py-3 rounded-xl"
                            >
                                Utiliser ma position
                            </button>

                        </div>

                        <div
                            id="map"
                            class="h-[500px] rounded-2xl border"
                        ></div>

                    </div>

                    <!-- COORDONNEES -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <div>

                            <label class="block mb-2 font-medium">
                                Latitude
                            </label>

                            <input
                                v-model="form.latitude"
                                type="text"
                                readonly
                                class="w-full border rounded-xl px-4 py-3 bg-gray-100"
                            />

                        </div>

                        <div>

                            <label class="block mb-2 font-medium">
                                Longitude
                            </label>

                            <input
                                v-model="form.longitude"
                                type="text"
                                readonly
                                class="w-full border rounded-xl px-4 py-3 bg-gray-100"
                            />

                        </div>

                    </div>

                    <!-- CONFIG -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <div>

                            <label class="block mb-2 font-medium">
                                Ordre
                            </label>

                            <input
                                v-model="form.ordre"
                                type="number"
                                class="w-full border rounded-xl px-4 py-3"
                            />

                        </div>

                        <div>

                            <label class="block mb-2 font-medium">
                                Popularité
                            </label>

                            <select
                                v-model="form.popularite"
                                class="w-full border rounded-xl px-4 py-3"
                            >
                                <option :value="1">1</option>
                                <option :value="2">2</option>
                                <option :value="3">3</option>
                                <option :value="4">4</option>
                                <option :value="5">5</option>
                            </select>

                        </div>

                    </div>

                    <!-- DESCRIPTION -->
                    <div>

                        <label class="block mb-2 font-medium">
                            Description
                        </label>

                        <textarea
                            v-model="form.description"
                            rows="5"
                            maxlength="500"
                            class="w-full border rounded-xl px-4 py-3"
                        ></textarea>

                    </div>

                    <!-- RAYON -->
                    <div>

                        <label class="block mb-2 font-medium">
                            Rayon validation (mètres)
                        </label>

                        <input
                            v-model="form.rayon_validation"
                            type="number"
                            class="w-full border rounded-xl px-4 py-3"
                        />

                    </div>

                    <!-- ACTIONS -->
                    <div class="flex justify-end gap-4 pt-6 border-t">

                        <button
                            type="button"
                            @click="form.reset()"
                            class="border px-6 py-3 rounded-xl"
                        >
                            Réinitialiser
                        </button>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="bg-indigo-600 text-white px-8 py-3 rounded-xl"
                        >
                            {{ form.processing ? 'Enregistrement...' : 'Enregistrer' }}
                        </button>

                    </div>

                </form>

            </div>

        </div>
        <Toast position="top-right" />
    </AppLayout>

</template>
