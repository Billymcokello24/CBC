<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

const name = ref('');
const code = ref('');
const description = ref('');
const head = ref('');
const isActive = ref(true);

const submit = () => {
  router.post('/departments', { name: name.value, code: code.value, description: description.value, head_of_department_id: head.value, is_active: isActive.value });
};
</script>

<template>
  <Head title="Create Department" />
  <AppLayout>
    <div class="p-6">
      <h1 class="text-xl font-bold mb-4">Create Department</h1>
      <div class="grid gap-3 max-w-lg">
        <Input v-model="name" placeholder="Name" />
        <Input v-model="code" placeholder="Code" />
        <Input v-model="description" placeholder="Description" />
        <select v-model="head" class="input">
          <option value="">-- Select Head (optional) --</option>
          <option v-for="t in $page.props.teachers" :key="t.id" :value="t.id">{{ t.name }}</option>
        </select>
        <div class="flex items-center gap-2">
          <input type="checkbox" v-model="isActive" /> Active
        </div>
        <div class="flex gap-2">
          <Button @click="submit">Save</Button>
          <Button variant="outline" as-child><a href="/departments">Cancel</a></Button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
