<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    BookOpenCheck,
    ChevronDown,
    ChevronUp,
    Filter,
    Grid2x2,
    List,
    Search,
    Users,
    UserPlus,
    GraduationCap,
} from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface EnrollmentGroup {
    class_id: number;
    class_name: string;
    grade_name: string | null;
    stream_name: string | null;
    academic_year: string | null;
    total_learners: number;
    active_learners: number;
    new_enrollments: number;
    promoted_learners: number;
}

interface Props {
    groups: EnrollmentGroup[];
    stats: {
        total: number;
        active: number;
        new: number;
        promoted: number;
    };
    filters: {
        search: string;
        status: string;
        enrollment_type: string;
        class_id: number | null;
        academic_year_id: number | null;
        view: 'grid' | 'list';
    };
    classes: Array<{ id: number; name: string }>;
    academicYears: Array<{ id: number; name: string }>;
    statusOptions: Array<{ value: string; label: string }>;
    enrollmentTypeOptions: Array<{ value: string; label: string }>;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Learner Registry', href: '/students' },
    { title: 'Enrollments', href: '/students/enrollments' },
];

const searchQuery = ref(props.filters.search ?? '');
const selectedStatus = ref(props.filters.status ?? 'all');
const selectedEnrollmentType = ref(props.filters.enrollment_type ?? 'all');
const selectedClassId = ref(props.filters.class_id ? String(props.filters.class_id) : '');
const selectedAcademicYearId = ref(props.filters.academic_year_id ? String(props.filters.academic_year_id) : '');
const selectedView = ref<'grid' | 'list'>(props.filters.view ?? 'grid');
const showFilters = ref(true);
let debounceTimer: ReturnType<typeof setTimeout> | null = null;

