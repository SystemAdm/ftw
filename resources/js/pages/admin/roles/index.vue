<script setup lang="ts">
import { usePage, router } from '@inertiajs/vue3';
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { Table, TableBody, TableCell, TableFooter, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import Paginator from '@/components/custom/Paginator.vue';
import { Button } from '@/components/ui/button';
import { trans } from 'laravel-vue-i18n';

const page = usePage<PageProps>();

function goCreate() {
    router.visit('/admin/roles/create');
}

function goShow(id: number) {
    router.visit(`/admin/roles/${id}`);
}
</script>

<template>
    <SidebarLayout>
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-xl font-semibold">{{ trans('pages.settings.roles.title') }}</h1>
            <Button @click="goCreate">{{ trans('pages.settings.roles.new') }}</Button>
        </div>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>{{ trans('pages.settings.roles.fields.name') }}</TableHead>
                    <TableHead>{{ trans('pages.settings.roles.fields.guard') }}</TableHead>
                    <TableHead class="text-center">{{ trans('pages.settings.roles.fields.users') }}</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="role in (page.props as any).roles.data" :key="role.id" class="cursor-pointer" @click="goShow(role.id)">
                    <TableCell class="font-medium">{{ role.name }}</TableCell>
                    <TableCell>{{ role.guard_name }}</TableCell>
                    <TableCell class="text-center">{{ role.users_count }}</TableCell>
                </TableRow>
            </TableBody>
            <TableFooter>
                <TableRow>
                    <TableCell colspan="3">
                        <Paginator :collection="(page.props as any).roles" />
                    </TableCell>
                </TableRow>
            </TableFooter>
        </Table>
    </SidebarLayout>
</template>
