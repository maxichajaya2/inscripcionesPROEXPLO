<script setup>
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Divider from 'primevue/divider';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import Calendar from 'primevue/calendar';
import Checkbox from 'primevue/checkbox';
import RadioButton from 'primevue/radiobutton';
import Card from 'primevue/card';
import InputGroup from 'primevue/inputgroup';
import { ref, onMounted, computed, watch, nextTick } from 'vue'; // Agregado nextTick
import { useForm } from 'vee-validate';
import * as yup from 'yup';
import { usePage, router } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import Functions from '@/Functions';
import FileUpload from 'primevue/fileupload';
import InputGroupAddon from 'primevue/inputgroupaddon';

import "../../../css/inscripciones.css";

const page = usePage();
const toast = useToast();
const props = defineProps({
    data_persona: Object,
    categorias: Object,
    saved_values: Object
});

const es_socio = ref(false);
const loading_doc = ref(false);
const show_days = ref(false);
const show_document = ref(false);
const upload_instruction = ref('');
const total = ref(0);
const src = ref(null);
const block_direction = ref(false);
const fileErrors = ref([]);
const maxSize = 6291456;
const allowedTypes = ['application/pdf', 'image/png', 'image/jpg', 'image/jpeg'];
// Agregamos un estado para controlar si el usuario puede editar manualmente
const isEditingBilling = ref(false);

let current_price = 0;
const billingMessage = ref(null);
const tipoDocumentoPago = computed(() => page.props.general.tipoDocumentoPago);
const tipoDocumento = computed(() => page.props.general.tipDocEmp)
const reglamento_inscripciones = computed(() => usePage().props.general.reglamento_inscripciones);

const departamentos = ref();
const days = { 'mar': 'Tuesday', 'mie': 'Wednesday', 'jue': 'Thursday' };
const current_days = { 'lun': false, 'mar': false, 'mie': false, 'jue': false, 'vie': false };

const formManualErrors = ref({ reglamento: null, total: null, uploadDocument: null });

const { defineField, errors, setValues, values } = useForm({
    validationSchema: yup.object({
        tipoDocumentoEmpresa: yup.mixed().required('Company document type is required'),
        documentoEmpresa: yup.string().required('Document number is required'),
        nombres: yup.string().required('Name is required'),
        apellido_paterno: yup.string().required('Last name is required'),
        fecha_nacimiento: yup.mixed().required('Date of birth is required'),
        sexo: yup.mixed().required('Gender is required'),
        correo: yup.string().required('Email is required').email('Enter a valid email'),
        celular: yup.string().required('Mobile phone is required'),
        pais: yup.mixed().required('Country is required'),
        direccionPersona: yup.string().required('Address is required'),
        empresa: yup.string().required('Company is required'),
        credencial: yup.string().required('Credential name is required'),
        razonSocial: yup.string().required('Business name is required'),
        direccionEmpresa: yup.string().required('Company address is required'),
        responsable: yup.string().required('Responsible party name is required'),
        correo_facturador: yup.string().required('Billing email is required').email('Enter a valid email'),
        selectTipoPago: yup.string().required('Payment type is required'),
        selectTipoDocPago: yup.string().required('Payment document type is required'),
        reglamento: yup.string().required('You must accept the Regulations'),
    })
})

const [nombres] = defineField('nombres');
const [apellido_paterno] = defineField('apellido_paterno');
const [documentoEmpresa] = defineField('documentoEmpresa');
const [razonSocial] = defineField('razonSocial');
const [responsable] = defineField('responsable');
const [correo_facturador] = defineField('correo_facturador');
const [tipoDocumentoEmpresa] = defineField('tipoDocumentoEmpresa');
const [direccionEmpresa] = defineField('direccionEmpresa');
const [selectTipoPago] = defineField('selectTipoPago');
const [reglamento] = defineField('reglamento');
const [selectTipoDocPago] = defineField('selectTipoDocPago');
const [selected_categoria, selected_categoriaAttrs] = defineField('selected_categoria');
const [selectedDays, selectedDaysAttrs] = defineField('selectedDays');
const [uploadDocument] = defineField('uploadDocument');
const [pais] = defineField('pais');


const is_category_fixed = ref(false);

