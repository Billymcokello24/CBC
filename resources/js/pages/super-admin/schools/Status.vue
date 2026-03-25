<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { 
    School, 
    CheckCircle2, 
    XCircle, 
    Clock, 
    ArrowRight, 
    ShieldCheck, 
    Zap,
    ExternalLink,
    Search,
    Filter
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import type { BreadcrumbItem } from '@/types';
import { format } from 'date-fns';

interface School {
    id: number;
    name: string;
    code: string;
    status: 'active' | 'inactive' | 'pending';
    created_at: string;
}

const props = defineProps<{
    schools: School[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Global Dashboard', href: '/super-admin/dashboard' },
    { title: 'Tenant Registry', href: '/super-admin/schools' },
    { title: 'Activation Status', href: '/super-admin/schools/status' },
];
</script>

<template>
    <Head title="Activation Status Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-foreground">School Status</h1>
                    <p class="text-[10px] font-black text-muted-foreground/40 uppercase tracking-widest mt-0.5">Monitor account activity and activation states.</p>
                </div>
            </div>

            <!-- Toolbar -->
            <div class="rounded-2xl border border-border bg-card p-4 shadow-sm flex flex-col md:flex-row gap-4 items-center dark:border-white/5">
                 <div class="relative flex-1 group w-full">
                      <Search class="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/40 group-focus-within:text-primary transition-colors" />
                      <Input 
                          placeholder="Search school name or code..." 
                          class="pl-10 h-11 rounded-xl border-border bg-muted/20 font-bold focus:ring-primary/20"
                      />
                 </div>
                 <Button variant="outline" class="h-11 px-6 rounded-xl border-border font-black text-[11px] uppercase tracking-widest text-muted-foreground hover:bg-muted/50 w-full md:w-auto">
                      <Filter class="mr-2 h-4 w-4 opacity-50" />
                      Filter Status
                 </Button>
            </div>

            <!-- Shard Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                 <div v-for="school in schools" :key="school.id" class="group relative overflow-hidden rounded-2xl border border-border bg-card p-6 transition-all hover:shadow-lg hover:-translate-y-1 dark:border-white/5">
                      <div class="relative space-y-6">
                           <div class="flex items-center justify-between">
                                <div :class="[
                                     'flex h-10 w-10 items-center justify-center rounded-xl transition-colors',
                                     school.status === 'active' ? 'bg-emerald-500/10 text-emerald-600' : 'bg-muted text-muted-foreground/40'
                                ]">
                                     <School class="h-5 w-5" />
                                </div>
                                <Badge :variant="school.status === 'active' ? 'default' : 'secondary'" class="bg-emerald-500/10 text-emerald-600 border-0 font-black text-[8px] uppercase tracking-widest px-2 shadow-none" v-if="school.status === 'active'">
                                    <div class="h-1 w-1 rounded-full bg-emerald-500 mr-1 animate-pulse"></div>
                                    Active
                                </Badge>
                                <Badge variant="outline" class="text-muted-foreground/40 border-muted font-black text-[8px] uppercase tracking-widest px-2 shadow-none" v-else>
                                    Inactive
                                </Badge>
                           </div>

                           <div>
                                <h3 class="text-lg font-black text-foreground tracking-tight leading-tight group-hover:text-primary transition-colors line-clamp-1">{{ school.name }}</h3>
                                <p class="text-[9px] font-black text-muted-foreground/40 uppercase tracking-widest mt-1">Code: {{ school.code }}</p>
                           </div>

                           <div class="space-y-3 pt-4 border-t border-border/50">
                                <div class="flex items-center justify-between">
                                     <span class="text-[9px] font-black text-muted-foreground/30 uppercase tracking-widest">Added On</span>
                                     <span class="text-[11px] font-black text-muted-foreground">{{ format(new Date(school.created_at), 'MMM dd, yyyy') }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                     <span class="text-[9px] font-black text-muted-foreground/30 uppercase tracking-widest">Connectivity</span>
                                     <Badge variant="outline" class="font-black text-[8px] px-1.5 py-0 border-emerald-500/20 text-emerald-600 bg-emerald-500/5 uppercase tracking-tighter">Healthy</Badge>
                                </div>
                           </div>

                           <div class="pt-2 flex gap-2">
                                <Button variant="ghost" class="flex-1 h-10 rounded-lg text-muted-foreground/40 font-black uppercase text-[9px] tracking-widest hover:bg-muted/50">History</Button>
                                <Link :href="`/super-admin/schools/${school.id}`" class="flex-1">
                                     <Button variant="outline" class="w-full h-10 rounded-lg border-border font-black text-[10px] uppercase tracking-widest text-foreground hover:bg-primary/5 hover:text-primary group/btn shadow-none">
                                          Details
                                          <ArrowRight class="ml-1.5 h-3.5 w-3.5 transition-transform group-hover/btn:translate-x-1" />
                                     </Button>
                                </Link>
                           </div>
                      </div>
                 </div>
            </div>

            <!-- Stats Overlay -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                 <div class="rounded-2xl bg-card border border-border p-8 shadow-sm relative overflow-hidden group dark:border-white/5">
                      <div class="relative space-y-4">
                           <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                <ShieldCheck class="h-5 w-5" />
                           </div>
                           <div>
                               <h3 class="text-xl font-black tracking-tight text-foreground">System Health</h3>
                               <p class="text-muted-foreground/60 font-black text-[10px] uppercase tracking-widest mt-1">99.98% Across all schools</p>
                           </div>
                           <div class="flex items-center gap-2 pt-2">
                                <div class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
                                <span class="text-[9px] font-black text-emerald-600 uppercase tracking-widest">All services nominal</span>
                           </div>
                      </div>
                 </div>

                 <div class="rounded-2xl bg-primary p-8 shadow-lg shadow-primary/20 text-primary-foreground relative overflow-hidden group">
                      <div class="relative space-y-4">
                           <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 text-white">
                                <Zap class="h-5 w-5" />
                           </div>
                           <div>
                               <h3 class="text-xl font-black tracking-tight">Active Schools</h3>
                               <p class="text-white/60 font-black text-[10px] uppercase tracking-widest mt-1">Real-time engagement metrics</p>
                           </div>
                           <Button variant="ghost" class="w-full h-10 rounded-lg bg-white/10 hover:bg-white/20 text-white font-black uppercase text-[9px] tracking-widest mt-2 border-0">
                                View Analytics <ExternalLink class="ml-2 h-3 w-3" />
                           </Button>
                      </div>
                 </div>

                 <div class="rounded-2xl border border-border bg-card p-8 shadow-sm space-y-6 dark:border-white/5">
                      <div class="flex items-center gap-4">
                           <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-muted text-foreground">
                                <Clock class="h-5 w-5 opacity-40" />
                           </div>
                           <h3 class="text-sm font-black text-foreground uppercase tracking-widest">Platform Uptime</h3>
                      </div>
                      <div class="space-y-3">
                           <div class="h-1.5 w-full rounded-full bg-muted overflow-hidden">
                                <div class="h-full bg-emerald-500 rounded-full w-[99.9%]"></div>
                           </div>
                           <div class="flex justify-between text-[9px] font-black text-muted-foreground/40 uppercase tracking-widest">
                                <span>Past 30 Days</span>
                                <span class="text-emerald-600">99.99% Operational</span>
                           </div>
                      </div>
                      <p class="text-[10px] font-bold text-muted-foreground/40 italic pt-2">Heartbeat monitored by Global Watchtower</p>
                 </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.tracking-widest {
    letter-spacing: 0.15em;
}
</style>
