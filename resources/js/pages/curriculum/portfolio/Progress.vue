<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { 
    GraduationCap, BookOpen, CheckCircle2, 
    ArrowLeft, Search, Filter, Calendar,
    User, Award, Zap, Activity, Info
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Badge } from '@/components/ui/badge';
import { Progress } from "@/components/ui/progress";
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    student: any;
    achievements: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Student Progress', href: '#' },
];

const getLevelColor = (level: string) => {
    switch (level) {
        case 'EE': return 'text-emerald-600 bg-emerald-50 border-emerald-100';
        case 'ME': return 'text-blue-600 bg-blue-50 border-blue-100';
        case 'AE': return 'text-amber-600 bg-amber-50 border-amber-100';
        case 'BE': return 'text-red-600 bg-red-50 border-red-100';
        default: return 'text-slate-600 bg-slate-50 border-slate-100';
    }
};

const getLevelName = (level: string) => {
    switch (level) {
        case 'EE': return 'Excellent';
        case 'ME': return 'Good';
        case 'AE': return 'Fair';
        case 'BE': return 'Needs Work';
        default: return level;
    }
};

</script>

<template>
    <Head :title="`${student.name} - Progress`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 p-8 font-sans max-w-[1600px] mx-auto animate-in fade-in duration-500">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-1">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 shadow-sm border border-blue-100">
                            <GraduationCap class="h-5 w-5" />
                        </div>
                        <h1 class="text-3xl font-bold tracking-tight text-slate-900">{{ student.name }}</h1>
                    </div>
                    <p class="text-sm font-medium text-slate-500 ml-14 tracking-tight">View student performance and learning progress.</p>
                </div>
            </div>

            <!-- Dashboard -->
            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Achievements -->
                <div class="lg:col-span-2 space-y-8">
                    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden min-h-[500px]">
                        <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/20">
                            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-tight">Learning Goals</h3>
                            <Badge variant="outline" class="text-[10px] font-bold uppercase tracking-wider border-blue-100 text-blue-600 px-3 py-1 bg-white">{{ achievements.length }} Goals Met</Badge>
                        </div>

                        <div v-if="achievements.length" class="p-6 space-y-4">
                            <div v-for="achievement in achievements" :key="achievement.id" class="p-5 rounded-2xl bg-slate-50 border border-slate-100 group hover:bg-white hover:border-blue-200 hover:shadow-sm transition-all">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex gap-4">
                                        <div :class="['h-10 w-10 rounded-xl flex items-center justify-center font-bold text-sm border transition-all', getLevelColor(achievement.achievement_level)]">
                                            {{ achievement.achievement_level }}
                                        </div>
                                        <div class="space-y-1">
                                            <h4 class="text-base font-bold text-slate-900 group-hover:text-blue-600 transition-colors uppercase tracking-tight">{{ achievement.learning_outcome?.outcome }}</h4>
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Checked on {{ achievement.assessment_date }}</p>
                                        </div>
                                    </div>
                                    <Badge variant="outline" class="text-[8px] font-bold uppercase tracking-wider bg-white border-slate-100">{{ getLevelName(achievement.achievement_level) }}</Badge>
                                </div>
                                
                                <div v-if="achievement.remarks" class="pl-14 italic text-xs text-slate-500">
                                    "{{ achievement.remarks }}"
                                </div>
                            </div>
                        </div>

                        <div v-else class="py-32 text-center">
                             <Award class="h-12 w-12 text-slate-200 mx-auto mb-4" />
                             <h3 class="text-xl font-bold text-slate-400 tracking-tight mb-2 uppercase">No progress yet</h3>
                             <p class="text-sm text-slate-400 italic">Progress will appear here once assessments are done.</p>
                        </div>
                    </div>
                </div>

                <!-- Summary -->
                <div class="space-y-8">
                     <div class="rounded-3xl bg-slate-900 p-8 shadow-xl text-white relative overflow-hidden group">
                        <div class="absolute -right-10 -top-10 opacity-10 group-hover:scale-110 transition-transform duration-700">
                            <Activity class="h-48 w-48" />
                        </div>
                        <div class="relative z-10 space-y-8">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-bold uppercase tracking-tight flex items-center gap-3">
                                    <Info class="h-5 w-5 text-blue-400" /> Completion
                                </h3>
                                <div class="h-12 w-12 rounded-xl bg-white/10 flex items-center justify-center font-bold text-blue-400 shadow-sm">
                                    {{ Math.round((achievements.length / 25) * 100) }}%
                                </div>
                            </div>
                            
                            <div class="space-y-5">
                                <div v-for="(count, level) in { 'EE': achievements.filter(a => a.achievement_level === 'EE').length, 'ME': achievements.filter(a => a.achievement_level === 'ME').length, 'AE': achievements.filter(a => a.achievement_level === 'AE').length, 'BE': achievements.filter(a => a.achievement_level === 'BE').length }" :key="level" class="space-y-2">
                                    <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider">
                                        <span class="text-slate-400">{{ getLevelName(level) }}</span>
                                        <span class="text-white">{{ count }}</span>
                                    </div>
                                    <div class="h-1.5 w-full rounded-full bg-white/5">
                                        <div class="h-full rounded-full transition-all duration-700" :class="getLevelColor(level).split(' ')[0].replace('text-', 'bg-')" :style="{ width: `${(count / Math.max(1, achievements.length)) * 100}%` }"></div>
                                    </div>
                                </div>
                            </div>

                            <p class="text-[10px] font-medium text-slate-400 leading-relaxed italic pt-6 border-t border-white/5">
                                Progress is calculated based on completed learning goals and syllabus updates.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Minimal styling */
</style>
