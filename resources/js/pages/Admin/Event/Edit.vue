<script setup lang="ts">
import ImagePicker from '@/components/ImagePicker.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { NumberField, NumberFieldContent, NumberFieldDecrement, NumberFieldIncrement, NumberFieldInput } from '@/components/ui/number-field';
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes/admin';
import type { BreadcrumbItem } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

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

function toLocalDateTimeInput(value?: string | null): string {
    if (!value) return '';
    const d = new Date(value);
    const pad = (n: number) => String(n).padStart(2, '0');
    const yyyy = d.getFullYear();
    const mm = pad(d.getMonth() + 1);
    const dd = pad(d.getDate());
    const hh = pad(d.getHours());
    const min = pad(d.getMinutes());
    return `${yyyy}-${mm}-${dd}T${hh}:${min}`; // local time for datetime-local inputs
}

const form = useForm<EventForm>({
    title: (initial?.title as any) ?? '',
    slug: (initial?.slug as any) ?? '',
    excerpt: (initial?.excerpt as any) ?? '',
    description: (initial?.description as any) ?? '',
    image_path: (initial?.image_path as any) ?? '',
    location_id: (initial?.location_id as any) ?? null,
    event_start: toLocalDateTimeInput(initial?.event_start as any),
    event_end: toLocalDateTimeInput(initial?.event_end as any),
    signup_needed: (initial?.signup_needed as any) ?? false,
    signup_start: toLocalDateTimeInput(initial?.signup_start as any),
    signup_end: toLocalDateTimeInput(initial?.signup_end as any),
    age_min: (initial?.age_min as any) ?? null,
    age_max: (initial?.age_max as any) ?? null,
    number_of_seats: (initial?.number_of_seats as any) ?? null,
    status: isEdit.value ? ((initial?.status as any) ?? 'draft') : 'draft',
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: dashboard().url },
    { title: 'Events', href: '/admin/events' },
    { title: isEdit.value ? 'Edit' : 'Create', href: '#' },
];

const showImagePicker = ref(false);
function onImageSelected(path: string) {
    (form as any).image_path = path;
}

// Controls to disable age fields
const disableAgeMin = ref<boolean>((form as any).age_min == null);
const disableAgeMax = ref<boolean>((form as any).age_max == null);

function changeAgeMin(v: boolean | 'indeterminate') {
    disableAgeMin.value = v === true;
}
function changeAgeMax(v: boolean | 'indeterminate') {
    disableAgeMax.value = v === true;
}

function changeSignupNeeded(v: boolean | 'indeterminate') {
    form.signup_needed = v === true;
}

