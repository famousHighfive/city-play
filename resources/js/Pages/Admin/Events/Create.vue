<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    environments: Array,
});

const form = useForm({
    nom: '',
    description: '',
    environment_id: '',
    date_evenement: '',
    points_xp_bonus: 50,
});

const submit = () => {
    form.post(route('events.store'));
};
</script>

<template>
    <Head title="Planifier un Événement" />

    <AppLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-gray-800">
                Planifier un Événement
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nom de l'événement</label>
                            <input v-model="form.nom" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea v-model="form.description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Parcours / Environnement</label>
                            <select v-model="form.environment_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="" disabled>Sélectionnez un parcours</option>
                                <option v-for="env in environments" :key="env.id" :value="env.id">{{ env.nom }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date et Heure</label>
                            <input v-model="form.date_evenement" type="datetime-local" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">XP Bonus</label>
                            <input v-model="form.points_xp_bonus" type="number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
                        </div>

                        <div class="flex items-center justify-end">
                            <button type="submit" :disabled="form.processing" class="bg-black text-white px-4 py-2 rounded-xl hover:bg-black/80 disabled:opacity-50">
                                Planifier l'événement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
