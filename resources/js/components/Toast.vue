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
            class="pointer-events-auto fixed top-6 right-6 z-[9999] w-full max-w-[280px] overflow-hidden rounded-lg border border-border bg-background/95 shadow-xl backdrop-blur-sm ring-1 ring-black/5 dark:ring-white/5"
        >
            <div class="px-3 py-2.5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <CheckCircle2
                            v-if="type === 'success'"
                            class="h-4 w-4 text-emerald-500"
                        />
                        <AlertCircle
                            v-else-if="type === 'error'"
                            class="h-4 w-4 text-rose-500"
                        />
                        <Info v-else class="h-4 w-4 text-primary" />
                    </div>
                    <div class="ml-2 w-0 flex-1">
                        <p
                            class="text-[10px] font-black tracking-[0.2em] uppercase leading-none"
                            :class="
                                type === 'success'
                                    ? 'text-emerald-500'
                                    : type === 'error'
                                      ? 'text-rose-500'
                                      : 'text-primary'
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
                            class="mt-1 text-[11px] font-medium leading-tight text-foreground/80"
                        >
                            {{ message }}
                        </p>
                    </div>
                    <div class="ml-3 flex flex-shrink-0">
                        <button
                            type="button"
                            @click="dismiss"
                            class="inline-flex rounded-md p-1 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground focus:outline-none"
                        >
                            <span class="sr-only">Close</span>
                            <X class="h-3.5 w-3.5" />
                        </button>
                    </div>
                </div>
            </div>
            <!-- Progress Bar -->
            <div
                v-if="show"
                class="h-0.5 w-full overflow-hidden bg-muted"
            >
                <div
                    class="h-full animate-[progress_5s_linear_forwards]"
                    :class="
                        type === 'success'
                            ? 'bg-emerald-500'
                            : type === 'error'
                              ? 'bg-rose-500'
                              : 'bg-primary'
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
