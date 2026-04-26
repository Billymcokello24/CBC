<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import {
    Upload,
    Download,
    X,
    FileText,
    CheckCircle2,
    AlertCircle,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

const props = defineProps<{
    open: boolean;
}>();

const emit = defineEmits(['update:open']);

const form = useForm({
    file: null as File | null,
});

const fileInput = ref<HTMLInputElement | null>(null);
const dragOver = ref(false);
const page = usePage();
const flashError = computed(
    () => (page.props as any).flash?.error as string | null,
);
const showErrorModal = ref(false);

watch(flashError, (newError) => {
    if (newError) {
        showErrorModal.value = true;
    }
});

const handleFileUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        form.file = target.files[0];
    }
};

const handleDrop = (event: DragEvent) => {
    dragOver.value = false;
    if (event.dataTransfer?.files && event.dataTransfer.files.length > 0) {
        form.file = event.dataTransfer.files[0];
    }
};

const removeFile = () => {
    form.file = null;
    if (fileInput.value) fileInput.value.value = '';
};

const submit = () => {
    if (!form.file) return;

    form.post('/staffs/bulk-upload', {
        onSuccess: () => {
            if (!flashError.value) {
                closeModal();
                form.reset();
            }
        },
    });
};

const closeModal = () => {
    emit('update:open', false);
    form.reset();
};
</script>

