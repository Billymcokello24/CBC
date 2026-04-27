<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { GraduationCap, ArrowLeft } from 'lucide-vue-next';

const page = usePage();

defineProps<{
    title?: string;
    description?: string;
    image?: string;
    quote?: string;
    author?: string;
    role?: string;
}>();

const route = (window as any).route;
</script>

<template>
    <div
        class="relative grid min-h-screen flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0 bg-white dark:bg-slate-950 transition-colors duration-500"
    >
        <div class="relative hidden h-full flex-col p-12 text-white lg:flex border-r border-slate-100 dark:border-slate-800">
            <!-- Background with Overlay -->
            <div class="absolute inset-0 z-0 overflow-hidden bg-slate-950">
                <img
                    v-if="image"
                    :src="image"
                    alt="Background"
                    class="animate-slow-zoom h-full w-full scale-105 object-cover opacity-50 contrast-125 grayscale-[0.5]"
                />
                <div
                    class="absolute inset-0 bg-gradient-to-t from-blue-950 via-slate-950/40 to-transparent"
                ></div>
                <!-- Glassmorphism card for branding -->
                <div class="absolute inset-0 z-10 flex flex-col justify-between p-16">
                     <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10 ring-1 ring-white/30 backdrop-blur-xl shadow-2xl">
                            <GraduationCap class="h-7 w-7 text-white" />
                        </div>
                        <div class="flex flex-col text-left">
                            <span class="text-xl font-black tracking-tight text-white leading-none uppercase">CLOUD SCHOOL</span>
                            <span class="text-[10px] font-bold text-blue-400 tracking-[0.3em] uppercase opacity-80">Institutional Access</span>
                        </div>
                    </div>

                    <div class="max-w-xl space-y-8">
                        <div class="h-[1px] w-12 bg-blue-500 opacity-50"></div>
                        <blockquote class="space-y-6">
                            <p class="text-4xl leading-tight font-black tracking-tight uppercase italic decoration-blue-500 underline-offset-8">
                                &ldquo;{{
                                    quote ||
                                    'Empowering the next generation of learners through innovative digital tools.'
                                }}&rdquo;
                            </p>
                            <footer class="pt-4 flex flex-col gap-1">
                                <p class="text-xl font-black uppercase tracking-wider text-blue-400">
                                    {{ author || 'Dr. Jane Muthoni' }}
                                </p>
                                <p class="text-[11px] font-bold text-white/50 uppercase tracking-[0.2em]">
                                    {{ role || 'Principal, Nairobi Academy' }}
                                </p>
                            </footer>
                        </blockquote>
                    </div>

                    <div class="flex items-center justify-between border-t border-white/10 pt-8 mt-auto">
                        <div class="flex items-center gap-6 text-[10px] font-black uppercase tracking-[0.2em] text-white/40">
                            <span>© 2026 DOITIX TECH LABS</span>
                            <span class="h-1 w-1 rounded-full bg-blue-500"></span>
                            <span>NAIROBI, KENYA</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:p-12 h-full flex flex-col justify-center py-16">
            <div class="mx-auto flex w-full flex-col justify-center space-y-12 sm:w-[480px] p-8 sm:p-0">
                <div class="flex flex-col space-y-4">
                    <!-- Mobile Logo -->
                    <div class="lg:hidden flex items-center gap-4 mb-8">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 shadow-lg shadow-blue-600/20">
                            <GraduationCap class="h-6 w-6 text-white" />
                        </div>
                        <span class="text-lg font-black tracking-tight text-slate-900 dark:text-white leading-none uppercase">CLOUD SCHOOL</span>
                    </div>

                    <h1
                        class="text-4xl font-black tracking-tight text-slate-900 dark:text-white uppercase leading-none"
                        v-if="title"
                    >
                        {{ title }}
                    </h1>
                    <p
                        class="text-sm font-medium text-slate-500 dark:text-slate-400 uppercase tracking-widest leading-relaxed"
                        v-if="description"
                    >
                        {{ description }}
                    </p>
                </div>
                
                <div class="relative">
                     <slot />
                </div>

                <div class="pt-8 text-center lg:text-left">
                     <Link :href="route('home')" class="text-[10px] font-black tracking-[0.3em] uppercase text-slate-400 hover:text-blue-600 transition-colors inline-flex items-center gap-2">
                        <ArrowLeft class="h-3 w-3" />
                        Return to Homepage
                     </Link>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes slow-zoom {
    from {
        transform: scale(1);
    }
    to {
        transform: scale(1.15);
    }
}
.animate-slow-zoom {
    animation: slow-zoom 30s ease-in-out infinite alternate;
}
</style>
