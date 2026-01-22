<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import colorbar from '@/Components/colorbar.vue';
import { router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import Dialog from 'primevue/dialog';
import FormValidacionDoc from './FormValidacionDoc.vue';
import FormInscription from './FormInscription.vue';
import FormPayment from './FormPayment.vue';
import Button from 'primevue/button';
import { useToast } from 'primevue/usetoast';
import Card from 'primevue/card';
import Stepper from 'primevue/stepper';
import StepList from 'primevue/steplist';
import StepPanels from 'primevue/steppanels';
import StepPanel from 'primevue/steppanel';
import Step from 'primevue/step';
import "../../../css/inscripciones.css";

const visible = ref(true);
const loading = ref(false);
const toast = useToast();
const bloqueoExtranjero = ref(false);
const categoriaIdActual = ref(null);
const uploading = ref(false);
const uploadProgress = ref(0);


const props = defineProps({
    title: String,
    categorias: Object,
    modal_texts: Object,
})

const formDataValidacionDoc = ref(null);
const formDataInscription = ref(null);
const formDataPayment = ref(null);
const data_persona = ref({});
const showRequisitosModal = ref(false); // Controla el modal
const tempResIns = ref(null);

const nacionalidadSeleccionada = ref(null);

const childFormValidacionDoc = ref();
const childFormInscription = ref(null);
const childFormPayment = ref(null);
const tipo_origen = ref(0);
const categoria_seleccionada = ref({}); // Esto debe empezar vacío

const resumen_dinamico = ref({
    total: 0,
    dias_seleccionados: [],
    requiere_doc: false,
    tiene_doc: false
});

const actualizarResumen = (datos) => {
    resumen_dinamico.value = { ...resumen_dinamico.value, ...datos };
};

// onMounted(() => {
//     window.addEventListener('beforeunload', handleBeforeUnload);
// });
// onUnmounted(() => {
//     // Es vital remover el evento para no afectar otras partes del sitio
//     window.removeEventListener('beforeunload', handleBeforeUnload);
// });

const handleBeforeUnload = (event) => {
    // Solo mostrar alerta si ya pasó al paso 2 o si ya ingresó nombres
    if (activeStep.value !== "1" || data_persona.value.nombres) {
        event.preventDefault();
        event.returnValue = '';
    }
};

// --- LÓGICA DE VALIDACIÓN COMPLETA EN INICIO.VUE ---
const validate = async (value) => {
    loading.value = true;
    switch (value) {
        case "Documento":
            const resDoc = await childFormValidacionDoc.value.getValidacionDoc();
            if (resDoc.validate) {
                // Guardamos DNI y TipoDoc aquí para usarlos en el siguiente paso
                data_persona.value = resDoc.formValidacionDoc;
                loading.value = false;
                return true;
            }
            break;

        case "Inscripcion":
            const resIns = await childFormInscription.value.getInscripcion();
            if (resIns.validate) {
                try {
                    const payload = new FormData();

                    // 1. PASAMOS TODOS LOS DATOS DE LA PERSONA (PASO 1)
                    // Esto incluye: tipo_doc, documento, nombres, pais, sexo, fecha_nacimiento, etc.
                    Object.keys(data_persona.value).forEach(key => {
                        payload.append(key, data_persona.value[key]);
                    });

                    // 2. PASAMOS LOS DATOS DE INSCRIPCIÓN/FACTURACIÓN (PASO 2)
                    Object.keys(resIns.formInscription).forEach(key => {
                        // Si el campo es el archivo, lo agregamos tal cual
                        if (key === 'uploadDocument') {
                            if (resIns.formInscription[key]) {
                                payload.append(key, resIns.formInscription[key]);
                            }
                        } else {
                            // Solo agregamos si no existe ya (para no duplicar datos del paso 1)
                            if (!payload.has(key)) {
                                payload.append(key, resIns.formInscription[key]);
                            }
                        }
                    });

                    const response = await axios.post('/pago/getform', payload, {
                        headers: { 'Content-Type': 'multipart/form-data' }
                    });

                    if (response.data.status && response.data.formulario) {
                        formDataPayment.value = response.data.formulario;
                        const cat = props.categorias.find(c => c.id == resIns.formInscription.selected_categoria);
                        if (cat) categoria_seleccionada.value = cat;
                        loading.value = false;
                        return true;
                    } else {
                        toast.add({ severity: 'error', summary: 'Error', detail: response.data.message });
                    }
                } catch (error) {
                    console.error("Error:", error);
                    toast.add({ severity: 'error', summary: 'Error', detail: 'Error al procesar el pago' });
                }
            }
            break;

    }
    loading.value = false;
    return false;
}
// --- FUNCIÓN MODIFICADA PARA RECIBIR EL ID ---
const seleccionarOrigen = (origen, id_numerico) => {
    nacionalidadSeleccionada.value = origen; // Guarda el texto 'peruano'/'extranjero'
    tipo_origen.value = id_numerico;         // Guarda el número 1 o 2
    visible.value = false;

    console.log("Nacionalidad:", origen);
    console.log("ID Numérico:", tipo_origen.value); // Para que veas en consola que se guardó
};

const goStart = () => {
    router.get(route('inscripcion.index'));
};

const activeStep = ref("1"); // Control del paso actual

// const handleInscripcionClick = async () => {
//     // 1. Validar el formulario del hijo primero
//     const resIns = await childFormInscription.value.getInscripcion();

//     if (resIns.validate) {
//         // Guardamos los datos para usarlos luego si acepta
//         tempResIns.value = resIns;
//         // 2. Mostrar el modal de requisitos
//         showRequisitosModal.value = true;
//     }
// };

// --- Busca esto en tu Inicio.vue ---
// const handleInscripcionClick = async () => {
//     // 1. Validar el formulario del hijo primero
//     const resIns = await childFormInscription.value.getInscripcion();

//     if (resIns.validate) {
//         // Guardamos los datos para usarlos luego
//         tempResIns.value = resIns;

//         // --- EL CAMBIO: ---
//         // showRequisitosModal.value = true; // COMENTA ESTA LÍNEA
//         confirmarYProcesar();               // AGREGA ESTA LÍNEA (Llama directo al proceso)

//         console.log("Puenteando modal: Saltando directo a confirmarYProcesar");
//     }
// };


const handleInscripcionClick = async () => {
    // ACTIVAMOS EL SPINNER
    loading.value = true;

    // 1. Validar el formulario del hijo primero
    const resIns = await childFormInscription.value.getInscripcion();

    if (resIns.validate) {
        // Guardamos los datos para usarlos luego
        tempResIns.value = resIns;

        // Llamamos al proceso de envío (esta función ya tiene su propio try/catch/finally)
        confirmarYProcesar();
    } else {
        // SI NO VALIDA, APAGAMOS EL SPINNER PARA QUE EL USUARIO CORRIJA
        loading.value = false;
    }
};

const confirmarYProcesar = async () => {
    showRequisitosModal.value = false;
    loading.value = true;

    try {
        const payload = new FormData();
        // Datos del paso 1
        Object.keys(data_persona.value).forEach(key => {
            payload.append(key, data_persona.value[key]);
        });

        // Datos del paso 2 guardados previamente
        Object.keys(tempResIns.value.formInscription).forEach(key => {
            if (!payload.has(key)) {
                payload.append(key, tempResIns.value.formInscription[key]);
            }
        });

        const response = await axios.post('/pago/getform', payload, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });


        if (response.data.status && response.data.formulario) {
            formDataPayment.value = response.data.formulario;
            activeStep.value = "3"; // Movemos al paso de pago manualmente
            loading.value = false;

            // --- ESTO ES LO QUE FALTA ---
            // Aseguramos que el padre sepa qué categoría es antes de mostrar el Paso 3
            const catId = tempResIns.value.formInscription.selected_categoria;
            const encontrada = props.categorias.find(c => c.id == catId);
            if (encontrada) {
                categoria_seleccionada.value = encontrada;
            }

            actualizarResumen({ total: tempResIns.value.total_final });
            // ----------------------------
        } else {
            toast.add({ severity: 'error', summary: 'Error', detail: response.data.message });
            loading.value = false;
        }
    } catch (error) {
        console.error("Error:", error);
        loading.value = false;
    }
};
// Modificamos el computed del total para que use el del formulario si existe
// const total_final = computed(() => {
//     // Si el formulario nos está enviando un total (por días), usamos ese.
//     // Si no, usamos el precio base de la categoría seleccionada.
//     return resumen_dinamico.value.total > 0
//         ? resumen_dinamico.value.total
//         : (categoria_seleccionada.value?.precio_disponible?.valor || '0.00');
// });

