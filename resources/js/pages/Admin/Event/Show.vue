<script setup lang="ts">
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes/admin';
import type { BreadcrumbItem } from '@/types';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faArrowLeft, faPencil, faSkull, faTrash, faCheck } from '@fortawesome/free-solid-svg-icons';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref, onMounted, onBeforeUnmount } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import UserListItem from '@/components/UserListItem.vue';
import EventUserLog from '@/components/EventUserLog.vue';

interface EventItem {
  id: number;
  title: string;
  slug?: string | null;
  excerpt?: string | null;
  description?: string | null;
  description_html?: string | null;
  image_path?: string | null;
  location?: { id: number; name: string } | null;
  event_start?: string | null;
  event_end?: string | null;
  signup_needed?: boolean;
  signup_start?: string | null;
  signup_end?: string | null;
  age_min?: number | null;
  age_max?: number | null;
  number_of_seats?: number | null;
  status?: 'draft' | 'active' | null;
  deleted_at?: string | null;
}

interface UserItem { id: number; name: string; email: string; avatar?: string | null }

const page = usePage<{ event: EventItem; reserved: UserItem[]; attendees: UserItem[]; inside: UserItem[] }>();
const event = computed(() => page.props.event);
const reserved = computed(() => page.props.reserved ?? []);
const attendees = computed(() => page.props.attendees ?? []);
const inside = computed(() => page.props.inside ?? []);

// Local reactive copies for realtime updates
const reservedList = ref<UserItem[]>([...reserved.value]);
const attendeesList = ref<UserItem[]>([...attendees.value]);
const insideList = ref<UserItem[]>([...inside.value]);

let subscribed = false;

onMounted(async () => {
  try {
    const anyWindow = window as any;
    if (!anyWindow.Echo) {
      const { default: Echo } = await import('laravel-echo');
      anyWindow.Echo = new Echo({
        broadcaster: 'reverb',
        key: (import.meta as any).env?.VITE_REVERB_APP_KEY ?? 'app-key',
        wsHost: (import.meta as any).env?.VITE_REVERB_HOST ?? window.location.hostname,
        wsPort: Number((import.meta as any).env?.VITE_REVERB_PORT ?? (window.location.protocol === 'https:' ? 443 : 80)),
        wssPort: Number((import.meta as any).env?.VITE_REVERB_PORT ?? 443),
        forceTLS: window.location.protocol === 'https:',
        enabledTransports: ['ws','wss'],
      });
    }
    const channel = (window as any).Echo.private(`events.${event.value.id}`);
    channel.listen('.EventUpdated', (e: any) => {
      if (Array.isArray(e.reserved)) reservedList.value = e.reserved;
      if (Array.isArray(e.attendees)) attendeesList.value = e.attendees;
      if (Array.isArray(e.inside)) insideList.value = e.inside;
    });
    subscribed = true;
  } catch {
    // no-op: realtime not available
  }
});

onBeforeUnmount(() => {
  try {
    if (subscribed && (window as any).Echo) {
      (window as any).Echo.leave(`private-events.${event.value.id}`);
    }
  } catch {}
});

// Attend modal state
const attendOpen = ref(false);
const identifier = ref('');
const loading = ref(false);
const errorMsg = ref('');
const foundUser = ref<UserItem | null>(null);
const foundCandidates = ref<UserItem[]>([]);
const creating = ref(false);
const newUserName = ref('');

function chooseFoundUser(u: UserItem) {
  foundUser.value = u;
  foundCandidates.value = [];
}

function csrfToken(): string {
  const el = document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null;
  return el?.content || '';
}

async function lookupIdentifier() {
  errorMsg.value = '';
  foundUser.value = null;
  foundCandidates.value = [];
  creating.value = false;
  if (!identifier.value.trim()) {
    errorMsg.value = 'Please enter an email or phone number.';
    return;
  }
  loading.value = true;
  try {
    const res = await fetch(`/admin/events/${event.value.id}/attend/lookup`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
      body: JSON.stringify({ identifier: identifier.value })
    });
    const data = await res.json();
    if (!res.ok || data.ok === false) {
      throw new Error(data.error || 'Lookup failed');
    }
    if (data.found && Array.isArray(data.users) && data.users.length > 1) {
      foundCandidates.value = data.users as UserItem[];
    } else if (data.found && data.user) {
      foundUser.value = data.user as UserItem;
    } else {
      // not found -> ask for name
      creating.value = true;
    }
  } catch (e: any) {
    errorMsg.value = e.message || 'Something went wrong.';
  } finally {
    loading.value = false;
  }
}

