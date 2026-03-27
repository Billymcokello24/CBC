<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Lock, Loader2, ShieldCheck, ArrowRight } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post('/user/confirm-password', {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <AuthLayout
        title="Verification Required"
        description="For your security, please confirm your password to access this high-security area."
    >
        <Head title="Confirm Password" />

        <div class="mb-8 p-4 rounded-2xl bg-indigo-50 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-900/50 flex items-center gap-4">
            <div class="h-10 w-10 shrink-0 rounded-xl bg-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-600/20">
                <ShieldCheck class="h-5 w-5 text-white" />
            </div>
            <p class="text-xs font-bold text-indigo-900 dark:text-indigo-200 leading-relaxed uppercase tracking-wider">
                Protected Segment
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
            <div class="space-y-3">
                <Label for="password" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 dark:text-zinc-500 ml-1">Identity Secret</Label>
                <div class="group relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-transform duration-300 group-focus-within:scale-110">
                        <Lock class="h-5 w-5 text-slate-400 group-focus-within:text-indigo-600" />
                    </div>
                    <Input
                        id="password"
                        v-model="form.password"
                        type="password"
                        class="h-14 bg-white dark:bg-zinc-950 border-slate-200 dark:border-zinc-800 rounded-2xl pl-12 pr-4 transition-all focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 text-lg font-mono tracking-widest"
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                        autofocus
                    />
                </div>
                <InputError :message="form.errors.password" class="ml-1" />
            </div>

            <Button
                type="submit"
                class="h-14 w-full bg-slate-900 dark:bg-zinc-100 text-white dark:text-zinc-900 rounded-2xl shadow-xl shadow-slate-900/20 hover:shadow-2xl hover:shadow-slate-900/30 transition-all font-black uppercase tracking-[0.2em] text-xs group"
                :disabled="form.processing"
            >
                <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                <template v-else>
                    Authorize Access
                    <ArrowRight class="ml-2 h-4 w-4 transition-transform group-hover:translate-x-1" />
                </template>
            </Button>
        </form>
    </AuthLayout>
</template>
