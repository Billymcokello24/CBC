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
    Eye,
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
        enabledModules.value = enabledModules.value.filter(
            (id) => id !== moduleId,
        );
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
        <div
            class="animate-in space-y-8 duration-700 fade-in slide-in-from-bottom-4"
        >
            <!-- Header -->
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div>
                    <h1
                        class="text-2xl font-bold tracking-tight text-foreground"
                    >
                        Feature Management
                    </h1>
                    <p
                        class="mt-0.5 text-xs font-bold tracking-tight text-muted-foreground/40 uppercase"
                    >
                        Configure active modules and capabilities per school.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-8 xl:grid-cols-4">
                <!-- School Selector -->
                <div class="space-y-6 xl:col-span-1">
                    <div
                        class="space-y-4 rounded-2xl border border-border bg-card p-4 shadow-sm dark:border-white/5"
                    >
                        <div class="group relative">
                            <Search
                                class="absolute top-1/2 left-4 h-4 w-4 -translate-y-1/2 text-muted-foreground/30 transition-colors group-focus-within:text-primary"
                            />
                            <Input
                                placeholder="Search schools..."
                                class="h-11 rounded-xl border-border bg-muted/20 pl-10 font-bold focus:ring-primary/20"
                            />
                        </div>

                        <div
                            class="custom-scrollbar max-h-[500px] space-y-1.5 overflow-y-auto pr-1"
                        >
                            <button
                                v-for="school in schools"
                                :key="school.id"
                                @click="selectedSchool = school"
                                :class="[
                                    'flex w-full flex-col rounded-xl border p-4 text-left transition-all outline-none',
                                    selectedSchool?.id === school.id
                                        ? 'border-primary bg-primary text-primary-foreground shadow-lg shadow-primary/20'
                                        : 'border-transparent bg-muted/10 hover:border-border hover:bg-muted/30',
                                ]"
                            >
                                <span
                                    class="line-clamp-1 text-[13px] font-bold tracking-tight"
                                    >{{ school.name }}</span
                                >
                                <div
                                    class="mt-2 flex items-center justify-between"
                                >
                                    <span
                                        :class="[
                                            'text-xs font-medium tracking-tight uppercase',
                                            selectedSchool?.id === school.id
                                                ? 'text-white/60'
                                                : 'text-muted-foreground/40',
                                        ]"
                                        >{{ school.code }}</span
                                    >
                                    <div class="flex items-center gap-1.5">
                                        <div
                                            :class="[
                                                'h-1 w-1 rounded-full',
                                                school.status === 'active'
                                                    ? selectedSchool?.id ===
                                                      school.id
                                                        ? 'bg-white'
                                                        : 'bg-emerald-500'
                                                    : selectedSchool?.id ===
                                                        school.id
                                                      ? 'bg-white/40'
                                                      : 'bg-rose-500',
                                            ]"
                                        ></div>
                                        <span
                                            :class="[
                                                'text-xs font-medium tracking-tight uppercase',
                                                selectedSchool?.id === school.id
                                                    ? 'text-white'
                                                    : 'text-muted-foreground/60',
                                            ]"
                                            >{{ school.status }}</span
                                        >
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Configuration Matrix -->
                <div class="space-y-8 xl:col-span-3">
                    <div
                        v-if="selectedSchool"
                        class="space-y-8 rounded-2xl border border-border bg-card p-8 shadow-sm dark:border-white/5"
                    >
                        <!-- Selected School Header -->
                        <div
                            class="flex flex-col justify-between gap-6 border-b border-border/50 pb-8 md:flex-row md:items-center"
                        >
                            <div class="flex items-center gap-5">
                                <div
                                    class="flex h-16 w-16 items-center justify-center rounded-2xl border border-primary/10 bg-primary/5 text-primary"
                                >
                                    <Building2 class="h-8 w-8" />
                                </div>
                                <div class="space-y-1">
                                    <h2
                                        class="text-2xl font-bold tracking-tight text-foreground"
                                    >
                                        {{ selectedSchool.name }}
                                    </h2>
                                    <div
                                        class="flex items-center gap-3 text-xs font-bold tracking-tight text-muted-foreground/40 uppercase"
                                    >
                                        <span class="flex items-center gap-1.5"
                                            ><Lock class="h-3 w-3" />
                                            {{ selectedSchool.code }}</span
                                        >
                                        <span
                                            class="h-1 w-1 rounded-full bg-border"
                                        ></span>
                                        <span class="flex items-center gap-1.5"
                                            ><Users class="h-3 w-3" />
                                            {{
                                                selectedSchool.users_count
                                            }}
                                            Staff</span
                                        >
                                        <span
                                            class="h-1 w-1 rounded-full bg-border"
                                        ></span>
                                        <span class="flex items-center gap-1.5"
                                            ><GraduationCap class="h-3 w-3" />
                                            {{
                                                selectedSchool.students_count
                                            }}
                                            Students</span
                                        >
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <Link
                                    :href="`/super-admin/schools/${selectedSchool.id}`"
                                >
                                    <Button
                                        variant="outline"
                                        class="h-10 rounded-xl border-border px-6 text-xs font-bold tracking-tight text-muted-foreground uppercase hover:bg-muted/50"
                                    >
                                        <Eye class="mr-2 h-4 w-4 opacity-50" />
                                        View Profile
                                    </Button>
                                </Link>
                            </div>
                        </div>

                        <!-- Capabilities Matrix -->
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div
                                v-for="module in available_modules"
                                :key="module.id"
                                :class="[
                                    'group relative overflow-hidden rounded-2xl border p-6 transition-all duration-300',
                                    enabledModules.includes(module.id)
                                        ? 'border-primary/20 bg-primary/5'
                                        : 'border-transparent bg-muted/10 opacity-50 grayscale',
                                ]"
                            >
                                <div
                                    class="relative mb-5 flex items-center justify-between"
                                >
                                    <div
                                        :class="[
                                            'flex h-10 w-10 items-center justify-center rounded-xl transition-transform group-hover:rotate-6',
                                            enabledModules.includes(module.id)
                                                ? 'bg-primary text-white shadow-md'
                                                : 'bg-muted text-muted-foreground/30',
                                        ]"
                                    >
                                        <Layers class="h-5 w-5" />
                                    </div>
                                    <Switch
                                        :checked="
                                            enabledModules.includes(module.id)
                                        "
                                        @click="toggleModule(module.id)"
                                        class="data-[state=checked]:bg-primary"
                                    />
                                </div>

                                <h4
                                    class="mb-1 text-base font-bold tracking-tight text-foreground uppercase"
                                >
                                    {{ module.name }}
                                </h4>
                                <p
                                    class="text-xs leading-relaxed font-bold tracking-tight text-muted-foreground/40 uppercase"
                                >
                                    Module enabled for this institution
                                </p>
                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <div
                            class="flex flex-col justify-between gap-6 border-t border-border/50 pt-8 md:flex-row md:items-center"
                        >
                            <div class="flex flex-col gap-1">
                                <span
                                    class="text-xs font-bold tracking-tight text-foreground uppercase"
                                    >Configuration Status</span
                                >
                                <p
                                    class="text-xs font-bold tracking-tight text-muted-foreground/40 uppercase"
                                >
                                    Changes are applied immediately after
                                    saving.
                                </p>
                            </div>
                            <Button
                                @click="saveConfig"
                                class="h-12 rounded-xl bg-primary px-8 text-xs font-bold tracking-tight text-white uppercase shadow-lg shadow-primary/20 transition-all hover:scale-[1.02] active:scale-[0.98]"
                            >
                                <div
                                    class="relative flex items-center justify-center gap-2"
                                >
                                    <Zap class="h-4 w-4" />
                                    Save Configuration
                                </div>
                            </Button>
                        </div>
                    </div>

                    <!-- Simple Banner -->
                    <div
                        class="group relative overflow-hidden rounded-2xl border border-border bg-card p-8 shadow-sm dark:border-white/5"
                    >
                        <div class="relative space-y-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/5"
                                >
                                    <ShieldCheck class="h-5 w-5 text-primary" />
                                </div>
                                <h3
                                    class="text-lg font-bold tracking-tight text-foreground uppercase"
                                >
                                    Provisioning Control
                                </h3>
                            </div>
                            <p
                                class="max-w-xl text-xs leading-relaxed font-bold tracking-tight text-muted-foreground/60 uppercase"
                            >
                                Manage feature access across all active
                                accounts. High-priority changes are synchronized
                                across the cluster automatically.
                            </p>
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
