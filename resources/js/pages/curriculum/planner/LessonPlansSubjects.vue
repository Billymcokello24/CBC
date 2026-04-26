<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    BookOpen,
    ChevronRight,
    School,
    ArrowLeft,
    LayoutGrid,
    Plus,
    BookCopy,
    List,
    Search,
    ArrowUpDown,
    ChevronDown,
    ArrowUp,
    Sparkles,
    Filter,
    MoreHorizontal,
    Table as TableIcon,
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    currentClass: any;
    subjects: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Academic Planner', href: '/curriculum/planner/schemes' },
    { title: 'Lesson Plans', href: '/curriculum/planner/lesson-plans' },
    {
        title: props.currentClass.grade_level.name,
        href: `/curriculum/planner/lesson-plans/grade/${props.currentClass.grade_level_id}`,
    },
    { title: props.currentClass.name, href: '#' },
];

// State
const searchQuery = ref('');
const sortBy = ref('name_asc');
const viewMode = ref<'grid' | 'list'>('grid');
const currentPage = ref(1);
const itemsPerPage = 12;

// Computed
const aggregatePlansCount = computed(() => {
    return props.subjects.reduce(
        (acc, sub) => acc + (sub.lesson_plans_count || 0),
        0,
    );
});

const filteredSubjects = computed(() => {
    let result = [...props.subjects];

    // Search filter
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(
            (s) =>
                s.name.toLowerCase().includes(query) ||
                s.code.toLowerCase().includes(query),
        );
    }

    // Sorting
    result.sort((a, b) => {
        if (sortBy.value === 'name_asc') return a.name.localeCompare(b.name);
        if (sortBy.value === 'name_desc') return b.name.localeCompare(a.name);
        if (sortBy.value === 'plans_high')
            return (b.lesson_plans_count || 0) - (a.lesson_plans_count || 0);
        if (sortBy.value === 'plans_low')
            return (a.lesson_plans_count || 0) - (b.lesson_plans_count || 0);
        if (sortBy.value === 'code_asc') return a.code.localeCompare(b.code);
        return 0;
    });

    return result;
});

const totalPages = computed(() =>
    Math.ceil(filteredSubjects.value.length / itemsPerPage),
);

const paginatedSubjects = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    return filteredSubjects.value.slice(start, start + itemsPerPage);
});

// Watchers
watch([searchQuery, sortBy], () => {
    currentPage.value = 1;
});

// Helpers
const getSubjectStyles = (type: string) => {
    switch (type) {
        case 'core':
            return 'bg-emerald-50 text-emerald-600 border-emerald-100';
        case 'elective':
            return 'bg-amber-50 text-amber-600 border-amber-100';
        default:
            return 'bg-slate-50 text-slate-600 border-slate-100';
    }
};

const clearFilters = () => {
    searchQuery.value = '';
    sortBy.value = 'name_asc';
    currentPage.value = 1;
};
</script>