// --- LÓGICA PRINCIPAL ---
function changeCategory(id, precio) {
    if (!id) return;
    current_price = precio;
    const categoria = props.categorias.find(c => c.id === id);

    if (categoria) {
        nextTick(() => {
            // Documentos
            show_document.value = Boolean(categoria.requiere_documento);
            if (show_document.value) {
                const nombre = categoria.nombre_en.toUpperCase();
                if (nombre.includes('STUDENT') || nombre.includes('ESTUDIANTE')) {
                    upload_instruction.value = "Rate applicable to undergraduate students, presentation of enrollment proof required.";
                } else if (nombre.includes('FACULTY') || nombre.includes('DOCENTE')) {
                    upload_instruction.value = "Special rate for faculty members, valid proof required.";
                } else {
                    upload_instruction.value = "Please upload the required document for this category.";
                }
            }

            // Días
            const nomEs = categoria.nombre_es.toUpperCase();
            const nomEn = categoria.nombre_en.toUpperCase();

            if (nomEs.includes("DIA") || nomEn.includes("DAY")) {
                show_days.value = true;
                total.value = total.value > 0 ? total.value : 0;
            } else {
                show_days.value = false;
                total.value = precio;
            }
        });
    }
}


onMounted(() => {
    // Configuraciones iniciales
    tipoDocumentoEmpresa.value = 1;
    selectTipoDocPago.value = 2;
    selectTipoPago.value = 3;

    // 1. Si los datos ya están presentes al montar, llenar facturación
    if (props.data_persona?.persona) {
        fillBillingData(props.data_persona.persona);
        es_socio.value = props.data_persona.persona.es_socio;
        loadDepartamentos();
    }

    // 2. Lógica de URL y categorías
    const urlParams = new URLSearchParams(window.location.search);
    const categoryIdFromUrl = urlParams.get('category');
    let targetCategoryId = null;

    if (props.saved_values && props.saved_values.selected_categoria) {
        targetCategoryId = props.saved_values.selected_categoria;
        setValues(props.saved_values);
    } else if (categoryIdFromUrl) {
        targetCategoryId = parseInt(categoryIdFromUrl);
        is_category_fixed.value = true;
    }

    if (targetCategoryId) {
        selected_categoria.value = targetCategoryId;
        const cat = props.categorias.find(c => c.id === targetCategoryId);
        setTimeout(() => {
            changeCategory(targetCategoryId, cat?.precio_disponible?.valor || 0);
        }, 150);
    }
});

watch(() => props.data_persona, (newVal) => {
    if (newVal && newVal.persona) {
        fillBillingData(newVal.persona);
    }
}, { immediate: true, deep: true });

// Watcher para asegurar que si el valor de la categoría cambia, la UI responda (Días/Documentos)
watch(selected_categoria, (newId) => {
    const cat = props.categorias.find(c => c.id === newId);
    if (cat) changeCategory(newId, cat.precio_disponible?.valor || 0);
});
const loadDepartamentos = async () => {
    departamentos.value = await Functions.loadDepartamentos(pais.value);
}

const getEmpresaData = async () => {
    loading_doc.value = true;
    try {
        const empresaData = await Functions.getEmpresaData(documentoEmpresa.value, tipoDocumentoEmpresa.value);
        if (empresaData?.empresa) {
            razonSocial.value = empresaData.empresa.nombre;
            direccionEmpresa.value = empresaData.empresa.direccionEmpresa;
            billingMessage.value = { type: 'success', text: 'Data found successfully.' };
        }
    } catch (e) {
        billingMessage.value = { type: 'error', text: 'Error searching for company data.' };
    } finally {
        loading_doc.value = false;
    }
}

const onFileSelect = (event) => {
    const file = event.files[0];
    if (file.size > maxSize) return;
    uploadDocument.value = file;
    const reader = new FileReader();
    reader.onload = (e) => {
        src.value = file.type === "application/pdf" ? '/images/pdf-file-document.png' : e.target.result;
    };
    reader.readAsDataURL(file);
}

function selectDays(id) {
    current_days[id] = !current_days[id];
    let count = Object.values(current_days).filter(v => v).length;
    total.value = count * current_price;
}

function getInscripcion() {
    formManualErrors.value = { reglamento: null, total: null, uploadDocument: null };
    let hasError = false;
    if (reglamento.value !== true) { formManualErrors.value.reglamento = "Required"; hasError = true; }
    if (total.value <= 0) { formManualErrors.value.total = "Select days"; hasError = true; }
    if (show_document.value && !uploadDocument.value) { formManualErrors.value.uploadDocument = "Upload file"; hasError = true; }

    return hasError ? { validate: false } : { validate: true, formInscription: values };
}

