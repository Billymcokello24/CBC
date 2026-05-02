<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    Save,
    Search,
    Zap,
    X,
    Upload,
    Plus,
    Calendar
} from 'lucide-vue-next';
import { ref, computed, onMounted, watch } from 'vue';
import BulkUploadDialog from '@/components/assessments/BulkUploadDialog.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import axios from 'axios';
import { route } from 'ziggy-js';

const props = defineProps<{
    assessment: any;
    allAssessments: Array<any>;
    students: Array<any>;
    existingRatings: Record<number, any[]>;
    studentAssessments: Record<number, any>; // Total marks
    rubric: any; // Dynamic rubric
    ratingScales: Array<any>;
    stats: any;
    gradeLevels: Array<any>;
}>();

const breadcrumbs = [
    { title: 'Institutional Control', href: '/dashboard' },
    { title: 'Curriculum Management', href: '/assessments' },
    { title: 'Assessment Terminal', href: '#' },
];

const activeTab = ref('entry');
const saving = ref(false);
const lastSavedAt = ref<Date | null>(null);
const bulkUploadOpen = ref(false);
const searchQuery = ref('');

const criteria = computed(() => {
    if (!props.assessment?.items) return [];
    return props.assessment.items.map((item: any) => {
        let name = item.name;
        // If the name is null or just generic "Indicator X", try fallback
        if (!name || name.toLowerCase().startsWith('indicator ')) {
            name = item.indicator?.indicator || item.indicator?.name || name || `Criterion ${item.display_order + 1}`;
        }
        
        return {
            id: item.id,
            name: name,
            outOf: item.total_marks || 100,
            competency: item.indicator?.competency?.name || 'General'
        };
    });
});

const results = ref<any[]>([]);

onMounted(() => {
    if (props.students) {
        results.value = props.students.map((student) => {
            const studentRatings = props.existingRatings?.[student.id] || [];
            const criteriaMarks: Record<number, any> = {};
            
            props.assessment?.items?.forEach((item: any) => {
                const existing = studentRatings.find(r => r.assessment_item_id === item.id); 
                criteriaMarks[item.id] = existing ? existing.marks : null;
            });

            const overall = props.studentAssessments?.[student.id];

            return {
                id: student.id,
                name: `${student.first_name} ${student.last_name}`,
                admission_number: student.admission_number,
                marks: criteriaMarks,
                total: overall?.marks_obtained ?? null,
                remarks: overall?.teacher_comments || (studentRatings.length > 0 ? studentRatings[0].feedback : ''),
            };
        });
    }
});

const translateMarksToRubric = (marks: number | null, outOf: number) => {
    if (marks === null || marks === undefined) return null;
    const percent = (marks / outOf) * 100;

    // Use dynamic rubric if available
    const levels = props.rubric?.criteria?.[0]?.levels;
    if (levels && levels.length > 0) {
        const matchingLevel = levels.find((l: any) => percent >= l.min_score && percent <= l.max_score);
        if (matchingLevel) return matchingLevel.points;
    }

    // Default fallback
    if (percent >= 80) return 4;
    if (percent >= 60) return 3;
    if (percent >= 40) return 2;
    return 1;
};

