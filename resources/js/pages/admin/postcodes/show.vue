<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { Button } from '@/components/ui/button';
import { Link, router, usePage } from '@inertiajs/vue3';
import { edit as editRoute, destroy as destroyRoute, index as indexRoute } from '@/routes/admin/postcodes';
import { restore as restoreRoute, forceDestroy as forceDestroyRoute } from '@/routes/admin/postcodes';
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
const postcode = (page.props as any).postcode as any;

function del() {
  router.delete(destroyRoute.url(postcode.postal_code), {
    onSuccess: () => router.visit(indexRoute.url()),
  });
}

function restorePc() {
  router.post(restoreRoute.url(postcode.postal_code), {}, { onSuccess: () => router.reload({ only: ['postcode'] }) });
}

function forceDel() {
  router.delete(forceDestroyRoute.url(postcode.postal_code), {
    onSuccess: () => router.visit(indexRoute.url()),
  });
}
</script>

<template>
  <SidebarLayout>
    <div class="mb-4 flex items-center justify-between">
      <h1 class="text-xl font-semibold">Postal Code {{ postcode.postal_code }}</h1>
      <div class="flex gap-2">
        <template v-if="!postcode.deleted_at">
          <Link class="inline-flex items-center rounded bg-black px-3 py-2 text-white dark:bg-white dark:text-black" :href="editRoute.url(postcode.postal_code)">Edit</Link>
          <AlertDialog>
            <AlertDialogTrigger as-child>
              <Button variant="secondary">Delete</Button>
            </AlertDialogTrigger>
            <AlertDialogContent>
              <AlertDialogHeader>
                <AlertDialogTitle>Delete postal code {{ postcode.postal_code }}?</AlertDialogTitle>
                <AlertDialogDescription>
                  This will move the postal code to the trash. You can restore it later.
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
          <Button variant="secondary" @click="restorePc">Restore</Button>
          <AlertDialog>
            <AlertDialogTrigger as-child>
              <Button variant="destructive">Force Delete</Button>
            </AlertDialogTrigger>
            <AlertDialogContent>
              <AlertDialogHeader>
                <AlertDialogTitle>Permanently delete {{ postcode.postal_code }}?</AlertDialogTitle>
                <AlertDialogDescription>
                  This action cannot be undone. This will permanently remove the postal code.
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

    <div class="max-w-xl rounded border">
      <dl class="divide-y">
        <div class="grid grid-cols-3 gap-4 p-3">
          <dt class="text-sm text-muted-foreground">City</dt>
          <dd class="col-span-2">{{ postcode.city }}</dd>
        </div>
        <div class="grid grid-cols-3 gap-4 p-3">
          <dt class="text-sm text-muted-foreground">State</dt>
          <dd class="col-span-2">{{ postcode.state }}</dd>
        </div>
        <div class="grid grid-cols-3 gap-4 p-3">
          <dt class="text-sm text-muted-foreground">Country</dt>
          <dd class="col-span-2">{{ postcode.country }}</dd>
        </div>
        <div class="grid grid-cols-3 gap-4 p-3">
          <dt class="text-sm text-muted-foreground">County</dt>
          <dd class="col-span-2">{{ postcode.county }}</dd>
        </div>
      </dl>
    </div>
  </SidebarLayout>
</template>
