<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from 'primevue/card';
import { router } from '@inertiajs/vue3'

import "../../../css/inscripciones.css";

  const props = defineProps({
    pago: Object
  })

const back = () => {
    router.visit('/', {
        onSuccess: () => {
            window.location.replace("/");
        }
    })
}

</script>

<template>
    <AppLayout title="Error Pago">
    <form id="FormPaymentError">
        <div class="flex gap-6 p-6 w-full justify-around">
            <div class ="text-green-iimp font-bold  max-w-[450px] p-4">
                <div class ="text-green-iimp font-bold text-center p-4">
                    Hubo un error en su Pago
                </div>
                <Card>
                    <template #content>
                        <div>
                            <table class="min-w-full border text-black rounded-[20px]">
                                <tbody>
                                    <tr>
                                        <td class="p-4 w-[50%] table-celd font-bold">Codigo</td>
                                        <td class="p-4 w-[50%] table-celd">{{ props.pago.estado }}</td>
                                    </tr>
                                    <tr>
                                        <td class="p-4 w-[50%] table-celd font-bold">Descripcion</td>
                                        <td class="p-4 w-[50%] table-celd">{{ props.pago.detalle }}</td>
                                    </tr>
                                     <tr>
                                        <td class="p-4 w-[50%] table-celd font-bold">Total</td>
                                        <td class="p-4 w-[50%] table-celd">USD {{ props.pago.monto }}</td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>
                    </template>
                </Card>
                <div class="flex justify-center mt-8">
                    <div class="border-green-iimp">
                        <button type="submit" class="bg-green-iimp text-white font-bold px-6 py-2 rounded shadow transition" @click="back">
                            Regresar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    </AppLayout>
</template>
