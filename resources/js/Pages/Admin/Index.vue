<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref, onMounted } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

const props = defineProps({
    stats: Object,
    ultimosUsuarios: Array,
    ultimosCupones: Array,
    ultimasInscripciones: Array
});

// --- EFECTO DE NÚMEROS ANIMADOS ---
const useAnimatedNumber = (targetValue, duration = 1500) => {
    const displayValue = ref(0);
    onMounted(() => {
        let start = null;
        const animate = (timestamp) => {
            if (!start) start = timestamp;
            const progress = Math.min((timestamp - start) / duration, 1);
            const easeOut = 1 - Math.pow(1 - progress, 4);
            displayValue.value = easeOut * targetValue;
            if (progress < 1) window.requestAnimationFrame(animate);
            else displayValue.value = targetValue;
        };
        window.requestAnimationFrame(animate);
    });
    return displayValue;
};

// Animamos las variables principales
const animatedIngresos = useAnimatedNumber(props.stats?.ingresos_totales || 0, 2000);
const animatedPagadas = useAnimatedNumber(props.stats?.inscripciones_pagadas || 0, 1500);
const animatedUsuarios = useAnimatedNumber(props.stats?.total_usuarios || 0, 1500);

// Formateador sin decimales para que los números grandes se lean mejor
const formatMoney = (amount) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(amount || 0);
};

// --- LÓGICA DEL SEMÁFORO INTELIGENTE (Basado en $300,000) ---
const semaforoProyeccion = computed(() => {
    const estimado = props.stats?.proyeccion?.total_estimado || 0;
    const estimadoMoney = formatMoney(estimado);

    if (estimado >= 300000) {
        return {
            bg: 'bg-emerald-500', text: 'text-white', shadow: 'shadow-[0_0_20px_rgba(16,185,129,0.5)]',
            icono: '🚀', titulo: '¡VAMOS MUY BIEEEEN!',
            mensaje: `Al ritmo actual, llegaremos a la meta y la superaremos con ${estimadoMoney} en ingresos.`
        };
    } else if (estimado >= 240000) {
        return {
            bg: 'bg-amber-400', text: 'text-slate-900', shadow: 'shadow-[0_0_20px_rgba(251,191,36,0.5)]',
            icono: '⚡', titulo: '¡ESTAMOS CERCA!',
            mensaje: `El pronóstico es de ${estimadoMoney}. ¡Falta un empujón más de publicidad!`
        };
    } else {
        return {
            bg: 'bg-rose-500', text: 'text-white', shadow: 'shadow-[0_0_20px_rgba(244,63,94,0.5)]',
            icono: '🚨', titulo: '¡ALERTA DE INGRESOS!',
            mensaje: `Estamos lejos. La proyección es de solo ${estimadoMoney}. ¡Hay que cambiar la estrategia!`
        };
    }
});

// --- GRÁFICA 1: RITMO DE INGRESOS (SÚPER LLAMATIVA) ---
const proyeccionSeries = computed(() => [
    { name: 'Ingresos Reales', data: props.stats?.proyeccion?.reales || [] },
    { name: 'Tendencia (Proyección)', data: props.stats?.proyeccion?.proyectada || [] },
    { name: 'Meta Ideal ($300,000)', data: props.stats?.proyeccion?.ideal || [] }
]);

const proyeccionOptions = computed(() => ({
    chart: {
        type: 'line', fontFamily: 'inherit', toolbar: { show: false },
        animations: { enabled: true, easing: 'easeinout', speed: 800 },
        dropShadow: { enabled: true, color: '#000', top: 8, left: 0, blur: 5, opacity: 0.1 }
    },
    stroke: {
        curve: 'smooth',
        width: [5, 4, 3],
        dashArray: [0, 5, 5]
    },
    colors: ['#10b981', '#f59e0b', '#94a3b8'],
    xaxis: {
        categories: props.stats?.proyeccion?.fechas || [],
        labels: { style: { fontWeight: 600, colors: '#64748b' } },
        tooltip: { enabled: false }, tickAmount: 10
    },
    yaxis: {
        labels: {
            style: { colors: '#64748b', fontWeight: 900 },
            formatter: (value) => {
                if (value >= 1000) return '$' + (value / 1000).toFixed(0) + 'k';
                return formatMoney(value);
            }
        },
        max: 320000
    },
    dataLabels: {
        enabled: true,
        enabledOnSeries: [0],
        offsetY: -5,
        formatter: (value) => {
            // AQUI ESTÁ LA MAGIA: Si el valor es nulo (fechas del futuro), devolvemos texto vacío para no dibujar globos de $0.
            if (value === null || value === undefined) return '';
            return formatMoney(value);
        },
        style: { fontSize: '11px', fontWeight: 'bold', colors: ['#047857'] },
        background: { enabled: true, foreColor: '#fff', padding: 4, borderRadius: 4, borderWidth: 1, borderColor: '#10b981' }
    },
    grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
    legend: { position: 'top', horizontalAlign: 'right', fontWeight: 900, fontSize: '13px' },
    markers: { size: [5, 0, 0], colors: ['#fff'], strokeColors: '#10b981', strokeWidth: 3, hover: { size: 8 } }
}));

