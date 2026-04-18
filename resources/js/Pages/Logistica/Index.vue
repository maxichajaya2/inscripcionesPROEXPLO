<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    proveedores: {
        type: Array,
        default: () => []
    }
});

// ==========================================
// MENSAJES FLASH (NOTIFICACIONES DINÁMICAS)
// ==========================================
const showFlash = ref(false);
const toastMessage = ref('');
const toastType = ref('success'); // 'success' o 'error'
let flashTimer = null;

const displayToast = (message, type = 'success') => {
    toastMessage.value = message;
    toastType.value = type;
    showFlash.value = true;
    if (flashTimer) clearTimeout(flashTimer);
    flashTimer = setTimeout(() => {
        showFlash.value = false;
    }, 4000);
};

// ==========================================
// MODAL DE CONFIRMACIÓN PERSONALIZADO
// ==========================================
const showConfirmModal = ref(false);
const confirmMessage = ref('');
const pendingAction = ref(null);

const openConfirm = (message, action) => {
    confirmMessage.value = message;
    pendingAction.value = action;
    showConfirmModal.value = true;
    document.body.style.overflow = 'hidden';
};

const executeConfirm = () => {
    if (pendingAction.value) pendingAction.value();
    closeConfirm();
};

const closeConfirm = () => {
    showConfirmModal.value = false;
    pendingAction.value = null;
    document.body.style.overflow = 'auto';
};

// ==========================================
// ESTADOS, BÚSQUEDA Y MODALES
// ==========================================
const searchQuery = ref('');
const currentPage = ref(1);
const itemsPerPage = 10;

const showModal = ref(false);
const selectedProveedor = ref(null);

// Modal de Formulario (Crear/Editar)
const showFormModal = ref(false);
const isEditing = ref(false);

const form = useForm({
    id: null,
    nombre_empresa: '',
    email_principal: '',
    emails_cc: [],
    is_active: true,
});

const tempCcEmail = ref('');

const addCcEmail = () => {
    if (tempCcEmail.value && /^\S+@\S+\.\S+$/.test(tempCcEmail.value) && !form.emails_cc.includes(tempCcEmail.value)) {
        form.emails_cc.push(tempCcEmail.value);
        tempCcEmail.value = '';
    }
};

const removeCcEmail = (index) => {
    form.emails_cc.splice(index, 1);
};

// Abre modal para CREAR
const openCreateModal = () => {
    isEditing.value = false;
    form.reset();
    form.clearErrors();
    form.is_active = true;
    tempCcEmail.value = '';
    showFormModal.value = true;
    document.body.style.overflow = 'hidden';
};

// Abre modal para EDITAR
const openEditModal = (proveedor) => {
    isEditing.value = true;
    form.clearErrors();
    form.id = proveedor.id;
    form.nombre_empresa = proveedor.nombre_empresa;
    form.email_principal = proveedor.email_principal;
    form.emails_cc = proveedor.emails_cc || [];
    form.is_active = proveedor.is_active;
    tempCcEmail.value = '';

    showFormModal.value = true;
    document.body.style.overflow = 'hidden';
};

// Envío unificado con manejo de Errores
const submitForm = () => {
    if (isEditing.value) {
        form.put(route('proveedores.update', form.id), {
            onSuccess: () => {
                closeFormModal();
                displayToast('Proveedor actualizado exitosamente.', 'success');
            },
            onError: () => {
                displayToast('Por favor, corrige los errores en el formulario.', 'error');
            }
        });
    } else {
        form.post(route('proveedores.store'), {
            onSuccess: () => {
                closeFormModal();
                displayToast('Proveedor registrado exitosamente.', 'success');
            },
            onError: () => {
                displayToast('Por favor, corrige los errores en el formulario.', 'error');
            }
        });
    }
};

const closeFormModal = () => {
    showFormModal.value = false;
    form.reset();
    form.clearErrors();
    document.body.style.overflow = 'auto';
};

