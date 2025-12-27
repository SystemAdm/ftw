<script setup lang="ts">
import DeleteConfirmationDialog from '@/components/custom/DeleteConfirmationDialog.vue';
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { usePage, router, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { trans } from 'laravel-vue-i18n';
import { Badge } from '@/components/ui/badge';
import { Calendar, MapPin, Users, Trash2, RotateCcw, Edit } from 'lucide-vue-next';
import { edit, index, destroy, restore, forceDestroy } from '@/routes/admin/events';
import { ref } from 'vue';

const page = usePage<PageProps>();
const event = (page.props as any).event;

const deleteDialogOpen = ref(false);
const forceDeleteDialogOpen = ref(false);

function formatDate(date: string) {
    return new Date(date).toLocaleString(page.props.i18n.locale, {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function getStatusColor(status: string, isDeleted: boolean) {
    if (isDeleted) return 'destructive';
    if (status === 'published') return 'default';
    if (status === 'draft') return 'secondary';
    if (status === 'cancelled') return 'destructive';
    return 'outline';
}

function handleRestore() {
    router.post(restore.url(event.id));
}

function handleDelete() {
    router.delete(destroy.url(event.id), {
        onFinish: () => (deleteDialogOpen.value = false),
    });
}

function handleForceDelete() {
    router.delete(forceDestroy.url(event.id), {
        onFinish: () => (forceDeleteDialogOpen.value = false),
    });
}
</script>

<template>
    <SidebarLayout>
        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <Link :href="index.url()" class="text-sm text-muted-foreground hover:underline">
                    &larr; {{ trans('pages.settings.events.actions.cancel') }}
                </Link>
                <h1 class="text-2xl font-bold">{{ event.title }}</h1>
                <Badge :variant="getStatusColor(event.status, !!event.deleted_at)">
                    {{ trans(`pages.settings.events.status.${event.status}`) }}
                </Badge>
                <Badge v-if="event.deleted_at" variant="destructive">
                    {{ trans('pages.settings.events.status.deleted') }}
                </Badge>
            </div>
            <div class="flex gap-2">
                <Button variant="outline" @click="router.visit(edit.url(event.id))">
                    <Edit class="mr-2 h-4 w-4" />
                    {{ trans('pages.settings.events.actions.edit') }}
                </Button>
                <template v-if="event.deleted_at">
                    <Button variant="outline" @click="handleRestore">
                        <RotateCcw class="mr-2 h-4 w-4" />
                        {{ trans('pages.settings.events.actions.restore') }}
                    </Button>
                    <Button variant="destructive" @click="forceDeleteDialogOpen = true">
                        <Trash2 class="mr-2 h-4 w-4" />
                        {{ trans('pages.settings.events.actions.force_delete') }}
                    </Button>
                </template>
                <Button v-else variant="destructive" @click="deleteDialogOpen = true">
                    <Trash2 class="mr-2 h-4 w-4" />
                    {{ trans('pages.settings.events.actions.delete') }}
                </Button>
            </div>
        </div>

        <DeleteConfirmationDialog
            v-model:open="deleteDialogOpen"
            :title="trans('pages.settings.events.delete.title')"
            :description="trans('pages.settings.events.delete.description')"
            @confirm="handleDelete"
        />

        <DeleteConfirmationDialog
            v-model:open="forceDeleteDialogOpen"
            :title="trans('pages.settings.events.force_delete.title')"
            :description="trans('pages.settings.events.force_delete.description')"
            @confirm="handleForceDelete"
        />

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-6">
                <div v-if="event.image_path" class="overflow-hidden rounded-lg border bg-card shadow-sm">
                    <img :src="`/storage/${event.image_path}`" class="h-64 w-full object-cover sm:h-96" alt="Event image" />
                </div>

                <div class="rounded-lg border bg-card p-6 shadow-sm">
                    <h2 class="mb-4 text-lg font-semibold">{{ trans('pages.settings.events.fields.description') }}</h2>
                    <div class="prose dark:prose-invert max-w-none whitespace-pre-wrap">
                        {{ event.description || event.excerpt || '—' }}
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-lg border bg-card p-6 shadow-sm">
                    <h2 class="mb-4 text-lg font-semibold">{{ trans('pages.settings.events.title') }} Info</h2>
                    <div class="space-y-4 text-sm">
                        <div class="flex items-start gap-3">
                            <Calendar class="mt-0.5 h-4 w-4 text-muted-foreground" />
                            <div>
                                <p class="font-medium">{{ trans('pages.settings.events.fields.event_start') }}</p>
                                <p class="text-muted-foreground">{{ formatDate(event.event_start) }}</p>
                                <template v-if="event.event_end">
                                    <p class="mt-1 font-medium">{{ trans('pages.settings.events.fields.event_end') }}</p>
                                    <p class="text-muted-foreground">{{ formatDate(event.event_end) }}</p>
                                </template>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <MapPin class="mt-0.5 h-4 w-4 text-muted-foreground" />
                            <div>
                                <p class="font-medium">{{ trans('pages.settings.events.fields.location') }}</p>
                                <p class="text-muted-foreground">{{ event.location?.name ?? '—' }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <Users class="mt-0.5 h-4 w-4 text-muted-foreground" />
                            <div>
                                <p class="font-medium">{{ trans('pages.settings.events.fields.seats') }}</p>
                                <p class="text-muted-foreground">{{ event.number_of_seats ?? '∞' }}</p>
                            </div>
                        </div>

                        <div v-if="event.signup_needed || event.signup_start || event.signup_end" class="mt-4 rounded-md bg-muted/50 p-3">
                            <p class="font-bold text-xs uppercase tracking-wider text-muted-foreground mb-2">
                                {{ event.signup_needed ? trans('pages.settings.events.fields.signup_needed') : trans('pages.settings.events.fields.signup_times') }}
                            </p>
                            <div class="space-y-2 text-xs">
                                <div v-if="event.signup_start">
                                    <p class="font-medium">{{ trans('pages.settings.events.fields.signup_start') }}</p>
                                    <p class="text-muted-foreground">{{ formatDate(event.signup_start) }}</p>
                                </div>
                                <div v-if="event.signup_end">
                                    <p class="font-medium">{{ trans('pages.settings.events.fields.signup_end') }}</p>
                                    <p class="text-muted-foreground">{{ formatDate(event.signup_end) }}</p>
                                </div>
                            </div>
                        </div>

                        <div v-if="event.age_min || event.age_max" class="mt-4 flex gap-4 border-t pt-4">
                            <div v-if="event.age_min">
                                <p class="text-xs font-medium text-muted-foreground">{{ trans('pages.settings.events.fields.age_min') }}</p>
                                <p class="text-lg font-bold">{{ event.age_min }}</p>
                            </div>
                            <div v-if="event.age_max">
                                <p class="text-xs font-medium text-muted-foreground">{{ trans('pages.settings.events.fields.age_max') }}</p>
                                <p class="text-lg font-bold">{{ event.age_max }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </SidebarLayout>
</template>
