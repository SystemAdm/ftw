<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import PublicLayout from '@/components/layouts/PublicLayout.vue'
</script>

<template>
  <PublicLayout>
  <div class="mx-auto w-full max-w-3xl px-4 py-10 sm:px-6">
    <Head title="Terms & legal" />

    <header class="mb-8 space-y-2">
      <h1 class="text-3xl font-bold tracking-tight">Terms & Legal</h1>
      <p class="text-sm text-muted-foreground">
        Easy to read overview of our key legal terms. Last updated: 2025‑12‑17
      </p>
    </header>

    <!-- Quick summary -->
    <section class="mb-8 rounded-lg border p-4 bg-background/50">
      <h2 class="text-lg font-semibold">In short</h2>
      <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-muted-foreground">
        <li>Use the website respectfully and lawfully. Don’t abuse or disrupt the service.</li>
        <li>Content we publish is owned by us or our partners; your content must be yours or licensed.</li>
        <li>Membership payments are handled by Stripe; see our Privacy notice for how we handle data.</li>
      </ul>
    </section>

    <!-- Table of contents -->
    <nav class="mb-8 rounded-lg border p-4">
      <h2 class="text-lg font-semibold">Contents</h2>
      <ul class="mt-3 grid gap-2 text-sm text-muted-foreground">
        <li><a class="hover:underline" href="#eula">End User License Agreement (EULA)</a></li>
        <li><a class="hover:underline" href="#website-terms">Terms of Use for Website</a></li>
        <li><a class="hover:underline" href="#privacy">Online Privacy Policy</a></li>
      </ul>
    </nav>

    <!-- EULA -->
    <section id="eula" class="mb-10 space-y-3">
      <h2 class="text-xl font-semibold">End User License Agreement (EULA)</h2>
      <p class="text-sm text-muted-foreground">
        These are the terms that govern your use of any software, digital content, or downloadable
        materials we may provide. The EULA generally grants you a personal, non‑transferable,
        revocable license to use such materials for their intended purpose and prohibits reverse
        engineering, redistribution, or commercial exploitation without permission.
      </p>
      <ul class="list-disc space-y-2 pl-5 text-sm text-muted-foreground">
        <li>License: limited, revocable, non‑exclusive.</li>
        <li>Restrictions: no reverse engineering, resale, or unlawful use.</li>
        <li>IP: all rights remain with Spillhuset and/or licensors.</li>
        <li>Termination: we may terminate access for breach of the EULA.</li>
      </ul>
    </section>

    <!-- Website Terms -->
    <section id="website-terms" class="mb-10 space-y-3">
      <h2 class="text-xl font-semibold">Terms of Use for Website</h2>
      <p class="text-sm text-muted-foreground">
        These terms apply to your use of our site and services accessible at ftw.test and related
        domains. By accessing or using the site, you agree to comply with these terms.
      </p>
      <ul class="list-disc space-y-2 pl-5 text-sm text-muted-foreground">
        <li>Acceptable Use: do not disrupt the service, attempt unauthorized access, or violate laws.</li>
        <li>Accounts: keep your credentials secure and your information accurate.</li>
        <li>Content: respect copyright and other intellectual property rights.</li>
        <li>Disclaimers: service is provided “as is”; availability may vary.</li>
        <li>Changes: we may update these terms; continued use means acceptance.</li>
      </ul>
    </section>

    <!-- Privacy Policy (linking to our dedicated page) -->
    <section id="privacy" class="mb-10 space-y-3">
      <h2 class="text-xl font-semibold">Online Privacy Policy</h2>
      <p class="text-sm text-muted-foreground">
        We explain how we collect, use, and protect your data in our
        <a href="/privacy" class="underline">Privacy notice</a>. Highlights:
      </p>
      <ul class="list-disc space-y-2 pl-5 text-sm text-muted-foreground">
        <li>We collect only what is necessary to operate your account and membership.</li>
        <li>We do not sell personal data. Payments are processed by Stripe.</li>
        <li>You can request access, correction, or deletion of your data at any time.</li>
      </ul>
    </section>

    <!-- Contact -->
    <section class="mt-10 space-y-2">
      <h2 class="text-xl font-semibold">Contact</h2>
      <p class="text-sm text-muted-foreground">
        Have questions? <a href="/contact" class="underline">Contact us</a> or email
        <a href="mailto:web@spillhuset.com" class="underline">web@spillhuset.com</a>.
      </p>
    </section>
  </div>
  </PublicLayout>

</template>

<style scoped></style>
