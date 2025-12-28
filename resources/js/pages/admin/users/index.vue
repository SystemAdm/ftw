<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { BreadcrumbItemType, AppPageProps } from '@/types';
import { Table, TableBody, TableCell, TableFooter, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';
import Paginator from '@/components/custom/Paginator.vue';
import { trans } from 'laravel-vue-i18n';
import { CheckCircle2, XCircle, ShieldCheck, ShieldAlert, MoreHorizontal, Eye, Edit, Trash2, Ban, UserCheck, Key } from 'lucide-vue-next';
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

interface PageProps extends AppPageProps {
    users: {
        data: any[];
    };
    i18n: {
        locale: string;
    };
}

const inertiaPage = usePage<PageProps>();

const banDialogOpen = ref(false);
const selectedUser = ref<any>(null);

const banForm = useForm({
    reason: '',
    banned_to: '',
});

const breadcrumbs = computed<BreadcrumbItemType[]>(() => [
    {
        title: trans('pages.settings.users.title'),
        href: '/admin/users',
    },
]);

function formatDate(date: string) {
    return new Date(date).toLocaleString(inertiaPage.props.i18n.locale, {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function handleVerify(user: any) {
    router.post(`/admin/users/${user.id}/verify`);
}

function handleResetPassword(user: any) {
    router.post(`/admin/users/${user.id}/reset-password`);
}

function openBanDialog(user: any) {
    selectedUser.value = user;
    banForm.reason = '';
    banForm.banned_to = '';
    banDialogOpen.value = true;
}

function handleBan() {
    banForm.post(`/admin/users/${selectedUser.value.id}/ban`, {
        onSuccess: () => {
            banDialogOpen.value = false;
            selectedUser.value = null;
        },
    });
}

function handleUnban(user: any) {
    router.post(`/admin/users/${user.id}/unban`);
}
</script>

<template>
    <SidebarLayout :breadcrumbs="breadcrumbs">
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-xl font-semibold">{{ trans('pages.settings.users.title') }}</h1>
        </div>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>{{ trans('pages.settings.users.fields.name') }}</TableHead>
                    <TableHead>{{ trans('pages.settings.users.fields.email') }}</TableHead>
                    <TableHead class="w-24 text-center">{{ trans('pages.settings.users.fields.status') }}</TableHead>
                    <TableHead class="w-12"></TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow
                    v-for="user in inertiaPage.props.users.data"
                    :key="user.id"
                    :class="{ 'bg-red-50 dark:bg-red-950/20 text-red-900 dark:text-red-200': user.is_banned }"
                >
                    <TableCell class="font-medium">{{ user.name }}</TableCell>
                    <TableCell>{{ user.email }}</TableCell>
                    <TableCell>
                        <div class="flex items-center justify-center gap-2">
                            <TooltipProvider>
                                <!-- Email Verification -->
                                <Tooltip>
                                    <TooltipTrigger>
                                        <CheckCircle2 v-if="user.email_verified_at" class="h-4 w-4 text-green-600" />
                                        <XCircle v-else class="h-4 w-4 text-gray-400" />
                                    </TooltipTrigger>
                                    <TooltipContent v-if="user.email_verified_at">
                                        {{ trans('pages.settings.users.status.email_verified') }}
                                        <div class="text-xs text-muted-foreground">{{ formatDate(user.email_verified_at) }}</div>
                                    </TooltipContent>
                                    <TooltipContent v-else>
                                        {{ trans('pages.settings.users.status.email_unverified') }}
                                    </TooltipContent>
                                </Tooltip>

                                <!-- Admin Verification -->
                                <Tooltip>
                                    <TooltipTrigger>
                                        <ShieldCheck v-if="user.verified_at" class="h-4 w-4 text-blue-600" />
                                        <ShieldAlert v-else class="h-4 w-4 text-gray-400" />
                                    </TooltipTrigger>
                                    <TooltipContent>
                                        <div v-if="user.verified_at" class="space-y-1">
                                            <p class="font-medium">{{ trans('pages.settings.users.status.verified') }}</p>
                                            <p class="text-xs">{{ trans('pages.settings.users.status.verified_at') }}: {{ formatDate(user.verified_at) }}</p>
                                            <p v-if="user.verifier" class="text-xs">{{ trans('pages.settings.users.status.verified_by') }}: {{ user.verifier.name }}</p>
                                        </div>
                                        <div v-else>
                                            {{ trans('pages.settings.users.status.unverified') }}
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
                                <DropdownMenuItem @click="router.visit(`/admin/users/${user.id}`)">
                                    <Eye class="mr-2 h-4 w-4" />
                                    {{ trans('pages.settings.events.actions.view') }}
                                </DropdownMenuItem>
                                <DropdownMenuItem @click="router.visit(`/admin/users/${user.id}/edit`)">
                                    <Edit class="mr-2 h-4 w-4" />
                                    {{ trans('pages.settings.events.actions.edit') }}
                                </DropdownMenuItem>

                                <DropdownMenuItem v-if="!user.verified_at" @click="handleVerify(user)">
                                    <UserCheck class="mr-2 h-4 w-4" />
                                    {{ trans('pages.settings.users.actions.verify') }}
                                </DropdownMenuItem>

                                <DropdownMenuItem @click="handleResetPassword(user)">
                                    <Key class="mr-2 h-4 w-4" />
                                    {{ trans('pages.settings.users.actions.reset_password') }}
                                </DropdownMenuItem>

                                <DropdownMenuItem v-if="!user.is_banned" class="text-destructive focus:text-destructive" @click="openBanDialog(user)">
                                    <Ban class="mr-2 h-4 w-4" />
                                    {{ trans('pages.settings.users.actions.ban') }}
                                </DropdownMenuItem>
                                <DropdownMenuItem v-else @click="handleUnban(user)">
                                    <ShieldCheck class="mr-2 h-4 w-4" />
                                    {{ trans('pages.settings.users.actions.unban') }}
                                </DropdownMenuItem>

                                <DropdownMenuItem class="text-destructive focus:text-destructive" @click="router.delete(`/admin/users/${user.id}`)">
                                    <Trash2 class="mr-2 h-4 w-4" />
                                    {{ trans('pages.settings.events.actions.delete') }}
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </TableCell>
                </TableRow>
            </TableBody>
            <TableFooter>
                <TableRow>
                    <TableCell colspan="4">
                        <Paginator :collection="(inertiaPage.props as any).users"></Paginator>
                    </TableCell>
                </TableRow>
            </TableFooter>
        </Table>

        <Dialog v-model:open="banDialogOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{{ trans('pages.settings.users.ban.title') }}</DialogTitle>
                    <DialogDescription>
                        {{ selectedUser?.name }} ({{ selectedUser?.email }})
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-4 py-4">
                    <Field>
                        <FieldLabel>{{ trans('pages.settings.users.ban.reason') }}</FieldLabel>
                        <Input v-model="banForm.reason" :placeholder="trans('pages.settings.users.ban.reason')" />
                        <div v-if="banForm.errors.reason" class="text-sm text-destructive">{{ banForm.errors.reason }}</div>
                    </Field>

                    <Field>
                        <FieldLabel>{{ trans('pages.settings.users.ban.banned_to') }}</FieldLabel>
                        <Input v-model="banForm.banned_to" type="datetime-local" />
                        <div v-if="banForm.errors.banned_to" class="text-sm text-destructive">{{ banForm.errors.banned_to }}</div>
                    </Field>
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="banDialogOpen = false">{{ trans('pages.settings.events.actions.cancel') }}</Button>
                    <Button variant="destructive" @click="handleBan" :disabled="banForm.processing">
                        {{ trans('pages.settings.users.ban.confirm') }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </SidebarLayout>
</template>
