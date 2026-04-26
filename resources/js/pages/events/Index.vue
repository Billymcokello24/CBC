<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Calendar,
    Users,
    Star,
    Trophy,
    Plus,
    ArrowUpRight,
    MapPin,
    Clock,
    Home,
    ChevronRight,
    Activity,
    History,
    Sparkles,
} from 'lucide-vue-next';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    stats: {
        upcoming_events: number;
        active_clubs: number;
        featured_events: number;
    };
    upcomingEvents: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Institutional Life', href: '/events' },
];

const getMonth = (date: string) => {
    return new Date(date).toLocaleString('en-US', { month: 'short' });
};

const getDay = (date: string) => {
    return new Date(date).getDate();
};
</script>

<template>
    <Head title="Events & Engagement" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto max-w-[1600px] animate-in space-y-6 p-4 pb-10 duration-700 fade-in slide-in-from-bottom-4 sm:space-y-8 sm:p-6 sm:pb-20 md:p-8"
        >
            <!-- Page Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between px-1">
                <div class="flex flex-col gap-1">
                    <div class="mb-1 flex items-center gap-2 text-[10px] font-bold tracking-tight text-muted-foreground uppercase">
                        <Home class="h-3 w-3" />
                        <ChevronRight class="h-2.5 w-2.5 opacity-50" />
                        <span>Experience</span>
                        <ChevronRight class="h-2.5 w-2.5 opacity-50" />
                        <span class="text-foreground">Activity Matrix</span>
                    </div>
                    <h1 class="text-2xl leading-tight font-bold tracking-tight text-foreground sm:text-3xl">
                        Events & Engagement
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Synchronize school calendar milestones and extracurricular club architectures.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Button variant="outline" as-child class="h-11 rounded-xl border-border bg-background px-6 text-xs font-bold tracking-tight uppercase shadow-sm transition-all hover:bg-muted">
                        <Link href="/events/clubs">
                            <Sparkles class="mr-2 h-4 w-4 opacity-40 text-amber-500" />Explore Clubs
                        </Link>
                    </Button>
                    <Button as-child class="h-11 rounded-xl bg-amber-600 px-6 text-xs font-bold tracking-tight text-white uppercase shadow-lg shadow-amber-500/30 transition-all hover:scale-[1.02] hover:bg-amber-700 active:scale-95 text-white border-0">
                        <Link href="/events/create">
                            <Plus class="mr-2 h-4 w-4" />Schedule Event
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Dashboard Analytics -->
            <div class="grid grid-cols-1 gap-4 px-1 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Upcoming Events -->
                <div class="group relative overflow-hidden rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:border-amber-500/20">
                    <div class="absolute -top-6 -right-6 h-24 w-24 rounded-full bg-amber-500/5 blur-2xl transition-all group-hover:bg-amber-500/10"></div>
                    <div class="relative z-10 flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 shadow-sm transition-all group-hover:bg-amber-600 group-hover:text-white">
                                <Calendar class="h-5 w-5" />
                            </div>
                            <Badge variant="outline" class="h-5 rounded-lg text-[8px] font-bold tracking-tighter opacity-40">CALENDAR READY</Badge>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-foreground tabular-nums">{{ stats.upcoming_events }}</h3>
                            <p class="text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase mt-1">Scheduled Institutional Events</p>
                        </div>
                    </div>
                </div>

                <!-- Active Clubs -->
                <div class="group relative overflow-hidden rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:border-blue-500/20">
                    <div class="absolute -top-6 -right-6 h-24 w-24 rounded-full bg-blue-500/5 blur-2xl transition-all group-hover:bg-blue-500/10"></div>
                    <div class="relative z-10 flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-500/10 text-blue-600 shadow-sm transition-all group-hover:bg-blue-600 group-hover:text-white">
                                <Users class="h-5 w-5" />
                            </div>
                            <span class="text-[10px] font-bold tracking-tight text-blue-600/40 uppercase">Interest Groups</span>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-foreground tabular-nums">{{ stats.active_clubs }}</h3>
                            <p class="text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase mt-1">Active Extracurricular Nodes</p>
                        </div>
                    </div>
                </div>

                <!-- Featured Activities -->
                <div class="group relative overflow-hidden rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:border-emerald-500/20">
                    <div class="absolute -top-6 -right-6 h-24 w-24 rounded-full bg-emerald-500/5 blur-2xl transition-all group-hover:bg-emerald-500/10"></div>
                    <div class="relative z-10 flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 shadow-sm transition-all group-hover:bg-emerald-600 group-hover:text-white">
                                <Star class="h-5 w-5" />
                            </div>
                            <span class="text-[10px] font-bold tracking-tight text-emerald-600/40 uppercase">Highlights</span>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-foreground tabular-nums">{{ stats.featured_events }}</h3>
                            <p class="text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase mt-1">Featured Institutional Spotlights</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Split -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 px-1">
                <!-- Events Timeline -->
                <div class="lg:col-span-2 overflow-hidden rounded-3xl border border-border bg-card shadow-sm flex flex-col">
                    <div class="flex items-center justify-between border-b border-border/50 bg-muted/10 px-8 py-6">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600">
                                <Calendar class="h-5 w-5" />
                            </div>
                            <h3 class="text-sm font-bold tracking-tight text-foreground uppercase">Institutional Calendar</h3>
                        </div>
                        <Button variant="ghost" size="sm" as-child class="h-9 rounded-xl text-[10px] font-bold tracking-tight uppercase hover:bg-muted">
                            <Link href="/events/timeline">Full Schedule</Link>
                        </Button>
                    </div>

                    <div class="divide-y divide-border/30">
                        <div
                            v-for="event in upcomingEvents"
                            :key="event.id"
                            class="group relative flex flex-col sm:flex-row transition-all hover:bg-muted/10"
                        >
                            <!-- Date Node -->
                            <div class="flex w-full flex-col items-center justify-center bg-muted/5 p-8 text-center border-b border-border/30 sm:w-32 sm:border-b-0 sm:border-r">
                                <span class="text-[10px] font-bold tracking-widest text-muted-foreground/40 uppercase mb-1">{{ getMonth(event.start_date) }}</span>
                                <span class="text-3xl font-bold tracking-tighter text-foreground group-hover:text-amber-600 transition-colors">{{ getDay(event.start_date) }}</span>
                            </div>
                            
                            <!-- Event Details -->
                            <div class="flex-1 p-8">
                                <div class="flex items-start justify-between mb-4">
                                    <div>
                                        <div class="flex items-center gap-2 mb-1.5">
                                            <Badge variant="outline" class="h-5 rounded-lg text-[9px] font-bold tracking-tighter uppercase px-2">{{ event.status }}</Badge>
                                            <Star v-if="event.is_featured" class="h-3.5 w-3.5 fill-amber-500 text-amber-500" />
                                        </div>
                                        <h4 class="text-base font-bold text-foreground group-hover:text-amber-600 transition-colors uppercase leading-tight">
                                            {{ event.title }}
                                        </h4>
                                    </div>
                                    <Button variant="ghost" size="icon" as-child class="h-10 w-10 rounded-xl opacity-0 group-hover:opacity-100 transition-all">
                                        <Link :href="`/events/${event.id}`"><ArrowUpRight class="h-5 w-5" /></Link>
                                    </Button>
                                </div>
                                <div class="flex flex-wrap gap-6">
                                    <div class="flex items-center gap-2 text-[10px] font-bold tracking-tight text-muted-foreground/60 uppercase">
                                        <Clock class="h-4 w-4 opacity-40 text-blue-500" />
                                        {{ event.start_time || 'Synchronized All-Day' }}
                                    </div>
                                    <div class="flex items-center gap-2 text-[10px] font-bold tracking-tight text-muted-foreground/60 uppercase">
                                        <MapPin class="h-4 w-4 opacity-40 text-rose-500" />
                                        {{ event.venue || 'Terminal Matrix' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-if="upcomingEvents.length === 0" class="flex flex-col items-center justify-center p-20 text-center">
                            <div class="h-16 w-16 rounded-3xl bg-muted/20 flex items-center justify-center mb-4">
                                <Calendar class="h-8 w-8 text-muted-foreground/20" />
                            </div>
                            <p class="text-xs font-bold text-muted-foreground/40 uppercase tracking-widest">No active milestones scheduled</p>
                        </div>
                    </div>
                </div>

                <!-- Strategic Engagement Sidebar -->
                <div class="space-y-6 flex flex-col">
                    <!-- Featured Achievement Card -->
                    <div class="relative overflow-hidden rounded-3xl bg-slate-900 px-8 py-10 text-white shadow-2xl flex-1">
                        <div class="absolute inset-0 opacity-[0.05] grayscale pointer-events-none">
                            <svg class="h-full w-full" xmlns="http://www.w3.org/2000/svg" width="100%" height="100%">
                                <defs><pattern id="grid-events" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="currentColor" stroke-width="1"/></pattern></defs>
                                <rect width="100%" height="100%" fill="url(#grid-events)" />
                            </svg>
                        </div>
                        
                        <div class="relative z-10 space-y-8 h-full flex flex-col justify-between">
                            <div class="space-y-6">
                                <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-white/5 border border-white/10 shadow-2xl transition-all hover:bg-amber-500/20 hover:scale-110">
                                    <Trophy class="h-10 w-10 text-amber-500" />
                                </div>
                                <div class="space-y-3">
                                    <h4 class="text-2xl font-bold tracking-tight">Institutional Vibrancy</h4>
                                    <p class="text-sm text-white/50 font-medium leading-relaxed max-w-xs">
                                        Synchronize student life through club architectures and institutional milestones. Track participant density and achievement matrices.
                                    </p>
                                </div>
                            </div>

                            <Button as-child class="h-14 rounded-2xl bg-amber-600 font-bold text-[10px] tracking-tight text-white uppercase shadow-xl shadow-amber-900/20 hover:bg-amber-500 transition-all active:scale-95 border-0 text-white">
                                <Link href="/events/clubs">Manage Engagement Nodes <Sparkles class="ml-2 h-4 w-4" /></Link>
                            </Button>
                        </div>
                    </div>

                    <!-- attendee Intelligence Card -->
                    <div class="rounded-3xl border border-border bg-card p-8 shadow-sm group">
                        <div class="flex items-center gap-4 mb-6 transition-all group-hover:translate-x-1">
                            <div class="h-10 w-10 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-600 shadow-sm border border-blue-500/10 group-hover:bg-blue-600 group-hover:text-white transition-all">
                                <History class="h-5 w-5" />
                            </div>
                            <div>
                                <h3 class="text-xs font-bold tracking-tight text-foreground uppercase leading-none">Attendee Metrics</h3>
                                <p class="text-[10px] font-bold text-muted-foreground/40 uppercase mt-1">Resource allocation mapping</p>
                            </div>
                        </div>
                        
                        <p class="text-[11px] leading-relaxed text-muted-foreground/60 mb-6">
                            Monitor ticket synchronization, participant lists, and institutional resource allocation for scheduled milestones.
                        </p>
                        
                        <Button variant="outline" as-child class="w-full h-11 rounded-xl border-border bg-background text-[10px] font-bold tracking-tight uppercase hover:bg-muted group-hover:border-primary/20 transition-all">
                            <Link href="/events/analytics">Analyze Participation <ArrowUpRight class="ml-2 h-4 w-4 opacity-40 group-hover:opacity-100 transition-opacity" /></Link>
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
