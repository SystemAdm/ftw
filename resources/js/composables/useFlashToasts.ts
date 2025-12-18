import { watch } from 'vue'
import { toast } from 'vue-sonner'
import type { Page } from '@inertiajs/core'

// Simple per-session dedupe so we don't show the same flash multiple times
const lastShown: { status?: string | null; error?: string | null } = {}

export function useFlashToasts(page: Page) {
  // Success/status
  watch(
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    () => (page.props as any).status,
    (val) => {
      if (!val) return
      if (lastShown.status === String(val)) return
      lastShown.status = String(val)
      toast.success(String(val))
    },
    { immediate: false }
  )

  // Error
  watch(
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    () => (page.props as any).error,
    (val) => {
      if (!val) return
      if (lastShown.error === String(val)) return
      lastShown.error = String(val)
      toast.error(String(val))
    },
    { immediate: false }
  )
}
