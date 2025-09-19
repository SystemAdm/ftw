<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes/admin';
import type { BreadcrumbItem } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface EventForm {
    id?: number;
    title: string;
    slug?: string | null;
    excerpt?: string | null;
    description?: string | null;
    image_path?: string | null;
    location_id?: number | null;
    event_start: string; // ISO local date-time (YYYY-MM-DDTHH:mm)
    event_end?: string | null;
    signup_needed?: boolean;
    signup_start?: string | null;
    signup_end?: string | null;
    age_min?: number | null;
    age_max?: number | null;
    number_of_seats?: number | null;
    status?: 'draft' | 'active' | null;
}

const page = usePage<{ event: Partial<EventForm> | null; locations?: Array<{ id: number; name: string }> }>();
const initial = page.props.event ?? null;
const isEdit = computed(() => !!initial && !!initial.id);
const locations = computed(() => page.props.locations ?? []);

const uploading = ref(false);
const uploadError = ref<string | null>(null);
const fileInputRef = ref<HTMLInputElement | null>(null);

const form = useForm<EventForm>({
    title: (initial?.title as any) ?? '',
    slug: (initial?.slug as any) ?? '',
    excerpt: (initial?.excerpt as any) ?? '',
    description: (initial?.description as any) ?? '',
    image_path: (initial?.image_path as any) ?? '',
    location_id: (initial?.location_id as any) ?? null,
    event_start: initial?.event_start ? new Date(initial.event_start as any).toISOString().slice(0, 16) : '',
    event_end: initial?.event_end ? new Date(initial.event_end as any).toISOString().slice(0, 16) : '',
    signup_needed: (initial?.signup_needed as any) ?? false,
    signup_start: initial?.signup_start ? new Date(initial.signup_start as any).toISOString().slice(0, 16) : '',
    signup_end: initial?.signup_end ? new Date(initial.signup_end as any).toISOString().slice(0, 16) : '',
    age_min: (initial?.age_min as any) ?? null,
    age_max: (initial?.age_max as any) ?? null,
    number_of_seats: (initial?.number_of_seats as any) ?? null,
    status: (initial?.status as any) ?? null,
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: dashboard().url },
    { title: 'Events', href: '/admin/events' },
    { title: isEdit.value ? 'Edit' : 'Create', href: '#' },
];

function getCsrfToken(): string {
    const el = document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null;
    return el?.content ?? '';
}

async function uploadFile(file: File) {
    uploadError.value = null;
    uploading.value = true;
    try {
        const fd = new FormData();
        fd.append('image', file);
        fd.append('folder', 'events');
        const res = await fetch('/admin/uploads/images', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': getCsrfToken() },
            body: fd,
        });
        if (!res.ok) {
            const text = await res.text();
            throw new Error(text || 'Upload failed');
        }
        const data = await res.json();
        (form as any).image_path = data.path || data.url;
    } catch (e: any) {
        uploadError.value = e?.message || 'Upload failed';
    } finally {
        uploading.value = false;
    }
}

function triggerFile() {
    fileInputRef.value?.click();
}

function onFileChange(e: any) {
    const file = e?.target?.files?.[0];
    if (!file) return;
    uploadFile(file);
    // reset input so selecting the same file again triggers change
    e.target.value = '';
}

function submit() {
    const payload: any = { ...form.data() };
    // Convert empty strings from datetime-local back to null
    for (const k of ['event_end', 'signup_start', 'signup_end']) {
        if ((payload[k] as any) === '') payload[k] = null;
    }
    if (isEdit.value && initial?.id) {
        form.transform(() => payload).put(`/admin/events/${initial.id}`);
    } else {
        form.transform(() => payload).post('/admin/events');
    }
}

function goBack() {
    router.get('/admin/events');
}
</script>

