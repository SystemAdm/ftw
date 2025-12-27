<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { usePage } from '@inertiajs/vue3'
import { computed, onMounted, ref } from 'vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faFacebook, faInstagram, faGoogle, faLinkedin, faGithub } from '@fortawesome/free-brands-svg-icons'

library.add(faFacebook, faInstagram, faGoogle, faLinkedin, faGithub)

type I18n = {
  i18n: {
    trans: {
      ui: Record<string, any>
    }
  }
}

const page = usePage<I18n>()
const ui = computed(() => page.props.i18n?.trans?.ui ?? {})

const appearance = ref<'light' | 'dark' | 'system'>((page.props as any).appearance ?? 'system')
const systemIsDark = ref(false)

const logoUrl = computed(() => {
  if (appearance.value === 'dark') {
    return '/images/Spillhuset_logo_light.png'
  }

  if (appearance.value === 'light') {
    return '/images/Spillhuset_logo_dark.png'
  }

  return systemIsDark.value ? '/images/Spillhuset_logo_light.png' : '/images/Spillhuset_logo_dark.png'
})

function applyAppearance(mode: 'light' | 'dark' | 'system') {
  const root = document.documentElement
  const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches
  const isDark = mode === 'dark' || (mode === 'system' && prefersDark)
  root.classList.toggle('dark', isDark)
}

onMounted(() => {
  const mq = window.matchMedia('(prefers-color-scheme: dark)')
  systemIsDark.value = mq.matches

  applyAppearance(appearance.value)

  const handler = () => {
    systemIsDark.value = mq.matches
    if (appearance.value === 'system') {
      applyAppearance('system')
    }
  }

  if (mq?.addEventListener) {
    mq.addEventListener('change', handler)
  } else if ((mq as any)?.addListener) {
    (mq as any).addListener(handler)
  }
})
</script>

<template>
  <footer class="border-t">
    <div class="mx-auto w-full max-w-7xl px-4 py-10 sm:px-6">
      <!-- Top: brand + socials -->
      <div class="flex flex-col items-center justify-between gap-6 md:flex-row">
        <!-- Brand -->
        <Link href="/" aria-label="Go to home" class="flex items-center gap-3 hover:opacity-90 transition">
          <span class="inline-grid h-10 place-items-center rounded-md ">
            <img :src="logoUrl" alt="Spillhuset logo" class="h-15 md:block" />
          </span>
          <!--<span class="text-base font-semibold tracking-wide text-foreground uppercase">{{ ui.brand ?? 'Spillhuset' }}</span>-->
        </Link>

        <!-- Socials -->
        <div class="flex items-center gap-4 text-muted-foreground">
          <a href="https://facebook.com/Spillhuset" target="_blank" rel="noopener noreferrer" aria-label="Facebook" class="hover:text-foreground transition">
            <FontAwesomeIcon :icon="['fab','facebook']" class="h-5 w-5" />
          </a>
          <a href="https://instagram.com/spillhusetoffisiell/" target="_blank" rel="noopener noreferrer" aria-label="Instagram" class="hover:text-foreground transition">
            <FontAwesomeIcon :icon="['fab','instagram']" class="h-5 w-5" />
          </a>
          <a href="https://share.google/sA6OLGWWsC2niRi84" target="_blank" rel="noopener noreferrer" aria-label="Google" class="hover:text-foreground transition">
            <FontAwesomeIcon :icon="['fab','google']" class="h-5 w-5" />
          </a>
          <a href="https://no.linkedin.com/company/spillhusetbaerum" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn" class="hover:text-foreground transition">
            <FontAwesomeIcon :icon="['fab','linkedin']" class="h-5 w-5" />
          </a>
          <a href="https://github.com/Spillhuset" target="_blank" rel="noopener noreferrer" aria-label="GitHub" class="hover:text-foreground transition">
            <FontAwesomeIcon :icon="['fab','github']" class="h-5 w-5" />
          </a>
        </div>
      </div>

      <!-- Divider -->
      <div class="my-8 h-px w-full" aria-hidden="true" role="presentation" />

      <!-- Links / Info -->
      <div class="grid grid-cols-1 gap-8 text-sm text-muted-foreground md:grid-cols-3">
        <!-- Legal -->
        <div class="text-center md:text-left">
          <h3 class="mb-3 text-foreground font-semibold">{{ ui.legal?.heading ?? 'Legal' }}</h3>
          <ul class="space-y-2">
            <li>
              <Link href="/privacy" class="hover:text-foreground transition">
                <span v-html="ui.legal?.privacy ?? 'Privacy notice'"></span>
              </Link>
            </li>
            <li>
              <Link href="/terms" class="hover:text-foreground transition">
                <span v-html="ui.legal?.terms ?? 'Terms & conditions'"></span>
              </Link>
            </li>
            <li>
              <Link href="/cookie" class="hover:text-foreground transition">
                <span v-html="ui.legal?.cookie ?? 'Cookie policy'"></span>
              </Link>
            </li>
          </ul>
        </div>

        <!-- Contact / Visit -->
        <div class="text-center md:text-left">
          <h3 class="mb-3 text-foreground font-semibold">{{ ui.contact?.heading ?? 'Contact' }}</h3>
          <ul class="space-y-2">
            <li>{{ ui.contact?.visit ?? 'Visit address' }}: Skollerudveien 5, 1353 Bærums Verk</li>
            <li>{{ ui.contact?.bank ?? 'Bank account' }}: <span class="font-medium text-foreground">2220.29.86645</span> <span>Sparebanken Øst</span></li>
            <li>{{ ui.contact?.vipps ?? 'Vipps' }}: <span class="font-medium text-foreground">#611764</span></li>
            <li>
              {{ ui.contact?.org ?? 'Org.nr' }}:
              <a
                href="https://virksomhet.brreg.no/nb/oppslag/enheter/917616140"
                target="_blank"
                rel="noopener noreferrer"
                class="font-medium text-foreground hover:underline"
              >
                917616140
              </a> <span>Link til Brønnøysundregisterene</span>
            </li>
            <li>
              <Link href="/contact" class="hover:text-foreground transition">
                <span class="text-foreground" v-html="ui.contact?.form ?? 'Contact form'"></span>
              </Link>{{ui.contact?.or ?? ''}}
                <span v-html="ui.contact?.direct ?? ''"></span>
            </li>
          </ul>
        </div>

        <!-- About -->
        <div class="text-center md:text-left">
          <h3 class="mb-3 text-foreground font-semibold">{{ ui.about?.heading ?? 'About' }}</h3>
          <p class="text-sm/6">{{ ui.about?.description ?? 'A meetup for gamers – play, compete, win. Follow us on social media for news and events.' }}</p>
        </div>
      </div>

      <!-- Bottom -->
      <div class="mt-10 text-center text-xs text-muted-foreground">© {{ "2014 - " + new Date().getFullYear() }} {{ ui.brand ?? 'Spillhuset' }}. {{ ui.footer?.rights ?? 'All rights reserved.' }}</div>
    </div>
  </footer>
</template>

<style scoped></style>
