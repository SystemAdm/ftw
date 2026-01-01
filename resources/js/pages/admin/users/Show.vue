<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { trans } from 'laravel-vue-i18n';
import { Mail, Phone, Calendar, MapPin, User as UserIcon, ShieldCheck, ShieldAlert, Ban, Clock, Users, Activity, ArrowLeft } from 'lucide-vue-next';
import { computed } from 'vue';
import { BreadcrumbItemType, AppPageProps } from '@/types';

interface User {
    id: number;
    name: string;
    email: string;
    username: string | null;
    avatar: string | null;
    birthday: string | null;
    postal_code: string | null;
    postal_code_visibility: string;
    birthday_visibility: string;
    email_verified_at: string | null;
    banned_at: string | null;
    banned_to: string | null;
    ban_reason: string | null;
    is_banned: boolean;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    verifier?: { id: number; name: string } | null;
    teams: any[];
    postal_code_relation?: any | null;
    phone_numbers: any[];
    guardians: any[];
    minors: any[];
    logs: any[];
    building_logs: any[];
    bans: any[];
}

interface PageProps extends AppPageProps {
    user: User;
    i18n: {
        locale: string;
    };
}

const page = usePage<PageProps>();
const user = computed(() => page.props.user);

const combinedLogs = computed(() => {
    const eventLogs = (user.value.logs || []).map((log: any) => ({
        ...log,
        type: 'event',
    }));
    const buildingLogs = (user.value.building_logs || []).map((log: any) => ({
        ...log,
        type: 'building',
    }));

    return [...eventLogs, ...buildingLogs].sort((a: any, b: any) => {
        return new Date(b.created_at).getTime() - new Date(a.created_at).getTime();
    });
});

const breadcrumbs = computed<BreadcrumbItemType[]>(() => [
    {
        title: trans('pages.settings.users.title'),
        href: '/admin/users',
    },
    {
        title: user.value.name,
        href: `/admin/users/${user.value.id}`,
    },
]);

