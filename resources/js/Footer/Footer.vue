<script setup>
// Importamos la imagen directamente
import LogoIIMPRender from '@/Components/LogoIIMPRender.vue';
</script>

<template>
    <footer class="bg-white text-slate-900 py-12 mt-20 border-t-4 border-[#b8904d]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 items-start">

                <div class="space-y-6">
                    <div class="flex flex-col space-y-4">
                        <LogoIIMPRender class="block w-auto" />
                        <div class="border-l-2 border-[#b8904d] pl-4">
                            <span class="block text-[10px] uppercase tracking-tighter text-gray-500">Organizado por:</span>
                            <span class="text-xs font-bold text-slate-900">IIMP - Instituto de Ingenieros de Minas del
                                Perú</span>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-xs font-bold mb-5 uppercase text-[#b8904d] tracking-widest flex items-center">
                        <i class="pi pi-shield mr-2"></i> Información Legal
                    </h3>

                    <div class="space-y-2">
                        <a href="/documents/reglamento.pdf" target="_blank"
                            class="flex items-center justify-between bg-gray-50 p-2 rounded border border-gray-200 shadow-sm hover:bg-gray-100 transition-all duration-300 group">
                            <div class="flex items-center space-x-2">
                                <i
                                    class="pi pi-file-pdf text-gray-400 group-hover:text-[#b8904d] transition-colors text-[11px]"></i>
                                <span
                                    class="text-[9px] text-gray-600 uppercase tracking-widest font-medium group-hover:text-slate-900 transition-colors">
                                    Términos y Condiciones
                                </span>
                            </div>
                            <i
                                class="pi pi-external-link text-[7px] text-gray-400 group-hover:text-[#b8904d] transition-colors"></i>
                        </a>

                        <!-- <a href="/documents/politicas.pdf" target="_blank"
                            class="flex items-center justify-between bg-gray-50 p-2 rounded border border-gray-200 shadow-sm hover:bg-gray-100 transition-all duration-300 group">
                            <div class="flex items-center space-x-2">
                                <i
                                    class="pi pi-list text-gray-400 group-hover:text-[#b8904d] transition-colors text-[11px]"></i>
                                <span
                                    class="text-[9px] text-gray-600 uppercase tracking-widest font-medium group-hover:text-slate-900 transition-colors">
                                    Políticas de Registro
                                </span>
                            </div>
                            <i
                                class="pi pi-external-link text-[7px] text-gray-400 group-hover:text-[#b8904d] transition-colors"></i>
                        </a> -->

                        <!-- <a href="/documents/privacy_policy.pdf" target="_blank"
                            class="flex items-center justify-between bg-gray-50 p-2 rounded border border-gray-200 shadow-sm hover:bg-gray-100 transition-all duration-300 group">
                            <div class="flex items-center space-x-2">
                                <i
                                    class="pi pi-verified text-gray-400 group-hover:text-[#b8904d] transition-colors text-[11px]"></i>
                                <span
                                    class="text-[9px] text-gray-600 uppercase tracking-widest font-medium group-hover:text-slate-900 transition-colors">
                                    Privacidad y Politica
                                </span>
                            </div>
                            <i
                                class="pi pi-external-link text-[7px] text-gray-400 group-hover:text-[#b8904d] transition-colors"></i>
                        </a> -->
                    </div>
                </div>

                <div>
                    <h3 class="text-xs font-bold mb-5 uppercase text-[#b8904d] tracking-widest">Seguridad de Plataforma</h3>
                    <div class="space-y-4">
                        <a href="https://aws.amazon.com/security/" target="_blank" rel="noopener noreferrer"
                            class="flex items-center space-x-3 bg-gray-50 p-3 rounded border border-gray-200 shadow-sm hover:bg-gray-100 transition-all duration-300 group cursor-pointer w-full">

                            <img src="https://upload.wikimedia.org/wikipedia/commons/9/93/Amazon_Web_Services_Logo.svg"
                                alt="AWS" class="h-4 opacity-80 group-hover:opacity-100 transition-opacity">

                            <div class="border-l border-gray-300 pl-3">
                                <span
                                    class="block text-[9px] font-bold text-slate-800 leading-none group-hover:text-blue-600 transition-colors">AWS
                                    CLOUD</span>
                                <span
                                    class="text-[8px] text-gray-500 uppercase tracking-tighter font-medium flex items-center">
                                    Secured Server <i class="pi pi-external-link ml-1 text-[6px] opacity-70"></i>
                                </span>
                            </div>
                        </a>
                        <a href="https://www.cloudflare.com/learning/ssl/what-is-ssl/" target="_blank"
                            rel="noopener noreferrer"
                            class="flex items-center space-x-3 bg-gray-50 p-3 rounded border border-gray-200 shadow-sm hover:bg-gray-100 transition-all duration-300 group cursor-pointer w-full">

                            <i
                                class="pi pi-lock text-green-600 text-xl ml-1 group-hover:scale-110 transition-transform duration-300"></i>

                            <div class="border-l border-gray-300 pl-3">
                                <span class="block text-[9px] font-bold text-green-700 leading-none uppercase">Data
                                    Encrypted</span>
                                <span
                                    class="text-[8px] text-gray-500 uppercase tracking-tighter font-medium flex items-center mt-1">
                                    AES-256 SSL <i class="pi pi-external-link ml-1 text-[6px] opacity-70"></i>
                                </span>
                            </div>
                        </a>
                    </div>
                </div>

                <div>
                    <h3 class="text-xs font-bold mb-5 uppercase text-[#b8904d] tracking-widest">Pagos Seguros</h3>
                    <a href="https://www.pcisecuritystandards.org/about_us/" target="_blank" rel="noopener noreferrer"
                        class="block group">
                        <div
                            class="flex items-center space-x-3 bg-gray-50 p-2 rounded border border-gray-200 hover:bg-gray-100 transition-colors duration-300">
                            <img src="https://i0.wp.com/akitra.com/wp-content/uploads/2023/03/PCI-DSS-large.png?fit=800%2C800&ssl=1"
                                alt="PCI Compliant"
                                class="h-9 bg-white p-1 rounded shadow-sm group-hover:shadow-md transition-shadow">
                            <div class="leading-none">
                                <span class="block text-[8px] font-bold text-gray-500 uppercase">Certificado</span>
                                <span
                                    class="text-[9px] text-slate-900 font-medium uppercase tracking-tighter group-hover:text-blue-600 transition-colors">
                                    PCI-DSS Compliant
                                </span>
                                <span class="block text-[7px] text-[#b8904d] mt-1 italic flex items-center">
                                    Verify Standard <i class="pi pi-external-link ml-1 text-[6px]"></i>
                                </span>
                            </div>
                        </div>
                    </a>
                </div>

            </div>
            <div class="mt-6 pt-6 border-t border-gray-100 text-center space-y-4 pb-2">
                <div class="text-[13px] text-gray-500 flex flex-col items-center justify-center space-y-1">
                    <p>
                        &copy; {{ new Date().getFullYear() }} <strong class="text-slate-800">XV Congreso Internacional de Prospectores y Exploradores</strong>. Todos los derechos reservados.
                    </p>
                    <!-- <p class="text-[14px] text-gray-400">
                        Organized by Instituto de Ingenieros de Minas del Perú (IIMP)
                    </p> -->
                </div>
            </div>
        </div>
    </footer>
</template>
