<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import colorbar from '@/Components/colorbar.vue';
import GreenArrowRight from '@/Components/GreenArrowRight.vue';
import IntroImage from '@/Components/IntroImage.vue';
import { usePage , router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import Skeleton from 'primevue/skeleton';
import Functions from '@/Functions';

import "../../../css/inscripciones.css";

const props = defineProps({
    facturacion : Object,
    inscripcion : Object,
    niubiz : Object,
    persona : Object,
})
console.log(props.inscripcion);
</script>
<template>

</template>
