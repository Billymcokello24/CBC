<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Book as BookIcon, Plus, Search, Filter, MoreHorizontal, Eye, Edit, Trash2, BookOpenCheck, Grid, List as ListIcon } from 'lucide-vue-next';
import { ref, computed } from 'vue';
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
    books: any; // Paginator
    categories: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Library', href: '/library' },
    { title: 'Books Catalog', href: '/library/books' },
];

const searchQuery = ref('');
const viewMode = ref<'grid' | 'list'>('grid');

const getAvailabilityColor = (available: number, total: number) => {
    if (available === 0) return 'text-rose-600 bg-rose-50 border-rose-100 dark:bg-rose-950/30 dark:text-rose-400 dark:border-rose-900/50';
    if (available < total * 0.2) return 'text-amber-600 bg-amber-50 border-amber-100 dark:bg-amber-950/30 dark:text-amber-400 dark:border-amber-900/50';
    return 'text-emerald-600 bg-emerald-50 border-emerald-100 dark:bg-emerald-950/30 dark:text-emerald-400 dark:border-emerald-900/50';
};

</script>

<template>
    <Head title="Library Catalog" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-500/10">
                        <BookIcon class="h-6 w-6 text-purple-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Books Catalog</h1>
                        <p class="text-muted-foreground">Manage school collection and bibliographic resource data</p>
                    </div>
                </div>
                <Button as-child class="bg-purple-600 hover:bg-purple-700">
                    <Link href="/library/books/create">
                        <Plus class="mr-2 h-4 w-4" />Add Book
                    </Link>
                </Button>
            </div>

            <div class="flex flex-col gap-4 rounded-xl border bg-card p-4 shadow-sm md:flex-row md:items-center">
                <div class="relative flex-1 max-w-sm">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input v-model="searchQuery" placeholder="Search title, author or ISBN..." class="pl-9 h-10" />
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" class="h-10" @click="viewMode = 'grid'">
                        <Grid class="h-4 w-4" />
                    </Button>
                    <Button variant="outline" size="sm" class="h-10" @click="viewMode = 'list'">
                        <ListIcon class="h-4 w-4" />
                    </Button>
                    <Button variant="outline" size="sm" class="h-10">
                        <Filter class="mr-2 h-4 w-4" />Categories
                    </Button>
                </div>
            </div>

            <div v-if="viewMode === 'grid'" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <div v-for="book in books.data" :key="book.id" class="group relative overflow-hidden rounded-2xl border bg-card transition-all hover:shadow-md hover:border-purple-200">
                    <!-- Placeholder Cover -->
                    <div class="aspect-[3/4] w-full bg-slate-100 flex items-center justify-center border-b">
                         <BookIcon class="h-12 w-12 text-slate-300 opacity-50" />
                    </div>
                    <div class="p-5">
                        <Badge variant="outline" class="mb-2 text-[10px] font-bold uppercase tracking-wider text-purple-600 border-purple-100">
                             {{ book.category?.name || 'General' }}
                        </Badge>
                        <h3 class="font-bold text-slate-900 dark:text-white line-clamp-1 mb-1">{{ book.title }}</h3>
                        <p class="text-xs text-muted-foreground mb-4">{{ book.author }}</p>
                        
                        <div class="flex items-center justify-between">
                            <Badge variant="outline" 
                                class="rounded-full px-2 py-0.5 text-[10px] font-bold"
                                :class="getAvailabilityColor(book.available_copies, book.total_copies)"
                            >
                                {{ book.available_copies }} / {{ book.total_copies }} available
                            </Badge>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="ghost" size="icon" class="h-8 w-8"><MoreHorizontal class="h-4 w-4" /></Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" class="w-40">
                                    <DropdownMenuItem as-child><Link :href="`/library/books/${book.id}`"><Eye class="mr-2 h-4 w-4" />View Details</Link></DropdownMenuItem>
                                    <DropdownMenuItem as-child><Link :href="`/library/books/${book.id}/edit`"><Edit class="mr-2 h-4 w-4" />Edit Book</Link></DropdownMenuItem>
                                    <DropdownMenuItem class="text-destructive"><Trash2 class="mr-2 h-4 w-4" />Delete</DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </div>
                </div>
            </div>

            <!-- List View -->
            <div v-else class="rounded-2xl border bg-card shadow-sm overflow-hidden overflow-x-auto">
                 <!-- Table implementation omitted for brevity, similar to Invoices/Payments -->
                 <table class="w-full text-left">
                    <thead class="bg-slate-50 border-b">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500">Title</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500">Author</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500">Category</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500">Shelf</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y text-sm">
                        <tr v-for="book in books.data" :key="book.id" class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4 font-bold text-slate-900">{{ book.title }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ book.author }}</td>
                            <td class="px-6 py-4">{{ book.category?.name || '—' }}</td>
                            <td class="px-6 py-4 font-mono text-xs uppercase">{{ book.shelf_location || '—' }}</td>
                            <td class="px-6 py-4 text-right">
                                <Badge variant="outline" 
                                    class="rounded-full px-2 py-0.5 text-[10px] font-bold"
                                    :class="getAvailabilityColor(book.available_copies, book.total_copies)"
                                >
                                    {{ book.available_copies }} Available
                                </Badge>
                            </td>
                        </tr>
                    </tbody>
                 </table>
            </div>

            <!-- Pagination -->
            <div v-if="books.links.length > 3" class="flex justify-center mt-4">
                <nav class="flex gap-1">
                    <template v-for="(link, k) in books.links" :key="k">
                        <Link v-if="link.url" 
                            :href="link.url" 
                            class="px-4 py-2 text-sm rounded-lg border transition-colors"
                            :class="link.active ? 'bg-purple-600 text-white border-purple-600 font-bold' : 'bg-card text-slate-600 hover:bg-slate-50'"
                            v-html="link.label"
                        />
                        <span v-else 
                            class="px-4 py-2 text-sm rounded-lg border bg-slate-100 text-slate-400 cursor-not-allowed opacity-50"
                            v-html="link.label"
                        />
                    </template>
                </nav>
            </div>
        </div>
    </AppLayout>
</template>
