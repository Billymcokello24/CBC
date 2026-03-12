<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { 
    Users, Plus, Search, Filter, Download, MoreHorizontal, 
    Eye, Edit, Trash2, Mail, Phone, ChevronDown, ChevronUp,
    CheckCircle2, Loader2, Upload, Grid2x2, List
} from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import type { BreadcrumbItem } from '@/types';
import TeacherBulkUploadModal from './partials/TeacherBulkUploadModal.vue';

interface TeacherRow {
    id: number;
    staff_number: string;
    tsc_number: string | null;
    full_name: string;
    first_name: string;
    last_name: string;
    gender: string;
    department: { id: number; name: string } | null;
    phone: string;
    email: string;
    status: string;
    photo: string | null;
}

interface PaginatedTeachers {
    data: TeacherRow[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: Array<{ url: string | null; label: string; active: boolean }>;
}

interface Props {
    teachers: PaginatedTeachers;
    departments: Array<{ id: number; name: string }>;
    stats: {
        total: number;
        active: number;
        on_leave: number;
        departments: number;
    };
    filters: {
        search: string;
        status: string;
        department_id: string;
        view: 'grid' | 'list';
    };
}

const props = defineProps<Props>();
const page = usePage();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Teachers', href: '/teachers' },
];

const searchQuery = ref(props.filters.search ?? '');
const selectedStatus = ref(props.filters.status ?? 'all');
const selectedDepartmentId = ref(props.filters.department_id ?? 'all');
const selectedView = ref<'grid' | 'list'>(props.filters.view ?? 'grid');
const showFilters = ref(false);
const perPage = ref(props.teachers.per_page ?? 20);

const selectedTeacherIds = ref<number[]>([]);
const bulkUploadOpen = ref(false);
const confirmOpen = ref(false);
const confirmMode = ref<'delete' | 'bulkDelete'>('delete');
const selectedTeacher = ref<TeacherRow | null>(null);
const actionForm = useForm({});

let debounceTimer: ReturnType<typeof setTimeout> | null = null;

const applyFilters = (pageNumber = 1) => {
    router.get(
        '/teachers',
        {
            search: searchQuery.value || undefined,
            status: selectedStatus.value !== 'all' ? selectedStatus.value : undefined,
            department_id: selectedDepartmentId.value !== 'all' ? selectedDepartmentId.value : undefined,
            per_page: perPage.value,
            view: selectedView.value,
            page: pageNumber,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['teachers', 'stats', 'filters'],
        },
    );
};

watch(searchQuery, () => {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => applyFilters(), 350);
});

watch([selectedStatus, selectedDepartmentId, perPage, selectedView], () => applyFilters());

const toggleAllTeachers = () => {
    selectedTeacherIds.value = allSelected.value ? [] : props.teachers.data.map(t => t.id);
};

const allSelected = computed(() => {
    return props.teachers.data.length > 0 && props.teachers.data.every(t => selectedTeacherIds.value.includes(t.id));
});

const openActionModal = (mode: 'delete' | 'bulkDelete', teacher: TeacherRow | null = null) => {
    confirmMode.value = mode;
    selectedTeacher.value = teacher;
    confirmOpen.value = true;
};

const closeActionModal = () => {
    confirmOpen.value = false;
    selectedTeacher.value = null;
};

const confirmAction = () => {
    if (confirmMode.value === 'bulkDelete') {
        actionForm.transform(() => ({
            ids: selectedTeacherIds.value
        })).post('/teachers/bulk-delete', {
            preserveScroll: true,
            onSuccess: () => {
                selectedTeacherIds.value = [];
                closeActionModal();
            },
        });
    } else if (selectedTeacher.value) {
        actionForm.delete(`/teachers/${selectedTeacher.value.id}`, {
            preserveScroll: true,
            onSuccess: () => closeActionModal(),
        });
    }
};

const pageLabel = computed(() => {
    const from = props.teachers.from ?? 0;
    const to = props.teachers.to ?? 0;
    return `Showing ${from} to ${to} of ${props.teachers.total} teachers`;
});

const statusOptions = [
    { value: 'all', label: 'All Statuses' },
    { value: 'active', label: 'Active' },
    { value: 'on_leave', label: 'On Leave' },
    { value: 'inactive', label: 'Inactive' },
    { value: 'suspended', label: 'Suspended' },
    { value: 'terminated', label: 'Terminated' },
];
</script>

