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
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
      
      <!-- Header -->
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-center gap-4">
          <Button variant="ghost" size="icon" as-child class="rounded-full">
            <Link href="/departments">
              <ArrowLeft class="h-5 w-5" />
            </Link>
          </Button>
          <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-600 text-white font-bold text-xl shadow-lg shadow-indigo-200">
            {{ dept.code?.substring(0, 2) || dept.name?.substring(0, 2).toUpperCase() }}
          </div>
          <div>
            <div class="flex items-center gap-3">
              <h1 class="text-2xl font-bold tracking-tight text-gray-900">{{ dept.name }}</h1>
              <Badge :variant="dept.is_active ? 'default' : 'outline'" class="rounded-full">
                {{ dept.is_active ? 'Active' : 'Inactive' }}
              </Badge>
            </div>
            <p class="text-sm text-muted-foreground">{{ dept.code }} • {{ dept.description || 'No description' }}</p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <Button variant="outline" size="sm" @click="toggleStatus">
            {{ dept.is_active ? 'Deactivate' : 'Activate' }}
          </Button>
          <Button size="sm" variant="outline" as-child>
            <Link :href="`/departments/${dept.id}/edit`">
              <Edit class="mr-2 h-4 w-4" />
              Edit
            </Link>
          </Button>
          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <Button size="icon" variant="ghost"><MoreHorizontal class="h-4 w-4" /></Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
              <DropdownMenuItem class="text-red-600 focus:text-red-600">
                <Trash2 class="mr-2 h-4 w-4" /> Delete Department
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </div>
      </div>

      <!-- Quick Stats & Analytics -->
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border bg-card p-4 transition-all hover:shadow-sm">
          <div class="flex items-center gap-3">
             <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                <Users class="h-4 w-4" />
             </div>
            <span class="text-sm text-muted-foreground font-medium">Teachers</span>
          </div>
          <p class="mt-2 text-2xl font-bold text-gray-900">{{ teachers.length }}</p>
        </div>
        <div class="rounded-xl border bg-card p-4 transition-all hover:shadow-sm">
          <div class="flex items-center gap-3">
             <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                <BookOpen class="h-4 w-4" />
             </div>
            <span class="text-sm text-muted-foreground font-medium">Subjects</span>
          </div>
          <p class="mt-2 text-2xl font-bold text-gray-900">{{ subjects.length }}</p>
        </div>
        <div class="rounded-xl border bg-card p-4 transition-all hover:shadow-sm">
          <div class="flex items-center gap-3">
             <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-50 text-orange-600">
                <Award class="h-4 w-4" />
             </div>
            <span class="text-sm text-muted-foreground font-medium">Mean Grade</span>
          </div>
          <p class="mt-2 text-2xl font-bold text-gray-900">{{ analytics.mean_grade ?? '—' }}%</p>
        </div>
        <div class="rounded-xl border bg-card p-4 transition-all hover:shadow-sm">
          <div class="flex items-center gap-3">
             <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-green-50 text-green-600">
                <TrendingUp class="h-4 w-4" />
             </div>
            <span class="text-sm text-muted-foreground font-medium">Pass Rate</span>
          </div>
          <p class="mt-2 text-2xl font-bold text-green-600">{{ analytics.pass_rate ?? 0 }}%</p>
        </div>
      </div>

      <div class="grid gap-6 lg:grid-cols-3">
        <!-- Main Content (Left) -->
        <div class="lg:col-span-2 space-y-6">
          
          <!-- Performance Analytics -->
          <div class="rounded-xl border bg-card p-6 shadow-sm">
            <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
              <TrendingUp class="h-5 w-5 text-indigo-600" />
              Academic Performance Analytics
            </h3>
            
            <div class="grid gap-8 md:grid-cols-2">
              <!-- Grade Distribution -->
              <div class="space-y-4">
                <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Grade Distribution</h4>
                <div class="space-y-4">
                  <div v-for="(count, grade) in { 'Exceeding (EE)': analytics.ee_count, 'Meeting (ME)': analytics.me_count, 'Approaching (AE)': analytics.ae_count, 'Below (BE)': analytics.be_count }" :key="grade">
                    <div class="flex items-center justify-between mb-1.5">
                      <span class="text-sm font-medium text-gray-600">{{ grade }}</span>
                      <span class="text-sm font-bold text-gray-900">{{ count ?? 0 }} {{ count === 1 ? 'student' : 'students' }}</span>
                    </div>
                    <div class="h-2.5 w-full bg-muted rounded-full overflow-hidden">
                      <div 
                        class="h-full rounded-full transition-all duration-1000"
                        :class="{
                          'bg-green-500': grade.includes('EE'),
                          'bg-blue-500': grade.includes('ME'),
                          'bg-orange-400': grade.includes('AE'),
                          'bg-red-500': grade.includes('BE'),
                        }"
                        :style="{ width: analytics.total_grades > 0 ? ((count ?? 0) / analytics.total_grades * 100) + '%' : '0%' }"
                      ></div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Pass/Fail breakdown -->
              <div class="flex flex-col justify-center gap-6 border-l pl-8">
                <div>
                  <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-2">Performance Summary</h4>
                  <p class="text-xs text-muted-foreground mb-4">Based on {{ analytics.total_grades }} assessment records</p>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                  <div class="rounded-xl bg-green-50 p-4 border border-green-100">
                    <p class="text-xs font-bold text-green-700 uppercase tracking-tight">Meeting/Exceeding</p>
                    <p class="text-3xl font-black text-green-800">{{ analytics.pass_rate }}%</p>
                  </div>
                   <div class="rounded-xl bg-red-50 p-4 border border-red-100">
                    <p class="text-xs font-bold text-red-700 uppercase tracking-tight">Below Expectations</p>
                    <p class="text-3xl font-black text-red-800">{{ analytics.fail_rate }}%</p>
                  </div>
                </div>
                
                <div class="rounded-xl border bg-muted/30 p-4">
                   <p class="text-xs text-muted-foreground mb-1 font-medium">Overall Trend</p>
                   <div class="flex items-center gap-2">
                     <div class="h-2 w-2 rounded-full bg-green-500 animate-pulse"></div>
                     <span class="font-bold text-gray-900">Positive performance trajectory</span>
                   </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Subjects List -->
          <div class="rounded-xl border bg-card shadow-sm overflow-hidden">
            <div class="flex items-center justify-between p-6 border-b">
              <div class="flex items-center gap-2">
                <BookOpen class="h-5 w-5 text-indigo-600" />
                <h3 class="text-lg font-bold text-gray-900">Department Subjects</h3>
              </div>
              <Button size="sm" @click="showAddSubjectModal = true">
                <Plus class="mr-2 h-4 w-4" />
                Manage Subjects
              </Button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-px bg-gray-100">
              <div v-if="subjects.length === 0" class="bg-white col-span-full py-12 text-center text-muted-foreground">
                No subjects assigned to this department yet.
              </div>
              <div v-for="subject in subjects" :key="subject.id" class="group bg-white p-5 flex items-start justify-between hover:bg-muted/30 transition-colors">
                <div class="flex gap-4">
                   <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-700 text-xs font-bold">
                      {{ subject.code }}
                   </div>
                   <div>
                     <h4 class="font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">{{ subject.name }}</h4>
                     <p class="text-xs text-muted-foreground line-clamp-1 mt-0.5">{{ subject.description || 'Global Curriculum Subject' }}</p>
                     <div class="mt-2 flex items-center gap-3">
                        <span class="inline-flex items-center rounded-full bg-green-50 px-2 py-0.5 text-[10px] font-bold text-green-700 border border-green-200">
                          {{ subject.is_offered ? 'Offered' : 'Paused' }}
                        </span>
                     </div>
                   </div>
                </div>
                <Button variant="ghost" size="icon" @click="removeSubject(subject.id)" class="text-muted-foreground hover:text-red-600 hover:bg-red-50 -mt-1 rounded-full opacity-0 group-hover:opacity-100 transition-all">
                  <X class="h-4 w-4" />
                </Button>
              </div>
            </div>
          </div>

          <!-- Recent Grades Table -->
          <div class="rounded-xl border bg-card shadow-sm overflow-hidden">
             <div class="flex flex-col md:flex-row md:items-center md:justify-between p-6 border-b gap-4">
              <div class="flex items-center gap-2">
                <GraduationCap class="h-5 w-5 text-indigo-600" />
                <h3 class="text-lg font-bold text-gray-900">Student Performance Records</h3>
              </div>
              <div class="flex items-center gap-2">
                  <select v-model="selectedTermFilter" class="h-8 rounded-md border border-input bg-background px-2 py-1 text-xs ring-offset-background outline-none">
                    <option value="all">All Terms</option>
                    <option v-for="t in ['Term 1', 'Term 2', 'Term 3']" :key="t" :value="t">{{ t }}</option>
                  </select>
                  <select v-model="selectedYearFilter" class="h-8 rounded-md border border-input bg-background px-2 py-1 text-xs ring-offset-background outline-none">
                    <option value="all">All Years</option>
                    <option v-for="y in ['2024', '2025', '2026']" :key="y" :value="y">{{ y }}</option>
                  </select>
              </div>
            </div>
            
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead class="bg-muted/50 border-b">
                  <tr>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Student</th>
                    <th class="px-6 py-3 text-left text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Subject</th>
                    <th class="px-6 py-3 text-center text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Score %</th>
                    <th class="px-6 py-3 text-center text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Grade</th>
                    <th class="px-6 py-3 text-right text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Period</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                  <tr v-if="filterGrades.length === 0" class="h-24">
                    <td colspan="5" class="text-center text-muted-foreground font-medium italic">No assessment data matches your filters.</td>
                  </tr>
                  <tr v-for="grade in filterGrades.slice(0, 10)" :key="grade.id" class="group hover:bg-muted/20 transition-colors">
                    <td class="px-6 py-4">
                      <div class="font-bold text-gray-900">{{ grade.student_name }}</div>
                    </td>
                    <td class="px-6 py-4">
                      <div class="inline-flex items-center gap-2">
                        <div class="h-1.5 w-1.5 rounded-full bg-indigo-400"></div>
                        <span class="text-gray-700">{{ grade.subject_name }}</span>
                      </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                      <div class="inline-flex items-center gap-1 font-black text-gray-900">
                        {{ grade.percentage }}%
                      </div>
                    </td>
                    <td class="px-6 py-4">
                      <div class="flex justify-center">
                        <Badge 
                          variant="outline"
                          :class="{
                            'bg-green-50 text-green-700 border-green-200': grade.grade === 'EE',
                            'bg-blue-50 text-blue-700 border-blue-200': grade.grade === 'ME',
                            'bg-orange-50 text-orange-700 border-orange-200': grade.grade === 'AE',
                            'bg-red-50 text-red-700 border-red-200': grade.grade === 'BE',
                          }"
                          class="font-bold text-[10px] min-w-[32px] justify-center"
                        >
                          {{ grade.grade }}
                        </Badge>
                      </div>
                    </td>
                    <td class="px-6 py-4 text-right">
                      <div class="text-[10px] flex flex-col items-end">
                        <span class="font-bold text-gray-600">{{ grade.term }}</span>
                        <span class="text-muted-foreground">{{ grade.year }}</span>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div v-if="filterGrades.length > 10" class="p-4 bg-muted/20 text-center border-t">
               <p class="text-xs text-muted-foreground">Showing latest 10 of {{ filterGrades.length }} performance records</p>
            </div>
          </div>
        </div>

        <!-- Sidebar (Right) -->
        <div class="space-y-6">
          <!-- Department Head Card -->
          <div class="rounded-xl border bg-card p-6 shadow-sm relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/5 rounded-full -mr-16 -mt-16 group-hover:scale-110 transition-transform duration-500"></div>
            
            <h3 class="font-bold text-gray-900 mb-6 flex items-center gap-2 relative">
              <Users class="h-4 w-4 text-indigo-600" />
              Department Head
            </h3>
            
            <div v-if="dept.head_of_department" class="flex flex-col items-center text-center relative">
               <div class="h-20 w-20 rounded-2xl bg-indigo-100 flex items-center justify-center text-indigo-600 font-black text-3xl shadow-sm border border-indigo-200 mb-4 transition-transform group-hover:rotate-3">
                  {{ dept.head_of_department.name.charAt(0) }}
               </div>
               <h4 class="font-black text-gray-900 text-lg">{{ dept.head_of_department.name }}</h4>
               <p class="text-sm font-bold text-indigo-600 uppercase tracking-widest mt-1">HEAD OF DEPARTMENT</p>
               
               <div class="mt-6 w-full space-y-2 border-t pt-6">
                  <div class="flex items-center justify-between text-xs">
                    <span class="text-muted-foreground">Email:</span>
                    <span class="font-bold text-gray-900">{{ dept.head_of_department.email }}</span>
                  </div>
                   <div class="flex items-center justify-between text-xs">
                    <span class="text-muted-foreground">Staff Status:</span>
                    <Badge variant="outline" class="bg-green-50 text-green-700 border-green-100 text-[10px]">ACTIVE</Badge>
                  </div>
               </div>
               
               <Button variant="outline" size="sm" class="mt-6 w-full" as-child>
                  <Link :href="`/departments/${dept.id}/edit`">Change Head</Link>
               </Button>
            </div>
            <div v-else class="py-8 text-center bg-muted/30 rounded-lg border border-dashed border-muted-foreground/30">
               <p class="text-sm text-muted-foreground font-medium">No Department Head assigned.</p>
               <Button variant="link" size="sm" as-child>
                  <Link :href="`/departments/${dept.id}/edit`">Assign Now</Link>
               </Button>
            </div>
          </div>

          <!-- Staff Members List -->
          <div class="rounded-xl border bg-card shadow-sm overflow-hidden">
            <div class="p-6 border-b flex items-center justify-between bg-muted/30">
               <h3 class="font-bold text-gray-900 flex items-center gap-2">
                <Users class="h-4 w-4 text-indigo-600" />
                Staff Members
                <span class="ml-1 px-1.5 py-0.5 rounded-full bg-indigo-600 text-white text-[10px]">{{ teachers.length }}</span>
              </h3>
            </div>
            
            <div class="divide-y divide-gray-100">
               <div v-if="teachers.length === 0" class="p-8 text-center text-muted-foreground text-sm">
                  No teachers assigned to this department.
               </div>
               <div v-for="teacher in teachers" :key="teacher.id" class="p-4 flex items-center gap-3 hover:bg-muted/30 transition-colors">
                  <div class="h-9 w-9 rounded-full bg-muted flex items-center justify-center font-bold text-indigo-600 text-sm">
                    {{ teacher.name.charAt(0) }}
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-gray-900 truncate">{{ teacher.name }}</p>
                    <div class="flex items-center gap-2 mt-0.5">
                       <span class="text-[10px] text-muted-foreground flex items-center gap-1">
                          <BookOpen class="h-3 w-3" /> {{ teacher.subjects_count }} sub.
                       </span>
                       <span class="text-[10px] text-muted-foreground flex items-center gap-1">
                          <Calendar class="h-3 w-3" /> {{ teacher.classes_count }} cl.
                       </span>
                    </div>
                  </div>
                  <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                      <Button variant="ghost" size="icon" class="h-8 w-8 rounded-full"><MoreHorizontal class="h-3.5 w-3.5" /></Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                       <DropdownMenuItem>View Profile</DropdownMenuItem>
                       <DropdownMenuItem>Message Staff</DropdownMenuItem>
                    </DropdownMenuContent>
                  </DropdownMenu>
               </div>
            </div>
          </div>

        </div>
      </div>

      <!-- Add Subject Modal -->
      <div v-if="showAddSubjectModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 animate-in fade-in duration-200">
        <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4 shadow-2xl animate-in zoom-in-95 duration-200">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
              <BookOpen class="h-5 w-5 text-indigo-600" />
              Manage Subjects
            </h3>
            <Button variant="ghost" size="icon" @click="showAddSubjectModal = false" class="rounded-full">
              <X class="h-5 w-5" />
            </Button>
          </div>
          
          <div class="space-y-4">
            <div class="relative">
              <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <Input v-model="subjectSearch" placeholder="Search curriculum subjects..." class="pl-9 rounded-xl border-indigo-100" />
            </div>
            
            <div class="max-h-[300px] overflow-y-auto space-y-1 pr-1 custom-scrollbar">
              <div v-if="filteredCurriculumSubjects.length === 0" class="py-12 text-center text-muted-foreground text-sm bg-muted/20 rounded-xl">
                 No unassigned subjects found.
              </div>
              <div 
                v-for="sub in filteredCurriculumSubjects" 
                :key="sub.id" 
                @click="addSubject(sub.id)"
                class="flex items-center justify-between p-3 rounded-xl hover:bg-indigo-50 border border-transparent hover:border-indigo-100 cursor-pointer transition-all group"
              >
                <div class="flex items-center gap-3">
                  <div class="h-8 w-8 rounded-lg bg-white border border-gray-100 flex items-center justify-center text-[10px] font-bold text-indigo-600 shadow-sm">
                    {{ sub.code }}
                  </div>
                  <span class="text-sm font-bold text-gray-800 group-hover:text-indigo-700">{{ sub.name }}</span>
                </div>
                <div class="h-6 w-6 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all">
                   <Plus class="h-3.5 w-3.5" />
                </div>
              </div>
            </div>
            
            <div class="pt-4 border-t flex items-center justify-between">
              <p class="text-[10px] text-muted-foreground font-medium uppercase tracking-wider">Only active curriculum subjects shown</p>
              <Button variant="outline" size="sm" @click="showAddSubjectModal = false" class="rounded-xl px-6">Close</Button>
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
