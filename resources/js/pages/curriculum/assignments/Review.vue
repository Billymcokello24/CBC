<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { 
    Target, ArrowLeft, User, Calendar, 
    Download, CheckCircle2, ChevronRight,
    MessageSquare, Eye, FileText, ChevronLeft
} from 'lucide-vue-next';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import MarkingTool from '@/components/curriculum/assignments/MarkingTool.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    assignment: any;
    submission: any;
    prevId?: number;
    nextId?: number;
}>();

// Defensive breadcrumbs
const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Assignments', href: '/curriculum/assignments' },
    { title: 'Submissions', href: props.assignment ? `/curriculum/assignments/${props.assignment.id}/submissions` : '#' },
    { title: props.submission?.student?.first_name ? `${props.submission.student.first_name}'s Profile` : 'Assessment', href: '#' },
]);

const gradingForm = useForm({
    marks_obtained: props.submission?.marks_obtained || 0,
    feedback: props.submission?.feedback || '',
    annotations: props.submission?.private_notes ? JSON.parse(props.submission.private_notes) : [],
    marked_images: [] as string[], // Flattened snapshots
});

const markingToolRef = ref<any>(null);

const onAnnotationsSaved = async (annotations: any[]) => {
    gradingForm.annotations = annotations;
    
    // Capture high-fidelity snapshots from the client-side canvas
    if (markingToolRef.value) {
        gradingForm.marked_images = await markingToolRef.value.getFlattenedSnapshots();
    }
    
    // Auto-sync with server immediately to prevent data loss
    gradingForm.post(route('curriculum.assignments.submissions.grade', props.submission.id), {
        preserveScroll: true,
        onSuccess: () => {
            // Optional: Show a fleeting success state in the tool if we had a ref to it
        }
    });
};

const goToSubmission = (id: number) => {
    router.get(route('curriculum.assignments.submissions.review', { 
        assignment: props.assignment.id, 
        submission: id 
    }));
};

const getStatusBadge = (status: string) => {
    switch (status) {
        case 'graded': return 'bg-emerald-50 text-emerald-600 border-emerald-100';
        case 'submitted': return 'bg-blue-50 text-blue-600 border-blue-100';
        default: return 'bg-slate-50 text-slate-600 border-slate-100';
    }
};

</script>

