<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { Button } from '@/components/ui/button';
import { router, usePage } from '@inertiajs/vue3';
import { edit as editRoute, destroy as destroyRoute, index as indexRoute } from '@/routes/admin/postcodes';
import { restore as restoreRoute, forceDestroy as forceDestroyRoute } from '@/routes/admin/postcodes';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
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
    <Card class="mx-auto w-full max-w-4xl">
      <CardHeader class="flex items-start justify-between gap-4 sm:flex-row">
        <div class="space-y-1">
          <CardTitle class="text-2xl">{{ trans('pages.settings.postcodes.fields.postal_code') }}: {{ postcode.postal_code }}</CardTitle>
          <CardDescription class="flex items-center gap-2">
            <span class="text-xs text-muted-foreground">{{ trans('pages.settings.postcodes.fields.city') }}:</span>
            <span class="font-medium text-foreground">{{ postcode.city }}</span>
            <Separator orientation="vertical" class="mx-1 hidden h-4 sm:inline-block" />
            <Badge v-if="postcode.deleted_at" variant="destructive">{{ trans('pages.settings.teams.status.deleted') }}</Badge>
          </CardDescription>
        </div>

        <div class="flex flex-wrap gap-2">
          <Button variant="outline" @click="router.visit(indexRoute.url())">{{ trans('pages.settings.postcodes.actions.cancel') }}</Button>

          <template v-if="!postcode.deleted_at">
            <Button variant="secondary" @click="router.visit(editRoute.url(postcode.postal_code))">
              {{ trans('pages.settings.postcodes.actions.edit') }}
            </Button>

            <AlertDialog>
              <AlertDialogTrigger as-child>
                <Button variant="destructive">{{ trans('pages.settings.postcodes.actions.delete') }}</Button>
              </AlertDialogTrigger>
              <AlertDialogContent>
                <AlertDialogHeader>
                  <AlertDialogTitle>{{ trans('pages.settings.postcodes.delete.title', { code: postcode.postal_code }) }}</AlertDialogTitle>
                  <AlertDialogDescription>
                    {{ trans('pages.settings.postcodes.delete.description') }}
                  </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                  <AlertDialogCancel>{{ trans('pages.settings.postcodes.actions.cancel') }}</AlertDialogCancel>
                  <AlertDialogAction @click="del">{{ trans('pages.settings.postcodes.actions.delete') }}</AlertDialogAction>
                </AlertDialogFooter>
              </AlertDialogContent>
            </AlertDialog>
          </template>

          <template v-else>
            <Button @click="restorePc">{{ trans('pages.settings.postcodes.actions.restore') }}</Button>

            <AlertDialog>
              <AlertDialogTrigger as-child>
                <Button variant="destructive">{{ trans('pages.settings.postcodes.actions.force_delete') }}</Button>
              </AlertDialogTrigger>
              <AlertDialogContent>
                <AlertDialogHeader>
                  <AlertDialogTitle>{{ trans('pages.settings.postcodes.force_delete.title', { code: postcode.postal_code }) }}</AlertDialogTitle>
                  <AlertDialogDescription>
                    {{ trans('pages.settings.postcodes.force_delete.description') }}
                  </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                  <AlertDialogCancel>{{ trans('pages.settings.postcodes.actions.cancel') }}</AlertDialogCancel>
                  <AlertDialogAction @click="forceDel">{{ trans('pages.settings.postcodes.actions.delete_permanently') }}</AlertDialogAction>
                </AlertDialogFooter>
              </AlertDialogContent>
            </AlertDialog>
          </template>
        </div>
      </CardHeader>

      <CardContent>
        <div class="rounded-md border bg-card p-4">
          <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
            <div>
              <dt class="text-xs uppercase tracking-wide text-muted-foreground">{{ trans('pages.settings.postcodes.fields.city') }}</dt>
              <dd class="mt-1 text-sm font-medium">{{ postcode.city }}</dd>
            </div>
            <div>
              <dt class="text-xs uppercase tracking-wide text-muted-foreground">{{ trans('pages.settings.postcodes.fields.state') }}</dt>
              <dd class="mt-1 text-sm font-medium">{{ postcode.state || '—' }}</dd>
            </div>
            <div>
              <dt class="text-xs uppercase tracking-wide text-muted-foreground">{{ trans('pages.settings.postcodes.fields.country') }}</dt>
              <dd class="mt-1 text-sm font-medium">{{ postcode.country }}</dd>
            </div>
            <div>
              <dt class="text-xs uppercase tracking-wide text-muted-foreground">{{ trans('pages.settings.postcodes.fields.county') }}</dt>
              <dd class="mt-1 text-sm font-medium">{{ postcode.county || '—' }}</dd>
            </div>
          </dl>
        </div>
      </CardContent>

      <CardFooter class="flex justify-end gap-2 border-t px-6 py-4">
        <Button variant="outline" @click="router.visit(indexRoute.url())">{{ trans('pages.settings.postcodes.actions.cancel') }}</Button>
        <Button v-if="!postcode.deleted_at" variant="secondary" @click="router.visit(editRoute.url(postcode.postal_code))">
          {{ trans('pages.settings.postcodes.actions.edit') }}
        </Button>
      </CardFooter>
    </Card>
  </SidebarLayout>
</template>
