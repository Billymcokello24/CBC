<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { 
    Folder, BookOpen, GraduationCap, ArrowLeft, 
    ChevronRight, FileText, Search, Filter, 
    Archive, Users, Calendar, Download, Eye,
    CheckCircle2, Clock
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { route } from 'ziggy-js';

const props = defineProps<{
    vault: Record<string, Record<string, any[]>>;
}>();

const breadcrumbs = [
    { title: 'Assignments', href: '/curriculum/assignments' },
    { title: 'Academic Vault', href: '#' },
];

const searchQuery = ref('');
const selectedFolder = ref<{subject: string, className: string} | null>(null);

const subjects = computed(() => Object.keys(props.vault));

const filteredVault = computed(() => {
    if (!searchQuery.value) return props.vault;
    
    const result: Record<string, any> = {};
    Object.entries(props.vault).forEach(([subject, classes]) => {
        if (subject.toLowerCase().includes(searchQuery.value.toLowerCase())) {
            result[subject] = classes;
            return;
        }
        
        const filteredClasses: Record<string, any> = {};
        Object.entries(classes).forEach(([className, submissions]) => {
            if (className.toLowerCase().includes(searchQuery.value.toLowerCase())) {
                filteredClasses[className] = submissions;
            }
        });
        
        if (Object.keys(filteredClasses).length > 0) {
            result[subject] = filteredClasses;
        }
    });
    return result;
});

const openFolder = (subject: string, className: string) => {
    selectedFolder.value = { subject, className };
};

const closeFolder = () => {
    selectedFolder.value = null;
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-KE', { 
        day: 'numeric', month: 'short', year: 'numeric' 
    });
};
</script>

