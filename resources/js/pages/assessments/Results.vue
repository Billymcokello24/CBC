<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    Award,
    BarChart3,
    Bot,
    BookOpen,
    Download,
    FileSpreadsheet,
    Filter,
    GraduationCap,
    LayoutGrid,
    LineChart,
    Medal,
    Printer,
    Search,
    Send,
    Trophy,
    Users,
} from 'lucide-vue-next';
import { Bar, Doughnut, Line } from 'vue-chartjs';
import {
    ArcElement,
    BarElement,
    CategoryScale,
    Chart as ChartJS,
    Filler,
    Legend,
    LinearScale,
    LineElement,
    PointElement,
    Tooltip,
} from 'chart.js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import SearchableSelect from '@/components/SearchableSelect.vue';
import type { BreadcrumbItem } from '@/types';

ChartJS.register(CategoryScale, LinearScale, BarElement, ArcElement, PointElement, LineElement, Tooltip, Legend, Filler);

const props = defineProps<{
    students: { data: Array<any>; links: Array<any>; meta?: any };
    activeYear: any;
    activeTerm: any;
    analytics: {
        overallMean: number;
        totalAssessments: number;
        activeStudents: number;
        recordedResults: number;
        subjectAnalysis: Array<any>;
        classAnalysis: Array<any>;
        gradeAnalysis: Array<any>;
        trend: Array<{ label: string; score: number }>;
        trendByClass: Array<any>;
        trendByGrade: Array<any>;
        trendBySubject: Array<any>;
        distribution: Record<string, number>;
    };
    rankings: {
        students: Array<any>;
        classes: Array<any>;
        grades: Array<any>;
        subjects: Array<any>;
        topClass?: any;
        topGrade?: any;
        topSubject?: any;
    };
    filters: Record<string, string>;
    classes: Array<any>;
    grades: Array<any>;
    subjects: Array<any>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Assessments', href: '/assessments' },
    { title: 'Results', href: '/assessments/results' },
];

const localFilters = ref({
    search: props.filters?.search || '',
    grade_level_id: props.filters?.grade_level_id || 'all',
    class_id: props.filters?.class_id || 'all',
    subject_id: props.filters?.subject_id || 'all',
});

const applyFilters = () => {
    router.get('/assessments/results', localFilters.value, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    localFilters.value = { search: '', grade_level_id: 'all', class_id: 'all', subject_id: 'all' };
    applyFilters();
};

const queryString = computed(() => new URLSearchParams(localFilters.value).toString());
const pdfUrl = computed(() => `/assessments/results/pdf?${queryString.value}`);
const templateUrl = '/assessments/results/template';
const agentPrompt = ref('');
const agentAnswer = ref('Ask for a class, grade, subject, top performers, or a print link. I will use the loaded results on this page.');

const palette = ['#2563eb', '#16a34a', '#f59e0b', '#dc2626', '#7c3aed', '#0891b2'];

const printUrl = (params: Record<string, string | number | null | undefined> = {}) => {
    const merged: Record<string, string> = {
        ...localFilters.value,
        ...Object.fromEntries(
            Object.entries(params)
                .filter(([, value]) => value !== null && value !== undefined)
                .map(([key, value]) => [key, String(value)]),
        ),
    };

    return `/assessments/results/pdf?${new URLSearchParams(merged).toString()}`;
};

const lineData = computed(() => ({
    labels: props.analytics.trend.map((item) => item.label),
    datasets: [{
        label: 'Mean performance',
        data: props.analytics.trend.map((item) => item.score),
        borderColor: '#2563eb',
        backgroundColor: 'rgba(37, 99, 235, 0.12)',
        fill: true,
        tension: 0.35,
        pointRadius: 3,
    }],
}));

const classBarData = computed(() => ({
    labels: props.analytics.classAnalysis.slice(0, 8).map((item) => item.name),
    datasets: [{
        label: 'Class mean',
        data: props.analytics.classAnalysis.slice(0, 8).map((item) => item.score),
        backgroundColor: '#2563eb',
        borderRadius: 6,
    }],
}));

const subjectBarData = computed(() => ({
    labels: props.analytics.subjectAnalysis.slice(0, 8).map((item) => item.name),
    datasets: [{
        label: 'Subject mean',
        data: props.analytics.subjectAnalysis.slice(0, 8).map((item) => item.score),
        backgroundColor: '#16a34a',
        borderRadius: 6,
    }],
}));

const distributionData = computed(() => ({
    labels: ['EE', 'ME', 'AE', 'BE'],
    datasets: [{
        data: ['EE', 'ME', 'AE', 'BE'].map((key) => props.analytics.distribution?.[key] || 0),
        backgroundColor: ['#16a34a', '#2563eb', '#f59e0b', '#dc2626'],
        borderWidth: 0,
    }],
}));

const groupedTrendData = (groups: Array<any>) => computed(() => {
    const labels = props.analytics.trend.map((item) => item.label);

    return {
        labels,
        datasets: groups.slice(0, 5).map((group, index) => ({
            label: group.name,
            data: labels.map((month) => group.points?.find((point: any) => point.label === month)?.score ?? null),
            borderColor: palette[index % palette.length],
            backgroundColor: `${palette[index % palette.length]}22`,
            tension: 0.35,
            pointRadius: 3,
            spanGaps: true,
        })),
    };
});

const gradeTrendData = groupedTrendData(props.analytics.trendByGrade || []);
const classTrendData = groupedTrendData(props.analytics.trendByClass || []);
const subjectTrendData = groupedTrendData(props.analytics.trendBySubject || []);

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        y: { beginAtZero: true, max: 100, grid: { color: '#eef2f7' } },
        x: { grid: { display: false } },
    },
};

