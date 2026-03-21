<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Building2, Users, BookOpen, ClipboardList } from 'lucide-vue-next';

const props = defineProps<{
    dashboardType: string;
    teacher: any;
    department: any;
    departmentTeachers: any[];
    departmentSubjects: any[];
    totalStudents: number;
    recentAssessments: any[];
    notificationsCount: number;
}>();
</script>

<template>
    <AppLayout>
        <Head title="HoD Dashboard" />
        <div class="mx-auto max-w-[90%] py-8 space-y-8">
            <div class="bg-gradient-to-r from-violet-600 to-fuchsia-600 rounded-2xl p-8 text-white shadow-xl">
                <h1 class="text-3xl font-black">{{ department?.name ?? 'Department' }} Overview 🎓</h1>
                <p class="mt-2 text-violet-100 text-lg">Head of Department Dashboard — {{ teacher?.first_name }} {{ teacher?.last_name }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white rounded-2xl shadow-sm border p-6">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-xl bg-violet-50 flex items-center justify-center"><Building2 class="h-6 w-6 text-violet-600" /></div>
                        <div><p class="text-sm text-gray-500 font-medium">Department</p><p class="text-lg font-black text-gray-900">{{ department?.name ?? 'N/A' }}</p></div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border p-6">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-xl bg-blue-50 flex items-center justify-center"><Users class="h-6 w-6 text-blue-600" /></div>
                        <div><p class="text-sm text-gray-500 font-medium">Teachers</p><p class="text-2xl font-black text-gray-900">{{ departmentTeachers?.length ?? 0 }}</p></div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border p-6">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-xl bg-green-50 flex items-center justify-center"><BookOpen class="h-6 w-6 text-green-600" /></div>
                        <div><p class="text-sm text-gray-500 font-medium">Subjects</p><p class="text-2xl font-black text-gray-900">{{ departmentSubjects?.length ?? 0 }}</p></div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border p-6">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-xl bg-amber-50 flex items-center justify-center"><ClipboardList class="h-6 w-6 text-amber-600" /></div>
                        <div><p class="text-sm text-gray-500 font-medium">Students Reached</p><p class="text-2xl font-black text-gray-900">{{ totalStudents }}</p></div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white rounded-2xl shadow-sm border p-6">
                    <h2 class="text-xl font-black text-gray-900 mb-4">Department Teachers</h2>
                    <div v-if="departmentTeachers?.length" class="space-y-3">
                        <div v-for="t in departmentTeachers" :key="t.id" class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
                            <div class="h-10 w-10 rounded-full bg-violet-100 flex items-center justify-center text-violet-600 font-bold text-sm">
                                {{ t.first_name?.[0] }}{{ t.last_name?.[0] }}
                            </div>
                            <div>
                                <p class="font-bold text-gray-900 text-sm">{{ t.first_name }} {{ t.last_name }}</p>
                                <p class="text-xs text-gray-500">{{ t.email }}</p>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-gray-400 text-center py-8">No teachers in this department.</p>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border p-6">
                    <h2 class="text-xl font-black text-gray-900 mb-4">Recent Assessments</h2>
                    <div v-if="recentAssessments?.length" class="space-y-3">
                        <div v-for="a in recentAssessments" :key="a.id" class="p-3 bg-gray-50 rounded-xl">
                            <p class="font-bold text-gray-900">{{ a.title }}</p>
                            <p class="text-xs text-gray-500">{{ a.subject?.name }} · {{ a.class?.name }} · {{ a.assessment_type?.name }}</p>
                        </div>
                    </div>
                    <p v-else class="text-gray-400 text-center py-8">No recent assessments.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
