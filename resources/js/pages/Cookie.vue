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
const t = computed(() => page.props.i18n?.trans?.pages?.cookie ?? {})
</script>

<template>
  <PublicLayout>
  <div class="mx-auto w-full max-w-3xl px-4 py-10 sm:px-6">
    <Head :title="t.title ?? 'Cookie policy'" />

    <header class="mb-8 space-y-2">
      <h1 class="text-3xl font-bold tracking-tight">{{ t.title ?? 'Cookie Policy' }}</h1>
      <p class="text-sm text-muted-foreground">{{ t.updated ?? 'Last updated' }}</p>
    </header>

    <!-- Quick summary -->
    <section class="mb-8 rounded-lg border p-4 bg-background/50">
      <h2 class="text-lg font-semibold">{{ t.summary_title ?? 'In short' }}</h2>
      <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-muted-foreground">
        <li>{{ t.summary?.essential }}</li>
        <li>{{ t.summary?.nomarketing }}</li>
        <li>{{ t.summary?.stripe }}</li>
      </ul>
    </section>

    <!-- Table of contents -->
    <nav class="mb-8 rounded-lg border p-4">
      <h2 class="text-lg font-semibold">{{ t.toc?.title ?? 'Contents' }}</h2>
      <ul class="mt-3 grid gap-2 text-sm text-muted-foreground">
        <li><a class="hover:underline" href="#what">{{ t.toc?.what ?? 'What are cookies?' }}</a></li>
        <li><a class="hover:underline" href="#types">{{ t.toc?.types ?? 'Types of cookies we use' }}</a></li>
        <li><a class="hover:underline" href="#list">{{ t.toc?.list ?? 'Cookies we use' }}</a></li>
        <li><a class="hover:underline" href="#third">{{ t.toc?.third ?? 'Third‑party cookies (Stripe)' }}</a></li>
        <li><a class="hover:underline" href="#manage">{{ t.toc?.manage ?? 'How to manage cookies' }}</a></li>
        <li><a class="hover:underline" href="#withdraw">{{ t.toc?.withdraw ?? 'Withdraw consent' }}</a></li>
        <li><a class="hover:underline" href="#contact">{{ t.toc?.contact ?? 'Contact' }}</a></li>
        <li><a class="hover:underline" href="#changes">{{ t.toc?.changes ?? 'Changes to this policy' }}</a></li>
      </ul>
    </nav>

    <section id="what" class="mb-8 space-y-2">
      <h2 class="text-xl font-semibold">{{ t.what?.heading ?? 'What are cookies?' }}</h2>
      <p class="text-sm text-muted-foreground">{{ t.what?.body }}</p>
    </section>

    <section id="types" class="mb-8 space-y-2">
      <h2 class="text-xl font-semibold">{{ t.types?.heading ?? 'Types of cookies we use' }}</h2>
      <ul class="list-disc space-y-2 pl-5 text-sm text-muted-foreground">
        <li><span class="text-foreground font-medium">{{ t.types?.items?.necessary?.split(':')[0] ?? 'Strictly necessary' }}</span>: {{ t.types?.items?.necessary?.split(':')[1] ?? 'required for core functionality such as authentication and security.' }}</li>
        <li><span class="text-foreground font-medium">{{ t.types?.items?.prefs?.split(':')[0] ?? 'Preferences' }}</span>: {{ t.types?.items?.prefs?.split(':')[1] ?? 'remembers your appearance (light/dark/system) and UI state like the sidebar.' }}</li>
        <li><span class="text-foreground font-medium">{{ t.types?.items?.analytics?.split(':')[0] ?? 'Analytics/Marketing' }}</span>: {{ t.types?.items?.analytics?.split(':')[1] ?? 'not used.' }}</li>
      </ul>
    </section>

    <section id="list" class="mb-8 space-y-2">
      <h2 class="text-xl font-semibold">{{ t.list?.heading ?? 'Cookies we use' }}</h2>
      <div class="overflow-hidden rounded-lg border">
        <div class="divide-y divide-white/10">
          <div class="p-4 text-sm">
            <div class="font-medium text-foreground">XSRF-TOKEN</div>
            <div class="text-muted-foreground">{{ t.list?.xsrf }}</div>
          </div>
          <div class="p-4 text-sm">
            <div class="font-medium text-foreground">laravel_session</div>
            <div class="text-muted-foreground">{{ t.list?.session }}</div>
          </div>
          <div class="p-4 text-sm">
            <div class="font-medium text-foreground">appearance</div>
            <div class="text-muted-foreground">{{ t.list?.appearance }}</div>
          </div>
          <div class="p-4 text-sm">
            <div class="font-medium text-foreground">sidebar_state</div>
            <div class="text-muted-foreground">{{ t.list?.sidebar }}</div>
          </div>
          <div class="p-4 text-sm">
            <div class="font-medium text-foreground">laravel_cookie_consent</div>
            <div class="text-muted-foreground">{{ t.list?.consent }}</div>
          </div>
        </div>
      </div>
      <p class="mt-3 text-xs text-muted-foreground">{{ t.list?.note }}</p>
    </section>

    <section id="third" class="mb-8 space-y-2">
      <h2 class="text-xl font-semibold">{{ t.third?.heading ?? 'Third‑party cookies (Stripe)' }}</h2>
      <p class="text-sm text-muted-foreground">
        {{ t.third?.body }}
        See <a href="https://stripe.com/cookies-policy/legal" target="_blank" rel="noopener noreferrer" class="underline">stripe.com/cookies-policy</a> and
        <a href="https://stripe.com/privacy" target="_blank" rel="noopener noreferrer" class="underline">stripe.com/privacy</a> for details.
      </p>
    </section>

    <section id="manage" class="mb-8 space-y-2">
      <h2 class="text-xl font-semibold">{{ t.manage?.heading ?? 'How to manage cookies' }}</h2>
      <p class="text-sm text-muted-foreground">{{ t.manage?.body }}</p>
      <ul class="list-disc space-y-1 pl-5 text-sm text-muted-foreground">
        <li>Chrome: Settings → Privacy and security → Cookies and other site data</li>
        <li>Safari: Preferences → Privacy</li>
        <li>Firefox: Preferences → Privacy & Security</li>
        <li>Edge: Settings → Cookies and site permissions</li>
      </ul>
    </section>

    <section id="withdraw" class="mb-8 space-y-2">
      <h2 class="text-xl font-semibold">{{ t.withdraw?.heading ?? 'Withdraw consent' }}</h2>
      <p class="text-sm text-muted-foreground">{{ t.withdraw?.body }}</p>
    </section>

    <section id="contact" class="mb-8 space-y-2">
      <h2 class="text-xl font-semibold">{{ t.contact?.heading ?? 'Contact' }}</h2>
      <p class="text-sm text-muted-foreground">{{ t.contact?.body }}</p>
    </section>

    <section id="changes" class="mb-8 space-y-2">
      <h2 class="text-xl font-semibold">{{ t.changes?.heading ?? 'Changes to this policy' }}</h2>
      <p class="text-sm text-muted-foreground">{{ t.changes?.body }}</p>
    </section>
  </div>
  </PublicLayout>

</template>

<style scoped></style>
