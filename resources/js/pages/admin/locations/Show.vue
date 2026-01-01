<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { BreadcrumbItemType } from '@/types';
import { Button } from '@/components/ui/button';
import { router, usePage } from '@inertiajs/vue3';
import { edit as editRoute, destroy as destroyRoute, index as indexRoute } from '@/routes/admin/locations';
import { restore as restoreRoute, forceDestroy as forceDestroyRoute } from '@/routes/admin/locations';
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

import { computed } from 'vue';

const page = usePage();
const location = (page.props as any).location as any;

const breadcrumbs = computed<BreadcrumbItemType[]>(() => [
    {
        title: trans('pages.settings.locations.title'),
        href: indexRoute.url(),
    },
    {
        title: location.name,
        href: page.url,
    },
]);

function del() {
  router.delete(destroyRoute.url(location.id), {
    onSuccess: () => router.visit(indexRoute.url()),
  });
}

function restoreLoc() {
  router.post(restoreRoute.url(location.id), {}, {
      onSuccess: () => router.reload({ only: ['location'] })
  });
}

function forceDel() {
  router.delete(forceDestroyRoute.url(location.id), {
    onSuccess: () => router.visit(indexRoute.url()),
  });
}
</script>

<template>
  <SidebarLayout :breadcrumbs="breadcrumbs">
    <div class="mb-4 flex items-center justify-between">
      <h1 class="text-xl font-semibold">{{ trans('pages.settings.locations.fields.name') }} {{ location.name }}</h1>
      <div class="flex gap-2">
        <template v-if="!location.deleted_at">
          <Button @click.prevent="router.visit(editRoute.url(location.id))">{{ trans('pages.settings.locations.actions.edit') }}</Button>
          <AlertDialog>
            <AlertDialogTrigger as-child>
              <Button variant="destructive">{{ trans('pages.settings.locations.actions.delete') }}</Button>
            </AlertDialogTrigger>
            <AlertDialogContent>
              <AlertDialogHeader>
                <AlertDialogTitle>{{ trans('pages.settings.locations.delete.title', { name: location.name }) }}</AlertDialogTitle>
                <AlertDialogDescription>
                  {{ trans('pages.settings.locations.delete.description') }}
                </AlertDialogDescription>
              </AlertDialogHeader>
              <AlertDialogFooter>
                <AlertDialogCancel>{{ trans('pages.settings.locations.actions.cancel') }}</AlertDialogCancel>
                <AlertDialogAction @click="del">{{ trans('pages.settings.locations.actions.delete') }}</AlertDialogAction>
              </AlertDialogFooter>
            </AlertDialogContent>
          </AlertDialog>
        </template>
        <template v-else>
          <Button variant="secondary" @click="restoreLoc">{{ trans('pages.settings.locations.actions.restore') }}</Button>
          <AlertDialog>
            <AlertDialogTrigger as-child>
              <Button variant="destructive">{{ trans('pages.settings.locations.actions.force_delete') }}</Button>
            </AlertDialogTrigger>
            <AlertDialogContent>
              <AlertDialogHeader>
                <AlertDialogTitle>{{ trans('pages.settings.locations.force_delete.title', { name: location.name }) }}</AlertDialogTitle>
                <AlertDialogDescription>
                  {{ trans('pages.settings.locations.force_delete.description') }}
                </AlertDialogDescription>
              </AlertDialogHeader>
              <AlertDialogFooter>
                <AlertDialogCancel>{{ trans('pages.settings.locations.actions.cancel') }}</AlertDialogCancel>
                <AlertDialogAction @click="forceDel">{{ trans('pages.settings.locations.actions.delete_permanently') }}</AlertDialogAction>
              </AlertDialogFooter>
            </AlertDialogContent>
          </AlertDialog>
        </template>
      </div>
    </div>

    <div class="max-w-2xl rounded border">
      <dl class="divide-y">
        <div class="grid grid-cols-3 gap-4 p-3">
          <dt class="text-sm text-muted-foreground">{{ trans('pages.settings.locations.fields.postal_code') }}</dt>
          <dd class="col-span-2">{{ location.postal }}</dd>
        </div>
        <div class="grid grid-cols-3 gap-4 p-3">
          <dt class="text-sm text-muted-foreground">{{ trans('pages.settings.locations.fields.active') }}</dt>
          <dd class="col-span-2">{{ location.active ? trans('pages.settings.locations.fields.yes') : trans('pages.settings.locations.fields.no') }}</dd>
        </div>
        <div class="grid grid-cols-3 gap-4 p-3">
          <dt class="text-sm text-muted-foreground">{{ trans('pages.settings.locations.fields.description') }}</dt>
          <dd class="col-span-2">{{ location.description }}</dd>
        </div>
        <div class="grid grid-cols-3 gap-4 p-3">
          <dt class="text-sm text-muted-foreground">{{ trans('pages.settings.locations.fields.latitude') }}</dt>
          <dd class="col-span-2">{{ location.latitude }}</dd>
        </div>
        <div class="grid grid-cols-3 gap-4 p-3">
          <dt class="text-sm text-muted-foreground">{{ trans('pages.settings.locations.fields.longitude') }}</dt>
          <dd class="col-span-2">{{ location.longitude }}</dd>
        </div>
        <div class="grid grid-cols-3 gap-4 p-3">
          <dt class="text-sm text-muted-foreground">{{ trans('pages.settings.locations.fields.google_maps_url') }}</dt>
          <dd class="col-span-2"><a v-if="location.google_maps_url" :href="location.google_maps_url" target="_blank" class="underline">{{ location.google_maps_url }}</a></dd>
        </div>
        <div class="grid grid-cols-3 gap-4 p-3">
          <dt class="text-sm text-muted-foreground">{{ trans('pages.settings.locations.fields.website') }}</dt>
          <dd class="col-span-2"><a v-if="location.link" :href="location.link" target="_blank" class="underline">{{ location.link }}</a></dd>
        </div>
        <div class="grid grid-cols-3 gap-4 p-3">
          <dt class="text-sm text-muted-foreground">{{ trans('pages.settings.locations.fields.street_address') }}</dt>
          <dd class="col-span-2">{{ location.street_address }} {{ location.street_number }}</dd>
        </div>
      </dl>
    </div>
  </SidebarLayout>
</template>
