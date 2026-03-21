<script setup lang="ts">
/* global route */
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    ChevronLeft, UserCheck, AlertCircle, Save, 
    UserMinus, UserPlus, Clock, Info, CheckCircle2,
    Calendar, School, CheckSquare, ListChecks
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { 
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue 
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Input } from '@/components/ui/input';
import { ref, onMounted, watch } from 'vue';

const props = defineProps<{
    classroom: any;
    students: any[];
    existing: any;
    activeYear: any;
    activeTerm: any;
}>();

const form = useForm({
    class_id: props.classroom.id,
    academic_year_id: props.activeYear?.id,
    academic_term_id: props.activeTerm?.id,
    attendance_date: new Date().toISOString().split('T')[0],
    attendance: props.students.map(s => ({
        student_id: s.id,
        status: props.existing[s.id]?.status || 'present',
        notes: props.existing[s.id]?.notes || '',
    }))
});

const submit = () => {
    form.post(route('attendance.store'), {
        preserveScroll: true,
        onSuccess: () => {
            // Success handled by Toast.vue globally
        }
    });
};

const updateStatus = (index: number, status: string) => {
    form.attendance[index].status = status;
    updateStats();
};

const markAll = (status: 'present' | 'absent') => {
    form.attendance.forEach(a => a.status = status);
    updateStats();
};

const stats = ref({
    present: 0,
    absent: 0,
    late: 0,
});

const updateStats = () => {
    stats.value.present = form.attendance.filter(a => a.status === 'present').length;
    stats.value.absent = form.attendance.filter(a => a.status === 'absent').length;
    stats.value.late = form.attendance.filter(a => a.status === 'late').length;
};

