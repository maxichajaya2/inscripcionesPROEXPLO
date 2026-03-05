<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { onMounted } from 'vue'

const props = defineProps({
    sessionToken: String,
    amount: String,
    purchaseNumber: [String, Number],
    merchantId: String
})

onMounted(() => {
    // Cargamos el script de Niubiz tal como lo hace el curso
    if (!document.getElementById('niubiz-sdk')) {
        const script = document.createElement('script');
        script.id = 'niubiz-sdk';
        script.src = 'https://static-content-qas.vnforapps.com/env/sandbox/js/checkout.js';
        script.async = true;
        document.body.appendChild(script);
    }
})



function pagar() {
    if (!window.VisanetCheckout) return;

    // Configuración exacta basada en el curso de CodersFree
    window.VisanetCheckout.configure({
        sessiontoken: props.sessionToken,
        channel: 'web',
        merchantid: props.merchantId,
        purchasenumber: String(props.purchaseNumber),
        amount: props.amount,
        expirationminutes: '20',
        timeouturl: window.location.origin,
        merchantlogo: 'https://papers.wmc2026.org/logo-wmc.png',
        formbuttoncolor: '#000000',
        action: '/niubiz-respuesta', // Ruta POST de tu Laravel
        complete: function (params) {
            console.log(params);
        }
    });

    window.VisanetCheckout.open();
}
</script>

<template>
    <AppLayout title="Pago Niubiz">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-10 text-center">
                    <h2 class="text-2xl font-bold mb-6">Finalizar Pago</h2>
                    <p class="text-4xl font-black text-green-600 mb-8">USD {{ amount }}</p>

                    <div class="flex justify-center mb-6">
                        <img src="/images/logo-proexplo.png" alt="World Mining Congress Logo"
                            class="h-20 w-auto object-contain" />
                    </div>
                    <button @click="pagar"
                        class="bg-black text-white px-10 py-4 rounded-md font-bold hover:bg-gray-800 transition">
                        Pagar con Tarjeta
                    </button>

                    <div class="mt-8 text-xs text-gray-400 text-left bg-gray-50 p-4 rounded">
                        <p>Merchant: {{ merchantId }}</p>
                        <p>Amount: {{ amount }}</p>
                        <p>Purchase: {{ purchaseNumber }}</p>
                        <p>Token: {{ sessionToken ? 'OK' : 'ERROR' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
