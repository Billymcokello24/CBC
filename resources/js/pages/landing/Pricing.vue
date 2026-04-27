<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { 
    Check, 
    ArrowRight, 
    Zap, 
    Building2,
    GraduationCap
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import LandingLayout from '@/layouts/LandingLayout.vue';

const plans = [
    {
        name: 'Essential',
        price: 'KSh 15,000',
        duration: 'per term',
        description: 'Perfect for small private schools starting their digital journey.',
        features: [
            'Student Information Management',
            'Basic Academic Tracking',
            'Parent Portal Access',
            'Daily Attendance Logging',
            'Basic Report Generation',
            'Email Support'
        ],
        cta: 'Start Free Trial',
        popular: false,
        icon: GraduationCap
    },
    {
        name: 'Professional',
        price: 'KSh 35,000',
        duration: 'per term',
        description: 'Comprehensive features for growing schools with multiple streams.',
        features: [
            'Everything in Essential',
            'Full CBC Assessment Suite',
            'Mobile App Access (Staff/Parents)',
            'Finance & Fee Collection',
            'Inventory & Library Management',
            'Priority 24/7 Support',
            'Bulk SMS Integration'
        ],
        cta: 'Go Pro Now',
        popular: true,
        icon: Zap
    },
    {
        name: 'Institutional',
        price: 'Contact Us',
        duration: 'custom pricing',
        description: 'Customized solutions for large institutions and school groups.',
        features: [
            'Everything in Professional',
            'Multi-Campus Support',
            'Custom Branding',
            'API Data Integration',
            'Dedicated Account Manager',
            'On-Site Staff Training',
            'Advanced AI Analytics'
        ],
        cta: 'Contact Sales',
        popular: false,
        icon: Building2
    }
];

const route = (window as any).route;
</script>

<template>
    <Head title="Pricing Plans" />
    
    <LandingLayout>
        <main class="pt-32 pb-24 lg:pt-48 bg-white dark:bg-slate-950">
            <div class="container mx-auto px-6 max-w-7xl">
                <!-- Header -->
                <div class="max-w-3xl mx-auto mb-20">
                    <h2 class="text-blue-600 text-xs font-black tracking-[0.4em] uppercase mb-4">Investment Plans</h2>
                    <h1 class="text-5xl md:text-6xl font-bold tracking-tight text-slate-900 dark:text-white uppercase mb-6 leading-tight">
                        Transparent <br> <span class="text-blue-600">Institutional</span> Scale
                    </h1>
                    <p class="text-lg font-medium text-slate-500 dark:text-slate-400 capitalize tracking-wide max-w-2xl">
                        Select the plan that aligns with your school's vision. No hidden integration fees. No complex contracts.
                    </p>
                </div>

                <!-- Pricing Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-stretch">
                    <div 
                        v-for="plan in plans" 
                        :key="plan.name"
                        class="relative group"
                        :class="plan.popular ? 'lg:-mt-4' : ''"
                    >
                        <div 
                            class="relative h-full flex flex-col p-10 rounded-[40px] border transition-all duration-500 bg-[#F8F9FF] dark:bg-slate-900"
                            :class="plan.popular ? 'border-[#3B63F6] shadow-2xl' : 'border-slate-50 dark:border-slate-800 shadow-sm'"
                        >
                            <div v-if="plan.popular" class="absolute top-0 right-10 -translate-y-1/2 px-4 py-1.5 bg-[#3B63F6] text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-full shadow-lg">
                                Recommended
                            </div>

                            <div class="flex items-center gap-4 mb-8">
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white dark:bg-slate-800 shadow-sm transition-colors group-hover:bg-[#3B63F6] group-hover:text-white">
                                    <component :is="plan.icon" class="h-7 w-7" />
                                </div>
                                <h3 class="text-xl font-bold uppercase tracking-wider text-slate-900 dark:text-white">{{ plan.name }}</h3>
                            </div>

                            <div class="mb-8">
                                <div class="flex items-baseline gap-2">
                                    <span class="text-4xl font-bold tracking-tight text-slate-900 dark:text-white">{{ plan.price }}</span>
                                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">{{ plan.duration }}</span>
                                </div>
                                <p class="mt-4 text-sm font-medium text-slate-500 dark:text-slate-400 leading-relaxed uppercase tracking-wider">
                                    {{ plan.description }}
                                </p>
                            </div>

                            <div class="space-y-4 mb-12 flex-grow">
                                <div v-for="feature in plan.features" :key="feature" class="flex items-start gap-3 group/feat">
                                    <div class="mt-0.5 flex h-5 w-5 items-center justify-center rounded-full bg-blue-50 dark:bg-blue-500/10 text-[#3B63F6] transition-colors group-hover/feat:bg-[#3B63F6] group-hover/feat:text-white">
                                        <Check class="h-3 w-3 stroke-[4]" />
                                    </div>
                                    <span class="text-[12px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">{{ feature }}</span>
                                </div>
                            </div>

                            <Link :href="route('register')" class="w-full">
                                <Button 
                                    class="w-full h-16 rounded-2xl text-[11px] font-black uppercase tracking-[0.2em] transition-all"
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
                <div class="mt-24 p-16 rounded-[40px] bg-[#F8F9FF] dark:bg-slate-900 text-center relative overflow-hidden shadow-sm">
                    <h4 class="text-3xl font-bold uppercase tracking-tight text-slate-900 dark:text-white mb-4">Unsure which plan fits?</h4>
                    <p class="text-lg font-medium text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-10 max-w-xl mx-auto">
                        Schedule a discovery call with our specialist to map out your institution's digital transformation.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-6">
                        <Link :href="route('landing.contact')">
                            <Button class="h-16 px-12 rounded-2xl bg-[#3B63F6] text-white text-xs font-bold uppercase tracking-widest shadow-xl">
                                Book a Consultation
                            </Button>
                        </Link>
                        <span class="text-[11px] font-black uppercase tracking-[0.3em] text-slate-400">or call +254 756 865 033</span>
                    </div>
                </div>
            </div>
        </main>
    </LandingLayout>
</template>

