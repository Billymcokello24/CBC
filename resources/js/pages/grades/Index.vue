<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import {
    CheckCircle2,
    Edit,
    Eye,
    Filter,
    GraduationCap,
    Grid2x2,
    List,
    MoreHorizontal,
    Plus,
    Search,
    ShieldCheck,
    ShieldOff,
    Trash2,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface GradeRow {
    id: number;
    name: string;
    code: string;
    category: string;
    level_order: number;
    minimum_age: number | null;
    maximum_age: number | null;
    is_active: boolean;
    classes_count: number;
    students_count: number;
    lead_name: string | null;
}

const props = defineProps<{
    grades: GradeRow[];
    stats: {
        total_grades: number;
        active_grades: number;
        total_classes: number;
        total_students: number;
    };
    filters: {
        search: string;
        status: string;
        view: 'grid' | 'list';
    };
    statusOptions: Array<{ value: string; label: string }>;
}>();

const page = usePage<{ flash?: { success?: string } }>();
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Grades', href: '/grades' },
];

const searchQuery = ref(props.filters.search ?? '');
const selectedStatus = ref(props.filters.status ?? 'all');
const selectedView = ref<'grid' | 'list'>(props.filters.view ?? 'grid');
const selectedGradeIds = ref<number[]>([]);
const bulkForm = useForm<{ grade_ids: number[]; action: 'activate' | 'deactivate' | 'delete' | '' }>({ grade_ids: [], action: '' });
const actionForm = useForm({});
const showFilters = ref(true);
const showToast = ref(false);
let toastTimer: ReturnType<typeof setTimeout> | null = null;
let debounceTimer: ReturnType<typeof setTimeout> | null = null;

