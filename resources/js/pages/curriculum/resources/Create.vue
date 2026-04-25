<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Sparkles, ArrowLeft } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import ResourceForm from './Partials/ResourceForm.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    subjects: any[];
    grades: any[];
    folders: any[];
    selectedFolderId?: number | string | null;
}>();

const page = usePage();
const themeColor = computed(() => (page.props.auth as any).school?.theme_color || '#1e40af');
const themeSecondary = computed(() => themeColor.value + '20');
const themeBorder = computed(() => themeColor.value + '30');

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Curriculum', href: '/curriculum' },
    { title: 'Resources', href: '/curriculum/resources' },
    { title: 'Provision Material', href: '#' },
];

const handleSuccess = () => {
    router.visit(route('curriculum.resources.index'));
};

const route = (window as any).route;

</script>

<template>
    <Head title="Provision Resource | Curriculum Gateway" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-10 p-8 font-sans max-w-[1200px] mx-auto animate-in fade-in slide-in-from-bottom-4 duration-700">
            
            <!-- Page Header -->
            <div class="space-y-4 border-b border-slate-100 pb-10">
                <button @click="router.visit(route('curriculum.resources.index'))" class="inline-flex items-center text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-primary transition-colors" :style="{ '--tw-text-hover-opacity': 1, '--hover-color': themeColor }">
                    <ArrowLeft class="mr-1 h-3 w-3" /> Navigation Directory
                </button>
                <div class="flex items-center gap-6">
                    <div class="flex h-16 w-16 items-center justify-center rounded-[1.5rem] bg-primary/10 shadow-sm border border-primary/5" :style="{ backgroundColor: themeSecondary, borderColor: themeBorder }">
                        <Sparkles class="h-8 w-8" :style="{ color: themeColor }" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-black tracking-tight text-slate-900 uppercase italic">Provision Material</h1>
                        <p class="text-sm text-muted-foreground font-medium mt-1">Global Gateway • Curriculum Instructional Support</p>
                    </div>
                </div>
            </div>

            <!-- Enhanced Form Card -->
            <div class="bg-white rounded-[3rem] border border-slate-100 p-12 shadow-sm">
                <ResourceForm 
                    :subjects="subjects" 
                    :grades="grades" 
                    :folders="folders"
                    :selectedFolderId="selectedFolderId"
                    @success="handleSuccess"
                />
            </div>

        </div>
    </AppLayout>
</template>
