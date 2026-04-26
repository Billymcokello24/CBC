<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    BarChart3,
    Search,
    Filter,
    Download,
    Upload,
    ChevronRight,
    User,
    GraduationCap,
    Calendar,
    ArrowUpRight,
    CheckCircle2,
    AlertCircle,
    LayoutGrid,
    MoreHorizontal,
    FileSpreadsheet,
    FileText,
    TrendingUp,
    Zap,
    ArrowRight,
    Target,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
} from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import BulkUploadDialog from '@/components/assessments/BulkUploadDialog.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    students: {
        data: Array<any>;
        links: Array<any>;
        meta: any;
    };
    activeYear: any;
    activeTerm: any;
    classes: Array<any>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Assessments Hub', href: '/assessments' },
    { title: 'Performance Results', href: '/assessments/results' },
];

const searchQuery = ref('');
const selectedClass = ref('all');
const showUploadDialog = ref(false);

const filteredStudents = computed(() => {
    return props.students.data.filter((student) => {
        const full_name =
            (student.first_name || '') + ' ' + (student.last_name || '');
        const matchesSearch =
            full_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            (student.admission_number || '')
                .toLowerCase()
                .includes(searchQuery.value.toLowerCase());
        const matchesClass =
            selectedClass.value === 'all' ||
            student.current_class_id?.toString() === selectedClass.value;
        return matchesSearch && matchesClass;
    });
});

const getPerformanceColor = (average: number) => {
    if (average >= 80) return 'text-emerald-600';
    if (average >= 60) return 'text-indigo-600';
    if (average >= 40) return 'text-amber-600';
    return 'text-rose-600';
};
</script>

