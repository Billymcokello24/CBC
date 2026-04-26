<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import {
    Users,
    ArrowRight,
    GraduationCap,
    Calendar,
    User,
} from 'lucide-vue-next';

const props = defineProps<{
    children: Array<{
        id: number;
        name: string;
        first_name: string;
        last_name: string;
        admission_number: string | null;
        gender: string | null;
        date_of_birth: string | null;
        age: number | null;
        status: string;
        photo_url: string | null;
        class_name: string | null;
        grade_name: string | null;
        stream_name: string | null;
        class_teacher: { name: string } | null;
    }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Parent Portal', href: '/guardian/portal' },
    { title: 'My Children', href: '/guardian/children' },
];

const getStatusColor = (status: string) => {
    switch (status?.toLowerCase()) {
        case 'active':
            return 'bg-emerald-500 text-white';
        case 'suspended':
            return 'bg-rose-500 text-white';
        case 'inactive':
        case 'withdrawn':
            return 'bg-slate-400 text-white';
        case 'graduated':
            return 'bg-blue-600 text-white';
        default:
            return 'bg-indigo-500 text-white';
    }
};
</script>

<template>
    <Head title="My Children" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex h-full max-w-[1600px] flex-1 animate-in flex-col gap-8 p-6 duration-500 fade-in md:p-8"
        >
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-200"
                    >
                        <Users class="h-6 w-6" />
                    </div>
                    <div>
                        <h1
                            class="text-2xl font-bold tracking-tight text-foreground"
                        >
                            My Children
                        </h1>
                        <p class="text-sm text-muted-foreground">
                            Select a child to access their academic workspace.
                        </p>
                    </div>
                </div>
                <Badge
                    variant="outline"
                    class="rounded-xl border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-700"
                >
                    {{ children.length }} Learner{{
                        children.length !== 1 ? 's' : ''
                    }}
                </Badge>
            </div>

            <!-- Children Grid -->
            <div
                v-if="children.length === 0"
                class="rounded-2xl border border-dashed p-16 text-center"
            >
                <Users
                    class="mx-auto mb-4 h-12 w-12 text-muted-foreground/20"
                />
                <h3 class="text-lg font-bold text-foreground">
                    No Linked Learners
                </h3>
                <p class="mt-1 text-sm text-muted-foreground">
                    No learners are currently linked to your guardian account.
                </p>
            </div>

            <div v-else class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                <Link
                    v-for="child in children"
                    :key="child.id"
                    :href="`/guardian/children/${child.id}`"
                    class="group block overflow-hidden rounded-2xl border bg-card shadow-sm transition-all duration-300 hover:border-blue-200 hover:shadow-xl"
                >
                    <!-- Card Header with gradient -->
                    <div
                        class="relative overflow-hidden bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-5"
                    >
                        <div
                            class="absolute -top-4 -right-4 h-20 w-20 rounded-full bg-white/10"
                        ></div>
                        <div class="relative z-10 flex items-center gap-4">
                            <div
                                class="h-16 w-16 flex-shrink-0 overflow-hidden rounded-2xl bg-white/10 shadow-xl ring-2 ring-white/30"
                            >
                                <img
                                    v-if="child.photo_url"
                                    :src="child.photo_url"
                                    class="h-full w-full object-cover"
                                />
                                <div
                                    v-else
                                    class="flex h-full w-full items-center justify-center text-2xl font-bold text-white"
                                >
                                    {{ child.name?.[0] || 'S' }}
                                </div>
                            </div>
                            <div class="min-w-0 text-white">
                                <h3 class="truncate text-lg font-bold">
                                    {{ child.name }}
                                </h3>
                                <p class="text-xs text-blue-100">
                                    {{
                                        child.admission_number ||
                                        'No admission number'
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="space-y-4 p-6">
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p
                                    class="mb-1 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                >
                                    Grade
                                </p>
                                <p class="font-bold text-foreground">
                                    {{ child.grade_name || '—' }}
                                </p>
                            </div>
                            <div>
                                <p
                                    class="mb-1 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                >
                                    Class
                                </p>
                                <p class="font-bold text-foreground">
                                    {{ child.class_name || '—' }}
                                </p>
                            </div>
                            <div>
                                <p
                                    class="mb-1 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                >
                                    Stream
                                </p>
                                <p class="font-bold text-foreground">
                                    {{ child.stream_name || '—' }}
                                </p>
                            </div>
                            <div>
                                <p
                                    class="mb-1 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                >
                                    Age
                                </p>
                                <p class="font-bold text-foreground">
                                    {{ child.age ? `${child.age} Yrs` : '—' }}
                                </p>
                            </div>
                        </div>

                        <div
                            v-if="child.class_teacher"
                            class="flex items-center gap-3 rounded-xl border border-border/50 bg-muted/30 px-4 py-3"
                        >
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-blue-600"
                            >
                                <User class="h-4 w-4" />
                            </div>
                            <div>
                                <p
                                    class="text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                >
                                    Class Teacher
                                </p>
                                <p class="text-xs font-bold text-foreground">
                                    {{ child.class_teacher.name }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="flex items-center justify-between border-t border-border/50 pt-2"
                        >
                            <Badge
                                :class="getStatusColor(child.status)"
                                class="rounded-lg border-0 px-2.5 py-0.5 text-xs font-bold tracking-wider uppercase"
                            >
                                {{ child.status }}
                            </Badge>
                            <span
                                class="flex items-center gap-1.5 text-xs font-bold text-blue-600 transition-all group-hover:gap-2.5"
                            >
                                Open Profile
                                <ArrowRight class="h-3.5 w-3.5" />
                            </span>
                        </div>
                    </div>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
