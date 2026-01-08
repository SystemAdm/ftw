<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { BreadcrumbItemType } from '@/types';
import { Head, router, useForm, Link } from '@inertiajs/vue3';
import { Card, CardContent, CardFooter } from '@/components/ui/card';
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
        title: trans('ui.navigation.home'),
        href: '/',
    },
    {
        title: trans('ui.navigation.crew'),
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

        <div class="space-y-8">
            <div class="mb-8 flex flex-col gap-4">
                <h1 class="text-3xl font-extrabold tracking-tight text-foreground sm:text-4xl">
                    {{ trans('pages.ui.navigation.teams') }}
                </h1>
                <p class="text-xl text-muted-foreground">
                    {{ trans('pages.teams.subtitle') }}
                </p>
            </div>

            <section class="space-y-4">
                <h2 class="text-xl font-bold">{{ trans('pages.crew.teams.my_teams') }}</h2>
                <div v-if="myTeams.length > 0" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <Card
                        v-for="team in myTeams"
                        :key="team.id"
                        class="group flex flex-col overflow-hidden transition-all hover:shadow-md"
                    >
                        <div class="aspect-video w-full overflow-hidden bg-muted cursor-pointer" @click="router.visit(`/crew/teams/${team.id}`)">
                            <img
                                v-if="team.logo"
                                :src="team.logo"
                                :alt="team.name"
                                class="h-full w-full object-cover transition-transform group-hover:scale-105"
                            />
                            <div v-else class="flex h-full w-full items-center justify-center bg-primary/10 text-4xl font-bold text-primary uppercase">
                                {{ team.slug?.substring(0, 2) || team.name.substring(0, 2) }}
                            </div>
                        </div>
                        <CardContent class="flex flex-1 flex-col p-6 cursor-pointer" @click="router.visit(`/crew/teams/${team.id}`)">
                            <div class="mb-2 flex items-center justify-between">
                                <Badge :variant="getStatusBadgeVariant(team.pivot.status)">
                                    {{ trans(`pages.crew.teams.status.${team.pivot.status}`) || team.pivot.status }}
                                </Badge>
                                <div v-if="team.slug" class="text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                    @{{ team.slug }}
                                </div>
                            </div>
                            <h3 class="mb-1 text-xl font-bold group-hover:text-primary">
                                {{ team.name }}
                            </h3>
                            <p class="text-sm font-medium text-muted-foreground">
                                {{ team.pivot.role ? trans('pages.roles.' + team.pivot.role) : trans('pages.roles.Crew') }}
                            </p>
                        </CardContent>
                        <CardFooter class="px-6 py-4 border-t bg-muted/50 flex justify-between items-center">
                            <Button variant="ghost" size="sm" @click="confirmLeaveTeam(team.id)" class="text-destructive hover:text-destructive hover:bg-destructive/10">
                                <LogOut class="mr-2 h-4 w-4" />
                                {{ trans('pages.crew.teams.leave') || 'Leave' }}
                            </Button>
                            <Link :href="`/crew/teams/${team.id}`" class="inline-flex items-center text-sm font-semibold text-primary">
                                {{ trans('pages.teams.view_details') }}
                                <span class="ml-1 transition-transform group-hover:translate-x-1">→</span>
                            </Link>
                        </CardFooter>
                    </Card>
                </div>
                <Card v-else class="border-dashed">
                    <CardContent class="flex flex-col items-center justify-center p-12 text-center">
                        <div class="text-muted-foreground">{{ trans('pages.crew.teams.no_teams') }}</div>
                    </CardContent>
                </Card>
            </section>

            <section class="space-y-4">
                <h2 class="text-xl font-bold">{{ trans('pages.crew.teams.available_teams') }}</h2>
                <div v-if="availableTeams.length > 0" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <Card v-for="team in availableTeams" :key="team.id" class="group flex flex-col overflow-hidden transition-all hover:shadow-md">
                        <div class="aspect-video w-full overflow-hidden bg-muted">
                            <img
                                v-if="team.logo"
                                :src="team.logo"
                                :alt="team.name"
                                class="h-full w-full object-cover transition-transform group-hover:scale-105"
                            />
                            <div v-else class="flex h-full w-full items-center justify-center bg-primary/10 text-4xl font-bold text-primary uppercase">
                                {{ team.slug?.substring(0, 2) || team.name.substring(0, 2) }}
                            </div>
                        </div>
                        <CardContent class="flex-1 p-6">
                            <div class="mb-2 flex items-center justify-between">
                                <div v-if="team.slug" class="text-xs font-medium text-muted-foreground uppercase tracking-wider">
                                    @{{ team.slug }}
                                </div>
                            </div>
                            <h3 class="mb-2 text-xl font-bold group-hover:text-primary">
                                {{ team.name }}
                            </h3>
                            <p class="line-clamp-2 text-sm text-muted-foreground">
                                {{ team.description || trans('crew.teams.no_description') }}
                            </p>
                        </CardContent>
                        <CardFooter class="px-6 py-4 border-t">
                            <Button variant="default" size="sm" class="w-full font-bold" @click="openApplyDialog(team)">
                                <Send class="mr-2 h-4 w-4" />
                                {{ trans('crew.teams.apply') || 'Apply' }}
                            </Button>
                        </CardFooter>
                    </Card>
                </div>
                <Card v-else class="border-dashed">
                    <CardContent class="flex flex-col items-center justify-center p-12 text-center">
                        <div class="text-muted-foreground">{{ trans('crew.teams.no_available_teams') }}</div>
                    </CardContent>
                </Card>
            </section>

            <Dialog :open="!!applyingToTeam" @update:open="(v) => (!v ? (applyingToTeam = null) : null)">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{{ trans('crew.teams.apply_to') || 'Apply to' }} {{ applyingToTeam?.name }}</DialogTitle>
                        <DialogDescription>
                            {{ trans('crew.teams.apply_description') || 'Please write a short application explaining why you want to join this team.' }}
                        </DialogDescription>
                    </DialogHeader>

                    <div class="space-y-4 py-4">
                        <div class="space-y-2">
                            <Label for="application">{{ trans('crew.teams.fields.application') || 'Application' }}</Label>
                            <Textarea
                                id="application"
                                v-model="applyForm.application"
                                :placeholder="trans('crew.teams.fields.application_placeholder') || 'Write your application here...'"
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
                            {{ applyForm.processing ? trans('pages.contact.actions.sending') : trans('crew.teams.apply') }}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <DeleteConfirmationDialog
                v-model:open="showLeaveTeamConfirm"
                :title="trans('pages.crew.teams.leave') || 'Leave team?'"
                :description="trans('pages.crew.teams.leave_confirm') || 'Are you sure you want to leave this team?'"
                @confirm="handleLeave"
            />
        </div>
    </SidebarLayout>
</template>
