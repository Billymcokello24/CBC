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
    School,
    Verified
} from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import InputError from '@/components/InputError.vue';
import AuthSplitLayout from '@/layouts/auth/AuthSplitLayout.vue';
import Toast from '@/components/Toast.vue';

const step = ref(1);
const showPassword = ref(false);
const isVerifying = ref(false);
const verificationError = ref('');

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
        title="Institution Onboarding"
        description="Securely register your school on the Cloud School platform."
        image="https://images.unsplash.com/photo-1544717297-fa95ec97eaf0?q=80&w=2070&auto=format&fit=crop"
        quote="Innovation in education is not just about tools, it's about the people who use them to inspire."
        author="Prof. Alice Wambui"
        role="Director of Education"
    >
        <Head title="School Registration" />

        <div class="relative">
            <!-- Progress Bar -->
            <div class="absolute -top-12 left-0 right-0 h-1 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                <div 
                    class="h-full bg-blue-600 transition-all duration-500"
                    :style="{ width: (step / 3 * 100) + '%' }"
                ></div>
            </div>

            <!-- Step 1: School & Admin Details -->
            <div v-if="step === 1" class="space-y-8 animate-in fade-in slide-in-from-right-4 duration-500">
                <div class="space-y-4">
                    <div class="space-y-2">
                        <Label for="school_name" class="text-[10px] font-black tracking-[0.2em] text-slate-400 uppercase">Institutional Name</Label>
                        <div class="group relative">
                            <Building2 class="absolute top-1/2 left-4 h-4 w-4 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-blue-600" />
                            <Input
                                id="school_name"
                                v-model="form.school_name"
                                type="text"
                                placeholder="Enter school name"
                                class="h-14 border-slate-100 dark:border-slate-800 rounded-xl pl-12 text-xs font-bold transition-all focus:ring-8 focus:ring-blue-600/5"
                                required
                            />
                        </div>
                        <InputError :message="form.errors.school_name" />
                    </div>

                    <div class="space-y-2">
                        <Label for="school_level" class="text-[10px] font-black tracking-[0.2em] text-slate-400 uppercase">Educational Level</Label>
                        <select
                            id="school_level"
                            v-model="form.school_level"
                            class="flex h-14 w-full rounded-xl border border-slate-100 bg-white px-4 py-2 text-xs font-bold ring-offset-white focus-visible:outline-none focus-visible:ring-8 focus-visible:ring-blue-600/5 dark:border-slate-800 dark:bg-slate-950 dark:ring-offset-slate-950"
                        >
                            <option>Primary</option>
                            <option>Secondary</option>
                            <option>Integrated</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <Label for="name" class="text-[10px] font-black tracking-[0.2em] text-slate-400 uppercase">Administrator Full Name</Label>
                        <div class="group relative">
                            <User class="absolute top-1/2 left-4 h-4 w-4 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-blue-600" />
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                placeholder="Admin contact person"
                                class="h-14 border-slate-100 dark:border-slate-800 rounded-xl pl-12 text-xs font-bold transition-all focus:ring-8 focus:ring-blue-600/5"
                                required
                            />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="email" class="text-[10px] font-black tracking-[0.2em] text-slate-400 uppercase">Official Email Address</Label>
                        <div class="group relative">
                            <Mail class="absolute top-1/2 left-4 h-4 w-4 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-blue-600" />
                            <Input
                                id="email"
                                v-model="form.email"
                                type="email"
                                placeholder="name@school.com"
                                class="h-14 border-slate-100 dark:border-slate-800 rounded-xl pl-12 text-xs font-bold transition-all focus:ring-8 focus:ring-blue-600/5"
                                required
                            />
                        </div>
                        <InputError :message="form.errors.email" />
                    </div>
                </div>

                <Button
                    @click="sendOtp"
                    type="button"
                    class="h-14 w-full bg-blue-600 text-[11px] font-black uppercase tracking-[0.2em] shadow-2xl shadow-blue-600/20 hover:scale-[1.02] active:scale-95 transition-all"
                    :disabled="form.processing"
                >
                    <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin text-white" />
                    Dispatch Verification Code
                    <ArrowRight v-if="!form.processing" class="ml-2 h-4 w-4" />
                </Button>
            </div>

            <!-- Step 2: OTP Verification -->
            <div v-else-if="step === 2" class="space-y-10 animate-in fade-in slide-in-from-right-4 duration-500">
                <div class="space-y-4">
                    <button @click="step = 1" class="text-[10px] font-black tracking-[0.2em] text-blue-600 uppercase flex items-center gap-2 hover:opacity-70 transition-opacity">
                        <ArrowLeft class="h-3 w-3" /> Correction Needed
                    </button>
                    <div class="p-6 bg-blue-50/50 dark:bg-blue-500/5 border border-blue-100 dark:border-blue-500/20 rounded-[2rem]">
                        <p class="text-sm font-medium text-slate-600 dark:text-slate-400 leading-relaxed italic">
                            Verification code dispatched to: <br>
                            <span class="font-black text-blue-600 uppercase tracking-tighter">{{ form.email }}</span>
                        </p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="space-y-2 text-center lg:text-left">
                        <Label for="otp" class="text-[10px] font-black tracking-[0.4em] text-slate-400 uppercase">Authentication Code</Label>
                        <div class="group relative">
                            <Verified class="absolute top-1/2 left-4 h-5 w-5 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-blue-600" />
                            <Input
                                id="otp"
                                v-model="form.otp"
                                type="text"
                                maxlength="6"
                                placeholder="••••••"
                                class="h-20 border-slate-100 dark:border-slate-800 rounded-[2rem] pl-14 text-center tracking-[0.8em] text-3xl font-black transition-all focus:ring-12 focus:ring-blue-600/5 dark:bg-slate-950"
                                required
                            />
                        </div>
                        <p v-if="verificationError" class="mt-4 text-[10px] font-black tracking-[0.1em] text-red-500 uppercase">{{ verificationError }}</p>
                    </div>
                </div>

                <Button
                    @click="verifyOtp"
                    type="button"
                    class="h-16 w-full bg-blue-600 text-[11px] font-black uppercase tracking-[0.3em] shadow-2xl shadow-blue-600/20 hover:scale-[1.02] active:scale-95 transition-all"
                    :disabled="isVerifying || form.otp.length < 6"
                >
                    <Loader2 v-if="isVerifying" class="mr-2 h-4 w-4 animate-spin text-white" />
                    Verify & Continue
                    <ArrowRight v-if="!isVerifying" class="ml-2 h-4 w-4" />
                </Button>
                
                <p class="text-center text-[10px] font-black tracking-[0.2em] text-slate-400 uppercase">
                    Code not received? 
                    <button @click="sendOtp" class="text-blue-600 hover:scale-105 transition-transform ml-2">Request New</button>
                </p>
            </div>

            <!-- Step 3: Password Choice -->
            <div v-else-if="step === 3" class="space-y-10 animate-in fade-in slide-in-from-right-4 duration-500">
                <div class="p-6 bg-emerald-50/50 dark:bg-emerald-500/5 border border-emerald-100 dark:border-emerald-500/20 rounded-[2rem] flex items-center gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-500 text-white shadow-lg shadow-emerald-500/20">
                        <CheckCircle2 class="h-6 w-6" />
                    </div>
                    <p class="text-xs font-black uppercase tracking-wider text-emerald-600">Identity Successfully Verified</p>
                </div>

                <form @submit.prevent="submitFinal" class="space-y-8">
                    <div class="space-y-6">
                        <div class="space-y-2">
                            <Label for="password" class="text-[10px] font-black tracking-[0.2em] text-slate-400 uppercase">Secure Access Password</Label>
                            <div class="group relative">
                                <Lock class="absolute top-1/2 left-4 h-4 w-4 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-blue-600" />
                                <Input
                                    id="password"
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    placeholder="••••••••"
                                    class="h-14 border-slate-100 dark:border-slate-800 rounded-xl pl-12 text-xs font-bold transition-all focus:ring-8 focus:ring-blue-600/5"
                                    required
                                />
                                <button type="button" @click="showPassword = !showPassword" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-blue-600">
                                    <Eye v-if="!showPassword" class="h-4 w-4" />
                                    <EyeOff v-else class="h-4 w-4" />
                                </button>
                            </div>
                            <InputError :message="form.errors.password" />
                        </div>

                        <div class="space-y-2">
                            <Label for="password_confirmation" class="text-[10px] font-black tracking-[0.2em] text-slate-400 uppercase">Confirm Access Password</Label>
                            <div class="group relative">
                                <Lock class="absolute top-1/2 left-4 h-4 w-4 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-blue-600" />
                                <Input
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    :type="showPassword ? 'text' : 'password'"
                                    placeholder="••••••••"
                                    class="h-14 border-slate-100 dark:border-slate-800 rounded-xl pl-12 text-xs font-bold transition-all focus:ring-8 focus:ring-blue-600/5"
                                    required
                                />
                            </div>
                        </div>
                    </div>

                    <Button
                        type="submit"
                        class="h-16 w-full bg-blue-600 text-[11px] font-black uppercase tracking-[0.3em] shadow-2xl shadow-blue-600/20 hover:scale-[1.02] active:scale-95 transition-all"
                        :disabled="form.processing"
                    >
                        <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin text-white" />
                        Finish Institutional Setup
                    </Button>
                </form>
            </div>
        </div>

        <Toast />
    </AuthSplitLayout>
</template>
