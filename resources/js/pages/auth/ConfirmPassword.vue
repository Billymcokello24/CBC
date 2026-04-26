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

        <div
            class="mb-8 flex items-center gap-4 rounded-2xl border border-indigo-100 bg-indigo-50 p-4 dark:border-indigo-900/50 dark:bg-indigo-950/20"
        >
            <div
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-600 shadow-lg shadow-indigo-600/20"
            >
                <ShieldCheck class="h-5 w-5 text-white" />
            </div>
            <p
                class="text-xs leading-relaxed font-bold tracking-wider text-indigo-900 uppercase dark:text-indigo-200"
            >
                Protected Segment
            </p>
        </div>

        <form
            @submit.prevent="submit"
            class="animate-in space-y-6 duration-500 fade-in slide-in-from-bottom-4"
        >
            <div class="space-y-3">
                <Label
                    for="password"
                    class="ml-1 text-xs font-medium tracking-tight text-muted-foreground text-slate-500 uppercase dark:text-zinc-500"
                    >Identity Secret</Label
                >
                <div class="group relative">
                    <div
                        class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 transition-transform duration-300 group-focus-within:scale-110"
                    >
                        <Lock
                            class="h-5 w-5 text-slate-400 group-focus-within:text-indigo-600"
                        />
                    </div>
                    <Input
                        id="password"
                        v-model="form.password"
                        type="password"
                        class="h-14 rounded-2xl border-slate-200 bg-white pr-4 pl-12 font-mono text-lg tracking-tight transition-all focus:border-indigo-600 focus:ring-4 focus:ring-indigo-500/10 dark:border-zinc-800 dark:bg-zinc-950"
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
                class="group h-14 w-full rounded-2xl bg-slate-900 text-xs font-medium tracking-tight text-muted-foreground text-white uppercase shadow-xl shadow-slate-900/20 transition-all hover:shadow-lg hover:shadow-slate-900/30 dark:bg-zinc-100 dark:text-zinc-900"
                :disabled="form.processing"
            >
                <Loader2
                    v-if="form.processing"
                    class="mr-2 h-4 w-4 animate-spin"
                />
                <template v-else>
                    Authorize Access
                    <ArrowRight
                        class="ml-2 h-4 w-4 transition-transform group-hover:translate-x-1"
                    />
                </template>
            </Button>
        </form>
    </AuthLayout>
</template>
