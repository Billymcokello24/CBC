<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Users,
    Plus,
    Search,
    Filter,
    MoreHorizontal,
    Eye,
    Edit,
    Trash2,
    User,
    MapPin,
    Clock,
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
    clubs: any; // Paginator
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Events & Clubs', href: '/events' },
    { title: 'School Clubs', href: '/events/clubs' },
];

const searchQuery = ref('');
</script>

<template>
    <Head title="School Clubs" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="font-pulsar flex h-full flex-1 flex-col gap-6 p-6">
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-500/10"
                    >
                        <Users class="h-6 w-6 text-blue-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">
                            Clubs & Societies
                        </h1>
                        <p class="text-muted-foreground">
                            Manage school clubs, memberships and extracurricular
                            participation
                        </p>
                    </div>
                </div>
                <Button
                    as-child
                    class="font-pulsar bg-blue-600 hover:bg-blue-700"
                >
                    <Link href="/events/clubs/create">
                        <Plus class="mr-2 h-4 w-4" />New Club
                    </Link>
                </Button>
            </div>

            <div
                class="flex items-center gap-4 rounded-xl border bg-card p-4 shadow-sm"
            >
                <div class="relative max-w-sm flex-1">
                    <Search
                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        v-model="searchQuery"
                        placeholder="Search club name or code..."
                        class="h-10 pl-9"
                    />
                </div>
                <Button
                    variant="outline"
                    size="sm"
                    class="font-pulsar flex h-10 items-center"
                >
                    <Filter class="mr-2 h-4 w-4" />Categories
                </Button>
            </div>

            <div
                class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
            >
                <div
                    v-for="club in clubs.data"
                    :key="club.id"
                    class="group relative flex flex-col overflow-hidden rounded-2xl border bg-card shadow-sm transition-all hover:border-blue-200"
                >
                    <div class="flex-1 p-6">
                        <div class="mb-4 flex items-start justify-between">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl border bg-slate-50 transition-colors group-hover:border-blue-100 group-hover:bg-blue-50"
                            >
                                <Users
                                    class="h-6 w-6 text-slate-400 transition-colors group-hover:text-blue-500"
                                />
                            </div>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8 text-slate-300 hover:text-slate-600"
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
                                        ><Plus class="mr-2 h-4 w-4" />Add
                                        Member</DropdownMenuItem
                                    >
                                    <DropdownMenuItem
                                        class="font-bold text-rose-600"
                                        ><Trash2
                                            class="mr-2 h-4 w-4"
                                        />Archive</DropdownMenuItem
                                    >
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>

                        <div>
                            <Badge
                                variant="outline"
                                class="mb-2 border-0 border-blue-100 bg-blue-50 px-2 py-0 text-xs font-bold tracking-tight text-blue-700 uppercase"
                            >
                                {{ club.category }}
                            </Badge>
                            <h3
                                class="text-lg font-bold text-slate-900 transition-colors group-hover:text-blue-700"
                            >
                                {{ club.name }}
                            </h3>
                            <p
                                class="mt-1 line-clamp-2 pr-4 text-xs text-muted-foreground"
                            >
                                "{{ club.motto }}"
                            </p>
                        </div>

                        <div class="mt-6 flex flex-col gap-3">
                            <div
                                class="flex items-center text-xs font-medium text-slate-500"
                            >
                                <User class="mr-2 h-3.5 w-3.5 text-slate-400" />
                                Patron: {{ club.patron?.name || 'Staff' }}
                            </div>
                            <div
                                class="flex items-center text-xs font-medium text-slate-500"
                            >
                                <Clock
                                    class="mr-2 h-3.5 w-3.5 text-slate-400"
                                />
                                {{ club.meeting_day }} @ {{ club.meeting_time }}
                            </div>
                            <div
                                class="flex items-center text-xs font-medium text-slate-500"
                            >
                                <MapPin
                                    class="mr-2 h-3.5 w-3.5 text-slate-400"
                                />
                                {{ club.meeting_venue }}
                            </div>
                        </div>
                    </div>
                    <div
                        class="mt-auto flex items-center justify-between border-t bg-slate-50/50 px-6 py-4"
                    >
                        <div class="text-xs font-bold text-slate-600">
                            {{ club.members_count }} Members
                        </div>
                        <Link
                            :href="`/events/clubs/${club.id}`"
                            class="text-xs font-bold tracking-tight text-blue-600 uppercase hover:underline"
                        >
                            Manager Club
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="clubs.links.length > 3" class="mt-4 flex justify-center">
                <nav class="flex gap-1">
                    <template v-for="(link, k) in clubs.links" :key="k">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="font-pulsar rounded-lg border px-4 py-2 text-sm transition-colors"
                            :class="
                                link.active
                                    ? 'border-blue-600 bg-blue-600 font-bold text-white'
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