const applyFilters = () => {
    router.get('/grades', {
        search: searchQuery.value || undefined,
        status: selectedStatus.value !== 'all' ? selectedStatus.value : undefined,
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
watch([selectedStatus, selectedView], () => applyFilters());
watch(
    () => props.grades,
    () => {
        selectedGradeIds.value = selectedGradeIds.value.filter((id) => props.grades.some((grade) => grade.id === id));
    },
    { deep: true },
);

const clearFilters = () => {
    searchQuery.value = '';
    selectedStatus.value = 'all';
    applyFilters();
};

const selectedCount = computed(() => selectedGradeIds.value.length);
const allSelected = computed(() => props.grades.length > 0 && props.grades.every((grade) => selectedGradeIds.value.includes(grade.id)));
const toggleAllGrades = () => {
    selectedGradeIds.value = allSelected.value ? [] : props.grades.map((grade) => grade.id);
};
const toggleGrade = (gradeId: number) => {
    selectedGradeIds.value = selectedGradeIds.value.includes(gradeId)
        ? selectedGradeIds.value.filter((id) => id !== gradeId)
        : [...selectedGradeIds.value, gradeId];
};

const runBulkAction = (action: 'activate' | 'deactivate' | 'delete') => {
    if (!selectedGradeIds.value.length) return;
    bulkForm.grade_ids = [...selectedGradeIds.value];
    bulkForm.action = action;
    bulkForm.post('/grades/bulk-action', {
        preserveScroll: true,
        onSuccess: () => {
            selectedGradeIds.value = [];
        },
    });
};

const toggleGradeStatus = (grade: GradeRow) => {
    actionForm.patch(`/grades/${grade.id}/${grade.is_active ? 'deactivate' : 'activate'}`, { preserveScroll: true });
};

const deleteGrade = (grade: GradeRow) => {
    actionForm.delete(`/grades/${grade.id}`, { preserveScroll: true });
};

const flashSuccess = computed(() => page.props.flash?.success ?? '');
watch(
    flashSuccess,
    (value) => {
        if (!value) return;
        showToast.value = true;
        if (toastTimer) clearTimeout(toastTimer);
        toastTimer = setTimeout(() => (showToast.value = false), 3000);
    },
    { immediate: true },
);
</script>

<template>
    <Head title="Grades" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="showToast && flashSuccess" class="fixed right-4 top-4 z-50">
            <div class="flex items-center gap-2 rounded-lg border bg-background px-4 py-3 shadow-lg">
                <CheckCircle2 class="h-4 w-4 text-green-600" />
                <span class="text-sm font-medium">{{ flashSuccess }}</span>
            </div>
        </div>

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-500/10">
                        <GraduationCap class="h-6 w-6 text-green-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Grades</h1>
                        <p class="text-muted-foreground">Manage grade levels, linked classes, and progression structure</p>
                    </div>
                </div>
                <Button as-child><Link href="/grades/create"><Plus class="mr-2 h-4 w-4" />Add Grade</Link></Button>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Total Grades</div><div class="mt-1 text-3xl font-bold">{{ stats.total_grades }}</div></div>
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Active Grades</div><div class="mt-1 text-3xl font-bold text-green-600">{{ stats.active_grades }}</div></div>
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Total Classes</div><div class="mt-1 text-3xl font-bold text-blue-600">{{ stats.total_classes }}</div></div>
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Total Students</div><div class="mt-1 text-3xl font-bold text-indigo-600">{{ stats.total_students }}</div></div>
            </div>

            <div class="flex flex-col gap-4 rounded-xl border bg-card p-4 md:flex-row md:items-center">
                <div class="relative flex-1 md:max-w-sm">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input v-model="searchQuery" placeholder="Search grades..." class="pl-9" />
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" @click="selectedView = 'grid'"><Grid2x2 class="mr-2 h-4 w-4" />Grid</Button>
                    <Button variant="outline" size="sm" @click="selectedView = 'list'"><List class="mr-2 h-4 w-4" />List</Button>
                    <Button variant="outline" size="sm" @click="showFilters = !showFilters"><Filter class="mr-2 h-4 w-4" />{{ showFilters ? 'Hide Filters' : 'Show Filters' }}</Button>
                    <Button variant="outline" size="sm" @click="clearFilters">Reset</Button>
                </div>
            </div>

            <div v-if="showFilters" class="grid gap-4 rounded-xl border bg-card p-4 md:grid-cols-2">
                <div class="space-y-2">
                    <label class="text-sm font-medium">Status</label>
                    <select v-model="selectedStatus" class="h-10 w-full rounded-md border bg-background px-3 text-sm">
                        <option v-for="option in statusOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                    </select>
                </div>
            </div>

            <div v-if="selectedCount > 0" class="flex items-center justify-between rounded-xl border border-primary/20 bg-primary/5 p-4">
                <div>
                    <p class="text-sm font-medium">{{ selectedCount }} grade{{ selectedCount === 1 ? '' : 's' }} selected</p>
                    <p class="text-xs text-muted-foreground">Apply bulk actions across multiple grades at once.</p>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" @click="selectedGradeIds = []">Clear</Button>
                    <Button variant="outline" size="sm" @click="runBulkAction('activate')">Activate</Button>
                    <Button variant="outline" size="sm" @click="runBulkAction('deactivate')">Deactivate</Button>
                    <Button variant="destructive" size="sm" @click="runBulkAction('delete')">Delete</Button>
                </div>
            </div>

            <div v-if="selectedView === 'grid'" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div v-for="grade in grades" :key="grade.id" class="rounded-xl border bg-card p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <input type="checkbox" :checked="selectedGradeIds.includes(grade.id)" @change="toggleGrade(grade.id)" />
                            <div>
                                <h2 class="text-lg font-semibold">{{ grade.name }}</h2>
                                <p class="text-sm text-muted-foreground">{{ grade.code }} • {{ grade.category }}</p>
                                <p class="mt-1 text-xs text-muted-foreground">Grade Lead: {{ grade.lead_name || 'Not assigned' }}</p>
                            </div>
                        </div>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child><Button variant="ghost" size="icon" class="h-8 w-8"><MoreHorizontal class="h-4 w-4" /></Button></DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuItem as-child><Link :href="`/grades/${grade.id}`"><Eye class="mr-2 h-4 w-4" />View</Link></DropdownMenuItem>
                                <DropdownMenuItem as-child><Link :href="`/grades/${grade.id}/edit`"><Edit class="mr-2 h-4 w-4" />Edit</Link></DropdownMenuItem>
                                <DropdownMenuItem @click="toggleGradeStatus(grade)">
                                    <component :is="grade.is_active ? ShieldOff : ShieldCheck" class="mr-2 h-4 w-4" />
                                    {{ grade.is_active ? 'Deactivate' : 'Activate' }}
                                </DropdownMenuItem>
                                <DropdownMenuItem class="text-destructive" @click="deleteGrade(grade)"><Trash2 class="mr-2 h-4 w-4" />Delete</DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                        <div class="rounded-lg border p-3"><div class="text-muted-foreground">Classes</div><div class="mt-1 font-semibold">{{ grade.classes_count }}</div></div>
                        <div class="rounded-lg border p-3"><div class="text-muted-foreground">Students</div><div class="mt-1 font-semibold">{{ grade.students_count }}</div></div>
                        <div class="rounded-lg border p-3"><div class="text-muted-foreground">Min Age</div><div class="mt-1 font-semibold">{{ grade.minimum_age ?? '—' }}</div></div>
                        <div class="rounded-lg border p-3"><div class="text-muted-foreground">Max Age</div><div class="mt-1 font-semibold">{{ grade.maximum_age ?? '—' }}</div></div>
                    </div>
                    <div class="mt-4 flex gap-2">
                        <Button variant="outline" size="sm" class="flex-1" as-child><Link :href="`/grades/${grade.id}`">Open Grade</Link></Button>
                        <Button variant="outline" size="sm" class="flex-1" as-child><Link href="/classes">Classes</Link></Button>
                    </div>
                </div>
            </div>

            <div v-else class="rounded-xl border bg-card">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b bg-muted/50">
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground"><input type="checkbox" :checked="allSelected" @change="toggleAllGrades" /></th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Grade</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Lead</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Classes</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Students</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Status</th>
                                <th class="px-4 py-3 text-right text-sm font-medium text-muted-foreground">Open</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="grade in grades" :key="grade.id" class="border-b transition-colors hover:bg-muted/50">
                                <td class="px-4 py-3"><input type="checkbox" :checked="selectedGradeIds.includes(grade.id)" @change="toggleGrade(grade.id)" /></td>
                                <td class="px-4 py-3"><div class="font-medium">{{ grade.name }}</div><div class="text-xs text-muted-foreground">{{ grade.code }} • {{ grade.category }}</div></td>
                                <td class="px-4 py-3 text-sm">{{ grade.lead_name || 'Not assigned' }}</td>
                                <td class="px-4 py-3 text-sm">{{ grade.classes_count }}</td>
                                <td class="px-4 py-3 text-sm">{{ grade.students_count }}</td>
                                <td class="px-4 py-3 text-sm">{{ grade.is_active ? 'Active' : 'Inactive' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        <Button variant="outline" size="sm" as-child><Link :href="`/grades/${grade.id}`">Open</Link></Button>
                                        <Button variant="outline" size="sm" as-child><Link :href="`/grades/${grade.id}/edit`">Edit</Link></Button>
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
