<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';
import { spillexpo } from '@/routes';
import { index } from '@/routes/locations';

interface Location {
    id: number;
    name: string;
    postal_code: number;
    description?: string | null;
    latitude?: string | null;
    longitude?: string | null;
    google_maps_url?: string | null;
    street_address?: string | null;
    street_number?: string | null;
    link?: string | null;
}

const page = usePage<{ location: Location }>();
const location = page.props.location;

function backUrl() {
    return '/locations';
}

function embedUrlFromLink(url: string) {
    // If it's already an embed URL, use as is
    if (url.includes('/embed')) return url;
    // Try to transform typical Google Maps share links to embed form
    try {
        const u = new URL(url);
        // If coordinates are present in query (like ?q=lat,lng)
        const q = u.searchParams.get('q');
        if (q && /^-?\d+(\.\d+)?\s*,\s*-?\d+(\.\d+)?$/.test(q)) {
            const [lat, lng] = q.split(/\s*,\s*/);
            return embedUrlFromCoords(lat, lng);
        }
    } catch {}
    // Fallback: let Google try to resolve the place by query parameter
    const encoded = encodeURIComponent(url);
    return `https://www.google.com/maps/embed/v1/search?key=&q=${encoded}`;
}

function embedUrlFromCoords(lat: string, lng: string) {
    // Using maps embed without API key through standard /embed with query
    const q = encodeURIComponent(`${lat},${lng}`);
    return `https://maps.google.com/maps?q=${q}&z=14&output=embed`;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: spillexpo().url },
    { title: 'Locations', href: index().url },
    { title: location.name, href: '' },
];
</script>

<template>
    <Head :title="location.name" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <div class="mb-4 flex items-center justify-between">
                <a :href="backUrl()" class="text-sm text-muted-foreground hover:underline">← Back to Locations</a>
                <span class="rounded-full bg-muted px-3 py-1 text-xs text-foreground/80">Postal code: {{ location.postal_code }}</span>
            </div>

            <div class="rounded-xl border border-sidebar-border/70 bg-white p-6 shadow-sm dark:border-sidebar-border dark:bg-[#161615]">
                <h1 class="mb-2 text-3xl font-semibold">{{ location.name }}</h1>


                <div class="grid gap-6 lg:grid-cols-2">
                    <div>
                        <div v-if="location.description" class="prose mb-6 max-w-none">
                            <p>{{ location.description }}</p>
                        </div>
                        <div class="mb-6 grid gap-2 sm:grid-cols-3 sm:gap-3">
                            <div class="text-sm font-medium text-muted-foreground">Address</div>
                            <div class="sm:col-span-2">
                                <span v-if="location.street_address || location.street_number">{{ location.street_address }} {{ location.street_number }}</span>
                                <span v-else class="text-muted-foreground">—</span>
                            </div>

                            <div class="text-sm font-medium text-muted-foreground">Website</div>
                            <div class="sm:col-span-2">
                                <a v-if="location.link" :href="location.link" target="_blank" rel="noopener" class="underline">{{ location.link }}</a>
                                <span v-else class="text-muted-foreground">—</span>
                            </div>

                            <div class="text-sm font-medium text-muted-foreground">Map link</div>
                            <div class="sm:col-span-2">
                                <span v-if="location.latitude && location.longitude" class="text-foreground/80">{{ location.latitude }}, {{ location.longitude }}</span>
                                <span v-else class="text-muted-foreground">—</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div v-if="location.google_maps_url || (location.latitude && location.longitude)" class="mt-0">
                            <h2 class="mb-2 text-lg font-semibold">Map</h2>
                            <div class="overflow-hidden rounded-lg border shadow-sm">
                                <iframe
                                    v-if="location.google_maps_url"
                                    :src="embedUrlFromLink(location.google_maps_url)"
                                    width="100%"
                                    height="360"
                                    style="border: 0"
                                    allowfullscreen=""
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"
                                ></iframe>
                                <iframe
                                    v-else
                                    :src="embedUrlFromCoords(location.latitude as string, location.longitude as string)"
                                    width="100%"
                                    height="360"
                                    style="border: 0"
                                    allowfullscreen=""
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"
                                ></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.prose p {
    white-space: pre-wrap;
}
</style>
