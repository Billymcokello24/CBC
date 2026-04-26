<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Mail, Loader2, ArrowLeft } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post('/forgot-password');
};
</script>

<template>
    <AuthLayout
        title="Forgot password?"
        description="No worries, we'll send you reset instructions."
    >
        <Head title="Forgot Password" />

        <div
            v-if="status"
            class="mb-6 rounded-xl bg-emerald-50 p-4 text-center text-sm font-medium text-emerald-600 dark:bg-emerald-900/30"
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
                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-purple-500"
                    />
                    <Input
                        id="email"
                        v-model="form.email"
                        type="email"
                        placeholder="name@school.com"
                        class="h-11 border-slate-200 pl-10 transition-all focus:border-purple-500 focus:ring-purple-500/20 dark:border-slate-800"
                        required
                        autofocus
                    />
                </div>
                <InputError :message="form.errors.email" />
            </div>

            <Button
                type="submit"
                class="h-11 w-full bg-purple-600 text-white shadow-lg shadow-purple-600/20 hover:bg-purple-700 hover:shadow-purple-600/30 dark:bg-purple-600 dark:hover:bg-purple-500"
                :disabled="form.processing"
            >
                <Loader2
                    v-if="form.processing"
                    class="mr-2 h-4 w-4 animate-spin"
                />
                Reset Password
            </Button>

            <Link
                href="/login"
                class="flex items-center justify-center gap-2 text-sm font-semibold text-slate-500 hover:text-purple-600 dark:text-slate-400"
            >
                <ArrowLeft class="h-4 w-4" />
                Back to sign in
            </Link>
        </form>
    </AuthLayout>
</template>
