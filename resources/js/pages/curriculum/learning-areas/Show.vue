<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { 
    Shapes, BookOpen, ArrowLeft, 
    MoreVertical, ExternalLink, GraduationCap,
    CheckCircle2, AlertCircle, Info
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    learningArea: any;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Learning Areas', href: '/curriculum/learning-areas' },
    { title: props.learningArea.name, href: '#' },
];

</script>

<template>
    <Head :title="learningArea.name" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 p-8 font-sans max-w-[1600px] mx-auto animate-in fade-in duration-500">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div class="space-y-4">
                    <Link href="/curriculum/learning-areas" class="inline-flex items-center gap-2 text-[10px] font-bold text-slate-400 hover:text-blue-600 transition-colors uppercase tracking-widest">
                        <ArrowLeft class="h-3 w-3" /> Back to Areas
                    </Link>
                    <div class="space-y-2">
                        <div class="flex items-center gap-3">
                             <div class="h-10 w-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 border border-blue-100">
                                <Shapes class="h-5 w-5" />
                             </div>
                             <h1 class="text-3xl font-bold tracking-tight text-slate-900 uppercase">{{ learningArea.name }}</h1>
                        </div>
                        <p class="text-sm font-medium text-slate-500 italic max-w-2xl">{{ learningArea.description || 'No detailed description available for this structural domain.' }}</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-3 pb-2">
                    <Badge variant="outline" class="h-10 px-4 rounded-xl border-slate-100 bg-white font-bold text-[10px] uppercase tracking-wider text-slate-500">
                        {{ learningArea.code }}
                    </Badge>
                    <Badge class="h-10 px-4 rounded-xl bg-blue-600 text-white font-bold text-[10px] uppercase tracking-wider shadow-sm">
                        {{ learningArea.category || 'Core Area' }}
                    </Badge>
                </div>
            </div>

            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Subjects Sidebar/Info -->
                <div class="lg:col-span-1 space-y-8">
                    <div class="p-8 rounded-[2.5rem] bg-white border border-slate-100 shadow-sm space-y-6">
                        <h4 class="text-xs font-bold text-slate-900 uppercase tracking-widest flex items-center gap-2">
                            <Info class="h-4 w-4 text-blue-500" /> Area Statistics
                        </h4>
                        
                        <div class="grid gap-4">
                            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between group hover:bg-white hover:border-blue-100 transition-all cursor-default">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest italic group-hover:text-slate-600 transition-colors">Total Subjects</span>
                                <span class="text-xl font-black text-slate-900">{{ learningArea.subjects?.length || 0 }}</span>
                            </div>
                            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between group hover:bg-white hover:border-blue-100 transition-all cursor-default">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest italic group-hover:text-slate-600 transition-colors">Integration Status</span>
                                <Badge variant="outline" class="border-emerald-100 bg-emerald-50 text-emerald-600 font-bold text-[9px] uppercase tracking-widest px-3">Active</Badge>
                            </div>
                        </div>

                        <div class="pt-4">
                            <div class="p-6 rounded-2xl bg-blue-50/50 border border-blue-100 border-dashed space-y-2">
                                <p class="text-[10px] font-bold text-blue-600 uppercase tracking-widest flex items-center gap-2">
                                    <GraduationCap class="h-3.5 w-3.5" /> CBC Alignment
                                </p>
                                <p class="text-[11px] text-blue-700/70 italic font-medium leading-relaxed">
                                    This learning area is mapped to the standard KICD curriculum framework for this academic year.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subjects List -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="flex items-center justify-between">
                         <h2 class="text-xl font-bold text-slate-900 tracking-tight flex items-center gap-2 lowercase italic">
                            associated <span class="font-black uppercase not-italic">subjects</span>
                         </h2>
                         <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ learningArea.subjects?.length || 0 }} Identified</span>
                    </div>

                    <div class="grid gap-4">
                        <div v-for="subject in learningArea.subjects" :key="subject.id" class="group p-6 rounded-3xl bg-white border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 flex items-center justify-between">
                            <div class="flex items-center gap-6">
                                <div class="h-12 w-12 rounded-2xl bg-slate-50 group-hover:bg-blue-50 flex items-center justify-center text-slate-400 group-hover:text-blue-600 border border-slate-100 group-hover:border-blue-100 transition-all">
                                    <BookOpen class="h-6 w-6" />
                                </div>
                                <div>
                                    <div class="flex items-center gap-2 mb-0.5">
                                        <h3 class="text-lg font-bold text-slate-900 group-hover:text-blue-600 transition-colors uppercase tracking-tight">{{ subject.name }}</h3>
                                        <Badge variant="outline" class="text-[8px] font-bold uppercase tracking-wider bg-slate-100 text-slate-500">{{ subject.code }}</Badge>
                                    </div>
                                    <p class="text-[10px] text-slate-400 italic font-medium">Mapped to Grade Level: All Relevant Grades</p>
                                </div>
                            </div>

                            <Link :href="`/curriculum/syllabus/${subject.id}/1`" class="h-10 w-10 rounded-xl bg-slate-50 group-hover:bg-slate-900 flex items-center justify-center text-slate-400 group-hover:text-white border border-slate-100 group-hover:border-slate-900 transition-all shadow-sm">
                                <ExternalLink class="h-4 w-4" />
                            </Link>
                        </div>

                        <!-- Empty Subjects -->
                        <div v-if="!learningArea.subjects?.length" class="py-16 text-center rounded-[2.5rem] bg-slate-50 border-2 border-dashed border-slate-100">
                             <AlertCircle class="h-10 w-10 text-slate-200 mx-auto mb-4" />
                             <h4 class="text-lg font-bold text-slate-400 uppercase tracking-tight">No Subjects Linked</h4>
                             <p class="text-[10px] text-slate-300 font-bold uppercase tracking-widest italic">Allocate subjects to this area in the curriculum settings.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Minimal styling */
</style>
