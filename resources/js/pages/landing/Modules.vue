<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    GraduationCap,
    Users,
    BookOpen,
    BarChart3,
    Shield,
    Zap,
    ArrowLeft,
    CheckCircle2,
    Calendar,
    MessageCircle,
    Layout,
    Wallet,
    FileSpreadsheet,
    Smartphone,
    Star
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

const moduleGroups = [
    {
        name: 'Administration & Management',
        items: [
            { icon: BarChart3, title: 'Strategic Dashboard', desc: 'A consolidated view of institutional metrics, enrollment trends, and academic performance indicators.' },
            { icon: Wallet, title: 'Fee & Billing', desc: 'Securely manage student accounts, generate invoices, and track payment histories with integrated reporting.' },
            { icon: Users, title: 'HR & Staffing', desc: 'Maintain detailed staff profiles, track attendance, and manage teacher-subject allocations efficiently.' }
        ]
    },
    {
        name: 'Academic Excellence',
        items: [
            { icon: FileSpreadsheet, title: 'Grading & Rubrics', desc: 'Customizable assessment tools designed specifically for the Kenyan CBC curriculum standards.' },
            { icon: Layout, title: 'Lesson Planning', desc: 'Digital repositories for schemes of work and lesson plans, allowing for easy collaboration among educators.' },
            { icon: CheckCircle2, title: 'Roll Call', desc: 'Real-time student attendance tracking with automated notifications for parents and supervisors.' }
        ]
    },
    {
        name: 'Communication & Engagement',
        items: [
            { icon: Smartphone, title: 'Parent Portal', desc: 'A dedicated interface for parents to monitor their child\'s progress, view report cards, and check fee balances.' },
            { icon: MessageCircle, title: 'Internal Messaging', desc: 'Facilitate clear communication between administration, staff, and parents via integrated messaging services.' },
            { icon: GraduationCap, title: 'Academic Portfolios', desc: 'Digital storage for student artifacts and achievements, creating a permanent record of the learning journey.' }
        ]
    }
];

const route = (window as any).route;
</script>

<template>
    <Head title="Our Modules - Cloud School System" />
    
    <div class="min-h-screen bg-slate-50 font-sans text-slate-900 transition-colors duration-500 dark:bg-[#020617] dark:text-white">
        <!-- Navigation -->
        <nav class="fixed top-0 right-0 left-0 z-50 border-b border-white/10 bg-white/70 backdrop-blur-2xl transition-all duration-300 dark:bg-slate-950/70">
            <div class="mx-auto max-w-7xl px-6">
                <div class="flex h-20 items-center justify-between">
                    <Link :href="route('home')" class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 shadow-lg shadow-blue-600/20">
                            <GraduationCap class="h-6 w-6 text-white" />
                        </div>
                        <div class="flex flex-col text-left">
                            <span class="text-lg font-black tracking-tight text-slate-900 dark:text-white leading-none">CLOUD SCHOOL</span>
                            <span class="text-[10px] font-bold text-blue-600 tracking-[0.2em] uppercase">System Components</span>
                        </div>
                    </Link>
                    <Link :href="route('home')" class="group flex items-center gap-2 text-[10px] font-black tracking-[0.2em] text-slate-500 uppercase hover:text-blue-600 transition-all">
                        <ArrowLeft class="h-4 w-4 transition-transform group-hover:-translate-x-1" />
                        RETURN HOME
                    </Link>
                </div>
            </div>
        </nav>

        <main class="pt-32 pb-24">
            <div class="mx-auto max-w-7xl px-6">
                <div class="mb-20 text-center lg:text-left">
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-indigo-200 bg-indigo-50/50 px-4 py-1.5 text-[10px] font-bold tracking-wider text-indigo-600 uppercase backdrop-blur-sm dark:border-indigo-500/20 dark:bg-indigo-500/10 dark:text-indigo-400">
                        <Zap class="h-3.5 w-3.5 fill-indigo-600/10" />
                        Platform Architecture
                    </div>
                    <h1 class="mb-6 text-4xl font-black leading-tight tracking-tight text-slate-900 sm:text-5xl lg:text-7xl dark:text-white uppercase">
                        Comprehensive <br> <span class="bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent italic">Institutional Tools</span>
                    </h1>
                    <p class="mx-auto lg:mx-0 max-w-xl text-base font-medium text-slate-500 dark:text-slate-400 uppercase tracking-widest leading-relaxed">
                        Explore the features designed to streamline every aspect of your school's administration and academic delivery.
                    </p>
                </div>

                <div class="space-y-32">
                    <div v-for="group in moduleGroups" :key="group.name">
                        <div class="mb-12 flex items-center gap-4">
                            <div class="h-[1px] flex-1 bg-slate-200 dark:bg-slate-800"></div>
                            <h2 class="text-[11px] font-black tracking-[0.4em] text-blue-600 uppercase">{{ group.name }}</h2>
                            <div class="h-[1px] flex-1 bg-slate-200 dark:bg-slate-800"></div>
                        </div>
                        <div class="grid gap-8 md:grid-cols-3">
                            <div v-for="item in group.items" :key="item.title" class="group relative overflow-hidden rounded-3xl border border-white bg-white/50 p-10 shadow-sm transition-all hover:-translate-y-2 hover:shadow-2xl dark:border-slate-800 dark:bg-slate-900/50 backdrop-blur-sm">
                                <div class="mb-8 flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-xl shadow-blue-600/20">
                                    <component :is="item.icon" class="h-7 w-7" />
                                </div>
                                <h3 class="mb-4 text-xl font-black tracking-tight uppercase">{{ item.title }}</h3>
                                <p class="text-sm leading-relaxed text-slate-500 dark:text-slate-400 font-medium">{{ item.desc }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <footer class="border-t border-slate-100 dark:border-slate-800 py-24 text-center bg-slate-50/30 dark:bg-slate-950/30 backdrop-blur-sm">
            <p class="text-[11px] font-black text-slate-400 uppercase tracking-[0.4em]">Ready to transform your school?</p>
            <div class="mt-10 flex flex-wrap justify-center gap-6">
                <Button size="lg" as-child class="h-14 rounded-2xl bg-blue-600 px-12 text-[11px] font-black tracking-[0.3em] shadow-2xl shadow-blue-600/20 transition-all hover:scale-105 active:scale-95">
                    <Link href="/register">START NOW</Link>
                </Button>
                <Link href="/contact" class="h-14 flex items-center px-12 text-[11px] font-black tracking-[0.3em] uppercase text-slate-500 hover:text-blue-600 transition-colors">
                    CONTACT SALES
                </Link>
            </div>
        </footer>
    </div>
</template>
