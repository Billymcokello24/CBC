<script setup lang="ts">
/* global route */
import { Head, Link } from '@inertiajs/vue3';
import { 
    BookOpen, GraduationCap, Layers, BrainCircuit, 
    ArrowRight, Activity, Zap, ShieldCheck, 
    LayoutDashboard, Plus, Settings, Network, Sparkles, Target, BookOpenCheck
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    stats: {
        learning_areas: number;
        subjects: number;
        strands: number;
        competencies: number;
    };
    recent_areas: any[];
    recent_competencies: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Curriculum Orchestration', href: '/curriculum' },
];

const modules = [
    {
        title: 'Learning Domains',
        description: 'Manage top-level curriculum clusters and specialized study areas.',
        count: props.stats.learning_areas,
        icon: GraduationCap,
        color: 'violet',
        href: route('curriculum.learning-areas'),
        action: 'Configure Clusters'
    },
    {
        title: 'Subject Registry',
        description: 'Define instructional models, weighting, and academic subjects.',
        count: props.stats.subjects,
        icon: BookOpen,
        color: 'emerald',
        href: route('curriculum.subjects'),
        action: 'Manage Registry'
    },
    {
        title: 'Strands & Topics',
        description: 'Map detailed learning outcomes and performance indicators by grade.',
        count: props.stats.strands,
        icon: Layers,
        color: 'blue',
        href: route('curriculum.strands'),
        action: 'Design Mapping'
    },
    {
        title: 'Core Competencies',
        description: 'Establish system-wide pillars and behavioral indicators.',
        count: props.stats.competencies,
        icon: BrainCircuit,
        color: 'indigo',
        href: route('curriculum.competencies'),
        action: 'Logic Control'
    }
];

const colors = {
    violet: 'bg-violet-500/10 text-violet-600 border-violet-200 group-hover:bg-violet-600',
    emerald: 'bg-emerald-500/10 text-emerald-600 border-emerald-200 group-hover:bg-emerald-600',
    blue: 'bg-blue-500/10 text-blue-600 border-blue-200 group-hover:bg-blue-600',
    indigo: 'bg-indigo-500/10 text-indigo-600 border-indigo-200 group-hover:bg-indigo-600'
};

</script>

