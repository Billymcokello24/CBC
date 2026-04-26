<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    Clock,
    Search,
    Filter,
    School,
    ArrowRight,
    Calendar,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { ref, computed } from 'vue';

const props = defineProps<{
    classes: any[];
}>();

const searchTerm = ref('');
const filteredClasses = computed(() => {
    if (!searchTerm.value) return props.classes;
    return props.classes.filter(
        (c) =>
            c.name.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
            c.grade_level?.name
                .toLowerCase()
                .includes(searchTerm.value.toLowerCase()),
    );
});

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Timetables', href: '/timetable' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="School Timetables" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Pulsar Header -->
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-500/10"
                    >
                        <Calendar class="h-6 w-6 text-violet-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight uppercase">
                            School Timetables
                        </h1>
                        <p class="text-muted-foreground">
                            Select a class to view its active lesson
                            distribution and assignments.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Search & Filter -->
            <div class="grid gap-4 md:grid-cols-4">
                <div class="relative md:col-span-3">
                    <Search
                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        v-model="searchTerm"
                        placeholder="Search class or grade level..."
                        class="h-11 pl-10"
                    />
                </div>
                <Button variant="outline" class="h-11">
                    <Filter class="mr-2 h-4 w-4" /> Filters
                </Button>
            </div>

            <!-- Classes Grid -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="cls in filteredClasses"
                    :key="cls.id"
                    class="group relative flex min-h-[200px] flex-col justify-between rounded-xl border bg-card p-6 shadow-sm transition-all duration-300 hover:shadow-md"
                >
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-50 text-violet-600 transition-colors group-hover:bg-violet-600 group-hover:text-white"
                            >
                                <School class="h-5 w-5" />
                            </div>
                            <span
                                class="text-xs font-medium tracking-tight text-gray-400 uppercase"
                            >
                                {{ cls.grade_level?.name }}
                            </span>
                        </div>

                        <div>
                            <h3
                                class="text-lg font-bold text-gray-900 transition-colors group-hover:text-violet-700"
                            >
                                {{ cls.name }}
                            </h3>
                            <p
                                class="mt-1 text-xs font-bold tracking-tight text-gray-400 uppercase"
                            >
                                Stream: {{ cls.stream?.name || 'N/A' }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="mt-6 flex items-center justify-between border-t border-gray-50 pt-4"
                    >
                        <div class="flex items-center gap-2">
                            <Clock class="h-3.5 w-3.5 text-gray-300" />
                            <span
                                class="text-xs font-bold tracking-tighter text-gray-400 uppercase"
                                >7 Lessons / Day</span
                            >
                        </div>
                        <Button
                            variant="ghost"
                            size="sm"
                            class="gap-2 text-xs font-bold tracking-tight text-violet-600 uppercase hover:text-violet-700"
                            as-child
                        >
                            <Link :href="route('timetable.class', cls.id)">
                                View Timetable <ArrowRight class="h-3 w-3" />
                            </Link>
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="filteredClasses.length === 0" class="py-20 text-center">
                <div
                    class="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-full bg-gray-50"
                >
                    <Search class="h-8 w-8 text-gray-200" />
                </div>
                <h3 class="text-lg font-bold text-gray-900 uppercase">
                    No classes matched
                </h3>
                <p
                    class="mx-auto mt-1 max-w-sm text-xs font-medium text-gray-400"
                >
                    Try refining your search terms.
                </p>
            </div>
        </div>
    </AppLayout>
</template>
