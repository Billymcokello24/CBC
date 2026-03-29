<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    ChevronLeft, 
    Plus, 
    FileText, 
    Image as ImageIcon, 
    Video, 
    Link as LinkIcon,
    Trash2,
    Calendar,
    CalendarDays,
    Info,
    BookOpen,
    Download
} from 'lucide-vue-next';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    student: any;
    portfolio: any;
    subjects: Array<any>;
    competencies: Array<any>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Assessments', href: '/assessments' },
    { title: 'Portfolio Management', href: '/assessments/portfolio' },
    { title: props.student.first_name + ' ' + props.student.last_name, href: '#' },
];

const showAddModal = ref(false);
const form = useForm({
    title: '',
    description: '',
    item_type: 'image',
    item_date: new Date().toISOString().split('T')[0],
    subject_id: '',
    indicator_ids: [] as number[],
    file: null as File | null,
    url: '',
});

const filteredCompetencies = ref<any[]>([]);

const onSubjectChange = (val: any) => {
    form.indicator_ids = [];
    if (val) {
        filteredCompetencies.value = props.competencies.filter(c => c.subject_id == parseInt(val as string));
    }
};

const toggleIndicator = (id: number) => {
    const index = form.indicator_ids.indexOf(id);
    if (index > -1) {
        form.indicator_ids.splice(index, 1);
    } else {
        form.indicator_ids.push(id);
    }
};

const submit = () => {
    form.post('/assessments/portfolio/' + props.portfolio.id + '/item', {
        onSuccess: () => {
            showAddModal.value = false;
            form.reset();
        },
    });
};

const deleteItem = (id: number) => {
    if (confirm('Are you sure you want to remove this item?')) {
        form.delete('/assessments/portfolio/item/' + id);
    }
};

const getItemIcon = (type: string) => {
    switch (type) {
        case 'image': return ImageIcon;
        case 'video': return Video;
        case 'link': return LinkIcon;
        case 'text': return FileText;
        default: return FileText;
    }
};

const getBadgeColor = (type: string) => {
    switch (type) {
        case 'image': return 'bg-blue-100 text-blue-700';
        case 'video': return 'bg-purple-100 text-purple-700';
        case 'link': return 'bg-orange-100 text-orange-700';
        default: return 'bg-slate-100 text-slate-700';
    }
};
</script>

