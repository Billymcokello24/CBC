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
                    <Upload class="h-5 w-5 text-indigo-600" />
                    {{ title }}
                </DialogTitle>
                <DialogDescription>
                    {{ description }}
                </DialogDescription>
            </DialogHeader>

            <div class="grid gap-6 py-4">
                <!-- Template Download Section -->
                <div
                    class="flex items-center justify-between rounded-xl border border-indigo-100 bg-indigo-50 p-4"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-white shadow-sm"
                        >
                            <FileSpreadsheet class="h-5 w-5 text-indigo-600" />
                        </div>
                        <div>
                            <p class="text-sm font-bold text-indigo-900">
                                Need a template?
                            </p>
                            <p class="text-xs text-indigo-600/70">
                                Download our CSV/Excel format
                            </p>
                        </div>
                    </div>
                    <Button
                        variant="ghost"
                        size="sm"
                        class="font-bold text-indigo-600 hover:text-indigo-700"
                        as-child
                    >
                        <a :href="templateUrl" download>
                            <Download class="mr-2 h-4 w-4" />
                            Template
                        </a>
                    </Button>
                </div>

                <!-- Dropzone -->
                <div
                    class="relative rounded-2xl border-2 border-dashed p-8 text-center transition-all duration-200"
                    :class="[
                        isDragging
                            ? 'scale-[1.02] border-indigo-500 bg-indigo-50/50'
                            : 'border-gray-200 hover:border-indigo-300 hover:bg-gray-50/50',
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
                            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-500 transition-colors group-hover:bg-indigo-100 group-hover:text-indigo-600"
                        >
                            <Upload class="h-6 w-6" />
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-900">
                                Click or drag file to upload
                            </p>
                            <p class="mt-1 text-xs text-gray-500">
                                Supports CSV, Excel (.xlsx, .xls)
                            </p>
                        </div>
                    </div>

                    <div v-else class="flex flex-col items-center gap-3">
                        <div
                            class="flex h-12 w-12 animate-in items-center justify-center rounded-2xl bg-green-100 text-green-600 duration-300 zoom-in-50"
                        >
                            <CheckCircle2 class="h-6 w-6" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p
                                class="max-w-[250px] truncate text-sm font-bold text-gray-900"
                            >
                                {{ selectedFile.name }}
                            </p>
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
                    class="bg-indigo-600 px-8 font-bold hover:bg-indigo-700"
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
