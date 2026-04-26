<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Users,
    Plus,
    Search,
    Phone,
    Mail,
    MoreHorizontal,
    Eye,
    Edit,
    Trash2,
    Home,
    ChevronRight,
    Filter,
    CheckCircle2,
} from 'lucide-vue-next';
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
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Guardians', href: '/guardians' },
];

const guardians = ref([
    {
        id: 1,
        name: 'Jane Wanjiku Kamau',
        phone: '+254712345678',
        email: 'jane.kamau@email.com',
        relationship: 'Mother',
        students: 2,
        status: 'active',
    },
    {
        id: 2,
        name: 'Peter Ochieng Otieno',
        phone: '+254723456789',
        email: 'peter.o@email.com',
        relationship: 'Father',
        students: 1,
        status: 'active',
    },
    {
        id: 3,
        name: 'Mary Muthoni Njeri',
        phone: '+254734567890',
        email: 'mary.njeri@email.com',
        relationship: 'Guardian',
        students: 3,
        status: 'active',
    },
    {
        id: 4,
        name: 'David Kiprop Koech',
        phone: '+254745678901',
        email: 'david.kiprop@email.com',
        relationship: 'Father',
        students: 2,
        status: 'active',
    },
    {
        id: 5,
        name: 'Grace Achieng Odhiambo',
        phone: '+254756789012',
        email: null,
        relationship: 'Mother',
        students: 1,
        status: 'inactive',
    },
]);
</script>

