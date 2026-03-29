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
    Target
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { 
    Table, 
    TableBody, 
    TableCell, 
    TableHead, 
    TableHeader, 
    TableRow 
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
    return props.students.data.filter(student => {
        const full_name = (student.first_name || '') + ' ' + (student.last_name || '');
        const matchesSearch = full_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                             (student.admission_number || '').toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesClass = selectedClass.value === 'all' || student.current_class_id?.toString() === selectedClass.value;
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
        <div class="flex h-full flex-1 flex-col gap-6 p-6 font-pulsar mt-2 max-w-[1400px] mx-auto">
            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-500/10 shadow-inner border border-indigo-100">
                        <BarChart3 class="h-6 w-6 text-indigo-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Performance Analytics</h1>
                        <p class="text-muted-foreground font-medium flex items-center gap-2 italic">
                            {{ activeYear?.name || 'Academic Year' }} <span class="text-slate-300">•</span> {{ activeTerm?.name || 'Current Term' }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" class="font-pulsar h-10 border-slate-200" @click="showUploadDialog = true">
                        <Upload class="mr-2 h-4 w-4" />Bulk Import
                    </Button>
                    <Button variant="outline" class="font-pulsar h-10 border-slate-200">
                        <Download class="mr-2 h-4 w-4" />Full Export
                    </Button>
                </div>
            </div>

            <!-- Dashboard Stats -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border bg-card p-5 shadow-sm border-l-4 border-l-slate-900 flex items-center gap-4 group hover:shadow-md transition-all">
                    <div class="rounded-xl bg-slate-900/5 p-3 text-slate-900 transition-all"><User class="h-5 w-5" /></div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Student Body</p>
                        <p class="text-xl font-black text-slate-900">{{ students.meta?.total ?? students.data.length }} Cohorts</p>
                    </div>
                </div>
                <div class="rounded-2xl border bg-card p-5 shadow-sm border-l-4 border-l-emerald-500 flex items-center gap-4 group hover:shadow-md transition-all">
                    <div class="rounded-xl bg-emerald-500/10 p-3 text-emerald-600 transition-all"><CheckCircle2 class="h-5 w-5" /></div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Pass Index</p>
                        <p class="text-xl font-black text-emerald-600">82.4% Efficient</p>
                    </div>
                </div>
                <div class="rounded-2xl border bg-card p-5 shadow-sm border-l-4 border-l-indigo-600 flex items-center gap-4 group hover:shadow-md transition-all">
                    <div class="rounded-xl bg-indigo-500/10 p-3 text-indigo-600 transition-all"><TrendingUp class="h-5 w-5" /></div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Mean Score</p>
                        <p class="text-xl font-black text-indigo-600">68.4 pts</p>
                    </div>
                </div>
                <div class="rounded-2xl border bg-card p-5 shadow-sm border-l-4 border-l-purple-600 flex items-center gap-4 group hover:shadow-md transition-all">
                    <div class="rounded-xl bg-purple-500/10 p-3 text-purple-600 transition-all"><Target class="h-5 w-5" /></div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Engine Load</p>
                        <p class="text-xl font-black text-purple-600 uppercase tracking-tighter italic">Optimized</p>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center bg-white p-4 rounded-xl border shadow-sm">
                <div class="relative flex-1 md:max-w-md">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground font-medium" />
                    <Input v-model="searchQuery" placeholder="Filter by name or admission code..." class="pl-9 h-11 border-slate-200 rounded-xl" />
                </div>
                <div class="flex items-center gap-3">
                     <SearchableSelect 
                        v-model="selectedClass"
                        :options="[{id: 'all', name: 'Every Class'}, ...classes]"
                        placeholder="Cluster Filter"
                        search-placeholder="Find class..."
                        class="w-full md:w-[220px] h-11"
                    />
                    <Button variant="outline" class="h-11 rounded-xl border-slate-200 font-black text-xs uppercase tracking-widest px-4 hover:bg-slate-50 transition-colors">
                        <Filter class="mr-2 h-4 w-4" /> Advance Filter
                    </Button>
                </div>
            </div>

            <!-- Results Table -->
            <div class="rounded-[2rem] border bg-card shadow-sm overflow-hidden overflow-x-auto border-t-8 border-t-indigo-500">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-b font-pulsar">
                            <th class="px-6 py-5 text-xs font-bold uppercase text-slate-500 tracking-widest">Student Profile</th>
                            <th class="px-6 py-5 text-xs font-bold uppercase text-slate-500 tracking-widest text-center">Cluster</th>
                            <th class="px-6 py-5 text-xs font-bold uppercase text-slate-500 tracking-widest text-center">Load</th>
                            <th class="px-6 py-5 text-xs font-bold uppercase text-slate-500 tracking-widest text-center">Mean (%)</th>
                            <th class="px-6 py-5 text-xs font-bold uppercase text-slate-500 tracking-widest text-center">Pulse Tracking</th>
                            <th class="px-6 py-5 text-right text-xs font-bold uppercase text-slate-500 tracking-widest">Logic</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y text-sm font-medium">
                        <tr v-for="student in filteredStudents" :key="student.id" class="group hover:bg-slate-50/70 transition-all duration-300">
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="h-12 w-12 rounded-2xl bg-indigo-600/10 flex items-center justify-center font-black text-indigo-700 group-hover:bg-indigo-600 group-hover:text-white transition-all text-sm shadow-inner border border-indigo-50 uppercase">
                                         {{ (student.first_name || 'U')[0] }}{{ (student.last_name || 'S')[0] }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 group-hover:text-indigo-700 transition-colors leading-tight">{{ student.first_name }} {{ student.last_name }}</div>
                                        <div class="text-[10px] font-black text-slate-400 mt-1 uppercase tracking-widest">ID: {{ student.admission_number }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <Badge variant="outline" class="bg-slate-50 border-slate-200 text-slate-600 text-[10px] font-black px-2 py-0.5 rounded-lg uppercase">
                                    {{ student.current_class?.name || 'Isolated' }}
                                </Badge>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <div class="inline-flex flex-col items-center">
                                     <span class="font-black text-slate-900 text-sm italic">{{ student.tests_count || 0 }}</span>
                                     <span class="text-[9px] font-black text-slate-400 uppercase tracking-tighter mt-0.5">Tests Logged</span>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <div class="inline-flex flex-col items-center">
                                     <span class="text-lg font-black tracking-tighter" :class="getPerformanceColor(student.mean_score)">{{ student.mean_score }}%</span>
                                     <span class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mt-0.5 italic">Consensus</span>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex flex-col items-center gap-2">
                                    <div class="h-2 w-28 bg-slate-100 rounded-full overflow-hidden border shadow-inner">
                                        <div class="h-full bg-linear-to-r from-indigo-500 to-purple-500 rounded-full" :style="{ width: student.mean_score + '%' }"></div>
                                    </div>
                                    <span class="text-[9px] font-black uppercase text-indigo-600 tracking-widest italic group-hover:scale-105 transition-transform">Trajectory {{ student.trajectory }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <Button size="icon" variant="ghost" class="h-9 w-9 text-slate-300 hover:text-indigo-600 hover:bg-indigo-50 transition-all rounded-xl" as-child title="Performance Log">
                                        <Link :href="`/assessments/results/${student.id}`">
                                            <ArrowUpRight class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" size="icon" class="h-9 w-9 rounded-xl text-slate-300 hover:text-slate-600 transition-all"><MoreHorizontal class="h-4 w-4" /></Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end" class="w-56 font-pulsar rounded-xl overflow-hidden p-1 shadow-2xl border-slate-200">
                                            <DropdownMenuItem class="rounded-lg font-bold"><FileText class="mr-3 h-4 w-4 text-indigo-600" /> View Transcript</DropdownMenuItem>
                                            <DropdownMenuItem class="rounded-lg font-bold"><TrendingUp class="mr-3 h-4 w-4 text-emerald-500" /> Progress Curve</DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem class="rounded-lg font-black text-indigo-600">Export Student ID Data</DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredStudents.length === 0">
                            <td colspan="6" class="px-6 py-24 text-center">
                                <div class="mx-auto w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mb-6 shadow-inner border border-slate-100">
                                    <BarChart3 class="h-10 w-10 text-slate-200" />
                                </div>
                                <h3 class="text-2xl font-black text-slate-800 mb-2">Registry Silent</h3>
                                <p class="text-slate-500 font-medium max-w-sm mx-auto italic">No student records found matching the current search parameters for this academic cluster.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer Pagination -->
            <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between py-12 border-t mt-6">
                <div class="flex items-center gap-8">
                     <p class="text-xs text-slate-400 font-black uppercase tracking-widest whitespace-nowrap">Node Registry: {{ filteredStudents.length }} Entries</p>
                    <div class="flex items-center gap-3">
                         <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Density:</span>
                         <select class="h-8 rounded-lg border-slate-200 bg-white px-2 text-[10px] font-black text-slate-600 focus:ring-indigo-500 uppercase">
                             <option>20 Rows</option>
                             <option>50 Rows</option>
                             <option>High Density</option>
                         </select>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 overflow-x-auto pb-2 sm:pb-0">
                    <Button 
                        v-for="(link, i) in students.links" 
                        :key="i"
                        variant="ghost"
                        size="sm"
                        :disabled="!link.url || link.active"
                        :class="['h-9 min-w-[36px] rounded-xl font-black text-xs uppercase tracking-tighter', 
                                link.active ? 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-lg' : 'text-slate-400 hover:bg-slate-100 hover:text-indigo-600']"
                        as-child
                    >
                        <Link v-if="link.url" :href="link.url" v-html="link.label" />
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
