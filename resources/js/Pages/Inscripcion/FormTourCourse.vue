<script setup>
import { ref, computed, defineExpose, watch, onMounted } from 'vue';
import Checkbox from 'primevue/checkbox';
import Accordion from 'primevue/accordion';
import AccordionTab from 'primevue/accordiontab';
import Card from 'primevue/card';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import { usePage } from '@inertiajs/vue3'; // Importado para las traducciones

const page = usePage();
const words = page.props.language.words; // Extraemos el diccionario

const props = defineProps({
    adicionales: Array,
    data_persona: Object,
    section: String,
    course: Array
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
    itinerarioSeleccionado.value = item.itinerario || [];
    tituloViaje.value = item.nombre_en;
    mostrarItinerario.value = true;
};

const validarSeleccion = () => {
    if (props.section === 'viajes' && extras_seleccionados.value.length === 0) {
        // Usamos la variable traducida
        formManualErrors.value.total = words.err_select_course_visit;

        const el = document.getElementById('error-container-extras');
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });

        return false;
    }

    formManualErrors.value.total = null;
    return true;
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
            const precio = item.precio_disponible ? Number(item.precio_disponible.valor) : 0;
            return sum + precio;
        }, 0);
});

const toggleSelection = (id) => {
    if (extras_seleccionados.value.includes(id)) {
        extras_seleccionados.value = extras_seleccionados.value.filter(i => i !== id);
    } else {
        const nuevoItem = props.adicionales.find(i => i.id === id);

        const conflicto = (props.adicionales || []).find(item =>
            extras_seleccionados.value.includes(item.id) &&
            item.fecha_texto === nuevoItem.fecha_texto &&
            item.horario_texto === nuevoItem.horario_texto
        );

        if (conflicto) {
            // Usamos la variable traducida
            alert(`${words.msg_schedule_conflict} "${conflicto.nombre_en}".`);
            return;
        }

        extras_seleccionados.value.push(id);
    }
};

const idsBloqueados = computed(() => {
    const bloqueados = [];
    const seleccionados = (props.adicionales || []).filter(item =>
        extras_seleccionados.value.includes(item.id)
    );

    (props.adicionales || []).forEach(item => {
        if (!extras_seleccionados.value.includes(item.id)) {
            const tieneConflicto = seleccionados.some(s =>
                s.fecha_texto === item.fecha_texto &&
                s.horario_texto === item.horario_texto
            );
            if (tieneConflicto) bloqueados.push(item.id);
        }
    });
    return bloqueados;
});

const selectedObjects = computed(() => {
    return (props.adicionales || []).filter(item => extras_seleccionados.value.includes(item.id));
});

const adicionalesFiltrados = computed(() => {
    const lista = props.adicionales || [];
    if (props.course && props.course.length > 0) {
        const idsPermitidos = props.course.map(id => Number(id));
        return lista.filter(item => idsPermitidos.includes(Number(item.id)));
    }
    return lista;
});

const cursosFiltrados = computed(() => {
    return adicionalesFiltrados.value.filter(i => i.tipo === 'curso');
});

