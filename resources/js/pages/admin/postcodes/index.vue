<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { Button } from '@/components/ui/button';
import { Link, router, usePage } from '@inertiajs/vue3';
import { create, show as showRoute, destroy as destroyRoute } from '@/routes/admin/postcodes';
import { restore as restoreRoute, forceDestroy as forceDestroyRoute } from '@/routes/admin/postcodes';
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



const page = usePage();
const postcodes = (page.props as any).postcodes;

function del(pc: any) {
  router.delete(destroyRoute.url(pc.postal_code));
}

function restorePc(pc: any) {
  router.post(restoreRoute.url(pc.postal_code));
}

function forceDel(pc: any) {
  router.delete(forceDestroyRoute.url(pc.postal_code));
}
</script>

<template>
  <SidebarLayout>
    <div class="mb-4 flex items-center justify-between">
      <h1 class="text-xl font-semibold">Postal Codes</h1>
      <Button @click.prevent="router.visit(create.url())">New</Button>
    </div>

    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>Postal Code</TableHead>
          <TableHead>City</TableHead>
          <TableHead>State</TableHead>
          <TableHead>Country</TableHead>
          <TableHead class="w-0 text-right"></TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableEmpty v-if="!postcodes || postcodes.data.length === 0" :colspan="5">
          No postal codes yet.
        </TableEmpty>
        <template v-else>
          <TableRow v-for="pc in postcodes.data" :key="pc.postal_code">
            <TableCell>{{ pc.postal_code }}</TableCell>
            <TableCell>{{ pc.city }}</TableCell>
            <TableCell>{{ pc.state }}</TableCell>
            <TableCell>{{ pc.country }}</TableCell>
            <TableCell class="text-right flex gap-2">
              <Button size="sm" @click.prevent="router.visit(showRoute.url(pc.postal_code))">View</Button>
              <AlertDialog v-if="!pc.deleted_at">
                <AlertDialogTrigger as-child>
                  <Button size="sm" variant="destructive">Delete</Button>
                </AlertDialogTrigger>
                <AlertDialogContent>
                  <AlertDialogHeader>
                    <AlertDialogTitle>Delete postal code {{ pc.postal_code }}?</AlertDialogTitle>
                    <AlertDialogDescription>
                      This will move the postal code to the trash. You can restore it later.
                    </AlertDialogDescription>
                  </AlertDialogHeader>
                  <AlertDialogFooter>
                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                    <AlertDialogAction @click="del(pc)">Delete</AlertDialogAction>
                  </AlertDialogFooter>
                </AlertDialogContent>
              </AlertDialog>
              <div v-else class="inline-flex gap-2">
                <Button size="sm" variant="secondary" @click="restorePc(pc)">Restore</Button>
                <AlertDialog>
                  <AlertDialogTrigger as-child>
                    <Button size="sm" variant="destructive">Force Delete</Button>
                  </AlertDialogTrigger>
                  <AlertDialogContent>
                    <AlertDialogHeader>
                      <AlertDialogTitle>Permanently delete {{ pc.postal_code }}?</AlertDialogTitle>
                      <AlertDialogDescription>
                        This action cannot be undone. This will permanently remove the postal code.
                      </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                      <AlertDialogCancel>Cancel</AlertDialogCancel>
                      <AlertDialogAction @click="forceDel(pc)">Delete permanently</AlertDialogAction>
                    </AlertDialogFooter>
                  </AlertDialogContent>
                </AlertDialog>
              </div>
            </TableCell>
          </TableRow>
        </template>
      </TableBody>
        <TableFooter v-if="postcodes">
            <TableRow>
                <TableCell colspan="5">
                    <Paginator :collection="postcodes" />
                </TableCell>
            </TableRow>
        </TableFooter>
    </Table>

  </SidebarLayout>
</template>
