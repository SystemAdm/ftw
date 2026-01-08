<script setup lang="ts">
import AppSidebar from '@/components/custom/AppSidebar.vue';
import { SidebarInset, SidebarProvider, SidebarTrigger } from '@/components/ui/sidebar';
import { Breadcrumb, BreadcrumbItem, BreadcrumbLink, BreadcrumbList, BreadcrumbPage, BreadcrumbSeparator } from '@/components/ui/breadcrumb';
import { BreadcrumbItem as BreadcrumbItemType } from '@/types';
import { Separator } from '@/components/ui/separator';
import Footer from '@/components/layouts/Footer.vue';
import { Toaster } from '@/components/ui/sonner'
import { usePage } from '@inertiajs/vue3'
import { useFlashToasts } from '@/composables/useFlashToasts'
import BannedUserDialog from '@/components/custom/BannedUserDialog.vue'
import { computed, ref, watch } from 'vue'

// Bridge Laravel flash messages -> Sonner toasts with dedupe
defineProps<{
    breadcrumbs?: BreadcrumbItemType[];
}>();

const page = usePage()
useFlashToasts(page)

const banMessage = computed(() => (page.props as any).ban_message as string | null);
const showBanDialog = ref(false);

watch(banMessage, (val) => {
    if (val) showBanDialog.value = true;
}, { immediate: true });
</script>

<template>
    <SidebarProvider>
        <!-- Global toast provider (Sonner) for settings area -->
        <Toaster
          position="top-right"
          rich-colors
          close-button
          expand
          theme="system"
          offset="64px"
          :duration="4000"
        />

        <!-- Banned User Dialog -->
        <BannedUserDialog v-if="showBanDialog" :message="banMessage" @close="showBanDialog = false" />

        <AppSidebar />
        <SidebarInset>
            <header
                class="flex h-16 shrink-0 items-center gap-2 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12"
            >
                <div class="flex items-center gap-2 px-4">
                    <SidebarTrigger class="-ml-1" />
                    <Separator orientation="vertical" class="mr-2 data-[orientation=vertical]:h-4" />
                    <Breadcrumb v-if="breadcrumbs && breadcrumbs.length > 0">
                        <BreadcrumbList class="gap-1 sm:gap-1">
                            <template v-for="(item, index) in breadcrumbs" :key="item.href">
                                <BreadcrumbItem class="bg-muted/50 px-2 py-0.5 rounded-md border border-transparent hover:border-border transition-colors">
                                    <BreadcrumbLink v-if="index < breadcrumbs.length - 1" :href="item.href" class="text-xs font-medium">
                                        {{ item.title }}
                                    </BreadcrumbLink>
                                    <BreadcrumbPage v-else class="text-xs font-bold">
                                        {{ item.title }}
                                    </BreadcrumbPage>
                                </BreadcrumbItem>
                                <BreadcrumbSeparator
                                    v-if="index < breadcrumbs.length - 1"
                                    class="mx-0"
                                />
                            </template>
                        </BreadcrumbList>
                    </Breadcrumb>
                </div>
            </header>
            <div class="flex flex-1 flex-col gap-4 p-4 pt-0">
                <div class="min-h-screen flex-1 rounded-xl bg-muted/50 md:min-h-min p-5">
                    <slot />
                </div>
            </div>
            <Footer />
        </SidebarInset>
    </SidebarProvider>
</template>

<style scoped></style>
