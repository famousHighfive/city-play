<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Connexion" />

        <!-- En-tête du formulaire -->
        <div class="mb-8">
            <div class="inline-flex items-center gap-2 rounded-full border border-emerald-500/20 bg-emerald-500/5 px-3 py-1 text-[11px] font-medium text-emerald-400 mb-3">
                <span class="flex h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                Portail Explorateur
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-white">
                Ravi de vous revoir
            </h1>
            <p class="text-xs text-slate-400 mt-1 font-light leading-relaxed">
                Connectez-vous pour continuer vos quêtes urbaines et historiques.
            </p>
        </div>

        <!-- Statut de session fluide -->
        <div v-if="status" class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-xs text-emerald-400">
            {{ status }}
        </div>

        <!-- Formulaire moderne -->
        <form @submit.prevent="submit" class="space-y-5">

            <!-- CHAMP : EMAIL -->
            <div class="space-y-1.5">
                <InputLabel for="email" value="Votre adresse email" class="text-xs font-medium text-slate-300 tracking-wide pl-1" />
                <TextInput
                    id="email"
                    type="email"
                    v-model="form.email"
                    class="w-full px-4 py-3 rounded-xl border border-white/10 bg-white/[0.02] text-white focus:border-emerald-500/40 focus:ring-0 transition-all duration-200 placeholder-slate-600 text-sm"
                    placeholder="nom@exemple.com"
                    required
                    autofocus
                    autocomplete="username"
                />
                <InputError :message="form.errors.email" class="mt-1.5 text-xs text-rose-400/90 pl-1" />
            </div>

            <!-- CHAMP : MOT DE PASSE -->
            <div class="space-y-1.5">
                <InputLabel for="password" value="Votre mot de passe" class="text-xs font-medium text-slate-300 tracking-wide pl-1" />
                <TextInput
                    id="password"
                    type="password"
                    v-model="form.password"
                    class="w-full px-4 py-3 rounded-xl border border-white/10 bg-white/[0.02] text-white focus:border-emerald-500/40 focus:ring-0 transition-all duration-200 placeholder-slate-600 text-sm"
                    placeholder="••••••••"
                    required
                    autocomplete="current-password"
                />
                <InputError :message="form.errors.password" class="mt-1.5 text-xs text-rose-400/90 pl-1" />
            </div>

            <!-- OPTIONS : RETENIR & SOUVENIR -->
            <div class="flex items-center justify-between text-xs py-1 px-1">
                <label class="flex items-center gap-2.5 cursor-pointer text-slate-400 hover:text-slate-200 transition-colors duration-200 select-none">
                    <Checkbox v-model:checked="form.remember" class="rounded border-white/20 bg-white/5 text-emerald-500 focus:ring-0 focus:ring-offset-0 transition-all duration-200" />
                    <span class="font-light">Se souvenir de moi</span>
                </label>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-slate-400 hover:text-emerald-400 transition-colors duration-200 font-light"
                >
                    Mot de passe oublié ?
                </Link>
            </div>

            <!-- BOUTON DE SOUMISSION UNIQUE -->
            <div class="pt-2">
                <PrimaryButton
                    class="w-full py-3.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 font-semibold tracking-wide text-sm justify-center shadow-lg shadow-emerald-500/5 transition-all duration-200 active:scale-[0.99]"
                    :class="{ 'opacity-40 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Lancement de la session...' : 'Rejoindre la quête' }}
                </PrimaryButton>
            </div>

            <!-- ZONE D'INSCRIPTION DISCRÈTEMENT CACHÉE (COMMENTÉE) -->
            <!-- 
            <div class="text-center text-xs mt-6 pt-4 border-t border-white/5 text-slate-500">
                Nouveau sur la plateforme ? 
                <Link :href="route('register')" class="text-emerald-400 hover:text-emerald-300 ml-1 font-medium transition-colors">
                    Créer un compte explorateur
                </Link>
            </div> 
            -->

        </form>
    </GuestLayout>
</template>