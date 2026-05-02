<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    FileText,
    Search,
    Filter,
    Download,
    Printer,
    Send,
    CheckCircle2,
    AlertCircle,
    LayoutGrid,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    ChevronRight,
    ChevronDown,
    GraduationCap,
    Users,
    Table,
} from 'lucide-vue-next';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { route } from 'ziggy-js';
import { router } from '@inertiajs/vue3';

const props = defineProps<{
    gradeLevels: Array<any>;
    students: Array<any>;
    activeYear: any;
    activeTerm: any;
    filters: any;
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Assessments', href: '/assessments' },
    { title: 'Report Cards', href: '/assessments/report-cards' },
];

const viewMode = ref('list'); // 'list' or 'grid'
const expandedGrades = ref<number[]>([]);
const selectedClass = ref(props.filters?.class_id || null);

const toggleGrade = (id: number) => {
    const index = expandedGrades.value.indexOf(id);
    if (index === -1) expandedGrades.value.push(id);
    else expandedGrades.value.splice(index, 1);
};

const selectClass = (classId: number) => {
    selectedClass.value = classId;
    router.get(route('assessments.report-cards'), { class_id: classId }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const getRubricColor = (average: number) => {
    if (average >= 75) return 'bg-emerald-500';
    if (average >= 50) return 'bg-blue-500';
    if (average >= 30) return 'bg-amber-500';
    return 'bg-rose-500';
};

const getRubricCode = (average: number) => {
    if (average >= 75) return 'EE';
    if (average >= 50) return 'ME';
    if (average >= 30) return 'AE';
    return 'BE';
};
</script>

<template>
    <Head title="Report Cards" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex h-full max-w-[1600px] flex-1 animate-in flex-col space-y-8 p-4 pb-20 duration-700 fade-in slide-in-from-bottom-4 sm:p-6 sm:pb-32 md:p-8">
            <!-- Header -->
            <div class="flex flex-col gap-6 px-1 md:flex-row md:items-center md:justify-between">
                <div class="space-y-1">
                    <h1 class="text-2xl font-black tracking-tight text-foreground sm:text-3xl uppercase">Report Cards</h1>
                    <p class="text-xs font-bold text-muted-foreground uppercase tracking-widest">{{ activeTerm?.name }} - {{ activeYear?.name }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center bg-muted/30 rounded-lg p-1 border border-border/50">
                        <button 
                            @click="viewMode = 'list'"
                            class="p-2 rounded-md transition-all"
                            :class="viewMode === 'list' ? 'bg-card text-primary shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                        >
                            <Table class="h-4 w-4" />
                        </button>
                        <button 
                            @click="viewMode = 'grid'"
                            class="p-2 rounded-md transition-all"
                            :class="viewMode === 'grid' ? 'bg-card text-primary shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                        >
                            <LayoutGrid class="h-4 w-4" />
                        </button>
                    </div>
                    <Button variant="outline" class="h-10 rounded-xl border-border bg-card px-4 text-[10px] font-black uppercase tracking-widest hover:bg-muted">
                        <Printer class="mr-2 h-4 w-4 text-primary" />
                        Bulk Print
                    </Button>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Sidebar Navigation: Hierarchy -->
                <div class="w-full lg:w-80 shrink-0 space-y-4">
                    <Card class="rounded-2xl border-border bg-card shadow-sm overflow-hidden">
                        <CardHeader class="pb-3 pt-4 border-b border-border/50 bg-muted/5">
                            <CardTitle class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground flex items-center gap-2">
                                <GraduationCap class="h-4 w-4 text-primary" />
                                Institutional Structure
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="p-2 space-y-1">
                            <div v-for="grade in gradeLevels" :key="grade.id" class="space-y-1">
                                <button 
                                    @click="toggleGrade(grade.id)"
                                    class="w-full flex items-center justify-between p-3 rounded-xl hover:bg-muted/50 transition-all text-left group"
                                    :class="expandedGrades.includes(grade.id) ? 'bg-primary/5 text-primary' : 'text-foreground'"
                                >
                                    <div class="flex items-center gap-3">
                                        <div class="h-8 w-8 rounded-lg bg-background flex items-center justify-center border border-border group-hover:border-primary/30 transition-all">
                                            <span class="text-[10px] font-black">{{ grade.name.match(/\d+/)?.[0] || 'G' }}</span>
                                        </div>
                                        <span class="text-xs font-bold">{{ grade.name }}</span>
                                    </div>
                                    <component :is="expandedGrades.includes(grade.id) ? ChevronDown : ChevronRight" class="h-4 w-4 opacity-40" />
                                </button>
                                
                                <div v-if="expandedGrades.includes(grade.id)" class="pl-4 space-y-1 py-1">
                                    <button 
                                        v-for="cls in grade.classes" 
                                        :key="cls.id"
                                        @click="selectClass(cls.id)"
                                        class="w-full flex items-center gap-3 p-2.5 rounded-xl transition-all text-left"
                                        :class="selectedClass == cls.id ? 'bg-primary text-white shadow-md shadow-primary/20' : 'text-muted-foreground hover:bg-muted hover:text-foreground'"
                                    >
                                        <Users class="h-3.5 w-3.5 opacity-60" />
                                        <span class="text-[11px] font-bold">{{ cls.name }}</span>
                                    </button>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Main Content: Student Registry -->
                <div class="flex-1 space-y-6">
                    <div v-if="!selectedClass" class="flex flex-col items-center justify-center p-20 rounded-3xl border-2 border-dashed border-border bg-muted/5 text-center">
                        <div class="h-20 w-20 rounded-full bg-primary/5 flex items-center justify-center mb-6">
                            <FileText class="h-10 w-10 text-primary opacity-40" />
                        </div>
                        <h3 class="text-sm font-black uppercase tracking-widest text-foreground">Select a Class to Begin</h3>
                        <p class="text-xs font-medium text-muted-foreground mt-2 max-w-xs">Generate termly report cards by selecting a specific class from the institutional structure on the left.</p>
                    </div>

                    <template v-else>
                        <!-- Search & Filters -->
                        <div class="flex items-center gap-4 bg-card rounded-2xl p-4 border border-border shadow-sm">
                            <div class="relative flex-1">
                                <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground/40" />
                                <Input 
                                    placeholder="Search by name or admission number..." 
                                    class="pl-10 h-11 rounded-xl border-border/50 bg-muted/5 font-medium text-sm focus:bg-background"
                                />
                            </div>
                            <Button variant="outline" class="h-11 rounded-xl gap-2 text-xs font-bold border-border/50">
                                <Filter class="h-4 w-4" /> Filter
                            </Button>
                        </div>

                        <!-- Table View -->
                        <div v-if="viewMode === 'list'" class="bg-card rounded-2xl border border-border shadow-sm overflow-hidden">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-border/50 bg-muted/5">
                                        <th class="p-5 text-[9px] font-black uppercase tracking-widest text-muted-foreground/60 w-12">#</th>
                                        <th class="p-5 text-[9px] font-black uppercase tracking-widest text-muted-foreground/60">Learner Details</th>
                                        <th class="p-5 text-[9px] font-black uppercase tracking-widest text-muted-foreground/60 text-center">Avg Score</th>
                                        <th class="p-5 text-[9px] font-black uppercase tracking-widest text-muted-foreground/60 text-center">Level</th>
                                        <th class="p-5 text-[9px] font-black uppercase tracking-widest text-muted-foreground/60 text-center">Status</th>
                                        <th class="p-5 text-[9px] font-black uppercase tracking-widest text-muted-foreground/60 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border/20">
                                    <tr v-for="(student, idx) in students" :key="student.id" class="group hover:bg-primary/[0.02] transition-colors">
                                        <td class="p-5 text-[10px] font-black text-muted-foreground/40">{{ idx + 1 }}</td>
                                        <td class="p-5">
                                            <div class="flex items-center gap-4">
                                                <div class="h-10 w-10 rounded-xl bg-muted/30 border border-border overflow-hidden">
                                                    <img v-if="student.photo" :src="student.photo" class="h-full w-full object-cover" />
                                                    <div v-else class="h-full w-full flex items-center justify-center text-[10px] font-black text-muted-foreground/50 bg-primary/5">{{ student.name.substring(0, 2).toUpperCase() }}</div>
                                                </div>
                                                <div class="flex flex-col">
                                                    <span class="text-xs font-black text-foreground group-hover:text-primary transition-colors uppercase">{{ student.name }}</span>
                                                    <span class="text-[10px] font-bold text-muted-foreground opacity-60 tracking-widest">{{ student.admission_number }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="p-5 text-center">
                                            <span class="text-sm font-black text-foreground">{{ student.average }}%</span>
                                        </td>
                                        <td class="p-5 text-center">
                                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-border/50 bg-muted/5">
                                                <div class="h-1.5 w-1.5 rounded-full" :class="getRubricColor(student.average)"></div>
                                                <span class="text-[10px] font-black">{{ getRubricCode(student.average) }}</span>
                                            </div>
                                        </td>
                                        <td class="p-5 text-center">
                                            <Badge variant="outline" class="rounded-lg border-emerald-500/30 bg-emerald-500/5 text-emerald-600 text-[10px] font-black uppercase px-2 py-0.5">Ready</Badge>
                                        </td>
                                        <td class="p-5">
                                            <div class="flex items-center justify-end gap-2">
                                                <Button variant="ghost" size="sm" class="h-8 w-8 p-0 rounded-lg hover:bg-primary/10 text-primary" as-child>
                                                    <Link :href="route('assessments.report-cards.show', { student: student.id })">
                                                        <FileText class="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                                <Button variant="ghost" size="sm" class="h-8 w-8 p-0 rounded-lg hover:bg-primary/10 text-primary" as-child>
                                                    <a :href="route('assessments.report-cards.pdf', { student: student.id })" target="_blank">
                                                        <Download class="h-4 w-4" />
                                                    </a>
                                                </Button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Grid View -->
                        <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                            <Card v-for="student in students" :key="student.id" class="rounded-2xl border-border bg-card shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all group">
                                <CardContent class="p-6">
                                    <div class="flex items-center gap-4 mb-6">
                                        <div class="h-14 w-14 rounded-2xl bg-muted/30 border-2 border-border/50 overflow-hidden shrink-0">
                                            <img v-if="student.photo" :src="student.photo" class="h-full w-full object-cover" />
                                            <div v-else class="h-full w-full flex items-center justify-center text-sm font-black text-muted-foreground/50 bg-primary/5">{{ student.name.substring(0, 2).toUpperCase() }}</div>
                                        </div>
                                        <div class="flex flex-col min-w-0">
                                            <h4 class="text-sm font-black text-foreground truncate uppercase tracking-tight">{{ student.name }}</h4>
                                            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-[0.2em] opacity-60">{{ student.admission_number }}</p>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4 mb-6">
                                        <div class="bg-muted/5 rounded-xl p-3 border border-border/50">
                                            <p class="text-[9px] font-black text-muted-foreground uppercase tracking-widest mb-1">Average</p>
                                            <p class="text-lg font-black text-primary">{{ student.average }}%</p>
                                        </div>
                                        <div class="bg-muted/5 rounded-xl p-3 border border-border/50">
                                            <p class="text-[9px] font-black text-muted-foreground uppercase tracking-widest mb-1">Rating</p>
                                            <div class="flex items-center gap-2">
                                                <div class="h-2 w-2 rounded-full" :class="getRubricColor(student.average)"></div>
                                                <p class="text-sm font-black text-foreground">{{ getRubricCode(student.average) }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <Button variant="outline" class="flex-1 h-10 rounded-xl text-[10px] font-black uppercase tracking-widest border-primary/20 text-primary hover:bg-primary/5" as-child>
                                            <Link :href="route('assessments.report-cards.show', { student: student.id })">View Details</Link>
                                        </Button>
                                        <Button class="h-10 w-10 rounded-xl bg-primary shadow-lg shadow-primary/20 p-0" as-child>
                                            <a :href="route('assessments.report-cards.pdf', { student: student.id })" target="_blank">
                                                <Download class="h-4 w-4" />
                                            </a>
                                        </Button>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