watch(disableAgeMin, (v) => {
    if (v) (form as any).age_min = null;
});
watch(disableAgeMax, (v) => {
    if (v) (form as any).age_max = null;
});

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
                                    <Checkbox id="signup_needed"  :modelValue="form.signup_needed" @update:modelValue="changeSignupNeeded" class="h-4 w-4" />
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
                                <div class="flex items-center justify-between">
                                    <Label for="age_min">Age min</Label>
                                    </div>
                                <div class="flex flex-nowrap items-center justify-between gap-5">
                                    <div class="flex items-center gap-2">
                                        <Checkbox
                                            id="disable_age_min"
                                            :modelValue="disableAgeMin"
                                            @update:modelValue="changeAgeMin"
                                            class="h-4 w-4"
                                        />
                                        Disable
                                    </div>
                                    <NumberField class="w-full">
                                        <NumberFieldContent>
                                            <NumberFieldDecrement />
                                            <NumberFieldInput
                                                id="age_min"
                                                :min="0"
                                                :max="120"
                                                v-model.number="(form as any).age_min"
                                                :disabled="disableAgeMin"
                                            />
                                            <NumberFieldIncrement />
                                        </NumberFieldContent>
                                    </NumberField>
                                </div>

                                <div v-if="form.errors.age_min" class="text-sm text-red-500">{{ form.errors.age_min }}</div>
                            </div>
                            <div>
                                <div class="flex items-center justify-between">
                                    <Label for="age_max">Age max</Label>
                                </div>
                                <div class="flex flex-nowrap items-center justify-between gap-5">
                                    <div class="flex items-center gap-2">
                                        <Checkbox
                                            id="disable_age_max"
                                            :modelValue="disableAgeMax"
                                            @update:modelValue="changeAgeMax"
                                            class="h-4 w-4"
                                        />
                                        Disable
                                    </div>
                                    <NumberField class="w-full">
                                        <NumberFieldContent>
                                            <NumberFieldDecrement />
                                            <NumberFieldInput
                                                id="age_max"
                                                :min="0"
                                                :max="120"
                                                v-model.number="(form as any).age_max"
                                                :disabled="disableAgeMax"
                                            />
                                            <NumberFieldIncrement />
                                        </NumberFieldContent>
                                    </NumberField>
                                </div>
                                <div v-if="form.errors.age_max" class="text-sm text-red-500">{{ form.errors.age_max }}</div>
                            </div>
                            <div>
                                <Label for="number_of_seats">Number of seats</Label>
                                <NumberField>
                                    <NumberFieldContent>
                                        <NumberFieldDecrement />
                                        <NumberFieldInput id="number_of_seats" :min="0" v-model.number="(form as any).number_of_seats" />
                                        <NumberFieldIncrement />
                                    </NumberFieldContent>
                                </NumberField>
                                <div v-if="form.errors.number_of_seats" class="text-sm text-red-500">{{ form.errors.number_of_seats }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <Label for="location_id">Location</Label>
                                <Select
                                    :model-value="form.location_id != null ? String(form.location_id) : ''"
                                    @update:model-value="(v: any) => ((form as any).location_id = v && v !== 'none' ? Number(v) : null)"
                                >
                                    <SelectTrigger id="location_id"><SelectValue placeholder="Optional" /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="none">— None —</SelectItem>
                                        <SelectItem v-for="loc in locations" :key="loc.id" :value="String(loc.id)">{{ loc.name }}</SelectItem>
                                    </SelectContent>
                                </Select>
                                <div v-if="form.errors.location_id" class="text-sm text-red-500">{{ form.errors.location_id }}</div>
                            </div>
                            <div>
                                <Label for="image_path">Image</Label>
                                <div class="mt-1 flex items-start gap-4">
                                    <div class="w-48">
                                        <div v-if="form.image_path" class="aspect-video w-48 overflow-hidden rounded border">
                                            <img :src="form.image_path" alt="Selected image preview" class="h-full w-full object-cover" />
                                        </div>
                                        <div
                                            v-else
                                            class="flex aspect-video w-48 items-center justify-center rounded border bg-gray-100 text-xs text-gray-500 dark:bg-gray-800/50"
                                        >
                                            No image selected
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <Button type="button" variant="secondary" @click="showImagePicker = true">Select image</Button>
                                        <div v-if="form.image_path" class="text-xs break-all text-gray-500">{{ form.image_path }}</div>
                                    </div>
                                </div>
                                <div v-if="form.errors.image_path" class="text-sm text-red-500">{{ form.errors.image_path }}</div>

                                <ImagePicker
                                    :open="showImagePicker"
                                    folder="events"
                                    @update:open="(v: boolean) => (showImagePicker = v)"
                                    @select="onImageSelected"
                                />
                            </div>
                        </div>

                        <div>
                            <Label for="status">Status</Label>
                            <Select
                                :model-value="form.status ?? ''"
                                @update:model-value="(v: any) => ((form as any).status = v && v !== 'none' ? v : null)"
                            >
                                <SelectTrigger id="status">
                                    <SelectValue placeholder="—" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectLabel>Status</SelectLabel>
                                        <SelectItem value="none">—</SelectItem>
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
