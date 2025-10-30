<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';
import { dashboard } from '@/routes/admin';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

interface UserItem { id: number; name: string; email: string; avatar?: string | null; birthday?: string | null; postal_code?: { code: string; city: string } | null; created_at?: string; updated_at?: string; verified_at?: string | null; verified_by?: number | null; verified_by_user?: { id: number; name: string } | null; banned_at?: string | null; banned_to?: string | null; ban_reason?: string | null; is_banned?: boolean }
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

function calculateAge(birthday?: string | null): number | null {
  if (!birthday) return null;
  const birthDate = new Date(birthday);
  const today = new Date();
  let age = today.getFullYear() - birthDate.getFullYear();
  const monthDiff = today.getMonth() - birthDate.getMonth();
  if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
    age--;
  }
  return age;
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

    <div class="grid gap-6 max-w-3xl">
      <!-- Basic Information Card -->
      <Card>
        <CardHeader>
          <CardTitle>Basic Information</CardTitle>
        </CardHeader>
        <CardContent class="grid gap-4">
          <div v-if="user?.avatar" class="flex items-center gap-4 pb-4 border-b">
            <img :src="user.avatar" :alt="user.name" class="w-20 h-20 rounded-full object-cover" />
            <div>
              <div class="text-sm font-medium text-muted-foreground">Avatar</div>
              <div class="text-sm text-muted-foreground">{{ user.avatar }}</div>
            </div>
          </div>
          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <div class="text-sm font-medium text-muted-foreground mb-1">Name</div>
              <div class="text-base">{{ user?.name }}</div>
            </div>
            <div>
              <div class="text-sm font-medium text-muted-foreground mb-1">Email</div>
              <div class="text-base">{{ user?.email }}</div>
            </div>
          </div>
          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <div class="text-sm font-medium text-muted-foreground mb-1">Birthday</div>
              <div class="text-base">
                <template v-if="user?.birthday">
                  {{ new Date(user.birthday).toLocaleDateString() }}
                  <span class="text-sm text-muted-foreground ml-2">(Age: {{ calculateAge(user.birthday) }})</span>
                </template>
                <template v-else>—</template>
              </div>
            </div>
            <div>
              <div class="text-sm font-medium text-muted-foreground mb-1">Postal Code</div>
              <div class="text-base">
                <template v-if="user?.postal_code">
                  {{ user.postal_code.code }} - {{ user.postal_code.city }}
                </template>
                <template v-else>—</template>
              </div>
            </div>
          </div>
          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <div class="text-sm font-medium text-muted-foreground mb-1">Created</div>
              <div class="text-sm">{{ formatDateTime(user?.created_at) }}</div>
            </div>
            <div>
              <div class="text-sm font-medium text-muted-foreground mb-1">Last Updated</div>
              <div class="text-sm">{{ formatDateTime(user?.updated_at) }}</div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Status Card -->
      <Card>
        <CardHeader>
          <CardTitle>Account Status</CardTitle>
        </CardHeader>
        <CardContent class="grid gap-4">
          <div>
            <div class="text-sm font-medium text-muted-foreground mb-2">Verification Status</div>
            <div v-if="user?.verified_at" class="flex items-center gap-2">
              <Badge variant="default">Verified</Badge>
              <span class="text-sm text-muted-foreground">
                {{ formatDateTime(user?.verified_at) }}
                <span v-if="user?.verified_by_user">
                  by <Link :href="`/admin/users/${user.verified_by_user.id}`" class="text-blue-600 hover:underline">{{ user.verified_by_user.name }}</Link>
                </span>
              </span>
            </div>
            <div v-else class="flex items-center gap-3">
              <Badge variant="secondary">Not Verified</Badge>
              <Button size="sm" @click="verifyUser">Verify User</Button>
            </div>
          </div>
          <div v-if="user?.is_banned">
            <div class="text-sm font-medium text-muted-foreground mb-2">Ban Status</div>
            <div class="flex items-center gap-2 flex-wrap">
              <Badge variant="destructive">Banned</Badge>
              <span class="text-sm text-muted-foreground">
                Since {{ formatDateTime(user?.banned_at) }}
                <span v-if="user?.banned_to"> until {{ formatDateTime(user?.banned_to) }}</span>
              </span>
            </div>
            <div v-if="user?.ban_reason" class="mt-2 text-sm">
              <span class="font-medium">Reason:</span> {{ user?.ban_reason }}
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Ban History Card -->
      <Card>
        <CardHeader>
          <CardTitle>Ban History</CardTitle>
          <CardDescription>Complete history of all bans for this user</CardDescription>
        </CardHeader>
        <CardContent>
          <div v-if="(bans as any)?.length === 0" class="text-sm text-muted-foreground">No ban records found.</div>
          <div v-else class="space-y-4">
            <div v-for="b in bans" :key="b.id" class="flex flex-col gap-2 pb-4 border-b last:border-b-0 last:pb-0">
              <div class="flex items-start justify-between gap-2">
                <div class="space-y-1">
                  <div class="text-sm font-medium">{{ formatDateTime(b.banned_at as any) }}</div>
                  <div class="text-sm text-muted-foreground" v-if="(b.banned_to as any)">
                    Until: {{ formatDateTime(b.banned_to as any) }}
                  </div>
                  <div class="text-sm" v-if="b.reason">
                    <span class="font-medium">Reason:</span> {{ b.reason }}
                  </div>
                </div>
                <div class="text-sm text-muted-foreground whitespace-nowrap">
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
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
