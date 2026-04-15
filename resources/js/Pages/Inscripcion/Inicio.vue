<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import colorbar from '@/Components/colorbar.vue';
import { router, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import Dialog from 'primevue/dialog';
import FormValidacionDoc from './FormValidacionDoc.vue';
import FormInscription from './FormInscription.vue';
import FormTourCourse from './FormTourCourse.vue';
import FormPayment from './FormPayment.vue';
import Button from 'primevue/button';
import { useToast } from 'primevue/usetoast';
import Card from 'primevue/card';
import Stepper from 'primevue/stepper';
import StepList from 'primevue/steplist';
import StepPanels from 'primevue/steppanels';
import StepPanel from 'primevue/steppanel';
import Step from 'primevue/step';
import "../../../css/inscripciones.css";

const page = usePage();
const words = page.props.language.words; // Extraemos el diccionario

const visible = ref(false);
const loading = ref(false);
const toast = useToast();
const bloqueoExtranjero = ref(false);
const categoriaIdActual = ref(null);
const props = defineProps({
    title: String,
    categorias: Object,
    adicionales: Array,
    section: String,
    perfil_id: Number,
    course: Array,
    cupones: Object
})

const formDataPayment = ref(null);
const mostrarModalFacturacion = ref(false);
const data_persona = ref({});
const showRequisitosModal = ref(false);
const tempResIns = ref(null);
const isPaying = ref(false);
const nacionalidadSeleccionada = ref(null);
const childFormValidacionDoc = ref();
const childFormInscription = ref(null);
const childFormTourCourse = ref(null);
const tipo_origen = ref(0);
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

const saltoCursos = ref(true);

const pasosCompletos = computed(() => [
    { value: "1", label: words.step_personal },
    { value: "2", label: words.step_billing },
    { value: "3", label: words.step_courses },
    { value: "4", label: words.step_payment }
]);

const pasosVisibles = computed(() => {
    let listaFiltrada = pasosCompletos.value.filter(p => {
        if (saltoCursos.value) return p.value !== "3";
        return true;
    });

    return listaFiltrada.map((paso, index) => {
        const nuevoValor = (index + 1).toString();
        return {
            ...paso,
            value: nuevoValor,
            labelReal: paso.label
        };
    });
});

const handleIrACursosDesdeModal = () => {
    saltoCursos.value = false;
    showConfirmNoExtrasModal.value = false;

    setTimeout(() => {
        activeStep.value = "3";
    }, 50);
};

const handleSaltarCursosEIrAPago = async () => {
    saltoCursos.value = true;
    showConfirmNoExtrasModal.value = false;
    loading.value = true;
    await confirmarYProcesar([]);
    loading.value = false;
};

const validate = async (value) => {
    loading.value = true;
    switch (value) {
        case "Documento":
            const resDoc = await childFormValidacionDoc.value.getValidacionDoc();
            if (resDoc.validate) {
                data_persona.value = resDoc.formValidacionDoc;
                mostrarModalFacturacion.value = true;
                loading.value = false;
                return true;
            }
            break;

        case "Inscripcion":
            const resIns = await childFormInscription.value.getInscripcion();
            if (resIns.validate) {
                try {
                    const payload = new FormData();
                    Object.keys(data_persona.value).forEach(key => {
                        payload.append(key, data_persona.value[key]);
                    });

                    Object.keys(resIns.formInscription).forEach(key => {
                        if (key === 'uploadDocument') {
                            if (resIns.formInscription[key]) {
                                payload.append(key, resIns.formInscription[key]);
                            }
                        } else {
                            if (!payload.has(key)) {
                                payload.append(key, resIns.formInscription[key]);
                            }
                        }
                    });

                    const response = await axios.post('/pago/getform', payload, {
                        headers: { 'Content-Type': 'multipart/form-data' }
                    });

                    if (response.data.status && response.data.formulario) {
                        formDataPayment.value = response.data.formulario;
                        const cat = props.categorias.find(c => c.id == resIns.formInscription.selected_categoria);
                        if (cat) categoria_seleccionada.value = cat;
                        loading.value = false;
                        return true;
                    } else {
                        toast.add({ severity: 'error', summary: words.toast_error_title, detail: response.data.message });
                    }
                } catch (error) {
                    console.error("Error:", error);
                    toast.add({
                        severity: 'error',
                        summary: words.toast_error_title,
                        detail: words.toast_payment_failed
                    });
                }
            }
            break;
    }
    loading.value = false;
    return false;
}

const goStart = () => {
    router.get(route('inscripcion.index'));
};

const activeStep = ref("1");

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

        const profileId = props.perfil_id || new URLSearchParams(window.location.search).get('profile');
        if (profileId) {
            payload.append('profile', profileId);
        }

        payload.append('section', props.section);
        payload.append('extras_seleccionados', JSON.stringify(extras));

        const response = await axios.post('/pago/getform', payload, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        if (response.data.status && response.data.formulario) {
            formDataPayment.value = response.data;
            activeStep.value = "4";
            loading.value = false;

            const catId = tempResIns.value.formInscription.selected_categoria;
            const encontrada = props.categorias.find(c => c.id == catId);
            if (encontrada) {
                categoria_seleccionada.value = encontrada;
            }

            const totalFinal = response.data.total_real || tempResIns.value.total_final;
            actualizarResumen({ total: totalFinal });
        } else {
            toast.add({ severity: 'error', summary: words.toast_error_title, detail: response.data.message });
            loading.value = false;
        }
    } catch (error) {
        console.error("Error en confirmarYProcesar:", error);
        toast.add({
            severity: 'error',
            summary: words.toast_error_title,
            detail: words.toast_error_processing
        });
        loading.value = false;
    }
};

