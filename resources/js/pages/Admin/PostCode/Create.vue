<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { dashboard } from '@/routes/admin';
import { create, index } from '@/routes/admin/postcodes';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faBan, faFloppyDisk } from '@fortawesome/free-solid-svg-icons';
import { Button } from '@/components/ui/button';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: dashboard().url },
    { title: 'Post Codes', href: index().url },
    { title: 'Create', href: create().url },
];

const form = useForm({
    postal_code: '',
    city: '',
    state: '',
    country: '',
    county: '',
});

function submit() {
    form.post('/admin/postcodes', {
        preserveScroll: true,
    });
}
function cancel() {
    router.get('/admin/postcodes', {
        preserveScroll: true,
    });
}

</script>

<template>
    <Head title="Create Post Code" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="relative flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <form @submit.prevent="submit" class="space-y-4 p-6">
                    <h1 class="text-xl font-semibold">Create Post Code</h1>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium" for="postal_code">Postal Code</label>
                            <input
                                id="postal_code"
                                v-model="form.postal_code"
                                type="text"
                                class="w-full rounded-md border border-sidebar-border/70 px-3 py-2 focus:outline-none"
                            />
                            <p v-if="form.errors.postal_code" class="mt-1 text-sm text-red-600">{{ form.errors.postal_code }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium" for="city">City</label>
                            <input
                                id="city"
                                v-model="form.city"
                                type="text"
                                class="w-full rounded-md border border-sidebar-border/70 px-3 py-2 focus:outline-none"
                            />
                            <p v-if="form.errors.city" class="mt-1 text-sm text-red-600">{{ form.errors.city }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium" for="state">State</label>
                            <input
                                id="state"
                                v-model="form.state"
                                type="text"
                                class="w-full rounded-md border border-sidebar-border/70 px-3 py-2 focus:outline-none"
                            />
                            <p v-if="form.errors.state" class="mt-1 text-sm text-red-600">{{ form.errors.state }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium" for="country">Country</label>
                            <input
                                id="country"
                                v-model="form.country"
                                type="text"
                                class="w-full rounded-md border border-sidebar-border/70 px-3 py-2 focus:outline-none"
                            />
                            <p v-if="form.errors.country" class="mt-1 text-sm text-red-600">{{ form.errors.country }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-medium" for="county">County</label>
                            <input
                                id="county"
                                v-model="form.county"
                                type="text"
                                class="w-full rounded-md border border-sidebar-border/70 px-3 py-2 focus:outline-none"
                            />
                            <p v-if="form.errors.county" class="mt-1 text-sm text-red-600">{{ form.errors.county }}</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 border-t border-sidebar-border/70 pt-4">
                        <Button variant="destructive" @click.prevent="cancel()"><font-awesome-icon :icon="faBan" />Cancel</Button>
                        <Button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center gap-2 rounded-md bg-primary px-3 py-2 text-sm font-medium text-primary-foreground shadow hover:opacity-95 disabled:opacity-70"
                        >
                            <font-awesome-icon :icon="faFloppyDisk" />Create
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
