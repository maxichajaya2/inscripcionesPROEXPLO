<script setup>
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import { usePage, router } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import { useForm, useField } from 'vee-validate';
import * as yup from 'yup';
import Functions from '@/Functions';
import { useToast } from 'primevue/usetoast';

import "../../../css/inscripciones.css";

const visible = ref(true);
const toast = useToast();

const page = usePage();
const props = defineProps({});
const tipoDocumento = computed(() => usePage().props.general.tipDocPer);

const schema = yup.object({
  tipo_doc: yup.mixed().required('Tipo documento es requerido'),
  documento: yup.string().when(
            'tipo_doc',
            (tipo_doc) => {
                if (typeof tipo_doc[0] != 'undefined') {
                    if (tipo_doc[0] == 1) {
                        return yup.string().matches(/^[0-9]+$/, "El valor debe ser numérico").test('len', 'Debe tener exactamente 8 dígitos', val => val && val.toString().length === 8)
                    } else {
                        return yup.string().required('Documento es requerido')
                    }
                }
            },
        ),
});

const { defineField, errors, handleSubmit, setValues, resetForm ,values } = useForm({
        validationSchema: schema,
 });

const [tipo_doc, tipo_docAttrs] = defineField('tipo_doc');
const [documento, documentoAttrs] = defineField('documento');

const onlyNumberKey = (event) => {
    if( tipo_doc.value == 1 ){
        const charCode = event.charCode ? event.charCode : event.keyCode
        if (charCode < 48 || charCode > 57) {
            event.preventDefault()
        }else{
            if(typeof documento.value != 'undefined' ){
                if(documento.value.length == 8){
                    event.preventDefault()
                }
            }

        }
    }
}

const getValidacionDoc =  () => {

    if(typeof documento.value == 'undefined' ){
        toast.add({ severity: 'error', summary: 'Ingresa un número de documento', life: 2000 });
         return { "validate" : false };
    }

    if (tipo_doc.value == 1) {
        if ( documento.value.length != 8) {
            toast.add({ severity: 'error', summary: 'El DNI debe tener 8 dígitos', life: 2000 });
            return { "validate" : false };
        }
    }

    if(Object.keys(errors._value).length == 0 && tipo_doc.value > 0){
        return { "validate" : true ,
            "formValidacionDoc": values
        };

    }else{
         return { "validate" : false };
    }


}

const clearDocument = () => {
    documento.value = "";
}

onMounted(() => {
    tipo_doc.value = 1;
})

defineExpose({
  getValidacionDoc
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

                    <label for="tipo_doc" class="">Tipo de documento*</label>
                    <Select name="tipo_doc" v-model="tipo_doc" v-bind="tipo_docAttrs" :options="tipoDocumento" @change="clearDocument"
                    optionLabel="name_es" optionValue="id" placeholder="Seleccione Documento" showClear checkmark
                    class="w-full border-green-iimp" />
                    <span class="font-normal text-red-600">{{ errors.tipo_doc }}</span>

                </div>
                <div class="col-span-3 sm:col-span-1 p-4">
                        <label for="documento"  class="">Número de documento*</label>
                        <InputText name="documento" v-model="documento" v-bind="documentoAttrs" class="w-full border-green-iimp"
                         @keypress="onlyNumberKey" :maxlength="25" />
                        <span class="font-normal text-red-600">{{ errors.documento }}</span>
                </div>
            </div>
        </div>
    </form>
</template>