// Eliminar Proveedor
const deleteProveedor = (id) => {
    openConfirm('¿Estás seguro de que deseas eliminar este proveedor? Esta acción no se puede deshacer.', () => {
        router.delete(route('proveedores.destroy', id), {
            onSuccess: () => {
                displayToast('Proveedor eliminado del sistema.', 'success');
            },
            onError: () => {
                displayToast('Ocurrió un error al intentar eliminar el proveedor.', 'error');
            }
        });
    });
};

const openDetails = (proveedor) => {
    selectedProveedor.value = proveedor;
    showModal.value = true;
    document.body.style.overflow = 'hidden';
};

const closeModal = () => {
    showModal.value = false;
    setTimeout(() => selectedProveedor.value = null, 300);
    document.body.style.overflow = 'auto';
};

// ==========================================
// FILTROS Y PAGINACIÓN
// ==========================================
const filteredProveedores = computed(() => {
    if (!searchQuery.value) return props.proveedores;

    const query = searchQuery.value.toLowerCase();
    return props.proveedores.filter(proveedor => {
        const nombreMatch = (proveedor.nombre_empresa || '').toLowerCase().includes(query);
        const emailMatch = (proveedor.email_principal || '').toLowerCase().includes(query);
        return nombreMatch || emailMatch;
    });
});

watch(searchQuery, () => {
    currentPage.value = 1;
});

const totalPages = computed(() => {
    return Math.max(1, Math.ceil(filteredProveedores.value.length / itemsPerPage));
});

const paginatedProveedores = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return filteredProveedores.value.slice(start, end);
});

const goToFirstPage = () => { currentPage.value = 1; };
const prevPage = () => { if (currentPage.value > 1) currentPage.value--; };
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++; };
const goToLastPage = () => { currentPage.value = totalPages.value; };

// ==========================================
// LÓGICA DE SELECCIÓN MÚLTIPLE Y ENVÍO (MODIFICADO)
// ==========================================
const selectedProveedores = ref([]);
const isSending = ref(false);
const totalAEnviar = ref(0);
const enviadoActual = ref(0);

const selectAll = computed({
    get: () => {
        // Solo considerar los activos de la página actual para el check "Todos"
        const activeProviders = paginatedProveedores.value.filter(p => p.is_active);
        if (activeProviders.length === 0) return false;
        return activeProviders.every(p => selectedProveedores.value.includes(p.id));
    },
    set: (val) => {
        if (val) {
            paginatedProveedores.value.forEach(p => {
                // SOLO agregar si el proveedor está ACTIVO
                if (p.is_active && !selectedProveedores.value.includes(p.id)) {
                    selectedProveedores.value.push(p.id);
                }
            });
        } else {
            paginatedProveedores.value.forEach(p => {
                const index = selectedProveedores.value.indexOf(p.id);
                if (index > -1) selectedProveedores.value.splice(index, 1);
            });
        }
    }
});

// Simulador de progreso para el UI
const iniciarProgresoSimulado = () => {
    enviadoActual.value = 0;
    const intervalo = setInterval(() => {
        if (enviadoActual.value < totalAEnviar.value - 1) {
            enviadoActual.value++;
        } else {
            clearInterval(intervalo);
        }
    }, 150);
};

// Enviar Correos
const enviarCorreosSeleccionados = () => {
    if (selectedProveedores.value.length === 0) return;

    openConfirm(
        `¿Estás seguro de enviar el correo masivo a los ${selectedProveedores.value.length} proveedores seleccionados?`,
        () => {
            isSending.value = true;
            totalAEnviar.value = selectedProveedores.value.length;
            document.body.style.overflow = 'hidden';

            router.post(route('proveedores.enviar-masivo'), {
                proveedores_ids: selectedProveedores.value
            }, {
                onStart: () => {
                    iniciarProgresoSimulado();
                },
                onFinish: () => {
                    isSending.value = false;
                    document.body.style.overflow = 'auto';
                },
                onSuccess: () => {
                    enviadoActual.value = totalAEnviar.value;
                    selectedProveedores.value = [];
                    displayToast('Correos masivos enviados exitosamente.', 'success');
                },
                onError: () => {
                    displayToast('Hubo un problema al enviar los correos.', 'error');
                }
            });
        }
    );
};

