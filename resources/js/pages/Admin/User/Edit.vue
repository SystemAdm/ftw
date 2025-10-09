<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';
import { dashboard } from '@/routes/admin';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { computed } from 'vue';

interface UserItem { id: number; name: string; email: string }

type PageProps = { user: UserItem };
const page = usePage<PageProps>();
const user = computed(() => page.props.user);

const form = useForm({
  name: user.value?.name ?? '',
  email: user.value?.email ?? '',
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

    <form class="grid gap-4 max-w-xl" @submit.prevent="submit">
      <div>
        <label class="block text-sm font-medium mb-1">Name</label>
        <Input v-model="form.name" />
        <div v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</div>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Email</label>
        <Input v-model="form.email" type="email" />
        <div v-if="form.errors.email" class="text-sm text-red-600 mt-1">{{ form.errors.email }}</div>
      </div>
      <div class="flex gap-2">
        <Button type="submit" :disabled="form.processing">Save</Button>
        <Button type="button" variant="secondary" :disabled="form.processing" @click="form.reset()">Reset</Button>
      </div>
    </form>
  </AppLayout>
</template>
