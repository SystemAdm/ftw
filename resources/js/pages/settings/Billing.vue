<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue'
import { BreadcrumbItemType } from '@/types'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import { Card } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { trans } from 'laravel-vue-i18n'

type Props = {
  hasActiveSubscription: boolean
  onGracePeriod: boolean
  stripePortalEnabled: boolean
  priceId?: string
  time_left: string | null
  next_billing_date: string | null
}

const props = defineProps<Props>()
const page = usePage<PageProps>()

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

const formattedBillingDate = computed(() => {
  if (!props.next_billing_date) return null
  return new Date(props.next_billing_date).toLocaleDateString(page.props.i18n.locale, {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  })
})

const breadcrumbs = computed<BreadcrumbItemType[]>(() => [
    {
        title: trans('pages.settings.billing.membership'),
        href: '/settings/billing',
    },
]);
</script>

<template>
  <SidebarLayout :breadcrumbs="breadcrumbs">
    <Head :title="trans('pages.settings.billing.membership')" />

    <div class="space-y-6">
      <h1 class="text-2xl font-bold tracking-tight">{{ trans('pages.settings.profile.title') }}</h1>

      <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <Card class="p-6 space-y-4">
          <h2 class="text-lg font-semibold">{{ trans('pages.settings.billing.membership') }}</h2>

          <div v-if="props.hasActiveSubscription" class="text-sm font-medium">
            {{ trans('pages.settings.billing.status') }}:
            <span :class="props.onGracePeriod ? 'text-yellow-600' : 'text-green-600'">
              {{ props.onGracePeriod ? trans('pages.settings.billing.cancelling') : trans('pages.settings.billing.active') }}
            </span>
          </div>

          <div v-else class="text-sm font-medium text-gray-500">
            {{ trans('pages.settings.billing.not_subscribed') }}
          </div>

          <div v-if="props.hasActiveSubscription" class="text-sm text-gray-500">
            <div v-if="props.time_left" class="font-medium text-gray-900 dark:text-gray-100">
              {{ trans('pages.settings.billing.time_left') }}: {{ props.time_left }}
            </div>

            <div v-if="formattedBillingDate" class="mt-1">
              <template v-if="props.onGracePeriod">
                {{ trans('pages.settings.billing.ends_on') }}: {{ formattedBillingDate }}
              </template>
              <template v-else>
                {{ trans('pages.settings.billing.next_renewal') }}: {{ formattedBillingDate }}
              </template>
            </div>
          </div>

          <div v-if="!props.hasActiveSubscription" class="mt-2 text-xs text-yellow-600 italic">
            {{ trans('pages.settings.billing.note') }}
          </div>

          <div class="flex gap-3">
            <form @submit.prevent="subscribe" class="flex items-center gap-3">
              <input
                class="hidden"
                type="text"
                name="price"
                v-model="checkoutForm.price"
              />
              <Button type="submit" :disabled="checkoutForm.processing || !checkoutForm.price">
                {{ checkoutForm.processing ? trans('pages.settings.billing.redirecting') : (props.hasActiveSubscription ? trans('pages.settings.billing.change_plan') : trans('pages.settings.billing.subscribe')) }}
              </Button>
            </form>

            <form v-if="props.stripePortalEnabled && props.hasActiveSubscription" @submit.prevent="openPortal">
              <Button type="submit" variant="secondary" :disabled="portalForm.processing">
                {{ portalForm.processing ? trans('pages.settings.billing.opening') : trans('pages.settings.billing.manage_subscription') }}
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
