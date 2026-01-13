<script setup>
import LogoIIMPRender from '@/Components/LogoIIMPRender.vue';
import 'primeicons/primeicons.css'
</script>

<template>
    <footer class="bg-white py-4 pt-10 mt-10">
        <div class="flex px-3 mx-auto max-w-7xl md:px-6 lg:px-8">
            <div class="flex-col items-center shrink-0">
                <p>Organizado por:</p>
                <LogoIIMPRender class="block w-auto" />
            </div>

            <div class="container mx-auto text-center space-x-6 text-2xl items-end" style="align-self: end;">
                <a href="https://www.instagram.com/iimp_oficial/" target ='_blank'>
                    <i class="pi pi-instagram"></i>
                </a>
                <a href="https://www.tiktok.com/@iimpoficial" target ='_blank'>
                    <i class="pi pi-tiktok"></i>
                </a>
                <a href="https://x.com/i/flow/login?redirect_after_login=%2FIIMPOficial" target ='_blank'>
                    <i class="pi pi-twitter"></i>
                </a>
                <a href="https://www.youtube.com/user/IIMPOficial" target ='_blank'>
                    <i class="pi pi-youtube"></i>
                </a>
                <a href="https://www.facebook.com/IIMPOficial?_rdc=1&_rdr" target ='_blank'>
                    <i class="pi pi-facebook"></i>
                </a>
                <a href="https://www.linkedin.com/company/instituto-de-ingenieros-de-minas-del-per%C3%BA" target ='_blank'>
                    <i class="pi pi-linkedin"></i>
                </a>
            </div>

            <div class="container mx-auto max-w-[20%]">
            </div>
        </div>
        <!-- <div style="position: fixed; z-index: 10;  right: 2rem;   bottom: 2rem;">
            <a href="https://api.whatsapp.com/send/?phone=16395272389&amp;text&amp;app_absent=0" target="_blank" rel="noopener noreferrer">
                <img alt="Contactar por Whatsapp" title="Contactar por Whatsapp" loading="lazy" style="color:transparent; width:70px;" src="https://perumin.com/perumin37/public/uploads/shares/imagenes/wsp.png">
            </a>
        </div> -->
    </footer>
</template>
