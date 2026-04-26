<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { ref } from 'vue';
import {
    ArrowLeft,
    Paperclip,
    Download,
    Upload,
    Send,
    Clock,
    User,
    BookOpen,
    FileText,
    Loader2,
    X,
    CheckCircle2,
    AlertCircle,
    Eye,
} from 'lucide-vue-next';
import MarkingTool from '@/components/curriculum/assignments/MarkingTool.vue';
import { route } from 'ziggy-js';

const props = defineProps<{
    assignment: {
        id: number;
        title: string;
        description: string | null;
        instructions: string | null;
        assignment_type: string;
        subject: string | null;
        teacher: {
            name: string;
            phone: string | null;
            email: string | null;
        } | null;
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
        private_notes: any | null;
        attachments: Array<{
            id: number;
            file_name: string;
            file_type: string;
            file_size: number;
            file_path: string;
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
        case 'graded':
            return 'bg-emerald-500 text-white';
        case 'submitted':
        case 'resubmitted':
            return 'bg-blue-500 text-white';
        case 'returned':
            return 'bg-amber-500 text-white';
        default:
            return 'bg-slate-400 text-white';
    }
};
</script>

<template>
    <Head :title="`Assignment: ${assignment.title}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto max-w-[1200px] animate-in space-y-8 p-6 pb-20 duration-500 fade-in md:p-8"
        >
            <!-- Header -->
            <div class="flex items-start gap-4">
                <Button
                    variant="ghost"
                    size="icon"
                    as-child
                    class="mt-1 h-10 w-10 rounded-xl hover:bg-blue-50"
                >
                    <Link :href="`/guardian/children/${student.id}`"
                        ><ArrowLeft class="h-5 w-5"
                    /></Link>
                </Button>
                <div class="flex-1">
                    <div class="flex items-start justify-between">
                        <div>
                            <p
                                class="mb-1 text-xs font-bold tracking-tight text-blue-600 uppercase"
                            >
                                {{ assignment.subject }}
                            </p>
                            <h1
                                class="text-2xl font-bold tracking-tight text-foreground"
                            >
                                {{ assignment.title }}
                            </h1>
                            <div
                                class="mt-2 flex items-center gap-3 text-sm text-muted-foreground"
                            >
                                <Badge
                                    variant="outline"
                                    class="rounded-lg px-2 py-0.5 text-xs font-bold uppercase"
                                    >{{ assignment.assignment_type }}</Badge
                                >
                                <span v-if="assignment.strand">{{
                                    assignment.strand
                                }}</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 text-right">
                            <Badge
                                v-if="submission"
                                :class="getStatusColor(submission.status)"
                                class="rounded-lg border-0 px-3 py-1 text-xs font-bold uppercase"
                            >
                                {{ submission.status }}
                            </Badge>
                            <Badge
                                v-else-if="assignment.is_overdue"
                                class="rounded-lg border-0 bg-rose-500 px-3 py-1 text-xs font-bold text-white uppercase"
                            >
                                Overdue
                            </Badge>
                            <Badge
                                v-else
                                class="rounded-lg border-0 bg-amber-500 px-3 py-1 text-xs font-bold text-white uppercase"
                            >
                                Pending
                            </Badge>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                <!-- Main Content Area -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Assignment Details -->
                    <div class="rounded-2xl border bg-card p-7 shadow-sm">
                        <h3
                            class="mb-4 text-sm font-bold tracking-wider text-foreground uppercase"
                        >
                            Description
                        </h3>
                        <div
                            v-if="assignment.description"
                            class="prose prose-sm max-w-none leading-relaxed text-muted-foreground"
                        >
                            {{ assignment.description }}
                        </div>
                        <p v-else class="text-sm text-muted-foreground">
                            No description provided.
                        </p>

                        <div
                            v-if="assignment.instructions"
                            class="mt-6 rounded-xl border border-blue-100 bg-blue-50 p-5"
                        >
                            <h4
                                class="mb-2 text-xs font-bold tracking-tight text-blue-800 uppercase"
                            >
                                Instructions
                            </h4>
                            <p class="text-sm leading-relaxed text-blue-700">
                                {{ assignment.instructions }}
                            </p>
                        </div>
                    </div>

                    <!-- Attachments -->
                    <div
                        v-if="assignment.attachments.length > 0"
                        class="rounded-2xl border bg-card p-7 shadow-sm"
                    >
                        <h3
                            class="mb-4 flex items-center gap-2 text-sm font-bold tracking-wider text-foreground uppercase"
                        >
                            <Paperclip class="h-4 w-4 text-muted-foreground" />
                            Attachments ({{ assignment.attachments.length }})
                        </h3>
                        <div class="space-y-2">
                            <a
                                v-for="att in assignment.attachments"
                                :key="att.id"
                                :href="att.download_url"
                                target="_blank"
                                class="group flex items-center gap-3 rounded-xl border p-3.5 transition-colors hover:bg-muted/30"
                            >
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-blue-600"
                                >
                                    <FileText class="h-5 w-5" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p
                                        class="truncate text-sm font-bold text-foreground"
                                    >
                                        {{ att.file_name }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ formatFileSize(att.file_size) }}
                                    </p>
                                </div>
                                <Download
                                    class="h-4 w-4 text-muted-foreground transition-colors group-hover:text-blue-600"
                                />
                            </a>
                        </div>
                    </div>

                    <!-- Submission / Feedback -->
                    <div
                        v-if="submission"
                        class="rounded-2xl border bg-card p-7 shadow-sm"
                    >
                        <div class="mb-5 flex items-center gap-3">
                            <CheckCircle2 class="h-5 w-5 text-emerald-500" />
                            <h3
                                class="text-sm font-bold tracking-wider text-foreground uppercase"
                            >
                                Your Submission
                            </h3>
                        </div>

                        <div
                            v-if="submission.content"
                            class="mb-4 rounded-xl border bg-muted/30 p-5"
                        >
                            <p
                                class="text-sm whitespace-pre-line text-foreground"
                            >
                                {{ submission.content }}
                            </p>
                        </div>

                        <div
                            v-if="submission.attachments?.length"
                            class="mb-4 space-y-2"
                        >
                            <p
                                class="mb-2 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                            >
                                Uploaded Files
                            </p>
                            <div
                                v-for="att in submission.attachments"
                                :key="att.id"
                                class="flex items-center gap-3 rounded-xl border bg-muted/20 p-3"
                            >
                                <FileText
                                    class="h-4 w-4 text-muted-foreground"
                                />
                                <span
                                    class="truncate text-sm font-medium text-foreground"
                                    >{{ att.file_name }}</span
                                >
                                <span
                                    class="ml-auto text-xs text-muted-foreground"
                                    >{{ formatFileSize(att.file_size) }}</span
                                >
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p
                                    class="mb-0.5 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                >
                                    Submitted At
                                </p>
                                <p class="font-bold text-foreground">
                                    {{ submission.submitted_at || '—' }}
                                </p>
                            </div>
                            <div v-if="submission.grade">
                                <p
                                    class="mb-0.5 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                >
                                    Grade
                                </p>
                                <p class="text-lg font-bold text-emerald-600">
                                    {{ submission.grade }}
                                </p>
                            </div>
                            <div v-if="submission.final_marks != null">
                                <p
                                    class="mb-0.5 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                >
                                    Marks
                                </p>
                                <p class="font-bold text-foreground">
                                    {{ submission.final_marks }} /
                                    {{ assignment.total_marks }}
                                </p>
                            </div>
                        </div>

                        <div
                            v-if="submission.feedback"
                            class="mt-5 rounded-xl border border-blue-100 bg-blue-50 p-5"
                        >
                            <h4
                                class="mb-2 text-xs font-bold tracking-tight text-blue-800 uppercase"
                            >
                                Teacher Feedback
                            </h4>
                            <p class="text-sm leading-relaxed text-blue-700">
                                {{ submission.feedback }}
                            </p>
                        </div>

                        <!-- Graded Status & Download for Parents -->
                        <div
                            v-if="submission.status === 'graded'"
                            class="mt-8 border-t pt-8"
                        >
                            <div
                                class="flex flex-col items-center gap-8 rounded-xl border border-slate-200 bg-white p-10 text-center shadow-lg shadow-blue-500/5"
                            >
                                <div
                                    class="flex h-20 w-20 items-center justify-center rounded-[1.5rem] bg-emerald-50 text-emerald-600 shadow-inner"
                                >
                                    <CheckCircle2 class="h-10 w-10" />
                                </div>

                                <div class="space-y-3">
                                    <h4
                                        class="text-2xl font-bold tracking-tight text-slate-900 uppercase"
                                    >
                                        Marked & Graded
                                    </h4>
                                    <p
                                        class="mx-auto max-w-sm text-sm font-medium text-slate-500"
                                    >
                                        Your instructor has completed the
                                        assessment. You can now download the
                                        official marked copy with all
                                        corrections and stamps.
                                    </p>
                                </div>

                                <!-- Primary Download Action -->
                                <a
                                    :href="
                                        route(
                                            'guardian.assignments.submissions.download-marked',
                                            {
                                                submission: String(
                                                    submission.id,
                                                ),
                                            },
                                        )
                                    "
                                    class="group flex h-14 items-center gap-3 rounded-2xl bg-blue-600 px-10 text-xs font-bold tracking-tight text-white uppercase shadow-lg shadow-blue-600/30 transition-all hover:bg-blue-700 active:scale-95"
                                >
                                    <Download
                                        class="h-5 w-5 transition-transform group-hover:translate-y-0.5"
                                    />
                                    Download Marked Assignment
                                </a>

                                <div
                                    class="mt-4 flex items-center gap-2 rounded-xl border border-slate-100 bg-slate-50 px-4 py-2"
                                >
                                    <div
                                        class="h-1.5 w-1.5 animate-pulse rounded-full bg-blue-600"
                                    ></div>
                                    <span
                                        class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                                        >Digital Official Record</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submission Form -->
                    <div
                        v-if="canSubmit()"
                        class="rounded-2xl border-2 border-blue-200 bg-card p-7 shadow-sm"
                    >
                        <div class="mb-6 flex items-center gap-3">
                            <Upload class="h-5 w-5 text-blue-600" />
                            <h3
                                class="text-sm font-bold tracking-wider text-foreground uppercase"
                            >
                                {{
                                    submission
                                        ? 'Resubmit Assignment'
                                        : 'Submit Assignment'
                                }}
                            </h3>
                        </div>

                        <form @submit.prevent="submit" class="space-y-5">
                            <!-- Text Content -->
                            <div>
                                <label
                                    class="mb-2 block text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                    >Written Response</label
                                >
                                <textarea
                                    v-model="form.content"
                                    rows="6"
                                    placeholder="Type your answer here..."
                                    class="w-full resize-y rounded-xl border border-border bg-muted/20 p-4 text-sm transition-all outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-200"
                                ></textarea>
                            </div>

                            <!-- File Upload -->
                            <div>
                                <label
                                    class="mb-2 block text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                    >File Attachments</label
                                >
                                <p class="mb-3 text-xs text-muted-foreground">
                                    Supported formats: PDF, DOCX, Images (JPG,
                                    PNG), Audio (MP3, WAV), Video (MP4, WebM).
                                    Max 20MB each.
                                </p>

                                <input
                                    ref="fileInput"
                                    type="file"
                                    multiple
                                    @change="handleFiles"
                                    class="hidden"
                                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.mp3,.wav,.ogg,.mp4,.webm,.mov"
                                />

                                <button
                                    type="button"
                                    @click="triggerFileInput"
                                    class="group w-full cursor-pointer rounded-xl border-2 border-dashed border-blue-300 bg-blue-50/50 p-8 text-center transition-all hover:border-blue-400 hover:bg-blue-100/50"
                                >
                                    <Upload
                                        class="mx-auto mb-2 h-8 w-8 text-blue-400 group-hover:text-blue-500"
                                    />
                                    <p class="text-sm font-bold text-blue-600">
                                        Click to upload files
                                    </p>
                                    <p class="mt-1 text-xs text-blue-400">
                                        or drag and drop
                                    </p>
                                </button>

                                <!-- Selected Files Preview -->
                                <div
                                    v-if="selectedFiles.length > 0"
                                    class="mt-4 space-y-2"
                                >
                                    <div
                                        v-for="(file, index) in selectedFiles"
                                        :key="index"
                                        class="flex items-center gap-3 rounded-xl border bg-card p-3"
                                    >
                                        <FileText
                                            class="h-4 w-4 flex-shrink-0 text-blue-500"
                                        />
                                        <div class="min-w-0 flex-1">
                                            <p
                                                class="truncate text-sm font-semibold text-foreground"
                                            >
                                                {{ file.name }}
                                            </p>
                                            <p
                                                class="text-xs text-muted-foreground"
                                            >
                                                {{ formatFileSize(file.size) }}
                                            </p>
                                        </div>
                                        <button
                                            type="button"
                                            @click="removeFile(index)"
                                            class="flex h-7 w-7 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-rose-50 hover:text-rose-500"
                                        >
                                            <X class="h-4 w-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <Button
                                type="submit"
                                :disabled="form.processing"
                                class="h-12 w-full rounded-xl bg-blue-600 text-sm font-bold text-white shadow-lg hover:bg-blue-700"
                            >
                                <Loader2
                                    v-if="form.processing"
                                    class="mr-2 h-4 w-4 animate-spin"
                                />
                                <Send v-else class="mr-2 h-4 w-4" />
                                {{
                                    submission
                                        ? 'Resubmit Assignment'
                                        : 'Submit Assignment'
                                }}
                            </Button>
                        </form>
                    </div>
                </div>

                <!-- Sidebar Info -->
                <div class="space-y-5">
                    <!-- Student Info -->
                    <div class="rounded-2xl border bg-card p-5 shadow-sm">
                        <p
                            class="mb-3 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                        >
                            Submitting For
                        </p>
                        <p class="text-sm font-bold text-foreground">
                            {{ student.name }}
                        </p>
                    </div>

                    <!-- Assignment Details -->
                    <div
                        class="space-y-4 rounded-2xl border bg-card p-5 shadow-sm"
                    >
                        <p
                            class="text-xs font-bold tracking-tight text-muted-foreground uppercase"
                        >
                            Details
                        </p>

                        <div>
                            <p
                                class="mb-0.5 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                            >
                                Subject
                            </p>
                            <p class="text-sm font-bold text-blue-600">
                                {{ assignment.subject || '—' }}
                            </p>
                        </div>
                        <div v-if="assignment.teacher">
                            <p
                                class="mb-0.5 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                            >
                                Teacher
                            </p>
                            <div class="flex items-center gap-2">
                                <div
                                    class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-100 text-blue-600"
                                >
                                    <User class="h-3.5 w-3.5" />
                                </div>
                                <div>
                                    <p
                                        class="text-sm font-bold text-foreground"
                                    >
                                        {{ assignment.teacher.name }}
                                    </p>
                                    <p
                                        v-if="assignment.teacher.phone"
                                        class="text-xs text-muted-foreground"
                                    >
                                        {{ assignment.teacher.phone }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div>
                            <p
                                class="mb-0.5 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                            >
                                Assigned
                            </p>
                            <p class="text-sm font-bold text-foreground">
                                {{ assignment.assigned_date || '—' }}
                            </p>
                        </div>
                        <div>
                            <p
                                class="mb-0.5 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                            >
                                Due Date
                            </p>
                            <p
                                class="text-sm font-bold"
                                :class="
                                    assignment.is_overdue
                                        ? 'text-rose-600'
                                        : 'text-foreground'
                                "
                            >
                                {{ assignment.due_date || '—' }}
                            </p>
                        </div>
                        <div v-if="assignment.total_marks">
                            <p
                                class="mb-0.5 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                            >
                                Total Marks
                            </p>
                            <p class="text-sm font-bold text-foreground">
                                {{ assignment.total_marks }}
                            </p>
                        </div>
                        <div>
                            <p
                                class="mb-0.5 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                            >
                                Late Submission
                            </p>
                            <p
                                class="text-sm font-bold"
                                :class="
                                    assignment.allow_late_submission
                                        ? 'text-emerald-600'
                                        : 'text-rose-600'
                                "
                            >
                                {{
                                    assignment.allow_late_submission
                                        ? 'Allowed'
                                        : 'Not Allowed'
                                }}
                            </p>
                        </div>
                        <div>
                            <p
                                class="mb-0.5 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                            >
                                Max Attempts
                            </p>
                            <p class="text-sm font-bold text-foreground">
                                {{ assignment.max_attempts }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
