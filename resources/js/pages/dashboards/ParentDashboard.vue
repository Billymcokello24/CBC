<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { GraduationCap, ClipboardList, DollarSign, Heart } from 'lucide-vue-next';

const props = defineProps<{
    dashboardType: string;
    guardian: any;
    children: any[];
    childrenResults: any[];
    notificationsCount: number;
}>();
</script>

<template>
    <AppLayout>
        <Head title="Parent Dashboard" />
        <div class="mx-auto max-w-[90%] py-8 space-y-8">
            <div class="bg-gradient-to-r from-sky-600 to-blue-600 rounded-2xl p-8 text-white shadow-xl">
                <h1 class="text-3xl font-black">Welcome, {{ guardian?.first_name ?? 'Parent' }} 👨‍👧‍👦</h1>
                <p class="mt-2 text-sky-100 text-lg">Stay updated on your children's progress.</p>
            </div>

            <!-- Children Cards -->
            <div>
                <h2 class="text-xl font-black text-gray-900 mb-4">My Children</h2>
                <div v-if="children?.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="child in children" :key="child.id" class="bg-white rounded-2xl shadow-sm border p-6 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="h-14 w-14 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white font-black text-lg">
                                {{ child.first_name?.[0] }}{{ child.last_name?.[0] }}
                            </div>
                            <div>
                                <h3 class="font-black text-gray-900 text-lg">{{ child.first_name }} {{ child.last_name }}</h3>
                                <p class="text-sm text-gray-500">{{ child.admission_number }}</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="px-3 py-1 bg-indigo-50 text-indigo-700 rounded-full font-bold text-xs">{{ child.current_class?.name ?? 'Not Assigned' }}</span>
                            <span :class="child.gender === 'male' ? 'text-blue-600' : 'text-pink-600'" class="font-bold text-xs uppercase">{{ child.gender }}</span>
                        </div>
                    </div>
                </div>
                <p v-else class="text-gray-400 text-center py-8 bg-white rounded-2xl border">No children linked to your account.</p>
            </div>

            <!-- Recent Results -->
            <div class="bg-white rounded-2xl shadow-sm border p-6">
                <h2 class="text-xl font-black text-gray-900 mb-4">Recent Results</h2>
                <div v-if="childrenResults?.length" class="space-y-3">
                    <div v-for="result in childrenResults" :key="result.id" class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                        <div>
                            <p class="font-bold text-gray-900">{{ result.assessment?.subject?.name }}</p>
                            <p class="text-xs text-gray-500">{{ result.assessment?.title }} · {{ result.assessment?.assessment_type?.name }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-black" :class="(result.percentage ?? 0) >= 50 ? 'text-green-600' : 'text-red-600'">
                                {{ result.marks_obtained ?? '-' }}
                            </p>
                            <p class="text-xs text-gray-500">out of {{ result.assessment?.total_marks }}</p>
                        </div>
                    </div>
                </div>
                <p v-else class="text-gray-400 text-center py-8">No results available yet.</p>
            </div>
        </div>
    </AppLayout>
</template>
