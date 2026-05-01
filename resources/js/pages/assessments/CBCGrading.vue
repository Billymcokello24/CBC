<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import {
    ClipboardList,
    Save,
    ArrowLeft,
    User,
    CheckCircle2,
    AlertCircle,
    Search,
    Keyboard,
    Zap,
    Info,
    MoreHorizontal,
    ChevronRight,
    ChevronDown,
    Check,
    X,
    Star,
    BarChart3,
    BookOpen,
    Layers,
    Calendar,
    Upload,
    Download,
    Filter,
    ArrowRight,
    LayoutGrid,
    Rows3,
    Trophy,
    Target,
    SearchCode,
    History
} from 'lucide-vue-next';
import { ref, computed, onMounted, watch } from 'vue';
import BulkUploadDialog from '@/components/assessments/BulkUploadDialog.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar';
import axios from 'axios';

const props = defineProps<{
    assessment: any;
    allAssessments: Array<any>;
    students: Array<any>;
    existingRatings: Record<number, any[]>;
    ratingScales: Array<any>;
    stats: any;
}>();

const breadcrumbs = [
    { title: 'Intelligence Hub', href: '/dashboard' },
    { title: 'Assessment Matrix', href: '/assessments' },
    { title: 'Grading Terminal', href: '#' },
];

const selectedGrade = ref(props.assessment?.class?.grade_level_id);
const selectedClass = ref(props.assessment?.class_id);
const selectedTerm = ref(props.assessment?.academic_term_id);
const activeTab = ref('entry');
const gradingMode = ref<'rubric' | 'marks'>('rubric');
const saving = ref(false);
const lastSavedAt = ref<Date | null>(null);
const bulkUploadOpen = ref(false);
const searchQuery = ref('');

const availableAssessments = computed(() => {
    if (!props.allAssessments) return [];
    return props.allAssessments.filter(a => {
        const matchesGrade = !selectedGrade.value || a.class?.grade_level_id === selectedGrade.value;
        const matchesClass = !selectedClass.value || a.class_id === selectedClass.value;
        const matchesTerm = !selectedTerm.value || a.academic_term_id === selectedTerm.value;
        return matchesGrade && matchesClass && matchesTerm;
    });
});

watch(selectedClass, (newVal) => {
    if (newVal && newVal !== props.assessment?.class_id) {
        // Automatically switch context to an assessment mapping to the selected class
        const target = props.allAssessments.find(a => a.class_id === newVal && (!props.assessment || a.subject_id === props.assessment.subject_id)) 
                    || props.allAssessments.find(a => a.class_id === newVal);
        if (target && target.id !== props.assessment?.id) {
            router.visit(route('assessments.grading', { assessment: target.id }));
        }
    }
});

const criteria = computed(() => {
    if (!props.assessment?.items) return [];
    return props.assessment.items.map((item: any) => ({
        id: item.id,
        code: item.code,
        name: item.name,
        outOf: 100
    }));
});

const results = ref<any[]>([]);

onMounted(() => {
    if (props.students) {
        results.value = props.students.map((student) => {
            const studentRatings = props.existingRatings?.[student.id] || [];
            const criteriaRatings: Record<number, any> = {};
            const criteriaMarks: Record<number, any> = {};
            
            props.assessment?.items?.forEach((item: any) => {
                const existing = studentRatings.find(r => r.assessment_item_id === item.id); 
                criteriaRatings[item.id] = existing ? existing.score : null;
                criteriaMarks[item.id] = existing ? existing.marks : null;
            });

            return {
                id: student.id,
                name: `${student.first_name} ${student.last_name}`,
                admission_number: student.admission_number,
                photo: student.photo,
                ratings: criteriaRatings,
                marks: criteriaMarks,
                remarks: studentRatings[0]?.remarks || '',
            };
        });
    }
});

const translateMarksToRubric = (marks: number | null, outOf: number) => {
    if (marks === null || marks === undefined) return null;
    const percent = (marks / outOf) * 100;
    if (percent >= 80) return 4;
    if (percent >= 60) return 3;
    if (percent >= 40) return 2;
    return 1;
};

