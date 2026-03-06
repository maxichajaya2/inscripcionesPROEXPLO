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

</script>

<template>
    <AppLayout title="Confirmación de Registro" class="bg-proexplo-dark">
        <div class="min-h-screen flex flex-col items-center py-12 px-4 sm:px-6 lg:px-8 font-sans">
            <div class="w-full max-w-2xl bg-white shadow-2xl rounded-2xl overflow-hidden border-t-8 border-orange-500">

                <div class="p-8 text-center bg-orange-50/50">
                    <div
                        class="inline-flex items-center justify-center w-16 h-16 bg-green-100 text-green-600 rounded-full mb-4">
                        <i class="pi pi-check-circle text-4xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-slate-800">¡Pago Confirmado!</h2>
                    <p class="text-slate-600 font-medium">Su registro se ha realizado con éxito.</p>
                </div>

                <div class="p-8 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-left">

                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-orange-600 uppercase tracking-widest mb-1">Participante</span>
                            <span class="text-lg text-slate-800 font-semibold">{{ persona.nombre_completo }}</span>
                        </div>

                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-orange-600 uppercase tracking-widest mb-1">
                                {{ documento_persona.nombre || 'Documento' }}
                            </span>
                            <span class="text-lg text-slate-800 font-semibold">{{ persona.documento }}</span>
                        </div>

                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-orange-600 uppercase tracking-widest mb-1">Número de Pedido</span>
                            <span class="text-lg text-slate-800 font-semibold">{{ pago.num_orden }}</span>
                        </div>

                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-orange-600 uppercase tracking-widest mb-1">Código de Autorización</span>
                            <span class="text-lg text-slate-800 font-semibold">{{ pago.detalle }}</span>
                        </div>

                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-orange-600 uppercase tracking-widest mb-1">Fecha y Hora</span>
                            <span class="text-lg text-slate-800 font-semibold">{{ pago.fecha }} {{ pago.hora }}</span>
                        </div>

                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-orange-600 uppercase tracking-widest mb-1">Moneda</span>
                            <span class="text-lg text-slate-800 font-semibold">USD (Dólares Americanos)</span>
                        </div>

                        <div class="flex flex-col md:col-span-2">
                            <span class="text-xs font-bold text-orange-600 uppercase tracking-widest mb-1">Categoría</span>
                            <span class="text-lg text-slate-800 font-bold italic">{{ categoria ? categoria.nombre_es : 'Categoría no especificada' }}</span>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 pt-6">
                        <div class="flex flex-col md:col-span-2 bg-slate-50 p-4 rounded-xl border border-slate-200">
                            <span class="text-sm font-bold text-slate-500 uppercase">Total de la Transacción</span>
                            <span class="text-3xl font-black text-orange-600">USD {{ facturacion.total }}</span>
                        </div>

                        <div class="mt-10 text-center">
                            <Button label="Finalizar y Salir" icon="pi pi-check" @click="goStart"
                                class="p-button-rounded px-8 py-3 shadow-lg"
                                style="background: linear-gradient(to right, #f97316, #ea580c); border: none;" />
                        </div>
                    </div>

                    <div class="mt-8 p-8 border-t border-slate-100">
                        <div class="flex flex-col md:flex-row gap-8 md:gap-0">

                            <div class="flex-1 md:pr-8 text-left">
                                <p class="text-sm text-slate-500 font-bold mb-3 uppercase tracking-tighter">
                                    Consultas sobre Inscripciones:
                                </p>
                                <div class="space-y-2">
                                    <a href="mailto:inscripciones.wmc@iimp.org.pe"
                                        class="flex items-center gap-2 text-slate-600 hover:text-orange-600 transition-colors">
                                        <i class="pi pi-envelope text-lg"></i>
                                        <span class="text-sm font-bold">inscripciones.wmc@iimp.org.pe</span>
                                    </a>
                                    <a href="https://wa.me/51951294314" target="_blank"
                                        class="flex items-center gap-2 text-green-600 hover:text-green-700 transition-colors">
                                        <i class="pi pi-whatsapp text-lg font-bold"></i>
                                        <span class="text-sm font-bold">+51 951 294 314 (Helen Loaiza)</span>
                                    </a>
                                </div>
                            </div>

                            <div class="hidden md:block w-px bg-slate-200"></div>

                            <div class="flex-1 md:pl-8 text-left">
                                <p class="text-sm text-slate-500 font-bold mb-3 uppercase tracking-tighter">
                                    Alojamiento (Tarifas Preferenciales):
                                </p>
                                <div class="space-y-2">
                                    <a href="mailto:reservas@iimp.org.pe"
                                        class="flex items-center gap-2 text-slate-600 hover:text-orange-600 transition-colors">
                                        <i class="pi pi-envelope text-lg"></i>
                                        <span class="text-sm font-bold">reservas@iimp.org.pe</span>
                                    </a>
                                    <a href="https://wa.me/51942797524" target="_blank"
                                        class="flex items-center gap-2 text-green-600 hover:text-green-700 transition-colors">
                                        <i class="pi pi-whatsapp text-lg font-bold"></i>
                                        <span class="text-sm font-bold">+51 942 797 254 (Melisa Ramos)</span>
                                    </a>
                                </div>
                            </div>

                        </div>
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

.bg-proexplo-dark {
    background: #f8fafc; /* Color de fondo suave para resaltar la tarjeta */
}
</style>
