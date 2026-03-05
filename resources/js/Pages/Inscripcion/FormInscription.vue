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
import { ref, onMounted, computed, watch, nextTick, onUnmounted } from 'vue'; // Agregado nextTick
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
const fieldNames = {
    selected_categoria: 'Registration Category',
    tipoDocumentoEmpresa: 'Billing Document Type',
    documentoEmpresa: 'Billing Document Number',
    razonSocial: 'Business Name / Full Name',
    direccionEmpresa: 'Billing Address',
    responsable: 'Billing Contact Name',
    correo_facturador: 'Billing Email',
    reglamento: 'Terms and Conditions Acceptance',
    uploadDocument: 'Category Required Document'
};


const es_socio = ref(false);
const loading_doc = ref(false);
const show_days = ref(false);
const show_document = ref(false);
const upload_instruction = ref('');
const showManualAlert = ref(false);
const showSuccessAlert = ref(false);
const total = ref(0);
const src = ref(null);
const block_direction = ref(false);
const fileErrors = ref([]);
const maxSize = 6291456;
const allowedTypes = ['application/pdf', 'image/png', 'image/jpg', 'image/jpeg'];
const fileupload = ref(null);
const alphanumericMessage = ref('');
// Agregamos un estado para controlar si el usuario puede editar manualmente
const isEditingBilling = ref(false);
const dniMessage = ref('');
let current_price = 0;
const tipoDocumento = computed(() => page.props.general.tipDocEmp)


const departamentos = ref();
const days = { 'mie': 'Wednesday', 'jue': 'Thursday', 'vie': 'Friday' };
const current_days = { 'lun': false, 'mar': false, 'mie': false, 'jue': false, 'vie': false };

const formManualErrors = ref({ reglamento: null, total: null, uploadDocument: null });

const { defineField, errors, setValues, values, validate } = useForm({
    validationSchema: yup.object({
        // selected_categoria: yup.mixed().required('La categoría es obligatoria'),
        // tipoDocumentoEmpresa: yup.mixed().required('El tipo de documento es obligatorio'),
        // documentoEmpresa: yup.string()
        //     .required('El número de documento es obligatorio')
        //     .test('len', 'Formato inválido', function (value) {
        //         const { tipoDocumentoEmpresa } = this.parent;

        //         // --- VALIDACIÓN PARA PERUANOS ---
        //         if (tipoDocumentoEmpresa === 1) { // DNI
        //             return value?.length === 8 || this.createError({ message: 'El DNI debe tener exactamente 8 dígitos' });
        //         }
        //         if (tipoDocumentoEmpresa === 2) { // RUC
        //             return value?.length === 11 || this.createError({ message: 'El RUC debe tener exactamente 11 dígitos' });
        //         }

        //         // --- FLUJO EXTRANJERO ---
        //         return value?.length > 0;
        //     }),
        // razonSocial: yup.string().required('La razón social es obligatoria'),
        // direccionEmpresa: yup.string().required('La dirección de la empresa es obligatoria'),
        // responsable: yup.string().required('El nombre del responsable es obligatorio'),
        // correo_facturador: yup.string()
        //     .email('Formato de correo inválido')
        //     .required('El correo de facturación es obligatorio'),
        // reglamento: yup.boolean().oneOf([true], 'Debe aceptar el reglamento'),
    })
});

const dniMessageEmpresa = ref('');

const [documentoEmpresa] = defineField('documentoEmpresa');
const [razonSocial] = defineField('razonSocial');
const [responsable] = defineField('responsable');
const [correo_facturador] = defineField('correo_facturador');
const [tipoDocumentoEmpresa] = defineField('tipoDocumentoEmpresa');
const [direccionEmpresa] = defineField('direccionEmpresa');
const [selectTipoPago] = defineField('selectTipoPago');
const [selectTipoDocPago] = defineField('selectTipoDocPago');
const [selected_categoria, selected_categoriaAttrs] = defineField('selected_categoria');
const [selectedDays, selectedDaysAttrs] = defineField('selectedDays');
const [uploadDocument] = defineField('uploadDocument');


const is_category_fixed = ref(false);
const urlParams = new URLSearchParams(window.location.search);
const esSeccionViajes = computed(() => urlParams.get('section') === 'viajes');

