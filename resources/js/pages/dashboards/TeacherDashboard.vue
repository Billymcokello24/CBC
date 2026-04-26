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
    ArrowRight,
    LayoutDashboard,
    UserCircle,
    BarChart3,
    BookMarked,
    ShieldCheck
} from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

interface Props {
    teacher: any;
    isClassTeacher: boolean;
    isHod: boolean;
    myClasses: any[];
    mySubjects: any[];
    totalStudents: number;
    todaysTimetable: any[];
    recentAssessments: any[];
    attendanceStats: any[];
    syllabusProgress: any[];
    pendingTasks: any[];
    classManagement?: {
        class: any;
        students: any[];
        total_students: number;
        boys_count: number;
        girls_count: number;
        attendance_rate: number;
        recent_assessments: any[];
    };
    departmentData?: {
        department: any;
        teachers: any[];
        subjects: any[];
        total_students: number;
        recent_assessments: any[];
    };
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

        <div class="space-y-6 p-2 sm:p-4 md:p-6 lg:p-0">
            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6">
                <StatCard 
                    v-for="stat in stats" 
                    :key="stat.title"
                    :title="stat.title"
                    :value="stat.value"
                    :icon="stat.icon"
                />
            </div>

            <!-- Role-Specific Sections -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" v-if="isClassTeacher || isHod">
                <!-- Class Teacher Quick View -->
                <div v-if="isClassTeacher && classManagement" class="bg-gradient-to-br from-emerald-600 to-emerald-800 rounded-3xl p-5 sm:p-6 text-white shadow-xl shadow-emerald-200">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-white/20 rounded-xl backdrop-blur-md">
                                <UserCircle class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h3 class="text-lg font-bold leading-tight">Class Management</h3>
                                <p class="text-emerald-100 text-xs">{{ classManagement.class?.name }} ({{ classManagement.total_students }} Students)</p>
                            </div>
                        </div>
                        <Link href="/classes" class="text-xs font-bold bg-white/10 hover:bg-white/20 px-3 py-1.5 rounded-xl transition-colors backdrop-blur-md flex items-center justify-center gap-2">
                            Manage Class <ArrowRight class="w-3 h-3" />
                        </Link>
                    </div>
                    
                    <div class="grid grid-cols-3 gap-2 sm:gap-4">
                        <div class="bg-white/10 rounded-2xl p-3 sm:p-4 backdrop-blur-sm border border-white/10">
                            <p class="text-[9px] sm:text-[10px] uppercase font-bold text-emerald-200 mb-1">Attendance</p>
                            <p class="text-lg sm:text-xl font-black tabular-nums">{{ classManagement.attendance_rate }}%</p>
                        </div>
                        <div class="bg-white/10 rounded-2xl p-3 sm:p-4 backdrop-blur-sm border border-white/10">
                            <p class="text-[9px] sm:text-[10px] uppercase font-bold text-emerald-200 mb-1">Boys</p>
                            <p class="text-lg sm:text-xl font-black tabular-nums">{{ classManagement.boys_count }}</p>
                        </div>
                        <div class="bg-white/10 rounded-2xl p-3 sm:p-4 backdrop-blur-sm border border-white/10">
                            <p class="text-[9px] sm:text-[10px] uppercase font-bold text-emerald-200 mb-1">Girls</p>
                            <p class="text-lg sm:text-xl font-black tabular-nums">{{ classManagement.girls_count }}</p>
                        </div>
                    </div>
                </div>

                <!-- HOD Quick View -->
                <div v-if="isHod && departmentData" class="bg-gradient-to-br from-blue-600 to-blue-800 rounded-3xl p-5 sm:p-6 text-white shadow-xl shadow-blue-200">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-white/20 rounded-xl backdrop-blur-md">
                                <LayoutDashboard class="w-6 h-6 text-white" />
                            </div>
                            <div>
                                <h3 class="text-lg font-bold leading-tight">Department Overview</h3>
                                <p class="text-blue-100 text-xs">{{ departmentData.department?.name }}</p>
                            </div>
                        </div>
                        <Link href="/curriculum" class="text-xs font-bold bg-white/10 hover:bg-white/20 px-3 py-1.5 rounded-xl transition-colors backdrop-blur-md flex items-center justify-center gap-2">
                            Full Analytics <ArrowRight class="w-3 h-3" />
                        </Link>
                    </div>
                    
