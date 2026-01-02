<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Card } from '@/components/ui/card';
import { trans } from 'laravel-vue-i18n';
import { show } from '@/routes/locations';

type LocationItem = {
  id: number;
  name: string;
  postal?: string | null;
  street_address?: string | null;
  street_number?: string | null;
  latitude?: number | null;
  longitude?: number | null;
};

type Paginated<T> = {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  next_page_url?: string | null;
  prev_page_url?: string | null;
};

const page = usePage<{ locations: Paginated<LocationItem> }>();
const locations = page.props.locations;
</script>

<template>
  <SidebarLayout>
    <Head :title="trans('pages.locations.title')" />
    <div class="space-y-4">
      <h1 class="text-2xl font-bold tracking-tight">{{ trans('pages.locations.title') }}</h1>

      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        <Card v-for="loc in locations.data" :key="loc.id" class="p-4">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold">{{ loc.name }}</h2>
          </div>
          <div class="mt-1 text-sm text-muted-foreground">
            <div v-if="loc.postal">{{ loc.postal }}</div>
            <div v-if="loc.street_address">{{ loc.street_address }} <span v-if="loc.street_number">{{ loc.street_number }}</span></div>
          </div>

          <div class="mt-3">
            <Link :href="show.url(loc.id)" class="text-primary hover:underline">{{ trans('pages.locations.view_details') }}</Link>
          </div>
        </Card>
      </div>

      <div class="flex items-center justify-between" v-if="locations.total > locations.per_page">
        <Link v-if="locations.prev_page_url" :href="locations.prev_page_url" class="text-primary hover:underline">{{ trans('pages.locations.pagination.previous') }}</Link>
        <div class="text-sm text-muted-foreground">{{ trans('pages.locations.pagination.page_of', { current: locations.current_page, last: locations.last_page }) }}</div>
        <Link v-if="locations.next_page_url" :href="locations.next_page_url" class="text-primary hover:underline">{{ trans('pages.locations.pagination.next') }}</Link>
      </div>
    </div>
  </SidebarLayout>
</template>
