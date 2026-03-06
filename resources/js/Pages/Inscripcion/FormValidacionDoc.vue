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

const generos = ref([{ label: 'Masculino', value: 'M' }, { label: 'Femenino', value: 'F' }]);
const paises = ref(page.props.general.paises || []);
const departamentos = ref([]);
const loadingSearch = ref(false);
const today = new Date();
const esSocio = ref(false);
const hasSearched = ref(false);
const noEncontrado = ref(false);
const alphanumericMessage = ref('');
const provincias = ref();
const distritos = ref();
const searchSuccess = ref(false);
const searchError = ref(false);

const props = defineProps({
    saved_values: Object,
    // tipo_origen: Number,
    perfil_id: Number
});

const dniMessage = ref('');
const schema = yup.object({
    tipo_doc: yup.mixed().required('El tipo de documento es obligatorio'),
    documento: yup.string()
        .required('El número de documento es obligatorio')
        .test('len', 'El DNI debe tener exactamente 8 dígitos', (val, context) => {
            // Solo aplicamos la regla de 8 dígitos si el tipo de doc es DNI (ID 1)
            if (context.parent.tipo_doc === 1) {
                return val?.length === 8;
            }
            return true; // Para otros documentos, no aplicamos esta restricción
        }),
    nombres: yup.string().required('Los nombres son obligatorios'),
    apellido_paterno: yup.string().required('El apellido paterno es obligatorio'),
    apellido_materno: yup.string().required('El apellido materno es obligatorio'),
    pais: yup.mixed().required('El país es obligatorio'),
    direccionPersona: yup.string().required('La dirección es obligatoria'),
    empresa: yup.string().required('El nombre de la empresa es obligatoria'),
    cargo: yup.string().required('El Cargo dentro de la empresa es obligatorio'),
    correo: yup.string()
        .email('Ingrese un correo electrónico válido')
        .required('El correo electrónico es obligatorio'),
    celular: yup.string()
        .matches(/^\+?[0-9]*$/, 'Solo se permiten números (el + al inicio es opcional)')
        .required('El teléfono es obligatorio'),
    sexo: yup.string().required('El sexo es obligatorio'),
    fecha_nacimiento: yup.date()
        .required('La fecha de nacimiento es obligatoria')
        .nullable()
        .test('is-18', 'Debes ser mayor de 18 años', (value) => {
            if (!value) return false;

            const today = new Date();
            const birthDate = new Date(value);
            let age = today.getFullYear() - birthDate.getFullYear();
            const m = today.getMonth() - birthDate.getMonth();

            // Ajuste si el mes actual es menor al de nacimiento
            if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }

            return age >= 18;
        })
});

const { defineField, errors, values, setValues, validate } = useForm({
    validationSchema: schema,
    initialValues: props.saved_values || {}
});

const [tipo_doc, tipo_docAttrs] = defineField('tipo_doc');
const [documento, documentoAttrs] = defineField('documento');
const [nombres, nombresAttrs] = defineField('nombres');
const [apellido_paterno, apellido_paternoAttrs] = defineField('apellido_paterno');
const [apellido_materno, apellido_maternoAttrs] = defineField('apellido_materno');
const [pais, paisAttrs] = defineField('pais');
const [departamento, departamentoAttrs] = defineField('departamento');
const [provincia, provinciaAttrs] = defineField('provincia');
const [distrito, distritoAttrs] = defineField('distrito');
const [direccionPersona, direccionPersonaAttrs] = defineField('direccionPersona');
const [correo, correoAttrs] = defineField('correo');
const [celular, celularAttrs] = defineField('celular');
const [empresa, empresaAttrs] = defineField('empresa');
const [fecha_nacimiento, fecha_nacimientoAttrs] = defineField('fecha_nacimiento');
const [sexo, sexoAttrs] = defineField('sexo');
const [cargo, cargoAttrs] = defineField('cargo');

