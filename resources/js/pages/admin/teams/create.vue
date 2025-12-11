<script setup lang="ts">
import { usePage, router } from '@inertiajs/vue3';
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { reactive } from 'vue';
import { Textarea } from '@/components/ui/textarea';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Field, FieldError, FieldLabel } from '@/components/ui/field';
import { Checkbox } from '@/components/ui/checkbox';
import { index } from '@/routes/admin/teams';

const page = usePage<PageProps>();
const usersList = (page.props as any).users ?? [];

const form = reactive({
    name: '',
    slug: '',
    description: '',
    logo: '',
    active: true,
    users: [] as number[],
});

const errors = reactive<Record<string, string[]>>({});

function submit() {
    router.post('/admin/teams', form, {
        onError: (err) => Object.assign(errors, err as any),
    });
}

function toggleUser(userId: number, checked: boolean | 'indeterminate') {
    const isChecked = checked === true;
    const idx = form.users.indexOf(userId);
    if (isChecked && idx === -1) form.users.push(userId);
    if (!isChecked && idx !== -1) form.users.splice(idx, 1);
}
</script>

<template>
    <SidebarLayout>
        <h1 class="mb-4 text-xl font-semibold">Create Team</h1>

        <form class="max-w-xl space-y-4" @submit.prevent="submit">
            <Field>
                <FieldLabel class="block text-sm font-medium">Name</FieldLabel>
                <Input
                    v-model="form.name"
                    class="mt-1 w-full rounded border px-3 py-2"
                    type="text"
                    :class="{ 'border-red-500': errors.name }"
                    @input="errors.name = ''"
                />
                <FieldError v-if="errors.name">{{ errors.name }}</FieldError>
            </Field>

            <div>
                <Label class="block text-sm font-medium">Slug</Label>
                <Input
                    v-model="form.slug"
                    class="mt-1 w-full rounded border px-3 py-2"
                    type="text"
                    :class="{ 'border-red-500': errors.slug }"
                    @input="errors.slug = ''"
                />
                <div v-if="errors.slug" class="mt-1 text-sm text-red-600">{{ errors.slug[0] }}</div>
            </div>

            <div>
                <Label class="block text-sm font-medium">Description</Label>
                <Textarea
                    v-model="form.description"
                    class="mt-1 w-full rounded border px-3 py-2"
                    :class="{ 'border-red-500': errors.description }"
                    @input="errors.description = ''"
                ></Textarea>
                <div v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description[0] }}</div>
            </div>

            <div>
                <label class="block text-sm font-medium">Logo URL</label>
                <input
                    v-model="form.logo"
                    class="mt-1 w-full rounded border px-3 py-2"
                    type="url"
                    :class="{ 'border-red-500': errors.logo }"
                    @input="errors.logo = ''"
                />
                <div v-if="errors.logo" class="mt-1 text-sm text-red-600">{{ errors.logo[0] }}</div>
            </div>

            <div>
                <Label class="block text-sm font-medium">Members</Label>
                <div class="mt-2 grid max-h-64 grid-cols-1 gap-2 overflow-y-auto rounded border p-3 sm:grid-cols-2">
                    <label v-for="u in usersList" :key="u.id" class="flex items-center gap-2">
                        <Checkbox
                            :model-value="form.users.includes(u.id)"
                            @update:model-value="(val) => toggleUser(u.id, val as boolean | 'indeterminate')"
                            :aria-label="`Select ${u.name}`"
                        />
                        <span class="text-sm">{{ u.name }}</span>
                    </label>
                </div>
                <div v-if="errors.users" class="mt-1 text-sm text-red-600">{{ errors.users[0] }}</div>
            </div>

            <label class="inline-flex items-center gap-2">
                <Checkbox :model-value="form.active" @update:model-value="(v) => (form.active = v === true)" />
                <span>Active</span>
            </label>

            <div class="flex gap-2">
                <Button>Save</Button>
                <Button variant="secondary" @click.prevent="router.visit(index.url())">Cancel</Button>
            </div>
        </form>
    </SidebarLayout>
</template>
