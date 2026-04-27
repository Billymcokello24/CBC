<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import {
    Mail,
    Lock,
    Eye,
    EyeOff,
    Loader2,
    User,
    Building2,
    CheckCircle2,
    ArrowRight,
    ArrowLeft,
    School
} from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import InputError from '@/components/InputError.vue';
import AuthSplitLayout from '@/layouts/auth/AuthSplitLayout.vue';
import Toast from '@/components/Toast.vue';

const step = ref(1);
const showPassword = ref(false);
const otpSent = ref(false);
const isVerifying = ref(false);
const verificationError = ref('');
const successMessage = ref('');

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    school_name: '',
    school_level: 'Primary',
    otp: '',
});

const sendOtp = () => {
    router.post(route('register.send-otp'), {
        school_name: form.school_name,
        admin_name: form.name,
        email: form.email,
        school_level: form.school_level
    }, {
        onSuccess: () => step.value = 2,
    });
};

const verifyOtp = () => {
    isVerifying.value = true;
    router.post(route('register.verify-otp'), {
        email: form.email,
        otp: form.otp
    }, {
        onSuccess: () => step.value = 3,
        onFinish: () => isVerifying.value = false,
    });
};

const submitFinal = () => {
    form.post(route('register.complete'), {
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

        <!-- Step 1: School & Admin Details -->
        <div v-if="step === 1" class="space-y-6 animate-in fade-in slide-in-from-right-4 duration-500">
            <div class="space-y-1">
                <h2 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">School Information</h2>
                <p class="text-sm text-slate-500">Tell us about your institution to get started.</p>
            </div>

            <div class="space-y-4">
                <div class="space-y-2">
                    <Label for="school_name" class="text-xs font-semibold tracking-wider text-slate-500 uppercase">School Name</Label>
                    <div class="group relative">
                        <Building2 class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-teal-500" />
                        <Input
                            id="school_name"
                            v-model="form.school_name"
                            type="text"
                            placeholder="e.g. Greenwood Academy"
                            class="h-11 border-slate-200 pl-10 transition-all focus:border-teal-500 focus:ring-teal-500/20 dark:border-slate-800"
                            required
                        />
                    </div>
                    <InputError :message="form.errors.school_name" />
                </div>

                <div class="space-y-2">
                    <Label for="school_level" class="text-xs font-semibold tracking-wider text-slate-500 uppercase">School Level</Label>
                    <select
                        id="school_level"
                        v-model="form.school_level"
                        class="flex h-11 w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm ring-offset-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-teal-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-800 dark:bg-slate-950 dark:ring-offset-slate-950"
                    >
                        <option>Primary</option>
                        <option>Secondary</option>
                        <option>Integrated</option>
                    </select>
                </div>

                <div class="space-y-2">
                    <Label for="name" class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Admin Full Name</Label>
                    <div class="group relative">
                        <User class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-teal-500" />
                        <Input
                            id="name"
                            v-model="form.name"
                            type="text"
                            placeholder="John Doe"
                            class="h-11 border-slate-200 pl-10 transition-all focus:border-teal-500 focus:ring-teal-500/20 dark:border-slate-800"
                            required
                        />
                    </div>
                </div>

                <div class="space-y-2">
                    <Label for="email" class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Admin Email address</Label>
                    <div class="group relative">
                        <Mail class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-teal-500" />
                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                            placeholder="name@school.com"
                            class="h-11 border-slate-200 pl-10 transition-all focus:border-teal-500 focus:ring-teal-500/20 dark:border-slate-800"
                            required
                        />
                    </div>
                    <InputError :message="form.errors.email" />
                </div>
            </div>

            <Button
                @click="sendOtp"
                type="button"
                class="h-11 w-full bg-teal-600 text-white shadow-lg shadow-teal-600/20 hover:bg-teal-700 hover:shadow-teal-600/30 dark:bg-teal-600 dark:hover:bg-teal-500"
                :disabled="form.processing"
            >
                <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                Send Verification Code
                <ArrowRight v-if="!form.processing" class="ml-2 h-4 w-4" />
            </Button>
        </div>

        <!-- Step 2: OTP Verification -->
        <div v-else-if="step === 2" class="space-y-6 animate-in fade-in slide-in-from-right-4 duration-500">
            <div class="space-y-1">
                <button @click="step = 1" class="text-xs text-teal-600 hover:underline flex items-center mb-2">
                    <ArrowLeft class="mr-1 h-3 w-3" /> Back to details
                </button>
                <h2 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Verify Email</h2>
                <p class="text-sm text-slate-500">We've sent a 6-digit code to <span class="font-semibold text-slate-900 dark:text-slate-200">{{ form.email }}</span></p>
            </div>

            <div class="space-y-4">
                <div class="space-y-2">
                    <Label for="otp" class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Verification Code</Label>
                    <div class="group relative">
                        <CheckCircle2 class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-teal-500" />
                        <Input
                            id="otp"
                            v-model="form.otp"
                            type="text"
                            maxlength="6"
                            placeholder="Enter 6-digit code"
                            class="h-11 border-slate-200 pl-10 text-center tracking-[0.5em] font-mono text-lg transition-all focus:border-teal-500 focus:ring-teal-500/20 dark:border-slate-800"
                            required
                        />
                    </div>
                    <p v-if="verificationError" class="text-xs text-red-500 text-center">{{ verificationError }}</p>
                </div>
            </div>

            <Button
                @click="verifyOtp"
                type="button"
                class="h-11 w-full bg-teal-600 text-white shadow-lg shadow-teal-600/20 hover:bg-teal-700 hover:shadow-teal-600/30"
                :disabled="isVerifying || form.otp.length < 6"
            >
                <Loader2 v-if="isVerifying" class="mr-2 h-4 w-4 animate-spin" />
                Verify Code
                <ArrowRight v-if="!isVerifying" class="ml-2 h-4 w-4" />
            </Button>
            
            <p class="text-center text-xs text-slate-400">
                Didn't receive the code? 
                <button @click="sendOtp" class="text-teal-600 hover:underline font-medium ml-1">Resend</button>
            </p>
        </div>

        <!-- Step 3: Password Choice -->
        <div v-else-if="step === 3" class="space-y-6 animate-in fade-in slide-in-from-right-4 duration-500">
            <div class="space-y-1">
                <h2 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Security Setup</h2>
                <p class="text-sm text-slate-500">Email verified! Now create a secure password for your account.</p>
            </div>

            <form @submit.prevent="submitFinal" class="space-y-5">
                <div class="space-y-4">
                    <div class="space-y-2">
                        <Label for="password" class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Create Password</Label>
                        <div class="group relative">
                            <Lock class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-teal-500" />
                            <Input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                placeholder="••••••••"
                                class="h-11 border-slate-200 pl-10 transition-all focus:border-teal-500 focus:ring-teal-500/20 dark:border-slate-800"
                                required
                            />
                        </div>
                        <InputError :message="form.errors.password" />
                    </div>

                    <div class="space-y-2">
                        <Label for="password_confirmation" class="text-xs font-semibold tracking-wider text-slate-500 uppercase">Confirm Password</Label>
                        <div class="group relative">
                            <Lock class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-teal-500" />
                            <Input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                :type="showPassword ? 'text' : 'password'"
                                placeholder="••••••••"
                                class="h-11 border-slate-200 pl-10 transition-all focus:border-teal-500 focus:ring-teal-500/20 dark:border-slate-800"
                                required
                            />
                        </div>
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    <Checkbox
                        id="show_password"
                        :checked="showPassword"
                        @update:checked="showPassword = $event"
                        class="border-slate-300 text-teal-600 focus:ring-teal-500"
                    />
                    <Label for="show_password" class="cursor-pointer text-xs font-medium text-slate-500">Show characters</Label>
                </div>

                <Button
                    type="submit"
                    class="h-11 w-full bg-teal-600 text-white shadow-lg shadow-teal-600/20 hover:bg-teal-700 hover:shadow-teal-600/30"
                    :disabled="form.processing"
                >
                    <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                    Complete Registration
                </Button>
            </form>
        </div>

        <p v-if="step === 1" class="text-center text-sm text-slate-500 dark:text-slate-400 mt-6">
            Already have an account?
            <Link href="/login" class="font-semibold text-teal-600 hover:text-teal-500">Sign in</Link>
        </p>
        <Toast />
    </AuthSplitLayout>
</template>
