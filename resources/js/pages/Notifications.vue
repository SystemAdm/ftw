<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { Head, usePage, router, Link } from '@inertiajs/vue3';
import { BreadcrumbItemType } from '@/types';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { computed } from 'vue';
import { trans } from 'laravel-vue-i18n';
import { Bell, Check, Trash2, ExternalLink } from 'lucide-vue-next';
import { index as indexRoute, markAsRead as markAsReadRoute, markAllAsRead as markAllAsReadRoute, destroy as destroyRoute } from '@/routes/notifications/index';

interface Notification {
    id: string;
    type: string;
    data: {
        message: string;
        action_url?: string;
        minor_id?: number;
        minor_name?: string;
        type?: string;
    };
    read_at: string | null;
    created_at: string;
}

interface PaginatedNotifications {
    data: Notification[];
    links: any[];
    current_page: number;
    last_page: number;
    total: number;
}

const page = usePage<{ notifications: PaginatedNotifications }>();
const notifications = computed(() => page.props.notifications.data);

const breadcrumbs: BreadcrumbItemType[] = [
    {
        title: trans('pages.notifications.title'),
        href: indexRoute.url(),
    },
];

function markAsRead(id: string) {
    router.post(markAsReadRoute.url(id), {}, { preserveScroll: true });
}

function markAllAsRead() {
    router.post(markAllAsReadRoute.url(), {}, { preserveScroll: true });
}

function deleteNotification(id: string) {
    router.delete(destroyRoute.url(id), { preserveScroll: true });
}

function formatDate(dateString: string) {
    return new Date(dateString).toLocaleString();
}
</script>

<template>
    <SidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="trans('pages.notifications.title')" />

        <div class="mx-auto max-w-4xl space-y-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold tracking-tight">{{ trans('pages.notifications.title') }}</h1>
                <Button v-if="notifications.some(n => !n.read_at)" variant="outline" size="sm" @click="markAllAsRead">
                    <Check class="mr-2 h-4 w-4" />
                    {{ trans('pages.notifications.actions.mark_all_as_read') }}
                </Button>
            </div>

            <Card v-if="notifications.length === 0" class="flex flex-col items-center justify-center p-12 text-center">
                <Bell class="mb-4 h-12 w-12 text-muted-foreground opacity-20" />
                <h2 class="text-xl font-semibold">{{ trans('pages.notifications.none_title') }}</h2>
                <p class="text-muted-foreground">{{ trans('pages.notifications.none_description') }}</p>
            </Card>

            <div v-else class="space-y-4">
                <Card v-for="notification in notifications" :key="notification.id" class="relative p-4" :class="{ 'bg-muted/30': notification.read_at }">
                    <div class="flex items-start gap-4">
                        <div class="mt-1 rounded-full bg-primary/10 p-2 text-primary">
                            <Bell class="h-4 w-4" />
                        </div>
                        <div class="flex-1 space-y-1">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium" :class="{ 'text-muted-foreground': notification.read_at }">
                                    {{ notification.data.message }}
                                </p>
                                <span class="text-xs text-muted-foreground">{{ formatDate(notification.created_at) }}</span>
                            </div>

                            <div class="mt-2 flex items-center gap-2">
                                <Button
                                    v-if="notification.data.action_url"
                                    as-child
                                    variant="secondary"
                                    size="sm"
                                    class="h-8"
                                    @click="!notification.read_at && markAsRead(notification.id)"
                                >
                                    <Link :href="notification.data.action_url">
                                        <ExternalLink class="mr-2 h-3 w-3" />
                                        {{ trans('pages.notifications.actions.view') }}
                                    </Link>
                                </Button>

                                <Button v-if="!notification.read_at" variant="ghost" size="sm" class="h-8" @click="markAsRead(notification.id)">
                                    <Check class="mr-2 h-3 w-3" />
                                    {{ trans('pages.notifications.actions.mark_as_read') }}
                                </Button>

                                <Button variant="ghost" size="sm" class="h-8 text-destructive hover:bg-destructive/10 hover:text-destructive" @click="deleteNotification(notification.id)">
                                    <Trash2 class="mr-2 h-3 w-3" />
                                    {{ trans('pages.notifications.actions.delete') }}
                                </Button>
                            </div>
                        </div>
                        <div v-if="!notification.read_at" class="absolute top-4 right-4 h-2 w-2 rounded-full bg-primary"></div>
                    </div>
                </Card>

                <div v-if="page.props.notifications.last_page > 1" class="flex justify-center gap-2 pt-4">
                    <template v-for="link in page.props.notifications.links" :key="link.label">
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="!link.url || link.active"
                            @click="router.get(link.url)"
                        >
                            <span v-html="link.label"></span>
                        </Button>
                    </template>
                </div>
            </div>
        </div>
    </SidebarLayout>
</template>
