<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';

interface EventItem {
  id: number;
  title: string;
  slug: string;
  excerpt?: string | null;
  description?: string | null;
  image_path?: string | null;
  event_start?: string | null;
  event_end?: string | null;
}

type PageProps = { events: { data: EventItem[] }; filters?: any };
const page = usePage<PageProps>();

const events = computed(() => page.props.events?.data ?? []);
const filters = computed(() => page.props.filters ?? {});

function apply(params: Record<string, any>) {
  const pruned = Object.fromEntries(Object.entries(params).filter(([, v]) => v !== '' && v !== null && v !== undefined));
  router.get('/events', pruned, { preserveScroll: true, preserveState: false, replace: true });
}
</script>

<template>
  <Head title="Events" />
  <AppLayout>
    <div class="mx-auto w-full max-w-5xl p-4">
      <h1 class="mb-4 text-2xl font-semibold">Events</h1>

      <div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-3">
        <div class="md:col-span-2">
          <Label for="q">Search</Label>
          <Input id="q" :value="filters.q ?? ''" @input="(e: any) => apply({ ...filters, q: e.target.value })" />
        </div>
        <div class="flex items-end">
          <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" :checked="!!filters.include_past" @change="(e: any) => apply({ ...filters, include_past: e.target.checked ? 1 : undefined })" />
            Include past events
          </label>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <a v-for="e in events" :key="e.id" class="group overflow-hidden rounded-lg border hover:shadow" :href="`/events/${e.slug}`">
          <div v-if="e.image_path" class="aspect-[16/9] w-full overflow-hidden bg-muted">
            <img :src="e.image_path" alt="" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105" />
          </div>
          <div class="p-4">
            <div class="text-xs text-muted-foreground">
              <span>{{ e.event_start ? new Date(e.event_start).toLocaleString() : '' }}</span>
              <span v-if="e.event_end"> – {{ new Date(e.event_end).toLocaleString() }}</span>
            </div>
            <h2 class="mt-1 text-lg font-semibold">{{ e.title }}</h2>
            <p class="mt-1 line-clamp-3 text-sm text-muted-foreground">{{ e.excerpt || e.description }}</p>
            <div class="mt-3">
              <Button size="sm" as-child><a :href="`/events/${e.slug}`">View details</a></Button>
            </div>
          </div>
        </a>
      </div>

      <div v-if="!events.length" class="py-12 text-center text-muted-foreground">No events found.</div>
    </div>
  </AppLayout>
</template>
