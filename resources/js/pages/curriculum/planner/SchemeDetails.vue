<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    ChevronLeft, Plus, Save, Trash2, 
    BookOpen, GraduationCap, Calendar, 
    Layers, Target, ListChecks, MessageSquare,
    Search, Download, FileText, Filter, ChevronRight
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { ref, computed } from 'vue';

const props = defineProps({
    scheme: Object,
    strands: Array
});

const isAddingEntry = ref(false);

const entryForm = useForm({
    week_number: '',
    lesson_number: '',
    strand_id: '',
    sub_strand_id: '',
    topic: '',
    learning_outcomes: '',
    learning_activities: '',
    resources: '',
    assessment: '',
    remarks: ''
});

const submitEntry = () => {
    entryForm.post(`/curriculum/planner/schemes/${props.scheme.id}/entries`, {
        onSuccess: () => {
            isAddingEntry.value = false;
            entryForm.reset();
        }
    });
};

const deleteEntry = (entryId) => {
    if (confirm('Are you sure you want to remove this entry?')) {
        useForm({}).delete(`/curriculum/planner/schemes/${props.scheme.id}/entries/${entryId}`);
    }
};

const getStatusColor = (status) => {
    switch (status) {
        case 'approved': return 'bg-emerald-100 text-emerald-700 border-emerald-200';
        case 'pending': return 'bg-amber-100 text-amber-700 border-amber-200';
        default: return 'bg-slate-100 text-slate-700 border-slate-200';
    }
};
</script>

<template>
    <Head :title="'Scheme Details - ' + scheme.title" />

    <AppLayout>
        <div class="max-w-[1600px] mx-auto p-6 space-y-8">
            <!-- Breadcrumbs & Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2 text-sm text-slate-500 mb-2">
                        <Link href="/curriculum/planner/schemes" class="hover:text-blue-600 transition-colors flex items-center gap-1">
                            <ChevronLeft class="h-4 w-4" /> Schemes of Work
                        </Link>
                        <span class="text-slate-300">/</span>
                        <span class="font-medium text-slate-900">{{ scheme.title }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-3xl font-bold tracking-tight text-slate-900">{{ scheme.title }}</h1>
                        <Badge :class="getStatusColor(scheme.status)" variant="outline" class="px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wider">
                            {{ scheme.status }}
                        </Badge>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <Button variant="outline" class="rounded-xl border-slate-200 hover:bg-slate-50 transition-all font-semibold">
                        <Download class="mr-2 h-4 w-4" /> Export Matrix
                    </Button>
                    <Button @click="isAddingEntry = true" class="bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow-md transition-all font-semibold" disabled>
                        <Plus class="mr-2 h-4 w-4" /> Add Lesson Entry
                    </Button>
                </div>
            </div>

            <!-- Administrative Details Card -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-start gap-4 transition-all hover:shadow-md">
                    <div class="p-3 bg-blue-50 rounded-xl">
                        <BookOpen class="h-6 w-6 text-blue-600" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500">Learning Area</p>
                        <p class="text-lg font-bold text-slate-900">{{ scheme.subject?.name }}</p>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-start gap-4 transition-all hover:shadow-md">
                    <div class="p-3 bg-indigo-50 rounded-xl">
                        <GraduationCap class="h-6 w-6 text-indigo-600" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500">Grade Level</p>
                        <p class="text-lg font-bold text-slate-900">{{ scheme.grade_level?.name }}</p>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-start gap-4 transition-all hover:shadow-md">
                    <div class="p-3 bg-amber-50 rounded-xl">
                        <Calendar class="h-6 w-6 text-amber-600" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500">Term / Schedule</p>
                        <p class="text-lg font-bold text-slate-900">{{ scheme.academic_term?.name }} ({{ scheme.total_weeks }} Weeks)</p>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-start gap-4 transition-all hover:shadow-md">
                    <div class="p-3 bg-slate-50 rounded-xl">
                        <Layers class="h-6 w-6 text-slate-600" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500">Lessons / Week</p>
                        <p class="text-lg font-bold text-slate-900">{{ scheme.lessons_per_week }} Periods</p>
                    </div>
                </div>
            </div>

            <!-- Main Matrix Table -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden border-b-0">
                <div class="p-5 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <FileText class="h-5 w-5 text-slate-400" />
                        <h2 class="font-bold text-slate-900">Termly Schemes of Work Matrix</h2>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="relative group">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-hover:text-blue-500 transition-colors" />
                            <Input placeholder="Search matrix..." class="pl-10 rounded-xl border-slate-200 w-64 h-9 bg-white" />
                        </div>
                        <Button variant="outline" size="sm" class="rounded-lg h-9">
                            <Filter class="mr-2 h-3.5 w-3.5" /> Filters
                        </Button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse table-fixed min-w-[1200px]">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-200">
                                <th class="p-4 text-xs font-bold uppercase tracking-wider text-slate-500 w-16">Wk</th>
                                <th class="p-4 text-xs font-bold uppercase tracking-wider text-slate-500 w-16">Ls</th>
                                <th class="p-4 text-xs font-bold uppercase tracking-wider text-slate-500 w-44">Strand/Sub-Strand</th>
                                <th class="p-4 text-xs font-bold uppercase tracking-wider text-slate-500 w-48">Topic</th>
                                <th class="p-4 text-xs font-bold uppercase tracking-wider text-slate-500 w-64">Learning Outcomes</th>
                                <th class="p-4 text-xs font-bold uppercase tracking-wider text-slate-500 w-64">Learning Activities</th>
                                <th class="p-4 text-xs font-bold uppercase tracking-wider text-slate-500 w-40">Resources</th>
                                <th class="p-4 text-xs font-bold uppercase tracking-wider text-slate-500 w-32">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="entry in scheme.entries" :key="entry.id" class="hover:bg-slate-50 transition-colors">
                                <td class="p-4 font-bold text-slate-900">{{ entry.week_number }}</td>
                                <td class="p-4 text-slate-600">{{ entry.lesson_number }}</td>
                                <td class="p-4">
                                    <div class="flex flex-col gap-1">
                                        <Badge variant="secondary" class="w-fit text-[10px] bg-slate-100 text-slate-600 rounded-md py-0 px-1.5">{{ entry.strand?.name }}</Badge>
                                        <span class="text-sm font-medium text-slate-800">{{ entry.sub_strand?.name }}</span>
                                    </div>
                                </td>
                                <td class="p-4 text-sm text-slate-600 leading-relaxed">{{ entry.topic }}</td>
                                <td class="p-4">
                                    <ul class="list-disc list-inside text-sm text-slate-600 space-y-1">
                                        <li v-for="(line, i) in (entry.learning_outcomes?.split('\n') || [])" :key="i">{{ line }}</li>
                                    </ul>
                                </td>
                                <td class="p-4">
                                    <ul class="list-disc list-inside text-sm text-slate-600 space-y-1">
                                        <li v-for="(line, i) in (entry.learning_activities?.split('\n') || [])" :key="i">{{ line }}</li>
                                    </ul>
                                </td>
                                <td class="p-4 text-sm text-slate-500">{{ entry.resources }}</td>
                                <td class="p-4">
                                    <div class="flex items-center gap-2">
                                        <Button variant="ghost" size="icon" class="h-8 w-8 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg">
                                            <FileText class="h-4 w-4" />
                                        </Button>
                                        <Button @click="deleteEntry(entry.id)" variant="ghost" size="icon" class="h-8 w-8 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg">
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!scheme.entries?.length">
                                <td colspan="8" class="p-12 text-center text-slate-500">
                                    No matrix entries yet.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
