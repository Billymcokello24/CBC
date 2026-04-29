<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { 
    User, Search, Filter, Download, Printer, 
    ChevronRight, BookOpen, Star, MessageCircle, 
    ArrowLeft, Calendar, LayoutGrid, CheckCircle2,
    Info, ExternalLink, GraduationCap, School,
    FileText, Award, TrendingUp, AlertCircle, TrendingDown, Activity
} from 'lucide-vue-next';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import StatCard from '@/components/dashboard/StatCard.vue';
import axios from 'axios';

interface ReportData {
    learner: {
        name: string;
        admission_number: string;
        grade: string;
        term: string;
        class: string;
    };
    performance: Array<{
        area: string;
        competencies: Array<{
            task: string;
            level: number;
            feedback: string;
        }>;
        remarks: string;
    }>;
    traits: Array<{
        trait: string;
        level: number;
    }>;
    comments: {
        teacher: string;
        principal: string;
        attendance: string;
    };
}

const props = defineProps<{
    students: any[];
    classes: any[];
    terms: any[];
    performanceLevels: any[];
}>();

const breadcrumbs = [
    { title: 'Intelligence', href: '/reports' },
    { title: 'Progress Report', href: '/reports/report-cards' },
];

const selectedStudentId = ref<string | null>(null);
const selectedTermId = ref<string | null>(props.terms[0]?.id || null);
const selectedClassId = ref<string | null>(null);
const reportData = ref<ReportData | null>(null);
const isLoading = ref(false);
const searchQuery = ref('');

const filteredStudents = computed(() => {
    return props.students.filter(s => {
        const matchesClass = !selectedClassId.value || s.class_id == selectedClassId.value;
        const matchesSearch = s.name.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
                             s.admission_number.toLowerCase().includes(searchQuery.value.toLowerCase());
        return matchesClass && matchesSearch;
    });
});

const loadReport = async (studentId: string) => {
    selectedStudentId.value = studentId;
    isLoading.value = true;
    try {
        const response = await axios.get(route('api.reports.progress', studentId), {
            params: { term_id: selectedTermId.value }
        });
        reportData.value = response.data;
    } catch (error) {
        console.error("Failed to load report", error);
    } finally {
        isLoading.value = false;
    }
};

const getLevelInfo = (level: number) => {
    return props.performanceLevels.find(l => l.level === level) || props.performanceLevels[1];
};

const triggerPrint = () => {
    window.print();
};
</script>

