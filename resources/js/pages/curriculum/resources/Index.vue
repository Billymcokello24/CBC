<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import { 
    Sparkles, Plus, Search, Filter, 
    MoreVertical, Eye, Edit2, Trash2, 
    FileText, FileVideo, Image as ImageIcon, 
    Link as LinkIcon, Download, Folder,
    Share2, Info, GraduationCap, ArrowLeft,
    CheckCircle2, X, Music, FileArchive, FileCode,
    LayoutGrid, List, ArrowUpDown, Clock, ExternalLink, MoreHorizontal,
    ChevronLeft, ChevronRight, FolderPlus, BookOpen
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
    DropdownMenuSeparator,
} from "@/components/ui/dropdown-menu";
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from "@/components/ui/dialog";
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    resources: any[];
    subjects: any[];
    grades: any[];
    folders: any[];
}>();

// UI State
const viewMode = ref<'grid' | 'list'>('grid');
const searchQuery = ref('');
const selectedSubject = ref<string | number>('all');
const selectedGradeId = ref<string | number>('all');
const selectedType = ref('all');
const sortBy = ref('newest');
const showFilters = ref(true);
const selectedIds = ref<number[]>([]);
const currentPage = ref(1);
const itemsPerPage = ref(12);
const activeFolderId = ref<number | null>(null);
const showFolderModal = ref(false);

const folderForm = useForm({
    name: '',
    subject_id: '',
    grade_level_id: '',
});

const createFolder = () => {
    folderForm.post(route('curriculum.resources.folders.store'), {
        onSuccess: () => {
            showFolderModal.value = false;
            folderForm.reset();
        },
    });
};

const page = usePage();
const themeColor = computed(() => (page.props.auth as any).school?.theme_color || '#1e40af');
const themeSecondary = computed(() => themeColor.value + '20'); // 12% opacity hex
const themeBorder = computed(() => themeColor.value + '30'); // 18% opacity hex

const breadcrumbs = computed<BreadcrumbItem[]>(() => {
    const base = [
        { title: 'Curriculum', href: '/curriculum' },
        { title: 'Learning Resources', href: activeFolderId.value ? '/curriculum/resources' : '#' },
    ];
    if (activeFolderId.value) {
        const folder = props.folders.find(f => f.id === activeFolderId.value);
        base.push({ title: folder?.name || 'Folder View', href: '#' });
    }
    return base;
});

watch([searchQuery, selectedSubject, selectedGradeId, selectedType, activeFolderId], () => {
    currentPage.value = 1;
});

// Folder Logic
const currentFolders = computed(() => {
    if (activeFolderId.value) return []; // No subfolders for now
    return props.folders.filter(f => {
        const matchesSubject = selectedSubject.value === 'all' || f.subject_id == selectedSubject.value;
        const matchesGrade = selectedGradeId.value === 'all' || f.grade_level_id == selectedGradeId.value;
        return matchesSubject && matchesGrade;
    });
});

// Stats Logic
const stats = computed(() => {
    const byType = props.resources.reduce((acc, r) => {
        acc[r.resource_type] = (acc[r.resource_type] || 0) + 1;
        return acc;
    }, {} as Record<string, number>);

    return {
        total: props.resources.length,
        recommended: props.resources.filter(r => r.is_recommended).length,
        documents: props.resources.filter(r => ['pdf', 'doc', 'document', 'docx'].includes(r.resource_type)).length,
        links: props.resources.filter(r => r.resource_type === 'link').length,
        folders: props.folders.length,
        byType
    };
});

const deleteResource = (id: number) => {
    if (confirm('Permanently remove this academic asset from the curriculum gateway?')) {
        router.delete(route('curriculum.resources.destroy', id), {
            preserveScroll: true
        });
    }
};

const selectAll = computed({
    get: () => filteredResources.value.length > 0 && selectedIds.value.length === filteredResources.value.length,
    set: (value) => {
        if (value) {
            selectedIds.value = filteredResources.value.map(r => r.id);
        } else {
            selectedIds.value = [];
        }
    }
});

