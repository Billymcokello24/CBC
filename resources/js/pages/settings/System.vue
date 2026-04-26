<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Settings,
    Save,
    Database,
    Cpu,
    Activity,
    RefreshCw,
    Zap,
    Server,
    Shield,
    Home,
    ChevronRight,
} from 'lucide-vue-next';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Settings', href: '/settings/school' },
    { title: 'System Diagnostic', href: '/settings/system' },
];
</script>

<template>
    <Head title="System Diagnostics" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-[1600px] animate-in space-y-6 p-4 pb-10 duration-700 fade-in slide-in-from-bottom-4 sm:space-y-8 sm:p-6 sm:pb-20 md:p-8">
            <div class="flex flex-col gap-4 border-b border-sidebar-border pb-8 md:flex-row md:items-end md:justify-between px-1">
                <div class="flex flex-col gap-1">
                    <div class="mb-1 flex items-center gap-2 text-xs text-muted-foreground sm:text-xs">
                        <Home class="h-3 w-3 sm:h-3.5 sm:w-3.5" />
                        <ChevronRight class="h-2.5 w-2.5 sm:h-3 sm:w-3" />
                        <span class="font-medium tracking-tight text-foreground uppercase">Settings</span>
                        <ChevronRight class="h-2.5 w-2.5 sm:h-3 sm:w-3" />
                        <span class="font-medium tracking-tight text-foreground uppercase">Diagnostics</span>
                    </div>
                    <h1 class="text-2xl leading-tight font-bold tracking-tight text-foreground sm:text-3xl">
                        System Engineering
                    </h1>
                    <p class="text-sm text-muted-foreground sm:text-sm">
                        Monitor infrastructure health, database performance and global system keys
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" class="font-pulsar"
                        ><RefreshCw class="mr-2 h-4 w-4" />Sync Core</Button
                    >
                    <Button class="font-pulsar bg-slate-900 hover:bg-slate-800"
                        >Update Settings</Button
                    >
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-2">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div
                            class="flex items-center gap-4 rounded-2xl border bg-card p-6 shadow-sm"
                        >
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600"
                            >
                                <Server class="h-6 w-6" />
                            </div>
                            <div>
                                <p
                                    class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                                >
                                    Platform Status
                                </p>
                                <p class="text-lg font-bold text-emerald-600">
                                    OPTIMAL
                                </p>
                            </div>
                        </div>
                        <div
                            class="flex items-center gap-4 rounded-2xl border bg-card p-6 shadow-sm"
                        >
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-500/10 text-blue-600"
                            >
                                <Zap class="h-6 w-6" />
                            </div>
                            <div>
                                <p
                                    class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                                >
                                    Latency Index
                                </p>
                                <p class="text-lg font-bold text-blue-600">
                                    42ms Response
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="overflow-hidden rounded-2xl border bg-card shadow-sm"
                    >
                        <div
                            class="flex items-center justify-between border-b bg-slate-50 px-6 py-4"
                        >
                            <h3
                                class="font-pulsar flex items-center gap-2 font-bold text-slate-700"
                            >
                                <Database class="h-4 w-4" /> Infrastructure
                                Metadata
                            </h3>
                            <Badge
                                variant="outline"
                                class="border-0 border-slate-200 px-2 py-0 text-xs font-bold tracking-tight text-slate-500 uppercase"
                                >V2.4.0</Badge
                            >
                        </div>
                        <div class="space-y-4 p-6">
                            <div
                                class="flex items-center justify-between border-b pb-4 last:border-0 last:pb-0"
                            >
                                <div>
                                    <p
                                        class="font-pulsar text-sm font-bold text-slate-900"
                                    >
                                        Local Storage Strategy
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        Physical disk utilization for student
                                        documents
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-slate-700">
                                        84.2 GB / 250 GB
                                    </p>
                                    <div
                                        class="mt-2 h-1.5 w-32 overflow-hidden rounded-full border bg-slate-100"
                                    >
                                        <div
                                            class="h-full w-[35%] bg-slate-400"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="flex items-center justify-between border-b pb-4 last:border-0 last:pb-0"
                            >
                                <div>
                                    <p
                                        class="font-pulsar text-sm font-bold text-slate-900"
                                    >
                                        Database Engine
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        MySQL 8.0 Logic Engine
                                    </p>
                                </div>
                                <Badge
                                    class="border-0 bg-indigo-500 text-xs font-bold tracking-tight uppercase"
                                    >STABLE</Badge
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div
                        class="rounded-2xl border border-t-4 border-t-slate-800 bg-card p-6 shadow-sm"
                    >
                        <h3 class="mb-4 flex items-center gap-2 font-bold">
                            <Cpu class="h-4 w-4 text-slate-900" /> Computing
                            Logic
                        </h3>
                        <div class="space-y-6">
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-xs font-bold tracking-tight text-slate-500 uppercase"
                                    >Worker Nodes</span
                                >
                                <span class="text-sm font-bold text-slate-900"
                                    >4 ACTIVE</span
                                >
                            </div>
                            <div
                                class="flex items-center justify-between border-t pt-4"
                            >
                                <span
                                    class="text-xs font-bold tracking-tight text-slate-500 uppercase"
                                    >Cache Store</span
                                >
                                <span class="text-sm font-bold text-slate-900"
                                    >Redis 6.2</span
                                >
                            </div>
                            <div
                                class="flex items-center justify-between border-t pt-4"
                            >
                                <span
                                    class="text-xs font-bold tracking-tight text-slate-500 uppercase"
                                    >Encryption</span
                                >
                                <span
                                    class="flex items-center gap-1.5 text-sm font-bold text-slate-900"
                                    ><Shield class="h-3 w-3 text-emerald-500" />
                                    AES-256</span
                                >
                            </div>
                        </div>
                    </div>

                    <div
                        class="flex flex-col items-center justify-center rounded-2xl border bg-slate-50 p-6 text-center"
                    >
                        <Activity
                            class="mb-4 h-12 w-12 animate-pulse text-slate-300"
                        />
                        <h4 class="mb-1 font-bold text-slate-700">
                            Live Telemetry
                        </h4>
                        <p
                            class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                        >
                            Connected to Pulsar Engine
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
