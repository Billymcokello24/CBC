<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Loader2, Save } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    student: {
        id: number;
        first_name: string;
        middle_name: string | null;
        last_name: string;
        admission_number: string | null;
        gender: string;
        date_of_birth: string | null;
        class_id: number | null;
        county: string | null;
        boarding_status: string | null;
        status: string;
    };
    classes: Array<{ id: number; name: string }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Students', href: '/students' },
    { title: 'Edit Student', href: `/students/${props.student.id}/edit` },
];

const form = useForm({
    first_name: props.student.first_name,
    middle_name: props.student.middle_name ?? '',
    last_name: props.student.last_name,
    admission_number: props.student.admission_number ?? '',
    gender: props.student.gender,
    date_of_birth: props.student.date_of_birth ?? '',
    class_id: props.student.class_id ? String(props.student.class_id) : '',
    county: props.student.county ?? '',
    boarding_status: props.student.boarding_status ?? 'day',
    status: props.student.status,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        class_id: data.class_id ? Number(data.class_id) : null,
    })).put(`/students/${props.student.id}`);
};
</script>

<template>
    <Head title="Edit Student" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" as-child>
                    <Link href="/students"><ArrowLeft class="h-5 w-5" /></Link>
                </Button>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Edit Student</h1>
                    <p class="text-muted-foreground">Update student information in the database</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-6 rounded-xl border bg-card p-6">
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div class="space-y-2">
                        <Label for="first_name">First Name</Label>
                        <Input id="first_name" v-model="form.first_name" />
                        <InputError :message="form.errors.first_name" />
                    </div>
                    <div class="space-y-2">
                        <Label for="middle_name">Middle Name</Label>
                        <Input id="middle_name" v-model="form.middle_name" />
                        <InputError :message="form.errors.middle_name" />
                    </div>
                    <div class="space-y-2">
                        <Label for="last_name">Last Name</Label>
                        <Input id="last_name" v-model="form.last_name" />
                        <InputError :message="form.errors.last_name" />
                    </div>
                    <div class="space-y-2">
                        <Label for="admission_number">Admission Number</Label>
                        <Input id="admission_number" v-model="form.admission_number" />
                        <InputError :message="form.errors.admission_number" />
                    </div>
                    <div class="space-y-2">
                        <Label for="gender">Gender</Label>
                        <select id="gender" v-model="form.gender" class="h-10 w-full rounded-md border bg-background px-3 text-sm">
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                        <InputError :message="form.errors.gender" />
                    </div>
                    <div class="space-y-2">
                        <Label for="date_of_birth">Date of Birth</Label>
                        <Input id="date_of_birth" v-model="form.date_of_birth" type="date" />
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
                        <Input id="county" v-model="form.county" />
                        <InputError :message="form.errors.county" />
                    </div>
                    <div class="space-y-2">
                        <Label for="boarding_status">Boarding Status</Label>
                        <select id="boarding_status" v-model="form.boarding_status" class="h-10 w-full rounded-md border bg-background px-3 text-sm">
                            <option value="day">Day</option>
                            <option value="boarding">Boarding</option>
                        </select>
                        <InputError :message="form.errors.boarding_status" />
                    </div>
                    <div class="space-y-2">
                        <Label for="status">Status</Label>
                        <select id="status" v-model="form.status" class="h-10 w-full rounded-md border bg-background px-3 text-sm">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="graduated">Graduated</option>
                            <option value="transferred">Transferred</option>
                            <option value="withdrawn">Withdrawn</option>
                            <option value="suspended">Suspended</option>
                        </select>
                        <InputError :message="form.errors.status" />
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <Button type="button" variant="outline" as-child>
                        <Link href="/students">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                        <Save v-else class="mr-2 h-4 w-4" />
                        Save Changes
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