function changeCategory(id, precioRecibido) {
    if (!id) return;

    const categoria = props.categorias.find(c => c.id === id);
    if (!categoria) return;

    // LÓGICA CRÍTICA: Forzamos a 0 si la sección es 'viajes'
    if (esSeccionViajes.value) {
        current_price = 0;
    } else {
        current_price = (precioRecibido > 0) ? precioRecibido : (categoria.precio_disponible?.valor || 0);
    }

    nextTick(() => {
        show_document.value = Boolean(categoria.requiere_documento);

        if (show_document.value) {
            const nombre = categoria.nombre_en.toUpperCase();

            if (nombre.includes('STUDENT') || nombre.includes('ESTUDIANTE')) {
                // Mensaje para Estudiantes (Quinto Superior)
                upload_instruction.value = "Para acceder a esta tarifa es obligatorio presentar una constancia de pertenencia al quinto superior.";
            }
            else if (nombre.includes('FACULTY') || nombre.includes('DOCENTE')) {
                // Mensaje para Docentes (Carta de institución)
                upload_instruction.value = "Para acceder a esta tarifa es obligatorio presentar una carta de la institución donde labora. (No aplica a docentes de postgrado).";
            }
            else {
                // Mensaje por defecto para otras categorías que pidan documento
                upload_instruction.value = "Por favor, adjunte el documento de sustento requerido para esta categoría.";
            }
        }

        if (id == 39) {
            show_days.value = true;
            // Al multiplicar por current_price (que es 0), el total será 0
            let count = Object.values(current_days).filter(v => v).length;
            total.value = count * current_price;
        } else {
            show_days.value = false;
            total.value = current_price; // Será 0
        }
    });
}



const getInscripcion = async () => {
    const result = await validate();
    formManualErrors.value.total = null;

    if (selected_categoria.value == 39) {
        const tieneDias = Object.values(current_days).some(v => v === true);
        if (!tieneDias) {
            formManualErrors.value.total = "Attention: You must select at least one day.";
            return { validate: false };
        }
    }

    // Si es viajes, permitimos pasar con 0
    const totalValido = esSeccionViajes.value ? true : (total.value > 0);

    console.log("Validación de formulario:", {
        valid: result.valid,
        total: total.value,
        show_document: show_document.value,
        uploadDocument: uploadDocument.value,
        totalValido
    });

    // if (!result.valid || !totalValido || (show_document.value && !uploadDocument.value)) {
    //     return { validate: false };
    // }

    return {
        validate: true,
        formInscription: values,
        total_final: total.value // Aquí enviará 0 si es viajes
    };
};

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


    console.log("Mounted FormInscription with props.data_persona:", props.data_persona);

});



watch(() => props.data_persona, (newVal) => {
    if (newVal && newVal.persona) {
        fillBillingData(newVal.persona);
    }
}, { immediate: true, deep: true });

// Busca este watcher en tu código y modifícalo así:
// watch(selected_categoria, (newId) => {
//     if (!newId) return; // Si es nulo, no hacer nada

//     const cat = props.categorias.find(c => c.id === newId);
//     if (cat) {
//         changeCategory(newId, cat.precio_disponible?.valor || 0);
//     }
// });

// Busca esto y cámbialo para asegurar que encuentre el precio
watch(selected_categoria, (newId) => {
    if (!newId) return;

    // Forzamos la búsqueda en el array de categorías
    const lista = Object.values(props.categorias); // Por si viene como objeto desde PHP
    const cat = lista.find(c => c.id == newId);

    if (cat) {
        // Asegúrate de pasar el valor numérico
        const precio = cat.precio_disponible?.valor || 0;
        changeCategory(newId, precio);
    }
});

watch(documentoEmpresa, (newVal) => {
    if (!newVal) return;

    // Solo aplicamos restricción numérica y de longitud para DNI (1) y RUC (2)
    if (tipoDocumentoEmpresa.value === 1 || tipoDocumentoEmpresa.value === 2) {
        // 1. Eliminar todo lo que no sea número (por si pegan texto)
        const cleanedValue = newVal.replace(/\D/g, '');

        // 2. Definir el máximo permitido
        const maxLength = tipoDocumentoEmpresa.value === 1 ? 8 : 11;

        // 3. Aplicar recortes si excede el largo
        if (newVal !== cleanedValue || newVal.length > maxLength) {
            documentoEmpresa.value = cleanedValue.slice(0, maxLength);
        }
    }
});