const updateMarks = async (studentId: number, criterionId: number | 'total', marks: any) => {
    const student = results.value.find(s => s.id === studentId);
    if (!student) return;

    const numMarks = marks === '' ? null : parseFloat(marks);
    
    if (criterionId === 'total') {
        student.total = numMarks;
    } else {
        student.marks[criterionId] = numMarks;
    }
    
    try {
        const payload: any = {
            student_id: studentId,
            assessment_id: props.assessment.id,
            marks: numMarks,
        };

        if (criterionId !== 'total') {
            payload.assessment_item_id = criterionId;
            payload.out_of = criteria.value.find((c: any) => c.id === criterionId)?.outOf || 100;
        }

        await axios.post(route('assessments.grading.quick-save'), payload);
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
    if (!score || !props.ratingScales) return '#94a3b8';
    return props.ratingScales.find((s: any) => s.id === Math.round(score))?.color || '#ef4444';
};

const filteredResults = computed(() => {
    let list = results.value;
    
    if (searchQuery.value) {
        const q = searchQuery.value.toLowerCase();
        list = list.filter((s: any) => 
            s.name.toLowerCase().includes(q) || 
            s.admission_number.toLowerCase().includes(q)
        );
    }
    
    if (activeTab.value === 'intervention') {
        // Only show BE and AE students
        list = list.filter((s: any) => {
            const mark = getEffectiveMark(s);
            const totalPossible = props.assessment.total_marks || criteria.value.reduce((sum: number, item: any) => sum + (item.outOf || 100), 0);
            const rubricLevel = translateMarksToRubric(mark as number, totalPossible);
            return (rubricLevel || 1) <= 2;
        });
    }

    // Auto rank: descending order of effective mark
    return [...list].sort((a: any, b: any) => (getEffectiveMark(b) as number) - (getEffectiveMark(a) as number));
});

const getEffectiveMark = (student: any) => {
    if (student.total !== null && student.total !== '') return Number(student.total);
    return Object.values(student.marks).reduce((a: any, b: any) => (Number(a) || 0) + (Number(b) || 0), 0);
};

const submitAll = () => {
    saving.value = true;
    const studentsData = results.value.map(student => ({
        student_id: student.id,
        marks: student.marks,
        total: student.total,
        remarks: student.remarks
    }));

    router.post(
        route('assessments.grading.store', { assessment: props.assessment.id }),
        { ratings: studentsData },
        { onFinish: () => (saving.value = false) }
    );
};
</script>

<template>
    <Head title="Grading Terminal" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-[1600px] space-y-4 p-4 fade-in sm:p-6 md:p-8">
            
            <!-- HEADER SECTION (Dashboard Style) -->
            <div class="flex flex-col md:flex-row md:items-end justify-between border-b pb-6 border-border/40 gap-6 mb-2">
                <div class="space-y-1.5 px-1">
                    <div class="flex items-center gap-2">
                        <div class="h-2 w-2 rounded-full bg-primary animate-pulse shadow-[0_0_8px_rgba(var(--primary),0.6)]"></div>
                        <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-[0.2em] text-primary select-none">Institutional Assessment Core</span>
                    </div>
                    <h1 class="text-3xl font-black tracking-tight text-foreground sm:text-4xl lg:text-5xl">
                       <span class="text-primary/90">{{ assessment.subject?.name }}</span> Terminal
                    </h1>
                     <p class="text-[11px] sm:text-xs text-muted-foreground font-bold flex items-center gap-2">
                        <Calendar class="h-3 w-3 opacity-50" />
                        G{{ assessment.class?.grade_level?.id }} • Term {{ assessment.academic_term_id }} • {{ assessment.class?.name }}
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Button variant="outline" @click="bulkUploadOpen = true" class="h-12 px-8 rounded-2xl border-border/60 text-[10px] font-black uppercase tracking-widest hover:bg-muted/50 transition-all flex items-center gap-3">
                        <Upload class="h-4 w-4" /> Bulk Sync Records
                    </Button>
                </div>
            </div>

            <!-- KPI GRID (Dashboard Style) -->
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
                <div v-for="(stat, i) in [
                    { label: 'Total Learners', val: students?.length || 0, color: 'text-foreground' },
                    { label: 'Exceeds (EE)', val: stats?.ee || 0, color: 'text-emerald-500' },
                    { label: 'Meets (ME)', val: stats?.me || 0, color: 'text-blue-500' },
                    { label: 'Approaching (AE)', val: stats?.ae || 0, color: 'text-amber-500' },
                    { label: 'Below (BE)', val: stats?.be || 0, color: 'text-rose-500' }
                ]" :key="i" class="bg-card rounded-2xl border p-5 shadow-sm hover:shadow-md transition-all group overflow-hidden relative">
                    <p class="text-[9px] font-black uppercase tracking-widest text-muted-foreground group-hover:text-primary transition-colors mb-2 whitespace-nowrap">{{ stat.label }}</p>
                    <h3 class="text-3xl font-black leading-none tracking-tighter" :class="stat.color">{{ stat.val }}</h3>
                    <div class="absolute -bottom-2 -right-2 h-10 w-10 opacity-[0.02] bg-primary group-hover:opacity-[0.05] transition-all rounded-full group-hover:scale-150"></div>
                </div>
            </div>

            <!-- MAIN DATA PANEL (Dashboard Style) -->
            <div class="rounded-2xl border bg-card/60 backdrop-blur-xl shadow-sm overflow-hidden flex flex-col min-h-[600px]">
                <!-- Panel Header -->
                <div class="flex items-center justify-between border-b px-8 py-6 bg-muted/5 gap-4">
                    <div class="flex items-center gap-8 overflow-x-auto no-scrollbar">
                         <button 
                            v-for="tab in ['Entry', 'Analytics', 'Intervention', 'Competency']" 
                            :key="tab"
                            @click="activeTab = tab.toLowerCase()"
                            class="text-[10px] font-black uppercase tracking-[0.25em] transition-all relative whitespace-nowrap"
                            :class="activeTab === tab.toLowerCase() ? 'text-primary' : 'text-muted-foreground/60 hover:text-foreground'"
                        >
                            {{ tab }} Sheet
                            <div v-if="activeTab === tab.toLowerCase()" class="absolute -bottom-2 left-0 w-full h-[3px] bg-primary rounded-full"></div>
                        </button>
                    </div>

                    <div class="flex items-center gap-4 min-w-[200px]">
                        <div class="flex items-center gap-3 bg-muted/50 rounded-2xl px-6 py-2.5 border border-border/40 w-full transition-all focus-within:ring-2 focus-within:ring-primary/20">
                            <Search class="h-4 w-4 text-muted-foreground/30" />
                            <input 
                                v-model="searchQuery"
                                type="text" 
                                placeholder="Filter Registry..." 
                                class="bg-transparent border-none text-[10px] font-black tracking-widest text-foreground placeholder:text-muted-foreground/20 focus:ring-0 p-0 uppercase"
                            />
                        </div>
                    </div>
                </div>

                <div class="flex-1 overflow-x-auto no-scrollbar custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b bg-muted/5">
                                <th class="p-4 w-12 text-center text-[9px] font-black uppercase tracking-[0.2em] text-muted-foreground/50">#</th>
                                <th class="p-8 text-[9px] font-black uppercase tracking-[0.2em] text-muted-foreground/50" :class="activeTab === 'entry' ? 'w-[320px]' : 'w-[400px]'">Learner Repository</th>
                                
                                <!-- Entry Specific Headers -->
                                <template v-if="activeTab === 'entry'">
                                    <th v-for="c in criteria" :key="c.id" class="p-8 text-center text-[9px] font-black uppercase tracking-[0.2em] text-muted-foreground/50">
                                        {{ c.name }}
                                    </th>
                                    <th class="p-8 text-center text-[9px] font-black uppercase tracking-[0.2em] text-primary w-[160px]">Master Score</th>
                                </template>

                                <!-- Analytics Specific Headers -->
                                <template v-if="activeTab === 'analytics'">
                                    <th class="p-8 text-center text-[9px] font-black uppercase tracking-[0.2em] text-muted-foreground/50">Raw Score</th>
                                    <th class="p-8 text-center text-[9px] font-black uppercase tracking-[0.2em] text-muted-foreground/50">Percentage</th>
                                    <th class="p-8 text-center text-[9px] font-black uppercase tracking-[0.2em] text-muted-foreground/50">Trajectory</th>
                                </template>

                                <!-- Competency Specific Headers -->
                                <template v-if="activeTab === 'competency'">
                                    <th v-for="comp in [...new Set(criteria.map(c => c.competency))]" :key="comp" class="p-8 text-center text-[9px] font-black uppercase tracking-[0.2em] text-muted-foreground/50">
                                        {{ comp }}
                                    </th>
                                </template>

                                <!-- Intervention Specific Headers -->
                                <template v-if="activeTab === 'intervention'">
                                    <th class="p-8 text-center text-[9px] font-black uppercase tracking-[0.2em] text-muted-foreground/50">Risk Level</th>
                                    <th class="p-8 text-left text-[9px] font-black uppercase tracking-[0.2em] text-muted-foreground/50">Recommended Intervention</th>
                                </template>

                                <th class="p-8 text-center text-[9px] font-black uppercase tracking-[0.2em] text-muted-foreground/50 w-[140px]">Overall rating</th>
                                <th v-if="activeTab === 'entry'" class="p-8 text-left text-[9px] font-black uppercase tracking-[0.2em] text-muted-foreground/50">Professional Remarks</th>
                                <th class="p-8 w-20"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/20">
                            <tr 
                                v-for="(student, idx) in filteredResults" 
                                :key="student.id"
                                class="group hover:bg-primary/[0.01] transition-all"
                            >
                                <td class="p-4 text-center text-[10px] font-black text-muted-foreground/40">{{ idx + 1 }}</td>
                                <td class="p-8">
                                    <div class="flex items-center gap-5">
                                         <Avatar class="h-10 w-10 ring-2 ring-muted group-hover:ring-primary/20 transition-all shrink-0">
                                            <AvatarFallback class="bg-primary/5 text-primary text-[10px] font-black uppercase">{{ student.name.substring(0, 2) }}</AvatarFallback>
                                        </Avatar>
                                        <div class="flex flex-col leading-tight min-w-0">
                                            <span class="text-[11px] font-black text-foreground truncate group-hover:text-primary transition-colors uppercase tracking-tight">{{ student.name }}</span>
                                            <span class="text-[9px] font-black text-muted-foreground opacity-60 mt-1 uppercase tracking-widest">{{ student.admission_number }}</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Entry Mode -->
                                <template v-if="activeTab === 'entry'">
                                    <td v-for="c in criteria" :key="c.id" class="p-4 text-center">
                                        <div class="relative group/input inline-block w-[90px]">
                                            <input 
                                                type="number"
                                                :value="student.marks[c.id]"
                                                @input="updateMarks(student.id, c.id, ($event.target as HTMLInputElement).value)"
                                                class="w-full h-12 bg-muted/40 border border-border/40 rounded-xl px-4 text-xs font-black text-center focus:outline-none focus:ring-2 focus:ring-primary/20 focus:bg-card transition-all text-muted-foreground/60"
                                                placeholder="--"
                                            />
                                        </div>
                                    </td>
                                    <td class="p-4 text-center">
                                        <div class="relative group/input inline-block w-[110px]">
                                            <input 
                                                type="number"
                                                v-model="student.total"
                                                @input="updateMarks(student.id, 'total', ($event.target as HTMLInputElement).value)"
                                                class="w-full h-12 bg-primary/5 border-2 border-primary/20 rounded-xl px-4 text-sm font-black text-center focus:outline-none focus:ring-2 focus:ring-primary/40 focus:bg-card transition-all text-primary"
                                                :placeholder="Object.values(student.marks).some(v => v !== null) ? 'Auto' : '--'"
                                            />
                                        </div>
                                    </td>
                                </template>

                                <!-- Analytics Mode -->
                                <template v-if="activeTab === 'analytics'">
                                    <td class="p-4 text-center font-black text-xs text-foreground uppercase tracking-widest">
                                        {{ getEffectiveMark(student) }} / {{ props.assessment.total_marks || criteria.reduce((sum: number, item: any) => sum + (item.outOf || 100), 0) }}
                                    </td>
                                    <td class="p-4 text-center">
                                         <Badge variant="outline" class="h-7 px-4 rounded-lg bg-primary/5 border-primary/20 text-[10px] font-black text-primary uppercase">
                                            {{ Math.round(((getEffectiveMark(student) as number) / (props.assessment.total_marks || criteria.reduce((sum: number, item: any) => sum + (item.outOf || 100), 0))) * 100) }}% Mastery
                                         </Badge>
                                    </td>
                                    <td class="p-4 text-center">
                                        <span class="text-[10px] font-black uppercase text-emerald-500 flex items-center justify-center gap-1">
                                            <Zap class="h-3 w-3" /> Positive
                                        </span>
                                    </td>
                                </template>

                                <!-- Competency Mode -->
                                <template v-if="activeTab === 'competency'">
                                    <td v-for="comp in [...new Set(criteria.map(c => c.competency))]" :key="comp" class="p-4 text-center">
                                         <div class="flex flex-col items-center">
                                            <span class="text-[10px] font-black text-foreground">
                                                {{ Math.round(Object.entries(student.marks)
                                                    .filter(([id]) => criteria.find(cr => String(cr.id) === id)?.competency === comp)
                                                    .reduce((sum, [, mark]) => sum + (Number(mark) || 0), 0)) 
                                                }} Pts
                                            </span>
                                            <span class="text-[8px] font-bold text-muted-foreground/40 uppercase tracking-widest">Mastery Level</span>
                                         </div>
                                    </td>
                                </template>

                                <!-- Intervention Mode -->
                                <template v-if="activeTab === 'intervention'">
                                    <td class="p-4 text-center">
                                         <Badge class="h-7 px-4 rounded-lg bg-rose-500 text-white text-[10px] font-black uppercase border-none shadow-none">
                                            Critical
                                         </Badge>
                                    </td>
                                    <td class="p-4">
                                        <p class="text-[10px] font-bold text-muted-foreground italic truncate max-w-[300px]">
                                            Differentiated instruction & remedial support required for {{ assessment.subject?.name }}...
                                        </p>
                                    </td>
                                </template>

                                <!-- Shared Overall Rating -->
                                <td class="p-4 text-center text-xs font-black">
                                    <div class="flex flex-col items-center gap-1.5">
                                        <Badge 
                                            variant="outline" 
                                            class="h-6 px-4 rounded-full text-[9px] font-black tracking-widest border-2 shadow-sm"
                                            :style="{ 
                                                borderColor: `${getScaleColor(translateMarksToRubric(
                                                    getEffectiveMark(student) as number,
                                                    assessment.total_marks || criteria.reduce((sum: number, item: any) => sum + (item.outOf || 100), 0)
                                                ))}30`,
                                                color: getScaleColor(translateMarksToRubric(
                                                    getEffectiveMark(student) as number,
                                                    assessment.total_marks || criteria.reduce((sum: number, item: any) => sum + (item.outOf || 100), 0)
                                                )),
                                                backgroundColor: `${getScaleColor(translateMarksToRubric(
                                                    getEffectiveMark(student) as number,
                                                    assessment.total_marks || criteria.reduce((sum: number, item: any) => sum + (item.outOf || 100), 0)
                                                ))}10`
                                            }"
                                        >
                                            {{ getScaleCode(
                                                translateMarksToRubric(
                                                    getEffectiveMark(student) as number,
                                                    assessment.total_marks || criteria.reduce((sum: number, item: any) => sum + (item.outOf || 100), 0)
                                                )
                                            ) || '--' }}
                                        </Badge>
                                    </div>
                                </td>

                                <td v-if="activeTab === 'entry'" class="p-4">
                                    <input 
                                        v-model="student.remarks"
                                        class="w-full h-12 px-6 bg-muted/40 border border-border/40 rounded-xl text-[10px] font-bold text-foreground placeholder:text-muted-foreground/20 focus:outline-none focus:ring-1 focus:ring-primary/20 focus:bg-card transition-all"
                                        placeholder="Observations for Terminal Report Card..."
                                    />
                                </td>

                                <td class="p-4 pr-12 text-right">
                                    <button class="h-10 w-10 flex items-center justify-center bg-muted/50 border border-border/40 rounded-xl text-muted-foreground/20 hover:bg-destructive/10 hover:text-destructive transition-all opacity-0 group-hover:opacity-100">
                                        <X class="h-4 w-4" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer Operations Bar -->
                <div class="p-8 border-t border-border/40 bg-muted/5 flex items-center justify-between">
                    <div class="flex items-center gap-6">
                        <button class="h-12 px-8 rounded-xl border border-border/60 text-[10px] font-black uppercase tracking-widest text-muted-foreground hover:bg-card transition-all flex items-center gap-3">
                            <Plus class="h-4 w-4" /> Add Record Case
                        </button>
                        
                        <div v-if="lastSavedAt" class="hidden md:flex items-center gap-3 text-[9px] font-black uppercase tracking-[0.2em] text-emerald-600 opacity-60">
                            <Zap class="h-3.5 w-3.5 animate-pulse" /> Live System Sync Active
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4">
                         <Button 
                            variant="ghost" 
                            @click="router.visit(route('assessments.index'))"
                            class="h-14 px-8 rounded-2xl text-[10px] font-black uppercase tracking-widest text-muted-foreground hover:text-foreground"
                        >
                            Cancel
                        </Button>
                        <Button 
                            @click="submitAll" 
                            :disabled="saving"
                            class="h-14 bg-primary text-primary-foreground hover:scale-[1.02] active:scale-95 px-12 rounded-2xl font-black text-[11px] uppercase tracking-[0.2em] transition-all flex items-center gap-4 shadow-xl shadow-primary/20"
                        >
                            <Save v-if="!saving" class="h-5 w-5" />
                            <span v-else class="h-5 w-5 border-2 border-primary-foreground border-t-transparent rounded-full animate-spin"></span>
                            {{ saving ? 'Syncing...' : 'Finalize & Post' }}
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>

    <BulkUploadDialog
        v-if="bulkUploadOpen"
        v-model:open="bulkUploadOpen"
        title="Institutional Bulk Import"
        description="Mass data ingestion via encrypted performance matrix. Logic auto-maps to specific criteria items."
        :template-url="route('assessments.grading.template', { assessment: assessment.id })"
        :upload-url="route('assessments.grading.import', { assessment: assessment.id })"
        @success="router.reload()"
    />
</template>

<style scoped>
.fade-in {
    animation: fadeIn 0.5s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}

.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

.custom-scrollbar::-webkit-scrollbar { width: 3px; height: 3px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: hsl(var(--border)); border-radius: 10px; }

input[type="number"] {
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
}

input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
</style>
