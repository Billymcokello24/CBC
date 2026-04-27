<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Mail, Phone, MapPin, Send, MessageCircle } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import LandingLayout from '@/layouts/LandingLayout.vue';

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
    
    <LandingLayout>
        <main class="pt-32 pb-24 lg:pt-48">
            <div class="container mx-auto px-6 max-w-7xl">
                <div class="grid gap-16 lg:grid-cols-2 items-center">
                    <!-- Info -->
                    <div class="space-y-12">
                        <div>
                            <div class="mb-6 inline-flex items-center gap-2 rounded-full px-4 py-1.5 text-[11px] font-bold tracking-wider text-blue-600 uppercase bg-blue-50 dark:bg-blue-500/10">
                                <MessageCircle class="h-3.5 w-3.5" />
                                Assistance & Support
                            </div>
                            <h1 class="mb-6 text-4xl font-bold leading-tight tracking-tight text-slate-900 sm:text-5xl lg:text-7xl dark:text-white uppercase">
                                Let's <span class="text-blue-600">Connect</span>
                            </h1>
                            <p class="text-lg font-medium text-slate-500 dark:text-slate-400 leading-relaxed uppercase tracking-wider">Have questions about implementation or need technical support? Our team is available to assist you.</p>
                        </div>
                        
                        <div class="space-y-8">
                            <div class="flex items-center gap-6 group">
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#F8F9FF] dark:bg-slate-900 shadow-sm transition-all group-hover:scale-110">
                                    <Mail class="h-6 w-6 text-blue-600" />
                                </div>
                                <div>
                                    <p class="text-[11px] font-black tracking-[0.2em] uppercase text-slate-400 mb-1">Email Correspondence</p>
                                    <p class="text-base font-bold tracking-tight text-slate-900 dark:text-white uppercase transition-colors group-hover:text-blue-600">support@cbccore.co.ke</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-6 group">
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#F8F9FF] dark:bg-slate-900 shadow-sm transition-all group-hover:scale-110">
                                    <Phone class="h-6 w-6 text-blue-600" />
                                </div>
                                <div>
                                    <p class="text-[11px] font-black tracking-[0.2em] uppercase text-slate-400 mb-1">Telephone Support</p>
                                    <p class="text-base font-bold tracking-tight text-slate-900 dark:text-white uppercase transition-colors group-hover:text-blue-600">+254 759 814 390</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-6 group">
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#F8F9FF] dark:bg-slate-900 shadow-sm transition-all group-hover:scale-110">
                                    <MapPin class="h-6 w-6 text-blue-600" />
                                </div>
                                <div>
                                    <p class="text-[11px] font-black tracking-[0.2em] uppercase text-slate-400 mb-1">Regional Office</p>
                                    <p class="text-base font-bold tracking-tight text-slate-900 dark:text-white uppercase transition-colors group-hover:text-blue-600">Nairobi, Kenya</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form -->
                    <div class="relative">
                        <div class="rounded-[40px] bg-white p-10 lg:p-12 shadow-2xl dark:bg-slate-900/60 border border-slate-50 dark:border-slate-800 backdrop-blur-sm">
                            <form @submit.prevent="submit" class="space-y-6">
                                <div class="grid gap-6 sm:grid-cols-2">
                                    <div class="space-y-2">
                                        <label class="text-[11px] font-black tracking-[0.2em] uppercase text-slate-400 pl-1">Full Name</label>
                                        <input v-model="form.name" type="text" placeholder="John Doe" class="h-14 w-full rounded-2xl bg-slate-50 dark:bg-slate-800 border-transparent focus:border-blue-500 transition-all px-6 text-sm font-medium" required />
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-[11px] font-black tracking-[0.2em] uppercase text-slate-400 pl-1">Phone Number</label>
                                        <input v-model="form.phone" type="tel" placeholder="+254..." class="h-14 w-full rounded-2xl bg-slate-50 dark:bg-slate-800 border-transparent focus:border-blue-500 transition-all px-6 text-sm font-medium" required />
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[11px] font-black tracking-[0.2em] uppercase text-slate-400 pl-1">Email Address</label>
                                    <input v-model="form.email" type="email" placeholder="school@example.com" class="h-14 w-full rounded-2xl bg-slate-50 dark:bg-slate-800 border-transparent focus:border-blue-500 transition-all px-6 text-sm font-medium" required />
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[11px] font-black tracking-[0.2em] uppercase text-slate-400 pl-1">Message Brief</label>
                                    <textarea v-model="form.message" rows="5" placeholder="How can we help your school?" class="w-full rounded-2xl bg-slate-50 dark:bg-slate-800 border-transparent focus:border-blue-500 transition-all p-6 text-sm font-medium" required></textarea>
                                </div>
                                <Button :disabled="form.processing" class="h-16 w-full rounded-2xl bg-[#3B63F6] text-sm font-bold shadow-2xl shadow-blue-600/20 hover:scale-[1.02] active:scale-95 transition-all">
                                    <Send v-if="!form.processing" class="mr-2 h-4 w-4" />
                                    {{ form.processing ? 'Dispatching Inquiry...' : 'Send Inquiry' }}
                                </Button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </LandingLayout>
</template>

