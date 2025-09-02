<script setup lang="ts">
import { useToast } from '@/composables/useToast'
import Toast from '@/components/ui/Toast.vue'

const { toasts } = useToast()
</script>

<template>
  <Teleport to="body">
    <div 
      v-if="toasts.length > 0"
      class="fixed top-4 right-4 z-50 max-w-md w-full space-y-2"
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
</style>
