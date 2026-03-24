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
    saved_values: Object,
    cupones: Object
});

const empresaCupon = ref(null);
const codigoVoucher = ref('');
const loadingCupon = ref(false);
const cuponAplicado = ref(false);
const mensajeVoucher = ref({ texto: '', tipo: '' });

const empresasAliadas = computed(() => {
    if (!props.cupones) return [];

    const lista = Object.values(props.cupones).map(c => {
        // Validamos si aún tiene stock disponible
        const tieneStock = parseInt(c.usos_actuales) < parseInt(c.limite_usos);

        return {
            id: c.id,
            nombre: tieneStock ? c.razon_social : `${c.razon_social} (CUPONES AGOTADOS)`,
            ruc: c.num_documento,
            codigo: c.codigo_cupon,
            valor: c.valor,
            tipo_doc: c.tipo_documento,
            disabled: !tieneStock // Nueva propiedad para deshabilitar en el Select
        };
    });

    return lista.sort((a, b) => a.nombre.localeCompare(b.nombre));
});

const fieldNames = {
    selected_categoria: 'Categoría de Inscripción',
    tipoDocumentoEmpresa: 'Tipo de Documento de Facturación',
    documentoEmpresa: 'Número de Documento de Facturación',
    razonSocial: 'Razón Social / Nombre Completo',
    direccionEmpresa: 'Dirección de Facturación',
    responsable: 'Nombre del Contacto de Facturación',
    correo_facturador: 'Correo Electrónico de Facturación',
    reglamento: 'Aceptación de Términos y Condiciones',
    uploadDocument: 'Documento Requerido de la Categoría'
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
const descuentoAplicadoMonto = ref(0);
// Agregamos un estado para controlar si el usuario puede editar manualmente
const isEditingBilling = ref(true);
const cuponIdSeleccionado = ref(null);
let current_price = 0;
const tipoDocumento = computed(() => page.props.general.tipDocEmp)
const days = { 'mie': 'Wednesday', 'jue': 'Thursday', 'vie': 'Friday' };
const current_days = { 'lun': false, 'mar': false, 'mie': false, 'jue': false, 'vie': false };
const showInfoModal = ref(false);
const modalConfig = ref({ title: '', message: '', icon: '', colorClass: '' });

const formManualErrors = ref({ reglamento: null, total: null, uploadDocument: null });

const { defineField, errors, setValues, values, validate } = useForm({
    validationSchema: yup.object({
        razonSocial: yup.string().trim().required('La razón social es obligatoria'),
        // direccionEmpresa: yup.string().trim().required('La dirección de la empresa es obligatoria'),
        direccionEmpresa: yup.string()
            .trim()
            .required('La dirección de la empresa es obligatoria')
            .min(5, 'La dirección es demasiado corta')
            .test('no-garbage', 'Por favor, ingrese una dirección válida', (value) => {
                if (!value) return false;
                // Verifica que contenga al menos 3 letras o números
                // y que no sean solo símbolos repetidos como --- o ***
                const hasContent = /[a-zA-Z0-9]{3,}/.test(value);
                const isNotJustSymbols = !/^[ \-*.;,_]+$/.test(value);
                return hasContent && isNotJustSymbols;
            }),
        responsable: yup.string().trim().required('El nombre del responsable es obligatorio'),
        correo_facturador: yup.string().trim()
            .email('Formato de correo inválido')
            .required('El correo de facturación es obligatorio'),
    })
});

const dniMessageEmpresa = ref('');

const [documentoEmpresa, documentoEmpresaAttrs] = defineField('documentoEmpresa');
const [razonSocial, razonSocialAttrs] = defineField('razonSocial');
const [responsable, responsableAttrs] = defineField('responsable');
const [correo_facturador, correo_facturadorAttrs] = defineField('correo_facturador');
const [tipoDocumentoEmpresa, tipoDocumentoEmpresaAttrs] = defineField('tipoDocumentoEmpresa');
const [direccionEmpresa, direccionEmpresaAttrs] = defineField('direccionEmpresa');
const [selectTipoPago] = defineField('selectTipoPago');
const [selectTipoDocPago] = defineField('selectTipoDocPago');
const [selected_categoria, selected_categoriaAttrs] = defineField('selected_categoria');
const [selectedDays, selectedDaysAttrs] = defineField('selectedDays');
const [uploadDocument] = defineField('uploadDocument');


const is_category_fixed = ref(false);
const urlParams = new URLSearchParams(window.location.search);
const esSeccionViajes = computed(() => urlParams.get('section') === 'viajes');
const noAsociadoCupon = computed(() => urlParams.get('profile') === '2');


const MODAL_DATA = {
    DNI: {
        title: 'Información de Boleta',
        message: 'Al seleccionar DNI, se emitirá una Boleta de Venta electrónica a nombre de la persona natural.',
        icon: 'pi pi-user',
        colorClass: 'text-blue-600'
    },
    RUC: {
        title: 'Información de Factura',
        message: 'Al seleccionar RUC, se emitirá una Factura Comercial electrónica a nombre de la empresa o entidad legal.',
        icon: 'pi pi-building',
        colorClass: 'text-purple-600'
    }
};

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

const validarCuponLocal = async () => {
    // Si no hay empresa seleccionada o código escrito, no hacemos nada
    if (!codigoVoucher.value || !empresaCupon.value) return;

    loadingCupon.value = true;
    mensajeVoucher.value = { texto: '', tipo: '' };

    try {
        // Buscamos el cupón en los datos originales que coincida con el ID seleccionado y el código escrito
        const cuponData = Object.values(props.cupones).find(c =>
            c.id === empresaCupon.value.id &&
            c.codigo_cupon.trim().toUpperCase() === codigoVoucher.value.trim().toUpperCase()
        );

        if (cuponData) {
            cuponAplicado.value = true;
            descuentoAplicadoMonto.value = cuponData.valor; // Guardamos el % o monto
            cuponIdSeleccionado.value = cuponData.id;

            mensajeVoucher.value = {
                texto: `¡Cupón de ${cuponData.valor}% válido para ${cuponData.razon_social}!`,
                tipo: 'success'
            };

            toast.add({
                severity: 'success',
                summary: 'Cupón Validado',
                detail: 'Se han cargado los datos de facturación de la empresa.',
                life: 4000
            });

        } else {
            mensajeVoucher.value = {
                texto: 'El código no coincide con la empresa seleccionada.',
                tipo: 'error'
            };
        }
    } catch (e) {
        mensajeVoucher.value = { texto: 'Error al validar cupón.', tipo: 'error' };
    } finally {
        loadingCupon.value = false;
    }
};


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

    const totalValido = esSeccionViajes.value ? true : (total.value > 0);

    if (!result.valid || isInvalid.value) {
        console.log("Validación fallida:", {
            resultValid: result.valid,
            isInvalid: isInvalid.value,
            errors: errors.value,
            values
        });
        return { validate: false };
    }

    return {
        validate: true,
        formInscription: {
            ...values,
            codigo_cupon: codigoVoucher.value, // Enviamos el código escrito
            empresa_id: empresaCupon.value?.id, // Enviamos el ID de la empresa aliada
            id_cupon: cuponIdSeleccionado.value,
        },
        total_final: total.value // Aquí enviará 0 si es viajes
    };
};

