<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Clock,
    Plus,
    Search,
    Filter,
    MoreHorizontal,
    Eye,
    CheckCircle2,
    AlertTriangle,
    User,
    BookOpen,
} from 'lucide-vue-next';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    loans: any; // Paginator
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Library', href: '/library' },
    { title: 'Book Loans', href: '/library/loans' },
];

const searchQuery = ref('');

const getStatusVariant = (status: string) => {
    switch (status.toLowerCase()) {
        case 'loaned':
            return 'secondary';
        case 'returned':
            return 'default';
        case 'overdue':
            return 'destructive';
        default:
            return 'secondary';
    }
};

const getStatusColor = (status: string, dueDate: string) => {
    if (status === 'loaned' && new Date(dueDate) < new Date())
        return 'bg-rose-500 hover:bg-rose-600';
    if (status === 'loaned') return 'bg-purple-500 hover:bg-purple-600';
    if (status === 'returned') return 'bg-emerald-500 hover:bg-emerald-600';
    return '';
};

const formatDate = (date: string | null) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString();
};
</script>

<template>
    <Head title="Book Loans" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-500/10"
                    >
                        <Clock class="h-6 w-6 text-purple-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">
                            Book Loans
                        </h1>
                        <p class="text-muted-foreground">
                            Track issuance and returns of library resources
                        </p>
                    </div>
                </div>
                <Button as-child class="bg-purple-600 hover:bg-purple-700">
                    <Link href="/library/loans/create">
                        <Plus class="mr-2 h-4 w-4" />Issue Book
                    </Link>
                </Button>
            </div>

            <div
                class="flex flex-col gap-4 rounded-xl border bg-card p-4 shadow-sm md:flex-row md:items-center"
            >
                <div class="relative max-w-sm flex-1">
                    <Search
                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        v-model="searchQuery"
                        placeholder="Search student or book..."
                        class="h-10 pl-9"
                    />
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" class="h-10">
                        <Filter class="mr-2 h-4 w-4" />Status
                    </Button>
                    <Button variant="outline" size="sm" class="h-10">
                        Active Only
                    </Button>
                </div>
            </div>

            <div
                class="overflow-hidden overflow-x-auto rounded-2xl border bg-card shadow-sm"
            >
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b bg-slate-50">
                            <th
                                class="px-6 py-4 text-xs font-bold text-slate-500 uppercase"
                            >
                                Student / Borrower
                            </th>
                            <th
                                class="px-6 py-4 text-xs font-bold text-slate-500 uppercase"
                            >
                                Book Title
                            </th>
                            <th
                                class="px-6 py-4 text-xs font-bold text-slate-500 uppercase"
                            >
                                Loan Date
                            </th>
                            <th
                                class="px-6 py-4 text-xs font-bold text-slate-500 uppercase"
                            >
                                Due Date
                            </th>
                            <th
                                class="px-6 py-4 text-xs font-bold text-slate-500 uppercase"
                            >
                                Status
                            </th>
                            <th
                                class="px-6 py-4 text-right text-xs font-bold text-slate-500 uppercase"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y text-sm">
                        <tr
                            v-for="loan in loans.data"
                            :key="loan.id"
                            class="transition-colors hover:bg-slate-50/50"
                        >
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-400"
                                    >
                                        {{
                                            loan.loaned_by?.name
                                                ? loan.loaned_by.name
                                                      .substring(0, 2)
                                                      .toUpperCase()
                                                : '??'
                                        }}
                                    </div>
                                    <div class="font-bold text-slate-900">
                                        {{ loan.loaned_by?.name }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 font-medium text-slate-600">
                                {{ loan.book_copy?.book?.title }}
                            </td>
                            <td class="px-6 py-4">
                                {{ formatDate(loan.loan_date) }}
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    :class="
                                        new Date(loan.due_date) < new Date() &&
                                        loan.status === 'loaned'
                                            ? 'font-bold text-rose-600'
                                            : ''
                                    "
                                >
                                    {{ formatDate(loan.due_date) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <Badge
                                    :variant="getStatusVariant(loan.status)"
                                    class="rounded-full px-3 py-0.5 text-xs font-bold tracking-wider uppercase"
                                    :class="
                                        getStatusColor(
                                            loan.status,
                                            loan.due_date,
                                        )
                                    "
                                >
                                    {{ loan.status }}
                                </Badge>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-8 w-8"
                                            ><MoreHorizontal class="h-4 w-4"
                                        /></Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent
                                        align="end"
                                        class="w-40"
                                    >
                                        <DropdownMenuItem
                                            ><Eye
                                                class="mr-2 h-4 w-4"
                                            />Details</DropdownMenuItem
                                        >
                                        <DropdownMenuItem
                                            v-if="loan.status === 'loaned'"
                                            class="text-emerald-600"
                                            ><CheckCircle2
                                                class="mr-2 h-4 w-4"
                                            />Mark Returned</DropdownMenuItem
                                        >
                                        <DropdownMenuItem
                                            v-if="loan.status === 'loaned'"
                                            ><Clock
                                                class="mr-2 h-4 w-4"
                                            />Extend Loan</DropdownMenuItem
                                        >
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </td>
                        </tr>
                        <tr v-if="loans.data.length === 0">
                            <td
                                colspan="6"
                                class="px-6 py-12 text-center text-muted-foreground"
                            >
                                <BookOpen
                                    class="mx-auto mb-2 h-10 w-10 opacity-20"
                                />
                                <p>No loan records found.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="loans.links.length > 3" class="mt-4 flex justify-center">
                <nav class="flex gap-1">
                    <template v-for="(link, k) in loans.links" :key="k">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="rounded-lg border px-4 py-2 text-sm transition-colors"
                            :class="
                                link.active
                                    ? 'border-purple-600 bg-purple-600 font-bold text-white'
                                    : 'bg-card text-slate-600 hover:bg-slate-50'
                            "
                            v-html="link.label"
                        />
                        <span
                            v-else
                            class="cursor-not-allowed rounded-lg border bg-slate-100 px-4 py-2 text-sm text-slate-400 opacity-50"
                            v-html="link.label"
                        />
                    </template>
                </nav>
            </div>
        </div>
    </AppLayout>
</template>
