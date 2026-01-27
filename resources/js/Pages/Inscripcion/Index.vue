<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import colorbar from '@/Components/colorbar.vue';
import GreenArrowRight from '@/Components/GreenArrowRight.vue';
import { router, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, nextTick } from 'vue';

// Estilos globales
import "../../../css/inscripciones.css";

const props = defineProps({
    categorias: {
        type: Array,
        default: () => []
    }
});


const grupoSeleccionado = ref(null);
const macroSeccion = ref(null); // 'inscripciones' o 'viajes'

const seleccionarGrupo = (grupo) => {
    grupoSeleccionado.value = grupo;
    scrollToCategories();
};

// const categoriasVisibles = computed(() => {
//     if (!grupoSeleccionado.value) return [];
//     return props.categorias.filter(cat => cat.grupo === grupoSeleccionado.value);
// });

const categoriasVisibles = computed(() => {
    if (!grupoSeleccionado.value) return [];

    // Filtramos por el grupo (autor/participante)
    // y podrías añadir un filtro extra si tu base de datos tiene algo para "viajes"
    return props.categorias.filter(cat => cat.grupo === grupoSeleccionado.value);
})

const volverAMacro = () => {
    macroSeccion.value = null;
    grupoSeleccionado.value = null;
};

// const irAlFormulario = (id) => {
//     // Buscamos la categoría para saber su grupo
//     const categoria = props.categorias.find(c => c.id === id);

//     if (categoria.grupo === 'autor') {
//         router.get(route('inscripcion.autor'), { category: id });
//     } else {
//         router.get(route('inscripcion.participante'), { category: id });
//     }
// };

const irAlFormulario = (id) => {
    const categoria = props.categorias.find(c => c.id === id);

    // Preparamos los parámetros
    const params = {
        category: id,
        section: macroSeccion.value,// Esto enviará 'inscripciones' o 'viajes'
        profile: categoria.id_perfil
    };

    if (categoria.grupo === 'autor') {
        router.get(route('inscripcion.autor'), params);
    } else {
        router.get(route('inscripcion.participante'), params);
    }
};

const scrollToCategories = () => {
    nextTick(() => {
        // Buscamos el ID que pusimos en el HTML
        const element = document.getElementById('section-categories');
        if (element) {
            // Ajuste: offset para que no quede pegado al borde superior
            const yOffset = -20; 
            const y = element.getBoundingClientRect().top + window.pageYOffset + yOffset;
            
            window.scrollTo({ top: y, behavior: 'smooth' });
        }
    });
};
</script>

