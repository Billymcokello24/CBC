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
    X
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
    SelectValue 
} from "@/components/ui/select";
import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert";
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
        <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-foreground">Broadcasts</h1>
                    <p class="text-sm font-bold text-muted-foreground/60 uppercase tracking-widest">Send system-wide announcements to users.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                <!-- Composer -->
                <div class="xl:col-span-2 space-y-6">
                    <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 ml-1">Recipients</Label>
                                    <Select v-model="form.recipients">
                                        <SelectTrigger class="h-11 rounded-xl border-border bg-muted/20 font-bold focus:ring-primary/20 transition-all">
                                            <SelectValue placeholder="Select Recipient Group" />
                                        </SelectTrigger>
                                        <SelectContent class="rounded-xl border-border shadow-xl">
                                            <SelectItem value="all_admins">School Administrators Only</SelectItem>
                                            <SelectItem value="all_users">All Active Users</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div class="space-y-2">
                                    <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 ml-1">Subject</Label>
                                    <Input 
                                        v-model="form.subject"
                                        placeholder="Enter announcement subject..." 
                                        class="h-11 rounded-xl border-border bg-muted/20 font-bold focus:ring-primary/20"
                                        required
                                    />
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 ml-1">Greeting</Label>
                                <Input 
                                    v-model="form.greeting"
                                    placeholder="e.g. Hello {name}," 
                                    class="h-11 rounded-xl border-border bg-muted/20 font-bold focus:ring-primary/20"
                                    required
                                />
                                <p class="text-[9px] font-bold text-muted-foreground/40 uppercase tracking-tight">Use {name} as a dynamic name placeholder.</p>
                            </div>

                            <div class="space-y-2">
                                <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 ml-1">Message Body</Label>
                                <Textarea 
                                    v-model="form.message"
                                    placeholder="Describe your announcement..." 
                                    class="min-h-[250px] rounded-xl border-border bg-muted/20 p-4 font-bold focus:ring-primary/20 transition-all"
                                    required
                                />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 ml-1">Button Text (Optional)</Label>
                                    <Input 
                                        v-model="form.action_text"
                                        placeholder="e.g. View Details" 
                                        class="h-11 rounded-xl border-border bg-muted/20 font-bold focus:ring-primary/20"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 ml-1">Button URL (Optional)</Label>
                                    <div class="relative group">
                                         <LinkIcon class="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/40 group-focus-within:text-primary transition-colors" />
                                         <Input 
                                             v-model="form.action_url"
                                             placeholder="https://..." 
                                             class="pl-10 h-11 rounded-xl border-border bg-muted/20 font-bold focus:ring-primary/20"
                                         />
                                    </div>
                                </div>
                            </div>

                            <Button type="submit" class="w-full h-12 rounded-xl bg-primary hover:bg-primary/90 text-white font-black shadow-lg shadow-primary/20 transition-all" :disabled="form.processing">
                                <div class="relative flex items-center justify-center gap-2">
                                     <Zap v-if="!form.processing" class="h-4 w-4" />
                                     <span class="text-sm font-black uppercase tracking-widest">{{ form.processing ? 'Sending...' : 'Send Broadcast' }}</span>
                                     <Send v-if="!form.processing" class="h-4 w-4 opacity-50" />
                                </div>
                            </Button>
                        </form>
                    </div>
                </div>

                <!-- Info Pane -->
                <div class="space-y-6">
                    <div class="rounded-2xl border border-border bg-card p-6 shadow-sm space-y-6 dark:border-white/5">
                        <div class="flex items-center gap-3">
                             <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary border border-primary/10">
                                  <Info class="h-5 w-5" />
                             </div>
                             <h3 class="text-lg font-black text-foreground tracking-tight">Information</h3>
                        </div>

                        <div class="space-y-4">
                            <div class="flex gap-4">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-muted/50 text-muted-foreground/40 font-black text-[10px] border border-border">01</div>
                                <p class="text-[11px] font-bold text-muted-foreground leading-relaxed">Broadcasts are sent in the background via <span class="text-primary">queues</span> for maximum performance.</p>
                            </div>
                            <div class="flex gap-4">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-muted/50 text-muted-foreground/40 font-black text-[10px] border border-border">02</div>
                                <p class="text-[11px] font-bold text-muted-foreground leading-relaxed">All sent messages are recorded in the <span class="text-primary font-black uppercase tracking-tighter">audit trail</span> for accountability.</p>
                            </div>
                            <div class="flex gap-4">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-muted/50 text-muted-foreground/40 font-black text-[10px] border border-border">03</div>
                                <p class="text-[11px] font-bold text-muted-foreground leading-relaxed">Personalization tags like <b>{name}</b> are automatically replaced with actual user data.</p>
                            </div>
                        </div>

                        <Alert class="bg-rose-500/5 border-rose-500/20 rounded-xl p-4">
                            <ShieldCheck class="h-5 w-5 text-rose-600 dark:text-rose-400" />
                            <AlertTitle class="ml-3 text-[11px] font-black text-rose-900 dark:text-rose-400 uppercase tracking-widest">Important</AlertTitle>
                            <AlertDescription class="ml-3 text-[10px] font-bold text-rose-600/80 dark:text-rose-400/80 mt-1">
                                Broadcasting to "All Active Users" will send a large number of emails. Please verify your message carefully.
                            </AlertDescription>
                        </Alert>
                    </div>

                    <!-- Stats Card -->
                    <div class="rounded-2xl bg-card border border-border p-6 shadow-sm space-y-6 relative overflow-hidden group dark:border-white/5">
                         <div class="relative flex items-center justify-between">
                              <h3 class="text-foreground font-black text-base tracking-tight uppercase">Recent Impact</h3>
                              <MessageSquare class="h-6 w-6 text-primary opacity-20" />
                         </div>
                         <div class="relative grid grid-cols-2 gap-4">
                              <div class="bg-muted/30 rounded-xl p-4 border border-border/50">
                                   <div class="text-[8px] font-black text-muted-foreground/40 uppercase tracking-widest">Open Rate</div>
                                   <div class="text-xl font-black text-foreground">92.4%</div>
                              </div>
                              <div class="bg-muted/30 rounded-xl p-4 border border-border/50">
                                   <div class="text-[8px] font-black text-muted-foreground/40 uppercase tracking-widest">Engaged</div>
                                   <div class="text-xl font-black text-foreground">14.8k</div>
                              </div>
                         </div>
                         <button class="w-full h-10 rounded-lg bg-muted/50 border border-border text-[9px] font-black text-muted-foreground/60 uppercase tracking-widest hover:bg-primary hover:text-white hover:border-primary transition-all flex items-center justify-center gap-2">
                               View Analytics <ExternalLink class="h-3 w-3 opacity-40 " />
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
