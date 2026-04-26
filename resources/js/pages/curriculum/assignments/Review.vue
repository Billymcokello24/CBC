<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import {
    Target,
    ArrowLeft,
    User,
    Calendar,
    Download,
    CheckCircle2,
    ChevronRight,
    MessageSquare,
    Eye,
    FileText,
    ChevronLeft,
} from 'lucide-vue-next';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import MarkingTool from '@/components/curriculum/assignments/MarkingTool.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    assignment: any;
    submission: any;
    prevId?: number;
    nextId?: number;
}>();

// Defensive breadcrumbs
const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Assignments', href: '/curriculum/assignments' },
    {
        title: 'Submissions',
        href: props.assignment
            ? `/curriculum/assignments/${props.assignment.id}/submissions`
            : '#',
    },
    {
        title: props.submission?.student?.first_name
            ? `${props.submission.student.first_name}'s Profile`
            : 'Assessment',
        href: '#',
    },
]);

const gradingForm = useForm({
    marks_obtained: props.submission?.marks_obtained || 0,
    feedback: props.submission?.feedback || '',
    annotations: props.submission?.private_notes
        ? JSON.parse(props.submission.private_notes)
        : [],
    marked_images: [] as string[], // Flattened snapshots
});

const markingToolRef = ref<any>(null);

const onAnnotationsSaved = async (annotations: any[]) => {
    gradingForm.annotations = annotations;

    // Capture high-fidelity snapshots from the client-side canvas
    if (markingToolRef.value) {
        gradingForm.marked_images =
            await markingToolRef.value.getFlattenedSnapshots();
    }

    // Auto-sync with server immediately to prevent data loss
    gradingForm.post(
        route('curriculum.assignments.submissions.grade', props.submission.id),
        {
            preserveScroll: true,
            onSuccess: () => {
                // Optional: Show a fleeting success state in the tool if we had a ref to it
            },
        },
    );
};

const goToSubmission = (id: number) => {
    router.get(
        route('curriculum.assignments.submissions.review', {
            assignment: props.assignment.id,
            submission: id,
        }),
    );
};

const getStatusBadge = (status: string) => {
    switch (status) {
        case 'graded':
            return 'bg-emerald-50 text-emerald-600 border-emerald-100';
        case 'submitted':
            return 'bg-blue-50 text-blue-600 border-blue-100';
        default:
            return 'bg-slate-50 text-slate-600 border-slate-100';
    }
};
</script>

