// Vue SFC declarations
declare module '*.vue' {
  import type { DefineComponent } from 'vue'
  const component: DefineComponent<{}, {}, any>
  export default component
}

// Toast module declarations
declare module '@/components/ui/toast' {
  export { default as Toast } from './Toast.vue'
  export { default as ToastContainer } from './ToastContainer.vue'
}

declare module '@/components/ui/toast/Toast.vue' {
  import type { DefineComponent } from 'vue'
  const component: DefineComponent<{}, {}, any>
  export default component
}

declare module '@/components/ui/toast/ToastContainer.vue' {
  import type { DefineComponent } from 'vue'
  const component: DefineComponent<{}, {}, any>
  export default component
}

// useToast composable type declarations
declare module '@/composables/useToast' {
  import type { Ref } from 'vue'
  
  export interface Toast {
    id: string
    title?: string
    description: string
    variant?: 'default' | 'destructive' | 'success' | 'warning'
    duration?: number
  }

  export type ToastVariant = Toast['variant']

  export function useToast(): {
    toasts: Ref<Toast[]>
    addToast: (toast: Omit<Toast, 'id'>) => string
    removeToast: (id: string) => void
    toast: {
      success: (description: string, title?: string) => string
      error: (description: string, title?: string) => string
      warning: (description: string, title?: string) => string
      info: (description: string, title?: string) => string
    }
  }
}

// useFlashMessages composable type declarations
declare module '@/composables/useFlashMessages' {
  export function useFlashMessages(): {
    // Add any return types here if needed
  }
}
