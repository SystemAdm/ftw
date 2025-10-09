<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';
import { dashboard } from '@/routes/admin';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';

interface UserItem { id: number; name: string; email: string; created_at?: string; updated_at?: string; verified_at?: string | null; verified_by?: number | null; verified_by_user?: { id: number; name: string } | null; banned_at?: string | null; banned_to?: string | null; ban_reason?: string | null; is_banned?: boolean }
interface BanItem { id: number; banned_at: string; banned_to?: string | null; reason?: string | null; admin?: { id: number; name: string } | null }

type PageProps = { user: UserItem; bans: BanItem[] };
const page = usePage<PageProps>();
const user = computed(() => page.props.user);
const bans = computed(() => page.props.bans ?? []);

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Admin', href: dashboard().url },
  { title: 'Users', href: '/admin/users' },
  { title: user.value?.name ?? 'User', href: `/admin/users/${user.value?.id}` },
];

function formatDateTime(value?: string | null) {
  if (!value) return '—';
  try {
    return new Date(value).toLocaleString();
  } catch {
    return value ?? '';
  }
}

function verifyUser() {
  if (!user.value?.id) return;
  router.post(`/admin/users/${user.value.id}/verify`, {}, { preserveScroll: true });
}

function unbanUser() {
  if (!user.value?.id) return;
  router.post(`/admin/users/${user.value.id}/unban`, {}, { preserveScroll: true });
}
</script>

<template>
  <Head :title="user?.name ? `${user.name}` : 'User'" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <template #header>
      <div class="flex items-center justify-between flex-wrap gap-3">
        <h1 class="text-xl font-semibold">User: {{ user?.name }}</h1>
        <div class="flex items-center gap-2">
          <Button v-if="!user?.verified_at" size="sm" @click="verifyUser">Verify user</Button>
          <Button v-if="user?.is_banned" size="sm" variant="destructive" @click="unbanUser">Unban</Button>
          <Link :href="`/admin/users/${user?.id}/edit`" class="text-blue-600 hover:underline">Edit</Link>
        </div>
      </div>
    </template>

    <div class="grid gap-4 max-w-xl">
      <div class="rounded-md border p-4">
        <div class="font-medium">Name</div>
        <div>{{ user?.name }}</div>
      </div>
      <div class="rounded-md border p-4">
        <div class="font-medium">Email</div>
        <div>{{ user?.email }}</div>
      </div>
      <div class="rounded-md border p-4">
        <div class="font-medium">Verification</div>
        <div v-if="user?.verified_at">
          Verified at: {{ formatDateTime(user?.verified_at) }}<span v-if="user?.verified_by_user"> by <Link :href="`/admin/users/${user.verified_by_user.id}`" class="text-blue-600 hover:underline">{{ user.verified_by_user.name }}</Link></span>
        </div>
        <div v-else class="flex items-center gap-3">
          <span>Not verified</span>
          <Button size="sm" @click="verifyUser">Verify user</Button>
        </div>
      </div>
      <div class="rounded-md border p-4 grid grid-cols-2 gap-4">
        <div>
          <div class="font-medium">Created</div>
          <div>{{ formatDateTime(user?.created_at) }}</div>
        </div>
        <div>
          <div class="font-medium">Updated</div>
          <div>{{ formatDateTime(user?.updated_at) }}</div>
        </div>
      </div>
    </div>

    <div class="rounded-md border p-4 mt-6 max-w-2xl">
      <div class="font-medium mb-2">Ban history</div>
      <div v-if="(bans as any)?.length === 0" class="text-sm text-muted-foreground">No bans.</div>
      <div v-else class="space-y-3">
        <div v-for="b in bans" :key="b.id" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 border-t pt-3 first:border-t-0 first:pt-0">
          <div>
            <div class="text-sm">Banned at: {{ formatDateTime(b.banned_at as any) }}</div>
            <div class="text-sm text-muted-foreground" v-if="(b.banned_to as any)">Until: {{ formatDateTime(b.banned_to as any) }}</div>
            <div class="text-sm" v-if="b.reason">Reason: {{ b.reason }}</div>
          </div>
          <div class="text-sm text-muted-foreground">
            <template v-if="b.admin">
              by <Link :href="`/admin/users/${b.admin.id}`" class="text-blue-600 hover:underline">{{ b.admin.name }}</Link>
            </template>
            <template v-else>
              by System
            </template>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
