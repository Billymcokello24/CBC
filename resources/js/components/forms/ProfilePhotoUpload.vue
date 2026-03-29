<script setup lang="ts">
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Camera, X, Upload, User, Loader2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

const props = defineProps<{
    modelValue?: File | null;
    currentImage?: string | null;
    error?: string;
    label?: string;
    uploadUrl?: string;
    isUploading?: boolean;
}>();

const emit = defineEmits(['update:modelValue', 'uploaded', 'uploading']);

const previewUrl = ref<string | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);
const internalIsUploading = ref(false);

// Initialize preview
watch(() => [props.modelValue, props.currentImage], ([newFile, currentImg]) => {
    if (newFile instanceof File) {
        // Preview handled by handleFileChange
    } else if (currentImg && !newFile) {
        previewUrl.value = currentImg as string;
    } else if (!newFile && !currentImg) {
        previewUrl.value = null;
    }
}, { immediate: true });

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files?.length) {
        const file = target.files[0];
        
        if (props.uploadUrl) {
            internalIsUploading.value = true;
            emit('uploading', true);
            router.post(props.uploadUrl, {
                photo: file,
            }, {
                forceFormData: true,
                onSuccess: () => {
                    internalIsUploading.value = false;
                    emit('uploaded');
                },
                onError: () => {
                    internalIsUploading.value = false;
                    emit('uploading', false);
                },
                onFinish: () => {
                    internalIsUploading.value = false;
                    emit('uploading', false);
                }
            });
            return;
        }

        emit('update:modelValue', file);
        
        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            previewUrl.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const triggerUpload = () => {
    if (internalIsUploading.value) return;
    fileInput.value?.click();
};

const removePhoto = () => {
    emit('update:modelValue', null);
    previewUrl.value = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

defineExpose({ triggerUpload });
</script>

<template>
    <div class="relative">
        <!-- If slot provided, use it as trigger -->
        <div v-if="$slots.default" @click="triggerUpload" class="cursor-pointer">
            <slot :is-uploading="internalIsUploading"></slot>
        </div>

        <!-- Default UI -->
        <div v-else class="space-y-4">
            <div v-if="label" class="text-sm font-medium text-foreground">{{ label }}</div>
            
            <div class="flex items-center gap-6">
                <div 
                    class="relative h-24 w-24 overflow-hidden rounded-2xl border-2 border-dashed border-muted-foreground/20 bg-muted/30 transition-all hover:border-indigo-600/30 group cursor-pointer"
                    @click="triggerUpload"
                >
                    <img v-if="previewUrl" :src="previewUrl" class="h-full w-full object-cover" />
                    <div v-else class="flex h-full w-full flex-col items-center justify-center text-muted-foreground/40">
                        <User class="h-10 w-10" />
                    </div>
                    
                    <div class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 transition-opacity group-hover:opacity-100">
                        <Loader2 v-if="internalIsUploading" class="h-6 w-6 text-white animate-spin" />
                        <Camera v-else class="h-6 w-6 text-white" />
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <div class="flex items-center gap-2">
                        <Button type="button" variant="outline" size="sm" @click="triggerUpload" class="rounded-xl h-9" :disabled="internalIsUploading">
                            <Loader2 v-if="internalIsUploading" class="mr-2 h-4 w-4 animate-spin" />
                            <Upload v-else class="mr-2 h-4 w-4" />
                            {{ previewUrl ? 'Change Photo' : 'Upload Photo' }}
                        </Button>
                        <Button v-if="previewUrl && !internalIsUploading" type="button" variant="ghost" size="sm" @click="removePhoto" class="rounded-xl h-9 text-rose-500 hover:text-rose-600 hover:bg-rose-50">
                            <X class="mr-2 h-4 w-4" />
                            Remove
                        </Button>
                    </div>
                    <p class="text-[11px] text-muted-foreground leading-tight">
                        JPG, PNG or WEBP. Max 2MB.
                    </p>
                </div>
            </div>
        </div>
        
        <input 
            ref="fileInput"
            type="file" 
            class="hidden" 
            accept="image/*"
            @change="handleFileChange"
        />
        
        <p v-if="error" class="text-xs font-medium text-rose-500 mt-2">{{ error }}</p>
    </div>
</template>
