<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { ArrowLeft, Building2, Save, X } from 'lucide-vue-next';

const page = usePage();
const form = useForm({
  name: '',
  code: '',
  description: '',
  head_of_department_id: '',
  is_active: true
});

const submit = () => {
  form.transform((data) => ({
    ...data,
    head_of_department_id: data.head_of_department_id || null
  })).post('/departments', {
    onSuccess: () => form.reset()
  });
};

const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Departments', href: '/departments' },
  { title: 'Create', href: '#' },
];
</script>

<template>
  <Head title="Create Department" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 p-6">
      <!-- Header -->
      <div class="flex items-center gap-4">
        <Button variant="ghost" size="icon" as-child class="rounded-full">
          <Link href="/departments">
            <ArrowLeft class="h-5 w-5" />
          </Link>
        </Button>
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-gray-900">Create Department</h1>
          <p class="text-sm text-muted-foreground">Add a new academic department to your school</p>
        </div>
      </div>

      <div class="grid gap-6 lg:grid-cols-3">
        <!-- Main Form -->
        <div class="lg:col-span-2 space-y-6">
          <div class="rounded-xl border bg-card p-6 shadow-sm">
            <div class="grid gap-6">
              <div class="grid gap-2">
                <label for="name" class="text-sm font-semibold text-gray-900">Department Name <span class="text-red-500">*</span></label>
                <Input id="name" v-model="form.name" placeholder="e.g., Mathematics & Sciences" :class="{ 'border-red-500': form.errors.name }" />
                <p v-if="form.errors.name" class="text-xs text-red-600">{{ form.errors.name[0] }}</p>
              </div>

              <div class="grid gap-2">
                <label for="code" class="text-sm font-semibold text-gray-900">Department Code <span class="text-red-500">*</span></label>
                <Input id="code" v-model="form.code" placeholder="e.g., MATH" :class="{ 'border-red-500': form.errors.code }" />
                <p v-if="form.errors.code" class="text-xs text-red-600">{{ form.errors.code[0] }}</p>
              </div>

              <div class="grid gap-2">
                <label for="description" class="text-sm font-semibold text-gray-900">Description</label>
                <textarea 
                  id="description" 
                  v-model="form.description" 
                  rows="4" 
                  class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                  placeholder="Describe the department's focus and responsibilities..."
                ></textarea>
                <p v-if="form.errors.description" class="text-xs text-red-600">{{ form.errors.description[0] }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar / Settings -->
        <div class="space-y-6">
          <div class="rounded-xl border bg-card p-6 shadow-sm">
            <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
              <Building2 class="h-4 w-4 text-indigo-600" />
              Department Settings
            </h3>
            
            <div class="space-y-4">
              <div class="grid gap-2">
                <label for="head" class="text-sm font-semibold text-gray-900">Head of Department</label>
                <select v-model="form.head_of_department_id" class="h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                  <option value="">-- Select Teacher --</option>
                  <option v-for="t in page.props.teachers" :key="t.id" :value="t.id">{{ t.name }}</option>
                </select>
                <p v-if="form.errors.head_of_department_id" class="text-xs text-red-600">{{ form.errors.head_of_department_id[0] }}</p>
              </div>

              <div class="flex items-center justify-between rounded-lg border p-3">
                <div class="space-y-0.5">
                  <label for="isActive" class="text-sm font-semibold text-gray-900">Status</label>
                  <p class="text-xs text-muted-foreground">Department is active</p>
                </div>
                <input 
                  type="checkbox" 
                  id="isActive" 
                  v-model="form.is_active"
                  class="h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
                />
              </div>
            </div>
          </div>

          <div class="flex flex-col gap-2">
            <Button @click="submit" class="w-full shadow-lg shadow-indigo-100">
              <Save class="mr-2 h-4 w-4" />
              Save Department
            </Button>
            <Button variant="outline" as-child class="w-full">
              <Link href="/departments">
                <X class="mr-2 h-4 w-4" />
                Cancel
              </Link>
            </Button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
