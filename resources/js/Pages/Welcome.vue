<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Head title="CITYPLAY - Explore Benin" />

    <!-- Conteneur Principal fluide et immersif -->
    <div class="relative min-h-screen text-slate-100 font-sans overflow-x-hidden bg-slate-950">
        
        <!-- Zone Arrière-plan : Emplacement pour votre GIF ou Vidéo d'aventure -->
        <div class="fixed inset-0 z-0 overflow-hidden">
            <!-- REMPLACEZ L'URL CI-DESSOUS PAR VOTRE GIF OU VIDÉO -->
            <img 
                src="https://media0.giphy.com/media/v1.Y2lkPTc5MGI3NjExMGN4Z2w0c3M0MnNsdTYzZGRtenIxdGx3YXhrcjdhcWxqZWxxZHY2cyZlcD12MV9pbnRlcm5hbF9naWZfYnlfaWQmY3Q9Zw/3o6Ztm62DeSStT0gCY/giphy.gif" 
                alt="Adventure Background" 
                class="w-full h-full object-cover scale-105 transition-transform duration-1000"
            />
            <!-- Voile sombre et doux pour apaiser l'œil et faire ressortir le texte -->
            <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-[2px]"></div>
        </div>

        <!-- Navbar Moderne & Translucide -->
        <header class="relative z-10 border-b border-white/10 bg-white/[0.04] backdrop-blur-md">
            <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-6">

                <!-- Logo Épuré -->
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-emerald-400 to-teal-500 text-white font-bold shadow-lg shadow-emerald-500/20">
                        CP
                    </div>
                    <div>
                        <h1 class="text-lg font-bold tracking-wider text-white">CITYPLAY</h1>
                        <p class="text-[11px] text-emerald-400 font-medium tracking-widest uppercase">Benin Edition</p>
                    </div>
                </div>

                <!-- Navigation -->
                <nav v-if="canLogin" class="flex items-center gap-4">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="rounded-xl bg-white/10 px-5 py-2.5 text-sm font-medium text-white backdrop-blur-md transition hover:bg-white/20"
                    >
                        Dashboard
                    </Link>

                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="rounded-xl bg-white/10 px-5 py-2.5 text-sm font-medium text-white backdrop-blur-md transition hover:bg-white/20 border border-white/10"
                        >
                            Se connecter
                        </Link>

                        <!-- BOUTON D'INSCRIPTION NAVBAR COMMENTÉ
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="rounded-xl bg-emerald-500 px-5 py-2.5 text-sm font-medium text-slate-950 transition hover:bg-emerald-400 shadow-lg shadow-emerald-500/20"
                        >
                            Créer un compte
                        </Link>
                        -->
                    </template>
                </nav>

            </div>
        </header>

        <!-- Hero Section -->
        <main class="relative z-10">
            <section class="mx-auto flex min-h-[75vh] max-w-4xl flex-col items-center justify-center px-6 text-center pt-12">

                <!-- Badge dispo fluide -->
                <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-4 py-1.5 text-xs font-medium text-emerald-400 backdrop-blur-md">
                    <span class="flex h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Nouvelle mise à jour : Exploration Bénin
                </div>

                <!-- Titre Fluide & Moderne -->
                <h1 class="text-4xl font-bold tracking-tight text-white md:text-6xl max-w-3xl leading-[1.15]">
                    L'aventure immersive au cœur du <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-teal-300">Bénin</span>
                </h1>

                <!-- Descriptif épuré -->
                <p class="mt-6 max-w-2xl text-base md:text-lg leading-relaxed text-slate-300/90 font-light">
                    Découvrez une nouvelle façon d'explorer les richesses du Bénin. Résolvez des énigmes en temps réel, parcourez des lieux chargés d'histoire et débloquez des parcours interactifs guidés par votre curiosité.
                </p>

                <!-- Boutons d'action -->
                <div class="mt-10 flex flex-col gap-4 sm:flex-row">
                    
                    <!-- BOUTON D'INSCRIPTION CENTRAL COMMENTÉ
                    <Link
                        :href="route('register')"
                        class="rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 px-8 py-4 text-sm font-semibold text-slate-950 shadow-xl shadow-emerald-500/10 transition hover:opacity-90 hover:scale-[1.01] active:scale-[0.99]"
                    >
                        Commencer l'aventure
                    </Link>
                    -->

                    <Link
                        :href="route('login')"
                        class="rounded-xl border border-white/20 bg-white/[0.06] px-8 py-4 text-sm font-medium text-white backdrop-blur-md transition hover:bg-white/[0.12] hover:scale-[1.01] active:scale-[0.99]"
                    >
                        Lancer une quête
                    </Link>

                </div>

            </section>

            <!-- Section Cartes Flexibles et Apaisantes (Infos Utiles) -->
            <section class="mx-auto grid max-w-7xl gap-6 px-6 pb-24 md:grid-cols-3">
                
                <!-- Carte 1 -->
                <div class="group rounded-2xl border border-white/[0.08] bg-slate-900/40 p-8 backdrop-blur-xl transition-all duration-300 hover:border-emerald-500/30 hover:bg-slate-900/50 hover:-translate-y-1">
                    <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white tracking-wide">Culture & Sites Sacrés</h3>
                    <p class="mt-3 text-sm leading-relaxed text-slate-400 font-light">
                        De la Cité lacustre de **Ganvié** aux Palais Royaux d'**Abomey**. 
                        <span class="block mt-2 text-emerald-400/90 text-xs font-medium">Conseil : Respectez les coutumes locales et privilégiez les guides certifiés sur l'application.</span>
                    </p>
                </div>

                <!-- Carte 2 -->
                <div class="group rounded-2xl border border-white/[0.08] bg-slate-900/40 p-8 backdrop-blur-xl transition-all duration-300 hover:border-emerald-500/30 hover:bg-slate-900/50 hover:-translate-y-1">
                    <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white tracking-wide">Réseau & Mobilité</h3>
                    <p class="mt-3 text-sm leading-relaxed text-slate-400 font-light">
                        Pour jouer en toute fluidité dans les zones urbaines comme Cotonou ou historiques comme Ouidah.
                        <span class="block mt-2 text-emerald-400/90 text-xs font-medium">Conseil : Une carte SIM locale (MTN ou Moov) avec un forfait internet actif est idéale pour la géolocalisation.</span>
                    </p>
                </div>

                <!-- Carte 3 -->
                <div class="group rounded-2xl border border-white/[0.08] bg-slate-900/40 p-8 backdrop-blur-xl transition-all duration-300 hover:border-emerald-500/30 hover:bg-slate-900/50 hover:-translate-y-1">
                    <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15a4.5 4.5 0 0 0 4.5 4.5H18a3.75 3.75 0 0 0 1.332-7.257 3 3 0 0 0-3.758-3.848 5.25 5.25 0 0 0-10.233 2.33A4.502 4.502 0 0 0 2.25 15Z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white tracking-wide">Climat & Équipement</h3>
                    <p class="mt-3 text-sm leading-relaxed text-slate-400 font-light">
                        Des pistes du Parc de la **Pendjari** aux zones côtières du Sud.
                        <span class="block mt-2 text-emerald-400/90 text-xs font-medium">Conseil : Prévoyez des vêtements légers, une batterie externe pour votre smartphone et de l'eau pour vos sessions de marche.</span>
                    </p>
                </div>

            </section>
        </main>

        <!-- Footer Discret -->
        <footer class="relative z-10 border-t border-white/5 bg-slate-950/80 py-8 text-center text-xs text-slate-500 tracking-wide font-light">
            CITYPLAY — Bénin Exploration — Laravel v{{ laravelVersion }} · PHP v{{ phpVersion }}
        </footer>

    </div>
</template>