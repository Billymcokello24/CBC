<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { ref, watch, onMounted, computed } from 'vue';
import { CheckCircle2, AlertCircle, Info, X } from 'lucide-vue-next';

const page = usePage<any>();

const show = ref(false);
const message = ref('');
const type = ref<'success' | 'error' | 'info'>('success');

const timeoutRef = ref<number | null>(null);

const flash = computed(() => page.props.flash || {});

const showToast = (
    msg: string,
    t: 'success' | 'error' | 'info' = 'success',
) => {
    message.value = msg;
    type.value = t;
    show.value = true;

    if (timeoutRef.value) window.clearTimeout(timeoutRef.value);
    timeoutRef.value = window.setTimeout(() => (show.value = false), 5000);
};

// Check for flash messages on mount
onMounted(() => {
    if (flash.value.success) showToast(flash.value.success, 'success');
    else if (flash.value.error) showToast(flash.value.error, 'error');
});

// Watch for changes in flash props (navigation/form submission)
watch(
    () => flash.value,
    (newFlash) => {
        if (newFlash.success) showToast(newFlash.success, 'success');
        else if (newFlash.error) showToast(newFlash.error, 'error');
    },
    { deep: true },
);

const dismiss = () => {
    show.value = false;
    if (timeoutRef.value) {
        window.clearTimeout(timeoutRef.value);
        timeoutRef.value = null;
    }
};
</script>

<template>
    <transition
        enter-active-class="transform ease-out duration-300 transition"
        enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-4"
        enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
        leave-active-class="transition ease-in duration-100"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="show"
            class="pointer-events-auto fixed top-4 right-4 z-[9999] w-full max-w-[240px] overflow-hidden rounded-md border border-slate-200 bg-white/95 shadow-lg backdrop-blur-sm dark:border-slate-800 dark:bg-slate-900/95"
        >
            <div class="px-2.5 py-2">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <CheckCircle2
                            v-if="type === 'success'"
                            class="h-3.5 w-3.5 text-blue-600"
                        />
                        <AlertCircle
                            v-else-if="type === 'error'"
                            class="h-3.5 w-3.5 text-rose-500"
                        />
                        <Info v-else class="h-3.5 w-3.5 text-slate-400" />
                    </div>
                    <div class="ml-2 w-0 flex-1">
                        <p
                            class="text-[8px] font-black tracking-[0.2em] uppercase leading-none"
                            :class="
                                type === 'success'
                                    ? 'text-blue-600'
                                    : type === 'error'
                                      ? 'text-rose-500'
                                      : 'text-slate-400'
                            "
                        >
                            {{
                                type === 'success'
                                    ? 'Success'
                                    : type === 'error'
                                      ? 'Error'
                                      : 'Notification'
                            }}
                        </p>
                        <p
                            class="mt-1 text-[10px] font-medium leading-tight text-slate-600 dark:text-slate-300"
                        >
                            {{ message }}
                        </p>
                    </div>
                    <div class="ml-2 flex flex-shrink-0">
                        <button
                            type="button"
                            @click="dismiss"
                            class="inline-flex rounded-md p-0.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-300"
                        >
                            <X class="h-3 w-3" />
                        </button>
                    </div>
                </div>
            </div>
            <!-- Progress Bar -->
            <div
                v-if="show"
                class="h-0.5 w-full overflow-hidden bg-slate-100 dark:bg-slate-800"
            >
                <div
                    class="h-full animate-[progress_5s_linear_forwards]"
                    :class="
                        type === 'success'
                            ? 'bg-blue-600'
                            : type === 'error'
                               ? 'bg-rose-500'
                               : 'bg-slate-400'
                    "
                ></div>
            </div>
        </div>
    </transition>
</template>

<style scoped>
@keyframes progress {
    from {
        width: 100%;
    }
    to {
        width: 0%;
    }
}
</style>
