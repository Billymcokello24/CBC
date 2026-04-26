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
            class="ring-opacity-5 pointer-events-auto fixed top-6 right-6 z-[9999] w-full max-w-sm overflow-hidden rounded-xl bg-white shadow-lg ring-1 ring-black"
        >
            <div class="p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <CheckCircle2
                            v-if="type === 'success'"
                            class="h-6 w-6 text-emerald-500"
                        />
                        <AlertCircle
                            v-else-if="type === 'error'"
                            class="h-6 w-6 text-rose-500"
                        />
                        <Info v-else class="h-6 w-6 text-sky-500" />
                    </div>
                    <div class="ml-3 w-0 flex-1 pt-0.5">
                        <p
                            class="text-xs font-medium tracking-tight text-muted-foreground uppercase"
                            :class="
                                type === 'success'
                                    ? 'text-emerald-700'
                                    : type === 'error'
                                      ? 'text-rose-700'
                                      : 'text-sky-700'
                            "
                        >
                            {{
                                type === 'success'
                                    ? 'Task Finalized'
                                    : type === 'error'
                                      ? 'System Warning'
                                      : 'Update'
                            }}
                        </p>
                        <p
                            class="mt-1 text-sm leading-relaxed font-bold tracking-tight text-gray-500 uppercase opacity-75"
                        >
                            {{ message }}
                        </p>
                    </div>
                    <div class="ml-4 flex flex-shrink-0">
                        <button
                            type="button"
                            @click="dismiss"
                            class="inline-flex rounded-md bg-white text-gray-400 transition-colors hover:text-gray-500 focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 focus:outline-none"
                        >
                            <span class="sr-only">Close</span>
                            <X class="h-5 w-5" />
                        </button>
                    </div>
                </div>
            </div>
            <!-- Progress Bar -->
            <div
                v-if="show"
                class="h-1 w-full overflow-hidden bg-violet-600/10"
            >
                <div
                    class="h-full animate-[progress_5s_linear_forwards] bg-violet-600"
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
