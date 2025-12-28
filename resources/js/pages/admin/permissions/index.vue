<script setup lang="ts">
import DeleteConfirmationDialog from '@/components/custom/DeleteConfirmationDialog.vue';
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { Table, TableBody, TableCell, TableFooter, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';
import Paginator from '@/components/custom/Paginator.vue';
import { router, usePage } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { Edit, Eye, MoreHorizontal, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { BreadcrumbItemType } from '@/types';

const page = usePage<PageProps>()

const deleteDialogOpen = ref(false);
const selectedPermissionId = ref<number | null>(null);

const breadcrumbs = computed<BreadcrumbItemType[]>(() => [
  {
    title: trans('pages.settings.permissions.title'),
    href: '/admin/permissions',
  },
]);

function goCreate() {
  router.visit('/admin/permissions/create')
}

function confirmDelete(id: number) {
  selectedPermissionId.value = id;
  deleteDialogOpen.value = true;
}

function handleDelete() {
  if (selectedPermissionId.value) {
    router.delete(`/admin/permissions/${selectedPermissionId.value}`, {
      onFinish: () => {
        deleteDialogOpen.value = false;
        selectedPermissionId.value = null;
      },
    });
  }
}
</script>

<template>
  <SidebarLayout :breadcrumbs="breadcrumbs">
    <div class="mb-4 flex items-center justify-between">
      <h1 class="text-xl font-semibold">{{ trans('pages.settings.permissions.title') }}</h1>
      <Button @click="goCreate">{{ trans('pages.settings.permissions.new') }}</Button>
    </div>

    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>{{ trans('pages.settings.roles.fields.name') }}</TableHead>
          <TableHead>{{ trans('pages.settings.roles.fields.guard') }}</TableHead>
          <TableHead class="text-center">{{ trans('pages.settings.roles.fields.roles') }}</TableHead>
          <TableHead class="text-center">{{ trans('pages.settings.roles.fields.users') }}</TableHead>
          <TableHead class="w-12"></TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow v-for="perm in (page.props as any).permissions.data" :key="perm.id" class="group">
          <TableCell class="cursor-pointer font-medium" @click="router.visit(`/admin/permissions/${perm.id}`)">
            {{ perm.name }}
          </TableCell>
          <TableCell class="cursor-pointer" @click="router.visit(`/admin/permissions/${perm.id}`)">
            {{ perm.guard_name }}
          </TableCell>
          <TableCell class="cursor-pointer text-center" @click="router.visit(`/admin/permissions/${perm.id}`)">
            {{ perm.roles_count }}
          </TableCell>
          <TableCell class="cursor-pointer text-center" @click="router.visit(`/admin/permissions/${perm.id}`)">
            {{ perm.users_count }}
          </TableCell>
          <TableCell>
            <DropdownMenu>
              <DropdownMenuTrigger as-child>
                <Button variant="ghost" size="icon">
                  <MoreHorizontal class="h-4 w-4" />
                  <span class="sr-only">Open menu</span>
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="end">
                <DropdownMenuItem @click="router.visit(`/admin/permissions/${perm.id}`)">
                  <Eye class="mr-2 h-4 w-4" />
                  {{ trans('pages.settings.locations.actions.view') }}
                </DropdownMenuItem>
                <DropdownMenuItem @click="router.visit(`/admin/permissions/${perm.id}/edit`)">
                  <Edit class="mr-2 h-4 w-4" />
                  {{ trans('pages.settings.locations.actions.edit') }}
                </DropdownMenuItem>
                <DropdownMenuItem class="text-destructive focus:text-destructive" @click="confirmDelete(perm.id)">
                  <Trash2 class="mr-2 h-4 w-4" />
                  {{ trans('pages.settings.locations.actions.delete') }}
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </TableCell>
        </TableRow>
      </TableBody>
      <TableFooter>
        <TableRow>
          <TableCell colspan="5">
            <Paginator :collection="(page.props as any).permissions" />
          </TableCell>
        </TableRow>
      </TableFooter>
    </Table>

    <DeleteConfirmationDialog
      v-model:open="deleteDialogOpen"
      :title="trans('pages.settings.permissions.delete.title')"
      :description="trans('pages.settings.permissions.delete.description')"
      @confirm="handleDelete"
    />
  </SidebarLayout>

</template>
