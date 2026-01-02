<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { computed, onMounted, ref } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import PublicLayout from '@/components/layouts/PublicLayout.vue'
import { submit as submitRoute } from '@/actions/App/http/controllers/pages/ContactController';

type I18n = {
  i18n: {
    trans: {
      pages: Record<string, any>
    }
  }
}

const page = usePage<I18n>()
const t = computed(() => page.props.i18n?.trans?.pages?.contact ?? {})
const siteKey = computed(() => (page.props as any).captcha?.turnstile_site_key ?? '')
const status = computed(() => (page.props as any).status ?? null)
const error = computed(() => (page.props as any).error ?? null)

// Cloudflare Turnstile integration
const widgetEl = ref<HTMLDivElement | null>(null)
let widgetId: any = null

function loadTurnstile(): Promise<void> {
  return new Promise((resolve) => {
    if ((window as any).turnstile) {
      resolve()
      return
    }
    const s = document.createElement('script')
    s.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js'
    s.async = true
    s.defer = true
    s.onload = () => resolve()
    document.head.appendChild(s)
  })
}

onMounted(async () => {
  if (!siteKey.value) return
  await loadTurnstile()
  // Render widget into the placeholder div; Turnstile will inject a hidden input named 'cf-turnstile-response'
  widgetId = (window as any).turnstile?.render(widgetEl.value, {
    sitekey: siteKey.value,
    appearance: 'always',
    theme: document.documentElement.classList.contains('dark') ? 'dark' : 'auto',
    callback: (token: string) => {
      // Sync token into Inertia form payload
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      ;(form as any)['cf-turnstile-response'] = token
    },
    'error-callback': () => {
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      ;(form as any)['cf-turnstile-response'] = ''
    },
  })
})

const form = useForm({
  name: '',
  email: '',
  subject: '',
  message: '',
  // Turnstile posts via hidden input automatically, but keep a field so Inertia can include it if needed
  'cf-turnstile-response': '' as any,
})

function submit() {
  form.post(submitRoute.url(), {
    onSuccess: () => {
      form.reset('name', 'email', 'subject', 'message')
      try {
        if ((window as any).turnstile && widgetId) {
          (window as any).turnstile.reset(widgetId)
        }
      } catch {}
    },
  })
}
</script>

<template>
  <PublicLayout>
  <div class="mx-auto w-full max-w-3xl px-4 py-10 sm:px-6">
    <Head :title="t.title ?? 'Contact'" />
    <h1 class="text-2xl font-bold tracking-tight mb-4">{{ t.title ?? 'Contact' }}</h1>

    <!-- Success / status message -->
    <div v-if="status" class="mb-4 rounded-md border border-green-500/30 bg-green-500/10 px-4 py-3 text-sm text-green-300" v-html="status">

    </div>
    <!-- Error message -->
    <div v-if="error" class="mb-4 rounded-md border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300" v-html="error">

    </div>

    <form @submit.prevent="submit" class="space-y-4">
      <div class="space-y-2">
        <Label for="name">{{ t.labels?.name ?? 'Name' }}</Label>
        <Input id="name" v-model="form.name" />
        <div v-if="form.errors.name" class="text-sm text-red-600">{{ form.errors.name }}</div>
      </div>
      <div class="space-y-2">
        <Label for="email">{{ t.labels?.email ?? 'Email' }}</Label>
        <Input id="email" type="email" v-model="form.email" />
        <div v-if="form.errors.email" class="text-sm text-red-600">{{ form.errors.email }}</div>
      </div>
      <div class="space-y-2">
        <Label for="subject">{{ t.labels?.subject ?? 'Subject' }}</Label>
        <Input id="subject" v-model="form.subject" />
        <div v-if="form.errors.subject" class="text-sm text-red-600">{{ form.errors.subject }}</div>
      </div>
      <div class="space-y-2">
        <Label for="message">{{ t.labels?.message ?? 'Message' }}</Label>
        <Textarea id="message" v-model="form.message" rows="6" />
        <div v-if="form.errors.message" class="text-sm text-red-600">{{ form.errors.message }}</div>
      </div>
      <!-- Captcha -->
      <div v-if="siteKey" class="space-y-2">
        <div ref="widgetEl" class="cf-turnstile" />
        <div v-if="form.errors['cf-turnstile-response']" class="text-sm text-red-600">{{ form.errors['cf-turnstile-response'] }}</div>
      </div>
      <Button type="submit" :disabled="form.processing">{{ form.processing ? (t.actions?.sending ?? 'Sending…') : (t.actions?.send ?? 'Send') }}</Button>
    </form>
  </div>
  </PublicLayout>

</template>

<style scoped></style>
