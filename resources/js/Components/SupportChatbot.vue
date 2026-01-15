<script setup>
import { ref } from 'vue';

const mostrarModalSoporte = ref(false);
const abrirSoporte = () => mostrarModalSoporte.value = true;
const cerrarSoporte = () => mostrarModalSoporte.value = false;
</script>

<template>
    <div>
        <div class="fixed bottom-6 right-6 z-50 flex flex-col items-end group font-sans">

            <div
                class="bg-white px-5 py-3 rounded-2xl rounded-br-none shadow-[0_8px_30px_rgb(0,0,0,0.12)] mb-3 mr-2 border border-gray-100 animate-bounce-slow cursor-pointer hover:bg-blue-50 transition-colors">
                <p class="text-sm font-extrabold text-gray-800 leading-tight">
                    Facing issues? 🛠️ <br>
                    <span class="text-blue-600">We have the solution!</span>
                </p>
            </div>

            <button @click="abrirSoporte"
                class="relative w-32 h-32 focus:outline-none group-hover:-translate-y-2 transition-transform duration-500 ease-in-out">
                <div
                    class="absolute inset-0 bg-blue-500 opacity-0 group-hover:opacity-20 rounded-full blur-xl transition-opacity duration-500">
                </div>
                <img src="/images/wmc-bot.png" alt="WMC Support"
                    class="w-full h-full object-contain drop-shadow-[0_20px_20px_rgba(0,0,0,0.25)] transform group-hover:scale-110 transition-all duration-300">
                <span class="absolute bottom-2 right-4 w-5 h-5 bg-green-500 border-4 border-white rounded-full"></span>
            </button>
        </div>

        <div v-if="mostrarModalSoporte"
            class="fixed inset-0 z-[100] flex items-center justify-end sm:justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-all"
            @click.self="cerrarSoporte">

            <div
                class="bg-white rounded-[2rem] shadow-2xl w-full max-w-md overflow-hidden animate-fade-in-up mx-4 sm:mx-0 relative">

                <div class="absolute top-0 left-0 w-full h-36 bg-gradient-to-br from-blue-900 to-slate-800">
                    <div
                        class="absolute inset-0 opacity-10 bg-[radial-gradient(circle_at_1px_1px,#ffffff_1px,transparent_0)] [background-size:20px_20px]">
                    </div>
                </div>

                <button @click="cerrarSoporte"
                    class="absolute top-4 right-4 text-white/80 hover:text-white bg-black/20 hover:bg-black/40 rounded-full p-2 transition-all z-20">
                    <i class="pi pi-times text-lg"></i>
                </button>

                <div class="relative z-10 pt-8 px-8 pb-8 text-center">

                    <h3 class="text-2xl font-black text-white mb-1 drop-shadow-md">Support Center</h3>
                    <p class="text-blue-100 text-sm mb-6 font-medium">How can we assist you today?</p>

                    <div class="bg-white rounded-3xl p-2 shadow-lg border border-gray-100 space-y-2">

                        <a href="mailto:wmcitsupport@iimp.org.pe"
                            class="flex items-start p-4 rounded-2xl hover:bg-blue-50 border border-transparent hover:border-blue-100 transition-all group text-left">
                            <div
                                class="w-12 h-12 bg-blue-100 text-blue-700 rounded-xl flex items-center justify-center mr-4 shrink-0 shadow-sm group-hover:scale-110 transition-transform">
                                <i class="pi pi-desktop text-xl"></i>
                            </div>
                            <div>
                                <p class="font-bold text-gray-900 text-[15px]">IT & Systems Support</p>
                                <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                                    Technical issues, platform navigation, buttons or system errors.
                                </p>
                                <span
                                    class="text-[10px] text-blue-600 font-semibold mt-1 block">inscripciones.wmc@iimp.org.pe</span>
                            </div>
                        </a>

                        <div class="h-px bg-gray-100 mx-4"></div>

                        <a href="mailto:wmc2026authors@iimp.org.pe"
                            class="flex items-start p-4 rounded-2xl hover:bg-green-50 border border-transparent hover:border-green-100 transition-all group text-left">
                            <div
                                class="w-12 h-12 bg-green-100 text-green-700 rounded-xl flex items-center justify-center mr-4 shrink-0 shadow-sm group-hover:scale-110 transition-transform">
                                <i class="pi pi-file-edit text-xl"></i>
                            </div>
                            <div>
                                <p class="font-bold text-gray-900 text-[15px]">Congress & Guidelines</p>
                                <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                                    Definitions (Author/Associate), categories, pricing and terms.
                                </p>
                                <span
                                    class="text-[10px] text-green-700 font-semibold mt-1 block">inscripciones.wmc@iimp.org.pe</span>
                            </div>
                        </a>

                    </div>
                </div>

                <div class="bg-gray-50 p-3 text-center border-t border-gray-200">
                    <p class="text-[10px] text-gray-400 font-bold tracking-widest uppercase">World Mining Congress 2026
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.animate-fade-in-up {
    animation: modal-enter 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.animate-bounce-slow {
    animation: float 3s ease-in-out infinite;
}

@keyframes modal-enter {
    from {
        opacity: 0;
        transform: translateY(40px) scale(0.95);
    }

    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes float {

    0%,
    100% {
        transform: translateY(0);
    }

    50% {
        transform: translateY(-6px);
    }
}
</style>
