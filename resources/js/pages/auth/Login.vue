<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Mail, Lock, Eye, EyeOff, Loader2 } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import InputError from '@/components/InputError.vue';
import AuthSplitLayout from '@/layouts/auth/AuthSplitLayout.vue';

defineProps<{ canResetPassword?: boolean; status?: string }>();

const showPassword = ref(false);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <AuthSplitLayout
        title="Welcome back"
        description="Enter your credentials to access your account"
        image="/images/auth/login_bg.png"
        quote="The beautiful thing about learning is that no one can take it away from you."
        author="B.B. King"
        role="Legendary Musician"
    >
        <Head title="Sign In" />

        <div
            v-if="status"
            class="mb-4 rounded-xl bg-emerald-50 p-4 text-sm font-medium text-emerald-600 dark:bg-emerald-900/30"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div class="space-y-2">
                <Label
                    for="email"
                    class="text-xs font-semibold tracking-wider text-slate-500 uppercase"
                    >Email address</Label
                >
                <div class="group relative">
                    <Mail
                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-[#3B63F6]"
                    />
                    <Input
                        id="email"
                        v-model="form.email"
                        type="email"
                        placeholder="name@school.com"
                        class="h-11 border-slate-200 pl-10 transition-all focus:border-[#3B63F6] focus:ring-[#3B63F6]/20 dark:border-slate-800"
                        required
                        autofocus
                    />
                </div>
                <InputError :message="form.errors.email" />
            </div>

            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <Label
                        for="password"
                        class="text-xs font-semibold tracking-wider text-slate-500 uppercase"
                        >Password</Label
                    >
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-xs font-medium text-[#3B63F6] hover:text-blue-500"
                    >
                        Forgot password?
                    </Link>
                </div>
                <div class="group relative">
                    <Lock
                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-[#3B63F6]"
                    />
                    <Input
                        id="password"
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        placeholder="••••••••"
                        class="h-11 border-slate-200 pr-10 pl-10 transition-all focus:border-[#3B63F6] focus:ring-[#3B63F6]/20 dark:border-slate-800"
                        required
                    />
                    <button
                        type="button"
                        class="absolute top-1/2 right-3 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300"
                        @click="showPassword = !showPassword"
                    >
                        <Eye v-if="!showPassword" class="h-4 w-4" />
                        <EyeOff v-else class="h-4 w-4" />
                    </button>
                </div>
                <InputError :message="form.errors.password" />
            </div>

            <div class="flex items-center space-x-2">
                <Checkbox
                    id="remember"
                    v-model:checked="form.remember"
                    class="border-slate-300 text-[#3B63F6] focus:ring-[#3B63F6]"
                />
                <Label
                    for="remember"
                    class="cursor-pointer text-sm font-medium text-slate-600 dark:text-slate-400"
                    >Stay signed in</Label
                >
            </div>

            <Button
                type="submit"
                class="h-11 w-full bg-[#3B63F6] text-white shadow-lg shadow-blue-600/20 hover:bg-blue-700 hover:shadow-blue-600/30 dark:bg-blue-600 dark:hover:bg-blue-500"
                :disabled="form.processing"
            >
                <Loader2
                    v-if="form.processing"
                    class="mr-2 h-4 w-4 animate-spin"
                />
                Sign in to Platform
            </Button>
        </form>

        <p class="text-center text-sm text-slate-500 dark:text-slate-400">
            New to our platform?
            <Link
                :href="route('register')"
                class="font-semibold text-[#3B63F6] hover:text-blue-500"
                >Create an account</Link
            >
        </p>
    </AuthSplitLayout>
</template>