<template>
    <Head
        :title="
            submission?.student?.first_name
                ? `${submission.student.first_name} - Assessment Studio`
                : 'Assessment Studio'
        "
    />
    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Robust v-if guard to prevent white page crashes -->
        <div
            v-if="submission && assignment"
            class="flex h-screen animate-in flex-col overflow-hidden bg-slate-50 duration-500 fade-in"
        >
            <!-- Context Header: Fixed at top -->
            <div
                class="z-10 flex flex-col justify-between gap-4 border-b border-slate-100 bg-white p-4 shadow-sm md:flex-row md:items-center"
            >
                <div class="flex items-center gap-4">
                    <Link
                        :href="
                            route(
                                'curriculum.assignments.submissions',
                                assignment.id,
                            )
                        "
                    >
                        <Button
                            variant="ghost"
                            size="icon"
                            class="h-10 w-10 rounded-xl transition-all hover:bg-slate-100"
                        >
                            <ArrowLeft class="h-5 w-5 text-slate-500" />
                        </Button>
                    </Link>
                    <div class="h-8 w-px bg-slate-100"></div>

                    <!-- Rapid Navigator Controls -->
                    <div
                        class="mr-2 flex items-center gap-2 rounded-xl border border-slate-100 bg-slate-50 p-1"
                    >
                        <Button
                            variant="ghost"
                            size="icon"
                            class="h-8 w-8 rounded-lg"
                            :disabled="!prevId"
                            @click="goToSubmission(prevId as number)"
                        >
                            <ChevronLeft class="h-4 w-4" />
                        </Button>
                        <div class="h-4 w-[1px] bg-slate-200"></div>
                        <Button
                            variant="ghost"
                            size="icon"
                            class="h-8 w-8 rounded-lg"
                            :disabled="!nextId"
                            @click="goToSubmission(nextId as number)"
                        >
                            <ChevronRight class="h-4 w-4" />
                        </Button>
                    </div>

                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-sm font-bold text-white shadow-lg"
                        >
                            {{ submission.student?.first_name?.charAt(0) }}
                        </div>
                        <div>
                            <h1
                                class="mb-1 text-base leading-none font-bold text-slate-900"
                            >
                                {{ submission.student?.first_name }}
                                {{ submission.student?.last_name }}
                                <span class="ml-1 font-bold text-slate-300"
                                    >#{{
                                        submission.student?.admission_number ||
                                        '001'
                                    }}</span
                                >
                            </h1>
                            <p
                                class="flex items-center gap-2 text-xs font-bold tracking-tight text-slate-400 uppercase"
                            >
                                {{ assignment.title }}
                                <ChevronRight class="h-2 w-2" />
                                <span class="text-blue-600"
                                    >Reviewing Studio</span
                                >
                                <template v-if="submission.marked_file_path">
                                    <ChevronRight class="h-2 w-2" />
                                    <span
                                        class="flex items-center gap-1.5 rounded-full border border-emerald-100/50 bg-emerald-50 px-2 py-0.5 text-emerald-600"
                                    >
                                        <CheckCircle2 class="h-2.5 w-2.5" />
                                        Vault Sync Active
                                    </span>
                                </template>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Grading Controls Integrated in Header -->
                <div
                    class="flex items-center gap-4 rounded-2xl border border-slate-100 bg-slate-50 p-2"
                >
                    <div class="flex flex-col px-3">
                        <span
                            class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                            >Score (Max {{ assignment.total_marks }})</span
                        >
                        <input
                            type="number"
                            v-model="gradingForm.marks_obtained"
                            class="w-20 border-0 bg-transparent p-0 text-xl font-bold text-blue-600 outline-none"
                        />
                    </div>
                    <div class="h-10 w-[1px] bg-slate-200"></div>
                    <div class="flex items-center gap-3 pr-2">
                        <div class="group relative">
                            <MessageSquare
                                class="absolute top-1/2 left-3 h-3.5 w-3.5 -translate-y-1/2 text-slate-300 transition-colors group-focus-within:text-blue-500"
                            />
                            <Input
                                v-model="gradingForm.feedback"
                                placeholder="Summary feedback..."
                                class="h-10 w-64 border-0 bg-transparent pl-10 text-xs font-semibold placeholder:text-slate-300 focus-visible:ring-0"
                            />
                        </div>
                        <a
                            :href="
                                route(
                                    'curriculum.assignments.submissions.download-compiled',
                                    submission.id,
                                )
                            "
                            target="_blank"
                        >
                            <Button
                                variant="outline"
                                class="h-10 rounded-xl border-slate-200 bg-white px-4 text-xs font-bold tracking-tight text-slate-600 uppercase shadow-sm transition-all hover:-translate-y-0.5 hover:bg-slate-50 active:translate-y-0"
                            >
                                <Download class="mr-2 h-4 w-4" /> PDF
                            </Button>
                        </a>
                        <Button
                            @click="
                                gradingForm.post(
                                    route(
                                        'curriculum.assignments.submissions.grade',
                                        props.submission.id,
                                    ),
                                    { preserveScroll: true },
                                )
                            "
                            :disabled="gradingForm.processing"
                            class="h-10 rounded-xl bg-blue-600 px-8 text-xs font-bold tracking-tight text-white uppercase shadow-xl shadow-blue-500/20 transition-all hover:-translate-y-0.5 hover:bg-blue-700 active:translate-y-0"
                        >
                            {{
                                gradingForm.processing
                                    ? 'Saving...'
                                    : 'Finalize Profile'
                            }}
                        </Button>
                        <div class="h-8 w-px bg-slate-200"></div>
                        <Button
                            variant="ghost"
                            size="icon"
                            @click="router.reload({ only: ['submission'] })"
                            class="h-10 w-10 rounded-xl border border-slate-200 hover:bg-slate-100"
                        >
                            <RotateCcw class="h-4 w-4 text-slate-400" />
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Main Workspace: Split View -->
            <div class="flex flex-1 overflow-hidden">
                <!-- Left Column: Marking Tool (Primary) -->
                <div class="relative flex-1 bg-slate-900">
                    <MarkingTool
                        v-if="submission.attachments?.length"
                        ref="markingToolRef"
                        :images="submission.attachments"
                        :initial-annotations="gradingForm.annotations"
                        :saving="gradingForm.processing"
                        @save="onAnnotationsSaved"
                    />
                    <div
                        v-else
                        class="flex h-full flex-col items-center justify-center gap-6 text-white"
                    >
                        <div
                            class="flex h-24 w-24 items-center justify-center rounded-full border border-white/10 bg-white/5"
                        >
                            <FileText class="h-10 w-10 opacity-20" />
                        </div>
                        <div class="space-y-2 text-center">
                            <p
                                class="text-sm font-bold tracking-tight text-slate-400 uppercase"
                            >
                                Inventory Empty
                            </p>
                            <p class="text-xs text-slate-500">
                                This learner has not uploaded any photographic
                                exhibits.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar: Submission Metadata (Profile) -->
                <div
                    class="flex w-[380px] flex-col border-l border-slate-100 bg-white shadow-[-4px_0_12px_rgba(0,0,0,0.02)]"
                >
                    <div class="space-y-8 overflow-y-auto p-8">
                        <!-- Submission Metadata -->
                        <div class="space-y-6">
                            <h3
                                class="border-b border-slate-50 pb-4 text-xs font-bold tracking-tight text-slate-900 uppercase"
                            >
                                Diagnostic Context
                            </h3>

                            <div class="space-y-5">
                                <div class="flex items-center justify-between">
                                    <span
                                        class="text-xs font-bold tracking-wider text-slate-400 uppercase"
                                        >Status Index</span
                                    >
                                    <Badge
                                        variant="outline"
                                        :class="[
                                            'rounded-lg border-0 px-3 py-1 text-xs font-medium tracking-tight uppercase',
                                            getStatusBadge(submission.status),
                                        ]"
                                    >
                                        {{ submission.status }}
                                    </Badge>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span
                                        class="text-xs font-bold tracking-wider text-slate-400 uppercase"
                                        >Ingestion Date</span
                                    >
                                    <span
                                        class="text-xs font-bold text-slate-700"
                                        >{{
                                            submission.submitted_at
                                                ? new Date(
                                                      submission.submitted_at,
                                                  ).toLocaleDateString(
                                                      undefined,
                                                      { dateStyle: 'long' },
                                                  )
                                                : 'Pending'
                                        }}</span
                                    >
                                </div>
                                <div class="flex items-center justify-between">
                                    <span
                                        class="text-xs font-bold tracking-wider text-slate-400 uppercase"
                                        >Exhibit Count</span
                                    >
                                    <span
                                        class="text-xs font-bold text-blue-600"
                                        >{{
                                            submission.attachments?.length || 0
                                        }}
                                        Digital Assets</span
                                    >
                                </div>
                                <div
                                    v-if="submission.marked_file_path"
                                    class="pt-2"
                                >
                                    <a
                                        :href="`/storage/${submission.marked_file_path}`"
                                        target="_blank"
                                        class="group flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50 p-3 transition-all hover:border-blue-200"
                                    >
                                        <div class="flex items-center gap-2">
                                            <FileText
                                                class="h-4 w-4 text-emerald-500"
                                            />
                                            <span
                                                class="text-xs font-bold tracking-tight text-slate-700 uppercase"
                                                >Finalized Vault Record</span
                                            >
                                        </div>
                                        <ExternalLink
                                            class="h-3 w-3 text-slate-300 transition-colors group-hover:text-blue-500"
                                        />
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Learner Note -->
                        <div class="space-y-4">
                            <h3
                                class="border-b border-slate-50 pb-4 text-xs font-bold tracking-tight text-slate-900 uppercase"
                            >
                                Learner Commentary
                            </h3>
                            <div
                                class="rounded-2xl border border-blue-100/50 bg-blue-50/50 p-5 text-xs leading-relaxed text-slate-600"
                            >
                                "{{
                                    submission.content ||
                                    'No commentary provided by student.'
                                }}"
                            </div>
                        </div>

                        <!-- Exhibit Previews -->
                        <div class="space-y-4">
                            <h3
                                class="border-b border-slate-50 pb-4 text-xs font-bold tracking-tight text-slate-900 uppercase"
                            >
                                Raw Exhibits ({{
                                    submission.attachments?.length || 0
                                }})
                            </h3>
                            <div class="grid grid-cols-2 gap-3">
                                <div
                                    v-for="attachment in submission.attachments"
                                    :key="attachment.id"
                                    class="group aspect-square cursor-pointer overflow-hidden rounded-2xl border border-slate-100 bg-slate-50 transition-all hover:border-blue-300"
                                >
                                    <img
                                        v-if="
                                            attachment.file_type.includes(
                                                'image',
                                            )
                                        "
                                        :src="`/storage/${attachment.file_path}`"
                                        class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
                                    />
                                    <div
                                        v-else
                                        class="flex h-full w-full items-center justify-center text-xs font-bold text-slate-300 uppercase"
                                    >
                                        {{ attachment.file_type.split('/')[1] }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Failure Placeholder -->
        <div
            v-else
            class="flex h-screen w-full items-center justify-center bg-white"
        >
            <div class="space-y-4 text-center">
                <div
                    class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-50"
                >
                    <Target class="h-8 w-8 animate-pulse text-slate-200" />
                </div>
                <h2
                    class="text-sm font-bold tracking-tight text-slate-900 uppercase"
                >
                    Loading Academic Profile...
                </h2>
                <p class="text-xs text-slate-400">
                    Connecting to the assessment intake service.
                </p>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Ensure the layout takes full height and hides body overflow */
:deep(body) {
    overflow: hidden;
}

input[type='number']::-webkit-inner-spin-button,
input[type='number']::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
</style>