<template>
    <Head :title="student.first_name + ' - Portfolio'" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" as-child class="h-10 w-10 p-0 rounded-xl">
                        <Link href="/assessments/portfolio">
                            <ChevronLeft class="h-6 w-6" />
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-3xl font-black tracking-tight text-slate-900 italic uppercase">Learner Evidence Portfolio</h1>
                        <p class="text-slate-500 font-bold mt-1">Collecting evidence of learning for {{ student.first_name }} {{ student.last_name }}.</p>
                    </div>
                </div>
                <Dialog v-model:open="showAddModal">
                    <DialogTrigger as-child>
                        <Button class="bg-indigo-600 hover:bg-indigo-700 h-11 rounded-2xl font-black px-6 gap-2">
                            <Plus class="h-5 w-5" /> Add Evidence
                        </Button>
                    </DialogTrigger>
                    <DialogContent class="sm:max-w-[600px] rounded-[2.5rem] p-8 border-0 shadow-2xl max-h-[90vh] overflow-y-auto">
                        <DialogHeader>
                            <DialogTitle class="text-2xl font-black italic uppercase tracking-tight">Add New Evidence</DialogTitle>
                            <DialogDescription class="font-bold text-slate-500">Capture a new learning outcome or achievement.</DialogDescription>
                        </DialogHeader>
                        <form @submit.prevent="submit" class="grid gap-6 py-4">
                            <div class="grid gap-2">
                                <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 pl-1">Evidence Title</label>
                                <Input v-model="form.title" placeholder="e.g., Solar System Model Project" class="h-11 rounded-2xl border-slate-200" required />
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="grid gap-2">
                                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 pl-1">Type</label>
                                    <Select v-model="form.item_type">
                                        <SelectTrigger class="h-11 rounded-2xl border-slate-200">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="image">Image / Photo</SelectItem>
                                            <SelectItem value="video">Video Recording</SelectItem>
                                            <SelectItem value="document">Document / PDF</SelectItem>
                                            <SelectItem value="text">Written Reflection</SelectItem>
                                            <SelectItem value="link">Web Link</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div class="grid gap-2">
                                    <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 pl-1">Date</label>
                                    <Input v-model="form.item_date" type="date" class="h-11 rounded-2xl border-slate-200" required />
                                </div>
                            </div>
                            <div class="grid gap-2">
                                <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 pl-1">Learning Area (Subject)</label>
                                <Select v-model="form.subject_id" @update:model-value="onSubjectChange">
                                    <SelectTrigger class="h-11 rounded-2xl border-slate-200">
                                        <SelectValue placeholder="Select Subject" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="subject in subjects" :key="subject.id" :value="subject.id.toString()">
                                            {{ subject.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <!-- Indicator Selection -->
                            <div v-if="form.subject_id && filteredCompetencies.length > 0" class="grid gap-3 p-4 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                                <label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Target Competencies / Indicators</label>
                                <div class="space-y-4 max-h-[200px] overflow-y-auto pr-2">
                                    <div v-for="comp in filteredCompetencies" :key="comp.id" class="space-y-2">
                                        <p class="text-[10px] font-black text-indigo-600 uppercase">{{ comp.title }}</p>
                                        <div class="flex flex-wrap gap-2">
                                            <button 
                                                v-for="indicator in comp.indicators" 
                                                :key="indicator.id"
                                                type="button"
                                                @click="toggleIndicator(indicator.id)"
                                                :class="[
                                                    'px-3 py-1.5 rounded-xl text-[10px] font-bold transition-all border text-left flex-1 min-w-[140px]',
                                                    form.indicator_ids.includes(indicator.id)
                                                        ? 'bg-indigo-600 border-indigo-600 text-white shadow-lg'
                                                        : 'bg-white border-slate-200 text-slate-600 hover:border-indigo-300'
                                                ]"
                                            >
                                                {{ indicator.title }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="grid gap-2" v-if="['image', 'video', 'document'].includes(form.item_type)">
                                <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 pl-1">File Upload</label>
                                <Input type="file" @input="form.file = ($event.target as HTMLInputElement).files?.[0] || null" class="h-11 rounded-2xl border-slate-200 pt-2" />
                            </div>
                             <div class="grid gap-2" v-if="form.item_type === 'link'">
                                <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 pl-1">URL</label>
                                <Input v-model="form.url" placeholder="https://" class="h-11 rounded-2xl border-slate-200" />
                            </div>
                            <div class="grid gap-2">
                                <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 pl-1">Observations / Description</label>
                                <Textarea v-model="form.description" class="rounded-2xl border-slate-200 min-h-[100px]" placeholder="What competency was demonstrated?" />
                            </div>
                            <DialogFooter>
                                <Button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 h-12 rounded-2xl font-black uppercase tracking-widest" :disabled="form.processing">
                                    Save to Portfolio
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>

            <!-- Evidence Grid -->
            <div v-if="portfolio.items.length === 0" class="flex flex-col items-center justify-center py-24 bg-white rounded-[3rem] border border-dashed border-slate-200">
                <div class="h-20 w-20 rounded-3xl bg-slate-50 flex items-center justify-center mb-4 text-slate-300">
                    <FileText class="h-10 w-10" />
                </div>
                <h3 class="text-xl font-black text-slate-900 uppercase italic">No Evidence Found</h3>
                <p class="text-slate-500 font-bold mt-1">Start adding learner achievements to build their portfolio.</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <Card v-for="item in portfolio.items" :key="item.id" class="group rounded-[2.5rem] border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all overflow-hidden bg-white flex flex-col">
                    <!-- Media Preview -->
                    <div class="h-48 bg-slate-100 relative overflow-hidden shrink-0">
                        <div v-if="item.item_type === 'image' && item.file_path" class="h-full w-full">
                            <img :src="'/storage/' + item.file_path" class="h-full w-full object-cover" />
                        </div>
                        <div v-else class="h-full w-full flex items-center justify-center text-slate-300">
                            <component :is="getItemIcon(item.item_type)" class="h-12 w-12" />
                        </div>
                        <Badge :class="getBadgeColor(item.item_type)" class="absolute top-4 left-4 border-0 font-black px-3 py-1 uppercase text-[10px] tracking-widest shadow-sm">
                            {{ item.item_type }}
                        </Badge>
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                             <Button v-if="item.file_path" variant="outline" size="sm" class="bg-white/20 backdrop-blur-md border-white/30 text-white hover:bg-white hover:text-indigo-600 rounded-full h-10 w-10 p-0" as-child>
                                <a :href="'/storage/' + item.file_path" target="_blank"><Download class="h-4 w-4" /></a>
                            </Button>
                            <Button @click="deleteItem(item.id)" variant="destructive" size="sm" class="bg-red-500/80 backdrop-blur-md border-0 h-10 w-10 p-0 rounded-full">
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                    <CardContent class="p-6 flex flex-col flex-1">
                        <div class="flex items-center gap-2 mb-3">
                            <BookOpen class="h-3.5 w-3.5 text-indigo-500" />
                            <span class="text-[10px] font-black uppercase text-indigo-600 tracking-widest">{{ item.subject?.name || 'General Achievement' }}</span>
                        </div>
                        <h4 class="text-lg font-black text-slate-900 tracking-tight leading-tight uppercase italic mb-2">{{ item.title }}</h4>
                        <p class="text-xs font-medium text-slate-500 line-clamp-2 mb-4">{{ item.description || 'No description provided.' }}</p>
                        
                        <!-- Linked Indicators -->
                        <div v-if="item.indicators?.length > 0" class="flex flex-wrap gap-1 mb-4">
                            <div v-for="ind in item.indicators" :key="ind.id" class="px-2 py-0.5 rounded-lg bg-indigo-50 text-indigo-600 text-[9px] font-bold border border-indigo-100 truncate max-w-[150px]" :title="ind.title">
                                {{ ind.title }}
                            </div>
                        </div>

                        <div class="mt-auto flex items-center justify-between pt-4 border-t border-slate-50">
                            <div class="flex items-center gap-1.5 text-slate-400 font-bold text-[10px] uppercase tracking-wider">
                                <CalendarDays class="h-3.5 w-3.5" />
                                {{ new Date(item.item_date).toLocaleDateString() }}
                            </div>
                             <div class="flex -space-x-2">
                                <div class="h-6 w-6 rounded-full border-2 border-white bg-slate-100 flex items-center justify-center overflow-hidden">
                                     <Info class="h-3 w-3 text-slate-300" />
                                </div>
                             </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
