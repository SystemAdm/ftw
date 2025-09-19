<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { dashboard } from '@/routes/admin';
import { create, index } from '@/routes/admin/locations';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faBan, faFloppyDisk } from '@fortawesome/free-solid-svg-icons';
import { Button } from '@/components/ui/button';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: dashboard().url },
    { title: 'Locations', href: index().url },
    { title: 'Create', href: create().url },
];

const form = useForm({
    postal_code: '',
    name: '',
    active: false,
    description: '',
    latitude: '',
    longitude: '',
    google_maps_url: '',
    images: '',
    street_address: '',
    street_number: '',
    link: '',
});

function submit() {
    form.post('/admin/locations', {
        preserveScroll: true,
    });
}
function cancel() {
    router.get('/admin/locations', {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Create Location" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="relative flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <form @submit.prevent="submit" class="space-y-4 p-6">
                    <h1 class="text-xl font-semibold">Create Location</h1>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium" for="postal_code">Postal Code</label>
                            <input id="postal_code" v-model.number="form.postal_code" type="number" class="w-full rounded-md border border-sidebar-border/70 px-3 py-2 focus:outline-none" />
                            <p v-if="form.errors.postal_code" class="mt-1 text-sm text-red-600">{{ form.errors.postal_code }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium" for="name">Name</label>
                            <input id="name" v-model="form.name" type="text" class="w-full rounded-md border border-sidebar-border/70 px-3 py-2 focus:outline-none" />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium" for="active">Active</label>
                            <input id="active" v-model="form.active" type="checkbox" class="h-4 w-4" />
                            <p v-if="form.errors.active" class="mt-1 text-sm text-red-600">{{ form.errors.active }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium" for="google_maps_url">Google Maps URL</label>
                            <input id="google_maps_url" v-model="form.google_maps_url" type="text" class="w-full rounded-md border border-sidebar-border/70 px-3 py-2 focus:outline-none" />
                            <p v-if="form.errors.google_maps_url" class="mt-1 text-sm text-red-600">{{ form.errors.google_maps_url }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium" for="latitude">Latitude</label>
                            <input id="latitude" v-model="form.latitude" type="text" class="w-full rounded-md border border-sidebar-border/70 px-3 py-2 focus:outline-none" />
                            <p v-if="form.errors.latitude" class="mt-1 text-sm text-red-600">{{ form.errors.latitude }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium" for="longitude">Longitude</label>
                            <input id="longitude" v-model="form.longitude" type="text" class="w-full rounded-md border border-sidebar-border/70 px-3 py-2 focus:outline-none" />
                            <p v-if="form.errors.longitude" class="mt-1 text-sm text-red-600">{{ form.errors.longitude }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-medium" for="description">Description</label>
                            <textarea id="description" v-model="form.description" class="w-full rounded-md border border-sidebar-border/70 px-3 py-2 focus:outline-none"></textarea>
                            <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium" for="street_address">Street Address</label>
                            <input id="street_address" v-model="form.street_address" type="text" class="w-full rounded-md border border-sidebar-border/70 px-3 py-2 focus:outline-none" />
                            <p v-if="form.errors.street_address" class="mt-1 text-sm text-red-600">{{ form.errors.street_address }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium" for="street_number">Street Number</label>
                            <input id="street_number" v-model="form.street_number" type="text" class="w-full rounded-md border border-sidebar-border/70 px-3 py-2 focus:outline-none" />
                            <p v-if="form.errors.street_number" class="mt-1 text-sm text-red-600">{{ form.errors.street_number }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-medium" for="images">Images (JSON or CSV)</label>
                            <input id="images" v-model="form.images" type="text" class="w-full rounded-md border border-sidebar-border/70 px-3 py-2 focus:outline-none" />
                            <p v-if="form.errors.images" class="mt-1 text-sm text-red-600">{{ form.errors.images }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-medium" for="link">Link</label>
                            <input id="link" v-model="form.link" type="text" class="w-full rounded-md border border-sidebar-border/70 px-3 py-2 focus:outline-none" />
                            <p v-if="form.errors.link" class="mt-1 text-sm text-red-600">{{ form.errors.link }}</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 border-t border-sidebar-border/70 pt-4">
                        <Button variant="destructive" @click.prevent="cancel()"><font-awesome-icon :icon="faBan" />Cancel</Button>
                        <Button type="submit" :disabled="form.processing" class="inline-flex items-center gap-2">
                            <font-awesome-icon :icon="faFloppyDisk" />Create
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
