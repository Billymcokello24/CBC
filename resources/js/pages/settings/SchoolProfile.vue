<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { School, Edit, Save, Globe, Mail, Phone, MapPin, Building2, ShieldCheck, BadgeCheck } from 'lucide-vue-next';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    school: any;
    settings: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Settings', href: '/settings/school' },
    { title: 'School Profile', href: '/settings/school' },
];

const isEditing = ref(false);

</script>

<template>
    <Head title="School Profile" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 font-pulsar">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-500/10">
                        <School class="h-6 w-6 text-violet-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">School Profile</h1>
                        <p class="text-muted-foreground">Manage your school's official identity, contacts and registration data</p>
                    </div>
                </div>
                <Button v-if="!isEditing" @click="isEditing = true" variant="outline" class="font-pulsar">
                    <Edit class="mr-2 h-4 w-4" />Edit Profile
                </Button>
                <div v-else class="flex gap-2">
                     <Button @click="isEditing = false" variant="ghost">Cancel</Button>
                     <Button class="bg-violet-600 hover:bg-violet-700 font-pulsar"><Save class="mr-2 h-4 w-4" />Save Changes</Button>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-1 space-y-6">
                    <div class="rounded-2xl border bg-card p-8 shadow-sm text-center relative overflow-hidden">
                        <div class="absolute top-4 right-4">
                             <BadgeCheck class="h-6 w-6 text-emerald-500" />
                        </div>
                        <div class="mx-auto h-24 w-24 rounded-2xl bg-slate-50 flex items-center justify-center border-2 border-dashed border-slate-200 mb-4 group relative overflow-hidden">
                             <img v-if="school.logo" :src="school.logo" class="h-full w-full object-contain p-2" />
                             <School v-else class="h-10 w-10 text-slate-300" />
                             <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity cursor-pointer">
                                 <Edit class="h-5 w-5 text-white" />
                             </div>
                        </div>
                        <h2 class="text-xl font-bold text-slate-900">{{ school.name }}</h2>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mt-1">{{ school.code }}</p>
                        
                        <div class="mt-8 space-y-3">
                             <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 text-xs">
                                <span class="text-slate-500 font-medium">Status</span>
                                <Badge variant="outline" class="bg-emerald-50 text-emerald-700 border-emerald-100 uppercase tracking-widest text-[9px] font-bold">{{ school.status }}</Badge>
                             </div>
                             <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 text-xs">
                                <span class="text-slate-500 font-medium">Established</span>
                                <span class="font-bold text-slate-700">{{ new Date(school.established_date).getFullYear() }}</span>
                             </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border bg-card p-6 shadow-sm">
                        <h3 class="font-bold mb-4 flex items-center gap-2"><Globe class="h-4 w-4 text-violet-600" /> Digital Presence</h3>
                        <div class="space-y-4">
                            <div>
                                <Label class="text-[10px] uppercase font-black text-slate-400 mb-1">Official Website</Label>
                                <div class="flex items-center gap-2 text-sm text-blue-600 hover:underline cursor-pointer">
                                    {{ school.website || 'Add website URL' }}
                                </div>
                            </div>
                            <div>
                                <Label class="text-[10px] uppercase font-black text-slate-400 mb-1">Primary Email</Label>
                                <div class="flex items-center gap-2 text-sm font-bold text-slate-700">
                                    {{ school.email }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2 space-y-6">
                    <div class="rounded-2xl border bg-card shadow-sm overflow-hidden">
                        <div class="bg-slate-50 px-6 py-4 border-b">
                            <h3 class="font-bold">General Information</h3>
                        </div>
                        <div class="p-6 grid gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label>School Name</Label>
                                <Input v-model="school.name" :disabled="!isEditing" />
                            </div>
                            <div class="space-y-2">
                                <Label>Registration Number</Label>
                                <Input v-model="school.registration_number" :disabled="!isEditing" />
                            </div>
                            <div class="md:col-span-2 space-y-2">
                                <Label>School Motto</Label>
                                <Input v-model="school.motto" :disabled="!isEditing" placeholder="Ex: Excellence Through Diligence" />
                            </div>
                            <div class="space-y-2">
                                <Label>Primary Phone</Label>
                                <Input v-model="school.phone" :disabled="!isEditing" />
                            </div>
                            <div class="space-y-2">
                                <Label>Alternate Phone</Label>
                                <Input v-model="school.alternate_phone" :disabled="!isEditing" />
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border bg-card shadow-sm overflow-hidden">
                        <div class="bg-slate-50 px-6 py-4 border-b">
                            <h3 class="font-bold">Location & Address</h3>
                        </div>
                        <div class="p-6 grid gap-6 md:grid-cols-2">
                            <div class="md:col-span-2 space-y-2">
                                <Label>Physical Address</Label>
                                <Input v-model="school.address" :disabled="!isEditing" />
                            </div>
                            <div class="space-y-2">
                                <Label>County</Label>
                                <Input v-model="school.county" :disabled="!isEditing" />
                            </div>
                            <div class="space-y-2">
                                <Label>Sub-County</Label>
                                <Input v-model="school.sub_county" :disabled="!isEditing" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