const onlyAlphanumericKey = (event) => {
    const charCode = event.which ? event.which : event.keyCode;
    const charStr = String.fromCharCode(charCode);

    // 1. Permitir teclas de control
    if ([8, 9, 13, 27, 37, 38, 39, 40].includes(charCode)) return true;

    // 2. Validar Alfanumérico
    if (!/^[a-zA-Z0-9]+$/.test(charStr)) {
        event.preventDefault();
        alphanumericMessage.value = "Solo se permiten letras y números (sin espacios ni símbolos)";

        setTimeout(() => {
            alphanumericMessage.value = '';
        }, 3000);

        return false;
    }
    return true;
};


watch(documentoEmpresa, (newValue) => {
    // CAMBIO AQUÍ: tipoDocumentoEmpresa en lugar de tipo_doc
    if (tipoDocumentoEmpresa.value !== 1 && newValue) {
        const cleaned = newValue.replace(/[^a-zA-Z0-9]/g, '');
        if (cleaned !== newValue) {
            documentoEmpresa.value = cleaned;
            alphanumericMessage.value = "Special characters were removed";
            setTimeout(() => { alphanumericMessage.value = ''; }, 3000);
        }
    }
});

watch(tipoDocumentoEmpresa, (newVal, oldVal) => {
    // Definimos qué IDs son de extranjeros (generalmente todo lo que no es 1 o 2)
    const documentosExtranjeros = [3, 4, 5]; // Ajusta estos IDs según tu base de datos (Pasaporte, CE, etc.)

    // LÓGICA:
    // Si el valor anterior era extranjero Y el nuevo también es extranjero, NO limpiamos.
    const ambosSonExtranjeros = documentosExtranjeros.includes(oldVal) && documentosExtranjeros.includes(newVal);

    if (isEditingBilling.value && oldVal !== undefined && !ambosSonExtranjeros) {

        setValues({
            ...values,
            documentoEmpresa: '',
            razonSocial: '',
            direccionEmpresa: '',
            responsable: '',
            correo_facturador: ''
        });

        setTipoDocPago();
    } else {
        // console.log("Cambio entre documentos extranjeros: Se mantiene la información.");
    }
});

const loadDepartamentos = async () => {
    departamentos.value = await Functions.loadDepartamentos(pais.value);
}


const getEmpresaData = async () => {
    // 1. Limpieza preventiva: Blanqueamos campos y ocultamos alertas previas
    razonSocial.value = '';
    direccionEmpresa.value = '';
    showManualAlert.value = false;
    showSuccessAlert.value = false;

    // Sincronizamos con vee-validate para que no queden valores viejos en el objeto 'values'
    setValues({
        ...values,
        razonSocial: '',
        direccionEmpresa: ''
    });

    if (!documentoEmpresa.value) return;

    loading_doc.value = true;

    try {
        const empresaData = await Functions.getEmpresaData(documentoEmpresa.value, tipoDocumentoEmpresa.value);

        // Si la API responde con éxito y trae datos
        if (empresaData?.status && empresaData?.empresa) {
            razonSocial.value = empresaData.empresa.nombre;
            direccionEmpresa.value = empresaData.empresa.direccionEmpresa;

            // Mostrar Alerta de Éxito
            showSuccessAlert.value = true;

            block_direction.value = false;
            isEditingBilling.value = true;
            // Actualizar vee-validate
            setValues({
                ...values,
                razonSocial: empresaData.empresa.nombre,
                direccionEmpresa: empresaData.empresa.direccionEmpresa
            });
        } else {
            // Caso: No encontrado
            showManualAlert.value = true;
            isEditingBilling.value = true;
            block_direction.value = false;
        }
    } catch (e) {
        console.error(e);
        showManualAlert.value = true;
        isEditingBilling.value = true;
    } finally {
        loading_doc.value = false;
    }
}

