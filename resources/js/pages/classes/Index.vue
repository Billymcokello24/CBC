<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { BookOpen, GraduationCap, List, MoreHorizontal, Plus, School, Search, Users, Grid2x2 } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface ClassRow {
    id: number;
    name: string;
    code: string;
    grade: string | null;
    stream: string | null;
    stream_code: string | null;
    teacher: string | null;
    students: number;
    capacity: number | null;
    academic_year: string | null;
    utilization: number;
}

const props = defineProps<{
    classes: ClassRow[];
    stats: {
        total_classes: number;
        total_students: number;
        average_class_size: number;
        grades_count: number;
    };
    filters: {
        search: string;
        grade_id: number | null;
        view: 'grid' | 'list';
    };
    grades: Array<{ id: number; name: string }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Classes', href: '/classes' },
];

const searchQuery = ref(props.filters.search ?? '');
const selectedGradeId = ref(props.filters.grade_id ? String(props.filters.grade_id) : '');
const selectedView = ref<'grid' | 'list'>(props.filters.view ?? 'grid');
let debounceTimer: ReturnType<typeof setTimeout> | null = null;

const applyFilters = () => {
    router.get('/classes', {
        search: searchQuery.value || undefined,
        grade_id: selectedGradeId.value || undefined,
        view: selectedView.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

watch(searchQuery, () => {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => applyFilters(), 300);
});
watch(selectedGradeId, () => applyFilters());
watch(selectedView, () => applyFilters());

watch(
    () => props.classes,
    () => {
        selectedClassIds.value = selectedClassIds.value.filter((id) => props.classes.some((cls) => cls.id === id));
    },
    { deep: true },
);

const bulkForm = useForm<{ class_ids: number[]; action: 'activate' | 'deactivate' | 'delete' | '' }>({ class_ids: [], action: '' });
const selectedClassIds = ref<number[]>([]);
const selectedCount = computed(() => selectedClassIds.value.length);
const allSelected = computed(() => props.classes.length > 0 && props.classes.every((cls) => selectedClassIds.value.includes(cls.id)));

const toggleAllClasses = () => {
    selectedClassIds.value = allSelected.value ? [] : props.classes.map((cls) => cls.id);
};

const toggleClassSelection = (classId: number) => {
    selectedClassIds.value = selectedClassIds.value.includes(classId)
        ? selectedClassIds.value.filter((id) => id !== classId)
        : [...selectedClassIds.value, classId];
};

const runBulkAction = (action: 'activate' | 'deactivate' | 'delete') => {
    if (!selectedClassIds.value.length) return;
    bulkForm.class_ids = [...selectedClassIds.value];
    bulkForm.action = action;
    bulkForm.post('/classes/bulk-action', {
        preserveScroll: true,
        onSuccess: () => {
            selectedClassIds.value = [];
        },
    });
};
</script>

<template>
    <Head title="Classes" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-500/10">
                        <School class="h-6 w-6 text-indigo-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Classes</h1>
                        <p class="text-muted-foreground">Manage class structures and allocations from the live database</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" as-child><Link href="/classes/allocations">Allocations</Link></Button>
                    <Button as-child><Link href="/classes/create"><Plus class="mr-2 h-4 w-4" />Add Class</Link></Button>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border bg-card p-4">
                    <div class="flex items-center gap-3"><School class="h-5 w-5 text-indigo-600" /><span class="text-sm text-muted-foreground">Total Classes</span></div>
                    <p class="mt-2 text-3xl font-bold text-indigo-600">{{ stats.total_classes }}</p>
                </div>
                <div class="rounded-xl border bg-card p-4">
                    <div class="flex items-center gap-3"><Users class="h-5 w-5 text-blue-600" /><span class="text-sm text-muted-foreground">Total Students</span></div>
                    <p class="mt-2 text-3xl font-bold text-blue-600">{{ stats.total_students }}</p>
                </div>
                <div class="rounded-xl border bg-card p-4">
                    <div class="flex items-center gap-3"><GraduationCap class="h-5 w-5 text-green-600" /><span class="text-sm text-muted-foreground">Avg. Class Size</span></div>
                    <p class="mt-2 text-3xl font-bold text-green-600">{{ stats.average_class_size }}</p>
                </div>
                <div class="rounded-xl border bg-card p-4">
                    <div class="flex items-center gap-3"><BookOpen class="h-5 w-5 text-purple-600" /><span class="text-sm text-muted-foreground">Grades</span></div>
                    <p class="mt-2 text-3xl font-bold text-purple-600">{{ stats.grades_count }}</p>
                </div>
            </div>

            <div class="flex flex-col gap-4 md:flex-row md:items-center">
                <div class="relative flex-1 md:max-w-sm">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input v-model="searchQuery" placeholder="Search classes..." class="pl-9" />
                </div>
                <select v-model="selectedGradeId" class="h-10 rounded-md border bg-background px-3 text-sm">
                    <option value="">All Grades</option>
                    <option v-for="grade in grades" :key="grade.id" :value="String(grade.id)">{{ grade.name }}</option>
                </select>
                <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" @click="selectedView = 'grid'"><Grid2x2 class="mr-2 h-4 w-4" />Grid</Button>
                    <Button variant="outline" size="sm" @click="selectedView = 'list'"><List class="mr-2 h-4 w-4" />List</Button>
                </div>
            </div>

            <div v-if="selectedCount > 0" class="flex items-center justify-between rounded-xl border border-primary/20 bg-primary/5 p-4">
                <div>
                    <p class="text-sm font-medium">{{ selectedCount }} class{{ selectedCount === 1 ? '' : 'es' }} selected</p>
                    <p class="text-xs text-muted-foreground">Apply bulk actions across multiple classes at once.</p>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" @click="selectedClassIds = []">Clear</Button>
                    <Button variant="outline" size="sm" @click="runBulkAction('activate')">Activate</Button>
                    <Button variant="outline" size="sm" @click="runBulkAction('deactivate')">Deactivate</Button>
                    <Button variant="destructive" size="sm" @click="runBulkAction('delete')">Delete</Button>
                </div>
            </div>

            <div v-if="selectedView === 'grid'" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div v-for="cls in classes" :key="cls.id" class="rounded-xl border bg-card p-6 transition-all hover:shadow-lg">
                    <div class="mb-4 flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" :checked="selectedClassIds.includes(cls.id)" @change="toggleClassSelection(cls.id)" />
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white font-bold">
                                {{ cls.stream_code || '?' }}
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold">{{ cls.name }}</h3>
                                <p class="text-sm text-muted-foreground">{{ cls.grade || 'Unknown grade' }}</p>
                            </div>
                        </div>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child><Button variant="ghost" size="icon" class="h-8 w-8"><MoreHorizontal class="h-4 w-4" /></Button></DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuItem as-child><Link :href="`/classes/${cls.id}`">View Details</Link></DropdownMenuItem>
                                <DropdownMenuItem as-child><Link :href="`/classes/${cls.id}/edit`">Edit Class</Link></DropdownMenuItem>
                                <DropdownMenuItem as-child><Link href="/students">Manage Students</Link></DropdownMenuItem>
                                <DropdownMenuItem as-child><Link :href="`/students/enrollments/groups/${cls.id}`">View Enrollments</Link></DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center gap-2 text-sm">
                            <Users class="h-4 w-4 text-muted-foreground" />
                            <span>Class Teacher: <span class="font-medium">{{ cls.teacher || 'Not assigned' }}</span></span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <Badge variant="outline">{{ cls.academic_year || 'No academic year' }}</Badge>
                            <Badge variant="outline">{{ cls.stream || 'No stream' }}</Badge>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground">Students</span>
                            <span class="font-semibold">{{ cls.students }}/{{ cls.capacity || '—' }}</span>
                        </div>
                        <div class="h-2 rounded-full bg-muted">
                            <div class="h-full rounded-full bg-indigo-500 transition-all" :style="{ width: `${cls.utilization}%` }"></div>
                        </div>
                        <div class="flex gap-2 pt-2">
                            <Button variant="outline" size="sm" class="flex-1" as-child><Link :href="`/classes/${cls.id}`">View Class</Link></Button>
                            <Button variant="outline" size="sm" class="flex-1" as-child><Link :href="`/students/enrollments/groups/${cls.id}`">Enrollments</Link></Button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="rounded-xl border bg-card">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b bg-muted/50">
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">
                                    <input type="checkbox" :checked="allSelected" @change="toggleAllClasses" />
                                </th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Class</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Grade</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Year</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Students</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Utilization</th>
                                <th class="px-4 py-3 text-right text-sm font-medium text-muted-foreground">Open</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="cls in classes" :key="cls.id" class="border-b transition-colors hover:bg-muted/50">
                                <td class="px-4 py-3"><input type="checkbox" :checked="selectedClassIds.includes(cls.id)" @change="toggleClassSelection(cls.id)" /></td>
                                <td class="px-4 py-3">
                                    <div class="font-medium">{{ cls.name }}</div>
                                    <div class="text-xs text-muted-foreground">{{ cls.code }}<span v-if="cls.stream"> • {{ cls.stream }}</span></div>
                                </td>
                                <td class="px-4 py-3 text-sm">{{ cls.grade || '—' }}</td>
                                <td class="px-4 py-3 text-sm">{{ cls.academic_year || '—' }}</td>
                                <td class="px-4 py-3 text-sm">{{ cls.students }}/{{ cls.capacity || '—' }}</td>
                                <td class="px-4 py-3 text-sm">{{ cls.utilization }}%</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        <Button variant="outline" size="sm" as-child><Link :href="`/classes/${cls.id}`">Open</Link></Button>
                                        <Button variant="outline" size="sm" as-child><Link :href="`/classes/${cls.id}/edit`">Edit</Link></Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
