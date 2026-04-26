<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Megaphone,
    Plus,
    Search,
    Filter,
    MoreHorizontal,
    Eye,
    Edit,
    Trash2,
    Calendar,
    User,
    Pin,
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
    announcements: any; // Paginator
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Communication', href: '/communication' },
    { title: 'Announcements', href: '/communication/announcements' },
];

const searchQuery = ref('');

const getPriorityColor = (priority: string) => {
    switch (priority?.toLowerCase()) {
        case 'high':
            return 'bg-rose-500 text-white';
        case 'medium':
            return 'bg-amber-500 text-white';
        case 'low':
            return 'bg-emerald-500 text-white';
        default:
            return 'bg-slate-500 text-white';
    }
};

const formatDate = (date: string | null) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString();
};
</script>

<template>
    <Head title="Public Announcements" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="font-pulsar flex h-full flex-1 flex-col gap-6 p-6">
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-500/10"
                    >
                        <Megaphone class="h-6 w-6 text-purple-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">
                            Announcements
                        </h1>
                        <p class="text-muted-foreground">
                            Manage school broadcasts, news and official alerts
                        </p>
                    </div>
                </div>
                <Button
                    as-child
                    class="font-pulsar bg-purple-600 hover:bg-purple-700"
                >
                    <Link href="/communication/announcements/create">
                        <Plus class="mr-2 h-4 w-4" />New Announcement
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
                        placeholder="Search title or content..."
                        class="h-10 pl-9"
                    />
                </div>
                <Button variant="outline" size="sm" class="font-pulsar h-10">
                    <Filter class="mr-2 h-4 w-4" />Type
                </Button>
            </div>

            <div
                class="overflow-hidden overflow-x-auto rounded-2xl border bg-card shadow-sm"
            >
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b bg-slate-50">
                            <th
                                class="px-6 py-4 text-xs font-bold tracking-wider text-slate-500 uppercase"
                            >
                                Announcement
                            </th>
                            <th
                                class="px-6 py-4 text-xs font-bold tracking-wider text-slate-500 uppercase"
                            >
                                Priority
                            </th>
                            <th
                                class="px-6 py-4 text-xs font-bold tracking-wider text-slate-500 uppercase"
                            >
                                Target
                            </th>
                            <th
                                class="px-6 py-4 text-xs font-bold tracking-wider text-slate-500 uppercase"
                            >
                                Author
                            </th>
                            <th
                                class="px-6 py-4 text-xs font-bold tracking-wider text-slate-500 uppercase"
                            >
                                Published
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
                            v-for="announcement in announcements.data"
                            :key="announcement.id"
                            class="group transition-colors hover:bg-slate-50/50"
                        >
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        v-if="announcement.is_pinned"
                                        class="text-purple-600"
                                    >
                                        <Pin class="h-4 w-4" />
                                    </div>
                                    <div>
                                        <div
                                            class="line-clamp-1 max-w-[200px] truncate font-bold text-slate-900"
                                        >
                                            {{ announcement.title }}
                                        </div>
                                        <div
                                            class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                                        >
                                            {{ announcement.type }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <Badge
                                    :class="
                                        getPriorityColor(announcement.priority)
                                    "
                                    class="rounded-full border-0 px-2 py-0.5 text-xs font-bold uppercase"
                                >
                                    {{ announcement.priority }}
                                </Badge>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-medium text-slate-600"
                                    >All Students</span
                                >
                            </td>
                            <td class="px-6 py-4">
                                <div
                                    class="flex items-center gap-2 text-xs text-slate-500"
                                >
                                    <User class="h-3 w-3" />
                                    {{ announcement.creator?.name || 'Admin' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500">
                                {{ formatDate(announcement.publish_at) }}
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
                                            ><Eye
                                                class="mr-2 h-4 w-4"
                                            />Details</DropdownMenuItem
                                        >
                                        <DropdownMenuItem
                                            ><Edit class="mr-2 h-4 w-4" />Edit
                                            Broadcast</DropdownMenuItem
                                        >
                                        <DropdownMenuItem
                                            class="font-bold text-rose-600"
                                            ><Trash2
                                                class="mr-2 h-4 w-4"
                                            />Remove</DropdownMenuItem
                                        >
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </td>
                        </tr>
                        <tr v-if="announcements.data.length === 0">
                            <td
                                colspan="6"
                                class="px-6 py-12 text-center text-muted-foreground"
                            >
                                No public announcements found for current term.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="announcements.links.length > 3"
                class="mt-4 flex justify-center"
            >
                <nav class="flex gap-1">
                    <template v-for="(link, k) in announcements.links" :key="k">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="font-pulsar rounded-lg border px-4 py-2 text-sm transition-colors"
                            :class="
                                link.active
                                    ? 'border-purple-600 bg-purple-600 font-bold text-white'
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
