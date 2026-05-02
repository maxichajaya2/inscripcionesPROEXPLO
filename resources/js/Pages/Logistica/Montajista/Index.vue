<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

// 1. CORRECCIÓN DEL PROP (Ahora recibe un Array directamente)
const props = defineProps({
    personal_montaje: {
        type: Array,
        required: true,
        default: () => []
    }
});

const showHelpModal = ref(false);


// ==========================================
// MENSAJES FLASH Y CONFIRMACIONES
// ==========================================
const showFlash = ref(false);
const toastMessage = ref('');
const toastType = ref('success');
let flashTimer = null;

const displayToast = (message, type = 'success') => {
    toastMessage.value = message;
    toastType.value = type;
    showFlash.value = true;
    if (flashTimer) clearTimeout(flashTimer);
    flashTimer = setTimeout(() => { showFlash.value = false; }, 4000);
};

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
const selectedPersona = ref(null);
const showFormModal = ref(false);
const isEditing = ref(false);

const form = useForm({
    id: null,
    documento: '',
    nombres: '',
    apellidos: '',
    correo: '',
    cargo: '',
    ruc_empresa: '',
    tipo_documento: '',
    nombre_empresa: '',
    aseguradora: '',
    poliza: '',
    codigo_qr: '',
    autorizado: false,
    motivo_bloqueo: '',
});

// Generador de código aleatorio para nuevos registros
const generarCodigoQR = () => {
    form.codigo_qr = 'PROX26-' + Math.random().toString(36).substr(2, 6).toUpperCase();
};

const openCreateModal = () => {
    isEditing.value = false;
    form.reset();
    form.clearErrors();
    form.autorizado = true;
    generarCodigoQR();
    showFormModal.value = true;
    document.body.style.overflow = 'hidden';
};

const openEditModal = (persona) => {
    isEditing.value = true;
    form.clearErrors();
    Object.assign(form, persona);
    form.autorizado = Boolean(persona.autorizado);
    showFormModal.value = true;
    document.body.style.overflow = 'hidden';
};

const submitForm = () => {
    if (form.autorizado) form.motivo_bloqueo = '';

    const routeName = isEditing.value ? 'personal-montaje.update' : 'personal-montaje.store';
    const routeParam = isEditing.value ? form.id : undefined;

    // LLAMADA DIRECTA (Corrección)
    if (isEditing.value) {
        form.put(route(routeName, routeParam), {
            onSuccess: () => {
                closeFormModal();
                displayToast('Personal actualizado exitosamente.', 'success');
            },
            onError: () => displayToast('Por favor, corrige los errores en el formulario.', 'error')
        });
    } else {
        form.post(route(routeName), {
            onSuccess: () => {
                closeFormModal();
                displayToast('Personal registrado exitosamente.', 'success');
            },
            onError: () => displayToast('Por favor, corrige los errores en el formulario.', 'error')
        });
    }
};

const closeFormModal = () => {
    showFormModal.value = false;
    form.reset();
    form.clearErrors();
    document.body.style.overflow = 'auto';
};

const deletePersona = (id) => {
    openConfirm('¿Estás seguro de eliminar a este trabajador? Perderá el acceso al evento.', () => {
        router.delete(route('personal-montaje.destroy', id), {
            onSuccess: () => {
                displayToast('Trabajador eliminado del sistema.', 'success');
                // Si borramos el último item de una página, retrocedemos
                if (paginatedPersonal.value.length === 1 && currentPage.value > 1) {
                    currentPage.value--;
                }
            },
            onError: () => displayToast('Error al intentar eliminar el registro.', 'error')
        });
    });
};

const openDetails = (persona) => {
    selectedPersona.value = persona;
    showModal.value = true;
    document.body.style.overflow = 'hidden';
};

const closeModal = () => {
    showModal.value = false;
    setTimeout(() => selectedPersona.value = null, 300);
    document.body.style.overflow = 'auto';
};

// ==========================================
// FILTROS Y PAGINACIÓN FRONT-END
// ==========================================
const filteredPersonal = computed(() => {
    const trabajadores = props.personal_montaje || [];

    if (!searchQuery.value) return trabajadores;

    const q = searchQuery.value.toLowerCase();
    return trabajadores.filter(p =>
        (p.nombres + ' ' + p.apellidos).toLowerCase().includes(q) ||
        (p.documento || '').toLowerCase().includes(q) ||
        (p.nombre_empresa || '').toLowerCase().includes(q)
    );
});

// Reiniciar a la página 1 cuando se busca algo
watch(searchQuery, () => currentPage.value = 1);

const totalPages = computed(() => Math.max(1, Math.ceil(filteredPersonal.value.length / itemsPerPage)));

const displayedPages = computed(() => {
    const total = totalPages.value;
    const current = currentPage.value;
    const delta = 2; // Páginas a mostrar a cada lado de la actual
    const range = [];
    const rangeWithDots = [];
    let l;

    range.push(1);
    for (let i = current - delta; i <= current + delta; i++) {
        if (i < total && i > 1) {
            range.push(i);
        }
    }
    if (total > 1) range.push(total);

    for (let i of range) {
        if (l) {
            if (i - l === 2) {
                rangeWithDots.push(l + 1);
            } else if (i - l !== 1) {
                rangeWithDots.push('...');
            }
        }
        rangeWithDots.push(i);
        l = i;
    }

    return rangeWithDots;
});

