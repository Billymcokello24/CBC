<script setup lang="ts">
import { computed, ref, onMounted } from 'vue';

interface Props {
    title: string;
    chartType?: 'bar' | 'line' | 'doughnut' | 'area';
    data: {
        labels: string[];
        datasets: {
            label: string;
            data: number[];
            color?: string;
        }[];
    };
    height?: number;
    loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    chartType: 'bar',
    height: 300,
    loading: false,
});

const chartRef = ref<HTMLCanvasElement | null>(null);

// Calculate max value for scaling
const maxValue = computed(() => {
    const allValues = props.data.datasets.flatMap(d => d.data);
    return Math.max(...allValues) * 1.1;
});

// Colors for datasets
const defaultColors = [
    'rgb(59, 130, 246)', // blue
    'rgb(16, 185, 129)', // green
    'rgb(245, 158, 11)', // amber
    'rgb(239, 68, 68)',  // red
    'rgb(139, 92, 246)', // purple
    'rgb(236, 72, 153)', // pink
];

const getBarHeight = (value: number) => {
    return (value / maxValue.value) * 100;
};

const getColor = (index: number, dataset: any) => {
    return dataset.color || defaultColors[index % defaultColors.length];
};
</script>

<template>
    <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5">
        <div class="mb-8 flex items-center justify-between">
            <div class="space-y-1">
                <h3 class="text-sm font-black uppercase tracking-widest text-foreground">{{ title }}</h3>
                <p class="text-[10px] font-bold text-muted-foreground/60 uppercase tracking-tight">Analytical insights from cluster data</p>
            </div>
            <div class="flex items-center gap-4">
                <div
                    v-for="(dataset, index) in data.datasets"
                    :key="index"
                    class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest"
                >
                    <span
                        class="h-2 w-2 rounded-full"
                        :style="{ backgroundColor: getColor(index, dataset) }"
                    ></span>
                    <span class="text-muted-foreground">{{ dataset.label }}</span>
                </div>
            </div>
        </div>

        <div v-if="loading" class="flex items-end justify-between gap-2" :style="{ height: `${height}px` }">
            <div
                v-for="i in 7"
                :key="i"
                class="flex-1 animate-pulse rounded-xl bg-muted/40"
                :style="{ height: `${Math.random() * 80 + 20}%` }"
            ></div>
        </div>

        <!-- Simple Bar Chart -->
        <div v-else-if="chartType === 'bar'" class="relative" :style="{ height: `${height}px` }">
            <!-- Y-axis labels -->
            <div class="absolute left-0 top-0 bottom-8 flex flex-col justify-between text-[9px] font-black text-muted-foreground/40 uppercase">
                <span>{{ Math.round(maxValue) }}</span>
                <span>{{ Math.round(maxValue / 2) }}</span>
                <span>0</span>
            </div>

            <!-- Chart area -->
            <div class="ml-10 flex h-full items-end gap-3 border-b border-l border-border/50 pb-8">
                <div
                    v-for="(label, labelIndex) in data.labels"
                    :key="labelIndex"
                    class="flex flex-1 flex-col items-center gap-2"
                >
                    <div class="flex w-full items-end justify-center gap-1.5" :style="{ height: `${height - 40}px` }">
                        <div
                            v-for="(dataset, datasetIndex) in data.datasets"
                            :key="datasetIndex"
                            class="w-full max-w-10 rounded-t-lg transition-all duration-500 hover:opacity-80"
                            :style="{
                                height: `${getBarHeight(dataset.data[labelIndex])}%`,
                                backgroundColor: getColor(datasetIndex, dataset)
                            }"
                        ></div>
                    </div>
                </div>
            </div>
             <!-- X-axis labels inline with bars -->
             <div class="absolute bottom-0 left-10 right-0 flex justify-between px-2">
                 <span v-for="(label, i) in data.labels" :key="i" class="text-[9px] font-black text-muted-foreground/40 uppercase truncate max-w-[40px]">{{ label }}</span>
             </div>
        </div>

        <!-- Simple Area/Line Chart -->
        <div v-else-if="chartType === 'line' || chartType === 'area'" class="relative" :style="{ height: `${height}px` }">
            <svg class="w-full h-full" preserveAspectRatio="none">
                <defs>
                    <linearGradient
                        v-for="(dataset, index) in data.datasets"
                        :key="`gradient-${index}`"
                        :id="`gradient-${index}`"
                        x1="0%"
                        y1="0%"
                        x2="0%"
                        y2="100%"
                    >
                        <stop offset="0%" :style="{ stopColor: getColor(index, dataset), stopOpacity: 0.2 }" />
                        <stop offset="100%" :style="{ stopColor: getColor(index, dataset), stopOpacity: 0 }" />
                    </linearGradient>
                </defs>

                <g v-for="(dataset, datasetIndex) in data.datasets" :key="datasetIndex">
                    <!-- Area fill -->
                    <path
                        v-if="chartType === 'area'"
                        :d="(() => {
                            const points = dataset.data.map((value, i) => {
                                const x = (i / (dataset.data.length - 1)) * 100;
                                const y = 10 - (value / maxValue) * 90; // Padding from top/bottom
                                return `${x}%,${y}%`;
                            }).join(' L');
                            return `M0%,100% L${points} L100%,100% Z`;
                        })()"
                        :fill="`url(#gradient-${datasetIndex})`"
                    />

                    <!-- Line -->
                    <polyline
                        :points="dataset.data.map((value, i) => {
                            const x = (i / (dataset.data.length - 1)) * 100;
                            const y = 10 - (value / maxValue) * 90;
                            return `${x}%,${y}%`;
                        }).join(' ')"
                        fill="none"
                        :stroke="getColor(datasetIndex, dataset)"
                        stroke-width="3"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        class="drop-shadow-sm"
                    />
                </g>
            </svg>

            <!-- X-axis labels -->
            <div class="absolute bottom-0 left-0 right-0 flex justify-between text-[9px] font-black text-muted-foreground/40 uppercase px-2">
                <span v-for="(label, i) in data.labels" :key="i">{{ label }}</span>
            </div>
        </div>

        <!-- Doughnut Chart -->
        <div v-else-if="chartType === 'doughnut'" class="flex items-center justify-between gap-10" :style="{ height: `${height}px` }">
            <div class="relative flex-1 flex items-center justify-center">
                <svg width="180" height="180" viewBox="0 0 200 200">
                    <g transform="translate(100, 100)">
                        <circle cx="0" cy="0" r="75" fill="none" stroke="currentColor" stroke-width="25" class="text-muted/10" />
                        <circle
                            v-for="(value, index) in data.datasets[0]?.data || []"
                            :key="index"
                            cx="0"
                            cy="0"
                            r="75"
                            fill="none"
                            :stroke="getColor(index, {})"
                            stroke-width="25"
                            :stroke-dasharray="`${(value / data.datasets[0].data.reduce((a, b) => a + b, 0)) * 471.2} 471.2`"
                            :stroke-dashoffset="`${-data.datasets[0].data.slice(0, index).reduce((a, b) => a + (b / data.datasets[0].data.reduce((c, d) => c + d, 0)) * 471.2, 0)}`"
                            stroke-linecap="round"
                            class="transition-all duration-700 ease-out"
                        />
                    </g>
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-3xl font-black tracking-tighter text-foreground">{{ data.datasets[0]?.data.reduce((a, b) => a + b, 0) || 0 }}</span>
                    <span class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Total Units</span>
                </div>
            </div>

            <!-- Legend -->
            <div class="flex flex-col gap-3 min-w-[140px]">
                <div
                    v-for="(label, index) in data.labels"
                    :key="index"
                    class="flex items-center justify-between gap-4 p-2 rounded-xl hover:bg-muted/30 transition-colors group"
                >
                    <div class="flex items-center gap-2">
                         <span
                            class="h-2 w-2 rounded-full"
                            :style="{ backgroundColor: getColor(index, {}) }"
                        ></span>
                        <span class="text-[10px] font-black uppercase tracking-widest text-muted-foreground group-hover:text-foreground transition-colors">{{ label }}</span>
                    </div>
                    <span class="text-[11px] font-black text-foreground">{{ data.datasets[0]?.data[index] || 0 }}</span>
                </div>
            </div>
        </div>
    </div>
</template>
