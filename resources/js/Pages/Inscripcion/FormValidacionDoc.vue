<script setup>
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import { usePage } from '@inertiajs/vue3';
import { ref, onMounted, computed, watch } from 'vue';
import { useForm } from 'vee-validate';
import * as yup from 'yup';
import Functions from '@/Functions';
import { useToast } from 'primevue/usetoast';
import Card from 'primevue/card';
import Calendar from 'primevue/calendar';
import InputGroup from 'primevue/inputgroup';
import InputGroupAddon from 'primevue/inputgroupaddon';
import Button from 'primevue/button'; // Importante para el botón de buscar
import axios from 'axios'; // Necesario para la petición

import "../../../css/inscripciones.css";

const toast = useToast();
const page = usePage();

const generos = ref([{ label: 'Male', value: 'M' }, { label: 'Female', value: 'F' }]); // Ejemplo
const paises = ref(page.props.general.paises || []);
const departamentos = ref([]);
const loadingSearch = ref(false); // Para el loading del botón
const today = new Date();
const esSocio = ref(false);
const hasSearched = ref(false);
const noEncontrado = ref(false);

const props = defineProps({
    saved_values: Object,
    tipo_origen: Number
});

// 1. ACTUALIZAR SCHEMA: Validar todos los campos nuevos
const schema = yup.object({
    tipo_doc: yup.mixed().required('Document type is required'),
    documento: yup.string().required('Document number is required'),
    nombres: yup.string().required('First Name is required'),
    apellido_paterno: yup.string().required('Last Name is required'),
    pais: yup.mixed().required('Country is required'),
    direccionPersona: yup.string().required('Address is required'),
    correo: yup.string().email().required('Email is required'),
    // celular: yup.string().required('Phone is required'),
    celular: yup.string()
        .matches(/^\+?[0-9]*$/, 'Only numbers are allowed (the + at the beginning is optional)')
        .required('Phone is required'),
    sexo: yup.string().required('Sex is required'),
    fecha_nacimiento: yup.date().required('Date of Birth is required'),
    // empresa: yup.string().required('Company is required'),
});

const { defineField, errors, values, setValues, validate } = useForm({
    validationSchema: schema,
    initialValues: props.saved_values || {}
});

// 2. DEFINIR TODOS LOS CAMPOS (Para que funcionen los v-model)
const [tipo_doc, tipo_docAttrs] = defineField('tipo_doc');
const [documento, documentoAttrs] = defineField('documento');
const [nombres, nombresAttrs] = defineField('nombres');
const [apellido_paterno, apellido_paternoAttrs] = defineField('apellido_paterno');
const [pais, paisAttrs] = defineField('pais');
const [direccionPersona, direccionPersonaAttrs] = defineField('direccionPersona');
const [correo, correoAttrs] = defineField('correo');
const [celular, celularAttrs] = defineField('celular');
const [empresa, empresaAttrs] = defineField('empresa');
const [fecha_nacimiento, fecha_nacimientoAttrs] = defineField('fecha_nacimiento');
const [sexo, sexoAttrs] = defineField('sexo');


const fieldNames = {
    tipo_doc: 'Document Type',
    documento: 'Document Number',
    nombres: 'First Name',
    apellido_paterno: 'Last Name',
    pais: 'Country',
    direccionPersona: 'Address',
    correo: 'Email',
    celular: 'Phone',
    sexo: 'Sex',
    fecha_nacimiento: 'Date of Birth'
};

// Obtiene la lista de campos con errores
const missingFields = computed(() => {
    return Object.keys(errors.value).map(key => fieldNames[key] || key);
});

// 3. FUNCIÓN PARA BUSCAR Y LLENAR DATOS
// const searchPerson = async () => {

//     if (!tipo_doc.value || !documento.value) {
//         toast.add({ severity: 'warn', summary: 'Warning', detail: 'Please enter document type and number', life: 3000 });
//         return;
//     }

//     loadingSearch.value = true;
//     hasSearched.value = false;
//     noEncontrado.value = false;

//     try {
//         const response = await axios.post('/api/getperson', {
//             id_tipo_documento: tipo_doc.value,
//             numero_documento: documento.value
//         });

//         const data = response.data;
//         hasSearched.value = true;

//         if (data.status && data.persona) {
//             const p = data.persona;

