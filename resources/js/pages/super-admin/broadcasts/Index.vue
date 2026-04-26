<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import {
    Mail,
    Send,
    MessageSquare,
    Link as LinkIcon,
    Users,
    ShieldCheck,
    Info,
    Zap,
    ExternalLink,
    X,
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Global Dashboard', href: '/super-admin/dashboard' },
    { title: 'System Communications', href: '/super-admin/broadcasts' },
];

const form = useForm({
    subject: '',
    greeting: 'Hello {name},',
    message: '',
    action_text: '',
    action_url: '',
    recipients: 'all_admins',
});

const submit = () => {
    form.post(route('super-admin.broadcasts.send'), {
        onSuccess: () => {
            form.reset('subject', 'message', 'action_text', 'action_url');
        },
    });
};
</script>

<template>
    <Head title="System Communications" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="animate-in space-y-8 duration-700 fade-in slide-in-from-bottom-4"
        >
            <!-- Header -->
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <h1
                        class="text-2xl font-bold tracking-tight text-foreground"
                    >
                        Broadcasts
                    </h1>
                    <p
                        class="text-sm font-bold tracking-tight text-muted-foreground/60 uppercase"
                    >
                        Send system-wide announcements to users.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-8 xl:grid-cols-3">
                <!-- Composer -->
                <div class="space-y-6 xl:col-span-2">
                    <div
                        class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5"
                    >
                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label
                                        class="ml-1 text-xs font-medium tracking-tight text-muted-foreground/60 uppercase"
                                        >Recipients</Label
                                    >
                                    <Select v-model="form.recipients">
                                        <SelectTrigger
                                            class="h-11 rounded-xl border-border bg-muted/20 font-bold transition-all focus:ring-primary/20"
                                        >
                                            <SelectValue
                                                placeholder="Select Recipient Group"
                                            />
                                        </SelectTrigger>
                                        <SelectContent
                                            class="rounded-xl border-border shadow-xl"
                                        >
                                            <SelectItem value="all_admins"
                                                >School Administrators
                                                Only</SelectItem
                                            >
                                            <SelectItem value="all_users"
                                                >All Active Users</SelectItem
                                            >
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div class="space-y-2">
                                    <Label
                                        class="ml-1 text-xs font-medium tracking-tight text-muted-foreground/60 uppercase"
                                        >Subject</Label
                                    >
                                    <Input
                                        v-model="form.subject"
                                        placeholder="Enter announcement subject..."
                                        class="h-11 rounded-xl border-border bg-muted/20 font-bold focus:ring-primary/20"
                                        required
                                    />
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label
                                    class="ml-1 text-xs font-medium tracking-tight text-muted-foreground/60 uppercase"
                                    >Greeting</Label
                                >
                                <Input
                                    v-model="form.greeting"
                                    placeholder="e.g. Hello {name},"
                                    class="h-11 rounded-xl border-border bg-muted/20 font-bold focus:ring-primary/20"
                                    required
                                />
                                <p
                                    class="text-xs font-bold tracking-tight text-muted-foreground/40 uppercase"
                                >
                                    Use {name} as a dynamic name placeholder.
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label
                                    class="ml-1 text-xs font-medium tracking-tight text-muted-foreground/60 uppercase"
                                    >Message Body</Label
                                >
                                <Textarea
                                    v-model="form.message"
                                    placeholder="Describe your announcement..."
                                    class="min-h-[250px] rounded-xl border-border bg-muted/20 p-4 font-bold transition-all focus:ring-primary/20"
                                    required
                                />
                            </div>

                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label
                                        class="ml-1 text-xs font-medium tracking-tight text-muted-foreground/60 uppercase"
                                        >Button Text (Optional)</Label
                                    >
                                    <Input
                                        v-model="form.action_text"
                                        placeholder="e.g. View Details"
                                        class="h-11 rounded-xl border-border bg-muted/20 font-bold focus:ring-primary/20"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label
                                        class="ml-1 text-xs font-medium tracking-tight text-muted-foreground/60 uppercase"
                                        >Button URL (Optional)</Label
                                    >
                                    <div class="group relative">
                                        <LinkIcon
                                            class="absolute top-1/2 left-4 h-4 w-4 -translate-y-1/2 text-muted-foreground/40 transition-colors group-focus-within:text-primary"
                                        />
                                        <Input
                                            v-model="form.action_url"
                                            placeholder="https://..."
                                            class="h-11 rounded-xl border-border bg-muted/20 pl-10 font-bold focus:ring-primary/20"
                                        />
                                    </div>
                                </div>
                            </div>

                            <Button
                                type="submit"
                                class="h-12 w-full rounded-xl bg-primary font-bold text-white shadow-lg shadow-primary/20 transition-all hover:bg-primary/90"
                                :disabled="form.processing"
                            >
                                <div
                                    class="relative flex items-center justify-center gap-2"
                                >
                                    <Zap
                                        v-if="!form.processing"
                                        class="h-4 w-4"
                                    />
                                    <span
                                        class="text-sm font-medium tracking-tight uppercase"
                                        >{{
                                            form.processing
                                                ? 'Sending...'
                                                : 'Send Broadcast'
                                        }}</span
                                    >
                                    <Send
                                        v-if="!form.processing"
                                        class="h-4 w-4 opacity-50"
                                    />
                                </div>
                            </Button>
                        </form>
                    </div>
                </div>

                <!-- Info Pane -->
                <div class="space-y-6">
                    <div
                        class="space-y-6 rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl border border-primary/10 bg-primary/10 text-primary"
                            >
                                <Info class="h-5 w-5" />
                            </div>
                            <h3
                                class="text-lg font-bold tracking-tight text-foreground"
                            >
                                Information
                            </h3>
                        </div>

                        <div class="space-y-4">
                            <div class="flex gap-4">
                                <div
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-border bg-muted/50 text-xs font-bold text-muted-foreground/40"
                                >
                                    01
                                </div>
                                <p
                                    class="text-sm leading-relaxed font-bold text-muted-foreground"
                                >
                                    Broadcasts are sent in the background via
                                    <span class="text-primary">queues</span> for
                                    maximum performance.
                                </p>
                            </div>
                            <div class="flex gap-4">
                                <div
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-border bg-muted/50 text-xs font-bold text-muted-foreground/40"
                                >
                                    02
                                </div>
                                <p
                                    class="text-sm leading-relaxed font-bold text-muted-foreground"
                                >
                                    All sent messages are recorded in the
                                    <span
                                        class="font-bold tracking-tighter text-primary uppercase"
                                        >audit trail</span
                                    >
                                    for accountability.
                                </p>
                            </div>
                            <div class="flex gap-4">
                                <div
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-border bg-muted/50 text-xs font-bold text-muted-foreground/40"
                                >
                                    03
                                </div>
                                <p
                                    class="text-sm leading-relaxed font-bold text-muted-foreground"
                                >
                                    Personalization tags like <b>{name}</b> are
                                    automatically replaced with actual user
                                    data.
                                </p>
                            </div>
                        </div>

                        <Alert
                            class="rounded-xl border-rose-500/20 bg-rose-500/5 p-4"
                        >
                            <ShieldCheck
                                class="h-5 w-5 text-rose-600 dark:text-rose-400"
                            />
                            <AlertTitle
                                class="ml-3 text-sm font-bold tracking-tight text-rose-900 uppercase dark:text-rose-400"
                                >Important</AlertTitle
                            >
                            <AlertDescription
                                class="mt-1 ml-3 text-xs font-bold text-rose-600/80 dark:text-rose-400/80"
                            >
                                Broadcasting to "All Active Users" will send a
                                large number of emails. Please verify your
                                message carefully.
                            </AlertDescription>
                        </Alert>
                    </div>

                    <!-- Stats Card -->
                    <div
                        class="group relative space-y-6 overflow-hidden rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5"
                    >
                        <div class="relative flex items-center justify-between">
                            <h3
                                class="text-base font-bold tracking-tight text-foreground uppercase"
                            >
                                Recent Impact
                            </h3>
                            <MessageSquare
                                class="h-6 w-6 text-primary opacity-20"
                            />
                        </div>
                        <div class="relative grid grid-cols-2 gap-4">
                            <div
                                class="rounded-xl border border-border/50 bg-muted/30 p-4"
                            >
                                <div
                                    class="text-xs font-bold tracking-tight text-muted-foreground/40 uppercase"
                                >
                                    Open Rate
                                </div>
                                <div class="text-xl font-bold text-foreground">
                                    92.4%
                                </div>
                            </div>
                            <div
                                class="rounded-xl border border-border/50 bg-muted/30 p-4"
                            >
                                <div
                                    class="text-xs font-bold tracking-tight text-muted-foreground/40 uppercase"
                                >
                                    Engaged
                                </div>
                                <div class="text-xl font-bold text-foreground">
                                    14.8k
                                </div>
                            </div>
                        </div>
                        <button
                            class="flex h-10 w-full items-center justify-center gap-2 rounded-lg border border-border bg-muted/50 text-xs font-bold tracking-tight text-muted-foreground/60 uppercase transition-all hover:border-primary hover:bg-primary hover:text-white"
                        >
                            View Analytics
                            <ExternalLink class="h-3 w-3 opacity-40" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.tracking-widest {
    letter-spacing: 0.15em;
}
</style>
