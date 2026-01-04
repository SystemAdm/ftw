<script setup lang="ts">
import Footer from '@/components/layouts/Footer.vue';
import { Link } from '@inertiajs/vue3';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetDescription, SheetTrigger, SheetClose } from '@/components/ui/sheet';
import { Button } from '@/components/ui/button';
import { usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import { Toaster } from '@/components/ui/sonner';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { library } from '@fortawesome/fontawesome-svg-core';
import { faSun, faMoon, faDesktop } from '@fortawesome/free-solid-svg-icons';
import { useFlashToasts } from '@/composables/useFlashToasts';
import BannedUserDialog from '@/components/custom/BannedUserDialog.vue';

library.add(faSun, faMoon, faDesktop);

import { useAppearance } from '@/composables/useAppearance';
import { UserIcon, Users, CalendarDays, MapPinIcon, LayoutDashboard, Cog, DoorOpen, History, LogIn } from 'lucide-vue-next';
import { dashboard as adminDashboardRoute } from '@/routes/admin/index';
import { index as modOpenRoute } from '@/routes/mod/open/index';
import { dashboard as dashboardRoute } from '@/routes/index';
import { show as profileRoute } from '@/routes/profile/index';
import { profile as settingsRoute } from '@/routes/settings';
import { index as teamsRoute } from '@/routes/teams/index';
import { index as locationsRoute } from '@/routes/locations/index';
import { index as eventsRoute } from '@/routes/events/index';
import { trans } from 'laravel-vue-i18n';

const page = usePage<any>();
const ui = computed(() => page.props.i18n?.trans?.ui ?? {});

const user = computed(() => {
    const u = page.props.auth?.user;
    return u ? { ...u, avatar: u.avatar ?? '', roles: page.props.auth?.roles ?? [] } : null;
});

const isAdmin = computed(() => page.props.auth?.roles?.includes('Admin'));
const isCrew = computed(() => page.props.auth?.roles?.includes('Crew') || isAdmin.value);
const isMod = computed(() => page.props.auth?.roles?.includes('Moderator') || isAdmin.value);

const { appearance, updateAppearance } = useAppearance();
const systemIsDark = ref(false);

const banMessage = computed(() => (page.props as any).ban_message as string | null);
const showBanDialog = ref(false);

watch(banMessage, (val) => {
    if (val) showBanDialog.value = true;
}, { immediate: true });

const logoUrl = computed(() => {
    if (appearance.value === 'dark') {
        return '/images/Spillhuset_roof_light.png';
    }

    if (appearance.value === 'light') {
        return '/images/Spillhuset_roof_dark.png';
    }

    return systemIsDark.value ? '/images/Spillhuset_roof_light.png' : '/images/Spillhuset_roof_dark.png';
});

function onAppearanceChange(mode: 'light' | 'dark' | 'system') {
    updateAppearance(mode);
    toast.success(ui.value?.appearance?.changed ?? 'Theme updated');
}

onMounted(() => {
    const mq = window.matchMedia('(prefers-color-scheme: dark)');
    systemIsDark.value = mq.matches;

    // React to OS changes
    const handler = () => {
        systemIsDark.value = mq.matches;
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

<template>
    <!-- Global toast provider (Sonner) -->
    <Toaster />

    <!-- Banned User Dialog -->
    <BannedUserDialog v-if="showBanDialog" :message="banMessage" @close="showBanDialog = false" />

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
                        <SheetContent side="right" class="w-80 max-w-[85vw] flex flex-col p-0">
                            <SheetHeader class="px-6 pt-6">
                                <SheetTitle>{{ ui.menu?.title ?? 'Menu' }}</SheetTitle>
                                <SheetDescription class="sr-only">{{ ui.menu?.title ?? 'Menu' }}</SheetDescription>
                            </SheetHeader>

                            <div class="flex-1 overflow-y-auto px-6 pb-6">
                                <nav class="mt-4 grid gap-1 text-sm">
                                    <template v-if="user">
                                        <div class="mb-2 text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                            {{ trans('pages.ui.navigation.user_menu') }}
                                        </div>
                                        <Link :href="dashboardRoute.url()" class="flex items-center gap-3 rounded-md px-3 py-2 text-foreground transition hover:bg-accent hover:text-accent-foreground">
                                            <LayoutDashboard class="h-4 w-4" />
                                            {{ trans('pages.ui.navigation.dashboard') }}
                                        </Link>
                                        <Link :href="profileRoute.url()" class="flex items-center gap-3 rounded-md px-3 py-2 text-foreground transition hover:bg-accent hover:text-accent-foreground">
                                            <UserIcon class="h-4 w-4" />
                                            {{ trans('pages.ui.navigation.profile') }}
                                        </Link>
                                        <Link :href="settingsRoute.url()" class="flex items-center gap-3 rounded-md px-3 py-2 text-foreground transition hover:bg-accent hover:text-accent-foreground">
                                            <Cog class="h-4 w-4" />
                                            {{ trans('pages.ui.navigation.settings') }}
                                        </Link>
                                        <Link :href="teamsRoute.url()" class="flex items-center gap-3 rounded-md px-3 py-2 text-foreground transition hover:bg-accent hover:text-accent-foreground">
                                            <Users class="h-4 w-4" />
                                            {{ trans('pages.ui.navigation.teams') }}
                                        </Link>
                                        <Link :href="locationsRoute.url()" class="flex items-center gap-3 rounded-md px-3 py-2 text-foreground transition hover:bg-accent hover:text-accent-foreground">
                                            <MapPinIcon class="h-4 w-4" />
                                            {{ trans('pages.ui.navigation.locations') }}
                                        </Link>
                                        <Link :href="eventsRoute.url()" class="flex items-center gap-3 rounded-md px-3 py-2 text-foreground transition hover:bg-accent hover:text-accent-foreground">
                                            <CalendarDays class="h-4 w-4" />
                                            {{ trans('pages.ui.navigation.events') }}
                                        </Link>

                                        <!-- Admin, Crew, Mod Sections -->
                                        <template v-if="isAdmin">
                                            <div class="mt-6 mb-2 text-xs font-semibold tracking-wide text-muted-foreground uppercase border-t pt-4">
                                                {{ trans('pages.ui.navigation.admin') }}
                                            </div>
                                            <Link :href="adminDashboardRoute.url()" class="flex items-center gap-3 rounded-md px-3 py-2 text-foreground transition hover:bg-accent hover:text-accent-foreground">
                                                <LayoutDashboard class="h-4 w-4" />
                                                {{ trans('pages.ui.navigation.dashboard') }}
                                            </Link>
                                        </template>

                                        <template v-if="isCrew">
                                            <div class="mt-6 mb-2 text-xs font-semibold tracking-wide text-muted-foreground uppercase border-t pt-4">
                                                {{ trans('pages.ui.navigation.crew') }}
                                            </div>
                                            <Link href="/crew" class="flex items-center gap-3 rounded-md px-3 py-2 text-foreground transition hover:bg-accent hover:text-accent-foreground">
                                                <LayoutDashboard class="h-4 w-4" />
                                                {{ trans('pages.ui.navigation.dashboard') }}
                                            </Link>
                                        </template>

                                        <template v-if="isMod">
                                            <div class="mt-6 mb-2 text-xs font-semibold tracking-wide text-muted-foreground uppercase border-t pt-4">
                                                {{ trans('pages.ui.navigation.moderator') }}
                                            </div>
                                            <Link :href="modOpenRoute.url()" class="flex items-center gap-3 rounded-md px-3 py-2 text-foreground transition hover:bg-accent hover:text-accent-foreground">
                                                <History class="h-4 w-4" />
                                                {{ trans('pages.ui.navigation.mod_open') }}
                                            </Link>
                                        </template>

                                        <div class="mt-6 border-t pt-4">
                                            <Link href="/logout" method="post" as="button" class="flex w-full items-center gap-3 rounded-md px-3 py-2 text-foreground transition hover:bg-destructive hover:text-destructive-foreground">
                                                <DoorOpen class="h-4 w-4" />
                                                {{ trans('pages.auth.logout') }}
                                            </Link>
                                        </div>
                                    </template>

                                    <template v-else>
                                        <Link href="/login" class="rounded-md px-3 py-2 text-foreground transition hover:bg-accent hover:text-accent-foreground flex items-center gap-3">
                                            <LogIn class="h-4 w-4" />
                                            {{ trans('pages.auth.login.login_button') }}
                                        </Link>
                                        <Link :href="teamsRoute.url()" class="flex items-center gap-3 rounded-md px-3 py-2 text-foreground transition hover:bg-accent hover:text-accent-foreground">
                                            <Users class="h-4 w-4" />
                                            {{ trans('pages.ui.navigation.teams') }}
                                        </Link>
                                        <Link :href="locationsRoute.url()" class="flex items-center gap-3 rounded-md px-3 py-2 text-foreground transition hover:bg-accent hover:text-accent-foreground">
                                            <MapPinIcon class="h-4 w-4" />
                                            {{ trans('pages.ui.navigation.locations') }}
                                        </Link>
                                        <Link :href="eventsRoute.url()" class="flex items-center gap-3 rounded-md px-3 py-2 text-foreground transition hover:bg-accent hover:text-accent-foreground">
                                            <CalendarDays class="h-4 w-4" />
                                            {{ trans('pages.ui.navigation.events') }}
                                        </Link>
                                    </template>


                                </nav>

                                <!-- Appearance toggle -->
                                <div class="mt-8 border-t pt-6">
                                    <div class="mb-3 text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                        {{ ui.appearance?.heading ?? 'Appearance' }}
                                    </div>
                                    <div class="grid grid-cols-1 gap-2">
                                        <button
                                            type="button"
                                            :aria-pressed="appearance === 'light'"
                                            @click="onAppearanceChange('light')"
                                            class="inline-flex items-center gap-3 rounded-md border border-input px-3 py-2 text-sm transition hover:bg-accent hover:text-accent-foreground"
                                            :class="{ 'bg-accent text-accent-foreground': appearance === 'light' }"
                                        >
                                            <FontAwesomeIcon icon="sun" class="h-4 w-4" />
                                            <span>{{ ui.appearance?.light ?? 'Light' }}</span>
                                        </button>
                                        <button
                                            type="button"
                                            :aria-pressed="appearance === 'dark'"
                                            @click="onAppearanceChange('dark')"
                                            class="inline-flex items-center gap-3 rounded-md border border-input px-3 py-2 text-sm transition hover:bg-accent hover:text-accent-foreground"
                                            :class="{ 'bg-accent text-accent-foreground': appearance === 'dark' }"
                                        >
                                            <FontAwesomeIcon icon="moon" class="h-4 w-4" />
                                            <span>{{ ui.appearance?.dark ?? 'Dark' }}</span>
                                        </button>
                                        <button
                                            type="button"
                                            :aria-pressed="appearance === 'system'"
                                            @click="onAppearanceChange('system')"
                                            class="inline-flex items-center gap-3 rounded-md border border-input px-3 py-2 text-sm transition hover:bg-accent hover:text-accent-foreground"
                                            :class="{ 'bg-accent text-accent-foreground': appearance === 'system' }"
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
