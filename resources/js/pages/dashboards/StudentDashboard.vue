<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import StatCard from '@/components/dashboard/StatCard.vue';
import {
    Book,
    TrendingUp,
    Calendar,
    Zap,
    Trophy,
    Award,
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
    {
        title: 'Assessments',
        value: props.myResults.length.toString(),
        icon: Award,
        color: 'emerald',
    },
    {
        title: 'Upcoming',
        value: props.upcomingAssessments.length.toString(),
        icon: Calendar,
        color: 'amber',
    },
    { title: 'Performance', value: 'Good', icon: Trophy, color: 'indigo' },
];
</script>

<template>
    <Head title="Student Dashboard" />

    <AppLayout>
        <template #header>
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">
                        Hi, {{ student?.first_name }}! 👋
                    </h2>
                    <p class="text-sm text-slate-500">
                        {{ student?.current_class?.name || 'My Class' }}
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
                <!-- Recent Results -->
                <div
                    class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm lg:col-span-2"
                >
                    <h3
                        class="mb-6 flex items-center gap-2 text-lg font-bold text-slate-800"
                    >
                        <TrendingUp class="h-5 w-5 text-emerald-500" />
                        Recent Results
                    </h3>

                    <div v-if="myResults.length > 0" class="space-y-4">
                        <div
                            v-for="result in myResults"
                            :key="result.id"
                            class="flex items-center justify-between rounded-2xl border border-slate-100 bg-slate-50 p-4"
                        >
                            <div>
                                <h4 class="text-sm font-bold text-slate-800">
                                    {{ result.assessment?.subject?.name }}
                                </h4>
                                <p class="text-xs text-slate-500">
                                    {{
                                        result.assessment?.assessment_type?.name
                                    }}
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="text-lg font-bold text-emerald-600"
                                    >{{
                                        result.score || result.percentage
                                    }}%</span
                                >
                                <p
                                    class="text-xs font-bold text-slate-400 capitalize"
                                >
                                    {{ result.grade || 'GRADED' }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div v-else class="py-12 text-center">
                        <p class="text-sm text-slate-500">
                            No results to show yet. Keep studying!
                        </p>
                    </div>
                </div>

                <!-- Upcoming Assessments -->
                <div
                    class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm"
                >
                    <h3
                        class="mb-6 flex items-center gap-2 text-lg font-bold text-slate-800"
                    >
                        <Calendar class="h-5 w-5 text-amber-500" />
                        Upcoming
                    </h3>
                    <div
                        v-if="upcomingAssessments.length > 0"
                        class="space-y-4"
                    >
                        <div
                            v-for="assessment in upcomingAssessments"
                            :key="assessment.id"
                            class="rounded-2xl border border-amber-100 bg-amber-50/50 p-4"
                        >
                            <h4 class="text-sm font-bold text-amber-900">
                                {{ assessment.subject?.name }}
                            </h4>
                            <p class="text-xs font-medium text-amber-600">
                                {{
                                    new Date(
                                        assessment.assessment_date,
                                    ).toLocaleDateString()
                                }}
                            </p>
                        </div>
                    </div>
                    <div v-else class="py-12 text-center">
                        <p class="text-xs text-slate-500">
                            No upcoming assessments. Enjoy your time!
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
