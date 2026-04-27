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
    ArrowRight
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
    { name: 'Modules', href: 'landing.modules' },
    { name: 'About', href: 'landing.about' },
    { name: 'Pricing', href: 'landing.pricing' },
    { name: 'FAQ', href: 'landing.faq' },
    { name: 'Contact', href: 'landing.contact' },
];

const route = (window as any).route;
</script>

<template>
    <div class="min-h-screen bg-white dark:bg-slate-950 transition-colors duration-500">
        <!-- Navigation -->
        <nav 
            class="fixed top-0 left-0 right-0 z-[100] transition-all duration-500 border-b"
            :class="[
                scrolled 
                    ? 'bg-white/80 dark:bg-slate-950/80 backdrop-blur-xl py-4 border-slate-100 dark:border-slate-800' 
                    : 'bg-transparent py-6 border-transparent'
            ]"
        >
            <div class="container mx-auto px-6">
                <div class="flex items-center justify-between">
                    <!-- Logo -->
                    <Link :href="route('home')" class="flex items-center gap-3 group">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 shadow-lg shadow-blue-600/20 group-hover:scale-110 transition-transform">
                            <GraduationCap class="h-6 w-6 text-white" />
                        </div>
                        <div class="flex flex-col">
                            <span class="text-lg font-black tracking-tighter text-slate-900 dark:text-white leading-none uppercase">CLOUD SCHOOL</span>
                            <span class="text-[9px] font-bold text-blue-600 tracking-[0.3em] uppercase opacity-80">Kenya Ecosystem</span>
                        </div>
                    </Link>

                    <!-- Desktop Nav -->
                    <div class="hidden lg:flex items-center gap-10">
                        <Link 
                            v-for="link in navLinks" 
                            :key="link.name"
                            :href="route(link.href)"
                            class="text-[10px] font-black uppercase tracking-[0.2em] transition-all hover:text-blue-600"
                            :class="route().current(link.href) ? 'text-blue-600' : 'text-slate-500'"
                        >
                            {{ link.name }}
                        </Link>
                    </div>

                    <!-- Actions -->
                    <div class="hidden lg:flex items-center gap-6">
                        <Link :href="route('login')" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 hover:text-blue-600 transition-colors">
                            Sign In
                        </Link>
                        <Link :href="route('register')">
                            <Button class="h-11 px-8 rounded-xl bg-blue-600 text-white text-[10px] font-black uppercase tracking-[0.2em] shadow-lg shadow-blue-600/20 hover:scale-105 transition-all">
                                Get Started
                            </Button>
                        </Link>
                    </div>

                    <!-- Mobile Toggle -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden text-slate-900 dark:text-white">
                        <Menu v-if="!mobileMenuOpen" class="h-6 w-6" />
                        <X v-else class="h-6 w-6" />
                    </button>
                </div>
            </div>
        </nav>

        <!-- Mobile Menu Overlay -->
        <transition 
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 translate-y-4"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-4"
        >
            <div v-if="mobileMenuOpen" class="fixed inset-0 z-[90] bg-white dark:bg-slate-950 pt-24 px-6 lg:hidden">
                <div class="flex flex-col gap-8">
                    <Link 
                        v-for="link in navLinks" 
                        :key="link.name"
                        @click="mobileMenuOpen = false"
                        :href="route(link.href)"
                        class="text-2xl font-black uppercase tracking-tight text-slate-900 dark:text-white"
                        :class="{ 'text-blue-600': route().current(link.href) }"
                    >
                        {{ link.name }}
                    </Link>
                    <div class="h-[1px] w-full bg-slate-100 dark:bg-slate-800"></div>
                    <Link :href="route('login')" class="text-lg font-bold text-slate-500 uppercase tracking-widest">Sign In</Link>
                    <Link :href="route('register')">
                        <Button class="h-14 w-full rounded-2xl bg-blue-600 text-white text-xs font-black uppercase tracking-widest shadow-xl shadow-blue-600/20">
                            Create Institutional Account
                        </Button>
                    </Link>
                </div>
            </div>
        </transition>

        <!-- Main Content -->
        <main>
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-slate-50 dark:bg-slate-900 py-24 border-t border-slate-100 dark:border-slate-800">
            <div class="container mx-auto px-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-16 mb-20">
                    <div class="md:col-span-1">
                        <Link :href="route('home')" class="flex items-center gap-3 mb-8">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-white">
                                <GraduationCap class="h-6 w-6" />
                            </div>
                            <span class="text-xl font-black tracking-tighter text-slate-900 dark:text-white leading-none uppercase">CLOUD SCHOOL</span>
                        </Link>
                        <p class="text-xs font-medium text-slate-500 leading-relaxed uppercase tracking-widest mb-8">
                            Kenya's premier digital infrastructure for CBC transition and holistic school management. Empowering tomorrow's leaders today.
                        </p>
                        <div class="flex items-center gap-4">
                            <a href="#" class="h-10 w-10 flex items-center justify-center rounded-xl bg-white dark:bg-slate-800 shadow-sm text-slate-400 hover:text-blue-600 transition-colors"><Facebook class="h-4 w-4" /></a>
                            <a href="#" class="h-10 w-10 flex items-center justify-center rounded-xl bg-white dark:bg-slate-800 shadow-sm text-slate-400 hover:text-blue-600 transition-colors"><Twitter class="h-4 w-4" /></a>
                            <a href="#" class="h-10 w-10 flex items-center justify-center rounded-xl bg-white dark:bg-slate-800 shadow-sm text-slate-400 hover:text-blue-600 transition-colors"><Linkedin class="h-4 w-4" /></a>
                            <a href="#" class="h-10 w-10 flex items-center justify-center rounded-xl bg-white dark:bg-slate-800 shadow-sm text-slate-400 hover:text-blue-600 transition-colors"><Instagram class="h-4 w-4" /></a>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-[10px] font-black uppercase tracking-[0.4em] text-blue-600 mb-8">Platform</h4>
                        <ul class="space-y-4">
                            <li v-for="link in navLinks" :key="link.name">
                                <Link :href="route(link.href)" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 hover:text-blue-500 transition-colors">
                                    {{ link.name }}
                                </Link>
                            </li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="text-[10px] font-black uppercase tracking-[0.4em] text-blue-600 mb-8">Legal</h4>
                        <ul class="space-y-4">
                            <li><a href="#" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 hover:text-blue-500 transition-colors">Privacy Policy</a></li>
                            <li><a href="#" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 hover:text-blue-500 transition-colors">Terms of Service</a></li>
                            <li><a href="#" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 hover:text-blue-500 transition-colors">Cookie Policy</a></li>
                            <li><a href="#" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 hover:text-blue-500 transition-colors">GDPR Compliance</a></li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="text-[10px] font-black uppercase tracking-[0.4em] text-blue-600 mb-8">Headquarters</h4>
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 leading-loose">
                            Westlands Commercial Centre<br>
                            Ring Road, Nairobi<br>
                            P.O Box 4509-00100 GPO<br>
                            Tel: +254 756 865 033
                        </p>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row items-center justify-between pt-12 border-t border-slate-100 dark:border-slate-800 gap-8">
                    <p class="text-[9px] font-black uppercase tracking-[0.3em] text-slate-400">
                        © 2026 DOITIX TECH LABS. ALL RIGHTS RESERVED.
                    </p>
                    <div class="flex items-center gap-8">
                        <span class="text-[9px] font-black uppercase tracking-[0.3em] text-slate-400">Designed in Nairobi</span>
                        <div class="h-1.5 w-1.5 rounded-full bg-blue-600"></div>
                        <span class="text-[9px] font-black uppercase tracking-[0.3em] text-slate-400">Engineered for Kenya</span>
                    </div>
                </div>
            </div>
        </footer>

        <Toast />
    </div>
</template>