const fieldNames = {
    tipo_doc: 'Tipo de Documento',
    documento: 'Numero de Documento',
    nombres: 'Nombres',
    apellido_paterno: 'Apellido Paterno',
    apellido_materno: 'Apellido Materno',
    pais: 'Pais',
    direccionPersona: 'Direccion',
    correo: 'Correo Electronico',
    celular: 'Celular',
    sexo: 'Sexo',
    fecha_nacimiento: 'Fecha de Nacimiento',
    empresa: 'Empresa',
    cargo: 'Cargo'
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
    searchSuccess.value = false;
    searchError.value = true;

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
            searchSuccess.value = true;
            searchError.value = false;
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
                apellido_materno: p.apellido_materno || '',
                correo: p.correo || '',
                celular: p.celular || '',
                direccionPersona: p.direccionPersona || p.direccion?.direccion || '',
                empresa: p.empresa_nombre || p.empresa || '',
                cargo: p.cargo || '',
                pais: p.pais || p.id_pais || paisAsignado,
                sexo: p.sexo || '',
                fecha_nacimiento: p.fecha_nacimiento ? new Date(p.fecha_nacimiento) : null
            });

            // toast.add({ severity: 'success', summary: 'Found', detail: 'Information loaded successfully', life: 3000 });
        } else {
            noEncontrado.value = true;
            // SI ES EXTRANJERO, permitimos que pase el check de socio aunque no se encuentre
            esSocio.value = props.tipo_origen === 2 ? true : false;
            searchSuccess.value = false;
            searchError.value = true;
            setValues({
                tipo_doc: tipo_doc.value,
                documento: documento.value,
                nombres: '',
                apellido_paterno: '',
                apellido_materno: '',
                cargo: '',
                correo: '',
                celular: '',
                direccionPersona: '',
                empresa: '',
                sexo: '',
                fecha_nacimiento: null
            });

            // toast.add({ severity: 'info', summary: 'Not Found', detail: 'No record found. Please fill manually.', life: 3000 });
        }
    } catch (error) {
        console.error("Search error:", error);
    } finally {
        loadingSearch.value = false;
    }
};

const onlyNumberKey = (event) => {
    const charCode = event.which ? event.which : event.keyCode;

    // 1. Permitir teclas de control (Backspace, Delete, Tab, flechas)
    if ([8, 46, 9, 37, 39].includes(charCode)) return true;

    // 2. Permitir combinaciones de teclas (Ctrl+A, Ctrl+C, Ctrl+V)
    if (event.ctrlKey || event.metaKey) return true;

    if (tipo_doc.value == 1) {
        // 3. Si hay una selección de texto (el usuario seleccionó todo para borrar/sobrescribir)
        // permitimos la entrada porque la selección será reemplazada.
        const selection = window.getSelection().toString();
        if (selection.length > 0 && selection.length === documento.value?.length) {
            return true;
        }

        // 4. Bloquear si no es número
        if (charCode < 48 || charCode > 57) {
            event.preventDefault();
            return false;
        }

        // 5. Bloquear si ya llegó a 8 dígitos Y MOSTRAR MENSAJE
        if (documento.value?.length >= 8) {
            dniMessage.value = "Solo se permiten 8 dígitos";

            // Limpiar el mensaje automáticamente después de 3 segundos
            setTimeout(() => {
                dniMessage.value = '';
            }, 3000);

            event.preventDefault();
            return false;
        } else {
            dniMessage.value = ''; // Limpiar si el usuario borra y vuelve a tener espacio
        }
    }
    return true;
};

const maxAdultDate = computed(() => {
    const date = new Date();
    date.setFullYear(date.getFullYear() - 18);
    return date;
});

const mostrarBannerBloqueo = computed(() => {
    // Si ya buscó y NO es perfil 1 o 5, el banner debe desaparecer
    if (hasSearched.value && ![1, 5].includes(props.perfil_id)) {
        return false;
    }
    // De lo contrario, sigue las reglas normales de bloqueo
    return camposBloqueados.value;
});

const camposBloqueados = computed(() => {
    // 1. Condición de búsqueda (Solo para peruanos que no han buscado aún)
    const faltaBuscarPeruano = esDNI.value && !hasSearched.value;

    // 2. Condición de Perfil Crítico (Bloqueo TOTAL e incondicional para perfil 1 o 5)
    const esPerfilBloqueado = [1, 4].includes(props.perfil_id);

    // Si cualquiera de las dos es verdadera, el campo se bloquea
    return faltaBuscarPeruano || esPerfilBloqueado;
});

