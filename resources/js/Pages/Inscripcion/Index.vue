<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import colorbar from '@/Components/colorbar.vue';
import GreenArrowRight from '@/Components/GreenArrowRight.vue';
import { router, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, nextTick } from 'vue';

// Estilos globales de Proexplo
import "../../../css/inscripciones.css";

const props = defineProps({
    categorias: {
        type: Array,
        default: () => []
    }
});

const showSupport = ref(false);
const grupoSeleccionado = ref(null); // 'general', 'docente', 'estudiante'
const macroSeccion = ref(null);      // 'inscripciones' o 'viajes'
const copiado = ref(false);

const seleccionarGrupo = (grupo) => {
    grupoSeleccionado.value = grupo;
    scrollToCategories();
};

const copiarCorreo = () => {
    const email = 'soporte@proexplo.com.pe';
    navigator.clipboard.writeText(email).then(() => {
        copiado.value = true;
        setTimeout(() => {
            copiado.value = false;
        }, 3000);
    });
};

const categoriasVisibles = computed(() => {
    if (!grupoSeleccionado.value) return [];

    return props.categorias.filter(cat => {
        const nombreEn = cat.nombre_en.toUpperCase();

        if (grupoSeleccionado.value === 'general') {
            // Filtra los que NO sean estudiantes ni docentes
            return !nombreEn.includes('ESTUDIANTE') && !nombreEn.includes('DOCENTE') && !nombreEn.includes('STUDENT');
        }

        if (grupoSeleccionado.value === 'estudiante') {
            // Solo los que digan Estudiante
            return nombreEn.includes('ESTUDIANTE') || nombreEn.includes('STUDENT');
        }

        if (grupoSeleccionado.value === 'docente') {
            // Solo los que digan Docente
            return nombreEn.includes('DOCENTE');
        }

        return false;
    });
});

console.log('Categorías recibidas:', props.categorias);

const volverAMacro = () => {
    macroSeccion.value = null;
    grupoSeleccionado.value = null;
};

