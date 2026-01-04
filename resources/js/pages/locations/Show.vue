<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Card } from '@/components/ui/card';
import { trans } from 'laravel-vue-i18n';
import { index } from '@/routes/locations';
import TeamHoverCard from '@/components/custom/TeamHoverCard.vue';
import { show as showTeam } from '@/routes/teams/index';

type Team = {
    id: number;
    name: string;
    slug?: string | null;
    description?: string | null;
    logo?: string | null;
    created_at?: string | null;
} | null;

type Upcoming = {
  id: number;
  date: string;
  label: string;
  weekday: number;
  weekday_label: string;
  name?: string | null;
  description?: string | null;
  start_time?: string | null;
  end_time?: string | null;
  team?: Team;
};

type LocationDetails = {
  id: number;
  name: string;
  description?: string | null;
  postal?: string | null;
  street_address?: string | null;
  street_number?: string | null;
  latitude?: number | null;
  longitude?: number | null;
  google_maps_url?: string | null;
  link?: string | null;
};

const page = usePage<{ location: LocationDetails; upcoming: Upcoming[] }>();
const location = page.props.location;
const upcoming = page.props.upcoming ?? [];

function googleMapEmbedUrl(loc: LocationDetails): string | null {
  if (loc.latitude && loc.longitude) {
    const lat = encodeURIComponent(String(loc.latitude));
    const lng = encodeURIComponent(String(loc.longitude));
    return `https://www.google.com/maps?q=${lat},${lng}&z=14&output=embed`;
  }
  if (loc.google_maps_url) {
    // Not perfect, but allows embedding a provided URL
    return loc.google_maps_url;
  }
  return null;
}
</script>

<template>
  <SidebarLayout>
    <Head :title="location.name" />
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight">{{ location.name }}</h1>
        <Link :href="index.url()" class="text-primary hover:underline">{{ trans('pages.locations.back_to_all') }}</Link>
      </div>

      <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-4">
          <Card class="p-4">
            <h2 class="text-lg font-semibold">{{ trans('pages.locations.details') }}</h2>
            <div class="mt-2 text-sm text-muted-foreground space-y-1">
              <div v-if="location.description" class="text-foreground">{{ location.description }}</div>
              <div v-if="location.postal">{{ location.postal }}</div>
              <div v-if="location.street_address">{{ location.street_address }} <span v-if="location.street_number">{{ location.street_number }}</span></div>
              <div v-if="location.link"><a :href="location.link" target="_blank" class="text-primary hover:underline">{{ trans('pages.locations.website') }}</a></div>
            </div>
          </Card>

          <Card class="p-4">
            <h2 class="text-lg font-semibold">{{ trans('pages.locations.upcoming_weekdays') }}</h2>
            <div v-if="upcoming.length === 0" class="mt-2 text-sm text-muted-foreground">{{ trans('pages.locations.no_upcoming') }}</div>
            <ul v-else class="mt-2 divide-y">
              <li v-for="day in upcoming" :key="day.id" class="py-3">
                <div class="flex items-center justify-between">
                  <div>
                    <div class="text-sm text-muted-foreground">{{ day.weekday_label }} • {{ day.label }}</div>
                    <div class="text-sm">
                        <Link v-if="day.team" :href="showTeam.url(day.team.id)" class="font-medium hover:underline">
                            {{ day.name ?? trans('pages.locations.unnamed_assignment') }}
                        </Link>
                        <span v-else class="font-medium">{{ day.name ?? trans('pages.locations.unnamed_assignment') }}</span>
                        <span v-if="day.start_time && day.end_time" class="text-muted-foreground">— {{ day.start_time }}–{{ day.end_time }}</span>
                    </div>
                    <div v-if="day.description" class="text-xs text-muted-foreground mt-1">{{ day.description }}</div>
                  </div>
                  <div v-if="day.team">
                    <TeamHoverCard :team="day.team" />
                  </div>
                </div>
              </li>
            </ul>
          </Card>
        </div>

        <div class="lg:col-span-1">
          <Card class="overflow-hidden">
            <div v-if="googleMapEmbedUrl(location)" class="aspect-video">
              <iframe :src="googleMapEmbedUrl(location)!" class="h-full w-full border-0" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
            </div>
            <div v-else class="p-4 text-sm text-muted-foreground">{{ trans('pages.locations.no_map') }}</div>
          </Card>
        </div>
      </div>
    </div>
  </SidebarLayout>
</template>
