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
        name: 'For School Leaders',
        items: [
            { icon: BarChart3, title: 'Easy Dashboard', desc: 'See your whole school in one simple view. Track student numbers and performance.' },
            { icon: Wallet, title: 'Fee Management', desc: 'Track school fees easily. Send reminders to parents and print receipts.' },
            { icon: Users, title: 'Staff Records', desc: 'Keep track of all your teachers and staff. Manage their duties and details.' }
        ]
    },
    {
        name: 'For Teachers',
        items: [
            { icon: FileSpreadsheet, title: 'Smart Record Books', desc: 'No more manual marking. Enter grades once and they are saved forever.' },
            { icon: Layout, title: 'Digital Lesson Plans', desc: 'Create and reuse lesson plans and schemes of work with one click.' },
            { icon: CheckCircle2, title: 'Attendance Tracker', desc: 'Mark class attendance on your phone or laptop in seconds.' }
        ]
    },
    {
        name: 'For Parents & Students',
        items: [
            { icon: Smartphone, title: 'Parent Portal', desc: 'Parents can see their children\'s progress and fee balances from home.' },
            { icon: MessageCircle, title: 'Easy Communication', desc: 'Send messages and announcements to all parents at once.' },
            { icon: GraduationCap, title: 'Student Portfolios', desc: 'Keep a digital history of every student\'s work and achievements.' }
        ]
    }
];
const route = (window as any).route;
</script>

<template>
    <Head title="What It Does - CBCCore" />
    
    <div class="min-h-screen bg-slate-50 font-sans text-slate-900 transition-colors duration-500 dark:bg-[#020617] dark:text-white">
        <!-- Minimal Nav -->
        <nav class="border-b bg-white/60 backdrop-blur-2xl dark:border-slate-800 dark:bg-[#020617]/60">
            <div class="mx-auto max-w-7xl px-6">
                <div class="flex h-20 items-center justify-between">
                    <Link :href="route('home')" class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600">
                            <GraduationCap class="h-6 w-6 text-white" />
                        </div>
                        <span class="text-xl font-black tracking-tighter uppercase">CBC<span class="text-blue-600">Core</span></span>
                    </Link>
                    <Link :href="route('home')" class="group flex items-center gap-2 text-xs font-black tracking-widest text-slate-500 uppercase hover:text-blue-600">
                        <ArrowLeft class="h-4 w-4 transition-transform group-hover:-translate-x-1" />
                        Back Home
                    </Link>
                </div>
            </div>
        </nav>

        <main class="py-20 lg:py-32">
            <div class="mx-auto max-w-7xl px-6">
                <div class="mb-20">
                    <h1 class="mb-6 text-5xl font-black tracking-tight lg:text-7xl">Powerful tools made <span class="text-blue-600">simple.</span></h1>
                    <p class="max-w-2xl text-xl text-slate-500 dark:text-slate-400">Everything we built was designed to be easy for everyone to use—from the principal to the parent.</p>
                </div>

                <div class="space-y-32">
                    <div v-for="group in moduleGroups" :key="group.name">
                        <h2 class="mb-10 text-[11px] font-black tracking-[0.4em] text-blue-600 uppercase">{{ group.name }}</h2>
                        <div class="grid gap-8 md:grid-cols-3">
                            <div v-for="item in group.items" :key="item.title" class="rounded-[2rem] border border-slate-200 bg-white p-10 transition-shadow hover:shadow-xl dark:border-slate-800 dark:bg-slate-900/50">
                                <div class="mb-8 flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-600/20">
                                    <component :is="item.icon" class="h-6 w-6" />
                                </div>
                                <h3 class="mb-4 text-xl font-black tracking-tight">{{ item.title }}</h3>
                                <p class="text-sm leading-relaxed text-slate-500 dark:text-slate-400">{{ item.desc }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <footer class="border-t py-20 text-center">
            <p class="text-[11px] font-bold text-slate-400 uppercase">Ready to see it in action?</p>
            <Button size="lg" as-child class="mt-8 h-16 rounded-full bg-blue-600 px-12 text-[15px] font-black tracking-widest shadow-2xl shadow-blue-600/30 hover:bg-blue-700">
                <Link href="/register">GET STARTED FOR FREE</Link>
            </Button>
        </footer>
    </div>
</template>
