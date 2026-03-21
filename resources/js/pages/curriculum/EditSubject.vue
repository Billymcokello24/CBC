<script setup lang="ts">
/* global route */
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Loader2, Save, BookOpen, ShieldCheck } from 'lucide-vue-next';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{ 
    subject: { 
        id: number; 
        learning_area_id: number; 
        department_id: number | null;
        name: string; 
        code: string; 
        description: string | null; 
        subject_type: string; 
        is_examinable: boolean; 
        display_order: number; 
        is_active: boolean 
    }; 
    learningAreas: Array<{ id: number; name: string }>;
    departments: Array<{ id: number; name: string }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' }, 
    { title: 'Curriculum', href: '/curriculum' }, 
    { title: 'Subjects Registry', href: route('curriculum.subjects') }, 
    { title: 'Modify Subject', href: route('curriculum.subjects.edit', props.subject.id) }
];

const form = useForm({ 
    learning_area_id: String(props.subject.learning_area_id), 
    department_id: props.subject.department_id ? String(props.subject.department_id) : '',
    name: props.subject.name, 
    code: props.subject.code, 
    description: props.subject.description ?? '', 
    subject_type: props.subject.subject_type, 
    is_examinable: props.subject.is_examinable, 
    display_order: props.subject.display_order, 
    is_active: props.subject.is_active 
});

const submit = () => form.transform((data) => ({ 
    ...data, 
    learning_area_id: Number(data.learning_area_id), 
    department_id: data.department_id ? Number(data.department_id) : null,
    display_order: Number(data.display_order), 
    is_examinable: Boolean(data.is_examinable), 
    is_active: Boolean(data.is_active) 
})).put(route('curriculum.subjects.update', props.subject.id));
</script>

