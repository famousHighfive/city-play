<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import { useAudioStore } from '@/stores/audio';
import Dialog from 'primevue/dialog';

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

const toast = useToast();
const audioStore = useAudioStore();

const currentStep = ref(1);
const showModeModal = ref(props.afficher_modal_mode);
const positionCaptured = ref(false);
const loadingLaunch = ref(false);
const loadingPosition = ref(false);
const playerLocation = ref({
    latitude: null,
    longitude: null,
    accuracy: null
});

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

const dureeOptions = [
    { label: '30 min', value: 30 },
    { label: '1h', value: 60 },
    { label: '2h', value: 120 },
    { label: '4h', value: 240 },
    { label: '8h', value: 480 },
    { label: '12h', value: 720 },
    { label: '24h', value: 1440 },
];

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

const difficultyLabel = computed(() => {
    const niveau = props.gameOptions.niveaux_difficulte.find(
        (n) => String(n.value) === String(form.niveau_difficulte)
    );
    return niveau?.label ?? form.niveau_difficulte;
});

const locomotionLabel = computed(() => {
    const moyen = props.gameOptions.moyens_locomotion.find(
        (m) => m.value === form.moyen_locomotion
    );
    return moyen?.label ?? form.moyen_locomotion;
});

const locomotionIcon = computed(() => {
    switch (form.moyen_locomotion) {
        case 'pied': return '🚶';
        case 'velo': return '🚲';
        case 'voiture': return '🚗';
        default: return '🚶';
    }
});

const nextStep = () => {
    audioStore.play('click');
    if (currentStep.value < 5) {
        currentStep.value++;
    }
};

const prevStep = () => {
    audioStore.play('click');
    if (currentStep.value > 1) {
        currentStep.value--;
    }
};

const selectMode = (mode) => {
    audioStore.play('click');
    form.mode_jeu = mode;
    showModeModal.value = false;
};

const retourDashboard = () => {
    audioStore.play('click');
    router.visit(route('dashboard'));
};

const capturePosition = () => {
    if (!navigator.geolocation) {
        toast.add({
            severity: 'error',
            summary: 'Erreur',
            detail: 'La géolocalisation n\'est pas supportée par votre navigateur.',
            life: 5000
        });
        return;
    }

    loadingPosition.value = true;
    audioStore.play('notification');

    navigator.geolocation.getCurrentPosition(
        (position) => {
            form.latitude = position.coords.latitude;
            form.longitude = position.coords.longitude;
            playerLocation.value = {
                latitude: position.coords.latitude,
                longitude: position.coords.longitude,
                accuracy: position.coords.accuracy
            };
            positionCaptured.value = true;
            loadingPosition.value = false;
            audioStore.play('success');
            
            toast.add({
                severity: 'success',
                summary: 'Position capturée !',
                detail: `Précision: ~${Math.round(position.coords.accuracy)}m`,
                life: 3000
            });
        },
        (error) => {
            loadingPosition.value = false;
            audioStore.play('error');
            
            let errorMessage = "Impossible d'accéder à votre position GPS.";
            switch (error.code) {
                case error.PERMISSION_DENIED:
                    errorMessage = "Vous avez refusé l'accès à la géolocalisation. Veuillez l'activer dans les paramètres de votre navigateur.";
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorMessage = "La position n'a pas pu être déterminée.";
                    break;
                case error.TIMEOUT:
                    errorMessage = "La demande de géolocalisation a expiré. Veuillez réessayer.";
                    break;
            }
            
            toast.add({
                severity: 'error',
                summary: 'Erreur GPS',
                detail: errorMessage,
                life: 8000
            });
        },
        { enableHighAccuracy: true, timeout: 20000, maximumAge: 0 }
    );
};

const payloadPourBackend = (data) => {
    const payload = { ...data };

    if (payload.mode_jeu === 'challenge') {
        delete payload.nb_membres;
        delete payload.participants;
    } else {
        delete payload.challenger_email;
    }

    return payload;
};

const peutLancer = computed(
    () => props.gameOptions.enigmes_disponibles > 0 && positionCaptured.value && form.mode_jeu
);

