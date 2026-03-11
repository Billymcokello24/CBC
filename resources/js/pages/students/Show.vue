<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Calendar, GraduationCap, MapPin, Phone, ShieldPlus, UserRound } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Guardian {
    id: number;
    name: string;
    phone: string | null;
    email: string | null;
}

interface Student {
    id: number;
    admission_number: string | null;
    upi: string | null;
    name: string;
    first_name: string;
    middle_name: string | null;
    last_name: string;
    gender: string;
    date_of_birth: string | null;
    age: number | null;
    class: string | null;
    status: string;
    boarding_status: string | null;
    county: string | null;
    admission_date: string | null;
    blood_group: string | null;
    religion: string | null;
    nationality: string | null;
    photo: string | null;
    guardians: Guardian[];
}

const props = defineProps<{ student: Student }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Students', href: '/students' },
    { title: props.student.name, href: `/students/${props.student.id}` },
];
</script>

<template>
    <Head :title="student.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="icon" as-child>
                        <Link href="/students"><ArrowLeft class="h-5 w-5" /></Link>
                    </Button>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">{{ student.name }}</h1>
                        <p class="text-muted-foreground">Student profile from the database</p>
                    </div>
                </div>
                <Button as-child>
                    <Link :href="`/students/${student.id}/edit`">Edit Student</Link>
                </Button>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="rounded-xl border bg-card p-6 lg:col-span-2">
                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <h2 class="mb-4 text-lg font-semibold">Student Details</h2>
                            <div class="space-y-3 text-sm">
                                <div class="flex items-center gap-2"><GraduationCap class="h-4 w-4 text-muted-foreground" /> Admission No: {{ student.admission_number || '—' }}</div>
                                <div class="flex items-center gap-2"><UserRound class="h-4 w-4 text-muted-foreground" /> Gender: {{ student.gender }}</div>
                                <div class="flex items-center gap-2"><Calendar class="h-4 w-4 text-muted-foreground" /> DOB: {{ student.date_of_birth || '—' }} <span v-if="student.age">({{ student.age }} yrs)</span></div>
                                <div class="flex items-center gap-2"><ShieldPlus class="h-4 w-4 text-muted-foreground" /> UPI: {{ student.upi || '—' }}</div>
                                <div class="flex items-center gap-2"><MapPin class="h-4 w-4 text-muted-foreground" /> County: {{ student.county || '—' }}</div>
                            </div>
                        </div>
                        <div>
                            <h2 class="mb-4 text-lg font-semibold">School Info</h2>
                            <div class="space-y-3 text-sm">
                                <div>Class: <span class="font-medium">{{ student.class || 'Unassigned' }}</span></div>
                                <div>Admission Date: <span class="font-medium">{{ student.admission_date || '—' }}</span></div>
                                <div>Boarding: <span class="font-medium">{{ student.boarding_status || '—' }}</span></div>
                                <div>Blood Group: <span class="font-medium">{{ student.blood_group || '—' }}</span></div>
                                <div>Nationality: <span class="font-medium">{{ student.nationality || '—' }}</span></div>
                                <div class="flex items-center gap-2">Status: <Badge>{{ student.status }}</Badge></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border bg-card p-6">
                    <h2 class="mb-4 text-lg font-semibold">Guardians</h2>
                    <div v-if="student.guardians.length" class="space-y-4">
                        <div v-for="guardian in student.guardians" :key="guardian.id" class="rounded-lg border p-4 text-sm">
                            <p class="font-medium">{{ guardian.name }}</p>
                            <p class="mt-2 flex items-center gap-2 text-muted-foreground"><Phone class="h-4 w-4" /> {{ guardian.phone || '—' }}</p>
                            <p class="text-muted-foreground">{{ guardian.email || 'No email' }}</p>
                        </div>
                    </div>
                    <p v-else class="text-sm text-muted-foreground">No guardians linked.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
