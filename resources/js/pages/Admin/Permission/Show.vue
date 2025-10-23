<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';
import { dashboard } from '@/routes/admin';

interface Role { id: number; name: string }
interface Permission { id: number; name: string; guard_name: string; created_at: string; updated_at: string }

type PageProps = {
  permission: Permission;
  roles: Role[];
};
const page = usePage<PageProps>();

function formatDate(value?: string) {
  if (!value) return '—';
  try { return new Date(value).toLocaleString(); } catch { return value; }
}

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Admin', href: dashboard().url },
  { title: 'Permissions', href: '/admin/permissions' },
  { title: page.props.permission.name, href: `/admin/permissions/${page.props.permission.id}` },
];
</script>

<template>
  <Head :title="`Permission: ${page.props.permission.name}`" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <template #header>
      <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold">Permission: {{ page.props.permission.name }}</h1>
        <Link :href="`/admin/permissions/${page.props.permission.id}/edit`">
          <Button>Edit Permission</Button>
        </Link>
      </div>
    </template>

    <div class="space-y-6">
      <div class="border rounded-lg p-6 space-y-4">
        <div>
          <h2 class="text-lg font-semibold mb-4">Permission Details</h2>
          <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
              <dt class="text-sm font-medium text-muted-foreground">ID</dt>
              <dd class="mt-1 text-sm">{{ page.props.permission.id }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-muted-foreground">Name</dt>
              <dd class="mt-1 text-sm">{{ page.props.permission.name }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-muted-foreground">Guard</dt>
              <dd class="mt-1 text-sm">{{ page.props.permission.guard_name }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-muted-foreground">Created At</dt>
              <dd class="mt-1 text-sm">{{ formatDate(page.props.permission.created_at) }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-muted-foreground">Updated At</dt>
              <dd class="mt-1 text-sm">{{ formatDate(page.props.permission.updated_at) }}</dd>
            </div>
          </dl>
        </div>
      </div>

      <div class="border rounded-lg p-6">
        <h2 class="text-lg font-semibold mb-4">Roles ({{ page.props.roles.length }})</h2>
        <div v-if="page.props.roles.length > 0" class="flex flex-wrap gap-2">
          <Badge v-for="role in page.props.roles" :key="role.id" variant="secondary">
            {{ role.name }}
          </Badge>
        </div>
        <p v-else class="text-sm text-muted-foreground">No roles have this permission.</p>
      </div>

      <div class="flex items-center gap-2">
        <Link :href="`/admin/permissions/${page.props.permission.id}/edit`">
          <Button>Edit Permission</Button>
        </Link>
        <Link href="/admin/permissions">
          <Button variant="outline">Back to Permissions</Button>
        </Link>
      </div>
    </div>
  </AppLayout>
</template>
