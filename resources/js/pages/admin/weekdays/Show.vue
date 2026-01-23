<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { usePage, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { edit as editRoute, index as indexRoute, destroy as destroyRoute } from '@/routes/admin/weekdays';
import { add as addExclusion, remove as removeExclusion } from '@/routes/admin/weekdays/exclusions';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ref, computed } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { trans } from 'laravel-vue-i18n';
import { Trash2 } from 'lucide-vue-next';
import DeleteConfirmationDialog from '@/components/custom/DeleteConfirmationDialog.vue';

import { AppPageProps } from '@/types';

const page = usePage<AppPageProps>();
const weekday = computed<any>(() => (page.props as any).weekday);

const open = ref(false);
const exclusionDate = ref('');

const deleteDialogOpen = ref(false);
const deleteExclusionDialogOpen = ref(false);
const selectedExclusionId = ref<number | null>(null);

function addExcluded() {
  if (!exclusionDate.value || !weekday.value?.id) return;
  router.post(
    addExclusion.url(weekday.value.id),
    { excluded_date: exclusionDate.value },
    {
      preserveScroll: true,
      onSuccess: () => {
        exclusionDate.value = '';
        open.value = false;
      },
    },
  );
}

function del() {
  router.delete(destroyRoute.url(weekday.value.id), {
    onBefore: () => {
      deleteDialogOpen.value = false;
    },
    onSuccess: () => {
      router.visit(indexRoute.url());
    },
  });
}

function confirmRemoveExcl(id: number) {
  selectedExclusionId.value = id;
  deleteExclusionDialogOpen.value = true;
}

function removeExcluded() {
  if (selectedExclusionId.value && weekday.value?.id) {
    router.delete(removeExclusion.url({ weekday: weekday.value.id, exclusion: selectedExclusionId.value }), {
      preserveScroll: true,
      onFinish: () => {
        deleteExclusionDialogOpen.value = false;
        selectedExclusionId.value = null;
      }
    });
  }
}
</script>