// ==========================================
// UTILIDADES VISUALES
// ==========================================
const getInitials = (name) => {
    if (!name) return 'NN';
    const parts = name.trim().split(' ');
    if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase();
    return name.substring(0, 2).toUpperCase();
};

const formatDate = (dateString) => {
    if (!dateString) return 'Nunca';
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};
</script>

<template>

    <Head title="Proveedores | Correos Masivos" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8 py-8">

            <div
                class="flex flex-col md:flex-row md:items-end justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                <div>
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight">Directorio de Proveedores</h2>
                    <p class="text-sm text-slate-500 mt-1">Gestiona a quiénes se enviarán los correos masivos y sus
                        copias.</p>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
                    <div class="relative w-full sm:w-80">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </span>
                        <input v-model="searchQuery" type="text" placeholder="Buscar por empresa o correo..."
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all text-sm outline-none text-slate-700 placeholder-slate-400" />
                    </div>

                    <Transition enter-active-class="transition ease-out duration-200"
                        enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100"
                        leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100 scale-100"
                        leave-to-class="opacity-0 scale-95">
                        <button v-if="selectedProveedores.length > 0" @click="enviarCorreosSeleccionados"
                            :disabled="isSending"
                            class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white text-sm font-bold rounded-xl shadow-lg shadow-blue-600/30 transition-all flex items-center justify-center gap-2">
                            <svg v-if="!isSending" class="w-5 h-5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <svg v-else class="animate-spin -ml-1 mr-2 h-5 w-5 text-white"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            {{ isSending ? 'Enviando...' : `Enviar a (${selectedProveedores.length})` }}
                        </button>
                    </Transition>

                    <button @click="openCreateModal"
                        class="w-full sm:w-auto px-5 py-2.5 bg-orange-600 hover:bg-orange-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-orange-600/30 transition-all flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Nuevo
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 flex flex-col overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th scope="col" class="px-6 py-4 w-12 text-center">
                                    <input type="checkbox" v-model="selectAll"
                                        :disabled="paginatedProveedores.filter(p => p.is_active).length === 0"
                                        class="rounded border-slate-300 text-orange-600 focus:ring-orange-500 w-4 h-4 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                                </th>
                                <th scope="col"
                                    class="px-6 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                    Empresa</th>
                                <th scope="col"
                                    class="px-6 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                    Correos Copia (CC)</th>
                                <th scope="col"
                                    class="px-6 py-4 text-center text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                    Estado</th>
                                <th scope="col"
                                    class="px-6 py-4 text-center text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                    Último Envío</th>
                                <th scope="col"
                                    class="px-6 py-4 text-center text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                    Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <tr v-for="proveedor in paginatedProveedores" :key="proveedor.id"
                                :class="{
                                    'bg-orange-50/30': selectedProveedores.includes(proveedor.id),
                                    'opacity-50 bg-slate-50 grayscale': !proveedor.is_active
                                }"
                                class="hover:bg-slate-50/80 transition-all group">

                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <input type="checkbox" :value="proveedor.id" v-model="selectedProveedores"
                                        class="rounded border-slate-300 text-orange-600 focus:ring-orange-500 w-4 h-4 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                                        :disabled="!proveedor.is_active">
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="h-9 w-9 rounded-full bg-slate-100 flex items-center justify-center text-slate-700 font-bold text-xs ring-2 ring-white shadow-sm">
                                            {{ getInitials(proveedor.nombre_empresa) }}
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-slate-900"
                                                :class="{ 'line-through text-slate-400': !proveedor.is_active }">{{
                                                    proveedor.nombre_empresa }}</span>
                                            <span class="text-[11px] font-medium text-orange-600">{{
                                                proveedor.email_principal }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <div v-if="proveedor.emails_cc && proveedor.emails_cc.length > 0"
                                        class="flex flex-wrap gap-1">
                                        <span v-for="(cc, index) in proveedor.emails_cc" :key="index"
                                            class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                            {{ cc }}
                                        </span>
                                    </div>
                                    <span v-else class="text-xs text-slate-400 italic">Sin copias</span>
                                </td>

                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <span v-if="proveedor.is_active"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-bold border border-emerald-200 bg-emerald-50 text-emerald-700 shadow-sm ring-1 ring-inset ring-emerald-600/20 tracking-wide">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Activo
                                    </span>
                                    <span v-else
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-bold border border-slate-200 bg-slate-50 text-slate-500 shadow-sm tracking-wide">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Inactivo
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-center whitespace-nowrap text-xs text-slate-500 font-medium">
                                    {{ formatDate(proveedor.last_sent_at) }}
                                </td>

                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-2">
                                        <button @click="openDetails(proveedor)"
                                            class="inline-flex items-center justify-center p-2 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-orange-600 hover:border-orange-200 hover:bg-orange-50 transition-all shadow-sm focus:outline-none"
                                            title="Ver detalle">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                        <button @click="openEditModal(proveedor)"
                                            class="inline-flex items-center justify-center p-2 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-blue-600 hover:border-blue-200 hover:bg-blue-50 transition-all shadow-sm focus:outline-none"
                                            title="Editar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button @click="deleteProveedor(proveedor.id)"
                                            class="inline-flex items-center justify-center p-2 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-red-600 hover:border-red-200 hover:bg-red-50 transition-all shadow-sm focus:outline-none"
                                            title="Eliminar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="filteredProveedores.length === 0">
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center">
                                        <div
                                            class="h-12 w-12 rounded-full bg-slate-50 flex items-center justify-center border border-slate-100 mb-3 text-slate-400">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                                            </svg>
                                        </div>
                                        <h3 class="text-sm font-bold text-slate-900">No se encontraron proveedores</h3>
                                        <p class="text-xs text-slate-500 mt-1">Intenta con otro término o crea uno
                                            nuevo.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="filteredProveedores.length > 0"
                    class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex items-center justify-between">
                    <span class="text-xs text-slate-500 font-medium">
                        Mostrando <span class="font-bold text-slate-900">{{ (currentPage - 1) * itemsPerPage + 1
                        }}</span> -
                        <span class="font-bold text-slate-900">{{ Math.min(currentPage * itemsPerPage,
                            filteredProveedores.length) }}</span>
                        de <span class="font-bold text-slate-900">{{ filteredProveedores.length }}</span>
                        <span v-if="selectedProveedores.length > 0" class="ml-2 text-blue-600 font-bold">
                            ({{ selectedProveedores.length }} seleccionados)
                        </span>
                    </span>

                    <div class="flex items-center gap-1.5">
                        <button @click="goToFirstPage" :disabled="currentPage === 1"
                            class="px-3 py-1.5 text-[11px] font-bold rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 disabled:opacity-40 transition-all uppercase">Inicio</button>
                        <button @click="prevPage" :disabled="currentPage === 1"
                            class="p-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 disabled:opacity-40 transition-all"><svg
                                class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 19l-7-7 7-7" />
                            </svg></button>
                        <span class="px-3 py-1 text-xs font-black text-slate-700 bg-slate-200/50 rounded-md mx-1">Pág.
                            {{ currentPage }} de {{ totalPages }}</span>
                        <button @click="nextPage" :disabled="currentPage === totalPages"
                            class="p-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 disabled:opacity-40 transition-all"><svg
                                class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg></button>
                        <button @click="goToLastPage" :disabled="currentPage === totalPages"
                            class="px-3 py-1.5 text-[11px] font-bold rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 disabled:opacity-40 transition-all uppercase">Final</button>
                    </div>
                </div>
            </div>
        </div>

        <Teleport to="body">
            <Transition enter-active-class="ease-out duration-300" enter-from-class="opacity-0"
                enter-to-class="opacity-100" leave-active-class="ease-in duration-200" leave-from-class="opacity-100"
                leave-to-class="opacity-0">
                <div v-if="showConfirmModal" class="fixed inset-0 z-[250] overflow-y-auto">
                    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="closeConfirm"></div>
                    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                        <Transition enter-active-class="ease-out duration-300"
                            enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                            leave-active-class="ease-in duration-200"
                            leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                            leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                            <div class="relative transform overflow-hidden rounded-2xl bg-white text-center shadow-2xl transition-all sm:my-8 w-full max-w-sm border border-slate-200 p-6">
                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-orange-100 mb-4">
                                    <svg class="h-8 w-8 text-orange-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-black text-slate-900 mb-2">Confirmar Acción</h3>
                                <p class="text-sm text-slate-500 px-2">{{ confirmMessage }}</p>
                                <div class="mt-6 flex flex-col-reverse sm:flex-row justify-center gap-3">
                                    <button @click="closeConfirm" class="w-full sm:w-auto px-5 py-2.5 text-sm font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">Cancelar</button>
                                    <button @click="executeConfirm" class="w-full sm:w-auto px-5 py-2.5 text-sm font-bold text-white bg-orange-600 hover:bg-orange-700 rounded-xl shadow-lg shadow-orange-600/30 transition-all">Sí, continuar</button>
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <Teleport to="body">
            <Transition enter-active-class="ease-out duration-300" enter-from-class="opacity-0"
                enter-to-class="opacity-100" leave-active-class="ease-in duration-200" leave-from-class="opacity-100"
                leave-to-class="opacity-0">
                <div v-if="showModal" class="fixed inset-0 z-[150] overflow-y-auto">
                    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="closeModal">
                    </div>
                    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                        <Transition enter-active-class="ease-out duration-300"
                            enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                            leave-active-class="ease-in duration-200"
                            leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                            leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                            <div v-if="selectedProveedor"
                                class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 w-full max-w-2xl border border-slate-200">
                                <div class="flex flex-col max-h-[90vh]">
                                    <div
                                        class="shrink-0 bg-white/90 backdrop-blur-md px-6 py-5 border-b border-slate-200 flex items-center justify-between z-10">
                                        <div>
                                            <h3 class="text-lg font-black text-slate-900">{{
                                                selectedProveedor.nombre_empresa }}</h3>
                                            <p class="text-xs text-slate-500 mt-0.5">ID: #{{ selectedProveedor.id }} |
                                                Registrado: {{ formatDate(selectedProveedor.created_at) }}</p>
                                        </div>
                                        <button @click="closeModal"
                                            class="rounded-full p-2 text-slate-400 hover:bg-slate-100"><svg
                                                class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg></button>
                                    </div>

                                    <div class="flex-1 overflow-y-auto px-6 py-6 bg-slate-50/50 space-y-6">
                                        <div>
                                            <h4
                                                class="text-xs font-black tracking-widest text-slate-400 uppercase mb-3">
                                                Información de Contacto</h4>
                                            <div
                                                class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 flex justify-between items-center">
                                                <div>
                                                    <p class="text-[10px] uppercase font-bold text-slate-400">Correo
                                                        Principal</p>
                                                    <p class="text-sm font-bold text-orange-600 mt-1">{{
                                                        selectedProveedor.email_principal }}</p>
                                                </div>
                                                <span v-if="selectedProveedor.is_active"
                                                    class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">ACTIVO</span>
                                                <span v-else
                                                    class="px-2.5 py-1 rounded-md text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">INACTIVO</span>
                                            </div>
                                        </div>

                                        <div>
                                            <h4
                                                class="text-xs font-black tracking-widest text-slate-400 uppercase mb-3">
                                                Correos en Copia (CC)</h4>
                                            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                                                <div v-if="selectedProveedor.emails_cc && selectedProveedor.emails_cc.length > 0"
                                                    class="flex flex-col gap-2">
                                                    <div v-for="(cc, i) in selectedProveedor.emails_cc" :key="i"
                                                        class="flex items-center gap-2 text-sm text-slate-700 font-medium">
                                                        <svg class="w-4 h-4 text-slate-400" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                                        </svg>
                                                        {{ cc }}
                                                    </div>
                                                </div>
                                                <p v-else class="text-sm text-slate-400 italic">No hay correos
                                                    registrados en copia.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        class="shrink-0 bg-white/90 px-6 py-4 border-t border-slate-100 flex justify-end z-10">
                                        <button @click="closeModal"
                                            class="px-6 py-2.5 text-sm font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors uppercase">Cerrar</button>
                                    </div>
                                </div>
                            </div>

                        </Transition>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <Teleport to="body">
            <Transition enter-active-class="ease-out duration-300" enter-from-class="opacity-0"
                enter-to-class="opacity-100" leave-active-class="ease-in duration-200" leave-from-class="opacity-100"
                leave-to-class="opacity-0">
                <div v-if="showFormModal" class="fixed inset-0 z-[150] overflow-y-auto">
                    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"
                        @click="closeFormModal"></div>
                    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                        <Transition enter-active-class="ease-out duration-300"
                            enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                            leave-active-class="ease-in duration-200"
                            leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                            leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                            <div
                                class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 w-full max-w-lg border border-slate-200">
                                <form @submit.prevent="submitForm" class="flex flex-col">
                                    <div class="px-6 py-5 border-b border-slate-200 flex justify-between items-center">
                                        <h3 class="text-lg font-black text-slate-900">
                                            {{ isEditing ? 'Editar Proveedor' : 'Registrar Proveedor' }}
                                        </h3>
                                        <button type="button" @click="closeFormModal"
                                            class="text-slate-400 hover:text-slate-600"><svg class="h-5 w-5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg></button>
                                    </div>

                                    <div class="p-6 space-y-5 bg-slate-50/50">
                                        <div
                                            class="flex items-center justify-between p-3 bg-white border border-slate-200 rounded-xl">
                                            <div class="flex flex-col">
                                                <span class="text-xs font-bold text-slate-900 uppercase">Estado del
                                                    Proveedor</span>
                                                <span class="text-[10px] text-slate-500">¿Recibirá correos
                                                    masivos?</span>
                                            </div>
                                            <button type="button" @click="form.is_active = !form.is_active"
                                                :class="form.is_active ? 'bg-emerald-500' : 'bg-slate-300'"
                                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none shadow-inner">
                                                <span :class="form.is_active ? 'translate-x-5' : 'translate-x-0'"
                                                    class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                                            </button>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nombre
                                                de Empresa</label>
                                            <input v-model="form.nombre_empresa" type="text" required
                                                class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-orange-500 focus:border-orange-500 text-sm"
                                                placeholder="Ej. Tech Solutions SAC">
                                            <div v-if="form.errors.nombre_empresa" class="text-red-500 text-xs mt-1">{{
                                                form.errors.nombre_empresa }}</div>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Email
                                                Principal (Para)</label>
                                            <input v-model="form.email_principal" type="email" required
                                                class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-orange-500 focus:border-orange-500 text-sm"
                                                placeholder="contacto@empresa.com">
                                            <div v-if="form.errors.email_principal" class="text-red-500 text-xs mt-1">{{
                                                form.errors.email_principal }}</div>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Correos
                                                en Copia (CC)</label>
                                            <div class="flex gap-2 mb-2">
                                                <input v-model="tempCcEmail" @keydown.enter.prevent="addCcEmail"
                                                    type="email"
                                                    class="flex-1 px-4 py-2 border border-slate-300 rounded-xl focus:ring-orange-500 focus:border-orange-500 text-sm"
                                                    placeholder="Añadir correo y presionar enter o '+'">
                                                <button type="button" @click="addCcEmail"
                                                    class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl font-bold transition-colors">+</button>
                                            </div>

                                            <div v-if="form.emails_cc.length > 0" class="flex flex-wrap gap-2 mt-2">
                                                <span v-for="(cc, index) in form.emails_cc" :key="index"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1 bg-white border border-slate-200 rounded-lg text-xs font-medium text-slate-600 shadow-sm">
                                                    {{ cc }}
                                                    <button type="button" @click="removeCcEmail(index)"
                                                        class="text-slate-400 hover:text-red-500 focus:outline-none">&times;</button>
                                                </span>
                                            </div>
                                            <div v-if="form.errors.emails_cc" class="text-red-500 text-xs mt-1">{{
                                                form.errors.emails_cc }}</div>
                                        </div>
                                    </div>

                                    <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-white">
                                        <button type="button" @click="closeFormModal"
                                            class="px-5 py-2 text-sm font-bold text-slate-600 hover:bg-slate-50 border border-slate-200 rounded-xl transition-colors">Cancelar</button>
                                        <button type="submit" :disabled="form.processing"
                                            class="px-5 py-2 text-sm font-bold text-white bg-orange-600 hover:bg-orange-700 rounded-xl shadow-lg shadow-orange-600/30 transition-all disabled:opacity-50 flex items-center gap-2">
                                            <span v-if="form.processing">Guardando...</span>
                                            <span v-else>{{ isEditing ? 'Actualizar Cambios' : 'Guardar Proveedor'
                                            }}</span>
                                        </button>
                                    </div>
                                </form>
                            </div>

                        </Transition>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <Teleport to="body">
            <Transition enter-active-class="ease-out duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="ease-in duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="isSending" class="fixed inset-0 z-[300] flex items-center justify-center p-4">
                    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-md"></div>
                    <div class="relative transform overflow-hidden rounded-3xl bg-white shadow-2xl transition-all w-full max-w-md p-8 text-center border border-slate-200">
                        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-orange-50 mb-6">
                            <svg class="h-10 w-10 text-orange-600 animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-black text-slate-900">Enviando Correos Masivos</h3>
                        <p class="text-sm text-slate-500 mt-2">Estamos procesando tu solicitud...</p>

                        <div class="mt-8">
                            <div class="flex justify-between items-end mb-2">
                                <span class="text-xs font-black text-orange-600 uppercase tracking-widest">PROGRESO</span>
                                <span class="text-2xl font-black text-slate-900">{{ enviadoActual }}<span class="text-slate-300 text-lg">/{{ totalAEnviar }}</span></span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-4 overflow-hidden border border-slate-200 p-0.5 shadow-inner">
                                <div class="bg-orange-500 h-full rounded-full transition-all duration-500 ease-out shadow-[0_0_10px_rgba(249,115,22,0.4)]" :style="{ width: `${totalAEnviar > 0 ? (enviadoActual / totalAEnviar) * 100 : 0}%` }"></div>
                            </div>
                        </div>

                        <div class="mt-8 p-4 bg-red-50 rounded-2xl border border-red-100 flex items-center gap-3">
                            <svg class="w-6 h-6 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <p class="text-[11px] text-red-700 font-bold leading-tight text-left uppercase tracking-tighter">No cierre ni actualice la ventana hasta que finalice el proceso para evitar duplicados.</p>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <Teleport to="body">
            <Transition enter-active-class="transform ease-out duration-300 transition"
                enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
                leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100"
                leave-to-class="opacity-0">
                <div v-if="showFlash"
                    class="fixed top-6 right-6 z-[300] max-w-sm w-full bg-slate-900 shadow-2xl rounded-2xl pointer-events-auto flex ring-1 ring-black ring-opacity-5 overflow-hidden">
                    <div class="p-4 flex items-center w-full">
                        <div class="flex-shrink-0">
                            <svg v-if="toastType === 'success'" class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <svg v-else class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3 w-0 flex-1 pt-0.5">
                            <p class="text-sm font-bold text-white">{{ toastMessage }}</p>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AuthenticatedLayout>
</template>
