<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    usersCount: Number,
    gamesCount: Number,
    environmentsCount: Number,
    enigmesCount: Number,
    recentGames: Array,
});
</script>

<template>
    <Head title="Dashboard Admin" />

    <AppLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-gray-800">
                Dashboard Admin
            </h2>
        </template>

        <div class="py-10 space-y-8">

            <!-- STATS -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <p class="text-sm text-gray-500">Utilisateurs</p>
                    <p class="text-3xl font-semibold mt-2">
                        {{ usersCount }}
                    </p>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <p class="text-sm text-gray-500">Parties</p>
                    <p class="text-3xl font-semibold mt-2">
                        {{ gamesCount }}
                    </p>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <p class="text-sm text-gray-500">Environnements</p>
                    <p class="text-3xl font-semibold mt-2">
                        {{ environmentsCount }}
                    </p>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <p class="text-sm text-gray-500">Énigmes</p>
                    <p class="text-3xl font-semibold mt-2">
                        {{ enigmesCount }}
                    </p>
                </div>

            </div>

            <!-- ACTIONS RAPIDES -->
            <div class="bg-white rounded-2xl p-6 border border-gray-100">

                <h3 class="text-lg font-semibold mb-4">
                    Actions rapides
                </h3>

                <div class="flex flex-wrap gap-3">

                    <a href="/environments/create"
                       class="px-4 py-2 rounded-xl bg-black text-white text-sm hover:bg-black/80">
                        + Environnement
                    </a>

                    <a href="/places/create"
                       class="px-4 py-2 rounded-xl bg-gray-100 text-black text-sm hover:bg-gray-200">
                        + Place
                    </a>

                    <a href="/enigmes/create"
                       class="px-4 py-2 rounded-xl bg-gray-100 text-black text-sm hover:bg-gray-200">
                        + Énigme
                    </a>

                    <a href="/events"
                       class="px-4 py-2 rounded-xl bg-indigo-100 text-indigo-700 text-sm hover:bg-indigo-200">
                        📅 Événements
                    </a>

                </div>

            </div>

            <!-- RECENT ACTIVITY -->
            <div class="bg-white rounded-2xl p-6 border border-gray-100">

                <h3 class="text-lg font-semibold mb-4">
                    Activité récente
                </h3>

                <div class="space-y-3">

                    <div
                        v-for="game in recentGames"
                        :key="game.id"
                        class="flex justify-between items-center p-3 rounded-xl bg-gray-50"
                    >
                        <div>
                            <p class="font-medium">
                                Partie #{{ game.id }}
                            </p>
                            <p class="text-sm text-gray-500">
                                {{ game.environment?.name }}
                            </p>
                        </div>

                        <span class="text-xs text-gray-400">
                            {{ game.created_at }}
                        </span>
                    </div>

                </div>

            </div>

            <!-- GRAPH PLACEHOLDER -->
            <div class="bg-white rounded-2xl p-6 border border-gray-100">

                <h3 class="text-lg font-semibold mb-4">
                    Statistiques globales
                </h3>

                <div class="h-48 flex items-center justify-center text-gray-400">
                    Graph (à brancher avec Chart.js ou ApexCharts)
                </div>

            </div>

        </div>

    </AppLayout>
</template>