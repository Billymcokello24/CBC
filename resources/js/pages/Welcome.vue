<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { GraduationCap, Users, BookOpen, BarChart3, Shield, Zap, Globe, CheckCircle, ArrowRight, Menu, X } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
defineProps<{ canLogin?: boolean; canRegister?: boolean }>();
const mobileMenuOpen = ref(false);
const features = [
    { icon: GraduationCap, title: 'Student Management', description: 'Complete student lifecycle management from admission to graduation with CBC competency tracking.' },
    { icon: Users, title: 'Teacher Portal', description: 'Empower teachers with tools for lesson planning, assessments, and performance tracking.' },
    { icon: BookOpen, title: 'CBC Curriculum', description: 'Full Kenya CBC curriculum support with competency-based assessments and rubrics.' },
    { icon: BarChart3, title: 'Analytics & Reports', description: 'Real-time insights and comprehensive reports for data-driven decisions.' },
    { icon: Shield, title: 'Secure & Reliable', description: 'Enterprise-grade security with role-based access control and data encryption.' },
    { icon: Zap, title: 'Fast & Modern', description: 'Lightning-fast performance with a beautiful, intuitive user interface.' },
];
const stats = [
    { value: '500+', label: 'Schools' },
    { value: '50K+', label: 'Students' },
    { value: '5K+', label: 'Teachers' },
    { value: '99.9%', label: 'Uptime' },
];
</script>
<template>
    <Head title="Welcome - CBC Learning Platform" />
    <div class="min-h-screen bg-gradient-to-b from-slate-50 to-white dark:from-slate-950 dark:to-slate-900">
        <!-- Navigation -->
        <nav class="fixed top-0 left-0 right-0 z-50 border-b bg-white/80 backdrop-blur-lg dark:bg-slate-950/80">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary">
                            <GraduationCap class="h-6 w-6 text-white" />
                        </div>
                        <span class="text-xl font-bold">CBC Platform</span>
                    </div>
                    <div class="hidden md:flex items-center gap-8">
                        <a href="#features" class="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">Features</a>
                        <a href="#pricing" class="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">Pricing</a>
                        <a href="#about" class="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">About</a>
                        <a href="#contact" class="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">Contact</a>
                    </div>
                    <div class="hidden md:flex items-center gap-4">
                        <Button variant="ghost" as-child v-if="canLogin">
                            <Link href="/login">Sign In</Link>
                        </Button>
                        <Button as-child v-if="canRegister">
                            <Link href="/register">Get Started <ArrowRight class="ml-2 h-4 w-4" /></Link>
                        </Button>
                    </div>
                    <Button variant="ghost" size="icon" class="md:hidden" @click="mobileMenuOpen = !mobileMenuOpen">
                        <Menu v-if="!mobileMenuOpen" class="h-6 w-6" />
                        <X v-else class="h-6 w-6" />
                    </Button>
                </div>
            </div>
            <!-- Mobile menu -->
            <div v-if="mobileMenuOpen" class="md:hidden border-t bg-white dark:bg-slate-950 p-4 space-y-4">
                <a href="#features" class="block text-sm font-medium">Features</a>
                <a href="#pricing" class="block text-sm font-medium">Pricing</a>
                <a href="#about" class="block text-sm font-medium">About</a>
                <a href="#contact" class="block text-sm font-medium">Contact</a>
                <div class="flex gap-2 pt-4 border-t">
                    <Button variant="outline" as-child class="flex-1" v-if="canLogin">
                        <Link href="/login">Sign In</Link>
                    </Button>
                    <Button as-child class="flex-1" v-if="canRegister">
                        <Link href="/register">Get Started</Link>
                    </Button>
                </div>
            </div>
        </nav>
        <!-- Hero Section -->
        <section class="pt-32 pb-20 px-4">
            <div class="mx-auto max-w-7xl text-center">
                <div class="inline-flex items-center gap-2 rounded-full bg-primary/10 px-4 py-2 text-sm font-medium text-primary mb-8">
                    <Zap class="h-4 w-4" />
                    Kenya's #1 CBC School Management System
                </div>
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-bold tracking-tight mb-6">
                    Transform Your School with
                    <span class="bg-gradient-to-r from-primary to-blue-600 bg-clip-text text-transparent"> CBC Digital Learning</span>
                </h1>
                <p class="mx-auto max-w-2xl text-lg text-muted-foreground mb-10">
                    A comprehensive, modern school management platform built for Kenya's Competency-Based Curriculum. 
                    Streamline administration, enhance learning outcomes, and empower educators.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
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
                <div class="mt-20 grid grid-cols-2 md:grid-cols-4 gap-8">
                    <div v-for="stat in stats" :key="stat.label" class="text-center">
                        <div class="text-3xl md:text-4xl font-bold text-primary">{{ stat.value }}</div>
                        <div class="text-sm text-muted-foreground mt-1">{{ stat.label }}</div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Dashboard Preview -->
        <section class="py-20 px-4 bg-slate-100 dark:bg-slate-900/50">
            <div class="mx-auto max-w-7xl">
                <div class="rounded-2xl border bg-white dark:bg-slate-950 shadow-2xl overflow-hidden">
                    <div class="flex items-center gap-2 border-b px-4 py-3">
                        <div class="h-3 w-3 rounded-full bg-red-500"></div>
                        <div class="h-3 w-3 rounded-full bg-yellow-500"></div>
                        <div class="h-3 w-3 rounded-full bg-green-500"></div>
                    </div>
                    <div class="aspect-video bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-900 flex items-center justify-center">
                        <div class="text-center">
                            <GraduationCap class="h-16 w-16 mx-auto text-primary/50 mb-4" />
                            <p class="text-muted-foreground">Dashboard Preview</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Features Section -->
        <section id="features" class="py-20 px-4">
            <div class="mx-auto max-w-7xl">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold mb-4">Everything You Need for CBC Excellence</h2>
                    <p class="text-lg text-muted-foreground max-w-2xl mx-auto">
                        Comprehensive tools designed specifically for Kenya's Competency-Based Curriculum
                    </p>
                </div>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="feature in features" :key="feature.title" class="group rounded-2xl border bg-card p-8 transition-all hover:shadow-lg hover:border-primary/50">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-primary mb-6 group-hover:bg-primary group-hover:text-white transition-colors">
                            <component :is="feature.icon" class="h-6 w-6" />
                        </div>
                        <h3 class="text-xl font-semibold mb-3">{{ feature.title }}</h3>
                        <p class="text-muted-foreground">{{ feature.description }}</p>
                    </div>
                </div>
            </div>
        </section>
        <!-- CTA Section -->
        <section class="py-20 px-4 bg-primary">
            <div class="mx-auto max-w-4xl text-center text-white">
                <h2 class="text-3xl md:text-4xl font-bold mb-6">Ready to Transform Your School?</h2>
                <p class="text-lg opacity-90 mb-8">
                    Join hundreds of schools across Kenya already using CBC Platform to enhance learning outcomes.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <Button size="lg" variant="secondary" as-child>
                        <Link href="/register">Start Free Trial</Link>
                    </Button>
                    <Button size="lg" variant="outline" class="text-white border-white hover:bg-white/10" as-child>
                        <Link href="/contact">Contact Sales</Link>
                    </Button>
                </div>
            </div>
        </section>
        <!-- Footer -->
        <footer class="py-12 px-4 border-t">
            <div class="mx-auto max-w-7xl">
                <div class="grid md:grid-cols-4 gap-8 mb-8">
                    <div>
                        <div class="flex items-center gap-2 mb-4">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary">
                                <GraduationCap class="h-5 w-5 text-white" />
                            </div>
                            <span class="font-bold">CBC Platform</span>
                        </div>
                        <p class="text-sm text-muted-foreground">
                            Kenya's leading CBC school management platform by Doitix Tech Labs.
                        </p>
                    </div>
                    <div>
                        <h4 class="font-semibold mb-4">Product</h4>
                        <ul class="space-y-2 text-sm text-muted-foreground">
                            <li><a href="#" class="hover:text-foreground">Features</a></li>
                            <li><a href="#" class="hover:text-foreground">Pricing</a></li>
                            <li><a href="#" class="hover:text-foreground">Demo</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold mb-4">Company</h4>
                        <ul class="space-y-2 text-sm text-muted-foreground">
                            <li><a href="#" class="hover:text-foreground">About</a></li>
                            <li><a href="#" class="hover:text-foreground">Blog</a></li>
                            <li><a href="#" class="hover:text-foreground">Careers</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold mb-4">Contact</h4>
                        <ul class="space-y-2 text-sm text-muted-foreground">
                            <li>info@doitrixtech.co.ke</li>
                            <li>+254 759 814 390</li>
                            <li>Nairobi, Kenya</li>
                        </ul>
                    </div>
                </div>
                <div class="border-t pt-8 flex flex-col md:flex-row items-center justify-between gap-4">
                    <p class="text-sm text-muted-foreground">© 2026 Doitix Tech Labs. All rights reserved.</p>
                    <div class="flex gap-6 text-sm text-muted-foreground">
                        <a href="#" class="hover:text-foreground">Privacy Policy</a>
                        <a href="#" class="hover:text-foreground">Terms of Service</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