const lineOptions = {
    ...chartOptions,
    plugins: { legend: { display: true, position: 'bottom' as const, labels: { usePointStyle: true, font: { size: 10 } } } },
};

const doughnutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '68%',
    plugins: { legend: { position: 'bottom' as const, labels: { usePointStyle: true, font: { size: 11 } } } },
};

const scoreClass = (score: number) => {
    if (score >= 80) return 'text-emerald-600';
    if (score >= 60) return 'text-blue-600';
    if (score >= 40) return 'text-amber-600';
    return 'text-rose-600';
};

const levelFor = (score: number) => {
    if (score >= 80) return 'EE';
    if (score >= 60) return 'ME';
    if (score >= 40) return 'AE';
    return 'BE';
};

const topNames = (items: Array<any>, count = 3) =>
    items.slice(0, count).map((item) => `${item.name} (${item.score}%)`).join(', ') || 'No data yet';

const runAgent = () => {
    const prompt = agentPrompt.value.toLowerCase();
    const selectedGrade = props.grades.find((grade) => String(grade.id) === String(localFilters.value.grade_level_id));
    const selectedClass = props.classes.find((klass) => String(klass.id) === String(localFilters.value.class_id));
    const selectedSubject = props.subjects.find((subject) => String(subject.id) === String(localFilters.value.subject_id));

    if (prompt.includes('print') || prompt.includes('pdf')) {
        agentAnswer.value = `Print link ready: ${printUrl({})}. Narrow the filters first for a specific grade, class, or subject.`;
        return;
    }

    if (prompt.includes('grade')) {
        agentAnswer.value = selectedGrade
            ? `${selectedGrade.name}: ${topNames(props.rankings.students.filter((student) => String(student.grade_level_id) === String(selectedGrade.id)))}. Grade print link: ${printUrl({ grade_level_id: selectedGrade.id })}`
            : `Top grade ranking: ${topNames(props.rankings.grades)}. Select a grade filter to print one grade.`;
        return;
    }

    if (prompt.includes('class')) {
        agentAnswer.value = selectedClass
            ? `${selectedClass.name}: ${topNames(props.rankings.students.filter((student) => String(student.current_class_id) === String(selectedClass.id)))}. Class print link: ${printUrl({ class_id: selectedClass.id })}`
            : `Top class ranking: ${topNames(props.rankings.classes)}. Select a class filter to print one class.`;
        return;
    }

    if (prompt.includes('subject')) {
        agentAnswer.value = selectedSubject
            ? `${selectedSubject.name}: current subject mean appears in the subject ranking when that filter is applied. Subject print link: ${printUrl({ subject_id: selectedSubject.id })}`
            : `Top subject ranking: ${topNames(props.rankings.subjects)}. Select a subject filter to print one subject.`;
        return;
    }

    agentAnswer.value = `Overall mean is ${props.analytics.overallMean}%. Top class: ${props.rankings.topClass?.name || 'No data'} (${props.rankings.topClass?.score || 0}%). Top grade: ${props.rankings.topGrade?.name || 'No data'} (${props.rankings.topGrade?.score || 0}%). Top subject: ${props.rankings.topSubject?.name || 'No data'} (${props.rankings.topSubject?.score || 0}%).`;
};
</script>

