<template>
    <form
        @submit.prevent="submit"
        class="animate-in space-y-10 duration-700 fade-in slide-in-from-bottom-4"
    >
        <!-- Section: Strategic Alignment -->
        <div
            class="overflow-hidden rounded-xl border border-slate-100 bg-white shadow-sm transition-all hover:shadow-xl hover:shadow-primary/5"
        >
            <div
                class="flex items-center justify-between border-b border-slate-100 bg-slate-50/50 px-10 py-6"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-2xl"
                        :style="{
                            backgroundColor: themeSecondary,
                            color: themeColor,
                        }"
                    >
                        <GraduationCap class="h-5 w-5" />
                    </div>
                    <div>
                        <h3
                            class="text-sm font-bold tracking-tight text-slate-900 uppercase"
                        >
                            Strategic Alignment
                        </h3>
                        <p
                            class="mt-0.5 text-xs font-bold tracking-tight text-slate-400 uppercase"
                        >
                            Curriculum Matrix Positioning
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <div
                        class="h-2 w-2 animate-pulse rounded-full bg-emerald-500"
                    ></div>
                    <span
                        class="font-mono text-xs font-bold tracking-tight text-slate-400 uppercase"
                        >Formal Logic</span
                    >
                </div>
            </div>

            <div class="grid gap-8 p-10 md:grid-cols-3">
                <div class="space-y-3">
                    <Label
                        class="ml-4 text-xs font-bold tracking-tight text-slate-400 uppercase"
                        >Discipline Area</Label
                    >
                    <div class="relative">
                        <BookOpen
                            class="absolute top-1/2 left-6 h-4 w-4 -translate-y-1/2 text-slate-300"
                        />
                        <select
                            v-model="form.subject_id"
                            class="h-16 w-full rounded-2xl border-2 border-slate-100 bg-slate-50/30 pr-8 pl-14 text-sm font-medium tracking-tight uppercase shadow-inner transition-all outline-none focus:bg-white focus:ring-8 focus:ring-primary/5"
                            required
                        >
                            <option value="" disabled>Select Area</option>
                            <option
                                v-for="s in subjects"
                                :key="s.id"
                                :value="s.id"
                            >
                                {{ s.name }}
                            </option>
                        </select>
                    </div>
                </div>
                <div class="space-y-3">
                    <Label
                        class="ml-4 text-xs font-bold tracking-tight text-slate-400 uppercase"
                        >Grade Level</Label
                    >
                    <div class="relative">
                        <Sparkles
                            class="absolute top-1/2 left-6 h-4 w-4 -translate-y-1/2 text-slate-300"
                        />
                        <select
                            v-model="form.grade_level_id"
                            class="h-16 w-full rounded-2xl border-2 border-slate-100 bg-slate-50/30 pr-8 pl-14 text-sm font-medium tracking-tight uppercase shadow-inner transition-all outline-none focus:bg-white focus:ring-8 focus:ring-primary/5"
                        >
                            <option value="">Universal</option>
                            <option
                                v-for="g in grades"
                                :key="g.id"
                                :value="g.id"
                            >
                                {{ g.name }}
                            </option>
                        </select>
                    </div>
                </div>
                <div class="space-y-3">
                    <Label
                        class="ml-4 text-xs font-bold tracking-tight text-slate-400 uppercase"
                        >Target Folder (Optional)</Label
                    >
                    <div class="relative">
                        <Plus
                            class="absolute top-1/2 left-6 h-4 w-4 -translate-y-1/2 text-slate-300"
                        />
                        <select
                            v-model="form.folder_id"
                            class="h-16 w-full rounded-2xl border-2 border-slate-100 bg-slate-50/30 pr-8 pl-14 text-sm font-medium tracking-tight uppercase shadow-inner transition-all outline-none focus:bg-white focus:ring-8 focus:ring-primary/5"
                        >
                            <option value="">No Folder (Root Index)</option>
                            <option
                                v-for="f in folders"
                                :key="f.id"
                                :value="f.id"
                            >
                                {{ f.name }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section: Asset Intelligence -->
        <div
            class="overflow-hidden rounded-xl border border-slate-100 bg-white shadow-sm transition-all hover:shadow-xl hover:shadow-primary/5"
        >
            <div class="border-b border-slate-100 bg-slate-50/50 px-10 py-6">
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-2xl bg-orange-100/50 text-orange-600"
                    >
                        <FileText class="h-5 w-5" />
                    </div>
                    <div>
                        <h3
                            class="text-sm font-bold tracking-tight text-slate-900 uppercase"
                        >
                            Asset Intelligence
                        </h3>
                        <p
                            class="mt-0.5 text-xs font-bold tracking-tight text-slate-400 uppercase"
                        >
                            Core Metadata & Source Definition
                        </p>
                    </div>
                </div>
            </div>

            <div class="space-y-10 p-10">
                <div class="grid gap-3">
                    <Label
                        class="ml-4 text-xs font-bold tracking-tight text-slate-400 uppercase"
                        >Formal Asset Title</Label
                    >
                    <Input
                        v-model="form.title"
                        placeholder="ENTER SYLLABIC ASSIGNMENT TITLE..."
                        class="h-20 rounded-3xl border-2 border-slate-100 bg-slate-50/30 px-10 text-lg font-bold uppercase shadow-inner transition-all outline-none focus:bg-white focus:ring-12 focus:ring-primary/5"
                        required
                    />
                </div>

                <div class="grid gap-10 lg:grid-cols-2">
                    <!-- File Provision -->
                    <div class="space-y-4">
                        <Label
                            class="ml-4 text-xs font-bold tracking-tight text-slate-400 uppercase"
                            >Binary Provision (Multiple Allowed)</Label
                        >
                        <div class="group/upload relative">
                            <input
                                type="file"
                                @change="handleFileChange"
                                multiple
                                class="absolute inset-0 z-20 h-full w-full cursor-pointer opacity-0"
                            />
                            <div
                                :class="[
                                    'flex min-h-[220px] flex-col items-center justify-center rounded-xl border-3 border-dashed bg-slate-50/50 p-8 text-center transition-all',
                                    form.files.length > 0
                                        ? 'border-emerald-200 bg-emerald-50/10'
                                        : 'border-slate-100 group-hover/upload:border-primary/30 group-hover/upload:bg-primary/[0.02]',
                                ]"
                            >
                                <div
                                    v-if="form.files.length > 0"
                                    class="max-h-[300px] w-full space-y-4 overflow-y-auto px-6"
                                >
                                    <div
                                        v-for="(f, i) in form.files"
                                        :key="i"
                                        class="flex animate-in items-center gap-4 rounded-2xl border border-emerald-100 bg-white p-4 shadow-sm duration-300 slide-in-from-left-4"
                                        :style="{
                                            transitionDelay: `${i * 100}ms`,
                                        }"
                                    >
                                        <div
                                            class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-emerald-500 text-white"
                                        >
                                            <CheckCircle2 class="h-5 w-5" />
                                        </div>
                                        <div class="overflow-hidden text-left">
                                            <span
                                                class="block truncate text-xs font-bold tracking-tight text-slate-700 uppercase"
                                                >{{ f.name }}</span
                                            >
                                            <span
                                                class="text-xs font-bold tracking-tight text-slate-400 uppercase"
                                                >{{
                                                    (
                                                        f.size /
                                                        1024 /
                                                        1024
                                                    ).toFixed(2)
                                                }}
                                                MB Payload</span
                                            >
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="space-y-4">
                                    <div
                                        class="mx-auto flex h-20 w-20 items-center justify-center rounded-xl border border-slate-100 bg-white text-slate-200 shadow-sm transition-all duration-500 group-hover/upload:scale-110 group-hover/upload:text-primary"
                                    >
                                        <UploadCloud class="h-10 w-10" />
                                    </div>
                                    <div class="space-y-1">
                                        <p
                                            class="text-xs font-medium tracking-tight text-muted-foreground text-slate-400 uppercase"
                                        >
                                            Syllabic Payload Drop
                                        </p>
                                        <p
                                            class="text-xs font-bold text-slate-300"
                                        >
                                            Select one or more assets to
                                            synchronize
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Link Provision -->
                    <div class="space-y-10">
                        <div class="space-y-4">
                            <Label
                                class="ml-4 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                >External Protocol (URL)</Label
                            >
                            <div class="relative flex items-center">
                                <div
                                    class="absolute left-6 flex h-12 w-12 items-center justify-center rounded-2xl border border-slate-100 bg-white text-slate-300 shadow-sm"
                                >
                                    <Globe class="h-5 w-5" />
                                </div>
                                <Input
                                    v-model="form.url"
                                    placeholder="HTTPS://KNOWLEDGE-VAULT.COM/ASSET..."
                                    class="h-20 rounded-3xl border-2 border-slate-100 bg-slate-50/50 pl-24 text-xs font-bold tracking-wider uppercase shadow-inner transition-all outline-none focus:bg-white"
                                />
                            </div>
                        </div>

                        <div class="space-y-4 border-t border-slate-50 pt-10">
                            <Label
                                class="ml-4 text-xs font-bold tracking-tight text-slate-400 uppercase"
                                >Discovery Logic (Type)</Label
                            >
                            <div class="grid grid-cols-2 gap-3">
                                <button
                                    v-for="type in [
                                        'video',
                                        'pdf',
                                        'image',
                                        'audio',
                                    ]"
                                    :key="type"
                                    type="button"
                                    @click="form.resource_type = type"
                                    :class="[
                                        'flex h-14 items-center justify-center gap-2 rounded-2xl border-2 text-xs font-medium tracking-tight text-muted-foreground uppercase shadow-sm transition-all',
                                        form.resource_type === type
                                            ? 'text-white'
                                            : 'border-slate-100 bg-slate-50 text-slate-400 hover:bg-white',
                                    ]"
                                    :style="
                                        form.resource_type === type
                                            ? {
                                                  backgroundColor: themeColor,
                                                  borderColor: themeColor,
                                                  boxShadow: `0 10px 15px -3px ${themeColor}33`,
                                              }
                                            : {}
                                    "
                                >
                                    <component
                                        :is="
                                            type === 'video'
                                                ? Play
                                                : type === 'pdf'
                                                  ? FileText
                                                  : type === 'image'
                                                    ? ImageIcon
                                                    : Music
                                        "
                                        class="h-4 w-4"
                                    />
                                    {{ type }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section: Instructional Meta -->
        <div
            class="overflow-hidden rounded-xl border border-slate-100 bg-white shadow-sm transition-all hover:shadow-xl hover:shadow-primary/5"
        >
            <div
                class="flex items-center justify-between border-b border-slate-100 bg-slate-50/50 px-10 py-6"
            >
                <div class="flex items-center gap-4">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-2xl bg-violet-100/50 text-violet-600"
                    >
                        <Clock class="h-5 w-5" />
                    </div>
                    <div>
                        <h3
                            class="text-sm font-bold tracking-tight text-slate-900 uppercase"
                        >
                            Instructional Meta
                        </h3>
                        <p
                            class="mt-0.5 text-xs font-bold tracking-tight text-slate-400 uppercase"
                        >
                            Authoritative Source & Bibliographic Data
                        </p>
                    </div>
                </div>
            </div>

            <div class="space-y-10 p-10">
                <div class="grid gap-3">
                    <Label
                        class="ml-4 text-xs font-bold tracking-tight text-slate-400 uppercase"
                        >Pedagogical Summary</Label
                    >
                    <Textarea
                        v-model="form.description"
                        placeholder="DESCRIBE THE CORE ACADEMIC OBJECTIVE OF THIS PROVISION..."
                        class="min-h-[160px] rounded-xl border-2 border-slate-100 bg-slate-50/30 p-10 text-sm leading-relaxed font-bold tracking-tight uppercase shadow-inner transition-all focus:bg-white"
                    />
                </div>

                <div class="grid grid-cols-2 gap-8 lg:grid-cols-4">
                    <div class="space-y-3">
                        <Label
                            class="ml-4 text-xs font-bold tracking-tight text-slate-400 uppercase"
                            >Primary Author</Label
                        >
                        <Input
                            v-model="form.author"
                            class="h-14 rounded-2xl border-2 border-slate-100 bg-slate-50/30 px-6 text-sm font-bold tracking-wider uppercase shadow-inner transition-all focus:bg-white"
                        />
                    </div>
                    <div class="space-y-3">
                        <Label
                            class="ml-4 text-xs font-bold tracking-tight text-slate-400 uppercase"
                            >Prov. Publisher</Label
                        >
                        <Input
                            v-model="form.publisher"
                            class="h-14 rounded-2xl border-2 border-slate-100 bg-slate-50/30 px-6 text-sm font-bold tracking-wider uppercase shadow-inner transition-all focus:bg-white"
                        />
                    </div>
                    <div class="space-y-3">
                        <Label
                            class="ml-4 text-xs font-bold tracking-tight text-slate-400 uppercase"
                            >Universal ISBN</Label
                        >
                        <Input
                            v-model="form.isbn"
                            class="h-14 rounded-2xl border-2 border-slate-100 bg-slate-50/30 px-6 text-sm font-bold tracking-wider uppercase shadow-inner transition-all focus:bg-white"
                        />
                    </div>
                    <div class="space-y-3">
                        <Label
                            class="ml-4 text-xs font-bold tracking-tight text-slate-400 uppercase"
                            >Release Year</Label
                        >
                        <Input
                            v-model="form.year_published"
                            class="h-14 rounded-2xl border-2 border-slate-100 bg-slate-50/30 px-6 text-sm font-bold tracking-wider uppercase shadow-inner transition-all focus:bg-white"
                        />
                    </div>
                </div>

                <div
                    @click="form.is_recommended = !form.is_recommended"
                    class="group relative flex cursor-pointer items-center gap-10 overflow-hidden rounded-xl border-2 border-dashed border-amber-100 bg-amber-50/20 p-10 transition-all hover:bg-amber-50"
                >
                    <div
                        :class="[
                            'relative z-10 flex h-20 w-20 items-center justify-center rounded-3xl shadow-lg transition-all duration-700',
                            form.is_recommended
                                ? 'scale-110 rotate-6 text-white'
                                : 'border-2 border-slate-100 bg-white text-slate-200 group-hover:border-amber-200',
                        ]"
                        :style="
                            form.is_recommended
                                ? { backgroundColor: '#f59e0b' }
                                : {}
                        "
                    >
                        <Sparkles class="h-10 w-10" />
                    </div>
                    <div class="relative z-10 flex-1">
                        <Label
                            class="m-0 cursor-pointer text-sm font-medium tracking-tight text-muted-foreground text-slate-900 uppercase"
                            >Institutional Recommendation</Label
                        >
                        <p
                            class="mt-1 text-xs font-bold tracking-tight text-slate-400 uppercase"
                        >
                            Flag this asset as a High-Depth Reference in the
                            curriculum matrix.
                        </p>
                    </div>
                    <div
                        class="absolute right-0 bottom-0 h-40 w-40 translate-x-1/2 translate-y-1/2 rounded-full bg-amber-200/10 blur-3xl"
                    ></div>
                </div>
            </div>
        </div>

        <!-- Submission Gates -->
        <div
            class="flex flex-col items-center justify-end gap-6 pt-10 pb-20 sm:flex-row"
        >
            <Link
                href="/curriculum/resources"
                class="h-16 rounded-2xl px-10 text-xs font-bold tracking-tight text-slate-400 uppercase transition-colors hover:text-slate-900"
                >Discard Blueprint</Link
            >
            <Button
                type="submit"
                :disabled="form.processing"
                class="flex h-16 transform items-center gap-4 rounded-xl border-0 px-16 text-sm font-bold tracking-[0.4em] text-white uppercase transition-all hover:opacity-90 active:scale-95"
                :style="{
                    backgroundColor: themeColor,
                    boxShadow: `0 20px 25px -5px ${themeColor}4d`,
                }"
            >
                <Sparkles class="h-5 w-5" />
                {{
                    form.processing
                        ? 'SYNCHRONIZING SYLLABUS...'
                        : resource
                          ? 'Update Academic Registry'
                          : 'Publish Asset to Gateway'
                }}
            </Button>
        </div>
    </form>
