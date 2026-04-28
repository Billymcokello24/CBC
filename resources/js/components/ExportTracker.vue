<script setup lang="ts">
import { usePage, router } from '@inertiajs/vue3';
import { onMounted, onUnmounted, watch, ref, computed } from 'vue';
import { Loader2, CheckCircle2, AlertCircle, X, Download, FileText } from 'lucide-vue-next';

const page = usePage();
const polling = ref(false);
const intervalId = ref<number | null>(null);

const activeExports = computed(() => (page.props.auth as any).active_exports || []);
const recentExports = computed(() => (page.props.auth as any).recent_exports || []);
const finishedExports = ref<number[]>([]);
const witnessedIds = ref<number[]>([]); // IDs clearly started or in progress during this session

const startPolling = () => {
    if (polling.value) return;
    polling.value = true;
    
    intervalId.value = window.setInterval(() => {
        router.reload({ 
            only: ['auth'],
            onSuccess: () => {
                if (activeExports.value.length === 0) {
                    stopPolling();
                }
            }
        });
    }, 3000);
};

const stopPolling = () => {
    if (intervalId.value) {
        clearInterval(intervalId.value);
        intervalId.value = null;
    }
    polling.value = false;
};

onMounted(() => {
    // On load, any active export is considered "witnessed"
    if (activeExports.value.length > 0) {
        witnessedIds.value = activeExports.value.map((e: any) => e.id);
        startPolling();
    }

    // Also listen for new exports started in this window
    window.addEventListener('export-started', (event: any) => {
        if (event.detail && event.detail.id) {
            witnessedIds.value.push(event.detail.id);
            startPolling();
        }
    });
});

watch(activeExports, (newVal) => {
    if (newVal.length > 0) {
        newVal.forEach((e: any) => {
            if (!witnessedIds.value.includes(e.id)) witnessedIds.value.push(e.id);
        });
        startPolling();
    } else if (polling.value) {
        stopPolling();
    }
}, { deep: true });

const downloadFile = (id: number) => {
    window.location.href = `/exports/${id}/download`;
    dismissExport(id);
};

const dismissExport = (id: number) => {
    finishedExports.value.push(id);
};

const visibleRecentExports = computed(() => {
    return recentExports.value.filter((e: any) => 
        witnessedIds.value.includes(e.id) && 
        !finishedExports.value.includes(e.id) && 
        e.status === 'completed'
    );
});

const failedExports = computed(() => {
    return recentExports.value.filter((e: any) => 
        witnessedIds.value.includes(e.id) && 
        !finishedExports.value.includes(e.id) && 
        e.status === 'failed'
    );
});

</script>

<template>
    <div v-if="activeExports.length > 0 || visibleRecentExports.length > 0 || failedExports.length > 0" 
         class="fixed bottom-6 right-6 z-[60] w-80 space-y-3 pointer-events-none">
        
        <!-- Processing Exports -->
        <div v-for="exp in activeExports" :key="exp.id" 
             class="pointer-events-auto bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-2xl p-4 flex items-center gap-4 animate-in slide-in-from-bottom-5 duration-300">
            <div class="h-10 w-10 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center flex-shrink-0">
                <Loader2 class="h-5 w-5 text-indigo-600 dark:text-indigo-400 animate-spin" />
            </div>
            <div class="flex-grow min-w-0">
                <p class="text-sm font-semibold text-slate-900 dark:text-white truncate">Generating {{ exp.type }} PDF...</p>
                <p class="text-[10px] text-slate-500 font-medium uppercase tracking-wider">Preparing Document</p>
            </div>
        </div>

        <!-- Completed Exports (Ready for download) -->
        <div v-for="exp in visibleRecentExports" :key="'ready-' + exp.id" 
             class="pointer-events-auto bg-indigo-600 border border-indigo-500 rounded-xl shadow-2xl p-4 flex items-center gap-4 animate-in slide-in-from-right-5 duration-300">
            <div class="h-10 w-10 rounded-full bg-white/20 flex items-center justify-center flex-shrink-0">
                <FileText class="h-5 w-5 text-white" />
            </div>
            <div class="flex-grow min-w-0 text-white">
                <p class="text-sm font-bold truncate">Report Ready!</p>
                <button @click="downloadFile(exp.id)" class="text-[10px] bg-white/20 hover:bg-white/30 px-2 py-0.5 rounded font-bold uppercase flex items-center gap-1 mt-1">
                    <Download class="h-3 w-3" />
                    Download Now
                </button>
            </div>
            <button @click="dismissExport(exp.id)" class="text-white/60 hover:text-white">
                <X class="h-4 w-4" />
            </button>
        </div>

        <!-- Failed Exports -->
        <div v-for="exp in failedExports" :key="'fail-' + exp.id" 
             class="pointer-events-auto bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-xl shadow-2xl p-4 flex items-center gap-4 animate-in slide-in-from-right-5">
            <div class="h-10 w-10 rounded-full bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center flex-shrink-0">
                <AlertCircle class="h-5 w-5 text-rose-600 dark:text-rose-400" />
            </div>
            <div class="flex-grow min-w-0">
                <p class="text-sm font-semibold text-rose-900 dark:text-rose-100 truncate">Export Failed</p>
                <p class="text-[10px] text-rose-600 truncate">{{ exp.error_message || 'Unexpected Error' }}</p>
            </div>
            <button @click="dismissExport(exp.id)" class="text-rose-400">
                <X class="h-4 w-4" />
            </button>
        </div>
    </div>
</template>
