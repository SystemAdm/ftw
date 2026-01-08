<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue'
import { Head, Link, usePage, useForm } from '@inertiajs/vue3'
import { Card } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Textarea } from '@/components/ui/textarea'
import { trans } from 'laravel-vue-i18n'
import { show as showLocation } from '@/routes/locations'
import { index, apply } from '@/routes/teams'
import { computed } from 'vue'
import { BreadcrumbItemType } from '@/types'

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
  applications_enabled: boolean
}

const page = usePage<{ team: Team; upcoming: Upcoming[]; isMember: boolean }>()
const team = page.props.team
const upcoming = page.props.upcoming ?? []
const isMember = page.props.isMember

const breadcrumbs = computed<BreadcrumbItemType[]>(() => [
  {
    title: trans('ui.navigation.home'),
    href: '/',
  },
  {
    title: trans('pages.teams.title'),
    href: index.url(),
  },
  {
    title: team.name,
    href: '#',
  },
])

const form = useForm({
  application: '',
})

function submitApplication() {
  form.post(apply.url(team.id), {
    onSuccess: () => {
      form.reset()
    },
  })
}
</script>

<template>
  <SidebarLayout :breadcrumbs="breadcrumbs">
    <Head :title="team.name" />
    <div class="space-y-8">
      <!-- Team Header -->
      <div class="flex flex-col gap-6 md:flex-row md:items-center">
        <div class="h-24 w-24 shrink-0 overflow-hidden rounded-lg bg-muted shadow-sm">
          <img v-if="team.logo" :src="team.logo" :alt="team.name" class="h-full w-full object-cover" />
          <div v-else class="flex h-full w-full items-center justify-center bg-primary/10 text-2xl font-bold text-primary">
            {{ team.name.charAt(0) }}
          </div>
        </div>
        <div class="flex-1 space-y-1">
          <div class="flex items-center gap-2">
            <h1 class="text-3xl font-extrabold tracking-tight">{{ team.name }}</h1>
            <Badge v-if="isMember" variant="secondary">{{ trans('pages.teams.already_member') }}</Badge>
          </div>
          <div v-if="team.slug" class="text-lg text-muted-foreground">@{{ team.slug }}</div>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <!-- Main Content -->
        <div class="space-y-8 lg:col-span-2">
          <!-- Description -->
          <section v-if="team.description" class="prose prose-sm dark:prose-invert max-w-none">
            <h2 class="text-xl font-bold">{{ trans('pages.teams.about') }}</h2>
            <p class="text-muted-foreground">{{ team.description }}</p>
          </section>

          <!-- Upcoming Assignments -->
          <section>
            <h2 class="mb-4 text-xl font-bold">{{ trans('pages.teams.upcoming_weekdays') }}</h2>
            <div v-if="upcoming.length === 0" class="rounded-lg border border-dashed p-8 text-center text-muted-foreground">
              {{ trans('pages.teams.no_upcoming') }}
            </div>
            <div v-else class="grid grid-cols-1 gap-4">
              <Card v-for="u in upcoming" :key="u.date + '-' + u.weekday" class="group flex flex-col p-5 transition-all hover:shadow-md">
                <div class="flex items-start justify-between">
                  <div class="space-y-1">
                    <div class="text-xs font-medium uppercase tracking-wider text-primary">{{ u.weekday_label }}</div>
                    <div class="text-xl font-bold">{{ u.label }}</div>
                  </div>
                  <Badge v-if="u.location" variant="outline">{{ u.location.name }}</Badge>
                </div>

                <div class="mt-4 flex-1 space-y-2">
                  <div class="font-semibold">{{ u.name ?? trans('pages.teams.unnamed_assignment') }}</div>
                  <div class="flex items-center gap-2 text-sm text-muted-foreground" v-if="u.start_time || u.end_time">
                    <span class="font-mono">{{ u.start_time?.slice(0, 5) }} — {{ u.end_time?.slice(0, 5) }}</span>
                  </div>
                  <div v-if="u.description" class="line-clamp-2 text-sm text-muted-foreground italic">
                    "{{ u.description }}"
                  </div>
                </div>

                <div v-if="u.location" class="mt-4 pt-4 border-t">
                  <Link :href="showLocation.url(u.location.id)" class="inline-flex items-center text-xs font-semibold text-primary hover:underline">
                    {{ trans('pages.teams.view_location') }}
                    <span class="ml-1">→</span>
                  </Link>
                </div>
              </Card>
            </div>
          </section>
        </div>

        <!-- Sidebar / Actions -->
        <div class="space-y-6">
          <Card v-if="team.applications_enabled && !isMember" class="p-6">
            <h2 class="mb-4 text-lg font-bold">{{ trans('crew.teams.apply') }}</h2>
            <form @submit.prevent="submitApplication" class="space-y-4">
              <div class="space-y-2">
                <label for="application" class="text-sm font-medium leading-none">{{ trans('crew.teams.apply_description') }}</label>
                <Textarea
                  id="application"
                  v-model="form.application"
                  class="min-h-[120px]"
                  :placeholder="trans('crew.teams.fields.application_placeholder')"
                  required
                />
                <div v-if="form.errors.application" class="text-xs text-destructive">
                  {{ form.errors.application }}
                </div>
              </div>
              <Button type="submit" class="w-full" :disabled="form.processing">
                {{ form.processing ? trans('crew.teams.status.pending') : trans('crew.teams.apply') }}
              </Button>
            </form>
          </Card>

          <Card v-else-if="isMember" class="flex flex-col items-center justify-center p-8 text-center bg-primary/5 border-primary/20">
            <div class="h-12 w-12 rounded-full bg-primary/20 flex items-center justify-center text-primary mb-4">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <h3 class="font-bold">{{ trans('pages.teams.already_member') }}</h3>
            <p class="text-xs text-muted-foreground mt-1">{{ trans('crew.teams.my_teams_description') }}</p>
          </Card>

          <Card v-else-if="!team.applications_enabled" class="p-6 bg-muted/50 border-dashed text-center">
             <p class="text-sm text-muted-foreground italic">{{ trans('crew.teams.no_available_teams') }}</p>
          </Card>
        </div>
      </div>
    </div>
  </SidebarLayout>
</template>
