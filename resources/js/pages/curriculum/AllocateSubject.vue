<script setup lang="ts">
/* global route */
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, BookOpen, Save, GraduationCap, CheckCircle2, ShieldCheck, Square, CheckSquare, Clock, BookOpenCheck } from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    subject: {
        id: number;
        name: string;
        code: string;
        subject_type: string;
    };
    grades: Array<{
        id: number;
        name: string;
        code: string;
        is_allocated: boolean;
        lessons_per_week: number;
        minutes_per_lesson: number;
        is_compulsory: boolean;
        is_active: boolean;
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Subjects Registry', href: route('curriculum.subjects') },
    { title: props.subject.name, href: route('curriculum.subjects.show', props.subject.id) },
    { title: 'Grade Allocation', href: route('curriculum.subjects.allocate', props.subject.id) },
];

const form = useForm({
    allocations: props.grades.map((grade) => ({
        grade_level_id: grade.id,
        selected: grade.is_allocated,
        lessons_per_week: grade.lessons_per_week,
        minutes_per_lesson: grade.minutes_per_lesson,
        is_compulsory: grade.is_compulsory,
        is_active: grade.is_active,
    })),
});

const selectedCount = computed(() => form.allocations.filter((item) => item.selected).length);

const submit = () => {
    form.put(route('curriculum.subjects.allocate.save', props.subject.id));
};

const toggleSelect = (index: number) => {
    form.allocations[index].selected = !form.allocations[index].selected;
};
</script>

