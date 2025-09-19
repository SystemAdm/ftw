<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { index } from '@/routes/admin/locations'
import { dashboard } from '@/routes/admin'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faFloppyDisk } from '@fortawesome/free-solid-svg-icons';
import { Button } from '@/components/ui/button';
import { Switch } from '@/components/ui/switch';

interface Location {
  id: number
  postal_code: number
  name: string
  active: boolean
  description?: string | null
  latitude?: string | null
  longitude?: string | null
  google_maps_url?: string | null
  images?: string | null
  street_address?: string | null
  street_number?: string | null
  link?: string | null
  deleted_at?: string | null
  created_at?: string
  updated_at?: string
}

const page = usePage<{ location: Location }>()
const location = page.props.location

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Admin', href: dashboard().url },
  { title: 'Locations', href: index().url },
  { title: `Edit #${location.id}`,href:'' },
]

const form = useForm({
  postal_code: location.postal_code,
  name: location.name,
  active: location.active,
  description: location.description ?? '',
  latitude: location.latitude ?? '',
  longitude: location.longitude ?? '',
  google_maps_url: location.google_maps_url ?? '',
  images: location.images ?? '',
  street_address: location.street_address ?? '',
  street_number: location.street_number ?? '',
  link: location.link ?? '',
})

function submit() {
  form.put(`/admin/locations/${location.id}`, {
    preserveScroll: true,
  })
}
</script>

<template>
  <Head title="Edit Location" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
      <div class="relative flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
        <form @submit.prevent="submit" class="space-y-4 p-6">
          <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold">Edit Location</h1>
            <span v-if="location.deleted_at" class="rounded bg-yellow-500 px-2 py-0.5 text-xs text-black">Trashed</span>
          </div>

          <div class="grid gap-4 md:grid-cols-2">
            <div>
              <label class="block text-sm font-medium mb-1" for="postal_code">Postal Code</label>
              <input id="postal_code" v-model.number="form.postal_code" type="number" class="w-full rounded-md border border-sidebar-border/70 px-3 py-2 focus:outline-none" />
              <p v-if="form.errors.postal_code" class="mt-1 text-sm text-red-600">{{ form.errors.postal_code }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1" for="name">Name</label>
              <input id="name" v-model="form.name" type="text" class="w-full rounded-md border border-sidebar-border/70 px-3 py-2 focus:outline-none" />
              <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1" for="active">Active</label>
              <Switch id="active" :model-value="form.active" @update:model-value="(v:boolean)=> form.active = v" />
              <p v-if="form.errors.active" class="mt-1 text-sm text-red-600">{{ form.errors.active }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1" for="google_maps_url">Google Maps URL</label>
              <input id="google_maps_url" v-model="form.google_maps_url" type="text" class="w-full rounded-md border border-sidebar-border/70 px-3 py-2 focus:outline-none" />
              <p v-if="form.errors.google_maps_url" class="mt-1 text-sm text-red-600">{{ form.errors.google_maps_url }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1" for="latitude">Latitude</label>
              <input id="latitude" v-model="form.latitude" type="text" class="w-full rounded-md border border-sidebar-border/70 px-3 py-2 focus:outline-none" />
              <p v-if="form.errors.latitude" class="mt-1 text-sm text-red-600">{{ form.errors.latitude }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1" for="longitude">Longitude</label>
              <input id="longitude" v-model="form.longitude" type="text" class="w-full rounded-md border border-sidebar-border/70 px-3 py-2 focus:outline-none" />
              <p v-if="form.errors.longitude" class="mt-1 text-sm text-red-600">{{ form.errors.longitude }}</p>
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-medium mb-1" for="description">Description</label>
              <textarea id="description" v-model="form.description" class="w-full rounded-md border border-sidebar-border/70 px-3 py-2 focus:outline-none"></textarea>
              <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1" for="street_address">Street Address</label>
              <input id="street_address" v-model="form.street_address" type="text" class="w-full rounded-md border border-sidebar-border/70 px-3 py-2 focus:outline-none" />
              <p v-if="form.errors.street_address" class="mt-1 text-sm text-red-600">{{ form.errors.street_address }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1" for="street_number">Street Number</label>
              <input id="street_number" v-model="form.street_number" type="text" class="w-full rounded-md border border-sidebar-border/70 px-3 py-2 focus:outline-none" />
              <p v-if="form.errors.street_number" class="mt-1 text-sm text-red-600">{{ form.errors.street_number }}</p>
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-medium mb-1" for="images">Images (JSON or CSV)</label>
              <input id="images" v-model="form.images" type="text" class="w-full rounded-md border border-sidebar-border/70 px-3 py-2 focus:outline-none" />
              <p v-if="form.errors.images" class="mt-1 text-sm text-red-600">{{ form.errors.images }}</p>
            </div>
            <div class="md:col-span-2">
              <label class="block textsm font-medium mb-1" for="link">Link</label>
              <input id="link" v-model="form.link" type="text" class="w-full rounded-md border border-sidebar-border/70 px-3 py-2 focus:outline-none" />
              <p v-if="form.errors.link" class="mt-1 text-sm text-red-600">{{ form.errors.link }}</p>
            </div>
          </div>

          <div class="flex items-center justify-end gap-2 pt-4 border-t border-sidebar-border/70">
            <Button  variant="destructive" size="sm" @click="index">Cancel</Button>
            <Button type="submit" :disabled="form.processing" ><font-awesome-icon :icon="faFloppyDisk"/>Save changes</Button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
