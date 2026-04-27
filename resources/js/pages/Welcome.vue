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
        title: 'Competency Tracking',
        description: 'Enable real-time evaluation of students\' skills such as creativity, critical thinking, and collaboration. Generate detailed reports that provide a full picture of each learner\'s progress.',
        color: 'bg-blue-600',
    },
    {
        icon: Heart,
        title: 'Behavioural Management',
        description: 'Record observations about students\' social and emotional development, not just academic performance. Nurture learners as well-rounded individuals through holistic tracking.',
        color: 'bg-rose-500',
    },
    {
        icon: ClipboardCheck,
        title: 'Formative & Summative',
        description: 'Continuously evaluate learners while efficiently managing end-of-term exams. Automated reports and analytics help identify learning gaps early.',
        color: 'bg-indigo-500',
    },
    {
        icon: Zap,
        title: 'Learning Pathways',
        description: 'Tailor education based on individual student needs. Align with CBC\'s goal of ensuring no learner is left behind by providing personalized learning experiences.',
        color: 'bg-amber-500',
    },
    {
        icon: Layout,
        title: 'Digital Portfolios',
        description: 'Allow students to showcase their work and progress over time. Reinforce practical and project-based learning with a permanent record of achievements.',
        color: 'bg-emerald-500',
    },
    {
        icon: BookOpen,
        title: 'Lesson Planning',
        description: 'Prepare CBC-aligned lessons efficiently with automated tools. Save time on administrative prep and focus more on student interaction.',
        color: 'bg-cyan-500',
    },
    {
        icon: MessageCircle,
        title: 'Parent Engagement',
        description: 'Strengthen collaboration between home and school through dedicated portals and real-time communication tools.',
        color: 'bg-purple-500',
    },
    {
        icon: Globe,
        title: 'Cloud-Based Access',
        description: 'Access the platform from anywhere at any time. Stay connected across all stakeholders—teachers, students, and parents.',
        color: 'bg-blue-600',
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
        <nav class="fixed top-0 right-0 left-0 z-50 border-b border-white/10 bg-white/70 backdrop-blur-2xl transition-all duration-300 dark:bg-slate-950/70">
            <div class="mx-auto max-w-7xl px-6">
                <div class="flex h-20 items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 shadow-lg shadow-blue-600/20">
                            <GraduationCap class="h-6 w-6 text-white" />
                        </div>
                        <div class="flex flex-col">
                            <span class="text-lg font-black tracking-tight text-slate-900 dark:text-white leading-none">CLOUD SCHOOL</span>
                            <span class="text-[10px] font-bold text-blue-600 tracking-[0.2em] uppercase">System - Kenya</span>
                        </div>
                    </div>

                    <div class="hidden items-center gap-8 lg:flex">
                        <Link :href="route('landing.modules')" class="text-xs font-bold tracking-widest text-slate-500 uppercase transition-all hover:text-blue-600 dark:text-slate-400">Modules</Link>
                        <Link :href="route('landing.about')" class="text-xs font-bold tracking-widest text-slate-500 uppercase transition-all hover:text-blue-600 dark:text-slate-400">About</Link>
                        <Link :href="route('landing.contact')" class="text-xs font-bold tracking-widest text-slate-500 uppercase transition-all hover:text-blue-600 dark:text-slate-400">Contact</Link>
                    </div>

                    <div class="flex items-center gap-4">
                        <Link v-if="canLogin" href="/login" class="hidden text-xs font-black tracking-widest text-slate-600 uppercase transition-all hover:text-blue-600 lg:block dark:text-slate-300">
                            Login
                        </Link>
                        <Button v-if="canRegister" as-child class="h-11 rounded-xl bg-blue-600 px-6 text-[10px] font-black tracking-widest shadow-lg shadow-blue-600/20 hover:scale-105 transition-all active:scale-95">
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
            <transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="translate-y-1 opacity-0"
                enter-to-class="translate-y-0 opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="translate-y-0 opacity-100"
                leave-to-class="translate-y-1 opacity-0"
            >
                <div
                    v-if="mobileMenuOpen"
                    class="space-y-4 border-t bg-white p-8 lg:hidden dark:bg-slate-950 border-slate-100 dark:border-slate-800 shadow-2xl"
                >
                    <Link :href="route('landing.modules')" class="block text-xs font-black tracking-widest uppercase text-slate-600 dark:text-slate-300">Modules</Link>
                    <Link :href="route('landing.about')" class="block text-xs font-black tracking-widest uppercase text-slate-600 dark:text-slate-300">About</Link>
                    <Link :href="route('landing.contact')" class="block text-xs font-black tracking-widest uppercase text-slate-600 dark:text-slate-300">Contact</Link>
                    <div class="flex flex-col gap-4 border-t border-slate-100 dark:border-slate-800 pt-6">
                        <Button variant="outline" as-child class="h-12 rounded-xl border-2 font-black tracking-widest">
                            <Link href="/login">LOGIN</Link>
                        </Button>
                        <Button as-child class="h-12 rounded-xl bg-blue-600 font-black tracking-widest shadow-xl shadow-blue-600/20">
                            <Link href="/register">GET STARTED</Link>
                        </Button>
                    </div>
                </div>
            </transition>
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
                            <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50/50 px-4 py-1.5 text-[10px] font-bold tracking-wider text-blue-600 uppercase backdrop-blur-sm dark:border-blue-500/20 dark:bg-blue-500/10 dark:text-blue-400">
                                <Zap class="h-3.5 w-3.5 fill-blue-600/10" />
                                Transforming Education in Kenya
                            </div>
                            <h1 class="mb-6 text-4xl font-bold leading-tight tracking-tight text-slate-900 sm:text-5xl lg:text-6xl dark:text-white">
                                CBC School <br> <span class="bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">Management System</span>
                            </h1>
                            <p class="mb-8 max-w-lg text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                                Transform education with a comprehensive digital platform designed specifically to align with Kenya's Competency-Based Curriculum. Support skills, competencies, and holistic development beyond traditional exam performance.
                            </p>
                            <div class="flex flex-wrap items-center gap-4">
                                <Button size="lg" as-child class="group h-12 rounded-lg bg-blue-600 px-8 text-[11px] font-bold tracking-wider shadow-lg shadow-blue-600/20 transition-all hover:bg-blue-700 hover:shadow-blue-600/40">
                                    <Link href="/register" class="flex items-center gap-2">
                                        GET STARTED
                                        <ArrowRight class="h-4 w-4 transition-transform group-hover:translate-x-1" />
                                    </Link>
                                </Button>
                                <button @click="showDemoModal = true" class="h-12 rounded-lg border border-slate-200 bg-white px-8 text-[11px] font-bold tracking-wider transition-all hover:bg-slate-50 dark:border-slate-800 dark:bg-transparent">
                                    REQUEST DEMO
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
                                <!-- Floating Stats Card -->
                                <div class="absolute -bottom-6 -left-12 z-20 animate-bounce-slow">
                                    <div class="flex items-center gap-3 rounded-xl border border-white bg-white/90 p-4 shadow-xl backdrop-blur-md dark:border-slate-800 dark:bg-[#0f172a]/90">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-500 text-white">
                                            <BarChart3 class="h-6 w-6" />
                                        </div>
                                        <div>
                                            <p class="text-[14px] font-bold text-slate-900 dark:text-white">500+</p>
                                            <p class="text-[9px] font-medium text-slate-500 uppercase tracking-wider">Schools Using CBC</p>
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

            <!-- Transformation Detail Section -->
            <section class="relative py-20 lg:py-32">
                <div class="mx-auto max-w-7xl px-6">
                    <div class="grid items-center gap-16 lg:grid-cols-2">
                        <div class="relative order-2 lg:order-1">
                            <div class="relative overflow-hidden rounded-2xl border-8 border-white bg-white shadow-2xl dark:border-slate-800">
                                <img src="https://images.unsplash.com/photo-1427504494785-3a9ca7044f45?q=80&w=2070&auto=format&fit=crop" alt="Students learning" class="w-full">
                                <div class="absolute inset-0 bg-gradient-to-tr from-blue-600/10 to-transparent"></div>
                            </div>
                            <!-- Small badge floating -->
                            <div class="absolute -top-6 -right-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-rose-500 to-pink-500 text-white shadow-xl">
                                <Star class="h-8 w-8" />
                            </div>
                        </div>
                        
                        <div class="order-1 lg:order-2 space-y-8">
                            <div class="space-y-4">
                                <div class="text-xs font-bold tracking-widest text-blue-600 uppercase">Mission & Transformation</div>
                                <h2 class="text-3xl font-bold leading-tight tracking-tight text-slate-900 sm:text-4xl dark:text-white">
                                    Guiding the Shift from 8-4-4 to <span class="text-blue-600">CBC Excellence</span>
                                </h2>
                                <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-400 font-medium">
                                    As schools shift from the traditional 8-4-4 system to CBC, there is an increasing need for systems that can effectively support this new model of learning. The Cloud School System is at the center of this transformation.
                                </p>
                            </div>
                            
                            <div class="space-y-6">
                                <p class="text-sm leading-relaxed text-slate-500 dark:text-slate-400 italic border-l-2 border-blue-600 pl-4">
                                    Focusing on skills, competencies, and holistic development rather than just exam performance, our platform provides a complete solution that enables schools in Kenya to successfully implement CBC requirements.
                                </p>
                                <ul class="grid gap-3 sm:grid-cols-2">
                                    <li v-for="item in ['Competency Tracking', 'Behavioural Records', 'Real-time Assessment', 'Parent Integration']" :key="item" class="flex items-center gap-2 text-[10px] font-bold text-slate-700 dark:text-slate-300">
                                        <div class="flex h-5 w-5 items-center justify-center rounded-full bg-blue-100 text-blue-600 dark:bg-blue-500/10">
                                            <CheckCircle2 class="h-3 w-3" />
                                        </div>
                                        {{ item }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Features -->
            <section id="features" class="relative py-20 lg:py-32 bg-slate-50/50 dark:bg-[#020617]/30">
                <!-- Background Blob Decor -->
                <div class="absolute top-1/2 left-1/2 -z-10 h-96 w-96 -translate-x-1/2 -translate-y-1/2 bg-blue-500/10 blur-[120px]"></div>

                <div class="mx-auto max-w-7xl px-6">
                    <div class="mb-16 text-center">
                        <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-purple-200 bg-purple-50 px-3 py-1 text-[10px] font-bold tracking-wider text-purple-600 uppercase dark:border-purple-500/20 dark:bg-purple-500/10 dark:text-purple-400">
                            <Zap class="h-3 w-3" />
                            Powerful Features
                        </div>
                        <h2 class="mb-4 text-3xl font-bold leading-tight tracking-tight text-slate-900 sm:text-4xl dark:text-white">
                            Comprehensive Features for <span class="bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">CBC Success</span>
                        </h2>
                        <p class="mx-auto max-w-2xl text-sm text-slate-600 dark:text-slate-400">
                            Everything you need to implement and manage Competency-Based Curriculum effectively, from evaluation to long-term digital growth.
                        </p>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        <div v-for="feature in features" :key="feature.title" class="group flex flex-col justify-between rounded-xl border border-white bg-white/50 p-7 shadow-sm transition-all hover:-translate-y-1 hover:border-blue-600/20 hover:shadow-xl dark:border-slate-800 dark:bg-[#0f172a]/50 backdrop-blur-sm">
                            <div>
                                <div :class="['mb-6 flex h-12 w-12 items-center justify-center rounded-xl text-white shadow-lg shadow-blue-600/5', feature.color || 'bg-blue-600']">
                                    <component :is="feature.icon" class="h-6 w-6" />
                                </div>
                                <h3 class="mb-3 text-base font-bold tracking-tight text-slate-900 dark:text-white">{{ feature.title }}</h3>
                                <p class="text-[11px] leading-relaxed text-slate-500 dark:text-slate-400">{{ feature.description }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Final CTA -->
            <section class="relative py-24 lg:py-36 overflow-hidden">
                <div class="absolute inset-0 bg-blue-600 dark:bg-blue-700 -z-10">
                    <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 40px 40px;"></div>
                    <div class="absolute top-0 left-0 right-0 h-24 bg-gradient-to-b from-white dark:from-slate-950 to-transparent"></div>
                </div>
                <div class="mx-auto max-w-4xl px-6 text-center">
                    <div class="mb-10 inline-flex h-16 w-16 items-center justify-center rounded-3xl bg-white/20 backdrop-blur-xl text-white shadow-2xl">
                        <GraduationCap class="h-8 w-8" />
                    </div>
                    <h2 class="mb-8 text-4xl font-black tracking-tight text-white sm:text-5xl lg:text-6xl uppercase">
                        Transform Your <br> <span class="bg-gradient-to-r from-white to-white/60 bg-clip-text text-transparent italic leading-[1.3]">Institution Today</span>
                    </h2>
                    <p class="mx-auto mb-12 max-w-xl text-sm font-medium text-blue-50/80 leading-relaxed uppercase tracking-widest">
                        Adopt a modern system that grows with your school. Start your 14-day free trial or contact us for a personalized demo.
                    </p>
                    <div class="flex flex-col items-center justify-center gap-6 sm:flex-row">
                        <Button size="lg" as-child class="h-14 rounded-2xl bg-white px-10 text-[11px] font-black tracking-[0.3em] text-blue-600 shadow-2xl hover:scale-105 transition-all active:scale-95">
                            <Link href="/register">START NOW</Link>
                        </Button>
                        <button @click="showDemoModal = true" class="h-14 rounded-2xl border-2 border-white/30 bg-white/10 px-10 text-[11px] font-black tracking-[0.3em] text-white backdrop-blur-xl transition-all hover:bg-white/20 hover:border-white/50 active:scale-95">
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
