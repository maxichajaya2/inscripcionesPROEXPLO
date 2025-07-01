<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import colorbar from '@/Components/colorbar.vue';
import { usePage , router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import Dialog from 'primevue/dialog';
import FormValidacionDoc from './FormValidacionDoc.vue';
import FormInscription from './FormInscription.vue';
import FormPayment from './FormPayment.vue';
import Button from 'primevue/button';
import Functions from '@/Functions';

import Stepper from 'primevue/stepper';
import StepList from 'primevue/steplist';
import StepPanels from 'primevue/steppanels';
import StepPanel from 'primevue/steppanel';
import Step from 'primevue/step';

import Skeleton from 'primevue/skeleton';

import "../../../css/inscripciones.css";

const visible = ref(true);
const page = usePage();
const props = defineProps({
    title : String,
    categorias : Object,
    modal_texts : Object,
})

const formDataValidacionDoc = ref(null);
const formDataInscription = ref(null);
const formDataPayment = ref(null);
const data_persona = ref({});
const categoria_seleccionada = ref({});

const childFormValidacionDoc = ref();
const childFormInscription = ref(null);
const childFormPayment = ref(null);


const validate = async (value) =>{
    switch(value){
        case "Documento":
            formDataValidacionDoc.value  = childFormValidacionDoc.value.getValidacionDoc();
            if(formDataValidacionDoc.value.validate){

                const response = await axios.post( '/api/getperson',
                        { id_tipo_documento: formDataValidacionDoc.value.formValidacionDoc.tipo_doc, numero_documento: formDataValidacionDoc.value.formValidacionDoc.documento } );

                data_persona.value = response.data;
                return true;
            }else{
                return false;
            }
            break;

        case "Inscripcion":

            formDataInscription.value  = childFormInscription.value.getInscripcion();

            if(formDataInscription.value.validate){
                props.categorias.forEach(categoria => {
                    if(categoria.id == formDataInscription.value.formInscription.selected_categoria){
                        categoria_seleccionada.value = categoria;
                    }
                });

                const form_payment = await axios.post( '/pago/getform',
                        { form: formDataInscription.value.formInscription } );

                formDataPayment.value = form_payment.data.formulario;

                return true;

            }else{
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
    <AppLayout title="inscripciones">
        <div class=" px-3 mx-auto max-w-7xl md:px-6 lg:px-8 font-(family-name:Roboto)">
            <div id = "titulo_inicial" class = "mt-8 mb-8">
                <h1 class="text-3xl text-green-iimp font-bold mb-2">{{  props.title }}</h1>
                <colorbar class="block w-auto" />
            </div>
            <div class = "flex justify-around mt-6 mb-6">
                <Stepper value="1" class="w-full">
                    <StepList>
                        <Step value="1" :disabled="true">Validacion de Datos</Step>
                        <Step value="2" :disabled="true">Datos Personales</Step>
                        <Step value="3" :disabled="true">Proceso de Pago</Step>
                    </StepList>
                    <StepPanels>
                        <StepPanel v-slot="{ activateCallback }" value="1">
                            <FormValidacionDoc ref="childFormValidacionDoc" />
                            <div class="flex p-6 justify-end">
                                <Button label="Validar" icon="pi pi-arrow-right" iconPos="right" @click="async () => await validate('Documento') ? activateCallback('2'): false " class="bg-green-iimp border-rounded-full" />
                            </div>
                        </StepPanel>
                        <StepPanel v-slot="{ activateCallback }" value="2" >
                            <FormInscription ref="childFormInscription" :data_persona="data_persona" :categorias="props.categorias"/>
                            <div class="flex justify-between p-6">
                                <Button label="Regresar" severity="secondary" icon="pi pi-arrow-left" @click="activateCallback('1')" class="border-rounded-full" />
                                <Button label="Registro" icon="pi pi-arrow-right" iconPos="right" @click="async () => await validate('Inscripcion') ? activateCallback('3'): false " class="bg-green-iimp border-rounded-full"  />
                            </div>
                        </StepPanel>
                        <StepPanel v-slot="{ activateCallback }" value="3">
                            <FormPayment ref="childFormPayment"  :data_persona="data_persona" :formulario = "formDataPayment" :categoria_seleccionada ="categoria_seleccionada" />
                            <div class="flex justify-between p-6">
                                <Button label="Regresar" severity="secondary" icon="pi pi-arrow-left" @click="activateCallback('2')" class="border-rounded-full" />
                                <!--<Button label="Finalizar" icon="pi pi-check" iconPos="right" @click="" class="bg-green-iimp border-rounded-full"/>-->
                            </div>
                        </StepPanel>
                    </StepPanels>
                </Stepper>
            </div>
        </div>
    </AppLayout>

    <Dialog v-model:visible="visible" modal :style="{ border: 'none' }" class="modal-green max-w-[750px] modal-custom-scroll"  :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
        <div class="modal-head-title font-bold">
            <p>Tarifas Disponibles</p>
        </div>
        <div class="flex justify-around mt-[25px]">
            <div class="flex max-w-[400px] justify-evenly w-[100%] flex-col">
                <div id="head-tarif" class="flex">
                    <div class="w-[65%] min-w-[200px]"></div>
                    <div class="w-[30%] min-w-[150px] text-green-iimp head-tarif font-bold p-[10px]">
                         Tarifa regular
                    </div>
                </div>

                <div id="body-tarif" class="flex font-bold" v-for="(categoria) in categorias">
                    <div class="w-[65%] min-w-[200px] text-center pt-[10px] pb-[10px]">{{  categoria.nombre_es }}</div>
                    <div class="w-[35%] min-w-[150px] text-center body-tarif pt-[10px] pb-[10px]">
                        <p>USD {{  categoria.precio_disponible.valor }}</p>
                    </div>
                </div>

                <div id="foot-tarif" class="flex foot-tarif pt-[10px]">
                    <div class="w-[65%] min-w-[200px]"></div>
                    <div class="w-[35%] min-w-[150px]">
                        <p>Precios incluyen IGV</p>
                    </div>
                </div>
            </div>
        </div>
        <div class ="p-[50px] modal-custom-scroll">
            <p class ="font-bold" >Beneficios:</p>
            <ul class="mt-5 mb-5 ml-10 list-disc">
                <div v-for="(text,index) in modal_texts">
                    <li class ="font-bold">{{ index }}</li>
                    <p class ="text-justify">{{ text }}</p>
                </div>
            </ul>
        </div>
        <div class="flex justify-around">
            <div class="flex max-w-[450px] justify-evenly w-[100%]">
                <button class="border border-white modal-cancel-button p-[12px] rounded-full font-bold min-w-[135px]" @click="goStart">
                    Cancelar
                </button>
                <button class="border border-white modal-continue-button p-[12px] rounded-full font-bold min-w-[130px]" @click="hideModal" >
                    Continuar Compra
                </button>
            </div>
        </div>
    </Dialog>


</template>
