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
    ShieldCheck
} from 'lucide-vue-next';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { 
    DropdownMenu, 
    DropdownMenuContent, 
    DropdownMenuItem, 
    DropdownMenuLabel, 
    DropdownMenuSeparator, 
    DropdownMenuTrigger 
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
    isDark.value = saved === 'dark' || (!saved && window.matchMedia('(prefers-color-scheme: dark)').matches);
    document.documentElement.classList.toggle('dark', isDark.value);
});

const searchFocused = ref(false);
</script>

<template>
    <header class="sticky top-0 z-50 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
        <div class="flex h-16 items-center px-4 md:px-8">
            <div class="flex items-center gap-4">
                <SidebarTrigger class="-ml-1 h-9 w-9 p-0 hover:bg-slate-100 dark:hover:bg-slate-800" />
                <div class="hidden md:flex items-center gap-2">
                     <!-- Breadcrumbs could go here if needed, but TailPanel seems to be a cleaner top bar -->
                </div>
            </div>

            <!-- Search Area -->
            <div class="flex flex-1 items-center justify-center px-4 md:px-20 lg:px-40">
                <div class="relative w-full max-w-2xl group">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                    <Input 
                        placeholder="Search anything... (Ctrl+K)" 
                        class="h-10 w-full pl-10 pr-12 rounded-xl border-border bg-muted/30 focus:bg-background transition-all"
                        @focus="searchFocused = true"
                        @blur="searchFocused = false"
                    />
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-1">
                        <kbd class="pointer-events-none hidden h-5 select-none items-center gap-1 rounded border bg-muted px-1.5 font-mono text-[10px] font-medium opacity-100 sm:flex">
                            <span class="text-xs">⌘</span>K
                        </kbd>
                    </div>
                </div>
            </div>

            <!-- Actions Area -->
            <div class="flex items-center gap-2 md:gap-4">
                <Button 
                    variant="ghost" 
                    size="icon" 
                    class="h-10 w-10 p-0 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800"
                    @click="toggleDarkMode"
                >
                    <Sun v-if="isDark" class="h-5 w-5 text-amber-500" />
                    <Moon v-else class="h-5 w-5 text-slate-700" />
                </Button>

                <Button variant="ghost" size="icon" class="relative h-10 w-10 p-0 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800">
                    <Bell class="h-5 w-5 text-slate-600 dark:text-slate-400" />
                    <span class="absolute right-2.5 top-2.5 flex h-2 w-2 rounded-full bg-rose-500 ring-2 ring-background"></span>
                </Button>

                <div class="h-6 w-px bg-border mx-1 hidden sm:block"></div>

                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="ghost" class="h-11 pl-2 pr-4 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center gap-3">
                            <div class="h-8 w-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center font-black text-sm">
                                {{ user.name?.split(' ').map(n => n[0]).join('').toUpperCase() }}
                            </div>
                            <div class="hidden lg:flex flex-col items-start text-left leading-tight">
                                <span class="text-xs font-black text-slate-900 dark:text-slate-100">{{ user.name }}</span>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">{{ user.role || 'Super Admin' }}</span>
                            </div>
                            <ChevronDown class="h-3 w-3 text-slate-400" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-56 mt-2 rounded-2xl border-border shadow-2xl p-2">
                        <DropdownMenuLabel class="px-3 py-2">
                            <div class="flex flex-col gap-1">
                                <p class="text-sm font-black leading-none">{{ user.name }}</p>
                                <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">{{ user.email }}</p>
                            </div>
                        </DropdownMenuLabel>
                        <DropdownMenuSeparator class="my-2" />
                        <DropdownMenuItem as-child class="rounded-xl px-3 py-2 cursor-pointer">
                            <Link href="/settings/profile" class="flex items-center w-full">
                                <User class="mr-2 h-4 w-4 text-slate-500" />
                                <span class="font-bold text-xs">Profile</span>
                            </Link>
                        </DropdownMenuItem>
                        <DropdownMenuItem as-child class="rounded-xl px-3 py-2 cursor-pointer">
                            <Link href="/settings/school" class="flex items-center w-full">
                                <Settings class="mr-2 h-4 w-4 text-slate-500" />
                                <span class="font-bold text-xs">Settings</span>
                            </Link>
                        </DropdownMenuItem>
                        <DropdownMenuSeparator class="my-2" />
                        <DropdownMenuItem class="rounded-xl px-3 py-2 cursor-pointer text-rose-600 focus:bg-rose-50 focus:text-rose-600">
                             <Link href="/logout" method="post" as="button" class="flex items-center w-full">
                                <LogOut class="mr-2 h-4 w-4" />
                                <span class="font-bold text-xs font-black uppercase tracking-widest">Sign Out</span>
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
