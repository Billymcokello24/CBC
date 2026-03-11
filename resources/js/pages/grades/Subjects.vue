<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import {
    BookCopy,
    CheckCircle2,
    ClipboardList,
    Edit,
    Eye,
    GraduationCap,
    List,
    MoreHorizontal,
    ShieldCheck,
    ShieldOff,
    Trash2,
    Users,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface GradeSubjectRow {
    id: number;
    name: string;
    code: string;
    description: string | null;
    subject_type: string;
    learning_area: string | null;
    is_examinable: boolean;
    subject_is_active: boolean;
    is_active: boolean;
    lessons_per_week: number | null;
    minutes_per_lesson: number | null;
    is_compulsory: boolean;
    teachers: string[];
    teachers_count: number;
}

const props = defineProps<{
    grade: {
        id: number;
        name: string;
        code: string;
        category: string;
        level_order: number;
        is_active: boolean;
    };
    subjects: GradeSubjectRow[];
    stats: {
        total: number;
        active: number;
        compulsory: number;
        examinable: number;
    };
}>();

const page = usePage<{ flash?: { success?: string } }>();
const viewMode = ref<'grid' | 'list'>('grid');
const showToast = ref(false);
let toastTimer: ReturnType<typeof setTimeout> | null = null;

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Grades', href: '/grades' },
    { title: props.grade.name, href: `/grades/${props.grade.id}` },
    { title: 'Subjects', href: `/grades/${props.grade.id}/subjects` },
];

const flashSuccess = computed(() => page.props.flash?.success ?? '');
watch(
    flashSuccess,
    (value) => {
        if (!value) return;
        showToast.value = true;
        if (toastTimer) clearTimeout(toastTimer);
        toastTimer = setTimeout(() => {
            showToast.value = false;
        }, 3000);
    },
    { immediate: true },
);

const actionForm = useForm({ ids: [] as number[], action: 'deactivate' });
const confirmOpen = ref(false);
const confirmMode = ref<'activate' | 'deactivate' | 'delete'>('deactivate');
const selectedSubject = ref<GradeSubjectRow | null>(null);

const openConfirmModal = (mode: 'activate' | 'deactivate' | 'delete', subject: GradeSubjectRow) => {
    confirmMode.value = mode;
    selectedSubject.value = subject;
    confirmOpen.value = true;
};

const closeConfirmModal = () => {
    confirmOpen.value = false;
    selectedSubject.value = null;
};

const confirmAction = () => {
    if (!selectedSubject.value) return;

    actionForm.ids = [selectedSubject.value.id];
    actionForm.action = confirmMode.value;
    actionForm.post('/curriculum/subjects/bulk-action', {
        preserveScroll: true,
        onSuccess: closeConfirmModal,
    });
};

const confirmTitle = computed(() => {
    switch (confirmMode.value) {
        case 'activate': return 'Activate subject';
        case 'delete': return 'Delete subject';
        default: return 'Deactivate subject';
    }
});
</script>

