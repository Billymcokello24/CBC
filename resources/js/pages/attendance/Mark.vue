<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { 
    Users, Calendar, ChevronRight, UserCheck, 
    ArrowRight, Search, Filter, Clock, GraduationCap,
    School
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { ref, computed } from 'vue';

const props = defineProps<{
    classes: any[];
}>();

const searchTerm = ref('');
const filteredClasses = computed(() => {
    if (!props.classes) return [];
    if (!searchTerm.value) return props.classes;
    return props.classes.filter(c => 
        c.name?.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
        c.grade_level?.name?.toLowerCase().includes(searchTerm.value.toLowerCase())
    );
});

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Attendance', href: '/attendance' }
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Mark Attendance" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Pulsar Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/10">
                        <UserCheck class="h-6 w-6 text-emerald-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight uppercase">Attendance Tracking</h1>
                        <p class="text-muted-foreground">Select a class to record student presence for today's sessions.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg border bg-emerald-50 text-emerald-700 text-xs font-bold uppercase tracking-widest">
                        <Clock class="h-3.5 w-3.5" /> {{ new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) }}
                    </div>
                </div>
            </div>

            <!-- Pulsar Search & Stats Grid -->
            <div class="grid gap-4 md:grid-cols-4">
                <div class="md:col-span-3 relative">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                    <Input 
                        v-model="searchTerm"
                        placeholder="Search class or grade..." 
                        class="pl-10 h-11"
                    />
                </div>
                <div class="rounded-xl border bg-linear-to-br from-emerald-50 to-emerald-100/50 px-4 py-2 flex items-center justify-between">
                    <span class="text-[10px] font-black text-emerald-800 uppercase tracking-widest leading-tight">Total<br/>Classes</span>
                    <span class="text-2xl font-black text-emerald-600">{{ classes?.length || 0 }}</span>
                </div>
            </div>

            <!-- Classes Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-2">
                <div v-for="cls in filteredClasses" :key="cls.id" 
                    class="group relative bg-card rounded-xl border shadow-sm hover:shadow-md transition-all duration-300 p-6 flex flex-col justify-between min-h-[220px]"
                >
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="h-10 w-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                                <School class="h-5 w-5" />
                            </div>
                            <Badge variant="outline" class="text-[10px] font-black uppercase tracking-widest py-0">
                                {{ cls.grade_level?.name }} · {{ cls.stream?.name }}
                            </Badge>
                        </div>

                        <div>
                            <h3 class="text-lg font-black text-gray-900 group-hover:text-emerald-700 transition-colors">{{ cls.name }}</h3>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">Class Code: {{ cls.code || 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="space-y-4 mt-6">
                        <div class="flex items-center gap-4 py-3 border-t border-gray-50">
                            <div class="flex-1 text-center">
                                <p class="text-sm font-black text-gray-900">42</p>
                                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Students</p>
                            </div>
                            <div class="w-px h-6 bg-gray-100"></div>
                            <div class="flex-1 text-center">
                                <p class="text-sm font-black text-emerald-600">92%</p>
                                <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Attendance</p>
                            </div>
                        </div>

                        <Button class="w-full bg-violet-600 hover:bg-violet-700 text-white rounded-lg h-10 font-black text-xs uppercase" as-child>
                            <Link :href="`/attendance/class/${cls.id}`">
                                Mark Attendance <ArrowRight class="ml-2 h-3.5 w-3.5" />
                            </Link>
                        </Button>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="filteredClasses.length === 0" class="col-span-full py-20 text-center">
                    <div class="inline-flex h-16 w-16 rounded-full bg-gray-50 items-center justify-center mb-4">
                        <Search class="h-8 w-8 text-gray-200" />
                    </div>
                    <h3 class="text-lg font-black text-gray-900 uppercase">No classes found</h3>
                    <p class="text-gray-400 max-w-sm mx-auto mt-1 text-xs font-medium">Try adjusting your search terms or filters.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
