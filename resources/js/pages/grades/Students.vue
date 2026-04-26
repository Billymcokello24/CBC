<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import {
    ArrowDownCircle,
    CheckCircle2,
    Edit,
    Eye,
    Filter,
    MoreHorizontal,
    Search,
    ShieldAlert,
    Trash2,
    UserCheck,
    Users,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
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

interface GradeStudentRow {
    id: number;
    name: string;
    admission_number: string | null;
    gender: string;
    status: string;
    class_name: string | null;
    stream_name: string | null;
}

const props = defineProps<{
    grade: {
        id: number;
        name: string;
        code: string;
        category: string;
        level_order: number;
        is_active: boolean;
    };
    students: GradeStudentRow[];
    filters: {
        search: string;
        status: string;
    };
    statusOptions: Array<{ value: string; label: string }>;
    stats: {
        total: number;
        active: number;
    };
}>();

const page = usePage<{ flash?: { success?: string } }>();
const searchQuery = ref(props.filters.search ?? '');
const selectedStatus = ref(props.filters.status ?? 'all');
const showFilters = ref(true);
const showToast = ref(false);
const confirmOpen = ref(false);
const confirmMode = ref<'suspend' | 'activate' | 'delete' | 'demote'>(
    'suspend',
);
const selectedStudent = ref<GradeStudentRow | null>(null);
const actionForm = useForm({});
let toastTimer: ReturnType<typeof setTimeout> | null = null;
let debounceTimer: ReturnType<typeof setTimeout> | null = null;

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Grades', href: '/grades' },
    { title: props.grade.name, href: `/grades/${props.grade.id}` },
    { title: 'Students', href: `/grades/${props.grade.id}/students` },
];

