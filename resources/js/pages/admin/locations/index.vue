<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { Button } from '@/components/ui/button';
import { router, usePage } from '@inertiajs/vue3';
import { create as createRoute, show as showRoute, destroy as destroyRoute } from '@/routes/admin/locations';
import { restore as restoreRoute, forceDestroy as forceDestroyRoute } from '@/routes/admin/locations';
import { Table, TableBody, TableEmpty, TableHead, TableHeader, TableRow, TableCell, TableFooter } from '@/components/ui/table';
import Paginator from '@/components/custom/Paginator.vue';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';

const page = usePage<PageProps>();

function del(loc: any) {
    router.delete(destroyRoute.url(loc.id));
}

function restoreLoc(loc: any) {
    router.post(restoreRoute.url(loc.id));
}

function forceDel(loc: any) {
    router.delete(forceDestroyRoute.url(loc.id));
}
</script>

<template>
    <SidebarLayout>
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-xl font-semibold">Locations</h1>
            <Button @click.prevent="router.visit(createRoute.url())">New</Button>
        </div>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Name</TableHead>
                    <TableHead>Postal Code</TableHead>
                    <TableHead>Active</TableHead>
                    <TableHead class="w-0 text-right"></TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableEmpty v-if="!page.props.locations || page.props.locations.data.length === 0" :colspan="4"> No locations yet. </TableEmpty>
                <template v-else>
                    <TableRow v-for="loc in page.props.locations.data" :key="loc.id">
                        <TableCell>{{ loc.name }}</TableCell>
                        <TableCell>{{ loc.postal }}</TableCell>
                        <TableCell>
                            <span :class="loc.active ? 'text-green-600' : 'text-gray-500'">{{ loc.active ? 'Yes' : 'No' }}</span>
                        </TableCell>
                        <TableCell class="flex gap-2 text-right">
                            <Button v-if="!loc.deleted_at" size="sm" @click.prevent="router.visit(showRoute.url(loc.id))">View</Button>
                            <AlertDialog v-if="!loc.deleted_at">
                                <AlertDialogTrigger as-child>
                                    <Button size="sm" variant="destructive">Delete</Button>
                                </AlertDialogTrigger>
                                <AlertDialogContent>
                                    <AlertDialogHeader>
                                        <AlertDialogTitle>Delete {{ loc.name }}?</AlertDialogTitle>
                                        <AlertDialogDescription>
                                            This will move the location to the trash. You can restore it later.
                                        </AlertDialogDescription>
                                    </AlertDialogHeader>
                                    <AlertDialogFooter>
                                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                                        <AlertDialogAction @click="del(loc)">Delete</AlertDialogAction>
                                    </AlertDialogFooter>
                                </AlertDialogContent>
                            </AlertDialog>
                            <div v-else class="inline-flex gap-2">
                                <Button size="sm" variant="secondary" @click="restoreLoc(loc)">Restore</Button>
                                <AlertDialog>
                                    <AlertDialogTrigger as-child>
                                        <Button size="sm" variant="destructive">Force Delete</Button>
                                    </AlertDialogTrigger>
                                    <AlertDialogContent>
                                        <AlertDialogHeader>
                                            <AlertDialogTitle>Permanently delete {{ loc.name }}?</AlertDialogTitle>
                                            <AlertDialogDescription>
                                                This action cannot be undone. This will permanently remove the location.
                                            </AlertDialogDescription>
                                        </AlertDialogHeader>
                                        <AlertDialogFooter>
                                            <AlertDialogCancel>Cancel</AlertDialogCancel>
                                            <AlertDialogAction @click="forceDel(loc)">Delete permanently</AlertDialogAction>
                                        </AlertDialogFooter>
                                    </AlertDialogContent>
                                </AlertDialog>
                            </div>
                        </TableCell>
                    </TableRow>
                </template>
            </TableBody>
            <TableFooter>
                <TableRow>
                    <TableCell colspan="4">
                        <Paginator :collection="(page.props as any).locations" />
                    </TableCell>
                </TableRow>
            </TableFooter>
        </Table>
    </SidebarLayout>
</template>
