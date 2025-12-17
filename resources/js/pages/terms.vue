<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import PublicLayout from '@/components/layouts/PublicLayout.vue';

type I18n = {
    i18n: {
        trans: {
            pages: Record<string, any>;
        };
    };
};

const page = usePage<I18n>();
const t = computed(() => page.props.i18n?.trans?.pages?.terms ?? {});
const ui = computed(() => page.props.i18n?.trans?.ui ?? {});
</script>

<template>
    <PublicLayout>
        <div class="mx-auto w-full max-w-3xl px-4 py-10 sm:px-6">
            <Head :title="t.title ?? 'Terms & legal'" />

            <header class="mb-8 space-y-2">
                <h1 class="text-3xl font-bold tracking-tight">{{ t.title ?? 'Terms & Legal' }}</h1>
                <p class="text-sm text-muted-foreground">{{ t.updated ?? 'Last updated' }}</p>
            </header>

            <!-- Quick summary -->
            <section class="mb-8 rounded-lg border bg-background/50 p-4">
                <h2 class="text-lg font-semibold">{{ t.summary_title ?? 'In short' }}</h2>
                <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-muted-foreground">
                    <li>{{ t.summary?.respect }}</li>
                    <li>{{ t.summary?.content }}</li>
                    <li>{{ t.summary?.payments }}</li>
                </ul>
            </section>

            <!-- Table of contents -->
            <nav class="mb-8 rounded-lg border p-4">
                <h2 class="text-lg font-semibold">{{ t.toc?.title ?? 'Contents' }}</h2>
                <ul class="mt-3 grid gap-2 text-sm text-muted-foreground">
                    <li>
                        <a class="hover:underline" href="#eula">{{ t.toc?.eula ?? 'End User License Agreement (EULA)' }}</a>
                    </li>
                    <li>
                        <a class="hover:underline" href="#website-terms">{{ t.toc?.website ?? 'Terms of Use for Website' }}</a>
                    </li>
                    <li>
                        <a class="hover:underline" href="#privacy">{{ t.toc?.privacy ?? 'Online Privacy Policy' }}</a>
                    </li>
                </ul>
            </nav>

            <!-- EULA -->
            <section id="eula" class="mb-10 space-y-3">
                <h2 class="text-xl font-semibold">{{ t.eula?.heading ?? 'End User License Agreement (EULA)' }}</h2>
                <p class="text-sm text-muted-foreground">{{ t.eula?.p1 }}</p>
                <ul class="list-disc space-y-2 pl-5 text-sm text-muted-foreground">
                    <li>{{ t.eula?.items?.license }}</li>
                    <li>{{ t.eula?.items?.restrictions }}</li>
                    <li>{{ t.eula?.items?.ip }}</li>
                    <li>{{ t.eula?.items?.termination }}</li>
                </ul>
            </section>

            <!-- Website Terms -->
            <section id="website-terms" class="mb-10 space-y-3">
                <h2 class="text-xl font-semibold">{{ t.website?.heading ?? 'Terms of Use for Website' }}</h2>
                <p class="text-sm text-muted-foreground">{{ t.website?.p1 }}</p>
                <ul class="list-disc space-y-2 pl-5 text-sm text-muted-foreground">
                    <li>{{ t.website?.items?.acceptable }}</li>
                    <li>{{ t.website?.items?.accounts }}</li>
                    <li>{{ t.website?.items?.copyright }}</li>
                    <li>{{ t.website?.items?.disclaimer }}</li>
                    <li>{{ t.website?.items?.changes }}</li>
                </ul>
            </section>

            <!-- Privacy Policy (linking to our dedicated page) -->
            <section id="privacy" class="mb-10 space-y-3">
                <h2 class="text-xl font-semibold">{{ t.privacy?.heading ?? 'Online Privacy Policy' }}</h2>
                <p class="text-sm text-muted-foreground">
                    {{ t.privacy?.p1 }}
                    <a href="/privacy" class="underline">{{ t.privacy?.link ?? 'Privacy notice' }}</a
                    >. Highlights:
                </p>
                <ul class="list-disc space-y-2 pl-5 text-sm text-muted-foreground">
                    <li>{{ t.privacy?.highlights?.minimal }}</li>
                    <li>{{ t.privacy?.highlights?.nosell }}</li>
                    <li>{{ t.privacy?.highlights?.rights }}</li>
                </ul>
            </section>

            <!-- Contact -->
            <section class="mt-10 space-y-2">
                <h2 class="text-xl font-semibold">{{ t.contact?.heading ?? 'Contact' }}</h2>
                <p class="text-sm text-muted-foreground">
                    {{ t.contact?.body }}
                    <a href="/contact" class="underline">{{ ui.contact?.form }}</a> ·
                    <a href="mailto:web@spillhuset.com" class="underline">web@spillhuset.com</a>.
                </p>
            </section>
        </div>
    </PublicLayout>
</template>

<style scoped></style>
