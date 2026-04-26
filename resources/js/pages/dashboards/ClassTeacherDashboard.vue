<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import StatCard from '@/components/dashboard/StatCard.vue';
import {
    Users,
    UserCheck,
    ClipboardList,
    TrendingUp,
    MessageSquare,
    PieChart,
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
    {
        title: 'Total Learners',
        value: props.totalStudents.toString(),
        icon: Users,
        color: 'blue',
    },
    {
        title: 'Attendance Rate',
        value: props.attendanceRate + '%',
        icon: UserCheck,
        color: 'emerald',
    },
    {
        title: 'Boys',
        value: props.boysCount.toString(),
        icon: PieChart,
        color: 'indigo',
    },
    {
        title: 'Girls',
        value: props.girlsCount.toString(),
        icon: PieChart,
        color: 'rose',
    },
];
</script>

<template>
    <Head title="Class Teacher Dashboard" />

    <AppLayout>
        <template #header>
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">
                        {{ myClass?.name }} Dashboard
                    </h2>
                    <p class="text-sm text-slate-500">
                        Class Teacher: {{ teacher?.full_name }}
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
                <!-- Class List Snippet -->
                <div
                    class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm lg:col-span-2"
                >
                    <div class="mb-6 flex items-center justify-between">
                        <h3
                            class="flex items-center gap-2 text-lg font-bold text-slate-800"
                        >
                            <Users class="h-5 w-5 text-blue-500" />
                            Class Learners
                        </h3>
                    </div>

                    <div v-if="students.length > 0" class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr
                                    class="text-left text-xs font-bold tracking-wider text-slate-400 uppercase"
                                >
                                    <th class="pb-4">Learner</th>
                                    <th class="pb-4">Adm No</th>
                                    <th class="pb-4">Gender</th>
                                    <th class="pb-4">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr
                                    v-for="student in students"
                                    :key="student.id"
                                    class="transition-colors hover:bg-slate-50/50"
                                >
                                    <td class="py-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex h-8 w-8 items-center justify-center overflow-hidden rounded-full bg-slate-100"
                                            >
                                                <img
                                                    v-if="student.photo"
                                                    :src="student.photo"
                                                    alt=""
                                                    class="h-full w-full object-cover"
                                                />
                                                <span
                                                    v-else
                                                    class="text-xs font-bold text-slate-400"
                                                >
                                                    {{ student.first_name[0]
                                                    }}{{ student.last_name[0] }}
                                                </span>
                                            </div>
                                            <span
                                                class="text-sm font-medium text-slate-800"
                                                >{{ student.first_name }}
                                                {{ student.last_name }}</span
                                            >
                                        </div>
                                    </td>
                                    <td
                                        class="py-4 font-mono text-xs text-slate-500"
                                    >
                                        {{ student.admission_number }}
                                    </td>
                                    <td class="py-4">
                                        <span
                                            class="rounded-full px-2 py-0.5 text-xs font-bold tracking-wider uppercase"
                                            :class="
                                                student.gender === 'male'
                                                    ? 'bg-blue-50 text-blue-600'
                                                    : 'bg-rose-50 text-rose-600'
                                            "
                                        >
                                            {{ student.gender }}
                                        </span>
                                    </td>
                                    <td class="py-4">
                                        <span
                                            class="mr-1 inline-block h-2 w-2 rounded-full"
                                            :class="
                                                student.status === 'active'
                                                    ? 'bg-emerald-500'
                                                    : 'bg-slate-300'
                                            "
                                        ></span>
                                        <span
                                            class="text-xs text-slate-600 capitalize"
                                            >{{ student.status }}</span
                                        >
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="py-12 text-center">
                        <p class="text-slate-500">
                            No learners assigned to this class.
                        </p>
                    </div>
                </div>

                <!-- Recent Activities & Reminders -->
                <div class="space-y-6">
                    <div
                        class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm"
                    >
                        <h3
                            class="mb-6 flex items-center gap-2 text-lg font-bold text-slate-800"
                        >
                            <ClipboardList class="h-5 w-5 text-rose-500" />
                            Recent Assessments
                        </h3>
                        <div
                            v-if="recentAssessments.length > 0"
                            class="space-y-4"
                        >
                            <div
                                v-for="assessment in recentAssessments"
                                :key="assessment.id"
                                class="rounded-2xl border border-rose-100 bg-rose-50/50 p-3"
                            >
                                <h4 class="text-sm font-bold text-rose-900">
                                    {{ assessment.subject?.name }}
                                </h4>
                                <p class="text-xs text-rose-600">
                                    {{ assessment.assessment_type?.name }}
                                </p>
                                <div
                                    class="mt-2 flex items-center justify-between"
                                >
                                    <span class="text-xs text-rose-500/70">{{
                                        new Date(
                                            assessment.created_at,
                                        ).toLocaleDateString()
                                    }}</span>
                                    <span
                                        class="rounded-full bg-rose-100 px-2 py-0.5 text-xs font-bold tracking-wider text-rose-700 uppercase"
                                    >
                                        {{ assessment.status }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div v-else class="py-8 text-center">
                            <p class="text-xs text-slate-500">
                                No recent assessments for this class.
                            </p>
                        </div>
                    </div>

                    <div
                        class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm"
                    >
                        <h3
                            class="mb-4 flex items-center gap-2 text-lg font-bold text-slate-800"
                        >
                            <TrendingUp class="h-5 w-5 text-emerald-500" />
                            Class Insights
                        </h3>
                        <div class="space-y-4">
                            <div
                                class="flex items-center justify-between rounded-2xl bg-emerald-50/30 p-3"
                            >
                                <span class="text-xs font-medium text-slate-600"
                                    >Boys Enrollment</span
                                >
                                <span class="text-xs font-bold text-emerald-600"
                                    >{{
                                        Math.round(
                                            (boysCount / totalStudents) * 100,
                                        )
                                    }}%</span
                                >
                            </div>
                            <div
                                class="flex items-center justify-between rounded-2xl bg-rose-50/30 p-3"
                            >
                                <span class="text-xs font-medium text-slate-600"
                                    >Girls Enrollment</span
                                >
                                <span class="text-xs font-bold text-rose-600"
                                    >{{
                                        Math.round(
                                            (girlsCount / totalStudents) * 100,
                                        )
                                    }}%</span
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
