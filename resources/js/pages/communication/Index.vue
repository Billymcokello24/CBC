<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Megaphone,
    MessageSquare,
    Send,
    Bell,
    Plus,
    ArrowUpRight,
    Mail,
    Home,
    ChevronRight,
    History,
    Sparkles,
    LayoutDashboard,
} from 'lucide-vue-next';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    stats: {
        active_announcements: number;
        unread_messages: number;
        total_sent_sms: number;
    };
    recentAnnouncements: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Communication Hub', href: '/communication' },
];

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};
</script>

<template>
    <Head title="Communication Hub" />
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
                        <span>Support</span>
                        <ChevronRight class="h-2.5 w-2.5 opacity-50" />
                        <span class="text-foreground">Broadcast Matrix</span>
                    </div>
                    <h1 class="text-2xl leading-tight font-bold tracking-tight text-foreground sm:text-3xl">
                        Communication Center
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        Orchestrate institutional broadcasts, SMS vectors, and internal messaging protocols.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Button variant="outline" as-child class="h-11 rounded-xl border-border bg-background px-6 text-xs font-bold tracking-tight uppercase shadow-sm transition-all hover:bg-muted">
                        <Link href="/communication/messages">
                            <MessageSquare class="mr-2 h-4 w-4 opacity-40" />Synchronize Inbox
                        </Link>
                    </Button>
                    <Button as-child class="h-11 rounded-xl bg-purple-600 px-6 text-xs font-bold tracking-tight text-white uppercase shadow-lg shadow-purple-500/30 transition-all hover:scale-[1.02] hover:bg-purple-700 active:scale-95 text-white">
                        <Link href="/communication/announcements/create">
                            <Plus class="mr-2 h-4 w-4" />New Broadcast
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Intelligence Map -->
            <div class="grid grid-cols-1 gap-4 px-1 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Active Announcements -->
                <div class="group relative overflow-hidden rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:border-purple-500/20">
                    <div class="absolute -top-6 -right-6 h-24 w-24 rounded-full bg-purple-500/5 blur-2xl transition-all group-hover:bg-purple-500/10"></div>
                    <div class="relative z-10 flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-500/10 text-purple-600 shadow-sm transition-all group-hover:bg-purple-600 group-hover:text-white">
                                <Megaphone class="h-5 w-5" />
                            </div>
                            <span class="text-[10px] font-bold tracking-tight text-purple-600/40 uppercase">Broadcasts</span>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-foreground tabular-nums">{{ stats.active_announcements }}</h3>
                            <p class="text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase mt-1">Active Institutional Alerts</p>
                        </div>
                    </div>
                </div>

                <!-- Unread Messages -->
                <div class="group relative overflow-hidden rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:border-blue-500/20">
                    <div class="absolute -top-6 -right-6 h-24 w-24 rounded-full bg-blue-500/5 blur-2xl transition-all group-hover:bg-blue-500/10"></div>
                    <div class="relative z-10 flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-500/10 text-blue-600 shadow-sm transition-all group-hover:bg-blue-600 group-hover:text-white">
                                <MessageSquare class="h-5 w-5" />
                            </div>
                            <span class="text-[10px] font-bold tracking-tight text-blue-600/40 uppercase">Incoming</span>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-foreground tabular-nums">{{ stats.unread_messages }}</h3>
                            <p class="text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase mt-1">Pending Interaction Nodes</p>
                        </div>
                    </div>
                </div>

                <!-- Total SMS Sent -->
                <div class="group relative overflow-hidden rounded-2xl border border-border bg-card p-6 shadow-sm transition-all hover:border-emerald-500/20">
                    <div class="absolute -top-6 -right-6 h-24 w-24 rounded-full bg-emerald-500/5 blur-2xl transition-all group-hover:bg-emerald-500/10"></div>
                    <div class="relative z-10 flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 shadow-sm transition-all group-hover:bg-emerald-600 group-hover:text-white">
                                <Send class="h-5 w-5" />
                            </div>
                            <span class="text-[10px] font-bold tracking-tight text-emerald-600/40 uppercase">Egress Flux</span>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-foreground tabular-nums">{{ stats.total_sent_sms.toLocaleString() }}</h3>
                            <p class="text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase mt-1">SMS Dispatched This Cycle</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Split -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 px-1">
                <!-- Announcement Feed -->
                <div class="overflow-hidden rounded-3xl border border-border bg-card shadow-sm flex flex-col">
                    <div class="flex items-center justify-between border-b border-border/50 bg-muted/10 px-8 py-6">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-500/10 text-purple-600">
                                <Megaphone class="h-5 w-5" />
                            </div>
                            <h3 class="text-sm font-bold tracking-tight text-foreground uppercase">Broadcast Feed</h3>
                        </div>
                        <Button variant="ghost" size="sm" as-child class="h-9 rounded-xl text-[10px] font-bold tracking-tight uppercase hover:bg-muted">
                            <Link href="/communication/announcements">Archive Feed</Link>
                        </Button>
                    </div>

                    <div class="divide-y divide-border/30 overflow-y-auto max-h-[600px] scrollbar-hide">
                        <div
                            v-for="announcement in recentAnnouncements"
                            :key="announcement.id"
                            class="group relative p-8 transition-all hover:bg-muted/10"
                        >
                            <div v-if="announcement.is_pinned" class="absolute top-0 right-0">
                                <Badge class="rounded-bl-2xl rounded-tr-none px-4 py-1.5 bg-purple-600 text-[10px] font-bold tracking-widest text-white shadow-lg border-0 uppercase">PINNED</Badge>
                            </div>
                            
                            <div class="flex items-start gap-6">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-muted/30 border border-border/50 text-muted-foreground/40 transition-colors group-hover:text-purple-600 group-hover:bg-purple-500/5">
                                    <Bell class="h-6 w-6" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h4 class="text-sm font-bold text-foreground group-hover:text-purple-600 transition-colors uppercase truncate pr-16 leading-tight">
                                            {{ announcement.title }}
                                        </h4>
                                        <Badge variant="outline" class="h-5 rounded-lg text-[9px] font-bold tracking-tighter uppercase px-2">{{ announcement.type }}</Badge>
                                    </div>
                                    <p class="text-[11px] leading-relaxed text-muted-foreground/60 line-clamp-2 mb-4">
                                        {{ announcement.content }}
                                    </p>
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] font-bold tracking-tight text-muted-foreground/30 uppercase">{{ formatDate(announcement.publish_at) }}</span>
                                        <Button variant="ghost" size="sm" as-child class="h-8 rounded-lg opacity-0 group-hover:opacity-100 transition-all text-[10px] font-bold tracking-tight uppercase">
                                            <Link :href="`/communication/announcements/${announcement.id}`">Inspect Broadcast <ArrowUpRight class="ml-2 h-3.5 w-3.5" /></Link>
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-if="recentAnnouncements.length === 0" class="flex flex-col items-center justify-center p-20 text-center">
                            <div class="h-16 w-16 rounded-3xl bg-muted/20 flex items-center justify-center mb-4">
                                <Megaphone class="h-8 w-8 text-muted-foreground/20" />
                            </div>
                            <p class="text-xs font-bold text-muted-foreground/40 uppercase tracking-widest">Zero Broadcasts in Current Cycle</p>
                        </div>
                    </div>
                </div>

                <!-- Strategic Outreach -->
                <div class="space-y-6 flex flex-col">
                    <!-- Notification Vector Card -->
                    <div class="relative overflow-hidden rounded-3xl bg-slate-900 px-8 py-10 text-white shadow-2xl flex-1">
                        <div class="absolute inset-0 opacity-[0.05] grayscale pointer-events-none">
                            <svg class="h-full w-full" xmlns="http://www.w3.org/2000/svg" width="100%" height="100%">
                                <defs><pattern id="grid-comm" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="currentColor" stroke-width="1"/></pattern></defs>
                                <rect width="100%" height="100%" fill="url(#grid-comm)" />
                            </svg>
                        </div>
                        
                        <div class="relative z-10 space-y-8 h-full flex flex-col justify-between">
                            <div class="space-y-6">
                                <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-white/5 border border-white/10 shadow-2xl transition-all hover:bg-purple-500/20 hover:scale-110">
                                    <Send class="h-10 w-10 text-purple-500" />
                                </div>
                                <div class="space-y-3">
                                    <h4 class="text-2xl font-bold tracking-tight">Mass Outreach Vectors</h4>
                                    <p class="text-sm text-white/50 font-medium leading-relaxed max-w-xs">
                                        Execute high-priority broadcasts across SMS and Email channels. Ensure all institutional signals reach the parent-child matrix.
                                    </p>
                                </div>
                            </div>

                            <div class="grid gap-3 pt-4">
                                <Button as-child class="h-14 rounded-2xl bg-purple-600 font-bold text-[10px] tracking-tight text-white uppercase shadow-xl shadow-purple-900/20 hover:bg-purple-500 transition-all active:scale-95 text-white">
                                    <Link href="/communication/sms/create">Deploy SMS Broadcast <Send class="ml-2 h-4 w-4" /></Link>
                                </Button>
                                <Button variant="outline" as-child class="h-14 rounded-2xl border-white/10 bg-white/5 text-white hover:bg-white/10 transition-all font-bold text-[10px] tracking-tight uppercase">
                                    <Link href="/communication/email/create">Send Email Blast <Mail class="ml-2 h-4 w-4" /></Link>
                                </Button>
                            </div>
                        </div>
                    </div>

                    <!-- Templates Card -->
                    <div class="rounded-3xl border border-border bg-card p-8 shadow-sm">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="h-10 w-10 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-600 ring-4 ring-blue-500/5">
                                <Sparkles class="h-5 w-5" />
                            </div>
                            <div>
                                <h3 class="text-xs font-bold tracking-tight text-foreground uppercase leading-none">Response Matrices</h3>
                                <p class="text-[10px] font-bold text-muted-foreground/40 uppercase mt-1">Pre-synchronized communication</p>
                            </div>
                        </div>
                        
                        <p class="text-[11px] leading-relaxed text-muted-foreground/60 mb-6">
                            Standardized templates for fee escalations, performance results, and emergency alerts.
                        </p>
                        
                        <Button variant="outline" as-child class="w-full h-11 rounded-xl border-border bg-background text-[10px] font-bold tracking-tight uppercase hover:bg-muted">
                            <Link href="/communication/templates"><History class="mr-2 h-4 w-4 opacity-40" /> Manage Blueprints</Link>
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
