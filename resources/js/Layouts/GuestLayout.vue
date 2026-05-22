<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen w-full flex bg-slate-950 text-slate-100 font-sans">

        <!-- LEFT SIDE (Aventure & GIF - Desktop uniquement) -->
        <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden bg-slate-900 border-r border-white/5">
            
            <!-- Le GIF d'aventure en arrière-plan complet du volet gauche -->
            <div class="absolute inset-0 z-0">
                <img 
                    src="https://media0.giphy.com/media/v1.Y2lkPTc5MGI3NjExMGN4Z2w0c3M0MnNsdTYzZGRtenIxdGx3YXhrcjdhcWxqZWxxZHY2cyZlcD12MV9pbnRlcm5hbF9naWZfYnlfaWQmY3Q9Zw/3o6Ztm62DeSStT0gCY/giphy.gif" 
                    alt="Adventure Animation" 
                    class="w-full h-full object-cover object-center scale-105"
                />
                <!-- Voile sombre et lissant pour rendre l'image reposante à l'œil -->
                <div class="absolute inset-0 bg-slate-950/70 backdrop-blur-[2px]"></div>
            </div>

            <!-- Contenu textuel par-dessus le GIF -->
            <div class="relative z-10 flex flex-col justify-between p-16 w-full h-full bg-gradient-to-t from-slate-950 via-transparent to-slate-950/50">
                
                <Link href="/" class="inline-flex items-center gap-3 group">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 backdrop-blur-md transition group-hover:border-emerald-400">
                        <ApplicationLogo class="h-6 w-6 text-emerald-400" />
                    </div>
                    <div>
                        <span class="text-sm font-bold tracking-widest text-white block">CITYPLAY</span>
                        <span class="text-[10px] text-emerald-400 font-medium tracking-widest uppercase block">Bénin</span>
                    </div>
                </Link>

                <div class="max-w-md">
                    <h1 class="text-4xl font-bold tracking-tight text-white leading-tight">
                        L'aventure interactive au bout des doigts.
                    </h1>
                    <p class="mt-4 text-sm text-slate-400 font-light leading-relaxed">
                        Rejoignez la communauté d'explorateurs, parcourez les cités historiques du Bénin et résolvez des énigmes uniques en temps réel.
                    </p>
                </div>

                <div class="text-xs text-slate-500 font-light tracking-wide">
                    Expédition Bénin — Tous droits réservés.
                </div>
            </div>

        </div>

        <!-- RIGHT SIDE (Formulaire flexible et épuré) -->
        <div class="w-full lg:w-1/2 flex items-center justify-center px-6 py-12 bg-slate-950">

            <div class="w-full max-w-md">

                <!-- Logo Mobile unique (Caché sur desktop) -->
                <div class="lg:hidden flex justify-center mb-10">
                    <Link href="/" class="flex flex-col items-center gap-2">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                            <ApplicationLogo class="h-6 w-6 text-emerald-400" />
                        </div>
                        <span class="text-xs font-bold tracking-widest text-white uppercase">CITYPLAY · BÉNIN</span>
                    </Link>
                </div>

                <!-- Injection de la page login -->
                <slot />

            </div>

        </div>

    </div>
</template>