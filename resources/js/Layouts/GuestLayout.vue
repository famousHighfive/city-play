<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen w-full flex bg-[#f6f6f6]">

        <!-- LEFT SIDE (desktop only) -->
        <div class="hidden lg:flex w-1/2 bg-black text-white relative">

            <div class="absolute inset-0 opacity-20 bg-gradient-to-br from-white/10 to-transparent"></div>

            <div class="relative z-10 flex flex-col justify-center px-20">
                <Link href="/">
                    <ApplicationLogo class="h-12 w-12 text-white" />
                </Link>

                <h1 class="mt-10 text-5xl font-semibold">
                    CITYPLAY
                </h1>

                <p class="mt-6 text-white/60 max-w-md">
                    Plateforme interactive de jeux et d’expériences.
                </p>
            </div>

        </div>

        <!-- RIGHT SIDE (form) -->
        <div class="w-full lg:w-1/2 flex items-center justify-center px-6 py-12">

            <div class="w-full max-w-xl">

                <!-- mobile logo -->
                <div class="lg:hidden flex justify-center mb-8">
                    <Link href="/">
                        <ApplicationLogo class="h-12 w-12 text-black" />
                    </Link>
                </div>

                <slot />

            </div>

        </div>

    </div>
</template>