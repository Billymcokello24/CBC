<script setup lang="ts">
import { usePage, router } from '@inertiajs/vue3';
import { onMounted, onUnmounted, watch, ref } from 'vue';
import { Loader2, CheckCircle2, AlertCircle, X } from 'lucide-vue-next';

const page = usePage();
const polling = ref(false);
const intervalId = ref<number | null>(null);
const showFinished = ref(false);
const lastStatus = ref<string | null>(null);

const startPolling = () => {
    if (polling.value) return;
    polling.value = true;
    
    intervalId.value = window.setInterval(() => {
        router.reload({ 
            only: ['auth'], // Only refresh the auth prop which has active_imports
            onSuccess: () => {
                const imports = (page.props.auth as any).active_imports;
                if (!imports || imports.length === 0) {
                    stopPolling();
                    showFinished.value = true;
                    // Full reload after a small delay to update the list
                    setTimeout(() => {
                        router.reload();
                    }, 500);
                    setTimeout(() => {
                        showFinished.value = false;
                    }, 5000);
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
    const imports = (page.props.auth as any).active_imports;
    if (imports && imports.length > 0) {
        startPolling();
    }
});

watch(() => (page.props.auth as any).active_imports, (newImports) => {
    if (newImports && newImports.length > 0) {
        startPolling();
    } else if (polling.value) {
        stopPolling();
    }
}, { deep: true });

onUnmounted(() => {
    stopPolling();
});
</script>

<template>
    <div v-if="polling || showFinished" class="fixed bottom-6 right-6 z-50 w-80">
        <div v-if="polling" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-2xl p-4 flex items-center gap-4 animate-in slide-in-from-bottom-5 duration-300">
            <div class="h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
                <Loader2 class="h-5 w-5 text-blue-600 dark:text-blue-400 animate-spin" />
            </div>
            <div class="flex-grow min-w-0">
                <p class="text-sm font-semibold text-slate-900 dark:text-white truncate">Importing Data...</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">Processing background tasks</p>
                <!-- Progress Bar -->
                <div v-if="(page.props.auth as any).active_imports?.[0]?.total_rows > 0" class="mt-2 h-1.5 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                    <div 
                        class="h-full bg-blue-500 transition-all duration-500" 
                        :style="{ width: `${((page.props.auth as any).active_imports[0].processed_rows / (page.props.auth as any).active_imports[0].total_rows) * 100}%` }"
                    ></div>
                </div>
            </div>
        </div>

        <div v-if="showFinished" class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl shadow-2xl p-4 flex items-center gap-4 animate-in slide-in-from-bottom-5 fade-in duration-300">
            <div class="h-10 w-10 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center flex-shrink-0">
                <CheckCircle2 class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
            </div>
            <div class="flex-grow min-w-0">
                <p class="text-sm font-semibold text-emerald-900 dark:text-emerald-100 truncate">Import Complete!</p>
                <p class="text-xs text-emerald-700 dark:text-emerald-300">Your data is now ready.</p>
            </div>
            <button @click="showFinished = false" class="text-emerald-400 hover:text-emerald-600">
                <X class="h-4 w-4" />
            </button>
        </div>
    </div>
</template>
