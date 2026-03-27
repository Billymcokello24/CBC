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
import { BookOpen, Plus, Trash2, GraduationCap, LayoutPanelLeft } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';

const props = defineProps<{
    open: boolean;
    staff: any;
    availableSubjects: any[];
    availableClasses: any[];
}>();

const emit = defineEmits(['update:open']);

const form = useForm({
    assignments: [] as { subject_id: string; class_id: string }[],
});

watch(() => props.staff, (newStaff) => {
    if (newStaff && newStaff.subject_assignments) {
        form.assignments = newStaff.subject_assignments.map((a: any) => ({
            subject_id: a.subject_id.toString(),
            class_id: a.class_id.toString(),
        }));
    } else {
        form.assignments = [{ subject_id: '', class_id: '' }];
    }
}, { immediate: true });

const addAssignment = () => {
    form.assignments.push({ subject_id: '', class_id: '' });
};

const removeAssignment = (index: number) => {
    form.assignments.splice(index, 1);
};

const submit = () => {
    form.post(route('staffs.assign-subjects', props.staff.id), {
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
        <DialogContent class="sm:max-w-[600px] p-0 overflow-hidden border-0 rounded-3xl shadow-2xl">
            <DialogHeader class="p-8 bg-indigo-950 text-white relative">
                <div class="absolute -right-6 -top-6 opacity-10 font-black uppercase text-7xl select-none pointer-events-none tracking-tighter">
                     TEACH
                </div>
                <div class="flex items-center gap-4 relative z-10">
                    <div class="h-12 w-12 rounded-2xl bg-white/10 flex items-center justify-center border border-white/10 shadow-inner">
                        <BookOpen class="h-6 w-6 text-indigo-400" />
                    </div>
                    <div>
                        <DialogTitle class="text-2xl font-bold tracking-tight">Assign Subjects & Classes</DialogTitle>
                        <DialogDescription class="text-indigo-300/70 mt-1 font-medium">
                            {{ staff?.first_name }} {{ staff?.last_name }} - {{ staff?.staff_number }}
                        </DialogDescription>
                    </div>
                </div>
            </DialogHeader>

            <div class="p-8 space-y-6 max-h-[60vh] overflow-y-auto custom-scrollbar">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-xs font-bold uppercase tracking-widest text-muted-foreground/70">Topic Assignments</h4>
                    <Button variant="outline" size="sm" @click="addAssignment" class="h-8 rounded-lg border-indigo-200 text-indigo-700 hover:bg-indigo-50 font-bold px-3">
                        <Plus class="mr-1.5 h-3.5 w-3.5" /> Add Assignment
                    </Button>
                </div>

                <div v-for="(assignment, index) in form.assignments" :key="index" 
                    class="p-5 rounded-2xl border border-indigo-100 bg-indigo-50/30 space-y-4 relative group animate-in fade-in slide-in-from-top-2 duration-300"
                >
                    <button 
                        v-if="form.assignments.length > 1"
                        @click="removeAssignment(index)" 
                        class="absolute -top-2 -right-2 h-7 w-7 rounded-full bg-white border border-rose-100 text-rose-500 shadow-sm flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all hover:bg-rose-50 hover:scale-110 active:scale-90"
                    >
                        <Trash2 class="h-3.5 w-3.5" />
                    </button>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/60 flex items-center gap-1.5 px-1">
                                <GraduationCap class="h-3 w-3" /> Subject
                            </Label>
                            <Select v-model="assignment.subject_id">
                                <SelectTrigger class="h-11 rounded-xl border-indigo-100 bg-white shadow-sm font-medium">
                                    <SelectValue placeholder="Select subject..." />
                                </SelectTrigger>
                                <SelectContent class="rounded-xl border border-indigo-100 shadow-xl max-h-[300px]">
                                    <SelectItem v-for="subject in availableSubjects" :key="subject.id" :value="subject.id.toString()" class="rounded-lg py-2.5 font-bold text-indigo-900">
                                        {{ subject.name }} <span class="text-[10px] text-muted-foreground/40 ml-1 font-medium">({{ subject.code }})</span>
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <Label class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/60 flex items-center gap-1.5 px-1">
                                <LayoutPanelLeft class="h-3 w-3" /> Class
                            </Label>
                            <Select v-model="assignment.class_id">
                                <SelectTrigger class="h-11 rounded-xl border-indigo-100 bg-white shadow-sm font-medium">
                                    <SelectValue placeholder="Select class..." />
                                </SelectTrigger>
                                <SelectContent class="rounded-xl border border-indigo-100 shadow-xl max-h-[300px]">
                                    <SelectItem v-for="cls in availableClasses" :key="cls.id" :value="cls.id.toString()" class="rounded-lg py-2.5 font-bold text-slate-700">
                                        {{ cls.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </div>

                <div v-if="form.assignments.length === 0" class="p-12 text-center border-2 border-dashed border-indigo-100 rounded-3xl bg-muted/5">
                    <BookOpen class="h-12 w-12 text-indigo-100 mx-auto mb-4" />
                    <p class="text-sm font-medium text-muted-foreground italic">No subjects assigned yet.</p>
                </div>
                
                <InputError v-if="form.errors.assignments" :message="form.errors.assignments" class="mt-4" />
            </div>

            <DialogFooter class="p-8 border-t border-border bg-muted/10 gap-3">
                <Button variant="ghost" class="rounded-xl font-bold h-12 px-6" @click="handleClose(false)">Cancel</Button>
                <Button 
                    class="rounded-xl bg-indigo-600 h-12 px-10 font-bold shadow-lg shadow-indigo-200 transition-all hover:bg-indigo-700 hover:shadow-indigo-300 active:scale-95" 
                    :disabled="form.processing"
                    @click="submit"
                >
                    <span v-if="form.processing" class="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-white/20 border-t-white"></span>
                    Save Assignments
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
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
