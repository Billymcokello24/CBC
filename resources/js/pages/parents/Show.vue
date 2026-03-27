<script setup lang="ts">
import { 
    User, Mail, Phone, Shield, 
    ArrowLeft, Edit, GraduationCap,
    Clock, CheckCircle2, AlertTriangle,
    Home, Heart, Calendar, BadgeCheck
} from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { format } from 'date-fns';

const props = defineProps<{
    parent: any;
}>();

const breadcrumbs = [
    { title: 'User Directory', href: '/staffs/directory' },
    { title: 'Parents', href: '/parents' },
    { title: 'Guardian Profile', href: '#' },
];

const formatDate = (date: string) => {
    if (!date) return 'N/A';
    return format(new Date(date), 'MMMM d, yyyy');
};
</script>

<template>
    <AppLayout title="Guardian Profile" :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-col p-6 lg:p-10 bg-slate-50/30">
            <!-- Top Navigation & Actions -->
            <div class="flex flex-wrap items-center justify-between gap-6 mb-10">
                <div class="space-y-1">
                    <Link href="/parents" class="inline-flex items-center text-xs font-bold text-muted-foreground hover:text-blue-600 transition-colors gap-1.5 group uppercase tracking-widest">
                        <ArrowLeft class="h-3.5 w-3.5 transition-transform group-hover:-translate-x-1" />
                        Back to Registry
                    </Link>
                    <h1 class="text-4xl font-black text-slate-900 tracking-tight flex items-center gap-3 italic uppercase">
                        Guardian Profile
                    </h1>
                </div>
                <div class="flex items-center gap-3">
                    <Button variant="outline" class="h-12 border-slate-200 bg-white hover:bg-slate-50 font-bold px-6 rounded-2xl transition-all shadow-sm" as-child>
                        <Link :href="`/parents/${parent.id}/edit`">
                            <Edit class="mr-2 h-4 w-4 text-blue-600" />
                            Edit Profile
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="grid gap-8 lg:grid-cols-12">
                <!-- Left: Identity Card -->
                <div class="lg:col-span-4 space-y-8">
                    <div class="overflow-hidden rounded-[2.5rem] border border-slate-200 bg-white shadow-xl shadow-slate-200/40 p-8 space-y-8 relative">
                        <div class="absolute top-0 right-0 p-8 opacity-[0.03] pointer-events-none">
                            <Heart class="h-40 w-40 text-rose-600" />
                        </div>

                        <div class="flex flex-col items-center text-center space-y-4">
                            <div class="h-24 w-24 rounded-[2rem] bg-rose-50 border-4 border-white shadow-lg flex items-center justify-center text-rose-600 font-black text-3xl rotate-2">
                                {{ parent.first_name[0] }}{{ parent.last_name[0] }}
                            </div>
                            <div class="space-y-1">
                                <h2 class="text-2xl font-black text-slate-900 tracking-tight">{{ parent.first_name }} {{ parent.last_name }}</h2>
                                <Badge variant="secondary" class="bg-rose-50 text-rose-600 border-rose-100/50 font-black uppercase text-[10px] tracking-widest px-3 py-1 italic">
                                    {{ parent.relationship_type || 'Guardian' }}
                                </Badge>
                            </div>
                        </div>

                        <div class="space-y-6 pt-6 border-t border-slate-100">
                            <div class="flex items-center gap-4 group">
                                <div class="h-10 w-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-blue-50 group-hover:text-blue-600 transition-colors">
                                    <Mail class="h-5 w-5" />
                                </div>
                                <div class="overflow-hidden">
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-0.5 mb-0.5">Primary Email</p>
                                    <p class="text-sm font-bold text-slate-900 truncate">{{ parent.user?.email || parent.email }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4 group">
                                <div class="h-10 w-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-blue-50 group-hover:text-blue-600 transition-colors">
                                    <Phone class="h-5 w-5" />
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-0.5 mb-0.5">Phone Contact</p>
                                    <p class="text-sm font-bold text-slate-900">{{ parent.phone }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4 group">
                                <div class="h-10 w-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-blue-50 group-hover:text-blue-600 transition-colors">
                                    <BadgeCheck class="h-5 w-5" />
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-0.5 mb-0.5">ID Number</p>
                                    <p class="text-sm font-bold text-slate-900">{{ parent.id_number || 'Not Provided' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-5 rounded-3xl bg-slate-900 text-white space-y-4 shadow-xl shadow-slate-900/20">
                            <div class="flex items-center justify-between">
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-rose-400">Account Status</p>
                                <div class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-xl bg-white/10 flex items-center justify-center">
                                    <Clock class="h-5 w-5 text-white/60" />
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest">Registered Since</p>
                                    <p class="text-xs font-bold text-white">{{ formatDate(parent.created_at) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Content Area -->
                <div class="lg:col-span-8 space-y-8">
                    <!-- Dependents Listing -->
                    <div class="overflow-hidden rounded-[2.5rem] border border-slate-200 bg-white shadow-xl shadow-slate-200/40 duration-500">
                        <div class="border-b border-slate-100 bg-slate-50/30 px-10 py-7 flex items-center justify-between">
                            <h2 class="text-lg font-bold text-slate-900 flex items-center gap-3">
                                <div class="h-10 w-10 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-200">
                                    <GraduationCap class="h-5 w-5" />
                                </div>
                                Linked Dependents
                            </h2>
                            <Badge class="bg-blue-50 text-blue-600 border-blue-100 font-bold px-3 py-1">
                                {{ parent.students?.length || 0 }} Learners
                            </Badge>
                        </div>
                        <div class="p-10">
                            <div v-if="parent.students?.length > 0" class="grid gap-6 md:grid-cols-2">
                                <Link 
                                    v-for="student in parent.students" 
                                    :key="student.id"
                                    :href="`/students/${student.id}`"
                                    class="p-6 rounded-[2rem] border-2 border-slate-50 bg-slate-50/40 hover:border-blue-200 hover:bg-white transition-all group flex items-center gap-5 shadow-sm hover:shadow-xl hover:shadow-blue-100/50"
                                >
                                    <div class="h-14 w-14 rounded-2xl bg-white border-2 border-slate-100 flex items-center justify-center font-black text-lg text-slate-400 group-hover:bg-blue-600 group-hover:text-white group-hover:border-blue-600 transition-all shadow-sm">
                                        {{ student.first_name[0] }}{{ student.last_name[0] }}
                                    </div>
                                    <div class="space-y-1">
                                        <h4 class="font-black text-slate-900 group-hover:text-blue-600 transition-colors">{{ student.first_name }} {{ student.last_name }}</h4>
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ student.admission_number }}</p>
                                    </div>
                                    <div class="ml-auto opacity-0 group-hover:opacity-100 transition-opacity">
                                        <div class="h-8 w-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center">
                                            <BadgeCheck class="h-4 w-4" />
                                        </div>
                                    </div>
                                </Link>
                            </div>
                            <div v-else class="flex flex-col items-center justify-center py-20 text-center space-y-4">
                                <div class="h-20 w-20 rounded-[2rem] bg-slate-50 flex items-center justify-center text-slate-200 shadow-inner">
                                    <GraduationCap class="h-10 w-10" />
                                </div>
                                <div class="space-y-1">
                                    <h3 class="text-xl font-bold text-slate-900 italic">No Linked Dependents</h3>
                                    <p class="text-sm text-slate-400 font-medium max-w-xs">This guardian account currently has no learner connections registered.</p>
                                </div>
                                <Button class="rounded-xl h-11 bg-slate-900" as-child>
                                    <Link :href="`/parents/${parent.id}/edit`">Link Learner</Link>
                                </Button>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Details / History -->
                    <div class="grid gap-8 md:grid-cols-2">
                        <div class="p-8 rounded-[2.5rem] bg-slate-900 text-white relative overflow-hidden group">
                            <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-1000">
                                <Shield class="h-32 w-32" />
                            </div>
                            <h3 class="text-xs font-black uppercase tracking-[0.2em] text-blue-400 mb-6">Security Access</h3>
                            <div class="space-y-6 relative">
                                <div class="flex items-center gap-4">
                                    <div class="h-10 w-10 rounded-xl bg-white/10 flex items-center justify-center text-white/40">
                                        <Calendar class="h-5 w-5" />
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest pl-0.5 mb-0.5">Last Portal Activity</p>
                                        <p class="text-sm font-bold text-white">Never Logged In</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="h-10 w-10 rounded-xl bg-emerald-500/20 flex items-center justify-center text-emerald-400">
                                        <CheckCircle2 class="h-5 w-5" />
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest pl-0.5 mb-0.5">Account Verification</p>
                                        <p class="text-sm font-bold text-white">Active Profile</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-8 rounded-[2.5rem] border border-slate-200 bg-white shadow-xl shadow-slate-200/40 relative group overflow-hidden">
                             <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-1000">
                                <Home class="h-32 w-32 text-indigo-600" />
                            </div>
                            <h3 class="text-xs font-black uppercase tracking-[0.2em] text-slate-400 mb-6 italic">Institutional Info</h3>
                            <div class="space-y-6 relative">
                                <div class="flex items-center gap-4">
                                    <div class="h-10 w-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                                        <Home class="h-5 w-5" />
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-0.5 mb-0.5">School Code</p>
                                        <p class="text-sm font-black text-slate-900 italic">CBC-INST-{{ parent.school_id }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="h-10 w-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400">
                                        <Info class="h-5 w-5" />
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-0.5 mb-0.5">Reference ID</p>
                                        <p class="text-sm font-black text-slate-900 uppercase">GDN-{{ parent.id.toString().padStart(4, '0') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