const applyFilters = () => {
    router.get(
        '/students/enrollments',
        {
            search: searchQuery.value || undefined,
            status: selectedStatus.value !== 'all' ? selectedStatus.value : undefined,
            enrollment_type: selectedEnrollmentType.value !== 'all' ? selectedEnrollmentType.value : undefined,
            class_id: selectedClassId.value || undefined,
            academic_year_id: selectedAcademicYearId.value || undefined,
            view: selectedView.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

watch(searchQuery, () => {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => applyFilters(), 350);
});
watch([selectedStatus, selectedEnrollmentType, selectedClassId, selectedAcademicYearId, selectedView], () => applyFilters());

const clearFilters = () => {
    searchQuery.value = '';
    selectedStatus.value = 'all';
    selectedEnrollmentType.value = 'all';
    selectedClassId.value = '';
    selectedAcademicYearId.value = '';
    applyFilters();
};
</script>

<template>
    <Head title="Enrollment Groups" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-600/10">
                        <GraduationCap class="h-6 w-6 text-indigo-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Enrollment Registry</h1>
                        <p class="text-muted-foreground">Formal academic assignments and class history.</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <Button variant="outline" as-child>
                        <Link href="/students">View Learners</Link>
                    </Button>
                    <Button as-child class="bg-indigo-600 hover:bg-indigo-700 text-white shadow-lg shadow-indigo-600/20">
                        <Link href="/students/enrollments/create">
                            <UserPlus class="mr-2 h-4 w-4" /> Enroll Learner
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border bg-card p-4">
                    <div class="text-sm text-muted-foreground">Total Enrollments</div>
                    <div class="mt-1 text-3xl font-bold">{{ stats.total.toLocaleString() }}</div>
                </div>
                <div class="rounded-xl border bg-card p-4">
                    <div class="text-sm text-muted-foreground">Active</div>
                    <div class="mt-1 text-3xl font-bold text-green-600">{{ stats.active.toLocaleString() }}</div>
                </div>
                <div class="rounded-xl border bg-card p-4">
                    <div class="text-sm text-muted-foreground">New</div>
                    <div class="mt-1 text-3xl font-bold text-blue-600">{{ stats.new.toLocaleString() }}</div>
                </div>
                <div class="rounded-xl border bg-card p-4">
                    <div class="text-sm text-muted-foreground">Promoted</div>
                    <div class="mt-1 text-3xl font-bold text-amber-600">{{ stats.promoted.toLocaleString() }}</div>
                </div>
            </div>

            <div class="flex flex-col gap-4 rounded-xl border bg-card p-4 md:flex-row md:items-center">
                <div class="relative flex-1 md:max-w-sm">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input v-model="searchQuery" placeholder="Search..." class="pl-9" />
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" @click="selectedView = 'grid'">
                        <Grid2x2 class="mr-2 h-4 w-4" /> Grid
                    </Button>
                    <Button variant="outline" size="sm" @click="selectedView = 'list'">
                        <List class="mr-2 h-4 w-4" /> List
                    </Button>
                    <Button variant="outline" size="sm" @click="showFilters = !showFilters">
                        <component :is="showFilters ? ChevronUp : ChevronDown" class="mr-2 h-4 w-4" />
                        {{ showFilters ? 'Hide Filters' : 'Show Filters' }}
                    </Button>
                    <Button variant="outline" size="sm" @click="clearFilters">
                        <Filter class="mr-2 h-4 w-4" /> Reset
                    </Button>
                </div>
            </div>

            <div v-if="showFilters" class="grid gap-4 rounded-xl border bg-card p-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="space-y-2">
                    <label class="text-sm font-medium">Status</label>
                    <select v-model="selectedStatus" class="h-10 w-full rounded-md border bg-background px-3 text-sm">
                        <option v-for="option in statusOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium">Enrollment Type</label>
                    <select v-model="selectedEnrollmentType" class="h-10 w-full rounded-md border bg-background px-3 text-sm">
                        <option v-for="option in enrollmentTypeOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium">Class</label>
                    <select v-model="selectedClassId" class="h-10 w-full rounded-md border bg-background px-3 text-sm">
                        <option value="">All Classes</option>
                        <option v-for="schoolClass in classes" :key="schoolClass.id" :value="String(schoolClass.id)">{{ schoolClass.name }}</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium">Academic Year</label>
                    <select v-model="selectedAcademicYearId" class="h-10 w-full rounded-md border bg-background px-3 text-sm">
                        <option value="">All Academic Years</option>
                        <option v-for="year in academicYears" :key="year.id" :value="String(year.id)">{{ year.name }}</option>
                    </select>
                </div>
            </div>

            <div v-if="groups.length === 0" class="rounded-xl border bg-card p-10 text-center text-sm text-muted-foreground">
                No enrollments found.
            </div>

            <div v-else-if="selectedView === 'grid'" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <Link
                    v-for="group in groups"
                    :key="group.class_id"
                    :href="`/students/enrollments/groups/${group.class_id}`"
                    class="rounded-xl border bg-card p-5 transition hover:border-primary/40 hover:shadow-md"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold">{{ group.class_name }}</h2>
                            <p class="text-sm text-muted-foreground">
                                {{ group.grade_name || 'Unknown Grade' }}<span v-if="group.stream_name"> • {{ group.stream_name }}</span>
                            </p>
                            <p class="mt-1 text-xs text-muted-foreground">{{ group.academic_year || 'No year set' }}</p>
                        </div>
                        <div class="rounded-full bg-primary/10 p-2 text-primary">
                            <Users class="h-4 w-4" />
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                        <div class="rounded-lg border p-3">
                            <div class="text-muted-foreground">Learners</div>
                            <div class="mt-1 font-semibold">{{ group.total_learners }}</div>
                        </div>
                        <div class="rounded-lg border p-3">
                            <div class="text-muted-foreground">Active</div>
                            <div class="mt-1 font-semibold text-green-600">{{ group.active_learners }}</div>
                        </div>
                        <div class="rounded-lg border p-3">
                            <div class="text-muted-foreground">New</div>
                            <div class="mt-1 font-semibold text-blue-600">{{ group.new_enrollments }}</div>
                        </div>
                        <div class="rounded-lg border p-3">
                            <div class="text-muted-foreground">Promoted</div>
                            <div class="mt-1 font-semibold text-amber-600">{{ group.promoted_learners }}</div>
                        </div>
                    </div>
                </Link>
            </div>

            <div v-else class="rounded-xl border bg-card">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b bg-muted/50">
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Group</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Academic Year</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Learners</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Active</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Promoted</th>
                                <th class="px-4 py-3 text-right text-sm font-medium text-muted-foreground">Open</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="group in groups" :key="group.class_id" class="border-b transition-colors hover:bg-muted/50">
                                <td class="px-4 py-3">
                                    <div class="font-medium">{{ group.class_name }}</div>
                                    <div class="text-xs text-muted-foreground">{{ group.grade_name || 'Unknown Grade' }}<span v-if="group.stream_name"> • {{ group.stream_name }}</span></div>
                                </td>
                                <td class="px-4 py-3 text-sm">{{ group.academic_year || '—' }}</td>
                                <td class="px-4 py-3 text-sm">{{ group.total_learners }}</td>
                                <td class="px-4 py-3 text-sm text-green-600">{{ group.active_learners }}</td>
                                <td class="px-4 py-3 text-sm text-amber-600">{{ group.promoted_learners }}</td>
                                <td class="px-4 py-3 text-right">
                                    <Button variant="outline" size="sm" as-child>
                                        <Link :href="`/students/enrollments/groups/${group.class_id}`">Open</Link>
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
