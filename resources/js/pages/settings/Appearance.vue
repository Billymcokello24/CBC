<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Palette } from 'lucide-vue-next';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/appearance';
import type { BreadcrumbItem } from '@/types';

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Settings', href: '/settings/profile' },
    { title: 'Appearance', href: edit() },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Appearance Settings" />

        <SettingsLayout>
            <Card
                class="overflow-hidden rounded-xl border-slate-200 shadow-xl shadow-slate-200/50 dark:border-zinc-800 dark:shadow-none"
            >
                <CardHeader
                    class="border-b border-slate-100 bg-slate-50/50 p-8 dark:border-zinc-800 dark:bg-zinc-900/50"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="rounded-2xl bg-indigo-100 p-3 dark:bg-indigo-900/30"
                        >
                            <Palette
                                class="h-6 w-6 text-indigo-600 dark:text-indigo-400"
                            />
                        </div>
                        <div>
                            <CardTitle
                                class="text-xl font-bold text-slate-900 dark:text-zinc-100"
                                >Interface Theme</CardTitle
                            >
                            <CardDescription class="text-sm font-medium"
                                >Choose between light and dark modes to suit
                                your preference.</CardDescription
                            >
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="p-8">
                    <AppearanceTabs />
                </CardContent>
            </Card>
        </SettingsLayout>
    </AppLayout>
</template>
