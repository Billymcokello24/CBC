<script setup lang="ts">
import { PopoverTrigger } from 'reka-ui'

const props = defineProps<{
  class?: string
}>()
</script>

<template>
  <PopoverTrigger v-bind="props">
    <slot />
  </PopoverTrigger>
</template>
