<script setup lang="ts">
import { usePage, useForm, router } from '@inertiajs/vue3';
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
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

const form = useForm({
    name: '',
    slug: '',
    description: '',
    logo: null as File | null,
    active: true,
    applications_enabled: true,
    users: [] as number[],
});

function submit() {
    form.post('/admin/teams', {
        forceFormData: true,
    });
}

function onFileChange(e: Event) {
    const target = e.target as HTMLInputElement;
    if (target.files?.length) {
        form.logo = target.files[0];
    }
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
                />
                <FieldError v-if="form.errors.name">{{ form.errors.name }}</FieldError>
            </Field>

            <Field>
                <FieldLabel>{{ trans('pages.settings.teams.fields.slug') }}</FieldLabel>
                <Input
                    v-model="form.slug"
                    type="text"
                />
                <FieldError v-if="form.errors.slug">{{ form.errors.slug }}</FieldError>
            </Field>

            <Field>
                <FieldLabel>{{ trans('pages.settings.teams.fields.description') }}</FieldLabel>
                <Textarea
                    v-model="form.description"
                ></Textarea>
                <FieldError v-if="form.errors.description">{{ form.errors.description }}</FieldError>
            </Field>

            <Field>
                <FieldLabel>{{ trans('pages.settings.teams.fields.logo') }}</FieldLabel>
                <Input
                    type="file"
                    accept="image/*"
                    @change="onFileChange"
                />
                <FieldError v-if="form.errors.logo">{{ form.errors.logo }}</FieldError>
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
                <div v-if="form.errors.users" class="mt-1 text-sm text-red-600">{{ form.errors.users }}</div>
            </div>

            <div class="flex items-center gap-2">
                <Checkbox :model-value="form.active" @update:model-value="(v) => (form.active = v)" />
                <Label>{{ trans('pages.settings.teams.fields.active') }}</Label>
            </div>

            <div class="flex items-center gap-2">
                <Checkbox :model-value="form.applications_enabled" @update:model-value="(v) => (form.applications_enabled = v)" />
                <Label>{{ trans('pages.settings.teams.fields.applications_enabled') || 'Applications Enabled' }}</Label>
            </div>

            <div class="flex gap-2">
                <Button type="submit">{{ trans('pages.settings.locations.actions.save') }}</Button>
                <Button variant="secondary" @click.prevent="router.visit(index.url())">{{ trans('pages.settings.locations.actions.cancel') }}</Button>
            </div>
        </form>
    </SidebarLayout>
</template>
