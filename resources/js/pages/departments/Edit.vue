<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { ArrowLeft, Building2, Save, X } from 'lucide-vue-next';

const page = usePage();
const dept = page.props?.department ?? {};
const teachers = page.props?.teachers ?? [];

const form = useForm({
  name: dept.name ?? '',
  code: dept.code ?? '',
  description: dept.description ?? '',
  head_of_department_id: dept.head_of_department_id ?? '',
  is_active: typeof dept.is_active !== 'undefined' ? (!!dept.is_active) : true
});

const submit = () => {
  form.transform((data) => ({
    ...data,
    head_of_department_id: data.head_of_department_id || null
  })).put(`/departments/${dept.id}`);
};

const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Departments', href: '/departments' },
  { title: 'Edit', href: '#' },
];
</script>

<template>
  <Head :title="`Edit Department - ${dept.name}`" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="w-full py-8 sm:py-12 px-4 sm:px-6 lg:px-8 space-y-8 sm:space-y-12 animate-in fade-in duration-500">
      <!-- Header -->
      <div class="flex items-center gap-4">
        <Button variant="ghost" size="icon" as-child class="h-10 w-10 rounded-xl hover:bg-slate-50 text-slate-400 hover:text-blue-600 transition-all">
          <Link href="/departments">
            <ArrowLeft class="h-5 w-5" />
          </Link>
        </Button>
        <div>
          <h1 class="text-2xl font-black tracking-tight text-slate-900 uppercase">Edit Department</h1>
          <p class="text-xs text-slate-400 font-bold uppercase tracking-widest mt-1">Update details for the {{ dept.name }} unit</p>
        </div>
      </div>

      <div class="grid gap-8 lg:grid-cols-3">
        <!-- Main Form -->
        <div class="lg:col-span-2 space-y-8">
          <div class="rounded-2xl border bg-white p-8 shadow-sm border-slate-100">
            <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-8 flex items-center gap-2">
              <Building2 class="h-4 w-4 text-blue-600" />
              Department Information
            </h3>
            <div class="grid gap-8">
              <div class="grid gap-3">
                <label for="name" class="text-xs font-bold text-slate-700 uppercase tracking-wide">Department Name <span class="text-rose-500">*</span></label>
                <Input id="name" v-model="form.name" placeholder="e.g., Mathematics & Sciences" class="h-12 rounded-xl border-slate-200 focus:ring-blue-600 transition-all" :class="{ 'border-rose-500': form.errors.name }" />
                <p v-if="form.errors.name" class="text-[10px] font-bold text-rose-600 uppercase tracking-tight">{{ form.errors.name[0] }}</p>
              </div>

              <div class="grid gap-3">
                <label for="code" class="text-xs font-bold text-slate-700 uppercase tracking-wide">Department Code <span class="text-rose-500">*</span></label>
                <Input id="code" v-model="form.code" placeholder="e.g., MATH" class="h-12 rounded-xl border-slate-200 focus:ring-blue-600 transition-all uppercase" :class="{ 'border-rose-500': form.errors.code }" />
                <p v-if="form.errors.code" class="text-[10px] font-bold text-rose-600 uppercase tracking-tight">{{ form.errors.code[0] }}</p>
              </div>

              <div class="grid gap-3">
                <label for="description" class="text-xs font-bold text-slate-700 uppercase tracking-wide">Department Description</label>
                <textarea 
                  id="description" 
                  v-model="form.description" 
                  rows="4" 
                  class="flex min-h-[120px] w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 transition-all placeholder:text-slate-400"
                  placeholder="Describe the department's focus and academic responsibilities..."
                ></textarea>
                <p v-if="form.errors.description" class="text-[10px] font-bold text-rose-600 uppercase tracking-tight">{{ form.errors.description[0] }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar / Settings -->
        <div class="space-y-8">
          <div class="rounded-2xl border bg-white p-8 shadow-sm border-slate-100">
            <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-8">Management Settings</h3>
            
            <div class="space-y-8">
              <div class="grid gap-3">
                <label for="head" class="text-xs font-bold text-slate-700 uppercase tracking-wide">Head of Department</label>
                <select v-model="form.head_of_department_id" class="h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 transition-all">
                  <option value="">-- Choose Instructor --</option>
                  <option v-for="t in teachers" :key="t.id" :value="t.id">{{ t.name }}</option>
                </select>
                <p v-if="form.errors.head_of_department_id" class="text-[10px] font-bold text-rose-600 uppercase tracking-tight">{{ form.errors.head_of_department_id[0] }}</p>
              </div>

              <div class="flex items-center justify-between rounded-xl border border-slate-100 p-4 bg-slate-50/50">
                <div class="space-y-1">
                  <label for="isActive" class="text-xs font-bold text-slate-900 uppercase tracking-tight">Active Status</label>
                  <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Visible to staff</p>
                </div>
                <input 
                  type="checkbox" 
                  id="isActive" 
                  v-model="form.is_active"
                  class="h-5 w-5 rounded-md border-slate-300 text-blue-600 focus:ring-blue-600 transition-all cursor-pointer"
                />
              </div>
            </div>
          </div>

          <div class="flex flex-col gap-4">
            <Button @click="submit" class="w-full h-14 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-black uppercase tracking-widest shadow-xl shadow-blue-100 transition-all border-0" :disabled="form.processing">
              <Save class="mr-3 h-5 w-5" />
              Update Department
            </Button>
            <Button variant="ghost" as-child class="w-full h-12 rounded-xl text-slate-400 hover:text-slate-600 font-bold uppercase tracking-wider transition-all">
              <Link href="/departments">
                <X class="mr-2 h-4 w-4" />
                Discard Changes
              </Link>
            </Button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