<template>
    <Head :title="isEdit ? 'Edit Event' : 'Create Event'" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="relative flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <div class="overflow-auto rounded-xl">
                    <div class="flex items-center justify-between border-b border-sidebar-border/70 p-4 dark:border-sidebar-border">
                        <h1 class="text-xl font-semibold">{{ isEdit ? 'Edit Event' : 'Create Event' }}</h1>
                    </div>

                    <form class="space-y-4 p-4" @submit.prevent="submit">
                        <div>
                            <Label for="title">Title</Label>
                            <Input id="title" v-model="form.title" />
                            <div v-if="form.errors.title" class="text-sm text-red-500">{{ form.errors.title }}</div>
                        </div>

                        <div>
                            <Label for="slug">Slug</Label>
                            <Input id="slug" v-model="form.slug" placeholder="Auto from title if left empty" />
                            <div v-if="form.errors.slug" class="text-sm text-red-500">{{ form.errors.slug }}</div>
                        </div>

                        <div>
                            <Label for="excerpt">Excerpt</Label>
                            <Textarea id="excerpt" v-model="form.excerpt" class="w-full rounded-md border px-3 py-2" />
                            <div v-if="form.errors.excerpt" class="text-sm text-red-500">{{ form.errors.excerpt }}</div>
                        </div>

                        <div>
                            <Label for="description">Description (Markdown supported)</Label>
                            <Textarea id="description" v-model="form.description" class="min-h-[200px] w-full rounded-md border px-3 py-2" />
                            <div v-if="form.errors.description" class="text-sm text-red-500">{{ form.errors.description }}</div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <Label for="event_start">Event start</Label>
                                <Input id="event_start" type="datetime-local" v-model="form.event_start" />
                                <div v-if="form.errors.event_start" class="text-sm text-red-500">{{ form.errors.event_start }}</div>
                            </div>
                            <div>
                                <Label for="event_end">Event end</Label>
                                <Input id="event_end" type="datetime-local" v-model="form.event_end" />
                                <div v-if="form.errors.event_end" class="text-sm text-red-500">{{ form.errors.event_end }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div>
                                <Label for="signup_needed" class="inline-flex items-center gap-2">
                                    <input id="signup_needed" type="checkbox" v-model="form.signup_needed" class="h-4 w-4" />
                                    Signup needed
                                </Label>
                                <div v-if="form.errors.signup_needed" class="text-sm text-red-500">{{ form.errors.signup_needed }}</div>
                            </div>
                            <div>
                                <Label for="signup_start">Signup start</Label>
                                <Input id="signup_start" type="datetime-local" v-model="form.signup_start" :disabled="!form.signup_needed" />
                                <div v-if="form.errors.signup_start" class="text-sm text-red-500">{{ form.errors.signup_start }}</div>
                            </div>
                            <div>
                                <Label for="signup_end">Signup end</Label>
                                <Input id="signup_end" type="datetime-local" v-model="form.signup_end" :disabled="!form.signup_needed" />
                                <div v-if="form.errors.signup_end" class="text-sm text-red-500">{{ form.errors.signup_end }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div>
                                <Label for="age_min">Age min</Label>
                                <Input id="age_min" type="number" min="0" max="120" v-model.number="(form as any).age_min" />
                                <div v-if="form.errors.age_min" class="text-sm text-red-500">{{ form.errors.age_min }}</div>
                            </div>
                            <div>
                                <Label for="age_max">Age max</Label>
                                <Input id="age_max" type="number" min="0" max="120" v-model.number="(form as any).age_max" />
                                <div v-if="form.errors.age_max" class="text-sm text-red-500">{{ form.errors.age_max }}</div>
                            </div>
                            <div>
                                <Label for="number_of_seats">Number of seats</Label>
                                <Input id="number_of_seats" type="number" min="1" v-model.number="(form as any).number_of_seats" />
                                <div v-if="form.errors.number_of_seats" class="text-sm text-red-500">{{ form.errors.number_of_seats }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <Label for="location_id">Location</Label>
                                <Select
                                    :model-value="form.location_id != null ? String(form.location_id) : ''"
                                    @update:model-value="(v: any) => ((form as any).location_id = v ? Number(v) : null)"
                                >
                                    <SelectTrigger id="location_id"><SelectValue placeholder="Optional" /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="-">— None —</SelectItem>
                                        <SelectItem v-for="loc in locations" :key="loc.id" :value="String(loc.id)">{{ loc.name }}</SelectItem>
                                    </SelectContent>
                                </Select>
                                <div v-if="form.errors.location_id" class="text-sm text-red-500">{{ form.errors.location_id }}</div>
                            </div>
                            <div>
                                <Label for="image_path">Image</Label>
                                <div class="mt-1 flex items-center gap-2">
                                    <Input
                                        id="image_path"
                                        v-model="form.image_path"
                                        placeholder="/storage/events/hero.jpg or https://..."
                                        class="flex-1"
                                    />
                                    <input ref="fileInputRef" type="file" accept="image/*" class="hidden" @change="onFileChange" />
                                    <Button type="button" variant="secondary" @click="triggerFile" :disabled="uploading">{{
                                        uploading ? 'Uploading...' : 'Upload'
                                    }}</Button>
                                </div>
                                <div v-if="uploadError" class="text-sm text-red-500">{{ uploadError }}</div>
                                <div v-if="form.image_path" class="mt-2">
                                    <img :src="form.image_path" alt="Image preview" class="h-24 rounded border object-cover" />
                                </div>
                                <div v-if="form.errors.image_path" class="text-sm text-red-500">{{ form.errors.image_path }}</div>
                            </div>
                        </div>

                        <div>
                            <Label for="status">Status</Label>
                            <Select >
                                <SelectTrigger>
                                    <SelectValue placeholder="—" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectLabel>Status</SelectLabel>
                                        <SelectItem value="draft">Draft</SelectItem>
                                        <SelectItem value="active">Active</SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <div v-if="form.errors.status" class="text-sm text-red-500">{{ form.errors.status }}</div>
                        </div>

                        <div class="flex gap-2">
                            <Button type="submit">Save</Button>
                            <Button type="button" variant="secondary" @click="goBack()">Cancel</Button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
