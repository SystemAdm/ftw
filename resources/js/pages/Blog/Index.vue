<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { spillexpo } from '@/routes';
import { Head, router, usePage } from '@inertiajs/vue3';
import type { BreadcrumbItem } from '@/types';
import { computed } from 'vue';

interface Blog {
  id: number;
  title: string;
  slug: string;
  excerpt?: string | null;
  published_at?: string | null;
}

const page = usePage<{ blogs: { data: Blog[] } }>();
const blogs = computed(() => page.props.blogs?.data ?? []);

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: spillexpo().url },
  { title: 'Blog', href: '/blog' },
];

function openBlog(slug: string) {
  router.get(`/blog/${slug}`);
}
</script>

<template>
  <Head title="Blog" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
      <div class="relative flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
        <div class="overflow-auto rounded-xl">
          <div class="flex items-center justify-between border-b border-sidebar-border/70 p-4 dark:border-sidebar-border">
            <h1 class="text-xl font-semibold">Blog</h1>
          </div>
          <div v-if="blogs.length === 0" class="m-4 rounded-xl border border-dashed p-8 text-center text-muted-foreground bg-muted/10">No posts yet.</div>
          <div v-else class="m-4 grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            <div v-for="b in blogs" :key="b.id" class="rounded-xl border p-4 bg-white dark:bg-[#161615] dark:border-sidebar-border border-sidebar-border/70">
              <h2 class="text-lg font-semibold">{{ b.title }}</h2>
              <div class="mt-2 text-xs text-muted-foreground">
                <span v-for="t in (b as any).tags ?? []" :key="t.name" class="mr-2 rounded bg-muted px-2 py-0.5">#{{ t.name }}</span>
              </div>
              <p class="mt-2 text-sm text-muted-foreground" v-if="b.excerpt">{{ b.excerpt }}</p>
              <div class="mt-3">
                <Button variant="secondary" size="sm" @click="openBlog(b.slug)">Read</Button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
