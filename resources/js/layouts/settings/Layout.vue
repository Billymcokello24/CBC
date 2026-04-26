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
    <div class="mx-auto max-w-[1600px] animate-in space-y-6 p-4 pb-10 duration-700 fade-in slide-in-from-bottom-4 sm:space-y-8 sm:p-6 sm:pb-20 md:p-8">
        <header
            class="flex flex-col gap-4 border-b border-sidebar-border pb-8 md:flex-row md:items-end md:justify-between px-1"
        >
            <div class="mb-2 flex items-center gap-4">
                <div
                    class="rounded-2xl border border-indigo-100 bg-indigo-50 p-3 shadow-lg shadow-indigo-600/5 dark:border-indigo-900/50 dark:bg-indigo-900/30"
                >
                    <SettingsIcon class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                </div>
                <div class="flex flex-col gap-1">
                    <h1
                        class="text-2xl leading-tight font-bold tracking-tight text-foreground sm:text-3xl"
                    >
                        Profile Settings
                    </h1>
                    <p
                        class="text-sm text-muted-foreground sm:text-sm"
                    >
                        Personalize your experience and secure your account.
                    </p>
                </div>
            </div>
        </header>

        <main class="flex-1">
            <div
                class="mx-auto flex flex-col gap-12 lg:flex-row px-1"
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
