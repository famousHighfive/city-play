<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

// Initialisation du formulaire avec Inertia useForm
const form = useForm({
    nom: '',
    description: '',
    actif: true,
});

// Fonction de soumission du formulaire
const submit = () => {
    form.post(route('environments.store'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Créer un Environnement" />

    <AppLayout>
        <template #default>
            <!-- En-tête de la page -->
            <header class="bg-white shadow">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        Créer un Nouvel Environnement
                    </h2>
                </div>
            </header>

            <!-- Zone du Formulaire -->
            <div class="py-12">
                <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6">
                        
                        <form @submit.prevent="submit" class="space-y-6">
                            
                            <!-- Champ Nom -->
                            <div>
                                <label for="nom" class="block text-sm font-medium text-gray-700">Nom de l'environnement</label>
                                <input 
                                    id="nom" 
                                    v-model="form.nom" 
                                    type="text" 
                                    maxlength="150"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    :class="{ 'border-red-500': form.errors.nom }"
                                    placeholder="Ex: Production, Développement..."
                                    required
                                />
                                <div v-if="form.errors.nom" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.nom }}
                                </div>
                            </div>

                            <!-- Champ Description -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea 
                                    id="description" 
                                    v-model="form.description" 
                                    rows="4"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    :class="{ 'border-red-500': form.errors.description }"
                                    placeholder="Décrivez cet environnement (optionnel)..."
                                ></textarea>
                                <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.description }}
                                </div>
                            </div>

                            <!-- Champ Actif (Checkbox) -->
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
                                    <label for="actif" class="font-medium text-gray-700">Activer l'environnement</label>
                                    <p class="text-gray-500">Cet environnement sera visible et utilisable immédiatement.</p>
                                </div>
                            </div>

                            <!-- Boutons d'action -->
                            <div class="flex items-center justify-end space-x-4 border-t border-gray-100 pt-4">
                                <button 
                                    type="button" 
                                    @click="form.reset()" 
                                    class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none"
                                    :disabled="form.processing"
                                >
                                    Réinitialiser
                                </button>
                                
                                <button 
                                    type="submit" 
                                    class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none disabled:opacity-50"
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
