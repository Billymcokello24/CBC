<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Loader2,
    Database,
    ShieldCheck,
    Activity,
    ChevronDown,
    Plus,
    Zap,
    ChevronRight,
    Home,
    Building2,
    CheckCircle2
} from 'lucide-vue-next';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Academic', href: '/streams' },
    { title: 'Academic Streams', href: '/streams' },
    { title: 'Provisioning', href: '/streams/create' },
];

const form = useForm({
    name: '',
    code: '',
    capacity: '',
    is_active: true,
    is_bulk: false,
    names: '',
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        capacity: data.capacity ? Number(data.capacity) : null,
        is_active: Boolean(data.is_active),
    })).post('/streams', {
        preserveScroll: true,
    });
};

const architectureItems = [
    'Global Synchronization', 
    'Resource Allocation', 
    'Asset Monitoring'
];

const pulseItems = [
    { label: 'Node Health', sub: 'Active Monitoring', icon: Activity },
    { label: 'Global Matrix', sub: 'Synchronized Hub', icon: LayoutGrid }
];
</script>

<template>
    <Head title="Channel Provisioning" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-[1000px] animate-in space-y-6 p-4 pb-10 duration-700 fade-in slide-in-from-bottom-4 sm:p-6 sm:pb-20 md:p-8">
            <!-- Strategic Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between px-1">
                <div class="flex flex-col gap-0.5">
                    <div class="mb-1 flex items-center gap-2 text-[10px] font-bold tracking-tight text-muted-foreground uppercase opacity-70">
                        <Building2 class="h-3 w-3" />
                        <ChevronRight class="h-2.5 w-2.5 opacity-50" />
                        <span>Academic</span>
                        <ChevronRight class="h-2.5 w-2.5 opacity-50" />
                        <span>Infrastructure</span>
                        <ChevronRight class="h-2.5 w-2.5 opacity-50" />
                        <span class="text-primary">Provision</span>
                    </div>
                    <h1 class="text-xl font-black tracking-tight text-foreground sm:text-2xl uppercase italic">Provision Channel</h1>
                    <p class="text-[11px] font-medium text-muted-foreground">Architecting institutional operational units for academic synchronization.</p>
                </div>

                <Link
                    href="/streams"
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
                                    <Activity class="h-3.5 w-3.5 text-primary" />
                                    {{ form.is_bulk ? 'Batch Node Configuration' : 'Single Node Parameters' }}
                                </h3>
                            </div>

                            <div class="p-6">
                                <div v-if="form.is_bulk" class="animate-in fade-in slide-in-from-top-4 duration-500 space-y-6">
                                    <div class="space-y-2">
                                        <Label class="ml-1 text-[9px] font-black tracking-[0.2em] text-muted-foreground uppercase opacity-60">Channel Identifier Matrix (Comma Separated) *</Label>
                                        <textarea
                                            v-model="form.names"
                                            placeholder="NORTH, SOUTH, EAST, WEST..."
                                            class="flex min-h-[100px] w-full rounded-2xl border border-border bg-muted/10 px-6 py-4 text-xs font-black tracking-widest uppercase transition-all outline-none focus:bg-background focus:ring-4 focus:ring-primary/5"
                                            required
                                        ></textarea>
                                        <InputError :message="form.errors.names" />
                                    </div>

                                    <div class="grid gap-6 md:grid-cols-2">
                                        <div class="space-y-2">
                                            <Label class="ml-1 text-[9px] font-bold uppercase tracking-widest text-muted-foreground opacity-60">Global Capacity per Node</Label>
                                            <Input v-model="form.capacity" type="number" min="1" placeholder="40" class="h-10 rounded-xl border-border bg-muted/10 text-xs font-bold transition-all focus:bg-background" />
                                            <InputError :message="form.errors.capacity" />
                                        </div>
                                        <div class="space-y-2">
                                            <Label class="ml-1 text-[9px] font-bold uppercase tracking-widest text-muted-foreground opacity-60">Node Protocol State</Label>
                                            <div class="relative">
                                                <select v-model="form.is_active" class="h-10 w-full cursor-pointer appearance-none rounded-xl border border-border bg-muted/10 px-4 text-[10px] font-bold uppercase outline-none focus:bg-background transition-all">
                                                    <option :value="true">Active Synchronization</option>
                                                    <option :value="false">Dormant Node</option>
                                                </select>
                                                <ChevronDown class="pointer-events-none absolute right-4 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground/30" />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div v-else class="animate-in fade-in slide-in-from-bottom-4 duration-500 space-y-6">
                                    <div class="grid gap-6 md:grid-cols-2">
                                        <div class="space-y-2">
                                            <Label class="ml-1 text-[9px] font-bold uppercase tracking-widest text-muted-foreground opacity-60">Strategic Channel Name *</Label>
                                            <Input v-model="form.name" placeholder="NORTH" required class="h-10 rounded-xl border-border bg-muted/10 text-xs font-black tracking-widest uppercase focus:bg-background transition-all" />
                                            <InputError :message="form.errors.name" />
                                        </div>
                                        <div class="space-y-2">
                                            <Label class="ml-1 text-[9px] font-bold uppercase tracking-widest text-muted-foreground opacity-60">Protocol identifier (Code) *</Label>
                                            <Input v-model="form.code" placeholder="NRTH" required class="h-10 rounded-xl border-border bg-muted/10 text-xs font-black tracking-[0.2em] text-primary uppercase focus:bg-background transition-all" />
                                            <InputError :message="form.errors.code" />
                                        </div>
                                        <div class="space-y-2">
                                            <Label class="ml-1 text-[9px] font-bold uppercase tracking-widest text-muted-foreground opacity-60">Node Capacity</Label>
                                            <Input v-model="form.capacity" type="number" min="1" placeholder="40" class="h-10 rounded-xl border-border bg-muted/10 text-xs font-bold transition-all focus:bg-background" />
                                            <InputError :message="form.errors.capacity" />
                                        </div>
                                         <div class="space-y-2">
                                            <Label class="ml-1 text-[9px] font-bold uppercase tracking-widest text-muted-foreground opacity-60">Pulse Protocol State</Label>
                                            <div class="relative">
                                                <select v-model="form.is_active" class="h-10 w-full cursor-pointer appearance-none rounded-xl border border-border bg-muted/10 px-4 text-[10px] font-bold uppercase outline-none focus:bg-background transition-all">
                                                    <option :value="true">Active Synchronization</option>
                                                    <option :value="false">Dormant Node</option>
                                                </select>
                                                <ChevronDown class="pointer-events-none absolute right-4 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground/30" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-3 border-t border-border/50 bg-muted/10 px-8 py-4">
                                <Button variant="ghost" class="h-10 rounded-xl px-6 text-[9px] font-black uppercase tracking-[0.2em] text-muted-foreground hover:bg-muted" as-child>
                                    <Link href="/streams">Abort Provisioning</Link>
                                </Button>
                                <Button type="submit" :disabled="form.processing" class="h-10 rounded-xl bg-primary px-10 text-[9px] font-black uppercase tracking-[0.2em] text-white shadow-xl shadow-primary/20 transition-all hover:scale-[1.02] active:scale-[0.98]">
                                    <Loader2 v-if="form.processing" class="mr-3 h-4 w-4 animate-spin" />
                                    <Database v-else class="mr-3 h-4 w-4" />
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
                                Infrastructure nodes are architected at the global level and synchronized with academic streams across all grade modules.
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
                        <h4 class="mb-5 text-[9px] font-black uppercase tracking-[0.2em] text-muted-foreground opacity-40">System Pulse</h4>
                        <div class="space-y-5">
                            <div v-for="(ref, idx) in pulseItems" :key="idx" class="flex items-center gap-4">
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