<template>
  <SidebarLayout>
    <div v-if="weekday" class="mb-4 flex items-center justify-between">
      <h1 class="text-xl font-semibold">{{ trans('pages.settings.weekdays.fields.weekday') }}: {{ trans(`pages.settings.weekdays.days.${weekday.weekday}`) }}</h1>
      <div class="flex gap-2">
        <Button variant="secondary" @click="router.visit(indexRoute.url())">{{ trans('pages.settings.locations.actions.cancel') }}</Button>
        <Button @click="router.visit(editRoute.url(weekday.id))">{{ trans('pages.settings.locations.actions.edit') }}</Button>
        <Button variant="destructive" @click="deleteDialogOpen = true">{{ trans('pages.settings.locations.actions.delete') }}</Button>
      </div>
    </div>

    <Card v-if="weekday" class="max-w-4xl">
      <CardHeader>
        <CardTitle>{{ trans('pages.settings.weekdays.title') }}</CardTitle>
        <CardDescription>{{ trans('pages.settings.weekdays.fields.description') }}: {{ weekday.description || '—' }}</CardDescription>
      </CardHeader>
      <CardContent>
        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <dt class="text-sm font-medium text-muted-foreground">{{ trans('pages.settings.weekdays.fields.weekday') }}</dt>
            <dd class="text-lg font-semibold">{{ trans(`pages.settings.weekdays.days.${weekday.weekday}`) }}</dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-muted-foreground">{{ trans('pages.settings.weekdays.fields.team') }}</dt>
            <dd class="text-lg font-semibold">{{ weekday.team?.name ?? '—' }}</dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-muted-foreground">{{ trans('pages.settings.weekdays.fields.location') }}</dt>
            <dd class="text-lg font-semibold">{{ weekday.location?.name ?? '—' }}</dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-muted-foreground">{{ trans('pages.settings.weekdays.fields.week_type') }}</dt>
            <dd class="text-lg font-semibold">{{ trans(`pages.settings.weekdays.week_types.${weekday.week_type}`) }}</dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-muted-foreground">{{ trans('pages.settings.weekdays.fields.month_occurrence') }}</dt>
            <dd class="text-lg font-semibold">{{ trans(`pages.settings.weekdays.month_occurrences.${weekday.month_occurrence}`) }}</dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-muted-foreground">{{ trans('pages.settings.teams.fields.status') }}</dt>
            <dd class="text-lg font-semibold" :class="weekday.is_ended ? 'text-red-600' : (weekday.active ? 'text-green-600' : 'text-gray-500')">
              {{ weekday.active ? trans('pages.settings.teams.status.active') : trans('pages.settings.teams.status.inactive') }}
            </dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-muted-foreground">{{ trans('pages.settings.weekdays.fields.date') }} (Start)</dt>
            <dd class="text-lg font-semibold">{{ weekday.event_start ?? '—' }}</dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-muted-foreground">{{ trans('pages.settings.weekdays.fields.date') }} (End)</dt>
            <dd class="text-lg font-semibold">{{ weekday.event_end ?? '—' }}</dd>
          </div>
          <div class="sm:col-span-2">
            <dt class="text-sm font-medium text-muted-foreground">{{ trans('pages.settings.weekdays.fields.time_from_to') }}</dt>
            <dd class="text-lg font-semibold">{{ weekday.start_time }} - {{ weekday.end_time }}</dd>
          </div>
        </dl>

        <Separator class="my-6" />

        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold">{{ trans('pages.settings.weekdays.fields.exclusions') }}</h3>
            <Dialog v-model:open="open">
              <DialogTrigger as-child>
                <Button size="sm">{{ trans('pages.settings.weekdays.actions.add_exclusion') }}</Button>
              </DialogTrigger>
              <DialogContent>
                <DialogHeader>
                  <DialogTitle>{{ trans('pages.settings.weekdays.actions.add_exclusion') }}</DialogTitle>
                </DialogHeader>
                <div class="space-y-4 py-4">
                  <div class="space-y-2">
                    <Label for="date">{{ trans('pages.settings.weekdays.fields.date') }}</Label>
                    <Input id="date" v-model="exclusionDate" type="date" />
                  </div>
                </div>
                <DialogFooter>
                  <Button variant="outline" @click="open = false">{{ trans('pages.settings.locations.actions.cancel') }}</Button>
                  <Button @click="addExcluded">{{ trans('pages.settings.locations.actions.create') }}</Button>
                </DialogFooter>
              </DialogContent>
            </Dialog>
          </div>

          <ul class="divide-y rounded-md border">
            <li v-if="(weekday.exclusions ?? []).length === 0" class="p-4 text-center text-sm text-muted-foreground">
              {{ trans('pages.settings.weekdays.exclusions.none') }}
            </li>
            <li v-for="ex in (weekday.exclusions ?? [])" :key="ex.id" class="flex items-center justify-between p-4">
              <div class="flex flex-col">
                <span class="font-medium">{{ ex.excluded_date_formatted ?? ex.excluded_date }}</span>
                <span class="text-xs text-muted-foreground">ID: {{ ex.id }}</span>
              </div>
              <Button variant="ghost" size="icon" class="text-destructive hover:bg-destructive/10" @click="confirmRemoveExcl(ex.id)">
                <Trash2 class="h-4 w-4" />
              </Button>
            </li>
          </ul>
        </div>
      </CardContent>
    </Card>

    <div v-else class="p-4 text-sm text-muted-foreground">Loading...</div>

    <DeleteConfirmationDialog
      v-model:open="deleteDialogOpen"
      :title="trans('pages.settings.weekdays.delete.title')"
      :description="trans('pages.settings.weekdays.delete.description')"
      @confirm="del"
    />

    <DeleteConfirmationDialog
      v-model:open="deleteExclusionDialogOpen"
      :title="trans('pages.settings.weekdays.messages.exclusion_removed')"
      :description="trans('ui.are_you_sure')"
      @confirm="removeExcluded"
    />
  </SidebarLayout>
</template>