function setTipoDocPago() {
    if (tipoDocumentoEmpresa.value == 2) {
        selectTipoDocPago.value = 1;
        block_direction.value = true;
    } else {
        selectTipoDocPago.value = 2;
        block_direction.value = false;
    }
}

const onlyNumberKey = (event) => {
    const charCode = event.which ? event.which : event.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57)) event.preventDefault();
}

const enableManualEdit = () => {
    isEditingBilling.value = true;
    block_direction.value = false; // Desbloqueamos dirección para edición manual
    toast.add({
        severity: 'info',
        summary: 'Manual Edit Enabled',
        detail: 'You can now modify the billing information fields.',
        life: 3000
    });
};

const fillBillingData = (p) => {
    if (!p) return;

    console.log("--- DEBUG LLENADO FACTURACIÓN ---");
    console.log("Data cruda recibida:", p);
    console.log("Documento capturado:", p.documento);
    console.log("Tipo Doc capturado:", p.tipo_doc || p.id_tipo_documento);

    const nombreCompleto = `${p.nombres || ''} ${p.apellido_paterno || ''}`.trim();
    const docTipo = p.id_tipo_documento || p.tipo_doc || 1;
    const docNum = p.documento || '';

    tipoDocumentoEmpresa.value = docTipo;
    documentoEmpresa.value = docNum;
    razonSocial.value = nombreCompleto;
    direccionEmpresa.value = p.direccionPersona || p.direccion || '';
    responsable.value = nombreCompleto;
    correo_facturador.value = p.correo || '';

    setValues({
        tipoDocumentoEmpresa: docTipo,
        documentoEmpresa: docNum,
        razonSocial: nombreCompleto,
        direccionEmpresa: direccionEmpresa.value,
        responsable: nombreCompleto,
        correo_facturador: p.correo || '',
        selectTipoDocPago: docTipo == 2 ? 1 : 2,
        selectTipoPago: 3
    });

    block_direction.value = (docTipo == 2);
};

watch(() => props.data_persona, (newVal) => {
    if (newVal) {
        // Intentamos con newVal.persona o con newVal directamente
        const data = newVal.persona ? newVal.persona : newVal;
        fillBillingData(data);
    }
}, { immediate: true, deep: true });

// const filteredDocTypes = computed(() => {
//     // Verificamos si es peruano por ID de país o por texto de nacionalidad
//     const esPeruano = props.data_persona?.persona?.id_pais == 1 ||
//         props.data_persona?.persona?.nacionalidad?.toLowerCase() === 'peruano';

//     if (esPeruano && tipoDocumento.value) {
//         // Filtramos para mostrar solo DNI (1) y RUC (2)
//         return tipoDocumento.value.filter(d => d.id == 1 || d.id == 2);
//     }
//     return tipoDocumento.value;
// });

const filteredDocTypes = computed(() => {
    const p = props.data_persona?.persona || props.data_persona;

    // --- LOGS DE CONTROL ---
    console.log("--- DEBUG NACIONALIDAD ---");
    console.log("ID País recibido:", p?.pais);
    console.log("Tipo Doc recibido:", p?.tipo_doc);

    // LÓGICA CORREGIDA:
    // Es peruano si el país es 1
    // O si ya trae un tipo_doc 1 (DNI) o 2 (RUC) aunque el ID de país diga otra cosa
    const esPeruano = p?.pais == 1 ||
                      p?.id_pais == 1 ||
                      p?.tipo_doc == 1 ||
                      p?.tipo_doc == 2 ||
                      p?.nacionalidad?.toLowerCase() === 'peruano';

    console.log("¿Es detectado como Peruano?:", esPeruano);

    if (!tipoDocumento.value) return [];

    if (esPeruano) {
        // Retorna SOLO DNI (1) y RUC (2)
        const filtrados = tipoDocumento.value.filter(d => d.id == 1 || d.id == 2);
        console.log("Documentos para Peruano:", filtrados);
        return filtrados;
    } else {
        // Retorna PASAPORTE, CE, etc. (quita DNI y RUC)
        const filtrados = tipoDocumento.value.filter(d => d.id != 1 && d.id != 2);
        console.log("Documentos para Extranjero:", filtrados);
        return filtrados;
    }
});

defineExpose({ getInscripcion });
</script>

