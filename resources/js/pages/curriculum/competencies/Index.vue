<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { 
    ShieldCheck, Plus, Search, Info, Edit2, Trash2, 
    Layers, ChevronRight, GraduationCap, CheckCircle2,
    BookOpen, Sparkles, Target, Users
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { 
    Dialog, DialogContent, DialogDescription, 
    DialogFooter, DialogHeader, DialogTitle 
} from "@/components/ui/dialog";
import {
    Tabs, TabsContent, TabsList, TabsTrigger
} from "@/components/ui/tabs";
import {
    Select, SelectContent, SelectItem, 
    SelectTrigger, SelectValue 
} from "@/components/ui/select";
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
    const list = activeTab.value === 'core' ? (props.competencies['core'] || []) : (props.competencies['custom'] || []);
    if (!competencySearch.value) return list;
    const search = competencySearch.value.toLowerCase();
    return list.filter(c => 
        c.name.toLowerCase().includes(search) || 
        c.code?.toLowerCase().includes(search)
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
        competencyForm.put(route('curriculum.competencies.update', editingCompetency.value.id), {
            onSuccess: () => {
                showCompetencyModal.value = false;
                editingCompetency.value = null;
            }
        });
    } else {
        competencyForm.post(route('curriculum.competencies.store'), {
            onSuccess: () => {
                showCompetencyModal.value = false;
            }
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
        }
    });
};

const deleteCompetency = (id: number) => {
    if (confirm('Are you sure you want to delete this competency?')) {
        useForm({}).delete(route('curriculum.competencies.destroy', id));
    }
};

const deleteIndicator = (id: number) => {
    if (confirm('Are you sure you want to delete this indicator?')) {
        useForm({}).delete(route('curriculum.competencies.destroyIndicator', id));
    }
};

</script>

