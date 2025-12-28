<script setup lang="ts">
import { SidebarMenu, SidebarMenuItem, SidebarMenuButton } from '@/components/ui/sidebar';
import { useAppearance } from '@/composables/useAppearance';
import { computed, onMounted, ref } from 'vue';

const { appearance } = useAppearance();
const systemIsDark = ref(false);

const logoUrl = computed(() => {
    if (appearance.value === 'dark') {
        return '/images/Spillhuset_logo_light.png';
    }

    if (appearance.value === 'light') {
        return '/images/Spillhuset_logo_dark.png';
    }

    return systemIsDark.value ? '/images/Spillhuset_logo_light.png' : '/images/Spillhuset_logo_dark.png';
});

onMounted(() => {
    const mq = window.matchMedia('(prefers-color-scheme: dark)');
    systemIsDark.value = mq.matches;

    const handler = () => {
        systemIsDark.value = mq.matches;
    };

    mq.addEventListener('change', handler);
});
</script>

<template>
    <SidebarMenu>
        <SidebarMenuItem>
            <SidebarMenuButton size="lg" as-child>
                <a href="/">
                    <div class="flex aspect-square size-8 items-center justify-center rounded-lg">
                        <img :src="logoUrl" alt="Spillhuset Logo" />
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
