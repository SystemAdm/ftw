<script setup lang="ts">
import { usePage, router } from '@inertiajs/vue3';
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { reactive } from 'vue';
import { Checkbox } from '@/components/ui/checkbox';

const page = usePage<PageProps>();
const team = page.props.team as any;
const usersList = (page.props as any).users ?? [];

const form = reactive({
  name: team.name ?? '',
  slug: team.slug ?? '',
  description: team.description ?? '',
  logo: team.logo ?? '',
  active: Boolean(team.active),
  users: (team.users ?? []).map((u: any) => u.id) as number[],
});

const errors = reactive<Record<string, string[]>>({});

function submit() {
  router.put(`/admin/teams/${team.id}`, form, {
    onError: (err) => Object.assign(errors, err as any),
    onSuccess: () => router.visit(`/admin/teams/${team.id}`),
  });
}

function cancel() {
  router.visit(`/admin/teams/${team.id}`);
}
</script>

<template>
  <SidebarLayout>
    <h1 class="text-xl font-semibold mb-4">Edit Team</h1>

    <form class="space-y-4 max-w-xl" @submit.prevent="submit">
      <div>
        <Label class="block text-sm font-medium">Name</Label>
        <Input v-model="form.name" class="mt-1 w-full" type="text" />
        <div v-if="errors.name" class="text-red-600 text-sm mt-1">{{ errors.name[0] }}</div>
      </div>

      <div>
        <Label class="block text-sm font-medium">Slug</Label>
        <Input v-model="form.slug" class="mt-1 w-full" type="text" />
        <div v-if="errors.slug" class="text-red-600 text-sm mt-1">{{ errors.slug[0] }}</div>
      </div>

      <div>
        <Label class="block text-sm font-medium">Description</Label>
        <Textarea v-model="form.description" class="mt-1 w-full" />
        <div v-if="errors.description" class="text-red-600 text-sm mt-1">{{ errors.description[0] }}</div>
      </div>

      <div>
        <Label class="block text-sm font-medium">Logo URL</Label>
        <Input v-model="form.logo" class="mt-1 w-full" type="text" />
        <div v-if="errors.logo" class="text-red-600 text-sm mt-1">{{ errors.logo[0] }}</div>
      </div>

      <div>
        <Label class="block text-sm font-medium">Members</Label>
        <div class="mt-2 grid max-h-64 grid-cols-1 gap-2 overflow-y-auto rounded border p-3 sm:grid-cols-2">
          <Label v-for="u in usersList" :key="u.id" class="flex items-center gap-2">
            <Checkbox
              :model-value="form.users.includes(u.id)"
              @update:model-value="(val) => { const isChecked = val === true; const idx = form.users.indexOf(u.id); if (isChecked && idx === -1) form.users.push(u.id); if (!isChecked && idx !== -1) form.users.splice(idx, 1); }"
              :aria-label="`Select ${u.name}`"
            />
            <span class="text-sm">{{ u.name }}</span>
          </Label>
        </div>
        <div v-if="errors.users" class="text-red-600 text-sm mt-1">{{ errors.users[0] }}</div>
      </div>

      <label class="inline-flex items-center gap-2">
        <Checkbox :model-value="form.active" @update:model-value="(v) => (form.active = v === true)" aria-label="Select Active" class="rounded border"  />
        <span>Active</span>
      </label>

      <div class="flex gap-2">
        <Button>Save</Button>
        <Button variant="secondary" type="button" @click="cancel">Cancel</Button>
      </div>
    </form>
  </SidebarLayout>
</template>