const submit = () => {
    if (!form.mode_jeu) {
        toast.add({
            severity: 'warn',
            summary: 'Attention',
            detail: 'Veuillez choisir un mode de jeu avant de lancer la partie.',
            life: 5000
        });
        audioStore.play('notification');
        return;
    }
    
    if (!positionCaptured.value) {
        toast.add({
            severity: 'warn',
            summary: 'Attention',
            detail: 'Veuillez capturer votre position avant de lancer la partie.',
            life: 5000
        });
        audioStore.play('notification');
        return;
    }

    loadingLaunch.value = true;
    audioStore.play('click');

    console.log('Form data being sent:', {
        mode_jeu: form.mode_jeu,
        duree_prevue: form.duree_prevue,
        moyen_locomotion: form.moyen_locomotion,
        niveau_difficulte: form.niveau_difficulte,
        nb_membres: form.nb_membres,
        participants: form.participants,
        challenger_email: form.challenger_email,
        latitude: form.latitude,
        longitude: form.longitude,
    });
    console.log('Form URL:', route('game.start', props.environment.id));
    
    form.post(route('game.start', props.environment.id), {
        preserveScroll: false,
        preserveState: false,
        onSuccess: (page) => {
            console.log('Success! Page:', page);
            loadingLaunch.value = false;
        },
        onError: (errors) => {
            loadingLaunch.value = false;
            console.error('Errors:', errors);
            toast.add({
                severity: 'error',
                summary: 'Erreur',
                detail: 'Une erreur est survenue lors du lancement de la partie.',
                life: 5000
            });
            audioStore.play('error');
        },
        onFinish: () => {
            loadingLaunch.value = false;
        },
    });
};

onMounted(() => {
    if (props.afficher_modal_mode && !form.mode_jeu) {
        showModeModal.value = true;
    }
});
</script>

