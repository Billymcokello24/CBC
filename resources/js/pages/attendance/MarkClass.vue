<script setup lang="ts">
/* global route */
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ChevronLeft,
    UserCheck,
    AlertCircle,
    Save,
    UserMinus,
    UserPlus,
    Clock,
    Info,
    CheckCircle2,
    Calendar,
    School,
    CheckSquare,
    ListChecks,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Input } from '@/components/ui/input';
import { ref, onMounted, watch } from 'vue';

const props = defineProps<{
    classroom: any;
    students: any[];
    existing: any;
    activeYear: any;
    activeTerm: any;
}>();

const form = useForm({
    class_id: props.classroom.id,
    academic_year_id: props.activeYear?.id,
    academic_term_id: props.activeTerm?.id,
    attendance_date: new Date().toISOString().split('T')[0],
    attendance: props.students.map((s) => ({
        student_id: s.id,
        status: props.existing[s.id]?.status || 'present',
        notes: props.existing[s.id]?.notes || '',
    })),
});

const submit = () => {
    form.post(route('attendance.store'), {
        preserveScroll: true,
        onSuccess: () => {
            // Success handled by Toast.vue globally
        },
    });
};

const updateStatus = (index: number, status: string) => {
    form.attendance[index].status = status;
    updateStats();
};

const markAll = (status: 'present' | 'absent') => {
    form.attendance.forEach((a) => (a.status = status));
    updateStats();
};

const stats = ref({
    present: 0,
    absent: 0,
    late: 0,
});

const updateStats = () => {
    stats.value.present = form.attendance.filter(
        (a) => a.status === 'present',
    ).length;
    stats.value.absent = form.attendance.filter(
        (a) => a.status === 'absent',
    ).length;
    stats.value.late = form.attendance.filter(
        (a) => a.status === 'late',
    ).length;
};

