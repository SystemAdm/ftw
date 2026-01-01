<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import { Card } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { trans } from 'laravel-vue-i18n'

type LocationMini = {
  id: number
  name: string
} | null

type Upcoming = {
  date: string
  label: string
  weekday: number
  weekday_label: string
  name: string | null
  description: string | null
  start_time: string | null
  end_time: string | null
  location: LocationMini
}

type Team = {
  id: number
  name: string
  slug?: string | null
  description?: string | null
  logo?: string | null
  active: boolean
}

const page = usePage<{ team: Team; upcoming: Upcoming[] }>()
const team = page.props.team
const upcoming = page.props.upcoming ?? []
</script>

<template>
  <SidebarLayout>
    <Head :title="team.name" />
    <div class="space-y-6">
      <div class="flex items-start gap-4">
        <img v-if="team.logo" :src="team.logo" :alt="team.name" class="h-16 w-16 rounded object-cover" />
        <div>
          <h1 class="text-2xl font-bold tracking-tight">{{ team.name }}</h1>
          <div v-if="team.slug" class="text-sm text-muted-foreground">@{{ team.slug }}</div>
          <p v-if="team.description" class="mt-2 max-w-3xl text-sm text-muted-foreground">{{ team.description }}</p>
        </div>
      </div>

      <div>
        <h2 class="mb-3 text-lg font-semibold">{{ trans('pages.teams.upcoming_weekdays') }}</h2>
        <div v-if="upcoming.length === 0" class="text-sm text-muted-foreground">{{ trans('pages.teams.no_upcoming') }}</div>
        <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
          <Card v-for="u in upcoming" :key="u.date + '-' + u.weekday" class="p-4">
            <div class="flex items-center justify-between">
              <div>
                <div class="text-sm text-muted-foreground">{{ u.weekday_label }}</div>
                <div class="text-lg font-semibold">{{ u.label }}</div>
              </div>
              <Badge v-if="u.location">{{ u.location.name }}</Badge>
            </div>

            <div class="mt-3 space-y-1">
              <div class="text-sm font-medium">{{ u.name ?? trans('pages.teams.unnamed_assignment') }}</div>
              <div class="text-xs text-muted-foreground" v-if="u.start_time || u.end_time">
                {{ u.start_time }} — {{ u.end_time }}
              </div>
              <div v-if="u.description" class="mt-1 text-sm text-muted-foreground">{{ u.description }}</div>
              <div v-if="u.location" class="mt-2 text-xs">
                <Link :href="`/locations/${u.location.id}`" class="text-primary hover:underline">{{ trans('pages.teams.view_location') }}</Link>
              </div>
            </div>
          </Card>
        </div>
      </div>
    </div>
  </SidebarLayout>
</template>
