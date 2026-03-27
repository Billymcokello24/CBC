<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { 
    Users, Search, Filter, Mail, Phone, Building2, 
    UserPlus, BadgeCheck, LayoutGrid, List as ListIcon,
    ChevronDown, GraduationCap, Eye, Plus, MoreHorizontal,
    Heart, Home, MapPin, PhoneForwarded, ShieldCheck,
    Edit, Trash2
} from 'lucide-vue-next';
import { ref, watch, computed } from 'vue';
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
    parents: {
        data: any[];
        total: number;
        current_page: number;
        last_page: number;
        from: number;
        to: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    filters: {
        search?: string;
    };
    stats: {
        total: number;
        active: number;
        new_this_month: number;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'User Directory', href: '/staffs/directory' },
    { title: 'Parent Registry', href: '/parents' },
];

const searchQuery = ref(props.filters.search || '');
const viewMode = ref('grid');

let debounceTimer: any;
const applyFilters = () => {
    router.get('/parents', { 
        search: searchQuery.value,
    }, { preserveState: true, replace: true });
};

watch(searchQuery, () => {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => applyFilters(), 400);
});

const getStatusColor = (active: boolean) => {
    return active ? 'bg-emerald-500 text-white shadow-sm' : 'bg-slate-400 text-white';
};

const deleteParent = (id: number) => {
    if (confirm('Are you sure you want to delete this parent account? This will unlink all students.')) {
        router.delete(`/parents/${id}`);
    }
};
</script>

