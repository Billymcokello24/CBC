<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    User,
    Lock,
    ShieldCheck,
    Palette,
    Settings as SettingsIcon,
    ChevronRight,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editProfile } from '@/routes/profile';
import { show } from '@/routes/two-factor';
import { edit as editPassword } from '@/routes/user-password';
import type { NavItem } from '@/types';

const sidebarNavItems = [
    {
        title: 'Profile Info',
        href: editProfile(),
        icon: User,
        description: 'Manage your name and email',
    },
    {
        title: 'Security',
        href: editPassword(),
        icon: Lock,
        description: 'Update your login password',
    },
    {
        title: 'Two-Factor Auth',
        href: show(),
        icon: ShieldCheck,
        description: 'Secure your account access',
    },
    {
        title: 'Appearance',
        icon: Palette,
        href: editAppearance(),
        description: 'Theme and display options',
    },
];

const { isCurrentOrParentUrl } = useCurrentUrl();
</script>

<template>
    <div class="flex h-full flex-col bg-slate-50/30 dark:bg-zinc-950/30">
        <header
            class="border-b border-slate-100 bg-white/50 px-8 py-10 backdrop-blur-sm dark:border-zinc-900 dark:bg-zinc-950/50"
        >
            <div class="mb-2 flex items-center gap-4">
                <div
                    class="rounded-2xl bg-indigo-600 p-3 shadow-lg shadow-indigo-600/20"
                >
                    <SettingsIcon class="h-6 w-6 text-white" />
                </div>
                <div>
                    <h1
                        class="text-3xl font-bold tracking-tight text-slate-900 dark:text-zinc-100"
                    >
                        Profile Settings
                    </h1>
                    <p
                        class="text-sm font-medium text-slate-500 dark:text-zinc-400"
                    >
                        Personalize your experience and secure your account.
                    </p>
                </div>
            </div>
        </header>

        <main class="flex-1 p-8">
            <div
                class="mx-auto flex max-w-[1200px] flex-col gap-12 lg:flex-row"
            >
                <!-- Navigation Sidebar -->
                <aside class="shrink-0 lg:w-72">
                    <nav class="sticky top-8 space-y-2">
                        <Link
                            v-for="item in sidebarNavItems"
                            :key="toUrl(item.href)"
                            :href="item.href"
                            class="group flex items-center justify-between rounded-2xl border border-transparent p-3.5 transition-all duration-300"
                            :class="[
                                isCurrentOrParentUrl(item.href)
                                    ? 'translate-x-1 border-slate-100 bg-white shadow-xl shadow-indigo-500/5 dark:border-zinc-800 dark:bg-zinc-900'
                                    : 'text-slate-500 hover:bg-slate-100/80 hover:text-slate-900 dark:text-zinc-500 dark:hover:bg-zinc-900/50 dark:hover:text-zinc-200',
                            ]"
                        >
                            <div class="flex items-center gap-4">
                                <div
                                    class="rounded-xl p-2.5 transition-colors duration-300"
                                    :class="[
                                        isCurrentOrParentUrl(item.href)
                                            ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20'
                                            : 'bg-slate-100 group-hover:bg-slate-200 dark:bg-zinc-800 dark:group-hover:bg-zinc-700',
                                    ]"
                                >
                                    <component
                                        :is="item.icon"
                                        class="h-5 w-5"
                                    />
                                </div>
                                <div>
                                    <p
                                        class="text-sm font-bold transition-colors"
                                        :class="
                                            isCurrentOrParentUrl(item.href)
                                                ? 'text-slate-900 dark:text-white'
                                                : 'text-slate-600 dark:text-zinc-400'
                                        "
                                    >
                                        {{ item.title }}
                                    </p>
                                    <p
                                        class="text-xs font-bold tracking-tight uppercase opacity-60"
                                    >
                                        {{ item.description }}
                                    </p>
                                </div>
                            </div>
                            <ChevronRight
                                class="mr-1 h-4 w-4 opacity-0 transition-all group-hover:opacity-100"
                                :class="
                                    isCurrentOrParentUrl(item.href)
                                        ? 'text-indigo-500 opacity-40'
                                        : ''
                                "
                            />
                        </Link>
                    </nav>
                </aside>

                <Separator class="my-4 opacity-50 lg:hidden" />

                <!-- Content Area -->
                <div
                    class="max-w-2xl flex-1 animate-in duration-500 fade-in slide-in-from-right-4"
                >
                    <slot />
                </div>
            </div>
        </main>
    </div>
</template>
