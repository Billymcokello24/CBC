<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    Shapes, Plus, Search, Filter, 
    MoreVertical, Eye, Edit2, Trash2, 
    CheckCircle2, ArrowRight, X
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
} from "@/components/ui/dropdown-menu";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog";
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
        form.put(route('curriculum.learning-areas.update', editingArea.value.id), {
            onSuccess: () => {
                showModal.value = false;
                editingArea.value = null;
            },
        });
    } else {
        form.post(route('curriculum.learning-areas.store'), {
            onSuccess: () => {
                showModal.value = false;
            },
        });
    }
};

const deleteArea = (id: number) => {
    if (confirm('Are you sure you want to delete this Learning Area? This will affect associated subjects.')) {
        useForm({}).delete(route('curriculum.learning-areas.destroy', id));
    }
};

</script>

<template>
    <Head title="Learning Areas" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 p-8 font-sans max-w-[1600px] mx-auto animate-in fade-in duration-500">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-1">
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900">Learning Areas</h1>
                    <p class="text-sm font-medium text-slate-500 italic">Major CBC subject categories and structural domains.</p>
                </div>
                
                <div class="flex items-center gap-3">
                    <div class="relative w-64">
                         <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-300 pointer-events-none" />
                         <Input placeholder="Search areas..." class="pl-10 h-12 rounded-xl text-xs border-slate-100 bg-white" />
                    </div>
                    <Button @click="openModal()" class="bg-blue-600 hover:bg-blue-700 h-12 rounded-xl font-bold text-xs uppercase tracking-wider text-white shadow-md">
                        <Plus class="mr-2 h-4 w-4" /> New Learning Area
                    </Button>
                </div>
            </div>

            <!-- List -->
            <div class="grid lg:grid-cols-2 xl:grid-cols-3 gap-8">
                <div v-for="area in learningAreas" :key="area.id" class="group relative p-8 rounded-[2rem] bg-white border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300">
                    <div class="relative z-10 flex items-start justify-between mb-6">
                         <div class="h-14 w-14 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 shadow-sm transition-all group-hover:scale-110">
                            <Shapes class="h-7 w-7" />
                         </div>
                         <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button variant="ghost" size="icon" class="h-10 w-10 rounded-full border border-slate-50 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <MoreVertical class="h-4 w-4 text-slate-400" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="rounded-2xl border-slate-100 shadow-xl p-2 min-w-[160px]">
                                <DropdownMenuItem @click="openModal(area)" class="rounded-xl text-xs font-bold py-3">
                                    <Edit2 class="mr-2 h-3.5 w-3.5" /> Edit Area
                                </DropdownMenuItem>
                                <DropdownMenuItem @click="deleteArea(area.id)" class="rounded-xl text-xs font-bold py-3 text-red-600">
                                    <Trash2 class="mr-2 h-3.5 w-3.5" /> Delete
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <Badge variant="outline" class="text-[8px] font-bold uppercase tracking-wider border-slate-100 bg-slate-50 text-slate-500 px-2 py-0.5">{{ area.code }}</Badge>
                                <Badge variant="outline" class="text-[8px] font-bold uppercase tracking-wider border-blue-100 bg-blue-50 text-blue-600 px-2 py-0.5">{{ area.category || 'Core' }}</Badge>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 tracking-tight leading-tight group-hover:text-blue-600 transition-colors uppercase">{{ area.name }}</h3>
                        </div>
                        <p class="text-[11px] text-slate-400 line-clamp-2 italic font-medium leading-relaxed">{{ area.description || 'No description provided for this learning area.' }}</p>
                    </div>

                    <div class="mt-8 pt-6 border-t border-slate-50 flex items-center justify-between">
                        <div class="flex items-center gap-2 text-slate-400">
                             <CheckCircle2 class="h-4 w-4" />
                             <span class="text-[10px] font-bold uppercase tracking-widest">{{ area.subjects_count || 0 }} Subjects</span>
                        </div>
                        <Link :href="`/curriculum/learning-areas/${area.id}`" class="h-10 w-10 rounded-xl bg-slate-50 group-hover:bg-slate-900 flex items-center justify-center text-slate-400 group-hover:text-white border border-slate-100 group-hover:border-slate-900 transition-all shadow-sm">
                            <ArrowRight class="h-3.5 w-3.5" />
                        </Link>
                    </div>
                </div>

                <!-- Add Placeholder -->
                <div @click="openModal()" class="p-12 rounded-[2rem] border-2 border-dashed border-slate-100 flex flex-col items-center justify-center gap-4 hover:bg-slate-50 hover:border-blue-200 transition-all duration-300 cursor-pointer group min-h-[300px]">
                     <div class="h-16 w-16 rounded-full bg-slate-50 flex items-center justify-center text-slate-300 group-hover:bg-blue-50 group-hover:text-blue-600 transition-all shadow-sm">
                        <Plus class="h-8 w-8" />
                    </div>
                    <div class="text-center">
                        <h4 class="text-lg font-bold text-slate-400 group-hover:text-blue-600 transition-all uppercase tracking-tight">Add Learning Area</h4>
                        <p class="text-[10px] font-bold text-slate-300 uppercase tracking-widest italic font-medium">Create a new structural domain</p>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!learningAreas.length" class="py-24 text-center rounded-[3rem] bg-slate-50 border-2 border-dashed border-slate-100">
                 <Shapes class="h-12 w-12 text-slate-200 mx-auto mb-4" />
                 <h3 class="text-xl font-bold text-slate-400 tracking-tight mb-2 uppercase">No Learning Areas</h3>
                 <p class="text-sm text-slate-400 italic font-medium">Define your curriculum structure starting with learning areas.</p>
                 <Button @click="openModal()" class="mt-8 bg-blue-600 hover:bg-blue-700 px-8 rounded-xl font-bold text-xs uppercase tracking-wider h-12 shadow-md text-white">
                    <Plus class="mr-2 h-4 w-4" /> Add Learning Area
                 </Button>
            </div>

            <!-- Modal -->
            <Dialog v-model:open="showModal">
                <DialogContent class="sm:max-w-[500px] rounded-[2rem] border-slate-100 shadow-2xl">
                    <DialogHeader>
                        <DialogTitle class="text-xl font-bold tracking-tight">{{ editingArea ? 'Edit Learning Area' : 'New Learning Area' }}</DialogTitle>
                        <DialogDescription class="text-xs text-slate-500 italic font-medium">
                            {{ editingArea ? 'Update the details for this structural domain.' : 'Define a new major subject category for the curriculum.' }}
                        </DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="submit" class="grid gap-6 py-4">
                        <div class="grid gap-2">
                            <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Area Name</Label>
                            <Input v-model="form.name" placeholder="e.g. Mathematics & Logic" class="rounded-xl border-slate-100 italic text-sm" required />
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-2">
                                <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Code</Label>
                                <Input v-model="form.code" placeholder="MATH01" class="rounded-xl border-slate-100 font-bold text-xs uppercase tracking-wider" required />
                            </div>
                            <div class="grid gap-2">
                                <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Category</Label>
                                <select v-model="form.category" class="w-full rounded-xl border-slate-100 bg-white px-3 h-10 text-xs font-bold uppercase tracking-wider focus:ring-2 focus:ring-blue-500 transition-all outline-none border" required>
                                    <option value="core">Core Curriculum</option>
                                    <option value="optional">Optional / Elective</option>
                                    <option value="co-curricular">Co-Curricular</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Description</Label>
                            <Textarea v-model="form.description" placeholder="What does this learning area cover?" class="rounded-xl border-slate-100 italic text-[13px] min-h-[100px] focus:ring-blue-500" />
                        </div>

                        <DialogFooter>
                            <Button type="submit" :disabled="form.processing" class="w-full bg-blue-600 hover:bg-blue-700 rounded-xl font-bold text-sm text-white h-12 shadow-lg">
                                {{ form.processing ? 'Saving...' : (editingArea ? 'Update Area' : 'Create Area') }}
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
