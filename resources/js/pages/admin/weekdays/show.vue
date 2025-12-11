<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { usePage, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { edit as editRoute, index as indexRoute } from '@/routes/admin/weekdays/index';
import { add as addExclusion } from '@/routes/admin/weekdays/exclusions';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ref, computed } from 'vue';

const page = usePage<PageProps>();
const weekday = computed<any>(() => (page.props as any).weekday);

const open = ref(false);
const exclusionDate = ref('');

function addExcluded() {
  if (!exclusionDate.value || !weekday.value?.id) return;
  router.post(
    addExclusion.url(weekday.value.id),
    { excluded_date: exclusionDate.value },
    {
      preserveScroll: true,
      onSuccess: () => {
        router.reload({ only: ['weekday'], preserveScroll: true });
        exclusionDate.value = '';
        open.value = false;
      },
    },
  );
}

function weekdayLabel(n: number): string {
  const names = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
  return names[n] ?? String(n);
}

// Backend now provides Carbon-formatted date as `excluded_date_formatted`
</script>

<template>
  <SidebarLayout>
    <div v-if="weekday" class="mb-4 flex items-center justify-between">
      <h1 class="text-xl font-semibold">Weekday</h1>
      <div class="flex gap-2">
        <Button variant="secondary" @click="router.visit(indexRoute.url())">Back</Button>
        <Button @click="router.visit(editRoute.url(weekday.id))">Edit</Button>
      </div>
    </div>

    <div v-if="weekday" class="grid max-w-xl grid-cols-1 gap-2 rounded border p-4">
      <div class="flex items-center justify-between">
        <div class="text-sm text-gray-500">Day</div>
        <div class="font-medium">{{ weekdayLabel(weekday.weekday) }}</div>
      </div>
      <div class="flex items-center justify-between">
        <div class="text-sm text-gray-500">Team</div>
        <div class="font-medium">{{ weekday.team?.name ?? '—' }}</div>
      </div>
      <div class="flex items-center justify-between">
        <div class="text-sm text-gray-500">Location</div>
        <div class="font-medium">{{ weekday.location?.name ?? '—' }}</div>
      </div>
      <div class="flex items-center justify-between">
        <div class="text-sm text-gray-500">Status</div>
        <div class="font-medium" :class="weekday.is_ended ? 'text-red-600' : (weekday.active ? 'text-green-600' : 'text-gray-500')">
          {{ weekday.status_label }}
        </div>
      </div>
      <div class="flex items-center justify-between">
        <div class="text-sm text-gray-500">Time</div>
        <div class="font-medium">{{ weekday.start_time }} - {{ weekday.end_time }}</div>
      </div>
    </div>

    <div v-if="weekday" class="mt-8 max-w-xl">
      <div class="mb-2 flex items-center justify-between">
        <h2 class="text-lg font-semibold">Excluded Dates</h2>
        <Dialog v-model:open="open">
          <DialogTrigger as-child>
            <Button size="sm">Add exclusion</Button>
          </DialogTrigger>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Add excluded date</DialogTitle>
            </DialogHeader>
            <div>
              <Label class="block text-sm font-medium">Date</Label>
              <Input v-model="exclusionDate" type="date" class="mt-1 w-full" />
            </div>
            <DialogFooter>
              <Button variant="secondary" @click="open = false">Cancel</Button>
              <Button @click="addExcluded">Add</Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
      </div>
      <ul class="divide-y rounded border">
        <li v-if="(weekday.exclusions ?? []).length === 0" class="p-3 text-sm text-gray-500">No exclusions</li>
        <li v-for="ex in (weekday.exclusions ?? [])" :key="ex.id" class="flex items-center justify-between p-3">
          <span>{{ ex.excluded_date_formatted ?? ex.excluded_date }}</span>
          <span class="text-xs text-gray-500">#{{ ex.id }}</span>
        </li>
      </ul>
    </div>

    <div v-else class="p-4 text-sm text-gray-500">Loading...</div>
  </SidebarLayout>
</template>
