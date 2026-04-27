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
            class="flex h-14 items-center gap-1 px-4 sm:h-16 sm:gap-2 sm:px-6 md:h-20 md:px-10 lg:px-12"
        >
            <div class="flex shrink-0 items-center gap-1 sm:gap-4">
                <SidebarTrigger
                    class="-ml-2 h-9 w-9 rounded-xl p-0 transition-all hover:bg-muted"
                />
            </div>

            <!-- Search Area -->
            <div
                class="hidden flex-1 items-center justify-center px-4 sm:flex md:px-12 lg:px-24"
            >
                <div class="group relative w-full max-w-lg">
                    <Search
                        class="absolute top-1/2 left-4 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground/60 transition-colors group-focus-within:text-primary"
                    />
                    <Input
                        placeholder="Search... (Ctrl+K)"
                        class="h-10 w-full rounded-2xl border-border/50 bg-muted/40 pr-4 pl-10 text-[11px] font-bold tracking-tight transition-all focus:bg-background focus:ring-4 focus:ring-primary/5 sm:h-11 sm:text-xs"
                        @focus="searchFocused = true"
                        @blur="searchFocused = false"
                    />
                </div>
            </div>

            <!-- Actions Area -->
            <div class="flex shrink-0 items-center gap-2 md:gap-3">
                <Button
                    variant="ghost"
                    size="icon"
                    class="h-9 w-9 rounded-xl transition-all hover:bg-muted sm:h-10 sm:w-10 md:h-12 md:w-12"
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
                    class="relative h-9 w-9 rounded-xl transition-all hover:bg-muted sm:h-10 sm:w-10 md:h-12 md:w-12"
                >
                    <Bell
                        class="h-4 w-4 text-muted-foreground sm:h-5 sm:w-5"
                    />
                    <span
                        class="absolute top-2 right-2 flex h-1.5 w-1.5 rounded-full bg-primary ring-4 ring-background sm:top-3 sm:right-3 sm:h-2 sm:w-2"
                    ></span>
                </Button>

                <div class="mx-1 h-6 w-px bg-border/50"></div>

                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button
                            variant="ghost"
                            class="group flex h-10 items-center gap-2 rounded-xl px-1 transition-all hover:bg-muted sm:h-12 sm:gap-3 sm:pr-4 sm:pl-2 md:h-14"
                        >
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-xl bg-primary/10 text-xs font-black text-primary transition-all group-hover:scale-110 sm:h-10 sm:w-10 sm:text-sm"
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
                                class="hidden flex-col items-start text-left leading-none lg:flex"
                            >
                                <span
                                    class="text-[11px] font-black uppercase tracking-tight text-foreground"
                                    >{{ user.name }}</span
                                >
                                <span
                                    class="mt-1 text-[9px] font-black tracking-[0.1em] text-muted-foreground uppercase"
                                    >{{ user.role || 'Super Admin' }}</span
                                >
                            </div>
                            <ChevronDown
                                class="hidden h-3 w-3 text-muted-foreground/50 transition-transform group-data-[state=open]:rotate-180 sm:block"
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
