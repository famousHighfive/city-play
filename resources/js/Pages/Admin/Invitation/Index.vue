<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    environment: Object,
    invitations: Array,
})

const form = useForm({
    destinataire: '',
    canal: 'email',
})

const envoyerInvitation = () => {
    form.post(
        route('invitations.store', { environment: props.environment.id }),
        { onSuccess: () => form.reset() }
    )
}
</script>

<template>
    <Head title="Invitations" />

    <AppLayout>
        <template #default>

            <!-- En-tête -->
            <header class="bg-white shadow">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 flex justify-between items-center">
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        Invitations — {{ environment.nom }}
                    </h2>
                    <Link
                        :href="route('environments.index')"
                        class="text-sm text-gray-500 hover:text-gray-700"
                    >
                        ← Retour aux environnements
                    </Link>
                </div>
            </header>

            <div class="py-12">
                <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-8">

                    <!-- Formulaire d'envoi -->
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">
                            Envoyer une invitation
                        </h3>

                        <div class="space-y-5 max-w-md">

                            <!-- Choix du canal -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Canal d'envoi
                                </label>
                                <div class="flex gap-2">
                                    <button
                                        v-for="c in ['email', 'sms', 'whatsapp']"
                                        :key="c"
                                        type="button"
                                        @click="form.canal = c"
                                        :class="[
                                            'flex-1 py-2 rounded-lg text-sm font-medium transition border',
                                            form.canal === c
                                                ? 'bg-indigo-600 text-white border-indigo-600'
                                                : 'bg-white text-gray-600 border-gray-200 hover:border-indigo-300'
                                        ]"
                                    >
                                        {{ c.toUpperCase() }}
                                    </button>
                                </div>
                            </div>

                            <!-- Destinataire -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ form.canal === 'email' ? 'Adresse email' : 'Numéro de téléphone' }}
                                </label>
                                <input
                                    v-model="form.destinataire"
                                    :type="form.canal === 'email' ? 'email' : 'tel'"
                                    :placeholder="form.canal === 'email' ? 'participant@email.com' : '+229 XX XX XX XX'"
                                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm
                                           focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                />
                                <p v-if="form.errors.destinataire" class="text-red-500 text-xs mt-1">
                                    {{ form.errors.destinataire }}
                                </p>
                            </div>

                            <!-- Bouton envoyer -->
                            <button
                                type="button"
                                @click="envoyerInvitation"
                                :disabled="form.processing"
                                class="w-full bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50
                                       text-white font-medium rounded-lg py-2.5 text-sm transition"
                            >
                                {{ form.processing ? 'Envoi...' : 'Envoyer l\'invitation' }}
                            </button>

                            <!-- Message succès -->
                            <p v-if="form.wasSuccessful" class="text-green-600 text-sm text-center">
                                Invitation envoyée avec succès !
                            </p>

                        </div>
                    </div>

                    <!-- Liste des invitations existantes -->
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            Invitations envoyées
                        </h3>

                        <div v-if="invitations.length === 0" class="text-center py-8 text-gray-500 text-sm">
                            Aucune invitation envoyée pour cet environnement.
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Destinataire</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Canal</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expire le</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joueur</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="inv in invitations" :key="inv.id" class="hover:bg-gray-50">

                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            {{ inv.destinataire }}
                                        </td>

                                        <td class="px-6 py-4 text-sm">
                                            <span class="uppercase font-medium text-indigo-600 text-xs">
                                                {{ inv.canal }}
                                            </span>
                                        </td>

                                        <td class="px-6 py-4 text-sm">
                                            <span
                                                class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset"
                                                :class="{
                                                    'bg-yellow-50 text-yellow-700 ring-yellow-600/20': inv.statut === 'pending',
                                                    'bg-green-50 text-green-700 ring-green-600/20':  inv.statut === 'used',
                                                    'bg-red-50 text-red-700 ring-red-600/20':        inv.statut === 'expired',
                                                }"
                                            >
                                                {{ inv.statut }}
                                            </span>
                                        </td>

                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            {{ new Date(inv.expires_at).toLocaleString('fr-FR') }}
                                        </td>

                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            {{ inv.player ? inv.player.name : '—' }}
                                        </td>

                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>

        </template>
    </AppLayout>
</template>