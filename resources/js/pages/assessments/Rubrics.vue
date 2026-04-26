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
        <!-- 80% width centered container for rubrics page -->
        <div class="mx-auto flex h-full w-4/5 flex-1 flex-col gap-6 p-6">
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl border border-orange-200 bg-orange-500/10 shadow-sm"
                    >
                        <BookMarked class="h-6 w-6 text-orange-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">
                            Rubrics & Grading Schemes
                        </h1>
                        <p class="text-sm text-muted-foreground">
                            Manage how scores translate to grades and feedback
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        as-child
                        class="border-gray-200 font-bold"
                    >
                        <Link href="/assessments">Back to Assessments</Link>
                    </Button>
                    <Button
                        as-child
                        class="bg-orange-600 shadow-lg shadow-orange-100 hover:bg-orange-700"
                    >
                        <Link href="/assessments/rubrics/create">
                            <Plus class="mr-2 h-4 w-4" />Create Rubric
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="flex flex-col items-center gap-4 md:flex-row">
                <div class="relative w-full flex-1 md:max-w-md">
                    <Search
                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        v-model="searchQuery"
                        placeholder="Search rubrics..."
                        class="h-11 rounded-xl border-orange-100 pl-9 focus:ring-orange-500"
                    />
                </div>
                <Button
                    variant="outline"
                    class="h-11 rounded-xl border-orange-100 px-4 font-bold"
                >
                    <Filter class="mr-2 h-4 w-4" /> Filter
                </Button>
            </div>

            <div
                v-if="rubrics.data.length === 0"
                class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-orange-200/50 bg-orange-50/20 py-20"
            >
                <div
                    class="mb-6 flex h-24 w-24 items-center justify-center rounded-3xl bg-white shadow-xl shadow-orange-100/50"
                >
                    <Beaker class="h-12 w-12 text-orange-400" />
                </div>
                <h3 class="text-xl font-bold text-gray-900">
                    No grading rubrics defined
                </h3>
                <p
                    class="mt-2 max-w-xs text-center font-medium text-muted-foreground"
                >
                    Create your first rubric to start using standardized grading
                    criteria.
                </p>
                <Button
                    as-child
                    class="mt-8 rounded-2xl bg-orange-600 px-8 py-6 text-lg font-bold shadow-xl shadow-orange-100 hover:bg-orange-700"
                >
                    <Link href="/assessments/rubrics/create">
                        <Plus class="mr-2 h-6 w-6" />Get Started
                    </Link>
                </Button>
            </div>

            <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="rubric in rubrics.data"
                    :key="rubric.id"
                    class="group relative overflow-hidden rounded-xl border border-gray-100 bg-white p-8 shadow-sm transition-all duration-500 hover:-translate-y-2 hover:shadow-lg"
                >
                    <div
                        class="absolute -top-12 -right-12 h-40 w-40 rounded-full bg-orange-50 opacity-50 transition-transform duration-700 group-hover:scale-150"
                    ></div>

                    <div class="relative">
                        <div class="mb-6 flex items-start justify-between">
                            <div
                                class="flex h-14 w-14 items-center justify-center rounded-2xl bg-orange-100 text-orange-600 shadow-inner"
                            >
                                <FileCode class="h-7 w-7" />
                            </div>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-10 w-10 rounded-full hover:bg-orange-50"
                                        ><MoreHorizontal
                                            class="h-5 w-5 text-gray-400"
                                    /></Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent
                                    align="end"
                                    class="rounded-2xl border-orange-100 p-2 shadow-xl"
                                >
                                    <DropdownMenuItem class="rounded-xl"
                                        ><Eye class="mr-2 h-4 w-4" />View
                                        Details</DropdownMenuItem
                                    >
                                    <DropdownMenuItem
                                        as-child
                                        class="rounded-xl"
                                        ><Link
                                            :href="`/assessments/rubrics/${rubric.id}/edit`"
                                            ><Edit class="mr-2 h-4 w-4" />Edit
                                            Rubric</Link
                                        ></DropdownMenuItem
                                    >
                                    <DropdownMenuItem
                                        class="rounded-xl text-red-600"
                                        ><Trash2
                                            class="mr-2 h-4 w-4"
                                        />Delete</DropdownMenuItem
                                    >
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>

                        <h3
                            class="mb-3 text-2xl leading-tight font-bold text-gray-900 transition-colors group-hover:text-orange-600"
                        >
                            {{ rubric.name }}
                        </h3>
                        <div class="mb-4 flex flex-wrap items-center gap-2">
                            <Badge
                                variant="outline"
                                class="rounded-lg border-0 bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-700 uppercase"
                            >
                                {{
                                    rubric.subject?.name ||
                                    'General / All Subjects'
                                }}
                            </Badge>
                            <Badge
                                variant="outline"
                                class="rounded-lg border-0 bg-orange-50 px-3 py-1 text-xs font-bold text-orange-600 uppercase"
                            >
                                {{ rubric.total_points }} Max Points
                            </Badge>
                        </div>

                        <p
                            class="line-clamp-3 min-h-[60px] text-sm font-medium text-muted-foreground"
                        >
                            {{
                                rubric.description ||
                                'Standardized grading criteria for evaluating student performance levels.'
                            }}
                        </p>

                        <div
                            class="mt-8 flex items-center justify-between border-t border-dashed border-gray-100 pt-6"
                        >
                            <div class="flex items-center gap-2">
                                <div
                                    class="h-2 w-2 rounded-full bg-green-500"
                                ></div>
                                <span
                                    class="text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                >
                                    {{
                                        rubric.criteria?.[0]?.levels?.length ||
                                        0
                                    }}
                                    Grading Levels
                                </span>
                            </div>
                            <Button
                                variant="ghost"
                                as-child
                                class="h-9 rounded-xl px-0 font-extrabold text-orange-600 transition-all hover:bg-orange-50 hover:px-4"
                            >
                                <Link
                                    :href="`/assessments/rubrics/${rubric.id}/edit`"
                                >
                                    Configure →
                                </Link>
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div
                class="mt-6 flex items-center justify-between border-t border-gray-100 py-8"
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
