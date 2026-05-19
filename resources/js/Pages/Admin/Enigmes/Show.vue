<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    enigme: {
        type: Object,
        required: true
    }
});

const getNiveauClass = (niveau) => {
    switch (niveau) {
        case '1': return 'bg-green-50 text-green-700 ring-green-600/20';
        case '2': return 'bg-blue-50 text-blue-700 ring-blue-600/20';
        case '3': return 'bg-red-50 text-red-700 ring-red-600/20';
        case 'enfant': return 'bg-purple-50 text-purple-700 ring-purple-600/20';
        default: return 'bg-gray-50 text-gray-700 ring-gray-600/20';
    }
};
</script>

<template>
    <Head :title="`Énigme - ${enigme.id}`" />

    <AppLayout>
        <template #default>
            <header class="bg-white shadow">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        <Link :href="route('enigmes.index')" class="text-gray-600 hover:text-gray-800">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </Link>
                        <h2 class="text-xl font-semibold leading-tight text-gray-800">
                            Détail de l'Énigme
                        </h2>
                    </div>
                    <div class="flex space-x-3">
                        <Link :href="route('enigmes.edit', enigme.id)" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
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
                                    <h3 class="text-lg font-medium text-gray-900">
                                        {{ enigme.place ? enigme.place.nom : 'Lieu inconnu' }}
                                    </h3>
                                    <span 
                                        class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset uppercase mt-2"
                                        :class="getNiveauClass(enigme.niveau)"
                                    >
                                        {{ enigme.niveau === 'enfant' ? 'Enfant' : 'Niveau ' + enigme.niveau }}
                                    </span>
                                </div>
                                <span 
                                    class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset"
                                    :class="enigme.actif 
                                        ? 'bg-green-50 text-green-700 ring-green-600/20' 
                                        : 'bg-red-50 text-red-700 ring-red-600/20'"
                                >
                                    {{ enigme.actif ? 'Actif' : 'Inactif' }}
                                </span>
                            </div>

                            <div v-if="enigme.image" class="mb-6">
                                <img 
                                    :src="`/storage/${enigme.image}`" 
                                    alt="Illustration" 
                                    class="w-full h-64 object-cover rounded-lg"
                                />
                            </div>

                            <div class="space-y-6">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 mb-2">Texte de l'énigme</h4>
                                    <p class="text-gray-900">{{ enigme.texte }}</p>
                                </div>

                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 mb-2">Solution</h4>
                                    <p class="text-gray-900">{{ enigme.solution }}</p>
                                </div>

                                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-500 mb-2">Premier indice</h4>
                                        <p class="text-gray-900">{{ enigme.indice_1 }}</p>
                                    </div>
                                    <div v-if="enigme.indice_2">
                                        <h4 class="text-sm font-medium text-gray-500 mb-2">Deuxième indice</h4>
                                        <p class="text-gray-900">{{ enigme.indice_2 }}</p>
                                    </div>
                                </div>

                                <div class="pt-4 border-t border-gray-200">
                                    <h4 class="text-sm font-medium text-gray-500 mb-2">Informations</h4>
                                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div>
                                            <dt class="text-sm text-gray-500">Créé le</dt>
                                            <dd class="text-sm text-gray-900">{{ new Date(enigme.created_at).toLocaleDateString('fr-FR') }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm text-gray-500">Mis à jour le</dt>
                                            <dd class="text-sm text-gray-900">{{ new Date(enigme.updated_at).toLocaleDateString('fr-FR') }}</dd>
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
