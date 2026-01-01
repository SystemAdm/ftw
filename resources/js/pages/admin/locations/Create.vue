<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Checkbox } from '@/components/ui/checkbox';
import { Field, FieldError, FieldLabel, FieldSet } from '@/components/ui/field';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select/index';
import { router, usePage } from '@inertiajs/vue3';
import { store as storeRoute, index as indexRoute } from '@/routes/admin/locations';
import { reactive, computed } from 'vue';
import { trans } from 'laravel-vue-i18n';

const page = usePage();
const postalCodes = (page.props as any).postalCodes as Array<{ postal_code: number; city: string }>;

const form = reactive({
  postal_code: '' as string,
  name: '' as string,
  active: true as boolean,
  description: '' as string,
  latitude: '' as string,
  longitude: '' as string,
  google_maps_url: '' as string,
  images: '' as string,
  street_address: '' as string,
  street_number: '' as string,
  link: '' as string,
});

const errors = reactive<Record<string, string[]>>({});

const postalCodeValue = computed<string>({
  get: () => String(form.postal_code ?? ''),
  set: (v) => { form.postal_code = v; },
});

function submit() {
  const payload: Record<string, any> = {
    ...form,
    postal_code: form.postal_code ? Number(form.postal_code) : undefined,
    latitude: form.latitude === '' ? undefined : Number(form.latitude),
    longitude: form.longitude === '' ? undefined : Number(form.longitude),
  };
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
    <h1 class="mb-4 text-xl font-semibold">{{ trans('pages.settings.locations.new') }}</h1>
    <form class="max-w-3xl space-y-4" @submit.prevent="submit">
      <FieldSet>
        <div class="grid grid-cols-2 gap-4">
          <Field>
            <FieldLabel>{{ trans('pages.settings.locations.fields.postal_code') }}</FieldLabel>
            <Select :model-value="postalCodeValue" @update:model-value="(v) => (postalCodeValue = v as string)">
              <SelectTrigger>
                <SelectValue :placeholder="trans('pages.settings.locations.fields.postal_code')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem v-for="pc in postalCodes" :key="pc.postal_code" :value="String(pc.postal_code)">{{ pc.postal_code }} - {{ pc.city }}</SelectItem>
              </SelectContent>
            </Select>
            <FieldError v-if="errors.postal_code" class="mt-1 text-sm text-red-600">{{ errors.postal_code[0] }}</FieldError>
          </Field>
          <Field>
            <FieldLabel>{{ trans('pages.settings.locations.fields.name') }}</FieldLabel>
            <Input v-model="form.name" class="mt-1 w-full" />
            <FieldError v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name[0] }}</FieldError>
          </Field>
        </div>

        <Field>
          <FieldLabel>{{ trans('pages.settings.locations.fields.description') }}</FieldLabel>
          <Input v-model="form.description" class="mt-1 w-full" />
          <FieldError v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description[0] }}</FieldError>
        </Field>

        <div class="grid grid-cols-2 gap-4">
          <Field>
            <FieldLabel>{{ trans('pages.settings.locations.fields.latitude') }}</FieldLabel>
            <Input v-model="form.latitude" type="number" step="0.000001" class="mt-1 w-full" />
            <FieldError v-if="errors.latitude" class="mt-1 text-sm text-red-600">{{ errors.latitude[0] }}</FieldError>
          </Field>
          <Field>
            <FieldLabel>{{ trans('pages.settings.locations.fields.longitude') }}</FieldLabel>
            <Input v-model="form.longitude" type="number" step="0.000001" class="mt-1 w-full" />
            <FieldError v-if="errors.longitude" class="mt-1 text-sm text-red-600">{{ errors.longitude[0] }}</FieldError>
          </Field>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <Field>
            <FieldLabel>{{ trans('pages.settings.locations.fields.google_maps_url') }}</FieldLabel>
            <Input v-model="form.google_maps_url" type="url" class="mt-1 w-full" />
            <FieldError v-if="errors.google_maps_url" class="mt-1 text-sm text-red-600">{{ errors.google_maps_url[0] }}</FieldError>
          </Field>
          <Field>
            <FieldLabel>{{ trans('pages.settings.locations.fields.website') }}</FieldLabel>
            <Input v-model="form.link" type="url" class="mt-1 w-full" />
            <FieldError v-if="errors.link" class="mt-1 text-sm text-red-600">{{ errors.link[0] }}</FieldError>
          </Field>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <Field>
            <FieldLabel>{{ trans('pages.settings.locations.fields.street_address') }}</FieldLabel>
            <Input v-model="form.street_address" class="mt-1 w-full" />
            <FieldError v-if="errors.street_address" class="mt-1 text-sm text-red-600">{{ errors.street_address[0] }}</FieldError>
          </Field>
          <Field>
            <FieldLabel>{{ trans('pages.settings.locations.fields.street_number') }}</FieldLabel>
            <Input v-model="form.street_number" class="mt-1 w-full" />
            <FieldError v-if="errors.street_number" class="mt-1 text-sm text-red-600">{{ errors.street_number[0] }}</FieldError>
          </Field>
        </div>

        <Field>
          <div class="flex items-center gap-2">
            <Checkbox :model-value="form.active" @update:model-value="(v) => (form.active = v)" />
            <FieldLabel>{{ trans('pages.settings.locations.fields.active') }}</FieldLabel>
          </div>
          <FieldError v-if="errors.active" class="mt-1 text-sm text-red-600">{{ errors.active[0] }}</FieldError>
        </Field>

        <div class="flex gap-2">
          <Button type="submit">{{ trans('pages.settings.locations.actions.create') }}</Button>
          <Button type="button" variant="secondary" @click="cancel">{{ trans('pages.settings.locations.actions.cancel') }}</Button>
        </div>
      </FieldSet>
    </form>
  </SidebarLayout>
</template>
