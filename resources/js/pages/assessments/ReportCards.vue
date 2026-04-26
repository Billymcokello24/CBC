<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    FileText,
    Search,
    Filter,
    Download,
    Printer,
    Send,
    CheckCircle2,
    AlertCircle,
    LayoutGrid,
} from 'lucide-vue-next';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
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
    CardHeader,
    CardTitle,
    CardDescription,
} from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    classes: Array<any>;
    students: {
        data: Array<any>;
        links: Array<any>;
        meta: any;
    };
    activeYear: any;
    activeTerm: any;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Assessments', href: '/assessments' },
    { title: 'Report Cards', href: '/assessments/report-cards' },
];

const selectedClass = ref('');
const status = ref('all');
</script>

<template>
    <Head title="Report Cards" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex h-full max-w-6xl flex-1 flex-col gap-6 p-6">
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl border border-purple-200 bg-purple-500/10"
                    >
                        <FileText class="h-6 w-6 text-purple-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">
                            Report Cards
                        </h1>
                        <p class="text-muted-foreground">
                            Generate and manage student termly reports for
                            {{ activeTerm?.name || 'Current Term' }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline"
                        ><Printer class="mr-2 h-4 w-4" />Bulk Print</Button
                    >
                    <Button
                        class="bg-purple-600 shadow-lg shadow-purple-200 hover:bg-purple-700"
                        ><Send class="mr-2 h-4 w-4" />Send to Parents</Button
                    >
                </div>
            </div>

            <Card class="border-purple-100 shadow-sm">
                <CardContent class="p-4">
                    <div
                        class="flex flex-col items-center justify-between gap-4 md:flex-row"
                    >
                        <div
                            class="flex w-full flex-1 items-center gap-4 md:w-auto"
                        >
                            <div class="w-full md:w-64">
                                <Select v-model="selectedClass">
                                    <SelectTrigger
                                        class="h-10 border-purple-100 bg-purple-50/30"
                                    >
                                        <SelectValue
                                            placeholder="Select Class"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="cls in classes"
                                            :key="cls.id"
                                            :value="cls.id.toString()"
                                        >
                                            {{ cls.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div
                                class="relative hidden max-w-xs flex-1 sm:block"
                            >
                                <Search
                                    class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                                />
                                <Input
                                    placeholder="Search student..."
                                    class="h-10 border-purple-100 pl-9"
                                />
                            </div>
                        </div>
                        <Tabs v-model="status" class="w-full md:w-auto">
                            <TabsList
                                class="grid h-10 w-full grid-cols-3 bg-purple-50/50 md:w-[400px]"
                            >
                                <TabsTrigger
                                    value="all"
                                    class="data-[state=active]:bg-white data-[state=active]:text-purple-700"
                                    >All</TabsTrigger
                                >
                                <TabsTrigger
                                    value="pending"
                                    class="data-[state=active]:bg-white data-[state=active]:text-purple-700"
                                    >Pending</TabsTrigger
                                >
                                <TabsTrigger
                                    value="published"
                                    class="data-[state=active]:bg-white data-[state=active]:text-purple-700"
                                    >Published</TabsTrigger
                                >
                            </TabsList>
                        </Tabs>
                    </div>
                </CardContent>
            </Card>

            <div
                v-if="!selectedClass"
                class="flex flex-col items-center justify-center rounded-3xl border-2 border-dashed border-muted-foreground/10 bg-muted/20 py-20"
            >
                <div
                    class="mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-indigo-50"
                >
                    <LayoutGrid class="h-10 w-10 text-indigo-400" />
                </div>
                <h3 class="text-lg font-bold text-gray-900">Select a Class</h3>
                <p class="mt-2 max-w-sm text-center text-muted-foreground">
                    Choose a class from the dropdown above to view and manage
                    student report cards for this term.
                </p>
            </div>

            <div
                v-else
                class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
            >
                <div
                    v-for="student in students.data"
                    :key="student.id"
                    class="group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
                >
                    <div class="absolute top-0 right-0 p-3">
                        <Badge
                            variant="outline"
                            class="border-0 bg-gray-50 text-xs font-bold tracking-wider text-gray-500 uppercase"
                            >Draft</Badge
                        >
                    </div>
                    <div class="flex flex-col items-center text-center">
                        <div
                            class="mb-3 flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-50"
                        >
                            <span class="text-xl font-bold text-indigo-600"
                                >{{ student.first_name[0]
                                }}{{ student.last_name[0] }}</span
                            >
                        </div>
                        <h4
                            class="line-clamp-1 text-lg font-bold text-gray-900"
                        >
                            {{ student.first_name }} {{ student.last_name }}
                        </h4>
                        <p
                            class="mb-4 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                        >
                            {{ student.admission_number }}
                        </p>

                        <div class="mb-5 grid w-full grid-cols-2 gap-3">
                            <div class="rounded-xl bg-gray-50 p-2">
                                <p
                                    class="text-xs font-bold text-muted-foreground uppercase"
                                >
                                    Average
                                </p>
                                <p class="font-bold text-indigo-600">74%</p>
                            </div>
                            <div class="rounded-xl bg-gray-50 p-2">
                                <p
                                    class="text-xs font-bold text-muted-foreground uppercase"
                                >
                                    Grade
                                </p>
                                <p class="font-bold text-purple-600">ME</p>
                            </div>
                        </div>

                        <div
                            class="flex w-full flex-col gap-2 border-t border-dashed pt-4"
                        >
                            <Button
                                variant="outline"
                                class="h-10 w-full rounded-xl border-indigo-100 font-bold text-indigo-600 hover:bg-indigo-50"
                                as-child
                            >
                                <Link
                                    :href="`/assessments/report-cards/${student.id}`"
                                >
                                    <FileText class="mr-2 h-4 w-4" />View Report
                                </Link>
                            </Button>
                            <Button
                                class="h-10 w-full rounded-xl bg-indigo-600 font-bold text-white shadow-lg shadow-indigo-100 hover:bg-indigo-700"
                            >
                                Generate
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <Card class="border-amber-100 bg-amber-50/30 shadow-sm">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-base">
                            <AlertCircle class="h-5 w-5 text-amber-600" />
                            Missing Submissions
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-sm text-amber-700">
                            6 students in this class have incomplete assessment
                            marks. Ensure all grades are entered before
                            generating report cards.
                        </p>
                        <Button
                            variant="link"
                            class="mt-2 h-auto p-0 font-bold text-amber-800"
                            >View incomplete marks →</Button
                        >
                    </CardContent>
                </Card>
                <Card class="border-green-100 bg-green-50/30 shadow-sm">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-base">
                            <CheckCircle2 class="h-5 w-5 text-green-600" />
                            Ready to Publish
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-sm text-green-700">
                            12 student report cards have been generated and are
                            ready for approval and publication.
                        </p>
                        <Button
                            variant="link"
                            class="mt-2 h-auto p-0 font-bold text-green-800"
                            >Approve for publication →</Button
                        >
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
