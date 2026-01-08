<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import { trans } from 'laravel-vue-i18n'
import { index, show } from '@/routes/teams'
import { computed } from 'vue'
import { BreadcrumbItemType } from '@/types'

type Team = {
  id: number
  name: string
  slug?: string | null
  logo?: string | null
  description?: string | null
}

type Pagination<T> = {
  data: T[]
  links?: any
}

const page = usePage<{ teams: Pagination<Team> }>()
const teams = (page.props.teams?.data ?? []) as Team[]

const breadcrumbs = computed<BreadcrumbItemType[]>(() => [
  {
    title: trans('ui.navigation.home'),
    href: '/',
  },
  {
    title: trans('pages.teams.title'),
    href: index.url(),
  },
])
</script>

<template>
  <SidebarLayout :breadcrumbs="breadcrumbs">
    <Head :title="trans('pages.teams.title')" />
    <div class="space-y-4">
      <h1 class="text-2xl font-bold tracking-tight">{{ trans('pages.teams.title') }}</h1>

      <div v-if="teams.length > 0" class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
        <Link
          v-for="t in teams"
          :key="t.id"
          :href="show.url(t.id)"
          class="group flex flex-col overflow-hidden rounded-xl border bg-card shadow-sm transition-all hover:shadow-md"
        >
          <div class="aspect-video w-full overflow-hidden bg-muted">
            <img
              v-if="t.logo"
              :src="t.logo"
              :alt="t.name"
              class="h-full w-full object-cover transition-transform group-hover:scale-105"
            />
            <div v-else class="flex h-full w-full items-center justify-center bg-primary/10 text-4xl font-bold text-primary">
              {{ t.name.charAt(0) }}
            </div>
          </div>
          <div class="flex flex-1 flex-col p-6">
            <div class="mb-2 flex items-center justify-between">
              <div v-if="t.slug" class="text-xs font-medium text-muted-foreground uppercase tracking-wider">
                @{{ t.slug }}
              </div>
            </div>
            <h3 class="mb-2 text-xl font-bold group-hover:text-primary">
              {{ t.name }}
            </h3>
            <p v-if="t.description" class="line-clamp-3 text-sm text-muted-foreground">
              {{ t.description }}
            </p>
            <div class="mt-auto pt-4">
              <span class="inline-flex items-center text-sm font-semibold text-primary">
                {{ trans('pages.teams.view_details') }}
                <span class="ml-1 transition-transform group-hover:translate-x-1">→</span>
              </span>
            </div>
          </div>
        </Link>
      </div>

      <div v-else class="rounded-lg border border-dashed p-12 text-center">
        <div class="flex flex-col items-center gap-2">
          <h3 class="text-lg font-medium text-foreground">{{ trans('pages.teams.none') }}</h3>
          <p class="text-sm text-muted-foreground">{{ trans('pages.teams.subtitle') }}</p>
        </div>
      </div>
    </div>
  </SidebarLayout>
</template>
