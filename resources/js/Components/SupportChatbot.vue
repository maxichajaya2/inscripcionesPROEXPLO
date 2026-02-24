<template>
    <div>
        <div class="fixed bottom-4 right-4 md:bottom-6 md:right-6 z-50 flex flex-col items-end group font-sans pointer-events-none">

            <div
                class="bg-white px-4 py-2 md:px-5 md:py-3 rounded-2xl rounded-br-none shadow-lg mb-2 md:mb-3 mr-2 border border-gray-100 animate-bounce-slow pointer-events-auto hidden sm:block">
                <p class="text-[10px] md:text-sm font-extrabold text-gray-800 leading-tight">
                    Facing issues? 🛠️ <br>
                    <span class="text-green-600">Chat with us!</span>
                </p>
            </div>

            <a href="https://api.whatsapp.com/send/?phone=51987196080&text=Hello, I need assistance with WMC 2026 registration&type=phone_number&app_absent=0"
                target="_blank"
                class="relative w-20 h-20 md:w-28 md:h-28 focus:outline-none group-hover:-translate-y-2 transition-transform duration-500 ease-in-out pointer-events-auto block">

                <div
                    class="absolute inset-0 bg-green-500 opacity-0 group-hover:opacity-20 rounded-full blur-xl transition-opacity duration-500">
                </div>

                <img src="/images/wmc-bot.png" alt="WMC Support"
                    class="w-full h-full object-contain drop-shadow-xl transform group-hover:scale-110 transition-all duration-300">

                <span class="absolute bottom-2 right-2 md:right-4 w-4 h-4 md:w-5 md:h-5 bg-green-500 border-2 md:border-4 border-white rounded-full animate-pulse"></span>
            </a>
        </div>
    </div>
</template>

<script setup>
// Ya no necesitamos refs ni funciones para el modal, el link hace todo el trabajo.
</script>

<style scoped>
.animate-bounce-slow {
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-6px); }
}
</style>
