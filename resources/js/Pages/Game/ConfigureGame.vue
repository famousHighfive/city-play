<script setup>
import { ref, computed, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    environment: {
        type: Object,
        required: true,
    },
    gameOptions: {
        type: Object,
        required: true,
    },
    existingGame: {
        type: Object,
        default: null,
    },
    afficher_modal_mode: {
        type: Boolean,
        default: false,
    },
});

const currentStep = ref(1);
const showModeModal = ref(props.afficher_modal_mode);
const positionCaptured = ref(false);
const loadingLaunch = ref(false);

const form = useForm({
    mode_jeu: '',

    nb_membres: 1,
    participants: [],

    challenger_email: '',

    duree_prevue: props.gameOptions.duree_defaut,
    moyen_locomotion: props.gameOptions.moyens_locomotion[0]?.value ?? 'pied',
    niveau_difficulte: props.gameOptions.niveaux_difficulte[0]?.value ?? 'easy',

    latitude: null,
    longitude: null,
});

/** Nombre d'emails à saisir = coéquipiers (hors joueur connecté). */
const nbEmailsCoEquipiers = computed(() => Math.max(0, (parseInt(form.nb_membres) || 1) - 1));

watch(() => form.nb_membres, (value) => {
    let total = parseInt(value) || 1;

    if (total > props.gameOptions.max_membres) {
        total = props.gameOptions.max_membres;
        form.nb_membres = props.gameOptions.max_membres;
    }

    if (total < 1) {
        total = 1;
        form.nb_membres = 1;
    }

    const additional = Math.max(0, total - 1);

    if (additional > form.participants.length) {
        while (form.participants.length < additional) {
            form.participants.push('');
        }
    } else {
        form.participants = form.participants.slice(0, additional);
    }
});

const nextStep = () => {
    currentStep.value++;
};

const retourDashboard = () => {
    router.visit(route('dashboard'));
};

const selectMode = (mode) => {
    form.mode_jeu = mode;
    showModeModal.value = false;
};


const capturePosition = () => {
    if (!navigator.geolocation) {
        alert('La géolocalisation n’est pas disponible.');
        return;
    }

    navigator.geolocation.getCurrentPosition(
        (position) => {
            form.latitude = position.coords.latitude;
            form.longitude = position.coords.longitude;

            positionCaptured.value = true;

            setTimeout(() => {
                currentStep.value = 3;
            }, 1800);
        },
        () => {
            alert('Impossible de récupérer votre position.');
        }
    );
};

const payloadPourBackend = (data) => {

    const payload = { ...data };

    if (payload.mode_jeu === 'challenge') {

        delete payload.nb_membres;
        delete payload.participants;

    } else {

        delete payload.challenger_email;

        const nb = parseInt(payload.nb_membres) || 1;

        if (nb <= 1) {
            payload.participants = [];
        }
    }

    return payload;
};

const submit = () => {
    if (!positionCaptured.value) {
        alert('Veuillez capturer votre position avant de lancer la partie.');
        return;
    }

    loadingLaunch.value = true;

    form
        .transform(payloadPourBackend)
        .post(route('game.start', props.environment.id), {
            onFinish: () => {
                loadingLaunch.value = false;
            },
        });
};

const difficultyLabel = computed(() => {
    const niveau = props.gameOptions.niveaux_difficulte.find(
        (n) => String(n.value) === String(form.niveau_difficulte)
    );
    return niveau ? `${niveau.emoji} ${niveau.label}` : '';
});

const locomotionLabel = computed(() => {
    const moyen = props.gameOptions.moyens_locomotion.find(
        (m) => m.value === form.moyen_locomotion
    );
    return moyen ? `${moyen.emoji} ${moyen.label}` : '';
});

const modeLabel = computed(() => {
    const mode = props.gameOptions.modes.find((m) => m.value === form.mode_jeu);
    return mode ? `${mode.emoji} ${mode.label}` : form.mode_jeu;
});

const peutLancer = computed(
    () => props.gameOptions.enigmes_disponibles > 0 && positionCaptured.value
);

const reprendrePartie = () => {
    if (props.existingGame?.id) {
        router.visit(route('game.resume', props.existingGame.id));
    }
};
</script>

