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
        <DialogContent class="sm:max-w-[540px] rounded-[2.5rem] border-slate-100 p-0 overflow-hidden shadow-2xl animate-in zoom-in-95 duration-300">
            <div class="border-b border-slate-50 bg-slate-50/50 px-8 py-6">
                <div class="flex items-center gap-4">
                    <div class="h-12 w-12 rounded-2xl bg-slate-900 text-white flex items-center justify-center shadow-lg shadow-slate-900/10 shrink-0">
                        <Upload class="h-6 w-6" />
                    </div>
                    <div>
                        <DialogTitle class="text-xl font-black text-slate-900 uppercase italic tracking-tight">Mass Node Ingest</DialogTitle>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1 italic opacity-70">Bulk Registry Propagation Protocol</p>
                    </div>
                </div>
            </div>

            <div class="p-8 space-y-8">
                <div class="flex items-center justify-between rounded-[2rem] border border-blue-100 bg-blue-50/20 p-5 group transition-all hover:bg-blue-50/40">
                    <div class="flex items-center gap-4">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-600 text-white shadow-lg shadow-blue-600/10">
                            <FileText class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-900 uppercase tracking-widest italic">Core Template</p>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1 italic">Structural Manifest Required</p>
                        </div>
                    </div>
                    <Button variant="outline" size="sm" as-child class="h-10 px-5 rounded-xl border-blue-200 bg-white hover:bg-blue-600 hover:text-white hover:border-blue-600 font-black text-[9px] uppercase tracking-[0.2em] transition-all shadow-sm">
                        <a href="/students/template/download" download class="flex items-center">
                            <Download class="mr-2 h-3.5 w-3.5" />
                            GET_SOURCE
                        </a>
                    </Button>
                </div>

                <div
                    class="relative flex flex-col items-center justify-center rounded-[2.5rem] border-2 border-dashed p-12 transition-all duration-300 group"
                    :class="[
                        dragOver ? 'border-blue-600 bg-blue-50/50 scale-[0.99]' : 'border-slate-200 bg-slate-50/30', 
                        form.file ? 'border-emerald-500 bg-emerald-50/20' : 'hover:border-slate-300 hover:bg-slate-50/50'
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
                        <div class="flex h-16 w-16 items-center justify-center rounded-[1.5rem] bg-white shadow-xl shadow-slate-200/50 group-hover:scale-110 transition-transform duration-500">
                            <Upload class="h-8 w-8 text-slate-400 group-hover:text-blue-600 transition-colors" />
                        </div>
                        <p class="mt-6 text-[10px] font-black text-slate-900 uppercase tracking-[0.2em] italic">Drop CSV Payload</p>
                        <p class="mt-2 text-[8px] font-black text-slate-400 uppercase tracking-[0.3em] opacity-60 italic">MAX_DENSITY: 5.0 MB</p>
                    </template>

                    <template v-else>
                        <div class="flex w-full items-center gap-5 min-w-0 animate-in zoom-in-95 duration-300">
                            <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-[1.5rem] bg-emerald-600 text-white shadow-xl shadow-emerald-500/20">
                                <FileText class="h-8 w-8" />
                            </div>
                            <div class="flex-1 min-w-0 overflow-hidden">
                                <p class="text-[11px] font-black text-slate-900 uppercase tracking-widest italic truncate leading-none" :title="form.file.name">
                                    {{ form.file.name }}
                                </p>
                                <p class="mt-2 text-[9px] font-black text-emerald-600 uppercase tracking-widest italic opacity-70">{{ (form.file.size / 1024).toFixed(2) }} KB | READY_FOR_SYNC</p>
                            </div>
                            <Button variant="ghost" size="icon" class="h-10 w-10 shrink-0 rounded-xl text-slate-400 hover:text-red-500 hover:bg-red-50 transition-all z-20" @click.stop="removeFile">
                                <X class="h-5 w-5" />
                            </Button>
                        </div>
                    </template>
                </div>

                <div v-if="form.errors.file" class="flex items-center gap-3 rounded-2xl bg-red-50 p-4 animate-in slide-in-from-top-2 duration-300 border border-red-100">
                    <AlertCircle class="h-4 w-4 text-red-500 shrink-0" />
                    <span class="text-[9px] font-black text-red-600 uppercase tracking-widest italic">{{ form.errors.file }}</span>
                </div>

                <div v-if="flashError && !showErrorModal" class="flex flex-col gap-3 rounded-2xl bg-red-50 p-5 text-red-600 border border-red-100 max-h-[160px] overflow-y-auto animate-in fade-in duration-300">
                    <div class="flex items-center gap-3 font-black text-[10px] uppercase tracking-widest italic">
                        <AlertCircle class="h-4 w-4 shrink-0" />
                        <span>Payload Exception Detected</span>
                    </div>
                    <p class="text-[9px] font-black uppercase tracking-widest leading-relaxed opacity-80 italic">{{ flashError }}</p>
                </div>

                <div v-if="form.wasSuccessful && !flashError" class="flex items-center gap-3 rounded-2xl bg-emerald-50 p-4 text-emerald-600 animate-in slide-in-from-top-2 duration-300 border border-emerald-100">
                    <CheckCircle2 class="h-5 w-5 shrink-0" />
                    <span class="text-[10px] font-black uppercase tracking-widest italic">PAYLOAD_INGESTED_SUCCESSFULLY</span>
                </div>
            </div>

            <div class="p-8 bg-slate-50/50 border-t border-slate-100 flex items-center justify-end gap-4">
                <Button variant="ghost" @click="closeModal" :disabled="form.processing" class="h-12 px-8 rounded-2xl font-black text-[10px] uppercase tracking-widest text-slate-400 hover:text-slate-900 transition-all">ABORT</Button>
                <Button @click="submit" :disabled="!form.file || form.processing" class="h-12 px-10 rounded-2xl bg-slate-900 text-white hover:bg-slate-800 font-black text-[10px] uppercase tracking-widest shadow-xl shadow-slate-900/10 transition-all border-0">
                    <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                    <Upload v-else class="mr-2 h-4 w-4" />
                    INITIATE_INGEST
                </Button>
            </div>
        </DialogContent>
    </Dialog>

    <!-- Detailed Error Modal -->
    <Dialog :open="showErrorModal" @update:open="showErrorModal = $event">
        <DialogContent class="sm:max-w-[580px] rounded-[3rem] border-red-100 p-0 overflow-hidden shadow-2xl animate-in zoom-in-95 duration-300">
            <div class="bg-red-600 px-10 py-8 text-white">
                <div class="flex items-center gap-5">
                    <div class="h-16 w-16 rounded-3xl bg-white/20 backdrop-blur-md flex items-center justify-center shadow-xl">
                        <AlertCircle class="h-10 w-10 text-white" />
                    </div>
                    <div>
                        <DialogTitle class="text-2xl font-black uppercase italic tracking-tight">Logical Fault Detected</DialogTitle>
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] mt-1 italic opacity-80">Payload Validation Breach</p>
                    </div>
                </div>
            </div>

            <div class="p-10 space-y-8">
                <p class="text-sm font-black text-slate-500 uppercase tracking-widest leading-relaxed italic opacity-80 px-2">
                    THE SUBMITTED MANIFEST CONTAINS STRUCTURAL INCONSISTENCIES THAT COMPROMISE REGISTRY INTEGRITY. REVIEW THE EXCEPTIONS BELOW:
                </p>

                <div class="rounded-[2rem] bg-slate-50 p-8 border border-slate-100 max-h-[300px] overflow-y-auto shadow-inner">
                    <p class="text-[9px] font-black text-red-600 uppercase tracking-widest leading-loose italic whitespace-pre-wrap">
                        {{ flashError }}
                    </p>
                </div>

                <div class="flex items-center gap-5 rounded-[2rem] border border-blue-100 bg-blue-50/30 p-6 group transition-all hover:bg-blue-50/50">
                    <div class="h-12 w-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/20 shrink-0">
                        <FileText class="h-6 w-6" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[10px] font-black text-slate-900 uppercase tracking-widest italic">Corrective Action</p>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1 italic leading-relaxed">RE-ALIGNE PAYLOAD AGAINST THE OFFICIAL MANIFEST HANDLE FOR SEAMLESS INTEGRATION.</p>
                    </div>
                    <Button variant="outline" size="sm" as-child class="h-11 px-5 rounded-xl border-blue-200 bg-white font-black text-[9px] uppercase tracking-widest hover:bg-blue-600 hover:text-white transition-all">
                        <a href="/students/template/download" download class="flex items-center">
                            <Download class="mr-2 h-4 w-4" />
                            MANIFEST_REF
                        </a>
                    </Button>
                </div>

                <Button @click="showErrorModal = false" class="w-full h-14 rounded-[1.5rem] bg-slate-900 text-white hover:bg-slate-800 font-black text-[11px] uppercase tracking-[0.2em] shadow-xl shadow-slate-900/10 transition-all border-0 italic">
                    RECALIBRATE & RETRY
                </Button>
            </div>
        </DialogContent>
    </Dialog>
</template>
