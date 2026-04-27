<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { GraduationCap, ArrowLeft, Mail, Phone, MapPin, Send, MessageCircle } from 'lucide-vue-next';
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
    <Head title="Contact Support - Cloud School System" />
    
    <div class="min-h-screen bg-slate-50 font-sans text-slate-900 transition-colors duration-500 dark:bg-[#020617] dark:text-white">
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
                            <span class="text-[10px] font-bold text-blue-600 tracking-[0.2em] uppercase">Connect With Us</span>
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
            <div class="mx-auto max-w-7xl px-6">
                <div class="grid gap-16 lg:grid-cols-2 items-center">
                    <!-- Info -->
                    <div class="space-y-12">
                        <div>
                            <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50/50 px-4 py-1.5 text-[10px] font-bold tracking-wider text-blue-600 uppercase backdrop-blur-sm dark:border-blue-500/20 dark:bg-blue-500/10 dark:text-blue-400">
                                <MessageCircle class="h-3.5 w-3.5 fill-blue-600/10" />
                                Assistance & Support
                            </div>
                            <h1 class="mb-6 text-4xl font-black leading-tight tracking-tight text-slate-900 sm:text-5xl lg:text-7xl dark:text-white uppercase">
                                Let's <span class="bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">Connect</span>
                            </h1>
                            <p class="text-base font-medium text-slate-500 dark:text-slate-400 leading-relaxed uppercase tracking-wider">Have questions about implementation or need technical support? Our team is available to assist you.</p>
                        </div>
                        
                        <div class="space-y-8">
                            <div class="flex items-center gap-6 group">
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white shadow-xl dark:bg-slate-900 border border-slate-50 dark:border-slate-800 transition-all group-hover:scale-110">
                                    <Mail class="h-6 w-6 text-blue-600" />
                                </div>
                                <div>
                                    <p class="text-[10px] font-black tracking-[0.2em] uppercase text-slate-400 mb-1">Email Correspondence</p>
                                    <p class="text-sm font-black tracking-tight dark:text-white uppercase transition-colors group-hover:text-blue-600">support@cbccore.co.ke</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-6 group">
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white shadow-xl dark:bg-slate-900 border border-slate-50 dark:border-slate-800 transition-all group-hover:scale-110">
                                    <Phone class="h-6 w-6 text-blue-600" />
                                </div>
                                <div>
                                    <p class="text-[10px] font-black tracking-[0.2em] uppercase text-slate-400 mb-1">Telephone Support</p>
                                    <p class="text-sm font-black tracking-tight dark:text-white uppercase transition-colors group-hover:text-blue-600">+254 759 814 390</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-6 group">
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white shadow-xl dark:bg-slate-900 border border-slate-50 dark:border-slate-800 transition-all group-hover:scale-110">
                                    <MapPin class="h-6 w-6 text-blue-600" />
                                </div>
                                <div>
                                    <p class="text-[10px] font-black tracking-[0.2em] uppercase text-slate-400 mb-1">Regional Office</p>
                                    <p class="text-sm font-black tracking-tight dark:text-white uppercase transition-colors group-hover:text-blue-600">Nairobi, Kenya</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form -->
                    <div class="relative">
                        <div class="absolute -inset-4 bg-gradient-to-br from-blue-600/10 to-indigo-600/10 blur-3xl -z-10 rounded-full"></div>
                        <div class="rounded-[2.5rem] bg-white p-10 lg:p-12 shadow-2xl dark:bg-slate-900/60 border border-white dark:border-slate-800 backdrop-blur-sm">
                            <form @submit.prevent="submit" class="space-y-6">
                                <div class="grid gap-6 sm:grid-cols-2">
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black tracking-[0.2em] uppercase text-slate-400">Full Name</label>
                                        <input v-model="form.name" type="text" placeholder="John Doe" class="w-full rounded-xl border border-slate-100 bg-slate-50/50 px-5 py-4 text-xs font-bold transition-all focus:border-blue-600 focus:bg-white focus:outline-none dark:border-slate-800 dark:bg-slate-950" required />
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[10px] font-black tracking-[0.2em] uppercase text-slate-400">Phone Number</label>
                                        <input v-model="form.phone" type="tel" placeholder="+254..." class="w-full rounded-xl border border-slate-100 bg-slate-50/50 px-5 py-4 text-xs font-bold transition-all focus:border-blue-600 focus:bg-white focus:outline-none dark:border-slate-800 dark:bg-slate-950" required />
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black tracking-[0.2em] uppercase text-slate-400">Email Address</label>
                                    <input v-model="form.email" type="email" placeholder="school@example.com" class="w-full rounded-xl border border-slate-100 bg-slate-50/50 px-5 py-4 text-xs font-bold transition-all focus:border-blue-600 focus:bg-white focus:outline-none dark:border-slate-800 dark:bg-slate-950" required />
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black tracking-[0.2em] uppercase text-slate-400">Message Brief</label>
                                    <textarea v-model="form.message" rows="5" placeholder="How can we help your school?" class="w-full rounded-xl border border-slate-100 bg-slate-50/50 px-5 py-4 text-xs font-bold transition-all focus:border-blue-600 focus:bg-white focus:outline-none dark:border-slate-800 dark:bg-slate-950" required></textarea>
                                </div>
                                <Button :disabled="form.processing" class="h-14 w-full rounded-2xl bg-blue-600 text-[11px] font-black tracking-[0.3em] uppercase shadow-2xl shadow-blue-600/20 hover:scale-[1.02] active:scale-95 transition-all">
                                    <Send v-if="!form.processing" class="mr-2 h-4 w-4" />
                                    {{ form.processing ? 'Dispatching Inquiry...' : 'Send Inquiry' }}
                                </Button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        
        <footer class="border-t border-slate-100 dark:border-slate-800 py-12 text-center bg-white/50 dark:bg-slate-950/50 backdrop-blur-sm">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-loose">
                &copy; {{ new Date().getFullYear() }} DOITIX TECH LABS. ALL RIGHTS RESERVED. <br>
                NAIROBI, KENYA
            </div>
        </footer>

        <Toast />
    </div>
</template>
