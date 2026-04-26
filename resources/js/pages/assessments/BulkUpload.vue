<script setup lang="ts">
/* global route */
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    Upload,
    FileText,
    CheckCircle2,
    AlertCircle,
    Download,
    ArrowRight,
    Info,
    ShieldCheck,
    Users,
    BookOpen,
    GraduationCap,
    ClipboardList,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { ref } from 'vue';

const props = defineProps<{
    assessments: any[];
}>();

const form = useForm({
    assessment_id: '',
    file: null as File | null,
});

const isDragging = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);

const handleFileDrop = (e: DragEvent) => {
    isDragging.value = false;
    if (e.dataTransfer?.files.length) {
        form.file = e.dataTransfer.files[0];
    }
};

const handleFileSelect = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files?.length) {
        form.file = target.files[0];
    }
};

const submit = () => {
    if (!form.assessment_id || !form.file) return;
    form.post(route('assessments.bulk-upload.store'), {
        forceFormData: true,
        onSuccess: () => form.reset(),
    });
};

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Assessments', href: '/assessments' },
    { title: 'Bulk Upload', href: '/assessments/bulk-upload' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Bulk Upload Marks" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Pulsar Header -->
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-500/10"
                    >
                        <ClipboardList class="h-6 w-6 text-violet-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight uppercase">
                            Bulk Marks Entry
                        </h1>
                        <p class="text-muted-foreground">
                            Upload assessment results using a structured CSV
                            file.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" as-child>
                        <a href="/templates/marks_template.csv" download>
                            <Download class="mr-2 h-4 w-4" />
                            Get Template
                        </a>
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                <!-- Instructions & Tips (Pulsar Style) -->
                <div class="space-y-6">
                    <div
                        class="space-y-6 rounded-xl border bg-card p-6 shadow-sm"
                    >
                        <h3
                            class="flex items-center gap-2 text-xs font-bold tracking-tight text-gray-400 uppercase"
                        >
                            <Info class="h-4 w-4 text-violet-600" /> IMPORT
                            PROTOCOL
                        </h3>
                        <ul class="space-y-6">
                            <li class="flex gap-4">
                                <span
                                    class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg border border-violet-100 bg-violet-50 text-xs font-bold text-violet-600"
                                    >01</span
                                >
                                <p
                                    class="text-xs leading-relaxed font-bold tracking-tight text-gray-500 uppercase"
                                >
                                    Prepare your data using the
                                    <span class="text-violet-700"
                                        >official CSV template</span
                                    >.
                                </p>
                            </li>
                            <li class="flex gap-4">
                                <span
                                    class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg border border-violet-100 bg-violet-50 text-xs font-bold text-violet-600"
                                    >02</span
                                >
                                <p
                                    class="text-xs leading-relaxed font-bold tracking-tight text-gray-500 uppercase"
                                >
                                    Ensure
                                    <span class="text-gray-900"
                                        >Admission Numbers</span
                                    >
                                    match student records.
                                </p>
                            </li>
                            <li class="flex gap-4">
                                <span
                                    class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg border border-violet-100 bg-violet-50 text-xs font-bold text-violet-600"
                                    >03</span
                                >
                                <p
                                    class="text-xs leading-relaxed font-bold tracking-tight text-gray-500 uppercase"
                                >
                                    Enter scores without
                                    <span class="text-gray-900"
                                        >exceeding Max Marks</span
                                    >.
                                </p>
                            </li>
                        </ul>
                    </div>

                    <div
                        class="rounded-xl border border-amber-100 bg-amber-50 p-6"
                    >
                        <div
                            class="mb-2 flex items-center gap-3 text-amber-700"
                        >
                            <AlertCircle class="h-4 w-4" />
                            <h4
                                class="text-xs font-bold tracking-tight uppercase"
                            >
                                Validation Warning
                            </h4>
                        </div>
                        <p
                            class="text-xs leading-relaxed font-bold tracking-tight text-amber-600 text-muted-foreground uppercase"
                        >
                            The system will automatically validate each row.
                            Invalid admission numbers or out-of-range scores
                            will be rejected.
                        </p>
                    </div>
                </div>

                <!-- Upload Form (Pulsar Style) -->
                <div class="space-y-6 lg:col-span-2">
                    <div
                        class="space-y-8 rounded-xl border bg-card p-8 shadow-sm"
                    >
                        <!-- Assessment Selection -->
                        <div class="space-y-3">
                            <label
                                class="text-xs font-bold tracking-tight text-gray-400 uppercase"
                                >Target Assessment</label
                            >
                            <Select v-model="form.assessment_id">
                                <SelectTrigger
                                    class="h-12 rounded-lg border-gray-100 bg-gray-50/50 text-sm font-bold text-gray-900"
                                >
                                    <SelectValue
                                        placeholder="Select an assessment..."
                                    />
                                </SelectTrigger>
                                <SelectContent
                                    class="rounded-xl border-gray-100 shadow-lg"
                                >
                                    <SelectItem
                                        v-for="asmt in assessments"
                                        :key="asmt.id"
                                        :value="asmt.id.toString()"
                                    >
                                        <div class="flex flex-col py-1">
                                            <span
                                                class="text-xs font-bold text-gray-900"
                                                >{{ asmt.title }}</span
                                            >
                                            <span
                                                class="mt-0.5 text-xs font-bold tracking-tight text-gray-400 uppercase"
                                                >{{ asmt.subject?.name }} ·
                                                {{ asmt.class?.name }}</span
                                            >
                                        </div>
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Dropzone (Pulsar Style) -->
                        <div class="space-y-3">
                            <label
                                class="text-xs font-bold tracking-tight text-gray-400 uppercase"
                                >Source Document</label
                            >
                            <div
                                @dragover.prevent="isDragging = true"
                                @dragleave.prevent="isDragging = false"
                                @drop.prevent="handleFileDrop"
                                @click="fileInput?.click()"
                                :class="[
                                    'group relative flex h-56 cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed transition-all duration-300',
                                    isDragging
                                        ? 'border-solid border-violet-400 bg-violet-50/50'
                                        : 'border-gray-100 bg-gray-50/30 hover:border-violet-200 hover:bg-white',
                                ]"
                            >
                                <input
                                    ref="fileInput"
                                    type="file"
                                    class="hidden"
                                    accept=".csv"
                                    @change="handleFileSelect"
                                />

                                <div
                                    v-if="!form.file"
                                    class="space-y-4 text-center"
                                >
                                    <div
                                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-xl border border-gray-100 bg-white shadow-sm transition-all duration-300 group-hover:bg-violet-600"
                                    >
                                        <Upload
                                            class="h-6 w-6 text-gray-300 group-hover:text-white"
                                        />
                                    </div>
                                    <div>
                                        <p
                                            class="text-sm font-bold text-gray-900 uppercase"
                                        >
                                            Drop CSV File
                                        </p>
                                        <p
                                            class="mt-1 text-xs font-bold tracking-tight text-gray-400 uppercase"
                                        >
                                            or click to browse database
                                        </p>
                                    </div>
                                </div>
                                <div
                                    v-else
                                    class="animate-in space-y-4 text-center duration-300 zoom-in-95"
                                >
                                    <div
                                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-xl border border-emerald-100 bg-emerald-50 shadow-sm"
                                    >
                                        <FileText
                                            class="h-6 w-6 text-emerald-600"
                                        />
                                    </div>
                                    <div class="px-6">
                                        <p
                                            class="max-w-[200px] truncate text-sm font-bold text-gray-900"
                                        >
                                            {{ form.file.name }}
                                        </p>
                                        <p
                                            class="mt-1 text-xs font-bold tracking-tight text-emerald-600 uppercase"
                                        >
                                            {{
                                                (form.file.size / 1024).toFixed(
                                                    1,
                                                )
                                            }}
                                            KB · Ready
                                        </p>
                                    </div>
                                    <button
                                        @click.stop="form.file = null"
                                        class="text-xs font-bold tracking-tight text-rose-600 uppercase hover:underline"
                                    >
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Action -->
                        <div
                            class="flex items-center justify-between border-t border-gray-50 pt-6"
                        >
                            <p
                                class="flex items-center gap-2 text-xs font-bold text-gray-400"
                            >
                                <ShieldCheck
                                    class="h-3.5 w-3.5 text-emerald-600"
                                />
                                Secure Node Processing
                            </p>
                            <Button
                                @click="submit"
                                :disabled="
                                    form.processing ||
                                    !form.assessment_id ||
                                    !form.file
                                "
                                class="h-12 rounded-lg bg-violet-600 px-8 text-xs font-bold tracking-tight text-white uppercase shadow-lg shadow-violet-100 transition-all duration-300 hover:bg-violet-700 active:scale-95"
                            >
                                <ArrowRight class="mr-2 h-4 w-4" /> Finalize
                                Import
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
