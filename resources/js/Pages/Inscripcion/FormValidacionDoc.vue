<script setup>
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import { usePage, router } from '@inertiajs/vue3';
import { ref, onMounted, computed, watch, nextTick, onUnmounted } from 'vue';
import { useForm } from 'vee-validate';
import * as yup from 'yup';
import Functions from '@/Functions';
import { useToast } from 'primevue/usetoast';
import Card from 'primevue/card';
import Calendar from 'primevue/calendar';
import InputGroup from 'primevue/inputgroup';
import InputGroupAddon from 'primevue/inputgroupaddon';
import Button from 'primevue/button';
import axios from 'axios';

import "../../../css/inscripciones.css";

const toast = useToast();
const page = usePage();

const generos = ref([{ label: 'Male', value: 'M' }, { label: 'Female', value: 'F' }]);
const paises = ref(page.props.general.paises || []);
const departamentos = ref([]);
const loadingSearch = ref(false);
const today = new Date();
const esSocio = ref(false);
const hasSearched = ref(false);
const noEncontrado = ref(false);

const props = defineProps({
    saved_values: Object,
    tipo_origen: Number
});

const schema = yup.object({
    tipo_doc: yup.mixed().required('Document type is required'),
    documento: yup.string().required('Document number is required'),
    nombres: yup.string().required('First Name is required'),
    apellido_paterno: yup.string().required('Last Name is required'),
    pais: yup.mixed().required('Country is required'),
    direccionPersona: yup.string().required('Address is required'),
    correo: yup.string().email().required('Email is required'),
    celular: yup.string()
        .matches(/^\+?[0-9]*$/, 'Only numbers are allowed (the + at the beginning is optional)')
        .required('Phone is required'),
    sexo: yup.string().required('Gender is required'),
    fecha_nacimiento: yup.date().required('Date of Birth is required'),
});

const { defineField, errors, values, setValues, validate } = useForm({
    validationSchema: schema,
    initialValues: props.saved_values || {}
});

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
    sexo: 'Gender',
    fecha_nacimiento: 'Date of Birth'
};

const missingFields = computed(() => {
    return Object.keys(errors.value).map(key => fieldNames[key] || key);
});

const searchPerson = async () => {
    if (!tipo_doc.value || !documento.value) {
        toast.add({ severity: 'warn', summary: 'Warning', detail: 'Please enter document type and number', life: 3000 });
        return;
    }

    loadingSearch.value = true;
    hasSearched.value = false;
    noEncontrado.value = false;

    try {
        const response = await axios.post('/api/getperson', {
            id_tipo_documento: tipo_doc.value,
            numero_documento: documento.value
        });

        const data = response.data;
        hasSearched.value = true;

        if (data.status && data.persona) {
            const p = data.persona;
            noEncontrado.value = false;
            const paisAsignado = props.tipo_origen === 1 ? 75 : (p.pais || p.id_pais);
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
                pais: p.pais || p.id_pais || paisAsignado,
                sexo: p.sexo || '',
                fecha_nacimiento: p.fecha_nacimiento ? new Date(p.fecha_nacimiento) : null
            });

            toast.add({ severity: 'success', summary: 'Found', detail: 'Information loaded successfully', life: 3000 });
        } else {
            noEncontrado.value = true;
            // SI ES EXTRANJERO, permitimos que pase el check de socio aunque no se encuentre
            esSocio.value = props.tipo_origen === 2 ? true : false;

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

            toast.add({ severity: 'info', summary: 'Not Found', detail: 'No record found. Please fill manually.', life: 3000 });
        }
    } catch (error) {
        console.error("Search error:", error);
    } finally {
        loadingSearch.value = false;
    }
};

const onlyNumberKey = (event) => {
    if (tipo_doc.value == 1) {
        const charCode = event.charCode ? event.charCode : event.keyCode
        if (charCode < 48 || charCode > 57) event.preventDefault();
        if (documento.value?.length >= 8) event.preventDefault();
    }
}

// const clearDocument = () => {
//     documento.value = "";
//     setValues({
//         nombres: '',
//         apellido_paterno: '',
//         empresa: ''
//     });
// }



// const clearDocument = () => {
//     documento.value = "";
//     hasSearched.value = false; // <-- CRUCIAL: Esto vuelve a bloquear los campos
//     setValues({
//         nombres: '',
//         apellido_paterno: '',
//         empresa: '',
//         correo: '',
//         celular: '',
//         direccionPersona: '',
//         sexo: '',
//         fecha_nacimiento: null
//     });
// }



