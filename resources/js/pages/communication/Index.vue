<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Megaphone,
    MessageSquare,
    Send,
    Bell,
    Plus,
    ArrowRight,
    Mail,
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
    { title: 'Communication Center', href: '/communication' },
];

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString();
};
</script>

<template>
    <Head title="Communication Dashboard" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="font-pulsar flex h-full flex-1 flex-col gap-6 p-6">
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-500/10"
                    >
                        <Megaphone class="h-6 w-6 text-purple-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">
                            Communication
                        </h1>
                        <p class="text-muted-foreground">
                            Manage school broadcasts, SMS alerts and internal
                            messaging
                        </p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" as-child
                        ><Link href="/communication/messages"
                            >Open Inbox</Link
                        ></Button
                    >
                    <Button
                        as-child
                        class="font-pulsar bg-purple-600 hover:bg-purple-700"
                        ><Plus class="mr-2 h-4 w-4" />New Announcement</Button
                    >
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    class="rounded-xl border border-l-4 border-l-purple-500 bg-card p-6 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <div class="rounded-lg bg-purple-500/10 p-2">
                            <Megaphone class="h-5 w-5 text-purple-600" />
                        </div>
                        <span
                            class="text-xs font-bold text-purple-600 uppercase"
                            >Live</span
                        >
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-muted-foreground">
                            Active Announcements
                        </p>
                        <p class="text-2xl font-bold text-purple-600">
                            {{ stats.active_announcements }}
                        </p>
                    </div>
                </div>
                <div
                    class="rounded-xl border border-l-4 border-l-blue-500 bg-card p-6 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <div class="rounded-lg bg-blue-500/10 p-2">
                            <MessageSquare class="h-5 w-5 text-blue-600" />
                        </div>
                        <span class="text-xs font-bold text-blue-600 uppercase"
                            >Inbox</span
                        >
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-muted-foreground">
                            Unread Messages
                        </p>
                        <p class="text-2xl font-bold text-blue-600">
                            {{ stats.unread_messages }}
                        </p>
                    </div>
                </div>
                <div class="rounded-xl border bg-card p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="rounded-lg bg-slate-500/10 p-2">
                            <Send class="h-5 w-5 text-slate-600" />
                        </div>
                        <span class="text-xs font-bold text-slate-600 uppercase"
                            >Analytics</span
                        >
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-muted-foreground">
                            SMS Sent This Month
                        </p>
                        <p class="text-2xl font-bold">
                            {{ stats.total_sent_sms }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold">Latest Announcements</h2>
                        <Link
                            href="/communication/announcements"
                            class="text-sm font-bold text-purple-600 hover:underline"
                            >View History</Link
                        >
                    </div>
                    <div
                        v-for="announcement in recentAnnouncements"
                        :key="announcement.id"
                        class="relative overflow-hidden rounded-xl border bg-card p-5 shadow-sm transition-colors hover:border-purple-200"
                    >
                        <div
                            v-if="announcement.is_pinned"
                            class="absolute top-0 right-0"
                        >
                            <div
                                class="rounded-bl-lg bg-purple-600 px-3 py-1 text-xs font-bold tracking-tight text-white uppercase"
                            >
                                PINNED
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-50"
                            >
                                <Bell class="h-5 w-5 text-slate-400" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3
                                    class="truncate pr-16 font-bold text-slate-900"
                                >
                                    {{ announcement.title }}
                                </h3>
                                <p
                                    class="mt-1 mb-3 line-clamp-2 text-xs text-muted-foreground"
                                >
                                    {{ announcement.content }}
                                </p>
                                <div
                                    class="flex items-center justify-between text-xs font-bold tracking-tight text-slate-400 uppercase"
                                >
                                    <span>{{
                                        formatDate(announcement.publish_at)
                                    }}</span>
                                    <span class="flex items-center gap-1"
                                        ><Plus class="h-3 w-3" />
                                        {{ announcement.type }}</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        v-if="recentAnnouncements.length === 0"
                        class="rounded-xl border-2 border-dashed p-12 text-center text-muted-foreground"
                    >
                        No active announcements for current term.
                    </div>
                </div>

                <div class="space-y-6">
                    <div
                        class="rounded-xl border bg-slate-900 p-8 text-white shadow-xl"
                    >
                        <h2 class="mb-2 text-2xl font-bold">
                            Mass Notification
                        </h2>
                        <p class="mb-6 text-sm text-slate-400">
                            Send SMS or Email alerts to parents, students or
                            staff in bulk.
                        </p>
                        <div class="grid gap-3">
                            <Button
                                as-child
                                size="lg"
                                class="font-pulsar w-full justify-between bg-purple-600 hover:bg-purple-700"
                            >
                                <Link href="/communication/sms/compose"
                                    >Broadcast SMS <Send class="h-4 w-4"
                                /></Link>
                            </Button>
                            <Button
                                variant="outline"
                                as-child
                                size="lg"
                                class="font-pulsar w-full justify-between border-white/20 bg-white/10 text-white hover:bg-white/20"
                            >
                                <Link href="/communication/email/compose"
                                    >Send Email Blast <Mail class="h-4 w-4"
                                /></Link>
                            </Button>
                        </div>
                    </div>

                    <div class="rounded-xl border bg-card p-6 shadow-sm">
                        <h3 class="mb-4 font-bold">Message Templates</h3>
                        <p class="mb-4 text-sm text-muted-foreground">
                            Standardized responses for fee reminders, results
                            and events.
                        </p>
                        <Button variant="secondary" class="w-full"
                            >Manage Templates</Button
                        >
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