const onFileSelect = (event) => {
    // 1. Verificamos que haya archivo
    if (!event.files || event.files.length === 0) return;

    const file = event.files[0];

    // 2. Reiniciamos errores y variables
    fileErrors.value = [];
    uploadDocument.value = file;
    src.value = null;

    // --- VALIDACIÓN 1: FORMATO ---
    if (!allowedTypes.includes(file.type)) {
        fileErrors.value.push("Invalid file format. Only PDF documents or Images (PNG, JPG, JPEG) are accepted.");
    }

    // --- VALIDACIÓN 2: TAMAÑO (6MB) ---
    if (file.size > maxSize) {
        fileErrors.value.push("File size exceeds the limit. Maximum allowed is 6MB.");
    }

    // 3. Si hay errores, limpiamos la selección para que el usuario deba elegir otro
    if (fileErrors.value.length > 0) {
        uploadDocument.value = null;
        if (fileupload.value) {
            fileupload.value.clear(); // Limpia el componente visualmente
        }
        return;
    }

    // 4. Si todo está OK, generar vista previa
    const reader = new FileReader();
    reader.onload = (e) => {
        if (file.type === "application/pdf") {
            src.value = '/images/pdf-file-document.png'; // Icono para PDFs
        } else {
            src.value = e.target.result; // Imagen real para PNG/JPG
        }
    };
    reader.readAsDataURL(file);
}

function selectDays(id) {
    current_days[id] = !current_days[id];
    let count = Object.values(current_days).filter(v => v).length;
    total.value = count * current_price;
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


const filteredDocTypes = computed(() => {
    const p = props.data_persona?.persona || props.data_persona;

    // Es peruano si el país es 1
    // O si ya trae un tipo_doc 1 (DNI) o 2 (RUC) aunque el ID de país diga otra cosa
    const esPeruano = p?.pais == 1 ||
        p?.id_pais == 1 ||
        p?.tipo_doc == 1 ||
        p?.tipo_doc == 2 ||
        p?.nacionalidad?.toLowerCase() === 'peruano';

    // console.log("¿Es detectado como Peruano?:", esPeruano);

    if (!tipoDocumento.value) return [];

    if (esPeruano) {
        // Retorna SOLO DNI (1) y RUC (2)
        const filtrados = tipoDocumento.value.filter(d => d.id == 1 || d.id == 2);
        // console.log("Documentos para Peruano:", filtrados);
        return filtrados;
    } else {
        // Retorna PASAPORTE, CE, etc. (quita DNI y RUC)
        const filtrados = tipoDocumento.value.filter(d => d.id != 1 && d.id != 2);
        // console.log("Documentos para Extranjero:", filtrados);
        return filtrados;
    }
});

const missingFields = computed(() => {
    return Object.keys(errors.value).map(key => fieldNames[key] || key);
});

const onlyNumberKey = (event) => {
    const charCode = event.which ? event.which : event.keyCode;

    // Si es DNI o RUC, solo permitir números
    if (tipoDocumentoEmpresa.value === 1 || tipoDocumentoEmpresa.value === 2) {
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            event.preventDefault();
            return false;
        }

        // Bloquear si ya llegó al máximo permitido
        const max = tipoDocumentoEmpresa.value === 1 ? 8 : 11;

        if (documentoEmpresa.value?.length >= max) {
            // --- NUEVO: Activación del mensaje ---
            dniMessageEmpresa.value = `Solo se permiten ${max} dígitos`;

            // Limpiamos el mensaje después de 3 segundos
            setTimeout(() => {
                dniMessageEmpresa.value = '';
            }, 3000);
            // -------------------------------------

            event.preventDefault();
            return false;
        } else {
            // Si el usuario está escribiendo y aún no llega al máximo, limpiamos el mensaje
            dniMessageEmpresa.value = '';
        }
    }
    return true;
}

// const onlyAlphanumericKey = (event) => {
//     const charCode = event.which ? event.which : event.keyCode;
//     const charStr = String.fromCharCode(charCode);

