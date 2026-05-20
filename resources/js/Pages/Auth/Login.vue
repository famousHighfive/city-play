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

        <div class="w-full max-w-lg mx-auto">

            <h1 class="text-3xl font-semibold text-black mb-8">
                Connexion
            </h1>

            <div v-if="status" class="mb-4 text-sm text-green-600">
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="space-y-6">

                <!-- EMAIL -->
                <div>
                    <InputLabel for="email" value="Email" />
                    <TextInput
                        id="email"
                        type="email"
                        v-model="form.email"
                        class="w-full mt-2 px-4 py-3 rounded-xl border border-black/10 focus:ring-0"
                        required
                        autofocus
                        autocomplete="username"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <!-- PASSWORD -->
                <div>
                    <InputLabel for="password" value="Mot de passe" />
                    <TextInput
                        id="password"
                        type="password"
                        v-model="form.password"
                        class="w-full mt-2 px-4 py-3 rounded-xl border border-black/10 focus:ring-0"
                        required
                        autocomplete="current-password"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <!-- OPTIONS -->
                <div class="flex items-center justify-between text-sm">

                    <label class="flex items-center gap-2">
                        <Checkbox v-model:checked="form.remember" />
                        <span>Se souvenir de moi</span>
                    </label>

                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-black/60 hover:text-black"
                    >
                        Mot de passe oublié
                    </Link>

                </div>

                <!-- BUTTON -->
                <PrimaryButton
                    class="w-full py-3 rounded-xl"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Connexion...' : 'Se connecter' }}
                </PrimaryButton>

                                <div class="text-center text-sm mt-4">
                    <Link :href="route('register')" class="text-black/60 hover:text-black">
                        Vous n'avez pas un compte ? S'enregister
                    </Link>
                </div>

            </form>

        </div>

    </GuestLayout>
</template>