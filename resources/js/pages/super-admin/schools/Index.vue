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
        total: number;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Global Dashboard', href: '/super-admin/dashboard' },
    { title: 'Tenant Registry', href: '/super-admin/schools' },
];

const stats = {
    total: props.schools.total,
    active: props.schools.data.filter(s => s.status === 'active').length,
    total_students: props.schools.data.reduce((acc, s) => acc + s.students_count, 0)
};
</script>

<template>
    <Head title="Tenant Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
            <!-- Header Section -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-foreground">Schools</h1>
                    <p class="text-sm font-bold text-muted-foreground/60 uppercase tracking-widest">Manage and monitor all registered school accounts.</p>
                </div>
                
                <div class="flex flex-wrap items-center gap-3">
                    <div class="hidden sm:flex items-center gap-4 px-4 py-2 bg-card rounded-xl border border-border shadow-sm mr-2 dark:border-white/5">
                         <div class="flex flex-col">
                              <span class="text-[8px] font-black text-muted-foreground/40 uppercase tracking-widest">Total Students</span>
                              <span class="text-xs font-black text-foreground">{{ stats.total_students.toLocaleString() }}</span>
                         </div>
                         <div class="h-6 w-px bg-border/50"></div>
                         <div class="flex flex-col">
                              <span class="text-[8px] font-black text-muted-foreground/40 uppercase tracking-widest">Institutions</span>
                              <span class="text-xs font-black text-foreground">{{ stats.total }}</span>
                         </div>
                    </div>

                    <Link :href="schoolsRoutes.create().url">
                        <Button class="h-11 px-6 rounded-xl bg-primary hover:bg-primary/90 text-white font-black shadow-lg shadow-primary/20 transition-all">
                            <Plus class="mr-2 h-4 w-4" />
                            <span class="text-sm font-black uppercase tracking-widest">Add School</span>
                        </Button>
                    </Link>
                </div>
            </div>

            <!-- Table Card -->
            <div class="rounded-2xl border border-border bg-card shadow-sm overflow-hidden dark:border-white/5">
                <!-- Toolbar -->
                <div class="p-4 border-b border-border bg-muted/20 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="relative w-full lg:w-[400px]">
                        <Search class="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/40" />
                        <Input 
                            placeholder="Search schools by name or code..." 
                            class="pl-10 h-10 rounded-xl border-border bg-background text-sm font-bold placeholder:text-muted-foreground/40 focus:ring-primary/20 transition-all" 
                        />
                    </div>
                    
                    <div class="flex flex-wrap items-center gap-2">
                         <Button variant="outline" class="h-10 px-4 rounded-xl border-border font-bold text-muted-foreground hover:bg-muted/50 text-[11px] uppercase tracking-wider">
                              <Filter class="mr-2 h-3.5 w-3.5 opacity-50" />
                              Filters
                         </Button>
                         <Button variant="outline" class="h-10 px-4 rounded-xl border-border font-bold text-muted-foreground hover:bg-muted/50 text-[11px] uppercase tracking-wider">
                              <Download class="mr-2 h-3.5 w-3.5 opacity-50" />
                              Export
                         </Button>
                         <div class="h-6 w-px bg-border mx-2 hidden lg:block"></div>
                         <div class="flex items-center bg-muted/50 p-1 rounded-lg border border-border">
                              <button class="p-1.5 rounded-md bg-background shadow-sm text-foreground"><List class="h-3.5 w-3.5" /></button>
                              <button class="p-1.5 rounded-md text-muted-foreground/50 hover:text-foreground"><LayoutGrid class="h-3.5 w-3.5" /></button>
                         </div>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-muted/10">
                                <th class="px-6 py-4 text-[10px] font-black text-muted-foreground/50 uppercase tracking-widest border-b border-border w-[40%]">School Information</th>
                                <th class="px-6 py-4 text-[10px] font-black text-muted-foreground/50 uppercase tracking-widest border-b border-border">Code</th>
                                <th class="px-6 py-4 text-[10px] font-black text-muted-foreground/50 uppercase tracking-widest border-b border-border">Population</th>
                                <th class="px-6 py-4 text-[10px] font-black text-muted-foreground/50 uppercase tracking-widest border-b border-border">Status</th>
                                <th class="px-6 py-4 text-[10px] font-black text-muted-foreground/50 uppercase tracking-widest border-b border-border text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/50">
                            <tr v-for="school in schools.data" :key="school.id" class="group transition-all hover:bg-muted/20">
                                <td class="px-6 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="relative flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-primary/10 border border-primary/10 text-primary font-black text-lg shadow-sm transition-transform group-hover:scale-105 group-hover:-rotate-2">
                                            {{ school.name[0].toUpperCase() }}
                                            <div class="absolute -right-1 -top-1 h-3 w-3 rounded-full border-2 border-card bg-emerald-500 shadow-sm" v-if="school.status === 'active'"></div>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-base font-black text-foreground tracking-tight leading-none group-hover:text-primary transition-colors">{{ school.name }}</span>
                                            <div class="flex items-center gap-2 mt-1 text-[10px] font-bold text-muted-foreground/60 uppercase tracking-tight">
                                                 <span class="flex items-center gap-1"><MapPin class="h-3 w-3 opacity-40" /> {{ school.county || 'Unmapped' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-6 font-mono text-[11px] font-black text-muted-foreground/80 tracking-widest">
                                     {{ school.code }}
                                </td>
                                <td class="px-6 py-6">
                                    <div class="flex items-center gap-6">
                                        <div class="flex flex-col">
                                            <div class="flex items-center gap-1 font-black text-foreground text-sm">
                                                 <Users class="h-3 w-3 text-primary opacity-30" />
                                                 {{ school.users_count }}
                                            </div>
                                            <span class="text-[8px] font-black text-muted-foreground/40 uppercase tracking-widest">Staff</span>
                                        </div>
                                        <div class="flex flex-col">
                                            <div class="flex items-center gap-1 font-black text-foreground text-sm">
                                                 <GraduationCap class="h-3 w-3 text-primary opacity-30" />
                                                 {{ school.students_count }}
                                            </div>
                                            <span class="text-[8px] font-black text-muted-foreground/40 uppercase tracking-widest">Learners</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-6">
                                    <Badge :variant="school.status === 'active' ? 'default' : 'secondary'" class="bg-emerald-500/10 text-emerald-600 border-0 font-black text-[9px] uppercase tracking-widest px-2.5 shadow-none" v-if="school.status === 'active'">
                                        <div class="h-1 w-1 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></div>
                                        Active
                                    </Badge>
                                    <Badge variant="outline" class="text-muted-foreground/40 border-muted font-black text-[9px] uppercase tracking-widest px-2.5 shadow-none" v-else>
                                        Inactive
                                    </Badge>
                                </td>
                                <td class="px-6 py-6 text-right">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" class="h-8 w-8 p-0 rounded-lg hover:bg-muted transition-colors">
                                                <MoreHorizontal class="h-4 w-4 text-muted-foreground/40" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end" class="w-56 rounded-xl p-1 shadow-2xl border-border border backdrop-blur-xl bg-card/95">
                                            <DropdownMenuLabel class="px-3 py-2 text-[9px] font-black uppercase text-muted-foreground/40 tracking-widest">School Management</DropdownMenuLabel>
                                            <DropdownMenuItem as-child>
                                                <Link :href="impersonate(school).url" method="post" as="button" class="group w-full text-left font-black text-[11px] uppercase tracking-wider flex items-center px-3 py-2.5 rounded-lg cursor-pointer hover:bg-primary/10 hover:text-primary transition-all">
                                                    <ExternalLink class="mr-2 h-3.5 w-3.5 text-muted-foreground/30 group-hover:text-primary" />
                                                    Impersonate
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator class="bg-border/50" />
                                            <DropdownMenuItem as-child>
                                                <Link :href="schoolsRoutes.edit(school).url" class="group font-black text-[11px] uppercase tracking-wider flex items-center px-3 py-2.5 rounded-lg cursor-pointer hover:bg-muted transition-all">
                                                    <Edit class="mr-2 h-3.5 w-3.5 text-muted-foreground/30 group-hover:text-foreground" />
                                                    Manage
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem class="group font-black text-[11px] uppercase tracking-wider flex items-center px-3 py-2.5 rounded-lg text-rose-600 hover:bg-rose-500/10 cursor-pointer transition-all">
                                                <Trash2 class="mr-2 h-3.5 w-3.5 text-rose-500/30 group-hover:text-rose-500" />
                                                Delete
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer Pagination -->
                <div class="p-4 border-t border-border bg-muted/20 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                     <p class="text-[10px] font-black text-muted-foreground/40 uppercase tracking-widest">
                          Showing 1 - {{ schools.data.length }} of {{ stats.total }} Schools
                     </p>
                     <div class="flex items-center gap-2">
                          <Button disabled variant="outline" class="h-9 px-4 rounded-lg border-border font-black text-[10px] uppercase tracking-widest text-muted-foreground/40">Prev</Button>
                          <Button variant="outline" class="h-9 px-4 rounded-lg border-border font-black text-[10px] uppercase tracking-widest text-foreground bg-background shadow-sm hover:bg-muted/50">Next</Button>
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
