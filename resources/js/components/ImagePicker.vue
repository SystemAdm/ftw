<script setup lang="ts">
import { ref, watch } from 'vue'
import { Button } from '@/components/ui/button'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'

const props = defineProps<{
  open: boolean
  folder?: string
}>()
const emit = defineEmits<{
  (e: 'update:open', v: boolean): void
  (e: 'select', path: string): void
}>()

const items = ref<Array<{ path: string; url: string; filename: string }>>([])
const loading = ref(false)
const error = ref<string | null>(null)
const selected = ref<string | null>(null)
const fileInput = ref<HTMLInputElement | null>(null)
const uploading = ref(false)

function close() {
  emit('update:open', false)
}

function getCsrfToken(): string {
  const el = document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null
  return el?.content ?? ''
}

async function fetchImages() {
  loading.value = true
  error.value = null
  try {
    const f = encodeURIComponent(props.folder || 'uploads')
    const res = await fetch(`/admin/uploads/images?folder=${f}`, { headers: { 'Accept': 'application/json' } })
    if (!res.ok) throw new Error(await res.text())
    const data = await res.json()
    items.value = Array.isArray(data?.data) ? data.data : []
  } catch (e: any) {
    error.value = e?.message || 'Failed to load images'
  } finally {
    loading.value = false
  }
}

function triggerUpload() {
  fileInput.value?.click()
}

async function onFileChange(e: any) {
  const file = e?.target?.files?.[0]
  if (!file) return
  uploading.value = true
  error.value = null
  try {
    const fd = new FormData()
    fd.append('image', file)
    fd.append('folder', props.folder || 'uploads')
    const res = await fetch('/admin/uploads/images', {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': getCsrfToken() },
      body: fd,
    })
    if (!res.ok) throw new Error(await res.text())
    const data = await res.json()
    // Prepend newly uploaded item
    items.value.unshift({ path: data.path, url: data.url, filename: data.path.split('/').pop() || 'image' })
  } catch (e: any) {
    error.value = e?.message || 'Upload failed'
  } finally {
    uploading.value = false
    e.target.value = ''
  }
}

function confirmSelection() {
  if (selected.value) {
    emit('select', selected.value)
    close()
  }
}

watch(
  () => props.open,
  (v) => {
    if (v) fetchImages()
  },
)
</script>

<template>
  <Dialog :open="open" @update:open="(v:boolean) => emit('update:open', v)">
    <DialogContent class="max-w-3xl">
      <DialogHeader>
        <DialogTitle>Select image</DialogTitle>
        <DialogDescription>
          Choose an existing image or upload a new one.
        </DialogDescription>
      </DialogHeader>

      <div class="space-y-3">
        <div class="flex items-center justify-between gap-2">
          <div class="text-sm text-muted-foreground">Folder: {{ folder || 'uploads' }}</div>
          <div class="flex items-center gap-2">
            <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="onFileChange" />
            <Button variant="secondary" type="button" @click="triggerUpload" :disabled="uploading">
              {{ uploading ? 'Uploading…' : 'Upload new' }}
            </Button>
          </div>
        </div>

        <div v-if="error" class="text-sm text-red-500">{{ error }}</div>
        <div v-if="loading" class="text-sm text-muted-foreground">Loading images…</div>

        <div v-if="!loading && items.length === 0" class="text-sm text-muted-foreground">No images found in this folder.</div>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4" v-if="items.length">
          <button
            v-for="it in items"
            :key="it.path"
            type="button"
            class="group relative overflow-hidden rounded border"
            :class="selected === it.path ? 'ring-2 ring-primary' : ''"
            @click="selected = it.path"
            title="Click to select"
          >
            <img :src="it.path" :alt="it.filename" class="aspect-video w-full object-cover" />
            <div class="absolute inset-x-0 bottom-0 bg-black/40 px-1 py-0.5 text-xs text-white truncate">{{ it.filename }}</div>
          </button>
        </div>
      </div>

      <DialogFooter class="gap-2">
        <Button variant="secondary" type="button" @click="close">Cancel</Button>
        <Button type="button" :disabled="!selected" @click="confirmSelection">Select</Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
