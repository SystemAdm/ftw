<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Field, FieldError, FieldLabel, FieldSet } from '@/components/ui/field';
import { router, usePage } from '@inertiajs/vue3';
import { show as showRoute, update as updateRoute } from '@/routes/admin/postcodes';
import { reactive } from 'vue';
import { trans } from 'laravel-vue-i18n';

const page = usePage();
const postcode = (page.props as any).postcode as any;

const form = reactive({
  city: postcode.city as string,
  state: postcode.state as string | undefined,
  country: postcode.country as string | undefined,
  municipality: postcode.municipality as string | undefined,
});

const errors = reactive<Record<string, string[]>>({});

function submit() {
  router.put(updateRoute.url(postcode.postal_code), form, {
    onError: (e) => Object.assign(errors, e as any),
    onSuccess: () => router.visit(showRoute.url(postcode.postal_code)),
  });
}

function cancel() {
  router.visit(showRoute.url(postcode.postal_code));
}
</script>

<template>
  <SidebarLayout>
    <h1 class="mb-4 text-xl font-semibold">{{ trans('pages.settings.postcodes.edit') }}</h1>
    <form class="max-w-xl space-y-4" @submit.prevent="submit">
      <FieldSet>
        <Field>
          <FieldLabel>{{ trans('pages.settings.postcodes.fields.postal_code') }}</FieldLabel>
          <Input :model-value="String(postcode.postal_code)" disabled class="mt-1 w-full opacity-60" />
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
          <Button type="submit">{{ trans('pages.settings.postcodes.actions.save') }}</Button>
          <Button type="button" variant="secondary" @click="cancel">{{ trans('pages.settings.postcodes.actions.cancel') }}</Button>
        </div>
      </FieldSet>
    </form>
  </SidebarLayout>
</template>
