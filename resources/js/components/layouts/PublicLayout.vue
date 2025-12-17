<script setup lang="ts">
import Footer from '@/components/layouts/Footer.vue'
import { Link } from '@inertiajs/vue3'
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetDescription, SheetTrigger, SheetClose } from '@/components/ui/sheet'
import { Button } from '@/components/ui/button'
</script>

<template>
  <div class="min-h-screen bg-black text-foreground selection:bg-green-500/40">
    <!-- Top nav -->
    <header class="sticky top-0 z-50 mx-auto w-full max-w-7xl bg-black/80 supports-backdrop-filter:bg-black/60 backdrop-blur border-b border-white/10 px-4 py-4 sm:px-6 sm:py-5">
      <div class="relative grid grid-cols-[1fr_auto_1fr] items-center">
        <!-- Left placeholder (ensures center title stays perfectly centered). Shows logo from md+. -->
        <div class="flex items-center pl-1 h-10 w-10">
          <span class="inline-grid   place-items-center rounded-md md:bg-black">
            <img src="/images/Spillhuset.png" alt="Spillhuset logo" class="hidden md:block" />
          </span>
        </div>
        <!-- Center title -->
        <p class="text-center text-lg font-bold tracking-wide sm:text-xl">SPILLHUSET</p>
        <!-- Right hamburger -->
        <div class="flex items-center justify-end pr-1">
          <Sheet v-if="!$page.props.isProduction">
            <SheetTrigger as-child>
              <button type="button" aria-label="Open menu" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-white/10 bg-white/5 hover:bg-white/10 transition">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5">
                  <path fill-rule="evenodd" d="M3.75 6.75A.75.75 0 0 1 4.5 6h15a.75.75 0 0 1 0 1.5h-15a.75.75 0 0 1-.75-.75Zm0 5.25A.75.75 0 0 1 4.5 11h15a.75.75 0 0 1 0 1.5h-15a.75.75 0 0 1-.75-.75Zm0 5.25a.75.75 0 0 1 .75-.75h15a.75.75 0 0 1 0 1.5h-15a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" />
                </svg>
              </button>
            </SheetTrigger>
            <SheetContent side="right" class="w-80 max-w-[85vw]">
              <SheetHeader>
                <SheetTitle>Menu</SheetTitle>
                <SheetDescription>Select a section</SheetDescription>
              </SheetHeader>

              <nav class="mt-6 grid gap-2 text-sm">
                <Link href="/" class="rounded-md px-3 py-2 hover:bg-white/5 transition text-foreground">Home</Link>
                <a href="#features" class="rounded-md px-3 py-2 hover:bg-white/5 transition text-foreground">Features</a>
                <Link href="/settings/profile" class="rounded-md px-3 py-2 hover:bg-white/5 transition text-foreground">Settings</Link>
                <Link href="/settings/billing" class="rounded-md px-3 py-2 hover:bg-white/5 transition text-foreground">Billing</Link>
                <Link href="/privacy" class="rounded-md px-3 py-2 hover:bg-white/5 transition text-foreground">Privacy</Link>
                <Link href="/terms" class="rounded-md px-3 py-2 hover:bg-white/5 transition text-foreground">Terms</Link>
                <Link href="/cookie" class="rounded-md px-3 py-2 hover:bg-white/5 transition text-foreground">Cookie Policy</Link>
              </nav>

              <div class="mt-8">
                <SheetClose as-child>
                  <Button variant="secondary" class="w-full">Close</Button>
                </SheetClose>
              </div>
            </SheetContent>
          </Sheet>
        </div>
      </div>
    </header>

    <main>
      <slot />
    </main>

    <Footer />
  </div>
</template>

<style scoped></style>
