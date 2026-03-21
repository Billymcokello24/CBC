<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Stethoscope, Plus, Search, Filter, MoreHorizontal, Eye, Edit, Trash2, User, Activity } from 'lucide-vue-next';
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
    visits: any; // Paginator
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Health', href: '/health' },
    { title: 'Clinic Visits', href: '/health/visits' },
];

const searchQuery = ref('');

const getVisitTypeColor = (type: string) => {
    switch (type?.toLowerCase()) {
        case 'emergency': return 'bg-rose-500 text-white';
        case 'routine': return 'bg-blue-500 text-white';
        case 'follow-up': return 'bg-amber-500 text-white';
        default: return 'bg-slate-500 text-white';
    }
};

const formatDate = (date: string | null) => {
    if (!date) return '—';
    return new Date(date).toLocaleString();
};

</script>

<template>
    <Head title="Clinic Visits" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 font-pulsar">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-rose-500/10">
                        <Stethoscope class="h-6 w-6 text-rose-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Clinic Visit Log</h1>
                        <p class="text-muted-foreground">Detailed record of student health assessments and treatments</p>
                    </div>
                </div>
                <Button as-child class="bg-rose-600 hover:bg-rose-700">
                    <Link href="/health/visits/create">
                        <Plus class="mr-2 h-4 w-4" />Log Visit
                    </Link>
                </Button>
            </div>

            <div class="flex flex-col gap-4 rounded-xl border bg-card p-4 shadow-sm md:flex-row md:items-center">
                <div class="relative flex-1 max-w-sm">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input v-model="searchQuery" placeholder="Search student or diagnosis..." class="pl-9 h-10" />
                </div>
                <Button variant="outline" size="sm" class="h-10">
                    <Filter class="mr-2 h-4 w-4" />Visit Type
                </Button>
            </div>

            <div class="rounded-2xl border bg-card shadow-sm overflow-hidden overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-b">
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-wider">Student</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-wider">Type</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-wider">Complaint / Symptoms</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-wider">Date & Time</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-wider">Diagnosis</th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase text-slate-500 tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y text-sm">
                        <tr v-for="visit in visits.data" :key="visit.id" class="group hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-400">
                                         <User class="h-4 w-4" />
                                    </div>
                                    <div class="font-bold text-slate-900">{{ visit.student?.first_name }} {{ visit.student?.last_name }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <Badge :class="getVisitTypeColor(visit.visit_type)" class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase border-0">
                                    {{ visit.visit_type }}
                                </Badge>
                            </td>
                            <td class="px-6 py-4">
                                <div class="max-w-xs truncate font-medium text-slate-600">
                                    {{ visit.chief_complaint }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-500 text-xs">
                                {{ formatDate(visit.visit_datetime) }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-1 text-blue-600 font-bold">
                                    <Activity class="h-3 w-3" /> {{ visit.diagnosis || 'General Checkup' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="ghost" size="icon" class="h-8 w-8"><MoreHorizontal class="h-4 w-4" /></Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end" class="w-40">
                                        <DropdownMenuItem><Eye class="mr-2 h-4 w-4" />Details</DropdownMenuItem>
                                        <DropdownMenuItem><Edit class="mr-2 h-4 w-4" />Edit Record</DropdownMenuItem>
                                        <DropdownMenuItem class="text-rose-600 font-bold"><Trash2 class="mr-2 h-4 w-4" />Delete</DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </td>
                        </tr>
                        <tr v-if="visits.data.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-muted-foreground italic">
                                No clinic visits in the current filter.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="visits.links.length > 3" class="flex justify-center mt-4">
                <nav class="flex gap-1">
                    <template v-for="(link, k) in visits.links" :key="k">
                        <Link v-if="link.url" 
                            :href="link.url" 
                            class="px-4 py-2 text-sm rounded-lg border transition-colors"
                            :class="link.active ? 'bg-rose-600 text-white border-rose-600 font-bold' : 'bg-card text-slate-600 hover:bg-slate-50'"
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
