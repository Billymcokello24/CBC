<script setup lang="ts">
import {
    LayoutDashboard, Users, BookOpen, ClipboardList,
    Calendar, Clock, Settings, LogOut, ChevronRight,
    Search, Bell, User, GraduationCap, School,
    BookMarked, Bus, Building2, Heart, Trophy,
    TrendingUp, DollarSign, MessageSquare, ChevronDown, ShieldCheck
} from 'lucide-vue-next';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { 
    Sidebar, 
    SidebarContent, 
    SidebarHeader, 
    SidebarFooter,
    SidebarMenu,
    SidebarMenuItem,
    SidebarMenuButton,
    SidebarGroup,
    SidebarGroupLabel,
    SidebarGroupContent
} from '@/components/ui/sidebar';

const { props } = usePage();
const user = computed(() => props.auth.user);
const permissions = computed(() => props.auth.permissions || []);

const hasPermission = (permission: string) => {
    if (permissions.value.includes('super_admin')) return true;
    return permissions.value.includes(permission);
};

const canAny = (perms: string[]) => {
    return perms.some(p => hasPermission(p));
};

const isSuperAdmin = computed(() => props.auth.is_super_admin);
const isImpersonating = computed(() => props.auth.impersonating?.active);

const saasNavigation = [
    {
        title: 'SaaS Management',
        items: [
            {
                title: 'System Dashboard',
                href: '/super-admin/dashboard',
                icon: LayoutDashboard,
            },
            {
                title: 'Manage Schools',
                href: '/super-admin/schools',
                icon: School,
            },
            {
                title: 'System Settings',
                href: '/super-admin/settings',
                icon: Settings,
            },
            {
                title: 'Maintenance',
                href: '/super-admin/maintenance',
                icon: ShieldCheck,
                children: [
                    { title: 'Backups', href: '/super-admin/backups' },
                    { title: 'Logs', href: '/super-admin/logs' },
                    { title: 'Performance', href: '/super-admin/performance' },
                ]
            }
        ],
    },
];