<template>
    <Head title="Teachers" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header Section -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-500/10">
                        <Users class="h-6 w-6 text-purple-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Teachers</h1>
                        <p class="text-muted-foreground">Manage teaching staff and departments</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Button variant="outline" size="sm" @click="bulkUploadOpen = true">
                        <Upload class="mr-2 h-4 w-4" />
                        Bulk Upload
                    </Button>
                    <Button v-if="selectedTeacherIds.length > 0" variant="destructive" size="sm" @click="openActionModal('bulkDelete')">
                        <Trash2 class="mr-2 h-4 w-4" />
                        Delete Selected ({{ selectedTeacherIds.length }})
                    </Button>
                    <Button size="sm" as-child>
                        <Link href="/teachers/create">
                            <Plus class="mr-2 h-4 w-4" />
                            Add Teacher
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border bg-linear-to-br from-purple-50 to-purple-100/50 p-4 dark:from-purple-950/50 dark:to-purple-900/30">
                    <div class="text-sm text-muted-foreground">Total Teachers</div>
                    <div class="mt-1 text-3xl font-bold text-purple-600">{{ stats.total }}</div>
                </div>
                <div class="rounded-xl border bg-linear-to-br from-green-50 to-green-100/50 p-4 dark:from-green-950/50 dark:to-green-900/30">
                    <div class="text-sm text-muted-foreground">Active</div>
                    <div class="mt-1 text-3xl font-bold text-green-600">{{ stats.active }}</div>
                </div>
                <div class="rounded-xl border bg-linear-to-br from-amber-50 to-amber-100/50 p-4 dark:from-amber-950/50 dark:to-amber-900/30">
                    <div class="text-sm text-muted-foreground">On Leave</div>
                    <div class="mt-1 text-3xl font-bold text-amber-600">{{ stats.on_leave }}</div>
                </div>
                <div class="rounded-xl border bg-linear-to-br from-blue-50 to-blue-100/50 p-4 dark:from-blue-950/50 dark:to-blue-900/30">
                    <div class="text-sm text-muted-foreground">Departments</div>
                    <div class="mt-1 text-3xl font-bold text-blue-600">{{ stats.departments }}</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center">
                <div class="relative flex-1 md:max-w-sm">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input v-model="searchQuery" placeholder="Search by name, staff no, TSC no..." class="pl-9" />
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" @click="showFilters = !showFilters">
                        <Filter class="mr-2 h-4 w-4" />
                        Filters
                        <component :is="showFilters ? ChevronUp : ChevronDown" class="ml-2 h-3 w-3" />
                    </Button>
                    <div class="flex items-center rounded-lg border bg-muted p-1">
                        <Button 
                            variant="ghost" 
                            size="icon" 
                            class="h-7 w-7" 
                            :class="{ 'bg-background shadow-xs': selectedView === 'grid' }"
                            @click="selectedView = 'grid'"
                        >
                            <Grid2x2 class="h-4 w-4" />
                        </Button>
                        <Button 
                            variant="ghost" 
                            size="icon" 
                            class="h-7 w-7" 
                            :class="{ 'bg-background shadow-xs': selectedView === 'list' }"
                            @click="selectedView = 'list'"
                        >
                            <List class="h-4 w-4" />
                        </Button>
                    </div>
                    <Button variant="outline" size="sm" as-child>
                        <a href="/teachers/template/download">
                            <Download class="mr-2 h-4 w-4" />
                            Template
                        </a>
                    </Button>
                </div>
            </div>

            <div v-if="showFilters" class="grid gap-4 rounded-xl border bg-card p-4 md:grid-cols-2 lg:grid-cols-3">
                <div class="space-y-2">
                    <label class="text-sm font-medium">Status</label>
                    <select v-model="selectedStatus" class="h-10 w-full rounded-md border bg-background px-3 text-sm">
                        <option v-for="option in statusOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium">Department</label>
                    <select v-model="selectedDepartmentId" class="h-10 w-full rounded-md border bg-background px-3 text-sm">
                        <option value="all">All Departments</option>
                        <option v-for="dept in departments" :key="dept.id" :value="String(dept.id)">{{ dept.name }}</option>
                    </select>
                </div>
            </div>

            <!-- List View (Table) -->
            <div v-if="selectedView === 'list'" class="rounded-xl border bg-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b bg-muted/50 text-left font-medium">
                                <th class="w-12 px-4 py-3 text-center">
                                    <input 
                                        type="checkbox" 
                                        :checked="allSelected"
                                        @change="toggleAllTeachers"
                                        class="h-4 w-4 rounded border-gray-300 text-purple-600 focus:ring-purple-600"
                                    />
                                </th>
                                <th class="px-4 py-3">Teacher</th>
                                <th class="px-4 py-3">Staff ID & TSC</th>
                                <th class="px-4 py-3">Department</th>
                                <th class="px-4 py-3">Contact</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y whitespace-nowrap">
                            <tr v-for="teacher in teachers.data" :key="teacher.id" class="group hover:bg-muted/50 transition-colors">
                                <td class="px-4 py-3 text-center">
                                    <input 
                                        type="checkbox" 
                                        :checked="selectedTeacherIds.includes(teacher.id)"
                                        @change="selectedTeacherIds.includes(teacher.id) ? selectedTeacherIds = selectedTeacherIds.filter(id => id !== teacher.id) : selectedTeacherIds.push(teacher.id)"
                                        class="h-4 w-4 rounded border-gray-300 text-purple-600 focus:ring-purple-600"
                                    />
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="h-9 w-9 overflow-hidden rounded-full border border-purple-100 bg-purple-50">
                                            <img v-if="teacher.photo" :src="`/storage/${teacher.photo}`" class="h-full w-full object-cover" />
                                            <div v-else class="flex h-full w-full items-center justify-center text-xs font-bold text-purple-600">
                                                {{ teacher.first_name[0] }}{{ teacher.last_name[0] }}
                                            </div>
                                        </div>
                                        <div class="min-w-0">
                                            <Link :href="`/teachers/${teacher.id}`" class="font-medium hover:text-purple-600">{{ teacher.full_name }}</Link>
                                            <div class="text-[11px] text-muted-foreground capitalize">{{ teacher.gender }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium">{{ teacher.staff_number }}</div>
                                    <div class="text-xs text-muted-foreground">{{ teacher.tsc_number || 'No TSC' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <Badge variant="outline" class="font-normal">{{ teacher.department?.name || '—' }}</Badge>
                                </td>
                                <td class="px-4 py-3 text-xs">
                                    <div class="flex items-center gap-1.5"><Mail class="h-3 w-3 opacity-50" /> {{ teacher.email }}</div>
                                    <div class="flex items-center gap-1.5 mt-1"><Phone class="h-3 w-3 opacity-50" /> {{ teacher.phone || '—' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <Badge :variant="teacher.status === 'active' ? 'default' : 'secondary'" class="capitalize text-[11px]">
                                        {{ teacher.status }}
                                    </Badge>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">
                                        <Button variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-purple-600" as-child>
                                            <Link :href="`/teachers/${teacher.id}`"><Eye class="h-4 w-4" /></Link>
                                        </Button>
                                        <Button variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-blue-600" as-child>
                                            <Link :href="`/teachers/${teacher.id}/edit`"><Edit class="h-4 w-4" /></Link>
                                        </Button>
                                        <Button variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-destructive" @click="openActionModal('delete', teacher)">
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="teachers.data.length === 0">
                                <td colspan="7" class="py-12 text-center text-muted-foreground">
                                    <Users class="mx-auto h-12 w-12 opacity-10 mb-4" />
                                    No teachers found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Grid View -->
            <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <div v-for="teacher in teachers.data" :key="teacher.id" class="group relative rounded-xl border bg-card p-5 transition-all hover:shadow-lg">
                    <div class="absolute right-4 top-4 z-10">
                        <input 
                            type="checkbox" 
                            :checked="selectedTeacherIds.includes(teacher.id)"
                            @change="selectedTeacherIds.includes(teacher.id) ? selectedTeacherIds = selectedTeacherIds.filter(id => id !== teacher.id) : selectedTeacherIds.push(teacher.id)"
                            class="h-4 w-4 rounded border-gray-300 text-purple-600 focus:ring-purple-600"
                        />
                    </div>
                    
                    <div class="flex items-start gap-4">
                        <div class="relative h-14 w-14 shrink-0 overflow-hidden rounded-full border border-purple-100 bg-purple-50">
                            <img v-if="teacher.photo" :src="`/storage/${teacher.photo}`" class="h-full w-full object-cover" />
                            <div v-else class="flex h-full w-full items-center justify-center font-bold text-purple-600">
                                {{ teacher.first_name[0] }}{{ teacher.last_name[0] }}
                            </div>
                        </div>
                        <div class="flex-1 overflow-hidden">
                            <h3 class="truncate font-semibold text-foreground group-hover:text-purple-600 transition-colors">
                                <Link :href="`/teachers/${teacher.id}`">{{ teacher.full_name }}</Link>
                            </h3>
                            <p class="text-xs text-muted-foreground">{{ teacher.staff_number }}</p>
                            <Badge variant="outline" class="mt-1 h-5 text-[10px]">{{ teacher.department?.name || 'No Dept' }}</Badge>
                        </div>
                    </div>

                    <div class="mt-4 space-y-2 border-t pt-4">
                        <div class="flex items-center gap-2 text-[13px] text-muted-foreground">
                            <Mail class="h-3.5 w-3.5" />
                            <span class="truncate">{{ teacher.email }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-[13px] text-muted-foreground">
                            <Phone class="h-3.5 w-3.5" />
                            <span>{{ teacher.phone || '—' }}</span>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-between gap-2 border-t pt-4">
                        <Badge :variant="teacher.status === 'active' ? 'default' : 'secondary'" class="capitalize">
                            {{ teacher.status }}
                        </Badge>
                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <Button variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-purple-600" as-child>
                                <Link :href="`/teachers/${teacher.id}`"><Eye class="h-4 w-4" /></Link>
                            </Button>
                            <Button variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-blue-600" as-child>
                                <Link :href="`/teachers/${teacher.id}/edit`"><Edit class="h-4 w-4" /></Link>
                            </Button>
                            <Button variant="ghost" size="icon" class="h-8 w-8 text-muted-foreground hover:text-destructive" @click="openActionModal('delete', teacher)">
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                </div>
                
                <div v-if="teachers.data.length === 0" class="col-span-full flex flex-col items-center justify-center py-20 text-muted-foreground">
                    <Users class="h-12 w-12 opacity-20" />
                    <p class="mt-4">No teachers found matching your criteria.</p>
                </div>
            </div>

            <!-- Pagination -->
            <div class="flex flex-col gap-3 border-t px-4 py-3 md:flex-row md:items-center md:justify-between">
                <p class="text-sm text-muted-foreground">{{ pageLabel }}</p>
                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-medium text-muted-foreground">Per page:</label>
                        <select v-model="perPage" class="h-8 rounded-md border bg-background px-2 text-xs">
                            <option v-for="n in [15, 20, 50, 100, 200, 500]" :key="n" :value="n">{{ n }}</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <Button 
                            variant="outline" 
                            size="sm" 
                            :disabled="teachers.current_page <= 1"
                            @click="applyFilters(teachers.current_page - 1)"
                        >
                            Previous
                        </Button>
                        <span class="text-sm text-muted-foreground">
                            Page {{ teachers.current_page }} of {{ teachers.last_page }}
                        </span>
                        <Button 
                            variant="outline" 
                            size="sm" 
                            :disabled="teachers.current_page >= teachers.last_page"
                            @click="applyFilters(teachers.current_page + 1)"
                        >
                            Next
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Confirm Action Modal -->
        <div v-if="confirmOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="closeActionModal">
            <div class="w-full max-w-md rounded-xl border bg-background p-6 shadow-xl animate-in fade-in zoom-in duration-200">
                <h2 class="text-lg font-semibold">{{ confirmMode === 'delete' ? 'Delete Teacher' : 'Delete Selected Teachers' }}</h2>
                <p class="mt-2 text-sm text-muted-foreground">
                    {{ confirmMode === 'delete' 
                        ? `Are you sure you want to delete ${selectedTeacher?.full_name}? This will also delete their user account and all associated records.` 
                        : `Are you sure you want to delete ${selectedTeacherIds.length} teachers? This action cannot be undone.` 
                    }}
                </p>
                <div class="mt-6 flex justify-end gap-2">
                    <Button variant="outline" @click="closeActionModal">Cancel</Button>
                    <Button variant="destructive" :disabled="actionForm.processing" @click="confirmAction">
                        <Loader2 v-if="actionForm.processing" class="mr-2 h-4 w-4 animate-spin" />
                        {{ confirmMode === 'delete' ? 'Delete Teacher' : 'Delete All Selected' }}
                    </Button>
                </div>
            </div>
        </div>

        <TeacherBulkUploadModal v-model:open="bulkUploadOpen" />
    </AppLayout>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
