<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import StatCard from '@/components/dashboard/StatCard.vue';
import {
    Users,
    BookOpen,
    ClipboardList,
    TrendingUp,
    Heart,
    Zap,
} from 'lucide-vue-next';

interface Props {
    guardian: any;
    children: any[];
    childrenResults: any[];
    notificationsCount: number;
}

const props = defineProps<Props>();

const stats = [
    {
        title: 'My Children',
        value: props.children.length.toString(),
        icon: Users,
        color: 'blue',
    },
    {
        title: 'Recent Results',
        value: props.childrenResults.length.toString(),
        icon: TrendingUp,
        color: 'emerald',
    },
    {
        title: 'Notifications',
        value: props.notificationsCount.toString(),
        icon: Zap,
        color: 'amber',
    },
    { title: 'Health Recs', value: '0', icon: Heart, color: 'rose' },
];
</script>

<template>
    <Head title="Parent Dashboard" />

    <AppLayout>
        <template #header>
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">
                        Hello, {{ guardian?.full_name || 'Guardian' }}
                    </h2>
                    <p class="text-sm text-slate-500">Parent/Guardian Portal</p>
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
                <!-- My Children -->
                <div class="space-y-4 lg:col-span-1">
                    <h3
                        class="mb-2 flex items-center gap-2 text-lg font-bold text-slate-800"
                    >
                        <Users class="h-5 w-5 text-blue-500" />
                        My Children
                    </h3>
                    <div
                        v-for="child in children"
                        :key="child.id"
                        class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm transition-colors hover:border-blue-200"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="flex h-16 w-16 items-center justify-center overflow-hidden rounded-2xl bg-slate-100"
                            >
                                <img
                                    v-if="child.photo"
                                    :src="child.photo"
                                    alt=""
                                    class="h-full w-full object-cover"
                                />
                                <span
                                    v-else
                                    class="text-lg font-bold text-slate-400"
                                >
                                    {{ child.first_name[0]
                                    }}{{ child.last_name[0] }}
                                </span>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800">
                                    {{ child.first_name }} {{ child.last_name }}
                                </h4>
                                <p class="text-xs text-slate-500">
                                    {{ child.admission_number }}
                                </p>
                                <p
                                    class="mt-1 text-xs font-medium text-blue-600"
                                >
                                    {{
                                        child.current_class?.name ||
                                        'Assigned Class'
                                    }}
                                </p>
                            </div>
                        </div>
                        <div class="mt-6 flex gap-2">
                            <button
                                class="flex-1 rounded-xl bg-blue-50 px-3 py-2 text-xs font-bold text-blue-600 transition-colors hover:bg-blue-100"
                            >
                                Profile
                            </button>
                            <button
                                class="flex-1 rounded-xl bg-slate-50 px-3 py-2 text-xs font-bold text-slate-600 transition-colors hover:bg-slate-100"
                            >
                                Results
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Recent results/performance -->
                <div
                    class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm lg:col-span-2"
                >
                    <h3
                        class="mb-6 flex items-center gap-2 text-lg font-bold text-slate-800"
                    >
                        <TrendingUp class="h-5 w-5 text-emerald-500" />
                        Recent Performance
                    </h3>

                    <div v-if="childrenResults.length > 0" class="space-y-4">
                        <div
                            v-for="result in childrenResults"
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
                                    • {{ result.student?.first_name }}
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="text-lg font-bold text-emerald-600"
                                    >{{
                                        result.score || result.percentage
                                    }}%</span
                                >
                                <p
                                    class="text-xs font-bold text-slate-400 uppercase"
                                >
                                    {{ result.grade || 'GRADED' }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div v-else class="py-12 text-center">
                        <p class="text-sm text-slate-500">
                            No recent results found.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
