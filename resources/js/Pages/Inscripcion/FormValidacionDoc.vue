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
// const props = defineProps({});
const tipoDocumento = computed(() => usePage().props.general.tipDocPer);

const props = defineProps({
    saved_values: Object // <--- Recibir
});

const schema = yup.object({
    tipo_doc: yup.mixed().required('Document type is required'),
    documento: yup.string().when(
        'tipo_doc',
        (tipo_doc) => {
            if (typeof tipo_doc[0] != 'undefined') {
                if (tipo_doc[0] == 1) {
                    // Validación para DNI (Solo números y 8 dígitos)
                    return yup.string()
                        .matches(/^[0-9]+$/, "The value must be numeric")
                        .test('len', 'Must be exactly 8 digits', val => val && val.toString().length === 8)
                } else {
                    // Validación para Pasaporte/Otros (Alfanumérico)
                    return yup.string()
                        .matches(/^[a-zA-Z0-9]+$/, "The value must be numbers or letters")
                        .required('Document number is required')
                }
            }
        },
    ),
});

const { defineField, errors, handleSubmit, setValues, resetForm, values } = useForm({
    validationSchema: schema,
});

const [tipo_doc, tipo_docAttrs] = defineField('tipo_doc');
const [documento, documentoAttrs] = defineField('documento');

const onlyNumberKey = (event) => {
    if (tipo_doc.value == 1) {
        const charCode = event.charCode ? event.charCode : event.keyCode
        if (charCode < 48 || charCode > 57) {
            event.preventDefault()
        } else {
            if (typeof documento.value != 'undefined') {
                if (documento.value.length == 8) {
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

const getValidacionDoc = () => {

    if (typeof documento.value == 'undefined') {
        toast.add({ severity: 'error', summary: 'Document number is required', life: 2000 });
        return { "validate": false };
    }

    if (tipo_doc.value == 1) {
        if (documento.value.length != 8) {
            toast.add({ severity: 'error', summary: 'The DNI must have 8 digits', life: 2000 });
            return { "validate": false };
        }
    }

    if (Object.keys(errors._value).length == 0 && tipo_doc.value > 0) {
        return {
            "validate": true,
            "formValidacionDoc": values
        };

    } else {
        return { "validate": false };
    }

    return {
        validate: true,
        formValidacionDoc: values
    }

}

const clearDocument = () => {
    documento.value = "";
}

onMounted(() => {
    tipo_doc.value = 5;
})

defineExpose({
    getValidacionDoc
});

</script>

<template>
    <form id="FormValidacionDoc">
        <div class="flex gap-6 p-6 w-full justify-around ">
            <div class="text-green-iimp font-bold  max-w-[450px] p-4">
                <div class="text-green-iimp font-bold text-center p-4">
                    We are about to validate your information. Please fill in your details.
                </div>
                <div class="col-span-3 sm:col-span-1 p-4">

                    <label for="tipo_doc" class="">Document Type*</label>
                    <Select name="tipo_doc" v-model="tipo_doc" v-bind="tipo_docAttrs" :options="tipoDocumento"
                        @change="clearDocument" optionLabel="name_en" optionValue="id" placeholder="Select Document"
                        showClear checkmark class="w-full border-green-iimp" />
                    <span class="font-normal text-red-600">{{ errors.tipo_doc }}</span>

                </div>
                <div class="col-span-3 sm:col-span-1 p-4">
                    <label for="documento" class="">Document Number*</label>
                    <InputText name="documento" v-model="documento" v-bind="documentoAttrs"
                        class="w-full border-green-iimp" @keypress="onlyNumberKey" :maxlength="25" />
                    <span class="font-normal text-red-600">{{ errors.documento }}</span>
                </div>
            </div>
        </div>
    </form>
</template>
