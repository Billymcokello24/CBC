<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { 
  Eye, Edit, Trash2, Grid3x3, List, Download, Plus, Filter, X, 
  Search, Users, BookOpen, MoreHorizontal, Building2, ShieldCheck, ShieldOff,
  CheckCircle2, ChevronDown, Check, Square, Minus
} from 'lucide-vue-next';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";

const page = usePage();
const filters = page.props?.filters ?? {};
const search = ref(filters.search ?? '');
const perPage = ref(filters.per_page ?? 20);
const viewMode = ref('list');
const statusFilter = ref(filters.status ?? 'all');
const showFilters = ref(false);
const selectedDepartments = ref([]);
const showDeleteModal = ref(false);
const deleteTarget = ref(null);

let debounceTimer = null;

const apply = () => {
  router.get('/departments', {
    search: search.value,
    per_page: perPage.value,
    view: viewMode.value,
    status: statusFilter.value
  }, { 
    replace: true, 
    preserveState: true,
    preserveScroll: true 
  });
};

watch(search, () => {
  if (debounceTimer) clearTimeout(debounceTimer);
  debounceTimer = setTimeout(apply, 300);
});

const allSelectableDeptIds = computed(() => (page.props.departments?.data ?? []).map((dept) => dept.id));
const allSelected = computed(() => allSelectableDeptIds.value.length > 0 && allSelectableDeptIds.value.every((id) => selectedDepartments.value.includes(id)));

const toggleAllDepartments = () => {
  selectedDepartments.value = allSelected.value ? [] : [...allSelectableDeptIds.value];
};

const toggleDeptSelection = (id) => {
  const idx = selectedDepartments.value.indexOf(id);
  if (idx > -1) {
    selectedDepartments.value.splice(idx, 1);
  } else {
    selectedDepartments.value.push(id);
  }
};

const isSelected = (id) => selectedDepartments.value.includes(id);

const toggleStatus = (dept) => {
  router.patch(`/departments/${dept.id}/${dept.is_active ? 'deactivate' : 'activate'}`, {}, {
    preserveScroll: true,
    onSuccess: () => apply()
  });
};

const openDeleteModal = (dept) => {
  deleteTarget.value = dept;
  showDeleteModal.value = true;
};

const confirmDelete = () => {
  if (!deleteTarget.value) return;
  router.delete(`/departments/${deleteTarget.value.id}`, {
    onSuccess: () => {
      showDeleteModal.value = false;
      deleteTarget.value = null;
      apply();
    }
  });
};

const runBulkAction = (action) => {
  if (selectedDepartments.value.length === 0) return;
  
  if (action === 'delete' && !confirm(`Are you sure you want to delete ${selectedDepartments.value.length} departments?`)) {
    return;
  }

  router.post('/departments/bulk-action', {
    department_ids: selectedDepartments.value,
    action: action
  }, {
    preserveScroll: true,
    onSuccess: () => {
      selectedDepartments.value = [];
    }
  });
};

const exportUrl = computed(() => {
  const params = new URLSearchParams({
    search: search.value,
    status: statusFilter.value
  });
  return `/departments/export?${params.toString()}`;
});

const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Departments', href: '/departments' },
];
</script>

