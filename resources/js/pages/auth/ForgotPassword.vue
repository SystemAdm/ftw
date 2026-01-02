<script setup lang="ts">
import { Head, useForm, Link, usePage } from '@inertiajs/vue3';
import { Card, CardContent } from '@/components/ui/card';
import { Field, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { trans } from 'laravel-vue-i18n';
import { email as store } from '@/routes/password/index';
import { login as loginForm } from '@/routes/index';
import { Toaster } from '@/components/ui/sonner';
import { useFlashToasts } from '@/composables/useFlashToasts';

defineProps<{
    status?: string;
}>();

const page = usePage();
useFlashToasts(page);

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(store.url());
};
</script>

<template>
    <Toaster />
    <Head :title="trans('pages.auth.forgot_password.title')" />

    <div class="flex min-h-svh flex-col items-center justify-center bg-black p-6 md:p-10">
        <div class="w-full max-w-sm">
            <Card>
                <CardContent class="p-6 md:p-8">
                    <div class="mb-4 flex flex-col items-center gap-2 text-center">
                        <h1 class="text-2xl font-bold">{{ trans('pages.auth.forgot_password.title') }}</h1>
                        <p class="text-sm text-muted-foreground">
                            {{ trans('pages.auth.forgot_password.description') }}
                        </p>
                    </div>

                    <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
                        {{ status }}
                    </div>

                    <form @submit.prevent="submit" class="space-y-4">
                        <Field>
                            <FieldLabel for="email">{{ trans('pages.auth.forgot_password.email_label') }}</FieldLabel>
                            <Input
                                id="email"
                                type="email"
                                v-model="form.email"
                                required
                                autofocus
                                autocomplete="username"
                            />
                            <div v-if="form.errors.email" class="text-sm text-red-600">{{ form.errors.email }}</div>
                        </Field>

                        <Button type="submit" class="w-full" :disabled="form.processing">
                            {{ trans('pages.auth.forgot_password.send_button') }}
                        </Button>
                    </form>

                    <div class="mt-4 text-center">
                        <Link :href="loginForm.url()" class="text-sm text-muted-foreground hover:underline">
                            {{ trans('pages.auth.login.back_button') }}
                        </Link>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
