<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import PublicLayout from '@/components/layouts/PublicLayout.vue'

type I18n = {
  i18n: {
    trans: {
      pages: Record<string, any>
    }
  }
}

const page = usePage<I18n>()
const t = computed(() => page.props.i18n?.trans?.pages?.privacy ?? {})
</script>

<template>
  <PublicLayout>
  <div class="mx-auto w-full max-w-3xl px-4 py-10 sm:px-6">
    <Head :title="t.title ?? 'Privacy notice'" />

    <header class="mb-8 space-y-2">
      <h1 class="text-3xl font-bold tracking-tight">{{ t.title ?? 'Privacy Notice' }}</h1>
      <p class="text-sm text-muted-foreground">{{ t.updated ?? 'Last updated' }}</p>
    </header>

    <!-- Quick summary -->
    <section class="mb-8 rounded-lg border p-4 bg-background/50">
      <h2 class="text-lg font-semibold">{{ t.summary_title ?? 'In short' }}</h2>
      <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-muted-foreground">
        <li>{{ t.summary?.minimal ?? 'We collect only what we need to run your account and membership.' }}</li>
        <li>{{ t.summary?.sell ?? 'We never sell your data.' }}</li>
        <li>{{ t.summary?.stripe ?? 'Payments are processed by Stripe; card data never touches our servers.' }}</li>
        <li>{{ t.summary?.rights ?? 'You can access, correct, or delete your data at any time.' }}</li>
      </ul>
    </section>

    <!-- Table of contents -->
    <nav class="mb-8 rounded-lg border p-4">
      <h2 class="text-lg font-semibold">{{ t.toc?.title ?? 'Contents' }}</h2>
      <ul class="mt-3 grid gap-2 text-sm text-muted-foreground">
        <li><a class="hover:underline" href="#who">{{ t.toc?.who ?? 'Who we are' }}</a></li>
        <li><a class="hover:underline" href="#data">{{ t.toc?.data ?? 'Data we collect' }}</a></li>
        <li><a class="hover:underline" href="#use">{{ t.toc?.use ?? 'How we use your data' }}</a></li>
        <li><a class="hover:underline" href="#legal">{{ t.toc?.legal ?? 'Legal bases' }}</a></li>
        <li><a class="hover:underline" href="#share">{{ t.toc?.share ?? 'Sharing' }}</a></li>
        <li><a class="hover:underline" href="#payments">{{ t.toc?.payments ?? 'Payments (Stripe)' }}</a></li>
        <li><a class="hover:underline" href="#cookies">{{ t.toc?.cookies ?? 'Cookies' }}</a></li>
        <li><a class="hover:underline" href="#retention">{{ t.toc?.retention ?? 'Retention' }}</a></li>
        <li><a class="hover:underline" href="#rights">{{ t.toc?.rights ?? 'Your rights' }}</a></li>
        <li><a class="hover:underline" href="#children">{{ t.toc?.children ?? 'Children' }}</a></li>
        <li><a class="hover:underline" href="#transfers">{{ t.toc?.transfers ?? 'International transfers' }}</a></li>
        <li><a class="hover:underline" href="#security">{{ t.toc?.security ?? 'Security' }}</a></li>
        <li><a class="hover:underline" href="#contact">{{ t.toc?.contact ?? 'Contact' }}</a></li>
        <li><a class="hover:underline" href="#changes">{{ t.toc?.changes ?? 'Changes to this notice' }}</a></li>
      </ul>
    </nav>

    <section id="who" class="mb-8 space-y-2">
      <h2 class="text-xl font-semibold">{{ t.who?.heading ?? 'Who we are' }}</h2>
      <p class="text-sm text-muted-foreground">{{ t.who?.body }}</p>
    </section>

    <section id="data" class="mb-8 space-y-2">
      <h2 class="text-xl font-semibold">{{ t.data?.heading ?? 'Data we collect' }}</h2>
      <ul class="list-disc space-y-2 pl-5 text-sm text-muted-foreground">
        <li>{{ t.data?.items?.account }}</li>
        <li>{{ t.data?.items?.profile }}</li>
        <li>{{ t.data?.items?.providers }}</li>
        <li>{{ t.data?.items?.billing }}</li>
        <li>{{ t.data?.items?.usage }}</li>
        <li>{{ t.data?.items?.contact }}</li>
      </ul>
    </section>

    <section id="use" class="mb-8 space-y-2">
      <h2 class="text-xl font-semibold">{{ t.use?.heading ?? 'How we use your data' }}</h2>
      <ul class="list-disc space-y-2 pl-5 text-sm text-muted-foreground">
        <li>{{ t.use?.items?.account }}</li>
        <li>{{ t.use?.items?.subscription }}</li>
        <li>{{ t.use?.items?.features }}</li>
        <li>{{ t.use?.items?.security }}</li>
        <li>{{ t.use?.items?.comms }}</li>
      </ul>
    </section>

    <section id="legal" class="mb-8 space-y-2">
      <h2 class="text-xl font-semibold">{{ t.legal?.heading ?? 'Legal bases' }}</h2>
      <ul class="list-disc space-y-2 pl-5 text-sm text-muted-foreground">
        <li>{{ t.legal?.items?.contract }}</li>
        <li>{{ t.legal?.items?.legitimate }}</li>
        <li>{{ t.legal?.items?.consent }}</li>
        <li>{{ t.legal?.items?.obligation }}</li>
      </ul>
    </section>

    <section id="share" class="mb-8 space-y-2">
      <h2 class="text-xl font-semibold">{{ t.share?.heading ?? 'Sharing' }}</h2>
      <p class="text-sm text-muted-foreground">{{ t.share?.body }}</p>
    </section>

    <section id="payments" class="mb-8 space-y-2">
      <h2 class="text-xl font-semibold">{{ t.payments?.heading ?? 'Payments (Stripe)' }}</h2>
      <p class="text-sm text-muted-foreground">
        {{ t.payments?.body }}
        <a href="https://stripe.com/privacy" target="_blank" rel="noopener noreferrer" class="underline">stripe.com/privacy</a>.
      </p>
    </section>

    <section id="cookies" class="mb-8 space-y-2">
      <h2 class="text-xl font-semibold">{{ t.cookies?.heading ?? 'Cookies' }}</h2>
      <p class="text-sm text-muted-foreground">
        {{ t.cookies?.body }}
        <a href="/cookie" class="underline">{{ t.cookies?.cookie_policy_link ?? 'Cookie policy' }}</a>.
      </p>
    </section>

    <section id="retention" class="mb-8 space-y-2">
      <h2 class="text-xl font-semibold">{{ t.retention?.heading ?? 'Retention' }}</h2>
      <p class="text-sm text-muted-foreground">{{ t.retention?.body }}</p>
    </section>

    <section id="rights" class="mb-8 space-y-2">
      <h2 class="text-xl font-semibold">{{ t.rights?.heading ?? 'Your rights' }}</h2>
      <ul class="list-disc space-y-2 pl-5 text-sm text-muted-foreground">
        <li>{{ t.rights?.items?.access }}</li>
        <li>{{ t.rights?.items?.rectify }}</li>
        <li>{{ t.rights?.items?.erasure }}</li>
        <li>{{ t.rights?.items?.restrict }}</li>
        <li>{{ t.rights?.items?.port }}</li>
        <li>{{ t.rights?.items?.withdraw }}</li>
      </ul>
      <p class="text-sm text-muted-foreground">
        {{ t.rights?.contact }}
      </p>
    </section>

    <section id="children" class="mb-8 space-y-2">
      <h2 class="text-xl font-semibold">{{ t.children?.heading ?? 'Children' }}</h2>
      <p class="text-sm text-muted-foreground">{{ t.children?.body }}</p>
    </section>

    <section id="transfers" class="mb-8 space-y-2">
      <h2 class="text-xl font-semibold">{{ t.transfers?.heading ?? 'International transfers' }}</h2>
      <p class="text-sm text-muted-foreground">{{ t.transfers?.body }}</p>
    </section>

    <section id="security" class="mb-8 space-y-2">
      <h2 class="text-xl font-semibold">{{ t.security?.heading ?? 'Security' }}</h2>
      <p class="text-sm text-muted-foreground">{{ t.security?.body }}</p>
    </section>

    <section id="contact" class="mb-8 space-y-2">
      <h2 class="text-xl font-semibold">{{ t.contact?.heading ?? 'Contact' }}</h2>
      <p class="text-sm text-muted-foreground">
        {{ t.contact?.email_prefix ?? 'Email:' }} <a href="mailto:web@spillhuset.com" class="underline">web@spillhuset.com</a> ·
        <a href="/contact" class="underline">{{ t.contact?.form ?? 'Contact form' }}</a>
      </p>
      <p class="text-sm text-muted-foreground">{{ t.contact?.address }}</p>
    </section>

    <section id="changes" class="mb-8 space-y-2">
      <h2 class="text-xl font-semibold">{{ t.changes?.heading ?? 'Changes to this notice' }}</h2>
      <p class="text-sm text-muted-foreground">{{ t.changes?.body }}</p>
    </section>
  </div>
  </PublicLayout>
</template>

<style scoped></style>
