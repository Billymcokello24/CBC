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
    form.post(route('demo.book'), {
        onSuccess: () => form.reset(),
    });
};

const route = (window as any).route;
</script>

<template>
    <Head title="Contact Support - CBC Core" />
    
    <div class="min-h-screen bg-slate-50 font-sans text-slate-900 transition-colors duration-500 dark:bg-[#020617] dark:text-white">
        <!-- Minimal Nav -->
        <nav class="border-b bg-white/80 backdrop-blur-xl dark:border-slate-800 dark:bg-[#020617]/80">
            <div class="mx-auto max-w-7xl px-6">
                <div class="flex h-16 items-center justify-between">
                    <Link :href="route('home')" class="flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600 shadow-sm">
                            <GraceCap class="h-5 w-5 text-white" />
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

        <main class="py-16 lg:py-24">
            <div class="mx-auto max-w-7xl px-6">
                <div class="grid gap-12 lg:grid-cols-2">
                    <!-- Info -->
                    <div>
                        <h1 class="mb-6 text-3xl font-bold tracking-tight lg:text-4xl">Get in <span class="text-blue-600">Touch</span></h1>
                        <p class="mb-10 text-base text-slate-500 dark:text-slate-400">Have questions about implementation or need technical support? Our team is available to assist you.</p>
                        
                        <div class="space-y-6">
                            <div class="flex items-center gap-4">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white shadow-sm dark:bg-slate-900 border dark:border-slate-800">
                                    <Mail class="h-5 w-5 text-blue-600" />
                                </div>
                                <div>
                                    <p class="text-[9px] font-bold tracking-widest uppercase text-slate-400">Email Correspondence</p>
                                    <p class="text-sm font-semibold">support@cbccore.co.ke</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white shadow-sm dark:bg-slate-900 border dark:border-slate-800">
                                    <Phone class="h-5 w-5 text-blue-600" />
                                </div>
                                <div>
                                    <p class="text-[9px] font-bold tracking-widest uppercase text-slate-400">Telephone Support</p>
                                    <p class="text-sm font-semibold">+254 759 814 390</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white shadow-sm dark:bg-slate-900 border dark:border-slate-800">
                                    <MapPin class="h-5 w-5 text-blue-600" />
                                </div>
                                <div>
                                    <p class="text-[9px] font-bold tracking-widest uppercase text-slate-400">Regional Office</p>
                                    <p class="text-sm font-semibold">Nairobi, Kenya</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form -->
                    <div class="rounded-xl bg-white p-8 shadow-lg dark:bg-slate-900/50 border dark:border-slate-800">
                        <form @submit.prevent="submit" class="space-y-4">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="text-[9px] font-bold tracking-widest uppercase text-slate-400">Full Name</label>
                                    <input v-model="form.name" type="text" class="mt-1.5 w-full rounded-md border border-slate-200 bg-slate-50 px-4 py-2.5 text-xs focus:border-blue-600 focus:outline-none dark:border-slate-700 dark:bg-slate-900" required />
                                </div>
                                <div>
                                    <label class="text-[9px] font-bold tracking-widest uppercase text-slate-400">Phone Number</label>
                                    <input v-model="form.phone" type="tel" class="mt-1.5 w-full rounded-md border border-slate-200 bg-slate-50 px-4 py-2.5 text-xs focus:border-blue-600 focus:outline-none dark:border-slate-700 dark:bg-slate-900" required />
                                </div>
                            </div>
                            <div>
                                <label class="text-[9px] font-bold tracking-widest uppercase text-slate-400">Electronic Mail</label>
                                <input v-model="form.email" type="email" class="mt-1.5 w-full rounded-md border border-slate-200 bg-slate-50 px-4 py-2.5 text-xs focus:border-blue-600 focus:outline-none dark:border-slate-700 dark:bg-slate-900" required />
                            </div>
                            <div>
                                <label class="text-[9px] font-bold tracking-widest uppercase text-slate-400">Message Brief</label>
                                <textarea v-model="form.message" rows="4" class="mt-1.5 w-full rounded-md border border-slate-200 bg-slate-50 px-4 py-2.5 text-xs focus:border-blue-600 focus:outline-none dark:border-slate-700 dark:bg-slate-900" required></textarea>
                            </div>
                            <Button :disabled="form.processing" class="h-12 w-full rounded-md bg-blue-600 text-xs font-bold tracking-widest uppercase shadow-md">
                                {{ form.processing ? 'Dispatching...' : 'Send Inquiry' }}
                            </Button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
        <Toast />
    </div>
</template>
