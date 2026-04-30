<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import {
    User,
    Mail,
    Phone,
    Shield,
    ArrowLeft,
    Save,
    Loader2,
    GraduationCap,
    CheckCircle2,
    AlertTriangle,
    X,
    Search,
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Dialog, DialogContent } from '@/components/ui/dialog';

const props = defineProps<{
    parent: any;
    students: any[];
    selectedStudentIds: number[];
}>();

const studentSearch = ref('');
const confirmOpen = ref(false);
const successOpen = ref(false);

const form = useForm({
    first_name: props.parent.first_name,
    middle_name: props.parent.middle_name || '',
    last_name: props.parent.last_name,
    email: props.parent.email,
    phone: props.parent.phone,
    id_number: props.parent.id_number,
    gender: props.parent.gender,
    relationship_type: props.parent.relationship_type || 'Father',
    student_ids: [...props.selectedStudentIds],
});

const filteredStudents = computed(() => {
    if (!studentSearch.value) return props.students.slice(0, 6);
    const search = studentSearch.value.toLowerCase();
    return props.students
        .filter(
            (s) =>
                s.first_name.toLowerCase().includes(search) ||
                s.last_name.toLowerCase().includes(search) ||
                s.admission_number.toLowerCase().includes(search),
        )
        .slice(0, 8);
});

const toggleStudent = (id: number) => {
    const index = form.student_ids.indexOf(id);
    if (index > -1) {
        form.student_ids.splice(index, 1);
    } else {
        form.student_ids.push(id);
    }
};

const getStudentName = (id: number) => {
    const student = props.students.find((s) => s.id === id);
    return student ? `${student.first_name} ${student.last_name}` : 'Unknown';
};

const submit = () => {
    confirmOpen.value = true;
};

const confirmSubmit = () => {
    confirmOpen.value = false;
    form.put(`/parents/${props.parent.id}`, {
        onSuccess: () => {
            successOpen.value = true;
        },
    });
};

const breadcrumbs = [
    { title: 'User Directory', href: '/staffs/directory' },
    { title: 'Parents', href: '/parents' },
    { title: 'Edit Parent Account', href: '#' },
];
</script>

