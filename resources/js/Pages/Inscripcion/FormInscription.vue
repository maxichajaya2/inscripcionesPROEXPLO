<script setup>
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Divider from 'primevue/divider';
import Button from 'primevue/button';
import Calendar from 'primevue/calendar';
import Checkbox from 'primevue/checkbox';
import RadioButton from 'primevue/radiobutton';
import Card from 'primevue/card';
import InputGroup from 'primevue/inputgroup';
import { ref, onMounted, computed, onBeforeMount } from 'vue';
import { useForm } from 'vee-validate';
import * as yup from 'yup';
import { usePage, router } from '@inertiajs/vue3';


import "../../../css/inscripciones.css";

const visible = ref(true);

const { defineField, errors, handleSubmit, setValues, resetForm ,values  } = useForm({
    validationSchema: yup.object({
        //friso: yup.string().required('Friso es requerido'),
    })
})

const today = new Date();
const [documento, documentoAttrs] = defineField('documento');
const [id_tipo_documento, id_tipo_documentoAttrs] = defineField('id_tipo_documento');
const [selectTipo, selectTipoAttrs] = defineField('selectTipo');


/* wip locale
const customLocale = {
  firstDayOfWeek: 1,
  dayNames: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
  dayNamesShort: ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
  dayNamesMin: ['D', 'L', 'M', 'M', 'J', 'V', 'S'],
  monthNames: [
    'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
    'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
  ],
  monthNamesShort: [
    'Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin',
    'Juil', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'
  ],
  today: 'Aujourd’hui',
  clear: 'Effacer'
}*/

const hideModal = () => {
    visible.value = !visible;
};

const onlyNumberKey = (event) => {
  const charCode = event.charCode ? event.charCode : event.keyCode
  if (charCode < 48 || charCode > 57) {
    event.preventDefault()
  }
}

const goStart = () => {
    router.get(route('inscripcion.index'));
};

function getDocument() {
    return { "validate" : true
    };
}

defineExpose({
  getDocument
});

</script>

