<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'

const form = useForm({
  name: '',
  email: '',
  subject: '',
  message: '',
})

function submit() {
  form.post('/contact', {
    onSuccess: () => {
      form.reset('name', 'email', 'subject', 'message')
    },
  })
}
</script>

<template>
  <div class="mx-auto w-full max-w-3xl px-4 py-10 sm:px-6">
    <Head title="Contact" />
    <h1 class="text-2xl font-bold tracking-tight mb-4">Contact</h1>

    <form @submit.prevent="submit" class="space-y-4">
      <div class="space-y-2">
        <Label for="name">Name</Label>
        <Input id="name" v-model="form.name" />
        <div v-if="form.errors.name" class="text-sm text-red-600">{{ form.errors.name }}</div>
      </div>
      <div class="space-y-2">
        <Label for="email">Email</Label>
        <Input id="email" type="email" v-model="form.email" />
        <div v-if="form.errors.email" class="text-sm text-red-600">{{ form.errors.email }}</div>
      </div>
      <div class="space-y-2">
        <Label for="subject">Subject</Label>
        <Input id="subject" v-model="form.subject" />
        <div v-if="form.errors.subject" class="text-sm text-red-600">{{ form.errors.subject }}</div>
      </div>
      <div class="space-y-2">
        <Label for="message">Message</Label>
        <Textarea id="message" v-model="form.message" rows="6" />
        <div v-if="form.errors.message" class="text-sm text-red-600">{{ form.errors.message }}</div>
      </div>
      <Button type="submit" :disabled="form.processing">{{ form.processing ? 'Sending…' : 'Send' }}</Button>
    </form>
  </div>

</template>

<style scoped></style>
