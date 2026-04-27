<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    GraduationCap,
    Users,
    BookOpen,
    BarChart3,
    Shield,
    Zap,
    ArrowRight,
    Menu,
    X,
    LayoutDashboard,
    ClipboardCheck,
    Wallet,
    Calendar,
    ChevronRight,
} from 'lucide-vue-next';
import { ref, onMounted, onUnmounted } from 'vue';
import { Button } from '@/components/ui/button';

defineProps<{ canLogin?: boolean; canRegister?: boolean }>();

const mobileMenuOpen = ref(false);
const scrollY = ref(0);

const updateScroll = () => {
    scrollY.value = window.scrollY;
};

onMounted(() => {
    window.addEventListener('scroll', updateScroll);
});

onUnmounted(() => {
    window.removeEventListener('scroll', updateScroll);
});

const features = [
    {
        icon: LayoutDashboard,
        title: 'Centralized Dashboard',
        description: 'View real-time school metrics, learner performance, and administrative tasks in one sleek interface.',
    },
    {
        icon: ClipboardCheck,
        title: 'CBC Assessment Engine',
        description: 'Automate competency-based grading with built-in rubrics designed specifically for the Kenya curriculum.',
    },
    {
        icon: Wallet,
        title: 'Smart Finance',
        description: 'Manage fees, payroll, and expenses with automated invoicing and M-Pesa integration.',
    },
    {
        icon: Calendar,
        title: 'Academic Planner',
        description: 'Effortlessly manage timetables, lesson plans, and school events with our dynamic scheduling tool.',
    },
];

const partners = [
    { name: 'Nairobi Academy', logo: 'https://cdn.worldvectorlogo.com/logos/anchorage-digital.svg' },
    { name: 'Hillcrest Prep', logo: 'https://cdn.worldvectorlogo.com/logos/angellist-1.svg' },
    { name: 'Riara Schools', logo: 'https://cdn.worldvectorlogo.com/logos/capital-one-2.svg' },
    { name: 'Brookhouse', logo: 'https://cdn.worldvectorlogo.com/logos/algolia-1.svg' },
];
</script>

