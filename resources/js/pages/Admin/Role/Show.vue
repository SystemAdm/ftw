<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogClose } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';
import { dashboard } from '@/routes/admin';
import { ref, computed } from 'vue';

interface Permission { id: number; name: string }
interface Role { id: number; name: string; guard_name: string; created_at: string; updated_at: string }
interface User { id: number; name: string; email: string; created_at?: string }

type PageProps = {
  role: Role;
  permissions: Permission[];
  users: { data: User[]; current_page: number; last_page: number; per_page: number; total: number };
};
const page = usePage<PageProps>();

const users = computed(() => page.props.users?.data ?? []);

const showAddUser = ref(false);
const searchQuery = ref('');
const searchResults = ref<User[]>([]);
const searching = ref(false);

async function searchUsers() {
  if (searchQuery.value.length < 2) {
    searchResults.value = [];
    return;
  }

  searching.value = true;
  try {
    const response = await fetch(`/admin/roles/${page.props.role.id}/users/search?search=${encodeURIComponent(searchQuery.value)}`);
    const data = await response.json();
    searchResults.value = data.users || [];
  } catch (error) {
    console.error('Search error:', error);
    searchResults.value = [];
  } finally {
    searching.value = false;
  }
}

function assignUser(userId: number) {
  router.post(`/admin/roles/${page.props.role.id}/users`, { user_id: userId }, {
    preserveScroll: true,
    onSuccess: () => {
      showAddUser.value = false;
      searchQuery.value = '';
      searchResults.value = [];
    }
  });
}

function removeUser(userId: number) {
  if (!confirm('Are you sure you want to remove this role from the user?')) return;
  router.delete(`/admin/roles/${page.props.role.id}/users/${userId}`, {
    preserveScroll: true
  });
}

function goToPage(p: number) {
  router.get(`/admin/roles/${page.props.role.id}`, { page: p }, { preserveScroll: true, preserveState: true });
}

function formatDate(value?: string) {
  if (!value) return '—';
  try { return new Date(value).toLocaleString(); } catch { return value; }
}

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Admin', href: dashboard().url },
  { title: 'Roles', href: '/admin/roles' },
  { title: page.props.role.name, href: `/admin/roles/${page.props.role.id}` },
];
</script>

<template>
  <Head :title="`Role: ${page.props.role.name}`" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <template #header>
      <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold">Role: {{ page.props.role.name }}</h1>
        <Link :href="`/admin/roles/${page.props.role.id}/edit`">
          <Button>Edit Role</Button>
        </Link>
      </div>
    </template>

    <div class="space-y-6">
      <div class="border rounded-lg p-6 space-y-4">
        <div>
          <h2 class="text-lg font-semibold mb-4">Role Details</h2>
          <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
              <dt class="text-sm font-medium text-muted-foreground">ID</dt>
              <dd class="mt-1 text-sm">{{ page.props.role.id }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-muted-foreground">Name</dt>
              <dd class="mt-1 text-sm">{{ page.props.role.name }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-muted-foreground">Guard</dt>
              <dd class="mt-1 text-sm">{{ page.props.role.guard_name }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-muted-foreground">Created At</dt>
              <dd class="mt-1 text-sm">{{ formatDate(page.props.role.created_at) }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-muted-foreground">Updated At</dt>
              <dd class="mt-1 text-sm">{{ formatDate(page.props.role.updated_at) }}</dd>
            </div>
          </dl>
        </div>
      </div>

      <div class="border rounded-lg p-6">
        <h2 class="text-lg font-semibold mb-4">Permissions ({{ page.props.permissions.length }})</h2>
        <div v-if="page.props.permissions.length > 0" class="flex flex-wrap gap-2">
          <Badge v-for="permission in page.props.permissions" :key="permission.id" variant="secondary">
            {{ permission.name }}
          </Badge>
        </div>
        <p v-else class="text-sm text-muted-foreground">No permissions assigned to this role.</p>
      </div>

      <div class="border rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold">Users with this role ({{ page.props.users?.total ?? 0 }})</h2>
          <Button @click="showAddUser = true">Add User</Button>
        </div>

        <div v-if="users.length > 0">
          <div class="rounded-md border overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Name</TableHead>
                  <TableHead>Email</TableHead>
                  <TableHead>Joined</TableHead>
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
                  <TableCell>
                    <Button size="sm" variant="destructive" @click="removeUser(u.id)">Remove</Button>
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
        </div>
        <p v-else class="text-sm text-muted-foreground">No users have this role yet.</p>
      </div>

      <div class="flex items-center gap-2">
        <Link :href="`/admin/roles/${page.props.role.id}/edit`">
          <Button>Edit Role</Button>
        </Link>
        <Link href="/admin/roles">
          <Button variant="outline">Back to Roles</Button>
        </Link>
      </div>
    </div>

    <!-- Add User Dialog -->
    <Dialog v-model:open="showAddUser">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Add User to Role</DialogTitle>
        </DialogHeader>
        <div class="grid gap-4 py-2">
          <div class="grid gap-2">
            <Label for="user-search">Search for user</Label>
            <Input
              id="user-search"
              v-model="searchQuery"
              placeholder="Type name or email..."
              @input="searchUsers"
            />
          </div>
          <div v-if="searching" class="text-sm text-muted-foreground">Searching...</div>
          <div v-else-if="searchResults.length > 0" class="border rounded-md max-h-60 overflow-y-auto">
            <div
              v-for="user in searchResults"
              :key="user.id"
              class="p-3 hover:bg-muted cursor-pointer border-b last:border-b-0"
              @click="assignUser(user.id)"
            >
              <div class="font-medium">{{ user.name }}</div>
              <div class="text-sm text-muted-foreground">{{ user.email }}</div>
            </div>
          </div>
          <div v-else-if="searchQuery.length >= 2" class="text-sm text-muted-foreground">No users found</div>
          <div v-else class="text-sm text-muted-foreground">Type at least 2 characters to search</div>
        </div>
        <DialogFooter>
          <DialogClose as-child>
            <Button type="button" variant="secondary">Cancel</Button>
          </DialogClose>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>
