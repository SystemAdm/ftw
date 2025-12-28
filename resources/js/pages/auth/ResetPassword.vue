<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { Card, CardContent } from '@/components/ui/card';
import { Field, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { trans } from 'laravel-vue-i18n';
import { update } from '@/actions/App/Http/Controllers/Auth/PasswordResetController';
import { Toaster } from '@/components/ui/sonner';
import { useFlashToasts } from '@/composables/useFlashToasts';

const props = defineProps<{
    email: string;
    token: string;
}>();

const page = usePage();
useFlashToasts(page);

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(update.url(), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <Toaster />
    <Head :title="trans('pages.auth.reset_password.title')" />

    <div class="flex min-h-svh flex-col items-center justify-center bg-black p-6 md:p-10">
        <div class="w-full max-w-sm">
            <Card>
                <CardContent class="p-6 md:p-8">
                    <div class="mb-4 flex flex-col items-center gap-2 text-center">
                        <h1 class="text-2xl font-bold">{{ trans('pages.auth.reset_password.title') }}</h1>
                    </div>

                    <form @submit.prevent="submit" class="space-y-4">
                        <Field>
                            <FieldLabel for="email">{{ trans('pages.auth.reset_password.email_label') }}</FieldLabel>
                            <Input
                                id="email"
                                type="email"
                                v-model="form.email"
                                required
                                autocomplete="username"
                            />
                            <div v-if="form.errors.email" class="text-sm text-red-600">{{ form.errors.email }}</div>
                        </Field>

                        <Field>
                            <FieldLabel for="password">{{ trans('pages.auth.reset_password.password_label') }}</FieldLabel>
                            <Input
                                id="password"
                                type="password"
                                v-model="form.password"
                                required
                                autofocus
                                autocomplete="new-password"
                            />
                            <div v-if="form.errors.password" class="text-sm text-red-600">{{ form.errors.password }}</div>
                        </Field>

                        <Field>
                            <FieldLabel for="password_confirmation">{{ trans('pages.auth.reset_password.confirm_password_label') }}</FieldLabel>
                            <Input
                                id="password_confirmation"
                                type="password"
                                v-model="form.password_confirmation"
                                required
                                autocomplete="new-password"
                            />
                            <div v-if="form.errors.password_confirmation" class="text-sm text-red-600">{{ form.errors.password_confirmation }}</div>
                        </Field>

                        <Button type="submit" class="w-full" :disabled="form.processing">
                            {{ trans('pages.auth.reset_password.reset_button') }}
                        </Button>
                    </form>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