<template>
  <Head title="Departments" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="w-full py-8 sm:py-12 px-4 sm:px-6 lg:px-8 space-y-8 sm:space-y-12 animate-in fade-in duration-500">
      <!-- Header -->
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-center gap-4">
          <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-600 shadow-lg text-white">
            <Building2 class="h-6 w-6" />
          </div>
          <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Departments</h1>
            <p class="text-sm text-muted-foreground mt-1">Manage school departments and staff allocations</p>
          </div>
        </div>
        </div>

      <!-- Stats Overview -->
      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border bg-white p-6 shadow-sm border-slate-100">
          <div class="flex items-center gap-3">
            <div class="p-2 rounded-lg bg-slate-50 text-slate-400">
              <Building2 class="h-5 w-5" />
            </div>
            <span class="text-xs text-slate-500 font-bold uppercase tracking-wider">Total Departments</span>
          </div>
          <p class="mt-4 text-3xl font-bold text-slate-900">{{ page.props?.stats?.total ?? 0 }}</p>
        </div>
        <div class="rounded-2xl border bg-white p-6 shadow-sm border-slate-100">
          <div class="flex items-center gap-3">
            <div class="h-2 w-2 rounded-full bg-emerald-500"></div>
            <span class="text-xs text-slate-500 font-bold uppercase tracking-wider">Active Status</span>
          </div>
          <p class="mt-4 text-3xl font-bold text-emerald-600">{{ page.props?.stats?.active ?? 0 }}</p>
        </div>
        <div class="rounded-2xl border bg-white p-6 shadow-sm border-slate-100">
          <div class="flex items-center gap-3">
            <div class="p-2 rounded-lg bg-blue-50 text-blue-600">
               <Users class="h-5 w-5" />
            </div>
            <span class="text-xs text-slate-500 font-bold uppercase tracking-wider">Total Teachers</span>
          </div>
          <p class="mt-4 text-3xl font-bold text-slate-900">{{ page.props?.stats?.teachers ?? 0 }}</p>
        </div>
        <div class="rounded-2xl border bg-white p-6 shadow-sm border-slate-100">
          <div class="flex items-center gap-3">
            <div class="p-2 rounded-lg bg-amber-50 text-amber-600">
              <BookOpen class="h-5 w-5" />
            </div>
            <span class="text-xs text-slate-500 font-bold uppercase tracking-wider">Academic Subjects</span>
          </div>
          <p class="mt-4 text-3xl font-bold text-slate-900">{{ page.props?.stats?.subjects ?? 0 }}</p>
        </div>
      </div>

      <!-- Search & Filters -->
      <div class="flex flex-col gap-4 md:flex-row md:items-center justify-between p-4 bg-white rounded-xl border shadow-sm">
        <div class="flex flex-1 items-center gap-2">
          <div class="relative flex-1 max-w-md">
            <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
            <Input v-model="search" placeholder="Search departments by name or code..." class="pl-9 h-11 border-slate-200 text-sm rounded-xl focus:ring-blue-600 transition-all" />
          </div>
          
          <div class="flex items-center rounded-xl bg-slate-100 p-1">
            <Button
              variant="ghost"
              size="icon"
              class="h-9 w-9 rounded-lg transition-all"
              :class="viewMode === 'grid' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-400 hover:text-slate-600'"
              @click="viewMode = 'grid'; apply()"
            >
              <Grid3x3 class="h-4 w-4" />
            </Button>
            <Button
              variant="ghost"
              size="icon"
              class="h-9 w-9 rounded-lg transition-all"
              :class="viewMode === 'list' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-400 hover:text-slate-600'"
              @click="viewMode = 'list'; apply()"
            >
              <List class="h-4 w-4" />
            </Button>
          </div>

          <Button variant="outline" class="h-11 rounded-xl border-slate-200 text-xs font-medium px-4 hover:bg-slate-50 transition-all shadow-sm" @click="showFilters = !showFilters">
            <Filter class="mr-2 h-4 w-4 text-blue-600" />
            {{ showFilters ? 'Hide Filters' : 'Show Filters' }}
          </Button>

          <Button variant="ghost" class="h-11 text-slate-400 hover:text-slate-600 font-bold uppercase text-[10px] tracking-widest px-4" @click="search = ''; apply()">Reset</Button>
        </div>

        <div class="flex items-center gap-3 ml-auto">
          <Button variant="outline" class="h-11 rounded-xl border-slate-200 text-xs font-bold uppercase tracking-widest px-4 hover:bg-slate-50 transition-all shadow-sm" @click="router.get(exportUrl)">
            <Download class="mr-1 h-4 w-4" />
            Export
          </Button>
          <Button class="h-11 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-black uppercase tracking-widest px-6 shadow-lg shadow-blue-200" as-child>
            <Link href="/departments/create">
              <Plus class="mr-1 h-4 w-4" />
              Add Dept
            </Link>
          </Button>

          <div v-if="selectedDepartments.length > 0" class="animate-in slide-in-from-right-4 duration-300">
               <DropdownMenu>
                  <DropdownMenuTrigger as-child>
                      <Button class="bg-blue-600 text-white hover:bg-blue-700 font-bold text-[10px] uppercase tracking-widest h-11 px-6 shadow-lg border-0 rounded-xl">
                          Batch Actions ({{ selectedDepartments.length }}) <ChevronDown class="ml-2 h-4 w-4" />
                      </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end" class="w-56 font-pulsar">
                      <DropdownMenuItem @click="runBulkAction('activate')"><CheckCircle2 class="mr-2 h-4 w-4 text-emerald-500" /> Activate Selected</DropdownMenuItem>
                      <DropdownMenuItem @click="runBulkAction('deactivate')"><ShieldOff class="mr-2 h-4 w-4 text-amber-500" /> Deactivate Selected</DropdownMenuItem>
                      <DropdownMenuItem class="text-rose-600 font-bold" @click="runBulkAction('delete')"><Trash2 class="mr-2 h-4 w-4" /> Delete Selected</DropdownMenuItem>
                  </DropdownMenuContent>
              </DropdownMenu>
          </div>
        </div>
      </div>
