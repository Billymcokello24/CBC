<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Calendar, Save, Clock, BookOpen, GraduationCap, ArrowRight, Settings2 } from 'lucide-vue-next';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    settings: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Settings', href: '/settings/school' },
    { title: 'Academic Year', href: '/settings/academic-year' },
];

</script>

<template>
    <Head title="Academic Settings" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 font-pulsar">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-500/10">
                        <Calendar class="h-6 w-6 text-blue-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Academic Year</h1>
                        <p class="text-muted-foreground">Configure terms, session dates and global academic behavior</p>
                    </div>
                </div>
                <Button class="bg-blue-600 hover:bg-blue-700 font-pulsar"><Save class="mr-2 h-4 w-4" />Save All</Button>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="space-y-6">
                    <div class="rounded-2xl border bg-card shadow-sm overflow-hidden border-l-4 border-l-blue-500">
                        <div class="bg-slate-50 px-6 py-4 border-b flex items-center justify-between">
                            <h3 class="font-bold flex items-center gap-2 text-blue-800"><Clock class="h-4 w-4" /> Current Term Session</h3>
                            <Badge class="bg-blue-600 text-[9px] font-black uppercase tracking-widest">ACTIVE</Badge>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <Label class="text-[10px] text-slate-400 font-black uppercase tracking-widest">Start Date</Label>
                                    <Input type="date" value="2026-01-05" />
                                </div>
                                <div class="space-y-1.5">
                                    <Label class="text-[10px] text-slate-400 font-black uppercase tracking-widest">End Date</Label>
                                    <Input type="date" value="2026-04-10" />
                                </div>
                            </div>
                            <div class="pt-2">
                                <p class="text-[10px] text-slate-400 font-medium italic">* These dates define automatic attendance and report card generation cycles.</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border bg-card shadow-sm overflow-hidden">
                        <div class="bg-slate-50 px-6 py-4 border-b">
                            <h3 class="font-bold flex items-center gap-2"><BookOpen class="h-4 w-4 text-slate-400" /> Subjects & Grading</h3>
                        </div>
                        <div class="p-6 space-y-6">
                             <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-bold text-slate-900">Auto-Calculate Mean Scores</p>
                                    <p class="text-xs text-muted-foreground">Sum subject scores for final report prints</p>
                                </div>
                                <div class="h-6 w-12 rounded-full bg-blue-100 relative cursor-pointer border border-blue-200">
                                     <div class="absolute right-1 top-1 h-4 w-4 rounded-full bg-blue-600 shadow-sm"></div>
                                </div>
                             </div>
                             <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-bold text-slate-900">Show Grade Percentages</p>
                                    <p class="text-xs text-muted-foreground">Display numerical values alongside rubric levels</p>
                                </div>
                                <div class="h-6 w-12 rounded-full bg-slate-100 relative cursor-pointer border border-slate-200">
                                     <div class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white shadow-sm border border-slate-300"></div>
                                </div>
                             </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border bg-slate-900 p-8 shadow-xl text-white relative flex flex-col justify-between overflow-hidden">
                    <div class="absolute -right-10 -top-10 opacity-10"><Settings2 class="h-48 w-48 text-white rotate-12" /></div>
                    <div>
                        <div class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-white/10 mb-6">
                            <GraduationCap class="h-6 w-6 text-blue-400" />
                        </div>
                        <h2 class="text-2xl font-bold mb-4">Academic Master Data</h2>
                        <p class="text-slate-400 mb-8 italic text-sm leading-relaxed">
                            Configure standard grading formats (Competency Levels vs Numerical), 
                            learning area clusters and global subject availability for the 2026 academic year.
                        </p>
                    </div>
                    
                    <div class="grid gap-3 font-pulsar">
                         <Button class="bg-blue-600 hover:bg-blue-700 w-full justify-between font-bold border-0 shadow-md transition-transform hover:scale-[1.01]">
                            Manage Grading Rubrics <ArrowRight class="h-4 w-4" />
                         </Button>
                         <Button variant="outline" class="bg-white/5 border-white/10 text-white hover:bg-white/10 w-full justify-between font-bold">
                            Subject Master List <ArrowRight class="h-4 w-4" />
                         </Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
