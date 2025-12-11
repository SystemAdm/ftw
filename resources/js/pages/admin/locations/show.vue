<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { Button } from '@/components/ui/button';
import { router, usePage } from '@inertiajs/vue3';
import { edit as editRoute, destroy as destroyRoute, index as indexRoute } from '@/routes/admin/locations';
import { restore as restoreRoute, forceDestroy as forceDestroyRoute } from '@/routes/admin/locations';
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
const location = (page.props as any).location as any;

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
  <SidebarLayout>
    <div class="mb-4 flex items-center justify-between">
      <h1 class="text-xl font-semibold">Location {{ location.name }}</h1>
      <div class="flex gap-2">
        <template v-if="!location.deleted_at">
          <Button @click.prevent="router.visit(editRoute.url(location.id))">Edit</Button>
          <AlertDialog>
            <AlertDialogTrigger as-child>
              <Button variant="destructive">Delete</Button>
            </AlertDialogTrigger>
            <AlertDialogContent>
              <AlertDialogHeader>
                <AlertDialogTitle>Delete {{ location.name }}?</AlertDialogTitle>
                <AlertDialogDescription>
                  This will move the location to the trash. You can restore it later.
                </AlertDialogDescription>
              </AlertDialogHeader>
              <AlertDialogFooter>
                <AlertDialogCancel>Cancel</AlertDialogCancel>
                <AlertDialogAction @click="del">Delete</AlertDialogAction>
              </AlertDialogFooter>
            </AlertDialogContent>
          </AlertDialog>
        </template>
        <template v-else>
          <Button variant="secondary" @click="restoreLoc">Restore</Button>
          <AlertDialog>
            <AlertDialogTrigger as-child>
              <Button variant="destructive">Force Delete</Button>
            </AlertDialogTrigger>
            <AlertDialogContent>
              <AlertDialogHeader>
                <AlertDialogTitle>Permanently delete {{ location.name }}?</AlertDialogTitle>
                <AlertDialogDescription>
                  This action cannot be undone. This will permanently remove the location.
                </AlertDialogDescription>
              </AlertDialogHeader>
              <AlertDialogFooter>
                <AlertDialogCancel>Cancel</AlertDialogCancel>
                <AlertDialogAction @click="forceDel">Delete permanently</AlertDialogAction>
              </AlertDialogFooter>
            </AlertDialogContent>
          </AlertDialog>
        </template>
      </div>
    </div>

    <div class="max-w-2xl rounded border">
      <dl class="divide-y">
        <div class="grid grid-cols-3 gap-4 p-3">
          <dt class="text-sm text-muted-foreground">Postal Code</dt>
          <dd class="col-span-2">{{ location.postal }}</dd>
        </div>
        <div class="grid grid-cols-3 gap-4 p-3">
          <dt class="text-sm text-muted-foreground">Active</dt>
          <dd class="col-span-2">{{ location.active ? 'Yes' : 'No' }}</dd>
        </div>
        <div class="grid grid-cols-3 gap-4 p-3">
          <dt class="text-sm text-muted-foreground">Description</dt>
          <dd class="col-span-2">{{ location.description }}</dd>
        </div>
        <div class="grid grid-cols-3 gap-4 p-3">
          <dt class="text-sm text-muted-foreground">Latitude</dt>
          <dd class="col-span-2">{{ location.latitude }}</dd>
        </div>
        <div class="grid grid-cols-3 gap-4 p-3">
          <dt class="text-sm text-muted-foreground">Longitude</dt>
          <dd class="col-span-2">{{ location.longitude }}</dd>
        </div>
        <div class="grid grid-cols-3 gap-4 p-3">
          <dt class="text-sm text-muted-foreground">Google Maps URL</dt>
          <dd class="col-span-2"><a v-if="location.google_maps_url" :href="location.google_maps_url" target="_blank" class="underline">{{ location.google_maps_url }}</a></dd>
        </div>
        <div class="grid grid-cols-3 gap-4 p-3">
          <dt class="text-sm text-muted-foreground">Website</dt>
          <dd class="col-span-2"><a v-if="location.link" :href="location.link" target="_blank" class="underline">{{ location.link }}</a></dd>
        </div>
        <div class="grid grid-cols-3 gap-4 p-3">
          <dt class="text-sm text-muted-foreground">Street</dt>
          <dd class="col-span-2">{{ location.street_address }} {{ location.street_number }}</dd>
        </div>
      </dl>
    </div>
  </SidebarLayout>
</template>