// --- GRÁFICA 2: IMPACTO DE CUPONES (DONUT) ---
const cuponesSeries = computed(() => [ Number(props.stats?.pagos_con_cupon || 0), Number(props.stats?.pagos_sin_cupon || 0) ]);
const cuponesChartOptions = {
    chart: { type: 'donut', fontFamily: 'inherit' }, labels: ['Con Cupón', 'Precio Full'], colors: ['#6366f1', '#cbd5e1'],
    plotOptions: { pie: { donut: { size: '75%', labels: { show: true, name: { show: true }, value: { show: true, fontSize: '24px', fontWeight: 900 }, total: { show: true, showAlways: true, label: 'Ventas Totales' } } } } },
    dataLabels: { enabled: false }, stroke: { width: 0 }, legend: { position: 'bottom', markers: { radius: 12 } }
};

// --- GRÁFICA 3: USO DE TARJETAS (BARRAS) ---
const tarjetasSeries = computed(() => [{ name: 'Transacciones', data: Object.values(props.stats?.uso_tarjetas || {}) }]);
const tarjetasChartOptions = computed(() => ({
    chart: { type: 'bar', fontFamily: 'inherit', toolbar: { show: false } },
    xaxis: { categories: Object.keys(props.stats?.uso_tarjetas || {}), labels: { style: { fontWeight: 600 } } },
    colors: ['#10b981'], plotOptions: { bar: { borderRadius: 6, horizontal: false, columnWidth: '45%' } },
    dataLabels: { enabled: true, style: { colors: ['#fff'] } }, grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
}));
</script>

