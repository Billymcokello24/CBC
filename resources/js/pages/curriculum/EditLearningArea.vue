<script setup lang="ts">
/* global route */
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Loader2, Save, GraduationCap, ShieldCheck } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
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
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Learning Areas', href: route('curriculum.learning-areas') },
    { title: 'Modify Domain', href: route('curriculum.learning-areas.edit', props.learningArea.id) },
];

const form = useForm({
    name: props.learningArea.name,
    code: props.learningArea.code,
    description: props.learningArea.description ?? '',
    category: props.learningArea.category ?? '',
    display_order: props.learningArea.display_order,
    is_active: props.learningArea.is_active,
});

const submit = () => {
    form.transform((data) => ({ ...data, display_order: Number(data.display_order), is_active: Boolean(data.is_active) }))
        .put(route('curriculum.learning-areas.update', props.learningArea.id));
};
</script>

<template>
    <Head title="Modify Learning Domain" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 p-8 font-pulsar mt-2 max-w-[1200px] mx-auto animate-in fade-in duration-500">
            <!-- Header section -->
            <div class="flex items-center gap-4">
                <Button variant="outline" size="icon" as-child class="h-10 w-10 shrink-0 border-slate-200">
                    <Link :href="route('curriculum.learning-areas')"><ArrowLeft class="h-4 w-4" /></Link>
                </Button>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-500/10 shrink-0">
                    <GraduationCap class="h-6 w-6 text-violet-600" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Modify Learning Domain</h1>
                    <p class="text-muted-foreground font-medium uppercase text-[10px] tracking-widest">{{ learningArea.name }} • SYSTEM_NODE_{{ learningArea.id }}</p>
                </div>
            </div>

            <div class="grid gap-8 lg:grid-cols-12">
                <!-- Main Form Area -->
                <div class="lg:col-span-8">
                    <form @submit.prevent="submit" class="space-y-8 rounded-[2.5rem] border bg-card p-10 shadow-sm border-t-8 border-t-violet-500">
                        <div class="grid gap-8">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="name" class="text-[10px] font-black uppercase tracking-widest text-slate-400 pl-1">Domain Name</Label>
                                    <Input id="name" v-model="form.name" class="h-12 border-slate-200 rounded-xl focus:ring-violet-500" placeholder="e.g. Creative Arts" />
                                    <InputError :message="form.errors.name" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="code" class="text-[10px] font-black uppercase tracking-widest text-slate-400 pl-1">Registry Code</Label>
                                    <Input id="code" v-model="form.code" class="h-12 border-slate-200 rounded-xl uppercase font-bold focus:ring-violet-500" placeholder="e.g. CART" />
                                    <InputError :message="form.errors.code" />
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="category" class="text-[10px] font-black uppercase tracking-widest text-slate-400 pl-1">Academic Category</Label>
                                    <Input id="category" v-model="form.category" class="h-12 border-slate-200 rounded-xl focus:ring-violet-500" placeholder="e.g. Core Cluster" />
                                    <InputError :message="form.errors.category" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="display_order" class="text-[10px] font-black uppercase tracking-widest text-slate-400 pl-1">Sequence Priority</Label>
                                    <Input id="display_order" v-model="form.display_order" type="number" min="0" class="h-12 border-slate-200 rounded-xl focus:ring-violet-500" />
                                    <InputError :message="form.errors.display_order" />
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label for="description" class="text-[10px] font-black uppercase tracking-widest text-slate-400 pl-1">Instructional Definition</Label>
                                <Textarea id="description" v-model="form.description" class="min-h-[120px] border-slate-200 rounded-2xl focus:ring-violet-500 p-4" placeholder="Specify the scope and objectives of this learning domain..." />
                                <InputError :message="form.errors.description" />
                            </div>

                            <div class="flex items-center gap-3 p-4 rounded-2xl bg-slate-50 border shadow-inner max-w-xs">
                                <Checkbox id="is_active" :checked="form.is_active" @update:checked="(val: boolean) => form.is_active = val" class="h-5 w-5 rounded-lg border-2 border-slate-300 data-[state=checked]:bg-emerald-600 data-[state=checked]:border-emerald-600" />
                                <Label for="is_active" class="text-xs font-black uppercase tracking-widest cursor-pointer" :class="form.is_active ? 'text-emerald-600' : 'text-slate-400'">
                                    {{ form.is_active ? 'Logic Node: Active' : 'Logic Node: Dormant' }}
                                </Label>
                            </div>
                        </div>

                        <div class="pt-8 flex items-center justify-end gap-3 border-t border-slate-50">
                            <Button type="button" variant="outline" class="h-12 px-8 rounded-2xl font-black text-xs uppercase tracking-widest" as-child>
                                <Link :href="route('curriculum.learning-areas')">Discard Changes</Link>
                            </Button>
                            <Button type="submit" :disabled="form.processing" class="bg-indigo-600 hover:bg-indigo-700 h-12 px-8 rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-indigo-100 min-w-[180px]">
                                <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                                <Save v-else class="mr-2 h-4 w-4" />
                                Synchronize Updates
                            </Button>
                        </div>
                    </form>
                </div>

                <!-- Intelligence Sidebar -->
                <div class="lg:col-span-4 space-y-6">
                     <div class="rounded-[2.5rem] bg-slate-950 p-10 shadow-2xl text-white relative overflow-hidden group border-[12px] border-slate-900">
                          <div class="absolute -right-8 -bottom-8 opacity-10 group-hover:scale-110 group-hover:-rotate-12 transition-all duration-700 transform">
                               <ShieldCheck class="h-48 w-48" />
                          </div>
                          <div class="relative z-10 space-y-6">
                               <h3 class="text-xl font-black uppercase tracking-tighter flex items-center gap-3">
                                   <ShieldCheck class="h-6 w-6 text-violet-400" /> Matrix Security
                               </h3>
                               <p class="text-sm font-medium text-slate-400 leading-relaxed italic opacity-80">
                                   Ensure all domain parameters align with the national curriculum frameworks before synchronizing updates with the global academic matrix.
                               </p>
                               <div class="pt-6 border-t border-white/10 space-y-4">
                                    <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-widest">
                                         <span class="text-slate-500">Registry Consistency</span>
                                         <span class="text-emerald-400 italic">Verified</span>
                                    </div>
                                    <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-widest">
                                         <span class="text-slate-500">Access Tier</span>
                                         <span class="text-violet-400 uppercase">Privileged</span>
                                    </div>
                               </div>
                          </div>
                     </div>

                     <div class="rounded-[2rem] border bg-slate-50 p-8 shadow-inner space-y-4">
                          <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">System Metadata</h4>
                          <div class="space-y-2">
                               <div class="text-xs font-bold text-slate-900">ID: {{ learningArea.id }}</div>
                               <div class="text-xs font-bold text-slate-900 uppercase">NODE: RECL_ORC_{{ learningArea.code }}</div>
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
