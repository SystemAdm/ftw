<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { usePage, router } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import { Button } from '@/components/ui/button';
import { Field, FieldError, FieldLabel, FieldSet } from '@/components/ui/field';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { index as indexRoute } from '@/routes/admin/weekdays';
import { trans } from 'laravel-vue-i18n';

const page = usePage<PageProps>();
const teams = (page.props as any).teams as Array<{ id: number; name: string }>;
const locations = (page.props as any).locations as Array<{ id: number; name: string }>;

const form = reactive({
  name: '' as string,
  description: '' as string,
  weekday: 1 as number,
  team_id: undefined as number | undefined,
  location_id: undefined as number | undefined,
  active: true,
  start_time: '18:00',
  end_time: '21:00',
});

const errors = reactive<Record<string, string[]>>({});

function submit() {
  const payload: Record<string, any> = {
    ...form,
    event_start: (form as any).event_start ? (form as any).event_start : null,
    event_end: (form as any).event_end ? (form as any).event_end : null,
  };
  router.post('/admin/weekdays', payload, {
    onError: (err) => Object.assign(errors, err as any),
  });
}
// Bridge numeric/optional values to Select's string v-model
const weekdayValue = computed<string>({
  get: () => String(form.weekday ?? ''),
  set: (v) => {
    form.weekday = v === '' ? 0 : Number(v);
  },
});

const teamIdValue = computed<string>({
  get: () => (form.team_id == null ? '__none__' : String(form.team_id)),
  set: (v) => {
    form.team_id = v === '__none__' ? undefined : Number(v);
  },
});

const locationIdValue = computed<string>({
  get: () => (form.location_id == null ? '__none__' : String(form.location_id)),
  set: (v) => {
    form.location_id = v === '__none__' ? undefined : Number(v);
  },
});

</script>

<template>
  <SidebarLayout>
    <h1 class="mb-4 text-xl font-semibold">{{ trans('pages.settings.weekdays.new') }}</h1>

    <form class="max-w-3xl space-y-4" @submit.prevent="submit">
      <FieldSet>
        <Field>
          <FieldLabel>{{ trans('pages.settings.locations.fields.name') }}</FieldLabel>
          <Input v-model="form.name" class="mt-1 w-full" type="text" />
          <FieldError v-if="errors.name">{{ errors.name[0] }}</FieldError>
        </Field>

        <Field>
          <FieldLabel>{{ trans('pages.settings.weekdays.fields.description') }}</FieldLabel>
          <Textarea v-model="form.description" class="mt-1 w-full" rows="3" />
          <FieldError v-if="errors.description">{{ errors.description[0] }}</FieldError>
        </Field>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <Field>
            <FieldLabel>{{ trans('pages.settings.weekdays.fields.weekday') }}</FieldLabel>
            <Select :model-value="weekdayValue" @update:model-value="(v) => (weekdayValue = v as string)">
              <SelectTrigger>
                <SelectValue :placeholder="trans('pages.settings.weekdays.fields.weekday')" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem v-for="n in 7" :key="n - 1" :value="String(n - 1)">{{ trans(`pages.settings.weekdays.days.${n-1}`) }}</SelectItem>
              </SelectContent>
            </Select>
            <FieldError v-if="errors.weekday">{{ errors.weekday[0] }}</FieldError>
          </Field>

          <Field>
            <FieldLabel>{{ trans('pages.settings.weekdays.fields.team') }}</FieldLabel>
            <Select :model-value="teamIdValue" @update:model-value="(v) => (teamIdValue = v as string)">
              <SelectTrigger>
                <SelectValue placeholder="—" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="__none__">—</SelectItem>
                <SelectItem v-for="t in teams" :key="t.id" :value="String(t.id)">{{ t.name }}</SelectItem>
              </SelectContent>
            </Select>
            <FieldError v-if="errors.team_id">{{ errors.team_id[0] }}</FieldError>
          </Field>
        </div>

        <Field>
          <FieldLabel>{{ trans('pages.settings.weekdays.fields.location') }}</FieldLabel>
          <Select :model-value="locationIdValue" @update:model-value="(v) => (locationIdValue = v as string)">
            <SelectTrigger>
              <SelectValue placeholder="—" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="__none__">—</SelectItem>
              <SelectItem v-for="l in locations" :key="l.id" :value="String(l.id)">{{ l.name }}</SelectItem>
            </SelectContent>
          </Select>
          <FieldError v-if="errors.location_id">{{ errors.location_id[0] }}</FieldError>
        </Field>

        <div class="grid grid-cols-2 gap-4">
          <Field>
            <FieldLabel>{{ trans('pages.settings.weekdays.fields.start_time') }}</FieldLabel>
            <Input v-model="form.start_time" class="mt-1 w-full" type="time" />
            <FieldError v-if="errors.start_time">{{ errors.start_time[0] }}</FieldError>
          </Field>
          <Field>
            <FieldLabel>{{ trans('pages.settings.weekdays.fields.end_time') }}</FieldLabel>
            <Input v-model="form.end_time" class="mt-1 w-full" type="time" />
            <FieldError v-if="errors.end_time">{{ errors.end_time[0] }}</FieldError>
          </Field>
        </div>

        <Field>
          <div class="flex items-center gap-2">
            <Checkbox :model-value="form.active" @update:model-value="(v) => (form.active = v)" />
            <FieldLabel>{{ trans('pages.settings.weekdays.fields.active') }}</FieldLabel>
          </div>
          <FieldError v-if="errors.active">{{ errors.active[0] }}</FieldError>
        </Field>

        <div class="flex gap-2 pt-4">
          <Button type="submit">{{ trans('pages.settings.locations.actions.create') }}</Button>
          <Button type="button" variant="secondary" @click.prevent="router.visit(indexRoute.url())">{{ trans('pages.settings.locations.actions.cancel') }}</Button>
        </div>
      </FieldSet>
    </form>
  </SidebarLayout>
</template>
