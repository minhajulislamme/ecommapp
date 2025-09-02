<script setup lang="ts">
import { cn } from '@/lib/utils'

interface AlertDescriptionProps {
  class?: string
}

const props = defineProps<AlertDescriptionProps>()
</script>

<template>
  <div :class="cn('text-sm [&_p]:leading-relaxed', props.class)">
    <slot />
  </div>
</template>
