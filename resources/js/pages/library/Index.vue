<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    BookOpen,
    BookCheck,
    Clock,
    AlertTriangle,
    Search,
    Plus,
    ExternalLink,
    User,
} from 'lucide-vue-next';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    stats: {
        total_books: number;
        available_books: number;
        active_loans: number;
        overdue_loans: number;
    };
    recentLoans: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Library', href: '/library' },
];

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString();
};
</script>

<template>
    <Head title="Library Dashboard" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-500/10"
                    >
                        <BookOpen class="h-6 w-6 text-purple-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">
                            Library
                        </h1>
                        <p class="text-muted-foreground">
                            Manage school books, catalog and loan distribution
                        </p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" as-child
                        ><Link href="/library/books"
                            ><ExternalLink class="mr-2 h-4 w-4" />View
                            Catalog</Link
                        ></Button
                    >
                    <Button
                        as-child
                        class="font-pulsar bg-purple-600 hover:bg-purple-700"
                        ><Link href="/library/books/create"
                            ><Plus class="mr-2 h-4 w-4" />Add New Book</Link
                        ></Button
                    >
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border bg-card p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="rounded-lg bg-blue-500/10 p-2">
                            <BookOpen class="h-5 w-5 text-blue-600" />
                        </div>
                        <span
                            class="text-xs font-medium tracking-wider text-blue-600 uppercase"
                            >Total</span
                        >
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-muted-foreground">Total Books</p>
                        <p class="text-2xl font-bold">
                            {{ stats.total_books }}
                        </p>
                    </div>
                </div>
                <div
                    class="rounded-xl border border-l-4 border-l-emerald-500 bg-card p-6 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <div class="rounded-lg bg-emerald-500/10 p-2">
                            <BookCheck class="h-5 w-5 text-emerald-600" />
                        </div>
                        <span
                            class="text-xs font-medium tracking-wider text-emerald-600 uppercase"
                            >Available</span
                        >
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-muted-foreground">
                            Available Copies
                        </p>
                        <p class="text-2xl font-bold text-emerald-600">
                            {{ stats.available_books }}
                        </p>
                    </div>
                </div>
                <div class="rounded-xl border bg-card p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="rounded-lg bg-purple-500/10 p-2">
                            <Clock class="h-5 w-5 text-purple-600" />
                        </div>
                        <span
                            class="text-xs font-medium tracking-wider text-purple-600 uppercase"
                            >Out</span
                        >
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-muted-foreground">
                            Active Loans
                        </p>
                        <p class="text-2xl font-bold text-purple-600">
                            {{ stats.active_loans }}
                        </p>
                    </div>
                </div>
                <div
                    class="rounded-xl border border-l-4 border-l-rose-500 bg-card p-6 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <div class="rounded-lg bg-rose-500/10 p-2">
                            <AlertTriangle class="h-5 w-5 text-rose-600" />
                        </div>
                        <span
                            class="text-xs font-medium tracking-wider text-rose-600 uppercase"
                            >Overdue</span
                        >
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-muted-foreground">Overdue</p>
                        <p class="text-2xl font-bold text-rose-600">
                            {{ stats.overdue_loans }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="rounded-xl border bg-card shadow-sm lg:col-span-2">
                    <div class="flex items-center justify-between border-b p-6">
                        <h2 class="text-lg font-bold">
                            Recent Loan Activities
                        </h2>
                        <Button variant="ghost" size="sm" as-child
                            ><Link href="/library/loans">View All</Link></Button
                        >
                    </div>
                    <div class="p-0">
                        <div
                            v-for="loan in recentLoans"
                            :key="loan.id"
                            class="flex items-center justify-between border-b p-4 transition-colors last:border-0 hover:bg-slate-50/50"
                        >
                            <div class="flex items-center gap-4">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100"
                                >
                                    <User class="h-5 w-5 text-slate-400" />
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900">
                                        {{ loan.book_copy?.book?.title }}
                                    </div>
                                    <div class="text-xs text-muted-foreground">
                                        {{ loan.loaned_by?.name }} • Due
                                        {{ formatDate(loan.due_date) }}
                                    </div>
                                </div>
                            </div>
                            <Badge
                                :variant="
                                    loan.status === 'returned'
                                        ? 'default'
                                        : 'secondary'
                                "
                                class="rounded-full px-2 py-0.5 text-xs font-bold uppercase"
                                :class="
                                    loan.status === 'loaned'
                                        ? 'border-purple-200 bg-purple-100 text-purple-700'
                                        : ''
                                "
                            >
                                {{ loan.status }}
                            </Badge>
                        </div>
                        <div
                            v-if="recentLoans.length === 0"
                            class="p-12 text-center text-muted-foreground"
                        >
                            No recent activities found.
                        </div>
                    </div>
                </div>
                <div class="space-y-6">
                    <div
                        class="rounded-xl border bg-gradient-to-br from-indigo-50 to-indigo-100/50 p-6 shadow-sm"
                    >
                        <h3 class="mb-2 font-bold text-indigo-900">
                            Library Status
                        </h3>
                        <p class="mb-4 text-sm text-indigo-700/80">
                            The library currently has
                            {{ stats.available_books }} books available for
                            loaning.
                        </p>
                        <Button
                            as-child
                            variant="outline"
                            class="w-full border-indigo-200 bg-white/50 text-indigo-700 hover:border-indigo-300 hover:bg-white"
                        >
                            <Link href="/library/loans/create"
                                >Issue a Book</Link
                            >
                        </Button>
                    </div>

                    <div class="rounded-xl border bg-card p-6 shadow-sm">
                        <h3 class="mb-4 font-bold">Quick Links</h3>
                        <div class="grid gap-2">
                            <Link
                                href="/library/books"
                                class="flex items-center rounded-lg p-2 text-sm font-medium transition-colors hover:bg-slate-50"
                            >
                                <span
                                    class="mr-2 h-2 w-2 rounded-full bg-blue-500"
                                ></span>
                                All Books Catalog
                            </Link>
                            <Link
                                href="/library/loans"
                                class="flex items-center rounded-lg p-2 text-sm font-medium transition-colors hover:bg-slate-50"
                            >
                                <span
                                    class="mr-2 h-2 w-2 rounded-full bg-purple-500"
                                ></span>
                                Active Loans
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
