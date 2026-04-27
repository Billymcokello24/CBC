<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { GraduationCap, ArrowLeft, Heart, Target, Lightbulb } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

const values = [
    {
        icon: Heart,
        title: 'We Care',
        description: 'We care about your school and your students. We build tools that make your life easier.',
    },
    {
        icon: Target,
        title: 'Our Mission',
        description: 'To help every school in Kenya go digital without any stress or high costs.',
    },
    {
        icon: Lightbulb,
        title: 'Simple Innovation',
        description: 'We take complex school work and make it simple for everyone to use.',
    },
];
</script>

<template>
    <Head title="About Us - CBCCore" />

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
                        <GraduationCap class="h-5 w-5 text-white" />
                    </div>
                    <span class="text-lg font-black tracking-tighter uppercase">CBC<span class="text-blue-600">Core</span></span>
                </div>
            </div>
        </nav>

        <main class="py-20 lg:py-32">
            <div class="mx-auto max-w-4xl px-6 text-center">
                <h1 class="mb-8 text-5xl font-black tracking-tight sm:text-7xl">
                    Who <span class="text-blue-600">we are.</span>
                </h1>
                <p class="text-xl leading-relaxed text-slate-600 dark:text-slate-400 mb-16">
                    We are a team of educators and tech lovers from Nairobi. We saw how schools struggle with paperwork and CBC rules, so we built something better. Our goal is to make school management easy for everyone—no matter how big or small your school is.
                </p>

                <div class="grid gap-8 md:grid-cols-3">
                    <div v-for="value in values" :key="value.title" class="p-8 rounded-[2rem] bg-slate-50 dark:bg-slate-900">
                        <div class="mb-6 flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-600/10 text-blue-600 mx-auto">
                            <component :is="value.icon" class="h-6 w-6" />
                        </div>
                        <h3 class="mb-4 text-xl font-black tracking-tight">{{ value.title }}</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ value.description }}</p>
                    </div>
                </div>
            </div>

            <div class="mt-32 bg-blue-600 py-20 lg:py-32">
                 <div class="mx-auto max-w-4xl px-6 text-center text-white">
                    <h2 class="text-4xl font-black mb-8 leading-tight">Ready to see how we can help your school?</h2>
                    <Button size="lg" as-child class="h-16 rounded-full bg-white text-blue-600 px-12 text-[15px] font-black tracking-widest hover:bg-slate-100">
                        <Link href="/register">JOIN US TODAY</Link>
                    </Button>
                 </div>
            </div>
        </main>
    </div>
</template>
