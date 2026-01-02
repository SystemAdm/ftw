<script setup lang="ts">
import NavAdmin from '@/components/custom/NavAdmin.vue';
import NavCrew from '@/components/custom/NavCrew.vue';
import NavDefault from '@/components/custom/NavDefault.vue';
import NavMod from '@/components/custom/NavMod.vue';
import NavUser from '@/components/custom/NavUser.vue';
import SidebarLogo from '@/components/custom/SidebarLogo.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader } from '@/components/ui/sidebar';
import { index as adminPermissionsRoute } from '@/actions/App/http/controllers/Admin/PermissionsController';
import { index as adminRelationsRoute } from '@/actions/App/http/controllers/Admin/RelationController';
import { index as adminRolesRoute } from '@/actions/App/http/controllers/Admin/RolesController';
import { index as adminTeamsRoute } from '@/actions/App/http/controllers/Admin/TeamsController';
import { index as adminUsersRoute } from '@/actions/App/http/controllers/Admin/UsersController';
import { index as adminWeekdaysRoute } from '@/actions/App/http/controllers/Admin/WeekdaysController';
import { index as adminPostcodesRoute } from '@/actions/App/http/controllers/Admin/PostCodeController';
import { index as adminLocationsRoute } from '@/actions/App/http/controllers/Admin/LocationController';
import { index as adminEventsRoute } from '@/actions/App/http/controllers/Admin/EventsController';
import { index as adminOpenRoute } from '@/actions/App/http/controllers/Admin/OpenController';
import { index as modOpenRoute } from '@/actions/App/http/controllers/mod/OpenController';
import { index as crewEventsRoute } from '@/actions/App/http/controllers/crew/EventsController';
import { dashboard as dashboardRoute } from '@/actions/App/http/controllers/auth/UsersController';
import { show as profileRoute } from '@/actions/App/http/controllers/ProfileController';
import { index as settingsRoute } from '@/actions/App/http/controllers/settings/index';
import { index as teamsRoute } from '@/actions/App/http/controllers/TeamController';
import { index as locationsRoute } from '@/actions/App/http/controllers/LocationController';
import { index as eventsRoute } from '@/actions/App/http/controllers/EventsController';

import { usePage } from '@inertiajs/vue3';
import { Gavel, ShieldAlert, UserIcon, Users, CalendarDays, Mail, MapPinIcon, LayoutDashboard, Cog, HeartHandshake, DoorOpen, History } from 'lucide-vue-next';
import { computed } from 'vue';
import { trans } from 'laravel-vue-i18n';
import { AppPageProps } from '@/types';

const page = usePage<AppPageProps>();
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
                v-if="page.props.auth.roles.includes('Admin')"
                :items="[
                    { title: trans('pages.ui.navigation.users'), icon: UserIcon, url: adminUsersRoute.url() },
                    { title: trans('pages.ui.navigation.roles'), icon: ShieldAlert, url: adminRolesRoute.url() },
                    { title: trans('pages.ui.navigation.relations'), icon: HeartHandshake, url: adminRelationsRoute.url() },
                    { title: trans('pages.ui.navigation.permissions'), icon: Gavel, url: adminPermissionsRoute.url() },
                    { title: trans('pages.ui.navigation.teams'), icon: Users, url: adminTeamsRoute.url() },
                    { title: trans('pages.ui.navigation.postcodes'), icon: Mail, url: adminPostcodesRoute.url() },
                    { title: trans('pages.ui.navigation.locations'), icon: MapPinIcon, url: adminLocationsRoute.url() },
                    { title: trans('pages.ui.navigation.weekdays'), icon: CalendarDays, url: adminWeekdaysRoute.url() },
                    { title: trans('pages.ui.navigation.events'), icon: CalendarDays, url: adminEventsRoute.url() },
                    { title: trans('pages.ui.navigation.admin_open'), icon: DoorOpen, url: adminOpenRoute.url() }
                ]"
            ></NavAdmin>
            <NavCrew
                v-if="page.props.auth.roles.includes('Crew') || page.props.auth.roles.includes('Admin')"
                :items="[{ title: trans('pages.ui.navigation.events'), icon: CalendarDays, url: crewEventsRoute.url() }]"
            ></NavCrew>
            <NavMod
                v-if="page.props.auth.roles.includes('Moderator') || page.props.auth.roles.includes('Admin')"
                :items="[{ title: trans('pages.ui.navigation.mod_open'), icon: History, url: modOpenRoute.url() }]"
            ></NavMod>
            <NavDefault
                :items="[
                    { title: trans('pages.ui.navigation.dashboard'), icon: LayoutDashboard, url: dashboardRoute.url() },
                    { title: trans('pages.ui.navigation.profile'), icon: UserIcon, url: profileRoute.url() },
                    { title: trans('pages.ui.navigation.settings'), icon: Cog, url: settingsRoute.url() },
                    { title: trans('pages.ui.navigation.teams'), icon: Users, url: teamsRoute.url() },
                    { title: trans('pages.ui.navigation.locations'), icon: MapPinIcon, url: locationsRoute.url() },
                    { title: trans('pages.ui.navigation.events'), icon: CalendarDays, url: eventsRoute.url() }
                ]"
            ></NavDefault>
        </SidebarContent>
        <SidebarFooter>
            <NavUser v-if="user" :user="user"></NavUser>
        </SidebarFooter>
    </Sidebar>
</template>

<style scoped></style>
