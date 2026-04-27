<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { 
    GraduationCap, 
    Menu, 
    X, 
    MessageCircle,
    Facebook,
    Twitter,
    Linkedin,
    Instagram,
    ArrowRight,
    BookOpen
} from 'lucide-vue-next';
import { ref, onMounted, onUnmounted } from 'vue';
import { Button } from '@/components/ui/button';
import Toast from '@/components/Toast.vue';

const page = usePage<any>();
const mobileMenuOpen = ref(false);
const scrolled = ref(false);

const handleScroll = () => {
    scrolled.value = window.scrollY > 20;
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});

const navLinks = [
    { name: 'Home', href: 'home' },
    { name: 'About Us', href: 'landing.about' },
    { name: 'Platform Modules', href: 'landing.modules' },
    { name: 'Pricing Plans', href: 'landing.pricing' },
    { name: 'FAQ', href: 'landing.faq' },
    { name: 'Contact', href: 'landing.contact' },
];


</script>

<template>
    <div class="min-h-screen bg-[#F8F9FF] dark:bg-slate-950 transition-colors duration-500 font-sans selection:bg-blue-100 selection:text-blue-900">
        <!-- Navigation -->
        <nav 
            class="fixed top-0 left-0 right-0 z-[100] transition-all duration-500"
            :class="[
                scrolled 
                    ? 'bg-white/80 dark:bg-slate-950/80 backdrop-blur-xl py-4 shadow-sm border-b border-slate-100 dark:border-slate-800' 
                    : 'bg-transparent py-6'
            ]"
        >
            <div class="container mx-auto px-6 max-w-7xl">
                <div class="flex items-center justify-between">
                    <!-- Logo -->
                    <Link :href="route('home')" class="flex items-center gap-3 group">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#3B63F6] shadow-lg shadow-blue-600/20 group-hover:rotate-6 transition-transform">
                            <BookOpen class="h-6 w-6 text-white" />
                        </div>
                        <span class="text-xl font-bold tracking-tight text-[#3B63F6] font-display">Cloud School System</span>
                    </Link>

                    <!-- Desktop Nav -->
                    <div class="hidden lg:flex items-center gap-8">
                        <Link 
                            v-for="link in navLinks" 
                            :key="link.name"
                            :href="route(link.href)"
                            class="text-[13px] font-medium transition-all hover:text-[#3B63F6]"
                            :class="route().current(link.href) ? 'text-[#3B63F6]' : 'text-slate-600 dark:text-slate-400'"
                        >
                            {{ link.name }}
                        </Link>
                    </div>

                    <!-- Actions -->
                    <div class="hidden lg:flex items-center gap-6">
                        <template v-if="page.props.auth?.user">
                            <Link :href="route('dashboard')">
                                <Button class="h-11 px-8 rounded-xl bg-[#3B63F6] hover:bg-blue-700 text-white text-[13px] font-bold shadow-lg shadow-blue-600/20 transition-all hover:scale-105 active:scale-95">
                                    Go to Dashboard
                                </Button>
                            </Link>
                        </template>
                        <template v-else>
                            <Link :href="route('login')" class="text-[13px] font-bold text-slate-600 hover:text-[#3B63F6] transition-colors">
                                Sign In
                            </Link>
                            <Link :href="route('register')">
                                <Button class="h-11 px-8 rounded-xl bg-[#3B63F6] hover:bg-blue-700 text-white text-[13px] font-bold shadow-lg shadow-blue-600/20 transition-all hover:scale-105 active:scale-95">
                                    Get Started
                                </Button>
                            </Link>
                        </template>
                    </div>

                    <!-- Mobile Toggle -->
                    <button @click="mobileMenuOpen = true" class="lg:hidden p-2 text-slate-900 dark:text-white rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        <Menu class="h-6 w-6" />
                    </button>
                </div>
            </div>
        </nav>

        <!-- Sidebar Mobile Menu -->
        <div 
            class="fixed inset-0 z-[110] lg:hidden pointer-events-none"
            :class="{ 'pointer-events-auto': mobileMenuOpen }"
        >
            <!-- Backdrop -->
            <transition
                enter-active-class="transition-opacity duration-300"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div 
                    v-if="mobileMenuOpen" 
                    @click="mobileMenuOpen = false"
                    class="absolute inset-0 bg-slate-900/60 backdrop-blur-md"
                ></div>
            </transition>

            <!-- Sidebar -->
            <transition
                enter-active-class="transition-transform duration-500 cubic-bezier(0.4, 0, 0.2, 1)"
                enter-from-class="-translate-x-full"
                enter-to-class="translate-x-0"
                leave-active-class="transition-transform duration-300 cubic-bezier(0.4, 0, 1, 1)"
                leave-from-class="translate-x-0"
                leave-to-class="-translate-x-full"
            >
                <div v-if="mobileMenuOpen" class="absolute top-0 left-0 bottom-0 w-[85%] max-w-[340px] bg-white dark:bg-slate-950 shadow-2xl p-8 flex flex-col pointer-events-auto">
                    <div class="flex items-center justify-between mb-12">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#3B63F6] text-white">
                                <BookOpen class="h-6 w-6" />
                            </div>
                            <span class="text-xl font-bold tracking-tight text-[#3B63F6]">Cloud School</span>
                        </div>
                        <button @click="mobileMenuOpen = false" class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500">
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <nav class="flex flex-col gap-2 flex-grow">
                        <Link 
                            v-for="link in navLinks" 
                            :key="link.name"
                            @click="mobileMenuOpen = false"
                            :href="route(link.href)"
                            class="flex items-center h-14 px-6 rounded-2xl text-base font-bold transition-all"
                            :class="route().current(link.href) 
                                ? 'bg-blue-50 text-[#3B63F6] dark:bg-blue-500/10' 
                                : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-900'"
                        >
                            {{ link.name }}
                        </Link>
                    </nav>

                    <div class="pt-8 border-t border-slate-100 dark:border-slate-800 space-y-4">
                        <template v-if="page.props.auth?.user">
                            <Link :href="route('dashboard')" @click="mobileMenuOpen = false" class="block">
                                <Button class="h-16 w-full rounded-2xl bg-[#3B63F6] text-white text-sm font-bold shadow-xl shadow-blue-600/20">
                                    Go to Dashboard
                                </Button>
                            </Link>
                        </template>
                        <template v-else>
                            <Link :href="route('login')" @click="mobileMenuOpen = false" class="block w-full text-center py-4 text-sm font-bold text-slate-600 dark:text-slate-400">
                                Sign In to Account
                            </Link>
                            <Link :href="route('register')" @click="mobileMenuOpen = false" class="block">
                                <Button class="h-16 w-full rounded-2xl bg-[#3B63F6] text-white text-sm font-bold shadow-xl shadow-blue-600/20">
                                    Get Started
                                </Button>
                            </Link>
                        </template>
                    </div>
                </div>
            </transition>
        </div>

        <!-- Main Content -->
        <main>
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-white dark:bg-slate-900 py-24 border-t border-slate-100 dark:border-slate-800">
            <div class="container mx-auto px-6 max-w-7xl">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-16 mb-20">
                    <div class="md:col-span-1">
                        <Link :href="route('home')" class="flex items-center gap-3 mb-8">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#3B63F6] text-white">
                                <BookOpen class="h-6 w-6" />
                            </div>
                            <span class="text-xl font-bold tracking-tight text-[#3B63F6] leading-none">Cloud School</span>
                        </Link>
                        <p class="text-sm font-medium text-slate-500 leading-relaxed mb-8">
                            Kenya's leading digital platform for internal school management and CBC curriculum alignment.
                        </p>
                        <div class="flex items-center gap-4">
                            <a href="#" class="h-11 w-11 flex items-center justify-center rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-400 hover:text-[#3B63F6] hover:bg-white transition-all shadow-sm"><Facebook class="h-5 w-5" /></a>
                            <a href="#" class="h-11 w-11 flex items-center justify-center rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-400 hover:text-[#3B63F6] hover:bg-white transition-all shadow-sm"><Twitter class="h-5 w-5" /></a>
                            <a href="#" class="h-11 w-11 flex items-center justify-center rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-400 hover:text-[#3B63F6] hover:bg-white transition-all shadow-sm"><Linkedin class="h-5 w-5" /></a>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white mb-8">Solution</h4>
                        <ul class="space-y-4">
                            <li><Link :href="route('landing.modules')" class="text-sm font-medium text-slate-500 hover:text-[#3B63F6] transition-colors">Academic Management</Link></li>
                            <li><Link :href="route('landing.modules')" class="text-sm font-medium text-slate-500 hover:text-[#3B63F6] transition-colors">Finance & Billing</Link></li>
                            <li><Link :href="route('landing.modules')" class="text-sm font-medium text-slate-500 hover:text-[#3B63F6] transition-colors">Parent Engagement</Link></li>
                            <li><Link :href="route('landing.modules')" class="text-sm font-medium text-slate-500 hover:text-[#3B63F6] transition-colors">Assessment Engine</Link></li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white mb-8">Company</h4>
                        <ul class="space-y-4">
                            <li><Link :href="route('landing.about')" class="text-sm font-medium text-slate-500 hover:text-[#3B63F6] transition-colors">About Us</Link></li>
                            <li><Link :href="route('landing.pricing')" class="text-sm font-medium text-slate-500 hover:text-[#3B63F6] transition-colors">Pricing</Link></li>
                            <li><Link :href="route('landing.contact')" class="text-sm font-medium text-slate-500 hover:text-[#3B63F6] transition-colors">Contact</Link></li>
                            <li><Link :href="route('landing.faq')" class="text-sm font-medium text-slate-500 hover:text-[#3B63F6] transition-colors">FAQ</Link></li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white mb-8">Connect</h4>
                        <p class="text-sm font-medium text-slate-500 leading-loose mb-4">
                            Westlands Commercial Centre<br>
                            Ring Road, Nairobi<br>
                            Kenya
                        </p>
                        <p class="text-sm font-bold text-[#3B63F6]">info@cloudschool.co.ke</p>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row items-center justify-between pt-12 border-t border-slate-100 dark:border-slate-800 gap-8">
                    <p class="text-sm font-medium text-slate-400">
                        © 2026 Cloud School System. Built for the Kenyan Future.
                    </p>
                    <div class="flex items-center gap-8">
                        <a href="#" class="text-xs font-bold text-slate-400 hover:text-slate-600">Privacy</a>
                        <a href="#" class="text-xs font-bold text-slate-400 hover:text-slate-600">Terms</a>
                        <a href="#" class="text-xs font-bold text-slate-400 hover:text-slate-600">Security</a>
                    </div>
                </div>
            </div>
        </footer>

        <Toast />
    </div>
</template>

