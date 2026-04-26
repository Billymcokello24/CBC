<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Rows3,
    School,
    Users,
    Activity,
    Database,
    Zap,
    TrendingUp,
    ShieldCheck,
    ArrowLeft,
    Edit,
    Building2,
    Calendar,
    ChevronRight,
} from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    stream: {
        id: number;
        name: string;
        code: string;
        capacity: number | null;
        is_active: boolean;
        lead_name: string | null;
    };
    classes: Array<{
        id: number;
        name: string;
        code: string;
        grade: string | null;
        academic_year: string | null;
        teacher: string | null;
        students_count: number;
        capacity: number | null;
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Intelligence Hub', href: '/dashboard' },
    { title: 'Academic Streams', href: '/streams' },
    { title: props.stream.name, href: `/streams/${props.stream.id}` },
];

const stats = [
    { label: 'Head of Channel', val: props.stream.lead_name || 'NULL_NODE', sub: 'Strategic Lead', icon: ShieldCheck },
    { label: 'Population Node', val: props.classes.reduce((acc, c) => acc + c.students_count, 0), sub: 'Assets Synchronized', icon: Users },
    { label: 'Cluster Density', val: props.classes.length, sub: 'Active Class Units', icon: Building2 },
    { label: 'Capacity Key', val: props.stream.capacity || 'INF', sub: 'Global Node Limit', icon: Database },
];
</script>

