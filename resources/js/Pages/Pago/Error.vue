<!-- <script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import colorbar from '@/Components/colorbar.vue';
import Button from 'primevue/button';
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';
import '../../../css/inscripciones.css';


const props = defineProps({
    pago: Object
  })

// Función para regresar inteligentemente
const retry = () => {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        // Si no hay historia, forzamos ir a la ruta de inscripción
        router.visit(route('inscripcion.index')); // Ajusta si tu ruta se llama distinto
    }
};
const goStart = () => router.get(route('inscripcion.index'));
</script>

<template>
    <AppLayout title="Payment Error" class="bg-gradient-wmc">
         <form id="FormPaymentError" >
        <div class="min-h-screen flex flex-col items-center py-12 px-4 ">

            <div class="w-full max-w-2xl  shadow-2xl rounded-2xl overflow-hidden border-t-8 border-red-600">

                <div class="p-8 text-center bg-red-50 border-b border-red-100">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-red-100 text-red-600 rounded-full mb-4">
                        <i class="pi pi-exclamation-triangle text-5xl"></i>
                    </div>
                    <h2 class="text-2xl font-black text-red-900 uppercase">Payment Declined</h2>
                </div>

                <div class="p-8 space-y-6 bg-white">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-red-500 uppercase tracking-widest">Error Code</span>
                            <span class="text-lg font-mono font-bold">#{{ props.pago.estado }}</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-red-500 uppercase tracking-widest">Amount</span>
                            <span class="text-lg font-bold">USD {{ props.pago.monto }}</span>
                        </div>
                    </div>

                    <div class="p-5 bg-red-50 rounded-xl border-l-4 border-red-500">
                        <span class="text-xs text-red-400 uppercase font-bold">Reason:</span>
                        <p class="text-red-900 font-bold text-lg mt-1 leading-tight">
                            {{ props.pago.detalle }}
                        </p>
                    </div>

                    <p class="text-sm text-gray-500 italic text-center">
                        Please verify your card details or try a different payment method.
                    </p>
                </div>

                <div class="p-8 bg-gray-50 flex flex-col sm:flex-row justify-center gap-4 border-t">
                    <Button label="Try Again" icon="pi pi-refresh" @click="retry" class="p-button-danger p-button-rounded px-8" />
                    <Button label="Go to Home" icon="pi pi-home" @click="goStart" class="p-button-secondary p-button-outlined p-button-rounded px-8" />
                </div>
            </div>
        </div>
        </form>
    </AppLayout>
</template>
 -->

 <script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import colorbar from '@/Components/colorbar.vue';
import Button from 'primevue/button';
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';
import '../../../css/inscripciones.css';

const props = defineProps({
    pago: Object
})

// Función para regresar inteligentemente
const retry = () => {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        router.visit(route('inscripcion.index'));
    }
};
const goStart = () => router.get(route('inscripcion.index'));
</script>

<template>
    <AppLayout title="Payment Error" class="bg-gradient-wmc">
        <form id="FormPaymentError" @submit.prevent>
            <div class="min-h-screen flex flex-col items-center py-12 px-4 ">

                <div class="w-full max-w-2xl shadow-2xl rounded-2xl overflow-hidden border-t-8 border-red-600 bg-white">

                    <div class="p-8 text-center bg-red-50 border-b border-red-100">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-red-100 text-red-600 rounded-full mb-4">
                            <i class="pi pi-exclamation-triangle text-5xl"></i>
                        </div>
                        <h2 class="text-2xl font-black text-red-900 uppercase">Payment Declined</h2>
                        <p class="text-red-700 font-medium">The transaction could not be processed.</p>
                    </div>

                    <div class="p-8 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-red-500 uppercase tracking-widest mb-1">Order Number</span>
                                <span class="text-lg text-gray-800 font-mono font-bold">{{ props.pago.purchasenumber }}</span>
                            </div>

                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-red-500 uppercase tracking-widest mb-1">Action Code</span>
                                <span class="text-lg text-gray-800 font-mono font-bold">{{ props.pago.estado }}</span>
                            </div>

                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-red-500 uppercase tracking-widest mb-1">Date and Time</span>
                                <span class="text-lg text-gray-800 font-semibold">{{ props.pago.fecha_pago }}</span>
                            </div>

                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-red-500 uppercase tracking-widest mb-1">Amount</span>
                                <span class="text-lg text-gray-800 font-bold">USD {{ props.pago.monto }}</span>
                            </div>
                        </div>

                        <div class="p-5 bg-red-50 rounded-xl border-l-4 border-red-500">
                            <span class="text-xs text-red-500 uppercase font-bold tracking-tighter">Response Message:</span>
                            <p class="text-red-900 font-bold text-lg mt-1 leading-tight">
                                {{ props.pago.detalle }}
                            </p>
                        </div>

                        <p class="text-sm text-gray-500 italic text-center">
                            Please verify your card details, ensure you have sufficient funds, or try a different payment method.
                        </p>
                    </div>

                    <div class="p-8 bg-gray-50 flex flex-col sm:flex-row justify-center gap-4 border-t">
                        <Button label="Try Again" icon="pi pi-refresh" @click="retry" class="p-button-danger p-button-rounded px-8 shadow-md" />
                        <Button label="Go to Home" icon="pi pi-home" @click="goStart" class="p-button-secondary p-button-outlined p-button-rounded px-8" />
                    </div>
                </div>
            </div>
        </form>
    </AppLayout>
</template>
