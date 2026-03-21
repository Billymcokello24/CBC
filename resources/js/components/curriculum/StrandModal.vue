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
  strand?: any;
  subjects: any[];
  grades: any[];
  title: string;
}>();

const emit = defineEmits(['close', 'success']);

const form = useForm({
  subject_id: '',
  grade_level_id: '',
  name: '',
  code: '',
  description: '',
  display_order: 0,
  is_active: true,
});

watch(() => props.strand, (newStrand) => {
  if (newStrand) {
    form.subject_id = newStrand.subject_id?.toString() || '';
    form.grade_level_id = newStrand.grade_level_id?.toString() || '';
    form.name = newStrand.name;
    form.code = newStrand.code;
    form.description = newStrand.description || '';
    form.display_order = newStrand.display_order;
    form.is_active = !!newStrand.is_active;
  } else {
    form.reset();
  }
}, { immediate: true });

const submit = () => {
  if (props.strand) {
    form.put(`/curriculum/strands/${props.strand.id}`, {
      onSuccess: () => {
        emit('success');
        emit('close');
      },
    });
  } else {
    form.post('/curriculum/strands', {
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
    <DialogContent class="sm:max-w-[550px] font-pulsar">
      <DialogHeader>
        <DialogTitle class="text-xl font-black text-slate-900 uppercase tracking-tight">{{ title }}</DialogTitle>
        <DialogDescription class="font-medium text-slate-500 italic">
          {{ strand ? 'Refine strand definitions and topical scope.' : 'Initialize a new topic strand within the subject hierarchy.' }}
        </DialogDescription>
      </DialogHeader>
      
      <form @submit.prevent="submit" class="grid gap-5 py-4">
        <div class="grid grid-cols-2 gap-4">
            <div class="grid gap-2">
                <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Parent Subject</Label>
                <Select v-model="form.subject_id">
                    <SelectTrigger class="h-10 border-slate-200">
                    <SelectValue placeholder="Select subject" />
                    </SelectTrigger>
                    <SelectContent class="font-pulsar">
                    <SelectItem v-for="sub in subjects" :key="sub.id" :value="sub.id.toString()">
                        {{ sub.name }}
                    </SelectItem>
                    </SelectContent>
                </Select>
            </div>
            <div class="grid gap-2">
                <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Grade Level</Label>
                <Select v-model="form.grade_level_id">
                    <SelectTrigger class="h-10 border-slate-200">
                    <SelectValue placeholder="Select grade" />
                    </SelectTrigger>
                    <SelectContent class="font-pulsar">
                    <SelectItem v-for="grade in grades" :key="grade.id" :value="grade.id.toString()">
                        {{ grade.name }}
                    </SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </div>

        <div class="grid gap-2">
          <Label for="name" class="text-[10px] font-black uppercase tracking-widest text-slate-400">Strand Name</Label>
          <Input id="name" v-model="form.name" class="h-10 border-slate-200" placeholder="e.g. Numbers & Operations" required />
        </div>
        
        <div class="grid gap-2">
          <Label for="code" class="text-[10px] font-black uppercase tracking-widest text-slate-400">Topic Code</Label>
          <Input id="code" v-model="form.code" class="h-10 border-slate-200 uppercase" placeholder="e.g. NUM-01" required />
        </div>

        <div class="grid gap-2">
          <Label for="description" class="text-[10px] font-black uppercase tracking-widest text-slate-400">Learning Objectives</Label>
          <Textarea id="description" v-model="form.description" class="border-slate-200 min-h-[80px]" placeholder="Key competencies covered..." />
        </div>

        <div class="flex items-center justify-between gap-4">
            <div class="grid gap-2 flex-1">
                <Label for="display_order" class="text-[10px] font-black uppercase tracking-widest text-slate-400">Sequence Order</Label>
                <Input id="display_order" type="number" v-model="form.display_order" class="h-10 border-slate-200" required />
            </div>
            <div class="flex items-center space-x-2 pt-6">
                <Checkbox id="is_active" :checked="form.is_active" @update:checked="(val: boolean) => form.is_active = val" />
                <Label for="is_active" class="text-[10px] font-bold uppercase cursor-pointer text-emerald-600">Active Node</Label>
            </div>
        </div>

        <DialogFooter class="pt-4">
          <Button type="button" variant="ghost" @click="$emit('close')" class="font-black text-[10px] uppercase tracking-widest text-slate-400">Cancel</Button>
          <Button type="submit" :disabled="form.processing" class="bg-amber-600 hover:bg-amber-700 font-black text-[10px] uppercase tracking-widest h-10 px-6 shadow-lg shadow-amber-100">
            {{ strand ? 'Synchronize Strand' : 'Engage Strand' }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>
