<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import StatCard from "@/components/dashboard/StatCard.vue";
import { 
    Users, 
    UserCheck,
    ClipboardList,
    TrendingUp,
    MessageSquare,
    PieChart
} from 'lucide-vue-next';

interface Props {
    teacher: any;
    myClass: any;
    students: any[];
    totalStudents: number;
    boysCount: number;
    girlsCount: number;
    attendanceRate: number;
    recentAssessments: any[];
    notificationsCount: number;
}

const props = defineProps<Props>();

const stats = [
    { title: 'Total Learners', value: props.totalStudents.toString(), icon: Users, color: 'blue' },
    { title: 'Attendance Rate', value: props.attendanceRate + '%', icon: UserCheck, color: 'emerald' },
    { title: 'Boys', value: props.boysCount.toString(), icon: PieChart, color: 'indigo' },
    { title: 'Girls', value: props.girlsCount.toString(), icon: PieChart, color: 'rose' },
];
</script>

<template>
    <Head title="Class Teacher Dashboard" />

    <AppLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">{{ myClass?.name }} Dashboard</h2>
                    <p class="text-slate-500 text-sm">Class Teacher: {{ teacher?.full_name }}</p>
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
                <!-- Class List Snippet -->
                <div class="lg:col-span-2 bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                            <Users class="w-5 h-5 text-blue-500" />
                            Class Learners
                        </h3>
                    </div>

                    <div v-if="students.length > 0" class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="text-left text-xs font-bold text-slate-400 uppercase tracking-wider">
                                    <th class="pb-4">Learner</th>
                                    <th class="pb-4">Adm No</th>
                                    <th class="pb-4">Gender</th>
                                    <th class="pb-4">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="student in students" :key="student.id" class="hover:bg-slate-50/50 transition-colors">
                                    <td class="py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center overflow-hidden">
                                                <img v-if="student.photo" :src="student.photo" alt="" class="w-full h-full object-cover">
                                                <span v-else class="text-[10px] font-bold text-slate-400">
                                                    {{ student.first_name[0] }}{{ student.last_name[0] }}
                                                </span>
                                            </div>
                                            <span class="text-sm font-medium text-slate-800">{{ student.first_name }} {{ student.last_name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 text-xs font-mono text-slate-500">{{ student.admission_number }}</td>
                                    <td class="py-4">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                                              :class="student.gender === 'male' ? 'bg-blue-50 text-blue-600' : 'bg-rose-50 text-rose-600'">
                                            {{ student.gender }}
                                        </span>
                                    </td>
                                    <td class="py-4">
                                        <span class="w-2 h-2 rounded-full inline-block mr-1"
                                              :class="student.status === 'active' ? 'bg-emerald-500' : 'bg-slate-300'"></span>
                                        <span class="text-xs text-slate-600 capitalize">{{ student.status }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-center py-12">
                        <p class="text-slate-500">No learners assigned to this class.</p>
                    </div>
                </div>

                <!-- Recent Activities & Reminders -->
                <div class="space-y-6">
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                        <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                            <ClipboardList class="w-5 h-5 text-rose-500" />
                            Recent Assessments
                        </h3>
                        <div v-if="recentAssessments.length > 0" class="space-y-4">
                            <div v-for="assessment in recentAssessments" :key="assessment.id" 
                                 class="p-3 rounded-2xl bg-rose-50/50 border border-rose-100">
                                <h4 class="text-sm font-bold text-rose-900">{{ assessment.subject?.name }}</h4>
                                <p class="text-xs text-rose-600">{{ assessment.assessment_type?.name }}</p>
                                <div class="mt-2 flex items-center justify-between">
                                    <span class="text-[10px] text-rose-500/70">{{ new Date(assessment.created_at).toLocaleDateString() }}</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] bg-rose-100 text-rose-700 font-bold uppercase tracking-wider">
                                        {{ assessment.status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8">
                            <p class="text-slate-500 text-xs">No recent assessments for this class.</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                        <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                            <TrendingUp class="w-5 h-5 text-emerald-500" />
                            Class Insights
                        </h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-3 rounded-2xl bg-emerald-50/30">
                                <span class="text-xs font-medium text-slate-600">Boys Enrollment</span>
                                <span class="text-xs font-bold text-emerald-600">{{ Math.round((boysCount / totalStudents) * 100) }}%</span>
                            </div>
                            <div class="flex items-center justify-between p-3 rounded-2xl bg-rose-50/30">
                                <span class="text-xs font-medium text-slate-600">Girls Enrollment</span>
                                <span class="text-xs font-bold text-rose-600">{{ Math.round((girlsCount / totalStudents) * 100) }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