<template>
    <Head title="Configurer votre partie" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-gradient-to-br from-slate-50 via-indigo-50 to-purple-50 py-12">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="text-center mb-12">
                    <div class="inline-flex items-center gap-3 mb-4">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-600 to-purple-600 flex items-center justify-center shadow-xl">
                            <span class="text-3xl">🎮</span>
                        </div>
                    </div>
                    <h1 class="text-4xl font-black text-gray-900 tracking-tight mb-2">
                        Préparez votre aventure
                    </h1>
                    <p class="text-xl text-gray-500">
                        {{ environment.nom }}
                    </p>
                </div>

                <!-- Progress Steps -->
                <div class="mb-10">
                    <div class="flex items-center justify-center gap-2">
                        <div 
                            v-for="step in 5" 
                            :key="step"
                            class="flex items-center gap-2"
                        >
                            <div 
                                class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300"
                                :class="currentStep >= step 
                                    ? 'bg-gradient-to-br from-indigo-600 to-purple-600 text-white shadow-lg' 
                                    : 'bg-gray-200 text-gray-500'"
                            >
                                {{ step }}
                            </div>
                            <div 
                                v-if="step < 5"
                                class="w-12 h-1 rounded-full transition-all duration-300"
                                :class="currentStep > step ? 'bg-gradient-to-r from-indigo-600 to-purple-600' : 'bg-gray-200'"
                            ></div>
                        </div>
                    </div>
                </div>

                <!-- Configuration Card -->
                <div class="bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden">
                    <!-- Step 1: Mode -->
                    <div v-if="currentStep === 1" class="p-8">
                        <div class="text-center mb-8">
                            <h2 class="text-2xl font-bold text-gray-900 mb-2">Choisissez votre mode de jeu</h2>
                            <p class="text-gray-500">Sélectionnez comment vous voulez jouer</p>
                        </div>
                        
                        <div class="grid md:grid-cols-2 gap-6">
                            <button
                                @click="selectMode('equipe')"
                                class="p-8 rounded-2xl border-2 transition-all duration-300 hover:scale-[1.02] hover:shadow-xl"
                                :class="form.mode_jeu === 'equipe' 
                                    ? 'border-indigo-500 bg-gradient-to-br from-indigo-50 to-purple-50' 
                                    : 'border-gray-200 bg-gray-50 hover:border-gray-300'"
                            >
                                <div class="text-5xl mb-4">👥</div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Mode équipe</h3>
                                <p class="text-gray-500 text-sm">Jouez avec vos amis et résolvez les énigmes ensemble</p>
                            </button>
                            
                            <button
                                @click="selectMode('challenge')"
                                class="p-8 rounded-2xl border-2 transition-all duration-300 hover:scale-[1.02] hover:shadow-xl"
                                :class="form.mode_jeu === 'challenge' 
                                    ? 'border-emerald-500 bg-gradient-to-br from-emerald-50 to-teal-50' 
                                    : 'border-gray-200 bg-gray-50 hover:border-gray-300'"
                            >
                                <div class="text-5xl mb-4">⚔️</div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Mode défi</h3>
                                <p class="text-gray-500 text-sm">Relevez le défi et testez vos compétences seul</p>
                            </button>
                        </div>
                        
                        <div v-if="form.mode_jeu" class="mt-8 text-center">
                            <button
                                @click="nextStep"
                                class="px-10 py-4 bg-gradient-to-br from-indigo-600 to-purple-600 text-white font-bold rounded-xl shadow-lg hover:shadow-xl hover:scale-[1.02] transition-all"
                            >
                                Continuer →
                            </button>
                        </div>
                    </div>

                    <!-- Step 2: Team / Challenge -->
                    <div v-if="currentStep === 2" class="p-8">
                        <button
                            @click="prevStep"
                            class="mb-6 px-4 py-2 text-gray-600 hover:text-gray-900 font-semibold text-sm flex items-center gap-2"
                        >
                            ← Retour
                        </button>
                        
                        <div class="text-center mb-8">
                            <h2 class="text-2xl font-bold text-gray-900 mb-2">
                                {{ form.mode_jeu === 'equipe' ? 'Votre équipe' : 'Votre défi' }}
                            </h2>
                            <p class="text-gray-500">
                                {{ form.mode_jeu === 'equipe' ? 'Ajoutez vos coéquipiers' : 'Préparez votre défi solo' }}
                            </p>
                        </div>

                        <div v-if="form.mode_jeu === 'equipe'" class="max-w-md mx-auto space-y-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-3">
                                    Nombre de membres (max {{ gameOptions.max_membres }})
                                </label>
                                <input
                                    v-model.number="form.nb_membres"
                                    type="range"
                                    min="1"
                                    :max="gameOptions.max_membres"
                                    class="w-full"
                                />
                                <div class="text-center mt-2 text-3xl font-bold text-indigo-600">
                                    {{ form.nb_membres }}
                                </div>
                            </div>

                            <div v-if="nbEmailsCoEquipiers > 0" class="space-y-4">
                                <label class="block text-sm font-semibold text-gray-700">
                                    Emails des coéquipiers
                                </label>
                                <input
                                    v-for="(email, index) in form.participants"
                                    :key="index"
                                    v-model="form.participants[index]"
                                    type="email"
                                    :placeholder="`Email du coéquipier ${index + 1}`"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none"
                                />
                            </div>
                        </div>

                        <div v-if="form.mode_jeu === 'challenge'" class="max-w-md mx-auto text-center">
                            <div class="p-8 bg-gradient-to-br from-emerald-50 to-teal-50 rounded-2xl border border-emerald-200">
                                <div class="text-6xl mb-4">🎯</div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Prêt pour le défi ?</h3>
                                <p class="text-gray-600">Vous allez relever l'aventure seul, bon courage !</p>
                            </div>
                        </div>

                        <div class="mt-8 text-center">
                            <button
                                @click="nextStep"
                                class="px-10 py-4 bg-gradient-to-br from-indigo-600 to-purple-600 text-white font-bold rounded-xl shadow-lg hover:shadow-xl hover:scale-[1.02] transition-all"
                            >
                                Continuer →
                            </button>
                        </div>
                    </div>

                    <!-- Step 3: Settings -->
                    <div v-if="currentStep === 3" class="p-8">
                        <button
                            @click="prevStep"
                            class="mb-6 px-4 py-2 text-gray-600 hover:text-gray-900 font-semibold text-sm flex items-center gap-2"
                        >
                            ← Retour
                        </button>
                        
                        <div class="text-center mb-8">
                            <h2 class="text-2xl font-bold text-gray-900 mb-2">Paramètres de la partie</h2>
                            <p class="text-gray-500">Personnalisez votre expérience</p>
                        </div>

                        <div class="grid md:grid-cols-3 gap-6 max-w-3xl mx-auto">
                            <!-- Difficulty -->
                            <div class="p-6 bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl border border-amber-200">
                                <div class="text-3xl mb-3">🎯</div>
                                <label class="block text-sm font-semibold text-gray-700 mb-3">
                                    Niveau de difficulté
                                </label>
                                <select
                                    v-model="form.niveau_difficulte"
                                    class="w-full px-3 py-2 border border-gray-200 rounded-xl bg-white"
                                >
                                    <option
                                        v-for="niveau in gameOptions.niveaux_difficulte"
                                        :key="niveau.value"
                                        :value="niveau.value"
                                    >
                                        {{ niveau.label }}
                                    </option>
                                </select>
                                <div class="mt-2 text-lg font-bold text-amber-700">
                                    {{ difficultyLabel }}
                                </div>
                            </div>

                            <!-- Duration -->
                            <div class="p-6 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl border border-blue-200">
                                <div class="text-3xl mb-3">⏱️</div>
                                <label class="block text-sm font-semibold text-gray-700 mb-3">
                                    Durée de la partie
                                </label>
                                <select
                                    v-model.number="form.duree_prevue"
                                    class="w-full px-3 py-2 border border-gray-200 rounded-xl bg-white focus:border-indigo-500 outline-none"
                                >
                                    <option
                                        v-for="option in dureeOptions"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                                <div class="mt-2 text-xl font-bold text-blue-700">
                                    {{ form.duree_prevue >= 60 ? Math.floor(form.duree_prevue / 60) + 'h' + (form.duree_prevue % 60 > 0 ? form.duree_prevue % 60 : '') : form.duree_prevue + ' min' }}
                                </div>
                            </div>

                            <!-- Locomotion -->
                            <div class="p-6 bg-gradient-to-br from-purple-50 to-pink-50 rounded-2xl border border-purple-200">
                                <div class="text-3xl mb-3">{{ locomotionIcon }}</div>
                                <label class="block text-sm font-semibold text-gray-700 mb-3">
                                    Moyen de locomotion
                                </label>
                                <select
                                    v-model="form.moyen_locomotion"
                                    class="w-full px-3 py-2 border border-gray-200 rounded-xl bg-white"
                                >
                                    <option
                                        v-for="moyen in gameOptions.moyens_locomotion"
                                        :key="moyen.value"
                                        :value="moyen.value"
                                    >
                                        {{ moyen.label }}
                                    </option>
                                </select>
                                <div class="mt-2 text-lg font-bold text-purple-700">
                                    {{ locomotionLabel }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 text-center">
                            <button
                                @click="nextStep"
                                class="px-10 py-4 bg-gradient-to-br from-indigo-600 to-purple-600 text-white font-bold rounded-xl shadow-lg hover:shadow-xl hover:scale-[1.02] transition-all"
                            >
                                Continuer →
                            </button>
                        </div>
                    </div>

                    <!-- Step 4: Recap -->
                    <div v-if="currentStep === 4" class="p-8">
                        <button
                            @click="prevStep"
                            class="mb-6 px-4 py-2 text-gray-600 hover:text-gray-900 font-semibold text-sm flex items-center gap-2"
                        >
                            ← Retour
                        </button>
                        
                        <div class="text-center mb-8">
                            <h2 class="text-2xl font-bold text-gray-900 mb-2">Récapitulatif de votre partie</h2>
                            <p class="text-gray-500">Vérifiez vos informations avant de commencer</p>
                        </div>

                        <div class="max-w-2xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="bg-gray-50 p-6 rounded-2xl border border-gray-100 space-y-4">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">{{ form.mode_jeu === 'equipe' ? '👥' : '🎯' }}</span>
                                    <div>
                                        <p class="text-xs text-gray-400 uppercase font-bold tracking-wider">Mode de jeu</p>
                                        <p class="font-bold text-gray-900">{{ form.mode_jeu === 'equipe' ? 'Mode Équipe' : 'Mode Défi' }}</p>
                                    </div>
                                </div>
                                <div v-if="form.mode_jeu === 'equipe'" class="pl-9">
                                    <p class="text-sm text-gray-600">{{ form.nb_membres }} membres</p>
                                    <div class="mt-1 flex flex-wrap gap-1">
                                        <span v-for="(p, i) in form.participants" :key="i" class="text-[10px] bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full">
                                            {{ p || 'Email non saisi' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 p-6 rounded-2xl border border-gray-100 space-y-4">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">🧠</span>
                                    <div>
                                        <p class="text-xs text-gray-400 uppercase font-bold tracking-wider">Difficulté</p>
                                        <p class="font-bold text-gray-900">{{ difficultyLabel }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">⏱️</span>
                                    <div>
                                        <p class="text-xs text-gray-400 uppercase font-bold tracking-wider">Durée prévue</p>
                                        <p class="font-bold text-gray-900">{{ form.duree_prevue >= 60 ? Math.floor(form.duree_prevue / 60) + 'h' + (form.duree_prevue % 60 > 0 ? form.duree_prevue % 60 : '') : form.duree_prevue + ' min' }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 p-6 rounded-2xl border border-gray-100 space-y-4">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">{{ locomotionIcon }}</span>
                                    <div>
                                        <p class="text-xs text-gray-400 uppercase font-bold tracking-wider">Locomotion</p>
                                        <p class="font-bold text-gray-900">{{ locomotionLabel }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 p-6 rounded-2xl border border-gray-100 space-y-4">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">📍</span>
                                    <div>
                                        <p class="text-xs text-gray-400 uppercase font-bold tracking-wider">Position</p>
                                        <p class="font-bold text-gray-900" :class="positionCaptured ? 'text-emerald-600' : 'text-red-600'">
                                            {{ positionCaptured ? 'Capturée' : 'Non capturée' }}
                                        </p>
                                    </div>
                                </div>
                                <button v-if="!positionCaptured" @click="capturePosition" class="text-xs bg-indigo-600 text-white px-3 py-1.5 rounded-lg">
                                    Capturer maintenant
                                </button>
                            </div>
                        </div>

                        <div class="mt-10 text-center">
                            <button
                                @click="currentStep = 5"
                                class="px-10 py-4 bg-gradient-to-br from-indigo-600 to-purple-600 text-white font-bold rounded-xl shadow-lg hover:shadow-xl hover:scale-[1.02] transition-all"
                            >
                                Tout est bon, continuer ! →
                            </button>
                        </div>
                    </div>

                    <!-- Step 5: Location (Old Step 4) -->
                    <div v-if="currentStep === 5" class="p-8">
                        <button
                            @click="prevStep"
                            class="mb-6 px-4 py-2 text-gray-600 hover:text-gray-900 font-semibold text-sm flex items-center gap-2"
                        >
                            ← Retour
                        </button>
                        
                        <div class="text-center mb-8">
                            <h2 class="text-2xl font-bold text-gray-900 mb-2">Votre position de départ</h2>
                            <p class="text-gray-500">Capturez votre position pour commencer</p>
                        </div>

                        <div class="max-w-md mx-auto text-center space-y-6">
                            <!-- Location Status -->
                            <div 
                                class="p-8 rounded-3xl border-2 transition-all duration-300"
                                :class="positionCaptured 
                                    ? 'border-emerald-500 bg-gradient-to-br from-emerald-50 to-teal-50' 
                                    : 'border-gray-200 bg-gray-50'"
                            >
                                <div class="text-6xl mb-4">
                                    {{ positionCaptured ? '✅' : '📍' }}
                                </div>
                                
                                <div v-if="positionCaptured" class="space-y-2">
                                    <h3 class="text-xl font-bold text-gray-900">Position capturée !</h3>
                                    <p class="text-sm text-gray-600 font-mono">
                                        {{ playerLocation.latitude?.toFixed(6) }}, {{ playerLocation.longitude?.toFixed(6) }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Précision: ~{{ Math.round(playerLocation.accuracy) }}m
                                    </p>
                                </div>
                                
                                <div v-else class="space-y-2">
                                    <h3 class="text-xl font-bold text-gray-900">Position non capturée</h3>
                                    <p class="text-gray-500">Cliquez ci-dessous pour capturer votre position</p>
                                </div>
                            </div>

                            <!-- Capture Button -->
                            <button
                                @click="capturePosition"
                                :disabled="loadingPosition"
                                class="w-full px-8 py-4 bg-gradient-to-br from-emerald-500 to-teal-600 text-white font-bold rounded-xl shadow-lg hover:shadow-xl hover:scale-[1.02] transition-all disabled:opacity-60 flex items-center justify-center gap-3"
                            >
                                <span v-if="loadingPosition" class="animate-spin">🔄</span>
                                <span v-else>📍</span>
                                {{ loadingPosition ? 'Capture en cours...' : (positionCaptured ? 'Recapturer ma position' : 'Capturer ma position') }}
                            </button>

                            <!-- Launch Button -->
                            <button
                                v-if="peutLancer"
                                @click="submit"
                                :disabled="loadingLaunch"
                                class="w-full px-8 py-5 bg-gradient-to-br from-indigo-600 to-purple-600 text-white font-black text-lg rounded-xl shadow-2xl hover:shadow-3xl hover:scale-[1.02] transition-all disabled:opacity-60 flex items-center justify-center gap-3"
                            >
                                <span v-if="loadingLaunch" class="animate-spin">⚙️</span>
                                <span v-else>🚀</span>
                                {{ loadingLaunch ? 'Lancement en cours...' : 'Lancer l\'aventure !' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Back to Dashboard -->
                <div class="text-center mt-8">
                    <button
                        @click="retourDashboard"
                        class="px-6 py-3 text-gray-500 hover:text-gray-700 font-semibold text-sm"
                    >
                        ← Retour au dashboard
                    </button>
                </div>
            </div>
        </div>

        <Toast position="top-right" />
    </AuthenticatedLayout>
</template>
