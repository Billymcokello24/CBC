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

watch(() => props.competency, (newComp) => {
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
}, { immediate: true });

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
    <DialogContent class="sm:max-w-[500px] font-pulsar">
      <DialogHeader>
        <DialogTitle class="text-xl font-black text-slate-900 uppercase tracking-tight">{{ title }}</DialogTitle>
        <DialogDescription class="font-medium text-slate-500 italic">
          {{ competency ? 'Synchronize competency definitions and indicator clusters.' : 'Initialize a new core competency within the academic framework.' }}
        </DialogDescription>
      </DialogHeader>
      
      <form @submit.prevent="submit" class="grid gap-5 py-4">
        <div class="grid gap-2">
          <Label for="name" class="text-[10px] font-black uppercase tracking-widest text-slate-400">Competency Name</Label>
          <Input id="name" v-model="form.name" class="h-10 border-slate-200" placeholder="e.g. Critical Thinking" required />
        </div>
        
        <div class="grid gap-2">
          <Label for="code" class="text-[10px] font-black uppercase tracking-widest text-slate-400">System Code</Label>
          <Input id="code" v-model="form.code" class="h-10 border-slate-200 uppercase" placeholder="e.g. CORE-CT" required />
        </div>

        <div class="grid gap-2">
          <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Classification</Label>
          <Select v-model="form.category">
            <SelectTrigger class="h-10 border-slate-200">
              <SelectValue placeholder="Select category" />
            </SelectTrigger>
            <SelectContent class="font-pulsar">
              <SelectItem value="Core">Core Competency</SelectItem>
              <SelectItem value="Technical">Technical / Practical</SelectItem>
              <SelectItem value="Social">Socio-Emotional</SelectItem>
              <SelectItem value="Values">Living Values</SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div class="grid gap-2">
          <Label for="description" class="text-[10px] font-black uppercase tracking-widest text-slate-400">Logical Scope</Label>
          <Textarea id="description" v-model="form.description" class="border-slate-200 min-h-[80px]" placeholder="Detailed breakdown..." />
        </div>

        <div class="flex items-center justify-between gap-4">
            <div class="grid gap-2 flex-1">
                <Label for="display_order" class="text-[10px] font-black uppercase tracking-widest text-slate-400">Registry Order</Label>
                <Input id="display_order" type="number" v-model="form.display_order" class="h-10 border-slate-200" required />
            </div>
            <div class="flex items-center space-x-2 pt-6">
                <Checkbox id="is_active" :checked="form.is_active" @update:checked="(val: boolean) => form.is_active = !!val" />
                <Label for="is_active" class="text-[10px] font-bold uppercase cursor-pointer text-emerald-600">Operational</Label>
            </div>
        </div>

        <DialogFooter>
          <Button type="button" variant="ghost" @click="$emit('close')" class="font-black text-[10px] uppercase tracking-widest text-slate-400">Abort</Button>
          <Button type="submit" :disabled="form.processing" class="bg-violet-600 hover:bg-violet-700 font-black text-[10px] uppercase tracking-widest h-10 px-6 shadow-lg shadow-violet-100">
            {{ competency ? 'Update Matrix' : 'Establish Node' }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>
