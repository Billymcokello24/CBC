<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { GraduationCap, ArrowLeft, Phone, Mail, MapPin, Loader2, Send } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import Toast from '@/components/Toast.vue';

const form = useForm({
    school_name: '',
    contact_person: '',
    email: '',
    phone: '',
    message: ''
});

const submit = () => {
    form.post(route('book-demo'), {
        onSuccess: () => {
            form.reset();
        }
    });
};
</script>

<template>
    <Head title="Contact Us - CBCCore" />

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

        <main class="py-20 lg:py-40">
            <div class="mx-auto max-w-7xl px-6">
                <div class="grid gap-20 lg:grid-cols-2">
                    <div>
                        <h1 class="mb-8 text-5xl font-black tracking-tight sm:text-7xl">
                            Talk <span class="text-blue-600">to us.</span>
                        </h1>
                        <p class="text-xl text-slate-600 dark:text-slate-400 mb-12 max-w-md">
                            Need help? Want to know more? Our team is always here to listen and help your school get better results.
                        </p>

                        <div class="space-y-8">
                            <div class="flex items-center gap-6">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-600/10 text-blue-600 shadow-sm">
                                    <Phone class="h-6 w-6" />
                                </div>
                                <div>
                                    <p class="text-xs font-black tracking-widest uppercase text-slate-400 mb-1">Call Us</p>
                                    <p class="text-lg font-bold">+254 759 814 390</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-6">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-600/10 text-blue-600 shadow-sm">
                                    <Mail class="h-6 w-6" />
                                </div>
                                <div>
                                    <p class="text-xs font-black tracking-widest uppercase text-slate-400 mb-1">Email Us</p>
                                    <p class="text-lg font-bold">info@doitrixtech.co.ke</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-6">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-600/10 text-blue-600 shadow-sm">
                                    <MapPin class="h-6 w-6" />
                                </div>
                                <div>
                                    <p class="text-xs font-black tracking-widest uppercase text-slate-400 mb-1">Visit Us</p>
                                    <p class="text-lg font-bold">Nairobi, Kenya</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[3rem] bg-slate-50 p-10 dark:bg-slate-900 shadow-2xl shadow-blue-600/5">
                        <h3 class="text-2xl font-black mb-8 dark:text-white">Send us a message</h3>
                        <form @submit.prevent="submit" class="space-y-6">
                             <div>
                                <label class="mb-2 block text-xs font-bold text-slate-400 uppercase tracking-widest">School Name</label>
                                <Input v-model="form.school_name" placeholder="Name of your school" required class="h-14 rounded-2xl" />
                            </div>
                            <div class="grid gap-6 sm:grid-cols-2">
                                <div>
                                    <label class="mb-2 block text-xs font-bold text-slate-400 uppercase tracking-widest">Your Name</label>
                                    <Input v-model="form.contact_person" placeholder="Full Name" required class="h-14 rounded-2xl" />
                                </div>
                                <div>
                                    <label class="mb-2 block text-xs font-bold text-slate-400 uppercase tracking-widest">Phone Number</label>
                                    <Input v-model="form.phone" placeholder="+254..." required class="h-14 rounded-2xl" />
                                </div>
                            </div>
                            <div>
                                <label class="mb-2 block text-xs font-bold text-slate-400 uppercase tracking-widest">Email Address</label>
                                <Input v-model="form.email" type="email" placeholder="mail@example.com" required class="h-14 rounded-2xl" />
                            </div>
                            <div>
                                <label class="mb-2 block text-xs font-bold text-slate-400 uppercase tracking-widest">How can we help?</label>
                                <Textarea v-model="form.message" placeholder="Tell us more about your needs..." class="rounded-2xl min-h-[120px]" />
                            </div>
                            <Button type="submit" :disabled="form.processing" class="h-16 w-full rounded-full bg-blue-600 text-sm font-black tracking-widest shadow-xl shadow-blue-600/20">
                                <template v-if="form.processing">
                                    <Loader2 class="mr-2 h-5 w-5 animate-spin" /> SENDING...
                                </template>
                                <template v-else>
                                    <Send class="mr-2 h-5 w-5" /> SEND MESSAGE
                                </template>
                            </Button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
        
        <Toast />
    </div>
</template>
