<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import colorbar from '@/Components/colorbar.vue';
import Button from 'primevue/button';
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';
import '../../../css/inscripciones.css';

// --- CAMBIO 1: Eliminamos el import que causaba el error ---

const props = defineProps({
    pago: Object // { action_code: '113', detalle: '...', monto: '...' }
});

// Diccionario de errores
const errorDictionary = {
    // Generales / Visa / MC / Amex / Diners
    '01':  'Expired card. Please use another one.',
    '02':  'Operation not permitted for this card.',
    '13':  'Amount not allowed.',
    '16':  'Insufficient funds. Please check your balance.',
    '18':  'Invalid card or not registered.',
    '29':  'Card not operational (Check CVV/Security Code).',
    '80':  'Invalid transaction or card.',
    '90':  'Transaction declined. Please contact your bank.',
    '91':  'Contact issuer. Verify your online purchase limits.',
    '207': 'Card reported as lost.',
    '208': 'Card reported as lost.',
    '209': 'Card reported as stolen.',
    '401': 'Store/Terminal disabled.',
    '476': 'Transaction already in a deposit.',
    '479': 'Invalid Merchant Code.',
    '666': 'Communication problems with the bank.',
    '668': 'Communication problems with anti-fraud system.',
    '670': 'Transaction denied by possible fraud.',
    '678': 'Authentication error (3D Secure/Verified by Visa).',
    '754': 'Invalid Merchant.',
    '10':  'Failed to affiliate with REC.',

    // Casos YAPE
    'YPCHK0001': 'Yape account is inactive.',
    'YPCHK0002': 'Account is in blacklist.',
    'YPCHK0003': 'Daily limit exceeded (> 500.00).',
    'YPCHK0004': 'Account blocked by OTP attempts.',
    'YPCHK0005': 'Absence in F&F.',
    'YPCHK0006': 'Incorrect OTP (1st attempt).',
    'YPCHK0007': 'Incorrect OTP (2nd attempt).',
    'YPCHK0008': 'Incorrect OTP (3rd attempt).',
    'YPCHK0010': 'OTP not generated.',
};

const friendlyMessage = computed(() => {
    const rawCode = String(props.pago?.action_code || '').trim();
    const cleanCode = rawCode.startsWith('11') ? rawCode.substring(2) : rawCode;
    return errorDictionary[cleanCode] || errorDictionary[rawCode] || props.pago?.detalle || 'Transaction declined.';
});

// const retry = () => window.history.back();
// Función para regresar inteligentemente
const retry = () => {
    // Si el navegador tiene historia (vino del formulario), regresa atrás.
    // Al regresar, el código del PASO 1 se activará y restaurará los datos.
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
                            <span class="text-lg font-mono font-bold">#{{ props.pago?.action_code }}</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-red-500 uppercase tracking-widest">Amount</span>
                            <span class="text-lg font-bold">USD {{ props.pago?.monto }}</span>
                        </div>
                    </div>

                    <div class="p-5 bg-red-50 rounded-xl border-l-4 border-red-500">
                        <span class="text-xs text-red-400 uppercase font-bold">Reason:</span>
                        <p class="text-red-900 font-bold text-lg mt-1 leading-tight">
                            {{ friendlyMessage }}
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
    </AppLayout>
</template>
