<script setup>
import { computed } from 'vue'
import { useForm, Head } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'

const props = defineProps({
    token: String,
    canal: String,
    environment: Object,
})

const form = useForm({
    name:                  '',
    pseudo:                '',
    destinataire:          '',
    password:              '',
    password_confirmation: '',
})

// Le placeholder change selon le canal
const placeholder = computed(() => {
    if (props.canal === 'email') return 'ton@email.com'
    return '+229 XX XX XX XX'
})

// Le label change selon le canal
const labelDestinataire = computed(() => {
    if (props.canal === 'email') return 'Ton adresse email'
    return 'Ton numéro de téléphone'
})

const submit = () => {
    form.post(route('invitation.register', { token: props.token }))
}
</script>

<template>
    <GuestLayout>
        <Head title="Invitation à jouer - CITYPLAY" />

        <!-- En-tête de l'invitation fluide -->
        <div class="mb-8">
            <div class="inline-flex items-center gap-2 rounded-full border border-emerald-500/20 bg-emerald-500/5 px-3 py-1 text-[11px] font-medium text-emerald-400 mb-3">
                <span class="flex h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                Quête Partagée
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-white">
                Tu es invité à jouer !
            </h1>
            <p class="text-xs text-slate-400 mt-1 font-light leading-relaxed">
                Rejoins l'environnement : <span class="font-medium text-emerald-400">{{ environment.nom }}</span>
            </p>
        </div>

        <!-- Formulaire immersif et apaisant à l'œil -->
        <form @submit.prevent="submit" class="space-y-5">

            <!-- CHAMP : PRÉNOM -->
            <div class="space-y-1.5">
                <label for="name" class="block text-xs font-medium text-slate-300 tracking-wide pl-1">
                    Ton prénom
                </label>
                <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    placeholder="Marie"
                    class="w-full px-4 py-3 rounded-xl border border-white/10 bg-white/[0.02] text-white focus:border-emerald-500/40 focus:ring-0 transition-all duration-200 placeholder-slate-600 text-sm"
                    required
                />
                <p v-if="form.errors.name" class="mt-1.5 text-xs text-rose-400/90 pl-1">
                    {{ form.errors.name }}
                </p>
            </div>

            <!-- CHAMP : PSEUDO -->
            <div class="space-y-1.5">
                <label for="pseudo" class="block text-xs font-medium text-slate-300 tracking-wide pl-1">
                    Choisis un pseudo
                </label>
                <input
                    id="pseudo"
                    v-model="form.pseudo"
                    type="text"
                    placeholder="Explorateur229"
                    class="w-full px-4 py-3 rounded-xl border border-white/10 bg-white/[0.02] text-white focus:border-emerald-500/40 focus:ring-0 transition-all duration-200 placeholder-slate-600 text-sm"
                    required
                />
                <p v-if="form.errors.pseudo" class="mt-1.5 text-xs text-rose-400/90 pl-1">
                    {{ form.errors.pseudo }}
                </p>
            </div>

            <!-- CHAMP : DESTINATAIRE (DYNAMIQUE) -->
            <div class="space-y-1.5">
                <label for="destinataire" class="block text-xs font-medium text-slate-300 tracking-wide pl-1">
                    {{ labelDestinataire }}
                </label>
                <input
                    id="destinataire"
                    v-model="form.destinataire"
                    :type="canal === 'email' ? 'email' : 'tel'"
                    :placeholder="placeholder"
                    class="w-full px-4 py-3 rounded-xl border border-white/10 bg-white/[0.02] text-white focus:border-emerald-500/40 focus:ring-0 transition-all duration-200 placeholder-slate-600 text-sm"
                    required
                />
                <p v-if="form.errors.destinataire" class="mt-1.5 text-xs text-rose-400/90 pl-1">
                    {{ form.errors.destinataire }}
                </p>
            </div>

            <!-- CHAMP : MOT DE PASSE -->
            <div class="space-y-1.5">
                <label for="password" class="block text-xs font-medium text-slate-300 tracking-wide pl-1">
                    Ton mot de passe
                </label>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    placeholder="••••••••"
                    class="w-full px-4 py-3 rounded-xl border border-white/10 bg-white/[0.02] text-white focus:border-emerald-500/40 focus:ring-0 transition-all duration-200 placeholder-slate-600 text-sm"
                    required
                />
                <p v-if="form.errors.password" class="mt-1.5 text-xs text-rose-400/90 pl-1">
                    {{ form.errors.password }}
                </p>
            </div>

            <!-- CHAMP : CONFIRMATION -->
            <div class="space-y-1.5">
                <label for="password_confirmation" class="block text-xs font-medium text-slate-300 tracking-wide pl-1">
                    Confirme ton mot de passe
                </label>
                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    placeholder="••••••••"
                    class="w-full px-4 py-3 rounded-xl border border-white/10 bg-white/[0.02] text-white focus:border-emerald-500/40 focus:ring-0 transition-all duration-200 placeholder-slate-600 text-sm"
                    required
                />
            </div>

            <!-- Notification du Canal (Lecture seule) -->
            <div class="rounded-xl border border-white/5 bg-white/[0.01] px-4 py-3 flex items-center justify-between text-xs backdrop-blur-sm">
                <span class="text-slate-400 font-light">
                    Code de vérification envoyé via
                </span>
                <span class="font-semibold text-emerald-400 uppercase tracking-wider bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-500/20">
                    {{ canal }}
                </span>
            </div>

            <!-- BOUTON DE SOUMISSION -->
            <div class="pt-2">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full py-3.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 font-semibold tracking-wide text-sm justify-center flex items-center shadow-lg shadow-emerald-500/5 transition-all duration-200 active:scale-[0.99] disabled:opacity-40 disabled:cursor-not-allowed"
                >
                    {{ form.processing ? 'Génération de l\'accès...' : 'Recevoir mon code' }}
                </button>
            </div>

        </form>
    </GuestLayout>
</template>