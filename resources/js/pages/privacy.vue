<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import PublicLayout from '@/components/layouts/PublicLayout.vue'
</script>

<template>
  <PublicLayout>
  <div class="mx-auto w-full max-w-3xl px-4 py-10 sm:px-6">
    <Head title="Privacy notice" />

    <header class="mb-8 space-y-2">
      <h1 class="text-3xl font-bold tracking-tight">Privacy Notice</h1>
      <p class="text-sm text-muted-foreground">
        Easy to read summary of how Spillhuset handles your data. Last updated: 2025‑12‑17
      </p>
    </header>

    <!-- Quick summary -->
    <section class="mb-8 rounded-lg border p-4 bg-background/50">
      <h2 class="text-lg font-semibold">In short</h2>
      <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-muted-foreground">
        <li>We collect only what we need to run your account and membership.</li>
        <li>We never sell your data.</li>
        <li>Payments are processed by Stripe; card data never touches our servers.</li>
        <li>You can access, correct, or delete your data at any time.</li>
      </ul>
    </section>

    <!-- Table of contents -->
    <nav class="mb-8 rounded-lg border p-4">
      <h2 class="text-lg font-semibold">Contents</h2>
      <ul class="mt-3 grid gap-2 text-sm text-muted-foreground">
        <li><a class="hover:underline" href="#who">Who we are</a></li>
        <li><a class="hover:underline" href="#data">Data we collect</a></li>
        <li><a class="hover:underline" href="#use">How we use your data</a></li>
        <li><a class="hover:underline" href="#legal">Legal bases</a></li>
        <li><a class="hover:underline" href="#share">Sharing</a></li>
        <li><a class="hover:underline" href="#payments">Payments (Stripe)</a></li>
        <li><a class="hover:underline" href="#cookies">Cookies</a></li>
        <li><a class="hover:underline" href="#retention">Retention</a></li>
        <li><a class="hover:underline" href="#rights">Your rights</a></li>
        <li><a class="hover:underline" href="#children">Children</a></li>
        <li><a class="hover:underline" href="#transfers">International transfers</a></li>
        <li><a class="hover:underline" href="#security">Security</a></li>
        <li><a class="hover:underline" href="#contact">Contact</a></li>
        <li><a class="hover:underline" href="#changes">Changes to this notice</a></li>
      </ul>
    </nav>

    <section id="who" class="mb-8 space-y-2">
      <h2 class="text-xl font-semibold">Who we are</h2>
      <p class="text-sm text-muted-foreground">
        Spillhuset (Org.nr: 917616140) provides community events and memberships for gamers.
        Address: Skollerudveien 5, 1353 Bærums Verk, Norway.
      </p>
    </section>

    <section id="data" class="mb-8 space-y-2">
      <h2 class="text-xl font-semibold">Data we collect</h2>
      <ul class="list-disc space-y-2 pl-5 text-sm text-muted-foreground">
        <li>Account: name, email, username, password, avatar.</li>
        <li>Profile (optional): birthday, postal code, appearance preferences.</li>
        <li>Auth providers you connect (e.g., Google, Discord, GitHub): provider IDs.</li>
        <li>Subscription/billing: Stripe customer ID, subscription status, invoices (from Stripe).</li>
        <li>Usage: logs for security and service operation (IP, user agent, session IDs).</li>
        <li>Contact: messages you send via our contact form.</li>
      </ul>
    </section>

    <section id="use" class="mb-8 space-y-2">
      <h2 class="text-xl font-semibold">How we use your data</h2>
      <ul class="list-disc space-y-2 pl-5 text-sm text-muted-foreground">
        <li>To create and manage your account and authentication.</li>
        <li>To process your membership subscription and payments via Stripe.</li>
        <li>To provide features like profile, preferences, and settings.</li>
        <li>To keep the service secure, prevent abuse, and troubleshoot issues.</li>
        <li>To communicate service updates, billing notices, and responses to your requests.</li>
      </ul>
    </section>

    <section id="legal" class="mb-8 space-y-2">
      <h2 class="text-xl font-semibold">Legal bases</h2>
      <ul class="list-disc space-y-2 pl-5 text-sm text-muted-foreground">
        <li>Contract: to provide the service you request (account, subscription, billing).</li>
        <li>Legitimate interests: operate, secure, and improve the service.</li>
        <li>Consent: optional features (e.g., certain cookies) when applicable.</li>
        <li>Legal obligation: invoicing, tax, and accounting requirements.</li>
      </ul>
    </section>

    <section id="share" class="mb-8 space-y-2">
      <h2 class="text-xl font-semibold">Sharing</h2>
      <p class="text-sm text-muted-foreground">
        We do not sell your data. We share data with service providers only to run the service, for example:
        Stripe (payments), email delivery, hosting, and error logging. These providers process data on our behalf
        under agreements consistent with this notice.
      </p>
    </section>

    <section id="payments" class="mb-8 space-y-2">
      <h2 class="text-xl font-semibold">Payments (Stripe)</h2>
      <p class="text-sm text-muted-foreground">
        We use Stripe to process payments. Card data is sent directly to Stripe and never touches our servers.
        We store your Stripe customer ID and subscription details to manage your membership. Learn more at
        <a href="https://stripe.com/privacy" target="_blank" rel="noopener noreferrer" class="underline">stripe.com/privacy</a>.
      </p>
    </section>

    <section id="cookies" class="mb-8 space-y-2">
      <h2 class="text-xl font-semibold">Cookies</h2>
      <p class="text-sm text-muted-foreground">
        We use necessary cookies for sign‑in and security. For more details, see our
        <a href="/cookie" class="underline">Cookie policy</a>.
      </p>
    </section>

    <section id="retention" class="mb-8 space-y-2">
      <h2 class="text-xl font-semibold">Retention</h2>
      <p class="text-sm text-muted-foreground">
        We keep your data only as long as needed for the purposes listed above. If you delete your account,
        we remove or anonymize personal data unless we must keep it longer for legal reasons (e.g., invoices).
      </p>
    </section>

    <section id="rights" class="mb-8 space-y-2">
      <h2 class="text-xl font-semibold">Your rights</h2>
      <ul class="list-disc space-y-2 pl-5 text-sm text-muted-foreground">
        <li>Access: get a copy of your data.</li>
        <li>Correction: fix inaccurate or incomplete data.</li>
        <li>Deletion: request deletion of your data.</li>
        <li>Restriction & objection: limit or object to certain processing.</li>
        <li>Portability: receive your data in a machine‑readable format.</li>
        <li>Withdraw consent where processing relies on consent.</li>
      </ul>
      <p class="text-sm text-muted-foreground">
        To exercise your rights, please <a href="/contact" class="underline">contact us</a>. You also have the right
        to complain to your local data protection authority.
      </p>
    </section>

    <section id="children" class="mb-8 space-y-2">
      <h2 class="text-xl font-semibold">Children</h2>
      <p class="text-sm text-muted-foreground">
        Our events may include youth activities. Where required, we request consent from guardians. If you believe a
        child provided personal data without appropriate consent, please contact us and we will take appropriate action.
      </p>
    </section>

    <section id="transfers" class="mb-8 space-y-2">
      <h2 class="text-xl font-semibold">International transfers</h2>
      <p class="text-sm text-muted-foreground">
        Some providers may process data outside the EEA. When this happens, we use appropriate safeguards such as
        Standard Contractual Clauses or ensure the provider offers an adequate level of protection.
      </p>
    </section>

    <section id="security" class="mb-8 space-y-2">
      <h2 class="text-xl font-semibold">Security</h2>
      <p class="text-sm text-muted-foreground">
        We take reasonable technical and organizational measures to protect your data. No system is 100% secure,
        but we continuously improve our safeguards.
      </p>
    </section>

    <section id="contact" class="mb-8 space-y-2">
      <h2 class="text-xl font-semibold">Contact</h2>
      <p class="text-sm text-muted-foreground">
        Email: <a href="mailto:web@spillhuset.com" class="underline">web@spillhuset.com</a> ·
        <a href="/contact" class="underline">Contact form</a>
      </p>
      <p class="text-sm text-muted-foreground">
        Address: Skollerudveien 5, 1353 Bærums Verk, Norway · Org.nr: 917616140
      </p>
    </section>

    <section id="changes" class="mb-8 space-y-2">
      <h2 class="text-xl font-semibold">Changes to this notice</h2>
      <p class="text-sm text-muted-foreground">
        We will update this page if our practices change. We encourage you to review it periodically.
      </p>
    </section>
  </div>
  </PublicLayout>
</template>

<style scoped></style>
