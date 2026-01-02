<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import { Card } from '@/components/ui/card'
import { trans } from 'laravel-vue-i18n'
import { show } from '@/routes/teams'

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
</script>

<template>
  <SidebarLayout>
    <Head :title="trans('pages.teams.title')" />
    <div class="space-y-4">
      <h1 class="text-2xl font-bold tracking-tight">{{ trans('pages.teams.title') }}</h1>

      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        <Card v-for="t in teams" :key="t.id" class="p-4">
          <div class="flex items-center gap-3">
            <img v-if="t.logo" :src="t.logo" :alt="t.name" class="h-10 w-10 shrink-0 rounded object-cover" />
            <div>
              <h2 class="font-semibold leading-tight">{{ t.name }}</h2>
              <div v-if="t.slug" class="text-xs text-muted-foreground">@{{ t.slug }}</div>
            </div>
          </div>

          <p v-if="t.description" class="mt-3 line-clamp-3 text-sm text-muted-foreground">
            {{ t.description }}
          </p>

          <div class="mt-4">
            <Link :href="show.url(t.id)" class="text-sm font-medium text-primary hover:underline">{{ trans('pages.teams.view_details') }}</Link>
          </div>
        </Card>
      </div>
    </div>
  </SidebarLayout>
</template>
