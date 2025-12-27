<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { Table, TableBody, TableCell, TableFooter, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import Paginator from '@/components/custom/Paginator.vue';
import { trans } from 'laravel-vue-i18n';
import { CheckCircle2, XCircle, ShieldCheck, ShieldAlert } from 'lucide-vue-next';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';

const inertiaPage = usePage<PageProps>();

function formatDate(date: string) {
    return new Date(date).toLocaleString(inertiaPage.props.i18n.locale, {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    });
}
</script>

<template>
    <SidebarLayout>
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-xl font-semibold">{{ trans('pages.settings.users.title') }}</h1>
        </div>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>{{ trans('pages.settings.users.fields.name') }}</TableHead>
                    <TableHead>{{ trans('pages.settings.users.fields.email') }}</TableHead>
                    <TableHead class="w-24 text-center">{{ trans('pages.settings.users.fields.status') }}</TableHead>
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
                                    <TooltipContent>
                                        {{ user.email_verified_at ? trans('pages.settings.users.status.email_verified') : trans('pages.settings.users.status.email_unverified') }}
                                        <div v-if="user.email_verified_at" class="text-xs text-muted-foreground">{{ formatDate(user.email_verified_at) }}</div>
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
                </TableRow>
            </TableBody>
            <TableFooter>
                <TableRow>
                    <TableCell colspan="3">
                        <Paginator :collection="(inertiaPage.props as any).users"></Paginator>
                    </TableCell>
                </TableRow>
            </TableFooter>
        </Table>
    </SidebarLayout>
</template>
