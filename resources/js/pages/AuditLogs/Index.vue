<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { 
    History, Search, Filter, Monitor, Smartphone, 
    ArrowLeft, ArrowRight, Download, Calendar, 
    User, ShieldCheck, Activity, Globe
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import type { BreadcrumbItem } from '@/types';

interface Log {
    id: number;
    event: string;
    description: string;
    who: string;
    role: string;
    gadget: string;
    ip: string;
    where: string;
    when: string;
    time_ago: string;
    properties: any;
}

interface Props {
    logs: {
        data: Log[];
        links: any[];
        meta: any;
    };
    filters: {
        search: string;
        user_id: string;
        event: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Home', href: '/dashboard' },
    { title: 'Operational Integrity', href: '/dashboard' },
    { title: 'Audit Repository', href: '#' },
];

const getGadgetIcon = (userAgent: string) => {
    const ua = userAgent.toLowerCase();
    if (ua.includes('mobile') || ua.includes('android') || ua.includes('iphone')) return Smartphone;
    return Monitor;
};

const getEventVariant = (event: string) => {
    switch (event) {
        case 'created': return 'default';
        case 'updated': return 'secondary';
        case 'deleted': return 'destructive';
        default: return 'outline';
    }
};
</script>

<template>
    <Head title="Institutional Audit Trail" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-[1400px] space-y-6 p-4 sm:p-6 fade-in">
            
            <!-- HEADER -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b pb-6 border-border/40">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <div class="h-2 w-2 rounded-full bg-primary animate-pulse shadow-[0_0_8px_rgba(var(--primary),0.4)]"></div>
                        <span class="text-[10px] font-black uppercase tracking-[0.2em] text-primary">System Integrity Archive</span>
                    </div>
                    <h1 class="text-3xl font-black tracking-tight text-foreground sm:text-4xl">Institutional Audit Hub</h1>
                    <p class="text-xs text-muted-foreground font-medium italic">Archiving and verifying authorized activity logs since deployment.</p>
                </div>

                <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" class="h-10 rounded-xl gap-2 font-black text-[10px] uppercase tracking-widest border-border/60 bg-white/50 backdrop-blur-sm transition-all hover:bg-white hover:shadow-lg">
                        <Download class="h-3.5 w-3.5" />
                        Export Vault
                    </Button>
                    <Link href="/dashboard">
                        <Button variant="ghost" size="sm" class="h-10 border border-primary/10 rounded-xl gap-2 font-black text-[10px] uppercase tracking-widest hover:bg-primary/5 hover:text-primary transition-all">
                            <ArrowLeft class="h-3.5 w-3.5" />
                            Command Center
                        </Button>
                    </Link>
                </div>
            </div>

            <!-- ENHANCED FILTERS -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-5 relative group">
                    <Search class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground/60 transition-colors group-focus-within:text-primary" />
                    <Input placeholder="Search logs by action, ID or identity string..." class="h-12 pl-12 rounded-2xl border-border/40 focus:ring-4 focus:ring-primary/5 bg-card/40 backdrop-blur-md transition-all text-xs font-bold" />
                </div>
                <div class="md:col-span-3 relative group">
                    <Filter class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground/60" />
                    <select class="h-12 w-full pl-12 pr-4 rounded-2xl border border-border/40 bg-card/40 backdrop-blur-md appearance-none text-[10px] font-black uppercase tracking-widest focus:outline-none focus:ring-4 focus:ring-primary/5 cursor-pointer">
                        <option>Global System Events</option>
                        <option>Asset Creation</option>
                        <option>Property Modification</option>
                        <option>Authorized Deletion</option>
                    </select>
                </div>
                <div class="md:col-span-4 relative group">
                    <Calendar class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground/60" />
                    <Input type="date" class="h-12 pl-12 rounded-2xl border-border/40 focus:ring-4 focus:ring-primary/5 bg-card/40 backdrop-blur-md text-xs font-bold" />
                </div>
            </div>

            <!-- AUDIT TABLE -->
            <div class="rounded-3xl border bg-card/60 backdrop-blur-xl shadow-xl shadow-primary/5 overflow-hidden min-h-[620px] transition-all hover:shadow-primary/10">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-muted/10 border-b border-border/40">
                                <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 select-none">Action Integrity</th>
                                <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 select-none">Investigated Identity</th>
                                <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 select-none">Digital Footprint</th>
                                <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 select-none">Operational Time</th>
                                <th class="px-6 py-5 text-right text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 select-none">Verification</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/30">
                            <tr v-for="log in props.logs.data" :key="log.id" class="group hover:bg-primary/[0.03] transition-all duration-300 relative translate-gpu">
                                <td class="px-6 py-6 max-w-[400px]">
                                    <div class="flex items-start gap-4">
                                        <div class="mt-1.5 h-2 w-2 rounded-full shrink-0 transition-transform group-hover:scale-125" 
                                            :class="{
                                                'bg-emerald-500 shadow-[0_0_12px_rgba(16,185,129,0.5)]': log.event === 'created',
                                                'bg-blue-500 shadow-[0_0_12px_rgba(59,130,246,0.5)]': log.event === 'updated',
                                                'bg-rose-500 shadow-[0_0_12px_rgba(244,63,94,0.5)]': log.event === 'deleted',
                                                'bg-slate-400': !['created', 'updated', 'deleted'].includes(log.event)
                                            }"></div>
                                        <div class="flex flex-col min-w-0">
                                            <p class="text-[13px] font-black text-foreground leading-snug group-hover:text-primary transition-colors">{{ log.description }}</p>
                                            <div class="flex items-center gap-2 mt-2.5">
                                                <Badge :variant="getEventVariant(log.event)" class="h-4 text-[8px] font-black uppercase px-2 tracking-widest border-border/40">{{ log.event }}</Badge>
                                                <span class="text-[9px] font-mono font-black text-muted-foreground opacity-40 truncate flex items-center gap-1">
                                                    <Globe class="h-2.5 w-2.5" />
                                                    {{ log.where }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-6">
                                    <div class="flex items-center gap-3">
                                        <Avatar class="h-9 w-9 ring-2 ring-background transition-transform group-hover:scale-105">
                                            <AvatarFallback class="bg-primary/5 text-primary text-[10px] font-black uppercase">{{ log.who.substring(0, 2) }}</AvatarFallback>
                                        </Avatar>
                                        <div class="flex flex-col leading-none">
                                            <span class="text-[12px] font-black text-foreground">{{ log.who }}</span>
                                            <span class="text-[9px] font-black text-primary/60 uppercase mt-1.5 tracking-wider">{{ log.role }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-6">
                                    <div class="flex flex-col gap-2.5">
                                        <div class="flex items-center gap-2">
                                            <component :is="getGadgetIcon(log.gadget)" class="h-3 w-3 text-muted-foreground opacity-50" />
                                            <span class="text-[9px] font-black text-muted-foreground uppercase opacity-80 truncate max-w-[140px]" :title="log.gadget">{{ log.gadget.split('(')[0] }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <div class="h-1 w-1 rounded-full bg-emerald-500/40"></div>
                                            <span class="text-[10px] font-mono font-black text-muted-foreground/60">{{ log.ip }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-6">
                                    <div class="flex flex-col leading-none">
                                        <span class="text-[11px] font-black text-foreground">{{ log.when.split(' ')[1] }}</span>
                                        <span class="text-[9px] font-black text-muted-foreground uppercase mt-1.5 opacity-50">{{ log.time_ago }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-6 text-right">
                                    <Link :href="route('audit-logs.show', log.id)">
                                        <Button variant="ghost" size="icon" class="h-9 w-9 rounded-xl hover:bg-primary/10 hover:text-primary transition-all border border-transparent hover:border-primary/20">
                                            <ShieldCheck class="h-4.5 w-4.5" />
                                        </Button>
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- EMPTY STATE -->
                <div v-if="props.logs.data.length === 0" class="flex flex-col items-center justify-center py-24 text-center opacity-30 grayscale translate-y-[-10px]">
                    <div class="relative mb-6">
                        <History class="h-16 w-16 text-muted-foreground" />
                        <div class="absolute -top-1 -right-1 h-4 w-4 rounded-full bg-primary animate-pulse"></div>
                    </div>
                    <p class="text-xl font-black uppercase tracking-[0.3em] text-foreground">Zero Archives</p>
                    <p class="text-xs font-bold text-muted-foreground mt-3 tracking-wide">No institutional actions detected in the current scope.</p>
                </div>
            </div>

            <!-- PAGINATION -->
            <div class="flex justify-between items-center bg-card/60 p-4 rounded-3xl border border-border/40 backdrop-blur-sm">
                <span class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Showing {{ props.logs.data.length }} of Total Records</span>
                <div class="flex gap-2">
                    <Button variant="outline" size="sm" class="h-8 w-8 rounded-full p-0 flex items-center justify-center border-border/40">
                        <ArrowLeft class="h-3.5 w-3.5" />
                    </Button>
                    <Button variant="outline" size="sm" class="h-8 w-8 rounded-full p-0 flex items-center justify-center border-border/40">
                        <ArrowRight class="h-3.5 w-3.5" />
                    </Button>
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

tr:last-child {
    border-bottom: none;
}
</style>
