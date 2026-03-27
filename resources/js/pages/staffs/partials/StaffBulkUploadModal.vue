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
        <DialogContent class="sm:max-w-[550px] p-0 overflow-hidden border-0 shadow-2xl rounded-2xl">
            <div class="relative p-8 pt-10">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-purple-500 via-indigo-500 to-purple-500"></div>
                <DialogHeader>
                    <div class="flex items-center gap-4 mb-2">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-purple-600 text-white shadow-lg shadow-purple-100/50">
                            <Upload class="h-6 w-6" />
                        </div>
                        <div>
                            <DialogTitle class="text-2xl font-bold text-foreground">Bulk Registration</DialogTitle>
                            <DialogDescription class="text-sm text-muted-foreground font-medium mt-0.5">
                                Onboard multiple educators using CSV data.
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>

                <div class="space-y-8 mt-8">
                    <!-- Template Card -->
                    <div class="group relative overflow-hidden rounded-2xl border border-border bg-muted/30 p-5 transition-all hover:bg-muted/50">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-background border border-border shadow-sm group-hover:scale-110 transition-transform">
                                    <Download class="h-6 w-6 text-purple-600" />
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-foreground">Standardized Template</p>
                                    <p class="text-xs text-muted-foreground mt-0.5 leading-relaxed">Required CSV format for seamless data ingestion.</p>
                                </div>
                            </div>
                            <Button variant="outline" size="sm" as-child class="h-10 rounded-xl px-4 font-bold text-[10px] uppercase tracking-wider transition-all hover:bg-background border-border bg-background/50 shadow-sm">
                                <a href="/staffs/template/download" download>
                                    Get Template
                                </a>
                            </Button>
                        </div>
                    </div>

                    <!-- Upload Zone -->
                    <div
                        class="relative flex flex-col items-center justify-center rounded-2xl border-2 border-dashed p-12 transition-all duration-300 group/drop"
                        :class="[
                            dragOver ? 'border-purple-500 bg-purple-50/50 scale-[0.99]' : 'border-border bg-muted/20 hover:bg-muted/30',
                            form.file ? 'bg-purple-50/30 border-purple-200' : ''
                        ]"
                        @dragover.prevent="dragOver = true"
                        @dragleave.prevent="dragOver = false"
                        @drop.prevent="handleDrop"
                    >
                        <input
                            ref="fileInput"
                            type="file"
                            accept=".csv"
                            class="absolute inset-0 cursor-pointer opacity-0 z-10"
                            @change="handleFileUpload"
                        />

                        <template v-if="!form.file">
                            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-background border border-border shadow-sm group-hover/drop:scale-110 transition-transform duration-500">
                                <Upload class="h-8 w-8 text-purple-600/50" />
                            </div>
                            <div class="mt-6 text-center">
                                <p class="text-sm font-bold text-foreground">Drop staff CSV here</p>
                                <p class="mt-1 text-xs text-muted-foreground px-4">Maximum file size: 5MB • Standard UTF-8 encoding</p>
                            </div>
                        </template>

                        <template v-else>
                            <div class="flex w-full flex-col items-center gap-4 animate-in zoom-in duration-300">
                                <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-white border border-purple-100 shadow-xl shadow-purple-100/20">
                                    <FileText class="h-10 w-10 text-purple-600" />
                                </div>
                                <div class="text-center min-w-0 max-w-full px-4">
                                    <p class="text-sm font-bold break-all leading-tight text-foreground truncate" :title="form.file.name">
                                        {{ form.file.name }}
                                    </p>
                                    <div class="flex items-center justify-center gap-2 mt-2">
                                        <Badge variant="outline" class="bg-purple-50 text-purple-600 border-purple-100 text-[10px] font-bold">READY TO SYNC</Badge>
                                        <span class="text-[10px] font-bold text-muted-foreground">{{ (form.file.size / 1024).toFixed(2) }} KB</span>
                                    </div>
                                </div>
                                <Button variant="ghost" size="sm" class="h-9 rounded-lg text-muted-foreground hover:text-destructive hover:bg-destructive/5 font-bold text-[10px] uppercase tracking-wider mt-2 z-20" @click.stop="removeFile">
                                    <X class="mr-2 h-3 w-3" />
                                    Remove File
                                </Button>
                            </div>
                        </template>
                    </div>

                    <div v-if="form.errors.file" class="flex items-start gap-3 rounded-xl bg-destructive/5 p-4 border border-destructive/10 animate-in slide-in-from-top-2 duration-300">
                        <AlertCircle class="h-5 w-5 text-destructive shrink-0 mt-0.5" />
                        <span class="text-xs font-medium text-destructive leading-relaxed">{{ form.errors.file }}</span>
                    </div>

                    <div v-if="flashError" class="flex flex-col gap-3 rounded-xl bg-destructive/5 p-5 border border-destructive/20 animate-in slide-in-from-top-2 duration-300">
                        <div class="flex items-center gap-2 font-bold text-destructive">
                            <AlertCircle class="h-4 w-4 shrink-0" />
                            <span class="text-xs uppercase tracking-wider">Ingestion Error</span>
                        </div>
                        <p class="text-[11px] text-destructive/80 leading-relaxed font-medium break-words">{{ flashError }}</p>
                        <p class="mt-1 text-[9px] font-bold text-destructive/60 uppercase tracking-tighter">Please recalibrate file and try again</p>
                    </div>

                    <div v-if="form.wasSuccessful && !flashError" class="flex items-center gap-3 rounded-xl bg-emerald-50 p-4 border border-emerald-100 text-emerald-700 animate-in slide-in-from-top-2 duration-300">
                        <CheckCircle2 class="h-5 w-5" />
                        <span class="text-xs font-bold leading-none">Data synchronized successfully!</span>
                    </div>
                </div>
            </div>

            <DialogFooter class="flex flex-col gap-2 p-6 bg-muted/30 border-t sm:flex-row sm:justify-end sm:items-center">
                <Button variant="ghost" @click="closeModal" :disabled="form.processing" class="h-11 px-6 rounded-xl font-bold text-muted-foreground hover:text-foreground">
                    Cancel
                </Button>
                <Button @click="submit" :disabled="!form.file || form.processing" class="h-11 px-10 rounded-xl bg-purple-600 text-white font-bold shadow-lg shadow-purple-100 hover:bg-purple-700 transition-all border-0">
                    <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                    <Upload v-else class="mr-2 h-4 w-4" />
                    <template v-if="form.processing">Synchronizing...</template>
                    <template v-else>Register Staff</template>
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <!-- Detailed Error Modal -->
    <Dialog :open="showErrorModal" @update:open="showErrorModal = $event">
        <DialogContent class="sm:max-w-[550px] p-0 overflow-hidden border-0 shadow-2xl rounded-2xl">
            <div class="relative p-8 pt-10 text-center">
                <div class="absolute top-0 left-0 w-full h-1 bg-destructive"></div>
                <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-destructive/10 border-4 border-white shadow-sm ring-1 ring-destructive/10 animate-in zoom-in duration-300">
                    <AlertCircle class="h-10 w-10 text-destructive" />
                </div>
                <DialogHeader>
                    <DialogTitle class="text-2xl font-bold text-foreground">Data Format Conflict</DialogTitle>
                    <DialogDescription class="text-sm text-muted-foreground mt-2 px-6 leading-relaxed">
                        We encountered structural issues while parsing your CSV file. Please review the specific error details below.
                    </DialogDescription>
                </DialogHeader>

                <div class="mt-8 rounded-2xl bg-muted/50 p-6 border border-border text-left max-h-[300px] overflow-y-auto custom-scrollbar">
                    <p class="text-xs font-bold text-foreground uppercase tracking-widest mb-3 flex items-center gap-2">
                        <span class="h-1.5 w-1.5 rounded-full bg-destructive animate-pulse"></span>
                        Error Trace
                    </p>
                    <p class="text-[11px] text-destructive break-words leading-relaxed font-semibold whitespace-pre-line">
                        {{ flashError }}
                    </p>
                </div>

                <div class="mt-8 flex flex-col gap-3">
                    <div class="flex items-center gap-4 rounded-2xl border border-purple-100 bg-purple-50/50 p-4 text-left transition-all hover:bg-purple-50">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white border border-purple-100 shadow-sm transition-transform group-hover:scale-110">
                            <FileText class="h-5 w-5 text-purple-600" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-purple-900">Optimization Tip</p>
                            <p class="text-[10px] text-purple-700/70 mt-0.5 leading-relaxed font-medium">Download the latest template to ensure all column headers are perfectly calibrated.</p>
                        </div>
                        <Button variant="outline" size="sm" as-child class="shrink-0 h-9 rounded-lg border-purple-200 bg-white text-purple-700 font-bold text-[10px] uppercase tracking-wider hover:bg-purple-50 shadow-sm transition-all px-4">
                            <a href="/staffs/template/download" download>
                                Get Template
                            </a>
                        </Button>
                    </div>
                </div>
            </div>

            <DialogFooter class="mt-4 p-6 bg-muted/30 border-t flex items-center justify-center">
                <Button @click="showErrorModal = false" class="w-full h-11 rounded-xl bg-foreground text-background font-bold shadow-lg shadow-foreground/10 transition-all border-0">
                    I'll Calibrate and Retry
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
