<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { useAudioStore } from '@/stores/audio';
import { onMounted } from 'vue';

const props = defineProps({
    game: Object,
    stats: Object,
});

const audioStore = useAudioStore();

const retourDashboard = () => {
    audioStore.play('click');
    router.visit(route('dashboard'));
};

onMounted(() => {
    audioStore.play('success');
});
</script>

<template>
    <Head title="Mission Accomplie" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-slate-950 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
            <!-- Background effects -->
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute bottom-1/4 right-1/4 w-80 h-80 bg-emerald-500/10 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s"></div>
            </div>

            <div class="relative z-10 max-w-2xl w-full">
                <div class="bg-slate-900/60 backdrop-blur-xl rounded-3xl border border-white/10 shadow-2xl overflow-hidden">
                    <div class="p-8 sm:p-12 text-center">
                        <!-- Success Icon -->
                        <div class="mb-8 relative">
                            <div class="w-32 h-32 bg-gradient-to-br from-indigo-600 to-emerald-500 rounded-3xl flex items-center justify-center shadow-2xl shadow-indigo-500/30 mx-auto transform rotate-12 animate-bounce">
                                <span class="text-6xl -rotate-12">🏆</span>
                            </div>
                            <div class="absolute -top-4 -right-4 w-12 h-12 bg-amber-400 rounded-full flex items-center justify-center shadow-lg animate-ping opacity-75"></div>
                            <div class="absolute -top-4 -right-4 w-12 h-12 bg-amber-400 rounded-full flex items-center justify-center shadow-lg">
                                <span class="text-2xl">✨</span>
                            </div>
                        </div>

                        <h1 class="text-4xl sm:text-5xl font-black text-white mb-4 tracking-tight">Mission Accomplie !</h1>
                        <p class="text-indigo-300 text-lg mb-12 uppercase tracking-widest font-semibold">Expédition : {{ game.environment.nom }}</p>

                        <!-- Stats Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-12">
                            <div class="bg-white/5 border border-white/5 rounded-2xl p-6 backdrop-blur-sm group hover:bg-white/10 transition-colors">
                                <p class="text-xs text-slate-400 uppercase font-bold tracking-widest mb-2">Temps Total</p>
                                <p class="text-3xl font-black text-white tracking-tight group-hover:text-emerald-400 transition-colors">
                                    {{ stats.temps_total }}
                                </p>
                            </div>
                            <div class="bg-white/5 border border-white/5 rounded-2xl p-6 backdrop-blur-sm group hover:bg-white/10 transition-colors">
                                <p class="text-xs text-slate-400 uppercase font-bold tracking-widest mb-2">Nombre de Pauses</p>
                                <p class="text-3xl font-black text-white tracking-tight group-hover:text-amber-400 transition-colors">
                                    {{ stats.nb_pauses }}
                                </p>
                            </div>
                            <div class="bg-white/5 border border-white/5 rounded-2xl p-6 backdrop-blur-sm group hover:bg-white/10 transition-colors sm:col-span-2">
                                <p class="text-xs text-slate-400 uppercase font-bold tracking-widest mb-2">Énigmes Résolues</p>
                                <p class="text-3xl font-black text-white tracking-tight">
                                    <span class="text-emerald-400">{{ stats.enigmes_resolues }}</span>
                                    <span class="text-slate-500 mx-2">/</span>
                                    <span class="text-slate-300">{{ stats.total_enigmes }}</span>
                                </p>
                                <div class="mt-4 h-2 bg-slate-800 rounded-full overflow-hidden p-[1px]">
                                    <div 
                                        class="h-full bg-gradient-to-r from-indigo-500 to-emerald-500 rounded-full transition-all duration-1000"
                                        :style="{ width: (stats.enigmes_resolues / stats.total_enigmes * 100) + '%' }"
                                    ></div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-col sm:flex-row gap-4">
                            <button 
                                @click="retourDashboard"
                                class="flex-1 px-8 py-5 bg-gradient-to-r from-indigo-600 to-emerald-500 text-white rounded-2xl font-black text-lg shadow-xl shadow-indigo-500/20 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-3"
                            >
                                <span>🏠</span>
                                Retour au camp de base
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Footer text -->
                <p class="mt-8 text-center text-slate-500 text-sm font-medium">
                    Félicitations pour avoir complété cette exploration urbaine !
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-bounce {
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 100% {
        transform: rotate(12deg) translateY(-5%);
        animation-timing-function: cubic-bezier(0.8, 0, 1, 1);
    }
    50% {
        transform: rotate(12deg) translateY(0);
        animation-timing-function: cubic-bezier(0, 0, 0.2, 1);
    }
}
</style>
