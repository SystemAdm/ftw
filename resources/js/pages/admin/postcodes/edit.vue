<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Field, FieldError, FieldLabel, FieldSet } from '@/components/ui/field';
import { router, usePage } from '@inertiajs/vue3';
import { show as showRoute, update as updateRoute, index as indexRoute } from '@/routes/admin/postcodes';
import { reactive } from 'vue';

const page = usePage();
const postcode = (page.props as any).postcode as any;

const form = reactive({
  city: postcode.city as string,
  state: postcode.state as string | undefined,
  country: postcode.country as string | undefined,
  county: postcode.county as string | undefined,
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
    <h1 class="mb-4 text-xl font-semibold">Edit Postal Code</h1>
    <form class="max-w-xl space-y-4" @submit.prevent="submit">
      <FieldSet>
        <Field>
          <FieldLabel>Postal Code</FieldLabel>
          <Input :model-value="String(postcode.postal_code)" disabled class="mt-1 w-full opacity-60" />
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
          <Button type="submit">Save</Button>
          <Button type="button" variant="secondary" @click="cancel">Cancel</Button>
        </div>
      </FieldSet>
    </form>
  </SidebarLayout>
</template>
