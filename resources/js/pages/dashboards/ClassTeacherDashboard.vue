<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Users, ClipboardList, CalendarCheck, UserCheck, UserX } from 'lucide-vue-next';

const props = defineProps<{
    dashboardType: string;
    teacher: any;
    myClass: any;
    students: any[];
    totalStudents: number;
    boysCount: number;
    girlsCount: number;
    attendanceRate: number;
    recentAssessments: any[];
    notificationsCount: number;
}>();
</script>

<template>
    <AppLayout>
        <Head title="Class Teacher Dashboard" />
        <div class="mx-auto max-w-[90%] py-8 space-y-8">
            <!-- Welcome Header -->
            <div class="bg-gradient-to-r from-emerald-600 to-teal-600 rounded-2xl p-8 text-white shadow-xl">
                <h1 class="text-3xl font-black">{{ myClass?.name ?? 'My Class' }} Dashboard 🏫</h1>
                <p class="mt-2 text-emerald-100 text-lg">Welcome, {{ teacher?.first_name }}. Manage your class here.</p>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white rounded-2xl shadow-sm border p-6">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-xl bg-blue-50 flex items-center justify-center">
                            <Users class="h-6 w-6 text-blue-600" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Total Students</p>
                            <p class="text-2xl font-black text-gray-900">{{ totalStudents }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border p-6">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-xl bg-sky-50 flex items-center justify-center">
                            <UserCheck class="h-6 w-6 text-sky-600" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Boys</p>
                            <p class="text-2xl font-black text-gray-900">{{ boysCount }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border p-6">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-xl bg-pink-50 flex items-center justify-center">
                            <UserX class="h-6 w-6 text-pink-600" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Girls</p>
                            <p class="text-2xl font-black text-gray-900">{{ girlsCount }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border p-6">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-xl bg-green-50 flex items-center justify-center">
                            <CalendarCheck class="h-6 w-6 text-green-600" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Attendance Rate</p>
                            <p class="text-2xl font-black text-gray-900">{{ attendanceRate }}%</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Students List -->
                <div class="bg-white rounded-2xl shadow-sm border p-6">
                    <h2 class="text-xl font-black text-gray-900 mb-4">Class Students</h2>
                    <div v-if="students?.length" class="space-y-2 max-h-[400px] overflow-y-auto">
                        <div v-for="student in students" :key="student.id" class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
                            <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-sm">
                                {{ student.first_name?.[0] }}{{ student.last_name?.[0] }}
                            </div>
                            <div class="flex-1">
                                <p class="font-bold text-gray-900 text-sm">{{ student.first_name }} {{ student.last_name }}</p>
                                <p class="text-xs text-gray-500">{{ student.admission_number }}</p>
                            </div>
                            <span :class="student.gender === 'male' ? 'bg-blue-100 text-blue-700' : 'bg-pink-100 text-pink-700'" class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase">
                                {{ student.gender }}
                            </span>
                        </div>
                    </div>
                    <p v-else class="text-gray-400 text-center py-8">No students found in this class.</p>
                </div>

                <!-- Recent Assessments -->
                <div class="bg-white rounded-2xl shadow-sm border p-6">
                    <h2 class="text-xl font-black text-gray-900 mb-4">Recent Assessments</h2>
                    <div v-if="recentAssessments?.length" class="space-y-3">
                        <div v-for="assessment in recentAssessments" :key="assessment.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                            <div>
                                <p class="font-bold text-gray-900">{{ assessment.title }}</p>
                                <p class="text-xs text-gray-500">{{ assessment.subject?.name }} · {{ assessment.assessment_type?.name }}</p>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-gray-400 text-center py-8">No recent assessments.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
