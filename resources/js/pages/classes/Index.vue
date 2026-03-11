<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { School, Plus, Search, Users, BookOpen, GraduationCap, MoreHorizontal } from 'lucide-vue-next';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import type { BreadcrumbItem } from '@/types';
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Classes', href: '/classes' },
];
const classes = ref([
    { id: 1, name: 'Grade 1A', grade: 'Grade 1', stream: 'A', teacher: 'Mrs. Jane Wambui', students: 35, capacity: 40 },
    { id: 2, name: 'Grade 1B', grade: 'Grade 1', stream: 'B', teacher: 'Mr. John Kamau', students: 38, capacity: 40 },
    { id: 3, name: 'Grade 2A', grade: 'Grade 2', stream: 'A', teacher: 'Ms. Grace Achieng', students: 32, capacity: 40 },
    { id: 4, name: 'Grade 2B', grade: 'Grade 2', stream: 'B', teacher: 'Mr. Peter Ochieng', students: 36, capacity: 40 },
    { id: 5, name: 'Grade 3A', grade: 'Grade 3', stream: 'A', teacher: 'Mrs. Mary Njeri', students: 40, capacity: 40 },
    { id: 6, name: 'Grade 3B', grade: 'Grade 3', stream: 'B', teacher: 'Mr. David Kiprop', students: 37, capacity: 40 },
]);
const grades = ['PP1', 'PP2', 'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6'];
</script>
<template>
    <Head title="Classes" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-500/10">
                        <School class="h-6 w-6 text-indigo-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Classes</h1>
                        <p class="text-muted-foreground">Manage class structures and allocations</p>
                    </div>
                </div>
                <Button as-child><Link href="/classes/create"><Plus class="mr-2 h-4 w-4" />Add Class</Link></Button>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border bg-gradient-to-br from-indigo-50 to-indigo-100/50 p-4 dark:from-indigo-950/50 dark:to-indigo-900/30">
                    <div class="flex items-center gap-3"><School class="h-5 w-5 text-indigo-600" /><span class="text-sm text-muted-foreground">Total Classes</span></div>
                    <p class="mt-2 text-3xl font-bold text-indigo-600">42</p>
                </div>
                <div class="rounded-xl border bg-gradient-to-br from-blue-50 to-blue-100/50 p-4 dark:from-blue-950/50 dark:to-blue-900/30">
                    <div class="flex items-center gap-3"><Users class="h-5 w-5 text-blue-600" /><span class="text-sm text-muted-foreground">Total Students</span></div>
                    <p class="mt-2 text-3xl font-bold text-blue-600">1,250</p>
                </div>
                <div class="rounded-xl border bg-gradient-to-br from-green-50 to-green-100/50 p-4 dark:from-green-950/50 dark:to-green-900/30">
                    <div class="flex items-center gap-3"><GraduationCap class="h-5 w-5 text-green-600" /><span class="text-sm text-muted-foreground">Avg. Class Size</span></div>
                    <p class="mt-2 text-3xl font-bold text-green-600">30</p>
                </div>
                <div class="rounded-xl border bg-gradient-to-br from-purple-50 to-purple-100/50 p-4 dark:from-purple-950/50 dark:to-purple-900/30">
                    <div class="flex items-center gap-3"><BookOpen class="h-5 w-5 text-purple-600" /><span class="text-sm text-muted-foreground">Grades</span></div>
                    <p class="mt-2 text-3xl font-bold text-purple-600">8</p>
                </div>
            </div>
            <div class="flex flex-col gap-4 md:flex-row md:items-center">
                <div class="relative flex-1 md:max-w-sm">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input placeholder="Search classes..." class="pl-9" />
                </div>
            </div>
            <!-- Grade Level Tabs -->
            <div class="flex flex-wrap gap-2">
                <Button v-for="grade in grades" :key="grade" variant="outline" size="sm" class="rounded-full">{{ grade }}</Button>
            </div>
            <!-- Class Cards Grid -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div v-for="cls in classes" :key="cls.id" class="rounded-xl border bg-card p-6 transition-all hover:shadow-lg">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white font-bold">
                                {{ cls.stream }}
                            </div>
                            <div>
                                <h3 class="font-semibold text-lg">{{ cls.name }}</h3>
                                <p class="text-sm text-muted-foreground">{{ cls.grade }}</p>
                            </div>
                        </div>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child><Button variant="ghost" size="icon" class="h-8 w-8"><MoreHorizontal class="h-4 w-4" /></Button></DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuItem>View Details</DropdownMenuItem>
                                <DropdownMenuItem>Edit Class</DropdownMenuItem>
                                <DropdownMenuItem>Manage Students</DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center gap-2 text-sm">
                            <Users class="h-4 w-4 text-muted-foreground" />
                            <span>Class Teacher: <span class="font-medium">{{ cls.teacher }}</span></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground">Students</span>
                            <span class="font-semibold">{{ cls.students }}/{{ cls.capacity }}</span>
                        </div>
                        <div class="h-2 rounded-full bg-muted">
                            <div class="h-full rounded-full bg-indigo-500 transition-all" :style="{ width: `${(cls.students / cls.capacity) * 100}%` }"></div>
                        </div>
                        <div class="flex gap-2 pt-2">
                            <Button variant="outline" size="sm" class="flex-1" as-child><Link :href="`/classes/${cls.id}`">View Class</Link></Button>
                            <Button variant="outline" size="sm" class="flex-1" as-child><Link :href="`/classes/${cls.id}/students`">Students</Link></Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