const bulkDelete = () => {
    if (selectedIds.value.length === 0) return;
    if (confirm(`Remove ${selectedIds.value.length} selected assets from the gateway?`)) {
        router.post(route('curriculum.resources.bulk-delete'), {
            ids: selectedIds.value
        }, {
            onSuccess: () => {
                selectedIds.value = [];
            }
        });
    }
};

// Filtering Logic
const filteredResources = computed(() => {
    let results = props.resources.filter(r => {
        const matchesSearch = r.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                             r.description?.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesSubject = selectedSubject.value === 'all' || r.subject_id == selectedSubject.value;
        const matchesGrade = selectedGradeId.value === 'all' || r.grade_level_id == selectedGradeId.value;
        const matchesType = selectedType.value === 'all' || r.resource_type === selectedType.value;
        const matchesFolder = activeFolderId.value ? (r.folder_id === activeFolderId.value) : (!r.folder_id);
        
        return matchesSearch && matchesSubject && matchesType && matchesGrade && (searchQuery.value ? true : matchesFolder);
    });

    results.sort((a, b) => {
        if (sortBy.value === 'newest') return new Date(b.created_at).getTime() - new Date(a.created_at).getTime();
        if (sortBy.value === 'oldest') return new Date(a.created_at).getTime() - new Date(b.created_at).getTime();
        if (sortBy.value === 'alphabetical') return a.title.localeCompare(b.title);
        return 0;
    });

    return results;
});

const totalPages = computed(() => Math.ceil(filteredResources.value.length / itemsPerPage.value));
const paginatedResources = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    return filteredResources.value.slice(start, start + itemsPerPage.value);
});

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
    activeFolderId.value = null;
};

const route = (window as any).route;

// End of script

</script>