<template>
    <Head :title="`CHANNEL_${stream.name.toUpperCase()}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex h-full max-w-[1600px] flex-1 animate-in flex-col gap-12 p-4 pb-20 duration-700 fade-in slide-in-from-bottom-4 sm:p-6 sm:pb-32 md:p-8"
        >
            <!-- High-Fidelity Header -->
            <div class="relative overflow-hidden rounded-[3rem] border border-border bg-card p-10 shadow-sm transition-all hover:border-primary/20">
                <div class="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-primary/5 blur-3xl opacity-50"></div>
                <div class="relative flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center gap-8">
                         <div class="flex h-24 w-24 items-center justify-center rounded-[2rem] bg-primary text-white shadow-2xl shadow-primary/20">
                            <Rows3 class="h-10 w-10" />
                         </div>
                         <div class="space-y-2">
                             <div class="flex items-center gap-4">
                                <h1 class="text-4xl font-black tracking-tight text-foreground uppercase">{{ stream.name }} channel</h1>
                                <Badge variant="outline" class="rounded-xl border-emerald-500/20 bg-emerald-500/10 px-4 py-1 text-[10px] font-black tracking-[0.2em] text-emerald-600 uppercase shadow-sm">
                                    {{ stream.is_active ? 'ACTIVE_SYNC' : 'DORMANT' }}
                                </Badge>
                             </div>
                             <p class="text-[10px] font-bold tracking-[0.3em] text-muted-foreground uppercase opacity-60">
                                Global Infrastructure Node // {{ stream.code }} // Intelligence Hub 
                             </p>
                         </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button variant="outline" class="h-14 rounded-2xl border-border bg-card px-8 text-[10px] font-black tracking-[0.2em] uppercase hover:bg-muted" as-child>
                             <Link :href="`/streams/${stream.id}/edit`">
                                <Edit class="mr-3 h-4 w-4 text-primary" />
                                Mutate configuration
                             </Link>
                        </Button>
                        <Button variant="ghost" size="icon" as-child class="h-14 w-14 rounded-2xl border-border bg-card text-muted-foreground hover:bg-muted shadow-sm">
                            <Link href="/streams">
                                <ArrowLeft class="h-5 w-5" />
                            </Link>
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Global Scorecard -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div v-for="(stat, idx) in stats" :key="idx" class="group relative overflow-hidden rounded-[2rem] border border-border bg-card p-8 transition-all hover:border-primary/20 hover:shadow-xl hover:shadow-primary/5">
                    <div class="absolute -right-2 -top-2 opacity-[0.05] transition-transform duration-700 group-hover:scale-110">
                        <component :is="stat.icon" class="h-20 w-20" />
                    </div>
                    <div class="space-y-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-muted/50 text-primary transition-colors group-hover:bg-primary group-hover:text-white">
                            <component :is="stat.icon" class="h-5 w-5" />
                        </div>
                        <div class="space-y-1">
                            <p class="text-[10px] font-black tracking-widest text-muted-foreground uppercase opacity-40">{{ stat.label }}</p>
                            <h3 class="text-xl font-black tracking-tight text-foreground uppercase">{{ stat.val }}</h3>
                            <p class="text-[9px] font-bold text-primary uppercase opacity-60">{{ stat.sub }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Operational Clusters (Classes) -->
            <div class="space-y-8">
                 <div class="flex items-center justify-between px-2">
                    <div class="space-y-1">
                        <h2 class="text-2xl font-black tracking-tight text-foreground uppercase">Linked operational clusters</h2>
                        <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">Synchronized class units within this infrastructure channel</p>
                    </div>
                    <Button variant="outline" class="h-10 rounded-xl text-[9px] font-black tracking-widest uppercase hover:bg-muted">Audit population</Button>
                </div>

                <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                    <Link v-for="cls in classes" :key="cls.id" :href="`/classes/${cls.id}`" class="group relative overflow-hidden rounded-[2.5rem] border border-border bg-card p-10 transition-all hover:border-primary/30 hover:scale-[1.02] hover:shadow-2xl hover:shadow-primary/5">
                        <div class="flex flex-col h-full gap-8">
                            <div class="flex items-start justify-between">
                                <div class="space-y-2">
                                    <Badge variant="outline" class="rounded-lg border-primary/20 bg-primary/5 text-[8px] font-black tracking-widest text-primary uppercase">CLUSTER_{{ cls.code }}</Badge>
                                    <h3 class="text-xl font-black tracking-tight text-foreground uppercase group-hover:text-primary transition-colors">{{ cls.name }}</h3>
                                    <p class="text-[10px] font-bold tracking-widest text-muted-foreground uppercase opacity-60">{{ cls.grade || 'GRAD_LEVEL' }} // {{ cls.academic_year || 'S_2024' }}</p>
                                </div>
                                <div class="flex h-14 w-14 items-center justify-center rounded-3xl bg-muted/50 text-muted-foreground transition-all group-hover:bg-primary group-hover:text-white group-hover:rotate-12">
                                    <School class="h-6 w-6" />
                                </div>
                            </div>

                            <div class="space-y-3 rounded-2xl border border-border/50 bg-muted/10 p-5">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-full border border-primary/10 bg-white flex items-center justify-center transition-transform group-hover:scale-110">
                                        <ShieldCheck class="h-4 w-4 text-primary" />
                                    </div>
                                    <p class="text-[10px] font-black text-foreground uppercase tracking-tight truncate">{{ cls.teacher || 'UNASSIGNED' }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-6 items-end mt-auto">
                                <div class="space-y-1">
                                    <p class="text-[9px] font-black tracking-widest text-muted-foreground uppercase opacity-40">Population</p>
                                    <p class="text-lg font-black text-foreground uppercase tracking-tight">{{ cls.students_count }} <span class="text-[10px] opacity-40">ASSETS</span></p>
                                </div>
                                <div class="space-y-1 text-right">
                                    <p class="text-[9px] font-black tracking-widest text-muted-foreground uppercase opacity-40">Capacity</p>
                                    <p class="text-lg font-black text-primary uppercase tracking-tight">{{ cls.capacity || 'INF' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="absolute bottom-0 left-0 h-1.5 w-full bg-muted overflow-hidden">
                            <div :style="{ width: `${Math.min((cls.students_count / (cls.capacity || 100)) * 100, 100)}%` }" class="h-full bg-primary transition-all duration-1000 group-hover:shadow-[0_0_15px_rgba(var(--primary),0.6)]"></div>
                        </div>
                    </Link>
                </div>
                
                <div v-if="classes.length === 0" class="flex flex-col items-center justify-center py-20 rounded-[3rem] border border-dashed border-border bg-muted/5">
                    <Database class="h-16 w-16 text-muted-foreground opacity-20 mb-6" />
                    <p class="text-[10px] font-black tracking-[0.2em] text-muted-foreground uppercase opacity-40">Zero Infrastructure Connectivity Found</p>
                    <Button variant="outline" class="mt-8 rounded-2xl h-12 px-10 text-[10px] font-black uppercase tracking-widest hover:bg-muted">Link clusters</Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
