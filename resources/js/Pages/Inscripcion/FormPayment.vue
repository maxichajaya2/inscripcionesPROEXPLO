<script setup>
import { ref, onMounted, computed, watch, nextTick } from 'vue';
import Card from 'primevue/card';
import { usePage } from '@inertiajs/vue3'; // Agregado para traducciones

const page = usePage();
const words = page.props.language.words; // Extraemos el diccionario

const props = defineProps({
    categoria_seleccionada: Object,
    data_persona: Object,
    formulario: Object,
    vouchers: Object,
    datos_facturacion: Object,
    extras_seleccionados: {
        type: Array,
        default: () => []
    }
});

const urlParams = new URLSearchParams(window.location.search);
const esSeccionViajes = computed(() => urlParams.get('section') === 'viajes');
const termsAccepted = ref(false);
const procesandoPago = ref(false);
const confirmacionComprobante = ref(false);

const esFactura = computed(() => {
    // 1. Prioridad absoluta al selector del formulario anterior
    const tipoDoc = props.datos_facturacion?.tipoDocumentoEmpresa;
    if (tipoDoc === 2) return true;
    if (tipoDoc === 1) return false;

    // 2. Backup por longitud
    const documento = props.datos_facturacion?.tipoDocumentoEmpresa?.toString().trim() || "";
    return documento.length === 11;
});

// Ahora usamos las variables del diccionario
const tipoComprobanteTexto = computed(() => {
    return esFactura.value ? words.lbl_invoice : words.lbl_receipt;
});

const formularioValido = computed(() => {
    return termsAccepted.value && confirmacionComprobante.value;
});

const precioInscripcion = computed(() => {
    return props.categoria_seleccionada?.precio_disponible?.valor || '0.00';
});

const montoDescuento = computed(() => {
    if (props.descuento !== undefined && props.descuento !== null) {
        return props.descuento;
    }
    return props.formulario?.descuento || 0;
});

const mountNiubiz = async (data) => {
    let config = data;
    if (data?.formulario) config = data.formulario;
    if (!config || !config.script) return;

    await nextTick();

    const form_holder = document.getElementById('form_holder');
    if (form_holder) {
        window.VisanetCheckout = undefined;
        window.v_checkout = undefined;
        delete window.VisanetCheckout;

        form_holder.innerHTML = "";
        const existingScripts = document.querySelectorAll('script[src*="checkout.js"]');
        existingScripts.forEach(s => s.remove());

        const residuals = document.querySelectorAll('.v-modal, .niubiz-visible, #visa_checkout, #visa_ads, .main-checkout');
        residuals.forEach(r => r.remove());

        setTimeout(() => {
            const form = document.createElement("form");
            form.action = config.form.action;
            form.method = "POST";
            form.id = "niubiz_form";

            const script = document.createElement("script");
            script.src = config.script.src + "?ts=" + new Date().getTime();

            // Usamos la traducción para el botón "Pagar USD" / "Pay USD"
            const textoBoton = `${words.btn_pay_usd} ` + config.script.amount;

            script.setAttribute('data-buttontext', textoBoton);
            script.setAttribute('data-untokenized-buttontext', textoBoton);
            script.dataset.buttontext = textoBoton;

            script.dataset.formbuttoncolor = "#1d4ed8";
            script.dataset.sessiontoken = config.script.sessiontoken;
            script.dataset.channel = config.script.channel;
            script.dataset.merchantid = config.script.merchantid;
            script.dataset.merchantlogo = config.script.merchantlogo;
            script.dataset.amount = config.script.amount;
            script.dataset.purchasenumber = config.script.purchasenumber;
            script.dataset.cardholdername = config.script.cardholdername;
            script.dataset.cardholderlastname = config.script.cardholderlastname;
            script.dataset.cardholderemail = config.script.cardholderemail;
            script.dataset.expirationminutes = config.script.expirationminutes;
            script.dataset.timeouturl = config.script.timeouturl;
            script.dataset.canvas = "form_holder";

            const detectorBotones = setInterval(() => {
                const btnNiubiz = document.querySelector('.v-button') ||
                    document.querySelector('#form_holder button') ||
                    document.querySelector('#niubiz_form button');

                if (btnNiubiz) {
                    btnNiubiz.addEventListener('click', () => {
                        setTimeout(() => {
                            procesandoPago.value = true;
                        }, 300);
                    });
                    clearInterval(detectorBotones);
                }
            }, 1000);

            form.appendChild(script);
            form_holder.appendChild(form);
        }, 200);
    }
};

