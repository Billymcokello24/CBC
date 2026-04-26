<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import {
    Sparkles,
    Plus,
    Search,
    Filter,
    MoreVertical,
    Eye,
    Edit2,
    Trash2,
    FileText,
    FileVideo,
    Image as ImageIcon,
    Link as LinkIcon,
    Download,
    Folder,
    Share2,
    Info,
    GraduationCap,
    ArrowLeft,
    CheckCircle2,
    X,
    Music,
    FileArchive,
    FileCode,
    LayoutGrid,
    List,
    ArrowUpDown,
    Clock,
    ExternalLink,
    MoreHorizontal,
    ChevronLeft,
    ChevronRight,
    FolderPlus,
    BookOpen,
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
} from '@/components/ui/dropdown-menu';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from '@/components/ui/dialog';
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
const themeColor = computed(
    () => (page.props.auth as any).school?.theme_color || '#1e40af',
);
const themeSecondary = computed(() => themeColor.value + '20'); // 12% opacity hex
const themeBorder = computed(() => themeColor.value + '30'); // 18% opacity hex

const breadcrumbs = computed<BreadcrumbItem[]>(() => {
    const base = [
        { title: 'Curriculum', href: '/curriculum' },
        {
            title: 'Learning Resources',
            href: activeFolderId.value ? '/curriculum/resources' : '#',
        },
    ];
    if (activeFolderId.value) {
        const folder = props.folders.find((f) => f.id === activeFolderId.value);
        base.push({ title: folder?.name || 'Folder View', href: '#' });
    }
    return base;
});

watch(
    [
        searchQuery,
        selectedSubject,
        selectedGradeId,
        selectedType,
        activeFolderId,
    ],
    () => {
        currentPage.value = 1;
    },
);

// Folder Logic
const currentFolders = computed(() => {
    if (activeFolderId.value) return []; // No subfolders for now
    return props.folders.filter((f) => {
        const matchesSubject =
            selectedSubject.value === 'all' ||
            f.subject_id == selectedSubject.value;
        const matchesGrade =
            selectedGradeId.value === 'all' ||
            f.grade_level_id == selectedGradeId.value;
        return matchesSubject && matchesGrade;
    });
});

// Stats Logic
const stats = computed(() => {
    const byType = props.resources.reduce(
        (acc, r) => {
            acc[r.resource_type] = (acc[r.resource_type] || 0) + 1;
            return acc;
        },
        {} as Record<string, number>,
    );

    return {
        total: props.resources.length,
        recommended: props.resources.filter((r) => r.is_recommended).length,
        documents: props.resources.filter((r) =>
            ['pdf', 'doc', 'document', 'docx'].includes(r.resource_type),
        ).length,
        links: props.resources.filter((r) => r.resource_type === 'link').length,
        folders: props.folders.length,
        byType,
    };
});

const deleteResource = (id: number) => {
    if (
        confirm(
            'Permanently remove this academic asset from the curriculum gateway?',
        )
    ) {
        router.delete(route('curriculum.resources.destroy', id), {
            preserveScroll: true,
        });
    }
};

const selectAll = computed({
    get: () =>
        filteredResources.value.length > 0 &&
        selectedIds.value.length === filteredResources.value.length,
    set: (value) => {
        if (value) {
            selectedIds.value = filteredResources.value.map((r) => r.id);
        } else {
            selectedIds.value = [];
        }
    },
});

const bulkDelete = () => {
    if (selectedIds.value.length === 0) return;
    if (
        confirm(
            `Remove ${selectedIds.value.length} selected assets from the gateway?`,
        )
    ) {
        router.post(
            route('curriculum.resources.bulk-delete'),
            {
                ids: selectedIds.value,
            },
            {
                onSuccess: () => {
                    selectedIds.value = [];
                },
            },
        );
    }
};

// Filtering Logic
const filteredResources = computed(() => {
    let results = props.resources.filter((r) => {
        const matchesSearch =
            r.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            r.description
                ?.toLowerCase()
                .includes(searchQuery.value.toLowerCase());
        const matchesSubject =
            selectedSubject.value === 'all' ||
            r.subject_id == selectedSubject.value;
        const matchesGrade =
            selectedGradeId.value === 'all' ||
            r.grade_level_id == selectedGradeId.value;
        const matchesType =
            selectedType.value === 'all' ||
            r.resource_type === selectedType.value;
        const matchesFolder = activeFolderId.value
            ? r.folder_id === activeFolderId.value
            : !r.folder_id;

        return (
            matchesSearch &&
            matchesSubject &&
            matchesType &&
            matchesGrade &&
            (searchQuery.value ? true : matchesFolder)
        );
    });

    results.sort((a, b) => {
        if (sortBy.value === 'newest')
            return (
                new Date(b.created_at).getTime() -
                new Date(a.created_at).getTime()
            );
        if (sortBy.value === 'oldest')
            return (
                new Date(a.created_at).getTime() -
                new Date(b.created_at).getTime()
            );
        if (sortBy.value === 'alphabetical')
            return a.title.localeCompare(b.title);
        return 0;
    });

    return results;
});

