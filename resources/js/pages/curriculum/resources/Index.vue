<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    Sparkles, Plus, Search, Filter, 
    MoreVertical, Eye, Edit2, Trash2, 
    FileText, FileVideo, Image as ImageIcon, 
    Link as LinkIcon, Download, 
    Share2, Info, GraduationCap, ArrowLeft,
    CheckCircle2, X, Music, FileArchive, FileCode,
    LayoutGrid, List, ArrowUpDown, Clock, ExternalLink, MoreHorizontal
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
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
    DropdownMenuSeparator,
} from "@/components/ui/dropdown-menu";
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    resources: any[];
    subjects: any[];
    grades: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Learning Resources', href: '#' },
];

// UI State
const viewMode = ref<'grid' | 'list'>('grid');
const searchQuery = ref('');
const selectedSubject = ref<string | number>('all');
const selectedType = ref('all');
const sortBy = ref('newest');

// Stats Logic
const stats = computed(() => ({
    total: props.resources.length,
    recommended: props.resources.filter(r => r.is_recommended).length,
    documents: props.resources.filter(r => ['pdf', 'doc', 'document'].includes(r.resource_type)).length,
    links: props.resources.filter(r => r.resource_type === 'link').length,
}));

// Filtering Logic
const filteredResources = computed(() => {
    let results = props.resources.filter(r => {
        const matchesSearch = r.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                             r.description?.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesSubject = selectedSubject.value === 'all' || r.subject_id == selectedSubject.value;
        const matchesType = selectedType.value === 'all' || r.resource_type === selectedType.value;
        return matchesSearch && matchesSubject && matchesType;
    });

    results.sort((a, b) => {
        if (sortBy.value === 'newest') return new Date(b.created_at).getTime() - new Date(a.created_at).getTime();
        if (sortBy.value === 'oldest') return new Date(a.created_at).getTime() - new Date(b.created_at).getTime();
        if (sortBy.value === 'alphabetical') return a.title.localeCompare(b.title);
        return 0;
    });

    return results;
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
    }
    showModal.value = true;
};

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        const file = target.files[0];
        if (file.size > 15 * 1024 * 1024) { // 15MB limit
            alert('Academic Asset too large (Max 15MB).');
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
    if (confirm('Permanently remove this academic asset from the curriculum gateway?')) {
        useForm({}).delete(route('curriculum.resources.destroy', id), {
            preserveScroll: true
        });
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
        case 'video': return 'text-red-600 bg-red-50 border-red-100';
        case 'image': return 'text-violet-600 bg-violet-50 border-violet-100';
        case 'audio': return 'text-pink-600 bg-pink-50 border-pink-100';
        case 'pdf': return 'text-orange-600 bg-orange-50 border-orange-100';
        case 'doc': return 'text-blue-600 bg-blue-50 border-blue-100';
        case 'link': return 'text-emerald-600 bg-emerald-50 border-emerald-100';
        default: return 'text-slate-600 bg-slate-50 border-slate-100';
    }
};

const clearFilters = () => {
    searchQuery.value = '';
    selectedSubject.value = 'all';
    selectedType.value = 'all';
    sortBy.value = 'newest';
};

</script>

