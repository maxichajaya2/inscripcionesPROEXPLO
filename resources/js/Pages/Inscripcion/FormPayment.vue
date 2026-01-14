<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useForm } from 'vee-validate';
import * as yup from 'yup';
import { usePage, router } from '@inertiajs/vue3';
import Card from 'primevue/card';


import "../../../css/inscripciones.css";

const props = defineProps({
    categoria_seleccionada: Object,
    data_persona: Object,
    formulario: String
});

const { defineField, errors, handleSubmit, setValues, resetForm, values } = useForm({
    validationSchema: yup.object({

    })
})

watch(() => props.formulario, (newVal) => {
    if (!newVal || !newVal.form || !newVal.script) return;

    const form_holder = document.getElementById('form_holder');
    if (form_holder) {
        form_holder.innerHTML = ""; // Limpiar contenido previo

        const form = document.createElement("form");
        form.action = newVal.form.action;
        form.method = "post";

        const script = document.createElement("script");
        script.src = newVal.script.src;
        script.dataset.sessiontoken = newVal.script.sessiontoken;
        script.dataset.channel = newVal.script.channel;
        script.dataset.merchantid = newVal.script.merchantid;
        script.dataset.merchantlogo = newVal.script.merchantlogo;
        script.dataset.formbuttoncolor = newVal.script.formbuttoncolor;
        script.dataset.amount = newVal.script.amount;
        script.dataset.purchasenumber = newVal.script.purchasenumber;
        script.dataset.cardholdername = newVal.script.cardholdername;
        script.dataset.cardholderlastname = newVal.script.cardholderlastname;
        script.dataset.cardholderemail = newVal.script.cardholderemail;
        script.dataset.expirationminutes = newVal.script.expirationminutes;
        script.dataset.timeouturl = newVal.script.timeouturl;

        form.appendChild(script);
        form_holder.appendChild(form);
    }
}, { immediate: true, deep: true });
</script>

<template>
    <form id="FormPaymentFinish">
        <div class="flex gap-6 p-6 w-full justify-around">
            <div class="text-green-iimp font-bold  max-w-[600px] p-4">
                <div class="text-blue-900 font-bold text-center text-2xl mb-6 tracking-wide">
                    Payment Process
                </div>
                <Card class="w-full max-w-md shadow-2xl border-t-4 border-blue-600 rounded-xl overflow-hidden">
                    <template #content>
                        <div v-if="(formulario != undefined) && (formulario != null)">
                            <div class="mb-8 border-b pb-6 bg-white p-4">
                                <div class="flex justify-between items-center py-3 border-b border-gray-100">
                                    <span
                                        class="font-bold text-gray-500 uppercase text-xs tracking-wider">Participant</span>
                                    <span class="text-gray-800 font-semibold text-right">
                                        {{ data_persona.persona.nombres }} {{
                                            data_persona.persona.apellido_paterno }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center py-3 border-b border-gray-100">
                                    <span
                                        class="font-bold text-gray-500 uppercase text-xs tracking-wider">Category</span>
                                    <span class="text-blue-600 font-bold text-right">
                                        {{ categoria_seleccionada.nombre_en }}
                                    </span>
                                </div>
                                <div
                                    class="flex justify-between items-center py-4 mt-2 bg-blue-50/50 px-3 rounded-lg border border-blue-100">
                                    <span class="font-bold text-blue-800">Total Amount</span>
                                    <span class="font-bold text-blue-900 text-2xl">
                                        USD {{ formulario.script.amount || '0.00' }}
                                    </span>
                                </div>
                            </div>

                        </div>
                        <div id="form_holder" class="flex justify-around p-6"></div>
                    </template>
                </Card>
            </div>
        </div>
    </form>
</template>
