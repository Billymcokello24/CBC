<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import { 
    User, Mail, Phone, Shield, 
    ArrowLeft, Save, Loader2, GraduationCap,
    CheckCircle2, AlertTriangle, X, Search
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { 
    Dialog, 
    DialogContent,
} from '@/components/ui/dialog';

const props = defineProps<{
    parent: any;
    students: any[];
    selectedStudentIds: number[];
}>();

const studentSearch = ref('');
const confirmOpen = ref(false);
const successOpen = ref(false);

const form = useForm({
    first_name: props.parent.first_name,
    last_name: props.parent.last_name,
    email: props.parent.email,
    phone: props.parent.phone,
    id_number: props.parent.id_number,
    gender: props.parent.gender,
    relationship_type: props.parent.relationship_type || 'Father',
    student_ids: [...props.selectedStudentIds],
    password: '',
    password_confirmation: '',
});

const filteredStudents = computed(() => {
    if (!studentSearch.value) return props.students.slice(0, 6);
    const search = studentSearch.value.toLowerCase();
    return props.students.filter(s => 
        s.first_name.toLowerCase().includes(search) || 
        s.last_name.toLowerCase().includes(search) || 
        s.admission_number.toLowerCase().includes(search)
    ).slice(0, 8);
});

const toggleStudent = (id: number) => {
    const index = form.student_ids.indexOf(id);
    if (index > -1) {
        form.student_ids.splice(index, 1);
    } else {
        form.student_ids.push(id);
    }
};

const getStudentName = (id: number) => {
    const student = props.students.find(s => s.id === id);
    return student ? `${student.first_name} ${student.last_name}` : 'Unknown';
};

const submit = () => {
    confirmOpen.value = true;
};

const confirmSubmit = () => {
    confirmOpen.value = false;
    form.put(`/parents/${props.parent.id}`, {
        onSuccess: () => {
            successOpen.value = true;
        },
    });
};

const breadcrumbs = [
    { title: 'User Directory', href: '/staffs/directory' },
    { title: 'Parents', href: '/parents' },
    { title: 'Edit Parent Account', href: '#' },
];
</script>

<template>
    <AppLayout title="Edit Parent Account" :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-col p-6 lg:p-10 bg-slate-50/50">
            <!-- Header Section -->
            <div class="flex flex-col gap-8 mb-12">
                <div class="flex flex-wrap items-center justify-between gap-6">
                    <div class="space-y-2">
                        <Link href="/parents" class="inline-flex items-center text-xs font-bold text-muted-foreground hover:text-blue-600 transition-colors gap-1.5 group uppercase tracking-widest pl-1">
                            <ArrowLeft class="h-3.5 w-3.5 transition-transform group-hover:-translate-x-1" />
                            Back to Registry
                        </Link>
                        <h1 class="text-4xl font-black text-slate-900 tracking-tight flex items-center gap-4 italic uppercase">
                            <span class="h-12 w-2 bg-blue-600 rounded-full"></span>
                            Edit Parent Account
                        </h1>
                        <p class="text-sm text-muted-foreground font-medium max-w-2xl pl-1">
                            Update account credentials and linked dependents for <span class="text-blue-600 font-bold decoration-blue-200 underline underline-offset-4">{{ parent.first_name }} {{ parent.last_name }}</span>.
                        </p>
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="grid gap-10">
                <div class="grid gap-10 lg:grid-cols-12">
                    <!-- Left: Profile Info -->
                    <div class="lg:col-span-7 space-y-10">
                        <div class="overflow-hidden rounded-[2.5rem] border border-slate-200 bg-white shadow-xl shadow-slate-200/40 transition-all hover:shadow-2xl hover:shadow-indigo-600/10 duration-500">
                            <div class="border-b border-slate-100 bg-slate-50/30 px-10 py-7">
                                <h2 class="text-lg font-bold text-slate-900 flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-200">
                                        <User class="h-5 w-5" />
                                    </div>
                                    Guardian Profile
                                </h2>
                            </div>
                            <div class="p-10 space-y-8">
                                <div class="grid gap-8 md:grid-cols-2">
                                    <div class="space-y-3">
                                        <Label for="first_name" class="text-xs font-black text-slate-500 uppercase tracking-widest ml-1">First Name</Label>
                                        <Input id="first_name" v-model="form.first_name" class="h-14 rounded-2xl border-slate-200 bg-slate-50/50 px-5 focus:ring-2 focus:ring-indigo-600/10 transition-all font-bold text-slate-900" required />
                                        <InputError :message="form.errors.first_name" />
                                    </div>
                                    <div class="space-y-3">
                                        <Label for="last_name" class="text-xs font-black text-slate-500 uppercase tracking-widest ml-1">Last Name</Label>
                                        <Input id="last_name" v-model="form.last_name" class="h-14 rounded-2xl border-slate-200 bg-slate-50/50 px-5 focus:ring-2 focus:ring-indigo-600/10 transition-all font-bold text-slate-900" required />
                                        <InputError :message="form.errors.last_name" />
                                    </div>
                                    <div class="space-y-3">
                                        <Label for="email" class="text-xs font-black text-slate-500 uppercase tracking-widest ml-1">Email Address</Label>
                                        <Input id="email" v-model="form.email" type="email" class="h-14 rounded-2xl border-slate-200 bg-slate-50/50 px-5 focus:ring-2 focus:ring-indigo-600/10 transition-all font-bold text-slate-900" required />
                                        <InputError :message="form.errors.email" />
                                    </div>
                                    <div class="space-y-3">
                                        <Label for="phone" class="text-xs font-black text-slate-500 uppercase tracking-widest ml-1">Phone Number</Label>
                                        <Input id="phone" v-model="form.phone" class="h-14 rounded-2xl border-slate-200 bg-slate-50/50 px-5 focus:ring-2 focus:ring-indigo-600/10 transition-all font-bold text-slate-900" required />
                                        <InputError :message="form.errors.phone" />
                                    </div>
                                    <div class="space-y-3">
                                        <Label for="id_number" class="text-xs font-black text-slate-500 uppercase tracking-widest ml-1">ID / Passport Number</Label>
                                        <Input id="id_number" v-model="form.id_number" class="h-14 rounded-2xl border-slate-200 bg-slate-50/50 px-5 focus:ring-2 focus:ring-indigo-600/10 transition-all font-bold text-slate-900" />
                                        <InputError :message="form.errors.id_number" />
                                    </div>
                                    <div class="space-y-3">
                                        <Label for="gender" class="text-xs font-black text-slate-500 uppercase tracking-widest ml-1">Gender</Label>
                                        <select id="gender" v-model="form.gender" class="h-14 w-full rounded-2xl border-slate-200 bg-slate-50/50 px-5 text-sm font-bold focus:ring-2 focus:ring-indigo-600/10 appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2364748b%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E')] bg-[length:18px] bg-[right_1.25rem_center] bg-no-repeat transition-all">
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="overflow-hidden rounded-[2.5rem] border border-slate-200 bg-white shadow-xl shadow-slate-200/40 transition-all hover:shadow-2xl hover:shadow-indigo-600/10 duration-500 p-1 bg-[linear-gradient(135deg,#f8faff_0%,#ffffff_100%)]">
                            <div class="px-10 py-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
                                <div class="space-y-1">
                                    <h3 class="text-lg font-extrabold text-slate-900 flex items-center gap-2 italic">
                                        <Shield class="h-5 w-5 text-blue-600" />
                                        Update Password
                                    </h3>
                                    <p class="text-xs text-muted-foreground font-medium">Leave blank to keep existing password</p>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 flex-1 max-w-lg">
                                    <Input v-model="form.password" type="password" placeholder="New Password" class="h-12 rounded-xl border-slate-200 bg-white px-4 font-bold text-sm" />
                                    <Input v-model="form.password_confirmation" type="password" placeholder="Confirm" class="h-12 rounded-xl border-slate-200 bg-white px-4 font-bold text-sm" />
                                </div>
                            </div>
                            <div v-if="form.errors.password" class="px-10 pb-4">
                                <InputError :message="form.errors.password" />
                            </div>
                        </div>
                    </div>

                    <!-- Right: Dependents -->
                    <div class="lg:col-span-5 space-y-8">
                        <div class="overflow-hidden rounded-[2.5rem] border border-slate-200 bg-white shadow-xl shadow-slate-200/40 p-10 space-y-8">
                            <div class="space-y-2">
                                <h2 class="text-xl font-black text-slate-900 flex items-center gap-3 italic uppercase">
                                    <GraduationCap class="h-6 w-6 text-blue-600" />
                                    Dependents
                                </h2>
                                <p class="text-xs text-muted-foreground font-bold uppercase tracking-widest pl-1">Link learner accounts to this guardian</p>
                            </div>

                            <div class="space-y-4">
                                <Label class="text-xs font-black text-slate-500 uppercase tracking-widest ml-1">Guardian Relationship</Label>
                                <div class="grid grid-cols-2 gap-3">
                                    <button 
                                        type="button"
                                        v-for="rel in ['Father', 'Mother', 'Sponsor', 'Guardian', 'Other']" 
                                        :key="rel"
                                        @click="form.relationship_type = rel"
                                        class="h-12 rounded-xl border-2 font-bold text-sm transition-all flex items-center justify-center gap-2"
                                        :class="form.relationship_type === rel ? 'border-blue-600 bg-blue-50 text-blue-600' : 'border-slate-100 bg-slate-50/50 text-slate-500 hover:border-slate-200'"
                                    >
                                        {{ rel }}
                                    </button>
                                </div>
                            </div>

                            <div class="space-y-6 pt-4 border-t border-slate-100">
                                <div class="relative">
                                    <Search class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
                                    <Input 
                                        v-model="studentSearch" 
                                        placeholder="Search learner by name or admission..." 
                                        class="h-12 pl-11 rounded-xl border-slate-200 bg-slate-50/30 font-bold text-sm"
                                    />
                                </div>

                                <div class="space-y-3 min-h-[300px]">
                                    <div 
                                        v-for="student in filteredStudents" 
                                        :key="student.id"
                                        @click="toggleStudent(student.id)"
                                        class="p-4 rounded-2xl border-2 cursor-pointer transition-all flex items-center justify-between group"
                                        :class="form.student_ids.includes(student.id) ? 'border-blue-600 bg-blue-50/50' : 'border-slate-50 bg-slate-50/30 hover:border-primary/20 hover:bg-white'"
                                    >
                                        <div class="flex items-center gap-4">
                                            <div class="h-10 w-10 rounded-xl flex items-center justify-center font-black text-xs transition-colors"
                                                :class="form.student_ids.includes(student.id) ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-500 group-hover:bg-primary/20 group-hover:text-blue-600'">
                                                {{ student.first_name[0] }}{{ student.last_name[0] }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-slate-900">{{ student.first_name }} {{ student.last_name }}</p>
                                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-tighter">{{ student.admission_number }}</p>
                                            </div>
                                        </div>
                                        <div class="h-6 w-6 rounded-full border-2 flex items-center justify-center transition-all"
                                            :class="form.student_ids.includes(student.id) ? 'bg-blue-600 border-blue-600 text-white scale-110' : 'border-slate-200 group-hover:border-primary/30'">
                                            <CheckCircle2 v-if="form.student_ids.includes(student.id)" class="h-3.5 w-3.5" />
                                        </div>
                                    </div>
                                    
                                    <div v-if="filteredStudents.length === 0" class="flex flex-col items-center justify-center py-10 text-center space-y-3">
                                        <div class="h-12 w-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-300">
                                            <Search class="h-6 w-6" />
                                        </div>
                                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">No learners found</p>
                                    </div>
                                </div>

                                <div v-if="form.student_ids.length > 0" class="p-5 rounded-3xl bg-slate-900 text-white space-y-4">
                                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-400">Selected Dependents</p>
                                    <div class="flex flex-wrap gap-2">
                                        <Badge 
                                            v-for="id in form.student_ids" 
                                            :key="id"
                                            class="bg-white/10 hover:bg-white/20 text-white border-0 px-3 py-1.5 rounded-lg text-[10px] font-bold gap-2"
                                        >
                                            {{ getStudentName(id) }}
                                            <X class="h-3 w-3 cursor-pointer text-white/40 hover:text-white" @click.stop="toggleStudent(id)" />
                                        </Badge>
                                    </div>
                                </div>
                                <InputError :message="form.errors.student_ids" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Hub -->
                <div class="flex flex-wrap items-center justify-between gap-6 py-10 border-t-4 border-dashed border-slate-200 mt-6">
                    <Button type="button" variant="ghost" class="text-slate-400 hover:text-red-600 font-black uppercase tracking-widest px-8 h-14 rounded-2xl transition-all" as-child>
                        <Link href="/parents">Discard Changes</Link>
                    </Button>
                    <div class="flex gap-4">
                         <Button type="submit" :disabled="form.processing" class="h-14 px-12 rounded-2xl bg-blue-600 text-white hover:bg-blue-700 font-black shadow-2xl shadow-blue-200 transition-all border-0 uppercase tracking-widest">
                             <Loader2 v-if="form.processing" class="mr-3 h-5 w-5 animate-spin" />
                             <Save v-else class="mr-3 h-5 w-5" />
                             Update Account
                         </Button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Confirmation Modal -->
        <Dialog :open="confirmOpen" @update:open="confirmOpen = $event">
            <DialogContent class="rounded-[3rem] border-0 p-0 overflow-hidden shadow-2xl max-w-lg bg-white">
                <div class="p-12 text-center space-y-8">
                    <div class="mx-auto h-24 w-24 rounded-[2.5rem] bg-amber-50 flex items-center justify-center border-8 border-white shadow-xl rotate-3">
                        <AlertTriangle class="h-10 w-10 text-amber-500" />
                    </div>
                    <div class="space-y-3">
                        <h3 class="text-2xl font-black text-slate-900 italic uppercase">Verify Updates</h3>
                        <p class="text-sm text-slate-500 font-medium">You are about to update the guardian profile for <span class="text-blue-600 font-bold">{{ parent.first_name }}</span>. This will affect their portal access and link to <span class="text-slate-900 font-bold">{{ form.student_ids.length }}</span> learner(s).</p>
                    </div>
                    <div class="rounded-[2rem] bg-slate-50 p-6 border-2 border-slate-100 grid grid-cols-2 gap-6">
                        <div class="text-left space-y-1">
                            <p class="text-[10px] text-slate-400 uppercase font-black tracking-widest leading-none">Registration</p>
                            <p class="font-bold text-slate-900 text-sm tracking-tight">{{ form.first_name }} {{ form.last_name }}</p>
                        </div>
                        <div class="text-left space-y-1">
                            <p class="text-[10px] text-slate-400 uppercase font-black tracking-widest leading-none">Role Type</p>
                            <p class="font-bold text-blue-600 text-sm italic">{{ form.relationship_type }}</p>
                        </div>
                    </div>
                </div>
                <div class="flex p-6 bg-slate-50 gap-4">
                    <Button variant="ghost" class="flex-1 rounded-2xl h-14 font-black uppercase tracking-widest text-slate-400" @click="confirmOpen = false">Wait, Edit</Button>
                    <Button @click="confirmSubmit" :disabled="form.processing" class="flex-1 bg-blue-600 hover:bg-slate-900 text-white rounded-2xl h-14 font-black uppercase tracking-widest shadow-xl shadow-indigo-600/20 transition-all border-0">
                        <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                        Confirm
                    </Button>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Success Modal -->
        <Dialog :open="successOpen" @update:open="successOpen = $event">
            <DialogContent class="rounded-[3rem] border-0 p-0 overflow-hidden shadow-2xl max-w-md bg-white">
                <div class="p-12 text-center space-y-8">
                    <div class="mx-auto h-24 w-24 rounded-[2.5rem] bg-emerald-50 flex items-center justify-center border-8 border-white shadow-xl -rotate-3">
                        <CheckCircle2 class="h-10 w-10 text-emerald-500" />
                    </div>
                    <div class="space-y-3">
                        <h3 class="text-3xl font-black text-slate-900 tracking-tight italic uppercase">Account Updated</h3>
                        <p class="text-sm text-slate-500 font-medium leading-relaxed">
                            Updates for <span class="text-emerald-500 font-black italic">{{ form.first_name }}</span> have been successfully synchronized.
                        </p>
                    </div>
                    <Button as-child class="w-full bg-slate-900 hover:bg-blue-600 text-white rounded-2xl h-14 font-black uppercase tracking-widest shadow-2xl transition-all border-0 mt-4">
                        <Link href="/parents">
                            Return to Registry
                        </Link>
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
