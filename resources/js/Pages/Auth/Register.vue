<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>

        <Head title="Inscription" />

        <div class="w-full max-w-lg mx-auto">

            <h1 class="text-3xl font-semibold text-black mb-8">
                Créer un compte
            </h1>

            <form @submit.prevent="submit" class="space-y-6">

                <!-- NAME -->
                <div>
                    <InputLabel for="name" value="Nom" />
                    <TextInput
                        id="name"
                        v-model="form.name"
                        class="w-full mt-2 px-4 py-3 rounded-xl border border-black/10 focus:ring-0"
                        required
                        autofocus
                    />
                    <InputError :message="form.errors.name" />
                </div>

                <!-- EMAIL -->
                <div>
                    <InputLabel for="email" value="Email" />
                    <TextInput
                        id="email"
                        type="email"
                        v-model="form.email"
                        class="w-full mt-2 px-4 py-3 rounded-xl border border-black/10 focus:ring-0"
                        required
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
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <!-- CONFIRM -->
                <div>
                    <InputLabel for="password_confirmation" value="Confirmation" />
                    <TextInput
                        id="password_confirmation"
                        type="password"
                        v-model="form.password_confirmation"
                        class="w-full mt-2 px-4 py-3 rounded-xl border border-black/10 focus:ring-0"
                        required
                    />
                </div>

                <!-- BUTTON -->
                <PrimaryButton
                    class="w-full py-3 rounded-xl"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Création...' : 'Créer un compte' }}
                </PrimaryButton>

                <div class="text-center text-sm mt-4">
                    <Link :href="route('login')" class="text-black/60 hover:text-black">
                        Déjà un compte ? Se connecter
                    </Link>
                </div>

            </form>

        </div>

    </GuestLayout>
</template>