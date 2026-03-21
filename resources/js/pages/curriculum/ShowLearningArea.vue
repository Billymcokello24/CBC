<script setup lang="ts">
/* global route */
import { Head, Link } from '@inertiajs/vue3';
import { BookOpenCheck, ArrowLeft, Edit, GraduationCap, Layers, CheckCircle2, ShieldCheck, ChevronRight } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    learningArea: {
        id: number;
        name: string;
        code: string;
        description: string | null;
        category: string | null;
        display_order: number;
        is_active: boolean;
        subjects: Array<{
            id: number;
            name: string;
            code: string;
            subject_type: string;
            is_active: boolean;
        }>;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Learning Areas', href: '/curriculum/learning-areas' },
    { title: props.learningArea.name, href: route('curriculum.learning-areas.show', props.learningArea.id) },
];
</script>

<template>
    <Head :title="learningArea.name" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 font-pulsar mt-2 max-w-[1400px] mx-auto animate-in fade-in duration-500">
            <!-- Header section -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="outline" size="icon" as-child class="h-10 w-10 shrink-0 border-slate-200">
                        <Link :href="route('curriculum.learning-areas')"><ArrowLeft class="h-4 w-4" /></Link>
                    </Button>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/10 shrink-0">
                        <BookOpenCheck class="h-6 w-6 text-emerald-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">{{ learningArea.name }}</h1>
                        <p class="text-muted-foreground font-medium uppercase text-[10px] tracking-widest">{{ learningArea.code }} • {{ learningArea.category || 'General Domain' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" as-child class="h-10 border-slate-200 font-pulsar">
                        <Link :href="route('curriculum.learning-areas.edit', learningArea.id)"><Edit class="mr-2 h-4 w-4" />Modify Configuration</Link>
                    </Button>
                    <Button class="bg-violet-600 hover:bg-violet-700 h-10 font-pulsar shadow-lg border-0" as-child>
                         <Link :href="route('curriculum.subjects.create', { area_id: learningArea.id })">Initialize Subject</Link>
                    </Button>
                </div>
            </div>

            <!-- Stats Section -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                 <div class="rounded-2xl border bg-card p-5 shadow-sm border-l-4 border-l-emerald-500 flex items-center gap-4 group hover:shadow-md transition-all">
                    <div class="rounded-xl bg-emerald-500/10 p-3 group-hover:bg-emerald-500 group-hover:text-white transition-all"><CheckCircle2 class="h-5 w-5 text-emerald-600 group-hover:text-white" /></div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Operational Status</p>
                        <p class="text-xl font-black" :class="learningArea.is_active ? 'text-emerald-600' : 'text-slate-400'">{{ learningArea.is_active ? 'ACTIVE PULSE' : 'DORMANT' }}</p>
                    </div>
                </div>
                <div class="rounded-2xl border bg-card p-5 shadow-sm border-l-4 border-l-violet-500 flex items-center gap-4 group hover:shadow-md transition-all">
                    <div class="rounded-xl bg-violet-500/10 p-3 group-hover:bg-violet-500 group-hover:text-white transition-all"><GraduationCap class="h-5 w-5 text-violet-600 group-hover:text-white" /></div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Subject Inventory</p>
                        <p class="text-xl font-black text-slate-900">{{ learningArea.subjects.length }} Nodes</p>
                    </div>
                </div>
                <div class="rounded-2xl border bg-card p-5 shadow-sm border-l-4 border-l-blue-500 flex items-center gap-4 group hover:shadow-md transition-all">
                    <div class="rounded-xl bg-blue-500/10 p-3 group-hover:bg-blue-500 group-hover:text-white transition-all"><Layers class="h-5 w-5 text-blue-600 group-hover:text-white" /></div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Logic Order</p>
                        <p class="text-xl font-black text-slate-900">POSITION #{{ learningArea.display_order }}</p>
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

            <!-- Details and Subjects -->
            <div class="grid gap-6 lg:grid-cols-12">
                <!-- Left Column: Details -->
                <div class="lg:col-span-4 flex flex-col gap-6">
                    <div class="rounded-[2rem] border bg-card p-8 shadow-sm space-y-6">
                        <h2 class="text-sm font-black text-slate-900 uppercase tracking-widest flex items-center gap-2">
                             Domain Description
                        </h2>
                        <p class="text-sm text-muted-foreground font-medium leading-relaxed italic opacity-80">
                            {{ learningArea.description || 'No descriptive metadata provided for this academic domain in the curriculum registry.' }}
                        </p>
                    </div>

                    <div class="rounded-[2rem] border bg-card p-8 shadow-sm space-y-6 border-t-8 border-t-violet-500">
                         <h2 class="text-sm font-black text-slate-900 uppercase tracking-widest">Metadata Summary</h2>
                         <div class="space-y-4">
                              <div class="flex items-center justify-between py-2 border-b border-slate-50">
                                   <span class="text-[10px] font-bold text-slate-400 uppercase uppercase">System ID</span>
                                   <span class="text-[10px] font-black text-slate-900">#{{ learningArea.id }}</span>
                              </div>
                              <div class="flex items-center justify-between py-2 border-b border-slate-50">
                                   <span class="text-[10px] font-bold text-slate-400 uppercase uppercase">Unique Code</span>
                                   <span class="text-[10px] font-black text-slate-900">{{ learningArea.code }}</span>
                              </div>
                              <div class="flex items-center justify-between py-2 border-b border-slate-50">
                                   <span class="text-[10px] font-bold text-slate-400 uppercase uppercase">Academic Group</span>
                                   <span class="text-[10px] font-black text-violet-600 uppercase">{{ learningArea.category || 'Standard' }}</span>
                              </div>
                         </div>
                    </div>
                </div>

                <!-- Right Column: Table -->
                <div class="lg:col-span-8 flex flex-col gap-6">
                    <div class="rounded-3xl border bg-card shadow-sm overflow-hidden border-t-4 border-t-emerald-500">
                        <div class="bg-slate-50 px-6 py-4 border-b flex items-center justify-between">
                             <h2 class="text-xs font-black text-slate-900 uppercase tracking-widest flex items-center gap-2">
                                  Academic Subjects Under This Domain
                             </h2>
                             <Badge variant="outline" class="text-[10px] font-black border-slate-200 text-slate-400 uppercase">{{ learningArea.subjects.length }} Total</Badge>
                        </div>
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-white border-b font-pulsar">
                                    <th class="px-6 py-4 text-[10px] font-bold uppercase text-slate-500 tracking-widest">Subject Matrix</th>
                                    <th class="px-6 py-4 text-[10px] font-bold uppercase text-slate-500 tracking-widest">Instructional Model</th>
                                    <th class="px-6 py-4 text-center text-[10px] font-bold uppercase text-slate-500 tracking-widest">Status</th>
                                    <th class="px-6 py-4 text-right text-[10px] font-bold uppercase text-slate-500 tracking-widest">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y text-sm">
                                <tr v-for="subject in learningArea.subjects" :key="subject.id" class="group hover:bg-slate-50/70 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="h-9 w-9 rounded-lg bg-emerald-600/10 flex items-center justify-center font-black text-emerald-700 group-hover:bg-emerald-600 group-hover:text-white transition-all text-xs border border-emerald-100 uppercase">
                                                 {{ subject.code.substring(0, 2) }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-slate-900 group-hover:text-emerald-700 transition-colors text-sm">{{ subject.name }}</div>
                                                <div class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">{{ subject.code }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <Badge variant="outline" class="border-0 bg-slate-100 text-slate-600 text-[10px] font-black uppercase tracking-tighter px-2 py-0.5 rounded-lg">{{ subject.subject_type }} Model</Badge>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <Badge :class="subject.is_active ? 'bg-emerald-500 text-white' : 'bg-slate-300 text-white'" class="rounded-full px-2 py-0.5 text-[8px] font-black uppercase tracking-widest border-0">
                                            {{ subject.is_active ? 'Active' : 'Dormant' }}
                                        </Badge>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <Button variant="ghost" size="icon" class="h-8 w-8 text-slate-400 hover:text-emerald-600" as-child>
                                             <Link :href="route('curriculum.subjects.show', subject.id)"><ChevronRight class="h-4 w-4" /></Link>
                                        </Button>
                                    </td>
                                </tr>
                                <tr v-if="learningArea.subjects.length === 0">
                                    <td colspan="4" class="px-6 py-12 text-center text-muted-foreground italic font-medium">
                                        No instructional nodes established under this domain.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.font-black { font-weight: 950; }
</style>
