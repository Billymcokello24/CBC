<script setup lang="ts">
/* global route */
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { 
    BookOpen, Search, Plus, Filter, MoreHorizontal, Eye, Edit, 
    Trash2, Layers, GraduationCap, ChevronDown, CheckSquare, Square,
    LayoutGrid, List, CheckCircle2, XCircle, ShieldCheck, BadgeCheck
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
import SubjectModal from '@/components/curriculum/SubjectModal.vue';
import ConfirmDialog from '@/components/curriculum/ConfirmDialog.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    subjects: any[];
    learningAreas: any[];
    stats: {
        total: number;
        active: number;
        strands: number;
    };
    filters: {
        search?: string;
        status?: string;
        area?: string;
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
    { title: 'Subjects', href: '/curriculum/subjects' },
];

// State
const searchQuery = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || 'all');
const selectedArea = ref(props.filters.area || 'all');
const currentView = ref(props.filters.view || 'list');
const selectedIds = ref<number[]>([]);

// Modals
const showModal = ref(false);
const modalTitle = ref('New Subject Model');
const editingSubject = ref<any>(null);

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
        router.get(route('curriculum.subjects'), {
            search: searchQuery.value,
            status: selectedStatus.value,
            area: selectedArea.value,
            view: currentView.value,
        }, {
            preserveState: true,
            preserveScroll: true,
            replace: true
        });
    }, 300);
};

watch([searchQuery, selectedStatus, selectedArea, currentView], () => updateFilters());

// Actions
const openCreate = () => {
    editingSubject.value = null;
    modalTitle.value = 'New Subject Model';
    showModal.value = true;
};

const openEdit = (subject: any) => {
    editingSubject.value = subject;
    modalTitle.value = `Edit ${subject.name}`;
    showModal.value = true;
};

const confirmDelete = (subject: any) => {
    confirmConfig.value = {
        title: 'Purge Subject?',
        message: `Are you sure you want to purge "${subject.name}"? This will remove all associated grades and performance metrics.`,
        confirmText: 'Purge Subject',
        variant: 'danger',
        loading: false,
        action: () => {
            confirmConfig.value.loading = true;
            router.delete(route('curriculum.subjects.destroy', subject.id), {
                onFinish: () => {
                    showConfirm.value = false;
                    confirmConfig.value.loading = false;
                }
            });
        }
    };
    showConfirm.value = true;
};

const toggleSelectAll = () => {
    if (selectedIds.value.length === props.subjects.length) {
        selectedIds.value = [];
    } else {
        selectedIds.value = props.subjects.map(s => s.id);
    }
};

const toggleSelect = (id: number) => {
    const index = selectedIds.value.indexOf(id);
    if (index > -1) selectedIds.value.splice(index, 1);
    else selectedIds.value.push(id);
};

