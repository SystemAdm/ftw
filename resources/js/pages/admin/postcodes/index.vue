<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { Button } from '@/components/ui/button';
import { router, usePage } from '@inertiajs/vue3';
import { create, show as showRoute, destroy as destroyRoute } from '@/routes/admin/postcodes';
import { restore as restoreRoute, forceDestroy as forceDestroyRoute } from '@/routes/admin/postcodes';
import { Table, TableBody, TableEmpty, TableHead, TableHeader, TableRow, TableCell, TableFooter } from '@/components/ui/table';
import Paginator from '@/components/custom/Paginator.vue';
import { trans } from 'laravel-vue-i18n';
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
      <h1 class="text-xl font-semibold">{{ trans('pages.settings.postcodes.title') }}</h1>
      <Button @click.prevent="router.visit(create.url())">{{ trans('pages.settings.postcodes.actions.create') }}</Button>
    </div>

    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>{{ trans('pages.settings.postcodes.fields.postal_code') }}</TableHead>
          <TableHead>{{ trans('pages.settings.postcodes.fields.city') }}</TableHead>
          <TableHead>{{ trans('pages.settings.postcodes.fields.state') }}</TableHead>
          <TableHead>{{ trans('pages.settings.postcodes.fields.country') }}</TableHead>
          <TableHead class="w-0 text-right"></TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableEmpty v-if="!postcodes || postcodes.data.length === 0" :colspan="5">
          {{ trans('pages.settings.postcodes.none') }}
        </TableEmpty>
        <template v-else>
          <TableRow v-for="pc in postcodes.data" :key="pc.postal_code">
            <TableCell>{{ pc.postal_code }}</TableCell>
            <TableCell>{{ pc.city }}</TableCell>
            <TableCell>{{ pc.state }}</TableCell>
            <TableCell>{{ pc.country }}</TableCell>
            <TableCell class="text-right flex gap-2">
              <Button size="sm" @click.prevent="router.visit(showRoute.url(pc.postal_code))">{{ trans('pages.settings.postcodes.actions.view') }}</Button>
              <AlertDialog v-if="!pc.deleted_at">
                <AlertDialogTrigger as-child>
                  <Button size="sm" variant="destructive">{{ trans('pages.settings.postcodes.actions.delete') }}</Button>
                </AlertDialogTrigger>
                <AlertDialogContent>
                  <AlertDialogHeader>
                    <AlertDialogTitle>{{ trans('pages.settings.postcodes.delete.title', { code: pc.postal_code }) }}</AlertDialogTitle>
                    <AlertDialogDescription>
                      {{ trans('pages.settings.postcodes.delete.description') }}
                    </AlertDialogDescription>
                  </AlertDialogHeader>
                  <AlertDialogFooter>
                    <AlertDialogCancel>{{ trans('pages.settings.postcodes.actions.cancel') }}</AlertDialogCancel>
                    <AlertDialogAction @click="del(pc)">{{ trans('pages.settings.postcodes.actions.delete') }}</AlertDialogAction>
                  </AlertDialogFooter>
                </AlertDialogContent>
              </AlertDialog>
              <div v-else class="inline-flex gap-2">
                <Button size="sm" variant="secondary" @click="restorePc(pc)">{{ trans('pages.settings.postcodes.actions.restore') }}</Button>
                <AlertDialog>
                  <AlertDialogTrigger as-child>
                    <Button size="sm" variant="destructive">{{ trans('pages.settings.postcodes.actions.force_delete') }}</Button>
                  </AlertDialogTrigger>
                  <AlertDialogContent>
                    <AlertDialogHeader>
                      <AlertDialogTitle>{{ trans('pages.settings.postcodes.force_delete.title', { code: pc.postal_code }) }}</AlertDialogTitle>
                      <AlertDialogDescription>
                        {{ trans('pages.settings.postcodes.force_delete.description') }}
                      </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                      <AlertDialogCancel>{{ trans('pages.settings.postcodes.actions.cancel') }}</AlertDialogCancel>
                      <AlertDialogAction @click="forceDel(pc)">{{ trans('pages.settings.postcodes.actions.delete_permanently') }}</AlertDialogAction>
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
