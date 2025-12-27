<script setup lang="ts">
import Footer from '@/components/layouts/Footer.vue';
import { Link } from '@inertiajs/vue3';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetDescription, SheetTrigger, SheetClose } from '@/components/ui/sheet';
import { Button } from '@/components/ui/button';
import { usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import { toast } from 'vue-sonner';
import { Toaster } from '@/components/ui/sonner';
import 'vue-sonner/style.css';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { library } from '@fortawesome/fontawesome-svg-core';
import { faSun, faMoon, faDesktop } from '@fortawesome/free-solid-svg-icons';
import { useFlashToasts } from '@/composables/useFlashToasts';

library.add(faSun, faMoon, faDesktop);

type I18n = {
    i18n: {
        locale: string;
        fallback: string;
        trans: {
            ui: Record<string, any>;
        };
    };
};

const page = usePage<I18n>();
const ui = computed(() => page.props.i18n?.trans?.ui ?? {});

const appearance = ref<'light' | 'dark' | 'system'>((page.props as any).appearance ?? 'system');
const systemIsDark = ref(false);

const logoUrl = computed(() => {
    if (appearance.value === 'dark') {
        return '/images/Spillhuset_roof_light.png';
    }

    if (appearance.value === 'light') {
        return '/images/Spillhuset_roof_dark.png';
    }

    return systemIsDark.value ? '/images/Spillhuset_roof_light.png' : '/images/Spillhuset_roof_dark.png';
});

function setCookie(name: string, value: string, days = 365) {
    const d = new Date();
    d.setTime(d.getTime() + days * 24 * 60 * 60 * 1000);
    document.cookie = `${name}=${value};expires=${d.toUTCString()};path=/`;
}

function applyAppearance(mode: 'light' | 'dark' | 'system') {
    const root = document.documentElement;
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    const isDark = mode === 'dark' || (mode === 'system' && prefersDark);
    root.classList.toggle('dark', isDark);
}

function onAppearanceChange(mode: 'light' | 'dark' | 'system') {
    appearance.value = mode;
    setCookie('appearance', mode);
    applyAppearance(mode);
    toast.success(ui.value?.appearance?.changed ?? 'Theme updated');
}

onMounted(() => {
    const mq = window.matchMedia('(prefers-color-scheme: dark)');
    systemIsDark.value = mq.matches;

    applyAppearance(appearance.value);

    // If user set System, react to OS changes
    const handler = () => {
        systemIsDark.value = mq.matches;
        if (appearance.value === 'system') {
            applyAppearance('system');
        }
    };
    if (mq?.addEventListener) {
        mq.addEventListener('change', handler);
    } else if ((mq as any)?.addListener) {
        (mq as any).addListener(handler);
    }
});

// Bridge Laravel flash messages -> Sonner toasts with dedupe
useFlashToasts(page);
</script>

<template><!-- Global toast provider (Sonner) -->
    <Toaster />
    <div class="min-h-screen bg-background text-foreground selection:bg-green-500/40 dark:bg-black">

        <!-- Top nav -->
        <header
            class="sticky top-0 z-50 mx-auto w-full max-w-7xl border-b px-4 py-4 backdrop-blur sm:px-6 sm:py-5 dark:border-white/10 dark:bg-black/80 dark:supports-backdrop-filter:bg-black/60"
        >
            <div class="relative grid grid-cols-[1fr_auto_1fr] items-center">
                <!-- Left placeholder (ensures center title stays perfectly centered). Shows logo from md+. -->
                <div class="flex h-10 items-center pl-1">
                    <span class="inline-grid place-items-center rounded-md">
                        <img :src="logoUrl" alt="Spillhuset logo" class="h-15 hidden md:block" />
                    </span>
                </div>
                <!-- Center title (brand links to home) -->
                <Link href="/" aria-label="Go to home" class="text-center text-lg font-bold tracking-wide transition hover:opacity-90 sm:text-xl uppercase">
                    {{ ui.brand ?? 'Spillhuset' }}
                </Link>
                <!-- Right hamburger -->
                <div class="flex items-center justify-end pr-1">
                    <Sheet>
                        <SheetTrigger as-child>
                            <button
                                type="button"
                                aria-label="Open menu"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-white/10 bg-white/5 transition hover:bg-white/10"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5">
                                    <path
                                        fill-rule="evenodd"
                                        d="M3.75 6.75A.75.75 0 0 1 4.5 6h15a.75.75 0 0 1 0 1.5h-15a.75.75 0 0 1-.75-.75Zm0 5.25A.75.75 0 0 1 4.5 11h15a.75.75 0 0 1 0 1.5h-15a.75.75 0 0 1-.75-.75Zm0 5.25a.75.75 0 0 1 .75-.75h15a.75.75 0 0 1 0 1.5h-15a.75.75 0 0 1-.75-.75Z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </button>
                        </SheetTrigger>
                        <SheetContent side="right" class="w-80 max-w-[85vw]">
                            <SheetHeader>
                                <SheetTitle>{{ ui.menu?.title ?? 'Menu' }}</SheetTitle>
                                <SheetDescription>{{ ui.menu?.title ?? 'Menu' }}</SheetDescription>
                            </SheetHeader>

                            <nav class="mt-6 grid gap-2 text-sm">
                                <Link href="/" class="rounded-md px-3 py-2 text-foreground transition hover:bg-white/5">{{
                                    ui.menu?.home ?? 'Home'
                                }}</Link>
                                <Link href="/events" class="rounded-md px-3 py-2 text-foreground transition hover:bg-white/5">{{
                                    ui.menu?.events ?? 'Events'
                                }}</Link>
                                <a href="#features" class="rounded-md px-3 py-2 text-foreground transition hover:bg-white/5">{{
                                    ui.menu?.features ?? 'Features'
                                }}</a>
                                <Link href="/settings/profile" class="rounded-md px-3 py-2 text-foreground transition hover:bg-white/5">{{
                                    ui.menu?.settings ?? 'Settings'
                                }}</Link>
                                <Link href="/settings/billing" class="rounded-md px-3 py-2 text-foreground transition hover:bg-white/5">{{
                                    ui.menu?.billing ?? 'Billing'
                                }}</Link>
                                <Link href="/privacy" class="rounded-md px-3 py-2 text-foreground transition hover:bg-white/5">{{
                                    ui.legal?.privacy ?? 'Privacy notice'
                                }}</Link>
                                <Link href="/terms" class="rounded-md px-3 py-2 text-foreground transition hover:bg-white/5">{{
                                    ui.legal?.terms ?? 'Terms & conditions'
                                }}</Link>
                                <Link href="/cookie" class="rounded-md px-3 py-2 text-foreground transition hover:bg-white/5">{{
                                    ui.legal?.cookie ?? 'Cookie policy'
                                }}</Link>
                            </nav>

                            <!-- Appearance toggle -->
                            <div class="mt-8 border-t pt-6">
                                <div class="mb-3 text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                    {{ ui.appearance?.heading ?? 'Appearance' }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <button
                                        type="button"
                                        :aria-pressed="appearance === 'light'"
                                        @click="onAppearanceChange('light')"
                                        class="inline-flex items-center gap-2 rounded-md border border-white/10 px-3 py-2 text-sm transition hover:bg-white/5"
                                        :class="{ 'bg-white/10': appearance === 'light' }"
                                    >
                                        <FontAwesomeIcon icon="sun" class="h-4 w-4" />
                                        <span>{{ ui.appearance?.light ?? 'Light' }}</span>
                                    </button>
                                    <button
                                        type="button"
                                        :aria-pressed="appearance === 'dark'"
                                        @click="onAppearanceChange('dark')"
                                        class="inline-flex items-center gap-2 rounded-md border border-white/10 px-3 py-2 text-sm transition hover:bg-white/5"
                                        :class="{ 'bg-white/10': appearance === 'dark' }"
                                    >
                                        <FontAwesomeIcon icon="moon" class="h-4 w-4" />
                                        <span>{{ ui.appearance?.dark ?? 'Dark' }}</span>
                                    </button>
                                    <button
                                        type="button"
                                        :aria-pressed="appearance === 'system'"
                                        @click="onAppearanceChange('system')"
                                        class="inline-flex items-center gap-2 rounded-md border border-white/10 px-3 py-2 text-sm transition hover:bg-white/5"
                                        :class="{ 'bg-white/10': appearance === 'system' }"
                                    >
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
