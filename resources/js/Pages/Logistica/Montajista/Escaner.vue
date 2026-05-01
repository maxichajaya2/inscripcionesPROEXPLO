<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';

const documento = ref('');
const inputEscaner = ref(null);
const isProcessing = ref(false);
const resultado = ref(null);
let timeoutLimpieza = null;

const procesarEscaneo = async () => {
    if (!documento.value || isProcessing.value) return;
    isProcessing.value = true;

    const documentoLimpio = documento.value.trim().replace(/'/g, '-');

    try {
        const response = await axios.post(route('escaner.validar'), {
            documento: documentoLimpio,
        });

        resultado.value = response.data;
    } catch (error) {
        resultado.value = {
            status: 'error',
            color: 'bg-red-600',
            titulo: 'ERROR CRÍTICO',
            mensaje: 'Falla de conexión con el servidor.',
        };
    } finally {
        documento.value = '';
        isProcessing.value = false;
        enfocarInput();

        if (timeoutLimpieza) clearTimeout(timeoutLimpieza);
        timeoutLimpieza = setTimeout(() => { resultado.value = null; }, 5000);
    }
};

const enfocarInput = () => { inputEscaner.value?.focus(); };

onMounted(() => {
    enfocarInput();
    document.addEventListener('click', enfocarInput);
});
</script>

<template>

    <Head title="Escáner PROEXPLO 2026" />

    <!-- Este div oculto EVITA QUE TAILWIND ELIMINE LOS COLORES al compilar -->
    <div
        class="hidden bg-emerald-600 bg-blue-600 bg-red-600 bg-slate-800 bg-fuchsia-600 text-emerald-400 text-blue-400 text-red-400">
    </div>

    <!-- CAMBIO AQUÍ: Cambiamos overflow-hidden por overflow-x-hidden overflow-y-auto -->
    <div class="min-h-screen flex flex-col transition-colors duration-700 ease-in-out relative overflow-x-hidden overflow-y-auto"
        :class="resultado ? resultado.color : 'bg-slate-900'">

        <!-- Efecto de Ondas Expansivas (Pulse) cuando hay éxito -->
        <div v-if="resultado && resultado.status === 'success'"
            class="absolute inset-0 flex items-center justify-center pointer-events-none z-0 fixed">
            <div class="w-[150vw] h-[150vw] rounded-full bg-white/10 animate-ping-slow absolute"></div>
            <div class="w-[100vw] h-[100vw] rounded-full bg-white/5 animate-ping-slower absolute"></div>
        </div>

        <!-- Partículas de fondo tecnológicas -->
        <div class="absolute inset-0 opacity-20 pointer-events-none z-0 fixed">
            <div
                class="absolute top-0 left-0 w-full h-full bg-[radial-gradient(circle,rgba(255,255,255,0.2)_2px,transparent_2px)] [background-size:30px_30px] animate-slide-bg">
            </div>
        </div>

        <!-- Header -->
        <div
            class="relative z-10 p-4 md:p-6 flex justify-between items-center text-white border-b border-white/10 backdrop-blur-md bg-black/20 shadow-lg">
            <div class="flex items-center gap-3">
                <div class="relative flex h-4 w-4">
                    <span
                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-4 w-4 bg-red-500"></span>
                </div>
                <h1 class="text-base md:text-lg font-black tracking-[0.2em] uppercase text-white/90 drop-shadow-md">
                    Security Live Terminal</h1>
            </div>
            <Link :href="route('personal-montaje.index')"
                class="px-5 py-2 bg-white/10 hover:bg-white text-white hover:text-black rounded-full text-xs font-black transition-all border border-white/20 uppercase shadow-lg hover:scale-105">
                Cerrar Terminal
            </Link>
        </div>

        <div class="flex-1 flex flex-col items-center justify-center p-4 md:p-6 relative z-10 my-auto">

            <!-- ESTADO: ESPERANDO DISPARO -->
            <Transition enter-active-class="duration-500 ease-out" enter-from-class="opacity-0 scale-90 translate-y-8"
                enter-to-class="opacity-100 scale-100 translate-y-0" leave-active-class="duration-300 ease-in"
                leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-90">

                <div v-if="!resultado" class="text-center space-y-8">
                    <div class="relative inline-block group">
                        <div class="absolute inset-0 bg-orange-500 blur-[60px] opacity-30 animate-pulse-fast"></div>

                        <!-- Radar / Scanner Icon -->
                        <div
                            class="relative border-4 border-slate-700/50 rounded-[2rem] p-8 md:p-10 bg-slate-800/30 backdrop-blur-sm shadow-[0_0_50px_rgba(0,0,0,0.3)] overflow-hidden">
                            <!-- Línea de escaneo láser naranja -->
                            <div
                                class="absolute inset-0 h-1 bg-orange-500 shadow-[0_0_15px_#f97316] w-full animate-laser z-20">
                            </div>

                            <svg class="w-24 h-24 md:w-32 md:h-32 mx-auto text-slate-500 relative z-10" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                    d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <h2
                            class="text-4xl md:text-5xl font-black text-white/30 tracking-tighter uppercase italic drop-shadow-lg">
                            Esperando Lectura</h2>
                        <div class="flex items-center justify-center gap-3">
                            <span class="h-2 w-2 bg-emerald-500 rounded-full animate-pulse"></span>
                            <p
                                class="text-emerald-500/80 font-mono tracking-[0.3em] text-xs md:text-sm font-bold shadow-emerald-500/50 drop-shadow-md">
                                SISTEMA_LISTO // LÁSER_ACTIVO</p>
                        </div>
                    </div>
                </div>
            </Transition>

            <!-- ESTADO: RESULTADO (BIENVENIDA / ERROR) -->
            <Transition enter-active-class="duration-[600ms] ease-[cubic-bezier(0.34,1.56,0.64,1)]"
                enter-from-class="opacity-0 translate-y-24 scale-50 rotate-3"
                enter-to-class="opacity-100 translate-y-0 scale-100 rotate-0">

                <div v-if="resultado" class="w-full max-w-4xl text-center transform-gpu py-4">

                    <!-- Icono Gigante de Estado Flotante -->
                    <div class="mb-4 md:mb-6 flex justify-center animate-bounce-slow">
                        <span v-if="resultado.status === 'success'"
                            class="inline-flex p-4 md:p-5 rounded-full bg-white/20 text-white shadow-[0_0_80px_rgba(255,255,255,0.4)] ring-8 ring-white/30 backdrop-blur-md">
                            <svg class="w-16 h-16 md:w-20 md:h-20 drop-shadow-lg" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                        </span>
                        <span v-else
                            class="inline-flex p-4 md:p-5 rounded-full bg-white/20 text-white shadow-[0_0_80px_rgba(255,0,0,0.4)] ring-8 ring-white/30 backdrop-blur-md">
                            <svg class="w-16 h-16 md:w-20 md:h-20 drop-shadow-lg" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </span>
                    </div>

                    <!-- Ajustamos los textos para que no sean tan exageradamente grandes -->
                    <h2
                        class="text-5xl md:text-7xl font-black text-white tracking-tighter uppercase italic drop-shadow-[0_5px_5px_rgba(0,0,0,0.5)] mb-2">
                        {{ resultado.titulo }}
                    </h2>
                    <p
                        class="text-xl md:text-2xl text-white/90 font-bold mb-8 md:mb-10 uppercase tracking-[0.2em] drop-shadow-md">
                        {{ resultado.mensaje }}
                    </p>

                    <!-- Tarjeta del Trabajador (Redujimos un poco el padding) -->
                    <div v-if="resultado.persona"
                        class="bg-gradient-to-br from-black/60 to-black/30 backdrop-blur-2xl border-2 border-white/20 rounded-[2.5rem] p-6 md:p-10 shadow-[0_30px_60px_rgba(0,0,0,0.6)] flex flex-col md:flex-row items-center gap-6 md:gap-10 text-left overflow-hidden relative group mx-auto max-w-3xl">

                        <!-- Resplandor dinámico de fondo en la tarjeta -->
                        <div class="absolute -top-32 -right-32 w-64 h-64 blur-[80px] rounded-full transition-colors duration-500"
                            :class="resultado.status === 'success' ? 'bg-emerald-400/30' : 'bg-red-500/30'"></div>

                        <!-- Imagen "Grande" (Inicial del nombre) ajustada -->
                        <div
                            class="w-32 h-32 md:w-40 md:h-40 bg-gradient-to-br from-white/20 to-transparent rounded-3xl border-2 border-white/30 flex items-center justify-center text-6xl md:text-7xl font-black text-white shadow-2xl flex-shrink-0 relative overflow-hidden ring-4 ring-black/20">
                            {{ resultado.persona.nombres[0] }}

                            <!-- Escáner dinámico pasando sobre la foto -->
                            <div class="absolute inset-0 bg-gradient-to-b h-1/2 w-full -translate-y-full animate-[scan_1.5s_ease-in-out_infinite]"
                                :class="resultado.status === 'success' ? 'from-transparent via-white/50 to-emerald-200/50' : 'from-transparent via-white/50 to-red-200/50'">
                                <div class="absolute bottom-0 w-full h-1 shadow-[0_0_10px_currentColor]"
                                    :class="resultado.status === 'success' ? 'bg-emerald-300 text-emerald-300' : 'bg-red-400 text-red-400'">
                                </div>
                            </div>
                        </div>

                        <div class="flex-1 space-y-4 md:space-y-6 z-10 w-full text-center md:text-left">
                            <div>
                                <h3
                                    class="text-white/50 text-xs md:text-sm font-black uppercase tracking-[0.4em] mb-2 flex items-center justify-center md:justify-start gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                        </path>
                                    </svg>
                                    Identidad Verificada
                                </h3>
                                <div class="text-3xl md:text-5xl font-black text-white leading-tight drop-shadow-lg">
                                    {{ resultado.persona.nombres }}<br />
                                    <span class="text-white/70">{{ resultado.persona.apellidos }}</span>
                                </div>
                            </div>

                            <div
                                class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-6 pt-4 md:pt-6 border-t border-white/20">
                                <div class="bg-white/5 rounded-2xl p-4 border border-white/10 backdrop-blur-sm">
                                    <div
                                        class="text-white/40 text-[10px] md:text-[11px] font-black uppercase tracking-[0.2em] mb-1">
                                        Empresa</div>
                                    <div class="text-lg md:text-xl font-bold text-white uppercase truncate">{{
                                        resultado.persona.nombre_empresa }}</div>
                                </div>
                                <div class="bg-white/5 rounded-2xl p-4 border border-white/10 backdrop-blur-sm">
                                    <div
                                        class="text-white/40 text-[10px] md:text-[11px] font-black uppercase tracking-[0.2em] mb-1">
                                        DNI / Documento</div>
                                    <div class="text-xl md:text-2xl font-mono font-bold text-white tracking-[0.2em]">{{
                                        resultado.persona.documento }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>

        </div>

        <!-- Input invisible para recibir el escaneo -->
        <form @submit.prevent="procesarEscaneo" class="absolute top-0 left-0 h-0 w-0 overflow-hidden opacity-0">
            <input type="text" ref="inputEscaner" v-model="documento" autocomplete="off" @blur="enfocarInput">
        </form>
    </div>
</template>

<style scoped>
/* Animación del láser pasando sobre la foto */
@keyframes scan {
    0% {
        transform: translateY(-100%);
    }

    100% {
        transform: translateY(200%);
    }
}

/* Animación del láser en la pantalla de espera */
@keyframes laser {
    0% {
        transform: translateY(0);
        opacity: 0;
    }

    10% {
        opacity: 1;
    }

    90% {
        opacity: 1;
    }

    100% {
        transform: translateY(160px);
        opacity: 0;
    }
}

.animate-laser {
    animation: laser 2s cubic-bezier(0.4, 0, 0.2, 1) infinite;
}

/* Efectos de pulso expansivo para el éxito */
@keyframes ping-slow {
    0% {
        transform: scale(0.5);
        opacity: 0.8;
    }

    100% {
        transform: scale(2);
        opacity: 0;
    }
}

@keyframes ping-slower {
    0% {
        transform: scale(0.2);
        opacity: 1;
    }

    100% {
        transform: scale(2.5);
        opacity: 0;
    }
}

.animate-ping-slow {
    animation: ping-slow 3s cubic-bezier(0, 0, 0.2, 1) infinite;
}

.animate-ping-slower {
    animation: ping-slower 4s cubic-bezier(0, 0, 0.2, 1) infinite 1s;
}

/* Flote sutil */
@keyframes bounce-slow {

    0%,
    100% {
        transform: translateY(-5%);
    }

    50% {
        transform: translateY(5%);
    }
}

.animate-bounce-slow {
    animation: bounce-slow 3s ease-in-out infinite;
}

/* Movimiento del patrón de fondo */
@keyframes slide-bg {
    0% {
        background-position: 0 0;
    }

    100% {
        background-position: 30px 30px;
    }
}

.animate-slide-bg {
    animation: slide-bg 4s linear infinite;
}
</style>
