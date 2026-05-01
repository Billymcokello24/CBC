<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import {
    ClipboardList,
    Save,
    ArrowLeft,
    User,
    CheckCircle2,
    AlertCircle,
    Search,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
} from '@/components/ui/card';
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    assessment: any;
    students: Array<any>;
    existingResults: Record<number, any>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Assessments', href: '/assessments' },
    { title: 'Grading', href: '#' },
];

const searchQuery = ref('');
const filteredStudents = computed(() => {
    if (!searchQuery.value) return props.students;
    const query = searchQuery.value.toLowerCase();
    return props.students.filter(
        (s) =>
            `${s.first_name} ${s.last_name}`.toLowerCase().includes(query) ||
            s.admission_number.toLowerCase().includes(query),
    );
});

const form = useForm({
    results: props.students.map((student) => ({
        student_id: student.id,
        marks_obtained: props.existingResults[student.id]?.marks_obtained ?? '',
        feedback: props.existingResults[student.id]?.feedback ?? '',
        is_absent: props.existingResults[student.id]?.is_absent ?? false,
    })),
});

const getGradeLevel = (marks: number | string) => {
    if (marks === '' || marks === null) return null;
    const score = Number(marks);
    const descriptors = props.assessment.grading_scale?.descriptors || [];
    return descriptors.find(
        (d: any) => score >= d.min_score && score <= d.max_score,
    );
};

const getResultByStudentId = (studentId: number) => {
    return form.results.find((r) => r.student_id === studentId);
};

const submit = () => {
    form.post(`/assessments/${props.assessment.id}/grading`);
};

const autoSave = (index: number) => {
    // In a real app, you might want to implement debounced auto-save per student
};
</script>

