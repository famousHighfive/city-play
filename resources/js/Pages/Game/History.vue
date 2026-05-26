<script setup>
/**
 * Page Historique : liste exhaustive de toutes les aventures du joueur.
 * Chaque nouvelle partie sur un territoire ajoute une entrée (les anciennes ne sont plus supprimées).
 */
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
const props = defineProps({
    games: {
        type: Array,
        required: true,
    },
    labels: {
        type: Object,
        required: true,
    },
});

const labelStatutPartie = (statut) =>
    props.labels.statuts_partie?.[statut] ?? statut;

const labelMode = (mode) => props.labels.modes?.[mode] ?? mode;

const labelDifficulte = (niveau) =>
    props.labels.niveaux_difficulte?.[String(niveau)] ?? niveau;

const labelLocomotion = (moyen) =>
    props.labels.moyens_locomotion?.[moyen] ?? moyen;

const badgeStatutClass = (statut) => {
    switch (statut) {
        case 'en_cours':
            return 'bg-emerald-500/10 text-emerald-400 ring-emerald-500/30';
        case 'terminee':
            return 'bg-amber-500/10 text-amber-400 ring-amber-500/30';
        case 'pause':
            return 'bg-orange-500/10 text-orange-400 ring-orange-500/30';
        case 'abandonnee':
            return 'bg-rose-500/10 text-rose-400 ring-rose-500/30';
        default:
            return 'bg-slate-500/10 text-slate-400 ring-slate-500/30';
    }
};

const formatDate = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const resumeGame = (gameId) => {
    router.visit(route('game.resume', gameId));
};

const startGame = (environmentId) => {
    router.visit(`${route('game.configure', environmentId)}?nouvelle=1`);
};

// Numéro d'aventure affiché (#1 = la plus récente dans la liste)
const numeroAventure = (index) => props.games.length - index;
</script>

<template>
    <Head title="Historique des parties" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-4 w-full">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h2 class="text-xl font-black tracking-wider text-gray-800 uppercase">
                        Chronique des aventures
                    </h2>
                </div>
                <Link
                    :href="route('dashboard')"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-bold text-slate-600 uppercase tracking-wider transition hover:bg-slate-50"
                >
                    ← Retour au camp de base
                </Link>
            </div>
        </template>

        <div class="relative min-h-screen py-12 antialiased overflow-hidden bg-slate-950 text-slate-100">
            <div
                class="absolute inset-0 bg-cover bg-center bg-no-repeat opacity-20 pointer-events-none blur-xs"
                style="background-image: url('https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1920&q=80');"
            />
            <div class="absolute inset-0 bg-gradient-to-b from-slate-950/90 via-slate-900/95 to-slate-950 pointer-events-none" />

            <div class="relative mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 space-y-8">
                <p class="text-sm text-slate-400">
                    Toutes vos expéditions, sur tous les territoires —
                    {{ games.length }} aventure{{ games.length > 1 ? 's' : '' }} au total
                </p>

                <div
                    v-if="games.length === 0"
                    class="text-center py-16 bg-slate-900/40 backdrop-blur-md rounded-2xl border border-slate-800"
                >
                    <p class="text-slate-400 text-sm font-medium">Aucune partie jouée pour le moment.</p>
                    <Link
                        :href="route('dashboard')"
                        class="mt-6 inline-block rounded-xl bg-white px-6 py-2.5 text-xs font-black text-slate-950 uppercase tracking-widest hover:bg-slate-200 transition"
                    >
                        Explorer un territoire
                    </Link>
                </div>

                <!-- Une carte par partie en base (y compris plusieurs sur la même ville) -->
                <div v-else class="space-y-4">
                    <div
                        v-for="(game, index) in games"
                        :key="game.id"
                        class="bg-slate-900/50 backdrop-blur-md rounded-2xl p-6 border border-slate-800 shadow-lg transition hover:border-slate-700"
                    >
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                            <div class="space-y-2 min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-3">
                                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">
                                        Aventure #{{ numeroAventure(index) }}
                                    </span>
                                    <h3 class="text-base font-black text-white tracking-wide">
                                        {{ game.environment.nom }}
                                    </h3>
                                    <span
                                        class="shrink-0 inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider ring-1 ring-inset"
                                        :class="badgeStatutClass(game.statut)"
                                    >
                                        {{ labelStatutPartie(game.statut) }}
                                    </span>
                                </div>

                                <p class="text-xs text-slate-500">
                                    Début : {{ formatDate(game.date_debut) }}
                                    <span v-if="game.date_fin"> · Fin : {{ formatDate(game.date_fin) }}</span>
                                </p>

                                <div class="flex flex-wrap gap-x-3 gap-y-1 text-[11px] text-slate-400">
                                    <span>{{ labelMode(game.mode_jeu) }} ({{ game.nb_membres }})</span>
                                    <span class="text-slate-700">•</span>
                                    <span>{{ labelDifficulte(game.niveau_difficulte) }}</span>
                                    <span class="text-slate-700">•</span>
                                    <span>{{ labelLocomotion(game.moyen_locomotion) }}</span>
                                </div>

                                <div class="pt-2">
                                    <div class="flex justify-between text-xs mb-1.5">
                                        <span class="text-slate-400">Progression</span>
                                        <span class="font-black text-emerald-400">
                                            {{ game.progression.pourcentage_avancement }}%
                                        </span>
                                    </div>
                                    <div class="h-1.5 bg-slate-800 rounded-full overflow-hidden">
                                        <div
                                            class="h-full bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full transition-all"
                                            :style="{ width: game.progression.pourcentage_avancement + '%' }"
                                        />
                                    </div>
                                    <p class="text-[11px] text-slate-500 mt-2">
                                        {{ game.progression.resolues }} résolues ·
                                        {{ game.progression.non_resolues }} échecs ·
                                        {{ game.progression.passees }} passées ·
                                        {{ game.progression.a_faire }} restantes
                                        ({{ game.progression.total }} au total)
                                    </p>
                                </div>
                            </div>

                            <div class="flex flex-col gap-2 shrink-0 sm:w-44">
                                <button
                                    v-if="game.peut_reprendre"
                                    type="button"
                                    class="w-full rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 py-2.5 text-xs font-black text-white uppercase tracking-widest hover:brightness-110 transition"
                                    @click="resumeGame(game.id)"
                                >
                                    Reprendre
                                </button>
                                <button
                                    v-else-if="['terminee', 'abandonnee'].includes(game.statut)"
                                    type="button"
                                    class="w-full rounded-xl border border-slate-700 bg-slate-800/40 py-2.5 text-xs font-bold text-slate-300 hover:bg-slate-800 transition uppercase tracking-wider"
                                    @click="startGame(game.environment_id)"
                                >
                                    Nouvelle expédition
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
