<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Calendar, Users, Star, Trophy, Plus, ArrowRight, MapPin, Clock } from 'lucide-vue-next';
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
    { title: 'Events & Clubs', href: '/events' },
];

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
};
</script>

<template>
    <Head title="Events & Clubs" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 font-pulsar">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-500/10">
                        <Trophy class="h-6 w-6 text-amber-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Events & Clubs</h1>
                        <p class="text-muted-foreground">Manage school calendar, extracurricular clubs and student activities</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" as-child><Link href="/events/clubs">View Clubs</Link></Button>
                    <Button as-child class="bg-amber-600 hover:bg-amber-700 font-pulsar"><Plus class="mr-2 h-4 w-4" />Create Event</Button>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-xl border bg-card p-6 shadow-sm border-l-4 border-l-amber-500">
                    <div class="flex items-center justify-between">
                        <div class="rounded-lg bg-amber-500/10 p-2"><Calendar class="h-5 w-5 text-amber-600" /></div>
                        <span class="text-xs font-bold text-amber-600 uppercase font-pulsar tracking-widest">Upcoming</span>
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-muted-foreground">Total Events</p>
                        <p class="text-2xl font-bold text-amber-600">{{ stats.upcoming_events }}</p>
                    </div>
                </div>
                <div class="rounded-xl border bg-card p-6 shadow-sm border-l-4 border-l-blue-500">
                    <div class="flex items-center justify-between">
                        <div class="rounded-lg bg-blue-500/10 p-2"><Users class="h-5 w-5 text-blue-600" /></div>
                        <span class="text-xs font-bold text-blue-600 uppercase font-pulsar tracking-widest">Active</span>
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-muted-foreground">Registered Clubs</p>
                        <p class="text-2xl font-bold text-blue-600">{{ stats.active_clubs }}</p>
                    </div>
                </div>
                <div class="rounded-xl border bg-card p-6 shadow-sm border-l-4 border-l-emerald-500">
                    <div class="flex items-center justify-between">
                        <div class="rounded-lg bg-emerald-500/10 p-2"><Star class="h-5 w-5 text-emerald-600" /></div>
                        <span class="text-xs font-bold text-emerald-600 uppercase font-pulsar tracking-widest">Featured</span>
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-muted-foreground">Highlighted Activities</p>
                        <p class="text-2xl font-bold text-emerald-600">{{ stats.featured_events }}</p>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2 space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold">Upcoming Calendar</h2>
                        <Link href="/events/list" class="text-sm text-amber-600 hover:underline font-bold">View Full Schedule</Link>
                    </div>
                    <div v-for="event in upcomingEvents" :key="event.id" class="rounded-2xl border bg-card overflow-hidden shadow-sm hover:border-amber-200 transition-all group flex flex-col md:flex-row">
                        <div class="w-full md:w-32 bg-slate-50 flex flex-col items-center justify-center p-4 border-b md:border-b-0 md:border-r text-center group-hover:bg-amber-50 transition-colors">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">{{ new Date(event.start_date).toLocaleString('en-GB', { month: 'short' }) }}</span>
                            <span class="text-3xl font-black text-slate-900 group-hover:text-amber-600 transition-colors">{{ new Date(event.start_date).getDate() }}</span>
                        </div>
                        <div class="p-5 flex-1">
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <Badge variant="outline" class="mb-1 text-[8px] font-bold uppercase tracking-tighter border-slate-200 text-slate-500">{{ event.status }}</Badge>
                                    <h3 class="font-bold text-lg text-slate-900">{{ event.title }}</h3>
                                </div>
                                <Star v-if="event.is_featured" class="h-4 w-4 text-amber-500 fill-amber-500" />
                            </div>
                            <div class="flex flex-wrap gap-4 text-xs text-muted-foreground mt-2">
                                <div class="flex items-center gap-1.5"><Clock class="h-3.5 w-3.5" /> {{ event.start_time || 'All Day' }}</div>
                                <div class="flex items-center gap-1.5"><MapPin class="h-3.5 w-3.5" /> {{ event.venue }}</div>
                            </div>
                        </div>
                    </div>
                    <div v-if="upcomingEvents.length === 0" class="p-12 text-center text-muted-foreground italic border-2 border-dashed rounded-xl">
                        No upcoming events scheduled for this term.
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-xl border bg-gradient-to-br from-amber-500 to-amber-600 p-8 shadow-xl text-white">
                        <h2 class="text-2xl font-bold mb-2 font-pulsar">Extracurriculars</h2>
                        <p class="text-amber-50/80 mb-6 italic text-sm">Join a club, track memberships and celebrate school achievements.</p>
                        <div class="grid gap-3">
                             <Button as-child size="lg" class="bg-white text-amber-600 hover:bg-amber-50 w-full justify-between font-bold border-0 shadow-md transition-transform hover:scale-[1.02]">
                                <Link href="/events/clubs">Explore School Clubs <ArrowRight class="h-4 w-4" /></Link>
                             </Button>
                        </div>
                    </div>
                    
                    <div class="rounded-xl border bg-card p-6 shadow-sm overflow-hidden relative">
                         <div class="absolute -right-4 -bottom-4 opacity-5 rotate-12"><Trophy class="h-24 w-24 text-slate-900" /></div>
                        <h3 class="font-bold mb-4">Event Registration</h3>
                        <p class="text-sm text-muted-foreground mb-4">Manage tickets, participant lists and resource allocation for school events.</p>
                        <Button variant="secondary" class="w-full font-pulsar">Attendee Analytics</Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
