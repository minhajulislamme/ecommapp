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

// Utility to detect mobile devices
const isMobile = () => {
  return window.innerWidth < 768 || /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)
}

// Get appropriate duration based on device type and variant
const getToastDuration = (variant?: ToastVariant, customDuration?: number) => {
  if (customDuration !== undefined) return customDuration
  
  // All toast notifications auto-hide after 5 seconds
  return 3000
}

export function useToast() {
  const addToast = (toast: Omit<Toast, 'id'>) => {
    const id = Math.random().toString(36).substring(2, 9)
    const duration = getToastDuration(toast.variant, toast.duration)
    
    const newToast: Toast = {
      variant: 'default',
      ...toast,
      id,
      duration: duration,
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

  const clearAllToasts = () => {
    toasts.value = []
  }

  const toast = {
    success: (description: string, title?: string, duration?: number) => 
      addToast({ description, title, variant: 'success', duration }),
    error: (description: string, title?: string, duration?: number) => 
      addToast({ description, title, variant: 'destructive', duration }),
    warning: (description: string, title?: string, duration?: number) => 
      addToast({ description, title, variant: 'warning', duration }),
    info: (description: string, title?: string, duration?: number) => 
      addToast({ description, title, variant: 'default', duration }),
  }

  return {
    toasts,
    addToast,
    removeToast,
    clearAllToasts,
    toast,
    isMobile,
  }
}