const handleInscripcionHaciaCursos = async () => {
    loading.value = true;
    const resIns = await childFormInscription.value.getInscripcion();

    if (resIns.validate) {
        tempResIns.value = resIns;
        showConfirmNoExtrasModal.value = true;
    }
    loading.value = false;
};

const handleFinalizarTodo = async () => {
    loading.value = true;
    if (childFormTourCourse.value) {
        const esValido = childFormTourCourse.value.validarSeleccion();
        if (!esValido) {
            loading.value = false;
            return;
        }

        const idsExtras = childFormTourCourse.value.extras_seleccionados || [];
        extras_para_mostrar.value = childFormTourCourse.value.selectedObjects || [];
        await confirmarYProcesar(idsExtras);
    }
    loading.value = false;
};

const isStep2Invalid = computed(() => {
    if (!childFormInscription.value) return true;
    return childFormInscription.value.isInvalid;
});


onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const categoryId = urlParams.get('category');
    categoriaIdActual.value = categoryId;

    if (categoryId == '35' || categoryId == '29') {
        bloqueoExtranjero.value = true;
    }

    if (categoryId && props.categorias) {
        const listaCategorias = Object.values(props.categorias);
        const encontrada = listaCategorias.find(c => c.id == categoryId);
        if (encontrada) {
            categoria_seleccionada.value = encontrada;
            console.log("Resumen actualizado con:", encontrada.nombre_en);
        }
    }
});

const handleBeforeUnload = (event) => {
    if (isPaying.value) return;
    if (data_persona.value.documento || data_persona.value.nombres) {
        event.preventDefault();
        event.returnValue = '';
    }
};

onMounted(() => {
    window.addEventListener('beforeunload', handleBeforeUnload);
});

onUnmounted(() => {
    window.removeEventListener('beforeunload', handleBeforeUnload);
});

watch(activeStep, () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
});

watch(activeStep, (newStep) => {
    if (!window.fbq) return;

    switch (newStep) {
        case "1":
            window.fbq('track', 'Detalles Personales PROEXPLO 2026', { step: 'Detalles Personales' });
            break;
        case "2":
            window.fbq('track', 'Informacion de Facturacion PROEXPLO 2026', { step: 'Informacion de Facturacion' });
            break;
        case "3":
            window.fbq('track', 'Cursos Cortos y Visitas Tecnicas PROEXPLO 2026', { step: 'Cursos Cortos y Visitas Tecnicas' });
            break;
        case "4":
            window.fbq('track', 'Proceso de Pago PROEXPLO 2026', { step: 'Proceso de Pago' });
            break;
    }
});