const applyFilters = () => {
    router.get(
        `/grades/${props.grade.id}/students`,
        {
            search: searchQuery.value || undefined,
            status:
                selectedStatus.value !== 'all'
                    ? selectedStatus.value
                    : undefined,
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
    debounceTimer = setTimeout(() => applyFilters(), 300);
});

watch(selectedStatus, () => applyFilters());

const clearFilters = () => {
    searchQuery.value = '';
    selectedStatus.value = 'all';
    applyFilters();
};

const flashSuccess = computed(() => page.props.flash?.success ?? '');
watch(
    flashSuccess,
    (value) => {
        if (!value) return;
        showToast.value = true;
        if (toastTimer) clearTimeout(toastTimer);
        toastTimer = setTimeout(() => {
            showToast.value = false;
        }, 3000);
    },
    { immediate: true },
);

const openActionModal = (
    mode: 'suspend' | 'activate' | 'delete' | 'demote',
    student: GradeStudentRow,
) => {
    confirmMode.value = mode;
    selectedStudent.value = student;
    confirmOpen.value = true;
};

const closeActionModal = () => {
    confirmOpen.value = false;
    selectedStudent.value = null;
};

const confirmAction = () => {
    if (!selectedStudent.value) return;

    if (confirmMode.value === 'suspend') {
        actionForm.patch(`/students/${selectedStudent.value.id}/suspend`, {
            preserveScroll: true,
            onSuccess: closeActionModal,
        });
        return;
    }

    if (confirmMode.value === 'activate') {
        actionForm.patch(`/students/${selectedStudent.value.id}/activate`, {
            preserveScroll: true,
            onSuccess: closeActionModal,
        });
        return;
    }

    if (confirmMode.value === 'demote') {
        actionForm.patch(`/students/${selectedStudent.value.id}/demote`, {
            preserveScroll: true,
            onSuccess: closeActionModal,
        });
        return;
    }

    actionForm.delete(`/students/${selectedStudent.value.id}`, {
        preserveScroll: true,
        onSuccess: closeActionModal,
    });
};

const modalTitle = computed(() => {
    switch (confirmMode.value) {
        case 'activate':
            return 'Activate student';
        case 'delete':
            return 'Delete student';
        case 'demote':
            return 'Demote student';
        default:
            return 'Suspend student';
    }
});
</script>

<template>
    <Head :title="`${grade.name} Students`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="showToast && flashSuccess" class="fixed top-4 right-4 z-50">
            <div
                class="flex items-center gap-2 rounded-lg border bg-background px-4 py-3 shadow-lg"
            >
                <CheckCircle2 class="h-4 w-4 text-green-600" />
                <span class="text-sm font-medium">{{ flashSuccess }}</span>
            </div>
        </div>

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10"
                    >
                        <Users class="h-6 w-6 text-primary" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">
                            {{ grade.name }} Students
                        </h1>
                        <p class="text-muted-foreground">
                            {{ grade.code }} • {{ grade.category }} • Level
                            {{ grade.level_order }}
                        </p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" as-child
                        ><Link :href="`/grades/${grade.id}`"
                            >Back to Grade</Link
                        ></Button
                    >
                    <Button variant="outline" as-child
                        ><Link href="/students">All Students</Link></Button
                    >
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl border bg-card p-4">
                    <div class="text-sm text-muted-foreground">
                        Total Students
                    </div>
                    <div class="mt-2 text-2xl font-bold">{{ stats.total }}</div>
                </div>
                <div class="rounded-xl border bg-card p-4">
                    <div class="text-sm text-muted-foreground">
                        Active Students
                    </div>
                    <div class="mt-2 text-2xl font-bold text-green-600">
                        {{ stats.active }}
                    </div>
                </div>
            </div>

            <div
                class="flex flex-col gap-4 rounded-xl border bg-card p-4 md:flex-row md:items-center"
            >
                <div class="relative flex-1 md:max-w-sm">
                    <Search
                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        v-model="searchQuery"
                        placeholder="Search student or admission number..."
                        class="pl-9"
                    />
                </div>
                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        @click="showFilters = !showFilters"
                        ><Filter class="mr-2 h-4 w-4" />{{
                            showFilters ? 'Hide Filters' : 'Show Filters'
                        }}</Button
                    >
                    <Button variant="outline" size="sm" @click="clearFilters"
                        >Reset</Button
                    >
                </div>
            </div>

            <div
                v-if="showFilters"
                class="grid gap-4 rounded-xl border bg-card p-4 md:grid-cols-1"
            >
                <div class="space-y-2">
                    <label class="text-sm font-medium">Student Status</label>
                    <select
                        v-model="selectedStatus"
                        class="h-10 w-full rounded-md border bg-background px-3 text-sm"
                    >
                        <option
                            v-for="option in statusOptions"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </option>
                    </select>
                </div>
            </div>

            <div class="rounded-xl border bg-card">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b bg-muted/50">
                                <th
                                    class="px-4 py-3 text-left text-sm font-medium text-muted-foreground"
                                >
                                    Student
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-sm font-medium text-muted-foreground"
                                >
                                    Adm. No
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-sm font-medium text-muted-foreground"
                                >
                                    Class
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-sm font-medium text-muted-foreground"
                                >
                                    Gender
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-sm font-medium text-muted-foreground"
                                >
                                    Status
                                </th>
                                <th
                                    class="px-4 py-3 text-right text-sm font-medium text-muted-foreground"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="students.length === 0">
                                <td
                                    colspan="6"
                                    class="px-4 py-10 text-center text-sm text-muted-foreground"
                                >
                                    No students found in this grade.
                                </td>
                            </tr>
                            <tr
                                v-for="student in students"
                                :key="student.id"
                                class="border-b transition-colors hover:bg-muted/50"
                            >
                                <td class="px-4 py-3 font-medium">
                                    {{ student.name }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ student.admission_number || '—' }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ student.class_name || '—'
                                    }}<span v-if="student.stream_name">
                                        • {{ student.stream_name }}</span
                                    >
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    {{ student.gender }}
                                </td>
                                <td class="px-4 py-3">
                                    <Badge
                                        :variant="
                                            student.status === 'active'
                                                ? 'default'
                                                : 'secondary'
                                        "
                                        >{{ student.status }}</Badge
                                    >
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child
                                            ><Button
                                                variant="ghost"
                                                size="icon"
                                                class="h-8 w-8"
                                                ><MoreHorizontal
                                                    class="h-4 w-4" /></Button
                                        ></DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuItem as-child
                                                ><Link
                                                    :href="`/students/${student.id}`"
                                                    ><Eye
                                                        class="mr-2 h-4 w-4"
                                                    />View</Link
                                                ></DropdownMenuItem
                                            >
                                            <DropdownMenuItem as-child
                                                ><Link
                                                    :href="`/students/${student.id}/edit`"
                                                    ><Edit
                                                        class="mr-2 h-4 w-4"
                                                    />Edit</Link
                                                ></DropdownMenuItem
                                            >
                                            <DropdownMenuItem
                                                v-if="
                                                    student.status !==
                                                    'suspended'
                                                "
                                                @click="
                                                    openActionModal(
                                                        'suspend',
                                                        student,
                                                    )
                                                "
                                                ><ShieldAlert
                                                    class="mr-2 h-4 w-4"
                                                />Suspend</DropdownMenuItem
                                            >
                                            <DropdownMenuItem
                                                v-else
                                                @click="
                                                    openActionModal(
                                                        'activate',
                                                        student,
                                                    )
                                                "
                                                ><UserCheck
                                                    class="mr-2 h-4 w-4"
                                                />Activate</DropdownMenuItem
                                            >
                                            <DropdownMenuItem
                                                @click="
                                                    openActionModal(
                                                        'demote',
                                                        student,
                                                    )
                                                "
                                                ><ArrowDownCircle
                                                    class="mr-2 h-4 w-4"
                                                />Demote</DropdownMenuItem
                                            >
                                            <DropdownMenuItem
                                                class="text-destructive"
                                                @click="
                                                    openActionModal(
                                                        'delete',
                                                        student,
                                                    )
                                                "
                                                ><Trash2
                                                    class="mr-2 h-4 w-4"
                                                />Delete</DropdownMenuItem
                                            >
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div
            v-if="confirmOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            @click.self="closeActionModal"
        >
            <div
                class="w-full max-w-md rounded-xl border bg-background p-6 shadow-xl"
            >
                <h2 class="text-lg font-semibold">{{ modalTitle }}</h2>
                <p
                    class="mt-2 text-sm text-muted-foreground"
                    v-if="selectedStudent"
                >
                    {{
                        confirmMode === 'activate'
                            ? `Activate ${selectedStudent.name}?`
                            : confirmMode === 'delete'
                              ? `Delete ${selectedStudent.name}?`
                              : confirmMode === 'demote'
                                ? `Demote ${selectedStudent.name}?`
                                : `Suspend ${selectedStudent.name}?`
                    }}
                </p>
                <div class="mt-6 flex justify-end gap-2">
                    <Button variant="outline" @click="closeActionModal"
                        >Cancel</Button
                    >
                    <Button
                        :variant="
                            confirmMode === 'delete' ? 'destructive' : 'default'
                        "
                        :disabled="actionForm.processing"
                        @click="confirmAction"
                        >Confirm</Button
                    >
                </div>
            </div>
        </div>
    </AppLayout>
</template>
