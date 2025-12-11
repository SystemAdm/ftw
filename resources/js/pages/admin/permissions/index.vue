<script setup lang="ts">
import { usePage, router } from '@inertiajs/vue3'
import SidebarLayout from '@/components/layouts/SidebarLayout.vue'
import { Table, TableBody, TableCell, TableFooter, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import Paginator from '@/components/custom/Paginator.vue'

const page = usePage<PageProps>()

function goCreate() {
  router.visit('/admin/permissions/create')
}

function goShow(id: number) {
  router.visit(`/admin/permissions/${id}`)
}
</script>

<template>
  <SidebarLayout>
    <div class="mb-4 flex items-center justify-between">
      <h1 class="text-xl font-semibold">Permissions</h1>
      <button class="rounded bg-black/90 px-3 py-2 text-white" @click="goCreate">New Permission</button>
    </div>

    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>Name</TableHead>
          <TableHead>Guard</TableHead>
          <TableHead>Roles</TableHead>
          <TableHead>Users</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow v-for="perm in (page.props as any).permissions.data" :key="perm.id" class="cursor-pointer" @click="goShow(perm.id)">
          <TableCell>{{ perm.name }}</TableCell>
          <TableCell>{{ perm.guard_name }}</TableCell>
          <TableCell>{{ perm.roles_count }}</TableCell>
          <TableCell>{{ perm.users_count }}</TableCell>
        </TableRow>
      </TableBody>
      <TableFooter>
        <TableRow>
          <TableCell colspan="4">
            <Paginator :collection="(page.props as any).permissions" />
          </TableCell>
        </TableRow>
      </TableFooter>
    </Table>
  </SidebarLayout>

</template>
