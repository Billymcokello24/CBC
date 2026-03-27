<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Lock, Mail, Loader2, Eye, EyeOff } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';

const props = defineProps<{
    token: string;
    email: string;
}>();

const showPassword = ref(false);

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/reset-password', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthLayout
        title="Set new password"
        description="Your new password must be different from previously used passwords."
    >
        <Head title="Reset Password" />

        <form @submit.prevent="submit" class="space-y-5">
            <div class="space-y-2">
                <Label for="email" class="text-xs font-semibold uppercase tracking-wider text-slate-500">Email address</Label>
                <div class="relative opacity-60">
                    <Mail class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <Input
                        id="email"
                        type="email"
                        v-model="form.email"
                        class="h-11 border-slate-200 bg-slate-50 pl-10 dark:border-slate-800 dark:bg-slate-900/50"
                        readonly
                    />
                </div>
            </div>

            <div class="space-y-2">
                <Label for="password" class="text-xs font-semibold uppercase tracking-wider text-slate-500">New Password</Label>
                <div class="group relative">
                    <Lock class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-purple-500" />
                    <Input
                        id="password"
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        placeholder="••••••••"
                        class="h-11 border-slate-200 pl-10 pr-10 transition-all focus:border-purple-500 focus:ring-purple-500/20 dark:border-slate-800"
                        required
                        autofocus
                    />
                    <button 
                        type="button"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300"
                        @click="showPassword = !showPassword"
                    >
                        <Eye v-if="!showPassword" class="h-4 w-4" />
                        <EyeOff v-else class="h-4 w-4" />
                    </button>
                </div>
                <InputError :message="form.errors.password" />
            </div>

            <div class="space-y-2">
                <Label for="password_confirmation" class="text-xs font-semibold uppercase tracking-wider text-slate-500">Confirm Password</Label>
                <div class="group relative">
                    <Lock class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-purple-500" />
                    <Input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        :type="showPassword ? 'text' : 'password'"
                        placeholder="••••••••"
                        class="h-11 border-slate-200 pl-10 transition-all focus:border-purple-500 focus:ring-purple-500/20 dark:border-slate-800"
                        required
                    />
                </div>
                <InputError :message="form.errors.password_confirmation" />
            </div>

            <Button
                type="submit"
                class="h-11 w-full bg-purple-600 text-white shadow-lg shadow-purple-600/20 hover:bg-purple-700 hover:shadow-purple-600/30 dark:bg-purple-600 dark:hover:bg-purple-500"
                :disabled="form.processing"
            >
                <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                Reset Password
            </Button>
        </form>
    </AuthLayout>
</template>
