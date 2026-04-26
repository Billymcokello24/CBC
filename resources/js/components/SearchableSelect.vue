<script setup lang="ts">
import { ref, computed } from 'vue';
import { Check, ChevronsUpDown, Search } from 'lucide-vue-next';
import { cn } from '@/lib/utils';
import { Button } from '@/components/ui/button';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { Input } from '@/components/ui/input';

interface Option {
    id: string | number;
    name: string;
}

const props = defineProps<{
    options: Option[];
    modelValue?: string | number;
    placeholder?: string;
    searchPlaceholder?: string;
    emptyText?: string;
    class?: string;
}>();

const emit = defineEmits(['update:modelValue']);

const open = ref(false);
const searchQuery = ref('');

const selectedOption = computed(() =>
    props.options.find(
        (opt) => opt.id.toString() === props.modelValue?.toString(),
    ),
);

const filteredOptions = computed(() => {
    if (!searchQuery.value) return props.options;
    return props.options.filter((opt) =>
        opt.name.toLowerCase().includes(searchQuery.value.toLowerCase()),
    );
});

const selectOption = (option: Option) => {
    emit('update:modelValue', option.id.toString());
    open.value = false;
    searchQuery.value = '';
};
</script>

<template>
    <Popover v-model:open="open">
        <PopoverTrigger as-child>
            <Button
                variant="outline"
                role="combobox"
                :aria-expanded="open"
                :class="
                    cn(
                        'w-full justify-between border-indigo-100 font-normal hover:bg-indigo-50/50',
                        props.class,
                    )
                "
            >
                <span class="truncate">{{
                    selectedOption
                        ? selectedOption.name
                        : placeholder || 'Select...'
                }}</span>
                <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
            </Button>
        </PopoverTrigger>
        <PopoverContent
            class="w-[--reka-popover-trigger-width] p-0"
            align="start"
        >
            <div class="flex flex-col">
                <div
                    class="flex items-center border-b bg-indigo-50/30 px-3 py-2"
                >
                    <Search
                        class="mr-2 h-4 w-4 shrink-0 text-indigo-600 opacity-50"
                    />
                    <input
                        v-model="searchQuery"
                        class="flex h-8 w-full rounded-md bg-transparent py-3 text-sm outline-none placeholder:text-muted-foreground disabled:cursor-not-allowed disabled:opacity-50"
                        :placeholder="searchPlaceholder || 'Search...'"
                    />
                </div>
                <div class="max-h-[300px] overflow-y-auto p-1">
                    <div
                        v-if="filteredOptions.length === 0"
                        class="py-6 text-center text-sm text-muted-foreground"
                    >
                        {{ emptyText || 'No results found.' }}
                    </div>
                    <div
                        v-for="option in filteredOptions"
                        :key="option.id"
                        class="relative flex w-full cursor-default items-center rounded-sm py-1.5 pr-2 pl-8 text-sm transition-colors outline-none select-none hover:bg-indigo-600 hover:text-white"
                        @click="selectOption(option)"
                    >
                        <span
                            class="absolute left-2 flex h-3.5 w-3.5 items-center justify-center"
                        >
                            <Check
                                v-if="
                                    modelValue?.toString() ===
                                    option.id.toString()
                                "
                                class="h-4 w-4"
                            />
                        </span>
                        <span class="truncate">{{ option.name }}</span>
                    </div>
                </div>
            </div>
        </PopoverContent>
    </Popover>
</template>
