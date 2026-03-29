<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { 
    ClipboardList, Save, ArrowLeft, User, CheckCircle2, 
    AlertCircle, Search, Keyboard, Zap, Info, MoreHorizontal,
    ChevronRight, ChevronDown, Check, X, Star, BarChart3,
    BookOpen, Layers
} from 'lucide-vue-next';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { route } from 'ziggy-js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { Separator } from '@/components/ui/separator';
import axios from 'axios';

const props = defineProps<{
    assessment: any;
    students: Array<any>;
    existingRatings: Record<number, any[]>;
    ratingScales: Array<any>;
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Assessments', href: '/assessments' },
    { title: 'Grading Sheet', href: '#' },
];

const searchQuery = ref('');
const activeCell = ref({ studentIndex: 0, itemIndex: 0 });
const saving = ref(false);
const lastSavedAt = ref<Date | null>(null);

// Group items by competency (Strand)
const groupedItems = computed(() => {
    const groups: Record<string, any[]> = {};
    props.assessment.items.forEach((item: any) => {
        const strandName = item.indicator?.competency?.name || 'General';
        if (!groups[strandName]) groups[strandName] = [];
        groups[strandName].push(item);
    });
    return groups;
});

// Flatten items for linear index mapping
const flatItems = computed(() => {
    return Object.values(groupedItems.value).flat();
});

// Initialize results matrix
const results = ref<any[]>(props.students.map(student => {
    const studentRatings = props.existingRatings[student.id] || [];
    return {
        id: student.id,
        name: `${student.first_name} ${student.last_name}`,
        admission_number: student.admission_number,
        photo: student.photo,
        ratings: flatItems.value.map((item: any) => {
            const existing = studentRatings.find((r: any) => r.assessment_item_id === item.id);
            return {
                assessment_item_id: item.id,
                rating: existing ? existing.rating : null,
                feedback: existing ? existing.feedback : ''
            };
        })
    };
}));

const filteredResults = computed(() => {
    if (!searchQuery.value) return results.value;
    const query = searchQuery.value.toLowerCase();
    return results.value.filter((r: any) => 
        r.name.toLowerCase().includes(query) || 
        r.admission_number.toLowerCase().includes(query)
    );
});

const getRatingStyle = (rating: number | null) => {
    if (!rating) return {};
    const scale = props.ratingScales.find(s => s.id === rating);
    return scale ? { backgroundColor: scale.color, borderColor: scale.color, color: 'white' } : {};
};

const updateRating = async (studentIndex: number, itemIndex: number, value: number | null) => {
    const student = filteredResults.value[studentIndex];
    const ratingEntry = student.ratings[itemIndex];
    
    if (ratingEntry.rating === value) {
        ratingEntry.rating = null;
    } else {
        ratingEntry.rating = value;
    }

    try {
        await axios.post(route('assessments.grading.quick-save'), {
            student_id: student.id,
            assessment_item_id: ratingEntry.assessment_item_id,
            rating: ratingEntry.rating
        });
        lastSavedAt.value = new Date();
    } catch (e) {
        console.error('Failed to auto-save', e);
    }
};

const handleKeyDown = (e: KeyboardEvent) => {
    const { studentIndex, itemIndex } = activeCell.value;
    
    if (e.key === 'ArrowUp' && studentIndex > 0) {
        activeCell.value.studentIndex--;
        e.preventDefault();
    } else if (e.key === 'ArrowDown' && studentIndex < filteredResults.value.length - 1) {
        activeCell.value.studentIndex++;
        e.preventDefault();
    } else if (e.key === 'ArrowLeft' && itemIndex > 0) {
        activeCell.value.itemIndex--;
        e.preventDefault();
    } else if (e.key === 'ArrowRight' && itemIndex < flatItems.value.length - 1) {
        activeCell.value.itemIndex++;
        e.preventDefault();
    } else if (['1', '2', '3', '4'].includes(e.key)) {
        updateRating(studentIndex, itemIndex, parseInt(e.key));
    } else if (e.key === 'Backspace' || e.key === 'Delete') {
        updateRating(studentIndex, itemIndex, null);
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown);
});

const submitAll = () => {
    saving.value = true;
    const flatRatings = filteredResults.value.flatMap(student => 
        student.ratings.map((r: any) => ({
            student_id: student.id,
            assessment_item_id: r.assessment_item_id,
            rating: r.rating,
            feedback: r.feedback
        }))
    );

    router.post(route('assessments.grading.store', { assessment: props.assessment.id }), {
        ratings: flatRatings
    }, {
        onFinish: () => {
            saving.value = false;
        }
    });
};
</script>

