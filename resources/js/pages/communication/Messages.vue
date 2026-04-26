<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    MessageSquare,
    Plus,
    Search,
    Filter,
    MoreHorizontal,
    Eye,
    Edit,
    Trash2,
    User,
    Send,
    MailOpen,
    AlertCircle,
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
    return new Date(date).toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatDate = (date: string | null) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString();
};
</script>

<template>
    <Head title="Direct Messages" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="font-pulsar flex h-full flex-1 flex-col gap-6 p-6">
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-500/10"
                    >
                        <MessageSquare class="h-6 w-6 text-blue-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">
                            Messages
                        </h1>
                        <p class="text-muted-foreground">
                            Internal messaging system for students, parents and
                            staff
                        </p>
                    </div>
                </div>
                <Button
                    as-child
                    class="font-pulsar bg-blue-600 hover:bg-blue-700"
                >
                    <Link href="/communication/messages/create">
                        <Plus class="mr-2 h-4 w-4" />Compose
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
                        placeholder="Search sender or content..."
                        class="h-10 pl-9"
                    />
                </div>
                <Button variant="outline" size="sm" class="font-pulsar h-10">
                    <Filter class="mr-2 h-4 w-4" />Unread Only
                </Button>
            </div>

            <div
                class="flex min-h-[500px] flex-col overflow-hidden rounded-2xl border bg-card shadow-sm"
            >
                <div class="flex-1 overflow-y-auto">
                    <div
                        v-for="message in messages.data"
                        :key="message.id"
                        class="group flex items-start gap-4 border-b p-4 transition-colors last:border-0 hover:bg-slate-50/50"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-400"
                        >
                            <User
                                v-if="!message.sender?.avatar"
                                class="h-5 w-5"
                            />
                            <img
                                v-else
                                :src="message.sender?.avatar"
                                class="h-full w-full rounded-full object-cover"
                            />
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="mb-1 flex items-center justify-between">
                                <div
                                    class="flex items-center gap-2 font-bold text-slate-900"
                                >
                                    {{ message.sender?.name }}
                                    <Badge
                                        v-if="!message.read_at"
                                        variant="outline"
                                        class="border-blue-100 bg-blue-50 px-1 py-0 text-xs font-bold tracking-tighter text-blue-700 uppercase"
                                        >NEW</Badge
                                    >
                                </div>
                                <div
                                    class="text-xs font-bold text-slate-400 uppercase"
                                >
                                    {{ formatDate(message.created_at) }}
                                    {{ formatTime(message.created_at) }}
                                </div>
                            </div>
                            <div
                                class="line-clamp-2 pr-12 text-sm text-slate-600"
                            >
                                {{ message.content }}
                            </div>
                            <div
                                class="mt-3 flex items-center gap-3 opacity-0 transition-opacity group-hover:opacity-100"
                            >
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    class="h-7 px-2 text-xs font-bold tracking-wider text-slate-500 uppercase hover:text-blue-600"
                                >
                                    <MailOpen class="mr-1 h-3 w-3" /> Open
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    class="h-7 px-2 text-xs font-bold tracking-wider text-slate-500 uppercase hover:text-rose-600"
                                >
                                    <Trash2 class="mr-1 h-3 w-3" /> Delete
                                </Button>
                            </div>
                        </div>
                        <div class="shrink-0">
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-8 w-8 text-slate-300 group-hover:text-slate-500"
                                        ><MoreHorizontal class="h-4 w-4"
                                    /></Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent
                                    align="end"
                                    class="font-pulsar w-40"
                                >
                                    <DropdownMenuItem
                                        ><Send
                                            class="mr-2 h-4 w-4"
                                        />Reply</DropdownMenuItem
                                    >
                                    <DropdownMenuItem
                                        ><AlertCircle
                                            class="mr-2 h-4 w-4"
                                        />Flag</DropdownMenuItem
                                    >
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </div>

                    <div
                        v-if="messages.data.length === 0"
                        class="flex h-full flex-col items-center justify-center py-20 text-center"
                    >
                        <div
                            class="mb-4 flex h-16 w-16 items-center justify-center rounded-full border-2 border-dashed border-slate-200 bg-slate-50 text-slate-300"
                        >
                            <MessageSquare class="h-8 w-8" />
                        </div>
                        <p class="font-medium text-muted-foreground">
                            Your inbox is currently empty.
                        </p>
                        <Button
                            variant="outline"
                            class="font-pulsar group mt-4"
                        >
                            <Plus
                                class="mr-2 h-4 w-4 transition-colors group-hover:text-blue-600"
                            />
                            Start a Conversation
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div
                v-if="messages.links.length > 3"
                class="mt-4 flex justify-center"
            >
                <nav class="flex gap-1">
                    <template v-for="(link, k) in messages.links" :key="k">
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
