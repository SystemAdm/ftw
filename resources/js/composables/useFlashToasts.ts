import { watch, onMounted } from 'vue'
import { toast } from 'vue-sonner'
import type { Page } from '@inertiajs/core'

// Simple per-session dedupe so we don't show the same flash multiple times
const lastShown: { status?: string | null; error?: string | null } = {}

export function useFlashToasts(page: Page) {
  const showToasts = () => {
    // Success/status
    const status = (page.props as any).status
    if (status && lastShown.status !== String(status)) {
      lastShown.status = String(status)
      toast.success(String(status))
    }

    // Error
    const error = (page.props as any).error
    if (error && lastShown.error !== String(error)) {
      lastShown.error = String(error)
      toast.error(String(error))
    }
  }

  // Handle SPA navigation
  watch(
    () => (page.props as any).status,
    (val) => {
      if (!val) return
      if (lastShown.status === String(val)) return
      lastShown.status = String(val)
      toast.success(String(val))
    }
  )

  watch(
    () => (page.props as any).error,
    (val) => {
      if (!val) return
      if (lastShown.error === String(val)) return
      lastShown.error = String(val)
      toast.error(String(val))
    }
  )

  // Handle initial page load
  onMounted(() => {
    showToasts()
  })
}
