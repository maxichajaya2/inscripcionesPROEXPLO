<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import colorbar from '@/Components/colorbar.vue';
import GreenArrowRight from '@/Components/GreenArrowRight.vue';
import { router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

// Estilos globales
import "../../../css/inscripciones.css";

// 1. Recibimos las categorías desde el Controlador (InscripcionController)
// Recuerda que en tu controlador debes estar enviando: compact('categorias')
const props = defineProps({
    categorias: {
        type: Array,
        default: () => []
    }
});

// 2. Controlamos qué grupo está seleccionado (null = ninguno, muestra la nota)
const grupoSeleccionado = ref(null);

// 3. Filtramos las categorías según la selección
const categoriasVisibles = computed(() => {
    if (!grupoSeleccionado.value) return [];
    return props.categorias.filter(cat => cat.grupo === grupoSeleccionado.value);
});

// 4. Función para ir al formulario
const irAlFormulario = (categoriaId) => {
    router.get(route('registro.formulario'), { category: categoriaId });
};
</script>

<template>
    <AppLayout title="Inscripciones" class="bg-gradient-wmc">
        <div class="px-6 py-12 mx-auto max-w-6xl min-h-[80vh] flex flex-col justify-center font-sans">

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

                <div class="w-full lg:w-5/12 space-y-4">

                    <button
                        @click="grupoSeleccionado = 'participante'"
                        :class="grupoSeleccionado === 'participante'
                            ? 'bg-white/20 border-[#1d4ed8] shadow-[0_0_25px_rgba(29,78,216,0.4)] scale-[1.02]'
                            : 'bg-white/10 border-white/20 hover:bg-white/20 hover:border-[#1d4ed8]'"
                        class="w-full group relative flex items-center justify-between p-6 backdrop-blur-md border rounded-2xl transition-all duration-300 text-left">

                        <div class="flex flex-col">
                            <span class="text-xs uppercase tracking-widest text-blue-400 font-bold mb-1">Registration</span>
                            <h5 class="text-2xl font-black text-white group-hover:text-blue-400 transition-colors">
                                GENERAL PARTICIPANT
                            </h5>
                        </div>

                        <div :class="grupoSeleccionado === 'participante' ? 'bg-[#1d4ed8]' : 'bg-white/10 group-hover:bg-[#1d4ed8]'"
                             class="p-3 rounded-xl transition-all duration-300">
                            <GreenArrowRight class="w-6 h-6 invert brightness-200" />
                        </div>
                    </button>

                    <button
                        @click="grupoSeleccionado = 'autor'"
                        :class="grupoSeleccionado === 'autor'
                            ? 'bg-white/20 border-[#06b6d4] shadow-[0_0_25px_rgba(6,182,212,0.4)] scale-[1.02]'
                            : 'bg-white/10 border-white/20 hover:bg-white/20 hover:border-[#06b6d4]'"
                        class="w-full group relative flex items-center justify-between p-6 backdrop-blur-md border rounded-2xl transition-all duration-300 text-left">

                        <div class="flex flex-col">
                            <span class="text-xs uppercase tracking-widest text-cyan-400 font-bold mb-1">Registration</span>
                            <h5 class="text-2xl font-black text-white group-hover:text-cyan-400 transition-colors">
                                AUTHOR SPECIAL
                            </h5>
                        </div>

                        <div :class="grupoSeleccionado === 'autor' ? 'bg-[#06b6d4]' : 'bg-white/10 group-hover:bg-[#06b6d4]'"
                             class="p-3 rounded-xl transition-all duration-300">
                            <GreenArrowRight class="w-6 h-6 invert brightness-200" />
                        </div>
                    </button>
                </div>

                <div class="w-full lg:w-7/12 mt-8 lg:mt-0 relative min-h-[300px]">

                    <div v-if="grupoSeleccionado" class="flex flex-col gap-4 animate-fade-in-right">

                        <div v-for="cat in categoriasVisibles" :key="cat.id"
                             @click="irAlFormulario(cat.id)"
                             class="w-full cursor-pointer group relative flex flex-row items-center justify-between p-6 bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl hover:bg-white/15 hover:border-yellow-price/50 hover:shadow-[0_0_15px_rgba(250,204,21,0.2)] transition-all duration-300">

                            <div class="flex-1 pr-4">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-white/10 text-white/70">
                                        {{ cat.grupo === 'autor' ? 'Author Rate' : 'Attendee Rate' }}
                                    </span>
                                </div>
                                <h4 class="text-lg md:text-xl font-bold text-white leading-tight group-hover:text-yellow-price transition-colors">
                                    {{ cat.nombre_en }}
                                </h4>
                                <p class="text-xs text-gray-400 mt-1">
                                   {{ cat.categoria_description }}
                                </p>
                            </div>

                            <div class="text-right flex flex-col items-end">
                                <span class="text-2xl md:text-3xl font-black text-yellow-price">
                                    {{ cat.precio_disponible?.moneda?.simbolo || '$' }}{{ cat.precio_disponible?.valor || '0' }}
                                </span>
                                <span class="text-[10px] uppercase text-gray-400 font-bold mb-2 block">
                                    + TAX / IVA
                                </span>
                                <div class="text-xs font-bold text-cyan-400 group-hover:text-white flex items-center transition-colors">
                                    Select <GreenArrowRight class="w-3 h-3 ml-1 invert brightness-150" />
                                </div>
                            </div>
                        </div>

                    </div>

                    <div v-else class="h-full flex flex-col justify-center p-8 bg-black/20 backdrop-blur-sm border-l-4 border-yellow-price rounded-r-2xl animate-fade-in">
                        <h6 class="text-yellow-price font-bold uppercase tracking-tighter mb-2">Important Note</h6>
                        <p class="text-white/80 text-sm leading-relaxed">
                            The World Mining Congress 2026 registration system is designed to manage participant
                            registrations and payment processing.
                            <br><br>
                            <strong>Please select your profile on the left</strong> (General or Author) to reveal the available categories and prices list.
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

<style scoped>
/* Animación de entrada para la lista derecha */
.animate-fade-in-right {
    animation: fadeInRight 0.4s ease-out forwards;
}

@keyframes fadeInRight {
    from {
        opacity: 0;
        transform: translateX(10px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Animación simple de fade */
.animate-fade-in {
    animation: fadeIn 0.5s ease-out;
}
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
</style>
