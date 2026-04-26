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
        document.documentElement.style.setProperty(
            '--sidebar-primary',
            themeColor,
        );
        document.documentElement.style.setProperty(
            '--sidebar-ring',
            themeColor,
        );
    }
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent
            variant="sidebar"
            class="flex min-h-screen flex-col bg-background"
        >
            <TailPanelHeader />
            <main class="flex-1 p-2 sm:p-4 md:p-8 lg:p-10">
                <!-- Breadcrumbs row -->
                <div
                    v-if="breadcrumbs && breadcrumbs.length > 0"
                    class="mb-3 flex items-center gap-1 text-xs font-medium tracking-tight text-muted-foreground/60 uppercase sm:mb-6 sm:gap-2 sm:text-xs"
                >
                    <template v-for="(item, index) in breadcrumbs" :key="index">
                        <Link
                            :href="item.href"
                            class="transition-colors hover:text-primary"
                            >{{ item.title }}</Link
                        >
                        <span
                            v-if="index < breadcrumbs.length - 1"
                            class="text-slate-300"
                            >/</span
                        >
                    </template>
                </div>
                <slot />
            </main>
        </AppContent>
        <Toast />
    </AppShell>
</template>
