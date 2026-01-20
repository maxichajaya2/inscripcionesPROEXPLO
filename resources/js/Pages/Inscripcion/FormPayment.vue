<script setup>
import { ref, onMounted, computed, watch, nextTick } from 'vue';
import Card from 'primevue/card';

const props = defineProps({
    categoria_seleccionada: Object,
    data_persona: Object,
    formulario: Object
});

const mountNiubiz = async (data) => {
    // 1. EXTRAER LA CONFIGURACIÓN CORRECTA
    // Laravel a veces envía { status: true, formulario: { ... } }
    let config = data;
    if (data.formulario) config = data.formulario;

    // Si no hay script, no podemos hacer nada
    if (!config || !config.script) {
        console.error("Configuración de Niubiz no encontrada en el prop 'formulario'");
        return;
    }

    // 2. ESPERAR A QUE EL DOM SE RENDERICE
    await nextTick();

    const form_holder = document.getElementById('form_holder');
    if (form_holder) {
        form_holder.innerHTML = ""; // Limpiar

        const form = document.createElement("form");
        form.action = config.form.action;
        form.method = "POST";

        const script = document.createElement("script");
        script.src = config.script.src;

        // Dataset requerido por Niubiz
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

        form.appendChild(script);
        form_holder.appendChild(form);
        console.log("Botón de Niubiz inyectado correctamente");
    } else {
        console.error("No se encontró el elemento #form_holder");
    }
};

// Si el componente se monta y ya tenemos la data, ejecutar
onMounted(() => {
    if (props.formulario) mountNiubiz(props.formulario);
});

// Si la data llega tarde (por el delay de la petición axios), vigilamos el cambio
watch(() => props.formulario, (newVal) => {
    if (newVal) mountNiubiz(newVal);
}, { deep: true });

// Helper para mostrar el monto en el texto del template
const scriptData = computed(() => {
    if (!props.formulario) return null;
    return props.formulario.formulario ? props.formulario.formulario.script : props.formulario.script;
});
</script>

<template>
    <div id="FormPaymentFinish" class="w-full">
        <div class="flex flex-col items-center p-6 w-full">
            <div class="text-blue-900 font-bold text-center text-2xl mb-6 tracking-wide uppercase">
                Payment Process
            </div>

            <Card class="w-full max-w-md shadow-2xl border-t-4 border-blue-600 rounded-xl bg-white">
                <template #content>
                    <div v-if="formulario">
                        <div class="mb-4 border-b pb-6 p-4">
                            <div class="flex justify-between items-center py-3 border-b border-gray-100">
                                <span class="font-bold text-gray-500 uppercase text-xs tracking-wider">Participant</span>
                                <span class="text-gray-800 font-semibold text-right">
                                    {{ data_persona?.nombres }} {{ data_persona?.apellido_paterno }}
                                </span>
                            </div>

                            <div class="flex justify-between items-center py-3 border-b border-gray-100">
                                <span class="font-bold text-gray-500 uppercase text-xs tracking-wider">Category</span>
                                <span class="text-blue-600 font-bold text-right">
                                    {{ categoria_seleccionada?.nombre_en }}
                                </span>
                            </div>

                            <div class="flex justify-between items-center py-4 mt-4 bg-blue-50 px-3 rounded-lg border border-blue-100">
                                <span class="font-bold text-blue-800">Total Amount</span>
                                <span class="font-bold text-blue-900 text-2xl">
                                    USD {{ scriptData?.amount }}
                                </span>
                            </div>
                        </div>

                        <div id="form_holder" class="flex justify-center p-4 min-h-[100px] border-2 border-dashed border-gray-100 rounded-lg">
                            <div class="flex flex-col items-center text-gray-400">
                                <i class="pi pi-spin pi-spinner mb-2"></i>
                                <span class="text-sm">Cargando pasarela...</span>
                            </div>
                        </div>

                        <p class="text-[10px] text-center text-gray-400 mt-4 px-4 uppercase">
                            Secure payment by Niubiz
                        </p>
                    </div>

                    <div v-else class="p-10 text-center">
                        <i class="pi pi-spin pi-spinner text-3xl text-blue-600"></i>
                        <p class="mt-2 text-gray-500 font-bold">Waiting for payment data...</p>
                    </div>
                </template>
            </Card>
        </div>
    </div>
</template>
