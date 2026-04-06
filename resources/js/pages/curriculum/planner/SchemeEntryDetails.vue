<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft, Calendar, BookOpen, Layers, Target,
    Activity, ClipboardCheck, MessageSquare, Info,
    Save, Loader2, Sparkles, AlertCircle, CheckCircle2,
    BookMarked, HelpCircle, GraduationCap, FileDown
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    scheme: any;
    entry: any;
    strands: any[];
}>();

const { props: pageProps } = usePage<any>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Curriculum', href: '#' },
    { title: 'Academic Planner', href: '/curriculum/planner/schemes' },
    { title: props.scheme.title, href: `/curriculum/planner/schemes/${props.scheme.id}` },
    { title: `Week ${props.entry.week_number} - Lesson ${props.entry.lesson_number}`, href: '#' },
];

const activeTab = ref('overview');
const isEditing = ref(false);

const form = useForm({
    week_number: props.entry.week_number,
    lesson_number: props.entry.lesson_number,
    topic: props.entry.topic,
    strand_id: props.entry.strand_id,
    sub_strand_id: props.entry.sub_strand_id,
    duration_minutes: props.entry.duration_minutes,
    lesson_date: props.entry.lesson_date,
    key_vocabulary: props.entry.key_vocabulary,
    learning_outcomes: props.entry.learning_outcomes,
    learning_activities: props.entry.learning_activities,
    teacher_activities: props.entry.teacher_activities,
    introduction: props.entry.introduction,
    lesson_development: props.entry.lesson_development,
    conclusion: props.entry.conclusion,
    resources: props.entry.resources,
    references: props.entry.references,
    assessment: props.entry.assessment,
    remarks: props.entry.remarks,
    reflection: props.entry.reflection,
    homework: props.entry.homework,
    core_competencies: props.entry.core_competencies || [],
    pci: props.entry.pci || [],
    inquiry_questions: props.entry.inquiry_questions ?? '',
});

