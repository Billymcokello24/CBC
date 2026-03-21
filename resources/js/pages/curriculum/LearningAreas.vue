<script setup lang="ts">
/* global route */
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { 
    GraduationCap, Search, Plus, Filter, MoreHorizontal, Eye, Edit, 
    Trash2, Layers, BookOpen, ChevronDown, CheckSquare, Square,
    LayoutGrid, List, CheckCircle2, XCircle, ShieldCheck
} from 'lucide-vue-next';
import { ref, watch, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import LearningAreaModal from '@/components/curriculum/LearningAreaModal.vue';
import ConfirmDialog from '@/components/curriculum/ConfirmDialog.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    areas: any[];
    stats: {
        total: number;
        active: number;
        subjects: number;
    };
    filters: {
        search?: string;
        status?: string;
        view?: 'grid' | 'list';
    };
    statusOptions: Array<{ value: string; label: string }>;
}>();

const { props: pageProps } = usePage<any>();
const permissions = computed(() => pageProps.auth.permissions || []);

const hasPermission = (permission: string) => {
    if (permissions.value.includes('super_admin')) return true;
    return permissions.value.includes(permission);
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Learning Areas', href: '/curriculum/learning-areas' },
];

// State
const searchQuery = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || 'all');
const currentView = ref(props.filters.view || 'list');
const selectedIds = ref<number[]>([]);

// Modals
const showModal = ref(false);
const modalTitle = ref('New Learning Area');
const editingArea = ref<any>(null);

const showConfirm = ref(false);
const confirmConfig = ref({
    title: '',
    message: '',
    confirmText: '',
    variant: 'danger' as 'danger' | 'warning' | 'info',
    action: null as (() => void) | null,
    loading: false
});

// Logic
let debounceTimeout: any = null;
const updateFilters = () => {
    if (debounceTimeout) clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        router.get(route('curriculum.learning-areas'), {
            search: searchQuery.value,
            status: selectedStatus.value,
            view: currentView.value,
        }, {
            preserveState: true,
            preserveScroll: true,
            replace: true
        });
    }, 300);
};

watch([searchQuery, selectedStatus, currentView], () => updateFilters());

// Actions
const openCreate = () => {
    editingArea.value = null;
    modalTitle.value = 'New Learning Area';
    showModal.value = true;
};

const openEdit = (area: any) => {
    editingArea.value = area;
    modalTitle.value = `Edit ${area.name}`;
    showModal.value = true;
};

const confirmDelete = (area: any) => {
    confirmConfig.value = {
        title: 'Delete Learning Area?',
        message: `Are you sure you want to delete "${area.name}"? This action cannot be undone and may affect related subjects.`,
        confirmText: 'Delete Area',
        variant: 'danger',
        loading: false,
        action: () => {
            confirmConfig.value.loading = true;
            router.delete(route('curriculum.learning-areas.destroy', area.id), {
                onFinish: () => {
                    showConfirm.value = false;
                    confirmConfig.value.loading = false;
                }
            });
        }
    };
    showConfirm.value = true;
};

// Selection
const toggleSelectAll = () => {
    if (selectedIds.value.length === props.areas.length) {
        selectedIds.value = [];
    } else {
        selectedIds.value = props.areas.map(a => a.id);
    }
};

const toggleSelect = (id: number) => {
    const index = selectedIds.value.indexOf(id);
    if (index > -1) selectedIds.value.splice(index, 1);
    else selectedIds.value.push(id);
};

