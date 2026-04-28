<script setup lang="ts">
import { computed, ref } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

interface Dataset {
    label: string;
    data: number[];
    color?: string;
}

interface ChartData {
    labels: string[];
    datasets: Dataset[];
}

interface Props {
    title: string;
    chartType: 'bar' | 'line' | 'doughnut' | 'area';
    data: ChartData;
    height?: number;
}

const props = withDefaults(defineProps<Props>(), {
    height: 300,
});

const activeTooltip = ref<{ x: number, y: number, label: string, values: { label: string, value: number, color: string }[] } | null>(null);
const containerRef = ref<HTMLElement | null>(null);

const maxVal = computed(() => {
    const allValues = props.data.datasets.flatMap(d => d.data);
    return Math.max(...allValues, 10) * 1.1; // Add 10% headroom
});

const colors = [
    'rgb(59, 130, 246)', // Blue
    'rgb(16, 185, 129)', // Emerald
    'rgb(244, 63, 94)',  // Rose
    'rgb(139, 92, 246)', // Violet
    'rgb(245, 158, 11)', // Amber
];

const getColor = (index: number, dataset: Dataset) => {
    return dataset.color || colors[index % colors.length];
};

const chartWidth = 600;
const chartHeight = 300;
const padding = { top: 30, right: 40, bottom: 40, left: 50 };

const innerWidth = chartWidth - padding.left - padding.right;
const innerHeight = chartHeight - padding.top - padding.bottom;

const getX = (index: number) => {
    return padding.left + (index * (innerWidth / (props.data.labels.length - 1 || 1)));
};

const getY = (value: number) => {
    return padding.top + innerHeight - (value / maxVal.value) * innerHeight;
};

// Advanced: Cubic Bezier Curve Generation for smooth links
const getCurvePath = (data: number[]) => {
    if (data.length < 2) return '';
    
    let path = `M ${getX(0)} ${getY(data[0])}`;
    
    for (let i = 0; i < data.length - 1; i++) {
        const x1 = getX(i);
        const y1 = getY(data[i]);
        const x2 = getX(i + 1);
        const y2 = getY(data[i + 1]);
        
        // Control points for smooth curve
        const cp1x = x1 + (x2 - x1) / 2;
        const cp1y = y1;
        const cp2x = x1 + (x2 - x1) / 2;
        const cp2y = y2;
        
        path += ` C ${cp1x} ${cp1y}, ${cp2x} ${cp2y}, ${x2} ${y2}`;
    }
    return path;
};

const handleMouseMove = (e: MouseEvent) => {
    if (!containerRef.value) return;
    const rect = containerRef.value.getBoundingClientRect();
    const svgRelX = (e.clientX - rect.left) * (chartWidth / rect.width);
    
    // Find closest index
    const labelSpacing = innerWidth / (props.data.labels.length - 1 || 1);
    let index = Math.round((svgRelX - padding.left) / labelSpacing);
    index = Math.max(0, Math.min(props.data.labels.length - 1, index));
    
    const x = getX(index);
    const label = props.data.labels[index];
    const values = props.data.datasets.map((ds, i) => ({
        label: ds.label,
        value: ds.data[index],
        color: getColor(i, ds)
    }));

    activeTooltip.value = {
        x,
        y: Math.min(...values.map(v => getY(v.value))),
        label,
        values
    };
};

const handleMouseLeave = () => {
    activeTooltip.value = null;
};
</script>

