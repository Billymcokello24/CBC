<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import TailPanelHeader from '@/components/TailPanelHeader.vue';
import Toast from '@/components/Toast.vue';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar" class="bg-background min-h-screen flex flex-col">
            <TailPanelHeader />
            <main class="flex-1 p-4 md:p-8 lg:p-10">
                <!-- Breadcrumbs row -->
                <div v-if="breadcrumbs && breadcrumbs.length > 0" class="mb-6 flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">
                     <template v-for="(item, index) in breadcrumbs" :key="index">
                          <Link :href="item.href" class="hover:text-primary transition-colors">{{ item.title }}</Link>
                          <span v-if="index < breadcrumbs.length - 1" class="text-slate-300">/</span>
                     </template>
                </div>
                <slot />
            </main>
        </AppContent>
        <Toast />
    </AppShell>
</template>
