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

interface PermissionItem { id: number; name: string; guard_name: string; created_at?: string; roles_count?: number }
interface Role { id: number; name: string }

type PageProps = {
  permissions: { data: PermissionItem[]; current_page: number; last_page: number; per_page: number; total: number };
  roles: Role[];
  filters?: any;
  sort?: any;
};
const page = usePage<PageProps>();

const permissions = computed(() => page.props.permissions?.data ?? []);
const roles = computed(() => page.props.roles ?? []);
const currentFilters = computed(() => page.props.filters ?? {});
const currentSort = computed(() => page.props.sort ?? { by: 'name', dir: 'asc' });

const search = ref(currentFilters.value.search ?? '');

const showDelete = ref(false);
const deletePermissionId = ref<number | null>(null);
const deletePermissionName = ref('');

const showCreate = ref(false);
const createForm = useForm({
  name: '',
  roles: [] as number[],
});

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

function openCreate() {
  createForm.reset();
  createForm.clearErrors();
  showCreate.value = true;
}

function toggleRole(id: number) {
  const index = createForm.roles.indexOf(id);
  if (index > -1) {
    createForm.roles.splice(index, 1);
  } else {
    createForm.roles.push(id);
  }
}

function submitCreate() {
  createForm.post('/admin/permissions', {
    preserveScroll: true,
    onSuccess: () => {
      showCreate.value = false;
      createForm.reset();
    },
  });
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
    <template #actions>
      <Button @click="openCreate">Create Permission</Button>
    </template>
    <template #header>
      <div class="flex items-center justify-between gap-4">
        <h1 class="text-xl font-semibold">Permissions</h1>
        <div class="flex items-center gap-2">
          <Input v-model="search" placeholder="Search permissions" class="w-64" @keyup.enter="apply({ search, sort_by: currentSort.by, sort_dir: currentSort.dir })" />
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

    <!-- Create permission sheet -->
    <Sheet v-model:open="showCreate">
      <SheetContent class="sm:max-w-xl overflow-y-auto">
        <SheetHeader>
          <SheetTitle>Create Permission</SheetTitle>
        </SheetHeader>
        <form @submit.prevent="submitCreate" class="space-y-6 mt-4">
          <div class="space-y-2">
            <Label for="permission-name">Permission Name</Label>
            <Input
              id="permission-name"
              v-model="createForm.name"
              type="text"
              required
              placeholder="e.g., edit-posts"
              :class="{ 'border-red-500': createForm.errors.name }"
            />
            <p v-if="createForm.errors.name" class="text-sm text-red-500">{{ createForm.errors.name }}</p>
          </div>

          <div class="space-y-3">
            <Label>Assign to Roles</Label>
            <div class="border rounded-md p-4 space-y-2 max-h-96 overflow-y-auto">
              <div v-for="role in roles" :key="role.id" class="flex items-center space-x-2">
                <Checkbox
                  :id="`create-role-${role.id}`"
                  :modelValue="createForm.roles.includes(role.id)"
                  @update:modelValue="toggleRole(role.id)"
                />
                <label :for="`create-role-${role.id}`" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 cursor-pointer">
                  {{ role.name }}
                </label>
              </div>
              <p v-if="roles.length === 0" class="text-sm text-muted-foreground">No roles available</p>
            </div>
            <p v-if="createForm.errors.roles" class="text-sm text-red-500">{{ createForm.errors.roles }}</p>
          </div>

          <SheetFooter class="gap-2">
            <SheetClose as-child>
              <Button type="button" variant="outline">Cancel</Button>
            </SheetClose>
            <Button type="submit" :disabled="createForm.processing">
              {{ createForm.processing ? 'Creating...' : 'Create Permission' }}
            </Button>
          </SheetFooter>
        </form>
      </SheetContent>
    </Sheet>
  </AppLayout>
</template>
