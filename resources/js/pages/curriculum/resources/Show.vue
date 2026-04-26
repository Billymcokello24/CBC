<template>
    <Head :title="resource.title + ' | Academic Architecture'" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            :style="{ '--primary': themeColor }"
            class="mx-auto flex h-full w-full max-w-[1600px] flex-1 flex-col gap-6 p-6 font-sans"
        >
            <!-- Page Header (Aligned with Lesson Plan Header Arrangement) -->
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl border border-primary/10 shadow-sm transition-transform duration-700"
                        :style="{
                            backgroundColor: themeSecondary,
                            borderColor: themeBorder,
                            color: themeColor,
                        }"
                    >
                        <component
                            :is="
                                isVideo
                                    ? Play
                                    : isImage
                                      ? ImageIcon
                                      : isAudio
                                        ? Music
                                        : FileText
                            "
                            class="h-6 w-6"
                        />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">
                            {{ resource.title }}
                        </h1>
                        <p class="text-muted-foreground">
                            {{
                                resource.subject?.name || 'General Discipline'
                            }}
                            •
                            {{
                                resource.grade_level?.name || 'Universal Level'
                            }}
                            • asset #{{
                                resource.id.toString().padStart(4, '0')
                            }}
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
                        <Link
                            :href="
                                route('curriculum.resources.edit', resource.id)
                            "
                            class="flex items-center gap-2 text-slate-600 hover:text-primary"
                        >
                            <Edit2 class="h-4 w-4" /> Edit Metadata
                        </Link>
                    </Button>
                    <Button
                        variant="outline"
                        @click="deleteResource(resource.id)"
                        class="border-rose-100 text-rose-600 hover:bg-rose-50"
                    >
                        <Trash2 class="h-4 w-4" />
                    </Button>
                    <a
                        v-if="resource.file_path"
                        :href="getFileUrl(resource.file_path)"
                        download
                        class="flex h-10 items-center gap-3 rounded-xl border-0 px-6 text-xs font-medium tracking-tight text-white uppercase shadow-lg transition-all hover:opacity-90 active:scale-95"
                        :style="{
                            backgroundColor: themeColor,
                            boxShadow: `0 10px 15px -3px ${themeColor}4d`,
                        }"
                    >
                        <Download class="h-4 w-4" /> Export Asset
                    </a>
                </div>
            </div>

            <!-- Stats Synchronizer (Mini Stats Board) -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border bg-card p-4">
                    <div class="text-sm font-medium text-muted-foreground">
                        Entry Date
                    </div>
                    <div class="mt-2 text-xl font-bold">
                        {{ new Date(resource.created_at).toLocaleDateString() }}
                    </div>
                </div>
                <div class="rounded-xl border bg-card p-4">
                    <div class="text-sm font-medium text-muted-foreground">
                        Author / Source
                    </div>
                    <div class="mt-2 truncate text-xl font-bold">
                        {{ resource.author || 'Institutional' }}
                    </div>
                </div>
                <div class="rounded-xl border bg-card p-4">
                    <div
                        class="text-xs font-medium tracking-tight text-muted-foreground uppercase"
                    >
                        Asset Modality
                    </div>
                    <div
                        class="mt-2 flex items-center gap-2 text-xl font-bold"
                        :style="{ color: themeColor }"
                    >
                        <component
                            :is="getResourceIcon(resource.resource_type)"
                            class="h-5 w-5"
                        />
                        <span class="capitalize"
                            >{{ resource.resource_type }} Media</span
                        >
                    </div>
                </div>
                <div
                    class="flex items-center justify-between rounded-xl border bg-card p-4"
                >
                    <div>
                        <div class="text-sm font-medium text-muted-foreground">
                            Status
                        </div>
                        <div class="mt-2 text-xl font-bold text-emerald-600">
                            Active
                        </div>
                    </div>
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-lg border border-emerald-100 bg-emerald-50 text-emerald-600"
                    >
                        <CheckCircle2 class="h-5 w-5" />
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
                <!-- Strategic Sidebar (Aligned Left 3 Columns) -->
                <div class="space-y-6 lg:col-span-3">
                    <!-- Academic Context Card -->
                    <div class="space-y-6 rounded-xl border bg-card p-5">
                        <div class="space-y-2">
                            <h4
                                class="text-xs font-bold tracking-tight text-muted-foreground uppercase"
                            >
                                Curriculum Context
                            </h4>
                            <div class="space-y-1">
                                <div
                                    class="text-sm font-semibold text-slate-900"
                                >
                                    {{ resource.subject?.name }}
                                </div>
                                <div class="text-xs text-muted-foreground">
                                    Strategic Resource Block
                                </div>
                            </div>
                        </div>

                        <Separator />

                        <div class="space-y-2">
                            <h4
                                class="text-xs font-bold tracking-tight text-muted-foreground uppercase"
                            >
                                Academic Depth
                            </h4>
                            <div class="flex items-baseline gap-2">
                                <span class="text-3xl font-bold text-primary">{{
                                    resource.grade_level?.name || 'ALL'
                                }}</span>
                                <span
                                    class="text-xs font-medium text-muted-foreground uppercase"
                                    >Grade Level</span
                                >
                            </div>
                        </div>

                        <Separator />

                        <div class="space-y-3">
                            <h4
                                class="text-xs font-bold tracking-tight text-muted-foreground uppercase"
                            >
                                Technical Specs
                            </h4>
                            <div class="space-y-2">
                                <div
                                    class="flex items-center justify-between text-sm"
                                >
                                    <span class="text-muted-foreground"
                                        >Year</span
                                    >
                                    <span class="font-bold text-slate-700">{{
                                        resource.year_published || '2024'
                                    }}</span>
                                </div>
                                <div
                                    class="flex items-center justify-between text-sm"
                                >
                                    <span class="text-muted-foreground"
                                        >Format</span
                                    >
                                    <span
                                        class="font-bold text-slate-700 capitalize"
                                        >{{ resource.resource_type }}</span
                                    >
                                </div>
                                <div
                                    class="flex items-center justify-between text-sm"
                                >
                                    <span class="text-muted-foreground"
                                        >ISBN</span
                                    >
                                    <span
                                        class="font-bold text-slate-700 uppercase"
                                        >{{ resource.isbn || 'INTERNAL' }}</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="resource.is_recommended"
                        class="group relative space-y-4 overflow-hidden rounded-xl border bg-primary p-6 text-primary-foreground shadow-lg shadow-primary/10"
                    >
                        <Sparkles
                            class="absolute -right-4 -bottom-4 h-24 w-24 opacity-10 transition-transform duration-700 group-hover:scale-110"
                        />
                        <h4
                            class="relative z-10 flex items-center gap-2 font-semibold"
                        >
                            <Sparkles class="h-4 w-4" /> Endorsed Asset
                        </h4>
                        <p
                            class="relative z-10 text-xs leading-relaxed text-primary-foreground/90"
                        >
                            This material has been strategically verified for
                            CBC curriculum compliance.
                        </p>
                    </div>

                    <!-- Related Resources Sidebar (Standard Academic List) -->
                    <div
                        class="space-y-4 rounded-xl border bg-card p-5 shadow-sm"
                    >
                        <h4
                            class="text-xs font-bold tracking-tight text-muted-foreground uppercase"
                        >
                            Related Provisions
                        </h4>
                        <div class="space-y-4">
                            <template v-if="relatedResources.length > 0">
                                <Link
                                    v-for="related in relatedResources"
                                    :key="related.id"
                                    :href="
                                        route(
                                            'curriculum.resources.show',
                                            related.id,
                                        )
                                    "
                                    class="hover:group block space-y-1"
                                >
                                    <div
                                        class="line-clamp-1 text-sm font-semibold text-slate-900 transition-colors hover:text-primary"
                                    >
                                        {{ related.title }}
                                    </div>
                                    <div
                                        class="text-xs tracking-tight text-muted-foreground uppercase"
                                    >
                                        {{ related.resource_type }} •
                                        {{ related.subject?.name }}
                                    </div>
                                </Link>
                            </template>
                            <div
                                v-else
                                class="py-2 text-center text-xs text-muted-foreground"
                            >
                                No alternative assets indexed for this block.
                            </div>
                        </div>
                        <Button
                            as-child
                            variant="ghost"
                            class="h-9 w-full text-xs font-bold tracking-tight uppercase hover:bg-slate-50"
                        >
                            <Link href="/curriculum/resources"
                                >Explore Vault</Link
                            >
                        </Button>
                    </div>
                </div>

                <!-- Main Asset Theater (9 Columns) -->
                <div
                    class="animate-in space-y-6 duration-700 slide-in-from-bottom-4 lg:col-span-9"
                >
                    <!-- The Theater Container -->
                    <div
                        class="group relative flex aspect-video items-center justify-center overflow-hidden rounded-xl border bg-slate-950 shadow-lg ring-4 ring-slate-950/20"
                    >
                        <!-- Premium Video Player -->
                        <template v-if="isVideo">
                            <iframe
                                v-if="videoEmbedUrl"
                                :src="videoEmbedUrl"
                                class="h-full w-full"
                                frameborder="0"
                                allow="
                                    accelerometer;
                                    autoplay;
                                    clipboard-write;
                                    encrypted-media;
                                    gyroscope;
                                    picture-in-picture;
                                "
                                allowfullscreen
                            ></iframe>
                            <video
                                v-else
                                controls
                                class="h-full max-h-full w-full"
                            >
                                <source
                                    :src="getFileUrl(resource.file_path)"
                                    type="video/mp4"
                                />
                                Your browser does not support the video tag.
                            </video>
                        </template>

                        <!-- High-Fidelity Image Viewer -->
                        <template v-else-if="isImage">
                            <img
                                :src="getFileUrl(resource.file_path)"
                                class="max-h-full max-w-full object-contain p-4 transition-transform duration-1000 group-hover:scale-[1.01]"
                            />
                            <div
                                class="absolute inset-0 flex items-end justify-end bg-gradient-to-t from-slate-950/40 to-transparent p-6 opacity-0 transition-opacity group-hover:opacity-100"
                            >
                                <Button
                                    variant="secondary"
                                    size="icon"
                                    class="h-10 w-10 rounded-lg border border-white/20 bg-white/10 text-white backdrop-blur-md hover:bg-white/20"
                                    ><Maximize2 class="h-5 w-5"
                                /></Button>
                            </div>
                        </template>

                        <!-- Audio Station Interface -->
                        <template v-else-if="isAudio">
                            <div
                                class="relative flex h-full w-full flex-col items-center gap-12 bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-primary/10 via-transparent to-transparent p-8"
                            >
                                <div class="relative">
                                    <div
                                        class="absolute -inset-8 animate-pulse rounded-full bg-primary/20 blur-3xl"
                                    ></div>
                                    <div
                                        class="relative flex h-32 w-32 items-center justify-center rounded-full border-4 border-slate-800 bg-slate-900 shadow-lg"
                                    >
                                        <Music class="h-12 w-12 text-primary" />
                                        <div
                                            class="absolute -inset-2 rounded-full bg-gradient-to-tr from-primary to-transparent opacity-30"
                                        ></div>
                                    </div>
                                </div>
                                <audio
                                    controls
                                    class="relative z-10 h-11 w-full max-w-lg rounded-full shadow-lg"
                                >
                                    <source
                                        :src="getFileUrl(resource.file_path)"
                                    />
                                    Your browser does not support the audio
                                    element.
                                </audio>
                            </div>
                        </template>

                        <!-- Document Insight Frame -->
                        <template v-else-if="isPdf">
                            <div
                                class="flex h-full w-full flex-col bg-slate-800"
                            >
                                <div
                                    class="flex items-center justify-between border-b border-slate-800 bg-slate-900 px-6 py-3"
                                >
                                    <div class="flex items-center gap-3">
                                        <FileText
                                            class="h-4 w-4 text-orange-500"
                                        />
                                        <span
                                            class="text-xs font-bold tracking-tight text-white uppercase"
                                            >Scientific Portfolio
                                            Inspection</span
                                        >
                                    </div>
                                    <a
                                        :href="getFileUrl(resource.file_path)"
                                        target="_blank"
                                        class="text-xs font-bold tracking-tight text-primary uppercase hover:underline"
                                        >Full System View</a
                                    >
                                </div>
                                <embed
                                    :src="getFileUrl(resource.file_path)"
                                    type="application/pdf"
                                    class="w-full flex-1"
                                />
                            </div>
                        </template>

                        <!-- Fallback Diagnostic -->
                        <div
                            v-else
                            class="flex flex-col items-center justify-center space-y-6 bg-slate-900/50 p-12 text-center"
                        >
                            <div
                                class="flex h-16 w-16 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-white/20"
                            >
                                <AlertCircle class="h-8 w-8 text-slate-600" />
                            </div>
                            <div class="space-y-2">
                                <h3
                                    class="text-xl font-bold tracking-tight text-white uppercase"
                                >
                                    Visualization Limited
                                </h3>
                                <p
                                    class="max-w-sm text-xs font-medium text-muted-foreground text-slate-400 opacity-80"
                                >
                                    This asset format requires local processing
                                    or secure download for full pedagogical
                                    inspection.
                                </p>
                            </div>
                            <a
                                v-if="resource.file_path"
                                :href="getFileUrl(resource.file_path)"
                                download
                                class="flex h-12 items-center gap-3 rounded-xl bg-white px-8 text-sm font-bold tracking-tight text-slate-900 uppercase shadow-xl transition-all hover:bg-slate-50 active:scale-95"
                            >
                                <Download class="h-4 w-4" /> Grab Archive
                            </a>
                        </div>
                    </div>

                    <!-- Information Anatomy Section (Aligned with Lesson Plan Goals) -->
                    <div class="rounded-xl border bg-card">
                        <div
                            class="flex items-center justify-between border-b px-6 py-4"
                        >
                            <h3
                                class="flex items-center gap-2 font-semibold text-slate-900"
                            >
                                <Sparkles class="h-4 w-4 text-primary" />
                                Pedagogical Summary
                            </h3>
                            <Button
                                variant="ghost"
                                size="icon"
                                class="h-8 w-8 text-slate-300 hover:text-primary"
                                ><Share2 class="h-4 w-4"
                            /></Button>
                        </div>
                        <div class="space-y-8 p-6">
                            <div
                                class="rounded-xl border bg-muted/30 p-5 leading-relaxed text-slate-700"
                            >
                                "{{
                                    resource.description ||
                                    'Global Reference Material Provisioned for Optimized Learning Areas.'
                                }}"
                            </div>

                            <div class="grid gap-8 pt-2 sm:grid-cols-2">
                                <div class="space-y-3">
                                    <h4
                                        class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                    >
                                        Source Intelligence
                                    </h4>
                                    <div class="grid grid-cols-1 gap-2">
                                        <div
                                            class="flex items-center gap-3 text-sm"
                                        >
                                            <div
                                                class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-100 bg-slate-50 text-slate-400"
                                            >
                                                <User class="h-4 w-4" />
                                            </div>
                                            <div>
                                                <div
                                                    class="text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                                >
                                                    Creator
                                                </div>
                                                <div
                                                    class="font-semibold text-slate-800"
                                                >
                                                    {{
                                                        resource.author ||
                                                        'Institutional Source'
                                                    }}
                                                </div>
                                            </div>
                                        </div>
                                        <div
                                            class="flex items-center gap-3 text-sm"
                                        >
                                            <div
                                                class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-100 bg-slate-50 text-slate-400"
                                            >
                                                <GraduationCap
                                                    class="h-4 w-4"
                                                />
                                            </div>
                                            <div>
                                                <div
                                                    class="text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                                >
                                                    Publisher
                                                </div>
                                                <div
                                                    class="font-semibold text-slate-800"
                                                >
                                                    {{
                                                        resource.publisher ||
                                                        'Curriculum Board'
                                                    }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-3">
                                    <h4
                                        class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                    >
                                        System Metadata
                                    </h4>
                                    <div class="flex flex-col gap-3">
                                        <div
                                            class="flex items-center gap-3 text-xs font-bold tracking-tight text-slate-500 uppercase"
                                        >
                                            <Clock
                                                class="h-4 w-4 text-slate-300"
                                            />
                                            Index Update:
                                            {{
                                                new Date(
                                                    resource.updated_at,
                                                ).toLocaleDateString()
                                            }}
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <Badge
                                                variant="outline"
                                                class="text-xs tracking-tight uppercase"
                                                >{{
                                                    resource.resource_type
                                                }}</Badge
                                            >
                                            <Badge
                                                v-if="resource.is_recommended"
                                                variant="secondary"
                                                class="bg-amber-100 text-xs tracking-tight text-amber-700 uppercase"
                                                >Featured</Badge
                                            >
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
    Play,
    FileText,
    Image as ImageIcon,
    Music,
    Share2,
    Info,
    GraduationCap,
    Maximize2,
    Calendar,
    User,
    BookOpen,
    Sparkles,
    Clock,
    AlertCircle,
    CheckCircle2,
    Edit2,
    Trash2,
    ArrowLeft,
    Download,
    Printer,
    BookCopy,
    Users,
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
const themeColor = computed(
    () => (page.props.auth as any).school?.theme_color || '#1e40af',
);
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
        const regExp =
            /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
        const match = url.match(regExp);
        videoId = match && match[2].length === 11 ? match[2] : '';
        return videoId ? `https://www.youtube.com/embed/${videoId}` : null;
    }
    if (url.includes('vimeo.com')) {
        const regExp =
            /(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/(?:[^\/]*)\/videos\/|album\/(?:\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)/;
        const match = url.match(regExp);
        videoId = match ? match[1] : '';
        return videoId ? `https://player.vimeo.com/video/${videoId}` : null;
    }
    return null;
});

const isVideo = computed(
    () => props.resource.resource_type === 'video' || videoEmbedUrl.value,
);
const isImage = computed(() => props.resource.resource_type === 'image');
const isAudio = computed(() => props.resource.resource_type === 'audio');
const isPdf = computed(
    () =>
        props.resource.resource_type === 'pdf' ||
        (props.resource.file_path && props.resource.file_path.endsWith('.pdf')),
);

const getFileUrl = (path: string) => `/storage/${path}`;

const deleteResource = (id: number) => {
    if (
        confirm('Permanently remove this academic asset from the repository?')
    ) {
        router.delete(route('curriculum.resources.destroy', id), {
            onSuccess: () => router.visit(route('curriculum.resources.index')),
        });
    }
};

const getResourceIcon = (type: string) => {
    switch (type) {
        case 'video':
            return Play;
        case 'image':
            return ImageIcon;
        case 'audio':
            return Music;
        case 'pdf':
            return FileText;
        default:
            return FileText;
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
