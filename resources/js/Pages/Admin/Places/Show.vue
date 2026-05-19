<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    place: {
        type: Object,
        required: true
    }
});
</script>

<template>
    <Head :title="`Lieu - ${place.nom}`" />

    <AppLayout>
        <template #default>
            <header class="bg-white shadow">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        <Link :href="route('places.index')" class="text-gray-600 hover:text-gray-800">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </Link>
                        <h2 class="text-xl font-semibold leading-tight text-gray-800">
                            Détail du Lieu
                        </h2>
                    </div>
                    <div class="flex space-x-3">
                        <Link :href="route('places.edit', place.id)" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                            Modifier
                        </Link>
                    </div>
                </div>
            </header>

            <div class="py-12">
                <div class="mx-auto max-w-4xl sm:px-6 lg:px-8 space-y-6">
                    
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="mb-6">
                                <h3 class="text-lg font-medium text-gray-900">{{ place.nom }}</h3>
                                <span class="inline-flex items-center rounded-md bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs font-medium mt-2">
                                    {{ place.environment ? place.environment.nom : 'Non assigné' }}
                                </span>
                            </div>

                            <div class="space-y-6">
                                <div v-if="place.description">
                                    <h4 class="text-sm font-medium text-gray-500 mb-2">Description</h4>
                                    <p class="text-gray-900">{{ place.description }}</p>
                                </div>

                                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500 mb-2">Latitude</h4>
                                        <p class="text-gray-900 font-mono">{{ place.latitude }}</p>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500 mb-2">Longitude</h4>
                                        <p class="text-gray-900 font-mono">{{ place.longitude }}</p>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 mb-2">Rayon de validation</h4>
                                    <p class="text-gray-900">{{ place.rayon_validation }} mètres</p>
                                </div>

                                <div class="pt-4 border-t border-gray-200">
                                    <h4 class="text-sm font-medium text-gray-500 mb-4">Énigmes associées ({{ place.enigmes ? place.enigmes.length : 0 }})</h4>
                                    <div v-if="place.enigmes && place.enigmes.length > 0" class="space-y-2">
                                        <div v-for="enigme in place.enigmes" :key="enigme.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ enigme.texte.substring(0, 50) }}...</p>
                                                <span class="text-xs text-gray-500">
                                                    {{ enigme.niveau === 'enfant' ? 'Enfant' : 'Niveau ' + enigme.niveau }}
                                                </span>
                                            </div>
                                            <Link :href="route('enigmes.show', enigme.id)" class="text-indigo-600 hover:text-indigo-900 text-sm">
                                                Voir
                                            </Link>
                                        </div>
                                    </div>
                                    <p v-else class="text-sm text-gray-500">Aucune énigme associée à ce lieu.</p>
                                </div>

                                <div class="pt-4 border-t border-gray-200">
                                    <h4 class="text-sm font-medium text-gray-500 mb-2">Informations</h4>
                                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <dt class="text-sm text-gray-500">Créé le</dt>
                                            <dd class="text-sm text-gray-900">{{ new Date(place.created_at).toLocaleDateString('fr-FR') }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm text-gray-500">Mis à jour le</dt>
                                            <dd class="text-sm text-gray-900">{{ new Date(place.updated_at).toLocaleDateString('fr-FR') }}</dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </template>
    </AppLayout>
</template>