const bulkAction = (action: 'activate' | 'deactivate' | 'delete') => {
    const labels = {
        activate: { title: 'Activate Subjects?', msg: 'Enable instructional delivery for the selected subjects.', text: 'Activate', variant: 'info' },
        deactivate: { title: 'Deactivate Subjects?', msg: 'Suspend instructional delivery for the selected subjects.', text: 'Deactivate', variant: 'warning' },
        delete: { title: 'Purge Subjects?', msg: 'Permanently remove the selected subjects from the academic matrix.', text: 'Purge', variant: 'danger' }
    };

    const config = labels[action];
    
    confirmConfig.value = {
        title: config.title,
        message: `${config.msg} (${selectedIds.value.length} nodes)`,
        confirmText: config.text,
        variant: config.variant as any,
        loading: false,
        action: () => {
            confirmConfig.value.loading = true;
            router.post(route('curriculum.subjects.bulk-action'), {
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
    <Head title="Subjects Registry" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 font-pulsar mt-2">
            <!-- Header section -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-500/10">
                        <BookOpen class="h-6 w-6 text-violet-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">Subjects Registry</h1>
                        <p class="text-muted-foreground font-medium">Manage academic subjects, weighting and instructional models</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" class="font-pulsar h-10 border-slate-200" v-if="selectedIds.length > 0" @click="selectedIds = []">
                        Clear Batch ({{ selectedIds.length }})
                    </Button>
                    <Button v-if="hasPermission('curriculum.create')" @click="openCreate" class="bg-violet-600 hover:bg-violet-700 font-pulsar shadow-lg h-10">
                        <Plus class="mr-2 h-4 w-4" />New Subject
                    </Button>
                </div>
            </div>

            <!-- Dashboard Stats -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border bg-card p-5 shadow-sm border-l-4 border-l-violet-500 flex items-center gap-4 group hover:shadow-md transition-all">
                    <div class="rounded-xl bg-violet-500/10 p-3 group-hover:bg-violet-500 group-hover:text-white transition-all"><BookOpen class="h-5 w-5 text-violet-600 group-hover:text-white" /></div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Total Subjects</p>
                        <p class="text-xl font-black text-slate-900">{{ stats.total }} Courses</p>
                    </div>
                </div>
                <div class="rounded-2xl border bg-card p-5 shadow-sm border-l-4 border-l-emerald-500 flex items-center gap-4 group hover:shadow-md transition-all">
                    <div class="rounded-xl bg-emerald-500/10 p-3 group-hover:bg-emerald-500 group-hover:text-white transition-all"><BadgeCheck class="h-5 w-5 text-emerald-600 group-hover:text-white" /></div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Active Status</p>
                        <p class="text-xl font-black text-emerald-600">{{ stats.active }} Pulse</p>
                    </div>
                </div>
                <div class="rounded-2xl border bg-card p-5 shadow-sm border-l-4 border-l-blue-500 flex items-center gap-4 group hover:shadow-md transition-all">
                    <div class="rounded-xl bg-blue-500/10 p-3 group-hover:bg-blue-500 group-hover:text-white transition-all"><Layers class="h-5 w-5 text-blue-600 group-hover:text-white" /></div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Global Strands</p>
                        <p class="text-xl font-black text-slate-900">{{ stats.strands }} Topics</p>
                    </div>
                </div>
                <div class="rounded-2xl border bg-card p-5 shadow-sm border-l-4 border-l-indigo-500 flex items-center gap-4 group hover:shadow-md transition-all">
                    <div class="rounded-xl bg-indigo-500/10 p-3 group-hover:bg-indigo-500 group-hover:text-white transition-all"><ShieldCheck class="h-5 w-5 text-indigo-600 group-hover:text-white" /></div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Access Control</p>
                        <p class="text-xl font-black text-indigo-600 uppercase tracking-tighter">SECURED</p>
                    </div>
                </div>
            </div>

            <!-- Toolbar Section -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center justify-between rounded-xl border bg-card p-4 shadow-sm">
                <div class="flex flex-1 items-center gap-3 max-w-2xl">
                    <div class="relative flex-1">
                        <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground font-medium" />
                        <Input v-model="searchQuery" placeholder="Search by name, code or model..." class="pl-9 h-10 border-slate-200" />
                    </div>
                    <DropdownMenu>
                         <DropdownMenuTrigger as-child>
                             <Button variant="outline" class="h-10 border-slate-200 font-pulsar px-4">
                               <Filter class="mr-2 h-4 w-4 text-violet-500" /> Areas
                             </Button>
                         </DropdownMenuTrigger>
                         <DropdownMenuContent class="font-pulsar w-56 scroll-auto max-h-[300px]">
                            <DropdownMenuItem @click="selectedArea = 'all'">All Learning Areas</DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem v-for="area in learningAreas" :key="area.id" @click="selectedArea = area.id.toString()">
                                {{ area.name }}
                            </DropdownMenuItem>
                         </DropdownMenuContent>
                    </DropdownMenu>
                    <DropdownMenu>
                         <DropdownMenuTrigger as-child>
                             <Button variant="outline" class="h-10 border-slate-200 font-pulsar px-4">
                               <Filter class="mr-2 h-4 w-4 text-violet-500" /> Status
                             </Button>
                         </DropdownMenuTrigger>
                         <DropdownMenuContent class="font-pulsar w-48">
                            <DropdownMenuItem @click="selectedStatus = 'all'">All Statuses</DropdownMenuItem>
                            <DropdownMenuItem @click="selectedStatus = 'active'">Active Nodes</DropdownMenuItem>
                            <DropdownMenuItem @click="selectedStatus = 'inactive'">Dormant Nodes</DropdownMenuItem>
                         </DropdownMenuContent>
                    </DropdownMenu>
                </div>
                
                <div class="flex items-center gap-2">
                    <div class="flex items-center rounded-lg bg-slate-100 p-1 border">
                        <Button variant="ghost" size="icon" class="h-8 w-8 rounded-md" :class="{ 'bg-white shadow-sm text-violet-600': currentView === 'grid' }" @click="currentView = 'grid'"><LayoutGrid class="h-4 w-4" /></Button>
                        <Button variant="ghost" size="icon" class="h-8 w-8 rounded-md" :class="{ 'bg-white shadow-sm text-violet-600': currentView === 'list' }" @click="currentView = 'list'"><List class="h-4 w-4" /></Button>
                    </div>

                    <div class="h-8 w-[1px] bg-slate-200 mx-1"></div>

                    <div v-if="selectedIds.length > 0">
                         <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button class="bg-slate-900 text-white hover:bg-black font-pulsar h-10 px-4 shadow-lg border-0">
                                    Bulk Actions ({{ selectedIds.length }}) <ChevronDown class="ml-2 h-4 w-4" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="w-48 font-pulsar">
                                <DropdownMenuItem @click="bulkAction('activate')"><CheckCircle2 class="mr-2 h-4 w-4 text-emerald-500" /> Restore Logic</DropdownMenuItem>
                                <DropdownMenuItem @click="bulkAction('deactivate')"><XCircle class="mr-2 h-4 w-4 text-amber-500" /> Suspend Logic</DropdownMenuItem>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem @click="bulkAction('delete')" class="text-rose-600 font-bold"><Trash2 class="mr-2 h-4 w-4" /> Purge Matrix</DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <transition name="fade" mode="out-in">
                <!-- Grid View -->
                <div v-if="currentView === 'grid'" :key="'grid'" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div v-for="subject in subjects" :key="subject.id" 
                        class="rounded-3xl border bg-card overflow-hidden shadow-sm hover:shadow-md transition-all flex flex-col group relative border-t-4"
                        :style="`border-top-color: ${subject.is_active ? '#8b5cf6' : '#94a3b8'}`"
                    >
                        <!-- Selector -->
                        <div class="absolute top-4 left-4 z-10">
                             <button @click="toggleSelect(subject.id)" class="h-6 w-6 rounded-lg border-2 flex items-center justify-center transition-all bg-white/50 backdrop-blur-sm" :class="selectedIds.includes(subject.id) ? 'bg-violet-600 border-violet-600 text-white' : 'border-slate-300'">
                                  <component :is="selectedIds.includes(subject.id) ? CheckSquare : Square" class="h-4 w-4" />
                             </button>
                        </div>

                        <div class="p-6 pb-4 flex-1">
                            <div class="flex items-start justify-between mb-4">
                                <div class="h-10 w-10 rounded-xl bg-violet-600/10 flex items-center justify-center font-black text-violet-700 group-hover:bg-violet-600 group-hover:text-white transition-all text-sm shadow-inner border border-violet-100 uppercase">
                                     {{ subject.code.substring(0, 2) }}
                                </div>
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="ghost" size="icon" class="h-8 w-8"><MoreHorizontal class="h-4 w-4" /></Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end" class="w-56 font-pulsar">
                                        <DropdownMenuItem v-if="hasPermission('curriculum.update')" @click="openEdit(subject)"><Edit class="mr-2 h-4 w-4 text-amber-500" /> Edit Subject</DropdownMenuItem>
                                        <DropdownMenuItem as-child><Link :href="route('curriculum.subjects.show', subject.id)"><Eye class="mr-2 h-4 w-4 text-violet-600" /> Performance Analytics</Link></DropdownMenuItem>
                                        <DropdownMenuItem as-child><Link :href="route('curriculum.subjects.allocate', subject.id)"><Layers class="mr-2 h-4 w-4 text-blue-600" /> Grade Allocations</Link></DropdownMenuItem>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem v-if="hasPermission('curriculum.delete')" @click="confirmDelete(subject)" class="text-rose-600 font-bold"><Trash2 class="mr-2 h-4 w-4" /> Delete Subject</DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                            
                            <div class="space-y-1">
                                 <Badge variant="outline" class="border-0 bg-violet-50 text-violet-600 text-[8px] font-black uppercase tracking-widest px-2 py-0.5 rounded-lg mb-2">{{ subject.learning_area || 'Core Area' }}</Badge>
                                 <h3 class="text-xl font-bold text-slate-900 group-hover:text-violet-700 transition-colors leading-tight">{{ subject.name }}</h3>
                                 <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ subject.code }} • {{ subject.subject_type }} Model</p>
                                 <p class="text-[11px] text-muted-foreground font-medium mt-2 line-clamp-2 italic opacity-80">{{ subject.description || 'Core academic subject mapping instructional delivery standards.' }}</p>
                            </div>
                            
                            <div class="mt-8 grid grid-cols-2 gap-3">
                                 <div class="p-3 rounded-xl bg-slate-50 border shadow-inner text-center group-hover:bg-white transition-all">
                                      <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Topics</p>
                                      <p class="text-lg font-black text-slate-900">{{ subject.strands_count }}</p>
                                 </div>
                                 <div class="p-3 rounded-xl bg-slate-50 border shadow-inner text-center group-hover:bg-white transition-all">
                                      <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Status</p>
                                      <div class="flex items-center justify-center gap-1.5 pt-0.5">
                                           <div class="h-2 w-2 rounded-full" :class="subject.is_active ? 'bg-emerald-500' : 'bg-slate-300'"></div>
                                           <p class="text-[9px] font-black text-slate-900 uppercase italic">{{ subject.is_active ? 'Active' : 'Inactive' }}</p>
                                      </div>
                                 </div>
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-slate-50/50 border-t flex items-center justify-between mt-auto">
                             <div class="flex items-center gap-2">
                                  <span class="text-[9px] font-black text-slate-300 uppercase tracking-widest">SEQ INDEX #{{ subject.display_order }}</span>
                             </div>
                             <Link :href="route('curriculum.subjects.show', subject.id)" class="text-[10px] font-black text-violet-600 hover:underline uppercase tracking-tight">
                                Matrix Hub
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
                                     <button @click="toggleSelectAll" class="h-6 w-6 rounded-lg border-2 flex items-center justify-center transition-all bg-white mx-auto shadow-sm" :class="selectedIds.length === subjects.length ? 'bg-violet-600 border-violet-600 text-white' : 'border-slate-300'">
                                          <component :is="selectedIds.length === subjects.length ? CheckSquare : Square" class="h-4 w-4" />
                                     </button>
                                </th>
                                <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-widest">Subject Matrix</th>
                                <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-widest">Learning Area</th>
                                <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-widest text-center">Strands</th>
                                <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 tracking-widest text-center">Status</th>
                                <th class="px-6 py-4 text-right text-xs font-bold uppercase text-slate-500 tracking-widest">Operations</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y text-sm">
                            <tr v-for="subject in subjects" :key="subject.id" class="group hover:bg-slate-50/70 transition-colors">
                                <td class="px-6 py-4 text-center">
                                     <button @click="toggleSelect(subject.id)" class="h-6 w-6 rounded-lg border-2 flex items-center justify-center transition-all bg-white mx-auto shadow-sm" :class="selectedIds.includes(subject.id) ? 'bg-violet-600 border-violet-600 text-white' : 'border-slate-200'">
                                          <component :is="selectedIds.includes(subject.id) ? CheckSquare : Square" class="h-4 w-4" />
                                     </button>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-xl bg-violet-600/10 flex items-center justify-center font-black text-violet-700 group-hover:bg-violet-600 group-hover:text-white transition-all text-sm shadow-inner border border-violet-100 uppercase">
                                             {{ subject.code.substring(0, 2) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900 group-hover:text-violet-700 transition-colors leading-none tracking-tight text-base mb-1">{{ subject.name }}</div>
                                            <div class="text-[10px] font-black text-slate-400 mt-0.5 tracking-widest uppercase italic">{{ subject.code }} • {{ subject.subject_type }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-xs text-indigo-600">{{ subject.learning_area || 'Universal' }}</span>
                                        <span class="text-[9px] font-black text-slate-400 uppercase tracking-tighter">{{ subject.department?.name || 'Standard Dept' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                     <div class="flex flex-col items-center">
                                         <span class="font-black text-slate-900 text-lg leading-none">{{ subject.strands_count }}</span>
                                         <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest mt-1">Strands</span>
                                     </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <Badge :class="getStatusColor(subject.is_active)" class="rounded-full px-2 py-0.5 text-[8px] font-black uppercase tracking-widest border-0">
                                        {{ subject.is_active ? 'Active' : 'Inactive' }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Button v-if="hasPermission('curriculum.update')" variant="ghost" size="icon" class="h-8 w-8 text-slate-400 hover:text-amber-600" @click="openEdit(subject)"><Edit class="h-3.5 w-3.5" /></Button>
                                        <Button v-if="hasPermission('curriculum.delete')" variant="ghost" size="icon" class="h-8 w-8 text-slate-400 hover:text-rose-600" @click="confirmDelete(subject)"><Trash2 class="h-3.5 w-3.5" /></Button>
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" size="icon" class="h-8 w-8"><MoreHorizontal class="h-4 w-4" /></Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end" class="w-56 font-pulsar">
                                                <DropdownMenuItem as-child><Link :href="route('curriculum.subjects.show', subject.id)"><Eye class="mr-2 h-4 w-4 text-violet-600" /> Details</Link></DropdownMenuItem>
                                                <DropdownMenuItem v-if="hasPermission('curriculum.update')" as-child><Link :href="route('curriculum.subjects.allocate', subject.id)"><Layers class="mr-2 h-4 w-4 text-blue-600" /> Allocations</Link></DropdownMenuItem>
                                                <DropdownMenuItem v-if="hasPermission('curriculum.update')" @click="openEdit(subject)"><Edit class="mr-2 h-4 w-4 text-amber-500" /> Edit</DropdownMenuItem>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem v-if="hasPermission('curriculum.delete')" @click="confirmDelete(subject)" class="text-rose-600 font-bold"><Trash2 class="mr-2 h-4 w-4" /> Purge</DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="subjects.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-muted-foreground italic font-medium">
                                    No subject records found in the current library search.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </transition>

            <!-- Sync Callout -->
            <div class="mt-2 p-5 rounded-2xl bg-slate-900 text-white flex flex-col md:flex-row items-center justify-between gap-6 shadow-xl border border-slate-700 transition-all hover:bg-slate-950 group">
                <div class="flex items-center gap-4">
                    <div class="h-10 w-10 rounded-xl bg-white/10 flex items-center justify-center group-hover:bg-violet-600 group-hover:text-white transition-all">
                         <BadgeCheck class="h-5 w-5 text-violet-400 group-hover:text-white" />
                    </div>
                    <div>
                        <h3 class="font-bold text-sm tracking-tight text-white/90 uppercase">Instructional Matrix Synced</h3>
                        <p class="text-slate-400 text-[10px] mt-0.5 tracking-wider font-semibold">Subject allocations and instructional weighting finalized for the 2026 Academic Cycle.</p>
                    </div>
                </div>
                <!-- Action for UI -->
                 <div class="flex gap-2">
                      <Button variant="outline" size="sm" class="bg-white/5 border-white/10 text-white hover:bg-white/20 h-9 font-bold px-4 rounded-xl text-xs uppercase cursor-default">Matrix: Optimized</Button>
                 </div>
            </div>
            
            <!-- Modals -->
            <SubjectModal 
                :show="showModal" 
                :subject="editingSubject" 
                :learning-areas="learningAreas"
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
