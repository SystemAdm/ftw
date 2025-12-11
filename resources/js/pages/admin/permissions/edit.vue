<script setup lang="ts">
import { usePage, router } from '@inertiajs/vue3'
import SidebarLayout from '@/components/layouts/SidebarLayout.vue'
import { reactive } from 'vue'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Field, FieldError, FieldLabel } from '@/components/ui/field'

const page = usePage<PageProps>()
const permission = (page.props as any).permission

const form = reactive({
  name: permission.name as string,
  guard_name: permission.guard_name as string,
})
const errors = reactive<Record<string, string[]>>({})

function submit() {
  router.put(`/admin/permissions/${permission.id}`, form, {
    onError: (err) => Object.assign(errors, err as any),
  })
}
</script>

<template>
  <SidebarLayout>
    <h1 class="mb-4 text-xl font-semibold">Edit Permission</h1>

    <form class="max-w-xl space-y-4" @submit.prevent="submit">
      <Field>
        <FieldLabel>Name</FieldLabel>
        <Input v-model="form.name" :class="{ 'border-red-500': errors.name }" @input="errors.name = [] as any" />
        <FieldError v-if="errors.name">{{ errors.name[0] }}</FieldError>
      </Field>

      <Field>
        <FieldLabel>Guard</FieldLabel>
        <Input v-model="form.guard_name" />
      </Field>

      <div class="flex gap-2">
        <Button>Save</Button>
        <Button variant="secondary" @click.prevent="router.visit(`/admin/permissions/${permission.id}`)">Cancel</Button>
      </div>
    </form>
  </SidebarLayout>
</template>
