<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { 
  ArrowLeft, Edit, Trash2, Plus, Filter, X, TrendingUp, 
  Users, BookOpen, Award, GraduationCap, Calendar, 
  Search, MoreHorizontal, Check
} from 'lucide-vue-next';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";

const page = usePage();
const dept = page.props?.department ?? {};
const teachers = page.props?.teachers ?? [];
const subjects = page.props?.subjects ?? [];
const grades = page.props?.grades ?? [];
const analytics = page.props?.analytics ?? {};
const curriculumSubjects = page.props?.curriculum_subjects ?? [];

const selectedTermFilter = ref('all');
const selectedYearFilter = ref('all');
const selectedExamFilter = ref('all');
const showAddSubjectModal = ref(false);
const subjectSearch = ref('');

const filteredCurriculumSubjects = computed(() => {
  const search = subjectSearch.value.toLowerCase();
  return curriculumSubjects.filter(s => 
    (s.name.toLowerCase().includes(search) || s.code.toLowerCase().includes(search)) &&
    !subjects.some(existing => existing.id === s.id)
  );
});

const filterGrades = computed(() => {
  let filtered = grades;
  if (selectedTermFilter.value !== 'all') {
    filtered = filtered.filter(g => g.term === selectedTermFilter.value);
  }
  if (selectedYearFilter.value !== 'all') {
    filtered = filtered.filter(g => g.year === selectedYearFilter.value);
  }
  // Exam type filtering if available in data
  return filtered;
});

const addSubject = (subjectId) => {
  router.post(`/departments/${dept.id}/subjects`, { subject_id: subjectId }, {
    preserveScroll: true,
    onSuccess: () => {
      showAddSubjectModal.value = false;
      subjectSearch.value = '';
    }
  });
};

const removeSubject = (subjectId) => {
  if (!confirm('Are you sure you want to remove this subject from the department?')) return;
  router.delete(`/departments/${dept.id}/subjects/${subjectId}`, {
    preserveScroll: true,
  });
};

const toggleStatus = () => {
  router.patch(`/departments/${dept.id}/toggle-status`, {}, {
    preserveScroll: true,
  });
};

const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Departments', href: '/departments' },
  { title: dept.name, href: '#' },
];
</script>

