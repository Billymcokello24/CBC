<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { BookOpen, Search, Plus, ChevronRight } from 'lucide-vue-next';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Curriculum', href: '/curriculum' },
];

const learningAreas = ref([
    { id: 1, name: 'Languages', code: 'LANG', color: 'blue', subjects: ['English', 'Kiswahili', 'Indigenous Languages'], icon: '📚' },
    { id: 2, name: 'Mathematics', code: 'MATH', color: 'green', subjects: ['Mathematics'], icon: '🔢' },
    { id: 3, name: 'Science & Technology', code: 'SCI', color: 'purple', subjects: ['Science', 'Technology'], icon: '🔬' },
    { id: 4, name: 'Social Studies', code: 'SS', color: 'amber', subjects: ['Social Studies', 'Religious Education'], icon: '🌍' },
    { id: 5, name: 'Creative Arts', code: 'ARTS', color: 'pink', subjects: ['Art & Craft', 'Music'], icon: '🎨' },
    { id: 6, name: 'Physical & Health Education', code: 'PHE', color: 'red', subjects: ['Physical Education', 'Health Education'], icon: '⚽' },
]);

const competencies = ref([
    { id: 1, name: 'Communication & Collaboration', description: 'Ability to communicate effectively and work with others' },
    { id: 2, name: 'Critical Thinking & Problem Solving', description: 'Analytical and creative thinking skills' },
    { id: 3, name: 'Creativity & Imagination', description: 'Innovative and original thinking' },
    { id: 4, name: 'Citizenship', description: 'Social responsibility and civic engagement' },
    { id: 5, name: 'Digital Literacy', description: 'Technology skills and digital citizenship' },
    { id: 6, name: 'Learning to Learn', description: 'Self-directed learning and metacognition' },
    { id: 7, name: 'Self-Efficacy', description: 'Confidence and self-management' },
]);

const getColorClass = (color: string) => {
    const colors: Record<string, string> = {
        blue: 'from-blue-500 to-blue-600',
        green: 'from-green-500 to-green-600',
        purple: 'from-purple-500 to-purple-600',
        amber: 'from-amber-500 to-amber-600',
        pink: 'from-pink-500 to-pink-600',
        red: 'from-red-500 to-red-600',
    };
    return colors[color] || colors.blue;
};
</script>

<template>
    <Head title="Curriculum" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/10">
                        <BookOpen class="h-6 w-6 text-emerald-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">CBC Curriculum</h1>
                        <p class="text-muted-foreground">Manage learning areas, subjects, and competencies</p>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Button variant="outline" class="h-auto justify-start p-4" as-child>
                    <Link href="/curriculum/learning-areas" class="flex items-center gap-3">
                        <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900/30"><BookOpen class="h-5 w-5 text-blue-600" /></div>
                        <div class="text-left"><div class="font-medium">Learning Areas</div><div class="text-xs text-muted-foreground">6 areas</div></div>
                        <ChevronRight class="ml-auto h-5 w-5 text-muted-foreground" />
                    </Link>
                </Button>
                <Button variant="outline" class="h-auto justify-start p-4" as-child>
                    <Link href="/curriculum/subjects" class="flex items-center gap-3">
                        <div class="rounded-lg bg-green-100 p-2 dark:bg-green-900/30"><BookOpen class="h-5 w-5 text-green-600" /></div>
                        <div class="text-left"><div class="font-medium">Subjects</div><div class="text-xs text-muted-foreground">14 subjects</div></div>
                        <ChevronRight class="ml-auto h-5 w-5 text-muted-foreground" />
                    </Link>
                </Button>
                <Button variant="outline" class="h-auto justify-start p-4" as-child>
                    <Link href="/curriculum/strands" class="flex items-center gap-3">
                        <div class="rounded-lg bg-purple-100 p-2 dark:bg-purple-900/30"><BookOpen class="h-5 w-5 text-purple-600" /></div>
                        <div class="text-left"><div class="font-medium">Strands</div><div class="text-xs text-muted-foreground">48 strands</div></div>
                        <ChevronRight class="ml-auto h-5 w-5 text-muted-foreground" />
                    </Link>
                </Button>
                <Button variant="outline" class="h-auto justify-start p-4" as-child>
                    <Link href="/curriculum/competencies" class="flex items-center gap-3">
                        <div class="rounded-lg bg-amber-100 p-2 dark:bg-amber-900/30"><BookOpen class="h-5 w-5 text-amber-600" /></div>
                        <div class="text-left"><div class="font-medium">Competencies</div><div class="text-xs text-muted-foreground">7 core</div></div>
                        <ChevronRight class="ml-auto h-5 w-5 text-muted-foreground" />
                    </Link>
                </Button>
            </div>

            <!-- Learning Areas -->
            <div>
                <h2 class="text-lg font-semibold mb-4">Learning Areas</h2>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <div v-for="area in learningAreas" :key="area.id" class="rounded-xl border bg-card overflow-hidden hover:shadow-lg transition-shadow">
                        <div :class="['h-2 bg-gradient-to-r', getColorClass(area.color)]"></div>
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <span class="text-3xl">{{ area.icon }}</span>
                                    <div>
                                        <h3 class="font-semibold">{{ area.name }}</h3>
                                        <p class="text-sm text-muted-foreground">{{ area.code }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <p class="text-sm text-muted-foreground">Subjects:</p>
                                <div class="flex flex-wrap gap-2">
                                    <Badge v-for="subject in area.subjects" :key="subject" variant="secondary">{{ subject }}</Badge>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Core Competencies -->
            <div>
                <h2 class="text-lg font-semibold mb-4">Core Competencies</h2>
                <div class="grid gap-4 md:grid-cols-2">
                    <div v-for="(comp, index) in competencies" :key="comp.id" class="flex items-start gap-4 rounded-xl border bg-card p-4 hover:bg-muted/50 transition-colors">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary text-white font-bold text-sm shrink-0">
                            {{ index + 1 }}
                        </div>
                        <div>
                            <h3 class="font-medium">{{ comp.name }}</h3>
                            <p class="text-sm text-muted-foreground">{{ comp.description }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
