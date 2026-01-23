<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { BreadcrumbItemType } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { trans } from 'laravel-vue-i18n';
import { computed, ref, onMounted, nextTick } from 'vue';

import { dashboard as adminDashboardRoute } from '@/routes/admin';

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
        title: trans('pages.admin.open.title'),
        href: '/admin/open',
    },
]);

const form = useForm({
    code: '',
});

const codeInput = ref<InstanceType<typeof Input> | null>(null);

onMounted(() => {
    codeInput.value?.focus();
});

const page = usePage();
const lastResult = ref<{ type: 'success' | 'error', message: string } | null>(null);
let timeout: any = null;

function handleSubmit() {
    if (timeout) clearTimeout(timeout);
    form.post('/admin/open', {
        preserveScroll: true,
        onSuccess: () => {
            const status = page.props.status;
            if (status) {
                lastResult.value = { type: 'success', message: status as string };
                timeout = setTimeout(() => {
                    lastResult.value = null;
                }, 5000);
            }
            form.reset('code');
            nextTick(() => {
                codeInput.value?.focus();
            });
        },
        onError: (errors) => {
            if (errors.code) {
                lastResult.value = { type: 'error', message: errors.code };
                timeout = setTimeout(() => {
                    lastResult.value = null;
                }, 5000);
            }
            form.reset('code');
            nextTick(() => {
                codeInput.value?.focus();
            });
        }
    });
}
</script>

<template>
    <SidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="trans('pages.ui.navigation.admin_menu')" />

        <div class="mx-auto max-w-md space-y-6 pt-12">
            <Card v-if="lastResult" :class="[
                'border-2',
                lastResult.type === 'success' ? 'border-green-500 bg-green-50 dark:bg-green-950/20' : 'border-red-500 bg-red-50 dark:bg-red-950/20'
            ]">
                <CardContent class="p-6 text-center">
                    <div :class="[
                        'text-2xl font-bold',
                        lastResult.type === 'success' ? 'text-green-700 dark:text-green-400' : 'text-red-700 dark:text-red-400'
                    ]">
                        {{ lastResult.message }}
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>{{ trans('pages.admin.open.title') }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="handleSubmit" class="space-y-4">
                        <div class="space-y-2">
                            <Label for="code">{{ trans('pages.admin.open.code_label') }}</Label>
                            <Input
                                id="code"
                                v-model="form.code"
                                ref="codeInput"
                                type="text"
                                :placeholder="trans('pages.admin.open.code_placeholder')"
                                :disabled="form.processing"
                                autocomplete="off"
                            />
                        </div>
                        <Button type="submit" class="w-full" :disabled="form.processing">
                            {{ form.processing ? trans('pages.admin.open.processing') : trans('pages.admin.open.submit') }}
                        </Button>
                    </form>
                </CardContent>
            </Card>
        </div>
    </SidebarLayout>
</template>
