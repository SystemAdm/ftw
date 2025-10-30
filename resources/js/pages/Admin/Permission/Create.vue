<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, usePage, useForm } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';
import { dashboard } from '@/routes/admin';

interface Role { id: number; name: string }

type PageProps = {
  roles: Role[];
};
const page = usePage<PageProps>();

const form = useForm({
  name: '',
  roles: [] as number[],
});

function toggleRole(id: number) {
  const index = form.roles.indexOf(id);
  if (index > -1) {
    form.roles.splice(index, 1);
  } else {
    form.roles.push(id);
  }
}

function submit() {
  form.post('/admin/permissions', {
    preserveScroll: true,
  });
}

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Admin', href: dashboard().url },
  { title: 'Permissions', href: '/admin/permissions' },
  { title: 'Create', href: '/admin/permissions/create' },
];
</script>

<template>
  <Head title="Create Permission" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <template #header>
      <h1 class="text-xl font-semibold">Create Permission</h1>
    </template>

    <form @submit.prevent="submit" class="space-y-6 max-w-2xl">
      <div class="space-y-2">
        <Label for="name">Permission Name</Label>
        <Input
          id="name"
          v-model="form.name"
          type="text"
          required
          placeholder="e.g., edit-posts"
          :class="{ 'border-red-500': form.errors.name }"
        />
        <p v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</p>
      </div>

      <div class="space-y-3">
        <Label>Assign to Roles</Label>
        <div class="border rounded-md p-4 space-y-2 max-h-96 overflow-y-auto">
          <div v-for="role in page.props.roles" :key="role.id" class="flex items-center space-x-2">
            <Checkbox
              :id="`role-${role.id}`"
              :modelValue="form.roles.includes(role.id)"
              @update:modelValue="toggleRole(role.id)"
            />
            <label :for="`role-${role.id}`" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 cursor-pointer">
              {{ role.name }}
            </label>
          </div>
          <p v-if="page.props.roles.length === 0" class="text-sm text-muted-foreground">No roles available</p>
        </div>
        <p v-if="form.errors.roles" class="text-sm text-red-500">{{ form.errors.roles }}</p>
      </div>

      <div class="flex items-center gap-2">
        <Button type="submit" :disabled="form.processing">
          {{ form.processing ? 'Creating...' : 'Create Permission' }}
        </Button>
        <Button type="button" variant="outline" @click="router.visit('/admin/permissions')">
          Cancel
        </Button>
      </div>
    </form>
  </AppLayout>
</template>
