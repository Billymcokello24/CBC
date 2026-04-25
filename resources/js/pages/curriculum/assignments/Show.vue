<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import {
    ArrowLeft, Calendar, Clock, CheckCircle2,
    Users, BookOpen, Target, Layers, Download,
    Edit2, ChevronRight, Trash2, Paperclip, User
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    assignment: any;
    submissions: any[];
    children: any[];
    userRole: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Assignments', href: '/curriculum/assignments' },
    { title: props.assignment.title, href: '#' },
];

const deleteAssignment = () => {
    if (confirm('Are you sure you want to permanently delete this assignment and all its files?')) {
        router.delete(`/curriculum/assignments/${props.assignment.id}`);
    }
};

const isGuardian = computed(() => props.userRole === 'parent');
const isInstructor = computed(() => !isGuardian.value);

const form = useForm({
    student_id: props.children.length === 1 ? props.children[0].id : '',
    content: '',
    attachments: [] as File[],
});

const handleFileChange = (e: Event) => {
    const files = (e.target as HTMLInputElement).files;
    if (files) {
        form.attachments = Array.from(files);
    }
};

const submitAssignment = () => {
    form.post(route('curriculum.assignments.submit', props.assignment.id), {
        onSuccess: () => {
            form.reset('content', 'attachments');
        },
    });
};

const getStatusBadge = (status: string) => {
    switch (status) {
        case 'published': return 'bg-emerald-50 text-emerald-600';
        case 'draft': return 'bg-slate-50 text-slate-500';
        case 'submitted': return 'bg-blue-50 text-blue-600';
        case 'graded': return 'bg-purple-50 text-purple-600';
        case 'closed': return 'bg-red-50 text-red-600';
        default: return 'bg-slate-50 text-slate-500';
    }
};

const formatFileSize = (bytes: number) => {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1048576).toFixed(1) + ' MB';
};

const getFileIcon = (type: string) => {
    if (type.includes('pdf')) return '📄';
    if (type.includes('image')) return '🖼️';
    if (type.includes('word') || type.includes('document')) return '📝';
    return '📎';
};
</script>

