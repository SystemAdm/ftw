<script setup lang="ts">
import DeleteConfirmationDialog from '@/components/custom/DeleteConfirmationDialog.vue';
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { router, usePage } from '@inertiajs/vue3';
import { create, show as showRoute, edit as editRoute, destroy as destroyRoute } from '@/routes/admin/postcodes';
import { restore as restoreRoute, forceDestroy as forceDestroyRoute } from '@/routes/admin/postcodes';
import { Table, TableBody, TableEmpty, TableHead, TableHeader, TableRow, TableCell, TableFooter } from '@/components/ui/table';
import Paginator from '@/components/custom/Paginator.vue';
import { trans } from 'laravel-vue-i18n';
import { Edit, Eye, MoreHorizontal, RotateCcw, Trash2 } from 'lucide-vue-next';
import { ref, computed } from 'vue';



import { BreadcrumbItemType } from '@/types';

const page = usePage();
const postcodes = computed(() => (page.props as any).postcodes);

const deleteDialogOpen = ref(false);
const forceDeleteDialogOpen = ref(false);
const selectedPostcode = ref<any>(null);

const breadcrumbs = computed<BreadcrumbItemType[]>(() => [
  {
    title: trans('pages.settings.postcodes.title'),
    href: '/admin/postcodes',
  },
]);

function handleRestore(pc: any) {
  router.post(restoreRoute.url(pc.postal_code));
}

function confirmDelete(pc: any) {
  selectedPostcode.value = pc;
  deleteDialogOpen.value = true;
}

function confirmForceDelete(pc: any) {
  selectedPostcode.value = pc;
  forceDeleteDialogOpen.value = true;
}

function handleDelete() {
  if (selectedPostcode.value) {
    router.delete(destroyRoute.url(selectedPostcode.value.postal_code), {
      onFinish: () => {
        deleteDialogOpen.value = false;
        selectedPostcode.value = null;
      },
    });
  }
}

function handleForceDelete() {
  if (selectedPostcode.value) {
    router.delete(forceDestroyRoute.url(selectedPostcode.value.postal_code), {
      onFinish: () => {
        forceDeleteDialogOpen.value = false;
        selectedPostcode.value = null;
      },
    });
  }
}
</script>

<template>
  <SidebarLayout :breadcrumbs="breadcrumbs">
    <div class="mb-4 flex items-center justify-between">
      <h1 class="text-xl font-semibold">{{ trans('pages.settings.postcodes.title') }}</h1>
      <Button @click.prevent="router.visit(create.url())">{{ trans('pages.settings.postcodes.actions.create') }}</Button>
    </div>

    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>{{ trans('pages.settings.postcodes.fields.postal_code') }}</TableHead>
          <TableHead>{{ trans('pages.settings.postcodes.fields.city') }}</TableHead>
          <TableHead>{{ trans('pages.settings.postcodes.fields.state') }}</TableHead>
          <TableHead>{{ trans('pages.settings.postcodes.fields.country') }}</TableHead>
          <TableHead class="w-0 text-right"></TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableEmpty v-if="!postcodes || postcodes.data.length === 0" :colspan="5">
          {{ trans('pages.settings.postcodes.none') }}
        </TableEmpty>
        <template v-else>
          <TableRow v-for="pc in postcodes.data" :key="pc.postal_code">
            <TableCell>{{ pc.postal_code }}</TableCell>
            <TableCell>{{ pc.city }}</TableCell>
            <TableCell>{{ pc.state }}</TableCell>
            <TableCell>{{ pc.country }}</TableCell>
            <TableCell class="text-right">
              <DropdownMenu>
                <DropdownMenuTrigger as-child>
                  <Button variant="ghost" size="icon">
                    <MoreHorizontal class="h-4 w-4" />
                    <span class="sr-only">Open menu</span>
                  </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                  <DropdownMenuItem @click="router.visit(showRoute.url(pc.postal_code))">
                    <Eye class="mr-2 h-4 w-4" />
                    {{ trans('pages.settings.postcodes.actions.view') }}
                  </DropdownMenuItem>
                  <DropdownMenuItem @click="router.visit(editRoute.url(pc.postal_code))">
                    <Edit class="mr-2 h-4 w-4" />
                    {{ trans('pages.settings.postcodes.actions.edit') }}
                  </DropdownMenuItem>

                  <template v-if="pc.deleted_at">
                    <DropdownMenuItem class="text-green-600 focus:text-green-600" @click="handleRestore(pc)">
                      <RotateCcw class="mr-2 h-4 w-4" />
                      {{ trans('pages.settings.postcodes.actions.restore') }}
                    </DropdownMenuItem>
                    <DropdownMenuItem class="text-destructive focus:text-destructive" @click="confirmForceDelete(pc)">
                      <Trash2 class="mr-2 h-4 w-4" />
                      {{ trans('pages.settings.postcodes.actions.force_delete') }}
                    </DropdownMenuItem>
                  </template>
                  <DropdownMenuItem v-else class="text-destructive focus:text-destructive" @click="confirmDelete(pc)">
                    <Trash2 class="mr-2 h-4 w-4" />
                    {{ trans('pages.settings.postcodes.actions.delete') }}
                  </DropdownMenuItem>
                </DropdownMenuContent>
              </DropdownMenu>
            </TableCell>
          </TableRow>
        </template>
      </TableBody>
        <TableFooter v-if="postcodes">
            <TableRow>
                <TableCell colspan="5">
                    <Paginator :collection="postcodes" />
                </TableCell>
            </TableRow>
        </TableFooter>
    </Table>

    <DeleteConfirmationDialog
      v-model:open="deleteDialogOpen"
      :title="trans('pages.settings.postcodes.delete.title', { code: selectedPostcode?.postal_code ?? '' })"
      :description="trans('pages.settings.postcodes.delete.description')"
      @confirm="handleDelete"
    />

    <DeleteConfirmationDialog
      v-model:open="forceDeleteDialogOpen"
      :title="trans('pages.settings.postcodes.force_delete.title', { code: selectedPostcode?.postal_code ?? '' })"
      :description="trans('pages.settings.postcodes.force_delete.description')"
      @confirm="handleForceDelete"
    />

  </SidebarLayout>
</template>
