<script setup lang="ts">
import { usePage, router } from '@inertiajs/vue3';
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
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
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { trans } from 'laravel-vue-i18n';

const page = usePage<PageProps>();
const team = page.props.team as any;

function goBack() {
    router.visit('/admin/teams');
}

function editTeam() {
    router.visit(`/admin/teams/${team.id}/edit`);
}

function deleteTeam() {
    router.delete(`/admin/teams/${team.id}`, {
        onFinish: () => router.visit('/admin/teams'),
    });
}

function restoreTeam() {
    router.post(`/admin/teams/${team.id}/restore`);
}

function forceDeleteTeam() {
    router.delete(`/admin/teams/${team.id}/force`, {
        onFinish: () => router.visit('/admin/teams'),
    });
}
</script>

<template>
    <SidebarLayout>
      <Card class="mx-auto w-full max-w-4xl">
        <CardHeader class="flex items-start justify-between gap-4 sm:flex-row">
          <div class="space-y-1">
            <CardTitle class="text-2xl">{{ team.name }}</CardTitle>
            <CardDescription class="flex items-center gap-2">
              <span class="text-xs text-muted-foreground">{{ trans('pages.settings.teams.fields.slug') }}:</span>
              <code class="rounded bg-muted px-1.5 py-0.5 text-xs">{{ team.slug }}</code>
              <Separator orientation="vertical" class="mx-1 hidden h-4 sm:inline-block" />
              <Badge v-if="team.deleted_at" variant="destructive">{{ trans('pages.settings.teams.status.deleted') }}</Badge>
              <Badge v-else :variant="team.active ? 'default' : 'secondary'">{{ team.active ? trans('pages.settings.teams.status.active') : trans('pages.settings.teams.status.inactive') }}</Badge>
            </CardDescription>
          </div>

          <div class="flex flex-wrap gap-2">
            <Button variant="outline" @click="goBack">{{ trans('pages.settings.locations.actions.cancel') }}</Button>
            <Button variant="secondary" v-if="!team.deleted_at" @click="editTeam">{{ trans('pages.settings.locations.actions.edit') }}</Button>

            <AlertDialog v-if="!team.deleted_at">
              <AlertDialogTrigger as-child>
                <Button variant="destructive">{{ trans('pages.settings.locations.actions.delete') }}</Button>
              </AlertDialogTrigger>
              <AlertDialogContent>
                <AlertDialogHeader>
                  <AlertDialogTitle>{{ trans('pages.settings.teams.delete.title') }}</AlertDialogTitle>
                  <AlertDialogDescription>
                    {{ trans('pages.settings.teams.delete.description') }}
                  </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                  <AlertDialogCancel>{{ trans('pages.settings.locations.actions.cancel') }}</AlertDialogCancel>
                  <AlertDialogAction @click="deleteTeam">{{ trans('pages.settings.locations.actions.delete') }}</AlertDialogAction>
                </AlertDialogFooter>
              </AlertDialogContent>
            </AlertDialog>

            <Button v-else @click="restoreTeam">{{ trans('pages.settings.teams.actions.restore') }}</Button>

            <AlertDialog v-if="team.deleted_at">
              <AlertDialogTrigger as-child>
                <Button variant="destructive">{{ trans('pages.settings.teams.actions.force_delete') }}</Button>
              </AlertDialogTrigger>
              <AlertDialogContent>
                <AlertDialogHeader>
                  <AlertDialogTitle>{{ trans('pages.settings.teams.force_delete.title') }}</AlertDialogTitle>
                  <AlertDialogDescription>
                    {{ trans('pages.settings.teams.force_delete.description') }}
                  </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                  <AlertDialogCancel>{{ trans('pages.settings.locations.actions.cancel') }}</AlertDialogCancel>
                  <AlertDialogAction @click="forceDeleteTeam">{{ trans('pages.settings.teams.actions.force_delete') }}</AlertDialogAction>
                </AlertDialogFooter>
              </AlertDialogContent>
            </AlertDialog>
          </div>
        </CardHeader>

        <CardContent>
          <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Details -->
            <div class="lg:col-span-2 space-y-6">
              <section>
                <h2 class="mb-2 text-sm font-medium text-muted-foreground">{{ trans('pages.settings.teams.fields.about') }}</h2>
                <div class="rounded-md border bg-card p-4">
                  <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-3">
                    <div class="sm:col-span-3">
                      <dt class="text-xs uppercase tracking-wide text-muted-foreground">{{ trans('pages.settings.teams.fields.description') }}</dt>
                      <dd class="mt-1 text-sm text-card-foreground">{{ team.description || '—' }}</dd>
                    </div>
                    <div>
                      <dt class="text-xs uppercase tracking-wide text-muted-foreground">{{ trans('pages.settings.teams.fields.created') }}</dt>
                      <dd class="mt-1 text-sm">{{ team.created_at }}</dd>
                    </div>
                    <div>
                      <dt class="text-xs uppercase tracking-wide text-muted-foreground">{{ trans('pages.settings.teams.fields.updated') }}</dt>
                      <dd class="mt-1 text-sm">{{ team.updated_at }}</dd>
                    </div>
                    <div v-if="team.deleted_at">
                      <dt class="text-xs uppercase tracking-wide text-muted-foreground">{{ trans('pages.settings.teams.fields.deleted') }}</dt>
                      <dd class="mt-1 text-sm">{{ team.deleted_at }}</dd>
                    </div>
                  </dl>
                </div>
              </section>

              <section>
                <h2 class="mb-2 text-sm font-medium text-muted-foreground">{{ trans('pages.settings.teams.fields.members') }}</h2>
                <div class="rounded-md border bg-card p-4">
                  <div v-if="(team.users ?? []).length" class="flex flex-wrap gap-2">
                    <Badge v-for="u in team.users" :key="u.id" variant="outline">{{ u.name }}</Badge>
                  </div>
                  <div v-else class="text-sm text-muted-foreground">{{ trans('pages.settings.locations.none') }}</div>
                </div>
              </section>
            </div>

            <!-- Logo preview -->
            <aside class="space-y-3">
              <h2 class="text-sm font-medium text-muted-foreground">{{ trans('pages.settings.teams.fields.logo') }}</h2>
              <div class="rounded-md border bg-card p-4">
                <div v-if="team.logo" class="space-y-3">
                  <div class="overflow-hidden rounded-md border">
                    <img :src="team.logo" alt="Team logo" class="h-40 w-full object-contain " />
                  </div>
                  <a :href="team.logo" target="_blank" class="text-xs text-blue-600 underline break-all">{{ team.logo }}</a>
                </div>
                <div v-else class="text-sm text-muted-foreground">{{ trans('pages.settings.locations.none') }}</div>
              </div>
            </aside>
          </div>
        </CardContent>

        <CardFooter class="flex justify-end gap-2">
          <Button variant="outline" @click="goBack">{{ trans('pages.settings.locations.actions.cancel') }}</Button>
          <Button v-if="!team.deleted_at" variant="secondary" @click="editTeam">{{ trans('pages.settings.locations.actions.edit') }}</Button>
        </CardFooter>
      </Card>
    </SidebarLayout>
</template>
