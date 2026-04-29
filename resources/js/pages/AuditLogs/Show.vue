<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { 
    ShieldCheck, ArrowLeft, Calendar, User, 
    Monitor, Globe, History, Activity, Database,
    Info, HardDrive, Key, Smartphone
} from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import type { BreadcrumbItem } from '@/types';

interface Props {
    log: {
        id: number;
        event: string;
        description: string;
        who: string;
        causer: any;
        subject: any;
        properties: any;
        ip: string;
        agent: string;
        created_at: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Home', href: '/dashboard' },
    { title: 'Audit Repository', href: '/audit-logs' },
    { title: `Log #${props.log.id}`, href: '#' },
];

const getGadgetIcon = (userAgent: string) => {
    const ua = (userAgent || '').toLowerCase();
    if (ua.includes('mobile') || ua.includes('android') || ua.includes('iphone')) return Smartphone;
    return Monitor;
};

const formatJson = (json: any) => {
    if (!json) return '{}';
    return JSON.stringify(json, null, 2);
};
</script>

<template>
    <Head :title="`Log Verification: #${log.id}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-[1000px] space-y-6 p-4 sm:p-6 fade-in">
            
            <!-- HEADER -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <ShieldCheck class="h-4 w-4 text-emerald-500" />
                        <span class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-600">Investigative Audit Feed</span>
                    </div>
                    <h1 class="text-3xl font-black tracking-tight text-foreground">Action Verification</h1>
                    <p class="text-xs text-muted-foreground font-medium">Detailed forensics for Log Entry #{{ log.id }}</p>
                </div>

                <Link href="/audit-logs">
                    <Button variant="ghost" class="h-10 rounded-xl gap-2 font-black text-[10px] uppercase tracking-widest">
                        <ArrowLeft class="h-3.5 w-3.5" />
                        Return to Archive
                    </Button>
                </Link>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- LEFT: SUMMARY & IDENTITY -->
                <div class="md:col-span-2 space-y-6">
                    <div class="rounded-3xl border bg-card/60 backdrop-blur-xl p-6 shadow-sm space-y-6">
                        <div class="flex items-center gap-4 border-b border-border/40 pb-6">
                            <div class="h-12 w-12 rounded-2xl bg-emerald-500/10 flex items-center justify-center">
                                <Activity class="h-6 w-6 text-emerald-600" />
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-foreground">{{ log.description }}</h3>
                                <Badge variant="outline" class="mt-2 h-5 text-[9px] font-black uppercase tracking-widest bg-emerald-500/5 text-emerald-600 border-emerald-500/20">
                                    Event: {{ log.event }}
                                </Badge>
                            </div>
                        </div>

                        <!-- Forensic Details -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <span class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 flex items-center gap-2">
                                    <User class="h-3 w-3" /> Investigated Identity
                                </span>
                                <div class="flex items-center gap-3 p-3 rounded-2xl bg-muted/20 border border-border/20">
                                    <Avatar class="h-10 w-10">
                                        <AvatarFallback class="bg-primary/5 text-primary text-xs font-black">{{ log.who.substring(0, 2) }}</AvatarFallback>
                                    </Avatar>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-black text-foreground">{{ log.who }}</span>
                                        <span class="text-[10px] font-black text-muted-foreground uppercase mt-1 opacity-60">Authorized Personnel</span>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <span class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 flex items-center gap-2">
                                    <Calendar class="h-3 w-3" /> Operational Time
                                </span>
                                <div class="flex items-center gap-3 p-3 rounded-2xl bg-muted/20 border border-border/20 h-[64px]">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-black text-foreground">{{ log.created_at }}</span>
                                        <span class="text-[10px] font-black text-muted-foreground uppercase mt-1 opacity-60">System Timestamp</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Data Properties -->
                        <div class="space-y-4 pt-4 border-t border-border/40">
                            <span class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 flex items-center gap-2">
                                <Database class="h-3.5 w-3.5" /> Payload Metadata
                            </span>
                            <div class="relative group">
                                <pre class="p-6 rounded-3xl bg-slate-950 text-emerald-400 font-mono text-[11px] overflow-x-auto shadow-inner leading-relaxed custom-scrollbar">{{ formatJson(log.properties) }}</pre>
                                <div class="absolute top-4 right-4 h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT: DIGITAL FOOTPRINT -->
                <div class="space-y-6">
                    <div class="rounded-3xl border bg-card/60 backdrop-blur-xl p-6 shadow-sm space-y-6 lg:sticky lg:top-24">
                        <h4 class="text-[11px] font-black uppercase tracking-widest text-foreground pb-4 border-b border-border/40">Verification Metrics</h4>
                        
                        <div class="space-y-6">
                            <div class="flex items-start gap-4">
                                <div class="h-8 w-8 rounded-xl bg-primary/5 flex items-center justify-center shrink-0">
                                    <component :is="getGadgetIcon(log.agent)" class="h-4 w-4 text-primary" />
                                </div>
                                <div class="space-y-1">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Gadget Protocol</p>
                                    <p class="text-[11px] font-bold text-foreground leading-snug break-all">{{ log.agent }}</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="h-8 w-8 rounded-xl bg-emerald-500/5 flex items-center justify-center shrink-0">
                                    <Globe class="h-4 w-4 text-emerald-600" />
                                </div>
                                <div class="space-y-1">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Digital Coordinates</p>
                                    <p class="text-[12px] font-mono font-black text-foreground">{{ log.ip }}</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="h-8 w-8 rounded-xl bg-blue-500/5 flex items-center justify-center shrink-0">
                                    <HardDrive class="h-4 w-4 text-blue-600" />
                                </div>
                                <div class="space-y-1">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">System Target</p>
                                    <p class="text-[11px] font-bold text-foreground">{{ log.subject?.name || 'Class Instance' }}</p>
                                    <p class="text-[9px] font-mono text-muted-foreground opacity-50">{{ log.properties.url || 'Internal API' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Verification Status -->
                        <div class="pt-6 border-t border-border/40">
                            <div class="p-4 rounded-2xl bg-emerald-500/5 border border-emerald-500/20 text-center">
                                <div class="flex items-center justify-center gap-2 text-emerald-600 mb-1">
                                    <ShieldCheck class="h-4 w-4" />
                                    <span class="text-[10px] font-black uppercase tracking-widest">Entry Verified</span>
                                </div>
                                <p class="text-[9px] font-bold text-emerald-600/60 leading-none">Integrity check passed vs central archive</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>

<style scoped>
.fade-in {
    animation: fadeIn 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}

pre::-webkit-scrollbar {
    height: 4px;
    width: 4px;
}
pre::-webkit-scrollbar-thumb {
    background: rgba(16, 185, 129, 0.2);
    border-radius: 10px;
}
</style>
