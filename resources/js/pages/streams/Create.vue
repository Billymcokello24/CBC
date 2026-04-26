<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Loader2,
    PlusSquare,
    Zap,
    Database,
    ShieldCheck,
    SearchCode,
    Activity,
    ChevronDown,
    Plus,
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
    { title: 'Intelligence Hub', href: '/dashboard' },
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
        <div
            class="mx-auto flex h-full max-w-[1000px] flex-1 animate-in flex-col gap-12 p-4 pb-20 duration-700 fade-in slide-in-from-bottom-4 sm:p-6 sm:pb-32 md:p-8"
        >
            <!-- Strategic Header -->
            <div
                class="flex flex-col gap-6 px-1 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-6">
                    <Button
                        variant="outline"
                        size="icon"
                        as-child
                        class="h-12 w-12 rounded-2xl border-border bg-card shadow-sm transition-all hover:bg-muted"
                    >
                        <Link href="/streams">
                            <ArrowLeft class="h-5 w-5 text-muted-foreground" />
                        </Link>
                    </Button>
                    <div class="space-y-1">
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl font-black uppercase">
                                Provision academic channel
                            </h1>
                            <Badge variant="outline" class="rounded-lg border-primary/20 bg-primary/5 px-2.5 py-0.5 text-[10px] font-bold tracking-widest text-primary uppercase shadow-sm">Infrastructure</Badge>
                        </div>
                        <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Architecting new operational units for academic synchronization</p>
                    </div>
                </div>
            </div>

            <!-- Provisioning Hub -->
            <div class="overflow-hidden rounded-[2.5rem] border border-border bg-card shadow-sm transition-all hover:border-primary/20">
                <div class="flex flex-col md:flex-row items-center justify-between border-b border-border/50 bg-muted/10 px-10 py-6 gap-6">
                    <div class="flex items-center gap-4">
                        <Zap class="h-5 w-5 text-primary" />
                        <div class="space-y-0.5">
                            <h2 class="text-sm font-bold tracking-tight text-foreground uppercase">Bulk instantiation</h2>
                            <p class="text-[9px] font-bold tracking-widest text-muted-foreground uppercase opacity-50">Enable high-volume channel creation</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 px-6 py-3 rounded-2xl bg-card border border-border/50 shadow-sm">
                        <span class="text-[10px] font-black uppercase tracking-widest transition-colors" :class="form.is_bulk ? 'text-primary' : 'text-muted-foreground opacity-40'">Enable Multi-Node Mode</span>
                        <Switch v-model:checked="form.is_bulk" class="data-[state=checked]:bg-primary" />
                    </div>
                </div>

                <form @submit.prevent="submit" class="p-10 space-y-10">
                    <!-- Bulk Node Logic -->
                    <div v-if="form.is_bulk" class="animate-in fade-in slide-in-from-top-4 duration-500 space-y-10">
                        <div class="space-y-4">
                            <Label class="ml-1 text-[10px] font-black tracking-[0.2em] text-muted-foreground uppercase opacity-60">Channel Identifier Matrix (Comma Separated) *</Label>
                            <textarea
                                v-model="form.names"
                                placeholder="NORTH, SOUTH, EAST, WEST..."
                                class="flex min-h-[160px] w-full rounded-2xl border border-border bg-muted/20 px-6 py-5 text-sm font-black tracking-widest uppercase transition-all outline-none focus:bg-background focus:ring-4 focus:ring-primary/5"
                                required
                            ></textarea>
                            <InputError :message="form.errors.names" />
                        </div>

                        <div class="grid gap-10 md:grid-cols-2">
                            <div class="space-y-3">
                                <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Global Capacity per Node</Label>
                                <Input v-model="form.capacity" type="number" min="1" placeholder="40" class="h-14 rounded-2xl border-border bg-muted/20 px-6 text-sm font-bold focus:bg-background" />
                                <InputError :message="form.errors.capacity" />
                            </div>
                            <div class="space-y-3">
                                <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Node State</Label>
                                <div class="relative">
                                    <select v-model="form.is_active" class="h-14 w-full cursor-pointer appearance-none rounded-2xl border border-border bg-muted/20 px-6 text-sm font-bold uppercase outline-none focus:bg-background">
                                        <option :value="true">Active Synchronization</option>
                                        <option :value="false">Dormant Node</option>
                                    </select>
                                    <ChevronDown class="pointer-events-none absolute right-6 top-1/2 h-5 w-5 -translate-y-1/2 text-muted-foreground/30" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Single Node Logic -->
                    <div v-else class="animate-in fade-in slide-in-from-bottom-4 duration-500 space-y-10">
                        <div class="grid gap-10 md:grid-cols-2">
                            <div class="space-y-3">
                                <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Strategic Channel Name *</Label>
                                <Input v-model="form.name" placeholder="NORTH" required class="h-14 rounded-2xl border-border bg-muted/20 px-6 text-sm font-black tracking-widest uppercase focus:bg-background" />
                                <InputError :message="form.errors.name" />
                            </div>
                            <div class="space-y-3">
                                <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Protocol identifier (Code) *</Label>
                                <Input v-model="form.code" placeholder="NRTH" required class="h-14 rounded-2xl border-border bg-muted/20 px-6 text-sm font-black tracking-[0.2em] text-primary uppercase focus:bg-background" />
                                <InputError :message="form.errors.code" />
                            </div>
                            <div class="space-y-3">
                                <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Population Capacity Node</Label>
                                <Input v-model="form.capacity" type="number" min="1" placeholder="40" class="h-14 rounded-2xl border-border bg-muted/20 px-6 text-sm font-bold focus:bg-background" />
                                <InputError :message="form.errors.capacity" />
                            </div>
                             <div class="space-y-3">
                                <Label class="ml-1 text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Pulse Protocol State</Label>
                                <div class="relative">
                                    <select v-model="form.is_active" class="h-14 w-full cursor-pointer appearance-none rounded-2xl border border-border bg-muted/20 px-6 text-sm font-bold uppercase outline-none focus:bg-background">
                                        <option :value="true">Active Synchronization</option>
                                        <option :value="false">Dormant Node</option>
                                    </select>
                                    <ChevronDown class="pointer-events-none absolute right-6 top-1/2 h-5 w-5 -translate-y-1/2 text-muted-foreground/30" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Final Infrastructure Actions -->
                    <div class="flex flex-wrap items-center justify-end gap-6 pt-6 border-t border-border/50">
                        <Button variant="ghost" class="h-14 rounded-[1.5rem] px-10 text-[10px] font-bold tracking-[0.2em] text-muted-foreground uppercase hover:bg-muted" as-child>
                            <Link href="/streams">Abort Provisioning</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing" class="h-14 rounded-[1.5rem] bg-primary px-14 text-[10px] font-bold tracking-[0.2em] text-white uppercase shadow-2xl shadow-primary/30 transition-all hover:scale-[1.05] active:scale-[0.98]">
                            <Loader2 v-if="form.processing" class="mr-3 h-5 w-5 animate-spin" />
                            <Database v-else class="mr-3 h-5 w-5" />
                            Synchronize Infrastructure
                        </Button>
                    </div>
                </form>
            </div>

            <!-- Strategic Analytics Hint -->
            <div class="flex items-center gap-6 rounded-[2rem] border border-primary/10 bg-primary/5 p-8">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white shadow-sm ring-1 ring-primary/10 transition-transform group-hover:rotate-12"><Activity class="h-6 w-6 text-primary" /></div>
                <div class="space-y-1">
                    <p class="text-[10px] font-black tracking-widest text-primary uppercase">Provisioning Analytics</p>
                    <p class="text-xs font-bold text-muted-foreground leading-relaxed uppercase opacity-70">Infrastructure nodes are architected at the global level and synchronized with academic streams across all grade modules.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
