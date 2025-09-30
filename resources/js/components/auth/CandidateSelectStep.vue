<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { User as UserIcon, ShieldCheck } from 'lucide-vue-next';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faLock } from '@fortawesome/free-solid-svg-icons';
import { faGoogle } from '@fortawesome/free-brands-svg-icons';

const props = defineProps<{
  identifier?: string;
  mode?: 'select' | 'create' | null;
  candidates?: Array<{
    id: number;
    name: string;
    email?: string;
    username?: string;
    avatar?: string;
    hasPassword?: boolean;
    google_id?: string;
  }>;
  canCreate?: boolean;
  backLabel?: string;
  guardianMode?: boolean;
}>();

const emit = defineEmits<{
  (e: 'select', user: any): void;
  (e: 'create'): void;
  (e: 'back'): void;
}>();
</script>

<template>
  <div class="flex flex-col gap-6">
    <div v-if="props.identifier" class="text-sm text-muted-foreground">
      <span v-if="props.guardianMode">Guardian identifier:</span>
      <span v-else>You entered:</span>
      <strong>{{ props.identifier }}</strong>
    </div>
    <div v-if="props.guardianMode" class="rounded-md border border-purple-200 bg-purple-50 p-3 text-sm text-purple-800 dark:border-purple-900/50 dark:bg-purple-950/30 dark:text-purple-200">
      <div class="flex items-center gap-2 font-medium">
        <ShieldCheck class="h-4 w-4" />
        Picking a guardian
      </div>
      <p class="mt-1">Select an existing guardian account below, or invite a new guardian if they are not listed.</p>
    </div>

    <template v-if="props.mode === 'select' && props.candidates && props.candidates.length">
      <div class="space-y-2">
        <div class="text-sm text-muted-foreground">{{ props.guardianMode ? 'Select a guardian account' : 'Select an account' }}</div>
        <div class="flex flex-col gap-3">
          <div class="flex flex-col gap-3">
            <template v-for="u in props.candidates" :key="u.id">
              <button
                type="button"
                class="flex w-full items-center justify-between rounded-md border p-3 text-left hover:bg-accent disabled:opacity-60"
                @click="emit('select', u)"
              >
                <div class="flex items-center gap-3">
                  <img v-if="u.avatar" :src="u.avatar" alt="avatar" class="h-6 w-6 rounded-full object-cover" />
                  <UserIcon v-else class="h-6 w-6" />
                  <div>
                    <div class="font-medium">{{ u.name }}</div>
                    <div class="text-xs text-muted-foreground">
                      <span v-if="u.email">{{ u.email }}</span>
                    </div>
                  </div>
                </div>
                <div class="flex items-center gap-2">
                  <div v-if="u.google_id" class="text-2xl" aria-label="Google account">
                    <FontAwesomeIcon :icon="faGoogle" />
                  </div>
                  <div v-else>
                    <font-awesome-icon v-if="u.hasPassword !== false" :icon="faLock" class="text-yellow-500" />
                    <span v-else class="text-xs text-muted-foreground">No password</span>
                  </div>
                </div>
              </button>
            </template>
          </div>
        </div>
      </div>
    </template>

    <div class="flex flex-col gap-2" v-if="props.canCreate || props.mode === 'create'">
      <Button type="button" variant="default" class="w-full" @click="emit('create')">{{ props.guardianMode ? 'Invite a new guardian' : 'Create a new account' }}</Button>
    </div>

    <div>
      <Button type="button" variant="outline" @click="emit('back')">{{ backLabel || 'Back' }}</Button>
    </div>
  </div>
</template>
