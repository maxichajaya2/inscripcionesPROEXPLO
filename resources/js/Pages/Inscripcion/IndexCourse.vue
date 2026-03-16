<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import colorbar from '@/Components/colorbar.vue';
import GreenArrowRight from '@/Components/GreenArrowRight.vue';
import { router } from '@inertiajs/vue3';
import { ref, computed, nextTick } from 'vue';

// Estilos globales de Proexplo
import "../../../css/inscripciones.css";

const props = defineProps({
    categorias: {
        type: Array,
        default: () => []
    },
    cursos: Array
});

// ESTADO INICIAL: Forzamos 'viajes' y perfil 'general' para carga inmediata
const macroSeccion = ref('viajes');
const grupoSeleccionado = ref('general');
const copiado = ref(false);

const seleccionarGrupo = (grupo) => {
    grupoSeleccionado.value = grupo;
    scrollToCategories();
};

const categoriasVisibles = computed(() => {
    if (!grupoSeleccionado.value) return [];

    return props.categorias.filter(cat => {
        const nombreEn = cat.nombre_en.toUpperCase();

        if (grupoSeleccionado.value === 'general') {
            return !nombreEn.includes('ESTUDIANTE') && !nombreEn.includes('DOCENTE') && !nombreEn.includes('STUDENT');
        }

        if (grupoSeleccionado.value === 'estudiante') {
            return nombreEn.includes('ESTUDIANTE') || nombreEn.includes('STUDENT');
        }

        if (grupoSeleccionado.value === 'docente') {
            return nombreEn.includes('DOCENTE');
        }

        return false;
    });
});

const irAlFormulario = (id) => {
    const categoriasLista = Object.values(props.categorias);
    const categoria = categoriasLista.find(c => c.id === id);

    if (!categoria) {
        console.error("No se encontró la categoría con ID:", id);
        return;
    }

    let nombreRuta = '';
    const perfilId = categoria.id_perfil;

    // Mapeo de rutas según perfil
    if (perfilId === 1 || perfilId === 2) {
        nombreRuta = 'inscripcion.general';
    } else if (perfilId === 3 || perfilId === 4) {
        nombreRuta = 'inscripcion.estudiante';
    } else if (perfilId === 5) {
        nombreRuta = 'inscripcion.docente';
    } else {
        nombreRuta = 'inscripcion.participante';
    }

    const listaCursos = (props.cursos && props.cursos.length > 0)
        ? props.cursos.join(',')
        : '0';

    router.get(route(nombreRuta), {
        category: id,
        section: macroSeccion.value,
        profile: perfilId,
        course: listaCursos
    });
};

const scrollToCategories = () => {
    nextTick(() => {
        const element = document.getElementById('section-categories');
        if (element) {
            const yOffset = -20;
            const y = element.getBoundingClientRect().top + window.pageYOffset + yOffset;
            window.scrollTo({ top: y, behavior: 'smooth' });
        }
    });
};
</script>

