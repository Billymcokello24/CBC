<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import {
    Upload,
    Download,
    FileSpreadsheet,
    X,
    CheckCircle2,
    AlertCircle,
    Loader2,
} from 'lucide-vue-next';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';

const props = defineProps<{
    open: boolean;
    title: string;
    description: string;
    templateUrl: string;
    uploadUrl: string;
}>();

const emit = defineEmits(['update:open', 'success']);

const fileInput = ref<HTMLInputElement | null>(null);
const selectedFile = ref<File | null>(null);
const isDragging = ref(false);

const form = useForm({
    file: null as File | null,
});

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        selectedFile.value = target.files[0];
        form.file = selectedFile.value;
    }
};

const handleDrop = (event: DragEvent) => {
    isDragging.value = false;
    if (event.dataTransfer?.files && event.dataTransfer.files.length > 0) {
        selectedFile.value = event.dataTransfer.files[0];
        form.file = selectedFile.value;
    }
};

const removeFile = () => {
    selectedFile.value = null;
    form.file = null;
    if (fileInput.value) fileInput.value.value = '';
};

const submit = () => {
    if (!form.file) return;

    form.post(props.uploadUrl, {
        onSuccess: () => {
            emit('success');
            emit('update:open', false);
            removeFile();
        },
    });
};
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="sm:max-w-[500px]">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2 text-xl">
                    <Upload class="h-5 w-5 text-primary" />
                    {{ title }}
                </DialogTitle>
                <DialogDescription>
                    {{ description }}
                </DialogDescription>
            </DialogHeader>

            <div class="grid gap-6 py-4">
                <!-- Template Download Section -->
                <div
                    class="flex flex-col gap-4 rounded-xl border border-border bg-muted/10 p-4"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-white shadow-sm"
                            >
                                <FileSpreadsheet class="h-5 w-5 text-primary" />
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-foreground">Need a template?</p>
                                <p class="text-xs text-muted-foreground">Download our CSV/Excel format</p>
                            </div>
                        </div>
                        <Button
                            variant="ghost"
                            size="sm"
                            class="font-semibold text-primary hover:text-primary/80"
                            as-child
                        >
                            <a :href="templateUrl" download>
                                <Download class="mr-2 h-4 w-4" />
                                Template
                            </a>
                        </Button>
                    </div>

                    <div class="space-y-2 border-t border-border/50 pt-2">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground opacity-70">Mandatory Data Points</p>
                        <div class="flex flex-wrap gap-1.5">
                            <Badge v-for="tag in ['title', 'class_name', 'subject_name', 'assessment_type', 'source', 'date']" :key="tag" variant="outline" class="rounded-lg border-primary/20 bg-primary/5 px-2 py-0.5 text-[8px] font-black tracking-tight text-primary uppercase">{{ tag.replace('_', ' ') }}</Badge>
                        </div>
                    </div>
                </div>

                <!-- Dropzone -->
                <div
                    class="relative rounded-2xl border-2 border-dashed p-8 text-center transition-all duration-200"
                    :class="[
                        isDragging
                            ? 'scale-[1.02] border-primary bg-primary/5'
                            : 'border-border hover:border-primary/30 hover:bg-muted/10',
                        selectedFile ? 'border-green-300 bg-green-50/10' : '',
                    ]"
                    @dragover.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    @drop.prevent="handleDrop"
                >
                    <input
                        type="file"
                        ref="fileInput"
                        class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                        accept=".csv,.xlsx,.xls"
                        @change="handleFileSelect"
                    />

                    <div
                        v-if="!selectedFile"
                        class="flex flex-col items-center gap-3"
                    >
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-muted text-muted-foreground"
                        >
                            <Upload class="h-6 w-6" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-foreground">Click or drag file to upload</p>
                            <p class="mt-1 text-xs text-muted-foreground">Supports CSV, Excel (.xlsx, .xls)</p>
                        </div>
                    </div>

                    <div v-else class="flex flex-col items-center gap-3">
                        <div
                            class="flex h-12 w-12 animate-in items-center justify-center rounded-2xl bg-green-100 text-green-600 duration-300 zoom-in-50"
                        >
                            <CheckCircle2 class="h-6 w-6" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="max-w-[250px] truncate text-sm font-semibold text-foreground">{{ selectedFile.name }}</p>
                            <p class="text-xs font-medium text-green-600">
                                Ready to import
                            </p>
                        </div>
                        <Button
                            variant="ghost"
                            size="icon"
                            class="absolute top-2 right-2 h-8 w-8 text-gray-400 hover:text-red-500"
                            @click.stop="removeFile"
                        >
                            <X class="h-4 w-4" />
                        </Button>
                    </div>
                </div>

                <Alert
                    v-if="form.errors.file"
                    variant="destructive"
                    class="border-red-200 bg-red-50 text-red-900"
                >
                    <AlertCircle class="h-4 w-4" />
                    <AlertTitle>Upload Error</AlertTitle>
                    <AlertDescription>{{ form.errors.file }}</AlertDescription>
                </Alert>
            </div>

            <DialogFooter class="gap-4 sm:justify-between">
                <Button
                    variant="ghost"
                    @click="emit('update:open', false)"
                    class="font-bold"
                    >Cancel</Button
                >
                <Button
                    @click="submit"
                    :disabled="!selectedFile || form.processing"
                    class="bg-primary px-8 font-semibold text-white hover:bg-primary/90"
                >
                    <Loader2
                        v-if="form.processing"
                        class="mr-2 h-4 w-4 animate-spin"
                    />
                    <Save v-else class="mr-2 h-4 w-4" />
                    Start Import
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
