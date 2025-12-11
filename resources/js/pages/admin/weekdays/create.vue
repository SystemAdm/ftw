<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { usePage, router } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';
import { Input } from '@/components/ui/input';
import { Checkbox } from '@/components/ui/checkbox';
import { Button } from '@/components/ui/button';
import { Field, FieldError, FieldLabel, FieldSet } from '@/components/ui/field';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select/index';
import { index as indexRoute } from '@/routes/admin/weekdays/index';

const page = usePage<PageProps>();
const teams = (page.props as any).teams as Array<{ id: number; name: string }>;
const locations = (page.props as any).locations as Array<{ id: number; name: string }>;

const form = reactive({
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
    <h1 class="mb-4 text-xl font-semibold">Create Weekday</h1>

    <form class="max-w-xl space-y-4" @submit.prevent="submit">
      <FieldSet>
        <Field>
          <FieldLabel class="block text-sm font-medium">Day of week</FieldLabel>
          <Select :model-value="weekdayValue" @update:model-value="(v) => (weekdayValue = v as string)">
            <SelectTrigger>
              <SelectValue placeholder="Select day" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="n in 7" :key="n - 1" :value="String(n - 1)">{{ ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'][n-1] }}</SelectItem>
            </SelectContent>
          </Select>
          <FieldError v-if="errors.weekday" class="mt-1 text-sm text-red-600">{{ errors.weekday[0] }}</FieldError>
        </Field>

        <Field>
          <FieldLabel class="block text-sm font-medium">Team (optional)</FieldLabel>
          <Select :model-value="teamIdValue" @update:model-value="(v) => (teamIdValue = v as string)">
            <SelectTrigger>
              <SelectValue placeholder="— None —" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="__none__">— None —</SelectItem>
              <SelectItem v-for="t in teams" :key="t.id" :value="String(t.id)">{{ t.name }}</SelectItem>
            </SelectContent>
          </Select>
          <FieldError v-if="errors.team_id" class="mt-1 text-sm text-red-600">{{ errors.team_id[0] }}</FieldError>
        </Field>

        <Field>
          <FieldLabel class="block text-sm font-medium">Location (optional)</FieldLabel>
          <Select :model-value="locationIdValue" @update:model-value="(v) => (locationIdValue = v as string)">
            <SelectTrigger>
              <SelectValue placeholder="— None —" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="__none__">— None —</SelectItem>
              <SelectItem v-for="l in locations" :key="l.id" :value="String(l.id)">{{ l.name }}</SelectItem>
            </SelectContent>
          </Select>
          <FieldError v-if="errors.location_id" class="mt-1 text-sm text-red-600">{{ errors.location_id[0] }}</FieldError>
        </Field>

        <div class="grid grid-cols-2 gap-4">
          <Field>
            <FieldLabel class="block text-sm font-medium">Start time</FieldLabel>
            <Input v-model="form.start_time" class="mt-1 w-full" type="time" />
            <FieldError v-if="errors.start_time" class="mt-1 text-sm text-red-600">{{ errors.start_time[0] }}</FieldError>
          </Field>
          <Field>
            <FieldLabel class="block text-sm font-medium">End time</FieldLabel>
            <Input v-model="form.end_time" class="mt-1 w-full" type="time" />
            <FieldError v-if="errors.end_time" class="mt-1 text-sm text-red-600">{{ errors.end_time[0] }}</FieldError>
          </Field>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <Field>
            <FieldLabel class="block text-sm font-medium">Event starts on (optional)</FieldLabel>
            <Input v-model="(form as any).event_start" class="mt-1 w-full" type="date" />
            <p class="mt-1 text-xs text-muted-foreground">Leave empty if it already started.</p>
            <FieldError v-if="errors.event_start" class="mt-1 text-sm text-red-600">{{ errors.event_start[0] }}</FieldError>
          </Field>
          <Field>
            <FieldLabel class="block text-sm font-medium">Event ends on (optional)</FieldLabel>
            <Input v-model="(form as any).event_end" class="mt-1 w-full" type="date" />
            <p class="mt-1 text-xs text-muted-foreground">Leave empty if the end is not defined.</p>
            <FieldError v-if="errors.event_end" class="mt-1 text-sm text-red-600">{{ errors.event_end[0] }}</FieldError>
          </Field>
        </div>

        <Field orientation="horizontal">
          <Checkbox :model-value="form.active" @update:model-value="(v) => (form.active = v === true)" />
          <FieldLabel class="inline-flex items-center gap-2">Active</FieldLabel>
        </Field>

        <div class="flex gap-2">
          <Button>Save</Button>
          <Button type="button" variant="secondary" @click.prevent="router.visit(indexRoute.url())">Cancel</Button>
        </div>
      </FieldSet>
    </form>
  </SidebarLayout>

</template>
