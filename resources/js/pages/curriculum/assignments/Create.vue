<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Underline from '@tiptap/extension-underline';
import TiptapLink from '@tiptap/extension-link';
import Placeholder from '@tiptap/extension-placeholder';
import {
    ArrowLeft, Save, Plus, X, Upload, FileText,
    Bold, Italic, Underline as UnderlineIcon, Link as LinkIcon, List, ListOrdered,
    Heading1, Heading2, Paperclip, Trash2,
    BookOpen, Target, Layers
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    subjects: any[];
    classes: any[];
    strands: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Assignments', href: '/curriculum/assignments' },
    { title: 'New Assignment', href: '#' },
];

const form = useForm({
    title: '',
    description: '',
    instructions: '',
    assignment_type: 'homework',
    due_date: '',
    assigned_date: new Date().toISOString().split('T')[0],
    total_marks: 100,
    class_id: '',
    subject_id: '',
    strand_id: '',
    sub_strand_id: '',
    academic_term_id: 1,
    attachments: [] as File[],
});

// Tiptap Editor
const editor = useEditor({
    extensions: [
        StarterKit.configure({
            heading: { levels: [1, 2, 3] },
        }),
        Underline,
        TiptapLink.configure({
            openOnClick: false,
            HTMLAttributes: { class: 'text-blue-600 underline cursor-pointer' },
        }),
        Placeholder.configure({
            placeholder: 'Write detailed instructions for this assignment...',
        }),
    ],
    content: '',
    editorProps: {
        attributes: {
            class: 'prose prose-sm max-w-none focus:outline-none min-h-[200px] px-4 py-3 text-sm text-slate-700 leading-relaxed',
        },
    },
    onUpdate: ({ editor: e }) => {
        form.instructions = e.getHTML();
    },
});

// Cascading selects
const filteredStrands = computed(() => {
    if (!form.subject_id) return [];
    return props.strands.filter((s: any) => String(s.subject_id) === String(form.subject_id));
});

const filteredSubStrands = computed(() => {
    if (!form.strand_id) return [];
    const strand = props.strands.find((s: any) => String(s.id) === String(form.strand_id));
    return strand?.sub_strands || [];
});

watch(() => form.subject_id, () => {
    form.strand_id = '';
    form.sub_strand_id = '';
});

watch(() => form.strand_id, () => {
    form.sub_strand_id = '';
});

// File attachments
const fileInput = ref<HTMLInputElement>();
const isDragOver = ref(false);

const addFiles = (files: FileList | null) => {
    if (!files) return;
    for (let i = 0; i < files.length; i++) {
        form.attachments.push(files[i]);
    }
};

const handleFileSelect = (e: Event) => {
    const target = e.target as HTMLInputElement;
    addFiles(target.files);
    target.value = '';
};

const handleDrop = (e: DragEvent) => {
    isDragOver.value = false;
    addFiles(e.dataTransfer?.files || null);
};

const removeFile = (index: number) => {
    form.attachments.splice(index, 1);
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
    if (type.includes('spreadsheet') || type.includes('excel')) return '📊';
    if (type.includes('presentation') || type.includes('powerpoint')) return '📑';
    return '📎';
};

// Link dialog
const setLink = () => {
    const url = window.prompt('Enter URL:');
    if (url && editor.value) {
        editor.value.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
    }
};