<template>
    <Card class="chart-card group relative overflow-hidden border-border/40 bg-card/50 backdrop-blur-xl transition-all hover:border-primary/20 hover:shadow-2xl">
        <CardHeader class="flex flex-row items-center justify-between pb-2">
            <div class="space-y-1">
                <CardTitle class="text-[10px] font-black uppercase tracking-widest text-foreground/70">
                    {{ title }}
                </CardTitle>
                <div class="h-0.5 w-6 bg-primary/40 rounded-full transition-all group-hover:w-10"></div>
            </div>
            <div class="flex gap-1 opacity-40">
                <div class="h-1 w-1 rounded-full bg-foreground"></div>
                <div class="h-1 w-1 rounded-full bg-foreground"></div>
            </div>
        </CardHeader>
        <CardContent>
            <div ref="containerRef" class="relative" :style="{ height: `${height}px` }" @mousemove="handleMouseMove" @mouseleave="handleMouseLeave">
                <!-- SVG Chart Engine -->
                <svg :viewBox="`0 0 ${chartWidth} ${chartHeight}`" class="h-full w-full overflow-visible">
                    <!-- Grid Lines -->
                    <g class="grid-lines">
                        <line v-for="i in 5" :key="i"
                            :x1="padding.left"
                            :y1="padding.top + (innerHeight / 4) * (i - 1)"
                            :x2="chartWidth - padding.right"
                            :y2="padding.top + (innerHeight / 4) * (i - 1)"
                            stroke="currentColor"
                            stroke-width="0.5"
                            class="text-muted-foreground/10"
                        />
                    </g>

                    <!-- AXIS LABELS -->
                    <g class="labels font-sans">
                        <text v-for="(label, i) in data.labels" :key="i"
                            :x="getX(i)"
                            :y="chartHeight - 10"
                            text-anchor="middle"
                            class="fill-muted-foreground text-[9px] font-black uppercase tracking-tighter"
                        >
                            {{ label }}
                        </text>
                        <!-- Y Axis labels (Mini) -->
                        <text v-for="i in 5" :key="i"
                            :x="padding.left - 10"
                            :y="padding.top + (innerHeight / 4) * (i - 1) + 3"
                            text-anchor="end"
                            class="fill-muted-foreground/40 text-[8px] font-bold"
                        >
                            {{ Math.round(maxVal - (maxVal / 4) * (i - 1)) }}
                        </text>
                    </g>

                    <!-- LINE CHART MODE -->
                    <template v-if="chartType === 'line' || chartType === 'area'">
                        <g v-for="(dataset, i) in data.datasets" :key="i">
                            <!-- Area Fill -->
                            <path v-if="chartType === 'area'"
                                :d="`${getCurvePath(dataset.data)} L ${getX(dataset.data.length-1)} ${padding.top + innerHeight} L ${padding.left} ${padding.top + innerHeight} Z`"
                                :fill="getColor(i, dataset)"
                                fill-opacity="0.05"
                                class="transition-all duration-700"
                            />
                            <!-- Curve Line -->
                            <path
                                :d="getCurvePath(dataset.data)"
                                fill="none"
                                :stroke="getColor(i, dataset)"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                class="transition-all duration-700"
                            />
                            <!-- Data Markers -->
                            <circle v-for="(val, j) in dataset.data" :key="j"
                                :cx="getX(j)"
                                :cy="getY(val)"
                                r="2.5"
                                :fill="getColor(i, dataset)"
                                stroke="white"
                                stroke-width="1"
                                class="transition-all hover:r-4 cursor-pointer"
                            />
                        </g>
                    </template>

                    <!-- BAR CHART MODE -->
                    <template v-else-if="chartType === 'bar'">
                        <g v-for="(dataset, i) in data.datasets" :key="i">
                            <rect v-for="(val, j) in dataset.data" :key="j"
                                :x="getX(j) - 8 + (i * 10)"
                                :y="getY(val)"
                                width="8"
                                :height="padding.top + innerHeight - getY(val)"
                                :fill="getColor(i, dataset)"
                                rx="1.5"
                                class="transition-all hover:opacity-80"
                            />
                        </g>
                    </template>

                    <!-- DOUGHNUT CHART MODE -->
                    <template v-else-if="chartType === 'doughnut'">
                        <g transform="translate(300, 150)">
                            <circle r="70" fill="none" stroke="currentColor" stroke-width="15" class="text-muted/10" />
                            <circle v-for="(val, i) in data.datasets[0].data" :key="i"
                                r="70" fill="none"
                                :stroke="colors[i % colors.length]"
                                stroke-width="15"
                                :stroke-dasharray="`${(val/maxVal)*440} 1000`"
                                transform="rotate(-90)"
                                stroke-linecap="round"
                            />
                        </g>
                    </template>

                    <!-- Interactive Tooltip Overlay -->
                    <g v-if="activeTooltip" class="pointer-events-none">
                        <line :x1="activeTooltip.x" :y1="padding.top" :x2="activeTooltip.x" :y2="padding.top + innerHeight" 
                            stroke="currentColor" stroke-width="1" stroke-dasharray="3" class="text-primary/20" />
                    </g>
                </svg>

                <!-- TOOLTIP POPUP -->
                <transition name="fade">
                    <div v-if="activeTooltip"
                        class="absolute z-50 pointer-events-none bg-card/95 backdrop-blur-md border border-primary/20 shadow-2xl rounded-xl p-3 text-left w-44"
                        :style="{ left: `${(activeTooltip.x / chartWidth) * 100}%`, top: `15%`, transform: 'translateX(-50%)' }"
                    >
                        <div class="text-[9px] font-black uppercase text-primary mb-2 border-b border-primary/10 pb-1 tracking-widest">{{ activeTooltip.label }}</div>
                        <div v-for="v in activeTooltip.values" :key="v.label" class="flex items-center justify-between gap-3 mb-1.5 last:mb-0">
                            <div class="flex items-center gap-2">
                                <div class="h-1.5 w-1.5 rounded-full" :style="{ backgroundColor: v.color }"></div>
                                <span class="text-[9px] font-bold text-foreground/80 truncate w-24 tracking-tighter">{{ v.label }}</span>
                            </div>
                            <span class="text-[10px] font-black text-foreground">{{ v.value.toLocaleString() }}</span>
                        </div>
                    </div>
                </transition>
            </div>

            <!-- Legend -->
            <div class="mt-4 flex flex-wrap justify-center gap-4 border-t border-border/20 pt-4">
                <div v-for="(ds, i) in data.datasets" :key="i" class="flex items-center gap-2">
                    <div class="h-1.5 w-3 rounded-full" :style="{ backgroundColor: getColor(i, ds) }"></div>
                    <span class="text-[8px] font-black uppercase text-muted-foreground tracking-widest">{{ ds.label }}</span>
                </div>
            </div>
        </CardContent>
    </Card>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
    transform: translateX(-50%) translateY(10px) scale(0.95);
}

path {
    transition: d 0.7s cubic-bezier(0.4, 0, 0.2, 1);
}

.chart-card:hover {
    transform: translateY(-2px);
}
</style>
