<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import {
    Folder,
    BookOpen,
    GraduationCap,
    ArrowLeft,
    ChevronRight,
    FileText,
    Search,
    Filter,
    Archive,
    Users,
    Calendar,
    Download,
    Eye,
    CheckCircle2,
    Clock,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { route } from 'ziggy-js';

const props = defineProps<{
    vault: Record<string, Record<string, any[]>>;
}>();

const breadcrumbs = [
    { title: 'Assignments', href: '/curriculum/assignments' },
    { title: 'Academic Vault', href: '#' },
];

const searchQuery = ref('');
const selectedFolder = ref<{ subject: string; className: string } | null>(null);

const subjects = computed(() => Object.keys(props.vault));

const filteredVault = computed(() => {
    if (!searchQuery.value) return props.vault;

    const result: Record<string, any> = {};
    Object.entries(props.vault).forEach(([subject, classes]) => {
        if (subject.toLowerCase().includes(searchQuery.value.toLowerCase())) {
            result[subject] = classes;
            return;
        }

        const filteredClasses: Record<string, any> = {};
        Object.entries(classes).forEach(([className, submissions]) => {
            if (
                className
                    .toLowerCase()
                    .includes(searchQuery.value.toLowerCase())
            ) {
                filteredClasses[className] = submissions;
            }
        });

        if (Object.keys(filteredClasses).length > 0) {
            result[subject] = filteredClasses;
        }
    });
    return result;
});

const openFolder = (subject: string, className: string) => {
    selectedFolder.value = { subject, className };
};

const closeFolder = () => {
    selectedFolder.value = null;
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-KE', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};
</script>

<template>
    <Head title="Academic Vault - Graded Assignments" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full min-h-screen flex-col bg-slate-50">
            <!-- Hero Header -->
            <div
                class="border-b border-slate-200 bg-white px-8 py-10 shadow-sm"
            >
                <div
                    class="mx-auto flex max-w-[1600px] flex-col justify-between gap-6 md:flex-row md:items-center"
                >
                    <div>
                        <div class="mb-2 flex items-center gap-2">
                            <div
                                class="flex h-6 w-6 items-center justify-center rounded-lg bg-blue-50 text-blue-600"
                            >
                                <Archive class="h-3.5 w-3.5" />
                            </div>
                            <span
                                class="text-xs font-medium tracking-tight text-muted-foreground text-slate-400 uppercase"
                                >Digital Archive</span
                            >
                        </div>
                        <h1
                            class="text-3xl font-bold tracking-tight text-slate-900 uppercase"
                        >
                            Academic <span class="text-blue-600">Vault</span>
                        </h1>
                        <p class="mt-1 text-sm font-medium text-slate-500">
                            Institutional repository of all marked and assessed
                            student materials.
                        </p>
                    </div>

                    <div
                        class="flex items-center gap-4 rounded-2xl border border-slate-100 bg-slate-50 p-2"
                    >
                        <div
                            class="border-r border-slate-200 px-6 py-2 text-center"
                        >
                            <p class="text-xl font-bold text-slate-900">
                                {{ subjects.length }}
                            </p>
                            <p
                                class="mt-0.5 text-xs font-bold tracking-tight text-slate-400 uppercase"
                            >
                                Subjects
                            </p>
                        </div>
                        <div class="px-6 py-2 text-center">
                            <p class="text-xl font-bold text-blue-600">
                                {{
                                    Object.values(vault).reduce(
                                        (acc, cls) =>
                                            acc + Object.keys(cls).length,
                                        0,
                                    )
                                }}
                            </p>
                            <p
                                class="mt-0.5 text-xs font-bold tracking-tight text-slate-400 uppercase"
                            >
                                Classes
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mx-auto w-full max-w-[1600px] flex-1 p-8">
                <!-- Drill-down View -->
                <div
                    v-if="selectedFolder"
                    class="animate-in space-y-6 duration-500 slide-in-from-right-4"
                >
                    <div
                        class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                    >
                        <div class="flex items-center gap-4">
                            <Button
                                @click="closeFolder"
                                variant="ghost"
                                size="icon"
                                class="h-10 w-10 rounded-xl hover:bg-slate-100"
                            >
                                <ArrowLeft class="h-5 w-5" />
                            </Button>
                            <div>
                                <h2
                                    class="text-lg font-bold tracking-tight text-slate-900 uppercase"
                                >
                                    {{ selectedFolder.className }}
                                </h2>
                                <p
                                    class="text-xs font-medium tracking-tight text-slate-500 uppercase"
                                >
                                    {{ selectedFolder.subject }} Portfolio
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <Badge
                                class="rounded-lg bg-blue-50 px-3 py-1 text-xs font-bold text-blue-600 uppercase"
                            >
                                {{
                                    vault[selectedFolder.subject][
                                        selectedFolder.className
                                    ].length
                                }}
                                Submissions
                            </Badge>
                        </div>
                    </div>

                    <div
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                    >
                        <table class="w-full border-collapse text-left">
                            <thead>
                                <tr
                                    class="border-b border-slate-200 bg-slate-50/50"
                                >
                                    <th
                                        class="py-4 pl-8 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                    >
                                        Student Name
                                    </th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                    >
                                        Submission Details
                                    </th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                    >
                                        Marked Date
                                    </th>
                                    <th
                                        class="px-6 py-4 text-right text-xs font-bold tracking-tight text-slate-400 uppercase"
                                    >
                                        Repository Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr
                                    v-for="submission in vault[
                                        selectedFolder.subject
                                    ][selectedFolder.className]"
                                    :key="submission.id"
                                    class="group transition-colors hover:bg-slate-50/50"
                                >
                                    <td class="py-5 pl-8">
                                        <div class="flex items-center gap-4">
                                            <div
                                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-xs font-bold text-blue-600 uppercase shadow-sm"
                                            >
                                                {{
                                                    submission.student?.first_name?.charAt(
                                                        0,
                                                    )
                                                }}{{
                                                    submission.student?.last_name?.charAt(
                                                        0,
                                                    )
                                                }}
                                            </div>
                                            <div>
                                                <p
                                                    class="line-clamp-1 text-sm font-bold text-slate-900"
                                                >
                                                    {{
                                                        submission.student
                                                            ?.first_name
                                                    }}
                                                    {{
                                                        submission.student
                                                            ?.last_name
                                                    }}
                                                </p>
                                                <p
                                                    class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                                                >
                                                    {{
                                                        submission.assignment
                                                            ?.title
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="flex h-6 w-6 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"
                                            >
                                                <CheckCircle2 class="h-3 w-3" />
                                            </div>
                                            <span
                                                class="text-xs font-bold text-slate-600"
                                                >Assessed & Graded</span
                                            >
                                        </div>
                                    </td>
                                    <td
                                        class="px-6 py-5 text-xs font-semibold text-slate-500"
                                    >
                                        {{ formatDate(submission.graded_at) }}
                                    </td>
                                    <td class="px-8 py-5 text-right">
                                        <div
                                            class="flex items-center justify-end gap-3 opacity-0 transition-opacity group-hover:opacity-100"
                                        >
                                            <Link
                                                :href="
                                                    route(
                                                        'curriculum.assignments.submissions.download-compiled',
                                                        {
                                                            submission: String(
                                                                submission.id,
                                                            ),
                                                        },
                                                    )
                                                "
                                                class="flex h-9 items-center gap-2 rounded-xl bg-blue-600 px-4 text-xs font-bold tracking-tight text-white uppercase shadow-lg shadow-blue-600/20 transition-all hover:bg-blue-700"
                                            >
                                                <Download class="h-3 w-3" />
                                                Download Marked Copy
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Initial Grid View -->
                <div v-else class="animate-in space-y-12 duration-700 fade-in">
                    <!-- Browser Controls -->
                    <div
                        class="grid grid-cols-1 items-center gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-4"
                    >
                        <div class="group relative md:col-span-3">
                            <Search
                                class="absolute top-1/2 left-6 h-4 w-4 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-blue-600"
                            />
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search folders..."
                                class="w-full rounded-xl border-none bg-slate-100 py-3 pr-6 pl-14 text-sm font-medium transition-all outline-none focus:ring-2 focus:ring-blue-100"
                            />
                        </div>
                        <Button
                            variant="outline"
                            class="h-full gap-3 rounded-xl border-2 text-xs font-medium tracking-tight uppercase"
                        >
                            <Filter class="h-4 w-4" /> Filter Options
                        </Button>
                    </div>

                    <div
                        v-if="Object.keys(filteredVault).length > 0"
                        class="space-y-12"
                    >
                        <div
                            v-for="(classes, subject) in filteredVault"
                            :key="subject"
                            class="space-y-6"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-white shadow-lg"
                                >
                                    <BookOpen class="h-5 w-5" />
                                </div>
                                <h2
                                    class="text-xl font-bold tracking-tight text-slate-900 uppercase"
                                >
                                    {{ subject }}
                                </h2>
                                <div
                                    class="ml-4 h-px flex-1 bg-slate-200"
                                ></div>
                            </div>

                            <div
                                class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                            >
                                <div
                                    v-for="(submissions, className) in classes"
                                    :key="className"
                                    @click="openFolder(subject, className)"
                                    class="group relative cursor-pointer rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-all hover:border-blue-200 hover:shadow-xl"
                                >
                                    <div class="flex h-full flex-col">
                                        <div class="mb-4">
                                            <div
                                                class="mb-5 flex h-14 w-14 items-center justify-center rounded-xl bg-blue-50 text-blue-600 transition-transform duration-500 group-hover:scale-110"
                                            >
                                                <Folder class="h-7 w-7" />
                                            </div>
                                            <h3
                                                class="mb-0.5 line-clamp-1 text-lg font-bold text-slate-900"
                                            >
                                                {{ className }}
                                            </h3>
                                            <p
                                                class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                                            >
                                                {{ submissions.length }} Graded
                                                Files
                                            </p>
                                        </div>

                                        <div
                                            class="mt-auto flex items-center justify-between border-t border-slate-50 pt-5"
                                        >
                                            <span
                                                class="text-xs font-bold tracking-tight text-blue-600 uppercase"
                                                >Open Portfolio</span
                                            >
                                            <ChevronRight
                                                class="h-4 w-4 text-slate-300 group-hover:text-blue-600"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div
                        v-else
                        class="flex flex-col items-center justify-center rounded-3xl border-2 border-dashed border-slate-200 bg-white py-24 text-center"
                    >
                        <div
                            class="mb-6 flex h-20 w-20 items-center justify-center rounded-2xl bg-slate-100 text-slate-300"
                        >
                            <Archive class="h-10 w-10" />
                        </div>
                        <h3
                            class="mb-2 text-xl font-bold tracking-tight text-slate-900 uppercase"
                        >
                            Archive Empty
                        </h3>
                        <p
                            class="mx-auto max-w-sm text-sm font-medium text-slate-400"
                        >
                            No assignments have been marked for archiving yet.
                            The vault auto-populates upon finalized assessment.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