const bulkAction = (action: 'activate' | 'deactivate' | 'delete') => {
    const labels = {
        activate: { title: 'Activate Areas?', msg: 'Enable student enrollment for the selected learning areas.', text: 'Activate', variant: 'info' },
        deactivate: { title: 'Deactivate Areas?', msg: 'Disable student enrollment for the selected learning areas.', text: 'Deactivate', variant: 'warning' },
        delete: { title: 'Delete Areas?', msg: 'Permanently remove the selected learning areas and their data.', text: 'Delete', variant: 'danger' }
    };

    const config = labels[action];
    
    confirmConfig.value = {
        title: config.title,
        message: `${config.msg} (${selectedIds.value.length} items)`,
        confirmText: config.text,
        variant: config.variant as any,
        loading: false,
        action: () => {
            confirmConfig.value.loading = true;
            router.post(route('curriculum.learning-areas.bulk-action'), {
                ids: selectedIds.value,
                action: action
            }, {
                onFinish: () => {
                    showConfirm.value = false;
                    confirmConfig.value.loading = false;
                    selectedIds.value = [];
                }
            });
        }
    };
    showConfirm.value = true;
};

const getStatusColor = (active: boolean) => {
    return active ? 'bg-emerald-500 text-white shadow-[0_0_8px_rgba(16,185,129,0.4)]' : 'bg-slate-400 text-white';
};

</script>

