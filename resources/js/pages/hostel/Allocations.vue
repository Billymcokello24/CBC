<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Bed, Plus, Search, Filter, MoreHorizontal, Eye, Edit, Trash2, User, Home, Calendar } from 'lucide-vue-next';
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
    allocations: any; // Paginator
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Hostels', href: '/hostels' },
    { title: 'Allocations', href: '/hostels/allocations' },
];

const searchQuery = ref('');

const getStatusColor = (status: string) => {
    switch (status.toLowerCase()) {
        case 'active': return 'bg-emerald-500 hover:bg-emerald-600';
        case 'pending': return 'bg-amber-500 hover:bg-amber-600';
        case 'completed': return 'bg-slate-500 hover:bg-slate-600';
        default: return 'bg-slate-500';
    }
};

const formatDate = (date: string | null) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString();
};

</script>

<template>
    <Head title="Hostel Allocations" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-500/10">
                        <Bed class="h-6 w-6 text-indigo-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Hostel Allocations</h1>
                        <p class="text-muted-foreground">Manage student room assignments and boarding residency log</p>
                    </div>
                </div>
                <Button as-child class="bg-indigo-600 hover:bg-indigo-700">
                    <Link href="/hostels/allocations/create">
                        <Plus class="mr-2 h-4 w-4" />New Allocation
                    </Link>
                </Button>
            </div>

            <div class="flex flex-col gap-4 rounded-xl border bg-card p-4 shadow-sm md:flex-row md:items-center">
                <div class="relative flex-1 max-w-sm">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input v-model="searchQuery" placeholder="Search student or hostel..." class="pl-9 h-10" />
                </div>
                <Button variant="outline" size="sm" class="h-10">
                    <Filter class="mr-2 h-4 w-4" />Hostels
                </Button>
            </div>

            <div class="rounded-2xl border bg-card shadow-sm overflow-hidden overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-b">
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-wider">Student</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-wider">Hostel / Block</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-wider">Bed #</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-wider">Date</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-wider">Status</th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase text-slate-500 tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y text-sm">
                        <tr v-for="allocation in allocations.data" :key="allocation.id" class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-400">
                                         <User class="h-4 w-4" />
                                    </div>
                                    <div class="font-bold text-slate-900">{{ allocation.student?.first_name }} {{ allocation.student?.last_name }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <Home class="h-3.5 w-3.5 text-indigo-500" />
                                    <span class="font-medium text-slate-600">{{ allocation.hostel?.name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 font-mono text-xs uppercase text-slate-500">
                                Bed-{{ allocation.bed_id || allocation.id }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-1.5 text-xs text-slate-500">
                                    <Calendar class="h-3 w-3" /> {{ formatDate(allocation.allocation_date) }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <Badge :class="getStatusColor(allocation.status)" class="rounded-full px-3 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white">
                                    {{ allocation.status }}
                                </Badge>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="ghost" size="icon" class="h-8 w-8"><MoreHorizontal class="h-4 w-4" /></Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end" class="w-40">
                                        <DropdownMenuItem><Eye class="mr-2 h-4 w-4" />View Profile</DropdownMenuItem>
                                        <DropdownMenuItem><Edit class="mr-2 h-4 w-4" />Modify Room</DropdownMenuItem>
                                        <DropdownMenuItem class="text-rose-600 font-bold"><Trash2 class="mr-2 h-4 w-4" />Deallocate</DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </td>
                        </tr>
                        <tr v-if="allocations.data.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-muted-foreground italic">
                                No hostel allocations found for current term.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="allocations.links.length > 3" class="flex justify-center mt-4">
                <nav class="flex gap-1">
                    <template v-for="(link, k) in allocations.links" :key="k">
                        <Link v-if="link.url" 
                            :href="link.url" 
                            class="px-4 py-2 text-sm rounded-lg border transition-colors"
                            :class="link.active ? 'bg-indigo-600 text-white border-indigo-600 font-bold' : 'bg-card text-slate-600 hover:bg-slate-50'"
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