// const camposBloqueados = computed(() => {
//     // Bloqueamos si es peruano (tipo_origen === 1) y aún no ha buscado
//     return esPeruano.value && !hasSearched.value;
// });

const camposBloqueados = computed(() => {
    return esPeruano.value && !hasSearched.value;
});

const tiposDocumentoFiltrados = computed(() => {
    const todos = page.props.general.tipDocPer || [];
    if (props.tipo_origen === 1) {
        return todos.filter(d => d.id === 1 || d.id === 2);
    } else if (props.tipo_origen === 2) {
        return todos.filter(d => d.id !== 1 && d.id !== 2);
    }
    return todos;
});

const esPeruano = computed(() => props.tipo_origen === 1);

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

const esCategoriaDeSocio = computed(() => {
    // CAMBIO CLAVE: Si es extranjero (2), NUNCA bloqueamos por socio
    if (props.tipo_origen === 2) return false;

    const urlParams = new URLSearchParams(window.location.search);
    const categoryId = urlParams.get('category');
    const categoriasSocio = ['35', '29'];
    return categoriasSocio.includes(categoryId);
});

const getValidacionDoc = async () => {
    const result = await validate();

    // Validamos socio solo para las categorías que lo exigen Y si no es extranjero
    if (esCategoriaDeSocio.value) {
        if (!hasSearched.value || !esSocio.value) {
            return { validate: false };
        }
    }

    if (result.valid) {
        return {
            validate: true,
            formValidacionDoc: values
        };
    } else {
        console.log("Errores:", errors.value);
        return { validate: false };
    }
}

onMounted(() => {
    if (!tipo_doc.value) tipo_doc.value = 5;

    if (esPeruano.value) {
        tipo_doc.value = 1;
        pais.value = 75; // Respetamos tu ID de Perú
        loadDepartamentos();
    }
})

defineExpose({
    getValidacionDoc,
    esSocio,
    hasSearched,
    esCategoriaDeSocio
});


const onlyPhoneKeys = (event) => {
    const charCode = event.charCode ? event.charCode : event.keyCode;
    const charStr = String.fromCharCode(charCode);
    if (charStr === '+' && event.target.selectionStart === 0 && !celular.value?.includes('+')) {
        return true;
    }
    if (charCode >= 48 && charCode <= 57) {
        return true;
    }
    event.preventDefault();
    return false;
};

const goToHome = () => {
    // Solo preguntamos si hay algo escrito (por ejemplo, si ya puso su documento)
    if (documento.value) {
        const confirmacion = confirm("Are you sure you want to leave? All progress in this registration will be lost.");
        if (confirmacion) {
            router.get('/');
        }
    } else {
        router.get('/');
    }
};

// const clearDocument = async () => {
//     documento.value = "";
//     // Si es extranjero (2), mantenemos los campos abiertos
//     hasSearched.value = (props.tipo_origen === 2);

//     setValues({
//         nombres: '',
//         apellido_paterno: '',
//         empresa: '',
//         correo: '',
//         celular: '',
//         direccionPersona: '',
//         sexo: '',
//         fecha_nacimiento: null
//     });

//     if (props.tipo_origen === 2) {
//         await nextTick();
//         validate();
//     }
// }

// const clearDocument = async () => {
//     documento.value = "";

//     // Si es peruano, al limpiar el documento debemos permitir que vuelva a buscar
//     if (props.tipo_origen === 1) {
//         hasSearched.value = false; // Esto "desbloquea" la lógica de búsqueda
//     } else {
//         hasSearched.value = true; // Si es extranjero, los campos siguen abiertos
//     }

//     setValues({
//         nombres: '',
//         apellido_paterno: '',
//         empresa: '',
//         correo: '',
//         celular: '',
//         direccionPersona: '',
//         sexo: '',
//         fecha_nacimiento: null
//     });
// }

const clearDocument = async () => {
    // Siempre limpiamos el número de documento al cambiar el tipo
    documento.value = "";

    if (props.tipo_origen === 1) {
        // --- FLUJO PERUANO: Blanqueo total porque los datos dependen del DNI ---
        hasSearched.value = false;
        setValues({
            nombres: '',
            apellido_paterno: '',
            empresa: '',
            correo: '',
            celular: '',
            direccionPersona: '',
            sexo: '',
            fecha_nacimiento: null
        });
    } else {
        // --- FLUJO EXTRANJERO: NO blanqueamos detalles personales ---
        // Solo aseguramos que los campos sigan editables
        hasSearched.value = true;

        // Mantenemos los valores actuales del formulario (nombres, correo, etc.)
        // y solo reseteamos el documento que ya hicimos arriba.
    }
};



