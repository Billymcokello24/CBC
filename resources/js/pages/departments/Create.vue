<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { ArrowLeft, Building2, Save, X, Home, ChevronRight } from 'lucide-vue-next';

const page = usePage();
const form = useForm({
    name: '',
    code: '',
    description: '',
    head_of_department_id: '',
    is_active: true,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        head_of_department_id: data.head_of_department_id || null,
    })).post('/departments', {
        onSuccess: () => form.reset(),
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
        <div class="mx-auto max-w-[1600px] animate-in space-y-6 p-4 pb-10 duration-700 fade-in slide-in-from-bottom-4 sm:space-y-8 sm:p-6 sm:pb-20 md:p-8">
            <!-- Header -->
            <div class="flex items-start gap-4 px-1">
                <Button variant="outline" size="icon" class="mt-1 flex h-8 w-8 shrink-0 rounded-xl md:h-10 md:w-10" as-child>
                    <Link href="/departments"><ArrowLeft class="h-4 w-4 md:h-5 md:w-5" /></Link>
                </Button>
                <div class="flex flex-col gap-1">
                    <div class="mb-1 flex items-center gap-2 text-xs text-muted-foreground sm:text-xs">
                        <Home class="h-3 w-3 sm:h-3.5 sm:w-3.5" />
                        <ChevronRight class="h-2.5 w-2.5 sm:h-3 sm:w-3" />
                        <span class="font-medium tracking-tight text-foreground uppercase">Departments</span>
                        <ChevronRight class="h-2.5 w-2.5 sm:h-3 sm:w-3" />
                        <span class="font-medium tracking-tight text-foreground uppercase">Create</span>
                    </div>
                    <h1 class="text-2xl leading-tight font-bold tracking-tight text-foreground sm:text-3xl">
                        Create Department
                    </h1>
                    <p class="text-sm text-muted-foreground sm:text-sm">
                        Add a new academic faculty or unit
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
                                        v-for="t in page.props.teachers"
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
                            Save Department
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
