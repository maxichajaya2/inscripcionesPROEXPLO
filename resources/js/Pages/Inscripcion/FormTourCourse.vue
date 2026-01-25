<script setup>
import { ref, computed, h } from 'vue';
import Checkbox from 'primevue/checkbox';
import Accordion from 'primevue/accordion';
import AccordionTab from 'primevue/accordiontab';
import Card from 'primevue/card';
import Button from 'primevue/button'; // Importamos el botón

const items_adicionales = ref([
    {
        id: 101,
        tipo: 'curso',
        titulo: 'Curso A [Waste Rock Dumps]',
        subtitulo: 'Diseño, Mantenimiento, Operación y Cierre de Depósitos de Desmonte',
        precio: 250,
        pdf_url: 'https://services.slopestability2026.com/api/files/ebfebbb6-83e8-47d8-83aa-965e1ea21a4a.pdf',
        detalles: {
            coordinador: 'Denys Parra (Anddes, Peru)',
            expositores: ['Denys Parra', 'Jesús Negrón', 'Humberto Alvarado', 'Mckevin Canicoba', 'Franco Sánchez', 'Luis Santamaria'],
            fecha: 'Sábado 24 de Octubre, 2026 (*)',
            horario: '08:00 - 12:00 y 14:00 - 18:00 horas',
            idioma: 'Español (Interpretación en inglés)',
            lugar: 'Centro de Convenciones de Lima, sede de Slope Stability 2026'
        }
    },
    {
        id: 102,
        tipo: 'curso',
        titulo: 'Curso B [Geoblast]',
        subtitulo: 'Optimización de Voladura y Estabilidad de Taludes',
        precio: 250,
        pdf_url: 'https://services.slopestability2026.com/api/files/ebfebbb6-83e8-47d8-83aa-965e1ea21a4a.pdf',
        detalles: {
            coordinador: 'Cristian Alvarez (Geoblast, Chile)',
            expositores: ['Carlos Scherpenisse', 'Alex Calderón'],
            fecha: 'Sábado 24 de Octubre, 2026 (*)',
            horario: '08:00 - 12:00 y 14:00 - 18:00 horas',
            idioma: 'Español (Interpretación en inglés)',
            lugar: 'Centro de Convenciones de Lima, sede de Slope Stability 2026'
        }
    },
    {
        id: 201,
        tipo: 'viaje',
        titulo: 'Visita Técnica: Unidad Minera Cerro Verde',
        subtitulo: 'Operaciones de tajo abierto y lixiviación a gran escala',
        precio: 450,
        pdf_url: 'https://services.slopestability2026.com/api/files/ebfebbb6-83e8-47d8-83aa-965e1ea21a4a.pdf',
        detalles: {
            responsable: 'Ing. Carlos Martínez (Sociedad Minera Cerro Verde)',
            fecha: 'Domingo 25 de Octubre, 2026',
            horario: '07:00 - 17:00 horas (Full Day)',
            lugar: 'Punto de partida: Centro de Convenciones de Lima',
            idioma: 'Español / Inglés',
            nota: 'Incluye traslados, almuerzo técnico y EPP básico.'
        }
    },
    {
        id: 202,
        tipo: 'viaje',
        titulo: 'Visita Técnica: Proyecto Quellaveco',
        subtitulo: 'Minería 100% Digital y Centro de Control Integrado',
        precio: 500,
        pdf_url: 'https://services.slopestability2026.com/api/files/ebfebbb6-83e8-47d8-83aa-965e1ea21a4a.pdf',
        detalles: {
            responsable: 'Staff Anglo American',
            fecha: 'Lunes 26 de Octubre, 2026',
            horario: '06:00 - 18:00 horas (Full Day)',
            lugar: 'Salida desde Hoteles autorizados (Miraflores/San Isidro)',
            idioma: 'Inglés con traducción al español',
            nota: 'Requiere validación de examen médico vigente.'
        }
    }
]);

const extras_seleccionados = ref([]);

// Para controlar qué acordeón de detalles está abierto manualmente si fuera necesario
const expandedDetails = ref({});

const total_extras = computed(() => {
    return items_adicionales.value
        .filter(item => extras_seleccionados.value.includes(item.id))
        .reduce((sum, item) => sum + (Number(item.precio) || 0), 0);
});

