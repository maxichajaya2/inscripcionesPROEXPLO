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

</script>
<template>
    <AppLayout title="inscripciones">
        <div class=" px-3 mx-auto max-w-7xl md:px-6 lg:px-8 font-(family-name:Roboto)">
            <div id = "titulo_inicial" class = "mt-8 mb-8">
                <h1 class="text-3xl text-green-iimp font-bold mb-2  ">¿Qué tipo de participante eres?</h1>
                <colorbar class="block w-auto" />
            </div>
            <div class = "flex justify-around mt-6 mb-6">
                <div class = "w-[40%]">
                    <div class = "flex-col text-green-iimp text-xl font-bold">
                        <a href="/registro/convencionista" class="container-visitor-border max-w-[400px] cursor-pointer border-green-iimp">
                            <div class = "container-title-visitor">
                                <h5>Convencionista</h5>
                                <GreenArrowRight class="block w-auto" />
                            </div>
                        </a>
                        <a href="/registro/docente" class = "container-visitor-border max-w-[400px] cursor-pointer border-green-iimp" style = "display :none;">
                            <div class = "container-title-visitor">
                                <h5>Docente</h5>
                                <GreenArrowRight class="block w-auto" />
                            </div>
                        </a>
                        <a href="/registro/estudiante" class = "container-visitor-border max-w-[400px] cursor-pointer border-green-iimp" style = "display :none;">
                            <div class = "container-title-visitor">
                                <h5>Estudiante</h5>
                                <GreenArrowRight class="block w-auto" />
                            </div>
                        </a>
                        <a href="/registro/extemin" class = "container-visitor-border max-w-[400px] cursor-pointer border-green-iimp" style = "display :none;">
                            <div class = "container-title-visitor">
                                <h5>EXTEMIN</h5>
                                <GreenArrowRight class="block w-auto" />
                            </div>
                        </a>
                    </div>
                </div>
                <div class = "w-[40%]">
                    <IntroImage class="block w-auto" />
                </div>
            </div>
            <div class ="row">
                <div class = "col">
                    <div class ="flex justify-between border-green-iimp rounded-[8px] border mt-6 mb-6 p-[20px]" >
                        <div class ="flex justify-center min-w-[80px]">
                            <img src ="/images/icon-fence.svg" class="m-auto">
                        </div>
                        <div class ="w-[90%]">
                            <div class ="">
                                <h6 class="font-bold">Otros participantes</h6>
                                <span>Si eres auspiciador, exhibidor, prensa o asociado SME comunícate con nosotros al correo inscripciones@iimp.org.pe para orientarte en el proceso de inscripción.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class ="row">
                <div class = "col">
                    <div class ="section-conditions">
                        <div class ="text-conditions mb-0">
                            <h6 class="font-bold">Los inscritos en PERUMIN 37 Convención Minera tendrán los siguientes beneficios: </h6>
                            <ul class="mt-5 mb-5 ml-10 list-disc">
                                <li>Asistencia a las Conferencias de Cumbre Minera y Foro TIS (Categoría Convencionista) con interpretación simultánea (inglés-castellano)</li>
                                <li>Asistencia a las Conferencias Comerciales (sin interpretación simultánea)</li>
                                <li>Acceso a la grabación de las conferencias</li>
                                <li>Acceso a networking a través del App</li>
                                <li>Material de trabajo</li>
                                <li>Certificado digital de participación a solicitud</li>
                                <li>Acceso a las presentaciones en PDF de las conferencias autorizadas</li>
                                <li>Asistencia a EXTEMIN 2025</li>
                                <li>Participación en las actividades sociales incluidas en el programa</li>
                                <li>Recesos de Café</li>
                                <li>Reserva limitada de hoteles por parte de la organización de PERUMIN 37 Convención Minera. El pago será por cuenta del participante. En caso no encontrar disponibilidad, la búsqueda de hotel será por cuenta del participante</li>
                                <li>Traslados hotel oficial / sede / hotel oficial y a las actividades sociales, para aquellos participantes que se alojen en hotel oficial</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
