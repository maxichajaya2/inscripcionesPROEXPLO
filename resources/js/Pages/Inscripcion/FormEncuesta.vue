<script setup>
import { ref , computed} from 'vue';
import GreenArrowRight from '@/Components/GreenArrowRight.vue';
import { router, usePage } from '@inertiajs/vue3';

const categorias = [
    { id: 1, nombre: 'Programa de Conferencias', url: 'https://proexplo.com.pe/es/programa-de-conferencias' },
    { id: 2, nombre: 'Cursos Cortos', url: 'https://proexplo.com.pe/es/cursos-cortos' },
    { id: 3, nombre: 'Visitas Técnicas', url: 'https://proexplo.com.pe/es/visitas-tecnicas' },
    { id: 4, nombre: 'Core Shack', url: 'https://proexplo.com.pe/es/core-shack-es' },
];


const form = ref({
    nombres: '',
    apellidos: '',
    email: '',
    telefono: '',
    mensaje: ''
});

const formularioValido = computed(() => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return (
        form.value.nombres.trim().length > 0 &&
        form.value.apellidos.trim().length > 0 &&
        emailRegex.test(form.value.email) &&
        form.value.telefono.trim().length > 0 &&
        form.value.mensaje.trim().length > 0
    );
});


const paises = [
    // Países principales para proEXPLO
    { iso: 'PE', nombre: 'Perú', codigo: '+51', bandera: '🇵🇪' },
    { iso: 'CL', nombre: 'Chile', codigo: '+56', bandera: '🇨🇱' },
    { iso: 'CA', nombre: 'Canadá', codigo: '+1', bandera: '🇨🇦' },
    { iso: 'AU', nombre: 'Australia', codigo: '+61', bandera: '🇦🇺' },
    { iso: 'US', nombre: 'Estados Unidos', codigo: '+1', bandera: '🇺🇸' },
    { iso: 'MX', nombre: 'México', codigo: '+52', bandera: '🇲🇽' },
    { iso: 'BR', nombre: 'Brasil', codigo: '+55', bandera: '🇧🇷' },
    { iso: 'AR', nombre: 'Argentina', codigo: '+54', bandera: '🇦🇷' },
    { iso: 'CO', nombre: 'Colombia', codigo: '+57', bandera: '🇨🇴' },
    { iso: 'ES', nombre: 'España', codigo: '+34', bandera: '🇪🇸' },
    { iso: 'CN', nombre: 'China', codigo: '+86', bandera: '🇨🇳' },
    { iso: 'ZA', nombre: 'Sudáfrica', codigo: '+27', bandera: '🇿🇦' },
    // Otros países conocidos
    { iso: 'BO', nombre: 'Bolivia', codigo: '+591', bandera: '🇧🇴' },
    { iso: 'EC', nombre: 'Ecuador', codigo: '+593', bandera: '🇪🇨' },
    { iso: 'DE', nombre: 'Alemania', codigo: '+49', bandera: '🇩🇪' },
    { iso: 'FR', nombre: 'Francia', codigo: '+33', bandera: '🇫🇷' },
    { iso: 'GB', nombre: 'Reino Unido', codigo: '+44', bandera: '🇬🇧' },
    { iso: 'CH', nombre: 'Suiza', codigo: '+41', bandera: '🇨🇭' },
    { iso: 'JP', nombre: 'Japón', codigo: '+81', bandera: '🇯🇵' },
    { iso: 'PA', nombre: 'Panamá', codigo: '+507', bandera: '🇵🇦' },
    { iso: 'VE', nombre: 'Venezuela', codigo: '+58', bandera: '🇻🇪' },
];

const paisSeleccionado = ref(paises[0]);

const enviarFormulario = () => {
    if (formularioValido.value) {
        // Enviamos al controlador usando Inertia
        router.post(route('contactanos.store'), {
            ...form.value,
            // Concatenamos el prefijo para que el controlador reciba el número completo
            telefono_completo: `${paisSeleccionado.value.codigo} ${form.value.telefono}`
        }, {
            onSuccess: () => {
                // Opcional: Limpiar el formulario o mostrar mensaje de éxito
                form.value = { nombres: '', apellidos: '', email: '', telefono: '', mensaje: '' };
                alert('¡Mensaje enviado con éxito!');
            },
            onError: (errors) => {
                console.error('Error al enviar:', errors);
            }
        });
    }
};

</script>