//             // --- CORRECCIÓN PARA DEPARTAMENTOS ---
//             // Primero asignamos el país y cargamos sus departamentos
//             // para que el Select de "State" tenga opciones que mostrar.
//             if (p.pais || p.id_pais) {
//                 pais.value = p.pais || p.id_pais;
//                 await loadDepartamentos(); // Cargamos la lista antes del setValues
//             }

//             esSocio.value = p.es_socio;
//             noEncontrado.value = false;
//             console.log("¿Es socio?:", esSocio.value);
//             // --- CORRECCIÓN PARA FECHA Y DEMÁS ---
//             setValues({
//                 tipo_doc: p.id_tipo_documento,
//                 documento: p.documento,
//                 nombres: p.nombres || '',
//                 apellido_paterno: p.apellido_paterno || '',
//                 correo: p.correo || '',
//                 celular: p.celular || '',
//                 direccionPersona: p.direccionPersona || p.direccion?.direccion || '',
//                 empresa: p.empresa_nombre || p.empresa || '',
//                 pais: p.pais || p.id_pais,
//                 departamento: p.departamento, // Ahora sí se verá porque ya cargamos la lista
//                 sexo: p.sexo || '',
//                 // FIX: Convertimos el string "1989-02-13" a objeto Date para el Calendar
//                 fecha_nacimiento: p.fecha_nacimiento ? new Date(p.fecha_nacimiento) : null
//             });

//             toast.add({ severity: 'success', summary: 'Found', detail: 'Information loaded successfully', life: 3000 });
//         } else {
//             toast.add({ severity: 'info', summary: 'Not Found', detail: 'No local record found', life: 2000 });
//         }
//     } catch (error) {
//         console.error("Search error:", error);
//     } finally {
//         loadingSearch.value = false;
//     }
// };
const searchPerson = async () => {
    if (!tipo_doc.value || !documento.value) {
        toast.add({ severity: 'warn', summary: 'Warning', detail: 'Please enter document type and number', life: 3000 });
        return;
    }

    loadingSearch.value = true;
    hasSearched.value = false;
    noEncontrado.value = false; // Resetear antes de buscar

    try {
        const response = await axios.post('/api/getperson', {
            id_tipo_documento: tipo_doc.value,
            numero_documento: documento.value
        });

        const data = response.data;
        hasSearched.value = true;

        if (data.status && data.persona) {
            const p = data.persona;
            noEncontrado.value = false; // Se encontró, ocultamos alerta de manual

            if (p.pais || p.id_pais) {
                pais.value = p.pais || p.id_pais;
                await loadDepartamentos();
            }

            esSocio.value = p.es_socio;

            setValues({
                tipo_doc: p.id_tipo_documento,
                documento: p.documento,
                nombres: p.nombres || '',
                apellido_paterno: p.apellido_paterno || '',
                correo: p.correo || '',
                celular: p.celular || '',
                direccionPersona: p.direccionPersona || p.direccion?.direccion || '',
                empresa: p.empresa_nombre || p.empresa || '',
                pais: p.pais || p.id_pais,
                sexo: p.sexo || '',
                fecha_nacimiento: p.fecha_nacimiento ? new Date(p.fecha_nacimiento) : null
            });

            toast.add({ severity: 'success', summary: 'Found', detail: 'Information loaded successfully', life: 3000 });
        } else {
            // CASO: NO ENCONTRADO
            noEncontrado.value = true;
            esSocio.value = true; // IMPORTANTE: Permitimos el registro manual asumiendo que es socio o se validará luego

            // Limpiamos los campos para llenado manual (excepto doc)
            setValues({
                tipo_doc: tipo_doc.value,
                documento: documento.value,
                nombres: '',
                apellido_paterno: '',
                correo: '',
                celular: '',
                direccionPersona: '',
                empresa: '',
                sexo: '',
                fecha_nacimiento: null
            });
        }
    } catch (error) {
        console.error("Search error:", error);
    } finally {
        loadingSearch.value = false;
    }
};
// --- Helpers de Input ---
const onlyNumberKey = (event) => {
    if (tipo_doc.value == 1) { // DNI
        const charCode = event.charCode ? event.charCode : event.keyCode
        if (charCode < 48 || charCode > 57) event.preventDefault();
        if (documento.value?.length >= 8) event.preventDefault();
    }
}

const clearDocument = () => {
    documento.value = "";
    // Opcional: Limpiar nombres al cambiar tipo de documento
    setValues({
        nombres: '',
        apellido_paterno: '',
        empresa: ''
    });
}

