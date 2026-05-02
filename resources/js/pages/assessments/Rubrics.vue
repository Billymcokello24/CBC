<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    BookMarked,
    Plus,
    Search,
    Filter,
    MoreHorizontal,
    Eye,
    Edit,
    Trash2,
    FileCode,
    Beaker,
} from 'lucide-vue-next';
import { ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    rubrics?: {
        data: Array<any>;
        links: Array<any>;
        meta: any;
    };
}>();

// Safe fallback for props
const rubrics = props.rubrics ?? { data: [], links: [], meta: {} };

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Assessments', href: '/assessments' },
    { title: 'Rubrics', href: '/assessments/rubrics' },
];

const searchQuery = ref('');
</script>

<template>
    <Head title="Rubrics" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex h-full max-w-[1600px] flex-1 animate-in flex-col space-y-8 p-4 pb-20 duration-700 fade-in slide-in-from-bottom-4 sm:p-6 sm:pb-32 md:p-8">
            <div class="flex flex-col gap-6 px-1 md:flex-row md:items-center md:justify-between">
                <div class="space-y-1">
                    <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">Rubrics & Grading Schemes</h1>
                    <p class="text-xs text-muted-foreground">Manage how scores translate to grades and feedback</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <Button variant="outline" class="h-10 rounded-lg border-border bg-card px-4 text-xs font-semibold hover:bg-muted" as-child>
                        <Link href="/assessments">Back to Assessments</Link>
                    </Button>
                    <Button as-child class="h-10 rounded-lg bg-primary px-6 text-xs font-semibold text-white shadow-sm hover:opacity-90 transition-all">
                        <Link href="/assessments/rubrics/create">
                            <Plus class="mr-2 h-4 w-4" />Create Rubric
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                <div class="flex h-12 items-center justify-between border-b border-border/50 bg-muted/5 px-6">
                    <div class="flex items-center gap-2">
                        <BookMarked class="h-4 w-4 text-primary" />
                        <span class="text-xs font-semibold text-foreground tracking-tight">Rubrics Registry</span>
                    </div>
                </div>
                <div class="p-6">
                    <div class="flex flex-col items-center gap-4 md:flex-row">
                        <div class="relative w-full flex-1 md:max-w-md">
                            <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground/40" />
                            <Input v-model="searchQuery" placeholder="Search rubrics..." class="h-10 rounded-lg border-border bg-muted/10 pl-10 pr-4 text-sm focus:bg-background" />
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-if="rubrics.data.length === 0"
                class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-primary/20 bg-primary/5 py-20"
            >
                <div
                    class="mb-6 flex h-24 w-24 items-center justify-center rounded-2xl bg-white shadow-xl shadow-primary/10"
                >
                    <BookMarked class="h-12 w-12 text-primary/40" />
                </div>
                <h3 class="text-xl font-bold text-foreground">
                    No grading rubrics defined
                </h3>
                <p
                    class="mt-2 max-w-xs text-center text-[11px] font-bold uppercase tracking-widest text-muted-foreground opacity-60"
                >
                    Create your first rubric to start using standardized grading
                    criteria.
                </p>
                <Button
                    as-child
                    class="mt-8 rounded-xl bg-primary px-10 py-6 text-[10px] font-black uppercase tracking-widest shadow-xl shadow-primary/20 hover:opacity-90"
                >
                    <Link href="/assessments/rubrics/create">
                        <Plus class="mr-2 h-5 w-5" />Get Started
                    </Link>
                </Button>
            </div>

            <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="rubric in rubrics.data"
                    :key="rubric.id"
                    class="group relative overflow-hidden rounded-2xl border border-border/50 bg-card p-8 shadow-sm transition-all duration-300 hover:shadow-xl hover:border-primary/20"
                >
                    <div class="relative">
                        <div class="mb-6 flex items-start justify-between">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/5 text-primary border border-primary/10"
                            >
                                <FileCode class="h-6 w-6" />
                            </div>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-10 w-10 rounded-full hover:bg-muted"
                                        ><MoreHorizontal
                                            class="h-5 w-5 text-muted-foreground/60"
                                    /></Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent
                                    align="end"
                                    class="rounded-xl border-border/40 p-2 shadow-xl"
                                >
                                    <DropdownMenuItem
                                        as-child
                                        class="rounded-lg text-xs font-bold uppercase tracking-widest"
                                        ><Link
                                            :href="`/assessments/rubrics/${rubric.id}/edit`"
                                            ><Edit class="mr-2 h-4 w-4 opacity-40" />Edit
                                            Rubric</Link
                                        ></DropdownMenuItem
                                    >
                                    <DropdownMenuItem
                                        class="rounded-lg text-xs font-bold uppercase tracking-widest text-red-600 focus:bg-red-50 focus:text-red-700"
                                        ><Trash2
                                            class="mr-2 h-4 w-4 opacity-40"
                                        />Delete</DropdownMenuItem
                                    >
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <h3 class="text-lg font-black tracking-tight text-foreground group-hover:text-primary transition-colors uppercase">
                                    {{ rubric.name }}
                                </h3>
                                <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground opacity-50 mt-1">
                                    {{ rubric.subject?.name || 'General Registry' }}
                                </p>
                            </div>

                            <div class="flex items-center gap-2">
                                <Badge
                                    variant="outline"
                                    class="rounded-lg border-primary/20 bg-primary/5 px-3 py-1 text-[9px] font-black uppercase tracking-widest text-primary"
                                >
                                    {{ rubric.total_points }} Points
                                </Badge>
                                <Badge
                                    variant="outline"
                                    class="rounded-lg border-border/60 bg-muted/30 px-3 py-1 text-[9px] font-black uppercase tracking-widest text-muted-foreground"
                                >
                                    {{ rubric.criteria?.[0]?.levels?.length || 0 }} Scales
                                </Badge>
                            </div>

                            <p class="line-clamp-2 text-[11px] leading-relaxed font-medium text-muted-foreground/80">
                                {{ rubric.description || 'Standardized terminal grading criteria for performance evaluation.' }}
                            </p>
                        </div>

                        <div class="mt-8 pt-6 border-t border-border/40 flex items-center justify-between">
                             <div class="flex items-center gap-2">
                                <div class="h-1.5 w-1.5 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.4)]"></div>
                                <span class="text-[9px] font-black uppercase tracking-[0.2em] text-muted-foreground opacity-60">System Ready</span>
                            </div>
                            <Button
                                variant="ghost"
                                as-child
                                class="h-9 px-4 rounded-xl text-[9px] font-black uppercase tracking-widest text-primary hover:bg-primary/5"
                            >
                                <Link :href="`/assessments/rubrics/${rubric.id}/edit`">
                                    Configure Module
                                </Link>
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
>

            <!-- Pagination -->
            <div
                class="mt-6 flex items-center justify-between border-t border-border py-8"
            >
                <p class="text-xs font-bold text-muted-foreground">
                    Showing {{ rubrics.data.length }} rubrics
                </p>
                <div
                    v-if="rubrics.meta?.last_page > 1"
                    class="flex items-center gap-2"
                >
                    <!-- Pagination simplified for now -->
                </div>
            </div>
        </div>
    </AppLayout>
</template>
