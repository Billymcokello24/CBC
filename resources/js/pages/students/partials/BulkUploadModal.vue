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

    form.post('/students/bulk-upload', {
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
            class="animate-in overflow-hidden rounded-xl border-slate-100 p-0 shadow-lg duration-300 zoom-in-95 sm:max-w-[540px]"
        >
            <div class="border-b border-slate-50 bg-slate-50/50 px-8 py-6">
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-slate-900 text-white shadow-lg shadow-slate-900/10"
                    >
                        <Upload class="h-6 w-6" />
                    </div>
                    <div>
                        <DialogTitle
                            class="text-xl font-bold tracking-tight text-slate-900"
                            >Mass Node Ingest</DialogTitle
                        >
                        <p
                            class="mt-1 text-xs font-bold tracking-tight text-muted-foreground text-slate-400 uppercase"
                        >
                            Bulk Registry Propagation Protocol
                        </p>
                    </div>
                </div>
            </div>

            <div class="space-y-8 p-8">
                <div
                    class="group flex items-center justify-between rounded-xl border border-blue-100 bg-blue-50/20 p-5 transition-all hover:bg-blue-50/40"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-600 text-white shadow-lg shadow-blue-600/10"
                        >
                            <FileText class="h-5 w-5" />
                        </div>
                        <div>
                            <p
                                class="text-xs font-bold tracking-tight text-slate-900 uppercase"
                            >
                                Core Template
                            </p>
                            <p
                                class="mt-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                            >
                                Structural Manifest Required
                            </p>
                        </div>
                    </div>
                    <Button
                        variant="outline"
                        size="sm"
                        as-child
                        class="h-10 rounded-xl border-blue-200 bg-white px-5 text-xs font-bold tracking-tight uppercase shadow-sm transition-all hover:border-blue-600 hover:bg-blue-600 hover:text-white"
                    >
                        <a
                            href="/students/template/download"
                            download
                            class="flex items-center"
                        >
                            <Download class="mr-2 h-3.5 w-3.5" />
                            GET_SOURCE
                        </a>
                    </Button>
                </div>

                <div
                    class="group relative flex flex-col items-center justify-center rounded-xl border-2 border-dashed p-12 transition-all duration-300"
                    :class="[
                        dragOver
                            ? 'scale-[0.99] border-blue-600 bg-blue-50/50'
                            : 'border-slate-200 bg-slate-50/30',
                        form.file
                            ? 'border-emerald-500 bg-emerald-50/20'
                            : 'hover:border-slate-300 hover:bg-slate-50/50',
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
                            class="flex h-16 w-16 items-center justify-center rounded-[1.5rem] bg-white shadow-xl shadow-slate-200/50 transition-transform duration-500 group-hover:scale-110"
                        >
                            <Upload
                                class="h-8 w-8 text-slate-400 transition-colors group-hover:text-blue-600"
                            />
                        </div>
                        <p
                            class="mt-6 text-xs font-bold tracking-tight text-slate-900 uppercase"
                        >
                            Drop CSV Payload
                        </p>
                        <p
                            class="mt-2 text-xs font-bold tracking-tight text-slate-400 uppercase opacity-60"
                        >
                            MAX_DENSITY: 5.0 MB
                        </p>
                    </template>

                    <template v-else>
                        <div
                            class="flex w-full min-w-0 animate-in items-center gap-5 duration-300 zoom-in-95"
                        >
                            <div
                                class="flex h-16 w-16 shrink-0 items-center justify-center rounded-[1.5rem] bg-emerald-600 text-white shadow-xl shadow-emerald-500/20"
                            >
                                <FileText class="h-8 w-8" />
                            </div>
                            <div class="min-w-0 flex-1 overflow-hidden">
                                <p
                                    class="truncate text-sm leading-none font-bold tracking-tight text-slate-900 uppercase"
                                    :title="form.file.name"
                                >
                                    {{ form.file.name }}
                                </p>
                                <p
                                    class="mt-2 text-xs font-bold tracking-tight text-emerald-600 text-muted-foreground uppercase"
                                >
                                    {{ (form.file.size / 1024).toFixed(2) }} KB
                                    | READY_FOR_SYNC
                                </p>
                            </div>
                            <Button
                                variant="ghost"
                                size="icon"
                                class="z-20 h-10 w-10 shrink-0 rounded-xl text-slate-400 transition-all hover:bg-red-50 hover:text-red-500"
                                @click.stop="removeFile"
                            >
                                <X class="h-5 w-5" />
                            </Button>
                        </div>
                    </template>
                </div>

                <div
                    v-if="form.errors.file"
                    class="flex animate-in items-center gap-3 rounded-2xl border border-red-100 bg-red-50 p-4 duration-300 slide-in-from-top-2"
                >
                    <AlertCircle class="h-4 w-4 shrink-0 text-red-500" />
                    <span
                        class="text-xs font-bold tracking-tight text-red-600 uppercase"
                        >{{ form.errors.file }}</span
                    >
                </div>

                <div
                    v-if="flashError && !showErrorModal"
                    class="flex max-h-[160px] animate-in flex-col gap-3 overflow-y-auto rounded-2xl border border-red-100 bg-red-50 p-5 text-red-600 duration-300 fade-in"
                >
                    <div
                        class="flex items-center gap-3 text-xs font-bold tracking-tight uppercase"
                    >
                        <AlertCircle class="h-4 w-4 shrink-0" />
                        <span>Payload Exception Detected</span>
                    </div>
                    <p
                        class="text-xs leading-relaxed font-medium tracking-tight uppercase opacity-80"
                    >
                        {{ flashError }}
                    </p>
                </div>

                <div
                    v-if="form.wasSuccessful && !flashError"
                    class="flex animate-in items-center gap-3 rounded-2xl border border-emerald-100 bg-emerald-50 p-4 text-emerald-600 duration-300 slide-in-from-top-2"
                >
                    <CheckCircle2 class="h-5 w-5 shrink-0" />
                    <span class="text-xs font-medium tracking-tight uppercase"
                        >PAYLOAD_INGESTED_SUCCESSFULLY</span
                    >
                </div>
            </div>

            <div
                class="flex items-center justify-end gap-4 border-t border-slate-100 bg-slate-50/50 p-8"
            >
                <Button
                    variant="ghost"
                    @click="closeModal"
                    :disabled="form.processing"
                    class="h-12 rounded-2xl px-8 text-xs font-bold tracking-tight text-slate-400 uppercase transition-all hover:text-slate-900"
                    >ABORT</Button
                >
                <Button
                    @click="submit"
                    :disabled="!form.file || form.processing"
                    class="h-12 rounded-2xl border-0 bg-slate-900 px-10 text-xs font-bold tracking-tight text-white uppercase shadow-xl shadow-slate-900/10 transition-all hover:bg-slate-800"
                >
                    <Loader2
                        v-if="form.processing"
                        class="mr-2 h-4 w-4 animate-spin"
                    />
                    <Upload v-else class="mr-2 h-4 w-4" />
                    INITIATE_INGEST
                </Button>
            </div>
        </DialogContent>
    </Dialog>

    <!-- Detailed Error Modal -->
    <Dialog :open="showErrorModal" @update:open="showErrorModal = $event">
        <DialogContent
            class="animate-in overflow-hidden rounded-2xl border-red-100 p-0 shadow-lg duration-300 zoom-in-95 sm:max-w-[580px]"
        >
            <div class="bg-red-600 px-10 py-8 text-white">
                <div class="flex items-center gap-5">
                    <div
                        class="flex h-16 w-16 items-center justify-center rounded-3xl bg-white/20 shadow-xl backdrop-blur-md"
                    >
                        <AlertCircle class="h-10 w-10 text-white" />
                    </div>
                    <div>
                        <DialogTitle class="text-2xl font-bold tracking-tight"
                            >Logical Fault Detected</DialogTitle
                        >
                        <p
                            class="mt-1 text-xs font-medium tracking-tight text-muted-foreground uppercase"
                        >
                            Payload Validation Breach
                        </p>
                    </div>
                </div>
            </div>

            <div class="space-y-8 p-10">
                <p
                    class="px-2 text-sm leading-relaxed font-bold tracking-tight text-muted-foreground text-slate-500 uppercase"
                >
                    THE SUBMITTED MANIFEST CONTAINS STRUCTURAL INCONSISTENCIES
                    THAT COMPROMISE REGISTRY INTEGRITY. REVIEW THE EXCEPTIONS
                    BELOW:
                </p>

                <div
                    class="max-h-[300px] overflow-y-auto rounded-xl border border-slate-100 bg-slate-50 p-8 shadow-inner"
                >
                    <p
                        class="text-xs leading-loose font-bold tracking-tight whitespace-pre-wrap text-red-600 uppercase"
                    >
                        {{ flashError }}
                    </p>
                </div>

                <div
                    class="group flex items-center gap-5 rounded-xl border border-blue-100 bg-blue-50/30 p-6 transition-all hover:bg-blue-50/50"
                >
                    <div
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-500/20"
                    >
                        <FileText class="h-6 w-6" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p
                            class="text-xs font-bold tracking-tight text-slate-900 uppercase"
                        >
                            Corrective Action
                        </p>
                        <p
                            class="mt-1 text-xs leading-relaxed font-bold tracking-tight text-slate-400 uppercase"
                        >
                            RE-ALIGNE PAYLOAD AGAINST THE OFFICIAL MANIFEST
                            HANDLE FOR SEAMLESS INTEGRATION.
                        </p>
                    </div>
                    <Button
                        variant="outline"
                        size="sm"
                        as-child
                        class="h-11 rounded-xl border-blue-200 bg-white px-5 text-xs font-bold tracking-tight uppercase transition-all hover:bg-blue-600 hover:text-white"
                    >
                        <a
                            href="/students/template/download"
                            download
                            class="flex items-center"
                        >
                            <Download class="mr-2 h-4 w-4" />
                            MANIFEST_REF
                        </a>
                    </Button>
                </div>

                <Button
                    @click="showErrorModal = false"
                    class="h-14 w-full rounded-[1.5rem] border-0 bg-slate-900 text-sm font-bold tracking-tight text-white uppercase shadow-xl shadow-slate-900/10 transition-all hover:bg-slate-800"
                >
                    RECALIBRATE & RETRY
                </Button>
            </div>
        </DialogContent>
    </Dialog>
</template>
