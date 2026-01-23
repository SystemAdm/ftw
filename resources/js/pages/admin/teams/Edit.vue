<script setup lang="ts">
import { usePage, useForm, router } from '@inertiajs/vue3';
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Field, FieldError, FieldLabel } from '@/components/ui/field';
import { trans } from 'laravel-vue-i18n';

const page = usePage<PageProps>();
const team = page.props.team as any;
const usersList = (page.props as any).users ?? [];

const form = useForm({
  _method: 'PUT',
  name: team.name ?? '',
  slug: team.slug ?? '',
  description: team.description ?? '',
  logo: null as File | null,
  active: Boolean(team.active),
  applications_enabled: Boolean(team.applications_enabled),
  users: (team.users ?? []).map((u: any) => u.id) as number[],
});

function submit() {
  form.post(`/admin/teams/${team.id}`, {
    forceFormData: true,
    onSuccess: () => router.visit(`/admin/teams/${team.id}`),
  });
}

function onFileChange(e: Event) {
  const target = e.target as HTMLInputElement;
  if (target.files?.length) {
    form.logo = target.files[0];
  }
}

function cancel() {
  router.visit(`/admin/teams/${team.id}`);
}
</script>

<template>
  <SidebarLayout>
    <h1 class="text-xl font-semibold mb-4">{{ trans('pages.settings.teams.edit') }}</h1>

    <form class="space-y-4 max-w-xl" @submit.prevent="submit">
      <Field>
        <FieldLabel>{{ trans('pages.settings.teams.fields.name') }}</FieldLabel>
        <Input v-model="form.name" type="text" />
        <FieldError v-if="form.errors.name">{{ form.errors.name }}</FieldError>
      </Field>

      <Field>
        <FieldLabel>{{ trans('pages.settings.teams.fields.slug') }}</FieldLabel>
        <Input v-model="form.slug" type="text" />
        <FieldError v-if="form.errors.slug">{{ form.errors.slug }}</FieldError>
      </Field>

      <Field>
        <FieldLabel>{{ trans('pages.settings.teams.fields.description') }}</FieldLabel>
        <Textarea v-model="form.description" />
        <FieldError v-if="form.errors.description">{{ form.errors.description }}</FieldError>
      </Field>

      <Field>
        <FieldLabel>{{ trans('pages.settings.teams.fields.logo') }}</FieldLabel>
        <div v-if="team.logo" class="mb-2">
            <img :src="team.logo" class="h-16 w-16 object-cover rounded border" alt="Current logo" />
        </div>
        <Input type="file" accept="image/*" @change="onFileChange" />
        <FieldError v-if="form.errors.logo">{{ form.errors.logo }}</FieldError>
      </Field>

      <div>
        <Label class="block text-sm font-medium">{{ trans('pages.settings.teams.fields.members') }}</Label>
        <div class="mt-2 grid max-h-64 grid-cols-1 gap-2 overflow-y-auto rounded border p-3 sm:grid-cols-2">
          <label v-for="u in usersList" :key="u.id" class="flex items-center gap-2">
            <Checkbox
              :model-value="form.users.includes(u.id)"
              @update:model-value="(val) => { const isChecked = val === true; const idx = form.users.indexOf(u.id); if (isChecked && idx === -1) form.users.push(u.id); if (!isChecked && idx !== -1) form.users.splice(idx, 1); }"
              :aria-label="`Select ${u.name}`"
            />
            <span class="text-sm">{{ u.name }}</span>
          </label>
        </div>
        <div v-if="form.errors.users" class="text-red-600 text-sm mt-1">{{ form.errors.users }}</div>
      </div>

      <div class="flex items-center gap-2">
        <Checkbox :model-value="form.active" @update:model-value="(v: any) => (form.active = v)" aria-label="Select Active" />
        <Label>{{ trans('pages.settings.teams.fields.active') }}</Label>
      </div>

      <div class="flex items-center gap-2">
        <Checkbox :model-value="form.applications_enabled" @update:model-value="(v: any) => (form.applications_enabled = v)" aria-label="Select Applications Enabled" />
        <Label>{{ trans('pages.settings.teams.fields.applications_enabled') || 'Applications Enabled' }}</Label>
      </div>

      <div class="flex gap-2">
        <Button type="submit">{{ trans('pages.settings.locations.actions.save') }}</Button>
        <Button variant="secondary" type="button" @click="cancel">{{ trans('pages.settings.locations.actions.cancel') }}</Button>
      </div>
    </form>
  </SidebarLayout>
</template>
