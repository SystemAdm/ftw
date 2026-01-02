<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { BreadcrumbItemType, AppPageProps } from '@/types';
import { Table, TableBody, TableCell, TableFooter, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';
import Paginator from '@/components/custom/Paginator.vue';
import { trans } from 'laravel-vue-i18n';
import { MoreHorizontal, ShieldCheck, ShieldAlert, Trash2, UserPlus } from 'lucide-vue-next';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Field, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import axios from 'axios';
import DeleteConfirmationDialog from '@/components/custom/DeleteConfirmationDialog.vue';
import { index as indexRoute, store as storeRoute, verify as verifyRoute, destroy as destroyRoute, searchUsers as searchUsersRoute } from '@/routes/admin/relations/index';

interface PageProps extends AppPageProps {
    relations: {
        data: any[];
    };
    i18n: {
        locale: string;
    };
}

const inertiaPage = usePage<PageProps>();

const createDialogOpen = ref(false);
const deleteDialogOpen = ref(false);
const selectedRelation = ref<any>(null);

const guardianSearch = ref('');
const minorSearch = ref('');
const guardiansFound = ref<any[]>([]);
const minorsFound = ref<any[]>([]);

const form = useForm({
    guardian_id: null as number | null,
    minor_id: null as number | null,
    relationship: '',
});

const breadcrumbs = computed<BreadcrumbItemType[]>(() => [
    {
        title: trans('pages.settings.relations.title'),
        href: indexRoute.url(),
    },
]);

function formatDate(date: string) {
    if (!date) return '';
    return new Date(date).toLocaleString(inertiaPage.props.i18n.locale, {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function handleVerify(relation: any) {
    router.post(verifyRoute.url({ guardian: relation.guardian_id, minor: relation.minor_id }));
}

function openDeleteDialog(relation: any) {
    selectedRelation.value = relation;
    deleteDialogOpen.value = true;
}

function handleDelete() {
    if (!selectedRelation.value) return;
    router.delete(destroyRoute.url({ guardian: selectedRelation.value.guardian_id, minor: selectedRelation.value.minor_id }), {
        onSuccess: () => {
            deleteDialogOpen.value = false;
            selectedRelation.value = null;
        },
    });
}

function openCreateDialog() {
    form.reset();
    guardianSearch.value = '';
    minorSearch.value = '';
    guardiansFound.value = [];
    minorsFound.value = [];
    createDialogOpen.value = true;
}

async function searchGuardians() {
    if (guardianSearch.value.length < 2) {
        guardiansFound.value = [];
        return;
    }
    const response = await axios.get(searchUsersRoute.url(), { params: { q: guardianSearch.value } });
    guardiansFound.value = response.data.data;
}

async function searchMinors() {
    if (minorSearch.value.length < 2) {
        minorsFound.value = [];
        return;
    }
    const response = await axios.get(searchUsersRoute.url(), { params: { q: minorSearch.value } });
    minorsFound.value = response.data.data;
}

function selectGuardian(user: any) {
    form.guardian_id = user.id;
    guardianSearch.value = `${user.name} (${user.email})`;
    guardiansFound.value = [];
}

function selectMinor(user: any) {
    form.minor_id = user.id;
    minorSearch.value = `${user.name} (${user.email})`;
    minorsFound.value = [];
}

function handleCreate() {
    form.post(storeRoute.url(), {
        onSuccess: () => {
            createDialogOpen.value = false;
        },
    });
}

watch(guardianSearch, (val) => {
    if (!val || !val.includes('(')) {
        form.guardian_id = null;
    }
});

watch(minorSearch, (val) => {
    if (!val || !val.includes('(')) {
        form.minor_id = null;
    }
});
</script>

<template>
    <SidebarLayout :breadcrumbs="breadcrumbs">
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-xl font-semibold">{{ trans('pages.settings.relations.title') }}</h1>
            <Button @click="openCreateDialog">
                <UserPlus class="mr-2 h-4 w-4" />
                {{ trans('pages.settings.relations.new') }}
            </Button>
        </div>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>{{ trans('pages.settings.relations.fields.guardian') }}</TableHead>
                    <TableHead>{{ trans('pages.settings.relations.fields.minor') }}</TableHead>
                    <TableHead>{{ trans('pages.settings.relations.fields.relationship') }}</TableHead>
                    <TableHead class="w-12 text-center">{{ trans('pages.settings.relations.fields.verified_user_at') }}</TableHead>
                    <TableHead class="w-12 text-center">{{ trans('pages.settings.relations.fields.verified_guardian_at') }}</TableHead>
                    <TableHead class="w-12 text-center">{{ trans('pages.settings.relations.fields.verified_at') }}</TableHead>
                    <TableHead class="w-12"></TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="relation in inertiaPage.props.relations.data" :key="`${relation.guardian_id}-${relation.minor_id}`">
                    <TableCell>
                        <div class="font-medium">{{ relation.guardian_name || trans('pages.auth.login.guardian_not_found_note') }}</div>
                        <div class="text-xs text-muted-foreground">{{ relation.guardian_email || relation.pending_contact }}</div>
                    </TableCell>
                    <TableCell>
                        <div class="font-medium">{{ relation.minor_name }}</div>
                        <div class="text-xs text-muted-foreground">{{ relation.minor_email }}</div>
                    </TableCell>
                    <TableCell>{{ relation.relationship }}</TableCell>
                    <TableCell>
                        <div class="flex items-center justify-center">
                            <TooltipProvider>
                                <Tooltip>
                                    <TooltipTrigger>
                                        <ShieldCheck v-if="relation.verified_user_at" class="h-4 w-4 text-green-600" />
                                        <ShieldAlert v-else class="h-4 w-4 text-gray-400" />
                                    </TooltipTrigger>
                                    <TooltipContent>
                                        <div v-if="relation.verified_user_at">
                                            {{ formatDate(relation.verified_user_at) }}
                                        </div>
                                        <div v-else>
                                            {{ trans('pages.settings.relations.status.unverified') }}
                                        </div>
                                    </TooltipContent>
                                </Tooltip>
                            </TooltipProvider>
                        </div>
                    </TableCell>
                    <TableCell>
                        <div class="flex items-center justify-center">
                            <TooltipProvider>
                                <Tooltip>
                                    <TooltipTrigger>
                                        <ShieldCheck v-if="relation.verified_guardian_at" class="h-4 w-4 text-green-600" />
                                        <ShieldAlert v-else class="h-4 w-4 text-gray-400" />
                                    </TooltipTrigger>
                                    <TooltipContent>
                                        <div v-if="relation.verified_guardian_at">
                                            {{ formatDate(relation.verified_guardian_at) }}
                                        </div>
                                        <div v-else>
                                            {{ trans('pages.settings.relations.status.unverified') }}
                                        </div>
                                    </TooltipContent>
                                </Tooltip>
                            </TooltipProvider>
                        </div>
                    </TableCell>
                    <TableCell>
                        <div class="flex items-center justify-center">
                            <TooltipProvider>
                                <Tooltip>
                                    <TooltipTrigger>
                                        <ShieldCheck v-if="relation.verified_at" class="h-4 w-4 text-blue-600" />
                                        <ShieldAlert v-else class="h-4 w-4 text-gray-400" />
                                    </TooltipTrigger>
                                    <TooltipContent>
                                        <div v-if="relation.verified_at" class="space-y-1">
                                            <p class="font-medium">{{ trans('pages.settings.relations.status.verified') }}</p>
                                            <p class="text-xs">{{ trans('pages.settings.relations.fields.verified_at') }}: {{ formatDate(relation.verified_at) }}</p>
                                            <p v-if="relation.verifier_name" class="text-xs">{{ trans('pages.settings.relations.fields.verified_by') }}: {{ relation.verifier_name }}</p>
                                        </div>
                                        <div v-else>
                                            {{ trans('pages.settings.relations.status.unverified') }}
                                        </div>
                                    </TooltipContent>
                                </Tooltip>
                            </TooltipProvider>
                        </div>
                    </TableCell>
                    <TableCell>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="ghost" size="icon">
                                    <MoreHorizontal class="h-4 w-4" />
                                    <span class="sr-only">Open menu</span>
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuItem v-if="!relation.verified_at" @click="handleVerify(relation)">
                                    <ShieldCheck class="mr-2 h-4 w-4" />
                                    {{ trans('pages.settings.relations.actions.verify') }}
                                </DropdownMenuItem>

                                <DropdownMenuItem class="text-destructive focus:text-destructive" @click="openDeleteDialog(relation)">
                                    <Trash2 class="mr-2 h-4 w-4" />
                                    {{ trans('pages.settings.relations.actions.delete') }}
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </TableCell>
                </TableRow>
            </TableBody>
            <TableFooter>
                <TableRow>
                    <TableCell colspan="7">
                        <Paginator :collection="(inertiaPage.props as any).relations"></Paginator>
                    </TableCell>
                </TableRow>
            </TableFooter>
        </Table>

        <!-- Create Dialog -->
        <Dialog v-model:open="createDialogOpen">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>{{ trans('pages.settings.relations.new') }}</DialogTitle>
                    <DialogDescription>
                        Create a new relationship between a guardian and a minor.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-4 py-4">
                    <Field>
                        <FieldLabel>{{ trans('pages.settings.relations.fields.guardian') }}</FieldLabel>
                        <div class="relative">
                            <Input
                                v-model="guardianSearch"
                                @input="searchGuardians"
                                :placeholder="trans('pages.auth.login.guardian_placeholder')"
                            />
                            <div v-if="guardiansFound.length > 0" class="absolute z-10 mt-1 w-full rounded-md border bg-popover shadow-md">
                                <div
                                    v-for="user in guardiansFound"
                                    :key="user.id"
                                    @click="selectGuardian(user)"
                                    class="cursor-pointer p-2 hover:bg-accent"
                                >
                                    {{ user.name }} ({{ user.email }})
                                </div>
                            </div>
                        </div>
                        <div v-if="form.errors.guardian_id" class="text-sm text-destructive">{{ form.errors.guardian_id }}</div>
                    </Field>

                    <Field>
                        <FieldLabel>{{ trans('pages.settings.relations.fields.minor') }}</FieldLabel>
                        <div class="relative">
                            <Input
                                v-model="minorSearch"
                                @input="searchMinors"
                                :placeholder="trans('pages.auth.login.input_placeholder')"
                            />
                            <div v-if="minorsFound.length > 0" class="absolute z-10 mt-1 w-full rounded-md border bg-popover shadow-md">
                                <div
                                    v-for="user in minorsFound"
                                    :key="user.id"
                                    @click="selectMinor(user)"
                                    class="cursor-pointer p-2 hover:bg-accent"
                                >
                                    {{ user.name }} ({{ user.email }})
                                </div>
                            </div>
                        </div>
                        <div v-if="form.errors.minor_id" class="text-sm text-destructive">{{ form.errors.minor_id }}</div>
                    </Field>

                    <Field>
                        <FieldLabel>{{ trans('pages.settings.relations.fields.relationship') }}</FieldLabel>
                        <Input v-model="form.relationship" :placeholder="trans('pages.auth.login.relationship_placeholder')" />
                        <div v-if="form.errors.relationship" class="text-sm text-destructive">{{ form.errors.relationship }}</div>
                    </Field>
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="createDialogOpen = false">{{ trans('pages.settings.locations.actions.cancel') }}</Button>
                    <Button @click="handleCreate" :disabled="form.processing || !form.guardian_id || !form.minor_id || !form.relationship">
                        {{ trans('pages.settings.locations.actions.create') }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation -->
        <DeleteConfirmationDialog
            v-model:open="deleteDialogOpen"
            :title="trans('pages.settings.relations.delete.title')"
            :description="trans('pages.settings.relations.delete.description')"
            @confirm="handleDelete"
        />
    </SidebarLayout>
</template>
