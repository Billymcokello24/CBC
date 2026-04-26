<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ClipboardList,
    Plus,
    Search,
    Filter,
    MoreHorizontal,
    Eye,
    Edit,
    Trash2,
    User,
    Activity,
    AlertCircle,
    Heart,
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
    records: any; // Paginator
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Health', href: '/health' },
    { title: 'Health Records', href: '/health/records' },
];

const searchQuery = ref('');
</script>

<template>
    <Head title="Student Health Records" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="font-pulsar flex h-full flex-1 flex-col gap-6 p-6">
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-rose-500/10"
                    >
                        <ClipboardList class="h-6 w-6 text-rose-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">
                            Student Health Records
                        </h1>
                        <p class="text-muted-foreground">
                            Comprehensive student medical backgrounds and
                            wellness data
                        </p>
                    </div>
                </div>
                <Button as-child class="bg-rose-600 hover:bg-rose-700">
                    <Link href="/health/records/create">
                        <Plus class="mr-2 h-4 w-4" />New Record
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
                        placeholder="Search student or condition..."
                        class="h-10 pl-9"
                    />
                </div>
                <Button variant="outline" size="sm" class="h-10">
                    <Filter class="mr-2 h-4 w-4" />Alerts Only
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
                                Student
                            </th>
                            <th
                                class="px-6 py-4 text-xs font-bold tracking-wider text-slate-500 uppercase"
                            >
                                Blood/Genotype
                            </th>
                            <th
                                class="px-6 py-4 text-xs font-bold tracking-wider text-slate-500 uppercase"
                            >
                                Allergies
                            </th>
                            <th
                                class="px-6 py-4 text-xs font-bold tracking-wider text-slate-500 uppercase"
                            >
                                Chronic Conditions
                            </th>
                            <th
                                class="px-6 py-4 text-xs font-bold tracking-wider text-slate-500 uppercase"
                            >
                                Last Exam
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
                            v-for="record in records.data"
                            :key="record.id"
                            class="group transition-colors hover:bg-slate-50/50"
                        >
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-400"
                                    >
                                        <User class="h-4 w-4" />
                                    </div>
                                    <div class="font-bold text-slate-900">
                                        {{ record.student?.first_name }}
                                        {{ record.student?.last_name }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="font-bold text-rose-600">{{
                                        record.blood_group || '—'
                                    }}</span>
                                    <span
                                        class="text-xs font-bold tracking-tight text-slate-400"
                                        >{{ record.genotype || '—' }}</span
                                    >
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div
                                    v-if="record.allergies"
                                    class="flex flex-wrap gap-1"
                                >
                                    <Badge
                                        variant="outline"
                                        class="border-rose-100 bg-rose-50 text-xs font-bold text-rose-700 uppercase"
                                        >{{ record.allergies }}</Badge
                                    >
                                </div>
                                <span v-else class="text-slate-300">None</span>
                            </td>
                            <td class="px-6 py-4">
                                <div
                                    v-if="record.chronic_conditions"
                                    class="flex flex-wrap gap-1"
                                >
                                    <Badge
                                        variant="outline"
                                        class="border-amber-100 bg-amber-50 text-xs font-bold text-amber-700 uppercase"
                                        >{{ record.chronic_conditions }}</Badge
                                    >
                                </div>
                                <span v-else class="text-slate-300">None</span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500">
                                {{
                                    record.last_physical_exam
                                        ? new Date(
                                              record.last_physical_exam,
                                          ).toLocaleDateString()
                                        : '—'
                                }}
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
                                        class="w-40"
                                    >
                                        <DropdownMenuItem
                                            ><Eye class="mr-2 h-4 w-4" />View
                                            Full Profile</DropdownMenuItem
                                        >
                                        <DropdownMenuItem
                                            ><Edit class="mr-2 h-4 w-4" />Update
                                            Record</DropdownMenuItem
                                        >
                                        <DropdownMenuItem
                                            ><Heart
                                                class="mr-2 h-4 w-4"
                                            />Vaccination Log</DropdownMenuItem
                                        >
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </td>
                        </tr>
                        <tr v-if="records.data.length === 0">
                            <td
                                colspan="6"
                                class="px-6 py-12 text-center text-muted-foreground"
                            >
                                No health records initialized for current term.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="records.links.length > 3"
                class="mt-4 flex justify-center"
            >
                <nav class="flex gap-1">
                    <template v-for="(link, k) in records.links" :key="k">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="rounded-lg border px-4 py-2 text-sm transition-colors"
                            :class="
                                link.active
                                    ? 'border-rose-600 bg-rose-600 font-bold text-white'
                                    : 'bg-card text-slate-600 hover:bg-slate-50'
                            "
                            v-html="link.label"
                        />
                        <span
                            v-else
                            class="cursor-not-allowed rounded-lg border bg-slate-100 px-4 py-2 text-sm text-slate-400 opacity-50"
                            v-html="link.label"
                        />
                    </template>
                </nav>
            </div>
        </div>
    </AppLayout>
</template>
