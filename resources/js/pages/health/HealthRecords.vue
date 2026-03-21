<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ClipboardList, Plus, Search, Filter, MoreHorizontal, Eye, Edit, Trash2, User, Activity, AlertCircle, Heart } from 'lucide-vue-next';
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
    records: any; // Paginator
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Health', href: '/health' },
    { title: 'Health Records', href: '/health/records' },
];

const searchQuery = ref('');

</script>

<template>
    <Head title="Student Health Records" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 font-pulsar">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-rose-500/10">
                        <ClipboardList class="h-6 w-6 text-rose-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Student Health Records</h1>
                        <p class="text-muted-foreground">Comprehensive student medical backgrounds and wellness data</p>
                    </div>
                </div>
                <Button as-child class="bg-rose-600 hover:bg-rose-700">
                    <Link href="/health/records/create">
                        <Plus class="mr-2 h-4 w-4" />New Record
                    </Link>
                </Button>
            </div>

            <div class="flex flex-col gap-4 rounded-xl border bg-card p-4 shadow-sm md:flex-row md:items-center">
                <div class="relative flex-1 max-w-sm">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input v-model="searchQuery" placeholder="Search student or condition..." class="pl-9 h-10" />
                </div>
                <Button variant="outline" size="sm" class="h-10">
                    <Filter class="mr-2 h-4 w-4" />Alerts Only
                </Button>
            </div>

            <div class="rounded-2xl border bg-card shadow-sm overflow-hidden overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-b">
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-wider">Student</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-wider">Blood/Genotype</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-wider">Allergies</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-wider">Chronic Conditions</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-wider">Last Exam</th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase text-slate-500 tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y text-sm">
                        <tr v-for="record in records.data" :key="record.id" class="group hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-400">
                                         <User class="h-4 w-4" />
                                    </div>
                                    <div class="font-bold text-slate-900">{{ record.student?.first_name }} {{ record.student?.last_name }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="font-bold text-rose-600">{{ record.blood_group || '—' }}</span>
                                    <span class="text-[10px] text-slate-400 font-bold tracking-widest">{{ record.genotype || '—' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div v-if="record.allergies" class="flex flex-wrap gap-1">
                                    <Badge variant="outline" class="bg-rose-50 text-rose-700 border-rose-100 text-[9px] font-bold uppercase">{{ record.allergies }}</Badge>
                                </div>
                                <span v-else class="text-slate-300">None</span>
                            </td>
                            <td class="px-6 py-4">
                                <div v-if="record.chronic_conditions" class="flex flex-wrap gap-1">
                                    <Badge variant="outline" class="bg-amber-50 text-amber-700 border-amber-100 text-[9px] font-bold uppercase">{{ record.chronic_conditions }}</Badge>
                                </div>
                                <span v-else class="text-slate-300">None</span>
                            </td>
                            <td class="px-6 py-4 text-slate-500 text-xs">
                                {{ record.last_physical_exam ? new Date(record.last_physical_exam).toLocaleDateString() : '—' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="ghost" size="icon" class="h-8 w-8"><MoreHorizontal class="h-4 w-4" /></Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end" class="w-40">
                                        <DropdownMenuItem><Eye class="mr-2 h-4 w-4" />View Full Profile</DropdownMenuItem>
                                        <DropdownMenuItem><Edit class="mr-2 h-4 w-4" />Update Record</DropdownMenuItem>
                                        <DropdownMenuItem><Heart class="mr-2 h-4 w-4" />Vaccination Log</DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </td>
                        </tr>
                        <tr v-if="records.data.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-muted-foreground italic">
                                No health records initialized for current term.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="records.links.length > 3" class="flex justify-center mt-4">
                <nav class="flex gap-1">
                    <template v-for="(link, k) in records.links" :key="k">
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
