<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import {
    Settings,
    Save,
    Power,
    ShieldCheck,
    Globe,
    Layout,
    Bell,
    Lock,
    ExternalLink,
    CheckCircle2,
    AlertTriangle,
    Home,
    ChevronRight,
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Separator } from '@/components/ui/separator';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import type { BreadcrumbItem } from '@/types';

interface Settings {
    app_name: string;
    app_url: string;
    app_env: string;
    maintenance_mode: boolean;
    registration_enabled: boolean;
}

const props = defineProps<{
    settings: Settings;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Global Dashboard', href: '/super-admin/dashboard' },
    { title: 'System Configuration', href: '/super-admin/settings' },
];

const form = useForm({
    app_name: props.settings.app_name,
    registration_enabled: props.settings.registration_enabled,
});

const toggleMaintenance = () => {
    if (
        confirm(
            `Attempting to toggle system state to ${props.settings.maintenance_mode ? 'ONLINE' : 'MAINTENANCE'}. Continue?`,
        )
    ) {
        useForm({}).post(route('super-admin.settings.toggle-maintenance'));
    }
};

const submit = () => {
    form.put(route('super-admin.settings.update'));
};
</script>

<template>
    <Head title="System Configuration" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-[1600px] animate-in space-y-6 p-4 pb-10 duration-700 fade-in slide-in-from-bottom-4 sm:space-y-8 sm:p-6 sm:pb-20 md:p-8">
            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between px-1">
                <div class="flex flex-col gap-1">
                    <div class="mb-1 flex items-center gap-2 text-xs text-muted-foreground sm:text-xs">
                        <Home class="h-3 w-3 sm:h-3.5 sm:w-3.5" />
                        <ChevronRight class="h-2.5 w-2.5 sm:h-3 sm:w-3" />
                        <span class="font-medium tracking-tight text-foreground uppercase">Global Dashboard</span>
                        <ChevronRight class="h-2.5 w-2.5 sm:h-3 sm:w-3" />
                        <span class="font-medium tracking-tight text-foreground uppercase">System Configuration</span>
                    </div>
                    <h1 class="text-2xl leading-tight font-bold tracking-tight text-foreground sm:text-3xl">
                        Settings
                    </h1>
                    <p class="text-sm text-muted-foreground sm:text-sm">
                        Configure global platform parameters and status.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                <!-- Main Configuration -->
                <div class="space-y-6 lg:col-span-2">
                    <div
                        class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5"
                    >
                        <div class="mb-8 flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl border border-primary/10 bg-primary/10 text-primary"
                            >
                                <Layout class="h-5 w-5" />
                            </div>
                            <h3
                                class="text-lg font-bold tracking-tight text-foreground"
                            >
                                General Configuration
                            </h3>
                        </div>

                        <form @submit.prevent="submit" class="space-y-8">
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label
                                        class="ml-1 text-xs font-medium tracking-tight text-muted-foreground/60 uppercase"
                                        >App Name</Label
                                    >
                                    <Input
                                        v-model="form.app_name"
                                        placeholder="e.g. CBC Portal"
                                        class="h-11 rounded-xl border-border bg-muted/20 font-bold focus:ring-primary/20"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label
                                        class="ml-1 text-xs font-medium tracking-tight text-muted-foreground/60 uppercase"
                                        >Environment</Label
                                    >
                                    <div
                                        class="flex h-11 items-center rounded-xl border border-dashed border-border bg-muted/10 px-4 text-xs font-bold tracking-tight text-muted-foreground/40 uppercase"
                                    >
                                        {{ settings.app_env }}
                                    </div>
                                </div>
                            </div>

                            <Separator class="bg-border/50" />

                            <div class="space-y-6">
                                <div
                                    class="group flex items-center justify-between"
                                >
                                    <div class="space-y-1">
                                        <Label
                                            class="text-sm leading-tight font-bold tracking-tight text-foreground"
                                            >Public Registration</Label
                                        >
                                        <p
                                            class="text-sm font-bold tracking-tight text-muted-foreground/60 uppercase"
                                        >
                                            Allow new schools to register
                                            accounts directly.
                                        </p>
                                    </div>
                                    <Switch
                                        :checked="form.registration_enabled"
                                        @update:checked="
                                            (val: boolean) =>
                                                (form.registration_enabled =
                                                    val)
                                        "
                                        class="data-[state=checked]:bg-primary"
                                    />
                                </div>

                                <div
                                    class="group flex items-center justify-between"
                                >
                                    <div
                                        class="space-y-1 opacity-40 select-none"
                                    >
                                        <Label
                                            class="text-sm leading-tight font-bold tracking-tight text-foreground"
                                            >Debug Mode</Label
                                        >
                                        <p
                                            class="text-sm font-bold tracking-tight text-muted-foreground/60 uppercase"
                                        >
                                            Show detailed error logs (Disabled
                                            in Production).
                                        </p>
                                    </div>
                                    <Switch disabled class="opacity-20" />
                                </div>
                            </div>

                            <Button
                                type="submit"
                                class="h-12 w-full rounded-xl bg-primary font-bold text-white shadow-lg shadow-primary/20 transition-all hover:bg-primary/90"
                                :disabled="form.processing"
                            >
                                <Save class="mr-2 h-4 w-4" />
                                <span
                                    class="text-sm font-medium tracking-tight uppercase"
                                    >{{
                                        form.processing
                                            ? 'Saving...'
                                            : 'Save Settings'
                                    }}</span
                                >
                            </Button>
                        </form>
                    </div>
                </div>

                <!-- Critical Operations -->
                <div class="space-y-6">
                    <!-- Maintenance Center -->
                    <div
                        class="space-y-6 rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                :class="[
                                    'flex h-10 w-10 items-center justify-center rounded-xl border transition-colors',
                                    settings.maintenance_mode
                                        ? 'border-rose-500/10 bg-rose-500/10 text-rose-600'
                                        : 'border-emerald-500/10 bg-emerald-500/10 text-emerald-600',
                                ]"
                            >
                                <Power class="h-5 w-5" />
                            </div>
                            <h3
                                class="text-lg font-bold tracking-tight text-foreground"
                            >
                                System Status
                            </h3>
                        </div>

                        <div class="space-y-4">
                            <div
                                class="flex items-center justify-between rounded-xl border border-border/50 bg-muted/30 p-4"
                            >
                                <span
                                    class="text-xs font-medium tracking-tight text-muted-foreground/40 uppercase"
                                    >Current Mode</span
                                >
                                <Badge
                                    :class="[
                                        'rounded-lg border-0 px-2.5 py-1 text-xs font-bold tracking-tight uppercase shadow-sm',
                                        settings.maintenance_mode
                                            ? 'bg-rose-600 text-white'
                                            : 'bg-emerald-600 text-white',
                                    ]"
                                >
                                    {{
                                        settings.maintenance_mode
                                            ? 'Maintenance'
                                            : 'Live'
                                    }}
                                </Badge>
                            </div>

                            <Alert
                                v-if="settings.maintenance_mode"
                                class="rounded-xl border-rose-500/20 bg-rose-500/5 p-4"
                            >
                                <AlertTriangle class="h-4 w-4 text-rose-600" />
                                <AlertDescription
                                    class="ml-2 text-xs leading-relaxed font-bold tracking-tight text-rose-600/80 uppercase"
                                >
                                    System is offline. Only admins can access
                                    the platform.
                                </AlertDescription>
                            </Alert>
                        </div>

                        <Button
                            @click="toggleMaintenance"
                            variant="outline"
                            :class="[
                                'h-11 w-full rounded-xl border-border text-xs font-bold tracking-tight uppercase transition-all',
                                settings.maintenance_mode
                                    ? 'text-emerald-600 hover:border-emerald-500/20 hover:bg-emerald-500/10'
                                    : 'text-rose-600 hover:border-rose-500/20 hover:bg-rose-500/10',
                            ]"
                        >
                            {{
                                settings.maintenance_mode
                                    ? 'Go Live'
                                    : 'Enable Maintenance'
                            }}
                        </Button>
                    </div>

                    <!-- Cloud Health -->
                    <div
                        class="group relative overflow-hidden rounded-2xl bg-primary p-6 shadow-lg shadow-primary/20"
                    >
                        <div
                            class="absolute -right-6 -bottom-6 h-32 w-32 rounded-full bg-white/10 blur-2xl transition-transform group-hover:scale-110"
                        ></div>
                        <div class="relative space-y-4">
                            <div class="flex items-center justify-between">
                                <Globe class="h-8 w-8 text-white/40" />
                                <div
                                    class="flex h-6 w-6 animate-pulse items-center justify-center rounded-full bg-white/20 text-white"
                                >
                                    <CheckCircle2 class="h-4 w-4" />
                                </div>
                            </div>
                            <div class="space-y-1">
                                <h3
                                    class="text-lg leading-none font-bold tracking-tight text-white uppercase"
                                >
                                    Network Status
                                </h3>
                                <p
                                    class="text-xs leading-relaxed font-bold tracking-tight text-white/60 uppercase"
                                >
                                    System is performing optimally. <br />Avg.
                                    Latency: 42ms
                                </p>
                            </div>
                            <Button
                                variant="ghost"
                                class="h-9 w-full rounded-lg border border-white/5 bg-white/10 text-xs font-bold tracking-tight text-white uppercase hover:bg-white/20"
                            >
                                Advanced Details
                                <ExternalLink class="ml-2 h-3 w-3 opacity-40" />
                            </Button>
                        </div>
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