const updateRating = async (studentId: number, criterionId: number, value: any) => {
    const student = results.value.find(s => s.id === studentId);
    if (!student) return;

    const numValue = value === 'null' ? null : parseInt(value);
    student.ratings[criterionId] = numValue;
    student.marks[criterionId] = null;

    try {
        // @ts-ignore
        await axios.post(route('assessments.grading.quick-save'), {
            student_id: studentId,
            assessment_item_id: criterionId,
            rating: numValue,
        });
        lastSavedAt.value = new Date();
    } catch (e) {
        console.error('Auto-save error', e);
    }
};

const updateMarks = async (studentId: number, criterionId: number, marks: any) => {
    const student = results.value.find(s => s.id === studentId);
    if (!student) return;

    const numMarks = marks === '' ? null : parseFloat(marks);
    student.marks[criterionId] = numMarks;
    
    const score = translateMarksToRubric(numMarks, 100);
    student.ratings[criterionId] = score;

    try {
        // @ts-ignore
        await axios.post(route('assessments.grading.quick-save'), {
            student_id: studentId,
            assessment_item_id: criterionId,
            marks: numMarks,
            out_of: 100
        });
        lastSavedAt.value = new Date();
    } catch (e) {
        console.error('Auto-save error', e);
    }
};

const getScaleCode = (score: number | null) => {
    if (!score || !props.ratingScales) return null;
    return props.ratingScales.find((s: any) => s.id === Math.round(score))?.code || 'BE';
};

const getScaleColor = (score: number | null) => {
    if (!score || !props.ratingScales) return '#e2e8f0';
    return props.ratingScales.find((s: any) => s.id === Math.round(score))?.color || '#ef4444';
};

const overallStats = computed(() => {
    const studentOveralls = results.value.map(r => {
        const values = Object.values(r.ratings).filter(v => v !== null) as number[];
        if (values.length === 0) return null;
        return values.reduce((a, b) => a + b, 0) / values.length;
    }).filter(v => v !== null);

    const counts = { EE: 0, ME: 0, AE: 0, BE: 0 };
    studentOveralls.forEach(avg => {
        if (avg >= 3.5) counts.EE++;
        else if (avg >= 2.5) counts.ME++;
        else if (avg >= 1.5) counts.AE++;
        else counts.BE++;
    });

    return counts;
});

const filteredResults = computed(() => {
    if (!searchQuery.value) return results.value;
    const query = searchQuery.value.toLowerCase();
    return results.value.filter(
        (r: any) =>
            r.name.toLowerCase().includes(query) ||
            r.admission_number.toLowerCase().includes(query),
    );
});

const submitAll = () => {
    saving.value = true;
    const ratingsArray = results.value.flatMap(student => 
        Object.entries(student.ratings).map(([itemId, rating]) => ({
            student_id: student.id,
            assessment_item_id: parseInt(itemId),
            rating: rating,
            marks: student.marks[itemId],
            out_of: 100,
            feedback: student.remarks
        }))
    ).filter(r => r.rating !== null);

    // @ts-ignore
    router.post(
        // @ts-ignore
        route('assessments.grading.store', { assessment: props.assessment.id }),
        { ratings: ratingsArray as any },
        { onFinish: () => (saving.value = false) }
    );
};
</script>

