<script setup>
import { ref, onMounted, computed, watch, nextTick } from 'vue';
import Card from 'primevue/card';

const props = defineProps({
    categoria_seleccionada: Object,
    data_persona: Object,
    formulario: Object
});

// Controla si el usuario aceptó los términos para habilitar el botón
const termsAccepted = ref(false);

const mountNiubiz = async (data) => {
    let config = data;
    if (data?.formulario) config = data.formulario;
    if (!config || !config.script) return;

    await nextTick();

    const form_holder = document.getElementById('form_holder');
    if (form_holder) {
        // 1. LIMPIEZA EXTREMA DE OBJETOS GLOBALES
        window.VisanetCheckout = undefined;
        window.v_checkout = undefined;
        delete window.VisanetCheckout;

        form_holder.innerHTML = "";
        const existingScripts = document.querySelectorAll('script[src*="checkout.js"]');
        existingScripts.forEach(s => s.remove());

        const residuals = document.querySelectorAll('.v-modal, .niubiz-visible, #visa_checkout, #visa_ads, .main-checkout');
        residuals.forEach(r => r.remove());

        // 2. INYECCIÓN DE LA PASARELA
        setTimeout(() => {
            const form = document.createElement("form");
            form.action = config.form.action;
            form.method = "POST";
            form.id = "niubiz_form";

            const script = document.createElement("script");
            script.src = config.script.src + "?ts=" + new Date().getTime();

            // Dataset (Merchant ID 651054910 según contrato)
            script.dataset.sessiontoken = config.script.sessiontoken;
            script.dataset.channel = config.script.channel;
            script.dataset.merchantid = config.script.merchantid;
            script.dataset.merchantlogo = config.script.merchantlogo;
            script.dataset.formbuttoncolor = config.script.formbuttoncolor;
            script.dataset.amount = config.script.amount;
            script.dataset.purchasenumber = config.script.purchasenumber;
            script.dataset.cardholdername = config.script.cardholdername;
            script.dataset.cardholderlastname = config.script.cardholderlastname;
            script.dataset.cardholderemail = config.script.cardholderemail;
            script.dataset.expirationminutes = config.script.expirationminutes;
            script.dataset.timeouturl = config.script.timeouturl;

            script.dataset.canvas = "form_holder";

            form.appendChild(script);
            form_holder.appendChild(form);

            console.log("Niubiz instanciado en modo preventivo.");
        }, 200);
    }
};

onMounted(() => {
    if (props.formulario) mountNiubiz(props.formulario);
});

// Vigilamos si la data del formulario cambia para recargar la pasarela
watch(() => props.formulario, (newVal) => {
    if (newVal) mountNiubiz(newVal);
}, { deep: true, immediate: true });

// Helper para extraer datos del script de Niubiz
const scriptData = computed(() => {
    if (!props.formulario) return null;
    return props.formulario.formulario ? props.formulario.formulario.script : props.formulario.script;
});
</script>

<template>
    <div id="FormPaymentFinish" class="w-full">
        <div class="flex flex-col items-center p-6 w-full">
            <div class="text-blue-900 font-bold text-center text-2xl mb-6 tracking-wide uppercase">
                Finalize Registration
            </div>

            <Card class="w-full max-w-md shadow-2xl border-t-4 border-blue-600 rounded-xl bg-white overflow-hidden">
                <template #content>
                    <div v-if="formulario">

                        <div class="mb-4 border-b pb-6 p-4">
                            <div class="flex justify-between items-center py-3 border-b border-gray-100">
                                <span
                                    class="font-bold text-gray-500 uppercase text-xs tracking-wider">Participant</span>
                                <span class="text-gray-800 font-semibold text-right">
                                    {{ data_persona?.nombres }} {{ data_persona?.apellido_paterno }}
                                </span>
                            </div>

                            <div class="flex justify-between items-center py-3 border-b border-gray-100">
                                <span class="font-bold text-gray-500 uppercase text-xs tracking-wider">Category</span>
                                <span class="text-blue-600 font-bold text-right">
                                    {{ (categoria_seleccionada && categoria_seleccionada.nombre_en) ?
                                        categoria_seleccionada.nombre_en : (categoria_seleccionada &&
                                    categoria_seleccionada.nombre ? categoria_seleccionada.nombre : 'WMC 2026 Delegate')
                                    }}
                                </span>
                            </div>

                            <div
                                class="flex justify-between items-center py-4 mt-4 bg-blue-50 px-3 rounded-lg border border-blue-100">
                                <span class="font-bold text-blue-800">Total to Pay</span>
                                <span class="font-bold text-blue-900 text-2xl">
                                    USD {{ scriptData?.amount }}
                                </span>
                            </div>
                        </div>

                        <div class="px-4 mb-6">
                            <div class="flex items-start gap-3 p-3 bg-orange-50 border border-orange-200 rounded-lg">
                                <input type="checkbox" id="check_terms" v-model="termsAccepted"
                                    class="mt-1 w-5 h-5 cursor-pointer accent-blue-600" />
                                <label for="check_terms"
                                    class="text-xs text-gray-700 leading-tight cursor-pointer select-none">
                                    I accept the
                                    <a href="/documents/reglamento.pdf" target="_blank"
                                        class="text-blue-700 font-bold underline">Terms and Conditions</a> of WMC 2026.
                                </label>
                            </div>
                        </div>

                        <div class="relative">
                            <div v-if="!termsAccepted" class="absolute inset-0 z-10 cursor-not-allowed"
                                title="Accept terms to enable payment"></div>

                            <div id="form_holder"
                                class="flex justify-center p-4 min-h-[100px] border-2 border-blue-100 bg-blue-50/30 rounded-lg overflow-hidden transition-all duration-300"
                                :style="{ opacity: termsAccepted ? '1' : '0.4', filter: termsAccepted ? 'grayscale(0)' : 'grayscale(1)' }">
                                <div class="flex flex-col items-center text-gray-400">
                                    <i class="pi pi-spin pi-spinner mb-2"></i>
                                    <span class="text-xs">Loading secure payment button...</span>
                                </div>
                            </div>
                        </div>

                        <p class="text-[9px] text-center text-gray-400 mt-6 uppercase tracking-widest">
                            Secure payment gateway by Niubiz
                        </p>
                    </div>

                    <div v-else class="p-10 text-center">
                        <i class="pi pi-spin pi-spinner text-3xl text-blue-600"></i>
                        <p class="mt-2 text-gray-500 font-bold">Obtaining session data...</p>
                    </div>
                </template>
            </Card>
        </div>
    </div>
</template>
