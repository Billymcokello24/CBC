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
            class="mx-auto flex h-full max-w-[1600px] flex-1 animate-in flex-col space-y-8 p-4 pb-20 duration-700 fade-in slide-in-from-bottom-4 sm:p-6 sm:pb-32 md:p-8"
        >
            <!-- Header -->
            <div class="flex flex-col gap-6 px-1 md:flex-row md:items-center md:justify-between">
                <div class="space-y-1">
                    <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">Performance Analytics</h1>
                    <p class="text-xs text-muted-foreground">{{ activeYear?.name || 'Academic Year' }} • {{ activeTerm?.name || 'Current Term' }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <Button variant="outline" class="h-10 rounded-lg border-border bg-card px-4 text-xs font-semibold hover:bg-muted" @click="showUploadDialog = true">
                        <Upload class="mr-2 h-4 w-4 text-primary" />
                        Bulk Import
                    </Button>
                    <Button variant="outline" class="h-10 rounded-lg border-border bg-card px-4 text-xs font-semibold hover:bg-muted">
                        <Download class="mr-2 h-4 w-4 text-primary" />
                        Full Export
                    </Button>
                </div>
            </div>

            <!-- Dashboard Stats -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div v-for="(stat, idx) in [
                    { label: 'Student Body', val: `${students.meta?.total ?? students.data.length} Cohorts`, icon: 'User' },
                    { label: 'Pass Index', val: '82.4% Efficient', icon: 'CheckCircle2' },
                    { label: 'Mean Score', val: '68.4 pts', icon: 'TrendingUp' },
                    { label: 'Engine Load', val: 'Optimized', icon: 'Target' }
                ]" :key="idx" class="group relative overflow-hidden rounded-xl border border-border bg-card p-6 transition-all hover:shadow-md">
                     <div class="absolute -right-4 -top-4 opacity-[0.05] transition-transform duration-700 group-hover:scale-110">
                        <component :is="stat.icon === 'User' ? User : stat.icon === 'CheckCircle2' ? CheckCircle2 : stat.icon === 'TrendingUp' ? TrendingUp : Target" class="h-24 w-24" />
                    </div>
                    <p class="text-xs font-medium text-muted-foreground">{{ stat.label }}</p>
                    <div class="mt-2 flex items-baseline gap-2">
                        <h3 class="text-2xl font-bold tracking-tight">{{ stat.val }}</h3>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                <div class="flex h-12 items-center justify-between border-b border-border/50 bg-muted/5 px-6">
                    <div class="flex items-center gap-2">
                        <BarChart3 class="h-4 w-4 text-primary" />
                        <span class="text-xs font-semibold text-foreground tracking-tight">Analytics Filter</span>
                    </div>
                </div>
                <div class="p-6">
                    <div class="flex flex-col gap-6 md:flex-row md:items-end">
                        <div class="flex-1 space-y-2">
                            <label class="text-xs font-medium text-muted-foreground">Search Profile</label>
                            <div class="relative">
                                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/40" />
                                <Input v-model="searchQuery" placeholder="Filter by name or admission code..." class="h-10 rounded-lg border-border bg-muted/10 pl-10 pr-4 text-sm focus:bg-background" />
                            </div>
                        </div>
                        <div class="flex-1 space-y-2">
                            <label class="text-xs font-medium text-muted-foreground">Class Filter</label>
                            <SearchableSelect
                                v-model="selectedClass"
                                :options="[{ id: 'all', name: 'Every Class' }, ...classes]"
                                placeholder="Cluster Filter"
                                search-placeholder="Find class..."
                                class="h-10"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results Table -->
            <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm transition-all">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-border/50 bg-muted/5 text-[11px] font-bold tracking-wider text-muted-foreground uppercase">
                                <th class="px-6 py-4">Student Profile</th>
                                <th class="px-6 py-4 text-center">Cluster</th>
                                <th class="px-6 py-4 text-center">Load</th>
                                <th class="px-6 py-4 text-center">Mean (%)</th>
                                <th class="px-6 py-4 text-center">Pulse Tracking</th>
                                <th class="px-6 py-4 text-right">Logic</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/50">
                        <tr
                            v-for="student in filteredStudents"
                            :key="student.id"
                            class="group transition-all duration-300 hover:bg-muted/10/70"
                        >
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex h-12 w-12 items-center justify-center rounded-2xl border border-indigo-50 bg-indigo-600/10 text-sm font-bold text-indigo-700  shadow-inner transition-all group-hover:bg-indigo-600 group-hover:text-white"
                                    >
                                        {{ (student.first_name || 'U')[0]
                                        }}{{ (student.last_name || 'S')[0] }}
                                    </div>
                                    <div>
                                        <div
                                            class="leading-tight font-bold text-foreground transition-colors group-hover:text-indigo-700"
                                        >
                                            {{ student.first_name }}
                                            {{ student.last_name }}
                                        </div>
                                        <div
                                            class="mt-1 text-xs font-bold tracking-tight text-muted-foreground/80 "
                                        >
                                            ID: {{ student.admission_number }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <Badge
                                    variant="outline"
                                    class="rounded-lg border-border/50 bg-muted/10 px-2 py-0.5 text-xs font-bold text-slate-600 "
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
                                        class="text-sm font-bold text-foreground"
                                        >{{ student.tests_count || 0 }}</span
                                    >
                                    <span
                                        class="mt-0.5 text-xs font-bold tracking-tight text-muted-foreground/80 "
                                        >Tests Logged</span
                                    >
                                </div>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <div class="inline-flex flex-col items-center">
                                    <span
                                        class="text-lg font-bold tracking-tight"
                                        :class="
                                            getPerformanceColor(
                                                student.mean_score,
                                            )
                                        "
                                        >{{ student.mean_score }}%</span
                                    >
                                    <span
                                        class="mt-0.5 text-xs font-bold tracking-tight text-muted-foreground/80 "
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
                                        class="text-xs font-bold tracking-tight text-indigo-600  transition-transform group-hover:scale-105"
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
                                            class="font-pulsar w-56 overflow-hidden rounded-xl border-border/50 p-1 shadow-lg"
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
                                    class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-full border border-border bg-muted/10 shadow-inner"
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
                                    class="mx-auto max-w-sm font-medium text-muted-foreground"
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
            <div class="flex h-16 items-center justify-between border-t border-border/50 px-6 bg-muted/5 rounded-xl border mt-auto">
                <p class="text-xs text-muted-foreground font-medium">Node Registry: {{ filteredStudents.length }} Entries</p>
                <div class="flex items-center gap-1.5">
                    <template v-for="(link, i) in students.links" :key="i">
                        <Button v-if="link.url && !link.label.includes('Next') && !link.label.includes('Previous')" variant="outline" size="sm" :class="['h-8 w-8 rounded-lg text-xs font-medium transition-all', link.active ? 'border-primary bg-primary text-white shadow-sm' : 'border-border bg-card hover:bg-muted text-muted-foreground']" as-child>
                            <Link :href="link.url" v-html="link.label"></Link>
                        </Button>
                        <Button v-else-if="link.label.includes('Previous')" variant="outline" size="sm" class="h-8 rounded-lg px-3 text-xs font-medium border-border bg-card hover:bg-muted disabled:opacity-30" :disabled="!link.url" as-child>
                             <Link v-if="link.url" :href="link.url">Prev</Link>
                             <span v-else>Prev</span>
                        </Button>
                        <Button v-else-if="link.label.includes('Next')" variant="outline" size="sm" class="h-8 rounded-lg px-3 text-xs font-medium border-border bg-card hover:bg-muted disabled:opacity-30" :disabled="!link.url" as-child>
                             <Link v-if="link.url" :href="link.url">Next</Link>
                             <span v-else>Next</span>
                        </Button>
                    </template>
                </div>
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
