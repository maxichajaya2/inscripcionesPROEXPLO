<script setup>
import { ref, computed, defineExpose, watch } from 'vue';
import Checkbox from 'primevue/checkbox';
import Accordion from 'primevue/accordion';
import AccordionTab from 'primevue/accordiontab';
import Card from 'primevue/card';
import Button from 'primevue/button'; // Importamos el botón
import Dialog from 'primevue/dialog';

const props = defineProps({
    adicionales: Array, // Aquí llegarán los 13 cursos del controlador
    data_persona: Object,
    section: String
});

const extras_seleccionados = ref([]);


const estaVacio = computed(() => {
    return (props.adicionales || []).length === 0;
});
const formManualErrors = ref({ total: null });
const mostrarModal = ref(false);
const contenidoAbstract = ref('');

const mostrarItinerario = ref(false);
const itinerarioSeleccionado = ref([]);
const tituloViaje = ref('');


const abrirAbstract = (texto) => {
    contenidoAbstract.value = texto;
    mostrarModal.value = true;
};

const verItinerario = (item) => {
    // Aquí asumimos que guardaste el JSON que armamos en el campo 'itinerario'
    itinerarioSeleccionado.value = item.itinerario || [];
    tituloViaje.value = item.nombre_en;
    mostrarItinerario.value = true;
};

const validarSeleccion = () => {
    // Si es sección viajes y no hay nada seleccionado
    if (props.section === 'viajes' && extras_seleccionados.value.length === 0) {
        formManualErrors.value.total = "Please select at least one course or technical visit to proceed with your registration.";

        // Scroll automático al error para que no haya pierde
        const el = document.getElementById('error-container-extras');
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });

        return false; // Bloquea el paso
    }

    formManualErrors.value.total = null;
    return true; // Deja pasar
};


watch(extras_seleccionados, (newVal) => {
    if (newVal.length > 0) {
        formManualErrors.value.total = null;
    }
}, { deep: true });

const total_extras = computed(() => {
    return (props.adicionales || [])
        .filter(item => extras_seleccionados.value.includes(item.id))
        .reduce((sum, item) => {
            // Accedemos a precio_disponible.valor que creamos en el controlador
            const precio = item.precio_disponible ? Number(item.precio_disponible.valor) : 0;
            return sum + precio;
        }, 0);
});

const toggleSelection = (id) => {
    if (extras_seleccionados.value.includes(id)) {
        extras_seleccionados.value = extras_seleccionados.value.filter(i => i !== id);
    } else {
        extras_seleccionados.value.push(id);
    }
};

const selectedObjects = computed(() => {
    return (props.adicionales || []).filter(item => extras_seleccionados.value.includes(item.id));
});

watch(() => props.section, (newVal) => {
    console.log("¡Sección recibida en el hijo!", newVal);
}, { immediate: true });

defineExpose({
    extras_seleccionados,
    total_extras,
    selectedObjects,
    validarSeleccion
});

</script>

