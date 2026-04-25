<template>
    <form @submit.prevent="submit" class="space-y-10 animate-in fade-in slide-in-from-bottom-4 duration-700">
        
        <!-- Section: Strategic Alignment -->
        <div class="rounded-[2.5rem] border border-slate-100 bg-white shadow-sm overflow-hidden transition-all hover:shadow-xl hover:shadow-primary/5">
            <div class="bg-slate-50/50 px-10 py-6 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="h-10 w-10 rounded-2xl flex items-center justify-center" :style="{ backgroundColor: themeSecondary, color: themeColor }">
                        <GraduationCap class="h-5 w-5" />
                    </div>
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-tight text-slate-900">Strategic Alignment</h3>
                        <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">Curriculum Matrix Positioning</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <div class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></div>
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest italic font-mono">Formal Logic</span>
                </div>
            </div>
            
            <div class="p-10 grid md:grid-cols-3 gap-8">
                <div class="space-y-3">
                    <Label class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 ml-4">Discipline Area</Label>
                    <div class="relative">
                        <BookOpen class="absolute left-6 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-300" />
                        <select v-model="form.subject_id" class="w-full rounded-2xl border-slate-100 bg-slate-50/30 pl-14 pr-8 h-16 text-[11px] font-black uppercase tracking-widest shadow-inner focus:ring-8 focus:ring-primary/5 focus:bg-white transition-all outline-none border-2" required>
                            <option value="" disabled>Select Area</option>
                            <option v-for="s in subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select>
                    </div>
                </div>
                <div class="space-y-3">
                    <Label class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 ml-4">Grade Level</Label>
                    <div class="relative">
                        <Sparkles class="absolute left-6 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-300" />
                        <select v-model="form.grade_level_id" class="w-full rounded-2xl border-slate-100 bg-slate-50/30 pl-14 pr-8 h-16 text-[11px] font-black uppercase tracking-widest shadow-inner focus:ring-8 focus:ring-primary/5 focus:bg-white transition-all outline-none border-2">
                            <option value="">Universal</option>
                            <option v-for="g in grades" :key="g.id" :value="g.id">{{ g.name }}</option>
                        </select>
                    </div>
                </div>
                <div class="space-y-3">
                    <Label class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 ml-4">Target Folder (Optional)</Label>
                    <div class="relative">
                        <Plus class="absolute left-6 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-300" />
                        <select v-model="form.folder_id" class="w-full rounded-2xl border-slate-100 bg-slate-50/30 pl-14 pr-8 h-16 text-[11px] font-black uppercase tracking-widest shadow-inner focus:ring-8 focus:ring-primary/5 focus:bg-white transition-all outline-none border-2">
                            <option value="">No Folder (Root Index)</option>
                            <option v-for="f in folders" :key="f.id" :value="f.id">{{ f.name }}</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section: Asset Intelligence -->
        <div class="rounded-[2.5rem] border border-slate-100 bg-white shadow-sm overflow-hidden transition-all hover:shadow-xl hover:shadow-primary/5">
            <div class="bg-slate-50/50 px-10 py-6 border-b border-slate-100">
                <div class="flex items-center gap-4">
                    <div class="h-10 w-10 rounded-2xl bg-orange-100/50 flex items-center justify-center text-orange-600">
                        <FileText class="h-5 w-5" />
                    </div>
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-tight text-slate-900">Asset Intelligence</h3>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">Core Metadata & Source Definition</p>
                    </div>
                </div>
            </div>

            <div class="p-10 space-y-10">
                <div class="grid gap-3">
                    <Label class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 ml-4 italic">Formal Asset Title</Label>
                    <Input v-model="form.title" placeholder="ENTER SYLLABIC ASSIGNMENT TITLE..." class="h-20 rounded-3xl border-2 border-slate-100 bg-slate-50/30 font-black text-lg px-10 focus:ring-12 focus:ring-primary/5 focus:bg-white transition-all outline-none uppercase shadow-inner" required />
                </div>

                <div class="grid lg:grid-cols-2 gap-10">
                    <!-- File Provision -->
                    <div class="space-y-4">
                        <Label class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 ml-4">Binary Provision (Multiple Allowed)</Label>
                        <div class="relative group/upload">
                            <input type="file" @change="handleFileChange" multiple class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20" />
                            <div :class="['bg-slate-50/50 border-3 border-dashed rounded-[2.5rem] p-8 text-center transition-all min-h-[220px] flex flex-col items-center justify-center', 
                                form.files.length > 0 ? 'border-emerald-200 bg-emerald-50/10' : 'border-slate-100 group-hover/upload:border-primary/30 group-hover/upload:bg-primary/[0.02]'
                            ]">
                                <div v-if="form.files.length > 0" class="w-full space-y-4 px-6 overflow-y-auto max-h-[300px]">
                                     <div v-for="(f, i) in form.files" :key="i" class="flex items-center gap-4 bg-white p-4 rounded-2xl border border-emerald-100 shadow-sm animate-in slide-in-from-left-4 duration-300" :style="{ transitionDelay: `${i * 100}ms` }">
                                         <div class="h-10 w-10 rounded-xl bg-emerald-500 overflow-hidden flex items-center justify-center text-white shrink-0">
                                            <CheckCircle2 class="h-5 w-5" />
                                         </div>
                                         <div class="text-left overflow-hidden">
                                            <span class="text-[10px] font-black uppercase tracking-tight text-slate-700 block truncate">{{ f.name }}</span>
                                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ (f.size / 1024 / 1024).toFixed(2) }} MB Payload</span>
                                         </div>
                                     </div>
                                </div>
                                <div v-else class="space-y-4">
                                    <div class="h-20 w-20 rounded-[2rem] bg-white shadow-sm border border-slate-100 flex items-center justify-center mx-auto text-slate-200 group-hover/upload:scale-110 group-hover/upload:text-primary transition-all duration-500">
                                        <UploadCloud class="h-10 w-10" />
                                    </div>
                                    <div class="space-y-1">
                                        <p class="text-[10px] text-slate-400 font-black uppercase tracking-[0.2em]">Syllabic Payload Drop</p>
                                        <p class="text-[9px] text-slate-300 font-bold uppercase italic">Select one or more assets to synchronize</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Link Provision -->
                    <div class="space-y-10">
                        <div class="space-y-4">
                            <Label class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 ml-4">External Protocol (URL)</Label>
                            <div class="relative flex items-center">
                                <div class="absolute left-6 h-12 w-12 rounded-2xl bg-white border border-slate-100 flex items-center justify-center text-slate-300 shadow-sm">
                                    <Globe class="h-5 w-5" />
                                </div>
                                <Input v-model="form.url" placeholder="HTTPS://KNOWLEDGE-VAULT.COM/ASSET..." class="h-20 pl-24 rounded-3xl border-2 border-slate-100 bg-slate-50/50 text-xs font-black shadow-inner uppercase tracking-wider focus:bg-white transition-all outline-none" />
                            </div>
                        </div>

                        <div class="space-y-4 border-t border-slate-50 pt-10">
                            <Label class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 ml-4">Discovery Logic (Type)</Label>
                            <div class="grid grid-cols-2 gap-3">
                                <button v-for="type in ['video', 'pdf', 'image', 'audio']" :key="type" type="button" @click="form.resource_type = type" 
                                    :class="['h-14 rounded-2xl border-2 text-[10px] font-black uppercase tracking-[0.2em] transition-all flex items-center justify-center gap-2 shadow-sm', 
                                    form.resource_type === type ? 'text-white' : 'bg-slate-50 border-slate-100 text-slate-400 hover:bg-white']"
                                    :style="form.resource_type === type ? { backgroundColor: themeColor, borderColor: themeColor, boxShadow: `0 10px 15px -3px ${themeColor}33` } : {}"
                                >
                                    <component :is="type === 'video' ? Play : type === 'pdf' ? FileText : type === 'image' ? ImageIcon : Music" class="h-4 w-4" />
                                    {{ type }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section: Instructional Meta -->
        <div class="rounded-[2.5rem] border border-slate-100 bg-white shadow-sm overflow-hidden transition-all hover:shadow-xl hover:shadow-primary/5">
             <div class="bg-slate-50/50 px-10 py-6 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="h-10 w-10 rounded-2xl bg-violet-100/50 flex items-center justify-center text-violet-600">
                        <Clock class="h-5 w-5" />
                    </div>
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-tight text-slate-900">Instructional Meta</h3>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">Authoritative Source & Bibliographic Data</p>
                    </div>
                </div>
            </div>

            <div class="p-10 space-y-10">
                <div class="grid gap-3">
                    <Label class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 ml-4 italic">Pedagogical Summary</Label>
                    <Textarea v-model="form.description" placeholder="DESCRIBE THE CORE ACADEMIC OBJECTIVE OF THIS PROVISION..." class="rounded-[2.5rem] border-2 border-slate-100 bg-slate-50/30 p-10 italic text-sm font-black uppercase tracking-tight min-h-[160px] focus:bg-white transition-all shadow-inner leading-relaxed" />
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-8">
                    <div class="space-y-3">
                        <Label class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 ml-4">Primary Author</Label>
                        <Input v-model="form.author" class="h-14 rounded-2xl border-2 border-slate-100 font-black text-[11px] uppercase tracking-wider bg-slate-50/30 focus:bg-white transition-all shadow-inner px-6" />
                    </div>
                    <div class="space-y-3">
                        <Label class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 ml-4">Prov. Publisher</Label>
                        <Input v-model="form.publisher" class="h-14 rounded-2xl border-2 border-slate-100 font-black text-[11px] uppercase tracking-wider bg-slate-50/30 focus:bg-white transition-all shadow-inner px-6" />
                    </div>
                    <div class="space-y-3">
                        <Label class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 ml-4">Universal ISBN</Label>
                        <Input v-model="form.isbn" class="h-14 rounded-2xl border-2 border-slate-100 font-black text-[11px] uppercase tracking-wider bg-slate-50/30 focus:bg-white transition-all shadow-inner px-6" />
                    </div>
                    <div class="space-y-3">
                        <Label class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 ml-4">Release Year</Label>
                        <Input v-model="form.year_published" class="h-14 rounded-2xl border-2 border-slate-100 font-black text-[11px] uppercase tracking-wider bg-slate-50/30 focus:bg-white transition-all shadow-inner px-6" />
                    </div>
                </div>

                <div @click="form.is_recommended = !form.is_recommended" class="flex items-center gap-10 p-10 rounded-[2.5rem] bg-amber-50/20 border-2 border-dashed border-amber-100 cursor-pointer group transition-all hover:bg-amber-50 relative overflow-hidden">
                    <div :class="['h-20 w-20 rounded-3xl flex items-center justify-center transition-all duration-700 relative z-10 shadow-2xl', form.is_recommended ? 'text-white rotate-6 scale-110' : 'bg-white text-slate-200 border-2 border-slate-100 group-hover:border-amber-200']"
                        :style="form.is_recommended ? { backgroundColor: '#f59e0b' } : {}"
                    >
                        <Sparkles class="h-10 w-10" />
                    </div>
                    <div class="flex-1 relative z-10">
                        <Label class="text-sm font-black uppercase tracking-[0.2em] text-slate-900 cursor-pointer m-0 italic">Institutional Recommendation</Label>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-[0.2em] mt-1 italic">Flag this asset as a High-Depth Reference in the curriculum matrix.</p>
                    </div>
                    <div class="absolute right-0 bottom-0 translate-y-1/2 translate-x-1/2 h-40 w-40 bg-amber-200/10 rounded-full blur-3xl"></div>
                </div>
            </div>
        </div>

        <!-- Submission Gates -->
        <div class="pt-10 flex flex-col sm:flex-row items-center justify-end gap-6 pb-20">
            <Link href="/curriculum/resources" class="h-16 px-10 rounded-2xl font-black text-[10px] uppercase tracking-[0.3em] text-slate-400 hover:text-slate-900 transition-colors">Discard Blueprint</Link>
            <Button type="submit" :disabled="form.processing" 
                class="h-16 px-16 rounded-[2rem] text-white font-black text-[11px] uppercase tracking-[0.4em] hover:opacity-90 transform transition-all active:scale-95 border-0 flex items-center gap-4"
                :style="{ backgroundColor: themeColor, boxShadow: `0 20px 25px -5px ${themeColor}4d` }"
            >
                <Sparkles class="h-5 w-5" />
                {{ form.processing ? 'SYNCHRONIZING SYLLABUS...' : (resource ? 'Update Academic Registry' : 'Publish Asset to Gateway') }}
            </Button>
        </div>
    </form>
</template>

<script setup lang="ts">
import { useForm, Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';
import { 
    Plus, CheckCircle2, 
    GraduationCap, BookOpen, Clock, 
    FileText, Sparkles, UploadCloud, 
    Globe, Play, Image as ImageIcon, Music, Trash2
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

const props = defineProps<{
    resource?: any;
    subjects: any[];
    grades: any[];
    folders?: any[];
    selectedFolderId?: number | string | null;
}>();

const page = usePage();
const themeColor = computed(() => (page.props.auth as any).school?.theme_color || '#1e40af');
const themeSecondary = computed(() => themeColor.value + '20'); 
const themeBorder = computed(() => themeColor.value + '30');

const emit = defineEmits(['success']);

const form = useForm({
    title: props.resource?.title || '',
    resource_type: props.resource?.resource_type || '',
    url: props.resource?.url || '',
    description: props.resource?.description || '',
    subject_id: props.resource?.subject_id || '',
    grade_level_id: props.resource?.grade_level_id || '',
    folder_id: props.resource?.folder_id || props.selectedFolderId || '',
    is_recommended: !!props.resource?.is_recommended,
    author: props.resource?.author || '',
    publisher: props.resource?.publisher || '',
    isbn: props.resource?.isbn || '',
    year_published: props.resource?.year_published || '',
    files: [] as File[],
});

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        form.files = Array.from(target.files);
        
        // Auto-detect type from first file if not set
        if (!form.resource_type) {
            const ext = target.files[0].name.split('.').pop()?.toLowerCase();
            if (ext === 'pdf') form.resource_type = 'pdf';
            else if (['mp4', 'mov', 'webm'].includes(ext!)) form.resource_type = 'video';
            else if (['jpg', 'jpeg', 'png', 'webp'].includes(ext!)) form.resource_type = 'image';
            else if (['mp3', 'wav'].includes(ext!)) form.resource_type = 'audio';
            else form.resource_type = 'document';
        }
    }
};

const submit = () => {
    if (props.resource) {
        form.transform((data) => ({
            ...data,
            _method: 'put',
        })).post(route('curriculum.resources.update', props.resource.id), {
            onSuccess: () => emit('success'),
        });
    } else {
        form.post(route('curriculum.resources.store'), {
            onSuccess: () => emit('success'),
        });
    }
};

const route = (window as any).route;
</script>

<style scoped>
select {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23cbd5e1' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 1.5rem center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
}
</style>

<style scoped>
select {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 1.25rem center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
}
</style>
