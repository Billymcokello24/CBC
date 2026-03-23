<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItem } from '@/types';

const props = withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);
</script>

<template>
    <div
        v-if="$page.props.auth.impersonating?.active"
        class="flex w-full items-center justify-between bg-violet-600 px-6 py-2 text-xs font-bold text-white shadow-sm"
    >
        <div class="flex items-center gap-2">
            <span class="flex h-2 w-2 rounded-full bg-white animate-pulse"></span>
            <span>Viewing as: {{ $page.props.auth.impersonating.school_name }}</span>
        </div>
        <Link
            :href="route('super-admin.stop-impersonating')"
            method="post"
            as="button"
            class="rounded bg-white/20 px-2 py-1 transition-colors hover:bg-white/30"
        >
            Back to Global Dashboard
        </Link>
    </div>
    <header
        class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>
    </header>
</template>
