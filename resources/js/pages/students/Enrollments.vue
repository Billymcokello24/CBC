<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { BookOpenCheck, ChevronDown, ChevronUp, Filter, Search } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { BreadcrumbItem } from '@/types';

interface EnrollmentRow {
    id: number;
    student_id: number;
    student_name: string;
    admission_number: string | null;
    student_status: string | null;
    class_name: string | null;
    academic_year: string | null;
    academic_term: string | null;
    enrollment_date: string | null;
    end_date: string | null;
    enrollment_type: string;
    status: string;
    roll_number: string | null;
    notes: string | null;
    enrolled_by: string | null;
}

interface PaginatedEnrollments {
    data: EnrollmentRow[];
    current_page: number;
    last_page: number;
    total: number;
    from: number | null;
    to: number | null;
}

interface Props {
    enrollments: PaginatedEnrollments;
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
        per_page: number;
    };
    classes: Array<{ id: number; name: string }>;
    academicYears: Array<{ id: number; name: string }>;
    statusOptions: Array<{ value: string; label: string }>;
    enrollmentTypeOptions: Array<{ value: string; label: string }>;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Students', href: '/students' },
    { title: 'Enrollments', href: '/students/enrollments' },
];

const searchQuery = ref(props.filters.search ?? '');
const selectedStatus = ref(props.filters.status ?? 'all');
const selectedEnrollmentType = ref(props.filters.enrollment_type ?? 'all');
const selectedClassId = ref(props.filters.class_id ? String(props.filters.class_id) : '');
const selectedAcademicYearId = ref(props.filters.academic_year_id ? String(props.filters.academic_year_id) : '');
const showFilters = ref(true);

let debounceTimer: ReturnType<typeof setTimeout> | null = null;

const applyFilters = (page = 1) => {
    router.get(
        '/students/enrollments',
        {
            search: searchQuery.value || undefined,
            status: selectedStatus.value !== 'all' ? selectedStatus.value : undefined,
            enrollment_type: selectedEnrollmentType.value !== 'all' ? selectedEnrollmentType.value : undefined,
            class_id: selectedClassId.value || undefined,
            academic_year_id: selectedAcademicYearId.value || undefined,
            page,
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

watch([selectedStatus, selectedEnrollmentType, selectedClassId, selectedAcademicYearId], () => applyFilters());

const clearFilters = () => {
    searchQuery.value = '';
    selectedStatus.value = 'all';
    selectedEnrollmentType.value = 'all';
    selectedClassId.value = '';
    selectedAcademicYearId.value = '';
    applyFilters();
};

const pageLabel = computed(() => {
    const from = props.enrollments.from ?? 0;
    const to = props.enrollments.to ?? 0;
    return `Showing ${from} to ${to} of ${props.enrollments.total} enrollments`;
});
</script>

<template>
    <Head title="Student Enrollments" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/10">
                        <BookOpenCheck class="h-6 w-6 text-emerald-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Student Enrollments</h1>
                        <p class="text-muted-foreground">Track class enrollment history from the live database</p>
                    </div>
                </div>
                <Button variant="outline" as-child>
                    <Link href="/students">Back to Students</Link>
                </Button>
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
                    <Input v-model="searchQuery" placeholder="Search student or admission number..." class="pl-9" />
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" @click="showFilters = !showFilters">
                        <component :is="showFilters ? ChevronUp : ChevronDown" class="mr-2 h-4 w-4" />
                        {{ showFilters ? 'Hide Filters' : 'Show Filters' }}
                    </Button>
                    <Button variant="outline" size="sm" @click="clearFilters">
                        <Filter class="mr-2 h-4 w-4" />
                        Reset
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

            <div class="rounded-xl border bg-card">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b bg-muted/50">
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Student</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Admission No</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Class</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Year / Term</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Enrollment Type</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Status</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Date</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="enrollments.data.length === 0">
                                <td colspan="8" class="px-4 py-10 text-center text-sm text-muted-foreground">
                                    No enrollment records found.
                                </td>
                            </tr>
                            <tr v-for="enrollment in enrollments.data" :key="enrollment.id" class="border-b transition-colors hover:bg-muted/50">
                                <td class="px-4 py-3 font-medium">{{ enrollment.student_name }}</td>
                                <td class="px-4 py-3 text-sm">{{ enrollment.admission_number || '—' }}</td>
                                <td class="px-4 py-3 text-sm">{{ enrollment.class_name || '—' }}</td>
                                <td class="px-4 py-3 text-sm">{{ enrollment.academic_year || '—' }}<span v-if="enrollment.academic_term"> / {{ enrollment.academic_term }}</span></td>
                                <td class="px-4 py-3 text-sm capitalize">{{ enrollment.enrollment_type.replace('_', ' ') }}</td>
                                <td class="px-4 py-3">
                                    <Badge :variant="enrollment.status === 'active' ? 'default' : 'secondary'">{{ enrollment.status }}</Badge>
                                </td>
                                <td class="px-4 py-3 text-sm">{{ enrollment.enrollment_date || '—' }}</td>
                                <td class="px-4 py-3 text-sm">{{ enrollment.enrolled_by || 'System' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="flex flex-col gap-3 border-t px-4 py-3 md:flex-row md:items-center md:justify-between">
                    <p class="text-sm text-muted-foreground">{{ pageLabel }}</p>
                    <div class="flex items-center gap-2">
                        <Button variant="outline" size="sm" :disabled="enrollments.current_page <= 1" @click="applyFilters(enrollments.current_page - 1)">Previous</Button>
                        <span class="text-sm text-muted-foreground">Page {{ enrollments.current_page }} of {{ enrollments.last_page }}</span>
                        <Button variant="outline" size="sm" :disabled="enrollments.current_page >= enrollments.last_page" @click="applyFilters(enrollments.current_page + 1)">Next</Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
