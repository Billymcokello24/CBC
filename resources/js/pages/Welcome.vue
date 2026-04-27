<script setup lang="ts">
import { Head, Link, usePage, useForm } from '@inertiajs/vue3';
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
    X,
    Heart,
    Star
} from 'lucide-vue-next';
import { ref, onMounted } from 'vue';
import { Button } from '@/components/ui/button';
import Toast from '@/components/Toast.vue';

defineProps<{ canLogin?: boolean; canRegister?: boolean }>();

const page = usePage<any>();
const isLoaded = ref(false);
const mobileMenuOpen = ref(false);
const showDemoModal = ref(false);

const demoForm = useForm({
    school_name: '',
    contact_person: '',
    email: '',
    phone: '',
    message: '',
});

onMounted(() => {
    isLoaded.value = true;
});

const submitDemo = () => {
    demoForm.post(route('demo.book'), {
        onSuccess: () => {
            showDemoModal.value = false;
            demoForm.reset();
        },
    });
};

const features = [
    {
        icon: GraduationCap,
        title: 'Manage Your Students Easily',
        description: 'Track your students from admission to graduation. Keep all their records safe in one place.',
    },
    {
        icon: Users,
        title: 'Better Tools for Teachers',
        description: 'Help your teachers save time with digital lesson plans and easy class management tools.',
    },
    {
        icon: ClipboardCheck,
        title: 'Smart Grading & Reports',
        description: 'Create report cards in seconds. Our system handles all the complex CBC grading for you.',
    },
    {
        icon: BarChart3,
        title: 'Simple Reports for Principals',
        description: 'See how your school is doing at a glance. Manage fees, staff, and classes without the stress.',
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
    <Head title="Welcome - Kenya's Best CBC School System" />

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
                        <Link :href="route('landing.modules')" class="text-[13px] font-bold tracking-widest text-slate-500 uppercase transition-colors hover:text-blue-600 dark:text-slate-400">What it does</Link>
                        <Link :href="route('landing.about')" class="text-[13px] font-bold tracking-widest text-slate-500 uppercase transition-colors hover:text-blue-600 dark:text-slate-400">About Us</Link>
                        <Link :href="route('landing.contact')" class="text-[13px] font-bold tracking-widest text-slate-500 uppercase transition-colors hover:text-blue-600 dark:text-slate-400">Contact</Link>
                    </div>

                    <div class="flex items-center gap-4">
                        <Link v-if="canLogin" href="/login" class="text-[13px] font-bold tracking-widest text-slate-600 uppercase transition-colors hover:text-blue-600 dark:text-slate-300">
                            Login
                        </Link>
                        <Button v-if="canRegister" as-child class="h-10 rounded-full bg-blue-600 px-6 text-[13px] font-bold tracking-widest shadow-lg shadow-blue-600/20 hover:bg-blue-700">
                            <Link href="/register">GET STARTED</Link>
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
                <Link :href="route('landing.modules')" class="block text-sm font-bold tracking-widest uppercase">What it does</Link>
                <Link :href="route('landing.about')" class="block text-sm font-bold tracking-widest uppercase">About Us</Link>
                <Link :href="route('landing.contact')" class="block text-sm font-bold tracking-widest uppercase">Contact</Link>
                <div class="flex flex-col gap-4 border-t pt-6">
                    <Button variant="outline" as-child class="rounded-full">
                        <Link href="/login">LOGIN</Link>
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
                <!-- Background Image Layer -->
                <div class="absolute inset-0 -z-10 opacity-[0.03] dark:opacity-[0.05]">
                    <img src="https://images.unsplash.com/photo-1544531585-9847b68c8c86?q=80&w=2070&auto=format&fit=crop" class="h-full w-full object-cover">
                </div>

                <div class="mx-auto max-w-7xl px-6">
                    <div class="grid items-center gap-16 lg:grid-cols-2">
                        <div :class="{ 'translate-y-0 opacity-100': isLoaded, 'translate-y-10 opacity-0': !isLoaded }" class="transition-all duration-1000 ease-out">
                            <div class="mb-8 inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-4 py-1.5 text-[11px] font-black tracking-[0.2em] text-blue-600 uppercase dark:border-blue-500/20 dark:bg-blue-500/10 dark:text-blue-400">
                                <Star class="h-4 w-4" />
                                Ranked #1 for CBC Schools
                            </div>
                            <h1 class="mb-8 text-5xl font-black leading-[1.05] tracking-tight text-slate-900 sm:text-7xl dark:text-white">
                                The Best Way to Manage Your <span class="bg-gradient-to-r from-blue-600 to-indigo-500 bg-clip-text text-transparent">CBC School.</span>
                            </h1>
                            <p class="mb-10 max-w-lg text-lg leading-relaxed text-slate-600 dark:text-slate-400">
                                We make school management simple. Handle your students, teachers, and school fees in one easy system. Built for Kenya, by Kenyans.
                            </p>
                            <div class="flex flex-wrap items-center gap-6">
                                <Button size="lg" as-child class="h-14 rounded-full bg-blue-600 px-10 text-[14px] font-black tracking-widest shadow-xl shadow-blue-600/20 hover:bg-blue-700">
                                    <Link href="/register">JOIN NOW</Link>
                                </Button>
                                <button @click="showDemoModal = true" class="group flex items-center gap-2 text-[14px] font-black tracking-widest text-slate-600 uppercase dark:text-slate-300">
                                    Book a Free Demo
                                    <ArrowRight class="h-5 w-5 transition-transform group-hover:translate-x-1" />
                                </button>
                            </div>
                        </div>

                        <!-- Hero Mockup (Keep the cool mockup) -->
                        <div class="relative lg:block transition-all duration-1000 delay-300 ease-out" :class="{ 'translate-x-0 opacity-100': isLoaded, 'translate-x-20 opacity-0': !isLoaded }">
                            <div class="relative mx-auto w-full max-w-[420px]">
                                <div class="relative z-10 overflow-hidden rounded-[3rem] border-[8px] border-slate-900 bg-slate-900 shadow-2xl dark:border-slate-800">
                                    <div class="aspect-[9/19.5] bg-slate-50 dark:bg-[#020617]">
                                        <div class="p-6 pt-12">
                                            <div class="mb-8 flex items-center justify-between">
                                                <div>
                                                    <p class="text-[10px] font-bold text-slate-400 uppercase">School Summary</p>
                                                    <h3 class="text-xl font-black text-slate-900 dark:text-white">Today's View</h3>
                                                </div>
                                                <div class="h-10 w-10 overflow-hidden rounded-full border-2 border-white shadow-sm dark:border-slate-800">
                                                    <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?q=80&w=100&auto=format&fit=crop" class="h-full w-full object-cover">
                                                </div>
                                            </div>
                                            <div class="mb-6 rounded-2xl bg-white p-5 shadow-sm dark:bg-slate-900">
                                                <p class="mb-1 text-[10px] font-bold text-slate-400 uppercase">Student Attendance</p>
                                                <div class="flex items-end justify-between">
                                                    <span class="text-2xl font-black text-blue-600">96.4%</span>
                                                    <span class="text-[10px] font-bold text-emerald-500">Normal Range</span>
                                                </div>
                                            </div>
                                            <div class="space-y-3">
                                                <p class="text-[10px] font-bold text-slate-400 uppercase">Quick Actions</p>
                                                <div v-for="i in 3" :key="i" class="flex items-center gap-3 rounded-2xl bg-white p-3 shadow-sm dark:bg-slate-900">
                                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-500/10">
                                                        <CheckCircle2 class="h-4 w-4" />
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
                                <div class="absolute -top-20 -right-20 -z-10 h-64 w-64 bg-blue-500/10 blur-[100px]"></div>
                                <div class="absolute -bottom-20 -left-20 -z-10 h-64 w-64 bg-indigo-500/10 blur-[100px]"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Partners -->
            <section class="border-y border-slate-100 py-16 dark:border-slate-900">
                <div class="mx-auto max-w-7xl px-6 text-center">
                    <p class="mb-10 text-[11px] font-black tracking-[0.3em] text-slate-400 uppercase">Helping Schools Across Kenya:</p>
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
                            Everything You Need for <span class="text-blue-600">Your School to Succeed.</span>
                        </h2>
                        <p class="text-lg text-slate-600 dark:text-slate-400">
                            We have built tools for every person in your school. Choose what you need to make your school better and more modern.
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

            <!-- CTA -->
            <section class="py-24 lg:py-40 bg-slate-50 dark:bg-[#020617]/50">
                <div class="mx-auto max-w-5xl px-6 text-center">
                    <h2 class="mb-10 text-5xl font-black tracking-tight dark:text-white sm:text-7xl">
                        Make Your School <br> <span class="text-blue-600">Modern Today.</span>
                    </h2>
                    <p class="mx-auto mb-12 max-w-2xl text-xl text-slate-600 dark:text-slate-400">
                        Join many other schools in Kenya. Start your 14-day free trial now. No hidden fees.
                    </p>
                    <div class="flex flex-col items-center justify-center gap-6 sm:flex-row">
                        <Button size="lg" as-child class="h-16 rounded-full bg-blue-600 px-12 text-[15px] font-black tracking-widest shadow-2xl shadow-blue-600/30 hover:bg-blue-700">
                            <Link href="/register">START NOW</Link>
                        </Button>
                        <button @click="showDemoModal = true" class="h-16 rounded-full border border-slate-200 bg-white px-12 text-[15px] font-black tracking-widest transition-colors hover:bg-slate-50 dark:border-slate-800 dark:bg-transparent">
                            BOOK A DEMO
                        </button>
                    </div>
                </div>
            </section>
        </main>

        <!-- Demo Modal -->
        <div v-if="showDemoModal" class="fixed inset-0 z-[100] flex items-center justify-center p-6">
            <div class="absolute inset-0 bg-[#020617]/80 backdrop-blur-sm" @click="showDemoModal = false"></div>
            <div class="relative w-full max-w-lg rounded-[2rem] bg-white p-8 shadow-2xl dark:bg-[#0f172a]">
                <button @click="showDemoModal = false" class="absolute top-6 right-6 text-slate-400 hover:text-slate-600">
                    <X class="h-6 w-6" />
                </button>
                <div class="mb-8">
                    <h2 class="text-2xl font-black tracking-tight dark:text-white">Book a Free Demo</h2>
                    <p class="mt-2 text-sm text-slate-500">Our team will show you how the system works for your school.</p>
                </div>
                <form @submit.prevent="submitDemo" class="space-y-4">
                    <div>
                        <label class="text-[10px] font-black tracking-widest uppercase text-slate-400">School Name</label>
                        <input v-model="demoForm.school_name" type="text" class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-600 focus:outline-none dark:border-slate-700 dark:bg-[#1e293b]" required />
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-[10px] font-black tracking-widest uppercase text-slate-400">Contact Person</label>
                            <input v-model="demoForm.contact_person" type="text" class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-600 focus:outline-none dark:border-slate-700 dark:bg-[#1e293b]" required />
                        </div>
                        <div>
                            <label class="text-[10px] font-black tracking-widest uppercase text-slate-400">Phone Number</label>
                            <input v-model="demoForm.phone" type="tel" class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-600 focus:outline-none dark:border-slate-700 dark:bg-[#1e293b]" required />
                        </div>
                    </div>
                    <div>
                        <label class="text-[10px] font-black tracking-widest uppercase text-slate-400">Email Address</label>
                        <input v-model="demoForm.email" type="email" class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-blue-600 focus:outline-none dark:border-slate-700 dark:bg-[#1e293b]" required />
                    </div>
                    <Button :disabled="demoForm.processing" class="h-14 w-full rounded-full bg-blue-600 text-sm font-black tracking-widest">
                        {{ demoForm.processing ? 'SENDING...' : 'CONFIRM BOOKING' }}
                    </Button>
                </form>
            </div>
        </div>

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
                            The best school management system in Kenya. We help schools manage students and reports easily.
                        </p>
                    </div>
                    <div>
                        <h5 class="mb-6 text-[11px] font-black tracking-[0.3em] text-slate-400 uppercase">Information</h5>
                        <ul class="space-y-4 text-sm font-bold text-slate-700 dark:text-slate-300">
                            <li><Link :href="route('landing.modules')" class="transition-colors hover:text-blue-600">What it does</Link></li>
                            <li><Link :href="route('landing.about')" class="transition-colors hover:text-blue-600">About Us</Link></li>
                            <li><Link :href="route('landing.contact')" class="transition-colors hover:text-blue-600">Contact Us</Link></li>
                        </ul>
                    </div>
                    <div>
                        <h5 class="mb-6 text-[11px] font-black tracking-[0.3em] text-slate-400 uppercase">Support</h5>
                        <ul class="space-y-4 text-sm font-bold text-slate-700 dark:text-slate-300">
                            <li><a href="#" class="transition-colors hover:text-blue-600">Help Center</a></li>
                            <li><a href="#" class="transition-colors hover:text-blue-600">Privacy Policy</a></li>
                            <li><a href="#" class="transition-colors hover:text-blue-600">Terms of Use</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </footer>
        <Toast />
    </div>
</template>
