<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { 
    BookOpen, Users, ClipboardList, GraduationCap, 
    Calendar, Clock, MapPin, ChevronRight, 
    Plus, Upload, CheckCircle2, AlertCircle,
    TrendingUp, UserCheck, MessageSquare, Award,
    Search, Filter, FileText
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';

const props = defineProps<{
    dashboardType: string;
    teacher: any;
    myClasses: any[];
    mySubjects: any[];
    totalStudents: number;
    todaysTimetable: any[];
    recentAssessments: any[];
    attendanceStats: any[];
    academicYear: string;
    notificationsCount: number;
    auth: any;
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' }
];

const activeRate = 95; // Mock data for now
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Teacher Dashboard" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Pulsar Header Style -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-500/10">
                        <GraduationCap class="h-6 w-6 text-violet-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight uppercase">Teacher Workspace</h1>
                        <p class="text-muted-foreground">Manage your classes, subjects, and student performance, {{ teacher?.first_name }}.</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Button variant="outline" size="sm">
                        <Calendar class="mr-2 h-4 w-4" />
                        {{ academicYear || '2024' }}
                    </Button>
                    <Button size="sm" as-child>
                        <Link href="/attendance">
                            <Plus class="mr-2 h-4 w-4" />
                            Mark Attendance
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Pulsar Stats Style -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border bg-linear-to-br from-violet-50 to-violet-100/50 p-4">
                    <div class="text-sm font-bold text-muted-foreground uppercase tracking-widest">Active Classes</div>
                    <div class="mt-1 text-3xl font-black text-violet-600">{{ myClasses.length }}</div>
                    <div class="mt-1 text-xs text-violet-600 font-bold italic tracking-tighter">Primary Teaching Load</div>
                </div>
                <div class="rounded-xl border bg-linear-to-br from-indigo-50 to-indigo-100/50 p-4">
                    <div class="text-sm font-bold text-muted-foreground uppercase tracking-widest">Total Students</div>
                    <div class="mt-1 text-3xl font-black text-indigo-600">{{ totalStudents.toLocaleString() }}</div>
                    <div class="mt-1 text-xs text-muted-foreground font-bold italic tracking-tighter">{{ activeRate }}% Avg attendance rate</div>
                </div>
                <div class="rounded-xl border bg-linear-to-br from-emerald-50 to-emerald-100/50 p-4">
                    <div class="text-sm font-bold text-muted-foreground uppercase tracking-widest">Subjects</div>
                    <div class="mt-1 text-3xl font-black text-emerald-600">{{ mySubjects.length }}</div>
                    <div class="mt-1 text-xs text-emerald-600 font-bold italic tracking-tighter">Current Academic Term</div>
                </div>
                <div class="rounded-xl border bg-linear-to-br from-amber-50 to-amber-100/50 p-4">
                    <div class="text-sm font-bold text-muted-foreground uppercase tracking-widest">Assessments</div>
                    <div class="mt-1 text-3xl font-black text-amber-600">{{ recentAssessments.length }}</div>
                    <div class="mt-1 text-xs text-muted-foreground font-bold italic tracking-tighter">Pending review records</div>
                </div>
            </div>

            <!-- Main Workspace Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Schedule Module (Pulsar List Style) -->
                <div class="lg:col-span-2 space-y-4">
                    <div class="flex items-center justify-between px-2">
                        <h3 class="text-lg font-black text-gray-900 flex items-center gap-2">
                            <Clock class="h-5 w-5 text-violet-600" /> TODAY'S SCHEDULE
                        </h3>
                        <Link href="/timetable/my" class="text-xs font-black text-violet-600 hover:underline uppercase tracking-widest">View Full Timetable</Link>
                    </div>

                    <div class="rounded-xl border bg-card overflow-hidden">
                        <div v-if="todaysTimetable.length > 0" class="divide-y divide-gray-100">
                            <div v-for="slot in todaysTimetable" :key="slot.id" class="p-6 hover:bg-gray-50/50 transition-colors flex items-center justify-between group">
                                <div class="flex items-center gap-5">
                                    <div class="text-center min-w-[70px] border-r-2 border-dashed border-gray-100 pr-5">
                                        <p class="text-sm font-black text-violet-600 uppercase">{{ slot.start_time }}</p>
                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">{{ slot.end_time }}</p>
                                    </div>
                                    <div>
                                        <h4 class="font-black text-gray-900 group-hover:text-violet-700 transition-colors">{{ slot.subject }}</h4>
                                        <div class="flex items-center gap-2 text-xs font-bold text-gray-400 mt-1 uppercase tracking-widest italic opacity-70">
                                            <MapPin class="h-3 w-3" /> {{ slot.room || 'General Hall' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4 text-right">
                                    <div>
                                        <p class="text-xs font-black text-gray-900 uppercase tracking-widest">{{ slot.class }}</p>
                                        <Badge class="mt-1 bg-violet-50 text-violet-600 border-violet-100 hover:bg-violet-100 transition-colors cursor-default">In Progress</Badge>
                                    </div>
                                    <ChevronRight class="h-5 w-5 text-gray-300 group-hover:text-violet-400 group-hover:translate-x-1 transition-all" />
                                </div>
                            </div>
                        </div>
                        <div v-else class="p-16 text-center">
                            <div class="inline-flex h-20 w-20 rounded-full bg-gray-50 items-center justify-center mb-6">
                                <Calendar class="h-10 w-10 text-gray-200" />
                            </div>
                            <h4 class="text-xl font-black text-gray-900">No lessons scheduled today</h4>
                            <p class="text-gray-400 max-w-sm mx-auto mt-2 text-sm font-medium">Use this time for lesson planning or professional development.</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Panels -->
                <div class="space-y-6">
                    <div class="bg-card rounded-xl border shadow-sm p-6 space-y-6">
                        <h4 class="text-sm font-black text-gray-400 uppercase tracking-widest flex items-center gap-2">
                             <TrendingUp class="h-4 w-4 text-emerald-600" /> Performance Insights
                        </h4>
                        
                        <!-- Attendance Chart/Trends Simplification -->
                        <div class="space-y-4">
                            <div v-for="stat in attendanceStats.slice(0, 3)" :key="stat.date" class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="h-2 w-2 rounded-full bg-emerald-500"></div>
                                    <span class="text-sm font-black text-gray-800">{{ stat.date }}</span>
                                </div>
                                <span class="text-xs font-black text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full">{{ stat.rate }}% Presence</span>
                            </div>
                        </div>
                        
                        <Button variant="outline" class="w-full h-12 rounded-xl font-black text-xs uppercase" as-child>
                             <Link href="/reports/attendance">Detailed Reports</Link>
                        </Button>
                    </div>

                    <div class="bg-card rounded-xl border border-violet-200/50 shadow-sm p-6 space-y-6 bg-linear-to-br from-white to-violet-50/30">
                        <h4 class="text-sm font-black text-gray-400 uppercase tracking-widest flex items-center gap-2">
                             <Award class="h-4 w-4 text-violet-600" /> Recent Assessments
                        </h4>
                        
                        <div class="space-y-4">
                            <div v-for="asmt in recentAssessments" :key="asmt.id" class="flex items-center justify-between p-3 rounded-xl hover:bg-white transition-colors border-transparent hover:border-violet-100 border">
                                <div class="space-y-1">
                                    <p class="text-xs font-black text-gray-900">{{ asmt.title }}</p>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase">{{ asmt.class?.name }} · {{ asmt.subject?.name }}</p>
                                </div>
                                <ChevronRight class="h-4 w-4 text-gray-300" />
                            </div>
                        </div>

                        <Button class="w-full bg-violet-600 hover:bg-violet-700 text-white h-12 rounded-xl font-black text-xs uppercase" as-child>
                            <Link href="/assessments/bulk-upload">Bulk Upload Marks</Link>
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Gradient and shadow transitions */
.bg-card {
    transition: all 0.3s ease;
}
.bg-card:hover {
    box-shadow: 0 10px 30px rgba(0,0,0,0.02);
}
</style>
