import PageHeader from '@/components/ui/PageHeader.vue';

const props = defineProps<{
    teachers: any;
    departments: any[];
    roles: any[];
    stats: any;
    filters: any;
    availableClasses: any[];
    availableSubjects: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'User Directory', href: '/staffs/directory' },
    { title: 'Staff Registry', href: '/staffs' },
];

const searchQuery = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || 'all');
const departmentFilter = ref(props.filters.department_id || 'all');
const roleFilter = ref(props.filters.role || 'all');
const viewMode = ref(props.filters.view || 'grid');

const applyFilters = () => {
    router.get('/staffs', { 
        search: searchQuery.value,
        status: statusFilter.value,
        department_id: departmentFilter.value,
        role: roleFilter.value,
        view: viewMode.value,
    }, { preserveState: true, replace: true });
};

watch([searchQuery, statusFilter, departmentFilter, roleFilter, viewMode], () => {
    applyFilters();
});

const getStatusColor = (status: string) => {
    switch (status?.toLowerCase()) {
        case 'active': return 'bg-emerald-500 text-white shadow-sm';
        case 'on_leave': return 'bg-amber-500 text-white shadow-sm';
        case 'suspended': return 'bg-rose-500 text-white shadow-sm';
        default: return 'bg-slate-400 text-white';
    }
};

const deleteStaff = (id: number) => {
    if (confirm('Are you sure you want to delete this staff member?')) {
        router.delete(`/staffs/${id}`);
    }
};
</script>