<template>
    <Head :title="`${currentClass.name} - Subjects`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex h-full w-full max-w-[1600px] flex-1 animate-in flex-col gap-6 p-6 duration-500 fade-in md:p-8"
        >
            <!-- Page Header (Aligned with Students Page) -->
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl border border-primary/5 bg-primary/10 shadow-sm"
                    >
                        <School class="h-6 w-6 text-primary" />
                    </div>
                    <div>
                        <h1
                            class="text-2xl font-bold tracking-tight text-slate-900"
                        >
                            {{ currentClass.name }} Subjects
                        </h1>
                        <p class="text-sm font-medium text-muted-foreground">
                            Select a discipline to manage operational lesson
                            plans
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        as-child
                        class="h-10 rounded-xl px-4 font-semibold"
                    >
                        <Link
                            :href="`/curriculum/planner/lesson-plans/grade/${currentClass.grade_level_id}`"
                        >
                            <ArrowLeft class="mr-2 h-4 w-4" /> Back to Grade
                        </Link>
                    </Button>
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button
                                class="h-10 rounded-xl bg-primary px-5 font-bold shadow-lg shadow-primary/20 hover:opacity-90"
                            >
                                <Plus class="mr-2 h-4 w-4" /> Quick Actions
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent
                            align="end"
                            class="w-48 rounded-xl"
                        >
                            <DropdownMenuItem
                                @click="
                                    router.get(
                                        `/curriculum/planner/lesson-plans/class/${currentClass.id}/subject/${subjects[0]?.id}?showBulk=true`,
                                    )
                                "
                            >
                                <BookCopy class="mr-2 h-4 w-4" /> Bulk Import
                            </DropdownMenuItem>
                            <DropdownMenuItem
                                @click="
                                    router.get(
                                        `/curriculum/planner/lesson-plans/class/${currentClass.id}/subject/${subjects[0]?.id}?addNew=true`,
                                    )
                                "
                            >
                                <Plus class="mr-2 h-4 w-4" /> Create Lesson
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>

            <!-- Stats Grid (Aligned with Students Page) -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    class="rounded-xl border bg-card p-5 shadow-sm transition-all hover:border-primary/20"
                >
                    <div
                        class="mb-2 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                    >
                        Subject Disciplines
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="text-2xl font-bold text-slate-900">
                            {{ subjects.length }}
                        </div>
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600"
                        >
                            <LayoutGrid class="h-4 w-4" />
                        </div>
                    </div>
                </div>
                <div
                    class="rounded-xl border bg-card p-5 shadow-sm transition-all hover:border-primary/20"
                >
                    <div
                        class="mb-2 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                    >
                        Total Lesson Plans
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="text-2xl font-bold text-slate-900">
                            {{ aggregatePlansCount }}
                        </div>
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"
                        >
                            <BookOpen class="h-4 w-4" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter & View Toolbar -->
            <div
                class="flex flex-col gap-4 rounded-xl border bg-card p-4 shadow-sm md:flex-row md:items-center"
            >
                <div class="group relative flex-1 md:max-w-md">
                    <Search
                        class="absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-muted-foreground transition-colors group-focus-within:text-primary"
                    />
                    <Input
                        v-model="searchQuery"
                        placeholder="Filter subjects by name or code..."
                        class="h-11 rounded-xl border-slate-200 bg-slate-50/30 pl-10 shadow-none transition-all focus:bg-white"
                    />
                </div>
                <div
                    class="flex items-center gap-3 overflow-x-auto pb-1 md:pb-0"
                >
                    <div class="flex gap-1 rounded-xl bg-muted p-1">
                        <Button
                            variant="ghost"
                            size="sm"
                            @click="viewMode = 'grid'"
                            class="h-8 rounded-lg px-3 text-xs font-bold uppercase transition-all"
                            :class="
                                viewMode === 'grid'
                                    ? 'bg-white text-primary shadow-sm'
                                    : 'text-muted-foreground'
                            "
                        >
                            <LayoutGrid class="mr-1.5 h-3.5 w-3.5" /> Grid
                        </Button>
                        <Button
                            variant="ghost"
                            size="sm"
                            @click="viewMode = 'list'"
                            class="h-8 rounded-lg px-3 text-xs font-bold uppercase transition-all"
                            :class="
                                viewMode === 'list'
                                    ? 'bg-white text-primary shadow-sm'
                                    : 'text-muted-foreground'
                            "
                        >
                            <List class="mr-1.5 h-3.5 w-3.5" /> List
                        </Button>
                    </div>

                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button
                                variant="outline"
                                size="sm"
                                class="h-10 gap-2 rounded-xl text-xs font-bold tracking-tight uppercase"
                            >
                                <ArrowUpDown class="h-3.5 w-3.5" /> Sort By
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent
                            align="end"
                            class="w-48 rounded-xl"
                        >
                            <DropdownMenuItem
                                @click="sortBy = 'name_asc'"
                                :class="{
                                    'bg-muted font-bold': sortBy === 'name_asc',
                                }"
                                >Name (A-Z)</DropdownMenuItem
                            >
                            <DropdownMenuItem
                                @click="sortBy = 'name_desc'"
                                :class="{
                                    'bg-muted font-bold':
                                        sortBy === 'name_desc',
                                }"
                                >Name (Z-A)</DropdownMenuItem
                            >
                            <DropdownMenuItem
                                @click="sortBy = 'plans_high'"
                                :class="{
                                    'bg-muted font-bold':
                                        sortBy === 'plans_high',
                                }"
                                >Highest Plans</DropdownMenuItem
                            >
                            <DropdownMenuItem
                                @click="sortBy = 'plans_low'"
                                :class="{
                                    'bg-muted font-bold':
                                        sortBy === 'plans_low',
                                }"
                                >Lowest Plans</DropdownMenuItem
                            >
                            <DropdownMenuItem
                                @click="sortBy = 'code_asc'"
                                :class="{
                                    'bg-muted font-bold': sortBy === 'code_asc',
                                }"
                                >Subject Code</DropdownMenuItem
                            >
                        </DropdownMenuContent>
                    </DropdownMenu>

                    <Button
                        variant="ghost"
                        size="sm"
                        @click="clearFilters"
                        class="h-10 rounded-xl text-xs font-semibold text-muted-foreground transition-colors hover:text-primary"
                    >
                        Reset
                    </Button>
                </div>
            </div>

            <!-- Content Area -->
            <div v-if="paginatedSubjects.length > 0">
                <!-- Grid View -->
                <div
                    v-if="viewMode === 'grid'"
                    class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4"
                >
                    <Link
                        v-for="subject in paginatedSubjects"
                        :key="subject.id"
                        :href="`/curriculum/planner/lesson-plans/class/${currentClass.id}/subject/${subject.id}`"
                        class="group relative flex min-h-[220px] flex-col justify-between overflow-hidden rounded-3xl border border-border bg-card p-6 shadow-sm transition-all duration-300 hover:shadow-xl hover:shadow-primary/5"
                    >
                        <div
                            class="absolute inset-x-0 top-0 h-1 bg-primary/10 transition-all group-hover:bg-primary"
                        ></div>

                        <div>
                            <div class="mb-4 flex items-center justify-between">
                                <div
                                    class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl border border-border/10 bg-muted/20 text-muted-foreground transition-all duration-300 group-hover:bg-primary/10 group-hover:text-primary"
                                >
                                    <LayoutGrid class="h-6 w-6" />
                                </div>
                                <Badge
                                    variant="outline"
                                    :class="[
                                        getSubjectStyles(subject.subject_type),
                                        'rounded-lg border px-2.5 py-1 text-xs font-medium tracking-tight uppercase shadow-none transition-colors',
                                    ]"
                                >
                                    {{ subject.subject_type || 'General' }}
                                </Badge>
                            </div>
                            <h3
                                class="line-clamp-2 text-lg font-bold text-slate-900 transition-colors group-hover:text-primary"
                            >
                                {{ subject.name }}
                            </h3>
                            <p
                                class="mt-2 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                            >
                                {{ subject.code }}
                            </p>
                        </div>

                        <div
                            class="mt-8 flex items-center justify-between border-t border-border/40 pt-4"
                        >
                            <div class="flex flex-col">
                                <span
                                    class="text-xs font-bold tracking-tight text-muted-foreground/60 uppercase"
                                    >Managed Plans</span
                                >
                                <span class="text-sm font-bold text-slate-900">
                                    {{ subject.lesson_plans_count || 0 }}
                                </span>
                            </div>
                            <div
                                class="flex h-8 w-8 transform items-center justify-center rounded-full bg-primary text-primary-foreground shadow-lg shadow-primary/10 transition-all group-hover:rotate-[-45deg]"
                            >
                                <ChevronRight class="h-4 w-4" />
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- List View (Table - Aligned with Students Page) -->
                <div
                    v-else
                    class="overflow-hidden rounded-2xl border bg-card shadow-sm"
                >
                    <div class="overflow-x-auto font-medium">
                        <table class="w-full text-left">
                            <thead
                                class="border-b border-border/50 bg-muted/30"
                            >
                                <tr>
                                    <th
                                        class="px-6 py-4 text-xs font-medium tracking-tight text-muted-foreground uppercase"
                                    >
                                        Discipline / Subject
                                    </th>
                                    <th
                                        class="px-6 py-4 text-xs font-medium tracking-tight text-muted-foreground uppercase"
                                    >
                                        Code
                                    </th>
                                    <th
                                        class="px-6 py-4 text-center text-xs font-medium tracking-tight text-muted-foreground uppercase"
                                    >
                                        Type
                                    </th>
                                    <th
                                        class="px-6 py-4 text-center text-xs font-medium tracking-tight text-muted-foreground uppercase"
                                    >
                                        Plan Count
                                    </th>
                                    <th
                                        class="px-6 py-4 text-right text-xs font-medium tracking-tight text-muted-foreground uppercase"
                                    >
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border/40">
                                <tr
                                    v-for="subject in paginatedSubjects"
                                    :key="subject.id"
                                    class="group transition-colors hover:bg-muted/20"
                                >
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <div
                                                class="flex h-9 w-9 items-center justify-center rounded-xl bg-muted/40 text-muted-foreground transition-all group-hover:bg-primary/10 group-hover:text-primary"
                                            >
                                                <BookOpen class="h-4.5 w-4.5" />
                                            </div>
                                            <span
                                                class="font-bold text-slate-900 transition-colors group-hover:text-primary"
                                                >{{ subject.name }}</span
                                            >
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <Badge
                                            variant="outline"
                                            class="bg-slate-50 text-xs font-bold text-slate-600"
                                            >{{ subject.code }}</Badge
                                        >
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span
                                            class="rounded-full px-3 py-1 text-xs font-bold tracking-wider uppercase"
                                            :class="
                                                getSubjectStyles(
                                                    subject.subject_type,
                                                )
                                            "
                                        >
                                            {{
                                                subject.subject_type ||
                                                'General'
                                            }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-sm font-bold text-emerald-600"
                                        >
                                            {{
                                                subject.lesson_plans_count || 0
                                            }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <Link
                                            :href="`/curriculum/planner/lesson-plans/class/${currentClass.id}/subject/${subject.id}`"
                                            class="inline-flex h-9 items-center justify-center rounded-xl bg-primary px-4 text-xs font-bold text-white shadow-md shadow-primary/10 transition-all hover:opacity-90 active:scale-95"
                                        >
                                            Manage Plans
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination Footer -->
                <div
                    class="mt-8 flex flex-col gap-4 border-t border-border/40 pt-6 sm:flex-row sm:items-center sm:justify-between"
                >
                    <p class="text-sm font-medium text-muted-foreground">
                        Showing
                        <span class="font-bold text-foreground">{{
                            paginatedSubjects.length
                        }}</span>
                        of
                        <span class="font-bold text-foreground">{{
                            filteredSubjects.length
                        }}</span>
                        disciplines
                    </p>
                    <div
                        v-if="totalPages > 1"
                        class="flex items-center gap-1.5"
                    >
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="currentPage === 1"
                            @click="currentPage--"
                            class="h-9 w-9 rounded-xl border-border bg-card p-0 transition-all hover:bg-muted"
                        >
                            <ArrowLeft class="h-4 w-4" />
                        </Button>
                        <div class="flex items-center gap-1">
                            <Button
                                v-for="page in totalPages"
                                :key="page"
                                variant="outline"
                                size="sm"
                                @click="currentPage = page"
                                class="h-9 min-w-[36px] rounded-xl border-border text-xs font-bold transition-all"
                                :class="
                                    currentPage === page
                                        ? 'border-primary bg-primary text-white shadow-lg shadow-primary/20'
                                        : 'bg-card hover:bg-muted'
                                "
                            >
                                {{ page }}
                            </Button>
                        </div>
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="currentPage === totalPages"
                            @click="currentPage++"
                            class="h-9 w-9 rounded-xl border-border bg-card p-0 transition-all hover:bg-muted"
                        >
                            <ChevronRight class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-else
                class="flex h-[450px] flex-col items-center justify-center rounded-3xl border border-dashed border-border/60 bg-card p-12 text-center shadow-inner"
            >
                <div
                    class="mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-muted/40"
                >
                    <Search
                        class="h-10 w-10 text-muted-foreground opacity-20"
                    />
                </div>
                <h3 class="text-xl font-bold tracking-tight text-slate-900">
                    No Subjects Found
                </h3>
                <p
                    class="mt-2 max-w-[360px] text-sm leading-relaxed font-medium text-muted-foreground"
                >
                    We couldn't find any disciplines matching your current
                    filters. Try refining your search query or reset the
                    toolbar.
                </p>
                <Button
                    variant="outline"
                    @click="clearFilters"
                    class="mt-8 h-11 rounded-xl border-border px-8 text-xs font-bold tracking-tight uppercase shadow-sm transition-all hover:bg-muted"
                >
                    Reset Filter Matrix
                </Button>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
