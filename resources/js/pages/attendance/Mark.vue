<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    Users,
    Calendar,
    ChevronRight,
    UserCheck,
    ArrowRight,
    Search,
    Filter,
    Clock,
    GraduationCap,
    School,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { ref, computed } from 'vue';

const props = defineProps<{
    classes: any[];
}>();

const searchTerm = ref('');
const filteredClasses = computed(() => {
    if (!props.classes) return [];
    if (!searchTerm.value) return props.classes;
    return props.classes.filter(
        (c) =>
            c.name?.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
            c.grade_level?.name
                ?.toLowerCase()
                .includes(searchTerm.value.toLowerCase()),
    );
});

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Attendance', href: '/attendance' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Mark Attendance" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Pulsar Header -->
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/10"
                    >
                        <UserCheck class="h-6 w-6 text-emerald-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight uppercase">
                            Attendance Tracking
                        </h1>
                        <p class="text-muted-foreground">
                            Select a class to record student presence for
                            today's sessions.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <div
                        class="flex items-center gap-2 rounded-lg border bg-emerald-50 px-3 py-1.5 text-xs font-bold tracking-tight text-emerald-700 uppercase"
                    >
                        <Clock class="h-3.5 w-3.5" />
                        {{
                            new Date().toLocaleDateString('en-US', {
                                month: 'short',
                                day: 'numeric',
                            })
                        }}
                    </div>
                </div>
            </div>

            <!-- Pulsar Search & Stats Grid -->
            <div class="grid gap-4 md:grid-cols-4">
                <div class="relative md:col-span-3">
                    <Search
                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        v-model="searchTerm"
                        placeholder="Search class or grade..."
                        class="h-11 pl-10"
                    />
                </div>
                <div
                    class="flex items-center justify-between rounded-xl border bg-linear-to-br from-emerald-50 to-emerald-100/50 px-4 py-2"
                >
                    <span
                        class="text-xs leading-tight font-bold tracking-tight text-emerald-800 uppercase"
                        >Total<br />Classes</span
                    >
                    <span class="text-2xl font-bold text-emerald-600">{{
                        classes?.length || 0
                    }}</span>
                </div>
            </div>

            <!-- Classes Grid -->
            <div
                class="mt-2 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
            >
                <div
                    v-for="cls in filteredClasses"
                    :key="cls.id"
                    class="group relative flex min-h-[220px] flex-col justify-between rounded-xl border bg-card p-6 shadow-sm transition-all duration-300 hover:shadow-md"
                >
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 transition-colors group-hover:bg-emerald-600 group-hover:text-white"
                            >
                                <School class="h-5 w-5" />
                            </div>
                            <Badge
                                variant="outline"
                                class="py-0 text-xs font-medium tracking-tight uppercase"
                            >
                                {{ cls.grade_level?.name }} ·
                                {{ cls.stream?.name }}
                            </Badge>
                        </div>

                        <div>
                            <h3
                                class="text-lg font-bold text-gray-900 transition-colors group-hover:text-emerald-700"
                            >
                                {{ cls.name }}
                            </h3>
                            <p
                                class="mt-1 text-xs font-bold tracking-tight text-gray-400 uppercase"
                            >
                                Class Code: {{ cls.code || 'N/A' }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 space-y-4">
                        <div
                            class="flex items-center gap-4 border-t border-gray-50 py-3"
                        >
                            <div class="flex-1 text-center">
                                <p class="text-sm font-bold text-gray-900">
                                    42
                                </p>
                                <p
                                    class="text-xs font-bold tracking-tight text-gray-400 uppercase"
                                >
                                    Students
                                </p>
                            </div>
                            <div class="h-6 w-px bg-gray-100"></div>
                            <div class="flex-1 text-center">
                                <p class="text-sm font-bold text-emerald-600">
                                    92%
                                </p>
                                <p
                                    class="text-xs font-bold tracking-tight text-gray-400 uppercase"
                                >
                                    Attendance
                                </p>
                            </div>
                        </div>

                        <Button
                            class="h-10 w-full rounded-lg bg-violet-600 text-xs font-bold text-white uppercase hover:bg-violet-700"
                            as-child
                        >
                            <Link :href="`/attendance/class/${cls.id}`">
                                Mark Attendance
                                <ArrowRight class="ml-2 h-3.5 w-3.5" />
                            </Link>
                        </Button>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-if="filteredClasses.length === 0"
                    class="col-span-full py-20 text-center"
                >
                    <div
                        class="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-full bg-gray-50"
                    >
                        <Search class="h-8 w-8 text-gray-200" />
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 uppercase">
                        No classes found
                    </h3>
                    <p
                        class="mx-auto mt-1 max-w-sm text-xs font-medium text-gray-400"
                    >
                        Try adjusting your search terms or filters.
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
