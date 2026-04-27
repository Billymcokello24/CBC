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
    Loader2
} from 'lucide-vue-next';
import { ref, onMounted } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import Toast from '@/components/Toast.vue';

defineProps<{ canLogin?: boolean; canRegister?: boolean }>();

const page = usePage<any>();
const isLoaded = ref(false);
const mobileMenuOpen = ref(false);
const showDemoModal = ref(false);

onMounted(() => {
    isLoaded.value = true;
});

const demoForm = useForm({
    school_name: '',
    contact_person: '',
    email: '',
    phone: '',
    message: ''
});

const submitDemo = () => {
    demoForm.post(route('book-demo'), {
        onSuccess: () => {
            showDemoModal.value = false;
            demoForm.reset();
        }
    });
};

const features = [
    {
        icon: GraduationCap,
        title: 'Student Records',
        description: 'Keep all student information in one place. Track their growth and grades without any stress.',
    },
    {
        icon: Users,
        title: 'Teacher Tools',
        description: 'Help your teachers plan lessons and mark exams faster. Give them more time to focus on teaching.',
    },
    {
        icon: ClipboardCheck,
        title: 'CBC Reports',
        description: 'Create beautiful report cards and student portfolios automatically. Stay fully compliant with CBC rules.',
    },
    {
        icon: BarChart3,
        title: 'School Finances',
        description: 'Track fee payments and school spending easily. Get clear reports on how your school is doing.',
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
    <Head title="The Easiest School Management System for Kenya" />

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
                        <Link :href="route('modules')" class="text-[13px] font-bold tracking-widest text-slate-500 uppercase transition-colors hover:text-blue-600 dark:text-slate-400">Modules</Link>
                        <Link :href="route('about')" class="text-[13px] font-bold tracking-widest text-slate-500 uppercase transition-colors hover:text-blue-600 dark:text-slate-400">About Us</Link>
                        <Link :href="route('contact')" class="text-[13px] font-bold tracking-widest text-slate-500 uppercase transition-colors hover:text-blue-600 dark:text-slate-400">Contact</Link>
                    </div>

                    <div class="flex items-center gap-4">
                        <Link v-if="canLogin" href="/login" class="text-[13px] font-bold tracking-widest text-slate-600 uppercase transition-colors hover:text-blue-600 dark:text-slate-300">
                            Login
                        </Link>
                        <Button v-if="canRegister" as-child class="h-10 rounded-full bg-blue-600 px-6 text-[13px] font-bold tracking-widest shadow-lg shadow-blue-600/20 hover:bg-blue-700">
                            <Link href="/register">START NOW</Link>
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
                <Link :href="route('modules')" class="block text-sm font-bold tracking-widest uppercase">Modules</Link>
                <Link :href="route('about')" class="block text-sm font-bold tracking-widest uppercase">About Us</Link>
                <Link :href="route('contact')" class="block text-sm font-bold tracking-widest uppercase">Contact</Link>
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
                <!-- Background Image Overlay -->
                <div class="absolute inset-0 -z-10 bg-[url('https://images.unsplash.com/photo-1562774053-701939374585?q=80&w=2086&auto=format&fit=crop')] bg-cover bg-center opacity-[0.03] dark:opacity-[0.07]"></div>
                
                <div class="mx-auto max-w-7xl px-6">
                    <div class="grid items-center gap-16 lg:grid-cols-2">
                        <div class="transition-all duration-1000 ease-out" :class="{ 'translate-y-0 opacity-100': isLoaded, 'translate-y-10 opacity-0': !isLoaded }">
                            <div class="mb-8 inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-4 py-1.5 text-[11px] font-black tracking-[0.2em] text-blue-600 uppercase dark:border-blue-500/20 dark:bg-blue-500/10 dark:text-blue-400">
                                <Zap class="h-4 w-4" />
                                Built for Kenyan Schools
                            </div>
                            <h1 class="mb-8 text-5xl font-black leading-[1.05] tracking-tight text-slate-900 sm:text-7xl dark:text-white">
                                The Simple Way to <span class="bg-gradient-to-r from-blue-600 to-indigo-500 bg-clip-text text-transparent">Manage Your School.</span>
                            </h1>
                            <p class="mb-10 max-w-lg text-lg leading-relaxed text-slate-600 dark:text-slate-400">
                                Stop wasting time on paperwork. Our easy system helps you track students, manage fees, and print CBC reports in just a few clicks.
                            </p>
                            <div class="flex flex-wrap items-center gap-6">
                                <Button size="lg" as-child class="h-14 rounded-full bg-blue-600 px-10 text-[14px] font-black tracking-widest shadow-xl shadow-blue-600/20 hover:bg-blue-700">
                                    <Link href="/register">START FOR FREE</Link>
                                </Button>
                                <button @click="showDemoModal = true" class="group flex items-center gap-2 text-[14px] font-black tracking-widest text-slate-600 uppercase transition-colors hover:text-blue-600 dark:text-slate-300">
                                    Book a Demo
                                    <ArrowRight class="h-5 w-5 transition-transform group-hover:translate-x-1" />
                                </button>
                            </div>
                        </div>

                        <!-- Hero Mockup -->
                        <div class="relative transition-all duration-1000 delay-300 ease-out lg:block" :class="{ 'translate-x-0 opacity-100': isLoaded, 'translate-x-20 opacity-0': !isLoaded }">
                            <div class="relative mx-auto w-full max-w-[420px]">
                                <!-- Phone Frame -->
                                <div class="relative z-10 overflow-hidden rounded-[3rem] border-[8px] border-slate-900 bg-slate-900 shadow-2xl dark:border-slate-800">
                                    <div class="aspect-[9/19.5] bg-slate-50 dark:bg-[#020617]">
                                        <!-- App Content Mockup -->
                                        <div class="p-6 pt-12">
                                            <div class="mb-8 flex items-center justify-between">
                                                <div>
                                                    <p class="text-[10px] font-bold text-slate-400 uppercase">Welcome back,</p>
                                                    <h3 class="text-xl font-black text-slate-900 dark:text-white">Head Teacher</h3>
                                                </div>
                                                <div class="h-10 w-10 overflow-hidden rounded-full border-2 border-white shadow-sm dark:border-slate-800">
                                                    <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?q=80&w=100&auto=format&fit=crop" class="h-full w-full object-cover">
                                                </div>
                                            </div>

                                            <div class="mb-6 rounded-2xl bg-white p-5 shadow-sm dark:bg-slate-900">
                                                <p class="mb-1 text-[10px] font-bold text-slate-400 uppercase">Fee Collection</p>
                                                <div class="flex items-end justify-between">
                                                    <span class="text-2xl font-black text-blue-600">Ksh 4.2M</span>
                                                    <span class="text-[10px] font-bold text-emerald-500">Collected Today</span>
                                                </div>
                                            </div>

                                            <div class="space-y-3">
                                                <p class="text-[10px] font-bold text-slate-400 uppercase">Recent Activity</p>
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
                    <p class="mb-10 text-center text-[11px] font-black tracking-[0.3em] text-slate-400 uppercase">Trusted by Schools Nationwide:</p>
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
                            Everything you need to <br><span class="text-blue-600">run your school.</span>
                        </h2>
                        <p class="text-lg text-slate-600 dark:text-slate-400">
                            We have everything in one place. Choose what your school needs and start making your work easier today.
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
            <section class="py-24 lg:py-40">
                <div class="mx-auto max-w-5xl px-6 text-center">
                    <h2 class="mb-10 text-5xl font-black tracking-tight dark:text-white sm:text-7xl">
                        Make your work easier <br> <span class="text-blue-600">today.</span>
                    </h2>
                    <p class="mx-auto mb-12 max-w-2xl text-xl text-slate-600 dark:text-slate-400">
                        Join many other schools in Kenya. Try it for 14 days and see the difference. No payment needed to start.
                    </p>
                    <div class="flex flex-col items-center justify-center gap-6 sm:flex-row">
                        <Button size="lg" as-child class="h-16 rounded-full bg-blue-600 px-12 text-[15px] font-black tracking-widest shadow-2xl shadow-blue-600/30 hover:bg-blue-700">
                            <Link href="/register">START NOW</Link>
                        </Button>
                        <button @click="showDemoModal = true" class="h-16 rounded-full border border-slate-200 bg-transparent px-12 text-[15px] font-black tracking-widest dark:border-slate-800">
                            BOOK A DEMO
                        </button>
                    </div>
                </div>
            </section>
        </main>

        <!-- Demo Modal -->
        <div v-if="showDemoModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" @click="showDemoModal = false"></div>
            <div class="relative w-full max-w-md overflow-hidden rounded-[2.5rem] bg-white shadow-2xl dark:bg-slate-900">
                <div class="p-8">
                    <div class="mb-8 flex items-center justify-between">
                        <h2 class="text-2xl font-black dark:text-white">Book a Demo</h2>
                        <button @click="showDemoModal = false" class="text-slate-400 hover:text-slate-600">
                            <X class="h-6 w-6" />
                        </button>
                    </div>
                    <form @submit.prevent="submitDemo" class="space-y-4">
                        <div>
                            <label class="mb-1 block text-xs font-bold text-slate-400 uppercase">School Name</label>
                            <Input v-model="demoForm.school_name" placeholder="Enter school name" required />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold text-slate-400 uppercase">Contact Person</label>
                            <Input v-model="demoForm.contact_person" placeholder="Enter your name" required />
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-xs font-bold text-slate-400 uppercase">Email Address</label>
                                <Input v-model="demoForm.email" type="email" placeholder="example@mail.com" required />
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold text-slate-400 uppercase">Phone Number</label>
                                <Input v-model="demoForm.phone" placeholder="+254..." required />
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold text-slate-400 uppercase">Anything else?</label>
                            <Textarea v-model="demoForm.message" placeholder="How can we help you?" />
                        </div>
                        <Button type="submit" :disabled="demoForm.processing" class="h-12 w-full rounded-full bg-blue-600 text-sm font-black tracking-widest shadow-lg shadow-blue-600/20">
                            <template v-if="demoForm.processing">
                                <Loader2 class="mr-2 h-4 w-4 animate-spin" /> SENDING...
                            </template>
                            <template v-else>SEND DEMO REQUEST</template>
                        </Button>
                    </form>
                </div>
            </div>
        </div>

        <Toast />

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
                             The simplest school system in Kenya. We help you manage your school without the headache.
                        </p>
                    </div>
                    <div>
                        <h5 class="mb-6 text-[11px] font-black tracking-[0.3em] text-slate-400 uppercase">Links</h5>
                        <ul class="space-y-4 text-sm font-bold text-slate-700 dark:text-slate-300">
                            <li><Link :href="route('modules')" class="transition-colors hover:text-blue-600">Our Modules</Link></li>
                            <li><Link :href="route('about')" class="transition-colors hover:text-blue-600">About Us</Link></li>
                            <li><Link :href="route('contact')" class="transition-colors hover:text-blue-600">Contact Us</Link></li>
                        </ul>
                    </div>
                </div>
                <div class="mt-20 flex flex-col items-center justify-between border-t border-slate-100 pt-10 dark:border-slate-900/50 md:flex-row">
                    <p class="text-[11px] font-bold text-slate-400 uppercase">© 2026 CBCCore Systems. Part of Doitix Tech Labs.</p>
                </div>
            </div>
        </footer>
    </div>
</template>
