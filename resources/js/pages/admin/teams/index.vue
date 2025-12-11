<script setup lang="ts">
import { usePage, router } from '@inertiajs/vue3';
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { Table, TableBody, TableCell, TableFooter, TableHead, TableHeader, TableRow } from '@/components/ui/table';
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
  AlertDialogTrigger
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { index,show,create, restore as restoreRoute, forceDestroy as deleteRoute  } from '@/routes/admin/teams';

const inertiaPage = usePage<PageProps>();

function goCreate() {
    router.visit(create.url() );
}

function restoreTeam(id: number, e?: Event) {
    e?.stopPropagation();
    router.post(restoreRoute(id));
}

function forceDeleteTeam(id: number, e?: Event) {
    e?.stopPropagation();
    router.delete(deleteRoute(id), {
        onFinish: () => router.visit(index.url())
    });
}
</script>

<template>
    <SidebarLayout>
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-xl font-semibold">Teams</h1>
            <button class="rounded bg-black/90 px-3 py-2 text-white" @click="goCreate">New Team</button>
        </div>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Name</TableHead>
                    <TableHead>Slug</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead class="text-right">Actions</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="team in inertiaPage.props.teams.data" :key="team.id" class="cursor-pointer" @click="router.visit(show.url(team.id))">
                    <TableCell>{{ team.name }}</TableCell>
                    <TableCell>{{ team.slug }}</TableCell>
                    <TableCell>
                        <span :class="team.deleted_at ? 'text-red-600' : team.active ? 'text-green-600' : 'text-gray-500'">
                            {{ team.deleted_at ? 'Deleted' : team.active ? 'Active' : 'Inactive' }}
                        </span>
                    </TableCell>
                    <TableCell class="text-right">
                        <div v-if="team.deleted_at" class="flex justify-end gap-2">
                            <Button @click="(e) => restoreTeam(team.id, e)" aria-label="Restore team">Restore</Button>

                            <AlertDialog>
                                <AlertDialogTrigger as-child>
                                    <Button @click.stop aria-label="Permanently delete team">Force Delete</Button>
                                </AlertDialogTrigger>
                                <AlertDialogContent>
                                    <AlertDialogHeader>
                                        <AlertDialogTitle>Permanently delete this team?</AlertDialogTitle>
                                        <AlertDialogDescription>
                                            This action cannot be undone. This will permanently delete the team.
                                        </AlertDialogDescription>
                                    </AlertDialogHeader>
                                    <AlertDialogFooter>
                                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                                        <AlertDialogAction @click="(e) => forceDeleteTeam(team.id, e)">Force Delete</AlertDialogAction>
                                    </AlertDialogFooter>
                                </AlertDialogContent>
                            </AlertDialog>
                        </div>
                        <div v-else class="text-sm text-gray-400">—</div>
                    </TableCell>
                </TableRow>
            </TableBody>
            <TableFooter>
                <TableRow>
                    <TableCell colspan="4">
                        <Paginator :collection="inertiaPage.props.teams" />
                    </TableCell>
                </TableRow>
            </TableFooter>
        </Table>
    </SidebarLayout>
</template>
