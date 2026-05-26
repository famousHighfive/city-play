<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <nav class="border-b border-gray-100 bg-white">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between items-center">

                    <!-- Navigation Gauche (Liens Inertia) -->
                    <div class="flex space-x-8 items-center">
                        <span class="font-bold text-gray-800 text-lg mr-4">CITYPLAY</span>
                        
                        <!-- Dashboard -->
                        <Link 
                            :href="route('dashboard.admin')" 
                            class="text-sm font-medium transition duration-150 ease-in-out"
                            :class="route().current('dashboard.admin') ? 'text-indigo-600 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                        >
                            Dashboard
                        </Link>

                        <!-- Environnements -->
                        <Link 
                            :href="route('environments.index')" 
                            class="text-sm font-medium transition duration-150 ease-in-out"
                            :class="route().current('environments.*') ? 'text-indigo-600 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                        >
                            Environnements
                        </Link>

                        <!-- Places -->
                        <Link 
                            :href="route('places.index')" 
                            class="text-sm font-medium transition duration-150 ease-in-out"
                            :class="route().current('places.*') ? 'text-indigo-600 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                        >
                            Places
                        </Link>

                        <!-- Enigmes -->
                        <Link 
                            :href="route('enigmes.index')" 
                            class="text-sm font-medium transition duration-150 ease-in-out"
                            :class="route().current('enigmes.*') ? 'text-indigo-600 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                        >
                            Enigmes
                        </Link>
                    </div>

                    <!-- Actions Droite -->
                    <div>
                        <Link 
                            :href="route('logout')" 
                            method="post" 
                            as="button" 
                            class="text-sm font-medium text-gray-500 hover:text-gray-700 bg-transparent border-0 cursor-pointer"
                        >
                            Déconnexion
                        </Link>
                    </div>

                </div>
            </div>
        </nav>

        <!-- Contenu Principal -->
        <main class="p-6">
            <slot>
                <p class="text-red-500 font-bold">
                    Si vous voyez ce message, le Slot est vide ! Ajoutez du contenu dans votre page.
                </p>
            </slot>
        </main>
    </div>
</template>
