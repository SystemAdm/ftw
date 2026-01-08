<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { destroy as destroyRoute, edit as editRoute, index as indexRoute, show as showRoute } from '@/routes/admin/roles';
import { assign as assignUserAction, remove as removeUserAction, search as searchUsersAction } from '@/routes/admin/roles/users';
import { BreadcrumbItemType } from '@/types';
import { router, usePage } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { computed, ref } from 'vue';

import { dashboard as adminDashboardRoute } from '@/routes/admin';

const page = usePage<PageProps>();
const role = (page.props as any).role;

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
        title: trans('pages.settings.roles.title'),
        href: indexRoute.url(),
    },
    {
        title: role.name,
        href: page.url,
    },
]);
const searchTerm = ref('');
const results = ref<Array<{ id: number; name: string; email: string }>>([]);
const searching = ref(false);

function goBack() {
    router.visit(indexRoute.url());
}

function editRole() {
    router.visit(editRoute.url(role.id));
}

function deleteRole() {
    router.delete(destroyRoute.url(role.id), {
        onFinish: () => router.visit(indexRoute.url()),
    });
}

async function searchUsers() {
    searching.value = true;
    results.value = [];
    try {
        const response = await fetch(searchUsersAction.url(role.id, { query: { q: searchTerm.value } }), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });
        const json = await response.json();
        results.value = json.data ?? [];
    } finally {
        searching.value = false;
    }
}

function assignUser(id: number) {
    router.post(
        assignUserAction.url(role.id),
        { user_id: id },
        {
            onFinish: () => router.visit(showRoute.url(role.id)),
        },
    );
}

function removeUser(id: number) {
    router.delete(removeUserAction.url(role.id, id), {
        onFinish: () => router.visit(showRoute.url(role.id)),
    });
}
</script>

<template>
    <SidebarLayout :breadcrumbs="breadcrumbs">
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-xl font-semibold">{{ trans('pages.settings.roles.fields.role') }}: {{ role.name }}</h1>
            <div class="flex gap-2">
                <Button variant="outline" @click="goBack">{{ trans('pages.settings.locations.actions.cancel') }}</Button>
                <Button variant="secondary" @click="editRole">{{ trans('pages.settings.locations.actions.edit') }}</Button>
                <Button variant="destructive" @click="deleteRole">{{ trans('pages.settings.locations.actions.delete') }}</Button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <section class="space-y-6 lg:col-span-2">
                <div class="rounded-md border bg-card p-4">
                    <h2 class="mb-2 text-sm font-medium text-muted-foreground">{{ trans('pages.settings.roles.fields.permissions') }}</h2>
                    <div v-if="(role.permissions ?? []).length" class="flex flex-wrap gap-2">
                        <Badge v-for="p in role.permissions" :key="p.id" variant="outline">{{ p.name }}</Badge>
                    </div>
                    <div v-else class="text-sm text-muted-foreground">{{ trans('pages.settings.locations.none') }}</div>
                </div>

                <div class="rounded-md border bg-card p-4">
                    <h2 class="mb-2 text-sm font-medium text-muted-foreground">{{ trans('pages.settings.roles.fields.users') }}</h2>
                    <div v-if="(role.users ?? []).length" class="flex flex-col gap-2">
                        <div v-for="u in role.users" :key="u.id" class="flex items-center justify-between">
                            <div class="text-sm">
                                {{ u.name }} <span class="text-muted-foreground">{{ u.email }}</span>
                            </div>
                            <Button size="sm" variant="outline" @click="removeUser(u.id)">{{ trans('pages.settings.roles.actions.remove') }}</Button>
                        </div>
                    </div>
                    <div v-else class="text-sm text-muted-foreground">{{ trans('pages.settings.locations.none') }}</div>
                </div>
            </section>

            <aside class="space-y-4">
                <div class="rounded-md border bg-card p-4">
                    <h2 class="mb-2 text-sm font-medium text-muted-foreground">
                        {{ trans('pages.settings.roles.actions.assign') }} {{ trans('pages.ui.navigation.users') }}
                    </h2>
                    <div class="flex gap-2">
                        <Input v-model="searchTerm" :placeholder="trans('pages.settings.roles.fields.users')" @keyup.enter="searchUsers" />
                        <Button :disabled="searching" @click="searchUsers">{{ trans('pages.settings.locations.actions.view') }}</Button>
                    </div>
                    <div class="mt-3 max-h-64 space-y-2 overflow-y-auto">
                        <div v-for="r in results" :key="r.id" class="flex items-center justify-between">
                            <div class="text-sm">
                                {{ r.name }} <span class="text-muted-foreground">{{ r.email }}</span>
                            </div>
                            <Button size="sm" @click="assignUser(r.id)">{{ trans('pages.settings.roles.actions.assign') }}</Button>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </SidebarLayout>
</template>
