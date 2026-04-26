<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Users,
    Plus,
    Search,
    Phone,
    Mail,
    MoreHorizontal,
    Eye,
    Edit,
    Trash2,
} from 'lucide-vue-next';
import { ref } from 'vue';
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
    { title: 'Guardians', href: '/guardians' },
];

const guardians = ref([
    {
        id: 1,
        name: 'Jane Wanjiku Kamau',
        phone: '+254712345678',
        email: 'jane.kamau@email.com',
        relationship: 'Mother',
        students: 2,
        status: 'active',
    },
    {
        id: 2,
        name: 'Peter Ochieng Otieno',
        phone: '+254723456789',
        email: 'peter.o@email.com',
        relationship: 'Father',
        students: 1,
        status: 'active',
    },
    {
        id: 3,
        name: 'Mary Muthoni Njeri',
        phone: '+254734567890',
        email: 'mary.njeri@email.com',
        relationship: 'Guardian',
        students: 3,
        status: 'active',
    },
    {
        id: 4,
        name: 'David Kiprop Koech',
        phone: '+254745678901',
        email: 'david.kiprop@email.com',
        relationship: 'Father',
        students: 2,
        status: 'active',
    },
    {
        id: 5,
        name: 'Grace Achieng Odhiambo',
        phone: '+254756789012',
        email: null,
        relationship: 'Mother',
        students: 1,
        status: 'inactive',
    },
]);
</script>

<template>
    <Head title="Guardians" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-teal-500/10"
                    >
                        <Users class="h-6 w-6 text-teal-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">
                            Guardians
                        </h1>
                        <p class="text-muted-foreground">
                            Manage parents and guardians
                        </p>
                    </div>
                </div>
                <Button as-child
                    ><Link href="/guardians/create"
                        ><Plus class="mr-2 h-4 w-4" />Add Guardian</Link
                    ></Button
                >
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    class="rounded-xl border bg-gradient-to-br from-teal-50 to-teal-100/50 p-4 dark:from-teal-950/50 dark:to-teal-900/30"
                >
                    <div class="text-sm text-muted-foreground">
                        Total Guardians
                    </div>
                    <div class="mt-1 text-3xl font-bold text-teal-600">892</div>
                </div>
                <div
                    class="rounded-xl border bg-gradient-to-br from-green-50 to-green-100/50 p-4 dark:from-green-950/50 dark:to-green-900/30"
                >
                    <div class="text-sm text-muted-foreground">Active</div>
                    <div class="mt-1 text-3xl font-bold text-green-600">
                        845
                    </div>
                </div>
                <div
                    class="rounded-xl border bg-gradient-to-br from-blue-50 to-blue-100/50 p-4 dark:from-blue-950/50 dark:to-blue-900/30"
                >
                    <div class="text-sm text-muted-foreground">With Email</div>
                    <div class="mt-1 text-3xl font-bold text-blue-600">756</div>
                </div>
            </div>

            <div class="flex flex-col gap-4 md:flex-row md:items-center">
                <div class="relative flex-1 md:max-w-sm">
                    <Search
                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input placeholder="Search guardians..." class="pl-9" />
                </div>
            </div>

            <div class="rounded-xl border bg-card">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b bg-muted/50">
                                <th
                                    class="px-4 py-3 text-left text-sm font-medium text-muted-foreground"
                                >
                                    Guardian
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-sm font-medium text-muted-foreground"
                                >
                                    Contact
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-sm font-medium text-muted-foreground"
                                >
                                    Relationship
                                </th>
                                <th
                                    class="px-4 py-3 text-center text-sm font-medium text-muted-foreground"
                                >
                                    Students
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-sm font-medium text-muted-foreground"
                                >
                                    Status
                                </th>
                                <th
                                    class="px-4 py-3 text-right text-sm font-medium text-muted-foreground"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="guardian in guardians"
                                :key="guardian.id"
                                class="border-b hover:bg-muted/50"
                            >
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-teal-500 to-teal-600 text-sm font-semibold text-white"
                                        >
                                            {{ guardian.name.charAt(0) }}
                                        </div>
                                        <span class="font-medium">{{
                                            guardian.name
                                        }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="space-y-1">
                                        <div
                                            class="flex items-center gap-2 text-sm"
                                        >
                                            <Phone
                                                class="h-3 w-3 text-muted-foreground"
                                            />{{ guardian.phone }}
                                        </div>
                                        <div
                                            v-if="guardian.email"
                                            class="flex items-center gap-2 text-sm text-muted-foreground"
                                        >
                                            <Mail class="h-3 w-3" />{{
                                                guardian.email
                                            }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <Badge variant="outline">{{
                                        guardian.relationship
                                    }}</Badge>
                                </td>
                                <td class="px-4 py-3 text-center font-medium">
                                    {{ guardian.students }}
                                </td>
                                <td class="px-4 py-3">
                                    <Badge
                                        :variant="
                                            guardian.status === 'active'
                                                ? 'default'
                                                : 'secondary'
                                        "
                                        >{{ guardian.status }}</Badge
                                    >
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child
                                            ><Button
                                                variant="ghost"
                                                size="icon"
                                                class="h-8 w-8"
                                                ><MoreHorizontal
                                                    class="h-4 w-4" /></Button
                                        ></DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuItem
                                                ><Eye
                                                    class="mr-2 h-4 w-4"
                                                />View</DropdownMenuItem
                                            >
                                            <DropdownMenuItem
                                                ><Edit
                                                    class="mr-2 h-4 w-4"
                                                />Edit</DropdownMenuItem
                                            >
                                            <DropdownMenuItem
                                                class="text-destructive"
                                                ><Trash2
                                                    class="mr-2 h-4 w-4"
                                                />Delete</DropdownMenuItem
                                            >
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