const navigation = [
// ... (rest of the navigation array remains same)
    {
        title: 'Core',
        items: [
            {
                title: 'Dashboard',
                href: '/dashboard',
                icon: LayoutDashboard,
            },
            {
                title: 'Learners', // changed from 'Students' to 'Learners' (UI label only)
                href: '/students',
                icon: Users,
                permissions: ['students.view', 'students.view_own'],
                children: [
                    { title: 'All Learners', href: '/students', permissions: ['students.view'] },
                    { title: 'Admission', href: '/students/create', permissions: ['students.create'] },
                    { title: 'Enrollments', href: '/students/enrollments', permissions: ['students.enroll'] },
                    { title: 'Graduates', href: '/students/graduates', permissions: ['students.view'] },
                ],
            },
            {
                title: 'Teachers',
                href: '/teachers',
                icon: GraduationCap,
                permissions: ['teachers.view', 'teachers.view_own'],
                children: [
                    { title: 'All Teachers', href: '/teachers', permissions: ['teachers.view'] },
                    { title: 'Staff Directory', href: '/teachers/directory', permissions: ['teachers.view'] },
                    { title: 'Assignments', href: '/teachers/assignments', permissions: ['teachers.manage'] },
                ],
            },
        ],
    },
    {
        title: 'Academic',
        items: [
            {
                title: 'Curriculum',
                href: '/curriculum',
                icon: BookOpen,
                permissions: ['curriculum.view'],
                children: [
                    { title: 'Learning Areas', href: '/curriculum/learning-areas' },
                    { title: 'Subjects', href: '/curriculum/subjects' },
                    { title: 'Strands', href: '/curriculum/strands' },
                    { title: 'Competencies', href: '/curriculum/competencies' },
                ],
            },
            {
                title: 'School Structure',
                href: '/grades',
                icon: School,
                permissions: ['classes.view'],
                children: [
                    { title: 'Grades', href: '/grades' },
                    { title: 'Classes', href: '/classes' },
                    { title: 'Streams', href: '/streams' },
                    { title: 'Departments', href: '/departments' },
                ],
            },
            {
                title: 'Assessments',
                href: '/assessments',
                icon: ClipboardList,
                permissions: ['assessments.view', 'assessments.view_own'],
                children: [
                    { title: 'Results', href: '/assessments/results' },
                    { title: 'Grading', href: '/assessments/grading', permissions: ['assessments.grade'] },
                    { title: 'Rubrics', href: '/assessments/rubrics' },
                    { title: 'Report Cards', href: '/assessments/report-cards' },
                    { title: 'Bulk Upload', href: '/assessments/bulk-upload' },
                ],
            },
            {
                title: 'Attendance',
                href: '/attendance',
                icon: User,
                permissions: ['attendance.view', 'attendance.view_own'],
                children: [
                    { title: 'Overview', href: '/attendance' },
                    { title: 'Mark Attendance', href: '/attendance/mark', permissions: ['attendance.mark'] },
                    { title: 'Reports', href: '/attendance/reports' },
                ],
            },
            {
                title: 'Timetable',
                href: '/timetable',
                icon: Clock,
                permissions: ['timetable.view', 'timetable.view_own'],
                children: [
                    { title: 'My Timetable', href: '/timetable/my', permissions: ['timetable.view_own'] },
                    { title: 'Global Schedule', href: '/timetable', permissions: ['timetable.view'] },
                ],
            },
        ],
    },
    {
        title: 'Management',
        items: [
            {
                title: 'Finance',
                href: '/finance',
                icon: DollarSign,
                permissions: ['finance.view', 'finance.view_own'],
                children: [
                    { title: 'Fee Structure', href: '/finance/fee-structures', permissions: ['finance.view'] },
                    { title: 'Invoices', href: '/finance/invoices', permissions: ['finance.view_own'] },
                    { title: 'Payments', href: '/finance/payments', permissions: ['finance.view_own'] },
                    { title: 'Fee Reports', href: '/finance/reports', permissions: ['finance.reports'] },
                    { title: 'Expenses', href: '/finance/expenses', permissions: ['finance.create'] },
                ],
            },
            {
                title: 'Library',
                href: '/library',
                icon: BookMarked,
                permissions: ['library.view', 'library.manage'],
                children: [
                    { title: 'Books', href: '/library/books', permissions: ['library.view'] },
                    { title: 'Issue/Return', href: '/library/transactions', permissions: ['library.issue_books'] },
                    { title: 'Categories', href: '/library/categories', permissions: ['library.manage'] },
                    { title: 'E-Resources', href: '/library/e-resources', permissions: ['library.view'] },
                ],
            },
            {
                title: 'Transport',
                href: '/transport',
                icon: Bus,
                permissions: ['transport.view', 'transport.manage'],
                children: [
                    { title: 'Vehicles', href: '/transport/vehicles', permissions: ['transport.view'] },
                    { title: 'Routes', href: '/transport/routes', permissions: ['transport.view'] },
                    { title: 'Assignments', href: '/transport/assignments', permissions: ['transport.manage'] },
                    { title: 'Tracking', href: '/transport/tracking', permissions: ['transport.view'] },
                ],
            },
            {
                title: 'Hostel',
                href: '/hostel',
                icon: Building2,
                permissions: ['hostel.view', 'hostel.manage'],
                children: [
                    { title: 'Hostels', href: '/hostel/buildings', permissions: ['hostel.view'] },
                    { title: 'Room Allocation', href: '/hostel/rooms', permissions: ['hostel.manage'] },
                    { title: 'Exeat Management', href: '/hostel/exeats', permissions: ['hostel.manage'] },
                ],
            },
        ],
    },
    {
        title: 'Extras',
        items: [
            {
                title: 'Health',
                href: '/health',
                icon: Heart,
                permissions: ['health.view', 'health.view_own'],
                children: [
                    { title: 'Medical Records', href: '/health/records', permissions: ['health.view'] },
                    { title: 'Clinic Visits', href: '/health/visits', permissions: ['health.manage'] },
                    { title: 'Immunizations', href: '/health/immunizations', permissions: ['health.manage'] },
                    { title: 'Health Screening', href: '/health/screenings', permissions: ['health.manage'] },
                ],
            },
            {
                title: 'Events',
                href: '/events',
                icon: Trophy,
                permissions: ['events.view'],
                children: [
                    { title: 'Events', href: '/events', permissions: ['events.view'] },
                    { title: 'Clubs', href: '/events/clubs', permissions: ['events.view'] },
                    { title: 'Sports', href: '/events/sports', permissions: ['events.view'] },
                    { title: 'Competitions', href: '/events/competitions', permissions: ['events.view'] },
                ],
            },
            {
                title: 'Communication',
                href: '/communication',
                icon: MessageSquare,
                permissions: ['communication.messages', 'communication.notifications'],
                children: [
                    { title: 'Messages', href: '/communication/messages' },
                    { title: 'Notifications', href: '/communication/notifications' },
                    { title: 'Announcements', href: '/communication/announcements', permissions: ['communication.announcements'] },
                ],
            },
        ],
    },
];

