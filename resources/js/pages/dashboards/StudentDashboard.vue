<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import StatCard from "@/components/dashboard/StatCard.vue";
import { 
    Book, 
    TrendingUp,
    Calendar,
    Zap,
    Trophy,
    Award
} from 'lucide-vue-next';

interface Props {
    student: any;
    myResults: any[];
    upcomingAssessments: any[];
    notificationsCount: number;
}

const props = defineProps<Props>();

const stats = [
    { title: 'My Subjects', value: '0', icon: Book, color: 'blue' },
    { title: 'Assessments', value: props.myResults.length.toString(), icon: Award, color: 'emerald' },
    { title: 'Upcoming', value: props.upcomingAssessments.length.toString(), icon: Calendar, color: 'amber' },
    { title: 'Performance', value: 'Good', icon: Trophy, color: 'indigo' },
];
</script>

<template>
    <Head title="Student Dashboard" />

    <AppLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">Hi, {{ student?.first_name }}! 👋</h2>
                    <p class="text-slate-500 text-sm">{{ student?.current_class?.name || 'My Class' }}</p>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <StatCard 
                    v-for="stat in stats" 
                    :key="stat.title"
                    :title="stat.title"
                    :value="stat.value"
                    :icon="stat.icon"
                    :color="stat.color"
                />
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Recent Results -->
                <div class="lg:col-span-2 bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                    <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <TrendingUp class="w-5 h-5 text-emerald-500" />
                        Recent Results
                    </h3>
                    
                    <div v-if="myResults.length > 0" class="space-y-4">
                         <div v-for="result in myResults" :key="result.id" 
                              class="flex items-center justify-between p-4 rounded-2xl bg-slate-50 border border-slate-100">
                            <div>
                                <h4 class="text-sm font-bold text-slate-800">{{ result.assessment?.subject?.name }}</h4>
                                <p class="text-xs text-slate-500">{{ result.assessment?.assessment_type?.name }}</p>
                            </div>
                            <div class="text-right">
                                <span class="text-lg font-bold text-emerald-600">{{ result.score || result.percentage }}%</span>
                                <p class="text-[10px] text-slate-400 capitalize font-bold">{{ result.grade || 'GRADED' }}</p>
                            </div>
                         </div>
                    </div>
                    <div v-else class="text-center py-12">
                        <p class="text-slate-500 text-sm">No results to show yet. Keep studying!</p>
                    </div>
                </div>

                <!-- Upcoming Assessments -->
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                    <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <Calendar class="w-5 h-5 text-amber-500" />
                        Upcoming
                    </h3>
                    <div v-if="upcomingAssessments.length > 0" class="space-y-4">
                        <div v-for="assessment in upcomingAssessments" :key="assessment.id" 
                             class="p-4 rounded-2xl bg-amber-50/50 border border-amber-100">
                            <h4 class="text-sm font-bold text-amber-900">{{ assessment.subject?.name }}</h4>
                            <p class="text-xs text-amber-600 font-medium">{{ new Date(assessment.assessment_date).toLocaleDateString() }}</p>
                        </div>
                    </div>
                    <div v-else class="text-center py-12">
                        <p class="text-slate-500 text-xs">No upcoming assessments. Enjoy your time!</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
