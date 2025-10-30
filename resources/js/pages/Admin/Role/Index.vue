<script setup lang="ts">
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, usePage, Link, useForm } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';
import { dashboard } from '@/routes/admin';
import { computed, ref } from 'vue';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogClose } from '@/components/ui/dialog';
import { Sheet, SheetContent, SheetFooter, SheetHeader, SheetTitle, SheetClose } from '@/components/ui/sheet';

interface RoleItem { id: number; name: string; guard_name: string; created_at?: string; permissions_count?: number }
interface Permission { id: number; name: string }

type PageProps = {
  roles: { data: RoleItem[]; current_page: number; last_page: number; per_page: number; total: number };
  permissions: Permission[];
  filters?: any;
  sort?: any;
};
const page = usePage<PageProps>();

const roles = computed(() => page.props.roles?.data ?? []);
const permissions = computed(() => page.props.permissions ?? []);
const currentFilters = computed(() => page.props.filters ?? {});
const currentSort = computed(() => page.props.sort ?? { by: 'name', dir: 'asc' });

const search = ref(currentFilters.value.search ?? '');

const showDelete = ref(false);
const deleteRoleId = ref<number | null>(null);
const deleteRoleName = ref('');

const showCreate = ref(false);
const createForm = useForm({
  name: '',
  permissions: [] as number[],
});

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

function openCreate() {
  createForm.reset();
  createForm.clearErrors();
  showCreate.value = true;
}

function togglePermission(id: number) {
  const index = createForm.permissions.indexOf(id);
  if (index > -1) {
    createForm.permissions.splice(index, 1);
  } else {
    createForm.permissions.push(id);
  }
}

function submitCreate() {
  createForm.post('/admin/roles', {
    preserveScroll: true,
    onSuccess: () => {
      showCreate.value = false;
      createForm.reset();
    },
  });
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
    <template #actions>
      <Button @click="openCreate">Create Role</Button>
    </template>
    <template #header>
      <div class="flex items-center justify-between gap-4">
        <h1 class="text-xl font-semibold">Roles</h1>
        <div class="flex items-center gap-2">
          <Input v-model="search" placeholder="Search roles" class="w-64" @keyup.enter="apply({ search, sort_by: currentSort.by, sort_dir: currentSort.dir })" />
          <Button variant="secondary" @click="apply({ search, sort_by: currentSort.by, sort_dir: currentSort.dir })">Search</Button>
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

    <!-- Create role sheet -->
    <Sheet v-model:open="showCreate">
      <SheetContent class="sm:max-w-xl overflow-y-auto">
        <SheetHeader>
          <SheetTitle>Create Role</SheetTitle>
        </SheetHeader>
        <form @submit.prevent="submitCreate" class="space-y-6 mt-4">
          <div class="space-y-2">
            <Label for="role-name">Role Name</Label>
            <Input
              id="role-name"
              v-model="createForm.name"
              type="text"
              required
              placeholder="e.g., moderator"
              :class="{ 'border-red-500': createForm.errors.name }"
            />
            <p v-if="createForm.errors.name" class="text-sm text-red-500">{{ createForm.errors.name }}</p>
          </div>

          <div class="space-y-3">
            <Label>Permissions</Label>
            <div class="border rounded-md p-4 space-y-2 max-h-96 overflow-y-auto">
              <div v-for="permission in permissions" :key="permission.id" class="flex items-center space-x-2">
                <Checkbox
                  :id="`create-perm-${permission.id}`"
                  :modelValue="createForm.permissions.includes(permission.id)"
                  @update:modelValue="togglePermission(permission.id)"
                />
                <label :for="`create-perm-${permission.id}`" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 cursor-pointer">
                  {{ permission.name }}
                </label>
              </div>
              <p v-if="permissions.length === 0" class="text-sm text-muted-foreground">No permissions available</p>
            </div>
            <p v-if="createForm.errors.permissions" class="text-sm text-red-500">{{ createForm.errors.permissions }}</p>
          </div>

          <SheetFooter class="gap-2">
            <SheetClose as-child>
              <Button type="button" variant="outline">Cancel</Button>
            </SheetClose>
            <Button type="submit" :disabled="createForm.processing">
              {{ createForm.processing ? 'Creating...' : 'Create Role' }}
            </Button>
          </SheetFooter>
        </form>
      </SheetContent>
    </Sheet>
  </AppLayout>
</template>