const esCampoBloqueado = (valorCampo) => {
    // 1. Si es peruano y no ha buscado, todo bloqueado
    if (esDNI.value && !hasSearched.value) return true;

    // 2. Si es perfil crítico (1 o 5)
    if ([1, 4].includes(props.perfil_id)) {
        // BLOQUEA solo si el campo NO está vacío (tiene contenido previo)
        // Usamos trim() para evitar espacios en blanco
        return valorCampo !== null && valorCampo !== undefined && String(valorCampo).trim() !== '';
    }

    return false;
};

const tiposDocumentoFiltrados = computed(() => {
    const todos = page.props.general.tipDocPer || [];
    return todos;
});

// const loadDepartamentos = async () => {
//     if (pais.value) {
//         try {
//             const res = await axios.post(route('padre.departamentos'), { id: pais.value });
//             departamentos.value = res.data.departamentos;
//         } catch (e) {
//             console.error("Error cargando departamentos", e);
//         }
//     }
// };

const loadDepartamentos = async () => {
    departamento.value = null; // Cambia undefined por null
    provincia.value = null;
    distrito.value = null;
    departamentos.value = await Functions.loadDepartamentos(pais.value);
}

const loadProvincias = async () => {
    provincia.value = null;
    distrito.value = null;
    provincias.value = await Functions.loadProvincias(pais.value, departamento.value);
}

const loadDistritos = async () => {
    distrito.value = null;
    distritos.value = await Functions.loadDistritos(pais.value, departamento.value, provincia.value);
}

