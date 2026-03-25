<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { 
    School as SchoolIcon, 
    ChevronLeft, 
    Save, 
    MapPin, 
    Phone, 
    Mail, 
    Building,
    CheckCircle2,
    AlertCircle
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
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

interface SchoolType {
    id: number;
    name: string;
}

interface SchoolLevel {
    id: number;
    name: string;
}

interface School {
    id: number;
    name: string;
    code: string;
    email: string;
    phone: string;
    county: string;
    school_type_id: number;
    school_level_id: number;
    status: 'active' | 'inactive';
}

interface Props {
    school: School;
    schoolTypes: SchoolType[];
    schoolLevels: SchoolLevel[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'SaaS Dashboard', href: '/super-admin/dashboard' },
    { title: 'Schools', href: '/super-admin/schools' },
    { title: `Edit ${props.school.name}`, href: `/super-admin/schools/${props.school.id}/edit` },
];

const form = useForm({
    name: props.school.name,
    email: props.school.email,
    phone: props.school.phone,
    county: props.school.county,
    status: props.school.status,
    _method: 'PUT'
});

const submit = () => {
    form.post(schoolsRoutes.update(props.school).url, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Edit ${school.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 animate-in fade-in duration-700">
            <div class="max-w-5xl mx-auto space-y-8 w-full">
                <div class="flex items-center gap-4">
                    <Link :href="schoolsRoutes.index().url">
                        <Button variant="ghost" size="icon" class="h-10 w-10 rounded-xl bg-card shadow-sm border border-border text-muted-foreground/40 hover:text-foreground transition-all active:scale-90">
                            <ChevronLeft class="h-5 w-5" />
                        </Button>
                    </Link>
                    <div>
                        <h1 class="text-2xl font-black tracking-tight text-foreground">Edit School</h1>
                        <p class="text-[10px] font-black text-muted-foreground/40 uppercase tracking-widest mt-0.5">Updating configuration for <span class="text-primary">{{ school.name }}</span></p>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-8">
                    <!-- Basic Details -->
                    <div class="rounded-2xl border border-border bg-card shadow-sm overflow-hidden dark:border-white/5">
                        <div class="p-6 border-b border-border bg-muted/20">
                            <div class="flex items-center gap-4">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary border border-primary/10">
                                    <Building class="h-5 w-5" />
                                </div>
                                <div>
                                    <h2 class="text-lg font-black text-foreground tracking-tight">School Details</h2>
                                    <p class="text-[11px] font-bold text-muted-foreground/60 uppercase tracking-tight">Core identification and system code.</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-6 grid gap-8">
                            <div class="grid gap-6 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="name" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 ml-1">School Name</Label>
                                    <div class="relative">
                                        <SchoolIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground/30" />
                                        <Input 
                                            id="name" 
                                            v-model="form.name" 
                                            class="pl-10 h-11 rounded-xl border-border bg-muted/20 focus:bg-background focus:ring-primary/20 font-bold transition-all"
                                            :class="{ 'border-rose-500 ring-rose-50': form.errors.name }"
                                        />
                                    </div>
                                    <p v-if="form.errors.name" class="text-[9px] font-black text-rose-500 uppercase">{{ form.errors.name }}</p>
                                </div>

                                <div class="space-y-2 opacity-60">
                                    <Label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 ml-1">School Code</Label>
                                    <div class="relative">
                                        <Hash class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground/30" />
                                        <Input 
                                            :value="school.code" 
                                            disabled
                                            class="pl-10 h-11 rounded-xl border-border bg-muted font-black cursor-not-allowed"
                                        />
                                    </div>
                                    <p class="text-[9px] font-black text-muted-foreground/40 uppercase flex items-center gap-1 mt-1">
                                        <AlertCircle class="h-3 w-3" /> System ID cannot be changed
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact and Location -->
                    <div class="rounded-2xl border border-border bg-card shadow-sm overflow-hidden dark:border-white/5">
                        <div class="p-6 border-b border-border bg-muted/20">
                            <div class="flex items-center gap-4">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary border border-primary/10">
                                    <MapPin class="h-5 w-5" />
                                </div>
                                <div>
                                    <h2 class="text-lg font-black text-foreground tracking-tight">Contact & Status</h2>
                                    <p class="text-[11px] font-bold text-muted-foreground/60 uppercase tracking-tight">Location, communication and account toggle.</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-6 grid gap-8">
                            <div class="grid gap-6 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="email" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 ml-1">Official Email</Label>
                                    <div class="relative">
                                        <Mail class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground/30" />
                                        <Input 
                                            id="email" 
                                            type="email"
                                            v-model="form.email" 
                                            class="pl-10 h-11 rounded-xl border-border bg-muted/20 focus:bg-background focus:ring-primary/20 font-bold transition-all"
                                            :class="{ 'border-rose-500 ring-rose-50': form.errors.email }"
                                        />
                                    </div>
                                    <p v-if="form.errors.email" class="text-[9px] font-black text-rose-500 uppercase">{{ form.errors.email }}</p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="phone" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 ml-1">Phone Number</Label>
                                    <div class="relative">
                                        <Phone class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground/30" />
                                        <Input 
                                            id="phone" 
                                            v-model="form.phone" 
                                            class="pl-10 h-11 rounded-xl border-border bg-muted/20 focus:bg-background focus:ring-primary/20 font-bold transition-all"
                                            :class="{ 'border-rose-500 ring-rose-50': form.errors.phone }"
                                        />
                                    </div>
                                    <p v-if="form.errors.phone" class="text-[9px] font-black text-rose-500 uppercase">{{ form.errors.phone }}</p>
                                </div>
                            </div>

                            <div class="grid gap-6 md:grid-cols-2">
                                 <div class="space-y-2">
                                    <Label for="county" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 ml-1">County / Region</Label>
                                    <div class="relative">
                                        <MapPin class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground/30" />
                                        <Input 
                                            id="county" 
                                            v-model="form.county" 
                                            class="pl-10 h-11 rounded-xl border-border bg-muted/20 focus:bg-background focus:ring-primary/20 font-bold transition-all"
                                            :class="{ 'border-rose-500 ring-rose-50': form.errors.county }"
                                        />
                                    </div>
                                    <p v-if="form.errors.county" class="text-[9px] font-black text-rose-500 uppercase">{{ form.errors.county }}</p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="status" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 ml-1">System Status</Label>
                                    <Select v-model="form.status">
                                        <SelectTrigger class="h-11 rounded-xl border-border bg-muted/20 font-bold focus:bg-background transition-all">
                                            <SelectValue placeholder="Select status" />
                                        </SelectTrigger>
                                        <SelectContent class="rounded-xl p-1 shadow-2xl border-border">
                                            <SelectItem value="active" class="rounded-lg p-2 font-bold text-sm text-emerald-600">Active</SelectItem>
                                            <SelectItem value="inactive" class="rounded-lg p-2 font-bold text-sm text-rose-600">Inactive</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-4 p-4">
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
                                <Save v-if="!form.processing" class="h-4 w-4" />
                                <span v-else class="h-4 w-4 rounded-full border-2 border-white/30 border-t-white animate-spin"></span>
                                <span class="text-sm uppercase tracking-widest">{{ form.processing ? 'Saving...' : 'Save Changes' }}</span>
                            </div>
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