async function confirmAttend() {
  errorMsg.value = '';
  loading.value = true;
  try {
    const payload: any = { identifier: identifier.value };
    if (foundUser.value) {
      payload.user_id = foundUser.value.id;
    } else {
      if (!newUserName.value.trim()) {
        errorMsg.value = 'Please enter a name.';
        loading.value = false;
        return;
      }
      payload.name = newUserName.value.trim();
    }
    const res = await fetch(`/admin/events/${event.value.id}/attend`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (!res.ok || data.ok === false) {
      throw new Error(data.error || 'Operation failed');
    }
    // success -> close and refresh
    attendOpen.value = false;
    identifier.value = '';
    foundUser.value = null;
    newUserName.value = '';
    creating.value = false;
    // Reload this page to update the attendees list
    router.visit(window.location.pathname, { preserveScroll: true, preserveState: false, replace: true });
  } catch (e: any) {
    errorMsg.value = e.message || 'Something went wrong.';
  } finally {
    loading.value = false;
  }
}

// Reserve modal state
const reserveOpen = ref(false);
const rIdentifier = ref('');
const rLoading = ref(false);
const rErrorMsg = ref('');
const rFoundUser = ref<UserItem | null>(null);
const rFoundCandidates = ref<UserItem[]>([]);
const rCreating = ref(false);
const rNewUserName = ref('');

function chooseRFoundUser(u: UserItem) {
  rFoundUser.value = u;
  rFoundCandidates.value = [];
}

async function reserveLookupIdentifier() {
  rErrorMsg.value = '';
  rFoundUser.value = null;
  rFoundCandidates.value = [];
  rCreating.value = false;
  if (!rIdentifier.value.trim()) {
    rErrorMsg.value = 'Please enter an email or phone number.';
    return;
  }
  rLoading.value = true;
  try {
    const res = await fetch(`/admin/events/${event.value.id}/reserve/lookup`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
      body: JSON.stringify({ identifier: rIdentifier.value })
    });
    const data = await res.json();
    if (!res.ok || data.ok === false) {
      throw new Error(data.error || 'Lookup failed');
    }
    if (data.found && Array.isArray(data.users) && data.users.length > 1) {
      rFoundCandidates.value = data.users as UserItem[];
    } else if (data.found && data.user) {
      rFoundUser.value = data.user as UserItem;
    } else {
      rCreating.value = true;
    }
  } catch (e: any) {
    rErrorMsg.value = e.message || 'Something went wrong.';
  } finally {
    rLoading.value = false;
  }
}

async function confirmReserve() {
  rErrorMsg.value = '';
  rLoading.value = true;
  try {
    const payload: any = { identifier: rIdentifier.value };
    if (rFoundUser.value) {
      payload.user_id = rFoundUser.value.id;
    } else {
      if (!rNewUserName.value.trim()) {
        rErrorMsg.value = 'Please enter a name.';
        rLoading.value = false;
        return;
      }
      payload.name = rNewUserName.value.trim();
    }
    const res = await fetch(`/admin/events/${event.value.id}/reserve`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (!res.ok || data.ok === false) {
      throw new Error(data.error || 'Operation failed');
    }
    reserveOpen.value = false;
    rIdentifier.value = '';
    rFoundUser.value = null;
    rNewUserName.value = '';
    rCreating.value = false;
    router.visit(window.location.pathname, { preserveScroll: true, preserveState: false, replace: true });
  } catch (e: any) {
    rErrorMsg.value = e.message || 'Something went wrong.';
  } finally {
    rLoading.value = false;
  }
}

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Admin', href: dashboard().url },
  { title: 'Events', href: '/admin/events' },
  { title: event.value?.title ?? 'Show', href: '#' },
];

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

function hasBegun(e: EventItem): boolean {
  if (!e.event_start) return false;
  return new Date() >= new Date(e.event_start);
}

function hasEnded(e: EventItem): boolean {
  if (!e.event_end) return false;
  return new Date() > new Date(e.event_end);
}

function destroyItem(id: number) {
  router.delete(`/admin/events/${id}`, { preserveScroll: true });
}

function restoreItem(id: number) {
  router.post(`/admin/events/${id}/restore`, {}, { preserveScroll: true });
}

