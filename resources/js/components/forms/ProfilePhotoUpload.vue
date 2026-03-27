<script setup lang="ts">
import { ref, watch } from 'vue';
import { Camera, X, Upload, User } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

const props = defineProps<{
    modelValue: File | null;
    currentImage?: string | null;
    error?: string;
    label?: string;
}>();

const emit = defineEmits(['update:modelValue']);

const previewUrl = ref<string | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);

// Initialize preview from currentImage or modelValue
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
        emit('update:modelValue', file);
        
        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            previewUrl.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const removePhoto = () => {
    emit('update:modelValue', null);
    previewUrl.value = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const triggerUpload = () => {
    fileInput.value?.click();
};
</script>

<template>
    <div class="space-y-4">
        <div v-if="label" class="text-sm font-medium text-foreground">{{ label }}</div>
        
        <div class="flex items-center gap-6">
            <div 
                class="relative h-24 w-24 overflow-hidden rounded-2xl border-2 border-dashed border-muted-foreground/20 bg-muted/30 transition-all hover:border-indigo-600/30 group"
                @click="triggerUpload"
            >
                <img v-if="previewUrl" :src="previewUrl" class="h-full w-full object-cover" />
                <div v-else class="flex h-full w-full flex-col items-center justify-center text-muted-foreground/40">
                    <User class="h-10 w-10" />
                </div>
                
                <div class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 transition-opacity group-hover:opacity-100 cursor-pointer">
                    <Camera class="h-6 w-6 text-white" />
                </div>
            </div>

            <div class="flex flex-col gap-2">
                <div class="flex items-center gap-2">
                    <Button type="button" variant="outline" size="sm" @click="triggerUpload" class="rounded-xl h-9">
                        <Upload class="mr-2 h-4 w-4" />
                        {{ previewUrl ? 'Change Photo' : 'Upload Photo' }}
                    </Button>
                    <Button v-if="previewUrl" type="button" variant="ghost" size="sm" @click="removePhoto" class="rounded-xl h-9 text-rose-500 hover:text-rose-600 hover:bg-rose-50">
                        <X class="mr-2 h-4 w-4" />
                        Remove
                    </Button>
                </div>
                <p class="text-[11px] text-muted-foreground leading-tight">
                    JPG, PNG or WEBP. Max 2MB.
                </p>
            </div>
        </div>
        
        <input 
            ref="fileInput"
            type="file" 
            class="hidden" 
            accept="image/*"
            @change="handleFileChange"
        />
        
        <p v-if="error" class="text-xs font-medium text-rose-500">{{ error }}</p>
    </div>
</template>