<template>

    <div class="gap-6 p-6 w-full justify-around overflow-visible">

        <!--          CATEGORIAS                      -->
        <!-- ======================================== -->
        <div class="text-green-iimp font-bold p-4">
            <Card class="mt-5 overflow-hidden shadow-lg border border-gray-200">
                <template #header>
                    <div
                        class="w-full py-3 text-xl font-bold text-center bg-lightblue-wmc border-blue-wmc text-blue-900">
                        Category Details
                    </div>
                </template>

                <template #content>
                    <div class="px-2">

                        <div v-if="is_category_fixed"
                            class="w-full p-4 bg-blue-50 border border-blue-200 rounded-xl shadow-sm flex justify-between items-center">
                            <div class="flex flex-col">
                                <span class="text-[10px] uppercase text-blue-400 font-black tracking-widest">Selected
                                    Profile</span>
                                <h4 class="text-lg font-bold text-blue-900 leading-tight">
                                    {{categorias.find(c => c.id === selected_categoria)?.nombre_en}}
                                </h4>
                            </div>
                            <div class="text-right">
                                <p class="text-yellow-price font-black text-xl">
                                    USD {{categorias.find(c => c.id === selected_categoria)?.precio_disponible?.valor
                                        || '0.00'}}
                                </p>
                            </div>
                        </div>

                        <div v-else>
                            <div v-for="(categoria) in categorias" :key="categoria.id"
                                class="w-full border-b border-gray-100 last:border-0 py-3 px-3 rounded-lg transition-colors duration-200"
                                :class="{
                                    'bg-blue-50 border border-blue-200 shadow-sm': selected_categoria === categoria.id,
                                    'hover:bg-gray-50': selected_categoria !== categoria.id
                                }">
                                <div class="flex items-start w-full">
                                    <div class="flex-none pt-1">
                                        <RadioButton v-model="selected_categoria" v-bind="selected_categoriaAttrs"
                                            name="selected_categoria" :value='categoria.id' class="radio-green-iimp"
                                            @click="changeCategory(categoria.id, categoria.precio_disponible.valor)" />
                                    </div>
                                    <div class="flex flex-col sm:flex-row sm:justify-between w-full pl-3 cursor-pointer"
                                        @click="changeCategory(categoria.id, categoria.precio_disponible.valor)">
                                        <label
                                            class="text-sm sm:text-base text-gray-700 leading-tight mb-1 sm:mb-0 cursor-pointer"
                                            :class="{ 'font-bold text-blue-900': selected_categoria === categoria.id }">
                                            {{ es_socio || categoria.condicion == 'NS' ? categoria.nombre_es :
                                                categoria.nombre_en }}
                                        </label>
                                        <p
                                            class="text-yellow-price font-bold text-sm sm:text-base whitespace-nowrap sm:pl-4">
                                            USD {{ categoria.precio_disponible?.valor ?? '0.00' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <Card v-if="show_days" class="mt-6 border border-dashed border-blue-300 bg-blue-50/30">
                        <template #content>
                            <div v-if="formManualErrors.total"
                                class="mb-4 flex items-center gap-3 rounded border-l-4 border-red-500 bg-red-50 px-4 py-2 text-red-800 shadow-sm">
                                <i class="pi pi-times-circle"></i>
                                <span class="text-xs font-bold">{{ formManualErrors.total }}</span>
                            </div>

                            <p class="text-sm text-blue-800 font-bold mb-4 text-center">
                                <i class="pi pi-calendar-plus mr-2"></i>Select the specific days of attendance:
                            </p>

                            <div class="flex justify-around flex-wrap gap-4">
                                <div v-for="(day, key) in days" :key="key" class="flex items-center">
                                    <Checkbox :inputId="day" :value="key" v-model="selectedDays"
                                        v-bind="selectedDaysAttrs" name="selectedDays" @click="selectDays(key)" />
                                    <label :for="day" class="pl-2 text-sm text-gray-700 font-semibold cursor-pointer">{{
                                        day }}</label>
                                </div>
                            </div>

                            <div class="flex justify-center mt-6 pt-4 border-t border-blue-200">
                                <div class="text-blue-900 font-black flex items-center gap-4">
                                    <span class="text-sm uppercase tracking-wider">Subtotal:</span>
                                    <span class="text-2xl text-yellow-price">USD {{ total }}</span>
                                </div>
                            </div>
                        </template>
                    </Card>

                    <Card v-if="show_document" class="mt-6 border border-dashed border-green-300">
                        <template #content>
                            <div v-if="upload_instruction"
                                class="mb-4 p-4 bg-blue-50 border-l-4 border-blue-500 text-blue-700">
                                <p class="text-sm font-bold">Requirement:</p>
                                <p class="text-sm">{{ upload_instruction }}</p>
                            </div>

                            <div class="flex justify-center mb-4 w-full">
                                <img v-if="src" :src="src" alt="Preview"
                                    class="shadow-md rounded-lg border border-gray-200 max-w-[200px] max-h-[200px] object-contain" />
                            </div>

                            <div class="flex flex-col items-center justify-center w-full">
                                <div v-if="formManualErrors.uploadDocument"
                                    class="w-full mb-4 flex items-center gap-3 rounded border-l-4 border-red-500 bg-red-50 px-4 py-2 text-red-800 shadow-sm">
                                    <i class="pi pi-times-circle"></i>
                                    <span class="text-xs font-bold">{{ formManualErrors.uploadDocument }}</span>
                                </div>

                                <FileUpload ref="fileupload" mode="basic"
                                    class="p-button-outlined text-green-iimp mx-auto" :auto="true" customUpload
                                    :chooseLabel="'Upload Document'" @select="onFileSelect" name="uploadDocument" />

                                <small class="text-slate-500 mt-3 text-center block text-xs">
                                    Accepted: PDF, JPG, PNG (Max 6MB)
                                </small>
                            </div>
                        </template>
                    </Card>

                </template>
            </Card>
        </div>
        <!--         DATOS DE FACTURACION             -->
        <!-- ======================================== -->
        <div class="text-green-iimp font-bold p-4">

            <Card class="mt-5 overflow-hidden">
                <template #header>
                    <div class="w-full py-3 text-xl font-bold text-center bg-lightblue-wmc border-blue-wmc">
                        Billing Information
                    </div>
                </template>

                <template #content>
                    <div class="flex justify-end px-6 mb-2">
                        <Button v-if="!isEditingBilling" icon="pi pi-pencil" label="Edit Info"
                            class="p-button-text p-button-sm text-blue-600" @click="enableManualEdit" />
                        <Button v-else icon="pi pi-lock" label="Finish Editing"
                            class="p-button-text p-button-sm text-green-600" @click="isEditingBilling = false" />
                    </div>

                    <div class="grid gap-6 m-6 md:grid-cols-2">
                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="col-span-3 sm:col-span-1">
                                <label class="block mb-1">Document Type <span class="text-red-600">*</span></label>
                                <Select v-model="tipoDocumentoEmpresa" :options="filteredDocTypes" optionLabel="name_en"
                                    optionValue="id" :disabled="!isEditingBilling" class="w-full border-green-iimp"
                                    @change="setTipoDocPago" />
                            </div>

                            <div class="col-span-3 sm:col-span-1">
                                <label class="block mb-1">Document Number <span class="text-red-600">*</span></label>
                                <InputGroup>
                                    <InputText v-model="documentoEmpresa" :readonly="!isEditingBilling"
                                        class="border-green-iimp" @keypress="onlyNumberKey" />
                                    <Button icon="pi pi-search" class="bg-green-iimp"
                                        :disabled="!isEditingBilling && tipoDocumentoEmpresa != 2"
                                        @click="getEmpresaData" />
                                </InputGroup>
                            </div>
                        </div>

                        <div class="w-full sm:col-span-1">
                            <label class="block mb-1">Business Name / Full Name <span
                                    class="text-red-600">*</span></label>
                            <InputText v-model="razonSocial" class="w-full border-green-iimp"
                                :readonly="!isEditingBilling || block_direction" />
                        </div>
                    </div>

                    <div class="grid gap-6 m-6 md:grid-cols-2">
                        <div class="w-full sm:col-span-1">
                            <label class="block mb-1">Address <span class="text-red-600">*</span></label>
                            <InputText v-model="direccionEmpresa" class="w-full border-green-iimp"
                                :readonly="!isEditingBilling || block_direction" />
                        </div>

                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="w-full sm:col-span-1">
                                <label class="block mb-1">Billing Contact <span class="text-red-600">*</span></label>
                                <InputText v-model="responsable" :readonly="!isEditingBilling"
                                    class="w-full border-green-iimp" />
                            </div>

                            <div class="w-full sm:col-span-1">
                                <label class="block mb-1">Billing Email <span class="text-red-600">*</span></label>
                                <InputText v-model="correo_facturador" :readonly="!isEditingBilling"
                                    class="w-full border-green-iimp" />
                            </div>
                        </div>
                    </div>
                </template>
            </Card>
        </div>
        <!-- <div class="text-green-iimp font-bold p-4">
            <Card class="mt-5 overflow-hidden shadow-lg border border-gray-200">

                <template #header>
                    <div class="w-full py-3 text-xl font-bold text-center bg-lightblue-wmc border-blue-wmc">
                        Terms and Conditions
                    </div>
                </template>

                <template #content>
                    <div v-if="formManualErrors.reglamento"
                        class="mb-4 flex items-center gap-3 rounded border-l-4 border-red-500 bg-red-50 px-4 py-2 text-red-800 shadow-sm animate-fade-in">
                        <i class="pi pi-times-circle"></i>
                        <span class="text-xs font-bold">{{ formManualErrors.reglamento }}</span>
                    </div>
                    <div
                        class="mb-6 p-4 bg-blue-50 border-l-4 border-blue-500 text-blue-800 font-normal text-sm text-justify rounded-r">
                        <p>
                            It is mandatory to read and accept the Terms and Conditions to proceed with the registration
                            process.
                            Please mark the checkbox below to confirm your agreement. We appreciate your participation.
                        </p>
                    </div>

                    <div class="flex flex-col items-center justify-center">
                        <div class="flex items-center">

                            <Checkbox :binary="true" v-model="reglamento" v-bind="reglamentoAttrs" name="reglamento" />
                            <a :href="reglamento_inscripciones" target="_blank" rel="noopener noreferrer"
                                title="Ver reglamento">
                                <label for="reglamento" class="pl-2 cursor-pointer"> I have read and accept the Terms
                                    and Conditions of
                                    Participation<span class="font-normal text-red-600">*</span></label>
                            </a>
                        </div>

                        <span class="font-normal text-red-600 text-sm mt-2">{{ errors.terminos }}</span>
                    </div>
                </template>
            </Card>
        </div> -->

    </div>

    <!--      MODAL DE TERMINOS Y CONDICIONES     -->
    <!-- ======================================== -->
    <!-- <Dialog v-model:visible="visible" modal :style="{ border: 'none' }"
        class="modal-green max-w-[750px] modal-custom-scroll m-[5px]"
        :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
        <div class="modal-head-title font-bold">
            <p>TÉRMINOS Y CONDICIONES DE PARTICIPACIÓN</p>
            <p>PERUMIN 37 CONVENCIÓN MINERA</p>
        </div>
        <div class="pt-[30px] pr-[30px] pl-[30px] pb-[20px] modal-custom-scroll">
            <ul class="mt-5 mb-5 ml-10 font'bold list-decimal">
                <li class="text-justify mb-4">Al comprar una entrada e ingresar al evento, el comprador declara haber
                    leído,
                    comprendido y aceptado el Reglamento de Inscripciones publicado en la
                    página web oficial del evento, así como todos los presentes términos
                    establecidos.</li>
                <li class="text-justify mb-4">Se autoriza al IIMP a usar gratuitamente la imagen captada del asistente
                    durante el evento, sin límite de tiempo ni territorio.</li>
                <li class="text-justify mb-4">Los datos personales serán tratados conforme a la ley peruana para fines
                    administrativos, de seguridad, estadísticos y de comunicación.</li>
                <li class="text-justify mb-4">Para el ingreso y permanencia, se requiere entrada válida y documento de
                    identidad. No se permite el ingreso con objetos peligrosos, drogas, armas ni
                    alcohol externo.</li>
                <li class="text-justify mb-4">Prohibido el uso de drones sin autorización expresa del IIMP; se debe
                    cumplir
                    con la normativa aérea vigente.</li>
                <li class="text-justify mb-4">El IIMP no se responsabiliza por pérdidas, robos o accidentes, salvo en
                    casos
                    de negligencia o dolo.</li>
                <li class="text-justify mb-4">No se permite la reventa no autorizada de entradas; el IIMP no responde
                    por
                    boletos comprados fuera de canales oficiales.</li>
                <li class="text-justify mb-4">El uso de marcas, logos o contenidos del evento sin permiso está
                    prohibido.
                </li>
                <li class="text-justify mb-4">Cualquier conflicto se resolverá según las leyes peruanas, ante tribunales
                    de
                    Lima.</li>

            </ul>
        </div>
        <div class="flex justify-around">
            <div class="flex max-w-[450px] justify-evenly w-[100%]">
                <button class="border border-white modal-continue-button p-[12px] rounded-full font-bold min-w-[130px]"
                    @click="acceptModal">
                    Aceptar
                </button>
            </div>
        </div>
    </Dialog> -->

</template>