<template>
    <Head title="Curriculum Orchestration" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 p-8 font-pulsar mt-2 max-w-[1700px] mx-auto animate-in fade-in duration-700">
            <!-- Premium Hero Header -->
            <div class="relative overflow-hidden rounded-[3.5rem] bg-slate-950 p-16 text-white shadow-2xl border-[12px] border-slate-900 group">
                <div class="absolute -right-20 -top-20 opacity-10 group-hover:scale-110 group-hover:rotate-12 transition-all duration-1000 transform">
                     <Network class="h-[500px] w-[500px]" />
                </div>
                <div class="absolute -left-20 -bottom-20 opacity-10 group-hover:scale-110 group-hover:-rotate-12 transition-all duration-1000 transform">
                     <Target class="h-64 w-64" />
                </div>
                
                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-12">
                     <div class="max-w-2xl space-y-6">
                          <div class="flex items-center gap-3">
                               <Badge class="bg-violet-600/30 text-violet-400 border border-violet-500/30 text-[10px] font-black uppercase tracking-widest px-4 py-1 rounded-full">Academic Engine v2.6</Badge>
                               <div class="h-[2px] w-12 bg-slate-800"></div>
                               <span class="text-[10px] font-black text-slate-500 uppercase tracking-[0.4em]">Integrated Matrix</span>
                          </div>
                          <h1 class="text-5xl md:text-7xl font-black tracking-tighter leading-[0.9] text-white">
                               CURRICULUM <br/>
                               <span class="text-violet-500 drop-shadow-[0_0_15px_rgba(139,92,246,0.3)]">ORCHESTRATION</span>
                          </h1>
                          <p class="text-slate-400 text-lg md:text-xl font-medium max-w-lg leading-relaxed opacity-80 italic">
                               Architecting the future of academic delivery through integrated instructional mapping and performance matrix control.
                          </p>
                          <div class="flex flex-wrap gap-4 pt-6">
                               <Button variant="outline" class="bg-white/5 border-white/10 text-white hover:bg-white hover:text-slate-950 h-14 px-8 rounded-2xl font-black text-xs uppercase tracking-widest transition-all duration-300 shadow-xl" as-child>
                                    <Link :href="route('curriculum.learning-areas')">Domain Registry</Link>
                               </Button>
                               <Button class="bg-violet-600 hover:bg-violet-700 h-14 px-8 rounded-2xl font-black text-xs uppercase tracking-widest transition-all duration-300 shadow-[0_0_25px_rgba(139,92,246,0.4)] border-0" as-child>
                                    <Link :href="route('curriculum.subjects')">Matrix Control</Link>
                               </Button>
                          </div>
                     </div>

                     <!-- Live Status Hub -->
                     <div class="hidden lg:flex flex-col gap-6 w-full max-w-sm">
                          <div class="p-8 rounded-[2.5rem] bg-white/5 border border-white/10 backdrop-blur-md shadow-inner space-y-8">
                               <div class="flex items-center justify-between">
                                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Logic Stream</h3>
                                    <div class="flex items-center gap-2">
                                         <div class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse shadow-[0_0_8px_rgba(16,185,129,0.8)]"></div>
                                         <span class="text-[10px] font-black text-emerald-400 uppercase italic">Active</span>
                                    </div>
                               </div>
                               <div class="space-y-6 border-t border-white/5 pt-6">
                                    <div class="flex items-center justify-between">
                                         <span class="text-sm font-bold text-slate-300">Global Sync</span>
                                         <span class="text-sm font-black text-white">VALIDATED</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                         <span class="text-sm font-bold text-slate-300">Matrix Load</span>
                                         <span class="text-sm font-black text-violet-400">OPTIMAL</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                         <span class="text-sm font-bold text-slate-300">Auth Tier</span>
                                         <span class="text-sm font-black text-slate-400 uppercase">SuperAdmin</span>
                                    </div>
                               </div>
                          </div>
                     </div>
                </div>
            </div>

            <!-- Dashboard Modules -->
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                <div v-for="mod in modules" :key="mod.title" class="group relative overflow-hidden rounded-[2.5rem] border bg-card p-10 shadow-sm transition-all hover:shadow-xl hover:translate-y-[-4px] border-b-8" :style="`border-bottom-color: var(--${mod.color}-500)`">
                    <div class="absolute -right-4 -top-4 opacity-[0.03] group-hover:scale-125 transition-transform duration-700 transform">
                         <mod.icon class="h-40 w-40" />
                    </div>
                    <div class="relative z-10 space-y-6">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl transition-all duration-300 shadow-sm" :class="colors[mod.color as keyof typeof colors]">
                            <mod.icon class="h-7 w-7 transition-colors group-hover:text-white" />
                        </div>
                        <div class="space-y-2">
                            <h2 class="text-2xl font-black tracking-tighter text-slate-900 group-hover:text-slate-700">{{ mod.title }}</h2>
                            <p class="text-sm font-medium text-muted-foreground leading-relaxed italic opacity-80">{{ mod.description }}</p>
                        </div>
                        <div class="flex items-center justify-between pt-6 border-t border-slate-50">
                            <div>
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Active Nodes</span>
                                <p class="text-3xl font-black text-slate-900">{{ mod.count }}</p>
                            </div>
                            <Button variant="ghost" size="icon" class="h-12 w-12 rounded-xl border border-slate-100 shadow-sm hover:bg-slate-900 hover:text-white transition-all group/btn" as-child>
                                <Link :href="mod.href"><ArrowRight class="h-5 w-5 group-hover/btn:translate-x-1 transition-transform" /></Link>
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lower Intelligence Matrix -->
            <div class="grid gap-8 lg:grid-cols-12">
                 <!-- Main Activity Cluster -->
                 <div class="lg:col-span-8 space-y-8">
                      <div class="rounded-[3rem] border bg-card p-10 shadow-sm border-t-8 border-t-emerald-500 relative overflow-hidden min-h-[500px]">
                           <div class="absolute -right-20 -top-20 opacity-[0.03]">
                                <Activity class="h-96 w-96 text-slate-950" />
                           </div>
                           <div class="relative z-10 flex items-center justify-between mb-10">
                                <div>
                                     <h2 class="text-xs font-black text-slate-900 uppercase tracking-[0.3em] flex items-center gap-3">
                                          <Sparkles class="h-4 w-4 text-emerald-600" /> Recent Domain Evolution
                                     </h2>
                                     <p class="text-xs text-slate-400 font-medium mt-1 italic uppercase tracking-wider">Latest updates within the curriculum library</p>
                                </div>
                                <Button variant="outline" class="h-9 font-pulsar border-slate-200 text-[10px] uppercase tracking-widest font-black rounded-xl" as-child>
                                     <Link :href="route('curriculum.learning-areas')">View All Clusters</Link>
                                </Button>
                           </div>

                           <div class="grid sm:grid-cols-2 gap-6">
                                <Link v-for="area in recent_areas" :key="area.id" :href="route('curriculum.learning-areas.show', area.id)" class="group/item relative p-8 rounded-[2rem] border bg-white hover:shadow-xl hover:border-emerald-200 transition-all duration-500 overflow-hidden">
                                     <div class="absolute -right-4 -bottom-4 opacity-[0.02] group-hover/item:scale-110 transition-transform duration-700">
                                          <GraduationCap class="h-24 w-24" />
                                     </div>
                                     <div class="relative z-10 flex items-start gap-5">
                                          <div class="h-12 w-12 rounded-2xl bg-slate-50 border shadow-inner flex items-center justify-center font-black text-slate-400 group-hover/item:bg-emerald-600 group-hover/item:text-white transition-all text-xs uppercase">
                                               {{ area.code.substring(0, 2) }}
                                          </div>
                                          <div class="space-y-1">
                                               <h4 class="text-lg font-black text-slate-900 group-hover/item:text-emerald-700 transition-colors leading-tight tracking-tight">{{ area.name }}</h4>
                                               <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic">{{ area.code }} • {{ area.subjects_count }} Subjects</p>
                                          </div>
                                     </div>
                                     <div class="mt-8 flex items-center justify-between relative z-10">
                                          <Badge variant="outline" class="border-0 bg-slate-100 text-[9px] font-black uppercase tracking-tighter px-2 group-hover/item:bg-emerald-50 group-hover/item:text-emerald-600 transition-colors">{{ area.category || 'Standard' }}</Badge>
                                          <ChevronRight class="h-5 w-5 text-slate-300 group-hover/item:text-emerald-600 transition-colors" />
                                     </div>
                                </Link>
                           </div>
                      </div>
                 </div>

                 <!-- Sidebar Intelligence -->
                 <div class="lg:col-span-4 space-y-8">
                      <div class="rounded-[3rem] bg-slate-950 p-10 shadow-2xl text-white relative overflow-hidden group border-[12px] border-slate-900 shrink-0 h-full">
                           <div class="absolute -right-16 -top-16 opacity-10 group-hover:scale-110 group-hover:-rotate-12 transition-all duration-700 transform">
                                <Network class="h-64 w-64" />
                           </div>
                           <div class="relative z-10 space-y-10">
                                <div class="flex items-center justify-between">
                                     <h3 class="text-xl font-black uppercase tracking-tighter flex items-center gap-3">
                                         <Activity class="h-6 w-6 text-violet-400" /> Matrix Pulse
                                     </h3>
                                     <div class="h-10 w-10 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center">
                                          <Settings class="h-5 w-5 text-slate-400" />
                                     </div>
                                </div>
                                
                                <div class="space-y-8">
                                     <div v-for="comp in recent_competencies" :key="comp.id" class="p-6 rounded-3xl bg-white/5 border border-white/10 shadow-inner group/node transition-all hover:bg-white/10 cursor-pointer">
                                          <div class="flex items-center gap-4">
                                               <div class="h-10 w-10 rounded-xl bg-violet-600/20 flex items-center justify-center font-black text-violet-400 group-hover/node:bg-violet-600 group-hover/node:text-white transition-all text-xs border border-violet-500/30 uppercase">
                                                    {{ comp.name.substring(0, 1) }}
                                               </div>
                                               <div>
                                                   <h4 class="text-sm font-bold text-white tracking-tight">{{ comp.name }}</h4>
                                                   <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mt-0.5">Core Indicator Node</p>
                                               </div>
                                          </div>
                                     </div>
                                </div>

                                <div class="p-8 rounded-[2.5rem] bg-violet-600/10 border border-violet-500/20 shadow-inner mt-10 space-y-4">
                                     <h4 class="text-[10px] font-black text-violet-400 uppercase tracking-widest leading-none">Security Protocol</h4>
                                     <p class="text-xs text-white/70 font-medium leading-relaxed italic">Curriculum nodes are finalized and synchronized across the 2026 Academic cycle cluster.</p>
                                     <div class="pt-4 flex items-center gap-3">
                                          <ShieldCheck class="h-5 w-5 text-emerald-400" />
                                          <span class="text-[10px] font-black text-emerald-400 uppercase tracking-widest italic">MATRIX_LOCKED_2026</span>
                                     </div>
                                </div>
                           </div>
                      </div>
                 </div>
            </div>

            <!-- Global Matrix Hub Callout -->
            <div class="mt-4 p-8 rounded-[3.5rem] bg-white border-2 border-slate-100 shadow-xl flex flex-col md:flex-row items-center justify-between gap-10 group hover:border-violet-200 transition-all duration-500">
                <div class="flex items-center gap-8">
                    <div class="h-20 w-20 rounded-[1.5rem] bg-violet-500/10 flex items-center justify-center text-violet-600 group-hover:bg-violet-600 group-hover:text-white transition-all duration-500 shadow-inner">
                         <BookOpenCheck class="h-10 w-10" />
                    </div>
                    <div class="max-w-xl">
                        <h3 class="text-2xl font-black text-slate-900 tracking-tighter uppercase mb-2">Initialize Global Academic Allocation</h3>
                        <p class="text-slate-500 text-sm font-medium leading-relaxed italic opacity-80">Synchronize subject nodes with grade-level performance metrics and instructional hourly weights across the entire learning ecosystem.</p>
                    </div>
                </div>
                 <div class="flex gap-4">
                      <Button class="bg-slate-950 hover:bg-black h-14 px-8 rounded-2xl font-black text-xs uppercase tracking-widest transition-all duration-300 shadow-xl border-0" as-child>
                           <Link :href="route('curriculum.subjects')">Finalize Matrix Mapping</Link>
                      </Button>
                 </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.font-black { font-weight: 950; }
:root {
  --violet-500: #8b5cf6;
  --emerald-500: #10b981;
  --blue-500: #3b82f6;
  --indigo-500: #6366f1;
}
</style>
