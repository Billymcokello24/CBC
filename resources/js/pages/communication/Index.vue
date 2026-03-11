<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { MessageSquare, Plus, Search, Bell, Send, Users, Megaphone } from 'lucide-vue-next';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Communication', href: '/communication' },
];

const announcements = ref([
    { id: 1, title: 'Parents Meeting Scheduled', date: '2026-03-15', audience: 'All Parents', priority: 'high', status: 'published' },
    { id: 2, title: 'Term 1 Exams Timetable Released', date: '2026-03-12', audience: 'All Students', priority: 'normal', status: 'published' },
    { id: 3, title: 'School Fees Reminder', date: '2026-03-10', audience: 'Parents with Balances', priority: 'high', status: 'published' },
]);

const recentMessages = ref([
    { id: 1, from: 'Mrs. Jane Kamau', subject: 'Regarding John\'s Progress', time: '2 hours ago', unread: true },
    { id: 2, from: 'Mr. Peter Ochieng', subject: 'Transport Arrangement', time: '5 hours ago', unread: true },
    { id: 3, from: 'Grace Achieng', subject: 'Medical Certificate Submission', time: '1 day ago', unread: false },
]);
</script>

<template>
    <Head title="Communication" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-500/10">
                        <MessageSquare class="h-6 w-6 text-violet-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Communication</h1>
                        <p class="text-muted-foreground">Manage announcements, messages and notifications</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" as-child><Link href="/communication/messages"><Send class="mr-2 h-4 w-4" />Messages</Link></Button>
                    <Button as-child><Link href="/communication/announcements/create"><Plus class="mr-2 h-4 w-4" />New Announcement</Link></Button>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border bg-gradient-to-br from-violet-50 to-violet-100/50 p-4 dark:from-violet-950/50 dark:to-violet-900/30">
                    <div class="flex items-center gap-3"><Megaphone class="h-5 w-5 text-violet-600" /><span class="text-sm text-muted-foreground">Announcements</span></div>
                    <p class="mt-2 text-3xl font-bold text-violet-600">24</p>
                </div>
                <div class="rounded-xl border bg-gradient-to-br from-blue-50 to-blue-100/50 p-4 dark:from-blue-950/50 dark:to-blue-900/30">
                    <div class="flex items-center gap-3"><Send class="h-5 w-5 text-blue-600" /><span class="text-sm text-muted-foreground">Messages</span></div>
                    <p class="mt-2 text-3xl font-bold text-blue-600">156</p>
                </div>
                <div class="rounded-xl border bg-gradient-to-br from-amber-50 to-amber-100/50 p-4 dark:from-amber-950/50 dark:to-amber-900/30">
                    <div class="flex items-center gap-3"><Bell class="h-5 w-5 text-amber-600" /><span class="text-sm text-muted-foreground">Unread</span></div>
                    <p class="mt-2 text-3xl font-bold text-amber-600">12</p>
                </div>
                <div class="rounded-xl border bg-gradient-to-br from-green-50 to-green-100/50 p-4 dark:from-green-950/50 dark:to-green-900/30">
                    <div class="flex items-center gap-3"><Users class="h-5 w-5 text-green-600" /><span class="text-sm text-muted-foreground">SMS Sent Today</span></div>
                    <p class="mt-2 text-3xl font-bold text-green-600">89</p>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Recent Announcements -->
                <div class="rounded-xl border bg-card">
                    <div class="flex items-center justify-between border-b p-4">
                        <h3 class="font-semibold">Recent Announcements</h3>
                        <Button variant="ghost" size="sm" as-child><Link href="/communication/announcements">View All</Link></Button>
                    </div>
                    <div class="divide-y">
                        <div v-for="announcement in announcements" :key="announcement.id" class="p-4 hover:bg-muted/50">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h4 class="font-medium">{{ announcement.title }}</h4>
                                    <p class="text-sm text-muted-foreground">{{ announcement.audience }} • {{ announcement.date }}</p>
                                </div>
                                <Badge :variant="announcement.priority === 'high' ? 'destructive' : 'secondary'">{{ announcement.priority }}</Badge>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Messages -->
                <div class="rounded-xl border bg-card">
                    <div class="flex items-center justify-between border-b p-4">
                        <h3 class="font-semibold">Recent Messages</h3>
                        <Button variant="ghost" size="sm" as-child><Link href="/communication/messages">View All</Link></Button>
                    </div>
                    <div class="divide-y">
                        <div v-for="message in recentMessages" :key="message.id" class="flex items-start gap-3 p-4 hover:bg-muted/50">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-violet-500 to-violet-600 text-sm font-semibold text-white shrink-0">
                                {{ message.from.charAt(0) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium">{{ message.from }}</span>
                                    <span v-if="message.unread" class="h-2 w-2 rounded-full bg-blue-500"></span>
                                </div>
                                <p class="text-sm text-muted-foreground truncate">{{ message.subject }}</p>
                                <p class="text-xs text-muted-foreground">{{ message.time }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
