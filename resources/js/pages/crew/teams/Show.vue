<script setup lang="ts">
import { usePage, router, Head } from '@inertiajs/vue3';
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { trans } from 'laravel-vue-i18n';
import { MoreHorizontal, Trash2, Check, X, Shield, User, ArrowLeft } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { BreadcrumbItemType } from '@/types';
import { index as crewTeamsRoute } from '@/routes/crew/teams/index';
import { update as updateMemberAction, destroy as removeMemberAction } from '@/routes/crew/teams/members/index';
import DeleteConfirmationDialog from '@/components/custom/DeleteConfirmationDialog.vue';

const page = usePage<any>();
const team = computed(() => page.props.team);
const isPrivileged = computed(() => page.props.isPrivileged);
const availableRoles = computed(() => page.props.availableRoles ?? []);

const showRemoveMemberConfirm = ref(false);
const memberToRemove = ref<number | null>(null);

const breadcrumbs = computed<BreadcrumbItemType[]>(() => [
    {
        title: trans('pages.ui.navigation.crew_menu'),
        href: '/crew',
    },
    {
        title: trans('pages.ui.navigation.teams'),
        href: crewTeamsRoute.url(),
    },
    {
        title: team.value.name,
        href: `/crew/teams/${team.value.id}`,
    }
]);

function updateMember(userId: number, data: { role?: string; status: string }) {
    router.post(updateMemberAction.url({ team: team.value.id, user: userId }), data, {
        preserveScroll: true,
    });
}

function confirmRemoveMember(userId: number) {
    memberToRemove.value = userId;
    showRemoveMemberConfirm.value = true;
}

function handleRemoveMember() {
    if (memberToRemove.value) {
        router.delete(removeMemberAction.url({ team: team.value.id, user: memberToRemove.value }), {
            preserveScroll: true,
            onSuccess: () => {
                showRemoveMemberConfirm.value = false;
                memberToRemove.value = null;
            },
        });
    }
}

function goBack() {
    router.visit(crewTeamsRoute.url());
}

function getStatusBadgeVariant(status: string) {
    switch (status) {
        case 'approved': return 'default';
        case 'pending': return 'secondary';
        case 'rejected': return 'destructive';
        default: return 'outline';
    }
}
</script>

<template>
    <SidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="team.name" />

        <div class="mx-auto w-full max-w-4xl space-y-6">
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" @click="goBack">
                    <ArrowLeft class="h-4 w-4" />
                </Button>
                <h1 class="text-2xl font-bold">{{ team.name }}</h1>
            </div>

            <Card>
                <CardHeader class="flex items-start justify-between gap-4 sm:flex-row">
                    <div class="flex items-center gap-4">
                        <div class="flex size-16 items-center justify-center overflow-hidden rounded-lg border bg-muted shadow-sm">
                            <img v-if="team.logo" :src="team.logo" :alt="team.name" class="h-full w-full object-contain" />
                            <div v-else class="text-xl font-bold text-muted-foreground uppercase">{{ team.slug?.substring(0, 2) || team.name.substring(0, 2) }}</div>
                        </div>
                        <div class="space-y-1">
                            <CardTitle>{{ team.name }}</CardTitle>
                            <CardDescription>{{ team.description || trans('pages.crew.teams.no_description') }}</CardDescription>
                        </div>
                    </div>
                    <Badge v-if="team.active" variant="outline" class="text-green-600 border-green-600/20 bg-green-600/10">
                        {{ trans('pages.settings.teams.status.active') }}
                    </Badge>
                </CardHeader>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>{{ trans('pages.settings.teams.fields.members') }}</CardTitle>
                </CardHeader>
                <CardContent class="p-0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{{ trans('pages.settings.users.fields.name') }}</TableHead>
                                <TableHead>{{ trans('pages.settings.teams.fields.role') }}</TableHead>
                                <TableHead>{{ trans('pages.settings.teams.fields.status') }}</TableHead>
                                <TableHead v-if="isPrivileged" class="text-right"></TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="u in team.users" :key="u.id">
                                <TableCell>
                                    <div class="flex items-center gap-3">
                                        <div class="flex size-8 items-center justify-center overflow-hidden rounded-full border bg-muted shadow-sm">
                                            <img v-if="u.avatar" :src="u.avatar" :alt="u.name" class="h-full w-full object-cover" />
                                            <User v-else class="h-4 w-4 text-muted-foreground" />
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="font-medium text-sm">{{ u.name }}</span>
                                            <span class="text-xs text-muted-foreground">{{ u.email }}</span>
                                        </div>
                                    </div>
                                    <div v-if="isPrivileged && u.pivot.application" class="mt-2 ml-11 rounded bg-muted/50 p-2 text-xs italic">
                                        <strong>{{ trans('pages.crew.teams.fields.application') || 'Application' }}:</strong>
                                        <p class="mt-1 whitespace-pre-wrap">{{ u.pivot.application }}</p>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge variant="outline" class="flex w-fit items-center gap-1 font-normal">
                                        <Shield v-if="u.pivot.role === 'Board Chairman'" class="h-3 w-3" />
                                        <User v-else class="h-3 w-3" />
                                        {{ u.pivot.role ? trans('pages.roles.' + u.pivot.role) : trans('pages.roles.Crew') }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="getStatusBadgeVariant(u.pivot.status)" class="font-normal capitalize">
                                        {{ trans(`pages.crew.teams.status.${u.pivot.status}`) || u.pivot.status }}
                                    </Badge>
                                </TableCell>
                                <TableCell v-if="isPrivileged" class="text-right">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" size="icon">
                                                <MoreHorizontal class="h-4 w-4" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <template v-if="u.pivot.status === 'pending'">
                                                <DropdownMenuItem @click="updateMember(u.id, { status: 'approved', role: u.pivot.role })">
                                                    <Check class="mr-2 h-4 w-4 text-green-600" />
                                                    {{ trans('pages.settings.locations.actions.approve') || 'Approve' }}
                                                </DropdownMenuItem>
                                                <DropdownMenuItem @click="updateMember(u.id, { status: 'rejected', role: u.pivot.role })">
                                                    <X class="mr-2 h-4 w-4 text-red-600" />
                                                    {{ trans('pages.settings.locations.actions.reject') || 'Reject' }}
                                                </DropdownMenuItem>
                                            </template>

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
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <DeleteConfirmationDialog
                v-model:open="showRemoveMemberConfirm"
                :title="trans('pages.settings.teams.members.remove_confirm') || 'Remove member?'"
                :description="trans('pages.settings.teams.members.remove_description') || 'Are you sure you want to remove this member from the team?'"
                @confirm="handleRemoveMember"
            />
        </div>
    </SidebarLayout>
</template>
