<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import StatCard from '@/components/dashboard/StatCard.vue';
import {
    Users,
    BookOpen,
    ClipboardList,
    TrendingUp,
    Briefcase,
    Zap,
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
    {
        title: 'Dept Teachers',
        value: props.departmentTeachers.length.toString(),
        icon: Users,
        color: 'blue',
    },
    {
        title: 'Dept Subjects',
        value: props.departmentSubjects.length.toString(),
        icon: BookOpen,
        color: 'emerald',
    },
    {
        title: 'Total Learners',
        value: props.totalStudents.toString(),
        icon: Zap,
        color: 'amber',
    },
    {
        title: 'Assessments',
        value: props.recentAssessments.length.toString(),
        icon: ClipboardList,
        color: 'rose',
    },
];
</script>

<template>
    <Head title="HOD Dashboard" />

    <AppLayout>
        <template #header>
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">
                        {{ department?.name }} Management
                    </h2>
                    <p class="text-sm text-slate-500">
                        Head of Department: {{ teacher?.full_name }}
                    </p>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Stats -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                <StatCard
                    v-for="stat in stats"
                    :key="stat.title"
                    :title="stat.title"
                    :value="stat.value"
                    :icon="stat.icon"
                    :color="stat.color"
                />
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Dept Teachers -->
                <div
                    class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm lg:col-span-2"
                >
                    <div class="mb-6 flex items-center justify-between">
                        <h3
                            class="flex items-center gap-2 text-lg font-bold text-slate-800"
                        >
                            <Users class="h-5 w-5 text-blue-500" />
                            Department Staff
                        </h3>
                    </div>

                    <div
                        v-if="departmentTeachers.length > 0"
                        class="grid grid-cols-1 gap-4 md:grid-cols-2"
                    >
                        <div
                            v-for="staff in departmentTeachers"
                            :key="staff.id"
                            class="flex items-center gap-4 rounded-2xl border border-slate-100 bg-slate-50 p-4 transition-colors hover:border-blue-200"
                        >
                            <div
                                class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-full bg-slate-200"
                            >
                                <img
                                    v-if="staff.photo"
                                    :src="staff.photo"
                                    alt=""
                                    class="h-full w-full object-cover"
                                />
                                <span
                                    v-else
                                    class="text-xs font-bold text-slate-400"
                                >
                                    {{ staff.first_name[0]
                                    }}{{ staff.last_name[0] }}
                                </span>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800">
                                    {{ staff.first_name }} {{ staff.last_name }}
                                </h4>
                                <p class="text-xs text-slate-500">
                                    {{ staff.email }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dept Subjects -->
                <div
                    class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm"
                >
                    <h3
                        class="mb-6 flex items-center gap-2 text-lg font-bold text-slate-800"
                    >
                        <BookOpen class="h-5 w-5 text-emerald-500" />
                        Curriculum Focus
                    </h3>
                    <div class="space-y-3">
                        <div
                            v-for="subject in departmentSubjects"
                            :key="subject.id"
                            class="rounded-2xl border border-emerald-100 bg-emerald-50/50 p-4"
                        >
                            <h4 class="text-sm font-bold text-emerald-900">
                                {{ subject.name }}
                            </h4>
                            <div class="mt-1 flex items-center justify-between">
                                <span
                                    class="font-mono text-xs tracking-tighter text-emerald-600/70"
                                    >{{ subject.code }}</span
                                >
                                <span
                                    class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-bold tracking-wider text-emerald-700 uppercase"
                                >
                                    {{
                                        subject.is_active
                                            ? 'Active'
                                            : 'Inactive'
                                    }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Assessments -->
            <div
                class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm"
            >
                <div class="mb-6 flex items-center justify-between">
                    <h3
                        class="flex items-center gap-2 text-lg font-bold text-slate-800"
                    >
                        <ClipboardList class="h-5 w-5 text-rose-500" />
                        Departmental Assessments
                    </h3>
                </div>

                <div
                    v-if="recentAssessments.length > 0"
                    class="overflow-x-auto"
                >
                    <table class="w-full">
                        <thead>
                            <tr
                                class="text-left text-xs font-bold tracking-wider text-slate-400 uppercase"
                            >
                                <th class="pb-4">Subject</th>
                                <th class="pb-4">Class</th>
                                <th class="pb-4">Type</th>
                                <th class="pb-4">Date</th>
                                <th class="pb-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="assessment in recentAssessments"
                                :key="assessment.id"
                                class="transition-colors hover:bg-slate-50/50"
                            >
                                <td class="py-4 font-medium text-slate-800">
                                    {{ assessment.subject?.name }}
                                </td>
                                <td class="py-4 text-sm text-slate-600">
                                    {{ assessment.class?.name }}
                                </td>
                                <td class="py-4 text-sm text-slate-500">
                                    {{ assessment.assessment_type?.name }}
                                </td>
                                <td class="py-4 text-sm text-slate-500">
                                    {{
                                        new Date(
                                            assessment.assessment_date,
                                        ).toLocaleDateString()
                                    }}
                                </td>
                                <td class="py-4">
                                    <span
                                        class="rounded-full px-2 py-1 text-xs font-bold tracking-wider uppercase"
                                        :class="
                                            assessment.status === 'graded'
                                                ? 'bg-emerald-100 text-emerald-600'
                                                : 'bg-amber-100 text-amber-600'
                                        "
                                    >
                                        {{ assessment.status }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="py-12 text-center">
                    <div
                        class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-50"
                    >
                        <ClipboardList class="h-8 w-8 text-slate-300" />
                    </div>
                    <p class="text-slate-500">
                        No assessments found for this department.
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
