<script setup>
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Button from 'primevue/button';
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

const [documento, documentoAttrs] = defineField('documento');
const [id_tipo_documento, id_tipo_documentoAttrs] = defineField('id_tipo_documento');

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
        <div class="flex gap-6 p-6 w-full justify-around">
            <div class ="text-green-iimp font-bold  max-w-[450px] p-4">
                <div class ="text-green-iimp font-bold text-center p-4">
                    Estamos a punto de validar tu información. Por favor, completa tus datos
                </div>
                <div class="col-span-3 sm:col-span-1 p-4">

                    <label for="id_tipo_documento" class="">Tipo de documento*</label>
                        <Dropdown name="tipo_doc" v-model="id_tipo_documento" v-bind="id_tipo_documentoAttrs"
                             optionLabel="name_es" optionValue="id" placeholder="Seleccione Documento" showClear
                            checkmark class="w-full border-green-iimp" />
                    <span class="font-normal text-red-600">{{ errors.id_tipo_documento }}</span>

                </div>
                <div class="col-span-3 sm:col-span-1 p-4">
                        <label class="">Número de documento*</label>
                        <InputText for="documento" name="documento" v-model="documento" v-bind="documentoAttrs" class="w-full border-green-iimp"
                         @keypress="onlyNumberKey" />
                        <span class="font-normal text-red-600">{{ errors.documento }}</span>
                </div>
            </div>
        </div>
    </form>
</template>