const esCategoriaDeSocio = computed(() => {

    const urlParams = new URLSearchParams(window.location.search);
    const categoryId = urlParams.get('category');
    const categoriasSocio = ['1', '4'];
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

const clearDocument = async () => {
    documento.value = "";
    hasSearched.value = false;
    searchSuccess.value = false;
    // IMPORTANTE: Usar null para campos que van a columnas Integer en la BD
    setValues({
        nombres: '',
        apellido_paterno: '',
        apellido_materno: '',
        empresa: '',
        cargo: '',
        pais: 75, // O null si prefieres que se borre
        departamento: null,
        provincia: null,
        distrito: null,
        correo: '',
        celular: '',
        direccionPersona: '',
        sexo: '',
        fecha_nacimiento: null
    });
};


const onlyAlphanumericKey = (event) => {
    const charCode = event.which ? event.which : event.keyCode;
    const charStr = String.fromCharCode(charCode);

    // 1. Permitir teclas de control
    if ([8, 9, 13, 27, 37, 38, 39, 40].includes(charCode)) return true;

    // 2. Validar Alfanumérico
    if (!/^[a-zA-Z0-9]+$/.test(charStr)) {
        event.preventDefault();
        alphanumericMessage.value = "Only letters and numbers are allowed (no spaces or symbols)";

        setTimeout(() => {
            alphanumericMessage.value = '';
        }, 3000);

        return false;
    }
    return true;
};

const esDNI = computed(() => tipo_doc.value === 1);
// Creamos estados individuales para los campos principales
const bloqueoNombres = computed(() => esCampoBloqueado(nombres.value));
const bloqueoApellidos = computed(() => esCampoBloqueado(apellido_paterno.value));
const bloqueoApellidoMaterno = computed(() => esCampoBloqueado(apellido_materno.value));
const bloqueoCorreo = computed(() => esCampoBloqueado(correo.value));
const bloqueoCelular = computed(() => esCampoBloqueado(celular.value));
const bloqueoFechaNac = computed(() => esCampoBloqueado(fecha_nacimiento.value));
const bloqueoPais = computed(() => esCampoBloqueado(pais.value));
const bloqueoDepartamento = computed(() => esCampoBloqueado(departamento.value));
const bloqueoProvincia = computed(() => esCampoBloqueado(provincia.value));
const bloqueoDistrito = computed(() => esCampoBloqueado(distrito.value));
const bloqueoSexo = computed(() => esCampoBloqueado(sexo.value));
const bloqueoCargo = computed(() => esCampoBloqueado(cargo.value));
const bloqueoEmpresa = computed(() => esCampoBloqueado(empresa.value));
const bloqueoDireccion = computed(() => esCampoBloqueado(direccionPersona.value));


// EXTREMA SEGURIDAD: Watcher para limpiar si pegan texto con símbolos
watch(documento, (newValue) => {
    if (tipo_doc.value !== 1 && newValue) { // Solo si NO es DNI
        const cleaned = newValue.replace(/[^a-zA-Z0-9]/g, '');
        if (cleaned !== newValue) {
            documento.value = cleaned;
            alphanumericMessage.value = "Special characters were removed";
            setTimeout(() => { alphanumericMessage.value = ''; }, 3000);
        }
    }
});

onMounted(() => {
    tipo_doc.value = 1;
    pais.value = 75;
});


</script>

<template>
    <div class="font-bold p-4 relative">
        <div class="flex justify-end pr-2 mb-4">
            <Button @click="goToHome" class="wmc-btn-international shadow-xl flex items-center">
                <i class="pi pi-home mr-3 text-lg"></i>
                <div class="flex flex-col items-start leading-none">
                    <span class="text-xs font-bold uppercase tracking-[0.3em] ml-4">Menu Principal</span>
                </div>
            </Button>
        </div>

        <Card class="mt-5 overflow-hidden shadow-lg border border-gray-200">
            <template #header>
                <div class="w-full py-3 text-xl font-bold text-center bg-lightblue-wmc border-blue-wmc">
                    Buscar y Validar Documento
                </div>
            </template>
            <template #content>
                <div class="w-full px-4 pb-2">
                    <div class="flex items-start p-3 mb-5 bg-blue-50 border border-blue-200 rounded-lg shadow-sm">
                        <i class="pi pi-shield text-blue-600 text-2xl mr-3 mt-1"></i>
                        <div>
                            <span class="block text-sm font-bold text-blue-900 mb-1">
                                Protección de Datos Garantizada
                            </span>
                            <p class="text-[11px] text-blue-800 leading-relaxed pr-2">
                                Toda la información proporcionada está <b>encriptada y se transmite de forma segura</b>.
                                Sus datos personales están estrictamente protegidos bajo las Leyes Internacionales de
                                Privacidad de Datos
                                (incluyendo el RGPD) y serán utilizados <b>exclusivamente</b> para su registro y
                                participación en
                                <strong>PROEXPLO 2026</strong>.
                            </p>
                        </div>
                    </div>
                    <div v-if="searchSuccess && !noEncontrado"
                        class="flex flex-col p-4 mb-4 text-green-800 border-t-4 border-green-300 bg-green-50 rounded-lg"
                        role="alert">
                        <div class="flex items-center">
                            <i class="pi pi-check-circle mr-2 text-xl"></i>
                            <span class="text-sm font-bold uppercase tracking-wide">Éxito</span>
                        </div>
                        <div class="mt-2 text-sm font-medium leading-tight">
                            Datos encontrados y cargados correctamente.
                        </div>
                    </div>

                    <div v-if="hasSearched && noEncontrado"
                        class="flex flex-col p-4 mb-4 text-blue-800 border-t-4 border-blue-300 bg-blue-50 rounded-lg"
                        role="alert">
                        <div class="flex items-center">
                            <i class="pi pi-info-circle mr-2 text-xl"></i>
                            <span class="text-sm font-bold uppercase tracking-wide">Información</span>
                        </div>
                        <div class="mt-2 text-sm font-medium leading-tight">
                            No se encontraron registros. Por favor, complete sus datos manualmente en el formulario.
                        </div>
                    </div>
                    <div v-if="hasSearched && esSocio && !noEncontrado && esCategoriaDeSocio"
                        class="flex flex-col p-4 mb-4 text-green-800 border-t-4 border-green-300 bg-green-50 rounded-lg"
                        role="alert">

                        <div class="flex items-center">
                            <i class="pi pi-check-circle mr-2 text-xl"></i>
                            <div class="text-sm font-medium">
                                Verificación exitosa. Usted es un <strong>Socio Activo</strong>.
                            </div>
                        </div>

                        <div v-if="[1, 5].includes(props.perfil_id)" class="mt-3 ml-7 pt-3 border-t border-green-200">
                            <p class="text-xs leading-relaxed">
                                Si desea <strong>editar sus datos personales</strong>, por favor contacte a nuestra
                                Coordinadora de Asociados:
                            </p>
                            <div class="mt-2 flex flex-col sm:flex-row sm:gap-6 text-xs italic">
                                <span class="font-bold text-green-900">
                                    <i class="pi pi-user mr-1"></i> Liset Otoya
                                </span>
                                <span>
                                    <i class="pi pi-whatsapp mr-1"></i>
                                    <a href="https://wa.me/51982097019" target="_blank" class="hover:underline">+51 982
                                        097 019</a>
                                </span>
                                <span>
                                    <i class="pi pi-envelope mr-1"></i>
                                    <a href="mailto:Liset.otoya@iimp.org.pe"
                                        class="hover:underline">Liset.otoya@iimp.org.pe</a>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div v-if="hasSearched && !esSocio && esCategoriaDeSocio"
                        class="flex flex-col p-4 mb-4 text-red-800 border-t-4 border-red-300 bg-red-50 rounded-lg"
                        role="alert">
                        <div class="flex items-center">
                            <i class="pi pi-exclamation-triangle mr-2 text-xl"></i>
                            <span class="text-sm font-bold">Usted no es asociado.</span>
                        </div>
                        <div class="mt-2 text-sm">
                            Esta categoría es exclusiva para asociados. Por favor, contacte a <a
                                href="mailto:asociados@iimp.org.pe"
                                class="font-bold underline">asociados@iimp.org.pe</a> o cambie su categoría.
                        </div>
                    </div>

                </div>

                <!-- <div v-if="camposBloqueados"
                    class="mx-6 mb-2 p-2 bg-yellow-50 text-yellow-700 border-l-4 border-yellow-400 text-xs font-semibold">
                    <i class="pi pi-lock mr-2"></i> PLEASE SEARCH BY DOCUMENT NUMBER TO UNLOCK THESE FIELDS
                </div> -->

                <div v-if="mostrarBannerBloqueo"
                    class="mx-6 mb-2 p-2 bg-orange-50 text-orange-700 border-l-4 border-orange-400 text-xs font-semibold">
                    <i class="pi pi-lock mr-2 text-[10px]"></i>

                    <span v-if="[1, 5].includes(props.perfil_id)">
                        Sus datos están precargados por su perfil y no pueden ser modificados.
                    </span>

                    <span v-else>
                        Por favor, busque por número de documento para desbloquear estos campos.
                    </span>
                </div>
                <div class="flex gap-6 p-2 w-full justify-around">
                    <div
                        class="text-green-iimp font-bold max-w-[650px] w-full p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="col-span-1">
                            <label for="tipo_doc">Tipo de Documento <span class="text-red-600">*</span></label>
                            <Select name="tipo_doc" v-model="tipo_doc" v-bind="tipo_docAttrs" translate="no"
                                :options="tiposDocumentoFiltrados" @change="clearDocument" optionLabel="name_en"
                                optionValue="id" placeholder="Seleccionar documento" showClear checkmark
                                class="w-full border-green-iimp" :class="{ 'bg-gray-100 opacity-70': esDNI }" />
                            <small class="text-red-600">{{ errors.tipo_doc }}</small>
                        </div>
                        <div class="col-span-1">
                            <label for="documento" translate="no">
                                {{ 'Numero de Documento' }} <span class="text-red-600">*</span>
                            </label>
                            <InputGroup>
                                <InputText name="documento" v-model="documento" v-bind="documentoAttrs"
                                    class="w-full border-green-iimp"
                                    :placeholder="esDNI ? 'Ingrese 8 dígitos' : 'Ingrese número de documento'"
                                    :maxlength="esDNI ? 8 : 15" :disabled="loadingSearch"
                                    @keypress="esDNI ? onlyNumberKey($event) : onlyAlphanumericKey($event)" />

                                <Button icon="pi pi-search" @click="searchPerson" :loading="loadingSearch"
                                    class="!bg-orange-600 !border-orange-600 hover:!bg-orange-500 hover:!border-orange-500 !text-white !shadow-none"
                                    :pt="{
                                        root: { class: 'bg-orange-600 border-orange-600' },
                                        loadingIcon: { class: 'text-white' }
                                    }" />
                            </InputGroup>

                            <small v-if="dniMessage" class="text-orange-600 font-bold block mt-1">
                                <i class="pi pi-info-circle mr-1"></i> {{ dniMessage }}
                            </small>
                            <div v-else-if="alphanumericMessage"
                                class="text-orange-600 font-bold block mt-1 animate-pulse">
                                <i class="pi pi-exclamation-triangle mr-1"></i> {{ alphanumericMessage }}
                            </div>
                            <small v-else class="text-red-600 block mt-1">{{ errors.documento }}</small>
                        </div>
                    </div>
                </div>
            </template>
        </Card>
        <!--PERSONAL DATAILS/
        /==============================  -->
        <Card class="mt-5 overflow-hidden shadow-lg border border-gray-200">
            <template #header>
                <div class="w-full py-3 text-xl font-bold text-center bg-lightblue-wmc border-blue-wmc">Detalles
                    Personales
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
                                <span class="text-sm font-bold">Información Requerida</span>
                            </div>
                            <div class="mt-2 text-sm">
                                Por favor complete los siguientes campos para continuar con su inscripción:
                                <ul class="list-disc ml-5 mt-1">
                                    <li v-for="field in missingFields" :key="field">{{ field }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- NOMBRES Y APELLIDOS -->
                    <div class="grid gap-6 m-6 md:grid-cols-2">
                        <div class="w-full">
                            <label for="nombres">Nombres <span class="text-red-600">*</span></label>
                            <InputText name="nombres" v-model="nombres" v-bind="nombresAttrs" :disabled="bloqueoNombres"
                                class="w-full border-green-iimp" />
                            <small class="text-red-600">{{ errors.nombres }}</small>
                        </div>
                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="w-full">
                                <label for="apellido_paterno">Apellido Paterno <span
                                        class="text-red-600">*</span></label>
                                <InputText name="apellido_paterno" v-model="apellido_paterno"
                                    v-bind="apellido_paternoAttrs" :disabled="bloqueoApellidos"
                                    class="w-full border-green-iimp" />
                                <small class="text-red-600">{{ errors.apellido_paterno }}</small>
                            </div>

                            <div class="col-span-3 sm:col-span-1">
                                <label for="apellido_materno" class="">Apellido Materno <span
                                        class="text-red-600">*</span></label>
                                <InputText name="apellido_materno" v-model="apellido_materno"
                                    :disabled="bloqueoApellidoMaterno" v-bind="apellido_maternoAttrs"
                                    class="w-full border-green-iimp" />
                                <small class="text-red-600">{{ errors.apellido_materno }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-6 m-6 md:grid-cols-2">
                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="col-span-3 sm:col-span-1">
                                <label for="correo" class="">Correo Electronico <span
                                        class="font-normal text-red-600">*</span></label>
                                <InputText name="correo" v-model="correo" v-bind="correoAttrs" :disabled="bloqueoCorreo"
                                    class="w-full border-green-iimp" />
                                <small class=" text-red-600">{{ errors.correo }}</small>
                            </div>
                            <div class="col-span-3 sm:col-span-1">
                                <label for="celular" class="">Celular <span
                                        class="font-normal text-red-600">*</span></label>
                                <InputText name="celular" v-model="celular" v-bind="celularAttrs"
                                    @keypress="onlyPhoneKeys" :disabled="bloqueoCelular"
                                    class="w-full border-green-iimp" placeholder="+51999888777 or 999888777" />
                                <small class="text-red-600">{{ errors.celular }}</small>
                            </div>
                        </div>
                        <div class="w-full sm:col-span-1">
                            <label for="direccionPersona">Dirección <span class="text-red-600">*</span></label>
                            <InputText name="direccionPersona" v-model="direccionPersona" v-bind="direccionPersonaAttrs"
                                :disabled="bloqueoDireccion" class="w-full border-green-iimp" />
                            <small class="text-red-600">{{ errors.direccionPersona }}</small>
                        </div>
                    </div>

                    <!-- UBICACION -->
                    <div class="grid gap-6 m-6 md:grid-cols-4">

                        <div class="w-full md:col-span-1">
                            <label for="pais">Pais <span class="text-red-600">*</span></label>
                            <Select name="pais" v-model="pais" v-bind="paisAttrs" :options="paises" optionLabel="name"
                                optionValue="id" placeholder="Select" showClear filter @change="loadDepartamentos" :disabled="bloqueoPais"
                                translate="no" class="w-full border-green-iimp" />
                            <small class="text-red-600">{{ errors.pais }}</small>
                        </div>

                        <div class="col-span-3 sm:col-span-1">
                            <label for="departamento" class="">Departamento</label>
                            <Select name="departamento" v-model="departamento" v-bind="departamentoAttrs" filter
                                @change="loadProvincias" :options="departamentos" optionLabel="name" :disabled="bloqueoDepartamento"
                                optionValue="id_departamento" placeholder="Elegir" showClear
                                class="w-full border-green-iimp" />
                            <span class="font-normal text-red-600">{{ errors.departamento }}</span>
                        </div>

                        <div class="w-full sm:col-span-1">
                            <label for="provincia" class="">Provincia</label>
                            <Select name="provincia" v-model="provincia" v-bind="provinciaAttrs" filter
                                @change="loadDistritos" :options="provincias" optionLabel="name"  :disabled="bloqueoProvincia"
                                optionValue="id_provincia" placeholder="Elegir" showClear
                                class="w-full border-green-iimp" />
                            <span class="font-normal text-red-600">{{ errors.provincia }}</span>
                        </div>
                        <div class="w-full sm:col-span-1">
                            <label for="distrito" class="">Distrito</label>
                            <Select name="distrito" v-model="distrito" v-bind="distritoAttrs" filter :disabled="bloqueoDistrito"
                                :options="distritos" optionLabel="name" optionValue="id_distrito" placeholder="Elegir"
                                showClear class="w-full border-green-iimp" />
                            <span class="font-normal text-red-600">{{ errors.distrito }}</span>
                        </div>
                    </div>

                    <div class="grid gap-6 m-6 grid-cols-1 md:grid-cols-4">
                        <div class="w-full">
                            <label for="sexo" class="">Sexo <span class="font-normal text-red-600"> *</span></label>
                            <Select name="sexo" v-model="sexo" v-bind="sexoAttrs" optionLabel="label"
                                optionValue="value" placeholder="Selecccionar" showClear checkmark :options="generos"
                                :disabled="bloqueoSexo" class="w-full border-green-iimp" />
                            <small class=" text-red-600">{{ errors.sexo }}</small>
                        </div>
                        <div class="w-full">
                            <label for="fecha_nacimiento">Fecha de Nacimiento <span
                                    class="text-red-600">*</span></label>
                            <InputGroup class="w-full h-[42px]">
                                <InputGroupAddon class="border-green-iimp border-r-0 bg-white text-green-iimp">
                                    <i class="pi pi-calendar"></i>
                                </InputGroupAddon>
                                <Calendar name="fecha_nacimiento" v-model="fecha_nacimiento"
                                    v-bind="fecha_nacimientoAttrs" :maxDate="maxAdultDate" dateFormat="yy-mm-dd"
                                    :showTime="false" placeholder="YYYY-MM-DD" class="w-full"
                                    :disabled="bloqueoFechaNac"
                                    inputClass="w-full border-green-iimp border-l-0 shadow-none outline-none bg-white" />
                            </InputGroup>
                            <small class=" text-red-600">{{ errors.fecha_nacimiento }}</small>
                        </div>

                        <div class="w-full">
                            <label for="empresa" class="">Empresa <span class="text-red-600">*</span></label>
                            <InputText name="empresa" v-model="empresa" v-bind="empresaAttrs" :disabled="bloqueoEmpresa"
                                class="w-full border-green-iimp" />
                            <small class=" text-red-600">{{ errors.empresa }}</small>
                        </div>

                        <div class="w-full sm:col-span-1">
                            <label for="cargo" class="">Cargo <span class="text-red-600">*</span></label>
                            <InputText name="cargo" v-model="cargo" v-bind="cargoAttrs" :disabled="bloqueoCargo"
                                class="w-full border-green-iimp" />
                            <small class="text-red-600">{{ errors.cargo }}</small>
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
