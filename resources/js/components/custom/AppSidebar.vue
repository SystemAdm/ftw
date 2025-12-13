<script setup lang="ts">
import NavAdmin from '@/components/custom/NavAdmin.vue';
import NavDefault from '@/components/custom/NavDefault.vue';
import NavMod from '@/components/custom/NavMod.vue';
import NavUser from '@/components/custom/NavUser.vue';
import SidebarLogo from '@/components/custom/SidebarLogo.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader } from '@/components/ui/sidebar';
import { index as adminPermissionsRoute } from '@/routes/admin/permissions/index';
import { index as adminRolesRoute } from '@/routes/admin/roles/index';
import { index as adminTeamsRoute } from '@/routes/admin/teams';
import { index as adminUsersRoute } from '@/routes/admin/users/index';
import { index as adminWeekdaysRoute } from '@/routes/admin/weekdays/index';
import { index as adminPostcodesRoute } from '@/routes/admin/postcodes';
import { index as adminLocationsRoute } from '@/routes/admin/locations';
import { usePage } from '@inertiajs/vue3';
import { Gavel, ShieldAlert, UserIcon, Users, CalendarDays, Mail, MapPinIcon, LayoutDashboard, Cog } from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage<PageProps>();
const user = computed(() => {
    const u = page.props.auth?.user;
    return u ? { ...u, avatar: u.avatar ?? '' } : null;
});
</script>

<template>
    <Sidebar>
        <SidebarHeader>
            <SidebarLogo />
        </SidebarHeader>
        <SidebarContent>
            <NavAdmin
                :items="[
                    { title: 'Users', icon: UserIcon, url: adminUsersRoute.url() },
                    { title: 'Roles', icon: ShieldAlert, url: adminRolesRoute.url() },
                    { title: 'Permissions', icon: Gavel, url: adminPermissionsRoute.url() },
                    { title: 'Teams', icon: Users, url: adminTeamsRoute.url() },
                    { title: 'Postcodes', icon: Mail, url: adminPostcodesRoute.url() },
                    { title: 'Locations', icon: MapPinIcon, url: adminLocationsRoute.url() },
                    { title: 'Weekdays', icon: CalendarDays, url: adminWeekdaysRoute.url() },
                ]"
            ></NavAdmin>
            <NavMod></NavMod>
            <NavDefault
                :items="[
                    { title: 'Dashboard', icon: LayoutDashboard, url: '/dashboard' },
                    { title: 'Profile', icon: UserIcon, url: '/profile' },
                    { title: 'Settings', icon: Cog, url: '/settings' },
                    { title: 'Teams', icon: Users, url: '/teams'},
                    { title: 'Locations', icon: MapPinIcon, url: '/locations'},
                ]"
            ></NavDefault>
        </SidebarContent>
        <SidebarFooter>
            <NavUser v-if="user" :user="user"></NavUser>
        </SidebarFooter>
    </Sidebar>
</template>

<style scoped></style>
