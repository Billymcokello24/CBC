<script setup lang="ts">
import {
    LayoutDashboard, Users, BookOpen, ClipboardList,
    Calendar, Clock, Settings, LogOut, ChevronRight,
    Search, Bell, User, GraduationCap, School,
    BookMarked, Bus, Building2, Heart, Trophy,
    TrendingUp, DollarSign, MessageSquare, ChevronDown, ShieldCheck,
    Database, CreditCard, ToggleLeft, Layers, Lock,
    Webhook, Mail, BarChart3, Server, LifeBuoy,
    Terminal, Fingerprint, Activity, UserMinus, HardDrive,
    Key, Zap, FileText, Globe, Cloud, Wrench, Code2,
    Baby, NotebookPen, Upload, Eye, CalendarDays, FileCheck, Megaphone, Folder
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
        title: 'Core System',
        items: [
            {
                title: 'Dashboard',
                href: '/super-admin/dashboard',
                icon: LayoutDashboard,
            },
            {
                title: 'Tenant Management',
                href: '/super-admin/schools',
                icon: School,
                children: [
                    { title: 'All Schools', href: '/super-admin/schools' },
                    { title: 'Registration Flow', href: '/super-admin/schools/create' },
                    { title: 'Activation Status', href: '/super-admin/schools/status' },
                    { title: 'Modular Config', href: '/super-admin/schools/modules' },
                ]
            },
            {
                title: 'User & Identity',
                href: '/super-admin/users',
                icon: Fingerprint,
                children: [
                    { title: 'Global Users', href: '/super-admin/users' },
                    { title: 'Role Templates', href: '/super-admin/roles' },
                    { title: 'Permission Sets', href: '/super-admin/permissions' },
                    { title: 'Active Sessions', href: '/super-admin/sessions' },
                ]
            },
            {
                title: 'Impersonation',
                href: '/super-admin/impersonation',
                icon: UserMinus,
                children: [
                    { title: 'Quick Switch', href: '/super-admin/impersonation' },
                    { title: 'Audit Logs', href: '/super-admin/impersonation/logs' },
                    { title: 'Debug Mode', href: '/super-admin/debug' },
                ]
            }
        ],
    },
    {
        title: 'Operations',
        items: [
            {
                title: 'System Monitoring',
                href: '/super-admin/monitoring',
                icon: Activity,
                children: [
                    { title: 'System Health', href: '/super-admin/dashboard' },
                    { title: 'Activity Logs', href: '/super-admin/activity-logs' },
                    { title: 'Queue Management', href: '/super-admin/queues' },
                    { title: 'Live Telemetry', href: '/super-admin/dashboard' },
                ]
            },
            {
                title: 'Database & Storage',
                href: '/super-admin/database',
                icon: Database,
                children: [
                    { title: 'DB Snapshots', href: '/super-admin/backups' },
                    { title: 'Storage Usage', href: '/super-admin/storage' },
                    { title: 'Cloud Sync', href: '/super-admin/cloud' },
                ]
            },
            {
                title: 'Billing & Plans',
                href: '/super-admin/billing',
                icon: CreditCard,
                children: [
                    { title: 'Pricing Plans', href: '/super-admin/plans' },
                    { title: 'Revenue Analytics', href: '/super-admin/revenue' },
                    { title: 'Subscribers', href: '/super-admin/subscribers' },
                    { title: 'Invoicing', href: '/super-admin/billing/invoices' },
                ]
            }
        ],
    },
    {
        title: 'Global Config',
        items: [
            {
                title: 'Configuration',
                href: '/super-admin/config',
                icon: Settings,
                children: [
                    { title: 'System Settings', href: '/super-admin/settings' },
                    { title: 'Feature Flags', href: '/super-admin/config/flags', icon: ToggleLeft },
                    { title: 'Global Templates', href: '/super-admin/config/templates' },
                    { title: 'Integrations', href: '/super-admin/config/integrations' },
                ]
            },
            {
                title: 'Module Control',
                href: '/super-admin/modules',
                icon: Layers,
                children: [
                    { title: 'Core Modules', href: '/super-admin/modules/core' },
                    { title: 'Premium Addons', href: '/super-admin/modules/premium' },
                    { title: 'Beta Testing', href: '/super-admin/modules/beta' },
                ]
            },
            {
                title: 'Security',
                href: '/super-admin/security',
                icon: Lock,
                children: [
                    { title: 'Security Audit', href: '/super-admin/security/audit' },
                    { title: 'Two-Factor Auth', href: '/super-admin/security/2fa' },
                    { title: 'API Security', href: '/super-admin/security/api' },
                    { title: 'Compliance', href: '/super-admin/security/compliance' },
                ]
            }
        ],
    },
    {
        title: 'Connectivity',
        items: [
            {
                title: 'API & Webhooks',
                href: '/super-admin/api',
                icon: Webhook,
                children: [
                    { title: 'API Keys', href: '/super-admin/api/keys' },
                    { title: 'Webhooks', href: '/super-admin/webhooks' },
                    { title: 'Documentation', href: '/super-admin/api/docs' },
                ]
            },
            {
                title: 'Communication',
                href: '/super-admin/communication',
                icon: Mail,
                children: [
                    { title: 'System Alerts', href: '/super-admin/alerts' },
                    { title: 'Broadcasts', href: '/super-admin/broadcasts' },
                    { title: 'Email Templates', href: '/super-admin/email-templates' },
                ]
            }
        ],
    },
    {
        title: 'Insights',
        items: [
            {
                title: 'Analytics',
                href: '/super-admin/analytics',
                icon: BarChart3,
                children: [
                    { title: 'Usage Trends', href: '/super-admin/analytics/usage' },
                    { title: 'Growth Metrics', href: '/super-admin/analytics/growth' },
                    { title: 'Custom Reports', href: '/super-admin/reports' },
                ]
            }
        ],
    },
    {
        title: 'Infrastructure',
        items: [
            {
                title: 'DevOps',
                href: '/super-admin/devops',
                icon: Cloud,
                children: [
                    { title: 'Server Status', href: '/super-admin/devops/servers', icon: Server },
                    { title: 'Deployments', href: '/super-admin/devops/deploys' },
                    { title: 'Environment', href: '/super-admin/devops/env' },
                ]
            },
            {
                title: 'Support',
                href: '/super-admin/support',
                icon: LifeBuoy,
                children: [
                    { title: 'Support Tickets', href: '/super-admin/support/tickets' },
                    { title: 'Knowledge Base', href: '/super-admin/support/kb' },
                    { title: 'System Updates', href: '/super-admin/support/updates' },
                ]
            },
            {
                title: 'Developer',
                href: '/super-admin/developer',
                icon: Code2,
                children: [
                    { title: 'Terminal', href: '/super-admin/developer/terminal', icon: Terminal },
                    { title: 'Database UI', href: '/super-admin/developer/db-ui' },
                    { title: 'GraphQL Playground', href: '/super-admin/developer/graphql' },
                    { title: 'Tools', href: '/super-admin/developer/tools', icon: Wrench },
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
                title: 'Users',
                href: '/staffs/directory', // Pointing to the hub as default
                icon: Users,
                permissions: ['staffs.view', 'staffs.view_own'],
                children: [
                    { title: 'Staff Directory', href: '/staffs', permissions: ['staffs.view'] },
                    { title: 'Parent Directory', href: '/parents', permissions: ['guardians.view'] },
                    { title: 'Role HUB', href: '/staffs/directory', permissions: ['staffs.view'] },
                ],
            },
        ],
    },
    {
        title: 'Academic',
        items: [
            {
                title: 'Curriculum & Planning',
                href: '/curriculum',
                icon: BookOpen,
                permissions: ['curriculum.view'],
                children: [
                    { title: 'Overview', href: '/curriculum' },
                    { title: 'Competencies', href: '/curriculum/competencies' },
                    { title: 'Scheme of Work', href: '/curriculum/planner/schemes' },
                    { title: 'Lesson Plan', href: '/curriculum/planner/lesson-plans' },
                    { title: 'Learning Resources', href: '/curriculum/resources' },
                    { title: 'Assignments', href: '/curriculum/assignments', icon: NotebookPen },
                ],
            },
            {
                title: 'CBC Evaluation',
                href: '/assessments',
                icon: ClipboardList,
                permissions: ['assessments.view', 'assessments.view_own'],
                children: [
                    { title: 'Assessment Tracker', href: '/assessments/setup', permissions: ['assessments.create'], icon: Zap },
                    { title: 'Grading Sheet', href: '/assessments/grading', permissions: ['assessments.grade'], icon: ClipboardList },
                    { title: 'Analytics & Trends', href: '/assessments/analytics', icon: BarChart3 },
                    { title: 'Report Cards', href: '/assessments/report-cards', icon: FileText },
                    { title: 'Student Portfolio', href: '/assessments/portfolio', icon: Folder },
                    { title: 'Rubric Builder', href: '/assessments/rubrics', icon: Settings },
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
            {
                title: 'Settings',
                href: '/settings',
                icon: Settings,
                children: [
                    { title: 'Profile', href: '/settings/profile' },
                    { title: 'School Settings', href: '/settings/school', permissions: ['settings.update'] },
                    { title: 'System', href: '/settings/system', permissions: ['settings.system'] },
                ],
            },
        ],
    },
];

const bottomNavigation: any[] = [];

const isParent = computed(() => {
    const roles = (props.auth as any).roles || [];
    return roles.includes('parent') || roles.includes('guardian');
});

const parentNavigation = [
    {
        title: 'Overview',
        items: [
            { title: 'Dashboard', href: '/guardian/portal', icon: LayoutDashboard },
            { title: 'My Children', href: '/guardian/children', icon: Baby },
        ],
    },
    {
        title: 'Academics',
        items: [
            { title: 'Assignments', href: '/guardian/assignments', icon: NotebookPen },
            {
                title: 'Learning Materials',
                href: '/curriculum/resources',
                icon: BookOpen,
                children: [
                    { title: 'All Resources', href: '/curriculum/resources' },
                ],
            },
            {
                title: 'Competencies',
                href: '/curriculum/competencies',
                icon: ShieldCheck,
            },
        ],
    },
    {
        title: 'Reports',
        items: [
            { title: 'Attendance', href: '/attendance', icon: CalendarDays },
            { title: 'Results & Reports', href: '/assessments/results', icon: BarChart3 },
        ],
    },
    {
        title: 'School Life',
        items: [
            { title: 'Announcements', href: '/communication/announcements', icon: Megaphone },
            { title: 'Communication', href: '/communication/messages', icon: MessageSquare },
            { title: 'Fees', href: '/finance/invoices', icon: DollarSign },
            { title: 'Documents', href: '/documents', icon: Folder },
            { title: 'Notifications', href: '/communication/notifications', icon: Bell },
            {
                title: 'Settings',
                href: '/settings/profile',
                icon: Settings,
                children: [
                    { title: 'Profile', href: '/settings/profile' },
                ],
            },
        ],
    },
];

const teacherRoles = computed(() => (props.auth as any).teacher_roles || {});
const isTeacherRole = computed(() => {
    const roles = (props.auth as any).roles || [];
    return roles.includes('teacher') || roles.includes('class_teacher') || roles.includes('hod');
});

const teacherNavigation = computed(() => {
    const nav: any[] = [
        {
            title: 'Overview',
            items: [
                { title: 'Dashboard', href: '/dashboard', icon: LayoutDashboard },
                { 
                    title: 'My Classes', 
                    href: '/classes', 
                    icon: Users,
                    hidden: !teacherRoles.value.assigned_classes_count && !teacherRoles.value.is_class_teacher
                },
                { 
                    title: 'My Subjects', 
                    href: '/curriculum', 
                    icon: BookOpen,
                    hidden: !teacherRoles.value.assigned_subjects_count
                },
            ],
        },
        {
            title: 'Academic',
            items: [
                {
                    title: 'Learners',
                    href: '/students',
                    icon: GraduationCap,
                    children: [
                        { title: 'My Students', href: '/students' },
                        { title: 'Student Profiles', href: '/students', hidden: !teacherRoles.value.is_class_teacher },
                    ],
                },
                {
                    title: 'Attendance',
                    href: '/attendance',
                    icon: Clock,
                    children: [
                        { title: 'Mark Attendance', href: '/attendance/mark' },
                        { title: 'Class Attendance', href: '/attendance/reports', hidden: !teacherRoles.value.is_class_teacher },
                    ],
                },
                {
                    title: 'CBC Evaluation',
                    href: '/assessments',
                    icon: ClipboardList,
                    children: [
                        { title: 'My Assessments', href: '/assessments' },
                        { title: 'Grading Sheet', href: '/assessments/grading' },
                        { title: 'Class Reports', href: '/assessments/analytics', hidden: !teacherRoles.value.is_class_teacher },
                    ],
                },
                { title: 'Timetable', href: '/timetable/my', icon: Calendar },
                { title: 'Remarks', href: '/curriculum/assignments', icon: NotebookPen },
            ],
        },
    ];

    if (teacherRoles.value.is_class_teacher) {
        nav.push({
            title: 'Class Management',
            items: [
                { title: 'Class List', href: '/classes', icon: Users },
                { title: 'Class Attendance', href: '/attendance/reports', icon: CalendarDays },
                { title: 'Class Reports', href: '/assessments/analytics', icon: BarChart3 },
                { title: 'Discipline', href: '/students', icon: ShieldCheck },
            ]
        });
    }

    if (teacherRoles.value.is_hod) {
        nav.push({
            title: 'Department',
            items: [
                { title: 'Dept Dashboard', href: '/dashboard', icon: LayoutDashboard },
                { title: 'Teachers', href: '/staffs', icon: Users },
                { title: 'Dept Subjects', href: '/curriculum', icon: BookMarked },
                { title: 'Dept Reports', href: '/assessments/analytics', icon: BarChart3 },
            ]
        });
    }

    return nav;
});

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
    // If Super Admin and NOT impersonating, ONLY show SaaS navigation
    if (isSuperAdmin.value && !isImpersonating.value) {
        return [...saasNavigation];
    }

    // If parent role, show parent-specific navigation
    if (isParent.value) {
        return [...parentNavigation];
    }
    
    // If teacher role, show teacher-specific navigation
    if (isTeacherRole.value) {
        return teacherNavigation.value.map(group => ({
            ...group,
            items: group.items.filter((item: any) => {
                if (item.hidden) return false;
                if (item.children) {
                    item.children = item.children.filter((child: any) => !child.hidden);
                    return item.children.length > 0 || item.href;
                }
                return true;
            })
        })).filter(group => group.items.length > 0);
    }
    
    // Otherwise (regular user OR impersonating Super Admin), show regular navigation
    const contextNav = navigation.map(group => ({
        ...group,
        items: group.items.map(item => ({
            ...item,
            children: (item as any).children ? (item as any).children.filter((child: any) => !child.permissions || canAny(child.permissions)) : undefined
        })).filter(filterNavItem)
    })).filter(group => group.items.length > 0);
    
    return [...contextNav];
});