const bottomNavigation = [
    {
        title: 'Settings',
        href: '/settings',
        icon: Settings,
        children: [
            { title: 'Profile', href: '/settings/profile' },
            { title: 'School Settings', href: '/settings/school', permissions: ['settings.update'] },
            { title: 'Academic Year', href: '/settings/academic-year', permissions: ['settings.update'] },
            { title: 'Users & Roles', href: '/settings/users', permissions: ['users.manage'] },
            { title: 'System', href: '/settings/system', permissions: ['settings.system'] },
        ],
    },
];

const filterNavItem = (item: any) => {
    if (!item.permissions) return true;
    const hasBasePermission = canAny(item.permissions);
    if (!hasBasePermission) return false;

    if (item.children) {
        item.children = item.children.filter((child: any) => {
            if (!child.permissions) return true;
            return canAny(child.permissions);
        });
        return item.children.length > 0;
    }

    return true;
};

const filteredNavigation = computed(() => {
    let result: any[] = [];
    
    // Show SaaS Management only for Super Admins when NOT impersonating
    if (isSuperAdmin.value && !isImpersonating.value) {
        result = [...saasNavigation];
    }
    
    // Process regular navigation
    const contextNav = navigation.map(group => ({
        ...group,
        items: group.items.map(item => ({
            ...item,
            children: item.children ? item.children.filter(child => !child.permissions || canAny(child.permissions)) : undefined
        })).filter(filterNavItem)
    })).filter(group => group.items.length > 0);
    
    return [...result, ...contextNav];
});

const filteredBottomNavigation = computed(() => {
    return bottomNavigation.filter(filterNavItem);
});

const activeItem = ref<string | null>(null);
const toggleItem = (title: string) => {
    activeItem.value = activeItem.value === title ? null : title;
};
</script>

