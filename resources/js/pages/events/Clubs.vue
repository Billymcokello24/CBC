<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Users, Plus, Search, Filter, MoreHorizontal, Eye, Edit, Trash2, User, MapPin, Clock } from 'lucide-vue-next';
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
        <div class="flex h-full flex-1 flex-col gap-6 p-6 font-pulsar">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-500/10">
                        <Users class="h-6 w-6 text-blue-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Clubs & Societies</h1>
                        <p class="text-muted-foreground">Manage school clubs, memberships and extracurricular participation</p>
                    </div>
                </div>
                <Button as-child class="bg-blue-600 hover:bg-blue-700 font-pulsar">
                    <Link href="/events/clubs/create">
                        <Plus class="mr-2 h-4 w-4" />New Club
                    </Link>
                </Button>
            </div>

            <div class="flex items-center gap-4 rounded-xl border bg-card p-4 shadow-sm">
                <div class="relative flex-1 max-w-sm">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input v-model="searchQuery" placeholder="Search club name or code..." class="pl-9 h-10" />
                </div>
                <Button variant="outline" size="sm" class="h-10 font-pulsar flex items-center">
                    <Filter class="mr-2 h-4 w-4" />Categories
                </Button>
            </div>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <div v-for="club in clubs.data" :key="club.id" class="rounded-2xl border bg-card overflow-hidden shadow-sm hover:border-blue-200 transition-all flex flex-col group relative">
                    <div class="p-6 flex-1">
                        <div class="flex items-start justify-between mb-4">
                            <div class="h-12 w-12 rounded-xl bg-slate-50 flex items-center justify-center border group-hover:bg-blue-50 group-hover:border-blue-100 transition-colors">
                                <Users class="h-6 w-6 text-slate-400 group-hover:text-blue-500 transition-colors" />
                            </div>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="ghost" size="icon" class="h-8 w-8 text-slate-300 hover:text-slate-600"><MoreHorizontal class="h-4 w-4" /></Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" class="w-40 font-pulsar">
                                    <DropdownMenuItem><Eye class="mr-2 h-4 w-4" />Details</DropdownMenuItem>
                                    <DropdownMenuItem><Plus class="mr-2 h-4 w-4" />Add Member</DropdownMenuItem>
                                    <DropdownMenuItem class="text-rose-600 font-bold"><Trash2 class="mr-2 h-4 w-4" />Archive</DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                        
                        <div>
                            <Badge variant="outline" class="mb-2 bg-blue-50 text-blue-700 border-blue-100 uppercase text-[9px] font-bold tracking-widest px-2 py-0 border-0">
                                {{ club.category }}
                            </Badge>
                            <h3 class="text-lg font-bold text-slate-900 group-hover:text-blue-700 transition-colors">{{ club.name }}</h3>
                            <p class="text-xs text-muted-foreground mt-1 line-clamp-2 italic pr-4">"{{ club.motto }}"</p>
                        </div>
                        
                        <div class="mt-6 flex flex-col gap-3">
                             <div class="flex items-center text-xs text-slate-500 font-medium">
                                <User class="h-3.5 w-3.5 mr-2 text-slate-400" /> Patron: {{ club.patron?.name || 'Staff' }}
                             </div>
                             <div class="flex items-center text-xs text-slate-500 font-medium">
                                 <Clock class="h-3.5 w-3.5 mr-2 text-slate-400" /> {{ club.meeting_day }} @ {{ club.meeting_time }}
                             </div>
                             <div class="flex items-center text-xs text-slate-500 font-medium">
                                 <MapPin class="h-3.5 w-3.5 mr-2 text-slate-400" /> {{ club.meeting_venue }}
                             </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-slate-50/50 border-t flex items-center justify-between mt-auto">
                        <div class="text-xs font-bold text-slate-600">
                             {{ club.members_count }} Members
                        </div>
                        <Link :href="`/events/clubs/${club.id}`" class="text-[10px] font-bold text-blue-600 hover:underline uppercase tracking-widest">
                            Manager Club
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="clubs.links.length > 3" class="flex justify-center mt-4">
                <nav class="flex gap-1">
                    <template v-for="(link, k) in clubs.links" :key="k">
                        <Link v-if="link.url" 
                            :href="link.url" 
                            class="px-4 py-2 text-sm rounded-lg border transition-colors font-pulsar"
                            :class="link.active ? 'bg-blue-600 text-white border-blue-600 font-bold' : 'bg-card text-slate-600 hover:bg-slate-50'"
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