<template>
    <Head title="Progress Report" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-[1600px] space-y-3 p-3 fade-in sm:p-5">
            
            <!-- HEADER SECTION (Dashboard Style) -->
            <div class="flex flex-col md:flex-row md:items-end justify-between border-b pb-4 border-border/40 gap-4 mb-2 print-hide">
                <div class="space-y-1.5 px-1">
                    <div class="flex items-center gap-2">
                        <div class="h-2 w-2 rounded-full bg-primary animate-pulse shadow-[0_0_8px_rgba(var(--primary),0.6)]"></div>
                        <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-[0.2em] text-primary select-none">Learner Analytics</span>
                    </div>
                    <h1 class="text-3xl font-black tracking-tight text-foreground sm:text-4xl lg:text-5xl">
                        Progress Report
                    </h1>
                    <p class="text-[11px] sm:text-xs text-muted-foreground font-bold flex items-center gap-2">
                        CBC Competency Journal
                    </p>
                </div>

                <div class="flex items-center gap-2 w-full md:w-auto">
                    <Button variant="outline" class="h-10 text-[11px] font-black uppercase tracking-widest bg-white" @click="triggerPrint">
                        <Printer class="mr-2 h-3.5 w-3.5" /> Print PDF
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
                
                <!-- SELECTION PANEL (Left Column - Hidden in Print) -->
                <div class="lg:col-span-3 space-y-4 print-hide">
                    <div class="rounded-2xl border bg-card p-5 shadow-sm overflow-hidden flex flex-col h-[75vh]">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-[10px] font-black uppercase tracking-widest text-foreground flex items-center gap-2">
                                <Search class="h-4 w-4 text-primary" />
                                Student Selection
                            </h3>
                        </div>
                        
                        <div class="space-y-3 mb-4">
                            <Select v-model="selectedClassId">
                                <SelectTrigger class="h-9 text-xs">
                                    <SelectValue placeholder="All Classes" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="null">All Classes</SelectItem>
                                    <SelectItem v-for="c in classes" :key="c.id" :value="c.id.toString()">
                                        {{ c.name }} ({{ c.grade }})
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            
                            <Select v-model="selectedTermId">
                                <SelectTrigger class="h-9 text-xs">
                                    <SelectValue placeholder="Select Term" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="t in terms" :key="t.id" :value="t.id.toString()">
                                        {{ t.name }} - {{ t.academic_year?.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>

                            <Input 
                                v-model="searchQuery"
                                placeholder="Search student..." 
                                class="h-9 text-xs bg-muted/20"
                            />
                        </div>

                        <Separator class="mb-3" />

                        <div class="space-y-1.5 flex-1 overflow-y-auto pr-1 custom-scrollbar">
                            <button 
                                v-for="s in filteredStudents" 
                                :key="s.id"
                                @click="loadReport(s.id)"
                                :class="[
                                    'w-full flex items-center gap-3 p-2 rounded-xl transition-all group text-left border',
                                    selectedStudentId === s.id 
                                        ? 'bg-primary/[0.05] border-primary/20 hover:bg-primary/[0.08]' 
                                        : 'bg-transparent border-transparent hover:bg-muted/50 hover:border-border/40'
                                ]"
                            >
                                <Avatar class="h-8 w-8 ring-1 ring-border/50">
                                    <AvatarFallback class="bg-primary/5 text-primary text-[10px] font-black uppercase">{{ s.name.substring(0, 2) }}</AvatarFallback>
                                </Avatar>
                                <div class="flex flex-col leading-none min-w-0">
                                    <span class="text-xs font-black text-foreground truncate">{{ s.name }}</span>
                                    <span class="text-[9px] text-muted-foreground font-black uppercase mt-1 opacity-70">{{ s.admission_number }} - {{ s.class }}</span>
                                </div>
                            </button>

                            <div v-if="filteredStudents.length === 0" class="py-8 text-center opacity-50">
                                <span class="text-[10px] font-black uppercase tracking-widest">No matching learners</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- REPORT VIEW (Right Column) -->
                <div class="lg:col-span-9 flex flex-col space-y-4">
                    
                    <div v-if="isLoading" class="flex flex-col items-center justify-center p-20 opacity-50">
                        <Activity class="h-8 w-8 animate-spin mb-4" />
                        <span class="text-[10px] font-black uppercase tracking-widest">Loading Record...</span>
                    </div>

                    <div v-else-if="!reportData" class="flex flex-col items-center justify-center p-20 opacity-40 border-2 border-dashed rounded-2xl print-hide">
                        <FileText class="h-10 w-10 mb-4" />
                        <span class="text-[11px] font-black uppercase tracking-widest">Select a student to view report</span>
                    </div>

                    <div v-else class="space-y-4 fade-in">
                        
                        <!-- Print Specific Header (Only visible when printing or as part of the unified styling) -->
                        <div class="rounded-2xl border bg-card p-6 shadow-sm flex flex-col sm:flex-row justify-between items-center gap-4">
                            <div class="flex items-center gap-4">
                                <div class="h-14 w-14 rounded-xl bg-primary/10 flex items-center justify-center border border-primary/20">
                                    <School class="h-7 w-7 text-primary" />
                                </div>
                                <div class="space-y-1">
                                    <h2 class="text-2xl font-black tracking-tight uppercase">{{ reportData.learner.name }}</h2>
                                    <p class="text-[10px] font-black text-muted-foreground uppercase tracking-widest">
                                        ADM: {{ reportData.learner.admission_number }} • {{ reportData.learner.grade }} • {{ reportData.learner.term }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-6">
                                <div class="text-right">
                                    <p class="text-[9px] font-black uppercase text-muted-foreground">Class Period</p>
                                    <p class="text-sm font-black">{{ reportData.learner.class }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Core Traits (Stat Cards style) -->
                        <div>
                            <h3 class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-3 px-1">Core Competencies & Values</h3>
                            <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                                <div v-for="trait in reportData.traits" :key="trait.trait" class="rounded-2xl border bg-card p-4 flex flex-col justify-between shadow-sm">
                                    <p class="text-[10px] font-black uppercase tracking-tight text-foreground leading-snug mb-3">
                                        {{ trait.trait }}
                                    </p>
                                    <div class="flex items-center gap-2">
                                        <Badge variant="outline" class="text-[9px] font-black rounded-sm border-primary/20 bg-primary/5 text-primary tracking-widest">
                                            {{ getLevelInfo(trait.level).code }}
                                        </Badge>
                                        <span class="text-[8px] font-bold text-muted-foreground uppercase opacity-70">{{ getLevelInfo(trait.level).label }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Learning Areas Table (Data Table style like Students page) -->
                        <div class="rounded-2xl border bg-card shadow-sm overflow-hidden">
                            <div class="flex items-center justify-between border-b px-5 py-4 bg-muted/5">
                                <h3 class="text-[10px] font-black uppercase tracking-widest text-foreground/70">Subject Proficiency Ledger</h3>
                            </div>
                            <div class="p-0">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-muted/10 border-b border-border/40 text-[9px] font-black uppercase tracking-widest text-muted-foreground">
                                            <th class="py-3 px-5 w-1/3">Learning Area / Competency</th>
                                            <th class="py-3 px-5 text-center">Score</th>
                                            <th class="py-3 px-5">Teacher Feedback</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-border/20">
                                        <template v-for="area in reportData.performance" :key="area.area">
                                            <!-- Subject Row -->
                                            <tr class="bg-muted/5">
                                                <td colspan="3" class="py-2.5 px-5 h-auto">
                                                    <div class="flex items-center gap-2">
                                                        <BookOpen class="h-3.5 w-3.5 text-primary opacity-70" />
                                                        <span class="text-xs font-black text-foreground uppercase tracking-tight">{{ area.area }}</span>
                                                    </div>
                                                </td>
                                            </tr>
                                            <!-- Task Rows -->
                                            <tr v-for="(comp, i) in area.competencies" :key="i" class="hover:bg-muted/5 transition-colors">
                                                <td class="py-3 px-5 pl-10 border-l-2 border-transparent hover:border-primary/50">
                                                    <span class="text-xs font-bold text-foreground block">{{ comp.task }}</span>
                                                </td>
                                                <td class="py-3 px-5 text-center">
                                                    <Badge variant="outline" class="font-black text-[9px] uppercase tracking-widest">
                                                        {{ getLevelInfo(comp.level).code }}
                                                    </Badge>
                                                </td>
                                                <td class="py-3 px-5">
                                                    <span class="text-[11px] text-muted-foreground font-medium italic">{{ comp.feedback }}</span>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Remarks Section (Dashboard Card layout) -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="rounded-2xl border bg-card p-5 shadow-sm">
                                <h3 class="text-[10px] font-black uppercase tracking-widest text-foreground flex items-center gap-2 mb-3">
                                    <CheckCircle2 class="h-4 w-4 text-emerald-500" />
                                    Class Teacher Input
                                </h3>
                                <p class="text-[11px] text-muted-foreground font-bold italic leading-relaxed">
                                    "{{ reportData.comments.teacher }}"
                                </p>
                            </div>
                            
                            <div class="rounded-2xl border bg-card p-5 shadow-sm">
                                <h3 class="text-[10px] font-black uppercase tracking-widest text-foreground flex items-center gap-2 mb-3">
                                    <CheckCircle2 class="h-4 w-4 text-blue-500" />
                                    Principal Remarks
                                </h3>
                                <p class="text-[11px] text-muted-foreground font-bold italic leading-relaxed">
                                    "{{ reportData.comments.principal }}"
                                </p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>

<style scoped>
.fade-in {
    animation: fadeIn 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}

.custom-scrollbar::-webkit-scrollbar {
    width: 3px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
::-webkit-scrollbar-thumb {
    background: hsl(var(--primary) / 0.15);
    border-radius: 10px;
}
</style>
