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
import { Label } from '@/components/ui/label';

interface UserItem { id: number; name: string; email: string; created_at?: string; is_banned?: boolean }

type PageProps = {
  users: { data: UserItem[]; current_page: number; last_page: number; per_page: number; total: number };
  filters?: any;
  sort?: any;
};
const page = usePage<PageProps>();

const users = computed(() => page.props.users?.data ?? []);
const currentFilters = computed(() => page.props.filters ?? {});
const currentSort = computed(() => page.props.sort ?? { by: 'name', dir: 'asc' });

const search = ref(currentFilters.value.search ?? '');

const showBan = ref(false);
const banUserId = ref<number | null>(null);
const banReason = ref('');
const banUntil = ref<string | null>(null);

function openBan(u: UserItem) {
  banUserId.value = u.id;
  banReason.value = '';
  banUntil.value = null;
  showBan.value = true;
}

function unban(u: UserItem) {
  if (!u?.id) return;
  router.post(`/admin/users/${u.id}/unban`, {}, { preserveScroll: true });
}

async function submitBan() {
  if (!banUserId.value) return;
  await router.post(`/admin/users/${banUserId.value}/ban`, { reason: banReason.value || null, banned_to: banUntil.value || null }, { preserveScroll: true });
  showBan.value = false;
}

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Admin', href: dashboard().url },
  { title: 'Users', href: '/admin/users' },
];

function apply(params: Record<string, any>) {
  const pruned = Object.fromEntries(Object.entries(params).filter(([, v]) => v !== '' && v !== null && v !== undefined));
  router.get('/admin/users', pruned, { preserveScroll: true, preserveState: false, replace: true });
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
  <Head title="Users" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <template #header>
      <div class="flex items-center justify-between gap-4">
        <h1 class="text-xl font-semibold">Users</h1>
        <div class="flex items-center gap-2">
          <Input v-model="search" placeholder="Search name or email" class="w-64" @keyup.enter="apply({ search, sort_by: currentSort.by, sort_dir: currentSort.dir })" />
          <Button variant="secondary" @click="apply({ search, sort_by: currentSort.by, sort_dir: currentSort.dir })">Search</Button>
        </div>
      </div>
    </template>

    <div class="rounded-md border overflow-x-auto">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead class="cursor-pointer" @click="toggleSort('name')">Name</TableHead>
            <TableHead class="cursor-pointer" @click="toggleSort('email')">Email</TableHead>
            <TableHead class="cursor-pointer" @click="toggleSort('created_at')">Joined</TableHead>
            <TableHead>Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="u in users" :key="u.id">
            <TableCell class="font-medium">
              <Link :href="`/admin/users/${u.id}`" class="hover:underline">{{ u.name }}</Link>
            </TableCell>
            <TableCell>{{ u.email }}</TableCell>
            <TableCell>{{ formatDate(u.created_at) }}</TableCell>
            <TableCell class="flex items-center gap-2">
              <Link :href="`/admin/users/${u.id}/edit`" class="text-blue-600 hover:underline">Edit</Link>
              <Button v-if="(u as any).is_banned" size="sm" variant="destructive" @click="unban(u)">Unban</Button>
              <Button v-else size="sm" variant="outline" @click="openBan(u)">Ban</Button>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <div class="flex items-center justify-between mt-4 text-sm text-muted-foreground">
      <div>Total: {{ page.props.users?.total ?? 0 }}</div>
      <div class="flex items-center gap-2">
        <Button size="sm" variant="outline" :disabled="(page.props.users?.current_page ?? 1) <= 1" @click="goToPage((page.props.users?.current_page ?? 1) - 1)">Prev</Button>
        <div>Page {{ page.props.users?.current_page ?? 1 }} / {{ page.props.users?.last_page ?? 1 }}</div>
        <Button size="sm" variant="outline" :disabled="(page.props.users?.current_page ?? 1) >= (page.props.users?.last_page ?? 1)" @click="goToPage((page.props.users?.current_page ?? 1) + 1)">Next</Button>
      </div>
    </div>

    <!-- Ban dialog -->
    <Dialog v-model:open="showBan">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Ban user</DialogTitle>
        </DialogHeader>
        <div class="grid gap-4 py-2">
          <div class="grid gap-2">
            <Label for="ban-reason">Reason (optional)</Label>
            <Input id="ban-reason" v-model="banReason" placeholder="Reason for ban" />
          </div>
          <div class="grid gap-2">
            <Label for="ban-until">Ban until (optional)</Label>
            <Input id="ban-until" type="datetime-local" v-model="banUntil" />
          </div>
        </div>
        <DialogFooter class="gap-2">
          <DialogClose as-child>
            <Button type="button" variant="secondary">Cancel</Button>
          </DialogClose>
          <Button type="button" @click="submitBan">Confirm ban</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>
