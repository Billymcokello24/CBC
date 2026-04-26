<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Activity,
    Search,
    Filter,
    Clock,
    User,
    Database,
    Eye,
    Info,
    ArrowRight,
    Terminal,
    History,
    Cpu,
    Box,
    Home,
    ChevronRight,
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import type { BreadcrumbItem } from '@/types';
import { formatDistanceToNow } from 'date-fns';
import debounce from 'lodash/debounce';

interface LogEntry {
    id: number;
    log_name: string;
    description: string;
    subject_type: string;
    subject_id: number;
    causer_type: string;
    causer_id: number;
    properties: any;
    created_at: string;
    causer?: { name: string; email: string };
}

interface Props {
    logs: {
        data: LogEntry[];
        links: any[];
        current_page: number;
        last_page: number;
    };
    filters: {
        search?: string;
        log_name?: string;
    };
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Global Dashboard', href: '/super-admin/dashboard' },
    { title: 'System Audit Trail', href: '/super-admin/activity-logs' },
];

const search = ref(props.filters.search || '');

const handleSearch = debounce(() => {
    router.get(
        route('super-admin.activity-logs.index'),
        {
            search: search.value,
        },
        { preserveState: true, replace: true },
    );
}, 500);
</script>

<template>
    <Head title="System Audit Trail" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-[1600px] animate-in space-y-6 p-4 pb-10 duration-700 fade-in slide-in-from-bottom-4 sm:space-y-8 sm:p-6 sm:pb-20 md:p-8">
            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between px-1">
                <div class="flex flex-col gap-1">
                    <div class="mb-1 flex items-center gap-2 text-xs text-muted-foreground sm:text-xs">
                        <Home class="h-3 w-3 sm:h-3.5 sm:w-3.5" />
                        <ChevronRight class="h-2.5 w-2.5 sm:h-3 sm:w-3" />
                        <span class="font-medium tracking-tight text-foreground uppercase">Global Dashboard</span>
                        <ChevronRight class="h-2.5 w-2.5 sm:h-3 sm:w-3" />
                        <span class="font-medium tracking-tight text-foreground uppercase">System Audit Trail</span>
                    </div>
                    <h1 class="text-2xl leading-tight font-bold tracking-tight text-foreground sm:text-3xl">
                        Activity Logs
                    </h1>
                    <p class="text-sm text-muted-foreground sm:text-sm">
                        Track all system events and user actions.
                    </p>
                </div>
            </div>

            <!-- Toolbar -->
            <div
                class="rounded-2xl border border-border bg-card p-4 shadow-sm dark:border-white/5"
            >
                <div class="flex flex-col items-center gap-4 md:flex-row">
                    <div class="group relative flex-1">
                        <Search
                            class="absolute top-1/2 left-4 h-4 w-4 -translate-y-1/2 text-muted-foreground/40"
                        />
                        <Input
                            v-model="search"
                            @input="handleSearch"
                            placeholder="Search logs by description, category, or user..."
                            class="h-11 rounded-xl border-border bg-muted/20 pl-10 font-bold focus:ring-primary/20"
                        />
                    </div>
                    <Button
                        variant="outline"
                        class="h-11 rounded-xl border-border px-6 text-xs font-bold tracking-tight text-muted-foreground uppercase hover:bg-muted/50 dark:border-white/5"
                    >
                        <History class="mr-2 h-4 w-4" />
                        Refresh
                    </Button>
                </div>
            </div>

            <!-- Log Entries List -->
            <div class="space-y-4">
                <div
                    v-for="log in logs.data"
                    :key="log.id"
                    class="group relative overflow-hidden rounded-2xl border border-border bg-card p-5 transition-all hover:shadow-md dark:border-white/5"
                >
                    <div
                        class="relative flex flex-col gap-6 lg:flex-row lg:items-center"
                    >
                        <!-- Event Icon -->
                        <div
                            :class="[
                                'flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-border/50 shadow-sm',
                                log.log_name === 'default'
                                    ? 'bg-muted/50 text-muted-foreground/40'
                                    : 'bg-primary/10 text-primary',
                            ]"
                        >
                            <Activity
                                v-if="log.description.includes('updated')"
                                class="h-6 w-6"
                            />
                            <Box
                                v-else-if="log.description.includes('created')"
                                class="h-6 w-6"
                            />
                            <Cpu v-else class="h-6 w-6" />
                        </div>

                        <!-- Core Info -->
                        <div class="flex-1 space-y-1">
                            <div class="flex items-center gap-2">
                                <Badge
                                    variant="outline"
                                    class="border-primary/10 bg-primary/5 px-2 py-0.5 text-xs font-bold tracking-tight text-primary uppercase"
                                >
                                    {{ log.log_name }}
                                </Badge>
                                <span
                                    class="font-mono text-xs font-bold tracking-tight text-muted-foreground/40 uppercase"
                                    >{{
                                        formatDistanceToNow(
                                            new Date(log.created_at),
                                            { addSuffix: true },
                                        )
                                    }}</span
                                >
                            </div>
                            <h3
                                class="text-base font-bold tracking-tight text-foreground transition-colors group-hover:text-primary"
                            >
                                {{ log.description }}
                            </h3>
                            <div
                                class="flex flex-wrap items-center gap-4 text-xs font-bold tracking-tight text-muted-foreground/60 uppercase"
                            >
                                <span class="flex items-center gap-1.5"
                                    ><User class="h-3.5 w-3.5" />
                                    {{ log.causer?.name || 'System' }}</span
                                >
                                <span
                                    class="h-1 w-1 rounded-full bg-border/50"
                                ></span>
                                <span class="flex items-center gap-1.5"
                                    ><Database class="h-3.5 w-3.5" />
                                    {{ log.subject_type.split('\\').pop() }} #{{
                                        log.subject_id
                                    }}</span
                                >
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex shrink-0 items-center gap-2">
                            <Button
                                variant="ghost"
                                class="h-9 w-9 rounded-lg hover:bg-muted/50"
                            >
                                <Info
                                    class="h-4 w-4 text-muted-foreground/40"
                                />
                            </Button>
                            <Button
                                variant="outline"
                                class="group/btn h-9 rounded-lg border-border px-4 text-xs font-medium tracking-tight text-foreground uppercase transition-all hover:border-primary hover:bg-primary hover:text-white"
                            >
                                Details
                                <ArrowRight
                                    class="ml-2 h-3 w-3 transition-transform group-hover/btn:translate-x-1"
                                />
                            </Button>
                        </div>
                    </div>
                </div>
                <div
                    v-if="!logs.data.length"
                    class="rounded-2xl border border-dashed border-border p-12 text-center text-xs font-bold tracking-tight text-muted-foreground/20 uppercase"
                >
                    No signals detected...
                </div>
            </div>

            <!-- Footer Pagination -->
            <div
                class="flex items-center justify-between rounded-2xl border border-border bg-card p-5 shadow-sm dark:border-white/5"
            >
                <p
                    class="text-xs font-bold tracking-tight text-muted-foreground/40 uppercase"
                >
                    Page {{ logs.current_page }} of {{ logs.last_page }}
                </p>
                <div class="flex items-center gap-2">
                    <template v-for="(link, index) in logs.links" :key="index">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="flex h-8 items-center justify-center rounded-lg border border-border bg-card px-3 text-xs font-medium tracking-tight uppercase transition-all hover:border-primary/20 hover:bg-primary/5 hover:text-primary"
                            :class="{
                                'border-primary bg-primary text-white shadow-lg shadow-primary/20':
                                    link.active,
                            }"
                            v-html="link.label"
                        />
                        <span
                            v-else
                            class="flex h-8 items-center justify-center rounded-lg border border-border/50 px-3 text-xs font-medium tracking-tight uppercase opacity-30 select-none"
                            v-html="link.label"
                        ></span>
                    </template>
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
