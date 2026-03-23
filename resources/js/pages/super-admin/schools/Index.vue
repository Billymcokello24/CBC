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
    GraduationCap
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
    { title: 'SaaS Dashboard', href: '/super-admin/dashboard' },
    { title: 'Schools', href: '/super-admin/schools' },
];
</script>

<template>
    <Head title="Manage Schools" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100">Schools Management</h1>
                    <p class="text-muted-foreground text-sm font-medium">Manage and onboard educational institutions.</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="schoolsRoutes.create().url">
                        <Button class="bg-violet-600 hover:bg-violet-700 text-white font-bold h-10 px-6 rounded-xl shadow-lg shadow-violet-200 transition-all active:scale-95">
                            <Plus class="mr-2 h-4 w-4" />
                            Onboard New School
                        </Button>
                    </Link>
                </div>
            </div>

            <div class="rounded-2xl border bg-card/50 backdrop-blur-sm shadow-sm md:p-1 overflow-hidden">
                <div class="flex flex-col gap-4 p-4 md:flex-row md:items-center md:justify-between">
                    <div class="relative w-full md:w-80">
                        <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input placeholder="Search schools..." class="pl-10 h-10 rounded-xl border-gray-200 focus:ring-violet-500" />
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left md:table-fixed">
                        <thead>
                            <tr class="border-b bg-muted/30 transition-colors">
                                <th class="h-12 px-4 text-left align-middle font-black text-gray-500 uppercase tracking-[0.1em] text-[10px] w-[300px]">Institution</th>
                                <th class="h-12 px-4 text-left align-middle font-black text-gray-500 uppercase tracking-[0.1em] text-[10px] w-[150px]">Location</th>
                                <th class="h-12 px-4 text-left align-middle font-black text-gray-500 uppercase tracking-[0.1em] text-[10px] w-[120px]">Learners</th>
                                <th class="h-12 px-4 text-left align-middle font-black text-gray-500 uppercase tracking-[0.1em] text-[10px] w-[100px]">Status</th>
                                <th class="h-12 px-4 text-right align-middle font-black text-gray-500 uppercase tracking-[0.1em] text-[10px] w-[100px]">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            <tr v-for="school in schools.data" :key="school.id" class="border-b transition-colors hover:bg-muted/30">
                                <td class="p-4 align-middle">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600 font-black">
                                            {{ school.name[0].toUpperCase() }}
                                        </div>
                                        <div class="flex flex-col min-w-0">
                                            <span class="font-bold text-gray-900 truncate">{{ school.name }}</span>
                                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-tighter">{{ school.code }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4 align-middle">
                                    <div class="flex items-center gap-2 text-gray-600 font-medium">
                                        <MapPin class="h-3.5 w-3.5 text-gray-400" />
                                        <span>{{ school.county || 'N/A' }}</span>
                                    </div>
                                </td>
                                <td class="p-4 align-middle">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-gray-900">{{ school.students_count }}</span>
                                        <span class="text-[10px] font-black text-gray-400 uppercase">Enrolled</span>
                                    </div>
                                </td>
                                <td class="p-4 align-middle">
                                    <Badge :variant="school.status === 'active' ? 'outline' : 'destructive'" class="capitalize rounded-lg px-2 py-0.5 font-bold tracking-tight">
                                        <div :class="['mr-1.5 h-1.5 w-1.5 rounded-full', school.status === 'active' ? 'bg-green-500' : 'bg-red-500']"></div>
                                        {{ school.status }}
                                    </Badge>
                                </td>
                                <td class="p-4 align-middle text-right">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" class="h-8 w-8 p-0 rounded-lg hover:bg-gray-100">
                                                <MoreHorizontal class="h-4 w-4" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end" class="w-48 rounded-xl p-1 shadow-2xl">
                                            <DropdownMenuLabel class="px-2 py-1.5 text-[10px] font-black uppercase text-gray-400">Actions</DropdownMenuLabel>
                                            <DropdownMenuItem as-child>
                                                <Link :href="impersonate(school).url" method="post" as="button" class="w-full text-left font-bold text-xs flex items-center px-2 py-2 rounded-lg cursor-pointer">
                                                    <ExternalLink class="mr-2 h-4 w-4" />
                                                    Impersonate Admin
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem as-child>
                                                <Link :href="schoolsRoutes.edit(school).url" class="font-bold text-xs flex items-center px-2 py-2 rounded-lg cursor-pointer">
                                                    <Edit class="mr-2 h-4 w-4" />
                                                    Edit Details
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem class="font-bold text-xs flex items-center px-2 py-2 rounded-lg text-red-600 hover:bg-red-50 cursor-pointer">
                                                <Trash2 class="mr-2 h-4 w-4" />
                                                Deactivate School
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
