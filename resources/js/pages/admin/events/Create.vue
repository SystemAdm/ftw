<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import { Button } from '@/components/ui/button';
import { Field, FieldError, FieldLabel, FieldSet } from '@/components/ui/field';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select/index';
import ImagePicker from '@/components/custom/ImagePicker.vue';
import { index as indexRoute } from '@/routes/admin/events/index';
import { trans } from 'laravel-vue-i18n';

const page = usePage<PageProps>();
const locations = (page.props as any).locations as Array<{ id: number; name: string }>;

const form = useForm({
  title: '',
  excerpt: '',
  description: '',
  image: null as File | null,
  image_path: '' as string | null,
  location_id: null as number | null,
  event_start: '',
  event_end: '',
  signup_needed: false,
  signup_start: '',
  signup_end: '',
  age_min: null as number | null,
  age_max: null as number | null,
  number_of_seats: null as number | null,
  status: 'draft',
});

function submit() {
  form.transform((data) => ({
    ...data,
    event_start: data.event_start ? new Date(data.event_start).toISOString() : null,
    event_end: data.event_end ? new Date(data.event_end).toISOString() : null,
    signup_start: data.signup_start ? new Date(data.signup_start).toISOString() : null,
    signup_end: data.signup_end ? new Date(data.signup_end).toISOString() : null,
  })).post('/admin/events');
}

const locationIdValue = computed<string>({
  get: () => (form.location_id == null ? '__none__' : String(form.location_id)),
  set: (v) => {
    form.location_id = v === '__none__' ? null : Number(v);
  },
});

</script>

<template>
  <SidebarLayout>
    <h1 class="mb-4 text-xl font-semibold">{{ trans('pages.settings.events.new') }}</h1>

    <form class="max-w-3xl space-y-4 pb-12" @submit.prevent="submit">
      <FieldSet>
        <Field>
          <FieldLabel>{{ trans('pages.settings.events.fields.title') }}</FieldLabel>
          <Input v-model="form.title" class="mt-1 w-full" type="text" />
          <FieldError v-if="form.errors.title">{{ form.errors.title }}</FieldError>
        </Field>

        <Field>
          <FieldLabel>{{ trans('pages.settings.events.fields.excerpt') }}</FieldLabel>
          <Textarea v-model="form.excerpt" class="mt-1 w-full" rows="2" />
          <FieldError v-if="form.errors.excerpt">{{ form.errors.excerpt }}</FieldError>
        </Field>

        <Field>
          <FieldLabel>{{ trans('pages.settings.events.fields.description') }}</FieldLabel>
          <Textarea v-model="form.description" class="mt-1 w-full" rows="5" />
          <FieldError v-if="form.errors.description">{{ form.errors.description }}</FieldError>
        </Field>

        <Field>
          <FieldLabel>{{ trans('pages.settings.events.fields.image') }}</FieldLabel>
          <ImagePicker
            v-model="form.image_path"
            @update:image-file="(file) => form.image = file"
          />
          <FieldError v-if="form.errors.image">{{ form.errors.image }}</FieldError>
          <FieldError v-if="form.errors.image_path">{{ form.errors.image_path }}</FieldError>
        </Field>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <Field>
              <FieldLabel>{{ trans('pages.settings.events.fields.event_start') }}</FieldLabel>
              <Input v-model="form.event_start" class="mt-1 w-full" type="datetime-local" />
              <FieldError v-if="form.errors.event_start">{{ form.errors.event_start }}</FieldError>
            </Field>

            <Field>
              <FieldLabel>{{ trans('pages.settings.events.fields.event_end') }}</FieldLabel>
              <Input v-model="form.event_end" class="mt-1 w-full" type="datetime-local" />
              <FieldError v-if="form.errors.event_end">{{ form.errors.event_end }}</FieldError>
            </Field>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <Field>
              <FieldLabel>{{ trans('pages.settings.events.fields.location') }}</FieldLabel>
              <Select :model-value="locationIdValue" @update:model-value="(v) => (locationIdValue = v as string)">
                <SelectTrigger>
                  <SelectValue placeholder="—" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="__none__">—</SelectItem>
                  <SelectItem v-for="l in locations" :key="l.id" :value="String(l.id)">{{ l.name }}</SelectItem>
                </SelectContent>
              </Select>
              <FieldError v-if="form.errors.location_id">{{ form.errors.location_id }}</FieldError>
            </Field>

            <Field>
              <FieldLabel>{{ trans('pages.settings.events.fields.status') }}</FieldLabel>
              <Select v-model="form.status">
                <SelectTrigger>
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="draft">{{ trans('pages.settings.events.status.draft') }}</SelectItem>
                  <SelectItem value="published">{{ trans('pages.settings.events.status.published') }}</SelectItem>
                  <SelectItem value="cancelled">{{ trans('pages.settings.events.status.cancelled') }}</SelectItem>
                </SelectContent>
              </Select>
              <FieldError v-if="form.errors.status">{{ form.errors.status }}</FieldError>
            </Field>
        </div>

        <div class="space-y-4 rounded-lg border p-4 bg-muted/20">
            <Field>
              <div class="flex items-center gap-2">
                <Checkbox :model-value="form.signup_needed" @update:model-value="(v) => (form.signup_needed = v)" />
                <FieldLabel>{{ trans('pages.settings.events.fields.signup_needed') }}</FieldLabel>
              </div>
              <FieldError v-if="form.errors.signup_needed">{{ form.errors.signup_needed }}</FieldError>
            </Field>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <Field>
                  <FieldLabel>{{ trans('pages.settings.events.fields.signup_start') }}</FieldLabel>
                  <Input v-model="form.signup_start" class="mt-1 w-full" type="datetime-local" />
                  <FieldError v-if="form.errors.signup_start">{{ form.errors.signup_start }}</FieldError>
                </Field>

                <Field>
                  <FieldLabel>{{ trans('pages.settings.events.fields.signup_end') }}</FieldLabel>
                  <Input v-model="form.signup_end" class="mt-1 w-full" type="datetime-local" />
                  <FieldError v-if="form.errors.signup_end">{{ form.errors.signup_end }}</FieldError>
                </Field>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <Field>
              <FieldLabel>{{ trans('pages.settings.events.fields.age_min') }}</FieldLabel>
              <Input v-model="form.age_min" class="mt-1 w-full" type="number" min="0" />
              <FieldError v-if="form.errors.age_min">{{ form.errors.age_min }}</FieldError>
            </Field>

            <Field>
              <FieldLabel>{{ trans('pages.settings.events.fields.age_max') }}</FieldLabel>
              <Input v-model="form.age_max" class="mt-1 w-full" type="number" min="0" />
              <FieldError v-if="form.errors.age_max">{{ form.errors.age_max }}</FieldError>
            </Field>

            <Field>
              <FieldLabel>{{ trans('pages.settings.events.fields.seats') }}</FieldLabel>
              <Input v-model="form.number_of_seats" class="mt-1 w-full" type="number" min="-1" />
              <FieldError v-if="form.errors.number_of_seats">{{ form.errors.number_of_seats }}</FieldError>
            </Field>
        </div>

        <div class="flex gap-2 pt-4">
          <Button type="submit" :disabled="form.processing">{{ trans('pages.settings.events.actions.create') }}</Button>
          <Button type="button" variant="secondary" @click.prevent="router.visit(indexRoute.url())">{{ trans('pages.settings.events.actions.cancel') }}</Button>
        </div>
      </FieldSet>
    </form>
  </SidebarLayout>
</template>
