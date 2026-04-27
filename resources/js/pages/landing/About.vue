<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Heart, Target, Users2, Star } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import LandingLayout from '@/layouts/LandingLayout.vue';

const route = (window as any).route;
</script>

<template>
    <Head title="About Us - Cloud School System" />
    
    <LandingLayout>
        <main class="pt-32 pb-24 lg:pt-48">
            <!-- Hero -->
            <section class="relative pb-20 lg:pb-32 overflow-hidden">
                <div class="container mx-auto px-6 max-w-7xl">
                    <div class="grid lg:grid-cols-2 gap-16 items-center">
                        <div class="max-w-2xl">
                            <div class="mb-6 inline-flex items-center gap-2 rounded-full px-4 py-1.5 text-[11px] font-bold tracking-wider text-blue-600 uppercase bg-blue-50 dark:bg-blue-500/10 dark:text-blue-400">
                                <Star class="h-3.5 w-3.5" />
                                Our Story
                            </div>
                            <h1 class="mb-8 text-4xl font-bold leading-tight tracking-tight text-slate-900 sm:text-5xl lg:text-7xl dark:text-white uppercase">
                                Dedicated to <span class="text-blue-600">Institutional <br>Progress</span>
                            </h1>
                            <p class="text-lg leading-relaxed text-slate-600 dark:text-slate-400 font-medium">
                                Cloud School System was established to bridge the technology gap in Kenyan educational institutions. Our mission is to empower schools with data-driven tools that enhance the learning experience for students and simplify administrative workflows for staff.
                            </p>
                        </div>
                        <div class="relative">
                            <div class="aspect-square overflow-hidden rounded-[3rem] border-8 border-white bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900">
                                <img src="https://images.unsplash.com/photo-1524178232363-1fb2b075b655?q=80&w=2070&auto=format&fit=crop" alt="Education" class="h-full w-full object-cover">
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Mission Cards -->
            <section class="py-24 bg-white dark:bg-slate-950/20">
                <div class="container mx-auto px-6 max-w-7xl">
                    <div class="grid gap-8 lg:grid-cols-3">
                        <div class="group relative overflow-hidden rounded-[32px] bg-[#F8F9FF] dark:bg-slate-900 p-10 shadow-sm transition-all hover:-translate-y-2 hover:shadow-2xl">
                            <div class="mb-8 flex h-14 w-14 items-center justify-center rounded-2xl bg-[#3B63F6] text-white shadow-xl shadow-blue-600/20">
                                <Heart class="h-7 w-7" />
                            </div>
                            <h3 class="mb-4 text-xl font-bold tracking-tight text-slate-900 dark:text-white uppercase">Our Philosophy</h3>
                            <p class="text-sm leading-relaxed text-slate-500 dark:text-slate-400 font-medium">We believe that technology should be an enabler, not a barrier. Our tools are designed to be accessible and intuitive for all users, fostering a collaborative environment.</p>
                        </div>
                        
                        <div class="group relative overflow-hidden rounded-[32px] bg-[#F8F9FF] dark:bg-slate-900 p-10 shadow-sm transition-all hover:-translate-y-2 hover:shadow-2xl">
                            <div class="mb-8 flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-600 text-white shadow-xl shadow-indigo-600/20">
                                <Target class="h-7 w-7" />
                            </div>
                            <h3 class="mb-4 text-xl font-bold tracking-tight text-slate-900 dark:text-white uppercase">Strategic Vision</h3>
                            <p class="text-sm leading-relaxed text-slate-500 dark:text-slate-400 font-medium">To be the leading provider of school management solutions in East Africa, fostering a more efficient education ecosystem through constant innovation.</p>
                        </div>

                        <div class="group relative overflow-hidden rounded-[32px] bg-[#F8F9FF] dark:bg-slate-900 p-10 shadow-sm transition-all hover:-translate-y-2 hover:shadow-2xl">
                            <div class="mb-8 flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-600 text-white shadow-xl shadow-emerald-600/20">
                                <Users2 class="h-7 w-7" />
                            </div>
                            <h3 class="mb-4 text-xl font-bold tracking-tight text-slate-900 dark:text-white uppercase">Institutional Impact</h3>
                            <p class="text-sm leading-relaxed text-slate-500 dark:text-slate-400 font-medium">By automating routine tasks, we allow educators to focus on what matters most: the individual development and success of their students.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Company -->
            <section class="py-24 text-center">
                <div class="container mx-auto px-6 max-w-4xl relative">
                    <p class="mb-8 text-[11px] font-black tracking-[0.4em] text-blue-600 uppercase">A Local Solution</p>
                    <h2 class="mb-10 text-3xl font-bold tracking-tight sm:text-5xl lg:text-6xl uppercase leading-tight text-slate-900 dark:text-white">
                        Expertly Built <br> <span class="italic text-blue-600/60">for Kenya</span>
                    </h2>
                    <p class="mx-auto mb-16 max-w-2xl text-lg font-medium text-slate-500 dark:text-slate-400 leading-relaxed uppercase tracking-wider">
                        Based in Nairobi, our team combines technical expertise with a deep understanding of the local curriculum to deliver a truly specialized product for modern schools.
                    </p>
                    
                    <div class="flex justify-center items-center gap-12 grayscale opacity-40">
                         <div class="text-4xl font-black tracking-tighter uppercase text-slate-900 dark:text-white">CLOUD<span class="text-blue-600">SCHOOL</span></div>
                    </div>
                </div>
            </section>
        </main>
    </LandingLayout>
</template>

