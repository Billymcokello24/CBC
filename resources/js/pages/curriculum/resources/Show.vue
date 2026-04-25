<template>
    <Head :title="resource.title + ' | Academic Architecture'" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div :style="{ '--primary': themeColor }" class="flex h-full flex-1 flex-col gap-6 p-6 font-sans max-w-[1600px] mx-auto w-full">
            
            <!-- Page Header (Aligned with Lesson Plan Header Arrangement) -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl border border-primary/10 shadow-sm transition-transform duration-700" :style="{ backgroundColor: themeSecondary, borderColor: themeBorder, color: themeColor }">
                        <component :is="isVideo ? Play : isImage ? ImageIcon : isAudio ? Music : FileText" class="h-6 w-6" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">{{ resource.title }}</h1>
                        <p class="text-muted-foreground">
                             {{ resource.subject?.name || 'General Discipline' }} • {{ resource.grade_level?.name || 'Universal Level' }} • asset #{{ resource.id.toString().padStart(4, '0') }}
                        </p>
                    </div>
                </div>

                <div class="flex gap-2">
                    <Button variant="outline" as-child>
                        <Link :href="route('curriculum.resources.index')">
                            <ArrowLeft class="mr-2 h-4 w-4" /> Back to Vault
                        </Link>
                    </Button>
                    <Button variant="outline" as-child>
                        <Link :href="route('curriculum.resources.edit', resource.id)" class="flex items-center gap-2 text-slate-600 hover:text-primary">
                            <Edit2 class="h-4 w-4" /> Edit Metadata
                        </Link>
                    </Button>
                    <Button variant="outline" @click="deleteResource(resource.id)" class="text-rose-600 hover:bg-rose-50 border-rose-100">
                        <Trash2 class="h-4 w-4" />
                    </Button>
                    <a v-if="resource.file_path" :href="getFileUrl(resource.file_path)" download 
                        class="h-10 px-6 rounded-xl text-white shadow-lg flex items-center gap-3 text-[10px] font-black uppercase tracking-widest hover:opacity-90 transition-all active:scale-95 border-0"
                        :style="{ backgroundColor: themeColor, boxShadow: `0 10px 15px -3px ${themeColor}4d` }"
                    >
                        <Download class="h-4 w-4" /> Export Asset
                    </a>
                </div>
            </div>

            <!-- Stats Synchronizer (Mini Stats Board) -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border bg-card p-4">
                    <div class="text-sm text-muted-foreground font-medium">Entry Date</div>
                    <div class="mt-2 text-xl font-bold">{{ new Date(resource.created_at).toLocaleDateString() }}</div>
                </div>
                <div class="rounded-xl border bg-card p-4">
                    <div class="text-sm text-muted-foreground font-medium">Author / Source</div>
                    <div class="mt-2 text-xl font-bold truncate">{{ resource.author || 'Institutional' }}</div>
                </div>
                <div class="rounded-xl border bg-card p-4">
                    <div class="text-[10px] text-muted-foreground font-black uppercase tracking-widest">Asset Modality</div>
                    <div class="mt-2 text-xl font-bold flex items-center gap-2" :style="{ color: themeColor }">
                        <component :is="getResourceIcon(resource.resource_type)" class="h-5 w-5" />
                        <span class="capitalize">{{ resource.resource_type }} Media</span>
                    </div>
                </div>
                <div class="rounded-xl border bg-card p-4 flex items-center justify-between">
                    <div>
                        <div class="text-sm text-muted-foreground font-medium">Status</div>
                        <div class="mt-2 text-xl font-bold text-emerald-600">Active</div>
                    </div>
                    <div class="h-10 w-10 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600 border border-emerald-100">
                        <CheckCircle2 class="h-5 w-5" />
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                <!-- Strategic Sidebar (Aligned Left 3 Columns) -->
                <div class="lg:col-span-3 space-y-6">
                    <!-- Academic Context Card -->
                    <div class="rounded-xl border bg-card p-5 space-y-6">
                        <div class="space-y-2">
                            <h4 class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Curriculum Context</h4>
                            <div class="space-y-1">
                                <div class="text-sm font-semibold text-slate-900">{{ resource.subject?.name }}</div>
                                <div class="text-xs text-muted-foreground italic">Strategic Resource Block</div>
                            </div>
                        </div>

                        <Separator />

                        <div class="space-y-2">
                            <h4 class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Academic Depth</h4>
                            <div class="flex items-baseline gap-2">
                                <span class="text-3xl font-bold text-primary">{{ resource.grade_level?.name || 'ALL' }}</span>
                                <span class="text-xs text-muted-foreground font-medium uppercase">Grade Level</span>
                            </div>
                        </div>

                        <Separator />

                        <div class="space-y-3">
                             <h4 class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Technical Specs</h4>
                             <div class="space-y-2">
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-muted-foreground">Year</span>
                                    <span class="font-bold text-slate-700">{{ resource.year_published || '2024' }}</span>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-muted-foreground">Format</span>
                                    <span class="font-bold text-slate-700 capitalize">{{ resource.resource_type }}</span>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-muted-foreground">ISBN</span>
                                    <span class="font-bold text-slate-700 uppercase">{{ resource.isbn || 'INTERNAL' }}</span>
                                </div>
                             </div>
                        </div>
                    </div>

                    <div v-if="resource.is_recommended" class="rounded-xl border bg-primary text-primary-foreground p-6 space-y-4 shadow-lg shadow-primary/10 relative overflow-hidden group">
                        <Sparkles class="absolute -right-4 -bottom-4 h-24 w-24 opacity-10 group-hover:scale-110 transition-transform duration-700" />
                        <h4 class="font-semibold relative z-10 flex items-center gap-2">
                            <Sparkles class="h-4 w-4" /> Endorsed Asset
                        </h4>
                        <p class="text-xs leading-relaxed text-primary-foreground/90 relative z-10 italic">This material has been strategically verified for CBC curriculum compliance.</p>
                    </div>

                    <!-- Related Resources Sidebar (Standard Academic List) -->
                    <div class="rounded-xl border bg-card p-5 space-y-4 shadow-sm">
                        <h4 class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Related Provisions</h4>
                        <div class="space-y-4">
                            <template v-if="relatedResources.length > 0">
                                <Link v-for="related in relatedResources" :key="related.id" :href="route('curriculum.resources.show', related.id)" class="block space-y-1 hover:group">
                                    <div class="text-sm font-semibold text-slate-900 line-clamp-1 hover:text-primary transition-colors">{{ related.title }}</div>
                                    <div class="text-[10px] text-muted-foreground uppercase tracking-widest">{{ related.resource_type }} • {{ related.subject?.name }}</div>
                                </Link>
                            </template>
                            <div v-else class="text-xs text-muted-foreground italic py-2 text-center">No alternative assets indexed for this block.</div>
                        </div>
                        <Button as-child variant="ghost" class="w-full text-[10px] font-bold uppercase tracking-widest h-9 hover:bg-slate-50">
                             <Link href="/curriculum/resources">Explore Vault</Link>
                        </Button>
                    </div>
                </div>

                <!-- Main Asset Theater (9 Columns) -->
                <div class="lg:col-span-9 space-y-6 animate-in slide-in-from-bottom-4 duration-700">
                    <!-- The Theater Container -->
                    <div class="rounded-xl border bg-slate-950 shadow-2xl overflow-hidden relative aspect-video flex items-center justify-center group ring-4 ring-slate-950/20">
                        
                        <!-- Premium Video Player -->
                        <template v-if="isVideo">
                            <iframe v-if="videoEmbedUrl" :src="videoEmbedUrl" class="w-full h-full" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                            <video v-else controls class="w-full h-full max-h-full">
                                <source :src="getFileUrl(resource.file_path)" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        </template>

                        <!-- High-Fidelity Image Viewer -->
                        <template v-else-if="isImage">
                            <img :src="getFileUrl(resource.file_path)" class="max-w-full max-h-full object-contain p-4 group-hover:scale-[1.01] transition-transform duration-1000" />
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end justify-end p-6">
                                <Button variant="secondary" size="icon" class="rounded-lg h-10 w-10 bg-white/10 backdrop-blur-md border border-white/20 text-white hover:bg-white/20"><Maximize2 class="h-5 w-5" /></Button>
                            </div>
                        </template>

                        <!-- Audio Station Interface -->
                        <template v-else-if="isAudio">
                            <div class="flex flex-col items-center gap-12 p-8 relative w-full h-full bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-primary/10 via-transparent to-transparent">
                                <div class="relative">
                                    <div class="absolute -inset-8 bg-primary/20 rounded-full blur-3xl animate-pulse"></div>
                                    <div class="h-32 w-32 rounded-full bg-slate-900 border-4 border-slate-800 flex items-center justify-center relative shadow-2xl">
                                         <Music class="h-12 w-12 text-primary" />
                                         <div class="absolute -inset-2 bg-gradient-to-tr from-primary to-transparent opacity-30 rounded-full"></div>
                                    </div>
                                </div>
                                <audio controls class="w-full max-w-lg h-11 rounded-full shadow-2xl relative z-10">
                                    <source :src="getFileUrl(resource.file_path)">
                                    Your browser does not support the audio element.
                                </audio>
                            </div>
                        </template>

                        <!-- Document Insight Frame -->
                        <template v-else-if="isPdf">
                             <div class="w-full h-full bg-slate-800 flex flex-col">
                                <div class="bg-slate-900 px-6 py-3 border-b border-slate-800 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <FileText class="h-4 w-4 text-orange-500" />
                                        <span class="text-[10px] font-bold text-white uppercase tracking-widest">Scientific Portfolio Inspection</span>
                                    </div>
                                    <a :href="getFileUrl(resource.file_path)" target="_blank" class="text-[10px] font-bold text-primary uppercase tracking-widest hover:underline">Full System View</a>
                                </div>
                                <embed :src="getFileUrl(resource.file_path)" type="application/pdf" class="w-full flex-1" />
                             </div>
                        </template>

                        <!-- Fallback Diagnostic -->
                        <div v-else class="flex flex-col items-center justify-center text-center p-12 space-y-6 bg-slate-900/50">
                             <div class="h-16 w-16 rounded-xl bg-white/5 flex items-center justify-center border border-white/10 text-white/20">
                                <AlertCircle class="h-8 w-8 text-slate-600" />
                             </div>
                             <div class="space-y-2">
                                <h3 class="text-xl font-bold text-white tracking-tight uppercase">Visualization Limited</h3>
                                <p class="text-slate-400 text-xs max-w-sm font-medium italic opacity-60">This asset format requires local processing or secure download for full pedagogical inspection.</p>
                             </div>
                             <a v-if="resource.file_path" :href="getFileUrl(resource.file_path)" download class="h-12 px-8 rounded-xl bg-white text-slate-900 font-bold text-[11px] uppercase tracking-widest shadow-xl hover:bg-slate-50 transition-all flex items-center gap-3 active:scale-95">
                                <Download class="h-4 w-4" /> Grab Archive
                             </a>
                        </div>
                    </div>

                    <!-- Information Anatomy Section (Aligned with Lesson Plan Goals) -->
                    <div class="rounded-xl border bg-card">
                        <div class="border-b px-6 py-4 flex items-center justify-between">
                            <h3 class="font-semibold flex items-center gap-2 text-slate-900">
                                <Sparkles class="h-4 w-4 text-primary" />
                                Pedagogical Summary
                            </h3>
                            <Button variant="ghost" size="icon" class="h-8 w-8 text-slate-300 hover:text-primary"><Share2 class="h-4 w-4" /></Button>
                        </div>
                        <div class="p-6 space-y-8">
                            <div class="bg-muted/30 p-5 rounded-xl border italic text-slate-700 leading-relaxed">
                                "{{ resource.description || 'Global Reference Material Provisioned for Optimized Learning Areas.' }}"
                            </div>
                            
                            <div class="grid sm:grid-cols-2 gap-8 pt-2">
                                <div class="space-y-3">
                                    <h4 class="text-xs font-bold text-muted-foreground uppercase tracking-wider">Source Intelligence</h4>
                                    <div class="grid grid-cols-1 gap-2">
                                        <div class="flex items-center gap-3 text-sm">
                                            <div class="h-8 w-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400 border border-slate-100"><User class="h-4 w-4" /></div>
                                            <div>
                                                <div class="text-[10px] text-muted-foreground font-bold uppercase tracking-widest">Creator</div>
                                                <div class="font-semibold text-slate-800">{{ resource.author || 'Institutional Source' }}</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3 text-sm">
                                            <div class="h-8 w-8 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400 border border-slate-100"><GraduationCap class="h-4 w-4" /></div>
                                            <div>
                                                <div class="text-[10px] text-muted-foreground font-bold uppercase tracking-widest">Publisher</div>
                                                <div class="font-semibold text-slate-800">{{ resource.publisher || 'Curriculum Board' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-3">
                                    <h4 class="text-xs font-bold text-muted-foreground uppercase tracking-wider">System Metadata</h4>
                                    <div class="flex flex-col gap-3">
                                        <div class="flex items-center gap-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                                            <Clock class="h-4 w-4 text-slate-300" /> Index Update: {{ new Date(resource.updated_at).toLocaleDateString() }}
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <Badge variant="outline" class="text-[9px] uppercase tracking-widest">{{ resource.resource_type }}</Badge>
                                            <Badge v-if="resource.is_recommended" variant="secondary" class="bg-amber-100 text-amber-700 text-[9px] uppercase tracking-widest">Featured</Badge>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { 
    Play, FileText, Image as ImageIcon, 
    Music, Share2, Info, GraduationCap,
    Maximize2, Calendar, User, BookOpen,
    Sparkles, Clock, AlertCircle, CheckCircle2,
    Edit2, Trash2, ArrowLeft, Download,
    Printer, BookCopy, Users
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    resource: any;
    relatedResources: any[];
}>();

const page = usePage();
const themeColor = computed(() => (page.props.auth as any).school?.theme_color || '#1e40af');
const themeSecondary = computed(() => themeColor.value + '20');
const themeBorder = computed(() => themeColor.value + '30');

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Academic Planner', href: '/curriculum' },
    { title: 'Learning Resources', href: '/curriculum/resources' },
    { title: 'Asset Detail', href: '#' },
]);

