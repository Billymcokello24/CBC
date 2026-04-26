<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    Shapes,
    Plus,
    Search,
    Filter,
    MoreVertical,
    Eye,
    Edit2,
    Trash2,
    CheckCircle2,
    ArrowRight,
    X,
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    learningAreas: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Learning Areas', href: '#' },
];

const showModal = ref(false);
const editingArea = ref<any>(null);

const form = useForm({
    name: '',
    code: '',
    description: '',
    category: 'core',
    display_order: 0,
});

const openModal = (area: any = null) => {
    editingArea.value = area;
    if (area) {
        form.name = area.name;
        form.code = area.code;
        form.description = area.description;
        form.category = area.category || 'core';
        form.display_order = area.display_order;
    } else {
        form.reset();
    }
    showModal.value = true;
};

const submit = () => {
    if (editingArea.value) {
        form.put(
            route('curriculum.learning-areas.update', editingArea.value.id),
            {
                onSuccess: () => {
                    showModal.value = false;
                    editingArea.value = null;
                },
            },
        );
    } else {
        form.post(route('curriculum.learning-areas.store'), {
            onSuccess: () => {
                showModal.value = false;
            },
        });
    }
};

const deleteArea = (id: number) => {
    if (
        confirm(
            'Are you sure you want to delete this Learning Area? This will affect associated subjects.',
        )
    ) {
        useForm({}).delete(route('curriculum.learning-areas.destroy', id));
    }
};
</script>

