<script setup>
import { useToast } from 'primevue/usetoast';
import Toast from 'primevue/toast';
import { Head } from '@inertiajs/vue3';
import Banner from '@/Components/Banner.vue';
import Footer from '@/Footer/Footer.vue';

import { Link } from '@inertiajs/vue3';
import SupportChatbot from '@/Components/SupportChatbot.vue';

const toast = useToast();
</script>

<template>
    <div class="light">

        <Head />

        <div class="min-h-screen bg-white-100">

            <Banner />

            <!-- Page Heading -->
            <header v-if="$slots.header" class="bg-white shadow dark:bg-gray-800">
                <div class="px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>

            <!-- Footer Content -->
            <!-- <Footer /> -->
        </div>
        <Toast />
        <!-- <SupportChatbot /> -->
    </div>
</template>
