<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
</script>

<template>
  <footer class="border-t">
    <div class="mx-auto w-full max-w-7xl px-4 py-10 sm:px-6">
      <!-- Top: brand + socials -->
      <div class="flex flex-col items-center justify-between gap-6 md:flex-row">
        <!-- Brand -->
        <div class="flex items-center gap-3">
          <span class="inline-grid h-10 w-10 place-items-center rounded-md ">
            <img src="/images/Spillhuset.png" alt="Spillhuset logo" />
          </span>
          <span class="text-base font-semibold tracking-wide text-foreground uppercase">Spillhuset</span>
        </div>

        <!-- Socials -->
        <div class="flex items-center gap-4 text-muted-foreground">
          <a href="https://facebook.com/Spillhuset" target="_blank" rel="noopener noreferrer" aria-label="Facebook" class="hover:text-foreground transition">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5"><path d="M22 12.07C22 6.48 17.52 2 11.93 2S2 6.48 2 12.07c0 5 3.66 9.14 8.44 9.93v-7.02H7.9v-2.9h2.54V9.41c0-2.5 1.49-3.89 3.77-3.89 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.62.77-1.62 1.56v1.87h2.77l-.44 2.9h-2.33V22c4.78-.79 8.44-4.93 8.44-9.93Z"/></svg>
          </a>
          <a href="https://instagram.com/spillhusetoffisiell/" target="_blank" rel="noopener noreferrer" aria-label="Instagram" class="hover:text-foreground transition">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5"><path d="M7 2h10a5 5 0 0 1 5 5v10a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5V7a5 5 0 0 1 5-5Zm10 2H7a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3Zm-5 3.5A5.5 5.5 0 1 1 6.5 13 5.5 5.5 0 0 1 12 7.5Zm0 2A3.5 3.5 0 1 0 15.5 13 3.5 3.5 0 0 0 12 9.5ZM17.75 6a1.25 1.25 0 1 1-1.25 1.25A1.25 1.25 0 0 1 17.75 6Z"/></svg>
          </a>
          <a href="https://share.google/sA6OLGWWsC2niRi84" target="_blank" rel="noopener noreferrer" aria-label="Google" class="hover:text-foreground transition">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5"><path d="M21.35 11.1H12v2.9h5.35c-.25 1.5-1.8 4.4-5.35 4.4a6 6 0 1 1 0-12c1.7 0 3.2.7 4.25 1.6l2-2A9.78 9.78 0 0 0 12 3a9 9 0 1 0 0 18c5.2 0 8.65-3.65 8.65-8.8 0-.6-.1-1.1-.3-1.6Z"/></svg>
          </a>
          <a href="https://no.linkedin.com/company/spillhusetbaerum" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn" class="hover:text-foreground transition">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5"><path d="M20.45 20.45h-3.55v-5.6c0-1.34-.02-3.06-1.87-3.06-1.88 0-2.17 1.46-2.17 2.96v5.7H9.3V9h3.4v1.56h.05c.47-.88 1.62-1.8 3.34-1.8 3.58 0 4.24 2.36 4.24 5.43v6.26ZM5.34 7.43a2.06 2.06 0 1 1 0-4.12 2.06 2.06 0 0 1 0 4.12ZM7.12 20.45H3.57V9H7.12v11.45Z"/></svg>
          </a>
          <a href="https://github.com/Spillhuset" target="_blank" rel="noopener noreferrer" aria-label="GitHub" class="hover:text-foreground transition">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5"><path fill-rule="evenodd" d="M12 .5a11.5 11.5 0 0 0-3.64 22.41c.58.1.8-.25.8-.56v-2.1c-3.26.71-3.95-1.4-3.95-1.4a3.1 3.1 0 0 0-1.3-1.7c-1.06-.72.08-.71.08-.71a2.45 2.45 0 0 1 1.79 1.2 2.48 2.48 0 0 0 3.39 1 2.47 2.47 0 0 1 .74-1.55c-2.6-.3-5.34-1.3-5.34-5.8A4.55 4.55 0 0 1 6.1 7.5a4.23 4.23 0 0 1 .11-3.14s.98-.31 3.2 1.2a11.03 11.03 0 0 1 5.82 0c2.2-1.51 3.2-1.2 3.2-1.2.32.79.35 1.67.1 2.49a4.55 4.55 0 0 1 1.21 3.16c0 4.51-2.75 5.49-5.37 5.78.42.37.8 1.08.8 2.17v3.22c0 .31.22.66.8.56A11.5 11.5 0 0 0 12 .5Z" clip-rule="evenodd"/></svg>
          </a>
        </div>
      </div>

      <!-- Divider -->
      <div class="my-8 h-px w-full bg-white/10" />

      <!-- Links / Info -->
      <div class="grid grid-cols-1 gap-8 text-sm text-muted-foreground md:grid-cols-3">
        <!-- Legal -->
        <div class="text-center md:text-left">
          <h3 class="mb-3 text-foreground font-semibold">Legal</h3>
          <ul class="space-y-2">
            <li><Link href="/privacy" class="hover:text-foreground transition">Privacy notice</Link></li>
            <li><Link href="/terms" class="hover:text-foreground transition">Terms & conditions</Link></li>
            <li><Link href="/cookie" class="hover:text-foreground transition">Cookie policy</Link></li>
          </ul>
        </div>

        <!-- Contact / Visit -->
        <div class="text-center md:text-left">
          <h3 class="mb-3 text-foreground font-semibold">Contact</h3>
          <ul class="space-y-2">
            <li>Besøksadresse: Skollerudveien 5, 1353 Bærums Verk</li>
            <li>Bankkonto: <span class="font-medium text-foreground">2220.29.86645</span></li>
            <li>Vipps: <span class="font-medium text-foreground">#611764</span></li>
            <li>
              Org.nr:
              <a
                href="https://virksomhet.brreg.no/nb/oppslag/enheter/917616140"
                target="_blank"
                rel="noopener noreferrer"
                class="font-medium text-foreground hover:underline"
              >
                917616140
              </a>
            </li>
            <li><Link href="/contact" class="hover:text-foreground transition">Kontakt skjema</Link></li>
          </ul>
        </div>

        <!-- About -->
        <div class="text-center md:text-left">
          <h3 class="mb-3 text-foreground font-semibold">About</h3>
          <p class="text-sm/6">Et møtepunkt for spillere – play, compete, win. Følg oss på sosiale medier for nyheter og arrangementer.</p>
        </div>
      </div>

      <!-- Bottom -->
      <div class="mt-10 text-center text-xs text-muted-foreground">© {{ new Date().getFullYear() }} Spillhuset. All rights reserved.</div>
    </div>
  </footer>
</template>

<style scoped></style>
