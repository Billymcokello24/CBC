<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { 
    BookOpen, FileText, ExternalLink, Download, 
    ChevronRight, User, Search, FolderOpen,
    Video, Music, Image as ImageIcon, Briefcase
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps<{
    children: Array<{
        id: number;
        name: string;
        class: string | null;
        resources: Array<{
            id: number;
            title: string;
            subject: string;
            folder: string | null;
            resource_type: string;
            file_path: string | null;
            url: string | null;
            download_url: string | null;
        }>;
    }>;
    total_resources: number;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Parent Portal', href: '/guardian/portal' },
    { title: 'Learning Resources', href: '#' },
];

const searchQuery = ref('');

const filteredChildren = computed(() => {
    if (!searchQuery.value) return props.children;
    
    const query = searchQuery.value.toLowerCase();
    return props.children.map(child => ({
        ...child,
        resources: child.resources.filter(r => 
            r.title.toLowerCase().includes(query) || 
            r.subject.toLowerCase().includes(query) ||
            (r.folder && r.folder.toLowerCase().includes(query))
        )
    })).filter(child => child.resources.length > 0);
});

const getResourceTypeIcon = (type: string) => {
    switch (type.toLowerCase()) {
        case 'pdf':
        case 'document': return FileText;
        case 'video': return Video;
        case 'audio': return Music;
        case 'image': return ImageIcon;
        case 'link': return ExternalLink;
        default: return FileText;
    }
};

const getResourceTypeColor = (type: string) => {
    switch (type.toLowerCase()) {
        case 'pdf': return 'text-rose-500 bg-rose-50 border-rose-100';
        case 'video': return 'text-purple-500 bg-purple-50 border-purple-100';
        case 'link': return 'text-blue-500 bg-blue-50 border-blue-100';
        default: return 'text-slate-500 bg-slate-50 border-slate-100';
    }
};
</script>

<template>
    <Head title="Learning Resources Registry" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-10 p-6 md:p-8 max-w-[1400px] mx-auto animate-in fade-in duration-500 pb-20">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 border-b border-slate-100 pb-8">
                <div class="flex items-center gap-5">
                    <div class="h-14 w-14 rounded-2xl bg-indigo-600 text-white flex items-center justify-center shadow-xl shadow-indigo-200">
                        <BookOpen class="h-7 w-7" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-black tracking-tighter text-slate-900 uppercase leading-none">Learning Resources</h1>
                        <p class="text-sm font-medium text-slate-500 mt-2">Access textbooks, revision materials, and digital lessons for your children.</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-4">
                    <div class="relative group">
                        <Search class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-indigo-600 transition-colors" />
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            placeholder="Search resources..."
                            class="pl-11 pr-4 py-3 bg-white border border-slate-100 rounded-2xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all w-full md:w-64 shadow-sm"
                        />
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="total_resources === 0" class="rounded-[3rem] bg-white border border-slate-100 p-20 text-center shadow-xl shadow-slate-200/20">
                <div class="h-24 w-24 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-8 text-slate-200">
                    <FolderOpen class="h-12 w-12" />
                </div>
                <h3 class="text-2xl font-black text-slate-900 tracking-tight uppercase mb-3">Resource Registry Empty</h3>
                <p class="text-sm text-slate-400 font-medium max-w-sm mx-auto italic">There are currently no learning materials published for your children's classes.</p>
            </div>

            <!-- Content Organized by Child -->
            <div v-else class="space-y-20">
                <div v-for="child in filteredChildren" :key="child.id" class="space-y-8">
                    <!-- Child Section Header -->
                    <div class="flex items-center justify-between px-2">
                        <div class="flex items-center gap-5">
                            <div class="h-14 w-14 rounded-2xl bg-slate-900 flex items-center justify-center text-white text-xl font-black shadow-2xl">
                                {{ child.name.charAt(0) }}
                            </div>
                            <div>
                                <h2 class="text-2xl font-black text-slate-900 tracking-tight uppercase">{{ child.name }}</h2>
                                <div class="flex items-center gap-3 mt-1">
                                    <Badge class="bg-indigo-50 text-indigo-600 border-indigo-100 rounded-lg px-2 text-[10px] font-black uppercase">{{ child.class || 'No Class' }}</Badge>
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ child.resources.length }} Resources Available</span>
                                </div>
                            </div>
                        </div>
                        <Link :href="`/guardian/children/${child.id}`" class="h-10 w-10 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 hover:bg-slate-900 hover:text-white transition-all shadow-sm">
                            <ChevronRight class="h-5 w-5" />
                        </Link>
                    </div>

                    <!-- Resources Grid for this Child -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <a 
                            v-for="res in child.resources" :key="res.id"
                            :href="res.download_url || res.url || '#'" 
                            target="_blank"
                            class="group bg-white rounded-3xl border border-slate-100 p-6 flex flex-col gap-5 transition-all hover:border-indigo-200 hover:shadow-2xl hover:shadow-indigo-500/10 hover:-translate-y-1 relative overflow-hidden"
                        >
                            <!-- Visual Decoration -->
                            <div class="absolute right-0 top-0 h-24 w-24 bg-indigo-50/50 rounded-bl-[4rem] translate-x-12 -translate-y-12 group-hover:translate-x-8 group-hover:-translate-y-8 transition-transform"></div>

                            <div class="flex items-start justify-between relative z-10">
                                <div :class="['h-14 w-14 rounded-2xl border flex items-center justify-center transition-transform group-hover:scale-110 shadow-sm', getResourceTypeColor(res.resource_type)]">
                                    <component :is="getResourceTypeIcon(res.resource_type)" class="h-7 w-7" />
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <Badge variant="outline" class="rounded-lg text-[9px] font-black uppercase tracking-widest px-2 py-0.5 border-slate-100 bg-slate-50 text-slate-400 truncate max-w-[120px]">
                                        {{ res.subject }}
                                    </Badge>
                                    <Badge v-if="res.folder" class="bg-indigo-600 text-white border-0 text-[8px] font-black px-2 py-0.5 rounded uppercase">
                                        {{ res.folder }}
                                    </Badge>
                                </div>
                            </div>

                            <div class="relative z-10">
                                <h3 class="text-base font-black text-slate-800 tracking-tight leading-snug group-hover:text-indigo-600 transition-colors line-clamp-2">
                                    {{ res.title }}
                                </h3>
                            </div>

                            <div class="mt-auto flex items-center justify-between border-t border-slate-50 pt-5 relative z-10">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-1.5">
                                    {{ res.resource_type }} <span class="h-1 w-1 bg-slate-200 rounded-full"></span> 2.4 MB
                                </span>
                                <div class="h-9 w-9 bg-slate-50 rounded-xl flex items-center justify-center text-slate-300 group-hover:bg-indigo-600 group-hover:text-white transition-all shadow-sm">
                                    <Download v-if="res.file_path" class="h-4 w-4" />
                                    <ExternalLink v-else class="h-4 w-4" />
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
