<script setup lang="ts">
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { CheckCircle2, ShieldCheck, Building2, UserCog } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';

const props = defineProps<{
    open: boolean;
    staff: any;
    roles: any[];
    departments: any[];
    availableClasses: any[];
}>();

const emit = defineEmits(['update:open']);

const activeTab = ref<'system' | 'hod' | 'class'>('system');

// System Role Form
const roleForm = useForm({
    role: '',
});

// HOD Assignment Form
const hodForm = useForm({
    department_id: '',
});

// Class Teacher Form
const classForm = useForm({
    class_id: '',
    assignment_role: 'primary',
});

watch(
    () => props.staff,
    (newStaff) => {
        if (newStaff) {
            roleForm.role = newStaff.user?.roles?.[0]?.name || '';
            hodForm.department_id = newStaff.department_id?.toString() || '';
        }
    },
    { immediate: true },
);

const submitRole = () => {
    roleForm.post(route('staffs.change-role', props.staff.id), {
        onSuccess: () => {
            emit('update:open', false);
        },
    });
};

const submitHOD = () => {
    hodForm.post(route('staffs.assign-hod', props.staff.id), {
        onSuccess: () => {
            emit('update:open', false);
        },
    });
};

const submitClass = () => {
    classForm.post(route('staffs.assign-class-teacher', props.staff.id), {
        onSuccess: () => {
            emit('update:open', false);
        },
    });
};

const handleClose = (value: boolean) => {
    emit('update:open', value);
};
</script>