<template>
    <Head :title="`Allocate ${subject.name}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 font-pulsar mt-2 max-w-[1400px] mx-auto animate-in fade-in duration-500">
            <!-- Header section -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="outline" size="icon" as-child class="h-10 w-10 shrink-0 border-slate-200">
                        <Link :href="route('curriculum.subjects.show', subject.id)"><ArrowLeft class="h-4 w-4" /></Link>
                    </Button>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-500/10 shrink-0">
                        <BookOpenCheck class="h-6 w-6 text-violet-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Grade Allocation Matrix</h1>
                        <p class="text-muted-foreground font-medium uppercase text-[10px] tracking-widest">{{ subject.name }} • {{ subject.code }} • {{ subject.subject_type }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="rounded-xl border bg-card px-5 py-2 text-sm shadow-sm flex items-center gap-3">
                         <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Selected Hubs</span>
                         <span class="text-lg font-black text-violet-600">{{ selectedCount }}</span>
                    </div>
                    <Button @click="submit" :disabled="form.processing" class="bg-slate-900 hover:bg-black h-10 font-pulsar shadow-lg border-0 px-6">
                        <Save class="mr-2 h-4 w-4" />Store Matrix
                    </Button>
                </div>
            </div>

            <!-- Dashboard Stats -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border bg-card p-5 shadow-sm border-l-4 border-l-violet-500 flex items-center gap-4 group hover:shadow-md transition-all">
                    <div class="rounded-xl bg-violet-500/10 p-3 group-hover:bg-violet-500 group-hover:text-white transition-all"><GraduationCap class="h-5 w-5 text-violet-600 group-hover:text-white" /></div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Available Grades</p>
                        <p class="text-xl font-black text-slate-900">{{ grades.length }} Levels</p>
                    </div>
                </div>
                <div class="rounded-2xl border bg-card p-5 shadow-sm border-l-4 border-l-emerald-500 flex items-center gap-4 group hover:shadow-md transition-all">
                    <div class="rounded-xl bg-emerald-500/10 p-3 group-hover:bg-emerald-500 group-hover:text-white transition-all"><CheckCircle2 class="h-5 w-5 text-emerald-600 group-hover:text-white" /></div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Allocation Pulse</p>
                        <p class="text-xl font-black text-emerald-600">{{ selectedCount }} Active</p>
                    </div>
                </div>
                <div class="rounded-2xl border bg-card p-5 shadow-sm border-l-4 border-l-blue-500 flex items-center gap-4 group hover:shadow-md transition-all">
                    <div class="rounded-xl bg-blue-500/10 p-3 group-hover:bg-blue-500 group-hover:text-white transition-all"><Clock class="h-5 w-5 text-blue-600 group-hover:text-white" /></div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Typical Weight</p>
                        <p class="text-xl font-black text-slate-900 uppercase tracking-tight">35 MINS / SESSION</p>
                    </div>
                </div>
                <div class="rounded-2xl border bg-card p-5 shadow-sm border-l-4 border-l-indigo-500 flex items-center gap-4 group hover:shadow-md transition-all">
                    <div class="rounded-xl bg-indigo-500/10 p-3 group-hover:bg-indigo-500 group-hover:text-white transition-all"><ShieldCheck class="h-5 w-5 text-indigo-600 group-hover:text-white" /></div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Security Status</p>
                        <p class="text-xl font-black text-indigo-600 uppercase tracking-tighter">SECURED</p>
                    </div>
                </div>
            </div>

            <!-- Allocation Table -->
            <div class="rounded-3xl border bg-card shadow-sm overflow-hidden border-t-8 border-t-violet-500">
                <div class="bg-slate-50 px-8 py-5 border-b flex items-center justify-between">
                     <h2 class="text-xs font-black text-slate-900 uppercase tracking-[0.2em] flex items-center gap-3">
                          <BookOpenCheck class="h-4 w-4 text-violet-600" /> Grade Level Mapping
                     </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-white border-b font-pulsar">
                                <th class="w-16 px-8 py-4 text-center text-[10px] font-bold uppercase text-slate-500 tracking-widest">Select</th>
                                <th class="px-8 py-4 text-[10px] font-bold uppercase text-slate-500 tracking-widest">Grade Hub</th>
                                <th class="px-8 py-4 text-[10px] font-bold uppercase text-slate-500 tracking-widest">Sessions / Week</th>
                                <th class="px-8 py-4 text-[10px] font-bold uppercase text-slate-500 tracking-widest">Duration (Mins)</th>
                                <th class="px-8 py-4 text-center text-[10px] font-bold uppercase text-slate-500 tracking-widest">Compulsory</th>
                                <th class="px-8 py-4 text-center text-[10px] font-bold uppercase text-slate-500 tracking-widest">Logic Active</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y text-sm">
                            <tr v-for="(allocation, index) in form.allocations" :key="allocation.grade_level_id" 
                                class="group transition-colors border-l-4 border-l-transparent"
                                :class="[allocation.selected ? 'bg-violet-50/30 border-l-violet-500' : 'hover:bg-slate-50/70']"
                            >
                                <td class="px-8 py-4 text-center">
                                     <button @click="toggleSelect(index)" type="button" class="h-6 w-6 rounded-lg border-2 flex items-center justify-center transition-all bg-white mx-auto shadow-sm" :class="allocation.selected ? 'bg-violet-600 border-violet-600 text-white' : 'border-slate-300'">
                                          <component :is="allocation.selected ? CheckSquare : Square" class="h-4 w-4" />
                                     </button>
                                </td>
                                <td class="px-8 py-4">
                                    <div>
                                        <div class="font-black text-slate-900 group-hover:text-violet-700 transition-colors text-base tracking-tight leading-none mb-1">{{ grades[index].name }}</div>
                                        <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest italic">{{ grades[index].code }} • Hub Node</div>
                                    </div>
                                </td>
                                <td class="px-8 py-4">
                                     <div class="relative max-w-[100px]">
                                          <Input v-model="allocation.lessons_per_week" type="number" min="1" :disabled="!allocation.selected" class="h-9 font-black text-center border-slate-200 focus:ring-violet-500" />
                                     </div>
                                </td>
                                <td class="px-8 py-4">
                                     <div class="relative max-w-[100px]">
                                          <Input v-model="allocation.minutes_per_lesson" type="number" min="1" :disabled="!allocation.selected" class="h-9 font-black text-center border-slate-200 focus:ring-violet-500" />
                                     </div>
                                </td>
                                <td class="px-8 py-4 text-center">
                                     <button @click="allocation.selected && (allocation.is_compulsory = !allocation.is_compulsory)" :disabled="!allocation.selected" type="button" class="h-6 w-6 rounded-lg border-2 flex items-center justify-center transition-all bg-white mx-auto shadow-sm disabled:opacity-30" :class="allocation.is_compulsory ? 'bg-blue-600 border-blue-600 text-white' : 'border-slate-300'">
                                          <component :is="allocation.is_compulsory ? CheckSquare : Square" class="h-4 w-4" />
                                     </button>
                                </td>
                                <td class="px-8 py-4 text-center">
                                     <button @click="allocation.selected && (allocation.is_active = !allocation.is_active)" :disabled="!allocation.selected" type="button" class="h-6 w-6 rounded-lg border-2 flex items-center justify-center transition-all bg-white mx-auto shadow-sm disabled:opacity-30" :class="allocation.is_active ? 'bg-emerald-600 border-emerald-600 text-white' : 'border-slate-300'">
                                          <component :is="allocation.is_active ? CheckSquare : Square" class="h-4 w-4" />
                                     </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Footer Sync -->
            <div class="mt-2 p-6 rounded-3xl bg-slate-900 text-white flex flex-col md:flex-row items-center justify-between gap-6 shadow-2xl border border-slate-800">
                <div class="flex items-center gap-4">
                    <div class="h-10 w-10 rounded-xl bg-white/10 flex items-center justify-center">
                         <Save class="h-5 w-5 text-violet-400" />
                    </div>
                    <div>
                        <h3 class="font-bold text-sm tracking-tight text-white/90 uppercase">Allocation Matrix Optimization</h3>
                        <p class="text-slate-400 text-[10px] mt-0.5 tracking-wider font-semibold">Store your grade-level mapping to finalize the instructional pulse for this subject node.</p>
                    </div>
                </div>
                 <div class="flex gap-3">
                      <Button variant="outline" type="button" as-child class="bg-white/5 border-white/10 text-white hover:bg-white/20 h-10 font-bold px-6 rounded-xl text-xs uppercase tracking-widest">
                           <Link :href="route('curriculum.subjects.show', subject.id)">Discard Changes</Link>
                      </Button>
                      <Button @click="submit" :disabled="form.processing" class="bg-violet-600 hover:bg-violet-700 h-10 font-pulsar px-8 rounded-xl text-xs uppercase tracking-widest shadow-xl border-0">
                           Finalize Matrix
                      </Button>
                 </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.font-black { font-weight: 950; }
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
  opacity: 1;
}
</style>