const isInvalid = computed(() => {
    const v = values;

    // Validar campos básicos
    const basicFields = !v.razonSocial || !v.direccionEmpresa || !v.responsable || !v.correo_facturador;

    // Validar documento según categoría
    const missingDoc = show_document.value && !v.uploadDocument;

    // Si hay errores en el objeto errors de vee-validate
    const hasErrors = Object.keys(errors.value).length > 0;

    return basicFields || missingDoc || hasErrors;
});

onMounted(() => {
    // Configuraciones iniciales
    tipoDocumentoEmpresa.value = 1;
    selectTipoDocPago.value = 2;
    selectTipoPago.value = 3;

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

    if (esSeccionViajes.value) {
        cuponAplicado.value = false;
        descuentoAplicadoMonto.value = 0;
        empresaCupon.value = null;
        codigoVoucher.value = '';
    }
    // console.log("Mounted FormInscription with props.data_persona:", props.data_persona);

});


const esRuc20 = computed(() => {
    return tipoDocumentoEmpresa.value === 2 && documentoEmpresa.value?.startsWith('20');
});

const camposFacturacionBloqueados = computed(() => {
    // Si es RUC 20, bloqueamos siempre (a menos que no se haya buscado nada aún)
    // Si NO es RUC 20 (ej. RUC 10 o DNI), dependemos de isEditingBilling
    if (esRuc20.value) {
        return !isEditingBilling.value || !showManualAlert.value;
    }

    // Para RUC 10 o DNI, seguimos tu lógica normal
    return !isEditingBilling.value;
});

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
    if (!newVal) {
        dniMessageEmpresa.value = '';
        return;
    }

    // 1. Definir el máximo permitido según el tipo de documento
    let maxLength = 12; // Valor por defecto para Pasaporte/Otros
    if (tipoDocumentoEmpresa.value === 1) maxLength = 8;  // DNI
    if (tipoDocumentoEmpresa.value === 2) maxLength = 11; // RUC

    // 2. Limpieza de caracteres según el tipo
    let cleanedValue = newVal;
    if (tipoDocumentoEmpresa.value === 1 || tipoDocumentoEmpresa.value === 2) {
        // Solo números para DNI y RUC
        cleanedValue = newVal.replace(/\D/g, '');
    } else {
        // Alfanumérico para extranjeros (sin símbolos)
        cleanedValue = newVal.replace(/[^a-zA-Z0-9]/g, '');
    }

    // 3. Control de longitud y mensajes
    if (newVal.length > maxLength) {
        dniMessageEmpresa.value = `Solo se permiten ${maxLength} dígitos`;

        // Forzamos el recorte y notificamos a Vue
        nextTick(() => {
            documentoEmpresa.value = cleanedValue.slice(0, maxLength);
        });

        setTimeout(() => {
            dniMessageEmpresa.value = '';
        }, 3000);
    } else if (newVal !== cleanedValue) {
        // Si se intentó pegar un símbolo o letra donde no debía
        documentoEmpresa.value = cleanedValue;
        alphanumericMessage.value = "Caracteres no permitidos eliminados";
        setTimeout(() => { alphanumericMessage.value = ''; }, 3000);
    }
});

