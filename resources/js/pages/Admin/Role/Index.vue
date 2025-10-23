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

interface RoleItem { id: number; name: string; guard_name: string; created_at?: string; permissions_count?: number }

type PageProps = {
  roles: { data: RoleItem[]; current_page: number; last_page: number; per_page: number; total: number };
  filters?: any;
  sort?: any;
};
const page = usePage<PageProps>();

const roles = computed(() => page.props.roles?.data ?? []);
const currentFilters = computed(() => page.props.filters ?? {});
const currentSort = computed(() => page.props.sort ?? { by: 'name', dir: 'asc' });

const search = ref(currentFilters.value.search ?? '');

const showDelete = ref(false);
const deleteRoleId = ref<number | null>(null);
const deleteRoleName = ref('');

function openDelete(role: RoleItem) {
  deleteRoleId.value = role.id;
  deleteRoleName.value = role.name;
  showDelete.value = true;
}

async function confirmDelete() {
  if (!deleteRoleId.value) return;
  await router.delete(`/admin/roles/${deleteRoleId.value}`, { preserveScroll: true });
  showDelete.value = false;
}

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Admin', href: dashboard().url },
  { title: 'Roles', href: '/admin/roles' },
];

function apply(params: Record<string, any>) {
  const pruned = Object.fromEntries(Object.entries(params).filter(([, v]) => v !== '' && v !== null && v !== undefined));
  router.get('/admin/roles', pruned, { preserveScroll: true, preserveState: false, replace: true });
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
  <Head title="Roles" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <template #header>
      <div class="flex items-center justify-between gap-4">
        <h1 class="text-xl font-semibold">Roles</h1>
        <div class="flex items-center gap-2">
          <Input v-model="search" placeholder="Search roles" class="w-64" @keyup.enter="apply({ search, sort_by: currentSort.by, sort_dir: currentSort.dir })" />
          <Button variant="secondary" @click="apply({ search, sort_by: currentSort.by, sort_dir: currentSort.dir })">Search</Button>
          <Link :href="`/admin/roles/create`">
            <Button>Create Role</Button>
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
            <TableHead>Permissions</TableHead>
            <TableHead class="cursor-pointer" @click="toggleSort('created_at')">Created</TableHead>
            <TableHead>Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="role in roles" :key="role.id">
            <TableCell class="font-medium">
              <Link :href="`/admin/roles/${role.id}`" class="hover:underline">{{ role.name }}</Link>
            </TableCell>
            <TableCell>{{ role.guard_name }}</TableCell>
            <TableCell>{{ role.permissions_count ?? 0 }}</TableCell>
            <TableCell>{{ formatDate(role.created_at) }}</TableCell>
            <TableCell class="flex items-center gap-2">
              <Link :href="`/admin/roles/${role.id}/edit`" class="text-blue-600 hover:underline">Edit</Link>
              <Button size="sm" variant="destructive" @click="openDelete(role)">Delete</Button>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <div class="flex items-center justify-between mt-4 text-sm text-muted-foreground">
      <div>Total: {{ page.props.roles?.total ?? 0 }}</div>
      <div class="flex items-center gap-2">
        <Button size="sm" variant="outline" :disabled="(page.props.roles?.current_page ?? 1) <= 1" @click="goToPage((page.props.roles?.current_page ?? 1) - 1)">Prev</Button>
        <div>Page {{ page.props.roles?.current_page ?? 1 }} / {{ page.props.roles?.last_page ?? 1 }}</div>
        <Button size="sm" variant="outline" :disabled="(page.props.roles?.current_page ?? 1) >= (page.props.roles?.last_page ?? 1)" @click="goToPage((page.props.roles?.current_page ?? 1) + 1)">Next</Button>
      </div>
    </div>

    <!-- Delete confirmation dialog -->
    <Dialog v-model:open="showDelete">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Delete Role</DialogTitle>
        </DialogHeader>
        <div class="py-4">
          <p>Are you sure you want to delete the role <strong>{{ deleteRoleName }}</strong>?</p>
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
