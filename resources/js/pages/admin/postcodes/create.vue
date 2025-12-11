<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Field, FieldError, FieldLabel, FieldSet } from '@/components/ui/field';
import { router } from '@inertiajs/vue3';
import { store as storeRoute, index as indexRoute } from '@/routes/admin/postcodes';
import { reactive } from 'vue';

const form = reactive({
  postal_code: '' as string,
  city: '' as string,
  state: '' as string,
  country: '' as string,
  county: '' as string,
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
    <h1 class="mb-4 text-xl font-semibold">New Postal Code</h1>
    <form class="max-w-xl space-y-4" @submit.prevent="submit">
      <FieldSet>
        <Field>
          <FieldLabel>Postal Code</FieldLabel>
          <Input v-model="form.postal_code" type="number" inputmode="numeric" class="mt-1 w-full" />
          <FieldError v-if="errors.postal_code" class="mt-1 text-sm text-red-600">{{ errors.postal_code[0] }}</FieldError>
        </Field>
        <Field>
          <FieldLabel>City</FieldLabel>
          <Input v-model="form.city" class="mt-1 w-full" />
          <FieldError v-if="errors.city" class="mt-1 text-sm text-red-600">{{ errors.city[0] }}</FieldError>
        </Field>
        <div class="grid grid-cols-3 gap-4">
          <Field>
            <FieldLabel>State</FieldLabel>
            <Input v-model="form.state" class="mt-1 w-full" />
            <FieldError v-if="errors.state" class="mt-1 text-sm text-red-600">{{ errors.state[0] }}</FieldError>
          </Field>
          <Field>
            <FieldLabel>Country</FieldLabel>
            <Input v-model="form.country" class="mt-1 w-full" />
            <FieldError v-if="errors.country" class="mt-1 text-sm text-red-600">{{ errors.country[0] }}</FieldError>
          </Field>
          <Field>
            <FieldLabel>County</FieldLabel>
            <Input v-model="form.county" class="mt-1 w-full" />
            <FieldError v-if="errors.county" class="mt-1 text-sm text-red-600">{{ errors.county[0] }}</FieldError>
          </Field>
        </div>
        <div class="flex gap-2">
          <Button type="submit">Create</Button>
          <Button type="button" variant="secondary" @click="cancel">Cancel</Button>
        </div>
      </FieldSet>
    </form>
  </SidebarLayout>

</template>
