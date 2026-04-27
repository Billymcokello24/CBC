<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { GraduationCap, ArrowLeft, Mail, Phone, MapPin, Send } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import Toast from '@/components/Toast.vue';

const form = useForm({
    name: '',
    email: '',
    phone: '',
    subject: '',
    message: '',
});

const submit = () => {
    form.post(route('demo.book'), { // Reuse the demo logic for simplicity or create a separate one
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Contact Us - CBCCore" />
    
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
                <div class="grid gap-16 lg:grid-cols-2">
                    <!-- Info -->
                    <div>
                        <h1 class="mb-8 text-5xl font-black tracking-tight lg:text-7xl">Get in <span class="text-blue-600">touch.</span></h1>
                        <p class="mb-12 text-xl text-slate-500 dark:text-slate-400">Have a question or need help? Our team is always here to listen. Send us a message or call us directly.</p>
                        
                        <div class="space-y-8">
                            <div class="flex items-center gap-6">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white shadow-sm dark:bg-slate-900 border dark:border-slate-800">
                                    <Mail class="h-6 w-6 text-blue-600" />
                                </div>
                                <div>
                                    <p class="text-[10px] font-black tracking-widest uppercase text-slate-400">Email Us</p>
                                    <p class="font-bold">hello@cbccore.co.ke</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-6">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white shadow-sm dark:bg-slate-900 border dark:border-slate-800">
                                    <Phone class="h-6 w-6 text-blue-600" />
                                </div>
                                <div>
                                    <p class="text-[10px] font-black tracking-widest uppercase text-slate-400">Call Us</p>
                                    <p class="font-bold">+254 700 000 000</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-6">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white shadow-sm dark:bg-slate-900 border dark:border-slate-800">
                                    <MapPin class="h-6 w-6 text-blue-600" />
                                </div>
                                <div>
                                    <p class="text-[10px] font-black tracking-widest uppercase text-slate-400">Visit Us</p>
                                    <p class="font-bold">Nairobi, Kenya</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form -->
                    <div class="rounded-[2.5rem] bg-white p-10 shadow-2xl dark:bg-slate-900/50 border dark:border-slate-800">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="grid gap-6 sm:grid-cols-2">
                                <div>
                                    <label class="text-[10px] font-black tracking-widest uppercase text-slate-400">Full Name</label>
                                    <input v-model="form.name" type="text" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm focus:border-blue-600 focus:outline-none dark:border-slate-700 dark:bg-slate-900" required />
                                </div>
                                <div>
                                    <label class="text-[10px] font-black tracking-widest uppercase text-slate-400">Phone</label>
                                    <input v-model="form.phone" type="tel" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm focus:border-blue-600 focus:outline-none dark:border-slate-700 dark:bg-slate-900" required />
                                </div>
                            </div>
                            <div>
                                <label class="text-[10px] font-black tracking-widest uppercase text-slate-400">Email Address</label>
                                <input v-model="form.email" type="email" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm focus:border-blue-600 focus:outline-none dark:border-slate-700 dark:bg-slate-900" required />
                            </div>
                            <div>
                                <label class="text-[10px] font-black tracking-widest uppercase text-slate-400">Your Message</label>
                                <textarea v-model="form.message" rows="4" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm focus:border-blue-600 focus:outline-none dark:border-slate-700 dark:bg-slate-900" required></textarea>
                            </div>
                            <Button :disabled="form.processing" class="h-16 w-full rounded-full bg-blue-600 text-[14px] font-black tracking-widest">
                                {{ form.processing ? 'SENDING message...' : 'SEND MESSAGE' }}
                            </Button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
        <Toast />
    </div>
</template>
