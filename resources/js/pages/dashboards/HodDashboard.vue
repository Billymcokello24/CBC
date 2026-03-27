<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import StatCard from "@/components/dashboard/StatCard.vue";
import { 
    Users, 
    BookOpen,
    ClipboardList,
    TrendingUp,
    Briefcase,
    Zap
} from 'lucide-vue-next';

interface Props {
    teacher: any;
    department: any;
    departmentTeachers: any[];
    departmentSubjects: any[];
    totalStudents: number;
    recentAssessments: any[];
    notificationsCount: number;
}

const props = defineProps<Props>();

const stats = [
    { title: 'Dept Teachers', value: props.departmentTeachers.length.toString(), icon: Users, color: 'blue' },
    { title: 'Dept Subjects', value: props.departmentSubjects.length.toString(), icon: BookOpen, color: 'emerald' },
    { title: 'Total Learners', value: props.totalStudents.toString(), icon: Zap, color: 'amber' },
    { title: 'Assessments', value: props.recentAssessments.length.toString(), icon: ClipboardList, color: 'rose' },
];
</script>

<template>
    <Head title="HOD Dashboard" />

    <AppLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">{{ department?.name }} Management</h2>
                    <p class="text-slate-500 text-sm">Head of Department: {{ teacher?.full_name }}</p>
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
                 <!-- Dept Teachers -->
                 <div class="lg:col-span-2 bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                            <Users class="w-5 h-5 text-blue-500" />
                            Department Staff
                        </h3>
                    </div>

                    <div v-if="departmentTeachers.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div v-for="staff in departmentTeachers" :key="staff.id" 
                             class="flex items-center gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-100 hover:border-blue-200 transition-colors">
                            <div class="w-12 h-12 rounded-full bg-slate-200 flex items-center justify-center overflow-hidden">
                                <img v-if="staff.photo" :src="staff.photo" alt="" class="w-full h-full object-cover">
                                <span v-else class="text-xs font-bold text-slate-400">
                                    {{ staff.first_name[0] }}{{ staff.last_name[0] }}
                                </span>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800">{{ staff.first_name }} {{ staff.last_name }}</h4>
                                <p class="text-xs text-slate-500">{{ staff.email }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dept Subjects -->
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                    <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <BookOpen class="w-5 h-5 text-emerald-500" />
                        Curriculum Focus
                    </h3>
                    <div class="space-y-3">
                        <div v-for="subject in departmentSubjects" :key="subject.id" 
                             class="p-4 rounded-2xl bg-emerald-50/50 border border-emerald-100">
                            <h4 class="text-sm font-bold text-emerald-900">{{ subject.name }}</h4>
                            <div class="mt-1 flex items-center justify-between">
                                <span class="text-[10px] text-emerald-600/70 font-mono tracking-tighter">{{ subject.code }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] bg-emerald-100 text-emerald-700 font-bold uppercase tracking-wider">
                                    {{ subject.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Assessments -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <ClipboardList class="w-5 h-5 text-rose-500" />
                        Departmental Assessments
                    </h3>
                </div>

                <div v-if="recentAssessments.length > 0" class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-left text-xs font-bold text-slate-400 uppercase tracking-wider">
                                <th class="pb-4">Subject</th>
                                <th class="pb-4">Class</th>
                                <th class="pb-4">Type</th>
                                <th class="pb-4">Date</th>
                                <th class="pb-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="assessment in recentAssessments" :key="assessment.id" class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-4 font-medium text-slate-800">{{ assessment.subject?.name }}</td>
                                <td class="py-4 text-sm text-slate-600">{{ assessment.class?.name }}</td>
                                <td class="py-4 text-sm text-slate-500">{{ assessment.assessment_type?.name }}</td>
                                <td class="py-4 text-sm text-slate-500">{{ new Date(assessment.assessment_date).toLocaleDateString() }}</td>
                                <td class="py-4">
                                    <span class="px-2 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider"
                                          :class="assessment.status === 'graded' ? 'bg-emerald-100 text-emerald-600' : 'bg-amber-100 text-amber-600'">
                                        {{ assessment.status }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="text-center py-12">
                    <div class="bg-slate-50 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <ClipboardList class="w-8 h-8 text-slate-300" />
                    </div>
                    <p class="text-slate-500">No assessments found for this department.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