const toggleSelection = (id) => {
    if (extras_seleccionados.value.includes(id)) {
        extras_seleccionados.value = extras_seleccionados.value.filter(i => i !== id);
    } else {
        extras_seleccionados.value.push(id);
    }
};
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
                <div class="px-2">
                    <Accordion :multiple="true" :activeIndex="[0, 1]" class="wmc-accordion">

                        <AccordionTab>
                            <template #header>
                                <span class="font-bold text-blue-900 uppercase text-sm italic">Short Courses</span>
                            </template>

                            <div class="space-y-4 py-2">
                                <div v-for="item in items_adicionales.filter(i => i.tipo === 'curso')" :key="item.id"
                                    class="w-full border rounded-lg transition-all duration-200 shadow-sm"
                                    :class="extras_seleccionados.includes(item.id) ? 'bg-blue-50 border-blue-300' : 'border-gray-100 bg-white'">

                                    <div class="flex items-center justify-between p-3 gap-3">
                                        <div class="flex items-center flex-1 cursor-pointer"
                                            @click="toggleSelection(item.id)">
                                            <Checkbox v-model="extras_seleccionados" :value="item.id" @click.stop
                                                class="mr-3" />
                                            <div class="flex flex-col">
                                                <label
                                                    class="text-sm font-black text-blue-900 leading-tight cursor-pointer">{{
                                                        item.titulo
                                                    }}</label>
                                                <span
                                                    class="text-[10px] text-slate-500 font-medium italic uppercase tracking-tighter leading-none mt-1">{{
                                                        item.subtitulo }}</span>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-4 border-l pl-4 border-gray-100">
                                            <a :href="item.pdf_url" target="_blank" class="no-underline">
                                                <Button icon="pi pi-file-pdf" label="Details"
                                                    class="p-button-text p-button-sm text-blue-500 font-bold uppercase text-[10px]" />
                                            </a>
                                            <div class="text-right">
                                                <p class="text-yellow-price font-black text-lg">USD {{ item.precio }}
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
                                                        class="text-[10px] font-black text-blue-400 uppercase tracking-widest italic underline">Technical
                                                        Sheet / Ver Ficha Técnica</span>
                                                </div>
                                            </template>
                                            <div
                                                class="p-4 bg-slate-50 grid grid-cols-1 md:grid-cols-2 gap-4 text-xs font-normal text-gray-600 ml-8 border-l-2 border-blue-200 rounded-br-lg">
                                                <div class="space-y-1.5">
                                                    <p><strong><i class="pi pi-user mr-1 text-blue-400"></i>
                                                            Coordinador:</strong> {{
                                                                item.detalles.coordinador }}</p>
                                                    <p><strong><i class="pi pi-users mr-1 text-blue-400"></i>
                                                            Expositores:</strong> {{
                                                                item.detalles.expositores.join(', ') }}</p>
                                                    <p><strong><i class="pi pi-language mr-1 text-blue-400"></i>
                                                            Idioma:</strong> {{
                                                                item.detalles.idioma }}</p>
                                                </div>
                                                <div class="space-y-1.5">
                                                    <p><strong><i class="pi pi-calendar mr-1 text-blue-400"></i>
                                                            Fecha:</strong> {{
                                                                item.detalles.fecha }}</p>
                                                    <p><strong><i class="pi pi-clock mr-1 text-blue-400"></i>
                                                            Horario:</strong> {{
                                                                item.detalles.horario }}</p>
                                                    <p><strong><i class="pi pi-map-marker mr-1 text-blue-400"></i>
                                                            Lugar:</strong> {{
                                                                item.detalles.lugar }}</p>
                                                </div>
                                            </div>
                                        </AccordionTab>
                                    </Accordion>
                                </div>
                            </div>
                        </AccordionTab>

                        <AccordionTab>
                            <template #header>
                                <span class="font-bold text-blue-900 uppercase text-sm italic">Technical Visits</span>
                            </template>
                            <div class="space-y-4 py-2">
                                <div v-for="item in items_adicionales.filter(i => i.tipo === 'viaje')" :key="item.id"
                                    class="w-full border rounded-lg transition-all shadow-sm"
                                    :class="extras_seleccionados.includes(item.id) ? 'bg-blue-50 border-blue-300' : 'border-gray-100 bg-white'">

                                    <div class="flex items-center justify-between p-3 gap-3">
                                        <div class="flex items-center flex-1 cursor-pointer"
                                            @click="toggleSelection(item.id)">
                                            <Checkbox v-model="extras_seleccionados" :value="item.id" @click.stop
                                                class="mr-3" />
                                            <div class="flex flex-col">
                                                <label
                                                    class="text-sm font-black text-blue-900 leading-tight cursor-pointer">{{
                                                        item.titulo
                                                    }}</label>
                                                <span
                                                    class="text-[10px] text-slate-500 font-medium italic uppercase tracking-tighter leading-none mt-1">{{
                                                        item.subtitulo }}</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-4 border-l pl-4 border-gray-100">
                                            <a :href="item.pdf_url" target="_blank" class="no-underline">
                                                <Button icon="pi pi-file-pdf" label="Details"
                                                    class="p-button-text p-button-sm text-blue-500 font-bold uppercase text-[10px]" />
                                            </a>
                                            <div class="text-right">
                                                <p class="text-yellow-price font-black text-lg">USD {{ item.precio }}
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
                                                        Logistics / Logística de Viaje</span>
                                                </div>
                                            </template>
                                            <div
                                                class="p-4 bg-slate-50 grid grid-cols-1 md:grid-cols-2 gap-4 text-xs font-normal text-gray-600 ml-8 border-l-2 border-blue-200 rounded-br-lg">
                                                <div class="space-y-1.5">
                                                    <p><strong>Responsable:</strong> {{ item.detalles.responsable }}</p>
                                                    <p><strong>Horario:</strong> {{ item.detalles.horario }}</p>
                                                    <p><strong>Encuentro:</strong> {{ item.detalles.lugar }}</p>
                                                </div>
                                                <div class="space-y-1.5 text-right md:text-left">
                                                    <p><strong>Fecha:</strong> {{ item.detalles.fecha }}</p>
                                                    <p><strong>Idioma:</strong> {{ item.detalles.idioma }}</p>
                                                    <div v-if="item.detalles.nota"
                                                        class="mt-2 text-blue-600 font-bold bg-blue-100 p-2 rounded-md italic shadow-inner">
                                                        <i class="pi pi-ticket mr-1"></i> {{ item.detalles.nota }}
                                                    </div>
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
</template>

<style scoped>
/* Estilos para limpiar el acordeón interno */
:deep(.details-accordion .p-accordion-header-link) {
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
}

/* Efecto hover suave en tarjetas */
.border-gray-100:hover {
    border-color: #cbd5e1;
    background-color: #f8fafc;
}
</style>
