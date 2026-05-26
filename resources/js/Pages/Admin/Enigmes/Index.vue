<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import ConfirmDialog from 'primevue/confirmdialog';
import { useConfirm } from 'primevue/useconfirm';
import { ref, computed } from 'vue';

const props = defineProps({
    enigmes: {
        type: Array,
        required: true
    }
});

const confirm = useConfirm();
const form = useForm();
const searchQuery = ref('');
const niveauFilter = ref('all');
const statusFilter = ref('all');

const niveauxDisponibles = ['easy', 'medium', 'hard', 'kid'];

const filteredEnigmes = computed(() => {
    return props.enigmes.filter(enigme => {
        const matchesSearch = !searchQuery.value || 
            enigme.texte.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            (enigme.place && enigme.place.nom.toLowerCase().includes(searchQuery.value.toLowerCase()));
        
        const matchesNiveau = niveauFilter.value === 'all' || enigme.niveau === niveauFilter.value;
        
        const matchesStatus = statusFilter.value === 'all' || 
            (statusFilter.value === 'active' && enigme.actif) ||
            (statusFilter.value === 'inactive' && !enigme.actif);
        
        return matchesSearch && matchesNiveau && matchesStatus;
    });
});

const deleteEnigme = (enigme) => {
    confirm.require({
        message: 'Êtes-vous sûr de vouloir supprimer cette énigme ?',
        header: 'Confirmation de suppression',
        icon: 'pi pi-exclamation-triangle',
        accept: () => {
            form.delete(route('enigmes.destroy', enigme.id));
        }
    });
};

const getNiveauClass = (niveau) => {
    switch (niveau) {
        case 'easy': return 'bg-green-50 text-green-700 ring-green-600/20';
        case 'medium': return 'bg-blue-50 text-blue-700 ring-blue-600/20';
        case 'hard': return 'bg-red-50 text-red-700 ring-red-600/20';
        case 'kid': return 'bg-purple-50 text-purple-700 ring-purple-600/20';
        default: return 'bg-gray-50 text-gray-700 ring-gray-600/20';
    }
};
</script>

<template>
    <Head title="Énigmes" />

    <AppLayout>
        <template #default>
            <header class="bg-white shadow">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        Liste des Énigmes
                    </h2>
                    
                    <!-- CORRECTION : 'enigmes.create' au lieu de 'admin.enigmes.create' -->
                    <Link 
                        :href="route('enigmes.create')" 
                        class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                    >
                        + Nouvelle Énigme
                    </Link>
                </div>
            </header>

            <div class="py-12">
                <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6">
                        
                        <!-- Filtres et recherche -->
                        <div class="mb-6 flex flex-col sm:flex-row gap-4">
                            <input 
                                v-model="searchQuery" 
                                type="text" 
                                placeholder="Rechercher par texte ou lieu..." 
                                class="flex-1 rounded-lg border-gray-300 px-4 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                            <select 
                                v-model="niveauFilter" 
                                class="rounded-lg border-gray-300 px-4 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="all">Tous les niveaux</option>
                                <option v-for="niveau in niveauxDisponibles" :key="niveau" :value="niveau">
                                    {{ niveau === 'kid' ? 'kid' : 'Niveau ' + niveau }}
                                </option>
                            </select>
                            <select 
                                v-model="statusFilter" 
                                class="rounded-lg border-gray-300 px-4 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="all">Tous les statuts</option>
                                <option value="active">Actif</option>
                                <option value="inactive">Inactif</option>
                            </select>
                        </div>
                        
                        <!-- Liste Vide -->
                        <div v-if="filteredEnigmes.length === 0" class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">
                                {{ props.enigmes.length === 0 ? 'Aucune énigme' : 'Aucune énigme ne correspond à vos filtres' }}
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ props.enigmes.length === 0 ? 'Ajoutez votre première énigme liée à un lieu de votre carte.' : 'Essayez de modifier vos critères de recherche.' }}
                            </p>
                        </div>

                        <!-- Tableau de données -->
                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aperçu</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Place</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Énigme / Texte</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Niveau</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="enigme in filteredEnigmes" :key="enigme.id" class="hover:bg-gray-50">
                                        
                                        <!-- Image d'illustration -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <img 
                                                v-if="enigme.image" 
                                                :src="`/storage/${enigme.image}`" 
                                                alt="Illustration" 
                                                class="h-10 w-10 rounded-md object-cover border border-gray-200"
                                            />
                                            <div v-else class="h-10 w-10 rounded-md bg-gray-100 flex items-center justify-center text-gray-400 text-xs font-medium">
                                                N/A
                                            </div>
                                        </td>

                                        <!-- Place associée -->
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                            {{ enigme.place ? enigme.place.nom : 'Lieu inconnu' }}
                                        </td>

                                        <!-- Contenu Texte -->
                                        <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                                            {{ enigme.texte }}
                                        </td>

                                        <!-- Niveau Difficulté -->
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span 
                                                class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset uppercase"
                                                :class="getNiveauClass(enigme.niveau)"
                                            >
                                                {{ enigme.niveau === 'enfant' ? 'Enfant' : 'Niveau ' + enigme.niveau }}
                                            </span>
                                        </td>

                                        <!-- Statut Actif -->
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span 
                                                class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset"
                                                :class="enigme.actif 
                                                    ? 'bg-green-50 text-green-700 ring-green-600/20' 
                                                    : 'bg-red-50 text-red-700 ring-red-600/20'"
                                            >
                                                {{ enigme.actif ? 'Actif' : 'Inactif' }}
                                            </span>
                                        </td>

                                        <!-- Actions -->
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                            <Link :href="route('enigmes.show', enigme.id)" class="text-gray-600 hover:text-gray-900">
                                                Voir
                                            </Link>
                                            <Link :href="route('enigmes.edit', enigme.id)" class="text-indigo-600 hover:text-indigo-900">
                                                Modifier
                                            </Link>
                                            <button @click="deleteEnigme(enigme)" class="text-red-600 hover:text-red-900">
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
