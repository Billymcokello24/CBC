<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    Sparkles, Plus, Search, Filter, 
    MoreVertical, Eye, Edit2, Trash2, 
    FileText, FileVideo, Image as ImageIcon, 
    Link as LinkIcon, Download, 
    Share2, Info, GraduationCap, ArrowLeft,
    CheckCircle2, X, Music, FileArchive, FileCode
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

// Search & Filter State
const searchQuery = ref('');
const selectedSubject = ref<string | number>('all');

const filteredResources = computed(() => {
    return props.resources.filter(r => {
        const matchesSearch = r.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                             r.description?.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesSubject = selectedSubject.value === 'all' || r.subject_id == selectedSubject.value;
        return matchesSearch && matchesSubject;
    });
});

// Modals State
const showModal = ref(false);
const editingResource = ref<any>(null);

const form = useForm({
    title: '',
    resource_type: '',
    url: '',
    description: '',
    subject_id: '',
    grade_level_id: '',
    is_recommended: false,
    file: null as File | null,
});

const openModal = (resource: any = null) => {
    editingResource.value = resource;
    if (resource) {
        form.title = resource.title;
        form.resource_type = resource.resource_type || '';
        form.url = resource.url || '';
        form.description = resource.description || '';
        form.subject_id = resource.subject_id || '';
        form.grade_level_id = resource.grade_level_id || '';
        form.is_recommended = resource.is_recommended || false;
        form.file = null;
    } else {
        form.reset();
        if (props.subjects.length > 0) form.subject_id = '';
        if (props.grades.length > 0) form.grade_level_id = '';
    }
    showModal.value = true;
};

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        const file = target.files[0];
        // 10MB soft limit on frontend, but PHP might be lower
        if (file.size > 10 * 1024 * 1024) {
            alert('File is too large for the curriculum gateway (Max 10MB per asset).');
            target.value = '';
            return;
        }
        form.file = file;
    }
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
        case 'audio': return Music;
        case 'pdf': return FileText;
        case 'doc': return FileText;
        case 'link': return LinkIcon;
        default: return FileText;
    }
};

const getResourceColor = (type: string) => {
    switch (type) {
        case 'video': return 'text-red-500 bg-red-50 border-red-100';
        case 'image': return 'text-violet-500 bg-violet-50 border-violet-100';
        case 'audio': return 'text-pink-500 bg-pink-50 border-pink-100';
        case 'pdf': return 'text-orange-500 bg-orange-50 border-orange-100';
        case 'doc': return 'text-blue-500 bg-blue-50 border-blue-100';
        case 'link': return 'text-emerald-500 bg-emerald-50 border-emerald-100';
        default: return 'text-slate-500 bg-slate-50 border-slate-100';
    }
};

</script>

