<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Calendar,
    Plus,
    Search,
    Filter,
    MoreHorizontal,
    Eye,
    Edit,
    Trash2,
    MapPin,
    Clock,
    Users,
    Star,
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
        case 'scheduled':
            return 'bg-emerald-500 text-white';
        case 'ongoing':
            return 'bg-amber-500 text-white';
        case 'completed':
            return 'bg-slate-500 text-white';
        case 'cancelled':
            return 'bg-rose-500 text-white';
        default:
            return 'bg-slate-500 text-white';
    }
};

const formatDate = (date: string | null) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};
</script>

<template>
    <Head title="School Calendar Log" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="font-pulsar flex h-full flex-1 flex-col gap-6 p-6">
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-500/10"
                    >
                        <Calendar class="h-6 w-6 text-amber-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">
                            Activity Log
                        </h1>
                        <p class="text-muted-foreground">
                            Historical and upcoming school events, festivals and
                            academic calendars
                        </p>
                    </div>
                </div>
                <Button
                    as-child
                    class="font-pulsar bg-amber-600 hover:bg-amber-700"
                >
                    <Link href="/events/create">
                        <Plus class="mr-2 h-4 w-4" />New Event
                    </Link>
                </Button>
            </div>

            <div
                class="flex flex-col gap-4 rounded-xl border bg-card p-4 shadow-sm md:flex-row md:items-center"
            >
                <div class="relative max-w-sm flex-1">
                    <Search
                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        v-model="searchQuery"
                        placeholder="Search event title or venue..."
                        class="h-10 pl-9"
                    />
                </div>
                <Button variant="outline" size="sm" class="font-pulsar h-10">
                    <Filter class="mr-2 h-4 w-4" />Status
                </Button>
            </div>

            <div
                class="overflow-hidden overflow-x-auto rounded-2xl border bg-card shadow-sm"
            >
                <table class="w-full text-left">
                    <thead>
                        <tr class="font-pulsar border-b bg-slate-50">
                            <th
                                class="px-6 py-4 text-xs font-bold tracking-wider text-slate-500 uppercase"
                            >
                                Event
                            </th>
                            <th
                                class="px-6 py-4 text-xs font-bold tracking-wider text-slate-500 uppercase"
                            >
                                Date
                            </th>
                            <th
                                class="px-6 py-4 text-xs font-bold tracking-wider text-slate-500 uppercase"
                            >
                                Venue
                            </th>
                            <th
                                class="px-6 py-4 text-xs font-bold tracking-wider text-slate-500 uppercase"
                            >
                                Status
                            </th>
                            <th
                                class="px-6 py-4 text-right text-xs font-bold tracking-wider text-slate-500 uppercase"
                            >
                                Fee
                            </th>
                            <th
                                class="px-6 py-4 text-right text-xs font-bold tracking-wider text-slate-500 uppercase"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y text-sm">
                        <tr
                            v-for="event in events.data"
                            :key="event.id"
                            class="group transition-colors hover:bg-slate-50/50"
                        >
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border bg-amber-50 text-amber-600 transition-colors group-hover:bg-amber-100"
                                    >
                                        <Calendar class="h-4 w-4" />
                                    </div>
                                    <div>
                                        <div
                                            class="line-clamp-1 max-w-[200px] truncate font-bold text-slate-900"
                                        >
                                            {{ event.title }}
                                        </div>
                                        <div
                                            v-if="event.is_featured"
                                            class="mt-0.5 flex items-center gap-1 text-xs font-bold tracking-tight text-amber-500 uppercase"
                                        >
                                            <Star
                                                class="h-2 w-2 fill-amber-500"
                                            />
                                            Featured Event
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div
                                    class="flex items-center gap-1.5 text-xs font-medium whitespace-nowrap text-slate-600"
                                >
                                    {{ formatDate(event.start_date) }}
                                </div>
                            </td>
                            <td
                                class="flex items-center gap-2 px-6 py-4 text-slate-500"
                            >
                                <MapPin class="h-3.5 w-3.5" />
                                <span class="max-w-[150px] truncate">{{
                                    event.venue
                                }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <Badge
                                    :class="getStatusColor(event.status)"
                                    class="rounded-full border-0 px-2 py-0.5 text-xs font-bold tracking-wider uppercase"
                                >
                                    {{ event.status }}
                                </Badge>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="font-bold text-slate-700">{{
                                    event.registration_fee > 0
                                        ? 'KES ' + event.registration_fee
                                        : 'FREE'
                                }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-8 w-8"
                                            ><MoreHorizontal class="h-4 w-4"
                                        /></Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent
                                        align="end"
                                        class="font-pulsar w-40"
                                    >
                                        <DropdownMenuItem
                                            ><Eye class="mr-2 h-4 w-4" />View
                                            Details</DropdownMenuItem
                                        >
                                        <DropdownMenuItem
                                            ><Edit class="mr-2 h-4 w-4" />Edit
                                            Event</DropdownMenuItem
                                        >
                                        <DropdownMenuItem
                                            ><Users
                                                class="mr-2 h-4 w-4"
                                            />Participants</DropdownMenuItem
                                        >
                                        <DropdownMenuItem
                                            class="font-bold text-rose-600"
                                            ><Trash2
                                                class="mr-2 h-4 w-4"
                                            />Cancel</DropdownMenuItem
                                        >
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </td>
                        </tr>
                        <tr v-if="events.data.length === 0">
                            <td
                                colspan="6"
                                class="px-6 py-12 text-center text-muted-foreground"
                            >
                                No events found for the requested period.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="events.links.length > 3"
                class="mt-4 flex justify-center"
            >
                <nav class="flex gap-1">
                    <template v-for="(link, k) in events.links" :key="k">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="font-pulsar rounded-lg border px-4 py-2 text-sm transition-colors"
                            :class="
                                link.active
                                    ? 'border-amber-600 bg-amber-600 font-bold text-white'
                                    : 'bg-card text-slate-600 hover:bg-slate-50'
                            "
                            v-html="link.label"
                        />
                        <span
                            v-else
                            class="font-pulsar cursor-not-allowed rounded-lg border bg-slate-100 px-4 py-2 text-sm text-slate-400 opacity-50"
                            v-html="link.label"
                        />
                    </template>
                </nav>
            </div>
        </div>
    </AppLayout>
</template>