onMounted(updateStats);

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Attendance', href: '/attendance' },
    { title: props.classroom.name, href: '#' }
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Mark Attendance - ${classroom.name}`" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Pulsar Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/10">
                        <UserCheck class="h-6 w-6 text-emerald-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight uppercase">Class Attendance: {{ classroom.name }}</h1>
                        <div class="flex items-center gap-2 mt-1">
                             <p class="text-muted-foreground text-sm">{{ classroom.grade_level?.name }} · {{ classroom.stream?.name }}</p>
                             <div class="h-1 w-1 rounded-full bg-gray-300"></div>
                             <p class="text-emerald-600 font-bold text-sm">{{ students.length }} Student Population</p>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="hidden sm:flex items-center gap-4 bg-gray-50 border border-gray-100 px-4 py-2 rounded-xl">
                        <div class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            <span class="text-xs font-black text-gray-900 uppercase tracking-tighter">{{ stats.present }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                            <span class="text-xs font-black text-gray-900 uppercase tracking-tighter">{{ stats.absent }}</span>
                        </div>
                    </div>
                    <Button 
                        @click="submit" 
                        :disabled="form.processing"
                        class="bg-violet-600 hover:bg-violet-700 text-white rounded-xl font-black px-8 h-12 uppercase tracking-widest text-xs shadow-lg shadow-violet-100 transition-all active:scale-95"
                    >
                        <Save v-if="!form.processing" class="mr-2 h-4 w-4" /> 
                        <span v-else class="mr-2 h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                        {{ form.processing ? 'Syncing...' : 'Finalize Entry' }}
                    </Button>
                </div>
            </div>

            <!-- Bulk Actions -->
            <div class="flex items-center justify-between bg-card rounded-xl border border-dashed p-4">
                <div class="flex items-center gap-3">
                    <ListChecks class="h-4 w-4 text-violet-600" />
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Batch Protocols</span>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" @click="markAll('present')" class="h-9 px-4 rounded-lg text-[9px] font-black uppercase tracking-widest">
                        Mark All Present
                    </Button>
                    <Button variant="outline" size="sm" @click="markAll('absent')" class="h-9 px-4 rounded-lg text-[9px] font-black uppercase tracking-widest text-rose-600 border-rose-100 hover:bg-rose-50">
                        Mark All Absent
                    </Button>
                </div>
            </div>

            <!-- Attendance Checklist -->
            <div class="rounded-xl border bg-card overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 border-b text-left">
                                <th class="p-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Student Information</th>
                                <th class="p-6 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center w-80">Presence Status</th>
                                <th class="p-6 text-[10px] font-black text-gray-400 uppercase tracking-widest">Notes / Observations</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="(student, index) in students" :key="student.id" 
                                class="hover:bg-gray-50/30 transition-colors group"
                            >
                                <td class="p-6">
                                    <div class="flex items-center gap-4">
                                        <div class="h-10 w-10 rounded-xl bg-violet-600/5 text-violet-600 flex items-center justify-center font-black text-xs uppercase border border-violet-100 shadow-sm">
                                            {{ student.first_name[0] }}{{ student.last_name[0] }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-black text-gray-900 leading-none group-hover:text-violet-700 transition-colors">{{ student.first_name }} {{ student.last_name }}</p>
                                            <p class="text-[10px] font-bold text-gray-400 mt-1 uppercase tracking-tighter">{{ student.admission_number }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-6">
                                    <div class="flex items-center justify-center gap-1.5 p-1.5 rounded-xl bg-gray-50/50 border w-full">
                                        <button 
                                            @click="updateStatus(index, 'present')"
                                            type="button"
                                            :class="[
                                                'flex-1 py-2 rounded-lg font-black text-[9px] uppercase tracking-widest transition-all',
                                                form.attendance[index].status === 'present' 
                                                ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-100' 
                                                : 'text-gray-400 hover:bg-gray-100'
                                            ]"
                                        >
                                            Present
                                        </button>
                                        <button 
                                            @click="updateStatus(index, 'absent')"
                                            type="button"
                                            :class="[
                                                'flex-1 py-2 rounded-lg font-black text-[9px] uppercase tracking-widest transition-all',
                                                form.attendance[index].status === 'absent' 
                                                ? 'bg-rose-600 text-white shadow-lg shadow-rose-100' 
                                                : 'text-gray-400 hover:bg-gray-100'
                                            ]"
                                        >
                                            Absent
                                        </button>
                                        <button 
                                            @click="updateStatus(index, 'late')"
                                            type="button"
                                            :class="[
                                                'flex-1 py-2 rounded-lg font-black text-[9px] uppercase tracking-widest transition-all',
                                                form.attendance[index].status === 'late' 
                                                ? 'bg-amber-500 text-white shadow-lg shadow-amber-100' 
                                                : 'text-gray-400 hover:bg-gray-100'
                                            ]"
                                        >
                                            Late
                                        </button>
                                    </div>
                                </td>
                                <td class="p-6">
                                    <Input 
                                        v-model="form.attendance[index].notes"
                                        placeholder="Add remark..." 
                                        class="h-10 text-xs border-gray-100 bg-gray-50/30 hover:bg-white hover:border-violet-200 focus:bg-white focus:border-violet-400 transition-all rounded-lg"
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer Summary (Pulsar Style) -->
                <div class="bg-gray-50/50 p-6 border-t flex items-center justify-between">
                    <div class="flex items-center gap-8">
                        <div class="flex items-center gap-2">
                            <Calendar class="h-4 w-4 text-violet-400" />
                            <span class="text-xs font-black text-gray-900 uppercase tracking-tight">{{ activeYear?.name }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <Clock class="h-4 w-4 text-violet-400" />
                            <span class="text-xs font-black text-gray-900 uppercase tracking-tight">{{ activeTerm?.name }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Caution Notice -->
            <div class="bg-violet-50 border border-violet-100 rounded-xl p-5 flex items-start gap-4">
                <Info class="h-5 w-5 text-violet-600 shrink-0 mt-0.5" />
                <div>
                    <h4 class="font-black text-violet-900 uppercase text-[10px] tracking-widest mb-1 leading-none">Security Protocol</h4>
                    <p class="text-xs text-violet-800 font-medium opacity-80 leading-relaxed uppercase tracking-tighter">
                        Synchronizing this data will update the academic ledger. verified records are immutable after the current session expires.
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
button {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>
