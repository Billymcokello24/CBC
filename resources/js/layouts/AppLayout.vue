<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import ImportTracker from '@/components/ImportTracker.vue';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
        <ImportTracker />
    </AppLayout>
</template>