<template>
    <AppLayout title="Edit Parent Account" :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-col bg-slate-50/50 p-6 lg:p-10">
            <!-- Header Section -->
            <div class="mb-12 flex flex-col gap-8">
                <div class="flex flex-wrap items-center justify-between gap-6">
                    <div class="space-y-2">
                        <Link
                            href="/parents"
                            class="group inline-flex items-center gap-1.5 pl-1 text-xs font-bold tracking-tight text-muted-foreground uppercase transition-colors hover:text-blue-600"
                        >
                            <ArrowLeft
                                class="h-3.5 w-3.5 transition-transform group-hover:-translate-x-1"
                            />
                            Back to Registry
                        </Link>
                        <h1
                            class="flex items-center gap-4 text-4xl font-bold tracking-tight text-slate-900 uppercase"
                        >
                            <span
                                class="h-12 w-2 rounded-full bg-blue-600"
                            ></span>
                            Edit Parent Account
                        </h1>
                        <p
                            class="max-w-2xl pl-1 text-sm font-medium text-muted-foreground"
                        >
                            Update account credentials and linked dependents for
                            <span
                                class="font-bold text-blue-600 underline decoration-blue-200 underline-offset-4"
                                >{{ parent.first_name }}
                                {{ parent.last_name }}</span
                            >.
                        </p>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="grid gap-10">
                <div class="grid gap-10 lg:grid-cols-12">
                    <!-- Left: Profile Info -->
                    <div class="space-y-10 lg:col-span-7">
                        <div
                            class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl shadow-slate-200/40 transition-all duration-500 hover:shadow-lg hover:shadow-indigo-600/10"
                        >
                            <div
                                class="border-b border-slate-100 bg-slate-50/30 px-10 py-7"
                            >
                                <h2
                                    class="flex items-center gap-3 text-lg font-bold text-slate-900"
                                >
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-200"
                                    >
                                        <User class="h-5 w-5" />
                                    </div>
                                    Guardian Profile
                                </h2>
                            </div>
                            <div class="space-y-8 p-10">
                                <div class="grid gap-8 md:grid-cols-3">
                                    <div class="space-y-3">
                                        <Label
                                            for="first_name"
                                            class="ml-1 text-xs font-bold tracking-tight text-slate-500 uppercase"
                                            >First Name</Label
                                        >
                                        <Input
                                            id="first_name"
                                            v-model="form.first_name"
                                            class="h-14 rounded-2xl border-slate-200 bg-slate-50/50 px-5 font-bold text-slate-900 transition-all focus:ring-2 focus:ring-indigo-600/10"
                                            required
                                        />
                                        <InputError
                                            :message="form.errors.first_name"
                                        />
                                    </div>
                                    <div class="space-y-3">
                                        <Label
                                            for="middle_name"
                                            class="ml-1 text-xs font-bold tracking-tight text-slate-500 uppercase"
                                            >Middle Name</Label
                                        >
                                        <Input
                                            id="middle_name"
                                            v-model="form.middle_name"
                                            class="h-14 rounded-2xl border-slate-200 bg-slate-50/50 px-5 font-bold text-slate-900 transition-all focus:ring-2 focus:ring-indigo-600/10"
                                        />
                                        <InputError
                                            :message="form.errors.middle_name"
                                        />
                                    </div>
                                    <div class="space-y-3">
                                        <Label
                                            for="last_name"
                                            class="ml-1 text-xs font-bold tracking-tight text-slate-500 uppercase"
                                            >Last Name</Label
                                        >
                                        <Input
                                            id="last_name"
                                            v-model="form.last_name"
                                            class="h-14 rounded-2xl border-slate-200 bg-slate-50/50 px-5 font-bold text-slate-900 transition-all focus:ring-2 focus:ring-indigo-600/10"
                                            required
                                        />
                                        <InputError
                                            :message="form.errors.last_name"
                                        />
                                    </div>
                                </div>
                                <div class="grid gap-8 md:grid-cols-2">
                                    <div class="space-y-3">
                                        <Label
                                            for="email"
                                            class="ml-1 text-xs font-bold tracking-tight text-slate-500 uppercase"
                                            >Email Address</Label
                                        >
                                        <Input
                                            id="email"
                                            v-model="form.email"
                                            type="email"
                                            class="h-14 rounded-2xl border-slate-200 bg-slate-50/50 px-5 font-bold text-slate-900 transition-all focus:ring-2 focus:ring-indigo-600/10"
                                            required
                                        />
                                        <InputError
                                            :message="form.errors.email"
                                        />
                                    </div>
                                    <div class="space-y-3">
                                        <Label
                                            for="phone"
                                            class="ml-1 text-xs font-bold tracking-tight text-slate-500 uppercase"
                                            >Phone Number</Label
                                        >
                                        <Input
                                            id="phone"
                                            v-model="form.phone"
                                            class="h-14 rounded-2xl border-slate-200 bg-slate-50/50 px-5 font-bold text-slate-900 transition-all focus:ring-2 focus:ring-indigo-600/10"
                                            required
                                        />
                                        <InputError
                                            :message="form.errors.phone"
                                        />
                                    </div>
                                    <div class="space-y-3">
                                        <Label
                                            for="id_number"
                                            class="ml-1 text-xs font-bold tracking-tight text-slate-500 uppercase"
                                            >ID / Passport Number</Label
                                        >
                                        <Input
                                            id="id_number"
                                            v-model="form.id_number"
                                            class="h-14 rounded-2xl border-slate-200 bg-slate-50/50 px-5 font-bold text-slate-900 transition-all focus:ring-2 focus:ring-indigo-600/10"
                                        />
                                        <InputError
                                            :message="form.errors.id_number"
                                        />
                                    </div>
                                    <div class="space-y-3">
                                        <Label
                                            for="gender"
                                            class="ml-1 text-xs font-bold tracking-tight text-slate-500 uppercase"
                                            >Gender</Label
                                        >
                                        <select
                                            id="gender"
                                            v-model="form.gender"
                                            class="h-14 w-full appearance-none rounded-2xl border-slate-200 bg-slate-50/50 bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2364748b%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E')] bg-[length:18px] bg-[right_1.25rem_center] bg-no-repeat px-5 text-sm font-bold transition-all focus:ring-2 focus:ring-indigo-600/10"
                                        >
                                            <option value="male">Male</option>
                                            <option value="female">
                                                Female
                                            </option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Right: Dependents -->
                    <div class="space-y-8 lg:col-span-5">
                        <div
                            class="space-y-8 overflow-hidden rounded-xl border border-slate-200 bg-white p-10 shadow-xl shadow-slate-200/40"
                        >
                            <div class="space-y-2">
                                <h2
                                    class="flex items-center gap-3 text-xl font-bold text-slate-900 uppercase"
                                >
                                    <GraduationCap
                                        class="h-6 w-6 text-blue-600"
                                    />
                                    Dependents
                                </h2>
                                <p
                                    class="pl-1 text-xs font-bold tracking-tight text-muted-foreground uppercase"
                                >
                                    Link learner accounts to this guardian
                                </p>
                            </div>

                            <div class="space-y-4">
                                <Label
                                    class="ml-1 text-xs font-bold tracking-tight text-slate-500 uppercase"
                                    >Guardian Relationship</Label
                                >
                                <div class="grid grid-cols-2 gap-3">
                                    <button
                                        type="button"
                                        v-for="rel in [
                                            'Father',
                                            'Mother',
                                            'Sponsor',
                                            'Guardian',
                                            'Other',
                                        ]"
                                        :key="rel"
                                        @click="form.relationship_type = rel"
                                        class="flex h-12 items-center justify-center gap-2 rounded-xl border-2 text-sm font-bold transition-all"
                                        :class="
                                            form.relationship_type === rel
                                                ? 'border-blue-600 bg-blue-50 text-blue-600'
                                                : 'border-slate-100 bg-slate-50/50 text-slate-500 hover:border-slate-200'
                                        "
                                    >
                                        {{ rel }}
                                    </button>
                                </div>
                            </div>

                            <div
                                class="space-y-6 border-t border-slate-100 pt-4"
                            >
                                <div class="relative">
                                    <Search
                                        class="absolute top-1/2 left-4 h-4 w-4 -translate-y-1/2 text-slate-400"
                                    />
                                    <Input
                                        v-model="studentSearch"
                                        placeholder="Search learner by name or admission..."
                                        class="h-12 rounded-xl border-slate-200 bg-slate-50/30 pl-11 text-sm font-bold"
                                    />
                                </div>

                                <div class="min-h-[300px] space-y-3">
                                    <div
                                        v-for="student in filteredStudents"
                                        :key="student.id"
                                        @click="toggleStudent(student.id)"
                                        class="group flex cursor-pointer items-center justify-between rounded-2xl border-2 p-4 transition-all"
                                        :class="
                                            form.student_ids.includes(
                                                student.id,
                                            )
                                                ? 'border-blue-600 bg-blue-50/50'
                                                : 'border-slate-50 bg-slate-50/30 hover:border-primary/20 hover:bg-white'
                                        "
                                    >
                                        <div class="flex items-center gap-4">
                                            <div
                                                class="flex h-10 w-10 items-center justify-center rounded-xl text-xs font-bold transition-colors"
                                                :class="
                                                    form.student_ids.includes(
                                                        student.id,
                                                    )
                                                        ? 'bg-blue-600 text-white'
                                                        : 'bg-slate-200 text-slate-500 group-hover:bg-primary/20 group-hover:text-blue-600'
                                                "
                                            >
                                                {{ student.first_name[0]
                                                }}{{ student.last_name[0] }}
                                            </div>
                                            <div>
                                                <p
                                                    class="text-sm font-bold text-slate-900"
                                                >
                                                    {{ student.first_name }}
                                                    {{ student.last_name }}
                                                </p>
                                                <p
                                                    class="text-xs font-bold tracking-tighter text-slate-400 uppercase"
                                                >
                                                    {{
                                                        student.admission_number
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                        <div
                                            class="flex h-6 w-6 items-center justify-center rounded-full border-2 transition-all"
                                            :class="
                                                form.student_ids.includes(
                                                    student.id,
                                                )
                                                    ? 'scale-110 border-blue-600 bg-blue-600 text-white'
                                                    : 'border-slate-200 group-hover:border-primary/30'
                                            "
                                        >
                                            <CheckCircle2
                                                v-if="
                                                    form.student_ids.includes(
                                                        student.id,
                                                    )
                                                "
                                                class="h-3.5 w-3.5"
                                            />
                                        </div>
                                    </div>

                                    <div
                                        v-if="filteredStudents.length === 0"
                                        class="flex flex-col items-center justify-center space-y-3 py-10 text-center"
                                    >
                                        <div
                                            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-50 text-slate-300"
                                        >
                                            <Search class="h-6 w-6" />
                                        </div>
                                        <p
                                            class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                                        >
                                            No learners found
                                        </p>
                                    </div>
                                </div>

                                <div
                                    v-if="form.student_ids.length > 0"
                                    class="space-y-4 rounded-3xl bg-slate-900 p-5 text-white"
                                >
                                    <p
                                        class="text-xs font-medium tracking-tight text-blue-400 text-muted-foreground uppercase"
                                    >
                                        Selected Dependents
                                    </p>
                                    <div class="flex flex-wrap gap-2">
                                        <Badge
                                            v-for="id in form.student_ids"
                                            :key="id"
                                            class="gap-2 rounded-lg border-0 bg-white/10 px-3 py-1.5 text-xs font-bold text-white hover:bg-white/20"
                                        >
                                            {{ getStudentName(id) }}
                                            <X
                                                class="h-3 w-3 cursor-pointer text-white/40 hover:text-white"
                                                @click.stop="toggleStudent(id)"
                                            />
                                        </Badge>
                                    </div>
                                </div>
                                <InputError
                                    :message="form.errors.student_ids"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Hub -->
                <div
                    class="mt-6 flex flex-wrap items-center justify-between gap-6 border-t-4 border-dashed border-slate-200 py-10"
                >
                    <Button
                        type="button"
                        variant="ghost"
                        class="h-14 rounded-2xl px-8 font-medium tracking-tight text-slate-400 uppercase transition-all hover:text-red-600"
                        as-child
                    >
                        <Link href="/parents">Discard Changes</Link>
                    </Button>
                    <div class="flex gap-4">
                        <Button
                            type="submit"
                            :disabled="form.processing"
                            class="h-14 rounded-2xl border-0 bg-blue-600 px-12 font-bold tracking-tight text-white uppercase shadow-lg shadow-blue-200 transition-all hover:bg-blue-700"
                        >
                            <Loader2
                                v-if="form.processing"
                                class="mr-3 h-5 w-5 animate-spin"
                            />
                            <Save v-else class="mr-3 h-5 w-5" />
                            Update Account
                        </Button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Confirmation Modal -->
        <Dialog :open="confirmOpen" @update:open="confirmOpen = $event">
            <DialogContent
                class="max-w-lg overflow-hidden rounded-2xl border-0 bg-white p-0 shadow-lg"
            >
                <div class="space-y-8 p-12 text-center">
                    <div
                        class="mx-auto flex h-24 w-24 rotate-3 items-center justify-center rounded-xl border-8 border-white bg-amber-50 shadow-xl"
                    >
                        <AlertTriangle class="h-10 w-10 text-amber-500" />
                    </div>
                    <div class="space-y-3">
                        <h3 class="text-2xl font-bold text-slate-900 uppercase">
                            Verify Updates
                        </h3>
                        <p class="text-sm font-medium text-slate-500">
                            You are about to update the guardian profile for
                            <span class="font-bold text-blue-600">{{
                                parent.first_name
                            }}</span
                            >. This will affect their portal access and link to
                            <span class="font-bold text-slate-900">{{
                                form.student_ids.length
                            }}</span>
                            learner(s).
                        </p>
                    </div>
                    <div
                        class="grid grid-cols-2 gap-6 rounded-xl border-2 border-slate-100 bg-slate-50 p-6"
                    >
                        <div class="space-y-1 text-left">
                            <p
                                class="text-xs leading-none font-bold tracking-tight text-slate-400 uppercase"
                            >
                                Registration
                            </p>
                            <p
                                class="text-sm font-bold tracking-tight text-slate-900"
                            >
                                {{ form.first_name }} {{ form.last_name }}
                            </p>
                        </div>
                        <div class="space-y-1 text-left">
                            <p
                                class="text-xs leading-none font-bold tracking-tight text-slate-400 uppercase"
                            >
                                Role Type
                            </p>
                            <p class="text-sm font-bold text-blue-600">
                                {{ form.relationship_type }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="flex gap-4 bg-slate-50 p-6">
                    <Button
                        variant="ghost"
                        class="h-14 flex-1 rounded-2xl font-medium tracking-tight text-slate-400 uppercase"
                        @click="confirmOpen = false"
                        >Wait, Edit</Button
                    >
                    <Button
                        @click="confirmSubmit"
                        :disabled="form.processing"
                        class="h-14 flex-1 rounded-2xl border-0 bg-blue-600 font-medium tracking-tight text-white uppercase shadow-xl shadow-indigo-600/20 transition-all hover:bg-slate-900"
                    >
                        <Loader2
                            v-if="form.processing"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        Confirm
                    </Button>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Success Modal -->
        <Dialog :open="successOpen" @update:open="successOpen = $event">
            <DialogContent
                class="max-w-md overflow-hidden rounded-2xl border-0 bg-white p-0 shadow-lg"
            >
                <div class="space-y-8 p-12 text-center">
                    <div
                        class="mx-auto flex h-24 w-24 -rotate-3 items-center justify-center rounded-xl border-8 border-white bg-emerald-50 shadow-xl"
                    >
                        <CheckCircle2 class="h-10 w-10 text-emerald-500" />
                    </div>
                    <div class="space-y-3">
                        <h3
                            class="text-3xl font-bold tracking-tight text-slate-900 uppercase"
                        >
                            Account Updated
                        </h3>
                        <p
                            class="text-sm leading-relaxed font-medium text-slate-500"
                        >
                            Updates for
                            <span class="font-bold text-emerald-500">{{
                                form.first_name
                            }}</span>
                            have been successfully synchronized.
                        </p>
                    </div>
                    <Button
                        as-child
                        class="mt-4 h-14 w-full rounded-2xl border-0 bg-slate-900 font-medium tracking-tight text-white uppercase shadow-lg transition-all hover:bg-blue-600"
                    >
                        <Link href="/parents"> Return to Registry </Link>
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
