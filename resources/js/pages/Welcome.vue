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
        title: 'Student Management',
        description: 'Easily manage student admissions, class lists, and individual profiles with digital record-keeping.',
    },
    {
        icon: Users,
        title: 'Staff Coordination',
        description: 'Organize teacher assignments, track performance, and manage staff records from a single dashboard.',
    },
    {
        icon: ClipboardCheck,
        title: 'CBC Assessments',
        description: 'Automated grading tools tailored for the Kenyan CBC curriculum, including rubrics and report generation.',
    },
    {
        icon: BarChart3,
        title: 'Financial Oversight',
        description: 'Track school fees, generate invoices, and monitor expenses with simplified accounting tools.',
    },
];

const partners = [
    { name: 'Ministry of Education', logo: 'MOE' },
    { name: 'KICD Certified', logo: 'KICD' },
    { name: 'KNEC Integrated', logo: 'KNEC' },
    { name: 'TSC Sync', logo: 'TSC' },
];

const route = (window as any).route;
</script>

<template>
    <Head title="Welcome - CBC Core School Management System" />

    <div class="min-h-screen bg-white font-sans text-slate-900 transition-colors duration-500 dark:bg-[#020617] dark:text-white">
        <!-- Navigation -->
        <nav class="fixed top-0 right-0 left-0 z-50 border-b border-slate-200 bg-white/80 backdrop-blur-xl dark:border-slate-800 dark:bg-[#020617]/80">
            <div class="mx-auto max-w-7xl px-6">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-7 w-7 items-center justify-center rounded-md bg-blue-600 shadow-sm">
                            <GraduationCap class="h-4 w-4 text-white" />
                        </div>
                        <span class="text-lg font-bold tracking-tight uppercase">CBC<span class="text-blue-600">Core</span></span>
                    </div>

                    <div class="hidden items-center gap-6 lg:flex">
                        <Link :href="route('landing.modules')" class="text-xs font-semibold tracking-wide text-slate-500 uppercase transition-colors hover:text-blue-600 dark:text-slate-400">Modules</Link>
                        <Link :href="route('landing.about')" class="text-xs font-semibold tracking-wide text-slate-500 uppercase transition-colors hover:text-blue-600 dark:text-slate-400">About</Link>
                        <Link :href="route('landing.contact')" class="text-xs font-semibold tracking-wide text-slate-500 uppercase transition-colors hover:text-blue-600 dark:text-slate-400">Contact</Link>
                    </div>

                    <div class="flex items-center gap-3">
                        <Link v-if="canLogin" href="/login" class="text-xs font-bold tracking-wide text-slate-600 uppercase transition-colors hover:text-blue-600 dark:text-slate-300">
                            Login
                        </Link>
                        <Button v-if="canRegister" as-child class="h-8 rounded-md bg-blue-600 px-4 text-[10px] font-bold tracking-wide shadow-sm hover:bg-blue-700">
                            <Link href="/register">GET STARTED</Link>
                        </Button>
                        <Button
                            variant="ghost"
                            size="icon"
                            class="lg:hidden"
                            @click="mobileMenuOpen = !mobileMenuOpen"
                        >
                            <Menu v-if="!mobileMenuOpen" class="h-5 w-5" />
                            <X v-else class="h-5 w-5" />
                        </Button>
                    </div>
                </div>
            </div>
            <!-- Mobile menu -->
            <div
                v-if="mobileMenuOpen"
                class="space-y-3 border-t bg-white p-6 lg:hidden dark:bg-[#020617]"
            >
                <Link :href="route('landing.modules')" class="block text-xs font-bold uppercase">Modules</Link>
                <Link :href="route('landing.about')" class="block text-xs font-bold uppercase">About</Link>
                <Link :href="route('landing.contact')" class="block text-xs font-bold uppercase">Contact</Link>
                <div class="flex flex-col gap-3 border-t pt-4">
                    <Button variant="outline" as-child class="rounded-md">
                        <Link href="/login">LOGIN</Link>
                    </Button>
                    <Button as-child class="rounded-md bg-blue-600">
                        <Link href="/register">GET STARTED</Link>
                    </Button>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <main>
            <section class="relative overflow-hidden pt-32 pb-16 lg:pt-48 lg:pb-24">
                <!-- Background Image Layer -->
                <div class="absolute inset-0 -z-10 opacity-[0.03] dark:opacity-[0.05]">
                    <img src="https://images.unsplash.com/photo-1503676260728-1c00da094a0b?q=80&w=2022&auto=format&fit=crop" class="h-full w-full object-cover">
                </div>

                <div class="mx-auto max-w-7xl px-6">
                    <div class="grid items-center gap-12 lg:grid-cols-2">
                        <div :class="{ 'translate-y-0 opacity-100': isLoaded, 'translate-y-6 opacity-0': !isLoaded }" class="transition-all duration-700 ease-out">
                            <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-[10px] font-bold tracking-wider text-blue-600 uppercase dark:border-blue-500/20 dark:bg-blue-500/10 dark:text-blue-400">
                                <Star class="h-3 w-3" />
                                Premium School Management
                            </div>
                            <h1 class="mb-6 text-3xl font-bold leading-tight tracking-tight text-slate-900 sm:text-4xl dark:text-white">
                                Modernize Your School with <span class="text-blue-600">CBC Core</span>
                            </h1>
                            <p class="mb-8 max-w-lg text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                                A comprehensive platform designed for Kenyan schools. Manage students, assessments, and finances with an intuitive interface built for efficiency.
                            </p>
                            <div class="flex flex-wrap items-center gap-4">
                                <Button size="lg" as-child class="h-10 rounded-md bg-blue-600 px-6 text-[10px] font-bold tracking-wider shadow-md hover:bg-blue-700">
                                    <Link href="/register">START FREE TRIAL</Link>
                                </Button>
                                <button @click="showDemoModal = true" class="group flex items-center gap-2 text-xs font-bold tracking-wider text-slate-600 uppercase dark:text-slate-300">
                                    Book a Demo
                                    <ArrowRight class="h-4 w-4 transition-transform group-hover:translate-x-1" />
                                </button>
                            </div>
                        </div>

                        <!-- Hero Mockup -->
                        <div class="relative lg:block transition-all duration-700 delay-200 ease-out" :class="{ 'translate-x-0 opacity-100': isLoaded, 'translate-x-12 opacity-0': !isLoaded }">
                            <div class="relative mx-auto w-full max-w-[380px]">
                                <div class="relative z-10 overflow-hidden rounded-md border-4 border-slate-900 bg-slate-900 shadow-xl dark:border-slate-800">
                                    <div class="aspect-[9/18] bg-slate-50 dark:bg-[#020617]">
                                        <div class="p-5 pt-8">
                                            <div class="mb-6 flex items-center justify-between">
                                                <div>
                                                    <p class="text-[9px] font-bold text-slate-400 uppercase">Dashboard</p>
                                                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Overview</h3>
                                                </div>
                                                <div class="h-8 w-8 overflow-hidden rounded-md border border-white shadow-sm dark:border-slate-800">
                                                    <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?q=80&w=100&auto=format&fit=crop" class="h-full w-full object-cover">
                                                </div>
                                            </div>
                                            <div class="mb-4 rounded-lg bg-white p-4 shadow-sm dark:bg-slate-900 border border-slate-100 dark:border-slate-800">
                                                <p class="mb-1 text-[9px] font-bold text-slate-400 uppercase">Total Students</p>
                                                <div class="flex items-end justify-between">
                                                    <span class="text-xl font-bold text-blue-600">1,240</span>
                                                    <span class="text-[9px] font-medium text-emerald-500">+12% this term</span>
                                                </div>
                                            </div>
                                            <div class="space-y-2">
                                                <p class="text-[9px] font-bold text-slate-400 uppercase">Recent Activity</p>
                                                <div v-for="i in 3" :key="i" class="flex items-center gap-3 rounded-lg bg-white p-2.5 shadow-sm dark:bg-slate-900 border border-slate-50 dark:border-slate-800">
                                                    <div class="flex h-7 w-7 items-center justify-center rounded bg-blue-50 text-blue-600 dark:bg-blue-500/10">
                                                        <CheckCircle2 class="h-4 w-4" />
                                                    </div>
                                                    <div class="flex-1">
                                                        <div class="h-1.5 w-20 rounded-full bg-slate-100 dark:bg-slate-800"></div>
                                                        <div class="mt-1 h-1 w-12 rounded-full bg-slate-50 dark:bg-slate-700"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="absolute -top-12 -right-12 -z-10 h-48 w-48 bg-blue-500/10 blur-[80px]"></div>
                                <div class="absolute -bottom-12 -left-12 -z-10 h-48 w-48 bg-indigo-500/10 blur-[80px]"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Partners -->
            <section class="border-y border-slate-100 py-10 dark:border-slate-900">
                <div class="mx-auto max-w-7xl px-6">
                    <p class="mb-6 text-center text-[10px] font-bold tracking-widest text-slate-400 uppercase">In alignment with industry standards:</p>
                    <div class="flex flex-wrap items-center justify-center gap-8 md:gap-16 opacity-40 grayscale transition-all hover:grayscale-0 hover:opacity-100">
                        <div v-for="partner in partners" :key="partner.name" class="flex items-center gap-2">
                             <div class="text-lg font-bold tracking-tight text-slate-900 dark:text-white">{{ partner.logo }}</div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Features -->
            <section id="features" class="py-20 lg:py-32">
                <div class="mx-auto max-w-7xl px-6">
                    <div class="mb-16 grid gap-8 lg:grid-cols-2 lg:items-end">
                        <h2 class="text-3xl font-bold leading-tight tracking-tight text-slate-900 sm:text-4xl dark:text-white">
                            Integrated Solutions for <span class="text-blue-600">Modern Schools</span>
                        </h2>
                        <p class="text-sm text-slate-600 dark:text-slate-400">
                            Our platform addresses the core needs of school administrators and teachers, providing tools that simplify complex administrative tasks.
                        </p>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                        <div v-for="feature in features" :key="feature.title" class="group flex flex-col justify-between rounded-md border border-slate-100 bg-white p-8 transition-all hover:border-blue-600/20 hover:shadow-lg dark:border-slate-800 dark:bg-slate-900/50">
                            <div>
                                <div class="mb-6 flex h-10 w-10 items-center justify-center rounded-lg bg-blue-600 text-white shadow-sm">
                                    <component :is="feature.icon" class="h-5 w-5" />
                                </div>
                                <h3 class="mb-3 text-lg font-bold tracking-tight text-slate-900 dark:text-white">{{ feature.title }}</h3>
                                <p class="text-xs leading-relaxed text-slate-500 dark:text-slate-400">{{ feature.description }}</p>
                            </div>
                            <div class="mt-6">
                                <ArrowRight class="h-4 w-4 text-slate-300 transition-colors group-hover:text-blue-600" />
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- CTA -->
            <section class="py-20 lg:py-32 bg-slate-50 dark:bg-[#020617]/50">
                <div class="mx-auto max-w-4xl px-6 text-center">
                    <h2 class="mb-8 text-3xl font-bold tracking-tight dark:text-white sm:text-4xl">
                        Streamline Your School's <br> <span class="text-blue-600">Operations Today</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-xl text-sm text-slate-600 dark:text-slate-400">
                        Adopt a system that grows with your institution. Start your 14-day free trial or contact us for a personalized demo.
                    </p>
                    <div class="flex flex-col items-center justify-center gap-4 sm:flex-row">
                        <Button size="lg" as-child class="h-12 rounded-md bg-blue-600 px-8 text-[10px] font-bold tracking-wider shadow-md hover:bg-blue-700">
                            <Link href="/register">START NOW</Link>
                        </Button>
                        <button @click="showDemoModal = true" class="h-12 rounded-md border border-slate-200 bg-white px-8 text-[10px] font-bold tracking-wider transition-colors hover:bg-slate-50 dark:border-slate-800 dark:bg-transparent">
                            REQUEST DEMO
                        </button>
                    </div>
                </div>
            </section>
        </main>

        <!-- Demo Modal -->
        <div v-if="showDemoModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-[#020617]/70 backdrop-blur-sm" @click="showDemoModal = false"></div>
            <div class="relative w-full max-w-md rounded-md bg-white p-6 shadow-xl dark:bg-[#0f172a] border dark:border-slate-800">
                <button @click="showDemoModal = false" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
                    <X class="h-5 w-5" />
                </button>
                <div class="mb-6">
                    <h2 class="text-xl font-bold tracking-tight dark:text-white">Schedule a Demo</h2>
                    <p class="mt-1 text-xs text-slate-500">Provide your school details and our team will get in touch.</p>
                </div>
                <form @submit.prevent="submitDemo" class="space-y-4">
                    <div>
                        <label class="text-[9px] font-bold tracking-widest uppercase text-slate-400">School Name</label>
                        <input v-model="demoForm.school_name" type="text" class="mt-1 w-full rounded-md border border-slate-200 bg-slate-50 px-4 py-2.5 text-xs focus:border-blue-600 focus:outline-none dark:border-slate-700 dark:bg-[#1e293b]" required />
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-[9px] font-bold tracking-widest uppercase text-slate-400">Contact Person</label>
                            <input v-model="demoForm.contact_person" type="text" class="mt-1 w-full rounded-md border border-slate-200 bg-slate-50 px-4 py-2.5 text-xs focus:border-blue-600 focus:outline-none dark:border-slate-700 dark:bg-[#1e293b]" required />
                        </div>
                        <div>
                            <label class="text-[9px] font-bold tracking-widest uppercase text-slate-400">Phone</label>
                            <input v-model="demoForm.phone" type="tel" class="mt-1 w-full rounded-md border border-slate-200 bg-slate-50 px-4 py-2.5 text-xs focus:border-blue-600 focus:outline-none dark:border-slate-700 dark:bg-[#1e293b]" required />
                        </div>
                    </div>
                    <div>
                        <label class="text-[9px] font-bold tracking-widest uppercase text-slate-400">Email Address</label>
                        <input v-model="demoForm.email" type="email" class="mt-1 w-full rounded-md border border-slate-200 bg-slate-50 px-4 py-2.5 text-xs focus:border-blue-600 focus:outline-none dark:border-slate-700 dark:bg-[#1e293b]" required />
                    </div>
                    <Button :disabled="demoForm.processing" class="h-12 w-full rounded-md bg-blue-600 text-xs font-bold tracking-wider">
                        {{ demoForm.processing ? 'SENDING...' : 'CONFIRM REQUEST' }}
                    </Button>
                </form>
            </div>
        </div>

        <!-- Footer -->
        <footer class="border-t border-slate-100 bg-slate-50 py-16 dark:border-slate-900 dark:bg-[#020617]">
            <div class="mx-auto max-w-7xl px-6">
                <div class="grid gap-12 lg:grid-cols-4">
                    <div class="col-span-2">
                        <div class="mb-6 flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600">
                                <GraduationCap class="h-5 w-5 text-white" />
                            </div>
                            <span class="text-lg font-bold tracking-tight uppercase">CBC<span class="text-blue-600">Core</span></span>
                        </div>
                        <p class="max-w-xs text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                            A specialized management system for primary and secondary schools in Kenya.
                        </p>
                    </div>
                    <div>
                        <h5 class="mb-5 text-[9px] font-bold tracking-widest text-slate-400 uppercase">Information</h5>
                        <ul class="space-y-3 text-xs font-semibold text-slate-700 dark:text-slate-300">
                            <li><Link :href="route('landing.modules')" class="transition-colors hover:text-blue-600">Modules</Link></li>
                            <li><Link :href="route('landing.about')" class="transition-colors hover:text-blue-600">About</Link></li>
                            <li><Link :href="route('landing.contact')" class="transition-colors hover:text-blue-600">Contact</Link></li>
                        </ul>
                    </div>
                    <div>
                        <h5 class="mb-5 text-[9px] font-bold tracking-widest text-slate-400 uppercase">Support</h5>
                        <ul class="space-y-3 text-xs font-semibold text-slate-700 dark:text-slate-300">
                            <li><a href="#" class="transition-colors hover:text-blue-600">Help Center</a></li>
                            <li><a href="#" class="transition-colors hover:text-blue-600">Privacy</a></li>
                            <li><a href="#" class="transition-colors hover:text-blue-600">Terms</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </footer>
        <Toast />
    </div>
</template>
