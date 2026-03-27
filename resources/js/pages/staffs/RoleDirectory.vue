<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { 
    Users, Search, Filter, Mail, Phone, Building2, 
    UserCheck, BadgeCheck, ArrowRight, LayoutGrid, List as ListIcon,
    ChevronDown, GraduationCap, Briefcase, Eye, Plus, MoreHorizontal
} from 'lucide-vue-next';
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    role: {
        id: number;
        name: string;
        display_name: string;
    };
    staffs: any; 
    departments: any[];
    filters: any;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'User Directory', href: '/staffs/directory' },
    { title: props.role.display_name, href: `/staffs/directory/${props.role.name}` },
];

const searchQuery = ref(props.filters?.search || '');
const departmentFilter = ref(props.filters?.department_id ?? 'all');
const viewMode = ref(props.filters?.view || 'grid');

const handleSearch = () => {
    router.get(`/staffs/directory/${props.role.name}`, { 
        search: searchQuery.value,
        department_id: departmentFilter.value,
        view: viewMode.value,
    }, { preserveState: true, replace: true });
};

watch([searchQuery, departmentFilter, viewMode], () => {
    handleSearch();
});

const getStatusColor = (status: string) => {
    switch (status?.toLowerCase()) {
        case 'active': return 'bg-emerald-500 text-white';
        case 'on_leave': return 'bg-amber-500 text-white';
        default: return 'bg-slate-400 text-white';
    }
};
</script>

