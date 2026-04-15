<script setup>
import { computed } from 'vue';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();

const currentLang = computed(() => {
    return page.props.language?.current?.toUpperCase() || 'ES';
});

// ¡ESTA ES LA FUNCIÓN QUE TE FALTABA!
const changeLanguage = (locale) => {
    window.location.assign(`/set-locale/${locale}`);
};
</script>

<template>
    <nav class="bg-white border-b-1 border-black shadow-md">
        <div class="px-4 mx-auto max-w-7xl md:px-6 lg:px-8">
            <div class="flex justify-between mx-auto w-full">
                <div class="flex mx-auto w-full">
                    <div class="flex justify-between h-16 mx-auto w-full">
                        <div class="flex items-center shrink-0">
                            <a href="https://proexplo.com.pe/es" rel="noopener noreferrer">
                                <img src="/images/logo-proexplo.png" alt="ProExplo"
                                    class="h-12 w-auto cursor-pointer" />
                            </a>
                        </div>

                        <div class="flex items-center">
                            <ul class="flex items-center space-x-4">
                                <li @click="changeLanguage('es')"
                                    class="flex items-center gap-2 border-r-2 border-black pr-4 cursor-pointer transition-all duration-300"
                                    :class="currentLang === 'ES' ? 'opacity-100' : 'opacity-50 hover:opacity-100'">

                                    <img src="https://api.iconify.design/twemoji:flag-spain.svg" alt="ES"
                                        class="w-5 h-5 shadow-sm" />
                                    <span class="text-sm font-black"
                                        :class="currentLang === 'ES' ? 'text-blue-900' : 'text-gray-500'">ES</span>
                                </li>

                                <li @click="changeLanguage('en')"
                                    class="flex items-center gap-2 cursor-pointer transition-all duration-300"
                                    :class="currentLang === 'EN' ? 'opacity-100' : 'opacity-50 hover:opacity-100'">

                                    <img src="https://api.iconify.design/twemoji:flag-united-kingdom.svg" alt="EN"
                                        class="w-5 h-5 shadow-sm" />
                                    <span class="text-sm font-black"
                                        :class="currentLang === 'EN' ? 'text-blue-900' : 'text-gray-500'">EN</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</template>