<template>
    <Head title="Academic Resources Gateway" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div :style="{ '--primary': themeColor }" class="flex h-full flex-1 flex-col gap-6 p-6 md:p-8 animate-in fade-in duration-500 max-w-[1600px] mx-auto w-full">
            <!-- Page Header (Standard Academic Arrangement) -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 shadow-sm border border-primary/5" :style="{ backgroundColor: themeSecondary, borderColor: themeBorder }">
                        <Sparkles class="h-6 w-6" :style="{ color: themeColor }" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 uppercase italic">Learning Resources</h1>
                        <p class="text-sm text-muted-foreground font-medium uppercase tracking-wider text-[9px] italic">Academic Asset Repository • Gateway Reference</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" class="rounded-xl h-10 px-4 font-black text-[10px] uppercase tracking-widest border-slate-100 hover:bg-slate-50">
                        <Download class="mr-2 h-4 w-4 text-slate-400" /> Export Index
                    </Button>
                    
                    <!-- Conditional Primary Action -->
                    <template v-if="!activeFolderId">
                        <Button @click="showFolderModal = true" class="bg-primary hover:opacity-90 rounded-xl h-10 px-6 font-black shadow-lg shadow-primary/20 text-white flex items-center border-0" :style="{ backgroundColor: themeColor }">
                            <Plus class="mr-2 h-4 w-4" /> Create Folder
                        </Button>
                    </template>
                    <template v-else>
                        <Button as-child class="bg-primary hover:opacity-90 rounded-xl h-10 px-6 font-black shadow-lg shadow-primary/20 text-white flex items-center border-0" :style="{ backgroundColor: themeColor }">
                            <Link :href="route('curriculum.resources.create', { folder_id: activeFolderId })">
                                <Plus class="mr-2 h-4 w-4" /> Publish Material
                            </Link>
                        </Button>
                    </template>
                </div>
            </div>

            <!-- Stats Grid (Aligned with Planner) -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border bg-card p-5 shadow-sm transition-all hover:border-primary/20" :style="{ '--hover-border': themeBorder }">
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Total directory</div>
                    <div class="flex items-center justify-between">
                        <div class="text-2xl font-black text-slate-900 leading-none">{{ stats.total }}</div>
                        <div class="h-10 w-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 border border-blue-100/50" :style="{ backgroundColor: themeSecondary, color: themeColor, borderColor: themeBorder }">
                            <FileText class="h-5 w-5" />
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border bg-card p-5 shadow-sm transition-all hover:border-primary/20">
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Folder Index</div>
                    <div class="flex items-center justify-between">
                        <div class="text-2xl font-black text-slate-900 leading-none">{{ stats.folders }}</div>
                        <div class="h-10 w-10 rounded-xl bg-orange-50 flex items-center justify-center text-orange-600 border border-orange-100/50">
                            <Folder class="h-5 w-5" />
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border bg-card p-5 shadow-sm transition-all hover:border-primary/20">
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Quality Control</div>
                    <div class="flex items-center justify-between">
                        <div class="text-2xl font-black text-slate-900 leading-none">{{ stats.recommended }}</div>
                        <div class="h-10 w-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 border border-emerald-100/50">
                            <CheckCircle2 class="h-5 w-5" />
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border bg-card p-5 shadow-sm transition-all hover:border-primary/20">
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Academic Coverage</div>
                    <div class="flex items-center justify-between">
                        <div class="text-2xl font-black text-slate-900 leading-none">{{ subjects.length }}</div>
                        <div class="h-10 w-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600 border border-amber-100/50">
                            <BookOpen class="h-5 w-5" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Toolbar (Aligned with Planner) - Only show if in folder or searching -->
            <div v-if="activeFolderId || searchQuery" class="flex flex-col gap-4 rounded-xl border bg-card p-4 md:flex-row md:items-center shadow-sm">
                <div class="relative flex-1 md:max-w-md group">
                    <Search class="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground transition-colors group-focus-within:text-primary" :style="{ '--tw-text-opacity': 1, color: themeColor }" />
                    <Input 
                        v-model="searchQuery" 
                        placeholder="SEARCH RESOURCE DIRECTORY..." 
                        class="pl-10 h-11 rounded-xl border-slate-200 bg-slate-50/50 focus:bg-white transition-all shadow-none font-black text-[10px] uppercase tracking-widest" 
                    />
                </div>
                <div class="flex items-center gap-2 overflow-x-auto pb-1 md:pb-0">
                    <Button variant="outline" size="sm" @click="showFilters = !showFilters" class="h-10 rounded-xl font-bold uppercase text-[10px] tracking-widest gap-2">
                        <Filter class="h-3.5 w-3.5" />
                        {{ showFilters ? 'Hide Logic' : 'Advanced Analysis' }}
                    </Button>
                    
                    <Button variant="ghost" size="sm" @click="clearFilters" class="h-10 rounded-xl text-xs font-semibold text-muted-foreground hover:text-primary transition-colors">
                        Reset
                    </Button>

                    <div v-if="selectedIds.length > 0" class="h-8 w-px bg-border mx-2 hidden md:block"></div>

                    <Button v-if="selectedIds.length > 0" variant="destructive" size="sm" @click="bulkDelete" class="h-10 rounded-xl font-bold uppercase text-[10px] tracking-widest gap-2 shadow-lg shadow-destructive/20 border-0">
                        <Trash2 class="h-3.5 w-3.5" /> Remove ({{ selectedIds.length }})
                    </Button>

                    <div class="flex p-1 bg-slate-50 border border-slate-100 rounded-xl ml-auto">
                        <button @click="viewMode = 'grid'" :class="['p-2 rounded-lg transition-all', viewMode === 'grid' ? 'bg-white text-primary shadow-sm' : 'text-slate-400 hover:text-slate-600']">
                            <LayoutGrid class="h-4 w-4" />
                        </button>
                        <button @click="viewMode = 'list'" :class="['p-2 rounded-lg transition-all', viewMode === 'list' ? 'bg-white text-primary shadow-sm' : 'text-slate-400 hover:text-slate-600']">
                            <List class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Extended Filter Engine -->
            <div v-if="showFilters" class="grid gap-4 rounded-xl border bg-card p-5 md:grid-cols-4 animate-in slide-in-from-top-2 duration-300">
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Learning Area</label>
                    <select v-model="selectedSubject" class="h-11 w-full rounded-xl border bg-slate-50/50 px-3 text-[10px] font-black uppercase tracking-widest outline-none focus:ring-2 focus:ring-primary/10 transition-all">
                        <option value="all">Every Subject</option>
                        <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Academic Grade</label>
                    <select v-model="selectedGradeId" class="h-11 w-full rounded-xl border bg-slate-50/50 px-3 text-[10px] font-black uppercase tracking-widest outline-none focus:ring-2 focus:ring-primary/10 transition-all">
                        <option value="all">Every Level</option>
                        <option v-for="g in grades" :key="g.id" :value="g.id">{{ g.name }}</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Material Category</label>
                    <select v-model="selectedType" class="h-11 w-full rounded-xl border bg-slate-50/50 px-3 text-[10px] font-black uppercase tracking-widest outline-none focus:ring-2 focus:ring-primary/10 transition-all">
                        <option value="all">All Modalities</option>
                        <option value="video">Interactive Video</option>
                        <option value="pdf">PDF Resource</option>
                        <option value="link">Digital Link</option>
                        <option value="audio">Audio Track</option>
                        <option value="image">Visual Asset</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 ml-1">Ordering Mode</label>
                    <select v-model="sortBy" class="h-11 w-full rounded-xl border bg-slate-50/50 px-3 text-[10px] font-black uppercase tracking-widest outline-none focus:ring-2 focus:ring-primary/10 transition-all">
                        <option value="newest">Newest Added</option>
                        <option value="oldest">Oldest Entry</option>
                        <option value="alphabetical">Alphabetical (A-Z)</option>
                    </select>
                </div>
            </div>

            <!-- Folder Navigation Section -->
            <div v-if="currentFolders.length > 0 && !searchQuery" class="space-y-4">
                <div class="flex items-center justify-between px-2">
                    <h3 class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400">Collective Provisioning (Folders)</h3>
                    <!-- Secondary Folder Creation Button -->
                    <Button variant="ghost" size="sm" @click="showFolderModal = true" class="h-8 rounded-lg text-[9px] font-black uppercase tracking-widest gap-2 bg-slate-50 hover:bg-slate-100" :style="{ color: themeColor }">
                        <FolderPlus class="h-3.5 w-3.5" /> Quick Init
                    </Button>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <div v-for="folder in currentFolders" :key="folder.id" 
                        @click="activeFolderId = folder.id"
                        class="group cursor-pointer rounded-2xl border bg-white p-4 transition-all hover:border-orange-200 hover:shadow-lg hover:shadow-orange-500/5 flex flex-col gap-3"
                    >
                        <div class="flex items-center justify-between">
                            <div class="h-10 w-10 rounded-xl bg-orange-50 flex items-center justify-center text-orange-400 group-hover:scale-110 group-hover:bg-orange-100 transition-all">
                                <Folder class="h-5 w-5 fill-current" />
                            </div>
                            <Badge variant="outline" class="text-[8px] font-bold text-slate-400">{{ folder.resources_count }} assets</Badge>
                        </div>
                        <div class="space-y-0.5">
                            <div class="text-xs font-black text-slate-700 uppercase tracking-tight truncate">{{ folder.name }}</div>
                            <div class="text-[9px] font-bold text-slate-300 uppercase tracking-widest truncate">{{ folder.subject?.name || 'Cross-Curricular' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resource Content Area - Restricted to Folder Context -->
            <div v-if="activeFolderId || searchQuery" class="space-y-6">
                <!-- Folder Navigation Header (if browsing folder) -->
                <div v-if="activeFolderId && !searchQuery" class="flex items-center justify-between p-5 rounded-2xl border animate-in slide-in-from-left-4 duration-500" :style="{ backgroundColor: themeSecondary, borderColor: themeBorder }">
                    <div class="flex items-center gap-4">
                        <Button variant="ghost" size="icon" @click="activeFolderId = null" class="h-10 w-10 rounded-xl hover:bg-white/50" :style="{ color: themeColor }">
                            <ArrowLeft class="h-5 w-5" />
                        </Button>
                        <div>
                            <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-0.5">COLLECTIVE DESIGNATION</div>
                            <div class="text-sm font-black text-slate-900 uppercase tracking-tight flex items-center gap-2">
                                <Folder class="h-4 w-4 fill-current opacity-20" />
                                {{ folders.find(f => f.id === activeFolderId)?.name }}
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <Badge variant="outline" class="text-[9px] font-black uppercase tracking-widest px-3 h-7 bg-white border-slate-100 text-slate-500">
                            {{ filteredResources.length }} OBJECTS IDENTIFIED
                        </Badge>
                    </div>
                </div>
                <!-- GRID VIEW -->
                <div v-if="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 animate-in fade-in zoom-in-95 duration-500">
                    <div v-for="resource in paginatedResources" :key="resource.id" 
                         class="group relative flex flex-col p-6 rounded-2xl bg-white border border-slate-100 shadow-sm hover:shadow-xl hover:border-primary/20 transition-all duration-500 overflow-hidden"
                         :class="{ 'ring-2 ring-primary bg-primary/[0.01]': selectedIds.includes(resource.id) }">
                        
                        <div class="absolute top-4 right-4 z-20">
                            <input 
                                type="checkbox" 
                                :value="resource.id"
                                v-model="selectedIds"
                                class="h-4 w-4 rounded border-slate-200 text-primary focus:ring-primary/20"
                            />
                        </div>

                        <div class="relative z-10 flex items-start justify-between mb-6">
                             <div :class="['h-12 w-12 rounded-xl flex items-center justify-center border transition-all group-hover:rotate-6 shadow-sm', getResourceColor(resource.resource_type)]">
                                <component :is="getResourceIcon(resource.resource_type)" class="h-6 w-6" />
                             </div>
                             <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="ghost" size="icon" class="h-8 w-8 rounded-lg hover:bg-slate-50">
                                            <MoreVertical class="h-4 w-4 text-slate-400" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end" class="w-48 rounded-xl p-2 font-bold uppercase text-[9px] tracking-widest">
                                        <DropdownMenuItem as-child class="rounded-lg">
                                            <Link :href="route('curriculum.resources.show', resource.id)">
                                                <Eye class="mr-2 h-3.5 w-3.5" /> View Asset
                                            </Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem as-child class="rounded-lg">
                                            <Link :href="route('curriculum.resources.edit', resource.id)">
                                                <Edit2 class="mr-2 h-3.5 w-3.5" /> Edit Details
                                            </Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem @click="deleteResource(resource.id)" class="rounded-lg text-destructive focus:bg-destructive/10">
                                            <Trash2 class="mr-2 h-3.5 w-3.5" /> Remove Asset
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                             </div>
                        </div>

                        <div class="relative z-10 flex-1 space-y-3">
                            <div class="flex items-center gap-2">
                                <Badge v-if="resource.is_recommended" class="bg-amber-100 text-amber-700 text-[8px] font-black uppercase tracking-wider h-5 flex items-center border-amber-200/50">Featured</Badge>
                                <span class="text-[9px] font-black text-slate-300 uppercase tracking-[0.2em]">{{ resource.resource_type }}</span>
                            </div>
                            <h3 class="text-sm font-bold text-slate-900 leading-snug group-hover:text-primary transition-colors line-clamp-2 uppercase tracking-tight">{{ resource.title }}</h3>
                            
                            <div class="flex flex-wrap gap-1.5 mt-2">
                                <Badge variant="outline" class="text-[9px] font-bold text-slate-500 bg-slate-50 border-slate-100 uppercase tracking-widest px-2.5 h-6">
                                    {{ resource.subject?.name || 'Academic' }}
                                </Badge>
                                <Badge v-if="resource.grade_level" variant="outline" class="text-[10px] font-black text-primary bg-primary/5 border-primary/10 uppercase tracking-widest px-2.5 h-6">
                                    {{ resource.grade_level.name }}
                                </Badge>
                            </div>
                        </div>

                        <div class="relative z-10 mt-6 pt-4 border-t border-slate-50 flex items-center justify-between">
                            <div class="flex items-center gap-2 text-[10px] text-slate-400 font-bold uppercase tracking-wider">
                                <Clock class="h-3 w-3" /> {{ new Date(resource.created_at).toLocaleDateString() }}
                            </div>
                            
                            <Link :href="route('curriculum.resources.show', resource.id)" class="h-9 px-4 rounded-xl bg-slate-50 text-slate-600 hover:bg-primary hover:text-white transition-all font-bold text-[10px] uppercase tracking-widest flex items-center gap-2 border border-slate-100">
                                <Eye class="h-3.5 w-3.5" /> Inspect
                            </Link>
                        </div>
                    </div>

                    <!-- Modern Add Area -->
                    <Link :href="route('curriculum.resources.create')" class="group relative p-8 rounded-2xl border-2 border-dashed border-slate-100 flex flex-col items-center justify-center gap-4 hover:bg-primary/[0.02] hover:border-primary/20 transition-all duration-500 cursor-pointer min-h-[280px]">
                         <div class="h-14 w-14 rounded-full bg-slate-50 flex items-center justify-center text-slate-300 group-hover:bg-primary/10 group-hover:text-primary transition-all shadow-sm group-hover:scale-110">
                            <Plus class="h-7 w-7" />
                        </div>
                        <div class="text-center space-y-1">
                            <h4 class="text-sm font-bold text-slate-400 group-hover:text-slate-600 transition-all uppercase tracking-tight">Provision Material</h4>
                            <p class="text-[10px] text-slate-300 font-medium uppercase tracking-widest px-4 leading-relaxed">Publish new academic assets</p>
                        </div>
                    </Link>
                </div>

                <!-- LIST VIEW (Standard Table) -->
                <div v-else class="rounded-xl border bg-card shadow-sm overflow-hidden animate-in fade-in slide-in-from-bottom-2 duration-500">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b bg-slate-50/50">
                                    <th class="px-6 py-4 text-left w-10">
                                        <input 
                                            type="checkbox" 
                                            v-model="selectAll"
                                            class="h-4 w-4 rounded border-slate-200 text-primary focus:ring-primary/20"
                                        />
                                    </th>
                                    <th class="px-6 py-4 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Academic Asset</th>
                                    <th class="px-6 py-4 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Context</th>
                                    <th class="px-6 py-4 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Type</th>
                                    <th class="px-6 py-4 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400">Published</th>
                                    <th class="px-6 py-4 text-right text-[10px] font-bold uppercase tracking-widest text-slate-400">Operations</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="resource in paginatedResources" :key="resource.id" 
                                    class="border-b transition-colors hover:bg-slate-50/50 group"
                                    :class="{ 'bg-primary/5': selectedIds.includes(resource.id) }">
                                    <td class="px-6 py-4">
                                        <input 
                                            type="checkbox" 
                                            :value="resource.id"
                                            v-model="selectedIds"
                                            class="h-4 w-4 rounded border-slate-200 text-primary focus:ring-primary/20"
                                        />
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <div :class="['h-10 w-10 rounded-xl flex items-center justify-center border transition-all', getResourceColor(resource.resource_type)]">
                                                <component :is="getResourceIcon(resource.resource_type)" class="h-5 w-5" />
                                            </div>
                                            <div>
                                                <Link :href="route('curriculum.resources.show', resource.id)" class="font-bold text-slate-900 group-hover:text-primary transition-colors text-sm uppercase tracking-tight block">{{ resource.title }}</Link>
                                                <div class="text-[10px] text-slate-400 font-medium line-clamp-1 max-w-sm mt-0.5">{{ resource.description || 'No meta description provided' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col gap-1">
                                            <span class="text-[10px] font-black text-slate-700 uppercase tracking-widest">{{ resource.subject?.name || 'General' }}</span>
                                            <span v-if="resource.grade_level" class="text-[9px] text-primary font-bold uppercase tracking-widest">{{ resource.grade_level.name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <Badge variant="outline" class="text-[9px] font-bold uppercase tracking-widest rounded-lg bg-white border-slate-100 text-slate-500 px-2 h-5">
                                            {{ resource.resource_type }}
                                        </Badge>
                                    </td>
                                    <td class="px-6 py-4 text-[10px] text-slate-500 font-bold uppercase tracking-wider">
                                        {{ new Date(resource.created_at).toLocaleDateString('en-GB') }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <Button variant="outline" size="sm" as-child class="h-8 rounded-lg px-3 border-slate-200 shadow-sm">
                                                <Link :href="route('curriculum.resources.show', resource.id)" class="flex items-center gap-2 text-[9px] font-bold uppercase tracking-widest">
                                                    <Eye class="h-3 w-3" /> View
                                                </Link>
                                            </Button>
                                            
                                            <DropdownMenu>
                                                <DropdownMenuTrigger as-child>
                                                    <Button variant="ghost" size="icon" class="h-8 w-8 rounded-lg"><MoreHorizontal class="h-4 w-4 text-slate-400" /></Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end" class="w-48 rounded-xl p-2 font-bold uppercase text-[9px] tracking-widest">
                                                    <DropdownMenuItem as-child class="rounded-lg">
                                                        <Link :href="route('curriculum.resources.edit', resource.id)">
                                                            <Edit2 class="mr-2 h-3.5 w-3.5" /> Edit Meta
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuSeparator />
                                                    <DropdownMenuItem @click="deleteResource(resource.id)" class="rounded-lg text-destructive">
                                                        <Trash2 class="mr-2 h-3.5 w-3.5" /> Remove
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

                <!-- Pagination Footer -->
                <div class="mt-8 flex flex-col md:flex-row items-center justify-between gap-4 border-t pt-6 border-slate-100">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                        Displaying <span class="text-slate-900">{{ paginatedResources.length }}</span> of <span class="text-slate-900">{{ filteredResources.length }}</span> Academic Assets
                    </div>
                    <div class="flex items-center gap-2">
                        <Button 
                            variant="outline" 
                            size="sm" 
                            :disabled="currentPage === 1"
                            @click="currentPage--"
                            class="h-10 w-10 rounded-xl p-0 border-slate-200 disabled:opacity-30"
                        >
                            <ChevronLeft class="h-4 w-4" />
                        </Button>
                        <div class="flex items-center gap-1">
                            <Button 
                                v-for="page in totalPages" 
                                :key="page"
                                variant="outline"
                                size="sm"
                                @click="currentPage = page"
                                :class="['h-10 w-10 rounded-xl font-bold text-xs border-slate-200 transition-all', currentPage === page ? 'bg-primary text-white border-primary shadow-lg shadow-primary/20' : 'hover:bg-slate-50 text-slate-600']"
                            >
                                {{ page }}
                            </Button>
                        </div>
                        <Button 
                            variant="outline" 
                            size="sm" 
                            :disabled="currentPage === totalPages"
                            @click="currentPage++"
                            class="h-10 w-10 rounded-xl p-0 border-slate-200 disabled:opacity-30"
                        >
                            <ChevronRight class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Enhanced Empty State -->
            <div v-if="(activeFolderId || searchQuery) && filteredResources.length === 0" class="py-32 text-center rounded-3xl bg-slate-50/50 border-2 border-dashed border-slate-100 flex flex-col items-center justify-center animate-in fade-in duration-700">
                 <div class="h-16 w-16 rounded-full bg-white flex items-center justify-center text-slate-200 shadow-sm mb-6">
                    <Sparkles class="h-8 w-8" />
                 </div>
                 <h3 class="text-xl font-bold text-slate-400 tracking-tight mb-2 uppercase">No Assets Found</h3>
                 <p class="text-xs text-slate-400 font-medium max-w-sm px-6 uppercase tracking-widest leading-loose">The current filter matrix returned zero results. Broaden your search or publish new material.</p>
            </div>


        </div>
    </AppLayout>

    <!-- Folder Creation Modal -->
    <Dialog :open="showFolderModal" @update:open="showFolderModal = $event">
        <DialogContent class="sm:max-w-[500px] rounded-[2rem] p-0 overflow-hidden border-0 shadow-2xl">
            <div class="h-2 w-full" :style="{ backgroundColor: themeColor }"></div>
            <DialogHeader class="px-10 pt-10 pb-6">
                <DialogTitle class="text-xl font-black italic uppercase tracking-tight text-slate-900">Initialize Registry Folder</DialogTitle>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Architecting New Organizational Containers</p>
            </DialogHeader>
            <form @submit.prevent="createFolder" class="px-10 pb-10 space-y-6">
                <div class="space-y-2">
                    <label class="text-[9px] font-black uppercase tracking-widest text-slate-400 ml-1">Cognitive Label (Folder Name)</label>
                    <Input 
                        v-model="folderForm.name" 
                        placeholder="e.g., MATHEMATICS REVISION 2024" 
                        class="h-14 rounded-2xl border-slate-100 bg-slate-50/50 px-6 text-[11px] font-bold uppercase tracking-widest focus-visible:ring-primary/20"
                        required
                    />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="text-[9px] font-black uppercase tracking-widest text-slate-400 ml-1">Learning Area</label>
                        <select v-model="folderForm.subject_id" class="h-14 w-full rounded-2xl border border-slate-100 bg-slate-50/50 px-4 text-[10px] font-black uppercase tracking-widest outline-none focus:ring-2 focus:ring-primary/10 transition-all">
                            <option value="">Cross-Curricular</option>
                            <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[9px] font-black uppercase tracking-widest text-slate-400 ml-1">Target Grade</label>
                        <select v-model="folderForm.grade_level_id" class="h-14 w-full rounded-2xl border border-slate-100 bg-slate-50/50 px-4 text-[10px] font-black uppercase tracking-widest outline-none focus:ring-2 focus:ring-primary/10 transition-all">
                            <option value="">Universal Level</option>
                            <option v-for="g in grades" :key="g.id" :value="g.id">{{ g.name }}</option>
                        </select>
                    </div>
                </div>
                <div class="pt-4 flex items-center justify-end gap-4">
                    <Button type="button" variant="ghost" @click="showFolderModal = false" class="h-12 px-6 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-400 border-0">Cancel</Button>
                    <Button type="submit" :disabled="folderForm.processing" class="h-12 px-10 rounded-xl text-white text-[10px] font-black uppercase tracking-widest border-0 shadow-lg" :style="{ backgroundColor: themeColor, boxShadow: `0 10px 15px -3px ${themeColor}4d` }">
                        {{ folderForm.processing ? 'INITIALIZING...' : 'Establish Folder' }}
                    </Button>
                </div>
            </form>
        </DialogContent>
    </Dialog>
</template>

<style scoped>
select {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 1rem center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
    padding-right: 2.5rem;
}
</style>
