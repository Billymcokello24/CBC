<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { GraduationCap, Mail, Lock, Eye, EyeOff, Loader2 } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import InputError from '@/components/InputError.vue';
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
    <Head title="Sign In" />
    <div class="min-h-screen flex">
        <!-- Left Side - Form -->
        <div class="flex-1 flex flex-col justify-center px-4 py-12 sm:px-6 lg:px-20 xl:px-24">
            <div class="mx-auto w-full max-w-sm">
                <div class="flex items-center gap-3 mb-8">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary">
                        <GraduationCap class="h-7 w-7 text-white" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">CBC Platform</h1>
                        <p class="text-xs text-muted-foreground">by Doitix Tech Labs</p>
                    </div>
                </div>
                <div class="mb-8">
                    <h2 class="text-2xl font-bold tracking-tight">Welcome back</h2>
                    <p class="mt-2 text-sm text-muted-foreground">
                        Sign in to your account to continue
                    </p>
                </div>
                <div v-if="status" class="mb-4 rounded-lg bg-green-50 p-4 text-sm font-medium text-green-600 dark:bg-green-900/30">
                    {{ status }}
                </div>
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="space-y-2">
                        <Label for="email">Email address</Label>
                        <div class="relative">
                            <Mail class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input 
                                id="email"
                                v-model="form.email"
                                type="email"
                                placeholder="name@school.com"
                                class="pl-10"
                                required
                                autofocus
                            />
                        </div>
                        <InputError :message="form.errors.email" />
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <Label for="password">Password</Label>
                            <Link v-if="canResetPassword" href="/forgot-password" class="text-sm text-primary hover:underline">
                                Forgot password?
                            </Link>
                        </div>
                        <div class="relative">
                            <Lock class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input 
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                placeholder="••••••••"
                                class="pl-10 pr-10"
                                required
                            />
                            <button 
                                type="button"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                                @click="showPassword = !showPassword"
                            >
                                <Eye v-if="!showPassword" class="h-4 w-4" />
                                <EyeOff v-else class="h-4 w-4" />
                            </button>
                        </div>
                        <InputError :message="form.errors.password" />
                    </div>
                    <div class="flex items-center space-x-2">
                        <Checkbox id="remember" v-model:checked="form.remember" />
                        <Label for="remember" class="text-sm font-normal">Remember me for 30 days</Label>
                    </div>
                    <Button type="submit" class="w-full" size="lg" :disabled="form.processing">
                        <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                        Sign in
                    </Button>
                </form>
                <p class="mt-8 text-center text-sm text-muted-foreground">
                    Don't have an account?
                    <Link href="/register" class="font-medium text-primary hover:underline">Sign up</Link>
                </p>
            </div>
        </div>
        <!-- Right Side - Branding -->
        <div class="hidden lg:flex lg:flex-1 bg-gradient-to-br from-primary to-blue-700 p-12 text-white">
            <div class="flex flex-col justify-between">
                <div></div>
                <div class="space-y-6">
                    <blockquote class="text-2xl font-medium leading-relaxed">
                        "CBC Platform has transformed how we manage our school. From student admissions to report cards, everything is seamless."
                    </blockquote>
                    <div>
                        <p class="font-semibold">Dr. Jane Muthoni</p>
                        <p class="text-white/70">Principal, Nairobi Academy</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 text-sm text-white/60">
                    <span>© 2026 Doitix Tech Labs</span>
                    <span>•</span>
                    <span>Nairobi, Kenya</span>
                </div>
            </div>
        </div>
    </div>
</template>
