<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Mail, Loader2, LogOut } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import AuthLayout from '@/layouts/AuthLayout.vue';

defineProps<{
    status?: string;
}>();

const form = useForm({});

const submit = () => {
    form.post('/email/verification-notification');
};
</script>

<template>
    <AuthLayout
        title="Verify email"
        description="Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you?"
    >
        <Head title="Email Verification" />

        <div
            v-if="status === 'verification-link-sent'"
            class="mb-6 rounded-xl bg-emerald-50 p-4 text-center text-sm font-medium text-emerald-600 dark:bg-emerald-900/30"
        >
            A new verification link has been sent to your email address.
        </div>

        <div class="flex flex-col gap-6">
            <div
                class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-purple-50 text-purple-600 dark:bg-purple-900/20 dark:text-purple-400"
            >
                <Mail class="h-8 w-8" />
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <Button
                    :disabled="form.processing"
                    class="h-11 w-full bg-purple-600 text-white shadow-lg shadow-purple-600/20 hover:bg-purple-700 hover:shadow-purple-600/30 dark:bg-purple-600 dark:hover:bg-purple-500"
                >
                    <Loader2
                        v-if="form.processing"
                        class="mr-2 h-4 w-4 animate-spin"
                    />
                    Resend Verification Email
                </Button>

                <Link
                    href="/logout"
                    method="post"
                    as="button"
                    class="flex w-full items-center justify-center gap-2 text-sm font-semibold text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200"
                >
                    <LogOut class="h-4 w-4" />
                    Log out
                </Link>
            </form>
        </div>
    </AuthLayout>
</template>
