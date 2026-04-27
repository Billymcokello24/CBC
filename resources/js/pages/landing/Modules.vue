<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { GraduationCap, ArrowLeft, BookOpen, Users, ClipboardCheck, BarChart3, Globe, Shield, CreditCard, MessageSquare, Truck, Home, Activity } from 'lucide-vue-next';

const modules = [
    {
        title: 'Students & Classes',
        icon: GraduationCap,
        description: 'Store all student records. Manage classes, streams, and grades easily.'
    },
    {
        title: 'Teahers & Staff',
        icon: Users,
        description: 'Track staff attendance, roles, and subject assignments in one place.'
    },
    {
        title: 'CBC Assessments',
        icon: ClipboardCheck,
        description: 'Full CBC support. Rubrics, report cards, and student portfolios.'
    },
    {
        title: 'Finance & Fees',
        icon: CreditCard,
        description: 'Track payments and school spending. Manage invoices and printing receipts.'
    },
     {
        title: 'Academic Planning',
        icon: BookOpen,
        description: 'Digital schemes of work and lesson plans that your teachers will love.'
    },
    {
        title: 'Communication',
        icon: MessageSquare,
        description: 'Send messages to parents and staff instantly via email and text.'
    },
    {
        title: 'Transport',
        icon: Truck,
        description: 'Manage school buses, routes, and student pick-up points.'
    },
    {
        title: 'Hostels & Boarding',
        icon: Home,
        description: 'Full boarding management, room allocations, and student check-ins.'
    },
    {
        title: 'Health Records',
        icon: Activity,
        description: 'Track student health visits and keep medical records safe.'
    },
    {
        title: 'Library System',
        icon: Globe,
        description: 'Digital catalog for books and automated book loan tracking.'
    }
];
</script>

<template>
    <Head title="Our Modules - CBCCore" />

    <div class="min-h-screen bg-white font-sans text-slate-900 dark:bg-[#020617] dark:text-white">
        <!-- Minimal Nav -->
        <nav class="border-b border-slate-100 py-6 dark:border-slate-800">
            <div class="mx-auto max-w-7xl px-6 flex items-center justify-between">
                 <Link :href="route('home')" class="flex items-center gap-2 group">
                    <ArrowLeft class="h-5 w-5 text-slate-400 group-hover:text-blue-600 transition-colors" />
                    <span class="text-sm font-bold tracking-widest uppercase">Back Home</span>
                </Link>
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600">
                        < GraduationCap class="h-5 w-5 text-white" />
                    </div>
                    <span class="text-lg font-black tracking-tighter uppercase">CBC<span class="text-blue-600">Core</span></span>
                </div>
            </div>
        </nav>

        <main class="py-20 lg:py-32">
            <div class="mx-auto max-w-7xl px-6 text-center mb-20 lg:mb-32">
                <h1 class="mb-8 text-5xl font-black tracking-tight sm:text-7xl">
                    Our <span class="text-blue-600">Modules.</span>
                </h1>
                <p class="text-xl leading-relaxed text-slate-600 dark:text-slate-400 max-w-2xl mx-auto">
                    Everything you need to handle your school efficiently. No more missing papers or slow work.
                </p>
            </div>

            <div class="mx-auto max-w-7xl px-6 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div v-for="module in modules" :key="module.title" class="p-10 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900/50 hover:border-blue-600/20 hover:shadow-2xl transition-all">
                    <div class="mb-8 flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-600/20">
                        <component :is="module.icon" class="h-6 w-6" />
                    </div>
                    <h3 class="mb-4 text-2xl font-black tracking-tight uppercase leading-none">{{ module.title }}</h3>
                    <p class="text-slate-500 dark:text-slate-400">{{ module.description }}</p>
                </div>
            </div>
        </main>
    </div>
</template>