<template>
    <Head title="Resources" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 p-8 font-sans max-w-[1600px] mx-auto animate-in fade-in duration-500">
            <!-- Header -->
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div class="space-y-1">
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900">Learning Resources</h1>
                    <p class="text-sm font-medium text-slate-500 italic">Access and manage educational materials, videos, and guides.</p>
                </div>
                
                <div class="flex flex-wrap items-center gap-3">
                    <div class="relative w-full sm:w-64">
                         <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-300 pointer-events-none" />
                         <Input v-model="searchQuery" placeholder="Search resources..." class="pl-10 h-12 rounded-xl text-xs border-slate-100 bg-white" />
                    </div>
                    
                    <select v-model="selectedSubject" class="h-12 px-4 rounded-xl border border-slate-100 bg-white text-xs font-bold uppercase tracking-wider outline-none focus:ring-2 focus:ring-blue-500 min-w-[180px]">
                        <option value="all">All Subjects</option>
                        <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select>

                    <Button @click="openModal()" class="bg-blue-600 hover:bg-blue-700 h-12 rounded-xl font-bold text-xs uppercase tracking-wider text-white shadow-md">
                        <Plus class="mr-2 h-4 w-4" /> Add Material
                    </Button>
                </div>
            </div>

            <!-- Resource Grid -->
            <div v-if="filteredResources.length > 0" class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <div v-for="resource in filteredResources" :key="resource.id" class="group relative flex flex-col p-6 rounded-[2rem] bg-white border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300">
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
                        <div class="flex flex-wrap gap-2">
                            <Badge variant="outline" class="text-[9px] font-bold text-blue-600 bg-blue-50 border-blue-100 uppercase tracking-widest px-2 py-0.5">
                                {{ resource.subject?.name || 'GENERIC' }}
                            </Badge>
                            <Badge v-if="resource.grade_level" variant="outline" class="text-[9px] font-bold text-slate-500 bg-slate-50 border-slate-100 uppercase tracking-widest px-2 py-0.5">
                                {{ resource.grade_level.name }}
                            </Badge>
                        </div>
                        <p v-if="resource.description" class="text-xs text-slate-500 line-clamp-2 mt-2 italic">{{ resource.description }}</p>
                    </div>

                    <div class="relative z-10 mt-8 pt-4 border-t border-slate-50 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                             <Badge v-if="resource.is_recommended" variant="outline" class="text-[8px] font-bold uppercase tracking-wider border-amber-100 bg-amber-50 text-amber-600 px-2 py-0.5">Featured</Badge>
                             <span class="text-[9px] font-bold text-slate-300 uppercase tracking-widest uppercase">{{ resource.resource_type }}</span>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <a v-if="resource.file_path" :href="'/storage/' + resource.file_path" target="_blank" class="p-2.5 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-100 transition-all border border-blue-100 shadow-sm">
                                <Download class="h-4 w-4" />
                            </a>
                            <a v-if="resource.url" :href="resource.url" target="_blank" class="p-2.5 rounded-xl bg-slate-50 text-slate-600 hover:bg-slate-100 transition-all border border-slate-100 shadow-sm">
                                <Eye class="h-4 w-4" />
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Add Placeholder -->
                <div @click="openModal()" class="p-8 rounded-[2rem] border-2 border-dashed border-slate-100 flex flex-col items-center justify-center gap-4 hover:bg-slate-50 hover:border-blue-200 transition-all duration-300 cursor-pointer group min-h-[260px]">
                     <div class="h-16 w-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-300 group-hover:bg-blue-50 group-hover:text-blue-600 transition-all shadow-sm">
                        <Plus class="h-8 w-8" />
                    </div>
                    <div class="text-center space-y-1">
                        <h4 class="text-base font-bold text-slate-400 group-hover:text-blue-600 transition-all uppercase tracking-tight">Add Material</h4>
                        <p class="text-[10px] text-slate-300 font-bold uppercase tracking-widest italic">PDF, Video, Audio or links</p>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="py-24 text-center rounded-[3rem] bg-slate-50 border-2 border-dashed border-slate-100">
                 <Sparkles class="h-12 w-12 text-slate-200 mx-auto mb-4" />
                 <h3 class="text-xl font-bold text-slate-400 tracking-tight mb-2 uppercase">No Assets Found</h3>
                 <p class="text-sm text-slate-400 italic font-medium">Try adjusting your search or filter criteria.</p>
                 <Button @click="openModal()" class="mt-8 bg-blue-600 hover:bg-blue-700 px-8 rounded-xl font-bold text-xs uppercase tracking-wider h-12 shadow-md text-white">
                    <Plus class="mr-2 h-4 w-4" /> Upload Resource
                 </Button>
            </div>

            <!-- Modal -->
            <Dialog v-model:open="showModal">
                <DialogContent class="sm:max-w-[550px] rounded-[2rem] border-slate-100 shadow-2xl p-0 overflow-hidden">
                    <div class="bg-blue-600 p-8 text-white relative">
                        <div class="relative z-10">
                            <h2 class="text-2xl font-bold tracking-tight mb-1">{{ editingResource ? 'Edit Resource' : 'Share Material' }}</h2>
                            <p class="text-blue-100 text-xs italic">{{ editingResource ? 'Update the details for this learning asset.' : 'Upload documents, videos, or share useful web links.' }}</p>
                        </div>
                        <div class="absolute right-0 top-0 h-full w-1/3 opacity-10 pointer-events-none">
                            <GraduationCap class="h-48 w-48 -mr-12 -mt-12" />
                        </div>
                    </div>

                    <form @submit.prevent="submit" class="p-8 space-y-6">
                        <div class="grid gap-2">
                            <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Material Title</Label>
                            <Input v-model="form.title" placeholder="e.g. Periodic Table Reference Guide" class="h-12 rounded-xl border-slate-100 font-medium text-sm" required />
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-2">
                                <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Learning Area / Subject</Label>
                                <select v-model="form.subject_id" class="w-full rounded-xl border-slate-100 bg-white px-3 h-10 text-xs font-bold uppercase tracking-wider focus:ring-2 focus:ring-blue-500 transition-all outline-none border" required>
                                    <option value="">Select Subject</option>
                                    <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
                                </select>
                            </div>
                            <div class="grid gap-2">
                                <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Target Grade Level</Label>
                                <select v-model="form.grade_level_id" class="w-full rounded-xl border-slate-100 bg-white px-3 h-10 text-xs font-bold uppercase tracking-wider focus:ring-2 focus:ring-blue-500 transition-all outline-none border">
                                    <option value="">Any Grade</option>
                                    <option v-for="g in grades" :key="g.id" :value="g.id">{{ g.name }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="space-y-4 p-5 rounded-2xl bg-slate-50 border border-slate-100">
                            <div class="flex items-center justify-between mb-2">
                                <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Resource Content</Label>
                                <Badge variant="outline" class="text-[8px] uppercase tracking-widest bg-white">Choose One</Badge>
                            </div>
                            
                            <div v-if="!editingResource" class="grid gap-4">
                                <div class="grid gap-2">
                                    <Label class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Upload File (Max 10MB)</Label>
                                    <div class="relative group">
                                        <input type="file" @change="handleFileChange" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20" />
                                        <div class="bg-white border border-slate-100 rounded-xl p-4 text-center group-hover:bg-blue-50 group-hover:border-blue-200 transition-all border-dashed border-2">
                                            <div v-if="form.file" class="flex items-center justify-center gap-2 text-blue-600 font-bold text-xs uppercase">
                                                <CheckCircle2 class="h-4 w-4" /> {{ form.file.name.substring(0, 20) }}...
                                            </div>
                                            <div v-else class="space-y-1">
                                                <Plus class="h-5 w-5 text-slate-300 mx-auto" />
                                                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Select PDF, Image, Video, Audio</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="relative py-2 flex items-center">
                                    <div class="flex-grow border-t border-slate-100"></div>
                                    <span class="flex-shrink mx-4 text-[9px] font-bold text-slate-300 uppercase tracking-widest">OR</span>
                                    <div class="flex-grow border-t border-slate-100"></div>
                                </div>
                            </div>

                            <div class="grid gap-2">
                                <Label class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">External Link (Optional)</Label>
                                <div class="relative">
                                    <LinkIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-slate-300" />
                                    <Input v-model="form.url" placeholder="https://youtube.com/..." class="h-10 pl-10 rounded-xl border-slate-100 text-xs font-medium" />
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Additional Notes / Description</Label>
                            <Textarea v-model="form.description" placeholder="Briefly describe what this resource covers..." class="rounded-xl border-slate-100 italic text-sm min-h-[80px]" />
                        </div>

                        <div class="flex items-center gap-2 p-1">
                            <input type="checkbox" id="recommended" v-model="form.is_recommended" class="h-4 w-4 rounded border-slate-200 text-blue-600 focus:ring-blue-500 transition-all cursor-pointer" />
                            <Label for="recommended" class="text-[10px] font-bold uppercase tracking-wider text-slate-500 cursor-pointer">Mark as featured / recommended</Label>
                        </div>

                        <DialogFooter class="pt-2">
                            <Button type="submit" :disabled="form.processing" class="w-full bg-blue-600 hover:bg-blue-700 rounded-xl font-bold text-sm text-white h-14 shadow-xl shadow-blue-500/10">
                                {{ form.processing ? 'Processing...' : (editingResource ? 'Update Changes' : 'Publish Resource') }}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

        </div>
    </AppLayout>
</template>

<style scoped>
/* Translucent slide animation */
.animate-in {
    animation: slide-up 0.4s ease-out;
}

@keyframes slide-up {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
