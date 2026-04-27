<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { GraduationCap, ArrowLeft, Heart, Target, Users2, Star } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

const route = (window as any).route;
</script>

<template>
    <Head title="About Us - Cloud School System" />
    
    <div class="min-h-screen bg-white font-sans text-slate-900 transition-colors duration-500 dark:bg-[#020617] dark:text-white">
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
                            <span class="text-[10px] font-bold text-blue-600 tracking-[0.2em] uppercase">About Our Mission</span>
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
            <!-- Hero -->
            <section class="relative py-20 lg:py-32 overflow-hidden">
                <div class="absolute top-0 right-0 -z-10 h-96 w-96 bg-blue-500/10 blur-[120px]"></div>
                <div class="absolute bottom-0 left-0 -z-10 h-96 w-96 bg-indigo-500/10 blur-[120px]"></div>

                <div class="mx-auto max-w-7xl px-6">
                    <div class="grid lg:grid-cols-2 gap-16 items-center">
                        <div class="max-w-2xl">
                            <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50/50 px-4 py-1.5 text-[10px] font-bold tracking-wider text-blue-600 uppercase backdrop-blur-sm dark:border-blue-500/20 dark:bg-blue-500/10 dark:text-blue-400">
                                <Star class="h-3.5 w-3.5 fill-blue-600/10" />
                                Our Story
                            </div>
                            <h1 class="mb-8 text-4xl font-black leading-tight tracking-tight text-slate-900 sm:text-5xl lg:text-7xl dark:text-white uppercase">
                                Dedicated to <span class="bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">Academic <br>Progress</span>
                            </h1>
                            <p class="text-base leading-relaxed text-slate-600 dark:text-slate-400 font-medium">
                                Cloud School System was established by Doitix Tech Labs to bridge the technology gap in Kenyan educational institutions. Our mission is to empower schools with data-driven tools that enhance the learning experience for students and simplify administrative workflows for staff.
                            </p>
                        </div>
                        <div class="relative">
                            <div class="aspect-square overflow-hidden rounded-[2.5rem] border-8 border-white bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900">
                                <img src="https://images.unsplash.com/photo-1524178232363-1fb2b075b655?q=80&w=2070&auto=format&fit=crop" alt="Education" class="h-full w-full object-cover">
                                <div class="absolute inset-0 bg-gradient-to-tr from-blue-600/20 to-transparent"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Mission Cards -->
            <section class="py-24 bg-slate-50/50 dark:bg-slate-950/20">
                <div class="mx-auto max-w-7xl px-6">
                    <div class="grid gap-8 lg:grid-cols-3">
                        <div class="group relative overflow-hidden rounded-3xl border border-white bg-white/50 p-10 shadow-sm transition-all hover:-translate-y-2 hover:shadow-2xl dark:border-slate-800 dark:bg-slate-900/50 backdrop-blur-sm">
                            <div class="mb-8 flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-xl shadow-blue-600/20">
                                <Heart class="h-7 w-7" />
                            </div>
                            <h3 class="mb-4 text-xl font-black tracking-tight uppercase">Our Philosophy</h3>
                            <p class="text-sm leading-relaxed text-slate-500 dark:text-slate-400">We believe that technology should be an enabler, not a barrier. Our tools are designed to be accessible and intuitive for all users, fostering a collaborative environment.</p>
                        </div>
                        
                        <div class="group relative overflow-hidden rounded-3xl border border-white bg-white/50 p-10 shadow-sm transition-all hover:-translate-y-2 hover:shadow-2xl dark:border-slate-800 dark:bg-slate-900/50 backdrop-blur-sm">
                            <div class="mb-8 flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-600 text-white shadow-xl shadow-indigo-600/20">
                                <Target class="h-7 w-7" />
                            </div>
                            <h3 class="mb-4 text-xl font-black tracking-tight uppercase">Strategic Vision</h3>
                            <p class="text-sm leading-relaxed text-slate-500 dark:text-slate-400">To be the leading provider of school management solutions in East Africa, fostering a more connected and efficient education ecosystem through constant innovation.</p>
                        </div>

                        <div class="group relative overflow-hidden rounded-3xl border border-white bg-white/50 p-10 shadow-sm transition-all hover:-translate-y-2 hover:shadow-2xl dark:border-slate-800 dark:bg-slate-900/50 backdrop-blur-sm">
                            <div class="mb-8 flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-600 text-white shadow-xl shadow-emerald-600/20">
                                <Users2 class="h-7 w-7" />
                            </div>
                            <h3 class="mb-4 text-xl font-black tracking-tight uppercase">Institutional Impact</h3>
                            <p class="text-sm leading-relaxed text-slate-500 dark:text-slate-400">By automating routine tasks, we allow educators to focus on what matters most: the individual development and success of their students in the CBC framework.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Company -->
            <section class="py-24 text-center overflow-hidden">
                <div class="mx-auto max-w-4xl px-6 relative">
                    <div class="absolute -top-24 left-1/2 -translate-x-1/2 h-64 w-64 bg-blue-500/5 blur-[100px] -z-10"></div>
                    <p class="mb-8 text-[11px] font-black tracking-[0.4em] text-blue-600 uppercase">Doitix Tech Labs</p>
                    <h2 class="mb-10 text-3xl font-black tracking-tight sm:text-5xl lg:text-6xl uppercase leading-none text-slate-900 dark:text-white">
                        Expertly Built <br> <span class="bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent italic">for the Kenyan Sector</span>
                    </h2>
                    <p class="mx-auto mb-16 max-w-2xl text-base font-medium text-slate-500 dark:text-slate-400 leading-relaxed uppercase tracking-wider">
                        Based in Nairobi, our team combines technical expertise with a deep understanding of the local curriculum to deliver a truly specialized product for modern schools.
                    </p>
                    
                    <div class="flex justify-center items-center gap-12 grayscale opacity-40 transition-all hover:grayscale-0 hover:opacity-100 duration-1000">
                         <div class="text-4xl font-black tracking-tighter uppercase">DOITIX<span class="text-blue-600">LABS</span></div>
                    </div>
                </div>
            </section>
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
            <div class="mt-20 text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-loose">
                &copy; {{ new Date().getFullYear() }} DOITIX TECH LABS. ALL RIGHTS RESERVED. <br>
                NAIROBI, KENYA
            </div>
        </footer>
    </div>
</template>
