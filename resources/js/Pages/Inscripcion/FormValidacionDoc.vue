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

// Listas de datos (asumiendo que vienen de props.general o similar)
const tipoDocumento = computed(() => page.props.general.tipDocPer);
const generos = ref([{ label: 'Male', value: 'M' }, { label: 'Female', value: 'F' }]); // Ejemplo
const paises = ref(page.props.general.paises || []);

// Listas reactivas para ubicación
const departamentos = ref([]);
const provincias = ref([]);
const distritos = ref([]);

const loadingSearch = ref(false); // Para el loading del botón
const today = new Date();

const props = defineProps({
    saved_values: Object,
    tipo_origen: Number
});

// 1. ACTUALIZAR SCHEMA: Validar todos los campos nuevos
const schema = yup.object({
    tipo_doc: yup.mixed().required('Document type is required'),
    documento: yup.string().required('Document number is required'),
    // Agregamos las validaciones para los campos de abajo
    nombres: yup.string().required('First Name is required'),
    apellido_paterno: yup.string().required('Last Name is required'),
    pais: yup.mixed().required('Country is required'),
    direccionPersona: yup.string().required('Address is required'),
    correo: yup.string().email().required('Email is required'),
    celular: yup.string().required('Phone is required'),
    sexo: yup.string().required('Sex is required'),
    // fecha_nacimiento: yup.date().required('Date of Birth is required'),
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
// Si tienes apellido materno, agrégalo también
// const [apellido_materno, apellido_maternoAttrs] = defineField('apellido_materno');
const [pais, paisAttrs] = defineField('pais');
const [departamento, departamentoAttrs] = defineField('departamento');
const [direccionPersona, direccionPersonaAttrs] = defineField('direccionPersona');
const [correo, correoAttrs] = defineField('correo');
const [celular, celularAttrs] = defineField('celular');
const [empresa, empresaAttrs] = defineField('empresa');
const [fecha_nacimiento, fecha_nacimientoAttrs] = defineField('fecha_nacimiento');
const [sexo, sexoAttrs] = defineField('sexo');


// 3. FUNCIÓN PARA BUSCAR Y LLENAR DATOS
const searchPerson = async () => {
    if (!tipo_doc.value || !documento.value) {
        toast.add({ severity: 'warn', summary: 'Warning', detail: 'Please enter document type and number', life: 3000 });
        return;
    }

    loadingSearch.value = true;
    try {
        const response = await axios.post('/api/getperson', {
            id_tipo_documento: tipo_doc.value,
            numero_documento: documento.value
        });

        const data = response.data;

        if (data.status && data.persona) {
            const p = data.persona;

            // --- CORRECCIÓN PARA DEPARTAMENTOS ---
            // Primero asignamos el país y cargamos sus departamentos
            // para que el Select de "State" tenga opciones que mostrar.
            if (p.pais || p.id_pais) {
                pais.value = p.pais || p.id_pais;
                await loadDepartamentos(); // Cargamos la lista antes del setValues
            }

            // --- CORRECCIÓN PARA FECHA Y DEMÁS ---
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
                departamento: p.departamento, // Ahora sí se verá porque ya cargamos la lista
                sexo: p.sexo || '',
                // FIX: Convertimos el string "1989-02-13" a objeto Date para el Calendar
                fecha_nacimiento: p.fecha_nacimiento ? new Date(p.fecha_nacimiento) : null
            });

            toast.add({ severity: 'success', summary: 'Found', detail: 'Information loaded successfully', life: 3000 });
        } else {
            toast.add({ severity: 'info', summary: 'Not Found', detail: 'No local record found', life: 2000 });
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

const loadProvincias = async () => {
    if (pais.value && departamento.value) {
        try {
            const res = await axios.post(route('padre.provincias'), {
                id_pais: pais.value,
                id_departamento: departamento.value
            });
            provincias.value = res.data.provincias;
        } catch (e) {
            console.error("Error cargando provincias", e);
        }
    }
};

// 4. VALIDACIÓN GLOBAL PARA EL BOTÓN "NEXT" DEL PADRE
const getValidacionDoc = async () => {
    const result = await validate();

    if (result.valid) {
        return {
            validate: true,
            formValidacionDoc: values // Enviamos todos los datos (nombre, apellido, etc)
        };
    } else {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Please fill all required fields', life: 3000 });
        return { validate: false };
    }
}

onMounted(() => {
    if (!tipo_doc.value) tipo_doc.value = 5; // Default value logic

    if (esPeruano.value) {
        tipo_doc.value = 1;
        // Asumiendo que 148 es el ID de Perú en tu BD
        id_pais.value = 148;
        // Disparar carga de departamentos si es necesario
        getDepartamentos();
    }
})

defineExpose({
    getValidacionDoc
});

watch(() => props.tipo_origen, (newOrigen) => {
    if (newOrigen === 1) { // CASO PERUANO
        // Asignamos DNI (ID 1)
        tipo_doc.value = 1;

        // Asignamos País Perú (ID 148 según tu lógica previa)
        // Usamos 'pais' porque así declaraste: const [pais] = defineField('pais');
        pais.value = 148;

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
                        <div class="w-full">
                            <label for="pais">Country <span class="text-red-600">*</span></label>
                            <Select name="pais" v-model="pais" v-bind="paisAttrs" :options="paises" optionLabel="name"
                                optionValue="id" placeholder="Select" showClear filter @change="loadDepartamentos"
                                class="w-full border-green-iimp" />
                            <small class="text-red-600">{{ errors.pais }}</small>
                        </div>

                        <div class="w-full">
                            <label for="departamento" class="">State <span
                                    class="font-normal text-red-600">*</span></label>
                            <Select name="departamento" v-model="departamento" v-bind="departamentoAttrs" filter
                                @change="loadProvincias" :options="departamentos" optionLabel="name"
                                optionValue="id_departamento" placeholder="Select" showClear
                                class="w-full border-green-iimp" />
                            <span class="font-normal text-red-600">{{ errors.departamento }}</span>
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
                            <InputText name="celular" v-model="celular" v-bind="celularAttrs"
                                class="w-full border-green-iimp" />
                            <span class="font-normal text-red-600">{{ errors.celular }}</span>
                        </div>
                        <div class="w-full">
                            <label for="empresa" class="">Company <span
                                    class="font-normal text-red-600">*</span></label>
                            <InputText name="empresa" v-model="empresa" v-bind="empresaAttrs"
                                class="w-full border-green-iimp" />
                            <span class="font-normal text-red-600">{{ errors.empresa }}</span>
                        </div>
                    </div>

                    <div class="grid gap-6 m-6 grid-cols-1 md:grid-cols-4">
                        <div class="w-full">
                            <label for="fecha_nacimiento">Date of Birth</label>
                            <InputGroup class="w-full h-[42px]">
                                <InputGroupAddon class="border-green-iimp border-r-0 bg-white text-green-iimp">
                                    <i class="pi pi-calendar"></i>
                                </InputGroupAddon>

                                <Calendar name="fecha_nacimiento" v-model="fecha_nacimiento"
                                    v-bind="fecha_nacimientoAttrs" :maxDate="today" dateFormat="yy-mm-dd"
                                    :showTime="false" placeholder="YYYY-MM-DD" class="w-full"
                                    inputClass="w-full border-green-iimp border-l-0 shadow-none outline-none bg-white" />
                            </InputGroup>
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