// UNICO WATCHER PARA TIPO_ORIGEN
watch(() => props.tipo_origen, async (newOrigen) => {
    if (newOrigen === 1) {
        // --- LÓGICA NACIONAL ---
        tipo_doc.value = 1;
        pais.value = 75;
        await loadDepartamentos();
        setValues({ ...values, pais: 75, tipo_doc: 1 });
        hasSearched.value = false; // Bloquea campos hasta que busquen DNI

    } else if (newOrigen === 2) {
        // --- LÓGICA INTERNACIONAL ---
        if (tipo_doc.value === 1) tipo_doc.value = null;

        pais.value = null;
        esSocio.value = true;
        hasSearched.value = true; // DESBLOQUEA CAMPOS PARA EXTRANJEROS

        // FORZAR VALIDACIÓN DE TODO EL FORMULARIO
        await nextTick();
        validate();
    }
}, { immediate: true });

// watch(() => props.tipo_origen, async (newOrigen) => {
//     if (newOrigen === 1) {
//         tipo_doc.value = 1;
//         pais.value = 75;
//         await loadDepartamentos();
//         hasSearched.value = false;
//     } else if (newOrigen === 2) {
//         // Al pasar a extranjero, marcamos como "buscado" para que los campos
//         // se desbloqueen inmediatamente sin borrar lo que el usuario ya escribió
//         hasSearched.value = true;
//         esSocio.value = true;
//     }
// }, { immediate: true });

// const handleBeforeUnload = (event) => {
//     // Solo bloquea si hay algún dato (ejemplo: documento o nombres)
//     if (props.data_persona?.documento || props.data_persona?.nombres) {
//         event.preventDefault();
//         event.returnValue = '';
//     }
// };

// onMounted(() => {
//     window.addEventListener('beforeunload', handleBeforeUnload);
// });

// onUnmounted(() => {
//     // ESTO ES VITAL: Si no lo pones, la alerta te seguirá al Paso 3
//     window.removeEventListener('beforeunload', handleBeforeUnload);
// });

</script>

