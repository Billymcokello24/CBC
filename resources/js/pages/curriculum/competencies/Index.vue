<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import {
    ShieldCheck,
    Plus,
    Search,
    Info,
    Edit2,
    Trash2,
    Layers,
    ChevronRight,
    GraduationCap,
    CheckCircle2,
    BookOpen,
    Sparkles,
    Target,
    Users,
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';

const props = defineProps<{
    competencies: Record<string, any[]>;
    grades: any[];
    indicators: Record<string, any[]>;
}>();

const breadcrumbs = [
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Competencies', href: '#' },
];

const activeTab = ref('core');
const competencySearch = ref('');
const showCompetencyModal = ref(false);
const showIndicatorModal = ref(false);
const editingCompetency = ref<any>(null);

const competencyForm = useForm({
    name: '',
    code: '',
    description: '',
    category: 'custom',
    display_order: 1,
    is_active: true,
});

const indicatorForm = useForm({
    competency_id: '',
    grade_level_id: '',
    indicator: '',
    description: '',
    display_order: 1,
    is_active: true,
});

const allCompetencies = computed(() => {
    return Object.values(props.competencies).flat();
});

const filteredCompetencies = computed(() => {
    const list =
        activeTab.value === 'core'
            ? props.competencies['core'] || []
            : props.competencies['custom'] || [];
    if (!competencySearch.value) return list;
    const search = competencySearch.value.toLowerCase();
    return list.filter(
        (c) =>
            c.name.toLowerCase().includes(search) ||
            c.code?.toLowerCase().includes(search),
    );
});

const openCompetencyModal = (comp: any = null) => {
    editingCompetency.value = comp;
    if (comp) {
        competencyForm.name = comp.name;
        competencyForm.code = comp.code;
        competencyForm.description = comp.description;
        competencyForm.category = comp.category;
        competencyForm.display_order = comp.display_order;
        competencyForm.is_active = comp.is_active;
    } else {
        competencyForm.reset();
        competencyForm.category = 'custom';
    }
    showCompetencyModal.value = true;
};

const submitCompetency = () => {
    if (editingCompetency.value) {
        competencyForm.put(
            route('curriculum.competencies.update', editingCompetency.value.id),
            {
                onSuccess: () => {
                    showCompetencyModal.value = false;
                    editingCompetency.value = null;
                },
            },
        );
    } else {
        competencyForm.post(route('curriculum.competencies.store'), {
            onSuccess: () => {
                showCompetencyModal.value = false;
            },
        });
    }
};

const openIndicatorModal = () => {
    indicatorForm.reset();
    showIndicatorModal.value = true;
};

const submitIndicator = () => {
    // Note: We'll add a specific route for this in controller or use resource if available
    indicatorForm.post(route('curriculum.competencies.storeIndicator'), {
        onSuccess: () => {
            showIndicatorModal.value = false;
        },
    });
};

const deleteCompetency = (id: number) => {
    if (confirm('Are you sure you want to delete this competency?')) {
        useForm({}).delete(route('curriculum.competencies.destroy', id));
    }
};

const deleteIndicator = (id: number) => {
    if (confirm('Are you sure you want to delete this indicator?')) {
        useForm({}).delete(
            route('curriculum.competencies.destroyIndicator', id),
        );
    }
};
</script>

<template>
    <Head title="Competencies Management" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="mx-auto flex h-full max-w-[1600px] flex-1 flex-col gap-8 bg-[#f9fafb]/30 p-8 font-sans"
        >
            <!-- Dynamic Header -->
            <div
                class="flex flex-col justify-between gap-6 md:flex-row md:items-center"
            >
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <div
                            class="rounded-xl bg-blue-600 p-2 shadow-lg shadow-blue-200"
                        >
                            <ShieldCheck class="h-6 w-6 text-white" />
                        </div>
                        <h1
                            class="text-3xl font-bold tracking-tight text-slate-900"
                        >
                            Competencies
                        </h1>
                    </div>
                    <p class="max-w-2xl font-medium text-slate-500">
                        Manage CBC core competencies and grade-specific
                        performance indicators across your school.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Button
                        @click="openCompetencyModal()"
                        class="h-12 rounded-2xl bg-blue-600 px-6 text-xs font-bold tracking-[0.1em] text-white uppercase shadow-xl shadow-blue-500/20 transition-all hover:bg-blue-700"
                    >
                        <Plus class="mr-2 h-4 w-4" /> Add Competency
                    </Button>
                </div>
            </div>

            <Tabs v-model="activeTab" class="w-full space-y-8">
                <div
                    class="flex flex-col justify-between gap-4 border-b border-slate-200/60 pb-1 md:flex-row md:items-center"
                >
                    <TabsList class="h-auto gap-8 border-0 bg-transparent p-0">
                        <TabsTrigger
                            value="core"
                            class="rounded-none border-b-2 border-transparent px-0 py-4 text-sm font-medium tracking-tight text-slate-400 uppercase transition-all data-[state=active]:border-blue-600 data-[state=active]:bg-transparent data-[state=active]:text-blue-600 data-[state=active]:shadow-none"
                        >
                            Core CBC Competencies
                        </TabsTrigger>
                        <TabsTrigger
                            value="custom"
                            class="rounded-none border-b-2 border-transparent px-0 py-4 text-sm font-medium tracking-tight text-slate-400 uppercase transition-all data-[state=active]:border-blue-600 data-[state=active]:bg-transparent data-[state=active]:text-blue-600 data-[state=active]:shadow-none"
                        >
                            School Specific
                        </TabsTrigger>
                        <TabsTrigger
                            value="indicators"
                            class="rounded-none border-b-2 border-transparent px-0 py-4 text-sm font-medium tracking-tight text-slate-400 uppercase transition-all data-[state=active]:border-blue-600 data-[state=active]:bg-transparent data-[state=active]:text-blue-600 data-[state=active]:shadow-none"
                        >
                            Grade Indicators
                        </TabsTrigger>
                    </TabsList>

                    <div
                        v-if="activeTab !== 'indicators'"
                        class="relative mb-2 w-full md:w-80"
                    >
                        <Search
                            class="absolute top-1/2 left-4 h-4 w-4 -translate-y-1/2 text-slate-400"
                        />
                        <Input
                            v-model="competencySearch"
                            placeholder="Filter competencies..."
                            class="h-11 rounded-2xl border-slate-100 bg-white pl-11 text-sm font-medium shadow-sm transition-all focus:ring-blue-100"
                        />
                    </div>
                </div>

                <!-- Core & Custom Competencies -->
                <TabsContent value="core" class="mt-0">
                    <div
                        class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
                    >
                        <div
                            v-for="comp in filteredCompetencies"
                            :key="comp.id"
                            class="group relative overflow-hidden rounded-xl border border-slate-100 bg-white p-8 shadow-sm transition-all duration-500 hover:shadow-lg hover:shadow-blue-500/5"
                        >
                            <div
                                class="absolute top-0 right-0 p-8 opacity-[0.03] transition-opacity group-hover:opacity-[0.07]"
                            >
                                <Sparkles class="h-24 w-24 -rotate-12" />
                            </div>

                            <div class="relative z-10 space-y-6">
                                <div class="flex items-start justify-between">
                                    <div
                                        class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 transition-transform duration-500 group-hover:scale-110"
                                    >
                                        <Target class="h-7 w-7" />
                                    </div>
                                    <Badge
                                        variant="outline"
                                        class="rounded-full border-blue-100 bg-blue-50/50 px-3 font-bold text-blue-600"
                                    >
                                        {{ comp.code }}
                                    </Badge>
                                </div>

                                <div class="space-y-2">
                                    <h3
                                        class="text-xl font-bold text-slate-900 transition-colors group-hover:text-blue-600"
                                    >
                                        {{ comp.name }}
                                    </h3>
                                    <p
                                        class="line-clamp-3 text-sm leading-relaxed text-slate-500"
                                    >
                                        {{
                                            comp.description ||
                                            'No description provided for this core competency.'
                                        }}
                                    </p>
                                </div>

                                <div
                                    class="flex items-center justify-between border-t border-slate-50 pt-4"
                                >
                                    <div class="flex -space-x-2">
                                        <!-- Mock avatars of teachers assigned or similar -->
                                        <div
                                            v-for="i in 3"
                                            :key="i"
                                            class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-slate-100 text-xs font-bold text-slate-400"
                                        >
                                            {{ i }}
                                        </div>
                                    </div>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        class="h-10 rounded-xl px-4 text-xs font-bold text-slate-400 uppercase hover:text-blue-600"
                                        @click="openCompetencyModal(comp)"
                                    >
                                        <Edit2 class="mr-2 h-3.5 w-3.5" />
                                        Details
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                </TabsContent>

                <TabsContent value="custom" class="mt-0">
                    <div
                        v-if="filteredCompetencies.length === 0"
                        class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-100 bg-white py-24"
                    >
                        <div class="mb-6 rounded-full bg-slate-50 p-6">
                            <Layers class="h-12 w-12 text-slate-300" />
                        </div>
                        <h3 class="text-xl font-bold text-slate-900">
                            No Custom Competencies
                        </h3>
                        <p
                            class="mt-2 max-w-sm text-center font-medium text-slate-400"
                        >
                            Add school-specific competencies that go beyond the
                            national core curriculum.
                        </p>
                        <Button
                            @click="openCompetencyModal()"
                            variant="outline"
                            class="mt-8 h-12 rounded-2xl border-slate-200 px-8 font-bold hover:bg-slate-50"
                        >
                            <Plus class="mr-2 h-4 w-4" /> Create Custom
                        </Button>
                    </div>

                    <div
                        v-else
                        class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
                    >
                        <!-- Same card as above but for custom -->
                        <div
                            v-for="comp in filteredCompetencies"
                            :key="comp.id"
                            class="group relative rounded-xl border border-slate-100 bg-white p-8 shadow-sm transition-all duration-500 hover:shadow-lg"
                        >
                            <!-- Card content same as core -->
                            <div class="space-y-6">
                                <div class="flex items-start justify-between">
                                    <div
                                        class="flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600"
                                    >
                                        <Target class="h-7 w-7" />
                                    </div>
                                    <Badge
                                        variant="secondary"
                                        class="rounded-full px-3 text-xs font-bold uppercase"
                                    >
                                        Custom
                                    </Badge>
                                </div>
                                <h3 class="text-xl font-bold text-slate-900">
                                    {{ comp.name }}
                                </h3>
                                <p
                                    class="text-sm leading-relaxed text-slate-500"
                                >
                                    {{ comp.description }}
                                </p>
                                <div
                                    class="flex items-center justify-between border-t border-slate-50 pt-4"
                                >
                                    <div class="flex gap-2">
                                        <Button
                                            size="sm"
                                            variant="ghost"
                                            class="h-9 rounded-lg px-3 text-slate-400 hover:text-blue-600"
                                            @click="openCompetencyModal(comp)"
                                        >
                                            <Edit2 class="h-3.5 w-3.5" />
                                        </Button>
                                        <Button
                                            size="sm"
                                            variant="ghost"
                                            class="h-9 rounded-lg px-3 text-slate-400 hover:text-red-600"
                                            @click="deleteCompetency(comp.id)"
                                        >
                                            <Trash2 class="h-3.5 w-3.5" />
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </TabsContent>

                <TabsContent value="indicators" class="mt-0 space-y-8">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="space-y-1">
                            <h2
                                class="text-xl font-bold tracking-tight text-slate-900"
                            >
                                Performance Indicators
                            </h2>
                            <p
                                class="text-xs font-semibold tracking-tight text-slate-400 uppercase"
                            >
                                Expectations per grade Level
                            </p>
                        </div>
                        <Button
                            @click="openIndicatorModal()"
                            variant="outline"
                            class="h-10 rounded-xl border-slate-100 bg-white text-xs font-bold tracking-tight uppercase shadow-sm"
                        >
                            <Plus class="mr-2 h-3 w-3" /> New Indicator
                        </Button>
                    </div>

                    <div
                        v-for="grade in grades"
                        :key="grade.id"
                        class="space-y-4"
                    >
                        <div class="flex items-center gap-4">
                            <div class="h-px flex-1 bg-slate-100"></div>
                            <Badge
                                class="rounded-full bg-slate-900 px-4 py-1.5 text-xs font-bold tracking-tight text-white uppercase"
                                >{{ grade.name }}</Badge
                            >
                            <div class="h-px flex-1 bg-slate-100"></div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div
                                v-for="ind in indicators[grade.id] || []"
                                :key="ind.id"
                                class="group rounded-3xl border border-slate-100 bg-white p-6 shadow-sm transition-all hover:border-blue-200"
                            >
                                <div class="flex items-start gap-4">
                                    <div
                                        class="mt-1 h-2 w-2 shrink-0 rounded-full bg-blue-500 transition-transform group-hover:scale-150"
                                    ></div>
                                    <div class="flex-1 space-y-2">
                                        <div
                                            class="flex items-center justify-between"
                                        >
                                            <span
                                                class="text-xs font-medium tracking-tight text-blue-600 uppercase"
                                                >{{
                                                    ind.competency?.name
                                                }}</span
                                            >
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <Badge
                                                    v-if="ind.display_order"
                                                    variant="outline"
                                                    class="border-slate-100 text-xs font-bold text-slate-400"
                                                    >Order
                                                    {{
                                                        ind.display_order
                                                    }}</Badge
                                                >
                                                <Button
                                                    size="sm"
                                                    variant="ghost"
                                                    class="h-6 w-6 rounded-lg p-0 text-slate-300 transition-colors hover:text-red-600"
                                                    @click="
                                                        deleteIndicator(ind.id)
                                                    "
                                                >
                                                    <Trash2 class="h-3 w-3" />
                                                </Button>
                                            </div>
                                        </div>
                                        <p
                                            class="text-sm leading-snug font-bold text-slate-700"
                                        >
                                            {{ ind.indicator }}
                                        </p>
                                        <p
                                            v-if="ind.description"
                                            class="text-xs leading-relaxed text-slate-400"
                                        >
                                            {{ ind.description }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div
                                v-if="!(indicators[grade.id] || []).length"
                                class="col-span-full rounded-3xl border-2 border-dashed border-slate-100 bg-slate-50/50 py-8 text-center"
                            >
                                <p
                                    class="text-xs font-bold tracking-tight text-slate-300 uppercase"
                                >
                                    No indicators defined for {{ grade.name }}
                                </p>
                            </div>
                        </div>
                    </div>
                </TabsContent>
            </Tabs>

            <!-- Competency Modal -->
            <Dialog v-model:open="showCompetencyModal">
                <DialogContent
                    class="overflow-hidden rounded-xl border-0 p-0 shadow-lg sm:max-w-[500px]"
                >
                    <DialogHeader
                        class="relative overflow-hidden bg-slate-900 p-8 text-white"
                    >
                        <div class="absolute top-0 right-0 p-8 opacity-10">
                            <Target class="h-24 w-24 rotate-12" />
                        </div>
                        <div class="relative z-10">
                            <DialogTitle
                                class="mb-2 text-2xl font-bold tracking-tight"
                            >
                                {{
                                    editingCompetency
                                        ? 'Edit Competency'
                                        : 'New Competency'
                                }}
                            </DialogTitle>
                            <DialogDescription
                                class="font-medium text-slate-400"
                            >
                                {{
                                    editingCompetency
                                        ? 'Modify existing curriculum standards.'
                                        : 'Define a new performance standard for learners.'
                                }}
                            </DialogDescription>
                        </div>
                    </DialogHeader>

                    <form
                        @submit.prevent="submitCompetency"
                        class="space-y-6 p-8"
                    >
                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-2 space-y-2 md:col-span-1">
                                <Label
                                    class="px-1 text-xs font-medium tracking-tight text-slate-400 uppercase"
                                    >Competency Name</Label
                                >
                                <Input
                                    v-model="competencyForm.name"
                                    placeholder="e.g. Digital Literacy"
                                    class="h-12 rounded-2xl border-slate-100 bg-slate-50 font-bold"
                                />
                            </div>
                            <div class="col-span-2 space-y-2 md:col-span-1">
                                <Label
                                    class="px-1 text-xs font-medium tracking-tight text-slate-400 uppercase"
                                    >Code</Label
                                >
                                <Input
                                    v-model="competencyForm.code"
                                    placeholder="e.g. DL"
                                    class="h-12 rounded-2xl border-slate-100 bg-slate-50 px-4 font-bold uppercase"
                                />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label
                                class="px-1 text-xs font-medium tracking-tight text-slate-400 uppercase"
                                >Category</Label
                            >
                            <Select v-model="competencyForm.category">
                                <SelectTrigger
                                    class="h-12 rounded-2xl border-slate-100 bg-slate-50 font-bold"
                                >
                                    <SelectValue
                                        placeholder="Select Category"
                                    />
                                </SelectTrigger>
                                <SelectContent
                                    class="rounded-2xl border-slate-100"
                                >
                                    <SelectItem
                                        value="core"
                                        class="cursor-pointer rounded-xl"
                                        >National Core</SelectItem
                                    >
                                    <SelectItem
                                        value="custom"
                                        class="cursor-pointer rounded-xl"
                                        >School Specific</SelectItem
                                    >
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <Label
                                class="px-1 text-xs font-medium tracking-tight text-slate-400 uppercase"
                                >Detailed Description</Label
                            >
                            <Textarea
                                v-model="competencyForm.description"
                                placeholder="What does this competency encompass?"
                                class="min-h-[100px] rounded-2xl border-slate-100 bg-slate-50 font-medium"
                            />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label
                                    class="px-1 text-xs font-medium tracking-tight text-slate-400 uppercase"
                                    >Order</Label
                                >
                                <Input
                                    type="number"
                                    v-model="competencyForm.display_order"
                                    class="h-12 rounded-2xl border-slate-100 bg-slate-50 font-bold"
                                />
                            </div>
                            <div
                                class="mt-6 flex items-center justify-between rounded-2xl border border-slate-100 bg-slate-50 p-4"
                            >
                                <span
                                    class="text-xs font-medium tracking-tight text-slate-700 uppercase"
                                    >Active</span
                                >
                                <!-- Using a manual check for simplicity since switch import is standard -->
                                <input
                                    type="checkbox"
                                    v-model="competencyForm.is_active"
                                    class="h-5 w-5 cursor-pointer accent-blue-600"
                                />
                            </div>
                        </div>

                        <DialogFooter class="px-0 pt-4">
                            <Button
                                type="submit"
                                :disabled="competencyForm.processing"
                                class="h-14 w-full rounded-2xl bg-blue-600 text-xs font-bold tracking-tight text-white uppercase shadow-xl shadow-blue-500/20 transition-all hover:bg-blue-700"
                            >
                                {{
                                    competencyForm.processing
                                        ? 'Saving...'
                                        : 'Confirm Details'
                                }}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <!-- Indicator Modal -->
            <Dialog v-model:open="showIndicatorModal">
                <DialogContent
                    class="overflow-hidden rounded-xl border-0 p-0 shadow-lg sm:max-w-[500px]"
                >
                    <DialogHeader
                        class="relative overflow-hidden bg-blue-600 p-8 text-white"
                    >
                        <div class="absolute top-0 right-0 p-8 opacity-10">
                            <CheckCircle2 class="h-24 w-24 rotate-12" />
                        </div>
                        <div class="relative z-10">
                            <DialogTitle
                                class="mb-2 text-2xl font-bold tracking-tight"
                                >New Grade Indicator</DialogTitle
                            >
                            <DialogDescription class="font-medium text-blue-100"
                                >Define what learners at this level are expected
                                to demonstrate.</DialogDescription
                            >
                        </div>
                    </DialogHeader>
                    <form
                        @submit.prevent="submitIndicator"
                        class="space-y-6 p-8"
                    >
                        <div class="space-y-2">
                            <Label
                                class="px-1 text-xs font-medium tracking-tight text-slate-400 uppercase"
                                >Select Competency</Label
                            >
                            <Select v-model="indicatorForm.competency_id">
                                <SelectTrigger
                                    class="h-12 rounded-2xl border-slate-100 bg-slate-50 font-bold"
                                >
                                    <SelectValue
                                        placeholder="Which competency is this for?"
                                    />
                                </SelectTrigger>
                                <SelectContent
                                    class="rounded-2xl border-slate-100"
                                >
                                    <SelectItem
                                        v-for="c in allCompetencies"
                                        :key="c.id"
                                        :value="c.id.toString()"
                                        class="cursor-pointer rounded-xl"
                                    >
                                        {{ c.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label
                                class="px-1 text-xs font-medium tracking-tight text-slate-400 uppercase"
                                >Target Grade</Label
                            >
                            <Select v-model="indicatorForm.grade_level_id">
                                <SelectTrigger
                                    class="h-12 rounded-2xl border-slate-100 bg-slate-50 font-bold"
                                >
                                    <SelectValue
                                        placeholder="Select Grade Level"
                                    />
                                </SelectTrigger>
                                <SelectContent
                                    class="rounded-2xl border-slate-100"
                                >
                                    <SelectItem
                                        v-for="g in grades"
                                        :key="g.id"
                                        :value="g.id.toString()"
                                        class="cursor-pointer rounded-xl"
                                    >
                                        {{ g.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label
                                class="px-1 text-xs font-medium tracking-tight text-slate-400 uppercase"
                                >Performance Indicator</Label
                            >
                            <Input
                                v-model="indicatorForm.indicator"
                                placeholder="What should the student be able to do?"
                                class="h-12 rounded-2xl border-slate-100 bg-slate-50 font-bold"
                            />
                        </div>
                        <div class="space-y-2">
                            <Label
                                class="px-1 text-xs font-medium tracking-tight text-slate-400 uppercase"
                                >Technical Description</Label
                            >
                            <Textarea
                                v-model="indicatorForm.description"
                                placeholder="Detailed criteria for assessment..."
                                class="min-h-[100px] rounded-2xl border-slate-100 bg-slate-50 font-medium"
                            />
                        </div>
                        <DialogFooter class="px-0 pt-4">
                            <Button
                                type="submit"
                                :disabled="indicatorForm.processing"
                                class="h-14 w-full rounded-2xl bg-blue-600 text-xs font-bold tracking-tight text-white uppercase shadow-xl shadow-blue-500/20 transition-all hover:bg-blue-700"
                            >
                                {{
                                    indicatorForm.processing
                                        ? 'Recording...'
                                        : 'Create Indicator'
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
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
