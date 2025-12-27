<script setup lang="ts">
import { usePage, router } from '@inertiajs/vue3';
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { reactive } from 'vue';
import { Textarea } from '@/components/ui/textarea';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Field, FieldError, FieldLabel } from '@/components/ui/field';
import { Checkbox } from '@/components/ui/checkbox';
import { index } from '@/routes/admin/teams';
import { trans } from 'laravel-vue-i18n';

const page = usePage<PageProps>();
const usersList = (page.props as any).users ?? [];

const form = reactive({
    name: '',
    slug: '',
    description: '',
    logo: '',
    active: true,
    users: [] as number[],
});

const errors = reactive<Record<string, string[]>>({});

function submit() {
    router.post('/admin/teams', form, {
        onError: (err) => Object.assign(errors, err as any),
    });
}

function toggleUser(userId: number, checked: boolean | 'indeterminate') {
    const isChecked = checked === true;
    const idx = form.users.indexOf(userId);
    if (isChecked && idx === -1) form.users.push(userId);
    if (!isChecked && idx !== -1) form.users.splice(idx, 1);
}
</script>

<template>
    <SidebarLayout>
        <h1 class="mb-4 text-xl font-semibold">{{ trans('pages.settings.teams.new') }}</h1>

        <form class="max-w-xl space-y-4" @submit.prevent="submit">
            <Field>
                <FieldLabel>{{ trans('pages.settings.teams.fields.name') }}</FieldLabel>
                <Input
                    v-model="form.name"
                    type="text"
                    :class="{ 'border-red-500': errors.name }"
                    @input="errors.name = ''"
                />
                <FieldError v-if="errors.name">{{ errors.name[0] }}</FieldError>
            </Field>

            <Field>
                <FieldLabel>{{ trans('pages.settings.teams.fields.slug') }}</FieldLabel>
                <Input
                    v-model="form.slug"
                    type="text"
                    :class="{ 'border-red-500': errors.slug }"
                    @input="errors.slug = ''"
                />
                <FieldError v-if="errors.slug">{{ errors.slug[0] }}</FieldError>
            </Field>

            <Field>
                <FieldLabel>{{ trans('pages.settings.teams.fields.description') }}</FieldLabel>
                <Textarea
                    v-model="form.description"
                    :class="{ 'border-red-500': errors.description }"
                    @input="errors.description = ''"
                ></Textarea>
                <FieldError v-if="errors.description">{{ errors.description[0] }}</FieldError>
            </Field>

            <Field>
                <FieldLabel>{{ trans('pages.settings.teams.fields.logo_url') }}</FieldLabel>
                <Input
                    v-model="form.logo"
                    type="url"
                    :class="{ 'border-red-500': errors.logo }"
                    @input="errors.logo = ''"
                />
                <FieldError v-if="errors.logo">{{ errors.logo[0] }}</FieldError>
            </Field>

            <div>
                <Label class="block text-sm font-medium">{{ trans('pages.settings.teams.fields.members') }}</Label>
                <div class="mt-2 grid max-h-64 grid-cols-1 gap-2 overflow-y-auto rounded border p-3 sm:grid-cols-2">
                    <label v-for="u in usersList" :key="u.id" class="flex items-center gap-2">
                        <Checkbox
                            :model-value="form.users.includes(u.id)"
                            @update:model-value="(val) => toggleUser(u.id, val as boolean | 'indeterminate')"
                            :aria-label="`Select ${u.name}`"
                        />
                        <span class="text-sm">{{ u.name }}</span>
                    </label>
                </div>
                <div v-if="errors.users" class="mt-1 text-sm text-red-600">{{ errors.users[0] }}</div>
            </div>

            <div class="flex items-center gap-2">
                <Checkbox :model-value="form.active" @update:model-value="(v) => (form.active = v)" />
                <Label>{{ trans('pages.settings.teams.fields.active') }}</Label>
            </div>

            <div class="flex gap-2">
                <Button type="submit">{{ trans('pages.settings.locations.actions.save') }}</Button>
                <Button variant="secondary" @click.prevent="router.visit(index.url())">{{ trans('pages.settings.locations.actions.cancel') }}</Button>
            </div>
        </form>
    </SidebarLayout>
</template>
