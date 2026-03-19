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
    { title: 'Assessments', href: '/assessments' },
    { title: 'Results', href: '/assessments/results' },
];

const searchQuery = ref('');
const selectedClass = ref('all');
const showUploadDialog = ref(false);

const filteredStudents = computed(() => {
    return props.students.data.filter(student => {
        const matchesSearch = (student.first_name + ' ' + student.last_name).toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                             student.admission_no.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesClass = selectedClass.value === 'all' || student.current_class_id?.toString() === selectedClass.value;
        return matchesSearch && matchesClass;
    });
});

const getPerformanceColor = (average: number) => {
    if (average >= 80) return 'text-green-600';
    if (average >= 60) return 'text-blue-600';
    if (average >= 40) return 'text-amber-600';
    return 'text-red-600';
};
</script>

<template>
    <Head title="Assessment Results" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 max-w-6xl mx-auto">
            <!-- Header Section -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-500/10 border border-indigo-200 shadow-sm">
                        <BarChart3 class="h-6 w-6 text-indigo-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Assessment Results</h1>
                        <p class="text-sm text-muted-foreground flex items-center gap-2">
                            <Calendar class="h-3.5 w-3.5" />
                            {{ activeYear?.name }} - {{ activeTerm?.name }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" class="border-indigo-200 text-indigo-700 bg-indigo-50/50 font-bold" @click="showUploadDialog = true">
                        <Upload class="mr-2 h-4 w-4" />
                        Import Results
                    </Button>
                    <Button variant="outline" class="font-bold">
                        <Download class="mr-2 h-4 w-4" />
                        Export All
                    </Button>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card class="border-indigo-100/50 shadow-sm">
                    <CardContent class="p-4 flex items-center gap-4">
                        <div class="h-10 w-10 rounded-xl bg-orange-100 flex items-center justify-center text-orange-600">
                            <User class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-xs font-bold text-muted-foreground uppercase tracking-wider">Total Students</p>
                            <p class="text-xl font-black">{{ students.meta?.total ?? students.data.length }}</p>
                        </div>
                    </CardContent>
                </Card>
                <Card class="border-indigo-100/50 shadow-sm">
                    <CardContent class="p-4 flex items-center gap-4">
                        <div class="h-10 w-10 rounded-xl bg-green-100 flex items-center justify-center text-green-600">
                            <CheckCircle2 class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-xs font-bold text-muted-foreground uppercase tracking-wider">Pass Rate</p>
                            <p class="text-xl font-black text-green-600">82%</p>
                        </div>
                    </CardContent>
                </Card>
                <Card class="border-indigo-100/50 shadow-sm">
                    <CardContent class="p-4 flex items-center gap-4">
                        <div class="h-10 w-10 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600">
                            <TrendingUp class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-xs font-bold text-muted-foreground uppercase tracking-wider">Average Score</p>
                            <p class="text-xl font-black text-blue-600">68.4%</p>
                        </div>
                    </CardContent>
                </Card>
                <Card class="border-indigo-100/50 shadow-sm">
                    <CardContent class="p-4 flex items-center gap-4">
                        <div class="h-10 w-10 rounded-xl bg-purple-100 flex items-center justify-center text-purple-600">
                            <FileText class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-xs font-bold text-muted-foreground uppercase tracking-wider">Assessments</p>
                            <p class="text-xl font-black text-purple-600">12</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Main Content -->
            <Card class="border-indigo-100/50 shadow-xl shadow-indigo-500/5 overflow-hidden rounded-3xl">
                <CardHeader class="border-b bg-muted/20 p-6">
                    <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                        <div class="relative flex-1 max-w-md w-full">
                            <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input v-model="searchQuery" placeholder="Search by name or admission number..." class="pl-9 h-11 border-indigo-100 rounded-xl focus:ring-indigo-500" />
                        </div>
                        <div class="flex items-center gap-3 w-full md:w-auto">
                            <SearchableSelect 
                                v-model="selectedClass"
                                :options="[{id: 'all', name: 'All Classes'}, ...classes]"
                                placeholder="All Classes"
                                search-placeholder="Search classes..."
                                class="w-full md:w-[200px] h-11"
                            />
                            <Button variant="outline" class="h-11 rounded-xl border-indigo-100 font-bold px-4">
                                <Filter class="mr-2 h-4 w-4" /> Filter
                            </Button>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="p-0">
                    <Table>
                        <TableHeader class="bg-muted/30">
                            <TableRow class="hover:bg-transparent border-0 font-bold text-xs uppercase tracking-widest text-muted-foreground">
                                <TableHead class="pl-6 py-4">Student Name</TableHead>
                                <TableHead>Class</TableHead>
                                <TableHead class="text-center">Assessments</TableHead>
                                <TableHead class="text-center">Average (%)</TableHead>
                                <TableHead class="text-center">Performance</TableHead>
                                <TableHead class="pr-6 text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="student in filteredStudents" :key="student.id" class="group hover:bg-muted/20 transition-colors border-indigo-50/50">
                                <TableCell class="pl-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-black text-sm shadow-md">
                                            {{ student.first_name[0] }}{{ student.last_name[0] }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-900">{{ student.first_name }} {{ student.last_name }}</p>
                                            <p class="text-xs text-muted-foreground font-medium uppercase tracking-tighter">{{ student.admission_no }}</p>
                                        </div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge variant="outline" class="bg-indigo-50 text-indigo-700 border-0 font-black text-[10px] uppercase">
                                        {{ student.current_class?.name || 'Unassigned' }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-center">
                                    <span class="font-bold text-sm">{{ student.assessment_results?.length || 0 }}</span>
                                </TableCell>
                                <TableCell class="text-center">
                                    <span class="font-black text-base" :class="getPerformanceColor(65)">65%</span>
                                </TableCell>
                                <TableCell class="text-center">
                                    <div class="flex flex-col items-center gap-1.5">
                                        <div class="h-2 w-24 bg-gray-100 rounded-full overflow-hidden border border-gray-50 shadow-inner">
                                            <div class="h-full bg-indigo-500 rounded-full shadow-[0_0_8px_rgba(99,102,241,0.5)]" :style="{ width: '65%' }"></div>
                                        </div>
                                        <span class="text-[10px] font-black uppercase text-indigo-600 tracking-tighter">Above Average</span>
                                    </div>
                                </TableCell>
                                <TableCell class="pr-6 text-right">
                                    <div class="flex items-center justify-end gap-2 translate-x-4 opacity-0 group-hover:translate-x-0 group-hover:opacity-100 transition-all duration-300">
                                        <Button size="icon" variant="ghost" class="h-8 w-8 rounded-full text-indigo-600 hover:bg-indigo-50" as-child>
                                            <Link :href="`/assessments/results/${student.id}`">
                                                <ArrowUpRight class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Button size="icon" variant="ghost" class="h-8 w-8 rounded-full text-gray-400">
                                            <MoreHorizontal class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <!-- Pagination -->
            <div class="flex items-center justify-between px-2 py-4">
                <p class="text-xs text-muted-foreground font-bold italic tracking-wide">
                    Showing {{ filteredStudents.length }} of {{ students.meta?.total ?? students.data.length }} students
                </p>
                <!-- Pagination components would go here -->
            </div>
        </div>

        <!-- Bulk Upload Dialog -->
        <BulkUploadDialog 
            v-model:open="showUploadDialog"
            title="Import Student Results"
            description="Upload a CSV or Excel file containing student marks for assessments. Make sure to use the official template."
            template-url="/assessments/results/export"
            upload-url="/assessments/results/import"
        />
    </AppLayout>
</template>
