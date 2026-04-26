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
    is_active: typeof dept.is_active !== 'undefined' ? !!dept.is_active : true,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        head_of_department_id: data.head_of_department_id || null,
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
        <div
            class="w-full animate-in space-y-8 px-4 py-8 duration-500 fade-in sm:space-y-12 sm:px-6 sm:py-12 lg:px-8"
        >
            <!-- Header -->
            <div class="flex items-center gap-4">
                <Button
                    variant="ghost"
                    size="icon"
                    as-child
                    class="h-10 w-10 rounded-xl text-slate-400 transition-all hover:bg-slate-50 hover:text-blue-600"
                >
                    <Link href="/departments">
                        <ArrowLeft class="h-5 w-5" />
                    </Link>
                </Button>
                <div>
                    <h1
                        class="text-2xl font-bold tracking-tight text-slate-900 uppercase"
                    >
                        Edit Department
                    </h1>
                    <p
                        class="mt-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                    >
                        Update details for the {{ dept.name }} unit
                    </p>
                </div>
            </div>

            <div class="grid gap-8 lg:grid-cols-3">
                <!-- Main Form -->
                <div class="space-y-8 lg:col-span-2">
                    <div
                        class="rounded-2xl border border-slate-100 bg-white p-8 shadow-sm"
                    >
                        <h3
                            class="mb-8 flex items-center gap-2 text-xs font-bold tracking-tight text-slate-400 uppercase"
                        >
                            <Building2 class="h-4 w-4 text-blue-600" />
                            Department Information
                        </h3>
                        <div class="grid gap-8">
                            <div class="grid gap-3">
                                <label
                                    for="name"
                                    class="text-xs font-bold tracking-wide text-slate-700 uppercase"
                                    >Department Name
                                    <span class="text-rose-500">*</span></label
                                >
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    placeholder="e.g., Mathematics & Sciences"
                                    class="h-12 rounded-xl border-slate-200 transition-all focus:ring-blue-600"
                                    :class="{
                                        'border-rose-500': form.errors.name,
                                    }"
                                />
                                <p
                                    v-if="form.errors.name"
                                    class="text-xs font-bold tracking-tight text-rose-600 uppercase"
                                >
                                    {{ form.errors.name[0] }}
                                </p>
                            </div>

                            <div class="grid gap-3">
                                <label
                                    for="code"
                                    class="text-xs font-bold tracking-wide text-slate-700 uppercase"
                                    >Department Code
                                    <span class="text-rose-500">*</span></label
                                >
                                <Input
                                    id="code"
                                    v-model="form.code"
                                    placeholder="e.g., MATH"
                                    class="h-12 rounded-xl border-slate-200 uppercase transition-all focus:ring-blue-600"
                                    :class="{
                                        'border-rose-500': form.errors.code,
                                    }"
                                />
                                <p
                                    v-if="form.errors.code"
                                    class="text-xs font-bold tracking-tight text-rose-600 uppercase"
                                >
                                    {{ form.errors.code[0] }}
                                </p>
                            </div>

                            <div class="grid gap-3">
                                <label
                                    for="description"
                                    class="text-xs font-bold tracking-wide text-slate-700 uppercase"
                                    >Department Description</label
                                >
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="4"
                                    class="flex min-h-[120px] w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm transition-all placeholder:text-slate-400 focus:ring-2 focus:ring-blue-600 focus:outline-none"
                                    placeholder="Describe the department's focus and academic responsibilities..."
                                ></textarea>
                                <p
                                    v-if="form.errors.description"
                                    class="text-xs font-bold tracking-tight text-rose-600 uppercase"
                                >
                                    {{ form.errors.description[0] }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar / Settings -->
                <div class="space-y-8">
                    <div
                        class="rounded-2xl border border-slate-100 bg-white p-8 shadow-sm"
                    >
                        <h3
                            class="mb-8 text-xs font-bold tracking-tight text-slate-400 uppercase"
                        >
                            Management Settings
                        </h3>

                        <div class="space-y-8">
                            <div class="grid gap-3">
                                <label
                                    for="head"
                                    class="text-xs font-bold tracking-wide text-slate-700 uppercase"
                                    >Head of Department</label
                                >
                                <select
                                    v-model="form.head_of_department_id"
                                    class="h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm transition-all focus:ring-2 focus:ring-blue-600 focus:outline-none"
                                >
                                    <option value="">
                                        -- Choose Instructor --
                                    </option>
                                    <option
                                        v-for="t in teachers"
                                        :key="t.id"
                                        :value="t.id"
                                    >
                                        {{ t.name }}
                                    </option>
                                </select>
                                <p
                                    v-if="form.errors.head_of_department_id"
                                    class="text-xs font-bold tracking-tight text-rose-600 uppercase"
                                >
                                    {{ form.errors.head_of_department_id[0] }}
                                </p>
                            </div>

                            <div
                                class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/50 p-4"
                            >
                                <div class="space-y-1">
                                    <label
                                        for="isActive"
                                        class="text-xs font-bold tracking-tight text-slate-900 uppercase"
                                        >Active Status</label
                                    >
                                    <p
                                        class="text-xs font-bold tracking-wider text-slate-400 uppercase"
                                    >
                                        Visible to staff
                                    </p>
                                </div>
                                <input
                                    type="checkbox"
                                    id="isActive"
                                    v-model="form.is_active"
                                    class="h-5 w-5 cursor-pointer rounded-md border-slate-300 text-blue-600 transition-all focus:ring-blue-600"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-4">
                        <Button
                            @click="submit"
                            class="h-14 w-full rounded-2xl border-0 bg-blue-600 font-medium tracking-tight text-white uppercase shadow-xl shadow-blue-100 transition-all hover:bg-blue-700"
                            :disabled="form.processing"
                        >
                            <Save class="mr-3 h-5 w-5" />
                            Update Department
                        </Button>
                        <Button
                            variant="ghost"
                            as-child
                            class="h-12 w-full rounded-xl font-bold tracking-wider text-slate-400 uppercase transition-all hover:text-slate-600"
                        >
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
