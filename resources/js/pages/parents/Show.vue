<script setup lang="ts">
import {
    User,
    Mail,
    Phone,
    Shield,
    ArrowLeft,
    Edit,
    GraduationCap,
    Clock,
    CheckCircle2,
    AlertTriangle,
    Home,
    Heart,
    Calendar,
    BadgeCheck,
} from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { format } from 'date-fns';

const props = defineProps<{
    parent: any;
}>();

const breadcrumbs = [
    { title: 'User Directory', href: '/staffs/directory' },
    { title: 'Parents', href: '/parents' },
    { title: 'Guardian Profile', href: '#' },
];

const formatDate = (date: string) => {
    if (!date) return 'N/A';
    return format(new Date(date), 'MMMM d, yyyy');
};
</script>

<template>
    <AppLayout title="Guardian Profile" :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-col bg-slate-50/30 p-6 lg:p-10">
            <!-- Top Navigation & Actions -->
            <div
                class="mb-10 flex flex-wrap items-center justify-between gap-6"
            >
                <div class="space-y-1">
                    <Link
                        href="/parents"
                        class="group inline-flex items-center gap-1.5 text-xs font-bold tracking-tight text-muted-foreground uppercase transition-colors hover:text-blue-600"
                    >
                        <ArrowLeft
                            class="h-3.5 w-3.5 transition-transform group-hover:-translate-x-1"
                        />
                        Back to Registry
                    </Link>
                    <h1
                        class="flex items-center gap-3 text-4xl font-bold tracking-tight text-slate-900 uppercase"
                    >
                        Guardian Profile
                    </h1>
                </div>
                <div class="flex items-center gap-3">
                    <Button
                        variant="outline"
                        class="h-12 rounded-2xl border-slate-200 bg-white px-6 font-bold shadow-sm transition-all hover:bg-slate-50"
                        as-child
                    >
                        <Link :href="`/parents/${parent.id}/edit`">
                            <Edit class="mr-2 h-4 w-4 text-blue-600" />
                            Edit Profile
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="grid gap-8 lg:grid-cols-12">
                <!-- Left: Identity Card -->
                <div class="space-y-8 lg:col-span-4">
                    <div
                        class="relative space-y-8 overflow-hidden rounded-xl border border-slate-200 bg-white p-8 shadow-xl shadow-slate-200/40"
                    >
                        <div
                            class="pointer-events-none absolute top-0 right-0 p-8 opacity-[0.03]"
                        >
                            <Heart class="h-40 w-40 text-rose-600" />
                        </div>

                        <div
                            class="flex flex-col items-center space-y-4 text-center"
                        >
                            <div
                                class="flex h-24 w-24 rotate-2 items-center justify-center rounded-xl border-4 border-white bg-rose-50 text-3xl font-bold text-rose-600 shadow-lg"
                            >
                                {{ parent.first_name[0]
                                }}{{ parent.last_name[0] }}
                            </div>
                            <div class="space-y-1">
                                <h2
                                    class="text-2xl font-bold tracking-tight text-slate-900"
                                >
                                    {{ parent.first_name }}
                                    {{ parent.last_name }}
                                </h2>
                                <Badge
                                    variant="secondary"
                                    class="border-rose-100/50 bg-rose-50 px-3 py-1 text-xs font-bold tracking-tight text-rose-600 uppercase"
                                >
                                    {{ parent.relationship_type || 'Guardian' }}
                                </Badge>
                            </div>
                        </div>

                        <div class="space-y-6 border-t border-slate-100 pt-6">
                            <div class="group flex items-center gap-4">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-50 text-slate-400 transition-colors group-hover:bg-blue-50 group-hover:text-blue-600"
                                >
                                    <Mail class="h-5 w-5" />
                                </div>
                                <div class="overflow-hidden">
                                    <p
                                        class="mb-0.5 pl-0.5 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                    >
                                        Primary Email
                                    </p>
                                    <p
                                        class="truncate text-sm font-bold text-slate-900"
                                    >
                                        {{ parent.user?.email || parent.email }}
                                    </p>
                                </div>
                            </div>
                            <div class="group flex items-center gap-4">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-50 text-slate-400 transition-colors group-hover:bg-blue-50 group-hover:text-blue-600"
                                >
                                    <Phone class="h-5 w-5" />
                                </div>
                                <div>
                                    <p
                                        class="mb-0.5 pl-0.5 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                    >
                                        Phone Contact
                                    </p>
                                    <p class="text-sm font-bold text-slate-900">
                                        {{ parent.phone }}
                                    </p>
                                </div>
                            </div>
                            <div class="group flex items-center gap-4">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-50 text-slate-400 transition-colors group-hover:bg-blue-50 group-hover:text-blue-600"
                                >
                                    <BadgeCheck class="h-5 w-5" />
                                </div>
                                <div>
                                    <p
                                        class="mb-0.5 pl-0.5 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                    >
                                        ID Number
                                    </p>
                                    <p class="text-sm font-bold text-slate-900">
                                        {{ parent.id_number || 'Not Provided' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div
                            class="space-y-4 rounded-3xl bg-slate-900 p-5 text-white shadow-xl shadow-slate-900/20"
                        >
                            <div class="flex items-center justify-between">
                                <p
                                    class="text-xs font-medium tracking-tight text-muted-foreground text-rose-400 uppercase"
                                >
                                    Account Status
                                </p>
                                <div
                                    class="h-2 w-2 animate-pulse rounded-full bg-emerald-500"
                                ></div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10"
                                >
                                    <Clock class="h-5 w-5 text-white/60" />
                                </div>
                                <div>
                                    <p
                                        class="text-xs font-bold tracking-tight text-white/40 uppercase"
                                    >
                                        Registered Since
                                    </p>
                                    <p class="text-xs font-bold text-white">
                                        {{ formatDate(parent.created_at) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Content Area -->
                <div class="space-y-8 lg:col-span-8">
                    <!-- Dependents Listing -->
                    <div
                        class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl shadow-slate-200/40 duration-500"
                    >
                        <div
                            class="flex items-center justify-between border-b border-slate-100 bg-slate-50/30 px-10 py-7"
                        >
                            <h2
                                class="flex items-center gap-3 text-lg font-bold text-slate-900"
                            >
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-200"
                                >
                                    <GraduationCap class="h-5 w-5" />
                                </div>
                                Linked Dependents
                            </h2>
                            <Badge
                                class="border-blue-100 bg-blue-50 px-3 py-1 font-bold text-blue-600"
                            >
                                {{ parent.students?.length || 0 }} Learners
                            </Badge>
                        </div>
                        <div class="p-10">
                            <div
                                v-if="parent.students?.length > 0"
                                class="grid gap-6 md:grid-cols-2"
                            >
                                <Link
                                    v-for="student in parent.students"
                                    :key="student.id"
                                    :href="`/students/${student.id}`"
                                    class="group flex items-center gap-5 rounded-xl border-2 border-slate-50 bg-slate-50/40 p-6 shadow-sm transition-all hover:border-blue-200 hover:bg-white hover:shadow-xl hover:shadow-blue-100/50"
                                >
                                    <div
                                        class="flex h-14 w-14 items-center justify-center rounded-2xl border-2 border-slate-100 bg-white text-lg font-bold text-slate-400 shadow-sm transition-all group-hover:border-blue-600 group-hover:bg-blue-600 group-hover:text-white"
                                    >
                                        {{ student.first_name[0]
                                        }}{{ student.last_name[0] }}
                                    </div>
                                    <div class="space-y-1">
                                        <h4
                                            class="font-bold text-slate-900 transition-colors group-hover:text-blue-600"
                                        >
                                            {{ student.first_name }}
                                            {{ student.last_name }}
                                        </h4>
                                        <p
                                            class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                                        >
                                            {{ student.admission_number }}
                                        </p>
                                    </div>
                                    <div
                                        class="ml-auto opacity-0 transition-opacity group-hover:opacity-100"
                                    >
                                        <div
                                            class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-50 text-blue-600"
                                        >
                                            <BadgeCheck class="h-4 w-4" />
                                        </div>
                                    </div>
                                </Link>
                            </div>
                            <div
                                v-else
                                class="flex flex-col items-center justify-center space-y-4 py-20 text-center"
                            >
                                <div
                                    class="flex h-20 w-20 items-center justify-center rounded-xl bg-slate-50 text-slate-200 shadow-inner"
                                >
                                    <GraduationCap class="h-10 w-10" />
                                </div>
                                <div class="space-y-1">
                                    <h3
                                        class="text-xl font-bold text-slate-900"
                                    >
                                        No Linked Dependents
                                    </h3>
                                    <p
                                        class="max-w-xs text-sm font-medium text-slate-400"
                                    >
                                        This guardian account currently has no
                                        learner connections registered.
                                    </p>
                                </div>
                                <Button
                                    class="h-11 rounded-xl bg-slate-900"
                                    as-child
                                >
                                    <Link :href="`/parents/${parent.id}/edit`"
                                        >Link Learner</Link
                                    >
                                </Button>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Details / History -->
                    <div class="grid gap-8 md:grid-cols-2">
                        <div
                            class="group relative overflow-hidden rounded-xl bg-slate-900 p-8 text-white"
                        >
                            <div
                                class="absolute -right-4 -bottom-4 opacity-5 transition-transform duration-1000 group-hover:scale-110"
                            >
                                <Shield class="h-32 w-32" />
                            </div>
                            <h3
                                class="mb-6 text-xs font-medium tracking-tight text-blue-400 text-muted-foreground uppercase"
                            >
                                Security Access
                            </h3>
                            <div class="relative space-y-6">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 text-white/40"
                                    >
                                        <Calendar class="h-5 w-5" />
                                    </div>
                                    <div>
                                        <p
                                            class="mb-0.5 pl-0.5 text-xs font-bold tracking-tight text-white/40 uppercase"
                                        >
                                            Last Portal Activity
                                        </p>
                                        <p class="text-sm font-bold text-white">
                                            Never Logged In
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/20 text-emerald-400"
                                    >
                                        <CheckCircle2 class="h-5 w-5" />
                                    </div>
                                    <div>
                                        <p
                                            class="mb-0.5 pl-0.5 text-xs font-bold tracking-tight text-white/40 uppercase"
                                        >
                                            Account Verification
                                        </p>
                                        <p class="text-sm font-bold text-white">
                                            Active Profile
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="group relative overflow-hidden rounded-xl border border-slate-200 bg-white p-8 shadow-xl shadow-slate-200/40"
                        >
                            <div
                                class="absolute -right-4 -bottom-4 opacity-5 transition-transform duration-1000 group-hover:scale-110"
                            >
                                <Home class="h-32 w-32 text-indigo-600" />
                            </div>
                            <h3
                                class="mb-6 text-xs font-medium tracking-tight text-muted-foreground text-slate-400 uppercase"
                            >
                                Institutional Info
                            </h3>
                            <div class="relative space-y-6">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600"
                                    >
                                        <Home class="h-5 w-5" />
                                    </div>
                                    <div>
                                        <p
                                            class="mb-0.5 pl-0.5 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                        >
                                            School Code
                                        </p>
                                        <p
                                            class="text-sm font-bold text-slate-900"
                                        >
                                            CBC-INST-{{ parent.school_id }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-50 text-slate-400"
                                    >
                                        <Info class="h-5 w-5" />
                                    </div>
                                    <div>
                                        <p
                                            class="mb-0.5 pl-0.5 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                        >
                                            Reference ID
                                        </p>
                                        <p
                                            class="text-sm font-bold text-slate-900 uppercase"
                                        >
                                            GDN-{{
                                                parent.id
                                                    .toString()
                                                    .padStart(4, '0')
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
