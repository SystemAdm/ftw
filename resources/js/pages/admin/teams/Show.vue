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
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Separator } from '@/components/ui/separator';
import { trans } from 'laravel-vue-i18n';
import { MoreHorizontal, Trash2, Check, X, Shield, User } from 'lucide-vue-next';
import {
    destroy as deleteTeamRoute,
    restore as restoreTeamRoute,
    forceDestroy as forceDeleteTeamRoute
} from '@/routes/admin/teams/index';
import { update as updateMemberAction, remove as removeMemberAction } from '@/routes/admin/teams/members/index';
import DeleteConfirmationDialog from '@/components/custom/DeleteConfirmationDialog.vue';
import { ref, computed } from 'vue';

const page = usePage<PageProps>();
const team = page.props.team as any;
const availableRoles = computed(() => (page.props as any).availableRoles ?? []);

const showRemoveMemberConfirm = ref(false);
const memberToRemove = ref<number | null>(null);

function updateMember(userId: number, data: { role?: string; status: string }) {
    router.post(updateMemberAction.url({ team: team.id, user: userId }), data, {
        preserveScroll: true,
    });
}

function confirmRemoveMember(userId: number) {
    memberToRemove.value = userId;
    showRemoveMemberConfirm.value = true;
}

function handleRemoveMember() {
    if (memberToRemove.value) {
        router.delete(removeMemberAction.url({ team: team.id, user: memberToRemove.value }), {
            preserveScroll: true,
            onSuccess: () => {
                showRemoveMemberConfirm.value = false;
                memberToRemove.value = null;
            },
        });
    }
}

function goBack() {
    router.visit('/admin/teams');
}

function editTeam() {
    router.visit(`/admin/teams/${team.id}/edit`);
}

function deleteTeam() {
    router.delete(deleteTeamRoute.url(team.id), {
        onFinish: () => router.visit('/admin/teams'),
    });
}

function restoreTeam() {
    router.post(restoreTeamRoute.url(team.id));
}

function forceDeleteTeam() {
    router.delete(forceDeleteTeamRoute.url(team.id), {
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
                <div class="rounded-md border bg-card">
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>{{ trans('pages.settings.users.fields.name') }}</TableHead>
                        <TableHead>{{ trans('pages.settings.teams.fields.role') }}</TableHead>
                        <TableHead>{{ trans('pages.settings.teams.fields.status') }}</TableHead>
                        <TableHead class="text-right"></TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      <TableRow v-for="u in team.users" :key="u.id">
                        <TableCell>
                          <div class="flex flex-col">
                            <span class="font-medium">{{ u.name }}</span>
                            <span class="text-xs text-muted-foreground">{{ u.email }}</span>
                            <div v-if="u.pivot.application" class="mt-2 rounded bg-muted/50 p-2 text-xs italic">
                              <strong>{{ trans('pages.crew.teams.fields.application') || 'Application' }}:</strong>
                              <p class="mt-1 whitespace-pre-wrap">{{ u.pivot.application }}</p>
                            </div>
                          </div>
                        </TableCell>
                        <TableCell>
                          <Badge variant="outline" class="flex w-fit items-center gap-1">
                            <Shield v-if="u.pivot.role === 'Board Chairman'" class="h-3 w-3" />
                            <User v-else class="h-3 w-3" />
                            {{ u.pivot.role ? trans('pages.roles.' + u.pivot.role) : trans('pages.roles.Crew') }}
                          </Badge>
                        </TableCell>
                        <TableCell>
                          <Badge :variant="u.pivot.status === 'approved' ? 'default' : u.pivot.status === 'pending' ? 'secondary' : 'destructive'">
                            {{ trans(`pages.crew.teams.status.${u.pivot.status}`) }}
                          </Badge>
                        </TableCell>
                        <TableCell class="text-right">
                          <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                              <Button variant="ghost" size="icon">
                                <MoreHorizontal class="h-4 w-4" />
                              </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                              <DropdownMenuItem v-if="u.pivot.status === 'pending'" @click="updateMember(u.id, { status: 'approved', role: u.pivot.role })">
                                <Check class="mr-2 h-4 w-4 text-green-600" />
                                {{ trans('pages.settings.locations.actions.approve') || 'Approve' }}
                              </DropdownMenuItem>
                              <DropdownMenuItem v-if="u.pivot.status === 'pending'" @click="updateMember(u.id, { status: 'rejected', role: u.pivot.role })">
                                <X class="mr-2 h-4 w-4 text-red-600" />
                                {{ trans('pages.settings.locations.actions.reject') || 'Reject' }}
                              </DropdownMenuItem>
                              <template v-if="u.pivot.status === 'approved'">
                                <DropdownMenuItem
                                    v-for="role in availableRoles"
                                    :key="role"
                                    v-show="u.pivot.role !== role"
                                    @click="updateMember(u.id, { status: u.pivot.status, role })"
                                >
                                    <Shield class="mr-2 h-4 w-4" />
                                    {{ trans('pages.settings.teams.actions.change_role_to') || 'Change role to' }} {{ trans('pages.roles.' + role) }}
                                </DropdownMenuItem>
                              </template>
                              <DropdownMenuItem class="text-destructive" @click="confirmRemoveMember(u.id)">
                                <Trash2 class="mr-2 h-4 w-4" />
                                {{ trans('pages.settings.locations.actions.delete') }}
                              </DropdownMenuItem>
                            </DropdownMenuContent>
                          </DropdownMenu>
                        </TableCell>
                      </TableRow>
                      <TableRow v-if="!team.users?.length">
                        <TableCell colspan="4" class="text-center text-sm text-muted-foreground">
                          {{ trans('pages.settings.locations.none') }}
                        </TableCell>
                      </TableRow>
                    </TableBody>
                  </Table>
                </div>
              </section>
            </div>

            <DeleteConfirmationDialog
                v-model:open="showRemoveMemberConfirm"
                :title="trans('pages.settings.teams.members.remove_confirm') || 'Remove member?'"
                :description="trans('pages.settings.teams.members.remove_description') || 'Are you sure you want to remove this member from the team?'"
                @confirm="handleRemoveMember"
            />

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
