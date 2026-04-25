<script setup lang="ts">
import { ref, onMounted, watch, nextTick, computed } from 'vue';
import { 
    MousePointer2, Pencil, Type, Eraser, 
    ChevronLeft, ChevronRight, Save, Trash2,
    Undo2, RotateCcw, Check, X,
    Maximize2, Info, Loader2, AlertTriangle, 
    ExternalLink, Eye, Layout
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip";

const props = defineProps<{
    images: { id: number; file_path: string; file_type: string; file_name?: string }[];
    initialAnnotations?: any;
    saving?: boolean;
    readOnly?: boolean;
}>();

const emit = defineEmits(['save']);

// State
const currentImageIndex = ref(0);
const currentPdfPage = ref(1);
const totalPdfPages = ref(1);
const tool = ref<'marker' | 'text' | 'pointer' | 'stamp_tick' | 'stamp_cross'>('marker');
const viewMode = ref<'marking' | 'standard'>('marking');
const color = ref('#ef4444');
const brushSize = ref(4);
const canvasRef = ref<HTMLCanvasElement | null>(null);
const ctx = ref<CanvasRenderingContext2D | null>(null);
const isDrawing = ref(false);

// Loading & Visibility State
const isImageLoading = ref(true);
const imageError = ref(false);
const currentImageUrl = ref('');

// Annotations & History
const annotations = ref<any[]>(props.initialAnnotations || []);
const history = ref<any[][]>([]);

// Logic: Coordinates
const getCoordinates = (e: MouseEvent | TouchEvent) => {
    const canvas = canvasRef.value!;
    const rect = canvas.getBoundingClientRect();
    let clientX, clientY;
    
    if (e instanceof MouseEvent) {
        clientX = e.clientX;
        clientY = e.clientY;
    } else {
        clientX = e.touches[0].clientX;
        clientY = e.touches[0].clientY;
    }
    
    return {
        x: (clientX - rect.left) * (canvas.width / rect.width),
        y: (clientY - rect.top) * (canvas.height / rect.height)
    };
};

const saveToHistory = () => {
    history.value.push(JSON.parse(JSON.stringify(annotations.value)));
    if (history.value.length > 30) history.value.shift();
};

const undo = () => {
    if (history.value.length > 0) {
        annotations.value = history.value.pop()!;
        if (viewMode.value === 'marking') redraw();
    }
};

const startDrawing = (e: MouseEvent | TouchEvent) => {
    if (viewMode.value !== 'marking' || tool.value === 'pointer' || isImageLoading.value || imageError.value) return;
    
    const { x, y } = getCoordinates(e);

    if (tool.value === 'stamp_tick' || tool.value === 'stamp_cross') {
        saveToHistory();
        annotations.value.push({
            type: 'stamp',
            stampType: tool.value === 'stamp_tick' ? 'tick' : 'cross',
            x, y,
            color: tool.value === 'stamp_tick' ? '#10b981' : '#ef4444',
            imageIndex: currentImageIndex.value,
            pageIndex: currentPdfPage.value
        });
        redraw();
        return;
    }

    if (tool.value === 'text') {
        const text = prompt('Enter correction comment:');
        if (text) {
            saveToHistory();
            annotations.value.push({
                type: 'text', text, x, y,
                color: color.value,
                imageIndex: currentImageIndex.value,
                pageIndex: currentPdfPage.value
            });
            redraw();
        }
        return;
    }

    if (tool.value === 'marker') {
        saveToHistory();
        isDrawing.value = true;
        ctx.value?.beginPath();
        ctx.value?.moveTo(x, y);
        
        annotations.value.push({
            type: 'path',
            color: color.value,
            size: brushSize.value,
            points: [{ x, y }],
            imageIndex: currentImageIndex.value,
            pageIndex: currentPdfPage.value
        });
    }
};

const draw = (e: MouseEvent | TouchEvent) => {
    if (!isDrawing.value || tool.value !== 'marker') return;
    const { x, y } = getCoordinates(e);
    
    if (ctx.value) {
        ctx.value.lineWidth = brushSize.value;
        ctx.value.lineCap = 'round';
        ctx.value.lineJoin = 'round';
        ctx.value.strokeStyle = color.value;
        ctx.value.lineTo(x, y);
        ctx.value.stroke();
        
        const currentPath = annotations.value[annotations.value.length - 1];
        currentPath.points.push({ x, y });
    }
};

const stopDrawing = () => {
    isDrawing.value = false;
    ctx.value?.closePath();
};

/**
 * Script Injector for PDF.js
 */
const loadPdfJs = () => {
    return new Promise((resolve) => {
        // @ts-ignore
        if (window.pdfjsLib) return resolve(window.pdfjsLib);
        
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js';
        script.onload = () => {
            // @ts-ignore
            const pdfjsLib = window['pdfjs-dist/build/pdf'] || window.pdfjsLib;
            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
            resolve(pdfjsLib);
        };
        document.head.appendChild(script);
    });
};

const redraw = () => {
    return new Promise<void>(async (resolve, reject) => {
        if (viewMode.value !== 'marking' || !ctx.value || !canvasRef.value || !props.images.length) return resolve();
        
        isImageLoading.value = true;
        imageError.value = false;
        
        const canvas = canvasRef.value;
        const exhibit = props.images[currentImageIndex.value];
        const filePath = exhibit.file_path;
        currentImageUrl.value = `/storage/${filePath}`;
        
        // Handle PDF Rendering
        if (filePath.toLowerCase().endsWith('.pdf')) {
            try {
                await renderPdfOnCanvas(currentImageUrl.value, canvas);
                resolve();
            } catch (err) {
                reject(err);
            }
            return;
        }

        // Handle Image Rendering
        const img = new Image();
        img.src = currentImageUrl.value;
        
        img.onload = () => {
            canvas.width = img.naturalWidth;
            canvas.height = img.naturalHeight;
            ctx.value!.clearRect(0, 0, canvas.width, canvas.height);
            ctx.value!.drawImage(img, 0, 0);
            renderAnnotations(canvas.width);
            isImageLoading.value = false;
            resolve();
        };
        
        img.onerror = () => {
            isImageLoading.value = false;
            imageError.value = true;
            reject(new Error('Failed to load image'));
        };
    });
};

const renderPdfOnCanvas = async (url: string, canvas: HTMLCanvasElement) => {
    try {
        const pdfjsLib: any = await loadPdfJs();
        const loadingTask = pdfjsLib.getDocument(url);
        const pdf = await loadingTask.promise;
        totalPdfPages.value = pdf.numPages;
        const page = await pdf.getPage(currentPdfPage.value);

        const viewport = page.getViewport({ scale: 2.0 });
        canvas.height = viewport.height;
        canvas.width = viewport.width;

        const renderContext = {
            canvasContext: ctx.value!,
            viewport: viewport
        };

        await page.render(renderContext).promise;
        renderAnnotations(canvas.width);
        isImageLoading.value = false;
    } catch (error) {
        console.error('PDF Render Error:', error);
        isImageLoading.value = false;
        imageError.value = true;
        throw error;
    }
};

const renderAnnotations = (canvasWidth: number) => {
    annotations.value.filter(a => a.imageIndex === currentImageIndex.value && (a.pageIndex === currentPdfPage.value || !isPdf.value)).forEach(a => {
        ctx.value!.strokeStyle = a.color;
        ctx.value!.fillStyle = a.color;
        
        if (a.type === 'path') {
            ctx.value!.beginPath();
            ctx.value!.lineWidth = a.size;
            ctx.value!.lineCap = 'round';
            ctx.value!.lineJoin = 'round';
            ctx.value!.moveTo(a.points[0].x, a.points[0].y);
            a.points.forEach((p: any) => ctx.value!.lineTo(p.x, p.y));
            ctx.value!.stroke();
        } else if (a.type === 'text') {
            ctx.value!.font = `bold ${Math.max(24, Math.round(canvasWidth / 50))}px sans-serif`;
            const textWidth = ctx.value!.measureText(a.text).width;
            ctx.value!.fillStyle = 'rgba(255, 255, 255, 0.8)';
            ctx.value!.fillRect(a.x - 5, a.y - 30, textWidth + 10, 40);
            ctx.value!.fillStyle = a.color;
            ctx.value!.fillText(a.text, a.x, a.y);
        } else if (a.type === 'stamp') {
            drawStamp(ctx.value!, a.stampType, a.x, a.y, a.color, canvasWidth);
        }
    });
};

const drawStamp = (context: CanvasRenderingContext2D, type: 'tick' | 'cross', x: number, y: number, color: string, canvasWidth: number) => {
    context.save();
    context.translate(x, y);
    context.strokeStyle = color;
    const size = Math.max(15, Math.round(canvasWidth / 60));
    context.lineWidth = Math.max(3, Math.round(size / 3));
    context.lineCap = 'round';
    
    if (type === 'tick') {
        context.beginPath();
        context.moveTo(-size, 0);
        context.lineTo(-size/3, size);
        context.lineTo(size, -size);
        context.stroke();
    } else {
        context.beginPath();
        context.moveTo(-size, -size);
        context.lineTo(size, size);
        context.moveTo(size, -size);
        context.lineTo(-size, size);
        context.stroke();
    }
    context.restore();
};

const nextImage = () => { 
    if (currentImageIndex.value < props.images.length - 1) {
        currentImageIndex.value++;
        currentPdfPage.value = 1;
    }
};

const prevImage = () => { 
    if (currentImageIndex.value > 0) {
        currentImageIndex.value--;
        currentPdfPage.value = 1;
    }
};

const nextPage = () => {
    if (currentPdfPage.value < totalPdfPages.value) {
        currentPdfPage.value++;
    }
};

const prevPage = () => {
    if (currentPdfPage.value > 1) {
        currentPdfPage.value--;
    }
};

const save = () => { emit('save', annotations.value); };

onMounted(() => {
    if (canvasRef.value) {
        ctx.value = canvasRef.value.getContext('2d');
    }
    redraw();
});

watch(currentImageIndex, redraw);
watch(currentPdfPage, redraw);
watch(viewMode, (newMode) => {
    if (newMode === 'marking') {
        nextTick(redraw);
    }
});

const isPdf = computed(() => {
    const exhibit = props.images[currentImageIndex.value];
    return exhibit?.file_path?.toLowerCase().endsWith('.pdf') || exhibit?.file_type?.includes('pdf');
});

const isVideo = computed(() => {
    const exhibit = props.images[currentImageIndex.value];
    const path = exhibit?.file_path?.toLowerCase() || '';
    const type = exhibit?.file_type?.toLowerCase() || '';
    return path.endsWith('.mp4') || path.endsWith('.webm') || path.endsWith('.mov') || type.includes('video');
});

const isAudio = computed(() => {
    const exhibit = props.images[currentImageIndex.value];
    const path = exhibit?.file_path?.toLowerCase() || '';
    const type = exhibit?.file_type?.toLowerCase() || '';
    return path.endsWith('.mp3') || path.endsWith('.wav') || path.endsWith('.ogg') || type.includes('audio');
});

/**
 * Snapshot Engine: Programmatically capture marked canvas for every page/asset
 */
const getFlattenedSnapshots = async () => {
    const snapshots: string[] = [];
    const originalIndex = currentImageIndex.value;
    const originalPage = currentPdfPage.value;

    for (let i = 0; i < props.images.length; i++) {
        currentImageIndex.value = i;
        currentPdfPage.value = 1;

        if (isPdf.value) {
            await redraw();
            const total = totalPdfPages.value;
            for (let p = 1; p <= total; p++) {
                currentPdfPage.value = p;
                await redraw();
                snapshots.push(canvasRef.value!.toDataURL('image/jpeg', 0.8));
            }
        } else {
            await redraw();
            snapshots.push(canvasRef.value!.toDataURL('image/jpeg', 0.8));
        }
    }

    currentImageIndex.value = originalIndex;
    currentPdfPage.value = originalPage;
    await redraw();

    return snapshots;
};

defineExpose({
    getFlattenedSnapshots
});

</script>

<template>
    <div class="flex flex-col h-full bg-slate-900 rounded-3xl overflow-hidden border border-slate-800 shadow-2xl relative">
        
        <!-- Floating Toolbar (Only in Marking Mode) -->
        <div v-if="viewMode === 'marking'" class="absolute left-6 top-1/2 -translate-y-1/2 flex flex-col gap-3 p-3 bg-slate-950/90 backdrop-blur-xl border border-slate-800 rounded-2xl shadow-2xl z-20">
            <TooltipProvider>
                <div class="flex flex-col gap-2">
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <Button variant="ghost" size="icon" :class="['h-10 w-10 rounded-xl transition-all', tool === 'pointer' ? 'bg-blue-600 text-white' : 'text-slate-400 hover:bg-slate-800']" @click="tool = 'pointer'">
                                <MousePointer2 class="h-5 w-5" />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent side="right">Selector</TooltipContent>
                    </Tooltip>
                    
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <Button variant="ghost" size="icon" :class="['h-10 w-10 rounded-xl transition-all', tool === 'marker' ? 'bg-red-600 text-white' : 'text-slate-400 hover:bg-slate-800']" @click="tool = 'marker'">
                                <Pencil class="h-5 w-5" />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent side="right">Pencil Correction</TooltipContent>
                    </Tooltip>
                </div>

                <div class="h-px bg-slate-800 w-full mx-auto my-1"></div>

                <div class="flex flex-col gap-2">
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <Button variant="ghost" size="icon" :class="['h-10 w-10 rounded-xl transition-all text-emerald-500 hover:bg-emerald-500/20', tool === 'stamp_tick' ? 'bg-emerald-600 text-white hover:bg-emerald-600' : '']" @click="tool = 'stamp_tick'">
                                <Check class="h-6 w-6" />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent side="right">Mark Correct</TooltipContent>
                    </Tooltip>

                    <Tooltip>
                        <TooltipTrigger as-child>
                            <Button variant="ghost" size="icon" :class="['h-10 w-10 rounded-xl transition-all text-red-500 hover:bg-red-500/20', tool === 'stamp_cross' ? 'bg-red-600 text-white hover:bg-red-600' : '']" @click="tool = 'stamp_cross'">
                                <X class="h-6 w-6" />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent side="right">Mark Incorrect</TooltipContent>
                    </Tooltip>
                </div>

                <div class="h-px bg-slate-800 w-full mx-auto my-1"></div>

                <div class="flex flex-col gap-2">
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <Button variant="ghost" size="icon" :class="['h-10 w-10 rounded-xl transition-all', tool === 'text' ? 'bg-purple-600 text-white' : 'text-slate-400 hover:bg-slate-800']" @click="tool = 'text'">
                                <Type class="h-5 w-5" />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent side="right">Add Comment</TooltipContent>
                    </Tooltip>
                </div>

                <div class="h-px bg-slate-800 w-full mx-auto my-1"></div>

                <div class="flex flex-col gap-2">
                    <Tooltip>
                        <TooltipTrigger as-child>
                            <Button variant="ghost" size="icon" @click="undo" :disabled="history.length === 0" class="h-10 w-10 rounded-xl text-slate-400 hover:bg-slate-800 disabled:opacity-20">
                                <Undo2 class="h-5 w-5" />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent side="right">Undo Last Action</TooltipContent>
                    </Tooltip>
                </div>
            </TooltipProvider>
        </div>

        <!-- Top Status & Mode Toggle -->
        <div class="absolute inset-x-0 top-0 p-5 bg-gradient-to-b from-slate-900/90 to-transparent flex items-center justify-between pointer-events-none z-30">
             <div class="flex items-center gap-3 pointer-events-auto">
                 <!-- Mode Selector -->
                 <div class="flex items-center bg-white/95 backdrop-blur-md p-1 rounded-xl border border-slate-200 shadow-xl overflow-hidden">
                    <button 
                        @click="viewMode = 'marking'" 
                        :class="['px-5 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all', viewMode === 'marking' ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50']"
                    >
                        <Pencil class="h-3 w-3 inline mr-2" /> Marking Mode
                    </button>
                    <button 
                        @click="viewMode = 'standard'" 
                        :class="['px-5 py-2 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all', viewMode === 'standard' ? 'bg-slate-900 text-white shadow-lg' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50']"
                    >
                        <Eye class="h-3 w-3 inline mr-2" /> Standard View
                    </button>
                 </div>

                 <!-- Palette (Marking Mode Only) -->
                 <div v-if="viewMode === 'marking'" class="flex items-center gap-2 bg-white/95 backdrop-blur-md px-4 py-2 rounded-xl border border-slate-200 shadow-xl ml-2">
                     <button 
                        v-for="c in ['#ef4444', '#10b981', '#3b82f6', '#f59e0b', '#000000']" 
                        :key="c"
                        @click="color = c"
                        :class="['h-6 w-6 rounded-full border-2 transition-transform hover:scale-110 active:scale-95 shadow-sm', color === c ? 'border-slate-900 scale-110' : 'border-transparent']"
                        :style="{ backgroundColor: c }"
                    ></button>
                 </div>
             </div>

             <div class="flex items-center gap-3 pointer-events-auto">
                <!-- Data Pagination (Image/File level) -->
                <div class="flex items-center gap-2 bg-white/95 backdrop-blur-md px-3 py-1.5 rounded-xl border border-slate-200 shadow-xl">
                    <Button variant="ghost" size="icon" @click="prevImage" :disabled="currentImageIndex === 0" class="h-9 w-9 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-900">
                        <ChevronLeft class="h-5 w-5" />
                    </Button>
                    <div class="flex flex-col items-center px-3 border-x border-slate-100">
                        <span class="text-[9px] font-black text-slate-900 uppercase tracking-[0.2em] leading-none mb-1">Asset</span>
                        <span class="text-[11px] font-black text-blue-600 tabular-nums">
                            {{ currentImageIndex + 1 }} / {{ images.length }}
                        </span>
                    </div>
                    <Button variant="ghost" size="icon" @click="nextImage" :disabled="currentImageIndex === images.length - 1" class="h-9 w-9 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-900">
                        <ChevronRight class="h-5 w-5" />
                    </Button>
                </div>

                <!-- PDF PAGE NAVIGATION -->
                <div v-if="isPdf" class="flex items-center gap-2 bg-white/95 backdrop-blur-md px-3 py-1.5 rounded-xl border border-slate-200 shadow-xl">
                    <Button variant="ghost" size="icon" @click="prevPage" :disabled="currentPdfPage === 1" class="h-9 w-9 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-900">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                    <div class="flex flex-col items-center px-4 border-x border-slate-100">
                        <span class="text-[9px] font-black text-slate-900 uppercase tracking-[0.2em] leading-none mb-1">Page</span>
                        <span class="text-[11px] font-black text-indigo-600 tabular-nums">
                             {{ currentPdfPage }} / {{ totalPdfPages }}
                        </span>
                    </div>
                    <Button variant="ghost" size="icon" @click="nextPage" :disabled="currentPdfPage === totalPdfPages" class="h-9 w-9 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-900">
                        <ChevronRight class="h-4 w-4" />
                    </Button>
                </div>

                <Button 
                    v-if="!readOnly"
                    @click="save" 
                    :disabled="saving"
                    class="h-11 px-8 rounded-xl bg-blue-600 hover:bg-blue-700 font-black text-[10px] uppercase tracking-widest text-white border-0 shadow-xl shadow-blue-600/20 disabled:opacity-50 transition-all active:scale-95"
                >
                    <Loader2 v-if="saving" class="mr-2 h-4 w-4 animate-spin" />
                    <Save v-else class="mr-2 h-4 w-4" />
                    {{ saving ? 'Syncing...' : 'Save Marking' }}
                </Button>
                <div v-if="viewMode !== 'marking' || readOnly" class="w-[140px]"></div>
             </div>
        </div>

        <!-- Theater Workspace -->
        <div class="flex-1 relative overflow-hidden bg-slate-100 flex items-center justify-center select-none min-h-0">
            
            <!-- MODE: MARKING (Canvas) -->
            <div v-show="viewMode === 'marking'" 
                class="absolute inset-0 overflow-auto p-12 flex items-center justify-center cursor-crosshair pb-24"
                @mousedown="startDrawing"
                @mousemove="draw"
                @mouseup="stopDrawing"
                @mouseleave="stopDrawing"
                @touchstart.prevent="startDrawing"
                @touchmove.prevent="draw"
                @touchend.prevent="stopDrawing"
            >
                <div v-if="isImageLoading" class="absolute inset-0 flex flex-col items-center justify-center bg-slate-50/80 backdrop-blur-sm z-30">
                    <div class="h-16 w-16 rounded-2xl bg-white shadow-2xl flex items-center justify-center mb-6">
                        <Loader2 class="h-8 w-8 text-blue-600 animate-spin" />
                    </div>
                    <p class="text-[10px] font-black text-slate-900 uppercase tracking-[0.3em]">Preparing Asset...</p>
                </div>

                <div v-if="imageError" class="flex flex-col items-center justify-center text-center p-12 z-40 max-w-sm bg-white rounded-3xl shadow-3xl border border-red-50">
                    <div class="h-20 w-20 rounded-full bg-red-50 text-red-500 flex items-center justify-center mb-6">
                        <AlertTriangle class="h-10 w-10" />
                    </div>
                    <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight mb-2">Display Breach</h3>
                    <p class="text-xs text-slate-500 font-medium mb-8">The theatre engine failed to draw this asset. Use "Standard View" to access the raw file.</p>
                    <Button variant="secondary" @click="viewMode = 'standard'" class="bg-blue-600 text-white rounded-xl h-11 px-8 active:scale-95 transition-all">Switch to Standard View</Button>
                </div>

                <canvas 
                    ref="canvasRef" 
                    class="shadow-[0_40px_100px_rgba(0,0,0,0.15)] bg-white max-w-full h-auto object-contain rounded-xl border border-slate-200"
                    style="max-height: 85vh;"
                    v-show="!isImageLoading && !imageError"
                ></canvas>
            </div>

            <!-- MODE: STANDARD (Learning Resource Style) -->
            <div v-if="viewMode === 'standard'" class="absolute inset-0 flex items-center justify-center bg-slate-50 pt-16">
                  <div class="w-full h-full max-w-[1200px] max-h-[92%] mx-auto bg-white shadow-2xl rounded-2xl overflow-hidden border border-slate-200 flex items-center justify-center">
                    <template v-if="isPdf">
                        <embed :src="`/storage/${images[currentImageIndex].file_path}#page=${currentPdfPage}`" type="application/pdf" class="w-full h-full" />
                    </template>
                    <template v-else-if="isVideo">
                        <video 
                            :src="`/storage/${images[currentImageIndex].file_path}`" 
                            controls 
                            class="max-w-full max-h-full"
                        ></video>
                    </template>
                    <template v-else-if="isAudio">
                        <div class="flex flex-col items-center gap-8 p-16 w-full max-w-lg bg-slate-50 rounded-[2.5rem] border border-slate-100 shadow-inner">
                            <div class="h-24 w-24 rounded-[2rem] bg-blue-600 text-white shadow-2xl shadow-blue-600/30 flex items-center justify-center">
                                <Info class="h-12 w-12" />
                            </div>
                            <div class="w-full space-y-4">
                                <audio 
                                    :src="`/storage/${images[currentImageIndex].file_path}`" 
                                    controls 
                                    class="w-full h-12"
                                ></audio>
                                <p class="text-center text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ images[currentImageIndex].file_name || 'Oral Submission Recording' }}</p>
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <div class="w-full h-full overflow-auto flex items-center justify-center p-8 bg-slate-50">
                            <img :src="`/storage/${images[currentImageIndex].file_path}`" class="max-w-full max-h-full object-contain shadow-2xl rounded-lg" />
                        </div>
                    </template>
                  </div>
            </div>

        </div>

        <!-- Help Context -->
        <div v-if="viewMode === 'marking' && !isImageLoading && !imageError" class="absolute bottom-8 left-1/2 -translate-x-1/2 px-6 py-4 bg-white/90 backdrop-blur-xl rounded-2xl border border-slate-200 shadow-2xl flex items-center gap-10 z-20 pointer-events-none">
            <span class="text-[10px] font-bold text-slate-800 uppercase tracking-widest flex items-center gap-3">
                <div class="h-2 w-2 rounded-full bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.5)]"></div> Correct
            </span>
            <div class="w-px h-4 bg-slate-200"></div>
            <span class="text-[10px] font-bold text-slate-800 uppercase tracking-widest flex items-center gap-3">
                <div class="h-2 w-2 rounded-full bg-red-500 shadow-[0_0_10px_rgba(239,68,68,0.5)]"></div> Incorrect
            </span>
            <div v-if="isPdf" class="w-px h-4 bg-slate-200"></div>
            <span v-if="isPdf" class="text-[10px] font-bold text-blue-600 uppercase tracking-widest flex items-center gap-3 italic">
                 Page {{ currentPdfPage }} / {{ totalPdfPages }}
            </span>
        </div>
    </div>
</template>

<style scoped>
.select-none {
    user-select: none;
    -webkit-user-select: none;
}
canvas {
    touch-action: none;
}
</style>
