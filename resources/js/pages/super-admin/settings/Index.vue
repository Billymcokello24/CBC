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
    AlertTriangle,
    CheckCircle2
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Separator } from '@/components/ui/separator';
import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert";
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
    if (confirm(`Attempting to toggle system state to ${props.settings.maintenance_mode ? 'ONLINE' : 'MAINTENANCE'}. Continue?`)) {
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
        <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-foreground">Settings</h1>
                    <p class="text-sm font-bold text-muted-foreground/60 uppercase tracking-widest">Configure global platform parameters and status.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Configuration -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5">
                        <div class="flex items-center gap-3 mb-8">
                             <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary border border-primary/10">
                                  <Layout class="h-5 w-5" />
                             </div>
                             <h3 class="text-lg font-black text-foreground tracking-tight">General Configuration</h3>
                        </div>

                        <form @submit.prevent="submit" class="space-y-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 ml-1">App Name</Label>
                                    <Input 
                                        v-model="form.app_name"
                                        placeholder="e.g. CBC Portal"
                                        class="h-11 rounded-xl border-border bg-muted/20 font-bold focus:ring-primary/20"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 ml-1">Environment</Label>
                                    <div class="h-11 rounded-xl border-border bg-muted/10 flex items-center px-4 font-black text-muted-foreground/40 uppercase tracking-widest text-[10px] border-dashed border">
                                         {{ settings.app_env }}
                                    </div>
                                </div>
                            </div>

                            <Separator class="bg-border/50" />

                            <div class="space-y-6">
                                <div class="flex items-center justify-between group">
                                    <div class="space-y-1">
                                        <Label class="text-sm font-black text-foreground tracking-tight leading-tight">Public Registration</Label>
                                        <p class="text-[11px] font-bold text-muted-foreground/60 uppercase tracking-tight">Allow new schools to register accounts directly.</p>
                                    </div>
                                    <Switch 
                                        :checked="form.registration_enabled"
                                        @update:checked="(val: boolean) => form.registration_enabled = val"
                                        class="data-[state=checked]:bg-primary"
                                    />
                                </div>

                                <div class="flex items-center justify-between group">
                                    <div class="space-y-1 opacity-40 select-none">
                                        <Label class="text-sm font-black text-foreground tracking-tight leading-tight">Debug Mode</Label>
                                        <p class="text-[11px] font-bold text-muted-foreground/60 uppercase tracking-tight">Show detailed error logs (Disabled in Production).</p>
                                    </div>
                                    <Switch 
                                        disabled
                                        class="opacity-20"
                                    />
                                </div>
                            </div>

                            <Button type="submit" class="w-full h-12 rounded-xl bg-primary hover:bg-primary/90 text-white font-black shadow-lg shadow-primary/20 transition-all" :disabled="form.processing">
                                 <Save class="mr-2 h-4 w-4" />
                                 <span class="text-sm font-black uppercase tracking-widest">{{ form.processing ? 'Saving...' : 'Save Settings' }}</span>
                            </Button>
                        </form>
                    </div>
                </div>

                <!-- Critical Operations -->
                <div class="space-y-6">
                    <!-- Maintenance Center -->
                    <div class="rounded-2xl border border-border bg-card p-6 shadow-sm space-y-6 dark:border-white/5">
                        <div class="flex items-center gap-3">
                             <div :class="['flex h-10 w-10 items-center justify-center rounded-xl border transition-colors', settings.maintenance_mode ? 'bg-rose-500/10 text-rose-600 border-rose-500/10' : 'bg-emerald-500/10 text-emerald-600 border-emerald-500/10']">
                                  <Power class="h-5 w-5" />
                             </div>
                             <h3 class="text-lg font-black text-foreground tracking-tight">System Status</h3>
                        </div>

                        <div class="space-y-4">
                             <div class="flex items-center justify-between bg-muted/30 p-4 rounded-xl border border-border/50">
                                  <span class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/40">Current Mode</span>
                                  <Badge :class="[
                                       'font-black text-[9px] uppercase tracking-widest px-2.5 py-1 rounded-lg border-0 shadow-sm',
                                       settings.maintenance_mode ? 'bg-rose-600 text-white' : 'bg-emerald-600 text-white'
                                  ]">
                                       {{ settings.maintenance_mode ? 'Maintenance' : 'Live' }}
                                  </Badge>
                             </div>

                             <Alert v-if="settings.maintenance_mode" class="bg-rose-500/5 border-rose-500/20 rounded-xl p-4">
                                  <AlertTriangle class="h-4 w-4 text-rose-600" />
                                  <AlertDescription class="ml-2 text-[10px] font-bold text-rose-600/80 leading-relaxed uppercase tracking-tight">
                                       System is offline. Only admins can access the platform.
                                  </AlertDescription>
                             </Alert>
                        </div>

                        <Button 
                            @click="toggleMaintenance"
                            variant="outline" 
                            :class="[
                                'w-full h-11 rounded-xl font-black text-[10px] uppercase tracking-widest transition-all border-border',
                                settings.maintenance_mode ? 'text-emerald-600 hover:bg-emerald-500/10 hover:border-emerald-500/20' : 'text-rose-600 hover:bg-rose-500/10 hover:border-rose-500/20'
                            ]"
                        >
                             {{ settings.maintenance_mode ? 'Go Live' : 'Enable Maintenance' }}
                        </Button>
                    </div>

                    <!-- Cloud Health -->
                    <div class="rounded-2xl bg-primary p-6 shadow-lg shadow-primary/20 relative overflow-hidden group">
                         <div class="absolute -right-6 -bottom-6 h-32 w-32 rounded-full bg-white/10 blur-2xl transition-transform group-hover:scale-110"></div>
                         <div class="relative space-y-4">
                              <div class="flex items-center justify-between">
                                   <Globe class="h-8 w-8 text-white/40" />
                                   <div class="flex h-6 w-6 items-center justify-center rounded-full bg-white/20 text-white animate-pulse">
                                        <CheckCircle2 class="h-4 w-4" />
                                   </div>
                              </div>
                              <div class="space-y-1">
                                   <h3 class="text-white font-black text-lg tracking-tight leading-none uppercase">Network Status</h3>
                                   <p class="text-white/60 text-[9px] font-bold uppercase tracking-widest leading-relaxed">System is performing optimally. <br/>Avg. Latency: 42ms</p>
                              </div>
                              <Button variant="ghost" class="w-full h-9 rounded-lg bg-white/10 text-white font-black uppercase text-[9px] tracking-widest hover:bg-white/20 border border-white/5">
                                   Advanced Details <ExternalLink class="ml-2 h-3 w-3 opacity-40" />
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
