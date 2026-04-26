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
    Download,
    Home,
    ChevronRight,
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
    DropdownMenuTrigger,
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
    active: props.schools.data.filter((s) => s.status === 'active').length,
    total_students: props.schools.data.reduce(
        (acc, s) => acc + s.students_count,
        0,
    ),
};
</script>

<template>
    <Head title="Tenant Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-[1600px] animate-in space-y-6 p-4 pb-10 duration-700 fade-in slide-in-from-bottom-4 sm:space-y-8 sm:p-6 sm:pb-20 md:p-8">
            <!-- Header Section -->
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between px-1">
                <div class="flex flex-col gap-1">
                    <div class="mb-1 flex items-center gap-2 text-xs text-muted-foreground sm:text-xs">
                        <Home class="h-3 w-3 sm:h-3.5 sm:w-3.5" />
                        <ChevronRight class="h-2.5 w-2.5 sm:h-3 sm:w-3" />
                        <span class="font-medium tracking-tight text-foreground uppercase">Global Dashboard</span>
                        <ChevronRight class="h-2.5 w-2.5 sm:h-3 sm:w-3" />
                        <span class="font-medium tracking-tight text-foreground uppercase">Tenant Registry</span>
                    </div>
                    <h1 class="text-2xl leading-tight font-bold tracking-tight text-foreground sm:text-3xl">
                        Schools Registry
                    </h1>
                    <p class="text-sm text-muted-foreground sm:text-sm">
                        Manage and monitor all registered school accounts within the system.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <div
                        class="mr-2 hidden items-center gap-4 rounded-xl border border-border bg-card px-4 py-2 shadow-sm sm:flex dark:border-white/5"
                    >
                        <div class="flex flex-col">
                            <span
                                class="text-xs font-bold tracking-tight text-muted-foreground/40 uppercase"
                                >Total Students</span
                            >
                            <span class="text-xs font-bold text-foreground">{{
                                stats.total_students.toLocaleString()
                            }}</span>
                        </div>
                        <div class="h-6 w-px bg-border/50"></div>
                        <div class="flex flex-col">
                            <span
                                class="text-xs font-bold tracking-tight text-muted-foreground/40 uppercase"
                                >Institutions</span
                            >
                            <span class="text-xs font-bold text-foreground">{{
                                stats.total
                            }}</span>
                        </div>
                    </div>

                    <Link :href="schoolsRoutes.create().url">
                        <Button
                            class="h-11 rounded-xl bg-primary px-6 font-bold text-white shadow-lg shadow-primary/20 transition-all hover:bg-primary/90"
                        >
                            <Plus class="mr-2 h-4 w-4" />
                            <span
                                class="text-sm font-medium tracking-tight uppercase"
                                >Add School</span
                            >
                        </Button>
                    </Link>
                </div>
            </div>

            <!-- Table Card -->
            <div class="flex flex-col overflow-hidden rounded-2xl border border-border bg-card shadow-sm dark:border-white/5">
                <!-- Toolbar -->
                <div class="flex flex-col gap-4 border-b border-border/50 bg-muted/5 p-4 lg:flex-row lg:items-center lg:justify-between sm:px-6">
                    <div class="relative w-full lg:w-[400px]">
                        <Search
                            class="absolute top-1/2 left-4 h-4 w-4 -translate-y-1/2 text-muted-foreground/40"
                        />
                        <Input
                            placeholder="Search schools by name or code..."
                            class="h-10 rounded-xl border-border bg-background pl-10 text-sm font-bold transition-all placeholder:text-muted-foreground/40 focus:ring-primary/20"
                        />
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <Button
                            variant="outline"
                            class="h-10 rounded-xl border-border px-4 text-sm font-bold tracking-wider text-muted-foreground uppercase hover:bg-muted/50"
                        >
                            <Filter class="mr-2 h-3.5 w-3.5 opacity-50" />
                            Filters
                        </Button>
                        <Button
                            variant="outline"
                            class="h-10 rounded-xl border-border px-4 text-sm font-bold tracking-wider text-muted-foreground uppercase hover:bg-muted/50"
                        >
                            <Download class="mr-2 h-3.5 w-3.5 opacity-50" />
                            Export
                        </Button>
                        <div
                            class="mx-2 hidden h-6 w-px bg-border lg:block"
                        ></div>
                        <div
                            class="flex items-center rounded-lg border border-border bg-muted/50 p-1"
                        >
                            <button
                                class="rounded-md bg-background p-1.5 text-foreground shadow-sm"
                            >
                                <List class="h-3.5 w-3.5" />
                            </button>
                            <button
                                class="rounded-md p-1.5 text-muted-foreground/50 hover:text-foreground"
                            >
                                <LayoutGrid class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr class="bg-muted/10">
                                <th class="w-[40%] border-b border-border/50 bg-muted/5 px-5 py-4 sm:px-6 text-xs font-bold tracking-tight text-foreground uppercase">
                                    School Information
                                </th>
                                <th class="border-b border-border/50 bg-muted/5 px-5 py-4 sm:px-6 text-xs font-bold tracking-tight text-foreground uppercase">
                                    Code
                                </th>
                                <th class="border-b border-border/50 bg-muted/5 px-5 py-4 sm:px-6 text-xs font-bold tracking-tight text-foreground uppercase">
                                    Population
                                </th>
                                <th class="border-b border-border/50 bg-muted/5 px-5 py-4 sm:px-6 text-xs font-bold tracking-tight text-foreground uppercase">
                                    Status
                                </th>
                                <th class="border-b border-border/50 bg-muted/5 px-5 py-4 sm:px-6 text-right text-xs font-bold tracking-tight text-foreground uppercase">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/50">
                            <tr
                                v-for="school in schools.data"
                                :key="school.id"
                                class="group transition-all hover:bg-muted/30"
                            >
                                <td class="px-5 py-3.5 sm:px-6 sm:py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 text-xs font-bold text-blue-600 ring-2 ring-white transition-colors group-hover:bg-blue-600 group-hover:text-white">
                                            {{ school.name[0].toUpperCase() }}
                                            <div
                                                class="absolute -top-1 -right-1 h-3 w-3 rounded-full border-2 border-white bg-emerald-500 shadow-sm"
                                                v-if="school.status === 'active'"
                                            ></div>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="mb-1 text-xs leading-none font-bold tracking-tight text-foreground sm:text-sm">
                                                {{ school.name }}
                                            </span>
                                            <span class="text-xs font-semibold tracking-tighter text-muted-foreground uppercase flex items-center gap-1">
                                                <MapPin class="h-3 w-3 opacity-40" />
                                                {{ school.county || 'Unmapped' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 sm:px-6 sm:py-4 font-mono text-xs font-bold tracking-tight text-muted-foreground">
                                    {{ school.code }}
                                </td>
                                <td class="px-5 py-3.5 sm:px-6 sm:py-4">
                                    <div class="flex items-center gap-6">
                                        <div class="flex flex-col">
                                            <div
                                                class="flex items-center gap-1 text-sm font-bold text-foreground"
                                            >
                                                <Users
                                                    class="h-3 w-3 text-primary opacity-30"
                                                />
                                                {{ school.users_count }}
                                            </div>
                                            <span
                                                class="text-xs font-bold tracking-tight text-muted-foreground/40 uppercase"
                                                >Staff</span
                                            >
                                        </div>
                                        <div class="flex flex-col">
                                            <div
                                                class="flex items-center gap-1 text-sm font-bold text-foreground"
                                            >
                                                <GraduationCap
                                                    class="h-3 w-3 text-primary opacity-30"
                                                />
                                                {{ school.students_count }}
                                            </div>
                                            <span
                                                class="text-xs font-bold tracking-tight text-muted-foreground/40 uppercase"
                                                >Learners</span
                                            >
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 sm:px-6 sm:py-4">
                                    <Badge
                                        :variant="
                                            school.status === 'active'
                                                ? 'default'
                                                : 'secondary'
                                        "
                                        class="border-0 bg-emerald-500/10 px-2.5 text-xs font-bold tracking-tight text-emerald-600 uppercase shadow-none"
                                        v-if="school.status === 'active'"
                                    >
                                        <div
                                            class="mr-1.5 h-1 w-1 animate-pulse rounded-full bg-emerald-500"
                                        ></div>
                                        Active
                                    </Badge>
                                    <Badge
                                        variant="outline"
                                        class="border-muted px-2.5 text-xs font-bold tracking-tight text-muted-foreground/40 uppercase shadow-none"
                                        v-else
                                    >
                                        Inactive
                                    </Badge>
                                </td>
                                <td class="px-5 py-3.5 text-right sm:px-6 sm:py-4">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button
                                                variant="ghost"
                                                class="h-8 w-8 rounded-lg p-0 transition-colors hover:bg-muted"
                                            >
                                                <MoreHorizontal
                                                    class="h-4 w-4 text-muted-foreground/40"
                                                />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent
                                            align="end"
                                            class="w-56 rounded-xl border border-border bg-card/95 p-1 shadow-lg backdrop-blur-xl"
                                        >
                                            <DropdownMenuLabel
                                                class="px-3 py-2 text-xs font-bold tracking-tight text-muted-foreground/40 uppercase"
                                                >School
                                                Management</DropdownMenuLabel
                                            >
                                            <DropdownMenuItem as-child>
                                                <Link
                                                    :href="
                                                        impersonate(school).url
                                                    "
                                                    method="post"
                                                    as="button"
                                                    class="group flex w-full cursor-pointer items-center rounded-lg px-3 py-2.5 text-left text-sm font-bold tracking-wider uppercase transition-all hover:bg-primary/10 hover:text-primary"
                                                >
                                                    <ExternalLink
                                                        class="mr-2 h-3.5 w-3.5 text-muted-foreground/30 group-hover:text-primary"
                                                    />
                                                    Impersonate
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator
                                                class="bg-border/50"
                                            />
                                            <DropdownMenuItem as-child>
                                                <Link
                                                    :href="
                                                        schoolsRoutes.edit(
                                                            school,
                                                        ).url
                                                    "
                                                    class="group flex cursor-pointer items-center rounded-lg px-3 py-2.5 text-sm font-bold tracking-wider uppercase transition-all hover:bg-muted"
                                                >
                                                    <Edit
                                                        class="mr-2 h-3.5 w-3.5 text-muted-foreground/30 group-hover:text-foreground"
                                                    />
                                                    Manage
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem
                                                class="group flex cursor-pointer items-center rounded-lg px-3 py-2.5 text-sm font-bold tracking-wider text-rose-600 uppercase transition-all hover:bg-rose-500/10"
                                            >
                                                <Trash2
                                                    class="mr-2 h-3.5 w-3.5 text-rose-500/30 group-hover:text-rose-500"
                                                />
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
                <div class="flex flex-col gap-4 border-t border-border/50 bg-muted/5 p-4 sm:flex-row sm:items-center sm:justify-between px-5 sm:px-6">
                    <p class="text-xs font-bold tracking-tight text-muted-foreground uppercase">
                        Showing 1 - {{ schools.data.length }} of
                        {{ stats.total }} Schools
                    </p>
                    <div class="flex items-center gap-2">
                        <Button
                            disabled
                            variant="outline"
                            class="h-9 rounded-lg border-border px-4 text-xs font-bold tracking-tight text-muted-foreground/40 uppercase"
                            >Prev</Button
                        >
                        <Button
                            variant="outline"
                            class="h-9 rounded-lg border-border bg-background px-4 text-xs font-bold tracking-tight text-foreground uppercase shadow-sm hover:bg-muted/50"
                            >Next</Button
                        >
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
