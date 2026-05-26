<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    place: {
        type: Object,
        required: true
    },
    environments: {
        type: Array,
        required: true
    }
});

const form = useForm({
    environment_id: props.place.environment_id,
    nom: props.place.nom,
    latitude: props.place.latitude,
    longitude: props.place.longitude,
    description: props.place.description ?? '',
    recommandation: Array.isArray(props.place.recommandation)
        ? props.place.recommandation.map((r) => ({ nom: r.nom ?? '', description: r.description ?? '' }))
        : [],
    ressource: props.place.ressource ?? '',
    rayon_validation: props.place.rayon_validation,
});

const ajouterRecommandation = () => {
    form.recommandation.push({ nom: '', description: '' });
};

const retirerRecommandation = (index) => {
    form.recommandation.splice(index, 1);
};

const submit = () => {
    form.put(route('places.update', props.place.id));
};
</script>

<template>
    <Head title="Modifier un Lieu" />

    <AppLayout>
        <template #default>
            <header class="bg-white shadow">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        Modifier le Lieu
                    </h2>
                </div>
            </header>

            <div class="py-12">
                <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6">
                        
                        <form @submit.prevent="submit" class="space-y-6">
                            
                            <div>
                                <label for="environment_id" class="block text-sm font-medium text-gray-700">Environnement associé</label>
                                <select 
                                    id="environment_id" 
                                    v-model="form.environment_id" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    :class="{ 'border-red-500': form.errors.environment_id }"
                                    required
                                >
                                    <option value="" disabled>Sélectionnez un environnement</option>
                                    <option v-for="env in environments" :key="env.id" :value="env.id">
                                        {{ env.nom }}
                                    </option>
                                </select>
                                <div v-if="form.errors.environment_id" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.environment_id }}
                                </div>
                            </div>

                            <div>
                                <label for="nom" class="block text-sm font-medium text-gray-700">Nom de la place</label>
                                <input 
                                    id="nom" 
                                    v-model="form.nom" 
                                    type="text" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    :class="{ 'border-red-500': form.errors.nom }"
                                    placeholder="Ex: Place de la République..."
                                    required
                                />
                                <div v-if="form.errors.nom" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.nom }}
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="latitude" class="block text-sm font-medium text-gray-700">Latitude</label>
                                    <input 
                                        id="latitude" 
                                        v-model="form.latitude" 
                                        type="number" 
                                        step="0.0000001"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        :class="{ 'border-red-500': form.errors.latitude }"
                                        placeholder="Ex: 48.856614"
                                        required
                                />
                                    <div v-if="form.errors.latitude" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.latitude }}
                                    </div>
                                </div>

                                <div>
                                    <label for="longitude" class="block text-sm font-medium text-gray-700">Longitude</label>
                                    <input 
                                        id="longitude" 
                                        v-model="form.longitude" 
                                        type="number" 
                                        step="0.0000001"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        :class="{ 'border-red-500': form.errors.longitude }"
                                        placeholder="Ex: 2.352221"
                                        required
                                    />
                                    <div v-if="form.errors.longitude" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.longitude }}
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea 
                                    id="description" 
                                    v-model="form.description" 
                                    rows="3"
                                    maxlength="500"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    :class="{ 'border-red-500': form.errors.description }"
                                    placeholder="Une brève description (500 caractères max, optionnel)..."
                                ></textarea>
                                <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.description }}
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Recommandations</label>
                                        <p class="text-xs text-gray-500 mt-1">Lieux conseillés autour de cette place</p>
                                    </div>
                                    <button
                                        type="button"
                                        @click="ajouterRecommandation"
                                        class="text-sm text-indigo-600 hover:text-indigo-800 font-medium"
                                    >
                                        + Ajouter
                                    </button>
                                </div>
                                <div
                                    v-for="(rec, index) in form.recommandation"
                                    :key="index"
                                    class="border rounded-lg p-4 space-y-2 bg-gray-50"
                                >
                                    <div class="flex justify-between">
                                        <span class="text-xs text-gray-500">Lieu #{{ index + 1 }}</span>
                                        <button type="button" @click="retirerRecommandation(index)" class="text-xs text-red-600">Retirer</button>
                                    </div>
                                    <input v-model="rec.nom" type="text" placeholder="Nom" class="block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" />
                                    <input v-model="rec.description" type="text" placeholder="Précision (optionnel)" class="block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" />
                                </div>
                            </div>

                            <div>
                                <label for="ressource" class="block text-sm font-medium text-gray-700">Plus d'infos (contact)</label>
                                <input
                                    id="ressource"
                                    v-model="form.ressource"
                                    type="tel"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Ex: +229 97 00 00 00"
                                />
                                <div v-if="form.errors.ressource" class="mt-1 text-sm text-red-600">{{ form.errors.ressource }}</div>
                            </div>

                            <div>
                                <label for="rayon_validation" class="block text-sm font-medium text-gray-700">Rayon de validation (en mètres)</label>
                                <input 
                                    id="rayon_validation" 
                                    v-model="form.rayon_validation" 
                                    type="number" 
                                    min="1"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    :class="{ 'border-red-500': form.errors.rayon_validation }"
                                    required
                                />
                                <p class="mt-1 text-xs text-gray-500">Distance à laquelle la présence de l'utilisateur est confirmée.</p>
                                <div v-if="form.errors.rayon_validation" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.rayon_validation }}
                                </div>
                            </div>

                            <div class="flex items-center justify-end space-x-4 border-t border-gray-100 pt-4">
                                <Link 
                                    :href="route('places.index')" 
                                    class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none"
                                >
                                    Annuler
                                </Link>
                                
                                <button 
                                    type="submit" 
                                    class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none disabled:opacity-50"
                                    :disabled="form.processing"
                                >
                                    {{ form.processing ? 'Enregistrement...' : 'Mettre à jour' }}
                                </button>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </template>
    </AppLayout>
</template>