<div v-if="showFilters" class="grid gap-4 rounded-xl border bg-card p-4 md:grid-cols-2 lg:grid-cols-3 animate-in slide-in-from-top-2 duration-300">
        <div class="space-y-1.5">
          <label class="text-[9px] font-black uppercase text-slate-400 tracking-widest pl-1">Results Per Page</label>
          <select v-model="perPage" @change="apply" class="h-10 w-full rounded-xl border-slate-200 bg-slate-50 px-3 text-[10px] font-black uppercase tracking-widest focus:ring-blue-600">
            <option v-for="n in [10, 20, 50, 100, 500]" :key="n" :value="n">{{ n }} Per Page</option>
          </select>
        </div>
        <div class="space-y-1.5">
          <label class="text-[9px] font-black uppercase text-slate-400 tracking-widest pl-1">Department Status</label>
          <select v-model="statusFilter" @change="apply" class="h-10 w-full rounded-xl border-slate-200 bg-slate-50 px-3 text-[10px] font-black uppercase tracking-widest focus:ring-blue-600">
            <option value="all">All Statuses</option>
            <option value="active">Active Departments</option>
            <option value="inactive">Inactive Departments</option>
          </select>
        </div>
      </div>
      <!-- Grid View -->
      <div v-if="viewMode === 'grid'" class="space-y-4">
        <div class="flex items-center gap-3 px-2 py-1">
            <button @click="toggleAllDepartments" class="h-5 w-5 rounded-lg border-2 flex items-center justify-center transition-all bg-white shadow-sm" :class="allSelected ? 'bg-blue-600 border-blue-600 text-white' : selectedDepartments.length > 0 ? 'border-blue-600 bg-blue-50 text-blue-600' : 'border-slate-300'">
                <Check v-if="allSelected" class="h-3.5 w-3.5 stroke-[4px]" />
                <Minus v-else-if="selectedDepartments.length > 0" class="h-3.5 w-3.5 stroke-[4px]" />
            </button>
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest cursor-pointer select-none" @click="toggleAllDepartments">Select All on Page</span>
        </div>
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
        <div v-if="!(page.props?.departments?.data?.length)" class="col-span-full flex flex-col items-center justify-center py-20 text-center">
          <div class="flex h-20 w-20 items-center justify-center rounded-full bg-slate-50 shadow-inner">
            <Building2 class="h-10 w-10 text-slate-200" />
          </div>
          <h3 class="mt-4 text-lg font-bold text-slate-900">No departments found</h3>
          <p class="text-sm text-slate-400">Try adjusting your search or filters to find what you're looking for.</p>
        </div>
        
        <div
          v-for="dept in page.props?.departments?.data ?? []"
          :key="dept.id"
          class="group relative flex flex-col rounded-2xl border bg-white p-6 transition-all hover:shadow-lg border-slate-100"
        >
          <div class="flex items-start justify-between mb-6">
            <div class="flex items-start gap-4">
              <div class="pt-1">
                <button @click="toggleDeptSelection(dept.id)" class="mt-1 h-5 w-5 rounded-lg border-2 flex items-center justify-center transition-all bg-white shadow-sm" :class="isSelected(dept.id) ? 'bg-blue-600 border-blue-600 text-white' : 'border-slate-300'">
                    <Check v-if="isSelected(dept.id)" class="h-3.5 w-3.5 stroke-[4px]" />
                </button>
              </div>
              <div>
                <h3 class="text-xl font-bold text-slate-900 group-hover:text-blue-600 transition-colors leading-tight uppercase truncate max-w-[180px]">{{ dept.name }}</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1">{{ dept.code || 'NO_CODE' }}</p>
              </div>
            </div>
            
            <DropdownMenu>
              <DropdownMenuTrigger as-child>
                <Button variant="ghost" size="icon" class="h-9 w-9 rounded-xl text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-all"><MoreHorizontal class="h-4 w-4" /></Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="end" class="w-56 rounded-xl p-2 shadow-xl border-slate-100">
                <DropdownMenuItem class="rounded-lg" as-child>
                  <Link :href="`/departments/${dept.id}`">
                    <Eye class="mr-2 h-4 w-4 text-blue-500" /> View Details
                  </Link>
                </DropdownMenuItem>
                <DropdownMenuItem class="rounded-lg" as-child>
                  <Link :href="`/departments/${dept.id}/edit`">
                    <Edit class="mr-2 h-4 w-4 text-amber-500" /> Edit Department
                  </Link>
                </DropdownMenuItem>
                <DropdownMenuItem class="rounded-lg" @click="toggleStatus(dept)">
                  <Building2 class="mr-2 h-4 w-4 text-blue-500" /> {{ dept.is_active ? 'Deactivate' : 'Activate' }}
                </DropdownMenuItem>
                <DropdownMenuItem @click="openDeleteModal(dept)" class="text-rose-600 font-bold rounded-lg">
                  <Trash2 class="mr-2 h-4 w-4" /> Delete Department
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>

          <div class="space-y-6 flex-1">
            <div class="flex items-center gap-3 p-3 bg-slate-50/50 rounded-xl border border-slate-100 group-hover:bg-blue-50/30 transition-colors">
              <div class="h-8 w-8 rounded-lg bg-white border border-slate-100 flex items-center justify-center text-[10px] font-bold text-blue-600 shadow-sm">{{ dept.head_of_department?.charAt(0) || '?' }}</div>
              <div>
                <p class="text-[8px] font-bold text-slate-400 uppercase tracking-wider leading-none mb-1">Department Head</p>
                <p class="text-[10px] font-bold text-slate-900 uppercase truncate leading-none pt-0.5">{{ dept.head_of_department || 'Not Assigned' }}</p>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div class="rounded-xl border border-slate-100 p-3 bg-white shadow-sm">
                <p class="text-[8px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Teachers</p>
                <p class="text-base font-bold text-slate-900">{{ dept.teachers_count ?? 0 }}</p>
              </div>
              <div class="rounded-xl border border-slate-100 p-3 bg-white shadow-sm">
                <p class="text-[8px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Subjects</p>
                <p class="text-base font-bold text-blue-600">{{ dept.subjects_count ?? 0 }}</p>
              </div>
            </div>
          </div>

          <div class="mt-6 flex gap-1.5 flex-wrap">
              <Button variant="outline" size="sm" class="flex-1 h-8 rounded-xl border-slate-200 text-[10px] font-bold hover:bg-blue-600 hover:text-white hover:border-blue-600 transition-all" as-child>
                <Link :href="`/departments/${dept.id}`"><Eye class="mr-1 h-3 w-3" />View</Link>
              </Button>
              <Button variant="outline" size="sm" class="flex-1 h-8 rounded-xl border-slate-200 text-[10px] font-bold hover:bg-amber-500 hover:text-white hover:border-amber-500 transition-all" as-child>
                <Link :href="`/departments/${dept.id}/edit`"><Edit class="mr-1 h-3 w-3" />Edit</Link>
              </Button>
              <Button variant="outline" size="sm" class="h-8 rounded-xl border-slate-200 text-[10px] font-bold transition-all px-2"
                :class="dept.is_active ? 'text-amber-600 hover:bg-amber-50' : 'text-emerald-600 hover:bg-emerald-50'"
                @click="toggleStatus(dept)">
                <Building2 class="h-3 w-3" />
              </Button>
              <Button variant="outline" size="sm" class="h-8 rounded-xl border-slate-200 text-rose-600 hover:bg-rose-50 transition-all px-2" @click="openDeleteModal(dept)">
                <Trash2 class="h-3 w-3" />
              </Button>
          </div>
        </div>
      </div>
    </div>

    <!-- List View -->
    <div v-else class="rounded-2xl border bg-white overflow-hidden shadow-sm border-slate-100">
        <div class="overflow-x-auto">
          <table class="w-full border-collapse">
            <thead>
              <tr class="bg-slate-50 border-b border-slate-100">
                <th class="px-6 py-4 text-left w-12 text-center">
                    <button @click="toggleAllDepartments" class="h-5 w-5 rounded-lg border-2 flex items-center justify-center transition-all bg-white mx-auto shadow-sm" :class="allSelected ? 'bg-blue-600 border-blue-600 text-white' : selectedDepartments.length > 0 ? 'border-blue-600 bg-blue-50 text-blue-600' : 'border-slate-300'">
                        <Check v-if="allSelected" class="h-3.5 w-3.5 stroke-[4px]" />
                        <Minus v-else-if="selectedDepartments.length > 0" class="h-3.5 w-3.5 stroke-[4px]" />
                    </button>
                </th>
                <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Department</th>
                <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Code</th>
                <th class="px-6 py-4 text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest">Department Head</th>
                <th class="px-6 py-4 text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest">Teachers</th>
                <th class="px-6 py-4 text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest">Subjects</th>
                <th class="px-6 py-4 text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest">Status</th>
                <th class="px-6 py-4 text-right text-[10px] font-bold text-slate-400 uppercase tracking-widest">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              <tr v-if="!(page.props?.departments?.data?.length)">
                <td colspan="8" class="text-center py-16 text-slate-400 font-medium italic">No departments found matching your criteria</td>
              </tr>
              <tr v-for="dept in page.props?.departments?.data ?? []" :key="dept.id" class="group transition-all hover:bg-blue-50/30">
                <td class="px-6 py-4 text-center">
                    <button @click="toggleDeptSelection(dept.id)" class="h-5 w-5 rounded-lg border-2 flex items-center justify-center transition-all bg-white mx-auto shadow-sm" :class="isSelected(dept.id) ? 'bg-blue-600 border-blue-600 text-white' : 'border-slate-300'">
                        <Check v-if="isSelected(dept.id)" class="h-3.5 w-3.5 stroke-[4px]" />
                    </button>
                </td>
                <td class="px-6 py-4">
                  <div class="flex flex-col">
                    <span class="font-bold text-slate-900 group-hover:text-blue-600 transition-colors uppercase truncate max-w-xs">{{ dept.name }}</span>
                    <span class="text-[10px] text-slate-400 font-medium line-clamp-1 max-w-xs mt-0.5">{{ dept.description || 'Global Faculty Department' }}</span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ dept.code || '—' }}</span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-2">
                    <div class="h-7 w-7 rounded-lg bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-500 shadow-sm">{{ dept.head_of_department?.charAt(0) || '?' }}</div>
                    <span class="text-xs font-bold text-slate-700 uppercase">{{ dept.head_of_department || 'Not Assigned' }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 text-center">
                  <div class="inline-flex items-center justify-center h-8 min-w-[32px] rounded-lg bg-slate-50 border border-slate-100 px-2 text-xs font-bold text-slate-700">
                    {{ dept.teachers_count ?? 0 }}
                  </div>
                </td>
                <td class="px-6 py-4 text-center">
                   <div class="inline-flex items-center justify-center h-8 min-w-[32px] rounded-lg bg-blue-50 border border-blue-100 px-2 text-xs font-bold text-blue-700">
                    {{ dept.subjects_count ?? 0 }}
                  </div>
                </td>
                <td class="px-6 py-4 text-center">
                  <div class="flex items-center justify-center gap-2">
                    <div class="h-1.5 w-1.5 rounded-full" :class="dept.is_active ? 'bg-emerald-500' : 'bg-slate-300'"></div>
                    <span class="text-[10px] font-bold uppercase tracking-wider" :class="dept.is_active ? 'text-emerald-600' : 'text-slate-400'">{{ dept.is_active ? 'Active' : 'Inactive' }}</span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center justify-end gap-1">
                    <Button variant="ghost" size="sm" class="h-8 w-8 p-0 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-all" as-child title="View Details">
                      <Link :href="`/departments/${dept.id}`"><Eye class="h-4 w-4" /></Link>
                    </Button>
                    <Button variant="ghost" size="sm" class="h-8 w-8 p-0 rounded-lg text-slate-400 hover:text-amber-500 hover:bg-amber-50 transition-all" as-child title="Edit Department">
                      <Link :href="`/departments/${dept.id}/edit`"><Edit class="h-4 w-4" /></Link>
                    </Button>
                    <Button variant="ghost" size="sm" class="h-8 w-8 p-0 rounded-lg transition-all" :class="dept.is_active ? 'text-amber-500 hover:bg-amber-50' : 'text-emerald-500 hover:bg-emerald-50'" @click="toggleStatus(dept)" :title="dept.is_active ? 'Deactivate' : 'Activate'">
                      <component :is="dept.is_active ? ShieldOff : ShieldCheck" class="h-4 w-4" />
                    </Button>
                    <Button variant="ghost" size="sm" class="h-8 w-8 p-0 rounded-lg text-rose-400 hover:bg-rose-50 transition-all" @click="openDeleteModal(dept)" title="Delete Department">
                      <Trash2 class="h-4 w-4" />
                    </Button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="page.props?.departments?.total > 0" class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between py-6">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">
          Showing <span class="text-slate-900">{{ page.props?.departments?.from ?? 0 }}</span> to <span class="text-slate-900">{{ page.props?.departments?.to ?? 0 }}</span> of <span class="text-slate-900">{{ page.props?.departments?.total ?? 0 }}</span> Departments
        </p>
        <div class="flex items-center gap-2">
            <Button 
                v-for="(link, i) in page.props.departments.links" 
                :key="i"
                variant="outline" 
                size="sm" 
                :disabled="!link.url"
                :class="link.active ? 'bg-blue-600 text-white border-blue-600 shadow-md shadow-blue-100' : 'text-slate-500 border-slate-200 hover:bg-slate-50'"
                @click="link.url && router.get(link.url)"
                class="min-w-[40px] h-10 rounded-xl font-bold text-xs transition-all"
            >
                <span v-html="link.label"></span>
            </Button>
        </div>
      </div>

      <!-- Delete Modal -->
      <div v-if="showDeleteModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-50 animate-in fade-in duration-300">
        <div class="bg-white rounded-3xl p-8 max-w-sm w-full mx-4 shadow-2xl animate-in zoom-in-95 duration-200 border border-slate-100">
          <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-rose-50 text-rose-600 mb-6 shadow-sm">
            <Trash2 class="h-8 w-8" />
          </div>
          <h3 class="text-2xl font-black text-slate-900 mb-3 tracking-tight">Delete Department?</h3>
          <p class="text-sm text-slate-500 mb-8 leading-relaxed font-medium">
            Are you sure you want to delete <span class="font-bold text-slate-900">{{ deleteTarget?.name }}</span>? This action is permanent and will remove all staff allocations.
          </p>
          <div class="flex gap-4">
            <Button variant="ghost" @click="showDeleteModal = false" class="flex-1 h-12 rounded-xl text-slate-500 font-bold hover:bg-slate-50 transition-all">Cancel</Button>
            <Button variant="destructive" @click="confirmDelete" class="flex-1 h-12 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl shadow-lg shadow-rose-200 border-0 transition-all">Delete</Button>
          </div>
        </div>
      </div>
    </div>
</template>

<style scoped>
.italic-none {
  font-style: normal;
}
</style>