const irAlFormulario = (id) => {
    // 1. Definir la categoría primero (usando Object.values por seguridad si es un objeto)
    const categoriasLista = Object.values(props.categorias);
    const categoria = categoriasLista.find(c => c.id === id);

    if (!categoria) {
        console.error("No se encontró la categoría con ID:", id);
        return;
    }

    // 3. Determinar la ruta según el id_perfil
    let nombreRuta = '';
    const perfilId = categoria.id_perfil;

    if (perfilId === 1 || perfilId === 2) {
        nombreRuta = 'inscripcion.general';
    }
    else if (perfilId === 3 || perfilId === 4) {
        nombreRuta = 'inscripcion.estudiante';
    }
    else if (perfilId === 5) {
        nombreRuta = 'inscripcion.docente';
    }
    else {
        // Ruta por defecto en caso de que venga un ID no mapeado
        nombreRuta = 'inscripcion.participante';
    }

    // 4. Configurar parámetros
    const params = {
        category: id,
        section: macroSeccion.value,
        profile: perfilId
    };

    // 5. Navegar
    router.get(route(nombreRuta), params);
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
                            Inscríbete antes del <strong>15 de Abril</strong> y asegura tu participación.
                        </p>
                    </div>
                </div>
            </div>

            <div id="titulo_inicial" class="mb-12 text-left animate-fade-in-down">
                <h1 class="text-4xl md:text-5xl font-black text-orange-500 tracking-tight mb-2">
                    XV Congreso Internacional de Prospectores y Exploradores <span class="text-green-500">PROEXPLO
                        2026</span>
                </h1>
                <h3 class="text-xl md:text-2xl text-white font-medium opacity-90 mb-4">
                    Seleccione su tipo de inscripción
                </h3>
                <div class="block w-48 h-1.5 rounded-full bg-orange-500 shadow-[0_0_15px_rgba(249,115,22,0.5)]"></div>
            </div>

            <div class="flex flex-col lg:flex-row gap-8 items-start">

                <div class="w-full lg:w-5/12 space-y-6">

                    <div v-if="!macroSeccion" class="space-y-6 animate-fade-in">
                        <button @click="macroSeccion = 'inscripciones'"
                            class="w-full group relative flex items-center justify-between p-8 bg-gradient-to-br from-[#fb923c] via-[#f97316] to-[#ea580c] border-2 border-orange-600 rounded-3xl transition-all duration-500 hover:scale-[1.02] text-left border-proexplo-loading glow-orange overflow-hidden">

                            <div class="absolute inset-0 border-anim-orange pointer-events-none opacity-100"></div>

                            <div class="flex flex-col z-10">
                                <span
                                    class="text-orange-100 text-xs uppercase tracking-widest font-bold mb-1 drop-shadow-sm">
                                    Registro Oficial
                                </span>
                                <h5
                                    class="text-2xl font-black text-white group-hover:text-orange-50 transition-colors flex items-center gap-3 uppercase drop-shadow-md">
                                    Inscripción al Evento
                                    <span class="relative flex h-3 w-3">
                                        <span
                                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                                        <span
                                            class="relative inline-flex rounded-full h-3 w-3 bg-white shadow-[0_0_10px_#ffffff]"></span>
                                    </span>
                                </h5>
                            </div>

                            <div
                                class="p-4 rounded-2xl bg-white/20 backdrop-blur-md text-white z-10 shadow-lg group-hover:bg-white/30 transition-all border border-white/30">
                                <GreenArrowRight class="w-6 h-6 invert" />
                            </div>
                        </button>

                        <button @click="macroSeccion = 'viajes'"
                            class="w-full group relative flex items-center justify-between p-8 bg-green-50 border border-green-200 rounded-3xl transition-all duration-300 hover:border-green-500 hover:bg-green-100 text-left shadow-sm">

                            <div class="flex flex-col z-10">
                                <span class="text-green-600 text-xs uppercase tracking-widest font-bold mb-1">
                                    Actividades Extras
                                </span>
                                <h5
                                    class="text-2xl font-black text-slate-800 group-hover:text-green-700 transition-colors uppercase leading-tight">
                                    Cursos Cortos y Visitas Técnicas
                                </h5>
                            </div>

                            <div
                                class="p-4 rounded-2xl bg-green-500 text-white shadow-lg group-hover:bg-green-600 transition-all duration-300">
                                <GreenArrowRight class="w-6 h-6" />
                            </div>
                        </button>
                    </div>

                    <div v-else class="space-y-4 animate-fade-in-right">
                        <button @click="volverAMacro"
                            class="group flex items-center gap-3 px-4 py-2 bg-slate-100 hover:bg-slate-200 border border-slate-200 rounded-xl transition-all mb-8 w-fit shadow-sm">
                            <div class="rotate-180">
                                <GreenArrowRight class="w-4 h-4 text-slate-600" />
                            </div>
                            <span class="text-slate-700 text-xs font-black uppercase">Volver</span>
                        </button>

                        <button v-for="perfil in ['general', 'docente', 'estudiante']" :key="perfil"
                            @click="seleccionarGrupo(perfil)" :class="grupoSeleccionado === perfil
                                ? 'bg-orange-600 border-orange-600 shadow-lg shadow-orange-500/30 scale-[1.02]'
                                : 'bg-white border-slate-200 hover:border-orange-400 hover:bg-orange-50 shadow-sm'"
                            class="w-full group p-6 rounded-3xl transition-all text-left flex justify-between items-center border uppercase">

                            <div class="z-10">
                                <span :class="grupoSeleccionado === perfil ? 'text-orange-200' : 'text-orange-600'"
                                    class="text-[10px] font-bold uppercase tracking-widest mb-1 block">
                                    Perfil
                                </span>
                                <h5 :class="grupoSeleccionado === perfil ? 'text-white' : 'text-slate-800'"
                                    class="text-2xl font-black transition-colors">
                                    {{ perfil }}
                                </h5>
                            </div>

                            <div :class="grupoSeleccionado === perfil ? 'bg-white/20' : 'bg-slate-100 group-hover:bg-orange-100'"
                                class="p-4 rounded-2xl transition-all">
                                <GreenArrowRight :class="grupoSeleccionado === perfil ? 'w-5 h-5 invert' : 'w-5 h-5'" />
                            </div>
                        </button>
                    </div>
                </div>

                <div class="w-full lg:w-7/12 mt-8 lg:mt-0 relative min-h-[300px]">
                    <div v-if="grupoSeleccionado" id="section-categories"
                        class="flex flex-col gap-5 animate-fade-in-right">

                        <div class="flex items-center justify-between px-2 mb-1">
                            <h6 class="text-slate-400 text-[10px] font-bold uppercase tracking-[0.2em]">
                                Seleccione una categoría
                            </h6>
                        </div>

                        <div v-for="cat in categoriasVisibles" :key="cat.id" @click="irAlFormulario(cat.id)"
                            class="w-full cursor-pointer group relative flex flex-row items-center justify-between p-6 rounded-3xl bg-white border border-slate-200 transition-all hover:bg-orange-50 hover:border-orange-500 hover:scale-[1.02] shadow-sm hover:shadow-md">

                            <div class="relative z-10 flex-1 pr-6">
                                <div class="flex items-center gap-2 mb-2">
                                    <span
                                        class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-slate-100 text-slate-500 group-hover:bg-orange-100 group-hover:text-orange-600 transition-colors">
                                        PROEXPLO 2026
                                    </span>
                                </div>
                                <h4
                                    class="text-lg md:text-xl font-bold text-slate-800 leading-tight group-hover:text-orange-700 transition-colors">
                                    {{ cat.nombre_en }}
                                </h4>
                                <p class="text-xs text-slate-500 mt-1 group-hover:text-slate-600 transition-colors">
                                    {{ cat.categoria_description }}
                                </p>
                            </div>

                            <div
                                class="relative z-10 text-right flex flex-col items-end border-l border-slate-100 pl-6 group-hover:border-orange-200 transition-colors">
                                <span
                                    class="text-2xl md:text-4xl font-black text-slate-900 group-hover:text-orange-600 transition-colors">
                                    {{ cat.precio_disponible?.moneda?.simbolo || 'USD ' }}{{
                                        cat.precio_disponible?.valor || '0' }}
                                </span>
                                <div
                                    class="mt-3 px-4 py-2 rounded-full border border-orange-500 text-orange-600 bg-white text-xs font-bold group-hover:bg-orange-600 group-hover:text-white transition-all shadow-sm">
                                    Seleccionar
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="w-full mt-8 md:mt-0">
                        <div
                            class="p-8 bg-[#FFF7ED] border-l-4 border-orange-500 rounded-r-2xl shadow-sm animate-fade-in">
                            <h6
                                class="text-orange-600 font-black uppercase tracking-widest mb-4 flex items-center gap-2 text-xs md:text-sm">
                                ℹ️ Los inscritos en PROEXPLO 2026 tendrán los siguientes beneficios:
                            </h6>

                            <ul class="space-y-2 text-slate-700 text-sm">
                                <li class="flex items-start gap-2">
                                    <span class="shrink-0">✅</span>
                                    <span class="leading-relaxed">Asistencia a las conferencias con interpretación
                                        simultánea (inglés - español)</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="shrink-0">✅</span>
                                    <span class="leading-relaxed">Ingreso a la Exhibición Técnica</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="shrink-0">✅</span>
                                    <span class="leading-relaxed">Participación en las ceremonias de inauguración y
                                        clausura</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="shrink-0">✅</span>
                                    <span class="leading-relaxed">Acceso a las presentaciones en PDF
                                        (autorizadas)</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="shrink-0">✅</span>
                                    <span class="leading-relaxed">Certificado digital de participación a
                                        solicitud</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="shrink-0">✅</span>
                                    <span class="leading-relaxed">Almuerzo incluido</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="shrink-0">✅</span>
                                    <span class="leading-relaxed">Recesos de café</span>
                                </li>
                            </ul>

                            <div class="mt-4 pt-4 border-t border-orange-200 flex items-center gap-3">
                                <div class="w-2 h-2 rounded-full bg-orange-500 animate-pulse"></div>
                                <p class="text-orange-600 text-[10px] md:text-xs font-bold tracking-wider uppercase">
                                    Selecciona tu perfil a la izquierda para continuar
                                </p>
                            </div>
                        </div>
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
</style>