</template>

<script setup lang="ts">
import { useForm, Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';
import {
    Plus,
    CheckCircle2,
    GraduationCap,
    BookOpen,
    Clock,
    FileText,
    Sparkles,
    UploadCloud,
    Globe,
    Play,
    Image as ImageIcon,
    Music,
    Trash2,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

const props = defineProps<{
    resource?: any;
    subjects: any[];
    grades: any[];
    folders?: any[];
    selectedFolderId?: number | string | null;
}>();

const page = usePage();
const themeColor = computed(
    () => (page.props.auth as any).school?.theme_color || '#1e40af',
);
const themeSecondary = computed(() => themeColor.value + '20');
const themeBorder = computed(() => themeColor.value + '30');

const emit = defineEmits(['success']);

const form = useForm({
    title: props.resource?.title || '',
    resource_type: props.resource?.resource_type || '',
    url: props.resource?.url || '',
    description: props.resource?.description || '',
    subject_id: props.resource?.subject_id || '',
    grade_level_id: props.resource?.grade_level_id || '',
    folder_id: props.resource?.folder_id || props.selectedFolderId || '',
    is_recommended: !!props.resource?.is_recommended,
    author: props.resource?.author || '',
    publisher: props.resource?.publisher || '',
    isbn: props.resource?.isbn || '',
    year_published: props.resource?.year_published || '',
    files: [] as File[],
});

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        form.files = Array.from(target.files);

        // Auto-detect type from first file if not set
        if (!form.resource_type) {
            const ext = target.files[0].name.split('.').pop()?.toLowerCase();
            if (ext === 'pdf') form.resource_type = 'pdf';
            else if (['mp4', 'mov', 'webm'].includes(ext!))
                form.resource_type = 'video';
            else if (['jpg', 'jpeg', 'png', 'webp'].includes(ext!))
                form.resource_type = 'image';
            else if (['mp3', 'wav'].includes(ext!))
                form.resource_type = 'audio';
            else form.resource_type = 'document';
        }
    }
};

const submit = () => {
    if (props.resource) {
        form.transform((data) => ({
            ...data,
            _method: 'put',
        })).post(route('curriculum.resources.update', props.resource.id), {
            onSuccess: () => emit('success'),
        });
    } else {
        form.post(route('curriculum.resources.store'), {
            onSuccess: () => emit('success'),
        });
    }
};

const route = (window as any).route;
</script>

<style scoped>
select {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23cbd5e1' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 1.5rem center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
}
</style>

<style scoped>
select {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 1.25rem center;
    background-repeat: no-repeat;
    background-size: 1.5em 1.5em;
}
</style>