<template>
    <Head title="Curriculum Resources" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 md:p-8 animate-in fade-in duration-500 max-w-[1600px] mx-auto w-full">
            
            <!-- Page Header (Standardized Arrangement) -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 shadow-sm border border-primary/5">
                        <Sparkles class="h-6 w-6 text-primary" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Academic Resources</h1>
                        <p class="text-sm text-muted-foreground font-medium">Global Curriculum Gateway • Reference Assets</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" class="rounded-xl h-10 px-4 font-semibold">
                        <Download class="mr-2 h-4 w-4" /> Export Directory
                    </Button>
                    <Button @click="openModal()" class="bg-primary hover:opacity-90 rounded-xl h-10 px-6 font-bold shadow-lg shadow-primary/20">
                        <Plus class="mr-2 h-4 w-4" /> Add Material
                    </Button>
                </div>
            </div>

            <!-- Enhanced Stats Grid -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border bg-card p-5 shadow-sm transition-all hover:border-primary/20">
                    <div class="text-xs font-bold text-muted-foreground uppercase tracking-widest mb-2">Academic Assets</div>
                    <div class="flex items-center justify-between">
                        <div class="text-2xl font-black text-slate-900">{{ stats.total }}</div>
                        <div class="h-8 w-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600">
                            <FileArchive class="h-4 w-4" />
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border bg-card p-5 shadow-sm transition-all hover:border-primary/20">
                    <div class="text-xs font-bold text-muted-foreground uppercase tracking-widest mb-2">Featured Guides</div>
                    <div class="flex items-center justify-between">
                        <div class="text-2xl font-black text-slate-900">{{ stats.recommended }}</div>
                        <div class="h-8 w-8 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600">
                            <Sparkles class="h-4 w-4" />
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border bg-card p-5 shadow-sm transition-all hover:border-primary/20">
                    <div class="text-xs font-bold text-muted-foreground uppercase tracking-widest mb-2">Document Vault</div>
                    <div class="flex items-center justify-between">
                        <div class="text-2xl font-black text-slate-900">{{ stats.documents }}</div>
                        <div class="h-8 w-8 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600">
                            <FileText class="h-4 w-4" />
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border bg-card p-5 shadow-sm transition-all hover:border-primary/20">
                    <div class="text-xs font-bold text-muted-foreground uppercase tracking-widest mb-2">Interactive Links</div>
                    <div class="flex items-center justify-between">
                        <div class="text-2xl font-black text-slate-900">{{ stats.links }}</div>
                        <div class="h-8 w-8 rounded-lg bg-violet-50 flex items-center justify-center text-violet-600">
                            <ExternalLink class="h-4 w-4" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Management Toolbar -->
            <div class="flex flex-col gap-4 rounded-xl border bg-card p-4 md:flex-row md:items-center shadow-sm">
                <div class="relative flex-1 md:max-w-md group">
                    <Search class="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground transition-colors group-focus-within:text-primary" />
                    <Input 
                        v-model="searchQuery" 
                        placeholder="Search resource titles or content..." 
                        class="pl-10 h-11 rounded-xl border-slate-200 bg-slate-50/30 focus:bg-white transition-all shadow-none" 
                    />
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <select v-model="selectedSubject" class="h-10 px-4 rounded-xl border border-slate-200 bg-white text-[10px] font-bold uppercase tracking-widest outline-none focus:ring-2 focus:ring-primary/20 transition-all min-w-[140px]">
                        <option value="all">All Subjects</option>
                        <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select>

                    <select v-model="selectedType" class="h-10 px-4 rounded-xl border border-slate-200 bg-white text-[10px] font-bold uppercase tracking-widest outline-none focus:ring-2 focus:ring-primary/20 transition-all min-w-[120px]">
                        <option value="all">All Types</option>
                        <option value="pdf">PDF Docs</option>
                        <option value="doc">Word Docs</option>
                        <option value="video">Videos</option>
                        <option value="link">Web Links</option>
                        <option value="image">Images</option>
                    </select>

                    <DropdownMenu>
                         <DropdownMenuTrigger as-child>
                             <Button variant="outline" size="sm" class="h-10 rounded-xl font-bold uppercase text-[10px] tracking-widest gap-2">
                                 <ArrowUpDown class="h-3.5 w-3.5" /> Sort
                             </Button>
                         </DropdownMenuTrigger>
                         <DropdownMenuContent align="end" class="w-48 rounded-xl p-2">
                             <DropdownMenuItem @click="sortBy = 'newest'" class="rounded-lg text-[11px] font-bold uppercase tracking-tight">Newest Added</DropdownMenuItem>
                             <DropdownMenuItem @click="sortBy = 'oldest'" class="rounded-lg text-[11px] font-bold uppercase tracking-tight">Oldest Added</DropdownMenuItem>
                             <DropdownMenuItem @click="sortBy = 'alphabetical'" class="rounded-lg text-[11px] font-bold uppercase tracking-tight">Alphabetical (A-Z)</DropdownMenuItem>
                         </DropdownMenuContent>
                    </DropdownMenu>

                    <div class="h-8 w-px bg-border mx-1"></div>

                    <div class="flex bg-muted/50 p-1 rounded-xl border">
                        <Button 
                            variant="ghost" 
                            size="icon" 
                            @click="viewMode = 'grid'" 
                            :class="['h-8 w-8 rounded-lg transition-all', viewMode === 'grid' ? 'bg-white shadow-sm text-primary' : 'text-slate-400']"
                        >
                            <LayoutGrid class="h-4 w-4" />
                        </Button>
                        <Button 
                            variant="ghost" 
                            size="icon" 
                            @click="viewMode = 'list'" 
                            :class="['h-8 w-8 rounded-lg transition-all', viewMode === 'list' ? 'bg-white shadow-sm text-primary' : 'text-slate-400']"
                        >
                            <List class="h-4 w-4" />
                        </Button>
                    </div>

                    <Button variant="ghost" size="sm" @click="clearFilters" class="h-10 rounded-xl text-xs font-semibold text-muted-foreground hover:text-primary transition-colors px-4">
                        Reset
                    </Button>
                </div>
            </div>

            <!-- Resource Content Area -->
            <div v-if="filteredResources.length > 0">
                <!-- GRID VIEW -->
                <div v-if="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 animate-in fade-in zoom-in-95 duration-500">
                    <div v-for="resource in filteredResources" :key="resource.id" class="group relative flex flex-col p-6 rounded-[2.5rem] bg-white border border-slate-100 shadow-sm hover:shadow-xl hover:border-primary/20 transition-all duration-500 overflow-hidden">
                        <!-- Decorative Backdrop -->
                        <div :class="['absolute top-0 right-0 h-32 w-32 -mr-12 -mt-12 opacity-[0.03] transition-all group-hover:scale-150 group-hover:opacity-[0.05]', getResourceColor(resource.resource_type)]">
                            <component :is="getResourceIcon(resource.resource_type)" class="h-full w-full" />
                        </div>

                        <div class="relative z-10 flex items-start justify-between mb-6">
                             <div :class="['h-14 w-14 rounded-2xl flex items-center justify-center border-2 transition-all group-hover:rotate-6 shadow-sm', getResourceColor(resource.resource_type)]">
                                <component :is="getResourceIcon(resource.resource_type)" class="h-7 w-7" />
                             </div>
                             <div class="flex items-center gap-1">
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="ghost" size="icon" class="h-9 w-9 rounded-xl hover:bg-slate-50">
                                            <MoreVertical class="h-4 w-4 text-slate-400" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end" class="w-48 rounded-xl p-2">
                                        <DropdownMenuItem @click="openModal(resource)" class="rounded-lg">
                                            <Edit2 class="mr-2 h-4 w-4" /> Edit Details
                                        </DropdownMenuItem>
                                        <DropdownMenuItem @click="deleteResource(resource.id)" class="rounded-lg text-destructive focus:bg-destructive/10">
                                            <Trash2 class="mr-2 h-4 w-4" /> Remove Asset
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                             </div>
                        </div>

                        <div class="relative z-10 flex-1 space-y-3">
                            <div class="flex items-center gap-2">
                                <Badge v-if="resource.is_recommended" class="bg-amber-100 text-amber-700 text-[8px] font-black uppercase tracking-wider h-5 flex items-center">Featured</Badge>
                                <span class="text-[9px] font-black text-slate-300 uppercase tracking-[0.2em]">{{ resource.resource_type }}</span>
                            </div>
                            <h3 class="text-lg font-black text-slate-900 leading-[1.2] group-hover:text-primary transition-colors line-clamp-2 uppercase tracking-tight">{{ resource.title }}</h3>
                            
                            <div class="flex flex-wrap gap-1.5 mt-2">
                                <Badge variant="outline" class="text-[9px] font-bold text-slate-500 bg-slate-50 border-slate-100 uppercase tracking-widest px-2.5 h-6">
                                    {{ resource.subject?.name || 'Academic' }}
                                </Badge>
                                <Badge v-if="resource.gradeLevel" variant="outline" class="text-[9px] font-bold text-primary bg-primary/5 border-primary/10 uppercase tracking-widest px-2.5 h-6">
                                    {{ resource.gradeLevel.name }}
                                </Badge>
                            </div>
                            <p v-if="resource.description" class="text-[11px] text-slate-400 font-medium line-clamp-2 mt-4 italic leading-relaxed">{{ resource.description }}</p>
                        </div>

                        <div class="relative z-10 mt-8 pt-5 border-t border-slate-50 flex items-center justify-between">
                            <div class="flex items-center gap-2 text-[10px] text-slate-400 font-bold uppercase tracking-wider">
                                <Clock class="h-3 w-3" /> {{ new Date(resource.created_at).toLocaleDateString() }}
                            </div>
                            
                            <div class="flex items-center gap-2">
                                <a v-if="resource.file_path" :href="'/storage/' + resource.file_path" target="_blank" class="h-10 px-4 rounded-xl bg-primary text-white hover:opacity-90 transition-all font-bold text-[10px] uppercase tracking-widest flex items-center gap-2 shadow-lg shadow-primary/20">
                                    <Download class="h-3.5 w-3.5" /> Grab Asset
                                </a>
                                <a v-if="resource.url" :href="resource.url" target="_blank" class="h-10 px-4 rounded-xl border-2 border-slate-100 text-slate-600 hover:bg-slate-50 transition-all font-bold text-[10px] uppercase tracking-widest flex items-center gap-2">
                                    <Eye class="h-3.5 w-3.5" /> Inspect
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Modern Add Area -->
                    <div @click="openModal()" class="group relative p-8 rounded-[2.5rem] border-3 border-dashed border-slate-100 flex flex-col items-center justify-center gap-6 hover:bg-primary/[0.02] hover:border-primary/30 transition-all duration-500 cursor-pointer min-h-[320px]">
                         <div class="h-20 w-20 rounded-full bg-slate-50 flex items-center justify-center text-slate-200 group-hover:bg-primary/10 group-hover:text-primary transition-all shadow-sm group-hover:scale-110">
                            <Plus class="h-10 w-10" />
                        </div>
                        <div class="text-center space-y-2">
                            <h4 class="text-xl font-black text-slate-400 group-hover:text-slate-600 transition-all uppercase tracking-tight">Provision Material</h4>
                            <p class="text-[10px] text-slate-300 font-black uppercase tracking-[0.2em] px-8">PDF, Instructionals, Videos or Strategic External Links</p>
                        </div>
                    </div>
                </div>

                <!-- LIST VIEW -->
                <div v-else class="rounded-2xl border bg-card shadow-sm overflow-hidden animate-in fade-in slide-in-from-bottom-2">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b bg-muted/30">
                                    <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-widest text-muted-foreground">Academic Asset</th>
                                    <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-widest text-muted-foreground">Discipline</th>
                                    <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-widest text-muted-foreground">Type</th>
                                    <th class="px-6 py-4 text-left text-[10px] font-black uppercase tracking-widest text-muted-foreground">Published</th>
                                    <th class="px-6 py-4 text-right text-[10px] font-black uppercase tracking-widest text-muted-foreground">Operational Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="resource in filteredResources" :key="resource.id" class="border-b transition-colors hover:bg-muted/30 group">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <div :class="['h-10 w-10 rounded-xl flex items-center justify-center border transition-all', getResourceColor(resource.resource_type)]">
                                                <component :is="getResourceIcon(resource.resource_type)" class="h-5 w-5" />
                                            </div>
                                            <div>
                                                <div class="font-black text-slate-900 group-hover:text-primary transition-colors text-sm uppercase tracking-tight">{{ resource.title }}</div>
                                                <div class="text-[11px] text-muted-foreground font-medium line-clamp-1 max-w-sm mt-0.5">{{ resource.description || 'No meta description provided' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col gap-1">
                                            <Badge variant="outline" class="w-fit text-[9px] font-bold uppercase tracking-widest">{{ resource.subject?.name || 'General' }}</Badge>
                                            <span v-if="resource.gradeLevel" class="text-[10px] text-primary font-black uppercase tracking-widest">{{ resource.gradeLevel.name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <Badge :variant="resource.resource_type === 'link' ? 'outline' : 'secondary'" class="text-[9px] font-black uppercase tracking-widest rounded-lg">
                                            {{ resource.resource_type }}
                                        </Badge>
                                    </td>
                                    <td class="px-6 py-4 text-[11px] text-slate-500 font-bold uppercase tracking-wider">
                                        {{ new Date(resource.created_at).toLocaleDateString('en-GB') }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <Button v-if="resource.file_path" variant="outline" size="sm" as-child class="h-9 rounded-xl px-4 border-slate-200 shadow-sm">
                                                <a :href="'/storage/' + resource.file_path" target="_blank" class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest">
                                                    <Download class="h-3.5 w-3.5" /> Download
                                                </a>
                                            </Button>
                                            <Button v-if="resource.url" variant="outline" size="sm" as-child class="h-9 rounded-xl px-4 border-slate-200 shadow-sm">
                                                <a :href="resource.url" target="_blank" class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest">
                                                    <ExternalLink class="h-3.5 w-3.5" /> Open
                                                </a>
                                            </Button>
                                            
                                            <DropdownMenu>
                                                <DropdownMenuTrigger as-child>
                                                    <Button variant="ghost" size="icon" class="h-9 w-9 rounded-xl"><MoreHorizontal class="h-4 w-4" /></Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end" class="w-48 rounded-xl p-2">
                                                    <DropdownMenuItem @click="openModal(resource)" class="rounded-lg">
                                                        <Edit2 class="mr-2 h-4 w-4" /> Edit Meta
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem @click="deleteResource(resource.id)" class="rounded-lg text-destructive">
                                                        <Trash2 class="mr-2 h-4 w-4" /> Remove Resource
                                                    </DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Enhanced Empty State -->
            <div v-else class="py-32 text-center rounded-[3rem] bg-slate-50 border-3 border-dashed border-slate-100 flex flex-col items-center justify-center animate-in fade-in duration-700">
                 <div class="h-20 w-20 rounded-full bg-white flex items-center justify-center text-slate-200 shadow-sm mb-6">
                    <Sparkles class="h-10 w-10" />
                 </div>
                 <h3 class="text-2xl font-black text-slate-400 tracking-tight mb-3 uppercase">Resource Matrix Empty</h3>
                 <p class="text-sm text-slate-400 italic font-medium max-w-sm px-6">We couldn't find any learning materials matching your active filters. Expand your search or provide new material.</p>
                 <Button @click="openModal()" class="mt-10 bg-primary hover:opacity-90 px-10 rounded-2xl font-black text-[11px] uppercase tracking-[0.2em] h-14 shadow-xl shadow-primary/20 text-white flex items-center gap-3">
                    <Plus class="h-5 w-5" /> Provision New Asset
                 </Button>
            </div>

            <!-- Modern Management Modal -->
            <Dialog v-model:open="showModal">
                <DialogContent class="sm:max-w-[650px] rounded-[2.5rem] border-slate-100 shadow-[0_32px_64px_-12px_rgba(0,0,0,0.14)] p-0 overflow-hidden bg-white">
                    <div class="bg-primary p-12 text-white relative">
                        <div class="relative z-10">
                            <h2 class="text-3xl font-black tracking-tight mb-2">{{ editingResource ? 'Refine Asset Metadata' : 'Provision Strategic Material' }}</h2>
                            <p class="text-primary-foreground/70 text-[11px] font-bold uppercase tracking-[0.2em] italic">{{ editingResource ? 'Operational adjustment of instructional provision' : 'Integrate high-fidelity curriculum assets into the gateway' }}</p>
                        </div>
                        <div class="absolute right-0 top-0 h-full w-2/5 opacity-[0.08] pointer-events-none transition-transform hover:scale-110">
                            <GraduationCap class="h-64 w-64 -mr-16 -mt-16" />
                        </div>
                    </div>

                    <form @submit.prevent="submit" class="p-10 space-y-8 no-scrollbar max-h-[70vh] overflow-y-auto">
                        <div class="grid gap-3">
                            <Label class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground ml-3">Formal Asset Title</Label>
                            <Input v-model="form.title" placeholder="e.g. Advanced Calculus Instructional PDF" class="h-14 rounded-2xl border-slate-100 bg-slate-50/50 font-black text-sm px-6 focus:bg-white focus:ring-4 focus:ring-primary/5 transition-all outline-none" required />
                        </div>
                        
                        <div class="grid grid-cols-2 gap-8">
                            <div class="grid gap-3">
                                <Label class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground ml-3">Curriculum Learning Area</Label>
                                <select v-model="form.subject_id" class="w-full rounded-2xl border-slate-100 bg-slate-50/50 px-5 h-12 text-[11px] font-black uppercase tracking-widest shadow-sm focus:bg-white focus:ring-4 focus:ring-primary/5 transition-all outline-none border" required>
                                    <option value="" disabled>Select Area</option>
                                    <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
                                </select>
                            </div>
                            <div class="grid gap-3">
                                <Label class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground ml-3">Target Academic Grade</Label>
                                <select v-model="form.grade_level_id" class="w-full rounded-2xl border-slate-100 bg-slate-50/50 px-5 h-12 text-[11px] font-black uppercase tracking-widest shadow-sm focus:bg-white focus:ring-4 focus:ring-primary/5 transition-all outline-none border">
                                    <option value="">Institution-Wide</option>
                                    <option v-for="g in grades" :key="g.id" :value="g.id">{{ g.name }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Asset Delivery Mechanism -->
                        <div class="space-y-6 p-8 rounded-[2rem] bg-slate-50/50 border border-slate-100">
                            <div class="flex items-center justify-between">
                                <Label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Asset Delivery Source</Label>
                                <Badge variant="outline" class="text-[8px] font-black uppercase tracking-widest bg-white text-primary border-primary/20">Operational</Badge>
                            </div>
                            
                            <div v-if="!editingResource" class="grid gap-6">
                                <div class="grid gap-2">
                                    <Label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Binary File Provisions (Max 15MB)</Label>
                                    <div class="relative group">
                                        <input type="file" @change="handleFileChange" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20" />
                                        <div class="bg-white border-2 border-dashed border-slate-200 rounded-2xl p-6 text-center group-hover:bg-primary/[0.03] group-hover:border-primary/30 transition-all">
                                            <div v-if="form.file" class="flex items-center justify-center gap-3 text-primary font-black text-[11px] uppercase tracking-widest">
                                                <CheckCircle2 class="h-5 w-5" /> {{ form.file.name.substring(0, 30) }}...
                                            </div>
                                            <div v-else class="space-y-2">
                                                <div class="h-10 w-10 rounded-full bg-slate-50 flex items-center justify-center mx-auto text-slate-300 group-hover:bg-primary/10 group-hover:text-primary transition-all">
                                                    <Plus class="h-5 w-5" />
                                                </div>
                                                <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest leading-relaxed">Select PDF, Multimedia or Archive Assets</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="relative py-2 flex items-center">
                                    <div class="flex-grow border-t border-slate-200 opacity-50"></div>
                                    <span class="flex-shrink mx-6 text-[10px] font-black text-slate-300 uppercase tracking-widest">Global Linkage</span>
                                    <div class="flex-grow border-t border-slate-200 opacity-50"></div>
                                </div>
                            </div>

                            <div class="grid gap-2">
                                <Label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">External Resource URL</Label>
                                <div class="relative flex items-center">
                                    <LinkIcon class="absolute left-4 h-4 w-4 text-slate-300" />
                                    <Input v-model="form.url" placeholder="https://cdn.example.edu/learning-asset" class="h-12 pl-12 rounded-xl border-slate-100 bg-white text-[11px] font-bold shadow-sm focus:ring-4 focus:ring-primary/5 transition-all outline-none" />
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-3">
                            <Label class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground ml-3">Asset Strategic Context</Label>
                            <Textarea v-model="form.description" placeholder="Formal instructional mapping or content summary..." class="rounded-2xl border-slate-100 bg-slate-50/50 p-6 italic text-[11px] font-medium min-h-[100px] focus:bg-white transition-all shadow-sm " />
                        </div>

                        <div @click="form.is_recommended = !form.is_recommended" class="flex items-center gap-4 p-5 rounded-2xl bg-amber-50/30 border border-amber-100/50 cursor-pointer group transition-all hover:bg-amber-50">
                            <div :class="['h-10 w-10 rounded-xl flex items-center justify-center transition-all', form.is_recommended ? 'bg-amber-500 text-white' : 'bg-white text-slate-300 border border-slate-100 group-hover:border-amber-200']">
                                <Sparkles class="h-5 w-5" />
                            </div>
                            <div class="flex-1">
                                <Label class="text-[11px] font-black uppercase tracking-widest text-slate-700 cursor-pointer m-0">Institutional Recommendation</Label>
                                <p class="text-[10px] text-slate-400 font-medium italic mt-0.5">Feature this asset prominently in the curriculum gateway</p>
                            </div>
                        </div>

                        <DialogFooter class="pt-6">
                            <Button type="submit" :disabled="form.processing" class="w-full bg-primary hover:opacity-90 rounded-2xl font-black text-xs text-white h-16 shadow-2xl shadow-primary/30 tracking-[0.2em] transition-all active:scale-[0.98]">
                                {{ form.processing ? 'GENESYS SYNCING...' : (editingResource ? 'UPDATE CONTENT MATRIX' : 'PUBLISH STRATEGIC ASSET') }}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

        </div>
    </AppLayout>
</template>

<style scoped>
select {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 1rem center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
    padding-right: 2.5rem;
}

.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