const tiposDocumentoFiltrados = computed(() => {
    const todos = page.props.general.tipDocPer || [];

    if (props.tipo_origen === 1) {
        // Si es Peruano, solo permitir DNI (ID 1) y quizás RUC (ID 2) si aplica
        return todos.filter(d => d.id === 1 || d.id === 2);
    } else if (props.tipo_origen === 2) {
        // Si es Extranjero, permitir Pasaporte, Carnet de Extranjería, etc. (Excluir DNI)
        return todos.filter(d => d.id !== 1 && d.id !== 2);
    }
    return todos;
});

const esPeruano = computed(() => props.tipo_origen === 1);
// --- Cargas de Ubigeo ---
const loadDepartamentos = async () => {
    if (pais.value) {
        try {
            const res = await axios.post(route('padre.departamentos'), { id: pais.value });
            departamentos.value = res.data.departamentos;
        } catch (e) {
            console.error("Error cargando departamentos", e);
        }
    }
};

// 4. VALIDACIÓN GLOBAL PARA EL BOTÓN "NEXT" DEL PADRE
// const getValidacionDoc = async () => {
//     const result = await validate();

//     if (hasSearched.value && !esSocio.value) {
//         return { validate: false }; // El botón de register no hará nada
//     }

//     if (result.valid) {
//         return {
//             validate: true,
//             formValidacionDoc: values // Enviamos todos los datos (nombre, apellido, etc)
//         };
//     } else {
//         toast.add({ severity: 'error', summary: 'Error', detail: 'Please fill all required fields', life: 3000 });
//         return { validate: false };
//     }
// }
const getValidacionDoc = async () => {
    const result = await validate();

    if (hasSearched.value && !esSocio.value) {
        return { validate: false };
    }

    if (result.valid) {
        return {
            validate: true,
            formValidacionDoc: values
        };
    } else {
        // Ya no enviamos Toast, la alerta en el template se encargará
        return { validate: false };
    }
}
onMounted(() => {
    if (!tipo_doc.value) tipo_doc.value = 5; // Default value logic

    if (esPeruano.value) {
        tipo_doc.value = 1;
        // Asumiendo que 148 es el ID de Perú en tu BD
        id_pais.value = 75;
        // Disparar carga de departamentos si es necesario
        getDepartamentos();
    }
})

defineExpose({
    getValidacionDoc,
    esSocio,       // Exponemos el estado de socio
    hasSearched
});

watch(() => props.tipo_origen, (newOrigen) => {
    if (newOrigen === 1) { // CASO PERUANO
        // Asignamos DNI (ID 1)
        tipo_doc.value = 1;

        // Asignamos País Perú (ID 148 según tu lógica previa)
        // Usamos 'pais' porque así declaraste: const [pais] = defineField('pais');
        pais.value = 75;

        // Ejecutar carga de departamentos si la función existe
        loadDepartamentos();
    } else if (newOrigen === 2) { // CASO EXTRANJERO
        // Limpiamos o ponemos un valor por defecto que NO sea DNI
        if (tipo_doc.value === 1) {
            tipo_doc.value = null;
        }
        // Limpiamos país para que el extranjero elija el suyo
        pais.value = null;
    }
}, { immediate: true });


const onlyPhoneKeys = (event) => {
    const charCode = event.charCode ? event.charCode : event.keyCode;
    const charStr = String.fromCharCode(charCode);

    // 1. Permitir el '+' solo si es el primer carácter y no hay otro ya puesto
    if (charStr === '+' && event.target.selectionStart === 0 && !celular.value?.includes('+')) {
        return true;
    }

    // 2. Permitir números (0-9)
    if (charCode >= 48 && charCode <= 57) {
        return true;
    }

    // 3. Bloquear todo lo demás (letras, espacios, puntos, guiones)
    event.preventDefault();
    return false;
};


</script>

