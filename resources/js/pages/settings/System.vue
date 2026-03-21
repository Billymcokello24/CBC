<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Settings, Save, Database, Cpu, Activity, RefreshCw, Zap, Server, Shield } from 'lucide-vue-next';
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
        <div class="flex h-full flex-1 flex-col gap-6 p-6 font-pulsar">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-900/10">
                        <Settings class="h-6 w-6 text-slate-900" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900">System Engineering</h1>
                        <p class="text-muted-foreground">Monitor infrastructure health, database performance and global system keys</p>
                    </div>
                </div>
                <div class="flex gap-2">
                     <Button variant="outline" class="font-pulsar"><RefreshCw class="mr-2 h-4 w-4" />Sync Core</Button>
                     <Button class="bg-slate-900 hover:bg-slate-800 font-pulsar">Update Settings</Button>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2 space-y-6">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl border bg-card p-6 shadow-sm flex items-center gap-4">
                            <div class="h-10 w-10 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-600">
                                <Server class="h-6 w-6" />
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Platform Status</p>
                                <p class="text-lg font-bold text-emerald-600">OPTIMAL</p>
                            </div>
                        </div>
                        <div class="rounded-2xl border bg-card p-6 shadow-sm flex items-center gap-4">
                            <div class="h-10 w-10 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-600">
                                <Zap class="h-6 w-6" />
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Latency Index</p>
                                <p class="text-lg font-bold text-blue-600">42ms Response</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border bg-card shadow-sm overflow-hidden">
                        <div class="bg-slate-50 px-6 py-4 border-b flex items-center justify-between">
                            <h3 class="font-bold flex items-center gap-2 text-slate-700 font-pulsar"><Database class="h-4 w-4" /> Infrastructure Metadata</h3>
                            <Badge variant="outline" class="text-[9px] font-black border-slate-200 text-slate-500 uppercase tracking-widest px-2 py-0 border-0">V2.4.0</Badge>
                        </div>
                        <div class="p-6 space-y-4">
                             <div class="flex items-center justify-between border-b pb-4 last:border-0 last:pb-0">
                                <div>
                                    <p class="text-sm font-bold text-slate-900 font-pulsar">Local Storage Strategy</p>
                                    <p class="text-xs text-muted-foreground italic">Physical disk utilization for student documents</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-black text-slate-700">84.2 GB / 250 GB</p>
                                    <div class="h-1.5 w-32 bg-slate-100 rounded-full mt-2 overflow-hidden border">
                                         <div class="h-full bg-slate-400 w-[35%]"></div>
                                    </div>
                                </div>
                             </div>
                             <div class="flex items-center justify-between border-b pb-4 last:border-0 last:pb-0">
                                <div>
                                    <p class="text-sm font-bold text-slate-900 font-pulsar">Database Engine</p>
                                    <p class="text-xs text-muted-foreground italic">MySQL 8.0 Logic Engine</p>
                                </div>
                                <Badge class="bg-indigo-500 text-[10px] font-bold uppercase tracking-widest border-0">STABLE</Badge>
                             </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-2xl border bg-card p-6 shadow-sm border-t-4 border-t-slate-800">
                        <h3 class="font-bold mb-4 flex items-center gap-2"><Cpu class="h-4 w-4 text-slate-900" /> Computing Logic</h3>
                        <div class="space-y-6">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">Worker Nodes</span>
                                <span class="text-sm font-black text-slate-900">4 ACTIVE</span>
                            </div>
                            <div class="flex items-center justify-between border-t pt-4">
                                <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">Cache Store</span>
                                <span class="text-sm font-black text-slate-900">Redis 6.2</span>
                            </div>
                            <div class="flex items-center justify-between border-t pt-4">
                                <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">Encryption</span>
                                <span class="text-sm font-black text-slate-900 flex items-center gap-1.5"><Shield class="h-3 w-3 text-emerald-500" /> AES-256</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="rounded-2xl border bg-slate-50 p-6 flex flex-col items-center justify-center text-center">
                         <Activity class="h-12 w-12 text-slate-300 mb-4 animate-pulse" />
                         <h4 class="font-bold text-slate-700 mb-1">Live Telemetry</h4>
                         <p class="text-[10px] text-slate-400 uppercase tracking-widest font-black">Connected to Pulsar Engine</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
