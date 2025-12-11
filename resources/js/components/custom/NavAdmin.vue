<script setup lang="ts">
import { SidebarMenu, SidebarMenuItem, SidebarMenuButton, SidebarGroup, SidebarGroupLabel } from '@/components/ui/sidebar';
import type { Component } from 'vue';
import { Link } from '@inertiajs/vue3';

interface NavItem {
    title: string;
    url: string;
    icon?: Component;
}

defineProps<{
    items: NavItem[];
}>();
</script>

<template>
    <SidebarGroup class="group-data-[collapsible=icon]:hidden">
        <SidebarGroupLabel>Admin Menu</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton :tooltip="item.title" as-child>
                    <Link :href="item.url" class="flex items-center gap-2">
                        <component :is="item.icon" v-if="item.icon" class="size-4" />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>

<style scoped></style>