</script>

<template>
    <AppLayout class="bg-proexplo-dark">
        <div class="px-3 mx-auto max-w-7xl md:px-6 lg:px-8 relative">
            <div id="titulo_inicial" class="mt-8 mb-8">
                <h1 class="text-3xl text-green-iimp font-bold mb-2 text-yellow-price">{{ props.title }}</h1>
                <colorbar class="block w-auto" />
            </div>

            <div class="mt-6 mb-6">
                <Stepper v-model:value="activeStep" class="w-full">
                    <StepList class="text-black-price bg-degradient">
                        <Step v-for="paso in pasosVisibles" :key="paso.value" :value="paso.value"
                            :class="{ 'pointer-events-none': activeStep !== paso.value }">
                            {{ paso.labelReal }}
                        </Step>
                    </StepList>
                    <StepPanels>
                        <StepPanel v-slot="{ activateCallback }" value="1"
                            class="rounded-2xl border-2 border-green-iimp bg-white-price shadow-wmc">
                            <FormValidacionDoc ref="childFormValidacionDoc" :perfil_id="props.perfil_id"  />
                            <div
                                class="sticky bottom-0 left-0 w-full p-4 md:p-6 bg-white/95 backdrop-blur-md border-t border-gray-200 shadow-[0_-5px_20px_rgba(0,0,0,0.1)] z-[50] flex justify-end gap-3 rounded-b-2xl">
                                <Button :label="words.btn_validate" icon="pi pi-arrow-right" iconPos="right"
                                    class="bg-degradient border-rounded-full" :loading="loading"
                                    :perfil_id="props.perfil_id"
                                    :disabled="childFormValidacionDoc?.esCategoriaDeSocio && childFormValidacionDoc?.hasSearched && !childFormValidacionDoc?.esSocio"
                                    @click="async () => {
                                        const isValid = await validate('Documento');
                                        if (isValid) {
                                            if (childFormValidacionDoc?.esCategoriaDeSocio) {
                                                if (childFormValidacionDoc?.esSocio) activateCallback('2');
                                            } else {
                                                activateCallback('2');
                                            }
                                        }
                                    }" />
                            </div>
                        </StepPanel>

                        <StepPanel v-slot="{ activateCallback }" value="2"
                            class="rounded-2xl border-2 border-green-iimp bg-white shadow-wmc">
                            <FormInscription ref="childFormInscription" :data_persona="data_persona"  :cupones="props.cupones"  :activarModal="mostrarModalFacturacion"
                                :categorias="props.categorias" />
                            <div
                                class="sticky bottom-0 left-0 w-full p-4 md:p-6 bg-white/95 backdrop-blur-md border-t border-gray-200 shadow-[0_-5px_20px_rgba(0,0,0,0.1)] z-[50] flex justify-between gap-3 rounded-b-2xl">
                                <Button :label="words.btn_back" severity="secondary" icon="pi pi-arrow-left"
                                    class="flex-1 md:flex-none" @click="activateCallback('1')" />
                                <Button :label="words.btn_register_pay" iconPos="right" icon="pi pi-arrow-right"  :disabled="isStep2Invalid"
                                    class="bg-degradient border-rounded-full flex-1 md:flex-none" :loading="loading"
                                    @click="handleInscripcionHaciaCursos" />
                            </div>
                        </StepPanel>

                        <StepPanel v-if="!saltoCursos" v-slot="{ activateCallback }" value="3"
                            class="rounded-2xl border-2 border-green-iimp bg-white shadow-wmc">
                            <FormTourCourse ref="childFormTourCourse" :data_persona="data_persona"
                                :adicionales="props.adicionales" :section="sectionUrl" :course="props.course"/>
                            <div
                                class="sticky bottom-0 left-0 w-full p-4 md:p-6 bg-white/95 backdrop-blur-md border-t border-gray-200 shadow-[0_-5px_20px_rgba(0,0,0,0.1)] z-[50] flex justify-between gap-3 rounded-b-2xl">
                                <Button :label="words.btn_back" severity="secondary" icon="pi pi-arrow-left"
                                    class="flex-1 md:flex-none p-3 font-bold" @click="activeStep = '2'" />
                                <Button :label="words.btn_continue" iconPos="right" icon="pi pi-arrow-right"
                                    class="bg-degradient border-rounded-full flex-1 md:flex-none" :loading="loading"
                                    @click="handleFinalizarTodo" />
                            </div>
                        </StepPanel>

                        <StepPanel v-slot="{ activateCallback }" value="4"
                            class="rounded-2xl border-2 border-green-iimp bg-white shadow-wmc">
                            <FormPayment ref="childFormPayment" :data_persona="data_persona"
                                :formulario="formDataPayment" :categoria_seleccionada="categoria_seleccionada" :vouchers="vouchers"
                                :extras_seleccionados="extras_para_mostrar" :descuento="formDataPayment?.descuento" :datos_facturacion="tempResIns?.formInscription" />
                            <div
                                class="sticky bottom-0 left-0 w-full p-4 md:p-6 bg-white/95 backdrop-blur-md border-t border-gray-200 shadow-[0_-5px_20px_rgba(0,0,0,0.1)] z-[50] flex justify-between gap-3 rounded-b-2xl">
                                <Button :label="words.btn_back" severity="secondary" icon="pi pi-arrow-left" @click="() => {
                                    if (saltoCursos) {
                                        activeStep = '2';
                                    } else {
                                        activeStep = '3';
                                    }
                                }" />
                            </div>
                        </StepPanel>
                    </StepPanels>
                </Stepper>
            </div>
        </div>

        <Dialog v-if="false" v-model:visible="showRequisitosModal" modal :header="words.modal_req_title"
            :style="{ width: '50vw' }" :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
            <div class="flex flex-col gap-4">
                <p class="text-gray-600">{{ words.modal_req_desc }}</p>

                <div class="w-full h-[400px] border rounded overflow-hidden">
                    <iframe src="/documents/reglamento.pdf" class="w-full h-full" frameborder="0"></iframe>
                </div>

                <div class="flex justify-end gap-3 mt-4">
                    <Button :label="words.btn_cancel" icon="pi pi-times" @click="showRequisitosModal = false"
                        class="p-button-text p-button-secondary" />
                    <Button :label="words.btn_accept_pay" icon="pi pi-check" @click="confirmarYProcesar"
                        class="p-button-success" :loading="loading" />
                </div>
            </div>
        </Dialog>

        <Dialog v-model:visible="showConfirmNoExtrasModal" modal :showHeader="false" :closable="false"
            :style="{ width: '550px' }" class="rounded-3xl overflow-hidden border-none shadow-2xl animate-modal-entry">
            <div class="p-0 relative overflow-hidden">
                <div class="bg-gradient-to-r from-orange-600 via-orange-500 to-orange-600 p-8 text-center relative overflow-hidden">
                    <div class="absolute inset-0 shine-effect"></div>
                    <div class="relative z-10">
                        <div class="w-20 h-20 bg-white/10 rounded-full flex items-center justify-center mx-auto mb-4 backdrop-blur-md border border-white/20 animate-bounce-slow">
                            <i class="pi pi-star-fill text-yellow-300 text-4xl drop-shadow-[0_0_15px_rgba(253,224,71,0.6)]"></i>
                        </div>
                        <h3 class="text-2xl font-black text-white uppercase tracking-tighter italic">
                            {{ words.modal_power_title }}
                        </h3>
                        <div class="h-1 w-20 bg-white mx-auto mt-2 rounded-full opacity-50"></div>
                    </div>
                </div>

                <div class="p-10 bg-white text-center">
                    <p class="text-slate-700 text-xl leading-tight font-bold mb-4" v-html="words.modal_power_subtitle"></p>
                    <p class="text-slate-600 text-sm leading-relaxed mb-6" v-html="words.modal_power_desc"></p>
                    <p class="text-green-600 text-xs font-black uppercase tracking-[0.1em] bg-green-50 p-4 rounded-2xl border border-green-100">
                        {{ words.modal_power_quote }}
                    </p>

                    <div class="mt-8 flex flex-col gap-4">
                        <button @click="handleIrACursosDesdeModal"
                            class="group relative w-full py-4 px-6 rounded-2xl bg-orange-600 text-white font-black uppercase tracking-widest overflow-hidden transition-all hover:scale-[1.02] active:scale-95 shadow-[0_10px_20px_rgba(249,115,22,0.3)] hover:bg-orange-500">
                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:animate-shine-fast"></div>
                            <span class="relative flex items-center justify-center gap-3">
                                <i class="pi pi-plus-circle"></i>
                                {{ words.modal_power_btn_yes }}
                            </span>
                        </button>

                        <button @click="handleSaltarCursosEIrAPago"
                            class="w-full py-2 text-slate-400 text-[10px] font-black uppercase tracking-[0.2em] hover:text-orange-600 transition-colors duration-300">
                            {{ words.modal_power_btn_no }}
                        </button>
                    </div>
                </div>
            </div>
        </Dialog>
    </AppLayout>
