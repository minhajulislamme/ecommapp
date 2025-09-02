<script setup lang="ts">
import { computed } from 'vue'
import { cva, type VariantProps } from 'class-variance-authority'
import { cn } from '@/lib/utils'

const alertVariants = cva(
  "relative w-full rounded-lg border p-4 [&>svg~*]:pl-7 [&>svg+div]:translate-y-[-3px] [&>svg]:absolute [&>svg]:left-4 [&>svg]:top-4 [&>svg]:text-foreground",
  {
    variants: {
      variant: {
        default: "bg-background text-foreground",
        destructive:
          "border-destructive/50 text-destructive dark:border-destructive [&>svg]:text-destructive",
        success:
          "border-green-500/50 text-green-700 bg-green-50 dark:border-green-500 dark:text-green-400 dark:bg-green-900/20 [&>svg]:text-green-600 dark:[&>svg]:text-green-400",
        warning:
          "border-yellow-500/50 text-yellow-700 bg-yellow-50 dark:border-yellow-500 dark:text-yellow-400 dark:bg-yellow-900/20 [&>svg]:text-yellow-600 dark:[&>svg]:text-yellow-400",
      },
    },
    defaultVariants: {
      variant: "default",
    },
  }
)

interface AlertProps {
  variant?: VariantProps<typeof alertVariants>['variant']
  class?: string
}

const props = withDefaults(defineProps<AlertProps>(), {
  variant: 'default'
})

const alertClass = computed(() => {
  return cn(alertVariants({ variant: props.variant }), props.class)
})
</script>

<template>
  <div
    role="alert"
    :class="alertClass"
  >
    <slot />
  </div>
</template>
