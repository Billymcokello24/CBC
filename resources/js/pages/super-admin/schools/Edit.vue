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
        <div class="flex h-full flex-1 flex-col gap-6 p-6 max-w-5xl mx-auto w-full">
            <div class="flex items-center gap-4">
                <Link :href="schoolsRoutes.index().url">
                    <Button variant="ghost" size="icon" class="rounded-full hover:bg-violet-50 text-violet-600 transition-all active:scale-90">
                        <ChevronLeft class="h-5 w-5" />
                    </Button>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-gray-900">Update Institution</h1>
                    <p class="text-muted-foreground text-sm font-medium">Modifying details for <span class="text-violet-600 font-bold">{{ school.name }}</span> ({{ school.code }})</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="grid gap-6">
                <!-- Basic Details -->
                <Card class="border-none shadow-xl shadow-violet-100/50 rounded-2xl overflow-hidden ring-1 ring-violet-50">
                    <CardHeader class="bg-violet-50/50 pb-8 pt-8">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-600 text-white font-black shadow-lg shadow-violet-200">
                                <Building class="h-5 w-5" />
                            </div>
                            <div>
                                <CardTitle class="text-xl font-black text-gray-900">General Information</CardTitle>
                                <CardDescription class="text-gray-500 font-bold">Update the core identification of this school.</CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="grid gap-8 p-8 pt-10">
                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="space-y-2.5">
                                <Label for="name" class="font-black text-xs uppercase tracking-widest text-gray-400">School Name</Label>
                                <div class="relative">
                                    <SchoolIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                                    <Input 
                                        id="name" 
                                        v-model="form.name" 
                                        class="pl-10 h-12 rounded-xl border-gray-100 bg-gray-50/30 focus:bg-white transition-all font-bold"
                                        :class="{ 'border-red-500 ring-red-50': form.errors.name }"
                                    />
                                </div>
                                <p v-if="form.errors.name" class="text-[10px] font-bold text-red-500 uppercase tracking-tighter">{{ form.errors.name }}</p>
                            </div>

                            <div class="space-y-2.5 opacity-60 grayscale cursor-not-allowed">
                                <Label class="font-black text-xs uppercase tracking-widest text-gray-400">Institution Code</Label>
                                <div class="relative">
                                    <Hash class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                                    <Input 
                                        :value="school.code" 
                                        disabled
                                        class="pl-10 h-12 rounded-xl border-gray-100 bg-gray-100 font-black cursor-not-allowed"
                                    />
                                </div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter flex items-center gap-1">
                                    <AlertCircle class="h-3 w-3" /> System ID cannot be changed
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Contact and Location -->
                <Card class="border-none shadow-xl shadow-sky-100/50 rounded-2xl overflow-hidden ring-1 ring-sky-50">
                    <CardHeader class="bg-sky-50/50 pb-8 pt-8">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-500 text-white font-black shadow-lg shadow-sky-200">
                                <MapPin class="h-5 w-5" />
                            </div>
                            <div>
                                <CardTitle class="text-xl font-black text-gray-900">Communication & Status</CardTitle>
                                <CardDescription class="text-gray-500 font-bold">Manage how the institution is contacted and its system access.</CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="grid gap-8 p-8 pt-10">
                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="space-y-2.5">
                                <Label for="email" class="font-black text-xs uppercase tracking-widest text-gray-400">Admin Email</Label>
                                <div class="relative">
                                    <Mail class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                                    <Input 
                                        id="email" 
                                        type="email"
                                        v-model="form.email" 
                                        class="pl-10 h-12 rounded-xl border-gray-100 bg-gray-50/30 focus:bg-white transition-all font-bold"
                                        :class="{ 'border-red-500 ring-red-50': form.errors.email }"
                                    />
                                </div>
                                <p v-if="form.errors.email" class="text-[10px] font-bold text-red-500 uppercase tracking-tighter">{{ form.errors.email }}</p>
                            </div>

                            <div class="space-y-2.5">
                                <Label for="phone" class="font-black text-xs uppercase tracking-widest text-gray-400">Contact Number</Label>
                                <div class="relative">
                                    <Phone class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                                    <Input 
                                        id="phone" 
                                        v-model="form.phone" 
                                        class="pl-10 h-12 rounded-xl border-gray-100 bg-gray-50/30 focus:bg-white transition-all font-bold"
                                        :class="{ 'border-red-500 ring-red-50': form.errors.phone }"
                                    />
                                </div>
                                <p v-if="form.errors.phone" class="text-[10px] font-bold text-red-500 uppercase tracking-tighter">{{ form.errors.phone }}</p>
                            </div>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2">
                             <div class="space-y-2.5">
                                <Label for="county" class="font-black text-xs uppercase tracking-widest text-gray-400">County / Region</Label>
                                <div class="relative">
                                    <MapPin class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                                    <Input 
                                        id="county" 
                                        v-model="form.county" 
                                        class="pl-10 h-12 rounded-xl border-gray-100 bg-gray-50/30 focus:bg-white transition-all font-bold"
                                        :class="{ 'border-red-500 ring-red-50': form.errors.county }"
                                    />
                                </div>
                                <p v-if="form.errors.county" class="text-[10px] font-bold text-red-500 uppercase tracking-tighter">{{ form.errors.county }}</p>
                            </div>

                            <div class="space-y-2.5">
                                <Label for="status" class="font-black text-xs uppercase tracking-widest text-gray-400">System Status</Label>
                                <Select v-model="form.status">
                                    <SelectTrigger class="h-12 rounded-xl border-gray-100 bg-gray-50/30 font-bold focus:bg-white transition-all">
                                        <SelectValue placeholder="Select status" />
                                    </SelectTrigger>
                                    <SelectContent class="rounded-xl p-1 shadow-2xl border-none">
                                        <SelectItem value="active" class="rounded-lg p-2 font-bold text-sm text-green-600">Active</SelectItem>
                                        <SelectItem value="inactive" class="rounded-lg p-2 font-bold text-sm text-red-600">Inactive</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-4 p-4">
                    <Link :href="schoolsRoutes.index().url">
                        <Button type="button" variant="ghost" class="font-bold text-gray-500 hover:bg-gray-100 h-12 px-8 rounded-xl">
                            Cancel
                        </Button>
                    </Link>
                    <Button 
                        type="submit" 
                        :disabled="form.processing"
                        class="bg-violet-600 hover:bg-violet-700 text-white font-black h-12 px-10 rounded-xl shadow-xl shadow-violet-200 transition-all active:scale-95 flex items-center gap-2"
                    >
                        <Save v-if="!form.processing" class="h-4 w-4" />
                        <span v-else class="h-4 w-4 rounded-full border-2 border-white/30 border-t-white animate-spin"></span>
                        Update Institution
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
