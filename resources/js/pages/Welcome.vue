<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    GraduationCap,
    Users,
    BookOpen,
    BarChart3,
    Shield,
    Zap,
    ArrowRight,
    CheckCircle2,
    Calendar,
    MessageCircle,
    ClipboardCheck,
    Globe,
    Layout,
    Menu,
    X
} from 'lucide-vue-next';
import { ref, onMounted } from 'vue';
import { Button } from '@/components/ui/button';

defineProps<{ canLogin?: boolean; canRegister?: boolean }>();

const page = usePage<any>();
const isLoaded = ref(false);
const mobileMenuOpen = ref(false);

onMounted(() => {
    isLoaded.value = true;
});

const features = [
    {
        icon: GraduationCap,
        title: 'Master Learner Lifecycle',
        description: 'Automated admissions, intelligent enrollment, and real-time competency-based progress tracking for every student.',
    },
    {
        icon: Users,
        title: 'Advanced Teacher Studio',
        description: 'A professional workspace for simplified lesson planning, digital scheme creation, and instant assessment grading.',
    },
    {
        icon: ClipboardCheck,
        title: 'CBC Assessment Engine',
        description: 'Fully compliant rubric-based assessments, automated report card generation, and lifelong student portfolios.',
    },
    {
        icon: BarChart3,
        title: 'Institutional Intelligence',
        description: 'Comprehensive analytics for school administrators to monitor financial health, academic performance, and operational efficiency.',
    },
];

const partners = [
    { name: 'Ministry of Education', logo: 'MOE' },
    { name: 'KICD Certified', logo: 'KICD' },
    { name: 'KNEC Integrated', logo: 'KNEC' },
    { name: 'TSC Sync', logo: 'TSC' },
];
</script>

