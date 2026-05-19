<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    environment: {
        type: Object,
        required: true
    }
});
</script>

<template>
    <Head :title="`Environnement - ${environment.nom}`" />

    <AppLayout>
        <template #default>
            <header class="bg-white shadow">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        <Link :href="route('environments.index')" class="text-gray-600 hover:text-gray-800">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </Link>
                        <h2 class="text-xl font-semibold leading-tight text-gray-800">
                            Détail de l'Environnement
                        </h2>
                    </div>
                    <div class="flex space-x-3">
                        <Link :href="route('environments.edit', environment.id)" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                            Modifier
                        </Link>
                    </div>
                </div>
            </header>

            <div class="py-12">
                <div class="mx-auto max-w-4xl sm:px-6 lg:px-8 space-y-6">
                    
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-6">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900">{{ environment.nom }}</h3>
                                </div>
                                <span 
                                    class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset"
                                    :class="environment.actif 
                                        ? 'bg-green-50 text-green-700 ring-green-600/20' 
                                        : 'bg-red-50 text-red-700 ring-red-600/20'"
                                >
                                    {{ environment.actif ? 'Actif' : 'Inactif' }}
                                </span>
                            </div>

                            <div class="space-y-6">
                                <div v-if="environment.description">
                                    <h4 class="text-sm font-medium text-gray-500 mb-2">Description</h4>
                                    <p class="text-gray-900">{{ environment.description }}</p>
                                </div>

                                <div class="pt-4 border-t border-gray-200">
                                    <h4 class="text-sm font-medium text-gray-500 mb-4">Lieux associés ({{ environment.places ? environment.places.length : 0 }})</h4>
                                    <div v-if="environment.places && environment.places.length > 0" class="space-y-2">
                                        <div v-for="place in environment.places" :key="place.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ place.nom }}</p>
                                                <span class="text-xs text-gray-500">
                                                    {{ Number(place.latitude).toFixed(5) }}, {{ Number(place.longitude).toFixed(5) }}
                                                </span>
                                            </div>
                                            <Link :href="route('places.show', place.id)" class="text-indigo-600 hover:text-indigo-900 text-sm">
                                                Voir
                                            </Link>
                                        </div>
                                    </div>
                                    <p v-else class="text-sm text-gray-500">Aucun lieu associé à cet environnement.</p>
                                </div>

                                <div class="pt-4 border-t border-gray-200">
                                    <h4 class="text-sm font-medium text-gray-500 mb-2">Informations</h4>
                                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <dt class="text-sm text-gray-500">Créé le</dt>
                                            <dd class="text-sm text-gray-900">{{ new Date(environment.created_at).toLocaleDateString('fr-FR') }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm text-gray-500">Mis à jour le</dt>
                                            <dd class="text-sm text-gray-900">{{ new Date(environment.updated_at).toLocaleDateString('fr-FR') }}</dd>
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