watch(direccionEmpresa, (newVal) => {
    if (!newVal) return;

    // Si el usuario empieza a escribir cadenas de símbolos repetidos
    // Podemos limpiar o simplemente dejar que Yup lo maneje,
    // pero aquí un ejemplo de limpieza de caracteres prohibidos al inicio:
    if (/^[ \-*.;,_]+$/.test(newVal) && newVal.length > 2) {
        // Opcional: Podrías mostrar un mensaje específico
        // alphanumericMessage.value = "La dirección no puede ser solo símbolos";
        setTimeout(() => { alphanumericMessage.value = ''; }, 3000);
    }
});

watch(tipoDocumentoEmpresa, (newVal, oldVal) => {
    // Solo actuamos si es un cambio provocado por el usuario (no carga inicial)
    if (oldVal !== undefined) {

        // A. LIMPIEZA DE CAMPOS (Tu lógica original)
        documentoEmpresa.value = '';
        razonSocial.value = '';
        direccionEmpresa.value = '';
        responsable.value = '';
        correo_facturador.value = '';

        showManualAlert.value = false;
        showSuccessAlert.value = false;
        isEditingBilling.value = false;

        // B. SINCRONIZAR CON VEE-VALIDATE
        setValues({
            ...values,
            documentoEmpresa: '',
            razonSocial: '',
            direccionEmpresa: '',
            responsable: '',
            correo_facturador: ''
        });

        // C. DISPARAR POPUP SEGÚN SELECCIÓN
        if (newVal === 1) { // DNI
            modalConfig.value = MODAL_DATA.DNI;
            showInfoModal.value = true;
        } else if (newVal === 2) { // RUC
            modalConfig.value = MODAL_DATA.RUC;
            showInfoModal.value = true;
        }

        // D. AJUSTAR TIPO DE PAGO
        setTipoDocPago();
    }
});

