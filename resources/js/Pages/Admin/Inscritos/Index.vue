<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    inscritos: Array
});

// ==========================================
// ESTADOS Y BÚSQUEDA
// ==========================================
const searchQuery = ref('');
const currentPage = ref(1);
const itemsPerPage = 10;

// Filtro en tiempo real (busca por nombre, documento, correo o RUC)
const filteredInscritos = computed(() => {
    const query = searchQuery.value.toLowerCase();
    return props.inscritos.filter(inscrito =>
        inscrito.nombres.toLowerCase().includes(query) ||
        inscrito.documento.toLowerCase().includes(query) ||
        inscrito.email.toLowerCase().includes(query) ||
        inscrito.factura_ruc.toLowerCase().includes(query) ||
        inscrito.factura_razon_social.toLowerCase().includes(query)
    );
});

watch(searchQuery, () => { currentPage.value = 1; });

// ==========================================
// PAGINACIÓN
// ==========================================
const totalPages = computed(() => Math.ceil(filteredInscritos.value.length / itemsPerPage));
const paginatedInscritos = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    return filteredInscritos.value.slice(start, start + itemsPerPage);
});

const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++; };
const prevPage = () => { if (currentPage.value > 1) currentPage.value--; };
</script>

<template>
    <Head title="Inscritos | Proexplo" />

    <AuthenticatedLayout>
        <div class="space-y-6">

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                <div>
                    <h2 class="font-black text-2xl text-slate-800 tracking-tight uppercase">Lista de Inscritos</h2>
                    <p class="text-sm text-slate-500 mt-1 font-medium">Gestiona los participantes registrados en el congreso.</p>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
                    <div class="relative w-full sm:w-80">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <input v-model="searchQuery" type="text" placeholder="Buscar por nombre, DNI, correo o RUC..." class="w-full pl-9 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all outline-none shadow-sm text-sm" />
                    </div>
                    <button class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-slate-900 hover:bg-slate-800 text-white px-5 py-2.5 rounded-xl font-bold text-sm shadow-sm transition-all active:scale-95 whitespace-nowrap border-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Exportar
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50/80">
                            <tr>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-slate-500 uppercase tracking-widest">Inscripción / Fecha</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-slate-500 uppercase tracking-widest">Participante</th>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-slate-500 uppercase tracking-widest">Facturación</th>
                                <th class="px-6 py-4 text-center text-[10px] font-black text-slate-500 uppercase tracking-widest">Cargo / Origen</th>
                                <th class="px-6 py-4 text-center text-[10px] font-black text-slate-500 uppercase tracking-widest">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <tr v-for="inscrito in paginatedInscritos" :key="inscrito.id" class="group hover:bg-orange-50/30 transition-colors">

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="font-black text-sm text-slate-800 tracking-tight">#{{ inscrito.id }}</span>
                                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ inscrito.fecha_registro }}</span>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <p class="font-bold text-xs text-slate-800 uppercase">{{ inscrito.nombres }}</p>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="text-[10px] text-slate-500 font-medium border border-slate-200 px-1.5 rounded bg-slate-50">Doc: {{ inscrito.documento }}</span>
                                            <span class="text-[10px] text-orange-600 font-medium truncate max-w-[150px]" :title="inscrito.email">{{ inscrito.email }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <div v-if="inscrito.tiene_factura" class="flex flex-col">
                                        <p class="font-bold text-xs text-slate-700 uppercase truncate max-w-[200px]" :title="inscrito.factura_razon_social">{{ inscrito.factura_razon_social }}</p>
                                        <p class="text-[10px] text-slate-400 font-medium mt-0.5">RUC: {{ inscrito.factura_ruc }}</p>
                                    </div>
                                    <div v-else>
                                        <span class="inline-flex px-2 py-0.5 rounded-md bg-slate-100 text-slate-500 text-[10px] font-bold uppercase tracking-widest border border-slate-200">
                                            Sin Factura
                                        </span>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <div class="flex flex-col items-center gap-1">
                                        <span class="text-xs font-bold text-slate-600 truncate max-w-[150px]" :title="inscrito.cargo">{{ inscrito.cargo }}</span>
                                        <span class="text-[9px] text-slate-400 font-bold uppercase tracking-widest border-b border-slate-200">{{ inscrito.origen }}</span>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <span v-if="inscrito.estado" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-green-50 text-green-600 text-[10px] font-black uppercase tracking-widest border border-green-200/60 shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Activo
                                    </span>
                                    <span v-else class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-slate-100 text-slate-500 text-[10px] font-black uppercase tracking-widest border border-slate-200 shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Inactivo
                                    </span>
                                </td>
                            </tr>

                            <tr v-if="filteredInscritos.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="inline-flex flex-col items-center justify-center">
                                        <div class="w-16 h-16 bg-orange-50 text-orange-400 rounded-full flex items-center justify-center mb-3">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                        </div>
                                        <span class="text-sm font-bold text-slate-700">No hay inscritos para mostrar.</span>
                                        <span class="text-xs text-slate-400 mt-1">Intenta buscar con otros términos.</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="filteredInscritos.length > 0" class="px-6 py-4 border-t border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <span class="text-xs text-slate-500 font-medium">
                        Mostrando <span class="font-black text-slate-800">{{ (currentPage - 1) * itemsPerPage + 1 }}</span> a
                        <span class="font-black text-slate-800">{{ Math.min(currentPage * itemsPerPage, filteredInscritos.length) }}</span>
                        de <span class="font-black text-slate-800">{{ filteredInscritos.length }}</span> inscritos
                    </span>
                    <div class="flex gap-2">
                        <button @click="prevPage" :disabled="currentPage === 1" class="px-4 py-2 text-xs font-bold rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:text-orange-600 disabled:opacity-50 disabled:hover:text-slate-600 transition-all shadow-sm">Anterior</button>
                        <button @click="nextPage" :disabled="currentPage === totalPages" class="px-4 py-2 text-xs font-bold rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:text-orange-600 disabled:opacity-50 disabled:hover:text-slate-600 transition-all shadow-sm">Siguiente</button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
