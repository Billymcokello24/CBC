<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import TailPanelHeader from '@/components/TailPanelHeader.vue';
import Toast from '@/components/Toast.vue';
import type { BreadcrumbItem } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { watchEffect } from 'vue';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

watchEffect(() => {
    const page = usePage();
    const themeColor = page.props.auth?.school?.theme_color;
    if (themeColor) {
        document.documentElement.style.setProperty('--primary', themeColor);
        document.documentElement.style.setProperty('--ring', themeColor);
        document.documentElement.style.setProperty('--sidebar-primary', themeColor);
        document.documentElement.style.setProperty('--sidebar-ring', themeColor);
    }
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar" class="bg-background min-h-screen flex flex-col">
            <TailPanelHeader />
            <main class="flex-1 p-4 sm:p-6 md:p-8 lg:p-10 max-w-[1600px] mx-auto w-full">
                <slot />
            </main>
        </AppContent>
        <Toast />
    </AppShell>
</template>
