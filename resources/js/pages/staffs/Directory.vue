<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Users,
    ShieldCheck,
    GraduationCap,
    Building2,
    Presentation,
    Library,
    Stethoscope,
    Banknote,
    FileText,
    Shield,
    Truck,
    ShieldAlert,
    ChevronRight,
    ArrowRight,
    BadgeCheck,
    Plus,
    Heart,
    FileText as FileTextIcon,
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
    { title: 'Staff Groups', href: '/staffs/directory' },
];

const getRoleIcon = (roleName: string) => {
    switch (roleName.toLowerCase()) {
        case 'super_admin':
            return ShieldAlert;
        case 'school_admin':
            return ShieldCheck;
        case 'principal':
        case 'deputy_principal':
            return GraduationCap;
        case 'teacher':
            return Users;
        case 'hod':
            return Building2;
        case 'class_teacher':
            return Presentation;
        case 'librarian':
            return Library;
        case 'nurse':
            return Stethoscope;
        case 'finance_officer':
            return Banknote;
        case 'clerk':
            return FileText;
        case 'security':
            return Shield;
        case 'driver':
            return Truck;
        case 'parent':
            return Heart;
        default:
            return Users;
    }
};

const getIconBg = (roleName: string) => {
    switch (roleName.toLowerCase()) {
        case 'super_admin':
            return 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400';
        case 'school_admin':
            return 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400';
        case 'principal':
        case 'deputy_principal':
            return 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400';
        case 'teacher':
            return 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400';
        case 'hod':
            return 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400';
        case 'parent':
            return 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400';
        default:
            return 'bg-slate-50 text-slate-600 dark:bg-slate-500/10 dark:text-slate-400';
    }
};

const navigateToRole = (role: any) => {
    if (role.href) {
        router.get(role.href);
    } else {
        router.get(`/staffs/directory/${role.name}`);
    }
};

const downloadPdf = () => {
    window.location.href = '/staffs/directory/export-pdf';
};
</script>

<template>
    <Head title="User Directory" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto max-w-[1600px] animate-in space-y-6 p-4 pb-10 duration-700 fade-in slide-in-from-bottom-4 sm:space-y-8 sm:p-6 sm:pb-20 md:p-8"
        >
            <!-- Page Header -->
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between px-1"
            >
                <div class="flex flex-col gap-1">
                    <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                        Staff Groups
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        View and manage staff members organized by their roles.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Button
                        variant="outline"
                        class="h-10 rounded-lg border-border px-4 text-xs font-bold uppercase transition-all hover:bg-muted"
                        @click="downloadPdf"
                    >
                        <FileTextIcon class="mr-2 h-4 w-4 text-rose-500" />
                        Download PDF
                    </Button>
                    <Link
                        href="/staffs/create"
                        class="inline-flex h-10 items-center justify-center rounded-lg bg-primary px-6 text-xs font-bold text-white shadow-sm transition-all hover:bg-primary/90"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        Add Staff
                    </Link>
                </div>
            </div>

            <!-- Header Stats -->
            <div class="grid grid-cols-1 gap-4 px-1 sm:grid-cols-2 lg:grid-cols-4">
                <div class="group rounded-xl border border-border bg-card p-5 shadow-sm transition-all">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary shadow-sm">
                            <Users class="h-5 w-5" />
                        </div>
                        <span class="text-[10px] font-bold text-muted-foreground uppercase">Total Staff</span>
                    </div>
                    <h3 class="text-2xl font-bold text-foreground tabular-nums">
                        {{ roles.reduce((acc, r) => acc + r.count, 0).toLocaleString() }}
                    </h3>
                    <p class="mt-1 text-[10px] font-medium text-muted-foreground uppercase opacity-60">Registered staff members</p>
                </div>
            </div>

            <!-- Role Grid -->
            <div
                class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 px-1"
            >
                <div
                    v-for="role in roles"
                    :key="role.id"
                    @click="navigateToRole(role)"
                    class="group relative flex cursor-pointer flex-col overflow-hidden rounded-xl border border-border bg-card p-6 shadow-sm transition-all duration-300 hover:border-primary/20 hover:shadow-lg"
                >
                    <div class="relative z-10 flex flex-col gap-5">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-lg bg-primary/5 text-primary transition-all duration-300 group-hover:bg-primary group-hover:text-white shadow-sm"
                        >
                            <component
                                :is="getRoleIcon(role.name)"
                                class="h-6 w-6"
                            />
                        </div>

                        <div class="space-y-1">
                            <h3
                                class="text-lg font-bold text-foreground transition-colors group-hover:text-primary uppercase"
                            >
                                {{ role.display_name }}
                            </h3>
                            <p class="text-[10px] font-bold text-muted-foreground uppercase opacity-40">
                                {{ role.count }} {{ role.count === 1 ? 'Staff Member' : 'Staff Members' }}
                            </p>
                        </div>

                        <div
                            class="flex items-center gap-2 pt-2 text-[10px] font-bold text-primary uppercase opacity-60 transition-all group-hover:opacity-100"
                        >
                            <span>Open Directory</span>
                            <ArrowRight class="h-3 w-3 transition-transform group-hover:translate-x-1" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Information Banner -->
            <div
                class="relative mt-4 overflow-hidden rounded-xl border border-border bg-muted/30 p-8"
            >
                <div class="relative z-10 flex flex-col items-center gap-8 md:flex-row">
                    <div
                        class="flex h-14 w-14 shrink-0 items-center justify-center rounded-lg bg-primary/10 shadow-sm"
                    >
                        <ShieldCheck class="h-7 w-7 text-primary" />
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-lg font-bold text-foreground uppercase">
                            Staff Access Control
                        </h3>
                        <p
                            class="max-w-3xl text-sm font-medium leading-relaxed text-muted-foreground/80"
                        >
                            Access to various parts of the system is managed based on staff roles. Changes to roles are updated automatically.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.font-inter {
    font-family: 'Inter', sans-serif;
}
</style>
