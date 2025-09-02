import { ref, reactive } from 'vue'

export interface Toast {
  id: string
  title?: string
  description: string
  variant?: 'default' | 'destructive' | 'success' | 'warning'
  duration?: number
}

export type ToastVariant = Toast['variant']

const toasts = ref<Toast[]>([])

export function useToast() {
  const addToast = (toast: Omit<Toast, 'id'>) => {
    const id = Math.random().toString(36).substring(2, 9)
    const newToast: Toast = {
      id,
      duration: 5000,
      variant: 'default',
      ...toast,
    }

    toasts.value.push(newToast)

    // Auto remove toast after duration
    if (newToast.duration && newToast.duration > 0) {
      setTimeout(() => {
        removeToast(id)
      }, newToast.duration)
    }

    return id
  }

  const removeToast = (id: string) => {
    const index = toasts.value.findIndex(toast => toast.id === id)
    if (index > -1) {
      toasts.value.splice(index, 1)
    }
  }

  const toast = {
    success: (description: string, title?: string) => addToast({ description, title, variant: 'success' }),
    error: (description: string, title?: string) => addToast({ description, title, variant: 'destructive' }),
    warning: (description: string, title?: string) => addToast({ description, title, variant: 'warning' }),
    info: (description: string, title?: string) => addToast({ description, title, variant: 'default' }),
  }

  return {
    toasts,
    addToast,
    removeToast,
    toast,
  }
}