<template>
    <Head title="Staff Registry" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700 max-w-[1600px] mx-auto pb-20 p-6 md:p-8">
            
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1">
                    <h1 class="text-3xl font-bold tracking-tight text-foreground">Staff Registry</h1>
                    <p class="text-[15px] text-muted-foreground leading-relaxed">
                        Comprehensive management of all institutional personnel across departments.
                    </p>
                </div>
                
                <div class="flex items-center gap-3">
                    <Link
                        href="/staffs/create"
                        class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 h-10 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition-all font-inter shadow-blue-200"
                    >
                        <UserPlus class="mr-2 h-4 w-4" />
                        Add Personnel
                    </Link>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-border bg-card p-6 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                            <Users class="h-6 w-6" />
                        </div>
                        <div>
                            <p class="text-xs font-bold text-muted-foreground uppercase tracking-widest">Total Staff</p>
                            <h3 class="text-2xl font-black text-foreground">{{ stats.total }}</h3>
                        </div>
                    </div>
                </div>
                <div class="rounded-2xl border border-border bg-card p-6 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <UserCheck class="h-6 w-6" />
                        </div>
                        <div>
                            <p class="text-xs font-bold text-muted-foreground uppercase tracking-widest">Active Now</p>
                            <h3 class="text-2xl font-black text-foreground">{{ stats.active }}</h3>
                        </div>
                    </div>
                </div>
                <div class="rounded-2xl border border-border bg-card p-6 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                            <GraduationCap class="h-6 w-6" />
                        </div>
                        <div>
                            <p class="text-xs font-bold text-muted-foreground uppercase tracking-widest">Academic</p>
                            <h3 class="text-2xl font-black text-foreground">{{ stats.teaching }}</h3>
                        </div>
                    </div>
                </div>
                <div class="rounded-2xl border border-border bg-card p-6 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                            <Briefcase class="h-6 w-6" />
                        </div>
                        <div>
                            <p class="text-xs font-bold text-muted-foreground uppercase tracking-widest">Admin/Support</p>
                            <h3 class="text-2xl font-black text-foreground">{{ stats.admins + stats.non_teaching }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Toolbar -->
            <div class="flex flex-col md:flex-row gap-4 items-center justify-between bg-card/60 p-4 rounded-2xl border border-border/50 backdrop-blur-sm shadow-sm ring-1 ring-black/[0.02]">
                <div class="flex flex-1 items-center gap-4 w-full">
                    <div class="relative flex-1 max-w-sm">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                        <Input 
                            v-model="searchQuery" 
                            placeholder="Search by name, email..." 
                            class="pl-10 h-10 bg-background border-border rounded-xl text-sm focus:ring-2 focus:ring-indigo-600/10 transition-all shadow-sm"
                        />
                    </div>
                    
                    <Select v-model="roleFilter">
                        <SelectTrigger class="w-[180px] h-10 rounded-xl border-border bg-background focus:ring-2 focus:ring-indigo-600/10 shadow-sm font-medium">
                            <SelectValue placeholder="All Roles" />
                        </SelectTrigger>
                        <SelectContent class="rounded-xl shadow-xl border-border">
                            <SelectItem value="all">All Roles</SelectItem>
                            <SelectItem v-for="r in roles" :key="r.id" :value="r.name">
                                {{ r.display_name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <Select v-model="departmentFilter">
                        <SelectTrigger class="w-[180px] h-10 rounded-xl border-border bg-background focus:ring-2 focus:ring-indigo-600/10 shadow-sm font-medium">
                            <SelectValue placeholder="Departments" />
                        </SelectTrigger>
                        <SelectContent class="rounded-xl shadow-xl border-border">
                            <SelectItem value="all">All Depts</SelectItem>
                            <SelectItem v-for="dept in departments" :key="dept.id" :value="dept.id.toString()">
                                {{ dept.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="flex items-center gap-2 bg-muted/30 p-1 rounded-xl border border-border/50 shadow-inner">
                    <Button 
                        variant="ghost" 
                        size="icon" 
                        class="h-8 w-8 rounded-lg transition-all"
                        :class="viewMode === 'grid' ? 'bg-background text-foreground shadow-sm ring-1 ring-border/50' : 'text-muted-foreground'"
                        @click="viewMode = 'grid'"
                    >
                        <LayoutGrid class="h-4 w-4" />
                    </Button>
                    <Button 
                        variant="ghost" 
                        size="icon" 
                        class="h-8 w-8 rounded-lg transition-all"
                        :class="viewMode === 'list' ? 'bg-background text-foreground shadow-sm ring-1 ring-border/50' : 'text-muted-foreground'"
                        @click="viewMode = 'list'"
                    >
                        <ListIcon class="h-4 w-4" />
                    </Button>
                </div>
            </div>

            <!-- Staff Grid -->
            <div v-if="viewMode === 'grid'" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <div v-for="teacher in teachers.data" :key="teacher.id" class="group relative overflow-hidden rounded-[2.5rem] border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/40 transition-all hover:shadow-2xl hover:shadow-indigo-100/50 hover:border-blue-400/30">
                    <Link :href="`/staffs/${teacher.id}`" class="flex flex-col items-center text-center space-y-4">
                        <div class="relative">
                            <div class="h-20 w-20 rounded-[2rem] overflow-hidden bg-slate-50 flex items-center justify-center ring-4 ring-white shadow-inner group-hover:scale-110 transition-transform duration-500">
                                <img v-if="teacher.photo_url" :src="teacher.photo_url" class="h-full w-full object-cover" />
                                <div v-else class="font-black text-2xl text-slate-400">
                                    {{ teacher.first_name[0] }}{{ teacher.last_name[0] }}
                                </div>
                            </div>
                            <div class="absolute -bottom-1 -right-1 h-6 w-6 rounded-lg border-2 border-white shadow-sm flex items-center justify-center" :class="getStatusColor(teacher.status)">
                                <BadgeCheck v-if="teacher.status === 'active'" class="h-3 w-3 text-white" />
                            </div>
                        </div>

                        <div class="space-y-1 w-full">
                            <h4 class="font-black text-slate-900 group-hover:text-blue-600 transition-colors truncate px-2">
                                {{ teacher.first_name }} {{ teacher.last_name }}
                            </h4>
                            <div class="flex items-center justify-center gap-2">
                                <Badge variant="outline" class="h-5 border-blue-100 bg-blue-50/50 text-blue-600 rounded-lg px-2 text-[9px] font-black uppercase tracking-widest border-none">
                                    {{ teacher.department?.name || 'Unassigned' }}
                                </Badge>
                            </div>
                        </div>
                    </Link>

                    <div class="grid grid-cols-2 gap-2 w-full pt-4 border-t border-slate-50 mt-4">
                        <Link :href="`/staffs/${teacher.id}`" class="flex items-center justify-center gap-2 h-10 rounded-2xl bg-slate-900 text-white font-bold text-xs hover:bg-blue-600 transition-all shadow-lg shadow-slate-200">
                            <Eye class="h-3.5 w-3.5" /> View
                        </Link>
                        <Link :href="`/staffs/${teacher.id}/edit`" class="flex items-center justify-center gap-2 h-10 rounded-2xl bg-slate-100 text-slate-600 font-bold text-xs hover:bg-white hover:text-blue-600 hover:shadow-lg transition-all border border-slate-200">
                            <Edit class="h-3.5 w-3.5" /> Edit
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Staff Table (List View) -->
            <div v-else class="overflow-hidden rounded-[2.5rem] border border-slate-200 bg-white shadow-xl shadow-slate-200/40">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/50">
                            <th class="p-6 text-xs font-black uppercase tracking-widest text-slate-500">Personnel</th>
                            <th class="p-6 text-xs font-black uppercase tracking-widest text-slate-500 text-center">Department</th>
                            <th class="p-6 text-xs font-black uppercase tracking-widest text-slate-500 text-center">Roles</th>
                            <th class="p-6 text-xs font-black uppercase tracking-widest text-slate-500 text-center">Status</th>
                            <th class="p-6 text-xs font-black uppercase tracking-widest text-slate-500 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="teacher in teachers.data" :key="teacher.id" @click="router.visit(`/staffs/${teacher.id}`)" class="group hover:bg-slate-50/40 transition-colors cursor-pointer">
                            <td class="p-6">
                                <div class="flex items-center gap-4">
                                    <div class="h-12 w-12 rounded-2xl overflow-hidden bg-slate-100 flex items-center justify-center font-black text-slate-400 text-sm italic group-hover:bg-blue-600 group-hover:text-white transition-all shadow-inner">
                                        <img v-if="teacher.photo_url" :src="teacher.photo_url" class="h-full w-full object-cover" />
                                        <template v-else>{{ teacher.first_name[0] }}{{ teacher.last_name[0] }}</template>
                                    </div>
                                    <div class="space-y-1">
                                        <p class="font-black text-slate-900 leading-none group-hover:text-blue-600 transition-colors">{{ teacher.first_name }} {{ teacher.last_name }}</p>
                                        <div class="flex items-center gap-2 opacity-60">
                                            <Mail class="h-3 w-3" />
                                            <p class="text-[10px] font-bold text-slate-500">{{ teacher.user?.email || 'N/A' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-6 text-center">
                                <Badge variant="outline" class="rounded-lg font-bold border-slate-100 px-3 py-1">
                                    {{ teacher.department?.name || 'N/A' }}
                                </Badge>
                            </td>
                            <td class="p-6 text-center">
                                <div class="flex flex-wrap items-center justify-center gap-1.5">
                                    <Badge v-for="role in teacher.user?.roles" :key="role.id" variant="secondary" class="bg-blue-50 text-blue-700 text-[8px] font-black uppercase px-2 py-0.5 rounded border-none">
                                        {{ role.name.replace('_', ' ') }}
                                    </Badge>
                                </div>
                            </td>
                            <td class="p-6 text-center">
                                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest" :class="getStatusColor(teacher.status)">
                                    <span class="h-1.5 w-1.5 rounded-full bg-white animate-pulse"></span>
                                    {{ teacher.status }}
                                </div>
                            </td>
                            <td class="p-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Button variant="ghost" size="icon" class="h-10 w-10 min-w-10 rounded-xl hover:bg-white hover:text-blue-600 hover:shadow-lg transition-all" as-child>
                                        <Link :href="`/staffs/${teacher.id}`">
                                            <Eye class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                    <Button variant="ghost" size="icon" class="h-10 w-10 min-w-10 rounded-xl hover:bg-white hover:text-blue-600 hover:shadow-lg transition-all" as-child>
                                        <Link :href="`/staffs/${teacher.id}/edit`">
                                            <Edit class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" size="icon" class="h-10 w-10 min-w-10 rounded-xl hover:bg-white transition-all">
                                                <MoreHorizontal class="h-4 w-4" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end" class="rounded-2xl border-slate-200 shadow-xl p-2 min-w-[180px]">
                                            <DropdownMenuItem @click="deleteStaff(teacher.id)" class="text-rose-600 focus:text-rose-600 focus:bg-rose-50 rounded-xl p-3 cursor-pointer">
                                                <Trash2 class="mr-2 h-4 w-4" /> Delete Account
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div v-if="teachers.data.length === 0" class="flex flex-col items-center justify-center py-32 space-y-4 bg-muted/10 rounded-[3rem] border-4 border-dashed border-border/60">
                <div class="h-24 w-24 rounded-[2.5rem] bg-muted/50 flex items-center justify-center border-8 border-white shadow-xl italic font-black text-slate-300 transform -rotate-6">
                    ?
                </div>
                <div class="text-center space-y-2">
                    <p class="text-xl font-black text-slate-900 uppercase italic">No personnel found</p>
                    <p class="text-sm text-muted-foreground font-medium">Try adjusting your filters or search keywords.</p>
                </div>
                <Button variant="outline" class="rounded-2xl px-8 h-12 font-black uppercase tracking-widest mt-6" @click="searchQuery = ''; statusFilter = 'all'; departmentFilter = 'all'; roleFilter = 'all';">
                    Clear All Filters
                </Button>
            </div>

            <!-- Pagination -->
            <div v-if="teachers.last_page > 1" class="flex items-center justify-between border-t border-border pt-8 px-4">
                 <div class="text-sm font-bold text-muted-foreground uppercase tracking-widest pl-2">
                    Showing {{ teachers.from }} - {{ teachers.to }} of {{ teachers.total }} personnel
                </div>
                <div class="flex items-center gap-2">
                    <Button 
                        variant="outline" 
                        class="h-12 w-12 rounded-2xl border-slate-200 shadow-sm"
                        :disabled="!teachers.prev_page_url"
                        as-child
                    >
                        <Link :href="teachers.prev_page_url || '#'" :preserve-state="true">
                            <ChevronDown class="h-5 w-5 rotate-90" />
                        </Link>
                    </Button>
                    <div class="flex items-center gap-1.5 px-4 font-black text-sm italic">
                        Page <span class="text-blue-600">{{ teachers.current_page }}</span> of {{ teachers.last_page }}
                    </div>
                    <Button 
                        variant="outline" 
                        class="h-12 w-12 rounded-2xl border-slate-200 shadow-sm"
                        :disabled="!teachers.next_page_url"
                        as-child
                    >
                        <Link :href="teachers.next_page_url || '#'" :preserve-state="true">
                            <ChevronDown class="h-5 w-5 -rotate-90" />
                        </Link>
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
