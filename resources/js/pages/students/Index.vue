<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { GraduationCap, Plus, Search, Filter, Download, MoreHorizontal, Eye, Edit, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { BreadcrumbItem } from '@/types';
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Students', href: '/students' },
];
const students = ref([
    { id: 1, admission_number: 'ADM001', name: 'John Kamau Mwangi', gender: 'Male', class: 'Grade 4A', status: 'active', photo: null },
    { id: 2, admission_number: 'ADM002', name: 'Mary Wanjiku Njeri', gender: 'Female', class: 'Grade 5B', status: 'active', photo: null },
    { id: 3, admission_number: 'ADM003', name: 'Peter Ochieng Otieno', gender: 'Male', class: 'Grade 4A', status: 'active', photo: null },
    { id: 4, admission_number: 'ADM004', name: 'Sarah Muthoni Wambui', gender: 'Female', class: 'Grade 5A', status: 'active', photo: null },
    { id: 5, admission_number: 'ADM005', name: 'David Kiprop Koech', gender: 'Male', class: 'Grade 4B', status: 'inactive', photo: null },
    { id: 6, admission_number: 'ADM006', name: 'Grace Akinyi Odhiambo', gender: 'Female', class: 'Grade 6A', status: 'active', photo: null },
    { id: 7, admission_number: 'ADM007', name: 'James Mutua Kioko', gender: 'Male', class: 'Grade 3B', status: 'active', photo: null },
    { id: 8, admission_number: 'ADM008', name: 'Faith Chebet Kiplagat', gender: 'Female', class: 'Grade 5C', status: 'active', photo: null },
]);
const searchQuery = ref('');
</script>
<template>
    <Head title="Students" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-500/10">
                        <GraduationCap class="h-6 w-6 text-blue-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Students</h1>
                        <p class="text-muted-foreground">Manage student records and enrollments</p>
                    </div>
                </div>
                <Button as-child>
                    <Link href="/students/create">
                        <Plus class="mr-2 h-4 w-4" />
                        Add Student
                    </Link>
                </Button>
            </div>
            <!-- Stats -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border bg-gradient-to-br from-blue-50 to-blue-100/50 p-4 dark:from-blue-950/50 dark:to-blue-900/30">
                    <div class="text-sm text-muted-foreground">Total Students</div>
                    <div class="mt-1 text-3xl font-bold text-blue-600">1,250</div>
                    <div class="mt-1 text-xs text-green-600">+5.2% from last term</div>
                </div>
                <div class="rounded-xl border bg-gradient-to-br from-green-50 to-green-100/50 p-4 dark:from-green-950/50 dark:to-green-900/30">
                    <div class="text-sm text-muted-foreground">Active</div>
                    <div class="mt-1 text-3xl font-bold text-green-600">1,188</div>
                    <div class="mt-1 text-xs text-muted-foreground">95% of total</div>
                </div>
                <div class="rounded-xl border bg-gradient-to-br from-indigo-50 to-indigo-100/50 p-4 dark:from-indigo-950/50 dark:to-indigo-900/30">
                    <div class="text-sm text-muted-foreground">Boys</div>
                    <div class="mt-1 text-3xl font-bold text-indigo-600">650</div>
                    <div class="mt-1 text-xs text-muted-foreground">52% of total</div>
                </div>
                <div class="rounded-xl border bg-gradient-to-br from-pink-50 to-pink-100/50 p-4 dark:from-pink-950/50 dark:to-pink-900/30">
                    <div class="text-sm text-muted-foreground">Girls</div>
                    <div class="mt-1 text-3xl font-bold text-pink-600">600</div>
                    <div class="mt-1 text-xs text-muted-foreground">48% of total</div>
                </div>
            </div>
            <!-- Filters -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center">
                <div class="relative flex-1 md:max-w-sm">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input v-model="searchQuery" placeholder="Search students..." class="pl-9" />
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm">
                        <Filter class="mr-2 h-4 w-4" />
                        Filters
                    </Button>
                    <Button variant="outline" size="sm">
                        <Download class="mr-2 h-4 w-4" />
                        Export
                    </Button>
                </div>
            </div>
            <!-- Table -->
            <div class="rounded-xl border bg-card">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b bg-muted/50">
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Student</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Adm. No</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Gender</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Class</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Status</th>
                                <th class="px-4 py-3 text-right text-sm font-medium text-muted-foreground">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="student in students" :key="student.id" class="border-b transition-colors hover:bg-muted/50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-primary/20 to-primary/10 text-sm font-semibold text-primary">
                                            {{ student.name.charAt(0) }}
                                        </div>
                                        <div>
                                            <p class="font-medium">{{ student.name }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm">{{ student.admission_number }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <span :class="student.gender === 'Male' ? 'text-blue-600' : 'text-pink-600'">
                                        {{ student.gender }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm">{{ student.class }}</td>
                                <td class="px-4 py-3">
                                    <Badge :variant="student.status === 'active' ? 'default' : 'secondary'">
                                        {{ student.status }}
                                    </Badge>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" size="icon" class="h-8 w-8">
                                                <MoreHorizontal class="h-4 w-4" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuItem>
                                                <Eye class="mr-2 h-4 w-4" /> View
                                            </DropdownMenuItem>
                                            <DropdownMenuItem>
                                                <Edit class="mr-2 h-4 w-4" /> Edit
                                            </DropdownMenuItem>
                                            <DropdownMenuItem class="text-destructive">
                                                <Trash2 class="mr-2 h-4 w-4" /> Delete
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="flex items-center justify-between border-t px-4 py-3">
                    <p class="text-sm text-muted-foreground">Showing 1 to 8 of 1,250 students</p>
                    <div class="flex items-center gap-2">
                        <Button variant="outline" size="sm" disabled>Previous</Button>
                        <Button variant="outline" size="sm">Next</Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
