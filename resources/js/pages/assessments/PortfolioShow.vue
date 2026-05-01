<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ChevronLeft,
    Plus,
    FileText,
    Image as ImageIcon,
    Video,
    Link as LinkIcon,
    Trash2,
    Calendar,
    CalendarDays,
    Info,
    BookOpen,
    Download,
} from 'lucide-vue-next';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    student: any;
    portfolio: any;
    subjects: Array<any>;
    competencies: Array<any>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Assessments', href: '/assessments' },
    { title: 'Portfolio Management', href: '/assessments/portfolio' },
    {
        title: props.student.first_name + ' ' + props.student.last_name,
        href: '#',
    },
];

const showAddModal = ref(false);
const form = useForm({
    title: '',
    description: '',
    item_type: 'image',
    item_date: new Date().toISOString().split('T')[0],
    subject_id: '',
    indicator_ids: [] as number[],
    file: null as File | null,
    url: '',
});

const filteredCompetencies = ref<any[]>([]);

const onSubjectChange = (val: any) => {
    form.indicator_ids = [];
    if (val) {
        filteredCompetencies.value = props.competencies.filter(
            (c) => c.subject_id == parseInt(val as string),
        );
    }
};

const toggleIndicator = (id: number) => {
    const index = form.indicator_ids.indexOf(id);
    if (index > -1) {
        form.indicator_ids.splice(index, 1);
    } else {
        form.indicator_ids.push(id);
    }
};

const submit = () => {
    form.post('/assessments/portfolio/' + props.portfolio.id + '/item', {
        onSuccess: () => {
            showAddModal.value = false;
            form.reset();
        },
    });
};

const deleteItem = (id: number) => {
    if (confirm('Are you sure you want to remove this item?')) {
        form.delete('/assessments/portfolio/item/' + id);
    }
};

const getItemIcon = (type: string) => {
    switch (type) {
        case 'image':
            return ImageIcon;
        case 'video':
            return Video;
        case 'link':
            return LinkIcon;
        case 'text':
            return FileText;
        default:
            return FileText;
    }
};

const getBadgeColor = (type: string) => {
    switch (type) {
        case 'image':
            return 'bg-blue-100 text-blue-700';
        case 'video':
            return 'bg-purple-100 text-purple-700';
        case 'link':
            return 'bg-orange-100 text-orange-700';
        default:
            return 'bg-slate-100 text-slate-700';
    }
};
</script>