<template>
    <Dialog :open="open" @update:open="handleClose">
        <DialogContent
            class="overflow-hidden rounded-3xl border-0 p-0 shadow-lg sm:max-w-[500px]"
        >
            <DialogHeader class="relative bg-slate-900 p-8 text-white">
                <div
                    class="pointer-events-none absolute -top-6 -right-6 text-6xl font-bold tracking-tighter uppercase opacity-10 select-none"
                >
                    ROLES
                </div>
                <div class="relative z-10 flex items-center gap-4">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-2xl border border-white/10 bg-white/10"
                    >
                        <ShieldCheck class="h-6 w-6 text-indigo-400" />
                    </div>
                    <div>
                        <DialogTitle class="text-2xl font-bold tracking-tight"
                            >Manage Staff Roles</DialogTitle
                        >
                        <DialogDescription
                            class="mt-1 font-medium text-slate-400"
                        >
                            {{ staff?.first_name }} {{ staff?.last_name }} -
                            {{ staff?.staff_number }}
                        </DialogDescription>
                    </div>
                </div>
            </DialogHeader>

            <div class="border-b border-border bg-muted/30 p-0">
                <div class="flex">
                    <button
                        @click="activeTab = 'system'"
                        class="flex-1 border-b-2 py-4 text-xs font-bold tracking-wider uppercase transition-all"
                        :class="
                            activeTab === 'system'
                                ? 'border-indigo-600 bg-white text-indigo-600'
                                : 'border-transparent text-muted-foreground hover:text-foreground'
                        "
                    >
                        Access Role
                    </button>
                    <button
                        @click="activeTab = 'hod'"
                        class="flex-1 border-b-2 py-4 text-xs font-bold tracking-wider uppercase transition-all"
                        :class="
                            activeTab === 'hod'
                                ? 'border-indigo-600 bg-white text-indigo-600'
                                : 'border-transparent text-muted-foreground hover:text-foreground'
                        "
                    >
                        HOD Assignment
                    </button>
                    <button
                        @click="activeTab = 'class'"
                        class="flex-1 border-b-2 py-4 text-xs font-bold tracking-wider uppercase transition-all"
                        :class="
                            activeTab === 'class'
                                ? 'border-indigo-600 bg-white text-indigo-600'
                                : 'border-transparent text-muted-foreground hover:text-foreground'
                        "
                    >
                        Class Teacher
                    </button>
                </div>
            </div>

            <div class="space-y-6 p-8">
                <!-- System Role Section -->
                <div
                    v-if="activeTab === 'system'"
                    class="animate-in space-y-6 duration-300 fade-in slide-in-from-bottom-2"
                >
                    <div
                        class="flex items-start gap-4 rounded-2xl border border-indigo-100 bg-indigo-50/50 p-4"
                    >
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-indigo-600 shadow-sm"
                        >
                            <UserCog class="h-5 w-5" />
                        </div>
                        <div>
                            <h4
                                class="text-sm font-bold tracking-tight text-slate-900"
                            >
                                System Account Role
                            </h4>
                            <p
                                class="mt-1 text-sm font-bold tracking-wider text-indigo-600/70 uppercase"
                            >
                                Global Permissions
                            </p>
                        </div>
                    </div>

                    <div class="space-y-2.5">
                        <Label
                            class="text-xs font-bold tracking-tight text-muted-foreground/70 uppercase"
                            >Select System Role</Label
                        >
                        <Select v-model="roleForm.role">
                            <SelectTrigger
                                class="h-12 rounded-xl border-border bg-background px-4 font-medium shadow-sm ring-offset-background transition-all focus:ring-2 focus:ring-indigo-600/10"
                            >
                                <SelectValue
                                    placeholder="Choose a system role..."
                                />
                            </SelectTrigger>
                            <SelectContent
                                class="rounded-xl border border-border shadow-xl"
                            >
                                <SelectItem
                                    v-for="role in roles"
                                    :key="role.id"
                                    :value="role.name"
                                    class="mb-1 cursor-pointer rounded-lg px-3 py-2.5 font-semibold text-indigo-600 capitalize transition-colors last:mb-0"
                                >
                                    {{ role.name.replace('_', ' ') }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="roleForm.errors.role" />
                        <p
                            class="mt-2 px-1 text-sm leading-relaxed font-medium text-muted-foreground/60"
                        >
                            Note: Changing the system role will immediately
                            update the staff member's administrative permissions
                            across the platform.
                        </p>
                    </div>

                    <DialogFooter
                        class="mt-8 gap-3 border-t border-border pt-4"
                    >
                        <Button
                            variant="ghost"
                            class="h-11 rounded-xl font-bold"
                            @click="handleClose(false)"
                            >Cancel</Button
                        >
                        <Button
                            class="h-11 rounded-xl bg-indigo-600 px-8 font-bold shadow-lg shadow-indigo-100 transition-all hover:bg-indigo-700 active:scale-95"
                            :disabled="roleForm.processing"
                            @click="submitRole"
                        >
                            <span
                                v-if="roleForm.processing"
                                class="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-white/20 border-t-white"
                            ></span>
                            Update System Role
                        </Button>
                    </DialogFooter>
                </div>

                <!-- HOD Section -->
                <div
                    v-if="activeTab === 'hod'"
                    class="animate-in space-y-6 duration-300 fade-in slide-in-from-bottom-2"
                >
                    <div
                        class="flex items-start gap-4 rounded-2xl border border-emerald-100 bg-emerald-50/50 p-4"
                    >
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-emerald-600 shadow-sm"
                        >
                            <Building2 class="h-5 w-5" />
                        </div>
                        <div>
                            <h4
                                class="text-sm font-bold tracking-tight text-slate-900"
                            >
                                HOD Assignment
                            </h4>
                            <p
                                class="mt-1 text-sm font-bold tracking-wider text-emerald-600/70 uppercase"
                            >
                                Department Leadership
                            </p>
                        </div>
                    </div>

                    <div class="space-y-2.5">
                        <Label
                            class="text-xs font-bold tracking-tight text-muted-foreground/70 uppercase"
                            >Select Department to Lead</Label
                        >
                        <Select v-model="hodForm.department_id">
                            <SelectTrigger
                                class="h-12 rounded-xl border-border bg-background px-4 font-medium shadow-sm transition-all"
                            >
                                <SelectValue
                                    placeholder="Choose a department..."
                                />
                            </SelectTrigger>
                            <SelectContent
                                class="rounded-xl border border-border shadow-xl"
                            >
                                <SelectItem
                                    v-for="dept in departments"
                                    :key="dept.id"
                                    :value="dept.id.toString()"
                                    class="mb-1 cursor-pointer rounded-lg px-3 py-2.5 font-bold text-emerald-600 transition-colors"
                                >
                                    Head of {{ dept.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="hodForm.errors.department_id" />
                    </div>

                    <DialogFooter
                        class="mt-8 gap-3 border-t border-border pt-4"
                    >
                        <Button
                            variant="ghost"
                            class="h-11 rounded-xl font-bold"
                            @click="handleClose(false)"
                            >Cancel</Button
                        >
                        <Button
                            class="h-11 rounded-xl bg-emerald-600 px-8 font-bold shadow-lg shadow-emerald-100 transition-all hover:bg-emerald-700 active:scale-95"
                            :disabled="hodForm.processing"
                            @click="submitHOD"
                        >
                            <span
                                v-if="hodForm.processing"
                                class="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-white/20 border-t-white"
                            ></span>
                            Appoint as HOD
                        </Button>
                    </DialogFooter>
                </div>

                <!-- Class Teacher Section -->
                <div
                    v-if="activeTab === 'class'"
                    class="animate-in space-y-6 duration-300 fade-in slide-in-from-bottom-2"
                >
                    <div
                        class="flex items-start gap-4 rounded-2xl border border-amber-100 bg-amber-50/50 p-4"
                    >
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-amber-600 shadow-sm"
                        >
                            <CheckCircle2 class="h-5 w-5" />
                        </div>
                        <div>
                            <h4
                                class="text-sm font-bold tracking-tight text-slate-900"
                            >
                                Class Leadership
                            </h4>
                            <p
                                class="mt-1 text-sm font-bold tracking-wider text-amber-600/70 uppercase"
                            >
                                Student Management
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2.5">
                            <Label
                                class="text-xs font-bold tracking-tight text-muted-foreground/70 uppercase"
                                >Select Class</Label
                            >
                            <Select v-model="classForm.class_id">
                                <SelectTrigger
                                    class="h-12 rounded-xl border-border bg-background px-4 font-medium shadow-sm"
                                >
                                    <SelectValue
                                        placeholder="Assign class..."
                                    />
                                </SelectTrigger>
                                <SelectContent
                                    class="max-h-[300px] overflow-y-auto rounded-xl border border-border"
                                >
                                    <SelectItem
                                        v-for="cls in availableClasses"
                                        :key="cls.id"
                                        :value="cls.id.toString()"
                                        class="cursor-pointer rounded-lg py-2.5 font-bold text-amber-600 transition-colors"
                                    >
                                        {{ cls.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2.5">
                            <Label
                                class="text-xs font-bold tracking-tight text-muted-foreground/70 uppercase"
                                >Teacher Type</Label
                            >
                            <Select v-model="classForm.assignment_role">
                                <SelectTrigger
                                    class="h-12 rounded-xl border-border bg-background px-4 font-medium tracking-tight shadow-sm"
                                >
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent
                                    class="rounded-xl border border-border font-bold"
                                >
                                    <SelectItem value="primary"
                                        >Primary Teacher</SelectItem
                                    >
                                    <SelectItem value="assistant"
                                        >Assistant Teacher</SelectItem
                                    >
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                    <InputError :message="classForm.errors.class_id" />

                    <DialogFooter
                        class="mt-8 gap-3 border-t border-border pt-4"
                    >
                        <Button
                            variant="ghost"
                            class="h-11 rounded-xl font-bold"
                            @click="handleClose(false)"
                            >Cancel</Button
                        >
                        <Button
                            class="h-11 rounded-xl bg-amber-600 px-8 font-bold shadow-lg shadow-amber-100 transition-all hover:bg-amber-700 active:scale-95"
                            :disabled="classForm.processing"
                            @click="submitClass"
                        >
                            <span
                                v-if="classForm.processing"
                                class="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-white/20 border-t-white"
                            ></span>
                            Assign Class Role
                        </Button>
                    </DialogFooter>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