<template>
    <Head title="Kenya's Most Advanced CBC Ecosystem" />

    <div class="min-h-screen bg-white font-sans text-slate-900 transition-colors duration-500 dark:bg-[#020617] dark:text-white">
        <!-- Navigation -->
        <nav class="fixed top-0 right-0 left-0 z-50 border-b border-slate-200 bg-white/60 backdrop-blur-2xl dark:border-slate-800 dark:bg-[#020617]/60">
            <div class="mx-auto max-w-7xl px-6">
                <div class="flex h-20 items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 shadow-lg shadow-blue-500/20">
                            <GraduationCap class="h-6 w-6 text-white" />
                        </div>
                        <span class="text-xl font-black tracking-tighter uppercase">CBC<span class="text-blue-600">Core</span></span>
                    </div>

                    <div class="hidden items-center gap-8 lg:flex">
                        <a href="#features" class="text-[13px] font-bold tracking-widest text-slate-500 uppercase transition-colors hover:text-blue-600 dark:text-slate-400">Capabilities</a>
                        <a href="#impact" class="text-[13px] font-bold tracking-widest text-slate-500 uppercase transition-colors hover:text-blue-600 dark:text-slate-400">Institutional Impact</a>
                        <a href="#ecosystem" class="text-[13px] font-bold tracking-widest text-slate-500 uppercase transition-colors hover:text-blue-600 dark:text-slate-400">Ecosystem</a>
                    </div>

                    <div class="flex items-center gap-4">
                        <Link v-if="canLogin" href="/login" class="text-[13px] font-bold tracking-widest text-slate-600 uppercase transition-colors hover:text-blue-600 dark:text-slate-300">
                            Access Portal
                        </Link>
                        <Button v-if="canRegister" as-child class="h-10 rounded-full bg-blue-600 px-6 text-[13px] font-bold tracking-widest shadow-lg shadow-blue-600/20 hover:bg-blue-700">
                            <Link href="/register">DEPLOY SYSTEM</Link>
                        </Button>
                        <Button
                            variant="ghost"
                            size="icon"
                            class="lg:hidden"
                            @click="mobileMenuOpen = !mobileMenuOpen"
                        >
                            <Menu v-if="!mobileMenuOpen" class="h-6 w-6" />
                            <X v-else class="h-6 w-6" />
                        </Button>
                    </div>
                </div>
            </div>
            <!-- Mobile menu -->
            <div
                v-if="mobileMenuOpen"
                class="space-y-4 border-t bg-white p-6 lg:hidden dark:bg-[#020617]"
            >
                <a href="#features" class="block text-sm font-bold tracking-widest uppercase">Capabilities</a>
                <a href="#impact" class="block text-sm font-bold tracking-widest uppercase">Impact</a>
                <a href="#ecosystem" class="block text-sm font-bold tracking-widest uppercase">Ecosystem</a>
                <div class="flex flex-col gap-4 border-t pt-6">
                    <Button variant="outline" as-child class="rounded-full">
                        <Link href="/login">SIGN IN</Link>
                    </Button>
                    <Button as-child class="rounded-full bg-blue-600">
                        <Link href="/register">GET STARTED</Link>
                    </Button>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <main>
            <section class="relative overflow-hidden pt-40 pb-20 lg:pt-56 lg:pb-32">
                <div class="mx-auto max-w-7xl px-6">
                    <div class="grid items-center gap-16 lg:grid-cols-2">
                        <div class="transition-all duration-1000 ease-out" :class="{ 'translate-y-0 opacity-100': isLoaded, 'translate-y-10 opacity-0': !isLoaded }">
                            <div class="mb-8 inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-4 py-1.5 text-[11px] font-black tracking-[0.2em] text-blue-600 uppercase dark:border-blue-500/20 dark:bg-blue-500/10 dark:text-blue-400">
                                <Zap class="h-4 w-4" />
                                2026 Academic Edition
                            </div>
                            <h1 class="mb-8 text-5xl font-black leading-[1.05] tracking-tight text-slate-900 sm:text-7xl dark:text-white">
                                Launch Your School's <span class="bg-gradient-to-r from-blue-600 to-indigo-500 bg-clip-text text-transparent">Digital Future</span> in Days.
                            </h1>
                            <p class="mb-10 max-w-lg text-lg leading-relaxed text-slate-600 dark:text-slate-400">
                                Kenya's most sophisticated CBC management ecosystem. Get faster money movement, automated academic compliance, and unprecedented redundancy with our network of institutional partners.
                            </p>
                            <div class="flex flex-wrap items-center gap-6">
                                <Button size="lg" as-child class="h-14 rounded-full bg-blue-600 px-10 text-[14px] font-black tracking-widest shadow-xl shadow-blue-600/20 hover:bg-blue-700">
                                    <Link href="/register">ENROLL YOUR SCHOOL</Link>
                                </Button>
                                <a href="#demo" class="group flex items-center gap-2 text-[14px] font-black tracking-widest text-slate-600 uppercase dark:text-slate-300">
                                    Watch Interface Review
                                    <ArrowRight class="h-5 w-5 transition-transform group-hover:translate-x-1" />
                                </a>
                            </div>
                        </div>

                        <!-- Hero Mockup -->
                        <div class="relative lg:block transition-all duration-1000 delay-300 ease-out" :class="{ 'translate-x-0 opacity-100': isLoaded, 'translate-x-20 opacity-0': !isLoaded }">
                            <div class="relative mx-auto w-full max-w-[420px]">
                                <!-- Phone Frame -->
                                <div class="relative z-10 overflow-hidden rounded-[3rem] border-[8px] border-slate-900 bg-slate-900 shadow-2xl dark:border-slate-800">
                                    <div class="aspect-[9/19.5] bg-slate-50 dark:bg-[#020617]">
                                        <!-- App Content Mockup -->
                                        <div class="p-6 pt-12">
                                            <div class="mb-8 flex items-center justify-between">
                                                <div>
                                                    <p class="text-[10px] font-bold text-slate-400 uppercase">Welcome back,</p>
                                                    <h3 class="text-xl font-black text-slate-900 dark:text-white">Jane Bloggs</h3>
                                                </div>
                                                <div class="h-10 w-10 overflow-hidden rounded-full border-2 border-white shadow-sm dark:border-slate-800">
                                                    <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?q=80&w=100&auto=format&fit=crop" class="h-full w-full object-cover">
                                                </div>
                                            </div>

                                            <div class="mb-6 rounded-2xl bg-white p-5 shadow-sm dark:bg-slate-900">
                                                <p class="mb-1 text-[10px] font-bold text-slate-400 uppercase">Academic Performance</p>
                                                <div class="flex items-end justify-between">
                                                    <span class="text-2xl font-black text-blue-600">88.4%</span>
                                                    <span class="text-[10px] font-bold text-emerald-500">+4.2% This Term</span>
                                                </div>
                                            </div>

                                            <div class="space-y-3">
                                                <p class="text-[10px] font-bold text-slate-400 uppercase">Recent Assessments</p>
                                                <div v-for="i in 3" :key="i" class="flex items-center gap-3 rounded-2xl bg-white p-3 shadow-sm dark:bg-slate-900">
                                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-500/10">
                                                        <ClipboardCheck class="h-4 w-4" />
                                                    </div>
                                                    <div class="flex-1">
                                                        <div class="h-1.5 w-24 rounded-full bg-slate-100 dark:bg-slate-800"></div>
                                                        <div class="mt-1.5 h-1 w-16 rounded-full bg-slate-50 dark:bg-slate-800"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Decorative Gradients -->
                                <div class="absolute -top-20 -right-20 -z-10 h-64 w-64 bg-blue-500/20 blur-[100px]"></div>
                                <div class="absolute -bottom-20 -left-20 -z-10 h-64 w-64 bg-indigo-500/20 blur-[100px]"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Partners -->
            <section class="border-y border-slate-100 py-16 dark:border-slate-900">
                <div class="mx-auto max-w-7xl px-6">
                    <p class="mb-10 text-center text-[11px] font-black tracking-[0.3em] text-slate-400 uppercase">Trusted by Kenya's Leading Institutions:</p>
                    <div class="flex flex-wrap items-center justify-center gap-12 grayscale opacity-50 transition-all hover:grayscale-0 hover:opacity-100 lg:gap-20">
                        <div v-for="partner in partners" :key="partner.name" class="flex items-center gap-2">
                             <div class="text-xl font-black tracking-tighter text-slate-900 dark:text-white">{{ partner.logo }}</div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Features -->
            <section id="features" class="py-24 lg:py-40">
                <div class="mx-auto max-w-7xl px-6">
                    <div class="mb-20 grid gap-12 lg:grid-cols-2 lg:items-end">
                        <h2 class="text-4xl font-black leading-none tracking-tight text-slate-900 sm:text-6xl dark:text-white">
                            Everything You Need for <span class="text-blue-600">CBC Excellence.</span>
                        </h2>
                        <p class="text-lg text-slate-600 dark:text-slate-400">
                            We've modularized the entire academic lifecycle. Choose the capabilities your school needs to outperform and scale efficiently.
                        </p>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                        <div v-for="feature in features" :key="feature.title" class="group flex flex-col justify-between rounded-[2rem] border border-slate-100 bg-white p-10 transition-all hover:border-blue-600/20 hover:shadow-2xl hover:shadow-blue-600/10 dark:border-slate-800 dark:bg-slate-900/50">
                            <div>
                                <div class="mb-8 flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-600/20">
                                    <component :is="feature.icon" class="h-6 w-6" />
                                </div>
                                <h3 class="mb-4 text-xl font-black tracking-tight text-slate-900 dark:text-white">{{ feature.title }}</h3>
                                <p class="text-sm leading-relaxed text-slate-500 dark:text-slate-400">{{ feature.description }}</p>
                            </div>
                            <div class="mt-8">
                                <ArrowRight class="h-5 w-5 text-slate-300 transition-colors group-hover:text-blue-600" />
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Informative Content Section -->
            <section id="ecosystem" class="py-24 bg-slate-50 dark:bg-slate-900/30">
                <div class="mx-auto max-w-7xl px-6">
                    <div class="space-y-32">
                        <!-- Teachers -->
                        <div class="grid items-center gap-16 lg:grid-cols-2">
                            <div class="relative aspect-[4/3] overflow-hidden rounded-[2.5rem]">
                                <img src="https://images.unsplash.com/photo-1544531585-9847b68c8c86?q=80&w=2070&auto=format&fit=crop" class="h-full w-full object-cover">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                                <div class="absolute bottom-10 left-10 text-white">
                                    <p class="text-[10px] font-bold tracking-widest uppercase">Educator Empowerment</p>
                                    <h4 class="text-2xl font-black">AI-Powered Lesson Planning</h4>
                                </div>
                            </div>
                            <div>
                                <h3 class="mb-6 text-4xl font-black tracking-tight dark:text-white">Empower Your Teachers to <br> <span class="text-blue-600">Focus on the Learner.</span></h3>
                                <p class="mb-8 text-lg text-slate-600 dark:text-slate-400">
                                    CBCCore reduces administrative burden by 60%. Automated marking, one-click report generation, and shared digital content libraries let teachers do what they do best: teach.
                                </p>
                                <ul class="space-y-4">
                                    <li v-for="item in ['Digital Schemes of Work', 'Competency Rubric Builder', 'Mobile-First Attendance', 'Professional Growth Tracker']" :key="item" class="flex items-center gap-3 text-sm font-bold text-slate-700 dark:text-slate-300">
                                        <CheckCircle2 class="h-5 w-5 text-blue-600" /> {{ item }}
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Management -->
                        <div class="grid items-center gap-16 lg:grid-cols-2">
                            <div class="lg:order-2">
                                <div class="relative aspect-[4/3] overflow-hidden rounded-[2.5rem]">
                                    <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?q=80&w=2070&auto=format&fit=crop" class="h-full w-full object-cover">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                                    <div class="absolute bottom-10 left-10 text-white">
                                        <p class="text-[10px] font-bold tracking-widest uppercase">Administrative Control</p>
                                        <h4 class="text-2xl font-black">Centralized Command Center</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="lg:order-1">
                                <h3 class="mb-6 text-4xl font-black tracking-tight dark:text-white">Complete Visibility Into Your <br> <span class="text-blue-600">Entire School Operation.</span></h3>
                                <p class="mb-8 text-lg text-slate-600 dark:text-slate-400">
                                    From fee collection and payroll to academic compliance and inventory. Manage everything from a single, high-security dashboard optimized for both desktop and mobile.
                                </p>
                                <div class="grid grid-cols-2 gap-8">
                                    <div>
                                        <h5 class="mb-2 text-xl font-black text-blue-600">500+</h5>
                                        <p class="text-xs font-bold text-slate-500 uppercase">Schools Active</p>
                                    </div>
                                    <div>
                                        <h5 class="mb-2 text-xl font-black text-blue-600">10min</h5>
                                        <p class="text-xs font-bold text-slate-500 uppercase">Deployment Time</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- CTA -->
            <section class="py-24 lg:py-40">
                <div class="mx-auto max-w-5xl px-6 text-center">
                    <h2 class="mb-10 text-5xl font-black tracking-tight dark:text-white sm:text-7xl">
                        Modernize Your School <br> <span class="text-blue-600">This Term.</span>
                    </h2>
                    <p class="mx-auto mb-12 max-w-2xl text-xl text-slate-600 dark:text-slate-400">
                        Join the most innovative schools in Kenya. Start your 14-day full ecosystem trial today. No credit card required.
                    </p>
                    <div class="flex flex-col items-center justify-center gap-6 sm:flex-row">
                        <Button size="lg" as-child class="h-16 rounded-full bg-blue-600 px-12 text-[15px] font-black tracking-widest shadow-2xl shadow-blue-600/30 hover:bg-blue-700">
                            <Link href="/register">INITIALIZE SYSTEM</Link>
                        </Button>
                        <Button size="lg" variant="outline" as-child class="h-16 rounded-full border-slate-200 px-12 text-[15px] font-black tracking-widest dark:border-slate-800">
                            <Link href="/contact">TALK TO SPECIALIST</Link>
                        </Button>
                    </div>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="border-t border-slate-100 bg-slate-50 py-20 dark:border-slate-900 dark:bg-[#020617]">
            <div class="mx-auto max-w-7xl px-6">
                <div class="grid gap-16 lg:grid-cols-4">
                    <div class="col-span-2">
                        <div class="mb-8 flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600">
                                <GraduationCap class="h-6 w-6 text-white" />
                            </div>
                            <span class="text-xl font-black tracking-tighter uppercase">CBC<span class="text-blue-600">Core</span></span>
                        </div>
                        <p class="max-w-xs text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                            Kenya's leading academic operating system. Empowering schools through intelligent automation and competency-based tracking.
                        </p>
                    </div>
                    <div>
                        <h5 class="mb-6 text-[11px] font-black tracking-[0.3em] text-slate-400 uppercase">Platform</h5>
                        <ul class="space-y-4 text-sm font-bold text-slate-700 dark:text-slate-300">
                            <li><a href="#" class="transition-colors hover:text-blue-600">Teacher Studio</a></li>
                            <li><a href="#" class="transition-colors hover:text-blue-600">Learner Portal</a></li>
                            <li><a href="#" class="transition-colors hover:text-blue-600">Finance Command</a></li>
                            <li><a href="#" class="transition-colors hover:text-blue-600">Security & Privacy</a></li>
                        </ul>
                    </div>
                    <div>
                        <h5 class="mb-6 text-[11px] font-black tracking-[0.3em] text-slate-400 uppercase">Company</h5>
                        <ul class="space-y-4 text-sm font-bold text-slate-700 dark:text-slate-300">
                            <li><a href="#" class="transition-colors hover:text-blue-600">Institutional Success</a></li>
                            <li><a href="#" class="transition-colors hover:text-blue-600">Partner Program</a></li>
                            <li><a href="#" class="transition-colors hover:text-blue-600">System Status</a></li>
                            <li><a href="#" class="transition-colors hover:text-blue-600">Technical Support</a></li>
                        </ul>
                    </div>
                </div>
                <div class="mt-20 flex flex-col items-center justify-between border-t border-slate-100 pt-10 dark:border-slate-900/50 md:flex-row">
                    <p class="text-[11px] font-bold text-slate-400 uppercase">© 2026 CBCCore Systems. An Enterprise Division of Doitix Tech Labs.</p>
                    <div class="flex gap-8 text-[11px] font-bold text-slate-400 uppercase">
                        <a href="#" class="hover:text-blue-600">Data Protection</a>
                        <a href="#" class="hover:text-blue-600">Service Terms</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* Custom animations or refined styles if needed */
</style>
