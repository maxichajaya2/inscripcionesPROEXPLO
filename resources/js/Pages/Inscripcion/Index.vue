<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import colorbar from '@/Components/colorbar.vue';
import GreenArrowRight from '@/Components/GreenArrowRight.vue';
import IntroImage from '@/Components/IntroImage.vue';
import { usePage, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import Skeleton from 'primevue/skeleton';
import Functions from '@/Functions';

import "../../../css/inscripciones.css";

</script>
<template>
    <AppLayout title="Inscripciones" class="bg-gradient-wmc">
        <div class="px-6 py-12 mx-auto max-w-5xl min-h-[80vh] flex flex-col justify-center font-sans">

            <div id="titulo_inicial" class="mb-12 text-left animate-fade-in-down">
                <h1 class="text-4xl md:text-5xl font-black text-yellow-price tracking-tight mb-2">
                    World Mining Congress <span class="text-white">2026</span>
                </h1>
                <h3 class="text-xl md:text-2xl text-cyan-50 font-medium opacity-90 mb-4">
                    ¿Qué tipo de participante es?
                </h3>
                <colorbar class="block w-48 h-1.5 rounded-full" />
            </div>

            <div class="flex flex-col md:flex-row gap-8 items-start">
                <div class="w-full md:w-1/2 space-y-4">

                    <a href="/registro/autor"
                        class="group relative flex items-center justify-between p-6 bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl hover:bg-white/20 hover:border-[#06b6d4] hover:shadow-[0_0_20px_rgba(6,182,212,0.3)] transition-all duration-300">
                        <div class="flex flex-col">
                            <span
                                class="text-xs uppercase tracking-widest text-cyan-400 font-bold mb-1">Registration</span>
                            <h5 class="text-2xl font-black text-white group-hover:text-cyan-400 transition-colors">
                                AUTHOR SPECIAL</h5>
                        </div>
                        <div class="bg-white/10 p-3 rounded-xl group-hover:bg-[#06b6d4] transition-all duration-300">
                            <GreenArrowRight
                                class="w-6 h-6 invert brightness-200 group-hover:translate-x-1 transition-transform" />
                        </div>
                    </a>

                    <a href="/registro/participante"
                        class="group relative flex items-center justify-between p-6 bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl hover:bg-white/20 hover:border-[#1d4ed8] hover:shadow-[0_0_20px_rgba(29,78,216,0.3)] transition-all duration-300">
                        <div class="flex flex-col">
                            <span
                                class="text-xs uppercase tracking-widest text-blue-400 font-bold mb-1">Registration</span>
                            <h5 class="text-2xl font-black text-white group-hover:text-blue-400 transition-colors">
                                GENERAL PARTICIPANT</h5>
                        </div>
                        <div class="bg-white/10 p-3 rounded-xl group-hover:bg-[#1d4ed8] transition-all duration-300">
                            <GreenArrowRight
                                class="w-6 h-6 invert brightness-200 group-hover:translate-x-1 transition-transform" />
                        </div>
                    </a>

                </div>

                <div class="w-full md:w-1/2 mt-8 md:mt-0">
                    <div class="p-8 bg-black/20 backdrop-blur-sm border-l-4 border-yellow-price rounded-r-2xl">
                        <h6 class="text-yellow-price font-bold uppercase tracking-tighter mb-2">Important Note</h6>
                        <p class="text-white/80 text-sm leading-relaxed">
                            The World Mining Congress 2026 registration system is designed to manage participant
                            registrations and payment processing.
                            <br><br>
                            For any questions or technical issues related to the system or paper submissions, please
                            contact
                            <span class="text-cyan-400 font-bold underline">wmc.itsupport@iimp.org.pe</span>
                            for assistance.
                        </p>

                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
