<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { Table, TableBody, TableCell, TableFooter, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import Paginator from '@/components/custom/Paginator.vue';
import { usePage, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { show, create as createRoute } from '@/routes/admin/weekdays/index';

const page = usePage<PageProps>();

function goCreate() {
    router.visit(createRoute.url());
}

function weekdayLabel(n: number): string {
    const names = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    return names[n] ?? String(n);
}
</script>

<template>
    <SidebarLayout>
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-xl font-semibold">Weekdays</h1>
            <Button @click="goCreate">New Weekday</Button>
        </div>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Day</TableHead>
                    <TableHead>Team</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Location</TableHead>
                    <TableHead>Time</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="w in (page.props as any).weekdays.data" :key="w.id" class="cursor-pointer" @click="router.visit(show.url(w.id))">
                    <TableCell>{{ weekdayLabel(w.weekday) }}</TableCell>
                    <TableCell>{{ w.team?.name ?? '—' }}</TableCell>
                    <TableCell>
                        <span :class="w.is_ended ? 'text-red-600' : (w.active ? 'text-green-600' : 'text-gray-500')">
                            {{ w.status_label }}
                        </span>
                    </TableCell>
                    <TableCell>{{ w.location?.name ?? '—' }}</TableCell>
                    <TableCell>{{ w.start_time }} - {{ w.end_time }}</TableCell>
                </TableRow>
            </TableBody>
            <TableFooter>
                <TableRow>
                    <TableCell colspan="5">
                        <Paginator :collection="(page.props as any).weekdays" />
                    </TableCell>
                </TableRow>
            </TableFooter>
        </Table>
    </SidebarLayout>
</template>
