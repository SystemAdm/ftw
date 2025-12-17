<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import PublicLayout from '@/components/layouts/PublicLayout.vue'
</script>

<template>
  <PublicLayout>
  <div class="mx-auto w-full max-w-3xl px-4 py-10 sm:px-6">
    <Head title="Cookie policy" />

    <header class="mb-8 space-y-2">
      <h1 class="text-3xl font-bold tracking-tight">Cookie Policy</h1>
      <p class="text-sm text-muted-foreground">How Spillhuset uses cookies and similar technologies. Last updated: 2025‑12‑17</p>
    </header>

    <!-- Quick summary -->
    <section class="mb-8 rounded-lg border p-4 bg-background/50">
      <h2 class="text-lg font-semibold">In short</h2>
      <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-muted-foreground">
        <li>We use only essential and basic preference cookies to run the site.</li>
        <li>No marketing/advertising cookies.</li>
        <li>Payments are processed by Stripe; their pages may set their own cookies.</li>
      </ul>
    </section>

    <!-- Table of contents -->
    <nav class="mb-8 rounded-lg border p-4">
      <h2 class="text-lg font-semibold">Contents</h2>
      <ul class="mt-3 grid gap-2 text-sm text-muted-foreground">
        <li><a class="hover:underline" href="#what">What are cookies?</a></li>
        <li><a class="hover:underline" href="#types">Types of cookies we use</a></li>
        <li><a class="hover:underline" href="#list">Cookies we use</a></li>
        <li><a class="hover:underline" href="#third">Third‑party cookies (Stripe)</a></li>
        <li><a class="hover:underline" href="#manage">How to manage cookies</a></li>
        <li><a class="hover:underline" href="#withdraw">Withdraw consent</a></li>
        <li><a class="hover:underline" href="#contact">Contact</a></li>
        <li><a class="hover:underline" href="#changes">Changes to this policy</a></li>
      </ul>
    </nav>

    <section id="what" class="mb-8 space-y-2">
      <h2 class="text-xl font-semibold">What are cookies?</h2>
      <p class="text-sm text-muted-foreground">
        Cookies are small text files stored on your device by your browser. They help websites remember information
        about your visit, like keeping you signed in or remembering preferences.
      </p>
    </section>

    <section id="types" class="mb-8 space-y-2">
      <h2 class="text-xl font-semibold">Types of cookies we use</h2>
      <ul class="list-disc space-y-2 pl-5 text-sm text-muted-foreground">
        <li><span class="text-foreground font-medium">Strictly necessary</span>: required for core functionality such as authentication and security.</li>
        <li><span class="text-foreground font-medium">Preferences</span>: remembers your appearance (light/dark/system) and UI state like the sidebar.</li>
        <li><span class="text-foreground font-medium">Analytics/Marketing</span>: not used.</li>
      </ul>
    </section>

    <section id="list" class="mb-8 space-y-2">
      <h2 class="text-xl font-semibold">Cookies we use</h2>
      <div class="overflow-hidden rounded-lg border">
        <div class="divide-y divide-white/10">
          <div class="p-4 text-sm">
            <div class="font-medium text-foreground">XSRF-TOKEN</div>
            <div class="text-muted-foreground">Helps protect against cross‑site request forgery. Required for forms and authentication.</div>
          </div>
          <div class="p-4 text-sm">
            <div class="font-medium text-foreground">laravel_session</div>
            <div class="text-muted-foreground">Session cookie that keeps you signed in and remembers state between requests.</div>
          </div>
          <div class="p-4 text-sm">
            <div class="font-medium text-foreground">appearance</div>
            <div class="text-muted-foreground">Stores your theme preference (light/dark/system). Set by our middleware.</div>
          </div>
          <div class="p-4 text-sm">
            <div class="font-medium text-foreground">sidebar_state</div>
            <div class="text-muted-foreground">Remembers whether the sidebar is open or collapsed.</div>
          </div>
          <div class="p-4 text-sm">
            <div class="font-medium text-foreground">laravel_cookie_consent</div>
            <div class="text-muted-foreground">Records whether you dismissed/accepted the cookie notice.</div>
          </div>
        </div>
      </div>
      <p class="mt-3 text-xs text-muted-foreground">Note: Cookie names may vary slightly between environments.</p>
    </section>

    <section id="third" class="mb-8 space-y-2">
      <h2 class="text-xl font-semibold">Third‑party cookies (Stripe)</h2>
      <p class="text-sm text-muted-foreground">
        When you start the subscription checkout, you may be redirected to Stripe. Stripe may set its own cookies to
        operate their checkout experience. See <a href="https://stripe.com/cookies-policy/legal" target="_blank" rel="noopener noreferrer" class="underline">stripe.com/cookies-policy</a> and
        <a href="https://stripe.com/privacy" target="_blank" rel="noopener noreferrer" class="underline">stripe.com/privacy</a> for details.
      </p>
    </section>

    <section id="manage" class="mb-8 space-y-2">
      <h2 class="text-xl font-semibold">How to manage cookies</h2>
      <p class="text-sm text-muted-foreground">
        You can control cookies in your browser settings and delete existing cookies. Disabling necessary cookies may
        affect site functionality (e.g., sign‑in will not work).
      </p>
      <ul class="list-disc space-y-1 pl-5 text-sm text-muted-foreground">
        <li>Chrome: Settings → Privacy and security → Cookies and other site data</li>
        <li>Safari: Preferences → Privacy</li>
        <li>Firefox: Preferences → Privacy & Security</li>
        <li>Edge: Settings → Cookies and site permissions</li>
      </ul>
    </section>

    <section id="withdraw" class="mb-8 space-y-2">
      <h2 class="text-xl font-semibold">Withdraw consent</h2>
      <p class="text-sm text-muted-foreground">
        If you consented to optional cookies in the past, you can withdraw your consent at any time by clearing cookies
        in your browser or using cookie controls if shown.
      </p>
    </section>

    <section id="contact" class="mb-8 space-y-2">
      <h2 class="text-xl font-semibold">Contact</h2>
      <p class="text-sm text-muted-foreground">
        Questions about this policy? Email <a href="mailto:web@spillhuset.com" class="underline">web@spillhuset.com</a> or use our
        <a href="/contact" class="underline">contact form</a>.
      </p>
    </section>

    <section id="changes" class="mb-8 space-y-2">
      <h2 class="text-xl font-semibold">Changes to this policy</h2>
      <p class="text-sm text-muted-foreground">We may update this policy from time to time. Please review it periodically for changes.</p>
    </section>
  </div>
  </PublicLayout>

</template>

<style scoped></style>
