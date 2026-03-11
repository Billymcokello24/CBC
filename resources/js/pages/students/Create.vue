<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { GraduationCap, ArrowLeft, Save, Loader2 } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import InputError from '@/components/InputError.vue';
import type { BreadcrumbItem } from '@/types';

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
    gender: '',
    date_of_birth: '',
    class_id: '',
    guardian_name: '',
    guardian_phone: '',
    guardian_email: '',
    address: '',
    county: '',
});

const submit = () => {
    form.post('/students');
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
                <!-- Personal Information -->
                <div class="rounded-xl border bg-card p-6">
                    <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
                        <GraduationCap class="h-5 w-5 text-primary" />
                        Personal Information
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
                        </div>
                        <div class="space-y-2">
                            <Label for="last_name">Last Name *</Label>
                            <Input id="last_name" v-model="form.last_name" placeholder="Mwangi" required />
                            <InputError :message="form.errors.last_name" />
                        </div>
                        <div class="space-y-2">
                            <Label for="admission_number">Admission Number *</Label>
                            <Input id="admission_number" v-model="form.admission_number" placeholder="ADM001" required />
                            <InputError :message="form.errors.admission_number" />
                        </div>
                        <div class="space-y-2">
                            <Label for="gender">Gender *</Label>
                            <Select v-model="form.gender">
                                <SelectTrigger><SelectValue placeholder="Select gender" /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="male">Male</SelectItem>
                                    <SelectItem value="female">Female</SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.gender" />
                        </div>
                        <div class="space-y-2">
                            <Label for="date_of_birth">Date of Birth *</Label>
                            <Input id="date_of_birth" v-model="form.date_of_birth" type="date" required />
                            <InputError :message="form.errors.date_of_birth" />
                        </div>
                        <div class="space-y-2">
                            <Label for="class_id">Class *</Label>
                            <Select v-model="form.class_id">
                                <SelectTrigger><SelectValue placeholder="Select class" /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="1">Grade 1A</SelectItem>
                                    <SelectItem value="2">Grade 1B</SelectItem>
                                    <SelectItem value="3">Grade 2A</SelectItem>
                                    <SelectItem value="4">Grade 2B</SelectItem>
                                    <SelectItem value="5">Grade 3A</SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.class_id" />
                        </div>
                    </div>
                </div>

                <!-- Guardian Information -->
                <div class="rounded-xl border bg-card p-6">
                    <h2 class="text-lg font-semibold mb-4">Guardian Information</h2>
                    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        <div class="space-y-2">
                            <Label for="guardian_name">Guardian Name *</Label>
                            <Input id="guardian_name" v-model="form.guardian_name" placeholder="Jane Wanjiku" required />
                        </div>
                        <div class="space-y-2">
                            <Label for="guardian_phone">Phone Number *</Label>
                            <Input id="guardian_phone" v-model="form.guardian_phone" placeholder="+254700000000" required />
                        </div>
                        <div class="space-y-2">
                            <Label for="guardian_email">Email</Label>
                            <Input id="guardian_email" v-model="form.guardian_email" type="email" placeholder="jane@email.com" />
                        </div>
                    </div>
                </div>

                <!-- Address Information -->
                <div class="rounded-xl border bg-card p-6">
                    <h2 class="text-lg font-semibold mb-4">Address Information</h2>
                    <div class="grid gap-6 md:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="address">Home Address</Label>
                            <Input id="address" v-model="form.address" placeholder="P.O. Box 123, Nairobi" />
                        </div>
                        <div class="space-y-2">
                            <Label for="county">County</Label>
                            <Select v-model="form.county">
                                <SelectTrigger><SelectValue placeholder="Select county" /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="nairobi">Nairobi</SelectItem>
                                    <SelectItem value="mombasa">Mombasa</SelectItem>
                                    <SelectItem value="kisumu">Kisumu</SelectItem>
                                    <SelectItem value="nakuru">Nakuru</SelectItem>
                                    <SelectItem value="eldoret">Uasin Gishu</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
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
