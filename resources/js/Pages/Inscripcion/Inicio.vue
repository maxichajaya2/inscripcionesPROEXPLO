<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import colorbar from '@/Components/colorbar.vue';
import { router, usePage } from '@inertiajs/vue3';
import { ref, onMounted, watch } from 'vue';
import Dialog from 'primevue/dialog';
import FormValidacionDoc from './FormValidacionDoc.vue';
import FormInscription from './FormInscription.vue';
import FormPayment from './FormPayment.vue';
import Button from 'primevue/button';
import { useToast } from 'primevue/usetoast';

import Stepper from 'primevue/stepper';
import StepList from 'primevue/steplist';
import StepPanels from 'primevue/steppanels';
import StepPanel from 'primevue/steppanel';
import Step from 'primevue/step';

import "../../../css/inscripciones.css";

const visible = ref(true);
const loading = ref(false);
const toast = useToast();
const props = defineProps({
    title: String,
    categorias: Object,
    modal_texts: Object,
})

const formDataValidacionDoc = ref(null);
const formDataInscription = ref(null);
const formDataPayment = ref(null);
const data_persona = ref({});
const categoria_seleccionada = ref({});

const childFormValidacionDoc = ref();
const childFormInscription = ref(null);
const childFormPayment = ref(null);


const validate = async (value) => {
    switch (value) {
        case "Documento":
            loading.value = true;
            formDataValidacionDoc.value = childFormValidacionDoc.value.getValidacionDoc();
            if (formDataValidacionDoc.value.validate) {

                const response = await axios.post('/api/getperson',
                    { id_tipo_documento: formDataValidacionDoc.value.formValidacionDoc.tipo_doc, numero_documento: formDataValidacionDoc.value.formValidacionDoc.documento });

                data_persona.value = response.data;
                loading.value = false;
                if (response.data.status == false) {
                    toast.add({ severity: 'error', summary: 'Document number not found', life: 2000 });
                    return false;
                }

                return true;
            } else {
                loading.value = false;
                return false;
            }
            break;

        case "Inscripcion":
            loading.value = true;
            formDataInscription.value = childFormInscription.value.getInscripcion();

            if (formDataInscription.value.validate) {
                props.categorias.forEach(categoria => {
                    if (categoria.id == formDataInscription.value.formInscription.selected_categoria) {
                        categoria_seleccionada.value = categoria;
                    }
                });

                const form_payment = await axios.post('/pago/getform',
                    { form: formDataInscription.value.formInscription }, { headers: { 'Content-Type': 'multipart/form-data' } });

                formDataPayment.value = form_payment.data.formulario;
                loading.value = false;
                return true;

            } else {
                loading.value = false;
                return false;
            }
            break;
    }

    return false;
}


const hideModal = () => {
    visible.value = !visible.value;
};

const goStart = () => {
    router.get(route('inscripcion.index'));
};


</script>