<template>
    <Head title="Learning Areas" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 font-pulsar mt-2">
            <!-- Header section -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-500/10">
                        <GraduationCap class="h-6 w-6 text-violet-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Learning Areas</h1>
                        <p class="text-muted-foreground font-medium">Manage top-level curriculum domains and subject clusters</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" class="font-pulsar h-10 border-slate-200" v-if="selectedIds.length > 0" @click="selectedIds = []">
                        Clear Selected ({{ selectedIds.length }})
                    </Button>
                    <Button v-if="hasPermission('curriculum.create')" @click="openCreate" class="bg-violet-600 hover:bg-violet-700 font-pulsar shadow-lg h-10">
                        <Plus class="mr-2 h-4 w-4" />New Learning Area
                    </Button>
                </div>
            </div>

            <!-- Stats section -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                 <div class="rounded-2xl border bg-card p-5 shadow-sm border-l-4 border-l-violet-500 flex items-center gap-4 group hover:shadow-md transition-all">
                    <div class="rounded-xl bg-violet-500/10 p-3 group-hover:bg-violet-500 group-hover:text-white transition-all"><Layers class="h-5 w-5 text-violet-600 group-hover:text-white" /></div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Total Areas</p>
                        <p class="text-xl font-black text-slate-900">{{ stats.total }} Domains</p>
                    </div>
                </div>
                <div class="rounded-2xl border bg-card p-5 shadow-sm border-l-4 border-l-emerald-500 flex items-center gap-4 group hover:shadow-md transition-all">
                    <div class="rounded-xl bg-emerald-500/10 p-3 group-hover:bg-emerald-500 group-hover:text-white transition-all"><CheckCircle2 class="h-5 w-5 text-emerald-600 group-hover:text-white" /></div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Active Areas</p>
                        <p class="text-xl font-black text-emerald-600">{{ stats.active }} Pulse</p>
                    </div>
                </div>
                <div class="rounded-2xl border bg-card p-5 shadow-sm border-l-4 border-l-blue-500 flex items-center gap-4 group hover:shadow-md transition-all">
                    <div class="rounded-xl bg-blue-500/10 p-3 group-hover:bg-blue-500 group-hover:text-white transition-all"><BookOpen class="h-5 w-5 text-blue-600 group-hover:text-white" /></div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Total Subjects</p>
                        <p class="text-xl font-black text-slate-900">{{ stats.subjects }} Active</p>
                    </div>
                </div>
                <div class="rounded-2xl border bg-card p-5 shadow-sm border-l-4 border-l-indigo-500 flex items-center gap-4 group hover:shadow-md transition-all">
                    <div class="rounded-xl bg-indigo-500/10 p-3 group-hover:bg-indigo-500 group-hover:text-white transition-all"><ShieldCheck class="h-5 w-5 text-indigo-600 group-hover:text-white" /></div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">RBAC Status</p>
                        <p class="text-xl font-black text-indigo-600 uppercase tracking-tighter">SECURED</p>
                    </div>
                </div>
            </div>

            <!-- Toolbar Section -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center justify-between rounded-xl border bg-card p-4 shadow-sm">
                <div class="flex flex-1 items-center gap-4 max-w-xl">
                    <div class="relative flex-1">
                        <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground font-medium" />
                        <Input v-model="searchQuery" placeholder="Search by name or code..." class="pl-9 h-10 border-slate-200" />
                    </div>
                    <DropdownMenu>
                         <DropdownMenuTrigger as-child>
                             <Button variant="outline" class="h-10 border-slate-200 font-pulsar px-4">
                               <Filter class="mr-2 h-4 w-4 text-violet-500" /> {{ selectedStatus === 'all' ? 'All Statuses' : selectedStatus.charAt(0).toUpperCase() + selectedStatus.slice(1) }}
                             </Button>
                         </DropdownMenuTrigger>
                         <DropdownMenuContent class="font-pulsar w-48">
                            <DropdownMenuItem @click="selectedStatus = 'all'">All Statuses</DropdownMenuItem>
                            <DropdownMenuItem @click="selectedStatus = 'active'">Active Only</DropdownMenuItem>
                            <DropdownMenuItem @click="selectedStatus = 'inactive'">Inactive Only</DropdownMenuItem>
                         </DropdownMenuContent>
                    </DropdownMenu>
                </div>
                
                <div class="flex items-center gap-2">
                    <div class="flex items-center rounded-lg bg-slate-100 p-1 border">
                        <Button variant="ghost" size="icon" class="h-8 w-8 rounded-md" :class="{ 'bg-white shadow-sm text-violet-600': currentView === 'grid' }" @click="currentView = 'grid'"><LayoutGrid class="h-4 w-4" /></Button>
                        <Button variant="ghost" size="icon" class="h-8 w-8 rounded-md" :class="{ 'bg-white shadow-sm text-violet-600': currentView === 'list' }" @click="currentView = 'list'"><List class="h-4 w-4" /></Button>
                    </div>

                    <div class="h-8 w-[1px] bg-slate-200 mx-2"></div>

                    <div v-if="selectedIds.length > 0">
                         <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button class="bg-slate-900 text-white hover:bg-black font-pulsar h-10 px-4 shadow-lg border-0">
                                    Bulk Actions ({{ selectedIds.length }}) <ChevronDown class="ml-2 h-4 w-4" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="w-48 font-pulsar">
                                <DropdownMenuItem @click="bulkAction('activate')"><CheckCircle2 class="mr-2 h-4 w-4 text-emerald-500" /> Activate</DropdownMenuItem>
                                <DropdownMenuItem @click="bulkAction('deactivate')"><XCircle class="mr-2 h-4 w-4 text-amber-500" /> Deactivate</DropdownMenuItem>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem @click="bulkAction('delete')" class="text-rose-600 font-bold"><Trash2 class="mr-2 h-4 w-4" /> Delete</DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <transition name="fade" mode="out-in">
                <!-- Grid View -->
                <div v-if="currentView === 'grid'" :key="'grid'" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div v-for="area in areas" :key="area.id" 
                        class="rounded-3xl border bg-card overflow-hidden shadow-sm hover:shadow-md transition-all flex flex-col group relative border-t-4"
                        :style="`border-top-color: ${area.is_active ? '#8b5cf6' : '#94a3b8'}`"
                    >
                        <!-- Selector -->
                        <div class="absolute top-4 left-4 z-10">
                             <button @click="toggleSelect(area.id)" class="h-6 w-6 rounded-lg border-2 flex items-center justify-center transition-all bg-white/50 backdrop-blur-sm" :class="selectedIds.includes(area.id) ? 'bg-violet-600 border-violet-600 text-white' : 'border-slate-300'">
                                  <component :is="selectedIds.includes(area.id) ? CheckSquare : Square" class="h-4 w-4" />
                             </button>
                        </div>

                        <div class="p-6 pb-4">
                            <div class="flex items-start justify-between mb-4">
                                <div class="h-12 w-12 rounded-xl bg-violet-600/10 flex items-center justify-center font-black text-violet-700 group-hover:bg-violet-600 group-hover:text-white transition-all text-sm shadow-inner border border-violet-100 uppercase">
                                     {{ area.code.substring(0, 2) }}
                                </div>
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="ghost" size="icon" class="h-8 w-8"><MoreHorizontal class="h-4 w-4" /></Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end" class="w-48 font-pulsar">
                                        <DropdownMenuItem v-if="hasPermission('curriculum.update')" @click="openEdit(area)"><Edit class="mr-2 h-4 w-4" /> Edit Details</DropdownMenuItem>
                                        <DropdownMenuItem as-child><Link :href="route('curriculum.learning-areas.show', area.id)"><Eye class="mr-2 h-4 w-4" /> View Logic</Link></DropdownMenuItem>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem v-if="hasPermission('curriculum.delete')" @click="confirmDelete(area)" class="text-rose-600 font-bold"><Trash2 class="mr-2 h-4 w-4" /> Delete Area</DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                            
                            <div class="space-y-2">
                                 <h3 class="text-xl font-bold text-slate-900 group-hover:text-violet-700 transition-colors">{{ area.name }}</h3>
                                 <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ area.code }}</span>
                                 <p class="text-xs text-muted-foreground font-medium line-clamp-2 mt-2 italic">{{ area.description || 'No description provided for this academic domain.' }}</p>
                            </div>
                            
                            <div class="mt-6 grid grid-cols-2 gap-3">
                                 <div class="p-3 rounded-xl bg-slate-50 border shadow-inner text-center">
                                      <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Subjects</p>
                                      <p class="text-lg font-black text-slate-900">{{ area.subjects_count }}</p>
                                 </div>
                                 <div class="p-3 rounded-xl bg-slate-50 border shadow-inner text-center">
                                      <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Status</p>
                                      <div class="flex items-center justify-center gap-1.5">
                                           <div class="h-2 w-2 rounded-full" :class="area.is_active ? 'bg-emerald-500' : 'bg-slate-300'"></div>
                                           <p class="text-[9px] font-black text-slate-900 uppercase italic">{{ area.is_active ? 'Active' : 'Inactive' }}</p>
                                      </div>
                                 </div>
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-slate-50/50 border-t flex items-center justify-between mt-auto">
                             <div class="flex items-center gap-2">
                                  <span class="text-[9px] font-black text-slate-300 uppercase tracking-widest">ORDER #{{ area.display_order }}</span>
                             </div>
                             <Link :href="route('curriculum.learning-areas.show', area.id)" class="text-[10px] font-black text-violet-600 uppercase hover:underline">
                                Expand Details
                             </Link>
                        </div>
                    </div>
                </div>

                <!-- List View -->
                <div v-else :key="'list'" class="rounded-3xl border bg-card shadow-sm overflow-hidden overflow-x-auto border-t-4 border-t-violet-500">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50 border-b font-pulsar">
                                <th class="w-12 px-6 py-4 text-center">
                                     <button @click="toggleSelectAll" class="h-6 w-6 rounded-lg border-2 flex items-center justify-center transition-all bg-white mx-auto shadow-sm" :class="selectedIds.length === areas.length ? 'bg-violet-600 border-violet-600 text-white' : 'border-slate-300'">
                                          <component :is="selectedIds.length === areas.length ? CheckSquare : Square" class="h-4 w-4" />
                                     </button>
                                </th>
                                <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-widest">Domain / Cluster</th>
                                <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-widest">Code</th>
                                <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-widest text-center">Subjects</th>
                                <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-widest text-center">Status</th>
                                <th class="px-6 py-4 text-right text-xs font-bold uppercase text-slate-500 tracking-widest">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y text-sm">
                            <tr v-for="area in areas" :key="area.id" class="group hover:bg-slate-50/70 transition-colors">
                                <td class="px-6 py-4 text-center">
                                     <button @click="toggleSelect(area.id)" class="h-6 w-6 rounded-lg border-2 flex items-center justify-center transition-all bg-white mx-auto shadow-sm" :class="selectedIds.includes(area.id) ? 'bg-violet-600 border-violet-600 text-white' : 'border-slate-200'">
                                          <component :is="selectedIds.includes(area.id) ? CheckSquare : Square" class="h-4 w-4" />
                                     </button>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-xl bg-violet-600/10 flex items-center justify-center font-black text-violet-700 group-hover:bg-violet-600 group-hover:text-white transition-all text-sm shadow-inner border border-violet-100 uppercase">
                                             {{ area.code.substring(0, 2) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900 group-hover:text-violet-700 transition-colors leading-none tracking-tight text-base mb-1">{{ area.name }}</div>
                                            <div class="text-[10px] font-black text-slate-400 mt-0.5 tracking-widest uppercase italic">{{ area.category || 'Curriculum Domain' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-black text-[10px] text-slate-900 tracking-widest">{{ area.code }}</div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                     <div class="flex flex-col items-center">
                                         <span class="font-black text-slate-900 text-lg leading-none">{{ area.subjects_count }}</span>
                                         <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1">Active Nodes</span>
                                     </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <Badge :class="getStatusColor(area.is_active)" class="rounded-full px-2 py-0.5 text-[8px] font-black uppercase tracking-widest border-0">
                                        {{ area.is_active ? 'Active' : 'Inactive' }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Button v-if="hasPermission('curriculum.update')" variant="ghost" size="icon" class="h-8 w-8 text-slate-400 hover:text-amber-600" @click="openEdit(area)"><Edit class="h-4 w-4" /></Button>
                                        <Button v-if="hasPermission('curriculum.delete')" variant="ghost" size="icon" class="h-8 w-8 text-slate-400 hover:text-rose-600" @click="confirmDelete(area)"><Trash2 class="h-4 w-4" /></Button>
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" size="icon" class="h-8 w-8"><MoreHorizontal class="h-4 w-4" /></Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end" class="w-48 font-pulsar">
                                                <DropdownMenuItem as-child><Link :href="route('curriculum.learning-areas.show', area.id)"><Eye class="mr-2 h-4 w-4" /> Details</Link></DropdownMenuItem>
                                                <DropdownMenuItem v-if="hasPermission('curriculum.update')" @click="openEdit(area)"><Edit class="mr-2 h-4 w-4" /> Edit</DropdownMenuItem>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem v-if="hasPermission('curriculum.delete')" @click="confirmDelete(area)" class="text-rose-600 font-bold"><Trash2 class="mr-2 h-4 w-4" /> Delete</DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="areas.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-muted-foreground italic font-medium">
                                    No administrative domains found in the current cluster search.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </transition>

            <!-- Footer Sync -->
            <div class="mt-2 p-5 rounded-2xl bg-slate-900 text-white flex flex-col md:flex-row items-center justify-between gap-6 shadow-xl border border-slate-700 transition-all hover:bg-slate-950 group">
                <div class="flex items-center gap-4">
                    <div class="h-10 w-10 rounded-xl bg-white/10 flex items-center justify-center group-hover:bg-violet-600 group-hover:text-white transition-all">
                         <ShieldCheck class="h-5 w-5 text-violet-400 group-hover:text-white" />
                    </div>
                    <div>
                        <h3 class="font-bold text-sm tracking-tight text-white/90 uppercase">Domain Synchronization Active</h3>
                        <p class="text-slate-400 text-[10px] mt-0.5 tracking-wider font-semibold">Ensuring curriculum consistency across all academic hierarchies mapping to the 2026 Academic Year.</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" size="sm" class="bg-white/5 border-white/10 text-white hover:bg-white/20 h-9 font-bold px-4 rounded-xl text-xs uppercase cursor-default">Registry: Stable</Button>
                </div>
            </div>
            
            <!-- Modals -->
            <LearningAreaModal 
                :show="showModal" 
                :area="editingArea" 
                :title="modalTitle" 
                @close="showModal = false" 
                @success="selectedIds = []"
            />

            <ConfirmDialog 
                :show="showConfirm" 
                v-bind="confirmConfig"
                @close="showConfirm = false"
                @confirm="confirmConfig.action?.()"
            />
        </div>
    </AppLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease, transform 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(10px);
}

.font-black { font-weight: 950; }
</style>