<template>
    <AppLayout class="bg-gradient-wmc">
        <div class="px-6 py-12 mx-auto max-w-6xl min-h-[80vh] flex flex-col justify-center font-sans">
            <div class="preventa-banner-home animate-fade-in-down mb-8">
                <div class="banner-home-content p-3 md:p-6 flex items-center gap-3">
                    <div class="banner-home-icon shrink-0">
                        <span class="text-xl md:text-2xl">⏳</span>
                    </div>
                    <div class="banner-home-text text-left">
                        <div class="flex items-center gap-2 mb-0.5">
                            <span class="tag-early-bird text-[10px] md:text-xs px-2 py-0.5">EARLY BIRD RATES</span>
                        </div>
                        <h2 class="text-base md:text-2xl font-black text-white leading-tight">
                            Take advantage of Early Bird rates!
                        </h2>
                        <p class="text-white/80 text-xs md:text-base leading-snug">
                            Register by <strong>March 31st</strong> and secure your spot.
                        </p>
                    </div>
                </div>
            </div>
            <div id="titulo_inicial" class="mb-12 text-left animate-fade-in-down">
                <h1 class="text-4xl md:text-5xl font-black text-yellow-price tracking-tight mb-2">
                    World Mining Congress <span class="text-white">2026</span>
                </h1>
                <h3 class="text-xl md:text-2xl text-cyan-50 font-medium opacity-90 mb-4">
                    Select your profile
                </h3>
                <colorbar class="block w-48 h-1.5 rounded-full" />
            </div>

            <div class="flex flex-col lg:flex-row gap-8 items-start">

                <div class="w-full lg:w-5/12 space-y-6">

                    <div v-if="!macroSeccion" class="space-y-6 animate-fade-in">
                        <button @click="macroSeccion = 'inscripciones'"
                            class="w-full group relative flex items-center justify-between p-8 bg-white/5 border border-white/10 rounded-3xl backdrop-blur-md transition-all duration-300 hover:border-blue-500 hover:bg-white/10 hover:shadow-[0_0_20px_rgba(59,130,246,0.3)] text-left">
                            <div class="flex flex-col z-10">
                                <div class="flex flex-col z-10">
                                    <span class="text-blue-400 text-xs uppercase tracking-widest font-bold mb-1">
                                        Register now and get access to courses and tours
                                    </span>
                                    <h5
                                        class="text-2xl font-black text-white group-hover:text-blue-200 transition-colors">
                                        REGISTRATION
                                    </h5>
                                </div>
                            </div>
                            <div
                                class="p-4 rounded-2xl bg-white/10 group-hover:bg-blue-600 transition-all duration-300">
                                <GreenArrowRight class="w-6 h-6 invert brightness-200" />
                            </div>
                        </button>

                        <button @click="macroSeccion = 'viajes'"
                            class="w-full group relative flex items-center justify-between p-8 bg-white/5 border border-white/10 rounded-3xl backdrop-blur-md transition-all duration-300 hover:border-green-500 hover:bg-white/10 hover:shadow-[0_0_20px_rgba(34,197,94,0.3)] text-left">
                            <div class="flex flex-col z-10">
                                <span class="text-green-400 text-xs uppercase tracking-widest font-bold mb-1">
                                    Exclusive technical visits and specialized training
                                </span>
                                <h5 class="text-2xl font-black text-white group-hover:text-green-200 transition-colors">
                                    TOURS & COURSES
                                </h5>
                            </div>
                            <div
                                class="p-4 rounded-2xl bg-white/10 group-hover:bg-green-600 transition-all duration-300">
                                <GreenArrowRight class="w-6 h-6 invert brightness-200" />
                            </div>
                        </button>
                    </div>
                    <div v-else class="space-y-6 animate-fade-in-right">
                        <button @click="volverAMacro"
                            class="group flex items-center gap-3 px-4 py-2 bg-white/10 hover:bg-white/20 border border-white/20 hover:border-white/40 rounded-xl transition-all duration-300 mb-8 w-fit shadow-lg backdrop-blur-sm">
                            <div class="rotate-180 transition-transform group-hover:-translate-x-1">
                                <GreenArrowRight class="w-4 h-4 invert brightness-200" />
                            </div>
                            <span class="text-white text-xs font-black uppercase tracking-[0.15em]">
                                Back to Start
                            </span>
                        </button>

                        <h4 class="text-white/60 font-bold text-[15px] uppercase tracking-[0.2em] mb-4">
                            Select your profile for <span class="text-yellow-price">{{ macroSeccion === 'inscripciones'
                                ? 'REGISTRATION' : 'TOURS & COURSES' }}</span>:
                        </h4>
                        <button @click="seleccionarGrupo('participante')"
                            :class="grupoSeleccionado === 'participante'
                                ? 'bg-blue-900/40 border-blue-400 shadow-[0_0_35px_rgba(59,130,246,0.6)] scale-105 ring-1 ring-blue-300'
                                : 'bg-white/5 border-white/10 hover:bg-white/10 hover:border-blue-500 hover:shadow-[0_0_20px_rgba(59,130,246,0.3)]'"
                            class="w-full group relative flex items-center justify-between p-6 backdrop-blur-md border rounded-3xl transition-all duration-300 ease-out text-left">

                            <div class="flex flex-col z-10">
                                <span :class="grupoSeleccionado === 'participante' ? 'text-blue-200' : 'text-blue-400'"
                                    class="text-xs uppercase tracking-widest font-bold mb-1 transition-colors">
                                    Registration
                                </span>
                                <h5 class="text-2xl font-black text-white group-hover:text-blue-200 transition-colors">
                                    GENERAL PARTICIPANT
                                </h5>
                            </div>

                            <div :class="grupoSeleccionado === 'participante'
                                ? 'bg-blue-600 shadow-lg scale-110 rotate-0'
                                : 'bg-white/10 group-hover:bg-blue-600 group-hover:rotate-[-45deg]'"
                                class="p-4 rounded-2xl transition-all duration-300">
                                <GreenArrowRight class="w-6 h-6 invert brightness-200" />
                            </div>
                        </button>

                        <button @click="seleccionarGrupo('autor')"
                            :class="grupoSeleccionado === 'autor'
                                ? 'bg-cyan-900/40 border-cyan-400 shadow-[0_0_35px_rgba(34,211,238,0.6)] scale-105 ring-1 ring-cyan-300'
                                : 'bg-white/5 border-white/10 hover:bg-white/10 hover:border-cyan-500 hover:shadow-[0_0_20px_rgba(34,211,238,0.3)]'"
                            class="w-full group relative flex items-center justify-between p-6 backdrop-blur-md border rounded-3xl transition-all duration-300 ease-out text-left">

                            <div class="flex flex-col z-10">
                                <span :class="grupoSeleccionado === 'autor' ? 'text-cyan-200' : 'text-cyan-400'"
                                    class="text-xs uppercase tracking-widest font-bold mb-1 transition-colors">
                                    Registration
                                </span>
                                <h5 class="text-2xl font-black text-white group-hover:text-cyan-200 transition-colors">
                                    AUTHOR SPECIAL
                                </h5>
                            </div>

                            <div :class="grupoSeleccionado === 'autor'
                                ? 'bg-cyan-600 shadow-lg scale-110 rotate-0'
                                : 'bg-white/10 group-hover:bg-cyan-600 group-hover:rotate-[-45deg]'"
                                class="p-4 rounded-2xl transition-all duration-300">
                                <GreenArrowRight class="w-6 h-6 invert brightness-200" />
                            </div>
                        </button>
                    </div>
                </div>

                <div class="w-full lg:w-7/12 mt-8 lg:mt-0 relative min-h-[300px]">
                    <div v-if="grupoSeleccionado" id="section-categories" class="flex flex-col gap-5 animate-fade-in-right">

                        <div class="flex items-center justify-between px-2 mb-1">
                            <h6 class="text-white/40 text-[10px] font-bold uppercase tracking-[0.2em]">
                                SELECT CATEGORY
                            </h6>
                        </div>

                        <div v-for="cat in categoriasVisibles" :key="cat.id" @click="irAlFormulario(cat.id)"
                            class="w-full cursor-pointer group relative flex flex-row items-center justify-between p-6 rounded-3xl
                                    bg-white/5 backdrop-blur-xl border border-white/10
                                    transition-all duration-300 ease-out overflow-hidden
                                    hover:bg-gradient-to-r hover:from-yellow-900/40 hover:to-transparent
                                    hover:border-yellow-400/80 hover:shadow-[0_0_35px_rgba(234,179,8,0.4)] hover:scale-[1.02] hover:-translate-y-1">

                            <div
                                class="absolute inset-0 bg-yellow-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500 blur-xl">
                            </div>

                            <div class="relative z-10 flex-1 pr-6">
                                <div class="flex items-center gap-2 mb-2">
                                    <span
                                        class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-white/10 text-white/60 group-hover:bg-yellow-500/20 group-hover:text-yellow-200 transition-colors">
                                        {{ cat.grupo === 'autor' ? 'Author Rate' : 'Attendee Rate' }}
                                    </span>
                                </div>
                                <h4
                                    class="text-lg md:text-xl font-bold text-white leading-tight group-hover:text-yellow-100 transition-colors">
                                    {{ cat.nombre_en }}
                                </h4>
                                <p
                                    class="text-xs text-gray-400 mt-1 line-clamp-2 group-hover:text-yellow-100/70 transition-colors">
                                    {{ cat.categoria_description }}
                                </p>
                            </div>

                            <div class="relative z-10 text-right flex flex-col items-end border-l border-white/10 pl-6 group-hover:border-yellow-500/30 transition-colors">
                                <!-- <span
                                    class="text-2xl md:text-4xl font-black text-yellow-price drop-shadow-[0_2px_10px_rgba(234,179,8,0.3)] group-hover:scale-110 transition-transform duration-300 origin-right">
                                    {{ cat.precio_disponible?.moneda?.simbolo || '$' }}{{ cat.precio_disponible?.valor
                                        || '0' }}
                                </span> -->
                                <span v-if="macroSeccion === 'inscripciones'"
                                    class="text-2xl md:text-4xl font-black text-yellow-price drop-shadow-[0_2px_10px_rgba(234,179,8,0.3)] group-hover:scale-110 transition-transform duration-300 origin-right">
                                    {{ cat.precio_disponible?.moneda?.simbolo || '$' }}{{ cat.precio_disponible?.valor
                                        || '0' }}
                                </span>
                                <!--    <span class="text-[9px] uppercase text-gray-400 font-bold mb-3 block tracking-wider">
                                    + TAX / IVA
                                </span>
                            -->
                                <div
                                    class="px-4 py-2 rounded-full flex items-center gap-2 text-xs font-bold transition-all duration-300
                                            border border-yellow-500/30 text-yellow-400 bg-yellow-500/5
                                            group-hover:bg-yellow-500 group-hover:text-black group-hover:shadow-[0_0_15px_rgba(234,179,8,0.6)]">
                                    {{ macroSeccion === 'inscripciones' ? 'Select' : 'View Details' }}
                                    <GreenArrowRight
                                        class="w-3 h-3 transition-all duration-300 group-hover:invert group-hover:brightness-0" />
                                </div>
                            </div>
                        </div>

                    </div>


                    <div v-else class="w-full  mt-8 md:mt-0">
                        <div class="p-8 bg-black/20 backdrop-blur-sm border-l-4 border-yellow-price rounded-r-2xl">
                            <h6 class="text-yellow-price font-bold uppercase tracking-tighter mb-2">Important Note
                            </h6>
                            <p class="text-white/80 text-sm leading-relaxed">
                                The World Mining Congress 2026 registration system is designed to manage participant
                                registrations and payment processing.
                                <br><br>
                                For any questions or technical issues related to the system or paper submissions,
                                please
                                contact
                                <span class="text-cyan-400 font-bold underline">wmc.itsupport@iimp.org.pe</span>
                                for assistance.
                            </p>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.animate-fade-in-right {
    animation: fadeInRight 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
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

.animate-fade-in {
    animation: fadeIn 0.8s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }

    to {
        opacity: 1;
    }
}

    .preventa-banner-home {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.15) 0%, rgba(245, 158, 11, 0.05) 100%);
        border: 1px solid rgba(245, 158, 11, 0.3);
        border-left: 6px solid #f59e0b;
        border-radius: 24px;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px); /* Para soporte en Safari/iPhone */
        position: relative;
        overflow: hidden;
    }

    .banner-home-content {
        display: flex;
        align-items: center;
        padding: 1.5rem;
        gap: 1.25rem;
    }

    .banner-home-icon {
        background: rgba(245, 158, 11, 0.2);
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border: 1px solid rgba(245, 158, 11, 0.2);
    }

    .tag-early-bird {
        background: #f59e0b;
        color: #000;
        font-size: 0.65rem;
        font-weight: 900;
        padding: 2px 8px;
        border-radius: 6px;
        letter-spacing: 0.05em;
    }

    .banner-home-text h2 {
        line-height: 1.2;
        margin-bottom: 4px;
    }

    /* Adaptación para Mobile */
    @media (max-width: 640px) {
        .banner-home-content {
            padding: 1.25rem;
            flex-direction: column;
            text-align: center;
        }
        
        .banner-home-icon {
            width: 48px;
            height: 48px;
        }

        .banner-home-text h2 {
            font-size: 1.25rem;
        }
        
        .banner-home-text p {
            font-size: 0.85rem;
        }
    }

    /* Animación de entrada igual a la de tus títulos */
    .animate-fade-in-down {
        animation: fadeInDown 0.6s ease-out forwards;
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
</style>
