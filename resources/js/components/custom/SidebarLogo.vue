<script setup lang="ts">
import { SidebarMenu,SidebarMenuItem,SidebarMenuButton } from '@/components/ui/sidebar';
</script>

<template>
    <SidebarMenu>
        <SidebarMenuItem>
            <SidebarMenuButton size="lg" as-child>
                <a href="#">
                    <div class="flex aspect-square size-8 items-center justify-center rounded-lg">
                        <img src="/images/Spillhuset_logo_light.png" alt="Spillhuset Logo" />
                    </div>
                    <div class="flex flex-col gap-0.5 leading-none">
                        <span class="font-medium">Spillhuset</span>
                        <span class="">v1.0.0</span>
                    </div>
                </a>
            </SidebarMenuButton>
        </SidebarMenuItem>
    </SidebarMenu>
</template>

<style scoped></style>
