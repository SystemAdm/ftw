<script setup lang="ts">
import AppSidebar from '@/components/custom/AppSidebar.vue';
import { SidebarInset, SidebarProvider, SidebarTrigger } from '@/components/ui/sidebar';
import { Breadcrumb, BreadcrumbItem, BreadcrumbLink, BreadcrumbList, BreadcrumbPage, BreadcrumbSeparator } from '@/components/ui/breadcrumb';
import { Separator } from '@/components/ui/separator';
import Footer from '@/components/layouts/Footer.vue';
import { Toaster } from '@/components/ui/sonner'
import { usePage } from '@inertiajs/vue3'
import { useFlashToasts } from '@/composables/useFlashToasts'

// Bridge Laravel flash messages -> Sonner toasts with dedupe
const page = usePage()
useFlashToasts(page)
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
        <AppSidebar />
        <SidebarInset>
            <header
                class="flex h-16 shrink-0 items-center gap-2 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12"
            >
                <div class="flex items-center gap-2 px-4">
                    <SidebarTrigger class="-ml-1" />
                    <Separator orientation="vertical" class="mr-2 data-[orientation=vertical]:h-4" />
                    <Breadcrumb>
                        <BreadcrumbList>
                            <BreadcrumbItem class="hidden md:block">
                                <BreadcrumbLink href="#"> Building Your Application </BreadcrumbLink>
                            </BreadcrumbItem>
                            <BreadcrumbSeparator class="hidden md:block" />
                            <BreadcrumbItem>
                                <BreadcrumbPage>Data Fetching</BreadcrumbPage>
                            </BreadcrumbItem>
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
