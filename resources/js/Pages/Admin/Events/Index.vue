<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    events: Array,
});
</script>

<template>
    <Head title="Événements Planifiés" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800">Événements Planifiés</h2>
                <Link :href="route('events.create')" class="px-4 py-2 bg-black text-white rounded-xl text-sm">
                    + Nouveau Événement
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lieu</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">XP Bonus</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="event in events" :key="event.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ event.nom }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ new Date(event.date_evenement).toLocaleString() }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ event.environment.nom }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ event.points_xp_bonus }} XP</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
