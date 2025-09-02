<template>
  <textarea
    :class="cn(
      'flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-0 focus-visible:ring-transparent focus-visible:ring-offset-0 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
      props.class
    )"
    v-bind="$attrs"
    :value="modelValue"
    @input="$emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
  />
</template>

<script setup lang="ts">
import { cn } from '@/lib/utils'

interface Props {
  modelValue?: string
  class?: string
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: '',
  class: ''
})

defineEmits<{
  'update:modelValue': [value: string]
}>()
</script>
