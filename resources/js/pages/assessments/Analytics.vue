<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { 
    TrendingUp, 
    BarChart3, 
    PieChart, 
    ChevronRight,
    Filter,
    ArrowUpRight,
    ArrowDownRight,
    Users
} from 'lucide-vue-next';

interface Props {
    distribution: Record<string, number>;
    trends: any[];
    classes: any[];
}

const props = defineProps<Props>();

const ratingLabels: Record<string, string> = {
    'EE': 'Exceeding Expectation',
    'ME': 'Meeting Expectation',
    'AE': 'Approaching Expectation',
    'BE': 'Below Expectation'
};

const ratingColors: Record<string, string> = {
    'EE': 'bg-emerald-500',
    'ME': 'bg-blue-500',
    'AE': 'bg-amber-500',
    'BE': 'bg-rose-500'
};

const ratingTextColors: Record<string, string> = {
    'EE': 'text-emerald-600',
    'ME': 'text-blue-600',
    'AE': 'text-amber-600',
    'BE': 'text-rose-600'
};

const totalRatings = Object.values(props.distribution).reduce((a, b) => a + b, 0);

const getPercentage = (count: number) => {
    return totalRatings > 0 ? Math.round((count / totalRatings) * 100) : 0;
};
</script>

<template>
    <Head title="Teacher Analytics" />

    <AppLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">Learning Analytics</h2>
                    <p class="text-slate-500 text-sm">Visualizing student performance and growth trends.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button class="bg-white border border-slate-200 px-4 py-2 rounded-2xl text-sm font-bold text-slate-700 flex items-center gap-2 hover:bg-slate-50 transition-colors">
                        <Filter class="w-4 h-4" />
                        Filter Data
                    </button>
                </div>
            </div>
        </template>

        <div class="space-y-6 pb-12">
            <!-- Summary Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Performance Distribution Card -->
                <div class="lg:col-span-2 bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">Performance Distribution</h3>
                            <p class="text-sm text-slate-500">Breakdown of student ratings across all assessments.</p>
                        </div>
                        <div class="bg-slate-50 px-4 py-2 rounded-2xl flex items-center gap-2">
                             <Users class="w-4 h-4 text-emerald-500" />
                             <span class="text-xs font-bold text-slate-600">{{ totalRatings }} Total Ratings</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                        <!-- Custom Bar Chart -->
                        <div class="space-y-6">
                            <div v-for="(count, level) in distribution" :key="level" class="space-y-2">
                                <div class="flex justify-between items-end">
                                    <span class="text-sm font-bold text-slate-700">{{ ratingLabels[level] }} ({{ level }})</span>
                                    <span class="text-xs font-black" :class="ratingTextColors[level]">{{ getPercentage(count) }}%</span>
                                </div>
                                <div class="w-full bg-slate-50 rounded-full h-3 overflow-hidden">
                                    <div class="h-full transition-all duration-1000" 
                                         :class="ratingColors[level]"
                                         :style="{ width: getPercentage(count) + '%' }"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Stats/Summary -->
                        <div class="bg-slate-50 rounded-3xl p-6 space-y-4 border border-slate-100">
                             <h4 class="text-sm font-bold text-slate-800">Overview Summary</h4>
                             <div class="grid grid-cols-2 gap-4">
                                 <div class="p-4 bg-white rounded-2xl border border-slate-100 shadow-sm">
                                     <p class="text-[10px] uppercase font-bold text-slate-400 mb-1">Pass Rate</p>
                                     <p class="text-xl font-black text-emerald-600">{{ getPercentage(distribution.EE + distribution.ME) }}%</p>
                                 </div>
                                 <div class="p-4 bg-white rounded-2xl border border-slate-100 shadow-sm">
                                     <p class="text-[10px] uppercase font-bold text-slate-400 mb-1">Needs Support</p>
                                     <p class="text-xl font-black text-rose-600">{{ getPercentage(distribution.BE) }}%</p>
                                 </div>
                             </div>
                             <p class="text-xs text-slate-500 leading-relaxed italic">
                                "Analysis shows that most learners are {{ getPercentage(distribution.EE + distribution.ME) > 70 ? 'excelling in' : 'making steady progress toward' }} the identified competencies."
                             </p>
                        </div>
                    </div>
                </div>

                <!-- Quick Trends -->
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8 flex flex-col">
                    <div class="mb-8">
                        <h3 class="text-lg font-bold text-slate-800">Performance Trends</h3>
                        <p class="text-sm text-slate-500">Average score over recent tasks.</p>
                    </div>

                    <div v-if="trends.length > 0" class="flex-1 space-y-4">
                        <div v-for="trend in trends" :key="trend.title" 
                             class="flex items-center gap-4 p-4 rounded-2xl hover:bg-slate-50 transition-colors group border border-transparent hover:border-slate-100">
                            <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center group-hover:bg-blue-600 transition-colors">
                                <TrendingUp class="w-6 h-6 text-blue-600 group-hover:text-white transition-colors" />
                            </div>
                            <div class="flex-1">
                                <h4 class="text-sm font-bold text-slate-800 line-clamp-1">{{ trend.title }}</h4>
                                <p class="text-[10px] text-slate-400 uppercase font-black tracking-tighter">{{ trend.date }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-black text-slate-800">{{ Math.round(trend.average) }}%</p>
                                <div class="flex items-center gap-1 justify-end">
                                    <ArrowUpRight v-if="trend.average >= 50" class="w-3 h-3 text-emerald-500" />
                                    <ArrowDownRight v-else class="w-3 h-3 text-rose-500" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="flex-1 flex flex-col items-center justify-center text-center">
                         <TrendingUp class="w-12 h-12 text-slate-200 mb-4" />
                         <p class="text-slate-400 text-sm">Not enough data to calculate trends.</p>
                    </div>
                </div>
            </div>

            <!-- Subject Breakdown -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Subject-wise Mastery</h3>
                        <p class="text-sm text-slate-500">Average performance levels per subject.</p>
                    </div>
                    <button class="text-xs font-bold text-blue-600 flex items-center gap-1 hover:underline">
                        View Detailed Report <ChevronRight class="w-3 h-3" />
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div v-for="cls in classes" :key="cls.id" 
                         class="p-6 rounded-3xl border border-slate-100 bg-slate-50 hover:shadow-md transition-shadow">
                        <h4 class="font-black text-slate-800 mb-4">{{ cls.name }}</h4>
                        <!-- Placeholder for class-specific mini-chart/stat -->
                        <div class="flex items-end gap-1 h-12 mb-4">
                             <div class="flex-1 bg-emerald-200 rounded-t-lg h-[80%]"></div>
                             <div class="flex-1 bg-blue-200 rounded-t-lg h-[60%]"></div>
                             <div class="flex-1 bg-amber-200 rounded-t-lg h-[40%]"></div>
                             <div class="flex-1 bg-rose-200 rounded-t-lg h-[10%]"></div>
                        </div>
                        <p class="text-xs text-slate-500">Active engagement across 4 curriculum strands.</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
