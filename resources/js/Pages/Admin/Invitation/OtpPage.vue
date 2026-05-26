<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
    token:        String,
    destinataire: String,
    canal:        String,
})

// Tableau de 6 cases
const cases = ref(['', '', '', '', '', ''])
const inputRefs = ref([])

// Le code complet formé par les 6 cases
const codeComplet = computed(() => cases.value.join(''))

const form = useForm({
    code:         '',
    destinataire: props.destinataire,
})

const renvoyerForm = useForm({
    destinataire: props.destinataire,
    canal:        props.canal,
})

// Gestion de la saisie case par case
const onInput = (index, event) => {
    const valeur = event.target.value.replace(/\D/g, '').slice(-1)
    cases.value[index] = valeur

    // Avance automatiquement à la case suivante
    if (valeur && index < 5) {
        inputRefs.value[index + 1]?.focus()
    }
}

// Gestion de la touche retour arrière
const onBackspace = (index, event) => {
    if (!cases.value[index] && index > 0) {
        cases.value[index - 1] = ''
        inputRefs.value[index - 1]?.focus()
    }
}

// Gestion du collage (si l'invité colle le code d'un coup)
const onPaste = (event) => {
    const texte = event.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6)
    texte.split('').forEach((char, i) => {
        cases.value[i] = char
    })
    inputRefs.value[Math.min(texte.length, 5)]?.focus()
    event.preventDefault()
}

const submit = () => {
    form.code = codeComplet.value
    form.post(route('invitation.otp.verifier', { token: props.token }))
}

const renvoyer = () => {
    renvoyerForm.post(route('invitation.otp.renvoyer', { token: props.token }))
}
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 w-full max-w-md p-8">

            <!-- En-tête -->
            <div class="mb-8 text-center">
                <h1 class="text-2xl font-semibold text-gray-900 mb-1">
                    Vérifie ton identité
                </h1>
                <p class="text-gray-500 text-sm">
                    Code envoyé par
                    <span class="font-semibold text-indigo-600 uppercase">{{ canal }}</span>
                    à {{ destinataire }}
                </p>
            </div>

            <!-- 6 cases OTP -->
            <div class="flex gap-2 justify-center mb-6">
                <input
                    v-for="(_, index) in cases"
                    :key="index"
                    :ref="el => inputRefs[index] = el"
                    :value="cases[index]"
                    maxlength="1"
                    inputmode="numeric"
                    @input="onInput(index, $event)"
                    @keydown.backspace="onBackspace(index, $event)"
                    @paste="onPaste"
                    class="w-12 h-14 text-center text-xl font-semibold border border-gray-200
                           rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500
                           focus:border-transparent"
                />
            </div>

            <!-- Erreur -->
            <p v-if="form.errors.code" class="text-red-500 text-sm text-center mb-4">
                {{ form.errors.code }}
            </p>

            <!-- Succès renvoi -->
            <p v-if="renvoyerForm.wasSuccessful" class="text-green-600 text-sm text-center mb-4">
                Nouveau code envoyé !
            </p>

            <!-- Bouton valider -->
            <button
                @click="submit"
                :disabled="codeComplet.length < 6 || form.processing"
                class="w-full bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50
                       text-white font-medium rounded-lg py-2.5 text-sm transition mb-4"
            >
                {{ form.processing ? 'Vérification...' : 'Valider le code' }}
            </button>

            <!-- Renvoyer le code -->
            <button
                @click="renvoyer"
                :disabled="renvoyerForm.processing"
                class="w-full text-sm text-gray-500 hover:text-indigo-600 transition"
            >
                {{ renvoyerForm.processing ? 'Envoi...' : 'Renvoyer un nouveau code' }}
            </button>

        </div>
    </div>
</template>