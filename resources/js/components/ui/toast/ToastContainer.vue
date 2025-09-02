<script setup lang="ts">
import { useToast } from '@/composables/useToast'
import Toast from './Toast.vue'

const { toasts } = useToast()
</script>

<template>
  <Teleport to="body">
    <div 
      v-if="toasts.length > 0"
      class="fixed z-50 space-y-2
             top-4 right-4 max-w-md w-auto
             sm:top-4 sm:right-4 sm:max-w-md sm:w-auto
             md:top-6 md:right-6
             lg:top-4 lg:right-4 lg:max-w-lg
             max-sm:top-2 max-sm:left-2 max-sm:right-2 max-sm:w-auto max-sm:max-w-none
             max-sm:px-safe-area-inset-left max-sm:pr-safe-area-inset-right"
    >
      <TransitionGroup
        name="toast"
        tag="div"
        class="space-y-2"
      >
        <Toast
          v-for="toast in toasts"
          :key="toast.id"
          :toast="toast"
        />
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(100%);
}

.toast-move {
  transition: transform 0.3s ease;
}

/* Mobile-specific animations */
@media (max-width: 640px) {
  .toast-enter-from {
    opacity: 0;
    transform: translateY(-10px) scale(0.95);
  }
  
  .toast-leave-to {
    opacity: 0;
    transform: translateY(-10px) scale(0.95);
  }
  
  .toast-enter-active,
  .toast-leave-active {
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  }
}

/* Enhanced mobile performance */
@media (max-width: 768px) {
  .toast-enter-active,
  .toast-leave-active {
    will-change: transform, opacity;
  }
}
</style>
