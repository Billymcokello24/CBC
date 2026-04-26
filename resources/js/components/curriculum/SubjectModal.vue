<script setup lang="ts">
/* global route */
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';

const props = defineProps<{
    show: boolean;
    subject?: any;
    learningAreas: any[];
    title: string;
}>();

const emit = defineEmits(['close', 'success']);

const form = useForm({
    learning_area_id: '',
    name: '',
    code: '',
    description: '',
    subject_type: 'Core',
    is_examinable: true,
    display_order: 0,
    is_active: true,
});

watch(
    () => props.subject,
    (newSubject) => {
        if (newSubject) {
            form.learning_area_id =
                newSubject.learning_area_id?.toString() || '';
            form.name = newSubject.name;
            form.code = newSubject.code;
            form.description = newSubject.description || '';
            form.subject_type = newSubject.subject_type || 'Core';
            form.is_examinable = !!newSubject.is_examinable;
            form.display_order = newSubject.display_order;
            form.is_active = !!newSubject.is_active;
        } else {
            form.reset();
        }
    },
    { immediate: true },
);

const submit = () => {
    if (props.subject) {
        form.put(`/curriculum/subjects/${props.subject.id}`, {
            onSuccess: () => {
                emit('success');
                emit('close');
            },
        });
    } else {
        form.post('/curriculum/subjects', {
            onSuccess: () => {
                emit('success');
                emit('close');
            },
        });
    }
};
</script>

<template>
    <Dialog :open="show" @update:open="$emit('close')">
        <DialogContent class="font-pulsar sm:max-w-[550px]">
            <DialogHeader>
                <DialogTitle
                    class="text-xl font-bold tracking-tight text-slate-900 uppercase"
                    >{{ title }}</DialogTitle
                >
                <DialogDescription class="font-medium text-slate-500">
                    {{
                        subject
                            ? 'Synchronize subject parameters and academic metadata.'
                            : 'Initialize a new subject node within the curriculum registry.'
                    }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="grid gap-5 py-4">
                <div class="grid grid-cols-4 items-center gap-4">
                    <Label
                        class="text-right text-xs font-medium tracking-tight text-slate-400 uppercase"
                        >Learning Area</Label
                    >
                    <Select v-model="form.learning_area_id">
                        <SelectTrigger class="col-span-3 h-10 border-slate-200">
                            <SelectValue placeholder="Select parent cluster" />
                        </SelectTrigger>
                        <SelectContent class="font-pulsar">
                            <SelectItem
                                v-for="area in learningAreas"
                                :key="area.id"
                                :value="area.id.toString()"
                            >
                                {{ area.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="grid grid-cols-4 items-center gap-4">
                    <Label
                        for="name"
                        class="text-right text-xs font-medium tracking-tight text-slate-400 uppercase"
                        >Name</Label
                    >
                    <Input
                        id="name"
                        v-model="form.name"
                        class="col-span-3 h-10 border-slate-200"
                        placeholder="e.g. Mathematics"
                        required
                    />
                </div>

                <div class="grid grid-cols-4 items-center gap-4">
                    <Label
                        for="code"
                        class="text-right text-xs font-medium tracking-tight text-slate-400 uppercase"
                        >Code</Label
                    >
                    <Input
                        id="code"
                        v-model="form.code"
                        class="col-span-3 h-10 border-slate-200 uppercase"
                        placeholder="e.g. MATH"
                        required
                    />
                </div>

                <div class="grid grid-cols-4 items-center gap-4">
                    <Label
                        class="text-right text-xs font-medium tracking-tight text-slate-400 uppercase"
                        >Subject Type</Label
                    >
                    <Select v-model="form.subject_type">
                        <SelectTrigger class="col-span-3 h-10 border-slate-200">
                            <SelectValue placeholder="Select type" />
                        </SelectTrigger>
                        <SelectContent class="font-pulsar">
                            <SelectItem value="Core">Core Subject</SelectItem>
                            <SelectItem value="Elective"
                                >Elective / Optional</SelectItem
                            >
                            <SelectItem value="Applied"
                                >Applied / Technical</SelectItem
                            >
                        </SelectContent>
                    </Select>
                </div>

                <div class="grid grid-cols-4 items-center gap-4">
                    <Label
                        for="description"
                        class="text-right text-xs font-medium tracking-tight text-slate-400 uppercase"
                        >Definition</Label
                    >
                    <Textarea
                        id="description"
                        v-model="form.description"
                        class="col-span-3 min-h-[80px] border-slate-200"
                        placeholder="Subject scope..."
                    />
                </div>

                <div class="grid grid-cols-4 items-center gap-4">
                    <Label
                        class="text-right text-xs font-medium tracking-tight text-slate-400 uppercase"
                        >Attributes</Label
                    >
                    <div class="col-span-3 flex items-center gap-6">
                        <div class="flex items-center space-x-2">
                            <Checkbox
                                id="is_examinable"
                                :checked="form.is_examinable"
                                @update:checked="
                                    (val: boolean) => (form.is_examinable = val)
                                "
                            />
                            <Label
                                for="is_examinable"
                                class="cursor-pointer text-xs font-bold uppercase"
                                >Examinable</Label
                            >
                        </div>
                        <div class="flex items-center space-x-2">
                            <Checkbox
                                id="is_active"
                                :checked="form.is_active"
                                @update:checked="
                                    (val: boolean) => (form.is_active = val)
                                "
                            />
                            <Label
                                for="is_active"
                                class="cursor-pointer text-xs font-bold text-emerald-600 uppercase"
                                >Active</Label
                            >
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-4 items-center gap-4">
                    <Label
                        for="display_order"
                        class="text-right text-xs font-medium tracking-tight text-slate-400 uppercase"
                        >Priority</Label
                    >
                    <Input
                        id="display_order"
                        type="number"
                        v-model="form.display_order"
                        class="col-span-3 h-10 border-slate-200"
                        required
                    />
                </div>

                <DialogFooter>
                    <Button
                        type="button"
                        variant="ghost"
                        @click="$emit('close')"
                        class="text-xs font-bold tracking-tight uppercase"
                        >Abort</Button
                    >
                    <Button
                        type="submit"
                        :disabled="form.processing"
                        class="h-10 bg-indigo-600 px-6 text-xs font-bold tracking-tight uppercase shadow-lg shadow-indigo-100 hover:bg-indigo-700"
                    >
                        {{ subject ? 'Update Logic' : 'Establish Node' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
