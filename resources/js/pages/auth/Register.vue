<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { GraduationCap, Mail, Lock, Eye, EyeOff, Loader2, User, Building2 } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import InputError from '@/components/InputError.vue';
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
    <Head title="Create Account" />
    <div class="min-h-screen flex">
        <!-- Left Side - Branding -->
        <div class="hidden lg:flex lg:flex-1 bg-gradient-to-br from-primary to-blue-700 p-12 text-white">
            <div class="flex flex-col justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/20">
                        <GraduationCap class="h-7 w-7" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">CBC Platform</h1>
                        <p class="text-xs text-white/70">by Doitix Tech Labs</p>
                    </div>
                </div>
                <div class="space-y-6">
                    <h2 class="text-3xl font-bold">Start your digital learning journey today</h2>
                    <ul class="space-y-4">
                        <li class="flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-white/20">✓</div>
                            <span>Complete CBC curriculum management</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-white/20">✓</div>
                            <span>Student & teacher portals</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-white/20">✓</div>
                            <span>Finance & fee management</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-white/20">✓</div>
                            <span>Real-time analytics & reports</span>
                        </li>
                    </ul>
                </div>
                <div class="flex items-center gap-4 text-sm text-white/60">
                    <span>© 2026 Doitix Tech Labs</span>
                    <span>•</span>
                    <span>Nairobi, Kenya</span>
                </div>
            </div>
        </div>
        <!-- Right Side - Form -->
        <div class="flex-1 flex flex-col justify-center px-4 py-12 sm:px-6 lg:px-20 xl:px-24">
            <div class="mx-auto w-full max-w-sm">
                <div class="flex items-center gap-3 mb-8 lg:hidden">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary">
                        <GraduationCap class="h-7 w-7 text-white" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">CBC Platform</h1>
                        <p class="text-xs text-muted-foreground">by Doitix Tech Labs</p>
                    </div>
                </div>
                <div class="mb-8">
                    <h2 class="text-2xl font-bold tracking-tight">Create your account</h2>
                    <p class="mt-2 text-sm text-muted-foreground">
                        Get started with a free 30-day trial
                    </p>
                </div>
                <form @submit.prevent="submit" class="space-y-5">
                    <div class="space-y-2">
                        <Label for="name">Full Name</Label>
                        <div class="relative">
                            <User class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input id="name" v-model="form.name" type="text" placeholder="John Doe" class="pl-10" required autofocus />
                        </div>
                        <InputError :message="form.errors.name" />
                    </div>
                    <div class="space-y-2">
                        <Label for="email">Email address</Label>
                        <div class="relative">
                            <Mail class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input id="email" v-model="form.email" type="email" placeholder="name@school.com" class="pl-10" required />
                        </div>
                        <InputError :message="form.errors.email" />
                    </div>
                    <div class="space-y-2">
                        <Label for="school_name">School Name</Label>
                        <div class="relative">
                            <Building2 class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input id="school_name" v-model="form.school_name" type="text" placeholder="Nairobi Primary School" class="pl-10" required />
                        </div>
                        <InputError :message="form.errors.school_name" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="password">Password</Label>
                            <div class="relative">
                                <Lock class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                <Input id="password" v-model="form.password" :type="showPassword ? 'text' : 'password'" placeholder="••••••••" class="pl-10" required />
                            </div>
                            <InputError :message="form.errors.password" />
                        </div>
                        <div class="space-y-2">
                            <Label for="password_confirmation">Confirm</Label>
                            <div class="relative">
                                <Lock class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                <Input id="password_confirmation" v-model="form.password_confirmation" :type="showPassword ? 'text' : 'password'" placeholder="••••••••" class="pl-10" required />
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="show_password" class="rounded" @change="showPassword = !showPassword" />
                        <Label for="show_password" class="text-sm font-normal">Show password</Label>
                    </div>
                    <Button type="submit" class="w-full" size="lg" :disabled="form.processing">
                        <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                        Create Account
                    </Button>
                    <p class="text-xs text-center text-muted-foreground">
                        By signing up, you agree to our 
                        <a href="#" class="text-primary hover:underline">Terms of Service</a> and 
                        <a href="#" class="text-primary hover:underline">Privacy Policy</a>
                    </p>
                </form>
                <p class="mt-8 text-center text-sm text-muted-foreground">
                    Already have an account?
                    <Link href="/login" class="font-medium text-primary hover:underline">Sign in</Link>
                </p>
            </div>
        </div>
    </div>
</template>
