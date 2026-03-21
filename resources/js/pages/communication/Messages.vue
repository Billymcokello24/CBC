<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { MessageSquare, Plus, Search, Filter, MoreHorizontal, Eye, Edit, Trash2, User, Send, MailOpen, AlertCircle } from 'lucide-vue-next';
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
    messages: any; // Paginator
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Communication', href: '/communication' },
    { title: 'Messages', href: '/communication/messages' },
];

const searchQuery = ref('');

const formatTime = (date: string | null) => {
    if (!date) return '—';
    return new Date(date).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

const formatDate = (date: string | null) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString();
};

</script>

<template>
    <Head title="Direct Messages" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 font-pulsar">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-500/10">
                        <MessageSquare class="h-6 w-6 text-blue-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Messages</h1>
                        <p class="text-muted-foreground">Internal messaging system for students, parents and staff</p>
                    </div>
                </div>
                <Button as-child class="bg-blue-600 hover:bg-blue-700 font-pulsar">
                    <Link href="/communication/messages/create">
                        <Plus class="mr-2 h-4 w-4" />Compose
                    </Link>
                </Button>
            </div>

            <div class="flex flex-col gap-4 rounded-xl border bg-card p-4 shadow-sm md:flex-row md:items-center">
                <div class="relative flex-1 max-w-sm">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input v-model="searchQuery" placeholder="Search sender or content..." class="pl-9 h-10" />
                </div>
                <Button variant="outline" size="sm" class="h-10 font-pulsar">
                    <Filter class="mr-2 h-4 w-4" />Unread Only
                </Button>
            </div>

            <div class="rounded-2xl border bg-card shadow-sm overflow-hidden flex flex-col min-h-[500px]">
                <div class="flex-1 overflow-y-auto">
                    <div v-for="message in messages.data" :key="message.id" class="group border-b last:border-0 hover:bg-slate-50/50 transition-colors p-4 flex items-start gap-4">
                        <div class="h-10 w-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 shrink-0">
                            <User v-if="!message.sender?.avatar" class="h-5 w-5" />
                            <img v-else :src="message.sender?.avatar" class="h-full w-full rounded-full object-cover" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <div class="font-bold text-slate-900 flex items-center gap-2">
                                    {{ message.sender?.name }}
                                    <Badge v-if="!message.read_at" variant="outline" class="bg-blue-50 text-blue-700 border-blue-100 text-[8px] font-bold px-1 py-0 uppercase tracking-tighter">NEW</Badge>
                                </div>
                                <div class="text-[10px] text-slate-400 font-bold uppercase">{{ formatDate(message.created_at) }} {{ formatTime(message.created_at) }}</div>
                            </div>
                            <div class="text-sm text-slate-600 line-clamp-2 pr-12">
                                {{ message.content }}
                            </div>
                            <div class="mt-3 flex items-center gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                <Button variant="ghost" size="sm" class="h-7 px-2 text-[10px] font-bold uppercase tracking-wider text-slate-500 hover:text-blue-600">
                                    <MailOpen class="h-3 w-3 mr-1" /> Open
                                </Button>
                                <Button variant="ghost" size="sm" class="h-7 px-2 text-[10px] font-bold uppercase tracking-wider text-slate-500 hover:text-rose-600">
                                    <Trash2 class="h-3 w-3 mr-1" /> Delete
                                </Button>
                            </div>
                        </div>
                        <div class="shrink-0">
                             <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="ghost" size="icon" class="h-8 w-8 text-slate-300 group-hover:text-slate-500"><MoreHorizontal class="h-4 w-4" /></Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" class="w-40 font-pulsar">
                                    <DropdownMenuItem><Send class="mr-2 h-4 w-4" />Reply</DropdownMenuItem>
                                    <DropdownMenuItem><AlertCircle class="mr-2 h-4 w-4" />Flag</DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </div>
                    
                    <div v-if="messages.data.length === 0" class="flex flex-col items-center justify-center h-full py-20 text-center">
                        <div class="h-16 w-16 rounded-full bg-slate-50 flex items-center justify-center mb-4 text-slate-300 border-2 border-dashed border-slate-200">
                             <MessageSquare class="h-8 w-8" />
                        </div>
                        <p class="text-muted-foreground italic font-medium">Your inbox is currently empty.</p>
                        <Button variant="outline" class="mt-4 font-pulsar group">
                             <Plus class="h-4 w-4 mr-2 group-hover:text-blue-600 transition-colors" /> Start a Conversation
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="messages.links.length > 3" class="flex justify-center mt-4">
                <nav class="flex gap-1">
                    <template v-for="(link, k) in messages.links" :key="k">
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