<template>
    <Head title="Assessment Results" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex h-full max-w-[1600px] flex-1 flex-col gap-6 p-4 pb-20 sm:p-6 md:p-8">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">Assessment Results</h1>
                    <p class="text-xs text-muted-foreground">
                        {{ activeTerm?.name || 'Current Term' }} / {{ activeYear?.name || 'Academic Year' }}
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Button variant="outline" as-child class="h-10 rounded-lg text-xs font-semibold">
                        <a :href="templateUrl">
                            <FileSpreadsheet class="mr-2 h-4 w-4" />
                            CSV Template
                        </a>
                    </Button>
                    <Button as-child class="h-10 rounded-lg text-xs font-semibold">
                        <a :href="pdfUrl" target="_blank">
                            <Download class="mr-2 h-4 w-4" />
                            Download PDF
                        </a>
                    </Button>
                </div>
            </div>

            <Card class="rounded-xl border-border shadow-sm">
                <CardHeader class="border-b bg-muted/5">
                    <CardTitle class="flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-muted-foreground">
                        <Filter class="h-4 w-4 text-primary" />
                        Results Filters
                    </CardTitle>
                </CardHeader>
                <CardContent class="grid gap-4 p-5 lg:grid-cols-[1.4fr_1fr_1fr_1fr_auto] lg:items-end">
                    <div class="space-y-2">
                        <label class="text-xs font-medium text-muted-foreground">Search</label>
                        <div class="relative">
                            <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/50" />
                            <Input v-model="localFilters.search" class="h-10 rounded-lg pl-9" placeholder="Learner, admission number, or assessment..." @keyup.enter="applyFilters" />
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-medium text-muted-foreground">Grade</label>
                        <SearchableSelect v-model="localFilters.grade_level_id" :options="[{ id: 'all', name: 'All Grades' }, ...grades]" placeholder="All Grades" />
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-medium text-muted-foreground">Class</label>
                        <SearchableSelect v-model="localFilters.class_id" :options="[{ id: 'all', name: 'All Classes' }, ...classes]" placeholder="All Classes" />
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-medium text-muted-foreground">Subject</label>
                        <SearchableSelect v-model="localFilters.subject_id" :options="[{ id: 'all', name: 'All Subjects' }, ...subjects]" placeholder="All Subjects" />
                    </div>
                    <div class="flex gap-2">
                        <Button class="h-10 rounded-lg px-5 text-xs font-semibold" @click="applyFilters">Apply</Button>
                        <Button variant="outline" class="h-10 rounded-lg px-4 text-xs font-semibold" @click="clearFilters">Reset</Button>
                    </div>
                </CardContent>
            </Card>

            <Card class="rounded-xl border-border shadow-sm">
                <CardHeader class="border-b bg-muted/5">
                    <CardTitle class="flex items-center gap-2 text-sm font-bold">
                        <Bot class="h-4 w-4 text-primary" />
                        Results Analysis Agent
                    </CardTitle>
                </CardHeader>
                <CardContent class="grid gap-4 p-5 lg:grid-cols-[1.2fr_1fr]">
                    <div class="space-y-3">
                        <div class="flex gap-2">
                            <Input
                                v-model="agentPrompt"
                                class="h-10 rounded-lg"
                                placeholder="Ask: print Grade 4, analyze class, top subject, overall summary..."
                                @keyup.enter="runAgent"
                            />
                            <Button class="h-10 rounded-lg px-4" @click="runAgent">
                                <Send class="h-4 w-4" />
                            </Button>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <Button variant="outline" size="sm" class="h-8 rounded-lg text-xs" @click="agentPrompt = 'overall summary'; runAgent()">Summary</Button>
                            <Button variant="outline" size="sm" class="h-8 rounded-lg text-xs" @click="agentPrompt = 'class analysis'; runAgent()">Class Analysis</Button>
                            <Button variant="outline" size="sm" class="h-8 rounded-lg text-xs" @click="agentPrompt = 'grade analysis'; runAgent()">Grade Analysis</Button>
                            <Button variant="outline" size="sm" class="h-8 rounded-lg text-xs" @click="agentPrompt = 'subject analysis'; runAgent()">Subject Analysis</Button>
                        </div>
                    </div>
                    <div class="rounded-lg border bg-muted/5 p-4 text-sm leading-relaxed text-muted-foreground">
                        {{ agentAnswer }}
                    </div>
                </CardContent>
            </Card>

            <Card class="rounded-xl border-border shadow-sm">
                <CardHeader class="border-b bg-muted/5">
                    <CardTitle class="flex items-center gap-2 text-sm font-bold">
                        <Printer class="h-4 w-4 text-primary" />
                        Print Center
                    </CardTitle>
                </CardHeader>
                <CardContent class="grid gap-3 p-5 sm:grid-cols-2 xl:grid-cols-5">
                    <Button variant="outline" class="h-10 rounded-lg text-xs font-semibold" as-child>
                        <a :href="printUrl({})" target="_blank">Current Filtered Results</a>
                    </Button>
                    <Button variant="outline" class="h-10 rounded-lg text-xs font-semibold" as-child>
                        <a :href="printUrl({ grade_level_id: localFilters.grade_level_id, class_id: 'all', subject_id: 'all' })" target="_blank">Entire Grade</a>
                    </Button>
                    <Button variant="outline" class="h-10 rounded-lg text-xs font-semibold" as-child>
                        <a :href="printUrl({ class_id: localFilters.class_id, subject_id: 'all' })" target="_blank">Entire Class</a>
                    </Button>
                    <Button variant="outline" class="h-10 rounded-lg text-xs font-semibold" as-child>
                        <a :href="printUrl({ grade_level_id: localFilters.grade_level_id, class_id: 'all' })" target="_blank">Subject Ranking / Grade</a>
                    </Button>
                    <Button variant="outline" class="h-10 rounded-lg text-xs font-semibold" as-child>
                        <a :href="printUrl({ class_id: localFilters.class_id })" target="_blank">Subject Ranking / Class</a>
                    </Button>
                </CardContent>
            </Card>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <Card class="rounded-xl border-border">
                    <CardContent class="flex items-center justify-between p-5">
                        <div>
                            <p class="text-xs font-medium text-muted-foreground">Overall Mean</p>
                            <p class="mt-1 text-3xl font-bold" :class="scoreClass(analytics.overallMean)">{{ analytics.overallMean }}%</p>
                        </div>
                        <BarChart3 class="h-9 w-9 text-primary/60" />
                    </CardContent>
                </Card>
                <Card class="rounded-xl border-border">
                    <CardContent class="flex items-center justify-between p-5">
                        <div>
                            <p class="text-xs font-medium text-muted-foreground">Learners Ranked</p>
                            <p class="mt-1 text-3xl font-bold">{{ analytics.activeStudents }}</p>
                        </div>
                        <Users class="h-9 w-9 text-primary/60" />
                    </CardContent>
                </Card>
                <Card class="rounded-xl border-border">
                    <CardContent class="flex items-center justify-between p-5">
                        <div>
                            <p class="text-xs font-medium text-muted-foreground">Assessments</p>
                            <p class="mt-1 text-3xl font-bold">{{ analytics.totalAssessments }}</p>
                        </div>
                        <BookOpen class="h-9 w-9 text-primary/60" />
                    </CardContent>
                </Card>
                <Card class="rounded-xl border-border">
                    <CardContent class="flex items-center justify-between p-5">
                        <div>
                            <p class="text-xs font-medium text-muted-foreground">Top Class</p>
                            <p class="mt-1 truncate text-xl font-bold">{{ rankings.topClass?.name || 'No Data' }}</p>
                            <p class="text-xs text-muted-foreground">{{ rankings.topClass?.score || 0 }}%</p>
                        </div>
                        <Trophy class="h-9 w-9 text-amber-500/80" />
                    </CardContent>
                </Card>
            </div>

            <div class="grid gap-6 xl:grid-cols-3">
                <Card class="rounded-xl border-border xl:col-span-2">
                    <CardHeader class="flex flex-row items-center justify-between border-b bg-muted/5">
                        <CardTitle class="flex items-center gap-2 text-sm font-bold">
                            <LineChart class="h-4 w-4 text-primary" />
                            Performance Trend
                        </CardTitle>
                        <Button variant="outline" size="sm" class="h-8 rounded-lg text-xs" as-child>
                            <a :href="printUrl({})" target="_blank">
                                <Printer class="mr-2 h-3.5 w-3.5" />
                                Print View
                            </a>
                        </Button>
                    </CardHeader>
                    <CardContent class="h-[300px] p-5">
                        <Line v-if="analytics.trend.length" :data="lineData" :options="lineOptions" />
                        <div v-else class="flex h-full items-center justify-center text-sm text-muted-foreground">No trend data yet.</div>
                    </CardContent>
                </Card>
                <Card class="rounded-xl border-border">
                    <CardHeader class="border-b bg-muted/5">
                        <CardTitle class="flex items-center gap-2 text-sm font-bold">
                            <Award class="h-4 w-4 text-primary" />
                            Rating Distribution
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="h-[300px] p-5">
                        <Doughnut v-if="analytics.activeStudents" :data="distributionData" :options="doughnutOptions" />
                        <div v-else class="flex h-full items-center justify-center text-sm text-muted-foreground">No ratings yet.</div>
                    </CardContent>
                </Card>
            </div>

            <div class="grid gap-6 xl:grid-cols-3">
                <Card class="rounded-xl border-border">
                    <CardHeader class="flex flex-row items-center justify-between border-b bg-muted/5">
                        <CardTitle class="text-sm font-bold">Grade Trend Lines</CardTitle>
                        <Button variant="outline" size="sm" class="h-8 rounded-lg text-xs" as-child>
                            <a :href="printUrl({ grade_level_id: localFilters.grade_level_id })" target="_blank">Print Grade</a>
                        </Button>
                    </CardHeader>
                    <CardContent class="h-[260px] p-5">
                        <Line v-if="analytics.trendByGrade.length" :data="gradeTrendData" :options="lineOptions" />
                        <div v-else class="flex h-full items-center justify-center text-sm text-muted-foreground">No grade trend data yet.</div>
                    </CardContent>
                </Card>
                <Card class="rounded-xl border-border">
                    <CardHeader class="flex flex-row items-center justify-between border-b bg-muted/5">
                        <CardTitle class="text-sm font-bold">Class Trend Lines</CardTitle>
                        <Button variant="outline" size="sm" class="h-8 rounded-lg text-xs" as-child>
                            <a :href="printUrl({ class_id: localFilters.class_id })" target="_blank">Print Class</a>
                        </Button>
                    </CardHeader>
                    <CardContent class="h-[260px] p-5">
                        <Line v-if="analytics.trendByClass.length" :data="classTrendData" :options="lineOptions" />
                        <div v-else class="flex h-full items-center justify-center text-sm text-muted-foreground">No class trend data yet.</div>
                    </CardContent>
                </Card>
                <Card class="rounded-xl border-border">
                    <CardHeader class="flex flex-row items-center justify-between border-b bg-muted/5">
                        <CardTitle class="text-sm font-bold">Subject Trend Lines</CardTitle>
                        <Button variant="outline" size="sm" class="h-8 rounded-lg text-xs" as-child>
                            <a :href="printUrl({ subject_id: localFilters.subject_id })" target="_blank">Print Subject</a>
                        </Button>
                    </CardHeader>
                    <CardContent class="h-[260px] p-5">
                        <Line v-if="analytics.trendBySubject.length" :data="subjectTrendData" :options="lineOptions" />
                        <div v-else class="flex h-full items-center justify-center text-sm text-muted-foreground">No subject trend data yet.</div>
                    </CardContent>
                </Card>
            </div>

            <div class="grid gap-6 xl:grid-cols-2">
                <Card class="rounded-xl border-border">
                    <CardHeader class="flex flex-row items-center justify-between border-b bg-muted/5">
                        <CardTitle class="flex items-center gap-2 text-sm font-bold">
                            <LayoutGrid class="h-4 w-4 text-primary" />
                            Class Ranking
                        </CardTitle>
                        <Button variant="outline" size="sm" class="h-8 rounded-lg text-xs" as-child>
                            <a :href="printUrl({})" target="_blank">
                                <Printer class="mr-2 h-3.5 w-3.5" />
                                Print
                            </a>
                        </Button>
                    </CardHeader>
                    <CardContent class="grid gap-5 p-5 lg:grid-cols-[1fr_1.2fr]">
                        <div class="space-y-3">
                            <div v-for="item in rankings.classes.slice(0, 6)" :key="item.id" class="flex items-center justify-between rounded-lg border p-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold">#{{ item.rank }} {{ item.name }}</p>
                                    <p class="text-xs text-muted-foreground">{{ item.students }} learners / {{ item.assessments }} assessments</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <Badge variant="outline" :class="scoreClass(item.score)">{{ item.score }}%</Badge>
                                    <Button variant="ghost" size="sm" class="h-8 rounded-lg px-2" as-child>
                                        <a :href="printUrl({ class_id: item.id })" target="_blank" title="Print class results">
                                            <Printer class="h-3.5 w-3.5" />
                                        </a>
                                    </Button>
                                </div>
                            </div>
                        </div>
                        <div class="h-[240px]">
                            <Bar v-if="analytics.classAnalysis.length" :data="classBarData" :options="chartOptions" />
                        </div>
                    </CardContent>
                </Card>

                <Card class="rounded-xl border-border">
                    <CardHeader class="flex flex-row items-center justify-between border-b bg-muted/5">
                        <CardTitle class="flex items-center gap-2 text-sm font-bold">
                            <BookOpen class="h-4 w-4 text-primary" />
                            Subject Ranking
                        </CardTitle>
                        <Button variant="outline" size="sm" class="h-8 rounded-lg text-xs" as-child>
                            <a :href="printUrl({})" target="_blank">
                                <Printer class="mr-2 h-3.5 w-3.5" />
                                Print
                            </a>
                        </Button>
                    </CardHeader>
                    <CardContent class="grid gap-5 p-5 lg:grid-cols-[1fr_1.2fr]">
                        <div class="space-y-3">
                            <div v-for="item in rankings.subjects.slice(0, 6)" :key="item.id" class="flex items-center justify-between rounded-lg border p-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold">#{{ item.rank }} {{ item.name }}</p>
                                    <p class="text-xs text-muted-foreground">{{ item.students }} learners / {{ item.records }} records</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <Badge variant="outline" :class="scoreClass(item.score)">{{ item.score }}%</Badge>
                                    <Button variant="ghost" size="sm" class="h-8 rounded-lg px-2" as-child>
                                        <a :href="printUrl({ subject_id: item.id })" target="_blank" title="Print subject results">
                                            <Printer class="h-3.5 w-3.5" />
                                        </a>
                                    </Button>
                                </div>
                            </div>
                        </div>
                        <div class="h-[240px]">
                            <Bar v-if="analytics.subjectAnalysis.length" :data="subjectBarData" :options="chartOptions" />
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="grid gap-6 xl:grid-cols-[1fr_1.4fr]">
                <Card class="rounded-xl border-border">
                    <CardHeader class="flex flex-row items-center justify-between border-b bg-muted/5">
                        <CardTitle class="flex items-center gap-2 text-sm font-bold">
                            <GraduationCap class="h-4 w-4 text-primary" />
                            Grade Ranking
                        </CardTitle>
                        <Button variant="outline" size="sm" class="h-8 rounded-lg text-xs" as-child>
                            <a :href="printUrl({})" target="_blank">
                                <Printer class="mr-2 h-3.5 w-3.5" />
                                Print
                            </a>
                        </Button>
                    </CardHeader>
                    <CardContent class="space-y-3 p-5">
                        <div v-for="(item, index) in rankings.grades" :key="item.id" class="flex items-center gap-3 rounded-lg border p-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg font-bold text-white" :style="{ backgroundColor: palette[index % palette.length] }">
                                {{ item.rank }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-bold">{{ item.name }}</p>
                                <p class="text-xs text-muted-foreground">{{ item.students }} learners assessed</p>
                            </div>
                            <p class="text-lg font-bold" :class="scoreClass(item.score)">{{ item.score }}%</p>
                            <Button variant="ghost" size="sm" class="h-8 rounded-lg px-2" as-child>
                                <a :href="printUrl({ grade_level_id: item.id })" target="_blank" title="Print grade results">
                                    <Printer class="h-3.5 w-3.5" />
                                </a>
                            </Button>
                        </div>
                        <div v-if="rankings.grades.length === 0" class="py-12 text-center text-sm text-muted-foreground">No grade data yet.</div>
                    </CardContent>
                </Card>

                <Card class="rounded-xl border-border">
                    <CardHeader class="border-b bg-muted/5">
                        <CardTitle class="flex items-center gap-2 text-sm font-bold">
                            <Medal class="h-4 w-4 text-primary" />
                            Learner Ranking
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="p-0">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="border-b bg-muted/5 text-xs uppercase text-muted-foreground">
                                    <tr>
                                        <th class="px-5 py-3">Rank</th>
                                        <th class="px-5 py-3">Learner</th>
                                        <th class="px-5 py-3">Class</th>
                                        <th class="px-5 py-3 text-center">Class Rank</th>
                                        <th class="px-5 py-3 text-center">Grade Rank</th>
                                        <th class="px-5 py-3 text-right">Mean</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr v-for="student in students.data" :key="student.id" class="hover:bg-muted/5">
                                        <td class="px-5 py-4 font-bold">#{{ student.overall_rank }}</td>
                                        <td class="px-5 py-4">
                                            <p class="font-bold">{{ student.name }}</p>
                                            <p class="text-xs text-muted-foreground">{{ student.admission_number }} / {{ student.tests_count }} assessments</p>
                                        </td>
                                        <td class="px-5 py-4">
                                            <p class="font-medium">{{ student.class_name }}</p>
                                            <p class="text-xs text-muted-foreground">{{ student.grade_name }}</p>
                                        </td>
                                        <td class="px-5 py-4 text-center">#{{ student.class_rank }}</td>
                                        <td class="px-5 py-4 text-center">#{{ student.grade_rank }}</td>
                                        <td class="px-5 py-4 text-right">
                                            <p class="text-lg font-bold" :class="scoreClass(student.mean_score)">{{ student.mean_score }}%</p>
                                            <Badge variant="outline" class="mt-1">{{ levelFor(student.mean_score) }}</Badge>
                                        </td>
                                    </tr>
                                    <tr v-if="students.data.length === 0">
                                        <td colspan="6" class="px-5 py-16 text-center text-sm text-muted-foreground">No results match the current filters.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="flex items-center justify-between border-t bg-muted/5 px-5 py-4">
                            <p class="text-xs text-muted-foreground">{{ analytics.recordedResults }} recorded result entries</p>
                            <div class="flex gap-1">
                                <template v-for="(link, i) in students.links" :key="i">
                                    <Button v-if="link.url" variant="outline" size="sm" class="h-8 rounded-lg text-xs" as-child>
                                        <Link :href="link.url" preserve-scroll v-html="link.label"></Link>
                                    </Button>
                                    <Button v-else variant="outline" size="sm" class="h-8 rounded-lg text-xs opacity-40" disabled>
                                        <span v-html="link.label"></span>
                                    </Button>
                                </template>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
