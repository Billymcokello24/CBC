<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ClipboardList, Search, Filter, FileText, CheckCircle2, AlertCircle, LayoutGrid, MoreHorizontal, Eye } from 'lucide-vue-next';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    assessments: {
        data: Array<any>;
        links: Array<any>;
        meta: any;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Assessments', href: '/assessments' },
    { title: 'Grading', href: '/assessments/grading' },
];

const searchQuery = ref('');

const getStatusColor = (status: string) => {
    switch (status) {
        case 'published': return 'bg-blue-100 text-blue-700';
        case 'in_progress': return 'bg-amber-100 text-amber-700';
        case 'completed': return 'bg-green-100 text-green-700';
        default: return 'bg-gray-100 text-gray-700';
    }
};
</script>

<template>
    <Head title="Grading" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 max-w-7xl mx-auto">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-500/10 border border-purple-200">
                        <FileText class="h-6 w-6 text-purple-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Grading</h1>
                        <p class="text-muted-foreground">Select an assessment to enter or review student marks</p>
                    </div>
                </div>
            </div>

            <Card class="border-purple-100 shadow-sm">
                <CardContent class="p-4">
                    <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                        <div class="relative flex-1 max-w-md w-full">
                            <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input v-model="searchQuery" placeholder="Search assessments..." class="pl-9 h-10 border-purple-100" />
                        </div>
                        <Button variant="outline" size="sm" class="h-10 border-purple-100"><Filter class="mr-2 h-4 w-4" />Filter</Button>
                    </div>
                </CardContent>
            </Card>

            <div v-if="assessments.data.length === 0" class="flex flex-col items-center justify-center py-20 bg-muted/20 rounded-3xl border-2 border-dashed border-muted-foreground/10">
                <div class="h-20 w-20 bg-purple-50 rounded-full flex items-center justify-center mb-4">
                    <LayoutGrid class="h-10 w-10 text-purple-400" />
                </div>
                <h3 class="text-lg font-bold text-gray-900">No assessments to grade</h3>
                <p class="text-muted-foreground max-w-sm text-center mt-2">Publish an assessment or mark it as in-progress to start grading.</p>
                <Button variant="outline" as-child class="mt-6 border-purple-200 text-purple-700 hover:bg-purple-50 font-bold">
                    <Link href="/assessments">View All Assessments</Link>
                </Button>
            </div>

            <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div v-for="assessment in assessments.data" :key="assessment.id" 
                    class="group relative bg-white rounded-3xl border border-gray-100 p-6 shadow-sm hover:shadow-2xl hover:-translate-y-2 transition-all duration-500 overflow-hidden"
                >
                    <div class="relative">
                        <div class="flex items-start justify-between mb-4">
                            <div class="h-12 w-12 bg-purple-100 rounded-2xl flex items-center justify-center text-purple-600">
                                <FileText class="h-6 w-6" />
                            </div>
                            <Badge variant="outline" :class="[getStatusColor(assessment.status), 'border-0 font-bold text-[10px] uppercase tracking-wider']">
                                {{ assessment.status }}
                            </Badge>
                        </div>

                        <h3 class="text-lg font-black text-gray-900 group-hover:text-purple-600 transition-colors capitalize">{{ assessment.title }}</h3>
                        <div class="flex flex-wrap items-center gap-2 mt-2">
                            <Badge variant="outline" class="bg-indigo-50 text-indigo-600 border-0 font-bold text-[10px] uppercase">{{ assessment.class?.name }}</Badge>
                            <Badge variant="outline" class="bg-amber-50 text-amber-600 border-0 font-bold text-[10px] uppercase">{{ assessment.subject?.name }}</Badge>
                        </div>
                        
                        <div class="mt-6 space-y-3">
                            <div class="flex items-center justify-between text-xs font-bold uppercase tracking-widest text-muted-foreground">
                                <span>Grading Progress</span>
                                <span class="text-purple-600">--%</span>
                            </div>
                            <div class="h-2 w-full bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-purple-500 rounded-full" style="width: 0%"></div>
                            </div>
                        </div>

                        <div class="flex flex-col w-full gap-2 mt-6 pt-6 border-t border-dashed">
                             <Button class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl shadow-lg shadow-purple-100 h-10" as-child>
                                <Link :href="`/assessments/${assessment.id}/grading`">
                                    Grade Students
                                </Link>
                            </Button>
                             <Button variant="outline" class="w-full text-gray-600 border-gray-100 hover:bg-gray-50 font-bold rounded-xl h-10" as-child>
                                <Link :href="`/assessments/${assessment.id}`">
                                    <Eye class="mr-2 h-4 w-4" />View Details
                                </Link>
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination Placeholder -->
            <div class="flex items-center justify-center py-4 border-t mt-4">
                <p class="text-xs text-muted-foreground font-medium italic">Showing {{ assessments.data.length }} assessments of {{ assessments.meta?.total ?? 0 }} total</p>
            </div>
        </div>
    </AppLayout>
</template>
