<script setup>
import Banner from '@/Components/Banner.vue';
import Footer from '@/Footer/Footer.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="light">

    <Head :title="Inscripciones" />

        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">

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
            <Footer />
        </div>

    </div>
</template>