<template>
    <Head :title="`Intelligence Terminal - ${assessment?.subject?.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex h-full max-w-[1600px] flex-1 animate-in flex-col gap-10 p-4 pb-20 duration-700 fade-in slide-in-from-bottom-4 sm:p-6 sm:pb-32 md:p-8 bg-background">
            
            <!-- Strategic Header -->
            <div class="flex flex-col gap-6 px-1 md:flex-row md:items-center md:justify-between">
                <div class="space-y-1">
                    <div class="flex items-center gap-3">
                         <!-- @ts-ignore -->
                        <Link :href="route('assessments.index')" class="group flex h-9 w-9 items-center justify-center rounded-md border border-border bg-card hover:bg-muted transition-all text-muted-foreground mr-1">
                            <ArrowLeft class="h-4 w-4 group-hover:-translate-x-0.5 transition-transform" />
                        </Link>
                        <h1 class="text-2xl font-black tracking-tighter text-foreground uppercase sm:text-3xl">Grading Terminal</h1>
                    </div>
                    <div class="flex items-center gap-2 mt-1">
                        <Badge variant="outline" class="h-6 border-primary/20 bg-primary/5 text-primary text-[8px] font-black tracking-widest uppercase rounded-md">{{ assessment?.subject?.name }}</Badge>
                        <Badge variant="outline" class="h-6 border-slate-200 bg-slate-50 text-slate-500 text-[8px] font-black tracking-widest uppercase rounded-md">{{ assessment?.class?.name }}</Badge>
                        <span class="text-[9px] font-bold text-muted-foreground/40 uppercase tracking-widest ml-2 italic">CBC Phase III Activated</span>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex h-11 items-center rounded-md border border-border bg-muted/30 p-1 mr-2 shadow-sm">
                        <button 
                            @click="gradingMode = 'rubric'"
                            class="flex items-center gap-2 px-4 h-full rounded-md text-[9px] font-black uppercase tracking-widest transition-all"
                            :class="gradingMode === 'rubric' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-muted-foreground/60 hover:text-foreground'"
                        >
                            <Trophy class="h-3.5 w-3.5" /> Rubric logic
                        </button>
                        <button 
                            @click="gradingMode = 'marks'"
                            class="flex items-center gap-2 px-4 h-full rounded-md text-[9px] font-black uppercase tracking-widest transition-all"
                            :class="gradingMode === 'marks' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-muted-foreground/60 hover:text-foreground'"
                        >
                            <Target class="h-3.5 w-3.5" /> Marks mode
                        </button>
                    </div>

                    <Button variant="outline" class="h-11 rounded-md border-border bg-card px-4 text-[10px] font-black uppercase tracking-widest hover:bg-muted shadow-sm" @click="bulkUploadOpen = true">
                        <Upload class="mr-2 h-4 w-4 text-primary" />Bulk Import
                    </Button>
                    <Button @click="submitAll" :disabled="saving" class="h-11 rounded-md bg-primary px-8 text-[10px] font-black uppercase tracking-widest text-white shadow-lg shadow-primary/20 transition-all hover:scale-[1.02] active:scale-95">
                        <Save class="mr-2 h-4 w-4" />
                        {{ saving ? 'Syncing...' : 'Finalize records' }}
                    </Button>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row gap-8">
                
                <!-- Selective Context Sidebar -->
                <div class="w-full lg:w-80 space-y-6 shrink-0">
                    <div class="rounded-lg border border-border bg-card p-6 shadow-sm space-y-5">
                         <div class="space-y-1.5">
                            <Label class="text-[9px] font-black uppercase tracking-[0.2em] text-muted-foreground ml-1">Grade Level</Label>
                            <div class="relative">
                                <select v-model="selectedGrade" class="h-12 w-full appearance-none rounded-md border border-border bg-muted/5 px-4 text-xs font-bold text-foreground outline-none focus:ring-2 focus:ring-primary/10">
                                    <option :value="undefined">All Grade Tiers</option>
                                    <option v-for="g in Array.from(new Set((allAssessments || []).map(a => a.class?.grade_level_id))).filter(id => id)" :key="g" :value="g">
                                        Grade {{ (allAssessments as any[]).find(a => a.class?.grade_level_id === g)?.class?.grade_level?.name }}
                                    </option>
                                </select>
                                <ChevronDown class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-primary opacity-40" />
                            </div>
                        </div>

                        <div class="space-y-1.5 mt-4">
                            <Label class="text-[9px] font-black uppercase tracking-[0.2em] text-muted-foreground ml-1">Class/Stream</Label>
                            <div class="relative">
                                <select v-model="selectedClass" class="h-12 w-full appearance-none rounded-md border border-border bg-muted/5 px-4 text-xs font-bold text-foreground outline-none focus:ring-2 focus:ring-primary/10">
                                    <option :value="undefined">All {{ selectedGrade ? `Grade ${allAssessments.find(a => a.class?.grade_level_id === selectedGrade)?.class?.grade_level?.name}` : '' }} Classes</option>
                                    <option v-for="c in Array.from(new Set((allAssessments || []).filter(a => !selectedGrade || a.class?.grade_level_id === selectedGrade).map(a => a.class_id))).filter(id => id)" :key="c" :value="c">
                                        {{ (allAssessments as any[]).find(a => a.class_id === c)?.class?.name }}
                                    </option>
                                </select>
                                <ChevronDown class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-primary opacity-40" />
                            </div>
                        </div>

                        <div class="space-y-1.5 border-t border-border pt-5">
                            <Label class="text-[9px] font-black uppercase tracking-[0.2em] text-muted-foreground ml-1">Context Switcher</Label>
                            <div class="grid gap-2 mt-2">
                                <button 
                                    v-for="a in (availableAssessments as any[])" 
                                    :key="a.id"
                                    @click="router.visit(route('assessments.grading', { assessment: a.id }))"
                                    class="w-full text-left p-4 rounded-lg transition-all border group relative overflow-hidden"
                                    :class="a.id === assessment?.id 
                                        ? 'bg-primary/5 border-primary/20 shadow-inner' 
                                        : 'bg-transparent border-transparent hover:bg-muted'"
                                >
                                    <div class="flex items-center justify-between relative z-10">
                                        <div class="space-y-0.5">
                                            <p class="text-[11px] font-black uppercase tracking-tight text-foreground">{{ a.subject?.name }}</p>
                                            <p class="text-[9px] font-bold text-muted-foreground uppercase opacity-60">{{ a.class?.name }}</p>
                                        </div>
                                        <div v-if="a.id === assessment?.id" class="h-2 w-2 rounded-full bg-primary animate-pulse"></div>
                                        <ChevronRight v-else class="h-3 w-3 text-muted-foreground/30 group-hover:text-foreground group-hover:translate-x-1 transition-all" />
                                    </div>
                                    <div v-if="a.id === assessment?.id" class="absolute inset-y-0 left-0 w-1 bg-primary"></div>
                                </button>
                                <div v-if="availableAssessments.length === 0" class="py-12 border-2 border-dashed border-border/50 rounded-lg flex flex-col items-center justify-center space-y-2 opacity-30">
                                    <SearchCode class="h-6 w-6" />
                                    <span class="text-[9px] font-black uppercase tracking-widest">No active sessions</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg border border-emerald-500/20 bg-emerald-500/5 p-6 space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="h-8 w-8 rounded-md bg-emerald-500 flex items-center justify-center text-white shadow-lg shadow-emerald-500/20">
                                <Check class="h-4 w-4" />
                            </div>
                            <span class="text-[10px] font-black uppercase tracking-widest text-emerald-700">Auto-Finalized</span>
                        </div>
                        <p class="text-[10px] text-emerald-900/60 leading-relaxed font-bold">Every entry is instantly recorded in the **Sync Hub**. Permanent records update upon finalization.</p>
                    </div>
                </div>

                <!-- Central Intelligence Grid -->
                <div class="flex-1 space-y-6">
                    
                    <!-- Performance Diagnostics -->
                    <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
                         <div v-for="(s, k) in [
                            { label: 'Learners', val: stats?.total || 0, color: 'text-foreground' },
                            { label: 'Exceeding', val: overallStats?.EE || 0, color: 'text-emerald-500' },
                            { label: 'Meeting', val: overallStats?.ME || 0, color: 'text-blue-500' },
                            { label: 'Approaching', val: overallStats?.AE || 0, color: 'text-amber-500' },
                            { label: 'Below', val: overallStats?.BE || 0, color: 'text-rose-500' }
                         ]" :key="k" class="rounded-lg border border-border bg-card p-5 shadow-sm hover:shadow-md transition-all group">
                            <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground/60 group-hover:text-primary transition-colors">{{ s.label }}</p>
                            <p class="mt-2 text-3xl font-black tracking-tight" :class="s.color">{{ s.val }}</p>
                         </div>
                    </div>

                     <!-- Intelligence Search -->
                    <div class="relative overflow-hidden rounded-lg border border-border bg-card shadow-sm">
                        <Search class="absolute left-6 top-1/2 -translate-y-1/2 h-5 w-5 text-muted-foreground/40" />
                        <Input
                            v-model="searchQuery"
                            placeholder="Identify learner node by name or admission index..."
                            class="h-14 w-full border-0 bg-transparent pl-16 pr-8 text-sm font-bold focus-visible:ring-0"
                        />
                         <div class="absolute right-6 top-1/2 -translate-y-1/2 flex items-center gap-2">
                             <div class="h-1.5 w-1.5 rounded-full bg-primary animate-pulse"></div>
                             <span class="text-[9px] font-black uppercase tracking-widest text-muted-foreground/60">Live Search</span>
                         </div>
                    </div>

                    <!-- Evaluation Matrix Terminal -->
                    <div class="rounded-lg border border-border bg-card shadow-sm overflow-hidden flex flex-col min-h-[600px]">
                         <div class="flex items-center justify-between border-b border-border bg-muted/5 px-8 pt-4">
                            <div class="flex items-center gap-8">
                                <button 
                                    v-for="tab in ['Entry Sheet', 'Analytics Hub', 'Flags', 'Vault']"
                                    :key="tab"
                                    @click="activeTab = tab.toLowerCase().split(' ')[0]"
                                    class="relative pb-4 text-[10px] font-black uppercase tracking-widest transition-all"
                                    :class="activeTab === tab.toLowerCase().split(' ')[0] ? 'text-primary' : 'text-muted-foreground/30 hover:text-foreground'"
                                >
                                    {{ tab }}
                                    <div v-if="activeTab === tab.toLowerCase().split(' ')[0]" class="absolute bottom-0 left-0 h-0.5 w-full bg-primary shadow-lg shadow-primary/20"></div>
                                </button>
                            </div>
                         </div>

                         <div class="flex-1 overflow-auto custom-scrollbar">
                            <table v-if="activeTab.startsWith('entry') && criteria.length > 0" class="w-full border-separate border-spacing-0">
                                <thead>
                                    <tr class="text-[9px] font-black uppercase tracking-widest text-muted-foreground/50 bg-muted/20">
                                        <th class="sticky left-0 z-30 border-b border-border bg-card p-6 text-left shadow-[4px_0_12px_-8px_rgba(0,0,0,0.1)]">LEARNER NODE</th>
                                        <th v-for="c in criteria" :key="c.id" class="border-b border-l border-border p-6 text-center">
                                            <div class="flex flex-col items-center gap-2">
                                                 <Badge variant="outline" class="rounded-md border-primary/20 bg-primary/5 text-primary text-[8px] font-black tracking-tighter">{{ c.code }}</Badge>
                                                 <span class="max-w-[100px] truncate text-[9px]">{{ c.name }}</span>
                                            </div>
                                        </th>
                                        <th class="border-b border-l border-border p-6 text-center bg-primary/5 text-primary">STATUS</th>
                                        <th class="border-b border-l border-border p-6 text-left">OBSERVATION</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border">
                                    <tr v-for="student in (filteredResults as any[])" :key="student.id" class="group hover:bg-primary/[0.01] transition-all">
                                        <td class="sticky left-0 z-20 border-border bg-card p-4 group-hover:bg-muted/30 transition-all shadow-[8px_0_12px_-8px_rgba(0,0,0,0.05)]">
                                            <div class="flex items-center gap-4">
                                                <Avatar class="h-11 w-11 rounded-lg border border-border shadow-sm">
                                                    <AvatarImage :src="student.photo" />
                                                    <AvatarFallback class="bg-primary/5 text-primary text-[10px] font-black">{{ student.name.substring(0,2).toUpperCase() }}</AvatarFallback>
                                                </Avatar>
                                                <div>
                                                    <p class="text-xs font-black text-foreground group-hover:text-primary transition-colors uppercase tracking-tight">{{ student.name }}</p>
                                                    <p class="text-[9px] font-bold text-muted-foreground/50 uppercase tracking-widest">{{ student.admission_number }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        <td v-for="c in criteria" :key="c.id" class="border-l border-border p-4 text-center">
                                            <div class="flex flex-col items-center gap-2">
                                                 <div v-if="gradingMode === 'rubric'" class="relative w-full max-w-[80px]">
                                                    <select 
                                                        :value="student.ratings[c.id]"
                                                        @change="updateRating(student.id, c.id, ($event.target as HTMLSelectElement).value)"
                                                        class="h-10 w-full appearance-none rounded-md border border-border bg-muted/5 px-2 text-center text-[10px] font-black outline-none transition-all hover:bg-primary/5 focus:ring-1 focus:ring-primary/30"
                                                        :style="{ color: getScaleColor(student.ratings[c.id]) }"
                                                    >
                                                        <option value="null">-</option>
                                                        <option v-for="s in ratingScales" :key="s.id" :value="s.id">{{ s.code }}</option>
                                                    </select>
                                                    <ChevronDown class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-primary opacity-20" />
                                                 </div>

                                                 <div v-else class="relative w-full max-w-[80px]">
                                                    <Input 
                                                        :value="student.marks[c.id]"
                                                        @input="updateMarks(student.id, c.id, ($event.target as HTMLInputElement).value)"
                                                        class="h-10 w-full text-center text-[11px] font-black rounded-md border-border bg-muted/5 focus:bg-background transition-all"
                                                        placeholder="0.0"
                                                        type="number"
                                                    />
                                                 </div>
                                            </div>
                                        </td>

                                        <td class="border-l border-border p-4 text-center bg-primary/[0.01]">
                                            <Badge 
                                                v-if="getScaleCode(student.ratings[criteria[0]?.id])"
                                                class="h-10 w-16 items-center justify-center rounded-md border-0 text-[10px] font-black shadow-sm text-white"
                                                :style="{ backgroundColor: getScaleColor(student.ratings[criteria[0]?.id]) }"
                                            >
                                                {{ getScaleCode(student.ratings[criteria[0]?.id]) }}
                                            </Badge>
                                            <span v-else class="text-[10px] font-black text-muted-foreground opacity-10">TBD</span>
                                        </td>
                                        
                                        <td class="border-l border-border p-4 min-w-[200px]">
                                            <Input 
                                                v-model="student.remarks" 
                                                placeholder="OBSERVATION..." 
                                                class="h-10 rounded-md border-border bg-muted/5 text-[9px] font-black uppercase tracking-widest focus:bg-background transition-all"
                                            />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-else class="flex flex-col items-center justify-center py-40 opacity-20">
                                <History class="h-14 w-14 mb-4" />
                                <p class="text-[10px] font-black uppercase tracking-widest">Repository Empty or Section Locked</p>
                            </div>
                         </div>
                    </div>
                </div>
            </div>
        </div>

        <BulkUploadDialog
            v-if="bulkUploadOpen"
            v-model:open="bulkUploadOpen"
            title="Mass Registration"
            description="Import grades from the standard CSV template. Auto-sync will persist records upon upload."
            :template-url="route('assessments.grading.template', { assessment: assessment.id })"
            :upload-url="route('assessments.grading.import', { assessment: assessment.id })"
            @success="router.reload()"
        />

    </AppLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(var(--primary), 0.1); border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(var(--primary), 0.2); }
th.sticky { box-shadow: 4px 0 12px -8px rgba(0,0,0,0.1); }
</style>
