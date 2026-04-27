<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { GraduationCap, ArrowLeft, Heart, Target, Users2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
</script>

<template>
    <Head title="About Us - CBCCore" />
    
    <div class="min-h-screen bg-white font-sans text-slate-900 transition-colors duration-500 dark:bg-[#020617] dark:text-white">
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

        <main>
            <!-- Hero -->
            <section class="py-20 lg:py-32">
                <div class="mx-auto max-w-7xl px-6">
                    <div class="max-w-3xl">
                        <h1 class="mb-8 text-5xl font-black tracking-tight lg:text-7xl">Who we are and <span class="text-blue-600">why we care.</span></h1>
                        <p class="text-xl leading-relaxed text-slate-600 dark:text-slate-400">
                            We are a team of Kenyans who believe that every child deserves a great education. We build technology that helps teachers and schools do their best work without being slowed down by paperwork.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Mission -->
            <section class="bg-slate-50 py-20 dark:bg-slate-900/50">
                <div class="mx-auto max-w-7xl px-6">
                    <div class="grid gap-16 lg:grid-cols-3">
                        <div class="space-y-6">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-600 text-white">
                                <Heart class="h-6 w-6" />
                            </div>
                            <h3 class="text-2xl font-black">Our Passion</h3>
                            <p class="text-slate-500">We love helping schools. Our goal is to see every school in Kenya using modern tools to help their students grow.</p>
                        </div>
                        <div class="space-y-6">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-600 text-white">
                                <Target class="h-6 w-6" />
                            </div>
                            <h3 class="text-2xl font-black">Our Goal</h3>
                            <p class="text-slate-500">We want to make CBC management simple. We build tools that handle all the complex parts so you can focus on teaching.</p>
                        </div>
                        <div class="space-y-6">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-600 text-white">
                                <Users2 class="h-6 w-6" />
                            </div>
                            <h3 class="text-2xl font-black">Our Community</h3>
                            <p class="text-slate-500">You are not alone. When you join CBCCore, you join a community of thousands of teachers across Kenya.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Company -->
            <section class="py-20 lg:py-40 text-center">
                <div class="mx-auto max-w-3xl px-6">
                    <p class="mb-8 text-[11px] font-black tracking-[0.4em] text-blue-600 uppercase">A part of Doitix Tech Labs</p>
                    <h2 class="mb-10 text-4xl font-black tracking-tight">Built by Kenyans for Kenyan Schools.</h2>
                    <p class="mb-12 text-lg text-slate-600">Our office is in Nairobi, but we serve schools all over the country. We are here to support you every step of the way.</p>
                    <div class="flex justify-center gap-12 grayscale opacity-50">
                         <div class="text-3xl font-black tracking-tighter uppercase opacity-30">DOITIX<span class="text-blue-600">LABS</span></div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>