<template>
    <Head :title="`Configurer - ${environment.nom}`" />

    <AuthenticatedLayout>
        <template #default>
            <!-- Partie déjà en cours -->
            <div
                v-if="existingGame"
                class="mx-auto max-w-5xl px-4 pt-6"
            >
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <p class="text-sm text-amber-900">
                        Partie en cours — reprendre à l'énigme
                        <strong>{{ existingGame.etape_reprise }}</strong>
                        / {{ existingGame.progression?.total ?? '?' }}
                    </p>
                    <button
                        @click="reprendrePartie"
                        class="rounded-xl bg-amber-600 px-5 py-2 text-sm font-semibold text-white hover:bg-amber-700"
                    >
                        Reprendre où j'en étais
                    </button>
                </div>
            </div>

            <!-- MODAL CHOIX -->
            <div
                v-if="showModeModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm px-4"
            >
                <div class="w-full max-w-2xl rounded-3xl bg-white p-8 shadow-2xl relative overflow-hidden">
                    <div class="absolute top-0 right-0 h-40 w-40 bg-indigo-100 rounded-full blur-3xl opacity-60"></div>

                    <button
                        @click="retourDashboard"
                        class="mb-6 inline-flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 transition"
                    >
                        ← Retour
                    </button>

                    <div class="relative">
                        <h2 class="text-3xl font-black text-gray-800 text-center">
                            Choisissez votre mode
                        </h2>

                        <p class="mt-3 text-center text-gray-500">
                            Sélectionnez votre expérience de jeu immersive
                        </p>

                        <p
                            v-if="gameOptions.enigmes_disponibles === 0"
                            class="mt-6 text-center text-sm text-red-600 font-medium"
                        >
                            Aucune énigme active n'est disponible pour cette ville.
                        </p>

                        <div class="mt-10 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <button
                                v-for="mode in gameOptions.modes"
                                :key="mode.value"
                                @click="selectMode(mode.value)"
                                :disabled="gameOptions.enigmes_disponibles === 0"
                                class="group relative overflow-hidden rounded-3xl border p-8 text-left transition-all duration-300 hover:scale-[1.03] hover:shadow-2xl disabled:opacity-50 disabled:pointer-events-none"
                                :class="mode.value === 'equipe'
                                    ? 'border-indigo-100 bg-gradient-to-br from-indigo-50 to-indigo-100'
                                    : 'border-pink-100 bg-gradient-to-br from-pink-50 to-pink-100'"
                            >
                                <div class="text-6xl mb-5">
                                    {{ mode.emoji }}
                                </div>

                                <h3 class="text-2xl font-bold text-gray-800">
                                    {{ mode.label }}
                                </h3>

                                <p class="mt-2 text-gray-600">
                                    {{ mode.description }}
                                </p>

                                <div
                                    class="mt-6 font-semibold"
                                    :class="mode.value === 'equipe' ? 'text-indigo-600' : 'text-pink-600'"
                                >
                                    {{ mode.value === 'equipe' ? 'Jouer en groupe →' : 'Défier un joueur →' }}
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- HEADER -->
            <header class="bg-white border-b border-gray-100">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-black text-gray-800">
                            {{ environment.nom }}
                        </h1>

                        <p class="text-sm text-gray-500 mt-1">
                            Configuration de votre aventure
                            <span v-if="gameOptions.enigmes_disponibles > 0" class="text-indigo-600">
                                · {{ gameOptions.enigmes_disponibles }} énigme(s) disponible(s)
                            </span>
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <div
                            v-for="step in 3"
                            :key="step"
                            class="h-3 w-16 rounded-full transition-all duration-500"
                            :class="currentStep >= step ? 'bg-indigo-600' : 'bg-gray-200'"
                        />
                    </div>
                </div>
            </header>

            <div class="py-12">
                <div class="mx-auto max-w-5xl px-4">
                    <div class="rounded-3xl bg-white p-8 shadow-xl">
                        <!-- ETAPE 1 -->
                        <div v-if="currentStep === 1">
                            <div class="mb-10">
                                <h2 class="text-3xl font-black text-gray-800">
                                    Informations de la partie
                                </h2>

                                <p class="mt-2 text-gray-500">
                                    Configurez les paramètres de votre session.
                                </p>
                            </div>

                            <!-- MODE EQUIPE -->
                            <div
                                v-if="form.mode_jeu === 'equipe'"
                                class="space-y-8"
                            >
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                                        Nombre de membres, vous inclus (max {{ gameOptions.max_membres }})
                                    </label>

                                    <input
                                        v-model="form.nb_membres"
                                        type="number"
                                        :min="1"
                                        :max="gameOptions.max_membres"
                                        required
                                        class="w-full rounded-2xl border-gray-200 bg-gray-50 px-5 py-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />

                                    <p
                                        v-if="nbEmailsCoEquipiers === 0"
                                        class="mt-2 text-sm text-gray-500"
                                    >
                                        Vous jouez seul : aucun email supplémentaire à renseigner.
                                    </p>
                                </div>

                                <div v-if="nbEmailsCoEquipiers > 0">
                                    <label class="block text-sm font-semibold text-gray-700 mb-4">
                                        Emails des coéquipiers ({{ nbEmailsCoEquipiers }} requis)
                                    </label>

                                    <div class="grid md:grid-cols-2 gap-4">
                                        <input
                                            v-for="(participant, index) in form.participants"
                                            :key="index"
                                            v-model="form.participants[index]"
                                            type="email"
                                            required
                                            :placeholder="`Coéquipier ${index + 1}`"
                                            class="rounded-2xl border-gray-200 bg-gray-50 px-5 py-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        />
                                    </div>

                                    <p v-if="form.errors.participants" class="text-red-500 text-xs mt-2">
                                        {{ form.errors.participants }}
                                    </p>
                                </div>
                            </div>

                            <!-- MODE CHALLENGE -->
                            <div
                                v-if="form.mode_jeu === 'challenge'"
                                class="space-y-8"
                            >
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                                        Email de votre adversaire
                                    </label>

                                    <input
                                        v-model="form.challenger_email"
                                        type="email"
                                        required
                                        placeholder="adversaire@email.com"
                                        class="w-full rounded-2xl border-gray-200 bg-gray-50 px-5 py-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />

                                    <p v-if="form.errors.challenger_email" class="text-red-500 text-xs mt-1">
                                        {{ form.errors.challenger_email }}
                                    </p>
                                </div>
                            </div>

                            <!-- PARAMETRES -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-10">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                                        Durée (min)
                                    </label>

                                    <input
                                        v-model="form.duree_prevue"
                                        type="number"
                                        :min="gameOptions.duree_min"
                                        required
                                        class="w-full rounded-2xl border-gray-200 bg-gray-50 px-5 py-4"
                                    />

                                    <p v-if="form.errors.duree_prevue" class="text-red-500 text-xs mt-1">
                                        {{ form.errors.duree_prevue }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                                        Difficulté
                                    </label>

                                    <select
                                        v-model="form.niveau_difficulte"
                                        class="w-full rounded-2xl border-gray-200 bg-gray-50 px-5 py-4"
                                    >
                                        <option
                                            v-for="niveau in gameOptions.niveaux_difficulte"
                                            :key="niveau.value"
                                            :value="niveau.value"
                                        >
                                            {{ niveau.emoji }} {{ niveau.label }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                                        Locomotion
                                    </label>

                                    <select
                                        v-model="form.moyen_locomotion"
                                        class="w-full rounded-2xl border-gray-200 bg-gray-50 px-5 py-4"
                                    >
                                        <option
                                            v-for="moyen in gameOptions.moyens_locomotion"
                                            :key="moyen.value"
                                            :value="moyen.value"
                                        >
                                            {{ moyen.emoji }} {{ moyen.label }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <p v-if="form.errors.mode_jeu" class="text-red-500 text-sm mt-4">
                                {{ form.errors.mode_jeu }}
                            </p>

                            <div class="flex justify-end mt-12">
                                <button
                                    @click="nextStep"
                                    :disabled="!form.mode_jeu"
                                    class="rounded-2xl bg-indigo-600 px-8 py-4 text-white font-bold shadow-lg transition hover:scale-105 hover:bg-indigo-700 disabled:opacity-50"
                                >
                                    Continuer →
                                </button>
                            </div>
                        </div>

                        <!-- ETAPE 2 : géolocalisation locale uniquement (non envoyée au back) -->
                        <div v-if="currentStep === 2">
                            <div class="py-16 text-center">
                                <div class="text-8xl mb-6">📡</div>

                                <h2 class="text-4xl font-black text-gray-800">
                                    Capture de position
                                </h2>

                                <p class="mt-4 text-gray-500 max-w-xl mx-auto">
                                    Activez votre position GPS pour confirmer que vous êtes prêt.
                                    Ces coordonnées restent sur votre appareil et ne sont pas enregistrées au lancement.
                                </p>

                                <div class="mt-14 flex justify-center">
                                    <button
                                        @click="capturePosition"
                                        class="relative overflow-hidden rounded-full bg-indigo-600 px-10 py-5 text-lg font-bold text-white shadow-2xl transition hover:scale-105"
                                    >
                                        <span class="absolute inset-0 animate-pulse bg-white/20"></span>
                                        <span class="relative">📍 Prendre ma position</span>
                                    </button>
                                </div>

                                <div
                                    v-if="positionCaptured"
                                    class="mt-10 inline-block rounded-3xl bg-green-50 px-8 py-5 shadow-lg border border-green-100"
                                >
                                    <div class="text-green-600 font-bold text-lg">
                                        ✅ Position enregistrée localement
                                    </div>

                                    <div class="mt-3 text-sm text-gray-600 space-y-1">
                                        <div>
                                            Latitude :
                                            <span class="font-semibold">{{ form.latitude }}</span>
                                        </div>
                                        <div>
                                            Longitude :
                                            <span class="font-semibold">{{ form.longitude }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ETAPE 3 -->
                        <div v-if="currentStep === 3">
                            <div class="mb-10">
                                <h2 class="text-4xl font-black text-gray-800">
                                    Récapitulatif
                                </h2>

                                <p class="mt-2 text-gray-500">
                                    Vérifiez les informations avant de commencer.
                                </p>
                            </div>

                            <div class="grid lg:grid-cols-2 gap-6">
                                <div class="rounded-3xl bg-gradient-to-br from-indigo-50 to-indigo-100 p-7 shadow-lg">
                                    <div class="text-sm font-semibold text-indigo-500 uppercase">Mode</div>
                                    <div class="mt-3 text-3xl font-black text-gray-800">{{ modeLabel }}</div>
                                </div>

                                <div class="rounded-3xl bg-gradient-to-br from-pink-50 to-pink-100 p-7 shadow-lg">
                                    <div class="text-sm font-semibold text-pink-500 uppercase">Difficulté</div>
                                    <div class="mt-3 text-3xl font-black text-gray-800">{{ difficultyLabel }}</div>
                                </div>

                                <div class="rounded-3xl bg-gradient-to-br from-green-50 to-green-100 p-7 shadow-lg">
                                    <div class="text-sm font-semibold text-green-500 uppercase">Durée</div>
                                    <div class="mt-3 text-3xl font-black text-gray-800">{{ form.duree_prevue }} min</div>
                                </div>

                                <div class="rounded-3xl bg-gradient-to-br from-yellow-50 to-yellow-100 p-7 shadow-lg">
                                    <div class="text-sm font-semibold text-yellow-600 uppercase">Locomotion</div>
                                    <div class="mt-3 text-3xl font-black text-gray-800">{{ locomotionLabel }}</div>
                                </div>
                            </div>

                            <div class="mt-10 rounded-3xl border border-gray-100 bg-gray-50 p-8">
                                <div class="flex items-center justify-between mb-6">
                                    <h3 class="text-2xl font-black text-gray-800">Participants</h3>

                                    <span class="rounded-full bg-indigo-100 px-4 py-2 text-sm font-bold text-indigo-600">
                                        {{
                                            form.mode_jeu === 'equipe'
                                                ? `${form.nb_membres} membres`
                                                : '1 challenger'
                                        }}
                                    </span>
                                </div>

                                <div v-if="form.mode_jeu === 'equipe'">
                                    <p
                                        v-if="form.nb_membres <= 1"
                                        class="rounded-2xl bg-white px-5 py-4 shadow-sm text-gray-700"
                                    >
                                        👤 Vous seul (aucun coéquipier)
                                    </p>
                                    <div
                                        v-else
                                        class="grid md:grid-cols-2 gap-4"
                                    >
                                        <div class="rounded-2xl bg-white px-5 py-4 shadow-sm">
                                            👤 Vous (organisateur)
                                        </div>
                                        <div
                                            v-for="(participant, index) in form.participants"
                                            :key="index"
                                            class="rounded-2xl bg-white px-5 py-4 shadow-sm"
                                        >
                                            👤 {{ participant }}
                                        </div>
                                    </div>
                                </div>

                                <div
                                    v-if="form.mode_jeu === 'challenge'"
                                    class="rounded-2xl bg-white px-5 py-4 shadow-sm"
                                >
                                    ⚔️ {{ form.challenger_email }}
                                </div>
                            </div>

                            <div class="mt-12 flex flex-col md:flex-row justify-end gap-4">
                                <button
                                    @click="currentStep = 1"
                                    class="rounded-2xl border border-gray-200 bg-white px-8 py-4 font-bold text-gray-700 transition hover:bg-gray-100"
                                >
                                    Modifier
                                </button>

                                <button
                                    @click="submit"
                                    :disabled="loadingLaunch || !peutLancer"
                                    class="relative overflow-hidden rounded-2xl bg-indigo-600 px-10 py-4 font-bold text-white shadow-xl transition hover:scale-105 disabled:opacity-70"
                                >
                                    <span
                                        v-if="loadingLaunch"
                                        class="absolute inset-0 bg-white/20 animate-pulse"
                                    ></span>

                                    <span class="relative flex items-center gap-3">
                                        <svg
                                            v-if="loadingLaunch"
                                            class="h-5 w-5 animate-spin"
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                        >
                                            <circle
                                                class="opacity-25"
                                                cx="12"
                                                cy="12"
                                                r="10"
                                                stroke="currentColor"
                                                stroke-width="4"
                                            />
                                            <path
                                                class="opacity-75"
                                                fill="currentColor"
                                                d="M4 12a8 8 0 018-8v8H4z"
                                            />
                                        </svg>

                                        {{
                                            loadingLaunch
                                                ? 'Lancement...'
                                                : '🚀 Lancer la partie'
                                        }}
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </AuthenticatedLayout>
</template>