//     // Regex: Permite solo letras (a-z, A-Z) y números (0-9)
//     if (!/^[a-zA-Z0-9]+$/.test(charStr)) {
//         event.preventDefault();
//         return false;
//     }
//     return true;
// };


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
                        Detalles de Categoria
                    </div>
                </template>

                <template #content>
                    <div class="px-2">

                        <div v-if="is_category_fixed || esSeccionViajes"
                            class="w-full p-4 bg-blue-50 border border-blue-200 rounded-xl shadow-sm flex justify-between items-center">
                            <div class="flex flex-col">
                                <span class="text-[10px] uppercase text-blue-400 font-black tracking-widest">Selected
                                    Profile</span>
                                <h4 class="text-lg font-bold text-blue-900 leading-tight">
                                    {{categorias.find(c => c.id === selected_categoria)?.nombre_en}}
                                </h4>
                            </div>
                            <div v-if="!esSeccionViajes" class="text-right">
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
                                        <RadioButton v-model="selected_categoria" :value="categoria.id" />
                                    </div>
                                    <div class="flex flex-col sm:flex-row sm:justify-between w-full pl-3 cursor-pointer"
                                        @click="changeCategory(categoria.id, categoria.precio_disponible.valor)">
                                        <label
                                            class="text-sm sm:text-base text-gray-700 leading-tight mb-1 sm:mb-0 cursor-pointer"
                                            :class="{ 'font-bold text-blue-900': selected_categoria === categoria.id }">
                                            {{ es_socio ? categoria.nombre_es :
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
                    <!-- =========== POR DIAS  ========== -->
                    <!-- ================================ -->
                    <Card v-if="show_days" class="mt-6 border border-dashed border-blue-300 bg-blue-50/30">
                        <template #content>
                            <div v-if="formManualErrors.total"
                                class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 flex items-center gap-4 animate-fade-in-down shadow-sm">
                                <div class="bg-red-500 rounded-full p-2 flex-none">
                                    <i class="pi pi-exclamation-triangle text-white text-lg"></i>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-red-800 font-black text-sm uppercase">Selección Requerida</span>
                                    <p class="text-red-700 text-sm font-medium leading-tight">
                                        {{ formManualErrors.total }}
                                    </p>
                                </div>

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
                            <span class="text-[10px] text-blue-700 font-bold italic uppercase tracking-wider">
                                * Rate per day of attendance
                            </span>
                        </template>
                    </Card>
                    <!-- =========== CARGAR DOCUMENTO  ========== -->
                    <!-- ================================= -->
                    <Card v-if="show_document" class="mt-6 border border-dashed border-green-300">
                        <template #content>
                            <div v-if="show_document && !uploadDocument"
                                class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 flex items-center gap-4 animate-fade-in-down shadow-sm">
                                <div class="bg-red-500 rounded-full p-2 flex-none">
                                    <i class="pi pi-exclamation-triangle text-white text-lg"></i>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-red-800 font-black text-sm uppercase">Documento Requerido</span>
                                    <p class="text-red-700 text-sm font-medium leading-tight">
                                        La categoría seleccionada requiere un <strong>archivo adjunto</strong>. Por
                                        favor, cargue su documento a continuación.
                                    </p>
                                </div>
                            </div>

                            <div v-if="upload_instruction"
                                class="mb-4 p-4 bg-blue-50 border-l-4 border-blue-500 text-blue-700">
                                <p class="text-sm font-bold">Requerimiento:</p>
                                <p class="text-sm">{{ upload_instruction }}</p>
                            </div>

                            <div class="flex justify-center mb-4 w-full">
                                <img v-if="src" :src="src" alt="Preview"
                                    class="shadow-md rounded-lg border border-gray-200 max-w-[200px] max-h-[200px] object-contain" />
                            </div>

                            <div class="flex flex-col items-center justify-center w-full">
                                <!-- <div v-if="formManualErrors.uploadDocument"
                                    class="w-full mb-4 flex items-center gap-3 rounded border-l-4 border-red-500 bg-red-50 px-4 py-2 text-red-800 shadow-sm">
                                    <i class="pi pi-times-circle"></i>
                                    <span class="text-xs font-bold">{{ formManualErrors.uploadDocument }}</span>
                                </div> -->
                                <div v-if="fileErrors.length > 0"
                                    class="w-full md:w-3/4 mb-4 p-3 bg-red-50 border border-red-200 rounded-md text-center mx-auto animate-fade-in">
                                    <div v-for="(error, index) in fileErrors" :key="index"
                                        class="flex items-center justify-center gap-2 text-red-600 font-bold mb-1 last:mb-0">
                                        <i class="pi pi-exclamation-triangle"></i>
                                        <span class="text-sm">{{ error }}</span>
                                    </div>
                                </div>
                                <FileUpload ref="fileupload" mode="basic"
                                    class="p-button-outlined text-green-iimp mx-auto" :auto="true" customUpload
                                    :chooseLabel="'Adjuntar Documento'" @select="onFileSelect" name="uploadDocument" />

                                <small class="text-slate-500 mt-3 text-center block text-xs">
                                    Aceptado: PDF, JPG, PNG (Máx. 6MB)
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
                    <div class="w-full py-3 text-xl font-bold text-center bg-lightblue-wmc border-blue-wmc mb-2">
                        Información de Facturación
                    </div>
                    <div v-if="missingFields.length > 0"
                        class="flex flex-col p-4 mb-6 text-orange-800 border-t-4 border-orange-300 bg-orange-50 rounded-lg shadow-sm"
                        role="alert">
                        <div class="flex items-center">
                            <i class="pi pi-exclamation-circle mr-2 text-xl"></i>
                            <span class="text-sm font-bold">Información de facturación incompleta</span>
                        </div>
                        <div class="mt-2 text-sm">
                            Por favor, complete los siguientes campos obligatorios para continuar con el pago:
                            <ul class="list-disc ml-5 mt-1 font-semibold">
                                <li v-for="field in missingFields" :key="field">{{ field }}</li>
                            </ul>
                        </div>
                    </div>

                    <div v-if="tipoDocumentoEmpresa === 1 || tipoDocumentoEmpresa === 2"
                        class="col-span-2 mb-4 p-3 rounded-lg border flex items-center gap-3 animate-fade-in"
                        :class="tipoDocumentoEmpresa === 1 ? 'bg-blue-50 border-blue-200' : 'bg-purple-50 border-purple-200'">

                        <div :class="tipoDocumentoEmpresa === 1 ? 'bg-blue-500' : 'bg-purple-500'"
                            class="rounded-full p-2 flex-none">
                            <i class="pi pi-info-circle text-white text-sm"></i>
                        </div>

                        <div class="flex flex-col">
                            <span class="font-black text-[12px] uppercase tracking-wider"
                                :class="tipoDocumentoEmpresa === 1 ? 'text-blue-800' : 'text-purple-800'">
                                {{ tipoDocumentoEmpresa === 1 ? 'Información de Boleta' : 'Información de Factura' }}
                            </span>

                            <p v-if="tipoDocumentoEmpresa === 1" class="text-xs font-medium text-blue-700">
                                <span class="italic opacity-80">
                                    Al seleccionar <strong>DNI</strong>, se emitirá una <strong>Boleta de Venta</strong>
                                    electrónica
                                    a nombre de la persona natural.
                                </span>
                            </p>

                            <p v-else class="text-xs font-medium text-purple-700">
                                <span class="italic opacity-80">
                                    Al seleccionar <strong>RUC</strong>, se emitirá una <strong>Factura
                                        Comercial</strong> electrónica
                                    a nombre de la empresa o entidad legal.
                                </span>
                            </p>
                        </div>
                    </div>

                </template>

                <template #content>

                    <div v-if="showSuccessAlert"
                        class="mx-6 mb-6 p-4 rounded-xl bg-green-50 border border-green-200 flex items-center gap-4 animate-fade-in shadow-sm">
                        <div class="bg-green-500 rounded-full p-2 flex-none">
                            <i class="pi pi-check-circle text-white text-lg"></i>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-green-900 font-black text-sm uppercase tracking-wide">Success</span>
                            <p class="text-green-800 text-sm font-medium leading-tight">
                                Data found and loaded successfully.
                            </p>
                        </div>
                        <Button icon="pi pi-times" class="p-button-text p-button-rounded text-green-400 ml-auto"
                            @click="showSuccessAlert = false" />
                    </div>
                    <div v-if="showManualAlert"
                        class="mx-6 mb-6 p-4 rounded-xl bg-blue-50 border border-blue-200 flex items-center gap-4 animate-fade-in shadow-sm">
                        <div class="bg-blue-500 rounded-full p-2 flex-none">
                            <i class="pi pi-info-circle text-white text-lg"></i>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-blue-900 font-black text-sm uppercase tracking-wide">Information</span>
                            <p class="text-blue-800 text-sm font-medium leading-tight">
                                Record not found. Please fill in the billing details manually to proceed with your
                                registration for the World Mining Congress.
                            </p>
                        </div>
                        <Button icon="pi pi-times" class="p-button-text p-button-rounded text-blue-400 ml-auto"
                            @click="showManualAlert = false" />
                    </div>

                    <div class="flex justify-center md:justify-end px-6 mb-6">
                        <Button v-if="!isEditingBilling" icon="pi pi-exclamation-circle"
                            label="¿La información es incorrecta? Haz clic aquí para modificar"
                            class="p-button-raised p-button-warning font-bold p-4 shadow-md w-full md:w-auto"
                            style="background-color: #f59e0b; border-color: #d97706; color: #ffffff;"
                            @click="enableManualEdit" />
                        <Button v-else icon="pi pi-check-circle" label="He terminado de editar, guardar cambios"
                            class="p-button-raised p-button-success font-bold p-4 shadow-md w-full md:w-auto"
                            style="background-color: #10b981; border-color: #059669; color: #ffffff;"
                            @click="isEditingBilling = false" />
                    </div>
                    <div class="grid gap-6 m-6 md:grid-cols-2">
                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="col-span-3 sm:col-span-1">
                                <label class="block mb-1">Tipo de Documento <span class="text-red-600">*</span></label>
                                <Select v-model="tipoDocumentoEmpresa" :options="filteredDocTypes" optionLabel="name_en"
                                    optionValue="id" :disabled="!isEditingBilling" class="w-full border-green-iimp"
                                    @change="setTipoDocPago" />
                                <small class="text-red-600" v-if="errors.tipoDocumentoEmpresa">{{
                                    errors.tipoDocumentoEmpresa }}</small>
                            </div>

                            <div class="col-span-3 sm:col-span-1">
                                <label class="block mb-1">Numero de Documento <span
                                        class="text-red-600">*</span></label>
                                <InputGroup>
                                    <InputText v-model="documentoEmpresa" :readonly="!isEditingBilling"
                                        class="border-green-iimp" @keypress="onlyAlphanumericKey" @paste="onlyNumberKey"
                                        :maxlength="12" :disabled="!isEditingBilling" />
                                    <Button icon="pi pi-search"
                                        class="!bg-orange-600 !border-orange-600 hover:!bg-orange-500 hover:!border-orange-500 !text-white !shadow-none"
                                        @click="getEmpresaData" :loading="loading_doc"
                                        :disabled="!isEditingBilling || !documentoEmpresa" />
                                </InputGroup>
                                <small v-if="dniMessageEmpresa" class="text-orange-600 font-bold block mt-1">
                                    <i class="pi pi-info-circle mr-1"></i> {{ dniMessageEmpresa }}
                                </small>

                                <div v-if="alphanumericMessage"
                                    class="text-orange-600 font-bold block mt-1 animate-bounce">
                                    <i class="pi pi-exclamation-triangle mr-1"></i> {{ alphanumericMessage }}
                                </div>
                                <small v-else-if="errors.documentoEmpresa" class="text-red-600 block mt-1">
                                    {{ errors.documentoEmpresa }}
                                </small>
                            </div>
                        </div>

                        <div class="w-full sm:col-span-1">
                            <label class="block mb-1">Nombre o Razon Social <span class="text-red-600">*</span></label>
                            <InputText v-model="razonSocial" class="w-full border-green-iimp"
                                :disabled="!isEditingBilling || loading_doc"
                                :readonly="!isEditingBilling || block_direction" />
                            <small class="text-red-600" v-if="errors.razonSocial">{{ errors.razonSocial }}</small>
                        </div>
                    </div>

                    <div class="grid gap-6 m-6 md:grid-cols-2">
                        <div class="w-full sm:col-span-1">
                            <label class="block mb-1">Dirección Fiscal <span class="text-red-600">*</span></label>
                            <InputText v-model="direccionEmpresa" class="w-full border-green-iimp"
                                :readonly="!isEditingBilling || block_direction"
                                :disabled="!isEditingBilling || loading_doc" />
                            <small class="text-red-600" v-if="errors.direccionEmpresa">{{ errors.direccionEmpresa
                                }}</small>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="w-full sm:col-span-1">
                                <label class="block mb-1">Responsable Facturación <span
                                        class="text-red-600">*</span></label>
                                <InputText v-model="responsable" :readonly="!isEditingBilling"
                                    class="w-full border-green-iimp" :disabled="!isEditingBilling || loading_doc" />
                                <small class="text-red-600" v-if="errors.responsable">{{ errors.responsable }}</small>
                            </div>

                            <div class="w-full sm:col-span-1">
                                <label class="block mb-1">Email Facturación <span class="text-red-600">*</span></label>
                                <InputText v-model="correo_facturador" :readonly="!isEditingBilling"
                                    class="w-full border-green-iimp" :disabled="!isEditingBilling || loading_doc" />
                                <small class="text-red-600" v-if="errors.correo_facturador">{{ errors.correo_facturador
                                    }}</small>
                            </div>
                        </div>
                    </div>
                </template>
                <pre class="bg-red-100 text-red-700 p-4">
            Errores actuales: {{ errors }}
        </pre>
            </Card>
        </div>

    </div>

</template>