<template>
    <Head title="Academic Vault - Graded Assignments" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="h-full flex flex-col bg-slate-50 min-h-screen">
            
            <!-- Hero Header -->
            <div class="bg-white border-b border-slate-200 px-8 py-10 shadow-sm">
                <div class="max-w-[1600px] mx-auto flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                             <div class="h-6 w-6 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                                <Archive class="h-3.5 w-3.5" />
                             </div>
                             <span class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-400">Digital Archive</span>
                        </div>
                        <h1 class="text-3xl font-black tracking-tight text-slate-900 uppercase">Academic <span class="text-blue-600">Vault</span></h1>
                        <p class="text-sm text-slate-500 font-medium mt-1">Institutional repository of all marked and assessed student materials.</p>
                    </div>

                    <div class="flex items-center gap-4 bg-slate-50 p-2 rounded-2xl border border-slate-100">
                        <div class="px-6 py-2 text-center border-r border-slate-200">
                            <p class="text-xl font-black text-slate-900">{{ subjects.length }}</p>
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Subjects</p>
                        </div>
                        <div class="px-6 py-2 text-center">
                            <p class="text-xl font-black text-blue-600">
                                {{ Object.values(vault).reduce((acc, cls) => acc + Object.keys(cls).length, 0) }}
                            </p>
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Classes</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex-1 max-w-[1600px] w-full mx-auto p-8">
                
                <!-- Drill-down View -->
                <div v-if="selectedFolder" class="space-y-6 animate-in slide-in-from-right-4 duration-500">
                    <div class="flex items-center justify-between bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                         <div class="flex items-center gap-4">
                            <Button @click="closeFolder" variant="ghost" size="icon" class="h-10 w-10 rounded-xl hover:bg-slate-100">
                                <ArrowLeft class="h-5 w-5" />
                            </Button>
                            <div>
                                <h2 class="text-lg font-black text-slate-900 uppercase tracking-tight">{{ selectedFolder.className }}</h2>
                                <p class="text-xs text-slate-500 font-medium uppercase tracking-widest">{{ selectedFolder.subject }} Portfolio</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                             <Badge class="bg-blue-50 text-blue-600 rounded-lg px-3 py-1 font-black text-[10px] uppercase">
                                {{ vault[selectedFolder.subject][selectedFolder.className].length }} Submissions
                             </Badge>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/50 border-b border-slate-200">
                                    <th class="pl-8 py-4 text-[10px] font-bold uppercase tracking-widest text-slate-400">Student Name</th>
                                    <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-widest text-slate-400">Submission Details</th>
                                    <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-widest text-slate-400">Marked Date</th>
                                    <th class="px-6 py-4 text-[10px] font-bold uppercase tracking-widest text-slate-400 text-right">Repository Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="submission in vault[selectedFolder.subject][selectedFolder.className]" :key="submission.id" class="group hover:bg-slate-50/50 transition-colors">
                                    <td class="pl-8 py-5">
                                        <div class="flex items-center gap-4">
                                            <div class="h-10 w-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-black text-xs uppercase shadow-sm">
                                                {{ submission.student?.first_name?.charAt(0) }}{{ submission.student?.last_name?.charAt(0) }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-slate-900 line-clamp-1">{{ submission.student?.first_name }} {{ submission.student?.last_name }}</p>
                                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest italic">{{ submission.assignment?.title }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-2">
                                            <div class="h-6 w-6 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                                <CheckCircle2 class="h-3 w-3" />
                                            </div>
                                            <span class="text-xs font-bold text-slate-600">Assessed & Graded</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-xs font-semibold text-slate-500">
                                        {{ formatDate(submission.graded_at) }}
                                    </td>
                                    <td class="px-8 py-5 text-right">
                                        <div class="flex items-center justify-end gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <Link :href="route('curriculum.assignments.submissions.download-compiled', { submission: String(submission.id) })" class="h-9 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-black text-[9px] uppercase tracking-widest flex items-center gap-2 transition-all shadow-lg shadow-blue-600/20">
                                                <Download class="h-3 w-3" /> Download Marked Copy
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Initial Grid View -->
                <div v-else class="space-y-12 animate-in fade-in duration-700">
                    <!-- Browser Controls -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-white border border-slate-200 rounded-2xl p-4 shadow-sm items-center">
                        <div class="md:col-span-3 relative group">
                            <Search class="absolute left-6 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-blue-600 transition-colors" />
                            <input 
                                v-model="searchQuery"
                                type="text" 
                                placeholder="Search folders..." 
                                class="w-full bg-slate-100 border-none rounded-xl py-3 pl-14 pr-6 text-sm font-medium focus:ring-2 focus:ring-blue-100 transition-all outline-none"
                            />
                        </div>
                        <Button variant="outline" class="h-full rounded-xl gap-3 text-[10px] font-black uppercase tracking-widest border-2">
                            <Filter class="h-4 w-4" /> Filter Options
                        </Button>
                    </div>

                    <div v-if="Object.keys(filteredVault).length > 0" class="space-y-12">
                        <div v-for="(classes, subject) in filteredVault" :key="subject" class="space-y-6">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-xl bg-slate-900 flex items-center justify-center text-white shadow-lg">
                                    <BookOpen class="h-5 w-5" />
                                </div>
                                <h2 class="text-xl font-black tracking-tight text-slate-900 uppercase tracking-widest">{{ subject }}</h2>
                                <div class="flex-1 h-px bg-slate-200 ml-4"></div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                                <div v-for="(submissions, className) in classes" :key="className" 
                                    @click="openFolder(subject, className)"
                                    class="group cursor-pointer bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-xl hover:border-blue-200 transition-all p-6 relative"
                                >
                                    <div class="flex flex-col h-full">
                                        <div class="mb-4">
                                            <div class="h-14 w-14 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mb-5 group-hover:scale-110 transition-transform duration-500">
                                                <Folder class="h-7 w-7" />
                                            </div>
                                            <h3 class="text-lg font-black text-slate-900 mb-0.5 line-clamp-1">{{ className }}</h3>
                                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-[0.2em]">{{ submissions.length }} Graded Files</p>
                                        </div>

                                        <div class="mt-auto flex items-center justify-between pt-5 border-t border-slate-50">
                                            <span class="text-[9px] font-black text-blue-600 uppercase tracking-widest">Open Portfolio</span>
                                            <ChevronRight class="h-4 w-4 text-slate-300 group-hover:text-blue-600" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="flex flex-col items-center justify-center py-24 text-center bg-white rounded-3xl border-2 border-dashed border-slate-200">
                        <div class="h-20 w-20 rounded-2xl bg-slate-100 text-slate-300 flex items-center justify-center mb-6">
                            <Archive class="h-10 w-10" />
                        </div>
                        <h3 class="text-xl font-black text-slate-900 mb-2 uppercase tracking-tight">Archive Empty</h3>
                        <p class="text-sm text-slate-400 max-w-sm mx-auto font-medium">
                            No assignments have been marked for archiving yet. The vault auto-populates upon finalized assessment.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
