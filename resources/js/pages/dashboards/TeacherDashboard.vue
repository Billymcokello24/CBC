<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import StatCard from "@/components/dashboard/StatCard.vue";
import { 
    Users, 
    BookOpen, 
    Calendar, 
    ClipboardList,
    Clock,
    GraduationCap,
    TrendingUp,
    CheckCircle,
    AlertCircle,
    ArrowRight
} from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

interface Props {
    teacher: any;
    myClasses: any[];
    mySubjects: any[];
    totalStudents: number;
    todaysTimetable: any[];
    recentAssessments: any[];
    attendanceStats: any[];
    syllabusProgress: any[];
    pendingTasks: any[];
    academicYear: string;
    notificationsCount: number;
}

const props = defineProps<Props>();

const stats = [
    { title: 'My Classes', value: props.myClasses.length.toString(), icon: Users, color: 'blue' },
    { title: 'My Subjects', value: props.mySubjects.length.toString(), icon: BookOpen, color: 'emerald' },
    { title: 'Today\'s Lessons', value: props.todaysTimetable.length.toString(), icon: Clock, color: 'amber' },
    { title: 'Active Students', value: (props.totalStudents || 0).toString(), icon: GraduationCap, color: 'indigo' },
];
</script>

<template>
    <Head title="Teacher Dashboard" />

    <AppLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">Welcome Back, {{ teacher?.first_name }}</h2>
                    <p class="text-slate-500 text-sm">Academic Year: {{ academicYear }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link href="/assessments/analytics" class="bg-blue-600 px-4 py-2 rounded-2xl text-sm font-bold text-white flex items-center gap-2 hover:bg-blue-700 transition-colors shadow-lg shadow-blue-200">
                        <TrendingUp class="w-4 h-4" />
                        Detailed Analytics
                    </Link>
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
                <!-- Timetable -->
                <div class="lg:col-span-2 bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                            <Clock class="w-5 h-5 text-blue-500" />
                            Today's Timetable
                        </h3>
                    </div>

                    <div v-if="todaysTimetable.length > 0" class="space-y-4">
                        <div v-for="slot in todaysTimetable" :key="slot.id" 
                             class="flex items-center gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-100">
                            <div class="flex-shrink-0 w-20 text-center">
                                <span class="text-sm font-bold text-blue-600 block">{{ slot.start_time }}</span>
                                <span class="text-xs text-slate-500">{{ slot.end_time }}</span>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-bold text-slate-800">{{ slot.subject }}</h4>
                                <p class="text-sm text-slate-500">{{ slot.class }} • {{ slot.room || 'No Room' }}</p>
                            </div>
                            <div class="hidden md:block">
                                <span class="px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-600">
                                    {{ slot.status || 'Scheduled' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-12">
                        <div class="bg-slate-50 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <Clock class="w-8 h-8 text-slate-300" />
                        </div>
                        <p class="text-slate-500">No lessons scheduled for today.</p>
                    </div>
                </div>

                <!-- Syllabus Coverage -->
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                    <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <CheckCircle class="w-5 h-5 text-emerald-500" />
                        Syllabus Coverage
                    </h3>
                    <div v-if="syllabusProgress.length > 0" class="space-y-6">
                        <div v-for="item in syllabusProgress" :key="item.subject + item.class">
                            <div class="flex justify-between items-end mb-2">
                                <div>
                                    <h4 class="text-sm font-bold text-slate-800">{{ item.subject }}</h4>
                                    <p class="text-xs text-slate-500">{{ item.class }}</p>
                                </div>
                                <span class="text-xs font-bold text-emerald-600">{{ item.progress }}%</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                <div class="bg-emerald-500 h-full transition-all duration-1000" 
                                     :style="{ width: item.progress + '%' }"></div>
                            </div>
                            <p class="text-[10px] text-slate-400 mt-1">{{ item.completed }} of {{ item.total }} lessons completed</p>
                        </div>
                    </div>
                    <div v-else class="text-center py-12">
                        <p class="text-slate-500 text-sm">No planning data available.</p>
                    </div>
                </div>
            </div>

            <!-- Pending Tasks & Dashboard Analytics -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Pending Tasks -->
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                    <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <AlertCircle class="w-5 h-5 text-amber-500" />
                        Pending Action Items
                    </h3>
                    <div class="space-y-4">
                        <div v-for="task in pendingTasks" :key="task.title" 
                             class="p-4 rounded-2xl bg-amber-50 border border-amber-100 flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-bold text-amber-900">{{ task.title }}</h4>
                                <p class="text-2xl font-black text-amber-600">{{ task.count }}</p>
                            </div>
                            <Link :href="task.link" class="p-2 bg-white rounded-xl shadow-sm text-amber-600 hover:bg-amber-100 transition-colors">
                                <ArrowRight class="w-5 h-5" />
                            </Link>
                        </div>
                    </div>
                </div>
                <div class="space-y-6">
                    <!-- My Classes -->
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                        <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                            <Users class="w-5 h-5 text-emerald-500" />
                            My Classes
                        </h3>
                        <div class="space-y-3">
                            <div v-for="cls in myClasses" :key="cls.id" 
                                 class="flex items-center justify-between p-3 rounded-2xl hover:bg-slate-50 transition-colors border border-transparent hover:border-slate-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 font-bold">
                                        {{ cls.name.charAt(0) }}
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-bold text-slate-800">{{ cls.name }}</h4>
                                        <p class="text-xs text-slate-500">{{ cls.learner_count }} Students</p>
                                    </div>
                                </div>
                                <span v-if="cls.is_class_teacher" class="px-2 py-0.5 rounded-full text-[10px] bg-emerald-100 text-emerald-600 font-bold">
                                    CLASS TEACHER
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- My Subjects -->
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                        <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                            <BookOpen class="w-5 h-5 text-indigo-500" />
                            My Subjects
                        </h3>
                        <div class="space-y-3">
                            <div v-for="subject in mySubjects" :key="subject.id + '-' + subject.class_name" 
                                 class="p-3 rounded-2xl bg-indigo-50/50 border border-indigo-100">
                                <h4 class="text-sm font-bold text-indigo-900">{{ subject.name }}</h4>
                                <p class="text-xs text-indigo-600/80">{{ subject.class_name }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Assessments & Attendance -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                 <!-- Recent Assessments -->
                 <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                            <ClipboardList class="w-5 h-5 text-rose-500" />
                            Recent Assessments
                        </h3>
                    </div>

                    <div v-if="recentAssessments.length > 0" class="overflow-x-auto">
                        <table class="w-full">
                             <thead>
                                <tr class="text-left text-xs font-bold text-slate-400 uppercase tracking-wider">
                                    <th class="pb-4">Assessment</th>
                                    <th class="pb-4">Class</th>
                                    <th class="pb-4">Date</th>
                                    <th class="pb-4">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="assessment in recentAssessments" :key="assessment.id">
                                    <td class="py-4">
                                        <p class="text-sm font-bold text-slate-800">{{ assessment.title || assessment.subject?.name }}</p>
                                        <p class="text-xs text-slate-500">{{ assessment.assessment_type?.name }}</p>
                                    </td>
                                    <td class="py-4 text-sm text-slate-600">{{ assessment.class?.name || assessment.school_class?.name }}</td>
                                    <td class="py-4 text-sm text-slate-600">{{ new Date(assessment.assessment_date).toLocaleDateString() }}</td>
                                    <td class="py-4">
                                        <span class="px-2 py-1 rounded-full text-[10px] font-bold"
                                              :class="assessment.status === 'graded' ? 'bg-emerald-100 text-emerald-600' : 'bg-amber-100 text-amber-600'">
                                            {{ assessment.status?.toUpperCase() || 'PENDING' }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-center py-8">
                        <p class="text-slate-500 text-sm">No recent assessments found.</p>
                    </div>
                </div>

                <!-- Attendance Trend -->
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                    <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <TrendingUp class="w-5 h-5 text-blue-500" />
                        Attendance Trend (Last 7 Days)
                    </h3>
                    
                    <div v-if="attendanceStats.length > 0" class="h-64 flex items-end justify-between gap-2 px-4">
                        <div v-for="day in attendanceStats" :key="day.date" class="flex-1 flex flex-col items-center gap-2">
                            <div class="w-full bg-slate-100 rounded-lg relative overflow-hidden flex flex-col justify-end" style="height: 180px">
                                <div class="bg-blue-500 absolute w-full transition-all duration-500" 
                                     :style="{ height: day.rate + '%' }"></div>
                                <div class="absolute inset-0 flex items-center justify-center text-[10px] font-bold" 
                                     :class="day.rate > 50 ? 'text-white' : 'text-slate-600'">
                                    {{ day.rate }}%
                                </div>
                            </div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ day.date }}</span>
                        </div>
                    </div>
                    <div v-else class="text-center py-12">
                        <p class="text-slate-500 text-sm">No attendance data available.</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
