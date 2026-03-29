<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { ref } from 'vue';
import {
    ArrowLeft, Paperclip, Download, Upload, Send, Clock,
    User, BookOpen, FileText, Loader2, X, CheckCircle2, AlertCircle
} from 'lucide-vue-next';

const props = defineProps<{
    assignment: {
        id: number;
        title: string;
        description: string | null;
        instructions: string | null;
        assignment_type: string;
        subject: string | null;
        teacher: { name: string; phone: string | null; email: string | null } | null;
        strand: string | null;
        sub_strand: string | null;
        assigned_date: string | null;
        due_date: string | null;
        total_marks: number | null;
        allow_late_submission: boolean;
        max_attempts: number;
        is_overdue: boolean;
        attachments: Array<{
            id: number;
            file_name: string;
            file_type: string;
            file_size: number;
            download_url: string;
        }>;
    };
    student: {
        id: number;
        name: string;
    };
    submission: {
        id: number;
        content: string | null;
        status: string;
        submitted_at: string | null;
        marks_obtained: number | null;
        final_marks: number | null;
        feedback: string | null;
        grade: string | null;
        attachments: Array<{
            id: number;
            file_name: string;
            file_type: string;
            file_size: number;
        }>;
    } | null;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Parent Portal', href: '/guardian/portal' },
    { title: 'Assignments', href: '/guardian/assignments' },
    { title: props.assignment.title, href: '#' },
];

const form = useForm({
    student_id: props.student.id,
    content: props.submission?.content || '',
    files: [] as File[],
});

const fileInput = ref<HTMLInputElement | null>(null);
const selectedFiles = ref<File[]>([]);

const triggerFileInput = () => fileInput.value?.click();

const handleFiles = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (input.files) {
        for (const file of Array.from(input.files)) {
            selectedFiles.value.push(file);
        }
    }
};

const removeFile = (index: number) => {
    selectedFiles.value.splice(index, 1);
};

const formatFileSize = (bytes: number) => {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
};

const submit = () => {
    const formData = useForm({
        student_id: props.student.id,
        content: form.content,
        files: selectedFiles.value,
    });

    formData.post(`/guardian/assignments/${props.assignment.id}/submit`, {
        preserveScroll: true,
        forceFormData: true,
    });
};

const canSubmit = () => {
    if (props.submission && props.submission.status === 'graded') return false;
    return true;
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'graded': return 'bg-emerald-500 text-white';
        case 'submitted':
        case 'resubmitted': return 'bg-blue-500 text-white';
        case 'returned': return 'bg-amber-500 text-white';
        default: return 'bg-slate-400 text-white';
    }
};
</script>

