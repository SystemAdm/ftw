<script setup lang="ts">
import { usePage, router } from '@inertiajs/vue3'
import SidebarLayout from '@/components/layouts/SidebarLayout.vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'

const page = usePage<PageProps>()
const permission = (page.props as any).permission

function goBack() {
  router.visit('/admin/permissions')
}

function editPermission() {
  router.visit(`/admin/permissions/${permission.id}/edit`)
}

function deletePermission() {
  router.delete(`/admin/permissions/${permission.id}`, {
    onFinish: () => router.visit('/admin/permissions'),
  })
}
</script>

<template>
  <SidebarLayout>
    <div class="mb-4 flex items-center justify-between">
      <h1 class="text-xl font-semibold">Permission: {{ permission.name }}</h1>
      <div class="flex gap-2">
        <Button variant="outline" @click="goBack">Back</Button>
        <Button variant="secondary" @click="editPermission">Edit</Button>
        <Button variant="destructive" @click="deletePermission">Delete</Button>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
      <section class="lg:col-span-2 space-y-6">
        <div class="rounded-md border bg-card p-4">
          <h2 class="mb-2 text-sm font-medium text-muted-foreground">Guard</h2>
          <div class="text-sm">{{ permission.guard_name }}</div>
        </div>

        <div class="rounded-md border bg-card p-4">
          <h2 class="mb-2 text-sm font-medium text-muted-foreground">Roles</h2>
          <div v-if="(permission.roles ?? []).length" class="flex flex-wrap gap-2">
            <Badge v-for="r in permission.roles" :key="r.id" variant="outline">{{ r.name }}</Badge>
          </div>
          <div v-else class="text-sm text-muted-foreground">No roles.</div>
        </div>

        <div class="rounded-md border bg-card p-4">
          <h2 class="mb-2 text-sm font-medium text-muted-foreground">Users</h2>
          <div v-if="(permission.users ?? []).length" class="flex flex-col gap-2">
            <div v-for="u in permission.users" :key="u.id" class="flex items-center justify-between">
              <div class="text-sm">{{ u.name }} <span class="text-muted-foreground">{{ u.email }}</span></div>
            </div>
          </div>
          <div v-else class="text-sm text-muted-foreground">No users with this permission.</div>
        </div>
      </section>
    </div>
  </SidebarLayout>
</template>
