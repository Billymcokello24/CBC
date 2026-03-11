<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    LayoutGrid,
    GraduationCap,
    Users,
    School,
    BookOpen,
    ClipboardList,
    CalendarCheck,
    Clock,
    MessageSquare,
    DollarSign,
    BookMarked,
    Bus,
    Building2,
    Heart,
    Trophy,
    Compass,
    BarChart3,
    Settings,
    ChevronDown,
    FolderGit2,
    HelpCircle
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarGroup,
    SidebarGroupLabel,
    SidebarGroupContent,
} from '@/components/ui/sidebar';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import type { NavItem } from '@/types';

const page = usePage();
const currentPath = computed(() => page.url);

// Navigation structure
const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
];

const academicNavItems = [
    {
        title: 'Students',
        href: '/students',
        icon: GraduationCap,
        children: [
            { title: 'All Students', href: '/students' },
            { title: 'Add Student', href: '/students/create' },
            { title: 'Enrollments', href: '/students/enrollments' },
            { title: 'Transfers', href: '/students/transfers' },
        ],
    },
    {
        title: 'Teachers',
        href: '/teachers',
        icon: Users,
        children: [
            { title: 'All Teachers', href: '/teachers' },
            { title: 'Add Teacher', href: '/teachers/create' },
            { title: 'Departments', href: '/departments' },
            { title: 'Leave Management', href: '/teachers/leaves' },
        ],
    },
    {
        title: 'Classes',
        href: '/classes',
        icon: School,
        children: [
            { title: 'All Classes', href: '/classes' },
            { title: 'Grade Levels', href: '/grades' },
            { title: 'Streams', href: '/streams' },
            { title: 'Class Allocation', href: '/classes/allocation' },
        ],
    },
    {
        title: 'Guardians',
        href: '/guardians',
        icon: Users,
    },
];

const curriculumNavItems = [
    {
        title: 'Curriculum',
        href: '/curriculum',
        icon: BookOpen,
        children: [
            { title: 'Learning Areas', href: '/curriculum/learning-areas' },
            { title: 'Subjects', href: '/curriculum/subjects' },
            { title: 'Strands', href: '/curriculum/strands' },
            { title: 'Competencies', href: '/curriculum/competencies' },
        ],
    },
    {
        title: 'Assessments',
        href: '/assessments',
        icon: ClipboardList,
        children: [
            { title: 'All Assessments', href: '/assessments' },
            { title: 'Create Assessment', href: '/assessments/create' },
            { title: 'Grading', href: '/assessments/grading' },
            { title: 'Report Cards', href: '/assessments/report-cards' },
            { title: 'Rubrics', href: '/assessments/rubrics' },
        ],
    },
    {
        title: 'Attendance',
        href: '/attendance',
        icon: CalendarCheck,
        children: [
            { title: 'Mark Attendance', href: '/attendance/mark' },
            { title: 'Attendance Report', href: '/attendance/reports' },
            { title: 'Student Attendance', href: '/attendance/students' },
            { title: 'Teacher Attendance', href: '/attendance/teachers' },
        ],
    },
    {
        title: 'Timetable',
        href: '/timetable',
        icon: Clock,
        children: [
            { title: 'Class Timetables', href: '/timetable' },
            { title: 'Teacher Timetables', href: '/timetable/teachers' },
            { title: 'Period Setup', href: '/timetable/periods' },
            { title: 'Room Management', href: '/timetable/rooms' },
        ],
    },
];

