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
  area?: any;
  title: string;
}>();

const emit = defineEmits(['close', 'success']);

const form = useForm({
  name: '',
  code: '',
  description: '',
  category: '',
  display_order: 0,
  is_active: true,
});

watch(() => props.area, (newArea) => {
  if (newArea) {
    form.name = newArea.name;
    form.code = newArea.code;
    form.description = newArea.description || '';
    form.category = newArea.category || '';
    form.display_order = newArea.display_order;
    form.is_active = !!newArea.is_active;
  } else {
    form.reset();
  }
}, { immediate: true });

const submit = () => {
  if (props.area) {
    form.put(route('curriculum.learning-areas.update', props.area.id), {
      onSuccess: () => {
        emit('success');
        emit('close');
      },
    });
  } else {
    form.post(route('curriculum.learning-areas.store'), {
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
    <DialogContent class="sm:max-w-[525px] font-pulsar">
      <DialogHeader>
        <DialogTitle class="text-xl font-black text-slate-900 uppercase tracking-tight">{{ title }}</DialogTitle>
        <DialogDescription class="font-medium text-slate-500 italic">
          {{ area ? 'Update existing learning area metadata and parameters.' : 'Define a new academic cluster within the curriculum.' }}
        </DialogDescription>
      </DialogHeader>
      
      <form @submit.prevent="submit" class="grid gap-6 py-4">
        <div class="grid grid-cols-4 items-center gap-4">
          <Label for="name" class="text-right text-[10px] font-black uppercase tracking-widest text-slate-400">Name</Label>
          <Input id="name" v-model="form.name" class="col-span-3 h-10 border-slate-200" placeholder="e.g. Languages" required />
          <div v-if="form.errors.name" class="col-start-2 col-span-3 text-[10px] font-bold text-rose-500 uppercase tracking-wider">{{ form.errors.name }}</div>
        </div>
        
        <div class="grid grid-cols-4 items-center gap-4">
          <Label for="code" class="text-right text-[10px] font-black uppercase tracking-widest text-slate-400">Code</Label>
          <Input id="code" v-model="form.code" class="col-span-3 h-10 border-slate-200 uppercase" placeholder="e.g. LANG" required />
          <div v-if="form.errors.code" class="col-start-2 col-span-3 text-[10px] font-bold text-rose-500 uppercase tracking-wider">{{ form.errors.code }}</div>
        </div>

        <div class="grid grid-cols-4 items-center gap-4">
          <Label for="category" class="text-right text-[10px] font-black uppercase tracking-widest text-slate-400">Category</Label>
          <Select v-model="form.category">
            <SelectTrigger class="col-span-3 h-10 border-slate-200">
              <SelectValue placeholder="Select cluster category" />
            </SelectTrigger>
            <SelectContent class="font-pulsar">
              <SelectItem value="Core">Core Cluster</SelectItem>
              <SelectItem value="Optional">Optional / Elective</SelectItem>
              <SelectItem value="Technical">Technical & Applied</SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div class="grid grid-cols-4 items-center gap-4">
          <Label for="description" class="text-right text-[10px] font-black uppercase tracking-widest text-slate-400">Notes</Label>
          <Textarea id="description" v-model="form.description" class="col-span-3 border-slate-200 min-h-[80px]" placeholder="Brief context for this area..." />
        </div>

        <div class="grid grid-cols-4 items-center gap-4">
          <Label for="display_order" class="text-right text-[10px] font-black uppercase tracking-widest text-slate-400">Priority</Label>
          <Input id="display_order" type="number" v-model="form.display_order" class="col-span-3 h-10 border-slate-200" required />
        </div>

        <div class="grid grid-cols-4 items-center gap-4">
          <Label class="text-right text-[10px] font-black uppercase tracking-widest text-slate-400">Status</Label>
          <div class="col-span-3 flex items-center space-x-2">
            <Checkbox :checked="form.is_active" @update:checked="(val: boolean) => form.is_active = val" />
            <span class="text-xs font-bold" :class="form.is_active ? 'text-emerald-600' : 'text-slate-400'">
              {{ form.is_active ? 'ENABLED NODE' : 'DISABLED NODE' }}
            </span>
          </div>
        </div>

        <DialogFooter>
          <Button type="button" variant="ghost" @click="$emit('close')" class="font-black text-[10px] uppercase tracking-widest">Cancel</Button>
          <Button type="submit" :disabled="form.processing" class="bg-indigo-600 hover:bg-indigo-700 font-black text-[10px] uppercase tracking-widest h-10 px-6 shadow-lg shadow-indigo-100">
            {{ area ? 'Synchronize Updates' : 'Initialize Area' }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>