<template>
    <div class="text-green-iimp font-bold p-4">
        <Card class="mt-5 overflow-hidden shadow-lg border border-gray-200">
            <template #header>
                <div
                    class="w-full py-3 text-xl font-bold text-center bg-lightblue-wmc border-blue-wmc text-blue-900 uppercase tracking-tight">
                    Courses & Technical Visits
                </div>
            </template>

            <template #content>
                <div id="error-container-extras">
                    <div v-if="formManualErrors.total"
                        class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 flex items-center gap-4 animate-fade-in-down shadow-sm">
                        <div class="bg-red-500 rounded-full p-2 flex-none">
                            <i class="pi pi-exclamation-triangle text-white text-lg"></i>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-red-800 font-black text-sm uppercase">Selection Required</span>
                            <p class="text-red-700 text-sm font-medium leading-tight">
                                {{ formManualErrors.total }}
                            </p>
                        </div>
                    </div>
                </div>
                <div v-if="estaVacio"
                    class="mb-6 p-6 rounded-3xl bg-blue-50 border border-blue-200 flex items-center gap-5 animate-fade-in-down shadow-sm">
                    <div class="bg-blue-600 rounded-full p-3 flex-none shadow-md">
                        <i class="pi pi-info-circle text-white text-xl"></i>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-blue-900 font-black text-sm uppercase tracking-wider">
                            Information for your profile
                        </span>
                        <p class="text-blue-800 text-sm font-medium leading-relaxed mt-1">
                            Dear students or day participants: this option for courses and technical visits is not
                            enabled for your
                            profile.
                            <br><strong>You may continue to the next step without any issues.</strong> Thank you!
                        </p>
                    </div>
                </div>

                <div v-if="section === 'inscripciones'"
                    class="mb-6 p-6 rounded-3xl bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 flex items-center gap-5 animate-fade-in-down shadow-sm">

                    <div class="bg-blue-600 rounded-full p-3 flex-none shadow-md">
                        <i class="pi pi-sparkles text-white text-xl"></i>
                    </div>

                    <div class="flex flex-col">
                        <span class="text-blue-900 font-black text-sm uppercase tracking-wider">
                            Personalize your participation
                        </span>
                        <p class="text-blue-800 text-sm font-medium leading-relaxed mt-1">
                            This section is <strong>optional</strong>. You have the opportunity to add
                            <span class="font-bold text-blue-900">Short Courses</span> or
                            <span class="font-bold text-blue-900">Technical Visits</span> to your experience.

                            <span class="block mt-2 text-xs italic opacity-90">
                                Please note that these specialized activities have an <strong>additional cost</strong>
                                independent of your main registration fee.
                            </span>

                            <br>
                            If you prefer not to add extras at this moment, feel free to click
                            <strong>"Continue"</strong> to proceed with your registration.
                        </p>
                    </div>
                </div>>

                <div v-if="!estaVacio" class="px-2">
                    <Accordion :multiple="true" :activeIndex="[0, 1]" class="wmc-accordion">

                        <!-- ========= CURSO CORTOS =========
                        ================================ -->
                        <AccordionTab>
                            <template #header>
                                <span class="font-bold text-blue-900 uppercase text-sm italic">Short Courses</span>
                            </template>

                            <div class="space-y-4 py-2">
                                <div v-for="item in adicionales.filter(i => i.tipo === 'curso')" :key="item.id"
                                    class="w-full border rounded-lg transition-all duration-200 shadow-sm"
                                    :class="extras_seleccionados.includes(item.id) ? 'bg-blue-50 border-blue-300' : 'border-gray-100 bg-white'">

                                    <!-- <div class="flex items-center justify-between p-3 gap-3">
                                        <div class="flex items-center flex-1 cursor-pointer"
                                            @click="toggleSelection(item.id)">
                                            <Checkbox v-model="extras_seleccionados" :value="item.id" @click.stop
                                                class="mr-3" />
                                            <div class="flex flex-col">
                                                <label
                                                    class="text-sm font-black text-blue-900 leading-tight cursor-pointer">
                                                    {{ item.nombre_en }}
                                                </label>
                                                <span
                                                    class="text-[10px] text-slate-500 font-medium italic uppercase tracking-tighter leading-none mt-1">
                                                    {{ item.subtitulo_en || 'WMC 2026 Special Course' }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-4 border-l pl-4 border-gray-100">
                                            <a :href="item.pdf_url" target="_blank" class="no-underline">
                                                <Button icon="pi pi-file-pdf" label="Details"
                                                    class="p-button-text p-button-sm text-blue-500 font-bold uppercase text-[10px]" />
                                            </a>
                                            <Button icon="pi pi-eye" label="Abstract"
                                                class="p-button-text p-button-sm text-blue-500 font-bold uppercase text-[10px]"
                                                @click="abrirAbstract(item.abstract)" />
                                            <div class="text-right">
                                                <p class="text-yellow-price font-black text-lg">
                                                    USD {{ item.precio_disponible?.valor || 0 }}
                                                </p>
                                            </div>
                                        </div>
                                    </div> -->

                                    <div
                                        class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-3 gap-3">

                                        <div class="flex items-center flex-1 cursor-pointer w-full"
                                            @click="toggleSelection(item.id)">
                                            <Checkbox v-model="extras_seleccionados" :value="item.id" @click.stop
                                                class="mr-3 flex-none" />
                                            <div class="flex flex-col min-w-0">
                                                <label
                                                    class="text-sm font-black text-blue-900 leading-tight cursor-pointer break-words">
                                                    {{ item.nombre_en }}
                                                </label>
                                                <span
                                                    class="text-[10px] text-slate-500 font-medium italic uppercase tracking-tighter leading-none mt-1">
                                                    {{ item.subtitulo_en || (item.tipo === 'curso' ? 'WMC 2026 Special Course' : 'Technical Trip')
                                                    }}
                                                </span>
                                            </div>
                                        </div>

                                        <div
                                            class="flex items-center justify-between sm:justify-end gap-2 w-full sm:w-auto border-t sm:border-t-0 sm:border-l pt-2 sm:pt-0 sm:pl-4 border-gray-100">
                                            <div class="flex items-center gap-1">
                                                <Button v-if="item.tipo === 'curso'" icon="pi pi-eye" label="Abstract"
                                                    class="p-button-text p-button-sm text-blue-500 font-bold uppercase text-[10px]"
                                                    @click.stop="abrirAbstract(item.abstract)" />

                                                <Button v-if="item.tipo === 'viaje' && item.itinerario" icon="pi pi-map"
                                                    label="Itinerary"
                                                    class="p-button-text p-button-sm text-green-600 font-bold uppercase text-[10px]"
                                                    @click.stop="verItinerario(item)" />
                                            </div>

                                            <div class="text-right min-w-[80px]">
                                                <p
                                                    class="text-yellow-price font-black text-base sm:text-lg whitespace-nowrap">
                                                    USD {{ item.precio_disponible?.valor || 0 }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <Accordion class="details-accordion border-t border-blue-50">
                                        <AccordionTab>
                                            <template #header>
                                                <div class="flex items-center gap-2 pl-8">
                                                    <i class="pi pi-info-circle text-blue-400 text-xs"></i>
                                                    <span
                                                        class="text-[10px] font-black text-blue-400 uppercase tracking-widest italic underline">
                                                        Technical Sheet
                                                    </span>
                                                </div>
                                            </template>
                                            <div
                                                class="p-4 bg-slate-50 grid grid-cols-1 md:grid-cols-2 gap-4 text-xs font-normal text-gray-600 ml-8 border-l-2 border-blue-200 rounded-br-lg">
                                                <div class="space-y-3">
                                                    <div class="flex flex-col gap-2">
                                                        <p class="mb-0">
                                                            <strong>
                                                                <i class="pi pi-users mr-1 text-blue-400"></i>
                                                                Speakers:
                                                            </strong>
                                                        </p>

                                                        <ul v-if="Array.isArray(item.expositores)"
                                                            class="list-none p-0 m-0 ml-7 space-y-2">
                                                            <li v-for="(expo, index) in item.expositores" :key="index"
                                                                class="text-xs leading-normal text-gray-700 flex items-start">
                                                                <span class="mr-2 text-blue-300">•</span>
                                                                <span>{{ expo }}</span>
                                                            </li>
                                                        </ul>

                                                        <p v-else class="text-xs ml-7 text-gray-600">
                                                            {{ item.expositores || 'Experts in the field' }}
                                                        </p>
                                                    </div>

                                                    <p>
                                                        <strong>
                                                            <i class="pi pi-language mr-1 text-blue-400"></i>
                                                            Language:
                                                        </strong>
                                                        {{ item.idioma_texto || 'English / Spanish' }}
                                                    </p>
                                                </div>
                                                <div class="space-y-1.5">
                                                    <p><strong>
                                                            <i class="pi pi-calendar mr-1 text-blue-400"></i>
                                                            Date:
                                                        </strong> {{ item.fecha_texto || 'To be defined' }}</p>
                                                    <p><strong>
                                                            <i class="pi pi-clock mr-1 text-blue-400"></i>
                                                            Schedule:
                                                        </strong> {{ item.horario_texto || 'Full Day' }}</p>
                                                    <p><strong>
                                                            <i class="pi pi-map-marker mr-1 text-blue-400"></i>
                                                            Location:
                                                        </strong> {{ item.lugar_texto || 'Convention Center'
                                                        }}
                                                    </p>
                                                </div>
                                            </div>
                                        </AccordionTab>
                                    </Accordion>
                                </div>
                            </div>
                        </AccordionTab>
                        <!-- ========= VIAJES =========
                        ================================ -->
                        | <AccordionTab>
                            <template #header>
                                <span class="font-bold text-blue-900 uppercase text-sm italic">Technical Visits</span>
                            </template>
                            <div class="space-y-4 py-2">
                                <div v-for="item in adicionales.filter(i => i.tipo === 'viaje')" :key="item.id"
                                    class="w-full border rounded-lg transition-all shadow-sm"
                                    :class="extras_seleccionados.includes(item.id) ? 'bg-blue-50 border-blue-300' : 'border-gray-100 bg-white'">

                                    <!-- <div class="flex items-center justify-between p-3 gap-3">
                                        <div class="flex items-center flex-1 cursor-pointer"
                                            @click="toggleSelection(item.id)">
                                            <Checkbox v-model="extras_seleccionados" :value="item.id" @click.stop
                                                class="mr-3" />
                                            <div class="flex flex-col">
                                                <label
                                                    class="text-sm font-black text-blue-900 leading-tight cursor-pointer">
                                                    {{ item.nombre_en }}
                                                </label>
                                                <span
                                                    class="text-[10px] text-slate-500 font-medium italic uppercase tracking-tighter leading-none mt-1">
                                                    {{ item.subtitulo_en || 'Technical Trip' }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-4 border-l pl-4 border-gray-100">
                                            <Button v-if="item.itinerario" icon="pi pi-map" label="View Itinerary"
                                                class="p-button-text p-button-sm text-green-600 font-bold uppercase text-[10px]"
                                                @click="verItinerario(item)" />

                                            <div class="text-right">
                                                <p class="text-yellow-price font-black text-lg">
                                                    USD {{ item.precio_disponible?.valor || 0 }}
                                                </p>
                                            </div>
                                        </div>
                                    </div> -->

                                    <div
                                        class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-3 gap-3">
                                        <div class="flex items-start flex-1 cursor-pointer w-full min-w-0"
                                            @click="toggleSelection(item.id)">
                                            <Checkbox v-model="extras_seleccionados" :value="item.id" @click.stop
                                                class="mr-3 mt-1 flex-none" />
                                            <div class="flex flex-col min-w-0">
                                                <label
                                                    class="text-xs sm:text-sm font-black text-blue-900 leading-tight cursor-pointer break-words">
                                                    {{ item.nombre_en }}
                                                </label>
                                                <span
                                                    class="text-[9px] sm:text-[10px] text-slate-500 font-medium italic uppercase tracking-tighter leading-none mt-1">
                                                    {{ item.subtitulo_en || 'Technical Trip' }}
                                                </span>
                                            </div>
                                        </div>

                                        <div
                                            class="flex items-center justify-between sm:justify-end gap-3 w-full sm:w-auto border-t sm:border-t-0 sm:border-l pt-2 sm:pt-0 sm:pl-4 border-gray-100">
                                            <Button v-if="item.itinerario" icon="pi pi-map" label="View Itinerary"
                                                class="p-button-text p-button-sm text-green-600 font-bold uppercase text-[10px] p-0"
                                                @click.stop="verItinerario(item)" />

                                            <div class="text-right flex-none">
                                                <p
                                                    class="text-yellow-price font-black text-sm sm:text-lg whitespace-nowrap">
                                                    USD {{ item.precio_disponible?.valor || 0 }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <Accordion class="details-accordion border-t border-blue-50">
                                        <AccordionTab>
                                            <template #header>
                                                <div class="flex items-center gap-2 pl-8">
                                                    <i class="pi pi-info-circle text-blue-400 text-xs"></i>
                                                    <span
                                                        class="text-[10px] font-black text-blue-400 uppercase tracking-widest italic underline">Trip
                                                        Logistics</span>
                                                </div>
                                            </template>
                                            <div
                                                class="p-4 bg-slate-50 grid grid-cols-1 md:grid-cols-2 gap-4 text-xs font-normal text-gray-600 ml-8 border-l-2 border-blue-200 rounded-br-lg">
                                                <div class="space-y-1.5">
                                                    <p>
                                                        <strong><i class="pi pi-calendar mr-1 text-blue-400"></i>
                                                            Date:</strong>
                                                        {{ item.fecha_texto || 'No definido' }}

                                                    </p>
                                                    <p>
                                                        <strong><i class="pi pi-check-circle mr-1 text-blue-400"></i>
                                                            Includes:</strong>
                                                        Registration, ground transfers, entrance to Machu
                                                        Picchu, meals, and
                                                        accommodation.
                                                    </p>
                                                </div>

                                                <div class="space-y-1.5">
                                                    <p>
                                                        <strong><i
                                                                class="pi pi-exclamation-triangle mr-1 text-blue-400"></i>
                                                            Requirements:</strong>
                                                        All participants must have SCTR (Health & Pension)
                                                        and Medical Exam Annex
                                                        16.
                                                    </p>
                                                    <p>
                                                        <strong><i class="pi pi-info-circle mr-1 text-blue-400"></i>
                                                            Note:</strong>
                                                        The transfer to Cusco at the start and return to
                                                        Lima at the end is assumed
                                                        by the participant.
                                                    </p>
                                                </div>
                                            </div>
                                        </AccordionTab>
                                    </Accordion>
                                </div>
                            </div>
                        </AccordionTab>
                    </Accordion>

                    <div v-if="extras_seleccionados.length > 0"
                        class="mt-8 p-5 bg-lightblue-wmc border border-blue-wmc rounded-xl flex justify-between items-center shadow-md animate-fade-in">
                        <div class="flex flex-col">
                            <span class="text-[10px] uppercase text-blue-500 font-black tracking-widest">
                                Additional Selection ({{ extras_seleccionados.length }})
                            </span>
                            <span class="text-xl font-bold uppercase tracking-tighter text-blue-900">
                                Subtotal Extras
                            </span>
                        </div>
                        <div class="text-right">
                            <span class="text-yellow-price font-black text-3xl tracking-tight">
                                USD {{ total_extras }}
                            </span>
                        </div>
                    </div>
                </div>
            </template>
        </Card>
    </div>
    <Dialog v-model:visible="mostrarModal" header="Course Abstract" :style="{ width: '50vw' }"
        :breakpoints="{ '960px': '75vw', '641px': '90vw' }" :modal="true" dismissableMask class="abstract-dialog">
        <div class="p-4 border-l-4 border-blue-500 bg-blue-50 rounded-r-lg">
            <p class="text-gray-700 leading-relaxed text-sm italic">
                {{ contenidoAbstract }}
            </p>
        </div>

        <template #footer>
            <Button label="CLOSE" icon="pi pi-times" @click="mostrarModal = false"
                class="p-button-text p-button-secondary font-bold" />
        </template>
    </Dialog>
    <Dialog v-model:visible="mostrarItinerario" :header="tituloViaje" :style="{ width: '600px' }"
        :breakpoints="{ '960px': '75vw', '641px': '90vw' }" :modal="true" dismissableMask class="itinerary-dialog">
        <div class="p-4 space-y-6">
            <div v-for="(step, index) in itinerarioSeleccionado" :key="index"
                class="relative pl-8 border-l-2 border-blue-100 pb-4">
                <div
                    class="absolute -left-[11px] top-0 w-5 h-5 rounded-full bg-white border-4 border-blue-500 shadow-sm">
                </div>

                <span class="text-[10px] font-black text-blue-500 uppercase tracking-widest">{{ step.day }}</span>
                <h4 class="text-sm font-bold text-slate-800 mt-1 mb-2">{{ step.title }}</h4>

                <ul class="space-y-2">
                    <li v-for="act in step.activities" :key="act" class="text-xs text-slate-600 flex items-start gap-2">
                        <i class="pi pi-circle-fill text-[6px] mt-1.5 text-slate-300"></i>
                        {{ act }}
                    </li>
                </ul>
            </div>
        </div>

        <template #footer>
            <Button label="Close" icon="pi pi-times" @click="mostrarItinerario = false"
                class="p-button-text p-button-secondary" />
        </template>
    </Dialog>
</template>

<style scoped>
/* Estilos para limpiar el acordeón interno */
/* :deep(.details-accordion .p-accordion-header-link) {
    padding: 0.5rem 1rem !important;
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
}

:deep(.details-accordion .p-accordion-content) {
    padding: 0 !important;
    border: none !important;
    background: transparent !important;
}

:deep(.wmc-accordion .p-accordion-tab) {
    margin-bottom: 1rem;
} */

/* Efecto hover suave en tarjetas */
.border-gray-100:hover {
    border-color: #cbd5e1;
    background-color: #f8fafc;
}

/* Ajuste para que el texto largo no empuje el precio fuera de la pantalla */
.min-w-0 {
    min-width: 0;
}


@media (max-width: 640px) {

    /* Ajuste de los títulos del acordeón principal */
    :deep(.p-accordion-header-link) {
        padding: 0.75rem !important;
    }

    :deep(.p-accordion-header-text) {
        font-size: 0.75rem !important;
    }

    /* Ajuste de la tarjeta de contenido */
    .p-4 {
        padding: 0.75rem !important;
    }

    /* Texto de los labels */
    label {
        font-size: 0.8rem !important;
        line-height: 1.1 !important;
    }

    /* El precio en móvil */
    .text-yellow-price {
        font-size: 0.95rem !important;
    }
}

/* Evitar que el acordeón de detalles (Technical Sheet) sea muy grande */
:deep(.details-accordion .p-accordion-header-link span) {
    font-size: 9px !important;
}

:deep(.details-accordion .p-accordion-content div) {
    padding: 0.75rem !important;
    gap: 0.5rem !important;
}

/* Contenedor de subtotal inferior bilingue */
.text-xl {
    @media (max-width: 640px) {
        font-size: 1rem !important;
    }
}

/* Limpieza de bordes en el sub-acordeón de logística */
:deep(.details-accordion .p-accordion-content) {
    padding: 1rem !important;
    font-size: 0.75rem;
}
</style>
