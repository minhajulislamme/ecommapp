import { usePage } from '@inertiajs/vue3'
import { watchEffect } from 'vue'
import { useToast } from '@/composables/useToast'

interface FlashData {
    success?: string
    error?: string
    warning?: string
    info?: string
    message?: string
    status?: string
}

export function useFlashMessages() {
    const page = usePage()
    const { toast } = useToast()

    watchEffect(() => {
        const flashData = (page.props.flash || {}) as FlashData

        // Handle success messages
        if (flashData.success) {
            toast.success(flashData.success, 'Success')
        }

        // Handle error messages
        if (flashData.error) {
            toast.error(flashData.error, 'Error')
        }

        // Handle warning messages
        if (flashData.warning) {
            toast.warning(flashData.warning, 'Warning')
        }

        // Handle info messages
        if (flashData.info) {
            toast.info(flashData.info, 'Information')
        }

        // Handle generic message (for backward compatibility)
        if (flashData.message) {
            toast.info(flashData.message, 'Notification')
        }

        // Handle status messages (usually from Laravel auth)
        if (flashData.status) {
            toast.info(flashData.status, 'Status')
        }
    })

    return {
        // You can add any additional flash message utilities here
    }
}
