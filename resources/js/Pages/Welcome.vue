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

function handleImageError() {
    document.getElementById('screenshot-container')?.classList.add('!hidden');
    document.getElementById('docs-card')?.classList.add('!row-span-1');
    document.getElementById('docs-card-content')?.classList.add('!flex-row');
    document.getElementById('background')?.classList.add('!hidden');
}
</script>
<template>
    <Head title="CITYPLAY" />

    <div class="min-h-screen bg-[#f5f5f2] text-[#111111] overflow-hidden">

        <!-- Background -->
        <div class="absolute inset-0 opacity-40">
            <div class="absolute top-0 left-0 h-[500px] w-[500px] rounded-full bg-[#d6d3d1] blur-3xl"></div>
            <div class="absolute bottom-0 right-0 h-[400px] w-[400px] rounded-full bg-[#e7e5e4] blur-3xl"></div>
        </div>

        <!-- Navbar -->
        <header class="relative z-10">
            <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-6">

                <!-- Logo -->
                <div class="flex items-center gap-4">

                    <div class="h-10 w-10 rounded-2xl border border-black/10 bg-white/70 backdrop-blur-xl"></div>

                    <div>
                        <h1 class="text-lg font-semibold tracking-wide">
                            CITYPLAY
                        </h1>

                        <p class="text-xs text-black/40">
                            Urban Exploration
                        </p>
                    </div>

                </div>

                <!-- Navigation -->
                <nav
                    v-if="canLogin"
                    class="flex items-center gap-3"
                >

                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="rounded-2xl border border-black/10 bg-white/70 px-5 py-2 text-sm font-medium text-black/70 backdrop-blur-xl transition hover:bg-white"
                    >
                        Dashboard
                    </Link>

                    <template v-else>

                        <Link
                            :href="route('login')"
                            class="rounded-2xl border border-black/10 bg-white/70 px-5 py-2 text-sm font-medium text-black/70 backdrop-blur-xl transition hover:bg-white"
                        >
                            Connexion
                        </Link>

                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="rounded-2xl bg-[#111111] px-5 py-2 text-sm font-medium text-white transition hover:bg-black"
                        >
                            Commencer
                        </Link>

                    </template>

                </nav>

            </div>
        </header>

        <!-- Hero -->
        <main class="relative z-10">

            <section class="mx-auto flex min-h-[88vh] max-w-7xl flex-col items-center justify-center px-6 text-center">

                <!-- Mini badge -->
                <div class="mb-8 rounded-full border border-black/10 bg-white/60 px-5 py-2 text-sm text-black/50 backdrop-blur-xl">
                    Nouvelle expérience interactive
                </div>

                <!-- Main title -->
                <h1 class="max-w-5xl text-5xl font-semibold leading-tight tracking-tight md:text-7xl">

                    Explorez les villes
                    à travers des
                    expériences immersives.

                </h1>

                <!-- Subtitle -->
                <p class="mt-8 max-w-2xl text-lg leading-relaxed text-black/50">

                    CITYPLAY transforme les lieux historiques et populaires
                    en parcours interactifs intelligents composés d’énigmes,
                    d’indices et d’exploration en temps réel.

                </p>

                <!-- Buttons -->
                <div class="mt-12 flex flex-col gap-4 sm:flex-row">

                    <Link
                        :href="route('register')"
                        class="rounded-2xl bg-[#111111] px-8 py-4 text-sm font-medium text-white shadow-xl transition hover:scale-[1.02]"
                    >
                        Créer un compte
                    </Link>

                    <Link
                        :href="route('login')"
                        class="rounded-2xl border border-black/10 bg-white/70 px-8 py-4 text-sm font-medium text-black/70 backdrop-blur-xl transition hover:bg-white"
                    >
                        Se connecter
                    </Link>

                </div>

            </section>

            <!-- Cards -->
            <section class="mx-auto grid max-w-7xl gap-6 px-6 pb-24 md:grid-cols-3">

                <div class="rounded-[28px] border border-black/10 bg-white/60 p-8 backdrop-blur-xl">

                    <div class="mb-6 h-12 w-12 rounded-2xl bg-black/5"></div>

                    <h3 class="text-xl font-semibold">
                        Parcours dynamiques
                    </h3>

                    <p class="mt-4 text-sm leading-relaxed text-black/50">
                        Créez des expériences urbaines modernes basées sur
                        des lieux réels et des scénarios interactifs.
                    </p>

                </div>

                <div class="rounded-[28px] border border-black/10 bg-white/60 p-8 backdrop-blur-xl">

                    <div class="mb-6 h-12 w-12 rounded-2xl bg-black/5"></div>

                    <h3 class="text-xl font-semibold">
                        Gestion des énigmes
                    </h3>

                    <p class="mt-4 text-sm leading-relaxed text-black/50">
                        Organisez des niveaux, indices et solutions
                        avec une administration simple et élégante.
                    </p>

                </div>

                <div class="rounded-[28px] border border-black/10 bg-white/60 p-8 backdrop-blur-xl">

                    <div class="mb-6 h-12 w-12 rounded-2xl bg-black/5"></div>

                    <h3 class="text-xl font-semibold">
                        Expérience fluide
                    </h3>

                    <p class="mt-4 text-sm leading-relaxed text-black/50">
                        Interface moderne pensée pour le mobile,
                        la géolocalisation et l’immersion utilisateur.
                    </p>

                </div>

            </section>

        </main>

        <!-- Footer -->
        <footer class="relative z-10 border-t border-black/5 py-8 text-center text-sm text-black/40">
            CITYPLAY — Laravel v{{ laravelVersion }} · PHP v{{ phpVersion }}
        </footer>

    </div>
</template>