watch(empresaCupon, () => {
    codigoVoucher.value = '';
    mensajeVoucher.value = { texto: '', tipo: '' };
    cuponAplicado.value = false;
    descuentoAplicadoMonto.value = 0;
});

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

            if (esRuc20.value) {
                isEditingBilling.value = false; // Bloquea edición manual para RUC 20
                block_direction.value = true;   // Refuerza bloqueo de dirección
            } else {
                isEditingBilling.value = true;  // Permite editar si es RUC 10 o DNI
                block_direction.value = false;
            }


            // block_direction.value = false;
            // isEditingBilling.value = true;
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
        fileErrors.value.push("Formato de archivo no válido. Solo se aceptan documentos PDF o imágenes (PNG, JPG, JPEG).");
    }

    // --- VALIDACIÓN 2: TAMAÑO (6MB) ---
    if (file.size > maxSize) {
        fileErrors.value.push("El tamaño del archivo excede el límite. El máximo permitido es de 6MB.");
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

    // Solo validamos DNI (1) y RUC (2)
    if (tipoDocumentoEmpresa.value === 1 || tipoDocumentoEmpresa.value === 2) {

        // 1. Si no es un número, bloquear
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            event.preventDefault();
            return false;
        }

        // 2. Si ya llegó al máximo, mostrar mensaje y bloquear
        const max = tipoDocumentoEmpresa.value === 1 ? 8 : 11;

        if (documentoEmpresa.value?.length >= max) {
            dniMessageEmpresa.value = `Límite alcanzado: ${max} dígitos`;

            // Limpiar mensaje después de un rato
            setTimeout(() => { dniMessageEmpresa.value = ''; }, 3000);

            event.preventDefault();
            return false;
        }
    }
    return true;
};

const montoDescuentoEfectivo = computed(() => {
    // Calculamos cuánto dinero representa el % de descuento sobre el total
    return (total.value * descuentoAplicadoMonto.value) / 100;
});

const totalFinalConDescuento = computed(() => {
    return total.value - montoDescuentoEfectivo.value;
});

watch(tipoDocumentoEmpresa, (newVal) => {
    // Sincronizamos la configuración del modal basado en el ID
    if (newVal === 1) {
        modalConfig.value = MODAL_DATA.DNI;
    } else if (newVal === 2) {
        modalConfig.value = MODAL_DATA.RUC;
    }
    setTipoDocPago();
}, { immediate: true });

