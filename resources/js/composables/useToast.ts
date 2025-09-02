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
  
  const mobile = isMobile()
  
  // Mobile devices get longer durations for better accessibility
  if (mobile) {
    switch (variant) {
      case 'destructive':
        return 7000 // 7 seconds for errors on mobile
      case 'warning':
        return 6000 // 6 seconds for warnings on mobile
      case 'success':
        return 4000 // 4 seconds for success on mobile
      default:
        return 5000 // 5 seconds for info on mobile
    }
  } else {
    // Desktop gets standard durations
    switch (variant) {
      case 'destructive':
        return 6000 // 6 seconds for errors on desktop
      case 'warning':
        return 5000 // 5 seconds for warnings on desktop
      case 'success':
        return 3000 // 3 seconds for success on desktop
      default:
        return 4000 // 4 seconds for info on desktop
    }
  }
}

export function useToast() {
  const addToast = (toast: Omit<Toast, 'id'>) => {
    const id = Math.random().toString(36).substring(2, 9)
    const newToast: Toast = {
      id,
      duration: getToastDuration(toast.variant, toast.duration),
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