const totalPages = computed(() =>
    Math.ceil(filteredResources.value.length / itemsPerPage.value),
);
const paginatedResources = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    return filteredResources.value.slice(start, start + itemsPerPage.value);
});

const getResourceIcon = (type: string) => {
    switch (type) {
        case 'video':
            return FileVideo;
        case 'image':
            return ImageIcon;
        case 'audio':
            return Music;
        case 'pdf':
            return FileText;
        case 'doc':
            return FileText;
        case 'link':
            return LinkIcon;
        default:
            return FileText;
    }
};

const getResourceColor = (type: string) => {
    switch (type) {
        case 'video':
            return 'text-red-600 bg-red-50 border-red-100';
        case 'image':
            return 'text-violet-600 bg-violet-50 border-violet-100';
        case 'audio':
            return 'text-pink-600 bg-pink-50 border-pink-100';
        case 'pdf':
            return 'text-orange-600 bg-orange-50 border-orange-100';
        case 'doc':
            return 'text-blue-600 bg-blue-50 border-blue-100';
        case 'link':
            return 'text-emerald-600 bg-emerald-50 border-emerald-100';
        default:
            return 'text-slate-600 bg-slate-50 border-slate-100';
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
        <div
            :style="{ '--primary': themeColor }"
            class="mx-auto flex h-full w-full max-w-[1600px] flex-1 animate-in flex-col gap-6 p-4 pb-20 duration-500 fade-in sm:p-6 sm:pb-10 md:p-8"
        >
            <!-- Page Header -->
            <div
                class="flex flex-col gap-4 px-1 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-3 sm:gap-4">
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-primary/5 bg-primary/10 shadow-sm sm:h-12 sm:w-12"
                        :style="{
                            backgroundColor: themeSecondary,
                            borderColor: themeBorder,
                        }"
                    >
                        <Sparkles
                            class="h-5 w-5 sm:h-6 sm:w-6"
                            :style="{ color: themeColor }"
                        />
                    </div>
                    <div>
                        <h1
                            class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl"
                        >
                            Resources
                        </h1>
                        <p
                            class="text-xs font-bold tracking-tight text-muted-foreground uppercase opacity-80 sm:text-xs"
                        >
                            Academic Asset Repository
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 sm:gap-3">
                    <Button
                        variant="outline"
                        class="hidden h-10 rounded-xl border-slate-100 px-4 text-xs font-bold tracking-tight uppercase transition-all hover:bg-slate-50 sm:flex"
                    >
                        <Download class="mr-2 h-4 w-4 text-slate-400" /> Export
                    </Button>

                    <template v-if="!activeFolderId">
                        <Button
                            @click="showFolderModal = true"
                            class="flex h-10 flex-1 items-center rounded-xl border-0 bg-primary px-4 text-xs font-bold tracking-tight text-white uppercase shadow-lg shadow-primary/20 transition-all hover:opacity-90 sm:flex-none sm:px-6"
                            :style="{ backgroundColor: themeColor }"
                        >
                            <Plus class="mr-2 h-4 w-4" /> New Folder
                        </Button>
                    </template>
                    <template v-else>
                        <Button
                            as-child
                            class="flex h-10 flex-1 items-center rounded-xl border-0 bg-primary px-4 text-xs font-bold tracking-tight text-white uppercase shadow-lg shadow-primary/20 transition-all hover:opacity-90 sm:flex-none sm:px-6"
                            :style="{ backgroundColor: themeColor }"
                        >
                            <Link
                                :href="
                                    route('curriculum.resources.create', {
                                        folder_id: activeFolderId,
                                    })
                                "
                            >
                                <Plus class="mr-2 h-4 w-4" /> Publish
                            </Link>
                        </Button>
                    </template>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 gap-3 px-1 sm:gap-4 lg:grid-cols-4">
                <div
                    v-for="stat in [
                        {
                            label: 'Total',
                            value: stats.total,
                            icon: FileText,
                            color: themeColor,
                            bg: themeSecondary,
                            border: themeBorder,
                        },
                        {
                            label: 'Folders',
                            value: stats.folders,
                            icon: Folder,
                            color: 'text-orange-600',
                            bg: 'bg-orange-50',
                            border: 'border-orange-100/50',
                        },
                        {
                            label: 'Featured',
                            value: stats.recommended,
                            icon: CheckCircle2,
                            color: 'text-emerald-600',
                            bg: 'bg-emerald-50',
                            border: 'border-emerald-100/50',
                        },
                        {
                            label: 'Subjects',
                            value: subjects.length,
                            icon: BookOpen,
                            color: 'text-amber-600',
                            bg: 'bg-amber-50',
                            border: 'border-amber-100/50',
                        },
                    ]"
                    :key="stat.label"
                    class="group rounded-xl border bg-card p-4 shadow-sm transition-all hover:border-primary/20 sm:p-5"
                >
                    <div
                        class="mb-2 text-xs font-bold tracking-tight text-slate-400 uppercase"
                    >
                        {{ stat.label }}
                    </div>
                    <div class="flex items-center justify-between">
                        <div
                            class="text-xl leading-none font-bold text-slate-900 tabular-nums sm:text-2xl"
                        >
                            {{ stat.value }}
                        </div>
                        <div
                            :class="[
                                'flex h-8 w-8 items-center justify-center rounded-xl border transition-transform group-hover:scale-110 sm:h-10 sm:w-10',
                                stat.bg,
                                stat.color,
                                stat.border,
                            ]"
                            :style="
                                stat.label === 'Total'
                                    ? {
                                          backgroundColor: stat.bg,
                                          color: stat.color,
                                          borderColor: stat.border,
                                      }
                                    : {}
                            "
                        >
                            <component
                                :is="stat.icon"
                                class="h-4 w-4 sm:h-5 sm:w-5"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Toolbar -->
            <div
                v-if="activeFolderId || searchQuery"
                class="flex flex-col gap-4 rounded-2xl border bg-card p-4 shadow-sm lg:flex-row lg:items-center"
            >
                <div class="group relative flex-1 lg:max-w-md">
                    <Search
                        class="absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-muted-foreground transition-colors group-focus-within:text-primary"
                        :style="{ color: themeColor }"
                    />
                    <Input
                        v-model="searchQuery"
                        placeholder="SEARCH DIRECTORY..."
                        class="h-11 rounded-xl border-slate-200 bg-slate-50/50 pl-10 text-xs font-bold tracking-tight uppercase shadow-none transition-all focus:bg-white"
                    />
                </div>
                <div class="flex w-full items-center gap-2 lg:w-auto">
                    <Button
                        variant="outline"
                        size="sm"
                        @click="showFilters = !showFilters"
                        class="h-10 flex-1 gap-2 rounded-xl bg-white text-xs font-bold tracking-tight uppercase transition-all lg:flex-none"
                    >
                        <Filter class="h-3.5 w-3.5" /> Logic
                    </Button>

                    <div
                        v-if="selectedIds.length > 0"
                        class="flex flex-1 items-center gap-2 lg:flex-none"
                    >
                        <Button
                            variant="destructive"
                            size="sm"
                            @click="bulkDelete"
                            class="h-10 w-full gap-2 rounded-xl border-0 text-xs font-bold tracking-tight uppercase shadow-lg shadow-destructive/20 transition-all"
                        >
                            <Trash2 class="h-3.5 w-3.5" /> ({{
                                selectedIds.length
                            }})
                        </Button>
                    </div>

                    <div
                        class="flex shrink-0 rounded-xl border border-slate-200/50 bg-slate-100/50 p-1"
                    >
                        <button
                            @click="viewMode = 'grid'"
                            :class="[
                                'rounded-lg p-2 transition-all',
                                viewMode === 'grid'
                                    ? 'bg-white text-primary shadow-sm'
                                    : 'text-slate-400 hover:text-slate-600',
                            ]"
                        >
                            <LayoutGrid class="h-4 w-4" />
                        </button>
                        <button
                            @click="viewMode = 'list'"
                            :class="[
                                'rounded-lg p-2 transition-all',
                                viewMode === 'list'
                                    ? 'bg-white text-primary shadow-sm'
                                    : 'text-slate-400 hover:text-slate-600',
                            ]"
                        >
                            <List class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Extended Filter Engine -->
            <div
                v-if="showFilters"
                class="grid animate-in grid-cols-1 gap-4 rounded-2xl border bg-card p-4 duration-300 slide-in-from-top-2 sm:grid-cols-2 sm:p-5 lg:grid-cols-4"
            >
                <div class="space-y-2">
                    <label
                        class="ml-1 text-xs font-medium tracking-tight text-slate-400 uppercase"
                        >Learning Area</label
                    >
                    <select
                        v-model="selectedSubject"
                        class="h-11 w-full cursor-pointer rounded-xl border bg-slate-50/50 px-3 text-xs font-medium tracking-tight uppercase transition-all outline-none focus:ring-2 focus:ring-primary/10"
                    >
                        <option value="all">Every Subject</option>
                        <option v-for="s in subjects" :key="s.id" :value="s.id">
                            {{ s.name }}
                        </option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label
                        class="ml-1 text-xs font-medium tracking-tight text-slate-400 uppercase"
                        >Academic Grade</label
                    >
                    <select
                        v-model="selectedGradeId"
                        class="h-11 w-full cursor-pointer rounded-xl border bg-slate-50/50 px-3 text-xs font-medium tracking-tight uppercase transition-all outline-none focus:ring-2 focus:ring-primary/10"
                    >
                        <option value="all">Every Level</option>
                        <option v-for="g in grades" :key="g.id" :value="g.id">
                            {{ g.name }}
                        </option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label
                        class="ml-1 text-xs font-medium tracking-tight text-slate-400 uppercase"
                        >Material Category</label
                    >
                    <select
                        v-model="selectedType"
                        class="h-11 w-full cursor-pointer rounded-xl border bg-slate-50/50 px-3 text-xs font-medium tracking-tight uppercase transition-all outline-none focus:ring-2 focus:ring-primary/10"
                    >
                        <option value="all">All Modalities</option>
                        <option value="video">Interactive Video</option>
                        <option value="pdf">PDF Resource</option>
                        <option value="link">Digital Link</option>
                        <option value="audio">Audio Track</option>
                        <option value="image">Visual Asset</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label
                        class="ml-1 text-xs font-medium tracking-tight text-slate-400 uppercase"
                        >Ordering Mode</label
                    >
                    <select
                        v-model="sortBy"
                        class="h-11 w-full cursor-pointer rounded-xl border bg-slate-50/50 px-3 text-xs font-medium tracking-tight uppercase transition-all outline-none focus:ring-2 focus:ring-primary/10"
                    >
                        <option value="newest">Newest Added</option>
                        <option value="oldest">Oldest Entry</option>
                        <option value="alphabetical">Alphabetical</option>
                    </select>
                </div>
            </div>

            <!-- Folder Navigation Section -->
            <div
                v-if="currentFolders.length > 0 && !searchQuery"
                class="space-y-4 px-1"
            >
                <div class="flex items-center justify-between">
                    <h3
                        class="text-xs font-bold tracking-tight text-slate-400 uppercase sm:text-xs"
                    >
                        Registry Folders
                    </h3>
                    <Button
                        variant="ghost"
                        size="sm"
                        @click="showFolderModal = true"
                        class="h-8 gap-2 rounded-lg bg-slate-50 text-xs font-medium tracking-tight uppercase transition-all hover:bg-slate-100"
                        :style="{ color: themeColor }"
                    >
                        <FolderPlus class="h-3.5 w-3.5" /> Initialize
                    </Button>
                </div>
                <div
                    class="grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-4 lg:grid-cols-6"
                >
                    <div
                        v-for="folder in currentFolders"
                        :key="folder.id"
                        @click="activeFolderId = folder.id"
                        class="group flex cursor-pointer flex-col gap-3 rounded-2xl border bg-white p-3 transition-all hover:border-orange-200 hover:shadow-lg hover:shadow-orange-500/5 sm:p-4"
                    >
                        <div class="flex items-center justify-between">
                            <div
                                class="flex h-9 w-9 items-center justify-center rounded-xl border border-orange-100/50 bg-orange-50 text-orange-400 transition-all group-hover:scale-110 group-hover:bg-orange-100 sm:h-10 sm:w-10"
                            >
                                <Folder class="h-5 w-5 fill-current" />
                            </div>
                            <Badge
                                variant="outline"
                                class="shrink-0 border-slate-100 text-[7px] font-bold text-slate-400 sm:text-xs"
                                >{{ folder.resources_count }} assets</Badge
                            >
                        </div>
                        <div class="min-w-0 space-y-0.5">
                            <div
                                class="truncate text-sm font-bold tracking-tight text-slate-700 uppercase sm:text-xs"
                            >
                                {{ folder.name }}
                            </div>
                            <div
                                class="truncate text-xs font-bold tracking-tight text-slate-300 uppercase sm:text-xs"
                            >
                                {{ folder.subject?.name || 'Cross-Curricular' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resource Content Area -->
            <div v-if="activeFolderId || searchQuery" class="space-y-6 px-1">
                <!-- Folder Navigation Header -->
                <div
                    v-if="activeFolderId && !searchQuery"
                    class="flex animate-in items-center justify-between rounded-2xl border p-4 duration-500 slide-in-from-left-4 sm:p-5"
                    :style="{
                        backgroundColor: themeSecondary,
                        borderColor: themeBorder,
                    }"
                >
                    <div class="flex items-center gap-3 sm:gap-4">
                        <Button
                            variant="ghost"
                            size="icon"
                            @click="activeFolderId = null"
                            class="h-9 w-9 rounded-xl hover:bg-white/50 sm:h-10 sm:w-10"
                            :style="{ color: themeColor }"
                        >
                            <ArrowLeft class="h-4 w-4 sm:h-5 sm:w-5" />
                        </Button>
                        <div class="min-w-0">
                            <div
                                class="mb-0.5 text-xs font-bold tracking-tight text-slate-400 uppercase opacity-60 sm:text-xs"
                            >
                                FOLDER CONTEXT
                            </div>
                            <div
                                class="flex items-center gap-2 truncate text-xs font-bold tracking-tight text-slate-900 uppercase sm:text-sm"
                            >
                                <Folder
                                    class="hidden h-3.5 w-3.5 fill-current opacity-20 sm:block"
                                />
                                {{
                                    folders.find((f) => f.id === activeFolderId)
                                        ?.name
                                }}
                            </div>
                        </div>
                    </div>
                    <div class="hidden shrink-0 items-center gap-3 sm:flex">
                        <Badge
                            variant="outline"
                            class="h-7 border-slate-100 bg-white px-3 text-xs font-medium tracking-tight text-slate-500 uppercase"
                        >
                            {{ filteredResources.length }} OBJECTS IDENTIFIED
                        </Badge>
                    </div>
                </div>
                <!-- GRID VIEW -->
                <div
                    v-if="viewMode === 'grid'"
                    class="grid animate-in grid-cols-1 gap-4 duration-500 zoom-in-95 fade-in sm:grid-cols-2 sm:gap-6 lg:grid-cols-3 xl:grid-cols-4"
                >
                    <div
                        v-for="resource in paginatedResources"
                        :key="resource.id"
                        class="group relative flex flex-col overflow-hidden rounded-2xl border border-slate-100 bg-white p-4 shadow-sm transition-all duration-500 hover:border-primary/20 hover:shadow-xl sm:p-6"
                        :class="{
                            'bg-primary/[0.01] ring-2 ring-primary':
                                selectedIds.includes(resource.id),
                        }"
                    >
                        <div class="absolute top-4 right-4 z-20">
                            <input
                                type="checkbox"
                                :value="resource.id"
                                v-model="selectedIds"
                                class="h-4 w-4 rounded-md border-slate-200 text-primary focus:ring-primary/20"
                            />
                        </div>

                        <div
                            class="relative z-10 mb-6 flex items-start justify-between"
                        >
                            <div
                                :class="[
                                    'flex h-10 w-10 items-center justify-center rounded-xl border shadow-sm transition-all group-hover:rotate-6 sm:h-12 sm:w-12',
                                    getResourceColor(resource.resource_type),
                                ]"
                            >
                                <component
                                    :is="
                                        getResourceIcon(resource.resource_type)
                                    "
                                    class="h-5 w-5 sm:h-6 sm:w-6"
                                />
                            </div>
                            <div
                                class="flex items-center gap-1 opacity-0 transition-opacity group-hover:opacity-100"
                            >
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-8 w-8 rounded-lg hover:bg-slate-50"
                                        >
                                            <MoreVertical
                                                class="h-4 w-4 text-slate-400"
                                            />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent
                                        align="end"
                                        class="w-48 rounded-xl p-2 text-xs font-bold tracking-tight uppercase"
                                    >
                                        <DropdownMenuItem
                                            as-child
                                            class="rounded-lg"
                                        >
                                            <Link
                                                :href="
                                                    route(
                                                        'curriculum.resources.show',
                                                        resource.id,
                                                    )
                                                "
                                            >
                                                <Eye class="mr-2 h-3.5 w-3.5" />
                                                View Asset
                                            </Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            as-child
                                            class="rounded-lg"
                                        >
                                            <Link
                                                :href="
                                                    route(
                                                        'curriculum.resources.edit',
                                                        resource.id,
                                                    )
                                                "
                                            >
                                                <Edit2
                                                    class="mr-2 h-3.5 w-3.5"
                                                />
                                                Edit Details
                                            </Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem
                                            @click="deleteResource(resource.id)"
                                            class="rounded-lg text-destructive focus:bg-destructive/10"
                                        >
                                            <Trash2 class="mr-2 h-3.5 w-3.5" />
                                            Remove Asset
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                        </div>

                        <div class="relative z-10 flex-1 space-y-3">
                            <div class="flex items-center gap-2">
                                <Badge
                                    v-if="resource.is_recommended"
                                    class="flex h-5 shrink-0 items-center border-amber-200/50 bg-amber-100 text-xs font-bold tracking-wider text-amber-700 uppercase"
                                    >Featured</Badge
                                >
                                <span
                                    class="truncate text-xs font-bold tracking-tight text-slate-300 uppercase"
                                    >{{ resource.resource_type }}</span
                                >
                            </div>
                            <h3
                                class="line-clamp-2 text-xs leading-snug font-bold tracking-tight text-slate-900 uppercase transition-colors group-hover:text-primary sm:text-sm"
                            >
                                {{ resource.title }}
                            </h3>

                            <div class="mt-2 flex flex-wrap gap-1.5">
                                <Badge
                                    variant="outline"
                                    class="h-6 border-slate-100 bg-slate-50 px-2.5 text-xs font-bold tracking-tight text-slate-500 uppercase sm:text-xs"
                                >
                                    {{ resource.subject?.name || 'Academic' }}
                                </Badge>
                                <Badge
                                    v-if="resource.grade_level"
                                    variant="outline"
                                    class="h-6 border-primary/10 bg-primary/5 px-2.5 text-xs font-bold tracking-tight text-primary uppercase sm:text-xs"
                                >
                                    {{ resource.grade_level.name }}
                                </Badge>
                            </div>
                        </div>

                        <div
                            class="relative z-10 mt-6 flex items-center justify-between border-t border-slate-50 pt-4"
                        >
                            <div
                                class="flex items-center gap-2 text-xs font-bold tracking-wider text-slate-400 uppercase sm:text-xs"
                            >
                                <Clock class="h-3 w-3" />
                                {{
                                    new Date(
                                        resource.created_at,
                                    ).toLocaleDateString()
                                }}
                            </div>

                            <Link
                                :href="
                                    route(
                                        'curriculum.resources.show',
                                        resource.id,
                                    )
                                "
                                class="flex h-8 items-center gap-2 rounded-xl border border-slate-100 bg-slate-50 px-3 text-xs font-bold tracking-tight text-slate-600 uppercase transition-all hover:bg-primary hover:text-white sm:h-9 sm:px-4"
                            >
                                <Eye class="h-3.5 w-3.5" /> Inspect
                            </Link>
                        </div>
                    </div>

                    <!-- Modern Add Area -->
                    <Link
                        :href="route('curriculum.resources.create')"
                        class="group relative flex min-h-[280px] cursor-pointer flex-col items-center justify-center gap-4 rounded-2xl border-2 border-dashed border-slate-100 p-8 transition-all duration-500 hover:border-primary/20 hover:bg-primary/[0.02]"
                    >
                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-full bg-slate-50 text-slate-300 shadow-sm transition-all group-hover:scale-110 group-hover:bg-primary/10 group-hover:text-primary"
                        >
                            <Plus class="h-7 w-7" />
                        </div>
                        <div class="space-y-1 text-center">
                            <h4
                                class="text-sm font-bold tracking-tight text-slate-400 uppercase transition-all group-hover:text-slate-600"
                            >
                                Provision Material
                            </h4>
                            <p
                                class="px-4 text-xs leading-relaxed font-medium tracking-tight text-slate-300 uppercase"
                            >
                                Publish new academic assets
                            </p>
                        </div>
                    </Link>
                </div>

                <!-- LIST VIEW (Standard Table) -->
                <div
                    v-else
                    class="animate-in overflow-hidden rounded-xl border bg-card shadow-sm duration-500 fade-in slide-in-from-bottom-2"
                >
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b bg-slate-50/50">
                                    <th class="w-10 px-6 py-4 text-left">
                                        <input
                                            type="checkbox"
                                            v-model="selectAll"
                                            class="h-4 w-4 rounded border-slate-200 text-primary focus:ring-primary/20"
                                        />
                                    </th>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-bold tracking-tight text-slate-400 uppercase"
                                    >
                                        Academic Asset
                                    </th>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-bold tracking-tight text-slate-400 uppercase"
                                    >
                                        Context
                                    </th>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-bold tracking-tight text-slate-400 uppercase"
                                    >
                                        Type
                                    </th>
                                    <th
                                        class="px-6 py-4 text-left text-xs font-bold tracking-tight text-slate-400 uppercase"
                                    >
                                        Published
                                    </th>
                                    <th
                                        class="px-6 py-4 text-right text-xs font-bold tracking-tight text-slate-400 uppercase"
                                    >
                                        Operations
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="resource in paginatedResources"
                                    :key="resource.id"
                                    class="group border-b transition-colors hover:bg-slate-50/50"
                                    :class="{
                                        'bg-primary/5': selectedIds.includes(
                                            resource.id,
                                        ),
                                    }"
                                >
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
                                            <div
                                                :class="[
                                                    'flex h-10 w-10 items-center justify-center rounded-xl border transition-all',
                                                    getResourceColor(
                                                        resource.resource_type,
                                                    ),
                                                ]"
                                            >
                                                <component
                                                    :is="
                                                        getResourceIcon(
                                                            resource.resource_type,
                                                        )
                                                    "
                                                    class="h-5 w-5"
                                                />
                                            </div>
                                            <div>
                                                <Link
                                                    :href="
                                                        route(
                                                            'curriculum.resources.show',
                                                            resource.id,
                                                        )
                                                    "
                                                    class="block text-sm font-bold tracking-tight text-slate-900 uppercase transition-colors group-hover:text-primary"
                                                    >{{ resource.title }}</Link
                                                >
                                                <div
                                                    class="mt-0.5 line-clamp-1 max-w-sm text-xs font-medium text-slate-400"
                                                >
                                                    {{
                                                        resource.description ||
                                                        'No meta description provided'
                                                    }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col gap-1">
                                            <span
                                                class="text-xs font-bold tracking-tight text-slate-700 uppercase"
                                                >{{
                                                    resource.subject?.name ||
                                                    'General'
                                                }}</span
                                            >
                                            <span
                                                v-if="resource.grade_level"
                                                class="text-xs font-bold tracking-tight text-primary uppercase"
                                                >{{
                                                    resource.grade_level.name
                                                }}</span
                                            >
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <Badge
                                            variant="outline"
                                            class="h-5 rounded-lg border-slate-100 bg-white px-2 text-xs font-bold tracking-tight text-slate-500 uppercase"
                                        >
                                            {{ resource.resource_type }}
                                        </Badge>
                                    </td>
                                    <td
                                        class="px-6 py-4 text-xs font-bold tracking-wider text-slate-500 uppercase"
                                    >
                                        {{
                                            new Date(
                                                resource.created_at,
                                            ).toLocaleDateString('en-GB')
                                        }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div
                                            class="flex items-center justify-end gap-2"
                                        >
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                as-child
                                                class="h-8 rounded-lg border-slate-200 px-3 shadow-sm"
                                            >
                                                <Link
                                                    :href="
                                                        route(
                                                            'curriculum.resources.show',
                                                            resource.id,
                                                        )
                                                    "
                                                    class="flex items-center gap-2 text-xs font-bold tracking-tight uppercase"
                                                >
                                                    <Eye class="h-3 w-3" /> View
                                                </Link>
                                            </Button>

                                            <DropdownMenu>
                                                <DropdownMenuTrigger as-child>
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        class="h-8 w-8 rounded-lg"
                                                        ><MoreHorizontal
                                                            class="h-4 w-4 text-slate-400"
                                                    /></Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent
                                                    align="end"
                                                    class="w-48 rounded-xl p-2 text-xs font-bold tracking-tight uppercase"
                                                >
                                                    <DropdownMenuItem
                                                        as-child
                                                        class="rounded-lg"
                                                    >
                                                        <Link
                                                            :href="
                                                                route(
                                                                    'curriculum.resources.edit',
                                                                    resource.id,
                                                                )
                                                            "
                                                        >
                                                            <Edit2
                                                                class="mr-2 h-3.5 w-3.5"
                                                            />
                                                            Edit Meta
                                                        </Link>
                                                    </DropdownMenuItem>
                                                    <DropdownMenuSeparator />
                                                    <DropdownMenuItem
                                                        @click="
                                                            deleteResource(
                                                                resource.id,
                                                            )
                                                        "
                                                        class="rounded-lg text-destructive"
                                                    >
                                                        <Trash2
                                                            class="mr-2 h-3.5 w-3.5"
                                                        />
                                                        Remove
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
                <div
                    class="mt-8 flex flex-col items-center justify-between gap-4 border-t border-slate-100 pt-6 md:flex-row"
                >
                    <div
                        class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                    >
                        Displaying
                        <span class="text-slate-900">{{
                            paginatedResources.length
                        }}</span>
                        of
                        <span class="text-slate-900">{{
                            filteredResources.length
                        }}</span>
                        Academic Assets
                    </div>
                    <div class="flex items-center gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="currentPage === 1"
                            @click="currentPage--"
                            class="h-10 w-10 rounded-xl border-slate-200 p-0 disabled:opacity-30"
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
                                :class="[
                                    'h-10 w-10 rounded-xl border-slate-200 text-xs font-bold transition-all',
                                    currentPage === page
                                        ? 'border-primary bg-primary text-white shadow-lg shadow-primary/20'
                                        : 'text-slate-600 hover:bg-slate-50',
                                ]"
                            >
                                {{ page }}
                            </Button>
                        </div>
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="currentPage === totalPages"
                            @click="currentPage++"
                            class="h-10 w-10 rounded-xl border-slate-200 p-0 disabled:opacity-30"
                        >
                            <ChevronRight class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Enhanced Empty State -->
            <div
                v-if="
                    (activeFolderId || searchQuery) &&
                    filteredResources.length === 0
                "
                class="flex animate-in flex-col items-center justify-center rounded-3xl border-2 border-dashed border-slate-100 bg-slate-50/50 py-32 text-center duration-700 fade-in"
            >
                <div
                    class="mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-white text-slate-200 shadow-sm"
                >
                    <Sparkles class="h-8 w-8" />
                </div>
                <h3
                    class="mb-2 text-xl font-bold tracking-tight text-slate-400 uppercase"
                >
                    No Assets Found
                </h3>
                <p
                    class="max-w-sm px-6 text-xs leading-loose font-medium tracking-tight text-slate-400 uppercase"
                >
                    The current filter matrix returned zero results. Broaden
                    your search or publish new material.
                </p>
            </div>
        </div>
    </AppLayout>

    <!-- Folder Creation Modal -->
    <Dialog :open="showFolderModal" @update:open="showFolderModal = $event">
        <DialogContent
            class="overflow-hidden rounded-xl border-0 p-0 shadow-lg sm:max-w-[500px]"
        >
            <div
                class="h-2 w-full"
                :style="{ backgroundColor: themeColor }"
            ></div>
            <DialogHeader class="px-10 pt-10 pb-6">
                <DialogTitle
                    class="text-xl font-bold tracking-tight text-slate-900 uppercase"
                    >Initialize Registry Folder</DialogTitle
                >
                <p
                    class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                >
                    Architecting New Organizational Containers
                </p>
            </DialogHeader>
            <form @submit.prevent="createFolder" class="space-y-6 px-10 pb-10">
                <div class="space-y-2">
                    <label
                        class="ml-1 text-xs font-medium tracking-tight text-slate-400 uppercase"
                        >Cognitive Label (Folder Name)</label
                    >
                    <Input
                        v-model="folderForm.name"
                        placeholder="e.g., MATHEMATICS REVISION 2024"
                        class="h-14 rounded-2xl border-slate-100 bg-slate-50/50 px-6 text-sm font-bold tracking-tight uppercase focus-visible:ring-primary/20"
                        required
                    />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label
                            class="ml-1 text-xs font-medium tracking-tight text-slate-400 uppercase"
                            >Learning Area</label
                        >
                        <select
                            v-model="folderForm.subject_id"
                            class="h-14 w-full rounded-2xl border border-slate-100 bg-slate-50/50 px-4 text-xs font-medium tracking-tight uppercase transition-all outline-none focus:ring-2 focus:ring-primary/10"
                        >
                            <option value="">Cross-Curricular</option>
                            <option
                                v-for="s in subjects"
                                :key="s.id"
                                :value="s.id"
                            >
                                {{ s.name }}
                            </option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label
                            class="ml-1 text-xs font-medium tracking-tight text-slate-400 uppercase"
                            >Target Grade</label
                        >
                        <select
                            v-model="folderForm.grade_level_id"
                            class="h-14 w-full rounded-2xl border border-slate-100 bg-slate-50/50 px-4 text-xs font-medium tracking-tight uppercase transition-all outline-none focus:ring-2 focus:ring-primary/10"
                        >
                            <option value="">Universal Level</option>
                            <option
                                v-for="g in grades"
                                :key="g.id"
                                :value="g.id"
                            >
                                {{ g.name }}
                            </option>
                        </select>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-4 pt-4">
                    <Button
                        type="button"
                        variant="ghost"
                        @click="showFolderModal = false"
                        class="h-12 rounded-xl border-0 px-6 text-xs font-medium tracking-tight text-slate-400 uppercase"
                        >Cancel</Button
                    >
                    <Button
                        type="submit"
                        :disabled="folderForm.processing"
                        class="h-12 rounded-xl border-0 px-10 text-xs font-medium tracking-tight text-white uppercase shadow-lg"
                        :style="{
                            backgroundColor: themeColor,
                            boxShadow: `0 10px 15px -3px ${themeColor}4d`,
                        }"
                    >
                        {{
                            folderForm.processing
                                ? 'INITIALIZING...'
                                : 'Establish Folder'
                        }}
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
