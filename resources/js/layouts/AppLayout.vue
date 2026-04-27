<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import ImportTracker from '@/components/ImportTracker.vue';
import type { BreadcrumbItem } from '@/types';

import { usePage } from '@inertiajs/vue3';
import { watchEffect } from 'vue';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage<any>();

watchEffect(() => {
    const school = page.props.auth?.school;
    if (school) {
        // Update Title - Prepend school name if we have a specific page title, or just use school name
        const schoolName = school.name;
        if (schoolName) {
            // We use a small delay to let Inertia's <Head> finish its work if necessary
            // but usually document.title update here is sufficient.
            document.title = schoolName;
        }

        // Update Favicon
        if (school.logo) {
            const favicon = document.getElementById('favicon') as HTMLLinkElement;
            const faviconSvg = document.getElementById('favicon-svg') as HTMLLinkElement;
            const appleIcon = document.getElementById('apple-touch-icon') as HTMLLinkElement;

            if (favicon) favicon.href = school.logo;
            if (faviconSvg) faviconSvg.href = school.logo;
            if (appleIcon) appleIcon.href = school.logo;
        }
    }
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
        <ImportTracker />
    </AppLayout>
</template>
