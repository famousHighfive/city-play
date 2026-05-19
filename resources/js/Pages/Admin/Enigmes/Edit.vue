<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

defineProps({
    enigme: {
        type: Object,
        required: true
    },
    places: {
        type: Array,
        required: true
    }
});

const form = useForm({
    place_id: props.enigme.place_id,
    niveau: props.enigme.niveau,
    texte: props.enigme.texte,
    solution: props.enigme.solution,
    indice_1: props.enigme.indice_1,
    indice_2: props.enigme.indice_2,
    image: null,
    actif: props.enigme.actif,
});

const submit = () => {
    form.put(route('enigmes.update', props.enigme.id));
};
</script>

<template>
    <Head title="Modifier une Énigme" />

    <AppLayout>
        <template #default>
            <header class="bg-white shadow">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 flex items-center space-x-4">
                    <Link :href="route('enigmes.index')" class="text-gray-600 hover:text-gray-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </Link>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        Modifier l'Énigme
                    </h2>
                </div>
            </header>

            <div class="py-12">
                <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6">
                        
                        <form @submit.prevent="submit" class="space-y-6" enctype="multipart/form-data">
                            
                            <div>
                                <label for="place_id" class="block text-sm font-medium text-gray-700">Lieu historique ou populaire associé</label>
                                <select 
                                    id="place_id" 
                                    v-model="form.place_id" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    :class="{ 'border-red-500': form.errors.place_id }"
                                    required
                                >
                                    <option value="" disabled>Sélectionnez un lieu...</option>
                                    <option v-for="place in places" :key="place.id" :value="place.id">
                                        {{ place.nom }}
                                    </option>
                                </select>
                                <div v-if="form.errors.place_id" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.place_id }}
                                </div>
                            </div>

                            <div>
                                <label for="niveau" class="block text-sm font-medium text-gray-700">Niveau de difficulté</label>
                                <select 
                                    id="niveau" 
                                    v-model="form.niveau" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    required
                                >
                                    <option value="1">Niveau 1 (Facile)</option>
                                    <option value="2">Niveau 2 (Moyen)</option>
                                    <option value="3">Niveau 3 (Difficile)</option>
                                    <option value="enfant">Enfant</option>
                                </select>
                                <div v-if="form.errors.niveau" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.niveau }}
                                </div>
                            </div>

                            <div>
                                <label for="texte" class="block text-sm font-medium text-gray-700">Texte de l'énigme / Question</label>
                                <textarea 
                                    id="texte" 
                                    v-model="form.texte" 
                                    rows="4"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    :class="{ 'border-red-500': form.errors.texte }"
                                    placeholder="Écrivez la question ou l'énigme que le joueur devra résoudre..."
                                    required
                                ></textarea>
                                <div v-if="form.errors.texte" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.texte }}
                                </div>
                            </div>

                            <div>
                                <label for="indice_1" class="block text-sm font-medium text-gray-700">Premier Indice</label>
                                <input 
                                    id="indice_1" 
                                    v-model="form.indice_1" 
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    :class="{ 'border-red-500': form.errors.indice_1 }"
                                    placeholder="Premier indice pour aider le joueur..."
                                    required
                                />
                                <div v-if="form.errors.indice_1" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.indice_1 }}
                                </div>
                            </div>

                            <div>
                                <label for="indice_2" class="block text-sm font-medium text-gray-700">Deuxième Indice (Optionnel)</label>
                                <input 
                                    id="indice_2" 
                                    v-model="form.indice_2" 
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    :class="{ 'border-red-500': form.errors.indice_2 }"
                                    placeholder="Un indice supplémentaire un peu plus précis..."
                                />
                                <div v-if="form.errors.indice_2" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.indice_2 }}
                                </div>
                            </div>

                            <div>
                                <label for="solution" class="block text-sm font-medium text-gray-700">Solution de l'énigme</label>
                                <input 
                                    id="solution" 
                                    v-model="form.solution" 
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    :class="{ 'border-red-500': form.errors.solution }"
                                    placeholder="La bonne réponse ou l'explication finale..."
                                    required
                                />
                                <div v-if="form.errors.solution" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.solution }}
                                </div>
                            </div>

                            <div>
                                <label for="image" class="block text-sm font-medium text-gray-700">Illustration de l'énigme</label>
                                <div v-if="enigme.image" class="mb-3">
                                    <img :src="`/storage/${enigme.image}`" alt="Current" class="w-32 h-32 object-cover rounded-md">
                                </div>
                                <input 
                                    id="image" 
                                    type="file"
                                    @input="form.image = $event.target.files[0]"
                                    class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                    :class="{ 'border-red-500': form.errors.image }"
                                    accept="image/*"
                                />
                                <div v-if="form.errors.image" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.image }}
                                </div>
                            </div>

                            <div class="flex items-start">
                                <div class="flex h-5 items-center">
                                    <input 
                                        id="actif" 
                                        v-model="form.actif" 
                                        type="checkbox" 
                                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    />
                                </div>
                                <div class="ms-3 text-sm">
                                    <label for="actif" class="font-medium text-gray-700">Rendre l'énigme active</label>
                                    <p class="text-gray-500">Elle pourra être intégrée directement aux parcours des joueurs.</p>
                                </div>
                            </div>

                            <div class="flex items-center justify-end space-x-4 border-t border-gray-100 pt-4">
                                <Link 
                                    :href="route('enigmes.index')" 
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
