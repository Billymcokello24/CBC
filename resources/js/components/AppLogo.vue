<script setup lang="ts">
import { GraduationCap } from 'lucide-vue-next';
</script>

<template>
    <div
        class="flex aspect-square size-8 items-center justify-center rounded-md bg-gradient-to-br from-primary to-blue-600 text-white"
    >
        <GraduationCap class="size-5" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-tight font-semibold"
            >CBC Platform</span
        >
        <span class="truncate text-xs text-muted-foreground"
            >by Doitix Tech</span
        >
    </div>
</template>