<template>
    <Head title="Panel de Control | WMC 2026" />

    <AuthenticatedLayout>
        <div class="space-y-8 max-w-7xl mx-auto pb-12">

            <div class="animate-[fadeIn_0.5s_ease-out]">
                <h2 class="font-black text-3xl text-slate-800 tracking-tight">Panel de Inteligencia Financiera</h2>
                <p class="text-sm text-slate-500 mt-1">Métricas de negocio basadas en transacciones reales (Niubiz).</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="relative bg-gradient-to-br from-emerald-400 to-teal-600 rounded-3xl p-6 text-white flex flex-col justify-between overflow-hidden group hover:-translate-y-2 hover:scale-[1.02] transition-all duration-300 shadow-[0_10px_25px_rgba(16,185,129,0.4)] cursor-pointer z-10">
                    <div class="absolute -right-10 -top-10 w-32 h-32 bg-white opacity-10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
                    <div class="flex items-center gap-3 mb-4 relative z-10">
                        <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center group-hover:rotate-12 transition-transform">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-emerald-50">Ingresos Reales</p>
                    </div>
                    <div class="relative z-10">
                        <p class="text-4xl font-black tracking-tight drop-shadow-md">{{ formatMoney(animatedIngresos) }}</p>
                    </div>
                </div>

                <div class="bg-white rounded-3xl p-6 border border-slate-100 flex flex-col justify-between group hover:-translate-y-2 transition-all duration-300 shadow-sm hover:shadow-[0_10px_25px_rgba(59,130,246,0.15)] cursor-pointer">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Tickets Pagados</p>
                    </div>
                    <p class="text-4xl font-black text-slate-800 tracking-tight">{{ Math.floor(animatedPagadas) }}</p>
                </div>

                <div class="bg-white rounded-3xl p-6 border border-slate-100 flex flex-col justify-between group hover:-translate-y-2 transition-all duration-300 shadow-sm hover:shadow-[0_10px_25px_rgba(99,102,241,0.15)] cursor-pointer">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
                        </div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Usaron Cupón</p>
                    </div>
                    <p class="text-4xl font-black text-slate-800 tracking-tight">{{ stats.pagos_con_cupon }}</p>
                </div>

                <div class="bg-white rounded-3xl p-6 border border-slate-100 flex flex-col justify-between group hover:-translate-y-2 transition-all duration-300 shadow-sm hover:shadow-[0_10px_25px_rgba(148,163,184,0.2)] cursor-pointer">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 rounded-xl bg-slate-50 text-slate-600 flex items-center justify-center group-hover:bg-slate-800 group-hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        </div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Cuentas</p>
                    </div>
                    <p class="text-4xl font-black text-slate-800 tracking-tight">{{ Math.floor(animatedUsuarios) }}</p>
                </div>
            </div>

            <div class="bg-white rounded-3xl p-6 border border-slate-100 hover:shadow-lg transition-shadow duration-300">
                <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-xl font-black text-slate-800 tracking-tight">Ritmo de Ingresos vs Meta</h3>
                        <p class="text-xs font-bold text-slate-500 mt-1 uppercase tracking-widest">Meta Final: $300,000</p>
                    </div>

                    <div v-if="stats?.proyeccion?.total_estimado"
                         class="px-6 py-3 rounded-2xl transition-all duration-500 transform hover:scale-105"
                         :class="[semaforoProyeccion.bg, semaforoProyeccion.text, semaforoProyeccion.shadow]">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xl">{{ semaforoProyeccion.icono }}</span>
                            <span class="font-black uppercase tracking-widest text-sm drop-shadow-md">
                                {{ semaforoProyeccion.titulo }}
                            </span>
                        </div>
                        <p class="text-sm font-medium opacity-90">{{ semaforoProyeccion.mensaje }}</p>
                    </div>
                </div>

                <div class="w-full mt-4">
                    <VueApexCharts type="line" height="350" :options="proyeccionOptions" :series="proyeccionSeries" />
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-3xl p-6 border border-slate-100 flex flex-col hover:shadow-lg transition-shadow duration-300">
                    <div class="mb-4 text-center">
                        <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest">Impacto de Cupones</h3>
                    </div>
                    <div class="flex-1 flex items-center justify-center min-h-[250px]">
                        <VueApexCharts type="donut" width="100%" :options="cuponesChartOptions" :series="cuponesSeries" />
                    </div>
                </div>

                <div class="bg-white rounded-3xl p-6 border border-slate-100 flex flex-col hover:shadow-lg transition-shadow duration-300">
                    <div class="mb-4 text-center">
                        <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest">Tarjetas Más Usadas</h3>
                    </div>
                    <div class="flex-1 flex items-end min-h-[250px]">
                        <VueApexCharts type="bar" width="100%" height="250" :options="tarjetasChartOptions" :series="tarjetasSeries" />
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden flex flex-col">
                    <div class="p-6 border-b border-slate-50 flex justify-between items-center bg-slate-50/50">
                        <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest">Últimos Usuarios</h3>
                        <Link :href="route('usuarios.index')" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition-colors">Ver todos &rarr;</Link>
                    </div>
                    <div class="overflow-x-auto p-2">
                        <table class="min-w-full divide-y divide-slate-50">
                            <tbody class="divide-y divide-slate-50 text-slate-700">
                                <tr v-for="user in ultimosUsuarios" :key="user.id" class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold uppercase text-xs">
                                                {{ user.name.charAt(0) }}
                                            </div>
                                            <div>
                                                <p class="font-bold text-xs text-slate-800">{{ user.name }}</p>
                                                <p class="text-[10px] text-slate-500">{{ user.email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!ultimosUsuarios?.length">
                                    <td class="px-4 py-8 text-center text-xs text-slate-500">No hay usuarios registrados.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden flex flex-col">
                    <div class="p-6 border-b border-slate-50 flex justify-between items-center bg-slate-50/50">
                        <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest">Cupones Recientes</h3>
                        <Link :href="route('cupones.index')" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition-colors">Ver todos &rarr;</Link>
                    </div>
                    <div class="overflow-x-auto p-2">
                        <table class="min-w-full divide-y divide-slate-50">
                            <tbody class="divide-y divide-slate-50 text-slate-700">
                                <tr v-for="cupon in ultimosCupones" :key="cupon.id" class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="flex flex-col gap-0.5">
                                            <span class="font-black text-xs text-slate-800 uppercase">{{ cupon.codigo_cupon }}</span>
                                            <span class="text-[10px] text-slate-500 truncate max-w-[150px]">{{ cupon.razon_social || 'Global' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        <span class="font-black text-indigo-600 text-sm">
                                            {{ cupon.tipo_descuento === 'porcentaje' ? cupon.valor + '%' : '$' + cupon.valor }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="!ultimosCupones?.length">
                                    <td colspan="2" class="px-4 py-8 text-center text-xs text-slate-500">No hay cupones creados.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>

<style>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