<template>
    <Sidebar collapsible="offcanvas">
        <SidebarHeader class="h-16 border-b px-6 flex items-center justify-between">
            <Link href="/" class="flex items-center gap-2 font-black text-violet-600">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-600 text-white">
                    <School class="h-5 w-5" />
                </div>
                <span class="text-xl tracking-tighter">CBC.edu</span>
            </Link>
        </SidebarHeader>

        <SidebarContent class="custom-scrollbar">
            <SidebarGroup v-for="group in filteredNavigation" :key="group.title">
                <SidebarGroupLabel class="px-2 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">
                    {{ group.title }}
                </SidebarGroupLabel>
                <SidebarGroupContent>
                    <SidebarMenu class="space-y-1">
                        <SidebarMenuItem v-for="item in group.items" :key="item.title">
                            <div v-if="item.children">
                                <SidebarMenuButton
                                    @click="toggleItem(item.title)"
                                    class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm font-bold text-gray-600 transition-colors hover:bg-violet-50 hover:text-violet-600 group"
                                    :class="{ 'bg-violet-50 text-violet-600': activeItem === item.title }"
                                >
                                    <div class="flex items-center gap-3">
                                        <component :is="item.icon" class="h-4 w-4" />
                                        <span>{{ item.title }}</span>
                                    </div>
                                    <ChevronDown
                                        class="h-3 w-3 transition-transform duration-300"
                                        :class="{ 'rotate-180': activeItem === item.title }"
                                    />
                                </SidebarMenuButton>
                                <!-- Dropdown -->
                                <div v-if="activeItem === item.title" class="mt-1 ml-4 border-l pl-4 space-y-1 animate-in slide-in-from-top-2 duration-300">
                                    <Link
                                        v-for="child in item.children"
                                        :key="child.title"
                                        :href="child.href"
                                        class="block rounded-lg px-3 py-2 text-xs font-bold text-gray-500 hover:bg-violet-50 hover:text-violet-600 transition-colors"
                                    >
                                        {{ child.title }}
                                    </Link>
                                </div>
                            </div>
                            <Link
                                v-else
                                :href="item.href"
                                class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-bold text-gray-600 transition-colors hover:bg-violet-50 hover:text-violet-600"
                            >
                                <component :is="item.icon" class="h-4 w-4" />
                                <span>{{ item.title }}</span>
                            </Link>
                        </SidebarMenuItem>
                    </SidebarMenu>
                </SidebarGroupContent>
            </SidebarGroup>
        </SidebarContent>

        <SidebarFooter class="border-t p-4">
            <div class="space-y-1">
                <div v-for="item in filteredBottomNavigation" :key="item.title">
                     <div v-if="item.children">
                            <SidebarMenuButton
                                @click="toggleItem(item.title)"
                                class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm font-bold text-gray-600 transition-colors hover:bg-violet-50 hover:text-violet-600 group"
                                :class="{ 'bg-violet-50 text-violet-600': activeItem === item.title }"
                            >
                                <div class="flex items-center gap-3">
                                    <component :is="item.icon" class="h-4 w-4" />
                                    <span>{{ item.title }}</span>
                                </div>
                                <ChevronDown
                                    class="h-3 w-3 transition-transform duration-300"
                                    :class="{ 'rotate-180': activeItem === item.title }"
                                />
                            </SidebarMenuButton>
                            <div v-if="activeItem === item.title" class="mt-1 ml-4 border-l pl-4 space-y-1 animate-in slide-in-from-top-2 duration-300">
                                <Link
                                    v-for="child in item.children"
                                    :key="child.title"
                                    :href="child.href"
                                    class="block rounded-lg px-3 py-2 text-xs font-bold text-gray-500 hover:bg-violet-50 hover:text-violet-600 transition-colors"
                                >
                                    {{ child.title }}
                                </Link>
                            </div>
                        </div>
                        <Link
                            v-else
                            :href="item.href"
                            class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-bold text-gray-600 transition-colors hover:bg-violet-50 hover:text-violet-600"
                        >
                            <component :is="item.icon" class="h-4 w-4" />
                            <span>{{ item.title }}</span>
                        </Link>
                </div>
            </div>

            <!-- Profile Info -->
            <div class="mt-4 flex items-center justify-between rounded-xl bg-gray-50 p-3">
                <div class="flex items-center gap-3 overflow-hidden">
                    <div class="h-10 w-10 shrink-0 rounded-full border-2 border-white bg-violet-600 flex items-center justify-center font-black text-white text-xs">
                        {{ user?.name?.[0]?.toUpperCase() }}
                    </div>
                    <div class="flex flex-col min-w-0">
                        <span class="truncate text-xs font-black text-gray-900">{{ user?.name }}</span>
                        <span class="truncate text-[10px] font-bold text-gray-400 uppercase tracking-tighter">{{ user?.role || 'User' }}</span>
                    </div>
                </div>
                <Link
                    href="/logout"
                    method="post"
                    as="button"
                    class="rounded-lg p-2 text-gray-400 hover:bg-rose-50 hover:text-rose-600 transition-colors"
                >
                    <LogOut class="h-4 w-4" />
                </Link>
            </div>
        </SidebarFooter>
    </Sidebar>
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
