<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Activity, Stethoscope, AlertCircle, Calendar, Plus, ArrowRight, ClipboardList } from 'lucide-vue-next';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    stats: {
        total_visits_today: number;
        referred_cases: number;
        common_condition: string;
        medical_alerts: number;
    };
    recentVisits: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Health & Wellness', href: '/health' },
];

const formatDate = (date: string) => {
    return new Date(date).toLocaleString();
};
</script>

<template>
    <Head title="Health Dashboard" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 font-pulsar">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-rose-500/10">
                        <Activity class="h-6 w-6 text-rose-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Clinic & Wellness</h1>
                        <p class="text-muted-foreground">Manage student health records, clinic visits and medical alerts</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" as-child><Link href="/health/records"><ClipboardList class="mr-2 h-4 w-4" />Health Records</Link></Button>
                    <Button as-child class="bg-rose-600 hover:bg-rose-700"><Plus class="mr-2 h-4 w-4" />Log Clinic Visit</Button>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border bg-card p-6 shadow-sm border-l-4 border-l-rose-500">
                    <div class="flex items-center justify-between">
                        <div class="rounded-lg bg-rose-500/10 p-2"><Stethoscope class="h-5 w-5 text-rose-600" /></div>
                        <span class="text-xs font-bold text-rose-600 uppercase">Today</span>
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-muted-foreground">Clinic Visits</p>
                        <p class="text-2xl font-bold text-rose-600">{{ stats.total_visits_today }}</p>
                    </div>
                </div>
                <div class="rounded-xl border bg-card p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="rounded-lg bg-orange-500/10 p-2"><AlertCircle class="h-5 w-5 text-orange-600" /></div>
                        <span class="text-xs font-bold text-orange-600 uppercase">Referrals</span>
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-muted-foreground">Referred to Hospital</p>
                        <p class="text-2xl font-bold">{{ stats.referred_cases }}</p>
                    </div>
                </div>
                <div class="rounded-xl border bg-card p-6 shadow-sm border-l-4 border-l-blue-500">
                    <div class="flex items-center justify-between">
                        <div class="rounded-lg bg-blue-500/10 p-2"><Activity class="h-5 w-5 text-blue-600" /></div>
                        <span class="text-xs font-bold text-blue-600 uppercase">Trends</span>
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-muted-foreground">Common Condition</p>
                        <p class="text-xl font-bold text-blue-600 truncate">{{ stats.common_condition }}</p>
                    </div>
                </div>
                <div class="rounded-xl border bg-card p-6 shadow-sm border-l-4 border-l-amber-500">
                    <div class="flex items-center justify-between">
                        <div class="rounded-lg bg-amber-500/10 p-2"><ClipboardList class="h-5 w-5 text-amber-600" /></div>
                        <span class="text-xs font-bold text-amber-600 uppercase">Alerts</span>
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-muted-foreground">Medical Alerts</p>
                        <p class="text-2xl font-bold text-amber-600">{{ stats.medical_alerts }}</p>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2 rounded-xl border bg-card shadow-sm">
                    <div class="p-6 border-b flex items-center justify-between">
                        <h2 class="text-lg font-bold">Recent Clinic Visits</h2>
                        <Button variant="ghost" size="sm" as-child><Link href="/health/visits">View Archive</Link></Button>
                    </div>
                    <div class="p-0">
                        <div v-for="visit in recentVisits" :key="visit.id" class="flex items-center justify-between p-4 border-b last:border-0 hover:bg-slate-50/50 transition-colors">
                            <div class="flex items-center gap-4">
                                <div class="h-10 w-10 rounded-lg bg-rose-50 flex items-center justify-center">
                                    <Stethoscope class="h-5 w-5 text-rose-500" />
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900">{{ visit.student?.first_name }} {{ visit.student?.last_name }}</div>
                                    <div class="text-xs text-muted-foreground italic line-clamp-1">{{ visit.chief_complaint }} — {{ visit.diagnosis }}</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-medium text-slate-500">{{ formatDate(visit.visit_datetime) }}</div>
                                <Badge v-if="visit.referred_to_hospital" class="bg-rose-100 text-rose-700 hover:bg-rose-200 border-rose-200 uppercase text-[8px] mt-1 font-bold">REFERRED</Badge>
                            </div>
                        </div>
                        <div v-if="recentVisits.length === 0" class="p-12 text-center text-muted-foreground italic">
                            No visits logged today.
                        </div>
                    </div>
                </div>
                
                <div class="space-y-6">
                    <div class="rounded-xl border bg-gradient-to-br from-rose-600 to-rose-700 p-8 shadow-xl text-white">
                        <h2 class="text-2xl font-bold mb-2">Emergency Protocols</h2>
                        <p class="text-rose-100/80 mb-6 text-sm">Quick access to student emergency contacts and medical history.</p>
                        <Button as-child variant="secondary" class="bg-white text-rose-600 hover:bg-rose-50 w-full justify-between font-bold">
                            <Link href="/health/records">Search Records <ArrowRight class="h-4 w-4" /></Link>
                        </Button>
                    </div>

                    <div class="rounded-xl border bg-card p-6 shadow-sm">
                        <h3 class="font-bold mb-4">Upcoming Screenings</h3>
                        <div class="space-y-3">
                             <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-50 transition-colors cursor-pointer">
                                <div class="bg-blue-100 p-2 rounded-lg text-blue-600"><Activity class="h-4 w-4" /></div>
                                <div class="text-sm">
                                    <div class="font-bold">Dental Checkup</div>
                                    <div class="text-xs text-muted-foreground">Next Week • Grade 4-6</div>
                                </div>
                             </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
