<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Clock, Plus, Search, Filter, MoreHorizontal, Eye, CheckCircle2, AlertTriangle, User, BookOpen } from 'lucide-vue-next';
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
        case 'loaned': return 'secondary';
        case 'returned': return 'default';
        case 'overdue': return 'destructive';
        default: return 'secondary';
    }
};

const getStatusColor = (status: string, dueDate: string) => {
    if (status === 'loaned' && new Date(dueDate) < new Date()) return 'bg-rose-500 hover:bg-rose-600';
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
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-500/10">
                        <Clock class="h-6 w-6 text-purple-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Book Loans</h1>
                        <p class="text-muted-foreground">Track issuance and returns of library resources</p>
                    </div>
                </div>
                <Button as-child class="bg-purple-600 hover:bg-purple-700">
                    <Link href="/library/loans/create">
                        <Plus class="mr-2 h-4 w-4" />Issue Book
                    </Link>
                </Button>
            </div>

            <div class="flex flex-col gap-4 rounded-xl border bg-card p-4 shadow-sm md:flex-row md:items-center">
                <div class="relative flex-1 max-w-sm">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input v-model="searchQuery" placeholder="Search student or book..." class="pl-9 h-10" />
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

            <div class="rounded-2xl border bg-card shadow-sm overflow-hidden overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-b">
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500">Student / Borrower</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500">Book Title</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500">Loan Date</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500">Due Date</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500">Status</th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase text-slate-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y text-sm">
                        <tr v-for="loan in loans.data" :key="loan.id" class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-400">
                                         {{ loan.loaned_by?.name ? loan.loaned_by.name.substring(0,2).toUpperCase() : '??' }}
                                    </div>
                                    <div class="font-bold text-slate-900">{{ loan.loaned_by?.name }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 font-medium text-slate-600">
                                {{ loan.book_copy?.book?.title }}
                            </td>
                            <td class="px-6 py-4">
                                {{ formatDate(loan.loan_date) }}
                            </td>
                            <td class="px-6 py-4">
                                <span :class="new Date(loan.due_date) < new Date() && loan.status === 'loaned' ? 'text-rose-600 font-bold' : ''">
                                    {{ formatDate(loan.due_date) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <Badge :variant="getStatusVariant(loan.status)" 
                                    class="rounded-full px-3 py-0.5 text-[10px] font-bold uppercase tracking-wider"
                                    :class="getStatusColor(loan.status, loan.due_date)"
                                >
                                    {{ loan.status }}
                                </Badge>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="ghost" size="icon" class="h-8 w-8"><MoreHorizontal class="h-4 w-4" /></Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end" class="w-40">
                                        <DropdownMenuItem><Eye class="mr-2 h-4 w-4" />Details</DropdownMenuItem>
                                        <DropdownMenuItem v-if="loan.status === 'loaned'" class="text-emerald-600"><CheckCircle2 class="mr-2 h-4 w-4" />Mark Returned</DropdownMenuItem>
                                        <DropdownMenuItem v-if="loan.status === 'loaned'"><Clock class="mr-2 h-4 w-4" />Extend Loan</DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </td>
                        </tr>
                        <tr v-if="loans.data.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-muted-foreground">
                                <BookOpen class="h-10 w-10 mx-auto mb-2 opacity-20" />
                                <p>No loan records found.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="loans.links.length > 3" class="flex justify-center mt-4">
                <nav class="flex gap-1">
                    <template v-for="(link, k) in loans.links" :key="k">
                        <Link v-if="link.url" 
                            :href="link.url" 
                            class="px-4 py-2 text-sm rounded-lg border transition-colors"
                            :class="link.active ? 'bg-purple-600 text-white border-purple-600 font-bold' : 'bg-card text-slate-600 hover:bg-slate-50'"
                            v-html="link.label"
                        />
                        <span v-else 
                            class="px-4 py-2 text-sm rounded-lg border bg-slate-100 text-slate-400 cursor-not-allowed opacity-50"
                            v-html="link.label"
                        />
                    </template>
                </nav>
            </div>
        </div>
    </AppLayout>
</template>
