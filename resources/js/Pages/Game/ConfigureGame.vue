<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

defineProps({
    environment: {
        type: Object,
        required: true
    }
});

const form = useForm({
    nb_membres: 1,
    duree_prevue: 60,
    moyen_locomotion: 'pied',
    niveau_difficulte: 1,
});

const submit = () => {
    form.post(route('game.start', props.environment.id));
};
</script>

<template>
    <Head :title="`Configurer - ${environment.nom}`" />

    <AppLayout>
        <template #default>
            <header class="bg-white shadow">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 flex items-center space-x-4">
                    <Link :href="route('dashboard')" class="text-gray-600 hover:text-gray-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </Link>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        Configurer votre partie
                    </h2>
                </div>
            </header>

            <div class="py-12">
                <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6">
                        
                        <form @submit.prevent="submit" class="space-y-6">
                            
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ environment.nom }}</h3>
                                <p v-if="environment.description" class="text-sm text-gray-500 mb-6">{{ environment.description }}</p>
                            </div>

                            <div>
                                <label for="nb_membres" class="block text-sm font-medium text-gray-700">Nombre de membres de l'équipe</label>
                                <input 
                                    id="nb_membres" 
                                    v-model="form.nb_membres" 
                                    type="number" 
                                    min="1"
                                    max="10"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    :class="{ 'border-red-500': form.errors.nb_membres }"
                                    required
                                />
                                <p class="mt-1 text-xs text-gray-500">Maximum 10 membres.</p>
                                <div v-if="form.errors.nb_membres" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.nb_membres }}
                                </div>
                            </div>

                            <div>
                                <label for="duree_prevue" class="block text-sm font-medium text-gray-700">Durée prévue pour le jeu (en minutes)</label>
                                <input 
                                    id="duree_prevue" 
                                    v-model="form.duree_prevue" 
                                    type="number" 
                                    min="1"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    :class="{ 'border-red-500': form.errors.duree_prevue }"
                                    placeholder="Ex: 60"
                                    required
                                />
                                <div v-if="form.errors.duree_prevue" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.duree_prevue }}
                                </div>
                            </div>

                            <div>
                                <label for="moyen_locomotion" class="block text-sm font-medium text-gray-700">Moyen de locomotion</label>
                                <select 
                                    id="moyen_locomotion" 
                                    v-model="form.moyen_locomotion" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    :class="{ 'border-red-500': form.errors.moyen_locomotion }"
                                    required
                                >
                                    <option value="pied">À pieds</option>
                                    <option value="velo">À vélo</option>
                                    <option value="voiture">En voiture</option>
                                </select>
                                <div v-if="form.errors.moyen_locomotion" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.moyen_locomotion }}
                                </div>
                            </div>

                            <div>
                                <label for="niveau_difficulte" class="block text-sm font-medium text-gray-700">Degré de difficulté</label>
                                <select 
                                    id="niveau_difficulte" 
                                    v-model="form.niveau_difficulte" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    :class="{ 'border-red-500': form.errors.niveau_difficulte }"
                                    required
                                >
                                    <option :value="1">Niveau 1 - Facile</option>
                                    <option :value="2">Niveau 2 - Moyen</option>
                                    <option :value="3">Niveau 3 - Difficile</option>
                                </select>
                                <div v-if="form.errors.niveau_difficulte" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.niveau_difficulte }}
                                </div>
                            </div>

                            <div class="flex items-center justify-end space-x-4 border-t border-gray-100 pt-4">
                                <Link 
                                    :href="route('dashboard')" 
                                    class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none"
                                >
                                    Annuler
                                </Link>
                                
                                <button 
                                    type="submit" 
                                    class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none disabled:opacity-50"
                                    :disabled="form.processing"
                                >
                                    {{ form.processing ? 'Démarrage...' : 'Commencer la partie' }}
                                </button>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </template>
    </AppLayout>
</template>