const paginatedPersonal = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    return filteredPersonal.value.slice(start, start + itemsPerPage);
});

// ==========================================
// LÓGICA DE ENVÍO MASIVO (CÓDIGOS QR)
// ==========================================
const selectedItems = ref([]);
const isSending = ref(false);
const totalAEnviar = ref(0);
const enviadoActual = ref(0);

const selectAll = computed({
    get: () => {
        const autorizados = paginatedPersonal.value.filter(p => p.autorizado);
        if (autorizados.length === 0) return false;
        return autorizados.every(p => selectedItems.value.includes(p.id));
    },
    set: (val) => {
        paginatedPersonal.value.forEach(p => {
            if (val && Boolean(p.autorizado) && !selectedItems.value.includes(p.id)) {
                selectedItems.value.push(p.id);
            } else if (!val) {
                const index = selectedItems.value.indexOf(p.id);
                if (index > -1) selectedItems.value.splice(index, 1);
            }
        });
    }
});

const enviarQRsSeleccionados = () => {
    if (selectedItems.value.length === 0) return;
    openConfirm(`¿Enviar los códigos QR de acceso a los ${selectedItems.value.length} trabajadores seleccionados?`, () => {
        isSending.value = true;
        totalAEnviar.value = selectedItems.value.length;
        document.body.style.overflow = 'hidden';

        // Simulación de envío
        enviadoActual.value = 0;
        const interval = setInterval(() => {
            if (enviadoActual.value < totalAEnviar.value) {
                enviadoActual.value++;
            } else {
                clearInterval(interval);
                isSending.value = false;
                document.body.style.overflow = 'auto';
                selectedItems.value = [];
                displayToast('Códigos QR enviados exitosamente.', 'success');
            }
        }, 200);
    });
};

