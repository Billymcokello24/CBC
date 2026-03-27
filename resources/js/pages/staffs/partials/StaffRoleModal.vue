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

watch(() => props.staff, (newStaff) => {
    if (newStaff) {
        roleForm.role = newStaff.user?.roles?.[0]?.name || '';
        hodForm.department_id = newStaff.department_id?.toString() || '';
    }
}, { immediate: true });

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
        <DialogContent class="sm:max-w-[500px] p-0 overflow-hidden border-0 rounded-3xl shadow-2xl">
            <DialogHeader class="p-8 bg-slate-900 text-white relative">
                <div class="absolute -right-6 -top-6 opacity-10 font-black uppercase text-6xl select-none pointer-events-none tracking-tighter">
                     ROLES
                </div>
                <div class="flex items-center gap-4 relative z-10">
                    <div class="h-12 w-12 rounded-2xl bg-white/10 flex items-center justify-center border border-white/10">
                        <ShieldCheck class="h-6 w-6 text-indigo-400" />
                    </div>
                    <div>
                        <DialogTitle class="text-2xl font-bold tracking-tight">Manage Staff Roles</DialogTitle>
                        <DialogDescription class="text-slate-400 mt-1 font-medium">
                            {{ staff?.first_name }} {{ staff?.last_name }} - {{ staff?.staff_number }}
                        </DialogDescription>
                    </div>
                </div>
            </DialogHeader>

            <div class="p-0 border-b border-border bg-muted/30">
                <div class="flex">
                    <button 
                        @click="activeTab = 'system'"
                        class="flex-1 py-4 text-xs font-bold uppercase tracking-wider transition-all border-b-2"
                        :class="activeTab === 'system' ? 'border-indigo-600 text-indigo-600 bg-white' : 'border-transparent text-muted-foreground hover:text-foreground'"
                    >
                        Access Role
                    </button>
                    <button 
                        @click="activeTab = 'hod'"
                        class="flex-1 py-4 text-xs font-bold uppercase tracking-wider transition-all border-b-2"
                        :class="activeTab === 'hod' ? 'border-indigo-600 text-indigo-600 bg-white' : 'border-transparent text-muted-foreground hover:text-foreground'"
                    >
                        HOD Assignment
                    </button>
                    <button 
                        @click="activeTab = 'class'"
                        class="flex-1 py-4 text-xs font-bold uppercase tracking-wider transition-all border-b-2"
                        :class="activeTab === 'class' ? 'border-indigo-600 text-indigo-600 bg-white' : 'border-transparent text-muted-foreground hover:text-foreground'"
                    >
                        Class Teacher
                    </button>
                </div>
            </div>

            <div class="p-8 space-y-6">
                <!-- System Role Section -->
                <div v-if="activeTab === 'system'" class="space-y-6 animate-in fade-in slide-in-from-bottom-2 duration-300">
                    <div class="flex items-start gap-4 p-4 rounded-2xl bg-indigo-50/50 border border-indigo-100">
                        <div class="h-10 w-10 rounded-xl bg-white flex items-center justify-center shadow-sm text-indigo-600">
                            <UserCog class="h-5 w-5" />
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-900 text-sm tracking-tight">System Account Role</h4>
                            <p class="text-[11px] text-indigo-600/70 font-bold uppercase tracking-wider mt-1">Global Permissions</p>
                        </div>
                    </div>

                    <div class="space-y-2.5">
                        <Label class="text-xs font-bold uppercase tracking-widest text-muted-foreground/70">Select System Role</Label>
                        <Select v-model="roleForm.role">
                            <SelectTrigger class="h-12 rounded-xl border-border bg-background px-4 font-medium shadow-sm ring-offset-background focus:ring-2 focus:ring-indigo-600/10 transition-all">
                                <SelectValue placeholder="Choose a system role..." />
                            </SelectTrigger>
                            <SelectContent class="rounded-xl border border-border shadow-xl">
                                <SelectItem v-for="role in roles" :key="role.id" :value="role.name" class="rounded-lg py-2.5 px-3 mb-1 last:mb-0 transition-colors cursor-pointer capitalize font-semibold italic text-indigo-600">
                                    {{ role.name.replace('_', ' ') }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="roleForm.errors.role" />
                        <p class="text-[11px] text-muted-foreground/60 leading-relaxed font-medium mt-2 italic px-1">
                            Note: Changing the system role will immediately update the staff member's administrative permissions across the platform.
                        </p>
                    </div>

                    <DialogFooter class="pt-4 mt-8 border-t border-border gap-3">
                        <Button variant="ghost" class="rounded-xl font-bold h-11" @click="handleClose(false)">Cancel</Button>
                        <Button 
                            class="rounded-xl bg-indigo-600 h-11 px-8 font-bold shadow-lg shadow-indigo-100 transition-all hover:bg-indigo-700 active:scale-95" 
                            :disabled="roleForm.processing"
                            @click="submitRole"
                        >
                            <span v-if="roleForm.processing" class="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-white/20 border-t-white"></span>
                            Update System Role
                        </Button>
                    </DialogFooter>
                </div>

                <!-- HOD Section -->
                <div v-if="activeTab === 'hod'" class="space-y-6 animate-in fade-in slide-in-from-bottom-2 duration-300">
                    <div class="flex items-start gap-4 p-4 rounded-2xl bg-emerald-50/50 border border-emerald-100">
                        <div class="h-10 w-10 rounded-xl bg-white flex items-center justify-center shadow-sm text-emerald-600">
                            <Building2 class="h-5 w-5" />
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-900 text-sm tracking-tight">HOD Assignment</h4>
                            <p class="text-[11px] text-emerald-600/70 font-bold uppercase tracking-wider mt-1">Department Leadership</p>
                        </div>
                    </div>

                    <div class="space-y-2.5">
                        <Label class="text-xs font-bold uppercase tracking-widest text-muted-foreground/70 text-[10px]">Select Department to Lead</Label>
                        <Select v-model="hodForm.department_id">
                            <SelectTrigger class="h-12 rounded-xl border-border bg-background px-4 font-medium shadow-sm transition-all">
                                <SelectValue placeholder="Choose a department..." />
                            </SelectTrigger>
                            <SelectContent class="rounded-xl border border-border shadow-xl">
                                <SelectItem v-for="dept in departments" :key="dept.id" :value="dept.id.toString()" class="rounded-lg py-2.5 px-3 mb-1 transition-colors cursor-pointer font-bold text-emerald-600">
                                    Head of {{ dept.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="hodForm.errors.department_id" />
                    </div>

                    <DialogFooter class="pt-4 mt-8 border-t border-border gap-3">
                        <Button variant="ghost" class="rounded-xl font-bold h-11" @click="handleClose(false)">Cancel</Button>
                        <Button 
                            class="rounded-xl bg-emerald-600 h-11 px-8 font-bold shadow-lg shadow-emerald-100 transition-all hover:bg-emerald-700 active:scale-95" 
                            :disabled="hodForm.processing"
                            @click="submitHOD"
                        >
                            <span v-if="hodForm.processing" class="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-white/20 border-t-white"></span>
                            Appoint as HOD
                        </Button>
                    </DialogFooter>
                </div>

                <!-- Class Teacher Section -->
                <div v-if="activeTab === 'class'" class="space-y-6 animate-in fade-in slide-in-from-bottom-2 duration-300">
                    <div class="flex items-start gap-4 p-4 rounded-2xl bg-amber-50/50 border border-amber-100">
                        <div class="h-10 w-10 rounded-xl bg-white flex items-center justify-center shadow-sm text-amber-600">
                            <CheckCircle2 class="h-5 w-5" />
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-900 text-sm tracking-tight">Class Leadership</h4>
                            <p class="text-[11px] text-amber-600/70 font-bold uppercase tracking-wider mt-1">Student Management</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2.5">
                            <Label class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/70">Select Class</Label>
                            <Select v-model="classForm.class_id">
                                <SelectTrigger class="h-12 rounded-xl border-border bg-background px-4 font-medium shadow-sm">
                                    <SelectValue placeholder="Assign class..." />
                                </SelectTrigger>
                                <SelectContent class="rounded-xl border border-border overflow-y-auto max-h-[300px]">
                                    <SelectItem v-for="cls in availableClasses" :key="cls.id" :value="cls.id.toString()" class="rounded-lg py-2.5 transition-colors cursor-pointer font-bold text-amber-600">
                                        {{ cls.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2.5">
                            <Label class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/70">Teacher Type</Label>
                            <Select v-model="classForm.assignment_role">
                                <SelectTrigger class="h-12 rounded-xl border-border bg-background px-4 font-medium shadow-sm tracking-tight">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent class="rounded-xl border border-border font-bold">
                                    <SelectItem value="primary">Primary Teacher</SelectItem>
                                    <SelectItem value="assistant">Assistant Teacher</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                    <InputError :message="classForm.errors.class_id" />

                    <DialogFooter class="pt-4 mt-8 border-t border-border gap-3">
                        <Button variant="ghost" class="rounded-xl font-bold h-11" @click="handleClose(false)">Cancel</Button>
                        <Button 
                            class="rounded-xl bg-amber-600 h-11 px-8 font-bold shadow-lg shadow-amber-100 transition-all hover:bg-amber-700 active:scale-95" 
                            :disabled="classForm.processing"
                            @click="submitClass"
                        >
                            <span v-if="classForm.processing" class="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-white/20 border-t-white"></span>
                            Assign Class Role
                        </Button>
                    </DialogFooter>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
