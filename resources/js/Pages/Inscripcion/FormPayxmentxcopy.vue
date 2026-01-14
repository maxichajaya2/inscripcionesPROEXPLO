<script setup>
import { onMounted, ref } from 'vue';
import Card from 'primevue/card';
import { useToast } from "primevue/usetoast";
import { usePage } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    categoria_seleccionada: Object,
    data_persona: Object,
    formulario: Object
});

const niubizErrorMessages = {
    "101": "Your card is expired. Please check the date or use another card.",
    "102": "Transaction not permitted for this card. Contact your bank.",
    "113": "Amount not allowed. Please verify the transaction limits.",
    "116": "Insufficient funds. Please check your balance.",
    "118": "Invalid card number. Please verify the digits entered.",
    "129": "Card not operational. Please check your CVV.",
    "180": "Invalid card. Please use a different payment method.",
    "190": "Invalid transaction. Please contact your card issuer.",
    "191": "Transaction declined. Please contact your bank.",
    "default": "Transaction declined. Please verify your card details."
};

const toast = useToast();
const loading = ref(false);
const errorMessage = ref(null);

const cargarScriptNiubiz = () => {
    if (!props.formulario) return;
    const scriptUrl = props.formulario.action || 'https://static-content-qas.vnforapps.com/env/sandbox/js/checkout.js';
    if (!document.querySelector(`script[src="${scriptUrl}"]`)) {
        const script = document.createElement('script');
        script.src = scriptUrl;
        script.async = true;
        document.body.appendChild(script);
    }
};

onMounted(() => {
    cargarScriptNiubiz();

    window.addEventListener('message', (event) => {
        // Seguridad: Solo mensajes de nuestro propio dominio
        if (event.origin !== window.location.origin) return;

        const { type, url, pago } = event.data;

        if (type === 'NIUBIZ_SUCCESS') {
            loading.value = false;
            window.location.href = url;
        }

        if (type === 'NIUBIZ_ERROR') {
            loading.value = false;

            // OPCIÓN 1: Ir a la página de error que ya tienes creada (Pago/Error.vue)
            // Esto es lo que pide el curso para mostrar el detalle completo
            router.visit('/pago/error', {
                method: 'get',
                data: { pago: pago }
            });

            // OPCIÓN 2 (Si quieres que se quede en la misma página):
            /*
            errorMessage.value = pago.detalle;
            toast.add({
                severity: 'error',
                summary: 'Pago Fallido',
                detail: pago.detalle,
                life: 5000
            });
            */
        }
    });
});

function pagarConNiubiz() {
    errorMessage.value = null;
    if (!window.VisanetCheckout) return;

    const data = props.formulario;
    const idFacturacion = data.quotaId;
    const numOrden = data.purchaseNumber;

    window.VisanetCheckout.configure({
        sessiontoken: data.token,
        channel: data.channel || 'web',
        merchantid: data.merchantId,
        purchasenumber: String(numOrden),
        amount: String(data.amount),
        expirationminutes: '20',
        timeouturl: 'about:blank',
        merchantlogo: 'https://papers.wmc2026.org/logo-wmc.png',
        formbuttoncolor: '#005bea',
        action: `/pago/getform/niubiz/${idFacturacion}/${numOrden}`,
        complete: function (params) {
            alert(JSON.stringify(params));
        }
    });

    window.VisanetCheckout.open();

    // 🎯 REFUERZO EXTREMO:
    const observer = new MutationObserver(() => {
        const niubizForm = document.querySelector('form[name="v_form"]');
        if (niubizForm) {
            niubizForm.setAttribute('target', 'niubiz_target');
            observer.disconnect();
        }
    });
    observer.observe(document.body, { childList: true, subtree: true });

}


</script>

<template>
    <div class="flex flex-col items-center justify-center w-full p-6 bg-gray-50 min-h-[50vh]">
        <div class="text-blue-900 font-bold text-center text-2xl mb-6 tracking-wide">
            Payment Process
        </div>

        <div v-if="errorMessage" class="w-full max-w-md mb-6 animate-fade-in">
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-md" role="alert">
                <p class="font-bold flex items-center">
                    <i class="pi pi-times-circle mr-2 text-xl"></i> Payment Declined
                </p>
                <p class="text-sm mt-1">{{ errorMessage }}</p>
            </div>
        </div>

        <Card class="w-full max-w-md shadow-2xl border-t-4 border-blue-600 rounded-xl overflow-hidden">
            <template #content>
                <div class="mb-8 border-b pb-6 bg-white p-4">
                    <div class="flex justify-between items-center py-3 border-b border-gray-100">
                        <span class="font-bold text-gray-500 uppercase text-xs tracking-wider">Participant</span>
                        <span class="text-gray-800 font-semibold text-right">
                            {{ formulario?.name || '---' }} {{ formulario?.lastname || '' }}
                        </span>
                    </div>
                     <div class="flex justify-between items-center py-3 border-b border-gray-100">
                        <span class="font-bold text-gray-500 uppercase text-xs tracking-wider">Category</span>
                        <span class="text-blue-600 font-bold text-right">
                            {{ categoria_seleccionada?.nombre_es || 'General' }}
                        </span>
                    </div>
                    <div
                        class="flex justify-between items-center py-4 mt-2 bg-blue-50/50 px-3 rounded-lg border border-blue-100">
                        <span class="font-bold text-blue-800">Total Amount</span>
                        <span class="font-bold text-blue-900 text-2xl">
                            USD {{ formulario?.amount || '0.00' }}
                        </span>
                    </div>
                </div>

                <div class="flex justify-center px-6 pb-6">
                    <button @click="pagarConNiubiz" :disabled="loading || !formulario"
                        class="relative w-full group bg-gradient-to-r from-blue-700 to-cyan-500 text-white font-bold py-4 rounded-full shadow-lg transition-all disabled:opacity-50">
                        <div class="relative flex items-center justify-center gap-2">
                            <span v-if="!loading">PAY</span>
                            <span v-else>PROCESSING...</span>
                            <i v-if="!loading" class="pi pi-credit-card"></i>
                        </div>
                    </button>
                </div>
            </template>
        </Card>

        <iframe name="niubiz_target" id="niubiz_target" style="display:none;"></iframe>
    </div>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
