<script setup lang="ts">
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';

interface EventItem {
  id: number;
  title: string;
  excerpt?: string | null;
  description?: string | null;
  image_path?: string | null;
  event_start?: string | null;
  event_end?: string | null;
  number_of_seats?: number | null;
  age_min?: number | null;
  age_max?: number | null;
  signup_needed?: boolean;
  signup_start?: string | null;
  signup_end?: string | null;
}

type PageProps = { event: EventItem; user_reserved?: boolean; auth?: { user?: any } };
const page = usePage<PageProps>();
const event = computed(() => page.props.event);
const authUser = computed(() => page.props.auth?.user ?? null);
const userReserved = computed(() => page.props.user_reserved ?? false);

function formatDateTime(value?: string | null): string {
  if (!value) return '—';
  const d = new Date(value);
  return d.toLocaleString(undefined, {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    hour12: false,
  } as Intl.DateTimeFormatOptions);
}
function isRegistrationOpen(e: EventItem): boolean {
  if (!e.signup_needed) return false;
  const now = new Date();
  const startOk = e.signup_start ? now >= new Date(e.signup_start) : true;
  const endOk = e.signup_end ? now <= new Date(e.signup_end) : true;
  return startOk && endOk;
}
async function reserve() {
  const csrf = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content;
  await fetch(`/events/${event.value.id}/reserve`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf ?? '' } });
  router.reload({ only: ['event', 'user_reserved'] });
}
async function unreserve() {
  const csrf = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content;
  await fetch(`/events/${event.value.id}/reserve`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf ?? '' } });
  router.reload({ only: ['event', 'user_reserved'] });
}
</script>

<template>
  <Head :title="event.title" />
  <PublicLayout>
    <article class="mx-auto w-full max-w-3xl p-4">
      <div class="mb-4 flex items-center gap-2 text-xs text-muted-foreground">
        <span>{{ event.event_start ? formatDateTime(event.event_start) : '' }}</span>
        <span v-if="event.event_end"> – {{ formatDateTime(event.event_end) }}</span>
        <span v-if="isRegistrationOpen(event)" class="ml-auto inline-flex items-center rounded bg-green-100 px-2 py-0.5 text-[10px] font-medium uppercase tracking-wide text-green-800 dark:bg-green-900/40 dark:text-green-200">
          Registration open
        </span>
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

      <div class="mt-6 rounded-lg border p-4">
        <template v-if="isRegistrationOpen(event)">
          <div v-if="authUser">
            <div class="flex items-center justify-between gap-4">
              <div class="text-sm text-muted-foreground" v-if="!userReserved">Reserve a seat to guarantee entry.</div>
              <div class="text-sm text-muted-foreground" v-else>You have a reservation for this event.</div>
              <div class="shrink-0">
                <Button v-if="!userReserved" @click="reserve">Reserve a seat</Button>
                <Button v-else variant="destructive" class="text-white" @click="unreserve">Cancel reservation</Button>
              </div>
            </div>
          </div>
          <div v-else class="flex items-center justify-between gap-4">
            <div class="text-sm text-muted-foreground">Register to reserve a seat.</div>
            <Button as-child><a href="/register">Register</a></Button>
          </div>
        </template>
        <template v-else>
          <div class="text-sm text-muted-foreground">Registration is currently closed.</div>
        </template>
      </div>
    </article>
  </PublicLayout>
</template>
