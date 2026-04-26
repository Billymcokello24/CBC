<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    School as SchoolIcon,
    Edit,
    Save,
    Globe,
    Mail,
    Phone,
    MapPin,
    Building2,
    ShieldCheck,
    BadgeCheck,
    Calendar,
    Plus,
    CheckCircle2,
    AlertTriangle,
    Clock,
} from 'lucide-vue-next';
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { Separator } from '@/components/ui/separator';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { BreadcrumbItem } from '@/types';
import { format } from 'date-fns';

const props = defineProps<{
    school: any;
    settings: any[];
    academicYears: any[];
    breadcrumbs: BreadcrumbItem[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Settings', href: '/settings/school' },
    { title: 'School Profile', href: '/settings/school' },
];

const isEditingProfile = ref(false);

const getSettingValue = (key: string, defaultValue: any) => {
    const setting = props.settings.find((s: any) => s.key === key);
    if (!setting) return defaultValue;
    if (setting.type === 'boolean')
        return setting.value === '1' || setting.value === true;
    if (setting.type === 'integer') return parseInt(setting.value);
    return setting.value;
};

const profileForm = useForm({
    name: props.school.name,
    registration_number: props.school.registration_number,
    motto: props.school.motto,
    vision: props.school.vision,
    mission: props.school.mission,
    email: props.school.email,
    phone: props.school.phone,
    alternate_phone: props.school.alternate_phone,
    website: props.school.website,
    address: props.school.address,
    county: props.school.county,
    sub_county: props.school.sub_county,
    ward: props.school.ward,
    pdf_theme_color: getSettingValue('pdf_theme_color', '#1e40af'),
});

const submitProfile = () => {
    profileForm.put('/settings/school', {
        onSuccess: () => (isEditingProfile.value = false),
        preserveScroll: true,
    });
};

// Logo Upload
const logoInput = ref<HTMLInputElement | null>(null);
const logoForm = useForm({
    logo: null as File | null,
});

const triggerLogoUpload = () => {
    if (isEditingProfile.value) {
        logoInput.value?.click();
    }
};

const handleLogoChange = (e: Event) => {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (file) {
        logoForm.logo = file;
        logoForm.post('/settings/school/logo', {
            preserveScroll: true,
        });
    }
};

const getLogoUrl = (path: string) => {
    if (!path) return undefined;
    if (path.startsWith('http')) return path;
    return `/storage/${path}`;
};

// Security Settings
// moved getSettingValue up
const securityForm = useForm({
    password_expiry_days: getSettingValue('password_expiry_days', 90),
    require_2fa: getSettingValue('require_2fa', false),
    session_timeout_minutes: getSettingValue('session_timeout_minutes', 60),
    max_login_attempts: getSettingValue('max_login_attempts', 5),
    lockout_duration_minutes: getSettingValue('lockout_duration_minutes', 30),
});

const submitSecurity = () => {
    securityForm.post('/settings/school/security', {
        preserveScroll: true,
    });
};

// Academic Year Management
const showAddYearDialog = ref(false);
const editingYear = ref<any>(null);

const yearForm = useForm({
    name: '',
    code: '',
    start_date: '',
    end_date: format(new Date(), 'yyyy-MM-dd'),
    is_current: false,
    status: 'active' as 'active' | 'inactive' | 'closed',
});

const openAddYear = () => {
    editingYear.value = null;
    yearForm.reset();
    yearForm.start_date = format(new Date(), 'yyyy-MM-dd');
    yearForm.end_date = format(new Date(), 'yyyy-MM-dd');
    showAddYearDialog.value = true;
};

const openEditYear = (year: any) => {
    editingYear.value = year;
    yearForm.name = year.name;
    yearForm.code = year.code;
    yearForm.start_date = year.start_date;
    yearForm.end_date = year.end_date;
    yearForm.is_current = !!year.is_current;
    yearForm.status = year.status;
    showAddYearDialog.value = true;
};

// Jan-Jan Enforcement Logic
watch(
    () => yearForm.name,
    (val) => {
        if (/^\d{4}$/.test(val)) {
            yearForm.code = `AY${val}`;
            yearForm.start_date = `${val}-01-01`;
            yearForm.end_date = `${val}-12-31`;
        }
    },
);

const submitYear = () => {
    if (editingYear.value) {
        yearForm.put(`/settings/academic-years/${editingYear.value.id}`, {
            onSuccess: () => {
                showAddYearDialog.value = false;
                editingYear.value = null;
                yearForm.reset();
            },
            preserveScroll: true,
        });
    } else {
        yearForm.post('/settings/academic-years', {
            onSuccess: () => {
                showAddYearDialog.value = false;
                yearForm.reset();
            },
            preserveScroll: true,
        });
    }
};

const setAsCurrent = (id: number) => {
    useForm({}).patch(`/settings/academic-years/${id}/current`, {
        preserveScroll: true,
    });
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'active':
            return 'bg-emerald-50 text-emerald-700 border-emerald-100';
        case 'inactive':
            return 'bg-slate-50 text-slate-700 border-slate-100';
        case 'closed':
            return 'bg-rose-50 text-rose-700 border-rose-100';
        default:
            return 'bg-slate-50 text-slate-700 border-slate-100';
    }
};
</script>

