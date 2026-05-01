<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Folder,
    Search,
    ChevronRight,
    User,
    GraduationCap,
    Plus,
    Filter,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    students: {
        data: Array<any>;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Assessments', href: '/assessments' },
    { title: 'Portfolio Management', href: '#' },
];

const searchQuery = ref('');

const filteredStudents = computed(() => {
    return props.students.data.filter(
        (student) =>
            (student.first_name + ' ' + student.last_name)
                .toLowerCase()
                .includes(searchQuery.value.toLowerCase()) ||
            student.admission_number
                .toLowerCase()
                .includes(searchQuery.value.toLowerCase()),
    );
});
</script>

<template>
    <Head title="Learner Portfolios" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex h-full max-w-[1600px] flex-1 animate-in flex-col space-y-8 p-4 pb-20 duration-700 fade-in slide-in-from-bottom-4 sm:p-6 sm:pb-32 md:p-8"
        >
            <div
                class="flex flex-col gap-6 px-1 md:flex-row md:items-center md:justify-between"
            >
                <div class="space-y-1">
                    <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">Learner Portfolios</h1>
                    <p class="text-xs text-muted-foreground">Manage and view collection of student evidence and achievements.</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                <div class="flex h-12 items-center justify-between border-b border-border/50 bg-muted/5 px-6">
                    <div class="flex items-center gap-2">
                        <Folder class="h-4 w-4 text-primary" />
                        <span class="text-xs font-semibold text-foreground tracking-tight">Portfolio Registry</span>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                        <div class="space-y-2">
                            <label class="text-xs font-medium text-muted-foreground">Search Learner</label>
                            <div class="relative">
                                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/40" />
                                <Input
                                    v-model="searchQuery"
                                    placeholder="Name or admission number..."
                                    class="h-10 rounded-lg border-border bg-muted/10 pl-10 pr-4 text-sm focus:bg-background"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm transition-all">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-border/50 bg-muted/5 text-[11px] font-bold tracking-wider text-muted-foreground uppercase">
                                <th class="px-6 py-4 pl-12">Learner</th>
                                <th class="px-6 py-4 text-center">Class / Grade</th>
                                <th class="px-6 py-4 text-center">Evidence Items</th>
                                <th class="px-6 py-4 text-center">Last Updated</th>
                                <th class="px-6 py-4 pr-12 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/50">
                            <tr
                                v-for="student in filteredStudents"
                                :key="student.id"
                                class="group transition-all hover:bg-muted/20"
                            >
                                <td class="px-6 py-4 pl-12">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 shrink-0 overflow-hidden rounded-lg border border-border bg-muted shadow-sm transition-transform group-hover:scale-105">
                                            <img v-if="student.photo_url" :src="student.photo_url" class="h-full w-full object-cover" />
                                            <div v-else class="flex h-full w-full items-center justify-center bg-primary/10 text-xs font-semibold text-primary">{{ student.first_name.charAt(0) }}</div>
                                        </div>
                                        <div class="space-y-0.5">
                                            <p class="text-sm font-semibold tracking-tight text-foreground group-hover:text-primary transition-colors">{{ student.first_name }} {{ student.last_name }}</p>
                                            <p class="text-[10px] font-medium text-muted-foreground uppercase">{{ student.admission_number || '--' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <Badge variant="outline" class="rounded-md border-primary/10 bg-primary/5 px-2 py-0.5 text-[10px] font-semibold text-primary">
                                        <GraduationCap class="mr-1.5 h-3 w-3" />
                                        {{ student.current_class?.name || 'Unassigned' }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="inline-flex flex-col items-center">
                                        <span class="text-sm font-bold text-foreground">{{ student.portfolio_items_count || 0 }}</span>
                                        <span class="text-[10px] font-medium text-muted-foreground uppercase">Items Logged</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-xs font-semibold text-muted-foreground">
                                        {{ student.portfolio?.updated_at ? new Date(student.portfolio.updated_at).toLocaleDateString() : 'Never' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 pr-12 text-right">
                                    <Button as-child variant="ghost" size="icon" class="h-8 w-8 rounded-lg hover:bg-primary/10 hover:text-primary">
                                        <Link :href="'/assessments/portfolio/' + student.id">
                                            <ChevronRight class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
