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
    class="mb-3 pr-12 transition-all duration-300 ease-in-out animate-in slide-in-from-right-full"
  >
    <component :is="icon" class="h-4 w-4" />
    <AlertTitle v-if="toast.title">{{ toast.title }}</AlertTitle>
    <AlertDescription>{{ toast.description }}</AlertDescription>
    <Button
      variant="ghost"
      size="sm"
      @click="handleClose"
      class="absolute right-2 top-2 h-6 w-6 p-0 hover:bg-transparent"
    >
      <X class="h-4 w-4" />
    </Button>
  </Alert>
</template>
