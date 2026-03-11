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
    <div class="rounded-xl border bg-card p-6">
        <div class="mb-6 flex items-center justify-between">
            <h3 class="text-lg font-semibold">{{ title }}</h3>
            <div class="flex items-center gap-4">
                <div
                    v-for="(dataset, index) in data.datasets"
                    :key="index"
                    class="flex items-center gap-2 text-sm"
                >
                    <span
                        class="h-3 w-3 rounded-full"
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
                class="flex-1 animate-pulse rounded-t bg-muted"
                :style="{ height: `${Math.random() * 80 + 20}%` }"
            ></div>
        </div>

        <!-- Simple Bar Chart -->
        <div v-else-if="chartType === 'bar'" class="relative" :style="{ height: `${height}px` }">
            <!-- Y-axis labels -->
            <div class="absolute left-0 top-0 bottom-8 flex flex-col justify-between text-xs text-muted-foreground">
                <span>{{ Math.round(maxValue) }}</span>
                <span>{{ Math.round(maxValue / 2) }}</span>
                <span>0</span>
            </div>

            <!-- Chart area -->
            <div class="ml-10 flex h-full items-end gap-2 border-b border-l border-border pb-8">
                <div
                    v-for="(label, labelIndex) in data.labels"
                    :key="labelIndex"
                    class="flex flex-1 flex-col items-center gap-1"
                >
                    <div class="flex w-full items-end justify-center gap-1" :style="{ height: `${height - 40}px` }">
                        <div
                            v-for="(dataset, datasetIndex) in data.datasets"
                            :key="datasetIndex"
                            class="w-full max-w-8 rounded-t transition-all duration-300 hover:opacity-80"
                            :style="{
                                height: `${getBarHeight(dataset.data[labelIndex])}%`,
                                backgroundColor: getColor(datasetIndex, dataset)
                            }"
                            :title="`${dataset.label}: ${dataset.data[labelIndex]}`"
                        ></div>
                    </div>
                    <span class="text-xs text-muted-foreground truncate max-w-full">{{ label }}</span>
                </div>
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
                        <stop offset="0%" :style="{ stopColor: getColor(index, dataset), stopOpacity: 0.3 }" />
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
                                const y = 100 - (value / maxValue) * 100;
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
                            const y = 100 - (value / maxValue) * 100;
                            return `${x}%,${y}%`;
                        }).join(' ')"
                        fill="none"
                        :stroke="getColor(datasetIndex, dataset)"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />

                    <!-- Data points -->
                    <circle
                        v-for="(value, i) in dataset.data"
                        :key="i"
                        :cx="`${(i / (dataset.data.length - 1)) * 100}%`"
                        :cy="`${100 - (value / maxValue) * 100}%`"
                        r="4"
                        :fill="getColor(datasetIndex, dataset)"
                        class="cursor-pointer transition-all hover:r-6"
                    >
                        <title>{{ dataset.label }}: {{ value }}</title>
                    </circle>
                </g>
            </svg>

            <!-- X-axis labels -->
            <div class="absolute bottom-0 left-0 right-0 flex justify-between text-xs text-muted-foreground px-2">
                <span v-for="(label, i) in data.labels" :key="i">{{ label }}</span>
            </div>
        </div>

        <!-- Doughnut Chart -->
        <div v-else-if="chartType === 'doughnut'" class="flex items-center justify-center" :style="{ height: `${height}px` }">
            <div class="relative">
                <svg width="200" height="200" viewBox="0 0 200 200">
                    <g transform="translate(100, 100)">
                        <circle cx="0" cy="0" r="70" fill="none" stroke="currentColor" stroke-width="30" class="text-muted/20" />
                        <circle
                            v-for="(value, index) in data.datasets[0]?.data || []"
                            :key="index"
                            cx="0"
                            cy="0"
                            r="70"
                            fill="none"
                            :stroke="getColor(index, {})"
                            stroke-width="30"
                            :stroke-dasharray="`${(value / data.datasets[0].data.reduce((a, b) => a + b, 0)) * 440} 440`"
                            :stroke-dashoffset="`${-data.datasets[0].data.slice(0, index).reduce((a, b) => a + (b / data.datasets[0].data.reduce((c, d) => c + d, 0)) * 440, 0)}`"
                            stroke-linecap="round"
                            class="transition-all duration-300"
                        />
                    </g>
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-3xl font-bold">{{ data.datasets[0]?.data.reduce((a, b) => a + b, 0) || 0 }}</span>
                    <span class="text-sm text-muted-foreground">Total</span>
                </div>
            </div>

            <!-- Legend -->
            <div class="ml-8 space-y-2">
                <div
                    v-for="(label, index) in data.labels"
                    :key="index"
                    class="flex items-center gap-2"
                >
                    <span
                        class="h-3 w-3 rounded-full"
                        :style="{ backgroundColor: getColor(index, {}) }"
                    ></span>
                    <span class="text-sm">{{ label }}</span>
                    <span class="text-sm font-medium ml-auto">{{ data.datasets[0]?.data[index] || 0 }}</span>
                </div>
            </div>
        </div>
    </div>
</template>
