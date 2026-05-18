<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

// Récupération des données envoyées par le contrôleur (incluant la relation environment)
defineProps({
    places: {
        type: Array,
        required: true
    }
});
</script>

<template>
    <Head title="Places" />

    <AppLayout>
        <template #default>
            <!-- En-tête de la page avec bouton de création -->
            <header class="bg-white shadow">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 flex justify-between items-center">
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        Liste des Places
                    </h2>
                    
                    <Link 
                        :href="route('places.create')" 
                        class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                    >
                        + Nouvelle Place
                    </Link>
                </div>
            </header>

            <!-- Table des places -->
            <div class="py-12">
                <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6">
                        
                        <!-- Cas vide -->
                        <div v-if="places.length === 0" class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune place disponible</h3>
                            <p class="mt-1 text-sm text-gray-500">Ajoutez des points d'intérêt ou des zones géographiques.</p>
                        </div>

                        <!-- Tableau des données -->
                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Environnement</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Coordonnées (Lat, Long)</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rayon (m)</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="place in places" :key="place.id" class="hover:bg-gray-50">
                                        
                                        <!-- Nom & Description Légère -->
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">{{ place.nom }}</div>
                                            <div class="text-xs text-gray-400 max-w-xs truncate">{{ place.description || 'Pas de description' }}</div>
                                        </td>
                                        
                                        <!-- Nom de l'Environnement parent -->
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs font-medium">
                                                {{ place.environment ? place.environment.nom : 'Non assigné' }}
                                            </span>
                                        </td>
                                        
                                        <!-- Coordonnées GPS -->
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-600">
                                            {{ Number(place.latitude).toFixed(5) }}, {{ Number(place.longitude).toFixed(5) }}
                                        </td>
                                        
                                        <!-- Rayon de validation -->
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ place.rayon_validation }} m
                                        </td>
                                        
                                        <!-- Actions -->
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                            <Link :href="route('places.edit', place.id)" class="text-indigo-600 hover:text-indigo-900">
                                                Modifier
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </template>
    </AppLayout>
</template>
