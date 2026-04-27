<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { 
    Check, 
    ArrowRight, 
    Zap, 
    Building2,
    GraduationCap,
    School,
    Trophy
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import LandingLayout from '@/layouts/LandingLayout.vue';

const plans = [
    {
        name: 'Starter Bloom',
        range: '50 - 100 Learners',
        price: '100',
        duration: 'per learner / term',
        description: 'Optimized for small institutions beginning their digital transition.',
        features: [
            'Basic CBC Assessment',
            'Student Roll Management',
            'Parent Notification Portal',
            'Daily Attendance Tracking',
            'Termly Report Cards',
            'Standard Support'
        ],
        cta: 'Register School',
        popular: false,
        icon: GraduationCap
    },
    {
        name: 'Academic Scale',
        range: '100 - 500 Learners',
        price: '70',
        duration: 'per learner / term',
        description: 'Perfect for growing schools requiring robust data management.',
        features: [
            'Everything in Starter',
            'Multi-Stream Support',
            'Advanced Assessment Engine',
            'Finance & Fees Module',
            'Digital Assignments',
            'Priority Email Support'
        ],
        cta: 'Register School',
        popular: true,
        icon: Zap
    },
    {
        name: 'Institutional',
        range: '500 - 1000 Learners',
        price: '50',
        duration: 'per learner / term',
        description: 'Comprehensive features for large schools with intensive academic operations.',
        features: [
            'Everything in Scale',
            'Inventory Management',
            'Library System',
            'Transport & Logistics',
            'Detailed Performance Analytics',
            'Dedicated Account Rep'
        ],
        cta: 'Register School',
        popular: false,
        icon: School
    },
    {
        name: 'Enterprise Elite',
        range: 'Over 1000 Learners',
        price: '30',
        duration: 'per learner / term',
        description: 'Dynamic pricing for elite schools and large educational chains.',
        features: [
            'Everything in Institutional',
            'Multi-Campus Dashboard',
            'Custom System Branding',
            'Full API Access',
            'White-glove Onboarding',
            'Custom AI Insights'
        ],
        cta: 'Contact for Enterprise',
        popular: false,
        icon: Trophy
    }
];

const route = (window as any).route;
</script>

<template>
    <Head title="Institutional Pricing" />
    
    <LandingLayout>
        <main class="pt-32 pb-24 lg:pt-48 bg-white dark:bg-slate-950">
            <div class="container mx-auto px-6 max-w-7xl">
                <!-- Header -->
                <div class="max-w-4xl mx-auto mb-20 text-left lg:text-center">
                    <h2 class="text-blue-600 text-xs font-black tracking-[0.4em] uppercase mb-4">Scaling with You</h2>
                    <h1 class="text-5xl md:text-6xl font-bold tracking-tight text-slate-900 dark:text-white uppercase mb-6 leading-tight">
                        Learner-Based <br> <span class="text-blue-600">Dynamic</span> Pricing
                    </h1>
                    <p class="text-lg font-medium text-slate-500 dark:text-slate-400 capitalize tracking-wide max-w-2xl mx-auto">
                        Economies of scale built for the Kenyan context. The more your institution grows, the more cost-effective the platform becomes.
                    </p>
                </div>

                <!-- Pricing grid updated for 4 cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 items-stretch">
                    <div 
                        v-for="plan in plans" 
                        :key="plan.name"
                        class="relative group h-full flex flex-col"
                    >
                        <div 
                            class="relative h-full flex flex-col p-8 rounded-[40px] border transition-all duration-500 bg-[#F8F9FF] dark:bg-slate-900"
                            :class="plan.popular ? 'border-[#3B63F6] shadow-2xl xl:scale-105 z-10' : 'border-slate-50 dark:border-slate-800 shadow-sm'"
                        >
                            <div v-if="plan.popular" class="absolute top-0 right-10 -translate-y-1/2 px-4 py-1.5 bg-[#3B63F6] text-white text-[9px] font-black uppercase tracking-[0.2em] rounded-full shadow-lg">
                                Most Popular choice
                            </div>

                            <div class="mb-8">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white dark:bg-slate-800 shadow-sm transition-colors group-hover:bg-[#3B63F6] group-hover:text-white mb-6">
                                    <component :is="plan.icon" class="h-6 w-6" />
                                </div>
                                <h3 class="text-lg font-bold uppercase tracking-wider text-slate-900 dark:text-white mb-1">{{ plan.name }}</h3>
                                <span class="text-[10px] font-black text-blue-600 uppercase tracking-widest">{{ plan.range }}</span>
                            </div>

                            <div class="mb-8">
                                <div class="flex items-baseline gap-1">
                                    <span class="text-[14px] font-bold text-slate-400 uppercase tracking-tighter">Ksh</span>
                                    <span class="text-5xl font-bold tracking-tight text-slate-900 dark:text-white">{{ plan.price }}</span>
                                </div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mt-1">{{ plan.duration }}</span>
                                <p class="mt-6 text-[12px] font-medium text-slate-500 dark:text-slate-400 leading-relaxed uppercase tracking-wider">
                                    {{ plan.description }}
                                </p>
                            </div>

                            <div class="space-y-4 mb-10 flex-grow">
                                <div v-for="feature in plan.features" :key="feature" class="flex items-start gap-3 group/feat">
                                    <div class="mt-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-blue-50 dark:bg-blue-500/10 text-[#3B63F6] transition-colors group-hover/feat:bg-[#3B63F6] group-hover/feat:text-white shrink-0">
                                        <Check class="h-2.5 w-2.5 stroke-[4]" />
                                    </div>
                                    <span class="text-[11px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">{{ feature }}</span>
                                </div>
                            </div>

                            <Link :href="route('register')" class="w-full">
                                <Button 
                                    class="w-full h-14 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] transition-all"
                                    :variant="plan.popular ? 'default' : 'outline'"
                                    :class="plan.popular ? 'bg-[#3B63F6] hover:bg-blue-700 shadow-xl' : 'border-slate-200 dark:border-slate-800 hover:bg-white dark:hover:bg-slate-800'"
                                >
                                    {{ plan.cta }}
                                    <ArrowRight class="ml-2 h-4 w-4" />
                                </Button>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Footer CTA -->
                <div class="mt-24 p-12 lg:p-20 rounded-[50px] bg-[#F8F9FF] dark:bg-slate-900 text-center relative overflow-hidden shadow-sm">
                    <h4 class="text-3xl font-bold uppercase tracking-tight text-slate-900 dark:text-white mb-4">Request Institutional Quote</h4>
                    <p class="text-lg font-medium text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-10 max-w-2xl mx-auto">
                        For larger datasets or campus groups, our pricing is even more flexible. Connect with our deployment team today.
                    </p>
                    <div class="flex flex-col md:flex-row items-center justify-center gap-6">
                        <Link :href="route('landing.contact')">
                            <Button class="h-16 px-12 rounded-2xl bg-[#3B63F6] text-white text-xs font-bold uppercase tracking-widest shadow-xl">
                                Request Custom Quote
                            </Button>
                        </Link>
                        <span class="text-[11px] font-black uppercase tracking-[0.3em] text-slate-400">Deployment Hotline: +254 756 865 033</span>
                    </div>
                </div>
            </div>
        </main>
    </LandingLayout>
</template>