const total_final = computed(() => {
    return resumen_dinamico.value.total > 0
        ? resumen_dinamico.value.total
        : (categoria_seleccionada.value?.precio_disponible?.valor || '0.00');
});

onMounted(() => {
    // 1. Leer el ID de la URL (ej: ?category=5)
    const urlParams = new URLSearchParams(window.location.search);
    const categoryId = urlParams.get('category');

    categoriaIdActual.value = categoryId;

    // Lógica de bloqueo para categorías 35 y 29
    if (categoryId == '35' || categoryId == '29') {
        bloqueoExtranjero.value = true;
    }

    if (categoryId && props.categorias) {
        // 2. Buscar en la lista de categorías que mandó el controlador
        // Convertimos a array por si viene como objeto indexado de PHP
        const listaCategorias = Object.values(props.categorias);
        const encontrada = listaCategorias.find(c => c.id == categoryId);

        if (encontrada) {
            // 3. SE ASIGNA AL REF QUE USA EL RESUMEN
            categoria_seleccionada.value = encontrada;
            console.log("Resumen actualizado con:", encontrada.nombre_en);
        }
    }
});


const resetToNationality = () => {
    // 1. Volvemos a mostrar el modal de selección
    visible.value = true;

    // 2. Opcional: Limpiamos los datos que se hayan podido cargar
    tipo_origen.value = 0;
    nacionalidadSeleccionada.value = null;
    data_persona.value = {};

    // 3. Opcional: Aseguramos que el Stepper vuelva al paso 1
    activeStep.value = "1";
};



