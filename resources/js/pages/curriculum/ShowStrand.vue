<script setup lang="ts">
/* global route */
import { Head, Link } from '@inertiajs/vue3';
import { Layers, ArrowLeft, Edit, GraduationCap, CheckCircle2, ShieldCheck, Target, Network } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{ 
    strand: { 
        id: number; 
        name: string; 
        code: string; 
        description: string | null; 
        display_order: number; 
        is_active: boolean; 
        subject_name: string; 
        grade_name: string 
    } 
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' }, 
    { title: 'Curriculum', href: '/curriculum' }, 
    { title: 'Strands Registry', href: route('curriculum.strands') }, 
    { title: props.strand.name, href: route('curriculum.strands.show', props.strand.id) }
];
</script>

<template>
    <Head :title="strand.name" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 font-pulsar mt-2 max-w-[1400px] mx-auto animate-in fade-in duration-500">
            <!-- Header section -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="outline" size="icon" as-child class="h-10 w-10 shrink-0 border-slate-200">
                        <Link :href="route('curriculum.strands')"><ArrowLeft class="h-4 w-4" /></Link>
                    </Button>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-500/10 shrink-0">
                        <Layers class="h-6 w-6 text-violet-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">{{ strand.name }}</h1>
                        <p class="text-muted-foreground font-medium uppercase text-[10px] tracking-widest">{{ strand.code }} • {{ strand.subject_name }} • {{ strand.grade_name }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" as-child class="h-10 border-slate-200 font-pulsar">
                        <Link :href="route('curriculum.strands.edit', strand.id)"><Edit class="mr-2 h-4 w-4" />Modify Topic Metadata</Link>
                    </Button>
                    <Button class="bg-violet-600 hover:bg-violet-700 h-10 font-pulsar shadow-lg border-0" as-child>
                         <Link :href="route('curriculum.competencies.create', { strand_id: strand.id })">Initialize Indicator</Link>
                    </Button>
                </div>
            </div>

            <!-- Stats section -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                 <div class="rounded-2xl border bg-card p-5 shadow-sm border-l-4 border-l-violet-500 flex items-center gap-4 group hover:shadow-md transition-all">
                    <div class="rounded-xl bg-violet-500/10 p-3 group-hover:bg-violet-500 group-hover:text-white transition-all"><Target class="h-5 w-5 text-violet-600 group-hover:text-white" /></div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Grade Alignment</p>
                        <p class="text-xl font-black text-slate-900 uppercase tracking-tight">{{ strand.grade_name }}</p>
                    </div>
                </div>
                <div class="rounded-2xl border bg-card p-5 shadow-sm border-l-4 border-l-emerald-500 flex items-center gap-4 group hover:shadow-md transition-all">
                    <div class="rounded-xl bg-emerald-500/10 p-3 group-hover:bg-emerald-500 group-hover:text-white transition-all"><CheckCircle2 class="h-5 w-5 text-emerald-600 group-hover:text-white" /></div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Logic Status</p>
                        <p class="text-xl font-black text-emerald-600">{{ strand.is_active ? 'ACTIVE PULSE' : 'DORMANT' }}</p>
                    </div>
                </div>
                <div class="rounded-2xl border bg-card p-5 shadow-sm border-l-4 border-l-blue-500 flex items-center gap-4 group hover:shadow-md transition-all">
                    <div class="rounded-xl bg-blue-500/10 p-3 group-hover:bg-blue-500 group-hover:text-white transition-all"><GraduationCap class="h-5 w-5 text-blue-600 group-hover:text-white" /></div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Academic Host</p>
                        <p class="text-xl font-black text-slate-900 uppercase tracking-tighter truncate max-w-[120px]">{{ strand.subject_name }}</p>
                    </div>
                </div>
                <div class="rounded-2xl border bg-card p-5 shadow-sm border-l-4 border-l-indigo-500 flex items-center gap-4 group hover:shadow-md transition-all">
                    <div class="rounded-xl bg-indigo-500/10 p-3 group-hover:bg-indigo-500 group-hover:text-white transition-all"><ShieldCheck class="h-5 w-5 text-indigo-600 group-hover:text-white" /></div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Matrix Hash</p>
                        <p class="text-xl font-black text-indigo-600 uppercase tracking-tighter">SECURED</p>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="grid gap-6 lg:grid-cols-12">
                 <div class="lg:col-span-8">
                      <div class="rounded-[2rem] border bg-card p-10 shadow-sm border-t-8 border-t-violet-500 min-h-[400px]">
                           <h2 class="text-xs font-black text-slate-900 uppercase tracking-[0.2em] mb-8 flex items-center gap-3">
                                <Network class="h-4 w-4 text-violet-600" /> Instructional Specification
                           </h2>
                           <div class="space-y-8">
                                <div>
                                     <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Topic Objective</h3>
                                     <p class="text-lg font-medium text-slate-900 leading-relaxed italic opacity-90 border-l-4 border-l-violet-200 pl-6">
                                          {{ strand.description || 'This curriculum strand specifies core learning objectives and performance indicators for the specified academic cycle.' }}
                                     </p>
                                </div>
                                <div class="grid sm:grid-cols-2 gap-8 pt-6">
                                     <div class="p-6 rounded-2xl bg-slate-50 border shadow-inner">
                                          <h4 class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">System Node</h4>
                                          <p class="text-sm font-bold text-slate-900">{{ strand.code }}</p>
                                     </div>
                                     <div class="p-6 rounded-2xl bg-slate-50 border shadow-inner">
                                          <h4 class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Sequence Position</h4>
                                          <p class="text-sm font-bold text-slate-900">#{{ strand.display_order }}</p>
                                     </div>
                                </div>
                           </div>
                      </div>
                 </div>
                 
                 <div class="lg:col-span-4 flex flex-col gap-6">
                      <div class="rounded-[2rem] bg-slate-950 p-10 shadow-2xl text-white relative overflow-hidden group border-[12px] border-slate-900 flex-1">
                          <div class="absolute -right-8 -bottom-8 opacity-10 group-hover:scale-110 group-hover:-rotate-12 transition-all duration-700 transform">
                               <Layers class="h-48 w-48" />
                          </div>
                          <div class="relative z-10 space-y-8">
                               <h3 class="text-xl font-black uppercase tracking-tighter flex items-center gap-3">
                                   <Target class="h-6 w-6 text-violet-400" /> Mapping Intelligence
                               </h3>
                               <div class="space-y-6">
                                    <div class="pb-6 border-b border-white/10">
                                         <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Academic Host</p>
                                         <p class="text-lg font-bold text-white tracking-tight">{{ strand.subject_name }}</p>
                                    </div>
                                    <div class="pb-6 border-b border-white/10">
                                         <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Grade Hub</p>
                                         <p class="text-lg font-bold text-white tracking-tight">{{ strand.grade_name }}</p>
                                    </div>
                                    <div>
                                         <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Validation Node</p>
                                         <Badge class="bg-violet-500/20 text-violet-400 border border-violet-500/30 text-[9px] font-black uppercase tracking-widest">Logic: Verified</Badge>
                                    </div>
                               </div>
                          </div>
                      </div>
                 </div>
            </div>
            
            <!-- Matrix Synced Callout -->
            <div class="mt-2 p-6 rounded-3xl bg-slate-900 text-white flex flex-col md:flex-row items-center justify-between gap-6 shadow-2xl border border-slate-800">
                <div class="flex items-center gap-4">
                    <div class="h-10 w-10 rounded-xl bg-white/10 flex items-center justify-center">
                         <ShieldCheck class="h-5 w-5 text-violet-400" />
                    </div>
                    <div>
                        <h3 class="font-bold text-sm tracking-tight text-white/90 uppercase">Instructional Mapping Finalized</h3>
                        <p class="text-slate-400 text-[10px] mt-0.5 tracking-wider font-semibold">Real-time instructional mapping and performance matrix synchronization across the 2026 Academic Cluster.</p>
                    </div>
                </div>
                 <div class="flex gap-2">
                      <Button variant="outline" size="sm" class="bg-white/5 border-white/10 text-white hover:bg-white/20 h-9 font-bold px-4 rounded-xl text-xs uppercase cursor-default">Status: Validated</Button>
                 </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.font-black { font-weight: 950; }
</style>
