<script setup lang="ts">
/* global route */
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Loader2, Save, Layers, ShieldCheck } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    strand: {
        id: number;
        subject_id: number;
        grade_level_id: number;
        name: string;
        code: string;
        description: string | null;
        display_order: number;
        is_active: boolean;
    };
    subjects: Array<{ id: number; name: string }>;
    grades: Array<{ id: number; name: string }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Strands Registry', href: '/curriculum/strands' },
    {
        title: 'Modify Strand',
        href: `/curriculum/strands/${props.strand.id}/edit`,
    },
];

const form = useForm({
    subject_id: String(props.strand.subject_id),
    grade_level_id: String(props.strand.grade_level_id),
    name: props.strand.name,
    code: props.strand.code,
    description: props.strand.description ?? '',
    display_order: props.strand.display_order,
    is_active: props.strand.is_active,
});

const submit = () =>
    form
        .transform((data) => ({
            ...data,
            subject_id: Number(data.subject_id),
            grade_level_id: Number(data.grade_level_id),
            display_order: Number(data.display_order),
            is_active: Boolean(data.is_active),
        }))
        .put(`/curriculum/strands/${props.strand.id}`);
</script>

<template>
    <Head title="Modify Topic Strand" />
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
                    <Link href="/curriculum/strands"
                        ><ArrowLeft class="h-4 w-4"
                    /></Link>
                </Button>
                <div
                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-violet-500/10"
                >
                    <Layers class="h-6 w-6 text-violet-600" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">
                        Modify Topic Strand
                    </h1>
                    <p
                        class="text-xs font-medium tracking-tight text-muted-foreground uppercase"
                    >
                        {{ strand.name }} • NODE_{{ strand.id }}
                    </p>
                </div>
            </div>

            <div class="grid gap-8 lg:grid-cols-12">
                <!-- Main Form Area -->
                <div class="lg:col-span-8">
                    <form
                        @submit.prevent="submit"
                        class="space-y-8 rounded-xl border border-t-8 border-t-violet-500 bg-card p-10 shadow-sm"
                    >
                        <div class="grid gap-8">
                            <div class="grid gap-4 text-left sm:grid-cols-2">
                                <div class="space-y-2">
                                    <Label
                                        class="pl-1 text-xs font-medium tracking-tight text-slate-400 uppercase"
                                        >Target Subject Registry</Label
                                    >
                                    <Select v-model="form.subject_id">
                                        <SelectTrigger
                                            class="h-12 rounded-xl border-slate-200 focus:ring-violet-500"
                                        >
                                            <SelectValue
                                                placeholder="Select subject"
                                            />
                                        </SelectTrigger>
                                        <SelectContent class="font-pulsar">
                                            <SelectItem
                                                v-for="sub in subjects"
                                                :key="sub.id"
                                                :value="String(sub.id)"
                                                >{{ sub.name }}</SelectItem
                                            >
                                        </SelectContent>
                                    </Select>
                                    <InputError
                                        :message="form.errors.subject_id"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label
                                        class="pl-1 text-xs font-medium tracking-tight text-slate-400 uppercase"
                                        >Grade Level Alignment</Label
                                    >
                                    <Select v-model="form.grade_level_id">
                                        <SelectTrigger
                                            class="h-12 rounded-xl border-slate-200 focus:ring-violet-500"
                                        >
                                            <SelectValue
                                                placeholder="Select grade"
                                            />
                                        </SelectTrigger>
                                        <SelectContent class="font-pulsar">
                                            <SelectItem
                                                v-for="grade in grades"
                                                :key="grade.id"
                                                :value="String(grade.id)"
                                                >{{ grade.name }}</SelectItem
                                            >
                                        </SelectContent>
                                    </Select>
                                    <InputError
                                        :message="form.errors.grade_level_id"
                                    />
                                </div>
                            </div>

                            <div class="grid gap-4 text-left sm:grid-cols-2">
                                <div class="space-y-2">
                                    <Label
                                        for="name"
                                        class="pl-1 text-xs font-medium tracking-tight text-slate-400 uppercase"
                                        >Strand Nomenclature</Label
                                    >
                                    <Input
                                        id="name"
                                        v-model="form.name"
                                        class="h-12 rounded-xl border-slate-200 focus:ring-violet-500"
                                        placeholder="e.g. Algebra & Functions"
                                    />
                                    <InputError :message="form.errors.name" />
                                </div>
                                <div class="space-y-2">
                                    <Label
                                        for="code"
                                        class="pl-1 text-xs font-medium tracking-tight text-slate-400 uppercase"
                                        >Registry Hash Code</Label
                                    >
                                    <Input
                                        id="code"
                                        v-model="form.code"
                                        class="h-12 rounded-xl border-slate-200 font-bold uppercase focus:ring-violet-500"
                                        placeholder="e.g. ALG-001"
                                    />
                                    <InputError :message="form.errors.code" />
                                </div>
                            </div>

                            <div class="space-y-2 text-left">
                                <Label
                                    for="display_order"
                                    class="pl-1 text-xs font-medium tracking-tight text-slate-400 uppercase"
                                    >Matrix Priority</Label
                                >
                                <Input
                                    id="display_order"
                                    v-model="form.display_order"
                                    type="number"
                                    min="0"
                                    class="h-12 max-w-[200px] rounded-xl border-slate-200 focus:ring-violet-500"
                                />
                                <InputError
                                    :message="form.errors.display_order"
                                />
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
                                    class="min-h-[120px] rounded-2xl border-slate-200 p-4 focus:ring-violet-500"
                                    placeholder="Specify topical scope and pedagogical boundaries..."
                                />
                                <InputError
                                    :message="form.errors.description"
                                />
                            </div>

                            <div
                                class="flex max-w-xs items-center gap-3 rounded-2xl border bg-slate-50 p-4 shadow-inner transition-all hover:bg-slate-100"
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
                                            ? 'Operational Pulse: Active'
                                            : 'Operational Pulse: Dormant'
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
                                <Link href="/curriculum/strands"
                                    >Discard Changes</Link
                                >
                            </Button>
                            <Button
                                type="submit"
                                :disabled="form.processing"
                                class="h-12 min-w-[200px] rounded-2xl border-0 bg-indigo-600 px-8 text-xs font-bold tracking-tight uppercase shadow-xl shadow-indigo-100 hover:bg-indigo-700"
                            >
                                <Loader2
                                    v-if="form.processing"
                                    class="mr-2 h-4 w-4 animate-spin"
                                />
                                <Save v-else class="mr-2 h-4 w-4" />
                                Finalize Updates
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
                            <Layers class="h-48 w-48" />
                        </div>
                        <div class="relative z-10 space-y-6">
                            <h3
                                class="flex items-center gap-3 text-xl font-bold tracking-tighter uppercase"
                            >
                                <ShieldCheck class="h-6 w-6 text-violet-400" />
                                Mapping Integrity
                            </h3>
                            <p
                                class="text-sm leading-relaxed font-medium text-muted-foreground text-slate-400"
                            >
                                Updates to this instructional node will be
                                synchronized across the academic timetable and
                                assessment matrices for the selected grade
                                level.
                            </p>
                            <div
                                class="space-y-4 border-t border-white/10 pt-6"
                            >
                                <div
                                    class="flex items-center justify-between text-xs font-medium tracking-tight uppercase"
                                >
                                    <span class="text-slate-500"
                                        >Registry Consistency</span
                                    >
                                    <span class="text-emerald-400">SECURE</span>
                                </div>
                                <div
                                    class="flex items-center justify-between text-xs font-medium tracking-tight uppercase"
                                >
                                    <span class="text-slate-500"
                                        >Registry Tier</span
                                    >
                                    <span class="text-violet-400"
                                        >Level 3 Hub</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="space-y-4 rounded-xl border bg-slate-50 p-8 shadow-inner"
                    >
                        <h4
                            class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                        >
                            Global Identifiers
                        </h4>
                        <div class="space-y-2 text-left">
                            <div class="text-xs font-bold text-slate-900">
                                NODE_ID: {{ strand.id }}
                            </div>
                            <div
                                class="text-xs font-bold text-slate-900 uppercase"
                            >
                                UID: STR_{{ strand.code }}_2026
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
