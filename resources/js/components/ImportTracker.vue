<script setup lang="ts">
import { usePage, router } from '@inertiajs/vue3';
import { onMounted, onUnmounted, watch, ref, computed } from 'vue';
import { Loader2, CheckCircle2, AlertCircle, X, History, Info, StopCircle } from 'lucide-vue-next';

const page = usePage();
const polling = ref(false);
const intervalId = ref<number | null>(null);
const showFinished = ref(false);
const showError = ref(false);
const watchedIds = ref<number[]>([]);

const activeImports = computed(() => (page.props.auth as any).active_imports || []);
const recentImports = computed(() => (page.props.auth as any).recent_imports || []);
const failedImports = computed(() => recentImports.value.filter((i: any) => i.status === 'failed'));

const startPolling = () => {
    if (polling.value) return;
    polling.value = true;
    
    intervalId.value = window.setInterval(() => {
        router.reload({ 
            only: ['auth'],
            onSuccess: () => {
                if (activeImports.value.length === 0) {
                    stopPolling();
                    
                    // Check if any of the IDs we were watching ended up as failures
                    const batchFailures = recentImports.value.filter((i: any) => 
                        watchedIds.value.includes(i.id) && i.status === 'failed'
                    );

                    if (batchFailures.length > 0) {
                        showError.value = true;
                        // For errors, maybe keep them a bit longer than 3s if multiple?
                        setTimeout(() => showError.value = false, 8000);
                    } else {
                        showFinished.value = true;
                        setTimeout(() => showFinished.value = false, 5000);
                    }
                    
                    watchedIds.value = []; // Reset
                    setTimeout(() => router.reload(), 500);
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
    if (activeImports.value.length > 0) {
        watchedIds.value = activeImports.value.map((i: any) => i.id);
        startPolling();
    }
});

watch(activeImports, (newImports) => {
    if (newImports.length > 0) {
        newImports.forEach((i: any) => {
            if (!watchedIds.value.includes(i.id)) watchedIds.value.push(i.id);
        });
        startPolling();
    } else if (polling.value) {
        stopPolling();
    }
}, { deep: true });

// Show error if a direct page load (non-polling) detects a NEW failure
watch(failedImports, (newVal: any[], oldVal: any[]) => {
    if (newVal.length > (oldVal?.length || 0)) {
        showError.value = true;
        setTimeout(() => showError.value = false, 8000);
    }
}, { deep: true });

const cancelImport = (id: number) => {
    if (confirm('Are you sure you want to stop this upload? Any already processed records will remain.')) {
        router.post(`/imports/${id}/cancel`, {}, {
            onSuccess: () => {
                router.reload({ only: ['auth'] });
            }
        });
    }
};

onUnmounted(() => stopPolling());
</script>

<template>
    <div v-if="activeImports.length > 0 || showFinished || showError" class="fixed bottom-6 right-6 z-50 w-80 space-y-3">
        <!-- Active Imports -->
        <div v-for="imp in activeImports" :key="imp.id" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-2xl p-4 flex items-center gap-4 animate-in slide-in-from-bottom-5 duration-300">
            <div class="h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
                <Loader2 class="h-5 w-5 text-blue-600 dark:text-blue-400 animate-spin" />
            </div>
            <div class="flex-grow min-w-0">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white truncate">Syncing {{ imp.type }}s...</p>
                    <button 
                        @click="cancelImport(imp.id)" 
                        class="h-6 w-6 rounded-md flex items-center justify-center text-slate-400 hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-900/30 transition-all"
                        title="Stop Upload"
                    >
                        <StopCircle class="h-3.5 w-3.5" />
                    </button>
                </div>
                <p class="text-[10px] text-slate-500 font-medium uppercase tracking-wider">Processing Row {{ imp.processed_rows }} of {{ imp.total_rows }}</p>
                <div v-if="imp.total_rows > 0" class="mt-2 h-1.5 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                    <div class="h-full bg-blue-500 transition-all duration-500" :style="{ width: `${(imp.processed_rows / imp.total_rows) * 100}%` }"></div>
                </div>
            </div>
        </div>

        <!-- Recent Failures (Showing error_message) -->
        <div v-if="showError" v-for="imp in failedImports" :key="'fail-' + imp.id" class="bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-xl shadow-2xl p-4 flex flex-col gap-3 animate-in fade-in slide-in-from-right-5">
            <div class="flex items-center gap-4">
                <div class="h-10 w-10 rounded-full bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center flex-shrink-0">
                    <AlertCircle class="h-5 w-5 text-rose-600 dark:text-rose-400" />
                </div>
                <div class="flex-grow min-w-0">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold text-rose-900 dark:text-rose-100 truncate">Sync Failure: {{ imp.type }}</p>
                        <button @click="showError = false" class="text-rose-400 hover:text-rose-600">
                            <X class="h-4 w-4" />
                        </button>
                    </div>
                    <p class="text-[10px] text-rose-700 dark:text-rose-300 font-bold uppercase">Critical Conflict Detected</p>
                </div>
            </div>
            <div v-if="imp.error_message" class="text-[11px] bg-white/50 dark:bg-black/20 p-2 rounded-lg border border-rose-100 dark:border-rose-800 font-medium text-rose-800 dark:text-rose-200 max-h-24 overflow-y-auto custom-scrollbar">
                {{ imp.error_message }}
            </div>
        </div>

        <!-- Finished Success -->
        <div v-if="showFinished" class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl shadow-2xl p-4 flex items-center gap-4 animate-in slide-in-from-bottom-5 fade-in duration-300">
            <div class="h-10 w-10 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center flex-shrink-0">
                <CheckCircle2 class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
            </div>
            <div class="flex-grow min-w-0">
                <p class="text-sm font-semibold text-emerald-900 dark:text-emerald-100 truncate">Sync Complete!</p>
                <p class="text-xs text-emerald-700 dark:text-emerald-300">Data successfully ingested.</p>
            </div>
            <button @click="showFinished = false" class="text-emerald-400 hover:text-emerald-600">
                <X class="h-4 w-4" />
            </button>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(225, 29, 72, 0.2);
    border-radius: 10px;
}
</style>