const viajesFiltrados = computed(() => {
    return adicionalesFiltrados.value.filter(i => i.tipo === 'viaje');
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

onMounted(() => {
    if (props.course && props.course.length > 0) {
        const idsASeleccionar = props.course
            .map(id => Number(id))
            .filter(id => props.adicionales.some(item => item.id === id));

        if (idsASeleccionar.length > 0) {
            extras_seleccionados.value = idsASeleccionar;
        }
    }
});

const esVisitaTecnicaForzada = computed(() => {
    const idsCursos = Array.isArray(props.course)
        ? props.course.map(Number)
        : [Number(props.course)];

    return idsCursos.includes(81) || idsCursos.includes(82) || idsCursos.includes(84);
});

</script>

<template>
    <div class="text-green-iimp font-bold p-4">
        <Card class="mt-5 overflow-hidden shadow-lg border border-gray-200">
            <template #header>
                <div
                    class="w-full py-3 text-xl font-bold text-center bg-lightblue-wmc border-blue-wmc text-blue-900 uppercase tracking-tight">
                    {{ words.courses_visits }}
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
                            <span class="text-red-800 font-black text-sm uppercase">{{ words.lbl_selection_required
                                }}</span>
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
                            {{ words.lbl_profile_info }}
                        </span>
                        <p class="text-blue-800 text-sm font-medium leading-relaxed mt-1"
                            v-html="words.msg_students_no_courses"></p>
                    </div>
                </div>

                <div v-if="section === 'inscripciones'"
                    class="mb-6 p-6 rounded-3xl bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 flex items-center gap-5 animate-fade-in-down shadow-sm">
                    <div class="bg-blue-600 rounded-full p-3 flex-none shadow-md">
                        <i class="pi pi-sparkles text-white text-xl"></i>
                    </div>

                    <div class="flex flex-col">
                        <span class="text-blue-900 font-black text-sm uppercase tracking-wider">
                            {{ words.lbl_enhance_experience }}
                        </span>
                        <div class="text-blue-800 text-sm font-medium leading-relaxed">
                            <p v-html="words.msg_enhance_desc_1"></p>

                            <span
                                class="block mt-4 p-3 bg-blue-600/20 rounded-xl border border-blue-500/30 text-xs italic text-blue-800">
                                <strong>{{ words.lbl_note }}:</strong> <span v-html="words.msg_enhance_desc_2"></span>
                            </span>

                            <span class="block mt-4 text-blue-800 font-bold" v-html="words.msg_enhance_desc_3"></span>
                        </div>
                    </div>
                </div>

                <div v-if="!estaVacio" class="px-2">
                    <Accordion :multiple="true" :activeIndex="[0, 1]" class="wmc-accordion">

                        <AccordionTab v-if="!esVisitaTecnicaForzada">
                            <template #header>
                                <span class="font-bold text-blue-900 uppercase text-sm italic">{{ words.btn_courses
                                    }}</span>
                            </template>

                            <div class="space-y-4 py-2">
                                <div v-for="item in adicionalesFiltrados.filter(i => i.tipo === 'curso')" :key="item.id"
                                    :class="[
                                        extras_seleccionados.includes(item.id) ? 'bg-blue-50 border-blue-300' : 'border-gray-100 bg-white',
                                        idsBloqueados.includes(item.id) ? 'opacity-50 grayscale pointer-events-none' : ''
                                    ]">

                                    <div
                                        class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-3 gap-3">
                                        <div class="flex items-center flex-1 cursor-pointer w-full"
                                            @click="toggleSelection(item.id)">
                                            <Checkbox v-model="extras_seleccionados" :value="item.id"
                                                :disabled="idsBloqueados.includes(item.id)" @click.stop
                                                class="mr-3 flex-none" />
                                            <div class="flex flex-col min-w-0">
                                                <label
                                                    class="text-sm font-black text-blue-900 leading-tight cursor-pointer break-words">
                                                    {{ item.nombre_es }}
                                                </label>
                                                <span
                                                    class="text-[10px] text-slate-500 font-medium italic uppercase tracking-tighter leading-none mt-1">
                                                    {{ item.subtitulo_en || (item.tipo === 'curso' ?
                                                    words.lbl_course_proexplo : words.btn_visits)
                                                    }}
                                                </span>
                                            </div>
                                        </div>

                                        <div
                                            class="flex items-center justify-between sm:justify-end gap-2 w-full sm:w-auto border-t sm:border-t-0 sm:border-l pt-2 sm:pt-0 sm:pl-4 border-gray-100">
                                            <div class="flex items-center gap-1">
                                                <span v-if="item.evento"
                                                    class="px-2 py-0.5 rounded-md bg-blue-100 text-blue-700 text-[9px] font-black uppercase tracking-wider border border-blue-200">
                                                    {{ item.evento }}
                                                </span>
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
                                                        {{ words.lbl_technical_sheet }}
                                                    </span>
                                                </div>
                                            </template>
                                            <div
                                                class="p-4 bg-slate-50 grid grid-cols-1 md:grid-cols-2 gap-4 text-xs font-normal text-gray-600 ml-8 border-l-2 border-blue-200 rounded-br-lg">
                                                <div class="space-y-3">
                                                    <div class="flex flex-col gap-2">
                                                        <p class="mb-0">
                                                            <strong><i class="pi pi-users mr-1 text-blue-400"></i>{{
                                                                words.lbl_instructors
                                                                }}:</strong>
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
                                                            {{ item.expositores || words.lbl_experts_field }}
                                                        </p>
                                                    </div>
                                                    <p>
                                                        <strong><i class="pi pi-language mr-1 text-blue-400"></i>{{
                                                            words.lbl_language }}:</strong>
                                                        {{ item.idioma_texto || words.lbl_eng_spa }}
                                                    </p>
                                                </div>
                                                <div class="space-y-1.5">
                                                    <p><strong><i class="pi pi-calendar mr-1 text-blue-400"></i>{{
                                                            words.lbl_date }}:</strong> {{
                                                        item.fecha_texto || words.lbl_to_be_defined }}</p>
                                                    <p><strong><i class="pi pi-clock mr-1 text-blue-400"></i>{{
                                                            words.lbl_schedule }}:</strong> {{
                                                        item.horario_texto || words.lbl_full_day }}</p>
                                                    <p><strong><i class="pi pi-map-marker mr-1 text-blue-400"></i>{{
                                                            words.lbl_location }}:</strong>
                                                        {{ item.lugar_texto || words.lbl_convention_center }}</p>
                                                </div>
                                            </div>
                                        </AccordionTab>
                                    </Accordion>
                                </div>
                            </div>
                        </AccordionTab>

                        <AccordionTab v-if="props.course == 0 || esVisitaTecnicaForzada">
                            <template #header>
                                <span class="font-bold text-blue-900 uppercase text-sm italic">{{ words.btn_visits
                                    }}</span>
                            </template>
                            <div class="space-y-4 py-2">
                                <div v-for="item in viajesFiltrados.filter(i => i.tipo === 'viaje')" :key="item.id"
                                    class="w-full border rounded-lg transition-all shadow-sm"
                                    :class="extras_seleccionados.includes(item.id) ? 'bg-blue-50 border-blue-300' : 'border-gray-100 bg-white'">

                                    <div
                                        class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-3 gap-3">
                                        <div class="flex items-start flex-1 cursor-pointer w-full min-w-0"
                                            @click="toggleSelection(item.id)">
                                            <Checkbox v-model="extras_seleccionados" :value="item.id" @click.stop
                                                class="mr-3 mt-1 flex-none" />
                                            <div class="flex flex-col min-w-0">
                                                <!-- <label class="text-xs sm:text-sm font-black text-blue-900 leading-tight cursor-pointer break-words">
                                                    {{ item.nombre_en }}
                                                </label> -->
                                                <label
                                                    class="text-xs sm:text-sm font-black text-blue-900 leading-tight cursor-pointer break-words">
                                                    {{ $page.props.language.current === 'en' ? item.nombre_en :
                                                    item.nombre_es }}
                                                </label>
                                                <span
                                                    class="text-[9px] sm:text-[10px] text-slate-500 font-medium italic uppercase tracking-tighter leading-none mt-1">
                                                    {{ item.subtitulo_en || words.lbl_technical_trip }}
                                                </span>
                                            </div>
                                        </div>

                                        <div
                                            class="flex items-center justify-between sm:justify-end gap-3 w-full sm:w-auto border-t sm:border-t-0 sm:border-l pt-2 sm:pt-0 sm:pl-4 border-gray-100">
                                            <Button icon="pi pi-eye" :label="words.lbl_brochure" as="a"
                                                :href="item.brochure_url" @click.stop target="_blank"
                                                class="p-button-text p-button-sm text-blue-500 font-bold uppercase text-[10px]" />

                                            <Button v-if="item.itinerario" icon="pi pi-map"
                                                :label="words.lbl_view_itinerary"
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
                                                        class="text-[10px] font-black text-blue-400 uppercase tracking-widest italic underline">
                                                        {{ words.lbl_trip_logistics }}
                                                    </span>
                                                </div>
                                            </template>
                                            <div
                                                class="p-4 bg-slate-50 grid grid-cols-1 md:grid-cols-2 gap-4 text-xs font-normal text-gray-600 ml-8 border-l-2 border-blue-200 rounded-br-lg">
                                                <div class="space-y-1.5">
                                                    <p>
                                                        <strong><i class="pi pi-calendar mr-1 text-blue-400"></i>{{
                                                            words.lbl_date }}:</strong>
                                                        {{ item.fecha_texto || words.lbl_to_be_defined }}
                                                    </p>
                                                    <p>
                                                        <strong><i class="pi pi-check-circle mr-1 text-blue-400"></i>{{
                                                            words.lbl_trip_leaders
                                                            }}:</strong>
                                                    <ul v-if="Array.isArray(item.expositores)"
                                                        class="list-none p-0 m-0 ml-7 space-y-2">
                                                        <li v-for="(expo, index) in item.expositores" :key="index"
                                                            class="text-xs leading-normal text-gray-700 flex items-start">
                                                            <span class="mr-2 text-blue-300">•</span>
                                                            <span>{{ expo }}</span>
                                                        </li>
                                                    </ul>
                                                    <p v-else class="text-xs ml-7 text-gray-600">
                                                        {{ item.expositores || words.lbl_experts_field }}
                                                    </p>
                                                    </p>
                                                </div>
                                            </div>
                                        </AccordionTab>
                                    </Accordion>
                                </div>
                            </div>
                        </AccordionTab>
                    </Accordion>

                    <div v-if="extras_seleccionados.length > 0 && props.course == 0"
                        class="mt-8 p-5 bg-lightblue-wmc border border-blue-wmc rounded-xl flex justify-between items-center shadow-md animate-fade-in">
                        <div class="flex flex-col">
                            <span class="text-[10px] uppercase text-blue-500 font-black tracking-widest">
                                {{ words.lbl_additional_selection }} ({{ extras_seleccionados.length }})
                            </span>
                            <span class="text-xl font-bold uppercase tracking-tighter text-blue-900">
                                {{ words.lbl_subtotal_extras }}
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

    <Dialog v-model:visible="mostrarModal" :header="words.lbl_course_abstract" :style="{ width: '50vw' }"
        :breakpoints="{ '960px': '75vw', '641px': '90vw' }" :modal="true" dismissableMask class="abstract-dialog">
        <div class="p-4 border-l-4 border-blue-500 bg-blue-50 rounded-r-lg">
            <p class="text-gray-700 leading-relaxed text-sm italic">
                {{ contenidoAbstract }}
            </p>
        </div>
        <template #footer>
            <Button :label="words.btn_close" icon="pi pi-times" @click="mostrarModal = false"
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
            <Button :label="words.btn_close" icon="pi pi-times" @click="mostrarItinerario = false"
                class="p-button-text p-button-secondary" />
        </template>
    </Dialog>
</template>

<style scoped>
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
