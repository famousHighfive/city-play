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
    evenements: {
        type: Array,
        default: () => [],
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

const startGame = (environmentId) => {
    router.visit(`${route('game.configure', environmentId)}?nouvelle=1`);
};

const resumeGame = (gameId) => {
    router.visit(route('game.resume', gameId));
};

const formatDate = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

// Calcul du périmètre SVG pour l'animation du graphique circulaire
const strokeDashoffset = computed(() => {
    const percentage = props.stats.pourcentage_global ?? 0;
    const radius = 40;
    const circumference = 2 * Math.PI * radius;
    return circumference - (percentage / 100) * circumference;
});
</script>

<template>
    <Head title="Camp de Base" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-4 w-full">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-emerald-400 animate-spin-slow" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707m12.728 6.364A9 9 0 113 12a9 9 0 0115.364 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9l1 3h-2l1-3z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15l-1-3h2l-1 3z" />
                    </svg>
                    <h2 class="text-xl font-black tracking-wider text-white uppercase">
                        Tableau d'Exploration
                    </h2>
                </div>
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2 text-xs font-black text-indigo-700 uppercase tracking-widest transition hover:bg-indigo-100"
                    @click="router.visit(route('game.history'))"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Historique
                </button>
            </div>
        </template>

        <!-- CONTENEUR PRINCIPAL AVEC BACKDROP ET IMAGE IMAGE UNPLASH -->
        <div class="relative min-h-screen py-12 antialiased overflow-hidden bg-slate-950 text-slate-100">
            <!-- Image Unsplash modifiable en arrière-plan -->
            <div 
                class="absolute inset-0 bg-cover bg-center bg-no-repeat opacity-25 scale-105 pointer-events-none blur-xs transition-all duration-1000"
                style="background-image: url('https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1920&q=80');"
            ></div>
            <!-- Overlay sombre progressif pour garantir la lisibilité -->
            <div class="absolute inset-0 bg-gradient-to-b from-slate-950/90 via-slate-900/95 to-slate-950 pointer-events-none"></div>

            <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-12">

                <!-- Notifications Flasques -->
                <div v-if="page.props.flash?.success" class="rounded-2xl bg-emerald-500/10 border border-emerald-500/20 backdrop-blur-md p-4 text-sm text-emerald-300 shadow-lg shadow-emerald-950/20 flex items-center gap-3 animate-fade-in">
                    <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span class="font-medium">{{ page.props.flash.success }}</span>
                </div>

                <!-- SECTION DES STATS ET GRAPH NATIVE CHART -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-stretch">
                    <!-- Les Petites Cartes de Stats -->
                    <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <!-- Points XP -->
                        <div class="bg-slate-900/60 backdrop-blur-md rounded-2xl border border-indigo-500/30 p-6 flex flex-col justify-between transition-all duration-300 hover:border-indigo-500 group shadow-lg shadow-indigo-500/10">
                            <div class="w-12 h-12 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400 group-hover:scale-110 transition-transform">
                                <span class="text-xl font-black">✨</span>
                            </div>
                            <div class="mt-4">
                                <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest">Niveau {{ stats.level }}</p>
                                <p class="text-3xl font-black text-white mt-0.5">{{ stats.xp }} <span class="text-sm text-indigo-400 font-medium">XP</span></p>
                            </div>
                        </div>

                        <div class="bg-slate-900/60 backdrop-blur-md rounded-2xl border border-slate-800 p-6 flex flex-col justify-between transition-all duration-300 hover:border-slate-700 group shadow-lg">
                            <div class="w-12 h-12 rounded-xl bg-slate-800/80 border border-slate-700 flex items-center justify-center text-slate-300 group-hover:text-emerald-400 transition-colors">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0022 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" /></svg>
                            </div>
                            <div class="mt-4">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Expéditions</p>
                                <p class="text-3xl font-black text-white mt-0.5">{{ stats.total_parties }}</p>
                            </div>
                        </div>

                        <div class="bg-slate-900/60 backdrop-blur-md rounded-2xl border border-slate-800 p-6 flex flex-col justify-between transition-all duration-300 hover:border-slate-700 group shadow-lg">
                            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                            </div>
                            <div class="mt-4">
                                <p class="text-[10px] font-black text-emerald-400 uppercase tracking-widest">En cours</p>
                                <p class="text-3xl font-black text-emerald-400 mt-0.5">{{ stats.parties_en_cours }}</p>
                            </div>
                        </div>

                        <div class="bg-slate-900/60 backdrop-blur-md rounded-2xl border border-slate-800 p-6 flex flex-col justify-between transition-all duration-300 hover:border-slate-700 group shadow-lg">
                            <div class="w-12 h-12 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                            </div>
                            <div class="mt-4">
                                <p class="text-[10px] font-black text-blue-400 uppercase tracking-widest">Énigmes Résolues</p>
                                <p class="text-3xl font-black text-white mt-0.5">
                                    {{ stats.enigmes_resolues }}<span class="text-sm text-slate-500 font-medium">/{{ stats.enigmes_total }}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- LE GRAPH CHART RADIAL DYNAMIQUE -->
                    <div class="bg-slate-900/60 backdrop-blur-md rounded-2xl border border-slate-800 p-6 flex items-center justify-between shadow-xl">
                        <div class="space-y-2">
                            <h4 class="text-sm font-bold tracking-wide text-white">Exploration Globale</h4>
                            <p class="text-xs text-slate-400 leading-relaxed">Progression totale calculée sur l'ensemble de vos quêtes cartographiées.</p>
                            <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-white/5 text-[11px] text-slate-300 border border-white/10">
                                Statut Actif
                            </div>
                        </div>
                        
                        <!-- Graphique en Anneau en SVG Pur -->
                        <div class="relative flex items-center justify-center shrink-0 w-28 h-28">
                            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                                <!-- Cercle Arrière-plan -->
                                <circle cx="50" cy="50" r="40" stroke="#1e293b" stroke-width="8" fill="transparent" />
                                <!-- Cercle Animé de Progression -->
                                <circle 
                                    cx="50" cy="50" r="40" 
                                    stroke="#34d399" stroke-width="8" fill="transparent" 
                                    stroke-linecap="round"
                                    class="transition-all duration-1000 ease-out"
                                    :stroke-dasharray="2 * Math.PI * 40"
                                    :stroke-dashoffset="strokeDashoffset"
                                />
                            </svg>
                            <!-- Texte au centre -->
                            <div class="absolute text-center">
                                <span class="text-xl font-black text-white block">{{ stats.pourcentage_global }}%</span>
                                <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wider">Complété</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ÉVÉNEMENTS PLANIFIÉS -->
                <div v-if="evenements.length > 0" class="space-y-6">
                    <div class="flex items-center gap-2 text-slate-400">
                        <span class="flex h-2 w-2 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                        </span>
                        <h3 class="text-xs font-black uppercase tracking-widest text-slate-400">Événements communautaires</h3>
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                        <div v-for="event in evenements" :key="event.id" class="group bg-gradient-to-br from-indigo-900/40 to-slate-900/40 backdrop-blur-md rounded-3xl p-1 border border-indigo-500/20 overflow-hidden transition-all duration-500 hover:border-indigo-500/50 hover:-translate-y-2 shadow-2xl">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="px-3 py-1 rounded-full bg-indigo-500/20 border border-indigo-500/30 text-[10px] font-black text-indigo-300 uppercase tracking-tighter">
                                        {{ formatDate(event.date_evenement) }}
                                    </div>
                                    <div class="flex items-center gap-1 text-amber-400 text-xs font-black">
                                        <span>+{{ event.points_xp_bonus }}</span>
                                        <span class="text-[10px]">XP</span>
                                    </div>
                                </div>
                                
                                <h4 class="text-xl font-black text-white mb-2 group-hover:text-indigo-300 transition-colors">{{ event.nom }}</h4>
                                <p class="text-xs text-slate-400 line-clamp-2 mb-6 leading-relaxed">{{ event.description }}</p>
                                
                                <div class="flex items-center gap-3 mb-6 p-3 rounded-2xl bg-white/5 border border-white/5">
                                    <div class="w-10 h-10 rounded-xl bg-slate-800 flex items-center justify-center text-xl shadow-inner">📍</div>
                                    <div>
                                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">Lieu de rdv</p>
                                        <p class="text-sm font-bold text-slate-200">{{ event.environment.nom }}</p>
                                    </div>
                                </div>
                                
                                <button @click="startGame(event.environment_id)" class="w-full py-3 bg-white text-slate-950 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-indigo-400 hover:text-white transition-all duration-300 shadow-lg shadow-white/5">
                                    Rejoindre l'événement
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- EXPÉDITIONS ACTIVES -->
                <div v-if="partiesEnCours.length > 0" class="space-y-6">
                    <div class="flex items-center gap-2 text-slate-400">
                        <span class="flex h-2 w-2 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <h3 class="text-xs font-black uppercase tracking-widest text-slate-400">Aventures en cours</h3>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <!-- Card d'aventure animée -->
                        <div v-for="game in partiesEnCours" :key="game.id" class="group relative bg-slate-900/40 backdrop-blur-md rounded-2xl p-6 border border-slate-800 flex flex-col justify-between transition-all duration-500 hover:-translate-y-2 hover:border-emerald-500/40 shadow-xl hover:shadow-emerald-950/20 overflow-hidden">
                            <!-- Lueur magique diffuse au survol -->
                            <div class="absolute -inset-px bg-gradient-to-br from-emerald-500/10 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>

                            <div class="relative z-10">
                                <div class="flex items-start justify-between gap-4 mb-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700 flex items-center justify-center text-emerald-400 shadow-inner group-hover:scale-110 transition-transform duration-300">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                        </div>
                                        <div>
                                            <h4 class="text-base font-black text-white tracking-wide">{{ game.environment.nom }}</h4>
                                            <p class="text-xs text-slate-500">Déployé le {{ formatDate(game.date_debut) }}</p>
                                        </div>
                                    </div>
                                    <span class="shrink-0 inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider ring-1 ring-inset backdrop-blur-xs" :class="badgeStatutClass(game.statut)">
                                        {{ labelStatutPartie(game.statut) }}
                                    </span>
                                </div>

                                <!-- Jauge linéaire immersive -->
                                <div class="mb-6 bg-slate-950/60 p-4 rounded-xl border border-slate-800/80">
                                    <div class="flex justify-between text-xs mb-2">
                                        <span class="text-slate-400 font-medium">Progression Immersion</span>
                                        <span class="font-black text-emerald-400">{{ game.progression.pourcentage_avancement }}%</span>
                                    </div>
                                    <div class="h-2 bg-slate-800 rounded-full overflow-hidden p-[2px]">
                                        <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full transition-all duration-700 shadow-glow" :style="{ width: game.progression.pourcentage_avancement + '%' }" />
                                    </div>
                                    <p v-if="game.etape_reprise" class="text-xs text-slate-300 mt-3 flex items-center gap-1.5 font-medium">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                        Reprendre à l'énigme {{ game.etape_reprise }} sur {{ game.progression.total }}
                                    </p>
                                </div>

                                <!-- Compteurs Data Grid -->
                                <div class="grid grid-cols-4 gap-2 mb-6 text-center text-xs">
                                    <div class="bg-slate-950/30 p-2 rounded-lg border border-slate-800/40">
                                        <p class="font-black text-white">{{ game.progression.resolues }}</p>
                                        <p class="text-[9px] text-slate-500 font-bold uppercase tracking-wider mt-0.5">Succès</p>
                                    </div>
                                    <div class="bg-slate-950/30 p-2 rounded-lg border border-slate-800/40">
                                        <p class="font-black text-rose-400">{{ game.progression.non_resolues }}</p>
                                        <p class="text-[9px] text-slate-500 font-bold uppercase tracking-wider mt-0.5">Échecs</p>
                                    </div>
                                    <div class="bg-slate-950/30 p-2 rounded-lg border border-slate-800/40">
                                        <p class="font-black text-slate-400">{{ game.progression.passees }}</p>
                                        <p class="text-[9px] text-slate-500 font-bold uppercase tracking-wider mt-0.5">Sautées</p>
                                    </div>
                                    <div class="bg-slate-950/30 p-2 rounded-lg border border-slate-800/40">
                                        <p class="font-black text-blue-400">{{ game.progression.a_faire }}</p>
                                        <p class="text-[9px] text-slate-500 font-bold uppercase tracking-wider mt-0.5">Restant</p>
                                    </div>
                                </div>

                                <!-- Configuration Métadonnées -->
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1.5 text-[11px] text-slate-400 border-t border-slate-800/60 pt-3 mb-6">
                                    <span class="flex items-center gap-1"><svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg> {{ labelMode(game.mode_jeu) }} ({{ game.nb_membres }})</span>
                                    <span class="text-slate-700">•</span>
                                    <span>{{ labelDifficulte(game.niveau_difficulte) }}</span>
                                    <span class="text-slate-700">•</span>
                                    <span class="flex items-center gap-1"><svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg> {{ labelLocomotion(game.moyen_locomotion) }}</span>
                                </div>
                            </div>

                            <button @click="resumeGame(game.id)" class="relative z-10 w-full rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 py-3 text-xs font-black text-white uppercase tracking-widest transition-all duration-300 hover:brightness-110 shadow-lg shadow-emerald-900/20">
                                Continuer le parcours
                            </button>
                        </div>
                    </div>
                </div>

                <!-- TERRITOIRES / VILLES DISPONIBLES -->
                <div class="space-y-6">
                    <div class="flex items-center gap-2 text-slate-400">
                        <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0022 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" /></svg>
                        <h3 class="text-xs font-black uppercase tracking-widest text-slate-400">Territoires cartographiés</h3>
                    </div>

                    <div v-if="environments.length === 0" class="text-center py-12 bg-slate-900/40 backdrop-blur-md rounded-2xl border border-slate-800">
                        <p class="text-slate-400 text-sm font-medium">Aucun univers n'a encore été découvert.</p>
                    </div>

                    <div v-else class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <div v-for="env in environments" :key="env.id" class="group relative bg-slate-900/40 backdrop-blur-md rounded-2xl p-6 border border-slate-800 flex flex-col justify-between transition-all duration-500 hover:-translate-y-1.5 hover:border-blue-500/40 shadow-xl overflow-hidden">
                            
                            <!-- Radar Ping Lumineux -->
                            <div class="absolute top-5 right-5 flex h-2.5 w-2.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75" :class="env.partie_active ? 'bg-emerald-400' : 'bg-blue-400'"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5" :class="env.partie_active ? 'bg-emerald-500' : 'bg-blue-500'"></span>
                            </div>

                            <div>
                                <div class="w-11 h-11 rounded-xl bg-slate-800/60 border border-slate-700/60 flex items-center justify-center text-slate-300 mb-4 transition-transform group-hover:scale-110 duration-300">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                </div>
                                
                                <h4 class="text-base font-bold text-white tracking-wide mb-2">
                                    {{ env.nom }}
                                </h4>
                                
                                <p v-if="env.description" class="text-xs text-slate-400 leading-relaxed line-clamp-3 mb-5">
                                    {{ env.description }}
                                </p>
                                <p v-else class="text-[11px] italic text-slate-500 mb-5">Aucun document topographique trouvé.</p>

                                <div v-if="env.partie_active" class="mb-5 rounded-xl bg-emerald-500/5 border border-emerald-500/20 px-3 py-2 text-xs text-emerald-400 flex items-center gap-2 font-semibold">
                                    <svg class="w-3.5 h-3.5 animate-pulse" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728m-9.9-2.829a5 5 0 010-7.071m7.072 0a5 5 0 010 7.071M13 12a1 1 0 11-2 0 1 1 0 012 0z" /></svg>
                                    Session à l'étape {{ env.partie_active.etape_reprise ?? '?' }}
                                </div>
                            </div>

                            <div class="flex flex-col gap-2 pt-3 border-t border-slate-800/40">
                                <button v-if="env.partie_active" @click="resumeGame(env.partie_active.id)" class="w-full rounded-xl bg-emerald-600/90 py-2.5 text-xs font-bold text-white shadow-md hover:bg-emerald-600 transition">
                                    Reprendre l'exploration
                                </button>
                                <button v-if="env.peut_nouvelle_partie" @click="startGame(env.id)" class="w-full rounded-xl py-2.5 text-xs font-bold transition-all duration-300 uppercase tracking-wider"
                                    :class="env.partie_active
                                        ? 'border border-slate-700 text-slate-300 bg-transparent hover:bg-slate-800'
                                        : 'bg-white text-slate-950 hover:bg-slate-200 shadow-md'"
                                >
                                    {{ env.partie_active ? 'Session Parallèle' : 'Découvrir la ville' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MISSIONS ACCOMPLIES -->
                <div v-if="partiesTerminees.length > 0" class="space-y-4">
                    <div class="flex items-center gap-2 text-slate-500">
                        <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                        <h3 class="text-xs font-black uppercase tracking-widest">Missions Accomplies</h3>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                        <div v-for="game in partiesTerminees" :key="game.id" class="bg-slate-900/30 backdrop-blur-md rounded-xl p-5 border border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-md">
                            <div class="space-y-1">
                                <h4 class="text-sm font-bold text-white tracking-wide">{{ game.environment.nom }}</h4>
                                <p class="text-xs text-slate-500">
                                    Terminé le {{ formatDate(game.date_fin ?? game.date_debut) }}
                                </p>
                                <div class="text-xs text-slate-400">
                                    Taux de réussite : <span class="text-amber-400 font-black">{{ game.progression.pourcentage_resolues }}%</span> ({{ game.progression.resolues }} / {{ game.progression.total }} résolues)
                                </div>
                            </div>
                            <button @click="startGame(game.environment_id)" class="shrink-0 rounded-xl border border-slate-700 bg-slate-800/40 px-4 py-2 text-xs font-bold text-slate-300 hover:bg-slate-800 transition-colors uppercase tracking-wider">
                                Recommencer la quête
                            </button>
                        </div>
                    </div>
                </div>

                <!-- CONTENEUR DES ARCHIVES (AUTRES STATUTS) -->
                <div v-if="autresParties.length > 0" class="space-y-4">
                    <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-500">Chroniques Archivées</h3>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <div v-for="game in autresParties" :key="game.id" class="bg-slate-900/20 backdrop-blur-xs rounded-xl p-4 border border-slate-800/60 flex items-center justify-between gap-3 shadow-sm">
                            <div class="overflow-hidden">
                                <h4 class="text-xs font-bold text-slate-300 truncate">{{ game.environment.nom }}</h4>
                                <p class="text-[11px] text-slate-500">
                                    {{ game.progression.resolues }} R. · {{ game.progression.non_resolues }} É.
                                </p>
                            </div>
                            <span class="shrink-0 inline-flex rounded-full px-2 py-0.5 text-[9px] font-bold tracking-wide ring-1 ring-inset" :class="badgeStatutClass(game.statut)">
                                {{ labelStatutPartie(game.statut) }}
                            </span>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Ajout d'utilitaires d'animations natifs Tailwind complémentaires si nécessaire */
.animate-spin-slow {
    animation: spin 12s linear infinite;
}
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>