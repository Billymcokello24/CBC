<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { GraduationCap, ArrowLeft, Heart, Target, Users2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

const route = (window as any).route;
</script>

<template>
    <Head title="About Us - CBC Core" />
    
    <div class="min-h-screen bg-white font-sans text-slate-900 transition-colors duration-500 dark:bg-[#020617] dark:text-white">
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

        <main>
            <!-- Hero -->
            <section class="py-16 lg:py-24">
                <div class="mx-auto max-w-7xl px-6">
                    <div class="max-w-2xl">
                        <h1 class="mb-6 text-3xl font-bold tracking-tight lg:text-4xl">Dedicated to <span class="text-blue-600">Academic Progress</span></h1>
                        <p class="text-base leading-relaxed text-slate-600 dark:text-slate-400">
                            CBC Core was established by Doitix Tech Labs to bridge the technology gap in Kenyan educational institutions. Our mission is to empower schools with data-driven tools that enhance the learning experience for students and simplify administrative workflows for staff.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Mission -->
            <section class="bg-slate-50 py-16 dark:bg-slate-900/50">
                <div class="mx-auto max-w-7xl px-6">
                    <div class="grid gap-12 lg:grid-cols-3">
                        <div class="space-y-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-600 text-white shadow-sm">
                                <Heart class="h-5 w-5" />
                            </div>
                            <h3 class="text-lg font-bold">Our Philosophy</h3>
                            <p class="text-xs leading-relaxed text-slate-500">We believe that technology should be an enabler, not a barrier. Our tools are designed to be accessible and intuitive for all users.</p>
                        </div>
                        <div class="space-y-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-600 text-white shadow-sm">
                                <Target class="h-5 w-5" />
                            </div>
                            <h3 class="text-lg font-bold">Strategic Vision</h3>
                            <p class="text-xs leading-relaxed text-slate-500">To be the leading provider of school management solutions in East Africa, fostering a more connected and efficient education ecosystem.</p>
                        </div>
                        <div class="space-y-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-600 text-white shadow-sm">
                                <Users2 class="h-5 w-5" />
                            </div>
                            <h3 class="text-lg font-bold">Institutional Impact</h3>
                            <p class="text-xs leading-relaxed text-slate-500">By automating routine tasks, we allow educators to focus on what matters most: the development and success of their students.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Company -->
            <section class="py-16 lg:py-32 text-center">
                <div class="mx-auto max-w-2xl px-6">
                    <p class="mb-6 text-[10px] font-bold tracking-[0.3em] text-blue-600 uppercase">Doitix Tech Labs</p>
                    <h2 class="mb-8 text-2xl font-bold tracking-tight">Expertly Built for the Kenyan Education Sector</h2>
                    <p class="mb-10 text-base text-slate-600">Based in Nairobi, our team combines technical expertise with a deep understanding of the local curriculum to deliver a truly specialized product.</p>
                    <div class="flex justify-center gap-12 grayscale opacity-30">
                         <div class="text-2xl font-bold tracking-tighter uppercase">DOITIX<span class="text-blue-600">LABS</span></div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>