<template>
    <Head :title="`Grading Sheet - ${assessment.title}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 font-sans mt-2 max-w-[1600px] mx-auto overflow-hidden bg-slate-50/50">
            <!-- Professional Header -->
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100">
                <div class="flex items-center gap-6">
                    <Button variant="ghost" size="icon" as-child class="rounded-2xl h-12 w-12 border border-slate-100 shadow-sm bg-white hover:bg-slate-50">
                        <Link href="/assessments"><ArrowLeft class="h-6 w-6 text-slate-500" /></Link>
                    </Button>
                    <div class="h-12 w-px bg-slate-100 hidden md:block"></div>
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <h1 class="text-3xl font-black text-slate-900 tracking-tighter uppercase italic">{{ assessment.title }}</h1>
                            <Badge class="bg-slate-900 text-white font-black text-[10px] uppercase tracking-widest px-3 py-1 rounded-lg">OFFICIAL EVALUATION</Badge>
                        </div>
                        <div class="flex items-center gap-4 text-xs font-bold text-slate-400">
                            <span class="flex items-center gap-2 px-2 py-0.5 bg-indigo-50 text-indigo-600 rounded-md border border-indigo-100">
                                <BookOpen class="h-3 w-3" /> {{ assessment.subject?.name }}
                            </span>
                            <span class="flex items-center gap-2 px-2 py-0.5 bg-purple-50 text-purple-600 rounded-md border border-purple-100">
                                <Layers class="h-3 w-3" /> {{ assessment.class?.name }}
                            </span>
                            <span class="flex items-center gap-2">
                                <Calendar class="h-3 w-3" /> {{ assessment.academic_term?.name }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="text-right hidden sm:block border-r border-slate-100 pr-6 mr-2">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Assessment Matrix State</p>
                        <p class="text-[11px] font-black text-emerald-600 flex items-center justify-end gap-2">
                            <Zap class="h-4 w-4 fill-emerald-600" />
                            {{ lastSavedAt ? `LAST SAVED: ${lastSavedAt.toLocaleTimeString()}` : 'LIVE SYNC ACTIVE' }}
                        </p>
                    </div>
                    <Button @click="submitAll" :disabled="saving" class="h-14 px-10 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white shadow-xl shadow-slate-200 font-black text-xs uppercase tracking-widest transition-all hover:scale-[1.02] active:scale-95">
                        <Save v-if="!saving" class="mr-3 h-5 w-5" />
                        <div v-else class="mr-3 h-5 w-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                        Finalize Assessment
                    </Button>
                </div>
            </div>

            <!-- Scoring Guide & Filters -->
            <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
                <div class="xl:col-span-3 relative group">
                    <Search class="absolute left-5 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400 group-focus-within:text-slate-900 transition-colors" />
                    <Input v-model="searchQuery" placeholder="Search learner list..." class="h-14 pl-14 rounded-2xl border-white bg-white shadow-sm focus:ring-slate-900/10 placeholder:text-slate-300 font-bold" />
                </div>
                
                <div class="xl:col-span-9 flex items-center gap-4 overflow-x-auto pb-4 scrollbar-none scroll-smooth">
                    <div v-for="scale in ratingScales" :key="scale.id" class="flex items-center gap-4 px-5 py-3 rounded-2xl bg-white border border-slate-100 shadow-sm shrink-0 border-b-4" :style="{ borderBottomColor: scale.color }">
                         <div class="h-8 w-8 rounded-lg flex items-center justify-center font-black text-sm text-white shadow-lg" :style="{ backgroundColor: scale.color }">
                             {{ scale.id }}
                         </div>
                         <div>
                             <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] leading-none mb-1">{{ scale.code }}</p>
                             <p class="text-xs font-black text-slate-800 whitespace-nowrap">{{ scale.name }}</p>
                         </div>
                    </div>
                </div>
            </div>

            <!-- Master Grading Matrix -->
            <Card class="flex-1 overflow-hidden border-none shadow-2xl rounded-[2.5rem] flex flex-col bg-white">
                <div class="flex-1 overflow-auto relative">
                    <table class="w-full border-collapse">
                        <thead class="sticky top-0 z-30 bg-white">
                            <!-- Strand Grouping Row -->
                            <tr class="bg-slate-900 text-white uppercase text-[10px] font-black tracking-[0.2em]">
                                <th class="sticky left-0 z-40 bg-slate-900 p-6 text-left w-[320px] min-w-[320px]">
                                    LEARNER IDENTIFICATION
                                </th>
                                <th v-for="(items, strand) in groupedItems" :key="strand" :colspan="items.length" class="border-l border-white/10 p-4 text-center">
                                    <span class="flex items-center justify-center gap-2">
                                        <Layers class="h-4 w-4" />
                                        STRAND: {{ strand }}
                                    </span>
                                </th>
                            </tr>
                            <!-- Indicators Row -->
                            <tr class="bg-white shadow-sm shadow-slate-100">
                                <th class="sticky left-0 z-40 bg-white p-6 text-left border-r border-b border-slate-100">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-black text-slate-800">Full Name</span>
                                        <Badge variant="outline" class="text-[9px] font-black">ADMISSION</Badge>
                                    </div>
                                </th>
                                <th v-for="(item, idx) in flatItems" :key="item.id" 
                                    class="p-4 text-left border-b border-l border-slate-100 min-w-[220px] transition-colors"
                                    :class="{ 'bg-slate-50 ring-2 ring-slate-900/5 ring-inset': activeCell.itemIndex === idx }"
                                >
                                    <div class="flex items-start gap-3">
                                        <div class="h-6 w-6 rounded-md bg-slate-900 text-white flex items-center justify-center text-[10px] font-black shrink-0">
                                            {{ Number(idx) + 1 }}
                                        </div>
                                        <TooltipProvider>
                                            <Tooltip>
                                                <TooltipTrigger class="text-left">
                                                    <p class="text-[11px] font-extrabold text-slate-700 leading-tight line-clamp-2 uppercase italic">{{ item.indicator?.indicator || 'N/A' }}</p>
                                                </TooltipTrigger>
                                                <TooltipContent class="max-w-[320px] p-5 bg-slate-900 text-white rounded-[1.5rem] border-none shadow-2xl">
                                                    <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-2">Detailed Indicator:</p>
                                                    <p class="text-xs font-bold leading-relaxed italic">"{{ item.indicator?.indicator }}"</p>
                                                </TooltipContent>
                                            </Tooltip>
                                        </TooltipProvider>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(student, sIdx) in filteredResults" :key="student.id" class="group transition-colors hover:bg-slate-50/50 even:bg-slate-50/20">
                                <!-- Student Name Column -->
                                <td class="sticky left-0 z-20 bg-white group-hover:bg-slate-50 p-6 border-r border-b border-slate-100 shadow-[4px_0_8px_-4px_rgba(0,0,0,0.05)]">
                                    <div class="flex items-center gap-4">
                                        <div class="relative">
                                            <Avatar class="h-12 w-12 border-2 border-white shadow-lg ring-4 ring-slate-100/50 group-hover:scale-110 transition-all duration-300">
                                                <AvatarImage :src="student.photo" />
                                                <AvatarFallback class="bg-indigo-600 text-white font-black text-sm uppercase">
                                                    {{ String(student.name).split(' ').map((n: string) => n[0]).join('') }}
                                                </AvatarFallback>
                                            </Avatar>
                                            <div v-if="student.ratings.every((r: any) => r.rating !== null)" class="absolute -right-2 -bottom-2 h-6 w-6 bg-emerald-500 rounded-full border-2 border-white flex items-center justify-center shadow-lg">
                                                <Check class="h-3.5 w-3.5 text-white stroke-[4]" />
                                            </div>
                                        </div>
                                        <div class="overflow-hidden">
                                            <p class="text-sm font-black text-slate-900 truncate tracking-tight mb-1 leading-none uppercase">{{ student.name }}</p>
                                            <p class="text-[10px] font-black text-slate-400 tracking-[0.2em] leading-none">{{ student.admission_number }}</p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Ratings Matrix Cells -->
                                <td v-for="(rating, iIdx) in student.ratings" :key="iIdx" 
                                    class="p-4 border-b border-l border-slate-100 transition-all text-center relative"
                                    :class="{ 'bg-white ring-4 ring-slate-900 z-10 scale-[1.05] shadow-2xl rounded-xl': activeCell.studentIndex === Number(sIdx) && activeCell.itemIndex === Number(iIdx) }"
                                    @click="activeCell = { studentIndex: Number(sIdx), itemIndex: Number(iIdx) }"
                                >
                                    <div class="flex items-center justify-center gap-2 h-12 w-full group/cell transition-all">
                                        <!-- Rating Buttons -->
                                        <template v-if="activeCell.studentIndex === sIdx && activeCell.itemIndex === iIdx">
                                            <button 
                                                v-for="val in [1,2,3,4]" 
                                                :key="val"
                                                @click.stop="updateRating(sIdx, iIdx, val)"
                                                class="h-10 w-10 rounded-xl flex items-center justify-center font-black text-sm transition-all hover:scale-110 active:scale-90 shadow-md border-2"
                                                :style="getRatingStyle(val === rating.rating ? val : null)"
                                                :class="[
                                                    val === rating.rating ? 'border-transparent' : 'bg-white text-slate-200 border-slate-50 hover:border-slate-200 hover:text-slate-400'
                                                ]"
                                            >
                                                {{ val }}
                                            </button>
                                        </template>
                                        <template v-else>
                                            <div 
                                                v-if="rating.rating"
                                                class="h-11 w-11 rounded-2xl flex items-center justify-center font-black text-base shadow-xl transition-all duration-300 group-hover/cell:scale-110"
                                                :style="getRatingStyle(rating.rating)"
                                            >
                                                {{ rating.rating }}
                                            </div>
                                            <div v-else class="h-11 w-11 rounded-2xl border-2 border-dashed border-slate-100 flex items-center justify-center group-hover/cell:border-slate-300 transition-all">
                                                <span class="text-[11px] font-black text-slate-100 group-hover/cell:text-slate-300 italic opacity-20">PENDING</span>
                                            </div>
                                        </template>
                                    </div>
                                    
                                    <!-- Keyboard Helper Overlay -->
                                    <div v-if="activeCell.studentIndex === sIdx && activeCell.itemIndex === iIdx" class="absolute -top-5 left-1/2 -translate-x-1/2 bg-slate-900 text-white text-[9px] font-black px-3 py-1 rounded-full z-50 animate-bounce uppercase tracking-widest shadow-xl">
                                        Type 1-4
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Matrix Legend / Global Stats -->
                <div class="p-8 bg-slate-900 border-t border-slate-800 flex flex-col md:flex-row items-center justify-between gap-8 text-white">
                    <div class="flex items-center gap-6">
                         <div class="h-14 w-14 rounded-2xl bg-white/10 flex items-center justify-center text-white backdrop-blur-md shadow-inner">
                             <Zap class="h-7 w-7 fill-white animate-pulse" />
                         </div>
                         <div class="space-y-1">
                             <h4 class="text-sm font-black uppercase tracking-[0.3em]">Evaluation Integrity Matrix</h4>
                             <p class="text-[10px] font-bold text-slate-400 tracking-wider">Keyboard Bindings: ARROW KEYS to navigate • 1-4 to Rate • BACKSPACE to Clear</p>
                         </div>
                    </div>

                    <div class="flex items-center gap-10">
                         <div class="text-center group">
                             <span class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] block mb-1">Total Learners</span>
                             <span class="text-2xl font-black italic">{{ results.length }}</span>
                         </div>
                         <div class="h-10 w-px bg-white/10"></div>
                         <div class="text-center group">
                             <span class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] block mb-1">Evaluation Points</span>
                             <span class="text-2xl font-black italic">{{ flatItems.length }}</span>
                         </div>
                         <div class="h-10 w-px bg-white/10"></div>
                         <div class="space-y-2 min-w-[200px]">
                             <div class="flex justify-between items-end">
                                 <span class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Completion Progress</span>
                                 <span class="text-sm font-black italic text-emerald-400">
                                     {{ Math.round((results.flatMap((s: any) => s.ratings).filter((r: any) => r.rating !== null).length / (results.length * flatItems.length)) * 100) }}%
                                 </span>
                             </div>
                             <div class="w-full h-2 bg-white/5 rounded-full overflow-hidden p-0.5 border border-white/10">
                                 <div 
                                    class="h-full bg-emerald-500 rounded-full transition-all duration-1000 shadow-[0_0_15px_rgba(16,185,129,0.5)]"
                                    :style="{ width: `${Math.round((results.flatMap((s: any) => s.ratings).filter((r: any) => r.rating !== null).length / (results.length * flatItems.length)) * 100)}%` }"
                                 ></div>
                             </div>
                         </div>
                    </div>
                </div>
            </Card>
        </div>
    </AppLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,700;0,800;1,400;1,700;1,800&display=swap');

.font-sans {
    font-family: 'Plus Jakarta Sans', sans-serif;
}

.scrollbar-none::-webkit-scrollbar {
    display: none;
}
.scrollbar-none {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

table {
    border-spacing: 0;
}

th.sticky, td.sticky {
    box-shadow: 4px 0 8px -4px rgba(0,0,0,0.05);
}

.animate-in {
    animation: fadeIn 0.4s ease-out forwards;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Transition for cell scaling */
td {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>
