<script setup lang="ts">
/* global route */
import { Head, Link } from '@inertiajs/vue3';
import { BookOpen, ArrowLeft, Edit, GraduationCap, Layers, CheckCircle2, ShieldCheck, ChevronRight, BadgeCheck, Network } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{ 
    subject: { 
        id: number; 
        learning_area: string | null; 
        department: { id: number; name: string; code: string; head_of_department: string | null } | null;
        name: string; 
        code: string; 
        description: string | null; 
        subject_type: string; 
        is_examinable: boolean; 
        display_order: number; 
        is_active: boolean; 
        strands: Array<{ id: number; name: string; code: string; is_active: boolean }> 
    } 
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' }, 
    { title: 'Curriculum', href: '/curriculum' }, 
    { title: 'Subjects Registry', href: '/curriculum/subjects' }, 
    { title: props.subject.name, href: `/curriculum/subjects/${props.subject.id}` }
];
</script>

<template>
    <Head :title="subject.name" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 font-pulsar mt-2 max-w-[1400px] mx-auto animate-in fade-in duration-500">
            <!-- Header section -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="outline" size="icon" as-child class="h-10 w-10 shrink-0 border-slate-200">
                        <Link href="/curriculum/subjects"><ArrowLeft class="h-4 w-4" /></Link>
                    </Button>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-500/10 shrink-0">
                        <BookOpen class="h-6 w-6 text-violet-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">{{ subject.name }}</h1>
                        <p class="text-muted-foreground font-medium uppercase text-[10px] tracking-widest">{{ subject.code }} • {{ subject.learning_area || 'Universal Area' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" as-child class="h-10 border-slate-200 font-pulsar">
                        <Link :href="`/curriculum/subjects/${subject.id}/edit`"><Edit class="mr-2 h-4 w-4" />Modify Configuration</Link>
                    </Button>
                    <Button class="bg-violet-600 hover:bg-violet-700 h-10 font-pulsar shadow-lg border-0" as-child>
                         <Link :href="`/curriculum/subjects/${subject.id}/allocate`">Grade Allocation</Link>
                    </Button>
                </div>
            </div>

            <!-- Stats section -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                 <div class="rounded-2xl border bg-card p-5 shadow-sm border-l-4 border-l-violet-500 flex items-center gap-4 group hover:shadow-md transition-all">
                    <div class="rounded-xl bg-violet-500/10 p-3 group-hover:bg-violet-500 group-hover:text-white transition-all"><BookOpen class="h-5 w-5 text-violet-600 group-hover:text-white" /></div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Instructional Nodes</p>
                        <p class="text-xl font-black text-slate-900">{{ subject.strands.length }} Strands</p>
                    </div>
                </div>
                <div class="rounded-2xl border bg-card p-5 shadow-sm border-l-4 border-l-emerald-500 flex items-center gap-4 group hover:shadow-md transition-all">
                    <div class="rounded-xl bg-emerald-500/10 p-3 group-hover:bg-emerald-500 group-hover:text-white transition-all"><BadgeCheck class="h-5 w-5 text-emerald-600 group-hover:text-white" /></div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Operational Pulse</p>
                        <p class="text-xl font-black text-emerald-600">{{ subject.is_active ? 'ACTIVE' : 'DORMANT' }}</p>
                    </div>
                </div>
                <div class="rounded-2xl border bg-card p-5 shadow-sm border-l-4 border-l-blue-500 flex items-center gap-4 group hover:shadow-md transition-all">
                    <div class="rounded-xl bg-blue-500/10 p-3 group-hover:bg-blue-500 group-hover:text-white transition-all"><Layers class="h-5 w-5 text-blue-600 group-hover:text-white" /></div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Subject Strategy</p>
                        <p class="text-xl font-black text-slate-900 uppercase tracking-tighter">{{ subject.subject_type }}</p>
                    </div>
                </div>
                <div class="rounded-2xl border bg-card p-5 shadow-sm border-l-4 border-l-indigo-500 flex items-center gap-4 group hover:shadow-md transition-all">
                    <div class="rounded-xl bg-indigo-500/10 p-3 group-hover:bg-indigo-500 group-hover:text-white transition-all"><ShieldCheck class="h-5 w-5 text-indigo-600 group-hover:text-white" /></div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Logic Consistency</p>
                        <p class="text-xl font-black text-indigo-600 uppercase tracking-tighter">SECURED</p>
                    </div>
                </div>
            </div>

            <!-- Subject Details & Strands Matrix -->
            <div class="grid gap-6 lg:grid-cols-12">
                <!-- Left Column: Knowledge Graph Details -->
                <div class="lg:col-span-4 flex flex-col gap-6">
                    <div class="rounded-[2rem] border bg-card p-8 shadow-sm space-y-6">
                        <h2 class="text-sm font-black text-slate-900 uppercase tracking-widest flex items-center gap-2">
                             Instructional Model
                        </h2>
                        <div class="p-6 rounded-2xl bg-slate-50 border shadow-inner">
                             <div class="text-lg font-bold text-indigo-600 mb-1">{{ subject.department?.name || 'Unassigned Department' }}</div>
                             <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                  {{ subject.department?.code || 'FACULTY_NODE_MISSING' }}
                                  <span v-if="subject.department?.head_of_department" class="text-slate-900 ml-2 border-l pl-2 text-indigo-600">HOD: {{ subject.department.head_of_department }}</span>
                             </div>
                        </div>
                        <div class="space-y-4 pt-2">
                             <p class="text-sm text-muted-foreground font-medium italic leading-relaxed opacity-80">
                                 {{ subject.description || 'Core academic subject mapping instructional delivery standards to specific grade-level learning milestones.' }}
                             </p>
                        </div>
                    </div>

                    <div class="rounded-[2.5rem] bg-slate-950 p-10 shadow-2xl text-white relative overflow-hidden group border-[12px] border-slate-900">
                        <div class="absolute -right-8 -bottom-8 opacity-10 group-hover:scale-110 group-hover:-rotate-12 transition-all duration-700 transform">
                             <Network class="h-48 w-48" />
                        </div>
                        <div class="relative z-10 space-y-6">
                            <h3 class="text-xl font-black uppercase tracking-tighter flex items-center gap-3">
                                <BadgeCheck class="h-6 w-6 text-violet-400" /> Matrix Specs
                            </h3>
                            <div class="grid gap-4">
                                 <div class="flex items-center justify-between p-4 rounded-2xl bg-white/5 border border-white/10 shadow-inner">
                                      <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Examinable</span>
                                      <Badge :class="subject.is_examinable ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30' : 'bg-slate-500/20 text-slate-400 border-slate-500/30'" class="text-[9px] font-black uppercase tracking-widest border">
                                           {{ subject.is_examinable ? 'VERIFIED' : 'CORE_ONLY' }}
                                      </Badge>
                                 </div>
                                 <div class="flex items-center justify-between p-4 rounded-2xl bg-white/5 border border-white/10 shadow-inner">
                                      <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Sequence Order</span>
                                      <span class="text-xl font-black text-white">#{{ subject.display_order }}</span>
                                 </div>
                                 <div class="flex items-center justify-between p-4 rounded-2xl bg-white/5 border border-white/10 shadow-inner">
                                      <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">System Hash</span>
                                      <span class="text-[9px] font-black text-slate-500 uppercase italic">NODE_{{ subject.id }}</span>
                                 </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Strands & Topics Matrix -->
                <div class="lg:col-span-8 flex flex-col gap-6">
                    <div class="rounded-3xl border bg-card shadow-sm overflow-hidden border-t-4 border-t-violet-500 shrink-0">
                        <div class="bg-slate-50 px-8 py-5 border-b flex items-center justify-between">
                             <h2 class="text-xs font-black text-slate-900 uppercase tracking-[0.2em] flex items-center gap-3">
                                  <Layers class="h-4 w-4 text-violet-600" /> Topic Strands Matrix
                             </h2>
                             <Badge variant="outline" class="text-[10px] font-black border-slate-200 text-slate-400 uppercase tracking-widest">{{ subject.strands.length }} Total</Badge>
                        </div>
                        
                        <div class="divide-y">
                            <div v-if="subject.strands.length === 0" class="p-16 text-center">
                                 <div class="h-16 w-16 rounded-full bg-slate-50 flex items-center justify-center mx-auto mb-4 border border-slate-100">
                                      <Layers class="h-8 w-8 text-slate-300" />
                                 </div>
                                 <h3 class="text-sm font-bold text-slate-900 uppercase mb-1">No instructional strands found</h3>
                                 <p class="text-xs text-slate-400 italic">Initialize topic strands to begin instructional mapping.</p>
                                 <Button variant="outline" class="mt-6 font-pulsar h-9 text-[10px] uppercase tracking-widest border-slate-200" as-child>
                                       <Link :href="`/curriculum/strands/create?subject_id=${subject.id}`">Initialize first strand</Link>
                                 </Button>
                            </div>

                            <div v-for="strand in subject.strands" :key="strand.id" class="px-8 py-6 flex items-center justify-between group hover:bg-slate-50/70 transition-all duration-300 border-l-4 border-l-transparent hover:border-l-violet-500">
                                <div class="flex items-center gap-4">
                                     <div class="h-10 w-10 rounded-xl bg-slate-100 flex items-center justify-center font-black text-slate-400 group-hover:bg-violet-600 group-hover:text-white transition-all text-[11px] shadow-inner border border-slate-200 uppercase">
                                          {{ strand.code.substring(0, 2) }}
                                     </div>
                                     <div>
                                         <div class="font-bold text-slate-900 group-hover:text-violet-700 transition-colors text-base tracking-tight leading-none mb-1.5">{{ strand.name }}</div>
                                         <div class="text-[10px] font-black text-slate-400 uppercase tracking-[0.1em] italic">{{ strand.code }} • Topic Intelligence</div>
                                     </div>
                                </div>
                                <div class="flex items-center gap-6">
                                    <Badge :class="strand.is_active ? 'bg-emerald-500 text-white shadow-[0_0_8px_rgba(16,185,129,0.3)]' : 'bg-slate-300 text-white'" class="rounded-full px-2.5 py-0.5 text-[8px] font-black uppercase tracking-widest border-0">
                                        {{ strand.is_active ? 'Active' : 'Dormant' }}
                                    </Badge>
                                    <Button variant="ghost" size="icon" class="h-9 w-9 text-slate-300 group-hover:text-violet-600 group-hover:bg-white group-hover:shadow-sm" as-child>
                                         <Link :href="`/curriculum/strands/${strand.id}`"><ChevronRight class="h-5 w-5" /></Link>
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Sync -->
            <div class="mt-2 p-5 rounded-3xl bg-slate-900 text-white flex flex-col md:flex-row items-center justify-between gap-6 shadow-2xl border border-slate-800 transition-all hover:bg-slate-950 group">
                <div class="flex items-center gap-4">
                    <div class="h-10 w-10 rounded-xl bg-white/10 flex items-center justify-center group-hover:bg-violet-600 group-hover:text-white transition-all">
                         <Network class="h-5 w-5 text-violet-400 group-hover:text-white" />
                    </div>
                    <div>
                        <h3 class="font-bold text-sm tracking-tight text-white/90 uppercase">Academic Node Integrity: SECURE</h3>
                        <p class="text-slate-400 text-[10px] mt-0.5 tracking-wider font-semibold">Real-time instructional mapping and performance matrix synchronization across the 2026 Academic Cluster.</p>
                    </div>
                </div>
                 <div class="flex gap-2">
                      <Button variant="outline" size="sm" class="bg-white/5 border-white/10 text-white hover:bg-white/20 h-9 font-bold px-4 rounded-xl text-xs uppercase cursor-default">Status: Optimized</Button>
                 </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.font-black { font-weight: 950; }
</style>
