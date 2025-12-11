<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { Table, TableBody, TableCell, TableFooter, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import Paginator from '@/components/custom/Paginator.vue';

const inertiaPage = usePage<PageProps>();
</script>

<template>
    <SidebarLayout>
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Name</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="user in inertiaPage.props.users.data" :key="user.id">
                    <TableCell>{{ user.name }}</TableCell>
                </TableRow>
            </TableBody>
            <TableFooter>
                <TableRow>
                    <TableCell aria-colspan="1">
                        <Paginator :collection="inertiaPage.props.users"></Paginator>
                    </TableCell>
                </TableRow>
            </TableFooter>
        </Table>
    </SidebarLayout>
</template>
