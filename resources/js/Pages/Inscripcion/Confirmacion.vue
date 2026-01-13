<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import colorbar from '@/Components/colorbar.vue';
import Button from 'primevue/button';
import { router } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import "../../../css/inscripciones.css";

const props = defineProps({
    facturacion: Object,
    pago: Object,
    persona: Object,
    categoria: Object,
    documento_persona: Object,
    documento_empresa: Object,
    tipo_doc_pago: Object,
    tipo_pago: Object,
})

const goStart = () => {
    router.get(route('inscripcion.index'));
};

onMounted(() => {
    // BORRAR LA MEMORIA AL TERMINAR CON ÉXITO
    sessionStorage.removeItem('wmc_inscripcion_data');
    sessionStorage.removeItem('wmc_inscripcion_step');
})
</script>

<template>
    <AppLayout title="Registration Confirmation" class="bg-gradient-wmc">
        <div class="min-h-screen flex flex-col items-center py-12 px-4 sm:px-6 lg:px-8 font-sans">
            <div class="w-full max-w-2xl bg-white shadow-2xl rounded-2xl overflow-hidden border-t-8 border-blue-600">

                <div class="p-8 text-center bg-blue-50/50">
                    <div
                        class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 text-blue-600 rounded-full mb-4">
                        <i class="pi pi-verified text-4xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-blue-900">Payment Confirmed!</h2>
                    <p class="text-blue-700/70 font-medium">Your participation in the event has been successfully
                        registered.</p>
                </div>

                <div class="p-8 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <div class="flex flex-col group">
                            <span
                                class="text-xs font-bold text-blue-500 uppercase tracking-widest mb-1">Participant</span>
                            <span class="text-lg text-gray-800 font-semibold">{{ persona.nombre_completo }}</span>
                        </div>

                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-blue-500 uppercase tracking-widest mb-1">{{
                                documento_persona.name_en || documento_persona.name_es }}</span>
                            <span class="text-lg text-gray-800 font-semibold">{{ persona.documento }}</span>
                        </div>

                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-blue-600 uppercase tracking-widest mb-1">Selected
                                Category</span>
                            <span class="text-xl text-blue-900 font-bold">{{ categoria.nombre_en || categoria.nombre_es
                                }}</span>
                        </div>

                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-blue-500 uppercase tracking-widest mb-1">Company Name /
                                Tax ID Name</span>
                            <span class="text-gray-800 font-semibold">{{ facturacion.nombre_facturador }}</span>
                        </div>

                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-blue-500 uppercase tracking-widest mb-1">Payment
                                Method</span>
                            <span class="text-gray-800 font-semibold">{{ tipo_pago.name }}</span>
                        </div>

                        <div v-if="pago?.digitos" class="flex flex-col">
                            <span class="text-xs font-bold text-blue-500 uppercase tracking-widest mb-1">Card
                                Used</span>
                            <span class="text-gray-800 font-semibold">•••• •••• •••• {{ pago.digitos }}</span>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 pt-6">
                        <div class="flex flex-col md:col-span-2 bg-blue-50 p-4 rounded-xl border border-blue-100">
                            <span class="text-lg font-bold text-gray-500">Transaction Total</span>
                            <span class="text-3xl font-black text-blue-900">USD {{ facturacion.total }}</span>
                        </div>
                        <div class="mt-10 text-center">
                            <Button label="Finish and Exit" icon="pi pi-check" @click="goStart"
                                class="p-button-rounded px-8 py-3 shadow-lg transform hover:scale-105 transition-all duration-200"
                                style="background: linear-gradient(to right, #1d4ed8, #2563eb); border: none;" />
                        </div>

                        <p class="mt-6 text-gray-400 text-sm italic text-center">
                            A detailed receipt has been sent to your email address.
                        </p>
                    </div>
                </div>
            </div>


        </div>
    </AppLayout>
</template>

<style scoped>
.p-button {
    font-weight: 700;
    letter-spacing: 0.05em;
}
</style>