function formatDate(date: string | null) {
    if (!date) return '-';
    return new Date(date).toLocaleString(page.props.i18n.locale, {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function formatBirthday(date: string | null) {
    if (!date) return '-';
    return new Date(date).toLocaleDateString(page.props.i18n.locale, {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}
</script>

<template>
    <SidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="user.name" />

        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="outline" size="icon" as-child>
                        <Link href="/admin/users">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">{{ user.name }}</h1>
                        <p class="text-sm text-muted-foreground" v-if="user.username">@{{ user.username }}</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Badge v-if="user.is_banned" variant="destructive" class="flex items-center gap-1">
                        <Ban class="h-3 w-3" />
                        {{ trans('pages.settings.users.status.banned') }}
                    </Badge>
                    <Badge
                        v-if="user.email_verified_at"
                        variant="outline"
                        class="flex items-center gap-1 border-green-200 bg-green-50 text-green-600 dark:border-green-900/50 dark:bg-green-950/20"
                    >
                        <ShieldCheck class="h-3 w-3" />
                        {{ trans('pages.settings.users.status.verified') }}
                    </Badge>
                    <Badge
                        v-else
                        variant="outline"
                        class="flex items-center gap-1 border-amber-200 bg-amber-50 text-amber-600 dark:border-amber-900/50 dark:bg-amber-950/20"
                    >
                        <ShieldAlert class="h-3 w-3" />
                        {{ trans('pages.settings.users.status.unverified') }}
                    </Badge>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-2">
                    <!-- Details Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <UserIcon class="h-5 w-5" />
                                {{ trans('pages.settings.users.title') }} Info
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div class="space-y-4">
                                    <div class="flex items-center gap-3">
                                        <Mail class="h-4 w-4 text-muted-foreground" />
                                        <div>
                                            <div class="text-xs text-muted-foreground uppercase">
                                                {{ trans('pages.settings.users.fields.email') }}
                                            </div>
                                            <div class="text-sm font-medium">{{ user.email }}</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <Phone class="h-4 w-4 text-muted-foreground" />
                                        <div>
                                            <div class="text-xs text-muted-foreground uppercase">
                                                {{ trans('pages.settings.users.fields.phone') }}
                                            </div>
                                            <div v-if="user.phone_numbers.length > 0" class="space-y-1">
                                                <div
                                                    v-for="phone in user.phone_numbers"
                                                    :key="phone.id"
                                                    class="flex items-center gap-2 text-sm font-medium"
                                                >
                                                    {{ phone.e164 }}
                                                    <Badge
                                                        v-if="phone.pivot.primary"
                                                        variant="outline"
                                                        class="h-4 px-1 text-[10px] tracking-wider uppercase"
                                                        >Primary</Badge
                                                    >
                                                </div>
                                            </div>
                                            <div v-else class="text-sm font-medium text-muted-foreground">-</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-4">
                                    <div class="flex items-center gap-3">
                                        <Calendar class="h-4 w-4 text-muted-foreground" />
                                        <div>
                                            <div class="text-xs text-muted-foreground uppercase">
                                                {{ trans('pages.settings.users.fields.birthday') }}
                                            </div>
                                            <div class="text-sm font-medium">{{ formatBirthday(user.birthday) }}</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <MapPin class="h-4 w-4 text-muted-foreground" />
                                        <div>
                                            <div class="text-xs text-muted-foreground uppercase">
                                                {{ trans('pages.settings.users.fields.postal_code') }}
                                            </div>
                                            <div class="text-sm font-medium">
                                                {{ trans('pages.settings.users.fields.code') }}: {{ user.postal_code?.postal_code || '-' }}
                                            </div>
                                            <div class="text-sm font-medium">
                                                {{ trans('pages.settings.postcodes.fields.city') }}: {{ user.postal_code?.city || '-' }}
                                            </div>
                                            <div class="text-sm font-medium">
                                                {{ trans('pages.settings.postcodes.fields.municipality') }}: {{ user.postal_code?.municipality || '-' }}
                                            </div>
                                            <div class="text-sm font-medium">
                                                {{ trans('pages.settings.postcodes.fields.state') }}: {{ user.postal_code?.state || '-' }}
                                            </div>
                                            <div class="text-sm font-medium">
                                                {{ trans('pages.settings.postcodes.fields.country') }}: {{ user.postal_code?.country || '-' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <Separator class="my-6" />

                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div class="space-y-1">
                                    <div class="text-xs text-muted-foreground uppercase">Created At</div>
                                    <div class="text-sm font-medium">{{ formatDate(user.created_at) }}</div>
                                </div>
                                <div class="space-y-1">
                                    <div class="text-xs text-muted-foreground uppercase">Verified By</div>
                                    <div class="text-sm font-medium" v-if="user.verifier">{{ user.verifier.name }}</div>
                                    <div class="text-sm font-medium text-muted-foreground" v-else>-</div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Relations Card -->
                    <Card v-if="user.guardians.length > 0 || user.minors.length > 0">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Users class="h-5 w-5" />
                                Relationships
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-6">
                                <div v-if="user.guardians.length > 0">
                                    <h3 class="mb-3 text-sm font-semibold">Guardians</h3>
                                    <div class="grid gap-4">
                                        <div
                                            v-for="guardian in user.guardians"
                                            :key="guardian.id"
                                            class="flex items-center justify-between rounded-lg border p-3"
                                        >
                                            <div>
                                                <div class="font-medium">{{ guardian.name }}</div>
                                                <div class="text-xs text-muted-foreground">{{ guardian.pivot.relationship }}</div>
                                            </div>
                                            <Button variant="ghost" size="sm" as-child>
                                                <Link :href="`/admin/users/${guardian.id}`">View</Link>
                                            </Button>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="user.minors.length > 0">
                                    <h3 class="mb-3 text-sm font-semibold">Minors</h3>
                                    <div class="grid gap-4">
                                        <div
                                            v-for="minor in user.minors"
                                            :key="minor.id"
                                            class="flex items-center justify-between rounded-lg border p-3"
                                        >
                                            <div>
                                                <div class="font-medium">{{ minor.name }}</div>
                                                <div class="text-xs text-muted-foreground">{{ minor.pivot.relationship }}</div>
                                            </div>
                                            <Button variant="ghost" size="sm" as-child>
                                                <Link :href="`/admin/users/${minor.id}`">View</Link>
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Activity Log -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Activity class="h-5 w-5" />
                                Recent Activity
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="combinedLogs.length === 0" class="py-4 text-sm text-muted-foreground italic">No recent activity found.</div>
                            <Table v-else>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Action</TableHead>
                                        <TableHead>Target</TableHead>
                                        <TableHead class="text-right">Date</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="log in combinedLogs" :key="log.type + '-' + log.id">
                                        <TableCell class="font-medium">
                                            <span v-if="log.action === 'in'" class="font-semibold text-green-600 dark:text-green-400">
                                                {{ trans('pages.mod.open.actions.in') }}
                                            </span>
                                            <span v-else-if="log.action === 'out'" class="font-semibold text-amber-600 dark:text-amber-400">
                                                {{ trans('pages.mod.open.actions.out') }}
                                            </span>
                                            <span v-else class="capitalize">{{ log.action }}</span>
                                        </TableCell>
                                        <TableCell>
                                            <template v-if="log.type === 'event'">
                                                <Link v-if="log.event" :href="`/admin/events/${log.event.id}`" class="text-primary hover:underline">
                                                    {{ log.event.title || log.event.name || 'Event #' + log.event.id }}
                                                </Link>
                                                <span v-else class="text-muted-foreground">-</span>
                                            </template>
                                            <template v-else-if="log.type === 'building'">
                                                <span class="text-muted-foreground italic">Building Access</span>
                                            </template>
                                        </TableCell>
                                        <TableCell class="text-right text-xs text-muted-foreground">
                                            {{ formatDate(log.created_at) }}
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                </div>

                <div class="space-y-6">
                    <!-- Teams Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Users class="h-5 w-5" />
                                Teams
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="user.teams.length === 0" class="text-sm text-muted-foreground italic">Not assigned to any teams.</div>
                            <div v-else class="flex flex-wrap gap-2">
                                <Badge v-for="team in user.teams" :key="team.id" variant="secondary">
                                    {{ team.name }}
                                </Badge>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Ban History Card -->
                    <Card v-if="user.bans.length > 0 || user.is_banned">
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Ban class="h-5 w-5 text-destructive" />
                                Ban History
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div
                                    v-for="ban in user.bans"
                                    :key="ban.id"
                                    class="rounded-lg border p-3 text-sm"
                                    :class="{
                                        'border-red-200 bg-red-50 dark:border-red-900 dark:bg-red-950/20':
                                            ban.id === user.bans[0].id && user.is_banned,
                                    }"
                                >
                                    <div class="mb-2 flex items-center justify-between">
                                        <span class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                                            {{ ban.banned_to ? 'Temporary' : 'Permanent' }}
                                        </span>
                                        <span class="text-[10px] text-muted-foreground">{{ formatDate(ban.banned_at) }}</span>
                                    </div>
                                    <div class="mb-1 font-medium">{{ ban.reason }}</div>
                                    <div class="text-xs text-muted-foreground" v-if="ban.banned_to">Until: {{ formatDate(ban.banned_to) }}</div>
                                    <div class="mt-2 text-xs text-muted-foreground" v-if="ban.banned_by">By: {{ ban.banned_by.name }}</div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Status Info -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2 text-sm">
                                <Clock class="h-4 w-4" />
                                Timestamps
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div>
                                <div class="text-[10px] tracking-wider text-muted-foreground uppercase">Created</div>
                                <div class="text-xs font-medium">{{ formatDate(user.created_at) }}</div>
                            </div>
                            <div>
                                <div class="text-[10px] tracking-wider text-muted-foreground uppercase">Updated</div>
                                <div class="text-xs font-medium">{{ formatDate(user.updated_at) }}</div>
                            </div>
                            <div v-if="user.deleted_at">
                                <div class="text-[10px] font-bold tracking-wider text-destructive uppercase">Deleted</div>
                                <div class="text-xs font-medium text-destructive">{{ formatDate(user.deleted_at) }}</div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </SidebarLayout>
</template>