const videoEmbedUrl = computed(() => {
    if (!props.resource.url) return null;
    const url = props.resource.url;
    let videoId = '';
    if (url.includes('youtube.com') || url.includes('youtu.be')) {
        const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
        const match = url.match(regExp);
        videoId = (match && match[2].length === 11) ? match[2] : '';
        return videoId ? `https://www.youtube.com/embed/${videoId}` : null;
    }
    if (url.includes('vimeo.com')) {
        const regExp = /(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/(?:[^\/]*)\/videos\/|album\/(?:\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)/;
        const match = url.match(regExp);
        videoId = match ? match[1] : '';
        return videoId ? `https://player.vimeo.com/video/${videoId}` : null;
    }
    return null;
});

const isVideo = computed(() => props.resource.resource_type === 'video' || videoEmbedUrl.value);
const isImage = computed(() => props.resource.resource_type === 'image');
const isAudio = computed(() => props.resource.resource_type === 'audio');
const isPdf = computed(() => props.resource.resource_type === 'pdf' || (props.resource.file_path && props.resource.file_path.endsWith('.pdf')));

const getFileUrl = (path: string) => `/storage/${path}`;

const deleteResource = (id: number) => {
    if (confirm('Permanently remove this academic asset from the repository?')) {
        router.delete(route('curriculum.resources.destroy', id), {
            onSuccess: () => router.visit(route('curriculum.resources.index'))
        });
    }
};

const getResourceIcon = (type: string) => {
    switch (type) {
        case 'video': return Play;
        case 'image': return ImageIcon;
        case 'audio': return Music;
        case 'pdf': return FileText;
        default: return FileText;
    }
};

const route = (window as any).route;
</script>

<style scoped>
.aspect-video iframe {
    width: 100%;
    height: 100%;
}
</style>