</script>

<template>
    <AppLayout title="Inscripciones" class="bg-gradient-wmc">


        <div class="px-3 mx-auto max-w-7xl md:px-6 lg:px-8 relative">

            <div id="titulo_inicial" class="mt-8 mb-8">
                <h1 class="text-3xl text-green-iimp font-bold mb-2 text-yellow-price">{{ props.title }}</h1>
                <colorbar class="block w-auto" />
            </div>

            <div class="mt-6 mb-6">
                <Stepper v-model:value="activeStep" class="w-full">
                    <StepList class="text-black-price bg-degradient">
                        <Step value="1">Data Validation</Step>
                        <Step value="2">Personal Details</Step>
                        <Step value="3">Payment Process</Step>
                    </StepList>

                    <StepPanels>
                        <StepPanel v-slot="{ activateCallback }" value="1"
                            class="rounded-2xl border-2 border-green-iimp bg-white-price shadow-wmc">
                            <FormValidacionDoc ref="childFormValidacionDoc" :tipo_origen="tipo_origen" />
                            <div class="flex p-6 justify-between items-center">
                                <Button label="Home" icon="pi pi-home" variant="text"
                                    class="p-button-secondary p-button-text font-bold text-gray-500 hover:text-green-iimp"
                                    @click="resetToNationality" />

                                <Button label="Validate" icon="pi pi-arrow-right" iconPos="right"
                                    class="bg-degradient border-rounded-full" :loading="loading"
                                    :disabled="childFormValidacionDoc?.esCategoriaDeSocio && childFormValidacionDoc?.hasSearched && !childFormValidacionDoc?.esSocio"
                                    @click="async () => {
                                        const isValid = await validate('Documento');

                                        // También ajustamos esta lógica para que deje pasar si NO es categoría de socio
                                        if (isValid) {
                                            if (childFormValidacionDoc?.esCategoriaDeSocio) {
                                                if (childFormValidacionDoc?.esSocio) activateCallback('2');
                                            } else {
                                                activateCallback('2');
                                            }
                                        }
                                    }" />
                            </div>
                        </StepPanel>

                        <StepPanel v-slot="{ activateCallback }" value="2"
                            class="rounded-2xl border-2 border-green-iimp bg-white shadow-wmc">
                            <FormInscription ref="childFormInscription" :data_persona="data_persona"
                                :categorias="props.categorias" />
                            <!-- <div class="flex justify-between p-6">
                                <Button label="Back" severity="secondary" icon="pi pi-arrow-left"
                                    @click="activateCallback('1')" />
                                <Button label="Register" icon="pi pi-arrow-right" iconPos="right" :loading="loading"
                                    @click="async () => {
                                        const success = await validate('Inscripcion');
                                        if (success) {
                                            activateCallback('3');
                                        }
                                    }" class="bg-green-iimp border-rounded-full" />
                            </div> -->
                            <div class="flex justify-between p-6">
                                <Button label="Back" severity="secondary" icon="pi pi-arrow-left"
                                    @click="activateCallback('1')" />

                                <!-- <Button label="Register" iconPos="right" icon="pi pi-arrow-right"
                                    @click="handleInscripcionClick" class="bg-degradient border-rounded-full" /> -->
                                <Button label="Register" iconPos="right" icon="pi pi-arrow-right" :loading="loading"
                                    @click="handleInscripcionClick" class="bg-degradient border-rounded-full" />
                            </div>
                        </StepPanel>

                        <StepPanel v-slot="{ activateCallback }" value="3"
                            class="rounded-2xl border-2 border-green-iimp bg-white shadow-wmc">
                            <FormPayment ref="childFormPayment" :data_persona="data_persona"
                                :formulario="formDataPayment" :categoria_seleccionada="categoria_seleccionada" />
                            <div class="flex justify-between p-6">
                                <Button label="Back" severity="secondary" icon="pi pi-arrow-left"
                                    @click="activateCallback('2')" />
                            </div>
                        </StepPanel>
                    </StepPanels>
                </Stepper>
            </div>

            <!-- <aside class="hidden xl:block absolute top-32 -right-64 w-60">
                <Card class="shadow-2xl border-t-4 border-yellow-price bg-white">
                    <template #title>
                        <div class="text-sm font-bold text-slate-700 flex items-center gap-2">
                            <i class="pi pi-shopping-cart text-green-iimp"></i> RESUMEN
                        </div>
                    </template>
