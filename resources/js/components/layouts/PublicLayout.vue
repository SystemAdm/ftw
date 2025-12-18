<script setup lang="ts">
import Footer from '@/components/layouts/Footer.vue'
import { Link } from '@inertiajs/vue3'
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetDescription, SheetTrigger, SheetClose } from '@/components/ui/sheet'
import { Button } from '@/components/ui/button'
import { usePage } from '@inertiajs/vue3'
import { computed, onMounted } from 'vue'
import { Toaster } from 'vue-sonner'
import { useFlashToasts } from '@/composables/useFlashToasts'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faSun, faMoon, faDesktop } from '@fortawesome/free-solid-svg-icons'

library.add(faSun, faMoon, faDesktop)

type I18n = {
  i18n: {
    locale: string
    fallback: string
    trans: {
      ui: Record<string, any>
    }
  }
}

const page = usePage<I18n>()
const ui = computed(() => page.props.i18n?.trans?.ui ?? {})
const currentAppearance = computed<'light'|'dark'|'system'>(() => (page.props as any).appearance ?? 'system')

function setCookie(name: string, value: string, days = 365) {
  const d = new Date()
  d.setTime(d.getTime() + days * 24 * 60 * 60 * 1000)
  document.cookie = `${name}=${value};expires=${d.toUTCString()};path=/`;
}

function applyAppearance(mode: 'light'|'dark'|'system') {
  const root = document.documentElement
  const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches
  const isDark = mode === 'dark' || (mode === 'system' && prefersDark)
  root.classList.toggle('dark', isDark)
}

function onAppearanceChange(mode: 'light'|'dark'|'system') {
  setCookie('appearance', mode)
  applyAppearance(mode)
  toast.success(ui.value?.appearance?.changed ?? 'Theme updated')
}

onMounted(() => {
  applyAppearance(currentAppearance.value)
  // If user set System, react to OS changes
  const mq = window.matchMedia('(prefers-color-scheme: dark)')
  const handler = () => {
    if ((document.cookie || '').includes('appearance=system')) {
      applyAppearance('system')
    }
  }
  if (mq?.addEventListener) {
    mq.addEventListener('change', handler)
  } else if ((mq as any)?.addListener) {
    ;(mq as any).addListener(handler)
  }
})

// Bridge Laravel flash messages -> Sonner toasts with dedupe
useFlashToasts(page)
</script>

