<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    GraduationCap,
    Users,
    BookOpen,
    BarChart3,
    Shield,
    Zap,
    Globe,
    CheckCircle,
    ArrowRight,
    Menu,
    X,
} from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
defineProps<{ canLogin?: boolean; canRegister?: boolean }>();
const mobileMenuOpen = ref(false);
const features = [
    {
        icon: GraduationCap,
        title: 'Learner Management',
        description:
            'Complete learner lifecycle management from admission to graduation with CBC competency tracking.',
    },
    {
        icon: Users,
        title: 'Teacher Portal',
        description:
            'Empower teachers with tools for lesson planning, assessments, and performance tracking.',
    },
    {
        icon: BookOpen,
        title: 'CBC Curriculum',
        description:
            'Full Kenya CBC curriculum support with competency-based assessments and rubrics.',
    },
    {
        icon: BarChart3,
        title: 'Analytics & Reports',
        description:
            'Real-time insights and comprehensive reports for data-driven decisions.',
    },
    {
        icon: Shield,
        title: 'Secure & Reliable',
        description:
            'Enterprise-grade security with role-based access control and data encryption.',
    },
    {
        icon: Zap,
        title: 'Fast & Modern',
        description:
            'Lightning-fast performance with a beautiful, intuitive user interface.',
    },
];
const stats = [
    { value: '500+', label: 'Schools' },
    { value: '50K+', label: 'Learners' },
    { value: '5K+', label: 'Teachers' },
    { value: '99.9%', label: 'Uptime' },
];
</script>
<template>
    <Head title="Welcome - CBC Learning Platform" />
    <div
        class="min-h-screen bg-gradient-to-b from-slate-50 to-white dark:from-slate-950 dark:to-slate-900"
    >
        <!-- Navigation -->
        <nav
            class="fixed top-0 right-0 left-0 z-50 border-b bg-white/80 backdrop-blur-lg dark:bg-slate-950/80"
        >
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary"
                        >
                            <GraduationCap class="h-6 w-6 text-white" />
                        </div>
                        <span class="text-xl font-bold">CBC Platform</span>
                    </div>
                    <div class="hidden items-center gap-8 md:flex">
                        <a
                            href="#features"
                            class="text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
                            >Features</a
                        >
                        <a
                            href="#pricing"
                            class="text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
                            >Pricing</a
                        >
                        <a
                            href="#about"
                            class="text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
                            >About</a
                        >
                        <a
                            href="#contact"
                            class="text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
                            >Contact</a
                        >
                    </div>
                    <div class="hidden items-center gap-4 md:flex">
                        <Button variant="ghost" as-child v-if="canLogin">
                            <Link href="/login">Sign In</Link>
                        </Button>
                        <Button as-child v-if="canRegister">
                            <Link href="/register"
                                >Get Started <ArrowRight class="ml-2 h-4 w-4"
                            /></Link>
                        </Button>
                    </div>
                    <Button
                        variant="ghost"
                        size="icon"
                        class="md:hidden"
                        @click="mobileMenuOpen = !mobileMenuOpen"
                    >
                        <Menu v-if="!mobileMenuOpen" class="h-6 w-6" />
                        <X v-else class="h-6 w-6" />
                    </Button>
                </div>
            </div>
            <!-- Mobile menu -->
            <div
                v-if="mobileMenuOpen"
                class="space-y-4 border-t bg-white p-4 md:hidden dark:bg-slate-950"
            >
                <a href="#features" class="block text-sm font-medium"
                    >Features</a
                >
                <a href="#pricing" class="block text-sm font-medium">Pricing</a>
                <a href="#about" class="block text-sm font-medium">About</a>
                <a href="#contact" class="block text-sm font-medium">Contact</a>
                <div class="flex gap-2 border-t pt-4">
                    <Button
                        variant="outline"
                        as-child
                        class="flex-1"
                        v-if="canLogin"
                    >
                        <Link href="/login">Sign In</Link>
                    </Button>
                    <Button as-child class="flex-1" v-if="canRegister">
                        <Link href="/register">Get Started</Link>
                    </Button>
                </div>
            </div>
        </nav>
        <!-- Hero Section -->
        <section class="px-4 pt-32 pb-20">
            <div class="mx-auto max-w-7xl text-center">
                <div
                    class="mb-8 inline-flex items-center gap-2 rounded-full bg-primary/10 px-4 py-2 text-sm font-medium text-primary"
                >
                    <Zap class="h-4 w-4" />
                    Kenya's #1 CBC School Management System
                </div>
                <h1
                    class="mb-6 text-4xl font-bold tracking-tight sm:text-5xl md:text-6xl"
                >
                    Transform Your School with
                    <span
                        class="bg-gradient-to-r from-primary to-blue-600 bg-clip-text text-transparent"
                    >
                        CBC Digital Learning</span
                    >
                </h1>
                <p
                    class="mx-auto mb-10 max-w-2xl text-lg text-muted-foreground"
                >
                    A comprehensive, modern school management platform built for
                    Kenya's Competency-Based Curriculum. Streamline
                    administration, enhance learning outcomes, and empower
                    educators.
                </p>
                <div
                    class="flex flex-col items-center justify-center gap-4 sm:flex-row"
                >
                    <Button size="lg" as-child>
                        <Link href="/register">
                            Start Free Trial
                            <ArrowRight class="ml-2 h-5 w-5" />
                        </Link>
                    </Button>
                    <Button size="lg" variant="outline" as-child>
                        <Link href="#demo">Watch Demo</Link>
                    </Button>
                </div>
                <!-- Stats -->
                <div class="mt-20 grid grid-cols-2 gap-8 md:grid-cols-4">
                    <div
                        v-for="stat in stats"
                        :key="stat.label"
                        class="text-center"
                    >
                        <div
                            class="text-3xl font-bold text-primary md:text-4xl"
                        >
                            {{ stat.value }}
                        </div>
                        <div class="mt-1 text-sm text-muted-foreground">
                            {{ stat.label }}
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Dashboard Preview -->
        <section class="bg-slate-100 px-4 py-20 dark:bg-slate-900/50">
            <div class="mx-auto max-w-7xl">
                <div
                    class="overflow-hidden rounded-2xl border bg-white shadow-lg dark:bg-slate-950"
                >
                    <div class="flex items-center gap-2 border-b px-4 py-3">
                        <div class="h-3 w-3 rounded-full bg-red-500"></div>
                        <div class="h-3 w-3 rounded-full bg-yellow-500"></div>
                        <div class="h-3 w-3 rounded-full bg-green-500"></div>
                    </div>
                    <div
                        class="flex aspect-video items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-900"
                    >
                        <div class="text-center">
                            <GraduationCap
                                class="mx-auto mb-4 h-16 w-16 text-primary/50"
                            />
                            <p class="text-muted-foreground">
                                Dashboard Preview
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Features Section -->
        <section id="features" class="px-4 py-20">
            <div class="mx-auto max-w-7xl">
                <div class="mb-16 text-center">
                    <h2 class="mb-4 text-3xl font-bold md:text-4xl">
                        Everything You Need for CBC Excellence
                    </h2>
                    <p class="mx-auto max-w-2xl text-lg text-muted-foreground">
                        Comprehensive tools designed specifically for Kenya's
                        Competency-Based Curriculum
                    </p>
                </div>
                <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="feature in features"
                        :key="feature.title"
                        class="group rounded-2xl border bg-card p-8 transition-all hover:border-primary/50 hover:shadow-lg"
                    >
                        <div
                            class="mb-6 flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-primary transition-colors group-hover:bg-primary group-hover:text-white"
                        >
                            <component :is="feature.icon" class="h-6 w-6" />
                        </div>
                        <h3 class="mb-3 text-xl font-semibold">
                            {{ feature.title }}
                        </h3>
                        <p class="text-muted-foreground">
                            {{ feature.description }}
                        </p>
                    </div>
                </div>
            </div>
        </section>
        <!-- CTA Section -->
        <section class="bg-primary px-4 py-20">
            <div class="mx-auto max-w-4xl text-center text-white">
                <h2 class="mb-6 text-3xl font-bold md:text-4xl">
                    Ready to Transform Your School?
                </h2>
                <p class="mb-8 text-lg opacity-90">
                    Join hundreds of schools across Kenya already using CBC
                    Platform to enhance learning outcomes.
                </p>
                <div
                    class="flex flex-col items-center justify-center gap-4 sm:flex-row"
                >
                    <Button size="lg" variant="secondary" as-child>
                        <Link href="/register">Start Free Trial</Link>
                    </Button>
                    <Button
                        size="lg"
                        variant="outline"
                        class="border-white text-white hover:bg-white/10"
                        as-child
                    >
                        <Link href="/contact">Contact Sales</Link>
                    </Button>
                </div>
            </div>
        </section>
        <!-- Footer -->
        <footer class="border-t px-4 py-12">
            <div class="mx-auto max-w-7xl">
                <div class="mb-8 grid gap-8 md:grid-cols-4">
                    <div>
                        <div class="mb-4 flex items-center gap-2">
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary"
                            >
                                <GraduationCap class="h-5 w-5 text-white" />
                            </div>
                            <span class="font-bold">CBC Platform</span>
                        </div>
                        <p class="text-sm text-muted-foreground">
                            Kenya's leading CBC school management platform by
                            Doitix Tech Labs.
                        </p>
                    </div>
                    <div>
                        <h4 class="mb-4 font-semibold">Product</h4>
                        <ul class="space-y-2 text-sm text-muted-foreground">
                            <li>
                                <a href="#" class="hover:text-foreground"
                                    >Features</a
                                >
                            </li>
                            <li>
                                <a href="#" class="hover:text-foreground"
                                    >Pricing</a
                                >
                            </li>
                            <li>
                                <a href="#" class="hover:text-foreground"
                                    >Demo</a
                                >
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="mb-4 font-semibold">Company</h4>
                        <ul class="space-y-2 text-sm text-muted-foreground">
                            <li>
                                <a href="#" class="hover:text-foreground"
                                    >About</a
                                >
                            </li>
                            <li>
                                <a href="#" class="hover:text-foreground"
                                    >Blog</a
                                >
                            </li>
                            <li>
                                <a href="#" class="hover:text-foreground"
                                    >Careers</a
                                >
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="mb-4 font-semibold">Contact</h4>
                        <ul class="space-y-2 text-sm text-muted-foreground">
                            <li>info@doitrixtech.co.ke</li>
                            <li>+254 759 814 390</li>
                            <li>Nairobi, Kenya</li>
                        </ul>
                    </div>
                </div>
                <div
                    class="flex flex-col items-center justify-between gap-4 border-t pt-8 md:flex-row"
                >
                    <p class="text-sm text-muted-foreground">
                        © 2026 Doitix Tech Labs. All rights reserved.
                    </p>
                    <div class="flex gap-6 text-sm text-muted-foreground">
                        <a href="#" class="hover:text-foreground"
                            >Privacy Policy</a
                        >
                        <a href="#" class="hover:text-foreground"
                            >Terms of Service</a
                        >
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
