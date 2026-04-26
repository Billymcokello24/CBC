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
    competency?: any;
    title: string;
}>();

const emit = defineEmits(['close', 'success']);

const form = useForm({
    name: '',
    code: '',
    description: '',
    category: 'Core',
    display_order: 0,
    is_active: true,
});

watch(
    () => props.competency,
    (newComp) => {
        if (newComp) {
            form.name = newComp.name;
            form.code = newComp.code;
            form.description = newComp.description || '';
            form.category = newComp.category || 'Core';
            form.display_order = newComp.display_order;
            form.is_active = !!newComp.is_active;
        } else {
            form.reset();
        }
    },
    { immediate: true },
);

const submit = () => {
    if (props.competency) {
        form.put(`/curriculum/competencies/${props.competency.id}`, {
            onSuccess: () => {
                emit('success');
                emit('close');
            },
        });
    } else {
        form.post('/curriculum/competencies', {
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
        <DialogContent class="font-pulsar sm:max-w-[500px]">
            <DialogHeader>
                <DialogTitle
                    class="text-xl font-bold tracking-tight text-slate-900 uppercase"
                    >{{ title }}</DialogTitle
                >
                <DialogDescription class="font-medium text-slate-500">
                    {{
                        competency
                            ? 'Synchronize competency definitions and indicator clusters.'
                            : 'Initialize a new core competency within the academic framework.'
                    }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="grid gap-5 py-4">
                <div class="grid gap-2">
                    <Label
                        for="name"
                        class="text-xs font-medium tracking-tight text-slate-400 uppercase"
                        >Competency Name</Label
                    >
                    <Input
                        id="name"
                        v-model="form.name"
                        class="h-10 border-slate-200"
                        placeholder="e.g. Critical Thinking"
                        required
                    />
                </div>

                <div class="grid gap-2">
                    <Label
                        for="code"
                        class="text-xs font-medium tracking-tight text-slate-400 uppercase"
                        >System Code</Label
                    >
                    <Input
                        id="code"
                        v-model="form.code"
                        class="h-10 border-slate-200 uppercase"
                        placeholder="e.g. CORE-CT"
                        required
                    />
                </div>

                <div class="grid gap-2">
                    <Label
                        class="text-xs font-medium tracking-tight text-slate-400 uppercase"
                        >Classification</Label
                    >
                    <Select v-model="form.category">
                        <SelectTrigger class="h-10 border-slate-200">
                            <SelectValue placeholder="Select category" />
                        </SelectTrigger>
                        <SelectContent class="font-pulsar">
                            <SelectItem value="Core"
                                >Core Competency</SelectItem
                            >
                            <SelectItem value="Technical"
                                >Technical / Practical</SelectItem
                            >
                            <SelectItem value="Social"
                                >Socio-Emotional</SelectItem
                            >
                            <SelectItem value="Values"
                                >Living Values</SelectItem
                            >
                        </SelectContent>
                    </Select>
                </div>

                <div class="grid gap-2">
                    <Label
                        for="description"
                        class="text-xs font-medium tracking-tight text-slate-400 uppercase"
                        >Logical Scope</Label
                    >
                    <Textarea
                        id="description"
                        v-model="form.description"
                        class="min-h-[80px] border-slate-200"
                        placeholder="Detailed breakdown..."
                    />
                </div>

                <div class="flex items-center justify-between gap-4">
                    <div class="grid flex-1 gap-2">
                        <Label
                            for="display_order"
                            class="text-xs font-medium tracking-tight text-slate-400 uppercase"
                            >Registry Order</Label
                        >
                        <Input
                            id="display_order"
                            type="number"
                            v-model="form.display_order"
                            class="h-10 border-slate-200"
                            required
                        />
                    </div>
                    <div class="flex items-center space-x-2 pt-6">
                        <Checkbox
                            id="is_active"
                            :checked="form.is_active"
                            @update:checked="
                                (val: boolean) => (form.is_active = !!val)
                            "
                        />
                        <Label
                            for="is_active"
                            class="cursor-pointer text-xs font-bold text-emerald-600 uppercase"
                            >Operational</Label
                        >
                    </div>
                </div>

                <DialogFooter>
                    <Button
                        type="button"
                        variant="ghost"
                        @click="$emit('close')"
                        class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                        >Abort</Button
                    >
                    <Button
                        type="submit"
                        :disabled="form.processing"
                        class="h-10 bg-violet-600 px-6 text-xs font-bold tracking-tight uppercase shadow-lg shadow-violet-100 hover:bg-violet-700"
                    >
                        {{ competency ? 'Update Matrix' : 'Establish Node' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
