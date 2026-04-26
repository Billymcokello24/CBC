<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { User, Mail, ShieldCheck, AlertCircle } from 'lucide-vue-next';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/DeleteUser.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/profile';
import type { BreadcrumbItem } from '@/types';

type Props = {
    mustVerifyEmail: boolean;
    status?: string;
};

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Settings', href: '/settings/profile' },
    { title: 'Profile Info', href: edit() },
];

const page = usePage();
const user = computed(() => page.props.auth.user);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Profile Settings" />

        <SettingsLayout>
            <div class="space-y-8">
                <Card
                    class="overflow-hidden rounded-xl border-slate-200 shadow-xl shadow-slate-200/50 dark:border-zinc-800 dark:shadow-none"
                >
                    <CardHeader
                        class="border-b border-slate-100 bg-slate-50/50 p-8 dark:border-zinc-800 dark:bg-zinc-900/50"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="rounded-2xl bg-violet-100 p-3 dark:bg-violet-900/30"
                            >
                                <User
                                    class="h-6 w-6 text-violet-600 dark:text-violet-400"
                                />
                            </div>
                            <div>
                                <CardTitle
                                    class="text-xl font-bold text-slate-900 dark:text-zinc-100"
                                    >Personal Information</CardTitle
                                >
                                <CardDescription class="text-sm font-medium"
                                    >Update your account's public identity and
                                    contact details.</CardDescription
                                >
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="p-8">
                        <Form
                            v-bind="ProfileController.update.form()"
                            class="space-y-8"
                            v-slot="{ errors, processing, recentlySuccessful }"
                        >
                            <div class="grid gap-6 md:grid-cols-2">
                                <div class="grid gap-2">
                                    <Label
                                        for="name"
                                        class="flex items-center gap-2 text-xs font-medium tracking-tight text-slate-500 uppercase"
                                    >
                                        <User class="h-3 w-3" /> Full Name
                                    </Label>
                                    <Input
                                        id="name"
                                        class="h-12 border-slate-200 font-semibold transition-all focus:border-violet-500 focus:ring-violet-500/20 dark:border-zinc-800 dark:bg-zinc-950"
                                        name="name"
                                        :default-value="user.name"
                                        required
                                        autocomplete="name"
                                        placeholder="Enter your full name"
                                    />
                                    <InputError :message="errors.name" />
                                </div>

                                <div class="grid gap-2">
                                    <Label
                                        for="email"
                                        class="flex items-center gap-2 text-xs font-medium tracking-tight text-slate-500 uppercase"
                                    >
                                        <Mail class="h-3 w-3" /> Email Address
                                    </Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        class="h-12 border-slate-200 font-semibold transition-all focus:border-violet-500 focus:ring-violet-500/20 dark:border-zinc-800 dark:bg-zinc-950"
                                        name="email"
                                        :default-value="user.email"
                                        required
                                        autocomplete="username"
                                        placeholder="email@example.com"
                                    />
                                    <InputError :message="errors.email" />
                                </div>
                            </div>

                            <div
                                v-if="
                                    mustVerifyEmail && !user.email_verified_at
                                "
                                class="flex items-start gap-3 rounded-2xl border border-amber-100 bg-amber-50 p-4 dark:border-amber-900/50 dark:bg-amber-950/20"
                            >
                                <AlertCircle
                                    class="mt-0.5 h-5 w-5 text-amber-600 dark:text-amber-400"
                                />
                                <div class="space-y-1">
                                    <p
                                        class="text-sm font-bold text-amber-900 dark:text-amber-200"
                                    >
                                        Email Unverified
                                    </p>
                                    <p
                                        class="text-xs leading-relaxed font-medium text-amber-700 dark:text-amber-400/80"
                                    >
                                        Your email address is unverified and
                                        some features may be limited.
                                        <button
                                            type="button"
                                            class="ml-1 font-bold underline hover:text-amber-900"
                                        >
                                            Resend link
                                        </button>
                                    </p>
                                </div>
                            </div>

                            <div
                                class="flex items-center justify-between border-t border-slate-50 pt-4 dark:border-zinc-900"
                            >
                                <div class="flex items-center gap-2">
                                    <Transition
                                        enter-active-class="animate-in fade-in slide-in-from-left-2"
                                        leave-active-class="animate-out fade-out slide-out-to-left-2"
                                    >
                                        <p
                                            v-show="recentlySuccessful"
                                            class="flex items-center gap-1.5 text-xs font-bold tracking-tight text-emerald-600 uppercase"
                                        >
                                            <ShieldCheck class="h-3.5 w-3.5" />
                                            Successfully Saved
                                        </p>
                                    </Transition>
                                </div>
                                <Button
                                    type="submit"
                                    :disabled="processing"
                                    class="h-12 rounded-2xl bg-slate-900 px-8 text-xs font-medium tracking-tight text-white uppercase shadow-xl shadow-slate-900/10 transition-all hover:bg-black dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-white"
                                >
                                    Update Profile
                                </Button>
                            </div>
                        </Form>
                    </CardContent>
                </Card>

                <DeleteUser />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