<template>
    <Head title="School Settings" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl border border-violet-100 bg-violet-600/10 shadow-sm"
                    >
                        <SchoolIcon class="h-6 w-6 text-violet-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">
                            System Settings
                        </h1>
                        <p class="text-sm text-muted-foreground">
                            Manage school identity, security, and academic
                            cycles.
                        </p>
                    </div>
                </div>
            </div>

            <Tabs defaultValue="profile" class="space-y-6">
                <TabsList class="h-11 self-start border bg-slate-100/50 p-1">
                    <TabsTrigger
                        value="profile"
                        class="px-6 data-[state=active]:bg-white data-[state=active]:shadow-sm"
                        >School Profile</TabsTrigger
                    >
                    <TabsTrigger
                        value="academic"
                        class="px-6 data-[state=active]:bg-white data-[state=active]:shadow-sm"
                        >Academic Management</TabsTrigger
                    >
                    <TabsTrigger
                        value="security"
                        class="px-6 text-sm data-[state=active]:bg-white data-[state=active]:shadow-sm"
                        >Security & Access</TabsTrigger
                    >
                </TabsList>

                <TabsContent
                    value="profile"
                    class="animate-in space-y-6 duration-300 fade-in slide-in-from-left-4"
                >
                    <div class="flex justify-end gap-2">
                        <Button
                            v-if="!isEditingProfile"
                            @click="isEditingProfile = true"
                            variant="outline"
                            class="shadow-sm"
                        >
                            <Edit class="mr-2 h-4 w-4" /> Edit School Data
                        </Button>
                        <template v-else>
                            <Button
                                @click="isEditingProfile = false"
                                variant="ghost"
                                >Cancel</Button
                            >
                            <Button
                                @click="submitProfile"
                                :disabled="profileForm.processing"
                                class="bg-violet-600 text-white shadow-lg shadow-violet-600/20 hover:bg-violet-700"
                            >
                                <Save class="mr-2 h-4 w-4" /> Save Profile
                                Changes
                            </Button>
                        </template>
                    </div>

                    <div class="grid gap-6 lg:grid-cols-3">
                        <!-- Left Sidebar: Brand -->
                        <div class="space-y-6 lg:col-span-1">
                            <Card
                                class="overflow-hidden border-slate-200 shadow-sm"
                            >
                                <CardHeader
                                    class="relative overflow-hidden border-b bg-slate-50 p-8 text-center"
                                >
                                    <div
                                        class="absolute top-0 left-0 h-1 w-full bg-gradient-to-r from-violet-500 to-indigo-500"
                                    ></div>
                                    <input
                                        type="file"
                                        ref="logoInput"
                                        class="hidden"
                                        accept="image/*"
                                        @change="handleLogoChange"
                                    />
                                    <div
                                        class="group relative mx-auto mb-4 flex h-28 w-28 items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed border-slate-200 bg-white shadow-inner"
                                        :class="{
                                            'cursor-pointer hover:border-violet-400':
                                                isEditingProfile,
                                        }"
                                        @click="triggerLogoUpload"
                                    >
                                        <img
                                            v-if="school.logo"
                                            :src="getLogoUrl(school.logo)"
                                            class="h-full w-full object-contain p-2"
                                        />
                                        <SchoolIcon
                                            v-else
                                            class="h-12 w-12 text-slate-300"
                                        />
                                        <div
                                            v-if="isEditingProfile"
                                            class="absolute inset-0 flex flex-col items-center justify-center bg-black/40 opacity-0 transition-opacity group-hover:opacity-100"
                                        >
                                            <Edit
                                                class="mb-1 h-6 w-6 text-white"
                                            />
                                            <span
                                                class="text-xs font-bold text-white uppercase"
                                                >Change Logo</span
                                            >
                                        </div>
                                    </div>
                                    <CardTitle
                                        class="text-xl font-bold text-slate-900"
                                        >{{ school.name }}</CardTitle
                                    >
                                    <Badge
                                        variant="outline"
                                        class="mt-2 text-xs font-bold tracking-tight uppercase"
                                        >{{ school.code }}</Badge
                                    >
                                </CardHeader>
                                <CardContent class="space-y-4 p-6">
                                    <div
                                        class="flex items-center justify-between rounded-xl border border-slate-100/50 bg-slate-50 p-3"
                                    >
                                        <span
                                            class="flex items-center gap-2 text-xs font-bold tracking-wide text-slate-500 uppercase"
                                        >
                                            <ShieldCheck class="h-3.5 w-3.5" />
                                            Status
                                        </span>
                                        <Badge
                                            variant="outline"
                                            class="border-emerald-100 bg-emerald-50 text-xs font-bold tracking-tight text-emerald-700 uppercase"
                                            >{{ school.status }}</Badge
                                        >
                                    </div>
                                    <div
                                        class="flex items-center justify-between rounded-xl border border-slate-100/50 bg-slate-50 p-3"
                                    >
                                        <span
                                            class="flex items-center gap-2 text-xs font-bold tracking-wide text-slate-500 uppercase"
                                        >
                                            <Clock class="h-3.5 w-3.5" />
                                            Established
                                        </span>
                                        <span
                                            class="text-xs font-bold text-slate-700"
                                            >{{
                                                school.established_date
                                                    ? format(
                                                          new Date(
                                                              school.established_date,
                                                          ),
                                                          'yyyy',
                                                      )
                                                    : 'N/A'
                                            }}</span
                                        >
                                    </div>
                                </CardContent>
                            </Card>

                            <Card
                                class="overflow-hidden border-slate-200 shadow-sm"
                            >
                                <CardHeader
                                    class="border-b border-slate-50 pb-3"
                                >
                                    <CardTitle
                                        class="flex items-center gap-2 text-xs font-medium tracking-tight text-slate-400 uppercase"
                                    >
                                        <Globe
                                            class="h-3.5 w-3.5 text-violet-500"
                                        />
                                        Official Channels
                                    </CardTitle>
                                </CardHeader>
                                <CardContent class="space-y-4 p-6">
                                    <div class="space-y-1">
                                        <Label
                                            class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                                            >Website</Label
                                        >
                                        <p
                                            class="cursor-pointer truncate text-sm font-bold text-slate-700 underline decoration-violet-200 decoration-2 underline-offset-4 transition-colors hover:text-violet-600"
                                        >
                                            {{
                                                school.website ||
                                                'Not configured'
                                            }}
                                        </p>
                                    </div>
                                    <div class="space-y-1">
                                        <Label
                                            class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                                            >Main Email</Label
                                        >
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="flex h-6 w-6 items-center justify-center rounded-md border border-violet-100 bg-violet-50"
                                            >
                                                <Mail
                                                    class="h-3 w-3 text-violet-600"
                                                />
                                            </div>
                                            <p
                                                class="truncate text-sm font-bold text-slate-700"
                                            >
                                                {{ school.email }}
                                            </p>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        <!-- Right Area: Editable Details -->
                        <div class="space-y-6 lg:col-span-2">
                            <Card
                                class="overflow-hidden border-slate-200 shadow-sm"
                            >
                                <CardHeader
                                    class="border-b border-slate-100 bg-slate-50/50"
                                >
                                    <CardTitle
                                        class="text-base font-bold text-slate-800"
                                        >Institutional Identity</CardTitle
                                    >
                                    <CardDescription class="text-xs"
                                        >Update your core school metadata and
                                        vision statements.</CardDescription
                                    >
                                </CardHeader>
                                <CardContent
                                    class="grid gap-6 p-6 md:grid-cols-2"
                                >
                                    <div class="grid gap-2">
                                        <Label
                                            class="text-xs font-bold tracking-wider text-slate-500 uppercase"
                                            >School Name</Label
                                        >
                                        <Input
                                            v-model="profileForm.name"
                                            :disabled="!isEditingProfile"
                                            class="h-11 font-semibold"
                                        />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label
                                            class="text-xs font-bold tracking-wider text-slate-500 uppercase"
                                            >MoE Registration Number</Label
                                        >
                                        <Input
                                            v-model="
                                                profileForm.registration_number
                                            "
                                            :disabled="!isEditingProfile"
                                            class="h-11 font-mono text-sm"
                                            placeholder="MOE/PVT/..."
                                        />
                                    </div>
                                    <div class="grid gap-2 md:col-span-2">
                                        <Label
                                            class="text-xs font-bold tracking-wider text-slate-500 uppercase"
                                            >Motto</Label
                                        >
                                        <Input
                                            v-model="profileForm.motto"
                                            :disabled="!isEditingProfile"
                                            class="h-11 text-slate-600"
                                            placeholder="The guiding principle..."
                                        />
                                    </div>
                                    <div class="grid gap-2 md:col-span-2">
                                        <Label
                                            class="text-xs font-bold tracking-wider text-slate-500 uppercase"
                                            >Vision Statement</Label
                                        >
                                        <Textarea
                                            v-model="profileForm.vision"
                                            :disabled="!isEditingProfile"
                                            class="min-h-[100px] leading-relaxed"
                                        />
                                    </div>
                                    <div class="grid gap-2 md:col-span-2">
                                        <Label
                                            class="text-xs font-bold tracking-wider text-slate-500 uppercase"
                                            >Mission Statement</Label
                                        >
                                        <Textarea
                                            v-model="profileForm.mission"
                                            :disabled="!isEditingProfile"
                                            class="min-h-[100px] leading-relaxed"
                                        />
                                    </div>
                                    <div
                                        class="grid gap-2 border-t border-slate-100 pt-2 md:col-span-2"
                                    >
                                        <Label
                                            class="text-xs font-bold tracking-wider text-slate-500 uppercase"
                                            >System Brand Color</Label
                                        >
                                        <div class="flex items-center gap-3">
                                            <input
                                                type="color"
                                                v-model="
                                                    profileForm.pdf_theme_color
                                                "
                                                :disabled="!isEditingProfile"
                                                class="h-10 w-12 cursor-pointer rounded border-0 bg-transparent p-0"
                                            />
                                            <p
                                                class="max-w-[280px] text-xs font-medium text-slate-400"
                                            >
                                                Select the primary brand color.
                                                This styles the entire system
                                                interface (buttons, sidebar
                                                menus) globally, as well as
                                                generated PDFs.
                                            </p>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>

                            <Card
                                class="overflow-hidden border-slate-200 shadow-sm"
                            >
                                <CardHeader
                                    class="border-b border-slate-100 bg-slate-50/50"
                                >
                                    <CardTitle
                                        class="text-base font-bold text-slate-800"
                                        >Communication & Location</CardTitle
                                    >
                                </CardHeader>
                                <CardContent
                                    class="grid gap-6 p-6 md:grid-cols-2"
                                >
                                    <div class="grid gap-2">
                                        <Label
                                            class="text-xs font-bold tracking-wider text-slate-500 uppercase"
                                            >Public Contact Email</Label
                                        >
                                        <Input
                                            v-model="profileForm.email"
                                            :disabled="!isEditingProfile"
                                            class="h-11"
                                        />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label
                                            class="text-xs font-bold tracking-wider text-slate-500 uppercase"
                                            >Main Phone Number</Label
                                        >
                                        <Input
                                            v-model="profileForm.phone"
                                            :disabled="!isEditingProfile"
                                            class="h-11"
                                        />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label
                                            class="text-xs font-bold tracking-wider text-slate-500 uppercase"
                                            >County</Label
                                        >
                                        <Input
                                            v-model="profileForm.county"
                                            :disabled="!isEditingProfile"
                                            class="h-11"
                                        />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label
                                            class="text-xs font-bold tracking-wider text-slate-500 uppercase"
                                            >Sub-County / District</Label
                                        >
                                        <Input
                                            v-model="profileForm.sub_county"
                                            :disabled="!isEditingProfile"
                                            class="h-11"
                                        />
                                    </div>
                                    <div class="grid gap-2 md:col-span-2">
                                        <Label
                                            class="text-xs font-bold tracking-wider text-slate-500 uppercase"
                                            >Physical Address</Label
                                        >
                                        <Input
                                            v-model="profileForm.address"
                                            :disabled="!isEditingProfile"
                                            class="h-11"
                                            placeholder="Plot No, Street, Town..."
                                        />
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </TabsContent>

                <TabsContent
                    value="academic"
                    class="mt-6 animate-in space-y-6 duration-300 fade-in slide-in-from-right-4"
                >
                    <div
                        class="flex items-center justify-between rounded-3xl border border-slate-100 bg-white p-6 shadow-sm"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-2xl border border-indigo-100 bg-indigo-50 shadow-inner"
                            >
                                <Calendar class="h-6 w-6 text-indigo-600" />
                            </div>
                            <div>
                                <h3
                                    class="text-lg leading-tight font-bold text-slate-900"
                                >
                                    Academic Year Lifecycle
                                </h3>
                                <p class="mt-0.5 text-xs text-slate-500">
                                    Automated Jan-Jan cycles for standard Kenyan
                                    school system.
                                </p>
                            </div>
                        </div>
                        <Button
                            @click="openAddYear"
                            class="h-11 bg-indigo-600 px-6 text-sm font-bold tracking-tight uppercase shadow-lg shadow-indigo-600/20 hover:bg-indigo-700"
                        >
                            <Plus class="mr-2 h-4 w-4" /> New Cycle
                        </Button>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <Card
                            v-for="year in academicYears"
                            :key="year.id"
                            class="group relative overflow-hidden border-slate-200 transition-all duration-300 hover:shadow-xl"
                            :class="{
                                'border-transparent bg-emerald-50/20 ring-2 ring-emerald-500 ring-offset-2':
                                    year.is_current,
                            }"
                        >
                            <div
                                v-if="year.is_current"
                                class="absolute top-0 right-0 p-3"
                            >
                                <Badge
                                    class="border-none bg-emerald-500 px-2.5 py-1 text-xs font-bold tracking-tight uppercase shadow-sm hover:bg-emerald-600"
                                >
                                    <CheckCircle2 class="mr-1.5 h-3 w-3" />
                                    Current
                                </Badge>
                            </div>
                            <CardHeader class="px-6 pt-6 pb-3">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <CardTitle
                                            class="text-xl font-bold text-slate-900"
                                            >{{ year.name }}</CardTitle
                                        >
                                        <CardDescription
                                            class="mt-0.5 font-mono text-xs tracking-tight text-slate-400 uppercase"
                                            >{{ year.code }}</CardDescription
                                        >
                                    </div>
                                    <div
                                        class="flex items-center gap-1 opacity-100 transition-opacity group-hover:opacity-100 md:opacity-0"
                                    >
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="h-8 w-8 rounded-lg text-slate-400 hover:bg-indigo-50 hover:text-indigo-600"
                                            @click="openEditYear(year)"
                                        >
                                            <Edit class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>
                            </CardHeader>
                            <CardContent class="space-y-4 px-6 pt-2 pb-6">
                                <div
                                    class="space-y-3 border-b border-slate-100/50 pb-4"
                                >
                                    <div
                                        class="flex items-center gap-2.5 text-xs font-bold text-slate-600"
                                    >
                                        <div
                                            class="flex h-6 w-6 items-center justify-center rounded-md bg-slate-100"
                                        >
                                            <Clock
                                                class="h-3.5 w-3.5 text-slate-500"
                                            />
                                        </div>
                                        <span
                                            >{{
                                                format(
                                                    new Date(year.start_date),
                                                    'MMM dd, yyyy',
                                                )
                                            }}
                                            -
                                            {{
                                                format(
                                                    new Date(year.end_date),
                                                    'MMM dd, yyyy',
                                                )
                                            }}</span
                                        >
                                    </div>
                                    <Badge
                                        variant="outline"
                                        :class="[
                                            getStatusColor(year.status),
                                            'border px-3 py-0.5 text-xs font-bold tracking-[0.15em] uppercase shadow-sm',
                                        ]"
                                    >
                                        {{ year.status }}
                                    </Badge>
                                </div>
                                <div v-if="!year.is_current" class="pt-2">
                                    <Button
                                        @click="setAsCurrent(year.id)"
                                        variant="outline"
                                        class="h-9 w-full border-indigo-100 text-xs font-bold tracking-tight text-indigo-600 uppercase hover:bg-indigo-50"
                                    >
                                        Switch to this Year
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <div
                        v-if="academicYears.length === 0"
                        class="flex flex-col items-center justify-center rounded-2xl border-4 border-dashed border-slate-100 bg-slate-50/30 p-20 text-center"
                    >
                        <div
                            class="mb-6 flex h-24 w-24 items-center justify-center rounded-xl border border-slate-50 bg-white shadow-xl"
                        >
                            <Calendar class="h-12 w-12 text-slate-200" />
                        </div>
                        <h4
                            class="text-xl font-bold tracking-tight text-slate-900"
                        >
                            Zero Academic Cycles
                        </h4>
                        <p
                            class="mt-2 max-w-[320px] text-sm leading-relaxed font-medium text-slate-500"
                        >
                            Define your school's timeline. Entering "2025" will
                            automatically fill the full Jan to Dec cycle for
                            you.
                        </p>
                        <Button
                            @click="openAddYear"
                            class="mt-8 h-12 rounded-2xl bg-indigo-600 px-10 text-xs font-bold tracking-tight uppercase shadow-xl shadow-indigo-600/30 hover:bg-indigo-700"
                        >
                            Initialize Timeline
                        </Button>
                    </div>
                </TabsContent>

                <TabsContent
                    value="security"
                    class="mt-6 animate-in space-y-6 duration-300 fade-in slide-in-from-bottom-4"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-100 pb-6"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-2xl border border-rose-100 bg-rose-50 shadow-inner"
                            >
                                <Shield class="h-6 w-6 text-rose-600" />
                            </div>
                            <div>
                                <h3
                                    class="text-lg font-bold tracking-tight text-slate-900 uppercase"
                                >
                                    Security & Access Vault
                                </h3>
                                <p class="text-xs font-medium text-slate-500">
                                    Guard your institutional data with robust
                                    authentication rules.
                                </p>
                            </div>
                        </div>
                        <Button
                            @click="submitSecurity"
                            :disabled="securityForm.processing"
                            class="h-12 rounded-2xl bg-slate-900 px-8 text-xs font-bold tracking-tight uppercase shadow-lg shadow-black/20 hover:bg-black"
                        >
                            <Save class="mr-2 h-4 w-4" /> Hardened Save
                        </Button>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2">
                        <!-- Password Policies -->
                        <Card
                            class="overflow-hidden rounded-3xl border-slate-200 shadow-sm"
                        >
                            <CardHeader
                                class="border-b border-slate-100 bg-slate-50/50 py-5"
                            >
                                <div class="flex items-center gap-3 px-1">
                                    <div
                                        class="rounded-xl border border-slate-100 bg-white p-2.5 shadow-sm"
                                    >
                                        <Key class="h-4 w-4 text-violet-600" />
                                    </div>
                                    <CardTitle
                                        class="text-sm font-medium tracking-tight text-muted-foreground text-slate-600 uppercase"
                                        >Identity Guard</CardTitle
                                    >
                                </div>
                            </CardHeader>
                            <CardContent class="space-y-8 p-8">
                                <div class="space-y-6">
                                    <div
                                        class="flex items-center justify-between gap-6"
                                    >
                                        <div class="space-y-1">
                                            <Label
                                                class="text-xs font-bold tracking-tight text-slate-800 uppercase"
                                                >Credential Expiry</Label
                                            >
                                            <p
                                                class="text-xs leading-relaxed font-medium text-slate-400"
                                            >
                                                Force re-authentication period
                                                (Days). Set 0 to disable.
                                            </p>
                                        </div>
                                        <div class="relative">
                                            <Input
                                                type="number"
                                                v-model="
                                                    securityForm.password_expiry_days
                                                "
                                                class="h-11 w-24 rounded-xl border-slate-200 text-center font-bold focus:ring-violet-500"
                                            />
                                            <span
                                                class="absolute -top-1 -right-1 flex h-3 w-3"
                                            >
                                                <span
                                                    class="absolute inline-flex h-full w-full animate-ping rounded-full bg-violet-400 opacity-75"
                                                ></span>
                                                <span
                                                    class="relative inline-flex h-3 w-3 rounded-full bg-violet-500"
                                                ></span>
                                            </span>
                                        </div>
                                    </div>
                                    <Separator class="bg-slate-50" />
                                    <div
                                        class="flex items-center justify-between gap-6"
                                    >
                                        <div class="space-y-1">
                                            <Label
                                                class="text-xs font-bold tracking-tight text-slate-800 uppercase"
                                                >Mandatory MFA</Label
                                            >
                                            <p
                                                class="text-xs leading-relaxed font-medium text-slate-400"
                                            >
                                                Enforce Two-Factor
                                                Authentication for all active
                                                staff profiles.
                                            </p>
                                        </div>
                                        <Switch
                                            :checked="securityForm.require_2fa"
                                            @update:checked="
                                                (val: boolean) =>
                                                    (securityForm.require_2fa =
                                                        val)
                                            "
                                        />
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Access Control -->
                        <Card
                            class="overflow-hidden rounded-3xl border-slate-200 shadow-sm"
                        >
                            <CardHeader
                                class="border-b border-slate-100 bg-slate-50/50 py-5"
                            >
                                <div class="flex items-center gap-3 px-1">
                                    <div
                                        class="rounded-xl border border-slate-100 bg-white p-2.5 shadow-sm"
                                    >
                                        <ShieldCheck
                                            class="h-4 w-4 text-emerald-600"
                                        />
                                    </div>
                                    <CardTitle
                                        class="text-sm font-medium tracking-tight text-muted-foreground text-slate-600 uppercase"
                                        >Access Perimeter</CardTitle
                                    >
                                </div>
                            </CardHeader>
                            <CardContent class="space-y-8 p-8">
                                <div class="space-y-6">
                                    <div
                                        class="flex items-center justify-between gap-6"
                                    >
                                        <div class="space-y-1">
                                            <Label
                                                class="text-xs font-bold tracking-tight text-slate-800 uppercase"
                                                >Session Lifespan</Label
                                            >
                                            <p
                                                class="text-xs leading-relaxed font-medium text-slate-400"
                                            >
                                                Idle timeout in minutes before
                                                mandatory session termination.
                                            </p>
                                        </div>
                                        <Input
                                            type="number"
                                            v-model="
                                                securityForm.session_timeout_minutes
                                            "
                                            class="h-11 w-24 rounded-xl border-slate-200 text-center font-bold focus:ring-emerald-500"
                                        />
                                    </div>
                                    <Separator class="bg-slate-50" />
                                    <div
                                        class="flex items-center justify-between gap-6"
                                    >
                                        <div class="space-y-1">
                                            <Label
                                                class="text-xs font-bold tracking-tight text-slate-800 uppercase"
                                                >Intrusion Threshold</Label
                                            >
                                            <p
                                                class="text-xs leading-relaxed font-medium text-slate-400"
                                            >
                                                Max failed attempts before the
                                                account enters lockdown state.
                                            </p>
                                        </div>
                                        <Input
                                            type="number"
                                            v-model="
                                                securityForm.max_login_attempts
                                            "
                                            class="h-11 w-24 rounded-xl border-slate-200 text-center font-bold focus:ring-emerald-500"
                                        />
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <div
                        class="relative mt-8 flex flex-col items-center gap-6 overflow-hidden rounded-xl border border-indigo-700 bg-indigo-900 p-8 shadow-lg shadow-indigo-900/40 md:flex-row"
                    >
                        <div
                            class="absolute -right-12 -bottom-12 h-40 w-40 rounded-full bg-white/5 blur-3xl"
                        ></div>
                        <div
                            class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl border border-indigo-600 bg-indigo-800 shadow-inner"
                        >
                            <AlertTriangle class="h-7 w-7 text-indigo-300" />
                        </div>
                        <div class="flex-1">
                            <h4
                                class="text-sm font-bold tracking-tight text-white uppercase"
                            >
                                Hardened Security Protocol
                            </h4>
                            <p
                                class="mt-2 text-xs leading-relaxed font-medium text-indigo-100/70"
                            >
                                Modifications to these vault settings will
                                propagate across all user sessions in real-time.
                                Enforcing MFA or changing session spans may
                                result in immediate logout for active staff
                                members. Use caution when adjusting these
                                thresholds during peak academic hours.
                            </p>
                        </div>
                    </div>
                </TabsContent>
            </Tabs>
        </div>

        <!-- Add/Edit Academic Year Dialog -->
        <Dialog
            :open="showAddYearDialog"
            @update:open="showAddYearDialog = $event"
        >
            <DialogContent
                class="overflow-hidden rounded-xl border-slate-100 p-0 shadow-lg sm:max-w-[480px]"
            >
                <div class="h-2 w-full bg-indigo-600"></div>
                <div class="p-8">
                    <DialogHeader>
                        <DialogTitle
                            class="text-2xl font-bold text-slate-900"
                            >{{
                                editingYear
                                    ? 'Refine Cycle'
                                    : 'Launch New Cycle'
                            }}</DialogTitle
                        >
                        <DialogDescription
                            class="mt-2 text-xs font-medium text-slate-500"
                        >
                            {{
                                editingYear
                                    ? 'Update the parameters for this academic period.'
                                    : 'Initialize a standard academic year. Type the year below to auto-calculate the Kenyan cycle.'
                            }}
                        </DialogDescription>
                    </DialogHeader>

                    <div class="grid gap-6 py-8">
                        <div
                            class="grid gap-3 rounded-2xl border border-slate-100 bg-slate-50/50 p-6 shadow-inner"
                        >
                            <Label
                                for="name"
                                class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                                >Year Identification</Label
                            >
                            <Input
                                id="name"
                                v-model="yearForm.name"
                                placeholder="2025"
                                class="h-12 rounded-xl border-slate-200 text-lg font-bold tracking-tight focus:ring-indigo-500"
                            />
                            <p
                                v-if="yearForm.errors.name"
                                class="text-xs font-bold tracking-wider text-rose-500 uppercase"
                            >
                                {{ yearForm.errors.name }}
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-4 px-1">
                            <div class="grid gap-2">
                                <Label
                                    for="code"
                                    class="text-xs font-bold text-slate-400 uppercase"
                                    >System Code</Label
                                >
                                <Input
                                    id="code"
                                    v-model="yearForm.code"
                                    placeholder="AY2025"
                                    class="h-10 rounded-lg font-mono text-xs font-bold"
                                />
                            </div>
                            <div class="grid gap-2">
                                <Label
                                    for="status"
                                    class="text-xs font-bold text-slate-400 uppercase"
                                    >Lifecycle Status</Label
                                >
                                <Select v-model="yearForm.status">
                                    <SelectTrigger
                                        class="h-10 rounded-lg border-slate-200 px-4 text-xs font-bold"
                                    >
                                        <SelectValue
                                            placeholder="Select status"
                                        />
                                    </SelectTrigger>
                                    <SelectContent
                                        class="rounded-xl border-slate-100"
                                    >
                                        <SelectItem
                                            value="active"
                                            class="text-xs font-bold"
                                            >Active Cycle</SelectItem
                                        >
                                        <SelectItem
                                            value="inactive"
                                            class="text-xs font-bold"
                                            >Draft / Idle</SelectItem
                                        >
                                        <SelectItem
                                            value="closed"
                                            class="text-xs font-bold text-rose-600"
                                            >Archived Period</SelectItem
                                        >
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 px-1">
                            <div class="grid gap-2">
                                <Label
                                    for="start"
                                    class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                                    >Start Boundary</Label
                                >
                                <div class="relative">
                                    <Input
                                        id="start"
                                        type="date"
                                        v-model="yearForm.start_date"
                                        class="h-10 rounded-lg pl-8 text-xs font-bold"
                                    />
                                    <Calendar
                                        class="absolute top-3 left-2.5 h-4 w-4 text-slate-300"
                                    />
                                </div>
                            </div>
                            <div class="grid gap-2">
                                <Label
                                    for="end"
                                    class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                                    >End Boundary</Label
                                >
                                <div class="relative">
                                    <Input
                                        id="end"
                                        type="date"
                                        v-model="yearForm.end_date"
                                        class="h-10 rounded-lg pl-8 text-xs font-bold"
                                    />
                                    <Calendar
                                        class="absolute top-3 left-2.5 h-4 w-4 text-slate-300"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <DialogFooter class="gap-3 pt-4 sm:justify-between">
                        <Button
                            variant="ghost"
                            @click="showAddYearDialog = false"
                            class="text-sm font-medium tracking-tight text-slate-400 uppercase hover:text-slate-600"
                        >
                            Abandon
                        </Button>
                        <Button
                            @click="submitYear"
                            :disabled="yearForm.processing"
                            class="h-12 rounded-2xl bg-indigo-600 px-8 text-xs font-medium tracking-tight uppercase shadow-xl shadow-indigo-600/30 transition-all hover:bg-indigo-700"
                        >
                            {{
                                editingYear
                                    ? 'Confirm Refinement'
                                    : 'Deploy Cycle'
                            }}
                        </Button>
                    </DialogFooter>
                </div>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<style scoped>
.font-bold {
    font-weight: 900;
}
</style>