<template>
    <Dialog :open="open" @update:open="closeModal">
        <DialogContent
            class="overflow-hidden rounded-2xl border-0 p-0 shadow-lg sm:max-w-[550px]"
        >
            <div class="relative p-8 pt-10">
                <div
                    class="absolute top-0 left-0 h-1 w-full bg-gradient-to-r from-purple-500 via-indigo-500 to-purple-500"
                ></div>
                <DialogHeader>
                    <div class="mb-2 flex items-center gap-4">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-purple-600 text-white shadow-lg shadow-purple-100/50"
                        >
                            <Upload class="h-6 w-6" />
                        </div>
                        <div>
                            <DialogTitle
                                class="text-2xl font-bold text-foreground"
                                >Bulk Registration</DialogTitle
                            >
                            <DialogDescription
                                class="mt-0.5 text-sm font-medium text-muted-foreground"
                            >
                                Onboard multiple educators using CSV data.
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>

                <div class="mt-8 space-y-8">
                    <!-- Template Card -->
                    <div
                        class="group relative overflow-hidden rounded-2xl border border-border bg-muted/30 p-5 transition-all hover:bg-muted/50"
                    >
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div
                                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-border bg-background shadow-sm transition-transform group-hover:scale-110"
                                >
                                    <Download class="h-6 w-6 text-purple-600" />
                                </div>
                                <div>
                                    <p
                                        class="text-sm font-bold text-foreground"
                                    >
                                        Standardized Template
                                    </p>
                                    <p
                                        class="mt-0.5 text-xs leading-relaxed text-muted-foreground"
                                    >
                                        Required CSV format for seamless data
                                        ingestion.
                                    </p>
                                </div>
                            </div>
                            <Button
                                variant="outline"
                                size="sm"
                                as-child
                                class="h-10 rounded-xl border-border bg-background/50 px-4 text-xs font-bold tracking-wider uppercase shadow-sm transition-all hover:bg-background"
                            >
                                <a href="/staffs/template/download" download>
                                    Get Template
                                </a>
                            </Button>
                        </div>
                    </div>

                    <!-- Upload Zone -->
                    <div
                        class="group/drop relative flex flex-col items-center justify-center rounded-2xl border-2 border-dashed p-12 transition-all duration-300"
                        :class="[
                            dragOver
                                ? 'scale-[0.99] border-purple-500 bg-purple-50/50'
                                : 'border-border bg-muted/20 hover:bg-muted/30',
                            form.file
                                ? 'border-purple-200 bg-purple-50/30'
                                : '',
                        ]"
                        @dragover.prevent="dragOver = true"
                        @dragleave.prevent="dragOver = false"
                        @drop.prevent="handleDrop"
                    >
                        <input
                            ref="fileInput"
                            type="file"
                            accept=".csv"
                            class="absolute inset-0 z-10 cursor-pointer opacity-0"
                            @change="handleFileUpload"
                        />

                        <template v-if="!form.file">
                            <div
                                class="flex h-16 w-16 items-center justify-center rounded-2xl border border-border bg-background shadow-sm transition-transform duration-500 group-hover/drop:scale-110"
                            >
                                <Upload class="h-8 w-8 text-purple-600/50" />
                            </div>
                            <div class="mt-6 text-center">
                                <p class="text-sm font-bold text-foreground">
                                    Drop staff CSV here
                                </p>
                                <p
                                    class="mt-1 px-4 text-xs text-muted-foreground"
                                >
                                    Maximum file size: 5MB • Standard UTF-8
                                    encoding
                                </p>
                            </div>
                        </template>

                        <template v-else>
                            <div
                                class="flex w-full animate-in flex-col items-center gap-4 duration-300 zoom-in"
                            >
                                <div
                                    class="flex h-20 w-20 items-center justify-center rounded-2xl border border-purple-100 bg-white shadow-xl shadow-purple-100/20"
                                >
                                    <FileText
                                        class="h-10 w-10 text-purple-600"
                                    />
                                </div>
                                <div
                                    class="max-w-full min-w-0 px-4 text-center"
                                >
                                    <p
                                        class="truncate text-sm leading-tight font-bold break-all text-foreground"
                                        :title="form.file.name"
                                    >
                                        {{ form.file.name }}
                                    </p>
                                    <div
                                        class="mt-2 flex items-center justify-center gap-2"
                                    >
                                        <Badge
                                            variant="outline"
                                            class="border-purple-100 bg-purple-50 text-xs font-bold text-purple-600"
                                            >READY TO SYNC</Badge
                                        >
                                        <span
                                            class="text-xs font-bold text-muted-foreground"
                                            >{{
                                                (form.file.size / 1024).toFixed(
                                                    2,
                                                )
                                            }}
                                            KB</span
                                        >
                                    </div>
                                </div>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    class="z-20 mt-2 h-9 rounded-lg text-xs font-bold tracking-wider text-muted-foreground uppercase hover:bg-destructive/5 hover:text-destructive"
                                    @click.stop="removeFile"
                                >
                                    <X class="mr-2 h-3 w-3" />
                                    Remove File
                                </Button>
                            </div>
                        </template>
                    </div>

                    <div
                        v-if="form.errors.file"
                        class="flex animate-in items-start gap-3 rounded-xl border border-destructive/10 bg-destructive/5 p-4 duration-300 slide-in-from-top-2"
                    >
                        <AlertCircle
                            class="mt-0.5 h-5 w-5 shrink-0 text-destructive"
                        />
                        <span
                            class="text-xs leading-relaxed font-medium text-destructive"
                            >{{ form.errors.file }}</span
                        >
                    </div>

                    <div
                        v-if="flashError"
                        class="flex animate-in flex-col gap-3 rounded-xl border border-destructive/20 bg-destructive/5 p-5 duration-300 slide-in-from-top-2"
                    >
                        <div
                            class="flex items-center gap-2 font-bold text-destructive"
                        >
                            <AlertCircle class="h-4 w-4 shrink-0" />
                            <span class="text-xs tracking-wider uppercase"
                                >Ingestion Error</span
                            >
                        </div>
                        <p
                            class="text-sm leading-relaxed font-medium break-words text-destructive/80"
                        >
                            {{ flashError }}
                        </p>
                        <p
                            class="mt-1 text-xs font-bold tracking-tighter text-destructive/60 uppercase"
                        >
                            Please recalibrate file and try again
                        </p>
                    </div>

                    <div
                        v-if="form.wasSuccessful && !flashError"
                        class="flex animate-in items-center gap-3 rounded-xl border border-emerald-100 bg-emerald-50 p-4 text-emerald-700 duration-300 slide-in-from-top-2"
                    >
                        <CheckCircle2 class="h-5 w-5" />
                        <span class="text-xs leading-none font-bold"
                            >Data synchronized successfully!</span
                        >
                    </div>
                </div>
            </div>

            <DialogFooter
                class="flex flex-col gap-2 border-t bg-muted/30 p-6 sm:flex-row sm:items-center sm:justify-end"
            >
                <Button
                    variant="ghost"
                    @click="closeModal"
                    :disabled="form.processing"
                    class="h-11 rounded-xl px-6 font-bold text-muted-foreground hover:text-foreground"
                >
                    Cancel
                </Button>
                <Button
                    @click="submit"
                    :disabled="!form.file || form.processing"
                    class="h-11 rounded-xl border-0 bg-purple-600 px-10 font-bold text-white shadow-lg shadow-purple-100 transition-all hover:bg-purple-700"
                >
                    <Loader2
                        v-if="form.processing"
                        class="mr-2 h-4 w-4 animate-spin"
                    />
                    <Upload v-else class="mr-2 h-4 w-4" />
                    <template v-if="form.processing">Synchronizing...</template>
                    <template v-else>Register Staff</template>
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <!-- Detailed Error Modal -->
    <Dialog :open="showErrorModal" @update:open="showErrorModal = $event">
        <DialogContent
            class="overflow-hidden rounded-2xl border-0 p-0 shadow-lg sm:max-w-[550px]"
        >
            <div class="relative p-8 pt-10 text-center">
                <div
                    class="absolute top-0 left-0 h-1 w-full bg-destructive"
                ></div>
                <div
                    class="mx-auto mb-6 flex h-20 w-20 animate-in items-center justify-center rounded-full border-4 border-white bg-destructive/10 shadow-sm ring-1 ring-destructive/10 duration-300 zoom-in"
                >
                    <AlertCircle class="h-10 w-10 text-destructive" />
                </div>
                <DialogHeader>
                    <DialogTitle class="text-2xl font-bold text-foreground"
                        >Data Format Conflict</DialogTitle
                    >
                    <DialogDescription
                        class="mt-2 px-6 text-sm leading-relaxed text-muted-foreground"
                    >
                        We encountered structural issues while parsing your CSV
                        file. Please review the specific error details below.
                    </DialogDescription>
                </DialogHeader>

                <div
                    class="custom-scrollbar mt-8 max-h-[300px] overflow-y-auto rounded-2xl border border-border bg-muted/50 p-6 text-left"
                >
                    <p
                        class="mb-3 flex items-center gap-2 text-xs font-bold tracking-tight text-foreground uppercase"
                    >
                        <span
                            class="h-1.5 w-1.5 animate-pulse rounded-full bg-destructive"
                        ></span>
                        Error Trace
                    </p>
                    <p
                        class="text-sm leading-relaxed font-semibold break-words whitespace-pre-line text-destructive"
                    >
                        {{ flashError }}
                    </p>
                </div>

                <div class="mt-8 flex flex-col gap-3">
                    <div
                        class="flex items-center gap-4 rounded-2xl border border-purple-100 bg-purple-50/50 p-4 text-left transition-all hover:bg-purple-50"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-purple-100 bg-white shadow-sm transition-transform group-hover:scale-110"
                        >
                            <FileText class="h-5 w-5 text-purple-600" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold text-purple-900">
                                Optimization Tip
                            </p>
                            <p
                                class="mt-0.5 text-xs leading-relaxed font-medium text-purple-700/70"
                            >
                                Download the latest template to ensure all
                                column headers are perfectly calibrated.
                            </p>
                        </div>
                        <Button
                            variant="outline"
                            size="sm"
                            as-child
                            class="h-9 shrink-0 rounded-lg border-purple-200 bg-white px-4 text-xs font-bold tracking-wider text-purple-700 uppercase shadow-sm transition-all hover:bg-purple-50"
                        >
                            <a href="/staffs/template/download" download>
                                Get Template
                            </a>
                        </Button>
                    </div>
                </div>
            </div>

            <DialogFooter
                class="mt-4 flex items-center justify-center border-t bg-muted/30 p-6"
            >
                <Button
                    @click="showErrorModal = false"
                    class="h-11 w-full rounded-xl border-0 bg-foreground font-bold text-background shadow-lg shadow-foreground/10 transition-all"
                >
                    I'll Calibrate and Retry
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
