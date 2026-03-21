<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ClipboardList, Search, Filter, FileText, CheckCircle2, AlertCircle, LayoutGrid, MoreHorizontal, Eye, BookOpen, Clock, Zap, ArrowRight, Target } from 'lucide-vue-next';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    assessments: {
        data: Array<any>;
        links: Array<any>;
        meta: any;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Assessments Hub', href: '/assessments' },
    { title: 'Grading Terminal', href: '/assessments/grading' },
];

const searchQuery = ref('');

const getStatusColor = (status: string) => {
    switch (status) {
        case 'published': return 'bg-blue-600 text-white shadow-[0_0_8px_rgba(37,99,235,0.4)]';
        case 'in_progress': return 'bg-amber-500 text-white';
        case 'completed': return 'bg-emerald-600 text-white shadow-[0_0_8px_rgba(16,185,129,0.4)]';
        default: return 'bg-slate-400 text-white';
    }
};

</script>

<template>
    <Head title="Grading Terminal" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 font-pulsar mt-2 max-w-[1400px] mx-auto">
            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-500/10 shadow-inner border border-purple-100">
                        <FileText class="h-6 w-6 text-purple-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Grading Terminal</h1>
                        <p class="text-muted-foreground font-medium">Capture student performance metrics and feedback across all testing nodes</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                     <Button variant="outline" class="font-pulsar h-10 border-slate-200" as-child>
                        <Link href="/assessments"><ClipboardList class="mr-2 h-4 w-4" />All Assessments</Link>
                     </Button>
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="rounded-2xl border bg-card p-4 shadow-sm flex items-center gap-4 group hover:shadow-md transition-all border-l-4 border-l-purple-500">
                    <div class="rounded-xl bg-purple-500/10 p-2.5 text-purple-600"><Clock class="h-5 w-5" /></div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Awaiting Marks</p>
                        <p class="text-lg font-black text-slate-900">{{ assessments.data.length }} Active Tests</p>
                    </div>
                </div>
                <div class="rounded-2xl border bg-card p-4 shadow-sm flex items-center gap-4 group hover:shadow-md transition-all border-l-4 border-l-blue-500">
                    <div class="rounded-xl bg-blue-500/10 p-2.5 text-blue-600"><Target class="h-5 w-5" /></div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Target Sample</p>
                        <p class="text-lg font-black text-slate-900">100% Student Body</p>
                    </div>
                </div>
                <div class="rounded-2xl border bg-card p-4 shadow-sm flex items-center gap-4 group hover:shadow-md transition-all border-l-4 border-l-indigo-500">
                    <div class="rounded-xl bg-indigo-500/10 p-2.5 text-indigo-600"><Zap class="h-5 w-5" /></div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Engine Status</p>
                        <p class="text-lg font-black text-indigo-600 uppercase tracking-tighter italic">Live Sync</p>
                    </div>
                </div>
            </div>

            <!-- Search -->
            <div class="bg-white p-4 rounded-xl border shadow-sm">
                <div class="relative w-full max-w-md">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground font-medium" />
                    <Input v-model="searchQuery" placeholder="Quick find by test title or class..." class="pl-9 h-10 border-slate-200" />
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="assessments.data.length === 0" class="flex flex-col items-center justify-center py-24 bg-slate-50 rounded-3xl border shadow-inner">
                <div class="h-20 w-20 bg-white rounded-2xl border flex items-center justify-center mb-6 shadow-sm">
                    <LayoutGrid class="h-10 w-10 text-slate-200" />
                </div>
                <h3 class="text-2xl font-black text-slate-800 mb-2">No Assessments to Grade</h3>
                <p class="text-slate-500 font-medium max-w-sm text-center">There are currently no published assessments awaiting data entry for your assigned cluster.</p>
                <Button variant="outline" as-child class="mt-8 border-slate-200 font-black text-xs uppercase tracking-widest h-10 px-6 rounded-xl hover:bg-slate-100 transition-colors">
                    <Link href="/assessments">Return to Dashboard</Link>
                </Button>
            </div>

            <!-- Cards Grid -->
            <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div v-for="assessment in assessments.data" :key="assessment.id" 
                    class="group relative bg-white rounded-[2rem] border border-slate-100 p-7 shadow-sm hover:shadow-2xl hover:-translate-y-2 transition-all duration-500 flex flex-col"
                >
                    <div class="flex items-start justify-between mb-6">
                        <div class="h-12 w-12 bg-purple-600/10 rounded-2xl flex items-center justify-center text-purple-700 font-black shadow-inner border border-purple-50 group-hover:bg-purple-600 group-hover:text-white transition-all text-sm uppercase">
                            {{ assessment.subject?.name.substring(0, 2) }}
                        </div>
                        <Badge :class="[getStatusColor(assessment.status), 'rounded-full px-3 py-1 text-[9px] font-black uppercase tracking-widest border-0']">
                            {{ assessment.status }}
                        </Badge>
                    </div>

                    <h3 class="text-xl font-black text-slate-900 group-hover:text-purple-700 transition-colors capitalize leading-tight mb-2">
                         {{ assessment.title }}
                    </h3>
                    
                    <div class="flex flex-wrap items-center gap-2 mb-6">
                        <Badge variant="outline" class="bg-indigo-50 border-indigo-100 text-indigo-700 text-[10px] font-black uppercase px-2 py-0.5 rounded-lg">GRADE {{ assessment.class?.name }}</Badge>
                        <Badge variant="outline" class="bg-amber-50 border-amber-100 text-amber-700 text-[10px] font-black uppercase px-2 py-0.5 rounded-lg">{{ assessment.subject?.name }}</Badge>
                    </div>
                    
                    <div class="mt-auto space-y-4 pt-6 border-t border-slate-50 border-dashed">
                        <div class="flex items-center justify-between">
                             <div class="flex items-center gap-2">
                                  <Clock class="h-3.5 w-3.5 text-slate-400" />
                                  <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Entry Progress</span>
                             </div>
                             <span class="text-[10px] font-black text-purple-600">--%</span>
                        </div>
                        <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden shadow-inner">
                            <div class="h-full bg-linear-to-r from-purple-500 to-indigo-500 rounded-full group-hover:ring-2 ring-purple-100 transition-all duration-1000" style="width: 5%"></div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 mt-4">
                             <Button class="bg-purple-600 hover:bg-purple-700 text-white font-black text-[10px] uppercase tracking-widest rounded-2xl shadow-lg shadow-purple-100 h-10 border-0" as-child>
                                <Link :href="`/assessments/${assessment.id}/grading`">
                                    Grade <Plus class="ml-1.5 h-3.5 w-3.5 font-black" />
                                </Link>
                            </Button>
                             <Button variant="ghost" class="text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 font-black text-[10px] uppercase tracking-widest rounded-2xl h-10" as-child>
                                <Link :href="`/assessments/${assessment.id}`">
                                    <Eye class="mr-2 h-3.5 w-3.5" />Preview
                                </Link>
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Meta -->
            <div class="flex items-center justify-between py-12 border-t mt-12 mb-12">
                <div class="flex items-center gap-3">
                     <div class="h-8 w-8 rounded-full bg-slate-900 flex items-center justify-center text-white"><Target class="h-4 w-4" /></div>
                     <p class="text-[10px] text-slate-400 font-black uppercase tracking-[0.2em] italic">CBC Compliance Layer v2.1</p>
                </div>
                <p class="text-xs text-slate-400 font-bold italic tracking-tight">Active Index: {{ assessments.data.length }} Assessments</p>
            </div>
        </div>
    </AppLayout>
</template>
