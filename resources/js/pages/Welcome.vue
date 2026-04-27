<script setup lang="ts">
import { Head, Link, usePage, useForm } from '@inertiajs/vue3';
import {
    GraduationCap,
    ArrowRight,
    CheckCircle2,
    BarChart3,
    Zap,
    Heart,
    ClipboardCheck,
    Layout,
    BookOpen,
    MessageCircle,
    Globe,
    Star,
    X
} from 'lucide-vue-next';
import { ref, onMounted } from 'vue';
import { Button } from '@/components/ui/button';
import LandingLayout from '@/layouts/LandingLayout.vue';

defineProps<{ canLogin?: boolean; canRegister?: boolean }>();

const page = usePage<any>();
const isLoaded = ref(false);
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
    <Head title="Welcome - Cloud School System" />

    <LandingLayout>
        <!-- Hero Section -->
        <section class="relative pt-32 pb-24 lg:pt-48 lg:pb-32 overflow-hidden bg-gradient-to-br from-[#EEF2FF] to-white dark:from-slate-950 dark:to-slate-900">
            <!-- Background Decorative Elements -->
            <div class="absolute top-0 right-0 -translate-y-1/4 translate-x-1/4 w-[600px] h-[600px] bg-blue-400/10 blur-[120px] rounded-full"></div>
            <div class="absolute bottom-0 left-0 translate-y-1/4 -translate-x-1/4 w-[400px] h-[400px] bg-indigo-400/10 blur-[100px] rounded-full"></div>

            <div class="container mx-auto px-6 max-w-7xl relative z-10">
                <div class="grid lg:grid-cols-2 items-center gap-16 lg:gap-24">
                    <!-- Text Content -->
                    <div 
                        class="transition-all duration-1000 transform"
                        :class="isLoaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'"
                    >
                        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-blue-50 border border-blue-100 mb-8 dark:bg-blue-500/10 dark:border-blue-500/20">
                            <Zap class="h-4 w-4 text-blue-600" />
                            <span class="text-[13px] font-bold text-blue-600 tracking-tight uppercase">Transforming Education in Kenya</span>
                        </div>
                        
                        <h1 class="text-5xl lg:text-7xl font-bold text-slate-900 dark:text-white tracking-tight mb-8 leading-[1.1]">
                            CBC School <br>
                            Management System
                        </h1>
                        
                        <p class="text-lg text-slate-600 dark:text-slate-400 leading-relaxed mb-10 max-w-lg">
                            Transform education with a comprehensive digital platform designed specifically to align with Kenya's Competency-Based Curriculum. Support skills, competencies, and holistic development beyond traditional exam performance.
                        </p>

                        <div class="flex flex-wrap items-center gap-4">
                            <Button @click="showDemoModal = true" class="h-14 px-10 rounded-2xl bg-[#3B63F6] hover:bg-blue-700 text-white text-sm font-bold shadow-2xl shadow-blue-600/30 group transition-all">
                                Request Demo
                                <ArrowRight class="ml-2 h-5 w-5 transition-transform group-hover:translate-x-1" />
                            </Button>
                            
                            <Link :href="route('landing.about')">
                                <Button variant="outline" class="h-14 px-10 rounded-2xl border-[#DDE4FF] bg-[#EEF2FF] hover:bg-[#DDE4FF] text-[#3B63F6] text-sm font-bold transition-all border-none shadow-sm">
                                    Learn More
                                </Button>
                            </Link>
                        </div>
                    </div>

                    <!-- Image Content -->
                    <div 
                        class="relative transition-all duration-1000 delay-300 transform"
                        :class="isLoaded ? 'opacity-100 scale-100' : 'opacity-0 scale-95'"
                    >
                        <!-- Main Image -->
                        <div class="relative z-10 rounded-[40px] overflow-hidden shadow-2xl border-8 border-white/50 dark:border-slate-800/50 backdrop-blur-sm">
                            <img 
                                src="https://images.unsplash.com/photo-1503676260728-1c00da094a0b?q=80&w=2000&auto=format&fit=crop" 
                                alt="Students in Classroom"
                                class="w-full aspect-[4/3] object-cover"
                            />
                        </div>

                        <!-- Floating Stat Card -->
                        <div class="absolute -bottom-8 -left-12 z-20 animate-bounce-slow">
                            <div class="bg-white dark:bg-slate-900 rounded-[28px] p-6 shadow-2xl border border-slate-100 dark:border-slate-800 flex items-center gap-5 min-w-[240px]">
                                <div class="h-14 w-14 rounded-2xl bg-[#10B981] flex items-center justify-center text-white shadow-lg shadow-emerald-500/20">
                                    <BarChart3 class="h-8 w-8" />
                                </div>
                                <div>
                                    <h4 class="text-2xl font-bold text-slate-900 dark:text-white">500+</h4>
                                    <p class="text-sm font-medium text-slate-500">Schools Using CBC</p>
                                </div>
                            </div>
                        </div>

                        <!-- Decorative Blobs -->
                        <div class="absolute -top-10 -right-10 w-40 h-40 bg-blue-400/20 rounded-full blur-3xl animate-pulse"></div>
                        <div class="absolute -bottom-10 -right-20 w-60 h-60 bg-indigo-400/20 rounded-full blur-3xl"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Partners -->
        <section class="py-12 bg-white dark:bg-slate-900/30 border-y border-slate-50 dark:border-slate-800">
            <div class="container mx-auto px-6">
                <p class="text-center text-[11px] font-bold tracking-[0.3em] text-slate-400 uppercase mb-10">Trusted & Aligned with Kenyan Standards</p>
                <div class="flex flex-wrap items-center justify-center gap-12 md:gap-24 grayscale opacity-40 hover:opacity-100 hover:grayscale-0 transition-all duration-700">
                    <span v-for="p in partners" :key="p.name" class="text-2xl font-black text-slate-900 dark:text-white tracking-tighter">{{ p.logo }}</span>
                </div>
            </div>
        </section>

        <!-- Mission Insight -->
        <section class="py-24 lg:py-32 bg-white dark:bg-slate-950">
            <div class="container mx-auto px-6 max-w-7xl text-center">
                <div class="max-w-3xl mx-auto space-y-8">
                    <div class="text-blue-600 text-xs font-black tracking-[0.4em] uppercase mb-4">The CBC Standard</div>
                    <h2 class="text-4xl lg:text-5xl font-bold text-slate-900 dark:text-white tracking-tight leading-tight">
                        Guiding the Shift from 8-4-4 to <br> <span class="text-blue-600 italic">Curriculum Excellence</span>
                    </h2>
                    <p class="text-lg text-slate-600 dark:text-slate-400 leading-relaxed font-medium">
                        As schools shift from the traditional 8-4-4 system to CBC, there is an increasing need for systems that can effectively support this new model of learning. Cloud School is at the center of this transformation.
                    </p>
                    <div class="pt-8 grid grid-cols-2 md:grid-cols-4 gap-8">
                        <div v-for="item in ['Skills-Based', 'Holistic Tracking', 'Real-time Analytics', 'Secure Portals']" :key="item" class="flex flex-col items-center gap-3">
                            <div class="h-12 w-12 rounded-2xl bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center text-blue-600">
                                <CheckCircle2 class="h-6 w-6" />
                            </div>
                            <span class="text-[12px] font-bold text-slate-900 dark:text-white uppercase tracking-widest">{{ item }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Grid -->
        <section class="py-24 lg:py-32 bg-[#F8F9FF] dark:bg-slate-900/50">
            <div class="container mx-auto px-6 max-w-7xl">
                <div class="mb-20">
                    <h2 class="text-3xl lg:text-4xl font-bold text-slate-900 dark:text-white tracking-tight mb-4">Core Ecosystem Features</h2>
                    <p class="text-slate-500 max-w-2xl font-medium">Everything you need to digitize your institution's operations while maintaining full CBC compliance.</p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div 
                        v-for="f in features" 
                        :key="f.title"
                        class="p-8 rounded-[32px] bg-white dark:bg-slate-900 shadow-sm hover:shadow-2xl transition-all duration-500 group border border-transparent hover:border-blue-100 dark:hover:border-blue-900/30"
                    >
                        <div :class="['h-14 w-14 rounded-2xl flex items-center justify-center text-white mb-8 shadow-lg group-hover:scale-110 transition-transform shadow-blue-600/10', f.color]">
                            <component :is="f.icon" class="h-7 w-7" />
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4 tracking-tight">{{ f.title }}</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed font-medium">{{ f.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Final CTA -->
        <section class="py-24 lg:py-40 bg-[#3B63F6] relative overflow-hidden">
            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 48px 48px;"></div>
            <div class="container mx-auto px-6 text-center relative z-10">
                <div class="h-20 w-20 rounded-[28px] bg-white/20 backdrop-blur-xl border border-white/30 flex items-center justify-center text-white mx-auto mb-10 shadow-2xl">
                    <GraduationCap class="h-10 w-10" />
                </div>
                <h2 class="text-4xl lg:text-6xl font-bold text-white tracking-tight mb-10 uppercase">Transform Your <br> <span class="italic text-white/70">Institution Today</span></h2>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-6">
                    <Link :href="route('register')">
                        <Button class="h-16 px-12 rounded-2xl bg-white text-[#3B63F6] hover:bg-white/90 text-sm font-bold shadow-2xl shadow-black/10">
                            Start 14-Day Free Trial
                        </Button>
                    </Link>
                    <Button @click="showDemoModal = true" variant="outline" class="h-16 px-12 rounded-2xl border-white/40 text-white hover:bg-white/10 text-sm font-bold backdrop-blur-md">
                        Request Group Demo
                    </Button>
                </div>
            </div>
        </section>

        <!-- Demo Modal -->
        <div v-if="showDemoModal" class="fixed inset-0 z-[200] flex items-center justify-center p-4">
            <div @click="showDemoModal = false" class="absolute inset-0 bg-slate-950/60 backdrop-blur-md animate-in fade-in duration-300"></div>
            <div class="relative w-full max-w-lg bg-white dark:bg-slate-900 rounded-[40px] p-10 shadow-3xl border border-slate-100 dark:border-slate-800 animate-in zoom-in-95 duration-500">
                <button @click="showDemoModal = false" class="absolute top-8 right-8 p-2 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <X class="h-6 w-6 text-slate-400" />
                </button>
                
                <div class="mb-10">
                    <div class="h-12 w-12 rounded-2xl bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center text-blue-600 mb-6">
                        <MessageCircle class="h-6 w-6" />
                    </div>
                    <h2 class="text-3xl font-bold text-slate-900 dark:text-white tracking-tight">Experience Cloud School</h2>
                    <p class="text-slate-500 font-medium mt-2">See how we can modernize your administration in 30 minutes.</p>
                </div>

                <form @submit.prevent="submitDemo" class="space-y-6">
                    <div class="grid gap-6 md:grid-cols-2">
                        <div class="space-y-2">
                            <label class="text-[11px] font-black uppercase tracking-widest text-slate-400 pl-1">School Name</label>
                            <input v-model="demoForm.school_name" type="text" class="h-14 w-full rounded-2xl bg-slate-50 dark:bg-slate-800 border-transparent focus:border-blue-500 focus:bg-white transition-all px-6 text-sm font-medium" placeholder="Ex: Nairobi Academy" required />
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-black uppercase tracking-widest text-slate-400 pl-1">Your Name</label>
                            <input v-model="demoForm.contact_person" type="text" class="h-14 w-full rounded-2xl bg-slate-50 dark:bg-slate-800 border-transparent focus:border-blue-500 focus:bg-white transition-all px-6 text-sm font-medium" placeholder="Ex: Jane Doe" required />
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[11px] font-black uppercase tracking-widest text-slate-400 pl-1">Work Email</label>
                        <input v-model="demoForm.email" type="email" class="h-14 w-full rounded-2xl bg-slate-50 dark:bg-slate-800 border-transparent focus:border-blue-500 focus:bg-white transition-all px-6 text-sm font-medium" placeholder="jane@school.co.ke" required />
                    </div>
                    <div class="space-y-2">
                        <label class="text-[11px] font-black uppercase tracking-widest text-slate-400 pl-1">Phone Number</label>
                        <input v-model="demoForm.phone" type="tel" class="h-14 w-full rounded-2xl bg-slate-50 dark:bg-slate-800 border-transparent focus:border-blue-500 focus:bg-white transition-all px-6 text-sm font-medium" placeholder="+254..." required />
                    </div>
                    <Button :disabled="demoForm.processing" class="h-16 w-full rounded-2xl bg-[#3B63F6] hover:bg-blue-700 text-white text-sm font-bold shadow-2xl shadow-blue-600/20 mt-4">
                        {{ demoForm.processing ? 'Scheduling...' : 'Reserve demo slot' }}
                    </Button>
                </form>
            </div>
        </div>
    </LandingLayout>
</template>

<style scoped>
@keyframes bounce-slow {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-20px); }
}
.animate-bounce-slow {
    animation: bounce-slow 4s ease-in-out infinite;
}
</style>

