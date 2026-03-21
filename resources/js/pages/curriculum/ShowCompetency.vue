<script setup lang="ts">
/* global route */
import { Head, Link } from '@inertiajs/vue3';
import { BrainCircuit, ArrowLeft, Edit, Target, CheckCircle2, ShieldCheck, ChevronRight, Sparkles, Network } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    competency: {
        id: number;
        name: string;
        code: string;
        description: string | null;
        category: string | null;
        display_order: number;
        is_active: boolean;
        indicators: Array<{
            id: number;
            indicator: string;
            is_active: boolean;
        }>;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' }, 
    { title: 'Curriculum', href: '/curriculum' }, 
    { title: 'Core Competencies', href: route('curriculum.competencies') }, 
    { title: props.competency.name, href: route('curriculum.competencies.show', props.competency.id) }
];
</script>

<template>
    <Head :title="competency.name" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 font-pulsar mt-2 max-w-[1400px] mx-auto animate-in fade-in duration-500">
            <!-- Header section -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="outline" size="icon" as-child class="h-10 w-10 shrink-0 border-slate-200">
                        <Link :href="route('curriculum.competencies')"><ArrowLeft class="h-4 w-4" /></Link>
                    </Button>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-500/10 shrink-0">
                        <BrainCircuit class="h-6 w-6 text-violet-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">{{ competency.name }}</h1>
                        <p class="text-muted-foreground font-medium uppercase text-[10px] tracking-widest">{{ competency.code }} • {{ competency.category || 'Core Pillar' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" as-child class="h-10 border-slate-200 font-pulsar">
                        <Link :href="route('curriculum.competencies.edit', competency.id)"><Edit class="mr-2 h-4 w-4" />Modify Pillar</Link>
                    </Button>
                    <Button class="bg-violet-600 hover:bg-violet-700 h-10 font-pulsar shadow-lg border-0" as-child>
                         <Link :href="route('curriculum.competencies.create')">Extend Matrix</Link>
                    </Button>
                </div>
            </div>

            <!-- Stats section -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                 <div class="rounded-2xl border bg-card p-5 shadow-sm border-l-4 border-l-violet-500 flex items-center gap-4 group hover:shadow-md transition-all">
                    <div class="rounded-xl bg-violet-500/10 p-3 group-hover:bg-violet-500 group-hover:text-white transition-all"><Sparkles class="h-5 w-5 text-violet-600 group-hover:text-white" /></div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Pillar Category</p>
                        <p class="text-xl font-black text-slate-900 uppercase tracking-tight">{{ competency.category || 'CORE' }}</p>
                    </div>
                </div>
                <div class="rounded-2xl border bg-card p-5 shadow-sm border-l-4 border-l-emerald-500 flex items-center gap-4 group hover:shadow-md transition-all">
                    <div class="rounded-xl bg-emerald-500/10 p-3 group-hover:bg-emerald-500 group-hover:text-white transition-all"><CheckCircle2 class="h-5 w-5 text-emerald-600 group-hover:text-white" /></div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Logic Pulse</p>
                        <p class="text-xl font-black text-emerald-600">{{ competency.is_active ? 'ACTIVE' : 'DORMANT' }}</p>
                    </div>
                </div>
                <div class="rounded-2xl border bg-card p-5 shadow-sm border-l-4 border-l-blue-500 flex items-center gap-4 group hover:shadow-md transition-all">
                    <div class="rounded-xl bg-blue-500/10 p-3 group-hover:bg-blue-500 group-hover:text-white transition-all"><Target class="h-5 w-5 text-blue-600 group-hover:text-white" /></div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Total Indicators</p>
                        <p class="text-xl font-black text-slate-900">{{ competency.indicators.length }} Nodes</p>
                    </div>
                </div>
                <div class="rounded-2xl border bg-card p-5 shadow-sm border-l-4 border-l-indigo-500 flex items-center gap-4 group hover:shadow-md transition-all">
                    <div class="rounded-xl bg-indigo-500/10 p-3 group-hover:bg-indigo-500 group-hover:text-white transition-all"><ShieldCheck class="h-5 w-5 text-indigo-600 group-hover:text-white" /></div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Sequence Hub</p>
                        <p class="text-xl font-black text-indigo-600 uppercase tracking-tighter">ORDER #{{ competency.display_order }}</p>
                    </div>
                </div>
            </div>

            <!-- Detailed Analysis & Indicators -->
            <div class="grid gap-6 lg:grid-cols-12">
                <!-- Left Column: Pillar Specification -->
                <div class="lg:col-span-12 flex flex-col gap-6">
                    <div class="rounded-[2.5rem] border bg-card p-10 shadow-sm border-t-[12px] border-t-violet-500 relative overflow-hidden">
                        <div class="absolute -right-20 -top-20 opacity-[0.03] rotate-12">
                             <BrainCircuit class="h-96 w-96 text-slate-950" />
                        </div>
                        <div class="relative z-10">
                            <h2 class="text-xs font-black text-slate-900 uppercase tracking-[0.3em] mb-8 flex items-center gap-3">
                                <Network class="h-4 w-4 text-violet-600" /> Pillar Specification
                            </h2>
                            <div class="max-w-4xl">
                                <p class="text-2xl font-medium text-slate-900 leading-relaxed italic opacity-90 border-l-[6px] border-l-violet-200 pl-8 mb-10">
                                    {{ competency.description || 'Core competency pillar guiding integrated learner development and cross-cutting academic behavioral indicators within the CBC framework.' }}
                                </p>
                                <div class="grid sm:grid-cols-3 gap-6">
                                     <div class="p-6 rounded-3xl bg-slate-50 border shadow-inner transition-all hover:bg-white hover:shadow-md group">
                                          <h4 class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 group-hover:text-violet-500">Registry Hash</h4>
                                          <p class="text-base font-black text-slate-900 uppercase">{{ competency.code }}</p>
                                     </div>
                                     <div class="p-6 rounded-3xl bg-slate-50 border shadow-inner transition-all hover:bg-white hover:shadow-md group">
                                          <h4 class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 group-hover:text-violet-500">Academic Target</h4>
                                          <p class="text-base font-black text-slate-900 uppercase">{{ competency.category || 'UNIVERSAL' }}</p>
                                     </div>
                                     <div class="p-6 rounded-3xl bg-slate-50 border shadow-inner transition-all hover:bg-white hover:shadow-md group">
                                          <h4 class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 group-hover:text-violet-500">Sequence Index</h4>
                                          <p class="text-base font-black text-slate-900 uppercase">POSITION #{{ competency.display_order }}</p>
                                     </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Indicators Matrix -->
                    <div class="rounded-[2.5rem] border bg-card shadow-sm overflow-hidden border-t-4 border-t-emerald-500 shrink-0">
                        <div class="bg-slate-50 px-10 py-6 border-b flex items-center justify-between">
                             <h2 class="text-xs font-black text-slate-900 uppercase tracking-[0.2em] flex items-center gap-3">
                                  <Target class="h-4 w-4 text-emerald-600" /> Performance Indicators Matrix
                             </h2>
                             <Badge variant="outline" class="text-[10px] font-black border-slate-200 text-slate-400 uppercase tracking-widest">{{ competency.indicators.length }} Indicators</Badge>
                        </div>
                        
                        <div class="divide-y relative">
                             <div v-if="competency.indicators.length === 0" class="p-20 text-center">
                                 <div class="h-20 w-20 rounded-full bg-slate-50 flex items-center justify-center mx-auto mb-6 border border-slate-100">
                                      <Target class="h-10 w-10 text-slate-300" />
                                 </div>
                                 <h3 class="text-base font-bold text-slate-900 uppercase mb-2 tracking-tight">Matrix is currently empty</h3>
                                 <p class="text-sm text-slate-400 italic font-medium max-w-md mx-auto">No performance indicators established for this pillar. Initialize nodes to begin behavioral mapping.</p>
                                 <Button class="mt-8 bg-slate-900 hover:bg-black font-pulsar h-10 px-6 rounded-xl text-xs uppercase tracking-widest shadow-xl border-0">Initialize Indicators</Button>
                            </div>

                            <div v-for="(node, index) in competency.indicators" :key="node.id" class="px-10 py-8 flex items-center justify-between group hover:bg-emerald-50/30 transition-all duration-300 border-l-[6px] border-l-transparent hover:border-l-emerald-500">
                                <div class="flex items-center gap-6 flex-1">
                                     <div class="h-12 w-12 rounded-2xl bg-white flex items-center justify-center font-black text-slate-400 group-hover:bg-emerald-600 group-hover:text-white transition-all text-[12px] shadow-sm border border-slate-200 uppercase shrink-0">
                                          {{ index + 1 }}
                                     </div>
                                     <div class="flex-1 max-w-3xl">
                                         <div class="text-lg font-bold text-slate-900 group-hover:text-emerald-700 transition-colors tracking-tight leading-relaxed">{{ node.indicator }}</div>
                                         <div class="text-[10px] font-black text-slate-400 uppercase tracking-[0.1em] italic mt-1.5 flex items-center gap-2">
                                              <Network class="h-3 w-3" /> Indicator Logic Node #{{ node.id }}
                                         </div>
                                     </div>
                                </div>
                                <div class="flex items-center gap-8 pl-8">
                                    <div class="flex flex-col items-end gap-1">
                                         <span class="text-[8px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Status</span>
                                         <Badge :class="node.is_active ? 'bg-emerald-500 text-white shadow-[0_0_10px_rgba(16,185,129,0.4)]' : 'bg-slate-300 text-white'" class="rounded-full px-3 py-1 text-[9px] font-black uppercase tracking-widest border-0">
                                             {{ node.is_active ? 'PULSE ACTIVE' : 'DORMANT' }}
                                         </Badge>
                                    </div>
                                    <Button variant="ghost" size="icon" class="h-10 w-10 text-slate-300 group-hover:text-emerald-600 hover:bg-white hover:shadow-sm" as-child>
                                         <Link :href="route('curriculum.competencies.edit', competency.id)"><ChevronRight class="h-6 w-6" /></Link>
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Global Matrix Callout -->
            <div class="mt-6 p-8 rounded-[3rem] bg-slate-950 text-white flex flex-col md:flex-row items-center justify-between gap-8 shadow-2xl border-[12px] border-slate-900 relative overflow-hidden group">
                <div class="absolute -right-20 -top-20 opacity-10 rotate-45 transform group-hover:scale-110 transition-transform duration-700">
                     <BrainCircuit class="h-64 w-64" />
                </div>
                <div class="relative z-10 flex items-center gap-6">
                    <div class="h-14 w-14 rounded-2xl bg-white/10 flex items-center justify-center border border-white/20 shadow-inner">
                         <ShieldCheck class="h-7 w-7 text-violet-400" />
                    </div>
                    <div>
                        <h3 class="font-black text-lg tracking-tighter text-white uppercase">Competency Pillar Integrity</h3>
                        <p class="text-slate-400 text-sm mt-1 tracking-tight font-medium opacity-80">This logic node and its indicators are synchronized with the global CBC performance matrix for the 2026 Academic Cycle.</p>
                    </div>
                </div>
                 <div class="flex gap-3 relative z-10">
                      <Button variant="outline" size="sm" class="bg-white/5 border-white/10 text-white hover:bg-white/20 h-10 font-bold px-6 rounded-2xl text-[10px] uppercase tracking-widest cursor-default">Matrix: Integrated</Button>
                 </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.font-black { font-weight: 950; }
</style>
