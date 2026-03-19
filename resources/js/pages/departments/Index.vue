<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { 
  Eye, Edit, Trash2, Grid3x3, List, Download, Plus, Filter, X, 
  Search, Users, BookOpen, MoreHorizontal, Building2 
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
const viewMode = ref(filters.view ?? 'grid');
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

const toggleSelectAll = (checked) => {
  if (checked) {
    selectedDepartments.value = (page.props?.departments?.data ?? []).map(d => d.id);
  } else {
    selectedDepartments.value = [];
  }
};

const toggleSelect = (id) => {
  const idx = selectedDepartments.value.indexOf(id);
  if (idx > -1) {
    selectedDepartments.value.splice(idx, 1);
  } else {
    selectedDepartments.value.push(id);
  }
};

const isSelected = (id) => selectedDepartments.value.includes(id);

const toggleStatus = (dept) => {
  router.patch(`/departments/${dept.id}/toggle-status`, {}, {
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

const bulkDelete = () => {
  if (selectedDepartments.value.length === 0) return;
  if (!confirm(`Are you sure you want to delete ${selectedDepartments.value.length} departments?`)) return;
  
  router.post('/departments/bulk-delete', {
    ids: selectedDepartments.value
  }, {
    onSuccess: () => {
      selectedDepartments.value = [];
      apply();
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
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
      <!-- Header -->
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-center gap-4">
          <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-600">
            <Building2 class="h-6 w-6" />
          </div>
          <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">Departments</h1>
            <p class="text-sm text-muted-foreground">Manage school departments and staff allocations</p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <Button variant="outline" size="sm" @click="router.get(exportUrl)">
            <Download class="mr-2 h-4 w-4" />
            Export
          </Button>
          <Button size="sm" as-child>
            <Link href="/departments/create">
              <Plus class="mr-2 h-4 w-4" />
              Add Department
            </Link>
          </Button>
        </div>
      </div>

      <!-- Stats Overview (Optional, matches classes/grades style) -->
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border bg-card p-4 transition-all hover:shadow-sm">
          <div class="flex items-center gap-3">
            <Building2 class="h-5 w-5 text-indigo-600" />
            <span class="text-sm text-muted-foreground font-medium">Total Departments</span>
          </div>
          <p class="mt-2 text-3xl font-bold text-indigo-600">{{ page.props?.stats?.total ?? 0 }}</p>
        </div>
        <div class="rounded-xl border bg-card p-4 transition-all hover:shadow-sm">
          <div class="flex items-center gap-3">
            <div class="h-2 w-2 rounded-full bg-green-500"></div>
            <span class="text-sm text-muted-foreground font-medium">Active</span>
          </div>
          <p class="mt-2 text-3xl font-bold text-green-600">{{ page.props?.stats?.active ?? 0 }}</p>
        </div>
        <div class="rounded-xl border bg-card p-4 transition-all hover:shadow-sm">
          <div class="flex items-center gap-3">
            <Users class="h-5 w-5 text-indigo-600" />
            <span class="text-sm text-muted-foreground font-medium">Teachers</span>
          </div>
          <p class="mt-2 text-3xl font-bold text-gray-900">{{ page.props?.stats?.teachers ?? 0 }}</p>
        </div>
        <div class="rounded-xl border bg-card p-4 transition-all hover:shadow-sm">
          <div class="flex items-center gap-3">
            <BookOpen class="h-5 w-5 text-purple-600" />
            <span class="text-sm text-muted-foreground font-medium">Subjects</span>
          </div>
          <p class="mt-2 text-3xl font-bold text-gray-900">{{ page.props?.stats?.subjects ?? 0 }}</p>
        </div>
      </div>

      <!-- Search & Filters -->
      <div class="flex flex-col gap-4 md:flex-row md:items-center">
        <div class="relative flex-1 md:max-w-sm">
          <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
          <Input v-model="search" placeholder="Search departments..." class="pl-9" />
        </div>
        
        <div class="flex items-center gap-2">
          <select v-model="statusFilter" @change="apply" class="h-10 rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
            <option value="all">All Statuses</option>
            <option value="active">Active Only</option>
            <option value="inactive">Inactive Only</option>
          </select>
          
          <div class="flex items-center rounded-md border bg-muted/50 p-1">
            <Button
              variant="ghost"
              size="icon"
              class="h-8 w-8 rounded-sm"
              :class="{ 'bg-background shadow-sm': viewMode === 'grid' }"
              @click="viewMode = 'grid'; apply()"
            >
              <Grid3x3 class="h-4 w-4" />
            </Button>
            <Button
              variant="ghost"
              size="icon"
              class="h-8 w-8 rounded-sm"
              :class="{ 'bg-background shadow-sm': viewMode === 'list' }"
              @click="viewMode = 'list'; apply()"
            >
              <List class="h-4 w-4" />
            </Button>
          </div>
        </div>
      </div>

      <!-- Bulk Actions -->
      <div v-if="selectedDepartments.length > 0" class="flex items-center justify-between rounded-xl border border-primary/20 bg-primary/5 p-4 animate-in fade-in slide-in-from-top-2">
        <div class="flex items-center gap-3">
          <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10 text-primary">
            <Building2 class="h-4 w-4" />
          </div>
          <div>
            <p class="text-sm font-medium">{{ selectedDepartments.length }} department{{ selectedDepartments.length === 1 ? '' : 's' }} selected</p>
            <p class="text-xs text-muted-foreground">Manage selected departments in bulk</p>
          </div>
        </div>
        <div class="flex gap-2">
          <Button variant="outline" size="sm" @click="selectedDepartments = []">Cancel</Button>
          <Button variant="destructive" size="sm" @click="bulkDelete">
            <Trash2 class="mr-2 h-4 w-4" />
            Delete Selected
          </Button>
        </div>
      </div>

      <!-- Grid View -->
      <div v-if="viewMode === 'grid'" class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
        <div v-if="!(page.props?.departments?.data?.length)" class="col-span-full flex flex-col items-center justify-center py-20 text-center">
          <div class="flex h-20 w-20 items-center justify-center rounded-full bg-muted">
            <Building2 class="h-10 w-10 text-muted-foreground/50" />
          </div>
          <h3 class="mt-4 text-lg font-semibold text-gray-900">No departments found</h3>
          <p class="text-muted-foreground">Try adjusting your search or filters to find what you're looking for.</p>
        </div>
        
        <div
          v-for="dept in page.props?.departments?.data ?? []"
          :key="dept.id"
          class="group relative flex flex-col rounded-xl border bg-card p-5 transition-all hover:shadow-lg hover:border-indigo-200"
        >
          <div class="flex items-start justify-between">
            <div class="flex items-center gap-3">
              <input
                type="checkbox"
                :checked="isSelected(dept.id)"
                @change="toggleSelect(dept.id)"
                class="mt-1 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
              />
              <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 font-bold group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                {{ dept.code?.substring(0, 2) || (dept.name?.substring(0, 2)).toUpperCase() }}
              </div>
              <div>
                <h3 class="font-bold text-gray-900 line-clamp-1">{{ dept.name }}</h3>
                <p class="text-xs text-muted-foreground">Code: {{ dept.code || '—' }}</p>
              </div>
            </div>
            
            <DropdownMenu>
              <DropdownMenuTrigger as-child>
                <Button variant="ghost" size="icon" class="h-8 w-8"><MoreHorizontal class="h-4 w-4" /></Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="end" class="w-48">
                <DropdownMenuItem as-child>
                  <Link :href="`/departments/${dept.id}`">
                    <Eye class="mr-2 h-4 w-4" /> View Details
                  </Link>
                </DropdownMenuItem>
                <DropdownMenuItem as-child>
                  <Link :href="`/departments/${dept.id}/edit`">
                    <Edit class="mr-2 h-4 w-4" /> Edit Department
                  </Link>
                </DropdownMenuItem>
                <DropdownMenuItem @click="toggleStatus(dept)">
                  <Building2 class="mr-2 h-4 w-4" /> {{ dept.is_active ? 'Deactivate' : 'Activate' }}
                </DropdownMenuItem>
                <DropdownMenuItem @click="openDeleteModal(dept)" class="text-red-600 focus:text-red-600">
                  <Trash2 class="mr-2 h-4 w-4" /> Delete
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>

          <div class="mt-4 flex-1">
            <p class="text-sm text-muted-foreground line-clamp-2 min-h-[2.5rem]">{{ dept.description || 'No description provided.' }}</p>
            
            <div class="mt-4 space-y-3">
              <div class="flex items-center justify-between text-sm">
                <div class="flex items-center gap-2 text-muted-foreground">
                  <Users class="h-4 w-4" />
                  <span>Head:</span>
                </div>
                <span class="font-medium text-gray-900">{{ dept.head_of_department ?? 'Not assigned' }}</span>
              </div>
              
              <div class="grid grid-cols-2 gap-2">
                <div class="rounded-lg bg-indigo-50/50 p-2 text-center transition-colors group-hover:bg-indigo-50">
                  <p class="text-[10px] uppercase tracking-wider text-indigo-500 font-bold">Teachers</p>
                  <p class="text-lg font-bold text-indigo-700">{{ dept.teachers_count ?? 0 }}</p>
                </div>
                <div class="rounded-lg bg-purple-50/50 p-2 text-center transition-colors group-hover:bg-purple-50">
                  <p class="text-[10px] uppercase tracking-wider text-purple-500 font-bold">Subjects</p>
                  <p class="text-lg font-bold text-purple-700">{{ dept.subjects_count ?? 0 }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="mt-5 flex items-center justify-between pt-4 border-t border-gray-100">
             <Badge 
                variant="outline"
                @click="toggleStatus(dept)"
                class="cursor-pointer transition-colors"
                :class="dept.is_active ? 'bg-green-50 text-green-700 border-green-200 hover:bg-green-100' : 'bg-gray-50 text-gray-600 border-gray-200 hover:bg-gray-100'"
              >
                {{ dept.is_active ? 'Active' : 'Inactive' }}
              </Badge>
              <Button variant="outline" size="sm" as-child>
                <Link :href="`/departments/${dept.id}`">View Details</Link>
              </Button>
          </div>
        </div>
      </div>

      <!-- List View -->
      <div v-else class="rounded-xl border bg-card overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-muted/50 border-b">
              <tr>
                <th class="px-4 py-3 text-left">
                  <input
                    type="checkbox"
                    :checked="allSelected"
                    @change="toggleSelectAll($event.target.checked)"
                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
                  />
                </th>
                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground uppercase tracking-wider">Department</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground uppercase tracking-wider">Code</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground uppercase tracking-wider">Head</th>
                <th class="px-4 py-3 text-center text-sm font-medium text-muted-foreground uppercase tracking-wider">Teachers</th>
                <th class="px-4 py-3 text-center text-sm font-medium text-muted-foreground uppercase tracking-wider">Subjects</th>
                <th class="px-4 py-3 text-center text-sm font-medium text-muted-foreground uppercase tracking-wider">Status</th>
                <th class="px-4 py-3 text-right text-sm font-medium text-muted-foreground uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 italic-none">
              <tr v-if="!(page.props?.departments?.data?.length)">
                <td colspan="8" class="text-center py-10 text-muted-foreground font-medium">No departments found</td>
              </tr>
              <tr v-for="dept in page.props?.departments?.data ?? []" :key="dept.id" class="group transition-colors hover:bg-muted/30">
                <td class="px-4 py-3">
                  <input
                    type="checkbox"
                    :checked="isSelected(dept.id)"
                    @change="toggleSelect(dept.id)"
                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
                  />
                </td>
                <td class="px-4 py-3">
                  <div class="font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">{{ dept.name }}</div>
                  <div class="text-xs text-muted-foreground line-clamp-1 max-w-xs">{{ dept.description }}</div>
                </td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ dept.code ?? '—' }}</td>
                <td class="px-4 py-3 text-sm font-medium text-gray-700">{{ dept.head_of_department ?? 'Not assigned' }}</td>
                <td class="px-4 py-3 text-center">
                  <span class="inline-flex items-center justify-center rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-bold text-indigo-700">
                    {{ dept.teachers_count ?? 0 }}
                  </span>
                </td>
                <td class="px-4 py-3 text-center">
                   <span class="inline-flex items-center justify-center rounded-full bg-purple-50 px-2.5 py-0.5 text-xs font-bold text-purple-700">
                    {{ dept.subjects_count ?? 0 }}
                  </span>
                </td>
                <td class="px-4 py-3 text-center">
                  <Badge 
                    variant="outline"
                    @click="toggleStatus(dept)"
                    class="cursor-pointer font-bold text-[10px]"
                    :class="dept.is_active ? 'bg-green-50 text-green-700 border-green-200 hover:bg-green-100' : 'bg-gray-50 text-gray-600 border-gray-200 hover:bg-gray-100'"
                  >
                    {{ dept.is_active ? 'Active' : 'Inactive' }}
                  </Badge>
                </td>
                <td class="px-4 py-3 text-right">
                  <div class="flex items-center justify-end gap-1">
                    <Button variant="ghost" size="icon" as-child class="h-8 w-8 text-indigo-600">
                      <Link :href="`/departments/${dept.id}`"><Eye class="h-4 w-4" /></Link>
                    </Button>
                    <Button variant="ghost" size="icon" as-child class="h-8 w-8 text-blue-600">
                      <Link :href="`/departments/${dept.id}/edit`"><Edit class="h-4 w-4" /></Link>
                    </Button>
                    <Button variant="ghost" size="icon" @click="openDeleteModal(dept)" class="h-8 w-8 text-red-600">
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
      <div v-if="page.props?.departments?.total > 0" class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between py-2">
        <div class="text-sm text-muted-foreground">
          Showing <span class="font-medium text-gray-900">{{ page.props?.departments?.from ?? 0 }}</span> to <span class="font-medium text-gray-900">{{ page.props?.departments?.to ?? 0 }}</span> of <span class="font-medium text-gray-900">{{ page.props?.departments?.total ?? 0 }}</span> departments
        </div>
        <div class="flex items-center gap-1">
            <Button 
                v-for="(link, i) in page.props.departments.links" 
                :key="i"
                variant="outline" 
                size="sm" 
                :disabled="!link.url"
                :class="{ 'bg-indigo-600 text-white hover:bg-indigo-700 hover:text-white border-indigo-600': link.active }"
                @click="link.url && router.get(link.url)"
                class="min-w-[32px] font-bold"
            >
                <span v-html="link.label"></span>
            </Button>
        </div>
      </div>

      <!-- Delete Modal -->
      <div v-if="showDeleteModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 animate-in fade-in duration-200">
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full mx-4 shadow-2xl animate-in zoom-in-95 duration-200">
          <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 text-red-600 mb-4">
            <Trash2 class="h-6 w-6" />
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-2">Delete Department?</h3>
          <p class="text-gray-600 mb-6 leading-relaxed">
            Are you sure you want to delete <span class="font-bold text-gray-900">{{ deleteTarget?.name }}</span>? This action will remove the department and staff mappings.
          </p>
          <div class="flex gap-3">
            <Button variant="outline" @click="showDeleteModal = false" class="flex-1 rounded-xl">Cancel</Button>
            <Button variant="destructive" @click="confirmDelete" class="flex-1 rounded-xl shadow-lg shadow-red-200">Yes, Delete</Button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.italic-none {
  font-style: normal;
}
</style>