</template>

<style scoped>
/* Fondo Proexplo Dark */
.bg-proexplo-dark {
    background: radial-gradient(circle at top right, #ffffff 0%, #ffffff 100%);
}

.banner-proexplo-early {
    background: linear-gradient(135deg, rgba(249, 115, 22, 0.15) 0%, rgba(0, 0, 0, 0.4) 100%);
    border: 1px solid rgba(249, 115, 22, 0.3);
    border-left: 6px solid #f97316;
    border-radius: 24px;
    backdrop-filter: blur(12px);
}

:deep(.p-steppanel) {
    display: flex;
    flex-direction: column;
    height: 100%;
    position: relative;
}

:deep(.p-steppanel-content) {
    flex: 1;
}

.sticky {
    position: -webkit-sticky;
    position: sticky;
    bottom: -2px;
    background-color: rgba(255, 255, 255, 0.98);
    z-index: 40;
    margin-top: auto;
}

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
    :deep(.p-steppanel) {
        padding-bottom: 80px !important;
    }
}

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

.shine-effect {
    background: linear-gradient(to right,
            rgba(255, 255, 255, 0) 0%,
            rgba(255, 255, 255, 0.05) 50%,
            rgba(255, 255, 255, 0) 100%);
    transform: skewX(-25deg);
    animation: shineLoop 3s infinite;
}

