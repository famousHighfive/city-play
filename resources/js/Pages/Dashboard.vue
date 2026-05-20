<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    environments: {
        type: Array,
        required: true
    },
    games: {
        type: Array,
        required: true
    }
});

const startGame = (environment) => {
    router.visit(route('game.configure', environment.id));
};

const resumeGame = (game) => {
    router.visit(route('game.show', game.id));
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                Tableau de bord
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-8">
                
                <!-- Parties en cours -->
                <div v-if="games.length > 0">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Vos parties en cours</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div v-for="game in games" :key="game.id" class="bg-white shadow-sm sm:rounded-lg p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-md font-medium text-gray-900">{{ game.environment.nom }}</h4>
                                <span 
                                    class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset"
                                    :class="game.statut === 'en_cours' 
                                        ? 'bg-green-50 text-green-700 ring-green-600/20' 
                                        : 'bg-yellow-50 text-yellow-700 ring-yellow-600/20'"
                                >
                                    {{ game.statut === 'en_cours' ? 'En cours' : game.statut }}
                                </span>
                            </div>
                            <div class="text-sm text-gray-500 space-y-1 mb-4">
                                <p>Début : {{ new Date(game.date_debut).toLocaleDateString('fr-FR') }}</p>
                                <p v-if="game.mode_jeu">Mode : {{ game.mode_jeu }}</p>
                                <p>Membres : {{ game.nb_membres }}</p>
                                <p>Durée : {{ game.duree_restante ?? game.duree_prevue }} min</p>
                                <p>Difficulté : {{ game.niveau_difficulte }}</p>
                                <p>Locomotion : {{ game.moyen_locomotion }}</p>
                            </div>
                            <button 
                                @click="resumeGame(game)"
                                class="w-full inline-flex justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                            >
                                Reprendre la partie
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Environnements disponibles -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Villes disponibles</h3>
                    <div v-if="environments.length === 0" class="text-center py-12 bg-white shadow-sm sm:rounded-lg">
                        <p class="text-gray-500">Aucune ville disponible pour le moment.</p>
                    </div>
                    <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div v-for="env in environments" :key="env.id" class="bg-white shadow-sm sm:rounded-lg p-6">
                            <h4 class="text-md font-medium text-gray-900 mb-2">{{ env.nom }}</h4>
                            <p v-if="env.description" class="text-sm text-gray-500 mb-4">{{ env.description }}</p>
                            <button 
                                @click="startGame(env)"
                                class="w-full inline-flex justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                            >
                                Commencer une partie
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
