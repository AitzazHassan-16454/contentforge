<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import VueApexCharts from 'vue3-apexcharts';

defineOptions({
    components: { apexchart: VueApexCharts },
});

const props = defineProps({
    labels: {
        type: Array,
        required: true,
    },
    values: {
        type: Array,
        required: true,
    },
    name: {
        type: String,
        default: 'Views',
    },
    color: {
        type: String,
        default: '#6366F1',
    },
    height: {
        type: Number,
        default: 320,
    },
});

const isDark = ref(document.documentElement.classList.contains('dark'));

let observer;

onMounted(() => {
    observer = new MutationObserver(() => {
        isDark.value = document.documentElement.classList.contains('dark');
    });

    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
});

onBeforeUnmount(() => observer?.disconnect());

const series = computed(() => [{ name: props.name, data: props.values }]);

const shortDate = (iso) =>
    new Date(`${iso}T00:00:00`).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });

const fullDate = (iso) =>
    new Date(`${iso}T00:00:00`).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

const tooltipTheme = computed(() => ({
    background: isDark.value ? '#18181b' : '#ffffff',
    color: isDark.value ? '#e4e4e7' : '#18181b',
    border: isDark.value ? '#27272a' : '#e4e4e7',
    muted: isDark.value ? '#a1a1aa' : '#71717a',
}));

const tickEvery = computed(() => Math.max(1, Math.ceil(props.labels.length / 6)));

const options = computed(() => ({
    chart: {
        type: 'area',
        toolbar: { show: false },
        fontFamily: "'Inter', sans-serif",
        background: 'transparent',
    },
    colors: [props.color],
    theme: { mode: isDark.value ? 'dark' : 'light' },
    stroke: { curve: 'smooth', width: 3 },
    fill: {
        type: 'gradient',
        gradient: { shadeIntensity: 1, opacityFrom: 0.3, opacityTo: 0.05 },
    },
    dataLabels: { enabled: false },
    xaxis: {
        categories: props.labels,
        axisBorder: { show: false },
        axisTicks: { show: false },
        labels: {
            rotate: 0,
            offsetY: 4,
            style: {
                fontSize: '11px',
                fontWeight: 600,
                colors: isDark.value ? '#a1a1aa' : '#71717a',
            },
            formatter: (value) => {
                const index = props.labels.indexOf(value);
                return index === -1 || index % tickEvery.value !== 0 ? '' : shortDate(value);
            },
        },
    },
    yaxis: {
        labels: { style: { fontSize: '11px' } },
        forceNiceScale: true,
    },
    grid: { strokeDashArray: 4, padding: { left: 8, right: 8 } },
    tooltip: {
        custom: ({ series, seriesIndex, dataPointIndex }) => {
            const iso = props.labels[dataPointIndex];
            const value = series[seriesIndex][dataPointIndex];
            const t = tooltipTheme.value;

            return `
                <div style="background:${t.background};color:${t.color};border:1px solid ${t.border};border-radius:10px;padding:8px 12px;font-family:'Inter',sans-serif;font-size:12px;box-shadow:0 8px 24px rgba(0,0,0,0.12);">
                    <div style="font-weight:700;">${fullDate(iso)}</div>
                    <div style="margin-top:4px;">${value.toLocaleString()} <span style="opacity:.6;">${props.name.toLowerCase()}</span></div>
                </div>
            `;
        },
        style: { fontSize: '12px' },
    },
    legend: { show: false },
}));
</script>

<template>
    <apexchart
        type="area"
        :height="height"
        :series="series"
        :options="options"
    />
</template>
