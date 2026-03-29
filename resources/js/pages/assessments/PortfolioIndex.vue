<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { 
    Folder, 
    Search, 
    ChevronRight, 
    User, 
    GraduationCap,
    Plus,
    Filter
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    students: {
        data: Array<any>;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Assessments', href: '/assessments' },
    { title: 'Portfolio Management', href: '#' },
];

const searchQuery = ref('');

const filteredStudents = computed(() => {
    return props.students.data.filter(student => 
        (student.first_name + ' ' + student.last_name).toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        student.admission_number.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});
</script>

<template>
    <Head title="Learner Portfolios" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-black tracking-tight text-slate-900 italic uppercase">Learner Portfolios</h1>
                    <p class="text-slate-500 font-bold mt-1">Manage and view collection of student evidence and achievements.</p>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
                <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                    <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                        <div class="relative w-full md:w-96">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
                            <Input 
                                v-model="searchQuery"
                                placeholder="Search by name or admission number..." 
                                class="pl-10 h-11 bg-white border-slate-200 rounded-2xl focus:ring-indigo-500/20"
                            />
                        </div>
                        <div class="flex items-center gap-2 w-full md:w-auto">
                            <Button variant="outline" class="h-11 rounded-2xl font-bold border-slate-200 gap-2 w-full md:w-auto">
                                <Filter class="h-4 w-4" /> Filter By Class
                            </Button>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50">
                                <th class="px-6 py-4 text-[10px] font-black uppercase text-slate-400 tracking-widest pl-12">Learner</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase text-slate-400 tracking-widest text-center">Class / Grade</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase text-slate-400 tracking-widest text-center">Evidence Items</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase text-slate-400 tracking-widest text-center">Last Updated</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase text-slate-400 tracking-widest text-right pr-12">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="student in filteredStudents" :key="student.id" class="group hover:bg-indigo-50/30 transition-all cursor-pointer">
                                <td class="px-6 py-5 pl-12">
                                    <div class="flex items-center gap-4">
                                        <div class="h-12 w-12 rounded-2xl bg-slate-100 flex items-center justify-center border-2 border-white shadow-sm overflow-hidden group-hover:border-indigo-200 transition-colors">
                                            <img v-if="student.photo_url" :src="student.photo_url" class="h-full w-full object-cover" />
                                            <User v-else class="h-6 w-6 text-slate-400" />
                                        </div>
                                        <div>
                                            <p class="font-black text-slate-900 italic tracking-tight uppercase">{{ student.first_name }} {{ student.last_name }}</p>
                                            <p class="text-xs font-bold text-slate-400 tracking-widest uppercase">{{ student.admission_number }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100">
                                        <GraduationCap class="h-3.5 w-3.5" />
                                        <span class="text-xs font-black uppercase tracking-wider">{{ student.current_class?.name || 'N/A' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <div class="inline-flex flex-col items-center">
                                         <span class="font-black text-slate-900 text-sm italic">{{ student.portfolio_items_count || 0 }}</span>
                                         <span class="text-[9px] font-black text-slate-400 uppercase tracking-tighter mt-0.5 whitespace-nowrap">Items Logged</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span class="text-xs font-bold text-slate-500">
                                        {{ student.portfolio?.updated_at ? new Date(student.portfolio.updated_at).toLocaleDateString() : 'Never' }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-right pr-12">
                                    <Button as-child variant="ghost" class="h-10 w-10 p-0 rounded-xl hover:bg-indigo-100 hover:text-indigo-600 transition-colors">
                                        <Link :href="'/assessments/portfolio/' + student.id">
                                            <ChevronRight class="h-5 w-5" />
                                        </Link>
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