<template>
    <Head title="Performance Analytics" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="font-pulsar mx-auto mt-2 flex h-full max-w-[1400px] flex-1 flex-col gap-6 p-6"
        >
            <!-- Header -->
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl border border-indigo-100 bg-indigo-500/10 shadow-inner"
                    >
                        <BarChart3 class="h-6 w-6 text-indigo-600" />
                    </div>
                    <div>
                        <h1
                            class="text-2xl font-bold tracking-tight text-slate-900"
                        >
                            Performance Analytics
                        </h1>
                        <p
                            class="flex items-center gap-2 font-medium text-muted-foreground"
                        >
                            {{ activeYear?.name || 'Academic Year' }}
                            <span class="text-slate-300">•</span>
                            {{ activeTerm?.name || 'Current Term' }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        class="font-pulsar h-10 border-slate-200"
                        @click="showUploadDialog = true"
                    >
                        <Upload class="mr-2 h-4 w-4" />Bulk Import
                    </Button>
                    <Button
                        variant="outline"
                        class="font-pulsar h-10 border-slate-200"
                    >
                        <Download class="mr-2 h-4 w-4" />Full Export
                    </Button>
                </div>
            </div>

            <!-- Dashboard Stats -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    class="group flex items-center gap-4 rounded-2xl border border-l-4 border-l-slate-900 bg-card p-5 shadow-sm transition-all hover:shadow-md"
                >
                    <div
                        class="rounded-xl bg-slate-900/5 p-3 text-slate-900 transition-all"
                    >
                        <User class="h-5 w-5" />
                    </div>
                    <div>
                        <p
                            class="mb-1 text-xs leading-none font-bold tracking-tight text-slate-400 uppercase"
                        >
                            Student Body
                        </p>
                        <p class="text-xl font-bold text-slate-900">
                            {{
                                students.meta?.total ?? students.data.length
                            }}
                            Cohorts
                        </p>
                    </div>
                </div>
                <div
                    class="group flex items-center gap-4 rounded-2xl border border-l-4 border-l-emerald-500 bg-card p-5 shadow-sm transition-all hover:shadow-md"
                >
                    <div
                        class="rounded-xl bg-emerald-500/10 p-3 text-emerald-600 transition-all"
                    >
                        <CheckCircle2 class="h-5 w-5" />
                    </div>
                    <div>
                        <p
                            class="mb-1 text-xs leading-none font-bold tracking-tight text-slate-400 uppercase"
                        >
                            Pass Index
                        </p>
                        <p class="text-xl font-bold text-emerald-600">
                            82.4% Efficient
                        </p>
                    </div>
                </div>
                <div
                    class="group flex items-center gap-4 rounded-2xl border border-l-4 border-l-indigo-600 bg-card p-5 shadow-sm transition-all hover:shadow-md"
                >
                    <div
                        class="rounded-xl bg-indigo-500/10 p-3 text-indigo-600 transition-all"
                    >
                        <TrendingUp class="h-5 w-5" />
                    </div>
                    <div>
                        <p
                            class="mb-1 text-xs leading-none font-bold tracking-tight text-slate-400 uppercase"
                        >
                            Mean Score
                        </p>
                        <p class="text-xl font-bold text-indigo-600">
                            68.4 pts
                        </p>
                    </div>
                </div>
                <div
                    class="group flex items-center gap-4 rounded-2xl border border-l-4 border-l-purple-600 bg-card p-5 shadow-sm transition-all hover:shadow-md"
                >
                    <div
                        class="rounded-xl bg-purple-500/10 p-3 text-purple-600 transition-all"
                    >
                        <Target class="h-5 w-5" />
                    </div>
                    <div>
                        <p
                            class="mb-1 text-xs leading-none font-bold tracking-tight text-slate-400 uppercase"
                        >
                            Engine Load
                        </p>
                        <p
                            class="text-xl font-bold tracking-tighter text-purple-600 uppercase"
                        >
                            Optimized
                        </p>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div
                class="flex flex-col gap-4 rounded-xl border bg-white p-4 shadow-sm md:flex-row md:items-center"
            >
                <div class="relative flex-1 md:max-w-md">
                    <Search
                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 font-medium text-muted-foreground"
                    />
                    <Input
                        v-model="searchQuery"
                        placeholder="Filter by name or admission code..."
                        class="h-11 rounded-xl border-slate-200 pl-9"
                    />
                </div>
                <div class="flex items-center gap-3">
                    <SearchableSelect
                        v-model="selectedClass"
                        :options="[
                            { id: 'all', name: 'Every Class' },
                            ...classes,
                        ]"
                        placeholder="Cluster Filter"
                        search-placeholder="Find class..."
                        class="h-11 w-full md:w-[220px]"
                    />
                    <Button
                        variant="outline"
                        class="h-11 rounded-xl border-slate-200 px-4 text-xs font-bold tracking-tight uppercase transition-colors hover:bg-slate-50"
                    >
                        <Filter class="mr-2 h-4 w-4" /> Advance Filter
                    </Button>
                </div>
            </div>

            <!-- Results Table -->
            <div
                class="overflow-hidden overflow-x-auto rounded-xl border border-t-8 border-t-indigo-500 bg-card shadow-sm"
            >
                <table class="w-full text-left">
                    <thead>
                        <tr class="font-pulsar border-b bg-slate-50">
                            <th
                                class="px-6 py-5 text-xs font-bold tracking-tight text-slate-500 uppercase"
                            >
                                Student Profile
                            </th>
                            <th
                                class="px-6 py-5 text-center text-xs font-bold tracking-tight text-slate-500 uppercase"
                            >
                                Cluster
                            </th>
                            <th
                                class="px-6 py-5 text-center text-xs font-bold tracking-tight text-slate-500 uppercase"
                            >
                                Load
                            </th>
                            <th
                                class="px-6 py-5 text-center text-xs font-bold tracking-tight text-slate-500 uppercase"
                            >
                                Mean (%)
                            </th>
                            <th
                                class="px-6 py-5 text-center text-xs font-bold tracking-tight text-slate-500 uppercase"
                            >
                                Pulse Tracking
                            </th>
                            <th
                                class="px-6 py-5 text-right text-xs font-bold tracking-tight text-slate-500 uppercase"
                            >
                                Logic
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y text-sm font-medium">
                        <tr
                            v-for="student in filteredStudents"
                            :key="student.id"
                            class="group transition-all duration-300 hover:bg-slate-50/70"
                        >
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex h-12 w-12 items-center justify-center rounded-2xl border border-indigo-50 bg-indigo-600/10 text-sm font-bold text-indigo-700 uppercase shadow-inner transition-all group-hover:bg-indigo-600 group-hover:text-white"
                                    >
                                        {{ (student.first_name || 'U')[0]
                                        }}{{ (student.last_name || 'S')[0] }}
                                    </div>
                                    <div>
                                        <div
                                            class="leading-tight font-bold text-slate-900 transition-colors group-hover:text-indigo-700"
                                        >
                                            {{ student.first_name }}
                                            {{ student.last_name }}
                                        </div>
                                        <div
                                            class="mt-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                        >
                                            ID: {{ student.admission_number }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <Badge
                                    variant="outline"
                                    class="rounded-lg border-slate-200 bg-slate-50 px-2 py-0.5 text-xs font-bold text-slate-600 uppercase"
                                >
                                    {{
                                        student.current_class?.name ||
                                        'Isolated'
                                    }}
                                </Badge>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <div class="inline-flex flex-col items-center">
                                    <span
                                        class="text-sm font-bold text-slate-900"
                                        >{{ student.tests_count || 0 }}</span
                                    >
                                    <span
                                        class="mt-0.5 text-xs font-bold tracking-tighter text-slate-400 uppercase"
                                        >Tests Logged</span
                                    >
                                </div>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <div class="inline-flex flex-col items-center">
                                    <span
                                        class="text-lg font-bold tracking-tighter"
                                        :class="
                                            getPerformanceColor(
                                                student.mean_score,
                                            )
                                        "
                                        >{{ student.mean_score }}%</span
                                    >
                                    <span
                                        class="mt-0.5 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                        >Consensus</span
                                    >
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex flex-col items-center gap-2">
                                    <div
                                        class="h-2 w-28 overflow-hidden rounded-full border bg-slate-100 shadow-inner"
                                    >
                                        <div
                                            class="h-full rounded-full bg-linear-to-r from-indigo-500 to-purple-500"
                                            :style="{
                                                width: student.mean_score + '%',
                                            }"
                                        ></div>
                                    </div>
                                    <span
                                        class="text-xs font-bold tracking-tight text-indigo-600 uppercase transition-transform group-hover:scale-105"
                                        >Trajectory
                                        {{ student.trajectory }}</span
                                    >
                                </div>
                            </td>
                            <td class="px-6 py-5 text-right">
                                <div
                                    class="flex items-center justify-end gap-1"
                                >
                                    <Button
                                        size="icon"
                                        variant="ghost"
                                        class="h-9 w-9 rounded-xl text-slate-300 transition-all hover:bg-indigo-50 hover:text-indigo-600"
                                        as-child
                                        title="Performance Log"
                                    >
                                        <Link
                                            :href="`/assessments/results/${student.id}`"
                                        >
                                            <ArrowUpRight class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                class="h-9 w-9 rounded-xl text-slate-300 transition-all hover:text-slate-600"
                                                ><MoreHorizontal
                                                    class="h-4 w-4"
                                            /></Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent
                                            align="end"
                                            class="font-pulsar w-56 overflow-hidden rounded-xl border-slate-200 p-1 shadow-lg"
                                        >
                                            <DropdownMenuItem
                                                class="rounded-lg font-bold"
                                                ><FileText
                                                    class="mr-3 h-4 w-4 text-indigo-600"
                                                />
                                                View
                                                Transcript</DropdownMenuItem
                                            >
                                            <DropdownMenuItem
                                                class="rounded-lg font-bold"
                                                ><TrendingUp
                                                    class="mr-3 h-4 w-4 text-emerald-500"
                                                />
                                                Progress Curve</DropdownMenuItem
                                            >
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem
                                                class="rounded-lg font-bold text-indigo-600"
                                                >Export Student ID
                                                Data</DropdownMenuItem
                                            >
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredStudents.length === 0">
                            <td colspan="6" class="px-6 py-24 text-center">
                                <div
                                    class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-full border border-slate-100 bg-slate-50 shadow-inner"
                                >
                                    <BarChart3
                                        class="h-10 w-10 text-slate-200"
                                    />
                                </div>
                                <h3
                                    class="mb-2 text-2xl font-bold text-slate-800"
                                >
                                    Registry Silent
                                </h3>
                                <p
                                    class="mx-auto max-w-sm font-medium text-slate-500"
                                >
                                    No student records found matching the
                                    current search parameters for this academic
                                    cluster.
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer Pagination -->
            <div
                class="mt-6 flex flex-col gap-6 border-t py-12 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-8">
                    <p
                        class="text-xs font-medium tracking-tight whitespace-nowrap text-slate-400 uppercase"
                    >
                        Node Registry: {{ filteredStudents.length }} Entries
                    </p>
                    <div class="flex items-center gap-3">
                        <span
                            class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                            >Density:</span
                        >
                        <select
                            class="h-8 rounded-lg border-slate-200 bg-white px-2 text-xs font-bold text-slate-600 uppercase focus:ring-indigo-500"
                        >
                            <option>20 Rows</option>
                            <option>50 Rows</option>
                            <option>High Density</option>
                        </select>
                    </div>
                </div>
                <div
                    class="flex items-center gap-1.5 overflow-x-auto pb-2 sm:pb-0"
                >
                    <Button
                        v-for="(link, i) in students.links"
                        :key="i"
                        variant="ghost"
                        size="sm"
                        :disabled="!link.url || link.active"
                        :class="[
                            'h-9 min-w-[36px] rounded-xl text-xs font-bold tracking-tighter uppercase',
                            link.active
                                ? 'bg-indigo-600 text-white shadow-lg hover:bg-indigo-700'
                                : 'text-slate-400 hover:bg-slate-100 hover:text-indigo-600',
                        ]"
                        as-child
                    >
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            v-html="link.label"
                        />
                        <span v-else v-html="link.label" />
                    </Button>
                </div>
            </div>
        </div>

        <!-- Bulk Upload Dialog -->
        <BulkUploadDialog
            v-model:open="showUploadDialog"
            title="Registry Bulk Import"
            description="Broadcast student performance data to the central engine. Ensure CSV normalization using the Pulsar template."
            template-url="/assessments/results/export"
            upload-url="/assessments/results/import"
        />
    </AppLayout>
</template>