<template>
    <Head title="Learning Areas" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex h-full max-w-[1600px] flex-1 animate-in flex-col gap-8 p-8 font-sans duration-500 fade-in"
        >
            <!-- Header -->
            <div
                class="flex flex-col justify-between gap-6 md:flex-row md:items-center"
            >
                <div class="space-y-1">
                    <h1
                        class="text-3xl font-bold tracking-tight text-slate-900"
                    >
                        Learning Areas
                    </h1>
                    <p class="text-sm font-medium text-slate-500">
                        Major CBC subject categories and structural domains.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <div class="relative w-64">
                        <Search
                            class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-300"
                        />
                        <Input
                            placeholder="Search areas..."
                            class="h-12 rounded-xl border-slate-100 bg-white pl-10 text-xs"
                        />
                    </div>
                    <Button
                        @click="openModal()"
                        class="h-12 rounded-xl bg-blue-600 text-xs font-bold tracking-wider text-white uppercase shadow-md hover:bg-blue-700"
                    >
                        <Plus class="mr-2 h-4 w-4" /> New Learning Area
                    </Button>
                </div>
            </div>

            <!-- List -->
            <div class="grid gap-8 lg:grid-cols-2 xl:grid-cols-3">
                <div
                    v-for="area in learningAreas"
                    :key="area.id"
                    class="group relative rounded-xl border border-slate-100 bg-white p-8 shadow-sm transition-all duration-300 hover:shadow-md"
                >
                    <div
                        class="relative z-10 mb-6 flex items-start justify-between"
                    >
                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-2xl border border-blue-100 bg-blue-50 text-blue-600 shadow-sm transition-all group-hover:scale-110"
                        >
                            <Shapes class="h-7 w-7" />
                        </div>
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    class="h-10 w-10 rounded-full border border-slate-50 opacity-0 transition-opacity group-hover:opacity-100"
                                >
                                    <MoreVertical
                                        class="h-4 w-4 text-slate-400"
                                    />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent
                                align="end"
                                class="min-w-[160px] rounded-2xl border-slate-100 p-2 shadow-xl"
                            >
                                <DropdownMenuItem
                                    @click="openModal(area)"
                                    class="rounded-xl py-3 text-xs font-bold"
                                >
                                    <Edit2 class="mr-2 h-3.5 w-3.5" /> Edit Area
                                </DropdownMenuItem>
                                <DropdownMenuItem
                                    @click="deleteArea(area.id)"
                                    class="rounded-xl py-3 text-xs font-bold text-red-600"
                                >
                                    <Trash2 class="mr-2 h-3.5 w-3.5" /> Delete
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <div class="mb-1 flex items-center gap-2">
                                <Badge
                                    variant="outline"
                                    class="border-slate-100 bg-slate-50 px-2 py-0.5 text-xs font-bold tracking-wider text-slate-500 uppercase"
                                    >{{ area.code }}</Badge
                                >
                                <Badge
                                    variant="outline"
                                    class="border-blue-100 bg-blue-50 px-2 py-0.5 text-xs font-bold tracking-wider text-blue-600 uppercase"
                                    >{{ area.category || 'Core' }}</Badge
                                >
                            </div>
                            <h3
                                class="text-xl leading-tight font-bold tracking-tight text-slate-900 uppercase transition-colors group-hover:text-blue-600"
                            >
                                {{ area.name }}
                            </h3>
                        </div>
                        <p
                            class="line-clamp-2 text-sm leading-relaxed font-medium text-slate-400"
                        >
                            {{
                                area.description ||
                                'No description provided for this learning area.'
                            }}
                        </p>
                    </div>

                    <div
                        class="mt-8 flex items-center justify-between border-t border-slate-50 pt-6"
                    >
                        <div class="flex items-center gap-2 text-slate-400">
                            <CheckCircle2 class="h-4 w-4" />
                            <span
                                class="text-xs font-bold tracking-tight uppercase"
                                >{{ area.subjects_count || 0 }} Subjects</span
                            >
                        </div>
                        <Link
                            :href="`/curriculum/learning-areas/${area.id}`"
                            class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-100 bg-slate-50 text-slate-400 shadow-sm transition-all group-hover:border-slate-900 group-hover:bg-slate-900 group-hover:text-white"
                        >
                            <ArrowRight class="h-3.5 w-3.5" />
                        </Link>
                    </div>
                </div>

                <!-- Add Placeholder -->
                <div
                    @click="openModal()"
                    class="group flex min-h-[300px] cursor-pointer flex-col items-center justify-center gap-4 rounded-xl border-2 border-dashed border-slate-100 p-12 transition-all duration-300 hover:border-blue-200 hover:bg-slate-50"
                >
                    <div
                        class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-50 text-slate-300 shadow-sm transition-all group-hover:bg-blue-50 group-hover:text-blue-600"
                    >
                        <Plus class="h-8 w-8" />
                    </div>
                    <div class="text-center">
                        <h4
                            class="text-lg font-bold tracking-tight text-slate-400 uppercase transition-all group-hover:text-blue-600"
                        >
                            Add Learning Area
                        </h4>
                        <p
                            class="text-xs font-bold font-medium tracking-tight text-slate-300 uppercase"
                        >
                            Create a new structural domain
                        </p>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-if="!learningAreas.length"
                class="rounded-2xl border-2 border-dashed border-slate-100 bg-slate-50 py-24 text-center"
            >
                <Shapes class="mx-auto mb-4 h-12 w-12 text-slate-200" />
                <h3
                    class="mb-2 text-xl font-bold tracking-tight text-slate-400 uppercase"
                >
                    No Learning Areas
                </h3>
                <p class="text-sm font-medium text-slate-400">
                    Define your curriculum structure starting with learning
                    areas.
                </p>
                <Button
                    @click="openModal()"
                    class="mt-8 h-12 rounded-xl bg-blue-600 px-8 text-xs font-bold tracking-wider text-white uppercase shadow-md hover:bg-blue-700"
                >
                    <Plus class="mr-2 h-4 w-4" /> Add Learning Area
                </Button>
            </div>

            <!-- Modal -->
            <Dialog v-model:open="showModal">
                <DialogContent
                    class="rounded-xl border-slate-100 shadow-lg sm:max-w-[500px]"
                >
                    <DialogHeader>
                        <DialogTitle class="text-xl font-bold tracking-tight">{{
                            editingArea
                                ? 'Edit Learning Area'
                                : 'New Learning Area'
                        }}</DialogTitle>
                        <DialogDescription
                            class="text-xs font-medium text-slate-500"
                        >
                            {{
                                editingArea
                                    ? 'Update the details for this structural domain.'
                                    : 'Define a new major subject category for the curriculum.'
                            }}
                        </DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="submit" class="grid gap-6 py-4">
                        <div class="grid gap-2">
                            <Label
                                class="text-xs font-bold tracking-wider text-slate-400 uppercase"
                                >Area Name</Label
                            >
                            <Input
                                v-model="form.name"
                                placeholder="e.g. Mathematics & Logic"
                                class="rounded-xl border-slate-100 text-sm"
                                required
                            />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-2">
                                <Label
                                    class="text-xs font-bold tracking-wider text-slate-400 uppercase"
                                    >Code</Label
                                >
                                <Input
                                    v-model="form.code"
                                    placeholder="MATH01"
                                    class="rounded-xl border-slate-100 text-xs font-bold tracking-wider uppercase"
                                    required
                                />
                            </div>
                            <div class="grid gap-2">
                                <Label
                                    class="text-xs font-bold tracking-wider text-slate-400 uppercase"
                                    >Category</Label
                                >
                                <select
                                    v-model="form.category"
                                    class="h-10 w-full rounded-xl border border-slate-100 bg-white px-3 text-xs font-bold tracking-wider uppercase transition-all outline-none focus:ring-2 focus:ring-blue-500"
                                    required
                                >
                                    <option value="core">
                                        Core Curriculum
                                    </option>
                                    <option value="optional">
                                        Optional / Elective
                                    </option>
                                    <option value="co-curricular">
                                        Co-Curricular
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label
                                class="text-xs font-bold tracking-wider text-slate-400 uppercase"
                                >Description</Label
                            >
                            <Textarea
                                v-model="form.description"
                                placeholder="What does this learning area cover?"
                                class="min-h-[100px] rounded-xl border-slate-100 text-[13px] focus:ring-blue-500"
                            />
                        </div>

                        <DialogFooter>
                            <Button
                                type="submit"
                                :disabled="form.processing"
                                class="h-12 w-full rounded-xl bg-blue-600 text-sm font-bold text-white shadow-lg hover:bg-blue-700"
                            >
                                {{
                                    form.processing
                                        ? 'Saving...'
                                        : editingArea
                                          ? 'Update Area'
                                          : 'Create Area'
                                }}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Minimal styling */
</style>
