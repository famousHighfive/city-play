<script setup>
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'

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
    <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 w-full max-w-md p-8">

            <!-- En-tête -->
            <div class="mb-8 text-center">
                <h1 class="text-2xl font-semibold text-gray-900 mb-1">
                    Tu es invité à jouer !
                </h1>
                <p class="text-gray-500 text-sm">
                    Environnement :
                    <span class="font-medium text-gray-700">{{ environment.nom }}</span>
                </p>
            </div>

            <!-- Formulaire -->
            <div class="space-y-5">

                <!-- Nom -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Ton prénom
                    </label>
                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="Marie"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm
                               focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                    <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">
                        {{ form.errors.name }}
                    </p>
                </div>

                <!-- Pseudo -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Choisis un pseudo
                    </label>
                    <input
                        v-model="form.pseudo"
                        type="text"
                        placeholder="Explorateur42"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm
                               focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                    <p v-if="form.errors.pseudo" class="text-red-500 text-xs mt-1">
                        {{ form.errors.pseudo }}
                    </p>
                </div>

                <!-- Email ou téléphone selon le canal -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        {{ labelDestinataire }}
                    </label>
                    <input
                        v-model="form.destinataire"
                        :type="canal === 'email' ? 'email' : 'tel'"
                        :placeholder="placeholder"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm
                               focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                    <p v-if="form.errors.destinataire" class="text-red-500 text-xs mt-1">
                        {{ form.errors.destinataire }}
                    </p>
                </div>

                <!-- Mot de passe -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Ton mot de passe
                    </label>
                    <input
                        v-model="form.password"
                        type="password"
                        placeholder="••••••••"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm
                               focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                    <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">
                        {{ form.errors.password }}
                    </p>
                </div>

                <!-- Confirmation mot de passe -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Confirme ton mot de passe
                    </label>
                    <input
                        v-model="form.password_confirmation"
                        type="password"
                        placeholder="••••••••"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm
                               focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                </div>

                <!-- Canal affiché en lecture seule -->
                <div class="bg-gray-50 rounded-lg px-4 py-3 flex items-center gap-2">
                    <span class="text-xs text-gray-500">
                        Tu recevras ton code de vérification par
                    </span>
                    <span class="text-xs font-semibold text-indigo-600 uppercase">
                        {{ canal }}
                    </span>
                </div>

                <!-- Bouton -->
                <button
                    @click="submit"
                    :disabled="form.processing"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50
                           text-white font-medium rounded-lg py-2.5 text-sm transition"
                >
                    {{ form.processing ? 'Envoi en cours...' : 'Recevoir mon code' }}
                </button>

            </div>
        </div>
    </div>
</template>