defineExpose({
    getInscripcion,
    values,         // Exponemos los valores actuales
    errors,         // Exponemos los errores actuales
    show_document,
    isInvalid       // EXPONEMOS LA VALIDEZ AL PADRE
});
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

                        <div v-if="is_category_fixed"
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

                    <!-- =========== CUPON DE DESCUENTO  ========== -->
                    <!-- ================================= -->
                    <Card v-if="!esSeccionViajes && noAsociadoCupon"
                        class="mt-6 border border-dashed border-blue-400 bg-blue-50/50 shadow-sm">
                        <template #content>
                            <!-- Mensaje Informativo Superior -->
                            <div class="flex items-start gap-3 mb-4 p-3 bg-white/60 rounded-lg border border-blue-100">
                                <i class="pi pi-info-circle text-blue-500 mt-1"></i>
                                <p class="text-xs text-blue-800 leading-tight">
                                    El cupón de descuento aplica exclusivamente para <strong>empresas e instituciones
                                        aliadas</strong> previamente registradas. Si tu organización cuenta con un
                                    convenio vigente, selecciona el nombre y valida el código.
                                </p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end">
                                <!-- Selector de Empresa -->
                                <div class="flex flex-col gap-2">
                                    <label class="text-xs font-black text-blue-900 uppercase tracking-wider">
                                        Empresa / Institución
                                    </label>
                                    <Select v-model="empresaCupon" :options="empresasAliadas" optionLabel="nombre"
                                        optionDisabled="disabled" placeholder="Escribe para buscar tu empresa..."
                                        class="w-full border-blue-300" :filter="true"
                                        filterPlaceholder="Ej: ALS PERU, MDH..." resetFilterOnHide>
                                        <template #option="slotProps">
                                            <div class="flex flex-col"
                                                :class="{ 'opacity-50 cursor-not-allowed': slotProps.option.disabled }">
                                                <div class="flex items-center gap-2">
                                                    <span
                                                        :class="{ 'font-bold text-sm': true, 'text-gray-900': slotProps.option.disabled }">
                                                        {{ slotProps.option.nombre }}
                                                    </span>
                                                    <!-- Badge de Agotado opcional -->
                                                    <span v-if="slotProps.option.disabled"
                                                        class="bg-red-100 text-red-600 text-[9px] px-2 py-0.5 rounded-full font-black uppercase">
                                                        Agotado
                                                    </span>
                                                </div>
                                                <small v-if="slotProps.option.disabled"
                                                    class="text-red-600 italic text-[10px]">
                                                    El cupón corporativo ha llegado a su límite de usos.
                                                </small>
                                            </div>
                                        </template>
                                    </Select>
                                </div>

                                <!-- Input de Código y Botón -->
                                <div class="flex flex-col gap-2">
                                    <label class="text-xs font-black text-blue-900 uppercase tracking-wider">Código de
                                        Descuento</label>
                                    <InputGroup>
                                        <InputText v-model="codigoVoucher" placeholder="Ingresa el código"
                                            class="border-blue-300 uppercase" :disabled="!empresaCupon" />
                                        <Button label="Validar" icon="pi pi-ticket"
                                            class="!bg-blue-700 !border-blue-700 hover:!bg-blue-800"
                                            :loading="loadingCupon" :disabled="!codigoVoucher"
                                            @click="validarCuponLocal" />
                                    </InputGroup>
                                </div>
                            </div>

                            <!-- Mensaje de Éxito / Error -->
                            <div v-if="mensajeVoucher.texto" class="mt-3 text-center animate-fade-in">
                                <span :class="mensajeVoucher.tipo === 'success' ? 'text-green-600' : 'text-red-600'"
                                    class="text-xs font-bold flex items-center justify-center gap-2">
                                    <i
                                        :class="mensajeVoucher.tipo === 'success' ? 'pi pi-check-circle' : 'pi pi-times-circle'"></i>
                                    {{ mensajeVoucher.texto }}
                                </span>
                            </div>
                        </template>
                    </Card>

                    <!-- RESUMEN VISUAL DE DESCUENTO -->
                    <div v-if="cuponAplicado"
                        class="mt-6 p-4 bg-green-50 border border-green-200 rounded-xl animate-fade-in">
                        <div class="flex flex-col gap-2">
                            <div class="flex justify-between items-center text-gray-600">
                                <span class="text-sm font-medium">Precio Base:</span>
                                <!-- Usamos Number() para prevenir el error de toFixed -->
                                <span class="text-sm line-through">USD {{ Number(total || 0).toFixed(2) }}</span>
                            </div>

                            <div class="flex justify-between items-center text-red-600 font-bold">
                                <div class="flex items-center gap-2">
                                    <i class="pi pi-tag text-xs"></i>
                                    <span class="text-sm uppercase tracking-tight">
                                        Descuento Corporativo ({{ descuentoAplicadoMonto }}%):
                                    </span>
                                </div>
                                <span class="text-sm">- USD {{ Number(montoDescuentoEfectivo || 0).toFixed(2) }}</span>
                            </div>

                            <Divider class="!my-1" />

                            <div class="flex justify-between items-center">
                                <span class="text-blue-900 font-black uppercase text-xs tracking-widest">Total a
                                    Pagar:</span>
                                <div class="flex flex-col items-end">
                                    <span class="text-2xl font-black text-green-700 leading-none">
                                        USD {{ Number(totalFinalConDescuento || 0).toFixed(2) }}
                                    </span>
                                    <small class="text-[10px] text-green-600 font-bold uppercase tracking-tighter">
                                        ¡Beneficio aplicado correctamente!
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

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
                            <span class="text-green-900 font-black text-sm uppercase tracking-wide">Éxito</span>
                            <p class="text-green-800 text-sm font-medium leading-tight">
                                Datos encontrados y cargados correctamente.
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
                            <span class="text-blue-900 font-black text-sm uppercase tracking-wide">Información</span>
                            <p class="text-blue-800 text-sm font-medium leading-tight">
                                Registro no encontrado. Por favor, complete los datos de facturación manualmente para
                                continuar con su
                                inscripción A ProExplo2026.
                            </p>
                        </div>
                        <Button icon="pi pi-times" class="p-button-text p-button-rounded text-blue-400 ml-auto"
                            @click="showManualAlert = false" />
                    </div>

                    <div class="grid gap-6 m-6 md:grid-cols-2">
                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="col-span-3 sm:col-span-1">
                                <label class="block mb-1">Tipo de Documento <span class="text-red-600">*</span></label>
                                <Select v-model="tipoDocumentoEmpresa" :options="filteredDocTypes" optionLabel="name_en"
                                    optionValue="id" class="w-full border-green-iimp" @change="setTipoDocPago" />
                                <small class="text-red-600" v-if="errors.tipoDocumentoEmpresa">{{
                                    errors.tipoDocumentoEmpresa }}</small>
                            </div>

                            <div class="col-span-3 sm:col-span-1">
                                <label class="block mb-1">Numero de Documento <span
                                        class="text-red-600">*</span></label>
                                <InputGroup>
                                    <InputText v-model="documentoEmpresa" class="border-green-iimp"
                                        @keypress="onlyNumberKey" @paste="onlyNumberKey"
                                        :maxlength="tipoDocumentoEmpresa === 1 ? 8 : (tipoDocumentoEmpresa === 2 ? 11 : 12)" />
                                    <Button icon="pi pi-search"
                                        class="!bg-orange-600 !border-orange-600 hover:!bg-orange-500 hover:!border-orange-500 !text-white !shadow-none"
                                        @click="getEmpresaData" :loading="loading_doc" :disabled="!documentoEmpresa" />
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
                            <InputText v-model="razonSocial" v-bind="razonSocialAttrs" class="w-full border-green-iimp"
                                :disabled="loading_doc || esRuc20" :readonly="camposFacturacionBloqueados"
                                :class="{ 'bg-gray-100 font-semibold': esRuc20 && !showManualAlert }" />
                            <small class="text-red-600" v-if="errors.razonSocial">{{ errors.razonSocial }}</small>
                        </div>
                    </div>

                    <div class="grid gap-6 m-6 md:grid-cols-2">
                        <div class="w-full sm:col-span-1">
                            <label class="block mb-1">Dirección Fiscal <span class="text-red-600">*</span></label>
                            <InputText v-model="direccionEmpresa" v-bind="direccionEmpresaAttrs"
                                class="w-full border-green-iimp" :readonly="camposFacturacionBloqueados"
                                :disabled="loading_doc || esRuc20"
                                :class="{ 'bg-gray-100': esRuc20 && !showManualAlert }" />
                            <small class="text-red-600" v-if="errors.direccionEmpresa">{{ errors.direccionEmpresa
                            }}</small>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="w-full sm:col-span-1">
                                <label class="block mb-1">Responsable Facturación <span
                                        class="text-red-600">*</span></label>
                                <InputText v-model="responsable" v-bind="responsableAttrs"
                                    class="w-full border-green-iimp" :disabled="loading_doc" />
                                <small class="text-red-600" v-if="errors.responsable">{{ errors.responsable }}</small>
                            </div>

                            <div class="w-full sm:col-span-1">
                                <label class="block mb-1">Email Facturación <span class="text-red-600">*</span></label>
                                <InputText v-model="correo_facturador" v-bind="correo_facturadorAttrs"
                                    class="w-full border-green-iimp" :disabled="loading_doc" />
                                <small class="text-red-600" v-if="errors.correo_facturador">{{ errors.correo_facturador
                                }}</small>
                            </div>
                        </div>
                    </div>

                </template>
                <pre v-if="false" class="bg-red-100 text-red-700 p-4">
            Errores actuales: {{ errors }}
        </pre>
            </Card>
        </div>

    </div>

    <Dialog v-model:visible="showInfoModal" modal header=" " :style="{ width: '25rem' }"
        :breakpoints="{ '1199px': '75vw', '575px': '90vw' }" class="custom-billing-modal" appendTo="body">
        <div class="flex flex-col items-center p-2 text-center">
            <div class="rounded-full w-20 h-20 flex items-center justify-center mb-6 animate-bounce-short"
                :class="tipoDocumentoEmpresa === 1 ? 'bg-blue-50 text-blue-500' : 'bg-purple-50 text-purple-500'">
                <i :class="modalConfig.icon" style="font-size: 2.5rem"></i>
            </div>

            <h3 class="text-xl font-black mb-2 uppercase tracking-tight" :class="modalConfig.colorClass">
                {{ modalConfig.title }}
            </h3>

            <p class="text-slate-600 leading-relaxed mb-6 font-medium">
                {{ modalConfig.message }}
            </p>

            <Button label="Entendido"
                :class="tipoDocumentoEmpresa === 1 ? '!bg-blue-600 !border-blue-600' : '!bg-purple-600 !border-purple-600'"
                class="w-full font-bold py-3 shadow-lg" @click="showInfoModal = false" />
        </div>
    </Dialog>

</template>
<style scoped>
/* Quitar bordes innecesarios del Dialog */
.billing-info-dialog .p-dialog-header {
    border-bottom: none;
    padding-bottom: 0;
}

.billing-info-dialog .p-dialog-content {
    border-radius: 0 0 1.5rem 1.5rem;
}

/* Sombra suave al modal */
.billing-info-dialog {
    border: none;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
}

/* Efecto de entrada suave */
.p-dialog-mask {
    backdrop-filter: blur(4px);
}
</style>