<template>
    <div class="max-w-6xl mx-auto px-6 py-12 font-sans">

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-12">
            <a v-for="cat in categorias" :key="cat.id" :href="cat.url" target="_blank"
                class="bg-pro-orange text-white py-4 px-2 rounded-xl font-bold text-sm md:text-base transition-transform hover:scale-105 shadow-md leading-tight text-center flex items-center justify-center">
                {{ cat.nombre }}
            </a>
        </div>

        <div
            class="border border-pro-orange/20 rounded-[2rem] p-8 mb-12 flex flex-col md:flex-row items-center gap-6 bg-white shadow-sm">
            <div class="p-4 bg-orange-50 rounded-2xl">
                <img src="/images/icon-fence.svg" class="w-12 h-12 opacity-60" alt="Icono">
            </div>
            <div class="text-center md:text-left">
                <h4 class="text-pro-blue font-black uppercase text-lg mb-1">Otros Participantes</h4>
                <p class="text-pro-gray text-sm md:text-base leading-relaxed">
                    Si eres auspiciador, exhibidor o prensa, comunícate con nosotros al correo
                    <a href="mailto:inscripciones@iimp.org.pe" class="text-pro-orange font-bold hover:underline">
                        inscripciones@iimp.org.pe
                    </a>
                    para orientarte sobre el proceso de inscripción.
                </p>
            </div>
        </div>

        <div class="relative overflow-hidden bg-[#1a1a1a] rounded-[2.5rem] shadow-2xl">

            <div class="absolute inset-0 z-0">
                <img src="/images/contacto.webp" class="object-cover w-full h-full opacity-30" alt="Fondo Proexplo">
                <div class="absolute top-0 right-0 w-80 h-80 bg-pro-orange/20 rounded-full blur-[120px] -z-0"></div>
            </div>

            <div class="relative z-10 p-8 md:p-16 text-white">
                <h2 class="text-3xl md:text-3xl font-black text-center mb-1 tracking-tight uppercase">
                    Contáctanos
                </h2>
                <div class="w-20 h-1.5 bg-pro-orange mx-auto mb-12 rounded-full shadow-[0_0_15px_rgba(226,122,17,0.4)]">
                </div>

                <form @submit.prevent="enviarFormulario" class="max-w-4xl mx-auto space-y-8">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="group">
                            <label
                                class="block text-[10px] uppercase font-black tracking-[0.2em] text-white/50 mb-3 ml-5">Nombres
                                *</label>
                            <input v-model="form.nombres" type="text" placeholder="Escriba sus nombres" required
                                class="w-full bg-white/95 text-slate-900 rounded-full px-7 py-4 border-none focus:ring-4 focus:ring-pro-orange/50 outline-none transition-all placeholder:text-slate-400">
                        </div>
                        <div class="group">
                            <label
                                class="block text-[10px] uppercase font-black tracking-[0.2em] text-white/50 mb-3 ml-5">Apellidos
                                *</label>
                            <input v-model="form.apellidos" type="text" placeholder="Escriba sus apellidos" required
                                class="w-full bg-white/95 text-slate-900 rounded-full px-7 py-4 border-none focus:ring-4 focus:ring-pro-orange/50 outline-none transition-all placeholder:text-slate-400">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
                        <div class="md:col-span-7">
                            <label
                                class="block text-[10px] uppercase font-black tracking-[0.2em] text-white/50 mb-3 ml-5">E-mail
                                corporativo *</label>
                            <input v-model="form.email" type="email" placeholder="ejemplo@empresa.com" required
                                class="w-full bg-white/95 text-slate-900 rounded-full px-7 py-4 border-none focus:ring-4 focus:ring-pro-orange/50 outline-none transition-all">
                        </div>
                        <div class="md:col-span-5">
                            <label
                                class="block text-[10px] uppercase font-black tracking-[0.2em] text-white/50 mb-3 ml-5">Teléfono
                                *</label>
                            <div class="flex gap-3">
                                <div
                                    class="relative bg-white/95 text-slate-900 rounded-full px-5 py-4 flex items-center gap-2 font-bold shadow-inner">
                                    <span class="text-xl">{{ paisSeleccionado.bandera }}</span>
                                    <span class="text-sm font-black">{{ paisSeleccionado.codigo }}</span>
                                    <select v-model="paisSeleccionado"
                                        class="absolute inset-0 opacity-0 cursor-pointer w-full h-full">
                                        <option v-for="pais in paises" :key="pais.iso" :value="pais">
                                            {{ pais.bandera }} {{ pais.nombre }}
                                        </option>
                                    </select>
                                </div>
                                <input v-model="form.telefono" type="tel" placeholder="Número" required
                                    class="w-full bg-white/95 text-slate-900 rounded-full px-7 py-4 border-none focus:ring-4 focus:ring-pro-orange/40 outline-none transition-all placeholder:text-slate-400 font-medium">
                            </div>
                        </div>
                    </div>

                    <div class="group">
                        <label
                            class="block text-[10px] uppercase font-black tracking-[0.2em] text-white/50 mb-3 ml-5">Mensaje
                            o Consulta *</label>
                        <textarea v-model="form.mensaje" rows="4" placeholder="¿En qué podemos ayudarle?" required
                            class="w-full bg-white/95 text-slate-900 rounded-[2rem] px-8 py-6 border-none focus:ring-4 focus:ring-pro-orange/50 outline-none transition-all resize-none"></textarea>
                    </div>

                    <div class="flex justify-center md:justify-start pt-6">
                        <button type="submit" :disabled="!formularioValido"
                            :class="!formularioValido ? 'opacity-40 cursor-not-allowed scale-95' : 'hover:scale-105 shadow-[0_10px_30px_rgba(226,122,17,0.3)]'"
                            class="relative overflow-hidden group bg-pro-orange text-white font-black py-5 px-16 rounded-full transition-all duration-500 uppercase tracking-[0.15em] text-sm">
                            <span class="relative z-10">Enviar formulario</span>
                            <div v-if="formularioValido"
                                class="absolute inset-0 bg-gradient-to-b from-pro-orange to-pro-red opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                            </div>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</template>
