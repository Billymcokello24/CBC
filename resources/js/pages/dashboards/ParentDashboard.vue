<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import StatCard from "@/components/dashboard/StatCard.vue";
import { 
    Users, 
    BookOpen,
    ClipboardList,
    TrendingUp,
    Heart,
    Zap
} from 'lucide-vue-next';

interface Props {
    guardian: any;
    children: any[];
    childrenResults: any[];
    notificationsCount: number;
}

const props = defineProps<Props>();

const stats = [
    { title: 'My Children', value: props.children.length.toString(), icon: Users, color: 'blue' },
    { title: 'Recent Results', value: props.childrenResults.length.toString(), icon: TrendingUp, color: 'emerald' },
    { title: 'Notifications', value: props.notificationsCount.toString(), icon: Zap, color: 'amber' },
    { title: 'Health Recs', value: '0', icon: Heart, color: 'rose' },
];
</script>

<template>
    <Head title="Parent Dashboard" />

    <AppLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">Hello, {{ guardian?.full_name || 'Guardian' }}</h2>
                    <p class="text-slate-500 text-sm">Parent/Guardian Portal</p>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <StatCard 
                    v-for="stat in stats" 
                    :key="stat.title"
                    :title="stat.title"
                    :value="stat.value"
                    :icon="stat.icon"
                    :color="stat.color"
                />
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- My Children -->
                <div class="lg:col-span-1 space-y-4">
                     <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2 mb-2">
                        <Users class="w-5 h-5 text-blue-500" />
                        My Children
                     </h3>
                     <div v-for="child in children" :key="child.id" 
                          class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 hover:border-blue-200 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center overflow-hidden">
                                <img v-if="child.photo" :src="child.photo" alt="" class="w-full h-full object-cover">
                                <span v-else class="text-lg font-bold text-slate-400">
                                    {{ child.first_name[0] }}{{ child.last_name[0] }}
                                </span>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800">{{ child.first_name }} {{ child.last_name }}</h4>
                                <p class="text-xs text-slate-500">{{ child.admission_number }}</p>
                                <p class="text-xs font-medium text-blue-600 mt-1">{{ child.current_class?.name || 'Assigned Class' }}</p>
                            </div>
                        </div>
                        <div class="mt-6 flex gap-2">
                            <button class="flex-1 py-2 px-3 bg-blue-50 text-blue-600 rounded-xl text-xs font-bold hover:bg-blue-100 transition-colors">
                                Profile
                            </button>
                            <button class="flex-1 py-2 px-3 bg-slate-50 text-slate-600 rounded-xl text-xs font-bold hover:bg-slate-100 transition-colors">
                                Results
                            </button>
                        </div>
                     </div>
                </div>

                <!-- Recent results/performance -->
                <div class="lg:col-span-2 bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                    <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <TrendingUp class="w-5 h-5 text-emerald-500" />
                        Recent Performance
                    </h3>
                    
                    <div v-if="childrenResults.length > 0" class="space-y-4">
                         <div v-for="result in childrenResults" :key="result.id" 
                              class="flex items-center justify-between p-4 rounded-2xl bg-slate-50 border border-slate-100">
                            <div>
                                <h4 class="text-sm font-bold text-slate-800">{{ result.assessment?.subject?.name }}</h4>
                                <p class="text-xs text-slate-500">{{ result.assessment?.assessment_type?.name }} • {{ result.student?.first_name }}</p>
                            </div>
                            <div class="text-right">
                                <span class="text-lg font-bold text-emerald-600">{{ result.score || result.percentage }}%</span>
                                <p class="text-[10px] text-slate-400 uppercase font-bold">{{ result.grade || 'GRADED' }}</p>
                            </div>
                         </div>
                    </div>
                    <div v-else class="text-center py-12">
                        <p class="text-slate-500 text-sm">No recent results found.</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
