<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { useForm, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Field, FieldError, FieldLabel, FieldSet } from '@/components/ui/field';
import { dashboard as adminDashboardRoute } from '@/routes/admin';
import { index as usersIndexRoute } from '@/routes/admin/users';
import { trans } from 'laravel-vue-i18n';
import { BreadcrumbItemType } from '@/types';

const breadcrumbs = computed<BreadcrumbItemType[]>(() => [
    {
        title: trans('ui.navigation.home'),
        href: '/',
    },
    {
        title: trans('ui.navigation.admin'),
        href: adminDashboardRoute.url(),
    },
    {
        title: trans('pages.settings.users.title'),
        href: usersIndexRoute.url(),
    },
    {
        title: trans('pages.settings.users.actions.create'),
        href: '#',
    },
]);

const form = useForm({
    given_name: '',
    middle_name: '',
    family_name: '',
    email: '',
    password: '',
    birthday: '',
    postal_code: '',
});

function submit() {
    form.post('/admin/users');
}
</script>

<template>
    <SidebarLayout :breadcrumbs="breadcrumbs">
        <h1 class="mb-4 text-xl font-semibold">{{ trans('pages.settings.users.actions.create') }}</h1>

        <form class="max-w-3xl space-y-4 pb-12" @submit.prevent="submit">
            <FieldSet>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <Field>
                        <FieldLabel>{{ trans('pages.settings.users.fields.given_name') }}</FieldLabel>
                        <Input v-model="form.given_name" class="mt-1 w-full" type="text" />
                        <FieldError v-if="form.errors.given_name">{{ form.errors.given_name }}</FieldError>
                    </Field>

                    <Field>
                        <FieldLabel>{{ trans('pages.settings.users.fields.middle_name') }}</FieldLabel>
                        <Input v-model="form.middle_name" class="mt-1 w-full" type="text" />
                        <FieldError v-if="form.errors.middle_name">{{ form.errors.middle_name }}</FieldError>
                    </Field>

                    <Field>
                        <FieldLabel>{{ trans('pages.settings.users.fields.family_name') }}</FieldLabel>
                        <Input v-model="form.family_name" class="mt-1 w-full" type="text" />
                        <FieldError v-if="form.errors.family_name">{{ form.errors.family_name }}</FieldError>
                    </Field>
                </div>

                <Field>
                    <FieldLabel>{{ trans('pages.settings.users.fields.email') }}</FieldLabel>
                    <Input v-model="form.email" class="mt-1 w-full" type="email" />
                    <FieldError v-if="form.errors.email">{{ form.errors.email }}</FieldError>
                </Field>

                <Field>
                    <FieldLabel>{{ trans('pages.settings.users.fields.password') }}</FieldLabel>
                    <Input v-model="form.password" class="mt-1 w-full" type="password" />
                    <FieldError v-if="form.errors.password">{{ form.errors.password }}</FieldError>
                </Field>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <Field>
                        <FieldLabel>{{ trans('pages.settings.users.fields.birthday') }}</FieldLabel>
                        <Input v-model="form.birthday" class="mt-1 w-full" type="date" />
                        <FieldError v-if="form.errors.birthday">{{ form.errors.birthday }}</FieldError>
                    </Field>

                    <Field>
                        <FieldLabel>{{ trans('pages.settings.users.fields.postal_code') }}</FieldLabel>
                        <Input v-model="form.postal_code" class="mt-1 w-full" type="text" />
                        <FieldError v-if="form.errors.postal_code">{{ form.errors.postal_code }}</FieldError>
                    </Field>
                </div>

                <div class="flex gap-2 pt-4">
                    <Button type="submit" :disabled="form.processing">{{ trans('pages.settings.events.actions.create') }}</Button>
                    <Button type="button" variant="secondary" @click.prevent="router.visit(usersIndexRoute.url())">{{ trans('pages.settings.events.actions.cancel') }}</Button>
                </div>
            </FieldSet>
        </form>
    </SidebarLayout>
</template>
