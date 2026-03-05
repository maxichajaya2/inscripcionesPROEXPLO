<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import colorbar from '@/Components/colorbar.vue';
import { router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import Dialog from 'primevue/dialog';
import FormValidacionDoc from './FormValidacionDoc.vue';
import FormInscription from './FormInscription.vue';
import FormTourCourse from './FormTourCourse.vue';
import FormPayment from './FormPayment.vue';
import Button from 'primevue/button';
import { useToast } from 'primevue/usetoast';
import Stepper from 'primevue/stepper';
import StepList from 'primevue/steplist';
import StepPanels from 'primevue/steppanels';
import StepPanel from 'primevue/steppanel';
import Step from 'primevue/step';
import "../../../css/inscripciones.css";

// ESTADOS INICIALES MODIFICADOS
const visible = ref(false); // Modal de origen ahora oculto por defecto
const loading = ref(false);
const toast = useToast();
const bloqueoExtranjero = ref(false);
const categoriaIdActual = ref(null);
const uploading = ref(false);
const uploadProgress = ref(0);

const props = defineProps({
    title: String,
    categorias: Object,
    adicionales: Array,
    section: String,
})

const formDataPayment = ref(null);
const data_persona = ref({});
const showRequisitosModal = ref(false);
const tempResIns = ref(null);
const isPaying = ref(false);
const nacionalidadSeleccionada = ref('peruano'); // Valor por defecto
const childFormValidacionDoc = ref();
const childFormInscription = ref(null);
const childFormTourCourse = ref(null);
const tipo_origen = ref(1); // 1 = Nacional (DNI) por defecto
const categoria_seleccionada = ref({});
const extras_para_mostrar = ref([]);
const urlParams = new URLSearchParams(window.location.search);
const sectionUrl = urlParams.get('section') || 'inscripciones';
const showConfirmNoExtrasModal = ref(false);
const resumen_dinamico = ref({
    total: 0,
    dias_seleccionados: [],
    requiere_doc: false,
    tiene_doc: false
});

const actualizarResumen = (datos) => {
    resumen_dinamico.value = { ...resumen_dinamico.value, ...datos };
};

const validate = async (value) => {
    loading.value = true;
    switch (value) {
        case "Documento":
            const resDoc = await childFormValidacionDoc.value.getValidacionDoc();
            if (resDoc.validate) {
                data_persona.value = resDoc.formValidacionDoc;
                loading.value = false;
                return true;
            }
            break;
    }
    loading.value = false;
    return false;
}

const goStart = () => {
    router.get(route('inscripcion.index'));
};

const proceedToBilling = () => {
    showConfirmNoExtrasModal.value = false;
    activeStep.value = "3";
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const activeStep = ref("1");

const handleCursosHaciaFacturacion = async () => {
    loading.value = true;
    if (childFormTourCourse.value) {
        const esValido = childFormTourCourse.value.validarSeleccion();
        if (!esValido) {
            loading.value = false;
            return;
        }
        const tieneSeleccion = childFormTourCourse.value.extras_seleccionados.length > 0;
        if (!tieneSeleccion) {
            showConfirmNoExtrasModal.value = true;
            loading.value = false;
            return;
        }
        extras_para_mostrar.value = childFormTourCourse.value.selectedObjects || [];
    }
    proceedToBilling();
    loading.value = false;
};

const handleInscripcionFinal = async () => {
    loading.value = true;
    const resIns = await childFormInscription.value.getInscripcion();
    if (resIns.validate) {
        tempResIns.value = resIns;
        const idsExtras = childFormTourCourse.value?.extras_seleccionados || [];
        await confirmarYProcesar(idsExtras);
    }
    loading.value = false;
};

const confirmarYProcesar = async (extras = []) => {
    showRequisitosModal.value = false;
    loading.value = true;
    isPaying.value = true;

    try {
        const payload = new FormData();
        Object.keys(data_persona.value).forEach(key => {
            payload.append(key, data_persona.value[key]);
        });

        if (tempResIns.value && tempResIns.value.formInscription) {
            Object.keys(tempResIns.value.formInscription).forEach(key => {
                if (key === 'uploadDocument') {
                    if (tempResIns.value.formInscription[key]) {
                        payload.append(key, tempResIns.value.formInscription[key]);
                    }
                } else {
                    if (!payload.has(key)) {
                        payload.append(key, tempResIns.value.formInscription[key]);
                    }
                }
            });
        }

        const profileId = urlParams.get('profile');
        if (profileId) payload.append('profile', profileId);

        payload.append('section', props.section);
        payload.append('extras_seleccionados', JSON.stringify(extras));

        const response = await axios.post('/pago/getform', payload, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        if (response.data.status && response.data.formulario) {
            formDataPayment.value = response.data.formulario;
            activeStep.value = "4";
            const catId = tempResIns.value.formInscription.selected_categoria;
            const encontrada = props.categorias.find(c => c.id == catId);
            if (encontrada) categoria_seleccionada.value = encontrada;

            const totalFinal = response.data.total_real || tempResIns.value.total_final;
            actualizarResumen({ total: totalFinal });
        } else {
            toast.add({ severity: 'error', summary: 'Error', detail: response.data.message });
        }
    } catch (error) {
        console.error("Error:", error);
        toast.add({ severity: 'error', summary: 'Error', detail: 'Ocurrió un error al procesar la solicitud.' });
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    const categoryId = urlParams.get('category');
    categoriaIdActual.value = categoryId;

    if (categoryId == '35' || categoryId == '29') {
        bloqueoExtranjero.value = true;
    }

    if (categoryId && props.categorias) {
        const listaCategorias = Object.values(props.categorias);
        const encontrada = listaCategorias.find(c => c.id == categoryId);
        if (encontrada) categoria_seleccionada.value = encontrada;
    }

    window.addEventListener('beforeunload', handleBeforeUnload);
});

const handleBeforeUnload = (event) => {
    if (isPaying.value) return;
    if (data_persona.value.documento || data_persona.value.nombres) {
        event.preventDefault();
        event.returnValue = '';
    }
};

onUnmounted(() => {
    window.removeEventListener('beforeunload', handleBeforeUnload);
});

watch(activeStep, () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
});
</script>

<template>
    <AppLayout class="bg-gradient-wmc">
        <div class="px-3 mx-auto max-w-7xl md:px-6 lg:px-8 relative">
            <div id="titulo_inicial" class="mt-8 mb-8">
                <h1 class="text-3xl text-green-iimp font-bold mb-2 text-yellow-price">{{ props.title }}</h1>
                <colorbar class="block w-auto" />
            </div>

            <div class="mt-6 mb-6">
                <Stepper v-model:value="activeStep" class="w-full">
                    <StepList class="text-black-price bg-degradient">
                        <Step value="1">Detalles Personales</Step>
                        <Step value="2">Cursos o Tours</Step>
                        <Step value="3">Información de Facturación</Step>
                        <Step value="4">Proceso de Pago</Step>
                    </StepList>

                    <StepPanels>
                        <StepPanel v-slot="{ activateCallback }" value="1"
                            class="rounded-2xl border-2 border-green-iimp bg-white-price shadow-wmc">
                            <FormValidacionDoc ref="childFormValidacionDoc" :tipo_origen="tipo_origen" />

                            <div class="sticky bottom-0 left-0 w-full p-4 md:p-6 bg-white/95 backdrop-blur-md border-t border-gray-200 z-[50] flex justify-end gap-3 rounded-b-2xl">
                                <Button label="Validar" icon="pi pi-arrow-right" iconPos="right"
                                    class="bg-degradient border-rounded-full" :loading="loading"
                                    :disabled="childFormValidacionDoc?.esCategoriaDeSocio && childFormValidacionDoc?.hasSearched && !childFormValidacionDoc?.esSocio"
                                    @click="async () => {
                                        const isValid = await validate('Documento');
                                        if (isValid) activateCallback('2');
                                    }" />
                            </div>
                        </StepPanel>

                        <StepPanel v-slot="{ activateCallback }" value="2"
                            class="rounded-2xl border-2 border-green-iimp bg-white shadow-wmc">
                            <FormTourCourse ref="childFormTourCourse" :data_persona="data_persona"
                                :adicionales="props.adicionales" :section="sectionUrl" />

                            <div class="sticky bottom-0 left-0 w-full p-4 md:p-6 bg-white/95 backdrop-blur-md border-t border-gray-200 z-[50] flex justify-between gap-3 rounded-b-2xl">
                                <Button label="Atrás" severity="secondary" icon="pi pi-arrow-left"
                                    class="flex-1 md:flex-none p-3 font-bold" @click="activateCallback('1')" />
                                <Button label="Continuar a Facturación" iconPos="right" icon="pi pi-arrow-right"
                                    class="bg-degradient border-rounded-full flex-1 md:flex-none" :loading="loading"
                                    @click="handleCursosHaciaFacturacion" />
                            </div>
                        </StepPanel>

                        <StepPanel v-slot="{ activateCallback }" value="3"
                            class="rounded-2xl border-2 border-green-iimp bg-white shadow-wmc">
                            <FormInscription ref="childFormInscription" :data_persona="data_persona"
                                :categorias="props.categorias" />

                            <div class="sticky bottom-0 left-0 w-full p-4 md:p-6 bg-white/95 backdrop-blur-md border-t border-gray-200 z-[50] flex justify-between gap-3 rounded-b-2xl">
                                <Button label="Atrás" severity="secondary" icon="pi pi-arrow-left"
                                    class="flex-1 md:flex-none" @click="activateCallback('2')" />
                                <Button label="Registrar y Pagar" iconPos="right" icon="pi pi-arrow-right"
                                    class="bg-degradient border-rounded-full flex-1 md:flex-none" :loading="loading"
                                    @click="handleInscripcionFinal" />
                            </div>
                        </StepPanel>

                        <StepPanel v-slot="{ activateCallback }" value="4"
                            class="rounded-2xl border-2 border-green-iimp bg-white shadow-wmc">
                            <FormPayment ref="childFormPayment" :data_persona="data_persona"
                                :formulario="formDataPayment" :categoria_seleccionada="categoria_seleccionada"
                                :extras_seleccionados="extras_para_mostrar" />

                            <div class="sticky bottom-0 left-0 w-full p-4 md:p-6 bg-white/95 backdrop-blur-md border-t border-gray-200 z-[50] flex justify-between gap-3 rounded-b-2xl">
                                <Button label="Atrás" severity="secondary" icon="pi pi-arrow-left"
                                    @click="activateCallback('3')" />
                            </div>
                        </StepPanel>
                    </StepPanels>
                </Stepper>
            </div>
        </div>

        <Dialog v-model:visible="uploading" modal :closable="false" :showHeader="false" :style="{ width: '350px' }">
            <div class="flex flex-col items-center p-6 text-center">
                <i class="pi pi-cloud-upload text-5xl animate-bounce text-green-iimp mb-4"></i>
                <h3 class="text-xl font-black text-blue-900 mb-2">Uploading Document</h3>
                <div class="w-full bg-gray-100 rounded-full h-4 mb-2 overflow-hidden border border-gray-200">
                    <div class="bg-gradient-to-r from-green-500 to-green-300 h-full transition-all duration-300"
                        :style="{ width: uploadProgress + '%' }"></div>
                </div>
                <span class="text-xs font-bold text-green-600">{{ uploadProgress }}% Completed</span>
            </div>
        </Dialog>

        <Dialog v-model:visible="showConfirmNoExtrasModal" modal :showHeader="false" :closable="false"
            :style="{ width: '500px' }" class="rounded-3xl overflow-hidden border-none shadow-2xl animate-modal-entry">
            <div class="p-0 relative overflow-hidden">
                <div class="bg-gradient-to-r from-blue-900 via-blue-700 to-blue-900 p-8 text-center">
                    <div class="w-20 h-20 bg-white/10 rounded-full flex items-center justify-center mx-auto mb-4 border border-white/20">
                        <i class="pi pi-shopping-cart text-yellow-400 text-4xl"></i>
                    </div>
                    <h3 class="text-2xl font-black text-white uppercase italic">Enhance your Experience</h3>
                </div>
                <div class="p-10 bg-white text-center">
                    <p class="text-slate-700 text-lg font-medium">
                        Are you sure you want to proceed without adding <span class="text-blue-700 font-extrabold">Short Courses</span> or <span class="text-blue-700 font-extrabold">Technical Visits</span>?
                    </p>
                    <div class="mt-8 flex flex-col gap-4">
                        <button @click="showConfirmNoExtrasModal = false"
                            class="w-full py-4 px-6 rounded-2xl bg-blue-900 text-white font-black uppercase tracking-widest shadow-lg">
                            Review Courses & Visits
                        </button>
                        <button @click="proceedToBilling"
                            class="w-full py-2 text-slate-400 text-[10px] font-black uppercase hover:text-red-500">
                            No thanks, proceed to billing anyway
                        </button>
                    </div>
                </div>
            </div>
        </Dialog>
    </AppLayout>
</template>
<style scoped>
/* 1. Aseguramos que el panel del Stepper permita el posicionamiento sticky */
:deep(.p-steppanel) {
    display: flex;
    flex-direction: column;
    height: 100%;
    position: relative;
}

/* 2. El contenido del formulario debe empujar los botones hacia abajo */
:deep(.p-steppanel-content) {
    flex: 1;
}

/* 3. Estilo para el contenedor Sticky */
.sticky {
    position: -webkit-sticky;
    /* Soporte para Safari */
    position: sticky;
    bottom: -2px;
    /* Un pequeño ajuste para que encaje perfecto con el borde */
    background-color: rgba(255, 255, 255, 0.98);
    z-index: 40;
    margin-top: auto;
    /* Empuja el div al final si el contenido es corto */
}

/* 4. En Web, le damos un redondeado inferior para que coincida con el Card */
@media (min-width: 768px) {
    .sticky {
        border-bottom-left-radius: 1rem;
        border-bottom-right-radius: 1rem;
    }
}



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

@media (max-width: 768px) {

    /* Añade espacio al final de los paneles para que el footer fijo no tape el contenido */
    :deep(.p-steppanel) {
        padding-bottom: 80px !important;
    }
}

/* Animación de entrada del Modal */
.animate-modal-entry {
    animation: modalSpring 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
}

@keyframes modalSpring {
    0% {
        opacity: 0;
        transform: scale(0.8) translateY(50px);
    }

    100% {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

/* Brillo constante en la cabecera */
.shine-effect {
    background: linear-gradient(to right,
            rgba(255, 255, 255, 0) 0%,
            rgba(255, 255, 255, 0.05) 50%,
            rgba(255, 255, 255, 0) 100%);
    transform: skewX(-25deg);
    animation: shineLoop 3s infinite;
}

@keyframes shineLoop {
    0% {
        transform: translateX(-150%) skewX(-25deg);
    }

    100% {
        transform: translateX(150%) skewX(-25deg);
    }
}

/* Animación de brillo rápido al pasar el mouse por el botón */
.group-hover\:animate-shine-fast {
    animation: shineFast 0.6s forwards;
}

@keyframes shineFast {
    0% {
        transform: translateX(-100%) skewX(-25deg);
    }

    100% {
        transform: translateX(100%) skewX(-25deg);
    }
}

/* Rebote suave para el icono */
.animate-bounce-slow {
    animation: bounceSlow 3s infinite;
}

@keyframes bounceSlow {

    0%,
    100% {
        transform: translateY(0);
    }

    50% {
        transform: translateY(-10px);
    }
}

/* Botón flotante - SOLO VISIBLE EN MÓVIL */
.mobile-floating-validate {
    display: none;
    position: fixed;
    bottom: 1.5rem;
    right: 1.5rem;
    z-index: 50;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fadeInUp 0.4s ease-out forwards;
}

/* MOSTRAR BOTÓN FLOTANTE SOLO EN MÓVIL */
@media (max-width: 768px) {
    .mobile-floating-validate {
        display: block;
    }
}

@media (max-width: 480px) {
    .mobile-floating-validate {
        bottom: 1rem !important;
        right: 1rem !important;
    }
}

/* Botón flotante Register - SOLO VISIBLE EN MÓVIL */
.mobile-floating-register {
    display: none;
    position: fixed;
    bottom: 1.5rem;
    right: 1.5rem;
    z-index: 50;
}

/* MOSTRAR BOTÓN FLOTANTE SOLO EN MÓVIL */
@media (max-width: 768px) {
    .mobile-floating-register {
        display: block;
    }
}

@media (max-width: 480px) {
    .mobile-floating-register {
        bottom: 1rem !important;
        right: 1rem !important;
    }
}
</style>
