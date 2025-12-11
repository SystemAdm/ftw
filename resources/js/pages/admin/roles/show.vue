<script setup lang="ts">
import { usePage, router } from '@inertiajs/vue3'
import SidebarLayout from '@/components/layouts/SidebarLayout.vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { ref } from 'vue'

const page = usePage<PageProps>()
const role = (page.props as any).role
// const permissions = (page.props as any).permissions as Array<{ id: number; name: string }>
const searchTerm = ref('')
const results = ref<Array<{ id: number; name: string; email: string }>>([])
const searching = ref(false)

function goBack() {
  router.visit('/admin/roles')
}

function editRole() {
  router.visit(`/admin/roles/${role.id}/edit`)
}

function deleteRole() {
  router.delete(`/admin/roles/${role.id}`, {
    onFinish: () => router.visit('/admin/roles'),
  })
}

async function searchUsers() {
  searching.value = true
  results.value = []
  try {
    const res = await fetch(`/admin/roles/${role.id}/users/search?q=${encodeURIComponent(searchTerm.value)}`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin',
    })
    const json = await res.json()
    results.value = json.data ?? []
  } finally {
    searching.value = false
  }
}

function assignUser(id: number) {
  router.post(`/admin/roles/${role.id}/users`, { user_id: id }, {
    onFinish: () => router.visit(`/admin/roles/${role.id}`)
  })
}

function removeUser(id: number) {
  router.delete(`/admin/roles/${role.id}/users/${id}`, {
    onFinish: () => router.visit(`/admin/roles/${role.id}`)
  })
}
</script>

<template>
  <SidebarLayout>
    <div class="mb-4 flex items-center justify-between">
      <h1 class="text-xl font-semibold">Role: {{ role.name }}</h1>
      <div class="flex gap-2">
        <Button variant="outline" @click="goBack">Back</Button>
        <Button variant="secondary" @click="editRole">Edit</Button>
        <Button variant="destructive" @click="deleteRole">Delete</Button>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
      <section class="lg:col-span-2 space-y-6">
        <div class="rounded-md border bg-card p-4">
          <h2 class="mb-2 text-sm font-medium text-muted-foreground">Permissions</h2>
          <div v-if="(role.permissions ?? []).length" class="flex flex-wrap gap-2">
            <Badge v-for="p in role.permissions" :key="p.id" variant="outline">{{ p.name }}</Badge>
          </div>
          <div v-else class="text-sm text-muted-foreground">No permissions assigned.</div>
        </div>

        <div class="rounded-md border bg-card p-4">
          <h2 class="mb-2 text-sm font-medium text-muted-foreground">Users</h2>
          <div v-if="(role.users ?? []).length" class="flex flex-col gap-2">
            <div v-for="u in role.users" :key="u.id" class="flex items-center justify-between">
              <div class="text-sm">{{ u.name }} <span class="text-muted-foreground">{{ u.email }}</span></div>
              <Button size="sm" variant="outline" @click="removeUser(u.id)">Remove</Button>
            </div>
          </div>
          <div v-else class="text-sm text-muted-foreground">No users assigned.</div>
        </div>
      </section>

      <aside class="space-y-4">
        <div class="rounded-md border bg-card p-4">
          <h2 class="mb-2 text-sm font-medium text-muted-foreground">Assign User</h2>
          <div class="flex gap-2">
            <Input v-model="searchTerm" placeholder="Search users by name or email" @keyup.enter="searchUsers" />
            <Button @click="searchUsers" :disabled="searching">Search</Button>
          </div>
          <div class="mt-3 space-y-2 max-h-64 overflow-y-auto">
            <div v-for="r in results" :key="r.id" class="flex items-center justify-between">
              <div class="text-sm">{{ r.name }} <span class="text-muted-foreground">{{ r.email }}</span></div>
              <Button size="sm" @click="assignUser(r.id)">Assign</Button>
            </div>
          </div>
        </div>
      </aside>
    </div>
  </SidebarLayout>
</template>