onMounted(updateStats);

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Attendance', href: '/attendance' },
    { title: props.classroom.name, href: '#' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Mark Attendance - ${classroom.name}`" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Pulsar Header -->
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/10"
                    >
                        <UserCheck class="h-6 w-6 text-emerald-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight uppercase">
                            Class Attendance: {{ classroom.name }}
                        </h1>
                        <div class="mt-1 flex items-center gap-2">
                            <p class="text-sm text-muted-foreground">
                                {{ classroom.grade_level?.name }} ·
                                {{ classroom.stream?.name }}
                            </p>
                            <div class="h-1 w-1 rounded-full bg-gray-300"></div>
                            <p class="text-sm font-bold text-emerald-600">
                                {{ students.length }} Student Population
                            </p>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div
                        class="hidden items-center gap-4 rounded-xl border border-gray-100 bg-gray-50 px-4 py-2 sm:flex"
                    >
                        <div class="flex items-center gap-2">
                            <span
                                class="h-2 w-2 rounded-full bg-emerald-500"
                            ></span>
                            <span
                                class="text-xs font-bold tracking-tighter text-gray-900 uppercase"
                                >{{ stats.present }}</span
                            >
                        </div>
                        <div class="flex items-center gap-2">
                            <span
                                class="h-2 w-2 rounded-full bg-rose-500"
                            ></span>
                            <span
                                class="text-xs font-bold tracking-tighter text-gray-900 uppercase"
                                >{{ stats.absent }}</span
                            >
                        </div>
                    </div>
                    <Button
                        @click="submit"
                        :disabled="form.processing"
                        class="h-12 rounded-xl bg-violet-600 px-8 text-xs font-bold tracking-tight text-white uppercase shadow-lg shadow-violet-100 transition-all hover:bg-violet-700 active:scale-95"
                    >
                        <Save v-if="!form.processing" class="mr-2 h-4 w-4" />
                        <span
                            v-else
                            class="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"
                        ></span>
                        {{ form.processing ? 'Syncing...' : 'Finalize Entry' }}
                    </Button>
                </div>
            </div>

            <!-- Bulk Actions -->
            <div
                class="flex items-center justify-between rounded-xl border border-dashed bg-card p-4"
            >
                <div class="flex items-center gap-3">
                    <ListChecks class="h-4 w-4 text-violet-600" />
                    <span
                        class="text-xs font-bold tracking-tight text-gray-400 uppercase"
                        >Batch Protocols</span
                    >
                </div>
                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        @click="markAll('present')"
                        class="h-9 rounded-lg px-4 text-xs font-medium tracking-tight uppercase"
                    >
                        Mark All Present
                    </Button>
                    <Button
                        variant="outline"
                        size="sm"
                        @click="markAll('absent')"
                        class="h-9 rounded-lg border-rose-100 px-4 text-xs font-medium tracking-tight text-rose-600 uppercase hover:bg-rose-50"
                    >
                        Mark All Absent
                    </Button>
                </div>
            </div>

            <!-- Attendance Checklist -->
            <div class="overflow-hidden rounded-xl border bg-card shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="border-b bg-gray-50/50 text-left">
                                <th
                                    class="p-6 text-xs font-bold tracking-tight text-gray-400 uppercase"
                                >
                                    Student Information
                                </th>
                                <th
                                    class="w-80 p-6 text-center text-xs font-bold tracking-tight text-gray-400 uppercase"
                                >
                                    Presence Status
                                </th>
                                <th
                                    class="p-6 text-xs font-bold tracking-tight text-gray-400 uppercase"
                                >
                                    Notes / Observations
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr
                                v-for="(student, index) in students"
                                :key="student.id"
                                class="group transition-colors hover:bg-gray-50/30"
                            >
                                <td class="p-6">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-xl border border-violet-100 bg-violet-600/5 text-xs font-bold text-violet-600 uppercase shadow-sm"
                                        >
                                            {{ student.first_name[0]
                                            }}{{ student.last_name[0] }}
                                        </div>
                                        <div>
                                            <p
                                                class="text-sm leading-none font-bold text-gray-900 transition-colors group-hover:text-violet-700"
                                            >
                                                {{ student.first_name }}
                                                {{ student.last_name }}
                                            </p>
                                            <p
                                                class="mt-1 text-xs font-bold tracking-tighter text-gray-400 uppercase"
                                            >
                                                {{ student.admission_number }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-6">
                                    <div
                                        class="flex w-full items-center justify-center gap-1.5 rounded-xl border bg-gray-50/50 p-1.5"
                                    >
                                        <button
                                            @click="
                                                updateStatus(index, 'present')
                                            "
                                            type="button"
                                            :class="[
                                                'flex-1 rounded-lg py-2 text-xs font-bold tracking-tight uppercase transition-all',
                                                form.attendance[index]
                                                    .status === 'present'
                                                    ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-100'
                                                    : 'text-gray-400 hover:bg-gray-100',
                                            ]"
                                        >
                                            Present
                                        </button>
                                        <button
                                            @click="
                                                updateStatus(index, 'absent')
                                            "
                                            type="button"
                                            :class="[
                                                'flex-1 rounded-lg py-2 text-xs font-bold tracking-tight uppercase transition-all',
                                                form.attendance[index]
                                                    .status === 'absent'
                                                    ? 'bg-rose-600 text-white shadow-lg shadow-rose-100'
                                                    : 'text-gray-400 hover:bg-gray-100',
                                            ]"
                                        >
                                            Absent
                                        </button>
                                        <button
                                            @click="updateStatus(index, 'late')"
                                            type="button"
                                            :class="[
                                                'flex-1 rounded-lg py-2 text-xs font-bold tracking-tight uppercase transition-all',
                                                form.attendance[index]
                                                    .status === 'late'
                                                    ? 'bg-amber-500 text-white shadow-lg shadow-amber-100'
                                                    : 'text-gray-400 hover:bg-gray-100',
                                            ]"
                                        >
                                            Late
                                        </button>
                                    </div>
                                </td>
                                <td class="p-6">
                                    <Input
                                        v-model="form.attendance[index].notes"
                                        placeholder="Add remark..."
                                        class="h-10 rounded-lg border-gray-100 bg-gray-50/30 text-xs transition-all hover:border-violet-200 hover:bg-white focus:border-violet-400 focus:bg-white"
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer Summary (Pulsar Style) -->
                <div
                    class="flex items-center justify-between border-t bg-gray-50/50 p-6"
                >
                    <div class="flex items-center gap-8">
                        <div class="flex items-center gap-2">
                            <Calendar class="h-4 w-4 text-violet-400" />
                            <span
                                class="text-xs font-bold tracking-tight text-gray-900 uppercase"
                                >{{ activeYear?.name }}</span
                            >
                        </div>
                        <div class="flex items-center gap-2">
                            <Clock class="h-4 w-4 text-violet-400" />
                            <span
                                class="text-xs font-bold tracking-tight text-gray-900 uppercase"
                                >{{ activeTerm?.name }}</span
                            >
                        </div>
                    </div>
                </div>
            </div>

            <!-- Caution Notice -->
            <div
                class="flex items-start gap-4 rounded-xl border border-violet-100 bg-violet-50 p-5"
            >
                <Info class="mt-0.5 h-5 w-5 shrink-0 text-violet-600" />
                <div>
                    <h4
                        class="mb-1 text-xs leading-none font-bold tracking-tight text-violet-900 uppercase"
                    >
                        Security Protocol
                    </h4>
                    <p
                        class="text-xs leading-relaxed font-medium tracking-tighter text-violet-800 uppercase opacity-80"
                    >
                        Synchronizing this data will update the academic ledger.
                        verified records are immutable after the current session
                        expires.
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
button {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>
