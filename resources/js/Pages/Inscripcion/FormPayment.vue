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
                    Estamos a punto de validar su pago. Por favor complete su pago
                </div>

            </div>
        </div>
    </form>
</template>