const filteredBottomNavigation = computed(() => {
    const items = (bottomNavigation as any[]).filter(filterNavItem);
    
    // If Super Admin and NOT impersonating, we might only want to show certain base items
    if (isSuperAdmin.value && !isImpersonating.value) {
        return items.map((group: any) => ({
            ...group,
            children: group.children ? group.children.filter((child: any) => child.title === 'Profile') : undefined
        })).filter((group: any) => group.children && group.children.length > 0 || !group.children);
    }
    
    return items;
});

const activeItem = ref<string | null>(null);
const toggleItem = (title: string) => {
    activeItem.value = activeItem.value === title ? null : title;
};
</script>

<template>
    <Sidebar collapsible="offcanvas">
        <SidebarHeader class="h-auto border-b border-sidebar-border px-6 py-6 flex flex-col items-center justify-center">
            <!-- Super Admin Context (Global) -->
            <Link href="/super-admin/dashboard" v-if="isSuperAdmin && !isImpersonating" class="flex items-center gap-3 font-black text-sidebar-foreground">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary text-primary-foreground shadow-lg shadow-primary/20">
                    <span class="text-xl font-black">S</span>
                </div>
                <span class="text-xl tracking-tight font-black uppercase">SUPERADMIN</span>
            </Link>

            <!-- School/Tenant Context -->
            <Link href="/dashboard" v-else class="flex flex-col items-center gap-3 font-black text-sidebar-foreground group w-full">
                <div class="flex flex-col items-center min-w-0 w-full mb-1">
                    <span class="text-[11px] tracking-[0.15em] font-black text-center truncate w-full uppercase leading-tight">{{ $page.props.auth.school?.name || 'TailPanel' }}</span>
                    <div class="h-0.5 w-8 bg-primary/20 rounded-full mt-2"></div>
                </div>
                
                <div v-if="$page.props.auth.school?.logo" class="h-20 w-20 shrink-0 overflow-hidden rounded-2xl border-2 border-sidebar-border bg-white shadow-xl shadow-slate-200/50 transition-all duration-300 group-hover:scale-105 group-hover:border-primary/20">
                    <img :src="$page.props.auth.school.logo" :alt="$page.props.auth.school.name" class="h-full w-full object-contain p-2" />
                </div>
                <div v-else class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-primary text-primary-foreground shadow-xl shadow-primary/20 transition-all duration-300 group-hover:scale-105">
                    <span class="text-2xl font-black">{{ $page.props.auth.school?.name?.[0] || 'T' }}</span>
                </div>
            </Link>
        </SidebarHeader>

        <SidebarContent class="custom-scrollbar pb-32">
            <SidebarGroup v-for="group in filteredNavigation" :key="group.title">
                <SidebarGroupLabel class="px-2 text-[10px] font-black uppercase tracking-[0.2em] text-sidebar-foreground/40">
                    {{ group.title }}
                </SidebarGroupLabel>
                <SidebarGroupContent>
                    <SidebarMenu class="space-y-1">
                        <SidebarMenuItem v-for="item in group.items" :key="item.title">
                            <div v-if="item.children">
                                <SidebarMenuButton
                                    @click="toggleItem(item.title)"
                                    class="flex w-full items-center justify-between rounded-xl px-4 py-3 text-sm font-bold text-sidebar-foreground/60 transition-all hover:bg-sidebar-accent hover:text-sidebar-accent-foreground group"
                                    :class="{ 'bg-sidebar-accent text-sidebar-accent-foreground shadow-sm': activeItem === item.title }"
                                >
                                    <div class="flex items-center gap-3">
                                        <component :is="item.icon" class="h-5 w-5 opacity-70 group-hover:opacity-100 transition-opacity" />
                                        <span>{{ item.title }}</span>
                                    </div>
                                    <ChevronDown
                                        class="h-3 w-3 transition-transform duration-300 opacity-50"
                                        :class="{ 'rotate-180': activeItem === item.title }"
                                    />
                                </SidebarMenuButton>
                                <!-- Dropdown -->
                                <div v-if="activeItem === item.title" class="mt-1 ml-4 border-l border-sidebar-border pl-4 space-y-1 animate-in slide-in-from-top-2 duration-300">
                                    <Link
                                        v-for="child in item.children"
                                        :key="child.title"
                                        :href="child.href"
                                        class="block rounded-lg px-3 py-2 text-xs font-bold text-sidebar-foreground/50 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground transition-colors"
                                    >
                                        {{ child.title }}
                                    </Link>
                                </div>
                            </div>
                            <Link
                                v-else
                                :href="item.href"
                                class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold text-sidebar-foreground/60 transition-all hover:bg-sidebar-accent hover:text-sidebar-accent-foreground group"
                                :class="{ 'bg-sidebar-accent text-sidebar-accent-foreground shadow-sm': $page.url === item.href }"
                            >
                                <component :is="item.icon" class="h-5 w-5 opacity-70 group-hover:opacity-100 transition-opacity" />
                                <span>{{ item.title }}</span>
                            </Link>
                        </SidebarMenuItem>
                    </SidebarMenu>
                </SidebarGroupContent>
            </SidebarGroup>
        </SidebarContent>

        <SidebarFooter class="border-t border-sidebar-border p-4">
            <div class="space-y-1">
                <div v-for="item in filteredBottomNavigation" :key="item.title">
                     <div v-if="item.children">
                            <SidebarMenuButton
                                @click="toggleItem(item.title)"
                                class="flex w-full items-center justify-between rounded-xl px-4 py-3 text-sm font-bold text-sidebar-foreground/60 transition-all hover:bg-sidebar-accent hover:text-sidebar-accent-foreground group"
                                :class="{ 'bg-sidebar-accent text-sidebar-accent-foreground shadow-sm': activeItem === item.title }"
                            >
                                <div class="flex items-center gap-3">
                                    <component :is="item.icon" class="h-4 w-4 opacity-70" />
                                    <span>{{ item.title }}</span>
                                </div>
                                <ChevronDown
                                    class="h-3 w-3 transition-transform duration-300 opacity-50"
                                    :class="{ 'rotate-180': activeItem === item.title }"
                                />
                            </SidebarMenuButton>
                            <div v-if="activeItem === item.title" class="mt-1 ml-4 border-l border-sidebar-border pl-4 space-y-1 animate-in slide-in-from-top-2 duration-300">
                                <Link
                                    v-for="child in item.children"
                                    :key="child.title"
                                    :href="child.href"
                                    class="block rounded-lg px-3 py-2 text-xs font-bold text-sidebar-foreground/50 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground transition-colors"
                                >
                                    {{ child.title }}
                                </Link>
                            </div>
                        </div>
                        <Link
                            v-else
                            :href="item.href"
                            class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold text-sidebar-foreground/60 transition-all hover:bg-sidebar-accent hover:text-sidebar-accent-foreground group"
                        >
                            <component :is="item.icon" class="h-4 w-4 opacity-70" />
                            <span>{{ item.title }}</span>
                        </Link>
                </div>
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
