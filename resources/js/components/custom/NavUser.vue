<script setup lang="ts">
import { computed } from 'vue';
import { SidebarMenu, SidebarMenuButton, SidebarMenuItem, useSidebar } from '@/components/ui/sidebar';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar';
import { ChevronsUpDown, Settings, Bell, LogOut, LayoutDashboard } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { profile as profileSettingsRoute } from '@/routes/settings';
import { dashboard as dashboardRoute } from '@/routes';

const props = defineProps<{
    user: {
        name: string;
        email: string;
        avatar?: string | null;
    };
}>();

const { isMobile } = useSidebar();

const initials = computed(() => {
    const name = props.user?.name ?? '';
    const parts = name.trim().split(/\s+/).filter(Boolean);
    const first = parts[0]?.[0] ?? '';
    const last = parts.length > 1 ? (parts[parts.length - 1][0] ?? '') : '';
    return (first + last).toUpperCase() || '?';
});
</script>

<template>
    <SidebarMenu>
        <SidebarMenuItem>
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <SidebarMenuButton size="lg" class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground">
                        <Avatar class="h-8 w-8 rounded-lg">
                            <AvatarImage :src="user.avatar || ''" :alt="user.name" />
                            <AvatarFallback class="rounded-lg"> {{ initials }} </AvatarFallback>
                        </Avatar>
                        <div class="grid flex-1 text-left text-sm leading-tight">
                            <span class="truncate font-medium">{{ user.name }}</span>
                            <span class="truncate text-xs">{{ user.email }}</span>
                        </div>
                        <ChevronsUpDown class="ml-auto size-4" />
                    </SidebarMenuButton>
                </DropdownMenuTrigger>
                <DropdownMenuContent
                    class="w-[--reka-dropdown-menu-trigger-width] min-w-56 rounded-lg"
                    :side="isMobile ? 'bottom' : 'right'"
                    align="end"
                    :side-offset="4"
                >
                    <DropdownMenuLabel class="p-0 font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                            <Avatar class="h-8 w-8 rounded-lg">
                                <AvatarImage :src="user.avatar || ''" :alt="user.name" />
                                <AvatarFallback class="rounded-lg"> {{ initials }} </AvatarFallback>
                            </Avatar>
                            <div class="grid flex-1 text-left text-sm leading-tight">
                                <span class="truncate font-semibold">{{ user.name }}</span>
                                <span class="truncate text-xs">{{ user.email }}</span>
                            </div>
                        </div>
                    </DropdownMenuLabel>
                    <DropdownMenuSeparator />
                    <DropdownMenuGroup>
                        <DropdownMenuItem as-child>
                            <Link :href="dashboardRoute.url()" class="flex w-full items-center gap-2">
                                <LayoutDashboard />
                                {{ trans('pages.ui.navigation.dashboard') }}
                            </Link>
                        </DropdownMenuItem>
                        <DropdownMenuItem as-child>
                            <Link :href="profileSettingsRoute.url()" class="flex w-full items-center gap-2">
                                <Settings />
                                {{ trans('pages.ui.navigation.settings') }}
                            </Link>
                        </DropdownMenuItem>
                        <DropdownMenuItem>
                            <Bell />
                            {{ trans('pages.ui.navigation.notifications') }}
                        </DropdownMenuItem>
                    </DropdownMenuGroup>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem as-child>
                        <Link :href="'/logout'" method="post" as="button" :preserve-scroll="false" class="flex w-full items-center gap-2">
                            <LogOut />
                            {{ trans('pages.ui.navigation.logout') }}
                        </Link>
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </SidebarMenuItem>
    </SidebarMenu>
</template>

<style scoped></style>
