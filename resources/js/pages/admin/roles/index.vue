<script setup lang="ts">
import { usePage, router } from '@inertiajs/vue3'
import SidebarLayout from '@/components/layouts/SidebarLayout.vue'
import { Table, TableBody, TableCell, TableFooter, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import Paginator from '@/components/custom/Paginator.vue'

const page = usePage<PageProps>()

function goCreate() {
  router.visit('/admin/roles/create')
}

function goShow(id: number) {
  router.visit(`/admin/roles/${id}`)
}
</script>

<template>
  <SidebarLayout>
    <div class="mb-4 flex items-center justify-between">
      <h1 class="text-xl font-semibold">Roles</h1>
      <button class="rounded bg-black/90 px-3 py-2 text-white" @click="goCreate">New Role</button>
    </div>

    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>Name</TableHead>
          <TableHead>Guard</TableHead>
          <TableHead>Users</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow v-for="role in (page.props as any).roles.data" :key="role.id" class="cursor-pointer" @click="goShow(role.id)">
          <TableCell>{{ role.name }}</TableCell>
          <TableCell>{{ role.guard_name }}</TableCell>
          <TableCell>{{ role.users_count }}</TableCell>
        </TableRow>
      </TableBody>
      <TableFooter>
        <TableRow>
          <TableCell colspan="3">
            <Paginator :collection="(page.props as any).roles" />
          </TableCell>
        </TableRow>
      </TableFooter>
    </Table>
  </SidebarLayout>
</template>
