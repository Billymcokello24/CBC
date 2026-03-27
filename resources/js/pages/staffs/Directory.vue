<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { 
    Users, ShieldCheck, GraduationCap, Building2, 
    Presentation, Library, Stethoscope, Banknote, 
    FileText, Shield, Truck, ShieldAlert,
    ChevronRight, ArrowRight, BadgeCheck, Plus, Heart
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    roles: Array<{
        id: number;
        name: string;
        display_name: string;
        count: number;
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'User Directory', href: '/staffs/directory' },
];

const getRoleIcon = (roleName: string) => {
    switch (roleName.toLowerCase()) {
        case 'super_admin': return ShieldAlert;
        case 'school_admin': return ShieldCheck;
        case 'principal':
        case 'deputy_principal': return GraduationCap;
        case 'teacher': return Users;
        case 'hod': return Building2;
        case 'class_teacher': return Presentation;
        case 'librarian': return Library;
        case 'nurse': return Stethoscope;
        case 'finance_officer': return Banknote;
        case 'clerk': return FileText;
        case 'security': return Shield;
        case 'driver': return Truck;
        case 'parent': return Heart;
        default: return Users;
    }
};

const getIconBg = (roleName: string) => {
    switch (roleName.toLowerCase()) {
        case 'super_admin': return 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400';
        case 'school_admin': return 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400';
        case 'principal':
        case 'deputy_principal': return 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400';
        case 'teacher': return 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400';
        case 'hod': return 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400';
        case 'parent': return 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400';
        default: return 'bg-slate-50 text-slate-600 dark:bg-slate-500/10 dark:text-slate-400';
    }
};

const navigateToRole = (role: any) => {
    if (role.href) {
        router.get(role.href);
    } else {
        router.get(`/staffs/directory/${role.name}`);
    }
};
</script>

<template>
    <Head title="User Directory" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700 max-w-[1600px] mx-auto pb-20 p-6 md:p-8">
            
            <!-- Standard Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <h1 class="text-3xl font-bold tracking-tight text-foreground">User Directory</h1>
                    <p class="text-[15px] text-muted-foreground">
                        Browse institutional users categorized by their professional roles.
                    </p>
                </div>
                
                <div class="flex items-center gap-3">
                    <Link
                        href="/staffs/create"
                        class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 h-10 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition-all font-inter"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        Add New User
                    </Link>
                </div>
            </div>

            <!-- Stats Overview - Subtle Cards -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-border bg-card p-6 shadow-sm flex items-center justify-between group hover:border-blue-500/30 transition-all duration-300">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground mb-1">Total Network</p>
                        <h3 class="text-2xl font-bold text-foreground">{{ roles.reduce((acc, r) => acc + r.count, 0) }}</h3>
                    </div>
                    <div class="h-12 w-12 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 group-hover:scale-110 transition-transform">
                        <Users class="h-6 w-6" />
                    </div>
                </div>
            </div>

            <!-- Roles Grid - Integrated Style -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <div 
                    v-for="role in roles" 
                    :key="role.id"
                    @click="navigateToRole(role)"
                    class="group relative flex flex-col p-6 rounded-2xl border border-border bg-card shadow-sm hover:shadow-md hover:border-blue-500/50 transition-all duration-300 cursor-pointer overflow-hidden"
                >
                    <!-- Corner Accent -->
                    <div class="absolute -right-4 -top-4 h-16 w-16 bg-blue-500/5 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
                    
                    <div class="flex flex-col gap-4">
                        <div 
                            class="h-12 w-12 rounded-xl flex items-center justify-center shadow-sm"
                            :class="getIconBg(role.name)"
                        >
                            <component :is="getRoleIcon(role.name)" class="h-6 w-6" />
                        </div>
                        
                        <div class="space-y-1">
                            <h3 class="text-lg font-bold text-foreground group-hover:text-blue-600 transition-colors">
                                {{ role.display_name }}
                            </h3>
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-muted-foreground font-medium">
                                    {{ role.count }} {{ role.count === 1 ? 'User' : 'Users' }}
                                </span >
                            </div>
                        </div>
                        
                        <div class="pt-2 flex items-center justify-between text-xs font-semibold text-blue-600 opacity-0 group-hover:opacity-100 transition-all translate-x-[-10px] group-hover:translate-x-0">
                            <span>Open Directory</span>
                            <ChevronRight class="h-3.5 w-3.5" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informational Banner - Simplified -->
            <div class="p-8 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-border flex flex-col md:flex-row items-center gap-6">
                <div class="h-12 w-12 rounded-xl bg-white dark:bg-slate-800 flex items-center justify-center border border-border shadow-sm shrink-0">
                    <ShieldCheck class="h-6 w-6 text-blue-600" />
                </div>
                <div class="space-y-1">
                    <h3 class="font-bold text-foreground">Role-Based Access Control</h3>
                    <p class="text-muted-foreground text-sm leading-relaxed max-w-2xl">
                        Permissions are assigned at the role level. Any modifications to professional categories or user status are reflected across the entire institution.
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.font-inter { font-family: 'Inter', sans-serif; }
</style>
