<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useForm } from 'vee-validate';
import * as yup from 'yup';
import { usePage, router } from '@inertiajs/vue3';
import Card from 'primevue/card';


import "../../../css/inscripciones.css";

const props = defineProps({
    data_persona: Object,
    formulario: String
});

const { defineField, errors, handleSubmit, setValues, resetForm ,values  } = useForm({
    validationSchema: yup.object({

    })
})

watch(() => props.formulario, (newVal, oldVal) => {

    const form = document.createElement("form");

    form.action = newVal.form.action;
    form.method = "post";

    const script = document.createElement("script");

	script.src = newVal.script.src;

	script.dataset.sessiontoken = newVal.script.sessiontoken;
	script.dataset.channel = newVal.script.channel;
	script.dataset.merchantid = newVal.script.merchantid;
	script.dataset.merchantlogo  = newVal.script.merchantlogo;
	script.dataset.formbuttoncolor = newVal.script.formbuttoncolor;
	script.dataset.amount = newVal.script.amount;
	script.dataset.purchasenumber = newVal.script.purchasenumber;
	script.dataset.cardholdername = newVal.script.cardholdername;
    script.dataset.cardholderlastname = newVal.script.cardholderlastname;
    script.dataset.cardholderemail = newVal.script.cardholderemail;

	script.dataset.expirationminutes = newVal.script.expirationminutes;
	script.dataset.timeouturl = newVal.script.timeouturl;

	form.appendChild(script);

    document.getElementById('form_holder').appendChild(form);
});

const [documento, documentoAttrs] = defineField('documento');
const [id_tipo_documento, id_tipo_documentoAttrs] = defineField('id_tipo_documento');

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
    <form id="FormPaymentFinish">
        <div class="flex gap-6 p-6 w-full justify-around">
            <div class ="text-green-iimp font-bold  max-w-[450px] p-4">
                <div class ="text-green-iimp font-bold text-center p-4">
                    Estamos a punto de validar su pago. Por favor complete su pago
                </div>
                <Card>
                    <template #content>
                        <div v-if="(formulario != undefined) && (formulario != null)">
                            <div>
                                {{ formulario.script.amount }}
                            </div>

                        </div>
                        <div id="form_holder" class="flex justify-around">

                        </div>
                    </template>
                </Card>
            </div>
        </div>
    </form>
</template>