<template>
    <Head :title="`Grading - ${assessment.title}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex h-full max-w-6xl flex-1 flex-col gap-6 p-6">
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <Button
                        variant="ghost"
                        size="icon"
                        as-child
                        class="rounded-full"
                    >
                        <Link href="/assessments"
                            ><ArrowLeft class="h-5 w-5"
                        /></Link>
                    </Button>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl font-bold tracking-tight">
                                {{ assessment.title }}
                            </h1>
                            <Badge variant="outline">{{
                                assessment.assessment_type?.name
                            }}</Badge>
                        </div>
                        <p class="text-sm text-muted-foreground">
                            {{ assessment.class?.name }} •
                            {{ assessment.subject?.name }} • Max Marks:
                            {{ assessment.total_marks }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="hidden text-right md:block">
                        <p class="text-xs font-medium text-muted-foreground">
                            Grading Progress
                        </p>
                        <p class="text-sm font-bold text-indigo-600">
                            {{
                                form.results.filter(
                                    (r) =>
                                        r.marks_obtained !== '' || r.is_absent,
                                ).length
                            }}
                            / {{ students.length }} Students
                        </p>
                    </div>
                    <Button
                        @click="submit"
                        :disabled="form.processing"
                        class="h-10 bg-indigo-600 px-6 font-bold hover:bg-indigo-700"
                    >
                        <Save class="mr-2 h-4 w-4" />
                        Save All Grades
                    </Button>
                </div>
            </div>

            <div class="flex items-center justify-between gap-4">
                <div class="relative max-w-sm flex-1">
                    <Search
                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        v-model="searchQuery"
                        placeholder="Search students..."
                        class="border-indigo-50 bg-muted/20 pl-9"
                    />
                </div>
                <div
                    class="flex items-center gap-4 rounded-lg bg-muted/30 px-4 py-2 text-xs font-medium text-muted-foreground"
                >
                    <span class="flex items-center gap-1"
                        ><span class="h-2 w-2 rounded-full bg-green-500"></span>
                        EE</span
                    >
                    <span class="flex items-center gap-1"
                        ><span class="h-2 w-2 rounded-full bg-blue-500"></span>
                        ME</span
                    >
                    <span class="flex items-center gap-1"
                        ><span class="h-2 w-2 rounded-full bg-amber-500"></span>
                        AE</span
                    >
                    <span class="flex items-center gap-1"
                        ><span class="h-2 w-2 rounded-full bg-red-500"></span>
                        BE</span
                    >
                </div>
            </div>

            <Card class="overflow-hidden border-indigo-100 shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b bg-muted/50">
                                <th
                                    class="px-6 py-4 text-left text-sm font-bold tracking-wider text-muted-foreground "
                                >
                                    Student
                                </th>
                                <th
                                    class="w-32 px-6 py-4 text-center text-sm font-bold tracking-wider text-muted-foreground "
                                >
                                    Marks / {{ assessment.total_marks }}
                                </th>
                                <th
                                    class="w-24 px-6 py-4 text-center text-sm font-bold tracking-wider text-muted-foreground "
                                >
                                    Level
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-sm font-bold tracking-wider text-muted-foreground "
                                >
                                    Feedback / Remarks
                                </th>
                                <th
                                    class="w-24 px-6 py-4 text-center text-sm font-bold tracking-wider text-muted-foreground "
                                >
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(student, index) in filteredStudents"
                                :key="student.id"
                                class="border-b transition-colors last:border-0 hover:bg-indigo-50/30"
                                :class="{
                                    'bg-red-50/30': getResultByStudentId(
                                        student.id,
                                    )?.is_absent,
                                }"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <Avatar
                                            class="h-10 w-10 border-2 border-white shadow-sm"
                                        >
                                            <AvatarImage :src="student.photo" />
                                            <AvatarFallback
                                                class="bg-indigo-100 font-bold text-indigo-700"
                                            >
                                                {{ student.first_name[0]
                                                }}{{ student.last_name[0] }}
                                            </AvatarFallback>
                                        </Avatar>
                                        <div>
                                            <div
                                                class="font-bold text-foreground capitalize"
                                            >
                                                {{ student.first_name }}
                                                {{ student.last_name }}
                                            </div>
                                            <div
                                                class="text-xs font-medium text-muted-foreground"
                                            >
                                                ADM:
                                                {{ student.admission_number }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div
                                        class="flex justify-center"
                                        v-if="getResultByStudentId(student.id)"
                                    >
                                        <Input
                                            v-model.number="
                                                getResultByStudentId(
                                                    student.id,
                                                )!.marks_obtained
                                            "
                                            type="number"
                                            :max="assessment.total_marks"
                                            min="0"
                                            class="h-10 w-20 border-indigo-200 text-center text-lg font-bold"
                                            :disabled="
                                                getResultByStudentId(
                                                    student.id,
                                                )!.is_absent
                                            "
                                            @input="autoSave(index)"
                                        />
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <template
                                        v-if="
                                            getResultByStudentId(student.id) &&
                                            !getResultByStudentId(student.id)!
                                                .is_absent
                                        "
                                    >
                                        <Badge
                                            v-if="
                                                getGradeLevel(
                                                    getResultByStudentId(
                                                        student.id,
                                                    )!.marks_obtained,
                                                )
                                            "
                                            :style="{
                                                backgroundColor:
                                                    getGradeLevel(
                                                        getResultByStudentId(
                                                            student.id,
                                                        )!.marks_obtained,
                                                    ).color + '20',
                                                color: getGradeLevel(
                                                    getResultByStudentId(
                                                        student.id,
                                                    )!.marks_obtained,
                                                ).color,
                                            }"
                                            class="border-0 font-bold"
                                        >
                                            {{
                                                getGradeLevel(
                                                    getResultByStudentId(
                                                        student.id,
                                                    )!.marks_obtained,
                                                ).level_code
                                            }}
                                        </Badge>
                                        <span
                                            v-else
                                            class="text-xs font-medium text-muted-foreground"
                                            >-</span
                                        >
                                    </template>
                                    <span
                                        v-else-if="
                                            getResultByStudentId(student.id)
                                                ?.is_absent
                                        "
                                        class="text-xs font-bold text-red-500"
                                        >N/A</span
                                    >
                                    <span
                                        v-else
                                        class="text-xs font-medium text-muted-foreground"
                                        >-</span
                                    >
                                </td>
                                <td class="px-6 py-4">
                                    <Input
                                        v-if="getResultByStudentId(student.id)"
                                        v-model="
                                            getResultByStudentId(student.id)!
                                                .feedback
                                        "
                                        placeholder="Add a remark..."
                                        class="h-9 border-transparent bg-transparent px-0 transition-all hover:border-muted-foreground/20 focus:border-indigo-400 focus:bg-white focus:px-3"
                                    />
                                </td>
                                <td class="px-6 py-4">
                                    <div
                                        class="flex flex-col items-center justify-center gap-1"
                                        v-if="getResultByStudentId(student.id)"
                                    >
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            class="h-8 px-2 text-xs font-bold tracking-wider "
                                            :class="
                                                getResultByStudentId(
                                                    student.id,
                                                )!.is_absent
                                                    ? 'bg-red-50 text-red-600'
                                                    : 'text-muted-foreground hover:text-red-600'
                                            "
                                            @click="
                                                () => {
                                                    const res =
                                                        getResultByStudentId(
                                                            student.id,
                                                        );
                                                    if (res) {
                                                        res.is_absent =
                                                            !res.is_absent;
                                                        if (res.is_absent)
                                                            res.marks_obtained =
                                                                '';
                                                    }
                                                }
                                            "
                                        >
                                            {{
                                                getResultByStudentId(
                                                    student.id,
                                                )!.is_absent
                                                    ? 'Absent'
                                                    : 'Mark Absent'
                                            }}
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </Card>

            <div
                class="flex items-center justify-between rounded-2xl bg-indigo-900 p-6 text-white shadow-xl shadow-indigo-200"
            >
                <div class="flex items-center gap-6">
                    <div class="flex flex-col">
                        <span
                            class="text-xs font-bold tracking-tight text-indigo-300 "
                            >Total Students</span
                        >
                        <span class="text-2xl font-bold">{{
                            students.length
                        }}</span>
                    </div>
                    <div class="h-10 w-px bg-indigo-700"></div>
                    <div class="flex flex-col">
                        <span
                            class="text-xs font-bold tracking-tight text-green-400 "
                            >Graded</span
                        >
                        <span class="text-2xl font-bold text-green-400">{{
                            form.results.filter(
                                (r) => r.marks_obtained !== '' || r.is_absent,
                            ).length
                        }}</span>
                    </div>
                    <div class="h-10 w-px bg-indigo-700"></div>
                    <div class="flex flex-col">
                        <span
                            class="text-xs font-bold tracking-tight text-amber-400 "
                            >Remaining</span
                        >
                        <span class="text-2xl font-bold text-amber-400">{{
                            students.length -
                            form.results.filter(
                                (r) => r.marks_obtained !== '' || r.is_absent,
                            ).length
                        }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <p class="mr-4 text-sm font-medium text-indigo-200">
                        Don't forget to save your progress!
                    </p>
                    <Button
                        @click="submit"
                        :disabled="form.processing"
                        class="h-12 rounded-xl bg-green-500 px-8 font-bold shadow-lg shadow-green-900/40 hover:bg-green-600"
                    >
                        <CheckCircle2 class="mr-2 h-5 w-5" />
                        SUBMIT ALL GRADES
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Chrome, Safari, Edge, Opera */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    appearance: none;
    margin: 0;
}

/* Firefox */
input[type='number'] {
    -moz-appearance: textfield;
    appearance: textfield;
}
</style>
