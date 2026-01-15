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
import { ref, onMounted, computed, watch } from 'vue';
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
// Definimos los tipos permitidos (Coincidiendo con tu accept)
const allowedTypes = [
    'application/pdf',
    'image/png',
    'image/jpg',
    'image/jpeg'
];

let current_price = 0;
const billingMessage = ref(null);
const generos = computed(() => usePage().props.general.generos);
const reglamento_inscripciones = computed(() => usePage().props.general.reglamento_inscripciones);
const nacionalidades = computed(() => usePage().props.general.paises);
const paises = computed(() => usePage().props.general.paises);
const tipoDocumentoPago = computed(() => page.props.general.tipoDocumentoPago);
const tipoDocumento = computed(() => page.props.general.tipDocEmp)
const departamentos = ref();
const provincias = ref();
const distritos = ref();
const visible = ref(false);
const days = {
    'mar': 'Tuesday',
    'mie': 'Wednesday',
    'jue': 'Thursday',
    // 'vie': 'Friday'
};

const current_days = { 'lun': false, 'mar': false, 'mie': false, 'jue': false, 'vie': false };
const formManualErrors = ref({
    reglamento: null,
    total: null,
    uploadDocument: null
});


const { defineField, errors, handleSubmit, setValues, resetForm, values } = useForm({
    validationSchema: yup.object({
        tipoDocumentoEmpresa: yup.mixed().required('Company document type is required'),
        documentoEmpresa: yup.string().when(
            'tipoDocumentoEmpresa',
            (tipoDocumentoEmpresa, schema) => {
                if (typeof tipoDocumentoEmpresa[0] !== 'undefined') {
                    const type = tipoDocumentoEmpresa[0];

                    if (type == 2) { // RUC
                        return yup.string()
                            .matches(/^\d+$/, 'The value must be numeric')
                            .length(11, 'Must be exactly 11 digits')
                            .required('Tax ID (RUC) is required');
                    } else if (type == 1) { // DNI
                        return yup.string()
                            .matches(/^\d+$/, 'The value must be numeric')
                            .length(8, 'Must be exactly 8 digits')
                            .required('Document number is required');
                    } else { // Others (Passport, etc.)
                        return yup.string()
                            .matches(/^[a-zA-Z0-9]+$/, "The value must contain only letters or numbers")
                            .required('Document number is required');
                    }
                }
                return schema;
            }
        ),
        nombres: yup.string().required('Name is required'),
        apellido_paterno: yup.string().required('Last name is required'),
        fecha_nacimiento: yup.mixed().required('Date of birth is required'),
        sexo: yup.mixed().required('Gender is required'),
        correo: yup.string().required('Email is required').email('Enter a valid email'),
        celular: yup.string().required('Mobile phone is required'),
        pais: yup.mixed().required('Country is required'),
        nacionalidad: yup.mixed().required('Nationality is required'),
        direccionPersona: yup.string().required('Address is required'),
        empresa: yup.string().required('Company is required'),
        cargo: yup.string().required('Job title is required'),
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

const [nombres, nombresAttrs] = defineField('nombres');
const [apellido_paterno, apellido_paternoAttrs] = defineField('apellido_paterno');
const [apellido_materno, apellido_maternoAttrs] = defineField('apellido_materno');
const [fecha_nacimiento, fecha_nacimientoAttrs] = defineField('fecha_nacimiento');
const [sexo, sexoAttrs] = defineField('sexo');
const [correo, correoAttrs] = defineField('correo');
const [celular, celularAttrs] = defineField('celular');
const [nacionalidad, nacionalidadAttrs] = defineField('nacionalidad');
const [pais, paisAttrs] = defineField('pais');
const [departamento, departamentoAttrs] = defineField('departamento');
const [provincia, provinciaAttrs] = defineField('provincia');
const [distrito, distritoAttrs] = defineField('distrito');
const [direccionPersona, direccionPersonaAttrs] = defineField('direccionPersona');
const [empresa, empresaAttrs] = defineField('empresa');
const [cargo, cargoAttrs] = defineField('cargo');
const [credencial, credencialAttrs] = defineField('credencial');
const [auth, authAttrs] = defineField('auth');
const [terminos, terminosAttrs] = defineField('terminos');
const [reglamento, reglamentoAttrs] = defineField('reglamento');
const [selectTipoDocPago, selectTipoDocPagoAttrs] = defineField('selectTipoDocPago');

const [selected_categoria, selected_categoriaAttrs] = defineField('selected_categoria');
const [selectedDays, selectedDaysAttrs] = defineField('selectedDays');

const [documentoEmpresa, documentoEmpresaAttrs] = defineField('documentoEmpresa');
const [razonSocial, razonSocialAttrs] = defineField('razonSocial');
const [responsable, responsableAttrs] = defineField('responsable');
const [correo_facturador, correo_facturadorAttrs] = defineField('correo_facturador');
const [tipoDocumentoEmpresa, tipoDocumentoEmpresaAttrs] = defineField('tipoDocumentoEmpresa');
const [direccionEmpresa, direccionEmpresaAttrs] = defineField('direccionEmpresa');
const [selectTipoPago, selectTipoPagoAttrs] = defineField('selectTipoPago');

const [uploadDocument, uploadDocumentAttrs] = defineField('uploadDocument');

const today = new Date();

// onMounted(() => {

//     tipoDocumentoEmpresa.value = 1;
//     // selectTipoDocPago.value = 3;
//     selectTipoDocPago.value = 2;
//     selectTipoPago.value = 3;
// })

onMounted(() => {
    // 1. Configuraciones iniciales
    tipoDocumentoEmpresa.value = 1;
    selectTipoDocPago.value = 2;
    selectTipoPago.value = 3;

    // 2. PRIORIDAD: Si hay datos guardados (porque dimos Back), los usamos
    if (props.saved_values) {
        console.log("Restaurando datos del formulario...", props.saved_values);

        // Restauramos los valores del formulario
        setValues(props.saved_values);

        // Restauramos variables reactivas locales que no son del form directamente
        // pero controlan la UI (como uploadDocument, días, etc)

        // Restaurar categoría para lógica visual
        if (props.saved_values.selected_categoria) {
            const cat = props.categorias.find(c => c.id === props.saved_values.selected_categoria);
            if (cat) changeCategory(cat.id, cat.precio_disponible?.valor || 0);
        }

        // Restaurar lógica de facturación si había RUC
        if (props.saved_values.tipoDocumentoEmpresa == 2) {
            block_direction.value = true;
        }

    } else if (props.data_persona && props.data_persona.persona) {
        // 3. Si no hay datos guardados, usamos los datos frescos de la API (Paso 1)
        // (Tu lógica original)
        es_socio.value = props.data_persona.persona.es_socio;
        // ... tu lógica de selección automática de categoría ...

        props.data_persona.persona.fecha_nacimiento = Functions.toLocalDateOnly(props.data_persona.persona.fecha_nacimiento);
        setValues(props.data_persona.persona);
        loadDepartamentos(); // etc...
    }
});

const loadDepartamentos = async () => {
    departamento.value = undefined;
    provincia.value = undefined;
    distrito.value = undefined;
    departamentos.value = await Functions.loadDepartamentos(pais.value).then(data => { return data });
}

const loadProvincias = async () => {
    provincia.value = undefined;
    distrito.value = undefined;
    provincias.value = await Functions.loadProvincias(pais.value, departamento.value).then(data => { return data });
}

const loadDistritos = async () => {
    distrito.value = undefined;
    distritos.value = await Functions.loadDistritos(pais.value, departamento.value, provincia.value).then(data => { return data });
}

// const getEmpresaData = async () => {
//     // 1. Reseteamos campos y mensajes previos
//     razonSocial.value = '';
//     direccionEmpresa.value = '';
//     loading_doc.value = true;
//     billingMessage.value = null; // Limpia la alerta anterior antes de buscar

//     try {
//         const empresa = await Functions.getEmpresaData(documentoEmpresa.value, tipoDocumentoEmpresa.value);

//         if (empresa) {
//             // Autocompleta los campos
//             razonSocial.value = empresa.empresa.nombre || '';
//             direccionEmpresa.value = empresa.empresa.direccionEmpresa || '';

//             if (empresa.status) {
//                 // Lógica de bloqueo para RUC
//                 if (tipoDocumentoEmpresa.value == 2) {
//                     block_direction.value = true;
//                 }

//                 // EXITO: Seteamos el mensaje verde
//                 billingMessage.value = {
//                     type: 'success',
//                     text: 'Datos Encontrados / Data Found'
//                 };

//             } else {
//                 // NO ENCONTRADO (API responde pero sin datos válidos)
//                 let msg = 'No se encontraron datos. Por favor, complete los campos manualmente.';
//                 if (tipoDocumentoEmpresa.value == 2) {
//                     msg = "No se encontraron datos. Verifique el RUC ingresado.";
//                 }

//                 // ADVERTENCIA: Seteamos el mensaje amarillo
//                 billingMessage.value = { type: 'warn', text: msg };
//             }
//         } else {
//             // RESPUESTA VACÍA
//             let msg = 'No se encontraron datos. Por favor, complete los campos manualmente.';
//             if (tipoDocumentoEmpresa.value == 2) {
//                 msg = "No se encontraron datos. Verifique el RUC ingresado.";
//             }

//             // ADVERTENCIA
//             billingMessage.value = { type: 'warn', text: msg };
//         }
//     } catch (e) {
//         // ERROR DE CONEXIÓN O SERVIDOR
//         billingMessage.value = {
//             type: 'error',
//             text: 'Error en la consulta. Por favor complete los campos manualmente.'
//         };
//     } finally {
//         loading_doc.value = false;
//     }
// }

// Agrega esta referencia arriba junto a tus otros refs si no la tienes

const getEmpresaData = async () => {
    // 1. Reset fields and messages
    razonSocial.value = '';
    direccionEmpresa.value = '';
    billingMessage.value = null;
    loading_doc.value = true;

    // 8-second timeout to handle slow server responses
    const timeout = new Promise((_, reject) =>
        setTimeout(() => reject(new Error('timeout')), 8000)
    );

    try {
        // Race between the actual request and the timeout
        const empresa = await Promise.race([
            Functions.getEmpresaData(documentoEmpresa.value, tipoDocumentoEmpresa.value),
            timeout
        ]);

        if (empresa) {
            razonSocial.value = empresa.empresa.nombre || '';
            direccionEmpresa.value = empresa.empresa.direccionEmpresa || '';

            if (empresa.status) {
                // SUCCESS: Data found
                if (tipoDocumentoEmpresa.value == 2) block_direction.value = true;
                billingMessage.value = {
                    type: 'success',
                    text: 'Data found successfully.'
                };
            } else {
                // WARNING: Request finished but no data was found
                let msg = tipoDocumentoEmpresa.value == 2
                    ? "Tax ID (RUC) not found. Please verify the number."
                    : "Document not found. Please complete the fields manually.";
                billingMessage.value = { type: 'warn', text: msg };
            }
        }
    } catch (e) {
        // ERROR: Server fail or Timeout
        let errorMsg = 'An error occurred during the search. Please fill in the details manually.';

        if (e.message === 'timeout') {
            errorMsg = 'The server is taking too long to respond. Please complete the fields manually.';
        }

        billingMessage.value = {
            type: 'error',
            text: errorMsg
        };
    } finally {
        loading_doc.value = false;
    }
}
const fileupload = ref(null);

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
        // OPCIÓN 1: Español (Con el detalle que pediste)
        // fileErrors.value.push("Formato de archivo no válido. Solo se permiten archivos PDF o Imágenes (PNG, JPG, JPEG).");
        // OPCIÓN 2: Inglés Formal WMC (Si prefieres mantener todo el formulario en inglés)
        fileErrors.value.push("Invalid file format. Only PDF documents or Images (PNG, JPG, JPEG) are accepted.");
    }

    // --- VALIDACIÓN 2: TAMAÑO ---
    if (file.size > maxSize) { // Recuerda: maxSize debe ser 6291456 para 6MB
        // Español
        // fileErrors.value.push("El archivo excede el límite permitido. El máximo es 6MB.");
        // Inglés
        fileErrors.value.push("File size exceeds the limit. Maximum allowed is 6MB.");
    }

    // 5. Si hay errores, limpiamos todo
    if (fileErrors.value.length > 0) {
        uploadDocument.value = null;

        if (fileupload.value) {
            fileupload.value.clear();
            fileupload.value.files = [];
        }
        return;
    }

    // 6. Vista previa
    const reader = new FileReader();
    reader.onload = async (e) => {
        if (file.type == "application/pdf") {
            src.value = '/images/pdf-file-document.png';
        } else {
            src.value = e.target.result;
        }
    };
    reader.readAsDataURL(file);
}
watch(() => props.data_persona, (newVal, oldVal) => {
    empresa.value = '';
    credencial.value = '';
    // tipoDocumentoEmpresa.value = 2;
    // selectTipoDocPago.value = 1;
    tipoDocumentoEmpresa.value = 1; // 1 para que sea DNI.
    selectTipoDocPago.value = 2;    // 2 para que sea BOLETA.
    selectTipoPago.value = 3;
    documentoEmpresa.value = '';
    razonSocial.value = '';
    direccionEmpresa.value = '';
    responsable.value = '';
    show_days.value = false;
    show_document.value = false;
    src.value = null;
    block_direction.value = true;

    if (typeof props.data_persona.persona != 'undefined') {
        es_socio.value = props.data_persona.persona.es_socio;

        if (props.categorias[0].control == 'CV') {
            for (var i = 0; props.categorias.length; i++) {

                if (newVal.persona.es_socio && props.categorias[i].condicion == 'SO') {
                    selected_categoria.value = props.categorias[i].id;
                    // total.value = props.categorias[i].precio_disponible.valor;
                    // Usa ?.valor y un valor por defecto (0) si no existe
                    total.value = props.categorias[i].precio_disponible?.valor || 0;
                    break;
                }
                if (!newVal.persona.es_socio && props.categorias[i].condicion == 'NS') {
                    selected_categoria.value = props.categorias[i].id;
                    // total.value = props.categorias[i].precio_disponible.valor;
                    total.value = props.categorias[i].precio_disponible?.valor || 0;
                    break;
                }
                if (props.categorias[i].condicion != 'SO' && props.categorias[i].condicion != 'NS') {
                    selected_categoria.value = props.categorias[i].id;
                    // total.value = props.categorias[i].precio_disponible.valor;
                    total.value = props.categorias[i].precio_disponible?.valor || 0;
                    if (props.categorias[i].requiere_documento) {
                        show_document.value = true;
                    }
                    break;
                }
            }
        } else {
            selected_categoria.value = props.categorias[0].id;
            total.value = props.categorias[0].precio_disponible.valor;;
        }

        props.data_persona.persona.fecha_nacimiento = Functions.toLocalDateOnly(newVal.persona.fecha_nacimiento);
        setValues(newVal.persona);
        loadDepartamentos()
        setValues(newVal.persona);
        loadProvincias()
        setValues(newVal.persona);
        loadDistritos()
        setValues(newVal.persona);

    }
});

const onlyNumberKey = (event) => {
    if (tipoDocumentoEmpresa.value == 2) {
        block_direction.value = true;
    } else {
        block_direction.value = false;
    }


    if (tipoDocumentoEmpresa.value == 2 || tipoDocumentoEmpresa.value == 1) {
        const charCode = event.charCode ? event.charCode : event.keyCode
        if (charCode < 48 || charCode > 57) {
            event.preventDefault()
        } else {
            if (typeof documentoEmpresa.value != 'undefined') {
                if (documentoEmpresa.value.length == 11 && tipoDocumentoEmpresa.value == 2) {
                    event.preventDefault()
                }

                if (documentoEmpresa.value.length == 8 && tipoDocumentoEmpresa.value == 1) {
                    event.preventDefault()
                }
            }

        }
    } else {
        const key = event.key;

        // Allow navigation and control keys
        if (["Backspace", "Delete", "Tab", "ArrowLeft", "ArrowRight"].includes(key)) {
            return;
        }

        // Block everything that's not a-z, A-Z, 0-9
        if (!/^[a-zA-Z0-9]$/.test(key)) {
            event.preventDefault();
        }
    }
}

const showModal = () => {
    visible.value = !visible.value;
};

const acceptModal = () => {
    visible.value = !visible.value;
    terminos.value = true;
};

const check_fact = () => {
    if (tipoDocumentoEmpresa.value == 2) { // 2 = RUC (Tax ID)
        toast.add({
            severity: 'warn',
            summary: 'Attention',
            detail: 'Data is automatically populated based on Tax ID (RUC) search.',
            life: 10000
        });
    }
}

function setTipoDocPago() {
    documentoEmpresa.value = "";
    razonSocial.value = "";
    direccionEmpresa.value = "";
    responsable.value = "";
    block_direction.value = false;

    if (tipoDocumentoEmpresa.value == 2) { //ruc
        selectTipoDocPago.value = 1; // factura
        block_direction.value = true;
    } else {
        selectTipoDocPago.value = 2; //boleta
    }
}

function changeCategory(id, precio) {
    current_price = precio;

    // 1. Buscamos la categoría completa
    const categoria = props.categorias.find(c => c.id === id);


    if (categoria) {
        // --- A. LÓGICA DE DOCUMENTOS ---
        show_document.value = categoria.requiere_documento ? true : false;
        upload_instruction.value = '';

        if (!show_document.value) {
            uploadDocument.value = null;
            src.value = null;
        } else {
            // <--- 2. LÓGICA PARA ASIGNAR EL TEXTO SEGÚN EL NOMBRE DE LA CATEGORÍA --->
            const nombre = categoria.nombre_en.toUpperCase();

            // CASO 1: DOCENTE AUTOR (Faculty member Author)
            if ((nombre.includes('AUTHOR') || nombre.includes('AUTOR')) && (nombre.includes('FACULTY') || nombre.includes('DOCENTE'))) {
                upload_instruction.value = "Special rate for undergraduate faculty members whose work has been selected, upon presentation of valid proof of their status.";
            }
            // CASO 2: ESTUDIANTE AUTOR (Student Author)
            else if ((nombre.includes('AUTHOR') || nombre.includes('AUTOR')) && (nombre.includes('STUDENT') || nombre.includes('ESTUDIANTE'))) {
                upload_instruction.value = "Rate for undergraduate students with selected papers, upon presentation of an updated proof of enrollment.";
            }
            // CASO 3: DOCENTE ASISTENTE GENERAL (General Attendee – Faculty member)
            else if (nombre.includes('FACULTY') || nombre.includes('DOCENTE')) {
                upload_instruction.value = "Special rate for undergraduate faculty members, who must present valid proof of their status.";
            }
            // CASO 4: ESTUDIANTE ASISTENTE GENERAL (General Attendee – Student)
            else if (nombre.includes('STUDENT') || nombre.includes('ESTUDIANTE')) {
                upload_instruction.value = "Rate applicable to undergraduate students in their 9th or 10th semester, upon presentation of an updated proof of enrollment.";
            }
            // CASO POR DEFECTO
            else {
                upload_instruction.value = "Please upload the required document for this category.";
            }
        }

        // --- B. LÓGICA DE DÍAS  ---
        const nombreMayus = categoria.nombre_es.toUpperCase();

        // CAMBIO AQUÍ: Usamos Regex para buscar la PALABRA EXACTA "DIA"
        // \b significa "borde de palabra". Así evitamos que detecte "MEDIA"
        if (/\bDIA\b/.test(nombreMayus)) {

            total.value = 0;
            show_days.value = true;
            selectedDays.value = [];

            for (const key in current_days) {
                current_days[key] = false;
            }

        } else {
            // Es una categoría normal (Estudiante, Socio Full, etc.)
            total.value = precio;
            show_days.value = false;
        }
    }
}

function selectDays(id) {
    var cantidad_dias = 0;
    current_days[id] = !current_days[id];
    for (const key in current_days) {
        if (current_days[key]) {
            cantidad_dias++;
        }
    }

    total.value = cantidad_dias * current_price;
}

// function getInscripcion() {

//     if (terminos.value != true) {
//         toast.add({ severity: 'error', summary: 'Es requerido aceptar los Términos y Condiciones', life: 2000 });
//         return { "validate": false };
//     }

//     if (reglamento.value != true) {
//         toast.add({ severity: 'error', summary: 'Es requerido aceptar el Reglamento', life: 2000 });
//         return { "validate": false };
//     }

//     if ((Object.keys(errors._value).length > 0)) {
//         toast.add({ severity: 'error', summary: 'Complete todos los campos requeridos', life: 2000 });
//         return { "validate": false };
//     }

//     if (typeof documentoEmpresa.value != 'undefined') {
//         if (documentoEmpresa.value.length != 11 && tipoDocumentoEmpresa.value == 2) {
//             toast.add({ severity: 'error', summary: 'El RUC debe ser de 11 caracteres', life: 2000 });
//             return { "validate": false };
//         }

//         if (documentoEmpresa.value.length != 8 && tipoDocumentoEmpresa.value == 1) {
//             toast.add({ severity: 'error', summary: 'El DNI debe ser de 8 caracteres', life: 2000 });
//             return { "validate": false };
//         }
//     } else {
//         toast.add({ severity: 'error', summary: 'Debe ingresar un documento para facturación', life: 2000 });
//         return { "validate": false };
//     }

//     if (total.value == 0) {
//         toast.add({ severity: 'error', summary: 'El total debe ser mayor a 0', life: 2000 });
//         return { "validate": false };
//     }

//     if (show_document.value == true && uploadDocument.value === null) {
//         toast.add({ severity: 'error', summary: 'Debe elegir un documento para su inscripción', life: 2000 });
//         return { "validate": false };
//     }

//     if (props.data_persona.persona.nombres.length == 0 || props.data_persona.persona.apellido_paterno.length == 0) {
//         props.data_persona.persona.nombres = nombres.value;
//         props.data_persona.persona.apellido_paterno = apellido_paterno.value;
//     }

//     return {
//         "validate": true,
//         "formInscription": values
//     };

// }


// Agrega esto cerca de tus otros refs

function getInscripcion() {
    // Reiniciamos errores manuales
    formManualErrors.value = { reglamento: null, total: null, uploadDocument: null };
    let hasError = false;

    // 1. Reglamento
    if (reglamento.value !== true) {
        formManualErrors.value.reglamento = "You must accept the Terms and Conditions.";
        hasError = true;
    }

    // 2. Errores de vee-validate (Inputs normales)
    if (Object.keys(errors._value).length > 0) {
        hasError = true;
    }

    // 3. Documento de Facturación (Validación manual de longitud)
    // Nota: Vee-validate ya debería manejar esto por el esquema Yup,
    // pero si necesitas forzar el scroll o alerta extra:
    if (!documentoEmpresa.value) {
        hasError = true;
    }

    // 4. Total (Días o Categoría)
    if (total.value <= 0) {
        formManualErrors.value.total = "The total amount must be greater than 0. Please select a category or specific days.";
        hasError = true;
    }

    // 5. Carga de archivo
    if (show_document.value === true && !uploadDocument.value) {
        formManualErrors.value.uploadDocument = "Please upload the required documentation for your category.";
        hasError = true;
    }

    if (hasError) {
        toast.add({ severity: 'error', summary: 'ATTENTION: ACTION REQUIRED', detail: 'Please complete all required fields marked in red before proceeding.', life: 10000 });
        return { "validate": false };
    }

    // Sync names if empty
    if (props.data_persona.persona.nombres.length === 0 || props.data_persona.persona.apellido_paterno.length === 0) {
        props.data_persona.persona.nombres = nombres.value;
        props.data_persona.persona.apellido_paterno = apellido_paterno.value;
    }

    return { "validate": true, "formInscription": values };
}

defineExpose({
    getInscripcion
});

</script>

<template>

    <div class="gap-6 p-6 w-full justify-around overflow-visible">
        <!--         DETALLES PERSONALES             -->
        <!-- ======================================== -->
        <div class="text-green-iimp font-bold p-4">
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
                                <label for="nombres" class="">First Name <span
                                        class="font-normal text-red-600">*</span></label>
                                <InputText name="nombres" v-model="nombres" v-bind="nombresAttrs"
                                    class="w-full border-green-iimp" />
                                <span class="font-normal text-red-600">{{ errors.nombres }}</span>
                            </div>
                            <div class="w-full">
                                <label for="apellido_paterno" class="">Last Name <span
                                        class="font-normal text-red-600">*</span></label>
                                <InputText name="apellido_paterno" v-model="apellido_paterno"
                                    v-bind="apellido_paternoAttrs" class="w-full border-green-iimp" />
                                <span class="font-normal text-red-600">{{ errors.apellido_paterno }}</span>
                            </div>
                        </div>

                        <div class="grid gap-6 m-6 grid-cols-1 md:grid-cols-4">
                            <div class="w-full">
                                <label for="pais" class="">Country <span
                                        class="font-normal text-red-600">*</span></label>
                                <Select name="pais" v-model="pais" v-bind="paisAttrs" optionLabel="name"
                                    optionValue="id" placeholder="Select" showClear filter @change="loadDepartamentos"
                                    :options="paises" class="w-full border-green-iimp" />
                                <span class="font-normal text-red-600">{{ errors.pais }}</span>
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
                            <div class="w-full">
                                <label for="provincia" class="">Province <span
                                        class="font-normal text-red-600">*</span></label>
                                <Select name="provincia" v-model="provincia" v-bind="provinciaAttrs" filter
                                    @change="loadDistritos" :options="provincias" optionLabel="name"
                                    optionValue="id_provincia" placeholder="Select" showClear
                                    class="w-full border-green-iimp" />
                                <span class="font-normal text-red-600">{{ errors.provincia }}</span>
                            </div>
                            <div class="w-full">
                                <label for="distrito" class="">District <span
                                        class="font-normal text-red-600">*</span></label>
                                <Select name="distrito" v-model="distrito" v-bind="distritoAttrs" filter
                                    :options="distritos" optionLabel="name" optionValue="id_distrito"
                                    placeholder="Select" showClear class="w-full border-green-iimp" />
                                <span class="font-normal text-red-600">{{ errors.distrito }}</span>
                            </div>
                        </div>

                        <div class="grid gap-6 m-6 md:grid-cols-2">
                            <div class="grid gap-6 md:grid-cols-2">
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
                                    <span class="font-normal text-red-600">{{ errors.id_tipo_celular }}</span>
                                </div>
                            </div>
                            <div class="w-full">
                                <label for="direccionPersona" class="">Address <span
                                        class="font-normal text-red-600">*</span></label>
                                <InputText name="direccionPersona" v-model="direccionPersona"
                                    v-bind="direccionPersonaAttrs" class="w-full border-green-iimp" />
                                <span class="font-normal text-red-600">{{ errors.direccionPersona }}</span>
                            </div>
                        </div>

                        <div class="grid gap-6 m-6 md:grid-cols-3">
                            <div class="w-full">
                                <label for="fecha_nacimiento" class="">Date of Birth <span
                                        class="font-normal text-red-600">*</span></label>
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
                                <label for="empresa" class="">Company <span
                                        class="font-normal text-red-600">*</span></label>
                                <InputText name="empresa" v-model="empresa" v-bind="empresaAttrs"
                                    class="w-full border-green-iimp" />
                                <span class="font-normal text-red-600">{{ errors.empresa }}</span>
                            </div>
                            <div class="w-full">
                                <label for="cargo" class="">Job Title <span
                                        class="font-normal text-red-600">*</span></label>
                                <InputText name="cargo" v-model="cargo" v-bind="cargoAttrs"
                                    class="w-full border-green-iimp" />
                                <span class="font-normal text-red-600">{{ errors.cargo }}</span>
                            </div>
                        </div>

                        <div class="grid gap-6 m-6 md:grid-cols-2">
                            <div class="grid gap-6 md:grid-cols-2">
                                <div class="w-full">
                                    <label for="sexo" class="">Sex <span
                                            class="font-normal text-red-600">*</span></label>
                                    <Select name="sexo" v-model="sexo" v-bind="sexoAttrs" optionLabel="label"
                                        optionValue="value" placeholder="Elegir" showClear checkmark :options="generos"
                                        class="w-full border-green-iimp" />
                                    <span class="font-normal text-red-600">{{ errors.sexo }}</span>
                                </div>
                                <div class="w-full">
                                    <label for="nacionalidad" class="">Nationality <span
                                            class="font-normal text-red-600">*</span></label>
                                    <Select name="nacionalidad" v-model="nacionalidad" v-bind="nacionalidadAttrs" filter
                                        :options="nacionalidades" optionLabel="name" optionValue="id"
                                        placeholder="Elegir" showClear class="w-full border-green-iimp" />
                                    <span class="font-normal text-red-600">{{ errors.nacionalidad }}</span>
                                </div>
                            </div>
                            <div class="w-full">
                                <label for="credencial" class="">Credential Name
                                    <span class="text-xs text-gray-400">(Short name for credential)</span>
                                    <span class="font-normal text-red-600">*</span>
                                </label>
                                <InputText name="credencial" v-model="credencial" v-bind="credencialAttrs"
                                    class="w-full border-green-iimp" />
                                <span class="font-normal text-red-600">{{ errors.credencial }}</span>
                            </div>
                        </div>

                    </div>
                </template>
            </Card>
        </div>
        <!--          CATEGORIAS                      -->
        <!-- ======================================== -->
        <div class="text-green-iimp font-bold p-4">
            <Card class="mt-5 overflow-hidden shadow-lg border border-gray-200">
                <template #header>
                    <div
                        class="w-full py-3 text-xl font-bold text-center bg-lightblue-wmc border-blue-wmc text-blue-900">
                        Category
                    </div>
                </template>

                <template #content>
                    <div class="px-2">
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
                    <Card v-if="show_days" class="m-6">
                        <template #content>
                            <div v-if="formManualErrors.total"
                                class="mt-4 flex items-center gap-3 rounded border-l-4 border-red-500 bg-red-50 px-4 py-2 text-red-800 shadow-sm animate-fade-in">
                                <i class="pi pi-times-circle"></i>
                                <span class="text-xs font-bold">{{ formManualErrors.total }}</span>
                            </div>
                            <div class=" flex justify-around">
                                <div v-for="(day, key) in days" :key="key">
                                    <Checkbox :inputId="day" :value="key" v-model="selectedDays"
                                        v-bind="selectedDaysAttrs" name="selectedDays" @click="selectDays(key)" />
                                    <label :for="day" class=" pl-3">{{ day }}</label>
                                </div>
                            </div>
                            <div class="flex justify-around mt-6 w-[100%]">
                                <div class="text-yellow-price min-w-[150px] flex justify-between">
                                    <label for="">TOTAL :</label>
                                    <label for="">USD {{ total }}</label>
                                </div>
                            </div>
                        </template>

                    </Card>

                    <Card v-if="show_document" class="m-6">
                        <template #content>
                            <div v-if="upload_instruction"
                                class="mb-4 p-4 bg-blue-50 border-l-4 border-blue-500 text-blue-700">
                                <p class="text-sm font-bold">Requirement:</p>
                                <p class="text-sm">{{ upload_instruction }}</p>
                            </div>

                            <div class="flex justify-center mb-4 w-full">
                                <img v-if="src" :src="src" alt="Vista previa"
                                    class="shadow-md rounded-lg border border-gray-200 max-w-[200px] max-h-[200px] object-contain" />
                            </div>

                            <div class="flex flex-col items-center justify-center w-full">

                                <div v-if="formManualErrors.uploadDocument"
                                    class="w-full mb-4 flex items-center gap-3 rounded border-l-4 border-red-500 bg-red-50 px-4 py-2 text-red-800 shadow-sm animate-fade-in">
                                    <i class="pi pi-times-circle"></i>
                                    <span class="text-xs font-bold">{{ formManualErrors.uploadDocument }}</span>
                                </div>
                                <div v-if="fileErrors.length > 0"
                                    class="w-full md:w-3/4 mb-4 p-3 bg-red-50 border border-red-200 rounded-md text-center mx-auto">
                                    <div v-for="(error, index) in fileErrors" :key="index"
                                        class="flex items-center justify-center gap-2 text-red-600 font-bold mb-1 last:mb-0">
                                        <i class="pi pi-exclamation-triangle"></i>
                                        <span class="text-sm">{{ error }}</span>
                                    </div>
                                </div>


                                <div class="flex justify-center items-center w-full text-center">
                                    <FileUpload ref="fileupload" mode="basic"
                                        class="p-button-outlined text-green-iimp mx-auto" :auto="true" customUpload
                                        :chooseLabel="'Choose File'" :uploadLabel="'Cargar'" @select="onFileSelect"
                                        name="uploadDocument" v-model="uploadDocument" v-bind="uploadDocumentAttrs" />
                                </div>

                                <small
                                    class="text-slate-500 mt-3 font-medium text-center block w-full text-xs leading-5">
                                    <i class="pi pi-file-check mr-1"></i>
                                    <span>Accepted formats: </span>
                                    <span class="font-bold text-slate-600">PDF, Image(JPG, JPEG, PNG)</span>.
                                    <br>
                                    <span>Max size: </span>
                                    <span class="font-bold text-slate-600">6MB</span>.
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
                        Billing Information</div>
                </template>


                <template #content>
                    <div v-if="billingMessage"
                        class=" flex items-start gap-2 rounded border-l-4 px-2 py-3 shadow-sm animate-fade-in"
                        :class="{
                            'bg-green-50 border-green-500 text-green-800': billingMessage.type === 'success',
                            'bg-orange-50 border-orange-500 text-orange-800': billingMessage.type === 'warn',
                            'bg-red-50 border-red-500 text-red-800': billingMessage.type === 'error'
                        }">

                        <i class="pi mt-0.5" :class="{
                            'pi-check-circle': billingMessage.type === 'success',
                            'pi-exclamation-triangle': billingMessage.type === 'warn',
                            'pi-times-circle': billingMessage.type === 'error'
                        }"></i>

                        <span class="text-sm font-normal text-left">{{ billingMessage.text }}</span>
                    </div>
                    <div class="py-2">
                        <div
                            class=" flex items-start gap-2 rounded-md border border-sky-300 bg-sky-50 px-2 py-3 text-sm text-sky-800">
                            <i class="pi pi-info-circle mt-0.5"></i>
                            <p class="text-left font-normal">
                                Participants registering with <strong>PASSPORT</strong>, <strong>DNI</strong> or
                                <strong>RUT</strong>
                                can
                                request a <strong>Boleta</strong> only. <strong>Factura</strong> is available
                                exclusively for
                                <strong>RUC</strong>.
                            </p>
                        </div>
                    </div>

                    <div
                        class=" flex items-start gap-2 rounded-md border border-sky-300 bg-sky-50 px-2 py-2 text-sm text-sky-800">
                        <i class="pi pi-exclamation-triangle mt-0.5"></i>
                        <div class="text-left font-normal">
                            <p class="mb-1">
                                <strong>Note:</strong> Only Peruvian companies have a <strong>RUC</strong>. Please
                                ensure you enter the data correctly to avoid errors in your receipt.
                            </p>
                        </div>
                    </div>
                    <div class="grid gap-6 m-6 md:grid-cols-2">
                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="col-span-3 sm:col-span-1">
                                <label for="tipoDocumentoEmpresa" class="">Document Type <span
                                        class="font-normal text-red-600">*</span></label>
                                <Select name="tipoDocumentoEmpresa" v-model="tipoDocumentoEmpresa"
                                    v-bind="tipoDocumentoEmpresaAttrs" :options="tipoDocumento" optionLabel="name_en"
                                    optionValue="id" placeholder="Elegir" showClear checkmark
                                    class="w-full border-green-iimp" @change="setTipoDocPago" />
                                <span class="font-normal text-red-600">{{ errors.tipoDocumentoEmpresa }}</span>

                            </div>

                            <div class="col-span-3 sm:col-span-1">
                                <label for="documentoEmpresa">Document Number <span
                                        class="text-red-600">*</span></label>
                                <InputGroup>
                                    <InputText name="documentoEmpresa" v-model="documentoEmpresa"
                                        v-bind="documentoEmpresaAttrs" class="border-green-iimp"
                                        @keypress="onlyNumberKey" :maxlength="25" />
                                    <Button icon="pi pi-search" class="border-green-iimp bg-green-iimp"
                                        @click="getEmpresaData" :disabled="!documentoEmpresa || loading_doc"
                                        :loading="loading_doc" />
                                </InputGroup>
                                <span class="font-normal text-red-600">{{ errors.documentoEmpresa }}</span>


                            </div>
                        </div>
                        <div class="w-full sm:col-span-1">
                            <label for="razonSocial" class="">Business Name <span
                                    class="font-normal text-red-600">*</span></label>
                            <InputText name="razonSocial" v-model="razonSocial" v-bind="razonSocialAttrs"
                                class="w-full border-green-iimp" :readonly="block_direction" @click="check_fact" />
                            <span class="font-normal text-red-600">{{ errors.razonSocial }}</span>
                        </div>

                    </div>

                    <div class="grid gap-6 m-6 md:grid-cols-2">
                        <div class="w-full sm:col-span-1">
                            <label for="direccionEmpresa" class="">Company Address <span
                                    class="font-normal text-red-600">*</span></label>
                            <InputText name="direccionEmpresa" v-model="direccionEmpresa" v-bind="direccionEmpresaAttrs"
                                class="w-full border-green-iimp" autocomplete="nueva-direccion"
                                :readonly="block_direction" @click="check_fact" />
                            <span class="font-normal text-red-600">{{ errors.direccionEmpresa }}</span>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="w-full sm:col-span-1">
                                <label for="responsable" class="">Billing Contact <span
                                        class="font-normal text-red-600">*</span></label>
                                <InputText name="responsable" v-model="responsable" v-bind="responsableAttrs"
                                    class="w-full border-green-iimp" />
                                <span class="font-normal text-red-600">{{ errors.responsable }}</span>
                            </div>

                            <div class="w-full sm:col-span-1">
                                <label for="correo_facturador" class="">Billing Email <span
                                        class="font-normal text-red-600">*</span></label>
                                <InputText name="correo_facturador" v-model="correo_facturador"
                                    v-bind="correo_facturadorAttrs" class="w-full border-green-iimp"
                                    autocomplete="nuevo-email" />
                                <span class="font-normal text-red-600">{{ errors.correo_facturador }}</span>
                            </div>

                        </div>
                    </div>

                    <div class="flex justify-around w-full mb-4">
                        <Card
                            class="gap-3 text-center w-full md:w-auto md:min-w-[450px] shadow-lg border border-gray-200">
                            <template #content>
                                <div class="text-lg font-semibold m-4">Payment Document</div>

                                <div class="flex flex-wrap justify-center gap-3">
                                    <div class="flex items-center" v-for="tipodocpago in tipoDocumentoPago"
                                        :key="tipodocpago.id">
                                        <RadioButton v-model="selectTipoDocPago" v-bind="selectTipoDocPagoAttrs"
                                            :inputId="tipodocpago.nombre" name="tipodocpago" :value="tipodocpago.id"
                                            @click="$event.preventDefault()" />
                                        <label :for="tipodocpago.nombre" class="ml-2 cursor-pointer">{{
                                            tipodocpago.nombre }}</label>
                                    </div>
                                </div>
                                <span class="font-normal text-red-600 block mt-2">{{ errors.selectTipoDocPago }}</span>
                            </template>
                        </Card>
                        <!-- <Card class="gap-3 text-center min-w-[450px]">
                            <template #content>
                                <div class="text-lg font-semibold m-4">Payment Method</div>

                                <div class="flex flex-wrap justify-center gap-3">
                                    <div class="flex items-center">
                                        <RadioButton v-model="selectTipoPago" v-bind="selectTipoPagoAttrs"
                                            name="tipodocfac" :value="3" class="radio-green-iimp "
                                            @click="$event.preventDefault()" />
                                        <label class="ml-2">Card</label>
                                    </div>
                                </div>
                                <span class="font-normal text-red-600">{{ errors.selectTipoPago }}</span>
                            </template>
                        </Card> -->
                    </div>

                </template>

            </Card>
        </div>
        <!--      TERMINOS Y REGLAS DE PARTICIPACION  -->
        <!-- ======================================== -->
        <!-- <div class="text-green-iimp font-bold p-4 flex justify-between">
            <div class="">
                <Checkbox :binary="true" v-model="terminos" v-bind="terminosAttrs" name="terminos" />
                <label for="terminos" class="pl-2 cursor-pointer" @click="showModal">Terms and Conditions of
                    Participation <span class="font-normal text-red-600">*</span></label>

            </div> -->

        <!-- <div class="">
                <Checkbox :binary="true" v-model="reglamento" v-bind="reglamentoAttrs" name="reglamento" />
                <a :href="reglamento_inscripciones" target="_blank" rel="noopener noreferrer" title="Ver reglamento">
                    <label for="reglamento" class="pl-2 cursor-pointer">Term and Conditions<span
                            class="font-normal text-red-600">*</span></label>
                </a>
            </div> -->

        <!-- </div> -->
        <div class="text-green-iimp font-bold p-4">
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
        </div>

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