function forceDestroyItem(id: number) {
  router.delete(`/admin/events/${id}/force`, { preserveScroll: true });
}

// Remove handlers
const errorOpen = ref(false);
const errorMessage = ref('');
function showError(message: string) { errorMessage.value = message; errorOpen.value = true; }

async function removeReservation(userId: number) {
  try {
    const res = await fetch(`/admin/events/${event.value.id}/reservations/${userId}`, {
      method: 'DELETE',
      headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok || (data && data.ok === false)) throw new Error((data && data.error) || 'Failed to remove.');
    router.visit(window.location.pathname, { preserveScroll: true, preserveState: false, replace: true });
  } catch (e) {
    showError(((e as any).message) || 'Failed to remove');
  }
}

// Copy a reserved user to attendees (does not remove from reservations)
async function makeAttendee(userId: number) {
  try {
    const res = await fetch(`/admin/events/${event.value.id}/reservations/${userId}/attend`, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok || (data && data.ok === false)) throw new Error((data && data.error) || 'Failed to copy.');
    router.visit(window.location.pathname, { preserveScroll: true, preserveState: false, replace: true });
  } catch (e) {
    showError(((e as any).message) || 'Failed to copy user to attendees');
  }
}
async function makeInside(userId: number) {
  try {
    const res = await fetch(`/admin/events/${event.value.id}/attendees/${userId}/inside`, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok || (data && data.ok === false)) throw new Error((data && data.error) || 'Failed to copy.');
    router.visit(window.location.pathname, { preserveScroll: true, preserveState: false, replace: true });
  } catch (e) {
    showError(((e as any).message) || 'Failed to copy user to inside');
  }
}
async function removeAttendee(userId: number) {
  try {
    const res = await fetch(`/admin/events/${event.value.id}/attendees/${userId}`, {
      method: 'DELETE',
      headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok || (data && data.ok === false)) throw new Error((data && data.error) || 'Failed to remove.');
    router.visit(window.location.pathname, { preserveScroll: true, preserveState: false, replace: true });
  } catch (e) {
    showError(((e as any).message) || 'Failed to remove');
  }
}
async function removeInside(userId: number) {
  try {
    const res = await fetch(`/admin/events/${event.value.id}/inside/${userId}`, {
      method: 'DELETE',
      headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok || (data && data.ok === false)) throw new Error((data && data.error) || 'Failed to remove.');
    router.visit(window.location.pathname, { preserveScroll: true, preserveState: false, replace: true });
  } catch (e) {
    showError(((e as any).message) || 'Failed to remove');
  }
}

// Force time actions
async function postAndRefresh(url: string) {
  try {
    const res = await fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' } });
    const data = await res.json().catch(() => ({}));
    if (!res.ok || (data && data.ok === false)) throw new Error((data && data.error) || 'Operation failed');
    router.visit(window.location.pathname, { preserveScroll: true, preserveState: false, replace: true });
  } catch (e) {
    showError(((e as any).message) || 'Operation failed');
  }
}
function forceSignupBegin() { return postAndRefresh(`/admin/events/${event.value.id}/force-signup-begin`); }
function forceSignupEnd() { return postAndRefresh(`/admin/events/${event.value.id}/force-signup-end`); }
function forceEventBegin() { return postAndRefresh(`/admin/events/${event.value.id}/force-event-begin`); }
function forceEventEnd() { return postAndRefresh(`/admin/events/${event.value.id}/force-event-end`); }
function scanner() {
    router.visit(`/admin/events/${event.value.id}/scanner`, { preserveScroll: true, preserveState: false, replace: true });
}
</script>

<template>
  <Head :title="event.title" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
      <div class="relative flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
        <div class="overflow-auto rounded-xl">
          <div class="flex items-center justify-between border-b border-sidebar-border/70 p-4 dark:border-sidebar-border">
            <h1 class="text-xl font-semibold">{{ event.title }}</h1>
            <div class="flex gap-2">
              <Button as-child variant="secondary"><a href="/admin/events"><FontAwesomeIcon :icon="faArrowLeft" class="mr-2"/>Back</a></Button>
              <Button as-child variant="secondary"><a :href="`/admin/events/${event.id}/edit`"><FontAwesomeIcon :icon="faPencil" class="mr-2"/>Edit</a></Button>

              <template v-if="!event.deleted_at">
                <AlertDialog>
                  <AlertDialogTrigger as-child>
                    <Button variant="destructive"><FontAwesomeIcon :icon="faTrash" class="mr-2"/>Delete</Button>
                  </AlertDialogTrigger>
                  <AlertDialogContent>
                    <AlertDialogHeader>
                      <AlertDialogTitle>Delete this event?</AlertDialogTitle>
                      <AlertDialogDescription>
                        This will soft-delete '{{ event.title }}' (ID {{ event.id }}). You can restore it later.
                      </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                      <AlertDialogCancel>Cancel</AlertDialogCancel>
                      <AlertDialogAction as-child>
                        <Button variant="destructive" class="text-white" @click="destroyItem(event.id)">Yes, delete</Button>
                      </AlertDialogAction>
                    </AlertDialogFooter>
                  </AlertDialogContent>
                </AlertDialog>
              </template>
              <template v-else>
                <AlertDialog>
                  <AlertDialogTrigger as-child>
                    <Button variant="secondary">Restore</Button>
                  </AlertDialogTrigger>
                  <AlertDialogContent>
                    <AlertDialogHeader>
                      <AlertDialogTitle>Restore this event?</AlertDialogTitle>
                      <AlertDialogDescription>
                        This will restore '{{ event.title }}' (ID {{ event.id }}).
                      </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                      <AlertDialogCancel>Cancel</AlertDialogCancel>
                      <AlertDialogAction as-child>
                        <Button variant="secondary" @click="restoreItem(event.id)">Yes, restore</Button>
                      </AlertDialogAction>
                    </AlertDialogFooter>
                  </AlertDialogContent>
                </AlertDialog>

                <AlertDialog>
                  <AlertDialogTrigger as-child>
                    <Button variant="destructive"><FontAwesomeIcon :icon="faSkull" class="mr-2"/>Force delete</Button>
                  </AlertDialogTrigger>
                  <AlertDialogContent>
                    <AlertDialogHeader>
                      <AlertDialogTitle>Permanently delete this event?</AlertDialogTitle>
                      <AlertDialogDescription>
                        This action cannot be undone. This will permanently delete '{{ event.title }}' (ID {{ event.id }}).
                      </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                      <AlertDialogCancel>Cancel</AlertDialogCancel>
                      <AlertDialogAction as-child>
                        <Button variant="destructive" class="text-white" @click="forceDestroyItem(event.id)">Yes, delete permanently</Button>
                      </AlertDialogAction>
                    </AlertDialogFooter>
                  </AlertDialogContent>
                </AlertDialog>
              </template>
            </div>
          </div>

          <!-- Attend Dialog -->
          <Dialog v-model:open="attendOpen">
            <DialogContent class="sm:max-w-[480px]">
              <DialogHeader>
                <DialogTitle>Add attendee</DialogTitle>
              </DialogHeader>

              <div class="space-y-3">
                <div>
                  <Label for="identifier">Email or phone</Label>
                  <Input id="identifier" v-model="identifier" type="text" placeholder="name@example.com or +65 8123 4567" />
                </div>

                <p v-if="errorMsg" class="text-sm text-red-600">{{ errorMsg }}</p>

                <!-- Multiple candidates -->
                <div v-if="foundCandidates.length" class="space-y-2">
                  <div class="text-xs text-muted-foreground">Multiple users share this phone. Please select one:</div>
                  <ul class="max-h-48 space-y-2 overflow-auto rounded border p-2">
                    <li v-for="u in foundCandidates" :key="`c-${u.id}`" class="flex items-center justify-between gap-2">
                      <div class="flex min-w-0 items-center gap-3">
                        <div class="h-8 w-8 overflow-hidden rounded-full bg-slate-200 text-slate-600 flex items-center justify-center">
                          <img v-if="(u as any).avatar" :src="(u as any).avatar as any" alt="" class="h-full w-full object-cover" />
                          <span v-else class="text-xs font-medium">{{ (u.name || '?').split(' ').map(p=>p[0]).join('').slice(0,2).toUpperCase() }}</span>
                        </div>
                        <div class="min-w-0">
                          <div class="truncate text-sm font-medium">{{ u.name }}</div>
                          <div class="truncate text-xs text-muted-foreground">{{ u.email }}</div>
                        </div>
                      </div>
                      <Button size="sm" variant="secondary" @click="chooseFoundUser(u)">Select</Button>
                    </li>
                  </ul>
                </div>

                <div v-if="foundUser" class="flex items-center gap-3 rounded border p-2">
                  <div class="h-10 w-10 flex-shrink-0 overflow-hidden rounded-full bg-slate-200 text-slate-600 flex items-center justify-center">
                    <img v-if="(foundUser as any).avatar" :src="(foundUser as any).avatar as any" alt="" class="h-full w-full object-cover" />
                    <span v-else class="text-sm font-medium">{{ (foundUser.name || '?').split(' ').map(p=>p[0]).join('').slice(0,2).toUpperCase() }}</span>
                  </div>
                  <div class="min-w-0">
                    <div class="truncate font-medium">{{ foundUser.name }}</div>
                    <div class="truncate text-xs text-muted-foreground">{{ foundUser.email }}</div>
                  </div>
                </div>

                <div v-if="creating" class="space-y-2">
                  <div>
                    <Label for="newUserName">Name</Label>
                    <Input id="newUserName" v-model="newUserName" placeholder="Enter full name" />
                  </div>
                  <p class="text-xs text-muted-foreground">No user found. Enter a name to create a new user.</p>
                </div>
              </div>

              <DialogFooter>
                <Button variant="secondary" @click="attendOpen = false">Cancel</Button>
                <Button v-if="!foundUser && !creating && foundCandidates.length === 0" :disabled="loading || !identifier" @click="lookupIdentifier">
                  {{ loading ? 'Searching...' : 'Search' }}
                </Button>
                <Button v-else :disabled="loading || (creating && !newUserName) || (!creating && !foundUser)" @click="confirmAttend">
                  {{ loading ? 'Saving...' : 'Confirm' }}
                </Button>
              </DialogFooter>
            </DialogContent>
          </Dialog>

          <!-- Reserve Dialog -->
          <Dialog v-model:open="reserveOpen">
            <DialogContent class="sm:max-w-[480px]">
              <DialogHeader>
                <DialogTitle>Add reservation</DialogTitle>
              </DialogHeader>

              <div class="space-y-3">
                <div>
                  <Label for="r_identifier">Email or phone</Label>
                  <Input id="r_identifier" v-model="rIdentifier" type="text" placeholder="name@example.com or +65 8123 4567" />
                </div>

                <p v-if="rErrorMsg" class="text-sm text-red-600">{{ rErrorMsg }}</p>

                <!-- Multiple candidates -->
                <div v-if="rFoundCandidates.length" class="space-y-2">
                  <div class="text-xs text-muted-foreground">Multiple users share this phone. Please select one:</div>
                  <ul class="max-h-48 space-y-2 overflow-auto rounded border p-2">
                    <li v-for="u in rFoundCandidates" :key="`rc-${u.id}`" class="flex items-center justify-between gap-2">
                      <div class="flex min-w-0 items-center gap-3">
                        <div class="h-8 w-8 overflow-hidden rounded-full bg-slate-200 text-slate-600 flex items-center justify-center">
                          <img v-if="(u as any).avatar" :src="(u as any).avatar as any" alt="" class="h-full w-full object-cover" />
                          <span v-else class="text-xs font-medium">{{ (u.name || '?').split(' ').map(p=>p[0]).join('').slice(0,2).toUpperCase() }}</span>
                        </div>
                        <div class="min-w-0">
                          <div class="truncate text-sm font-medium">{{ u.name }}</div>
                          <div class="truncate text-xs text-muted-foreground">{{ u.email }}</div>
                        </div>
                      </div>
                      <Button size="sm" variant="secondary" @click="chooseRFoundUser(u)">Select</Button>
                    </li>
                  </ul>
                </div>

                <div v-if="rFoundUser" class="flex items-center gap-3 rounded border p-2">
                  <div class="h-10 w-10 flex-shrink-0 overflow-hidden rounded-full bg-slate-200 text-slate-600 flex items-center justify-center">
                    <img v-if="(rFoundUser as any).avatar" :src="(rFoundUser as any).avatar as any" alt="" class="h-full w-full object-cover" />
                    <span v-else class="text-sm font-medium">{{ (rFoundUser.name || '?').split(' ').map(p=>p[0]).join('').slice(0,2).toUpperCase() }}</span>
                  </div>
                  <div class="min-w-0">
                    <div class="truncate font-medium">{{ rFoundUser.name }}</div>
                    <div class="truncate text-xs text-muted-foreground">{{ rFoundUser.email }}</div>
                  </div>
                </div>

                <div v-if="rCreating" class="space-y-2">
                  <div>
                    <Label for="r_newUserName">Name</Label>
                    <Input id="r_newUserName" v-model="rNewUserName" placeholder="Enter full name" />
                  </div>
                  <p class="text-xs text-muted-foreground">No user found. Enter a name to create a new user.</p>
                </div>
              </div>

              <DialogFooter>
                <Button variant="secondary" @click="reserveOpen = false">Cancel</Button>
                <Button v-if="!rFoundUser && !rCreating && rFoundCandidates.length === 0" :disabled="rLoading || !rIdentifier" @click="reserveLookupIdentifier">
                  {{ rLoading ? 'Searching...' : 'Search' }}
                </Button>
                <Button v-else :disabled="rLoading || (rCreating && !rNewUserName) || (!rCreating && !rFoundUser)" @click="confirmReserve">
                  {{ rLoading ? 'Saving...' : 'Confirm' }}
                </Button>
              </DialogFooter>
            </DialogContent>
          </Dialog>

          <!-- Error Alert Dialog -->
          <AlertDialog v-model:open="errorOpen">
            <AlertDialogContent>
              <AlertDialogHeader>
                <AlertDialogTitle>Error</AlertDialogTitle>
                <AlertDialogDescription>{{ errorMessage }}</AlertDialogDescription>
              </AlertDialogHeader>
              <AlertDialogFooter>
                <AlertDialogAction>OK</AlertDialogAction>
              </AlertDialogFooter>
            </AlertDialogContent>
          </AlertDialog>

          <div class="grid grid-cols-1 gap-6 p-4 md:grid-cols-3">
            <div class="md:col-span-1">
              <div class="overflow-hidden rounded-lg border bg-muted/20">
                <img v-if="event.image_path" :src="event.image_path" alt="" class="h-auto w-full object-cover" />
                <div v-else class="flex h-48 items-center justify-center text-muted-foreground">No image</div>
              </div>

              <div class="mt-4 flex flex-wrap gap-2">
                <span v-if="isRegistrationOpen(event)" class="rounded bg-emerald-100 px-2 py-0.5 text-xs text-emerald-700">reg open</span>
                <span v-else-if="event.signup_needed" class="rounded bg-slate-100 px-2 py-0.5 text-xs text-slate-700">reg closed</span>
                <span v-if="hasBegun(event) && !hasEnded(event)" class="rounded bg-blue-100 px-2 py-0.5 text-xs text-blue-700">began</span>
                <span v-if="hasEnded(event)" class="rounded bg-red-100 px-2 py-0.5 text-xs text-red-700">ended</span>
                <span v-if="event.status === 'draft'" class="rounded bg-yellow-100 px-2 py-0.5 text-xs text-yellow-700">draft</span>
                <span v-else-if="event.status === 'active'" class="rounded bg-green-100 px-2 py-0.5 text-xs text-green-700">active</span>
              </div>
            </div>

            <div class="md:col-span-2">
              <div class="space-y-4">
                <div>
                  <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Details</h2>
                    <div class="flex flex-wrap gap-2">
                      <AlertDialog>
                        <AlertDialogTrigger as-child>
                          <Button variant="secondary" size="sm" :disabled="!event.signup_needed" title="Signup not needed" >Force signup begin</Button>
                        </AlertDialogTrigger>
                        <AlertDialogContent>
                          <AlertDialogHeader>
                            <AlertDialogTitle>Force signup begin now?</AlertDialogTitle>
                            <AlertDialogDescription>
                              This sets signup start to the current time.
                            </AlertDialogDescription>
                          </AlertDialogHeader>
                          <AlertDialogFooter>
                            <AlertDialogCancel>Cancel</AlertDialogCancel>
                            <AlertDialogAction as-child>
                              <Button size="sm" variant="secondary" @click="forceSignupBegin">Confirm</Button>
                            </AlertDialogAction>
                          </AlertDialogFooter>
                        </AlertDialogContent>
                      </AlertDialog>

                      <AlertDialog>
                        <AlertDialogTrigger as-child>
                          <Button variant="secondary" size="sm" :disabled="!event.signup_needed" title="Signup not needed" >Force signup end</Button>
                        </AlertDialogTrigger>
                        <AlertDialogContent>
                          <AlertDialogHeader>
                            <AlertDialogTitle>Force signup end now?</AlertDialogTitle>
                            <AlertDialogDescription>
                              This sets signup end to the current time.
                            </AlertDialogDescription>
                          </AlertDialogHeader>
                          <AlertDialogFooter>
                            <AlertDialogCancel>Cancel</AlertDialogCancel>
                            <AlertDialogAction as-child>
                              <Button size="sm" variant="secondary" @click="forceSignupEnd">Confirm</Button>
                            </AlertDialogAction>
                          </AlertDialogFooter>
                        </AlertDialogContent>
                      </AlertDialog>

                      <AlertDialog>
                        <AlertDialogTrigger as-child>
                          <Button variant="secondary" size="sm">Force event begin</Button>
                        </AlertDialogTrigger>
                        <AlertDialogContent>
                          <AlertDialogHeader>
                            <AlertDialogTitle>Force event begin now?</AlertDialogTitle>
                            <AlertDialogDescription>
                              This sets event start to the current time.
                            </AlertDialogDescription>
                          </AlertDialogHeader>
                          <AlertDialogFooter>
                            <AlertDialogCancel>Cancel</AlertDialogCancel>
                            <AlertDialogAction as-child>
                              <Button size="sm" variant="secondary" @click="forceEventBegin">Confirm</Button>
                            </AlertDialogAction>
                          </AlertDialogFooter>
                        </AlertDialogContent>
                      </AlertDialog>

                      <AlertDialog>
                        <AlertDialogTrigger as-child>
                          <Button variant="secondary" size="sm">Force event end</Button>
                        </AlertDialogTrigger>
                        <AlertDialogContent>
                          <AlertDialogHeader>
                            <AlertDialogTitle>Force event end now?</AlertDialogTitle>
                            <AlertDialogDescription>
                              This sets event end to the current time.
                            </AlertDialogDescription>
                          </AlertDialogHeader>
                          <AlertDialogFooter>
                            <AlertDialogCancel>Cancel</AlertDialogCancel>
                            <AlertDialogAction as-child>
                              <Button size="sm" variant="secondary" @click="forceEventEnd">Confirm</Button>
                            </AlertDialogAction>
                          </AlertDialogFooter>
                        </AlertDialogContent>
                      </AlertDialog>
                    </div>
                  </div>
                  <div class="mt-2 grid grid-cols-1 gap-2 md:grid-cols-2">
                    <div>
                      <div class="text-xs text-muted-foreground">Start</div>
                      <div>{{ formatDateTime(event.event_start) }}</div>
                    </div>
                    <div>
                      <div class="text-xs text-muted-foreground">End</div>
                      <div>{{ formatDateTime(event.event_end) }}</div>
                    </div>
                    <div>
                      <div class="text-xs text-muted-foreground">Location</div>
                      <div>{{ event.location?.name ?? '—' }}</div>
                    </div>
                    <div>
                      <div class="text-xs text-muted-foreground">Seats</div>
                      <div>{{ event.number_of_seats ?? '—' }}</div>
                    </div>
                    <div>
                      <div class="text-xs text-muted-foreground">Age range</div>
                      <div>
                        <template v-if="event.age_min != null || event.age_max != null">
                          {{ event.age_min ?? '—' }} - {{ event.age_max ?? '—' }}
                        </template>
                        <template v-else>—</template>
                      </div>
                    </div>
                    <div>
                      <div class="text-xs text-muted-foreground">Signup needed</div>
                      <div>{{ event.signup_needed ? 'yes' : 'no' }}</div>
                    </div>
                    <div v-if="event.signup_needed">
                      <div class="text-xs text-muted-foreground">Signup window</div>
                      <div>{{ formatDateTime(event.signup_start) }} → {{ formatDateTime(event.signup_end) }}</div>
                    </div>
                  </div>
                </div>

                <div>
                    <div class="flex items-center justify-between ">
                        <h2 class="text-lg font-semibold">Participants</h2>
                        <div class="flex gap-2">
                            <Button variant="default" @click="reserveOpen = true">Reserve</Button>
                            <Button variant="default" @click="attendOpen = true">Attend</Button>
                            <Button variant="secondary" @click="scanner">Scanner</Button>
                        </div>
                    </div>

                  <div class="mt-2 grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                      <div class="mb-1 text-xs font-medium uppercase text-muted-foreground">Reserved ({{ reservedList.length }})</div>
                      <ul class="space-y-1">
                        <li v-for="u in reservedList" :key="`r-${u.id}`" class="flex items-center justify-between">
                          <UserListItem :user="u">
                            <EventUserLog :event-id="event.id" :user="u" />
                            <button class="text-emerald-700" title="Copy to attendees" @click="makeAttendee(u.id)">
                              <FontAwesomeIcon :icon="faCheck" />
                            </button>
                            <AlertDialog>
                              <AlertDialogTrigger as-child>
                                <button  class="text-red-600" title="Remove">
                                  <FontAwesomeIcon :icon="faTrash" />
                                </button>
                              </AlertDialogTrigger>
                              <AlertDialogContent>
                                <AlertDialogHeader>
                                  <AlertDialogTitle>Remove from reservations?</AlertDialogTitle>
                                  <AlertDialogDescription>
                                    This will remove {{ u.name }} from the Reserved list.
                                  </AlertDialogDescription>
                                </AlertDialogHeader>
                                <AlertDialogFooter>
                                  <AlertDialogCancel>Cancel</AlertDialogCancel>
                                  <AlertDialogAction as-child>
                                    <Button size="sm" variant="destructive" class="text-white" @click="removeReservation(u.id)">Remove</Button>
                                  </AlertDialogAction>
                                </AlertDialogFooter>
                              </AlertDialogContent>
                            </AlertDialog>
                          </UserListItem>
                        </li>
                        <li v-if="reservedList.length === 0" class="text-muted-foreground">—</li>
                      </ul>
                    </div>
                    <div>
                      <div class="mb-1 text-xs font-medium uppercase text-muted-foreground">Attendees ({{ attendeesList.length }})</div>
                      <ul class="space-y-1">
                        <li v-for="u in attendeesList" :key="`a-${u.id}`" class="flex items-center justify-between">
                          <UserListItem :user="u">
                            <EventUserLog :event-id="event.id" :user="u" />
                            <button  class="text-emerald-700" title="Copy to inside" @click="makeInside(u.id)">
                              <FontAwesomeIcon :icon="faCheck" />
                            </button>
                            <AlertDialog>
                              <AlertDialogTrigger as-child>
                                <button class="text-red-600" title="Remove">
                                  <FontAwesomeIcon :icon="faTrash" />
                                </button>
                              </AlertDialogTrigger>
                              <AlertDialogContent>
                                <AlertDialogHeader>
                                  <AlertDialogTitle>Remove from attendees?</AlertDialogTitle>
                                  <AlertDialogDescription>
                                    This will remove {{ u.name }} from the Attendees list (and Inside, if present).
                                  </AlertDialogDescription>
                                </AlertDialogHeader>
                                <AlertDialogFooter>
                                  <AlertDialogCancel>Cancel</AlertDialogCancel>
                                  <AlertDialogAction as-child>
                                    <Button size="sm" variant="destructive" class="text-white" @click="removeAttendee(u.id)">Remove</Button>
                                  </AlertDialogAction>
                                </AlertDialogFooter>
                              </AlertDialogContent>
                            </AlertDialog>
                          </UserListItem>
                        </li>
                        <li v-if="attendeesList.length === 0" class="text-muted-foreground">—</li>
                      </ul>
                    </div>
                    <div>
                      <div class="mb-1 text-xs font-medium uppercase text-muted-foreground">Inside ({{ insideList.length }})</div>
                      <ul class="space-y-1">
                        <li v-for="u in insideList" :key="`i-${u.id}`" class="flex items-center justify-between">
                          <UserListItem :user="u">
                            <EventUserLog :event-id="event.id" :user="u" />
                            <AlertDialog>
                              <AlertDialogTrigger as-child>
                                <button class="text-red-600" title="Remove">
                                  <FontAwesomeIcon :icon="faTrash" />
                                </button>
                              </AlertDialogTrigger>
                              <AlertDialogContent>
                                <AlertDialogHeader>
                                  <AlertDialogTitle>Remove from inside?</AlertDialogTitle>
                                  <AlertDialogDescription>
                                    This will remove {{ u.name }} from the Inside list.
                                  </AlertDialogDescription>
                                </AlertDialogHeader>
                                <AlertDialogFooter>
                                  <AlertDialogCancel>Cancel</AlertDialogCancel>
                                  <AlertDialogAction as-child>
                                    <Button size="sm" variant="destructive" class="text-white" @click="removeInside(u.id)">Remove</Button>
                                  </AlertDialogAction>
                                </AlertDialogFooter>
                              </AlertDialogContent>
                            </AlertDialog>
                          </UserListItem>
                        </li>
                        <li v-if="insideList.length === 0" class="text-muted-foreground">—</li>
                      </ul>
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
