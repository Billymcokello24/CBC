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
  CheckCircle2, ChevronDown, Check, Square, Minus, Rows3, Grid2x2,
  ChevronLeft, ChevronRight
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
  { title: 'Pulse', href: '/dashboard' },
  { title: 'Faculty Departments', href: '/departments' },
];
</script>

<template>
  <Head title="Departments" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-12 animate-in fade-in slide-in-from-bottom-4 duration-1000 max-w-[1600px] mx-auto pb-20 p-6">
      <!-- Simple Header -->
      <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div class="space-y-1">
          <h1 class="text-3xl font-black tracking-tight text-foreground uppercase italic underline decoration-blue-600 decoration-4 underline-offset-8">Faculty Departments</h1>
          <p class="text-sm font-bold text-muted-foreground/60 uppercase tracking-widest pt-2">
            Organize academic specialized units and staff leadership.
          </p>
        </div>
        
        <div class="flex items-center gap-3">
          <Button variant="outline" class="rounded-xl h-11 px-6 border-border font-black uppercase text-[10px] tracking-widest hover:bg-muted italic">
            <Rows3 class="mr-2 h-4 w-4 opacity-40" />
            Hierarchy Map
          </Button>
          <Link
            v-if="true"
            href="/departments/create"
            class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-8 h-11 text-[10px] font-black uppercase tracking-widest text-white shadow-lg shadow-blue-600/20 transition-all hover:scale-[1.02] active:scale-[0.98] italic"
          >
            <Plus class="mr-2 h-4 w-4" />
            New Faculty
          </Link>
        </div>
      </div>

      <!-- TailPanel Stats -->
      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5 group hover:border-blue-600/20 transition-all duration-500">
          <div class="flex items-center justify-between mb-4">
            <div class="h-10 w-10 rounded-xl bg-blue-600/5 flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-all duration-500 shadow-inner">
              <Building2 class="h-5 w-5 opacity-40 group-hover:opacity-100" />
            </div>
            <span class="text-[10px] font-black text-muted-foreground/30 uppercase tracking-widest italic">Units</span>
          </div>
          <p class="text-[10px] font-black text-muted-foreground/60 uppercase tracking-widest mb-1 italic">Total Departments</p>
          <h3 class="text-2xl font-black text-foreground uppercase italic">{{ page.props?.stats?.total ?? 0 }} Nodes</h3>
        </div>

        <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5 group hover:border-emerald-500/20 transition-all duration-500">
          <div class="flex items-center justify-between mb-4">
            <div class="h-10 w-10 rounded-xl bg-emerald-500/5 flex items-center justify-center text-emerald-500 group-hover:bg-emerald-500 group-hover:text-white transition-all duration-500 shadow-inner">
              <ShieldCheck class="h-5 w-5 opacity-40 group-hover:opacity-100" />
            </div>
            <span class="text-[10px] font-black text-muted-foreground/30 uppercase tracking-widest italic">Active</span>
          </div>
          <p class="text-[10px] font-black text-muted-foreground/60 uppercase tracking-widest mb-1 italic">Live Faculties</p>
          <h3 class="text-2xl font-black text-foreground uppercase italic">{{ page.props?.stats?.active ?? 0 }} Paths</h3>
        </div>

        <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5 group hover:border-orange-500/20 transition-all duration-500">
          <div class="flex items-center justify-between mb-4">
            <div class="h-10 w-10 rounded-xl bg-orange-500/5 flex items-center justify-center text-orange-500 group-hover:bg-orange-500 group-hover:text-white transition-all duration-500 shadow-inner">
              <Users class="h-5 w-5 opacity-40 group-hover:opacity-100" />
            </div>
            <span class="text-[10px] font-black text-muted-foreground/30 uppercase tracking-widest italic">Elite</span>
          </div>
          <p class="text-[10px] font-black text-muted-foreground/60 uppercase tracking-widest mb-1 italic">Total Staff</p>
          <h3 class="text-2xl font-black text-foreground uppercase italic">{{ page.props?.stats?.teachers ?? 0 }} Minds</h3>
        </div>

        <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5 group hover:border-indigo-600/20 transition-all duration-500">
          <div class="flex items-center justify-between mb-4">
            <div class="h-10 w-10 rounded-xl bg-indigo-600/5 flex items-center justify-center text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-500 shadow-inner">
              <BookOpen class="h-5 w-5 opacity-40 group-hover:opacity-100" />
            </div>
            <span class="text-[10px] font-black text-muted-foreground/30 uppercase tracking-widest italic">Assets</span>
          </div>
          <p class="text-[10px] font-black text-muted-foreground/60 uppercase tracking-widest mb-1 italic">Academic Subjects</p>
          <h3 class="text-2xl font-black text-foreground uppercase italic">{{ page.props?.stats?.subjects ?? 0 }} Gems</h3>
        </div>
      </div>

      <!-- Toolbar Section -->
      <div class="flex flex-col gap-6 rounded-2xl border border-border bg-card p-6 shadow-sm dark:border-white/5">
        <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
          <div class="flex flex-1 items-center gap-4 w-full md:max-w-4xl">
            <div class="relative flex-1">
              <Search class="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/60" />
              <Input v-model="search" placeholder="Filter faculties by name or code..." class="pl-12 h-12 bg-muted/20 border-border rounded-xl text-xs font-black focus:bg-background transition-all placeholder:text-[9px] placeholder:uppercase placeholder:tracking-widest italic" />
            </div>
            
            <div class="flex items-center p-1 bg-muted/20 rounded-xl border border-border">
              <Button variant="ghost" :class="['h-10 w-10 p-0 rounded-lg transition-all', viewMode === 'grid' ? 'bg-background text-foreground shadow-sm border border-border/50' : 'text-muted-foreground/40 hover:text-foreground']" @click="viewMode = 'grid'"><Grid2x2 class="h-4 w-4" /></Button>
              <Button variant="ghost" :class="['h-10 w-10 p-0 rounded-lg transition-all', viewMode === 'list' ? 'bg-background text-foreground shadow-sm border border-border/50' : 'text-muted-foreground/40 hover:text-foreground']" @click="viewMode = 'list'"><List class="h-4 w-4" /></Button>
            </div>

            <Button variant="outline" class="h-12 border-border font-black px-6 rounded-xl text-[10px] uppercase tracking-widest whitespace-nowrap italic" @click="showFilters = !showFilters">
              <Filter class="mr-2 h-4 w-4 opacity-40" /> {{ showFilters ? 'DENSE' : 'REFINE' }}
            </Button>
            <Button variant="ghost" class="h-12 text-muted-foreground/40 hover:text-foreground font-black uppercase text-[10px] tracking-widest px-4 italic" @click="search = ''; apply()">Reset</Button>
          </div>

          <div class="flex items-center gap-3 ml-auto">
            <Button variant="outline" class="h-12 border-border font-black px-6 rounded-xl text-[10px] uppercase tracking-widest hover:bg-muted italic" @click="router.get(exportUrl)">
              <Download class="mr-2 h-4 w-4 opacity-40" />
              Extract
            </Button>

            <div v-if="selectedDepartments.length > 0" class="animate-in slide-in-from-right-4 duration-300">
               <DropdownMenu>
                  <DropdownMenuTrigger as-child>
                    <Button class="bg-foreground text-background hover:bg-foreground/90 font-black text-[10px] uppercase tracking-[0.2em] h-12 px-8 shadow-xl border-0 rounded-xl italic transition-all active:scale-95 shadow-foreground/20">
                      Batch Control ({{ selectedDepartments.length }}) <ChevronDown class="ml-2 h-4 w-4" />
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end" class="w-64 p-2 rounded-xl border border-border shadow-2xl">
                    <DropdownMenuItem class="rounded-lg py-2.5 font-bold text-xs" @click="runBulkAction('activate')"><CheckCircle2 class="mr-3 h-4 w-4 text-emerald-500 opacity-60" /> Initialize Units</DropdownMenuItem>
                    <DropdownMenuItem class="rounded-lg py-2.5 font-bold text-xs" @click="runBulkAction('deactivate')"><ShieldOff class="mr-3 h-4 w-4 text-amber-500 opacity-60" /> Halt Units</DropdownMenuItem>
                    <DropdownMenuSeparator class="my-1 bg-border/50" />
                    <DropdownMenuItem class="text-rose-500 rounded-lg py-2.5 font-bold text-xs group" @click="runBulkAction('delete')"><Trash2 class="mr-3 h-4 w-4 opacity-60 group-hover:opacity-100" /> Purge Faculty</DropdownMenuItem>
                  </DropdownMenuContent>
              </DropdownMenu>
            </div>
          </div>
        </div>

        <div v-if="showFilters" class="grid gap-6 md:grid-cols-4 pt-4 border-t border-border/50 animate-in fade-in slide-in-from-top-2 duration-500">
          <div class="space-y-3">
            <label class="text-[10px] font-black text-muted-foreground/40 uppercase tracking-[0.2em] italic">Flow Status</label>
            <select v-model="statusFilter" @change="apply" class="h-12 w-full rounded-xl border border-border bg-muted/20 px-4 text-xs font-black focus:bg-background transition-all outline-none italic uppercase tracking-widest">
              <option value="all">Global Matrix</option>
              <option value="active">Initialized Only</option>
              <option value="inactive">Suspended Units</option>
            </select>
          </div>
          <div class="space-y-3">
            <label class="text-[10px] font-black text-muted-foreground/40 uppercase tracking-[0.2em] italic">Segments</label>
            <select v-model="perPage" @change="apply" class="h-12 w-full rounded-xl border border-border bg-muted/20 px-4 text-xs font-black focus:bg-background transition-all outline-none italic uppercase tracking-widest">
              <option v-for="n in [10, 20, 50, 100, 500]" :key="n" :value="n">{{ n }} Per Set</option>
            </select>
          </div>
        </div>
      </div>
      <!-- Grid View -->
      <div v-if="viewMode === 'grid'" class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
        <div class="flex items-center gap-3 px-2 py-1">
          <button @click="toggleAllDepartments" class="h-6 w-6 rounded-lg border-2 flex items-center justify-center transition-all bg-card dark:border-white/10" :class="allSelected ? 'bg-blue-600 border-blue-600 text-white shadow-blue-600/30' : selectedDepartments.length > 0 ? 'border-blue-600 bg-blue-500/10 text-blue-600' : 'border-border'">
            <Check v-if="allSelected" class="h-4 w-4 stroke-[3px]" />
            <Minus v-else-if="selectedDepartments.length > 0" class="h-4 w-4 stroke-[3px]" />
          </button>
          <span class="text-[10px] font-black text-muted-foreground/50 uppercase tracking-[0.2em] italic cursor-pointer select-none" @click="toggleAllDepartments">Global Flow Sync</span>
        </div>
        
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
          <div v-if="!(page.props?.departments?.data?.length)" class="col-span-full flex flex-col items-center justify-center py-32 text-center bg-muted/10 rounded-[3rem] border border-dashed border-border/50">
            <div class="h-24 w-24 rounded-full bg-muted flex items-center justify-center mb-6 shadow-inner">
              <Building2 class="h-10 w-10 text-muted-foreground/20" />
            </div>
            <h3 class="text-xl font-black text-foreground uppercase italic tracking-widest">No Faculty Detected</h3>
            <p class="text-[10px] font-black text-muted-foreground/40 uppercase tracking-widest mt-2 italic">The matrix registry is currently void of this unit type.</p>
          </div>

          <div
            v-for="dept in page.props?.departments?.data ?? []"
            :key="dept.id"
            class="group relative rounded-[2rem] border border-border bg-card p-8 transition-all duration-500 hover:shadow-2xl hover:scale-[1.02] dark:border-white/5 flex flex-col overflow-hidden"
          >
            <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-blue-600/5 blur-3xl group-hover:bg-blue-600/15 transition-all duration-700"></div>
            
            <div class="mb-8 flex items-start justify-between relative z-10">
              <div class="flex items-start gap-5">
                <button @click="toggleDeptSelection(dept.id)" class="mt-1 h-6 w-6 rounded-lg border-2 flex items-center justify-center transition-all bg-background dark:border-white/10 shadow-inner" :class="isSelected(dept.id) ? 'bg-blue-600 border-blue-600 text-white shadow-blue-600/30' : 'border-border'">
                  <Check v-if="isSelected(dept.id)" class="h-4 w-4 stroke-[3px]" />
                </button>
                <div>
                  <h2 class="text-2xl font-black text-foreground group-hover:text-blue-600 transition-colors leading-tight uppercase italic tracking-tighter truncate max-w-[200px]">{{ dept.name }}</h2>
                  <p class="text-[10px] font-black text-muted-foreground/40 uppercase tracking-[0.2em] mt-2 italic">{{ dept.code || 'NULL' }} UNIT</p>
                </div>
              </div>
              
              <DropdownMenu>
                <DropdownMenuTrigger as-child><Button variant="ghost" size="icon" class="h-10 w-10 rounded-xl text-muted-foreground hover:bg-muted hover:text-foreground"><MoreHorizontal class="h-5 w-5" /></Button></DropdownMenuTrigger>
                <DropdownMenuContent align="end" class="w-64 p-2 rounded-xl border border-border shadow-2xl">
                  <DropdownMenuItem class="rounded-lg py-2.5 font-bold text-xs" as-child><Link :href="`/departments/${dept.id}`"><Eye class="mr-3 h-4 w-4 text-blue-500 opacity-60" /> Unit Specs</Link></DropdownMenuItem>
                  <DropdownMenuItem class="rounded-lg py-2.5 font-bold text-xs" as-child><Link :href="`/departments/${dept.id}/edit`"><Edit class="mr-3 h-4 w-4 text-amber-500 opacity-60" /> Reconfigure Unit</Link></DropdownMenuItem>
                  <DropdownMenuItem class="rounded-lg py-2.5 font-bold text-xs" @click="toggleStatus(dept)">
                    <component :is="dept.is_active ? ShieldOff : ShieldCheck" class="mr-3 h-4 w-4" :class="dept.is_active ? 'text-amber-500' : 'text-emerald-500'" />
                    {{ dept.is_active ? 'Suspend Unit' : 'Initialize Unit' }}
                  </DropdownMenuItem>
                  <DropdownMenuSeparator class="my-1 bg-border/50" />
                  <DropdownMenuItem @click="openDeleteModal(dept)" class="text-rose-500 font-bold rounded-lg py-2.5 text-xs group">
                    <Trash2 class="mr-3 h-4 w-4 opacity-60 group-hover:opacity-100" /> Purge Unit
                  </DropdownMenuItem>
                </DropdownMenuContent>
              </DropdownMenu>
            </div>

            <div class="mt-auto space-y-6 relative z-10">
              <div class="flex items-center gap-3 p-4 bg-muted/20 rounded-2xl border border-border/50 group-hover:bg-background transition-colors shadow-inner">
                <div class="h-10 w-10 rounded-xl bg-background border border-border flex items-center justify-center text-[10px] font-black text-blue-600 shadow-sm uppercase italic">{{ dept.head_of_department?.charAt(0) || '?' }}</div>
                <div>
                  <p class="text-[8px] font-black text-muted-foreground/40 uppercase tracking-[0.2em] leading-none mb-1.5 italic">Unit Head</p>
                  <p class="text-[10px] font-black text-foreground uppercase truncate leading-none pt-0.5 italic tracking-tight">{{ dept.head_of_department || 'NULL NODE' }}</p>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div class="rounded-2xl border border-border bg-muted/20 p-4 shadow-inner dark:border-white/5 transition-all group-hover:bg-background">
                  <p class="text-[9px] font-black text-muted-foreground/40 uppercase tracking-[0.2em] mb-2 italic">Minds</p>
                  <p class="text-lg font-black text-foreground italic">{{ dept.teachers_count ?? 0 }}</p>
                </div>
                <div class="rounded-2xl border border-border bg-muted/20 p-4 shadow-inner dark:border-white/5 transition-all group-hover:bg-background">
                  <p class="text-[9px] font-black text-muted-foreground/40 uppercase tracking-[0.2em] mb-2 italic">Gems</p>
                  <p class="text-lg font-black text-blue-600 italic">{{ dept.subjects_count ?? 0 }}</p>
                </div>
              </div>

              <div class="flex gap-3 h-12">
                <Button variant="outline" class="flex-1 rounded-xl border-border font-black text-[10px] uppercase tracking-widest h-full hover:bg-blue-600 hover:text-white hover:border-blue-600 transition-all italic shadow-sm" as-child>
                  <Link :href="`/departments/${dept.id}`">Sync</Link>
                </Button>
                <Button variant="outline" class="flex-1 rounded-xl border-border font-black text-[10px] uppercase tracking-widest h-full hover:bg-amber-500 hover:text-white hover:border-amber-500 transition-all italic shadow-sm" as-child>
                  <Link :href="`/departments/${dept.id}/edit`">Mod</Link>
                </Button>
                <Button variant="outline" class="w-12 rounded-xl border-border transition-all flex items-center justify-center p-0 hover:bg-muted italic shadow-inner"
                  :class="dept.is_active ? 'text-amber-500 hover:bg-amber-50' : 'text-emerald-500 hover:bg-emerald-50'"
                  @click="toggleStatus(dept)">
                  <component :is="dept.is_active ? ShieldOff : ShieldCheck" class="h-4.5 w-4.5" />
                </Button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- List View -->
      <div v-else class="rounded-[2.5rem] border border-border bg-card shadow-2xl overflow-hidden dark:border-white/5 animate-in fade-in slide-in-from-bottom-4 duration-700">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-foreground border-b border-foreground/10 text-background">
                <th class="w-20 px-8 py-6 text-center border-r border-background/10">
                  <button @click="toggleAllDepartments" class="h-6 w-6 rounded-lg border-2 flex items-center justify-center transition-all bg-background mx-auto shadow-inner" :class="allSelected ? 'bg-blue-600 border-blue-600 text-white shadow-blue-600/30' : selectedDepartments.length > 0 ? 'border-blue-600 bg-blue-500/10 text-blue-600' : 'border-background/20'">
                    <Check v-if="allSelected" class="h-4 w-4 stroke-[3px]" />
                    <Minus v-else-if="selectedDepartments.length > 0" class="h-4 w-4 stroke-[3px]" />
                  </button>
                </th>
                <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest opacity-80 border-r border-background/10 italic">Faculty Intel</th>
                <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest opacity-80 border-r border-background/10 italic">Unit Code</th>
                <th class="px-8 py-6 text-[10px] font-black uppercase tracking-widest opacity-80 border-r border-background/10 italic">Unit Leadership</th>
                <th class="px-8 py-6 text-center text-[10px] font-black uppercase tracking-widest opacity-80 border-r border-background/10 italic">Minds</th>
                <th class="px-8 py-6 text-center text-[10px] font-black uppercase tracking-widest opacity-80 border-r border-background/10 italic">Gems</th>
                <th class="px-8 py-6 text-center text-[10px] font-black uppercase tracking-widest opacity-80 border-r border-background/10 italic">Status</th>
                <th class="px-8 py-6 text-right text-[10px] font-black uppercase tracking-widest opacity-80 italic">Control</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-border/30">
              <tr v-if="!(page.props?.departments?.data?.length)">
                <td colspan="8" class="px-8 py-32 text-center text-muted-foreground/20 italic font-black uppercase text-xl tracking-[0.2em] bg-muted/10">No Units Detected in Matrix</td>
              </tr>
              <tr v-for="dept in page.props?.departments?.data ?? []" :key="dept.id" class="group transition-all duration-300 hover:bg-muted/30">
                <td class="px-8 py-5 text-center">
                  <button @click="toggleDeptSelection(dept.id)" class="h-6 w-6 rounded-lg border-2 flex items-center justify-center transition-all bg-background mx-auto shadow-inner dark:border-white/10" :class="isSelected(dept.id) ? 'bg-blue-600 border-blue-600 text-white shadow-blue-600/30' : 'border-border'">
                    <Check v-if="isSelected(dept.id)" class="h-4 w-4 stroke-[3px]" />
                  </button>
                </td>
                <td class="px-8 py-5">
                  <div class="flex items-center gap-5">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-muted text-foreground font-black group-hover:bg-foreground group-hover:text-background transition-all duration-500 shadow-inner text-sm uppercase italic border border-border/50">
                      {{ dept.name.charAt(0) }}
                    </div>
                    <div class="min-w-0">
                      <p class="font-black text-foreground group-hover:text-blue-600 transition-colors text-sm uppercase italic tracking-tight truncate max-w-[220px] leading-tight mb-1">{{ dept.name }}</p>
                      <p class="text-[9px] font-black text-muted-foreground/40 uppercase tracking-widest italic leading-none truncate max-w-[220px]">{{ dept.description || 'Global Faculty Unit' }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-8 py-5 text-xs font-black text-muted-foreground/60 uppercase tracking-widest italic tabular-nums">
                  {{ dept.code || '—' }}
                </td>
                <td class="px-8 py-5">
                  <div class="flex items-center gap-3">
                    <div class="h-8 w-8 rounded-lg bg-muted border border-border flex items-center justify-center text-[9px] font-black text-slate-500 shadow-sm uppercase italic">{{ dept.head_of_department?.charAt(0) || '?' }}</div>
                    <span class="text-xs font-black text-foreground uppercase italic group-hover:text-blue-600 transition-colors truncate max-w-[150px]">{{ dept.head_of_department || 'NULL NODE' }}</span>
                  </div>
                </td>
                <td class="px-8 py-5 text-center">
                  <Badge variant="outline" class="rounded-lg h-7 border-border bg-muted/30 text-foreground font-black text-[10px] px-4 uppercase italic tracking-widest group-hover:bg-foreground group-hover:text-background transition-all tabular-nums">{{ dept.teachers_count ?? 0 }} Minds</Badge>
                </td>
                <td class="px-8 py-5 text-center font-black text-blue-600 text-sm italic tabular-nums">
                   {{ dept.subjects_count ?? 0 }}
                </td>
                <td class="px-8 py-5 text-center">
                  <div class="flex items-center justify-center gap-3">
                    <div class="h-2 w-2 rounded-full shadow-[0_0_8px_rgba(34,197,94,0.4)]" :class="dept.is_active ? 'bg-emerald-500 animate-pulse' : 'bg-muted-foreground/20'"></div>
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] italic" :class="dept.is_active ? 'text-emerald-600' : 'text-muted-foreground/30'">{{ dept.is_active ? 'INITIALIZED' : 'SUSPENDED' }}</span>
                  </div>
                </td>
                <td class="px-8 py-5 text-right">
                  <div class="flex items-center justify-end gap-2 outline-none">
                    <Button variant="ghost" size="sm" class="h-10 w-10 p-0 rounded-xl text-muted-foreground hover:bg-blue-600/10 hover:text-blue-600 transition-all opacity-0 group-hover:opacity-100 border border-transparent hover:border-blue-600/20 shadow-sm" as-child title="Sync Specs">
                      <Link :href="`/departments/${dept.id}`"><Eye class="h-4.5 w-4.5" /></Link>
                    </Button>
                    <Button variant="ghost" size="sm" class="h-10 w-10 p-0 rounded-xl text-muted-foreground hover:bg-amber-500/10 hover:text-amber-500 transition-all opacity-0 group-hover:opacity-100 border border-transparent hover:border-amber-500/20 shadow-sm" as-child title="Reconfig Unit">
                      <Link :href="`/departments/${dept.id}/edit`"><Edit class="h-4.5 w-4.5" /></Link>
                    </Button>
                    <Button variant="ghost" size="sm" class="h-10 w-10 p-0 rounded-xl transition-all border border-transparent opacity-0 group-hover:opacity-100 shadow-sm" :class="dept.is_active ? 'text-amber-500 hover:bg-amber-50 border-amber-500/10' : 'text-emerald-500 hover:bg-emerald-50 border-emerald-500/10'" @click="toggleStatus(dept)" :title="dept.is_active ? 'Suspend Flow' : 'Enable Flow'">
                      <component :is="dept.is_active ? ShieldOff : ShieldCheck" class="h-4.5 w-4.5" />
                    </Button>
                    <Button variant="ghost" size="sm" class="h-10 w-10 p-0 rounded-xl text-rose-500 hover:bg-rose-500/10 transition-all opacity-0 group-hover:opacity-100 border border-transparent hover:border-rose-500/20 shadow-sm" @click="openDeleteModal(dept)" title="Purge Unit">
                      <Trash2 class="h-4.5 w-4.5" />
                    </Button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination Footer -->
      <div v-if="page.props?.departments?.total > 0" class="flex flex-col gap-6 border-t border-border/30 bg-muted/10 px-10 py-10 md:flex-row md:items-center md:justify-between group/footer rounded-[2rem] shadow-sm relative overflow-hidden dark:border-white/5">
        <div class="absolute -left-20 -bottom-20 h-64 w-64 rounded-full bg-blue-600/5 blur-3xl group-hover/footer:bg-blue-600/10 transition-all duration-1000"></div>
        
        <div class="flex items-center gap-6 relative z-10">
          <p class="text-[10px] font-black text-muted-foreground/40 uppercase tracking-[0.15em] italic">
            Scanning <span class="text-foreground italic font-black">{{ page.props?.departments?.from ?? 0 }}-{{ page.props?.departments?.to ?? 0 }}</span> of <span class="text-foreground italic font-black">{{ page.props?.departments?.total ?? 0 }}</span> Units
          </p>
        </div>

        <div class="flex items-center gap-2 relative z-10">
            <template v-for="(link, i) in page.props.departments.links" :key="i">
                <Button 
                    v-if="link.url || link.label.includes('...')"
                    variant="outline" 
                    size="sm" 
                    :disabled="!link.url"
                    :class="[
                      'h-12 min-w-[3rem] px-4 rounded-2xl border-border font-black text-[11px] transition-all duration-300 uppercase italic tracking-tighter shadow-sm',
                      link.active ? 'bg-blue-600 text-white border-blue-600 shadow-blue-600/20 scale-105 shadow-inner' : 'text-foreground hover:bg-muted'
                    ]"
                    @click="link.url && router.get(link.url)"
                >
                    <span v-html="link.label.replace('Previous', '').replace('Next', '')"></span>
                    <ChevronLeft v-if="link.label.includes('Previous')" class="h-4 w-4" />
                    <ChevronRight v-if="link.label.includes('Next')" class="h-4 w-4" />
                </Button>
            </template>
        </div>
      </div>

      <!-- Matrix Pulse Footer -->
      <div class="p-10 rounded-[3rem] bg-slate-900 text-white flex flex-col md:flex-row items-center justify-between gap-10 group relative overflow-hidden shadow-[0_0_60px_rgba(0,0,0,0.3)] dark:bg-black">
        <div class="absolute -right-24 -bottom-24 opacity-5 group-hover:scale-125 transition-all duration-[2000ms] text-white font-black italic uppercase text-[12rem] select-none pointer-events-none tracking-widest">
            CORE
        </div>
        <div class="flex items-center gap-8 relative z-10">
          <div class="h-20 w-20 rounded-3xl bg-white/5 flex items-center justify-center border border-white/10 group-hover:bg-blue-600 group-hover:border-blue-600 transition-all duration-700 shadow-2xl">
              <Building2 class="h-10 w-10 text-white/30 group-hover:text-white group-hover:scale-110 transition-all duration-500" />
          </div>
          <div>
            <h3 class="font-black text-2xl tracking-tighter uppercase italic group-hover:text-blue-400 transition-colors duration-500">Faculty Matrix Center</h3>
            <p class="text-white/40 text-[10px] mt-2 tracking-[0.2em] font-black uppercase leading-relaxed italic opacity-80 decoration-blue-500/30 underline underline-offset-4">Maintaining academic equilibrium across all specialized units.</p>
          </div>
        </div>
        <div class="flex gap-8 relative z-10">
          <div class="flex flex-col items-end">
              <span class="text-[10px] font-black text-white/30 uppercase tracking-[0.3em] mb-2 opacity-60">Matrix Sync</span>
              <span class="bg-blue-600/20 text-blue-400 border border-blue-400/20 px-8 py-3 rounded-full text-[11px] font-black uppercase tracking-[0.25em] shadow-[0_0_30px_rgba(59,130,246,0.3)] animate-pulse italic">Locked & Balanced</span>
          </div>
        </div>
      </div>

      <!-- Delete Modal -->
      <div v-if="showDeleteModal" class="fixed inset-0 bg-black/60 backdrop-blur-xl flex items-center justify-center z-[100] animate-in fade-in duration-500 p-6">
        <div class="bg-card rounded-[3rem] p-12 max-w-sm w-full shadow-[0_0_80px_rgba(0,0,0,0.5)] animate-in zoom-in-95 duration-300 border border-border relative overflow-hidden">
          <div class="absolute -right-16 -top-16 h-32 w-32 rounded-full bg-rose-600/10 blur-3xl"></div>
          
          <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-rose-500/10 text-rose-500 mb-8 shadow-inner border border-rose-500/20 group mx-auto">
            <Trash2 class="h-10 w-10 group-hover:scale-110 transition-transform duration-500" />
          </div>
          <h3 class="text-3xl font-black text-foreground mb-4 tracking-tighter text-center uppercase italic">Purge Unit?</h3>
          <p class="text-[10px] font-black text-muted-foreground/60 mb-10 leading-relaxed text-center uppercase tracking-widest italic pt-2 decoration-rose-500/20 underline underline-offset-4">
            Are you sure you want to delete <span class="text-foreground">{{ deleteTarget?.name }}</span>? All matrix allocations will be permanently severed.
          </p>
          <div class="flex flex-col gap-4">
            <Button @click="confirmDelete" class="w-full h-14 bg-rose-600 hover:bg-rose-700 text-white font-black rounded-2xl shadow-xl shadow-rose-600/20 border-0 transition-all uppercase text-[10px] tracking-[0.2em] italic active:scale-95">Purge Unit</Button>
            <Button variant="ghost" @click="showDeleteModal = false" class="w-full h-14 rounded-2xl text-muted-foreground font-black hover:bg-muted transition-all uppercase text-[10px] tracking-[0.2em] italic">Abort</Button>
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
