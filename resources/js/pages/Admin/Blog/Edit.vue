<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { TagsInput, TagsInputInput, TagsInputItem, TagsInputItemDelete, TagsInputItemText } from '@/components/ui/tags-input';
import AppLayout from '@/layouts/AppLayout.vue';
import { cn } from '@/lib/utils';
import { dashboard } from '@/routes/admin';
import type { BreadcrumbItem } from '@/types';
import { faCalendar } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { getLocalTimeZone, parseDate } from '@internationalized/date';
import { nextTick, ref } from 'vue';

interface Blog {
    id?: number;
    title: string;
    slug?: string | null;
    excerpt?: string | null;
    content?: string | null;
    published_at?: string | null;
    tags?: string[];
}

const page = usePage<{ blog: Blog | null }>();
const blog = page.props.blog;
const isEdit = !!blog?.id;

const form = useForm<Blog>({
    title: blog?.title ?? '',
    slug: blog?.slug ?? '',
    excerpt: blog?.excerpt ?? '',
    content: blog?.content ?? '',
    published_at: blog?.published_at ? new Date(blog.published_at as any).toISOString().slice(0, 16) : '',
    // Normalize tag names if Tag name is translatable/object
    tags: ((blog as any)?.tags ?? [])
        .map((t: any) => {
            const n = t?.name as any;
            if (typeof n === 'string') return n;
            if (n && typeof n === 'object') {
                const values = Object.values(n as Record<string, string>);
                return (values[0] as string) ?? '';
            }
            return '';
        })
        .filter(Boolean),
});

// Date picker state (DateValue from @internationalized/date)
let initialPicked: any = null;
if (blog?.published_at) {
    const d = new Date(blog.published_at as any);
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    initialPicked = parseDate(`${y}-${m}-${day}`);
}
const pickedDate = ref<any>(initialPicked);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: dashboard().url },
    { title: 'Blogs', href: '/admin/blogs' },
    { title: isEdit ? 'Edit' : 'Create', href: '#' },
];

async function submit() {
    // Ensure any pending input (e.g., Enter pressed in TagsInput) is flushed
    await nextTick();
    const payload: any = { ...form.data() };
    // Derive published_at from pickedDate (date-only); send ISO string or null
    if (pickedDate.value) {
        const d = pickedDate.value.toDate(getLocalTimeZone());
        // Ensure time is at local midnight before ISO conversion
        const atMidnight = new Date(d.getFullYear(), d.getMonth(), d.getDate());
        payload.published_at = atMidnight.toISOString();
    } else {
        payload.published_at = null;
    }
    if (isEdit && blog?.id) {
        form.transform(() => payload).put(`/admin/blogs/${blog.id}`);
    } else {
        form.transform(() => payload).post('/admin/blogs');
    }
}

function goBack() {
    router.get('/admin/blogs');
}
</script>

<template>
    <Head :title="isEdit ? 'Edit Blog' : 'Create Blog'" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="relative flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <div class="overflow-auto rounded-xl">
                    <div class="flex items-center justify-between border-b border-sidebar-border/70 p-4 dark:border-sidebar-border">
                        <h1 class="text-xl font-semibold">{{ isEdit ? 'Edit Blog' : 'Create Blog' }}</h1>
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
                            <Label for="content">Content (HTML allowed)</Label>
                            <Textarea id="content" v-model="form.content" class="min-h-[200px] w-full rounded-md border px-3 py-2" />
                            <div v-if="form.errors.content" class="text-sm text-red-500">{{ form.errors.content }}</div>
                        </div>
                        <div>
                            <Label for="published_at">Published at</Label>
                            <div class="mt-1">
                                <Popover>
                                    <PopoverTrigger as-child>
                                        <Button
                                            variant="outline"
                                            :class="cn('w-[280px] justify-start text-left font-normal', !pickedDate && 'text-muted-foreground')"
                                        >
                                            <span class="mr-2"><font-awesome-icon :icon="faCalendar" /></span>
                                            {{
                                                pickedDate
                                                    ? new Intl.DateTimeFormat(undefined, { dateStyle: 'medium' }).format(
                                                          pickedDate.toDate(getLocalTimeZone()),
                                                      )
                                                    : 'Pick a date'
                                            }}
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent class="w-auto p-0">
                                        <div class="p-2">
                                            <Calendar v-model="pickedDate" />
                                        </div>
                                        <div class="flex justify-end gap-2 border-t p-2">
                                            <Button size="sm" variant="secondary" @click="pickedDate = null">Clear</Button>
                                        </div>
                                    </PopoverContent>
                                </Popover>
                            </div>
                            <div v-if="form.errors.published_at" class="text-sm text-red-500">{{ form.errors.published_at }}</div>
                        </div>
                        <div>
                            <Label for="tags">Tags</Label>
                            <TagsInput :model-value="form.tags" @update:model-value="(v: any) => (form.tags = v)" class="mt-1">
                                <template v-for="t in form.tags" :key="t">
                                    <TagsInputItem :value="t" class="flex items-center gap-1">
                                        <TagsInputItemText />
                                        <TagsInputItemDelete />
                                    </TagsInputItem>
                                </template>
                                <TagsInputInput id="tags" placeholder="Add a tag and press Enter" />
                            </TagsInput>
                            <div v-if="form.errors.tags" class="text-sm text-red-500">{{ form.errors.tags }}</div>
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
