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
    Info,
    School
} from 'lucide-vue-next';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
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

const conductAttributes = [
    { label: 'Classroom Conduct', t1: 'Excellent', t2: '', t3: '' },
    { label: 'Work Completion', t1: 'Good', t2: '', t3: '' },
    { label: 'Working with Others', t1: 'Very Good', t2: '', t3: '' },
    { label: 'Time management', t1: 'Punctual', t2: '', t3: '' },
    { label: 'Cleanliness / Grooming', t1: 'Neat', t2: '', t3: '' },
    { label: 'Communication', t1: 'Active', t2: '', t3: '' },
    { label: 'Respect', t1: 'Polite', t2: '', t3: '' },
];
</script>

<template>
    <Head title="Pupil's Termly Assessment Report" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 max-w-6xl mx-auto print:p-0 print:bg-white overflow-y-auto">
            <!-- Action Bar (Hidden on Print) -->
            <div class="flex items-center justify-between print:hidden">
                <Button variant="ghost" as-child class="font-black italic uppercase -ml-4">
                    <Link href="/assessments/report-cards">
                        <ChevronLeft class="mr-2 h-4 w-4" />
                        Back to List
                    </Link>
                </Button>
                <div class="flex items-center gap-2">
                    <Button variant="outline" class="font-black italic uppercase rounded-xl border-slate-200 shadow-sm">
                        <Download class="mr-2 h-4 w-4" /> Export PDF
                    </Button>
                    <Button @click="printReport" class="bg-indigo-600 hover:bg-indigo-700 font-black italic uppercase rounded-xl shadow-lg shadow-indigo-100">
                        <Printer class="mr-2 h-4 w-4" /> Print Report
                    </Button>
                </div>
            </div>

            <!-- Report Card Container -->
            <div class="bg-white p-12 shadow-2xl border border-slate-100 print:shadow-none print:border-none print:p-4 text-slate-900 font-serif leading-relaxed">
                
                <!-- Page 1 Header -->
                <div class="flex flex-col md:flex-row justify-between gap-8 mb-10 border-b-2 border-slate-900 pb-8">
                     <div class="space-y-4 max-w-xl">
                        <div class="flex items-center gap-4">
                             <div class="h-16 w-16 bg-slate-900 rounded-2xl flex items-center justify-center p-3">
                                 <School class="h-full w-full text-white" />
                             </div>
                             <div>
                                 <h1 class="text-4xl font-black italic uppercase tracking-tighter text-slate-900">Education Group</h1>
                                 <p class="text-xs font-bold tracking-[0.3em] text-slate-400 uppercase">Excellence in Competency Based Learning</p>
                             </div>
                        </div>
                        <h2 class="text-2xl font-black italic border-l-4 border-indigo-600 pl-4 py-1">PUPIL'S TERMLY ASSESSMENT REPORT</h2>
                        <div class="grid grid-cols-2 gap-x-8 gap-y-2 text-sm font-bold uppercase tracking-wider">
                             <div class="flex justify-between border-b border-slate-100 pb-1"><span>NAME:</span> <span class="text-indigo-600">{{ student?.first_name }} {{ student?.last_name }}</span></div>
                             <div class="flex justify-between border-b border-slate-100 pb-1"><span>YEAR:</span> <span class="text-indigo-600">{{ academicYear?.name }}</span></div>
                             <div class="flex justify-between border-b border-slate-100 pb-1"><span>GRADE:</span> <span class="text-indigo-600">{{ student?.current_class?.name }}</span></div>
                             <div class="flex justify-between border-b border-slate-100 pb-1"><span>TERM:</span> <span class="text-indigo-600">{{ academicTerm?.name }}</span></div>
                        </div>
                     </div>

                     <!-- Performance Legend Table -->
                     <div class="w-full md:w-80">
                         <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">1.0 LEARNER'S PERFORMANCE KEY</h3>
                         <table class="w-full text-[10px] border-collapse border border-slate-200 bg-slate-50">
                             <thead>
                                 <tr class="bg-slate-900 text-white font-black uppercase tracking-tighter text-[9px]">
                                     <th class="border border-slate-300 p-1.5 text-left">Performance Level</th>
                                     <th class="border border-slate-300 p-1.5">Rating</th>
                                     <th class="border border-slate-300 p-1.5">Key</th>
                                     <th class="border border-slate-300 p-1.5">Score</th>
                                 </tr>
                             </thead>
                             <tbody>
                                 <tr v-for="level in performanceLevels" :key="level.code" class="font-bold border-b border-slate-200">
                                     <td class="border border-slate-200 p-1.5">{{ level.label }}</td>
                                     <td class="border border-slate-200 p-1.5 text-center">{{ level.rating || '-' }}</td>
                                     <td class="border border-slate-200 p-1.5 text-center">{{ level.code }}</td>
                                     <td class="border border-slate-200 p-1.5 text-center">{{ level.range }}</td>
                                 </tr>
                             </tbody>
                         </table>
                     </div>
                </div>

                <!-- Main Assessment Matrix -->
                <div class="mb-10 overflow-x-auto">
                    <table class="w-full text-xs border-collapse border-2 border-slate-900">
                        <thead>
                            <tr class="bg-slate-900 text-white font-black uppercase text-center">
                                <th rowspan="2" class="border border-white p-4 text-left w-1/4">Learning Area (s) / Activity Area</th>
                                <th colspan="8" class="border border-white p-2">Assessment Module</th>
                                <th rowspan="2" class="border border-white p-4 w-1/4">Performance Comments</th>
                            </tr>
                            <tr class="bg-slate-800 text-white font-bold text-[9px] uppercase tracking-tighter">
                                <th colspan="2" class="border border-white p-1">Opening Exam</th>
                                <th colspan="2" class="border border-white p-1">Mid term Exam</th>
                                <th colspan="2" class="border border-white p-1">End Term Exam</th>
                                <th colspan="2" class="border border-indigo-500 p-1 bg-indigo-900">Average</th>
                            </tr>
                            <tr class="bg-slate-50 text-slate-400 font-black text-[8px] uppercase tracking-widest text-center">
                                <td class="border border-slate-200 p-1 text-left px-4">Subject Title</td>
                                <td class="border border-slate-200 p-1">Score</td>
                                <td class="border border-slate-200 p-1">Level</td>
                                <td class="border border-slate-200 p-1">Score</td>
                                <td class="border border-slate-200 p-1">Level</td>
                                <td class="border border-slate-200 p-1">Score</td>
                                <td class="border border-slate-200 p-1">Level</td>
                                <td class="border border-indigo-200 p-1 bg-indigo-50/50">Score</td>
                                <td class="border border-indigo-200 p-1 bg-indigo-50/50">Level</td>
                                <td class="border border-slate-200 p-1 text-left px-4 italic">Feedback Summary</td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="result in results" :key="result.subject" class="hover:bg-indigo-50/30 transition-colors">
                                <td class="border border-slate-200 p-3 font-black uppercase italic">{{ result.subject }}</td>
                                <td class="border border-slate-200 p-3 text-center font-bold text-slate-500">{{ result.opening?.score || '-' }}</td>
                                <td class="border border-slate-200 p-3 text-center font-black text-indigo-600">{{ result.opening?.level || '-' }}</td>
                                <td class="border border-slate-200 p-3 text-center font-bold text-slate-500">{{ result.mid?.score || '-' }}</td>
                                <td class="border border-slate-200 p-3 text-center font-black text-indigo-600">{{ result.mid?.level || '-' }}</td>
                                <td class="border border-slate-200 p-3 text-center font-bold text-slate-500">{{ result.end?.score || '-' }}</td>
                                <td class="border border-slate-200 p-3 text-center font-black text-indigo-600">{{ result.end?.level || '-' }}</td>
                                <td class="border border-indigo-100 p-3 text-center font-bold bg-indigo-50/20">{{ result.average?.score || '-' }}</td>
                                <td class="border border-indigo-100 p-3 text-center font-black text-indigo-700 bg-indigo-50/40">{{ result.average?.level || '-' }}</td>
                                <td class="border border-slate-200 p-3 text-xs italic text-slate-600 font-medium">{{ result.comments || 'Exhibits clear understanding and high retention of concepts.' }}</td>
                            </tr>
                            <!-- Summary Totals -->
                            <tr class="bg-indigo-50/50 font-black">
                                <td class="border-2 border-slate-900 p-4 uppercase text-sm tracking-tighter">Total Score / Mean Index</td>
                                <td colspan="6" class="border border-slate-200 text-center text-slate-400 font-bold uppercase tracking-widest text-[10px]">Aggregated Summary</td>
                                <td class="border-2 border-slate-900 p-4 text-center text-lg text-indigo-600">74.2%</td>
                                <td class="border-2 border-slate-900 p-4 text-center text-indigo-700">ME</td>
                                <td class="border-2 border-slate-900 p-4 text-right text-[10px] uppercase tracking-widest text-indigo-600 underline">Meeting Expectation</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Page 2 Content -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <!-- Left Column: Conduct & Behavior -->
                    <div class="space-y-6">
                        <section>
                            <h3 class="text-xs font-black uppercase tracking-widest text-slate-400 mb-3 flex items-center gap-2">
                                <span class="h-5 w-5 rounded bg-slate-900 text-white flex items-center justify-center text-[10px]">2.0</span> 
                                CONDUCT AND BEHAVIOUR
                            </h3>
                            <table class="w-full text-[10px] border-collapse border border-slate-200">
                                <thead>
                                    <tr class="bg-slate-50 font-black uppercase tracking-tighter">
                                        <th class="border border-slate-200 p-2 text-left">Attributes / Area</th>
                                        <th class="border border-slate-200 p-2">Term 1</th>
                                        <th class="border border-slate-200 p-2">Term 2</th>
                                        <th class="border border-slate-200 p-2">Term 3</th>
                                    </tr>
                                </thead>
                                <tbody class="font-bold">
                                    <tr v-for="attr in conductAttributes" :key="attr.label">
                                        <td class="border border-slate-200 p-2 uppercase text-slate-500">{{ attr.label }}</td>
                                        <td class="border border-slate-200 p-2 text-center text-indigo-600 italic">{{ attr.t1 }}</td>
                                        <td class="border border-slate-200 p-2 text-center text-slate-300 italic">{{ attr.t2 || '-' }}</td>
                                        <td class="border border-slate-200 p-2 text-center text-slate-300 italic">{{ attr.t3 || '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </section>

                        <section>
                            <h3 class="text-xs font-black uppercase tracking-widest text-slate-400 mb-3 flex items-center gap-2">
                                <span class="h-5 w-5 rounded bg-indigo-600 text-white flex items-center justify-center text-[10px]">3.0</span> 
                                CORE COMPETENCIES FEEDBACK
                            </h3>
                            <div class="p-4 bg-slate-50 border border-dashed border-slate-200 rounded-2xl min-h-[140px] text-xs font-medium italic text-slate-600">
                                <p class="mb-2 font-bold not-italic font-sans text-[10px] text-slate-400 leading-tight uppercase tracking-tighter">Acquisition of core competencies (Communication, Collaboration, Critical Thinking, etc.):</p>
                                {{ student?.first_name }} demonstrates strong communication skills and collaborates effectively with peers. Showcases emerging critical thinking during problem-solving activities.
                            </div>
                        </section>
                    </div>

                    <!-- Right Column: Values & CS Learning -->
                    <div class="space-y-6">
                         <section>
                            <h3 class="text-xs font-black uppercase tracking-widest text-slate-400 mb-3 flex items-center gap-2">
                                <span class="h-5 w-5 rounded bg-indigo-600 text-white flex items-center justify-center text-[10px]">4.0</span> 
                                ACQUISITION OF VALUES
                            </h3>
                            <div class="p-4 bg-slate-50 border border-dashed border-slate-200 rounded-2xl min-h-[120px] text-xs font-medium italic text-slate-600">
                                <p class="mb-2 font-bold not-italic font-sans text-[10px] text-slate-400 leading-tight uppercase tracking-tighter">(Love, Responsibility, Respect, Unity, Peace, Patriotism, Integrity):</p>
                                Displays a high sense of responsibility and respect for school property and peers. Consistently acts with integrity.
                            </div>
                        </section>

                        <section>
                            <h3 class="text-xs font-black uppercase tracking-widest text-slate-400 mb-3 flex items-center gap-2">
                                <span class="h-5 w-5 rounded bg-slate-900 text-white flex items-center justify-center text-[10px]">5.0</span> 
                                COMMUNITY SERVICE LEARNING
                            </h3>
                            <div class="p-4 bg-slate-50 border border-dashed border-slate-200 rounded-2xl min-h-[120px] text-xs font-medium italic text-slate-600">
                                <p class="mb-2 font-bold not-italic font-sans text-[10px] text-slate-400 leading-tight uppercase tracking-tighter">Participation in community service learning program:</p>
                                Actively participated in the environment cleaning drive and tree planting exercise. Showed great enthusiasm.
                            </div>
                        </section>

                        <!-- Dates Section -->
                        <div class="grid grid-cols-2 gap-4 pt-4">
                            <div class="p-4 border-2 border-slate-900 bg-white">
                                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">Closing Date</p>
                                <p class="text-sm font-black italic">{{ new Date().toLocaleDateString('en-GB') }}</p>
                            </div>
                            <div class="p-4 border-2 border-slate-900 bg-white">
                                <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-1">Opening Date</p>
                                <p class="text-sm font-black italic">TBD</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Signatures -->
                <div class="mt-16 pt-8 border-t-2 border-slate-900">
                    <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-8">6.0 SIGNING & OFFICIAL STAMP</h3>
                    <div class="grid grid-cols-3 gap-12">
                        <div class="space-y-4">
                             <div class="h-10 border-b border-slate-900"></div>
                             <div>
                                 <p class="text-[10px] font-black uppercase tracking-widest">Class Teacher</p>
                                 <p class="text-[9px] font-bold text-slate-400 italic">Signature / Date</p>
                             </div>
                        </div>
                        <div class="space-y-4">
                             <div class="h-10 border-b border-slate-900"></div>
                             <div>
                                 <p class="text-[10px] font-black uppercase tracking-widest">Head Teacher</p>
                                 <p class="text-[9px] font-bold text-slate-400 italic">Signature / Date</p>
                             </div>
                        </div>
                        <div class="space-y-4">
                             <div class="h-10 border-b border-slate-900"></div>
                             <div>
                                 <p class="text-[10px] font-black uppercase tracking-widest">Parent / Guardian</p>
                                 <p class="text-[9px] font-bold text-slate-400 italic">Signature / Date</p>
                             </div>
                        </div>
                    </div>
                </div>

                <!-- System Metadata -->
                <div class="mt-20 pt-4 flex justify-between items-center text-[9px] font-black uppercase tracking-[0.2em] text-slate-300">
                    <span>Generated by CBC Smart LMS v4.2</span>
                    <span>Document ID: {{ student?.admission_number }}-RC-{{ academicTerm?.id }}</span>
                    <span>Page 1 of 1</span>
                </div>
            </div>

            <!-- Print Prompt -->
            <p class="text-center text-xs text-slate-400 font-bold py-6 italic print:hidden">
                Tip: Press <kbd class="px-1 py-0.5 bg-slate-100 rounded border">Ctrl + P</kbd> to save this as a high-quality PDF.
            </p>
        </div>
    </AppLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Crimson+Pro:ital,wght@0,400;0,700;0,900;1,400;1,700;1,900&display=swap');

.font-serif {
    font-family: 'Crimson Pro', serif;
}

@media print {
    .p-12 { padding: 0 !important; }
    .shadow-2xl { box-shadow: none !important; }
    .border { border: none !important; }
    
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
    }

    /* Force landscape if items overflow, but portrait usually better for reports */
    @page {
        size: auto;
        margin: 0.5cm;
    }
}

/* Custom Scrollbar for the container */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}
.overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
}
.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
</style>