const managementNavItems = [
    {
        title: 'Finance',
        href: '/finance',
        icon: DollarSign,
        children: [
            { title: 'Fee Structure', href: '/finance/fee-structures' },
            { title: 'Invoices', href: '/finance/invoices' },
            { title: 'Payments', href: '/finance/payments' },
            { title: 'Fee Reports', href: '/finance/reports' },
            { title: 'Expenses', href: '/finance/expenses' },
        ],
    },
    {
        title: 'Communication',
        href: '/communication',
        icon: MessageSquare,
        children: [
            { title: 'Announcements', href: '/communication/announcements' },
            { title: 'Messages', href: '/communication/messages' },
            { title: 'Notifications', href: '/communication/notifications' },
            { title: 'SMS', href: '/communication/sms' },
        ],
    },
    {
        title: 'Library',
        href: '/library',
        icon: BookMarked,
        children: [
            { title: 'Books', href: '/library/books' },
            { title: 'Issue/Return', href: '/library/transactions' },
            { title: 'Categories', href: '/library/categories' },
            { title: 'E-Resources', href: '/library/e-resources' },
        ],
    },
    {
        title: 'Transport',
        href: '/transport',
        icon: Bus,
        children: [
            { title: 'Vehicles', href: '/transport/vehicles' },
            { title: 'Routes', href: '/transport/routes' },
            { title: 'Assignments', href: '/transport/assignments' },
            { title: 'Tracking', href: '/transport/tracking' },
        ],
    },
    {
        title: 'Hostel',
        href: '/hostel',
        icon: Building2,
        children: [
            { title: 'Hostels', href: '/hostel/buildings' },
            { title: 'Room Allocation', href: '/hostel/rooms' },
            { title: 'Exeat Management', href: '/hostel/exeats' },
        ],
    },
    {
        title: 'Health',
        href: '/health',
        icon: Heart,
        children: [
            { title: 'Medical Records', href: '/health/records' },
            { title: 'Clinic Visits', href: '/health/visits' },
            { title: 'Immunizations', href: '/health/immunizations' },
            { title: 'Health Screening', href: '/health/screenings' },
        ],
    },
];

const extrasNavItems = [
    {
        title: 'Events & Activities',
        href: '/events',
        icon: Trophy,
        children: [
            { title: 'Events', href: '/events' },
            { title: 'Clubs', href: '/events/clubs' },
            { title: 'Sports', href: '/events/sports' },
            { title: 'Competitions', href: '/events/competitions' },
        ],
    },
    {
        title: 'Career Guidance',
        href: '/career',
        icon: Compass,
        children: [
            { title: 'Career Pathways', href: '/career/pathways' },
            { title: 'Counseling', href: '/career/counseling' },
            { title: 'Mentorship', href: '/career/mentorship' },
        ],
    },
    {
        title: 'Reports & Analytics',
        href: '/reports',
        icon: BarChart3,
        children: [
            { title: 'Academic Reports', href: '/reports/academic' },
            { title: 'Financial Reports', href: '/reports/financial' },
            { title: 'Attendance Reports', href: '/reports/attendance' },
            { title: 'Analytics Dashboard', href: '/reports/analytics' },
        ],
    },
];

const settingsNavItems = [
    {
        title: 'Settings',
        href: '/settings',
        icon: Settings,
        children: [
            { title: 'Profile', href: '/settings/profile' },
            { title: 'School Settings', href: '/settings/school' },
            { title: 'Academic Year', href: '/settings/academic-year' },
            { title: 'Users & Roles', href: '/settings/users' },
            { title: 'System', href: '/settings/system' },
        ],
    },
];

const footerNavItems = [
    {
        title: 'Help & Support',
        href: '/help',
        icon: HelpCircle,
    },
];

const isActive = (href: string) => {
    return currentPath.value.startsWith(href);
};

const expandedItems = ref<string[]>(['Students', 'Assessments']);

