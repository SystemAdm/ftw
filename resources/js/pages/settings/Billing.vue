<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import { Card } from '@/components/ui/card'
import { Button } from '@/components/ui/button'

type Props = {
  hasActiveSubscription: boolean
  onGracePeriod: boolean
  stripePortalEnabled: boolean
  priceId?: string
}

const props = defineProps<Props>()

const checkoutForm = useForm({
  price: props.priceId ?? '',
})

function subscribe() {
  checkoutForm.post('/settings/billing/checkout')
}

const portalForm = useForm({})
function openPortal() {
  portalForm.post('/settings/billing/portal')
}

const statusText = computed(() => {
  if (props.hasActiveSubscription) {
    return props.onGracePeriod ? 'Active (grace period)' : 'Active'
  }
  return 'Not subscribed'
})
</script>

<template>
  <SidebarLayout>
    <Head title="Billing" />

    <div class="space-y-6">
      <h1 class="text-2xl font-bold tracking-tight">Settings</h1>

      <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <Card class="p-6 space-y-4">
          <h2 class="text-lg font-semibold">Membership</h2>

          <div class="text-sm text-gray-600 dark:text-gray-300">Status: {{ statusText }}</div>

          <div class="flex gap-3">
            <form @submit.prevent="subscribe" class="flex items-center gap-3">
              <input
                class="hidden"
                type="text"
                name="price"
                v-model="checkoutForm.price"
              />
              <Button type="submit" :disabled="checkoutForm.processing || !checkoutForm.price">
                {{ checkoutForm.processing ? 'Redirecting…' : (props.hasActiveSubscription ? 'Change Plan' : 'Subscribe') }}
              </Button>
            </form>

            <form v-if="props.stripePortalEnabled && props.hasActiveSubscription" @submit.prevent="openPortal">
              <Button type="submit" variant="secondary" :disabled="portalForm.processing">
                {{ portalForm.processing ? 'Opening…' : 'Manage Subscription' }}
              </Button>
            </form>
          </div>

          <div v-if="checkoutForm.errors.price" class="text-sm text-red-600">{{ checkoutForm.errors.price }}</div>
        </Card>
      </div>
    </div>
  </SidebarLayout>

</template>

<style scoped></style>
