<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { BreadcrumbItemType, AppPageProps } from '@/types';
import { Table, TableBody, TableCell, TableFooter, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';
import Paginator from '@/components/custom/Paginator.vue';
import { trans } from 'laravel-vue-i18n';
import { MoreHorizontal, Plus, Trash2, UserPlus, Star, UserMinus, Pencil } from 'lucide-vue-next';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
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
import { Checkbox } from '@/components/ui/checkbox';
import axios from 'axios';
import DeleteConfirmationDialog from '@/components/custom/DeleteConfirmationDialog.vue';
import { Badge } from '@/components/ui/badge';

interface PageProps extends AppPageProps {
    phoneNumbers: {
        data: any[];
    };
    i18n: {
        locale: string;
    };
}

const inertiaPage = usePage<PageProps>();

const createDialogOpen = ref(false);
const editDialogOpen = ref(false);
const associateDialogOpen = ref(false);
const deleteDialogOpen = ref(false);

const selectedPhone = ref<any>(null);
const userSearch = ref('');
const usersFound = ref<any[]>([]);

const createForm = useForm({
    e164: '',
    raw: '',
    user_id: null as number | null,
    primary: false,
});

const editForm = useForm({
    e164: '',
    raw: '',
});

const associateForm = useForm({
    user_id: null as number | null,
    primary: false,
});

const breadcrumbs = computed<BreadcrumbItemType[]>(() => [
    {
        title: trans('ui.navigation.home'),
        href: '/',
    },
    {
        title: trans('ui.navigation.admin'),
        href: '/admin',
    },
    {
        title: trans('pages.settings.phone.title'),
        href: '/admin/phone',
    },
]);


function openCreateDialog() {
    createForm.reset();
    userSearch.value = '';
    usersFound.value = [];
    createDialogOpen.value = true;
}

function openEditDialog(phone: any) {
    selectedPhone.value = phone;
    editForm.e164 = phone.e164;
    editForm.raw = phone.raw;
    editDialogOpen.value = true;
}

function openAssociateDialog(phone: any) {
    selectedPhone.value = phone;
    associateForm.reset();
    userSearch.value = '';
    usersFound.value = [];
    associateDialogOpen.value = true;
}

function openDeleteDialog(phone: any) {
    selectedPhone.value = phone;
    deleteDialogOpen.value = true;
}

async function searchUsers() {
    if (userSearch.value.length < 2) {
        usersFound.value = [];
        return;
    }
    const response = await axios.get('/admin/phone/search-users', { params: { q: userSearch.value } });
    usersFound.value = response.data.data;
}

function selectUser(user: any, formType: 'create' | 'associate') {
    if (formType === 'create') {
        createForm.user_id = user.id;
    } else {
        associateForm.user_id = user.id;
    }
    userSearch.value = `${user.name} (${user.email})`;
    usersFound.value = [];
}

function handleCreate() {
    createForm.post('/admin/phone', {
        onSuccess: () => {
            createDialogOpen.value = false;
        },
    });
}

function handleUpdate() {
    editForm.put(`/admin/phone/${selectedPhone.value.id}`, {
        onSuccess: () => {
            editDialogOpen.value = false;
        },
    });
}

function handleAssociate() {
    associateForm.post(`/admin/phone/${selectedPhone.value.id}/users`, {
        onSuccess: () => {
            associateDialogOpen.value = false;
        },
    });
}

function handleDisassociate(phone: any, user: any) {
    router.delete(`/admin/phone/${phone.id}/users/${user.id}`);
}

function handleTogglePrimary(phone: any, user: any) {
    router.post(`/admin/phone/${phone.id}/users/${user.id}/primary`);
}

function handleDelete() {
    if (!selectedPhone.value) return;
    router.delete(`/admin/phone/${selectedPhone.value.id}`, {
        onSuccess: () => {
            deleteDialogOpen.value = false;
            selectedPhone.value = null;
        },
    });
}
</script>

<template>
    <SidebarLayout :breadcrumbs="breadcrumbs">
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-xl font-semibold">{{ trans('pages.settings.phone.title') }}</h1>
            <Button @click="openCreateDialog">
                <Plus class="mr-2 h-4 w-4" />
                {{ trans('pages.settings.phone.new') }}
            </Button>
        </div>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>{{ trans('pages.settings.phone.fields.e164') }}</TableHead>
                    <TableHead>{{ trans('pages.settings.phone.fields.raw') }}</TableHead>
                    <TableHead>{{ trans('pages.settings.phone.fields.users') }}</TableHead>
                    <TableHead class="w-12"></TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="phone in inertiaPage.props.phoneNumbers.data" :key="phone.id">
                    <TableCell class="font-medium">{{ phone.e164 }}</TableCell>
                    <TableCell>{{ phone.raw }}</TableCell>
                    <TableCell>
                        <div class="flex flex-col gap-1">
                            <div v-for="user in phone.users" :key="user.id" class="flex items-center gap-2 text-sm">
                                <Badge variant="outline" class="flex items-center gap-1 py-0 px-1">
                                    <span :class="{ 'font-bold': user.pivot.primary }">{{ user.name }}</span>
                                    <TooltipProvider>
                                        <Tooltip>
                                            <TooltipTrigger as-child>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    class="h-4 w-4"
                                                    @click="handleTogglePrimary(phone, user)"
                                                >
                                                    <Star class="h-3 w-3" :class="user.pivot.primary ? 'fill-yellow-400 text-yellow-400' : 'text-muted-foreground'" />
                                                </Button>
                                            </TooltipTrigger>
                                            <TooltipContent>
                                                {{ user.pivot.primary ? 'Primary' : 'Set as primary' }}
                                            </TooltipContent>
                                        </Tooltip>
                                    </TooltipProvider>
                                    <TooltipProvider>
                                        <Tooltip>
                                            <TooltipTrigger as-child>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    class="h-4 w-4 text-destructive"
                                                    @click="handleDisassociate(phone, user)"
                                                >
                                                    <UserMinus class="h-3 w-3" />
                                                </Button>
                                            </TooltipTrigger>
                                            <TooltipContent>
                                                Disassociate
                                            </TooltipContent>
                                        </Tooltip>
                                    </TooltipProvider>
                                </Badge>
                            </div>
                            <Button variant="ghost" size="xs" class="h-6 w-fit text-[10px]" @click="openAssociateDialog(phone)">
                                <UserPlus class="mr-1 h-3 w-3" />
                                Associate User
                            </Button>
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
                                <DropdownMenuItem @click="openEditDialog(phone)">
                                    <Pencil class="mr-2 h-4 w-4" />
                                    {{ trans('pages.settings.locations.actions.edit') }}
                                </DropdownMenuItem>
                                <DropdownMenuItem class="text-destructive focus:text-destructive" @click="openDeleteDialog(phone)">
                                    <Trash2 class="mr-2 h-4 w-4" />
                                    {{ trans('pages.settings.locations.actions.delete') }}
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </TableCell>
                </TableRow>
            </TableBody>
            <TableFooter>
                <TableRow>
                    <TableCell colspan="4">
                        <Paginator :collection="(inertiaPage.props as any).phoneNumbers"></Paginator>
                    </TableCell>
                </TableRow>
            </TableFooter>
        </Table>

        <!-- Create Dialog -->
        <Dialog v-model:open="createDialogOpen">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>{{ trans('pages.settings.phone.new') }}</DialogTitle>
                    <DialogDescription>
                        Create a new phone number.
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-4 py-4">
                    <Field>
                        <FieldLabel>{{ trans('pages.settings.phone.fields.e164') }}</FieldLabel>
                        <Input v-model="createForm.e164" placeholder="+4712345678" />
                        <div v-if="createForm.errors.e164" class="text-sm text-destructive">{{ createForm.errors.e164 }}</div>
                    </Field>

                    <Field>
                        <FieldLabel>{{ trans('pages.settings.phone.fields.raw') }}</FieldLabel>
                        <Input v-model="createForm.raw" placeholder="123 45 678" />
                        <div v-if="createForm.errors.raw" class="text-sm text-destructive">{{ createForm.errors.raw }}</div>
                    </Field>

                    <div class="space-y-4 rounded-lg border p-3">
                        <h4 class="text-sm font-medium">Initial Association (Optional)</h4>
                        <Field>
                            <FieldLabel>{{ trans('pages.settings.users.title') }}</FieldLabel>
                            <div class="relative">
                                <Input
                                    v-model="userSearch"
                                    @input="searchUsers"
                                    placeholder="Search users..."
                                />
                                <div v-if="usersFound.length > 0" class="absolute z-10 mt-1 w-full rounded-md border bg-popover shadow-md">
                                    <div
                                        v-for="user in usersFound"
                                        :key="user.id"
                                        @click="selectUser(user, 'create')"
                                        class="cursor-pointer p-2 hover:bg-accent"
                                    >
                                        {{ user.name }} ({{ user.email }})
                                    </div>
                                </div>
                            </div>
                        </Field>

                        <div class="flex items-center space-x-2">
                            <Checkbox id="primary" :checked="createForm.primary" @update:checked="(v) => (createForm.primary = v)" />
                            <label for="primary" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                Set as primary
                            </label>
                        </div>
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="createDialogOpen = false">{{ trans('pages.settings.locations.actions.cancel') }}</Button>
                    <Button @click="handleCreate" :disabled="createForm.processing">
                        {{ trans('pages.settings.locations.actions.create') }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Edit Dialog -->
        <Dialog v-model:open="editDialogOpen">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>{{ trans('pages.settings.locations.actions.edit') }} Phone Number</DialogTitle>
                </DialogHeader>

                <div class="grid gap-4 py-4">
                    <Field>
                        <FieldLabel>{{ trans('pages.settings.phone.fields.e164') }}</FieldLabel>
                        <Input v-model="editForm.e164" />
                        <div v-if="editForm.errors.e164" class="text-sm text-destructive">{{ editForm.errors.e164 }}</div>
                    </Field>

                    <Field>
                        <FieldLabel>{{ trans('pages.settings.phone.fields.raw') }}</FieldLabel>
                        <Input v-model="editForm.raw" />
                        <div v-if="editForm.errors.raw" class="text-sm text-destructive">{{ editForm.errors.raw }}</div>
                    </Field>
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="editDialogOpen = false">{{ trans('pages.settings.locations.actions.cancel') }}</Button>
                    <Button @click="handleUpdate" :disabled="editForm.processing">
                        {{ trans('pages.settings.locations.actions.update') }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Associate Dialog -->
        <Dialog v-model:open="associateDialogOpen">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Associate User</DialogTitle>
                    <DialogDescription>
                        Associate a user with {{ selectedPhone?.e164 }}
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-4 py-4">
                    <Field>
                        <FieldLabel>{{ trans('pages.settings.users.title') }}</FieldLabel>
                        <div class="relative">
                            <Input
                                v-model="userSearch"
                                @input="searchUsers"
                                placeholder="Search users..."
                            />
                            <div v-if="usersFound.length > 0" class="absolute z-10 mt-1 w-full rounded-md border bg-popover shadow-md">
                                <div
                                    v-for="user in usersFound"
                                    :key="user.id"
                                    @click="selectUser(user, 'associate')"
                                    class="cursor-pointer p-2 hover:bg-accent"
                                >
                                    {{ user.name }} ({{ user.email }})
                                </div>
                            </div>
                        </div>
                        <div v-if="associateForm.errors.user_id" class="text-sm text-destructive">{{ associateForm.errors.user_id }}</div>
                    </Field>

                    <div class="flex items-center space-x-2">
                        <Checkbox id="assoc-primary" :checked="associateForm.primary" @update:checked="(v) => (associateForm.primary = v)" />
                        <label for="assoc-primary" class="text-sm font-medium leading-none">
                            Set as primary
                        </label>
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="associateDialogOpen = false">{{ trans('pages.settings.locations.actions.cancel') }}</Button>
                    <Button @click="handleAssociate" :disabled="associateForm.processing || !associateForm.user_id">
                        Associate
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation -->
        <DeleteConfirmationDialog
            v-model:open="deleteDialogOpen"
            :title="trans('pages.settings.locations.delete.title', { name: selectedPhone?.e164 ?? '' })"
            :description="trans('pages.settings.locations.delete.description')"
            @confirm="handleDelete"
        />
    </SidebarLayout>
</template>
