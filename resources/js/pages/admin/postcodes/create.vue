<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Field, FieldError, FieldLabel, FieldSet } from '@/components/ui/field';
import { router } from '@inertiajs/vue3';
import { store as storeRoute, index as indexRoute } from '@/routes/admin/postcodes';
import { reactive } from 'vue';
import { trans } from 'laravel-vue-i18n';

const form = reactive({
  postal_code: '' as string,
  city: '' as string,
  state: '' as string,
  country: '' as string,
  municipality: '' as string,
});

const errors = reactive<Record<string, string[]>>({});

function submit() {
  // Coerce postal_code to int
  const payload = {
    ...form,
    postal_code: form.postal_code ? Number(form.postal_code) : undefined,
  } as Record<string, any>;
  router.post(storeRoute.url(), payload, {
    onError: (e) => Object.assign(errors, e as any),
  });
}

function cancel() {
  router.visit(indexRoute.url());
}
</script>

<template>
  <SidebarLayout>
    <h1 class="mb-4 text-xl font-semibold">{{ trans('pages.settings.postcodes.new') }}</h1>
    <form class="max-w-xl space-y-4" @submit.prevent="submit">
      <FieldSet>
        <Field>
          <FieldLabel>{{ trans('pages.settings.postcodes.fields.postal_code') }}</FieldLabel>
          <Input v-model="form.postal_code" type="number" inputmode="numeric" class="mt-1 w-full" />
          <FieldError v-if="errors.postal_code">{{ errors.postal_code[0] }}</FieldError>
        </Field>
        <Field>
          <FieldLabel>{{ trans('pages.settings.postcodes.fields.city') }}</FieldLabel>
          <Input v-model="form.city" class="mt-1 w-full" />
          <FieldError v-if="errors.city">{{ errors.city[0] }}</FieldError>
        </Field>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
          <Field>
            <FieldLabel>{{ trans('pages.settings.postcodes.fields.state') }}</FieldLabel>
            <Input v-model="form.state" class="mt-1 w-full" />
            <FieldError v-if="errors.state">{{ errors.state[0] }}</FieldError>
          </Field>
          <Field>
            <FieldLabel>{{ trans('pages.settings.postcodes.fields.country') }}</FieldLabel>
            <Input v-model="form.country" class="mt-1 w-full" />
            <FieldError v-if="errors.country">{{ errors.country[0] }}</FieldError>
          </Field>
          <Field>
            <FieldLabel>{{ trans('pages.settings.postcodes.fields.municipality') }}</FieldLabel>
            <Input v-model="form.municipality" class="mt-1 w-full" />
            <FieldError v-if="errors.municipality">{{ errors.municipality[0] }}</FieldError>
          </Field>
        </div>
        <div class="flex gap-2">
          <Button type="submit">{{ trans('pages.settings.postcodes.actions.create') }}</Button>
          <Button type="button" variant="secondary" @click="cancel">{{ trans('pages.settings.postcodes.actions.cancel') }}</Button>
        </div>
      </FieldSet>
    </form>
  </SidebarLayout>
</template>
