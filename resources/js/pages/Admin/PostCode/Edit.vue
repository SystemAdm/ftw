<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { index } from '@/routes/admin/postcodes'
import { dashboard } from '@/routes/admin'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faFloppyDisk } from '@fortawesome/free-solid-svg-icons';
import { Button } from '@/components/ui/button';

interface PostalCode {
  id: number
  postal_code: string
  city: string
  state?: string | null
  country?: string | null
  county?: string | null
  deleted_at?: string | null
  created_at?: string
  updated_at?: string
}

const page = usePage<{ code: PostalCode }>()
const code = page.props.code

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Admin', href: dashboard().url },
  { title: 'Post Codes', href: index().url },
  { title: `Edit #${code.id}`,href:'' },
]

const form = useForm({
  postal_code: code.postal_code,
  city: code.city,
  state: code.state ?? '',
  country: code.country ?? '',
  county: code.county ?? '',
})

function submit() {
  form.put(`/admin/postcodes/${code.id}`, {
    preserveScroll: true,
  })
}
</script>

<template>
  <Head title="Edit Post Code" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
      <div class="relative flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
        <form @submit.prevent="submit" class="space-y-4 p-6">
          <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold">Edit Post Code</h1>
            <span v-if="code.deleted_at" class="rounded bg-yellow-500 px-2 py-0.5 text-xs text-black">Trashed</span>
          </div>

          <div class="grid gap-4 md:grid-cols-2">
            <div>
              <label class="block text-sm font-medium mb-1" for="postal_code">Postal Code</label>
              <input id="postal_code" v-model="form.postal_code" type="text" class="w-full rounded-md border border-sidebar-border/70 px-3 py-2 focus:outline-none" />
              <p v-if="form.errors.postal_code" class="mt-1 text-sm text-red-600">{{ form.errors.postal_code }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1" for="city">City</label>
              <input id="city" v-model="form.city" type="text" class="w-full rounded-md border border-sidebar-border/70 px-3 py-2 focus:outline-none" />
              <p v-if="form.errors.city" class="mt-1 text-sm text-red-600">{{ form.errors.city }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1" for="state">State</label>
              <input id="state" v-model="form.state" type="text" class="w-full rounded-md border border-sidebar-border/70 px-3 py-2 focus:outline-none" />
              <p v-if="form.errors.state" class="mt-1 text-sm text-red-600">{{ form.errors.state }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1" for="country">Country</label>
              <input id="country" v-model="form.country" type="text" class="w-full rounded-md border border-sidebar-border/70 px-3 py-2 focus:outline-none" />
              <p v-if="form.errors.country" class="mt-1 text-sm text-red-600">{{ form.errors.country }}</p>
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-medium mb-1" for="county">County</label>
              <input id="county" v-model="form.county" type="text" class="w-full rounded-md border border-sidebar-border/70 px-3 py-2 focus:outline-none" />
              <p v-if="form.errors.county" class="mt-1 text-sm text-red-600">{{ form.errors.county }}</p>
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
