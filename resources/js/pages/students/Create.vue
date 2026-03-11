<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, GraduationCap, Loader2, Save } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

defineProps<{
    classes: Array<{ id: number; name: string }>;
    counties: string[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Students', href: '/students' },
    { title: 'Add Student', href: '/students/create' },
];

const form = useForm({
    first_name: '',
    middle_name: '',
    last_name: '',
    admission_number: '',
    gender: 'male',
    date_of_birth: '',
    class_id: '',
    county: '',
    boarding_status: 'day',
    status: 'active',
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        class_id: data.class_id ? Number(data.class_id) : null,
    })).post('/students');
};
</script>

<template>
    <Head title="Add Student" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="icon" as-child>
                        <Link href="/students"><ArrowLeft class="h-5 w-5" /></Link>
                    </Button>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Add New Student</h1>
                        <p class="text-muted-foreground">Register a new student in the system</p>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-8">
                <div class="rounded-xl border bg-card p-6">
                    <h2 class="mb-4 flex items-center gap-2 text-lg font-semibold">
                        <GraduationCap class="h-5 w-5 text-primary" />
                        Student Information
                    </h2>
                    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        <div class="space-y-2">
                            <Label for="first_name">First Name *</Label>
                            <Input id="first_name" v-model="form.first_name" placeholder="John" required />
                            <InputError :message="form.errors.first_name" />
                        </div>
                        <div class="space-y-2">
                            <Label for="middle_name">Middle Name</Label>
                            <Input id="middle_name" v-model="form.middle_name" placeholder="Kamau" />
                            <InputError :message="form.errors.middle_name" />
                        </div>
                        <div class="space-y-2">
                            <Label for="last_name">Last Name *</Label>
                            <Input id="last_name" v-model="form.last_name" placeholder="Mwangi" required />
                            <InputError :message="form.errors.last_name" />
                        </div>
                        <div class="space-y-2">
                            <Label for="admission_number">Admission Number *</Label>
                            <Input id="admission_number" v-model="form.admission_number" placeholder="STU00666" required />
                            <InputError :message="form.errors.admission_number" />
                        </div>
                        <div class="space-y-2">
                            <Label for="gender">Gender *</Label>
                            <select id="gender" v-model="form.gender" class="h-10 w-full rounded-md border bg-background px-3 text-sm">
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                            <InputError :message="form.errors.gender" />
                        </div>
                        <div class="space-y-2">
                            <Label for="date_of_birth">Date of Birth *</Label>
                            <Input id="date_of_birth" v-model="form.date_of_birth" type="date" required />
                            <InputError :message="form.errors.date_of_birth" />
                        </div>
                        <div class="space-y-2">
                            <Label for="class_id">Class</Label>
                            <select id="class_id" v-model="form.class_id" class="h-10 w-full rounded-md border bg-background px-3 text-sm">
                                <option value="">Unassigned</option>
                                <option v-for="schoolClass in classes" :key="schoolClass.id" :value="String(schoolClass.id)">
                                    {{ schoolClass.name }}
                                </option>
                            </select>
                            <InputError :message="form.errors.class_id" />
                        </div>
                        <div class="space-y-2">
                            <Label for="county">County</Label>
                            <select id="county" v-model="form.county" class="h-10 w-full rounded-md border bg-background px-3 text-sm">
                                <option value="">Select county</option>
                                <option v-for="county in counties" :key="county" :value="county">{{ county }}</option>
                            </select>
                            <InputError :message="form.errors.county" />
                        </div>
                        <div class="space-y-2">
                            <Label for="boarding_status">Boarding Status *</Label>
                            <select id="boarding_status" v-model="form.boarding_status" class="h-10 w-full rounded-md border bg-background px-3 text-sm">
                                <option value="day">Day</option>
                                <option value="boarding">Boarding</option>
                            </select>
                            <InputError :message="form.errors.boarding_status" />
                        </div>
                        <div class="space-y-2">
                            <Label for="status">Initial Status *</Label>
                            <select id="status" v-model="form.status" class="h-10 w-full rounded-md border bg-background px-3 text-sm">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="suspended">Suspended</option>
                            </select>
                            <InputError :message="form.errors.status" />
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-4">
                    <Button type="button" variant="outline" as-child>
                        <Link href="/students">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                        <Save v-else class="mr-2 h-4 w-4" />
                        Save Student
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