<template>
    <Head :title="role.display_name + ' Directory'" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700 max-w-[1600px] mx-auto pb-20 p-6 md:p-8">
            
            <!-- Standard Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Badge variant="secondary" class="bg-blue-50 text-blue-700 hover:bg-blue-50 border-blue-100 rounded-lg px-2 text-[10px] font-bold uppercase tracking-wider">
                            {{ role.display_name }}
                        </Badge>
                        <h1 class="text-3xl font-bold tracking-tight text-foreground">Personnel Registry</h1>
                    </div>
                    <p class="text-[15px] text-muted-foreground">
                        Managing {{ staffs.total }} users categorized as {{ role.display_name }}.
                    </p>
                </div>
                
                <div class="flex items-center gap-3">
                    <Link
                        :href="`/staffs/create?role=${role.name}`"
                        class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 h-10 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition-all font-inter"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        Add {{ role.display_name }}
                    </Link>
                    <div class="flex p-1 bg-muted/50 rounded-xl border border-border">
                        <button 
                            @click="viewMode = 'grid'"
                            class="p-2 rounded-lg transition-all"
                            :class="viewMode === 'grid' ? 'bg-white shadow-sm text-blue-600' : 'text-muted-foreground hover:text-foreground'"
                        >
                            <LayoutGrid class="h-4 w-4" />
                        </button>
                        <button 
                            @click="viewMode = 'list'"
                            class="p-2 rounded-lg transition-all"
                            :class="viewMode === 'list' ? 'bg-white shadow-sm text-blue-600' : 'text-muted-foreground hover:text-foreground'"
                        >
                            <ListIcon class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filters Toolbar - Integrated -->
            <div class="flex flex-col md:flex-row gap-4 items-center justify-between bg-card p-4 rounded-2xl border border-border shadow-sm">
                <div class="relative flex-1 w-full md:max-w-md">
                    <Search class="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input 
                        v-model="searchQuery" 
                        placeholder="Search by name or number..." 
                        class="pl-11 h-11 bg-muted/30 border-none rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20"
                    />
                </div>
                
                <div class="flex items-center gap-3 w-full md:w-auto">
                    <Select v-model="departmentFilter">
                        <SelectTrigger class="h-11 w-full md:min-w-[200px] rounded-xl border-border bg-background px-4 text-sm font-medium">
                            <SelectValue placeholder="All Departments" />
                        </SelectTrigger>
                        <SelectContent class="rounded-xl">
                            <SelectItem value="all">All Departments</SelectItem>
                            <SelectItem v-for="dept in departments" :key="dept.id" :value="dept.id.toString()">
                                {{ dept.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <!-- Grid Listing -->
            <div v-if="viewMode === 'grid' && staffs.total > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <div 
                    v-for="item in staffs.data" 
                    :key="item.id"
                    class="group relative flex flex-col p-6 rounded-2xl border border-border bg-card shadow-sm hover:shadow-md hover:border-blue-500/30 transition-all duration-300"
                >
                    <div class="flex flex-col items-center text-center space-y-4">
                        <div class="relative">
                            <div class="h-24 w-24 rounded-2xl overflow-hidden ring-4 ring-muted bg-muted flex items-center justify-center">
                                <img v-if="item.photo_url" :src="item.photo_url" class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-500" />
                                <div v-else class="text-2xl font-bold text-blue-600/40 uppercase">
                                    {{ item.first_name[0] }}{{ item.last_name[0] }}
                                </div>
                            </div>
                            <div class="absolute -bottom-1 -right-1 h-6 w-6 rounded-lg bg-white shadow-md border border-border flex items-center justify-center">
                                <div class="h-2.5 w-2.5 rounded-full" :class="item.status === 'active' ? 'bg-emerald-500' : 'bg-amber-500'"></div>
                            </div>
                        </div>
                        
                        <div class="space-y-1">
                            <h3 class="text-lg font-bold text-foreground group-hover:text-blue-600 transition-colors line-clamp-1">
                                {{ item.first_name }} {{ item.last_name }}
                            </h3>
                            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">{{ item.staff_number }}</p>
                        </div>
                        
                        <div class="w-full pt-4 border-t border-border flex flex-col gap-3">
                            <div class="flex items-center justify-center gap-2">
                                <Badge variant="outline" class="rounded-md bg-muted/30 border-none px-2 py-0.5 font-bold text-[9px] text-muted-foreground uppercase truncate max-w-full">
                                    {{ item.department?.name || 'Institutional' }}
                                </Badge>
                            </div>
                            
                            <Button as-child variant="outline" class="w-full h-10 rounded-xl text-xs font-semibold hover:bg-blue-600 hover:text-white transition-all">
                                <Link :href="`/staffs/${item.id}`">View Profile</Link>
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- List Listing -->
            <div v-else-if="viewMode === 'list' && staffs.total > 0" class="bg-card rounded-2xl border border-border shadow-sm overflow-hidden">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-muted/30 border-b">
                            <th class="p-6 text-[10px] font-bold uppercase tracking-widest text-muted-foreground">User Information</th>
                            <th class="p-6 text-[10px] font-bold uppercase tracking-widest text-muted-foreground">Department</th>
                            <th class="p-6 text-[10px] font-bold uppercase tracking-widest text-muted-foreground text-center">Status</th>
                            <th class="p-6 text-[10px] font-bold uppercase tracking-widest text-muted-foreground text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr v-for="item in staffs.data" :key="item.id" class="group hover:bg-muted/20 transition-all">
                            <td class="p-6">
                                <div class="flex items-center gap-4">
                                    <div class="h-10 w-10 rounded-lg overflow-hidden bg-muted flex items-center justify-center shrink-0">
                                        <img v-if="item.photo_url" :src="item.photo_url" class="h-full w-full object-cover" />
                                        <div v-else class="text-sm font-bold text-blue-600/40 uppercase">
                                            {{ item.first_name[0] }}{{ item.last_name[0] }}
                                        </div>
                                    </div>
                                    <div>
                                        <p class="font-bold text-foreground leading-tight">{{ item.first_name }} {{ item.last_name }}</p>
                                        <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mt-0.5">{{ item.staff_number }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-6">
                                <Badge variant="outline" class="rounded-lg bg-blue-50 text-blue-700 border-blue-100 font-bold px-3 py-1 text-[9px] uppercase">
                                    {{ item.department?.name || 'Institutional' }}
                                </Badge>
                            </td>
                            <td class="p-6 text-center">
                                <div class="flex items-center justify-center">
                                    <Badge :class="getStatusColor(item.status)" class="rounded-lg font-bold px-3 py-1 text-[9px] uppercase border-none shadow-sm capitalize">
                                        {{ item.status }}
                                    </Badge>
                                </div>
                            </td>
                            <td class="p-6 text-right">
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="ghost" size="icon" class="h-8 w-8 rounded-lg text-muted-foreground hover:text-foreground">
                                            <MoreHorizontal class="h-4 w-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end" class="rounded-xl">
                                        <DropdownMenuItem as-child>
                                            <Link :href="`/staffs/${item.id}`" class="flex items-center gap-2">
                                                <Eye class="h-4 w-4" /> View Profile
                                            </Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem as-child>
                                            <Link :href="`/staffs/${item.id}/edit`" class="flex items-center gap-2 text-blue-600">
                                                <Edit class="h-4 w-4" /> Edit User
                                            </Link>
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div v-else class="flex flex-col items-center justify-center py-32 space-y-4 bg-muted/10 rounded-3xl border border-dashed">
                <div class="h-16 w-16 rounded-2xl bg-muted flex items-center justify-center">
                    <Users class="h-8 w-8 text-muted-foreground/30" />
                </div>
                <div class="text-center space-y-2">
                    <h3 class="text-xl font-bold text-foreground leading-tight">No Results Found</h3>
                    <p class="text-sm text-muted-foreground max-w-xs mx-auto italic">We couldn't find any users in this category matching your criteria.</p>
                </div>
                <Button variant="outline" class="rounded-xl mt-4" @click="searchQuery = ''; departmentFilter = 'all'">
                    Reset Filters
                </Button>
            </div>

            <!-- Pagination -->
            <div v-if="staffs.last_page > 1" class="flex items-center justify-between border-t border-border pt-8">
                <p class="text-xs font-semibold text-muted-foreground">
                    Showing {{ staffs.from }} to {{ staffs.to }} of {{ staffs.total }} users
                </p>
                <div class="flex items-center gap-2">
                    <Button variant="outline" class="h-9 px-4 rounded-xl text-xs font-bold" :disabled="staffs.current_page <= 1" @click="router.get(staffs.prev_page_url)">
                        Previous
                    </Button>
                    <div class="h-9 px-4 flex items-center justify-center rounded-xl bg-blue-600 text-white font-bold text-xs shadow-sm">
                        {{ staffs.current_page }}
                    </div>
                    <Button variant="outline" class="h-9 px-4 rounded-xl text-xs font-bold" :disabled="staffs.current_page >= staffs.last_page" @click="router.get(staffs.next_page_url)">
                        Next
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