<template>
  <div class="min-h-screen dark:bg-black bg-background text-foreground selection:bg-green-500/40">
    <!-- Global toast provider (Sonner) -->
    <Toaster
      position="top-right"
      rich-colors
      close-button
      expand
      theme="system"
      offset="64px"
      :duration="4000"
      :toast-options="{
        class: 'border border-white/10 bg-background/95 text-foreground rounded-lg shadow-[0_8px_30px_rgb(0,0,0,0.12)]',
        descriptionClass: 'text-sm text-muted-foreground',
        actionButtonClass: 'rounded-md bg-white/10 hover:bg-white/15 text-foreground',
        cancelButtonClass: 'rounded-md bg-transparent hover:bg-white/5 text-muted-foreground'
      }"
    />
    <!-- Top nav -->
    <header class="sticky top-0 z-50 mx-auto w-full max-w-7xl dark:bg-black/80 dark:supports-backdrop-filter:bg-black/60 backdrop-blur border-b dark:border-white/10 px-4 py-4 sm:px-6 sm:py-5">
      <div class="relative grid grid-cols-[1fr_auto_1fr] items-center">
        <!-- Left placeholder (ensures center title stays perfectly centered). Shows logo from md+. -->
        <div class="flex items-center pl-1 h-10 w-10">
          <span class="inline-grid   place-items-center rounded-md dark:md:bg-black md:bg-white">
            <img src="/images/Spillhuset_logo_tak.png" alt="Spillhuset logo" class="hidden md:block h-9 w-auto" />
          </span>
        </div>
        <!-- Center title (brand links to home) -->
        <Link href="/" aria-label="Go to home" class="text-center text-lg font-bold tracking-wide sm:text-xl hover:opacity-90 transition">
          {{ ui.brand ?? 'Spillhuset' }}
        </Link>
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
                <SheetTitle>{{ ui.menu?.title ?? 'Menu' }}</SheetTitle>
                <SheetDescription>{{ ui.menu?.title ?? 'Menu' }}</SheetDescription>
              </SheetHeader>

              <nav class="mt-6 grid gap-2 text-sm">
                <Link href="/" class="rounded-md px-3 py-2 hover:bg-white/5 transition text-foreground">{{ ui.menu?.home ?? 'Home' }}</Link>
                <a href="#features" class="rounded-md px-3 py-2 hover:bg-white/5 transition text-foreground">{{ ui.menu?.features ?? 'Features' }}</a>
                <Link href="/settings/profile" class="rounded-md px-3 py-2 hover:bg-white/5 transition text-foreground">{{ ui.menu?.settings ?? 'Settings' }}</Link>
                <Link href="/settings/billing" class="rounded-md px-3 py-2 hover:bg-white/5 transition text-foreground">{{ ui.menu?.billing ?? 'Billing' }}</Link>
                <Link href="/privacy" class="rounded-md px-3 py-2 hover:bg-white/5 transition text-foreground">{{ ui.legal?.privacy ?? 'Privacy notice' }}</Link>
                <Link href="/terms" class="rounded-md px-3 py-2 hover:bg-white/5 transition text-foreground">{{ ui.legal?.terms ?? 'Terms & conditions' }}</Link>
                <Link href="/cookie" class="rounded-md px-3 py-2 hover:bg-white/5 transition text-foreground">{{ ui.legal?.cookie ?? 'Cookie policy' }}</Link>
              </nav>

              <!-- Appearance toggle -->
              <div class="mt-8 border-t pt-6">
                <div class="mb-3 text-xs font-semibold uppercase tracking-wide text-muted-foreground">{{ ui.appearance?.heading ?? 'Appearance' }}</div>
                <div class="flex items-center gap-2">
                  <button type="button"
                          :aria-pressed="currentAppearance === 'light'"
                          @click="onAppearanceChange('light')"
                          class="inline-flex items-center gap-2 rounded-md border border-white/10 px-3 py-2 text-sm hover:bg-white/5 transition"
                          :class="{ 'bg-white/10': currentAppearance === 'light' }">
                    <FontAwesomeIcon icon="sun" class="h-4 w-4" />
                    <span>{{ ui.appearance?.light ?? 'Light' }}</span>
                  </button>
                  <button type="button"
                          :aria-pressed="currentAppearance === 'dark'"
                          @click="onAppearanceChange('dark')"
                          class="inline-flex items-center gap-2 rounded-md border border-white/10 px-3 py-2 text-sm hover:bg-white/5 transition"
                          :class="{ 'bg-white/10': currentAppearance === 'dark' }">
                    <FontAwesomeIcon icon="moon" class="h-4 w-4" />
                    <span>{{ ui.appearance?.dark ?? 'Dark' }}</span>
                  </button>
                  <button type="button"
                          :aria-pressed="currentAppearance === 'system'"
                          @click="onAppearanceChange('system')"
                          class="inline-flex items-center gap-2 rounded-md border border-white/10 px-3 py-2 text-sm hover:bg-white/5 transition"
                          :class="{ 'bg-white/10': currentAppearance === 'system' }">
                    <FontAwesomeIcon icon="desktop" class="h-4 w-4" />
                    <span>{{ ui.appearance?.system ?? 'System' }}</span>
                  </button>
                </div>
              </div>

              <div class="mt-8">
                <SheetClose as-child>
                  <Button variant="secondary" class="w-full">{{ ui.menu?.close ?? 'Close' }}</Button>
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
