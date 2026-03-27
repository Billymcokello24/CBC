<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Mail, Lock, Eye, EyeOff, Loader2, User, Building2 } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import InputError from '@/components/InputError.vue';
import AuthSplitLayout from '@/layouts/auth/AuthSplitLayout.vue';

const showPassword = ref(false);

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    school_name: '',
    role: 'school_admin',
});

const submit = () => {
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthSplitLayout
        title="Create your account"
        description="Join our platform and start managing your school digitally"
        image="/images/auth/register_bg.png"
        quote="Education is the most powerful weapon which you can use to change the world."
        author="Nelson Mandela"
        role="Revolutionary & Statesman"
    >
        <Head title="Create Account" />

        <form @submit.prevent="submit" class="space-y-5">
            <div class="space-y-2">
                <Label for="name" class="text-xs font-semibold uppercase tracking-wider text-slate-500">Full Name</Label>
                <div class="group relative">
                    <User class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-teal-500" />
                    <Input id="name" v-model="form.name" type="text" placeholder="John Doe" class="h-11 border-slate-200 pl-10 transition-all focus:border-teal-500 focus:ring-teal-500/20 dark:border-slate-800" required autofocus />
                </div>
                <InputError :message="form.errors.name" />
            </div>

            <div class="space-y-2">
                <Label for="email" class="text-xs font-semibold uppercase tracking-wider text-slate-500">Email address</Label>
                <div class="group relative">
                    <Mail class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-teal-500" />
                    <Input id="email" v-model="form.email" type="email" placeholder="name@school.com" class="h-11 border-slate-200 pl-10 transition-all focus:border-teal-500 focus:ring-teal-500/20 dark:border-slate-800" required />
                </div>
                <InputError :message="form.errors.email" />
            </div>

            <div class="space-y-2">
                <Label for="school_name" class="text-xs font-semibold uppercase tracking-wider text-slate-500">School Name</Label>
                <div class="group relative">
                    <Building2 class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-teal-500" />
                    <Input id="school_name" v-model="form.school_name" type="text" placeholder="Nairobi Primary School" class="h-11 border-slate-200 pl-10 transition-all focus:border-teal-500 focus:ring-teal-500/20 dark:border-slate-800" required />
                </div>
                <InputError :message="form.errors.school_name" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <Label for="password" class="text-xs font-semibold uppercase tracking-wider text-slate-500">Password</Label>
                    <div class="group relative">
                        <Lock class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-teal-500" />
                        <Input id="password" v-model="form.password" :type="showPassword ? 'text' : 'password'" placeholder="••••••••" class="h-11 border-slate-200 pl-10 transition-all focus:border-teal-500 focus:ring-teal-500/20 dark:border-slate-800" required />
                    </div>
                    <InputError :message="form.errors.password" />
                </div>
                <div class="space-y-2">
                    <Label for="password_confirmation" class="text-xs font-semibold uppercase tracking-wider text-slate-500">Confirm</Label>
                    <div class="group relative">
                        <Lock class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-teal-500" />
                        <Input id="password_confirmation" v-model="form.password_confirmation" :type="showPassword ? 'text' : 'password'" placeholder="••••••••" class="h-11 border-slate-200 pl-10 transition-all focus:border-teal-500 focus:ring-teal-500/20 dark:border-slate-800" required />
                    </div>
                </div>
            </div>

            <div class="flex items-center space-x-2">
                <Checkbox id="show_password" :checked="showPassword" @update:checked="showPassword = $event" class="border-slate-300 text-teal-600 focus:ring-teal-500" />
                <Label for="show_password" class="text-xs font-medium text-slate-500 cursor-pointer">Show characters</Label>
            </div>

            <Button type="submit" class="h-11 w-full bg-teal-600 text-white shadow-lg shadow-teal-600/20 hover:bg-teal-700 hover:shadow-teal-600/30 dark:bg-teal-600 dark:hover:bg-teal-500" :disabled="form.processing">
                <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                Create Account
            </Button>
            
            <p class="text-[10px] text-center text-slate-400 leading-normal">
                By signing up, you agree to our 
                <a href="#" class="text-teal-600 font-medium hover:underline">Terms of Service</a> & 
                <a href="#" class="text-teal-600 font-medium hover:underline">Privacy Policy</a>
            </p>
        </form>

        <p class="text-center text-sm text-slate-500 dark:text-slate-400">
            Already have an account?
            <Link href="/login" class="font-semibold text-teal-600 hover:text-teal-500">Sign in</Link>
        </p>
    </AuthSplitLayout>
</template>
