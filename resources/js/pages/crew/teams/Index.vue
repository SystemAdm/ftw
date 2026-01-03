<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { BreadcrumbItemType } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { trans } from 'laravel-vue-i18n';
import { computed, ref } from 'vue';
import { index as crewTeamsRoute, apply as applyRoute, leave as leaveRoute } from '@/routes/crew/teams/index';
import { Badge } from '@/components/ui/badge';
import { LogOut, Send } from 'lucide-vue-next';
import DeleteConfirmationDialog from '@/components/custom/DeleteConfirmationDialog.vue';

defineProps<{
    myTeams: any[];
    availableTeams: any[];
}>();

const breadcrumbs = computed<BreadcrumbItemType[]>(() => [
    {
        title: trans('pages.ui.navigation.crew_menu'),
        href: '/crew',
    },
    {
        title: trans('pages.ui.navigation.teams'),
        href: crewTeamsRoute.url(),
    },
]);

const applyingToTeam = ref<any | null>(null);
const applyForm = useForm({
    application: '',
});

const showLeaveTeamConfirm = ref(false);
const teamToLeave = ref<number | null>(null);

function openApplyDialog(team: any) {
    applyingToTeam.value = team;
    applyForm.reset();
}

function handleApply() {
    if (!applyingToTeam.value) return;

    applyForm.post(applyRoute.url(applyingToTeam.value.id), {
        onSuccess: () => {
            applyingToTeam.value = null;
        },
    });
}

function confirmLeaveTeam(teamId: number) {
    teamToLeave.value = teamId;
    showLeaveTeamConfirm.value = true;
}

function handleLeave() {
    if (teamToLeave.value) {
        router.delete(leaveRoute.url(teamToLeave.value), {
            onSuccess: () => {
                showLeaveTeamConfirm.value = false;
                teamToLeave.value = null;
            },
        });
    }
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
        <Head :title="trans('pages.ui.navigation.teams')" />

        <div class="space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle>{{ trans('pages.crew.teams.my_teams') }}</CardTitle>
                    <CardDescription>{{ trans('pages.crew.teams.my_teams_description') }}</CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{{ trans('pages.settings.teams.fields.name') }}</TableHead>
                                <TableHead>{{ trans('pages.crew.teams.fields.role') }}</TableHead>
                                <TableHead>{{ trans('pages.crew.teams.fields.status') }}</TableHead>
                                <TableHead class="text-right"></TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="team in myTeams" :key="team.id">
                                <TableCell class="font-medium cursor-pointer" @click="router.visit(`/crew/teams/${team.id}`)">
                                    <div class="flex items-center gap-2">
                                        <div class="flex size-8 items-center justify-center overflow-hidden rounded border bg-muted">
                                            <img v-if="team.logo" :src="team.logo" :alt="team.name" class="h-full w-full object-contain" />
                                            <div v-else class="text-[10px] font-bold text-muted-foreground uppercase">{{ team.slug?.substring(0, 2) || team.name.substring(0, 2) }}</div>
                                        </div>
                                        {{ team.name }}
                                    </div>
                                </TableCell>
                                <TableCell class="cursor-pointer" @click="router.visit(`/crew/teams/${team.id}`)">
                                    {{ team.pivot.role ? trans('pages.roles.' + team.pivot.role) : trans('pages.roles.Crew') }}
                                </TableCell>
                                <TableCell class="cursor-pointer" @click="router.visit(`/crew/teams/${team.id}`)">
                                    <Badge :variant="getStatusBadgeVariant(team.pivot.status)">
                                        {{ trans(`pages.crew.teams.status.${team.pivot.status}`) || team.pivot.status }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right">
                                    <Button variant="ghost" size="sm" @click="confirmLeaveTeam(team.id)" class="text-destructive hover:text-destructive">
                                        <LogOut class="mr-2 h-4 w-4" />
                                        {{ trans('pages.crew.teams.leave') || 'Leave' }}
                                    </Button>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="myTeams.length === 0">
                                <TableCell colspan="4" class="py-4 text-center text-muted-foreground">
                                    {{ trans('pages.crew.teams.no_teams') }}
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <DeleteConfirmationDialog
                v-model:open="showLeaveTeamConfirm"
                :title="trans('pages.crew.teams.leave') || 'Leave team?'"
                :description="trans('pages.crew.teams.leave_confirm') || 'Are you sure you want to leave this team?'"
                @confirm="handleLeave"
            />

            <Card>
                <CardHeader>
                    <CardTitle>{{ trans('pages.crew.teams.available_teams') }}</CardTitle>
                    <CardDescription>{{ trans('pages.crew.teams.available_teams_description') }}</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <Card v-for="team in availableTeams" :key="team.id" class="flex flex-col">
                            <CardHeader class="flex flex-row items-center gap-4 pb-2">
                                <div class="flex size-10 items-center justify-center overflow-hidden rounded border bg-muted">
                                    <img v-if="team.logo" :src="team.logo" :alt="team.name" class="h-full w-full object-contain" />
                                    <div v-else class="text-xs font-bold text-muted-foreground uppercase">{{ team.slug?.substring(0, 2) || team.name.substring(0, 2) }}</div>
                                </div>
                                <div class="flex flex-col">
                                    <CardTitle class="text-base">{{ team.name }}</CardTitle>
                                    <CardDescription>{{ team.slug }}</CardDescription>
                                </div>
                            </CardHeader>
                            <CardContent class="flex-1">
                                <p class="line-clamp-2 text-sm text-muted-foreground">
                                    {{ team.description || trans('pages.crew.teams.no_description') }}
                                </p>
                            </CardContent>
                            <CardFooter>
                                <Button variant="default" size="sm" class="w-full" @click="openApplyDialog(team)">
                                    <Send class="mr-2 h-4 w-4" />
                                    {{ trans('pages.crew.teams.apply') || 'Apply' }}
                                </Button>
                            </CardFooter>
                        </Card>
                    </div>
                    <div v-if="availableTeams.length === 0" class="py-4 text-center text-muted-foreground">
                        {{ trans('pages.crew.teams.no_available_teams') }}
                    </div>
                </CardContent>
            </Card>

            <Dialog :open="!!applyingToTeam" @update:open="(v) => (!v ? (applyingToTeam = null) : null)">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{{ trans('pages.crew.teams.apply_to') || 'Apply to' }} {{ applyingToTeam?.name }}</DialogTitle>
                        <DialogDescription>
                            {{ trans('pages.crew.teams.apply_description') || 'Please write a short application explaining why you want to join this team.' }}
                        </DialogDescription>
                    </DialogHeader>

                    <div class="space-y-4 py-4">
                        <div class="space-y-2">
                            <Label for="application">{{ trans('pages.crew.teams.fields.application') || 'Application' }}</Label>
                            <Textarea
                                id="application"
                                v-model="applyForm.application"
                                :placeholder="trans('pages.crew.teams.fields.application_placeholder') || 'Write your application here...'"
                                class="min-h-[100px]"
                            />
                            <div v-if="applyForm.errors.application" class="text-sm text-destructive">
                                {{ applyForm.errors.application }}
                            </div>
                        </div>
                    </div>

                    <DialogFooter>
                        <Button variant="outline" @click="applyingToTeam = null">
                            {{ trans('pages.settings.locations.actions.cancel') }}
                        </Button>
                        <Button @click="handleApply" :disabled="applyForm.processing">
                            {{ applyForm.processing ? trans('pages.contact.actions.sending') : trans('pages.crew.teams.apply') }}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </SidebarLayout>
</template>