<template>
    <div class="font-bold p-4 relative">

        <div class="flex justify-end pr-2 mb-4">
            <Button @click="goToHome" class="wmc-btn-international shadow-xl flex items-center">
                <i class="pi pi-home mr-3 text-lg"></i>
                <div class="flex flex-col items-start leading-none">
                    <span class="text-xs font-bold uppercase tracking-[0.3em] ml-4">Main Menu</span>
                </div>
            </Button>
        </div>

        <Card class="mt-5 overflow-hidden shadow-lg border border-gray-200">
            <template #header>
                <div class="w-full py-3 text-xl font-bold text-center bg-lightblue-wmc border-blue-wmc">
                    {{ esPeruano ? 'Search' : 'Identification' }}
                </div>
            </template>
            <template #content>
                <div class="w-full px-4 pb-4">
                    <div v-if="hasSearched && esSocio && !noEncontrado && esCategoriaDeSocio"
                        class="flex items-center p-4 mb-4 text-green-800 border-t-4 border-green-300 bg-green-50 rounded-lg"
                        role="alert">
                        <i class="pi pi-check-circle mr-2 text-xl"></i>
                        <div class="text-sm font-medium">Verification successful. You are an <strong>Active
                                Member</strong>.</div>
                    </div>

                    <div v-if="hasSearched && !esSocio && esCategoriaDeSocio"
                        class="flex flex-col p-4 mb-4 text-red-800 border-t-4 border-red-300 bg-red-50 rounded-lg"
                        role="alert">
                        <div class="flex items-center">
                            <i class="pi pi-exclamation-triangle mr-2 text-xl"></i>
                            <span class="text-sm font-bold">You are not a member.</span>
                        </div>
                        <div class="mt-2 text-sm">
                            This category is exclusive for members. Please contact <a
                                href="mailto:asociados@iimp.org.pe"
                                class="font-bold underline">asociados@iimp.org.pe</a> or change your category.
                        </div>
                    </div>

                    <div v-if="hasSearched && noEncontrado"
                        class="flex items-center p-4 mb-4 text-blue-800 border-t-4 border-blue-300 bg-blue-50 rounded-lg"
                        role="alert">
                        <i class="pi pi-info-circle mr-2 text-xl"></i>
                        <div class="text-sm font-medium">No records found. <strong>Please complete your details manually
                                in the form below.</strong></div>
                    </div>
                </div>

                <div v-if="camposBloqueados"
                    class="mx-6 mb-2 p-2 bg-yellow-50 text-yellow-700 border-l-4 border-yellow-400 text-xs font-semibold">
                    <i class="pi pi-lock mr-2"></i> PLEASE SEARCH BY DOCUMENT NUMBER TO UNLOCK THESE FIELDS
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
                                {{ esPeruano ? 'Document Number' : 'Identity Card / Passport Number' }} <span
                                    class="text-red-600">*</span>
                            </label>
                            <InputGroup v-if="esPeruano">
                                <!-- <InputText name="documento" v-model="documento" v-bind="documentoAttrs"
                                    class="w-full border-green-iimp" @keypress="onlyNumberKey" :maxlength="25" /> -->
                                <InputText name="documento" v-model="documento" v-bind="documentoAttrs"
                                    class="w-full border-green-iimp" @keypress="onlyNumberKey" :maxlength="25"
                                    :disabled="loadingSearch" />
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
        <!--PERSONAL DATAILS/
        /==============================  -->
        <Card class="mt-5 overflow-hidden shadow-lg border border-gray-200">
            <template #header>
                <div class="w-full py-3 text-xl font-bold text-center bg-lightblue-wmc border-blue-wmc">Personal Details
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
                                :disabled="camposBloqueados" class="w-full border-green-iimp" />
                            <small class="text-red-600">{{ errors.nombres }}</small>
                        </div>
                        <div class="w-full">
                            <label for="apellido_paterno">Last Name <span class="text-red-600">*</span></label>
                            <InputText name="apellido_paterno" v-model="apellido_paterno" v-bind="apellido_paternoAttrs"
                                :disabled="camposBloqueados" class="w-full border-green-iimp" />
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
                                :disabled="camposBloqueados" class="w-full border-green-iimp" />
                            <small class="text-red-600">{{ errors.direccionPersona }}</small>
                        </div>
                    </div>

                    <div class="grid gap-6 m-6 md:grid-cols-3">
                        <div class="w-full">
                            <label for="correo" class="">Email Address <span
                                    class="font-normal text-red-600">*</span></label>
                            <InputText name="correo" v-model="correo" v-bind="correoAttrs" :disabled="camposBloqueados"
                                class="w-full border-green-iimp" />
                            <span class="font-normal text-red-600">{{ errors.correo }}</span>
                        </div>
                        <div class="w-full">
                            <label for="celular" class="">Phone Number <span
                                    class="font-normal text-red-600">*</span></label>
                            <InputText name="celular" v-model="celular" v-bind="celularAttrs" @keypress="onlyPhoneKeys"
                                :disabled="camposBloqueados" class="w-full border-green-iimp"
                                placeholder="+51999888777 or 999888777" />
                            <span class="font-normal text-red-600">{{ errors.celular }}</span>
                        </div>
                        <div class="w-full">
                            <label for="empresa" class="">Company <span
                                    class="font-normal text-gray-500 ml-1">(Optional)</span></label>
                            <InputText name="empresa" v-model="empresa" v-bind="empresaAttrs"
                                :disabled="camposBloqueados" class="w-full border-green-iimp" />
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
                                    :disabled="camposBloqueados"
                                    inputClass="w-full border-green-iimp border-l-0 shadow-none outline-none bg-white" />
                            </InputGroup>
                            <span class="font-normal text-red-600">{{ errors.fecha_nacimiento }}</span>
                        </div>
                        <div class="w-full">
                            <label for="sexo" class="">Gender <span class="font-normal text-red-600"> *</span></label>
                            <Select name="sexo" v-model="sexo" v-bind="sexoAttrs" optionLabel="label"
                                optionValue="value" placeholder="Select" showClear checkmark :options="generos"
                                :disabled="camposBloqueados" class="w-full border-green-iimp" />
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
    opacity: 1;
}

:deep(.p-select.p-disabled .p-select-label) {
    color: #4b5563 !important;
}

.wmc-btn-international {
    /* Fondo blanco con un toque de gris muy claro */
    background-color: #f8fafc !important;
    /* Color de texto "Steel" (Acero) profesional */
    color: #334155 !important;
    border: 1.5px solid #cbd5e1 !important;
    padding: 0.8rem 1.8rem !important;
    border-radius: 12px !important;
    transition: all 0.3s ease;
}

.wmc-btn-international:hover {
    background-color: #ffffff !important;
    border-color: #94a3b8 !important;
    color: #0f172a !important;
    transform: translateY(-1px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
}

.wmc-btn-international i {
    color: #64748b;
    /* El icono un poco más suave que el texto */
}
</style>
