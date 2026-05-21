<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    environments: {
        type: Array,
        required: true,
    },
    games: {
        type: Array,
        required: true,
    },
    stats: {
        type: Object,
        required: true,
    },
    labels: {
        type: Object,
        required: true,
    },
});

const page = usePage();

const partiesEnCours = computed(() =>
    props.games.filter((g) => g.peut_reprendre)
);

const partiesTerminees = computed(() =>
    props.games.filter((g) => g.statut === 'terminee')
);

const autresParties = computed(() =>
    props.games.filter((g) => !['en_cours', 'terminee'].includes(g.statut))
);

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
            return 'bg-green-50 text-green-700 ring-green-600/20';
        case 'terminee':
            return 'bg-indigo-50 text-indigo-700 ring-indigo-600/20';
        case 'pause':
            return 'bg-yellow-50 text-yellow-700 ring-yellow-600/20';
        case 'abandonnee':
            return 'bg-red-50 text-red-700 ring-red-600/20';
        default:
            return 'bg-gray-50 text-gray-700 ring-gray-600/20';
    }
};

const startGame = (environmentId) => {
    router.visit(`${route('game.configure', environmentId)}?nouvelle=1`);
};

const resumeGame = (gameId) => {
    router.visit(route('game.resume', gameId));
};

const formatDate = (date) => {
    if (!date) {
        return '—';
    }
    return new Date(date).toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Tableau de bord
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-8">

                <div
                    v-if="page.props.flash?.success"
                    class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800"
                >
                    {{ page.props.flash.success }}
                </div>

                <div
                    v-if="page.props.flash?.info"
                    class="rounded-lg bg-blue-50 border border-blue-200 px-4 py-3 text-sm text-blue-800"
                >
                    {{ page.props.flash.info }}
                </div>

                <div
                    v-if="page.props.flash?.error"
                    class="rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800"
                >
                    {{ page.props.flash.error }}
                </div>

                <!-- Synthèse globale -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <p class="text-xs font-medium text-gray-500 uppercase">Parties</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">{{ stats.total_parties }}</p>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <p class="text-xs font-medium text-gray-500 uppercase">En cours</p>
                        <p class="text-3xl font-bold text-green-600 mt-1">{{ stats.parties_en_cours }}</p>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <p class="text-xs font-medium text-gray-500 uppercase">Énigmes résolues</p>
                        <p class="text-3xl font-bold text-indigo-600 mt-1">
                            {{ stats.enigmes_resolues }}
                            <span class="text-lg text-gray-400 font-normal">/ {{ stats.enigmes_total }}</span>
                        </p>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <p class="text-xs font-medium text-gray-500 uppercase">Progression globale</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">{{ stats.pourcentage_global }}%</p>
                        <div class="mt-2 h-2 bg-gray-100 rounded-full overflow-hidden">
                            <div
                                class="h-full bg-indigo-600 rounded-full transition-all"
                                :style="{ width: stats.pourcentage_global + '%' }"
                            />
                        </div>
                    </div>
                </div>

                <!-- Parties en cours -->
                <div v-if="partiesEnCours.length > 0">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        Parties en cours
                    </h3>
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                        <div
                            v-for="game in partiesEnCours"
                            :key="game.id"
                            class="bg-white shadow-sm sm:rounded-xl p-6 border border-gray-100"
                        >
                            <div class="flex items-start justify-between gap-3 mb-4">
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-900">
                                        {{ game.environment.nom }}
                                    </h4>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        Début : {{ formatDate(game.date_debut) }}
                                    </p>
                                </div>
                                <span
                                    class="shrink-0 inline-flex rounded-md px-2.5 py-1 text-xs font-semibold ring-1 ring-inset"
                                    :class="badgeStatutClass(game.statut)"
                                >
                                    {{ labelStatutPartie(game.statut) }}
                                </span>
                            </div>

                            <!-- Barre de progression -->
                            <div class="mb-4">
                                <div class="flex justify-between text-xs text-gray-600 mb-1">
                                    <span>Avancement</span>
                                    <span>{{ game.progression.pourcentage_avancement }}%</span>
                                </div>
                                <div class="h-2.5 bg-gray-100 rounded-full overflow-hidden">
                                    <div
                                        class="h-full bg-indigo-500 rounded-full"
                                        :style="{ width: game.progression.pourcentage_avancement + '%' }"
                                    />
                                </div>
                                <p
                                    v-if="game.etape_reprise"
                                    class="text-xs text-indigo-600 mt-2 font-medium"
                                >
                                    Reprendre à l'énigme {{ game.etape_reprise }} / {{ game.progression.total }}
                                </p>
                            </div>

                            <!-- Détail énigmes -->
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-4">
                                <div class="rounded-lg bg-green-50 px-3 py-2 text-center">
                                    <p class="text-lg font-bold text-green-700">{{ game.progression.resolues }}</p>
                                    <p class="text-[10px] uppercase text-green-600 font-medium">Résolues</p>
                                </div>
                                <div class="rounded-lg bg-amber-50 px-3 py-2 text-center">
                                    <p class="text-lg font-bold text-amber-700">{{ game.progression.non_resolues }}</p>
                                    <p class="text-[10px] uppercase text-amber-600 font-medium">Non résolues</p>
                                </div>
                                <div class="rounded-lg bg-gray-50 px-3 py-2 text-center">
                                    <p class="text-lg font-bold text-gray-600">{{ game.progression.passees }}</p>
                                    <p class="text-[10px] uppercase text-gray-500 font-medium">Passées</p>
                                </div>
                                <div class="rounded-lg bg-slate-50 px-3 py-2 text-center">
                                    <p class="text-lg font-bold text-slate-600">{{ game.progression.a_faire }}</p>
                                    <p class="text-[10px] uppercase text-slate-500 font-medium">À faire</p>
                                </div>
                            </div>

                            <div class="text-sm text-gray-500 space-y-1 mb-4 border-t border-gray-50 pt-3">
                                <p v-if="game.mode_jeu">{{ labelMode(game.mode_jeu) }} · {{ game.nb_membres }} membre(s)</p>
                                <p>{{ labelDifficulte(game.niveau_difficulte) }} · {{ labelLocomotion(game.moyen_locomotion) }}</p>
                                <p>Durée : {{ game.duree_restante ?? game.duree_prevue }} min</p>
                            </div>

                            <button
                                @click="resumeGame(game.id)"
                                class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500 transition"
                            >
                                Reprendre la partie
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Parties terminées -->
                <div v-if="partiesTerminees.length > 0">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        Parties terminées
                    </h3>
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                        <div
                            v-for="game in partiesTerminees"
                            :key="game.id"
                            class="bg-white shadow-sm sm:rounded-xl p-6 border border-indigo-100"
                        >
                            <div class="flex items-start justify-between gap-3 mb-4">
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-900">
                                        {{ game.environment.nom }}
                                    </h4>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        {{ formatDate(game.date_debut) }}
                                        <span v-if="game.date_fin"> → {{ formatDate(game.date_fin) }}</span>
                                    </p>
                                </div>
                                <span
                                    class="shrink-0 inline-flex rounded-md px-2.5 py-1 text-xs font-semibold ring-1 ring-inset"
                                    :class="badgeStatutClass('terminee')"
                                >
                                    {{ labelStatutPartie('terminee') }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-3">
                                <div class="rounded-lg bg-green-50 px-3 py-2 text-center">
                                    <p class="text-lg font-bold text-green-700">{{ game.progression.resolues }}</p>
                                    <p class="text-[10px] uppercase text-green-600 font-medium">Résolues</p>
                                </div>
                                <div class="rounded-lg bg-amber-50 px-3 py-2 text-center">
                                    <p class="text-lg font-bold text-amber-700">{{ game.progression.non_resolues }}</p>
                                    <p class="text-[10px] uppercase text-amber-600 font-medium">Non résolues</p>
                                </div>
                                <div class="rounded-lg bg-gray-50 px-3 py-2 text-center">
                                    <p class="text-lg font-bold text-gray-600">{{ game.progression.passees }}</p>
                                    <p class="text-[10px] uppercase text-gray-500 font-medium">Passées</p>
                                </div>
                                <div class="rounded-lg bg-slate-50 px-3 py-2 text-center">
                                    <p class="text-lg font-bold text-slate-600">{{ game.progression.total }}</p>
                                    <p class="text-[10px] uppercase text-slate-500 font-medium">Total</p>
                                </div>
                            </div>

                            <p class="text-sm text-gray-600 mb-4">
                                Taux de réussite :
                                <span class="font-semibold text-indigo-600">
                                    {{ game.progression.pourcentage_resolues }}%
                                </span>
                                ({{ game.progression.resolues }} énigme(s) trouvée(s) sur {{ game.progression.total }})
                            </p>

                            <button
                                @click="startGame(game.environment_id)"
                                class="w-full rounded-lg border border-indigo-200 bg-white px-4 py-2.5 text-sm font-semibold text-indigo-700 hover:bg-indigo-50"
                            >
                                Rejouer une nouvelle partie
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Autres statuts (pause, abandonnée…) -->
                <div v-if="autresParties.length > 0">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Autres parties</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div
                            v-for="game in autresParties"
                            :key="game.id"
                            class="bg-white shadow-sm rounded-lg p-5"
                        >
                            <div class="flex justify-between items-center mb-2">
                                <h4 class="font-medium">{{ game.environment.nom }}</h4>
                                <span
                                    class="text-xs px-2 py-1 rounded-md ring-1 ring-inset"
                                    :class="badgeStatutClass(game.statut)"
                                >
                                    {{ labelStatutPartie(game.statut) }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-500">
                                {{ game.progression.resolues }} résolue(s) ·
                                {{ game.progression.non_resolues }} non résolue(s)
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Villes / nouvelle partie -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">
                        {{ environments.length === 1 ? 'Votre ville' : 'Vos villes' }}
                    </h3>

                    <div
                        v-if="environments.length === 0"
                        class="text-center py-12 bg-white shadow-sm sm:rounded-lg"
                    >
                        <p class="text-gray-500">Aucune invitation acceptée pour le moment.</p>
                    </div>

                    <div
                        v-else
                        class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"
                    >
                        <div
                            v-for="env in environments"
                            :key="env.id"
                            class="bg-white shadow-sm sm:rounded-xl p-6 border border-gray-100"
                        >
                            <h4 class="text-md font-semibold text-gray-900 mb-1">{{ env.nom }}</h4>
                            <p v-if="env.description" class="text-sm text-gray-500 mb-4 line-clamp-2">
                                {{ env.description }}
                            </p>

                            <div
                                v-if="env.partie_active"
                                class="mb-4 rounded-lg bg-green-50 border border-green-100 px-3 py-2 text-xs text-green-800"
                            >
                                Partie en cours — reprendre à l'énigme
                                {{ env.partie_active.etape_reprise ?? '?' }}
                                / {{ env.partie_active.progression.total }}
                                ({{ env.partie_active.progression.resolues }} résolue(s))
                            </div>

                            <div class="flex flex-col gap-2">
                                <button
                                    v-if="env.partie_active"
                                    @click="resumeGame(env.partie_active.id)"
                                    class="w-full rounded-lg bg-green-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-green-500"
                                >
                                    Reprendre où j'en étais
                                </button>
                                <button
                                    v-if="env.peut_nouvelle_partie"
                                    @click="startGame(env.id)"
                                    class="w-full rounded-lg px-4 py-2.5 text-sm font-semibold transition"
                                    :class="env.partie_active
                                        ? 'border border-indigo-200 text-indigo-700 bg-white hover:bg-indigo-50'
                                        : 'bg-indigo-600 text-white hover:bg-indigo-500'"
                                >
                                    {{ env.partie_active ? 'Nouvelle partie' : 'Commencer une partie' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-if="games.length === 0 && environments.length > 0"
                    class="text-center py-8 bg-white rounded-xl border border-dashed border-gray-200"
                >
                    <p class="text-gray-500 text-sm">
                        Aucune partie pour l'instant. Choisissez une ville ci-dessus pour commencer.
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
