<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import {
    BookOpen,
    FileText,
    ExternalLink,
    Download,
    ChevronRight,
    User,
    Search,
    FolderOpen,
    Video,
    Music,
    Image as ImageIcon,
    Briefcase,
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
    return props.children
        .map((child) => ({
            ...child,
            resources: child.resources.filter(
                (r) =>
                    r.title.toLowerCase().includes(query) ||
                    r.subject.toLowerCase().includes(query) ||
                    (r.folder && r.folder.toLowerCase().includes(query)),
            ),
        }))
        .filter((child) => child.resources.length > 0);
});

const getResourceTypeIcon = (type: string) => {
    switch (type.toLowerCase()) {
        case 'pdf':
        case 'document':
            return FileText;
        case 'video':
            return Video;
        case 'audio':
            return Music;
        case 'image':
            return ImageIcon;
        case 'link':
            return ExternalLink;
        default:
            return FileText;
    }
};

const getResourceTypeColor = (type: string) => {
    switch (type.toLowerCase()) {
        case 'pdf':
            return 'text-rose-500 bg-rose-50 border-rose-100';
        case 'video':
            return 'text-purple-500 bg-purple-50 border-purple-100';
        case 'link':
            return 'text-blue-500 bg-blue-50 border-blue-100';
        default:
            return 'text-slate-500 bg-slate-50 border-slate-100';
    }
};
</script>

<template>
    <Head title="Learning Resources Registry" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex h-full max-w-[1400px] flex-1 animate-in flex-col gap-10 p-6 pb-20 duration-500 fade-in md:p-8"
        >
            <!-- Header -->
            <div
                class="flex flex-col justify-between gap-6 border-b border-slate-100 pb-8 md:flex-row md:items-center"
            >
                <div class="flex items-center gap-5">
                    <div
                        class="flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-600 text-white shadow-xl shadow-indigo-200"
                    >
                        <BookOpen class="h-7 w-7" />
                    </div>
                    <div>
                        <h1
                            class="text-3xl leading-none font-bold tracking-tighter text-slate-900 uppercase"
                        >
                            Learning Resources
                        </h1>
                        <p class="mt-2 text-sm font-medium text-slate-500">
                            Access textbooks, revision materials, and digital
                            lessons for your children.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="group relative">
                        <Search
                            class="absolute top-1/2 left-4 h-4 w-4 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-indigo-600"
                        />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search resources..."
                            class="w-full rounded-2xl border border-slate-100 bg-white py-3 pr-4 pl-11 text-sm font-medium shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/10 focus:outline-none md:w-64"
                        />
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-if="total_resources === 0"
                class="rounded-2xl border border-slate-100 bg-white p-20 text-center shadow-xl shadow-slate-200/20"
            >
                <div
                    class="mx-auto mb-8 flex h-24 w-24 items-center justify-center rounded-full bg-slate-50 text-slate-200"
                >
                    <FolderOpen class="h-12 w-12" />
                </div>
                <h3
                    class="mb-3 text-2xl font-bold tracking-tight text-slate-900 uppercase"
                >
                    Resource Registry Empty
                </h3>
                <p class="mx-auto max-w-sm text-sm font-medium text-slate-400">
                    There are currently no learning materials published for your
                    children's classes.
                </p>
            </div>

            <!-- Content Organized by Child -->
            <div v-else class="space-y-20">
                <div
                    v-for="child in filteredChildren"
                    :key="child.id"
                    class="space-y-8"
                >
                    <!-- Child Section Header -->
                    <div class="flex items-center justify-between px-2">
                        <div class="flex items-center gap-5">
                            <div
                                class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-900 text-xl font-bold text-white shadow-lg"
                            >
                                {{ child.name.charAt(0) }}
                            </div>
                            <div>
                                <h2
                                    class="text-2xl font-bold tracking-tight text-slate-900 uppercase"
                                >
                                    {{ child.name }}
                                </h2>
                                <div class="mt-1 flex items-center gap-3">
                                    <Badge
                                        class="rounded-lg border-indigo-100 bg-indigo-50 px-2 text-xs font-bold text-indigo-600 uppercase"
                                        >{{ child.class || 'No Class' }}</Badge
                                    >
                                    <span
                                        class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                                        >{{ child.resources.length }} Resources
                                        Available</span
                                    >
                                </div>
                            </div>
                        </div>
                        <Link
                            :href="`/guardian/children/${child.id}`"
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-50 text-slate-400 shadow-sm transition-all hover:bg-slate-900 hover:text-white"
                        >
                            <ChevronRight class="h-5 w-5" />
                        </Link>
                    </div>

                    <!-- Resources Grid for this Child -->
                    <div
                        class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
                    >
                        <a
                            v-for="res in child.resources"
                            :key="res.id"
                            :href="res.download_url || res.url || '#'"
                            target="_blank"
                            class="group relative flex flex-col gap-5 overflow-hidden rounded-3xl border border-slate-100 bg-white p-6 transition-all hover:-translate-y-1 hover:border-indigo-200 hover:shadow-lg hover:shadow-indigo-500/10"
                        >
                            <!-- Visual Decoration -->
                            <div
                                class="absolute top-0 right-0 h-24 w-24 translate-x-12 -translate-y-12 rounded-bl-[4rem] bg-indigo-50/50 transition-transform group-hover:translate-x-8 group-hover:-translate-y-8"
                            ></div>

                            <div
                                class="relative z-10 flex items-start justify-between"
                            >
                                <div
                                    :class="[
                                        'flex h-14 w-14 items-center justify-center rounded-2xl border shadow-sm transition-transform group-hover:scale-110',
                                        getResourceTypeColor(res.resource_type),
                                    ]"
                                >
                                    <component
                                        :is="
                                            getResourceTypeIcon(
                                                res.resource_type,
                                            )
                                        "
                                        class="h-7 w-7"
                                    />
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <Badge
                                        variant="outline"
                                        class="max-w-[120px] truncate rounded-lg border-slate-100 bg-slate-50 px-2 py-0.5 text-xs font-medium tracking-tight text-slate-400 uppercase"
                                    >
                                        {{ res.subject }}
                                    </Badge>
                                    <Badge
                                        v-if="res.folder"
                                        class="rounded border-0 bg-indigo-600 px-2 py-0.5 text-xs font-bold text-white uppercase"
                                    >
                                        {{ res.folder }}
                                    </Badge>
                                </div>
                            </div>

                            <div class="relative z-10">
                                <h3
                                    class="line-clamp-2 text-base leading-snug font-bold tracking-tight text-slate-800 transition-colors group-hover:text-indigo-600"
                                >
                                    {{ res.title }}
                                </h3>
                            </div>

                            <div
                                class="relative z-10 mt-auto flex items-center justify-between border-t border-slate-50 pt-5"
                            >
                                <span
                                    class="flex items-center gap-1.5 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                >
                                    {{ res.resource_type }}
                                    <span
                                        class="h-1 w-1 rounded-full bg-slate-200"
                                    ></span>
                                    2.4 MB
                                </span>
                                <div
                                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-50 text-slate-300 shadow-sm transition-all group-hover:bg-indigo-600 group-hover:text-white"
                                >
                                    <Download
                                        v-if="res.file_path"
                                        class="h-4 w-4"
                                    />
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
