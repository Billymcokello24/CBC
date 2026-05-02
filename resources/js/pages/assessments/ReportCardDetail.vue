<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Download,
    Printer,
    Mail,
    Phone,
    MapPin,
    Calendar,
    GraduationCap,
    TrendingUp,
    TrendingDown,
    Zap,
    Award,
    BookOpen,
    MessageSquare,
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { route } from 'ziggy-js';

const props = defineProps<{
    student: any;
    subjects: Array<any>;
    summary: any;
    activeTerm: any;
    activeYear: any;
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Report Cards', href: '/assessments/report-cards' },
    { title: props.student.full_name, href: '#' },
];

const getRubricColor = (average: number) => {
    if (average >= 75) return 'text-emerald-600 bg-emerald-50 border-emerald-100';
    if (average >= 50) return 'text-blue-600 bg-blue-50 border-blue-100';
    if (average >= 30) return 'text-amber-600 bg-amber-50 border-amber-100';
    return 'text-rose-600 bg-rose-50 border-rose-100';
};
</script>

<template>
    <Head :title="`Report Card - ${student.full_name}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex h-full max-w-[1400px] flex-1 animate-in flex-col space-y-8 p-4 pb-20 duration-700 fade-in slide-in-from-bottom-4 sm:p-6 sm:pb-32 md:p-8">
            
            <!-- Actions Toolbar -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between px-1">
                <Button variant="ghost" class="w-fit gap-2 text-xs font-black uppercase tracking-widest" as-child>
                    <Link :href="route('assessments.report-cards')">
                        <ArrowLeft class="h-4 w-4" /> Back to Registry
                    </Link>
                </Button>
                <div class="flex items-center gap-3">
                    <Button variant="outline" class="h-10 rounded-xl gap-2 text-[10px] font-black uppercase tracking-widest border-border bg-card">
                        <Printer class="h-4 w-4 text-primary" /> Print
                    </Button>
                    <Button class="h-10 rounded-xl gap-2 text-[10px] font-black uppercase tracking-widest bg-primary shadow-lg shadow-primary/20" as-child>
                        <a :href="route('assessments.report-cards.pdf', { student: student.id })" target="_blank">
                            <Download class="h-4 w-4" /> Download PDF
                        </a>
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                <!-- Profile & Stats -->
                <div class="space-y-8">
                    <Card class="rounded-3xl border-border bg-card shadow-sm overflow-hidden">
                        <div class="h-32 bg-gradient-to-br from-primary/10 to-primary/5 border-b border-border/50 relative">
                            <div class="absolute -bottom-12 left-1/2 -translate-x-1/2">
                                <div class="h-24 w-24 rounded-3xl bg-card border-4 border-card shadow-xl overflow-hidden">
                                     <img v-if="student.photo_url" :src="student.photo_url" class="h-full w-full object-cover" />
                                     <div v-else class="h-full w-full flex items-center justify-center text-2xl font-black text-primary/20 bg-primary/5 uppercase">{{ student.first_name[0] }}{{ student.last_name[0] }}</div>
                                </div>
                            </div>
                        </div>
                        <CardContent class="pt-16 pb-8 px-8 text-center">
                            <Badge variant="outline" class="mb-4 rounded-lg border-emerald-500/30 bg-emerald-500/5 text-emerald-600 text-[10px] font-black uppercase tracking-widest px-3 py-1">Active Learner</Badge>
                            <h2 class="text-xl font-black text-foreground uppercase tracking-tight">{{ student.full_name }}</h2>
                            <p class="text-xs font-bold text-muted-foreground uppercase mt-1 tracking-[0.2em] opacity-60">{{ student.admission_number }}</p>
                            
                            <div class="grid grid-cols-2 gap-4 mt-8">
                                <div class="flex flex-col items-center p-4 rounded-2xl bg-muted/5 border border-border/50">
                                    <GraduationCap class="h-5 w-5 text-primary mb-2 opacity-60" />
                                    <span class="text-[9px] font-black text-muted-foreground uppercase tracking-widest">Grade</span>
                                    <span class="text-sm font-black text-foreground uppercase">{{ student.current_class?.grade_level?.name || 'N/A' }}</span>
                                </div>
                                <div class="flex flex-col items-center p-4 rounded-2xl bg-muted/5 border border-border/50">
                                    <Users class="h-5 w-5 text-primary mb-2 opacity-60" />
                                    <span class="text-[9px] font-black text-muted-foreground uppercase tracking-widest">Class</span>
                                    <span class="text-sm font-black text-foreground uppercase">{{ student.current_class?.name || 'N/A' }}</span>
                                </div>
                            </div>

                            <Separator class="my-8 opacity-50" />

                            <div class="space-y-4 text-left">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-lg bg-muted/30 flex items-center justify-center border border-border/50">
                                        <Zap class="h-4 w-4 text-primary opacity-60" />
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-[9px] font-black text-muted-foreground uppercase tracking-widest">UPI Number</span>
                                        <span class="text-xs font-bold text-foreground">{{ student.upi || 'NOT ISSUED' }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-lg bg-muted/30 flex items-center justify-center border border-border/50">
                                        <Calendar class="h-4 w-4 text-primary opacity-60" />
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-[9px] font-black text-muted-foreground uppercase tracking-widest">Date of Birth</span>
                                        <span class="text-xs font-bold text-foreground">{{ student.date_of_birth || 'N/A' }} ({{ student.age }} yrs)</span>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Summary Card -->
                    <Card class="rounded-3xl border-border bg-card shadow-sm overflow-hidden">
                        <CardHeader class="pb-3 border-b border-border/50 bg-muted/5">
                            <CardTitle class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground">Termly Performance Matrix</CardTitle>
                        </CardHeader>
                        <CardContent class="p-8 space-y-6">
                            <div class="flex items-center justify-between">
                                <div class="flex flex-col">
                                    <span class="text-[10px] font-black text-muted-foreground uppercase tracking-widest mb-1">Mean Score</span>
                                    <span class="text-4xl font-black text-primary">{{ summary.average }}<span class="text-lg opacity-40">%</span></span>
                                </div>
                                <div :class="getRubricColor(summary.average)" class="h-20 w-20 rounded-3xl border-2 flex flex-col items-center justify-center">
                                    <span class="text-2xl font-black">{{ summary.rubric }}</span>
                                    <span class="text-[8px] font-black uppercase tracking-tighter">{{ summary.rubric_name }}</span>
                                </div>
                            </div>
                            
                            <div class="space-y-4 pt-4 border-t border-dashed border-border">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-black text-muted-foreground uppercase tracking-widest">Total Marks</span>
                                    <span class="text-xs font-black">{{ summary.total }} <span class="opacity-40 text-[10px]">/ {{ summary.max }}</span></span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-black text-muted-foreground uppercase tracking-widest">Grade Rank</span>
                                    <span class="text-xs font-black">1st <span class="opacity-40 text-[10px]">of 42</span></span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Subjects & Feedback -->
                <div class="xl:col-span-2 space-y-8">
                    <!-- Results Table -->
                    <Card class="rounded-3xl border-border bg-card shadow-sm overflow-hidden">
                        <CardHeader class="pb-3 pt-6 px-8 flex flex-row items-center justify-between border-b border-border/50 bg-muted/5">
                            <div>
                                <CardTitle class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground">Competency Area Performance</CardTitle>
                            </div>
                            <Badge variant="outline" class="rounded-lg border-primary/20 bg-primary/5 text-primary text-[9px] font-black uppercase tracking-widest px-3 py-1">Learning Areas: {{ subjects.length }}</Badge>
                        </CardHeader>
                        <CardContent class="p-0">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-muted/10 border-b border-border/30">
                                            <th class="p-6 text-[9px] font-black uppercase tracking-widest text-muted-foreground/60">Learning Area</th>
                                            <th class="p-6 text-[9px] font-black uppercase tracking-widest text-muted-foreground/60 text-center">Score</th>
                                            <th class="p-6 text-[9px] font-black uppercase tracking-widest text-muted-foreground/60 text-center">Rating</th>
                                            <th class="p-6 text-[9px] font-black uppercase tracking-widest text-muted-foreground/60">Professional Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-border/20">
                                        <tr v-for="subject in subjects" :key="subject.subject_id" class="group hover:bg-muted/5 transition-colors">
                                            <td class="p-6">
                                                <div class="flex items-center gap-3">
                                                    <div class="h-8 w-8 rounded-lg bg-primary/5 flex items-center justify-center border border-primary/10">
                                                        <BookOpen class="h-4 w-4 text-primary opacity-60" />
                                                    </div>
                                                    <span class="text-xs font-black text-foreground uppercase tracking-tight">{{ subject.name }}</span>
                                                </div>
                                            </td>
                                            <td class="p-6 text-center">
                                                <div class="flex flex-col">
                                                    <span class="text-sm font-black text-foreground">{{ subject.score }}</span>
                                                    <span class="text-[9px] font-bold text-muted-foreground opacity-40">of {{ subject.max }}</span>
                                                </div>
                                            </td>
                                            <td class="p-6 text-center">
                                                <div class="inline-flex flex-col items-center">
                                                    <span class="text-xs font-black text-primary">{{ subject.rubric }}</span>
                                                    <span class="text-[8px] font-bold text-muted-foreground uppercase tracking-tighter">{{ subject.rubric_name }}</span>
                                                </div>
                                            </td>
                                            <td class="p-6">
                                                <div class="flex items-start gap-3 max-w-[300px]">
                                                    <MessageSquare class="h-3.5 w-3.5 text-muted-foreground/30 mt-0.5 shrink-0" />
                                                    <span class="text-[11px] font-medium text-muted-foreground leading-relaxed">{{ subject.remarks }}</span>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Remarks & Signatures -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <Card class="rounded-3xl border-border bg-card shadow-sm border-l-4 border-l-primary/30">
                            <CardHeader class="pb-3 px-8">
                                <CardTitle class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground flex items-center gap-2">
                                    <Award class="h-4 w-4 text-primary" />
                                    General Professional Feedback
                                </CardTitle>
                            </CardHeader>
                            <CardContent class="px-8 pb-8">
                                <p class="text-xs font-medium text-foreground leading-relaxed italic opacity-80">
                                    "{{ student.full_name }} has demonstrated exceptional commitment to learning across most areas. The overall rating of {{ summary.rubric_name }} reflects a consistent high-performance culture. Maintain this trajectory into the next assessment cycle."
                                </p>
                                <div class="mt-6 flex flex-col items-end">
                                    <div class="h-1px w-32 bg-border mb-2"></div>
                                    <span class="text-[10px] font-black text-muted-foreground uppercase uppercase tracking-widest">Class Teacher</span>
                                </div>
                            </CardContent>
                        </Card>
                        
                        <Card class="rounded-3xl border-border bg-card shadow-sm">
                            <CardHeader class="pb-3 px-8">
                                <CardTitle class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground">Institutional Validation</CardTitle>
                            </CardHeader>
                            <CardContent class="px-8 pb-8 space-y-6">
                                <div class="flex items-center gap-4 p-4 rounded-2xl bg-muted/5 border border-border/50">
                                    <div class="h-10 w-10 rounded-xl bg-primary/10 flex items-center justify-center">
                                        <TrendingUp v-if="summary.average >= 50" class="h-5 w-5 text-emerald-600" />
                                        <TrendingDown v-else class="h-5 w-5 text-rose-600" />
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-[10px] font-black text-muted-foreground uppercase tracking-widest">Trajectory</span>
                                        <span class="text-xs font-black uppercase">{{ summary.average >= 50 ? 'Positive Growth' : 'Requires Intervention' }}</span>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end mt-4">
                                    <div class="h-1px w-32 bg-border mb-2"></div>
                                    <span class="text-[10px] font-black text-muted-foreground uppercase tracking-widest">Head of Institution</span>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
