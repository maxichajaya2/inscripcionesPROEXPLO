<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import colorbar from '@/Components/colorbar.vue';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
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
const nacionalidadSeleccionada = ref(null);

const childFormValidacionDoc = ref();
const childFormInscription = ref(null);
const childFormPayment = ref(null);
const tipo_origen = ref(0);

// --- LÓGICA DE VALIDACIÓN ---
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

// --- FUNCIÓN MODIFICADA PARA RECIBIR EL ID ---
const seleccionarOrigen = (origen, id_numerico) => {
    nacionalidadSeleccionada.value = origen; // Guarda el texto 'peruano'/'extranjero'
    tipo_origen.value = id_numerico;         // Guarda el número 1 o 2
    visible.value = false;

    console.log("Nacionalidad:", origen);
    console.log("ID Numérico:", tipo_origen.value); // Para que veas en consola que se guardó
};

const goStart = () => {
    router.get(route('inscripcion.index'));
};
</script>

<template>
    <AppLayout title="Inscripciones" class="bg-gradient-wmc">

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
                        <StepPanel v-slot="{ activateCallback }" value="1"
                            class="rounded-2xl border-2 border-green-iimp bg-white-price shadow-wmc">
                            <FormValidacionDoc ref="childFormValidacionDoc"
                                :nacionalidadPrevia="nacionalidadSeleccionada" />
                            <div class="flex p-6 justify-end">
                                <Button label="Validate" icon="pi pi-arrow-right" iconPos="right"
                                    @click="async () => await validate('Documento') ? activateCallback('2') : false"
                                    class="bg-degradient border-rounded-full" :loading="loading" />
                            </div>
                        </StepPanel>
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
                        <StepPanel v-slot="{ activateCallback }" value="3">
                            <FormPayment ref="childFormPayment" :data_persona="data_persona"
                                :formulario="formDataPayment" :categoria_seleccionada="categoria_seleccionada" />
                            <div class="flex justify-between p-6">
                                <Button label="Back" severity="secondary" icon="pi pi-arrow-left"
                                    @click="activateCallback('2')" class="border-rounded-full" />
                            </div>
                        </StepPanel>
                    </StepPanels>
                </Stepper>
            </div>
        </div>

        <Dialog v-model:visible="visible" modal :showHeader="false" :closable="false" :style="{ width: '900px' }"
            class="bg-transparent shadow-none border-none px-0" :pt="{
                mask: { class: 'bg-slate-900/90 backdrop-blur-md' },
                content: { class: 'bg-transparent px-0 py-0 border-none shadow-none' }
            }">

            <div class="bg-white rounded-3xl overflow-hidden shadow-2xl animate-fade-in-down px-0">

                <div
                    class="bg-gradient-to-r from-[#001e3d] via-[#002855] to-[#003366] px-8 py-8 border-b-4 border-yellow-500 flex items-center justify-between relative overflow-hidden">

                    <div
                        class="absolute inset-0 opacity-20 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')]">
                    </div>

                    <div
                        class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2">
                    </div>

                    <div class="relative z-10">
                        <div class="mb-3 animate-fade-in-up" style="animation-delay: 0.1s;">
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-yellow-500/10 border border-yellow-500/30 backdrop-blur-md">
                                <span class="relative flex h-2 w-2">
                                    <span
                                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-yellow-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-yellow-500"></span>
                                </span>
                                <span
                                    class="text-[10px] font-bold text-yellow-400 uppercase tracking-widest">Registration
                                    Open</span>
                            </span>
                        </div>

                        <h2
                            class=" text-2xl md:text-4xl text-transparent bg-clip-text bg-gradient-to-r from-yellow-200 via-yellow-400 to-yellow-600">
                            World Mining Congress 2026

                        </h2>
                    </div>
                </div>

                <div class="p-8 md:p-12 bg-slate-50 px-0">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                        <button @click="seleccionarOrigen('peruano',1)"
                            class="group relative h-auto rounded-2xl bg-white border border-slate-200 shadow-lg hover:shadow-2xl hover:shadow-red-900/20 transition-all duration-300 flex flex-col overflow-hidden hover:-translate-y-2">

                            <div class="h-1 w-full bg-red-600"></div>

                            <div class="p-8 flex flex-col items-center text-center h-full">

                                <div
                                    class="w-24 h-24 mb-6 relative drop-shadow-lg group-hover:scale-110 transition-transform duration-300">
                                    <svg viewBox="0 0 300 200" class="w-full h-full rounded-lg shadow-sm">
                                        <rect width="300" height="200" fill="#ffffff" stroke="#e2e8f0"
                                            stroke-width="2" />
                                        <rect width="100" height="200" fill="#D91023" />
                                        <rect x="200" width="100" height="200" fill="#D91023" />
                                    </svg>
                                    <div class="absolute inset-0 bg-red-500/20 blur-2xl -z-10 rounded-full"></div>
                                </div>

                                <h3
                                    class="text-2xl font-black text-slate-800 group-hover:text-red-700 transition-colors uppercase">
                                    National
                                </h3>
                                <p class="text-sm text-slate-500 mt-2 mb-8 leading-relaxed font-medium">
                                    Peruvian citizen or resident with DNI.
                                </p>

                                <div class="mt-auto w-full">
                                    <span
                                        class="block w-full py-3 px-4 rounded-xl bg-gradient-to-r from-red-700 to-red-600 text-white font-bold text-sm tracking-wider uppercase shadow-md group-hover:shadow-lg group-hover:from-red-600 group-hover:to-red-500 transition-all flex items-center justify-center gap-2">
                                        Continue Purchase <i class="pi pi-arrow-right text-xs"></i>
                                    </span>
                                </div>
                            </div>
                        </button>

                        <button @click="seleccionarOrigen('extranjero',2)"
                            class="group relative h-auto rounded-2xl bg-white border border-slate-200 shadow-lg hover:shadow-2xl hover:shadow-blue-900/20 transition-all duration-300 flex flex-col overflow-hidden hover:-translate-y-2">

                            <div class="h-1 w-full bg-blue-600"></div>

                            <div class="p-8 flex flex-col items-center text-center h-full">

                                <div
                                    class="w-24 h-24 mb-6 relative drop-shadow-lg group-hover:scale-110 group-hover:rotate-12 transition-all duration-500">
                                    <svg viewBox="0 0 24 24" fill="none" class="w-full h-full text-blue-600">
                                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"
                                            fill="#eff6ff" />
                                        <path d="M2.3 12H21.7" stroke="currentColor" stroke-width="1.5"
                                            stroke-linecap="round" />
                                        <path
                                            d="M12 2.3C14.5 5 16 8.5 16 12C16 15.5 14.5 19 12 21.7C9.5 19 8 15.5 8 12C8 8.5 9.5 5 12 2.3Z"
                                            stroke="currentColor" stroke-width="1.5" fill="#dbeafe" />
                                    </svg>
                                    <div class="absolute inset-0 bg-blue-500/20 blur-2xl -z-10 rounded-full"></div>
                                </div>

                                <h3
                                    class="text-2xl font-black text-slate-800 group-hover:text-blue-700 transition-colors uppercase">
                                    International
                                </h3>
                                <p class="text-sm text-slate-500 mt-2 mb-8 leading-relaxed font-medium">
                                    Joining from abroad (Foreigner).
                                </p>

                                <div class="mt-auto w-full">
                                    <span
                                        class="block w-full py-3 px-4 rounded-xl bg-gradient-to-r from-[#002855] to-blue-700 text-white font-bold text-sm tracking-wider uppercase shadow-md group-hover:shadow-lg group-hover:from-blue-800 group-hover:to-blue-600 transition-all flex items-center justify-center gap-2">
                                        Continue Purchase <i class="pi pi-arrow-right text-xs"></i>
                                    </span>
                                </div>
                            </div>
                        </button>

                    </div>
                </div>

                <div class="bg-gray-50 p-6 border-t border-slate-200 text-center">
                    <button @click="goStart"
                        class="group flex items-center justify-center gap-2 text-xs text-slate-400 font-bold uppercase tracking-widest hover:text-red-500 transition-colors mx-auto">
                        <i class="pi pi-times-circle text-lg group-hover:scale-110 transition-transform"></i>
                        Cancel Process
                    </button>
                </div>

            </div>
        </Dialog>

    </AppLayout>
</template>

<style scoped>
.animate-fade-in-down {
    animation: fadeInDown 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-40px) scale(0.95);
    }

    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}
</style>