<template>
    <Head title="Guardians" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto max-w-[1600px] animate-in space-y-6 p-4 pb-10 duration-700 fade-in slide-in-from-bottom-4 sm:space-y-8 sm:p-6 sm:pb-20 md:p-8"
        >
            <!-- Page Header -->
            <div
                class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between px-1"
            >
                <div class="flex flex-col gap-1">
                    <div class="mb-1 flex items-center gap-2 text-xs text-muted-foreground sm:text-xs">
                        <Home class="h-3 w-3 sm:h-3.5 sm:w-3.5" />
                        <ChevronRight class="h-2.5 w-2.5 sm:h-3 sm:w-3" />
                        <span class="font-medium tracking-tight text-foreground uppercase">People</span>
                        <ChevronRight class="h-2.5 w-2.5 sm:h-3 sm:w-3" />
                        <span class="font-medium tracking-tight text-foreground uppercase">Guardians</span>
                    </div>
                    <h1 class="text-2xl leading-tight font-bold tracking-tight text-foreground sm:text-3xl">
                        Family Registry
                    </h1>
                    <p class="text-sm text-muted-foreground sm:text-sm">
                        Manage parents and legal guardians associated with your students.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Link
                        href="/guardians/create"
                        class="inline-flex h-10 items-center justify-center rounded-xl bg-primary px-6 text-xs font-bold tracking-tight text-white uppercase shadow-lg shadow-primary/30 transition-all hover:scale-[1.02] hover:bg-primary/90 active:scale-95"
                    >
                        <Plus class="mr-2 h-4 w-4" />
                        Add Guardian
                    </Link>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 gap-4 px-1 sm:grid-cols-3 sm:gap-6">
                <div class="group rounded-2xl border border-border bg-card p-5 shadow-sm transition-all hover:border-primary/20 dark:border-white/5">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary group-hover:bg-primary group-hover:text-white transition-all shadow-sm">
                            <Users class="h-5 w-5" />
                        </div>
                        <span class="text-[10px] font-bold tracking-tight text-muted-foreground/30 uppercase">Total Count</span>
                    </div>
                    <h3 class="text-2xl font-bold text-foreground tabular-nums">892</h3>
                    <p class="mt-1 text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase">Registered Guardians</p>
                </div>

                <div class="group rounded-2xl border border-border bg-card p-5 shadow-sm transition-all hover:border-emerald-200 dark:border-white/5">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-all shadow-sm">
                            <CheckCircle2 class="h-5 w-5" />
                        </div>
                        <span class="text-[10px] font-bold tracking-tight text-muted-foreground/30 uppercase">Verified</span>
                    </div>
                    <h3 class="text-2xl font-bold text-foreground tabular-nums">845</h3>
                    <p class="mt-1 text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase">Active Accounts</p>
                </div>

                <div class="group rounded-2xl border border-border bg-card p-5 shadow-sm transition-all hover:border-primary/20 dark:border-white/5">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary group-hover:bg-primary group-hover:text-white transition-all shadow-sm">
                            <Mail class="h-5 w-5" />
                        </div>
                        <span class="text-[10px] font-bold tracking-tight text-muted-foreground/30 uppercase">Connect</span>
                    </div>
                    <h3 class="text-2xl font-bold text-foreground tabular-nums">756</h3>
                    <p class="mt-1 text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase">Email Linked</p>
                </div>
            </div>

            <!-- Toolbar & Filter -->
            <div class="flex flex-col items-center justify-between gap-4 rounded-2xl border border-border bg-card p-4 shadow-sm md:flex-row">
                <div class="group relative w-full md:max-w-md">
                    <Search class="absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-muted-foreground/40 transition-colors group-focus-within:text-primary" />
                    <Input
                        placeholder="Search by name, phone or email..."
                        class="h-11 rounded-xl border-border bg-muted/20 pl-10 text-xs font-bold tracking-tight uppercase transition-all focus:bg-background"
                    />
                </div>
                
                <div class="flex items-center gap-3">
                    <Button
                        variant="outline"
                        class="h-11 rounded-xl border-border bg-background px-4 text-xs font-bold tracking-tight uppercase hover:bg-muted"
                    >
                        <Filter class="mr-2 h-3.5 w-3.5 opacity-70" />
                        Advanced Filter
                    </Button>
                </div>
            </div>

            <!-- Table Card -->
            <div
                class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm dark:border-white/5"
            >
                <div class="scrollbar-hide overflow-x-auto">
                    <table class="w-full min-w-[800px] text-left">
                        <thead>
                            <tr class="border-b border-border/50 bg-muted/5 text-muted-foreground">
                                <th class="px-6 py-4 text-xs font-bold tracking-tight uppercase">Parent / Guardian</th>
                                <th class="px-6 py-4 text-xs font-bold tracking-tight uppercase">Contact Details</th>
                                <th class="px-6 py-4 text-center text-xs font-bold tracking-tight uppercase">Relationship</th>
                                <th class="px-6 py-4 text-center text-xs font-bold tracking-tight uppercase">Students</th>
                                <th class="px-6 py-4 text-center text-xs font-bold tracking-tight uppercase">Status</th>
                                <th class="px-6 py-4 text-right text-xs font-bold tracking-tight uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/30">
                            <tr
                                v-for="guardian in guardians"
                                :key="guardian.id"
                                class="group transition-colors hover:bg-muted/30"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-primary/10 text-[10px] font-bold text-primary uppercase transition-all group-hover:scale-110">
                                            {{ guardian.name.charAt(0) }}
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-foreground transition-colors group-hover:text-primary">
                                                {{ guardian.name }}
                                            </span>
                                            <span class="text-[10px] font-bold tracking-tight text-muted-foreground/40 uppercase">
                                                ID: #GRD-{{ guardian.id.toString().padStart(4, '0') }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col space-y-1">
                                        <div class="flex items-center gap-2 text-xs font-bold text-foreground">
                                            <Phone class="h-3 w-3 text-primary opacity-60" />
                                            {{ guardian.phone }}
                                        </div>
                                        <div v-if="guardian.email" class="flex items-center gap-2 text-[10px] font-bold text-muted-foreground tracking-tight uppercase">
                                            <Mail class="h-3 w-3 opacity-40" />
                                            {{ guardian.email }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <Badge variant="outline" class="h-6 rounded-lg border-none bg-primary/5 px-2 text-[10px] font-bold tracking-tight text-primary uppercase">
                                        {{ guardian.relationship }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-muted text-[10px] font-bold text-foreground">
                                        {{ guardian.students }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <Badge
                                        variant="secondary"
                                        class="border-none px-3 py-1 text-[10px] font-bold tracking-tight uppercase"
                                        :class="guardian.status === 'active' ? 'bg-emerald-500/10 text-emerald-600' : 'bg-slate-500/10 text-slate-500'"
                                    >
                                        {{ guardian.status }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-1 px-1">
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-9 w-9 rounded-xl text-muted-foreground transition-all hover:bg-primary/10 hover:text-primary"
                                        >
                                            <Eye class="h-4 w-4" />
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-9 w-9 rounded-xl text-muted-foreground transition-all hover:bg-primary/10 hover:text-primary"
                                        >
                                            <Edit class="h-4 w-4" />
                                        </Button>
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    class="h-9 w-9 rounded-xl text-muted-foreground hover:bg-muted"
                                                >
                                                    <MoreHorizontal class="h-4 w-4" />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent
                                                align="end"
                                                class="w-56 rounded-xl border-border p-2 shadow-xl"
                                            >
                                                <DropdownMenuItem class="rounded-lg py-2.5 text-xs font-bold">
                                                    <Users class="mr-3 h-4 w-4 opacity-60" />
                                                    View Linked Students
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator class="my-1" />
                                                <DropdownMenuItem class="rounded-lg py-2.5 text-xs font-bold text-rose-500">
                                                    <Trash2 class="mr-3 h-4 w-4 opacity-60" />
                                                    Remove Record
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
