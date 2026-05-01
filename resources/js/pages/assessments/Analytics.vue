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
    Users,
} from 'lucide-vue-next';

interface Props {
    distribution: Record<string, number>;
    trends: any[];
    classes: any[];
}

const props = defineProps<Props>();

const ratingLabels: Record<string, string> = {
    EE: 'Exceeding Expectation',
    ME: 'Meeting Expectation',
    AE: 'Approaching Expectation',
    BE: 'Below Expectation',
};

const ratingColors: Record<string, string> = {
    EE: 'bg-emerald-500',
    ME: 'bg-blue-500',
    AE: 'bg-amber-500',
    BE: 'bg-rose-500',
};

const ratingTextColors: Record<string, string> = {
    EE: 'text-emerald-600',
    ME: 'text-blue-600',
    AE: 'text-amber-600',
    BE: 'text-rose-600',
};

const totalRatings = Object.values(props.distribution).reduce(
    (a, b) => a + b,
    0,
);

const getPercentage = (count: number) => {
    return totalRatings > 0 ? Math.round((count / totalRatings) * 100) : 0;
};
</script>

<template>
    <Head title="Teacher Analytics" />

    <AppLayout>
        <template #header>
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">
                        Learning Analytics
                    </h2>
                    <p class="text-sm text-muted-foreground">
                        Visualizing student performance and growth trends.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <button
                        class="flex items-center gap-2 rounded-2xl border border-border/50 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition-colors hover:bg-muted/10"
                    >
                        <Filter class="h-4 w-4" />
                        Filter Data
                    </button>
                </div>
            </div>
        </template>

        <div class="space-y-6 pb-12">
            <!-- Summary Grid -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                <!-- Performance Distribution Card -->
                <div
                    class="rounded-2xl border border-border bg-white p-8 shadow-sm lg:col-span-2"
                >
                    <div class="mb-8 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">
                                Performance Distribution
                            </h3>
                            <p class="text-sm text-muted-foreground">
                                Breakdown of student ratings across all
                                assessments.
                            </p>
                        </div>
                        <div
                            class="flex items-center gap-2 rounded-2xl bg-muted/10 px-4 py-2"
                        >
                            <Users class="h-4 w-4 text-emerald-500" />
                            <span class="text-xs font-bold text-slate-600"
                                >{{ totalRatings }} Total Ratings</span
                            >
                        </div>
                    </div>

                    <div
                        class="grid grid-cols-1 items-center gap-12 md:grid-cols-2"
                    >
                        <!-- Custom Bar Chart -->
                        <div class="space-y-6">
                            <div
                                v-for="(count, level) in distribution"
                                :key="level"
                                class="space-y-2"
                            >
                                <div class="flex items-end justify-between">
                                    <span
                                        class="text-sm font-bold text-slate-700"
                                        >{{ ratingLabels[level] }} ({{
                                            level
                                        }})</span
                                    >
                                    <span
                                        class="text-xs font-bold"
                                        :class="ratingTextColors[level]"
                                        >{{ getPercentage(count) }}%</span
                                    >
                                </div>
                                <div
                                    class="h-3 w-full overflow-hidden rounded-full bg-muted/10"
                                >
                                    <div
                                        class="h-full transition-all duration-1000"
                                        :class="ratingColors[level]"
                                        :style="{
                                            width: getPercentage(count) + '%',
                                        }"
                                    ></div>
                                </div>
                            </div>
                        </div>

                        <!-- Stats/Summary -->
                        <div
                            class="space-y-4 rounded-2xl border border-border bg-muted/10 p-6"
                        >
                            <h4 class="text-sm font-bold text-slate-800">
                                Overview Summary
                            </h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div
                                    class="rounded-2xl border border-border bg-white p-4 shadow-sm"
                                >
                                    <p
                                        class="mb-1 text-xs font-bold text-muted-foreground/80 "
                                    >
                                        Pass Rate
                                    </p>
                                    <p
                                        class="text-xl font-bold text-emerald-600"
                                    >
                                        {{
                                            getPercentage(
                                                distribution.EE +
                                                    distribution.ME,
                                            )
                                        }}%
                                    </p>
                                </div>
                                <div
                                    class="rounded-2xl border border-border bg-white p-4 shadow-sm"
                                >
                                    <p
                                        class="mb-1 text-xs font-bold text-muted-foreground/80 "
                                    >
                                        Needs Support
                                    </p>
                                    <p class="text-xl font-bold text-rose-600">
                                        {{ getPercentage(distribution.BE) }}%
                                    </p>
                                </div>
                            </div>
                            <p class="text-xs leading-relaxed text-muted-foreground">
                                "Analysis shows that most learners are
                                {{
                                    getPercentage(
                                        distribution.EE + distribution.ME,
                                    ) > 70
                                        ? 'excelling in'
                                        : 'making steady progress toward'
                                }}
                                the identified competencies."
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Quick Trends -->
                <div
                    class="flex flex-col rounded-2xl border border-border bg-white p-8 shadow-sm"
                >
                    <div class="mb-8">
                        <h3 class="text-lg font-bold text-slate-800">
                            Performance Trends
                        </h3>
                        <p class="text-sm text-muted-foreground">
                            Average score over recent tasks.
                        </p>
                    </div>

                    <div v-if="trends.length > 0" class="flex-1 space-y-4">
                        <div
                            v-for="trend in trends"
                            :key="trend.title"
                            class="group flex items-center gap-4 rounded-2xl border border-transparent p-4 transition-colors hover:border-border hover:bg-muted/10"
                        >
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 transition-colors group-hover:bg-blue-600"
                            >
                                <TrendingUp
                                    class="h-6 w-6 text-blue-600 transition-colors group-hover:text-white"
                                />
                            </div>
                            <div class="flex-1">
                                <h4
                                    class="line-clamp-1 text-sm font-bold text-slate-800"
                                >
                                    {{ trend.title }}
                                </h4>
                                <p
                                    class="text-xs font-bold tracking-tight text-muted-foreground/80 "
                                >
                                    {{ trend.date }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-slate-800">
                                    {{ Math.round(trend.average) }}%
                                </p>
                                <div
                                    class="flex items-center justify-end gap-1"
                                >
                                    <ArrowUpRight
                                        v-if="trend.average >= 50"
                                        class="h-3 w-3 text-emerald-500"
                                    />
                                    <ArrowDownRight
                                        v-else
                                        class="h-3 w-3 text-rose-500"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        v-else
                        class="flex flex-1 flex-col items-center justify-center text-center"
                    >
                        <TrendingUp class="mb-4 h-12 w-12 text-slate-200" />
                        <p class="text-sm text-muted-foreground/80">
                            Not enough data to calculate trends.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Subject Breakdown -->
            <div
                class="rounded-2xl border border-border bg-white p-8 shadow-sm"
            >
                <div class="mb-8 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">
                            Subject-wise Mastery
                        </h3>
                        <p class="text-sm text-muted-foreground">
                            Average performance levels per subject.
                        </p>
                    </div>
                    <button
                        class="flex items-center gap-1 text-xs font-bold text-blue-600 hover:underline"
                    >
                        View Detailed Report <ChevronRight class="h-3 w-3" />
                    </button>
                </div>

                <div
                    class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4"
                >
                    <div
                        v-for="cls in classes"
                        :key="cls.id"
                        class="rounded-2xl border border-border bg-muted/10 p-6 transition-shadow hover:shadow-md"
                    >
                        <h4 class="mb-4 font-bold text-slate-800">
                            {{ cls.name }}
                        </h4>
                        <!-- Placeholder for class-specific mini-chart/stat -->
                        <div class="mb-4 flex h-12 items-end gap-1">
                            <div
                                class="h-[80%] flex-1 rounded-t-lg bg-emerald-200"
                            ></div>
                            <div
                                class="h-[60%] flex-1 rounded-t-lg bg-blue-200"
                            ></div>
                            <div
                                class="h-[40%] flex-1 rounded-t-lg bg-amber-200"
                            ></div>
                            <div
                                class="h-[10%] flex-1 rounded-t-lg bg-rose-200"
                            ></div>
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Active engagement across 4 curriculum strands.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