<template #content>
                        <div class="flex flex-col gap-3">
                            <div v-if="categoria_seleccionada.id">
                                <span
                                    class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Categoría</span>
                                <p class="text-xs font-bold text-blue-900 uppercase">
                                    {{ categoria_seleccionada.nombre_en }}
                                </p>
                                <p class="text-[10px] text-slate-500 italic">
                                    {{ categoria_seleccionada.precio_disponible?.nombre_en }}
                                </p>
                            </div>

                            <div v-if="data_persona.nombres">
                                <span
                                    class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Participante</span>
                                <p class="text-xs font-semibold text-slate-700 leading-tight">
                                    {{ data_persona.nombres }} {{ data_persona.apellido_paterno }}
                                </p>
                            </div>

                            <div class="pt-2 border-t border-gray-100">
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-bold text-slate-600">TOTAL:</span>
                                    <span class="text-xl font-black text-green-iimp">
                                        {{ categoria_seleccionada.precio_disponible?.moneda?.simbolo || '$' }} {{
                                            total_final }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </template>
</Card>
</aside> -->
        </div>

        <Dialog v-model:visible="visible" modal :showHeader="false" :closable="false" :style="{ width: '900px' }"
            class="bg-transparent shadow-none border-none px-0" :pt="{
                mask: { class: 'bg-slate-900/90 backdrop-blur-md' },
                content: { class: 'bg-transparent px-0 py-0 border-none shadow-none' }
            }">

            <div class="bg-white rounded-3xl overflow-hidden shadow-2xl animate-fade-in-down px-0">

                <div
                    class="bg-gradient-to-r from-[#001e3d] via-[#002855] to-[#003366] px-8 py-8 border-b-4 border-yellow-500 flex items-center justify-between relative overflow-hidden">

                    <div
                        class="absolute inset-0 opacity-20 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')]">
                    </div>

                    <div
                        class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2">
                    </div>

                    <div class="relative z-10">
                        <div class="mb-3 animate-fade-in-up" style="animation-delay: 0.1s;">
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-yellow-500/10 border border-yellow-500/30 backdrop-blur-md">
                                <span class="relative flex h-2 w-2">
                                    <span
                                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-yellow-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-yellow-500"></span>
                                </span>
                                <span
                                    class="text-[10px] font-bold text-yellow-400 uppercase tracking-widest">Registration
                                    Open</span>
                            </span>
                        </div>

                        <h2
                            class=" text-2xl md:text-4xl text-transparent bg-clip-text bg-gradient-to-r from-yellow-200 via-yellow-400 to-yellow-600">
                            World Mining Congress 2026

                        </h2>
                    </div>
                </div>

                <div class="p-8 md:p-12 bg-slate-50 px-0">
                    <div v-if="bloqueoExtranjero"
                        class="p-4 mb-6 rounded-xl bg-amber-50 border border-amber-200 flex items-start gap-3 animate-fade-in-up">
                        <i class="pi pi-info-circle text-amber-600 text-xl mt-0.5"></i>
                        <p class="text-sm text-amber-800 leading-relaxed">
                            The <b>International</b> option for Author Members or Member Participants is not available
                            through
                            this portal. If you are an international attendee and an active member, please contact
                            <a href="mailto:asociados@iimp.org.pe"
                                class="font-bold underline hover:text-amber-900">asociados@iimp.org.pe</a> for
                            assistance.
                        </p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">


                        <button @click="seleccionarOrigen('peruano', 1)"
                            class="group relative h-auto rounded-2xl bg-white border border-slate-200 shadow-lg hover:shadow-2xl hover:shadow-red-900/20 transition-all duration-300 flex flex-col overflow-hidden hover:-translate-y-2">

                            <div class="h-1 w-full bg-red-600"></div>

                            <div class="p-8 flex flex-col items-center text-center h-full">

                                <div
                                    class="w-24 h-24 mb-6 relative drop-shadow-lg group-hover:scale-110 transition-transform duration-300">
                                    <svg viewBox="0 0 300 200" class="w-full h-full rounded-lg shadow-sm">
                                        <rect width="300" height="200" fill="#ffffff" stroke="#e2e8f0"
                                            stroke-width="2" />
                                        <rect width="100" height="200" fill="#D91023" />
                                        <rect x="200" width="100" height="200" fill="#D91023" />
                                    </svg>
                                    <div class="absolute inset-0 bg-red-500/20 blur-2xl -z-10 rounded-full"></div>
                                </div>

                                <h3
                                    class="text-2xl font-black text-slate-800 group-hover:text-red-700 transition-colors uppercase">
                                    National
                                </h3>
                                <p class="text-sm text-slate-500 mt-2 mb-8 leading-relaxed font-medium">
                                    Peruvian citizen or resident with DNI.
                                </p>

                                <div class="mt-auto w-full">
                                    <span
                                        class="block w-full py-3 px-4 rounded-xl bg-gradient-to-r from-red-700 to-red-600 text-white font-bold text-sm tracking-wider uppercase shadow-md group-hover:shadow-lg group-hover:from-red-600 group-hover:to-red-500 transition-all flex items-center justify-center gap-2">
                                        Continue Purchase <i class="pi pi-arrow-right text-xs"></i>
                                    </span>
                                </div>
                            </div>
                        </button>

                        <button @click="!bloqueoExtranjero && seleccionarOrigen('extranjero', 2)"
                            :disabled="bloqueoExtranjero" :class="[
                                'group relative h-auto rounded-2xl bg-white border border-slate-200 shadow-lg transition-all duration-300 flex flex-col overflow-hidden',
                                bloqueoExtranjero
                                    ? 'opacity-60 cursor-not-allowed grayscale'
                                    : 'hover:shadow-2xl hover:shadow-blue-900/20 hover:-translate-y-2'
                            ]">

                            <div class="h-1 w-full bg-blue-600"></div>

                            <div class="p-8 flex flex-col items-center text-center h-full">

                                <div
                                    class="w-24 h-24 mb-6 relative drop-shadow-lg group-hover:scale-110 group-hover:rotate-12 transition-all duration-500">
                                    <svg viewBox="0 0 24 24" fill="none" class="w-full h-full text-blue-600">
                                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"
                                            fill="#eff6ff" />
                                        <path d="M2.3 12H21.7" stroke="currentColor" stroke-width="1.5"
                                            stroke-linecap="round" />
                                        <path
                                            d="M12 2.3C14.5 5 16 8.5 16 12C16 15.5 14.5 19 12 21.7C9.5 19 8 15.5 8 12C8 8.5 9.5 5 12 2.3Z"
                                            stroke="currentColor" stroke-width="1.5" fill="#dbeafe" />
                                    </svg>
                                    <div class="absolute inset-0 bg-blue-500/20 blur-2xl -z-10 rounded-full"></div>
                                </div>

                                <h3
                                    class="text-2xl font-black text-slate-800 group-hover:text-blue-700 transition-colors uppercase">
                                    International
                                </h3>
                                <p class="text-sm text-slate-500 mt-2 mb-8 leading-relaxed font-medium">
                                    Joining from abroad (Foreigner).
                                </p>

                                <div class="mt-auto w-full">
                                    <span :class="[
                                        'block w-full py-3 px-4 rounded-xl text-white font-bold text-sm tracking-wider uppercase shadow-md transition-all flex items-center justify-center gap-2',
                                        bloqueoExtranjero
                                            ? 'bg-gray-400'
                                            : 'bg-gradient-to-r from-[#002855] to-blue-700 group-hover:from-blue-800 group-hover:to-blue-600'
                                    ]">
                                        {{ bloqueoExtranjero ? 'Not Available' : 'Continue Purchase' }}
                                        <i v-if="!bloqueoExtranjero" class="pi pi-arrow-right text-xs"></i>
                                    </span>
                                </div>
                            </div>
                        </button>

                    </div>
                </div>

                <div class="bg-gray-50 p-6 border-t border-slate-200 text-center">
                    <button @click="goStart"
                        class="group flex items-center justify-center gap-2 text-xs text-slate-400 font-bold uppercase tracking-widest hover:text-red-500 transition-colors mx-auto">
                        <i class="pi pi-times-circle text-lg group-hover:scale-110 transition-transform"></i>
                        Cancel Process
                    </button>
                </div>

            </div>
        </Dialog>

        <Dialog v-model:visible="uploading" modal :closable="false" :showHeader="false" :style="{ width: '350px' }">
            <div class="flex flex-col items-center p-6 text-center">
                <div class="mb-4 text-green-iimp relative">
                    <i class="pi pi-cloud-upload text-5xl animate-bounce"></i>
                </div>
                <h3 class="text-xl font-black text-blue-900 mb-2">Uploading Document</h3>
                <p class="text-sm text-gray-500 mb-6">Please wait while we process your file...</p>

                <div class="w-full bg-gray-100 rounded-full h-4 mb-2 overflow-hidden border border-gray-200">
                    <div class="bg-gradient-to-r from-green-500 to-green-300 h-full transition-all duration-300"
                        :style="{ width: uploadProgress + '%' }"></div>
                </div>
                <span class="text-xs font-bold text-green-600">{{ uploadProgress }}% Completed</span>
            </div>
        </Dialog>

        <Dialog v-if="false" v-model:visible="showRequisitosModal" modal header="Requirements and Conditions"
            :style="{ width: '50vw' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
            <div class="flex flex-col gap-4">
                <p class="text-gray-600">Please review the requirements before proceeding to payment.</p>

                <div class="w-full h-[400px] border rounded overflow-hidden">
                    <iframe src="/documents/reglamento.pdf" class="w-full h-full" frameborder="0">
                    </iframe>
                </div>

                <div class="flex justify-end gap-3 mt-4">
                    <Button label="Cancel" icon="pi pi-times" @click="showRequisitosModal = false"
                        class="p-button-text p-button-secondary" />
                    <Button label="I Accept & Continue to Payment" icon="pi pi-check" @click="confirmarYProcesar"
                        class="p-button-success" :loading="loading" />
                </div>
            </div>
        </Dialog>
    </AppLayout>
</template>

<style scoped>
.animate-fade-in-down {
    animation: fadeInDown 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-40px) scale(0.95);
    }

    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}
</style>
