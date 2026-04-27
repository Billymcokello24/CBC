<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    ArrowLeft, 
    Loader2, 
    PlusCircle, 
    LayoutGrid, 
    BookOpen, 
    ShieldCheck, 
    ChevronRight,
    Milestone,
    Layers,
    Calendar,
    CheckCircle2
} from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Academic', href: '/grades' },
    { title: 'Grade Structure', href: '/grades' },
    { title: 'Provision Grade', href: '/grades/create' },
];

const form = useForm({
    name: '',
    code: '',
    category: 'CBC',
    level_order: 1,
    minimum_age: '',
    maximum_age: '',
    is_active: true,
    is_bulk: false,
    base_name: 'Grade',
    start_level: 1,
    end_level: 6,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        level_order: data.is_bulk
            ? Number(data.start_level)
            : Number(data.level_order),
        minimum_age: data.minimum_age ? Number(data.minimum_age) : null,
        maximum_age: data.maximum_age ? Number(data.maximum_age) : null,
        start_level: Number(data.start_level),
        end_level: Number(data.end_level),
        is_active: Boolean(data.is_active),
    })).post('/grades');
};
</script>

<template>
    <Head title="Provision Grade" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-[1200px] animate-in space-y-8 p-4 pb-10 duration-700 fade-in slide-in-from-bottom-4 sm:p-6 sm:pb-20 md:p-8">
            <!-- Header Section -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between px-1">
                <div class="flex flex-col gap-1">
                    <div class="mb-1 flex items-center gap-2 text-[10px] font-bold tracking-tight text-muted-foreground uppercase">
                        <Milestone class="h-3 w-3" />
                        <ChevronRight class="h-2.5 w-2.5 opacity-50" />
                        <span>Academic</span>
                        <ChevronRight class="h-2.5 w-2.5 opacity-50" />
                        <span>Grade Structure</span>
                        <ChevronRight class="h-2.5 w-2.5 opacity-50" />
                        <span class="text-foreground">Provision</span>
                    </div>
                    <h1 class="text-xl leading-tight font-extrabold tracking-tight text-foreground sm:text-2xl italic">Provision Academic Grade</h1>
                    <p class="text-xs text-muted-foreground">Define new educational levels and sequence them within the institutional hierarchy.</p>
                </div>

                <Link
                    href="/grades"
                    class="inline-flex h-9 items-center justify-center rounded-xl border border-border bg-background px-4 text-[10px] font-bold tracking-tight text-foreground uppercase transition-all hover:bg-muted"
                >
                    <ArrowLeft class="mr-2 h-4 w-4 opacity-70" />
                    Back to Matrix
                </Link>
            </div>

            <div class="grid gap-8 lg:grid-cols-3">
                <!-- Main Form Area -->
                <div class="lg:col-span-2 space-y-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Mode Selector Card -->
                        <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-all hover:shadow-md dark:border-slate-800 dark:bg-slate-900/50">
                            <div class="flex items-start justify-between">
                                <div class="space-y-1">
                                    <h3 class="text-xs font-bold tracking-tight uppercase text-slate-900 dark:text-white">Provisioning Strategy</h3>
                                    <p class="text-[10px] text-muted-foreground text-slate-500">Choose between individual entry or bulk generation of academic levels.</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <label class="relative inline-flex cursor-pointer items-center">
                                        <input type="checkbox" v-model="form.is_bulk" class="peer sr-only" />
                                        <div class="h-5 w-9 rounded-full bg-slate-200 after:absolute after:top-[2px] after:left-[2px] after:h-4 after:w-4 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-blue-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none dark:bg-slate-700"></div>
                                        <span class="ml-3 text-[10px] font-bold tracking-tight uppercase text-slate-600 dark:text-slate-400">Bulk Mode</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Form Content Card -->
                        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900/50 overflow-hidden">
                            <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-3 dark:border-slate-800 dark:bg-slate-800/30">
                                <h3 class="flex items-center gap-2 text-[10px] font-bold tracking-tight uppercase text-slate-700 dark:text-slate-300">
                                    <Layers class="h-3.5 w-3.5 text-blue-500" />
                                    {{ form.is_bulk ? 'Batch Configuration' : 'Single Grade Parameters' }}
                                </h3>
                            </div>

                            <div class="p-6">
                                <div v-if="form.is_bulk" class="grid gap-6 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <Label for="base_name" class="text-[9px] font-bold uppercase tracking-widest text-slate-500">Base Labeling</Label>
                                        <Input id="base_name" v-model="form.base_name" placeholder="Grade" class="h-10 rounded-xl border-slate-200 focus:ring-blue-500/20 text-xs" />
                                        <InputError :message="form.errors.base_name" />
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="space-y-2">
                                            <Label for="start_level" class="text-[9px] font-bold uppercase tracking-widest text-slate-500">Start Offset</Label>
                                            <Input id="start_level" v-model="form.start_level" type="number" class="h-10 rounded-xl border-slate-200 text-xs" />
                                        </div>
                                        <div class="space-y-2">
                                            <Label for="end_level" class="text-[9px] font-bold uppercase tracking-widest text-slate-500">End Limit</Label>
                                            <Input id="end_level" v-model="form.end_level" type="number" class="h-10 rounded-xl border-slate-200 text-xs" />
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="category" class="text-[9px] font-bold uppercase tracking-widest text-slate-500">Curriculum Category</Label>
                                        <Input id="category" v-model="form.category" class="h-10 rounded-xl border-slate-200 text-xs" />
                                        <InputError :message="form.errors.category" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="is_active_bulk" class="text-[9px] font-bold uppercase tracking-widest text-slate-500">Initial Status</Label>
                                        <select id="is_active_bulk" v-model="form.is_active" class="flex h-10 w-full rounded-xl border border-slate-200 bg-white px-4 text-xs transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-800 dark:bg-slate-900">
                                            <option :value="true">Active & Visible</option>
                                            <option :value="false">Archived / Hidden</option>
                                        </select>
                                    </div>
                                </div>

                                <div v-else class="grid gap-6 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <Label for="name" class="text-[9px] font-bold uppercase tracking-widest text-slate-500">Full Designation</Label>
                                        <Input id="name" v-model="form.name" placeholder="Grade 9" class="h-10 rounded-xl border-slate-200 text-xs" />
                                        <InputError :message="form.errors.name" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="code" class="text-[9px] font-bold uppercase tracking-widest text-slate-500">System Alias (Code)</Label>
                                        <Input id="code" v-model="form.code" placeholder="G9" class="h-10 rounded-xl border-slate-200 text-xs" />
                                        <InputError :message="form.errors.code" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="level_order" class="text-[9px] font-bold uppercase tracking-widest text-slate-500">Progression Index</Label>
                                        <Input id="level_order" v-model="form.level_order" type="number" class="h-10 rounded-xl border-slate-200 text-xs" />
                                        <InputError :message="form.errors.level_order" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="category" class="text-[9px] font-bold uppercase tracking-widest text-slate-500">Category Tag</Label>
                                        <Input id="category" v-model="form.category" class="h-10 rounded-xl border-slate-200 text-xs" />
                                        <InputError :message="form.errors.category" />
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="space-y-2">
                                            <Label for="min_age" class="text-[9px] font-bold uppercase tracking-widest text-slate-500">Min Age</Label>
                                            <Input id="min_age" v-model="form.minimum_age" type="number" class="h-10 rounded-xl border-slate-200 text-xs" />
                                        </div>
                                        <div class="space-y-2">
                                            <Label for="max_age" class="text-[9px] font-bold uppercase tracking-widest text-slate-500">Max Age</Label>
                                            <Input id="max_age" v-model="form.maximum_age" type="number" class="h-10 rounded-xl border-slate-200 text-xs" />
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="is_active" class="text-[9px] font-bold uppercase tracking-widest text-slate-500">Presence</Label>
                                        <select id="is_active" v-model="form.is_active" class="flex h-10 w-full rounded-xl border border-slate-200 bg-white px-4 text-xs transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-800 dark:bg-slate-900">
                                            <option :value="true">Active & Visible</option>
                                            <option :value="false">Archived / Hidden</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50/50 px-6 py-4 dark:border-slate-800 dark:bg-slate-800/30">
                                <Button type="button" variant="ghost" class="text-[10px] font-bold uppercase tracking-tight" as-child>
                                    <Link href="/grades">Cancel Request</Link>
                                </Button>
                                <Button 
                                    type="submit" 
                                    :disabled="form.processing"
                                    class="h-10 rounded-xl bg-blue-600 px-8 text-[10px] font-bold uppercase tracking-tight text-white shadow-lg shadow-blue-500/20 transition-all hover:bg-blue-700 active:scale-95"
                                >
                                    <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                                    <PlusCircle v-else class="mr-2 h-4 w-4" />
                                    Commit Provision
                                </Button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Info Sidebar -->
                <div class="space-y-6">
                    <div class="rounded-2xl border border-blue-100 bg-blue-50/30 p-5 dark:border-blue-500/20 dark:bg-blue-500/5">
                        <div class="mb-4 flex h-8 w-8 items-center justify-center rounded-xl bg-blue-500 text-white shadow-lg shadow-blue-500/30">
                            <ShieldCheck class="h-4 w-4" />
                        </div>
                        <h3 class="mb-2 text-xs font-extrabold tracking-tight text-slate-900 dark:text-white uppercase italic">System Architecture</h3>
                        <p class="text-[10px] leading-relaxed text-slate-600 dark:text-slate-400">
                            Grades form the structural backbone of your institution. {{ form.is_bulk ? 'Bulk provisioning automatically sequences levels.' : 'Individual grades allow for specific constraints like age limits.' }}
                        </p>
                        <ul class="mt-4 space-y-2">
                            <li class="flex items-center gap-2 text-[9px] font-semibold text-slate-500 uppercase">
                                <CheckCircle2 class="h-3 w-3 text-blue-500" />
                                Automatic Progression Mapping
                            </li>
                            <li class="flex items-center gap-2 text-[9px] font-semibold text-slate-500 uppercase">
                                <CheckCircle2 class="h-3 w-3 text-blue-500" />
                                Enrollment Capacity Validation
                            </li>
                        </ul>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900/50">
                        <h4 class="mb-4 text-[9px] font-bold uppercase tracking-widest text-slate-400">Quick Reference</h4>
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600">
                                    <BookOpen class="h-3.5 w-3.5" />
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-slate-900 dark:text-white">CBC Standards</p>
                                    <p class="text-[9px] text-slate-500">Kenya Curriculum compliant</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600">
                                    <Calendar class="h-3.5 w-3.5" />
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-slate-900 dark:text-white">Academic Cycle</p>
                                    <p class="text-[9px] text-slate-500">Linked to 2024 active year</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.italic {
    font-style: italic;
}
</style>
