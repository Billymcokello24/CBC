<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    Sparkles, Plus, Search, Filter, 
    MoreVertical, Eye, Edit2, Trash2, 
    FileText, FileVideo, Image as ImageIcon, 
    Link as LinkIcon, Download, 
    Share2, Info, GraduationCap, ArrowLeft,
    CheckCircle2, X
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog";
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    resources: any[];
    subjects: any[];
    grades: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Resources', href: '#' },
];

// Modals State
const showModal = ref(false);
const editingResource = ref<any>(null);

const form = useForm({
    title: '',
    resource_type: 'document',
    url: '',
    description: '',
    subject_id: '',
    grade_level_id: '',
    is_recommended: false,
});

const openModal = (resource: any = null) => {
    editingResource.value = resource;
    if (resource) {
        form.title = resource.title;
        form.resource_type = resource.resource_type || 'document';
        form.url = resource.url;
        form.description = resource.description;
        form.subject_id = resource.subject_id;
        form.grade_level_id = resource.grade_level_id;
        form.is_recommended = resource.is_recommended || false;
    } else {
        form.reset();
        if (props.subjects.length > 0) form.subject_id = props.subjects[0].id;
        if (props.grades.length > 0) form.grade_level_id = props.grades[0].id;
    }
    showModal.value = true;
};

const submit = () => {
    if (editingResource.value) {
        form.put(route('curriculum.resources.update', editingResource.value.id), {
            onSuccess: () => {
                showModal.value = false;
                editingResource.value = null;
            },
        });
    } else {
        form.post(route('curriculum.resources.store'), {
            onSuccess: () => {
                showModal.value = false;
            },
        });
    }
};

const deleteResource = (id: number) => {
    if (confirm('Are you sure you want to remove this resource?')) {
        useForm({}).delete(route('curriculum.resources.destroy', id));
    }
};

const getResourceIcon = (type: string) => {
    switch (type) {
        case 'video': return FileVideo;
        case 'image': return ImageIcon;
        case 'link': return LinkIcon;
        default: return FileText;
    }
};

const getResourceColor = (type: string) => {
    switch (type) {
        case 'video': return 'text-red-500 bg-red-50 border-red-100';
        case 'image': return 'text-violet-500 bg-violet-50 border-violet-100';
        case 'link': return 'text-blue-500 bg-blue-50 border-blue-100';
        default: return 'text-cyan-500 bg-cyan-50 border-cyan-100';
    }
};

</script>

