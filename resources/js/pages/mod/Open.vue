<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { BreadcrumbItemType } from '@/types';
import { Head, usePage, useForm } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import Paginator from '@/components/custom/Paginator.vue';
import { trans } from 'laravel-vue-i18n';
import { computed, ref, watch, onMounted, nextTick } from 'vue';
import { index as modOpenRoute, store as modOpenStore, destroy as modOpenDestroy } from '@/routes/mod/open/index';
import { search as modUsersSearch } from '@/routes/mod/users/index';
import axios from 'axios';
import DeleteConfirmationDialog from '@/components/custom/DeleteConfirmationDialog.vue';
import UserHoverCard from '@/components/custom/UserHoverCard.vue';

const breadcrumbs = computed<BreadcrumbItemType[]>(() => [
    {
        title: trans('ui.navigation.home'),
        href: '/',
    },
    {
        title: trans('ui.navigation.moderator'),
        href: modOpenRoute.url(),
    },
]);

const page = usePage<any>();
const inside = computed(() => page.props.inside);
const history = computed(() => page.props.history);

const searchForm = useForm({
    user_id: '' as string | number,
});

const searchQuery = ref('');
const searchResults = ref<any[]>([]);
const isSearching = ref(false);
const isSelecting = ref(false);
const searchInput = ref<InstanceType<typeof Input> | null>(null);

onMounted(() => {
    searchInput.value?.focus();
});

const showConfirmCheckOut = ref(false);
const userToCheckOut = ref<number | null>(null);

watch(searchQuery, async (query) => {
    if (isSelecting.value) {
        isSelecting.value = false;
        return;
    }

    if (query.length < 2) {
        searchResults.value = [];
        return;
    }

    isSearching.value = true;
    try {
        const response = await axios.get(modUsersSearch.url({ query: { q: query } }));
        searchResults.value = response.data.data;
    } catch (error) {
        console.error('Error searching users:', error);
    } finally {
        isSearching.value = false;
    }
});

function selectUser(user: any) {
    isSelecting.value = true;
    searchForm.user_id = user.id;
    searchQuery.value = user.name;
    searchResults.value = [];
}

function submitCheckIn() {
    searchForm.post(modOpenStore.url(), {
        onSuccess: () => {
            searchForm.reset();
            searchQuery.value = '';
            nextTick(() => {
                searchInput.value?.focus();
            });
        },
    });
}

function confirmCheckOut(userId: number) {
    userToCheckOut.value = userId;
    showConfirmCheckOut.value = true;
}

function submitCheckOut() {
    if (userToCheckOut.value) {
        useForm({}).delete(modOpenDestroy.url(userToCheckOut.value), {
            onFinish: () => {
                showConfirmCheckOut.value = false;
                userToCheckOut.value = null;
            },
        });
    }
}

function formatDate(date: string) {
    return new Date(date).toLocaleString(page.props.i18n.locale, {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    });
}
</script>

<template>
    <SidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="trans('pages.ui.navigation.mod_menu')" />

        <div class="space-y-6">
            <h1 class="text-2xl font-bold tracking-tight">{{ trans('pages.mod.open.title') }}</h1>

            <Card>
                <CardHeader>
                    <CardTitle>{{ trans('pages.mod.open.manual_check_in') }}</CardTitle>
                    <CardDescription>{{ trans('pages.mod.open.manual_check_in_description') }}</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="relative w-full max-w-sm">
                        <Input
                            v-model="searchQuery"
                            ref="searchInput"
                            type="text"
                            :placeholder="trans('pages.mod.open.search_user_placeholder')"
                            autocomplete="off"
                        />
                        <div v-if="searchResults.length > 0" class="absolute z-10 mt-1 w-full rounded-md border bg-popover text-popover-foreground shadow-md outline-none animate-in fade-in-0 zoom-in-95">
                            <ul class="py-1">
                                <li
                                    v-for="result in searchResults"
                                    :key="result.id"
                                    @click="selectUser(result)"
                                    class="relative flex cursor-default select-none items-center rounded-sm px-2 py-1.5 text-sm outline-none transition-colors hover:bg-accent hover:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50"
                                >
                                    {{ result.name }} ({{ result.email }})
                                </li>
                            </ul>
                        </div>
                    </div>
                    <Button :disabled="!searchForm.user_id || searchForm.processing" @click="submitCheckIn" class="mt-4">
                        {{ searchForm.processing ? trans('pages.mod.open.checking_in') : trans('pages.mod.open.check_in') }}
                    </Button>
                </CardContent>
            </Card>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>{{ trans('pages.mod.open.inside_title') }} ({{ inside.length }})</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>{{ trans('pages.settings.users.fields.name') }}</TableHead>
                                    <TableHead>{{ trans('pages.mod.open.entered_at') }}</TableHead>
                                    <TableHead></TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="entry in inside" :key="entry.id">
                                    <TableCell>
                                        <UserHoverCard :user="entry.user" />
                                    </TableCell>
                                    <TableCell>{{ formatDate(entry.entered_at) }}</TableCell>
                                    <TableCell class="text-right">
                                        <Button variant="ghost" size="sm" @click="confirmCheckOut(entry.user_id)">
                                            {{ trans('pages.mod.open.check_out') }}
                                        </Button>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="inside.length === 0">
                                    <TableCell colspan="3" class="text-center text-muted-foreground py-4">
                                        {{ trans('pages.mod.open.no_one_inside') }}
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{{ trans('pages.mod.open.history_title') }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>{{ trans('pages.settings.users.fields.name') }}</TableHead>
                                    <TableHead>{{ trans('pages.mod.open.action') }}</TableHead>
                                    <TableHead>{{ trans('pages.mod.open.time') }}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="log in history.data" :key="log.id">
                                    <TableCell>
                                        <UserHoverCard :user="log.user" />
                                    </TableCell>
                                    <TableCell>
                                        <span :class="log.action === 'in' ? 'text-green-600' : 'text-red-600'">
                                            {{ trans(`pages.mod.open.actions.${log.action}`) }}
                                        </span>
                                    </TableCell>
                                    <TableCell>{{ formatDate(log.created_at) }}</TableCell>
                                </TableRow>
                                <TableRow v-if="history.data.length === 0">
                                    <TableCell colspan="3" class="text-center text-muted-foreground py-4">
                                        {{ trans('pages.mod.open.no_history') }}
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                        <div class="mt-4">
                            <Paginator :collection="history" />
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>

        <DeleteConfirmationDialog
            v-model:open="showConfirmCheckOut"
            :title="trans('pages.mod.open.confirm_check_out')"
            :description="trans('pages.mod.open.confirm_check_out')"
            @confirm="submitCheckOut"
        >
            {{ trans('pages.mod.open.check_out') }}
        </DeleteConfirmationDialog>
    </SidebarLayout>
</template>