<template>
    <Head title="Transforming Education - CBC Learning Platform" />

    <div class="min-h-screen bg-[#020202] text-white selection:bg-primary selection:text-white dark">
        <!-- Promotional Banner -->
        <div class="bg-[#0a0a0a] border-b border-white/5 py-2.5 text-center">
            <p class="text-[11px] font-medium tracking-tight text-white/60">
                New Feature: AI-Powered Performance Analytics is here! 
                <a href="#" class="ml-1 text-white hover:underline transition-all">Read more <ArrowRight class="inline h-3 w-3" /></a>
            </p>
        </div>

        <!-- Navigation -->
        <nav 
            class="sticky top-0 z-[100] transition-all duration-300 border-b border-transparent"
            :class="{ 'bg-black/80 backdrop-blur-xl border-white/5': scrollY > 20 }"
        >
            <div class="mx-auto max-w-[1400px] px-6 lg:px-12">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center gap-10">
                        <Link href="/" class="flex items-center gap-2 group transition-transform active:scale-95">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-white">
                                <GraduationCap class="h-5 w-5 text-black" />
                            </div>
                            <span class="text-lg font-black tracking-tighter uppercase italic">CBC.PLATFORM</span>
                        </Link>

                        <div class="hidden items-center gap-10 md:flex">
                            <a href="#features" class="text-xs font-bold tracking-widest uppercase text-white/50 hover:text-white transition-colors">Features</a>
                            <a href="#solutions" class="text-xs font-bold tracking-widest uppercase text-white/50 hover:text-white transition-colors">Solutions</a>
                            <a href="#demo" class="text-xs font-bold tracking-widest uppercase text-white/50 hover:text-white transition-colors">Demo</a>
                            <a href="#pricing" class="text-xs font-bold tracking-widest uppercase text-white/50 hover:text-white transition-colors">Pricing</a>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <template v-if="canLogin">
                            <Link 
                                href="/login" 
                                class="hidden sm:block text-[10px] font-black tracking-[0.2em] uppercase text-white/80 hover:text-white transition-colors"
                            >
                                Sign In
                            </Link>
                        </template>
                        <Button 
                            as-child 
                            class="rounded-full bg-white px-8 text-[11px] font-black tracking-widest uppercase text-black hover:bg-slate-200 transition-all shadow-[0_0_20px_rgba(255,255,255,0.1)]"
                        >
                            <Link href="/register">Get Started</Link>
                        </Button>
                        <button class="md:hidden text-white/80" @click="mobileMenuOpen = !mobileMenuOpen">
                            <Menu v-if="!mobileMenuOpen" class="h-6 w-6" />
                            <X v-else class="h-6 w-6" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile menu -->
            <transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="opacity-0 -translate-y-4"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-4"
            >
                <div v-if="mobileMenuOpen" class="absolute inset-x-0 bg-black border-b border-white/5 p-8 md:hidden shadow-2xl">
                    <div class="flex flex-col gap-6">
                        <a href="#features" class="text-lg font-bold tracking-tight" @click="mobileMenuOpen = false">Features</a>
                        <a href="#solutions" class="text-lg font-bold tracking-tight" @click="mobileMenuOpen = false">Solutions</a>
                        <a href="#pricing" class="text-lg font-bold tracking-tight" @click="mobileMenuOpen = false">Pricing</a>
                        <div class="h-px bg-white/10 my-2"></div>
                        <Link href="/login" class="text-lg font-bold tracking-tight">Sign In</Link>
                    </div>
                </div>
            </transition>
        </nav>

        <!-- Hero Section -->
        <main class="relative overflow-hidden pt-20 pb-32 lg:pt-32 lg:pb-48">
            <!-- Background Glows -->
            <div class="absolute -top-48 right-0 -z-10 h-[600px] w-[600px] bg-primary/20 blur-[160px] opacity-20 pointer-events-none"></div>
            <div class="absolute top-1/2 left-0 -z-10 h-[400px] w-[400px] bg-blue-600/20 blur-[140px] opacity-10 pointer-events-none"></div>

            <div class="mx-auto max-w-[1400px] px-6 lg:px-12">
                <div class="flex flex-col lg:flex-row items-center gap-20">
                    <!-- Text Content -->
                    <div class="flex-1 text-left">
                        <div class="mb-10 inline-flex items-center gap-3 rounded-full bg-white/5 border border-white/10 px-4 py-1.5 backdrop-blur-sm shadow-xl animate-in fade-in slide-in-from-bottom-4 duration-1000">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-primary"></span>
                            </span>
                            <span class="text-[10px] font-black tracking-[0.2em] uppercase text-white/90">Enterprise Learning v2.0 is live</span>
                        </div>

                        <h1 class="mb-10 text-5xl font-bold tracking-[-0.04em] leading-[0.95] sm:text-7xl xl:text-[5.5rem] animate-in fade-in slide-in-from-bottom-8 duration-1000 delay-100 italic font-black uppercase">
                            Modernized <br /> 
                            <span class="text-white/30 italic">Education Management.</span>
                        </h1>

                        <p class="mb-12 max-w-xl text-lg lg:text-xl font-medium leading-relaxed text-white/50 animate-in fade-in slide-in-from-bottom-12 duration-1000 delay-200">
                            The comprehensive, cloud-native platform designed for Kenya's Competency-Based Curriculum. Streamline administration and amplify learning outcomes in days, not months.
                        </p>

                        <div class="flex flex-col sm:flex-row items-center gap-6 animate-in fade-in slide-in-from-bottom-16 duration-1000 delay-300">
                            <Button class="rounded-full bg-white px-10 py-7 text-[12px] font-black tracking-widest uppercase text-black hover:bg-slate-200 transition-all shadow-[0_0_40px_rgba(255,255,255,0.15)] active:scale-95 group">
                                <Link href="/register" class="flex items-center gap-2">
                                    Start Full Trial 
                                    <ArrowRight class="h-4 w-4 group-hover:translate-x-1 transition-transform" />
                                </Link>
                            </Button>
                            <a href="#demo" class="group flex items-center gap-2 text-[10px] font-black tracking-[0.3em] uppercase text-white/80 hover:text-white transition-all">
                                Watch Tutorial
                                <ChevronRight class="h-3.5 w-3.5 group-hover:translate-x-1 transition-transform" />
                            </a>
                        </div>

                        <!-- Trust Logos -->
                        <div class="mt-24 animate-in fade-in duration-1000 delay-500">
                            <p class="mb-8 text-[10px] font-black tracking-[0.3em] uppercase text-white/30">Trusted by leading kenyan institutions:</p>
                            <div class="flex flex-wrap gap-x-12 gap-y-8 grayscale opacity-50 contrast-125 transition-all hover:grayscale-0 hover:opacity-100">
                                <template v-for="partner in partners" :key="partner.name">
                                    <img :src="partner.logo" :alt="partner.name" class="h-6 w-auto brightness-200" title="partner.name">
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Visual Section -->
                    <div class="relative flex-1 animate-in zoom-in fade-in duration-1000 delay-300">
                        <!-- Background Orb -->
                        <div class="absolute -inset-20 rounded-full bg-gradient-to-br from-primary/20 to-blue-600/20 blur-[100px] opacity-30 animate-pulse"></div>

                        <!-- Main Phone Frame -->
                        <div class="relative mx-auto w-full max-w-[340px] perspective-[1000px] rotate-y-[-10deg] rotate-x-[5deg]">
                            <div class="relative rounded-[2.5rem] border-[8px] border-[#18181b] bg-[#0a0a0a] shadow-[0_50px_100px_-20px_rgba(0,0,0,0.8)] overflow-hidden aspect-[9/19.5]">
                                <div class="absolute top-0 right-0 left-0 h-8 bg-black/40 backdrop-blur-md flex items-center justify-between px-6 z-10">
                                    <span class="text-[10px] font-bold">9:41</span>
                                    <div class="flex items-center gap-1.5">
                                        <div class="h-2 w-2 rounded-full bg-white/40"></div>
                                        <div class="h-2 w-1.5 rounded-sm bg-white/60"></div>
                                    </div>
                                </div>

                                <!-- Screen Content Simulation -->
                                <div class="p-6 pt-12 space-y-6">
                                    <div class="space-y-1">
                                        <p class="text-[10px] uppercase tracking-widest text-white/40">Principal View</p>
                                        <h3 class="text-lg font-bold">Welcome, Dr. Kamau</h3>
                                    </div>

                                    <div class="rounded-2xl bg-white/5 border border-white/10 p-4 space-y-4">
                                        <div class="flex items-center justify-between">
                                            <p class="text-[9px] uppercase tracking-widest font-black text-white/40">Revenue Growth</p>
                                            <span class="text-[9px] text-emerald-400">+12.4%</span>
                                        </div>
                                        <div class="flex items-end gap-2 h-12">
                                            <div v-for="h in [0.4, 0.7, 0.5, 0.9, 0.6, 0.8]" :key="h" class="flex-1 bg-primary rounded-sm" :style="{ height: (h * 100) + '%' }"></div>
                                        </div>
                                    </div>

                                    <div class="space-y-3">
                                        <p class="text-[9px] uppercase tracking-widest font-black text-white/40">Recent Activities</p>
                                        <div v-for="i in [1, 2, 3]" :key="i" class="flex items-center gap-3 p-2.5 rounded-xl bg-white/5 border border-white/5">
                                            <div class="h-8 w-8 rounded-lg bg-white/10 flex items-center justify-center">
                                                <Users class="h-4 w-4" />
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-[10px] font-bold truncate">Grade 4 Admission</p>
                                                <p class="text-[8px] text-white/40">2 mins ago</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Dynamic Floating Element -->
                                <div class="absolute bottom-10 left-[-40px] right-[-40px] rotate-3 animate-bounce">
                                    <div class="mx-auto w-[200px] rounded-2xl bg-white p-4 shadow-2xl flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-xl bg-black flex items-center justify-center">
                                            <Zap class="h-5 w-5 text-white" />
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-black uppercase text-black italic">Live Track</p>
                                            <p class="text-[12px] font-bold text-black/60 tracking-tight">System Online</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Features Grid -->
        <section id="features" class="py-32 bg-[#050505] relative overflow-hidden">
             <div class="mx-auto max-w-[1400px] px-6 lg:px-12">
                <div class="flex flex-col lg:flex-row justify-between items-end gap-10 mb-20">
                    <div class="max-w-2xl">
                        <h2 class="text-4xl lg:text-6xl font-black uppercase tracking-[-0.05em] leading-[0.9] italic mb-6">
                            Tailored for <br />
                            <span class="text-white/30 italic">Modern Schools.</span>
                        </h2>
                        <p class="text-lg text-white/40 font-medium max-w-lg leading-relaxed">
                            Every feature is built with the Kenyan educator in mind — simple enough for anyone, powerful enough for the whole school.
                        </p>
                    </div>
                    <Button variant="outline" class="rounded-full border-white/10 bg-white/5 px-8 text-[10px] font-black tracking-widest uppercase hover:bg-white hover:text-black transition-all">Explore Documentation</Button>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-2">
                    <div 
                        v-for="feature in features" 
                        :key="feature.title"
                        class="p-10 bg-[#0a0a0a] border border-white/5 rounded-3xl group hover:bg-primary transition-all duration-500 hover:scale-[1.02] hover:-translate-y-2 cursor-pointer"
                    >
                        <div class="h-12 w-12 rounded-2xl bg-white/5 flex items-center justify-center mb-8 group-hover:bg-white/20 transition-colors">
                            <component :is="feature.icon" class="h-6 w-6 text-white" />
                        </div>
                        <h3 class="text-xl font-bold mb-4 italic uppercase tracking-tight">{{ feature.title }}</h3>
                        <p class="text-sm leading-relaxed text-white/40 group-hover:text-white/80 transition-colors">{{ feature.description }}</p>
                    </div>
                </div>
             </div>
        </section>

        <!-- Interactive Quote / Stat Section -->
        <section class="py-32 relative overflow-hidden">
            <div class="absolute inset-0 bg-primary/5 -z-10"></div>
            <div class="mx-auto max-w-[1000px] px-6 text-center">
                <h3 class="text-3xl lg:text-5xl font-medium leading-tight mb-12">
                    "Since switching to CBC Platform, our administrative workload has dropped by <span class="italic text-primary font-black">70%</span>, allowing our teachers to focus solely on the learners."
                </h3>
                <div class="flex items-center justify-center gap-4">
                    <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?q=80&w=1976&auto=format&fit=crop" class="h-12 w-12 rounded-full border-2 border-white shadow-xl" />
                    <div class="text-left">
                        <p class="text-sm font-bold uppercase italic tracking-widest">Sister Mary Wambui</p>
                        <p class="text-[10px] font-black uppercase text-white/40 tracking-widest leading-none">Head Teacher, St. Peters Academy</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Final CTA -->
        <section class="pb-32 px-6">
            <div class="mx-auto max-w-[1400px] bg-gradient-to-br from-white/10 to-transparent border border-white/5 rounded-[3rem] p-12 lg:p-32 text-center relative overflow-hidden group">
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-primary/20 blur-[150px] opacity-20 -z-10 transition-all duration-1000 group-hover:scale-150"></div>
                
                <h2 class="text-5xl lg:text-[7rem] font-black uppercase leading-[0.85] tracking-[-0.06em] italic mb-12">
                    Start Your <br />
                    <span class="bg-gradient-to-r from-primary to-blue-400 bg-clip-text text-transparent">Transformation.</span>
                </h2>
                
                <div class="flex flex-col sm:flex-row items-center justify-center gap-8">
                    <Button class="rounded-full bg-white px-12 py-8 text-[12px] font-black tracking-[0.3em] uppercase text-black hover:bg-slate-200 transition-all shadow-2xl hover:scale-110 active:scale-95">
                        <Link href="/register">Register Institution</Link>
                    </Button>
                    <a href="#contact" class="text-[11px] font-black tracking-[0.4em] uppercase text-white/40 hover:text-white transition-all">Support & Licensing</a>
                </div>
            </div>
        </section>

        <!-- Minimalist Footer -->
        <footer class="py-20 border-t border-white/5">
            <div class="mx-auto max-w-[1400px] px-6 lg:px-12">
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-12">
                    <div class="col-span-2">
                        <div class="flex items-center gap-2 mb-6">
                            <div class="flex h-6 w-6 items-center justify-center rounded bg-white">
                                <GraduationCap class="h-4 w-4 text-black" />
                            </div>
                            <span class="text-sm font-black tracking-tighter uppercase italic">CBC.PLATFORM</span>
                        </div>
                        <p class="text-xs text-white/30 leading-relaxed max-w-xs">
                            Elevating Kenyan education through high-fidelity digital solutions. Built by and for educators.
                        </p>
                    </div>
                    <div>
                        <h4 class="text-[10px] font-black uppercase tracking-[0.3em] text-white/20 mb-6">Product</h4>
                        <ul class="space-y-3 text-[11px] font-bold tracking-tight text-white/50">
                            <li><a href="#" class="hover:text-white transition-colors">Features</a></li>
                            <li><a href="#" class="hover:text-white transition-colors">Assessment Engine</a></li>
                            <li><a href="#" class="hover:text-white transition-colors">Finance Pro</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-[10px] font-black uppercase tracking-[0.3em] text-white/20 mb-6">Company</h4>
                        <ul class="space-y-3 text-[11px] font-bold tracking-tight text-white/50">
                            <li><a href="#" class="hover:text-white transition-colors">Our Vision</a></li>
                            <li><a href="#" class="hover:text-white transition-colors">Careers</a></li>
                            <li><a href="#" class="hover:text-white transition-colors">Contact</a></li>
                        </ul>
                    </div>
                </div>
                <div class="mt-20 pt-10 border-t border-white/5 flex flex-col sm:flex-row justify-between gap-6">
                    <p class="text-[10px] font-bold text-white/20 lowercase tracking-widest">© 2026 CBC Platform — Designed with precision by Antigravity AI.</p>
                    <div class="flex gap-8 text-[10px] font-black uppercase tracking-widest text-white/20">
                        <a href="#" class="hover:text-white transition-colors">Privacy</a>
                        <a href="#" class="hover:text-white transition-colors">Terms</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.perspective-\[1000px\] {
    perspective: 1000px;
}
.rotate-y-\[-10deg\] {
    transform: rotateY(-10deg);
}
.rotate-x-\[5deg\] {
    transform: rotateX(5deg);
}

@keyframes float {
    0%, 100% { transform: translateY(0) rotate(3deg); }
    50% { transform: translateY(-10px) rotate(3deg); }
}

.animate-float {
    animation: float 6s ease-in-out infinite;
}

@keyframes pulse-glow {
    0%, 100% { opacity: 0.3; }
    50% { opacity: 0.5; }
}

.animate-pulse {
    animation: pulse-glow 4s ease-in-out infinite;
}

/* Custom In-animations */
.animate-in {
    animation-fill-mode: forwards;
}

@keyframes fade-in-up {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes zoom-in {
    from { opacity: 0; scale: 0.95; }
    to { opacity: 1; scale: 1; }
}

.fade-in { animation: fade-in-up 1s ease-out; }
.zoom-in { animation: zoom-in 1.2s cubic-bezier(0.16, 1, 0.3, 1); }
</style>
