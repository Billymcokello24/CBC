<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    Search,
    Bell,
    Moon,
    Sun,
    Menu,
    X,
    ChevronDown,
    Settings,
    User,
    LogOut,
    ShieldCheck,
} from 'lucide-vue-next';
import { SidebarTrigger } from '@/components/ui/sidebar';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

const { props } = usePage();
const user = props.auth.user;

const isDark = ref(false);

const toggleDarkMode = () => {
    isDark.value = !isDark.value;
    document.documentElement.classList.toggle('dark', isDark.value);
    localStorage.setItem('appearance', isDark.value ? 'dark' : 'light');
};

onMounted(() => {
    const saved = localStorage.getItem('appearance');
    isDark.value =
        saved === 'dark' ||
        (!saved && window.matchMedia('(prefers-color-scheme: dark)').matches);
    document.documentElement.classList.toggle('dark', isDark.value);
});

const searchFocused = ref(false);
</script>

<template>
    <header
        class="sticky top-0 z-50 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60"
    >
        <div
            class="flex h-12 items-center gap-1 px-2 sm:h-14 sm:gap-2 sm:px-4 md:h-16 md:px-8"
        >
            <div class="flex shrink-0 items-center gap-1 sm:gap-4">
                <SidebarTrigger
                    class="-ml-0.5 h-8 w-8 p-0 hover:bg-slate-100 sm:h-9 sm:w-9 dark:hover:bg-slate-800"
                />
            </div>

            <!-- Search Area — hidden on very small screens -->
            <div
                class="hidden flex-1 items-center justify-center px-2 sm:flex md:px-20 lg:px-40"
            >
                <div class="group relative w-full max-w-2xl">
                    <Search
                        class="absolute top-1/2 left-3 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground transition-colors group-focus-within:text-primary sm:h-4 sm:w-4"
                    />
                    <Input
                        placeholder="Search... (Ctrl+K)"
                        class="h-8 w-full rounded-xl border-border bg-muted/30 pr-4 pl-9 text-xs transition-all focus:bg-background sm:h-9 sm:pr-12 sm:pl-10 sm:text-sm md:h-10"
                        @focus="searchFocused = true"
                        @blur="searchFocused = false"
                    />
                    <div
                        class="absolute top-1/2 right-3 flex -translate-y-1/2 items-center gap-1"
                    >
                        <kbd
                            class="pointer-events-none hidden h-5 items-center gap-1 rounded border bg-muted px-1.5 font-mono text-xs font-medium opacity-100 select-none md:flex"
                        >
                            <span class="text-xs">⌘</span>K
                        </kbd>
                    </div>
                </div>
            </div>

            <!-- Spacer on very small screens where search is hidden -->
            <div class="flex-1 sm:hidden"></div>

            <!-- Actions Area -->
            <div class="flex shrink-0 items-center gap-1 sm:gap-2 md:gap-4">
                <!-- Search icon for very small screens -->
                <Button
                    variant="ghost"
                    size="icon"
                    class="h-8 w-8 rounded-lg p-0 hover:bg-slate-100 sm:hidden dark:hover:bg-slate-800"
                >
                    <Search
                        class="h-4 w-4 text-slate-600 dark:text-slate-400"
                    />
                </Button>

                <Button
                    variant="ghost"
                    size="icon"
                    class="h-8 w-8 rounded-lg p-0 hover:bg-slate-100 sm:h-9 sm:w-9 sm:rounded-xl md:h-10 md:w-10 dark:hover:bg-slate-800"
                    @click="toggleDarkMode"
                >
                    <Sun
                        v-if="isDark"
                        class="h-4 w-4 text-amber-500 sm:h-5 sm:w-5"
                    />
                    <Moon v-else class="h-4 w-4 text-slate-700 sm:h-5 sm:w-5" />
                </Button>

                <Button
                    variant="ghost"
                    size="icon"
                    class="relative h-8 w-8 rounded-lg p-0 hover:bg-slate-100 sm:h-9 sm:w-9 sm:rounded-xl md:h-10 md:w-10 dark:hover:bg-slate-800"
                >
                    <Bell
                        class="h-4 w-4 text-slate-600 sm:h-5 sm:w-5 dark:text-slate-400"
                    />
                    <span
                        class="absolute top-1.5 right-1.5 flex h-1.5 w-1.5 rounded-full bg-rose-500 ring-2 ring-background sm:top-2.5 sm:right-2.5 sm:h-2 sm:w-2"
                    ></span>
                </Button>

                <div class="mx-0.5 hidden h-5 w-px bg-border sm:block"></div>

                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button
                            variant="ghost"
                            class="flex h-8 items-center gap-1 rounded-lg px-1 hover:bg-slate-100 sm:h-10 sm:gap-3 sm:rounded-xl sm:pr-4 sm:pl-2 md:h-11 dark:hover:bg-slate-800"
                        >
                            <div
                                class="flex h-7 w-7 items-center justify-center rounded-lg bg-primary/10 text-xs font-bold text-primary sm:h-8 sm:w-8 sm:text-sm"
                            >
                                {{
                                    user.name
                                        ?.split(' ')
                                        .map((n) => n[0])
                                        .join('')
                                        .toUpperCase()
                                }}
                            </div>
                            <div
                                class="hidden flex-col items-start text-left leading-tight lg:flex"
                            >
                                <span
                                    class="text-xs font-bold text-slate-900 dark:text-slate-100"
                                    >{{ user.name }}</span
                                >
                                <span
                                    class="text-xs font-bold tracking-tighter text-slate-400 uppercase"
                                    >{{ user.role || 'Super Admin' }}</span
                                >
                            </div>
                            <ChevronDown
                                class="hidden h-3 w-3 text-slate-400 sm:block"
                            />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent
                        align="end"
                        class="mt-2 w-56 rounded-2xl border-border p-2 shadow-lg"
                    >
                        <DropdownMenuLabel class="px-3 py-2">
                            <div class="flex flex-col gap-1">
                                <p class="text-sm leading-none font-bold">
                                    {{ user.name }}
                                </p>
                                <p
                                    class="text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                >
                                    {{ user.email }}
                                </p>
                            </div>
                        </DropdownMenuLabel>
                        <DropdownMenuSeparator class="my-2" />
                        <DropdownMenuItem
                            as-child
                            class="cursor-pointer rounded-xl px-3 py-2"
                        >
                            <Link
                                href="/settings/profile"
                                class="flex w-full items-center"
                            >
                                <User class="mr-2 h-4 w-4 text-slate-500" />
                                <span class="text-xs font-bold">Profile</span>
                            </Link>
                        </DropdownMenuItem>
                        <DropdownMenuItem
                            as-child
                            class="cursor-pointer rounded-xl px-3 py-2"
                        >
                            <Link
                                href="/settings/school"
                                class="flex w-full items-center"
                            >
                                <Settings class="mr-2 h-4 w-4 text-slate-500" />
                                <span class="text-xs font-bold">Settings</span>
                            </Link>
                        </DropdownMenuItem>
                        <DropdownMenuSeparator class="my-2" />
                        <DropdownMenuItem
                            class="cursor-pointer rounded-xl px-3 py-2 text-rose-600 focus:bg-rose-50 focus:text-rose-600"
                        >
                            <Link
                                href="/logout"
                                method="post"
                                as="button"
                                class="flex w-full items-center"
                            >
                                <LogOut class="mr-2 h-4 w-4" />
                                <span
                                    class="text-xs font-bold font-medium tracking-tight uppercase"
                                    >Sign Out</span
                                >
                            </Link>
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </div>
    </header>
</template>

<style scoped>
.tracking-widest {
    letter-spacing: 0.1em;
}
</style>
