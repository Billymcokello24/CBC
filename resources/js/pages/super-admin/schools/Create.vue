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
        <div class="space-y-8 animate-in fade-in duration-700">
            <div class="max-w-5xl mx-auto space-y-8">
                <!-- Navigation Header -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <Link :href="schoolsRoutes.index().url">
                            <Button variant="ghost" class="h-10 w-10 rounded-xl bg-card shadow-sm border border-border text-muted-foreground/40 hover:text-foreground transition-all">
                                <ChevronLeft class="h-5 w-5" />
                            </Button>
                        </Link>
                        <div>
                             <h1 class="text-2xl font-black text-foreground tracking-tight">Add New School</h1>
                             <p class="text-[10px] font-black text-muted-foreground/40 uppercase tracking-widest mt-0.5">Register a new institution on the platform.</p>
                        </div>
                    </div>
                </div>

                <!-- Progress Stepper (Visual) -->
                <div class="grid grid-cols-3 gap-3">
                    <div v-for="step in 3" :key="step" class="h-1 rounded-full transition-all duration-500" :class="currentStep >= step ? 'bg-primary shadow-sm' : 'bg-muted'"></div>
                </div>

                <form @submit.prevent="submit" class="space-y-8 pb-20">
                    <!-- Step 1: Identity -->
                    <div class="relative overflow-hidden rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5">
                        <div class="relative flex items-center gap-4 mb-10">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-primary border border-primary/10">
                                <Building class="h-6 w-6" />
                            </div>
                            <div>
                                <h2 class="text-lg font-black text-foreground tracking-tight">School Details</h2>
                                <p class="text-[11px] font-bold text-muted-foreground/60 uppercase tracking-tight">General information and categorization.</p>
                            </div>
                        </div>

                        <div class="relative grid gap-8 md:grid-cols-2">
                             <div class="space-y-2">
                                <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 ml-1">School Name</Label>
                                <div class="relative">
                                    <SchoolIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground/30" />
                                    <Input 
                                        v-model="form.name" 
                                        placeholder="Enter school name..." 
                                        class="pl-10 h-11 rounded-xl border-border bg-muted/20 focus:bg-background focus:ring-primary/20 font-bold transition-all"
                                    />
                                </div>
                                <span v-if="form.errors.name" class="text-[9px] font-black text-rose-500 uppercase">{{ form.errors.name }}</span>
                             </div>

                             <div class="space-y-2">
                                <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 ml-1">School Code</Label>
                                <div class="relative">
                                    <Hash class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground/30" />
                                    <Input 
                                        v-model="form.code" 
                                        placeholder="e.g. SCH-001" 
                                        class="pl-10 h-11 rounded-xl border-border bg-muted/20 focus:bg-background focus:ring-primary/20 font-bold transition-all uppercase"
                                    />
                                </div>
                                <span v-if="form.errors.code" class="text-[9px] font-black text-rose-500 uppercase">{{ form.errors.code }}</span>
                             </div>
                        </div>

                        <div class="relative grid gap-8 md:grid-cols-2 mt-8">
                             <div class="space-y-2">
                                <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 ml-1">School Type</Label>
                                <Select v-model="form.school_type_id">
                                    <SelectTrigger class="h-11 rounded-xl border-border bg-muted/20 focus:bg-background font-bold">
                                        <SelectValue placeholder="Select Type" />
                                    </SelectTrigger>
                                    <SelectContent class="rounded-xl p-1 shadow-2xl border-border">
                                        <SelectItem v-for="type in schoolTypes" :key="type.id" :value="type.id.toString()" class="rounded-lg font-bold text-sm">
                                            {{ type.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                             </div>

                             <div class="space-y-2">
                                <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 ml-1">Academic Level</Label>
                                <Select v-model="form.school_level_id">
                                    <SelectTrigger class="h-11 rounded-xl border-border bg-muted/20 focus:bg-background font-bold">
                                        <SelectValue placeholder="Select Level" />
                                    </SelectTrigger>
                                    <SelectContent class="rounded-xl p-1 shadow-2xl border-border">
                                        <SelectItem v-for="level in schoolLevels" :key="level.id" :value="level.id.toString()" class="rounded-lg font-bold text-sm">
                                            {{ level.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                             </div>
                        </div>
                    </div>

                    <!-- Step 2: Connectivity -->
                    <div class="relative overflow-hidden rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5">
                        <div class="relative flex items-center gap-4 mb-10">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-primary border border-primary/10">
                                <Globe class="h-6 w-6" />
                            </div>
                            <div>
                                <h2 class="text-lg font-black text-foreground tracking-tight">Contact Information</h2>
                                <p class="text-[11px] font-bold text-muted-foreground/60 uppercase tracking-tight">Location and communication channels.</p>
                            </div>
                        </div>

                        <div class="relative grid gap-8 md:grid-cols-3">
                             <div class="space-y-2">
                                <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 ml-1">Official Email</Label>
                                <div class="relative">
                                    <Mail class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground/30" />
                                    <Input 
                                        type="email"
                                        v-model="form.email" 
                                        placeholder="e.g. info@school.com" 
                                        class="pl-10 h-11 rounded-xl border-border bg-muted/20 focus:bg-background font-bold transition-all"
                                    />
                                </div>
                                <span v-if="form.errors.email" class="text-[9px] font-black text-rose-500 uppercase">{{ form.errors.email }}</span>
                             </div>

                             <div class="space-y-2">
                                <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 ml-1">Phone Number</Label>
                                <div class="relative">
                                    <Phone class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground/30" />
                                    <Input 
                                        v-model="form.phone" 
                                        placeholder="+254..." 
                                        class="pl-10 h-11 rounded-xl border-border bg-muted/20 focus:bg-background font-bold transition-all"
                                    />
                                </div>
                                <span v-if="form.errors.phone" class="text-[9px] font-black text-rose-500 uppercase">{{ form.errors.phone }}</span>
                             </div>

                             <div class="space-y-2">
                                <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 ml-1">County / Region</Label>
                                <div class="relative">
                                    <MapPin class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground/30" />
                                    <Input 
                                        v-model="form.county" 
                                        placeholder="Enter county..." 
                                        class="pl-10 h-11 rounded-xl border-border bg-muted/20 focus:bg-background font-bold transition-all"
                                    />
                                </div>
                                <span v-if="form.errors.county" class="text-[9px] font-black text-rose-500 uppercase">{{ form.errors.county }}</span>
                             </div>
                        </div>
                    </div>

                    <!-- Step 3: Admin User -->
                    <div class="relative overflow-hidden rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5">
                        <div class="relative flex items-center gap-4 mb-10">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 text-primary border border-primary/10">
                                <ShieldCheck class="h-6 w-6" />
                            </div>
                            <div>
                                <h2 class="text-lg font-black text-foreground tracking-tight">Primary Administrator</h2>
                                <p class="text-[11px] font-bold text-muted-foreground/60 uppercase tracking-tight">Create the root admin account for this school.</p>
                            </div>
                        </div>

                        <div class="relative grid gap-8 md:grid-cols-2">
                             <div class="space-y-2">
                                <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 ml-1">Admin Full Name</Label>
                                <Input 
                                    v-model="form.admin_name" 
                                    placeholder="Enter admin name..." 
                                    class="h-11 rounded-xl border-border bg-muted/20 focus:bg-background font-bold transition-all"
                                />
                                <span v-if="form.errors.admin_name" class="text-[9px] font-black text-rose-500 uppercase">{{ form.errors.admin_name }}</span>
                             </div>

                             <div class="space-y-2">
                                <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 ml-1">Admin Email</Label>
                                <Input 
                                    type="email"
                                    v-model="form.admin_email" 
                                    placeholder="admin@school.com" 
                                    class="h-11 rounded-xl border-border bg-muted/20 focus:bg-background font-bold transition-all"
                                />
                                <span v-if="form.errors.admin_email" class="text-[9px] font-black text-rose-500 uppercase">{{ form.errors.admin_email }}</span>
                             </div>
                        </div>

                        <div class="relative grid gap-8 md:grid-cols-3 mt-8">
                             <div class="space-y-2">
                                <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 ml-1">Password</Label>
                                <Input 
                                    type="password"
                                    v-model="form.admin_password" 
                                    placeholder="••••••••" 
                                    class="h-11 rounded-xl border-border bg-muted/20 focus:bg-background font-bold transition-all"
                                />
                                <span v-if="form.errors.admin_password" class="text-[9px] font-black text-rose-500 uppercase">{{ form.errors.admin_password }}</span>
                             </div>

                             <div class="space-y-2">
                                <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 ml-1">Confirm Password</Label>
                                <Input 
                                    type="password"
                                    v-model="form.admin_password_confirmation" 
                                    placeholder="••••••••" 
                                    class="h-11 rounded-xl border-border bg-muted/20 focus:bg-background font-bold transition-all"
                                />
                             </div>

                             <div class="space-y-2">
                                <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 ml-1">Admin Phone</Label>
                                <Input 
                                    v-model="form.admin_phone" 
                                    placeholder="+254..." 
                                    class="h-11 rounded-xl border-border bg-muted/20 focus:bg-background font-bold transition-all"
                                />
                                <span v-if="form.errors.admin_phone" class="text-[9px] font-black text-rose-500 uppercase">{{ form.errors.admin_phone }}</span>
                             </div>
                        </div>
                    </div>

                    <!-- Submission Actions -->
                    <div class="flex items-center justify-end gap-4 pt-6">
                        <Link :href="schoolsRoutes.index().url">
                            <Button type="button" variant="ghost" class="h-11 px-6 rounded-xl font-black text-[10px] uppercase tracking-widest text-muted-foreground/40 hover:text-foreground">
                                Cancel
                            </Button>
                        </Link>
                        <Button 
                            type="submit" 
                            :disabled="form.processing"
                            class="h-11 px-10 rounded-xl bg-primary hover:bg-primary/90 text-white font-black shadow-lg shadow-primary/20 transition-all"
                        >
                             <div class="relative flex items-center gap-2">
                                  <Zap v-if="!form.processing" class="h-4 w-4" />
                                  <span v-else class="h-4 w-4 rounded-full border-2 border-white/30 border-t-white animate-spin"></span>
                                  <span class="text-sm uppercase tracking-widest">{{ form.processing ? 'Processing...' : 'Create School' }}</span>
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
