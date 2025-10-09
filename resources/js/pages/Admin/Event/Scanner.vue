<script setup lang="ts">
import { ref, onBeforeUnmount } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

const props = defineProps<{
  event: { id: number }
}>()

const code = ref('')
const loading = ref(false)
const error = ref<string | null>(null)
const success = ref<string | null>(null)
let hideTimer: number | null = null

function scheduleHide() {
  if (hideTimer) {
    clearTimeout(hideTimer)
    hideTimer = null
  }
  hideTimer = window.setTimeout(() => {
    error.value = null
    success.value = null
  }, 2500)
}

onBeforeUnmount(() => {
  if (hideTimer) {
    clearTimeout(hideTimer)
    hideTimer = null
  }
})

async function onSubmit(e: Event) {
  e.preventDefault()
  error.value = null
  success.value = null
  if (!code.value.trim()) {
    error.value = 'Please enter a value.'
    scheduleHide()
    return
  }
  loading.value = true
  try {
    const token = (document.head.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content
    const res = await fetch(`/admin/events/${props.event.id}/scanner`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        ...(token ? { 'X-CSRF-TOKEN': token } : {}),
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json, text/plain, */*',
      },
      body: JSON.stringify({ code: code.value })
    })

    // Try to parse JSON if possible, otherwise read text
    let data: any = null
    const text = await res.text()
    try { data = text ? JSON.parse(text) : null } catch { data = { raw: text } }

    if (!res.ok) {
      error.value = (data && (data.message || data.error)) || `Request failed (${res.status})`
    } else {
      success.value = (data && (data.message || 'Submitted')) as string
      // Clear the input on success for quicker subsequent scans
      code.value = ''
    }
  } catch (err: any) {
    error.value = err?.message || 'Network error.'
  } finally {
    loading.value = false
    scheduleHide()
  }
}
</script>

<template>
  <div class="min-h-screen w-full flex items-center justify-center p-4">
    <form class="w-full max-w-md" @submit="onSubmit">
      <div class="flex items-center gap-2">
        <Input v-model="code" placeholder="Scan or enter code" :disabled="loading" />
        <Button type="submit" variant="default" :disabled="loading">
          {{ loading ? 'Submitting…' : 'Submit' }}
        </Button>
      </div>
      <div class="mt-2 text-sm" v-if="error"><span class="text-red-600">{{ error }}</span></div>
      <div class="mt-2 text-sm" v-if="success"><span class="text-green-600">{{ success }}</span></div>
    </form>

    <!-- Big, clear overlay notification for scanner submission -->
    <div
      v-if="error || success"
      class="fixed inset-0 z-50 flex items-center justify-center"
      role="alert"
      aria-live="assertive"
      @click="error = null; success = null"
    >
      <!-- Backdrop -->
      <div class="absolute inset-0 bg-black/50"></div>
      <!-- Message card -->
      <div
        class="relative max-w-lg w-[90%] rounded-2xl shadow-2xl px-8 py-10 text-center border-2"
        :class="error ? 'bg-red-600/95 border-red-300 text-white' : 'bg-green-600/95 border-green-300 text-white'"
      >
        <div class="mx-auto mb-4">
          <svg v-if="success" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-16 h-16">
            <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-2.59a.75.75 0 1 0-1.22-.88l-3.59 4.98-1.74-1.74a.75.75 0 0 0-1.06 1.06l2.4 2.4c.32.32.84.28 1.1-.09l4.11-5.73Z" clip-rule="evenodd" />
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-16 h-16">
            <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm.75 5.25a.75.75 0 0 0-1.5 0v6a.75.75 0 0 0 1.5 0v-6Zm0 9a.75.75 0 1 0-1.5 0 .75.75 0 0 0 1.5 0Z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="text-3xl font-extrabold tracking-wide">
          {{ success || error }}
        </div>
        <div class="mt-2 opacity-90">
          Click anywhere to dismiss
        </div>
      </div>
    </div>
  </div>
</template>