<template>
    <Head title="Modify Subject Registry" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 p-8 font-pulsar mt-2 max-w-[1200px] mx-auto animate-in fade-in duration-500">
            <!-- Header section -->
            <div class="flex items-center gap-4">
                <Button variant="outline" size="icon" as-child class="h-10 w-10 shrink-0 border-slate-200">
                    <Link :href="route('curriculum.subjects')"><ArrowLeft class="h-4 w-4" /></Link>
                </Button>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-500/10 shrink-0">
                    <BookOpen class="h-6 w-6 text-violet-600" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Modify Subject Registry</h1>
                    <p class="text-muted-foreground font-medium uppercase text-[10px] tracking-widest">{{ subject.name }} • NODE_{{ subject.id }}</p>
                </div>
            </div>

            <div class="grid gap-8 lg:grid-cols-12">
                <!-- Main Form Area -->
                <div class="lg:col-span-8">
                    <form @submit.prevent="submit" class="space-y-8 rounded-[2.5rem] border bg-card p-10 shadow-sm border-t-8 border-t-violet-500">
                        <div class="grid gap-8">
                             <div class="grid gap-4 sm:grid-cols-2 text-left">
                                <div class="space-y-2">
                                    <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400 pl-1">Parent Learning Domain</Label>
                                    <Select v-model="form.learning_area_id">
                                        <SelectTrigger class="h-12 border-slate-200 rounded-xl focus:ring-violet-500">
                                            <SelectValue placeholder="Select domain" />
                                        </SelectTrigger>
                                        <SelectContent class="font-pulsar">
                                            <SelectItem v-for="area in learningAreas" :key="area.id" :value="String(area.id)">{{ area.name }}</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError :message="form.errors.learning_area_id" />
                                </div>
                                <div class="space-y-2">
                                     <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400 pl-1">Faculty Department</Label>
                                     <Select v-model="form.department_id">
                                        <SelectTrigger class="h-12 border-slate-200 rounded-xl focus:ring-violet-500">
                                            <SelectValue placeholder="No Department" />
                                        </SelectTrigger>
                                        <SelectContent class="font-pulsar">
                                            <SelectItem value="">No Department</SelectItem>
                                            <SelectItem v-for="dept in departments" :key="dept.id" :value="String(dept.id)">{{ dept.name }}</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError :message="form.errors.department_id" />
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2 text-left">
                                <div class="space-y-2">
                                    <Label for="name" class="text-[10px] font-black uppercase tracking-widest text-slate-400 pl-1">Subject Nomenclature</Label>
                                    <Input id="name" v-model="form.name" class="h-12 border-slate-200 rounded-xl focus:ring-violet-500" placeholder="e.g. Physics" />
                                    <InputError :message="form.errors.name" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="code" class="text-[10px] font-black uppercase tracking-widest text-slate-400 pl-1">Registry Hash Code</Label>
                                    <Input id="code" v-model="form.code" class="h-12 border-slate-200 rounded-xl uppercase font-bold focus:ring-violet-500" placeholder="e.g. PHYS" />
                                    <InputError :message="form.errors.code" />
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2 text-left">
                                <div class="space-y-2">
                                    <Label for="subject_type" class="text-[10px] font-black uppercase tracking-widest text-slate-400 pl-1">Instructional Type</Label>
                                    <Input id="subject_type" v-model="form.subject_type" class="h-12 border-slate-200 rounded-xl focus:ring-violet-500" placeholder="e.g. Core" />
                                    <InputError :message="form.errors.subject_type" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="display_order" class="text-[10px] font-black uppercase tracking-widest text-slate-400 pl-1">Matrix Priority</Label>
                                    <Input id="display_order" v-model="form.display_order" type="number" min="0" class="h-12 border-slate-200 rounded-xl focus:ring-violet-500" />
                                    <InputError :message="form.errors.display_order" />
                                </div>
                            </div>

                            <div class="space-y-2 text-left">
                                <Label for="description" class="text-[10px] font-black uppercase tracking-widest text-slate-400 pl-1">Academic Definition</Label>
                                <Textarea id="description" v-model="form.description" class="min-h-[120px] border-slate-200 rounded-2xl focus:ring-violet-500 p-4" placeholder="Specify subject pedagogical boundaries..." />
                                <InputError :message="form.errors.description" />
                            </div>

                            <div class="flex flex-wrap gap-6 pt-2">
                                <div class="flex items-center gap-3 p-4 rounded-2xl bg-slate-50 border shadow-inner min-w-[180px]">
                                    <Checkbox id="is_examinable" :checked="form.is_examinable" @update:checked="(val: boolean) => form.is_examinable = val" class="h-5 w-5 rounded-lg border-2 border-slate-300 data-[state=checked]:bg-blue-600 data-[state=checked]:border-blue-600" />
                                    <Label for="is_examinable" class="text-[10px] font-black uppercase tracking-widest cursor-pointer" :class="form.is_examinable ? 'text-blue-600' : 'text-slate-400'">Examinable Hub</Label>
                                </div>
                                <div class="flex items-center gap-3 p-4 rounded-2xl bg-slate-50 border shadow-inner min-w-[180px]">
                                    <Checkbox id="is_active" :checked="form.is_active" @update:checked="(val: boolean) => form.is_active = val" class="h-5 w-5 rounded-lg border-2 border-slate-300 data-[state=checked]:bg-emerald-600 data-[state=checked]:border-emerald-600" />
                                    <Label for="is_active" class="text-[10px] font-black uppercase tracking-widest cursor-pointer" :class="form.is_active ? 'text-emerald-600' : 'text-slate-400'">Operational Pulse</Label>
                                </div>
                            </div>
                        </div>

                        <div class="pt-8 flex items-center justify-end gap-3 border-t border-slate-50">
                            <Button type="button" variant="outline" class="h-12 px-8 rounded-2xl font-black text-xs uppercase tracking-widest" as-child>
                                <Link :href="route('curriculum.subjects')">Cancel</Link>
                            </Button>
                            <Button type="submit" :disabled="form.processing" class="bg-violet-600 hover:bg-violet-700 h-12 px-8 rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-violet-100 min-w-[200px] border-0">
                                <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                                <Save v-else class="mr-2 h-4 w-4" />
                                Update Matrix Node
                            </Button>
                        </div>
                    </form>
                </div>

                <!-- Intelligence Sidebar -->
                <div class="lg:col-span-4 space-y-6">
                     <div class="rounded-[2.5rem] bg-slate-950 p-10 shadow-2xl text-white relative overflow-hidden group border-[12px] border-slate-900">
                          <div class="absolute -right-8 -bottom-8 opacity-10 group-hover:scale-110 group-hover:-rotate-12 transition-all duration-700 transform">
                               <BookOpen class="h-48 w-48" />
                          </div>
                          <div class="relative z-10 space-y-6">
                               <h3 class="text-xl font-black uppercase tracking-tighter flex items-center gap-3">
                                   <ShieldCheck class="h-6 w-6 text-violet-400" /> Policy Control
                               </h3>
                               <p class="text-sm font-medium text-slate-400 leading-relaxed italic opacity-80">
                                   Modifying this registry node will ripple through grade-level allocations and instructional mapping matrices. Finalize with caution.
                               </p>
                               <div class="pt-6 border-t border-white/10 space-y-4">
                                    <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-widest">
                                         <span class="text-slate-500">Subject Integrity</span>
                                         <span class="text-emerald-400 italic">SECURE</span>
                                    </div>
                                    <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-widest">
                                         <span class="text-slate-500">Dependency Check</span>
                                         <span class="text-violet-400 uppercase italic">Passed</span>
                                    </div>
                               </div>
                          </div>
                     </div>

                     <div class="rounded-[2rem] border bg-slate-50 p-8 shadow-inner space-y-4">
                          <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Global Identifiers</h4>
                          <div class="space-y-2">
                               <div class="text-xs font-bold text-slate-900">NODE_ID: {{ subject.id }}</div>
                               <div class="text-xs font-bold text-slate-900 uppercase">UID: SUB_{{ subject.code }}_2026</div>
                          </div>
                     </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.font-black { font-weight: 950; }
</style>
