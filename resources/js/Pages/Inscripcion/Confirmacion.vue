<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import colorbar from '@/Components/colorbar.vue';
import Button from 'primevue/button';
import { router } from '@inertiajs/vue3';

import "../../../css/inscripciones.css";

const props = defineProps({
    facturacion : Object,
    pago : Object,
    persona : Object,
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
    <AppLayout title="confirmacion">
        <div class=" px-3 mx-auto max-w-7xl md:px-6 lg:px-8 font-(family-name:Roboto)">
            <div class="flex justify-around">
                <div class="max-w-[800px] w-[80%]">
                    <div id = "" class = "mt-8 mb-8">
                        <h1 class="text-3xl text-green-iimp font-bold mb-2  ">Confirmación de Pago</h1>
                        <colorbar class="block w-auto" />
                    </div>
                    <div class="mt-8 mb-8 w-full">
                        <div class="mt-8 mb-8 w-full">
                            <table class="min-w-full border text-black rounded-[20px]">
                                <tbody>
                                    <tr>
                                        <td class="p-4 w-[50%] table-celd font-bold">Inscrito</td>
                                        <td class="p-4 w-[50%] table-celd">{{ persona.nombre_completo }}</td>
                                    </tr>
                                    <tr>
                                        <td class="p-4 w-[50%] table-celd font-bold">{{ documento_persona.name_es}}</td>
                                        <td class="p-4 w-[50%] table-celd">{{ persona.documento }}</td>
                                    </tr>
                                    <tr>
                                        <td class="p-4 w-[50%] table-celd font-bold">Categoria</td>
                                        <td class="p-4 w-[50%] table-celd">{{ categoria.nombre_es}}</td>
                                    </tr>
                                    <tr>
                                        <td class="p-4 w-[50%] table-celd font-bold">{{ documento_empresa.name_es}}</td>
                                        <td class="p-4 w-[50%] table-celd">{{ facturacion.numero_doc_facturador }}</td>
                                    </tr>
                                    <tr>
                                        <td class="p-4 w-[50%] table-celd font-bold">Razon Social </td>
                                        <td class="p-4 w-[50%] table-celd">{{ facturacion.nombre_facturador }}</td>
                                    </tr>
                                    <tr>
                                        <td class="p-4 w-[50%] table-celd font-bold">Tipo Pago </td>
                                        <td class="p-4 w-[50%] table-celd">{{ tipo_pago.name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="p-4 w-[50%] table-celd font-bold">Tarjeta</td>
                                        <td class="p-4 w-[50%] table-celd">{{ pago.digitos }}</td>
                                    </tr>
                                    <tr>
                                        <td class="p-4 w-[50%] table-celd font-bold">Total</td>
                                        <td class="p-4 w-[50%] table-celd">USD {{ facturacion.total }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="flex justify-around">
                             <Button label="Volver al Inicio"   @click="goStart" class="bg-green-iimp border-rounded-full" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
