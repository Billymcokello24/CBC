<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { Lock, ShieldCheck, Key } from 'lucide-vue-next';
import PasswordController from '@/actions/App/Http/Controllers/Settings/PasswordController';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/user-password';
import type { BreadcrumbItem } from '@/types';

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Settings', href: '/settings/profile' },
    { title: 'Security', href: edit() },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Security Settings" />

        <SettingsLayout>
            <Card class="border-slate-200 dark:border-zinc-800 shadow-xl shadow-slate-200/50 dark:shadow-none overflow-hidden rounded-[2rem]">
                <CardHeader class="bg-slate-50/50 dark:bg-zinc-900/50 border-b border-slate-100 dark:border-zinc-800 p-8">
                    <div class="flex items-center gap-4">
                        <div class="p-3 rounded-2xl bg-rose-100 dark:bg-rose-900/30">
                            <Lock class="h-6 w-6 text-rose-600 dark:text-rose-400" />
                        </div>
                        <div>
                            <CardTitle class="text-xl font-black text-slate-900 dark:text-zinc-100">Update Password</CardTitle>
                            <CardDescription class="text-sm font-medium">Keep your account safe by using a strong, unique password.</CardDescription>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="p-8">
                    <Form
                        v-bind="PasswordController.update.form()"
                        :options="{
                            preserveScroll: true,
                        }"
                        reset-on-success
                        :reset-on-error="[
                            'password',
                            'password_confirmation',
                            'current_password',
                        ]"
                        class="space-y-8"
                        v-slot="{ errors, processing, recentlySuccessful }"
                    >
                        <div class="grid gap-6">
                            <div class="grid gap-2">
                                <Label for="current_password" class="text-xs font-black uppercase tracking-widest text-slate-500 flex items-center gap-2">
                                    <Key class="h-3 w-3" /> Current Password
                                </Label>
                                <PasswordInput
                                    id="current_password"
                                    name="current_password"
                                    class="h-12 border-slate-200 dark:border-zinc-800 focus:ring-rose-500/20 focus:border-rose-500 dark:bg-zinc-950 transition-all"
                                    autocomplete="current-password"
                                    placeholder="Enter current password"
                                />
                                <InputError :message="errors.current_password" />
                            </div>

                            <Separator class="bg-slate-50 dark:bg-zinc-900" />

                            <div class="grid md:grid-cols-2 gap-6">
                                <div class="grid gap-2">
                                    <Label for="password" class="text-xs font-black uppercase tracking-widest text-slate-500 flex items-center gap-2">
                                        <Lock class="h-3 w-3" /> New Password
                                    </Label>
                                    <PasswordInput
                                        id="password"
                                        name="password"
                                        class="h-12 border-slate-200 dark:border-zinc-800 focus:ring-rose-500/20 focus:border-rose-500 dark:bg-zinc-950 transition-all"
                                        autocomplete="new-password"
                                        placeholder="Min 8 characters"
                                    />
                                    <InputError :message="errors.password" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="password_confirmation" class="text-xs font-black uppercase tracking-widest text-slate-500 flex items-center gap-2">
                                        <ShieldCheck class="h-3 w-3" /> Confirm Password
                                    </Label>
                                    <PasswordInput
                                        id="password_confirmation"
                                        name="password_confirmation"
                                        class="h-12 border-slate-200 dark:border-zinc-800 focus:ring-rose-500/20 focus:border-rose-500 dark:bg-zinc-950 transition-all"
                                        autocomplete="new-password"
                                        placeholder="Repeat new password"
                                    />
                                    <InputError :message="errors.password_confirmation" />
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-4 border-t border-slate-50 dark:border-zinc-900">
                            <div class="flex items-center gap-2">
                                <Transition
                                    enter-active-class="animate-in fade-in slide-in-from-left-2"
                                    leave-active-class="animate-out fade-out slide-out-to-left-2"
                                >
                                    <p v-show="recentlySuccessful" class="text-xs font-black text-emerald-600 flex items-center gap-1.5 uppercase tracking-widest">
                                        <ShieldCheck class="h-3.5 w-3.5" /> Password Updated
                                    </p>
                                </Transition>
                            </div>
                            <Button
                                type="submit"
                                :disabled="processing"
                                class="bg-rose-600 dark:bg-rose-600 text-white hover:bg-rose-700 dark:hover:bg-rose-500 px-8 h-12 rounded-2xl font-black uppercase tracking-widest text-xs transition-all shadow-xl shadow-rose-600/20"
                            >
                                Update Security
                            </Button>
                        </div>
                    </Form>
                </CardContent>
            </Card>
        </SettingsLayout>
    </AppLayout>
</template>
