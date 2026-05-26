<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import ConfirmDialog from 'primevue/confirmdialog';
import { useConfirm } from 'primevue/useconfirm';
import { ref, computed } from 'vue';

const props = defineProps({
    environments: {
        type: Array,
        required: true
    }
});

const confirm = useConfirm();
const form = useForm();
const searchQuery = ref('');
const statusFilter = ref('all');

const filteredEnvironments = computed(() => {
    return props.environments.filter(env => {
        const matchesSearch = !searchQuery.value || 
            env.nom.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            (env.description && env.description.toLowerCase().includes(searchQuery.value.toLowerCase()));
        
        const matchesStatus = statusFilter.value === 'all' || 
            (statusFilter.value === 'active' && env.actif) ||
            (statusFilter.value === 'inactive' && !env.actif);
        
        return matchesSearch && matchesStatus;
    });
});

const deleteEnvironment = (environment) => {
    confirm.require({
        message: 'Êtes-vous sûr de vouloir supprimer cet environnement ?',
        header: 'Confirmation de suppression',
        icon: 'pi pi-exclamation-triangle',
        accept: () => {
            form.delete(route('environments.destroy', environment.id));
        }
    });
};
</script>

<template>
    <Head title="Environnements" />

    <AppLayout>
        <template #default>
            <!-- En-tête de la page avec bouton de création -->
            <header class="bg-white shadow">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        Liste des Environnements
                    </h2>
                    
                    <!-- Lien vers le formulaire de création -->
                    <Link 
                        :href="route('environments.create')" 
                        class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                    >
                        + Nouvel Environnement
                    </Link>
                </div>
            </header>

            <!-- Table des environnements -->
            <div class="py-12">
                <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6">
                        
                        <!-- Filtres et recherche -->
                        <div class="mb-6 flex flex-col sm:flex-row gap-4">
                            <input 
                                v-model="searchQuery" 
                                type="text" 
                                placeholder="Rechercher par nom ou description..." 
                                class="flex-1 rounded-lg border-gray-300 px-4 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                            <select 
                                v-model="statusFilter" 
                                class="rounded-lg border-gray-300 px-4 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="all">Tous les statuts</option>
                                <option value="active">Actif</option>
                                <option value="inactive">Inactif</option>
                            </select>
                        </div>
                        
                        <!-- Cas où il n'y a aucun environnement en base de données -->
                        <div v-if="filteredEnvironments.length === 0" class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">
                                {{ props.environments.length === 0 ? 'Aucun environnement' : 'Aucun environnement ne correspond à vos filtres' }}
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ props.environments.length === 0 ? 'Commencez par créer votre premier environnement système.' : 'Essayez de modifier vos critères de recherche.' }}
                            </p>
                        </div>

                        <!-- Tableau des données -->
                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="env in filteredEnvironments" :key="env.id" class="hover:bg-gray-50">
                                        <!-- ID -->
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            #{{ env.id }}
                                        </td>
                                        
                                        <!-- Nom -->
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ env.nom }}
                                        </td>
                                        
                                        <!-- Description (Tronquée si trop longue) -->
                                        <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                                            {{ env.description || 'Aucune description' }}
                                        </td>
                                        
                                        <!-- Statut Actif / Inactif -->
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span 
                                                class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset"
                                                :class="env.actif 
                                                    ? 'bg-green-50 text-green-700 ring-green-600/20' 
                                                    : 'bg-red-50 text-red-700 ring-red-600/20'"
                                            >
                                                {{ env.actif ? 'Actif' : 'Inactif' }}
                                            </span>
                                        </td>
                                        
                                        <!-- Boutons d'édition future -->
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                            <Link :href="route('invitations.index', env.id)" class="text-green-600 hover:text-green-900">
                                                Invitations
                                            </Link>
                                            <Link :href="route('environments.show', env.id)" class="text-gray-600 hover:text-gray-900">
                                                Voir
                                            </Link>
                                            <Link :href="route('environments.edit', env.id)" class="text-indigo-600 hover:text-indigo-900">
                                                Modifier
                                            </Link>
                                            <button @click="deleteEnvironment(env)" class="text-red-600 hover:text-red-900">
                                                Supprimer
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        <ConfirmDialog />
        </template>
    </AppLayout>
</template>