<template>
    <AppLayout class="bg-proexplo-dark">
        <div class="px-6 py-12 mx-auto max-w-6xl min-h-[80vh] flex flex-col justify-center font-sans">

            <div class="banner-proexplo-early animate-fade-in-down mb-8">
                <div class="p-3 md:p-6 flex items-center gap-3">
                    <div class="banner-icon-orange shrink-0">
                        <span class="text-xl md:text-2xl text-white">🔥</span>
                    </div>
                    <div class="text-left">
                        <div class="flex items-center gap-2 mb-0.5">
                            <span class="tag-proexplo text-[10px] md:text-xs px-2 py-0.5">TARIFAS PREVENTA</span>
                        </div>
                        <h2 class="text-base md:text-2xl font-black text-slate-800 leading-tight">
                            ¡Aprovecha los precios preventa de PROEXPLO!
                        </h2>
                        <p class="text-slate-600 text-xs md:text-base leading-snug">
                            Inscríbete antes del <strong>31 de Marzo</strong> y asegura tu participación.
                        </p>
                    </div>
                </div>
            </div>

            <div id="titulo_inicial" class="mb-12 text-left animate-fade-in-down">
                <h1 class="text-4xl md:text-5xl font-black pro-orange tracking-tight mb-2">
                    XV Congreso Internacional <span class="text-green-500">PROEXPLO 2026</span>
                </h1>
                <h3 class="text-xl md:text-2xl text-white font-medium opacity-90 mb-4">
                    Cursos Cortos y Visitas Técnicas
                </h3>
                <div class="block w-48 h-1.5 rounded-full bg-orange-500 shadow-[0_0_15px_rgba(249,115,22,0.5)]"></div>
            </div>

            <div class="flex flex-col lg:flex-row gap-8 items-start">

                <div class="w-full lg:w-4/12 space-y-4 animate-fade-in-right">
                    <p class="text-white/50 text-[10px] font-bold uppercase tracking-widest ml-2 mb-2">Filtrar por
                        perfil:</p>

                    <button v-for="perfil in ['general', 'docente', 'estudiante']" :key="perfil"
                        @click="seleccionarGrupo(perfil)" :class="grupoSeleccionado === perfil
                            ? 'bg-orange-600 border-orange-600 shadow-lg shadow-orange-500/30 scale-[1.02]'
                            : 'bg-white border-slate-200 hover:border-orange-400 hover:bg-orange-50 shadow-sm'"
                        class="w-full group p-5 rounded-3xl transition-all text-left flex justify-between items-center border uppercase">

                        <div class="z-10">
                            <span :class="grupoSeleccionado === perfil ? 'text-orange-200' : 'text-orange-600'"
                                class="text-[9px] font-bold uppercase tracking-widest mb-1 block">
                                Selección
                            </span>
                            <h5 :class="grupoSeleccionado === perfil ? 'text-white' : 'text-slate-800'"
                                class="text-xl font-black transition-colors">
                                {{ perfil }}
                            </h5>
                        </div>

                        <div :class="grupoSeleccionado === perfil ? 'bg-white/20' : 'bg-slate-100 group-hover:bg-orange-100'"
                            class="p-4 rounded-2xl transition-all">
                            <GreenArrowRight :class="grupoSeleccionado === perfil ? 'w-4 h-4 invert' : 'w-4 h-4'" />
                        </div>
                    </button>

                    <div class="pt-6 border-t border-white/10 mt-6">
                        <button @click="router.get(route('inscripciones.index'))"
                            class="w-full py-3 text-white/60 hover:text-white text-[10px] font-bold uppercase tracking-widest transition-colors">
                            Ir a Inscripción al Congreso
                        </button>
                    </div>
                </div>

                <div class="w-full lg:w-8/12 relative min-h-[300px]">
                    <div id="section-categories" class="flex flex-col gap-4 animate-fade-in-right">

                        <div class="flex items-center justify-between px-2 mb-1">
                            <h6 class="text-orange-500 text-[10px] font-bold uppercase tracking-[0.2em]">
                                Seleccione una Actividad
                            </h6>
                        </div>

                        <div v-for="cat in categoriasVisibles" :key="cat.id" @click="irAlFormulario(cat.id)"
                            class="w-full cursor-pointer group relative flex flex-row items-center justify-between p-6 rounded-3xl bg-white border border-slate-200 transition-all hover:bg-orange-50 hover:border-orange-500 hover:scale-[1.02] shadow-sm hover:shadow-md">

                            <div class="relative z-10 flex-1 pr-6">
                                <div class="flex items-center gap-2 mb-2">
                                    <span
                                        class="text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-green-100 text-green-700">
                                        PROEXPLO 2026
                                    </span>
                                </div>
                                <h4
                                    class="text-lg md:text-xl font-bold text-slate-800 leading-tight group-hover:text-orange-700 transition-colors uppercase">
                                    {{ cat.nombre_en }}
                                </h4>
                                <p class="text-xs text-slate-500 mt-1 group-hover:text-slate-600 transition-colors">
                                    {{ cat.categoria_description }}
                                </p>
                            </div>

                            <div
                                class="relative z-10 text-right flex flex-col items-end border-l border-slate-100 pl-6 group-hover:border-orange-200 transition-colors">
                                <!-- <span
                                    class="text-2xl font-black text-slate-900 group-hover:text-orange-600 transition-colors">
                                    {{ cat.precio_disponible?.moneda?.simbolo || 'USD ' }}{{
                                        cat.precio_disponible?.valor || '0' }}
                                </span> -->
                                <div
                                    class="mt-3 px-6 py-2 rounded-full bg-orange-600 text-white text-[10px] font-bold uppercase group-hover:bg-orange-700 transition-all shadow-sm">
                                    Inscribirse
                                </div>
                            </div>
                        </div>

                        <div v-if="categoriasVisibles.length === 0"
                            class="p-12 text-center border-2 border-dashed border-white/10 rounded-3xl animate-fade-in">
                            <p class="text-white/40 italic">No hay actividades disponibles para este perfil.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-full mt-12 animate-fade-in-up">
                <div
                    class="flex flex-col md:flex-row justify-between items-center border-orange-200 bg-orange-50/30 rounded-3xl border p-6 md:p-8 gap-6">
                    <div class="flex justify-center min-w-[80px]">
                        <div class="p-4 bg-orange-100 rounded-2xl">
                            <img src="/images/icon-fence.svg" class="w-10 h-10">
                        </div>
                    </div>
                    <div class="flex-1 text-center md:text-left">
                        <h6 class="text-xl font-black text-slate-800 uppercase mb-2">Otros participantes</h6>
                        <p class="text-slate-600 leading-relaxed">
                            Si eres auspiciador, exhibidor o prensa, comunícate con nosotros al correo
                            <a href="mailto:inscripciones@iimp.org.pe"
                                class="text-orange-600 font-bold hover:underline">
                                inscripciones@iimp.org.pe
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
<style scoped>
/* Fondo Proexplo Dark */
.bg-proexplo-dark {
    background: radial-gradient(circle at top right, #ffffff 0%, #ffffff 100%);
}

/* Banner de Preventa Naranja */
.banner-proexplo-early {
    background: #fff7ed;
    /* Naranja muy clarito */
    border: 1px solid #ffedd5;
    border-left: 6px solid #f97316;
    border-radius: 24px;
}

.banner-icon-orange {
    background: rgba(249, 115, 22, 0.2);
    width: 56px;
    height: 56px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(249, 115, 22, 0.3);
}

.tag-proexplo {
    background: #f97316;
    color: white;
    font-weight: 900;
    border-radius: 6px;
}

/* Efecto Laser Naranja */
@keyframes rotate-border {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}

.border-proexplo-loading {
    position: relative;
    overflow: hidden;
    animation: border-pulse 2.5s infinite ease-in-out;
}

.border-anim-orange::before {
    content: "";
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: conic-gradient(transparent, transparent, transparent, #f97316, #ffffff, #f97316, transparent);
    animation: rotate-border 3s linear infinite;
    z-index: 0;
}

.border-proexplo-loading::after {
    content: "";
    position: absolute;
    inset: 3px;
    /* Espacio para que se vea el borde animado */
    background: linear-gradient(135deg, #fb923c 0%, #f97316 50%, #ea580c 100%);
    border-radius: 22px;
    z-index: 0;
}

@keyframes border-pulse {

    0%,
    100% {
        border-color: rgba(249, 115, 22, 0.4);
        box-shadow: 0 0 15px rgba(249, 115, 22, 0.2);
    }

    50% {
        border-color: #fb923c;
        box-shadow: 0 0 30px rgba(249, 115, 22, 0.5);
    }
}

.glow-orange {
    box-shadow: 0 0 20px rgba(249, 115, 22, 0.2);
}

/* Animaciones */
.animate-fade-in-down {
    animation: fadeInDown 0.6s ease-out forwards;
}

.animate-fade-in-right {
    animation: fadeInRight 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeInRight {
    from {
        opacity: 0;
        transform: translateX(20px);
    }

    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.animate-fade-in-up {
    animation: fadeInUp 0.8s ease-out forwards;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
