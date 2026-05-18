<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    places: {
        type: Array,
        required: true
    }
});

const form = useForm({
    place_id: '',
    niveau: '1', // Valeur par défaut
    texte: '',
    image: null, // Initialisé à null pour la gestion du fichier
    actif: true,
});

const submit = () => {
    form.post(route('enigmes.store'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Créer une Énigme" />

    <AppLayout>
        <template #default>
            <header class="bg-white shadow">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        Créer une Nouvelle Énigme
                    </h2>
                </div>
            </header>

            <div class="py-12">
                <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6">
                        
                        <form @submit.prevent="submit" class="space-y-6">
                            
                            <!-- Sélection de la Place -->
                            <div>
                                <label for="place_id" class="block text-sm font-medium text-gray-700">Place associée</label>
                                <select 
                                    id="place_id" 
                                    v-model="form.place_id" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    :class="{ 'border-red-500': form.errors.place_id }"
                                    required
                                >
                                    <option value="" disabled>Sélectionnez une place</option>
                                    <option v-for="place in places" :key="place.id" :value="place.id">
                                        {{ place.nom }}
                                    </option>
                                </select>
                                <div v-if="form.errors.place_id" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.place_id }}
                                </div>
                            </div>

                            <!-- Sélection du Niveau (Enum de votre migration) -->
                            <div>
                                <label for="niveau" class="block text-sm font-medium text-gray-700">Niveau de difficulté</label>
                                <select 
                                    id="niveau" 
                                    v-model="form.niveau" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    :class="{ 'border-red-500': form.errors.niveau }"
                                    required
                                >
                                    <option value="1">Niveau 1</option>
                                    <option value="2">Niveau 2</option>
                                    <option value="3">Niveau 3</option>
                                    <option value="enfant">Enfant</option>
                                </select>
                                <div v-if="form.errors.niveau" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.niveau }}
                                </div>
                            </div>

                            <!-- Texte de l'Énigme -->
                            <div>
                                <label for="texte" class="block text-sm font-medium text-gray-700">Texte de l'énigme</label>
                                <textarea 
                                    id="texte" 
                                    v-model="form.texte" 
                                    rows="5"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    :class="{ 'border-red-500': form.errors.texte }"
                                    placeholder="Écrivez l'énigme ou la question ici..."
                                    required
                                ></textarea>
                                <div v-if="form.errors.texte" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.texte }}
                                </div>
                            </div>

                            <!-- Importation de l'Image -->
                            <div>
                                <label for="image" class="block text-sm font-medium text-gray-700">Image d'illustration (optionnel)</label>
                                <input 
                                    id="image" 
                                    type="file" 
                                    accept="image/*"
                                    @input="form.image = $event.target.files[0]"
                                    class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                    :class="{ 'border-red-500': form.errors.image }"
                                />
                                <div v-if="form.errors.image" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.image }}
                                </div>
                            </div>

                            <!-- Statut Actif -->
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
                                    <label for="actif" class="font-medium text-gray-700">Activer l'énigme</label>
                                    <p class="text-gray-500">Rendre l'énigme immédiatement visible dans le jeu.</p>
                                </div>
                            </div>

                            <!-- Boutons d'action -->
                            <div class="flex items-center justify-end space-x-4 border-t border-gray-100 pt-4">
                                <button 
                                    type="button" 
                                    @click="form.reset()" 
                                    class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                                    :disabled="form.processing"
                                >
                                    Réinitialiser
                                </button>
                                
                                <button 
                                    type="submit" 
                                    class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 disabled:opacity-50"
                                    :disabled="form.processing"
                                >
                                    {{ form.processing ? 'Enregistrement...' : 'Enregistrer' }}
                                </button>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </template>
    </AppLayout>
</template>
