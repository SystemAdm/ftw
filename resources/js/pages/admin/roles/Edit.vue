<script setup lang="ts">
import { usePage, router } from '@inertiajs/vue3';
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { reactive } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Button } from '@/components/ui/button';
import { Field, FieldError, FieldLabel } from '@/components/ui/field';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { trans } from 'laravel-vue-i18n';

const page = usePage<PageProps>();
const role = (page.props as any).role;
const permissions = (page.props as any).permissions ?? [];
const teams = (page.props as any).teams ?? [];

const form = reactive({
    name: role.name as string,
    team_id: (role.team_id ?? 0).toString(),
    guard_name: role.guard_name as string,
    permissions: (role.permissions ?? []).map((p: any) => p.id) as number[],
});
const errors = reactive<Record<string, string[]>>({});

function togglePermission(id: number, checked: boolean | 'indeterminate') {
    const isChecked = checked === true;
    const idx = form.permissions.indexOf(id);
    if (isChecked && idx === -1) form.permissions.push(id);
    if (!isChecked && idx !== -1) form.permissions.splice(idx, 1);
}

function submit() {
    router.put(`/admin/roles/${role.id}`, form, {
        onError: (err) => Object.assign(errors, err as any),
    });
}
</script>

<template>
    <SidebarLayout>
        <h1 class="mb-4 text-xl font-semibold">{{ trans('pages.settings.roles.edit') }}</h1>

        <form class="max-w-xl space-y-4" @submit.prevent="submit">
            <Field>
                <FieldLabel>{{ trans('pages.settings.roles.fields.name') }}</FieldLabel>
                <Input v-model="form.name" :class="{ 'border-red-500': errors.name }" @input="errors.name = [] as any" />
                <FieldError v-if="errors.name">{{ errors.name[0] }}</FieldError>
            </Field>

            <Field>
                <FieldLabel>{{ trans('pages.settings.roles.fields.scope') }}</FieldLabel>
                <Select v-model="form.team_id">
                    <SelectTrigger>
                        <SelectValue :placeholder="trans('pages.settings.roles.fields.global')" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="0">{{ trans('pages.settings.roles.fields.global') }}</SelectItem>
                        <SelectItem v-for="team in teams" :key="team.id" :value="team.id.toString()">
                            {{ team.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <FieldError v-if="errors.team_id">{{ errors.team_id[0] }}</FieldError>
            </Field>

            <Field>
                <FieldLabel>{{ trans('pages.settings.roles.fields.guard') }}</FieldLabel>
                <Input v-model="form.guard_name" />
            </Field>

            <div>
                <Label class="block text-sm font-medium">{{ trans('pages.settings.roles.fields.permissions') }}</Label>
                <div class="mt-2 grid max-h-64 grid-cols-1 gap-2 overflow-y-auto rounded border p-3 sm:grid-cols-2">
                    <label v-for="p in permissions" :key="p.id" class="flex items-center gap-2">
                        <Checkbox :model-value="form.permissions.includes(p.id)" @update:model-value="(v) => togglePermission(p.id, v)" />
                        <span class="text-sm">{{ p.name }}</span>
                    </label>
                </div>
                <div v-if="errors.permissions" class="mt-1 text-sm text-red-600">{{ errors.permissions[0] }}</div>
            </div>

            <div class="flex gap-2">
                <Button>{{ trans('pages.settings.locations.actions.save') }}</Button>
                <Button variant="secondary" @click.prevent="router.visit(`/admin/roles/${role.id}`)">{{ trans('pages.settings.locations.actions.cancel') }}</Button>
            </div>
        </form>
    </SidebarLayout>
</template>
