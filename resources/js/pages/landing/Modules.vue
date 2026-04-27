<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    GraduationCap,
    Users,
    BookOpen,
    BarChart3,
    Zap,
    CheckCircle2,
    MessageCircle,
    Layout,
    Wallet,
    FileSpreadsheet,
    Smartphone
} from 'lucide-vue-next';
import LandingLayout from '@/layouts/LandingLayout.vue';

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


</script>

<template>
    <Head title="Our Modules - Cloud School System" />
    
    <LandingLayout>
        <main class="pt-32 pb-24 lg:pt-48">
            <div class="container mx-auto px-6 max-w-7xl">
                <div class="mb-20">
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full px-4 py-1.5 text-[11px] font-bold tracking-wider text-blue-600 uppercase bg-blue-50 dark:bg-blue-500/10">
                        <Zap class="h-3.5 w-3.5" />
                        Platform Architecture
                    </div>
                    <h1 class="mb-6 text-4xl font-bold leading-tight tracking-tight text-slate-900 sm:text-5xl lg:text-7xl dark:text-white uppercase">
                        Comprehensive <br> <span class="text-blue-600">Institutional Tools</span>
                    </h1>
                    <p class="max-w-2xl text-lg font-medium text-slate-500 dark:text-slate-400 leading-relaxed uppercase tracking-widest">
                        Explore the features designed to streamline every aspect of your school's administration and academic delivery.
                    </p>
                </div>

                <div class="space-y-32">
                    <div v-for="group in moduleGroups" :key="group.name">
                        <div class="mb-12 flex items-center gap-4">
                            <h2 class="text-[12px] font-black tracking-[0.4em] text-blue-600 uppercase">{{ group.name }}</h2>
                            <div class="h-[1px] flex-1 bg-slate-100 dark:bg-slate-800"></div>
                        </div>
                        <div class="grid gap-8 md:grid-cols-3">
                            <div v-for="item in group.items" :key="item.title" class="group relative overflow-hidden rounded-[32px] bg-[#F8F9FF] dark:bg-slate-900 p-10 shadow-sm transition-all hover:-translate-y-2 hover:shadow-2xl">
                                <div class="mb-8 flex h-14 w-14 items-center justify-center rounded-2xl bg-[#3B63F6] text-white shadow-xl shadow-blue-600/20 group-hover:scale-110 transition-transform">
                                    <component :is="item.icon" class="h-7 w-7" />
                                </div>
                                <h3 class="mb-4 text-xl font-bold tracking-tight text-slate-900 dark:text-white uppercase">{{ item.title }}</h3>
                                <p class="text-sm leading-relaxed text-slate-500 dark:text-slate-400 font-medium">{{ item.desc }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </LandingLayout>
</template>

