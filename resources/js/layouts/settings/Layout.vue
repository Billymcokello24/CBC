<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { 
    User, Lock, ShieldCheck, Palette, 
    Settings as SettingsIcon, ChevronRight
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
        description: 'Manage your name and email'
    },
    {
        title: 'Security',
        href: editPassword(),
        icon: Lock,
        description: 'Update your login password'
    },
    {
        title: 'Two-Factor Auth',
        href: show(),
        icon: ShieldCheck,
        description: 'Secure your account access'
    },
    {
        title: 'Appearance',
        icon: Palette,
        href: editAppearance(),
        description: 'Theme and display options'
    },
];

const { isCurrentOrParentUrl } = useCurrentUrl();
</script>

<template>
    <div class="flex flex-col h-full bg-slate-50/30 dark:bg-zinc-950/30">
        <header class="px-8 py-10 border-b border-slate-100 dark:border-zinc-900 bg-white/50 dark:bg-zinc-950/50 backdrop-blur-sm">
            <div class="flex items-center gap-4 mb-2">
                <div class="p-3 rounded-2xl bg-indigo-600 shadow-lg shadow-indigo-600/20">
                    <SettingsIcon class="h-6 w-6 text-white" />
                </div>
                <div>
                    <h1 class="text-3xl font-black tracking-tight text-slate-900 dark:text-zinc-100">Profile Settings</h1>
                    <p class="text-slate-500 dark:text-zinc-400 font-medium text-sm">Personalize your experience and secure your account.</p>
                </div>
            </div>
        </header>

        <main class="flex-1 p-8">
            <div class="max-w-[1200px] mx-auto flex flex-col lg:flex-row gap-12">
                <!-- Navigation Sidebar -->
                <aside class="lg:w-72 shrink-0">
                    <nav class="space-y-2 sticky top-8">
                        <Link 
                            v-for="item in sidebarNavItems" 
                            :key="toUrl(item.href)"
                            :href="item.href"
                            class="group flex items-center justify-between p-3.5 rounded-2xl transition-all duration-300 border border-transparent"
                            :class="[
                                isCurrentOrParentUrl(item.href) 
                                ? 'bg-white dark:bg-zinc-900 shadow-xl shadow-indigo-500/5 border-slate-100 dark:border-zinc-800 translate-x-1' 
                                : 'hover:bg-slate-100/80 dark:hover:bg-zinc-900/50 text-slate-500 dark:text-zinc-500 hover:text-slate-900 dark:hover:text-zinc-200'
                            ]"
                        >
                            <div class="flex items-center gap-4">
                                <div 
                                    class="p-2.5 rounded-xl transition-colors duration-300"
                                    :class="[
                                        isCurrentOrParentUrl(item.href)
                                        ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20'
                                        : 'bg-slate-100 dark:bg-zinc-800 group-hover:bg-slate-200 dark:group-hover:bg-zinc-700'
                                    ]"
                                >
                                    <component :is="item.icon" class="h-5 w-5" />
                                </div>
                                <div>
                                    <p 
                                        class="text-sm font-black transition-colors"
                                        :class="isCurrentOrParentUrl(item.href) ? 'text-slate-900 dark:text-white' : 'text-slate-600 dark:text-zinc-400'"
                                    >
                                        {{ item.title }}
                                    </p>
                                    <p class="text-[10px] uppercase font-bold tracking-widest opacity-60">{{ item.description }}</p>
                                </div>
                            </div>
                            <ChevronRight 
                                class="h-4 w-4 opacity-0 transition-all group-hover:opacity-100 mr-1" 
                                :class="isCurrentOrParentUrl(item.href) ? 'opacity-40 text-indigo-500' : ''"
                            />
                        </Link>
                    </nav>
                </aside>

                <Separator class="lg:hidden my-4 opacity-50" />

                <!-- Content Area -->
                <div class="flex-1 max-w-2xl animate-in fade-in slide-in-from-right-4 duration-500">
                    <slot />
                </div>
            </div>
        </main>
    </div>
</template>
