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
              <span class="text-xs text-muted-foreground">Slug:</span>
              <code class="rounded bg-muted px-1.5 py-0.5 text-xs">{{ team.slug }}</code>
              <Separator orientation="vertical" class="mx-1 hidden h-4 sm:inline-block" />
              <Badge v-if="team.deleted_at" variant="destructive">Deleted</Badge>
              <Badge v-else :variant="team.active ? 'default' : 'secondary'">{{ team.active ? 'Active' : 'Inactive' }}</Badge>
            </CardDescription>
          </div>

          <div class="flex flex-wrap gap-2">
            <Button variant="outline" @click="goBack">Back</Button>
            <Button variant="secondary" v-if="!team.deleted_at" @click="editTeam">Edit</Button>

            <AlertDialog v-if="!team.deleted_at">
              <AlertDialogTrigger as-child>
                <Button variant="destructive">Delete</Button>
              </AlertDialogTrigger>
              <AlertDialogContent>
                <AlertDialogHeader>
                  <AlertDialogTitle>Delete team?</AlertDialogTitle>
                  <AlertDialogDescription>
                    This action will soft delete the team. You can restore it later.
                  </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                  <AlertDialogCancel>Cancel</AlertDialogCancel>
                  <AlertDialogAction @click="deleteTeam">Delete</AlertDialogAction>
                </AlertDialogFooter>
              </AlertDialogContent>
            </AlertDialog>

            <Button v-else @click="restoreTeam">Restore</Button>

            <AlertDialog v-if="team.deleted_at">
              <AlertDialogTrigger as-child>
                <Button variant="destructive">Force Delete</Button>
              </AlertDialogTrigger>
              <AlertDialogContent>
                <AlertDialogHeader>
                  <AlertDialogTitle>Permanently delete team?</AlertDialogTitle>
                  <AlertDialogDescription>
                    This action cannot be undone. This will permanently delete the team and remove its data.
                  </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                  <AlertDialogCancel>Cancel</AlertDialogCancel>
                  <AlertDialogAction @click="forceDeleteTeam">Force Delete</AlertDialogAction>
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
                <h2 class="mb-2 text-sm font-medium text-muted-foreground">About</h2>
                <div class="rounded-md border bg-card p-4">
                  <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-3">
                    <div class="sm:col-span-3">
                      <dt class="text-xs uppercase tracking-wide text-muted-foreground">Description</dt>
                      <dd class="mt-1 text-sm text-card-foreground">{{ team.description || '—' }}</dd>
                    </div>
                    <div>
                      <dt class="text-xs uppercase tracking-wide text-muted-foreground">Created</dt>
                      <dd class="mt-1 text-sm">{{ team.created_at }}</dd>
                    </div>
                    <div>
                      <dt class="text-xs uppercase tracking-wide text-muted-foreground">Updated</dt>
                      <dd class="mt-1 text-sm">{{ team.updated_at }}</dd>
                    </div>
                    <div v-if="team.deleted_at">
                      <dt class="text-xs uppercase tracking-wide text-muted-foreground">Deleted</dt>
                      <dd class="mt-1 text-sm">{{ team.deleted_at }}</dd>
                    </div>
                  </dl>
                </div>
              </section>

              <section>
                <h2 class="mb-2 text-sm font-medium text-muted-foreground">Members</h2>
                <div class="rounded-md border bg-card p-4">
                  <div v-if="(team.users ?? []).length" class="flex flex-wrap gap-2">
                    <Badge v-for="u in team.users" :key="u.id" variant="outline">{{ u.name }}</Badge>
                  </div>
                  <div v-else class="text-sm text-muted-foreground">No members assigned.</div>
                </div>
              </section>
            </div>

            <!-- Logo preview -->
            <aside class="space-y-3">
              <h2 class="text-sm font-medium text-muted-foreground">Logo</h2>
              <div class="rounded-md border bg-card p-4">
                <div v-if="team.logo" class="space-y-3">
                  <div class="overflow-hidden rounded-md border">
                    <img :src="team.logo" alt="Team logo" class="h-40 w-full object-contain " />
                  </div>
                  <a :href="team.logo" target="_blank" class="text-xs text-blue-600 underline break-all">{{ team.logo }}</a>
                </div>
                <div v-else class="text-sm text-muted-foreground">No logo provided.</div>
              </div>
            </aside>
          </div>
        </CardContent>

        <CardFooter class="flex justify-end gap-2">
          <Button variant="outline" @click="goBack">Back</Button>
          <Button v-if="!team.deleted_at" variant="secondary" @click="editTeam">Edit</Button>
        </CardFooter>
      </Card>
    </SidebarLayout>
</template>
