<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Users, Plus, Search, Filter, Download, MoreHorizontal, Eye, Edit, Trash2, Mail, Phone } from 'lucide-vue-next';
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import type { BreadcrumbItem } from '@/types';
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Teachers', href: '/teachers' },
];
const teachers = ref([
    { id: 1, staff_number: 'TCH001', name: 'Mr. James Ochieng', gender: 'Male', department: 'Mathematics', phone: '+254712345678', email: 'j.ochieng@school.com', status: 'active' },
    { id: 2, staff_number: 'TCH002', name: 'Mrs. Jane Wambui', gender: 'Female', department: 'English', phone: '+254723456789', email: 'j.wambui@school.com', status: 'active' },
    { id: 3, staff_number: 'TCH003', name: 'Mr. Peter Kimani', gender: 'Male', department: 'Science', phone: '+254734567890', email: 'p.kimani@school.com', status: 'active' },
    { id: 4, staff_number: 'TCH004', name: 'Ms. Grace Achieng', gender: 'Female', department: 'Kiswahili', phone: '+254745678901', email: 'g.achieng@school.com', status: 'on_leave' },
    { id: 5, staff_number: 'TCH005', name: 'Mr. David Mutua', gender: 'Male', department: 'Social Studies', phone: '+254756789012', email: 'd.mutua@school.com', status: 'active' },
]);
const searchQuery = ref('');
</script>
<template>
    <Head title="Teachers" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-500/10">
                        <Users class="h-6 w-6 text-purple-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Teachers</h1>
                        <p class="text-muted-foreground">Manage teaching staff and departments</p>
                    </div>
                </div>
                <Button as-child>
                    <Link href="/teachers/create"><Plus class="mr-2 h-4 w-4" />Add Teacher</Link>
                </Button>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border bg-gradient-to-br from-purple-50 to-purple-100/50 p-4 dark:from-purple-950/50 dark:to-purple-900/30">
                    <div class="text-sm text-muted-foreground">Total Teachers</div>
                    <div class="mt-1 text-3xl font-bold text-purple-600">85</div>
                </div>
                <div class="rounded-xl border bg-gradient-to-br from-green-50 to-green-100/50 p-4 dark:from-green-950/50 dark:to-green-900/30">
                    <div class="text-sm text-muted-foreground">Active</div>
                    <div class="mt-1 text-3xl font-bold text-green-600">78</div>
                </div>
                <div class="rounded-xl border bg-gradient-to-br from-amber-50 to-amber-100/50 p-4 dark:from-amber-950/50 dark:to-amber-900/30">
                    <div class="text-sm text-muted-foreground">On Leave</div>
                    <div class="mt-1 text-3xl font-bold text-amber-600">5</div>
                </div>
                <div class="rounded-xl border bg-gradient-to-br from-blue-50 to-blue-100/50 p-4 dark:from-blue-950/50 dark:to-blue-900/30">
                    <div class="text-sm text-muted-foreground">Departments</div>
                    <div class="mt-1 text-3xl font-bold text-blue-600">8</div>
                </div>
            </div>
            <div class="flex flex-col gap-4 md:flex-row md:items-center">
                <div class="relative flex-1 md:max-w-sm">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input v-model="searchQuery" placeholder="Search teachers..." class="pl-9" />
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm"><Filter class="mr-2 h-4 w-4" />Filters</Button>
                    <Button variant="outline" size="sm"><Download class="mr-2 h-4 w-4" />Export</Button>
                </div>
            </div>
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div v-for="teacher in teachers" :key="teacher.id" class="rounded-xl border bg-card p-6 transition-all hover:shadow-lg">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-4">
                            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-purple-500 to-purple-600 text-lg font-semibold text-white">
                                {{ teacher.name.split(' ')[1]?.charAt(0) || teacher.name.charAt(0) }}
                            </div>
                            <div>
                                <h3 class="font-semibold">{{ teacher.name }}</h3>
                                <p class="text-sm text-muted-foreground">{{ teacher.staff_number }}</p>
                            </div>
                        </div>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="ghost" size="icon" class="h-8 w-8"><MoreHorizontal class="h-4 w-4" /></Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuItem><Eye class="mr-2 h-4 w-4" />View</DropdownMenuItem>
                                <DropdownMenuItem><Edit class="mr-2 h-4 w-4" />Edit</DropdownMenuItem>
                                <DropdownMenuItem class="text-destructive"><Trash2 class="mr-2 h-4 w-4" />Delete</DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                    <div class="mt-4 space-y-2">
                        <div class="flex items-center gap-2 text-sm">
                            <Badge variant="outline">{{ teacher.department }}</Badge>
                            <Badge :variant="teacher.status === 'active' ? 'default' : 'secondary'">{{ teacher.status }}</Badge>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-muted-foreground">
                            <Phone class="h-4 w-4" />{{ teacher.phone }}
                        </div>
                        <div class="flex items-center gap-2 text-sm text-muted-foreground">
                            <Mail class="h-4 w-4" />{{ teacher.email }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