<template>
  <Head :title="dept.name" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="w-full py-8 sm:py-12 px-4 sm:px-6 lg:px-8 space-y-8 sm:space-y-12 animate-in fade-in duration-500">
      
      <!-- Header -->
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-center gap-4">
          <Button variant="ghost" size="icon" as-child class="h-10 w-10 rounded-xl hover:bg-slate-50 text-slate-400 hover:text-blue-600 transition-all">
            <Link href="/departments">
              <ArrowLeft class="h-5 w-5" />
            </Link>
          </Button>
          <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-600 text-white font-black text-xl shadow-lg shadow-blue-100 uppercase tracking-tighter">
            {{ dept.code?.substring(0, 2) || dept.name?.substring(0, 2).toUpperCase() }}
          </div>
          <div>
            <div class="flex items-center gap-3">
              <h1 class="text-2xl font-black tracking-tight text-slate-900 uppercase truncate max-w-[400px]">{{ dept.name }}</h1>
              <div class="flex items-center gap-2 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest border transition-all" :class="dept.is_active ? 'bg-emerald-50 text-emerald-600 border-emerald-100 shadow-sm' : 'bg-slate-50 text-slate-400 border-slate-100'">
                <div class="h-1 w-1 rounded-full" :class="dept.is_active ? 'bg-emerald-500 animate-pulse' : 'bg-slate-300'"></div>
                {{ dept.is_active ? 'Active' : 'Inactive' }}
              </div>
            </div>
            <p class="text-xs text-slate-400 font-bold uppercase tracking-widest mt-1">{{ dept.code }} • <span class="normal-case font-medium">{{ dept.description || 'Academic Excellence Department' }}</span></p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <Button variant="outline" class="h-11 rounded-xl border-slate-200 text-[10px] font-bold uppercase tracking-wider px-6 hover:bg-slate-50 transition-all shadow-sm" @click="toggleStatus">
            {{ dept.is_active ? 'Deactivate' : 'Activate' }} Status
          </Button>
          <Button class="h-11 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-[10px] font-bold uppercase tracking-wider px-6 shadow-sm transition-all" as-child>
            <Link :href="`/departments/${dept.id}/edit`">
              <Edit class="mr-2 h-4 w-4" />
              Edit Details
            </Link>
          </Button>
          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <Button class="h-11 w-11 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-all shadow-sm" variant="ghost" size="icon"><MoreHorizontal class="h-4 w-4" /></Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" class="w-56 rounded-xl p-2 shadow-xl border-slate-100">
               <DropdownMenuItem class="rounded-lg text-rose-600 font-bold">
                <Trash2 class="mr-2 h-4 w-4" /> Delete Department
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </div>
      </div>

      <!-- Quick Stats & Analytics -->
      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border bg-white p-6 shadow-sm border-slate-100">
          <div class="flex items-center gap-3">
             <div class="p-2 rounded-lg bg-blue-50 text-blue-600">
                <Users class="h-5 w-5" />
             </div>
            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Total Teachers</span>
          </div>
          <p class="mt-4 text-3xl font-bold text-slate-900">{{ teachers.length }}</p>
        </div>
        <div class="rounded-2xl border bg-white p-6 shadow-sm border-slate-100">
          <div class="flex items-center gap-3">
             <div class="p-2 rounded-lg bg-amber-50 text-amber-600">
                <BookOpen class="h-5 w-5" />
             </div>
            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Department Subjects</span>
          </div>
          <p class="mt-4 text-3xl font-bold text-slate-900">{{ subjects.length }}</p>
        </div>
        <div class="rounded-2xl border bg-white p-6 shadow-sm border-slate-100">
          <div class="flex items-center gap-3">
             <div class="p-2 rounded-lg bg-emerald-50 text-emerald-600">
                <Award class="h-5 w-5" />
             </div>
            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Mean Grade Average</span>
          </div>
          <p class="mt-4 text-3xl font-bold text-slate-900">{{ analytics.mean_grade ?? '—' }}%</p>
        </div>
        <div class="rounded-2xl border bg-white p-6 shadow-sm border-slate-100">
          <div class="flex items-center gap-3">
             <div class="p-2 rounded-lg bg-blue-600 text-white shadow-md shadow-blue-100">
                <TrendingUp class="h-5 w-5" />
             </div>
            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Student Pass Rate</span>
          </div>
          <p class="mt-4 text-3xl font-bold text-blue-600">{{ analytics.pass_rate ?? 0 }}%</p>
        </div>
      </div>

      <div class="grid gap-8 lg:grid-cols-3">
        <!-- Main Content (Left) -->
        <div class="lg:col-span-2 space-y-8 sm:space-y-12">
          
          <!-- Performance Analytics -->
          <div class="rounded-2xl border bg-white p-8 shadow-sm border-slate-100">
            <h3 class="text-lg font-bold text-slate-900 mb-8 flex items-center gap-3">
              <TrendingUp class="h-5 w-5 text-blue-600" />
              Academic Performance Analysis
            </h3>
            
            <div class="grid gap-12 md:grid-cols-2">
              <!-- Grade Distribution -->
              <div class="space-y-6">
                <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Grade Distribution</h4>
                <div class="space-y-6">
                  <div v-for="(count, grade) in { 'Exceeding (EE)': analytics.ee_count, 'Meeting (ME)': analytics.me_count, 'Approaching (AE)': analytics.ae_count, 'Below (BE)': analytics.be_count }" :key="grade">
                    <div class="flex items-center justify-between mb-2">
                      <span class="text-xs font-bold text-slate-700 uppercase tracking-wide">{{ grade }}</span>
                      <span class="text-xs font-bold text-slate-900">{{ count ?? 0 }} {{ count === 1 ? 'Learner' : 'Learners' }}</span>
                    </div>
                    <div class="h-2 w-full bg-slate-100 rounded-full overflow-hidden">
                      <div 
                        class="h-full rounded-full transition-all duration-1000 shadow-sm"
                        :class="{
                          'bg-emerald-500': grade.includes('EE'),
                          'bg-blue-600': grade.includes('ME'),
                          'bg-amber-400': grade.includes('AE'),
                          'bg-rose-500': grade.includes('BE'),
                        }"
                        :style="{ width: analytics.total_grades > 0 ? ((count ?? 0) / analytics.total_grades * 100) + '%' : '0%' }"
                      ></div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Pass/Fail breakdown -->
              <div class="flex flex-col justify-center gap-8 md:border-l border-slate-100 md:pl-12">
                <div>
                  <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Performance Summary</h4>
                  <p class="text-[10px] text-slate-400 font-medium italic">Based on {{ analytics.total_grades }} assessment records</p>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                  <div class="rounded-2xl bg-emerald-50 p-5 border border-emerald-100 shadow-sm">
                    <p class="text-[8px] font-bold text-emerald-700 uppercase tracking-widest mb-1">Meeting Expectations</p>
                    <p class="text-3xl font-black text-emerald-800">{{ analytics.pass_rate }}%</p>
                  </div>
                   <div class="rounded-2xl bg-rose-50 p-5 border border-rose-100 shadow-sm">
                    <p class="text-[8px] font-bold text-rose-700 uppercase tracking-widest mb-1">Below Expectations</p>
                    <p class="text-3xl font-black text-rose-800">{{ analytics.fail_rate }}%</p>
                  </div>
                </div>
                
                <div class="rounded-2xl bg-slate-50 p-5 border border-slate-100">
                   <p class="text-[8px] text-slate-400 mb-2 font-bold uppercase tracking-widest">Department Status</p>
                   <div class="flex items-center gap-3">
                     <div class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></div>
                     <span class="text-xs font-bold text-slate-900 uppercase tracking-tight">Active Academic Growth</span>
                   </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Subjects List -->
          <div class="rounded-2xl border bg-white shadow-sm overflow-hidden border-slate-100">
            <div class="flex items-center justify-between p-8 border-b border-slate-50 bg-slate-50/30">
              <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg bg-blue-50 text-blue-600">
                  <BookOpen class="h-5 w-5" />
                </div>
                <h3 class="text-lg font-bold text-slate-900">Academic Subjects</h3>
              </div>
              <Button class="h-10 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-bold uppercase tracking-wider px-6 shadow-md shadow-blue-100" @click="showAddSubjectModal = true">
                <Plus class="mr-2 h-4 w-4" />
                Manage Subjects
              </Button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-px bg-slate-50">
              <div v-if="subjects.length === 0" class="bg-white col-span-full py-16 text-center text-slate-400 font-medium italic">
                No subjects assigned to this department yet.
              </div>
              <div v-for="subject in subjects" :key="subject.id" class="group bg-white p-6 flex items-start justify-between hover:bg-blue-50/30 transition-all">
                <div class="flex gap-4">
                   <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-50 border border-slate-100 text-slate-400 text-xs font-bold shadow-sm transition-all group-hover:bg-blue-600 group-hover:text-white group-hover:border-blue-600">
                      {{ subject.code }}
                   </div>
                   <div>
                     <h4 class="font-bold text-slate-900 group-hover:text-blue-600 transition-colors uppercase text-sm leading-tight">{{ subject.name }}</h4>
                     <p class="text-[10px] text-slate-400 font-medium line-clamp-1 mt-1">{{ subject.description || 'Academic Subject' }}</p>
                     <div class="mt-3 flex items-center gap-2">
                        <span class="inline-flex items-center rounded-lg bg-emerald-50 px-2 py-1 text-[8px] font-bold text-emerald-700 border border-emerald-100 uppercase tracking-widest shadow-sm">
                          {{ subject.is_offered ? 'Active' : 'Paused' }}
                        </span>
                     </div>
                   </div>
                </div>
                <Button variant="ghost" size="icon" @click="removeSubject(subject.id)" class="h-9 w-9 rounded-xl text-slate-300 hover:text-rose-600 hover:bg-rose-50 transition-all opacity-100 md:opacity-0 group-hover:opacity-100">
                  <Trash2 class="h-4 w-4" />
                </Button>
              </div>
            </div>
          </div>

          <!-- Recent Grades Table -->
          <div class="rounded-2xl border bg-white shadow-sm overflow-hidden border-slate-100">
             <div class="flex flex-col md:flex-row md:items-center md:justify-between p-8 border-b border-slate-50 bg-slate-50/30 gap-4">
              <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg bg-blue-50 text-blue-600">
                  <GraduationCap class="h-5 w-5" />
                </div>
                <h3 class="text-lg font-bold text-slate-900">Learner Performance Records</h3>
              </div>
              <div class="flex items-center gap-2">
                  <select v-model="selectedTermFilter" class="h-9 rounded-xl border border-slate-200 bg-white px-3 py-1 text-[10px] font-bold uppercase tracking-wider outline-none focus:ring-2 focus:ring-blue-600 transition-all shadow-sm">
                    <option value="all">All Terms</option>
                    <option v-for="t in ['Term 1', 'Term 2', 'Term 3']" :key="t" :value="t">{{ t }}</option>
                  </select>
                  <select v-model="selectedYearFilter" class="h-9 rounded-xl border border-slate-200 bg-white px-3 py-1 text-[10px] font-bold uppercase tracking-wider outline-none focus:ring-2 focus:ring-blue-600 transition-all shadow-sm">
                    <option value="all">All Years</option>
                    <option v-for="y in ['2024', '2025', '2026']" :key="y" :value="y">{{ y }}</option>
                  </select>
              </div>
            </div>
            
            <div class="overflow-x-auto">
              <table class="w-full border-collapse">
                <thead>
                  <tr class="bg-slate-50 border-b border-slate-100">
                    <th class="px-8 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Learner Name</th>
                    <th class="px-8 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Subject</th>
                    <th class="px-8 py-4 text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest">Score %</th>
                    <th class="px-8 py-4 text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest">Grade</th>
                    <th class="px-8 py-4 text-right text-[10px] font-bold text-slate-400 uppercase tracking-widest">Academic Period</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                  <tr v-if="filterGrades.length === 0" class="h-32">
                    <td colspan="5" class="text-center text-slate-400 font-medium italic">No performance records found matching your filters.</td>
                  </tr>
                  <tr v-for="grade in filterGrades.slice(0, 10)" :key="grade.id" class="group hover:bg-blue-50/30 transition-all">
                    <td class="px-8 py-5">
                      <div class="font-bold text-slate-900 group-hover:text-blue-600 transition-colors uppercase text-xs">{{ grade.student_name }}</div>
                    </td>
                    <td class="px-8 py-5">
                      <div class="flex items-center gap-2">
                        <div class="h-1.5 w-1.5 rounded-full bg-blue-500 shadow-sm shadow-blue-200"></div>
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-tight">{{ grade.subject_name }}</span>
                      </div>
                    </td>
                    <td class="px-8 py-5 text-center">
                      <div class="inline-flex items-center justify-center h-8 min-w-[40px] rounded-lg bg-slate-50 border border-slate-100 px-2 text-sm font-black text-slate-900">
                        {{ grade.percentage }}%
                      </div>
                    </td>
                    <td class="px-8 py-5">
                      <div class="flex justify-center">
                        <div 
                          class="flex items-center justify-center px-3 py-1 rounded-lg text-[10px] font-black tracking-widest border transition-all shadow-sm"
                          :class="{
                            'bg-emerald-50 text-emerald-700 border-emerald-100': grade.grade === 'EE',
                            'bg-blue-50 text-blue-700 border-blue-100': grade.grade === 'ME',
                            'bg-amber-50 text-amber-700 border-amber-100': grade.grade === 'AE',
                            'bg-rose-50 text-rose-700 border-rose-100': grade.grade === 'BE',
                          }"
                        >
                          {{ grade.grade }}
                        </div>
                      </div>
                    </td>
                    <td class="px-8 py-5 text-right">
                      <div class="flex flex-col items-end">
                        <span class="text-[10px] font-bold text-slate-900 uppercase tracking-widest leading-none">{{ grade.term }}</span>
                        <span class="text-[8px] text-slate-400 font-bold uppercase tracking-[0.2em] mt-1.5 leading-none">{{ grade.year }}</span>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div v-if="filterGrades.length > 10" class="p-4 bg-slate-50/50 text-center border-t border-slate-50">
               <p class="text-[10px] text-slate-400 font-bold uppercase tracking-[0.1em]">Showing latest 10 of {{ filterGrades.length }} performance records</p>
            </div>
          </div>
        </div>

        <!-- Sidebar (Right) -->
        <div class="space-y-8 sm:space-y-12">
          <!-- Department Head -->
          <div class="rounded-2xl border bg-white p-8 shadow-sm border-slate-100 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50/50 rounded-bl-[100px] -z-0 transition-all group-hover:bg-blue-100/50"></div>
            
            <div class="relative z-10">
              <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-8">Department Head</h3>
              <div class="flex flex-col items-center text-center">
                <div class="h-24 w-24 rounded-2xl bg-slate-50 border-2 border-white shadow-xl flex items-center justify-center mb-6 overflow-hidden">
                   <div v-if="dept.head_of_department" class="h-full w-full bg-blue-600 flex items-center justify-center text-3xl font-black text-white uppercase tracking-tighter">
                     {{ dept.head_of_department.name.charAt(0) }}
                   </div>
                   <User v-else class="h-10 w-10 text-slate-200" />
                </div>
                <h4 class="text-xl font-black text-slate-900 uppercase tracking-tight mb-1">{{ dept.head_of_department ? dept.head_of_department.name : 'Not Assigned' }}</h4>
                <p class="text-[10px] font-bold text-blue-600 uppercase tracking-widest mb-8">Lead Instructor</p>
                
                <div class="w-full space-y-3">
                  <Button variant="outline" class="w-full h-11 rounded-xl border-slate-200 text-[10px] font-bold uppercase tracking-wider hover:bg-slate-50 transition-all shadow-sm" @click="showAssignHeadModal = true">
                    <UserPlus class="mr-2 h-4 w-4" />
                    Change Head
                  </Button>
                </div>
              </div>
            </div>
          </div>

          <!-- Staff Members -->
          <div class="rounded-2xl border bg-white shadow-sm overflow-hidden border-slate-100">
            <div class="p-8 border-b border-slate-50 bg-slate-50/30 flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg bg-blue-50 text-blue-600">
                  <Users class="h-5 w-5" />
                </div>
                <h3 class="text-lg font-bold text-slate-900 leading-none">Staff Members</h3>
              </div>
              <span class="text-[10px] font-black text-blue-600 bg-blue-50 px-2 py-1 rounded-lg">{{ teachers.length }}</span>
            </div>
            
            <div class="divide-y divide-slate-50 max-h-[600px] overflow-y-auto custom-scrollbar">
              <div v-if="teachers.length === 0" class="p-12 text-center text-slate-400 font-medium italic">
                No staff members currently assigned.
              </div>
              <div v-for="teacher in teachers" :key="teacher.id" class="p-6 flex items-center justify-between hover:bg-blue-50/30 transition-all group">
                <div class="flex items-center gap-4">
                  <div class="h-10 w-10 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center text-xs font-bold text-slate-400 shadow-sm transition-all group-hover:bg-blue-600 group-hover:text-white group-hover:border-blue-600">
                    {{ teacher.name.charAt(0) }}
                  </div>
                  <div>
                    <h5 class="text-xs font-bold text-slate-900 uppercase tracking-tight group-hover:text-blue-600 transition-colors">{{ teacher.name }}</h5>
                    <div class="flex items-center gap-2 mt-1">
                       <span class="text-[8px] font-bold text-slate-400 uppercase tracking-widest leading-none">{{ teacher.staff_code || 'T-ID' }}</span>
                       <div class="h-1 w-1 rounded-full bg-slate-200"></div>
                       <span class="text-[8px] font-bold text-blue-500 uppercase tracking-widest leading-none">{{ teacher.subjects_count || 0 }} Subjects</span>
                    </div>
                  </div>
                </div>
                <DropdownMenu>
                  <DropdownMenuTrigger as-child>
                    <Button variant="ghost" size="icon" class="h-8 w-8 rounded-lg text-slate-300 hover:text-blue-600 hover:bg-blue-50"><MoreHorizontal class="h-4 w-4" /></Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end" class="rounded-xl p-2 shadow-xl border-slate-100">
                     <DropdownMenuItem class="rounded-lg font-bold text-[10px] uppercase tracking-wider" @click="removeTeacher(teacher.id)">
                      <X class="mr-2 h-4 w-4 text-rose-500" /> Remove from Staff
                    </DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </div>
            </div>
            
            <div class="p-6 bg-slate-50/50 border-t border-slate-50">
              <Button class="w-full h-11 rounded-xl bg-slate-900 border-0 hover:bg-slate-800 text-white text-[10px] font-bold uppercase tracking-wider shadow-lg shadow-slate-200 transition-all" @click="showAddTeacherModal = true">
                <Plus class="mr-2 h-4 w-4" />
                Add Staff Member
              </Button>
            </div>
          </div>
        </div>
      </div>

      <!-- Add Subject Modal -->
      <div v-if="showAddSubjectModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-50 animate-in fade-in duration-300">
        <div class="bg-white rounded-3xl p-8 max-w-md w-full mx-4 shadow-2xl animate-in zoom-in-95 duration-200 border border-slate-100">
          <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-3">
              <div class="p-2 rounded-lg bg-blue-50 text-blue-600">
                <BookOpen class="h-5 w-5" />
              </div>
              <h3 class="text-xl font-black text-slate-900 tracking-tight">Assign Subjects</h3>
            </div>
            <Button variant="ghost" size="icon" @click="showAddSubjectModal = false" class="h-10 w-10 rounded-xl hover:bg-slate-50 text-slate-400 hover:text-rose-600 transition-all">
              <X class="h-5 w-5" />
            </Button>
          </div>
          
          <div class="space-y-6">
            <div class="relative">
              <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
              <Input v-model="subjectSearch" placeholder="Search curriculum subjects..." class="pl-10 h-12 rounded-xl border-slate-200 focus:ring-blue-600 text-sm transition-all" />
            </div>
            
            <div class="max-h-[350px] overflow-y-auto space-y-2 pr-1 custom-scrollbar">
              <div v-if="filteredCurriculumSubjects.length === 0" class="py-16 text-center text-slate-400 font-medium italic bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                 No unassigned subjects found.
              </div>
              <div 
                v-for="sub in filteredCurriculumSubjects" 
                :key="sub.id" 
                @click="addSubject(sub.id)"
                class="flex items-center justify-between p-4 rounded-2xl hover:bg-blue-50 border border-transparent hover:border-blue-100 cursor-pointer transition-all group shadow-sm bg-white"
              >
                <div class="flex items-center gap-4">
                  <div class="h-10 w-10 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-500 shadow-sm transition-all group-hover:bg-blue-600 group-hover:text-white group-hover:border-blue-600">
                    {{ sub.code }}
                  </div>
                  <div>
                    <span class="text-xs font-bold text-slate-900 group-hover:text-blue-600 transition-colors uppercase block leading-none">{{ sub.name }}</span>
                    <span class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mt-1.5 block leading-none">{{ sub.is_offered ? 'Core Subject' : 'Elective' }}</span>
                  </div>
                </div>
                <div class="h-8 w-8 rounded-lg bg-blue-100/50 text-blue-600 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all">
                   <Plus class="h-4 w-4" />
                </div>
              </div>
            </div>
            
            <div class="pt-6 border-t border-slate-50 flex items-center justify-between">
              <p class="text-[8px] text-slate-400 font-bold uppercase tracking-[0.15em]">Only active subjects available</p>
              <Button variant="outline" size="sm" @click="showAddSubjectModal = false" class="h-10 rounded-xl border-slate-200 text-[10px] font-bold uppercase tracking-wider px-8 hover:bg-slate-50 transition-all shadow-sm">Done</Button>
            </div>
          </div>
        </div>
      </div>
      
    </div>
  </AppLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #cbd5e1;
}
</style>
