<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import {
    Users, ArrowRight, GraduationCap, Calendar, User
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
        case 'active': return 'bg-emerald-500 text-white';
        case 'suspended': return 'bg-rose-500 text-white';
        case 'inactive':
        case 'withdrawn': return 'bg-slate-400 text-white';
        case 'graduated': return 'bg-blue-600 text-white';
        default: return 'bg-indigo-500 text-white';
    }
};
</script>

<template>
    <Head title="My Children" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 p-6 md:p-8 max-w-[1600px] mx-auto animate-in fade-in duration-500">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="h-12 w-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-200">
                        <Users class="h-6 w-6" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-foreground">My Children</h1>
                        <p class="text-sm text-muted-foreground">Select a child to access their academic workspace.</p>
                    </div>
                </div>
                <Badge variant="outline" class="rounded-xl text-xs font-bold px-3 py-1.5 border-blue-200 bg-blue-50 text-blue-700">
                    {{ children.length }} Learner{{ children.length !== 1 ? 's' : '' }}
                </Badge>
            </div>

            <!-- Children Grid -->
            <div v-if="children.length === 0" class="rounded-2xl border border-dashed p-16 text-center">
                <Users class="h-12 w-12 mx-auto mb-4 text-muted-foreground/20" />
                <h3 class="text-lg font-bold text-foreground">No Linked Learners</h3>
                <p class="text-sm text-muted-foreground mt-1">No learners are currently linked to your guardian account.</p>
            </div>

            <div v-else class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                <Link
                    v-for="child in children" :key="child.id"
                    :href="`/guardian/children/${child.id}`"
                    class="group block rounded-2xl border bg-card shadow-sm hover:shadow-xl transition-all duration-300 hover:border-blue-200 overflow-hidden"
                >
                    <!-- Card Header with gradient -->
                    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-5 relative overflow-hidden">
                        <div class="absolute -right-4 -top-4 h-20 w-20 bg-white/10 rounded-full"></div>
                        <div class="flex items-center gap-4 relative z-10">
                            <div class="h-16 w-16 rounded-2xl overflow-hidden shadow-xl bg-white/10 flex-shrink-0 ring-2 ring-white/30">
                                <img v-if="child.photo_url" :src="child.photo_url" class="h-full w-full object-cover" />
                                <div v-else class="h-full w-full flex items-center justify-center text-white text-2xl font-black">
                                    {{ child.name?.[0] || 'S' }}
                                </div>
                            </div>
                            <div class="text-white min-w-0">
                                <h3 class="text-lg font-bold truncate">{{ child.name }}</h3>
                                <p class="text-xs text-blue-100">{{ child.admission_number || 'No admission number' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mb-1">Grade</p>
                                <p class="font-bold text-foreground">{{ child.grade_name || '—' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mb-1">Class</p>
                                <p class="font-bold text-foreground">{{ child.class_name || '—' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mb-1">Stream</p>
                                <p class="font-bold text-foreground">{{ child.stream_name || '—' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mb-1">Age</p>
                                <p class="font-bold text-foreground">{{ child.age ? `${child.age} Yrs` : '—' }}</p>
                            </div>
                        </div>

                        <div v-if="child.class_teacher" class="flex items-center gap-3 bg-muted/30 rounded-xl px-4 py-3 border border-border/50">
                            <div class="h-8 w-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                                <User class="h-4 w-4" />
                            </div>
                            <div>
                                <p class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest">Class Teacher</p>
                                <p class="text-xs font-bold text-foreground">{{ child.class_teacher.name }}</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-2 border-t border-border/50">
                            <Badge :class="getStatusColor(child.status)" class="rounded-lg px-2.5 py-0.5 text-[9px] font-bold uppercase tracking-wider border-0">
                                {{ child.status }}
                            </Badge>
                            <span class="flex items-center gap-1.5 text-xs font-bold text-blue-600 group-hover:gap-2.5 transition-all">
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