<template>
    <div class="font-bold p-4">
        <Card class="mt-5 overflow-hidden shadow-lg border border-gray-200">
            <template #header>
                <div class="w-full py-3 text-xl font-bold text-center bg-lightblue-wmc border-blue-wmc">
                    {{ esPeruano ? 'Search' : 'Identification' }}
                </div>
            </template>
            <template #content>
                <div class="w-full px-4 pb-4">

                    <div v-if="hasSearched && esSocio && !noEncontrado"
                        class="flex items-center p-4 mb-4 text-green-800 border-t-4 border-green-300 bg-green-50 rounded-lg"
                        role="alert">
                        <i class="pi pi-check-circle mr-2 text-xl"></i>
                        <div class="text-sm font-medium">Verification successful. You are an <strong>Active
                                Member</strong>.</div>
                    </div>

                    <div v-if="hasSearched && !esSocio"
                        class="flex flex-col p-4 mb-4 text-red-800 border-t-4 border-red-300 bg-red-50 rounded-lg"
                        role="alert">
                        <div class="flex items-center">
                            <i class="pi pi-exclamation-triangle mr-2 text-xl"></i>
                            <span class="text-sm font-bold">You are not a member.</span>
                        </div>
                        <div class="mt-2 text-sm">
                            Please contact <a href="mailto:asociados@iimp.org.pe"
                                class="font-bold underline">asociados@iimp.org.pe</a> to update your status.
                        </div>
                    </div>

                    <div v-if="hasSearched && noEncontrado"
                        class="flex items-center p-4 mb-4 text-blue-800 border-t-4 border-blue-300 bg-blue-50 rounded-lg"
                        role="alert">
                        <i class="pi pi-info-circle mr-2 text-xl"></i>
                        <div class="text-sm font-medium">
                            No records found. <strong>Please complete your details manually in the form below.</strong>
                        </div>
                    </div>


                </div>

                <div class="flex gap-6 p-2 w-full justify-around">

                    <div
                        class="text-green-iimp font-bold max-w-[650px] w-full p-4 grid grid-cols-1 md:grid-cols-2 gap-4">

                        <div class="col-span-1">
                            <label for="tipo_doc">Document Type <span class="text-red-600">*</span></label>
                            <Select name="tipo_doc" v-model="tipo_doc" v-bind="tipo_docAttrs"
                                :options="tiposDocumentoFiltrados" @change="clearDocument" optionLabel="name_en"
                                optionValue="id" placeholder="Select Document" showClear checkmark
                                class="w-full border-green-iimp" :disabled="esPeruano"
                                :class="{ 'bg-gray-100 opacity-70': esPeruano }" />
                            <small class="text-red-600">{{ errors.tipo_doc }}</small>
                        </div>

                        <div class="col-span-1">
                            <label for="documento">
                                {{ esPeruano ? 'Document Number' : 'Identity Card / Passport Number' }}
                                <span class="text-red-600">*</span>
                            </label>

                            <InputGroup v-if="esPeruano">
                                <InputText name="documento" v-model="documento" v-bind="documentoAttrs"
                                    class="w-full border-green-iimp" @keypress="onlyNumberKey" :maxlength="25" />
                                <Button icon="pi pi-search" severity="info" @click="searchPerson"
                                    :loading="loadingSearch" label="Search" />
                            </InputGroup>

                            <InputText v-else name="documento" v-model="documento" v-bind="documentoAttrs"
                                class="w-full border-green-iimp" :maxlength="25" placeholder="Enter number" />

                            <small class="text-red-600">{{ errors.documento }}</small>
                        </div>

                    </div>
                </div>
            </template>
        </Card>

        <Card class="mt-5 overflow-hidden shadow-lg border border-gray-200">
            <template #header>
                <div class="w-full py-3 text-xl font-bold text-center bg-lightblue-wmc border-blue-wmc">
                    Personal Details
                </div>
            </template>
            <template #content>
                <div class="p-2">
                    <div class="w-full px-4 pb-4">
                        <div v-if="missingFields.length > 0"
                            class="flex flex-col p-4 mb-4 text-orange-800 border-t-4 border-orange-300 bg-orange-50 rounded-lg"
                            role="alert">
                            <div class="flex items-center">
                                <i class="pi pi-exclamation-circle mr-2 text-xl"></i>
                                <span class="text-sm font-bold">Required Information Missing</span>
                            </div>
                            <div class="mt-2 text-sm">
                                Please complete the following fields to proceed:
                                <ul class="list-disc ml-5 mt-1">
                                    <li v-for="field in missingFields" :key="field">{{ field }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="grid gap-6 m-6 md:grid-cols-2">
                        <div class="w-full">
                            <label for="nombres">First Name <span class="text-red-600">*</span></label>
                            <InputText name="nombres" v-model="nombres" v-bind="nombresAttrs"
                                class="w-full border-green-iimp" />
                            <small class="text-red-600">{{ errors.nombres }}</small>
                        </div>
                        <div class="w-full">
                            <label for="apellido_paterno">Last Name <span class="text-red-600">*</span></label>
                            <InputText name="apellido_paterno" v-model="apellido_paterno" v-bind="apellido_paternoAttrs"
                                class="w-full border-green-iimp" />
                            <small class="text-red-600">{{ errors.apellido_paterno }}</small>
                        </div>
                    </div>

                    <div class="grid gap-6 m-6 grid-cols-1 md:grid-cols-4">
                        <div class="w-full md:col-span-2">
                            <label for="pais">Country <span class="text-red-600">*</span></label>
                            <Select name="pais" v-model="pais" v-bind="paisAttrs" :options="paises" optionLabel="name"
                                optionValue="id" placeholder="Select" showClear filter @change="loadDepartamentos"
                                :disabled="esPeruano" class="w-full border-green-iimp" />
                            <small class="text-red-600">{{ errors.pais }}</small>
                        </div>

                        <div class="w-full md:col-span-2">
                            <label for="direccionPersona">Address <span class="text-red-600">*</span></label>
                            <InputText name="direccionPersona" v-model="direccionPersona" v-bind="direccionPersonaAttrs"
                                class="w-full border-green-iimp" />
                            <small class="text-red-600">{{ errors.direccionPersona }}</small>
                        </div>
                    </div>

                    <div class="grid gap-6 m-6 md:grid-cols-3">
                        <div class="w-full">
                            <label for="correo" class="">Email Address <span
                                    class="font-normal text-red-600">*</span></label>
                            <InputText name="correo" v-model="correo" v-bind="correoAttrs"
                                class="w-full border-green-iimp" />
                            <span class="font-normal text-red-600">{{ errors.correo }}</span>
                        </div>

                        <div class="w-full">
                            <label for="celular" class="">Phone Number <span
                                    class="font-normal text-red-600">*</span></label>
                            <InputText name="celular" v-model="celular" v-bind="celularAttrs" @keypress="onlyPhoneKeys"
                                class="w-full border-green-iimp" placeholder="+51999888777 or 999888777" />
                            <span class="font-normal text-red-600">{{ errors.celular }}</span>
                        </div>
                        <div class="w-full">
                            <label for="empresa" class="">
                                Company
                                <span class="font-normal text-gray-500 ml-1">(Optional)</span>
                            </label>
                            <InputText name="empresa" v-model="empresa" v-bind="empresaAttrs"
                                class="w-full border-green-iimp" />
                            <span class="font-normal text-red-600">{{ errors.empresa }}</span>
                        </div>
                    </div>

                    <div class="grid gap-6 m-6 grid-cols-1 md:grid-cols-4">
                        <div class="w-full">
                            <label for="fecha_nacimiento">Date of Birth <span class="text-red-600">*</span></label>
                            <InputGroup class="w-full h-[42px]">
                                <InputGroupAddon class="border-green-iimp border-r-0 bg-white text-green-iimp">
                                    <i class="pi pi-calendar"></i>
                                </InputGroupAddon>

                                <Calendar name="fecha_nacimiento" v-model="fecha_nacimiento"
                                    v-bind="fecha_nacimientoAttrs" :maxDate="today" dateFormat="yy-mm-dd"
                                    :showTime="false" placeholder="YYYY-MM-DD" class="w-full"
                                    inputClass="w-full border-green-iimp border-l-0 shadow-none outline-none bg-white" />
                            </InputGroup>
                            <span class="font-normal text-red-600">{{ errors.fecha_nacimiento }}</span>
                        </div>
                        <div class="w-full">
                            <label for="sexo" class="">Sex <span class="font-normal text-red-600"> *</span></label>
                            <Select name="sexo" v-model="sexo" v-bind="sexoAttrs" optionLabel="label"
                                optionValue="value" placeholder="Elegir" showClear checkmark :options="generos"
                                class="w-full border-green-iimp" />
                            <span class="font-normal text-red-600">{{ errors.sexo }}</span>
                        </div>
                    </div>

                </div>
            </template>
        </Card>
    </div>
</template>
<style>
:deep(.p-select.p-disabled) {
    background-color: #f3f4f6 !important;
    /* Un gris suave */
    opacity: 1;
}

:deep(.p-select.p-disabled .p-select-label) {
    color: #4b5563 !important;
    /* Texto un poco más oscuro para que se lea bien */
}
</style>