onMounted(() => {
    if (props.formulario) {
        mountNiubiz(props.formulario);
    }
});

watch(() => props.formulario, (newVal) => {
    if (newVal) mountNiubiz(newVal);
}, { deep: true, immediate: true });

const scriptData = computed(() => {
    if (!props.formulario) return null;
    return props.formulario.formulario ? props.formulario.formulario.script : props.formulario.script;
});
</script>

<template>
    <div id="FormPaymentFinish" class="w-full">
        <div class="flex flex-col items-center p-6 w-full">
            <div class="text-blue-900 font-bold text-center text-2xl mb-6 tracking-wide uppercase">
                {{ words.lbl_finish_registration }}
            </div>

            <Card class="w-full max-w-md shadow-2xl border-t-4 border-orange-600 rounded-xl bg-white overflow-hidden">
                <template #content>
                    <div v-if="formulario">
                        <div class="mb-4 border-b pb-6 p-4">
                            <div class="flex justify-between items-center py-3 border-b border-gray-100">
                                <span class="font-bold text-gray-500 uppercase text-xs tracking-wider">{{
                                    words.lbl_participant }}</span>
                                <span class="text-gray-800 font-semibold text-right">
                                    {{ data_persona?.nombres }} {{ data_persona?.apellido_paterno }}
                                </span>
                            </div>

                            <div class="flex justify-between items-center py-3 border-b border-gray-100">
                                <span class="font-bold text-gray-500 uppercase text-xs tracking-wider">{{
                                    words.lbl_category }}</span>
                                <span class="text-blue-600 font-bold text-right">
                                    {{ (categoria_seleccionada && categoria_seleccionada.nombre_en) ?
                                        categoria_seleccionada.nombre_en : (categoria_seleccionada &&
                                            categoria_seleccionada.nombre ? categoria_seleccionada.nombre :
                                            words.lbl_proexplo_delegate)
                                    }}
                                </span>
                            </div>

                            <div v-if="!esSeccionViajes"
                                class="flex justify-between items-start mb-1 text-sm pl-2 border-l-2 border-gray-100 p-1 ">
                                <div class="flex flex-col w-2/3">
                                    <span class="text-blue-600 font-medium leading-tight">
                                        {{ words.lbl_registration }}
                                    </span>
                                </div>
                                <span class="text-gray-600 font-medium text-right w-1/3">
                                    USD {{ precioInscripcion || '0.00' }}
                                </span>
                            </div>

                            <div v-if="montoDescuento > 0"
                                class="flex justify-between items-start mb-1 text-sm pl-2 border-l-2 border-green-500 p-2 bg-green-50">
                                <div class="flex flex-col w-2/3">
                                    <span class="text-green-700 font-bold italic">{{ words.lbl_coupon_discount }}</span>
                                </div>
                                <span class="text-green-700 font-bold text-right w-1/3">
                                    - USD {{ montoDescuento }}
                                </span>
                            </div>

                            <div v-for="extra in extras_seleccionados" :key="extra.id"
                                class="flex justify-between items-start mb-1 text-sm pl-2 border-l-2 border-gray-100 p-1 ">
                                <div class="flex flex-col w-2/3">
                                    <span class="text-blue-600 font-medium leading-tight">
                                        {{ extra.titulo || extra.nombre_es }}
                                    </span>
                                    <span class="text-[10px] text-gray-400 uppercase">
                                        {{ extra.tipo === 'viaje' ? words.lbl_technical_visit : words.lbl_short_course
                                        }}
                                    </span>
                                </div>
                                <span class="text-gray-600 font-medium text-right w-1/3">
                                    USD {{ extra.precio_disponible?.valor || '0.00' }}
                                </span>
                            </div>

                            <div
                                class="flex justify-between items-center py-4 mt-4 bg-blue-50 px-3 rounded-lg border border-blue-100">
                                <span class="font-bold text-blue-800">{{ words.lbl_total_to_pay }}</span>
                                <span class="font-bold text-blue-900 text-2xl">
                                    USD {{ scriptData?.amount }}
                                </span>
                            </div>
                        </div>

                        <!-- <div class="px-4 mb-6 text-align-center">
                            <div class="flex items-start gap-3 p-3 bg-orange-50 border border-orange-200 rounded-lg">
                                <input type="checkbox" id="check_terms" v-model="termsAccepted"
                                    class="mt-1 w-5 h-5 cursor-pointer accent-blue-600" />
                                <label for="check_terms"
                                    class="text-xs text-gray-700 leading-tight cursor-pointer select-none">
                                    {{ words.lbl_i_accept_the }}
                                    <a :href="words.doc_privacy_policies" target="_blank"
                                        class="text-blue-700 font-bold underline">
                                        {{ words.lbl_privacy_policies }}
                                    </a>
                                    {{ words.lbl_and_the }}
                                    <a :href="words.doc_participation_rules" target="_blank"
                                        class="text-blue-700 font-bold underline">
                                        {{ words.lbl_participation_rules }}
                                    </a>
                                    {{ words.lbl_of_proexplo }}
                                </label>
                            </div>
                        </div> -->

                        <div class="px-4 mb-6 text-align-center">
                            <div class="flex items-start gap-3 p-3 bg-orange-50 border border-orange-200 rounded-lg">
                                <input type="checkbox" id="check_terms" v-model="termsAccepted"
                                    class="mt-1 w-5 h-5 cursor-pointer accent-blue-600" />
                                <label for="check_terms"
                                    class="text-xs text-gray-700 leading-tight cursor-pointer select-none">
                                    {{ words.lbl_i_accept_the }}

                                    <a :href="words.doc_privacy_policies" target="_blank"
                                        class="text-blue-700 font-bold underline hover:text-blue-900 transition-colors">
                                        {{ words.lbl_privacy_policies }}
                                    </a>

                                    <template v-if="esSeccionViajes">
                                        {{ words.lbl_and_the }}
                                        <a :href="words.doc_participation_rules" target="_blank"
                                            class="text-blue-700 font-bold underline hover:text-blue-900 transition-colors">
                                            {{ words.lbl_participation_rules }}
                                        </a>
                                    </template>

                                    <template v-else>
                                        ,
                                        <a :href="words.doc_participation_rules" target="_blank"
                                            class="text-blue-700 font-bold underline hover:text-blue-900 transition-colors">
                                            {{ words.lbl_participation_rules }}
                                        </a>
                                        {{ words.lbl_and_the }}
                                        <a :href="words.doc_terms_conditions" target="_blank"
                                            class="text-blue-700 font-bold underline hover:text-blue-900 transition-colors">
                                            {{ words.footer_terms }}
                                        </a>
                                    </template>

                                    {{ words.lbl_of_proexplo }}
                                </label>
                            </div>
                        </div>

                        <div class="mb-6 p-4 rounded-xl border-2 transition-all duration-300"
                            :class="confirmacionComprobante ? 'border-green-500 bg-green-50' : 'border-orange-200 bg-orange-50'">
                            <div class="flex items-center gap-3 mb-3">
                                <i class="pi text-xl"
                                    :class="[esFactura ? 'pi-building text-purple-600' : 'pi-user text-blue-600']"></i>
                                <span class="font-black uppercase text-sm tracking-tight text-slate-800">
                                    {{ words.lbl_confirmation_of }} {{ tipoComprobanteTexto }}
                                </span>
                            </div>

                            <div
                                class="text-[11px] leading-relaxed text-slate-600 mb-4 text-justify bg-white/50 p-3 rounded-lg border border-orange-100">
                                <p v-if="esFactura">
                                    {{ words.msg_you_are_requesting }} <strong>{{ words.lbl_commercial_invoice
                                    }}</strong> {{ words.lbl_in_the_name_of }}
                                    <span class="text-purple-700 font-bold">{{ datos_facturacion?.razonSocial || 'la empresa' }}</span> {{
                                    words.lbl_with_ruc }}
                                    <span class="text-purple-700 font-bold">{{ datos_facturacion?.documentoEmpresa || ''
                                    }}</span>.
                                </p>
                                <p v-else>
                                    {{ words.msg_you_are_requesting }} <strong>{{ words.lbl_sales_receipt }}</strong> {{
                                        words.lbl_in_the_name_of }}
                                    <span class="text-blue-700 font-bold">{{ datos_facturacion?.razonSocial || ''
                                    }}</span>.
                                </p>
                                <p class="mt-2 text-red-600 font-semibold italic">
                                    {{ words.msg_check_data_invoice }}
                                </p>
                            </div>

                            <div class="flex items-start gap-3">
                                <input type="checkbox" id="check_comprobante" v-model="confirmacionComprobante"
                                    class="mt-1 w-5 h-5 cursor-pointer accent-green-600" />
                                <label for="check_comprobante"
                                    class="text-[11px] text-slate-700 font-bold cursor-pointer select-none">
                                    {{ words.msg_confirm_billing_data }}
                                </label>
                            </div>
                        </div>

                        <div class="relative w-full">
                            <div v-if="!formularioValido" class="absolute inset-0 z-20 cursor-not-allowed bg-white/10"
                                :title="words.msg_must_accept_terms"></div>
                            <div id="form_holder"
                                class="flex justify-center p-4 min-h-[100px] border-2 rounded-lg overflow-hidden transition-all duration-500"
                                :class="formularioValido ? 'border-blue-500 bg-white shadow-md' : 'border-gray-200 bg-gray-50'"
                                :style="{
                                    opacity: formularioValido ? '1' : '0.3',
                                    filter: formularioValido ? 'grayscale(0)' : 'grayscale(1)',
                                    pointerEvents: formularioValido ? 'auto' : 'none'
                                }">
                            </div>
                        </div>

                        <p class="text-[9px] text-center text-gray-400 mt-6 uppercase tracking-widest">
                            {{ words.lbl_secure_payment_gateway }}
                        </p>
                    </div>

                    <div v-else class="p-10 text-center">
                        <i class="pi pi-spin pi-spinner text-3xl text-blue-600"></i>
                        <p class="mt-2 text-gray-500 font-bold">{{ words.msg_obtaining_session }}</p>
                    </div>
                </template>
            </Card>
        </div>
    </div>

    <Dialog v-model:visible="procesandoPago" modal :showHeader="false" :closable="false" :baseZIndex="99999"
        class="bg-slate-900/80 backdrop-blur-sm border-none shadow-none m-0 p-0"
        :style="{ width: '100vw', height: '100vh' }" :pt="{
            root: { style: 'z-index: 99999 !important;' },
            mask: { style: 'z-index: 99998 !important; background: rgba(0,0,0,0.85);' }
        }">
        <div class="flex flex-col items-center justify-center">
            <div class="relative w-24 h-24 mb-6">
                <div class="absolute inset-0 border-4 border-blue-500/20 rounded-full"></div>
                <div class="absolute inset-0 border-4 border-t-blue-500 rounded-full animate-spin"></div>
                <i
                    class="pi pi-lock absolute inset-0 flex items-center justify-center text-blue-400 text-2xl animate-pulse"></i>
            </div>

            <h3 class="text-2xl font-black text-white uppercase tracking-widest animate-pulse">
                {{ words.msg_processing_payment }}
            </h3>
            <p class="text-blue-300 text-sm mt-2 font-medium">
                {{ words.msg_do_not_close }}
            </p>

            <div class="w-64 h-1 bg-white/10 rounded-full mt-6 overflow-hidden">
                <div class="h-full bg-gradient-to-r from-blue-600 to-blue-400 animate-infinite-scroll"></div>
            </div>
        </div>
    </Dialog>
</template>

<style scoped>
.pi-lock {
    text-shadow: 0 0 15px rgba(96, 165, 250, 0.8);
    animation: lock-glow 1.5s ease-in-out infinite;
}

@keyframes infiniteScroll {
    0% {
        transform: translateX(-100%);
    }

    100% {
        transform: translateX(100%);
    }
}

.animate-infinite-scroll {
    width: 100%;
    animation: infiniteScroll 2s linear infinite;
}
</style>
