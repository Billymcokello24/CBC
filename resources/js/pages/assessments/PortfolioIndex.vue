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
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div
                class="flex flex-col justify-between gap-4 md:flex-row md:items-center"
            >
                <div>
                    <h1
                        class="text-3xl font-bold tracking-tight text-slate-900 uppercase"
                    >
                        Learner Portfolios
                    </h1>
                    <p class="mt-1 font-bold text-slate-500">
                        Manage and view collection of student evidence and
                        achievements.
                    </p>
                </div>
            </div>

            <div
                class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-xl"
            >
                <div class="border-b border-slate-100 bg-slate-50/50 p-6">
                    <div
                        class="flex flex-col items-center justify-between gap-4 md:flex-row"
                    >
                        <div class="relative w-full md:w-96">
                            <Search
                                class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400"
                            />
                            <Input
                                v-model="searchQuery"
                                placeholder="Search by name or admission number..."
                                class="h-11 rounded-2xl border-slate-200 bg-white pl-10 focus:ring-indigo-500/20"
                            />
                        </div>
                        <div class="flex w-full items-center gap-2 md:w-auto">
                            <Button
                                variant="outline"
                                class="h-11 w-full gap-2 rounded-2xl border-slate-200 font-bold md:w-auto"
                            >
                                <Filter class="h-4 w-4" /> Filter By Class
                            </Button>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr class="bg-slate-50">
                                <th
                                    class="px-6 py-4 pl-12 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                >
                                    Learner
                                </th>
                                <th
                                    class="px-6 py-4 text-center text-xs font-bold tracking-tight text-slate-400 uppercase"
                                >
                                    Class / Grade
                                </th>
                                <th
                                    class="px-6 py-4 text-center text-xs font-bold tracking-tight text-slate-400 uppercase"
                                >
                                    Evidence Items
                                </th>
                                <th
                                    class="px-6 py-4 text-center text-xs font-bold tracking-tight text-slate-400 uppercase"
                                >
                                    Last Updated
                                </th>
                                <th
                                    class="px-6 py-4 pr-12 text-right text-xs font-bold tracking-tight text-slate-400 uppercase"
                                >
                                    Action
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="student in filteredStudents"
                                :key="student.id"
                                class="group cursor-pointer transition-all hover:bg-indigo-50/30"
                            >
                                <td class="px-6 py-5 pl-12">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-2xl border-2 border-white bg-slate-100 shadow-sm transition-colors group-hover:border-indigo-200"
                                        >
                                            <img
                                                v-if="student.photo_url"
                                                :src="student.photo_url"
                                                class="h-full w-full object-cover"
                                            />
                                            <User
                                                v-else
                                                class="h-6 w-6 text-slate-400"
                                            />
                                        </div>
                                        <div>
                                            <p
                                                class="font-bold tracking-tight text-slate-900 uppercase"
                                            >
                                                {{ student.first_name }}
                                                {{ student.last_name }}
                                            </p>
                                            <p
                                                class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                                            >
                                                {{ student.admission_number }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <div
                                        class="inline-flex items-center gap-1.5 rounded-full border border-indigo-100 bg-indigo-50 px-3 py-1.5 text-indigo-700"
                                    >
                                        <GraduationCap class="h-3.5 w-3.5" />
                                        <span
                                            class="text-xs font-bold tracking-wider uppercase"
                                            >{{
                                                student.current_class?.name ||
                                                'N/A'
                                            }}</span
                                        >
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <div
                                        class="inline-flex flex-col items-center"
                                    >
                                        <span
                                            class="text-sm font-bold text-slate-900"
                                            >{{
                                                student.portfolio_items_count ||
                                                0
                                            }}</span
                                        >
                                        <span
                                            class="mt-0.5 text-xs font-bold tracking-tighter whitespace-nowrap text-slate-400 uppercase"
                                            >Items Logged</span
                                        >
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span
                                        class="text-xs font-bold text-slate-500"
                                    >
                                        {{
                                            student.portfolio?.updated_at
                                                ? new Date(
                                                      student.portfolio
                                                          .updated_at,
                                                  ).toLocaleDateString()
                                                : 'Never'
                                        }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 pr-12 text-right">
                                    <Button
                                        as-child
                                        variant="ghost"
                                        class="h-10 w-10 rounded-xl p-0 transition-colors hover:bg-indigo-100 hover:text-indigo-600"
                                    >
                                        <Link
                                            :href="
                                                '/assessments/portfolio/' +
                                                student.id
                                            "
                                        >
                                            <ChevronRight class="h-5 w-5" />
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
