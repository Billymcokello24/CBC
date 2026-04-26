<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    School,
    CheckCircle2,
    XCircle,
    Clock,
    ArrowRight,
    ShieldCheck,
    Zap,
    ExternalLink,
    Search,
    Filter,
    Home,
    ChevronRight,
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import type { BreadcrumbItem } from '@/types';
import { format } from 'date-fns';

interface School {
    id: number;
    name: string;
    code: string;
    status: 'active' | 'inactive' | 'pending';
    created_at: string;
}

const props = defineProps<{
    schools: School[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Global Dashboard', href: '/super-admin/dashboard' },
    { title: 'Tenant Registry', href: '/super-admin/schools' },
    { title: 'Activation Status', href: '/super-admin/schools/status' },
];
</script>

<template>
    <Head title="Activation Status Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-[1600px] animate-in space-y-6 p-4 pb-10 duration-700 fade-in slide-in-from-bottom-4 sm:space-y-8 sm:p-6 sm:pb-20 md:p-8">
            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between px-1">
                <div class="flex flex-col gap-1">
                    <div class="mb-1 flex items-center gap-2 text-xs text-muted-foreground sm:text-xs">
                        <Home class="h-3 w-3 sm:h-3.5 sm:w-3.5" />
                        <ChevronRight class="h-2.5 w-2.5 sm:h-3 sm:w-3" />
                        <span class="font-medium tracking-tight text-foreground uppercase">Tenant Registry</span>
                        <ChevronRight class="h-2.5 w-2.5 sm:h-3 sm:w-3" />
                        <span class="font-medium tracking-tight text-foreground uppercase">Activation Status</span>
                    </div>
                    <h1 class="text-2xl leading-tight font-bold tracking-tight text-foreground sm:text-3xl">
                        School Status
                    </h1>
                    <p class="text-sm text-muted-foreground sm:text-sm">
                        Monitor account activity and activation states.
                    </p>
                </div>
            </div>

            <!-- Toolbar -->
            <div
                class="flex flex-col items-center gap-4 rounded-2xl border border-border bg-card p-4 shadow-sm md:flex-row dark:border-white/5"
            >
                <div class="group relative w-full flex-1">
                    <Search
                        class="absolute top-1/2 left-4 h-4 w-4 -translate-y-1/2 text-muted-foreground/40 transition-colors group-focus-within:text-primary"
                    />
                    <Input
                        placeholder="Search school name or code..."
                        class="h-11 rounded-xl border-border bg-muted/20 pl-10 font-bold focus:ring-primary/20"
                    />
                </div>
                <Button
                    variant="outline"
                    class="h-11 w-full rounded-xl border-border px-6 text-sm font-bold tracking-tight text-muted-foreground uppercase hover:bg-muted/50 md:w-auto"
                >
                    <Filter class="mr-2 h-4 w-4 opacity-50" />
                    Filter Status
                </Button>
            </div>

            <!-- Shard Grid -->
            <div
                class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
            >
                <div
                    v-for="school in schools"
                    :key="school.id"
                    class="group relative overflow-hidden rounded-2xl border border-border bg-card p-6 transition-all hover:-translate-y-1 hover:shadow-lg dark:border-white/5"
                >
                    <div class="relative space-y-6">
                        <div class="flex items-center justify-between">
                            <div
                                :class="[
                                    'flex h-10 w-10 items-center justify-center rounded-xl transition-colors',
                                    school.status === 'active'
                                        ? 'bg-emerald-500/10 text-emerald-600'
                                        : 'bg-muted text-muted-foreground/40',
                                ]"
                            >
                                <School class="h-5 w-5" />
                            </div>
                            <Badge
                                :variant="
                                    school.status === 'active'
                                        ? 'default'
                                        : 'secondary'
                                "
                                class="border-0 bg-emerald-500/10 px-2 text-xs font-bold tracking-tight text-emerald-600 uppercase shadow-none"
                                v-if="school.status === 'active'"
                            >
                                <div
                                    class="mr-1 h-1 w-1 animate-pulse rounded-full bg-emerald-500"
                                ></div>
                                Active
                            </Badge>
                            <Badge
                                variant="outline"
                                class="border-muted px-2 text-xs font-bold tracking-tight text-muted-foreground/40 uppercase shadow-none"
                                v-else
                            >
                                Inactive
                            </Badge>
                        </div>

                        <div>
                            <h3
                                class="line-clamp-1 text-lg leading-tight font-bold tracking-tight text-foreground transition-colors group-hover:text-primary"
                            >
                                {{ school.name }}
                            </h3>
                            <p
                                class="mt-1 text-xs font-bold tracking-tight text-muted-foreground/40 uppercase"
                            >
                                Code: {{ school.code }}
                            </p>
                        </div>

                        <div class="space-y-3 border-t border-border/50 pt-4">
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-xs font-bold tracking-tight text-muted-foreground/30 uppercase"
                                    >Added On</span
                                >
                                <span
                                    class="text-sm font-bold text-muted-foreground"
                                    >{{
                                        format(
                                            new Date(school.created_at),
                                            'MMM dd, yyyy',
                                        )
                                    }}</span
                                >
                            </div>
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-xs font-bold tracking-tight text-muted-foreground/30 uppercase"
                                    >Connectivity</span
                                >
                                <Badge
                                    variant="outline"
                                    class="border-emerald-500/20 bg-emerald-500/5 px-1.5 py-0 text-xs font-bold tracking-tighter text-emerald-600 uppercase"
                                    >Healthy</Badge
                                >
                            </div>
                        </div>

                        <div class="flex gap-2 pt-2">
                            <Button
                                variant="ghost"
                                class="h-10 flex-1 rounded-lg text-xs font-bold tracking-tight text-muted-foreground/40 uppercase hover:bg-muted/50"
                                >History</Button
                            >
                            <Link
                                :href="`/super-admin/schools/${school.id}`"
                                class="flex-1"
                            >
                                <Button
                                    variant="outline"
                                    class="group/btn h-10 w-full rounded-lg border-border text-xs font-bold tracking-tight text-foreground uppercase shadow-none hover:bg-primary/5 hover:text-primary"
                                >
                                    Details
                                    <ArrowRight
                                        class="ml-1.5 h-3.5 w-3.5 transition-transform group-hover/btn:translate-x-1"
                                    />
                                </Button>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Overlay -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div
                    class="group relative overflow-hidden rounded-2xl border border-border bg-card p-8 shadow-sm dark:border-white/5"
                >
                    <div class="relative space-y-4">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary"
                        >
                            <ShieldCheck class="h-5 w-5" />
                        </div>
                        <div>
                            <h3
                                class="text-xl font-bold tracking-tight text-foreground"
                            >
                                System Health
                            </h3>
                            <p
                                class="mt-1 text-xs font-bold tracking-tight text-muted-foreground/60 uppercase"
                            >
                                99.98% Across all schools
                            </p>
                        </div>
                        <div class="flex items-center gap-2 pt-2">
                            <div
                                class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-500"
                            ></div>
                            <span
                                class="text-xs font-bold tracking-tight text-emerald-600 uppercase"
                                >All services nominal</span
                            >
                        </div>
                    </div>
                </div>

                <div
                    class="group relative overflow-hidden rounded-2xl bg-primary p-8 text-primary-foreground shadow-lg shadow-primary/20"
                >
                    <div class="relative space-y-4">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 text-white"
                        >
                            <Zap class="h-5 w-5" />
                        </div>
                        <div>
                            <h3 class="text-xl font-bold tracking-tight">
                                Active Schools
                            </h3>
                            <p
                                class="mt-1 text-xs font-bold tracking-tight text-white/60 uppercase"
                            >
                                Real-time engagement metrics
                            </p>
                        </div>
                        <Button
                            variant="ghost"
                            class="mt-2 h-10 w-full rounded-lg border-0 bg-white/10 text-xs font-bold tracking-tight text-white uppercase hover:bg-white/20"
                        >
                            View Analytics <ExternalLink class="ml-2 h-3 w-3" />
                        </Button>
                    </div>
                </div>

                <div
                    class="space-y-6 rounded-2xl border border-border bg-card p-8 shadow-sm dark:border-white/5"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-muted text-foreground"
                        >
                            <Clock class="h-5 w-5 opacity-40" />
                        </div>
                        <h3
                            class="text-sm font-bold tracking-tight text-foreground uppercase"
                        >
                            Platform Uptime
                        </h3>
                    </div>
                    <div class="space-y-3">
                        <div
                            class="h-1.5 w-full overflow-hidden rounded-full bg-muted"
                        >
                            <div
                                class="h-full w-[99.9%] rounded-full bg-emerald-500"
                            ></div>
                        </div>
                        <div
                            class="flex justify-between text-xs font-bold tracking-tight text-muted-foreground/40 uppercase"
                        >
                            <span>Past 30 Days</span>
                            <span class="text-emerald-600"
                                >99.99% Operational</span
                            >
                        </div>
                    </div>
                    <p class="pt-2 text-xs font-bold text-muted-foreground/40">
                        Heartbeat monitored by Global Watchtower
                    </p>
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