<template>
    <Head :title="`${grade.name} Subjects`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="showToast && flashSuccess" class="fixed right-4 top-4 z-50">
            <div class="flex items-center gap-2 rounded-lg border bg-background px-4 py-3 shadow-lg">
                <CheckCircle2 class="h-4 w-4 text-green-600" />
                <span class="text-sm font-medium">{{ flashSuccess }}</span>
            </div>
        </div>

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-500/10">
                        <BookCopy class="h-6 w-6 text-green-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">{{ grade.name }} Subjects</h1>
                        <p class="text-muted-foreground">{{ grade.code }} • {{ grade.category }} • Level {{ grade.level_order }}</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Button variant="outline" as-child><Link :href="`/grades/${grade.id}`">Back to Grade</Link></Button>
                    <Button variant="outline" as-child><Link href="/curriculum/subjects">All Subjects</Link></Button>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Total</div><div class="mt-2 text-2xl font-bold">{{ stats.total }}</div></div>
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Active</div><div class="mt-2 text-2xl font-bold text-emerald-600">{{ stats.active }}</div></div>
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Compulsory</div><div class="mt-2 text-2xl font-bold text-blue-600">{{ stats.compulsory }}</div></div>
                <div class="rounded-xl border bg-card p-4"><div class="text-sm text-muted-foreground">Examinable</div><div class="mt-2 text-2xl font-bold text-violet-600">{{ stats.examinable }}</div></div>
            </div>

            <div class="rounded-xl border bg-card p-6">
                <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">Grade Curriculum Subjects</h2>
                        <p class="text-sm text-muted-foreground">Open a subject, edit it, allocate it to grades, or change its active state.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <Button :variant="viewMode === 'grid' ? 'default' : 'outline'" size="sm" @click="viewMode = 'grid'"><GraduationCap class="mr-2 h-4 w-4" />Grid View</Button>
                        <Button :variant="viewMode === 'list' ? 'default' : 'outline'" size="sm" @click="viewMode = 'list'"><List class="mr-2 h-4 w-4" />List View</Button>
                    </div>
                </div>

                <div v-if="subjects.length === 0" class="rounded-xl border border-dashed p-10 text-center text-sm text-muted-foreground">No subjects allocated to this grade yet.</div>

                <div v-else-if="viewMode === 'grid'" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <div v-for="subject in subjects" :key="subject.id" class="group relative overflow-hidden rounded-xl border bg-card transition hover:border-primary/40 hover:shadow-md">
                        <Link :href="`/curriculum/subjects/${subject.id}`" class="block h-full p-5 pr-16">
                            <h3 class="text-lg font-semibold transition group-hover:text-primary">{{ subject.name }}</h3>
                            <p class="text-sm text-muted-foreground">{{ subject.code }} • {{ subject.learning_area || 'General' }}</p>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <Badge variant="outline">{{ subject.subject_type }}</Badge>
                                <Badge :variant="subject.is_active ? 'default' : 'outline'">{{ subject.is_active ? 'Active' : 'Inactive' }}</Badge>
                                <Badge v-if="subject.is_compulsory" variant="secondary">Compulsory</Badge>
                            </div>
                            <div class="mt-4 space-y-2 text-sm text-muted-foreground">
                                <p><span class="font-medium text-foreground">Teachers:</span> {{ subject.teachers_count ? subject.teachers.join(', ') : 'Not yet assigned' }}</p>
                                <p><span class="font-medium text-foreground">Lessons / week:</span> {{ subject.lessons_per_week ?? '—' }}</p>
                                <p><span class="font-medium text-foreground">Minutes / lesson:</span> {{ subject.minutes_per_lesson ?? '—' }}</p>
                            </div>
                        </Link>
                        <div class="absolute right-3 top-3 z-10">
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="ghost" size="icon" class="h-8 w-8 bg-background/80 backdrop-blur"><MoreHorizontal class="h-4 w-4" /></Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end">
                                    <DropdownMenuItem as-child><Link :href="`/curriculum/subjects/${subject.id}`"><Eye class="mr-2 h-4 w-4" />View Subject</Link></DropdownMenuItem>
                                    <DropdownMenuItem as-child><Link :href="`/curriculum/subjects/${subject.id}/edit`"><Edit class="mr-2 h-4 w-4" />Edit Subject</Link></DropdownMenuItem>
                                    <DropdownMenuItem as-child><Link :href="`/curriculum/subjects/${subject.id}/allocate`"><ClipboardList class="mr-2 h-4 w-4" />Allocate to Grades</Link></DropdownMenuItem>
                                    <DropdownMenuItem as-child><Link :href="`/classes/allocations?grade_id=${grade.id}&subject_id=${subject.id}`"><Users class="mr-2 h-4 w-4" />Assign Teachers</Link></DropdownMenuItem>
                                    <DropdownMenuItem @click="openConfirmModal(subject.subject_is_active ? 'deactivate' : 'activate', subject)"><component :is="subject.subject_is_active ? ShieldOff : ShieldCheck" class="mr-2 h-4 w-4" />{{ subject.subject_is_active ? 'Deactivate' : 'Activate' }}</DropdownMenuItem>
                                    <DropdownMenuItem class="text-destructive" @click="openConfirmModal('delete', subject)"><Trash2 class="mr-2 h-4 w-4" />Delete Subject</DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </div>
                </div>

                <div v-else class="overflow-x-auto rounded-xl border">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b bg-muted/50 text-left text-sm text-muted-foreground">
                                <th class="px-4 py-3 font-medium">Subject</th>
                                <th class="px-4 py-3 font-medium">Teachers</th>
                                <th class="px-4 py-3 font-medium">Lessons</th>
                                <th class="px-4 py-3 font-medium">Status</th>
                                <th class="px-4 py-3 text-right font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="subject in subjects" :key="subject.id" class="border-b transition-colors hover:bg-muted/40">
                                <td class="px-4 py-3">
                                    <Link :href="`/curriculum/subjects/${subject.id}`" class="font-medium hover:text-primary">{{ subject.name }}</Link>
                                    <div class="text-sm text-muted-foreground">{{ subject.code }} • {{ subject.learning_area || 'General' }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm">{{ subject.teachers_count ? subject.teachers.join(', ') : 'Not yet assigned' }}</td>
                                <td class="px-4 py-3 text-sm">{{ subject.lessons_per_week ?? '—' }}/week</td>
                                <td class="px-4 py-3"><Badge :variant="subject.is_active ? 'default' : 'outline'">{{ subject.is_active ? 'Active' : 'Inactive' }}</Badge></td>
                                <td class="px-4 py-3 text-right">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child><Button variant="ghost" size="icon" class="h-8 w-8"><MoreHorizontal class="h-4 w-4" /></Button></DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuItem as-child><Link :href="`/curriculum/subjects/${subject.id}`"><Eye class="mr-2 h-4 w-4" />View Subject</Link></DropdownMenuItem>
                                            <DropdownMenuItem as-child><Link :href="`/curriculum/subjects/${subject.id}/edit`"><Edit class="mr-2 h-4 w-4" />Edit Subject</Link></DropdownMenuItem>
                                            <DropdownMenuItem as-child><Link :href="`/curriculum/subjects/${subject.id}/allocate`"><ClipboardList class="mr-2 h-4 w-4" />Allocate to Grades</Link></DropdownMenuItem>
                                            <DropdownMenuItem as-child><Link :href="`/classes/allocations?grade_id=${grade.id}&subject_id=${subject.id}`"><Users class="mr-2 h-4 w-4" />Assign Teachers</Link></DropdownMenuItem>
                                            <DropdownMenuItem @click="openConfirmModal(subject.subject_is_active ? 'deactivate' : 'activate', subject)"><component :is="subject.subject_is_active ? ShieldOff : ShieldCheck" class="mr-2 h-4 w-4" />{{ subject.subject_is_active ? 'Deactivate' : 'Activate' }}</DropdownMenuItem>
                                            <DropdownMenuItem class="text-destructive" @click="openConfirmModal('delete', subject)"><Trash2 class="mr-2 h-4 w-4" />Delete Subject</DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="confirmOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="closeConfirmModal">
                <div class="w-full max-w-md rounded-xl border bg-background p-6 shadow-xl">
                    <h2 class="text-lg font-semibold">{{ confirmTitle }}</h2>
                    <p class="mt-2 text-sm text-muted-foreground" v-if="selectedSubject">
                        {{ confirmMode === 'delete' ? `Delete ${selectedSubject.name}?` : confirmMode === 'activate' ? `Activate ${selectedSubject.name}?` : `Deactivate ${selectedSubject.name}?` }}
                    </p>
                    <div class="mt-6 flex justify-end gap-2">
                        <Button variant="outline" @click="closeConfirmModal">Cancel</Button>
                        <Button :variant="confirmMode === 'delete' ? 'destructive' : 'default'" :disabled="actionForm.processing" @click="confirmAction">Confirm</Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
