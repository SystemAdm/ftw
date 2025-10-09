<script setup lang="ts">
interface UserItem { id: number; name: string; email: string; avatar?: string | null }

const props = withDefaults(defineProps<{ user: UserItem; showAvatar?: boolean }>(), {
  showAvatar: false,
});
</script>

<template>
  <div class="flex items-center justify-between gap-2">
    <!-- Left: identity -->
    <div v-if="showAvatar" class="flex min-w-0 items-center gap-3">
      <div class="h-8 w-8 overflow-hidden rounded-full bg-slate-200 text-slate-600 flex items-center justify-center">
        <img v-if="(props.user as any).avatar" :src="(props.user as any).avatar as any" alt="" class="h-full w-full object-cover" />
        <span v-else class="text-xs font-medium">{{ (props.user.name || '?').split(' ').map(p=>p[0]).join('').slice(0,2).toUpperCase() }}</span>
      </div>
      <div class="min-w-0">
        <div class="truncate text-sm font-medium">{{ props.user.name }}</div>
      </div>
    </div>
    <div v-else class="min-w-0 truncate">
      <span class="font-medium">{{ props.user.name }}</span>
    </div>

    <!-- Right: actions -->
    <div class="flex items-center gap-1">
      <slot />
    </div>
  </div>
</template>
