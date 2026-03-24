<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { 
    School as SchoolIcon, 
    ChevronLeft, 
    Save, 
    Info, 
    MapPin, 
    Phone, 
    Mail, 
    Hash,
    Building,
    CheckCircle2,
    Zap,
    Globe,
    ShieldCheck,
    Cpu,
    Database,
    Network
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import schoolsRoutes from '@/routes/super-admin/schools';
import type { BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { 
    Select, 
    SelectContent, 
    SelectItem, 
    SelectTrigger, 
    SelectValue 
} from '@/components/ui/select';

interface SchoolType {
    id: number;
    name: string;
}

interface SchoolLevel {
    id: number;
    name: string;
}

interface Props {
    schoolTypes: SchoolType[];
    schoolLevels: SchoolLevel[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Global Dashboard', href: '/super-admin/dashboard' },
    { title: 'Tenant Registry', href: '/super-admin/schools' },
    { title: 'Provision Node', href: '/super-admin/schools/create' },
];

const form = useForm({
    name: '',
    code: '',
    email: '',
    phone: '',
    county: '',
    school_type_id: '',
    school_level_id: '',
    status: 'active',
    // Admin account fields
    admin_name: '',
    admin_email: '',
    admin_password: '',
    admin_password_confirmation: '',
    admin_phone: '',
});

const submit = () => {
    form.post(schoolsRoutes.store().url, {
        preserveScroll: true,
    });
};

const currentStep = ref(1);
</script>

<template>
    <Head title="Provision New Tenant" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-full bg-slate-50/50 p-6 lg:p-10 animate-in fade-in duration-700">
            <div class="max-w-5xl mx-auto space-y-12">
                <!-- Navigation Header -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-6">
                        <Link :href="schoolsRoutes.index().url">
                            <Button variant="ghost" class="h-14 w-14 rounded-2xl bg-white shadow-sm border border-slate-200 text-slate-400 hover:text-slate-900 transition-all">
                                <ChevronLeft class="h-6 w-6" />
                            </Button>
                        </Link>
                        <div>
                             <div class="flex items-center gap-2 mb-1">
                                  <Badge class="bg-blue-600 font-black text-[9px] uppercase tracking-widest px-2 py-0.5">SaaS Core</Badge>
                                  <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Shard Provisioning v2.4</span>
                             </div>
                             <h1 class="text-3xl font-black text-slate-900 tracking-tight">Provision Institutional <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600">Shard</span></h1>
                        </div>
                    </div>

                    <div class="hidden lg:flex items-center gap-4">
                         <div class="flex flex-col items-end">
                              <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Global Status</span>
                              <span class="text-xs font-black text-emerald-500 flex items-center gap-1.5"><div class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></div> Infrastructure Nominal</span>
                         </div>
                    </div>
                </div>

                <!-- Progress Stepper (Visual) -->
                <div class="grid grid-cols-3 gap-4">
                    <div v-for="step in 3" :key="step" class="h-1.5 rounded-full transition-all duration-500" :class="currentStep >= step ? 'bg-indigo-600 shadow-[0_0_10px_rgba(79,70,229,0.3)]' : 'bg-slate-200'"></div>
                </div>

                <form @submit.prevent="submit" class="space-y-10 pb-20">
                    <!-- Step 1: Identity -->
                    <div class="relative overflow-hidden rounded-[2.5rem] border border-slate-200/60 bg-white p-10 shadow-xl shadow-slate-200/30">
                        <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-50/50 blur-3xl opacity-50"></div>
                        
                        <div class="relative flex items-center gap-6 mb-12">
                            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-600 text-white shadow-xl shadow-indigo-100">
                                <Building class="h-8 w-8" />
                            </div>
                            <div>
                                <h2 class="text-2xl font-black text-slate-900">Entity Topology</h2>
                                <p class="text-slate-500 font-medium">Define the core identity and academic level for the new shard.</p>
                            </div>
                        </div>

                        <div class="relative grid gap-10 md:grid-cols-2">
                             <div class="space-y-3">
                                <Label class="text-[11px] font-black uppercase tracking-widest text-slate-400">Institution Name</Label>
                                <div class="relative">
                                    <SchoolIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-300" />
                                    <Input 
                                        v-model="form.name" 
                                        placeholder="e.g., Summit International School" 
                                        class="pl-12 h-14 rounded-2xl border-slate-100 bg-slate-50/30 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 font-bold transition-all"
                                    />
                                </div>
                                <span v-if="form.errors.name" class="text-[10px] font-black text-rose-500 uppercase">{{ form.errors.name }}</span>
                             </div>

                             <div class="space-y-3">
                                <Label class="text-[11px] font-black uppercase tracking-widest text-slate-400">Unique Identifier (Code)</Label>
                                <div class="relative">
                                    <Hash class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-300" />
                                    <Input 
                                        v-model="form.code" 
                                        placeholder="e.g., SIS-001" 
                                        class="pl-12 h-14 rounded-2xl border-slate-100 bg-slate-50/30 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 font-bold transition-all uppercase"
                                    />
                                </div>
                                <span v-if="form.errors.code" class="text-[10px] font-black text-rose-500 uppercase">{{ form.errors.code }}</span>
                             </div>
                        </div>

                        <div class="relative grid gap-10 md:grid-cols-2 mt-10">
                             <div class="space-y-3">
                                <Label class="text-[11px] font-black uppercase tracking-widest text-slate-400">Classification Model</Label>
                                <Select v-model="form.school_type_id">
                                    <SelectTrigger class="h-14 rounded-2xl border-slate-100 bg-slate-50/30 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 font-bold">
                                        <SelectValue placeholder="System Classification" />
                                    </SelectTrigger>
                                    <SelectContent class="rounded-2xl p-2 shadow-2xl border-slate-50">
                                        <SelectItem v-for="type in schoolTypes" :key="type.id" :value="type.id.toString()" class="rounded-xl p-3 font-bold">
                                            {{ type.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                             </div>

                             <div class="space-y-3">
                                <Label class="text-[11px] font-black uppercase tracking-widest text-slate-400">Academic Vertical</Label>
                                <Select v-model="form.school_level_id">
                                    <SelectTrigger class="h-14 rounded-2xl border-slate-100 bg-slate-50/30 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 font-bold">
                                        <SelectValue placeholder="Academic Vertical" />
                                    </SelectTrigger>
                                    <SelectContent class="rounded-2xl p-2 shadow-2xl border-slate-50">
                                        <SelectItem v-for="level in schoolLevels" :key="level.id" :value="level.id.toString()" class="rounded-xl p-3 font-bold">
                                            {{ level.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                             </div>
                        </div>
                    </div>

                    <!-- Step 2: Connectivity -->
                    <div class="relative overflow-hidden rounded-[2.5rem] border border-slate-200/60 bg-white p-10 shadow-xl shadow-slate-200/30">
                        <div class="absolute -left-20 -top-20 h-64 w-64 rounded-full bg-blue-50/50 blur-3xl opacity-50"></div>

                        <div class="relative flex items-center gap-6 mb-12">
                            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-xl shadow-blue-100">
                                <Globe class="h-8 w-8" />
                            </div>
                            <div>
                                <h2 class="text-2xl font-black text-slate-900">Connectivity Hub</h2>
                                <p class="text-slate-500 font-medium">Institutional contact points and regional node mapping.</p>
                            </div>
                        </div>

                        <div class="relative grid gap-10 md:grid-cols-3">
                             <div class="space-y-3">
                                <Label class="text-[11px] font-black uppercase tracking-widest text-slate-400">School Email</Label>
                                <div class="relative">
                                    <Mail class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-300" />
                                    <Input 
                                        type="email"
                                        v-model="form.email" 
                                        placeholder="office@summit.edu" 
                                        class="pl-12 h-14 rounded-2xl border-slate-100 bg-slate-50/30 focus:bg-white font-bold transition-all"
                                    />
                                </div>
                                <span v-if="form.errors.email" class="text-[10px] font-black text-rose-500 uppercase">{{ form.errors.email }}</span>
                             </div>

                             <div class="space-y-3">
                                <Label class="text-[11px] font-black uppercase tracking-widest text-slate-400">School Phone</Label>
                                <div class="relative">
                                    <Phone class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-300" />
                                    <Input 
                                        v-model="form.phone" 
                                        placeholder="+254 7XX XXX XXX" 
                                        class="pl-12 h-14 rounded-2xl border-slate-100 bg-slate-50/30 focus:bg-white font-bold transition-all"
                                    />
                                </div>
                                <span v-if="form.errors.phone" class="text-[10px] font-black text-rose-500 uppercase">{{ form.errors.phone }}</span>
                             </div>

                             <div class="space-y-3">
                                <Label class="text-[11px] font-black uppercase tracking-widest text-slate-400">Geo-Mapping (County)</Label>
                                <div class="relative">
                                    <MapPin class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-300" />
                                    <Input 
                                        v-model="form.county" 
                                        placeholder="Regional Node" 
                                        class="pl-12 h-14 rounded-2xl border-slate-100 bg-slate-50/30 focus:bg-white font-bold transition-all"
                                    />
                                </div>
                                <span v-if="form.errors.county" class="text-[10px] font-black text-rose-500 uppercase">{{ form.errors.county }}</span>
                             </div>
                        </div>
                    </div>

                    <!-- Step 3: Shard Administrator (New Section) -->
                    <div class="relative overflow-hidden rounded-[2.5rem] border border-slate-200/60 bg-white p-10 shadow-xl shadow-slate-200/30">
                        <div class="absolute -right-20 -bottom-20 h-64 w-64 rounded-full bg-emerald-50/50 blur-3xl opacity-50"></div>

                        <div class="relative flex items-center gap-6 mb-12">
                            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-600 text-white shadow-xl shadow-emerald-100">
                                <ShieldCheck class="h-8 w-8" />
                            </div>
                            <div>
                                <h2 class="text-2xl font-black text-slate-900">Shard Administrator</h2>
                                <p class="text-slate-500 font-medium">Provision the primary authority account for this institutional node.</p>
                            </div>
                        </div>

                        <div class="relative grid gap-10 md:grid-cols-2">
                             <div class="space-y-3">
                                <Label class="text-[11px] font-black uppercase tracking-widest text-slate-400">Admin Full Name</Label>
                                <div class="relative">
                                    <User class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-300" />
                                    <Input 
                                        v-model="form.admin_name" 
                                        placeholder="e.g., Dr. Jane Smith" 
                                        class="pl-12 h-14 rounded-2xl border-slate-100 bg-slate-50/30 focus:bg-white font-bold transition-all"
                                    />
                                </div>
                                <span v-if="form.errors.admin_name" class="text-[10px] font-black text-rose-500 uppercase">{{ form.errors.admin_name }}</span>
                             </div>

                             <div class="space-y-3">
                                <Label class="text-[11px] font-black uppercase tracking-widest text-slate-400">Login Email (Identity)</Label>
                                <div class="relative">
                                    <Mail class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-300" />
                                    <Input 
                                        type="email"
                                        v-model="form.admin_email" 
                                        placeholder="admin@summit.edu" 
                                        class="pl-12 h-14 rounded-2xl border-slate-100 bg-slate-50/30 focus:bg-white font-bold transition-all"
                                    />
                                </div>
                                <span v-if="form.errors.admin_email" class="text-[10px] font-black text-rose-500 uppercase">{{ form.errors.admin_email }}</span>
                             </div>
                        </div>

                        <div class="relative grid gap-10 md:grid-cols-3 mt-10">
                             <div class="space-y-3">
                                <Label class="text-[11px] font-black uppercase tracking-widest text-slate-400">Access Password</Label>
                                <div class="relative">
                                    <Lock class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-300" />
                                    <Input 
                                        type="password"
                                        v-model="form.admin_password" 
                                        placeholder="••••••••" 
                                        class="pl-12 h-14 rounded-2xl border-slate-100 bg-slate-50/30 focus:bg-white font-bold transition-all"
                                    />
                                </div>
                                <span v-if="form.errors.admin_password" class="text-[10px] font-black text-rose-500 uppercase">{{ form.errors.admin_password }}</span>
                             </div>

                             <div class="space-y-3">
                                <Label class="text-[11px] font-black uppercase tracking-widest text-slate-400">Confirm Password</Label>
                                <div class="relative">
                                    <Lock class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-300" />
                                    <Input 
                                        type="password"
                                        v-model="form.admin_password_confirmation" 
                                        placeholder="••••••••" 
                                        class="pl-12 h-14 rounded-2xl border-slate-100 bg-slate-50/30 focus:bg-white font-bold transition-all"
                                    />
                                </div>
                             </div>

                             <div class="space-y-3">
                                <Label class="text-[11px] font-black uppercase tracking-widest text-slate-400">Shard Access Phone</Label>
                                <div class="relative">
                                    <Phone class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-300" />
                                    <Input 
                                        v-model="form.admin_phone" 
                                        placeholder="+254 7XX XXX XXX" 
                                        class="pl-12 h-14 rounded-2xl border-slate-100 bg-slate-50/30 focus:bg-white font-bold transition-all"
                                    />
                                </div>
                                <span v-if="form.errors.admin_phone" class="text-[10px] font-black text-rose-500 uppercase">{{ form.errors.admin_phone }}</span>
                             </div>
                        </div>
                    </div>

                    <!-- Infrastructure Pulse (Visual Only) -->
                    <div class="rounded-[2.5rem] bg-slate-900 p-10 shadow-2xl overflow-hidden relative group">
                         <div class="absolute inset-0 bg-gradient-to-r from-blue-600/20 to-indigo-600/20 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                         <div class="relative grid md:grid-cols-4 gap-8">
                              <div class="flex items-center gap-4">
                                   <div class="h-12 w-12 rounded-2xl bg-white/5 flex items-center justify-center text-blue-400 border border-white/10">
                                        <Cpu class="h-6 w-6" />
                                   </div>
                                   <div>
                                        <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Compute Node</p>
                                        <p class="text-sm font-black text-white">AWS c5.large</p>
                                   </div>
                              </div>
                              <div class="flex items-center gap-4">
                                   <div class="h-12 w-12 rounded-2xl bg-white/5 flex items-center justify-center text-indigo-400 border border-white/10">
                                        <Database class="h-6 w-6" />
                                   </div>
                                   <div>
                                        <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Storage Shard</p>
                                        <p class="text-sm font-black text-white">100GB NVMe</p>
                                   </div>
                              </div>
                              <div class="flex items-center gap-4">
                                   <div class="h-12 w-12 rounded-2xl bg-white/5 flex items-center justify-center text-emerald-400 border border-white/10">
                                        <Network class="h-6 w-6" />
                                   </div>
                                   <div>
                                        <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Network Edge</p>
                                        <p class="text-sm font-black text-white">Cloudflare 15ms</p>
                                   </div>
                              </div>
                              <div class="flex items-center justify-end">
                                   <div class="text-right">
                                        <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Isolation Layer</p>
                                        <p class="text-sm font-black text-emerald-400">Strict Tenancy enforced</p>
                                   </div>
                              </div>
                         </div>
                    </div>

                    <!-- Submission Actions -->
                    <div class="flex items-center justify-end gap-6 pt-10">
                        <Link :href="schoolsRoutes.index().url">
                            <Button type="button" variant="ghost" class="h-14 px-10 rounded-2xl font-black text-slate-400 hover:bg-slate-100 uppercase tracking-[0.2em] text-[10px]">
                                Abandon Provisioning
                            </Button>
                        </Link>
                        <Button 
                            type="submit" 
                            :disabled="form.processing"
                            class="relative group h-16 px-14 rounded-2xl bg-indigo-600 text-white font-black shadow-2xl shadow-indigo-200 transition-all hover:scale-[1.02] active:scale-[0.98] overflow-hidden"
                        >
                             <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-indigo-600 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                             <div class="relative flex items-center gap-3">
                                  <Zap v-if="!form.processing" class="h-5 w-5" />
                                  <span v-else class="h-5 w-5 rounded-full border-2 border-white/30 border-t-white animate-spin"></span>
                                  <span class="uppercase tracking-widest">Execute Node Provisioning</span>
                             </div>
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.tracking-widest {
    letter-spacing: 0.15em;
}
</style>
