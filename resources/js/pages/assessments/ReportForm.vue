<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { 
    Printer, 
    Download, 
    ChevronLeft, 
    Calendar, 
    User, 
    GraduationCap, 
    Award,
    CheckCircle2,
    BookOpen,
    Info
} from 'lucide-vue-next';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    student: any;
    academicYear: any;
    academicTerm: any;
    results: Array<any>;
    performanceLevels: Array<any>;
    attendance: any;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Assessments', href: '/assessments' },
    { title: 'Report Cards', href: '/assessments/report-cards' },
    { title: 'Report Form', href: '#' },
];

const printReport = () => {
    window.print();
};
</script>

<template>
    <Head title="Pupil's Termly Assessment Report" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 max-w-5xl mx-auto print:p-0 print:bg-white">
            <!-- Action Bar (Hidden on Print) -->
            <div class="flex items-center justify-between print:hidden">
                <Button variant="ghost" as-child class="font-bold -ml-4">
                    <Link href="/assessments/report-cards">
                        <ChevronLeft class="mr-2 h-4 w-4" />
                        Back to List
                    </Link>
                </Button>
                <div class="flex items-center gap-2">
                    <Button variant="outline" class="font-bold">
                        <Download class="mr-2 h-4 w-4" /> Export PDF
                    </Button>
                    <Button @click="printReport" class="bg-indigo-600 hover:bg-indigo-700 font-bold">
                        <Printer class="mr-2 h-4 w-4" /> Print Report
                    </Button>
                </div>
            </div>

            <!-- Report Form Container -->
            <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden print:shadow-none print:border-none print:rounded-none">
                <!-- Header -->
                <div class="bg-indigo-600 p-8 text-white relative overflow-hidden print:bg-white print:text-black print:border-b-2 print:border-indigo-600">
                    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div class="flex items-center gap-6">
                            <div class="h-20 w-20 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center p-2 border border-white/30 print:bg-gray-100 print:border-black">
                                <img src="/logo.png" alt="School Logo" class="h-full object-contain" />
                                <!-- Fallback if no logo -->
                                <School v-if="!false" class="h-10 w-10 text-white print:text-black" />
                            </div>
                            <div>
                                <h2 class="text-xs font-black uppercase tracking-[0.3em] opacity-80 print:text-gray-500">Official Document</h2>
                                <h1 class="text-3xl font-black tracking-tight mt-1">Pupil's Termly Assessment Report</h1>
                                <div class="flex flex-wrap gap-x-6 gap-y-2 mt-4 text-sm font-bold opacity-90 print:text-black">
                                    <span class="flex items-center gap-2"><Calendar class="h-4 w-4" /> {{ academicYear?.name }}</span>
                                    <span class="flex items-center gap-2"><Award class="h-4 w-4" /> {{ academicTerm?.name }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/20 min-w-[200px] print:bg-gray-50 print:border-black">
                            <p class="text-xs font-bold uppercase tracking-widest opacity-70 mb-1 print:text-gray-500">Student ID</p>
                            <p class="text-xl font-black tracking-widest">{{ student?.admission_number }}</p>
                        </div>
                    </div>
                </div>

                <!-- Student Info Section -->
                <div class="p-8 grid gap-8 md:grid-cols-3 bg-gray-50/50 border-b border-gray-100 print:bg-white">
                    <div class="flex gap-4">
                        <div class="h-12 w-12 rounded-xl bg-indigo-100 flex items-center justify-center text-indigo-600 shrink-0 print:hidden">
                            <User class="h-6 w-6" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Pupil's Name</p>
                            <p class="text-lg font-black text-gray-900 mt-0.5">{{ student?.first_name }} {{ student?.last_name }}</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="h-12 w-12 rounded-xl bg-purple-100 flex items-center justify-center text-purple-600 shrink-0 print:hidden">
                            <GraduationCap class="h-6 w-6" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Grade / Class</p>
                            <p class="text-lg font-black text-gray-900 mt-0.5 uppercase">{{ student?.current_class?.name }}</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="h-12 w-12 rounded-xl bg-orange-100 flex items-center justify-center text-orange-600 shrink-0 print:hidden">
                            <CheckCircle2 class="h-6 w-6" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Performance Level</p>
                            <Badge class="bg-green-100 text-green-700 border-0 font-black text-xs px-3 py-1 mt-1 uppercase">Meeting Expectation (ME)</Badge>
                        </div>
                    </div>
                </div>

                <!-- Performance Level Key -->
                <div class="px-8 pt-8">
                    <div class="flex flex-wrap gap-4 items-center p-4 bg-muted/20 border-2 border-dashed border-muted-foreground/10 rounded-2xl print:border-solid print:bg-white">
                        <div class="flex items-center gap-2 mr-4">
                            <Info class="h-4 w-4 text-indigo-600" />
                            <span class="text-xs font-black uppercase tracking-widest text-muted-foreground">Performance Key:</span>
                        </div>
                        <div v-for="level in performanceLevels" :key="level.code" class="flex items-center gap-2">
                             <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-white border border-gray-100 shadow-sm print:border-black print:shadow-none">
                                <span class="text-xs font-black text-indigo-600">{{ level.code }}:</span>
                                <span class="text-xs font-bold text-gray-700">{{ level.label }}</span>
                                <span class="text-[10px] font-black text-muted-foreground ml-1">({{ level.range }})</span>
                             </div>
                        </div>
                    </div>
                </div>

                <!-- Assessment Table -->
                <div class="p-8">
                    <div class="rounded-3xl border border-gray-100 overflow-hidden shadow-sm print:rounded-none print:border-black print:shadow-none">
                        <Table>
                            <TableHeader class="bg-indigo-600/5 print:bg-gray-100 print:text-black">
                                <TableRow class="hover:bg-transparent border-b-2 border-indigo-600 print:border-black">
                                    <TableHead class="font-black text-gray-900 py-6 pl-6 w-[30%]">Learning Area / Activity Area</TableHead>
                                    <TableHead class="text-center font-black text-gray-900 border-l border-gray-100 print:border-black">Opening Exam</TableHead>
                                    <TableHead class="text-center font-black text-gray-900 border-l border-gray-100 print:border-black">Mid Term Exam</TableHead>
                                    <TableHead class="text-center font-black text-gray-900 border-l border-gray-100 print:border-black">End Term Exam</TableHead>
                                    <TableHead class="text-center font-black text-indigo-600 border-l border-gray-100 print:border-black">Term Average</TableHead>
                                    <TableHead class="pr-6 font-black text-gray-900 border-l border-gray-100 print:border-black">Performance Comments</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="result in results" :key="result.subject" class="hover:bg-indigo-50/30 transition-colors border-gray-50 print:border-black">
                                    <TableCell class="font-black text-gray-900 py-5 pl-6 flex items-center gap-3">
                                        <div class="h-8 w-8 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600 text-xs shrink-0 print:hidden">
                                            <BookOpen class="h-4 w-4" />
                                        </div>
                                        {{ result.subject }}
                                    </TableCell>
                                    <TableCell class="text-center font-bold border-l border-gray-50 print:border-black">
                                        <div class="flex flex-col gap-1">
                                            <span class="text-sm">{{ result.opening?.score || '-' }}</span>
                                            <Badge v-if="result.opening?.level" variant="outline" class="mx-auto text-[8px] font-black h-4 px-1.5 bg-gray-50 border-0 uppercase">{{ result.opening.level }}</Badge>
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-center font-bold border-l border-gray-50 print:border-black">
                                        <div class="flex flex-col gap-1">
                                            <span class="text-sm">{{ result.mid?.score || '-' }}</span>
                                            <Badge v-if="result.mid?.level" variant="outline" class="mx-auto text-[8px] font-black h-4 px-1.5 bg-gray-50 border-0 uppercase">{{ result.mid.level }}</Badge>
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-center font-bold border-l border-gray-50 print:border-black">
                                        <div class="flex flex-col gap-1">
                                            <span class="text-sm">{{ result.end?.score || '-' }}</span>
                                            <Badge v-if="result.end?.level" variant="outline" class="mx-auto text-[8px] font-black h-4 px-1.5 bg-gray-50 border-0 uppercase">{{ result.end.level }}</Badge>
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-center font-black text-indigo-600 border-l border-gray-50 bg-indigo-50/20 print:border-black print:bg-white">
                                        <div class="flex flex-col gap-1">
                                            <span class="text-base">{{ result.average?.score || '-' }}</span>
                                            <Badge v-if="result.average?.level" variant="outline" class="mx-auto text-[9px] font-black h-4.5 px-2 bg-indigo-100 text-indigo-700 border-0 uppercase">{{ result.average.level }}</Badge>
                                        </div>
                                    </TableCell>
                                    <TableCell class="pr-6 text-xs text-muted-foreground font-medium border-l border-gray-50 italic print:border-black print:text-black">
                                        {{ result.comments || 'Developing well in this area with consistent improvement.' }}
                                    </TableCell>
                                </TableRow>
                                <!-- Footer / Summary Row -->
                                <TableRow class="bg-gray-50 border-t-2 border-gray-200 print:border-black print:bg-white">
                                    <TableCell class="py-6 pl-6 font-black text-gray-900">TERM TOTALS & AVERAGE</TableCell>
                                    <TableCell class="text-center font-black">-</TableCell>
                                    <TableCell class="text-center font-black">-</TableCell>
                                    <TableCell class="text-center font-black">-</TableCell>
                                    <TableCell class="text-center font-black text-indigo-600 text-lg">74.2%</TableCell>
                                    <TableCell class="pr-6 text-xs font-black uppercase tracking-widest text-indigo-600 text-right">MEETING EXPECTATIONS</TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </div>

                <!-- Footer Details -->
                <div class="p-8 grid gap-8 md:grid-cols-2 border-t border-gray-100 bg-gray-50/30 print:bg-white">
                    <div class="space-y-6">
                        <div>
                            <p class="text-xs font-black uppercase tracking-widest text-indigo-600">Class Teacher's Comments</p>
                            <div class="mt-3 p-4 bg-white rounded-2xl border border-gray-100 shadow-sm min-h-[80px] text-sm font-medium text-gray-700 print:border-black print:shadow-none">
                                {{ student?.first_name }} has shown great commitment to learning this term. Focus and participation in class discussions are exceptional. Keep up the good work!
                            </div>
                        </div>
                        <div>
                            <p class="text-xs font-black uppercase tracking-widest text-purple-600">Head Teacher's Remarks</p>
                            <div class="mt-3 p-4 bg-white rounded-2xl border border-gray-100 shadow-sm min-h-[60px] text-sm font-medium text-gray-700 print:border-black print:shadow-none">
                                Commendable progress. A disciplined and hard-working learner.
                            </div>
                        </div>
                    </div>

                    <div class="space-y-8">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="p-4 bg-white rounded-2xl border border-gray-100 shadow-sm print:border-black">
                                <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Closing Date</p>
                                <p class="text-sm font-bold text-gray-900 mt-1">April 12, 2026</p>
                            </div>
                            <div class="p-4 bg-white rounded-2xl border border-gray-100 shadow-sm print:border-black">
                                <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Opening Date</p>
                                <p class="text-sm font-bold text-gray-900 mt-1">May 4, 2026</p>
                            </div>
                        </div>

                        <!-- Signatures -->
                         <div class="grid grid-cols-2 gap-8 pt-8">
                            <div class="text-center space-y-2">
                                <div class="h-12 border-b-2 border-dotted border-gray-300 mx-auto w-4/5 flex items-end justify-center pb-2">
                                    <span class="text-[10px] text-muted-foreground font-bold italic opacity-30">Electronic Signature</span>
                                </div>
                                <p class="text-xs font-black uppercase text-gray-900 tracking-widest">Class Teacher</p>
                            </div>
                            <div class="text-center space-y-2">
                                <div class="h-12 border-b-2 border-dotted border-gray-300 mx-auto w-4/5 flex items-end justify-center pb-2">
                                     <span class="text-[10px] text-muted-foreground font-bold italic opacity-30">School Seal / Signature</span>
                                </div>
                                <p class="text-xs font-black uppercase text-indigo-600 tracking-widest underline decoration-2 underline-offset-4">Head Teacher</p>
                            </div>
                         </div>
                    </div>
                </div>
            </div>

            <!-- Footer Message (Hidden on Print) -->
            <p class="text-center text-xs text-muted-foreground font-bold py-6 italic print:hidden">
                Generated by CBC Learning Management System • {{ new Date().toLocaleDateString() }}
            </p>
        </div>
    </AppLayout>
</template>

<style scoped>
@media print {
    /* Ensure colors print correctly if user enables background colors */
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}
</style>
