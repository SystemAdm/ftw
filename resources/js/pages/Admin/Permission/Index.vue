<script setup lang="ts">
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, usePage, Link } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';
import { dashboard } from '@/routes/admin';
import { computed, ref } from 'vue';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogClose } from '@/components/ui/dialog';

interface PermissionItem { id: number; name: string; guard_name: string; created_at?: string; roles_count?: number }

type PageProps = {
  permissions: { data: PermissionItem[]; current_page: number; last_page: number; per_page: number; total: number };
  filters?: any;
  sort?: any;
};
const page = usePage<PageProps>();

const permissions = computed(() => page.props.permissions?.data ?? []);
const currentFilters = computed(() => page.props.filters ?? {});
const currentSort = computed(() => page.props.sort ?? { by: 'name', dir: 'asc' });

const search = ref(currentFilters.value.search ?? '');

const showDelete = ref(false);
const deletePermissionId = ref<number | null>(null);
const deletePermissionName = ref('');

function openDelete(permission: PermissionItem) {
  deletePermissionId.value = permission.id;
  deletePermissionName.value = permission.name;
  showDelete.value = true;
}

async function confirmDelete() {
  if (!deletePermissionId.value) return;
  await router.delete(`/admin/permissions/${deletePermissionId.value}`, { preserveScroll: true });
  showDelete.value = false;
}

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Admin', href: dashboard().url },
  { title: 'Permissions', href: '/admin/permissions' },
];

function apply(params: Record<string, any>) {
  const pruned = Object.fromEntries(Object.entries(params).filter(([, v]) => v !== '' && v !== null && v !== undefined));
  router.get('/admin/permissions', pruned, { preserveScroll: true, preserveState: false, replace: true });
}

function toggleSort(column: string) {
  const by = currentSort.value.by === column ? column : column;
  const dir = currentSort.value.by === column ? (currentSort.value.dir === 'asc' ? 'desc' : 'asc') : 'asc';
  apply({ ...currentFilters.value, sort_by: by, sort_dir: dir, search: search.value });
}

function goToPage(p: number) {
  apply({ ...currentFilters.value, sort_by: currentSort.value.by, sort_dir: currentSort.value.dir, page: p, search: search.value });
}

function formatDate(value?: string) {
  if (!value) return '—';
  try { return new Date(value).toLocaleDateString(); } catch { return value; }
}
</script>

<template>
  <Head title="Permissions" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <template #header>
      <div class="flex items-center justify-between gap-4">
        <h1 class="text-xl font-semibold">Permissions</h1>
        <div class="flex items-center gap-2">
          <Input v-model="search" placeholder="Search permissions" class="w-64" @keyup.enter="apply({ search, sort_by: currentSort.by, sort_dir: currentSort.dir })" />
          <Button variant="secondary" @click="apply({ search, sort_by: currentSort.by, sort_dir: currentSort.dir })">Search</Button>
          <Link :href="`/admin/permissions/create`">
            <Button>Create Permission</Button>
          </Link>
        </div>
      </div>
    </template>

    <div class="rounded-md border overflow-x-auto">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead class="cursor-pointer" @click="toggleSort('name')">Name</TableHead>
            <TableHead>Guard</TableHead>
            <TableHead>Roles</TableHead>
            <TableHead class="cursor-pointer" @click="toggleSort('created_at')">Created</TableHead>
            <TableHead>Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="permission in permissions" :key="permission.id">
            <TableCell class="font-medium">
              <Link :href="`/admin/permissions/${permission.id}`" class="hover:underline">{{ permission.name }}</Link>
            </TableCell>
            <TableCell>{{ permission.guard_name }}</TableCell>
            <TableCell>{{ permission.roles_count ?? 0 }}</TableCell>
            <TableCell>{{ formatDate(permission.created_at) }}</TableCell>
            <TableCell class="flex items-center gap-2">
              <Link :href="`/admin/permissions/${permission.id}/edit`" class="text-blue-600 hover:underline">Edit</Link>
              <Button size="sm" variant="destructive" @click="openDelete(permission)">Delete</Button>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <div class="flex items-center justify-between mt-4 text-sm text-muted-foreground">
      <div>Total: {{ page.props.permissions?.total ?? 0 }}</div>
      <div class="flex items-center gap-2">
        <Button size="sm" variant="outline" :disabled="(page.props.permissions?.current_page ?? 1) <= 1" @click="goToPage((page.props.permissions?.current_page ?? 1) - 1)">Prev</Button>
        <div>Page {{ page.props.permissions?.current_page ?? 1 }} / {{ page.props.permissions?.last_page ?? 1 }}</div>
        <Button size="sm" variant="outline" :disabled="(page.props.permissions?.current_page ?? 1) >= (page.props.permissions?.last_page ?? 1)" @click="goToPage((page.props.permissions?.current_page ?? 1) + 1)">Next</Button>
      </div>
    </div>

    <!-- Delete confirmation dialog -->
    <Dialog v-model:open="showDelete">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Delete Permission</DialogTitle>
        </DialogHeader>
        <div class="py-4">
          <p>Are you sure you want to delete the permission <strong>{{ deletePermissionName }}</strong>?</p>
          <p class="text-sm text-muted-foreground mt-2">This action cannot be undone.</p>
        </div>
        <DialogFooter class="gap-2">
          <DialogClose as-child>
            <Button type="button" variant="secondary">Cancel</Button>
          </DialogClose>
          <Button type="button" variant="destructive" @click="confirmDelete">Delete</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>