const toggleExpanded = (title: string) => {
    const index = expandedItems.value.indexOf(title);
    if (index > -1) {
        expandedItems.value.splice(index, 1);
    } else {
        expandedItems.value.push(title);
    }
};
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/dashboard">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent class="overflow-x-hidden">
            <!-- Main Navigation -->
            <SidebarGroup>
                <SidebarGroupContent>
                    <SidebarMenu>
                        <SidebarMenuItem v-for="item in mainNavItems" :key="item.title">
                            <SidebarMenuButton
                                as-child
                                :is-active="isActive(item.href)"
                                tooltip-content="Dashboard"
                            >
                                <Link :href="item.href">
                                    <component :is="item.icon" class="h-4 w-4" />
                                    <span>{{ item.title }}</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    </SidebarMenu>
                </SidebarGroupContent>
            </SidebarGroup>

            <!-- Academic Section -->
            <SidebarGroup>
                <SidebarGroupLabel>Academic</SidebarGroupLabel>
                <SidebarGroupContent>
                    <SidebarMenu>
                        <template v-for="item in academicNavItems" :key="item.title">
                            <Collapsible
                                v-if="item.children"
                                v-model:open="expandedItems"
                                class="group/collapsible"
                            >
                                <SidebarMenuItem>
                                    <CollapsibleTrigger as-child>
                                        <SidebarMenuButton
                                            :is-active="isActive(item.href)"
                                            :tooltip-content="item.title"
                                        >
                                            <component :is="item.icon" class="h-4 w-4" />
                                            <span>{{ item.title }}</span>
                                            <ChevronDown class="ml-auto h-4 w-4 transition-transform group-data-[state=open]/collapsible:rotate-180" />
                                        </SidebarMenuButton>
                                    </CollapsibleTrigger>
                                    <CollapsibleContent>
                                        <SidebarMenu class="ml-4 border-l border-sidebar-border pl-2">
                                            <SidebarMenuItem v-for="child in item.children" :key="child.title">
                                                <SidebarMenuButton
                                                    as-child
                                                    :is-active="currentPath === child.href"
                                                    size="sm"
                                                >
                                                    <Link :href="child.href">
                                                        <span>{{ child.title }}</span>
                                                    </Link>
                                                </SidebarMenuButton>
                                            </SidebarMenuItem>
                                        </SidebarMenu>
                                    </CollapsibleContent>
                                </SidebarMenuItem>
                            </Collapsible>
                            <SidebarMenuItem v-else>
                                <SidebarMenuButton
                                    as-child
                                    :is-active="isActive(item.href)"
                                    :tooltip-content="item.title"
                                >
                                    <Link :href="item.href">
                                        <component :is="item.icon" class="h-4 w-4" />
                                        <span>{{ item.title }}</span>
                                    </Link>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                        </template>
                    </SidebarMenu>
                </SidebarGroupContent>
            </SidebarGroup>

            <!-- Curriculum & Learning Section -->
            <SidebarGroup>
                <SidebarGroupLabel>Learning</SidebarGroupLabel>
                <SidebarGroupContent>
                    <SidebarMenu>
                        <template v-for="item in curriculumNavItems" :key="item.title">
                            <Collapsible
                                v-if="item.children"
                                class="group/collapsible"
                            >
                                <SidebarMenuItem>
                                    <CollapsibleTrigger as-child>
                                        <SidebarMenuButton
                                            :is-active="isActive(item.href)"
                                            :tooltip-content="item.title"
                                        >
                                            <component :is="item.icon" class="h-4 w-4" />
                                            <span>{{ item.title }}</span>
                                            <ChevronDown class="ml-auto h-4 w-4 transition-transform group-data-[state=open]/collapsible:rotate-180" />
                                        </SidebarMenuButton>
                                    </CollapsibleTrigger>
                                    <CollapsibleContent>
                                        <SidebarMenu class="ml-4 border-l border-sidebar-border pl-2">
                                            <SidebarMenuItem v-for="child in item.children" :key="child.title">
                                                <SidebarMenuButton
                                                    as-child
                                                    :is-active="currentPath === child.href"
                                                    size="sm"
                                                >
                                                    <Link :href="child.href">
                                                        <span>{{ child.title }}</span>
                                                    </Link>
                                                </SidebarMenuButton>
                                            </SidebarMenuItem>
                                        </SidebarMenu>
                                    </CollapsibleContent>
                                </SidebarMenuItem>
                            </Collapsible>
                        </template>
                    </SidebarMenu>
                </SidebarGroupContent>
            </SidebarGroup>

            <!-- Management Section -->
            <SidebarGroup>
                <SidebarGroupLabel>Management</SidebarGroupLabel>
                <SidebarGroupContent>
                    <SidebarMenu>
                        <template v-for="item in managementNavItems" :key="item.title">
                            <Collapsible
                                v-if="item.children"
                                class="group/collapsible"
                            >
                                <SidebarMenuItem>
                                    <CollapsibleTrigger as-child>
                                        <SidebarMenuButton
                                            :is-active="isActive(item.href)"
                                            :tooltip-content="item.title"
                                        >
                                            <component :is="item.icon" class="h-4 w-4" />
                                            <span>{{ item.title }}</span>
                                            <ChevronDown class="ml-auto h-4 w-4 transition-transform group-data-[state=open]/collapsible:rotate-180" />
                                        </SidebarMenuButton>
                                    </CollapsibleTrigger>
                                    <CollapsibleContent>
                                        <SidebarMenu class="ml-4 border-l border-sidebar-border pl-2">
                                            <SidebarMenuItem v-for="child in item.children" :key="child.title">
                                                <SidebarMenuButton
                                                    as-child
                                                    :is-active="currentPath === child.href"
                                                    size="sm"
                                                >
                                                    <Link :href="child.href">
                                                        <span>{{ child.title }}</span>
                                                    </Link>
                                                </SidebarMenuButton>
                                            </SidebarMenuItem>
                                        </SidebarMenu>
                                    </CollapsibleContent>
                                </SidebarMenuItem>
                            </Collapsible>
                        </template>
                    </SidebarMenu>
                </SidebarGroupContent>
            </SidebarGroup>

            <!-- Extras Section -->
            <SidebarGroup>
                <SidebarGroupLabel>Extras</SidebarGroupLabel>
                <SidebarGroupContent>
                    <SidebarMenu>
                        <template v-for="item in extrasNavItems" :key="item.title">
                            <Collapsible
                                v-if="item.children"
                                class="group/collapsible"
                            >
                                <SidebarMenuItem>
                                    <CollapsibleTrigger as-child>
                                        <SidebarMenuButton
                                            :is-active="isActive(item.href)"
                                            :tooltip-content="item.title"
                                        >
                                            <component :is="item.icon" class="h-4 w-4" />
                                            <span>{{ item.title }}</span>
                                            <ChevronDown class="ml-auto h-4 w-4 transition-transform group-data-[state=open]/collapsible:rotate-180" />
                                        </SidebarMenuButton>
                                    </CollapsibleTrigger>
                                    <CollapsibleContent>
                                        <SidebarMenu class="ml-4 border-l border-sidebar-border pl-2">
                                            <SidebarMenuItem v-for="child in item.children" :key="child.title">
                                                <SidebarMenuButton
                                                    as-child
                                                    :is-active="currentPath === child.href"
                                                    size="sm"
                                                >
                                                    <Link :href="child.href">
                                                        <span>{{ child.title }}</span>
                                                    </Link>
                                                </SidebarMenuButton>
                                            </SidebarMenuItem>
                                        </SidebarMenu>
                                    </CollapsibleContent>
                                </SidebarMenuItem>
                            </Collapsible>
                        </template>
                    </SidebarMenu>
                </SidebarGroupContent>
            </SidebarGroup>

            <!-- Settings Section -->
            <SidebarGroup>
                <SidebarGroupLabel>System</SidebarGroupLabel>
                <SidebarGroupContent>
                    <SidebarMenu>
                        <template v-for="item in settingsNavItems" :key="item.title">
                            <Collapsible
                                v-if="item.children"
                                class="group/collapsible"
                            >
                                <SidebarMenuItem>
                                    <CollapsibleTrigger as-child>
                                        <SidebarMenuButton
                                            :is-active="isActive(item.href)"
                                            :tooltip-content="item.title"
                                        >
                                            <component :is="item.icon" class="h-4 w-4" />
                                            <span>{{ item.title }}</span>
                                            <ChevronDown class="ml-auto h-4 w-4 transition-transform group-data-[state=open]/collapsible:rotate-180" />
                                        </SidebarMenuButton>
                                    </CollapsibleTrigger>
                                    <CollapsibleContent>
                                        <SidebarMenu class="ml-4 border-l border-sidebar-border pl-2">
                                            <SidebarMenuItem v-for="child in item.children" :key="child.title">
                                                <SidebarMenuButton
                                                    as-child
                                                    :is-active="currentPath === child.href"
                                                    size="sm"
                                                >
                                                    <Link :href="child.href">
                                                        <span>{{ child.title }}</span>
                                                    </Link>
                                                </SidebarMenuButton>
                                            </SidebarMenuItem>
                                        </SidebarMenu>
                                    </CollapsibleContent>
                                </SidebarMenuItem>
                            </Collapsible>
                        </template>
                    </SidebarMenu>
                </SidebarGroupContent>
            </SidebarGroup>
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
