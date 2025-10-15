<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { spillexpo } from '@/routes';
import { index } from '@/routes/locations';
import type { BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

type Location = {
    id: number;
    postal_code: number;
    name: string;
    description?: string | null;
    google_maps_url?: string | null;
};

const page = usePage<{ locations: Location[]; filters?: any; sort?: any }>();
const rows = computed(() => page.props.locations ?? []);
const currentFilters = computed(() => page.props.filters ?? {});

// Toggle filters visibility; show if any filter active
const showFilters = ref(false);
if (page.props.filters) {
    const f = page.props.filters as Record<string, any>;
    showFilters.value = Object.keys(f).some((k) => f[k] !== undefined && f[k] !== null && f[k] !== '');
}

const searchText = ref<string>(currentFilters.value.search ?? '');

function apply(params: Record<string, any>) {
    const pruned = Object.fromEntries(Object.entries(params).filter(([, v]) => v !== '' && v !== null && v !== undefined));
    router.get('/locations', pruned, { preserveState: true, preserveScroll: true, replace: true });
}

function submitSearch() {
    apply({ ...currentFilters.value, search: searchText.value });
}

function clearSearch() {
    searchText.value = '';
    apply({});
}

function showUrl(id: number) {
    router.get(`/locations/${id}`);
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: spillexpo().url },
    { title: 'Locations', href: index().url },
];
</script>

<template>
    <Head title="Locations" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="relative flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <div class="overflow-auto rounded-xl">
                    <div class="flex items-center justify-between border-b border-sidebar-border/70 p-4 dark:border-sidebar-border">
                        <h1 class="text-xl font-semibold">Locations</h1>
                        <div class="flex items-center gap-2">
                            <Button variant="secondary" @click="showFilters = !showFilters" :aria-pressed="showFilters">
                                {{ showFilters ? 'Hide filters' : 'Show filters' }}
                            </Button>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-end gap-3 p-4 bg-muted/20 border-t border-sidebar-border/70" v-show="showFilters">
                        <Input
                            type="text"
                            v-model="searchText"
                            placeholder="Search locations..."
                            class="w-full rounded-md border px-3 py-2"
                            @keyup.enter="submitSearch"
                        />
                        <Button variant="default" @click="submitSearch">Search</Button>
                        <Button variant="secondary" @click="clearSearch">Reset</Button>
                    </div>

                    <div v-if="rows.length === 0" class="m-4 rounded-xl border border-dashed p-8 text-center text-muted-foreground bg-muted/10">No locations found.</div>

                    <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 m-4">
                        <div
                            v-for="l in rows"
                            :key="l.id"
                            class="group rounded-xl border border-sidebar-border/70 bg-white p-4 shadow-sm transition hover:shadow-md dark:border-sidebar-border dark:bg-[#161615]"
                        >
                            <div class="mb-2 flex items-start justify-between gap-2">
                                <h2 class="text-lg leading-snug font-semibold">{{ l.name }}</h2>
                                <Button variant="secondary" size="sm" @click="showUrl(l.id)">View</Button>
                            </div>
                            <p class="text-sm text-muted-foreground">Postal code: {{ l.postal_code }}</p>
                            <p v-if="l.description" class="mt-2 line-clamp-2 text-sm text-foreground/80">{{ l.description }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Minimal styling using utility classes */
</style>
