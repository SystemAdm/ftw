<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, usePage, useForm } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';
import { dashboard } from '@/routes/admin';

interface Permission { id: number; name: string }

type PageProps = {
  permissions: Permission[];
};
const page = usePage<PageProps>();

const form = useForm({
  name: '',
  permissions: [] as number[],
});

function togglePermission(id: number) {
  const index = form.permissions.indexOf(id);
  if (index > -1) {
    form.permissions.splice(index, 1);
  } else {
    form.permissions.push(id);
  }
}

function submit() {
  form.post('/admin/roles', {
    preserveScroll: true,
  });
}

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Admin', href: dashboard().url },
  { title: 'Roles', href: '/admin/roles' },
  { title: 'Create', href: '/admin/roles/create' },
];
</script>

<template>
  <Head title="Create Role" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <template #header>
      <h1 class="text-xl font-semibold">Create Role</h1>
    </template>

    <form @submit.prevent="submit" class="space-y-6 max-w-2xl">
      <div class="space-y-2">
        <Label for="name">Role Name</Label>
        <Input
          id="name"
          v-model="form.name"
          type="text"
          required
          placeholder="e.g., moderator"
          :class="{ 'border-red-500': form.errors.name }"
        />
        <p v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</p>
      </div>

      <div class="space-y-3">
        <Label>Permissions</Label>
        <div class="border rounded-md p-4 space-y-2 max-h-96 overflow-y-auto">
          <div v-for="permission in page.props.permissions" :key="permission.id" class="flex items-center space-x-2">
            <Checkbox
              :id="`perm-${permission.id}`"
              :modelValue="form.permissions.includes(permission.id)"
              @update:modelValue="togglePermission(permission.id)"
            />
            <label :for="`perm-${permission.id}`" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 cursor-pointer">
              {{ permission.name }}
            </label>
          </div>
          <p v-if="page.props.permissions.length === 0" class="text-sm text-muted-foreground">No permissions available</p>
        </div>
        <p v-if="form.errors.permissions" class="text-sm text-red-500">{{ form.errors.permissions }}</p>
      </div>

      <div class="flex items-center gap-2">
        <Button type="submit" :disabled="form.processing">
          {{ form.processing ? 'Creating...' : 'Create Role' }}
        </Button>
        <Button type="button" variant="outline" @click="router.visit('/admin/roles')">
          Cancel
        </Button>
      </div>
    </form>
  </AppLayout>
</template>
