<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Users, Search, Plus, Filter, MoreHorizontal, Eye, Edit, Trash2, Mail, Phone, Building2, UserCheck, ShieldCheck, BadgeCheck } from 'lucide-vue-next';
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
    teachers: any;
    departments: any[];
    stats: {
        total: number;
        active: number;
        on_leave: number;
        departments: number;
    };
    filters: any;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Staff Directory', href: '/teachers' },
];

const searchQuery = ref(props.filters.search || '');

const getStatusColor = (status: string) => {
    switch (status?.toLowerCase()) {
        case 'active': return 'bg-emerald-500 text-white shadow-[0_0_8px_rgba(16,185,129,0.4)]';
        case 'on_leave': return 'bg-amber-500 text-white';
        case 'inactive': return 'bg-slate-400 text-white';
        default: return 'bg-slate-400 text-white';
    }
};

</script>

<template>
    <Head title="Staff Directory" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 font-pulsar mt-2">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-500/10">
                        <Users class="h-6 w-6 text-violet-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Staff Directory</h1>
                        <p class="text-muted-foreground font-medium">Manage academic faculty, department heads and staff roles</p>
                    </div>
                </div>
                <div class="flex gap-2">
                     <Button variant="outline" class="font-pulsar h-10 border-slate-200">
                        <UserCheck class="mr-2 h-4 w-4" />Manage Roles
                     </Button>
                     <Button as-child class="bg-violet-600 hover:bg-violet-700 font-pulsar shadow-lg h-10">
                        <Link href="/teachers/create">
                            <Plus class="mr-2 h-4 w-4" />Onboard Staff
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Dashboard Stats -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border bg-card p-5 shadow-sm border-l-4 border-l-violet-500 flex items-center gap-4 group hover:shadow-md transition-all">
                    <div class="rounded-xl bg-violet-500/10 p-3 group-hover:bg-violet-500 group-hover:text-white transition-all"><Users class="h-5 w-5 text-violet-600 group-hover:text-white" /></div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Total Staff</p>
                        <p class="text-xl font-black text-slate-900">{{ stats.total }} Personnel</p>
                    </div>
                </div>
                <div class="rounded-2xl border bg-card p-5 shadow-sm border-l-4 border-l-emerald-500 flex items-center gap-4 group hover:shadow-md transition-all">
                    <div class="rounded-xl bg-emerald-500/10 p-3 group-hover:bg-emerald-500 group-hover:text-white transition-all"><UserCheck class="h-5 w-5 text-emerald-600 group-hover:text-white" /></div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Active Faculty</p>
                        <p class="text-xl font-black text-emerald-600">{{ stats.active }} Pulse</p>
                    </div>
                </div>
                <div class="rounded-2xl border bg-card p-5 shadow-sm border-l-4 border-l-amber-500 flex items-center gap-4 group hover:shadow-md transition-all">
                    <div class="rounded-xl bg-amber-500/10 p-3 group-hover:bg-amber-500 group-hover:text-white transition-all"><Building2 class="h-5 w-5 text-amber-600 group-hover:text-white" /></div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Departments</p>
                        <p class="text-xl font-black text-slate-900">{{ stats.departments }} Nodes</p>
                    </div>
                </div>
                <div class="rounded-2xl border bg-card p-5 shadow-sm border-l-4 border-l-indigo-500 flex items-center gap-4 group hover:shadow-md transition-all">
                    <div class="rounded-xl bg-indigo-500/10 p-3 group-hover:bg-indigo-500 group-hover:text-white transition-all"><ShieldCheck class="h-5 w-5 text-indigo-600 group-hover:text-white" /></div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Access Control</p>
                        <p class="text-xl font-black text-indigo-600 uppercase tracking-tighter">SECURED</p>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center justify-between rounded-xl border bg-card p-4 shadow-sm">
                <div class="relative flex-1 max-w-sm">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground font-medium" />
                    <Input v-model="searchQuery" placeholder="Search by name, ID or TSC..." class="pl-9 h-10 border-slate-200" />
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" class="h-10 font-pulsar border-slate-200">
                        <Filter class="mr-2 h-4 w-4" />Department
                    </Button>
                    <Button variant="outline" size="sm" class="h-10 font-pulsar border-slate-200">
                        <Filter class="mr-2 h-4 w-4" />Status
                    </Button>
                </div>
            </div>

            <!-- Unified List View -->
            <div class="rounded-3xl border bg-card shadow-sm overflow-hidden overflow-x-auto border-t-4 border-t-violet-500">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-b font-pulsar">
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-widest">Teacher / Faculty</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-widest">TSC / Staff ID</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-widest">Department</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-widest">Contact</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-widest">Status</th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase text-slate-500 tracking-widest">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y text-sm">
                        <tr v-for="teacher in teachers.data" :key="teacher.id" class="group hover:bg-slate-50/70 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-xl bg-violet-600/10 flex items-center justify-center font-black text-violet-700 group-hover:bg-violet-600 group-hover:text-white transition-all text-sm shadow-inner border border-violet-100 uppercase">
                                         {{ teacher.first_name[0] }}{{ teacher.last_name[0] }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 group-hover:text-violet-700 transition-colors">{{ teacher.first_name }} {{ teacher.last_name }}</div>
                                        <div class="text-[10px] font-black text-slate-400 mt-0.5 tracking-widest uppercase">{{ teacher.gender }} Faculty</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-0.5">
                                     <div class="font-black text-[10px] text-slate-900 tracking-widest">{{ teacher.staff_number }}</div>
                                     <div class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">TSC: {{ teacher.tsc_number || 'N/A' }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <Link v-if="teacher.department" :href="`/departments/${teacher.department_id}`" class="text-xs font-bold text-indigo-600 hover:underline">
                                    {{ teacher.department.name }}
                                </Link>
                                <span v-else class="text-xs text-slate-400 italic">Unassigned</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-1 flex flex-col items-start justify-center">
                                     <div class="flex items-center gap-1.5 text-xs text-slate-600 font-medium tracking-tight">
                                          <Mail class="h-3 w-3 text-slate-400" /> {{ teacher.email }}
                                     </div>
                                     <div class="flex items-center gap-1.5 text-xs text-slate-600 font-medium tracking-tight">
                                          <Phone class="h-3 w-3 text-slate-400" /> {{ teacher.phone }}
                                     </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <Badge :class="getStatusColor(teacher.status)" class="rounded-full px-2 py-0.5 text-[8px] font-black uppercase tracking-widest border-0">
                                    {{ teacher.status }}
                                </Badge>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="ghost" size="icon" class="h-8 w-8"><MoreHorizontal class="h-4 w-4" /></Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end" class="w-44 font-pulsar">
                                        <DropdownMenuItem as-child>
                                            <Link :href="`/teachers/${teacher.id}`"><Eye class="mr-2 h-4 w-4" />View Full Profile</Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem as-child>
                                            <Link :href="`/teachers/${teacher.id}/edit`"><Edit class="mr-2 h-4 w-4" />Edit Account</Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem class="text-rose-600 font-bold"><Trash2 class="mr-2 h-4 w-4" />Terminate Access</DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </td>
                        </tr>
                        <tr v-if="teachers.data.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-muted-foreground italic font-medium">
                                No faculty members found in the current cluster search.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Callout -->
            <div class="mt-6 p-5 rounded-2xl bg-slate-900 text-white flex flex-col md:flex-row items-center justify-between gap-6 shadow-xl border border-slate-700">
                <div class="flex items-center gap-4">
                    <div class="h-10 w-10 rounded-xl bg-white/10 flex items-center justify-center">
                         <BadgeCheck class="h-5 w-5 text-emerald-400" />
                    </div>
                    <div>
                        <h3 class="font-bold text-sm tracking-tight text-white/90">Staff Synchronization Active</h3>
                        <p class="text-slate-400 text-xs mt-0.5">Showing faculty assignments for the 2026 Academic Year.</p>
                    </div>
                </div>
                <!-- Mock Pagination for UI -->
                 <div class="flex gap-2">
                      <Button variant="outline" size="sm" class="bg-white/5 border-white/10 text-white hover:bg-white/20 h-9 font-bold">1</Button>
                      <Button variant="outline" size="sm" class="bg-transparent border-white/10 text-slate-400 hover:bg-white/5 h-9 font-bold">2</Button>
                      <Button variant="outline" size="px-4" class="bg-transparent border-white/10 text-slate-400 h-9 font-bold">Next <ArrowRight class="ml-2 h-3 w-3" /></Button>
                 </div>
            </div>
        </div>
    </AppLayout>
</template>