<template>
    <Head title="Parent Registry" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700 max-w-[1600px] mx-auto pb-20 p-6 md:p-8">
            
            <!-- Page Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <h1 class="text-3xl font-bold tracking-tight text-foreground">Parent Registry</h1>
                    <p class="text-[15px] text-muted-foreground">
                        Manage primary guardians and institutional parents.
                    </p>
                </div>
                
                <div class="flex items-center gap-3">
                    <Button variant="outline" class="h-10 px-4 rounded-xl border-border font-medium hover:bg-muted" @click="viewMode = viewMode === 'grid' ? 'list' : 'grid'">
                        <component :is="viewMode === 'grid' ? ListIcon : LayoutGrid" class="mr-2 h-4 w-4 opacity-70" />
                        {{ viewMode === 'grid' ? 'List View' : 'Grid View' }}
                    </Button>
                    <Link
                        href="/parents/create"
                        class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 h-10 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition-all"
                    >
                        <UserPlus class="mr-2 h-4 w-4" />
                        Add Parent
                    </Link>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-2xl border border-border bg-card p-6 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground mb-1">Total Parents</p>
                        <h3 class="text-2xl font-bold text-foreground">{{ stats.total.toLocaleString() }}</h3>
                    </div>
                    <div class="h-12 w-12 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600">
                        <Users class="h-6 w-6" />
                    </div>
                </div>

                <div class="rounded-2xl border border-border bg-card p-6 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground mb-1">Active Accounts</p>
                        <h3 class="text-2xl font-bold text-foreground">{{ stats.active.toLocaleString() }}</h3>
                    </div>
                    <div class="h-12 w-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                        <ShieldCheck class="h-6 w-6" />
                    </div>
                </div>

                <div class="rounded-2xl border border-border bg-card p-6 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground mb-1">New Registrations</p>
                        <h3 class="text-2xl font-bold text-foreground">+{{ stats.new_this_month }}</h3>
                        <p class="text-[10px] text-muted-foreground mt-0.5 font-bold uppercase tracking-wider">This Month</p>
                    </div>
                    <div class="h-12 w-12 rounded-2xl bg-blue-50/50 flex items-center justify-center text-blue-500">
                        <UserPlus class="h-6 w-6" />
                    </div>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="relative max-w-md">
                <Search class="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                <Input 
                    v-model="searchQuery" 
                    placeholder="Search by name, phone or email..." 
                    class="pl-11 h-11 bg-card border-border rounded-xl text-sm focus:ring-2 focus:ring-indigo-600/10"
                />
            </div>

            <!-- Grid View -->
            <div v-if="viewMode === 'grid' && parents.total > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <div 
                    v-for="parent in parents.data" 
                    :key="parent.id"
                    class="group relative flex flex-col p-6 rounded-2xl border border-border bg-card shadow-sm hover:shadow-md hover:border-blue-500/30 transition-all duration-300"
                >
                    <div class="flex flex-col items-center text-center space-y-4">
                        <div class="relative">
                            <div class="h-20 w-20 rounded-full overflow-hidden ring-4 ring-muted bg-muted flex items-center justify-center">
                                <img v-if="parent.photo" :src="parent.photo" class="h-full w-full object-cover" />
                                <div v-else class="text-xl font-bold text-blue-600/40 uppercase">
                                    {{ parent.first_name[0] }}{{ parent.last_name[0] }}
                                </div>
                            </div>
                        </div>
                        
                        <div class="space-y-1">
                            <h3 class="text-lg font-bold text-foreground group-hover:text-blue-600 transition-colors line-clamp-1">
                                {{ parent.first_name }} {{ parent.last_name }}
                            </h3>
                            <div class="flex items-center justify-center gap-1.5 text-xs text-muted-foreground font-medium">
                                <Phone class="h-3 w-3" />
                                {{ parent.phone }}
                            </div>
                        </div>
                        
                        <!-- Children Pills -->
                        <div class="flex flex-wrap items-center justify-center gap-2 pt-2">
                            <Badge 
                                v-for="student in parent.students" 
                                :key="student.id"
                                variant="outline" 
                                class="rounded-lg bg-blue-50/50 border-blue-100 text-blue-700 text-[9px] font-bold px-2 py-0.5 truncate max-w-[120px]"
                            >
                                {{ student.first_name }}
                            </Badge>
                            <span v-if="!parent.students?.length" class="text-[10px] italic text-muted-foreground">No students linked</span>
                        </div>

                        <div class="w-full pt-4 border-t border-border mt-auto">
                            <Button as-child variant="link" class="w-full h-8 text-xs font-bold text-blue-600 hover:text-blue-700 p-0">
                                <Link :href="`/parents/${parent.id}`">
                                    Full Directory Entry
                                </Link>
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- List View -->
            <div v-else-if="viewMode === 'list' && parents.total > 0" class="bg-card rounded-2xl border border-border shadow-sm overflow-hidden animate-in fade-in duration-500">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-muted/30 border-b">
                            <th class="p-6 text-[10px] font-bold uppercase tracking-widest text-muted-foreground">Parent Identity</th>
                            <th class="p-6 text-[10px] font-bold uppercase tracking-widest text-muted-foreground">Children / Dependents</th>
                            <th class="p-6 text-[10px] font-bold uppercase tracking-widest text-muted-foreground">Primary Contact</th>
                            <th class="p-6 text-[10px] font-bold uppercase tracking-widest text-muted-foreground text-center">Account</th>
                            <th class="p-6 text-[10px] font-bold uppercase tracking-widest text-muted-foreground text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr v-for="parent in parents.data" :key="parent.id" class="group hover:bg-muted/10 transition-all">
                            <td class="p-6">
                                <div class="flex items-center gap-4">
                                    <div class="h-10 w-10 rounded-xl overflow-hidden bg-muted flex items-center justify-center">
                                        <img v-if="parent.photo" :src="parent.photo" class="h-full w-full object-cover" />
                                        <div v-else class="text-sm font-bold text-blue-600/40 uppercase">
                                            {{ parent.first_name[0] }}{{ parent.last_name[0] }}
                                        </div>
                                    </div>
                                    <div>
                                        <p class="font-bold text-foreground leading-tight">{{ parent.first_name }} {{ parent.last_name }}</p>
                                        <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mt-1">ID: {{ parent.id_number || 'N/A' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-6">
                                <div class="flex flex-wrap gap-2">
                                    <Badge 
                                        v-for="student in parent.students" 
                                        :key="student.id"
                                        class="bg-blue-50 text-blue-700 border-blue-100 font-bold px-2 py-0.5 text-[9px] uppercase"
                                    >
                                        {{ student.first_name }} {{ student.last_name }}
                                    </Badge>
                                </div>
                            </td>
                            <td class="p-6">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2 text-sm font-medium text-foreground">
                                        <Phone class="h-3.5 w-3.5 text-blue-500" />
                                        {{ parent.phone }}
                                    </div>
                                    <div class="flex items-center gap-2 text-[10px] text-muted-foreground truncate max-w-[200px]">
                                        <Mail class="h-3 w-3" />
                                        {{ parent.email }}
                                    </div>
                                </div>
                            </td>
                            <td class="p-6 text-center">
                                <Badge :class="parent.is_active ? 'bg-emerald-500 text-white' : 'bg-slate-400 text-white'" class="rounded-lg font-bold px-3 py-1 text-[9px] uppercase border-none">
                                    {{ parent.is_active ? 'Active' : 'Locked' }}
                                </Badge>
                            </td>
                            <td class="p-6 text-right">
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="ghost" size="icon" class="h-8 w-8 rounded-lg text-muted-foreground hover:text-foreground">
                                            <MoreHorizontal class="h-4 w-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end" class="rounded-xl border-border shadow-lg">
                                        <DropdownMenuItem as-child>
                                            <Link :href="`/parents/${parent.id}`" class="flex items-center gap-2">
                                                <Eye class="h-4 w-4" /> View Profile
                                            </Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem as-child>
                                            <Link :href="`/parents/${parent.id}/edit`" class="flex items-center gap-2 text-blue-600">
                                                <Edit class="h-4 w-4" /> Edit Record
                                            </Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem @click="deleteParent(parent.id)" class="flex items-center gap-2 text-red-600">
                                            <Trash2 class="h-4 w-4" /> Delete Account
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div v-if="parents.total === 0" class="flex flex-col items-center justify-center py-32 space-y-4 bg-muted/10 rounded-3xl border border-dashed border-border/60">
                <div class="h-16 w-16 rounded-2xl bg-muted flex items-center justify-center">
                    <Users class="h-8 w-8 text-muted-foreground/30" />
                </div>
                <div class="text-center space-y-2">
                    <h3 class="text-xl font-bold text-foreground">No Parents Found</h3>
                    <p class="text-sm text-muted-foreground max-w-xs mx-auto italic">Try adjusting your search or add a new parent to the registry.</p>
                </div>
                <Button variant="outline" class="rounded-xl mt-4" @click="searchQuery = ''">
                    Reset Search
                </Button>
            </div>

            <!-- Pagination -->
            <div v-if="parents.last_page > 1" class="flex items-center justify-between border-t border-border pt-8">
                <p class="text-xs font-semibold text-muted-foreground italic">
                    Showing {{ parents.from }} to {{ parents.to }} of {{ parents.total }} results
                </p>
                <div class="flex items-center gap-2">
                    <Button variant="outline" class="h-9 px-4 rounded-xl text-xs font-bold" :disabled="parents.current_page <= 1" @click="router.get(parents.prev_page_url || '')">
                        Previous
                    </Button>
                    <div class="h-9 min-w-[36px] flex items-center justify-center rounded-xl bg-blue-600 text-white font-bold text-xs shadow-sm shadow-blue-200">
                        {{ parents.current_page }}
                    </div>
                    <Button variant="outline" class="h-9 px-4 rounded-xl text-xs font-bold" :disabled="parents.current_page >= parents.last_page" @click="router.get(parents.next_page_url || '')">
                        Next
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
