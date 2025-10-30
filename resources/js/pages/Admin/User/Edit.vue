<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';
import { dashboard } from '@/routes/admin';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { computed } from 'vue';

interface UserItem { id: number; name: string; email: string; avatar?: string | null; birthday?: string | null; postal_code?: number | null; role_ids?: number[] }
interface RoleItem { id: number; name: string }

type PageProps = { user: UserItem; roles: RoleItem[] };
const page = usePage<PageProps>();
const user = computed(() => page.props.user);
const roles = computed(() => page.props.roles ?? []);

const form = useForm({
  name: user.value?.name ?? '',
  email: user.value?.email ?? '',
  avatar: user.value?.avatar ?? '',
  birthday: user.value?.birthday ?? '',
  postal_code: user.value?.postal_code ?? null,
  role_ids: user.value?.role_ids ?? [],
});

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Admin', href: dashboard().url },
  { title: 'Users', href: '/admin/users' },
  { title: user.value?.name ?? 'User', href: `/admin/users/${user.value?.id}` },
  { title: 'Edit', href: `/admin/users/${user.value?.id}/edit` },
];

function submit() {
  form.put(`/admin/users/${user.value?.id}`);
}

function toggleRole(roleId: number) {
  const index = form.role_ids.indexOf(roleId);
  if (index > -1) {
    form.role_ids.splice(index, 1);
  } else {
    form.role_ids.push(roleId);
  }
}
</script>

<template>
  <Head :title="user?.name ? `Edit ${user.name}` : 'Edit User'" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <template #header>
      <div class="flex items-center justify-between gap-4">
        <h1 class="text-xl font-semibold">Edit User</h1>
        <Link :href="`/admin/users/${user?.id}`" class="text-blue-600 hover:underline">View</Link>
      </div>
    </template>

    <form class="grid gap-6 max-w-2xl" @submit.prevent="submit">
      <!-- Basic Information Card -->
      <Card>
        <CardHeader>
          <CardTitle>Basic Information</CardTitle>
          <CardDescription>Update the user's name and email address</CardDescription>
        </CardHeader>
        <CardContent class="grid gap-4">
          <div>
            <Label for="name" class="text-sm font-medium mb-2">Name</Label>
            <Input id="name" v-model="form.name" class="mt-1" />
            <div v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</div>
          </div>
          <div>
            <Label for="email" class="text-sm font-medium mb-2">Email</Label>
            <Input id="email" v-model="form.email" type="email" class="mt-1" />
            <div v-if="form.errors.email" class="text-sm text-red-600 mt-1">{{ form.errors.email }}</div>
          </div>
          <div>
            <Label for="avatar" class="text-sm font-medium mb-2">Avatar URL</Label>
            <Input id="avatar" v-model="form.avatar" class="mt-1" placeholder="https://example.com/avatar.jpg" />
            <div v-if="form.errors.avatar" class="text-sm text-red-600 mt-1">{{ form.errors.avatar }}</div>
          </div>
          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <Label for="birthday" class="text-sm font-medium mb-2">Birthday</Label>
              <Input id="birthday" v-model="form.birthday" type="date" class="mt-1" />
              <div v-if="form.errors.birthday" class="text-sm text-red-600 mt-1">{{ form.errors.birthday }}</div>
            </div>
            <div>
              <Label for="postal_code" class="text-sm font-medium mb-2">Postal Code</Label>
              <Input id="postal_code" v-model="form.postal_code" type="number" class="mt-1" placeholder="Enter postal code" />
              <div v-if="form.errors.postal_code" class="text-sm text-red-600 mt-1">{{ form.errors.postal_code }}</div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Roles Card -->
      <Card>
        <CardHeader>
          <CardTitle>Roles & Permissions</CardTitle>
          <CardDescription>Assign roles to control user permissions</CardDescription>
        </CardHeader>
        <CardContent>
          <div class="grid gap-3">
            <div v-for="role in roles" :key="role.id" class="flex items-center gap-3 p-2 rounded-md hover:bg-muted/50 transition-colors">
              <Checkbox
                :id="`role-${role.id}`"
                :modelValue="form.role_ids.includes(role.id)"
                @update:modelValue="toggleRole(role.id)"
              />
              <Label :for="`role-${role.id}`" class="cursor-pointer font-medium flex-1">{{ role.name }}</Label>
            </div>
          </div>
          <div v-if="form.errors.role_ids" class="text-sm text-red-600 mt-3">{{ form.errors.role_ids }}</div>
        </CardContent>
      </Card>

      <!-- Actions -->
      <div class="flex gap-3">
        <Button type="submit" :disabled="form.processing">
          {{ form.processing ? 'Saving...' : 'Save Changes' }}
        </Button>
        <Button type="button" variant="outline" :disabled="form.processing" @click="form.reset()">
          Reset
        </Button>
        <Link :href="`/admin/users/${user?.id}`" class="ml-auto">
          <Button type="button" variant="secondary" :disabled="form.processing">
            Cancel
          </Button>
        </Link>
      </div>
    </form>
  </AppLayout>
</template>
