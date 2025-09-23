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
import { ref, onMounted, computed, watch  } from 'vue';
import { useForm } from 'vee-validate';
import * as yup from 'yup';
import { usePage, router } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import Functions from '@/Functions';
import FileUpload from 'primevue/fileupload';

import "../../../css/inscripciones.css";

const page = usePage();
const toast = useToast();
const props = defineProps({
    data_persona: Object,
    categorias: Object
});

const es_socio = ref(false);
const show_days = ref(false);
const show_document = ref(false);
const total = ref(0);
const src = ref(null);
const block_direction = ref(false);
const maxSize = 520000;
let current_price = 0;

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
const days = { 'mar' : 'Martes' , 'mie' : 'Miercoles' , 'jue' : 'Jueves' , 'vie' : 'Viernes'};//'lun' : 'Lunes',
const current_days = { 'lun' : false , 'mar' : false , 'mie' : false , 'jue' : false , 'vie' : false };

const { defineField, errors, handleSubmit, setValues, resetForm ,values  } = useForm({
    validationSchema: yup.object({
        tipoDocumentoEmpresa: yup.mixed().required('Tipo documento de Empresa es requerido'),
        documentoEmpresa: yup.number().when(
            'tipoDocumentoEmpresa',
            (tipoDocumentoEmpresa) => {
                if (typeof tipoDocumentoEmpresa[0] != 'undefined') {
                    if (tipoDocumentoEmpresa[0] == 2) {
                        return yup.number().typeError('El valor debe ser numérico').test('len', 'Debe tener exactamente 11 dígitos', val => val && val.toString().length === 11)
                    } else if (tipoDocumentoEmpresa[0] == 1){
                        return yup.number().typeError('El valor debe ser numérico').test('len', 'Debe tener exactamente 8 dígitos', val => val && val.toString().length === 8)
                    }   else {
                        return yup.string().matches(/^[a-zA-Z0-9]+$/, "El valor debe ser numéros o letras").required('Documento es requerido')
                    }
                }
            },
        ),
        nombres: yup.string().required('Nombre es requerido'),
        apellido_paterno: yup.string().required('Apellido Paterno es requerido'),
        fecha_nacimiento: yup.mixed().required('Fecha de nacimiento es requerida'),
        sexo: yup.mixed().required('Sexo es requerido'),
        correo: yup.string().required('Correo es requerido').email('Ingrese un Email válido'),
        celular: yup.string().required('Celular es requerido'),
        pais: yup.mixed().required('Pais es requerido'),
        nacionalidad: yup.mixed().required('Nacionalidad es requerida'),
        direccionPersona: yup.string().required('Direccion de inscrito es requerida'),
        empresa: yup.string().required('Empresa es requerida'),
        cargo: yup.string().required('Cargo es requerido'),
        credencial: yup.string().required('Nombre en credencial es requerido'),

        razonSocial: yup.string().required('Razón social es requerido'),
        direccionEmpresa: yup.string().required('Dirección de empresa es requerida'),
        responsable: yup.string().required('Nombre en responsable es requerido'),
        correo_facturador: yup.string().required('Email de Facturación es requerido').email('Ingrese un Email válido'),
        selectTipoPago: yup.string().required('Tipo de pago es requerido'),
        selectTipoDocPago: yup.string().required('Seleccionar tipo de documento de pago es requerido'),

        terminos: yup.string().required('Es requerido aceptar los Términos y Condiciones'),
        reglamento: yup.string().required('Es requerido aceptar el Reglamento'),
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

onMounted(() => {
    tipoDocumentoEmpresa.value = 2;
    selectTipoDocPago.value = 1;
    selectTipoPago.value = 3;
})

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

const getEmpresaData = async () => {
    razonSocial.value = '';
    direccionEmpresa.value = '';
    var not_found = 'No se encontraron datos. Por favor, complete los campos manualmente.';

            try {
                const empresa = await Functions.getEmpresaData(documentoEmpresa.value, tipoDocumentoEmpresa.value);

                if (empresa) {
                    // Autocompleta TODOS los campos desde la BD
                    razonSocial.value = empresa.empresa.nombre || '';
                    direccionEmpresa.value = empresa.empresa.direccionEmpresa || '';

                    if(empresa.status){
                        if(tipoDocumentoEmpresa.value == 2){ //ruc
                            block_direction.value = true;
                        }
                        toast.add({
                            severity: 'success',
                            summary: 'Datos Encontrados',
                            life: 2500
                        })

                    }else{
                        if(tipoDocumentoEmpresa.value == 2){ //ruc
                            not_found = "No se encontraron datos. Verifique el RUC ingresado";
                        }

                        toast.add({
                            severity: 'warn',
                            summary: 'No encontrado',
                            detail: not_found,
                            life: 3000
                        })
                    }

                    return;
                }else{
                    if(tipoDocumentoEmpresa.value == 2){ //ruc
                            not_found = "No se encontraron datos. Verifique el RUC ingresado";
                        }

                        toast.add({
                            severity: 'warn',
                            summary: 'No encontrado',
                            detail: not_found,
                            life: 3000
                        })

                    return;

                }
            } catch (e) {

                toast.add({
                            severity: 'warn',
                            summary: 'No encontrado',
                            detail: 'Error en la consulta, complete los campos manualmente.',
                            life: 3000
                        })
            }

}

const onFileSelect = (event) => {
  const file = event.files[0];
  uploadDocument.value = event.files[0];

  if (file.size > maxSize) {
    toast.add({ severity: 'error', summary: `"${file.name}" supera el tamaño máximo de 500 KB.`, life: 2000 });
    return;
  }

  const reader = new FileReader();

    reader.onload = async (e) => {
        if( file.type == "application/pdf"){
            src.value = '/images/pdf-file-document.png';
        }else{
            src.value = e.target.result;
        }
    };

    reader.readAsDataURL(file);
}

watch(() => props.data_persona, (newVal, oldVal) => {
    empresa.value = '';
    credencial.value = '';
    tipoDocumentoEmpresa.value = 2;
    selectTipoDocPago.value = 1;
    selectTipoPago.value = 3;
    documentoEmpresa.value = '';
    razonSocial.value = '';
    direccionEmpresa.value = '';
    responsable.value = '';
    show_days.value = false;
    show_document.value = false;
    src.value = null;
    block_direction.value = true;

    if(typeof props.data_persona.persona != 'undefined' ){
        es_socio.value =  props.data_persona.persona.es_socio;

        if(props.categorias[0].control == 'CV'){
            for(var i = 0; props.categorias.length; i++){

                if(newVal.persona.es_socio && props.categorias[i].condicion == 'SO'){
                    selected_categoria.value = props.categorias[i].id;
                    total.value = props.categorias[i].precio_disponible.valor;
                    break;
                }
                if(!newVal.persona.es_socio && props.categorias[i].condicion == 'NS'){
                    selected_categoria.value = props.categorias[i].id;
                    total.value = props.categorias[i].precio_disponible.valor;
                    break;
                }
                if (props.categorias[i].condicion != 'SO' && props.categorias[i].condicion != 'NS'){
                    selected_categoria.value = props.categorias[i].id;
                    total.value = props.categorias[i].precio_disponible.valor;
                    if(props.categorias[i].requiere_documento){
                        show_document.value = true;
                    }
                    break;
                }
            }
        }else{
            selected_categoria.value = props.categorias[0].id;
            total.value = props.categorias[0].precio_disponible.valor;;
        }

        props.data_persona.persona.fecha_nacimiento = Functions.toLocalDateOnly(newVal.persona.fecha_nacimiento);
        setValues(newVal.persona);
        loadDepartamentos()
        setValues(newVal.persona );
        loadProvincias()
        setValues(newVal.persona );
        loadDistritos()
        setValues(newVal.persona );

    }
});

const onlyNumberKey = (event) => {
    if(tipoDocumentoEmpresa.value == 2){
        block_direction.value = true;
    }else{
        block_direction.value = false;
    }


    if( tipoDocumentoEmpresa.value == 2 || tipoDocumentoEmpresa.value == 1 ){
        const charCode = event.charCode ? event.charCode : event.keyCode
        if (charCode < 48 || charCode > 57) {
            event.preventDefault()
        }else{
            if(typeof documentoEmpresa.value != 'undefined' ){
                if(documentoEmpresa.value.length == 11 && tipoDocumentoEmpresa.value == 2){
                    event.preventDefault()
                }

                if(documentoEmpresa.value.length == 8 && tipoDocumentoEmpresa.value == 1){
                    event.preventDefault()
                }
            }

        }
    }else{
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

const check_fact = () =>{
    if( tipoDocumentoEmpresa.value == 2){
                toast.add({
                                severity: 'warn',
                                summary: 'Atencion',
                                detail: 'Datos automaticos con la busqueda de RUC',
                                life: 3000
                            })
            }
    }

function setTipoDocPago(){
    documentoEmpresa.value = "";
    razonSocial.value = "";
    direccionEmpresa.value = "";
    responsable.value = "";
    block_direction.value = false;

    if(tipoDocumentoEmpresa.value == 2){ //ruc
        selectTipoDocPago.value = 1; // factura
        block_direction.value = true;
    }else{
        selectTipoDocPago.value = 2; //boleta
    }
}

function changeCategory(id, precio){
    current_price = precio;
    if(selected_categoria.value != id){
        if(id == 12 || id == 13){

            total.value = 0;
            show_days.value = true;
            selectedDays.value = [];

            for (const key in current_days) {
                current_days[key] = false;
            }

        }else{
            total.value = precio;
            show_days.value = false;
        }
    }
}

function selectDays(id){
    var cantidad_dias = 0;
    current_days[id] = !current_days[id];
    for (const key in current_days) {
        if(current_days[key] ){
            cantidad_dias++;
        }
    }

    total.value = cantidad_dias * current_price;
}

function getInscripcion() {

    if(terminos.value != true){
        toast.add({ severity: 'error', summary: 'Es requerido aceptar los Términos y Condiciones', life: 2000 });
        return { "validate" : false };
    }

    if(reglamento.value != true){
        toast.add({ severity: 'error', summary: 'Es requerido aceptar el Reglamento', life: 2000 });
        return { "validate" : false };
    }

    if( (Object.keys(errors._value).length > 0) ){
        toast.add({ severity: 'error', summary: 'Complete todos los campos requeridos', life: 2000 });
        return { "validate" : false };
    }

    if(typeof documentoEmpresa.value != 'undefined' ){
        if( documentoEmpresa.value.length != 11 && tipoDocumentoEmpresa.value == 2 ){
            toast.add({ severity: 'error', summary: 'El RUC debe ser de 11 caracteres', life: 2000 });
            return { "validate" : false };
        }

        if( documentoEmpresa.value.length != 8 && tipoDocumentoEmpresa.value == 1 ){
            toast.add({ severity: 'error', summary: 'El DNI debe ser de 8 caracteres', life: 2000 });
            return { "validate" : false };
        }
    }else{
        toast.add({ severity: 'error', summary: 'Debe ingresar un documento para facturación', life: 2000 });
        return { "validate" : false };
    }

    if(total.value == 0){
        toast.add({ severity: 'error', summary: 'El total debe ser mayor a 0', life: 2000 });
        return { "validate" : false };
    }

    if(show_document.value == true  && uploadDocument.value === null){
        toast.add({ severity: 'error', summary: 'Debe elegir un documento para su inscripción', life: 2000 });
        return { "validate" : false };
    }

    if(props.data_persona.persona.nombres.length ==0 || props.data_persona.persona.apellido_paterno.length ==0){
        props.data_persona.persona.nombres = nombres.value;
        props.data_persona.persona.apellido_paterno = apellido_paterno.value;
    }

    return { "validate" : true,
            "formInscription" : values
    };

}

defineExpose({
  getInscripcion
});

</script>

<template>

        <div class="gap-6 p-6 w-full justify-around overflow-visible">
            <div class ="text-green-iimp font-bold p-4">

                <div class ="text-green-iimp font-bold text-center p-4">
                    <span class="text-3xl">Datos Personales</span>
                </div>
                <Divider />
                <div class="col-span-1 sm:col-span-1 p-4">

                    <div>
                        <div class="grid gap-6 m-6 md:grid-cols-2" >
                            <div class="w-full sm:col-span-1">
                                    <label for="nombres" class="">Nombres*</label>
                                    <InputText name="nombres" v-model="nombres" v-bind="nombresAttrs" class="w-full border-green-iimp"
                                                                        />
                                    <span class="font-normal text-red-600">{{ errors.nombres }}</span>
                            </div>
                            <div class="grid gap-6 md:grid-cols-2" >
                                <div class="col-span-3 sm:col-span-1">
                                    <label for="apellido_paterno" class="">Apellido Paterno*</label>
                                    <InputText name="apellido_paterno" v-model="apellido_paterno" v-bind="apellido_paternoAttrs" class="w-full border-green-iimp"
                                    />
                                    <span class="font-normal text-red-600">{{ errors.apellido_paterno }}</span>

                                </div>
                                <div class="col-span-3 sm:col-span-1">
                                        <label for="apellido_materno" class="">Apellido Materno</label>
                                        <InputText name="apellido_materno" v-model="apellido_materno" v-bind="apellido_maternoAttrs" class="w-full border-green-iimp"
                                         />
                                        <span class="font-normal text-red-600">{{ errors.apellido_materno }}</span>
                                </div>
                            </div>

                        </div>

                        <div class="grid gap-6 m-6 md:grid-cols-4" >

                                <div class="col-span-3 sm:col-span-1">
                                    <label for="pais" class="">País*</label>
                                    <Select name="pais" v-model="pais" v-bind="paisAttrs"
                                            optionLabel="name" optionValue="id" placeholder="Elegir" showClear filter @change="loadDepartamentos" :options="paises"
                                            class="w-full border-green-iimp" />
                                    <span class="font-normal text-red-600">{{ errors.pais }}</span>

                                </div>
                                <div class="col-span-3 sm:col-span-1">
                                        <label for="departamento" class="">Departamento</label>
                                        <Select name="departamento" v-model="departamento" v-bind="departamentoAttrs" filter @change="loadProvincias" :options="departamentos"
                                            optionLabel="name" optionValue="id_departamento" placeholder="Elegir" showClear
                                            class="w-full border-green-iimp" />
                                        <span class="font-normal text-red-600">{{ errors.departamento }}</span>
                                </div>

                                <div class="w-full sm:col-span-1">
                                        <label for="provincia" class="">Provincia</label>
                                        <Select name="provincia" v-model="provincia" v-bind="provinciaAttrs" filter @change="loadDistritos" :options="provincias"
                                            optionLabel="name" optionValue="id_provincia" placeholder="Elegir" showClear
                                            class="w-full border-green-iimp" />
                                        <span class="font-normal text-red-600">{{ errors.provincia }}</span>
                                </div>
                                <div class="w-full sm:col-span-1">
                                        <label for="distrito" class="">Distrito</label>
                                        <Select name="distrito" v-model="distrito" v-bind="distritoAttrs" filter :options="distritos"
                                            optionLabel="name" optionValue="id_distrito" placeholder="Elegir" showClear
                                            class="w-full border-green-iimp" />
                                        <span class="font-normal text-red-600">{{ errors.distrito }}</span>
                                </div>
                        </div>

                        <div class="grid gap-6 m-6 md:grid-cols-2" >
                            <div class="grid gap-6 md:grid-cols-2" >
                                <div class="col-span-3 sm:col-span-1">
                                        <label for="correo" class="">Correo Electrónico*</label>
                                        <InputText name="correo" v-model="correo" v-bind="correoAttrs" class="w-full border-green-iimp"
                                         />
                                        <span class="font-normal text-red-600">{{ errors.correo }}</span>
                                </div>
                                <div class="col-span-3 sm:col-span-1">
                                        <label for="celular" class="">Celular*</label>
                                        <InputText name="celular" v-model="celular" v-bind="celularAttrs" class="w-full border-green-iimp"
                                        />
                                        <span class="font-normal text-red-600">{{ errors.id_tipo_celular }}</span>

                                </div>

                            </div>
                            <div class="w-full sm:col-span-1">
                                    <label for="direccionPersona"  class="">Dirección*</label>
                                    <InputText name="direccionPersona" v-model="direccionPersona" v-bind="direccionPersonaAttrs" class="w-full border-green-iimp"
                                    />
                                    <span class="font-normal text-red-600">{{ errors.direccionPersona }}</span>
                            </div>

                        </div>

                        <div class="grid gap-6 m-6 md:grid-cols-3" >
                                <div class="w-full sm:col-span-1">
                                    <label for="fecha_nacimiento" class="">Fecha de Nacimiento*</label>
                                    <Calendar name="fecha_nacimiento" v-model="fecha_nacimiento" modelValue=undefined v-bind="fecha_nacimientoAttrs" :maxDate="today" showIcon
                                    iconDisplay="input" class="w-full leading-3 border-green-iimp" dateFormat="yy-mm-dd" :showTime="false" />
                                    <span class="font-normal text-red-600">{{ errors.fecha_nacimiento }}</span>
                                </div>

                                <div class="w-full sm:col-span-1">
                                        <label for="empresa" class="">Empresa*</label>
                                        <InputText name="empresa" v-model="empresa" v-bind="empresaAttrs" class="w-full border-green-iimp"
                                         />
                                        <span class="font-normal text-red-600">{{ errors.empresa }}</span>
                                </div>
                                <div class="w-full sm:col-span-1">
                                        <label for="cargo"  class="">Cargo*</label>
                                        <InputText name="cargo" v-model="cargo" v-bind="cargoAttrs" class="w-full border-green-iimp"
                                         />
                                        <span class="font-normal text-red-600">{{ errors.cargo }}</span>
                                </div>
                        </div>

                        <div class="grid gap-6 m-6 md:grid-cols-2" >
                            <div class="grid gap-6 md:grid-cols-2" >
                                <div class="col-span-3 sm:col-span-1">
                                        <label for="sexo" class="">Sexo*</label>
                                            <Select name="sexo" v-model="sexo" v-bind="sexoAttrs"
                                                optionLabel="label" optionValue="value" placeholder="Elegir" showClear checkmark :options="generos"
                                                 class="w-full border-green-iimp" />
                                        <span class="font-normal text-red-600">{{ errors.sexo }}</span>

                                </div>
                                <div class="col-span-3 sm:col-span-1">
                                        <label for ="nacionalidad" class="">Nacionalidad*</label>
                                        <Select name="nacionalidad" v-model="nacionalidad" v-bind="nacionalidadAttrs" filter  :options="nacionalidades"
                                            optionLabel="name" optionValue="id" placeholder="Elegir" showClear
                                             class="w-full border-green-iimp" />
                                        <span class="font-normal text-red-600">{{ errors.nacionalidad }}</span>
                                </div>
                            </div>
                            <div class="w-full sm:col-span-1">
                                        <label for ="credencial" class="">Nombre Credencial*</label>
                                        <InputText name="credencial" v-model="credencial" v-bind="credencialAttrs" class="w-full border-green-iimp"
                                         />
                                        <span class="font-normal text-red-600">{{ errors.credencial }}</span>
                                </div>
                        </div>



                        <div class="gap-6 m-6 bg-lightgreen-iimp p-4 rounded-lg mt-[50px]" >
                            <div class="p-2">
                                    Autorización para el tratamiento de Datos Personales
                            </div>
                            <div class="font-normal p-2">
                                    <p>De conformidad con lo dispuesto en la Ley N° 29733, Ley de Protección de Datos Personales y su Reglamento aprobado por Decreto Supremo N° 003-2013-JUS, a través del presente documento, otorgo mi consentimiento para que el Instituto de Ingenieros de Minas del Perú (en adelante, "IIMP") pueda efectuar el tratamiento de mis datos personales consignados en la presente ficha de inscripción y otros documentos oficiales de
                                    "PERUMIN 37" (en adelante, el "Evento"), tales como: nombres y apellidos, número de documento de identidad, dirección, sexo, profesión, entre otros datos, así como de toda aquella información registrada durante mi participación en el Evento, tales como: imagen física, fotografía, grabación en audio y/o video, entre otros; con la finalidad de que el IIMP pueda usarlos y/o reproducirlos libremente para fines de promoción del Evento y/o de posteriores ediciones, promoción de programas educativos o institucionales, eventos similares y otros permitidos por Ley.</p>
                                    <p>Este material podrá difundirse a través de spots televisivos y/o radiales, avisos en prensa escrita, afiches, volantes, encartes, folletos, banners impresos, sitios web, aplicativos (APP) del Evento y/o del IIMP, redes sociales, merchandising y cualquier otro tipo de material de soporte audio-.visual, ya sea en formato físico o digital. Suscribo esta autorización en señal de conformidad en todos sus extremos, la cual se otorga por tiempo indefinido y sin restricción geográfica.</p>
                            </div>
                            <div class="flex font-normal p-2 w-full justify-end">
                                    <Checkbox :binary="true" v-model="auth" v-bind="authAttrs" name="auth"/>
                                    <label for="auth" class="pl-2">Autorización para compartir Datos Personales</label>

                            </div>

                        </div>
                    </div>

                </div>
            </div>

            <div class ="text-green-iimp font-bold p-4">
                <Card class="mt-5 overflow-hidden">
                    <template #header>
                        <div class="w-full py-3 text-xl font-bold text-center bg-lightgreen-iimp border-lightgreen-iimp">Categorías</div>
                    </template>

                    <template #content>

                        <div class="flex items-center  w-full" v-for="(categoria) in categorias" >
                            <div v-if="categoria.control =='CV'" class="w-full">
                                <div v-if="es_socio && (categoria.condicion == 'SO')" class="flex items-center w-full" >
                                    <RadioButton  v-model="selected_categoria" v-bind="selected_categoriaAttrs"
                                                    name="selected_categoria" :value='categoria.id' class="ml-6 radio-green-iimp" @click ="changeCategory(categoria.id, categoria.precio_disponible.valor)" />
                                    <div class="flex justify-between w-full ml-6 mr-6" >
                                        <label  class="ml-2">{{  categoria.nombre_es }}</label>
                                        <p class="text-yellow-price">USD  {{  categoria.precio_disponible.valor }}</p>
                                    </div>
                                </div>
                                <div v-if="!es_socio && (categoria.condicion == 'NS')" class="flex items-center  w-full" >
                                    <RadioButton  v-model="selected_categoria" v-bind="selected_categoriaAttrs"
                                                    name="selected_categoria" :value='categoria.id' class="ml-6 radio-green-iimp" @click ="changeCategory(categoria.id, categoria.precio_disponible.valor)" />
                                    <div class="flex justify-between w-full ml-6 mr-6" >
                                        <label  class="ml-2">{{  categoria.nombre_es }}</label>
                                        <p class="text-yellow-price">USD  {{  categoria.precio_disponible.valor }}</p>
                                    </div>
                                </div>
                                <div v-if="(categoria.condicion != 'SO' && categoria.condicion != 'NS')" class="flex items-center w-full" >
                                    <RadioButton  v-model="selected_categoria" v-bind="selected_categoriaAttrs"
                                                    name="selected_categoria" :value='categoria.id' class="ml-6 radio-green-iimp" @click ="changeCategory(categoria.id, categoria.precio_disponible.valor)" />
                                    <div class="flex justify-between w-full ml-6 mr-6" >
                                        <label  class="ml-2">{{  categoria.nombre_es }}</label>
                                        <p class="text-yellow-price">USD  {{  categoria.precio_disponible.valor }}</p>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="w-full">
                                <div class="flex items-center w-full" >
                                    <RadioButton  v-model="selected_categoria" v-bind="selected_categoriaAttrs"
                                                    name="selected_categoria" :value='categoria.id' class="ml-6 radio-green-iimp" @click ="changeCategory(categoria.id, categoria.precio_disponible.valor)" />
                                    <div class="flex justify-between w-full ml-6 mr-6" >
                                        <label  class="ml-2">{{  categoria.nombre_es }}</label>
                                        <p class="text-yellow-price">USD  {{  categoria.precio_disponible.valor }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <Card v-if="show_days" class="m-6">
                            <template #content>
                                <div class =" flex justify-around">
                                    <div  v-for="(day, key) in days" :key="key">
                                        <Checkbox :inputId="day"
                                                :value="key"
                                                v-model="selectedDays" v-bind="selectedDaysAttrs" name="selectedDays" @click ="selectDays(key)" />
                                                <label :for="day" class =" pl-3">{{ day }}</label>
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
                                <div class="flex justify-around mb-5">
                                    <img v-if="src" :src="src" alt="Image" class="shadow-md rounded-l max-w-[350px] max-h-[350px]"/>
                                </div>
                                    <FileUpload ref="fileupload" mode="basic"  class="p-button-outlined text-green-iimp"
                                    :auto="true"
                                    customUpload
                                    :chooseLabel="'Elegir Documento'"
                                    :uploadLabel="'Subir Imagen'"
                                    @select="onFileSelect"
                                    accept="image/jpg, image/jpeg, image/png, application/pdf"
                                    name="uploadDocument" v-model="uploadDocument" v-bind="uploadDocumentAttrs"  />
                            </template>

                        </Card>

                    </template>
                </Card>
            </div>
            <div class ="text-green-iimp font-bold p-4">

                <Card class="mt-5 overflow-hidden">

                    <template #header>
                        <div class="w-full py-3 text-xl font-bold text-center bg-lightgreen-iimp border-lightgreen-iimp">Datos de Facturación</div>
                    </template>

                    <template #content>

                        <div class="grid gap-6 m-6 md:grid-cols-2" >

                                <div class="grid gap-6 md:grid-cols-2" >
                                    <div class="col-span-3 sm:col-span-1">
                                        <label for="tipoDocumentoEmpresa" class="">Tipo de Documento*</label>
                                        <Select name="tipoDocumentoEmpresa" v-model="tipoDocumentoEmpresa" v-bind="tipoDocumentoEmpresaAttrs" :options="tipoDocumento"
                                                optionLabel="name_es" optionValue="id" placeholder="Elegir" showClear
                                                checkmark class="w-full border-green-iimp" @change="setTipoDocPago" />
                                        <span class="font-normal text-red-600">{{ errors.tipoDocumentoEmpresa }}</span>

                                    </div>

                                    <div class="col-span-3 sm:col-span-1">
                                            <label for="documentoEmpresa" class="">Número de Documento *</label>
                                            <InputGroup>
                                                <InputText name="documentoEmpresa" v-model="documentoEmpresa" v-bind="documentoEmpresaAttrs"
                                                    class="border-green-iimp " @keypress="onlyNumberKey"  :maxlength="25" />
                                                <Button icon="pi pi-search"
                                                    class="border-green-iimp bg-green-iimp" @click="getEmpresaData"  :disabled="!documentoEmpresa"/>
                                            </InputGroup>
                                            <span class="font-normal text-red-600">{{ errors.documentoEmpresa }}</span>
                                    </div>
                                </div>
                                <div class="w-full sm:col-span-1">
                                        <label for="razonSocial" class="">Nombre o Razón Social *</label>
                                        <InputText name="razonSocial" v-model="razonSocial" v-bind="razonSocialAttrs" class="w-full border-green-iimp"
                                        :readonly="block_direction"  @click="check_fact" />
                                        <span class="font-normal text-red-600">{{ errors.razonSocial }}</span>
                                </div>

                        </div>

                        <div class="grid gap-6 m-6 md:grid-cols-2" >
                            <div class="w-full sm:col-span-1">
                                        <label for="direccionEmpresa" class="">Dirección Fiscal *</label>
                                        <InputText name="direccionEmpresa" v-model="direccionEmpresa" v-bind="direccionEmpresaAttrs" class="w-full border-green-iimp"
                                        autocomplete="nueva-direccion" :readonly="block_direction"  @click="check_fact" />
                                        <span class="font-normal text-red-600">{{ errors.direccionEmpresa }}</span>
                            </div>

                            <div class="grid gap-6 md:grid-cols-2">
                                <div class="w-full sm:col-span-1">
                                        <label for="responsable" class="">Responsable de Facturación *</label>
                                        <InputText name="responsable" v-model="responsable" v-bind="responsableAttrs" class="w-full border-green-iimp"
                                        />
                                        <span class="font-normal text-red-600">{{ errors.responsable }}</span>
                                </div>

                                <div class="w-full sm:col-span-1">
                                            <label for="correo_facturador" class="">Email Facturación *</label>
                                            <InputText name="correo_facturador" v-model="correo_facturador" v-bind="correo_facturadorAttrs" class="w-full border-green-iimp"
                                            autocomplete="nuevo-email"/>
                                            <span class="font-normal text-red-600">{{ errors.correo_facturador }}</span>
                                </div>

                            </div>
                        </div>

                        <div class="flex justify-around w-full mb-4">
                            <Card class="gap-3 text-center min-w-[450px]">
                                <template #content>
                                    <div class="text-lg font-semibold m-4">Documento de Pago</div>

                                    <div class="flex flex-wrap justify-center gap-3">
                                        <div class="flex items-center" v-for="tipodocpago in tipoDocumentoPago">
                                            <RadioButton v-model="selectTipoDocPago" v-bind="selectTipoDocPagoAttrs" :inputId="tipodocpago.nombre"
                                                name="tipodocpago" :value="tipodocpago.id"  @click="$event.preventDefault()"/>
                                            <label :for="tipodocpago.nombre" class="ml-2">{{ tipodocpago.nombre }}</label>
                                        </div>
                                    </div>
                                    <span class="font-normal text-red-600">{{ errors.selectTipoDocPago }}</span>
                                </template>
                            </Card>
                            <Card class="gap-3 text-center min-w-[450px]">
                                <template #content>
                                    <div class="text-lg font-semibold m-4">Método de pago</div>

                                    <div class="flex flex-wrap justify-center gap-3">
                                        <div class="flex items-center">
                                            <RadioButton v-model="selectTipoPago" v-bind="selectTipoPagoAttrs"
                                                name="tipodocfac" :value="3" class="radio-green-iimp " @click="$event.preventDefault()" />
                                            <label  class="ml-2">Tarjeta</label>
                                        </div>
                                    </div>
                                    <span class="font-normal text-red-600">{{ errors.selectTipoPago }}</span>
                                </template>
                            </Card>
                        </div>

                    </template>

                </Card>
            </div>
            <div class ="text-green-iimp font-bold p-4 flex justify-between">
                <div class="">
                    <Checkbox :binary="true" v-model="terminos" v-bind="terminosAttrs" name="terminos"/>
                    <label for="terminos" class="pl-2 cursor-pointer" @click="showModal">Términos y condiciones de participación *</label>

                </div>

                <div class="">
                    <Checkbox :binary="true" v-model="reglamento" v-bind="reglamentoAttrs" name="reglamento"/>
                    <a :href="reglamento_inscripciones" target="_blank" rel="noopener noreferrer" title="Ver reglamento" >
                        <label for="reglamento" class="pl-2 cursor-pointer">Reglamento de Participación *</label>
                    </a>
                </div>

            </div>
        </div>

        <Dialog v-model:visible="visible" modal :style="{ border: 'none' }" class="modal-green max-w-[750px] modal-custom-scroll m-[5px]"  :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
            <div class="modal-head-title font-bold">
                <p>TÉRMINOS Y CONDICIONES DE PARTICIPACIÓN</p>
                <p>PERUMIN 37 CONVENCIÓN MINERA</p>
            </div>
            <div class ="pt-[30px] pr-[30px] pl-[30px] pb-[20px] modal-custom-scroll">
                <ul class="mt-5 mb-5 ml-10 font'bold list-decimal">
                        <li class ="text-justify mb-4">Al comprar una entrada e ingresar al evento, el comprador declara haber leído,
                        comprendido y aceptado el Reglamento de Inscripciones publicado en la
                        página web oficial del evento, así como todos los presentes términos
                        establecidos.</li>
                        <li class ="text-justify mb-4">Se autoriza al IIMP a usar gratuitamente la imagen captada del asistente
                        durante el evento, sin límite de tiempo ni territorio.</li>
                        <li class ="text-justify mb-4">Los datos personales serán tratados conforme a la ley peruana para fines
                        administrativos, de seguridad, estadísticos y de comunicación.</li>
                        <li class ="text-justify mb-4">Para el ingreso y permanencia, se requiere entrada válida y documento de
                        identidad. No se permite el ingreso con objetos peligrosos, drogas, armas ni
                        alcohol externo.</li>
                        <li class ="text-justify mb-4">Prohibido el uso de drones sin autorización expresa del IIMP; se debe cumplir
                        con la normativa aérea vigente.</li>
                        <li class ="text-justify mb-4">El IIMP no se responsabiliza por pérdidas, robos o accidentes, salvo en casos
                        de negligencia o dolo.</li>
                        <li class ="text-justify mb-4">No se permite la reventa no autorizada de entradas; el IIMP no responde por
                        boletos comprados fuera de canales oficiales.</li>
                        <li class ="text-justify mb-4">El uso de marcas, logos o contenidos del evento sin permiso está prohibido.</li>
                        <li class ="text-justify mb-4">Cualquier conflicto se resolverá según las leyes peruanas, ante tribunales de
                        Lima.</li>

                        <!--<li class ="font-bold">Aceptación de los Términos</li>
                        <p class ="text-justify mb-4">Al comprar una entrada e ingresar al evento, el comprador declara haber leído, comprendido y aceptado el Reglamento de Inscripciones publicado en la página web oficial del evento, así como todos los presentes términos establecidos.</p>
                        <li class ="font-bold">Uso de Imagen</li>
                        <p class ="text-justify mb-4">El asistente autoriza de forma gratuita, expresa, indefinida y sin limitación territorial, al Instituto de Ingenieros de Minas del Perú (IIMP), a registrar y difundir su imagen personal captada durante el evento, mediante fotografías, videos o cualquier otro medio audiovisual, en publicaciones impresas, medios digitales, redes sociales, transmisiones en vivo u otros formatos de comunicación institucional.</p>
                        <li class ="font-bold">Tratamiento de Datos Personales</li>
                        <p class ="text-justify mb-4">Los datos personales proporcionados por el asistente al momento de adquirir la entrada serán tratados de conformidad con la Ley N° 29733 – Ley de Protección de Datos Personales, y su reglamento. Dichos datos serán utilizados exclusivamente para fines administrativos, de seguridad, estadísticos y de comunicación institucional.</p>
                        <li class ="font-bold">Reglas de Ingreso y Permanencia</li>
                        <p class ="text-justify mb-4">Se requiere portar una entrada válida y un documento de identidad oficial para ingresar al evento. El IIMP se reserva el derecho de admisión y permanencia por motivos de seguridad, conducta inapropiada, o incumplimiento de normas. No se permite el ingreso con armas, objetos peligrosos, sustancias ilegales ni bebidas alcohólicas del exterior.</p>
                        <li class ="font-bold">Uso de Drones</li>
                        <p class ="text-justify mb-4">Por razones de seguridad, privacidad y cumplimiento normativo, queda prohibido el uso de drones o vehículos aéreos no tripulados (VANT) dentro del recinto del evento sin autorización previa y expresa del IIMP. Cualquier operación autorizada deberá cumplir con la normativa vigente de la Dirección General de Aeronáutica Civil (DGAC) del Perú. El incumplimiento de esta disposición puede ser sancionado con el retiro inmediato del evento y la denuncia correspondiente.</p>
                        <li class ="font-bold">Reembolsos y Cancelación</li>
                        <p class ="text-justify mb-4">En caso de cancelación o modificación sustancial del evento por causas de fuerza
                            mayor, caso fortuito o razones operativas, el IIMP informará oportunamente a través de
                            sus canales oficiales. Las políticas de devolución o reprogramación se establecerán
                            según corresponda en cada caso.</p>
                        <li class ="font-bold">Limitación de Responsabilidad</li>
                        <p class ="text-justify mb-4">El IIMP no será responsable por pérdidas, robos, accidentes o daños personales ocurridos durante el evento, salvo aquellos que deriven de su actuación dolosa o negligente.</p>
                        <li class ="font-bold">Prohibición de Reventa</li>
                        <p class ="text-justify mb-4">Está prohibida la reventa no autorizada de entradas. El IIMP no se responsabiliza por entradas adquiridas fuera de los canales oficiales de venta.</p>
                        <li class ="font-bold">Propiedad Intelectual</li>
                        <p class ="text-justify mb-4">Las marcas, logotipos, contenidos y diseños vinculados al evento son propiedad del IIMP o de sus aliados estratégicos. Queda prohibido su uso sin autorización previa y por escrito.</p>
                        <li class ="font-bold">Jurisdicción y Ley Aplicable</li>
                        <p class ="text-justify">Estos Términos se rigen por las leyes de la República del Perú. Cualquier controversia será resuelta por los tribunales competentes de la ciudad de Lima.</p>-->

                </ul>
            </div>
            <div class="flex justify-around">
                <div class="flex max-w-[450px] justify-evenly w-[100%]">
                    <button class="border border-white modal-continue-button p-[12px] rounded-full font-bold min-w-[130px]" @click="acceptModal" >
                        Aceptar
                    </button>
                </div>
            </div>
        </Dialog>

</template>