@keyframes shineLoop {
    0% { transform: translateX(-150%) skewX(-25deg); }
    100% { transform: translateX(150%) skewX(-25deg); }
}

.group-hover\:animate-shine-fast {
    animation: shineFast 0.6s forwards;
}

@keyframes shineFast {
    0% { transform: translateX(-100%) skewX(-25deg); }
    100% { transform: translateX(100%) skewX(-25deg); }
}

.animate-bounce-slow {
    animation: bounceSlow 3s infinite;
}

@keyframes bounceSlow {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.mobile-floating-validate {
    display: none;
    position: fixed;
    bottom: 1.5rem;
    right: 1.5rem;
    z-index: 50;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in-up {
    animation: fadeInUp 0.4s ease-out forwards;
}

@media (max-width: 768px) {
    .mobile-floating-validate { display: block; }
}

@media (max-width: 480px) {
    .mobile-floating-validate {
        bottom: 1rem !important;
        right: 1rem !important;
    }
}

.mobile-floating-register {
    display: none;
    position: fixed;
    bottom: 1.5rem;
    right: 1.5rem;
    z-index: 50;
}

@media (max-width: 768px) {
    .mobile-floating-register { display: block; }
}

@media (max-width: 480px) {
    .mobile-floating-register {
        bottom: 1rem !important;
        right: 1rem !important;
    }
}
</style>