<template>
    <Head :title="student.first_name + ' - Portfolio'" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex h-full max-w-[1600px] flex-1 animate-in flex-col space-y-8 p-4 pb-20 duration-700 fade-in slide-in-from-bottom-4 sm:p-6 sm:pb-32 md:p-8">
            <!-- Header -->
            <div class="flex flex-col gap-6 px-1 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="outline" size="icon" as-child class="h-8 w-8 rounded-lg">
                        <Link href="/assessments/portfolio">
                            <ChevronLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <div class="space-y-1">
                        <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">Learner Evidence Portfolio</h1>
                        <p class="text-xs text-muted-foreground">Collecting evidence of learning for {{ student.first_name }} {{ student.last_name }}.</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                     <Dialog v-model:open="showAddModal">
                        <DialogTrigger as-child>
                            <Button class="h-10 rounded-lg bg-primary px-6 text-xs font-semibold text-white shadow-sm hover:bg-primary/90 transition-all">
                                <Plus class="mr-2 h-4 w-4" /> Add Evidence
                            </Button>
                        </DialogTrigger>
                    <DialogContent
                        class="max-h-[90vh] overflow-y-auto rounded-xl border-0 p-8 shadow-lg sm:max-w-[600px]"
                    >
                        <DialogHeader>
                            <DialogTitle
                                class="text-2xl font-bold tracking-tight "
                                >Add New Evidence</DialogTitle
                            >
                            <DialogDescription class="font-bold text-muted-foreground"
                                >Capture a new learning outcome or
                                achievement.</DialogDescription
                            >
                        </DialogHeader>
                        <form @submit.prevent="submit" class="grid gap-6 py-4">
                            <div class="grid gap-2">
                                <label
                                    class="pl-1 text-xs font-medium tracking-tight text-muted-foreground/80 "
                                    >Evidence Title</label
                                >
                                <Input
                                    v-model="form.title"
                                    placeholder="e.g., Solar System Model Project"
                                    class="h-11 rounded-2xl border-border/50"
                                    required
                                />
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="grid gap-2">
                                    <label
                                        class="pl-1 text-xs font-medium tracking-tight text-muted-foreground/80 "
                                        >Type</label
                                    >
                                    <Select v-model="form.item_type">
                                        <SelectTrigger
                                            class="h-11 rounded-2xl border-border/50"
                                        >
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="image"
                                                >Image / Photo</SelectItem
                                            >
                                            <SelectItem value="video"
                                                >Video Recording</SelectItem
                                            >
                                            <SelectItem value="document"
                                                >Document / PDF</SelectItem
                                            >
                                            <SelectItem value="text"
                                                >Written Reflection</SelectItem
                                            >
                                            <SelectItem value="link"
                                                >Web Link</SelectItem
                                            >
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div class="grid gap-2">
                                    <label
                                        class="pl-1 text-xs font-medium tracking-tight text-muted-foreground/80 "
                                        >Date</label
                                    >
                                    <Input
                                        v-model="form.item_date"
                                        type="date"
                                        class="h-11 rounded-2xl border-border/50"
                                        required
                                    />
                                </div>
                            </div>
                            <div class="grid gap-2">
                                <label
                                    class="pl-1 text-xs font-medium tracking-tight text-muted-foreground/80 "
                                    >Learning Area (Subject)</label
                                >
                                <Select
                                    v-model="form.subject_id"
                                    @update:model-value="onSubjectChange"
                                >
                                    <SelectTrigger
                                        class="h-11 rounded-2xl border-border/50"
                                    >
                                        <SelectValue
                                            placeholder="Select Subject"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="subject in subjects"
                                            :key="subject.id"
                                            :value="subject.id.toString()"
                                        >
                                            {{ subject.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <!-- Indicator Selection -->
                            <div
                                v-if="
                                    form.subject_id &&
                                    filteredCompetencies.length > 0
                                "
                                class="grid gap-3 rounded-2xl border border-dashed border-border/50 bg-muted/10 p-4"
                            >
                                <label
                                    class="text-xs font-medium tracking-tight text-muted-foreground/80 "
                                    >Target Competencies / Indicators</label
                                >
                                <div
                                    class="max-h-[200px] space-y-4 overflow-y-auto pr-2"
                                >
                                    <div
                                        v-for="comp in filteredCompetencies"
                                        :key="comp.id"
                                        class="space-y-2"
                                    >
                                        <p
                                            class="text-xs font-bold text-indigo-600 "
                                        >
                                            {{ comp.title }}
                                        </p>
                                        <div class="flex flex-wrap gap-2">
                                            <button
                                                v-for="indicator in comp.indicators"
                                                :key="indicator.id"
                                                type="button"
                                                @click="
                                                    toggleIndicator(
                                                        indicator.id,
                                                    )
                                                "
                                                :class="[
                                                    'min-w-[140px] flex-1 rounded-xl border px-3 py-1.5 text-left text-xs font-bold transition-all',
                                                    form.indicator_ids.includes(
                                                        indicator.id,
                                                    )
                                                        ? 'border-indigo-600 bg-indigo-600 text-white shadow-lg'
                                                        : 'border-border/50 bg-white text-slate-600 hover:border-indigo-300',
                                                ]"
                                            >
                                                {{ indicator.title }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="grid gap-2"
                                v-if="
                                    ['image', 'video', 'document'].includes(
                                        form.item_type,
                                    )
                                "
                            >
                                <label
                                    class="pl-1 text-xs font-medium tracking-tight text-muted-foreground/80 "
                                    >File Upload</label
                                >
                                <Input
                                    type="file"
                                    @input="
                                        form.file =
                                            ($event.target as HTMLInputElement)
                                                .files?.[0] || null
                                    "
                                    class="h-11 rounded-2xl border-border/50 pt-2"
                                />
                            </div>
                            <div
                                class="grid gap-2"
                                v-if="form.item_type === 'link'"
                            >
                                <label
                                    class="pl-1 text-xs font-medium tracking-tight text-muted-foreground/80 "
                                    >URL</label
                                >
                                <Input
                                    v-model="form.url"
                                    placeholder="https://"
                                    class="h-11 rounded-2xl border-border/50"
                                />
                            </div>
                            <div class="grid gap-2">
                                <label
                                    class="pl-1 text-xs font-medium tracking-tight text-muted-foreground/80 "
                                    >Observations / Description</label
                                >
                                <Textarea
                                    v-model="form.description"
                                    class="min-h-[100px] rounded-2xl border-border/50"
                                    placeholder="What competency was demonstrated?"
                                />
                            </div>
                            <DialogFooter>
                                <Button
                                    type="submit"
                                    class="h-12 w-full rounded-2xl bg-indigo-600 font-medium tracking-tight  hover:bg-indigo-700"
                                    :disabled="form.processing"
                                >
                                    Save to Portfolio
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
                </div>
            </div>

            <!-- Evidence Grid -->
            <div
                v-if="portfolio.items.length === 0"
                class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-border/50 bg-white py-24"
            >
                <div
                    class="mb-4 flex h-20 w-20 items-center justify-center rounded-2xl bg-muted/10 text-slate-300"
                >
                    <FileText class="h-10 w-10" />
                </div>
                <h3 class="text-xl font-bold text-foreground">
                    No Evidence Found
                </h3>
                <p class="mt-1 font-bold text-muted-foreground">
                    Start adding learner achievements to build their portfolio.
                </p>
            </div>

            <div
                v-else
                class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
            >
                <Card
                    v-for="item in portfolio.items"
                    :key="item.id"
                    class="group flex flex-col overflow-hidden rounded-xl border-border bg-white shadow-sm transition-all hover:-translate-y-1 hover:shadow-xl"
                >
                    <!-- Media Preview -->
                    <div
                        class="relative h-48 shrink-0 overflow-hidden bg-slate-100"
                    >
                        <div
                            v-if="item.item_type === 'image' && item.file_path"
                            class="h-full w-full"
                        >
                            <img
                                :src="'/storage/' + item.file_path"
                                class="h-full w-full object-cover"
                            />
                        </div>
                        <div
                            v-else
                            class="flex h-full w-full items-center justify-center text-slate-300"
                        >
                            <component
                                :is="getItemIcon(item.item_type)"
                                class="h-12 w-12"
                            />
                        </div>
                        <Badge
                            :class="getBadgeColor(item.item_type)"
                            class="absolute top-4 left-4 border-0 px-3 py-1 text-xs font-bold tracking-tight  shadow-sm"
                        >
                            {{ item.item_type }}
                        </Badge>
                        <div
                            class="absolute inset-0 flex items-center justify-center gap-2 bg-black/40 opacity-0 transition-opacity group-hover:opacity-100"
                        >
                            <Button
                                v-if="item.file_path"
                                variant="outline"
                                size="sm"
                                class="h-10 w-10 rounded-full border-white/30 bg-white/20 p-0 text-white backdrop-blur-md hover:bg-white hover:text-indigo-600"
                                as-child
                            >
                                <a
                                    :href="'/storage/' + item.file_path"
                                    target="_blank"
                                    ><Download class="h-4 w-4"
                                /></a>
                            </Button>
                            <Button
                                @click="deleteItem(item.id)"
                                variant="destructive"
                                size="sm"
                                class="h-10 w-10 rounded-full border-0 bg-red-500/80 p-0 backdrop-blur-md"
                            >
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                    <CardContent class="flex flex-1 flex-col p-6">
                        <div class="mb-3 flex items-center gap-2">
                            <BookOpen class="h-3.5 w-3.5 text-indigo-500" />
                            <span
                                class="text-xs font-bold tracking-tight text-indigo-600 "
                                >{{
                                    item.subject?.name || 'General Achievement'
                                }}</span
                            >
                        </div>
                        <h4
                            class="mb-2 text-lg leading-tight font-bold tracking-tight text-foreground"
                        >
                            {{ item.title }}
                        </h4>
                        <p
                            class="mb-4 line-clamp-2 text-xs font-medium text-muted-foreground"
                        >
                            {{ item.description || 'No description provided.' }}
                        </p>

                        <!-- Linked Indicators -->
                        <div
                            v-if="item.indicators?.length > 0"
                            class="mb-4 flex flex-wrap gap-1"
                        >
                            <div
                                v-for="ind in item.indicators"
                                :key="ind.id"
                                class="max-w-[150px] truncate rounded-lg border border-indigo-100 bg-indigo-50 px-2 py-0.5 text-xs font-bold text-indigo-600"
                                :title="ind.title"
                            >
                                {{ ind.title }}
                            </div>
                        </div>

                        <div
                            class="mt-auto flex items-center justify-between border-t border-slate-50 pt-4"
                        >
                            <div
                                class="flex items-center gap-1.5 text-xs font-bold tracking-wider text-muted-foreground/80 "
                            >
                                <CalendarDays class="h-3.5 w-3.5" />
                                {{
                                    new Date(
                                        item.item_date,
                                    ).toLocaleDateString()
                                }}
                            </div>
                            <div class="flex -space-x-2">
                                <div
                                    class="flex h-6 w-6 items-center justify-center overflow-hidden rounded-full border-2 border-white bg-slate-100"
                                >
                                    <Info class="h-3 w-3 text-slate-300" />
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
