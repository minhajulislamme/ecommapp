<script setup lang="ts">
import { ref, computed } from 'vue'
import { AlertTriangle, X } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'

interface ConfirmDialogProps {
  isOpen: boolean
  title: string
  description: string
  confirmText?: string
  cancelText?: string
  variant?: 'default' | 'destructive'
}

const props = withDefaults(defineProps<ConfirmDialogProps>(), {
  confirmText: 'Confirm',
  cancelText: 'Cancel',
  variant: 'default'
})

const emit = defineEmits<{
  confirm: []
  cancel: []
  close: []
}>()

const handleConfirm = () => {
  emit('confirm')
  emit('close')
}

const handleCancel = () => {
  emit('cancel')
  emit('close')
}

const handleBackdropClick = (event: MouseEvent) => {
  if (event.target === event.currentTarget) {
    handleCancel()
  }
}
</script>

<template>
  <Teleport to="body">
    <Transition
      name="modal"
      enter-active-class="transition-all duration-300 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-all duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="isOpen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
        @click="handleBackdropClick"
      >
        <Transition
          name="modal-content"
          enter-active-class="transition-all duration-300 ease-out"
          enter-from-class="opacity-0 scale-95 translate-y-4"
          enter-to-class="opacity-100 scale-100 translate-y-0"
          leave-active-class="transition-all duration-200 ease-in"
          leave-from-class="opacity-100 scale-100 translate-y-0"
          leave-to-class="opacity-0 scale-95 translate-y-4"
        >
          <Card v-if="isOpen" class="w-full max-w-md mx-auto shadow-2xl">
            <CardHeader class="pb-4">
              <div class="flex items-start gap-4">
                <div 
                  class="flex h-12 w-12 items-center justify-center rounded-full"
                  :class="variant === 'destructive' 
                    ? 'bg-red-100 dark:bg-red-900/20' 
                    : 'bg-blue-100 dark:bg-blue-900/20'"
                >
                  <AlertTriangle 
                    class="h-6 w-6"
                    :class="variant === 'destructive' 
                      ? 'text-red-600 dark:text-red-400' 
                      : 'text-blue-600 dark:text-blue-400'"
                  />
                </div>
                <div class="flex-1">
                  <CardTitle class="text-lg">{{ title }}</CardTitle>
                  <CardDescription class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    {{ description }}
                  </CardDescription>
                </div>
                <Button
                  variant="ghost"
                  size="sm"
                  @click="handleCancel"
                  class="h-8 w-8 p-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                >
                  <X class="h-4 w-4" />
                </Button>
              </div>
            </CardHeader>
            <CardContent class="pt-0">
              <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <Button
                  variant="outline"
                  @click="handleCancel"
                  class="w-full sm:w-auto"
                >
                  {{ cancelText }}
                </Button>
                <Button
                  :variant="variant === 'destructive' ? 'destructive' : 'default'"
                  @click="handleConfirm"
                  class="w-full sm:w-auto"
                >
                  {{ confirmText }}
                </Button>
              </div>
            </CardContent>
          </Card>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-content-enter-active,
.modal-content-leave-active {
  transition: all 0.3s ease;
}

.modal-content-enter-from,
.modal-content-leave-to {
  opacity: 0;
  transform: scale(0.95) translateY(1rem);
}
</style>