                    <div class="grid grid-cols-3 gap-2 sm:gap-4">
                        <div class="bg-white/10 rounded-2xl p-3 sm:p-4 backdrop-blur-sm border border-white/10">
                            <p class="text-[9px] sm:text-[10px] uppercase font-bold text-blue-200 mb-1">Teachers</p>
                            <p class="text-lg sm:text-xl font-black tabular-nums">{{ departmentData.teachers?.length || 0 }}</p>
                        </div>
                        <div class="bg-white/10 rounded-2xl p-3 sm:p-4 backdrop-blur-sm border border-white/10">
                            <p class="text-[9px] sm:text-[10px] uppercase font-bold text-blue-200 mb-1">Subjects</p>
                            <p class="text-lg sm:text-xl font-black tabular-nums">{{ departmentData.subjects?.length || 0 }}</p>
                        </div>
                        <div class="bg-white/10 rounded-2xl p-3 sm:p-4 backdrop-blur-sm border border-white/10">
                            <p class="text-[9px] sm:text-[10px] uppercase font-bold text-blue-200 mb-1">Learners</p>
                            <p class="text-lg sm:text-xl font-black tabular-nums">{{ departmentData.total_students }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Timetable -->
                <div class="lg:col-span-2 bg-white rounded-3xl shadow-sm border border-slate-100 p-5 sm:p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                            <Clock class="w-5 h-5 text-blue-500" />
                            Today's Timetable
                        </h3>
                    </div>

                    <div v-if="todaysTimetable.length > 0" class="space-y-3 sm:space-y-4">
                        <div v-for="slot in todaysTimetable" :key="slot.id" 
                             class="flex items-center gap-3 sm:gap-4 p-3 sm:p-4 rounded-2xl bg-slate-50 border border-slate-100 transition-colors hover:bg-slate-100/50">
                            <div class="flex-shrink-0 w-16 sm:w-20 text-center">
                                <span class="text-xs sm:text-sm font-bold text-blue-600 block leading-tight">{{ slot.start_time }}</span>
                                <span class="text-[10px] sm:text-xs text-slate-500 tracking-tight">{{ slot.end_time }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-bold text-slate-800 text-sm sm:text-base truncate">{{ slot.subject }}</h4>
                                <p class="text-xs sm:text-sm text-slate-500 truncate">{{ slot.class }} • {{ slot.room || 'No Room' }}</p>
                            </div>
                            <div class="hidden sm:block">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-blue-100 text-blue-600">
                                    {{ slot.status || 'Active' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-10 sm:py-12">
                        <div class="bg-slate-50 w-12 sm:h-16 w-12 sm:w-16 rounded-full flex items-center justify-center mx-auto mb-4">
                            <Clock class="w-6 sm:w-8 h-6 sm:h-8 text-slate-300" />
                        </div>
                        <p class="text-slate-500 text-sm">No lessons scheduled for today.</p>
                    </div>
                </div>

                <!-- Syllabus Coverage -->
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-5 sm:p-6">
                    <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <CheckCircle class="w-5 h-5 text-emerald-500" />
                        Syllabus Coverage
                    </h3>
                    <div v-if="syllabusProgress.length > 0" class="space-y-5 sm:space-y-6">
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
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-5 sm:p-6">
                    <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <AlertCircle class="w-5 h-5 text-amber-500" />
                        Pending Action Items
                    </h3>
                    <div class="space-y-3 sm:space-y-4">
                        <div v-for="task in pendingTasks" :key="task.title" 
                             class="p-4 rounded-2xl bg-amber-50 border border-amber-100 flex items-center justify-between group hover:bg-amber-100/50 transition-colors">
                            <div>
                                <h4 class="text-xs sm:text-sm font-bold text-amber-900 uppercase tracking-tight">{{ task.title }}</h4>
                                <p class="text-xl sm:text-2xl font-black text-amber-600 tabular-nums">{{ task.count }}</p>
                            </div>
                            <Link :href="task.link" class="p-2.5 bg-white rounded-xl shadow-sm text-amber-600 hover:bg-amber-600 hover:text-white transition-all transform group-hover:scale-110">
                                <ArrowRight class="w-5 h-5" />
                            </Link>
                        </div>
                    </div>
                </div>

                <div class="space-y-6 lg:col-span-1">
                    <!-- My Classes -->
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-5 sm:p-6">
                        <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                            <Users class="w-5 h-5 text-emerald-500" />
                            My Classes
                        </h3>
                        <div class="space-y-2">
                            <div v-for="cls in myClasses" :key="cls.id" 
                                 class="flex items-center justify-between p-2.5 rounded-2xl hover:bg-slate-50 transition-colors border border-transparent hover:border-slate-100 group">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 font-bold group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                                        {{ cls.name.charAt(0) }}
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-bold text-slate-800">{{ cls.name }}</h4>
                                        <p class="text-[10px] sm:text-xs text-slate-500 font-medium">{{ cls.learner_count }} Students</p>
                                    </div>
                                </div>
                                <span v-if="cls.is_class_teacher" class="px-2 py-0.5 rounded-full text-[9px] bg-emerald-100 text-emerald-600 font-black uppercase tracking-tighter">
                                    CLASS TEACHER
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- My Subjects -->
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-5 sm:p-6">
                        <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                            <BookOpen class="w-5 h-5 text-indigo-500" />
                            My Subjects
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-2">
                            <div v-for="subject in mySubjects" :key="subject.id + '-' + subject.class_name" 
                                 class="p-3 rounded-2xl bg-indigo-50/50 border border-indigo-100 hover:bg-indigo-50 transition-colors group">
                                <h4 class="text-xs sm:text-sm font-bold text-indigo-900 group-hover:text-indigo-700">{{ subject.name }}</h4>
                                <p class="text-[10px] text-indigo-600/80 font-bold uppercase tracking-widest leading-none mt-1">{{ subject.class_name }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attendance Trend -->
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-5 sm:p-6">
                    <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <TrendingUp class="w-5 h-5 text-blue-500" />
                        Attendance Trend
                    </h3>
                    
                    <div v-if="attendanceStats.length > 0" class="h-48 sm:h-64 flex items-end justify-between gap-1 sm:gap-2 px-1">
                        <div v-for="day in attendanceStats" :key="day.date" class="flex-1 flex flex-col items-center gap-2 group">
                            <div class="w-full bg-slate-50 rounded-lg relative overflow-hidden flex flex-col justify-end h-32 sm:h-44 border border-slate-100/50">
                                <div class="bg-blue-600 absolute w-full transition-all duration-700 rounded-t-sm group-hover:bg-blue-400" 
                                     :style="{ height: day.rate + '%' }"></div>
                                <div class="absolute inset-x-0 bottom-2 flex items-center justify-center text-[9px] font-black tracking-tighter" 
                                     :class="day.rate > 40 ? 'text-white' : 'text-slate-600'">
                                    {{ day.rate }}%
                                </div>
                            </div>
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ day.date.substring(0,3) }}</span>
                        </div>
                    </div>
                    <div v-else class="text-center py-10 sm:py-12">
                        <p class="text-slate-500 text-sm">No attendance data available.</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