const imprimirQR = (persona) => {
    const urlQR = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${persona.codigo_qr}`;

    // Crear una ventana temporal para impresión
    const ventanaImpresion = window.open('', '_blank');
    ventanaImpresion.document.write(`
        <html>
            <head>
                <title>Imprimir Fotocheck - ${persona.nombres}</title>
                <style>
                    body { font-family: sans-serif; text-align: center; padding: 20px; }
                    .card { border: 2px solid #000; padding: 20px; width: 300px; margin: 0 auto; border-radius: 10px; }
                    .name { font-size: 1.2rem; font-weight: bold; margin-bottom: 5px; }
                    .company { color: #666; margin-bottom: 15px; }
                    img { margin-bottom: 15px; }
                </style>
            </head>
            <body onload="window.print();window.close()">
                <div class="card">
                    <div class="name">${persona.nombres} ${persona.apellidos}</div>
                    <div class="company">${persona.nombre_empresa}</div>
                    <img src="${urlQR}" />
                    <div style="font-family: monospace; font-weight: bold;">${persona.codigo_qr}</div>
                </div>
            </body>
        </html>
    `);
    ventanaImpresion.document.close();
};


// ==========================================
// LÓGICA DE IMPORTACIÓN EXCEL/CSV
// ==========================================
const fileInput = ref(null);
const isImporting = ref(false);

const triggerFileInput = () => {
    fileInput.value.click();
};

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    isImporting.value = true;

    router.post(route('personal-montaje.importar'), { archivo: file }, {
        forceFormData: true,
        onSuccess: () => {
            displayToast('¡Personal importado correctamente!', 'success');
            event.target.value = '';
        },
        onError: (errors) => {
            const mensajeExacto = errors.archivo || errors.error_importacion || 'Hubo un error con el archivo.';
            displayToast(mensajeExacto, 'error');
            console.error("Detalle del error del servidor:", errors);
            event.target.value = '';
        },
        onFinish: () => {
            isImporting.value = false;
        }
    });
};


// ==========================================
// CAMBIO MANUAL DE PRESENCIA (INGRESO/SALIDA)
// ==========================================
const processingId = ref(null);

const togglePresencia = (persona) => {
    if (!persona.autorizado && persona.estado_presencia !== 'Adentro') {
        displayToast('No puedes ingresar manualmente a alguien bloqueado.', 'error');
        return;
    }

    const estadoActual = persona.estado_presencia || 'Afuera';
    const nuevoEstado = estadoActual === 'Adentro' ? 'Afuera' : 'Adentro';
    const accionTexto = estadoActual === 'Adentro' ? 'registrar la SALIDA' : 'registrar el INGRESO';

    openConfirm(`¿Estás seguro de ${accionTexto} manual de ${persona.nombres}?`, () => {
        processingId.value = persona.id;

        router.patch(route('personal-montaje.toggle-presencia', persona.id), {
            estado_presencia: nuevoEstado
        }, {
            preserveScroll: true,
            onSuccess: () => {
                displayToast(`Se registró el ${nuevoEstado === 'Adentro' ? 'ingreso' : 'salida'} correctamente.`, 'success');
            },
            onError: (errors) => {
                console.error(errors);
                displayToast('Hubo un error al cambiar el estado.', 'error');
            },
            onFinish: () => {
                processingId.value = null;
            }
        });
    });
};

// ==========================================
// LÓGICA DE HISTORIAL (MODAL)
// ==========================================
const showHistorialModal = ref(false);
const historialData = ref([]);
const loadingHistorial = ref(false);

const openHistorial = (persona) => {
    selectedPersona.value = persona;
    showHistorialModal.value = true;
    loadingHistorial.value = true;
    document.body.style.overflow = 'hidden';

    // Asumiendo que usas axios para esto
    axios.get(route('personal-montaje.historial-especifico', persona.id))
        .then(response => {
            historialData.value = response.data;
        })
        .catch(error => {
            displayToast('No se pudo cargar el historial', 'error');
        })
        .finally(() => {
            loadingHistorial.value = false;
        });
};

const closeHistorialModal = () => {
    showHistorialModal.value = false;
    setTimeout(() => {
        historialData.value = [];
        selectedPersona.value = null;
    }, 300);
    document.body.style.overflow = 'auto';
};

// DESCARGA DE PLANTILLA
// ==========================================
const descargarPlantilla = () => {
    const url = '/documents/formato_montajistas.xlsx';
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', 'formato_montajistas.xlsx');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};

const getInitials = (nombres, apellidos) => {
    return ((nombres?.[0] || '') + (apellidos?.[0] || '')).toUpperCase() || 'NN';
};
</script>

<template>

    <Head title="Control de Personal | PROEXPLO 2026" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8 py-8">

            <!-- HEADER -->
            <div
                class="flex flex-col xl:flex-row xl:items-center justify-between gap-6 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                <!-- Título -->
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <div class="p-2 bg-orange-100 rounded-lg">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h2 class="text-2xl font-black text-slate-900 tracking-tight">Personal de Montaje</h2>
                    </div>
                    <p class="text-sm text-slate-500 mt-1 pl-12">Gestiona ingresos, autorizaciones y fotochecks.</p>
                </div>

                <!-- Controles Responsivos -->
                <div class="flex flex-col md:flex-row items-center gap-3 w-full xl:w-auto">

                    <div class="relative w-full md:w-64 lg:w-72 flex-shrink-0">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </span>
                        <input v-model="searchQuery" type="text" placeholder="Buscar DNI, empresa..."
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all text-sm outline-none text-slate-700 placeholder-slate-400" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 w-full md:w-auto">

                        <!-- Botón Escáner -->
                        <Link :href="route('escaner.index')"
                            class="w-full px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white text-sm font-bold rounded-xl shadow-lg shadow-slate-900/20 transition-all flex items-center justify-center gap-2">
                            <svg class="w-5 h-5 text-orange-400 flex-shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                            <span class="whitespace-nowrap">Escáner</span>
                        </Link>

                        <!-- Botón Nuevo -->
                        <button @click="openCreateModal"
                            class="w-full px-4 py-2.5 bg-orange-600 hover:bg-orange-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-orange-600/30 transition-all flex items-center justify-center gap-2">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            <span class="whitespace-nowrap">Registrar</span>
                        </button>

                        <input type="file" ref="fileInput" class="hidden"
                            accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
                            @change="handleFileUpload">

                        <!-- Botón Importar Excel -->
                        <div class="flex gap-1">
                            <button @click="triggerFileInput" :disabled="isImporting"
                                class="w-full px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-emerald-600/30 transition-all flex items-center justify-center gap-2 disabled:opacity-50">

                                <svg v-if="isImporting" class="animate-spin h-5 w-5 text-white flex-shrink-0"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                <svg v-else class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                                <span class="whitespace-nowrap">{{ isImporting ? 'Cargando...' : 'Importar' }}</span>
                            </button>

                            <button @click="showHelpModal = true"
                                class="px-3 bg-emerald-700 hover:bg-emerald-800 text-white rounded-r-xl border-l border-emerald-500 transition-colors"
                                title="Ver formato obligatorio">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </button>

                        </div>

                    </div>

                </div>
            </div>
        </div>

        <!-- TABLA Y PAGINACIÓN -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 flex flex-col overflow-hidden relative">

            <!-- Barra de Acción Masiva Flotante -->
            <Transition enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0 -translate-y-full" enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-full">
                <div v-if="selectedItems.length > 0"
                    class="absolute top-0 inset-x-0 bg-blue-600 px-6 py-3 flex items-center justify-between z-20 shadow-md">
                    <span class="text-white font-bold text-sm">{{ selectedItems.length }} trabajadores seleccionados</span>
                    <button @click="enviarQRsSeleccionados" :disabled="isSending"
                        class="bg-white text-blue-600 px-4 py-1.5 rounded-lg text-xs font-black uppercase tracking-wider hover:bg-blue-50 transition-colors shadow-sm">
                        Enviar Fotochecks QR
                    </button>
                </div>
            </Transition>

            <div class="overflow-x-auto" :class="{ 'pt-12': selectedItems.length > 0 }">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-4 w-12 text-center">
                                <input type="checkbox" v-model="selectAll"
                                    :disabled="paginatedPersonal.filter(p => p.autorizado).length === 0"
                                    class="rounded border-slate-300 text-orange-600 focus:ring-orange-500 w-4 h-4 cursor-pointer disabled:opacity-50">
                            </th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">Trabajador</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">Documento / Cargo</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">Empresa</th>
                            <th class="px-6 py-4 text-center text-[11px] font-bold text-slate-500 uppercase tracking-wider">Seguridad</th>
                            <th class="px-6 py-4 text-center text-[11px] font-bold text-slate-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">Código QR / Pase</th>
                            <th class="px-6 py-4 text-center text-[11px] font-bold text-slate-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <tr v-for="persona in paginatedPersonal" :key="persona.id"
                            :class="{ 'bg-orange-50/30': selectedItems.includes(persona.id), 'opacity-60 bg-red-50/20': !persona.autorizado }"
                            class="hover:bg-slate-50/80 transition-all group">

                            <td class="px-6 py-4 text-center">
                                <input type="checkbox" :value="persona.id" v-model="selectedItems"
                                    class="rounded border-slate-300 text-orange-600 focus:ring-orange-500 w-4 h-4 cursor-pointer disabled:opacity-50"
                                    :disabled="!persona.autorizado">
                            </td>

                            <!-- Nombres -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="h-10 w-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 font-black text-xs ring-2 ring-white shadow-sm border border-slate-200">
                                        {{ getInitials(persona.nombres, persona.apellidos) }}
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-slate-900">{{ persona.nombres }} {{ persona.apellidos }}</span>
                                        <span class="text-[11px] font-medium text-slate-500">{{ persona.correo || 'Sin correo' }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- DNI y Cargo -->
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-slate-700">{{ persona.documento }}</span>
                                    <span class="text-xs text-slate-500">{{ persona.cargo }}</span>
                                </div>
                            </td>

                            <!-- Empresa y SCTR -->
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-orange-700">{{ persona.nombre_empresa }}</span>
                                    <span class="text-xs text-slate-500 font-mono mb-1">RUC: {{ persona.ruc_empresa }}</span>

                                    <!-- Indicador visual de Seguro -->
                                    <span v-if="persona.aseguradora"
                                        class="text-[10px] text-blue-600 font-medium flex items-center gap-1 bg-blue-50 w-fit px-1.5 py-0.5 rounded border border-blue-100">
                                        <svg class="w-3 h-3 text-blue-500 flex-shrink-0" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                                            </path>
                                        </svg>
                                        {{ persona.aseguradora }} (Pol: {{ persona.poliza || 'S/N' }})
                                    </span>
                                    <span v-else class="text-[10px] text-slate-400 font-medium italic">
                                        Sin seguro registrado
                                    </span>
                                </div>
                            </td>

                            <!-- Seguridad (Autorizado/No) -->
                            <td class="px-6 py-4 text-center">
                                <span v-if="persona.autorizado"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-bold border border-emerald-200 bg-emerald-50 text-emerald-700 shadow-sm">
                                    <svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                    AUTORIZADO
                                </span>
                                <div v-else class="flex flex-col items-center group/tooltip relative">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-bold border border-rose-200 bg-rose-50 text-rose-700 shadow-sm cursor-help">
                                        <svg class="w-3 h-3 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        BLOQUEADO
                                    </span>
                                    <span class="text-[10px] text-rose-500 mt-1 italic max-w-[120px] truncate" :title="persona.motivo_bloqueo">{{ persona.motivo_bloqueo }}</span>
                                </div>
                            </td>

                            <!-- Estado Presencia -->
                            <td class="px-6 py-4 text-center">
                                <button @click="togglePresencia(persona)" :disabled="processingId === persona.id"
                                    title="Clic para cambiar ingreso/salida manualmente"
                                    :class="persona.estado_presencia === 'Adentro'
                                        ? 'text-orange-600 bg-orange-50 border-orange-200 hover:bg-orange-100 hover:border-orange-300'
                                        : 'text-slate-500 bg-slate-100 border-slate-200 hover:bg-slate-200 hover:border-slate-300'"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black tracking-widest uppercase border transition-all cursor-pointer group/btn shadow-sm disabled:opacity-60 disabled:cursor-wait">

                                    <!-- ESTADO CARGANDO (Spinner) -->
                                    <template v-if="processingId === persona.id">
                                        <svg class="animate-spin h-3 w-3 text-current"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                        <span>ACTUALIZANDO...</span>
                                    </template>

                                    <!-- ESTADO NORMAL -->
                                    <template v-else>
                                        {{ (persona.estado_presencia || 'AFUERA').toUpperCase() }}
                                        <svg class="w-3 h-3 opacity-40 group-hover/btn:opacity-100 transition-opacity"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                        </svg>
                                    </template>
                                </button>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-12 w-12 bg-white border border-slate-200 rounded-lg p-1 shadow-sm flex-shrink-0 group-hover:scale-110 transition-transform cursor-pointer"
                                        @click="imprimirQR(persona)">
                                        <img :src="`https://api.qrserver.com/v1/create-qr-code/?size=50x50&data=${persona.codigo_qr}`" alt="QR">
                                    </div>

                                    <div class="flex flex-col">
                                        <span class="text-[10px] font-mono font-bold text-slate-600 bg-slate-100 px-1.5 py-0.5 rounded w-fit">
                                            {{ persona.codigo_qr }}
                                        </span>
                                        <button @click="imprimirQR(persona)"
                                            class="mt-1 text-[10px] flex items-center gap-1 text-orange-600 hover:text-orange-700 font-bold uppercase tracking-tighter">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                            </svg>
                                            Imprimir Pase
                                        </button>
                                    </div>
                                </div>
                            </td>

                            <!-- Acciones -->
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="openEditModal(persona)"
                                        class="p-2 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-all shadow-sm"><svg
                                            class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg></button>
                                    <button @click="deletePersona(persona.id)"
                                        class="p-2 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-red-600 hover:bg-red-50 transition-all shadow-sm"><svg
                                            class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg></button>
                                    <button @click="openHistorial(persona)"
                                        class="p-2 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-orange-600 hover:bg-orange-50 transition-all shadow-sm"
                                        title="Ver historial de accesos">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredPersonal.length === 0">
                            <td colspan="8" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div
                                        class="h-12 w-12 rounded-full bg-slate-50 flex items-center justify-center border border-slate-100 mb-3 text-slate-400">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-sm font-bold text-slate-900">No se encontraron trabajadores</h3>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- CONTROLES DE PAGINACIÓN FRONTEND -->
            <div v-if="filteredPersonal.length > 0"
                class="px-6 py-4 border-t border-slate-200 flex items-center justify-between bg-slate-50">
                <span class="text-xs text-slate-500 font-medium">
                    Mostrando <span class="font-black text-slate-700">{{ ((currentPage - 1) * itemsPerPage) + 1 }}</span> al
                    <span class="font-black text-slate-700">{{ Math.min(currentPage * itemsPerPage, filteredPersonal.length) }}</span>
                    de <span class="font-black text-slate-700">{{ filteredPersonal.length }}</span> registros
                </span>

                <div class="flex gap-2">
                    <button @click="currentPage--" :disabled="currentPage === 1"
                        class="px-3 py-1.5 border border-slate-200 rounded-lg text-sm font-bold text-slate-600 bg-white hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        Anterior
                    </button>

                    <div class="flex gap-1 hidden sm:flex">
                        <template v-for="page in displayedPages" :key="page">
                            <button v-if="page !== '...'" @click="currentPage = page" :class="currentPage === page
                                ? 'bg-orange-600 text-white border-orange-600 shadow-sm'
                                : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                                class="w-8 h-8 rounded-lg border text-xs font-black flex items-center justify-center transition-colors">
                                {{ page }}
                            </button>
                            <span v-else class="w-8 h-8 flex items-center justify-center text-slate-400 font-bold">
                                ...
                            </span>
                        </template>
                    </div>
                    <button @click="currentPage++" :disabled="currentPage === totalPages"
                        class="px-3 py-1.5 border border-slate-200 rounded-lg text-sm font-bold text-slate-600 bg-white hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        Siguiente
                    </button>
                </div>
            </div>
        </div>


        <!-- MODAL FORMULARIO (CREAR/EDITAR) -->
        <Teleport to="body">
            <Transition enter-active-class="ease-out duration-300" enter-from-class="opacity-0"
                enter-to-class="opacity-100" leave-active-class="ease-in duration-200" leave-from-class="opacity-100"
                leave-to-class="opacity-0">
                <div v-if="showFormModal" class="fixed inset-0 z-[150] overflow-y-auto">
                    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="closeFormModal"></div>
                    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                        <Transition enter-active-class="ease-out duration-300"
                            enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                            leave-active-class="ease-in duration-200"
                            leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                            leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                            <div
                                class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 w-full max-w-3xl border border-slate-200">
                                <form @submit.prevent="submitForm" class="flex flex-col">

                                    <div
                                        class="px-6 py-5 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
                                        <h3 class="text-lg font-black text-slate-900 flex items-center gap-2">
                                            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                                            </svg>
                                            {{ isEditing ? 'Editar Trabajador' : 'Registrar Trabajador' }}
                                        </h3>
                                        <button type="button" @click="closeFormModal"
                                            class="text-slate-400 hover:text-slate-600"><svg class="h-5 w-5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg></button>
                                    </div>

                                    <div class="p-6 space-y-6">
                                        <!-- Card Seguridad PRINCIPAL (Arriba) -->
                                        <!-- ========== AQUI ESTA EL CAMBIO SOLICITADO ========== -->
                                        <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl">
                                            <div class="flex items-center justify-between mb-4">
                                                <div>
                                                    <span class="text-xs font-black text-slate-900 uppercase">Aprobación de Seguridad (SST)</span>
                                                    <p class="text-[11px] text-slate-500">Determina si la persona puede cruzar los molinetes.</p>
                                                </div>
                                                <div class="flex items-center gap-3">
                                                    <!-- TEXTO DINÁMICO AUTORIZADO/BLOQUEADO -->
                                                    <span class="text-[11px] font-black uppercase tracking-wider"
                                                        :class="form.autorizado ? 'text-emerald-600' : 'text-rose-600'">
                                                        {{ form.autorizado ? 'AUTORIZADO' : 'BLOQUEADO' }}
                                                    </span>
                                                    <!-- BOTÓN SWITCH -->
                                                    <button type="button" @click="form.autorizado = !form.autorizado"
                                                        :class="form.autorizado ? 'bg-emerald-500' : 'bg-rose-500'"
                                                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none shadow-inner">
                                                        <span :class="form.autorizado ? 'translate-x-5' : 'translate-x-0'"
                                                            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200"></span>
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- INPUT MOTIVO (Solo visible si esta bloqueado) -->
                                            <div v-if="!form.autorizado" class="mt-3">
                                                <label class="block text-xs font-bold text-rose-700 uppercase mb-1">Motivo del Bloqueo <span class="text-rose-500">*</span></label>
                                                <input v-model="form.motivo_bloqueo" type="text"
                                                    class="w-full px-4 py-2 border border-rose-300 bg-rose-50 rounded-lg focus:ring-rose-500 focus:border-rose-500 text-sm placeholder-rose-300 text-rose-900"
                                                    placeholder="Ej. Falta SCTR, Charla pendiente...">
                                            </div>
                                        </div>
                                        <!-- ==================================================== -->

                                        <!-- Grid Datos -->
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                            <!-- Datos Personales -->
                                            <div class="space-y-4">
                                                <h4 class="text-xs font-black text-orange-600 uppercase tracking-widest border-b border-slate-100 pb-1">
                                                    Datos Personales</h4>
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div class="col-span-2">
                                                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">DNI / Carnet</label>
                                                        <input v-model="form.documento" type="text" required
                                                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 text-sm">
                                                    </div>
                                                    <div class="col-span-1">
                                                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nombres</label>
                                                        <input v-model="form.nombres" type="text" required
                                                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 text-sm">
                                                    </div>
                                                    <div class="col-span-1">
                                                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Apellidos</label>
                                                        <input v-model="form.apellidos" type="text" required
                                                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 text-sm">
                                                    </div>
                                                    <div class="col-span-2">
                                                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Correo (Opcional - Envío QR)</label>
                                                        <input v-model="form.correo" type="email"
                                                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 text-sm">
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Datos Laborales y Seguro -->
                                            <div class="space-y-4">
                                                <h4 class="text-xs font-black text-orange-600 uppercase tracking-widest border-b border-slate-100 pb-1">
                                                    Datos Laborales y Seguro</h4>
                                                <div>
                                                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Empresa Contratista</label>
                                                    <input v-model="form.nombre_empresa" type="text" required
                                                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 text-sm">
                                                </div>
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">RUC</label>
                                                        <input v-model="form.ruc_empresa" type="text" required
                                                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 text-sm">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Cargo</label>
                                                        <input v-model="form.cargo" type="text"
                                                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 text-sm placeholder-slate-400"
                                                            placeholder="Ej. Operario">
                                                    </div>
                                                </div>

                                                <!-- NUEVOS CAMPOS: Aseguradora y Póliza -->
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Aseguradora (SCTR)</label>
                                                        <input v-model="form.aseguradora" type="text"
                                                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 text-sm placeholder-slate-400"
                                                            placeholder="Ej. Rimac, Pacífico...">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">N° Póliza</label>
                                                        <input v-model="form.poliza" type="text"
                                                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 text-sm placeholder-slate-400"
                                                            placeholder="N° de documento">
                                                    </div>
                                                </div>

                                                <div>
                                                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1 flex justify-between">
                                                        Código Escáner (QR)
                                                        <button type="button" @click="generarCodigoQR"
                                                            class="text-[10px] text-orange-600 hover:text-orange-800 underline">Generar Nuevo</button>
                                                    </label>
                                                    <input v-model="form.codigo_qr" type="text" required
                                                        class="w-full px-4 py-2 bg-slate-100 border border-slate-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 font-mono text-sm text-slate-600">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-white">
                                        <button type="button" @click="closeFormModal"
                                            class="px-5 py-2 text-sm font-bold text-slate-600 hover:bg-slate-50 border border-slate-200 rounded-xl transition-colors">Cancelar</button>
                                        <button type="submit" :disabled="form.processing"
                                            class="px-5 py-2 text-sm font-bold text-white bg-orange-600 hover:bg-orange-700 rounded-xl shadow-lg shadow-orange-600/30 transition-all disabled:opacity-50">
                                            {{ isEditing ? 'Guardar Cambios' : 'Registrar Personal' }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </Transition>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- MODAL DE CONFIRMACIÓN -->
        <Teleport to="body">
            <Transition enter-active-class="ease-out duration-300" enter-from-class="opacity-0"
                enter-to-class="opacity-100" leave-active-class="ease-in duration-200" leave-from-class="opacity-100"
                leave-to-class="opacity-0">
                <div v-if="showConfirmModal" class="fixed inset-0 z-[200] overflow-y-auto">
                    <!-- Fondo oscuro -->
                    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="closeConfirm"></div>

                    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                        <Transition enter-active-class="ease-out duration-300"
                            enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                            leave-active-class="ease-in duration-200"
                            leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                            leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                            <div
                                class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200">
                                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                    <div class="sm:flex sm:items-start">
                                        <div
                                            class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-orange-100 sm:mx-0 sm:h-10 sm:w-10">
                                            <svg class="h-6 w-6 text-orange-600" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                        </div>
                                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                            <h3 class="text-lg font-bold leading-6 text-slate-900">
                                                Confirmar Acción
                                            </h3>
                                            <div class="mt-2">
                                                <p class="text-sm text-slate-500">
                                                    {{ confirmMessage }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 border-t border-slate-200">
                                    <button type="button"
                                        class="inline-flex w-full justify-center rounded-xl bg-orange-600 px-3 py-2 text-sm font-bold text-white shadow-sm hover:bg-orange-700 sm:ml-3 sm:w-auto transition-colors"
                                        @click="executeConfirm">
                                        Aceptar
                                    </button>
                                    <button type="button"
                                        class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-3 py-2 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors"
                                        @click="closeConfirm">
                                        Cancelar
                                    </button>
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <Transition enter-active-class="transform ease-out duration-300 transition"
            enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
            leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100"
            leave-to-class="opacity-0">
            <div v-if="showFlash"
                class="fixed bottom-4 right-4 z-[200] max-w-sm w-full bg-white shadow-2xl rounded-2xl pointer-events-auto border border-slate-100 overflow-hidden flex items-center p-4 gap-3">
                <div :class="toastType === 'success' ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600'"
                    class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center">
                    <svg v-if="toastType === 'success'" class="w-6 h-6" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </div>
                <p class="text-sm font-bold text-slate-800 flex-1">{{ toastMessage }}</p>
            </div>
        </Transition>

        <!-- MODAL: GUÍA DE IMPORTACIÓN ACTUALIZADO -->
        <Teleport to="body">
            <Transition enter-active-class="ease-out duration-300" enter-from-class="opacity-0"
                enter-to-class="opacity-100" leave-active-class="ease-in duration-200" leave-from-class="opacity-100"
                leave-to-class="opacity-0">
                <div v-if="showHelpModal" class="fixed inset-0 z-[200] overflow-y-auto">
                    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showHelpModal = false"></div>
                    <div class="flex min-h-full items-center justify-center p-4">
                        <div
                            class="relative bg-white rounded-2xl max-w-3xl w-full shadow-2xl border border-slate-200 overflow-hidden">

                            <!-- Header -->
                            <div class="p-6 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                                <div>
                                    <h3 class="text-xl font-black text-slate-800">Estructura del Excel</h3>
                                    <p class="text-xs text-slate-500">Asegúrate de que la primera fila contenga estos nombres exactos.</p>
                                </div>
                                <button @click="showHelpModal = false"
                                    class="text-slate-400 hover:text-slate-600 transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path d="M6 18L18 6M6 6l12 12" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Contenido -->
                            <div class="p-6">
                                <div class="overflow-x-auto rounded-xl border border-slate-200 mb-6">
                                    <table class="min-w-full divide-y divide-slate-200 text-xs">
                                        <thead class="bg-slate-100">
                                            <tr>
                                                <th class="px-4 py-3 text-left font-black text-slate-700 uppercase tracking-wider">Encabezado en Excel</th>
                                                <th class="px-4 py-3 text-left font-black text-slate-700 uppercase tracking-wider">Obligatorio</th>
                                                <th class="px-4 py-3 text-left font-black text-slate-700 uppercase tracking-wider">Ejemplo / Formato</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            <tr>
                                                <td class="px-4 py-2 font-mono font-bold text-blue-600">documento</td>
                                                <td class="px-4 py-2 text-rose-600 font-bold">SÍ</td>
                                                <td class="px-4 py-2 text-slate-600">70123456 (DNI o CE)</td>
                                            </tr>
                                            <tr>
                                                <td class="px-4 py-2 font-mono text-blue-600">tipo_documento</td>
                                                <td class="px-4 py-2 text-slate-400">No (Def: DNI)</td>
                                                <td class="px-4 py-2 text-slate-600">PASAPORTE</td>
                                            </tr>
                                            <tr>
                                                <td class="px-4 py-2 font-mono text-blue-600">nombres</td>
                                                <td class="px-4 py-2 text-slate-400">No</td>
                                                <td class="px-4 py-2 text-slate-600">JUAN ALBERTO</td>
                                            </tr>
                                            <tr>
                                                <td class="px-4 py-2 font-mono text-blue-600">apellidos</td>
                                                <td class="px-4 py-2 text-slate-400">No</td>
                                                <td class="px-4 py-2 text-slate-600">PEREZ ROJAS</td>
                                            </tr>
                                            <tr>
                                                <td class="px-4 py-2 font-mono text-blue-600">correo</td>
                                                <td class="px-4 py-2 text-slate-400">No</td>
                                                <td class="px-4 py-2 text-slate-600">juan@empresa.com</td>
                                            </tr>
                                            <tr>
                                                <td class="px-4 py-2 font-mono text-blue-600">cargo</td>
                                                <td class="px-4 py-2 text-slate-400">No</td>
                                                <td class="px-4 py-2 text-slate-600">MONTAJISTA</td>
                                            </tr>
                                            <tr>
                                                <td class="px-4 py-2 font-mono text-blue-600">ruc_empresa</td>
                                                <td class="px-4 py-2 text-slate-400">No</td>
                                                <td class="px-4 py-2 text-slate-600">20100200300</td>
                                            </tr>
                                            <tr>
                                                <td class="px-4 py-2 font-mono text-blue-600">nombre_empresa</td>
                                                <td class="px-4 py-2 text-slate-400">No</td>
                                                <td class="px-4 py-2 text-slate-600">CONTRATISTA GENERAL SAC</td>
                                            </tr>
                                            <tr class="bg-orange-50/50">
                                                <td class="px-4 py-2 font-mono text-orange-700 font-bold">aseguradora</td>
                                                <td class="px-4 py-2 text-slate-400">No</td>
                                                <td class="px-4 py-2 text-slate-600">RIMAC / PACIFICO</td>
                                            </tr>
                                            <tr class="bg-orange-50/50">
                                                <td class="px-4 py-2 font-mono text-orange-700 font-bold">poliza</td>
                                                <td class="px-4 py-2 text-slate-400">No</td>
                                                <td class="px-4 py-2 text-slate-600">SCTR-12345678</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Footer Info -->
                                <div class="flex flex-col md:flex-row gap-4 items-center justify-between bg-slate-50 p-4 rounded-xl border border-slate-200">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 bg-orange-100 rounded-lg">
                                            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path
                                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </div>
                                        <p class="text-[11px] text-slate-600 leading-tight">
                                            <span class="font-bold block text-slate-800">Nota sobre Actualizaciones:</span>
                                            Si el <span class="font-mono bg-slate-200 px-1">documento</span> ya existe en el sistema, los datos se <b>actualizarán</b> con la información del Excel.
                                        </p>
                                    </div>
                                    <button @click="descargarPlantilla"
                                        class="whitespace-nowrap px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-lg transition-all flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        Descargar Plantilla
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- MODAL: HISTORIAL DE ACCESOS -->
        <Teleport to="body">
            <Transition enter-active-class="ease-out duration-300" enter-from-class="opacity-0"
                enter-to-class="opacity-100" leave-active-class="ease-in duration-200" leave-from-class="opacity-100"
                leave-to-class="opacity-0">
                <div v-if="showHistorialModal" class="fixed inset-0 z-[200] overflow-y-auto">
                    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="closeHistorialModal"></div>
                    <div class="flex min-h-full items-center justify-center p-4">
                        <div
                            class="relative bg-white rounded-3xl max-w-lg w-full shadow-2xl border border-slate-200 overflow-hidden">

                            <!-- Header -->
                            <div class="p-6 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-orange-100 rounded-lg text-orange-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-tight">Historial de Accesos</h3>
                                        <p class="text-[11px] text-slate-500 font-bold">{{ selectedPersona?.nombres }} {{ selectedPersona?.apellidos }}</p>
                                    </div>
                                </div>
                                <button @click="closeHistorialModal" class="text-slate-400 hover:text-slate-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path d="M6 18L18 6M6 6l12 12" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Contenido del Historial -->
                            <div class="p-6 max-h-[400px] overflow-y-auto bg-white">

                                <!-- Estado Cargando -->
                                <div v-if="loadingHistorial"
                                    class="py-12 flex flex-col items-center justify-center gap-3">
                                    <div
                                        class="w-8 h-8 border-4 border-orange-500 border-t-transparent rounded-full animate-spin">
                                    </div>
                                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Cargando
                                        registros...</span>
                                </div>

                                <!-- Lista de Movimientos -->
                                <div v-else-if="historialData.length > 0"
                                    class="space-y-6 relative before:absolute before:inset-y-0 before:left-4 before:w-0.5 before:bg-slate-100">
                                    <div v-for="log in historialData" :key="log.id" class="relative pl-10">
                                        <!-- Circulito de la línea de tiempo -->
                                        <div :class="log.tipo_movimiento === 'INGRESO' ? 'bg-emerald-500 ring-emerald-100' : 'bg-blue-500 ring-blue-100'"
                                            class="absolute left-2 top-1 w-4 h-4 rounded-full ring-4 z-10"></div>

                                        <div class="flex flex-col">
                                            <div class="flex justify-between items-center">
                                                <span class="text-xs font-black text-slate-800 tracking-tight">{{ log.tipo_movimiento }}</span>
                                                <span class="text-[10px] font-mono font-bold text-slate-400">{{ log.fecha_hora }}</span>
                                            </div>
                                            <p class="text-[11px] text-slate-500 mt-0.5">Puerta: <span
                                                    class="text-slate-700 font-bold">{{ log.puerta_acceso }}</span></p>
                                            <p class="text-[10px] text-slate-400 italic">Operador: {{ log.usuario_seguridad }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Sin Registros -->
                                <div v-else class="py-12 text-center">
                                    <p class="text-xs font-bold text-slate-400 uppercase">No hay movimientos registrados aún.</p>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="p-4 bg-slate-50 border-t border-slate-100 flex justify-center">
                                <button @click="closeHistorialModal"
                                    class="text-[11px] font-black text-slate-500 uppercase tracking-widest hover:text-slate-700 transition-colors">Cerrar
                                    Historial</button>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AuthenticatedLayout>
</template>
