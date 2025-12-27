<script setup lang="ts">
import { usePage, router } from '@inertiajs/vue3';
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { reactive } from 'vue';
import { Checkbox } from '@/components/ui/checkbox';
import { Field, FieldError, FieldLabel } from '@/components/ui/field';
import { trans } from 'laravel-vue-i18n';

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
    <h1 class="text-xl font-semibold mb-4">{{ trans('pages.settings.teams.edit') }}</h1>

    <form class="space-y-4 max-w-xl" @submit.prevent="submit">
      <Field>
        <FieldLabel>{{ trans('pages.settings.teams.fields.name') }}</FieldLabel>
        <Input v-model="form.name" type="text" />
        <FieldError v-if="errors.name">{{ errors.name[0] }}</FieldError>
      </Field>

      <Field>
        <FieldLabel>{{ trans('pages.settings.teams.fields.slug') }}</FieldLabel>
        <Input v-model="form.slug" type="text" />
        <FieldError v-if="errors.slug">{{ errors.slug[0] }}</FieldError>
      </Field>

      <Field>
        <FieldLabel>{{ trans('pages.settings.teams.fields.description') }}</FieldLabel>
        <Textarea v-model="form.description" />
        <FieldError v-if="errors.description">{{ errors.description[0] }}</FieldError>
      </Field>

      <Field>
        <FieldLabel>{{ trans('pages.settings.teams.fields.logo_url') }}</FieldLabel>
        <Input v-model="form.logo" type="text" />
        <FieldError v-if="errors.logo">{{ errors.logo[0] }}</FieldError>
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
        <div v-if="errors.users" class="text-red-600 text-sm mt-1">{{ errors.users[0] }}</div>
      </div>

      <div class="flex items-center gap-2">
        <Checkbox :model-value="form.active" @update:model-value="(v) => (form.active = v)" aria-label="Select Active" />
        <Label>{{ trans('pages.settings.teams.fields.active') }}</Label>
      </div>

      <div class="flex gap-2">
        <Button type="submit">{{ trans('pages.settings.locations.actions.save') }}</Button>
        <Button variant="secondary" type="button" @click="cancel">{{ trans('pages.settings.locations.actions.cancel') }}</Button>
      </div>
    </form>
  </SidebarLayout>
</template>
