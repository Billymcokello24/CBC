<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { 
    School as SchoolIcon, 
    Plus, 
    Search, 
    MoreHorizontal, 
    ExternalLink, 
    Edit, 
    Trash2,
    MapPin,
    Users,
    GraduationCap,
    TrendingUp,
    Filter,
    LayoutGrid,
    List,
    Download
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { impersonate } from '@/routes/super-admin';
import schoolsRoutes from '@/routes/super-admin/schools';
import type { BreadcrumbItem } from '@/types';
import { 
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger 
} from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';

interface School {
    id: number;
    name: string;
    code: string;
    county: string;
    status: 'active' | 'inactive';
    users_count: number;
    students_count: number;
    created_at: string;
}

interface Props {
    schools: {
        data: School[];
        links: any[];
        meta: any;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Global Dashboard', href: '/super-admin/dashboard' },
    { title: 'Tenant Registry', href: '/super-admin/schools' },
];

const stats = {
    total: props.schools.meta?.total || props.schools.data.length,
    active: props.schools.data.filter(s => s.status === 'active').length,
    total_students: props.schools.data.reduce((acc, s) => acc + s.students_count, 0)
};
</script>

<template>
    <Head title="Tenant Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-full bg-slate-50/50 p-6 lg:p-10 space-y-10 animate-in fade-in slide-in-from-bottom-4 duration-700">
            <!-- Header Section -->
            <div class="flex flex-col gap-6 md:flex-row md:items-end md:justify-between lg:gap-10">
                <div class="space-y-1">
                    <h1 class="text-4xl font-black tracking-tight text-slate-900 leading-none">Tenant <span class="text-transparent bg-clip-text bg-gradient-to-r from-violet-600 to-indigo-600">Registry</span></h1>
                    <p class="text-slate-500 font-medium text-lg">Orchestrate and monitor independent school infrastructure shards.</p>
                </div>
                
                <div class="flex flex-wrap items-center gap-3">
                    <div class="hidden sm:flex items-center gap-4 px-6 py-3 bg-white rounded-2xl border border-slate-200 shadow-sm mr-2">
                         <div class="flex flex-col">
                              <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Global Capacity</span>
                              <span class="text-xs font-black text-slate-900">{{ stats.total_students.toLocaleString() }} Identities</span>
                         </div>
                         <div class="h-8 w-px bg-slate-100"></div>
                         <div class="flex flex-col">
                              <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Active Shards</span>
                              <span class="text-xs font-black text-slate-900">{{ stats.total }} Schools</span>
                         </div>
                    </div>

                    <Link :href="schoolsRoutes.create().url">
                        <Button class="relative group h-14 px-8 rounded-2xl bg-slate-900 text-white font-black overflow-hidden shadow-xl shadow-slate-200 transition-all hover:scale-[1.02] active:scale-[0.98]">
                            <div class="absolute inset-0 bg-gradient-to-r from-violet-600 to-indigo-600 opacity-0 transition-opacity group-hover:opacity-100"></div>
                            <Plus class="relative mr-2 h-5 w-5" />
                            <span class="relative">Provision Tenant</span>
                        </Button>
                    </Link>
                </div>
            </div>

            <!-- Table Card -->
            <div class="rounded-[2.5rem] border border-slate-200/60 bg-white shadow-xl shadow-slate-200/30 overflow-hidden">
                <!-- Toolbar -->
                <div class="p-8 border-b border-slate-100 bg-slate-50/30 backdrop-blur-md flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div class="relative w-full lg:w-[400px]">
                        <Search class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" />
                        <Input 
                            placeholder="Universal shard search..." 
                            class="pl-12 h-14 rounded-2xl border-slate-200/80 bg-white/50 text-base font-bold text-slate-900 placeholder:text-slate-300 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500 transition-all" 
                        />
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-3">
                         <Button variant="outline" class="h-12 px-5 rounded-xl border-slate-200 font-bold text-slate-600 hover:bg-slate-50">
                              <Filter class="mr-2 h-4 w-4" />
                              Advanced Filters
                         </Button>
                         <Button variant="outline" class="h-12 px-5 rounded-xl border-slate-200 font-bold text-slate-600 hover:bg-slate-50">
                              <Download class="mr-2 h-4 w-4" />
                              Export Logs
                         </Button>
                         <div class="h-8 w-px bg-slate-200 mx-2 hidden lg:block"></div>
                         <div class="flex items-center bg-slate-100 p-1 rounded-xl">
                              <button class="p-2 rounded-lg bg-white shadow-sm text-slate-900"><List class="h-4 w-4" /></button>
                              <button class="p-2 rounded-lg text-slate-400"><LayoutGrid class="h-4 w-4" /></button>
                         </div>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/20">
                                <th class="p-8 text-[11px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 w-[40%]">Institution Entity</th>
                                <th class="p-8 text-[11px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Sharding Node</th>
                                <th class="p-8 text-[11px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Resources</th>
                                <th class="p-8 text-[11px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Status</th>
                                <th class="p-8 text-[11px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 text-right">Operations</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="school in schools.data" :key="school.id" class="group transition-all hover:bg-slate-50/50">
                                <td class="p-8">
                                    <div class="flex items-center gap-6">
                                        <div class="relative flex h-16 w-16 shrink-0 items-center justify-center rounded-[1.25rem] bg-gradient-to-br from-violet-50 to-indigo-50 border border-violet-100 text-violet-600 font-black text-xl shadow-sm transition-transform group-hover:scale-105 group-hover:-rotate-2">
                                            {{ school.name[0].toUpperCase() }}
                                            <div class="absolute -right-1 -top-1 h-4 w-4 rounded-full border-2 border-white bg-emerald-500 shadow-sm" v-if="school.status === 'active'"></div>
                                        </div>
                                        <div class="flex flex-col space-y-0.5">
                                            <span class="text-xl font-black text-slate-900 tracking-tight leading-tight group-hover:text-violet-600 transition-colors">{{ school.name }}</span>
                                            <div class="flex items-center gap-2 text-xs font-bold text-slate-400">
                                                 <span class="uppercase tracking-widest px-2 py-0.5 bg-slate-100 rounded text-[10px]">{{ school.code }}</span>
                                                 <span class="flex items-center gap-1.5 ml-2"><MapPin class="h-3.5 w-3.5" /> {{ school.county || 'Unmapped' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-8">
                                     <div class="flex flex-col">
                                          <span class="text-sm font-black text-slate-900 uppercase tracking-widest">Shard-{{ school.id.toString().padStart(3, '0') }}</span>
                                          <span class="text-[10px] font-bold text-slate-400 uppercase mt-1">AWS-Region: AF-South-1</span>
                                     </div>
                                </td>
                                <td class="p-8">
                                    <div class="flex items-center gap-8">
                                        <div class="flex flex-col">
                                            <div class="flex items-center gap-1.5 font-black text-slate-900">
                                                 <Users class="h-3.5 w-3.5 text-slate-300" />
                                                 {{ school.users_count }}
                                            </div>
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Staff Node</span>
                                        </div>
                                        <div class="flex flex-col">
                                            <div class="flex items-center gap-1.5 font-black text-slate-900">
                                                 <GraduationCap class="h-3.5 w-3.5 text-slate-300" />
                                                 {{ school.students_count }}
                                            </div>
                                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Learners</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-8">
                                    <div :class="[
                                         'inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-widest',
                                         school.status === 'active' 
                                            ? 'bg-emerald-50 text-emerald-600 ring-1 ring-emerald-100' 
                                            : 'bg-slate-100 text-slate-400 ring-1 ring-slate-200'
                                    ]">
                                        <div :class="['h-1.5 w-1.5 rounded-full', school.status === 'active' ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400']"></div>
                                        {{ school.status }}
                                    </div>
                                </td>
                                <td class="p-8 text-right">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" class="h-12 w-12 p-0 rounded-2xl hover:bg-slate-100 transition-colors">
                                                <MoreHorizontal class="h-6 w-6 text-slate-400" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end" class="w-64 rounded-3xl p-2 shadow-2xl border-slate-100 border backdrop-blur-xl bg-white/95">
                                            <DropdownMenuLabel class="px-3 py-4 text-[11px] font-black uppercase text-slate-400 tracking-widest">Shard Operations</DropdownMenuLabel>
                                            <DropdownMenuItem as-child>
                                                <Link :href="impersonate(school).url" method="post" as="button" class="group w-full text-left font-black text-sm flex items-center px-4 py-4 rounded-2xl cursor-pointer hover:bg-violet-50 hover:text-violet-600 transition-all">
                                                    <ExternalLink class="mr-3 h-5 w-5 text-slate-300 group-hover:text-violet-500" />
                                                    Impersonate Tenant
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator class="bg-slate-50" />
                                            <DropdownMenuItem as-child>
                                                <Link :href="schoolsRoutes.edit(school).url" class="group font-black text-sm flex items-center px-4 py-4 rounded-2xl cursor-pointer hover:bg-slate-50 transition-all">
                                                    <Edit class="mr-3 h-5 w-5 text-slate-300 group-hover:text-slate-900" />
                                                    Manage Shard
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem class="group font-black text-sm flex items-center px-4 py-4 rounded-2xl text-rose-600 hover:bg-rose-50 cursor-pointer transition-all">
                                                <Trash2 class="mr-3 h-5 w-5 text-rose-300 group-hover:text-rose-500" />
                                                Terminate Node
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer Pagination -->
                <div class="p-8 border-t border-slate-100 bg-slate-50/20 flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                     <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">
                          Showing 1 - {{ schools.data.length }} of {{ stats.total }} tenant nodes
                     </p>
                     <div class="flex items-center gap-2">
                          <Button disabled variant="outline" class="h-10 px-4 rounded-xl border-slate-200 font-bold text-slate-400">Previous</Button>
                          <Button variant="outline" class="h-10 px-4 rounded-xl border-slate-200 font-bold text-slate-900 bg-white shadow-sm ring-1 ring-slate-200 hover:bg-slate-50">Next Node</Button>
                     </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.tracking-widest {
    letter-spacing: 0.15em;
}
</style>
