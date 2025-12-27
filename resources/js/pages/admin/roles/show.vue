<script setup lang="ts">
import { usePage, router } from '@inertiajs/vue3';
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { ref } from 'vue';
import { trans } from 'laravel-vue-i18n';

const page = usePage<PageProps>();
const role = (page.props as any).role;
const searchTerm = ref('');
const results = ref<Array<{ id: number; name: string; email: string }>>([]);
const searching = ref(false);

function goBack() {
    router.visit('/admin/roles');
}

function editRole() {
    router.visit(`/admin/roles/${role.id}/edit`);
}

function deleteRole() {
    router.delete(`/admin/roles/${role.id}`, {
        onFinish: () => router.visit('/admin/roles'),
    });
}

async function searchUsers() {
    searching.value = true;
    results.value = [];
    try {
        const res = await fetch(`/admin/roles/${role.id}/users/search?q=${encodeURIComponent(searchTerm.value)}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });
        const json = await res.json();
        results.value = json.data ?? [];
    } finally {
        searching.value = false;
    }
}

function assignUser(id: number) {
    router.post(
        `/admin/roles/${role.id}/users`,
        { user_id: id },
        {
            onFinish: () => router.visit(`/admin/roles/${role.id}`),
        },
    );
}

function removeUser(id: number) {
    router.delete(`/admin/roles/${role.id}/users/${id}`, {
        onFinish: () => router.visit(`/admin/roles/${role.id}`),
    });
}
</script>

<template>
    <SidebarLayout>
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
                            <div class="text-sm">{{ u.name }} <span class="text-muted-foreground">{{ u.email }}</span></div>
                            <Button size="sm" variant="outline" @click="removeUser(u.id)">{{ trans('pages.settings.roles.actions.remove') }}</Button>
                        </div>
                    </div>
                    <div v-else class="text-sm text-muted-foreground">{{ trans('pages.settings.locations.none') }}</div>
                </div>
            </section>

            <aside class="space-y-4">
                <div class="rounded-md border bg-card p-4">
                    <h2 class="mb-2 text-sm font-medium text-muted-foreground">{{ trans('pages.settings.roles.actions.assign') }} {{ trans('pages.ui.navigation.users') }}</h2>
                    <div class="flex gap-2">
                        <Input v-model="searchTerm" :placeholder="trans('pages.settings.roles.fields.users')" @keyup.enter="searchUsers" />
                        <Button :disabled="searching" @click="searchUsers">{{ trans('pages.settings.locations.actions.view') }}</Button>
                    </div>
                    <div class="mt-3 max-h-64 space-y-2 overflow-y-auto">
                        <div v-for="r in results" :key="r.id" class="flex items-center justify-between">
                            <div class="text-sm">{{ r.name }} <span class="text-muted-foreground">{{ r.email }}</span></div>
                            <Button size="sm" @click="assignUser(r.id)">{{ trans('pages.settings.roles.actions.assign') }}</Button>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </SidebarLayout>
</template>
