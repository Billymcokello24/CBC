<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Calendar, Plus, Search, Filter, MoreHorizontal, Eye, Edit, Trash2, MapPin, Clock, Users, Star } from 'lucide-vue-next';
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
} from '@/components/ui/dropdown-menu';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    events: any; // Paginator
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Events & Clubs', href: '/events' },
    { title: 'Calendar Log', href: '/events/list' },
];

const searchQuery = ref('');

const getStatusColor = (status: string) => {
    switch (status?.toLowerCase()) {
        case 'scheduled': return 'bg-emerald-500 text-white';
        case 'ongoing': return 'bg-amber-500 text-white';
        case 'completed': return 'bg-slate-500 text-white';
        case 'cancelled': return 'bg-rose-500 text-white';
        default: return 'bg-slate-500 text-white';
    }
};

const formatDate = (date: string | null) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
};

</script>

<template>
    <Head title="School Calendar Log" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 font-pulsar">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-500/10">
                        <Calendar class="h-6 w-6 text-amber-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Activity Log</h1>
                        <p class="text-muted-foreground">Historical and upcoming school events, festivals and academic calendars</p>
                    </div>
                </div>
                <Button as-child class="bg-amber-600 hover:bg-amber-700 font-pulsar">
                    <Link href="/events/create">
                        <Plus class="mr-2 h-4 w-4" />New Event
                    </Link>
                </Button>
            </div>

            <div class="flex flex-col gap-4 rounded-xl border bg-card p-4 shadow-sm md:flex-row md:items-center">
                <div class="relative flex-1 max-w-sm">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input v-model="searchQuery" placeholder="Search event title or venue..." class="pl-9 h-10" />
                </div>
                <Button variant="outline" size="sm" class="h-10 font-pulsar">
                    <Filter class="mr-2 h-4 w-4" />Status
                </Button>
            </div>

            <div class="rounded-2xl border bg-card shadow-sm overflow-hidden overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-b font-pulsar">
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-wider">Event</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-wider">Date</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-wider">Venue</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-wider">Status</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-wider text-right">Fee</th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase text-slate-500 tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y text-sm">
                        <tr v-for="event in events.data" :key="event.id" class="group hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                     <div class="h-8 w-8 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600 border group-hover:bg-amber-100 transition-colors shrink-0">
                                         <Calendar class="h-4 w-4" />
                                     </div>
                                    <div>
                                        <div class="font-bold text-slate-900 line-clamp-1 truncate max-w-[200px]">{{ event.title }}</div>
                                        <div v-if="event.is_featured" class="flex items-center gap-1 text-[8px] text-amber-500 font-bold uppercase tracking-widest mt-0.5"><Star class="h-2 w-2 fill-amber-500" /> Featured Event</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-1.5 text-xs text-slate-600 font-medium whitespace-nowrap">
                                    {{ formatDate(event.start_date) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 flex items-center gap-2 text-slate-500">
                                <MapPin class="h-3.5 w-3.5" /> <span class="truncate max-w-[150px]">{{ event.venue }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <Badge :class="getStatusColor(event.status)" class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider border-0">
                                    {{ event.status }}
                                </Badge>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="font-bold text-slate-700">{{ event.registration_fee > 0 ? 'KES ' + event.registration_fee : 'FREE' }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="ghost" size="icon" class="h-8 w-8"><MoreHorizontal class="h-4 w-4" /></Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end" class="w-40 font-pulsar">
                                        <DropdownMenuItem><Eye class="mr-2 h-4 w-4" />View Details</DropdownMenuItem>
                                        <DropdownMenuItem><Edit class="mr-2 h-4 w-4" />Edit Event</DropdownMenuItem>
                                        <DropdownMenuItem><Users class="mr-2 h-4 w-4" />Participants</DropdownMenuItem>
                                        <DropdownMenuItem class="text-rose-600 font-bold"><Trash2 class="mr-2 h-4 w-4" />Cancel</DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </td>
                        </tr>
                        <tr v-if="events.data.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-muted-foreground italic">
                                No events found for the requested period.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="events.links.length > 3" class="flex justify-center mt-4">
                <nav class="flex gap-1">
                    <template v-for="(link, k) in events.links" :key="k">
                        <Link v-if="link.url" 
                            :href="link.url" 
                            class="px-4 py-2 text-sm rounded-lg border transition-colors font-pulsar"
                            :class="link.active ? 'bg-amber-600 text-white border-amber-600 font-bold' : 'bg-card text-slate-600 hover:bg-slate-50'"
                            v-html="link.label"
                        />
                        <span v-else 
                            class="px-4 py-2 text-sm rounded-lg border bg-slate-100 text-slate-400 cursor-not-allowed opacity-50 font-pulsar"
                            v-html="link.label"
                        />
                    </template>
                </nav>
            </div>
        </div>
    </AppLayout>
</template>
