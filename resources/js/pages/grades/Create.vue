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

const architectureItems = [
    'Automatic Progression', 
    'Enrollment Validation', 
    'Asset Synchronization'
];

const operationalRefs = [
    { label: 'CBC Compliant', sub: 'National Standards', icon: BookOpen },
    { label: 'Sync Enabled', sub: '2024 Academic Cycle', icon: Calendar }
];
</script>

<template>
    <Head title="Provision Grade" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-[1000px] animate-in space-y-6 p-4 pb-10 duration-700 fade-in slide-in-from-bottom-4 sm:p-6 sm:pb-20 md:p-8">
            <!-- Header Section -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between px-1">
                <div class="flex flex-col gap-0.5">
                    <div class="mb-1 flex items-center gap-2 text-[10px] font-bold tracking-tight text-muted-foreground uppercase opacity-70">
                        <Milestone class="h-3 w-3" />
                        <ChevronRight class="h-2.5 w-2.5 opacity-50" />
                        <span>Academic</span>
                        <ChevronRight class="h-2.5 w-2.5 opacity-50" />
                        <span>Structure</span>
                        <ChevronRight class="h-2.5 w-2.5 opacity-50" />
                        <span class="text-primary">Provision</span>
                    </div>
                    <h1 class="text-xl font-black tracking-tight text-foreground sm:text-2xl uppercase italic">Provision Grade</h1>
                    <p class="text-[11px] font-medium text-muted-foreground">Architecting institutional academic nodes for global synchronization.</p>
                </div>

                <Link
                    href="/grades"
                    class="inline-flex h-9 items-center justify-center rounded-xl border border-border bg-background px-4 text-[10px] font-bold tracking-widest text-foreground uppercase transition-all hover:bg-muted shadow-sm"
                >
                    <ArrowLeft class="mr-2 h-3.5 w-3.5 opacity-70" />
                    Back to Matrix
                </Link>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Main Form Area -->
                <div class="lg:col-span-2 space-y-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Mode Selector Card -->
                        <div class="group relative overflow-hidden rounded-2xl border border-border bg-card p-4 shadow-sm transition-all hover:border-primary/20">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                        <Zap class="h-4 w-4" />
                                    </div>
                                    <div class="space-y-0.5">
                                        <h3 class="text-[10px] font-black tracking-widest uppercase text-foreground">Provisioning Strategy</h3>
                                        <p class="text-[9px] font-bold text-muted-foreground uppercase opacity-50">Bulk or Individual Node Instantiation</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4 px-3 py-1.5 rounded-xl bg-muted/50 border border-border/50">
                                    <span class="text-[9px] font-black uppercase tracking-widest transition-colors" :class="form.is_bulk ? 'text-primary' : 'text-muted-foreground opacity-40'">Bulk Mode</span>
                                    <Switch v-model:checked="form.is_bulk" class="h-4 w-8" />
                                </div>
                            </div>
                        </div>

                        <!-- Form Content Card -->
                        <div class="rounded-3xl border border-border bg-card shadow-sm overflow-hidden">
                            <div class="border-b border-border/50 bg-muted/10 px-6 py-3">
                                <h3 class="flex items-center gap-2 text-[10px] font-bold tracking-widest uppercase text-muted-foreground">
                                    <Layers class="h-3.5 w-3.5 text-primary" />
                                    {{ form.is_bulk ? 'Batch Node Configuration' : 'Single Grade Parameters' }}
                                </h3>
                            </div>

                            <div class="p-6">
                                <div v-if="form.is_bulk" class="grid gap-6 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <Label class="ml-1 text-[9px] font-bold uppercase tracking-widest text-muted-foreground opacity-60">Base Designation</Label>
                                        <Input v-model="form.base_name" placeholder="Grade" class="h-10 rounded-xl border-border bg-muted/10 text-xs font-bold uppercase transition-all focus:bg-background" />
                                        <InputError :message="form.errors.base_name" />
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="space-y-2">
                                            <Label class="ml-1 text-[9px] font-bold uppercase tracking-widest text-muted-foreground opacity-60">Start Offset</Label>
                                            <Input v-model="form.start_level" type="number" class="h-10 rounded-xl border-border bg-muted/10 text-xs font-bold transition-all focus:bg-background" />
                                        </div>
                                        <div class="space-y-2">
                                            <Label class="ml-1 text-[9px] font-bold uppercase tracking-widest text-muted-foreground opacity-60">End Limit</Label>
                                            <Input v-model="form.end_level" type="number" class="h-10 rounded-xl border-border bg-muted/10 text-xs font-bold transition-all focus:bg-background" />
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <Label class="ml-1 text-[9px] font-bold uppercase tracking-widest text-muted-foreground opacity-60">Curriculum Category</Label>
                                        <Input v-model="form.category" class="h-10 rounded-xl border-border bg-muted/10 text-xs font-bold uppercase transition-all focus:bg-background" />
                                        <InputError :message="form.errors.category" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label class="ml-1 text-[9px] font-bold uppercase tracking-widest text-muted-foreground opacity-60">Initial Protocol State</Label>
                                        <select v-model="form.is_active" class="flex h-10 w-full rounded-xl border border-border bg-muted/10 px-4 text-[10px] font-bold uppercase outline-none transition-all focus:bg-background focus:ring-2 focus:ring-primary/5">
                                            <option :value="true">Active & Synchronized</option>
                                            <option :value="false">Archived / Dormant</option>
                                        </select>
                                    </div>
                                </div>

                                <div v-else class="grid gap-6 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <Label class="ml-1 text-[9px] font-bold uppercase tracking-widest text-muted-foreground opacity-60">Full Designation *</Label>
                                        <Input v-model="form.name" placeholder="Grade 9" required class="h-10 rounded-xl border-border bg-muted/10 text-xs font-bold uppercase transition-all focus:bg-background" />
                                        <InputError :message="form.errors.name" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label class="ml-1 text-[9px] font-bold uppercase tracking-widest text-muted-foreground opacity-60">System Alias (Code) *</Label>
                                        <Input v-model="form.code" placeholder="G9" required class="h-10 rounded-xl border-border bg-muted/10 text-xs font-black tracking-widest text-primary uppercase transition-all focus:bg-background" />
                                        <InputError :message="form.errors.code" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label class="ml-1 text-[9px] font-bold uppercase tracking-widest text-muted-foreground opacity-60">Progression Index</Label>
                                        <Input v-model="form.level_order" type="number" class="h-10 rounded-xl border-border bg-muted/10 text-xs font-bold transition-all focus:bg-background" />
                                        <InputError :message="form.errors.level_order" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label class="ml-1 text-[9px] font-bold uppercase tracking-widest text-muted-foreground opacity-60">Category Tag</Label>
                                        <Input v-model="form.category" class="h-10 rounded-xl border-border bg-muted/10 text-xs font-bold uppercase transition-all focus:bg-background" />
                                        <InputError :message="form.errors.category" />
                                    </div>
                                    <div class="grid grid-cols-2 gap-4 text-xs">
                                        <div class="space-y-2 text-xs">
                                            <Label class="ml-1 text-[9px] font-bold uppercase tracking-widest text-muted-foreground opacity-60">Min Age</Label>
                                            <Input v-model="form.minimum_age" type="number" class="h-10 rounded-xl border-border bg-muted/10 text-xs font-bold transition-all focus:bg-background" />
                                        </div>
                                        <div class="space-y-2 text-xs">
                                            <Label class="ml-1 text-[9px] font-bold uppercase tracking-widest text-muted-foreground opacity-60">Max Age</Label>
                                            <Input v-model="form.maximum_age" type="number" class="h-10 rounded-xl border-border bg-muted/10 text-xs font-bold transition-all focus:bg-background" />
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <Label class="ml-1 text-[9px] font-bold uppercase tracking-widest text-muted-foreground opacity-60">State</Label>
                                        <select v-model="form.is_active" class="flex h-10 w-full rounded-xl border border-border bg-muted/10 px-4 text-[10px] font-bold uppercase outline-none transition-all focus:bg-background focus:ring-2 focus:ring-primary/5">
                                            <option :value="true">Active Synchronization</option>
                                            <option :value="false">Dormant / Archived</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-3 border-t border-border/50 bg-muted/10 px-8 py-4">
                                <Button type="button" variant="ghost" class="h-10 text-[9px] font-black uppercase tracking-[0.2em] text-muted-foreground hover:bg-muted" as-child>
                                    <Link href="/grades">Abort Provisioning</Link>
                                </Button>
                                <Button 
                                    type="submit" 
                                    :disabled="form.processing"
                                    class="h-10 rounded-xl bg-primary px-8 text-[9px] font-black uppercase tracking-[0.2em] text-white shadow-xl shadow-primary/20 transition-all hover:scale-[1.02] active:scale-95"
                                >
                                    <Loader2 v-if="form.processing" class="mr-3 h-4 w-4 animate-spin" />
                                    <Database v-else class="mr-3 h-4 w-4 text-white" />
                                    Commit Node
                                </Button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Info Sidebar -->
                <div class="space-y-6">
                    <div class="rounded-[2rem] border border-primary/10 bg-primary/5 p-6 space-y-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary text-white shadow-lg shadow-primary/30">
                            <ShieldCheck class="h-5 w-5" />
                        </div>
                        <div class="space-y-2">
                             <h3 class="text-xs font-black tracking-widest text-foreground uppercase italic underline decoration-primary/30 decoration-2 underline-offset-4">Core Architecture</h3>
                            <p class="text-[10px] leading-relaxed font-bold text-muted-foreground uppercase opacity-60">
                                Grades form the structural backbone of your institution. {{ form.is_bulk ? 'Bulk provisioning automatically sequences levels.' : 'Individual grades allow for specific constraints.' }}
                            </p>
                        </div>
                        <div class="space-y-2 pt-2">
                            <div v-for="item in architectureItems" :key="item" class="flex items-center gap-3">
                                <CheckCircle2 class="h-3 w-3 text-primary" />
                                <span class="text-[9px] font-black tracking-widest text-primary uppercase">{{ item }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-border bg-card p-6">
                        <h4 class="mb-5 text-[9px] font-black uppercase tracking-[0.2em] text-muted-foreground opacity-40">Operational Reference</h4>
                        <div class="space-y-5">
                            <div v-for="(ref, idx) in operationalRefs" :key="idx" class="flex items-center gap-4">
                                <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-muted text-muted-foreground">
                                    <component :is="ref.icon" class="h-4 w-4" />
                                </div>
                                <div class="space-y-0.5">
                                    <p class="text-[10px] font-black text-foreground uppercase">{{ ref.label }}</p>
                                    <p class="text-[9px] font-bold text-muted-foreground uppercase opacity-50">{{ ref.sub }}</p>
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
