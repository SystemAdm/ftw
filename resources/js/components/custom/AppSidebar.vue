<script setup lang="ts">
import NavAdmin from '@/components/custom/NavAdmin.vue';
import NavCrew from '@/components/custom/NavCrew.vue';
import NavDefault from '@/components/custom/NavDefault.vue';
import NavMod from '@/components/custom/NavMod.vue';
import NavUser from '@/components/custom/NavUser.vue';
import SidebarLogo from '@/components/custom/SidebarLogo.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader } from '@/components/ui/sidebar';
import { index as adminPermissionsRoute } from '@/routes/admin/permissions/index';
import { index as adminRelationsRoute } from '@/routes/admin/relations/index';
import { index as adminRolesRoute } from '@/routes/admin/roles/index';
import { index as adminTeamsRoute } from '@/routes/admin/teams/index';
import { index as adminUsersRoute } from '@/routes/admin/users/index';
import { index as adminWeekdaysRoute } from '@/routes/admin/weekdays/index';
import { index as adminPostcodesRoute } from '@/routes/admin/postcodes/index';
import { index as adminLocationsRoute } from '@/routes/admin/locations/index';
import { index as adminEventsRoute } from '@/routes/admin/events/index';
import { index as adminOpenRoute } from '@/routes/admin/open/index';
import { dashboard as adminDashboardRoute } from '@/routes/admin/index';
import { index as modOpenRoute } from '@/routes/mod/open/index';
import { index as crewTeamsRoute } from '@/routes/crew/teams/index';
import { index as crewEventsRoute } from '@/routes/crew/events/index';
import { dashboard as dashboardRoute } from '@/routes/index';
import { show as profileRoute } from '@/routes/profile/index';
import { profile as settingsRoute } from '@/routes/settings';
import { index as teamsRoute } from '@/routes/teams/index';
import { index as locationsRoute } from '@/routes/locations/index';
import { index as eventsRoute } from '@/routes/events/index';
import { login as loginRoute } from '@/routes';

import { usePage } from '@inertiajs/vue3';
import {
    Gavel,
    ShieldAlert,
    UserIcon,
    Users,
    CalendarDays,
    Mail,
    MapPinIcon,
    LayoutDashboard,
    Cog,
    HeartHandshake,
    DoorOpen,
    History,
    LogIn,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { trans } from 'laravel-vue-i18n';
import { AppPageProps } from '@/types';

const page = usePage<AppPageProps>();
const user = computed(() => {
    const u = page.props.auth?.user;
    return u ? { ...u, avatar: u.avatar ?? '', roles: page.props.auth?.roles ?? [] } : null;
});
const loggedInMenu = computed(() => [
    { title: trans('pages.ui.navigation.dashboard'), icon: LayoutDashboard, url: dashboardRoute.url() },
    { title: trans('pages.ui.navigation.profile'), icon: UserIcon, url: profileRoute.url() },
    { title: trans('pages.ui.navigation.settings'), icon: Cog, url: settingsRoute.url() },
]);
const loggedOutMenu = computed(() => [{ title: trans('pages.auth.login'), icon: LogIn, url: loginRoute.url() }]);
const defaultMenu = computed(() => [
    { title: trans('pages.ui.navigation.teams'), icon: Users, url: teamsRoute.url() },
    { title: trans('pages.ui.navigation.locations'), icon: MapPinIcon, url: locationsRoute.url() },
    { title: trans('pages.ui.navigation.events'), icon: CalendarDays, url: eventsRoute.url() },
]);
const menu = computed(() => (user.value ? [...loggedInMenu.value, ...defaultMenu.value] : [...loggedOutMenu.value, ...defaultMenu.value]));
</script>

<template>
    <Sidebar>
        <SidebarHeader>
            <SidebarLogo />
        </SidebarHeader>
        <SidebarContent>
            <NavAdmin
                v-if="page.props.auth.roles.includes('Admin')"
                :items="[
                    { title: trans('pages.ui.navigation.dashboard'), icon: LayoutDashboard, url: adminDashboardRoute.url() },
                    { title: trans('pages.ui.navigation.users'), icon: UserIcon, url: adminUsersRoute.url() },
                    { title: trans('pages.ui.navigation.roles'), icon: ShieldAlert, url: adminRolesRoute.url() },
                    { title: trans('pages.ui.navigation.relations'), icon: HeartHandshake, url: adminRelationsRoute.url() },
                    { title: trans('pages.ui.navigation.permissions'), icon: Gavel, url: adminPermissionsRoute.url() },
                    { title: trans('pages.ui.navigation.teams'), icon: Users, url: adminTeamsRoute.url() },
                    { title: trans('pages.ui.navigation.postcodes'), icon: Mail, url: adminPostcodesRoute.url() },
                    { title: trans('pages.ui.navigation.locations'), icon: MapPinIcon, url: adminLocationsRoute.url() },
                    { title: trans('pages.ui.navigation.weekdays'), icon: CalendarDays, url: adminWeekdaysRoute.url() },
                    { title: trans('pages.ui.navigation.events'), icon: CalendarDays, url: adminEventsRoute.url() },
                    { title: trans('pages.ui.navigation.admin_open'), icon: DoorOpen, url: adminOpenRoute.url() },
                ]"
            ></NavAdmin>
            <NavCrew
                v-if="page.props.auth.roles.includes('Crew') || page.props.auth.roles.includes('Admin')"
                :items="[
                    { title: trans('pages.ui.navigation.dashboard'), icon: LayoutDashboard, url: '/crew' },
                    { title: trans('pages.ui.navigation.teams'), icon: Users, url: crewTeamsRoute.url() },
                    { title: trans('pages.ui.navigation.events'), icon: CalendarDays, url: crewEventsRoute.url() },
                ]"
            ></NavCrew>
            <NavMod
                v-if="page.props.auth.roles.includes('Moderator') || page.props.auth.roles.includes('Admin')"
                :items="[{ title: trans('pages.ui.navigation.mod_open'), icon: History, url: modOpenRoute.url() }]"
            ></NavMod>
            <NavDefault :items="menu"></NavDefault>
        </SidebarContent>
        <SidebarFooter>
            <NavUser v-if="user" :user="user"></NavUser>
        </SidebarFooter>
    </Sidebar>
</template>

<style scoped></style>
