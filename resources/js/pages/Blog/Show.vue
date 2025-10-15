<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { faFaceLaugh, faHeart, faThumbsUp } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon as fontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { dashboard } from '@/routes';

interface User {
    id: number;
    name: string;
}
interface Tag {
    name: string;
}
interface Comment {
    id: number;
    body: string;
    created_at: string;
    user?: User | null;
} // user is included for ownership check
interface ReactionSummary {
    type: string;
    count: number;
}

interface Blog {
    id: number;
    title: string;
    slug: string;
    content?: string | null;
    excerpt?: string | null;
    tags?: Tag[];
    comments?: Comment[];
}

const page = usePage<{ blog: Blog; reactions?: { summary: ReactionSummary[]; mine: string[] }; auth?: { user?: User | null } }>();
const blog = computed(() => page.props.blog);
const authUser = computed(() => page.props.auth?.user ?? null);
const reactions = computed(() => page.props.reactions ?? { summary: [], mine: [] });

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Blog', href: '/blog' },
    { title: blog.value?.title ?? 'Post', href: `/blog/${blog.value?.slug}` },
];

const commentBody = ref<string>('');

function postComment() {
    if (!commentBody.value.trim()) return;
    router.post(
        `/blog/${blog.value.slug}/comments`,
        { body: commentBody.value },
        {
            onSuccess: () => {
                commentBody.value = '';
            },
            preserveScroll: true,
        },
    );
}

const types = ['like', 'love', 'laugh'];

function react(type: string) {
    router.post(`/blog/${blog.value.slug}/reactions`, { type }, { preserveScroll: true });
}
function unreact(type: string) {
    router.delete(`/blog/${blog.value.slug}/reactions`, { data: { type }, preserveScroll: true });
}

function countFor(type: string) {
    return reactions.value.summary.find((s) => s.type === type)?.count ?? 0;
}
function mine(type: string) {
    return (reactions.value.mine ?? []).includes(type);
}

function deleteComment(id: number) {
    router.delete(`/blog/${blog.value.slug}/comments/${id}`, { preserveScroll: true });
}
</script>

<template>
    <Head :title="blog.title" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="prose dark:prose-invert max-w-none p-6">
            <h1 class="mb-2">{{ blog.title }}</h1>
            <div class="mb-4 text-sm text-muted-foreground">
                <span v-for="t in (blog.tags as any[]) ?? []" :key="t.name" class="mr-2 rounded bg-muted px-2 py-1">#{{ t.name }}</span>
            </div>
            <div v-html="blog.content || blog.excerpt || ''"></div>

            <div class="not-prose mt-6 border-t pt-4">
                <div class="mb-3 flex gap-2">
                    <template v-for="t in types" :key="t">
                        <Button size="sm" :variant="mine(t) ? 'secondary' : 'outline'" @click="mine(t) ? unreact(t) : react(t)">
                            <font-awesome-icon v-if="t === 'like'" :icon="faThumbsUp" class="mr-1 text-orange-300" />
                            <font-awesome-icon v-else-if="t === 'love'" :icon="faHeart" class="mr-1 text-red-500" />
                            <font-awesome-icon v-else-if="t === 'laugh'" :icon="faFaceLaugh" class="mr-1 text-yellow-500" />
                            ({{ countFor(t) }})
                        </Button>
                    </template>
                </div>
            </div>

            <div class="not-prose mt-8">
                <h2 class="mb-3 text-lg font-semibold">Comments</h2>
                <div v-if="authUser" class="mb-4 flex gap-2">
                    <Input class="flex-1" placeholder="Write a comment..." v-model="commentBody" @keyup.enter="postComment" />
                    <Button @click="postComment">Post</Button>
                </div>
                <div v-else class="mb-4 text-sm text-muted-foreground"><Link href="/login" class="underline">Login</Link> to comment.</div>
                <div v-if="(blog.comments?.length ?? 0) === 0" class="text-sm text-muted-foreground">No comments yet.</div>
                <ul v-else class="space-y-3">
                    <li v-for="c in blog.comments" :key="c.id" class="rounded-md border p-3">
                        <div class="mb-1 flex items-center justify-between text-sm font-medium">
                            <div>
                                {{ c.user?.name ?? 'Anonymous' }}
                                <span class="ml-2 text-xs text-muted-foreground">{{ c.posted }}</span>
                            </div>
                            <div v-if="authUser && c.user?.id === authUser.id">
                                <Button size="sm" variant="destructive" class="h-7 px-2 text-white" @click="deleteComment(c.id)">Delete</Button>
                            </div>
                        </div>
                        <div class="text-sm">{{ c.body }}</div>
                    </li>
                </ul>
            </div>

            <div class="mt-8">
                <Link href="/blog" class="text-primary underline">Back to blog</Link>
            </div>
        </div>
    </AppLayout>
</template>