<template>
    <Head :title="assignment.title" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 font-sans max-w-[1200px] mx-auto bg-[#f9fafb]/30">

            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <Link href="/curriculum/assignments" class="group inline-flex items-center gap-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider hover:text-blue-600 transition-all mb-1">
                        <ArrowLeft class="h-3 w-3 group-hover:-translate-x-1 transition-transform" /> Back to List
                    </Link>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ assignment.title }}</h1>
                        <Badge :class="['rounded border-0 text-[10px] font-bold px-2.5 py-0.5 uppercase tracking-wider', getStatusBadge(assignment.status)]">
                            {{ assignment.status }}
                        </Badge>
                    </div>
                </div>

                <div class="flex items-center gap-3" v-if="isInstructor">
                    <Button @click="deleteAssignment" variant="outline" class="h-10 px-4 rounded-xl border-red-100 bg-white font-semibold text-xs text-red-500 shadow-sm hover:bg-red-50 hover:border-red-200">
                        <Trash2 class="mr-2 h-4 w-4" /> Delete
                    </Button>
                    <Link :href="`/curriculum/assignments/${assignment.id}/edit`">
                        <Button variant="outline" class="h-10 px-4 rounded-xl border-slate-200 bg-white font-semibold text-xs text-slate-600 shadow-sm hover:bg-slate-50">
                            <Edit2 class="mr-2 h-4 w-4" /> Edit
                        </Button>
                    </Link>
                    <Link :href="`/curriculum/assignments/${assignment.id}/submissions`">
                        <Button variant="outline" class="h-10 px-4 rounded-xl border-slate-200 bg-white font-semibold text-xs text-slate-600 shadow-sm hover:bg-slate-50">
                            <Users class="mr-2 h-4 w-4" /> Review Submissions ({{ assignment.submissions?.length || 0 }})
                        </Button>
                    </Link>
                </div>
                <div v-else class="flex items-center gap-3">
                    <Badge variant="outline" class="h-10 px-4 rounded-xl border-blue-100 bg-blue-50/30 font-bold text-xs text-blue-600">
                         Guardian Submission Portal
                    </Badge>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Main Content Column -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Assignment Overview Card -->
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-6">
                        <div class="space-y-2">
                            <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Description</h3>
                            <p class="text-slate-600 text-sm leading-relaxed">
                                {{ assignment.description || 'No description provided.' }}
                            </p>
                        </div>

                        <div class="space-y-4 pt-4 border-t border-slate-50">
                            <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Instructions</h3>
                            <!-- Render Tiptap HTML content -->
                            <div class="prose prose-sm max-w-none text-slate-700" v-html="assignment.instructions || '<p class=\'italic text-slate-400\'>No instructions provided.</p>'"></div>
                        </div>
                    </div>

                    <!-- Attachments Card -->
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-50 flex items-center justify-between bg-[#f9fafb]/50">
                            <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <Paperclip class="h-3 w-3" /> Assignment Files ({{ assignment.attachments?.length || 0 }})
                            </h3>
                        </div>

                        <div class="p-6">
                            <div v-if="assignment.attachments?.length" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div v-for="file in assignment.attachments" :key="file.id" class="flex items-center justify-between p-4 rounded-xl border border-slate-100 bg-white hover:border-blue-200 hover:shadow-md hover:shadow-blue-50/50 transition-all group">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="h-10 w-10 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center text-lg group-hover:bg-blue-50 group-hover:border-blue-100 transition-colors">
                                            {{ getFileIcon(file.file_type) }}
                                        </div>
                                        <div class="flex flex-col min-w-0">
                                            <span class="text-[11px] font-bold text-slate-800 line-clamp-1 group-hover:text-blue-700 transition-colors">{{ file.file_name }}</span>
                                            <span class="text-[9px] font-medium text-slate-400 uppercase">{{ formatFileSize(file.file_size) }} • {{ file.file_type.split('/')[1] }}</span>
                                        </div>
                                    </div>
                                    <a :href="`/curriculum/assignments/attachments/${file.id}/download`" class="h-8 w-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-blue-600 hover:text-white transition-all shadow-sm">
                                        <Download class="h-3.5 w-3.5" />
                                    </a>
                                </div>
                            </div>
                            <div v-else class="text-center py-8">
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">No resources attached</p>
                            </div>
                        </div>
                    </div>

                    <!-- Submission Portal (For Guardians) -->
                    <div v-if="isGuardian" class="space-y-6">
                        <div class="bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden">
                            <div class="px-6 py-4 border-b border-blue-50 bg-blue-50/30 flex items-center justify-between">
                                <h3 class="text-[10px] font-black text-blue-600 uppercase tracking-widest flex items-center gap-2">
                                    <Paperclip class="h-3 w-3" /> Digital Submission Studio
                                </h3>
                            </div>
                            
                            <form @submit.prevent="submitAssignment" class="p-8 space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Student Context</label>
                                        <select v-model="form.student_id" class="w-full h-12 rounded-xl border-2 border-slate-100 bg-slate-50/50 px-4 text-xs font-bold text-slate-700 outline-none focus:border-blue-500 focus:bg-white transition-all" required>
                                            <option value="" disabled>Select Student...</option>
                                            <option v-for="child in children" :key="child.id" :value="child.id">
                                                {{ child.first_name }} {{ child.last_name }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Evidence Acquisition (Photos/Scans)</label>
                                        <div class="relative">
                                            <input type="file" multiple @change="handleFileChange" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />
                                            <div class="h-12 w-full rounded-xl border-2 border-dashed border-slate-200 bg-slate-50/50 flex items-center justify-center gap-2 group-hover:border-blue-500 transition-all">
                                                <Paperclip class="h-4 w-4 text-slate-400" />
                                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                                    {{ form.attachments.length ? `${form.attachments.length} files selected` : 'Choose Images' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Brief Commentary (Optional)</label>
                                    <textarea v-model="form.content" placeholder="Any specific notes for the instructor..." class="w-full min-h-[120px] rounded-2xl border-2 border-slate-100 bg-slate-50/50 p-4 text-xs font-medium text-slate-700 outline-none focus:border-blue-500 focus:bg-white transition-all"></textarea>
                                </div>

                                <div class="flex items-center justify-end">
                                    <Button type="submit" :disabled="form.processing" class="h-12 px-10 rounded-xl bg-blue-600 hover:bg-blue-700 font-black text-[10px] uppercase tracking-widest shadow-xl shadow-blue-500/20 text-white border-0">
                                        {{ form.processing ? 'DEPOSITING...' : 'Certify & Submit Work' }}
                                    </Button>
                                </div>
                            </form>
                        </div>

                        <!-- Previous Submissions (For selected student) -->
                        <div v-if="submissions.length" class="space-y-4">
                            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] ml-2">Portfolio History</h3>
                            <div v-for="submission in submissions" :key="submission.id" class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden p-6 space-y-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                                            <Calendar class="h-5 w-5" />
                                        </div>
                                        <div>
                                            <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">SUBMITTED ON</div>
                                            <div class="text-xs font-black text-slate-700">{{ new Date(submission.submitted_at).toLocaleDateString(undefined, { dateStyle: 'medium', timeStyle: 'short' }) }}</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <a :href="route('curriculum.assignments.submissions.download-compiled', submission.id)" target="_blank" class="h-10 px-4 rounded-xl border border-slate-100 bg-slate-50 flex items-center justify-center gap-2 text-[10px] font-black text-slate-500 uppercase tracking-widest hover:bg-slate-100 transition-all shadow-sm">
                                            <Download class="h-3.5 w-3.5" /> PDF Archive
                                        </a>
                                        <Badge :class="['rounded border-0 text-[10px] font-bold px-2.5 py-0.5 uppercase tracking-wider', getStatusBadge(submission.status)]">
                                            {{ submission.status }}
                                        </Badge>
                                    </div>
                                </div>

                                <div v-if="submission.feedback" class="p-4 bg-purple-50 rounded-xl border border-purple-100">
                                    <div class="text-[9px] font-black text-purple-600 uppercase tracking-widest mb-1 italic">Instructor Feedback</div>
                                    <p class="text-xs text-purple-800 font-medium">{{ submission.feedback }}</p>
                                    <div v-if="submission.marks_obtained !== null" class="mt-2 text-xs font-black text-purple-900 border-t border-purple-100 pt-2 animate-in slide-in-from-bottom-2">
                                        Performance Index: {{ submission.marks_obtained }} / {{ assignment.total_marks }}
                                    </div>
                                </div>

                                <div class="grid grid-cols-4 md:grid-cols-6 gap-3 pt-4 border-t border-slate-50">
                                    <div v-for="attachment in submission.attachments" :key="attachment.id" class="aspect-square rounded-lg border border-slate-100 bg-slate-50 flex flex-col items-center justify-center p-2 group hover:border-blue-300 transition-all cursor-pointer overflow-hidden">
                                        <template v-if="attachment.file_type.includes('image')">
                                            <img :src="`/storage/${attachment.file_path}`" class="w-full h-full object-cover rounded shadow-sm opacity-60 group-hover:opacity-100 transition-opacity" />
                                        </template>
                                        <template v-else>
                                            <span class="text-lg">{{ getFileIcon(attachment.file_type) }}</span>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Content Column -->
                <div class="space-y-6">

                    <!-- Info Card -->
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-6">
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-50 pb-3">Assignment Info</h3>

                        <div class="space-y-4">
                            <!-- Type -->
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 rounded-lg bg-orange-50 flex items-center justify-center text-orange-500">
                                    <Target class="h-4 w-4" />
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Type</span>
                                    <span class="text-xs font-bold text-slate-700 capitalize">{{ assignment.assignment_type }}</span>
                                </div>
                            </div>

                            <!-- Due Date -->
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600">
                                    <Calendar class="h-4 w-4" />
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Due Date</span>
                                    <span class="text-xs font-bold text-slate-700">{{ new Date(assignment.due_date).toLocaleDateString(undefined, { dateStyle: 'medium' }) }}</span>
                                </div>
                            </div>

                            <!-- Total Marks -->
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600">
                                    <CheckCircle2 class="h-4 w-4" />
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Total Marks</span>
                                    <span class="text-xs font-bold text-slate-700">{{ assignment.total_marks }} pts</span>
                                </div>
                            </div>

                            <!-- Teacher -->
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 rounded-lg bg-purple-50 flex items-center justify-center text-purple-600">
                                    <User class="h-4 w-4" />
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Teacher</span>
                                    <span class="text-xs font-bold text-slate-700">{{ assignment.teacher?.full_name || 'System Admin' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Curriculum Alignment Card -->
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-6">
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider border-b border-slate-50 pb-3">Academic Alignment</h3>

                        <div class="space-y-4">
                            <!-- Class -->
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-500">
                                        <Users class="h-4 w-4" />
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Class</span>
                                        <span class="text-xs font-bold text-slate-700">{{ assignment.classroom?.name || 'Unassigned' }}</span>
                                    </div>
                                </div>

                                <!-- Subject -->
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600">
                                        <BookOpen class="h-4 w-4" />
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Subject</span>
                                        <span class="text-xs font-bold text-slate-700">{{ assignment.subject?.name || 'Unassigned' }}</span>
                                    </div>
                                </div>

                                <!-- Strand -->
                                <div v-if="assignment.strand" class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600">
                                        <Layers class="h-4 w-4" />
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Strand</span>
                                        <span class="text-xs font-bold text-slate-700">{{ assignment.strand?.name }}</span>
                                    </div>
                                </div>

                                <!-- Sub-strand -->
                                <div v-if="assignment.sub_strand" class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-lg bg-orange-100 flex items-center justify-center text-orange-600">
                                        <Layers class="h-4 w-4" />
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Sub-strand</span>
                                        <span class="text-xs font-bold text-slate-700">{{ assignment.sub_strand?.name }}</span>
                                    </div>
                                </div>
                        </div>
                    </div>

                    <!-- Quick Navigation -->
                    <div class="p-2">
                        <Link href="/curriculum/assignments" class="w-full flex items-center justify-center gap-2 py-3 rounded-xl hover:bg-slate-50 text-slate-400 hover:text-slate-600 text-xs font-bold uppercase tracking-wider transition-all">
                            All Assignments <ChevronRight class="h-3 w-3" />
                        </Link>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
:deep(.prose) {
    line-height: 1.6;
}
:deep(.prose p) {
    margin-bottom: 1rem;
}
:deep(.prose h1, .prose h2, .prose h3) {
    font-weight: 700;
    margin-top: 1.5rem;
    margin-bottom: 0.75rem;
    color: #1e293b;
}
:deep(.prose ul) {
    list-style-type: disc;
    padding-left: 1.25rem;
    margin-bottom: 1rem;
}
:deep(.prose ol) {
    list-style-type: decimal;
    padding-left: 1.25rem;
    margin-bottom: 1rem;
}
</style>