<template>
    <form id="FormValidacionDoc">
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
                                    <label class="">Nombres*</label>
                                    <InputText for="documento" name="documento" v-model="documento" v-bind="documentoAttrs" class="w-full border-green-iimp"
                                    />
                                    <span class="font-normal text-red-600">{{ errors.documento }}</span>
                            </div>
                            <div class="grid gap-6 md:grid-cols-2" >
                                <div class="col-span-3 sm:col-span-1">
                                    <label for="id_tipo_documento" class="">Apellido Paterno*</label>
                                    <InputText for="documento" name="documento" v-model="documento" v-bind="documentoAttrs" class="w-full border-green-iimp"
                                    />
                                    <span class="font-normal text-red-600">{{ errors.id_tipo_documento }}</span>

                                </div>
                                <div class="col-span-3 sm:col-span-1">
                                        <label class="">Apellido Materno</label>
                                        <InputText for="documento" name="documento" v-model="documento" v-bind="documentoAttrs" class="w-full border-green-iimp"
                                        @keypress="onlyNumberKey" />
                                        <span class="font-normal text-red-600">{{ errors.documento }}</span>
                                </div>
                            </div>

                        </div>

                        <div class="grid gap-6 m-6 md:grid-cols-4" >

                                <div class="col-span-3 sm:col-span-1">
                                    <label for="id_tipo_documento" class="">País*</label>
                                    <Dropdown name="tipo_doc" v-model="id_tipo_documento" v-bind="id_tipo_documentoAttrs"
                                            optionLabel="name_es" optionValue="id" placeholder="Seleccione Documento" showClear
                                            checkmark class="w-full border-green-iimp" />
                                    <span class="font-normal text-red-600">{{ errors.id_tipo_documento }}</span>

                                </div>
                                <div class="col-span-3 sm:col-span-1">
                                        <label class="">Departamento</label>
                                        <Dropdown name="tipo_doc" v-model="id_tipo_documento" v-bind="id_tipo_documentoAttrs"
                                            optionLabel="name_es" optionValue="id" placeholder="Seleccione Documento" showClear
                                            checkmark class="w-full border-green-iimp" />
                                        <span class="font-normal text-red-600">{{ errors.documento }}</span>
                                </div>

                                <div class="w-full sm:col-span-1">
                                        <label class="">Provincia</label>
                                        <Dropdown name="tipo_doc" v-model="id_tipo_documento" v-bind="id_tipo_documentoAttrs"
                                            optionLabel="name_es" optionValue="id" placeholder="Seleccione Documento" showClear
                                            checkmark class="w-full border-green-iimp" />
                                        <span class="font-normal text-red-600">{{ errors.documento }}</span>
                                </div>
                                <div class="w-full sm:col-span-1">
                                        <label class="">Distrito</label>
                                        <Dropdown name="tipo_doc" v-model="id_tipo_documento" v-bind="id_tipo_documentoAttrs"
                                            optionLabel="name_es" optionValue="id" placeholder="Seleccione Documento" showClear
                                            checkmark class="w-full border-green-iimp" />
                                        <span class="font-normal text-red-600">{{ errors.documento }}</span>
                                </div>
                        </div>

                        <div class="grid gap-6 m-6 md:grid-cols-2" >
                            <div class="grid gap-6 md:grid-cols-2" >
                                <div class="col-span-3 sm:col-span-1">
                                        <label class="">Correo Electrónico*</label>
                                        <InputText for="documento" name="documento" v-model="documento" v-bind="documentoAttrs" class="w-full border-green-iimp"
                                        @keypress="onlyNumberKey" />
                                        <span class="font-normal text-red-600">{{ errors.documento }}</span>
                                </div>
                                <div class="col-span-3 sm:col-span-1">
                                        <label for="id_tipo_documento" class="">Celular*</label>
                                        <InputText for="documento" name="documento" v-model="documento" v-bind="documentoAttrs" class="w-full border-green-iimp"
                                        />
                                        <span class="font-normal text-red-600">{{ errors.id_tipo_documento }}</span>

                                </div>

                            </div>
                            <div class="w-full sm:col-span-1">
                                    <label class="">Dirección*</label>
                                    <InputText for="documento" name="documento" v-model="documento" v-bind="documentoAttrs" class="w-full border-green-iimp"
                                    />
                                    <span class="font-normal text-red-600">{{ errors.documento }}</span>
                            </div>

                        </div>

                        <div class="grid gap-6 m-6 md:grid-cols-3" >
                                <div class="w-full sm:col-span-1">
                                    <label class="">Fecha de Nacimiento*</label>
                                    <Calendar name="documento" v-model="documento" modelValue=undefined v-bind="documentoAttrs" :maxDate="today" showIcon
                                    iconDisplay="input" class="w-full leading-3 border-green-iimp" dateFormat="yy-mm-dd" />
                                    <span class="font-normal text-red-600">{{ errors.documento }}</span>
                                </div>

                                <div class="w-full sm:col-span-1">
                                        <label class="">Empresa*</label>
                                        <InputText for="documento" name="documento" v-model="documento" v-bind="documentoAttrs" class="w-full border-green-iimp"
                                        @keypress="onlyNumberKey" />
                                        <span class="font-normal text-red-600">{{ errors.documento }}</span>
                                </div>
                                <div class="w-full sm:col-span-1">
                                        <label class="">Cargo*</label>
                                        <InputText for="documento" name="documento" v-model="documento" v-bind="documentoAttrs" class="w-full border-green-iimp"
                                        @keypress="onlyNumberKey" />
                                        <span class="font-normal text-red-600">{{ errors.documento }}</span>
                                </div>
                        </div>

                        <div class="grid gap-6 m-6 md:grid-cols-2" >
                            <div class="grid gap-6 md:grid-cols-2" >
                                <div class="col-span-3 sm:col-span-1">
                                        <label for="id_tipo_documento" class="">Sexo*</label>
                                            <Dropdown name="tipo_doc" v-model="id_tipo_documento" v-bind="id_tipo_documentoAttrs"
                                                optionLabel="name_es" optionValue="id" placeholder="Seleccione Documento" showClear
                                                checkmark class="w-full border-green-iimp" />
                                        <span class="font-normal text-red-600">{{ errors.id_tipo_documento }}</span>

                                </div>
                                <div class="col-span-3 sm:col-span-1">
                                        <label class="">Nacionalidad*</label>
                                        <Dropdown name="tipo_doc" v-model="id_tipo_documento" v-bind="id_tipo_documentoAttrs"
                                            optionLabel="name_es" optionValue="id" placeholder="Seleccione Documento" showClear
                                            checkmark class="w-full border-green-iimp" />
                                        <span class="font-normal text-red-600">{{ errors.documento }}</span>
                                </div>
                            </div>
                            <div class="w-full sm:col-span-1">
                                        <label class="">Nombre Credencial*</label>
                                        <InputText for="documento" name="documento" v-model="documento" v-bind="documentoAttrs" class="w-full border-green-iimp"
                                        @keypress="onlyNumberKey" />
                                        <span class="font-normal text-red-600">{{ errors.documento }}</span>
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
                                    <Checkbox :binary="true" />
                                    <label for="id_tipo_documento" class="pl-2">Autorización para compartir Datos Personales</label>

                            </div>

                        </div>
                    </div>

                </div>
            </div>
            <div class ="text-green-iimp font-bold p-4">

                <Card class="mt-5 overflow-hidden">

                    <template #header>
                        <div class="w-full py-3 text-xl font-bold text-center bg-lightgreen-iimp border-lightgreen-iimp">Datos de Facturación</div>
                    </template>

                    <template #content>

                        <div class="flex justify-around w-full mb-4">
                            <Card class="gap-3 text-center min-w-[450px]">
                                <template #content>
                                    <div class="text-lg font-semibold m-4">Seleccione el Documento de Pago</div>

                                    <div class="flex flex-wrap justify-center gap-3">
                                        <div class="flex items-center">
                                            <RadioButton v-model="selectTipo" v-bind="selectTipoAttrs"
                                                name="tipodocfac" :value="1" class="radio-green-iimp " />
                                            <label  class="ml-2">Boleta</label>
                                        </div>
                                        <div class="flex items-center">
                                            <RadioButton v-model="selectTipo" v-bind="selectTipoAttrs"
                                                name="tipodocfac" :value="2" />
                                            <label  class="ml-2">Factura</label>
                                        </div>

                                    </div>
                                    <span class="font-normal text-red-600">{{ errors.selectTipo }}</span>
                                </template>
                            </Card>
                            <Card class="gap-3 text-center min-w-[450px]">
                                <template #content>
                                    <div class="text-lg font-semibold m-4">Seleccione Metodo de pago</div>

                                    <div class="flex flex-wrap justify-center gap-3">
                                        <div class="flex items-center">
                                            <RadioButton v-model="selectTipo" v-bind="selectTipoAttrs"
                                                name="tipodocfac" :value="1" class="radio-green-iimp " />
                                            <label  class="ml-2">Tarjeta</label>
                                        </div>
                                    </div>
                                    <span class="font-normal text-red-600">{{ errors.selectTipo }}</span>
                                </template>
                            </Card>
                        </div>

                        <div class="grid gap-6 m-6 md:grid-cols-2" >

                                <div class="grid gap-6 md:grid-cols-2" >
                                    <div class="col-span-3 sm:col-span-1">
                                        <label for="id_tipo_documento" class="">Tipo de Documento*</label>
                                        <Dropdown name="tipo_doc" v-model="id_tipo_documento" v-bind="id_tipo_documentoAttrs"
                                                optionLabel="name_es" optionValue="id" placeholder="Seleccione Documento" showClear
                                                checkmark class="w-full border-green-iimp" />
                                        <span class="font-normal text-red-600">{{ errors.id_tipo_documento }}</span>

                                    </div>

                                    <div class="col-span-3 sm:col-span-1">
                                            <label class="">Número de Documento</label>
                                            <InputGroup>
                                                <InputText name="documento" v-model="documento" v-bind="documentoAttrs"
                                                    class="border-green-iimp " />
                                                <Button icon="pi pi-search"
                                                    class="border-green-iimp bg-green-iimp" @click="" />
                                            </InputGroup>
                                            <span class="font-normal text-red-600">{{ errors.documento }}</span>
                                    </div>
                                </div>
                                <div class="w-full sm:col-span-1">
                                        <label class="">Nombre o Razón Social*</label>
                                        <InputText for="documento" name="documento" v-model="documento" v-bind="documentoAttrs" class="w-full border-green-iimp"
                                        />
                                        <span class="font-normal text-red-600">{{ errors.documento }}</span>
                                </div>

                        </div>

                        <div class="grid gap-6 m-6 md:grid-cols-2" >
                            <div class="w-full sm:col-span-1">
                                        <label class="">Dirección Fiscal*</label>
                                        <InputText for="documento" name="documento" v-model="documento" v-bind="documentoAttrs" class="w-full border-green-iimp"
                                        />
                                        <span class="font-normal text-red-600">{{ errors.documento }}</span>
                            </div>

                            <div class="w-full sm:col-span-1">
                                        <label class="">Responsable de Facturación*</label>
                                        <InputText for="documento" name="documento" v-model="documento" v-bind="documentoAttrs" class="w-full border-green-iimp"
                                        />
                                        <span class="font-normal text-red-600">{{ errors.documento }}</span>
                            </div>

                        </div>

                    </template>

                </Card>
            </div>
        </div>
    </form>
</template>