<template>
    <Head title="Competencies Management" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 p-8 font-sans max-w-[1600px] mx-auto bg-[#f9fafb]/30">
            <!-- Dynamic Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-blue-600 rounded-xl shadow-lg shadow-blue-200">
                            <ShieldCheck class="h-6 w-6 text-white" />
                        </div>
                        <h1 class="text-3xl font-black tracking-tight text-slate-900">Competencies</h1>
                    </div>
                    <p class="text-slate-500 font-medium max-w-2xl">
                        Manage CBC core competencies and grade-specific performance indicators across your school.
                    </p>
                </div>
                
                <div class="flex items-center gap-3">
                    <Button @click="openCompetencyModal()" class="h-12 px-6 rounded-2xl bg-blue-600 hover:bg-blue-700 font-black text-xs text-white shadow-xl shadow-blue-500/20 transition-all uppercase tracking-[0.1em]">
                        <Plus class="mr-2 h-4 w-4" /> Add Competency
                    </Button>
                </div>
            </div>

            <Tabs v-model="activeTab" class="w-full space-y-8">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-200/60 pb-1">
                    <TabsList class="h-auto p-0 bg-transparent border-0 gap-8">
                        <TabsTrigger value="core" class="px-0 py-4 rounded-none border-b-2 border-transparent data-[state=active]:border-blue-600 data-[state=active]:bg-transparent data-[state=active]:shadow-none text-sm font-black uppercase tracking-widest text-slate-400 data-[state=active]:text-blue-600 transition-all">
                            Core CBC Competencies
                        </TabsTrigger>
                        <TabsTrigger value="custom" class="px-0 py-4 rounded-none border-b-2 border-transparent data-[state=active]:border-blue-600 data-[state=active]:bg-transparent data-[state=active]:shadow-none text-sm font-black uppercase tracking-widest text-slate-400 data-[state=active]:text-blue-600 transition-all">
                            School Specific
                        </TabsTrigger>
                        <TabsTrigger value="indicators" class="px-0 py-4 rounded-none border-b-2 border-transparent data-[state=active]:border-blue-600 data-[state=active]:bg-transparent data-[state=active]:shadow-none text-sm font-black uppercase tracking-widest text-slate-400 data-[state=active]:text-blue-600 transition-all">
                            Grade Indicators
                        </TabsTrigger>
                    </TabsList>
                    
                    <div v-if="activeTab !== 'indicators'" class="relative w-full md:w-80 mb-2">
                        <Search class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
                        <Input v-model="competencySearch" placeholder="Filter competencies..." class="h-11 pl-11 rounded-2xl border-slate-100 bg-white shadow-sm focus:ring-blue-100 transition-all font-medium text-sm" />
                    </div>
                </div>

                <!-- Core & Custom Competencies -->
                <TabsContent value="core" class="mt-0">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="comp in filteredCompetencies" :key="comp.id" class="group relative bg-white rounded-[2.5rem] border border-slate-100 p-8 shadow-sm hover:shadow-2xl hover:shadow-blue-500/5 transition-all duration-500 overflow-hidden">
                            <div class="absolute top-0 right-0 p-8 opacity-[0.03] group-hover:opacity-[0.07] transition-opacity">
                                <Sparkles class="h-24 w-24 -rotate-12" />
                            </div>
                            
                            <div class="relative z-10 space-y-6">
                                <div class="flex items-start justify-between">
                                    <div class="h-14 w-14 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 group-hover:scale-110 transition-transform duration-500">
                                        <Target class="h-7 w-7" />
                                    </div>
                                    <Badge variant="outline" class="rounded-full bg-blue-50/50 border-blue-100 text-blue-600 font-bold px-3">
                                        {{ comp.code }}
                                    </Badge>
                                </div>
                                
                                <div class="space-y-2">
                                    <h3 class="text-xl font-black text-slate-900 group-hover:text-blue-600 transition-colors">{{ comp.name }}</h3>
                                    <p class="text-sm text-slate-500 leading-relaxed line-clamp-3">
                                        {{ comp.description || 'No description provided for this core competency.' }}
                                    </p>
                                </div>

                                <div class="pt-4 flex items-center justify-between border-t border-slate-50">
                                    <div class="flex -space-x-2">
                                        <!-- Mock avatars of teachers assigned or similar -->
                                        <div v-for="i in 3" :key="i" class="h-8 w-8 rounded-full border-2 border-white bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-400">
                                            {{ i }}
                                        </div>
                                    </div>
                                    <Button variant="ghost" size="sm" class="h-10 px-4 rounded-xl text-xs font-black uppercase text-slate-400 hover:text-blue-600" @click="openCompetencyModal(comp)">
                                        <Edit2 class="h-3.5 w-3.5 mr-2" /> Details
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                </TabsContent>

                <TabsContent value="custom" class="mt-0">
                    <div v-if="filteredCompetencies.length === 0" class="flex flex-col items-center justify-center py-24 bg-white rounded-[3rem] border-2 border-dashed border-slate-100">
                        <div class="p-6 bg-slate-50 rounded-full mb-6">
                            <Layers class="h-12 w-12 text-slate-300" />
                        </div>
                        <h3 class="text-xl font-black text-slate-900">No Custom Competencies</h3>
                        <p class="text-slate-400 font-medium max-w-sm text-center mt-2">
                            Add school-specific competencies that go beyond the national core curriculum.
                        </p>
                        <Button @click="openCompetencyModal()" variant="outline" class="mt-8 h-12 rounded-2xl px-8 border-slate-200 font-bold hover:bg-slate-50">
                            <Plus class="mr-2 h-4 w-4" /> Create Custom
                        </Button>
                    </div>
                    
                    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Same card as above but for custom -->
                        <div v-for="comp in filteredCompetencies" :key="comp.id" class="group relative bg-white rounded-[2.5rem] border border-slate-100 p-8 shadow-sm hover:shadow-2xl transition-all duration-500">
                             <!-- Card content same as core -->
                             <div class="space-y-6">
                                <div class="flex items-start justify-between">
                                    <div class="h-14 w-14 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                                        <Target class="h-7 w-7" />
                                    </div>
                                    <Badge variant="secondary" class="rounded-full font-bold px-3 uppercase text-[10px]">
                                        Custom
                                    </Badge>
                                </div>
                                <h3 class="text-xl font-black text-slate-900">{{ comp.name }}</h3>
                                <p class="text-sm text-slate-500 leading-relaxed">{{ comp.description }}</p>
                                <div class="pt-4 flex items-center justify-between border-t border-slate-50">
                                    <div class="flex gap-2">
                                        <Button size="sm" variant="ghost" class="h-9 px-3 rounded-lg text-slate-400 hover:text-blue-600" @click="openCompetencyModal(comp)">
                                            <Edit2 class="h-3.5 w-3.5" />
                                        </Button>
                                        <Button size="sm" variant="ghost" class="h-9 px-3 rounded-lg text-slate-400 hover:text-red-600" @click="deleteCompetency(comp.id)">
                                            <Trash2 class="h-3.5 w-3.5" />
                                        </Button>
                                    </div>
                                </div>
                             </div>
                        </div>
                    </div>
                </TabsContent>

                <TabsContent value="indicators" class="mt-0 space-y-8">
                    <div class="flex items-center justify-between mb-4">
                        <div class="space-y-1">
                            <h2 class="text-xl font-black text-slate-900 tracking-tight">Performance Indicators</h2>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest">Expectations per grade Level</p>
                        </div>
                        <Button @click="openIndicatorModal()" variant="outline" class="h-10 rounded-xl border-slate-100 bg-white font-black text-[10px] uppercase tracking-widest shadow-sm">
                            <Plus class="mr-2 h-3 w-3" /> New Indicator
                        </Button>
                    </div>

                    <div v-for="grade in grades" :key="grade.id" class="space-y-4">
                        <div class="flex items-center gap-4">
                            <div class="h-px flex-1 bg-slate-100"></div>
                            <Badge class="bg-slate-900 text-white font-black px-4 py-1.5 rounded-full text-xs uppercase tracking-widest">{{ grade.name }}</Badge>
                            <div class="h-px flex-1 bg-slate-100"></div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-for="ind in indicators[grade.id] || []" :key="ind.id" class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm hover:border-blue-200 transition-all group">
                                <div class="flex items-start gap-4">
                                    <div class="mt-1 h-2 w-2 rounded-full bg-blue-500 shrink-0 group-hover:scale-150 transition-transform"></div>
                                    <div class="space-y-2 flex-1">
                                        <div class="flex items-center justify-between">
                                            <span class="text-[10px] font-black uppercase tracking-widest text-blue-600">{{ ind.competency?.name }}</span>
                                            <div class="flex items-center gap-2">
                                                <Badge v-if="ind.display_order" variant="outline" class="text-[9px] font-bold border-slate-100 text-slate-400">Order {{ ind.display_order }}</Badge>
                                                <Button size="sm" variant="ghost" class="h-6 w-6 p-0 rounded-lg text-slate-300 hover:text-red-600 transition-colors" @click="deleteIndicator(ind.id)">
                                                    <Trash2 class="h-3 w-3" />
                                                </Button>
                                            </div>
                                        </div>
                                        <p class="text-sm font-bold text-slate-700 leading-snug">{{ ind.indicator }}</p>
                                        <p v-if="ind.description" class="text-xs text-slate-400 leading-relaxed">{{ ind.description }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div v-if="!(indicators[grade.id] || []).length" class="col-span-full py-8 text-center bg-slate-50/50 rounded-3xl border-2 border-dashed border-slate-100">
                                <p class="text-[10px] font-black text-slate-300 uppercase tracking-[0.2em]">No indicators defined for {{ grade.name }}</p>
                            </div>
                        </div>
                    </div>
                </TabsContent>
            </Tabs>

            <!-- Competency Modal -->
            <Dialog v-model:open="showCompetencyModal">
                <DialogContent class="sm:max-w-[500px] rounded-[2.5rem] p-0 overflow-hidden border-0 shadow-2xl">
                    <DialogHeader class="p-8 bg-slate-900 text-white relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-8 opacity-10">
                            <Target class="h-24 w-24 rotate-12" />
                        </div>
                        <div class="relative z-10">
                            <DialogTitle class="text-2xl font-black tracking-tight mb-2">
                                {{ editingCompetency ? 'Edit Competency' : 'New Competency' }}
                            </DialogTitle>
                            <DialogDescription class="text-slate-400 font-medium">
                                {{ editingCompetency ? 'Modify existing curriculum standards.' : 'Define a new performance standard for learners.' }}
                            </DialogDescription>
                        </div>
                    </DialogHeader>
                    
                    <form @submit.prevent="submitCompetency" class="p-8 space-y-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2 col-span-2 md:col-span-1">
                                <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">Competency Name</Label>
                                <Input v-model="competencyForm.name" placeholder="e.g. Digital Literacy" class="h-12 rounded-2xl border-slate-100 bg-slate-50 font-bold" />
                            </div>
                            <div class="space-y-2 col-span-2 md:col-span-1">
                                <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">Code</Label>
                                <Input v-model="competencyForm.code" placeholder="e.g. DL" class="h-12 rounded-2xl border-slate-100 bg-slate-50 font-black uppercase px-4" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">Category</Label>
                            <Select v-model="competencyForm.category">
                                <SelectTrigger class="h-12 rounded-2xl border-slate-100 bg-slate-50 font-bold">
                                    <SelectValue placeholder="Select Category" />
                                </SelectTrigger>
                                <SelectContent class="rounded-2xl border-slate-100">
                                    <SelectItem value="core" class="rounded-xl cursor-pointer">National Core</SelectItem>
                                    <SelectItem value="custom" class="rounded-xl cursor-pointer">School Specific</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">Detailed Description</Label>
                            <Textarea v-model="competencyForm.description" placeholder="What does this competency encompass?" class="min-h-[100px] rounded-2xl border-slate-100 bg-slate-50 font-medium" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">Order</Label>
                                <Input type="number" v-model="competencyForm.display_order" class="h-12 rounded-2xl border-slate-100 bg-slate-50 font-bold" />
                            </div>
                            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100 mt-6">
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-700">Active</span>
                                <!-- Using a manual check for simplicity since switch import is standard -->
                                <input type="checkbox" v-model="competencyForm.is_active" class="h-5 w-5 accent-blue-600 cursor-pointer" />
                            </div>
                        </div>

                        <DialogFooter class="pt-4 px-0">
                            <Button type="submit" :disabled="competencyForm.processing" class="w-full bg-blue-600 hover:bg-blue-700 rounded-2xl font-black text-xs text-white h-14 shadow-xl shadow-blue-500/20 transition-all uppercase tracking-[0.2em]">
                                {{ competencyForm.processing ? 'Saving...' : 'Confirm Details' }}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <!-- Indicator Modal -->
            <Dialog v-model:open="showIndicatorModal">
                <DialogContent class="sm:max-w-[500px] rounded-[2.5rem] p-0 overflow-hidden border-0 shadow-2xl">
                    <DialogHeader class="p-8 bg-blue-600 text-white relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-8 opacity-10">
                            <CheckCircle2 class="h-24 w-24 rotate-12" />
                        </div>
                        <div class="relative z-10">
                            <DialogTitle class="text-2xl font-black tracking-tight mb-2">New Grade Indicator</DialogTitle>
                            <DialogDescription class="text-blue-100 font-medium">Define what learners at this level are expected to demonstrate.</DialogDescription>
                        </div>
                    </DialogHeader>
                    <form @submit.prevent="submitIndicator" class="p-8 space-y-6">
                        <div class="space-y-2">
                            <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">Select Competency</Label>
                            <Select v-model="indicatorForm.competency_id">
                                <SelectTrigger class="h-12 rounded-2xl border-slate-100 bg-slate-50 font-bold">
                                    <SelectValue placeholder="Which competency is this for?" />
                                </SelectTrigger>
                                <SelectContent class="rounded-2xl border-slate-100">
                                    <SelectItem v-for="c in allCompetencies" :key="c.id" :value="c.id.toString()" class="rounded-xl cursor-pointer">
                                        {{ c.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">Target Grade</Label>
                            <Select v-model="indicatorForm.grade_level_id">
                                <SelectTrigger class="h-12 rounded-2xl border-slate-100 bg-slate-50 font-bold">
                                    <SelectValue placeholder="Select Grade Level" />
                                </SelectTrigger>
                                <SelectContent class="rounded-2xl border-slate-100">
                                    <SelectItem v-for="g in grades" :key="g.id" :value="g.id.toString()" class="rounded-xl cursor-pointer">
                                        {{ g.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">Performance Indicator</Label>
                            <Input v-model="indicatorForm.indicator" placeholder="What should the student be able to do?" class="h-12 rounded-2xl border-slate-100 bg-slate-50 font-bold" />
                        </div>
                        <div class="space-y-2">
                            <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-1">Technical Description</Label>
                            <Textarea v-model="indicatorForm.description" placeholder="Detailed criteria for assessment..." class="min-h-[100px] rounded-2xl border-slate-100 bg-slate-50 font-medium" />
                        </div>
                        <DialogFooter class="pt-4 px-0">
                            <Button type="submit" :disabled="indicatorForm.processing" class="w-full bg-blue-600 hover:bg-blue-700 rounded-2xl font-black text-xs text-white h-14 shadow-xl shadow-blue-500/20 transition-all uppercase tracking-[0.2em]">
                                {{ indicatorForm.processing ? 'Recording...' : 'Create Indicator' }}
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
