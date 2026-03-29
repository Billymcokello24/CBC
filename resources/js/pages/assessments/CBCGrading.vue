<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { 
    ClipboardList, Save, ArrowLeft, User, CheckCircle2, 
    AlertCircle, Search, Keyboard, Zap, Info, MoreHorizontal,
    ChevronRight, ChevronDown, Check, X, Star
} from 'lucide-vue-next';
import { ref, computed, onMounted, onUnmounted } from 'vue';
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
    { title: 'CBC Grading', href: '#' },
];

const searchQuery = ref('');
const activeCell = ref({ studentIndex: 0, itemIndex: 0 });
const saving = ref(false);
const lastSavedAt = ref<Date | null>(null);

// Initialize results matrix
const results = ref<any[]>(props.students.map(student => {
    const studentRatings = props.existingRatings[student.id] || [];
    return {
        id: student.id,
        name: `${student.first_name} ${student.last_name}`,
        admission_number: student.admission_number,
        photo: student.photo,
        ratings: props.assessment.items.map((item: any) => {
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

const getRatingColor = (rating: number | null) => {
    if (!rating) return 'text-slate-300 bg-slate-50';
    const scale = props.ratingScales.find(s => s.id === rating);
    return scale ? `text-white` : 'text-slate-300 bg-slate-50';
};

const getRatingBg = (rating: number | null) => {
    if (!rating) return 'bg-white border-slate-100';
    const scale = props.ratingScales.find(s => s.id === rating);
    return scale ? `bg-[${scale.color}] border-[${scale.color}]` : 'bg-white border-slate-100';
};

// Custom style helper since Tailwind won't pick up dynamic hex colors easily without JIT mapping
const getRatingStyle = (rating: number | null) => {
    if (!rating) return {};
    const scale = props.ratingScales.find(s => s.id === rating);
    return scale ? { backgroundColor: scale.color, borderColor: scale.color, color: 'white' } : {};
};

const updateRating = async (studentIndex: number, itemIndex: number, value: number | null) => {
    const student = filteredResults.value[studentIndex];
    const ratingEntry = student.ratings[itemIndex];
    
    // Toggle if same value
    if (ratingEntry.rating === value) {
        ratingEntry.rating = null;
    } else {
        ratingEntry.rating = value;
    }

    // Auto-save
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

// Keyboard Navigation
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
    } else if (e.key === 'ArrowRight' && itemIndex < props.assessment.items.length - 1) {
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
    <Head :title="`CBC Grading - ${assessment.title}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 font-pulsar mt-2 max-w-[1400px] mx-auto overflow-hidden">
            <!-- Header Section -->
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="icon" as-child class="rounded-full h-10 w-10 border border-slate-100 shadow-sm bg-white">
                        <Link href="/assessments"><ArrowLeft class="h-5 w-5 text-slate-500" /></Link>
                    </Button>
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <h1 class="text-2xl font-black text-slate-800 tracking-tight">{{ assessment.title }}</h1>
                            <Badge class="bg-indigo-600 text-white font-black text-[10px] uppercase tracking-widest px-2.5 py-1">CBC EVALUATION</Badge>
                        </div>
                        <div class="flex items-center gap-2 text-xs font-bold text-slate-400">
                            <span class="flex items-center gap-1"><Keyboard class="h-3 w-3" /> Keyboard Mode Active</span>
                            <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                            <span>{{ assessment.class?.name }}</span>
                            <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                            <span>{{ assessment.subject?.name }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-4 px-6 py-3 bg-white border border-slate-100 rounded-3xl shadow-sm">
                    <div class="text-right hidden sm:block border-r border-slate-100 pr-4 mr-1">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Auto-Save Status</p>
                        <p class="text-[10px] font-bold text-emerald-500 flex items-center justify-end gap-1">
                            <Zap class="h-3 w-3 fill-emerald-500" /> {{ lastSavedAt ? `Saved ${lastSavedAt.toLocaleTimeString()}` : 'Ready' }}
                        </p>
                    </div>
                    <Button @click="submitAll" :disabled="saving" class="h-11 px-8 rounded-2xl bg-indigo-600 hover:bg-indigo-700 shadow-lg shadow-indigo-100 font-black text-xs uppercase tracking-widest">
                        <Save v-if="!saving" class="mr-2 h-4 w-4" />
                        <div v-else class="mr-2 h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                        Finalize & Close
                    </Button>
                </div>
            </div>

            <!-- Stats & Filter -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="md:col-span-1 relative">
                    <Search class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
                    <Input v-model="searchQuery" placeholder="Filter student list..." class="h-12 pl-11 rounded-2xl border-slate-100 shadow-sm focus:ring-indigo-500/10" />
                </div>
                
                <div class="md:col-span-3 flex items-center gap-4 overflow-x-auto pb-2 scrollbar-none">
                    <div v-for="scale in ratingScales" :key="scale.id" class="flex items-center gap-3 px-4 py-2.5 rounded-2xl bg-white border border-slate-100 shadow-sm shrink-0">
                         <div class="h-8 w-8 rounded-xl flex items-center justify-center font-black text-xs text-white shadow-xl" :style="{ backgroundColor: scale.color }">
                             {{ scale.id }}
                         </div>
                         <div>
                             <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest leading-none mb-0.5">{{ scale.code }}</p>
                             <p class="text-[10px] font-bold text-slate-700 whitespace-nowrap">{{ scale.name }}</p>
                         </div>
                    </div>
                </div>
            </div>

            <!-- Grading Sheet Matrix -->
            <Card class="flex-1 overflow-hidden border-slate-100 shadow-xl rounded-3xl flex flex-col">
                <div class="flex-1 overflow-auto relative">
                    <table class="w-full border-collapse">
                        <thead class="sticky top-0 z-30 bg-white shadow-sm">
                            <tr>
                                <th class="sticky left-0 z-40 bg-white p-6 text-left border-b border-r border-slate-100 w-[280px] min-w-[280px]">
                                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 leading-none">Grading Sheet</div>
                                    <h3 class="text-sm font-black text-slate-800 tracking-tight">Student Name</h3>
                                </th>
                                <th v-for="(item, idx) in assessment.items" :key="item.id" 
                                    class="p-4 text-left border-b border-slate-100 min-w-[200px] bg-slate-50/50"
                                    :class="{ 'ring-2 ring-indigo-500 ring-inset bg-indigo-50/30': activeCell.itemIndex === idx }"
                                >
                                    <div class="flex items-start gap-3">
                                        <div class="h-6 w-6 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-[10px] font-black text-slate-400 shrink-0 shadow-sm">
                                            {{ Number(idx) + 1 }}
                                        </div>
                                        <div>
                                            <p class="text-[8px] font-black text-indigo-400 uppercase tracking-widest leading-none mb-1">Indicator</p>
                                            <TooltipProvider>
                                                <Tooltip>
                                                    <TooltipTrigger class="text-left">
                                                        <p class="text-[11px] font-bold text-slate-700 leading-snug line-clamp-2">{{ item.indicator?.indicator || 'N/A' }}</p>
                                                    </TooltipTrigger>
                                                    <TooltipContent class="max-w-[300px] p-4 bg-slate-900 text-white rounded-xl border-none shadow-2xl">
                                                        <p class="text-xs font-bold leading-relaxed">{{ item.indicator?.indicator }}</p>
                                                    </TooltipContent>
                                                </Tooltip>
                                            </TooltipProvider>
                                        </div>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(student, sIdx) in filteredResults" :key="student.id" class="group transition-colors hover:bg-slate-50/50">
                                <!-- Student Name Column -->
                                <td class="sticky left-0 z-20 bg-white group-hover:bg-slate-50 p-4 border-r border-b border-slate-100 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)]">
                                    <div class="flex items-center gap-3">
                                        <div class="relative">
                                            <Avatar class="h-10 w-10 border-2 border-white shadow-md ring-2 ring-slate-100 group-hover:scale-105 transition-transform">
                                                <AvatarImage :src="student.photo" />
                                                <AvatarFallback class="bg-indigo-100 text-indigo-700 font-black text-xs">
                                                    {{ String(student.name).split(' ').map((n: string) => n[0]).join('') }}
                                                </AvatarFallback>
                                            </Avatar>
                                            <div v-if="student.ratings.every((r: any) => r.rating !== null)" class="absolute -right-1 -bottom-1 h-4 w-4 bg-emerald-500 rounded-full border-2 border-white flex items-center justify-center">
                                                <Check class="h-2.5 w-2.5 text-white stroke-[4]" />
                                            </div>
                                        </div>
                                        <div class="overflow-hidden">
                                            <p class="text-sm font-black text-slate-800 truncate leading-tight mb-0.5">{{ student.name }}</p>
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none">{{ student.admission_number }}</p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Ratings Matrix Cells -->
                                <td v-for="(rating, iIdx) in student.ratings" :key="iIdx" 
                                    class="p-3 border-b border-slate-100 transition-all text-center relative"
                                    :class="{ 'bg-indigo-50/40 ring-2 ring-indigo-500/20 z-10': activeCell.studentIndex === Number(sIdx) && activeCell.itemIndex === Number(iIdx) }"
                                    @click="activeCell = { studentIndex: Number(sIdx), itemIndex: Number(iIdx) }"
                                >
                                    <div class="flex items-center justify-center gap-1.5 h-10 w-full group/cell transition-all">
                                        <!-- Rating Buttons -->
                                        <template v-if="activeCell.studentIndex === sIdx && activeCell.itemIndex === iIdx">
                                            <button 
                                                v-for="val in [1,2,3,4]" 
                                                :key="val"
                                                @click.stop="updateRating(sIdx, iIdx, val)"
                                                class="h-8 w-8 rounded-xl flex items-center justify-center font-black text-xs transition-all hover:scale-110 active:scale-90 shadow-sm border"
                                                :style="getRatingStyle(val === rating.rating ? val : null)"
                                                :class="[
                                                    val === rating.rating ? '' : 'bg-white text-slate-300 border-slate-100 hover:border-indigo-200 hover:text-indigo-400'
                                                ]"
                                            >
                                                {{ val }}
                                            </button>
                                        </template>
                                        <template v-else>
                                            <div 
                                                v-if="rating.rating"
                                                class="h-9 w-9 rounded-2xl flex items-center justify-center font-black text-sm shadow-lg transition-transform hover:scale-105"
                                                :style="getRatingStyle(rating.rating)"
                                            >
                                                {{ rating.rating }}
                                            </div>
                                            <div v-else class="h-9 w-9 rounded-2xl border-2 border-dashed border-slate-100 flex items-center justify-center group-hover:border-slate-200 transition-colors">
                                                <span class="text-[10px] font-black text-slate-200 group-hover:text-slate-300">0</span>
                                            </div>
                                        </template>
                                    </div>
                                    
                                    <!-- Keyboard Helper Overlay -->
                                    <div v-if="activeCell.studentIndex === sIdx && activeCell.itemIndex === iIdx" class="absolute -top-4 left-1/2 -translate-x-1/2 bg-slate-900 text-white text-[8px] font-black px-2 py-0.5 rounded-full z-50 animate-bounce uppercase tracking-tighter">
                                        Use 1-4
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Matrix Legend / Footer info -->
                <div class="p-6 bg-slate-50 border-t border-slate-100 flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="flex items-center gap-3">
                         <div class="h-10 w-10 rounded-2xl bg-indigo-100 flex items-center justify-center text-indigo-600 shadow-sm">
                             <Zap class="h-5 w-5 fill-indigo-600" />
                         </div>
                         <div>
                             <h4 class="text-xs font-black text-slate-800 uppercase tracking-widest">Efficiency Mode Engaged</h4>
                             <p class="text-[10px] font-bold text-slate-400 leading-none mt-1">Movement with Arrows • Rating with 1-4 • Clear with Backspace</p>
                         </div>
                    </div>

                    <div class="flex items-center gap-8">
                         <div class="flex flex-col items-center">
                             <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Students</span>
                             <span class="text-lg font-black text-slate-800">{{ results.length }}</span>
                         </div>
                         <div class="h-8 w-px bg-slate-200"></div>
                         <div class="flex flex-col items-center">
                             <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Indicators</span>
                             <span class="text-lg font-black text-slate-800">{{ assessment.items.length }}</span>
                         </div>
                         <div class="h-8 w-px bg-slate-200"></div>
                         <div class="flex flex-col items-center">
                             <span class="text-[9px] font-black text-emerald-400 uppercase tracking-widest leading-none mb-1">Completion</span>
                             <div class="flex items-center gap-2">
                                <span class="text-lg font-black text-slate-800">
                                    {{ Math.round((results.flatMap((s: any) => s.ratings).filter((r: any) => r.rating !== null).length / (results.length * assessment.items.length)) * 100) }}%
                                </span>
                                <div class="w-20 h-2 bg-slate-200 rounded-full overflow-hidden">
                                     <div 
                                        class="h-full bg-emerald-500 transition-all duration-1000"
                                        :style="{ width: `${Math.round((results.flatMap((s: any) => s.ratings).filter((r: any) => r.rating !== null).length / (results.length * assessment.items.length)) * 100)}%` }"
                                     ></div>
                                </div>
                             </div>
                         </div>
                    </div>
                </div>
            </Card>
        </div>
    </AppLayout>
</template>

<style scoped>
.font-pulsar {
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
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
    box-shadow: 2px 0 5px -2px rgba(0,0,0,0.05);
}

input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  appearance: none;
  margin: 0;
}

.animate-in {
    animation: fadeIn 0.4s ease-out forwards;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
