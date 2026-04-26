<script setup lang="ts">
/* global route */
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Loader2,
    Plus,
    GraduationCap,
    ShieldCheck,
} from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Learning Areas', href: '/curriculum/learning-areas' },
    { title: 'Initialize Domain', href: '/curriculum/learning-areas/create' },
];

const form = useForm({
    name: '',
    code: '',
    description: '',
    category: '',
    display_order: 0,
    is_active: true,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        display_order: Number(data.display_order),
        is_active: Boolean(data.is_active),
    })).post('/curriculum/learning-areas');
};
</script>

<template>
    <Head title="Initialize New Learning Domain" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="font-pulsar mx-auto mt-2 flex h-full max-w-[1200px] flex-1 animate-in flex-col gap-8 p-8 duration-500 fade-in"
        >
            <!-- Header section -->
            <div class="flex items-center gap-4">
                <Button
                    variant="outline"
                    size="icon"
                    as-child
                    class="h-10 w-10 shrink-0 border-slate-200"
                >
                    <Link href="/curriculum/learning-areas"
                        ><ArrowLeft class="h-4 w-4"
                    /></Link>
                </Button>
                <div
                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10"
                >
                    <Plus class="h-6 w-6 text-emerald-600" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">
                        Initialize Learning Domain
                    </h1>
                    <p
                        class="text-xs font-medium tracking-tight text-muted-foreground uppercase"
                    >
                        Establish a new top-level domain within the curriculum
                        framework
                    </p>
                </div>
            </div>

            <div class="grid gap-8 lg:grid-cols-12">
                <!-- Main Form Area -->
                <div class="lg:col-span-8">
                    <form
                        @submit.prevent="submit"
                        class="space-y-8 rounded-xl border border-t-8 border-t-emerald-500 bg-card p-10 shadow-sm"
                    >
                        <div class="grid gap-8">
                            <div class="grid gap-4 text-left sm:grid-cols-2">
                                <div class="space-y-2">
                                    <Label
                                        for="name"
                                        class="pl-1 text-xs font-medium tracking-tight text-slate-400 uppercase"
                                        >Domain Nomenclature</Label
                                    >
                                    <Input
                                        id="name"
                                        v-model="form.name"
                                        class="h-12 rounded-xl border-slate-200 focus:ring-emerald-500"
                                        placeholder="e.g. Social Sciences"
                                    />
                                    <InputError :message="form.errors.name" />
                                </div>
                                <div class="space-y-2">
                                    <Label
                                        for="code"
                                        class="pl-1 text-xs font-medium tracking-tight text-slate-400 uppercase"
                                        >Registry Code</Label
                                    >
                                    <Input
                                        id="code"
                                        v-model="form.code"
                                        class="h-12 rounded-xl border-slate-200 font-bold uppercase focus:ring-emerald-500"
                                        placeholder="e.g. SOC-SCI"
                                    />
                                    <InputError :message="form.errors.code" />
                                </div>
                            </div>

                            <div class="grid gap-4 text-left sm:grid-cols-2">
                                <div class="space-y-2">
                                    <Label
                                        for="category"
                                        class="pl-1 text-xs font-medium tracking-tight text-slate-400 uppercase"
                                        >Framework Category</Label
                                    >
                                    <Input
                                        id="category"
                                        v-model="form.category"
                                        class="h-12 rounded-xl border-slate-200 focus:ring-emerald-500"
                                        placeholder="e.g. Core Cluster"
                                    />
                                    <InputError
                                        :message="form.errors.category"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label
                                        for="display_order"
                                        class="pl-1 text-xs font-medium tracking-tight text-slate-400 uppercase"
                                        >Sequence Order</Label
                                    >
                                    <Input
                                        id="display_order"
                                        v-model="form.display_order"
                                        type="number"
                                        min="0"
                                        class="h-12 rounded-xl border-slate-200 focus:ring-emerald-500"
                                    />
                                    <InputError
                                        :message="form.errors.display_order"
                                    />
                                </div>
                            </div>

                            <div class="space-y-2 text-left">
                                <Label
                                    for="description"
                                    class="pl-1 text-xs font-medium tracking-tight text-slate-400 uppercase"
                                    >Instructional Definition</Label
                                >
                                <Textarea
                                    id="description"
                                    v-model="form.description"
                                    class="min-h-[120px] rounded-2xl border-slate-200 p-4 focus:ring-emerald-500"
                                    placeholder="Briefly specify the scope of this instructional domain..."
                                />
                                <InputError
                                    :message="form.errors.description"
                                />
                            </div>

                            <div
                                class="flex max-w-xs items-center gap-3 rounded-2xl border bg-slate-50 p-4 shadow-inner"
                            >
                                <Checkbox
                                    id="is_active"
                                    :checked="form.is_active"
                                    @update:checked="
                                        (val: boolean) => (form.is_active = val)
                                    "
                                    class="h-5 w-5 rounded-lg border-2 border-slate-300 data-[state=checked]:border-emerald-600 data-[state=checked]:bg-emerald-600"
                                />
                                <Label
                                    for="is_active"
                                    class="cursor-pointer text-xs font-medium tracking-tight uppercase"
                                    :class="
                                        form.is_active
                                            ? 'text-emerald-600'
                                            : 'text-slate-400'
                                    "
                                >
                                    {{
                                        form.is_active
                                            ? 'Logic Node: Active'
                                            : 'Logic Node: Dormant'
                                    }}
                                </Label>
                            </div>
                        </div>

                        <div
                            class="flex items-center justify-end gap-3 border-t border-slate-50 pt-8"
                        >
                            <Button
                                type="button"
                                variant="outline"
                                class="h-12 rounded-2xl px-8 text-xs font-bold tracking-tight uppercase"
                                as-child
                            >
                                <Link href="/curriculum/learning-areas"
                                    >Discard</Link
                                >
                            </Button>
                            <Button
                                type="submit"
                                :disabled="form.processing"
                                class="h-12 min-w-[200px] rounded-2xl border-0 bg-slate-900 px-8 text-xs font-bold tracking-tight uppercase shadow-xl hover:bg-black"
                            >
                                <Loader2
                                    v-if="form.processing"
                                    class="mr-2 h-4 w-4 animate-spin"
                                />
                                <Plus v-else class="mr-2 h-4 w-4" />
                                Establish Domain
                            </Button>
                        </div>
                    </form>
                </div>

                <!-- Intelligence Sidebar -->
                <div class="space-y-6 lg:col-span-4">
                    <div
                        class="group relative overflow-hidden rounded-xl border-[12px] border-slate-900 bg-slate-950 p-10 text-white shadow-lg"
                    >
                        <div
                            class="absolute -right-8 -bottom-8 transform opacity-10 transition-all duration-700 group-hover:scale-110 group-hover:-rotate-12"
                        >
                            <GraduationCap class="h-48 w-48" />
                        </div>
                        <div class="relative z-10 space-y-6">
                            <h3
                                class="flex items-center gap-3 text-xl font-bold tracking-tighter uppercase"
                            >
                                <ShieldCheck class="h-6 w-6 text-violet-400" />
                                Framework Audit
                            </h3>
                            <p
                                class="text-sm leading-relaxed font-medium text-muted-foreground text-slate-400"
                            >
                                Initialize high-level domains to consolidate
                                subject clusters and academic instructional
                                pathways.
                            </p>
                            <div
                                class="space-y-4 border-t border-white/10 pt-6"
                            >
                                <div
                                    class="flex items-center justify-between text-xs font-medium tracking-tight uppercase"
                                >
                                    <span class="text-slate-500"
                                        >Node Status</span
                                    >
                                    <span class="text-emerald-400"
                                        >Initialized</span
                                    >
                                </div>
                                <div
                                    class="flex items-center justify-between text-xs font-medium tracking-tight uppercase"
                                >
                                    <span class="text-slate-500"
                                        >Registry Tier</span
                                    >
                                    <span class="text-violet-400"
                                        >Level 1 Registry</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.font-bold {
    font-weight: 950;
}
</style>
