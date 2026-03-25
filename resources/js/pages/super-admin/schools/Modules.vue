<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    Layers, 
    Settings, 
    CheckCircle2, 
    Circle, 
    ShieldCheck, 
    Zap, 
    ArrowRight,
    Search,
    Building2,
    Users,
    GraduationCap,
    Lock,
    Eye
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Switch } from '@/components/ui/switch';
import { Separator } from '@/components/ui/separator';
import type { BreadcrumbItem } from '@/types';

interface Module {
    id: string;
    name: string;
}

interface School {
    id: number;
    name: string;
    code: string;
    status: string;
    users_count: number;
    students_count: number;
}

const props = defineProps<{
    schools: School[];
    available_modules: Module[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Global Dashboard', href: '/super-admin/dashboard' },
    { title: 'Tenant Registry', href: '/super-admin/schools' },
    { title: 'Modular Config', href: '/super-admin/schools/modules' },
];

const selectedSchool = ref<School | null>(props.schools[0] || null);

// In a real app, you'd load the enabled modules for the selected school
const enabledModules = ref<string[]>(['finance', 'transport']);

const toggleModule = (moduleId: string) => {
    if (enabledModules.value.includes(moduleId)) {
        enabledModules.value = enabledModules.value.filter(id => id !== moduleId);
    } else {
        enabledModules.value.push(moduleId);
    }
};

const saveConfig = () => {
    // Logic to save the modular configuration
    alert('Modular configuration synchronized across the shard cluster.');
};
</script>

<template>
    <Head title="Modular Configuration Matrix" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-foreground">Feature Management</h1>
                    <p class="text-[10px] font-black text-muted-foreground/40 uppercase tracking-widest mt-0.5">Configure active modules and capabilities per school.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-4 gap-8">
                 <!-- School Selector -->
                 <div class="xl:col-span-1 space-y-6">
                      <div class="rounded-2xl border border-border bg-card p-4 shadow-sm space-y-4 dark:border-white/5">
                           <div class="relative group">
                                <Search class="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/30 group-focus-within:text-primary transition-colors" />
                                <Input 
                                    placeholder="Search schools..." 
                                    class="pl-10 h-11 rounded-xl border-border bg-muted/20 font-bold focus:ring-primary/20"
                                />
                           </div>

                           <div class="space-y-1.5 max-h-[500px] overflow-y-auto pr-1 custom-scrollbar">
                                <button 
                                    v-for="school in schools" 
                                    :key="school.id"
                                    @click="selectedSchool = school"
                                    :class="[
                                         'w-full flex flex-col p-4 rounded-xl text-left transition-all border outline-none',
                                         selectedSchool?.id === school.id 
                                            ? 'bg-primary border-primary text-primary-foreground shadow-lg shadow-primary/20' 
                                            : 'bg-muted/10 border-transparent hover:bg-muted/30 hover:border-border'
                                    ]"
                                >
                                     <span class="text-[13px] font-black tracking-tight line-clamp-1">{{ school.name }}</span>
                                     <div class="flex items-center justify-between mt-2">
                                          <span :class="['text-[9px] font-black uppercase tracking-widest', selectedSchool?.id === school.id ? 'text-white/60' : 'text-muted-foreground/40']">{{ school.code }}</span>
                                          <div class="flex items-center gap-1.5">
                                               <div :class="['h-1 w-1 rounded-full', school.status === 'active' ? (selectedSchool?.id === school.id ? 'bg-white' : 'bg-emerald-500') : (selectedSchool?.id === school.id ? 'bg-white/40' : 'bg-rose-500')]"></div>
                                               <span :class="['text-[8px] font-black uppercase tracking-widest', selectedSchool?.id === school.id ? 'text-white' : 'text-muted-foreground/60']">{{ school.status }}</span>
                                          </div>
                                     </div>
                                </button>
                           </div>
                      </div>
                 </div>

                 <!-- Configuration Matrix -->
                 <div class="xl:col-span-3 space-y-8">
                      <div v-if="selectedSchool" class="rounded-2xl border border-border bg-card p-8 shadow-sm space-y-8 dark:border-white/5">
                           <!-- Selected School Header -->
                           <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-8 border-b border-border/50">
                                <div class="flex items-center gap-5">
                                     <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-primary/5 text-primary border border-primary/10">
                                          <Building2 class="h-8 w-8" />
                                     </div>
                                     <div class="space-y-1">
                                          <h2 class="text-2xl font-black text-foreground tracking-tight">{{ selectedSchool.name }}</h2>
                                          <div class="flex items-center gap-3 text-[10px] font-black text-muted-foreground/40 uppercase tracking-widest">
                                               <span class="flex items-center gap-1.5"><Lock class="h-3 w-3" /> {{ selectedSchool.code }}</span>
                                               <span class="h-1 w-1 rounded-full bg-border"></span>
                                               <span class="flex items-center gap-1.5"><Users class="h-3 w-3" /> {{ selectedSchool.users_count }} Staff</span>
                                               <span class="h-1 w-1 rounded-full bg-border"></span>
                                               <span class="flex items-center gap-1.5"><GraduationCap class="h-3 w-3" /> {{ selectedSchool.students_count }} Students</span>
                                          </div>
                                     </div>
                                </div>
                                <div class="flex items-center gap-3">
                                     <Link :href="`/super-admin/schools/${selectedSchool.id}`">
                                          <Button variant="outline" class="h-10 px-6 rounded-xl border-border font-black text-[10px] uppercase tracking-widest text-muted-foreground hover:bg-muted/50">
                                               <Eye class="mr-2 h-4 w-4 opacity-50" />
                                               View Profile
                                          </Button>
                                     </Link>
                                </div>
                           </div>

                           <!-- Capabilities Matrix -->
                           <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div v-for="module in available_modules" :key="module.id" :class="[
                                     'p-6 rounded-2xl border transition-all duration-300 group relative overflow-hidden',
                                     enabledModules.includes(module.id) 
                                        ? 'bg-primary/5 border-primary/20' 
                                        : 'bg-muted/10 border-transparent grayscale opacity-50'
                                ]">
                                     <div class="flex items-center justify-between mb-5 relative">
                                          <div :class="[
                                               'flex h-10 w-10 items-center justify-center rounded-xl transition-transform group-hover:rotate-6',
                                               enabledModules.includes(module.id) ? 'bg-primary text-white shadow-md' : 'bg-muted text-muted-foreground/30'
                                          ]">
                                               <Layers class="h-5 w-5" />
                                          </div>
                                          <Switch 
                                               :checked="enabledModules.includes(module.id)"
                                               @click="toggleModule(module.id)"
                                               class="data-[state=checked]:bg-primary"
                                          />
                                     </div>

                                     <h4 class="text-base font-black text-foreground tracking-tight mb-1 uppercase">{{ module.name }}</h4>
                                     <p class="text-[9px] font-black text-muted-foreground/40 uppercase tracking-widest leading-relaxed">Module enabled for this institution</p>
                                </div>
                           </div>

                           <!-- Footer Actions -->
                           <div class="pt-8 flex flex-col md:flex-row md:items-center justify-between gap-6 border-t border-border/50">
                                <div class="flex flex-col gap-1">
                                     <span class="text-[10px] font-black text-foreground uppercase tracking-widest">Configuration Status</span>
                                     <p class="text-[9px] font-black text-muted-foreground/40 uppercase tracking-widest">Changes are applied immediately after saving.</p>
                                </div>
                                <Button @click="saveConfig" class="h-12 px-8 rounded-xl bg-primary text-white font-black text-xs uppercase tracking-widest transition-all hover:scale-[1.02] active:scale-[0.98] shadow-lg shadow-primary/20">
                                     <div class="relative flex items-center justify-center gap-2">
                                          <Zap class="h-4 w-4" />
                                          Save Configuration
                                     </div>
                                </Button>
                           </div>
                      </div>

                      <!-- Simple Banner -->
                      <div class="rounded-2xl bg-card border border-border p-8 shadow-sm overflow-hidden relative group dark:border-white/5">
                           <div class="relative space-y-4">
                                <div class="flex items-center gap-3">
                                     <div class="h-10 w-10 rounded-xl bg-primary/5 flex items-center justify-center">
                                         <ShieldCheck class="h-5 w-5 text-primary" />
                                     </div>
                                     <h3 class="text-lg font-black tracking-tight text-foreground uppercase">Provisioning Control</h3>
                                </div>
                                <p class="text-muted-foreground/60 font-black text-[10px] uppercase tracking-widest leading-relaxed max-w-xl">Manage feature access across all active accounts. High-priority changes are synchronized across the cluster automatically.</p>
                           </div>
                      </div>
                 </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.tracking-widest {
    letter-spacing: 0.15em;
}
</style>
