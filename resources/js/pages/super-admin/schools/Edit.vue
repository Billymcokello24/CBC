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
    AlertCircle,
    Home,
    ChevronRight,
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
    SelectValue,
} from '@/components/ui/select';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

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
    {
        title: `Edit ${props.school.name}`,
        href: `/super-admin/schools/${props.school.id}/edit`,
    },
];

const form = useForm({
    name: props.school.name,
    email: props.school.email,
    phone: props.school.phone,
    county: props.school.county,
    status: props.school.status,
    _method: 'PUT',
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
        <div class="mx-auto max-w-[1600px] animate-in space-y-6 p-4 pb-10 duration-700 fade-in slide-in-from-bottom-4 sm:space-y-8 sm:p-6 sm:pb-20 md:p-8">
            <div class="mx-auto w-full max-w-5xl space-y-8">
                <!-- Navigation Header -->
                <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between px-1">
                    <div class="flex flex-col gap-1">
                        <div class="mb-1 flex items-center gap-2 text-xs text-muted-foreground sm:text-xs">
                            <Home class="h-3 w-3 sm:h-3.5 sm:w-3.5" />
                            <ChevronRight class="h-2.5 w-2.5 sm:h-3 sm:w-3" />
                            <span class="font-medium tracking-tight text-foreground uppercase">Tenant Registry</span>
                            <ChevronRight class="h-2.5 w-2.5 sm:h-3 sm:w-3" />
                            <span class="font-medium tracking-tight text-foreground uppercase">Edit {{ school.name }}</span>
                        </div>
                        <div class="flex items-center gap-4">
                            <Link :href="schoolsRoutes.index().url">
                                <Button
                                    variant="ghost"
                                    class="h-10 w-10 shrink-0 rounded-xl border border-border bg-card text-muted-foreground/40 shadow-sm transition-all hover:text-foreground"
                                >
                                    <ChevronLeft class="h-5 w-5" />
                                </Button>
                            </Link>
                            <h1 class="text-2xl leading-tight font-bold tracking-tight text-foreground sm:text-3xl">
                                Edit School
                            </h1>
                        </div>
                        <p class="text-sm text-muted-foreground sm:text-sm">
                            Updating configuration for <span class="text-primary font-bold">{{ school.name }}</span>
                        </p>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-8">
                    <!-- Basic Details -->
                    <div
                        class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm dark:border-white/5"
                    >
                        <div class="border-b border-border bg-muted/20 p-6">
                            <div class="flex items-center gap-4">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl border border-primary/10 bg-primary/10 text-primary"
                                >
                                    <Building class="h-5 w-5" />
                                </div>
                                <div>
                                    <h2
                                        class="text-lg font-bold tracking-tight text-foreground"
                                    >
                                        School Details
                                    </h2>
                                    <p
                                        class="text-sm font-bold tracking-tight text-muted-foreground/60 uppercase"
                                    >
                                        Core identification and system code.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="grid gap-8 p-6">
                            <div class="grid gap-6 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label
                                        for="name"
                                        class="ml-1 text-xs font-medium tracking-tight text-muted-foreground/60 uppercase"
                                        >School Name</Label
                                    >
                                    <div class="relative">
                                        <SchoolIcon
                                            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground/30"
                                        />
                                        <Input
                                            id="name"
                                            v-model="form.name"
                                            class="h-11 rounded-xl border-border bg-muted/20 pl-10 font-bold transition-all focus:bg-background focus:ring-primary/20"
                                            :class="{
                                                'border-rose-500 ring-rose-50':
                                                    form.errors.name,
                                            }"
                                        />
                                    </div>
                                    <p
                                        v-if="form.errors.name"
                                        class="text-xs font-bold text-rose-500 uppercase"
                                    >
                                        {{ form.errors.name }}
                                    </p>
                                </div>

                                <div class="space-y-2 opacity-60">
                                    <Label
                                        class="ml-1 text-xs font-medium tracking-tight text-muted-foreground/60 uppercase"
                                        >School Code</Label
                                    >
                                    <div class="relative">
                                        <Hash
                                            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground/30"
                                        />
                                        <Input
                                            :value="school.code"
                                            disabled
                                            class="h-11 cursor-not-allowed rounded-xl border-border bg-muted pl-10 font-bold"
                                        />
                                    </div>
                                    <p
                                        class="mt-1 flex items-center gap-1 text-xs font-bold text-muted-foreground/40 uppercase"
                                    >
                                        <AlertCircle class="h-3 w-3" /> System
                                        ID cannot be changed
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact and Location -->
                    <div
                        class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm dark:border-white/5"
                    >
                        <div class="border-b border-border bg-muted/20 p-6">
                            <div class="flex items-center gap-4">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl border border-primary/10 bg-primary/10 text-primary"
                                >
                                    <MapPin class="h-5 w-5" />
                                </div>
                                <div>
                                    <h2
                                        class="text-lg font-bold tracking-tight text-foreground"
                                    >
                                        Contact & Status
                                    </h2>
                                    <p
                                        class="text-sm font-bold tracking-tight text-muted-foreground/60 uppercase"
                                    >
                                        Location, communication and account
                                        toggle.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="grid gap-8 p-6">
                            <div class="grid gap-6 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label
                                        for="email"
                                        class="ml-1 text-xs font-medium tracking-tight text-muted-foreground/60 uppercase"
                                        >Official Email</Label
                                    >
                                    <div class="relative">
                                        <Mail
                                            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground/30"
                                        />
                                        <Input
                                            id="email"
                                            type="email"
                                            v-model="form.email"
                                            class="h-11 rounded-xl border-border bg-muted/20 pl-10 font-bold transition-all focus:bg-background focus:ring-primary/20"
                                            :class="{
                                                'border-rose-500 ring-rose-50':
                                                    form.errors.email,
                                            }"
                                        />
                                    </div>
                                    <p
                                        v-if="form.errors.email"
                                        class="text-xs font-bold text-rose-500 uppercase"
                                    >
                                        {{ form.errors.email }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label
                                        for="phone"
                                        class="ml-1 text-xs font-medium tracking-tight text-muted-foreground/60 uppercase"
                                        >Phone Number</Label
                                    >
                                    <div class="relative">
                                        <Phone
                                            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground/30"
                                        />
                                        <Input
                                            id="phone"
                                            v-model="form.phone"
                                            class="h-11 rounded-xl border-border bg-muted/20 pl-10 font-bold transition-all focus:bg-background focus:ring-primary/20"
                                            :class="{
                                                'border-rose-500 ring-rose-50':
                                                    form.errors.phone,
                                            }"
                                        />
                                    </div>
                                    <p
                                        v-if="form.errors.phone"
                                        class="text-xs font-bold text-rose-500 uppercase"
                                    >
                                        {{ form.errors.phone }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid gap-6 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label
                                        for="county"
                                        class="ml-1 text-xs font-medium tracking-tight text-muted-foreground/60 uppercase"
                                        >County / Region</Label
                                    >
                                    <div class="relative">
                                        <MapPin
                                            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground/30"
                                        />
                                        <Input
                                            id="county"
                                            v-model="form.county"
                                            class="h-11 rounded-xl border-border bg-muted/20 pl-10 font-bold transition-all focus:bg-background focus:ring-primary/20"
                                            :class="{
                                                'border-rose-500 ring-rose-50':
                                                    form.errors.county,
                                            }"
                                        />
                                    </div>
                                    <p
                                        v-if="form.errors.county"
                                        class="text-xs font-bold text-rose-500 uppercase"
                                    >
                                        {{ form.errors.county }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label
                                        for="status"
                                        class="ml-1 text-xs font-medium tracking-tight text-muted-foreground/60 uppercase"
                                        >System Status</Label
                                    >
                                    <Select v-model="form.status">
                                        <SelectTrigger
                                            class="h-11 rounded-xl border-border bg-muted/20 font-bold transition-all focus:bg-background"
                                        >
                                            <SelectValue
                                                placeholder="Select status"
                                            />
                                        </SelectTrigger>
                                        <SelectContent
                                            class="rounded-xl border-border p-1 shadow-lg"
                                        >
                                            <SelectItem
                                                value="active"
                                                class="rounded-lg p-2 text-sm font-bold text-emerald-600"
                                                >Active</SelectItem
                                            >
                                            <SelectItem
                                                value="inactive"
                                                class="rounded-lg p-2 text-sm font-bold text-rose-600"
                                                >Inactive</SelectItem
                                            >
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-4 p-4">
                        <Link :href="schoolsRoutes.index().url">
                            <Button
                                type="button"
                                variant="ghost"
                                class="h-11 rounded-xl px-6 text-xs font-bold tracking-tight text-muted-foreground/40 uppercase hover:text-foreground"
                            >
                                Cancel
                            </Button>
                        </Link>
                        <Button
                            type="submit"
                            :disabled="form.processing"
                            class="h-11 rounded-xl bg-primary px-10 font-bold text-white shadow-lg shadow-primary/20 transition-all hover:bg-primary/90"
                        >
                            <div class="relative flex items-center gap-2">
                                <Save v-if="!form.processing" class="h-4 w-4" />
                                <span
                                    v-else
                                    class="h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white"
                                ></span>
                                <span
                                    class="text-sm tracking-tight uppercase"
                                    >{{
                                        form.processing
                                            ? 'Saving...'
                                            : 'Save Changes'
                                    }}</span
                                >
                            </div>
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