<template>
    <Head :title="submission?.student?.first_name ? `${submission.student.first_name} - Assessment Studio` : 'Assessment Studio'" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Robust v-if guard to prevent white page crashes -->
        <div v-if="submission && assignment" class="flex h-screen flex-col bg-slate-50 overflow-hidden animate-in fade-in duration-500">
            
            <!-- Context Header: Fixed at top -->
            <div class="bg-white border-b border-slate-100 p-4 shadow-sm z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <Link :href="route('curriculum.assignments.submissions', assignment.id)">
                        <Button variant="ghost" size="icon" class="h-10 w-10 rounded-xl hover:bg-slate-100 transition-all">
                            <ArrowLeft class="h-5 w-5 text-slate-500" />
                        </Button>
                    </Link>
                    <div class="h-8 w-px bg-slate-100"></div>
                    
                    <!-- Rapid Navigator Controls -->
                    <div class="flex items-center gap-2 bg-slate-50 p-1 rounded-xl border border-slate-100 mr-2">
                        <Button 
                            variant="ghost" 
                            size="icon" 
                            class="h-8 w-8 rounded-lg" 
                            :disabled="!prevId"
                            @click="goToSubmission(prevId as number)"
                        >
                            <ChevronLeft class="h-4 w-4" />
                        </Button>
                        <div class="h-4 w-[1px] bg-slate-200"></div>
                        <Button 
                            variant="ghost" 
                            size="icon" 
                            class="h-8 w-8 rounded-lg" 
                            :disabled="!nextId"
                            @click="goToSubmission(nextId as number)"
                        >
                            <ChevronRight class="h-4 w-4" />
                        </Button>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-slate-900 flex items-center justify-center text-white font-black text-sm shadow-lg">
                            {{ submission.student?.first_name?.charAt(0) }}
                        </div>
                        <div>
                            <h1 class="text-base font-black text-slate-900 leading-none mb-1">
                                {{ submission.student?.first_name }} {{ submission.student?.last_name }} 
                                <span class="text-slate-300 font-bold ml-1">#{{ submission.student?.admission_number || '001' }}</span>
                            </h1>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                {{ assignment.title }} <ChevronRight class="h-2 w-2" /> <span class="text-blue-600">Reviewing Studio</span>
                                <template v-if="submission.marked_file_path">
                                    <ChevronRight class="h-2 w-2" />
                                    <span class="flex items-center gap-1.5 text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100/50">
                                        <CheckCircle2 class="h-2.5 w-2.5" /> Vault Sync Active
                                    </span>
                                </template>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Grading Controls Integrated in Header -->
                <div class="flex items-center gap-4 bg-slate-50 p-2 rounded-2xl border border-slate-100">
                    <div class="flex flex-col px-3">
                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Score (Max {{ assignment.total_marks }})</span>
                        <input type="number" v-model="gradingForm.marks_obtained" class="w-20 bg-transparent text-xl font-black text-blue-600 outline-none border-0 p-0" />
                    </div>
                    <div class="h-10 w-[1px] bg-slate-200"></div>
                    <div class="flex items-center gap-3 pr-2">
                        <div class="relative group">
                            <MessageSquare class="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-slate-300 group-focus-within:text-blue-500 transition-colors" />
                            <Input v-model="gradingForm.feedback" placeholder="Summary feedback..." class="h-10 w-64 border-0 bg-transparent text-xs font-semibold placeholder:text-slate-300 focus-visible:ring-0 pl-10" />
                        </div>
                        <a :href="route('curriculum.assignments.submissions.download-compiled', submission.id)" target="_blank">
                            <Button variant="outline" class="h-10 px-4 rounded-xl border-slate-200 bg-white font-black text-[10px] uppercase tracking-widest text-slate-600 shadow-sm hover:bg-slate-50 hover:-translate-y-0.5 active:translate-y-0 transition-all">
                                <Download class="h-4 w-4 mr-2" /> PDF
                            </Button>
                        </a>
                        <Button @click="gradingForm.post(route('curriculum.assignments.submissions.grade', props.submission.id), { preserveScroll: true })" :disabled="gradingForm.processing" class="h-10 px-8 rounded-xl bg-blue-600 hover:bg-blue-700 font-black text-[10px] uppercase tracking-widest text-white shadow-xl shadow-blue-500/20 hover:-translate-y-0.5 active:translate-y-0 transition-all">
                            {{ gradingForm.processing ? 'Saving...' : 'Finalize Profile' }}
                        </Button>
                        <div class="h-8 w-px bg-slate-200"></div>
                        <Button variant="ghost" size="icon" @click="router.reload({ only: ['submission'] })" class="h-10 w-10 border border-slate-200 rounded-xl hover:bg-slate-100">
                            <RotateCcw class="h-4 w-4 text-slate-400" />
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Main Workspace: Split View -->
            <div class="flex-1 flex overflow-hidden">
                <!-- Left Column: Marking Tool (Primary) -->
                <div class="flex-1 bg-slate-900 relative">
                     <MarkingTool 
                        v-if="submission.attachments?.length"
                        ref="markingToolRef"
                        :images="submission.attachments" 
                        :initial-annotations="gradingForm.annotations"
                        :saving="gradingForm.processing"
                        @save="onAnnotationsSaved"
                    />
                    <div v-else class="h-full flex flex-col items-center justify-center text-white gap-6">
                        <div class="h-24 w-24 rounded-full bg-white/5 flex items-center justify-center border border-white/10">
                            <FileText class="h-10 w-10 opacity-20" />
                        </div>
                        <div class="text-center space-y-2">
                            <p class="text-sm font-black uppercase tracking-[0.3em] text-slate-400">Inventory Empty</p>
                            <p class="text-xs text-slate-500 italic">This learner has not uploaded any photographic exhibits.</p>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar: Submission Metadata (Profile) -->
                <div class="w-[380px] bg-white border-l border-slate-100 flex flex-col shadow-[-4px_0_12px_rgba(0,0,0,0.02)]">
                    <div class="p-8 space-y-8 overflow-y-auto">
                        <!-- Submission Metadata -->
                        <div class="space-y-6">
                            <h3 class="text-xs font-black text-slate-900 uppercase tracking-widest border-b border-slate-50 pb-4">Diagnostic Context</h3>
                            
                            <div class="space-y-5">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Status Index</span>
                                    <Badge variant="outline" :class="['text-[8px] font-black uppercase tracking-widest border-0 px-3 py-1 rounded-lg', getStatusBadge(submission.status)]">
                                        {{ submission.status }}
                                    </Badge>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Ingestion Date</span>
                                    <span class="text-xs font-bold text-slate-700">{{ submission.submitted_at ? new Date(submission.submitted_at).toLocaleDateString(undefined, { dateStyle: 'long' }) : 'Pending' }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Exhibit Count</span>
                                    <span class="text-xs font-black text-blue-600">{{ submission.attachments?.length || 0 }} Digital Assets</span>
                                </div>
                                <div v-if="submission.marked_file_path" class="pt-2">
                                    <a :href="`/storage/${submission.marked_file_path}`" target="_blank" class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100 group hover:border-blue-200 transition-all">
                                        <div class="flex items-center gap-2">
                                            <FileText class="h-4 w-4 text-emerald-500" />
                                            <span class="text-[10px] font-bold text-slate-700 uppercase tracking-tight">Finalized Vault Record</span>
                                        </div>
                                        <ExternalLink class="h-3 w-3 text-slate-300 group-hover:text-blue-500 transition-colors" />
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Learner Note -->
                        <div class="space-y-4">
                            <h3 class="text-xs font-black text-slate-900 uppercase tracking-widest border-b border-slate-50 pb-4">Learner Commentary</h3>
                            <div class="bg-blue-50/50 p-5 rounded-2xl border border-blue-100/50 italic text-slate-600 text-xs leading-relaxed">
                                "{{ submission.content || 'No commentary provided by student.' }}"
                            </div>
                        </div>

                        <!-- Exhibit Previews -->
                        <div class="space-y-4">
                            <h3 class="text-xs font-black text-slate-900 uppercase tracking-widest border-b border-slate-50 pb-4">Raw Exhibits ({{ submission.attachments?.length || 0 }})</h3>
                            <div class="grid grid-cols-2 gap-3">
                                <div v-for="attachment in submission.attachments" :key="attachment.id" class="aspect-square rounded-2xl border border-slate-100 bg-slate-50 overflow-hidden group hover:border-blue-300 transition-all cursor-pointer">
                                    <img v-if="attachment.file_type.includes('image')" :src="`/storage/${attachment.file_path}`" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" />
                                    <div v-else class="w-full h-full flex items-center justify-center text-xs font-bold text-slate-300 uppercase">
                                        {{ attachment.file_type.split('/')[1] }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Failure Placeholder -->
        <div v-else class="h-screen w-full flex items-center justify-center bg-white">
            <div class="text-center space-y-4">
                <div class="h-16 w-16 rounded-full bg-slate-50 flex items-center justify-center mx-auto mb-4">
                    <Target class="h-8 w-8 text-slate-200 animate-pulse" />
                </div>
                <h2 class="text-sm font-black text-slate-900 uppercase tracking-widest">Loading Academic Profile...</h2>
                <p class="text-xs text-slate-400 italic">Connecting to the assessment intake service.</p>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Ensure the layout takes full height and hides body overflow */
:deep(body) {
    overflow: hidden;
}

input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
  -webkit-appearance: none; 
  margin: 0; 
}
</style>