<template>
    <AppLayout title="inscripciones" class="bg-gradient-wmc">
        <div class=" px-3 mx-auto max-w-7xl md:px-6 lg:px-8 font-(family-name:Roboto)">
            <div id="titulo_inicial" class="mt-8 mb-8">
                <h1 class="text-3xl text-green-iimp font-bold mb-2 text-yellow-price">{{ props.title }}</h1>
                <colorbar class="block w-auto" />
            </div>
            <div class="flex justify-around mt-6 mb-6">
                <Stepper value="1" class="w-full">
                    <StepList class="text-black-price bg-degradient">
                        <Step value="1" :disabled="true" class="text-black-price">Data Validation</Step>
                        <Step value="2" :disabled="true">Personal Details</Step>
                        <Step value="3" :disabled="true">Payment Process</Step>
                    </StepList>
                    <StepPanels>
                        <!-- ===== 1. Validacion de Documento =====-->
                        <StepPanel v-slot="{ activateCallback }" value="1"
                            class="rounded-2xl border-2 border-green-iimp bg-white-price shadow-wmc">
                            <FormValidacionDoc ref="childFormValidacionDoc" />
                            <div class="flex p-6 justify-end">
                                <Button label="Validate" icon="pi pi-arrow-right" iconPos="right"
                                    @click="async () => await validate('Documento') ? activateCallback('2') : false"
                                    class="bg-degradient border-rounded-full" :loading="loading" />
                            </div>
                        </StepPanel>
                        <!-- ===== 2. Formulario de Inscripcion =====-->
                        <StepPanel v-slot="{ activateCallback }" value="2">
                            <FormInscription ref="childFormInscription" :data_persona="data_persona"
                                :categorias="props.categorias" />
                            <div class="flex justify-between p-6">
                                <Button label="Back" severity="secondary" icon="pi pi-arrow-left"
                                    @click="activateCallback('1')" class="border-rounded-full" />
                                <Button label="Register" icon="pi pi-arrow-right" iconPos="right"
                                    @click="async () => await validate('Inscripcion') ? activateCallback('3') : false"
                                    class="bg-green-iimp border-rounded-full" :loading="loading" />
                            </div>
                        </StepPanel>
                        <!-- ===== 3. Formulario de Pago =====-->
                        <StepPanel v-slot="{ activateCallback }" value="3">
                            <FormPayment ref="childFormPayment" :data_persona="data_persona"
                                :formulario="formDataPayment" :categoria_seleccionada="categoria_seleccionada" />
                            <div class="flex justify-between p-6">
                                <Button label="Back" severity="secondary" icon="pi pi-arrow-left"
                                    @click="activateCallback('2')" class="border-rounded-full" />
                                <!--<Button label="Finalizar" icon="pi pi-check" iconPos="right" @click="" class="bg-green-iimp border-rounded-full"/>-->
                            </div>
                        </StepPanel>
                    </StepPanels>
                </Stepper>
            </div>

        </div>
    </AppLayout>

    <!-- ===== MODAL DE TARIFAS Y BENEFICIOS ===== -->
    <!-- ========================================= -->
    <Dialog v-model:visible="visible" modal :showHeader="false"
        class="bg-white text-slate-800 max-w-[700px] rounded-2xl overflow-hidden shadow-2xl"
        :breakpoints="{ '1199px': '75vw', '575px': '95vw' }">

        <div class="bg-slate-50 border-b border-gray-100 p-6 text-center">
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight ">
                {{ title }}
            </h2>
            <p class="text-sm text-slate-500 mt-1 uppercase tracking-widest font-medium italic">Available Rates</p>
        </div>

        <div class="p-6 md:p-8">
            <div class="overflow-hidden border border-gray-200 rounded-xl mb-8 shadow-sm">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-slate-800 text-white">
                            <th class="text-left py-4 px-6 text-sm font-semibold uppercase tracking-wider">Registration
                                Category</th>
                            <th
                                class="text-center py-4 px-6 text-sm font-semibold uppercase tracking-wider bg-amber-500">
                                Regular Rate</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="categoria in categorias" :key="categoria.id"
                            class="hover:bg-slate-50 transition-colors">
                            <td class="py-4 px-6 text-sm font-bold text-slate-700">
                                {{ categoria.nombre_en }}
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span class="text-lg font-black text-blue-900">
                                    USD {{ categoria.precio_disponible?.valor ?? '0.00' }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="bg-gray-50 py-2 px-6 text-right border-t border-gray-100">
                    <span class="text-xs text-gray-400 italic font-medium">Prices include VAT (18% IGV)</span>
                </div>
            </div>

            <div class="space-y-6">
                <div class="flex items-center gap-3 border-b border-gray-100 pb-2">
                    <div class="h-5 w-1 bg-gradient-to-r from-blue-700 via-blue-600 to-cyan-500 rounded-full"></div>
                    <h3 class="font-black text-slate-800 uppercase tracking-wide">Detailed Benefits & Terms</h3>
                </div>

                <div class="grid gap-6 max-h-[300px] overflow-y-auto pr-4 custom-scrollbar">
                    <div v-for="(text, title) in modal_texts" :key="title" class="group">
                        <h4
                            class="font-bold text-blue-800 text-sm mb-1 group-hover:text-blue-600 transition-colors flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-300"></span>
                            {{ title }}
                        </h4>
                        <p class="text-sm text-slate-600 leading-relaxed text-justify ml-3.5">
                            {{ text }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row justify-center gap-4 mt-10">
                <button @click="goStart"
                    class="order-2 sm:order-1 px-8 py-3 rounded-full font-bold text-slate-500 hover:bg-gray-100 transition-all border border-gray-200">
                    Cancel
                </button>
                <button @click="hideModal"
                    class="order-1 sm:order-2 px-10 py-3 rounded-full font-extrabold text-white bg-gradient-to-r from-blue-700 via-blue-600 to-cyan-500 hover:bg-blue-800 hover:shadow-lg active:transform active:scale-95 transition-all shadow-md">
                    Continue Purchase
                </button>
            </div>
        </div>
    </Dialog>

</template>