<template>
    <Head :title="`Assignment: ${assignment.title}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-[1200px] mx-auto p-6 md:p-8 space-y-8 animate-in fade-in duration-500 pb-20">
            <!-- Header -->
            <div class="flex items-start gap-4">
                <Button variant="ghost" size="icon" as-child class="h-10 w-10 rounded-xl hover:bg-blue-50 mt-1">
                    <Link :href="`/guardian/children/${student.id}`"><ArrowLeft class="h-5 w-5" /></Link>
                </Button>
                <div class="flex-1">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-[10px] font-bold text-blue-600 uppercase tracking-widest mb-1">{{ assignment.subject }}</p>
                            <h1 class="text-2xl font-bold tracking-tight text-foreground">{{ assignment.title }}</h1>
                            <div class="flex items-center gap-3 mt-2 text-sm text-muted-foreground">
                                <Badge variant="outline" class="rounded-lg text-[9px] font-bold uppercase px-2 py-0.5">{{ assignment.assignment_type }}</Badge>
                                <span v-if="assignment.strand">{{ assignment.strand }}</span>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <Badge v-if="submission" :class="getStatusColor(submission.status)" class="rounded-lg px-3 py-1 text-[10px] font-bold uppercase border-0">
                                {{ submission.status }}
                            </Badge>
                            <Badge v-else-if="assignment.is_overdue" class="bg-rose-500 text-white rounded-lg px-3 py-1 text-[10px] font-bold uppercase border-0">
                                Overdue
                            </Badge>
                            <Badge v-else class="bg-amber-500 text-white rounded-lg px-3 py-1 text-[10px] font-bold uppercase border-0">
                                Pending
                            </Badge>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content Area -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Assignment Details -->
                    <div class="rounded-2xl border bg-card p-7 shadow-sm">
                        <h3 class="text-sm font-bold text-foreground mb-4 uppercase tracking-wider">Description</h3>
                        <div v-if="assignment.description" class="prose prose-sm max-w-none text-muted-foreground leading-relaxed">
                            {{ assignment.description }}
                        </div>
                        <p v-else class="text-sm text-muted-foreground italic">No description provided.</p>

                        <div v-if="assignment.instructions" class="mt-6 bg-blue-50 border border-blue-100 rounded-xl p-5">
                            <h4 class="text-xs font-bold text-blue-800 uppercase tracking-widest mb-2">Instructions</h4>
                            <p class="text-sm text-blue-700 leading-relaxed">{{ assignment.instructions }}</p>
                        </div>
                    </div>

                    <!-- Attachments -->
                    <div v-if="assignment.attachments.length > 0" class="rounded-2xl border bg-card p-7 shadow-sm">
                        <h3 class="text-sm font-bold text-foreground mb-4 uppercase tracking-wider flex items-center gap-2">
                            <Paperclip class="h-4 w-4 text-muted-foreground" />
                            Attachments ({{ assignment.attachments.length }})
                        </h3>
                        <div class="space-y-2">
                            <a v-for="att in assignment.attachments" :key="att.id" :href="att.download_url" target="_blank"
                                class="flex items-center gap-3 rounded-xl border p-3.5 hover:bg-muted/30 transition-colors group">
                                <div class="h-10 w-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                                    <FileText class="h-5 w-5" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-foreground truncate">{{ att.file_name }}</p>
                                    <p class="text-xs text-muted-foreground">{{ formatFileSize(att.file_size) }}</p>
                                </div>
                                <Download class="h-4 w-4 text-muted-foreground group-hover:text-blue-600 transition-colors" />
                            </a>
                        </div>
                    </div>

                    <!-- Submission / Feedback -->
                    <div v-if="submission" class="rounded-2xl border bg-card p-7 shadow-sm">
                        <div class="flex items-center gap-3 mb-5">
                            <CheckCircle2 class="h-5 w-5 text-emerald-500" />
                            <h3 class="text-sm font-bold text-foreground uppercase tracking-wider">Your Submission</h3>
                        </div>

                        <div v-if="submission.content" class="rounded-xl bg-muted/30 border p-5 mb-4">
                            <p class="text-sm text-foreground whitespace-pre-line">{{ submission.content }}</p>
                        </div>

                        <div v-if="submission.attachments?.length" class="space-y-2 mb-4">
                            <p class="text-xs font-bold text-muted-foreground uppercase tracking-widest mb-2">Uploaded Files</p>
                            <div v-for="att in submission.attachments" :key="att.id" class="flex items-center gap-3 rounded-xl border p-3 bg-muted/20">
                                <FileText class="h-4 w-4 text-muted-foreground" />
                                <span class="text-sm font-medium text-foreground truncate">{{ att.file_name }}</span>
                                <span class="text-xs text-muted-foreground ml-auto">{{ formatFileSize(att.file_size) }}</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mb-0.5">Submitted At</p>
                                <p class="font-bold text-foreground">{{ submission.submitted_at || '—' }}</p>
                            </div>
                            <div v-if="submission.grade">
                                <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mb-0.5">Grade</p>
                                <p class="font-bold text-emerald-600 text-lg">{{ submission.grade }}</p>
                            </div>
                            <div v-if="submission.final_marks != null">
                                <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mb-0.5">Marks</p>
                                <p class="font-bold text-foreground">{{ submission.final_marks }} / {{ assignment.total_marks }}</p>
                            </div>
                        </div>

                        <div v-if="submission.feedback" class="mt-5 bg-blue-50 border border-blue-100 rounded-xl p-5">
                            <h4 class="text-xs font-bold text-blue-800 uppercase tracking-widest mb-2">Teacher Feedback</h4>
                            <p class="text-sm text-blue-700 leading-relaxed">{{ submission.feedback }}</p>
                        </div>
                    </div>

                    <!-- Submission Form -->
                    <div v-if="canSubmit()" class="rounded-2xl border-2 border-blue-200 bg-card p-7 shadow-sm">
                        <div class="flex items-center gap-3 mb-6">
                            <Upload class="h-5 w-5 text-blue-600" />
                            <h3 class="text-sm font-bold text-foreground uppercase tracking-wider">
                                {{ submission ? 'Resubmit Assignment' : 'Submit Assignment' }}
                            </h3>
                        </div>

                        <form @submit.prevent="submit" class="space-y-5">
                            <!-- Text Content -->
                            <div>
                                <label class="text-xs font-bold text-muted-foreground uppercase tracking-widest block mb-2">Written Response</label>
                                <textarea v-model="form.content" rows="6" placeholder="Type your answer here..."
                                    class="w-full rounded-xl border border-border bg-muted/20 p-4 text-sm focus:ring-2 focus:ring-blue-200 focus:border-blue-400 outline-none transition-all resize-y"></textarea>
                            </div>

                            <!-- File Upload -->
                            <div>
                                <label class="text-xs font-bold text-muted-foreground uppercase tracking-widest block mb-2">File Attachments</label>
                                <p class="text-xs text-muted-foreground mb-3">Supported formats: PDF, DOCX, Images (JPG, PNG), Audio (MP3, WAV), Video (MP4, WebM). Max 20MB each.</p>

                                <input ref="fileInput" type="file" multiple @change="handleFiles" class="hidden"
                                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.mp3,.wav,.ogg,.mp4,.webm,.mov" />

                                <button type="button" @click="triggerFileInput"
                                    class="w-full rounded-xl border-2 border-dashed border-blue-300 bg-blue-50/50 p-8 text-center hover:bg-blue-100/50 hover:border-blue-400 transition-all cursor-pointer group">
                                    <Upload class="h-8 w-8 mx-auto text-blue-400 group-hover:text-blue-500 mb-2" />
                                    <p class="text-sm font-bold text-blue-600">Click to upload files</p>
                                    <p class="text-xs text-blue-400 mt-1">or drag and drop</p>
                                </button>

                                <!-- Selected Files Preview -->
                                <div v-if="selectedFiles.length > 0" class="mt-4 space-y-2">
                                    <div v-for="(file, index) in selectedFiles" :key="index"
                                        class="flex items-center gap-3 rounded-xl border p-3 bg-card">
                                        <FileText class="h-4 w-4 text-blue-500 flex-shrink-0" />
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-foreground truncate">{{ file.name }}</p>
                                            <p class="text-[10px] text-muted-foreground">{{ formatFileSize(file.size) }}</p>
                                        </div>
                                        <button type="button" @click="removeFile(index)" class="h-7 w-7 rounded-lg text-muted-foreground hover:bg-rose-50 hover:text-rose-500 flex items-center justify-center transition-colors">
                                            <X class="h-4 w-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <Button type="submit" :disabled="form.processing" class="w-full h-12 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-lg">
                                <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                                <Send v-else class="mr-2 h-4 w-4" />
                                {{ submission ? 'Resubmit Assignment' : 'Submit Assignment' }}
                            </Button>
                        </form>
                    </div>
                </div>

                <!-- Sidebar Info -->
                <div class="space-y-5">
                    <!-- Student Info -->
                    <div class="rounded-2xl border bg-card p-5 shadow-sm">
                        <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mb-3">Submitting For</p>
                        <p class="text-sm font-bold text-foreground">{{ student.name }}</p>
                    </div>

                    <!-- Assignment Details -->
                    <div class="rounded-2xl border bg-card p-5 shadow-sm space-y-4">
                        <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Details</p>

                        <div>
                            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mb-0.5">Subject</p>
                            <p class="text-sm font-bold text-blue-600">{{ assignment.subject || '—' }}</p>
                        </div>
                        <div v-if="assignment.teacher">
                            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mb-0.5">Teacher</p>
                            <div class="flex items-center gap-2">
                                <div class="h-7 w-7 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center"><User class="h-3.5 w-3.5" /></div>
                                <div>
                                    <p class="text-sm font-bold text-foreground">{{ assignment.teacher.name }}</p>
                                    <p v-if="assignment.teacher.phone" class="text-[10px] text-muted-foreground">{{ assignment.teacher.phone }}</p>
                                </div>
                            </div>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mb-0.5">Assigned</p>
                            <p class="text-sm font-bold text-foreground">{{ assignment.assigned_date || '—' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mb-0.5">Due Date</p>
                            <p class="text-sm font-bold" :class="assignment.is_overdue ? 'text-rose-600' : 'text-foreground'">
                                {{ assignment.due_date || '—' }}
                            </p>
                        </div>
                        <div v-if="assignment.total_marks">
                            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mb-0.5">Total Marks</p>
                            <p class="text-sm font-bold text-foreground">{{ assignment.total_marks }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mb-0.5">Late Submission</p>
                            <p class="text-sm font-bold" :class="assignment.allow_late_submission ? 'text-emerald-600' : 'text-rose-600'">
                                {{ assignment.allow_late_submission ? 'Allowed' : 'Not Allowed' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mb-0.5">Max Attempts</p>
                            <p class="text-sm font-bold text-foreground">{{ assignment.max_attempts }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
