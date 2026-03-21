<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { GraduationCap, ClipboardList, CalendarCheck, Clock } from 'lucide-vue-next';

const props = defineProps<{
    dashboardType: string;
    student: any;
    myResults: any[];
    upcomingAssessments: any[];
    notificationsCount: number;
}>();
</script>

<template>
    <AppLayout>
        <Head title="Student Dashboard" />
        <div class="mx-auto max-w-[90%] py-8 space-y-8">
            <div class="bg-gradient-to-r from-cyan-600 to-blue-600 rounded-2xl p-8 text-white shadow-xl">
                <div class="flex items-center gap-6">
                    <div class="h-20 w-20 rounded-full bg-white/20 backdrop-blur flex items-center justify-center text-3xl font-black">
                        {{ student?.first_name?.[0] }}{{ student?.last_name?.[0] }}
                    </div>
                    <div>
                        <h1 class="text-3xl font-black">Hi, {{ student?.first_name ?? 'Student' }}! 📚</h1>
                        <p class="mt-1 text-cyan-100 text-lg">{{ student?.current_class?.name ?? 'No Class' }} · {{ student?.admission_number }}</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- My Results -->
                <div class="bg-white rounded-2xl shadow-sm border p-6">
                    <h2 class="text-xl font-black text-gray-900 mb-4">📊 My Results</h2>
                    <div v-if="myResults?.length" class="space-y-3">
                        <div v-for="result in myResults" :key="result.id" class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                            <div>
                                <p class="font-bold text-gray-900">{{ result.assessment?.subject?.name }}</p>
                                <p class="text-xs text-gray-500">{{ result.assessment?.title }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-black" :class="(result.percentage ?? 0) >= 50 ? 'text-green-600' : 'text-red-600'">
                                    {{ result.marks_obtained ?? '-' }}<span class="text-sm text-gray-400">/{{ result.assessment?.total_marks }}</span>
                                </p>
                                <p v-if="result.grade_level" class="text-xs font-bold text-indigo-600">{{ result.grade_level }}</p>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-gray-400 text-center py-8">No results yet. Keep studying! 💪</p>
                </div>

                <!-- Upcoming Assessments -->
                <div class="bg-white rounded-2xl shadow-sm border p-6">
                    <h2 class="text-xl font-black text-gray-900 mb-4">📅 Upcoming Assessments</h2>
                    <div v-if="upcomingAssessments?.length" class="space-y-3">
                        <div v-for="assessment in upcomingAssessments" :key="assessment.id" class="p-4 bg-gray-50 rounded-xl">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-bold text-gray-900">{{ assessment.title }}</p>
                                    <p class="text-xs text-gray-500">{{ assessment.subject?.name }} · {{ assessment.assessment_type?.name }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-amber-600">{{ new Date(assessment.assessment_date).toLocaleDateString() }}</p>
                                    <p class="text-xs text-gray-500">{{ assessment.total_marks }} marks</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-gray-400 text-center py-8">No upcoming assessments. Enjoy! 🎉</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
