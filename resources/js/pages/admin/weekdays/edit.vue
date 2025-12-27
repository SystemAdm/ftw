<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select/index';
import { add as addExclusion, remove as removeExclusion } from '@/routes/admin/weekdays/exclusions';
import { show as showRoute, update as updateRoute } from '@/routes/admin/weekdays/index';
import { router, usePage } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';
import { Field, FieldError, FieldLabel, FieldSet } from '@/components/ui/field';
import { trans } from 'laravel-vue-i18n';

const page = usePage<PageProps>();
const weekday = (page.props as any).weekday as any;
const teams = (page.props as any).teams as Array<{ id: number; name: string }>;
const locations = (page.props as any).locations as Array<{ id: number; name: string }>;

const form = reactive({
    name: (weekday.name ?? '') as string,
    description: (weekday.description ?? '') as string,
    weekday: weekday.weekday as number,
    team_id: (weekday.team?.id ?? weekday.team_id) as number | undefined,
    location_id: (weekday.location?.id ?? weekday.location_id) as number | undefined,
    active: Boolean(weekday.active),
    start_time: weekday.start_time.slice(0, 5),
    end_time: weekday.end_time.slice(0, 5),
    // Nullable date fields as strings for <input type="date">
    event_start: (weekday.event_start ? String(weekday.event_start).substring(0, 10) : '') as string | '',
    event_end: (weekday.event_end ? String(weekday.event_end).substring(0, 10) : '') as string | '',
});

const errors = reactive<Record<string, string[]>>({});

const exclusionDate = reactive<{ date: string }>({ date: '' });

const sortedExclusions = computed(() => (weekday.exclusions ?? []).slice().sort((a: any, b: any) => (a.excluded_date < b.excluded_date ? 1 : -1)));

function submit() {
    // Coerce empty strings to null for nullable date fields
    const payload: Record<string, any> = {
        ...form,
        event_start: form.event_start ? form.event_start : null,
        event_end: form.event_end ? form.event_end : null,
    };

    router.put(updateRoute.url(weekday.id), payload, {
        onError: (err) => Object.assign(errors, err as any),
        onSuccess: () => router.visit(showRoute.url(weekday.id)),
    });
}

function addExcl() {
    if (!exclusionDate.date) return;
    router.post(addExclusion.url(weekday.id), { excluded_date: exclusionDate.date });
}

function removeExcl(exclusionId: number) {
    router.delete(removeExclusion.url([weekday.id, exclusionId]));
}

function cancel() {
    router.visit(showRoute.url(weekday.id));
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
        <h1 class="mb-4 text-xl font-semibold">{{ trans('pages.settings.weekdays.edit') }}</h1>

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
                    <Button type="submit">{{ trans('pages.settings.locations.actions.save') }}</Button>
                    <Button type="button" variant="secondary" @click="cancel">{{ trans('pages.settings.locations.actions.cancel') }}</Button>
                </div>
            </FieldSet>
        </form>

        <div class="mt-10 max-w-3xl">
            <h2 class="mb-4 text-lg font-semibold">{{ trans('pages.settings.weekdays.fields.exclusions') }}</h2>
            <div class="mb-4 flex items-end gap-2">
                <div class="flex-1 space-y-2">
                    <Label for="excl_date">{{ trans('pages.settings.weekdays.fields.date') }}</Label>
                    <Input id="excl_date" v-model="exclusionDate.date" type="date" class="w-full" />
                </div>
                <Button type="button" @click="addExcl">{{ trans('pages.settings.locations.actions.create') }}</Button>
            </div>

            <ul class="divide-y rounded-md border">
                <li v-if="sortedExclusions.length === 0" class="p-4 text-center text-sm text-muted-foreground">
                    {{ trans('pages.settings.locations.none') }}
                </li>
                <li v-for="ex in sortedExclusions" :key="ex.id" class="flex items-center justify-between p-4">
                    <span>{{ ex.excluded_date_formatted ?? ex.excluded_date }}</span>
                    <Button size="sm" variant="secondary" @click="removeExcl(ex.id)">{{ trans('pages.settings.weekdays.actions.remove_exclusion') }}</Button>
                </li>
            </ul>
        </div>
    </SidebarLayout>
</template>
