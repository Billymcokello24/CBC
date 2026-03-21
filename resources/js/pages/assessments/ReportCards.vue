<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { FileText, Search, Filter, Download, Printer, Send, CheckCircle2, AlertCircle, LayoutGrid } from 'lucide-vue-next';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
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
        <div class="flex h-full flex-1 flex-col gap-6 p-6 max-w-6xl mx-auto">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-500/10 border border-purple-200">
                        <FileText class="h-6 w-6 text-purple-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Report Cards</h1>
                        <p class="text-muted-foreground">Generate and manage student termly reports for {{ activeTerm?.name || 'Current Term' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline"><Printer class="mr-2 h-4 w-4" />Bulk Print</Button>
                    <Button class="bg-purple-600 hover:bg-purple-700 shadow-lg shadow-purple-200"><Send class="mr-2 h-4 w-4" />Send to Parents</Button>
                </div>
            </div>

            <Card class="border-purple-100 shadow-sm">
                <CardContent class="p-4">
                    <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                        <div class="flex flex-1 items-center gap-4 w-full md:w-auto">
                            <div class="w-full md:w-64">
                                <Select v-model="selectedClass">
                                    <SelectTrigger class="h-10 border-purple-100 bg-purple-50/30">
                                        <SelectValue placeholder="Select Class" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="cls in classes" :key="cls.id" :value="cls.id.toString()">
                                            {{ cls.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="relative flex-1 max-w-xs hidden sm:block">
                                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                <Input placeholder="Search student..." class="pl-9 h-10 border-purple-100" />
                            </div>
                        </div>
                        <Tabs v-model="status" class="w-full md:w-auto">
                            <TabsList class="grid grid-cols-3 w-full md:w-[400px] h-10 bg-purple-50/50">
                                <TabsTrigger value="all" class="data-[state=active]:bg-white data-[state=active]:text-purple-700">All</TabsTrigger>
                                <TabsTrigger value="pending" class="data-[state=active]:bg-white data-[state=active]:text-purple-700">Pending</TabsTrigger>
                                <TabsTrigger value="published" class="data-[state=active]:bg-white data-[state=active]:text-purple-700">Published</TabsTrigger>
                            </TabsList>
                        </Tabs>
                    </div>
                </CardContent>
            </Card>

            <div v-if="!selectedClass" class="flex flex-col items-center justify-center py-20 bg-muted/20 rounded-3xl border-2 border-dashed border-muted-foreground/10">
                <div class="h-20 w-20 bg-indigo-50 rounded-full flex items-center justify-center mb-4">
                    <LayoutGrid class="h-10 w-10 text-indigo-400" />
                </div>
                <h3 class="text-lg font-bold text-gray-900">Select a Class</h3>
                <p class="text-muted-foreground max-w-sm text-center mt-2">Choose a class from the dropdown above to view and manage student report cards for this term.</p>
            </div>

            <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <div v-for="student in students.data" :key="student.id" class="group relative bg-white rounded-2xl border border-gray-100 p-5 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                    <div class="absolute top-0 right-0 p-3">
                        <Badge variant="outline" class="bg-gray-50 text-gray-500 font-bold text-[10px] uppercase tracking-wider border-0">Draft</Badge>
                    </div>
                    <div class="flex flex-col items-center text-center">
                        <div class="h-16 w-16 bg-indigo-50 rounded-2xl flex items-center justify-center mb-3">
                            <span class="text-xl font-black text-indigo-600">{{ student.first_name[0] }}{{ student.last_name[0] }}</span>
                        </div>
                        <h4 class="font-black text-gray-900 text-lg line-clamp-1">{{ student.first_name }} {{ student.last_name }}</h4>
                        <p class="text-xs text-muted-foreground font-bold tracking-tight mb-4 uppercase">{{ student.admission_number }}</p>
                        
                        <div class="grid grid-cols-2 w-full gap-3 mb-5">
                            <div class="bg-gray-50 rounded-xl p-2">
                                <p class="text-[10px] text-muted-foreground font-bold uppercase">Average</p>
                                <p class="font-black text-indigo-600">74%</p>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-2">
                                <p class="text-[10px] text-muted-foreground font-bold uppercase">Grade</p>
                                <p class="font-black text-purple-600">ME</p>
                            </div>
                        </div>

                        <div class="flex flex-col w-full gap-2 pt-4 border-t border-dashed">
                             <Button variant="outline" class="w-full text-indigo-600 border-indigo-100 hover:bg-indigo-50 font-bold rounded-xl h-10" as-child>
                                <Link :href="`/assessments/report-cards/${student.id}`">
                                    <FileText class="mr-2 h-4 w-4" />View Report
                                </Link>
                            </Button>
                            <Button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-100 h-10">
                                Generate
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <Card class="border-amber-100 shadow-sm bg-amber-50/30">
                    <CardHeader>
                        <CardTitle class="text-base flex items-center gap-2">
                            <AlertCircle class="h-5 w-5 text-amber-600" />
                            Missing Submissions
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-sm text-amber-700">6 students in this class have incomplete assessment marks. Ensure all grades are entered before generating report cards.</p>
                        <Button variant="link" class="p-0 h-auto text-amber-800 font-bold mt-2">View incomplete marks →</Button>
                    </CardContent>
                </Card>
                <Card class="border-green-100 shadow-sm bg-green-50/30">
                    <CardHeader>
                        <CardTitle class="text-base flex items-center gap-2">
                            <CheckCircle2 class="h-5 w-5 text-green-600" />
                            Ready to Publish
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-sm text-green-700">12 student report cards have been generated and are ready for approval and publication.</p>
                        <Button variant="link" class="p-0 h-auto text-green-800 font-bold mt-2">Approve for publication →</Button>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
