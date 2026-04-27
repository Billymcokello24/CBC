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
    Smartphone
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
    <Head title="Our Modules - CBC Core" />
    
    <div class="min-h-screen bg-slate-50 font-sans text-slate-900 transition-colors duration-500 dark:bg-[#020617] dark:text-white">
        <!-- Minimal Nav -->
        <nav class="border-b bg-white/80 backdrop-blur-xl dark:border-slate-800 dark:bg-[#020617]/80">
            <div class="mx-auto max-w-7xl px-6">
                <div class="flex h-16 items-center justify-between">
                    <Link :href="route('home')" class="flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600 shadow-sm">
                            <GraduationCap class="h-5 w-5 text-white" />
                        </div>
                        <span class="text-lg font-bold tracking-tight uppercase">CBC<span class="text-blue-600">Core</span></span>
                    </Link>
                    <Link :href="route('home')" class="group flex items-center gap-2 text-[10px] font-bold tracking-widest text-slate-500 uppercase hover:text-blue-600">
                        <ArrowLeft class="h-4 w-4 transition-transform group-hover:-translate-x-1" />
                        Return Home
                    </Link>
                </div>
            </div>
        </nav>

        <main class="py-16 lg:py-24">
            <div class="mx-auto max-w-7xl px-6">
                <div class="mb-16">
                    <h1 class="mb-4 text-3xl font-bold tracking-tight lg:text-4xl">Comprehensive <span class="text-blue-600">Institutional Tools</span></h1>
                    <p class="max-w-xl text-base text-slate-500 dark:text-slate-400">Explore the features designed to streamline every aspect of your school's administration and academic delivery.</p>
                </div>

                <div class="space-y-20">
                    <div v-for="group in moduleGroups" :key="group.name">
                        <h2 class="mb-8 text-[9px] font-bold tracking-[0.3em] text-blue-600 uppercase">{{ group.name }}</h2>
                        <div class="grid gap-6 md:grid-cols-3">
                            <div v-for="item in group.items" :key="item.title" class="rounded-xl border border-slate-200 bg-white p-8 transition-shadow hover:shadow-md dark:border-slate-800 dark:bg-slate-900/50">
                                <div class="mb-6 flex h-10 w-10 items-center justify-center rounded-lg bg-blue-600 text-white shadow-sm">
                                    <component :is="item.icon" class="h-5 w-5" />
                                </div>
                                <h3 class="mb-3 text-lg font-bold tracking-tight">{{ item.title }}</h3>
                                <p class="text-xs leading-relaxed text-slate-500 dark:text-slate-400">{{ item.desc }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <footer class="border-t py-16 text-center">
            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Experience the full platform</p>
            <Button size="lg" as-child class="mt-6 h-12 rounded-md bg-blue-600 px-10 text-xs font-bold tracking-widest shadow-md hover:bg-blue-700">
                <Link href="/register">GET STARTED FOR FREE</Link>
            </Button>
        </footer>
    </div>
</template>
