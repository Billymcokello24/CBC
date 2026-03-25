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
    Box
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
    router.get(route('super-admin.activity-logs.index'), { 
        search: search.value,
    }, { preserveState: true, replace: true });
}, 500);
</script>

<template>
    <Head title="System Audit Trail" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-foreground">Activity Logs</h1>
                    <p class="text-sm font-bold text-muted-foreground/60 uppercase tracking-widest">Track all system events and user actions.</p>
                </div>
            </div>

            <!-- Toolbar -->
            <div class="rounded-2xl border border-border bg-card p-4 shadow-sm dark:border-white/5">
                <div class="flex flex-col md:flex-row gap-4 items-center">
                    <div class="relative flex-1 group">
                        <Search class="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/40" />
                        <Input 
                            v-model="search"
                            @input="handleSearch"
                            placeholder="Search logs by description, category, or user..." 
                            class="pl-10 h-11 rounded-xl border-border bg-muted/20 font-bold focus:ring-primary/20"
                        />
                    </div>
                    <Button variant="outline" class="h-11 px-6 rounded-xl border-border font-black text-xs uppercase tracking-widest text-muted-foreground hover:bg-muted/50 dark:border-white/5">
                        <History class="mr-2 h-4 w-4" />
                        Refresh
                    </Button>
                </div>
            </div>

            <!-- Log Entries List -->
            <div class="space-y-4">
                <div v-for="log in logs.data" :key="log.id" class="group relative overflow-hidden rounded-2xl border border-border bg-card p-5 transition-all hover:shadow-md dark:border-white/5">
                    <div class="flex flex-col lg:flex-row lg:items-center gap-6 relative">
                        <!-- Event Icon -->
                        <div :class="[
                            'flex h-12 w-12 shrink-0 items-center justify-center rounded-xl shadow-sm border border-border/50',
                            log.log_name === 'default' ? 'bg-muted/50 text-muted-foreground/40' : 'bg-primary/10 text-primary'
                        ]">
                            <Activity v-if="log.description.includes('updated')" class="h-6 w-6" />
                            <Box v-else-if="log.description.includes('created')" class="h-6 w-6" />
                            <Cpu v-else class="h-6 w-6" />
                        </div>

                        <!-- Core Info -->
                        <div class="flex-1 space-y-1">
                            <div class="flex items-center gap-2">
                                <Badge variant="outline" class="font-black text-[8px] uppercase tracking-widest bg-primary/5 text-primary border-primary/10 px-2 py-0.5">
                                    {{ log.log_name }}
                                </Badge>
                                <span class="text-[9px] font-black text-muted-foreground/40 uppercase tracking-widest font-mono">{{ formatDistanceToNow(new Date(log.created_at), { addSuffix: true }) }}</span>
                            </div>
                            <h3 class="text-base font-black text-foreground tracking-tight group-hover:text-primary transition-colors">{{ log.description }}</h3>
                            <div class="flex flex-wrap items-center gap-4 text-[10px] font-bold text-muted-foreground/60 uppercase tracking-tight">
                                <span class="flex items-center gap-1.5"><User class="h-3.5 w-3.5" /> {{ log.causer?.name || 'System' }}</span>
                                <span class="h-1 w-1 rounded-full bg-border/50"></span>
                                <span class="flex items-center gap-1.5"><Database class="h-3.5 w-3.5" /> {{ log.subject_type.split('\\').pop() }} #{{ log.subject_id }}</span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-2 shrink-0">
                            <Button variant="ghost" class="h-9 w-9 rounded-lg hover:bg-muted/50">
                                <Info class="h-4 w-4 text-muted-foreground/40" />
                            </Button>
                            <Button variant="outline" class="h-9 px-4 rounded-lg border-border text-[10px] font-black uppercase tracking-widest text-foreground hover:bg-primary hover:text-white hover:border-primary transition-all group/btn">
                                Details
                                <ArrowRight class="ml-2 h-3 w-3 transition-transform group-hover/btn:translate-x-1" />
                            </Button>
                        </div>
                    </div>
                </div>
                <div v-if="!logs.data.length" class="p-12 text-center rounded-2xl border border-dashed border-border text-[10px] font-black text-muted-foreground/20 uppercase tracking-widest">
                     No signals detected...
                </div>
            </div>

            <!-- Footer Pagination -->
            <div class="flex items-center justify-between p-5 rounded-2xl border border-border bg-card shadow-sm dark:border-white/5">
                 <p class="text-[10px] font-black text-muted-foreground/40 uppercase tracking-widest">
                      Page {{ logs.current_page }} of {{ logs.last_page }}
                 </p>
                 <div class="flex items-center gap-2">
                      <template v-for="(link, index) in logs.links" :key="index">
                           <Link
                               v-if="link.url"
                               :href="link.url"
                               class="h-8 px-3 rounded-lg border border-border bg-card text-[10px] font-black uppercase tracking-widest flex items-center justify-center transition-all hover:bg-primary/5 hover:text-primary hover:border-primary/20"
                               :class="{ 'bg-primary text-white border-primary shadow-lg shadow-primary/20': link.active }"
                               v-html="link.label"
                           />
                           <span v-else class="h-8 px-3 rounded-lg border border-border/50 text-[10px] font-black uppercase tracking-widest flex items-center justify-center opacity-30 select-none" v-html="link.label"></span>
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
