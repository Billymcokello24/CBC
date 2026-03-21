<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { BookMarked, Plus, Search, Filter, MoreHorizontal, Eye, Edit, Trash2, FileCode, Beaker } from 'lucide-vue-next';
import { ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
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
        <div class="flex h-full flex-1 flex-col gap-6 p-6 w-4/5 mx-auto">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-orange-500/10 border border-orange-200 shadow-sm">
                        <BookMarked class="h-6 w-6 text-orange-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Rubrics & Grading Schemes</h1>
                        <p class="text-muted-foreground text-sm">Manage how scores translate to grades and feedback</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" as-child class="font-bold border-gray-200">
                        <Link href="/assessments">Back to Assessments</Link>
                    </Button>
                    <Button as-child class="bg-orange-600 hover:bg-orange-700 shadow-lg shadow-orange-100">
                        <Link href="/assessments/rubrics/create">
                            <Plus class="mr-2 h-4 w-4" />Create Rubric
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="flex flex-col md:flex-row gap-4 items-center">
                <div class="relative flex-1 md:max-w-md w-full">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input v-model="searchQuery" placeholder="Search rubrics..." class="pl-9 h-11 border-orange-100 rounded-xl focus:ring-orange-500" />
                </div>
                <Button variant="outline" class="h-11 rounded-xl border-orange-100 font-bold px-4">
                    <Filter class="mr-2 h-4 w-4" /> Filter
                </Button>
            </div>

            <div v-if="rubrics.data.length === 0" class="flex flex-col items-center justify-center py-20 bg-orange-50/20 rounded-[3rem] border-2 border-dashed border-orange-200/50">
                <div class="h-24 w-24 bg-white rounded-3xl flex items-center justify-center mb-6 shadow-xl shadow-orange-100/50">
                    <Beaker class="h-12 w-12 text-orange-400" />
                </div>
                <h3 class="text-xl font-black text-gray-900">No grading rubrics defined</h3>
                <p class="text-muted-foreground max-w-xs text-center mt-2 font-medium">Create your first rubric to start using standardized grading criteria.</p>
                <Button as-child class="mt-8 bg-orange-600 hover:bg-orange-700 px-8 py-6 rounded-2xl font-black text-lg shadow-xl shadow-orange-100">
                    <Link href="/assessments/rubrics/create">
                        <Plus class="mr-2 h-6 w-6" />Get Started
                    </Link>
                </Button>
            </div>

            <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div v-for="rubric in rubrics.data" :key="rubric.id"
                    class="group relative bg-white rounded-[2.5rem] border border-gray-100 p-8 shadow-sm hover:shadow-2xl hover:-translate-y-2 transition-all duration-500 overflow-hidden"
                >
                    <div class="absolute -right-12 -top-12 h-40 w-40 bg-orange-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-700"></div>

                    <div class="relative">
                        <div class="flex items-start justify-between mb-6">
                            <div class="h-14 w-14 bg-orange-100 rounded-2xl flex items-center justify-center text-orange-600 shadow-inner">
                                <FileCode class="h-7 w-7" />
                            </div>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="ghost" size="icon" class="rounded-full h-10 w-10 hover:bg-orange-50"><MoreHorizontal class="h-5 w-5 text-gray-400" /></Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" class="rounded-2xl p-2 border-orange-100 shadow-xl">
                                    <DropdownMenuItem class="rounded-xl"><Eye class="mr-2 h-4 w-4" />View Details</DropdownMenuItem>
                                    <DropdownMenuItem as-child class="rounded-xl"><Link :href="`/assessments/rubrics/${rubric.id}/edit`"><Edit class="mr-2 h-4 w-4" />Edit Rubric</Link></DropdownMenuItem>
                                    <DropdownMenuItem class="text-red-600 rounded-xl"><Trash2 class="mr-2 h-4 w-4" />Delete</DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>

                        <h3 class="text-2xl font-black text-gray-900 leading-tight group-hover:text-orange-600 transition-colors mb-3">{{ rubric.name }}</h3>
                        <div class="flex flex-wrap items-center gap-2 mb-4">
                            <Badge variant="outline" class="bg-indigo-50 text-indigo-700 border-0 font-black text-[10px] uppercase px-3 py-1 rounded-lg">
                                {{ rubric.subject?.name || 'General / All Subjects' }}
                            </Badge>
                            <Badge variant="outline" class="bg-orange-50 text-orange-600 border-0 font-black text-[10px] uppercase px-3 py-1 rounded-lg">
                                {{ rubric.total_points }} Max Points
                            </Badge>
                        </div>

                        <p class="text-sm text-muted-foreground font-medium line-clamp-3 min-h-[60px] italic">
                            {{ rubric.description || 'Standardized grading criteria for evaluating student performance levels.' }}
                        </p>

                        <div class="mt-8 pt-6 border-t border-dashed border-gray-100 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="h-2 w-2 rounded-full bg-green-500"></div>
                                <span class="text-[10px] font-black uppercase text-muted-foreground tracking-widest">
                                    {{ rubric.criteria?.[0]?.levels?.length || 0 }} Grading Levels
                                </span>
                            </div>
                            <Button variant="ghost" as-child class="text-orange-600 font-extrabold hover:bg-orange-50 px-0 hover:px-4 transition-all rounded-xl h-9">
                                <Link :href="`/assessments/rubrics/${rubric.id}/edit`">
                                    Configure →
                                </Link>
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="flex items-center justify-between py-8 border-t border-gray-100 mt-6">
                <p class="text-xs text-muted-foreground font-bold italic">
                    Showing {{ rubrics.data.length }} rubrics
                </p>
                <div v-if="rubrics.meta?.last_page > 1" class="flex items-center gap-2">
                    <!-- Pagination simplified for now -->
                </div>
            </div>
        </div>
    </AppLayout>
</template>
