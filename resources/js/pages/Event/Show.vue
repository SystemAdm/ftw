<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface EventItem {
  id: number;
  title: string;
  slug: string;
  excerpt?: string | null;
  description?: string | null;
  image_path?: string | null;
  event_start?: string | null;
  event_end?: string | null;
  number_of_seats?: number | null;
  age_min?: number | null;
  age_max?: number | null;
}

const page = usePage<{ event: EventItem }>();
const event = computed(() => page.props.event);
</script>

<template>
  <Head :title="event.title" />
  <AppLayout>
    <article class="mx-auto w-full max-w-3xl p-4">
      <div class="mb-4 text-xs text-muted-foreground">
        <span>{{ event.event_start ? new Date(event.event_start).toLocaleString() : '' }}</span>
        <span v-if="event.event_end"> – {{ new Date(event.event_end).toLocaleString() }}</span>
      </div>
      <h1 class="mb-4 text-3xl font-bold">{{ event.title }}</h1>
      <div v-if="event.image_path" class="mb-6 overflow-hidden rounded-lg">
        <img :src="event.image_path" alt="" class="w-full object-cover" />
      </div>
      <p v-if="event.excerpt" class="mb-4 text-lg text-muted-foreground">{{ event.excerpt }}</p>
      <div class="prose prose-neutral max-w-none dark:prose-invert">
        <pre class="whitespace-pre-wrap">{{ event.description }}</pre>
      </div>

      <div class="mt-8 grid grid-cols-1 gap-4 rounded-lg border p-4 md:grid-cols-2">
        <div>
          <div class="text-sm text-muted-foreground">Seats</div>
          <div class="font-medium">{{ event.number_of_seats ?? '—' }}</div>
        </div>
        <div>
          <div class="text-sm text-muted-foreground">Age</div>
          <div class="font-medium">
            <template v-if="event.age_min || event.age_max">
              {{ event.age_min ?? 0 }} – {{ event.age_max ?? '∞' }}
            </template>
            <template v-else>—</template>
          </div>
        </div>
      </div>
    </article>
  </AppLayout>
</template>