const submit = () => {
    form.put(`/curriculum/planner/schemes/${props.scheme.id}/entries/${props.entry.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            isEditing.value = false;
        },
        onError: (errors) => {
            console.error('Validation Errors:', errors);
        }
    });
};

const downloadPdf = () => {
    window.open(`/curriculum/planner/schemes/${props.scheme.id}/entries/${props.entry.id}/download`, '_blank');
};

const tabs = [
    { id: 'overview', name: 'Overview', icon: Info },
    { id: 'instructional', name: 'Instructional', icon: BookOpen },
    { id: 'delivery', name: 'Delivery', icon: Activity },
    { id: 'outcomes', name: 'Outcomes', icon: Target },
];

const cbcCompetencies = [
    'Communication and Collaboration',
    'Self-efficacy',
    'Critical Thinking and Problem Solving',
    'Creativity and Imagination',
    'Citizenship',
    'Digital Literacy',
    'Learning to Learn'
];

const cbcPCIs = [
    'Environmental Education',
    'Health Education',
    'Life Skills Education',
    'Values Education',
    'Human Rights Education',
    'Gender Issues',
    'Financial Literacy'
];

const getStatusColor = (status: string) => {
    switch (status?.toLowerCase()) {
        case 'approved': return 'bg-emerald-500 text-white shadow-sm';
        case 'submitted': return 'bg-amber-500 text-white shadow-sm';
        case 'rejected': return 'bg-rose-500 text-white shadow-sm';
        default: return 'bg-blue-600 text-white shadow-sm';
    }
};
</script>

<template>
    <Head :title="`Entry Details - ${entry.topic}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 animate-in fade-in duration-500 max-w-[1600px] mx-auto pb-20 p-6 md:p-8">
            <!-- Header section -->
            <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-5">
                    <Button variant="ghost" size="icon" as-child class="h-10 w-10 text-muted-foreground hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all border border-transparent hover:border-blue-100">
                        <Link :href="`/curriculum/planner/schemes/${scheme.id}`"><ArrowLeft class="h-5 w-5" /></Link>
                    </Button>
                    <div class="flex items-center gap-6">
                        <div class="h-20 w-20 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 text-white flex items-center justify-center shadow-lg ring-4 ring-blue-50 transition-transform duration-500 hover:scale-105">
                            <BookMarked class="h-10 w-10" />
                        </div>
                        <div>
                            <div class="flex items-center gap-3">
                                <h1 class="text-3xl font-bold tracking-tight text-foreground">{{ entry.topic }}</h1>
                                <Badge :class="getStatusColor(scheme.status)" class="rounded-lg px-3 py-1 text-[10px] font-bold uppercase tracking-widest border-0 shadow-sm">
                                    {{ scheme.status }}
                                </Badge>
                            </div>
                            <div class="flex items-center gap-2 mt-2 text-sm font-medium text-muted-foreground">
                                <span class="text-blue-600">Week {{ entry.week_number }}</span>
                                <span class="h-1 w-1 bg-border rounded-full"></span>
                                <span class="text-indigo-600">Lesson {{ entry.lesson_number }}</span>
                                <span class="h-1 w-1 bg-border rounded-full"></span>
                                <span>{{ scheme.subject?.name }}</span>
                                <span class="h-1 w-1 bg-border rounded-full"></span>
                                <span>{{ scheme.grade_level?.name }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <Button variant="outline" class="h-11 px-5 rounded-xl border-slate-200 text-slate-600 font-semibold hover:bg-slate-50 transition-all" @click="downloadPdf">
                        <FileDown class="mr-2 h-4 w-4" />
                        Download PDF
                    </Button>
                    <Button v-if="!isEditing" variant="outline" class="h-11 px-5 rounded-xl border-blue-100 bg-blue-50/50 hover:bg-blue-600 hover:text-white hover:border-blue-600 text-blue-700 font-semibold shadow-sm transition-all" @click="isEditing = true">
                        <Edit2 class="mr-2 h-4 w-4 text-amber-500" />
                        Edit Instruction
                    </Button>
                    <Button v-if="isEditing" @click="submit" :disabled="form.processing" class="bg-blue-600 hover:bg-blue-700 h-11 px-6 shadow-md rounded-xl font-semibold text-white transition-all transform active:scale-95">
                        <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                        <Save v-else class="mr-2 h-4 w-4" />
                        Save Changes
                    </Button>
                </div>
            </div>

            <!-- Main Content Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Navigation Sidebar -->
                <div class="lg:col-span-3 space-y-4">
                    <div class="flex flex-col gap-1.5 rounded-2xl border border-border bg-card p-2 shadow-sm">
                        <button
                            v-for="tab in tabs"
                            :key="tab.id"
                            @click="activeTab = tab.id"
                            class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-xl transition-all duration-200 group relative"
                            :class="activeTab === tab.id
                                ? 'bg-blue-600 text-white shadow-md'
                                : 'text-muted-foreground hover:bg-muted hover:text-foreground'"
                        >
                            <component :is="tab.icon" class="h-4.5 w-4.5" :class="activeTab === tab.id ? 'text-white' : 'text-muted-foreground/50 group-hover:text-foreground'" />
                            {{ tab.name }}
                            <div v-if="activeTab === tab.id" class="ml-auto">
                                <div class="h-1.5 w-1.5 bg-blue-200 rounded-full animate-pulse"></div>
                            </div>
                        </button>
                    </div>

                    <!-- Scheme Context Card -->
                    <div class="rounded-2xl border border-border bg-foreground p-6 text-background shadow-xl relative overflow-hidden group">
                        <div class="absolute -right-6 -bottom-6 opacity-5 group-hover:scale-110 transition-transform duration-1000">
                             <GraduationCap class="h-32 w-32" />
                        </div>
                        <p class="text-[9px] font-black uppercase tracking-[0.2em] opacity-60 mb-5 italic border-l-2 border-blue-500 pl-2">Associated Scheme</p>
                        <h4 class="text-sm font-bold mb-4 line-clamp-2 leading-relaxed">{{ scheme.title }}</h4>
                        <div class="space-y-4 relative z-10">
                            <div class="flex items-center justify-between text-[10px] font-bold">
                                <span class="opacity-60 uppercase tracking-widest">Term</span>
                                <span class="bg-blue-500 px-2 py-0.5 rounded text-white">{{ scheme.academic_term?.name || 'N/A' }}</span>
                            </div>
                            <div class="flex items-center justify-between text-[10px] font-bold border-t border-white/10 pt-4">
                                <span class="opacity-60 uppercase tracking-widest">Prepared By</span>
                                <span>{{ scheme.prepared_by?.name || 'Academic Dept' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Content Area -->
                <div class="lg:col-span-9 space-y-6 animate-in slide-in-from-bottom-4 duration-700">
                    
                    <!-- Overview Tab -->
                    <div v-if="activeTab === 'overview'" class="space-y-6">
                        <div class="bg-card rounded-2xl border border-border p-8 shadow-sm">
                            <div class="flex items-center justify-between border-b border-border/50 pb-5 mb-8">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                                        <Info class="h-5 w-5" />
                                    </div>
                                    <h3 class="text-[11px] font-black text-foreground uppercase tracking-widest">General Information</h3>
                                </div>
                                <Badge variant="outline" class="text-[10px] font-bold uppercase tracking-wider px-3 border-blue-100 bg-blue-50/30 text-blue-600">Core Meta</Badge>
                            </div>

                            <div class="grid gap-x-8 gap-y-8 md:grid-cols-2 lg:grid-cols-3">
                                <!-- Week Number -->
                                <div class="space-y-1.5">
                                    <Label class="text-[11px] font-bold text-muted-foreground uppercase tracking-wider">Academic Week</Label>
                                    <div v-if="isEditing">
                                        <Input type="number" v-model="form.week_number" class="h-11 rounded-xl border-border bg-muted/30 font-medium focus:bg-background transition-all" />
                                        <InputError :message="form.errors.week_number" />
                                    </div>
                                    <p v-else class="text-[15px] font-bold text-foreground">Week {{ entry.week_number }}</p>
                                </div>

                                <!-- Lesson Number -->
                                <div class="space-y-1.5">
                                    <Label class="text-[11px] font-bold text-muted-foreground uppercase tracking-wider">Lesson Number</Label>
                                    <div v-if="isEditing">
                                        <Input type="number" v-model="form.lesson_number" class="h-11 rounded-xl border-border bg-muted/30 font-medium focus:bg-background transition-all" />
                                        <InputError :message="form.errors.lesson_number" />
                                    </div>
                                    <p v-else class="text-[15px] font-bold text-foreground">Lesson {{ entry.lesson_number }}</p>
                                </div>

                                <!-- Duration -->
                                <div class="space-y-1.5">
                                    <Label class="text-[11px] font-bold text-muted-foreground uppercase tracking-wider">Duration (Mins)</Label>
                                    <div v-if="isEditing">
                                        <Input type="number" v-model="form.duration_minutes" class="h-11 rounded-xl border-border bg-muted/30 font-medium focus:bg-background transition-all" />
                                        <InputError :message="form.errors.duration_minutes" />
                                    </div>
                                    <p v-else class="text-[15px] font-bold text-foreground">{{ entry.duration_minutes }} Minutes</p>
                                </div>

                                <!-- Strand -->
                                <div class="space-y-1.5 md:col-span-2">
                                    <Label class="text-[11px] font-bold text-muted-foreground uppercase tracking-wider">Strand (Topic Area)</Label>
                                    <div v-if="isEditing">
                                        <select v-model="form.strand_id" class="flex h-11 w-full rounded-xl border border-input bg-muted/30 px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 font-medium">
                                            <option value="">Select Strand</option>
                                            <option v-for="s in strands" :key="s.id" :value="s.id">{{ s.name }}</option>
                                        </select>
                                        <InputError :message="form.errors.strand_id" />
                                    </div>
                                    <p v-else class="text-[15px] font-bold text-blue-600">{{ entry.strand?.name || '--' }}</p>
                                </div>

                                <!-- Sub-Strand -->
                                <div class="space-y-1.5 md:col-span-1">
                                    <Label class="text-[11px] font-bold text-muted-foreground uppercase tracking-wider">Sub-Strand</Label>
                                    <div v-if="isEditing">
                                        <select v-model="form.sub_strand_id" class="flex h-11 w-full rounded-xl border border-input bg-muted/30 px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 font-medium">
                                            <option value="">Select Sub-Strand (Optional)</option>
                                            <option v-for="ss in strands.find(s => s.id == form.strand_id)?.sub_strands || []" :key="ss.id" :value="ss.id">{{ ss.name }}</option>
                                        </select>
                                        <InputError :message="form.errors.sub_strand_id" />
                                    </div>
                                    <p v-else class="text-[15px] font-bold text-foreground">{{ entry.sub_strand?.name || '--' }}</p>
                                </div>

                                <!-- Lesson Date -->
                                <div class="space-y-1.5">
                                    <Label class="text-[11px] font-bold text-muted-foreground uppercase tracking-wider">Intended Date</Label>
                                    <div v-if="isEditing">
                                        <Input type="date" v-model="form.lesson_date" class="h-11 rounded-xl border-border bg-muted/30 font-medium focus:bg-background transition-all" />
                                        <InputError :message="form.errors.lesson_date" />
                                    </div>
                                    <p v-else class="text-[15px] font-bold text-foreground">{{ entry.lesson_date ? new Date(entry.lesson_date).toLocaleDateString() : 'Unscheduled' }}</p>
                                </div>

                                <!-- Key Vocabulary -->
                                <div class="space-y-1.5 md:col-span-2">
                                    <Label class="text-[11px] font-bold text-muted-foreground uppercase tracking-wider">Key Vocabulary</Label>
                                    <div v-if="isEditing">
                                        <Input v-model="form.key_vocabulary" class="h-11 rounded-xl border-border bg-muted/30 font-medium focus:bg-background transition-all" />
                                        <InputError :message="form.errors.key_vocabulary" />
                                    </div>
                                    <p v-else class="text-[15px] font-bold text-foreground">{{ entry.key_vocabulary || '--' }}</p>
                                </div>

                                <!-- Topic -->
                                <div class="space-y-1.5 md:col-span-3">
                                    <Label class="text-[11px] font-bold text-muted-foreground uppercase tracking-wider">Sub-Strand / Lesson Topic</Label>
                                    <div v-if="isEditing">
                                        <Input v-model="form.topic" class="h-11 rounded-xl border-border bg-muted/30 font-medium focus:bg-background transition-all" />
                                        <InputError :message="form.errors.topic" />
                                    </div>
                                    <p v-else class="text-[15px] font-bold text-foreground">{{ entry.topic }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Secondary Context Card -->
                        <div class="bg-indigo-600 rounded-3xl p-10 text-white shadow-2xl relative overflow-hidden group">
                            <div class="absolute -right-20 -top-20 opacity-10 group-hover:scale-125 transition-transform duration-1000 rotate-12">
                                 <HelpCircle class="h-64 w-64" />
                            </div>
                            <div class="relative z-10 max-w-2xl">
                                <h4 class="text-[10px] font-black uppercase tracking-[0.3em] opacity-70 mb-6">Instructional Focus</h4>
                                <div v-if="isEditing" class="space-y-4">
                                    <Label class="text-xs font-bold opacity-80 uppercase tracking-widest">Inquiry Questions</Label>
                                    <textarea v-model="form.inquiry_questions" class="w-full min-h-[120px] bg-white/10 border border-white/20 rounded-2xl p-6 text-sm font-medium outline-none focus:bg-white/20 transition-all" placeholder="What questions will drive this lesson?"></textarea>
                                </div>
                                <div v-else>
                                    <p class="text-2xl font-bold leading-tight mb-8">{{ entry.inquiry_questions || 'What do we intend to explore in this specific instructional unit?' }}</p>
                                    <div class="flex items-center gap-4">
                                        <div class="h-12 w-12 rounded-full bg-white/20 flex items-center justify-center backdrop-blur-md">
                                            <Sparkles class="h-6 w-6" />
                                        </div>
                                        <p class="text-xs font-semibold opacity-80 uppercase tracking-widest italic">Core Inquiry Strategy</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Instructional Tab -->
                    <div v-else-if="activeTab === 'instructional'" class="space-y-6">
                        <div class="bg-card rounded-2xl border border-border p-8 shadow-sm">
                            <div class="flex items-center justify-between border-b border-border/50 pb-5 mb-8">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                                        <BookOpen class="h-5 w-5" />
                                    </div>
                                    <h3 class="text-[11px] font-black text-foreground uppercase tracking-widest">Instructional Implementation</h3>
                                </div>
                            </div>

                            <div class="space-y-10">
                                <!-- Introduction -->
                                <div class="space-y-4">
                                    <Label class="text-[10px] font-black text-muted-foreground uppercase tracking-[0.15em] ml-1 flex items-center gap-2">
                                        <div class="h-1.5 w-1.5 bg-amber-500 rounded-full"></div> Lesson Introduction
                                    </Label>
                                    <div v-if="isEditing">
                                        <textarea v-model="form.introduction" class="w-full min-h-[120px] rounded-2xl border-border bg-muted/30 p-6 text-sm font-medium focus:bg-background transition-all outline-none focus:ring-4 focus:ring-blue-600/5"></textarea>
                                        <InputError :message="form.errors.introduction" />
                                    </div>
                                    <div v-else class="p-6 bg-slate-50/50 border border-border/40 rounded-2xl prose prose-sm max-w-none">
                                        <p class="text-[15px] font-medium text-foreground leading-relaxed">{{ entry.introduction || 'Establish psychological set and prior knowledge review.' }}</p>
                                    </div>
                                </div>

                                <!-- Lesson Development -->
                                <div class="space-y-4">
                                    <Label class="text-[10px] font-black text-muted-foreground uppercase tracking-[0.15em] ml-1 flex items-center gap-2">
                                        <div class="h-1.5 w-1.5 bg-blue-500 rounded-full"></div> Detailed Lesson Development
                                    </Label>
                                    <div v-if="isEditing">
                                        <textarea v-model="form.lesson_development" class="w-full min-h-[250px] rounded-2xl border-border bg-muted/30 p-6 text-sm font-medium focus:bg-background transition-all outline-none focus:ring-4 focus:ring-blue-600/5"></textarea>
                                        <InputError :message="form.errors.lesson_development" />
                                    </div>
                                    <div v-else class="p-8 bg-blue-50/20 border border-blue-100/50 rounded-2xl prose prose-sm max-w-none shadow-inner">
                                        <p class="text-[15px] whitespace-pre-wrap font-medium text-foreground leading-loose tracking-wide">{{ entry.lesson_development || 'No implementation details shared.' }}</p>
                                    </div>
                                </div>

                                <!-- Conclusion -->
                                <div class="space-y-4">
                                    <Label class="text-[10px] font-black text-muted-foreground uppercase tracking-[0.15em] ml-1 flex items-center gap-2">
                                        <div class="h-1.5 w-1.5 bg-emerald-500 rounded-full"></div> Lesson Conclusion
                                    </Label>
                                    <div v-if="isEditing">
                                        <textarea v-model="form.conclusion" class="w-full min-h-[120px] rounded-2xl border-border bg-muted/30 p-6 text-sm font-medium focus:bg-background transition-all outline-none focus:ring-4 focus:ring-blue-600/5"></textarea>
                                        <InputError :message="form.errors.conclusion" />
                                    </div>
                                    <div v-else class="p-6 bg-slate-50/50 border border-border/40 rounded-2xl prose prose-sm max-w-none">
                                        <p class="text-[15px] font-medium text-foreground leading-relaxed">{{ entry.conclusion || 'Wrap up and summarization.' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Tab -->
                    <div v-else-if="activeTab === 'delivery'" class="space-y-6">
                        <div class="bg-card rounded-2xl border border-border p-8 shadow-sm">
                            <div class="flex items-center justify-between border-b border-border/50 pb-5 mb-8">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                                        <Activity class="h-5 w-5" />
                                    </div>
                                    <h3 class="text-[11px] font-black text-foreground uppercase tracking-widest">Instructional Delivery</h3>
                                </div>
                            </div>

                            <div class="grid gap-8 md:grid-cols-2">
                                <!-- Learning Activities -->
                                <div class="space-y-4 md:col-span-2">
                                    <Label class="text-[10px] font-black text-muted-foreground uppercase tracking-[0.15em] ml-1">Learning Activities (Learner-Centered)</Label>
                                    <div v-if="isEditing">
                                        <textarea v-model="form.learning_activities" class="w-full min-h-[140px] rounded-2xl border-border bg-muted/30 p-6 text-sm font-medium focus:bg-background outline-none"></textarea>
                                        <InputError :message="form.errors.learning_activities" />
                                    </div>
                                    <div v-else class="p-6 bg-slate-50 border border-border/40 rounded-2xl">
                                        <p class="text-sm font-medium leading-relaxed">{{ entry.learning_activities || '--' }}</p>
                                    </div>
                                </div>

                                <!-- Teacher Activities -->
                                <div class="space-y-4 md:col-span-2">
                                    <Label class="text-[10px] font-black text-muted-foreground uppercase tracking-[0.15em] ml-1">Teacher Activities (Facilitation)</Label>
                                    <div v-if="isEditing">
                                        <textarea v-model="form.teacher_activities" class="w-full min-h-[140px] rounded-2xl border-border bg-muted/30 p-6 text-sm font-medium focus:bg-background outline-none"></textarea>
                                        <InputError :message="form.errors.teacher_activities" />
                                    </div>
                                    <div v-else class="p-6 bg-slate-50 border border-border/40 rounded-2xl">
                                        <p class="text-sm font-medium leading-relaxed">{{ entry.teacher_activities || '--' }}</p>
                                    </div>
                                </div>

                                <!-- Resources -->
                                <div class="space-y-2">
                                    <Label class="text-[10px] font-black text-muted-foreground uppercase tracking-[0.15em] ml-1">Teaching Aids & Resources</Label>
                                    <div v-if="isEditing">
                                        <textarea v-model="form.resources" class="w-full min-h-[80px] rounded-xl border-border bg-muted/30 p-4 text-xs font-bold outline-none"></textarea>
                                        <InputError :message="form.errors.resources" />
                                    </div>
                                    <p v-else class="text-xs font-bold text-foreground bg-muted/20 p-4 rounded-xl border border-border/40 italic">{{ entry.resources || 'None listed.' }}</p>
                                </div>

                                <!-- References -->
                                <div class="space-y-2">
                                    <Label class="text-[10px] font-black text-muted-foreground uppercase tracking-[0.15em] ml-1">Textbook References</Label>
                                    <div v-if="isEditing">
                                        <textarea v-model="form.references" class="w-full min-h-[80px] rounded-xl border-border bg-muted/30 p-4 text-xs font-bold outline-none"></textarea>
                                        <InputError :message="form.errors.references" />
                                    </div>
                                    <p v-else class="text-xs font-bold text-blue-600 bg-blue-50/30 p-4 rounded-xl border border-blue-100/30 uppercase tracking-tighter italic">{{ entry.references || 'None listed.' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Outcomes Tab -->
                    <div v-else-if="activeTab === 'outcomes'" class="space-y-6">
                        <div class="bg-card rounded-2xl border border-border p-8 shadow-sm">
                            <div class="flex items-center justify-between border-b border-border/50 pb-5 mb-8">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                        <Target class="h-5 w-5" />
                                    </div>
                                    <h3 class="text-[11px] font-black text-foreground uppercase tracking-widest">Assessment & Competencies</h3>
                                </div>
                            </div>

                            <div class="space-y-8">
                                <!-- Competencies Selection -->
                                <div class="space-y-4">
                                     <Label class="text-[10px] font-black text-muted-foreground uppercase tracking-[0.15em] ml-1">CBC Core Competencies</Label>
                                     <div v-if="isEditing" class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                          <div v-for="comp in cbcCompetencies" :key="comp" class="flex items-center gap-3 p-3 rounded-xl border border-border/60 hover:bg-muted/30 transition-all">
                                              <input type="checkbox" :value="comp" v-model="form.core_competencies" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                              <span class="text-[10px] font-bold uppercase tracking-tight">{{ comp }}</span>
                                          </div>
                                     </div>
                                     <div v-else class="flex flex-wrap gap-2">
                                          <Badge v-for="comp in entry.core_competencies" :key="comp" variant="secondary" class="rounded-lg px-3 py-1 text-[9px] font-bold uppercase tracking-widest bg-blue-50 text-blue-700 border-blue-100">
                                              {{ comp }}
                                          </Badge>
                                          <p v-if="!entry.core_competencies?.length" class="text-xs font-bold text-muted-foreground italic">No competencies specifically assigned.</p>
                                     </div>
                                </div>

                                <div class="grid gap-8 md:grid-cols-2">
                                     <!-- Assessment -->
                                     <div class="space-y-2">
                                         <Label class="text-[10px] font-black text-muted-foreground uppercase tracking-[0.15em] ml-1">Assessment Strategy</Label>
                                         <div v-if="isEditing">
                                             <textarea v-model="form.assessment" class="w-full min-h-[100px] rounded-xl border-border bg-muted/30 p-4 text-sm font-medium outline-none"></textarea>
                                             <InputError :message="form.errors.assessment" />
                                         </div>
                                         <p v-else class="text-sm font-bold text-emerald-700 bg-emerald-50/30 p-4 rounded-xl border border-emerald-100/50 italic leading-relaxed">{{ entry.assessment || '--' }}</p>
                                     </div>

                                     <!-- Homework -->
                                     <div class="space-y-2">
                                         <Label class="text-[10px] font-black text-muted-foreground uppercase tracking-[0.15em] ml-1">Further Learning / Homework</Label>
                                         <div v-if="isEditing">
                                             <textarea v-model="form.homework" class="w-full min-h-[100px] rounded-xl border-border bg-muted/30 p-4 text-sm font-medium outline-none"></textarea>
                                             <InputError :message="form.errors.homework" />
                                         </div>
                                         <p v-else class="text-sm font-bold text-indigo-700 bg-indigo-50/30 p-4 rounded-xl border border-indigo-100/50 italic leading-relaxed">{{ entry.homework || '--' }}</p>
                                     </div>
                                </div>

                                <!-- Remarks -->
                                <div class="space-y-4">
                                     <Label class="text-[10px] font-black text-muted-foreground uppercase tracking-[0.15em] ml-1">Instructional Remarks & Reflection</Label>
                                     <div v-if="isEditing" class="space-y-6">
                                         <div class="space-y-2">
                                             <Label class="text-[11px] font-bold text-muted-foreground uppercase tracking-wider">Teacher Remarks</Label>
                                             <textarea v-model="form.remarks" placeholder="Observations after lesson delivery..." class="w-full min-h-[100px] rounded-2xl border-border bg-muted/30 p-6 text-sm font-medium focus:bg-background outline-none"></textarea>
                                             <InputError :message="form.errors.remarks" />
                                         </div>
                                         <div class="space-y-2">
                                             <Label class="text-[11px] font-bold text-muted-foreground uppercase tracking-wider">Self Reflection</Label>
                                             <textarea v-model="form.reflection" placeholder="What went well? What could be improved?" class="w-full min-h-[100px] rounded-2xl border-border bg-muted/30 p-6 text-sm font-medium focus:bg-background outline-none"></textarea>
                                             <InputError :message="form.errors.reflection" />
                                         </div>
                                     </div>
                                     <div v-else class="space-y-6">
                                          <div class="p-6 bg-slate-100/50 border border-border/40 rounded-2xl border-l-4 border-l-slate-400">
                                               <p class="text-[12px] font-bold text-muted-foreground uppercase tracking-widest mb-2">Remarks</p>
                                               <p class="text-sm font-bold text-foreground leading-relaxed italic">"{{ entry.remarks || 'No post-instructional remarks recorded yet.' }}"</p>
                                          </div>
                                          <div class="p-6 bg-blue-50/50 border border-blue-100/50 rounded-2xl border-l-4 border-l-blue-400">
                                               <p class="text-[12px] font-bold text-blue-600 uppercase tracking-widest mb-2">Teacher Reflection</p>
                                               <p class="text-sm font-bold text-foreground leading-relaxed italic">"{{ entry.reflection || 'No teacher reflection recorded yet.' }}"</p>
                                          </div>
                                     </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
