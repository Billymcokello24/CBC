<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { 
    HelpCircle, 
    MessageSquare, 
    ShieldCheck, 
    Zap,
    ChevronDown
} from 'lucide-vue-next';
import { ref } from 'vue';
import LandingLayout from '@/layouts/LandingLayout.vue';

const faqs = [
    {
        question: "How does the system handle CBC assessment?",
        answer: "Our system is built specifically for the Kenyan Competency-Based Curriculum. It features pre-configured learning areas, strands, and sub-strands for all grade levels. Teachers can record observations, upload student evidence (photos/videos), and generate automated progress reports that align with the latest KICD and KNEC requirements.",
        category: "Curriculum"
    },
    {
        question: "Is our school data secure on your cloud servers?",
        answer: "Security is our highest priority. We use industry-standard AES-256 encryption for all data at rest and SSL/TLS encryption for data in transit. Our servers are hosted on high-availability cloud infrastructure with daily automated backups and multi-layer firewall protection.",
        category: "Security"
    },
    {
        question: "Can parents access their children's progress reports?",
        answer: "Yes. Every parent is provided with secure login credentials to our Parent Portal. They can track their child's attendance, view graded assignments, download termly progress reports, and receive real-time notifications about school events or fee payments.",
        category: "Parent Portal"
    },
    {
        question: "How difficult is it to migrate from our current system?",
        answer: "Migration is seamless with our dedicated onboarding team. We provide bulk data import templates for students, staff, and historical records. Most institutions complete their full digital transition within 48 to 72 hours, including staff training.",
        category: "Onboarding"
    },
    {
        question: "Does the system work offline?",
        answer: "While the core system is cloud-based to ensure real-time synchronization across all devices, our mobile app supports offline attendance marking and assessment entry. Data automatically syncs once an internet connection is re-established.",
        category: "Technical"
    },
    {
        question: "What kind of training do you provide for teachers?",
        answer: "We offer comprehensive training sessions including on-site workshops, weekly webinars, and a library of video tutorials. Every school also gets a dedicated account manager who is available 24/7 to assist with any technical or operational queries.",
        category: "Support"
    }
];

const openIndex = ref<number | null>(0);

const toggle = (index: number) => {
    openIndex.value = openIndex.value === index ? null : index;
};
</script>

<template>
    <Head title="Frequently Asked Questions" />
    
    <LandingLayout>
        <main class="pt-32 pb-24 lg:pt-48 bg-white dark:bg-slate-950 min-h-screen">
            <div class="container mx-auto px-6 max-w-7xl">
                <!-- Header -->
                <div class="max-w-3xl mx-auto mb-20 text-left lg:text-center">
                    <h2 class="text-blue-600 text-xs font-black tracking-[0.4em] uppercase mb-4">Knowledge Base</h2>
                    <h1 class="text-5xl md:text-6xl font-bold tracking-tight text-slate-900 dark:text-white uppercase mb-6 leading-tight">
                        Common <br> <span class="text-blue-600">Queries</span> Answered
                    </h1>
                    <p class="text-lg font-medium text-slate-500 dark:text-slate-400 capitalize tracking-wide max-w-2xl mx-auto">
                        Everything you need to know about the Cloud School ecosystem and how we empower Kenyan institutions.
                    </p>
                </div>

                <!-- FAQ Accordion -->
                <div class="max-w-4xl mx-auto space-y-4">
                    <div 
                        v-for="(faq, index) in faqs" 
                        :key="index"
                        class="group"
                    >
                        <div 
                            class="rounded-[32px] border transition-all duration-500 overflow-hidden"
                            :class="openIndex === index 
                                ? 'border-[#3B63F6] bg-[#F8F9FF] dark:bg-blue-500/5 shadow-xl' 
                                : 'border-slate-50 dark:border-slate-800 hover:border-slate-200 dark:hover:border-slate-700 bg-transparent'"
                        >
                            <button 
                                @click="toggle(index)"
                                class="w-full flex items-center justify-between p-8 text-left outline-none"
                            >
                                <div class="flex items-center gap-6">
                                    <div 
                                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl transition-all duration-500"
                                        :class="openIndex === index ? 'bg-[#3B63F6] text-white shadow-lg' : 'bg-[#F8F9FF] dark:bg-slate-800 text-slate-400'"
                                    >
                                        <HelpCircle v-if="index % 3 === 0" class="h-5 w-5" />
                                        <ShieldCheck v-else-if="index % 3 === 1" class="h-5 w-5" />
                                        <Zap v-else class="h-5 w-5" />
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <span class="text-[10px] font-black uppercase tracking-[0.2em] text-[#3B63F6] opacity-60">{{ faq.category }}</span>
                                        <span class="text-base font-bold tracking-tight text-slate-900 dark:text-white uppercase">{{ faq.question }}</span>
                                    </div>
                                </div>
                                <div 
                                    class="h-10 w-10 flex items-center justify-center rounded-full border border-slate-100 dark:border-slate-800 transition-transform duration-500"
                                    :class="openIndex === index ? 'rotate-180 bg-[#3B63F6] border-[#3B63F6] text-white' : 'text-slate-400 rotate-0'"
                                >
                                    <ChevronDown class="h-4 w-4" />
                                </div>
                            </button>
                            
                            <div 
                                class="transition-all duration-500 ease-in-out overflow-hidden"
                                :style="{ 
                                    maxHeight: openIndex === index ? '500px' : '0px',
                                    opacity: openIndex === index ? '1' : '0'
                                }"
                            >
                                <div class="px-8 pb-8 pl-24 pr-16 border-t border-blue-100/50 dark:border-blue-950/30 pt-6">
                                    <p class="text-[12px] font-medium text-slate-600 dark:text-slate-400 leading-relaxed uppercase tracking-widest">
                                        {{ faq.answer }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Still have questions? -->
                <div class="mt-20 text-center">
                    <div class="inline-flex items-center gap-2 px-6 py-3 bg-[#F8F9FF] dark:bg-slate-900 rounded-full border border-slate-50 dark:border-slate-800 mb-8">
                        <MessageSquare class="h-4 w-4 text-blue-600" />
                        <span class="text-[11px] font-black uppercase tracking-[0.2em] text-slate-400">Still have questions? We're here to help.</span>
                    </div>
                    <div>
                        <Link :href="route('landing.contact')">
                            <button class="h-16 px-12 rounded-2xl bg-[#3B63F6] text-white text-xs font-bold uppercase tracking-[0.2em] shadow-2xl hover:scale-105 active:scale-95 transition-all">
                                Contact Support Team
                            </button>
                        </Link>
                    </div>
                </div>
            </div>
        </main>
    </LandingLayout>
</template>

