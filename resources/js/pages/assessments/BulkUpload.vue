<script setup lang="ts">
/* global route */
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    Upload, FileText, CheckCircle2, AlertCircle, 
    Download, ArrowRight, Info, ShieldCheck,
    Users, BookOpen, GraduationCap, ClipboardList
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { 
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue 
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
    { title: 'Bulk Upload', href: '/assessments/bulk-upload' }
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Bulk Upload Marks" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Pulsar Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-500/10">
                        <ClipboardList class="h-6 w-6 text-violet-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight uppercase">Bulk Marks Entry</h1>
                        <p class="text-muted-foreground">Upload assessment results using a structured CSV file.</p>
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

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Instructions & Tips (Pulsar Style) -->
                <div class="space-y-6">
                    <div class="bg-card rounded-xl border shadow-sm p-6 space-y-6">
                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em] flex items-center gap-2">
                             <Info class="h-4 w-4 text-violet-600" /> IMPORT PROTOCOL
                        </h3>
                        <ul class="space-y-6">
                            <li class="flex gap-4">
                                <span class="h-6 w-6 rounded-lg bg-violet-50 text-violet-600 flex items-center justify-center shrink-0 font-black text-[10px] border border-violet-100">01</span>
                                <p class="text-xs text-gray-500 font-bold leading-relaxed uppercase tracking-tight">Prepare your data using the <span class="text-violet-700">official CSV template</span>.</p>
                            </li>
                            <li class="flex gap-4">
                                <span class="h-6 w-6 rounded-lg bg-violet-50 text-violet-600 flex items-center justify-center shrink-0 font-black text-[10px] border border-violet-100">02</span>
                                <p class="text-xs text-gray-500 font-bold leading-relaxed uppercase tracking-tight">Ensure <span class="text-gray-900">Admission Numbers</span> match student records.</p>
                            </li>
                            <li class="flex gap-4">
                                <span class="h-6 w-6 rounded-lg bg-violet-50 text-violet-600 flex items-center justify-center shrink-0 font-black text-[10px] border border-violet-100">03</span>
                                <p class="text-xs text-gray-500 font-bold leading-relaxed uppercase tracking-tight">Enter scores without <span class="text-gray-900">exceeding Max Marks</span>.</p>
                            </li>
                        </ul>
                    </div>

                    <div class="bg-amber-50 border border-amber-100 rounded-xl p-6">
                        <div class="flex items-center gap-3 mb-2 text-amber-700">
                            <AlertCircle class="h-4 w-4" />
                            <h4 class="font-black text-[10px] uppercase tracking-widest">Validation Warning</h4>
                        </div>
                        <p class="text-[10px] text-amber-600 font-bold leading-relaxed uppercase tracking-tight italic opacity-80">
                            The system will automatically validate each row. Invalid admission numbers or out-of-range scores will be rejected.
                        </p>
                    </div>
                </div>

                <!-- Upload Form (Pulsar Style) -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-card rounded-xl border shadow-sm p-8 space-y-8">
                        <!-- Assessment Selection -->
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Target Assessment</label>
                            <Select v-model="form.assessment_id">
                                <SelectTrigger class="h-12 rounded-lg bg-gray-50/50 border-gray-100 font-bold text-gray-900 text-sm">
                                    <SelectValue placeholder="Select an assessment..." />
                                </SelectTrigger>
                                <SelectContent class="rounded-xl shadow-2xl border-gray-100">
                                    <SelectItem v-for="asmt in assessments" :key="asmt.id" :value="asmt.id.toString()">
                                        <div class="flex flex-col py-1">
                                            <span class="font-black text-xs text-gray-900">{{ asmt.title }}</span>
                                            <span class="text-[9px] text-gray-400 uppercase font-bold tracking-widest mt-0.5">{{ asmt.subject?.name }} · {{ asmt.class?.name }}</span>
                                        </div>
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Dropzone (Pulsar Style) -->
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Source Document</label>
                            <div 
                                @dragover.prevent="isDragging = true"
                                @dragleave.prevent="isDragging = false"
                                @drop.prevent="handleFileDrop"
                                @click="fileInput?.click()"
                                :class="[
                                    'group relative h-56 rounded-xl border-2 border-dashed transition-all duration-300 flex flex-col items-center justify-center cursor-pointer',
                                    isDragging ? 'bg-violet-50/50 border-violet-400 border-solid' : 'bg-gray-50/30 border-gray-100 hover:bg-white hover:border-violet-200'
                                ]"
                            >
                                <input 
                                    ref="fileInput"
                                    type="file" 
                                    class="hidden" 
                                    accept=".csv"
                                    @change="handleFileSelect"
                                />
                                
                                <div v-if="!form.file" class="text-center space-y-4">
                                    <div class="h-14 w-14 rounded-xl bg-white border border-gray-100 shadow-sm flex items-center justify-center mx-auto group-hover:bg-violet-600 transition-all duration-300">
                                        <Upload class="h-6 w-6 text-gray-300 group-hover:text-white" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-gray-900 uppercase">Drop CSV File</p>
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">or click to browse database</p>
                                    </div>
                                </div>
                                <div v-else class="text-center space-y-4 animate-in zoom-in-95 duration-300">
                                    <div class="h-14 w-14 rounded-xl bg-emerald-50 border border-emerald-100 shadow-sm flex items-center justify-center mx-auto">
                                        <FileText class="h-6 w-6 text-emerald-600" />
                                    </div>
                                    <div class="px-6">
                                        <p class="text-sm font-black text-gray-900 truncate max-w-[200px]">{{ form.file.name }}</p>
                                        <p class="text-[9px] font-black text-emerald-600 uppercase tracking-widest mt-1 italic">{{ (form.file.size / 1024).toFixed(1) }} KB · Ready</p>
                                    </div>
                                    <button 
                                        @click.stop="form.file = null"
                                        class="text-[10px] font-black text-rose-600 hover:underline uppercase tracking-widest"
                                    >Remove</button>
                                </div>
                            </div>
                        </div>

                        <!-- Action -->
                        <div class="flex items-center justify-between pt-6 border-t border-gray-50">
                            <p class="text-[10px] font-black text-gray-400 italic flex items-center gap-2">
                                <ShieldCheck class="h-3.5 w-3.5 text-emerald-600" /> Secure Node Processing
                            </p>
                            <Button 
                                @click="submit"
                                :disabled="form.processing || !form.assessment_id || !form.file"
                                class="h-12 px-8 rounded-lg bg-violet-600 hover:bg-violet-700 text-white font-black text-xs uppercase tracking-[0.2em] shadow-lg shadow-violet-100 transition-all duration-300 active:scale-95"
                            >
                                <ArrowRight class="mr-2 h-4 w-4" /> Finalize Import
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
