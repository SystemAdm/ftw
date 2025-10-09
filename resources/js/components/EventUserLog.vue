<script setup lang="ts">
import { ref, watch } from 'vue'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import { Button } from '@/components/ui/button'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faClockRotateLeft } from '@fortawesome/free-solid-svg-icons'

interface UserItem { id: number; name: string; email?: string }
const props = defineProps<{ eventId: number; user: UserItem }>()

const open = ref(false)
const loading = ref(false)
const errorMsg = ref('')
const logs = ref<{ id: number; action: 'in' | 'out' | 'attend'; at: string }[]>([])

function format(ts: string) {
  const d = new Date(ts)
  return d.toLocaleString(undefined, {
    year: 'numeric', month: 'short', day: '2-digit',
    hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false
  } as Intl.DateTimeFormatOptions)
}

async function fetchLogs() {
  loading.value = true
  errorMsg.value = ''
  logs.value = []
  try {
    const res = await fetch(`/admin/events/${props.eventId}/users/${props.user.id}/logs`, { headers: { 'Accept': 'application/json' }})
    const data = await res.json()
    if (!res.ok || data.ok === false) throw new Error(data.error || 'Failed to load logs')
    logs.value = Array.isArray(data.logs) ? data.logs : []
  } catch (e: any) {
    errorMsg.value = e?.message || 'Failed to load logs'
  } finally {
    loading.value = false
  }
}

watch(open, (v) => { if (v) fetchLogs() })
</script>

<template>
  <Popover v-model:open="open">
    <PopoverTrigger as-child>
      <Button variant="ghost" size="icon" class="h-7 w-7" title="Show check-in/out log">
        <FontAwesomeIcon :icon="faClockRotateLeft" />
      </Button>
    </PopoverTrigger>
    <PopoverContent class="w-72 p-3" align="end">
      <div class="text-sm font-medium mb-2">Log for {{ props.user.name }}</div>
      <div v-if="loading" class="text-xs text-muted-foreground">Loading…</div>
      <div v-else-if="errorMsg" class="text-xs text-red-600">{{ errorMsg }}</div>
      <ul v-else class="max-h-64 overflow-auto space-y-1">
        <li v-for="entry in logs" :key="entry.id" class="flex items-center justify-between text-xs">
          <span :class="entry.action === 'in' ? 'text-emerald-700' : (entry.action === 'out' ? 'text-red-700' : 'text-slate-700')">{{ entry.action }}</span>
          <span class="ml-2 text-right">{{ format(entry.at) }}</span>
        </li>
        <li v-if="logs.length === 0" class="text-xs text-muted-foreground">No logs yet</li>
      </ul>
    </PopoverContent>
  </Popover>
</template>
