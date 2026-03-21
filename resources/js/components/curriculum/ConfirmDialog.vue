<script setup lang="ts">
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { AlertTriangle, Trash2, ShieldAlert } from 'lucide-vue-next';

const props = defineProps<{
  show: boolean;
  title: string;
  message: string;
  confirmText?: string;
  variant?: 'danger' | 'warning' | 'info';
  loading?: boolean;
}>();

const emit = defineEmits(['close', 'confirm']);

const getIcon = () => {
  switch (props.variant) {
    case 'danger': return Trash2;
    case 'warning': return AlertTriangle;
    default: return ShieldAlert;
  }
};

const getVariantClass = () => {
  switch (props.variant) {
    case 'danger': return 'bg-rose-500 hover:bg-rose-600 text-white shadow-rose-100';
    case 'warning': return 'bg-amber-500 hover:bg-amber-600 text-white shadow-amber-100';
    default: return 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-indigo-100';
  }
};

const getIconBg = () => {
  switch (props.variant) {
    case 'danger': return 'bg-rose-50 text-rose-600 border-rose-100';
    case 'warning': return 'bg-amber-50 text-amber-600 border-amber-100';
    default: return 'bg-indigo-50 text-indigo-600 border-indigo-100';
  }
};
</script>

<template>
  <Dialog :open="show" @update:open="$emit('close')">
    <DialogContent class="sm:max-w-[400px] font-pulsar border-0 shadow-2xl p-0 overflow-hidden">
      <div class="p-6">
        <div class="flex items-center gap-4 mb-4">
          <div :class="['h-12 w-12 rounded-2xl flex items-center justify-center border shadow-inner', getIconBg()]">
            <component :is="getIcon()" class="h-6 w-6" />
          </div>
          <div>
            <DialogTitle class="text-lg font-black text-slate-900 uppercase tracking-tight">{{ title }}</DialogTitle>
          </div>
        </div>
        
        <DialogDescription class="text-sm font-medium text-slate-500 leading-relaxed italic">
          {{ message }}
        </DialogDescription>
      </div>
      
      <div class="bg-slate-50/50 p-4 flex items-center justify-end gap-3 border-t">
        <Button variant="ghost" @click="$emit('close')" :disabled="loading" class="font-black text-[10px] uppercase tracking-widest text-slate-400">Abort Action</Button>
        <Button 
          @click="$emit('confirm')" 
          :disabled="loading" 
          :class="['font-black text-[10px] uppercase tracking-widest h-10 px-6 shadow-lg transition-all', getVariantClass()]"
        >
          {{ loading ? 'Processing...' : (confirmText || 'Confirm System Change') }}
        </Button>
      </div>
    </DialogContent>
  </Dialog>
</template>