<template>
    <Head title="Resources" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 p-8 font-sans max-w-[1600px] mx-auto animate-in fade-in duration-500">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-1">
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900">Resources</h1>
                    <p class="text-sm font-medium text-slate-500 italic">Manage and share teaching materials with your students.</p>
                </div>
                
                <div class="flex items-center gap-3">
                    <div class="relative w-64">
                         <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-300 pointer-events-none" />
                         <Input placeholder="Search resources..." class="pl-10 h-12 rounded-xl text-xs border-slate-100 bg-white" />
                    </div>
                    <Button @click="openModal()" class="bg-blue-600 hover:bg-blue-700 h-12 rounded-xl font-bold text-xs uppercase tracking-wider text-white shadow-md">
                        <Plus class="mr-2 h-4 w-4" /> Add Material
                    </Button>
                </div>
            </div>

            <!-- Resource Grid -->
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <div v-for="resource in resources" :key="resource.id" class="group relative flex flex-col p-6 rounded-[2rem] bg-white border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300">
                    <div class="relative z-10 flex items-start justify-between mb-6">
                         <div :class="['h-14 w-14 rounded-2xl flex items-center justify-center border transition-all group-hover:scale-110 shadow-sm', getResourceColor(resource.resource_type)]">
                            <component :is="getResourceIcon(resource.resource_type)" class="h-7 w-7" />
                         </div>
                         <div class="flex items-center gap-0.5 opacity-0 group-hover:opacity-100 transition-opacity">
                            <Button @click="openModal(resource)" variant="ghost" size="icon" class="h-9 w-9 rounded-xl hover:bg-slate-50 transition-all">
                                <Edit2 class="h-4 w-4 text-slate-400 hover:text-blue-600" />
                            </Button>
                            <Button @click="deleteResource(resource.id)" variant="ghost" size="icon" class="h-9 w-9 rounded-xl hover:bg-red-50 transition-all text-red-500">
                                <Trash2 class="h-4 w-4" />
                            </Button>
                         </div>
                    </div>

                    <div class="relative z-10 flex-1 space-y-2">
                        <h3 class="text-lg font-bold text-slate-900 leading-tight group-hover:text-blue-600 transition-colors line-clamp-2 uppercase tracking-tight">{{ resource.title }}</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest italic truncate">{{ resource.subject?.name || 'GENERIC' }} • {{ resource.grade_level?.name || 'ANY GRADE' }}</p>
                    </div>

                    <div class="relative z-10 mt-8 pt-4 border-t border-slate-50 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                             <Badge v-if="resource.is_recommended" variant="outline" class="text-[8px] font-bold uppercase tracking-wider border-amber-100 bg-amber-50 text-amber-600 px-2 py-0.5">Featured</Badge>
                             <span class="text-[9px] font-bold text-slate-300 uppercase tracking-widest">Added Dec 24</span>
                        </div>
                        
                        <Button variant="ghost" size="icon" class="h-10 w-10 rounded-xl hover:bg-blue-50 text-blue-600 transition-all border border-transparent hover:border-blue-100 shadow-sm">
                            <Download class="h-5 w-5" />
                        </Button>
                    </div>
                </div>

                <!-- Add Placeholder -->
                <div @click="openModal()" class="p-8 rounded-[2rem] border-2 border-dashed border-slate-100 flex flex-col items-center justify-center gap-4 hover:bg-slate-50 hover:border-blue-200 transition-all duration-300 cursor-pointer group min-h-[260px]">
                     <div class="h-16 w-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-300 group-hover:bg-blue-50 group-hover:text-blue-600 transition-all shadow-sm">
                        <Plus class="h-8 w-8" />
                    </div>
                    <div class="text-center space-y-1">
                        <h4 class="text-base font-bold text-slate-400 group-hover:text-blue-600 transition-all uppercase tracking-tight">Add Material</h4>
                        <p class="text-[10px] text-slate-300 font-bold uppercase tracking-widest italic">Notes, videos, or links</p>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!resources.length" class="py-24 text-center rounded-[3rem] bg-slate-50 border-2 border-dashed border-slate-100">
                 <Sparkles class="h-12 w-12 text-slate-200 mx-auto mb-4" />
                 <h3 class="text-xl font-bold text-slate-400 tracking-tight mb-2 uppercase">No Assets Yet</h3>
                 <p class="text-sm text-slate-400 italic font-medium">Upload your teaching materials here for your students to access.</p>
                 <Button @click="openModal()" class="mt-8 bg-blue-600 hover:bg-blue-700 px-8 rounded-xl font-bold text-xs uppercase tracking-wider h-12 shadow-md text-white">
                    <Plus class="mr-2 h-4 w-4" /> Upload Resource
                 </Button>
            </div>

            <!-- Modal -->
            <Dialog v-model:open="showModal">
                <DialogContent class="sm:max-w-[500px] rounded-[2rem] border-slate-100 shadow-2xl">
                    <DialogHeader>
                        <DialogTitle class="text-xl font-bold tracking-tight">{{ editingResource ? 'Edit Resource' : 'Add Learning Material' }}</DialogTitle>
                        <DialogDescription class="text-xs text-slate-500 italic font-medium">
                            {{ editingResource ? 'Update the details for this curriculum asset.' : 'Share a document, video, or link with your students.' }}
                        </DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="submit" class="grid gap-6 py-4">
                        <div class="grid gap-2">
                            <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Material Title</Label>
                            <Input v-model="form.title" placeholder="e.g. Periodic Table Reference Guide" class="rounded-xl border-slate-100 italic text-sm" required />
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-2">
                                <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Type</Label>
                                <select v-model="form.resource_type" class="w-full rounded-xl border-slate-100 bg-white px-3 h-10 text-xs font-bold uppercase tracking-wider focus:ring-2 focus:ring-blue-500 transition-all outline-none border" required>
                                    <option value="document">Document (PDF/Doc)</option>
                                    <option value="video">Video Loop / Link</option>
                                    <option value="image">Diagram / Image</option>
                                    <option value="link">Web Resource</option>
                                </select>
                            </div>
                            <div class="grid gap-2">
                                <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Target Grade</Label>
                                <select v-model="form.grade_level_id" class="w-full rounded-xl border-slate-100 bg-white px-3 h-10 text-xs font-bold uppercase tracking-wider focus:ring-2 focus:ring-blue-500 transition-all outline-none border">
                                    <option value="">Any Grade</option>
                                    <option v-for="g in grades" :key="g.id" :value="g.id">{{ g.name }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">URL / Location</Label>
                            <Input v-model="form.url" placeholder="https://..." class="rounded-xl border-slate-100 italic text-sm font-medium" required />
                        </div>

                        <div class="grid gap-2">
                            <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Description</Label>
                            <Textarea v-model="form.description" placeholder="What is this material about?" class="rounded-xl border-slate-100 italic text-[13px] min-h-[80px] focus:ring-blue-500" />
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="recommended" v-model="form.is_recommended" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                            <Label for="recommended" class="text-[10px] font-bold uppercase tracking-wider text-slate-500 cursor-pointer">Recommend to all students</Label>
                        </div>

                        <DialogFooter>
                            <Button type="submit" :disabled="form.processing" class="w-full bg-blue-600 hover:bg-blue-700 rounded-xl font-bold text-sm text-white h-12 shadow-lg">
                                {{ form.processing ? 'Uploading...' : (editingResource ? 'Update Info' : 'Share Material') }}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

        </div>
    </AppLayout>
</template>

<style scoped>
/* Minimal styling */
</style>
