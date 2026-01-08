<script setup lang="ts">
import Paginator from '@/components/custom/Paginator.vue';
import UpcomingWeekdays from '@/components/custom/UpcomingWeekdays.vue';
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { Badge } from '@/components/ui/badge';
import { index as eventsIndex, show } from '@/routes/events';
import { BreadcrumbItem } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { Calendar, CheckCircle, MapPin, Users } from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage<any>();

defineProps<{
    events: any;
    days: any[];
    week: number;
}>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: trans('ui.navigation.home'),
        href: '/',
    },
    {
        title: trans('pages.events.title'),
        href: eventsIndex.url(),
    },
]);

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
    <Head :title="trans('pages.events.title')" />

    <SidebarLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="mb-12">
                <UpcomingWeekdays :days="days" :week="week" :base-url="eventsIndex.url()" />
            </div>

            <div class="mb-8">
                <h1 class="text-3xl font-extrabold tracking-tight text-foreground sm:text-4xl">
                    {{ trans('pages.events.title') }}
                </h1>
                <p class="mt-4 text-xl text-muted-foreground">
                    {{ trans('pages.events.subtitle') }}
                </p>
            </div>

            <div v-if="events.data.length > 0" class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="event in events.data"
                    :key="event.id"
                    :href="show.url(event.id)"
                    class="group flex flex-col overflow-hidden rounded-lg border bg-card shadow-sm transition-all hover:shadow-md"
                >
                    <div class="aspect-video w-full overflow-hidden bg-muted">
                        <img
                            v-if="event.image_path"
                            :src="event.image_path.startsWith('Http') ? event.image_path : `/storage/${event.image_path}`"
                            class="h-full w-full object-cover transition-transform group-hover:scale-105"
                            alt="Event image"
                        />
                        <div v-else class="flex h-full w-full items-center justify-center text-muted-foreground">
                            <Calendar class="h-12 w-12 opacity-20" />
                        </div>
                    </div>
                    <div class="flex flex-1 flex-col p-6">
                        <div class="mb-2 flex items-center justify-between">
                            <div class="flex items-center gap-2 text-xs font-medium text-muted-foreground">
                                <Calendar class="h-3 w-3" />
                                {{ formatDate(event.event_start) }}
                            </div>
                            <Badge v-if="event.is_signed_up" variant="default" class="bg-green-600 hover:bg-green-700">
                                <CheckCircle class="mr-1 h-3 w-3" />
                                {{ trans('pages.events.signup.already_signed_up') }}
                            </Badge>
                        </div>
                        <h3 class="mb-2 text-xl font-bold group-hover:text-primary">
                            {{ event.title }}
                        </h3>
                        <p class="mb-4 line-clamp-2 text-sm text-muted-foreground">
                            {{ event.excerpt || event.description }}
                        </p>
                        <div class="mt-auto flex items-center gap-4 text-xs text-muted-foreground">
                            <div v-if="event.location" class="flex items-center gap-1">
                                <MapPin class="h-3 w-3" />
                                {{ event.location.name }}
                            </div>
                            <div v-if="event.number_of_seats !== null" class="flex items-center gap-1">
                                <Users class="h-3 w-3" />
                                {{ event.number_of_seats === -1 ? '∞' : event.number_of_seats }}
                            </div>
                        </div>
                    </div>
                </Link>
            </div>

            <div v-else class="rounded-lg border border-dashed p-12 text-center">
                <Calendar class="mx-auto h-12 w-12 text-muted-foreground opacity-20" />
                <h3 class="mt-2 text-sm font-medium text-foreground">{{ trans('pages.events.none_found') }}</h3>
                <p class="mt-1 text-sm text-muted-foreground">{{ trans('pages.events.none_found_description') }}</p>
            </div>

            <div v-if="events.last_page > 1" class="mt-12">
                <Paginator :collection="events" />
            </div>
        </div>
    </SidebarLayout>
</template>
