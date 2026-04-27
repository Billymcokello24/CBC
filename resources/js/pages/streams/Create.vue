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
</script>

<template>
    <Head title="Channel Provisioning Protocol" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-[1200px] animate-in space-y-8 p-4 pb-10 duration-700 fade-in slide-in-from-bottom-4 sm:p-6 sm:pb-20 md:p-8">
            <!-- Strategic Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between px-1">
                <div class="flex flex-col gap-1">
                    <div class="mb-1 flex items-center gap-2 text-[10px] font-bold tracking-tight text-muted-foreground uppercase">
                        <Building2 class="h-3 w-3" />
                        <ChevronRight class="h-2.5 w-2.5 opacity-50" />
                        <span>Academic</span>
                        <ChevronRight class="h-2.5 w-2.5 opacity-50" />
                        <span>Streams</span>
                        <ChevronRight class="h-2.5 w-2.5 opacity-50" />
                        <span class="text-foreground">Provision</span>
                    </div>
                    <h1 class="text-xl leading-tight font-extrabold tracking-tight text-foreground sm:text-2xl italic uppercase">Provision academic channel</h1>
                    <p class="text-xs text-muted-foreground">Architecting new operational units for academic synchronization.</p>
                </div>

                <Link
                    href="/streams"
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
                        <div class="group relative overflow-hidden rounded-2xl border border-border bg-white p-5 shadow-sm transition-all hover:shadow-md dark:border-slate-800 dark:bg-slate-900/50">
                            <div class="flex items-start justify-between">
                                <div class="space-y-1">
                                    <h3 class="text-xs font-bold tracking-tight uppercase text-slate-900 dark:text-white">Provisioning Strategy</h3>
                                    <p class="text-[10px] text-muted-foreground text-slate-500">Enable high-volume channel creation with multi-node mode.</p>
                                </div>
                                <div class="flex items-center gap-4 px-4 py-2 rounded-xl bg-slate-50 border border-slate-100 dark:bg-slate-800 dark:border-slate-700">
                                    <span class="text-[9px] font-black uppercase tracking-widest transition-colors" :class="form.is_bulk ? 'text-primary' : 'text-muted-foreground opacity-40'">Bulk Mode</span>
                                    <Switch v-model:checked="form.is_bulk" class="data-[state=checked]:bg-primary h-5 w-9" />
                                </div>
                            </div>
                        </div>

                        <!-- Form Content Card -->
                        <div class="rounded-2xl border border-border bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900/50 overflow-hidden">
                            <div class="border-b border-border/50 bg-slate-50/50 px-8 py-3 dark:border-slate-800 dark:bg-slate-800/30">
                                <h3 class="flex items-center gap-2 text-[10px] font-bold tracking-tight uppercase text-slate-700 dark:text-slate-300">
                                    <Zap class="h-3.5 w-3.5 text-primary" />
                                    {{ form.is_bulk ? 'Batch Instantiation' : 'Single Node Parameters' }}
                                </h3>
                            </div>

                            <div class="p-8 space-y-8">
                                <div v-if="form.is_bulk" class="animate-in fade-in slide-in-from-top-4 duration-500 space-y-8">
                                    <div class="space-y-3">
                                        <Label class="ml-1 text-[9px] font-black tracking-[0.2em] text-muted-foreground uppercase opacity-60">Channel Identifier Matrix (Comma Separated) *</Label>
                                        <textarea
                                            v-model="form.names"
                                            placeholder="NORTH, SOUTH, EAST, WEST..."
                                            class="flex min-h-[120px] w-full rounded-2xl border border-border bg-muted/20 px-6 py-4 text-xs font-black tracking-widest uppercase transition-all outline-none focus:bg-background focus:ring-4 focus:ring-primary/5"
                                            required
                                        ></textarea>
                                        <InputError :message="form.errors.names" />
                                    </div>

                                    <div class="grid gap-8 md:grid-cols-2">
                                        <div class="space-y-2">
                                            <Label class="ml-1 text-[9px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Global Capacity per Node</Label>
                                            <Input v-model="form.capacity" type="number" min="1" placeholder="40" class="h-10 rounded-xl border-border bg-muted/20 px-4 text-xs font-bold focus:bg-background" />
                                            <InputError :message="form.errors.capacity" />
                                        </div>
                                        <div class="space-y-2">
                                            <Label class="ml-1 text-[9px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Node State</Label>
                                            <div class="relative">
                                                <select v-model="form.is_active" class="h-10 w-full cursor-pointer appearance-none rounded-xl border border-border bg-muted/20 px-4 text-xs font-bold uppercase outline-none focus:bg-background">
                                                    <option :value="true">Active Synchronization</option>
                                                    <option :value="false">Dormant Node</option>
                                                </select>
                                                <ChevronDown class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/30" />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div v-else class="animate-in fade-in slide-in-from-bottom-4 duration-500 space-y-8">
                                    <div class="grid gap-8 md:grid-cols-2">
                                        <div class="space-y-2">
                                            <Label class="ml-1 text-[9px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Strategic Channel Name *</Label>
                                            <Input v-model="form.name" placeholder="NORTH" required class="h-10 rounded-xl border-border bg-muted/20 px-4 text-xs font-black tracking-widest uppercase focus:bg-background" />
                                            <InputError :message="form.errors.name" />
                                        </div>
                                        <div class="space-y-2">
                                            <Label class="ml-1 text-[9px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Protocol identifier (Code) *</Label>
                                            <Input v-model="form.code" placeholder="NRTH" required class="h-10 rounded-xl border-border bg-muted/20 px-4 text-xs font-black tracking-[0.2em] text-primary uppercase focus:bg-background" />
                                            <InputError :message="form.errors.code" />
                                        </div>
                                        <div class="space-y-2">
                                            <Label class="ml-1 text-[9px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Population Capacity Node</Label>
                                            <Input v-model="form.capacity" type="number" min="1" placeholder="40" class="h-10 rounded-xl border-border bg-muted/20 px-4 text-xs font-bold focus:bg-background" />
                                            <InputError :message="form.errors.capacity" />
                                        </div>
                                         <div class="space-y-2">
                                            <Label class="ml-1 text-[9px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Pulse Protocol State</Label>
                                            <div class="relative">
                                                <select v-model="form.is_active" class="h-10 w-full cursor-pointer appearance-none rounded-xl border border-border bg-muted/20 px-4 text-xs font-bold uppercase outline-none focus:bg-background">
                                                    <option :value="true">Active Synchronization</option>
                                                    <option :value="false">Dormant Node</option>
                                                </select>
                                                <ChevronDown class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/30" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-3 border-t border-border/50 bg-slate-50/50 px-8 py-4 dark:border-slate-800 dark:bg-slate-800/30">
                                <Button variant="ghost" class="h-10 rounded-xl px-6 text-[10px] font-bold tracking-[0.2em] text-muted-foreground uppercase hover:bg-muted" as-child>
                                    <Link href="/streams">Abort Provisioning</Link>
                                </Button>
                                <Button type="submit" :disabled="form.processing" class="h-10 rounded-xl bg-primary px-10 text-[10px] font-bold tracking-[0.2em] text-white uppercase shadow-lg shadow-primary/30 transition-all hover:scale-[1.02] active:scale-[0.98]">
                                    <Loader2 v-if="form.processing" class="mr-3 h-4 w-4 animate-spin" />
                                    <Database v-else class="mr-3 h-4 w-4" />
                                    Synchronize Infrastructure
                                </Button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Info Sidebar -->
                <div class="space-y-6">
                    <div class="rounded-2xl border border-primary/10 bg-primary/5 p-5">
                        <div class="mb-4 flex h-8 w-8 items-center justify-center rounded-xl bg-primary text-white shadow-lg shadow-primary/30">
                            <ShieldCheck class="h-4 w-4" />
                        </div>
                        <h3 class="mb-2 text-xs font-extrabold tracking-tight text-foreground uppercase italic">System Architecture</h3>
                        <p class="text-[10px] leading-relaxed text-muted-foreground uppercase opacity-70">
                            Infrastructure nodes are architected at the global level and synchronized with academic streams across all grade modules.
                        </p>
                        <ul class="mt-4 space-y-2">
                            <li class="flex items-center gap-2 text-[9px] font-black tracking-widest text-primary uppercase">
                                <CheckCircle2 class="h-3 w-3" />
                                Global Synchronization
                            </li>
                            <li class="flex items-center gap-2 text-[9px] font-black tracking-widest text-primary uppercase">
                                <CheckCircle2 class="h-3 w-3" />
                                Resource Allocation Hub
                            </li>
                        </ul>
                    </div>

                    <div class="rounded-2xl border border-border bg-white p-5 dark:border-slate-800 dark:bg-slate-900/50">
                        <h4 class="mb-4 text-[9px] font-bold uppercase tracking-widest text-muted-foreground opacity-60">Provisioning Analytics</h4>
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-muted text-muted-foreground">
                                    <Activity class="h-3.5 w-3.5" />
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-foreground">Node Health</p>
                                    <p class="text-[9px] text-muted-foreground">Active monitoring enabled</p>
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
