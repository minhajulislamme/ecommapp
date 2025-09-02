<script setup lang="ts">
import { computed } from 'vue'
import { X, CheckCircle, AlertCircle, AlertTriangle, Info } from 'lucide-vue-next'
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import { Button } from '@/components/ui/button'
import { useToast, type Toast } from '@/composables/useToast'

interface ToastProps {
  toast: Toast
}

const props = defineProps<ToastProps>()
const { removeToast } = useToast()

const icon = computed(() => {
  switch (props.toast.variant) {
    case 'success':
      return CheckCircle
    case 'destructive':
      return AlertCircle
    case 'warning':
      return AlertTriangle
    default:
      return Info
  }
})

const handleClose = () => {
  removeToast(props.toast.id)
}
</script>

<template>
  <Alert 
    :variant="toast.variant" 
    class="mb-3 pr-12 transition-all duration-300 ease-in-out animate-in slide-in-from-right-full
           p-3 sm:p-4 shadow-lg border-l-4
           max-sm:text-sm max-sm:pr-10
           md:max-w-md lg:max-w-lg
           backdrop-blur-sm bg-opacity-95 dark:bg-opacity-95"
  >
    <component 
      :is="icon" 
      class="h-4 w-4 sm:h-5 sm:w-5 
             max-sm:h-4 max-sm:w-4 max-sm:mt-0.5" 
    />
    <AlertTitle 
      v-if="toast.title"
      class="text-sm sm:text-base font-semibold leading-tight
             max-sm:text-sm max-sm:mb-1"
    >
      {{ toast.title }}
    </AlertTitle>
    <AlertDescription 
      class="text-sm sm:text-sm leading-relaxed
             max-sm:text-xs max-sm:leading-normal"
    >
      {{ toast.description }}
    </AlertDescription>
    <Button
      variant="ghost"
      size="sm"
      @click="handleClose"
      class="absolute right-2 top-2 h-6 w-6 p-0 hover:bg-transparent
             max-sm:h-8 max-sm:w-8 max-sm:right-1 max-sm:top-1
             touch-manipulation active:scale-95 transition-transform"
      aria-label="Close notification"
    >
      <X class="h-4 w-4 max-sm:h-5 max-sm:w-5" />
    </Button>
  </Alert>
</template>
