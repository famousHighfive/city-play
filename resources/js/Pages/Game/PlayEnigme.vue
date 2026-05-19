<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    game: Object,
    enigme: Object
});

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
</script>

<template>
    <Head title="Partie en cours" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Partie en cours
            </h2>
        </template>

        <div class="py-12 bg-gray-100 min-h-screen">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                
                <div class="bg-indigo-600 text-white p-4 rounded-t-lg shadow">
                    <p class="text-xs uppercase tracking-wider font-semibold opacity-75">Étape {{ enigme.pivot.ordre }}</p>
                    <h1 class="text-xl font-bold">Lieu : {{ enigme.place.nom }}</h1>
                </div>

                <div class="bg-white p-6 shadow rounded-b-lg space-y-6">
                    
                    <div v-if="enigme.image" class="overflow-hidden rounded-lg max-h-60 bg-gray-200">
                        <img :src="'/storage/' + enigme.image" alt="Illustration" class="w-full h-full object-cover">
                    </div>

                    <div class="prose max-w-none">
                        <h3 class="text-gray-500 text-sm font-medium">Votre Mission :</h3>
                        <p class="text-lg text-gray-800 font-serif italic">" {{ enigme.texte }} "</p>
                    </div>

                    <div class="border-t border-gray-100 pt-4 space-y-3">
                        <h4 class="text-sm font-semibold text-gray-700">Indices demandés ({{ enigme.pivot.nb_indices_demandes }} / 2)</h4>
                        
                        <div v-if="enigme.pivot.nb_indices_demandes >= 1" class="bg-amber-50 border-l-4 border-amber-500 p-3 rounded text-sm text-amber-900">
                            <strong>Indice 1 :</strong> {{ enigme.indice_1 }}
                        </div>

                        <div v-if="enigme.pivot.nb_indices_demandes >= 2" class="bg-amber-50 border-l-4 border-amber-500 p-3 rounded text-sm text-amber-900">
                            <strong>Indice 2 :</strong> {{ enigme.indice_2 || "Pas d'indice supplémentaire disponible." }}
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
                        <div v-if="enigme.pivot.solution_affichee" class="bg-red-50 border-l-4 border-red-500 p-4 rounded text-red-900">
                            <h4 class="font-bold">Solution révélée :</h4>
                            <p class="mt-1 text-sm font-mono">{{ enigme.solution }}</p>
                        </div>

                        <div class="flex items-center justify-between pt-4">
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
                                class="bg-green-600 text-white text-sm px-5 py-2 rounded-md shadow hover:bg-green-700 font-medium animate-pulse"
                            >
                                📍 Valider ma position GPS
                            </button>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
