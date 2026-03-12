<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { Upload, Download, X, FileText, CheckCircle2, AlertCircle } from 'lucide-vue-next';
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
const flashError = computed(() => (page.props as any).flash?.error as string | null);
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

    form.post('/teachers/bulk-upload', {
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
        <DialogContent class="sm:max-w-[500px]">
            <DialogHeader>
                <DialogTitle>Bulk Upload Teachers</DialogTitle>
                <DialogDescription>
                    Upload a CSV file to add multiple teachers at once. 
                    Download the template below to ensure your data is formatted correctly.
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-6 py-4">
                <div class="flex items-center justify-between rounded-lg border bg-muted/30 p-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-500/10">
                            <FileText class="h-6 w-6 text-purple-600" />
                        </div>
                        <div>
                            <p class="text-sm font-medium">CSV Template</p>
                            <p class="text-xs text-muted-foreground">Required format for successful upload</p>
                        </div>
                    </div>
                    <Button variant="outline" size="sm" as-child>
                        <a href="/teachers/template/download" download>
                            <Download class="mr-2 h-4 w-4" />
                            Download
                        </a>
                    </Button>
                </div>

                <div
                    class="relative flex flex-col items-center justify-center rounded-xl border-2 border-dashed p-10 transition-colors"
                    :class="[dragOver ? 'border-primary bg-primary/5' : 'border-muted-foreground/20', form.file ? 'bg-muted/30' : '']"
                    @dragover.prevent="dragOver = true"
                    @dragleave.prevent="dragOver = false"
                    @drop.prevent="handleDrop"
                >
                    <input
                        ref="fileInput"
                        type="file"
                        accept=".csv"
                        class="absolute inset-0 cursor-pointer opacity-0"
                        @change="handleFileUpload"
                    />

                    <template v-if="!form.file">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-muted">
                            <Upload class="h-6 w-6 text-muted-foreground" />
                        </div>
                        <p class="mt-4 text-sm font-medium">Click or drag CSV file to upload</p>
                        <p class="mt-1 text-xs text-muted-foreground">Maximum file size: 5MB</p>
                    </template>

                    <template v-else>
                        <div class="flex w-full items-center gap-4 min-w-0">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-purple-500/10">
                                <FileText class="h-6 w-6 text-purple-600" />
                            </div>
                            <div class="flex-1 min-w-0 overflow-hidden">
                                <p class="text-sm font-medium break-all leading-tight text-foreground" :title="form.file.name">
                                    {{ form.file.name }}
                                </p>
                                <p class="mt-1 text-xs text-muted-foreground">{{ (form.file.size / 1024).toFixed(2) }} KB</p>
                            </div>
                            <Button variant="ghost" size="icon" class="h-8 w-8 shrink-0 text-muted-foreground hover:text-destructive" @click.stop="removeFile">
                                <X class="h-4 w-4" />
                            </Button>
                        </div>
                    </template>
                </div>

                <div v-if="form.errors.file" class="flex items-start gap-2 text-xs text-destructive">
                    <AlertCircle class="h-3 w-3" />
                    <span>{{ form.errors.file }}</span>
                </div>

                <div v-if="flashError" class="flex flex-col gap-2 rounded-lg bg-destructive/10 p-4 text-xs text-destructive border border-destructive/20 overflow-hidden">
                    <div class="flex items-center gap-2 font-semibold">
                        <AlertCircle class="h-4 w-4 shrink-0" />
                        <span>Upload Failed</span>
                    </div>
                    <p class="break-words leading-relaxed">{{ flashError }}</p>
                    <p class="mt-1 text-[10px] opacity-80">Please check your file format and try again. Use the template provided above.</p>
                </div>

                <div v-if="form.wasSuccessful && !flashError" class="flex items-center gap-2 rounded-lg bg-green-500/10 p-3 text-xs text-green-600">
                    <CheckCircle2 class="h-4 w-4" />
                    <span>File uploaded successfully!</span>
                </div>
            </div>

            <DialogFooter>
                <Button variant="outline" @click="closeModal" :disabled="form.processing">Cancel</Button>
                <Button @click="submit" :disabled="!form.file || form.processing">
                    <template v-if="form.processing">Uploading...</template>
                    <template v-else>Start Upload</template>
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <!-- Detailed Error Modal -->
    <Dialog :open="showErrorModal" @update:open="showErrorModal = $event">
        <DialogContent class="sm:max-w-[550px]">
            <DialogHeader>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-destructive/10 mb-4">
                    <AlertCircle class="h-6 w-6 text-destructive" />
                </div>
                <DialogTitle class="text-xl text-destructive">Invalid CSV Format</DialogTitle>
                <DialogDescription class="text-base">
                    We encountered some issues with your uploaded file. Please review the errors below.
                </DialogDescription>
            </DialogHeader>

            <div class="mt-4 rounded-lg bg-muted p-4 border max-h-[300px] overflow-y-auto">
                <p class="text-sm font-medium text-foreground mb-2">Error Details:</p>
                <p class="text-sm text-destructive break-words leading-relaxed whitespace-pre-line">
                    {{ flashError }}
                </p>
            </div>

            <div class="mt-6 flex flex-col gap-3">
                <div class="flex items-center gap-3 rounded-lg border border-primary/20 bg-primary/5 p-4">
                    <FileText class="h-5 w-5 text-primary shrink-0" />
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium">Quick Tip</p>
                        <p class="text-xs text-muted-foreground">Download our template to ensure your columns match exactly what the system expects.</p>
                    </div>
                    <Button variant="outline" size="sm" as-child class="shrink-0">
                        <a href="/teachers/template/download" download>
                            <Download class="mr-2 h-4 w-4" />
                            Get Template
                        </a>
                    </Button>
                </div>
            </div>

            <DialogFooter class="mt-6">
                <Button @click="showErrorModal = false" class="w-full sm:w-auto">
                    I'll Fix and Try Again
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