// Submit
const submit = () => {
    form.post(route('curriculum.assignments.store'), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="New Assignment" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 font-sans max-w-[1200px] mx-auto bg-[#f9fafb]/30">

            <!-- Header -->
            <div class="flex items-center justify-between gap-4">
                <div class="space-y-1">
                    <Link href="/curriculum/assignments" class="group inline-flex items-center gap-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider hover:text-blue-600 transition-all mb-1">
                        <ArrowLeft class="h-3 w-3 group-hover:-translate-x-1 transition-transform" /> Assignments
                    </Link>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">New Assignment</h1>
                </div>
                <div class="flex items-center gap-3">
                    <Link href="/curriculum/assignments">
                        <Button variant="outline" class="h-10 px-4 rounded-xl border-slate-200 bg-white font-semibold text-xs text-slate-600 shadow-sm hover:bg-slate-50">
                            Cancel
                        </Button>
                    </Link>
                    <Button @click="submit" :disabled="form.processing" class="h-10 px-6 rounded-xl bg-blue-600 hover:bg-blue-700 font-semibold text-xs text-white shadow-sm transition-all">
                        <Save class="mr-2 h-4 w-4" /> {{ form.processing ? 'Saving...' : 'Publish Assignment' }}
                    </Button>
                </div>
            </div>

            <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Left Column (Main Content) -->
                <div class="lg:col-span-2 flex flex-col gap-6">

                    <!-- Title & Description -->
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
                        <div class="space-y-1.5">
                            <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Title</Label>
                            <Input v-model="form.title" placeholder="e.g. Weekly Vocabulary Quiz" class="h-12 rounded-xl border-slate-100 text-lg font-bold text-slate-900 placeholder:text-slate-300" required />
                            <p v-if="form.errors.title" class="text-xs text-red-500 font-medium">{{ form.errors.title }}</p>
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Short Description</Label>
                            <Input v-model="form.description" placeholder="A brief summary of this assignment..." class="h-10 rounded-xl border-slate-100 text-sm font-medium text-slate-600 placeholder:text-slate-300" />
                        </div>
                    </div>

                    <!-- Rich Text Editor -->
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-50">
                            <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Instructions</Label>
                        </div>

                        <!-- Toolbar -->
                        <div class="flex flex-wrap items-center gap-1 px-4 py-3 border-b border-slate-50 bg-[#f9fafb]/50" v-if="editor">
                            <button type="button" @click="editor?.chain().focus().toggleBold().run()"
                                :class="['h-8 w-8 rounded-lg flex items-center justify-center transition-all', editor?.isActive('bold') ? 'bg-blue-100 text-blue-600' : 'text-slate-400 hover:bg-slate-100 hover:text-slate-600']">
                                <Bold class="h-3.5 w-3.5" />
                            </button>
                            <button type="button" @click="editor?.chain().focus().toggleItalic().run()"
                                :class="['h-8 w-8 rounded-lg flex items-center justify-center transition-all', editor?.isActive('italic') ? 'bg-blue-100 text-blue-600' : 'text-slate-400 hover:bg-slate-100 hover:text-slate-600']">
                                <Italic class="h-3.5 w-3.5" />
                            </button>
                            <button type="button" @click="editor?.chain().focus().toggleUnderline().run()"
                                :class="['h-8 w-8 rounded-lg flex items-center justify-center transition-all', editor?.isActive('underline') ? 'bg-blue-100 text-blue-600' : 'text-slate-400 hover:bg-slate-100 hover:text-slate-600']">
                                <UnderlineIcon class="h-3.5 w-3.5" />
                            </button>

                            <div class="w-px h-5 bg-slate-200 mx-1"></div>

                            <button type="button" @click="editor?.chain().focus().toggleHeading({ level: 1 }).run()"
                                :class="['h-8 w-8 rounded-lg flex items-center justify-center transition-all', editor?.isActive('heading', { level: 1 }) ? 'bg-blue-100 text-blue-600' : 'text-slate-400 hover:bg-slate-100 hover:text-slate-600']">
                                <Heading1 class="h-3.5 w-3.5" />
                            </button>
                            <button type="button" @click="editor?.chain().focus().toggleHeading({ level: 2 }).run()"
                                :class="['h-8 w-8 rounded-lg flex items-center justify-center transition-all', editor?.isActive('heading', { level: 2 }) ? 'bg-blue-100 text-blue-600' : 'text-slate-400 hover:bg-slate-100 hover:text-slate-600']">
                                <Heading2 class="h-3.5 w-3.5" />
                            </button>

                            <div class="w-px h-5 bg-slate-200 mx-1"></div>

                            <button type="button" @click="editor?.chain().focus().toggleBulletList().run()"
                                :class="['h-8 w-8 rounded-lg flex items-center justify-center transition-all', editor?.isActive('bulletList') ? 'bg-blue-100 text-blue-600' : 'text-slate-400 hover:bg-slate-100 hover:text-slate-600']">
                                <List class="h-3.5 w-3.5" />
                            </button>
                            <button type="button" @click="editor?.chain().focus().toggleOrderedList().run()"
                                :class="['h-8 w-8 rounded-lg flex items-center justify-center transition-all', editor?.isActive('orderedList') ? 'bg-blue-100 text-blue-600' : 'text-slate-400 hover:bg-slate-100 hover:text-slate-600']">
                                <ListOrdered class="h-3.5 w-3.5" />
                            </button>

                            <div class="w-px h-5 bg-slate-200 mx-1"></div>

                            <button type="button" @click="setLink"
                                :class="['h-8 w-8 rounded-lg flex items-center justify-center transition-all', editor?.isActive('link') ? 'bg-blue-100 text-blue-600' : 'text-slate-400 hover:bg-slate-100 hover:text-slate-600']">
                                <LinkIcon class="h-3.5 w-3.5" />
                            </button>
                        </div>

                        <!-- Editor Content -->
                        <EditorContent :editor="editor" class="min-h-[250px]" />
                    </div>

                    <!-- Attachments -->
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Attachments</Label>
                            <Badge class="rounded bg-slate-100 text-slate-500 border-0 text-[9px] font-bold px-2 py-0.5">
                                {{ form.attachments.length }} files
                            </Badge>
                        </div>

                        <!-- Drop Zone -->
                        <div
                            @drop.prevent="handleDrop"
                            @dragover.prevent="isDragOver = true"
                            @dragleave="isDragOver = false"
                            @click="fileInput?.click()"
                            :class="[
                                'p-8 rounded-xl border-2 border-dashed flex flex-col items-center justify-center gap-3 cursor-pointer transition-all text-center',
                                isDragOver ? 'border-blue-400 bg-blue-50/50' : 'border-slate-100 hover:border-blue-200 hover:bg-[#f9fafb]/50'
                            ]"
                        >
                            <div class="h-12 w-12 rounded-full bg-slate-50 flex items-center justify-center text-slate-300">
                                <Upload class="h-5 w-5" />
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-600">Drop files here or <span class="text-blue-600">browse</span></p>
                                <p class="text-[10px] font-medium text-slate-400 mt-0.5">PDF, DOC, DOCX, PPT, Images (max 10MB each)</p>
                            </div>
                        </div>
                        <input ref="fileInput" type="file" multiple accept=".pdf,.doc,.docx,.ppt,.pptx,.png,.jpg,.jpeg,.gif,.xls,.xlsx" class="hidden" @change="handleFileSelect" />

                        <!-- File List -->
                        <div v-if="form.attachments.length" class="space-y-2">
                            <div v-for="(file, index) in form.attachments" :key="index" class="flex items-center justify-between p-3 rounded-xl bg-[#f9fafb] border border-slate-50 group hover:bg-white hover:border-slate-100 transition-all">
                                <div class="flex items-center gap-3">
                                    <span class="text-lg">{{ getFileIcon(file.type) }}</span>
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-slate-700 line-clamp-1">{{ file.name }}</span>
                                        <span class="text-[10px] font-medium text-slate-400">{{ formatFileSize(file.size) }}</span>
                                    </div>
                                </div>
                                <button type="button" @click="removeFile(index)" class="h-7 w-7 rounded-lg flex items-center justify-center text-slate-300 hover:bg-red-50 hover:text-red-500 transition-all opacity-0 group-hover:opacity-100">
                                    <X class="h-3.5 w-3.5" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column (Sidebar) -->
                <div class="flex flex-col gap-6">

                    <!-- Assignment Details -->
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Details</h3>

                        <div class="space-y-1.5">
                            <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Type</Label>
                            <select v-model="form.assignment_type" class="w-full h-10 rounded-xl border border-slate-100 bg-white px-3 text-xs font-semibold text-slate-700 outline-none focus:ring-2 focus:ring-blue-100 transition-all">
                                <option value="homework">Homework</option>
                                <option value="project">Project</option>
                                <option value="practical">Practical</option>
                                <option value="research">Research</option>
                                <option value="presentation">Presentation</option>
                                <option value="group_work">Group Work</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Assigned</Label>
                                <Input type="date" v-model="form.assigned_date" class="h-10 rounded-xl border-slate-100 text-xs font-semibold" required />
                            </div>
                            <div class="space-y-1.5">
                                <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Due Date</Label>
                                <Input type="date" v-model="form.due_date" class="h-10 rounded-xl border-slate-100 text-xs font-semibold" required />
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Marks</Label>
                            <Input type="number" v-model="form.total_marks" class="h-10 rounded-xl border-slate-100 text-sm font-bold" required />
                        </div>
                    </div>

                    <!-- Class & Subject -->
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Classification</h3>

                        <div class="space-y-1.5">
                            <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                                <BookOpen class="h-3 w-3 text-blue-500" /> Class
                            </Label>
                            <select v-model="form.class_id" class="w-full h-10 rounded-xl border border-slate-100 bg-white px-3 text-xs font-semibold text-slate-700 outline-none focus:ring-2 focus:ring-blue-100 transition-all" required>
                                <option value="" disabled>Select class...</option>
                                <option v-for="c in classes" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                                <Target class="h-3 w-3 text-blue-500" /> Subject
                            </Label>
                            <select v-model="form.subject_id" class="w-full h-10 rounded-xl border border-slate-100 bg-white px-3 text-xs font-semibold text-slate-700 outline-none focus:ring-2 focus:ring-blue-100 transition-all" required>
                                <option value="" disabled>Select subject...</option>
                                <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
                            </select>
                        </div>

                        <!-- Strand (cascading) -->
                        <div class="space-y-1.5" v-if="filteredStrands.length">
                            <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                                <Layers class="h-3 w-3 text-emerald-500" /> Strand
                            </Label>
                            <select v-model="form.strand_id" class="w-full h-10 rounded-xl border border-slate-100 bg-white px-3 text-xs font-semibold text-slate-700 outline-none focus:ring-2 focus:ring-blue-100 transition-all">
                                <option value="">No strand</option>
                                <option v-for="strand in filteredStrands" :key="strand.id" :value="strand.id">{{ strand.name }}</option>
                            </select>
                        </div>

                        <!-- Sub-strand (cascading) -->
                        <div class="space-y-1.5" v-if="filteredSubStrands.length">
                            <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                                <Layers class="h-3 w-3 text-orange-500" /> Sub-strand
                            </Label>
                            <select v-model="form.sub_strand_id" class="w-full h-10 rounded-xl border border-slate-100 bg-white px-3 text-xs font-semibold text-slate-700 outline-none focus:ring-2 focus:ring-blue-100 transition-all">
                                <option value="">No sub-strand</option>
                                <option v-for="ss in filteredSubStrands" :key="ss.id" :value="ss.id">{{ ss.name }}</option>
                            </select>
                        </div>

                        <!-- Empty strand hint -->
                        <div v-if="form.subject_id && !filteredStrands.length" class="p-3 rounded-xl bg-slate-50 border border-dashed border-slate-100 text-center">
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">No strands for this subject</p>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-3">
                        <Button @click="submit" :disabled="form.processing" class="w-full h-11 rounded-xl bg-blue-600 hover:bg-blue-700 font-bold text-xs text-white shadow-sm uppercase tracking-wider transition-all">
                            <Save class="mr-2 h-4 w-4" /> {{ form.processing ? 'Saving...' : 'Publish Assignment' }}
                        </Button>
                        <Link href="/curriculum/assignments">
                            <Button variant="outline" class="w-full h-11 rounded-xl border-slate-200 font-semibold text-xs text-slate-500 hover:bg-slate-50 transition-all">
                                Cancel & Return
                            </Button>
                        </Link>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<style>
/* Tiptap Styles */
.tiptap p.is-editor-empty:first-child::before {
    content: attr(data-placeholder);
    float: left;
    color: #94a3b8;
    pointer-events: none;
    height: 0;
    font-style: italic;
}
.tiptap h1 { font-size: 1.5rem; font-weight: 700; margin: 0.75rem 0 0.5rem; color: #1e293b; }
.tiptap h2 { font-size: 1.25rem; font-weight: 700; margin: 0.75rem 0 0.5rem; color: #1e293b; }
.tiptap h3 { font-size: 1.1rem; font-weight: 600; margin: 0.5rem 0 0.25rem; color: #334155; }
.tiptap ul { list-style: disc; padding-left: 1.25rem; margin: 0.5rem 0; }
.tiptap ol { list-style: decimal; padding-left: 1.25rem; margin: 0.5rem 0; }
.tiptap li { margin: 0.25rem 0; }
.tiptap p { margin: 0.25rem 0; }
.tiptap a { color: #2563eb; text-decoration: underline; cursor: pointer; }
.tiptap blockquote { border-left: 3px solid #e2e8f0; padding-left: 1rem; color: #64748b; font-style: italic; margin: 0.5rem 0; }
</style>
